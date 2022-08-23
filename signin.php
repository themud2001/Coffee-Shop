<?php
    session_start();

    if (!empty($_SESSION["email"]) || !empty($_SESSION["phone"]) || !empty($_SESSION["username"])) {
        header("Location: ./");
        exit();
    }
    
    $connection = new mysqli("localhost", "root", "123456789", "coffee_shop");
    $existError = "";
    $emailError = "";
    $passwordError = "";
    

    if ($connection->connect_errno) {
        exit("Connection failed: " . $connection->connect_error);
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $email = sanitizeInput($_POST["email"]);
        $password = sanitizeInput($_POST["password"]);

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $emailError = "Invalid E-mail!";
        }

        if (empty($password)) {
            $passwordError = "Invalid password!";
        }

        if (!haveErrors($emailError, $passwordError)) {
            $preparedStatement = $connection->prepare("SELECT * FROM users WHERE email=?");
            $preparedStatement->bind_param("s", $email);
            $preparedStatement->execute();

            $result = $preparedStatement->get_result();

            if ($result->num_rows == 0) {
                $existError = "User doesn't exist!";
            } else {
                $row = $result->fetch_assoc();

                if (password_verify($password, $row["password"])) {
                    $_SESSION["username"] = $row["username"];
                    $_SESSION["email"] = $row["email"];
                    $_SESSION["phone"] = $row["phone"];
                    header("Location: ./");
                    exit();
                } else {
                    $existError = "Invalid credentials!";
                }
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

    function haveErrors($emailError, $passwordError) {
        if (empty($emailError) && empty($passwordError)) {
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
        <title>Coffee Shop - Sign In</title>

        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

        <link rel="stylesheet" href="css/signin.css">
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

        <section class="signin-container">
            <h1>Sign In</h1>

            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) ?>" method="POST">
                <?php
                    if (!empty($existError)) {
                        echo "<p style='color: red'>$existError</p>";
                    }
                ?>

                <div class="email-container">
                    <?php
                        if (!empty($emailError)) {
                            echo "<p style='color: red'>$emailError</p>";
                        }
                    ?>
                    <label for="email">E-mail</label>
                    <input type="text" name="email" id="email" />
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

                <button type="submit">Sign In</button>
            </form>

            <p>Don't have an account? <a href="./signup.php">Sign up</a>.</p>
        </section>
    </body>

    <script src="js/script.js"></script>
</html>