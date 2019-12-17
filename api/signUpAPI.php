<?php

require_once "../database/variables.php";

$connection = mysqli_connect(HOST, USER, PASSWORD,DATABASE);

$inputJSON = file_get_contents('php://input');

$user = json_decode($inputJSON, TRUE);

$user_name = $user['user_name'];

$pass_word = $user['pass_word'];

if(strlen($pass_word) < 8){
    http_response_code(400);
    print "The password must be at least 8 characters long.\n";
}

$mysqli_stmt = $connection->prepare("INSERT INTO my_users(user_name,pass_word) VALUES(?,?)");

$mysqli_stmt->bind_param("ss", $user_name, $pass_word);

$mysqli_stmt->execute();

foreach ($connection->error_list as $error){
    if ($error["sqlstate"] == 23000) {
        http_response_code(400);
        print "User with username $user_name already exists.";
        exit();
    }
}

print "signIn.php";

$connection->close();

