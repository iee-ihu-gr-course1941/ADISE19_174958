<?php
require_once "entranceSystem.php";
require_once "joinSystem.php";
require_once "gameSystem.php";
require_once "../database/variables.php";

deleteStalePlayers();//delete players that are in betting or hitting status and haven't played for 2 minutes or more.

updateGames();

session_start();

$inputJSON = json_decode(file_get_contents('php://input'),TRUE);

$method = $_SERVER['REQUEST_METHOD'];

$request = explode('/', trim($_SERVER['PATH_INFO'],'/'));

$input = json_decode(file_get_contents('php://input'),true);

switch ($request[0]){
    case "signIn" :
        $user_name = $input["user_name"];
        $pass_word = $input["pass_word"];
        signIn($user_name,$pass_word);
        break;
    case "signUp" :
        $user_name = $input["user_name"];
        $pass_word = $input["pass_word"];
        signUp($user_name,$pass_word);
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
    default:
        http_response_code(404);
        exit();
}

/**
 * Deletes all players that have been unresponsive for 2 minute or more.
 * If the user that fired the request was one of the players that got deleted,his/her token is deleted from SESSION.
 */
function deleteStalePlayers(){


}

function isLogin(){
    if (!isset($_SESSION["user_name"])) {
        http_response_code(401);
        exit();
    }
}

function updateGames(){
    $connection = mysqli_connect(HOST, USER, PASSWORD,DATABASE);

    $selectGames = $connection->prepare("SELECT * FROM games ");

    $selectGames -> execute();

    $mysqli_result = $selectGames->get_result();

    while($row = $mysqli_result->fetch_assoc()){
        if($row["games_status"] == "initialized"){
            checkInitialized($row["game_id"]);
        }
    }

    $connection -> close();
}

function checkInitialized($game_id){
    $connection = mysqli_connect(HOST, USER, PASSWORD,DATABASE);

    $mysqli_stmt = $connection->prepare("SELECT nums_of_players FROM games WHERE game_id = ? ");

    $mysqli_stmt -> bind_param("i",$game_id);

    $mysqli_stmt -> execute();

    $result = $mysqli_stmt->get_result();

    if ($result->fetch_assoc()['num_of_players'] > 0) {
        changeStatusTo($game_id,"betting");
    }

    $connection -> close();
}

function checkBetting($game_id){

}

function changeStatusTo($game_id,$status){

}