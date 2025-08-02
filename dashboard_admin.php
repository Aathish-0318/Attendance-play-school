<?php
session_start();
include 'db.php';

if (!isset($_SESSION['admin'])) {
    header("Location: index.php");
    exit();
}

$branch_result = $conn->query("SELECT * FROM branches");
$branches = [];
while ($row = $branch_result->fetch_assoc()) {
    $branches[] = $row;
}

$selected_branch_id = $_GET['branch_id'] ?? '';
$students = [];
$show_attendance = isset($_GET['attendance_summary']);

if ($selected_branch_id) {
    if ($show_attendance) {
        $stmt = $conn->prepare("
            SELECT s.student_id, s.name, s.age, b.branch_name,
                   a.status, a.date AS latest_date, a.guardian_photo, a.guardian_relation
            FROM students s
            JOIN branches b ON s.branch_id = b.branch_id
            LEFT JOIN (
                SELECT a1.*
                FROM attendance a1
                JOIN (
                    SELECT student_id, MAX(date) AS latest_date
                    FROM attendance
                    GROUP BY student_id
                ) a2 ON a1.student_id = a2.student_id AND a1.date = a2.latest_date
            ) a ON s.student_id = a.student_id
            WHERE s.branch_id = ?
        ");
    } else {
        $stmt = $conn->prepare("SELECT s.student_id, s.name, s.age, b.branch_name 
            FROM students s 
            JOIN branches b ON s.branch_id = b.branch_id 
            WHERE s.branch_id = ?");
    }

    $stmt->bind_param("i", $selected_branch_id);
    $stmt->execute();
    $students = $stmt->get_result();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin Dashboard</title>
  <style>
    * {
      box-sizing: border-box;
    }

    body {
      margin: 0;
      font-family: 'Segoe UI', Tahoma, sans-serif;
      background: linear-gradient(to right, #74ebd5, #acb6e5);
      padding: 20px;
    }

    .dashboard {
      background: #fff;
      padding: 20px;
      border-radius: 12px;
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
      max-width: 1000px;
      margin: 0 auto;
      overflow-x: auto;
    }

    h2, h3 {
      text-align: center;
      color: #333;
    }

    form {
      display: flex;
      flex-direction: column;
      align-items: center;
      margin-bottom: 20px;
    }

    select {
      padding: 10px;
      font-size: 16px;
      border-radius: 6px;
      border: 1px solid #ccc;
      margin-bottom: 10px;
      width: 100%;
      max-width: 300px;
    }

    .btn-group {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
      justify-content: center;
    }

    .btn {
      padding: 10px 20px;
      font-size: 16px;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      color: #fff;
      text-decoration: none;
      display: inline-block;
    }

    .view-btn { background-color: #0072ff; }
    .attendance-btn { background-color: #28a745; }
    .edit-btn { background-color: #17a2b8; }
    .delete-btn { background-color: #dc3545; }
    .att-btn {
      background-color: #ffc107;
      color: #000;
    }

    .btn:hover { opacity: 0.9; }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
    }

    th, td {
      border: 1px solid #ccc;
      padding: 12px;
      text-align: center;
    }

    th {
      background-color: #0072ff;
      color: #fff;
    }

    td {
      background-color: #f9f9f9;
    }

    .status-present { color: green; font-weight: bold; }
    .status-absent { color: red; font-weight: bold; }

    img.guardian-img {
      width: 60px;
      border-radius: 6px;
    }

    .btn-action {
      color: white;
      padding: 5px 10px;
      margin: 2px;
      border-radius: 5px;
      font-size: 14px;
      text-decoration: none;
      display: inline-block;
    }

    @media (max-width: 768px) {
      table, thead, tbody, th, td, tr {
        display: block;
      }

      thead {
        display: none;
      }

      tr {
        margin-bottom: 15px;
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 0 10px rgba(0,0,0,0.05);
        padding: 10px;
      }

      td {
        text-align: right;
        padding-left: 50%;
        position: relative;
        border: none;
        border-bottom: 1px solid #ccc;
      }

      td::before {
        position: absolute;
        left: 10px;
        top: 12px;
        white-space: nowrap;
        font-weight: bold;
        color: #0072ff;
        content: attr(data-label);
      }

      img.guardian-img {
        width: 100px;
      }
    }
  </style>
</head>
<body>
  <div class="dashboard">
    <h2>Admin Dashboard</h2>

    <form method="GET">
      <label><strong>Select Branch:</strong></label>
      <select name="branch_id" required>
        <option value="">-- Select Branch --</option>
        <?php foreach ($branches as $branch): ?>
          <option value="<?= $branch['branch_id']; ?>" <?= $selected_branch_id == $branch['branch_id'] ? 'selected' : '' ?>>
            <?= ucfirst($branch['branch_name']); ?>
          </option>
        <?php endforeach; ?>
      </select>

      <div class="btn-group">
        <button type="submit" name="view" class="btn view-btn">View Students</button>
        <button type="submit" name="attendance_summary" class="btn attendance-btn">View Attendance Summary</button>
      </div>
    </form>

    <?php if ($students && $students->num_rows > 0): ?>
      <h3><?= $show_attendance ? "Latest Attendance Summary" : "Student List" ?></h3>
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Age</th>
            <th>Branch</th>
            <?php if ($show_attendance): ?>
              <th>Date</th>
              <th>Status</th>
              <th>Guardian Photo</th>
              <th>Relation</th>
            <?php else: ?>
              <th>Actions</th>
            <?php endif; ?>
          </tr>
        </thead>
        <tbody>
          <?php while ($row = $students->fetch_assoc()): ?>
            <tr>
              <td data-label="ID"><?= $row['student_id']; ?></td>
              <td data-label="Name"><?= htmlspecialchars($row['name']); ?></td>
              <td data-label="Age"><?= $row['age']; ?></td>
              <td data-label="Branch"><?= ucfirst($row['branch_name']); ?></td>

              <?php if ($show_attendance): ?>
                <td data-label="Date"><?= $row['latest_date'] ?? '-' ?></td>
                <td data-label="Status">
                  <?php
                    if ($row['status'] === 'Present') {
                      echo '<span class="status-present">Present</span>';
                    } elseif ($row['status'] === 'Absent') {
                      echo '<span class="status-absent">Absent</span>';
                    } else {
                      echo '<span style="color:#999;">No Record</span>';
                    }
                  ?>
                </td>
                <td data-label="Guardian Photo">
                  <?php if (!empty($row['guardian_photo']) && file_exists($row['guardian_photo'])): ?>
                    <img src="<?= htmlspecialchars($row['guardian_photo']); ?>" alt="Guardian" class="guardian-img">
                  <?php else: ?>
                    <span style="color:gray;">No Photo</span>
                  <?php endif; ?>
                </td>
                <td data-label="Relation"><?= htmlspecialchars($row['guardian_relation'] ?? '—'); ?></td>
              <?php else: ?>
                <td data-label="Actions">
                  <a href="edit_student.php?id=<?= $row['student_id']; ?>" class="btn-action edit-btn">Edit</a>
                  <a href="delete_student.php?id=<?= $row['student_id']; ?>" class="btn-action delete-btn" onclick="return confirm('Are you sure?')">Delete</a>
                  <a href="view_attendance.php?id=<?= $row['student_id']; ?>" class="btn-action att-btn">Attendance</a>
                </td>
              <?php endif; ?>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    <?php elseif (isset($_GET['view']) || $show_attendance): ?>
      <p style="text-align:center; color:red;">No students found in this branch.</p>
    <?php endif; ?>

    <div style="text-align:center; margin-top: 25px;">
      <a href="index.php" class="btn view-btn">← Back to Home</a>
    </div>
  </div>
</body>
</html>
