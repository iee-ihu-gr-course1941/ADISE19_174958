<?php

require_once "../database/variables.php";

function join_game()
{
    findGame();
}


function findGame(){
    assignToken();
}


function assignToken(){
    $connection = mysqli_connect(HOST, USER, PASSWORD, DATABASE);

    $selectToken = $connection->prepare("SELECT token FROM players WHERE user_name = ? ");

    $selectToken->bind_param("s", $_SESSION['user_name']);

    $selectToken->execute();

    $result = $selectToken->get_result();

    if ($result->num_rows == 0) {
        create_token();
    }

    $connection->close();

}


function create_token(){
    $connection = mysqli_connect(HOST, USER, PASSWORD, DATABASE);

    $insertToken = $connection->prepare("INSERT INTO players(user_name, game_id, token, player_status) VALUES (?,?,MD5(CONCAT(user_name,NOW())),?)");

    $game = getRandomGame();

    increasePlayers($game['game_id']);

    $user_name = $_SESSION["user_name"];

    switch ($game['games_status']) {
        case 'initialized' :
            $player_status = "betting";
            break;
        default :
            $player_status = "waiting";
    }

    $insertToken->bind_param("sis", $user_name, $game['game_id'], $player_status);

    $insertToken -> execute();

    $connection->close();
}


function getRandomGame()
{
    $connection = mysqli_connect(HOST, USER, PASSWORD, DATABASE);

    $mysqli_stmt = $connection->prepare("SELECT * FROM games WHERE nums_of_players < 3 LIMIT 1");

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

function createNewGame()
{
    $connection = mysqli_connect(HOST, USER, PASSWORD, DATABASE);

    $mysqli_stmt = $connection->prepare("INSERT INTO games(games_status, points, nums_of_players) VALUES('initialized', 0, 0) ");

    $mysqli_stmt->execute();

    $connection -> close();

}

function increasePlayers($game_id){
    $connection = mysqli_connect(HOST, USER, PASSWORD, DATABASE);

    $mysqli_stmt = $connection->prepare("UPDATE games SET nums_of_players = nums_of_players + 1 WHERE game_id = ?");

    $mysqli_stmt -> bind_param("i",$game_id);

    if(!$mysqli_stmt -> execute()){
//        foreach ($connection->error_list as $error) {
//            print_r($error);
//        }
        http_response_code(500);
        exit();
    }

    $connection->close();
}
