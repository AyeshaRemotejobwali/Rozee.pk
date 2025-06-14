<?php
session_start();
require_once 'db.php';

$stmt = $pdo->query("SELECT * FROM jobs ORDER BY posted_at DESC LIMIT 6");
$jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Portal - Home</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        }
        header {
            background: #2c3e50;
            color: white;
            padding: 1rem;
            text-align: center;
        }
        nav {
            display: flex;
            justify-content: space-around;
            background: #34495e;
            padding: 1rem;
        }
        nav a {
            color: white;
            text-decoration: none;
            font-size: 1.1rem;
        }
        nav a:hover {
            color: #3498db;
        }
        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        .job-card {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            margin: 1rem 0;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s;
        }
        .job-card:hover {
            transform: translateY(-5px);
        }
        .categories {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            justify-content: center;
        }
        .category {
            background: #3498db;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            cursor: pointer;
        }
        .category:hover {
            background: #2980b9;
        }
        @media (max-width: 768px) {
            nav {
                flex-direction: column;
                gap: 0.5rem;
            }
            .job-card {
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
    <header>
        <h1>Job Portal</h1>
    </header>
    <nav>
        <a href="#" onclick="redirectTo('index.php')">Home</a>
        <a href="#" onclick="redirectTo('job_search.php')">Search Jobs</a>
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="#" onclick="redirectTo('<?php echo $_SESSION['user_type'] == 'employer' ? 'employer_profile.php' : 'job_seeker_profile.php'; ?>')">Profile</a>
            <a href="#" onclick="redirectTo('logout.php')">Logout</a>
        <?php else: ?>
            <a href="#" onclick="redirectTo('login.php')">Login</a>
            <a href="#" onclick="redirectTo('signup.php')">Sign Up</a>
        <?php endif; ?>
    </nav>
    <div class="container">
        <h2>Featured Jobs</h2>
        <?php foreach ($jobs as $job): ?>
            <div class="job-card">
                <h3><?php echo htmlspecialchars($job['title']); ?></h3>
                <p><?php echo htmlspecialchars($job['description']); ?></p>
                <p><strong>Location:</strong> <?php echo htmlspecialchars($job['location']); ?></p>
                <p><strong>Salary:</strong> <?php echo htmlspecialchars($job['salary_range']); ?></p>
                <p><strong>Type:</strong> <?php echo htmlspecialchars($job['job_type']); ?></p>
            </div>
        <?php endforeach; ?>
        <h2>Trending Categories</h2>
        <div class="categories">
            <div class="category">IT</div>
            <div class="category">Marketing</div>
            <div class="category">Finance</div>
            <div class="category">Engineering</div>
        </div>
    </div>
    <script>
        function redirectTo(page) {
            window.location.href = page;
        }
    </script>
</body>
</html>
