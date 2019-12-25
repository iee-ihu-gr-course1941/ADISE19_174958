<?php
require_once "entranceSystem.php";
require_once "joinSystem.php";
require_once "gameSystem.php";
require_once "../database/variables.php";

deleteStalePlayers();//delete players that are in betting or hitting status and haven't played for 2 minutes or more.

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
    case "game":
        game();
        break;
    case "user":
        user();
        break;
    case "bet":
        $method = $_SERVER['REQUEST_METHOD'];
        if ($method === 'GET') {
            canBet();
        }else if($method === 'POST'){
            if (!isset($_POST['amount'])) {
                http_response_code(400);
                exit();
            }
            bet($_POST['amount']);
        }
        break;
    default:
        http_response_code(404);
        exit();
}

/**
 * Deletes all players that have been unresponsive for 2 minute or more.
 * If the user that fired the request was one of the players that got deleted,his/her token is deleted from SESSION.
 */
function deleteStalePlayers(){
    $connection = mysqli_connect(HOST, USER, PASSWORD, DATABASE);

    $mysqli_stmt = $connection->prepare("DELETE FROM players WHERE TIMESTAMPDIFF(MINUTE,last_action,NOW()) >= 2 AND (player_status = 'hitting' OR player_status = 'betting')");

    $mysqli_stmt->execute();

    $mysqli_stmt->affected_rows;

    $connection->close();

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

    $selectGames = $connection->prepare("SELECT * FROM games ");

    foreach ($connection->error_list as $error) {
        print_r($error);
    }

    $selectGames->execute();

    $mysqli_result = $selectGames->get_result();

    while ($row = $mysqli_result->fetch_assoc()) {
        if ($row["games_status"] == "initialized") {
            checkInitialized($row["game_id"]);
        } else if ($row["games_status"] == "betting") {
            checkBetting($row["game_id"]);
        } else if ($row["games_status"] == "players_turn") {
            checkPlayersTurn($row["game_id"]);
        }
    }

    $connection->close();
}

function checkInitialized($game_id)
{
    $connection = mysqli_connect(HOST, USER, PASSWORD, DATABASE);

    $connection->begin_transaction(MYSQLI_TRANS_START_READ_ONLY);

    $mysqli_stmt = $connection->prepare("SELECT nums_of_players FROM games WHERE game_id = ? ");

    $mysqli_stmt->bind_param("i", $game_id);

    $mysqli_stmt->execute();

    $result = $mysqli_stmt->get_result();

    if ($result->fetch_assoc()['nums_of_players'] > 0) {
        changeStatusTo($game_id, "betting");
    }

    $connection->commit();

    $connection->close();
}

function checkBetting($game_id)
{
    $connection = mysqli_connect(HOST, USER, PASSWORD, DATABASE);

    $connection->begin_transaction(MYSQLI_TRANS_START_READ_ONLY);

    $mysqli_stmt = $connection->prepare("SELECT player_status FROM players WHERE game_id = ? AND player_status = 'betting' ");

    $mysqli_stmt->bind_param("s", $game_id);

    $mysqli_stmt->execute();

    if ($mysqli_stmt->get_result()->num_rows == 0) {
        changeStatusTo($game_id, 'players_turn');
    }

    $connection->commit();

    $connection->close();
}


function checkPlayersTurn($game_id)
{
    $connection = mysqli_connect(HOST, USER, PASSWORD, DATABASE);

    $connection->begin_transaction(MYSQLI_TRANS_START_READ_WRITE);

    $checkIfAllPlayersDone = $connection->prepare("SELECT * FROM players WHERE player_status = 'hitting' || player_status = 'done_betting' AND game_id = ?");
    $checkIfAllPlayersDone->bind_param("i", $game_id);
    $checkIfAllPlayersDone->execute();

    $result = $checkIfAllPlayersDone->get_result();

    if ($result->num_rows == 0) {
        changeStatusTo($game_id, "computer_turn");
    } else {
        $isTherePlayerPlaying = $connection->prepare("SELECT * FROM players WHERE player_status = 'hitting' ");
        $isTherePlayerPlaying->execute();
        $mysqli_result = $isTherePlayerPlaying->get_result();
        if ($mysqli_result->num_rows == 0) {
            $nextPlayer = $connection->prepare("UPDATE players p set p.player_status = 'hitting' WHERE p.player_status = 'done_betting' AND last_action <= (SELECT MIN(p2.last_action) FROM players p2 WHERE p2.player_status = 'done_betting')");
            $nextPlayer->execute();
        }
    }

    $connection->commit();

    $connection->close();
}


function changeStatusTo($game_id, $status)
{
    $connection = mysqli_connect(HOST, USER, PASSWORD, DATABASE);

    $mysqli_stmt = $connection->prepare("UPDATE games SET games_status = ? WHERE game_id = ?");

    $mysqli_stmt->bind_param("si", $status, $game_id);

    $mysqli_stmt->execute();

    $connection->close();
}