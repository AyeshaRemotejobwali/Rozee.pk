<?php
session_start();
require_once 'db.php';

// Initialize error variable
$error = '';

// Check if user is logged in and is a job seeker
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type']) || $_SESSION['user_type'] != 'job_seeker') {
    echo "<script>window.location.href = 'login.php';</script>";
    exit;
}

// Handle profile creation
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['profile'])) {
    if (empty($_POST['full_name']) || empty($_POST['skills'])) {
        $error = "All fields are required.";
    } else {
        $full_name = trim($_POST['full_name']);
        $skills = trim($_POST['skills']);
        
        try {
            // Check if profile already exists
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM job_seeker_profiles WHERE user_id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            if ($stmt->fetchColumn() > 0) {
                $error = "Profile already exists.";
            } else {
                // Insert new profile
                $stmt = $pdo->prepare("INSERT INTO job_seeker_profiles (user_id, full_name, skills) VALUES (?, ?, ?)");
                if ($stmt->execute([$_SESSION['user_id'], $full_name, $skills])) {
                    $success = "Profile created successfully.";
                } else {
                    $error = "Failed to create profile.";
                }
            }
        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage();
        }
    }
}

// Handle resume upload
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['resume'])) {
    if ($_FILES['resume']['error'] !== UPLOAD_ERR_OK) {
        $error = "Resume upload failed: " . $_FILES['resume']['error'];
    } else {
        $upload_dir = 'uploads/';
        if (!is_dir($upload_dir)) {
            if (!mkdir($upload_dir, 0777, true)) {
                $error = "Failed to create upload directory.";
            }
        }
        $resume_path = $upload_dir . time() . '_' . basename($_FILES['resume']['name']);
        if (move_uploaded_file($_FILES['resume']['tmp_name'], $resume_path)) {
            try {
                $stmt = $pdo->prepare("UPDATE job_seeker_profiles SET resume_path = ? WHERE user_id = ?");
                if ($stmt->execute([$resume_path, $_SESSION['user_id']])) {
                    $success = "Resume uploaded successfully.";
                } else {
                    $error = "Failed to update resume path.";
                }
            } catch (PDOException $e) {
                $error = "Database error: " . $e->getMessage();
            }
        } else {
            $error = "Failed to move uploaded file.";
        }
    }
}

// Fetch profile data
try {
    $stmt = $pdo->prepare("SELECT * FROM job_seeker_profiles WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $profile = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Seeker Profile</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            margin: 0;
        }
        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        .form-container {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 1rem;
        }
        input, textarea, button {
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
        .success {
            color: green;
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
    <div class="container">
        <h2>Job Seeker Profile</h2>
        <?php if (!empty($error)): ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
        <?php if (isset($success)): ?>
            <p class="success"><?php echo htmlspecialchars($success); ?></p>
        <?php endif; ?>
        <?php if (!$profile): ?>
            <div class="form-container">
                <h3>Create Profile</h3>
                <form method="POST" action="">
                    <input type="text" name="full_name" placeholder="Full Name" value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>" required>
                    <textarea name="skills" placeholder="Skills (comma-separated)" required><?php echo isset($_POST['skills']) ? htmlspecialchars($_POST['skills']) : ''; ?></textarea>
                    <button type="submit" name="profile">Save Profile</button>
                </form>
            </div>
        <?php else: ?>
            <div class="form-container">
                <h3>Your Profile</h3>
                <p><strong>Name:</strong> <?php echo htmlspecialchars($profile['full_name']); ?></p>
                <p><strong>Skills:</strong> <?php echo htmlspecialchars($profile['skills']); ?></p>
                <p><strong>Resume:</strong> <?php echo $profile['resume_path'] ? htmlspecialchars($profile['resume_path']) : 'Not uploaded'; ?></p>
            </div>
        <?php endif; ?>
        <div class="form-container">
            <h3>Upload Resume</h3>
            <form method="POST" enctype="multipart/form-data">
                <input type="file" name="resume" accept=".pdf" required>
                <button type="submit">Upload</button>
            </form>
        </div>
        <p><a href="#" onclick="redirectTo('index.php')">Back to Home</a></p>
    </div>
    <script>
        function redirectTo(page) {
            window.location.href = page;
        }
    </script>
</body>
</html>
