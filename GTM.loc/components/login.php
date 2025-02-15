<form action="handlers/login_handler.php" method="post">
    <input type="text" name="username" placeholder="Login" required><br>
    <input type="password" name="password" placeholder="Password" required><br>
    <button type="submit" name="enter">Enter</button>
</form>

<?php
session_start();
if (isset($_SESSION['error'])): ?>
    <p style="color: red;"><?php echo $_SESSION['error'];
                            unset($_SESSION['error']); ?></p>
<?php endif; ?>

<p>If you don't have an account, register below:</p>
<a href="index.php?page=register"><button>Go to Registration</button></a>