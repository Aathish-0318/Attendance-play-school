<?php
session_start();
include 'db.php';

if (!isset($_SESSION['staff_id']) || !isset($_SESSION['branch_name'])) {
    header("Location: index.php");
    exit();
}

$staff_id = $_SESSION['staff_id'];
$branch_name = $_SESSION['branch_name'];

// Get students for this branch
$stmt = $conn->prepare("SELECT s.student_id, s.name, s.age, b.branch_name FROM students s JOIN branches b ON s.branch_id = b.branch_id WHERE b.branch_name = ?");
$stmt->bind_param("s", $branch_name);
$stmt->execute();
$students = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Staff Dashboard - <?php echo ucfirst($branch_name); ?></title>
  <link rel="stylesheet" href="css/staff.css">
</head>
<body >
  <div class="dashboard">
    <h2>Welcome, <?php echo htmlspecialchars($staff_id); ?></h2>
    <p>Branch: <strong><?php echo ucfirst(htmlspecialchars($branch_name)); ?></strong></p>

    <div class="actions">
      <a href="add_student.php" class="btn"> Add Student</a>
      <a href="mark_attendance.php" class="btn"> Mark Attendance</a>
      <a href="logout.php" class="btn logout"> Logout</a>
    </div>

    
    </table>
  </div>
</body>
</html>
