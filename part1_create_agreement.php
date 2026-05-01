<?php
include("config/db_connect.php");

if (isset($_POST['submit'])) {

    $tenant_id = $_POST['tenant_id'];
    $flat_id = $_POST['flat_id'];
    $owner_id = $_POST['owner_id'];
    $advance = $_POST['advance'];
    $start_date = $_POST['start_date'];
    $month = date("F-Y");

    $sql1 = "INSERT INTO agreement 
    (advance, start_date, owner_id, tenant_id, flat_id)
    VALUES ('$advance','$start_date','$owner_id','$tenant_id','$flat_id')";

    if ($conn->query($sql1)) {

        $agreement_id = $conn->insert_id;

        $flat = $conn->query("SELECT asking_rent FROM flat WHERE flat_id='$flat_id'");
        $data = $flat->fetch_assoc();
        $base_rent = $data['asking_rent'];

        $sql2 = "INSERT INTO monthly_bill 
        VALUES ('$agreement_id','$month','$base_rent',0,0,0,'Unpaid')";

        $conn->query($sql2);

        echo "Agreement + First Bill Created!";
    }
}
?>

<form method="POST">
Tenant ID: <input name="tenant_id"><br>
Flat ID: <input name="flat_id"><br>
Owner ID: <input name="owner_id"><br>
Advance: <input name="advance"><br>
Start Date: <input type="date" name="start_date"><br>
<button name="submit">Create Agreement</button>
</form>

/*
git cmd 
git add . 
git commit -m "your commit message"
git push 
*/

<?php
include("db.php");

if (isset($_POST['submit'])) {

    $tenant_id = $_POST['tenant_id'];
    $flat_id = $_POST['flat_id'];
    $owner_id = $_POST['owner_id'];
    $advance = $_POST['advance'];
    $start_date = $_POST['start_date'];

    $month = date("F-Y");

    // 1. Insert into agreement
    $sql1 = "INSERT INTO agreement 
    (advance, start_date, owner_id, tenant_id, flat_id)
    VALUES ('$advance','$start_date','$owner_id','$tenant_id','$flat_id')";

    if ($conn->query($sql1)) {

        $agreement_id = $conn->insert_id;

        // 2. Get base rent from flat
        $res = $conn->query("SELECT asking_rent FROM flat WHERE flat_id='$flat_id'");
        $data = $res->fetch_assoc();
        $base_rent = $data['asking_rent'];

        // 3. Insert first bill
        $sql2 = "INSERT INTO monthly_bill 
        (agreement_id, billing_month, base_rent, maintenance, electricity, gas, payment_status)
        VALUES ('$agreement_id','$month','$base_rent',0,0,0,'Unpaid')";

        if ($conn->query($sql2)) {
            echo "Agreement + First Bill Created Successfully!";
        } else {
            echo "Bill Error: " . $conn->error;
        }

    } else {
        echo "Agreement Error: " . $conn->error;
    }
}
?>

<h2>Create Agreement</h2>

<form method="POST">
Tenant ID: <input name="tenant_id"><br><br>
Flat ID: <input name="flat_id"><br><br>
Owner ID: <input name="owner_id"><br><br>
Advance: <input name="advance"><br><br>
Start Date: <input type="date" name="start_date"><br><br>

<button name="submit">Create Agreement</button>
</form>