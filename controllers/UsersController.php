<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Methods: *");

include_once("checkAuth.php");
include_once("vendor\\firebase\php-jwt\src\BeforeValidException.php");
include_once("vendor\\firebase\php-jwt\src\ExpiredException.php");
include_once("vendor\\firebase\php-jwt\src\SignatureInvalidException.php");
include_once("vendor\\firebase\php-jwt\src\JWT.php");
include_once("vendor\\firebase\php-jwt\src\Key.php");
use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

class UsersController {
    static public function signUp($db, $data) {
        try {
            if (strlen(trim($data['login'])) < 4) {
                http_response_code(400);
                echo json_encode(["success" => false, "errorMessage" => "Login must have more than 4 characters"]);
                return;
            }

            if (strlen(trim($data['password'])) < 8) {
                http_response_code(400);
                echo json_encode(["success" => false, "errorMessage" => "Password must have more than 7 characters"]);
                return;
            }

            if ($data['password'] !== $data['passwordRepeat']) {
                http_response_code(400);
                echo json_encode(["success" => false, "errorMessage" => "Passwords don't match"]);
                return;
            }

            $user = self::isExistUser($db, $data['login']);
            if ($user) {
                http_response_code(400);
                echo json_encode(["success" => false, "errorMessage" => "This login is already exists"]);
                return;
            }

            $sql = "INSERT INTO users (login, passwordHash) VALUES (?, ?)";

            $password_hash = password_hash($data['password'], PASSWORD_BCRYPT);

            $stmt = $db->prepare($sql);
            $stmt->execute([$data['login'], $password_hash]);

            http_response_code(201);
            echo json_encode(["success" => true, "message" => "Registration OK"]);
        }
        catch (Exception $err) {
            http_response_code(500);
            echo json_encode(["success" => false, "errorMessage" => "Server error"]);
        }
    }

    static public function signIn($db, $data) {
        try {
            $user = self::isExistUser($db, $data['login']);
            if (!$user) {
                http_response_code(404);
                echo json_encode(["success" => false, "errorMessage" => "User is not found"]);
                return;
            }

            if (!password_verify($data['password'], $user['passwordHash'])) {
                http_response_code(401);
                echo json_encode(["success" => false, "errorMessage" => "Invalid login or password"]);
                return;
            }

            include "config.php";
            $token = [
                "iss" => $iss,
                "aud" => $aud,
                "iat" => $iat,
                "nbf" => $nbf,
                "userId" => $user['id']
            ];
            $jwt = JWT::encode($token, $key, 'HS256');

            echo json_encode(["success" => true, "token" => $jwt]);
        }
        catch (Exception $err) {
            http_response_code(500);
            echo json_encode(["success" => false, "errorMessage" => "Server error"]);
        }
    }

    static public function isExistUser($db, $login) {
        try {
            $sql = "SELECT * FROM users WHERE login = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$login]);

            if ($stmt->rowCount() === 0) {
                return null;
            }

            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            return $user;
        }
        catch (Exception $err) {
            http_response_code(500);
            echo json_encode(["success" => false, "errorMessage" => "Server error"]);
        }
    }

    static public function getMe($db) {
        try {
            $userId = checkAuth();

            if (!$userId) {
                http_response_code(401);
                echo json_encode(["success" => false, "errorMessage" => "Access denied"]);
                return;
            }

            $sql = "SELECT login FROM users WHERE id = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$userId]);

            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            echo json_encode(["success" => true, "userLogin" => $user["login"]]);
        }
        catch (Exception $err) {
            http_response_code(500);
            echo json_encode(["success" => false, "errorMessage" => "Server error"]);
        }
    }
}