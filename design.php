<?php
// ============================================
// admin.php - Simple Admin Dashboard (Optional)
// ============================================
// This file provides a simple web interface to view submitted contacts
// Protect this file with .htaccess or rename it for security

session_start();

// Simple authentication - CHANGE THIS PASSWORD!
$admin_password = 'BKK2025Secure!';

// Check login
if (!isset($_SESSION['admin_logged_in']) && isset($_POST['password'])) {
    if ($_POST['password'] === $admin_password) {
        $_SESSION['admin_logged_in'] = true;
    }
}

if (!isset($_SESSION['admin_logged_in'])) {
    // Show login form
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Admin Login - BKK Technical</title>
        <style>
            body { font-family: Arial; background: #000; color: #fff; display: flex; justify-content: center; align-items: center; height: 100vh; }
            .login-box { background: #111; padding: 40px; border-radius: 20px; border: 1px solid #e67e22; }
            input { padding: 10px; margin: 10px 0; width: 100%; background: #222; border: 1px solid #e67e22; color: #fff; border-radius: 8px; }
            button { background: #e67e22; color: #000; padding: 10px 20px; border: none; border-radius: 8px; cursor: pointer; }
        </style>
    </head>
    <body>
        <div class="login-box">
            <h2>Admin Login</h2>
            <form method="POST">
                <input type="password" name="password" placeholder="Enter Password" required>
                <button type="submit">Login</button>
            </form>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Database connection (configure your credentials)
$servername = "localhost";
$username = "your_username";
$password = "your_password";
$dbname = "bkk_contacts";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Handle delete request
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM contacts WHERE id = $id");
    header("Location: admin.php");
    exit;
}

// Handle status update
if (isset($_GET['status']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $status = $_GET['status'];
    $conn->query("UPDATE contacts SET status = '$status' WHERE id = $id");
}

// Fetch all contacts
$result = $conn->query("SELECT * FROM contacts ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard - BKK Technical</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #0a0a0a; color: #e0e0e0; padding: 20px; }
        .container { max-width: 1400px; margin: 0 auto; }
        h1 { color: #e67e22; margin-bottom: 20px; border-left: 4px solid #e67e22; padding-left: 15px; }
        .stats { display: flex; gap: 20px; margin-bottom: 30px; flex-wrap: wrap; }
        .stat-card { background: #111; padding: 20px; border-radius: 16px; border: 1px solid #e67e22; min-width: 150px; text-align: center; }
        .stat-card .number { font-size: 2rem; font-weight: bold; color: #e67e22; }
        .stat-card .label { font-size: 0.8rem; color: #888; }
        table { width: 100%; border-collapse: collapse; background: #0c0c0c; border-radius: 16px; overflow: hidden; }
        th, td { padding: 15px; text-align: left; border-bottom: 1px solid #222; }
        th { background: #e67e22; color: #000; font-weight: 600; }
        tr:hover { background: #151515; }
        .status { padding: 4px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: bold; display: inline-block; }
        .status-new { background: #e67e22; color: #000; }
        .status-read { background: #2c3e50; color: #fff; }
        .status-replied { background: #27ae60; color: #fff; }
        .btn { padding: 4px 12px; border-radius: 8px; text-decoration: none; font-size: 0.75rem; margin: 0 2px; display: inline-block; }
        .btn-delete { background: #c0392b; color: #fff; }
        .btn-read { background: #3498db; color: #fff; }
        .btn-replied { background: #27ae60; color: #fff; }
        .logout { background: #333; color: #fff; padding: 10px 20px; border-radius: 8px; text-decoration: none; float: right; }
        .message-preview { max-width: 300px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        @media (max-width: 768px) { th, td { font-size: 12px; padding: 8px; } }
    </style>
</head>
<body>
    <div class="container">
        <a href="?logout=1" class="logout" onclick="return confirm('Logout?')">Logout</a>
        <h1>📋 BKK Technical - Contact Submissions</h1>
        
        <?php
        // Handle logout
        if (isset($_GET['logout'])) {
            session_destroy();
            header("Location: admin.php");
            exit;
        }
        
        // Get statistics
        $total = $conn->query("SELECT COUNT(*) as count FROM contacts")->fetch_assoc()['count'];
        $new = $conn->query("SELECT COUNT(*) as count FROM contacts WHERE status = 'new'")->fetch_assoc()['count'];
        ?>
        
        <div class="stats">
            <div class="stat-card"><div class="number"><?php echo $total; ?></div><div class="label">Total Messages</div></div>
            <div class="stat-card"><div class="number"><?php echo $new; ?></div><div class="label">Unread</div></div>
        </div>
        
        <table>
            <thead>
                <tr><th>ID</th><th>Date</th><th>Name</th><th>Email</th><th>Phone</th><th>Service</th><th>Message</th><th>Status</th><th>Actions</th></tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo date('Y-m-d H:i', strtotime($row['created_at'])); ?></td>
                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                    <td><?php echo htmlspecialchars($row['phone'] ?? '-'); ?></td>
                    <td><?php echo htmlspecialchars($row['service'] ?? '-'); ?></td>
                    <td class="message-preview" title="<?php echo htmlspecialchars($row['message']); ?>"><?php echo htmlspecialchars(substr($row['message'], 0, 50)) . '...'; ?></td>
                    <td>
                        <span class="status status-<?php echo $row['status']; ?>"><?php echo $row['status']; ?></span>
                    </td>
                    <td>
                        <a href="?status=read&id=<?php echo $row['id']; ?>" class="btn btn-read">Mark Read</a>
                        <a href="?status=replied&id=<?php echo $row['id']; ?>" class="btn btn-replied">Replied</a>
                        <a href="?delete=<?php echo $row['id']; ?>" class="btn btn-delete" onclick="return confirm('Delete this message?')">Delete</a>
                    </td>
                </tr>
                <?php endwhile; ?>
                <?php if ($result->num_rows === 0): ?>
                <tr><td colspan="9" style="text-align: center;">No messages yet</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
<?php $conn->close(); ?>