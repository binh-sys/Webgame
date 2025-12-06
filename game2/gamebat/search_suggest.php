<?php
include 'ketnoi.php';
header('Content-Type: application/json; charset=utf-8');

$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';

if ($keyword === '' || mb_strlen($keyword, 'UTF-8') < 1) {
    echo json_encode([]);
    exit;
}

$suggestions = [];

// Tìm kiếm không phân biệt hoa thường
// MySQL LIKE mặc định không phân biệt hoa thường với utf8_general_ci
$search = "%$keyword%";

try {
    // Tìm theo tiêu đề bài viết, nội dung, danh mục và tags
    $sql = "SELECT DISTINCT a.article_id, a.title, a.slug, c.name AS category_name, u.display_name AS author_name
            FROM articles a
            LEFT JOIN categories c ON a.category_id = c.category_id
            LEFT JOIN users u ON a.author_id = u.user_id
            LEFT JOIN article_tags at ON a.article_id = at.article_id
            LEFT JOIN tags t ON at.tag_id = t.tag_id
            WHERE a.status = 'published' 
            AND (
                a.title LIKE ?
                OR a.content LIKE ?
                OR a.excerpt LIKE ?
                OR c.name LIKE ?
                OR t.name LIKE ?
            )
            ORDER BY 
                CASE 
                    WHEN a.title LIKE ? THEN 1
                    WHEN c.name LIKE ? THEN 2
                    WHEN t.name LIKE ? THEN 3
                    ELSE 4
                END,
                a.created_at DESC
            LIMIT 10";

    // Tìm từ đầu chuỗi để ưu tiên
    $search_start = "$keyword%";

    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("ssssssss", $search, $search, $search, $search, $search, $search_start, $search_start, $search_start);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $suggestions[] = [
                'id' => $row['article_id'],
                'title' => $row['title'],
                'slug' => $row['slug'],
                'author' => $row['author_name'] ?: 'Ẩn danh',
                'category' => $row['category_name'] ?: 'Chưa phân loại'
            ];
        }
        $stmt->close();
    }
} catch (Exception $e) {
    // Fallback: tìm kiếm đơn giản chỉ theo tiêu đề
    $sql_simple = "SELECT article_id, title, slug FROM articles 
                   WHERE status = 'published' AND title LIKE ? 
                   ORDER BY created_at DESC LIMIT 10";
    $stmt = $conn->prepare($sql_simple);
    if ($stmt) {
        $stmt->bind_param("s", $search);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $suggestions[] = [
                'id' => $row['article_id'],
                'title' => $row['title'],
                'slug' => $row['slug'],
                'author' => 'Ẩn danh',
                'category' => 'Chưa phân loại'
            ];
        }
        $stmt->close();
    }
}

echo json_encode($suggestions, JSON_UNESCAPED_UNICODE);
$conn->close();
?>
