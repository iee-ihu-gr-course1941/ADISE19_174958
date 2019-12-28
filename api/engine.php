<?php
require_once "entranceSystem.php";
require_once "joinSystem.php";
require_once "gameSystem.php";
require_once "../database/variables.php";

updateGames();//update each game's status

session_start();

$inputJSON = json_decode(file_get_contents('php://input'), TRUE);

$method = $_SERVER['REQUEST_METHOD'];

$request = explode('/', trim($_SERVER['PATH_INFO'], '/'));

$input = json_decode(file_get_contents('php://input'), true);

switch ($request[0]) {
    case "signIn" :
        $user_name = $input["user_name"];
        $pass_word = $input["pass_word"];
        signIn($user_name, $pass_word);
        break;
    case "signUp" :
        $user_name = $input["user_name"];
        $pass_word = $input["pass_word"];
        signUp($user_name, $pass_word);
        break;
    case "logout" :
        logout();
        break;
    case "join":
        isLogin();
        join_game();
        break;
    case "token":
        isLogin();
        token();
        break;
    default:
        $token = getToken();
        $gameId = getUsersGameId($token);
        markLeftPlayers($gameId);//delete players that are in betting or hitting status and haven't played for 2 minutes or more.

        switch ($request[0]) {
            case "game":
                game();
                break;
            case "user":
                user();
                break;
            case "bet":
                $method = $_SERVER['REQUEST_METHOD'];
                if ($method === 'POST') {
                    if (!isset($_POST['amount'])) {
                        http_response_code(400);
                        exit();
                    }
                    $amount = $_POST['amount'];
                    if ($amount <= 0) {
                        markPlayerAsLeft($token, $gameId);
                        exit();
                    }
                    bet($amount);
                }
                break;
            case "hit":
                hit();
                break;
            case "enough":
                enough();
                break;
            default:
                http_response_code(404);
        }

}

function markPlayerAsLeft($token, $gameId)
{
    $connection = mysqli_connect(HOST, USER, PASSWORD, DATABASE);

    $connection->begin_transaction(MYSQLI_TRANS_START_READ_WRITE);

    $mysqli_stmt = $connection->prepare("UPDATE players SET player_status = 'left_game' WHERE token = ?");

    $mysqli_stmt->bind_param("s", $token);
    $mysqli_stmt->execute();

    decreasePlayers(1, $gameId, $connection);

    $connection->commit();
}


function markLeftPlayers($gameId)
{
    $connection = mysqli_connect(HOST, USER, PASSWORD, DATABASE);

    $connection->begin_transaction(MYSQLI_TRANS_START_READ_WRITE);

    $mysqli_stmt = $connection->prepare("UPDATE players SET player_status = 'left_game' WHERE TIMESTAMPDIFF(MINUTE,last_action,NOW()) >= 1 AND (player_status = 'hitting' OR player_status = 'betting')");

    $mysqli_stmt->execute();

    $affected_rows = $mysqli_stmt->affected_rows;

    decreasePlayers($affected_rows, $gameId, $connection);

    $connection->commit();

    $connection->close();
}

function decreasePlayers($num, $gameId, $connection)
{

    $mysqli_stmt = $connection->prepare("UPDATE games SET nums_of_players = nums_of_players - ? WHERE game_id = ?");

    $mysqli_stmt->bind_param("ii", $num, $gameId);

    $mysqli_stmt->execute();

}

function isLogin()
{
    if (!isset($_SESSION["user_name"])) {
        http_response_code(401);
        exit();
    }
}

function updateGames()
{
    $connection = mysqli_connect(HOST, USER, PASSWORD, DATABASE);

    $connection->begin_transaction(MYSQLI_TRANS_START_READ_WRITE);

    $selectGames = $connection->prepare("SELECT * FROM games ");

    $selectGames->execute();

    $mysqli_result = $selectGames->get_result();

    $selectPlayingPlayers = $connection->prepare("SELECT COUNT(*) as players_playing FROM players WHERE player_status != 'waiting' AND player_status != 'left_game' AND game_id = ? ");
    while ($row = $mysqli_result->fetch_assoc()) {
        $selectPlayingPlayers->bind_param("i",$row["game_id"]);
        $selectPlayingPlayers->execute();
        $numOfPlayersPlaying = $selectPlayingPlayers->get_result();
        $numberOfNotWaitingPlayers = $numOfPlayersPlaying ->fetch_assoc()["players_playing"];

        if ($row["games_status"] === "initialized" || $numberOfNotWaitingPlayers == 0) {
            prepareGame($row["game_id"],$connection);
        } else if ($row["games_status"] === "betting") {
            checkBetting($row["game_id"],$connection);
        } else if ($row["games_status"] === "players_turn") {
            checkPlayersTurn($row["game_id"],$connection);
        } else if ($row['games_status'] === "computer_turn") {
            checkComputerTurn($row["game_id"],$connection);
        } else if ($row["games_status"] === "end_game") {
            checkEndGame($row["game_id"],$connection);
        }
    }

    $connection->commit();

    $connection->close();
}

