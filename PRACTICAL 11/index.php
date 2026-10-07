<?php require_once 'db.php';

$search = trim($_GET['q'] ?? '');
$course_filter = trim($_GET['course'] ?? '');
$status_filter = trim($_GET['status'] ?? 'active');
if (!in_array($status_filter, ['active', 'inactive', 'all'], true)) $status_filter = 'active';

$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 8;
$offset = ($page - 1) * $limit;

$where = []; $types = ''; $params = [];
if ($search !== '') {
    $where[] = "(name LIKE CONCAT('%', ?, '%') OR email LIKE CONCAT('%', ?, '%') OR course LIKE CONCAT('%', ?, '%'))";
    $types .= 'sss';
    array_push($params, $search, $search, $search);
}
if ($course_filter !== '') { $where[] = "course = ?"; $types .= 's'; $params[] = $course_filter; }
if ($status_filter !== 'all') { $where[] = "status = ?"; $types .= 's'; $params[] = $status_filter; }
$where_sql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

$count_sql = "SELECT COUNT(*) AS total FROM students $where_sql";
$stmt = $conn->prepare($count_sql);
if ($params) $stmt->bind_param($types, ...$params);
$stmt->execute();
$total = (int)$stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

$total_pages = max(1, (int)ceil($total / $limit));
if ($page > $total_pages) { $page = $total_pages; $offset = ($page - 1) * $limit; }

$list_sql = "SELECT id, name, email, phone, course, status FROM students $where_sql ORDER BY id DESC LIMIT ? OFFSET ?";
$stmt = $conn->prepare($list_sql);
$types2 = $types . 'ii';
$params2 = array_merge($params, [$limit, $offset]);
$stmt->bind_param($types2, ...$params2);
$stmt->execute();
$students = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$courses = [];
$res = $conn->query("SELECT DISTINCT course FROM students ORDER BY course");
if ($res) while ($r = $res->fetch_assoc()) $courses[] = $r['course'];

