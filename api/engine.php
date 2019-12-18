<?php
require_once "entranceSystem.php";
require_once "joinSystem.php";

deleteStalePlayers();//delete players that are in betting or hitting status and haven't played for 2 minutes or more.

session_start();

if (!isset($_SESSION["user_name"])) {
    http_response_code(401);
    exit();
}

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
        join_game();
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
    require_once "../database/variables.php";

    $connection = mysqli_connect(HOST, USER, PASSWORD,DATABASE);

    $currentPlayerIsStale = $connection
        ->prepare("SELECT * FROM players WHERE TIMESTAMPDIFF(MINUTE,last_action,NOW()) >= 2 AND (player_status = 'betting' || player_status = 'hitting') AND user_name = ? ");
    $currentPlayerIsStale->bind_param("s",$_SESSION["user_name"]);

    $deleteStalePlayers = $connection
        ->prepare("DELETE FROM players WHERE TIMESTAMPDIFF(MINUTE,last_action,NOW()) >= 2 AND (player_status = 'betting' || player_status = 'hitting') ");

    $deleteStalePlayers->execute();

    $currentPlayerIsStale->execute();

    if ($currentPlayerIsStale->num_rows() == 1) {
        unset($_SESSION['token']);
    }

    $connection->close();
}
