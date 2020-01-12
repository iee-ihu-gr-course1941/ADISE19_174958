<?php

require_once "../database/variables.php";

function join_game()
{
    findGame();
    print "play.php";
}

/**
 * Assigns new token(if player doesn't participate in any game) to the user,which is identical to the user becoming a player.
 */
function findGame(){
    assignToken();
}

/**
 * Checks if user has token.If doesn't,assigns new token to the user.
 */
function assignToken(){
    $connection = mysqli_connect(HOST, USER, PASSWORD, DATABASE,null,SOCKET);

    $selectToken = $connection->prepare("SELECT token FROM players WHERE user_name = ? ");

    $selectToken->bind_param("s", $_SESSION['user_name']);

    $selectToken->execute();

    $result = $selectToken->get_result();

    if ($result->num_rows == 0) {
        create_token();
    }

    $connection->close();

}

/**
 * Creates new token for the user and inserts him/her into a randomly found game.Specifically,if game with empty seats exists,inserts the player
 * into this game;otherwise,created new game and inserts the user into the newly created game.
 */
function create_token(){
    $connection = mysqli_connect(HOST, USER, PASSWORD, DATABASE,null,SOCKET);

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

/**
 * @return array a random game that has empty seats or a newly created game.
 */
function getRandomGame()
{
    $connection = mysqli_connect(HOST, USER, PASSWORD, DATABASE,null,SOCKET);

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

/**
 * Creates new game with 0 points,0 players and in initialized status.
 */
function createNewGame()
{
    $connection = mysqli_connect(HOST, USER, PASSWORD, DATABASE,null,SOCKET);

    $mysqli_stmt = $connection->prepare("INSERT INTO games(games_status, points, nums_of_players) VALUES('initialized', 0, 0) ");

    $mysqli_stmt->execute();

    insertCards($mysqli_stmt->insert_id);

    $connection -> close();

}

/**
 * Takes cards from cards table and inserts them into the game cards table for the given game.
 * @param $gameID the game id of the game.
 */
function insertCards($gameID){
    $connection = mysqli_connect(HOST, USER, PASSWORD, DATABASE,null,SOCKET);


    $selectCards = $connection->prepare("SELECT * FROM cards ");
    $insertCard = $connection->prepare("INSERT INTO game_cards(card_color, card_value, game_id, taken) VALUES(?,?,?,false)");

    $selectCards->execute();

    $cards = $selectCards->get_result();

    while ($card = $cards->fetch_assoc()) {
        $insertCard->bind_param("ssi", $card['card_color'], $card['card_value'], $gameID);
        $insertCard->execute();
    }

    $connection->close();

}

/**
 * Increases the number of players for the game wit the given game id by one.
 * @param $game_id
 */
function increasePlayers($game_id){
    $connection = mysqli_connect(HOST, USER, PASSWORD, DATABASE,null,SOCKET);

    $mysqli_stmt = $connection->prepare("UPDATE games SET nums_of_players = nums_of_players + 1 WHERE game_id = ?");

    $mysqli_stmt -> bind_param("i",$game_id);

    if(!$mysqli_stmt -> execute()){
        http_response_code(500);
        exit();
    }

 
  $connection->close();
}
