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
        :root{--bg:#f6f8fb;--card:#ffffff;--primary:#2563eb;--muted:#6b7280;--accent:#10b981}
        body{background:var(--bg);font-family:Inter,ui-sans-serif,system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', Arial}
        .bill-form{max-width:820px;margin:28px auto;padding:0}
        .card{background:var(--card);border-radius:12px;box-shadow:0 8px 24px rgba(15,23,42,0.06);overflow:hidden}
        .card-header{padding:20px 24px;border-bottom:1px solid #eef2f6;display:flex;align-items:center;justify-content:space-between}
        .card-header h2{margin:0;font-size:18px;color:#0f172a}
        .card-body{padding:20px 24px}
        label{display:block;margin:10px 0 6px;color:#0f172a;font-weight:600}
        input[type=text], input[type=number], input[type=month], select{width:100%;padding:10px 12px;border:1px solid #e6edf3;border-radius:8px;background:#fff}
        .grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px}
        .grid.single{grid-template-columns:1fr}
        .actions{display:flex;gap:10px;align-items:center;margin-top:16px}
        .btn-primary{background:var(--primary);color:#fff;padding:10px 14px;border-radius:8px;text-decoration:none;border:none;cursor:pointer;font-weight:700;display:inline-flex;align-items:center;gap:8px}
        .btn-muted{background:#f1f5f9;color:#0f172a;padding:10px 12px;border-radius:8px;text-decoration:none}
        .alert-error{background:#fff1f2;border:1px solid #fecaca;color:#9f1239;padding:12px;border-radius:8px}
        .note{color:var(--muted);margin-top:12px}
        .summary{background:#fbfdff;border:1px solid #e6f2ff;padding:12px;border-radius:8px;margin-top:12px;font-weight:700;color:#0f172a}
        @media (max-width:800px){.grid{grid-template-columns:1fr}}
    </style>
</head>
<body>
    <div class="bill-form">
        <div class="card">
            <div class="card-header">
                <h2>Generate Monthly Bill</h2>
                <a href="owner_dashboard.php" class="btn-muted">Back</a>
            </div>
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="alert-error"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <?php if (count($agreements) === 0): ?>
                    <p>No active agreements found for your account. Please confirm a tenant or create an agreement before generating a monthly bill.</p>
                    <p class="note">Use the Back button above to return to the dashboard.</p>
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

                        <div class="grid single" style="margin-top:12px;">
                            <div>
                                <label for="billing_month">Billing Month</label>
                                <input type="month" id="billing_month" name="billing_month" required value="<?php echo htmlspecialchars($_POST['billing_month'] ?? $defaultMonth); ?>">
                            </div>
                            <div>
                                <label for="base_rent">Base Rent</label>
                                <input type="text" id="base_rent" value="<?php echo htmlspecialchars($defaultBaseRent); ?>" readonly>
                            </div>
                        </div>

                        <div class="grid" style="margin-top:12px;">
                            <div>
                                <label for="electricity">Electricity Charge</label>
                                <input type="number" id="electricity" name="electricity" min="0" value="<?php echo htmlspecialchars($_POST['electricity'] ?? '0'); ?>" required>
                            </div>
                            <div>
                                <label for="gas">Gas Charge</label>
                                <input type="number" id="gas" name="gas" min="0" value="<?php echo htmlspecialchars($_POST['gas'] ?? '0'); ?>" required>
                            </div>
                        </div>
                        <div class="grid" style="margin-top:12px;">
                            <div>
                                <label for="water">Water Charge</label>
                                <input type="number" id="water" name="water" min="0" value="<?php echo htmlspecialchars($_POST['water'] ?? '0'); ?>" required>
                            </div>
                            <div>
                                <label for="service_charge">Service Charge</label>
                                <input type="number" id="service_charge" name="service_charge" min="0" value="<?php echo htmlspecialchars($_POST['service_charge'] ?? '0'); ?>" required>
                            </div>
                        </div>

                        <div class="summary" id="bill_summary">Total: BDT 0</div>

                        <div class="actions">
                            <button type="submit" name="submit" class="btn-primary">
                                <!-- icon -->
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 5v14M5 12h14" stroke="#fff" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                Generate Bill
                            </button>
                            <a href="owner_dashboard.php" class="btn-muted">Cancel</a>
                        </div>
                        <p class="note">The total bill amount is base rent plus electricity, gas, water, and service charges.</p>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        const agreementSelect = document.getElementById('agreement_id');
        const baseRentInput = document.getElementById('base_rent');
        const electricityInput = document.getElementById('electricity');
        const gasInput = document.getElementById('gas');
        const waterInput = document.getElementById('water');
        const serviceChargeInput = document.getElementById('service_charge');
        const billSummary = document.getElementById('bill_summary');

        function updateBillPreview() {
            const baseRent = Number(baseRentInput?.value || 0);
            const electricity = Number(electricityInput?.value || 0);
            const gas = Number(gasInput?.value || 0);
            const water = Number(waterInput?.value || 0);
            const serviceCharge = Number(serviceChargeInput?.value || 0);

            const total = baseRent + electricity + gas + water + serviceCharge;
            if (billSummary) {
                billSummary.innerText = 'Total: BDT ' + total.toLocaleString();
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
