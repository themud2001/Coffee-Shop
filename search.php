<?php
    ini_set("display_errors", 1);
    session_start();
    
    $connection = new mysqli("localhost", "root", "123456789", "coffee_shop");
    $favItemsCount = 0;

    if ($connection->connect_errno) {
        exit("Connection failed: " . $connection->connect_error);
    }

    $preparedStatement = $connection->prepare("SELECT * FROM favorites WHERE email=?");
    $preparedStatement->bind_param("s", $_SESSION["email"]);
    $preparedStatement->execute();
    $result = $preparedStatement->get_result();
    $favItemsCount = $result->num_rows;
    $favItemsArray = [];
    
    if ($favItemsCount > 0) {
        while ($tempArray = $result->fetch_assoc()) {
            array_push($favItemsArray, ["productName" => $tempArray["name"], "price" => $tempArray["price"], "image" => $tempArray["image"]]);
        }
    }

    $favItemsArray = json_encode($favItemsArray);
    echo "<script>localStorage.setItem('fav', '$favItemsArray')</script>";

    $preparedStatement->close();

    $test = "";
    $existError = "";
    $searchError = "";
    $resultHistory = null;
    $resultProducts = null;
    $resultMenu = null;

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $search = sanitizeInput($_POST["search"]);

        if (empty($search)) {
            $searchError = "Invalid search keyword!";
        }

        if (!haveErrors($searchError)) {
            $finalSearch = "";

            for ($i = 0; $i < strlen($search); $i++) {
                $finalSearch .= $search[$i] . "%";
            }

            $preparedStatement = $connection->prepare("SELECT * FROM products WHERE name LIKE ?");
            $preparedStatement->bind_param("s", $finalSearch);
            $preparedStatement->execute();
            $resultProducts = $preparedStatement->get_result();

            $preparedStatement = $connection->prepare("SELECT * FROM menu WHERE name LIKE ?");
            $preparedStatement->bind_param("s", $finalSearch);
            $preparedStatement->execute();
            $resultMenu = $preparedStatement->get_result();

            $preparedStatement->close();
        }
    }

    function sanitizeInput($value) {
        $value = trim($value);
        $value = stripslashes($value);
        $value = htmlspecialchars($value);

        return $value;
    }

    function haveErrors($searchError) {
        if (empty($searchError)) {
            return false;
        }

        return true;
    }
?>

<!DOCTYPE html>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Coffee Shop</title>

    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

 
    <link rel="stylesheet" href="css/search.css">

</head>
<body>

<div class="notification">
    <h3>Added to cart!</h3>
    <span class="fas fa-times close-notification"></span>
</div>

<div class="notification-fav">
    <h3 class="text">Added to favorites!</h3>
    <span class="fas fa-times close-fav-notification"></span>
</div>
    


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
        
        <?php
            if (!empty($_SESSION["email"]) && !empty($_SESSION["phone"]) && !empty($_SESSION["username"])) {
                if ($favItemsCount > 0) {
                    echo "<div class='fas fa-heart' id='fav-btn'> (" . $favItemsCount . ")</div>";
                } else {
                    echo "<div class='fas fa-heart' id='fav-btn'></div>";
                }
            }
        ?>

        <div class="fas fa-shopping-cart" id="cart-btn"></div>
        <?php
            if (!empty($_SESSION["email"]) && !empty($_SESSION["phone"]) && !empty($_SESSION["username"])) {
                if (strtolower($_SESSION["username"]) == "admin") {
                    echo "<a href='./admin.php' class='fas fa-tools'> Admin Panel</a>";
                } else {
                    echo "<a id='history-btn' class='fas fa-user'>"." Welcome, " .$_SESSION["username"]."</a>";
                }

                echo "<a href='./signout.php' class='fas fa-sign-out-alt'></a>";
            } else {
                echo "<a href='./signin.php' class='fas fa-user' id='user-btn'></a>"; 
            }
        ?>
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
        <div class="items">
            <?php
                if (!empty($_SESSION["email"]) && !empty($_SESSION["phone"]) && !empty($_SESSION["username"])) {
                    $preparedStatement = $connection->prepare("SELECT id, name, price, image FROM favorites WHERE email=?");
                    $preparedStatement->bind_param("s", $_SESSION["email"]);
                    $preparedStatement->execute();

                    $result = $preparedStatement->get_result();
                    
                    if ($result->num_rows == 0) {
                        echo "<h1 style='height: 100%; text-align: center; opacity: 50%; margin-top: 50%'>Empty list!</h1>";
                    } else {
                        while ($favoritesArray = $result->fetch_assoc()) {
                            echo "<div class='fav-item'>";
                            echo "<span class='fas fa-shopping-cart add-to-cart-icon-fav'></span>";
                            echo "<span class='fas fa-times remove-from-fav-btn'></span>";
                            echo "<img src='" . $favoritesArray["image"] . "' alt='" . $favoritesArray["name"] . "' />";
                            echo "<div class='content'>";
                            echo "<h3>" . $favoritesArray["name"] . "</h3>";
                            echo "<div class='price'><b>$</b>" . $favoritesArray["price"] . "<span></span></div>";
                            echo "</div>";
                            echo "</div>";
                        }
                    }
                }
            ?>
        </div>
    </div>

    <div class="history-items-container">
        <h1 style='text-align: center; border-bottom: 1px solid #000; padding: 15px 0px;'>Orders History</h1>
        <div class="items">
            <?php
                if (!empty($_SESSION["email"]) && !empty($_SESSION["phone"]) && !empty($_SESSION["username"])) {
                    $preparedStatement = $connection->prepare("SELECT id, total, timestamp FROM orders WHERE email=? ORDER BY timestamp DESC");
                    $preparedStatement->bind_param("s", $_SESSION["email"]);
                    $preparedStatement->execute();

                    $resultHistory = $preparedStatement->get_result();
                    
                    if ($resultHistory->num_rows == 0) {
                        echo "<h1 style='height: 100%; text-align: center; opacity: 50%; margin-top: 50%'>Empty list!</h1>";
                    } else {
                        $counter = $resultHistory->num_rows;
                        while ($ordersArray = $resultHistory->fetch_assoc()) {
                            echo "<div class='history-item'>";
                            echo "<img src='./images/logo.png' alt='Test 1' />";
                            echo "<div class='content'>
                                  <h3>Order #" . $counter . "</h3>";
                            echo "<span class='date'>" . $ordersArray["timestamp"] . "</span>";
                            echo "<div class='price'>" . $ordersArray["total"] . "</div>";
                            echo "</div>";
                            echo "</div>";
                            $counter--;
                        }
                    }
                }
            ?>
        </div>
    </div>
   
