<form action="" method="post">
    <input type="text" name="username" placeholder="User Name" required><br>
    <!--<input type="tel" name="phone" placeholder="Phone Number" required><br>-->
    <input type="email" name="email" placeholder="Email"><br>
    <input type="password" name="password" placeholder="Password" required><br>
    <input type="password" name="confirm_password" placeholder="Confirm Password" required><br>
    <button type="submit" name="register">Register</button>
</form>


<?php
require_once 'functions/functions.php';

if (isset($_POST['register']) && isset($_POST['username']) && isset($_POST['email']) && isset($_POST['password']) && isset($_POST['confirm_password'])) {

    $username = htmlentities($_POST['username']);
    $email = htmlentities($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    registerUser($username, $email, $password, $confirm_password);

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    try {
        $conn = connectDB();
        //echo "DB connect established<br>";
        $sql = "INSERT INTO `users` (`username`, `email`,`password`) VALUES (?,?,?)";
        $stmt = $conn->prepare($sql);
        $rowsAdded = $stmt->execute([$username, $email, $hashed_password]);

        if ($rowsAdded > 0) {
            echo "User created succesfully: <br> name = $username <br> email = $email";
        }
    } catch (PDOException $e) {
        echo "Connection failed: " . $e->getMessage();
    }
}
