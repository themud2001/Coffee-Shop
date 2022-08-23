<?php
    session_start();
    
    if (!empty($_SESSION["email"]) || !empty($_SESSION["phone"]) || !empty($_SESSION["username"])) {
        header("Location: ./");
        exit();
    }

    $connection = new mysqli("localhost", "root", "123456789", "coffee_shop");
    $successMessage = "";
    $duplicateError = "";
    $emailError = "";
    $phoneError = "";
    $passwordError = "";
    $usernameError = "";
    $adminError = "";

    if ($connection->connect_errno) {
        exit("Connection failed: " . $connection->connect_error);
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $email = sanitizeInput($_POST["email"]);
        $phone = sanitizeInput($_POST["phone"]);
        $password = sanitizeInput($_POST["password"]);
        $confirmPassword = sanitizeInput($_POST["confirm"]);
        $username = sanitizeInput($_POST["username"]);

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $emailError = "Invalid E-mail!";
        }
        if (empty($username) ) {
            $usernameError = "Invalid Username";
        }
         if (empty($phone) || strlen($phone) != 10 || !is_numeric($phone)) {
            $phoneError = "Invalid phone number!";
        }

        if (empty($password) || empty($confirmPassword)) {
            $passwordError = "Invalid password!";
        }

        if ($password !== $confirmPassword) {
            $passwordError = "Passwords don't match!";
        }

        if (strtolower($username) == "admin") {
            $adminError = "Can't sign up as admin!";
        }

        if (!haveErrors($usernameError, $emailError, $phoneError, $passwordError, $adminError)) {
            $preparedStatement = $connection->prepare("SELECT * FROM users WHERE email=?");
            $preparedStatement->bind_param("s", $email);
            $preparedStatement->execute();

            $result = $preparedStatement->get_result();

            if ($result->num_rows > 0) {
                $duplicateError = "Email already exists!";
            } else {
              $password = password_hash($password, PASSWORD_DEFAULT); 
                $preparedStatement = $connection->prepare("INSERT INTO users (username,email, phone, password) VALUES (?, ?, ?,?)");
                $preparedStatement->bind_param("ssss",$username,$email, $phone, $password);
                $preparedStatement->execute();
                $successMessage = "User created! Redirecting...";
                header("refresh: 3; url=./signin.php");
            }

            $preparedStatement->close();
        }
    }

    function sanitizeInput($value) {
        $value = trim($value);
        $value = stripslashes($value);
        $value = htmlspecialchars($value);

        return $value;
    }

    function haveErrors($usernameError, $emailError, $phoneError, $passwordError, $adminError) {
        if (empty($usernameError) && empty($emailError) && empty($phoneError) && empty($passwordError) && empty($adminError)) {
            return false;
        }

        return true;
    }

    $connection->close();
?>

<!DOCTYPE html>

<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Coffee Shop - Sign Up</title>

        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

        <link rel="stylesheet" href="css/signup.css">
    </head>
 
    <body>
        <header class="header">

            <a href="./" class="logo">
                <img src="images/logo.png" alt="">
            </a>

            <nav class="navbar">
                <a href="./">Home</a>
                <a href="./#about">About</a>
                <a href="./#menu">Menu</a>
                <a href="./#products">Products</a>
                <a href="./#review">Review</a>
                <a href="./#blogs">Blogs</a>
            </nav>

            <div class="icons">
                <div class="fas fa-search" id="search-btn"></div>
                <div class="fas fa-shopping-cart" id="cart-btn"></div>
                <a href="./signin.php" class="fas fa-user" id="user-btn"></a>
                <div class="fas fa-bars" id="menu-btn"></div>
            </div>

            <form class="search-form" action="./search.php" method="POST">
                <input name="search" type="text" id="search-box" placeholder="search here...">
                <button type="submit" for="search-box" class="fas fa-search search-btn"></button>
            </form>

            <div class="cart-items-container">
                <h1 style='text-align: center; border-bottom: 1px solid #000; padding: 15px 0px;'>Shopping Cart</h1>
                <div class="items">
                </div>
                
                <?php
                    if(empty($_SESSION["email"]) || empty($_SESSION["username"]) || empty ($_SESSION ["phone"]) )
                    {
                        echo '<a href="./signin.php" class="btn checkout-btn">checkout now</a> ';
                    }
                    else {
                    echo '<a href="./" id="checkout-btn" class="btn checkout-btn">Check Out</a>';
                    }
                ?>
            </div>

            <div class="fav-items-container">
                <h1 style='text-align: center; border-bottom: 1px solid #000; padding: 15px 0px;'>Favorite Items</h1>
                <div class="items"></div>
            </div>
    
            </div>
        </header>

        <section class="signup-container">
            <h1>Sign Up</h1>

            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="POST">
                <?php
                    if (!empty($successMessage)) {
                        echo "<p style='color: green'>$successMessage</p>";
                    } elseif (!empty($adminError)) {
                        echo "<p style='color: red'>$adminError</p>";
                    } elseif (!empty($duplicateError)) {
                        echo "<p style='color: red'>$duplicateError</p>";
                    }
                ?>
                <div class="username-container">
                    <?php
                        if (!empty($usernameError)) {
                            echo "<p style='color: red'>$usernameError</p>";
                        }
                    ?>
                    <label for="username">Username</label>
                    <input type="text" name="username" id="username" />
                </div>


                <div class="email-container">
                    <?php
                        if (!empty($emailError)) {
                            echo "<p style='color: red'>$emailError</p>";
                        }
                    ?>
                    <label for="email">E-mail</label>
                    <input type="text" name="email" id="email" />
                </div>

                <div class="phone-container">
                    <?php
                        if (!empty($phoneError)) {
                            echo "<p style='color: red'>$phoneError</p>";
                        }
                    ?>
                    <label for="phone">Phone</label>
                    <input type="text" name="phone" id="phone" maxlength="10" />
                </div>

                <div class="password-container">
                    <?php
                        if (!empty($passwordError)) {
                            echo "<p style='color: red'>$passwordError</p>";
                        }
                    ?>
                    <label for="password">Password</label>
                    <input type="password" name="password" id="password" />
                </div>

                <div class="confirm-container">
                    <label for="confirm">Password Confirmation</label>
                    <input type="password" name="confirm" id="confirm" />
                </div>

                <button type="submit">Sign Up</button>
            </form>

            <p>Already have an account? <a href="./signin.php">Sign in</a>.</p>
        </section>
    </body>

    <script src="js/script.js"></script>
</html>