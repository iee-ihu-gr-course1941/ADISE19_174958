<?php
if (!isset($_SESSION["user_name"])) {
    http_response_code("403");
}