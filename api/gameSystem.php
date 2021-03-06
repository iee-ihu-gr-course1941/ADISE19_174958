<?php

require_once "../database/variables.php";

/**
 * Returns the token of the sign in user.
 * If token is not assigned,returns an error(404 status code).
 */
function token()
{
    $connection = mysqli_connect(HOST, USER, PASSWORD, DATABASE,null,SOCKET);

    $selectToken = $connection->prepare("SELECT token FROM players WHERE user_name = ? ");

    $selectToken->bind_param("s", $_SESSION["user_name"]);

    $selectToken->execute();

    $mysqli_result = $selectToken->get_result();

    if ($mysqli_result->num_rows == 0) {
        $connection->close();
        http_response_code(404);
        exit();
    }


    print json_encode($mysqli_result->fetch_assoc()['token'], JSON_PRETTY_PRINT);

    $connection->close();
}

/**
 * Prints the game in json format as follows:
 * {
 *"status": <<Status of the game>>,
 *"players": <<An array of players in the given format:\
 *          [
 *              {
 *                  "username": <<Username of the first player>>,
 *                  "status": <<Status of the first player>>,
 *                  "points": <<Points of the first player>>,
 *                  "balance": <<Balance of the first player>>,
 *                  "cards": [<<An array of strings representing the urls to the images of the cards of the first player>>]
 *              }
 *          ],
 *"points": <<The points of the game>>,
 *"cards": [<<An array of strings represeting the urls to the images of the cards of the computer],
 *"user": <<An user that is requesting the game in the following format>>{
 *"username": <<Username of the user>>,
 *"status": <<Status of the user>>,
 *"balance": <<Balance of the user>>
 *}
 *}
 *
 */
function game()
{
    $connection = mysqli_connect(HOST, USER, PASSWORD, DATABASE,null,SOCKET);

     $token = getToken();

    $gameId = getPlayersGameId($token,$connection);

    $response = array();
    $game = getGame($gameId,$connection);
    switch($game['status']){
        case "players_turn":
            $response['status'] = "Player's Turn";
            break;
        case "computer_turn":
            $response["status"] = "Computer's Turn";
            break;
        case "end_game":
            $response["status"] = "End Of Game";
            break;
        default:
            $response['status'] = ucfirst($game['status']);
    }
    $response['players'] = getPlayers($gameId,$connection);
    $response['points'] = $game['points'];
    $response['cards'] = getGameCards($gameId,$connection);
    $response['user'] = getPlayer($token, $connection);

    $connection->close();

    print json_encode($response, JSON_PRETTY_PRINT);

}

/**
 * Returns a map array containing all the players of the game with the given game id.
 * @param $gameId
 * @param $connection
 * @return array
 */
function getPlayers($gameId,$connection)
{
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
            case "left_game":
                $player['status']= "Left The Game";
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

    return $players;
}

/**
 * Returns a map array containing information about the status and points of the game with the given id.
 * @param $gameId
 * @param $connection
 * @return mixed
 */
function getGame($gameId,$connection)
{

    $selectStatus = $connection->prepare("SELECT games_status as status ,points FROM games WHERE game_id = ?");

    $selectStatus->bind_param("i", $gameId);

    $selectStatus->execute();

    return $selectStatus->get_result()->fetch_assoc();
}

/**
 * Returns a map array containing urls to the images of the cards of the computer of the given game id.
 * @param $gameId
 * @param $connection
 * @return array
 */
function getGameCards($gameId,$connection)
{
    $computerHand = $connection->prepare("SELECT CONCAT('imgs/',image_name,'.png') as card FROM computer_hands ch INNER JOIN cards_images ci ON ci.card_color = ch.card_color AND ci.card_value = ch.card_value WHERE game_id = ?");
    $computerHand->bind_param("i", $gameId);
    $computerHand->execute();

    $cards = array();

    $computerCards = $computerHand->get_result();

    while ($card = $computerCards->fetch_assoc()) {
        array_push($cards, $card['card']);
    }

    return $cards;
}

/**
 * Returns the game id of the game to which the player with the given token belongs.
 * @param $token
 * @param $connection
 * @return mixed
 */
