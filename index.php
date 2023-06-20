<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Methods: *");

include_once("config.php");
include_once("DB.php");
include_once("controllers\TodosContoller.php");
include_once("controllers\UsersController.php");

$db = new DB($db_config);
$db = $db->getConnection();

$method = $_SERVER["REQUEST_METHOD"];
$path = $_SERVER['REQUEST_URI'];
$path = explode('/', $path);

if ($path[2] === 'todos') {
    if ($method === 'GET') {
        if ($path[3] === 'getall') {
            if (!isset($path[4])) {
                TodosController::get($db);
            }
            else {
                $type = $path[4];
                TodosController::get($db, $type);
            }
        }
        else if ($path[3] === 'left') {
            TodosController::left($db);
        }
    }

    else if ($method === 'POST') {
        if ($path[3] === 'add') {
            TodosController::add($db, $_POST);
        }
    }

    else if ($method === 'DELETE') {
        if ($path[3] === 'delete') {
            if (isset($path[4]) && is_numeric($path[4])) {
                $todoId = $path[4];
                TodosController::delete($db, $todoId);
            }
            else {
                echo json_encode(["success" => false, "errorMessage" => ["Invalid id"]]);
            } 
        }
        else if ($path[3] === 'delete_completed') {
            TodosController::deleteCompleted($db);
        }
    }

    else if ($method === 'PATCH') {
        if ($path[3] === 'toggle_done') {
            if (isset($path[4]) && is_numeric($path[4])) {
                $todoId = $path[4];
                TodosController::toggleDone($db, $todoId);
            }
        }
    }
}
else if ($path[2] === 'users') {
    if ($method === 'POST') {
        if ($path[3] === 'signup') {
            UsersController::signup($db, $_POST);
        }
        else if ($path[3] === 'signin') {
            UsersController::signIn($db, $_POST);
        }
    }
    
    else if ($method === 'GET') {
        if ($path[3] === 'me') {
            UsersController::getMe($db);
        }
    }
}
