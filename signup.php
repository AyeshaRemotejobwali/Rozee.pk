<?php
session_start();
require_once 'db.php';

// Initialize error variable
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validate form inputs
    if (empty($_POST['username']) || empty($_POST['email']) || empty($_POST['password']) || empty($_POST['user_type'])) {
        $error = "All fields are required.";
    } else {
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $password = password_hash(trim($_POST['password']), PASSWORD_BCRYPT);
        $user_type = $_POST['user_type'];

        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Invalid email format.";
        } elseif (!in_array($user_type, ['employer', 'job_seeker'])) {
            $error = "Invalid user type.";
        } else {
            try {
                // Check for duplicate username or email
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ? OR email = ?");
                $stmt->execute([$username, $email]);
                if ($stmt->fetchColumn() > 0) {
                    $error = "Username or email already exists.";
                } else {
                    // Insert new user
                    $stmt = $pdo->prepare("INSERT INTO users (username, email, password, user_type) VALUES (?, ?, ?, ?)");
                    if ($stmt->execute([$username, $email, $password, $user_type])) {
                        // Redirect to login page on success
                        echo "<script>window.location.href = 'login.php';</script>";
                        exit;
                    } else {
                        $error = "Failed to register user. Please try again.";
                    }
                }
            } catch (PDOException $e) {
                $error = "Database error: " . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .form-container {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }
        input, select, button {
            width: 100%;
            padding: 0.8rem;
            margin: 0.5rem 0;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
        }
        button {
            background: #3498db;
            color: white;
            border: none;
            cursor: pointer;
            transition: background 0.3s;
        }
        button:hover {
            background: #2980b9;
        }
        .error {
            color: red;
            text-align: center;
            margin-bottom: 1rem;
        }
        a {
            color: #3498db;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
        @media (max-width: 768px) {
            .form-container {
                padding: 1rem;
                margin: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Sign Up</h2>
        <?php if (!empty($error)): ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
        <form method="POST" action="">
            <input type="text" name="username" placeholder="Username" value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" required>
            <input type="email" name="email" placeholder="Email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
            <input type="password" name="password" placeholder="Password" required>
            <select name="user_type" required>
                <option value="" disabled selected>Select User Type</option>
                <option value="employer" <?php echo (isset($_POST['user_type']) && $_POST['user_type'] == 'employer') ? 'selected' : ''; ?>>Employer</option>
                <option value="job_seeker" <?php echo (isset($_POST['user_type']) && $_POST['user_type'] == 'job_seeker') ? 'selected' : ''; ?>>Job Seeker</option>
            </select>
            <button type="submit">Sign Up</button>
        </form>
        <p>Already have an account? <a href="#" onclick="redirectTo('login.php')">Login</a></p>
    </div>
    <script>
        function redirectTo(page) {
            window.location.href = page;
        }
    </script>
</body>
</html>
