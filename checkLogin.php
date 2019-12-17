<?php
if (!isset($_SESSION["started"])) {
    http_response_code("403");
}