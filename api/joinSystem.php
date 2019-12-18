<?php

require_once "../database/variables.php";

function play()
{
    session_start();

    $game = getGame();

    print json_encode($game,JSON_PRETTY_PRINT);
}

/**
 * Returns the current game the player participates in.
 */
function getGame()
{

    $game['token'] = getToken();

    $game['game'] = getGameOfUser();

    return $game;
}

/**
 * Checks if the user has token.If doesn't,creates new.
 * @return string token of the user.
 */
function getToken()
{
    if (!isset($_SESSION["user_name"])) {
        http_response_code(401);
        exit();
    }

    $connection = mysqli_connect(HOST, USER, PASSWORD, DATABASE);

    $selectToken = $connection->prepare("SELECT token FROM players WHERE user_name = ? ");

    $selectToken->bind_param("s", $_SESSION['user_name']);

    $selectToken->execute();

    $result = $selectToken->get_result();

    if ($result->num_rows == 0) {
        $token = create_token();
    } else {
        $token = $result->fetch_assoc()['token'];
    }

    $connection->close();

    return $token;
}

/**
 * Creates and returns new token.
 * If the insertion of token fails,exits with status error 500.
 * @return token
 */
function create_token()
{

    $connection = mysqli_connect(HOST, USER, PASSWORD, DATABASE);

    $insertToken = $connection->prepare("INSERT INTO players(user_name, game_id, token, last_action, player_status) VALUES (?,?,MD5(CONCAT(user_name,NOW())),?,?)");

    $game = getRandomGame();

    increasePlayers($game['game_id']);

    $user_name = $_SESSION["user_name"];

    $last_action = date('Y/m/d h:i:s a', time());

    switch ($game['games_status']) {
        case null :
            $player_status = "betting";
            break;
        default :
            $player_status = "waiting";
    }

    $insertToken->bind_param("siss", $user_name, $game['game_id'], $last_action, $player_status);

    if (!$insertToken->execute()) {
        http_response_code(500);

        exit();
    }


    $selectToken = $connection->prepare("SELECT token FROM players WHERE user_name = ?");
    $selectToken->bind_param("s", $user_name);
    $selectToken->execute();

    $token = $selectToken->get_result()->fetch_assoc()['token'];

    $connection->close();

    return $token;
}

/**
 * Returns the game.
 * @return array
 */
function getRandomGame()
{
    $connection = mysqli_connect(HOST, USER, PASSWORD, DATABASE);

    $mysqli_stmt = $connection->prepare("SELECT * FROM games WHERE nums_of_players < 4 LIMIT 1");

    $mysqli_stmt->execute();

    $result = $mysqli_stmt->get_result();

    $game = null;

    if ($result->num_rows == 0) {
        createNewGame();
        $game = getRandomGame();
    } else {
        $row = $result->fetch_assoc();
        $game = $row;
    }


    $connection->close();

    return $game;
}

/**
 * Creates new game.
 * @return array
 */
function createNewGame()
{
    $connection = mysqli_connect(HOST, USER, PASSWORD, DATABASE);

    $mysqli_stmt = $connection->prepare("INSERT INTO games(games_status, points, nums_of_players) VALUES('betting', 0, 0) ");

    $mysqli_stmt->execute();

    $connection -> close();

}

function getGameOfUser(){

    $connection = mysqli_connect(HOST, USER, PASSWORD, DATABASE);

    $mysqli_stmt = $connection->prepare("SELECT g.game_id
                                FROM games g INNER JOIN players p on g.game_id = p.game_id WHERE p.user_name = ?");

    $mysqli_stmt -> bind_param("s",$_SESSION['user_name']);

    $mysqli_stmt ->execute();

    $mysqli_result = $mysqli_stmt->get_result();

    return $mysqli_result -> fetch_assoc();
}

function increasePlayers($game_id){
    $connection = mysqli_connect(HOST, USER, PASSWORD, DATABASE);

    $mysqli_stmt = $connection->prepare("UPDATE games SET nums_of_players = nums_of_players + 1 WHERE game_id = ?");

    $mysqli_stmt -> bind_param("i",$game_id);

    if(!$mysqli_stmt -> execute()){
        http_response_code(500);
        exit();
    }

    $connection->close();
}
