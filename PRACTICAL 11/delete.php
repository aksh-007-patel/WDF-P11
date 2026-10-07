<?php require_once 'db.php';
// delete.php — Soft Delete (Advanced extension) + Restore + Permanent delete
// Confirmed before execution: JS confirm on list + this confirmation page.

$id = (int)($_REQUEST['id'] ?? 0);
$action = $_GET['action'] ?? 'delete'; // delete | restore | permanent
if ($id <= 0) { set_msg('error', 'Invalid student ID.'); header("Location: index.php"); exit; }

// Fetch row (MySQLi prepared)
$stmt = $conn->prepare("SELECT id, name, email, course, status FROM students WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$student) { set_msg('error', "Student ID $id not found."); header("Location: index.php"); exit; }

// Handle confirmed POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $confirm = $_POST['confirm'] ?? 'no';
    if ($confirm !== 'yes') { set_msg('error', 'Delete cancelled.'); header("Location: index.php"); exit; }
    try {
        if ($action === 'restore') {
            $stmt = $conn->prepare("UPDATE students SET status='active' WHERE id=?");
            $stmt->bind_param("i", $id);
            $stmt->execute(); $stmt->close();
            set_msg('success', "Student '{$student['name']}' restored successfully.");
        } elseif ($action === 'permanent') {
            $stmt = $conn->prepare("DELETE FROM students WHERE id=?");
            $stmt->bind_param("i", $id);
            $stmt->execute(); $stmt->close();
            set_msg('success', "Student '{$student['name']}' permanently deleted.");
        } else {
            // Soft delete: UPDATE status instead of DELETE
            $stmt = $conn->prepare("UPDATE students SET status='inactive' WHERE id=?");
            $stmt->bind_param("i", $id);
            $stmt->execute(); $stmt->close();
            set_msg('success', "Student '{$student['name']}' moved to Inactive (soft deleted).");
        }
    } catch (mysqli_sql_exception $ex) {
        set_msg('error', "Operation failed: " . $ex->getMessage());
    }
    header("Location: index.php");
    exit;
}

$title = $action === 'restore' ? 'Restore Student' : ($action === 'permanent' ? 'Permanently Delete Student' : 'Delete Student (Soft Delete)');
$btnLabel = $action === 'restore' ? 'Yes, Restore' : 'Yes, Delete';
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title><?php echo e($title); ?></title>
<style>*{box-sizing:border-box;font-family:'Segoe UI',Arial}body{background:#f1f5f9;margin:0}.topbar{background:linear-gradient(120deg,#0f172a,#0d9488);color:#fff;padding:16px 22px}.topbar h2{margin:0}.box{max-width:540px;margin:24px auto;background:#fff;padding:26px;border-radius:14px;box-shadow:0 6px 20px rgba(15,23,42,.08);border:1px solid #e2e8f0}.btn{padding:10px 20px;border:none;border-radius:999px;cursor:pointer;text-decoration:none;color:#fff;font-size:14px;font-weight:700}.btn-r{background:#e11d48}.btn-g{background:#166534}.btn-b{background:#0f172a}.info{background:#f8fafc;border:1px solid #e2e8f0;padding:12px;border-radius:10px;margin:12px 0}</style>
</head>
<body>
<div class="topbar"><h2>🎓 Student Management</h2></div>
<div class="box">
  <h2><?php echo e($title); ?></h2>
  <div class="info">
    <b>ID:</b> <?php echo (int)$student['id']; ?><br>
    <b>Name:</b> <?php echo e($student['name']); ?><br>
    <b>Email:</b> <?php echo e($student['email']); ?><br>
    <b>Course:</b> <?php echo e($student['course']); ?> |
    <b>Status:</b> <?php echo e($student['status']); ?>
  </div>
  <p><?php echo $action==='restore' ? 'Are you sure you want to restore this student to Active?' : 'Are you sure? (Soft delete keeps the record with status = inactive.)'; ?></p>
  <form method="POST">
    <input type="hidden" name="confirm" value="yes">
    <button class="btn <?php echo $action==='restore'?'btn-g':'btn-r'; ?>" type="submit"><?php echo $btnLabel; ?></button>
    <a class="btn btn-b" href="index.php">No, Cancel</a>
  </form>
  <?php if ($action !== 'permanent' && $student['status']==='inactive'): ?>
    <p style="margin-top:14px"><a href="delete.php?id=<?php echo (int)$id; ?>&action=permanent" onclick="return confirm('Permanently remove this record? This cannot be undone.');">Permanently delete instead</a></p>
  <?php endif; ?>
</div>
</body>
</html>
