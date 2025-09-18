<?php include 'admin-header.php'; ?>

<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/db.php';

$pdo = getDB();

if (!isset($_GET['user_id']) || !is_numeric($_GET['user_id'])) {
    die("Invalid user ID");
}

$user_id = (int)$_GET['user_id'];

// Get user info
$stmt = $pdo->prepare("SELECT first_name, last_name FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
if (!$user) {
    die("User not found");
}

// Handle assign new plan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['plan_id'])) {
    $plan_id = (int)$_POST['plan_id'];

    // prevent duplicate assignment
    $check = $pdo->prepare("SELECT id FROM user_services WHERE user_id=? AND plan_id=?");
    $check->execute([$user_id, $plan_id]);

    if (!$check->fetch()) {
        $stmt = $pdo->prepare("INSERT INTO user_services (user_id, plan_id, status, start_date) VALUES (?, ?, 'active', NOW())");
        $stmt->execute([$user_id, $plan_id]);
        $message = "Service assigned successfully.";
    } else {
        $error = "This service is already assigned to the user.";
    }
}

// Handle remove plan
if (isset($_GET['remove_id']) && is_numeric($_GET['remove_id'])) {
    $remove_id = (int)$_GET['remove_id'];
    $stmt = $pdo->prepare("DELETE FROM user_services WHERE id = ? AND user_id = ?");
    $stmt->execute([$remove_id, $user_id]);
    $message = "Service removed.";
}

// Get all available plans
$plans = $pdo->query("SELECT id, name FROM plans WHERE active = 1 ORDER BY name ASC")->fetchAll();

// Get current services
$stmt = $pdo->prepare("
    SELECT us.id, p.name, us.start_date, us.end_date, us.status
    FROM user_services us
    JOIN plans p ON us.plan_id = p.id
    WHERE us.user_id = ?
    ORDER BY us.start_date DESC
");
$stmt->execute([$user_id]);
$current_services = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Assign Services to <?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { border-collapse: collapse; width: 100%; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        .form-row { margin-top: 20px; display: flex; gap: 10px; align-items: center; }
        select, button { padding: 8px; }
        .btn { background: #007BFF; color: #fff; padding: 8px 12px; border: none; border-radius: 4px; cursor: pointer; }
        .btn:hover { background: #0056b3; }
        .btn-remove { background: #dc3545; }
        .btn-remove:hover { background: #b02a37; }
        .message { margin-top: 15px; color: green; }
        .error { margin-top: 15px; color: red; }
    </style>
</head>
<body>
    <h1>Assign Services to <?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></h1>

    <a href="list-users.php" style="text-decoration:none;color:#007BFF;">&larr; Back to Users</a>

    <?php if (!empty($message)): ?>
        <div class="message"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="form-row">
        <form method="post">
            <label for="plan_id">Add a service:</label>
            <select name="plan_id" id="plan_id" required>
                <option value="">-- Select Plan --</option>
                <?php foreach ($plans as $plan): ?>
                    <option value="<?= $plan['id'] ?>"><?= htmlspecialchars($plan['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn">Assign</button>
        </form>
    </div>

    <h2>Current Services</h2>
    <?php if (count($current_services) > 0): ?>
        <table>
            <tr>
                <th>Service</th>
                <th>Status</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Actions</th>
            </tr>
            <?php foreach ($current_services as $svc): ?>
                <tr>
                    <td><?= htmlspecialchars($svc['name']) ?></td>
                    <td><?= htmlspecialchars($svc['status']) ?></td>
                    <td><?= htmlspecialchars($svc['start_date']) ?></td>
                    <td><?= htmlspecialchars($svc['end_date'] ?? '') ?></td>
                    <td>
                        <a class="btn btn-remove" href="?user_id=<?= $user_id ?>&remove_id=<?= $svc['id'] ?>" onclick="return confirm('Remove this service?');">Remove</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p>No services currently assigned.</p>
    <?php endif; ?>
</body>
</html>

