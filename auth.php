    <?php
    session_start();
    include 'db.php'; // connect to database

    if (isset($_POST['signup'])) {
        $name = htmlspecialchars($_POST['name']);
        $email = htmlspecialchars($_POST['email']);
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $role = 'admin'; 

        // Check if email already exists
        $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            echo "<h2>Email already registered!</h2>";
            echo '<p><a href="signup.php">Try Again</a></p>';
        } else {
            // Insert new user
            $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $name, $email, $password, $role);
            if ($stmt->execute()) {
                echo "<h2>Sign Up Successful!</h2>";
                echo "<p>Welcome, $name ($role)</p>";
                echo '<p><a href="index.php">Go to Login</a></p>';
            } else {
                echo "Something went wrong.";
            }
        }

    } elseif (isset($_POST['login'])) {
        $email = htmlspecialchars($_POST['email']);
        $password = $_POST['password'];

        // Find user by email
        $stmt = $conn->prepare("SELECT id, name, password, role FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            if (password_verify($password, $user['password'])) {
                $_SESSION['user'] = [
                    'id' => $user['id'],
                    'name' => $user['name'],
                    'role' => $user['role']
                ];

                // Correct redirection based on role
                if ($user['role'] === 'admin') {
                    header("Location: admin_dashboard.php");  // (✅ correct dashboard name)
                    exit();
                } elseif ($user['role'] === 'user') {
                    header("Location: user_dashboard.php");   // (✅ correct dashboard name)
                    exit();
                } else {
                    echo "<h2>Invalid user role: " . htmlspecialchars($user['role']) . "</h2>";
                    echo '<p><a href="index.php">Try Again</a></p>';
                }

            } else {
                echo "<h2>Incorrect password!</h2>";
                echo '<p><a href="index.php">Try Again</a></p>';
            }
        } else {
            echo "<h2>User not found!</h2>";
            echo '<p><a href="index.php">Try Again</a></p>';
        }

    } else {
        echo "Invalid access.";
    }
    ?>