function getPlayersGameId($token, $connection)
{
    $mysqli_stmt = $connection->prepare("SELECT game_id FROM players WHERE token = ?");

    $mysqli_stmt->bind_param("s", $token);

    $mysqli_stmt->execute();

    return $mysqli_stmt->get_result()->fetch_assoc()['game_id'];
}

/**
 * Returns a map array containing the information of the player with the given token.
 * @param $token
 * @param $connection
 * @return mixed
 */
function getPlayer($token, $connection){
    $selectUser = $connection->prepare("SELECT p.user_name as username,player_status as status ,balance FROM players p INNER JOIN my_users mu on p.user_name = mu.user_name WHERE token = ? ");
    $selectUser->bind_param("s", $token);
    $selectUser->execute();

    return $selectUser->get_result()->fetch_assoc();
}

/**
 * Updates the balance and bet of the player betting using the given amount.If the player can't bet the given amount,returns an error(400 status code).
 * @param $amount
 */
function bet($amount){

    $connection = mysqli_connect(HOST, USER, PASSWORD, DATABASE,null,SOCKET);

    $token = getToken();

    if (!canBet($amount,$token,$connection)) {
        http_response_code(401);
        $connection->close();
        exit();
    }

    updateLastAction($token,$connection);

    $checkAmountAfterBet = $connection->prepare("SELECT balance - ? as balanceAfter FROM my_users u INNER JOIN players p on u.user_name = p.user_name WHERE token = ?");

    $checkAmountAfterBet->bind_param("is", $amount, $token);
    $checkAmountAfterBet->execute();

    if ( $checkAmountAfterBet->get_result()->fetch_assoc()['balanceAfter'] <= 0) {
        $connection->close();
        http_response_code(400);
        exit();
    }

    $updateBalance = $connection->prepare("UPDATE my_users u INNER JOIN players p ON u.user_name = p.user_name SET balance = balance - ?,player_status = 'done_betting' WHERE token = ? ");
    $updateBalance->bind_param("is", $amount, $token);
    $updateBalance->execute();

    $insertBet = $connection->prepare("INSERT INTO bets (token, amount) VALUES(?,?)");
    $insertBet->bind_param("si",$token,$amount);
    $insertBet->execute();

    $connection->close();
}

/**
 * Updates the status of the player to done_hitting
 */
function enough(){
    $token = getToken();

    $connection = mysqli_connect(HOST, USER, PASSWORD, DATABASE,null,SOCKET);

    if (!isInHittingStatus($token,$connection)) {
        $connection->close();
        http_response_code(401);
        exit();
    }

    updateLastAction($token,$connection);

    $updaterStatus = $connection->prepare("UPDATE players SET player_status = 'done_hitting' WHERE token = ?");
    $updaterStatus->bind_param("s",$token);
    $updaterStatus->execute();

    $connection->close();

}

/**
 * Gets a cards and inserts it into the hand of the player hitting.If the player can't hit,return an error(401 status code).
 * Updates points of the player and his/her status.If player reached 21 points ,changes his/her status to done_hitting;if overflow 21 points,changes
 * the status to overflow.
 */
