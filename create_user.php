<?php
    session_start();

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
    $emailError = "";
    $phoneError = "";
    $passwordError = "";
    $usernameError = "";
    $adminError = "";

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $username = sanitizeInput($_POST["username"]);
        $email = sanitizeInput($_POST["email"]);
        $phone = sanitizeInput($_POST["phone"]);
        $password = sanitizeInput($_POST["password"]);

        if (empty($username) ) {
            $usernameError = "Invalid username!";
        }

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $emailError = "Invalid E-mail!";
        }
        
        if (empty($phone) || strlen($phone) != 10 || !is_numeric($phone)) {
            $phoneError = "Invalid phone number!";
        }

        if (empty($password)) {
            $passwordError = "Invalid password!";
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
                $preparedStatement = $connection->prepare("INSERT INTO users (username,email, phone, password) VALUES (?, ?, ?, ?)");
                $preparedStatement->bind_param("ssss",$username,$email, $phone, $password);
                $preparedStatement->execute();
                $successMessage = "User created!";
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
                        <h1 class="mt-4">Create a User</h1>

                        <br />

                        <div class="card" style="width: 600px">
                            <div class="card-body">
                                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) ?>" method="POST">
                                    <?php
                                        if (!empty($successMessage)) {
                                            echo "<p style='color: green'>$successMessage</p>";
                                        } elseif (!empty($adminError)) {
                                            echo "<p style='color: red'>$adminError</p>";
                                        } elseif (!empty($duplicateError)) {
                                            echo "<p style='color: red'>$duplicateError</p>";
                                        }
                                    ?>

                                    <div class="mb-3">
                                        <?php
                                            if (!empty($usernameError)) {
                                                echo "<p style='color: red'>$usernameError</p>";
                                            }
                                        ?>
                                        <label for="username" class="form-label">Username</label>
                                        <input name="username" type="text" class="form-control" id="username" />
                                    </div>

                                    <div class="mb-3">
                                        <?php
                                            if (!empty($emailError)) {
                                                echo "<p style='color: red'>$emailError</p>";
                                            }
                                        ?>
                                        <label for="email" class="form-label">E-mail</label>
                                        <input name="email" type="text" class="form-control" id="email" />
                                    </div>

                                    <div class="mb-3">
                                        <?php
                                            if (!empty($phoneError)) {
                                                echo "<p style='color: red'>$phoneError</p>";
                                            }
                                        ?>
                                        <label for="phone" class="form-label">Phone</label>
                                        <input name="phone" type="text" class="form-control" id="phone" maxlength="10" />
                                    </div>

                                    <div class="mb-3">
                                        <?php
                                            if (!empty($passwordError)) {
                                                echo "<p style='color: red'>$passwordError</p>";
                                            }
                                        ?>
                                        <label for="password" class="form-label">Password</label>
                                        <input name="password" type="password" class="form-control" id="password" />
                                    </div>

                                    <button type="submit" class="btn btn-primary" style="background-color: #d3ad7f; border-color: #d3ad7f">Create User</button>
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
