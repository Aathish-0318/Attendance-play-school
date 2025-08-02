<?php
session_start();
include 'db.php';

if (!isset($_SESSION['staff_id']) || !isset($_SESSION['branch_name'])) {
    header("Location: index.html");
    exit();
}

$branch_name = $_SESSION['branch_name'];

// Fetch students of the branch
$stmt = $conn->prepare("SELECT s.student_id, s.name FROM students s JOIN branches b ON s.branch_id = b.branch_id WHERE b.branch_name = ?");
$stmt->bind_param("s", $branch_name);
$stmt->execute();
$students = $stmt->get_result();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date = $_POST['date'];

    if (!is_dir('uploads')) {
        mkdir('uploads', 0777, true);
    }

    foreach ($_POST['attendance'] as $student_id => $status) {
        $relation = $_POST['guardian_relation'][$student_id];

        // Handle photo upload
        $file_name = $_FILES['guardian_photo']['name'][$student_id];
        $file_tmp = $_FILES['guardian_photo']['tmp_name'][$student_id];
        $upload_path = 'uploads/' . time() . '_' . basename($file_name);
        move_uploaded_file($file_tmp, $upload_path);

        // Insert into attendance table
        $stmt = $conn->prepare("INSERT INTO attendance (student_id, date, status, guardian_photo, guardian_relation)
            VALUES (?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE status=VALUES(status), guardian_photo=VALUES(guardian_photo), guardian_relation=VALUES(guardian_relation)");
        $stmt->bind_param("issss", $student_id, $date, $status, $upload_path, $relation);
        $stmt->execute();
    }

    echo "<script>
        alert('Attendance has been submitted successfully!');
        window.location.href='dashboard_staff.php';
    </script>";
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Mark Attendance</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <!-- Flatpickr CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

  <style>
    * { box-sizing: border-box; }

    body {
      margin: 0;
      padding: 0;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: url('images/a.jpg') no-repeat center center/cover;
      min-height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
    }

    .form-container {
      background-color: rgba(255, 255, 255, 0.95);
      padding: 20px;
      border-radius: 12px;
      width: 90%;
      max-width: 1200px;
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
      overflow-x: auto;
    }

    h2 {
      text-align: center;
      margin-bottom: 20px;
      color: #333;
    }

    label {
      font-weight: bold;
      margin-bottom: 8px;
      display: block;
      color: #444;
    }

    input[type="text"],
    input[type="file"],
    select {
      padding: 8px;
      border-radius: 6px;
      border: 2px solid #333;
      width: 100%;
      margin-bottom: 10px;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 20px;
    }

    table th, table td {
      border: 2px solid black;
      padding: 10px;
      text-align: center;
      vertical-align: middle;
    }

    table th {
      background-color: green;
      color: white;
    }

    table td {
      background-color: #f0f0f0;
    }

    button {
      width: 100%;
      padding: 12px;
      background-color: #0072ff;
      color: white;
      border: none;
      border-radius: 6px;
      font-weight: bold;
      cursor: pointer;
    }

    button:hover {
      background-color: #005ec2;
    }

    a {
      display: block;
      text-align: center;
      margin-top: 15px;
      text-decoration: none;
      color: #0072ff;
      font-weight: bold;
    }

    a:hover {
      text-decoration: underline;
    }

    @media (max-width: 480px) {
      .form-container { padding: 15px; }
      table th, table td { font-size: 14px; padding: 8px; }
      h2 { font-size: 20px; }
      button { padding: 10px; }
    }

    .date-note {
      color: #555;
      font-size: 14px;
      margin-bottom: 10px;
    }
  </style>
</head>
<body>
  <div class="form-container">
    <h2>Mark Attendance - <?php echo ucfirst($branch_name); ?></h2>
    <form method="POST" enctype="multipart/form-data">
      <label for="datepicker">Select Date (DD-MM-YYYY):</label>
      <input type="text" id="datepicker" name="date" required>
      <div class="date-note">Today: <?php echo date("d-m-Y"); ?></div>

      <table>
        <tr>
          <th>Student Name</th>
          <th>Status</th>
          <th>Guardian Photo & Relation</th>
        </tr>
        <?php while($row = $students->fetch_assoc()): ?>
        <tr>
          <td><?php echo htmlspecialchars($row['name']); ?></td>
          <td>
            <select name="attendance[<?php echo $row['student_id']; ?>]">
              <option value="Present">Present</option>
              <option value="Absent">Absent</option>
            </select>
          </td>
          <td>
            <input type="file" name="guardian_photo[<?php echo $row['student_id']; ?>]" accept="image/*" capture="environment" required><br>
            <input type="text" name="guardian_relation[<?php echo $row['student_id']; ?>]" placeholder="e.g., Father, Uncle" required>
          </td>
        </tr>
        <?php endwhile; ?>
      </table>

      <button type="submit">Submit Attendance</button>
    </form>
    <a href="dashboard_staff.php">Back to Dashboard</a>
  </div>

  <!-- Flatpickr JS -->
  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
  <script>
    flatpickr("#datepicker", {
      dateFormat: "Y-m-d",       // For backend (MySQL)
      altInput: true,
      altFormat: "d-m-Y",        // Visible format for users
      defaultDate: new Date()
    });
  </script>
</body>
</html>
