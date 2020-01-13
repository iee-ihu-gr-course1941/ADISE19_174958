<?php
require_once "entranceSystem.php";
require_once "joinSystem.php";
require_once "gameSystem.php";
require_once "../database/variables.php";

session_start();//start session to be able to user user's username saved in the session.

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
        markLeftPlayers();
        updateGames();

        switch ($request[0]) {
            case "game":
                game();
                break;
            case "bet":
                if ($method === 'POST') {
                    if (!isset($_POST['amount'])) {
                        http_response_code(400);
                        exit();
                    }
                    $amount = $_POST['amount'];
                    if ($amount <= 0) {
                        markPlayerAsLeft($token);
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
                print "NOT FOUND";
                http_response_code(404);
        }

}

/**
 * Marks the player with the given token as a left player.
 * @param $token
 */
function markPlayerAsLeft($token)
{
    $connection = mysqli_connect(HOST, USER, PASSWORD, DATABASE, null, SOCKET);

    $mysqli_stmt = $connection->prepare("UPDATE players SET player_status = 'left_game' WHERE token = ?");

    $mysqli_stmt->bind_param("s", $token);
    $mysqli_stmt->execute();

    $connection->close();
}

/**
 * Marks all the players as left players who are in betting or hitting state and  haven't been active for more than one minute.
 */
function markLeftPlayers()
{
    $connection = mysqli_connect(HOST, USER, PASSWORD, DATABASE, null, SOCKET);

    $mysqli_stmt = $connection->prepare("UPDATE players SET player_status = 'left_game' WHERE TIMESTAMPDIFF(MINUTE,last_action,NOW()) >= 1 AND (player_status = 'hitting' OR player_status = 'betting')");

    $mysqli_stmt->execute();

    $connection->close();
}

/**
 * Decreases the number of the players in the game with the given game id by the given num argument.
 * @param $num
 * @param $gameId
 * @param $connection
 */
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

/**
 * For each active game,calls a function appropriate to its status and condition.
 */
function updateGames()
{
    $connection = mysqli_connect(HOST, USER, PASSWORD, DATABASE, null, SOCKET);

    $selectGames = $connection->prepare("SELECT game_id,games_status,TIMESTAMPDIFF(SECOND,initialized,NOW()) as past_since_initialized FROM games ");

    $selectGames->execute();

    $mysqli_result = $selectGames->get_result();
    while ($row = $mysqli_result->fetch_assoc()) {
        if ($row["games_status"] === "initialized" && $row['past_since_initialized'] >= 10 ) {
            prepareGame($row["game_id"], $connection);
        } else if ($row["games_status"] === "betting" ) {
            checkBetting($row["game_id"], $connection);
        } else if ($row["games_status"] === "players_turn"  ) {
            checkPlayersTurn($row["game_id"], $connection);
        } else if ($row['games_status'] === "computer_turn"  ) {
            checkComputerTurn($row["game_id"], $connection);
        } else if ($row["games_status"] === "end_game" ) {
            checkEndGame($row["game_id"], $connection);
        }
    }

    $connection->close();
}

/**
 * Prepares the game for the game with the given game id by :
 * 1)Inserting two cards for each player that belongs to the game with the given game id.
 * 2)Inserting one card to the computer.
 * @param $game_id
 * @param $connection
 */
function prepareGame($game_id, $connection)
{
    $selectRemainingTokens = $connection->prepare("SELECT token FROM players WHERE game_id = ? ");
    $selectRemainingTokens->bind_param("i", $game_id);
    $selectRemainingTokens->execute();
    $resultTokens = $selectRemainingTokens->get_result();

    $insertPlayerCard = $connection->prepare("INSERT INTO player_hands(token,card_color,card_value) VALUES(?,?,?)");
    $updatePlayerPoints = $connection->prepare("UPDATE players SET points = points + ? WHERE token = ?");

    changeStatusTo($game_id, 'betting', $connection);

    $updatePlayersStatus = $connection->prepare("UPDATE players SET player_status = 'betting',last_action = NOW() WHERE game_id = ? AND player_status != 'left_game'");
    $updatePlayersStatus->bind_param("i", $game_id);
    $updatePlayersStatus->execute();

    while ($token = $resultTokens->fetch_assoc()) {
        $currentPoints = 0;
        for ($index = 0; $index < 2; $index++) {
            $card = getCard($game_id, $connection);
            $points = getPoints($card, $currentPoints, $connection);
            $currentPoints += $points;
            $insertPlayerCard->bind_param("sss", $token["token"], $card["card_color"], $card["card_value"]);
            $updatePlayerPoints->bind_param("is", $points, $token["token"]);
            $insertPlayerCard->execute();
            $updatePlayerPoints->execute();
        }

    }

    $insertGameCard = $connection->prepare("INSERT INTO computer_hands(game_id, card_color, card_value) VALUES(?,?,?)");
    $increaseGamePoints = $connection->prepare("UPDATE games SET points = points + ? WHERE game_id = ?");
    $computerCard = getCard($game_id, $connection);
    $increaseGamePoints->bind_param("ii", $computerCard["points"], $game_id);
    $insertGameCard->bind_param("iss", $game_id, $computerCard["card_color"], $computerCard["card_value"]);
    $insertGameCard->execute();
    $increaseGamePoints->execute();
}

/**
 * Checks whether there are players left in betting state.If none left,changes the status of the game to players_turn.
 * @param $game_id
 * @param $connection
 */
function checkBetting($game_id, $connection)
{

    $mysqli_stmt = $connection->prepare("SELECT player_status FROM players WHERE game_id = ? AND player_status = 'betting' ");

    $mysqli_stmt->bind_param("s", $game_id);

    $mysqli_stmt->execute();

    if ($mysqli_stmt->get_result()->num_rows == 0) {
        changeStatusTo($game_id, 'players_turn', $connection);
    }

}

/**
 * Checks whether all players with less than 21 points are done hitting.If true,updates the statuses of the players that might have 21 points already to done hitting and
 * switches the game's status to computer_turn.Otherwise,finds the next player to begin hitting.
 * @param $game_id
 * @param $connection
 */
function checkPlayersTurn($game_id, $connection)
{
    $checkIfAllPlayersDone = $connection->prepare("SELECT * FROM players WHERE (player_status = 'hitting' OR player_status = 'done_betting') AND points < 21 AND game_id = ?");
    $checkIfAllPlayersDone->bind_param("i", $game_id);
    $checkIfAllPlayersDone->execute();

    $result = $checkIfAllPlayersDone->get_result();

    if ($result->num_rows == 0) {
        $updateStatus = $connection->prepare("UPDATE players SET player_status = 'done_hitting' WHERE game_id = ? AND player_status != 'left_game'");
        $updateStatus->bind_param("i", $game_id);
        $updateStatus->execute();
        changeStatusTo($game_id, "computer_turn", $connection);
    } else {
        $isTherePlayerPlaying = $connection->prepare("SELECT * FROM players WHERE player_status = 'hitting' ");
        $isTherePlayerPlaying->execute();
        $mysqli_result = $isTherePlayerPlaying->get_result();
        if ($mysqli_result->num_rows == 0) {
            $minLastAction = $connection->prepare("SELECT MIN(p2.last_action) as min_last_action FROM players as p2 WHERE p2.player_status = 'done_betting'");
            $minLastAction->execute();
            $minResult = $minLastAction->get_result();
            $nextPlayer = $connection->prepare("UPDATE players as p set p.player_status = 'hitting',last_action = NOW() WHERE p.player_status = 'done_betting' AND last_action <= ?");
            $nextPlayer->bind_param("s", $minResult->fetch_assoc()["min_last_action"]);
            $nextPlayer->execute();
            foreach ($connection->error_list as $error) {
                print_r($error);
            }
        }
    }

}

/**
 * Checks whether the computer hae reached or passed the threshold of 17 points.If true,changes the game's status to end_game.
 * Otherwise,picks the next card for the computer.
 * @param $gameId
 * @param $connection
 */
function checkComputerTurn($gameId, $connection)
{
    $selectComputerPoints = $connection->prepare("SELECT points FROM games WHERE game_id = ? ");
    $selectComputerPoints->bind_param("i", $gameId);
    $selectComputerPoints->execute();
    $result = $selectComputerPoints->get_result();
    $computerPoints = $result->fetch_assoc()['points'];

    if ($computerPoints >= 17) {
        changeStatusTo($gameId, "end_game", $connection);
    } else {
        $card = getCard($gameId, $connection);
        $insertCard = $connection->prepare("INSERT INTO computer_hands(game_id, card_color, card_value) VALUES(?,?,?)");
        $insertCard->bind_param("iss", $gameId, $card['card_color'], $card['card_value']);
        $insertCard->execute();

        $points = getPoints($card, $computerPoints, $connection);

        $updatePoints = $connection->prepare("UPDATE games SET points = points + ? WHERE game_id = ?");
        $updatePoints->bind_param("ii", $points, $gameId);
        $updatePoints->execute();
    }
}

/**
 * Ends the game by:
 * 1)Increasing the amount of the winner by 1.5 of his/her bet.
 * 2)Returning amount equal to the bet of the player if he/she has equal points with the computer
 * 3)Updating the players' statuses to waiting(those who haven't left the game).
 * 4)Updating game's cards so that all the cards are considered as not taken.
 * 5)Clears the game by calling clearGame function
 * 6)Changed the status of the game to initialized.
 * @param $gameId
 * @param $connection
 */
function checkEndGame($gameId, $connection)
{
    $selectWinners = $connection->prepare("SELECT u.user_name as username,amount FROM my_users u INNER JOIN players p ON p.user_name = u.user_name 
                                            INNER JOIN games g on p.game_id = g.game_id INNER JOIN bets b ON b.token = p.token 
                                            WHERE p.points <= 21 AND ((g.points <= 21 AND g.points < p.points) OR g.points > 21)  AND g.game_id = ?");
    $selectWinners->bind_param("i", $gameId);
    $selectWinners->execute();
    $winners = $selectWinners->get_result();

    $selectSamePoints = $connection->prepare("SELECT u.user_name as username,amount FROM my_users u INNER JOIN players p ON p.user_name = u.user_name 
                                                INNER JOIN games g on p.game_id = g.game_id INNER JOIN bets b ON b.token = p.token 
                                                WHERE p.points <= 21 AND g.points <= 21 AND p.points = g.points  AND g.game_id = ?");
    $selectSamePoints->bind_param("i", $gameId);
    $selectSamePoints->execute();
    $playersWithEqualPoints = $selectSamePoints->get_result();

    $updateBalance = $connection->prepare("UPDATE my_users SET balance = balance + ? WHERE user_name = ?");

    while ($winner = $winners->fetch_assoc()) {
        $amount = ($winner["amount"] * 1.5 + $winner["amount"]);
        $updateBalance->bind_param("is", $amount, $winner["username"]);
        $updateBalance->execute();
    }

    while ($player = $playersWithEqualPoints->fetch_assoc()) {
        $updateBalance->bind_param("is", $player["amount"], $player["username"]);
        $updateBalance->execute();
    }

    $updateAllPlayersToWaitingStatus = $connection->prepare("UPDATE players SET player_status = 'waiting',points = 0 WHERE game_id = ? AND player_status != 'left_game'");
    $updateAllPlayersToWaitingStatus->bind_param("i", $gameId);
    $updateAllPlayersToWaitingStatus->execute();

    $markGameCardsAsNotTaken = $connection->prepare("UPDATE game_cards SET taken = false WHERE game_id = ?");
    $markGameCardsAsNotTaken->bind_param("i", $gameId);
    $markGameCardsAsNotTaken->execute();

    clearGame($gameId, $connection);

    changeStatusTo($gameId, "initialized", $connection);

}

/**
 * Clears the game by:
 * 1)Deleting all left players from the game and decreasing the number of players by the number of players deleted.
 * 2)Deleting all cards of the computer and player.
 * 3)Updating the points of the game to zero.
 * @param $gameId
 * @param $connection
 */
function clearGame($gameId, $connection)
{

    $deleteLeftPlayers = $connection->prepare("DELETE FROM players WHERE game_id = ? AND player_status ='left_game' ");
    $deleteLeftPlayers->bind_param("i", $gameId);
    $deleteLeftPlayers->execute();
    decreasePlayers($deleteLeftPlayers->affected_rows, $gameId, $connection);

    $deleteComputersCards = $connection->prepare("DELETE FROM computer_hands WHERE game_id = ? ");
    $deleteComputersCards->bind_param("i", $gameId);
    $deleteComputersCards->execute();

    $selectRemainingTokens = $connection->prepare("SELECT token FROM players WHERE game_id = ? ");
    $selectRemainingTokens->bind_param("i", $gameId);
    $selectRemainingTokens->execute();
    $resultTokens = $selectRemainingTokens->get_result();

    $deletePlayersCards = $connection->prepare("DELETE FROM player_hands WHERE token = ? ");
    $deletePlayersBets = $connection->prepare("DELETE FROM bets WHERE token = ?");

    $updateGamePoints = $connection->prepare("UPDATE games SET points = 0 WHERE game_id = ?");
    $updateGamePoints->bind_param("i", $gameId);
    $updateGamePoints->execute();

    while ($token = $resultTokens->fetch_assoc()) {

        $deletePlayersBets->bind_param("s", $token["token"]);
        $deletePlayersBets->execute();

        $deletePlayersCards->bind_param("s", $token["token"]);
        $deletePlayersCards->execute();
    }

}

/**
 * Changes the status of the game with the given game id to the given value.If the value is initialized,also updates the initialized column of the table.
 * @param $game_id
 * @param $status
 * @param $connection
 */
function changeStatusTo($game_id, $status, $connection)
{
    $mysqli_stmt = null;

    if ($status === "initialized") {
        $mysqli_stmt = $connection->prepare("UPDATE games SET games_status = ? , initialized = NOW() WHERE game_id = ?");
        $mysqli_stmt->bind_param("si", $status, $game_id);
    } else {
        $mysqli_stmt = $connection->prepare("UPDATE games SET games_status = ? WHERE game_id = ?");

        $mysqli_stmt->bind_param("si", $status, $game_id);
    }

    $mysqli_stmt->execute();

}