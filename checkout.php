<?php
    session_start();
    date_default_timezone_set("Asia/Amman");

    if (empty($_SESSION["email"]) || empty($_SESSION["username"]) || empty($_SESSION["phone"])) {
        header("Location: ./");
        exit();
    }

    $connection = new mysqli("localhost", "root", "123456789", "coffee_shop");

    if ($connection->connect_errno) {
        exit("Connection failed: " . $connection->connect_error);
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (!empty($_POST["total"]) && isset($_POST["total"])) {
            $preparedStatement = $connection->prepare("INSERT INTO orders (total, email, timestamp) VALUES (?, ?, ?)");
            $preparedStatement->bind_param("sss", $_POST["total"], $_SESSION["email"], date("d/m/Y h:i A"));
            $preparedStatement->execute();
            $preparedStatement->close();
            $connection->close();
        }
    } else {
        header("Location: ./");
    }
?>