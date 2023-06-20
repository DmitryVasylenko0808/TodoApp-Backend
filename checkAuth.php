<?php
include_once("vendor\\firebase\php-jwt\src\BeforeValidException.php");
include_once("vendor\\firebase\php-jwt\src\ExpiredException.php");
include_once("vendor\\firebase\php-jwt\src\SignatureInvalidException.php");
include_once("vendor\\firebase\php-jwt\src\JWT.php");
include_once("vendor\\firebase\php-jwt\src\Key.php");
use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

function checkAuth() {
    $headers = getallheaders();
    $token = explode(' ', $headers['Authorization'])[1];
    
    if ($token) {
        try {
            include "config.php";
            $decoded = JWT::decode($token, new Key($key, 'HS256'));

            return $decoded->userId;
        }
        catch (Exception $err) {
            return null;
        }
    }
    else {
        return null;
    }
}