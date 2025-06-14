<?php
session_start();
require_once 'db.php';

$category = $_GET['category'] ?? '';
$location = $_GET['location'] ?? '';
$job_type = $_GET['job_type'] ?? '';

$query = "SELECT * FROM jobs WHERE 1=1";
$params = [];

if ($category) {
    $query .= " AND category = ?";
    $params[] = $category;
}
if ($location) {
    $query .= " AND location = ?";
    $params[] = $location;
}
if ($job_type) {
    $query .= " AND job_type = ?";
    $params[] = $job_type;
}

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['apply'])) {
    $job_id = $_POST['job_id'];
    $stmt = $pdo->prepare("SELECT resume_path FROM job_seeker_profiles WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $profile = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($profile && $profile['resume_path']) {
        $stmt = $pdo->prepare("INSERT INTO applications (job_id, job_seeker_id, resume_path) VALUES (?, ?, ?)");
        $stmt->execute([$job_id, $_SESSION['user_id'], $profile['resume_path']]);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Search</title>
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
        input, select, button {
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
        <h2>Search Jobs</h2>
        <div class="form-container">
            <form method="GET">
                <input type="text" name="category" placeholder="Category" value="<?php echo htmlspecialchars($category); ?>">
                <input type="text" name="location" placeholder="Location" value="<?php echo htmlspecialchars($location); ?>">
                <select name="job_type">
                    <option value="">Job Type</option>
                    <option value="full_time" <?php if ($job_type == 'full_time') echo 'selected'; ?>>Full Time</option>
                    <option value="part_time" <?php if ($job_type == 'part_time') echo 'selected'; ?>>Part Time</option>
                    <option value="remote" <?php if ($job_type == 'remote') echo 'selected'; ?>>Remote</option>
                </select>
                <button type="submit">Search</button>
            </form>
        </div>
        <?php foreach ($jobs as $job): ?>
            <div class="job-card">
                <h3><?php echo htmlspecialchars($job['title']); ?></h3>
                <p><?php echo htmlspecialchars($job['description']); ?></p>
                <p><strong>Location:</strong> <?php echo htmlspecialchars($job['location']); ?></p>
                <p><strong>Salary:</strong> <?php echo htmlspecialchars($job['salary_range']); ?></p>
                <p><strong>Type:</strong> <?php echo htmlspecialchars($job['job_type']); ?></p>
                <?php if (isset($_SESSION['user_id']) && $_SESSION['user_type'] == 'job_seeker'): ?>
                    <form method="POST">
                        <input type="hidden" name="job_id" value="<?php echo $job['id']; ?>">
                        <button type="submit" name="apply">Apply</button>
                    </form>
                <?php endif; ?>
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