function prepareGame($game_id, $connection)
{
    $markGameCardsAsNotTaken = $connection->prepare("UPDATE game_cards SET taken = false WHERE game_id = ?");
    $markGameCardsAsNotTaken->bind_param("i",$game_id);
    $markGameCardsAsNotTaken->execute();

    $deleteLeftPlayers = $connection->prepare("DELETE FROM players WHERE game_id = ? AND player_status ='left_game' ");
    $deleteLeftPlayers->bind_param("i",$game_id);
    $deleteLeftPlayers->execute();
    decreasePlayers($deleteLeftPlayers->affected_rows,$game_id,$connection);

    $deleteComputersCards = $connection->prepare("DELETE FROM computer_hands WHERE game_id = ? ");
    $deleteComputersCards->bind_param("i",$game_id);
    $deleteComputersCards->execute();

    $selectRemainingTokens = $connection->prepare("SELECT token FROM players WHERE game_id = ? ");
    $selectRemainingTokens->bind_param("i",$game_id);
    $selectRemainingTokens->execute();
    $resultTokens = $selectRemainingTokens->get_result();

    $deletePlayersCards = $connection->prepare("DELETE FROM player_hands WHERE token = ? ");
    $deletePlayersBets = $connection->prepare("DELETE FROM bets WHERE token = ?");

    while ($token = $resultTokens->fetch_assoc()) {
        $deletePlayersBets->bind_param("s",$token["token"]);
        $deletePlayersBets->execute();

        $deletePlayersCards->bind_param("s", $token["token"]);
        $deletePlayersCards->execute();
    }

    $updateGamePoints = $connection->prepare("UPDATE games SET points = 0 WHERE game_id = ?");
    $updateGamePoints->bind_param("i",$game_id);
    $updateGamePoints->execute();

    changeStatusTo($game_id,'betting',$connection);

    $updatePlayersStatus = $connection->prepare("UPDATE players SET player_status = 'betting',last_action = NOW(),points = 0 WHERE game_id = ?");
    $updatePlayersStatus->bind_param("i",$game_id);
    $updatePlayersStatus->execute();


}

function checkBetting($game_id,$connection)
{

    $mysqli_stmt = $connection->prepare("SELECT player_status FROM players WHERE game_id = ? AND player_status = 'betting' ");

    $mysqli_stmt->bind_param("s", $game_id);

    $mysqli_stmt->execute();

    if ($mysqli_stmt->get_result()->num_rows == 0) {
        changeStatusTo($game_id, 'players_turn',$connection);
    }

}


function checkPlayersTurn($game_id,$connection)
{
    $checkIfAllPlayersDone = $connection->prepare("SELECT * FROM players WHERE (player_status = 'hitting' OR player_status = 'done_betting') AND game_id = ?");
    $checkIfAllPlayersDone->bind_param("i", $game_id);
    $checkIfAllPlayersDone->execute();

    $result = $checkIfAllPlayersDone->get_result();

    if ($result->num_rows == 0) {
        changeStatusTo($game_id, "computer_turn",$connection);
    } else {
        $isTherePlayerPlaying = $connection->prepare("SELECT * FROM players WHERE player_status = 'hitting' ");
        $isTherePlayerPlaying->execute();
        $mysqli_result = $isTherePlayerPlaying->get_result();
        if ($mysqli_result->num_rows == 0) {
            $nextPlayer = $connection->prepare("UPDATE players p set p.player_status = 'hitting' WHERE p.player_status = 'done_betting' AND last_action <= (SELECT MIN(p2.last_action) FROM players p2 WHERE p2.player_status = 'done_betting')");
            $nextPlayer->execute();
        }
    }

}

function checkComputerTurn($gameId, $connection)
{
    $selectComputerPoints = $connection->prepare("SELECT points FROM games WHERE game_id = ? ");
    $selectComputerPoints->bind_param("i", $gameId);
    $selectComputerPoints->execute();
    $result = $selectComputerPoints->get_result();
    $points = $result->fetch_assoc()['points'];

    if ($points > 17) {
        changeStatusTo($gameId,"end_game",$connection);
    }else{
        $card = getCard($gameId, $connection);
        $insertCard = $connection->prepare("INSERT INTO computer_hands(game_id, card_color, card_value) VALUES(?,?,?)");
        $insertCard->bind_param("iss",$gameId,$card['card_color'],$card['card_value']);
        $insertCard->execute();

        $updatePoints = $connection->prepare("UPDATE games SET points = points + (SELECT cp.points FROM cards_points cp WHERE card_value = ? AND card_color = ?) WHERE game_id = ?");
        $updatePoints->bind_param("ssi", $card['card_value'], $card['card_color'], $gameId);
        $updatePoints->execute();
    }
}

function checkEndGame($gameId, $connection)
{
    $selectWinners = $connection->prepare("SELECT u.user_name as username,amount FROM my_users u INNER JOIN players p ON p.user_name = u.user_name INNER JOIN games g on p.game_id = g.game_id INNER JOIN bets b ON b.token = p.token WHERE p.points <= 21 AND ((g.points <= 21 AND g.points < p.points) OR g.points > 21)  AND g.game_id = ?");
    $selectWinners->bind_param("i", $gameId);
    $selectWinners->execute();
    $winners = $selectWinners->get_result();

    $updateWinner = $connection->prepare("UPDATE my_users SET balance = balance + ? WHERE user_name = ?");

    while ($winner = $winners->fetch_assoc()) {
        $amount = ($winner["amount"]*1.5+$winner["amount"]);
        $updateWinner->bind_param("is",$amount,$winner["username"]);
        $updateWinner->execute();
    }

    $updateAllPlayersToWaitingStatus  = $connection->prepare("UPDATE players SET player_status = 'waiting' WHERE game_id = ? AND player_status != 'left_game'");
    $updateAllPlayersToWaitingStatus->bind_param("i", $gameId);
    $updateAllPlayersToWaitingStatus->execute();

    changeStatusTo($gameId,"initialized",$connection);

}

function changeStatusTo($game_id, $status,$connection)
{
    $mysqli_stmt = $connection->prepare("UPDATE games SET games_status = ? WHERE game_id = ?");

    $mysqli_stmt->bind_param("si", $status, $game_id);

    $mysqli_stmt->execute();

}