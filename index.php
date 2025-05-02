


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
  <style>
    * {
      box-sizing: border-box;
    }

    body {
      margin: 0;
      font-family: 'Inter', sans-serif;
      background: #f8f9fc;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
    }

    .wrapper {
      display: flex;
      width: 900px;
      background: white;
      border-radius: 16px;
      box-shadow: 0 12px 25px rgba(0, 0, 0, 0.1);
      overflow: hidden;
    }

    .left {
      flex: 1;
      padding: 60px 40px;
      display: flex;
      flex-direction: column;
      justify-content: center;
    }

    .left h2 {
      margin-bottom: 25px;
      font-size: 32px;
      font-weight: 700;
      color: #222;
    }

    form input {
      margin-bottom: 15px;
      padding: 12px 14px;
      border: 1px solid #ccc;
      border-radius: 8px;
      font-size: 15px;
      width: 100%;
    }

    form input:focus {
      border-color: #66a6ff;
      outline: none;
    }

    button {
      padding: 12px;
      background: #66a6ff;
      color: white;
      border: none;
      border-radius: 8px;
      font-size: 15px;
      font-weight: 600;
      cursor: pointer;
      width: 100%;
    }

    button:hover {
      background: #4a90e2;
    }

    .right {
      flex: 1;
      background: #f2f4f8;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .right img {
      max-width: 90%;
      height: auto;
    }

    .switch {
      margin-top: 20px;
      text-align: center;
      font-size: 14px;
    }

    .switch a {
      color: #4a90e2;
      text-decoration: none;
      font-weight: 600;
    }

    .switch a:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>

<div class="wrapper">
  <div class="left">
    <h2>Login</h2>
    <form action="auth.php" method="POST">
      <input type="email" name="email" placeholder="Your Email" required>
      <input type="password" name="password" placeholder="Password" required>
      <button type="submit" name="login">Login</button>
    </form>
    <!--<div class="switch">
      Don't have an account? <a href="signup.php">Sign up</a>
    </div>-->
  </div>
  <div class="right">
    <img src="images/face-scan.png" alt="Desk Illustration">
  </div>
</div>



</body>
</html>

