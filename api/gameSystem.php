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

function game(){
    $gameId = getUsersGameId();

    $response = array();
    $game = getGame($gameId);
    $response['_status'] = $game['status'];
    $response['_players'] = getPlayers($gameId);
    $response['_points'] = $game['points'];
    $response['_cards'] = getGameCards($gameId);
}

function getPlayers($gameId){
    $connection = mysqli_connect(HOST, USER, PASSWORD, DATABASE);

    $players = array();

    $playersInfo = $connection->prepare("SELECT p.user_name as _username,player_status as _status,points as _points ,balance as _balance FROM players p INNER JOIN my_users u ON p.user_name = u.user_name WHERE game_id = ?");
    $playersInfo->bind_param("i", $gameId);
    $playersInfo->execute();
    $result = $playersInfo->get_result();

    $playerCards = $connection->prepare("SELECT 'imgs/'||image_name||'.png' AS card FROM player_hands ph INNER JOIN cards_images ci ON ci.card_color = ph.card_color AND ci.card_value = ph.card_value WHERE user_name = ?");

    while ($row = $result->fetch_assoc()) {
        $player = array();
        $cards = array();
        $player['_username'] = $row['_username'];
        $player['_status'] = $row['_status'];
        $player['_points'] = $row['_points'];
        $player['_balance'] = $row['_balance'];
        $playerCards->bind_param("s", $player['_username']);
        $playerCards->execute();
        $cardsResult = $playerCards->get_result();

        while ($card = $cardsResult->fetch_assoc()) {
            array_push($cards, $card['card']);
        }

        $player['cards'] = $cards;

        array_push($players, $player);
    }

    $connection->close();

    return $players;
}

function getGame($gameId){
    $connection = mysqli_connect(HOST, USER, PASSWORD, DATABASE);

    $selectStatus = $connection->prepare("SELECT games_status as status ,points FROM games WHERE game_id = ?");

    $selectStatus->bind_param("i", $gameId);

    $selectStatus->execute();

    $game = $selectStatus->get_result()->fetch_assoc();

    $connection->close();

    return $game;
}

function getGameCards($gameId){
    $connection = mysqli_connect(HOST, USER, PASSWORD, DATABASE);
    $computerHand = $connection->prepare("SELECT 'imgs/'||image_name||'.png' as card FROM computer_hands ch INNER JOIN cards_images ci ON ci.card_color = ch.card_color AND ci.card_value = ch.card_value WHERE game_id = ?");
    $computerHand->bind_param("i", $gameId);
    $computerHand -> execute();

    $cards = array();

    $computerCards = $computerHand->get_result();

    while ($card = $computerCards->fetch_assoc()) {
        array_push($cards, $card['card']);
    }

    return $cards;
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