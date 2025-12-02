<?php
include 'ketnoi.php'; // Kết nối CSDL

$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Kết quả tìm kiếm</title>
  <link rel="stylesheet" href="css/bootstrap.min.css">
  <link rel="stylesheet" href="css/style.css">
  <style>
    body { background-color: #0b0c2a; color: #fff; }
    .card { background-color: #1b1d3c; border: none; }
    .card h5 { color: #ffd700; }
    .meta-info { font-size: 14px; color: #ccc; }
  </style>
</head>
<body>
  <div class="container my-5">
    <h2 class="mb-4 text-warning">Kết quả tìm kiếm cho: 
      <span class="text-light"><?php echo htmlspecialchars($keyword); ?></span>
    </h2>

    <?php
    if ($keyword != '') {
        // JOIN với bảng categories & authors
       $stmt = $conn->prepare("
  SELECT a.*, c.name AS category_name, au.name AS author_name
  FROM articles a
  LEFT JOIN categories c ON a.category_id = c.category_id
  LEFT JOIN authors au ON a.author_id = au.author_id
  WHERE a.title LIKE ? OR a.content LIKE ?
  ORDER BY a.created_at DESC
");
        $search = "%$keyword%";
        $stmt->bind_param("ss", $search, $search);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            echo '<div class="row">';
            while ($row = $result->fetch_assoc()) {
                echo '
                <div class="col-md-4 mb-4">
                  <div class="card h-100 shadow">
                    <img src="' . $row['featured_image'] . '" class="card-img-top" alt="Ảnh bài viết">
                    <div class="card-body">
                      <h5 class="card-title">' . htmlspecialchars($row['title']) . '</h5>
                      <p class="meta-info mb-2">
                        🕒 ' . date("d/m/Y", strtotime($row['created_at'])) . '<br>
                        🧑 ' . htmlspecialchars($row['author_name'] ?: 'Không rõ') . '<br>
                        📂 ' . htmlspecialchars($row['category_name'] ?: 'Chưa phân loại') . '
                      </p>
                      <p class="card-text">' . htmlspecialchars(substr($row['excerpt'], 0, 120)) . '...</p>
                      <a href="article.php?slug=' . urlencode($row['slug']) . '" class="btn btn-warning btn-sm">Đọc tiếp</a>
                    </div>
                  </div>
                </div>';
            }
            echo '</div>';
        } else {
            echo '<div class="alert alert-info">Không tìm thấy bài viết nào phù hợp.</div>';
        }

        $stmt->close();
    } else {
        echo '<div class="alert alert-warning">Vui lòng nhập từ khóa tìm kiếm.</div>';
    }

    $conn->close();
    ?>
  </div>
</body>
</html>