function page_url($p) {
    $q = $_GET; $q['page'] = $p;
    return 'index.php?' . http_build_query($q);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Student Management | Practical 11</title>
<style>
:root{--teal:#0d9488;--ink:#0f172a;--muted:#64748b;--line:#e2e8f0;--bg:#f1f5f9;--rose:#e11d48;--amber:#d97706}
*{box-sizing:border-box;font-family:'Segoe UI',Arial,sans-serif}
body{margin:0;background:var(--bg);color:var(--ink)}
.hero{background:linear-gradient(120deg,#0f172a 10%,#0d9488 90%);color:#fff;padding:26px 0}
.hero-inner{max-width:1080px;margin:0 auto;padding:0 20px;display:flex;justify-content:space-between;align-items:center;gap:16px;flex-wrap:wrap}
.hero h1{margin:0;font-size:26px;letter-spacing:.3px}
.hero p{margin:4px 0 0;color:#ccfbf1;font-size:13px}
.hero-nav{display:flex;gap:10px}
.pill-btn{background:rgba(255,255,255,.15);border:1px solid rgba(255,255,255,.35);color:#fff;padding:8px 16px;border-radius:999px;text-decoration:none;font-size:13px;font-weight:600}
.pill-btn.solid{background:#fff;color:#0f172a;border-color:#fff}
.shell{max-width:1080px;margin:-18px auto 30px;padding:0 20px}
.card{background:#fff;border:1px solid var(--line);border-radius:14px;box-shadow:0 6px 20px rgba(15,23,42,.07);overflow:hidden}
.toolbar{padding:16px 18px;border-bottom:1px solid var(--line);display:flex;gap:10px;flex-wrap:wrap;align-items:center;background:#f8fafc}
.toolbar input[type=text]{flex:1;min-width:230px;padding:10px 14px;border:1px solid var(--line);border-radius:999px;outline:none}
.toolbar input:focus{border-color:var(--teal)}
.toolbar select{padding:10px 12px;border:1px solid var(--line);border-radius:999px;background:#fff}
.btn{padding:10px 18px;border:none;border-radius:999px;cursor:pointer;text-decoration:none;display:inline-block;font-size:13px;font-weight:700}
.btn-go{background:var(--teal);color:#fff}
.btn-reset{background:var(--ink);color:#fff}
.btn-csv{background:#166534;color:#fff}
.meta{display:flex;justify-content:space-between;align-items:center;padding:12px 18px;font-size:13px;color:var(--muted);flex-wrap:wrap;gap:8px}
.count-badge{background:#ccfbf1;color:#0f766e;font-weight:800;padding:4px 12px;border-radius:999px}
.table-wrap{overflow-x:auto}
table{width:100%;border-collapse:collapse;font-size:13.5px;min-width:820px}
thead th{background:var(--ink);color:#fff;text-transform:uppercase;font-size:11px;letter-spacing:.8px;padding:12px 10px;text-align:left}
tbody td{padding:11px 10px;border-bottom:1px solid var(--line)}
tbody tr:nth-child(even){background:#f8fafc}
tbody tr:hover{background:#ecfeff}
.status{display:inline-block;padding:4px 12px;border-radius:999px;font-size:11px;font-weight:800;text-transform:uppercase}
.status.active{background:#dcfce7;color:#166534}
.status.inactive{background:#e2e8f0;color:#475569}
.act{display:flex;gap:8px}
.edit-btn{border:1.5px solid var(--teal);color:var(--teal);padding:6px 14px;border-radius:999px;text-decoration:none;font-size:12px;font-weight:700;background:#fff}
.del-btn{background:var(--rose);color:#fff;padding:6px 14px;border-radius:999px;text-decoration:none;font-size:12px;font-weight:700}
.restore-btn{background:#166534;color:#fff;padding:6px 14px;border-radius:999px;text-decoration:none;font-size:12px;font-weight:700}
.pager{padding:14px 18px;display:flex;gap:8px;align-items:center}
.pager a,.pager span{min-width:32px;height:32px;display:inline-flex;align-items:center;justify-content:center;border:1.5px solid var(--teal);color:var(--teal);border-radius:10px;text-decoration:none;font-size:13px;font-weight:700;padding:0 10px}
.pager .current{background:var(--teal);color:#fff}
.msg-success{background:#dcfce7;color:#166534;border:1px solid #86efac;padding:10px 14px;font-size:14px}
.msg-error{background:#fee2e2;color:#991b1b;border:1px solid #fca5a5;padding:10px 14px;font-size:14px}
.empty{text-align:center;padding:26px;color:var(--muted)}
</style>
</head>
<body>
<div class="hero">
  <div class="hero-inner">
    <div>
      <h1>🎓 Student Management</h1>
      <p>Practical 11 &bull; CRUD + Search &amp; Filter &bull; MySQLi Prepared Statements</p>
    </div>
    <div class="hero-nav">
      <a class="pill-btn solid" href="index.php">All Students</a>
      <a class="pill-btn" href="add.php">+ Add Student</a>
    </div>
  </div>
</div>

<div class="shell">
  <div class="card">
    <?php show_msg(); ?>
    <form class="toolbar" method="GET" action="index.php">
      <input type="text" name="q" placeholder="🔍 Search name / email / course" value="<?php echo e($search); ?>">
      <select name="course">
        <option value="">All Courses</option>
        <?php foreach ($courses as $c): ?>
          <option value="<?php echo e($c); ?>" <?php echo ($course_filter === $c) ? 'selected' : ''; ?>><?php echo e($c); ?></option>
        <?php endforeach; ?>
      </select>
      <select name="status">
        <option value="active" <?php echo $status_filter==='active'?'selected':''; ?>>Active</option>
        <option value="inactive" <?php echo $status_filter==='inactive'?'selected':''; ?>>Inactive</option>
        <option value="all" <?php echo $status_filter==='all'?'selected':''; ?>>All</option>
      </select>
      <button class="btn btn-go" type="submit">Search</button>
      <a class="btn btn-reset" href="index.php">Reset</a>
      <a class="btn btn-csv" href="export.php?<?php echo http_build_query(['q'=>$search,'course'=>$course_filter,'status'=>$status_filter]); ?>">⬇ Export CSV</a>
    </form>

    <div class="meta">
      <span><span class="count-badge"><?php echo $total; ?></span> record(s) found.</span>
      <span>Page <?php echo $page; ?> of <?php echo $total_pages; ?></span>
    </div>

    <div class="table-wrap">
    <table>
      <thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Phone</th><th>Course</th><th>Status</th><th>Actions</th></tr></thead>
      <tbody>
      <?php if (!$students): ?>
        <tr><td colspan="7"><div class="empty">No records found.</div></td></tr>
      <?php else: foreach ($students as $s): ?>
        <tr>
          <td><b>#<?php echo (int)$s['id']; ?></b></td>
          <td><?php echo e($s['name']); ?></td>
          <td><?php echo e($s['email']); ?></td>
          <td><?php echo e($s['phone']); ?></td>
          <td><?php echo e($s['course']); ?></td>
          <td><span class="status <?php echo e($s['status']); ?>"><?php echo e($s['status']); ?></span></td>
          <td>
            <div class="act">
              <a class="edit-btn" href="edit.php?id=<?php echo (int)$s['id']; ?>">Edit</a>
              <?php if ($s['status'] === 'active'): ?>
                <a class="del-btn" href="delete.php?id=<?php echo (int)$s['id']; ?>"
                   onclick="return confirm('Delete <?php echo e($s['name']); ?>? (soft delete)');">Delete</a>
              <?php else: ?>
                <a class="restore-btn" href="delete.php?id=<?php echo (int)$s['id']; ?>&action=restore"
                   onclick="return confirm('Restore this student?');">Restore</a>
              <?php endif; ?>
            </div>
          </td>
        </tr>
      <?php endforeach; endif; ?>
      </tbody>
    </table>
    </div>

    <div class="pager">
      <?php for ($i = 1; $i <= $total_pages; $i++): ?>
        <?php if ($i === $page): ?><span class="current"><?php echo $i; ?></span>
        <?php else: ?><a href="<?php echo e(page_url($i)); ?>"><?php echo $i; ?></a><?php endif; ?>
      <?php endfor; ?>
    </div>
  </div>
</div>
</body>
</html>
