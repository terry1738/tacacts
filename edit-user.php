<?php include 'admin-header.php'; ?>

<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/db.php';

$pdo = getDB();

// Get user id
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid user ID");
}

$id = (int)$_GET['id'];

// Fetch existing user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();

if (!$user) {
    die("User not found");
}

// Handle form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fields = [
        'first_name', 'last_name', 'email',
        'phone_home', 'phone_mobile',
        'address_street', 'address_city', 'address_state', 'address_postcode'
    ];

    $updates = [];
    $values = [];

    foreach ($fields as $field) {
        $updates[] = "$field = ?";
        $values[] = $_POST[$field] ?? '';
    }

    $values[] = $id;

    $sql = "UPDATE users SET " . implode(', ', $updates) . " WHERE id = ?";
    $stmt = $pdo->prepare($sql);

    try {
        $stmt->execute($values);
        $message = "User updated successfully.";
        // reload fresh data
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $user = $stmt->fetch();
    } catch (PDOException $e) {
        $error = "Update failed: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit User</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        form { max-width: 600px; margin: auto; }
        label { display: block; margin-top: 12px; }
        input[type="text"], input[type="email"] {
            width: 100%; padding: 8px; margin-top: 4px; box-sizing: border-box;
        }
        .actions {
            margin-top: 20px; display: flex; justify-content: space-between;
        }
        .btn {
            background: #007BFF; color: #fff; padding: 10px 16px;
            text-decoration: none; border: none; border-radius: 4px; cursor: pointer;
        }
        .btn:hover { background: #0056b3; }
        .message { margin-top: 15px; color: green; }
        .error { margin-top: 15px; color: red; }
    </style>
</head>
<body>
    <h1>Edit User</h1>
    <form method="post">
        <label>First Name
            <input type="text" name="first_name" value="<?= htmlspecialchars($user['first_name']) ?>" required>
        </label>
        <label>Last Name
            <input type="text" name="last_name" value="<?= htmlspecialchars($user['last_name']) ?>" required>
        </label>
        <label>Email
            <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
        </label>
        <label>Phone (Home)
            <input type="text" name="phone_home" value="<?= htmlspecialchars($user['phone_home']) ?>">
        </label>
        <label>Phone (Mobile)
            <input type="text" name="phone_mobile" value="<?= htmlspecialchars($user['phone_mobile']) ?>">
        </label>
        <label>Street Address
            <input type="text" name="address_street" value="<?= htmlspecialchars($user['address_street']) ?>">
        </label>
        <label>City
            <input type="text" name="address_city" value="<?= htmlspecialchars($user['address_city']) ?>">
        </label>
        <label>State
            <input type="text" name="address_state" value="<?= htmlspecialchars($user['address_state']) ?>">
        </label>
        <label>Postcode
            <input type="text" name="address_postcode" value="<?= htmlspecialchars($user['address_postcode']) ?>">
        </label>

        <div class="actions">
            <button class="btn" type="submit">Save Changes</button>
            <a class="btn" href="list-users.php" style="background:#6c757d;">Back</a>
        </div>

        <?php if (!empty($message)): ?>
            <div class="message"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        <?php if (!empty($error)): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
    </form>
</body>
</html>

