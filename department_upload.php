<?php
session_start();
include "../config.php";

// 🔐 Only admin
if($_SESSION['role'] != 'admin'){
    header("Location: ../login.php");
    exit();
}

if(isset($_POST['upload']))
{
    $dept_type = $_POST['type']; // accounts/library/lab/exam

    if($_FILES['file']['error'] == 0)
    {
        $file = $_FILES['file']['tmp_name'];
        $handle = fopen($file,"r");

        fgetcsv($handle); // skip header

        while(($data = fgetcsv($handle,1000,",")) !== FALSE)
        {
            if(empty($data[0]) || empty($data[1])) continue;

            $prn = trim($data[0]);
            $amount = (int)$data[1];

            // 🔍 Find student
            $s = mysqli_query($conn,"SELECT student_id FROM students WHERE prn='$prn'");
            if(mysqli_num_rows($s) > 0)
            {
                $student = mysqli_fetch_assoc($s);
                $sid = $student['student_id'];

                // 🔥 Dynamic column
                $column = $dept_type . "_due";

                // ✅ Update only that department due
                mysqli_query($conn,"UPDATE dues 
                                    SET $column='$amount' 
                                    WHERE student_id='$sid'");

                // 🧠 SMART STATUS LOGIC
                $check = mysqli_query($conn,"SELECT accounts_due, library_due, lab_due, exam_due FROM dues WHERE student_id='$sid'");
                $dues = mysqli_fetch_assoc($check);

                $total_due = $dues['accounts_due'] + $dues['library_due'] + $dues['lab_due'] + $dues['exam_due'];

                if($total_due > 0){
                    mysqli_query($conn,"UPDATE dues SET status='Pending' WHERE student_id='$sid'");
                } else {
                    mysqli_query($conn,"UPDATE dues SET status='Cleared' WHERE student_id='$sid'");
                }
            }
        }

        fclose($handle);
        echo "<script>alert('Department Data Uploaded Successfully');</script>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Department Upload</title>
<style>
body { font-family: Arial; background:#f5f7fa; }
.box { margin:50px auto; width:400px; background:white; padding:20px; border-radius:10px; }
</style>
</head>

<body>

<div class="box">
<h3>Upload Department File</h3>

<form method="post" enctype="multipart/form-data">

<select name="type" required>
<option value="">Select Department</option>
<option value="accounts">Accounts</option>
<option value="library">Library</option>
<option value="lab">Lab</option>
<option value="exam">Exam</option>
</select><br><br>

<input type="file" name="file" required><br><br>

<button name="upload">Upload</button>

</form>
</div>

</body>
</html>
