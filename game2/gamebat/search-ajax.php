<?php
include 'ketnoi.php';

$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';

if ($keyword != '') {
    $sql = "SELECT article_id, title FROM articles 
            WHERE title LIKE '%" . $conn->real_escape_string($keyword) . "%' 
            AND status = 'published'
            ORDER BY created_at DESC 
            LIMIT 5";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo '<a href="article.php?id=' . intval($row['article_id']) . '">' . htmlspecialchars($row['title']) . '</a>';
        }
    } else {
        echo '<p>Không tìm thấy bài viết.</p>';
    }
}
?>
