<?php
session_start();
include "../config.php";

error_reporting(E_ALL);
ini_set('display_errors', 1);

// 🔐 Admin check
if(!isset($_SESSION['role']) || $_SESSION['role'] != 'admin'){
    header("Location: ../login.php");
    exit();
}

$message = "";
$error = "";

if(isset($_POST['create_class']))
{
    $dept_id = $_POST['dept'];
    $year_id = $_POST['year'];
    $division_id = $_POST['division'];

    if(empty($dept_id) || empty($year_id) || empty($division_id)){
        $error = "All fields are required!";
    }
    else {

        // ✅ Create class
        $insertClass = mysqli_query($conn,"
            INSERT INTO classes (dept_id, year_id, division_id)
            VALUES ('$dept_id','$year_id','$division_id')
        ");

        if(!$insertClass){
            die("Class Error: " . mysqli_error($conn));
        }

        $class_id = mysqli_insert_id($conn);

        if($_FILES['file']['error'] != 0){
            die("File upload error!");
        }

        $handle = fopen($_FILES['file']['tmp_name'], "r");

        if(!$handle){
            die("Cannot open CSV");
        }

        fgetcsv($handle); // skip header

        $inserted = 0;
        $skipped = 0;

        while(($data = fgetcsv($handle,1000,",")) !== FALSE)
        {
            if(count($data) < 2){
                $skipped++;
                continue;
            }

            $name = trim($data[0]);
            $prn  = trim($data[1]);

            if(empty($name) || empty($prn)){
                $skipped++;
                continue;
            }

            // ✅ Duplicate check
            $check = mysqli_query($conn,"SELECT student_id FROM students WHERE prn='$prn'");
            if(mysqli_num_rows($check) > 0){
                $skipped++;
                continue;
            }

            // ✅ INSERT with SQL-based first_name extraction
            $insertStudent = mysqli_query($conn,"
                INSERT INTO students 
                (name, first_name, prn, dept_id, year_id, division_id, class_id)
                VALUES 
                (
                    '$name',
                    SUBSTRING_INDEX('$name', ' ', 1),
                    '$prn',
                    '$dept_id',
                    '$year_id',
                    '$division_id',
                    '$class_id'
                )
            ");

            if(!$insertStudent){
                die("Student Error: " . mysqli_error($conn));
            }

            $student_id = mysqli_insert_id($conn);

            // ✅ Dues
            mysqli_query($conn,"INSERT INTO dues (student_id) VALUES ('$student_id')");

            // ✅ User
            mysqli_query($conn,"
                INSERT INTO users (username,password,role)
                VALUES ('$name','$student_id','student')
            ");

            $inserted++;
        }

        fclose($handle);

        $message = "✅ Class Created! Inserted: $inserted | Skipped: $skipped";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Create Class</title>
</head>

<body>

<h2>Create Class</h2>

<!-- SUCCESS -->
<?php if(!empty($message)){ ?>
    <p style="color:green;"><?= $message ?></p>
<?php } ?>

<!-- ERROR -->
<?php if(!empty($error)){ ?>
    <p style="color:red;"><?= $error ?></p>
<?php } ?>

<form method="post" enctype="multipart/form-data">

<select name="dept" required>
<option value="">Department</option>
<option value="1">Computer Engineering</option>
<option value="2">IT Engineering</option>
<option value="3">Mechanical Engineering</option>
<option value="4">Civil Engineering</option>
</select>

<select name="year" required>
<option value="">Year</option>
<option value="1">F.E</option>
<option value="2">S.E</option>
<option value="3">T.E</option>
<option value="4">B.E</option>
</select>

<select name="division" required>
<option value="">Division</option>
<option value="1">A</option>
<option value="2">B</option>
</select>

<br><br>

<input type="file" name="file" required>

<br><br>

<button name="create_class">Create Class</button>

</form>

</body>
</html>
