<?php
session_start();
include "../config.php";
// 🔓 Logout
if(isset($_GET['logout']))
{
    session_destroy();
    header("Location: ../login.php");
    exit();
}
if($_SESSION['role'] != 'accounts'){
    header("Location: ../login.php");
    exit();
}

// cards
$total = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) c FROM students"))['c'];
$cleared = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) c FROM dues WHERE accounts_due=0"))['c'];
$pending = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) c FROM dues WHERE accounts_due>0"))['c'];

$query = "SELECT students.name, students.prn, dues.accounts_due, dues.status, students.student_id
          FROM students
          JOIN dues ON students.student_id = dues.student_id";

$result = mysqli_query($conn,$query);
?>

<!DOCTYPE html>
<html>
<head>
<title>Accounts Dashboard</title>
<style>
body{font-family:Arial;background:#f5f7fa;}
.cards{display:flex;gap:20px;margin:20px;}
.card{background:white;padding:20px;border-radius:10px;flex:1;text-align:center;}
table{width:95%;margin:20px;border-collapse:collapse;background:white;}
th,td{padding:10px;border:1px solid #ddd;text-align:center;}
button{padding:5px 10px;}
</style>
</head>

<body>

<div style="display:flex; justify-content:space-between; align-items:center; margin:20px;">
    <h2>Account Dashboard</h2>
    <a href="?logout=true">
        <button style="background:red; color:white; border:none; padding:8px 15px; border-radius:5px;">
            Logout
        </button>
    </a>
</div>
<div class="cards">
<div class="card">Total Students<br><b><?= $total ?></b></div>
<div class="card">Cleared<br><b><?= $cleared ?></b></div>
<div class="card">Pending<br><b><?= $pending ?></b></div>
</div>

<table>
<tr>
<th>Name</th>
<th>PRN</th>
<th>Accounts Due</th>
<th>Status</th>
<th>Action</th>
</tr>

<?php while($row=mysqli_fetch_assoc($result)) { ?>
<tr>
<td><?= $row['name'] ?></td>
<td><?= $row['prn'] ?></td>
<td><?= $row['accounts_due'] ?></td>
<td><?= $row['status'] ?></td>
<td>
<a href="delete_due.php?id=<?= $row['student_id'] ?>&type=accounts">
<button>Clear</button>
</a>
</td>
</tr>
<?php } ?>

</table>

</body>
</html>
