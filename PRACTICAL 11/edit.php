<?php require_once 'db.php';

// SELECT one record with MySQLi prepared statement
$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) { set_msg('error', 'Invalid student ID.'); header("Location: index.php"); exit; }

$stmt = $conn->prepare("SELECT id, name, email, phone, course, age, gender, status FROM students WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$student) { set_msg('error', "Student ID $id not found."); header("Location: index.php"); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name   = trim($_POST['name'] ?? '');
    $email  = trim($_POST['email'] ?? '');
    $phone  = trim($_POST['phone'] ?? '');
    $course = trim($_POST['course'] ?? '');
    $age    = trim($_POST['age'] ?? '');
    $gender = trim($_POST['gender'] ?? '');

    if ($name===''||$email===''||$phone===''||$course===''||$age===''||$gender==='') {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } elseif (!preg_match('/^[0-9]{10}$/', $phone)) {
        $error = "Phone must be exactly 10 digits.";
    } elseif (!filter_var($age, FILTER_VALIDATE_INT, ["options"=>["min_range"=>1,"max_range"=>120]])) {
        $error = "Age must be between 1 and 120.";
    } elseif (!in_array($gender, ['Male','Female','Other'], true)) {
        $error = "Invalid gender.";
    } else {
        // UPDATE using MySQLi prepared statement
        try {
            $stmt = $conn->prepare("UPDATE students SET name=?, email=?, phone=?, course=?, age=?, gender=? WHERE id=?");
            $age_int = (int)$age;
            $stmt->bind_param("ssssisi", $name, $email, $phone, $course, $age_int, $gender, $id);
            $stmt->execute();
            $stmt->close();
            set_msg('success', "Student ID $id updated successfully.");
            header("Location: index.php");
            exit;
        } catch (mysqli_sql_exception $ex) {
            $error = ($ex->getCode() === 1062) ? "Email already exists." : "Update failed: " . $ex->getMessage();
        }
    }
    $student = ['id'=>$id,'name'=>$name,'email'=>$email,'phone'=>$phone,'course'=>$course,'age'=>$age,'gender'=>$gender] + $student;
}
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>Edit Student</title>
<style>*{box-sizing:border-box;font-family:'Segoe UI',Arial}body{background:#f1f5f9;margin:0}.topbar{background:linear-gradient(120deg,#0f172a,#0d9488);color:#fff;padding:16px 22px}.topbar h2{margin:0}.box{max-width:580px;margin:24px auto;background:#fff;padding:26px;border-radius:14px;box-shadow:0 6px 20px rgba(15,23,42,.08);border:1px solid #e2e8f0}label{display:block;margin:12px 0 5px;font-weight:700;font-size:13px}input,select{width:100%;padding:10px 12px;border:1px solid #e2e8f0;border-radius:10px}.btn{padding:10px 20px;border:none;border-radius:999px;cursor:pointer;text-decoration:none;color:#fff;font-size:14px;font-weight:700}.btn-p{background:#0d9488}.btn-b{background:#0f172a}.err{background:#fee2e2;color:#991b1b;padding:10px;border-radius:10px}</style>
</head>
<body>
<div class="topbar"><h2>🎓 Student Management</h2></div>
<div class="box">
  <h2>Edit Student #<?php echo (int)$student['id']; ?> (UPDATE)</h2>
  <?php if ($error) echo '<div class="err">'.htmlspecialchars($error).'</div>'; ?>
  <form method="POST">
    <label>Name</label><input name="name" value="<?php echo e($student['name']); ?>" required>
    <label>Email</label><input type="email" name="email" value="<?php echo e($student['email']); ?>" required>
    <label>Phone</label><input name="phone" pattern="[0-9]{10}" value="<?php echo e($student['phone']); ?>" required>
    <label>Course</label>
    <select name="course" required>
      <?php foreach (['BCA','MCA','B.Tech','M.Tech','BBA','MBA','B.Sc'] as $c): ?>
        <option <?php echo ($student['course']===$c)?'selected':''; ?>><?php echo $c; ?></option>
      <?php endforeach; ?>
    </select>
    <label>Age</label><input type="number" name="age" min="1" max="120" value="<?php echo e((string)$student['age']); ?>" required>
    <label>Gender</label>
    <select name="gender" required>
      <?php foreach (['Male','Female','Other'] as $g): ?>
        <option <?php echo ($student['gender']===$g)?'selected':''; ?>><?php echo $g; ?></option>
      <?php endforeach; ?>
    </select>
    <br><br>
    <button class="btn btn-p" type="submit">Update</button>
    <a class="btn btn-b" href="index.php">Back</a>
  </form>
</div>
</body>
</html>
