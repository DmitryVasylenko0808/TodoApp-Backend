<?php
include_once("checkAuth.php");

class TodosController {
    static public function get($db, $type = 'all') {
        try {
            $userId = checkAuth(); 

            if (!$userId) {
                http_response_code(401);
                echo json_encode(["success" => false, "errorMessage" => "Access denied"]);
                return;
            }

            $sql = "SELECT todoitems.id, title, done FROM todoitems LEFT JOIN users ON todoitems.user_id = users.id WHERE users.id = ?";
            if ($type !== 'all') {
                $sql .= " AND done = ?";
            }
            
            $stmt = $db->prepare($sql);
            if ($type === 'all') {
                $stmt->execute([$userId]);
            }
            else if ($type === 'active') {
                $stmt->execute([$userId, 0]);
            }
            else if ($type === 'completed') {
                $stmt->execute([$userId, 1]);
            }

            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(["success" => true, "todos" => $data]);
        }
        catch (Exception $err) {
            http_response_code(500);
            echo json_encode(["success" => false, "errorMessage" => "Server error"]);
        }
    }

    static public function add($db, $todo) {
        try {
            $userId = checkAuth();

            if (!$userId) {
                http_response_code(401);
                echo json_encode(["success" => false, "errorMessage" => "Access denied"]);
                return;
            }

            if (strlen(trim($todo['title'])) === 0) {
                http_response_code(400);
                echo json_encode(["success" => false, "errorMessage" => "Text must have more than 0 characters"]);
                return;
            }

            $sql = "INSERT INTO todoitems (title, done, user_id) VALUES (?, ?, ?)";
            
            $stmt = $db->prepare($sql);
            $stmt->execute([$todo['title'], $todo['done'], $userId]);

            echo json_encode(["success" => true, "message" => "Todo has been added"]);
        }
        catch (Exception $err) {
            http_response_code(500);
            echo json_encode(["success" => false, "errorMessage" => "Server error"]);
        }
    }

    static public function delete($db, $id) {
        try {
            $userId = checkAuth();

            if (!$userId) {
                http_response_code(401);
                echo json_encode(["success" => false, "errorMessage" => "Access denied"]);
                return;
            }

            $sql = "DELETE FROM todoitems WHERE id = ? AND user_id = ?";

            $stmt = $db->prepare($sql);
            $stmt->execute([$id, $userId]);

            echo json_encode(["success" => true, "message" => "Todo has been deleted"]);
        }
        catch (Exception $err) {
            http_response_code(500);
            echo json_encode(["success" => false, "errorMessage" => "Server error"]);
        }
    }

    static public function toggleDone($db, $id) {
        try {
            $userId = checkAuth();

            if (!$userId) {
                http_response_code(401);
                echo json_encode(["success" => false, "errorMessage" => "Access denied"]);
                return;
            }

            $sql = "SELECT done FROM todoitems WHERE id = ? AND user_id = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$id, $userId]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);

            $newStatusDone;
            if ($data['done'] === '0') {
                $newStatusDone = 1;
            }
            else {
                $newStatusDone = 0;
            }

            $sql = "UPDATE todoitems SET done = ? WHERE id = ? AND user_id = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$newStatusDone, $id, $userId]);

            echo json_encode(["success" => true, "message" => "Todo has been updated"]);
        }
        catch (Exception $err) {
            http_response_code(500);
            echo json_encode(["success" => false, "errorMessage" => "Server error"]);
        }
    }

    static public function left($db) {
        try {
            $userId = checkAuth();

            if (!$userId) {
                http_response_code(401);
                echo json_encode(["success" => false, "errorMessage" => "Access denied"]);
                return;
            }

            $sql = "SELECT COUNT(done) AS countLeft FROM todoitems LEFT JOIN users ON todoitems.user_id = users.id WHERE users.id = ? AND done = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$userId, 0]);

            $leftItems = $stmt->fetch(PDO::FETCH_ASSOC);
            echo json_encode(["success" => true, "leftItems" => $leftItems['countLeft']]);
        }
        catch (Exception $err) {
            http_response_code(500);
            echo json_encode(["success" => false, "errorMessage" => "Server error"]);
        }
    }

    static public function deleteCompleted($db) {
        try {
            $userId = checkAuth();

            if (!$userId) {
                http_response_code(401);
                echo json_encode(["success" => false, "errorMessage" => "Access denied"]);
                return;
            }

            $sql = "DELETE FROM todoitems WHERE user_id = ? AND done = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$userId, 1]);

            echo json_encode(["success" => true, "message" => "Completed todos have been deleted"]);
        }
        catch (Exception $err) {
            http_response_code(500);
            echo json_encode(["success" => false, "errorMessage" => "Server error"]);
        }
    }
}