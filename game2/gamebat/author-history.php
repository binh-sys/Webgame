<?php

require_once 'ketnoi.php';

// Chỉ cho tác giả xem
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'editor') {
    header("Location: login.php");
    exit;
}

$user_id = intval($_SESSION['user_id']);

// Lấy danh sách bài viết của tác giả
$sql = "
    SELECT a.article_id, a.title, a.slug, a.status, a.created_at, a.views,
           c.name AS category_name
    FROM articles a
    LEFT JOIN categories c ON a.category_id = c.category_id
    WHERE a.author_id = $user_id
    ORDER BY a.created_at DESC
";
$result = mysqli_query($conn, $sql);
?>
<?php include 'header.php'; ?> 
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Lịch sử đăng bài</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
</head>

<body class="bg-dark text-light">

<div class="container py-5">

    <h3 class="mb-4 text-warning fw-bold">
        <i class="fa fa-history me-2"></i> Lịch Sử Đăng Bài
    </h3>

    <table class="table table-dark table-hover align-middle">
        <thead>
            <tr>
                <th>Tiêu đề</th>
                <th>Danh mục</th>
                <th>Ngày đăng</th>
                <th>Lượt xem</th>
                <th>Trạng thái</th>
                <th style="width:180px">Hành động</th>
            </tr>
        </thead>
        <tbody>

        <?php while ($row = mysqli_fetch_assoc($result)): ?>
            <tr>
                <td>
                    <a href="article.php?slug=<?php echo $row['slug']; ?>" class="text-info">
                        <?php echo htmlspecialchars($row['title']); ?>
                    </a>
                </td>

                <td><?php echo $row['category_name']; ?></td>
                <td><?php echo date("d/m/Y", strtotime($row['created_at'])); ?></td>
                <td><?php echo $row['views']; ?></td>

                <td>
                    <?php if ($row['status'] == 'pending'): ?>
                        <span class="badge bg-warning text-dark">Đang chờ duyệt</span>

                    <?php elseif ($row['status'] == 'published'): ?>
                        <span class="badge bg-success">Đã duyệt</span>

                    <?php elseif ($row['status'] == 'rejected'): ?>
                        <span class="badge bg-danger">Bị từ chối</span>

                    <?php else: ?>
                        <span class="badge bg-secondary"><?php echo $row['status']; ?></span>
                    <?php endif; ?>
                </td>

                <td>
                    <a href="edit-article.php?id=<?php echo $row['article_id']; ?>" class="btn btn-sm btn-primary">
                        <i class="fa fa-edit"></i> Sửa
                    </a>

                    <a href="delete-article.php?id=<?php echo $row['article_id']; ?>" 
                       class="btn btn-sm btn-danger"
                       onclick="return confirm('Bạn chắc chắn muốn xóa bài này?');">
                        <i class="fa fa-trash"></i> Xóa
                    </a>
                </td>
            </tr>
        <?php endwhile; ?>

        </tbody>
    </table>

</div>
<?php include 'footer.php'; ?>
</body>
</html>
