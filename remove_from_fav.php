<?php
    ini_set("display_errors", 1);
    session_start();

    if (empty($_SESSION["email"]) || empty($_SESSION["username"]) || empty($_SESSION["phone"])) {
        header("Location: ./");
        exit();
    }

    $connection = new mysqli("localhost", "root", "123456789", "coffee_shop");

    if ($connection->connect_errno) {
        exit("Connection failed: " . $connection->connect_error);
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $name = sanitizeInput($_POST["name"]);

        if (!empty($name) && isset($name)) {
            $preparedStatement = $connection->prepare("DELETE FROM favorites WHERE email=? AND name=?");
            $preparedStatement->bind_param("ss", $_SESSION["email"], $name);
            $preparedStatement->execute();
            $preparedStatement->close();

            $connection->close();
        }
    } else {
        header("Location: ./");
    }

    function sanitizeInput($value) {
        $value = trim($value);
        $value = stripslashes($value);
        $value = htmlspecialchars($value);

        return $value;
    }
?>