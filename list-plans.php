<?php
// show all errors while developing
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/db.php';

try {
    $pdo = getDB();
    $stmt = $pdo->query("SELECT * FROM plans ORDER BY created DESC");
    $plans = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error fetching plans: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>List of Plans</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { border-collapse: collapse; width: 100%; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        a.edit-link { color: blue; text-decoration: none; }
        a.edit-link:hover { text-decoration: underline; }
    </style>
</head>
<body>
<?php include 'admin-header.php'; ?>

    <h1>Available Plans</h1>

    <?php if (count($plans) > 0): ?>
        <table>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Short Desc</th>
                <th>Wholesaler</th>
                <th>Wholesale</th>
                <th>Retail</th>
                <th>Billing Cycle</th>
                <th>Display</th>
                <th>Active</th>
                <th>Step 1 Action</th>
                <th>Step 2 Action</th>
                <th>Step 3 Action</th>
                <th>Step 4 Action</th>
                <th>Step 5 Action</th>
                <th>Step 6 Action</th>
                <th>Step 7 Action</th>
                <th>Created</th>
                <th>Modified</th>
                <th>Actions</th>
            </tr>
            <?php foreach ($plans as $plan): ?>
                <tr>
                    <td><?= htmlspecialchars($plan['id']) ?></td>
                    <td><?= htmlspecialchars($plan['name']) ?></td>
                    <td><?= htmlspecialchars($plan['sdesc']) ?></td>
                    <td><?= htmlspecialchars($plan['wsaler']) ?></td>
                    <td><?= htmlspecialchars(number_format($plan['wcost'], 2)) ?></td>
                    <td><?= htmlspecialchars(number_format($plan['rsell'], 2)) ?></td>
                    <td><?= htmlspecialchars($plan['billing_cycle']) ?></td>
                    <td><?= htmlspecialchars($plan['display']) ?></td>
                    <td><?= $plan['active'] ? "Yes" : "No" ?></td>
                    <td><?= htmlspecialchars($plan['step1_action']) ?></td>
                    <td><?= htmlspecialchars($plan['step2_action']) ?></td>
                    <td><?= htmlspecialchars($plan['step3_action']) ?></td>
                    <td><?= htmlspecialchars($plan['step4_action']) ?></td>
                    <td><?= htmlspecialchars($plan['step5_action']) ?></td>
                    <td><?= htmlspecialchars($plan['step6_action']) ?></td>
                    <td><?= htmlspecialchars($plan['step7_action']) ?></td>
                    <td><?= htmlspecialchars($plan['created']) ?></td>
                    <td><?= htmlspecialchars($plan['modified']) ?></td>
                    <td><a class="edit-link" href="edit-plan.php?id=<?= urlencode($plan['id']) ?>">Edit</a></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p>No plans found.</p>
    <?php endif; ?>
</body>
</html>

