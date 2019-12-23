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


function hands(){

}