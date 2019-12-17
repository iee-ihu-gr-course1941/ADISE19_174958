<?php
require_once "functions/loadFunctions.php";

$method = $_SERVER['REQUEST_METHOD'];

$request = explode("/",trim($_SERVER['PATH_INFO'],"/"));

load($request[0]);
