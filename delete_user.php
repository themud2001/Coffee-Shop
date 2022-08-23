<?php
    session_start();

    if (strtolower($_SESSION["username"]) != "admin") {
        header("Location: ./");
        exit();
    }

    $connection = new mysqli("localhost", "root", "123456789", "coffee_shop");

    if ($connection->connect_errno) {
        exit("Connection failed: " . $connection->connect_error);
    }

    if (empty($_GET["id"])) {
        header("Location: ./admin.php");
        exit();
    }

    $result = $connection->query("SELECT * FROM users WHERE id=" . $_GET["id"]);

    if ($result->num_rows == 0) {
        header("Location: ./admin.php");
        exit();
    }

    $user = $result->fetch_assoc();

    if (strtolower($user["username"]) == "admin") {
        header("Location: ./admin.php");
        exit();
    }

    $result = $connection->query("DELETE FROM users WHERE id=" . $_GET["id"]);

    $connection->close();
    
    header("Location: ./admin.php");
    exit();
?>