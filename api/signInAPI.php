<?php
require_once "../database/variables.php";

$connection = mysqli_connect(HOST, USER, PASSWORD,DATABASE);

$inputJSON = file_get_contents('php://input');

$user = json_decode($inputJSON, TRUE);

$mysqli_stmt = $connection->prepare("SELECT * FROM my_users WHERE user_name = ? AND pass_word = ?");

$mysqli_stmt->bind_param("ss", $user["user_name"],$user["pass_word"]);

$mysqli_stmt->execute();

$result = $mysqli_stmt->get_result();

if (mysqli_num_rows($result) == 1) {
    session_start();
    $_SESSION["started"] = true;
    print "index.php";
}else{
    http_response_code(400);
    print "Κωδικος ή/και ονομα χρηστη ειναι λαθος.";
}

mysqli_close($connection);