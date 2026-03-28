<?php
session_start();
include "../config.php";

if(!isset($_SESSION['role']) || $_SESSION['role'] != 'admin'){
    header("Location: ../login.php");
    exit();
}

$class_id = $_GET['class_id'] ?? '';

if($class_id == ''){
    die("Invalid class");
}

$result = mysqli_query($conn,"
SELECT students.*, dues.status 
FROM students
JOIN dues ON students.student_id = dues.student_id
WHERE students.class_id = '$class_id'
");
?>

<!DOCTYPE html>
<html>
<head>
<title>Class Students</title>
<style>
table {
    width:90%;
    margin:20px auto;
    border-collapse:collapse;
}
th,td {
    border:1px solid #ddd;
    padding:10px;
    text-align:center;
}
</style>
</head>

<body>

<h2 style="text-align:center;">Class Students</h2>

<table>
<tr>
<th>Name</th>
<th>PRN</th>
<th>Status</th>
<th>Action</th>
</tr>

<?php while($row=mysqli_fetch_assoc($result)) { ?>
<tr>
<td><?= htmlspecialchars($row['name']) ?></td>
<td><?= htmlspecialchars($row['prn']) ?></td>
<td><?= htmlspecialchars($row['status']) ?></td>
<td>
<a href="update_dues.php?id=<?= $row['student_id'] ?>">
<button>Update</button>
</a>
</td>
</tr>
<?php } ?>

</table>

</body>
</html>
