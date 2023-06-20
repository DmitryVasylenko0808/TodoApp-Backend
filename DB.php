<?php

class DB {
    private $db;

    public function __construct($db_config) {
        try {
            $this->db = new PDO($db_config["dsn"], $db_config["user"], $db_config["password"]);
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        catch (Exception $err) {
            echo "Server error: {$err->getMessage()}";
        }
    }

    public function getConnection() {
        return $this->db;
    }
}