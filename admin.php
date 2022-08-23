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
                        <h1 class="mt-4">Dashboard</h1>

                        <br />

                        <div class="card">
                            <div class="card-body">
                                <div class="table-responsive-md">
                                    <h2>Users</h2>

                                    <table class="table">
                                        <caption>List of users</caption>

                                        <thead>
                                            <tr>
                                            <th scope="col">Username</th>
                                            <th scope="col">E-mail</th>
                                            <th scope="col">Phone</th>
                                            <th scope="col">Delete</th>
                                            </tr>
                                        </thead>
                                        
                                        <tbody>
                                            <?php
                                                $result = $connection->query("SELECT * FROM users;");
                                                
                                                if ($result->num_rows == 0) {
                                                    echo "</tbody></table><h6 style='text-align: center' class='text-muted'>There are no users!</h6>";
                                                } else {
                                                    while ($usersArray = $result->fetch_assoc()) {
                                                        echo "<tr>";
                                                        echo "<td>" . $usersArray["username"] . "</td>";
                                                        echo "<td>" . $usersArray["email"] . "</td>";
                                                        echo "<td>" . $usersArray["phone"] . "</td>";
                                                        if (strtolower($usersArray["username"]) == "admin") {
                                                            echo "<td><a style='background-color: #d3ad7f; border-color: #d3ad7f' class='btn btn-primary disabled' href='./delete_user.php?id=" . $usersArray["id"] . "'>Delete</a></td>";
                                                        } else {
                                                            echo "<td><a style='background-color: #d3ad7f; border-color: #d3ad7f' class='btn btn-primary' href='./delete_user.php?id=" . $usersArray["id"] . "'>Delete</a></td>";
                                                        }
                                                        echo "</tr>";
                                                        
                                                    }
                                                }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <br /><br />
                        <br /><br />

                        <div class="card">
                            <div class="card-body">
                                <div class="table-responsive-md">
                                    <h2>Products</h2>

                                    <table class="table">
                                        <caption>List of products</caption>

                                        <thead>
                                            <tr>
                                            <th scope="col">Name</th>
                                            <th scope="col">Price</th>
                                            <th scope="col">Delete</th>
                                            </tr>
                                        </thead>
                                        
                                        <tbody>
                                            <?php
                                                $result = $connection->query("SELECT * FROM products;");
                                                
                                                if ($result->num_rows == 0) {
                                                    echo "</tbody></table><h6 style='text-align: center' class='text-muted'>There are no products!</h6>";
                                                } else {
                                                    while ($productsArray = $result->fetch_assoc()) {
                                                        echo "<tr>";
                                                        echo "<td>" . $productsArray["name"] . "</td>";
                                                        echo "<td>" . $productsArray["price"] . "</td>";
                                                        echo "<td><a style='background-color: #d3ad7f; border-color: #d3ad7f' class='btn btn-primary' href='./delete_product.php?id=" . $productsArray["id"] . "'>Delete</a></td>";
                                                        echo "</tr>";
                                                    }
                                                }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <br /><br />
                        <br /><br />

                        <div class="card">
                            <div class="card-body">
                                <div class="table-responsive-md">
                                    <h2>Menu</h2>

                                    <table class="table">
                                        <caption>List of menu</caption>

                                        <thead>
                                            <tr>
                                            <th scope="col">Name</th>
                                            <th scope="col">Price</th>
                                            <th scope="col">Delete</th>
                                            </tr>
                                        </thead>
                                        
                                        <tbody>
                                            <?php
                                                $result = $connection->query("SELECT * FROM menu;");
                                                
                                                if ($result->num_rows == 0) {
                                                    echo "</tbody></table><h6 style='text-align: center' class='text-muted'>There are no menu!</h6>";
                                                } else {
                                                    while ($menuArray = $result->fetch_assoc()) {
                                                        echo "<tr>";
                                                        echo "<td>" . $menuArray["name"] . "</td>";
                                                        echo "<td>" . $menuArray["price"] . "</td>";
                                                        echo "<td><a style='background-color: #d3ad7f; border-color: #d3ad7f' class='btn btn-primary' href='./delete_menu.php?id=" . $menuArray["id"] . "'>Delete</a></td>";
                                                        echo "</tr>";
                                                    }
                                                }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        

                        <br /><br />
                        <br /><br />

                        <div class="card">
                            <div class="card-body">
                                <div class="table-responsive-md">
                                    <h2>Blogs</h2>

                                    <table class="table">
                                        <caption>List of blogs</caption>

                                        <thead>
                                            <tr>
                                            <th scope="col">Title</th>
                                            <th scope="col">Description</th>
                                            <th scope="col">Delete</th>
                                            </tr>
                                        </thead>
                                        
                                        <tbody>
                                            <?php
                                                $result = $connection->query("SELECT * FROM blogs;");
                                                
                                                if ($result->num_rows == 0) {
                                                    echo "</tbody></table><h6 style='text-align: center' class='text-muted'>There are no blogs!</h6>";
                                                } else {
                                                    while ($blogsArray = $result->fetch_assoc()) {
                                                        echo "<tr>";
                                                        echo "<td>" . $blogsArray["title"] . "</td>";
                                                        echo "<td>" . $blogsArray["description"] . "</td>";
                                                        echo "<td><a style='background-color: #d3ad7f; border-color: #d3ad7f' class='btn btn-primary' href='./delete_blog.php?id=" . $blogsArray["id"] . "'>Delete</a></td>";
                                                        echo "</tr>";
                                                    }
                                                }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <br /><br />
                        <br /><br />

                        <div class="card">
                            <div class="card-body">
                                <div class="table-responsive-md">
                                    <h2>Reviews</h2>

                                    <table class="table">
                                        <caption>List of reviews</caption>

                                        <thead>
                                            <tr>
                                            <th scope="col">Username</th>
                                            <th scope="col">Comment</th>
                                            <th scope="col">Rating</th>
                                            </tr>
                                        </thead>
                                        
                                        <tbody>
                                            <?php
                                                $result = $connection->query("SELECT * FROM reviews;");
                                                
                                                if ($result->num_rows == 0) {
                                                    echo "</tbody></table><h6 style='text-align: center' class='text-muted'>There are no reviews!</h6>";
                                                } else {
                                                    while ($reviewsArray = $result->fetch_assoc()) {
                                                        echo "<tr>";
                                                        echo "<td>" . $reviewsArray["username"] . "</td>";
                                                        echo "<td>" . $reviewsArray["comment"] . "</td>";
                                                        echo "<td>" . $reviewsArray["rating"] . "</td>";
                                                        echo "<td><a style='background-color: #d3ad7f; border-color: #d3ad7f' class='btn btn-primary' href='./delete_review.php?id=" . $reviewsArray["id"] . "'>Delete</a></td>";
                                                        echo "</tr>";
                                                    }
                                                }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <br /><br />
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
