<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login & Sign Up (Admin/Employee)</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #f0f2f5;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
    }
    .container {
      background: white;
      padding: 30px;
      border-radius: 10px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
      width: 320px;
    }
    h2 {
      text-align: center;
      margin-bottom: 20px;
    }
    form {
      display: flex;
      flex-direction: column;
    }
    input, select {
      margin-bottom: 15px;
      padding: 10px;
      border: 1px solid #ddd;
      border-radius: 5px;
    }
    button {
      padding: 10px;
      background: #4CAF50;
      color: white;
      border: none;
      border-radius: 5px;
      cursor: pointer;
    }
    .switch {
      margin-top: 15px;
      text-align: center;
    }
    .switch a {
      color: #4CAF50;
      text-decoration: none;
    }
  </style>
</head>
<body>

<div class="container">
  <?php if (isset($_GET['action']) && $_GET['action'] == 'signup'): ?>
    <h2>Sign Up</h2>
    <form action="auth.php" method="POST">
      <input type="text" name="name" placeholder="Full Name" required>
      <input type="email" name="email" placeholder="Email" required>
      <input type="password" name="password" placeholder="Password" required>
      <button type="submit" name="signup">Sign Up</button>
    </form>
    <div class="switch">
      Already have an account? <a href="index.php">Login</a>
    </div>
  <?php else: ?>
    <h2>Login</h2>
    <form action="auth.php" method="POST">
      <input type="email" name="email" placeholder="Email" required>
      <input type="password" name="password" placeholder="Password" required>

      <button type="submit" name="login">Login</button>
    </form>
    <div class="switch">
      Don't have an account? <a href="index.php?action=signup">Sign Up</a>
    </div>
  <?php endif; ?>
</div>

</body>
</html>
