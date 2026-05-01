<?php
include("db.php");

$message = '';

function ensureAgreementFirstMonthRentColumn($conn)
{
    $checkSql = "SELECT COUNT(*) AS column_count
                 FROM INFORMATION_SCHEMA.COLUMNS
                 WHERE TABLE_SCHEMA = DATABASE()
                   AND TABLE_NAME = 'agreement'
                   AND COLUMN_NAME = 'first_month_rent'";
    $checkResult = $conn->query($checkSql);
    $row = $checkResult ? $checkResult->fetch_assoc() : null;

    if (!$row || intval($row['column_count']) === 0) {
        $conn->query("ALTER TABLE agreement ADD COLUMN first_month_rent int(11) NOT NULL DEFAULT 0 AFTER start_date");
    }
}

if (isset($_POST['submit'])) {
    ensureAgreementFirstMonthRentColumn($conn);

    $tenant_id = intval($_POST['tenant_id']);
    $flat_id = intval($_POST['flat_id']);
    $owner_id = intval($_POST['owner_id']);
    $advance = floatval($_POST['advance']);
    $start_date = trim($_POST['start_date']);
    $month = date("F-Y", strtotime($start_date));

    if (!$tenant_id || !$flat_id || !$owner_id || !$start_date) {
        $message = 'All fields are required.';
    } else {
        $res = $conn->query("SELECT asking_rent FROM flat WHERE flat_id='$flat_id'");
        $data = $res->fetch_assoc();
        $base_rent = $data['asking_rent'];

        $sql1 = "INSERT INTO agreement (advance, start_date, first_month_rent, owner_id)
                 VALUES ('$advance','$start_date','$base_rent','$owner_id')";

        if ($conn->query($sql1)) {
            $agreement_id = $conn->insert_id;

            $conn->query("INSERT INTO links (agreement_id, tenant_id, flat_id)
                          VALUES ('$agreement_id', '$tenant_id', '$flat_id')");

            $sql2 = "INSERT INTO monthly_bill 
                     (agreement_id, billing_month, base_rent, maintanance, electricity, gas, water, service_charge, total_amount, payment_status)
                     VALUES ('$agreement_id','$month','$base_rent',0,0,0,0,0,'$base_rent','Unpaid')";

            if ($conn->query($sql2)) {
                $message = 'Agreement created and first month bill recorded successfully.';
            } else {
                $message = 'Bill Error: ' . $conn->error;
            }
        } else {
            $message = 'Agreement Error: ' . $conn->error;
        }
    }
}
?>

<h2>Create Agreement</h2>
<?php if ($message): ?>
    <p><?php echo htmlspecialchars($message); ?></p>
<?php endif; ?>

<form method="POST">
    Tenant ID: <input name="tenant_id" required><br><br>
    Flat ID: <input name="flat_id" required><br><br>
    Owner ID: <input name="owner_id" required><br><br>
    Advance: <input type="number" step="0.01" name="advance" required><br><br>
    Start Date: <input type="date" name="start_date" required><br><br>
    <button name="submit">Create Agreement</button>
</form>