<?php

function load($file_name){
    $file = null;

    switch ($file_name) {
        case 'signInPage':
            $file = fopen("files/signInAPI.php","r");
            break;
        case "signUpPage":
            $file = fopen("files/signUpAPI.php","r");
            break;
    }

    if ($file != null) {
        header("Content-Type:text/html");

        fpassthru($file);

        fclose($file);

    } else {
        header("Location: /blackjack/index.php",true,302);
        exit;
    }
}