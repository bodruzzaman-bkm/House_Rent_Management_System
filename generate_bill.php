<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'owner') {
    header('Location: login.php');
    exit();
}

$owner_id = $_SESSION['user_id'];
$error = '';
$base_rent = 0;

$agreements = [];
$query = "SELECT a.agreement_id, l.flat_id, l.tenant_id, u.name AS tenant_name, f.location, f.asking_rent AS base_rent
          FROM agreement a
          JOIN links l ON a.agreement_id = l.agreement_id
          JOIN user u ON l.tenant_id = u.user_id
          JOIN flat f ON l.flat_id = f.flat_id
          WHERE a.owner_id = ?
          ORDER BY a.agreement_id DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $owner_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $agreements[] = $row;
}
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $agreement_id = isset($_POST['agreement_id']) ? intval($_POST['agreement_id']) : 0;
    $billing_month_raw = trim($_POST['billing_month'] ?? '');
    $electricity = isset($_POST['electricity']) ? intval($_POST['electricity']) : 0;
    $gas = isset($_POST['gas']) ? intval($_POST['gas']) : 0;
    $water = isset($_POST['water']) ? intval($_POST['water']) : 0;
    $service_charge = isset($_POST['service_charge']) ? intval($_POST['service_charge']) : 0;

    if ($agreement_id <= 0) {
        $error = 'Please select a valid agreement.';
    }

    $billing_month = '';
    if (!$error) {
        $month_date = DateTime::createFromFormat('Y-m', $billing_month_raw);
        if ($month_date) {
            $billing_month = $month_date->format('F-Y');
        } else {
            $error = 'Please enter a valid billing month.';
        }
    }

    if (!$error) {
        $verifySql = "SELECT f.asking_rent AS base_rent
                      FROM agreement a
                      JOIN links l ON a.agreement_id = l.agreement_id
                      JOIN flat f ON l.flat_id = f.flat_id
                      WHERE a.agreement_id = ? AND a.owner_id = ?
                      LIMIT 1";
        $verifyStmt = $conn->prepare($verifySql);
        $verifyStmt->bind_param('ii', $agreement_id, $owner_id);
        $verifyStmt->execute();
        $verifyResult = $verifyStmt->get_result();

        if ($verifyResult->num_rows === 0) {
            $error = 'Selected agreement is not valid for this owner.';
        } else {
            $agreementData = $verifyResult->fetch_assoc();
            $base_rent = intval($agreementData['base_rent']);
        }
        $verifyStmt->close();
    }

    if (!$error) {
        $total_amount = $base_rent + $electricity + $gas + $water + $service_charge;
        $payment_status = 'Unpaid';

        $existingSql = "SELECT agreement_id FROM monthly_bill WHERE agreement_id = ? AND billing_month = ? LIMIT 1";
        $existingStmt = $conn->prepare($existingSql);
        $existingStmt->bind_param('is', $agreement_id, $billing_month);
        $existingStmt->execute();
        $existingResult = $existingStmt->get_result();

        if ($existingResult->num_rows > 0) {
            $updateSql = "UPDATE monthly_bill
                          SET base_rent = ?, maintanance = 0, electricity = ?, gas = ?, water = ?, service_charge = ?, total_amount = ?, payment_status = ?
                          WHERE agreement_id = ? AND billing_month = ?";
            $updateStmt = $conn->prepare($updateSql);
            $updateStmt->bind_param('iiiiisiss', $base_rent, $electricity, $gas, $water, $service_charge, $total_amount, $payment_status, $agreement_id, $billing_month);

            if ($updateStmt->execute()) {
                $_SESSION['bill_total'] = $total_amount;
                $_SESSION['bill_month'] = $billing_month;
                $_SESSION['bill_message'] = 'Existing bill updated for this month.';
                header('Location: success.php');
                exit();
            }

            $error = 'Unable to update bill: ' . $updateStmt->error;
            $updateStmt->close();
        } else {
            $insertSql = "INSERT INTO monthly_bill
                          (agreement_id, billing_month, base_rent, maintanance, electricity, gas, water, service_charge, total_amount, payment_status)
                          VALUES (?, ?, ?, 0, ?, ?, ?, ?, ?, ?)";
            $insertStmt = $conn->prepare($insertSql);
            $insertStmt->bind_param('isiiiiiis', $agreement_id, $billing_month, $base_rent, $electricity, $gas, $water, $service_charge, $total_amount, $payment_status);

            if ($insertStmt->execute()) {
                $_SESSION['bill_total'] = $total_amount;
                $_SESSION['bill_month'] = $billing_month;
                $_SESSION['bill_message'] = 'New bill generated successfully.';
                header('Location: success.php');
                exit();
            }

            $error = 'Unable to generate bill: ' . $insertStmt->error;
            $insertStmt->close();
        }
        $existingStmt->close();
    }
}

