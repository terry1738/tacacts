<?php
// show all errors while developing
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
if (isset($_SESSION['success_message'])) {
    echo "<p class='success'>" . htmlspecialchars($_SESSION['success_message']) . "</p>";
    unset($_SESSION['success_message']);
}
require 'db.php'; // uses your current getDB() /e/t/d

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    try {
        $pdo = getDB();

        // Base plan fields
        $name          = trim($_POST['name'] ?? '');
        $description   = trim($_POST['description'] ?? '');
        $base_price    = $_POST['base_price'] ?? 0;
        $billing_cycle = $_POST['billing_cycle'] ?? 'monthly';
        $wsaler        = trim($_POST['wsaler'] ?? '');
        $wcost         = $_POST['wcost'] ?? 0;
        $rsell         = $_POST['rsell'] ?? 0;
        $display       = $_POST['display'] ?? 'hide';
        $sdesc         = trim($_POST['sdesc'] ?? '');
        $ldesc         = trim($_POST['ldesc'] ?? '');

        // Steps actions, limits, costs
        $actions = [];
        $limits  = [];
        $costs   = [];

        for ($i = 1; $i <= 7; $i++) {
            // Read the action (disable, fixed_cost, base_cost, unit_cost)
            $actions[$i] = $_POST["step{$i}_action"] ?? 'disable';

            if ($actions[$i] === 'disable') {
                // If disabled, set limit/cost null
                $limits[$i] = null;
                $costs[$i]  = null;
            } else {
                // If not disabled, allow empty => null
                $limit_key = "step{$i}_limit";
                $limit_val = isset($_POST[$limit_key]) ? trim($_POST[$limit_key]) : '';
                $limits[$i] = ($limit_val === '' ? null : (int)$limit_val);

                $cost_key  = "step{$i}_cost";
                $cost_val  = isset($_POST[$cost_key]) ? trim($_POST[$cost_key]) : '';
                $costs[$i] = ($cost_val === '' ? null : (float)$cost_val);
            }
        }

        // Build SQL statement
        $sql = "INSERT INTO plans (
                    name, description, base_price, billing_cycle,
                    wsaler, wcost, rsell, display, sdesc, ldesc,";

        for ($i = 1; $i <= 7; $i++) {
            $sql .= " step{$i}_action, step{$i}_limit, step{$i}_cost";
            if ($i < 7) {
                $sql .= ",";
            }
        }
        $sql .= " ) VALUES (";
        $sql .= " :name, :description, :base_price, :billing_cycle, :wsaler, :wcost, :rsell, :display, :sdesc, :ldesc,";
        for ($i = 1; $i <= 7; $i++) {
            $sql .= " :step{$i}_action, :step{$i}_limit, :step{$i}_cost";
            if ($i < 7) {
                $sql .= ",";
            }
        }
        $sql .= " )";

        $stmt = $pdo->prepare($sql);

        // Prepare the parameters
        $params = [
            ':name'          => $name,
            ':description'   => $description,
            ':base_price'    => $base_price,
            ':billing_cycle' => $billing_cycle,
            ':wsaler'        => $wsaler,
            ':wcost'         => $wcost,
            ':rsell'         => $rsell,
            ':display'       => $display,
            ':sdesc'         => $sdesc,
            ':ldesc'         => $ldesc,
        ];
        for ($i = 1; $i <= 7; $i++) {
            $params[":step{$i}_action"] = $actions[$i];
            $params[":step{$i}_limit"]  = $limits[$i];
            $params[":step{$i}_cost"]   = $costs[$i];
        }


	// After successful insert
    	// Clear all form variables and do the insert once
	$ok = $stmt->execute($params);
	
	if ($ok) {
	    // Success: set message & redirect
	    session_start();  // if not already
	    $_SESSION['success_message'] = "Plan added successfully!";
	    header("Location: create-plan.php");
	    exit;
	} else {
	    echo "<p class='error'>Error adding plan.</p>";
	}


    } catch (PDOException $e) {
        echo "<p>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
}
?>

<!-- Your current HTML form; ensure each step has an action select -->
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
        <div style="border:1px solid #ccc; padding:10px; margin-bottom:10px;">
            <label>Step <?= $i ?> Action:
                <select name="step<?= $i ?>_action">
                    <option value="disable" <?= (isset($_POST["step{$i}_action"]) && $_POST["step{$i}_action"] === 'disable') ? 'selected' : '' ?>>Disable</option>
                    <option value="fixed_cost" <?= (isset($_POST["step{$i}_action"]) && $_POST["step{$i}_action"] === 'fixed_cost') ? 'selected' : '' ?>>Fixed Cost</option>
                    <option value="base_cost" <?= (isset($_POST["step{$i}_action"]) && $_POST["step{$i}_action"] === 'base_cost') ? 'selected' : '' ?>>Base Cost</option>
                    <option value="unit_cost" <?= (isset($_POST["step{$i}_action"]) && $_POST["step{$i}_action"] === 'unit_cost') ? 'selected' : '' ?>>Unit Cost</option>
                </select>
            </label><br>
            <label>Limit: <input type="text" name="step<?= $i ?>_limit" value="<?= htmlspecialchars($_POST["step{$i}_limit"] ?? '') ?>"></label><br>
            <label>Cost: <input type="text" name="step<?= $i ?>_cost" value="<?= htmlspecialchars($_POST["step{$i}_cost"] ?? '') ?>"></label><br>
        </div>
    <?php endfor; ?>

    <button type="submit">Create Plan</button>
</form>