function hit(){
    $token = getToken();

    $connection = mysqli_connect(HOST, USER, PASSWORD, DATABASE,null,SOCKET);

    if (!isInHittingStatus($token,$connection)) {
        http_response_code(401);
        $connection->close();
        exit();
    }

    updateLastAction($token,$connection);

    $gameId = getPlayersGameId($token,$connection);

    $card = getCard($gameId, $connection);

    $insertCard = $connection->prepare("INSERT INTO player_hands(token, card_color, card_value) VALUES(?,?,?)");
    $insertCard->bind_param("sss",$token,$card["card_color"],$card["card_value"]);
    $insertCard->execute();

    $selectPlayersPoints = $connection->prepare("SELECT points FROM players WHERE token = ?");
    $selectPlayersPoints->bind_param("s",$token);
    $selectPlayersPoints->execute();
    $playerPointsResult = $selectPlayersPoints->get_result();
    $playerPoints = $playerPointsResult->fetch_assoc()["points"];

    $points = getPoints($card, $playerPoints, $connection);

    $updatePoints = $connection->prepare("UPDATE players SET points = points + ?,player_status = CASE WHEN points > 21 THEN 'overflow' 
                        WHEN points = 21 THEN 'done_hitting' ELSE 'hitting' END WHERE token = ?");
    $updatePoints->bind_param("is",$points,$token);
    $updatePoints->execute();

    $connection->close();

}

/**
 * Returns true if the player with the given token can hit.
 * @param $token
 * @param $connection
 * @return bool
 */
function isInHittingStatus($token,$connection){

    $check = $connection->prepare("SELECT * FROM players WHERE token = ? AND player_status = 'hitting'");
    $check->bind_param("s", $token);
    $check->execute();
    $mysqli_result = $check->get_result();

    return $mysqli_result->num_rows !== 0;
}

/**
 * Returns true if the player with the given token can bet the given amount.
 * @param $amount
 * @param $token
 * @param $connection
 * @return bool
 */
function canBet($amount,$token,$connection)
{
    $question = $connection->prepare("SELECT * FROM players p INNER JOIN my_users u ON p.user_name = u.user_name WHERE token = ? AND player_status = 'betting' AND (balance - ?) >= 0");
    $question->bind_param("si", $token,$amount);
    $question->execute();

    return $question->get_result()->num_rows !== 0;
}

/**
 * Updates the last_action column of the players table for the player with the given token.
 * @param $token
 * @param $connection
 */
function updateLastAction($token,$connection){
    $mysqli_stmt = $connection->prepare("UPDATE players SET last_action = NOW() WHERE token = ?");

    $mysqli_stmt->bind_param("s",$token);

    $mysqli_stmt->execute();

}

/**
 * Return a map array containing a random card from the game cards.Updates the card taken an indicates that it has been taken.
 * @param $gameId
 * @param $connection
 * @return mixed
 */
function getCard($gameId,$connection){
    $numOfCardsLeft = $connection->prepare("SELECT COUNT(*) as cardsLeft FROM game_cards WHERE game_id = ? AND taken = false");
    $numOfCardsLeft->bind_param("i",$gameId);
    $numOfCardsLeft->execute();
    $cardsLeft = $numOfCardsLeft->get_result()->fetch_assoc()['cardsLeft'];

    $randomCardNum = rand(0, $cardsLeft);

    $randomCard = $connection->prepare("SELECT gc.card_color as card_color,gc.card_value as card_value,points FROM game_cards gc 
                                        INNER JOIN cards_points cp on gc.card_color = cp.card_color AND cp.card_value = gc.card_value 
                                        WHERE game_id = ? AND taken = false LIMIT 1 OFFSET ?");
    $randomCard->bind_param("ii",$gameId,$randomCardNum);
    $randomCard->execute();
    $card = $randomCard->get_result()->fetch_assoc();

    $updateCard = $connection->prepare("UPDATE game_cards SET taken = true WHERE game_id = ? AND card_value = ? AND card_color =?");
    $updateCard->bind_param("iss",$gameId,$card['card_value'],$card['card_color']);
    $updateCard->execute();

    return $card;
}

/**
 * Accepts the carda taken,current amount of points and returns the points the given card has.
 * @param $card
 * @param $currentPoints
 * @param $connection
 * @return int|mixed
 */
function getPoints($card, $currentPoints,$connection)
{
    $selectPointsOfCard = $connection->prepare("SELECT points FROM cards_points WHERE card_value = ? AND card_color = ? ");
    $selectPointsOfCard->bind_param("ss", $card["card_value"], $card["card_color"]);
    $selectPointsOfCard->execute();
    $pointsResult = $selectPointsOfCard->get_result();
    $points = $pointsResult->fetch_assoc()["points"];

    if ($points === null || $currentPoints === null) {
        http_send_status(500);
        exit();
    }

    if ($points === 11 && $currentPoints > 10) {
        $points = 1;
    }

    return $points;
}

/**
 * Returns the token that must be inside the request header by name TOKEN.If not found,return an error(400 status code).
 * @return mixed
 */
function getToken(){
    $token = apache_request_headers()["TOKEN"];

    if (!$token) {
        http_response_code(400);
        exit();
    }

    return $token;
}
