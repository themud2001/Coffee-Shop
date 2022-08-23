<?php
    session_start();

    $connection = new mysqli("localhost", "root", "123456789", "coffee_shop");
    $wroteReview = false;
    $favItemsCount = 0;

    if ($connection->connect_errno) {
        exit("Connection failed: " . $connection->connect_error);
    }

    $preparedStatement = $connection->prepare("SELECT * FROM reviews WHERE email=?");
    $preparedStatement->bind_param("s", $_SESSION["email"]);
    $preparedStatement->execute();
    $result = $preparedStatement->get_result();
    $preparedStatement->close();

    if ($result->num_rows > 0) {
        $wroteReview = true;
    }

    $preparedStatement = $connection->prepare("SELECT name, price, image FROM favorites WHERE email=?");
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

    $successMessage = "";
    $ratingError = "";
    $commentError = "";

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (!$wroteReview) {
            $rating = sanitizeInput($_POST["rating"]);
            $comment = sanitizeInput($_POST["comment"]);

            if (empty($rating)) {
                $ratingError = "Invalid rating!";
            }

            if (empty($comment)) {
                $commentError = "Invalid review comment!";
            }

            if (!haveErrors($ratingError, $commentError)) {
                $preparedStatement = $connection->prepare("INSERT INTO reviews (username, email, comment, rating) VALUES (?, ?, ?, ?)");
                $preparedStatement->bind_param("ssss", $_SESSION["username"], $_SESSION["email"], $comment, $rating);
                $preparedStatement->execute();
                $successMessage = "Review added!";
                $wroteReview = true;

                $preparedStatement->close();
            }
        }
    }

    function sanitizeInput($value) {
        $value = trim($value);
        $value = stripslashes($value);
        $value = htmlspecialchars($value);

        return $value;
    }

    function haveErrors($ratingError, $commentError) {
        if (empty($ratingError) && empty($commentError)) {
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

 
    <link rel="stylesheet" href="css/style.css">
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
        <a href="#home">home</a>
        <a href="#about">about</a>
        <a href="#menu">menu</a>
        <a href="#products">products</a>
        <a href="#review">review</a>
       
        <a href="#blogs">blogs</a>
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
            if(empty($_SESSION["email"]) || empty($_SESSION["username"]) || empty ($_SESSION ["phone"])) {
                echo '<a href="./signin.php" class="btn checkout-btn">checkout now</a> ';
            } else {
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
                    $preparedStatement = $connection->prepare("SELECT total, timestamp FROM orders WHERE email=? ORDER BY timestamp DESC");
                    $preparedStatement->bind_param("s", $_SESSION["email"]);
                    $preparedStatement->execute();

                    $result = $preparedStatement->get_result();
                    
                    if ($result->num_rows == 0) {
                        echo "<h1 style='height: 100%; text-align: center; opacity: 50%; margin-top: 50%'>Empty list!</h1>";
                    } else {
                        $counter = $result->num_rows;
                        while ($ordersArray = $result->fetch_assoc()) {
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





<section class="home" id="home">
    <div class="content">
        <h3>fresh coffee in the morning</h3>
        <p>Lorem ipsum, dolor sit amet consectetur adipisicing elit. Placeat labore, sint cupiditate distinctio tempora reiciendis.</p>
        <a href="#menu" class="btn">get yours now</a>
    </div>
</section>





<section class="about" id="about">
    <h1 class="heading"> <span>about</span> us </h1>

    <div class="row">
        <div class="image">
            <img src="images/about-img.jpeg" alt="">
        </div>

        <div class="content">
            <h3>what makes our coffee special?</h3>
            <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Voluptatibus qui ea ullam, enim tempora ipsum fuga alias quae ratione a officiis id temporibus autem? Quod nemo facilis cupiditate. Ex, vel?</p>
            <p>Lorem ipsum dolor sit amet consectetur, adipisicing elit. Odit amet enim quod veritatis, nihil voluptas culpa! Neque consectetur obcaecati sapiente?</p>
        </div>

    </div>

</section>
<section class="menu" id="menu">

    <h1 class="heading"> our <span>menu</span> </h1>

    <div class="box-container">
        <?php
            $result = $connection->query("SELECT * FROM menu;");
            
            if ($result->num_rows == 0) {
                echo "<h1 class='no-menu-text'>There is no menu!</h1>";
            } else {
                while ($menuArray = $result->fetch_assoc()) {
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
        ?>

    </div>

</section>


<section class="products" id="products">

    <h1 class="heading"> our <span>products</span> </h1>

    <div class="box-container">
        <?php
            $result = $connection->query("SELECT * FROM products;");
            
            if ($result->num_rows == 0) {
                echo "<h1 class='no-products-text'>There are no products!</h1>";
            } else {
                while ($productsArray = $result->fetch_assoc()) {
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
        ?>

    </div>

</section>



<section class="review" id="review">

    <h1 class="heading"> customer's <span>review</span> </h1>

    <div class="box-container">
        <?php
            $result = $connection->query("SELECT * FROM reviews;");
            
            if ($result->num_rows == 0) {
                echo "<h1 class='no-reviews-text'>There are no reviews!</h1>";
            } else {
                while ($reviewsArray = $result->fetch_assoc()) {
                    echo "<div class='box'>
                        <img src='./images/quote-img.png' alt='' class='quote'>
                        <p>" . $reviewsArray["comment"] . "</p>
                        <img src='./images/user-profile.png' class='user' alt=''>
                        <h3>" . $reviewsArray["username"] . "</h3>";

                    echo "<div class='stars'>";
                    for ($i = 0; $i < $reviewsArray["rating"]; $i++) {
                        echo "<i class='fas fa-star'></i>";
                    }

                    for ($i = 0; $i < 5 - $reviewsArray["rating"]; $i++) {
                        echo "<i class='far fa-star'></i>";
                    }
                    echo "</div>";

                    echo "</div>";
                }
            }
        ?>


    </div>

    <?php
        if (!empty($successMessage)) {
            echo "<p class='returned-message' style='color: green'>$successMessage</p>";
        }

        if (!empty($duplicateError)) {
            echo "<p class='returned-message'>$duplicateError</p>";
        }

        if (!empty($_SESSION["email"]) && !empty($_SESSION["username"]) && !empty($_SESSION["phone"]) && strtolower($_SESSION["username"]) != "admin" && !$wroteReview) {
            echo "<div class='box-container review-form-container'>";
            echo "<form action='" . htmlspecialchars($_SERVER["PHP_SELF"]) . "#review' method='POST'>";

            echo "<h1 class='heading'>Write Your <span>Review</span></h1>";

            if (!empty($ratingError)) {
                echo "<p>$ratingError</p>";
            }

            echo "<select class='fas fa-star' id='rating' name='rating'>";
            echo "<option class='fas fa-star' value='1'>&#xf005;</option>";
            echo "<option class='fas fa-star' value='2'>&#xf005;&#xf005;</option>";
            echo "<option class='fas fa-star' value='3'>&#xf005;&#xf005;&#xf005;</option>";
            echo "<option class='fas fa-star' value='4'>&#xf005;&#xf005;&#xf005;&#xf005;</option>";
            echo "<option class='fas fa-star' value='5'>&#xf005;&#xf005;&#xf005;&#xf005;&#xf005;</option>";
            echo "</select>";
            
            if (!empty($commentError)) {
                echo "<p>$commentError</p>";
            }

            echo "<textarea id='comment' name='comment' placeholder='Write a review...'></textarea>";

            echo "<input type='submit' class='submit' />";

            echo "</form>";
            echo "</div>";
        }
    ?>

</section>


<section class="blogs" id="blogs">

    <h1 class="heading"> our <span>blogs</span> </h1>

    <div class="box-container">

        <?php
            $result = $connection->query("SELECT * FROM blogs;");
            
            if ($result->num_rows == 0) {
                echo "<h1 class='no-blogs-text'>There are no blogs!</h1>";
            } else {
                while ($blogsArray = $result->fetch_assoc()) {
                    echo "<div class='box'>
                        <div class='image'>
                            <img src='" . $blogsArray["image"] . "' alt=''>
                        </div>
                        <div class='content'>
                            <a class='title'>" . $blogsArray["title"] . "</a>
                            <span>by admin</span>
                            <p>" . $blogsArray["description"] . "</p>
                        </div>
                    </div>";
                }
            }
        ?>

</section>



<section class="footer">
    <div class="contact">
        <h3> Contact us </h3>
        <h3> social media :</h3> 
        </div>
    <div class="share">
        
        <a href="http://facebook.com/" target="_blank" class="fab fa-facebook-f"></a>
        <a href="http://twitter.com/" target="_blank" class="fab fa-twitter"></a>
        <a href="http://instagram.com/" target="_blank" class="fab fa-instagram"></a>
        <a href="http://linkedin.com/" target="_blank" class="fab fa-linkedin"></a>
        <a href="http://pinterest.com/" target="_blank" class="fab fa-pinterest"></a>
       
    </div> 
    <div class="contact">
        <h3>phone : 0772341720</h3>
        </div>
    

    <div class="links">
        <a href="#home">home</a>
        <a href="#about">about</a>
        <a href="#menu">menu</a>
        <a href="#products">products</a>
        <a href="#review">review</a>
        
        <a href="#blogs">blogs</a>
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