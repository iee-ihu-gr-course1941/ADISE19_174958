<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">

    <link rel="stylesheet" href="css/style.css"/>

    <!-- jQuery library -->
    <script src="js/jquery.js"></script>

    <!-- Popper JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>

    <!-- Latest compiled JavaScript -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>


</head>
<body>
<div class="container-fluid">
    <div class="row">
        <nav class="navbar navbar-expand-sm bg-dark w-100 navbar-dark">

            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#collapsibleNavbar">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="collapsibleNavbar">

                <ul class="navbar-nav w-100">
                    <?php
                    session_start();
                    if (isset($_SESSION["user_name"])) {
                        print "<li class='nav-item'><a href='#' class='nav-link'>".$_SESSION["user_name"]."</a></li>";
                        print "<li class='nav-item'><a href='#' class='nav-link' id='playButton'>Play</a></li>";
                        print "<li class='nav-item'><a href='#' class='nav-link'>Rank</a></li>";
                        print "<li class='nav-item'><a href='#' id='logoutBtn' class='nav-link'>Logout</a></li>";
                        print "<script src=\"js/logout.js\" ></script>";
                        print "<script src=\"js/joinGame.js\"></script>";
                    }else{
                        print "<li class='nav-item'><a href='signIn.php' class='nav-link'>Sign In</a></li>";
                        print "<li class='nav-item'><a href='signUp.php' class='nav-link'>Sign Up</a></li>";
                    }
                    ?>
                </ul>
            </div>

        </nav>
    </div>

<?php

?>
