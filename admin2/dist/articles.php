<?php
require_once('ketnoi.php');

// Lấy danh sách bài viết cùng tên danh mục và tác giả
$sql = "SELECT a.article_id, a.title, a.slug, a.status, a.views, a.created_at, a.featured_image,
        c.name AS category_name, u.display_name AS author_name
        FROM articles a
        LEFT JOIN categories c ON a.category_id = c.category_id
        LEFT JOIN users u ON a.author_id = u.user_id
        ORDER BY a.created_at DESC";
$query = mysqli_query($ketnoi, $sql);

// Đếm thống kê
$total = mysqli_num_rows($query);
$published = mysqli_fetch_assoc(mysqli_query($ketnoi, "SELECT COUNT(*) as c FROM articles WHERE status='published'"))['c'];
$draft = mysqli_fetch_assoc(mysqli_query($ketnoi, "SELECT COUNT(*) as c FROM articles WHERE status='draft'"))['c'];
?>

<link rel="stylesheet" href="assets/css/admin-forms.css">

<style>
/* Stats Cards */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 16px;
    margin-bottom: 24px;
}

.stat-card {
    background: linear-gradient(145deg, var(--bg-card), #080c12);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-lg);
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 16px;
    transition: var(--transition-normal);
}

.stat-card:hover {
    border-color: var(--border-hover);
    transform: translateY(-2px);
}

.stat-icon {
    width: 50px;
    height: 50px;
    border-radius: var(--radius-md);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
}

.stat-icon.blue { background: rgba(0, 212, 255, 0.15); color: var(--primary); }
.stat-icon.green { background: rgba(0, 255, 136, 0.15); color: var(--accent-green); }
.stat-icon.orange { background: rgba(255, 149, 0, 0.15); color: var(--accent-orange); }

.stat-info h3 {
    font-size: 28px;
    font-weight: 700;
    color: var(--text-primary);
    margin: 0;
}

.stat-info p {
    font-size: 13px;
    color: var(--text-muted);
    margin: 4px 0 0;
}

/* Table Card */
.table-card {
    background: linear-gradient(145deg, var(--bg-card), #080c12);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-xl);
    overflow: hidden;
}

.table-header {
    padding: 20px 24px;
    border-bottom: 1px solid var(--border-color);
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 16px;
}

.table-title {
    display: flex;
    align-items: center;
    gap: 10px;
    color: var(--primary);
    font-size: 16px;
    font-weight: 600;
}

.table-title i {
    font-size: 20px;
}

/* Data Table */
.data-table {
    width: 100%;
    border-collapse: collapse;
}

.data-table th {
    background: rgba(0, 212, 255, 0.05);
    color: var(--primary);
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 14px 16px;
    text-align: left;
    border-bottom: 1px solid var(--border-color);
}

.data-table td {
    padding: 16px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.03);
    color: var(--text-primary);
    font-size: 14px;
    vertical-align: middle;
}

.data-table tbody tr {
    transition: var(--transition-fast);
}

.data-table tbody tr:hover {
    background: rgba(0, 212, 255, 0.03);
}

/* Article Title Cell */
.article-cell {
    display: flex;
    align-items: center;
    gap: 12px;
}

.article-thumb {
    width: 50px;
    height: 50px;
    border-radius: var(--radius-sm);
    object-fit: cover;
    border: 1px solid var(--border-color);
}

.article-thumb-placeholder {
    width: 50px;
    height: 50px;
    border-radius: var(--radius-sm);
    background: var(--bg-input);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--text-muted);
    font-size: 20px;
}

.article-info h4 {
    margin: 0 0 4px;
    font-size: 14px;
    font-weight: 600;
    color: var(--text-primary);
    max-width: 280px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.article-info span {
    font-size: 12px;
    color: var(--text-muted);
}

/* Status Badge */
.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}

.status-badge.published {
    background: rgba(0, 255, 136, 0.15);
    color: var(--accent-green);
    border: 1px solid rgba(0, 255, 136, 0.3);
}

.status-badge.draft {
    background: rgba(255, 149, 0, 0.15);
    color: var(--accent-orange);
    border: 1px solid rgba(255, 149, 0, 0.3);
}

/* Action Buttons */
.action-btns {
    display: flex;
    gap: 8px;
}

.action-btn {
    width: 36px;
    height: 36px;
    border-radius: var(--radius-sm);
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1px solid var(--border-color);
    background: transparent;
    color: var(--text-secondary);
    cursor: pointer;
    transition: var(--transition-fast);
    text-decoration: none;
}

