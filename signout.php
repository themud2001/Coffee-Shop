<?php
    session_start();
    unset($_SESSION["username"]);
    unset($_SESSION["email"]);
    unset($_SESSION["phone"]);
?>

<html>
    <script>
        localStorage.removeItem("fav");
        localStorage.removeItem("checkoutTime");
        window.location.href = "./";
    </script>
</html>