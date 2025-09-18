<?php
// show all errors while developing
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/db.php';

$pdo = getDB();

// Load available domains from DB
$domains = $pdo->query("SELECT domain_name FROM domains WHERE active = 1 ORDER BY domain_name")->fetchAll(PDO::FETCH_COLUMN);

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $email = $_POST['username'] . '@' . $_POST['domain'];

        $sql = "INSERT INTO users (
                    first_name, last_name, email,
                    phone_mobile, phone_home,
                    address_street, address_city, address_state, address_postcode, address_country
                ) VALUES (
                    :first_name, :last_name, :email,
                    :phone_mobile, :phone_home,
                    :address_street, :address_city, :address_state, :address_postcode, :address_country
                )";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':first_name'       => $_POST['first_name'] ?? null,
            ':last_name'        => $_POST['last_name'] ?? null,
            ':email'            => $email,
            ':phone_mobile'     => $_POST['phone_mobile'] ?? null,
            ':phone_home'       => $_POST['phone_home'] ?? null,
            ':address_street'   => $_POST['address_street'] ?? null,
            ':address_city'     => $_POST['address_city'] ?? null,
            ':address_state'    => $_POST['address_state'] ?? null,
            ':address_postcode' => $_POST['address_postcode'] ?? null,
            ':address_country'  => $_POST['address_country'] ?? null,
        ]);

        $message = "<p style='color:green;'>User <strong>$email</strong> created successfully.</p>";

    } catch (PDOException $e) {
        $message = "<p style='color:red;'>Error: " . $e->getMessage() . "</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Panel - Create User</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        header { margin-bottom: 20px; }
        nav a { margin-right: 15px; text-decoration: none; color: blue; }
        nav a:hover { text-decoration: underline; }
        form { max-width: 600px; }
        label { display: block; margin: 8px 0; }
        input, select { width: 100%; padding: 6px; box-sizing: border-box; }
        button { margin-top: 15px; padding: 8px 15px; }
        #checkResult { font-weight: bold; margin-left: 10px; }
    </style>
</head>
<body>

<?php include 'admin-header.php'; ?>
<?php if ($message) echo $message; ?>

<h2>Create New User</h2>

<form method="post" id="createUserForm">
    <label>Username:
        <input type="text" name="username" id="username" required>
    </label>

    <label>Domain:
        <select name="domain" id="domain" required>
            <option value="">-- Select Domain --</option>
            <?php foreach ($domains as $d): ?>
                <option value="<?= htmlspecialchars($d) ?>"><?= htmlspecialchars($d) ?></option>
            <?php endforeach; ?>
        </select>
    </label>

    <button type="button" id="checkBtn">Check Availability</button>
    <span id="checkResult"></span>

    <hr>

    <label>First Name: <input type="text" name="first_name" required></label>
    <label>Last Name: <input type="text" name="last_name" required></label>
    <label>Mobile Phone: <input type="text" name="phone_mobile"></label>
    <label>Home Phone: <input type="text" name="phone_home"></label>
    <label>Street: <input type="text" name="address_street"></label>
    <label>City: <input type="text" name="address_city"></label>
    <label>State: <input type="text" name="address_state"></label>
    <label>Postcode: <input type="text" name="address_postcode"></label>
    <label>Country: <input type="text" name="address_country" value="Australia"></label>

    <button type="submit" id="submitBtn" disabled>Create User</button>
</form>

<script>
document.getElementById('checkBtn').addEventListener('click', function() {
    const username = document.getElementById('username').value.trim();
    const domain = document.getElementById('domain').value;
    const result = document.getElementById('checkResult');
    const submitBtn = document.getElementById('submitBtn');

    result.textContent = '';
    submitBtn.disabled = true;

    if (!username || !domain) {
        result.textContent = 'Enter a username and select a domain';
        result.style.color = 'red';
        return;
    }

    fetch('check-username.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'username=' + encodeURIComponent(username) + '&domain=' + encodeURIComponent(domain)
    })
    .then(response => response.json())
    .then(data => {
        if (data.ok) {
            result.textContent = 'Available';
            result.style.color = 'green';
            submitBtn.disabled = false;
        } else {
            result.textContent = data.error;
            result.style.color = 'red';
        }
    })
    .catch(err => {
        result.textContent = 'Error checking username';
        result.style.color = 'red';
    });
});
</script>

</body>
</html>

