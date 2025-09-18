<?php include 'admin-header.php'; ?>

<?php

// show all errors while developing
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'db.php'; // gives us getDB()

$pdo = getDB();

// check if an ID was passed
if (!isset($_GET['id'])) {
    die("No plan ID provided.");
}

$id = (int) $_GET['id'];

// fetch existing plan
try {
    $stmt = $pdo->prepare("SELECT * FROM plans WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $plan = $stmt->fetch();
    if (!$plan) {
        die("Plan not found.");
    }
} catch (PDOException $e) {
    die("Error fetching plan: " . $e->getMessage());
}

// update if form submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $sql = "UPDATE plans SET
                    name = :name,
                    description = :description,
                    base_price = :base_price,
                    billing_cycle = :billing_cycle,
                    wsaler = :wsaler,
                    wcost = :wcost,
                    rsell = :rsell,
                    display = :display,
                    sdesc = :sdesc,
                    ldesc = :ldesc,
                    step1_limit = :step1_limit, step1_cost = :step1_cost, step1_action = :step1_action,
                    step2_limit = :step2_limit, step2_cost = :step2_cost, step2_action = :step2_action,
                    step3_limit = :step3_limit, step3_cost = :step3_cost, step3_action = :step3_action,
                    step4_limit = :step4_limit, step4_cost = :step4_cost, step4_action = :step4_action,
                    step5_limit = :step5_limit, step5_cost = :step5_cost, step5_action = :step5_action,
                    step6_limit = :step6_limit, step6_cost = :step6_cost, step6_action = :step6_action,
                    step7_limit = :step7_limit, step7_cost = :step7_cost, step7_action = :step7_action
                WHERE id = :id";

        $stmt = $pdo->prepare($sql);

        $params = [
            ':id'            => $id,
            ':name'          => $_POST['name'] ?? null,
            ':description'   => $_POST['description'] ?? null,
            ':base_price'    => $_POST['base_price'] ?? 0,
            ':billing_cycle' => $_POST['billing_cycle'] ?? 'monthly',
            ':wsaler'        => $_POST['wsaler'] ?? null,
            ':wcost'         => $_POST['wcost'] ?? 0,
            ':rsell'         => $_POST['rsell'] ?? 0,
            ':display'       => $_POST['display'] ?? 'hide',
            ':sdesc'         => $_POST['sdesc'] ?? null,
            ':ldesc'         => $_POST['ldesc'] ?? null,
        ];

        // add step fields dynamically
        for ($i = 1; $i <= 7; $i++) {
            $params[":step{$i}_limit"]  = $_POST["step{$i}_limit"] ?? null;
            $params[":step{$i}_cost"]   = $_POST["step{$i}_cost"] ?? null;
            $params[":step{$i}_action"] = $_POST["step{$i}_action"] ?? 'disable';
        }

        $stmt->execute($params);

        echo "<p>Plan updated successfully.</p>";
        // refresh plan data
        $stmt = $pdo->prepare("SELECT * FROM plans WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $plan = $stmt->fetch();

    } catch (PDOException $e) {
        echo "<p>Error: " . $e->getMessage() . "</p>";
    }
}
?>

<!-- Edit Form -->
<form method="post">
    <label>Plan Name: <input type="text" name="name" value="<?= htmlspecialchars($plan['name']) ?>"></label><br>
    <label>Description: <textarea name="description"><?= htmlspecialchars($plan['description']) ?></textarea></label><br>
    <label>Base Price: <input type="text" name="base_price" value="<?= htmlspecialchars($plan['base_price']) ?>"></label><br>
    <label>Billing Cycle:
        <select name="billing_cycle">
            <option value="monthly"   <?= $plan['billing_cycle'] === 'monthly' ? 'selected' : '' ?>>Monthly</option>
            <option value="quarterly" <?= $plan['billing_cycle'] === 'quarterly' ? 'selected' : '' ?>>Quarterly</option>
            <option value="yearly"    <?= $plan['billing_cycle'] === 'yearly' ? 'selected' : '' ?>>Yearly</option>
        </select>
    </label><br>
    <label>Wholesaler: <input type="text" name="wsaler" value="<?= htmlspecialchars($plan['wsaler']) ?>"></label><br>
    <label>Wholesale Cost: <input type="text" name="wcost" value="<?= htmlspecialchars($plan['wcost']) ?>"></label><br>
    <label>Resell Price: <input type="text" name="rsell" value="<?= htmlspecialchars($plan['rsell']) ?>"></label><br>
    <label>Display:
        <select name="display">
            <option value="show" <?= $plan['display'] === 'show' ? 'selected' : '' ?>>Show</option>
            <option value="hide" <?= $plan['display'] === 'hide' ? 'selected' : '' ?>>Hide</option>
        </select>
    </label><br>
    <label>Short Desc: <input type="text" name="sdesc" value="<?= htmlspecialchars($plan['sdesc']) ?>"></label><br>
    <label>Long Desc: <textarea name="ldesc"><?= htmlspecialchars($plan['ldesc']) ?></textarea></label><br>

    <h3>Steps</h3>
    <?php 
    $actions = ['fixed_cost'=>'Fixed Cost','unit_cost'=>'Unit Cost','base_cost'=>'Base Cost','disable'=>'Disable'];
    for ($i = 1; $i <= 7; $i++): ?>
        <label>Step <?= $i ?> Limit: 
            <input type="text" name="step<?= $i ?>_limit" value="<?= htmlspecialchars($plan["step{$i}_limit"]) ?>">
        </label>
        <label>Step <?= $i ?> Cost: 
            <input type="text" name="step<?= $i ?>_cost" value="<?= htmlspecialchars($plan["step{$i}_cost"]) ?>">
        </label>
        <label>Step <?= $i ?> Action: 
            <select name="step<?= $i ?>_action">
                <?php foreach ($actions as $val => $label): ?>
                    <option value="<?= $val ?>" <?= $plan["step{$i}_action"] === $val ? 'selected' : '' ?>><?= $label ?></option>
                <?php endforeach; ?>
            </select>
        </label><br>
    <?php endfor; ?>

    <button type="submit">Update Plan</button>
</form>

