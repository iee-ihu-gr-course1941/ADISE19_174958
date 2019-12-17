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


    <title>Title</title>
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
                    <li id='#special' class="nav-item"><a href="index.php" class="nav-link">Home</a></li>
                    <?php
                    session_start();
                    if (isset($_SESSION["started"])) {
                        print "<li class='nav-item'><a href='#' class='nav-link'>My Profile</a></li>";
                        print "<li class='nav-item'><a href='#' class='nav-link'>Play</a></li>";
                        print "<li class='nav-item'><a href='#' class='nav-link'>Rank</a></li>";
                        print "<li class='nav-item'><a href='#' id='logoutBtn' class='nav-link'>Logout</a></li>";
                    }else{
                        print "<li class='nav-item'><a href='signIn.php' class='nav-link'>Sign In</a></li>";
                        print "<li class='nav-item'><a href='signUp.php' class='nav-link'>Sign Up</a></li>";
                    }
                    ?>
                </ul>
            </div>

        </nav>
    </div>

    <script src="js/logout.js" ></script>
<?php

?>
