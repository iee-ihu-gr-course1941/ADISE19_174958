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
    $token = getToken();
    $gameId = getUsersGameId($token);

    $response = array();
    $game = getGame($gameId);
    switch($game['status']){
        case "players_turn":
            $response['status'] = "Player's Turn";
            break;
        case "computer_turn":
            $response["status"] = "Computer's Turn";
            break;
        default:
            $response['status'] = ucfirst($game['status']);
    }
    $response['players'] = getPlayers($gameId);
    $response['points'] = $game['points'];
    $response['cards'] = getGameCards($gameId);

    print json_encode($response, JSON_PRETTY_PRINT);
}

function getPlayers($gameId)
{
    $connection = mysqli_connect(HOST, USER, PASSWORD, DATABASE);

    $players = array();

    $playersInfo = $connection->prepare("SELECT p.user_name as username,player_status as status,points ,balance,token FROM players p INNER JOIN my_users u ON p.user_name = u.user_name WHERE game_id = ?");
    $playersInfo->bind_param("i", $gameId);
    $playersInfo->execute();
    $result = $playersInfo->get_result();

    $playerCards = $connection->prepare("SELECT CONCAT('imgs/',image_name,'.png' )  AS card FROM player_hands ph INNER JOIN cards_images ci ON ci.card_color = ph.card_color AND ci.card_value = ph.card_value WHERE token = ?");

    while ($row = $result->fetch_assoc()) {
        $player = array();
        $cards = array();
        $player['username'] = $row['username'];
        switch($row['status']){
            case "done_betting":
                $player['status'] = "Done Betting";
                break;
            case "done_hitting":
                $player['status'] = "Done Hitting";
                break;
            default:
                $player["status"] = ucfirst($row["status"]);
        }
        $player['points'] = $row['points'];
        $player['balance'] = $row['balance'];
        $playerCards->bind_param("s", $row['token']);
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

function getUsersGameId($token)
{
    $connection = mysqli_connect(HOST, USER, PASSWORD, DATABASE);

    $mysqli_stmt = $connection->prepare("SELECT game_id FROM players WHERE token = ?");

    $mysqli_stmt->bind_param("s", $token);

    $mysqli_stmt->execute();

    $game_id = $mysqli_stmt->get_result()->fetch_assoc()['game_id'];

    $connection->close();

    return $game_id;
}

function canBet($amount,$token)
{

    $connection = mysqli_connect(HOST, USER, PASSWORD, DATABASE);

    $question = $connection->prepare("SELECT * FROM players p INNER JOIN my_users u ON p.user_name = u.user_name WHERE token = ? AND player_status = 'betting' AND (balance - ?) >= 0");
    $question->bind_param("si", $token,$amount);
    $question->execute();

    $answer= $question->get_result()->num_rows !== 0;

    $connection->close();

    return $answer;
}

function user(){
    $token = getToken();

    $connection = mysqli_connect(HOST, USER, PASSWORD, DATABASE);

    $selectUser = $connection->prepare("SELECT p.user_name as username ,points,balance,player_status as status FROM players p INNER JOIN my_users mu on p.user_name = mu.user_name WHERE token = ? ");
    $selectUser->bind_param("s", $token);
    $selectUser->execute();

    $user = $selectUser->get_result()->fetch_assoc();

    $connection->close();

    print json_encode($user, JSON_PRETTY_PRINT);

}

function bet($amount){
    $token = getToken();

    $connection = mysqli_connect(HOST, USER, PASSWORD, DATABASE);

    $connection->begin_transaction(MYSQLI_TRANS_START_READ_WRITE);

    if (!canBet($amount,$token)) {
        http_response_code(401);
        $connection->close();
        exit();
    }

    updateLastAction($token);

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

function updateLastAction($token){
    $connection = mysqli_connect(HOST, USER, PASSWORD, DATABASE);

    $mysqli_stmt = $connection->prepare("UPDATE players SET last_action = NOW() WHERE token = ?");

    $mysqli_stmt->bind_param("s",$token);

    $mysqli_stmt->execute();

    $connection->close();

}

function enough(){
    $token = getToken();

    $connection = mysqli_connect(HOST, USER, PASSWORD, DATABASE);

    $connection->begin_transaction(MYSQLI_TRANS_START_READ_WRITE);

    if (!isInHittingStatus($token)) {
        $connection->close();
        http_response_code(401);
        exit();
    }

    updateLastAction($token);

    $updaterStatus = $connection->prepare("UPDATE players SET player_status = 'done_hitting' WHERE token = ?");
    $updaterStatus->bind_param("s",$token);
    $updaterStatus->execute();

    $connection->commit();

    $connection->close();

}

function hit(){
    $token = getToken();


    $connection = mysqli_connect(HOST, USER, PASSWORD, DATABASE);

    $connection->begin_transaction(MYSQLI_TRANS_START_READ_WRITE);

    if (!isInHittingStatus($token)) {
        http_response_code(401);
        $connection->close();
        exit();
    }

    updateLastAction($token);

    $gameId = getUsersGameId($token);

    $numOfCardsLeft = $connection->prepare("SELECT COUNT(*) as cardsLeft FROM game_cards WHERE game_id = ? AND taken = false");
    $numOfCardsLeft->bind_param("i",$gameId);
    $numOfCardsLeft->execute();
    $cardsLeft = $numOfCardsLeft->get_result()->fetch_assoc()['cardsLeft'];

    $randomCardNum = rand(0, $cardsLeft);

    $randomCard = $connection->prepare("SELECT card_color,card_value FROM game_cards WHERE game_id = ? LIMIT 1 OFFSET ?");
    $randomCard->bind_param("ii",$gameId,$randomCardNum);
    $randomCard->execute();
    $card = $randomCard->get_result()->fetch_assoc();

    $updateCard = $connection->prepare("UPDATE game_cards SET taken = true WHERE game_id = ? AND card_value = ? AND card_color =?");
    $updateCard->bind_param("iss",$gameId,$card['card_value'],$card['card_color']);
    $updateCard->execute();

    $insertCard = $connection->prepare("INSERT INTO player_hands(token, card_color, card_value) VALUES(?,?,?)");
    $insertCard->bind_param("sss",$token,$card["card_color"],$card["card_value"]);
    $insertCard->execute();

    $updatePoints = $connection->prepare("UPDATE players SET points = points + (SELECT cp.points FROM cards_points cp WHERE card_value = ? AND card_color = ?),
                        player_status = CASE WHEN points > 21 THEN 'overflow' ELSE 'hitting' END WHERE token = ?");
    $updatePoints->bind_param("sss",$card['card_value'],$card['card_color'],$token);
    $updatePoints->execute();

    $connection->commit();

    $connection->close();

}

function isInHittingStatus($token){
    $connection = mysqli_connect(HOST, USER, PASSWORD, DATABASE);
    $check = $connection->prepare("SELECT * FROM players WHERE token = ? AND player_status = 'hitting'");
    $check->bind_param("s", $token);
    $check->execute();
    $mysqli_result = $check->get_result();
    return $mysqli_result->num_rows !== 0;
}

function getToken(){
    $token = apache_request_headers()["TOKEN"];

    if (!$token) {
        http_response_code(400);
        exit();
    }

    return $token;
}


