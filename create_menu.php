<?php
    session_start();
    clearstatcache();

    if (empty($_SESSION["email"]) || empty($_SESSION["username"]) || empty($_SESSION["phone"]) || strtolower($_SESSION["username"]) != "admin") {
        header("Location: ./");
        exit();
    }

    $connection = new mysqli("localhost", "root", "123456789", "coffee_shop");

    if ($connection->connect_errno) {
        exit("Connection failed: " . $connection->connect_error);
    }

    $successMessage = "";
    $duplicateError = "";
    $nameError = "";
    $priceError = "";
    $imageError = "";

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $name = sanitizeInput($_POST["name"]);
        $price = sanitizeInput($_POST["price"]);
        $image = $_FILES["image"];
        $imageDir = "./images/" . $image["name"];

        if (empty($name)) {
            $nameError = "Invalid menu name!";
        }

        if (empty($price) || !is_numeric($price)) {
            $priceError = "Invalid menu price!";
        }

        if (empty($image) || !getimagesize($image["tmp_name"])) {
            $imageError = "Invalid image!";
        }

        if (!haveErrors($nameError, $priceError, $imageError)) {
            $preparedStatement = $connection->prepare("SELECT * FROM menu WHERE name=?");
            $preparedStatement->bind_param("s", $name);
            $preparedStatement->execute();

            $result = $preparedStatement->get_result();

            if ($result->num_rows > 0) {
                $duplicateError = "menu already exists!";
            } else {
                move_uploaded_file($image["tmp_name"], $imageDir);
                $preparedStatement = $connection->prepare("INSERT INTO menu (name, price, image) VALUES (?, ?, ?)");
                $preparedStatement->bind_param("sss", $name, $price, $imageDir);
                $preparedStatement->execute();
                $successMessage = "Menu created!";
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

    function haveErrors($nameError, $priceError, $imageError) {
        if (empty($nameError) && empty($priceError) && empty($imageError)) {
            return false;
        }

        return true;
    }

    $connection->close();
?>

<!DOCTYPE html>

<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <title>Coffee Shop - Admin Panel</title>

        <link href="css/bootstrap.css" rel="stylesheet" />
        <script src="https://use.fontawesome.com/releases/v6.1.0/js/all.js" crossorigin="anonymous"></script>
    </head>
    <body>
        <nav class="navbar navbar-dark bg-dark">
            <a class="navbar-brand ps-3" href="./admin.php">Admin Panel</a>
            
            <ul class="navbar-nav ms-md-0 me-3 me-lg-4">
                <li class="nav-item">
                    <a class="nav-link" href="./signout.php"><span class="fas fa-sign-out-alt"></span> Logout</a>
                </li>
            </ul>
        </nav>

        <div id="layoutSidenav">
            <div id="layoutSidenav_nav">
                <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
                    <div class="sb-sidenav-menu">
                        <div class="nav">
                            <br />

                            <a class="nav-link" href="./admin.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                                Dashboard
                            </a>

                            <a class="nav-link" href="./create_user.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-user-plus"></i></div>
                                Create a User
                            </a>

                            <a class="nav-link" href="./create_product.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-folder-plus"></i></div>
                                Create a Product
                            </a>
                            <a class="nav-link" href="./create_menu.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-folder-plus"></i></div>
                                Create a Menu
                            </a>

                            <a class="nav-link" href="./create_blog.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-calendar-plus"></i></div>
                                Create a Blog
                            </a>

                            <a class="nav-link" href="./change_password.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-pen-to-square"></i></div>
                                Change Password
                            </a>

                            <a class="nav-link" href="./">
                                <div class="sb-nav-link-icon"><i class="fas fa-circle-arrow-left"></i></div>
                                Go to Site Home
                            </a>
                        </div>
                    </div>

                    <div class="sb-sidenav-footer">
                        <div class="small">Logged in as:</div>
                        <?php echo $_SESSION["username"]; ?>
                    </div>
                </nav>
            </div>
            
            <div id="layoutSidenav_content">
                <main>
                    <div class="container-fluid px-4">
                        <h1 class="mt-4">Create a Menu</h1>

                        <br />

                        <div class="card" style="width: 600px">
                            <div class="card-body">
                                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) ?>" method="POST" enctype="multipart/form-data">
                                    <?php
                                        if (!empty($successMessage)) {
                                            echo "<p style='color: green'>$successMessage</p>";
                                        } elseif (!empty($duplicateError)) {
                                            echo "<p style='color: red'>$duplicateError</p>";
                                        }
                                    ?>

                                    <div class="mb-3">
                                        <?php
                                            if (!empty($nameError)) {
                                                echo "<p style='color: red'>$nameError</p>";
                                            }
                                        ?>
                                        <label for="name" class="form-label">Name</label>
                                        <input name="name" type="text" class="form-control" id="name" />
                                    </div>

                                    <div class="mb-3">
                                        <?php
                                            if (!empty($priceError)) {
                                                echo "<p style='color: red'>$priceError</p>";
                                            }
                                        ?>
                                        <label for="price" class="form-label">Price</label>
                                        <input name="price" type="text" class="form-control" id="price" />
                                    </div>

                                    <div class="mb-3">
                                        <?php
                                            if (!empty($imageError)) {
                                                echo "<p style='color: red'>$imageError</p>";
                                            }
                                        ?>
                                        <label for="image" class="form-label">Image: </label>
                                        <input name="image" type="file" class="form-control" id="image" />
                                    </div>

                                    <button type="submit" class="btn btn-primary" style="background-color: #d3ad7f; border-color: #d3ad7f">Create menu</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </main>

                <footer class="py-4 bg-light mt-auto">
                    <div class="container-fluid px-4">
                        <div class="d-flex align-items-center justify-content-between small">
                            <div class="text-muted">Copyright &copy; Coffee Shop 2022</div>
                        </div>
                    </div>
                </footer>
            </div>
        </div>
    </body>
</html>
