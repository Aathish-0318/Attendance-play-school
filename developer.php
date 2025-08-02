<?php
include 'db.php';

// Handle delete requests
if (isset($_GET['delete_student'])) {
    $id = $_GET['delete_student'];
    $conn->query("DELETE FROM students WHERE student_id = $id");
}

if (isset($_GET['delete_attendance'])) {
    $id = $_GET['delete_attendance'];
    $conn->query("DELETE FROM attendance WHERE student_id = $id");
}

// Fetch student and branch data
$students = $conn->query("
    SELECT s.student_id, s.name, s.age, b.branch_name
    FROM students s
    JOIN branches b ON s.branch_id = b.branch_id
");

// Fetch attendance data
$attendances = $conn->query("SELECT * FROM attendance");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Developer Panel</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #f2f2f2;
      margin: 0;
      padding: 20px;
    }

    h2 {
      text-align: center;
      margin-top: 10px;
      color: #333;
    }

    .table-container {
      margin-bottom: 40px;
      overflow-x: auto;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 10px;
      background: #fff;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }

    th, td {
      padding: 10px;
      border: 1px solid #ddd;
      text-align: center;
    }

    th {
      background-color: #0072ff;
      color: white;
    }

    td img {
      max-width: 80px;
      border-radius: 5px;
    }

    a.btn {
      padding: 6px 12px;
      border-radius: 5px;
      font-size: 14px;
      text-decoration: none;
      margin: 0 4px;
      display: inline-block;
    }

    .edit-btn {
      background-color: #28a745;
      color: white;
    }

    .delete-btn {
      background-color: #dc3545;
      color: white;
    }

    .back-btn {
      display: block;
      text-align: center;
      margin-top: 30px;
      text-decoration: none;
      font-weight: bold;
      color: #0072ff;
    }

    /* Mobile responsiveness */
    @media (max-width: 768px) {
      table, thead, tbody, th, td, tr {
        display: block;
      }

      thead {
        display: none;
      }

      tr {
        margin-bottom: 15px;
        border: 1px solid #ccc;
        border-radius: 8px;
        padding: 10px;
        background-color: #fff;
      }

      td {
        text-align: left;
        padding: 8px 10px;
        border: none;
        position: relative;
      }

      td::before {
        content: attr(data-label);
        font-weight: bold;
        display: block;
        color: #0072ff;
        margin-bottom: 4px;
      }

      td img {
        max-width: 100px;
      }

      a.btn {
        margin-top: 5px;
      }
    }
  </style>
</head>
<body>

<h2>Developer Panel - Manage Students</h2>
<div class="table-container">
  <table>
    <thead>
      <tr>
        <th>Student ID</th>
        <th>Name</th>
        <th>Age</th>
        <th>Branch</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php while ($row = $students->fetch_assoc()): ?>
        <tr>
          <td data-label="Student ID"><?= $row['student_id']; ?></td>
          <td data-label="Name"><?= htmlspecialchars($row['name']); ?></td>
          <td data-label="Age"><?= $row['age']; ?></td>
          <td data-label="Branch"><?= htmlspecialchars($row['branch_name']); ?></td>
          <td data-label="Actions">
            <a class="btn edit-btn" href="edit_student.php?id=<?= $row['student_id']; ?>">Edit</a>
            <a class="btn delete-btn" href="?delete_student=<?= $row['student_id']; ?>" onclick="return confirm('Delete student?')">Delete</a>
          </td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>

<h2>Developer Panel - Manage Attendance</h2>
<div class="table-container">
  <table>
    <thead>
      <tr>
        <th>Student ID</th>
        <th>Date</th>
        <th>Status</th>
        <th>Guardian Photo</th>
        <th>Relation</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php while ($row = $attendances->fetch_assoc()): ?>
        <tr>
          <td data-label="Student ID"><?= $row['student_id']; ?></td>
          <td data-label="Date"><?= $row['date']; ?></td>
          <td data-label="Status"><?= $row['status']; ?></td>
          <td data-label="Guardian Photo">
            <?php if (!empty($row['guardian_photo']) && file_exists($row['guardian_photo'])): ?>
              <img src="<?= $row['guardian_photo']; ?>" alt="Guardian Photo">
            <?php else: ?>
              <span style="color:gray;">No Photo</span>
            <?php endif; ?>
          </td>
          <td data-label="Relation"><?= htmlspecialchars($row['guardian_relation']); ?></td>
          <td data-label="Actions">
            <a class="btn delete-btn" href="?delete_attendance=<?= $row['student_id']; ?>" onclick="return confirm('Delete attendance?')">Delete</a>
          </td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>

<a href="index.html" class="back-btn">← Back to Index</a>

</body>
</html>
