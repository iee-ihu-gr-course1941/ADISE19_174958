<?php

/**
 * Destroys the session.
 */
function logout(){

    session_destroy();

    print "index.php";
}

/**
 * Sign ins the the user give the username and password.If no user found with these credentials,return an error(400 status code).
 * @param $user_name
 * @param $pass_word
 */
function signIn($user_name,$pass_word){
    require_once "../database/variables.php";

    $connection = mysqli_connect(HOST, USER, PASSWORD,DATABASE,null,SOCKET);

    $mysqli_stmt = $connection->prepare("SELECT * FROM my_users WHERE user_name = ? AND pass_word = ?");

    $mysqli_stmt->bind_param("ss", $user_name,$pass_word);

    $mysqli_stmt->execute();

    $result = $mysqli_stmt->get_result();

    if (mysqli_num_rows($result) == 1) {
        $_SESSION["user_name"] = $user_name;
        print "index.php";
    }else{
        http_response_code(400);
        print "Κωδικος ή/και ονομα χρηστη ειναι λαθος.";
    }

    $connection->close();
}

/**
 * Inserts new user into the storage.If user with the given username already exists,return an error(400 status code).
 * If the password doesn't have valid length,returns an error(400 status code).
 * @param $user_name
 * @param $pass_word
 */
function signUp($user_name,$pass_word){
    require_once "../database/variables.php";

    $connection = mysqli_connect(HOST, USER, PASSWORD,DATABASE,null,SOCKET);

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
}
