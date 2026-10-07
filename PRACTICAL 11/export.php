<?php require_once 'db.php';
// export.php — Intermediate extension: Export (filtered) student list to CSV

$search = trim($_GET['q'] ?? '');
$course_filter = trim($_GET['course'] ?? '');
$status_filter = trim($_GET['status'] ?? 'active');
if (!in_array($status_filter, ['active','inactive','all'], true)) $status_filter = 'active';

$where = []; $types = ''; $params = [];
if ($search !== '') {
    $where[] = "(name LIKE CONCAT('%', ?, '%') OR email LIKE CONCAT('%', ?, '%') OR course LIKE CONCAT('%', ?, '%'))";
    $types .= 'sss'; array_push($params, $search, $search, $search);
}
if ($course_filter !== '') { $where[] = "course = ?"; $types .= 's'; $params[] = $course_filter; }
if ($status_filter !== 'all') { $where[] = "status = ?"; $types .= 's'; $params[] = $status_filter; }
$where_sql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

$sql = "SELECT id, name, email, phone, course, age, gender, status, created_at FROM students $where_sql ORDER BY id DESC";
$stmt = $conn->prepare($sql);
if ($params) $stmt->bind_param($types, ...$params);
$stmt->execute();
$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=students_' . date('Ymd_His') . '.csv');
$out = fopen('php://output', 'w');
fputcsv($out, ['ID','Name','Email','Phone','Course','Age','Gender','Status','Created_At']);
foreach ($rows as $r) fputcsv($out, $r);
fclose($out);
exit;
