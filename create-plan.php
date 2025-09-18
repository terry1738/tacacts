<?php
// show all errors while developing
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'db.php'; // this now provides getDB()

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $pdo = getDB(); // ✅ get a PDO connection

        $sql = "INSERT INTO plans (
                    name, description, base_price, billing_cycle,
                    wsaler, wcost, rsell, display, sdesc, ldesc,
                    step1_limit, step1_cost, step1_action,
                    step2_limit, step2_cost, step2_action,
                    step3_limit, step3_cost, step3_action,
                    step4_limit, step4_cost, step4_action,
                    step5_limit, step5_cost, step5_action,
                    step6_limit, step6_cost, step6_action,
                    step7_limit, step7_cost, step7_action
                ) VALUES (
                    :name, :description, :base_price, :billing_cycle,
                    :wsaler, :wcost, :rsell, :display, :sdesc, :ldesc,
                    :step1_limit, :step1_cost, :step1_action,
                    :step2_limit, :step2_cost, :step2_action,
                    :step3_limit, :step3_cost, :step3_action,
                    :step4_limit, :step4_cost, :step4_action,
                    :step5_limit, :step5_cost, :step5_action,
                    :step6_limit, :step6_cost, :step6_action,
                    :step7_limit, :step7_cost, :step7_action
                )";

        $stmt = $pdo->prepare($sql);

        $params = [
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

        // loop over 7 steps and add limit, cost, action
        for ($i = 1; $i <= 7; $i++) {
            $params[":step{$i}_limit"]  = $_POST["step{$i}_limit"] ?? null;
            $params[":step{$i}_cost"]   = $_POST["step{$i}_cost"] ?? null;
            $params[":step{$i}_action"] = $_POST["step{$i}_action"] ?? 'disable';
        }

        $stmt->execute($params);

        echo "<p>Plan created successfully.</p>";

    } catch (PDOException $e) {
        echo "<p>Error: " . $e->getMessage() . "</p>";
    }
}
?>

<!-- Basic form for testing -->
<form method="post">
    <label>Plan Name: <input type="text" name="name"></label><br>
    <label>Description: <textarea name="description"></textarea></label><br>
    <label>Base Price: <input type="text" name="base_price"></label><br>
    <label>Billing Cycle: 
        <select name="billing_cycle">
            <option value="monthly">Monthly</option>
            <option value="quarterly">Quarterly</option>
            <option value="yearly">Yearly</option>
        </select>
    </label><br>
    <label>Wholesaler: <input type="text" name="wsaler"></label><br>
    <label>Wholesale Cost: <input type="text" name="wcost"></label><br>
    <label>Resell Price: <input type="text" name="rsell"></label><br>
    <label>Display: 
        <select name="display">
            <option value="show">Show</option>
            <option value="hide">Hide</option>
        </select>
    </label><br>
    <label>Short Desc: <input type="text" name="sdesc"></label><br>
    <label>Long Desc: <textarea name="ldesc"></textarea></label><br>

    <h3>Steps</h3>
    <?php for ($i = 1; $i <= 7; $i++): ?>
        <fieldset style="margin-bottom:10px;">
            <legend>Step <?= $i ?></legend>
            <label>Limit: <input type="text" name="step<?= $i ?>_limit"></label>
            <label>Cost: <input type="text" name="step<?= $i ?>_cost"></label>
            <label>Action: 
                <select name="step<?= $i ?>_action">
                    <option value="fixed_cost">Fixed Cost</option>
                    <option value="unit_cost">Unit Cost</option>
                    <option value="base_cost">Base Cost</option>
                    <option value="disable" selected>Disable</option>
                </select>
            </label>
        </fieldset>
    <?php endfor; ?>

    <button type="submit">Create Plan</button>
</form>

