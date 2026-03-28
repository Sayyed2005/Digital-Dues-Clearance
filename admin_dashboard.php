<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include "../config.php";
// 🔓 Logout
if(isset($_GET['logout']))
{
    session_destroy();
    header("Location: ../login.php");
    exit();
}
// 🔐 Admin check
if(!isset($_SESSION['role']) || $_SESSION['role'] != 'admin'){
    header("Location: ../login.php");
    exit();
}

/* ================================
   STATIC MAPPING
================================ */
$dept_map = [
    "Computer Engineering" => 1,
    "IT Engineering" => 2,
    "Mechanical Engineering" => 3,
    "Civil Engineering" => 4
];

$year_map = [
    "F.E" => 1,
    "S.E" => 2,
    "T.E" => 3,
    "B.E" => 4
];

$division_map = [
    "A" => 1,
    "B" => 2
];

/* ================================
   STUDENT UPLOAD
================================ */
if(isset($_POST['upload_students']))
{
    $dept_name = $_POST['dept'] ?? '';
    $year_name = $_POST['year'] ?? '';
    $division_name = $_POST['division'] ?? '';

    if(isset($dept_map[$dept_name]) && isset($year_map[$year_name]) && isset($division_map[$division_name]))
    {
        $dept_id = $dept_map[$dept_name];
        $year_id = $year_map[$year_name];
        $division_id = $division_map[$division_name];

        if(isset($_FILES['file']) && $_FILES['file']['error'] == 0)
        {
            $file = $_FILES['file']['tmp_name'];
            $handle = fopen($file,"r");

            if($handle)
            {
                fgetcsv($handle); // skip header

                while(($data = fgetcsv($handle,1000,",")) !== FALSE)
                {
                    // ✅ Ensure CSV has correct columns
                    if(count($data) < 6) continue;

                    // CSV FORMAT:
                    // [0] student_id (ignore)
                    // [1] name
                    // [2] prn

                    $name = trim($data[1]);
                    $prn  = trim($data[2]);

                    if(empty($name) || empty($prn)) continue;

                    // 🔹 Prevent duplicate PRN
                    $check = mysqli_query($conn,"SELECT * FROM students WHERE prn='$prn'");
                    if(mysqli_num_rows($check) > 0) continue;

                    // 🔹 Insert student
                    $insert = "INSERT INTO students (name, prn, dept_id, year_id, division_id)
                               VALUES ('$name','$prn','$dept_id','$year_id','$division_id')";

                    if(mysqli_query($conn,$insert))
                    {
                        $student_id = mysqli_insert_id($conn);

                        // 🔹 Create dues
                        mysqli_query($conn,"INSERT INTO dues (student_id) VALUES ('$student_id')");

                        // 🔥 Create Login (USERNAME = FULL NAME, PASSWORD = student_id)
                        $username = mysqli_real_escape_string($conn, $name);
                        $password = $student_id;

                        // Prevent duplicate username for students
                        $checkUser = mysqli_query($conn,"SELECT * FROM users WHERE username='$username' AND role='student'");
                        
                        if(mysqli_num_rows($checkUser) == 0)
                        {
                            mysqli_query($conn,"INSERT INTO users (username,password,role)
                                                VALUES ('$username','$password','student')");
                        }
                    }
                }

                fclose($handle);
                echo "<script>alert('Students Uploaded Successfully');</script>";
            }
        }
    }
}

/* ================================
   FETCH COUNTS
================================ */
$total = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as c FROM students"))['c'];
$cleared = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as c FROM dues WHERE status='Cleared'"))['c'];
$pending = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as c FROM dues WHERE status='Pending'"))['c'];

/* ================================
   FILTERS
================================ */
$filter_dept = $_GET['dept'] ?? '';
$filter_year = $_GET['year'] ?? '';
$filter_div = $_GET['division'] ?? '';

$query = "SELECT students.*, dues.status FROM students 
JOIN dues ON students.student_id = dues.student_id WHERE 1";

if($filter_dept != '') $query .= " AND students.dept_id='$filter_dept'";
if($filter_year != '') $query .= " AND students.year_id='$filter_year'";
if($filter_div != '') $query .= " AND students.division_id='$filter_div'";

$result = mysqli_query($conn,$query);
?>

<!DOCTYPE html>
<html>
<head>
<title>Admin Dashboard</title>
<style>
body { font-family: Arial; background:#f5f7fa; }
.cards { display:flex; gap:20px; margin:20px; }
.card { padding:20px; background:white; border-radius:10px; flex:1; text-align:center; }
.upload-box, .filters { margin:20px; background:white; padding:15px; border-radius:10px; }
table { width:95%; margin:20px; border-collapse:collapse; background:white; }
th,td { padding:10px; border:1px solid #ddd; text-align:center; }
button { padding:5px 10px; cursor:pointer; }
</style>
</head>

<body>


<div style="display:flex; justify-content:space-between; align-items:center; margin:20px;">
    <h2>Admin Dashboard</h2>
    <a href="?logout=true">
        <button style="background:red; color:white; border:none; padding:8px 15px; border-radius:5px;">
            Logout
        </button>
    </a>
</div>
<!-- Cards -->
<div class="cards">
<div class="card">Total Students<br><b><?= $total ?></b></div>
<div class="card">Cleared<br><b><?= $cleared ?></b></div>
<div class="card">Pending<br><b><?= $pending ?></b></div>
</div>

<!-- Upload -->
<div class="upload-box">
<h3>Upload Students</h3>
<form method="post" enctype="multipart/form-data">

<select name="dept" required>
<option value="">Department</option>
<option>Computer Engineering</option>
<option>IT Engineering</option>
<option>Mechanical Engineering</option>
<option>Civil Engineering</option>
</select>

<select name="year" required>
<option value="">Year</option>
<option>F.E</option>
<option>S.E</option>
<option>T.E</option>
<option>B.E</option>
</select>

<select name="division" required>
<option value="">Division</option>
<option>A</option>
<option>B</option>
</select>

<input type="file" name="file" required>
<button name="upload_students">Upload</button>

</form>
</div>

<!-- Filters -->
<form method="get" class="filters">

<select name="dept">
<option value="">Department</option>
<option value="1">Computer Engineering</option>
<option value="2">IT Engineering</option>
<option value="3">Mechanical Engineering</option>
<option value="4">Civil Engineering</option>
</select>

<select name="year">
<option value="">Year</option>
<option value="1">F.E</option>
<option value="2">S.E</option>
<option value="3">T.E</option>
<option value="4">B.E</option>
</select>

<select name="division">
<option value="">Division</option>
<option value="1">A</option>
<option value="2">B</option>
</select>

<button type="submit">Filter</button>
</form>

<!-- Table -->
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
