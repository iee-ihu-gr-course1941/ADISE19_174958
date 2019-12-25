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

    $connection->close();
}

function game()
{
    $gameId = getUsersGameId();

    $response = array();
    $game = getGame($gameId);
    $response['status'] = $game['status'];
    $response['players'] = getPlayers($gameId);
    $response['points'] = $game['points'];
    $response['cards'] = getGameCards($gameId);

    print json_encode($response, JSON_PRETTY_PRINT);
}

function getPlayers($gameId)
{
    $connection = mysqli_connect(HOST, USER, PASSWORD, DATABASE);

    $players = array();

    $playersInfo = $connection->prepare("SELECT p.user_name as username,player_status as status,points ,balance FROM players p INNER JOIN my_users u ON p.user_name = u.user_name WHERE game_id = ?");
    $playersInfo->bind_param("i", $gameId);
    $playersInfo->execute();
    $result = $playersInfo->get_result();

    $playerCards = $connection->prepare("SELECT 'imgs/'||image_name||'.png' AS card FROM player_hands ph INNER JOIN cards_images ci ON ci.card_color = ph.card_color AND ci.card_value = ph.card_value WHERE user_name = ?");

    while ($row = $result->fetch_assoc()) {
        $player = array();
        $cards = array();
        $player['username'] = $row['username'];
        $player['status'] = $row['status'];
        $player['points'] = $row['points'];
        $player['balance'] = $row['balance'];
        $playerCards->bind_param("s", $player['username']);
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

function getGame($gameId)
{
    $connection = mysqli_connect(HOST, USER, PASSWORD, DATABASE);

    $selectStatus = $connection->prepare("SELECT games_status as status ,points FROM games WHERE game_id = ?");

    $selectStatus->bind_param("i", $gameId);

    $selectStatus->execute();

    $game = $selectStatus->get_result()->fetch_assoc();

    $connection->close();

    return $game;
}

function getGameCards($gameId)
{
    $connection = mysqli_connect(HOST, USER, PASSWORD, DATABASE);
    $computerHand = $connection->prepare("SELECT 'imgs/'||image_name||'.png' as card FROM computer_hands ch INNER JOIN cards_images ci ON ci.card_color = ch.card_color AND ci.card_value = ch.card_value WHERE game_id = ?");
    $computerHand->bind_param("i", $gameId);
    $computerHand->execute();

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

function canBet()
{
    $token = apache_request_headers()['TOKEN'];

    if (!$token) {
        http_response_code(400);
        exit();
    }

    $answer = array();

    $connection = mysqli_connect(HOST, USER, PASSWORD, DATABASE);

    $question = $connection->prepare("SELECT * FROM players WHERE token = ? AND player_status = 'betting'");
    $question->bind_param("s", $token);
    $question->execute();

    $answer['answer'] = $question->get_result()->num_rows !== 0;

    $connection->close();

    print json_encode($answer, JSON_PRETTY_PRINT);

    return $answer;
}

function user(){
    $token = apache_request_headers()['TOKEN'];

    if (!$token) {
        http_response_code(400);
        exit();
    }

    $connection = mysqli_connect(HOST, USER, PASSWORD, DATABASE);

    $selectUser = $connection->prepare("SELECT p.user_name as username ,points,balance FROM players p INNER JOIN my_users mu on p.user_name = mu.user_name WHERE token = ? ");
    $selectUser->bind_param("s", $token);
    $selectUser->execute();

    $user = $selectUser->get_result()->fetch_assoc();

    $connection->close();

    print json_encode($user, JSON_PRETTY_PRINT);

}

function bet($amount){
    $token = apache_request_headers()['TOKEN'];

    if (!$token) {
        http_response_code(400);
        exit();
    }

    if ($amount <= 0) {
        exit();
    }

    $connection = mysqli_connect(HOST, USER, PASSWORD, DATABASE);

    $connection->begin_transaction(MYSQLI_TRANS_START_READ_WRITE);

    $canBet = canBet();

    if (!$canBet['answer'] === true) {
        http_response_code(401);
    }

    $checkAmountAfterBet = $connection->prepare("SELECT balance - ? as balanceAfter FROM my_users u INNER JOIN players p on u.user_name = p.user_name WHERE token = ?");

    $checkAmountAfterBet->bind_param("is", $amount, $token);
    $checkAmountAfterBet->execute();

    if ( $checkAmountAfterBet->get_result()->fetch_assoc()['balanceAfter'] <= 0) {
        http_response_code(400);
        exit();
    }

    $updateBalance = $connection->prepare("UPDATE my_users u INNER JOIN players p ON u.user_name = p.user_name SET balance = balance - ?,player_status = 'done_betting' WHERE token = ? ");
    $updateBalance->bind_param("is", $amount, $token);
    $updateBalance->execute();

    $connection->commit();

    $connection->close();
}