$defaultBaseRent = count($agreements) > 0 ? $agreements[0]['base_rent'] : 0;
$defaultMonth = date('Y-m');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Monthly Bill</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .bill-form { max-width: 700px; margin: 20px auto; padding: 24px; background: #fff; border-radius: 8px; box-shadow: 0 4px 18px rgba(0,0,0,0.08); }
        .bill-form h2 { margin-bottom: 16px; }
        .bill-form label { display: block; margin: 12px 0 6px; font-weight: 600; }
        .bill-form input, .bill-form select { width: 100%; padding: 10px 12px; border: 1px solid #ccc; border-radius: 5px; }
        .bill-form .form-row { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; }
        .bill-form .form-row.single { grid-template-columns: 1fr; }
        .bill-form button { margin-top: 18px; padding: 12px 18px; background: #007BFF; color: #fff; border: none; border-radius: 5px; cursor: pointer; }
        .bill-form .btn-back { display: inline-block; margin-bottom: 16px; padding: 10px 14px; background: #6c757d; color: #fff; text-decoration: none; border-radius: 5px; }
        .bill-form .alert-error { margin-bottom: 16px; padding: 12px 14px; background: #ffe1e1; border: 1px solid #ff7b7b; color: #a41c1c; border-radius: 5px; }
        .bill-form .info-text { margin-top: 10px; color: #555; }
    </style>
</head>
<body>
    <div class="bill-form">
        <h2>Generate Monthly Bill</h2>
        <a href="owner_dashboard.php" class="btn-back">Back</a>
        <?php if ($error): ?>
            <div class="alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if (count($agreements) === 0): ?>
            <p>No active agreements found for your account. Please confirm a tenant or create an agreement before generating a monthly bill.</p>
            <p class="info-text">You can go back to the dashboard using the Back button above.</p>
        <?php else: ?>
            <form method="post">
                <label for="agreement_id">Select Agreement</label>
                <select id="agreement_id" name="agreement_id" required>
                    <?php foreach ($agreements as $agreement): ?>
                        <option value="<?php echo $agreement['agreement_id']; ?>" data-base-rent="<?php echo $agreement['base_rent']; ?>">
                            Agreement #<?php echo $agreement['agreement_id']; ?> — Flat #<?php echo $agreement['flat_id']; ?> (<?php echo htmlspecialchars($agreement['location']); ?>) — Tenant: <?php echo htmlspecialchars($agreement['tenant_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <div class="form-row single">
                    <div>
                        <label for="billing_month">Billing Month</label>
                        <input type="month" id="billing_month" name="billing_month" required value="<?php echo htmlspecialchars($_POST['billing_month'] ?? $defaultMonth); ?>">
                    </div>
                    <div>
                        <label for="base_rent">Base Rent</label>
                        <input type="text" id="base_rent" value="<?php echo htmlspecialchars($defaultBaseRent); ?>" readonly>
                    </div>
                </div>

                <div class="form-row">
                    <div>
                        <label for="electricity">Electricity Charge</label>
                        <input type="number" id="electricity" name="electricity" min="0" value="<?php echo htmlspecialchars($_POST['electricity'] ?? '0'); ?>" required>
                    </div>
                    <div>
                        <label for="gas">Gas Charge</label>
                        <input type="number" id="gas" name="gas" min="0" value="<?php echo htmlspecialchars($_POST['gas'] ?? '0'); ?>" required>
                    </div>
                </div>
                <div class="form-row">
                    <div>
                        <label for="water">Water Charge</label>
                        <input type="number" id="water" name="water" min="0" value="<?php echo htmlspecialchars($_POST['water'] ?? '0'); ?>" required>
                    </div>
                    <div>
                        <label for="service_charge">Service Charge</label>
                        <input type="number" id="service_charge" name="service_charge" min="0" value="<?php echo htmlspecialchars($_POST['service_charge'] ?? '0'); ?>" required>
                    </div>
                </div>

                <div class="form-row single">
                    <div>
                        <label for="bill_preview">Total Bill Preview</label>
                        <input type="text" id="bill_preview" value="0" readonly>
                    </div>
                </div>

                <button type="submit" name="submit">Generate Bill</button>
            </form>
            <p class="info-text">The total bill amount is base rent plus electricity, gas, water, and service charges.</p>
        <?php endif; ?>
    </div>

    <script>
        const agreementSelect = document.getElementById('agreement_id');
        const baseRentInput = document.getElementById('base_rent');
        const electricityInput = document.getElementById('electricity');
        const gasInput = document.getElementById('gas');
        const waterInput = document.getElementById('water');
        const serviceChargeInput = document.getElementById('service_charge');
        const billPreviewInput = document.getElementById('bill_preview');

        function updateBillPreview() {
            const baseRent = Number(baseRentInput?.value || 0);
            const electricity = Number(electricityInput?.value || 0);
            const gas = Number(gasInput?.value || 0);
            const water = Number(waterInput?.value || 0);
            const serviceCharge = Number(serviceChargeInput?.value || 0);

            if (billPreviewInput) {
                billPreviewInput.value = baseRent + electricity + gas + water + serviceCharge;
            }
        }

        agreementSelect?.addEventListener('change', function () {
            const selected = agreementSelect.selectedOptions[0];
            if (selected) {
                baseRentInput.value = selected.dataset.baseRent || '0';
            }
            updateBillPreview();
        });

        [electricityInput, gasInput, waterInput, serviceChargeInput].forEach(function (input) {
            input?.addEventListener('input', updateBillPreview);
        });

        updateBillPreview();
    </script>
</body>
</html>
