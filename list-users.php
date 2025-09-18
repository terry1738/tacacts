<?php
// list-users.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/db.php';

try {
    $pdo = getDB();
    $stmt = $pdo->query("SELECT * FROM users ORDER BY created DESC");
    $users = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error fetching users: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin - Users</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; }
        header { background-color: #333; color: #fff; padding: 10px 20px; }
        nav a { color: #fff; margin-right: 15px; text-decoration: none; font-weight: bold; }
        nav a:hover { text-decoration: underline; }
        main { padding: 20px; }
        table { border-collapse: collapse; width: 100%; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        a.edit-link { color: blue; text-decoration: none; margin-right: 10px; }
        a.edit-link:hover { text-decoration: underline; }
    </style>
</head>
<body>
<?php include 'admin-header.php'; ?>

<main>
    <h1>Registered Users</h1>

    <?php if (count($users) > 0): ?>
        <table>
            <tr>
                <th>Acc Num</th>
                <th>Full Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= htmlspecialchars($user['id']) ?></td>
                    <td><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></td>
                    <td><?= htmlspecialchars($user['email'] ?? '') ?></td>
                    <td><?= htmlspecialchars($user['phone'] ?? '') ?></td>
                    <td><?= htmlspecialchars($user['created']) ?></td>
                    <td>
                        <a class="edit-link" href="edit-user.php?id=<?= urlencode($user['id']) ?>">Edit User</a>
                        <a class="edit-link" href="assign-service.php?user_id=<?= urlencode($user['id']) ?>">Edit Services</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p>No users found.</p>
    <?php endif; ?>
</main>

</body>
</html>

