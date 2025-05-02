<?php
session_start();
include 'db.php';


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Signup Page</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f2f2f2;
        }
        .signup-container {
            width: 400px;
            margin: 80px auto;
            padding: 30px;
            background: white;
            border-radius: 10px;
            box-shadow: 0px 0px 15px rgba(0,0,0,0.2);
        }
        .signup-container h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        .signup-container input[type="text"],
        .signup-container input[type="email"],
        .signup-container input[type="password"],
        .signup-container select {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
        .signup-container input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            border: none;
            width: 100%;
            padding: 10px;
            border-radius: 5px;
            cursor: pointer;
        }
        .signup-container input[type="submit"]:hover {
            background-color: #45a049;
        }
        .error-message {
            color: red;
            text-align: center;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>

<div class="signup-container">
    <h2>Sign Up</h2>
    
    <?php
    if (isset($error)) {
        echo "<div class='error-message'>$error</div>";
    }
    ?>

    <form method="POST" action="auth.php">
        <input type="text" name="name" placeholder="Full Name" required>
        
        <input type="email" name="email" placeholder="Email Address" required>
        
        <input type="password" name="password" placeholder="Password" required>
        <input type="hidden" name="role" value="admin" >

        <input type="submit" name="signup" value="Sign Up">
    </form>

    <p style="text-align:center; margin-top:10px;">
        Already have an account? <a href="index.php">Login here</a>
    </p>
</div>

</body>
</html>