.action-btn:hover {
    border-color: var(--primary);
    color: var(--primary);
    background: rgba(0, 212, 255, 0.1);
}

.action-btn.approve:hover {
    border-color: var(--accent-green);
    color: var(--accent-green);
    background: rgba(0, 255, 136, 0.1);
}

.action-btn.edit:hover {
    border-color: var(--accent-yellow);
    color: var(--accent-yellow);
    background: rgba(255, 213, 0, 0.1);
}

.action-btn.delete:hover {
    border-color: var(--accent-red);
    color: var(--accent-red);
    background: rgba(255, 71, 87, 0.1);
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 60px 20px;
    color: var(--text-muted);
}

.empty-state i {
    font-size: 48px;
    margin-bottom: 16px;
    opacity: 0.5;
}

.empty-state p {
    margin: 0;
}
</style>

<div class="admin-form-container">
    <!-- Stats -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon blue"><i class='bx bx-news'></i></div>
            <div class="stat-info">
                <h3><?= $total ?></h3>
                <p>Tổng bài viết</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon green"><i class='bx bx-check-circle'></i></div>
            <div class="stat-info">
                <h3><?= $published ?></h3>
                <p>Đã xuất bản</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon orange"><i class='bx bx-time'></i></div>
            <div class="stat-info">
                <h3><?= $draft ?></h3>
                <p>Bản nháp</p>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="table-card">
        <div class="table-header">
            <div class="table-title">
                <i class='bx bx-list-ul'></i>
                <span>Danh sách bài viết</span>
            </div>
            <a href="?page_layout=them_baiviet" class="btn btn-success">
                <i class='bx bx-plus'></i> Thêm bài viết
            </a>
        </div>

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width:50px">#</th>
                        <th>Bài viết</th>
                        <th>Danh mục</th>
                        <th>Tác giả</th>
                        <th>Trạng thái</th>
                        <th>Lượt xem</th>
                        <th>Ngày tạo</th>
                        <th style="width:140px">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($query) > 0): ?>
                        <?php $i = 1; while ($row = mysqli_fetch_assoc($query)): ?>
                        <tr>
                            <td><strong><?= $i++ ?></strong></td>
                            <td>
                                <div class="article-cell">
                                    <?php if (!empty($row['featured_image'])): ?>
                                        <img src="../../game2/uploads/<?= htmlspecialchars($row['featured_image']) ?>" class="article-thumb" alt="">
                                    <?php else: ?>
                                        <div class="article-thumb-placeholder"><i class='bx bx-image'></i></div>
                                    <?php endif; ?>
                                    <div class="article-info">
                                        <h4><?= htmlspecialchars($row['title']) ?></h4>
                                        <span>/<?= htmlspecialchars($row['slug']) ?></span>
                                    </div>
                                </div>
                            </td>
                            <td><?= $row['category_name'] ?: '<em style="color:var(--text-muted)">Chưa phân loại</em>' ?></td>
                            <td><?= $row['author_name'] ?: '<em style="color:var(--text-muted)">Ẩn danh</em>' ?></td>
                            <td>
                                <span class="status-badge <?= $row['status'] ?>">
                                    <i class='bx <?= $row['status'] == 'published' ? 'bx-check' : 'bx-time' ?>'></i>
                                    <?= $row['status'] == 'published' ? 'Đã xuất bản' : 'Nháp' ?>
                                </span>
                            </td>
                            <td><i class='bx bx-show' style="opacity:0.5"></i> <?= number_format($row['views']) ?></td>
                            <td><?= date('d/m/Y', strtotime($row['created_at'])) ?></td>
                            <td>
                                <div class="action-btns">
                                    <?php if ($row['status'] != 'published'): ?>
                                    <a href="?page_layout=duyet_baiviet&id=<?= $row['article_id'] ?>" 
                                       class="action-btn approve" title="Duyệt bài"
                                       onclick="return confirm('Duyệt bài viết này?');">
                                        <i class='bx bx-check'></i>
                                    </a>
                                    <?php endif; ?>
                                    <a href="?page_layout=sua_baiviet&id=<?= $row['article_id'] ?>" 
                                       class="action-btn edit" title="Sửa">
                                        <i class='bx bx-edit'></i>
                                    </a>
                                    <a href="?page_layout=xoa_baiviet&id=<?= $row['article_id'] ?>" 
                                       class="action-btn delete" title="Xóa"
                                       onclick="return confirm('Xóa bài viết này?');">
                                        <i class='bx bx-trash'></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8">
                                <div class="empty-state">
                                    <i class='bx bx-news'></i>
                                    <p>Chưa có bài viết nào</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
