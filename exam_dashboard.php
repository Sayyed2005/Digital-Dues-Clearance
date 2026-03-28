<?php
session_start();
include "../config.php";

// 🔐 Admin Check
if($_SESSION['role'] != 'admin'){
    header("Location: ../login.php");
    exit();
}

/* ================================
   FETCH COUNTS
================================ */

$total = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) c FROM students"))['c'];

$cleared = mysqli_fetch_assoc(
mysqli_query($conn,"SELECT COUNT(*) c FROM dues WHERE exam_due=0"))['c'];

$pending = mysqli_fetch_assoc(
mysqli_query($conn,"SELECT COUNT(*) c FROM dues WHERE exam_due>0"))['c'];

/* ================================
   FETCH DATA
================================ */

$query = "SELECT students.name, students.prn, dues.exam_due, dues.status, students.student_id
          FROM students
          JOIN dues ON students.student_id = dues.student_id";

$result = mysqli_query($conn,$query);

?>

<!DOCTYPE html>
<html>
<head>
<title>Exam Dashboard</title>

<style>

body { font-family: Arial; background:#f5f7fa; }

.cards { display:flex; gap:20px; margin:20px; }

.card {
padding:20px;
background:white;
border-radius:10px;
flex:1;
text-align:center;
}

table {
width:95%;
margin:20px;
border-collapse:collapse;
background:white;
}

th,td {
padding:10px;
border:1px solid #ddd;
text-align:center;
}

button {
padding:5px 10px;
cursor:pointer;
}

</style>

</head>

<body>

<h2 style="margin:20px;">Exam Dashboard</h2>

<!-- Cards -->

<div class="cards">

<div class="card">
Total Students<br>
<b><?= $total ?></b>
</div>

<div class="card">
Cleared<br>
<b><?= $cleared ?></b>
</div>

<div class="card">
Pending<br>
<b><?= $pending ?></b>
</div>

</div>

<!-- Table -->

<table>

<tr>
<th>Name</th>
<th>PRN</th>
<th>Exam Due</th>
<th>Status</th>
<th>Action</th>
</tr>

<?php while($row=mysqli_fetch_assoc($result)) { ?>

<tr>

<td><?= htmlspecialchars($row['name']) ?></td>

<td><?= htmlspecialchars($row['prn']) ?></td>

<td><?= htmlspecialchars($row['exam_due']) ?></td>

<td><?= htmlspecialchars($row['status']) ?></td>

<td>

<a href="delete_due.php?id=<?= $row['student_id'] ?>&type=exam">
<button>Clear</button>
</a>

</td>

</tr>

<?php } ?>

</table>

</body>
</html>