</header>


<div class="box-container big-search-form">
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) ?>" method="POST">
        <?php
            if (!empty($searchError)) {
                echo "<p style='color: red; text-align: center; font-size: 24px; margin-bottom: 30px'>Invalid search keyword!</p>";
            }
        ?>
        <input type="text" name="search" class="big-search-input" placeholder="Search..." />
        <button type="submit" class="fas fa-search big-search-btn"></button>
    </form>
</div>

<section class="products" id="products">
    <h1 class="heading">Products</h1>

    <div class="box-container">
        <?php
            if ($resultProducts) {
                if ($resultProducts->num_rows == 0 && empty($searchError)) {
                    echo "<p style='color: #FFF; text-align: center; font-size: 24px;'>The product doesn't exist!</p>";
                } else {
                    while ($productsArray = $resultProducts->fetch_assoc()) {
                        echo "<div class='box'>";
                        echo "<div class='icons'>
                                <a class='fas fa-shopping-cart add-to-cart-icon'></a>";
    
                        if (!empty($_SESSION["email"]) && !empty($_SESSION["username"]) && !empty($_SESSION["phone"])) {
                            echo "<a class='fas fa-heart add-to-fav-icon'></a>";
                        }
    
                        echo "</div>
                            <div class='image'>
                                <img src='" . $productsArray["image"] . "' alt=''>
                            </div>";
                        echo "<div class='content'>
                                    <h3 class='name'>" . $productsArray["name"] . "</h3>
                                    <div class='price'><b>$</b>" . $productsArray["price"] . " <span>$" . number_format((float)$productsArray["price"] * 1.3, 2, ".", "") . "</span></div>
                                </div>";
                        echo "</div>";
                    }
                }
            }
        ?>
    </div>
</section>

<div style="width: 95%; height: 1px; border: 1px solid var(--main-color); margin: 50px auto; "></div>

<section class="menu" id="menu">
    <h1 class="heading">Menu</h1>

    <div class="box-container">
        <?php
            if ($resultMenu) {
                if ($resultMenu->num_rows == 0 && empty($searchError)) {
                    echo "<p style='color: #FFF; text-align: center; font-size: 24px;'>The menu doesn't exist! ". $test . "</p>";
                } else {
                    while ($menuArray = $resultMenu->fetch_assoc()) {
                        echo "<div class='box'>";
                        echo "<div class='icons'>
                                <a class='fas fa-shopping-cart add-to-cart-icon'></a>";
    
                        if (!empty($_SESSION["email"]) && !empty($_SESSION["username"]) && !empty($_SESSION["phone"])) {
                            echo "<a class='fas fa-heart add-to-fav-icon'></a>";
                        }
                        
                        echo "</div><div class='image'>
                                <img src='" . $menuArray["image"] . "' alt=''>
                            </div>";
                        echo "<div class='content'>
                                    <h3 class='name'>" . $menuArray["name"] . "</h3>
                                    <div class='price'><b>$</b>" . $menuArray["price"] . " <span>$" . number_format((float)$menuArray["price"] * 1.3, 2, ".", "") . "</span></div>
                                </div>";
                        echo "</div>";
                    }
                }
            }
        ?>
    </div>

</section>

<div id="sound">
<audio id="notifSound">
  <source src="./sounds/Popup.mp3" type="audio/mpeg">
</audio>

</div>

<script src="js/script.js"></script>

</body>
</html>