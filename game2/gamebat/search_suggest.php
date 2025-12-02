<?php
include 'ketnoi.php';
header('Content-Type: application/json; charset=utf-8');

$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';

if ($keyword === '') {
  echo json_encode([]);
  exit;
}

$stmt = $conn->prepare("
  SELECT a.*, c.name AS category_name, au.name AS author_name
  FROM articles a
  LEFT JOIN categories c ON a.category_id = c.category_id
  LEFT JOIN authors au ON a.author_id = au.author_id
  WHERE a.title LIKE ? OR a.content LIKE ?
  ORDER BY a.created_at DESC
");

$search = "%$keyword%";
$stmt->bind_param("ss", $search, $search); // ✅ sửa tại đây
$stmt->execute();
$result = $stmt->get_result();

$suggestions = [];

while ($row = $result->fetch_assoc()) {
  $suggestions[] = [
    'title' => $row['title'],
    'slug' => $row['slug'],
    'author' => $row['author_name'] ?: 'Không rõ',
    'category' => $row['category_name'] ?: 'Chưa phân loại'
  ];
}

echo json_encode($suggestions, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

$stmt->close();
$conn->close();
?>
