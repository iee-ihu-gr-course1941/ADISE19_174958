<?php

require_once "../database/variables.php";

function token()
{
    $connection = mysqli_connect(HOST, USER, PASSWORD, DATABASE);

    $selectToken = $connection->prepare("SELECT token,user_name FROM players WHERE user_name = ? ");

    $selectToken->bind_param("s", $_SESSION["user_name"]);

    $selectToken->execute();

    $mysqli_result = $selectToken->get_result();

    if ($mysqli_result->num_rows == 0) {
        http_response_code(404);
        exit();
    }

    print json_encode($mysqli_result->fetch_assoc(), JSON_PRETTY_PRINT);

    $connection -> close();
}


function playersHands()
{
    $gameId = getUsersGameId();

    $connection = mysqli_connect(HOST, USER, PASSWORD, DATABASE);

    $selectPlayersHands = $connection->prepare("SELECT p.user_name,ph.card_color,ph.card_value FROM players p LEFT JOIN player_hands ph on p.user_name = ph.user_name INNER JOIN cards_images ci on ph.card_color = ci.card_color AND ph.card_value = ci.card_value WHERE game_id = ?");

    $selectPlayersHands->bind_param("i", $gameId);

    $selectPlayersHands->execute();

    $mysqli_result = $selectPlayersHands->get_result();

    $rows[] = array();

    $counter = 0;

    while ($row = $mysqli_result->fetch_assoc()) {
        $rows[$counter] = $row;
        $counter++;
    }

    print json_encode($rows, JSON_PRETTY_PRINT);


    $connection->close();

}

function gameStatus()
{
    $gameId = getUsersGameId();

    $connection = mysqli_connect(HOST, USER, PASSWORD, DATABASE);

    $selectStatus = $connection->prepare("SELECT games_status FROM games WHERE game_id = ?");
    $selectStatus->bind_param("i", $gameId);

    $selectStatus->execute();

    $mysqli_result = $selectStatus->get_result();

    print json_encode($mysqli_result->fetch_assoc(),JSON_PRETTY_PRINT);

    $connection->close();
}

function players(){
    $gameId = getUsersGameId();

    $connection = mysqli_connect(HOST, USER, PASSWORD, DATABASE);

    $mysqli_stmt = $connection->prepare("SELECT u.user_name,u.balance,p.player_status,p.points FROM players p INNER JOIN my_users u ON p.user_name = u.user_name  WHERE game_id = ?");

    $mysqli_stmt -> bind_param("i",$gameId);

    $mysqli_stmt->execute();

    $result = $mysqli_stmt->get_result();

    $rows[] = array();

    $counter = 0;

    while ($row = $result->fetch_assoc()) {
        $rows[$counter] = $row;
        $counter++;
    }

    print json_encode($rows, JSON_PRETTY_PRINT);

    $connection->close();
}

function getUsersGameId()
{
    $token = apache_request_headers()['TOKEN'];

    if (!$token) {
        http_response_code(400);
        exit();
    }
    $connection = mysqli_connect(HOST, USER, PASSWORD, DATABASE);

    $mysqli_stmt = $connection->prepare("SELECT game_id FROM players WHERE token = ?");

    $mysqli_stmt->bind_param("s", $token);

    $mysqli_stmt->execute();

    $game_id = $mysqli_stmt->get_result()->fetch_assoc()['game_id'];

    $connection->close();

    return $game_id;
}