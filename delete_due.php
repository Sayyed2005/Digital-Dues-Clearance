<?php
session_start();
include "../config.php";

if($_SESSION['role'] != 'admin'){
    header("Location: ../login.php");
    exit();
}

$id = $_GET['id'];
$type = $_GET['type']; // accounts/library/lab/exam

$column = $type . "_due";

// set department due = 0
mysqli_query($conn,"UPDATE dues SET $column = 0 WHERE student_id='$id'");

// 🧠 Recalculate status
$check = mysqli_query($conn,"SELECT accounts_due, library_due, lab_due, exam_due 
                             FROM dues WHERE student_id='$id'");
$dues = mysqli_fetch_assoc($check);

$total = $dues['accounts_due'] + $dues['library_due'] + $dues['lab_due'] + $dues['exam_due'];

if($total > 0){
    mysqli_query($conn,"UPDATE dues SET status='Pending' WHERE student_id='$id'");
}else{
    mysqli_query($conn,"UPDATE dues SET status='Cleared' WHERE student_id='$id'");
}

header("Location: ".$_SERVER['HTTP_REFERER']);
exit();
?>
