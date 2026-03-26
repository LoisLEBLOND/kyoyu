<?php
try {
    $database = new PDO(
        "mysql:host=localhost;dbname=kyoyu;charset=utf8mb4",
        "root",
        ""
    );
} catch (Exception $exception) {
    echo "Erreur de connexion BDD";
    exit;
}