<?php

require_once "../database/variables.php";

function token(){
    $connection = mysqli_connect(HOST, USER, PASSWORD, DATABASE);

    $selectToken = $connection->prepare("SELECT token FROM players WHERE user_name = ? ");

    $selectToken->bind_param("s", $_SESSION["user_name"]);

    $selectToken -> execute();

    $mysqli_result = $selectToken->get_result();

    if ($mysqli_result->num_rows == 0) {
        http_response_code(404);
        exit();
    }

    print json_encode($mysqli_result->fetch_assoc(),JSON_PRETTY_PRINT);

}


function playersHands(){
    $gameId = getUsersGameId();

    $connection = mysqli_connect(HOST, USER, PASSWORD, DATABASE);

    $selectPlayersHands = $connection -> prepare("SELECT p.user_name,ph.card_color,ph.card_value FROM players p INNER JOIN player_hands ph on p.user_name = ph.user_name INNER JOIN cards_images ci on ph.card_color = ci.card_color AND ph.card_value = ci.card_value WHERE game_id = ?");

    $selectPlayersHands -> bind_param("i",$gameId);

    $selectPlayersHands -> execute();

    $mysqli_result = $selectPlayersHands->get_result();

    $rows[] = array();

    $counter = 0;

    while ($row = $mysqli_result->fetch_assoc()) {
        $rows[$counter] = $row;
        $counter++;
    }

    print json_encode($rows, JSON_PRETTY_PRINT);




    $connection -> close();

}


function getUsersGameId(){
    if (!isset($_SERVER['HTTP_TOKEN'])) {
        http_response_code(400);
        exit();
    }
    $token = $_SERVER['HTTP_TOKEN'];

    $connection = mysqli_connect(HOST, USER, PASSWORD, DATABASE);

    $mysqli_stmt = $connection->prepare("SELECT game_id FROM players WHERE token = ?");

    $mysqli_stmt -> bind_param("s",$token);

    $mysqli_stmt -> execute();

    $game_id = $mysqli_stmt->get_result()->fetch_assoc()['game_id'];

    $connection -> close();

    return $game_id;
}