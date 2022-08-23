<?php
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
        $price = sanitizeInput($_POST["price"]);
        $image = sanitizeInput($_POST["image"]);

        if (!empty($name) && isset($name) && !empty($price) && isset($price) && !empty($image) && isset($image)) {
            $preparedStatement = $connection->prepare("SELECT * FROM favorites WHERE email=? AND name=?");
            $preparedStatement->bind_param("ss", $_SESSION["email"], $name);
            $preparedStatement->execute();
            $result = $preparedStatement->get_result();
            $preparedStatement->close();

            if ($result->num_rows == 0) {
                $preparedStatement = $connection->prepare("INSERT INTO favorites (name, price, image, email) VALUES (?, ?, ?, ?)");
                $preparedStatement->bind_param("ssss", $name, $price, $image, $_SESSION["email"]);
                $preparedStatement->execute();
                $preparedStatement->close();
            }

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