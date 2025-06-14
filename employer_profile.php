<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'employer') {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['company_profile'])) {
    $company_name = $_POST['company_name'];
    $company_description = $_POST['company_description'];
    $location = $_POST['location'];

    $stmt = $pdo->prepare("INSERT INTO employer_profiles (user_id, company_name, company_description, location) VALUES (?, ?, ?, ?)");
    $stmt->execute([$_SESSION['user_id'], $company_name, $company_description, $location]);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['job_post'])) {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $category = $_POST['category'];
    $location = $_POST['location'];
    $salary_range = $_POST['salary_range'];
    $job_type = $_POST['job_type'];
    $experience_level = $_POST['experience_level'];

    $stmt = $pdo->prepare("INSERT INTO jobs (employer_id, title, description, category, location, salary_range, job_type, experience_level) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$_SESSION['user_id'], $title, $description, $category, $location, $salary_range, $job_type, $experience_level]);
}

$stmt = $pdo->prepare("SELECT * FROM employer_profiles WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$profile = $stmt->fetch(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("SELECT * FROM jobs WHERE employer_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employer Profile</title>
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
        .form-container, .job-card {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 1rem;
        }
        input, select, textarea, button {
            width: 100%;
            padding: 0.8rem;
            margin: 0.5rem 0;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        button {
            background: #3498db;
            color: white;
            border: none;
            cursor: pointer;
        }
        button:hover {
            background: #2980b9;
        }
        @media (max-width: 768px) {
            .form-container, .job-card {
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Employer Profile</h2>
        <?php if (!$profile): ?>
            <div class="form-container">
                <h3>Create Company Profile</h3>
                <form method="POST">
                    <input type="text" name="company_name" placeholder="Company Name" required>
                    <textarea name="company_description" placeholder="Company Description" required></textarea>
                    <input type="text" name="location" placeholder="Location" required>
                    <button type="submit" name="company_profile">Save Profile</button>
                </form>
            </div>
        <?php else: ?>
            <div class="form-container">
                <h3>Company Profile</h3>
                <p><strong>Name:</strong> <?php echo htmlspecialchars($profile['company_name']); ?></p>
                <p><strong>Description:</strong> <?php echo htmlspecialchars($profile['company_description']); ?></p>
                <p><strong>Location:</strong> <?php echo htmlspecialchars($profile['location']); ?></p>
            </div>
        <?php endif; ?>
        <div class="form-container">
            <h3>Post a Job</h3>
            <form method="POST">
                <input type="text" name="title" placeholder="Job Title" required>
                <textarea name="description" placeholder="Job Description" required></textarea>
                <input type="text" name="category" placeholder="Category" required>
                <input type="text" name="location" placeholder="Location" required>
                <input type="text" name="salary_range" placeholder="Salary Range" required>
                <select name="job_type" required>
                    <option value="full_time">Full Time</option>
                    <option value="part_time">Part Time</option>
                    <option value="remote">Remote</option>
                </select>
                <input type="text" name="experience_level" placeholder="Experience Level" required>
                <button type="submit" name="job_post">Post Job</button>
            </form>
        </div>
        <h3>Your Jobs</h3>
        <?php foreach ($jobs as $job): ?>
            <div class="job-card">
                <h4><?php echo htmlspecialchars($job['title']); ?></h4>
                <p><?php echo htmlspecialchars($job['description']); ?></p>
                <p><strong>Category:</strong> <?php echo htmlspecialchars($job['category']); ?></p>
                <p><strong>Location:</strong> <?php echo htmlspecialchars($job['location']); ?></p>
                <p><strong>Salary:</strong> <?php echo htmlspecialchars($job['salary_range']); ?></p>
                <p><strong>Type:</strong> <?php echo htmlspecialchars($job['job_type']); ?></p>
            </div>
        <?php endforeach; ?>
    </div>
    <script>
        function redirectTo(page) {
            window.location.href = page;
        }
    </script>
</body>
</html>
