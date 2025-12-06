<?php
require_once('ketnoi.php');

$sql = "SELECT c.*, COUNT(a.article_id) as article_count 
        FROM categories c 
        LEFT JOIN articles a ON c.category_id = a.category_id 
        GROUP BY c.category_id 
        ORDER BY c.category_id DESC";
$query = mysqli_query($ketnoi, $sql);
$total = mysqli_num_rows($query);
?>

<link rel="stylesheet" href="assets/css/admin-forms.css">

<style>
/* Stats Cards */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
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
    background: rgba(168, 85, 247, 0.15);
    color: var(--accent-purple);
}

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

/* Category Name */
.category-name {
    display: flex;
    align-items: center;
    gap: 12px;
}

.category-icon {
    width: 40px;
    height: 40px;
    border-radius: var(--radius-sm);
    background: linear-gradient(135deg, rgba(168, 85, 247, 0.2), rgba(0, 212, 255, 0.1));
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--accent-purple);
    font-size: 18px;
}

.category-info h4 {
    margin: 0 0 2px;
    font-size: 14px;
    font-weight: 600;
}

.category-info span {
    font-size: 12px;
    color: var(--text-muted);
}

/* Article Count Badge */
.count-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 600;
    background: rgba(0, 212, 255, 0.1);
    color: var(--primary);
    border: 1px solid rgba(0, 212, 255, 0.2);
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

/* Description */
.description-cell {
    max-width: 300px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    color: var(--text-secondary);
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
</style>

<div class="admin-form-container">
    <!-- Stats -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon"><i class='bx bx-folder'></i></div>
            <div class="stat-info">
                <h3><?= $total ?></h3>
                <p>Tổng chuyên mục</p>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="table-card">
        <div class="table-header">
            <div class="table-title">
                <i class='bx bx-list-ul'></i>
                <span>Danh sách chuyên mục</span>
            </div>
            <a href="?page_layout=themchuyenmuc" class="btn btn-success">
                <i class='bx bx-plus'></i> Thêm chuyên mục
            </a>
        </div>

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width:60px">#</th>
                        <th>Chuyên mục</th>
                        <th>Slug</th>
                        <th>Mô tả</th>
                        <th>Số bài viết</th>
                        <th style="width:120px">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($total > 0): ?>
                        <?php $i = 1; while ($row = mysqli_fetch_assoc($query)): ?>
                        <tr>
                            <td><strong><?= $i++ ?></strong></td>
                            <td>
                                <div class="category-name">
                                    <div class="category-icon"><i class='bx bx-folder'></i></div>
                                    <div class="category-info">
                                        <h4><?= htmlspecialchars($row['name']) ?></h4>
                                    </div>
                                </div>
                            </td>
                            <td><code style="color:var(--primary);background:rgba(0,212,255,0.1);padding:4px 8px;border-radius:4px;"><?= htmlspecialchars($row['slug']) ?></code></td>
                            <td class="description-cell"><?= htmlspecialchars($row['description']) ?: '<em style="color:var(--text-muted)">Chưa có mô tả</em>' ?></td>
                            <td>
                                <span class="count-badge">
                                    <i class='bx bx-news'></i> <?= $row['article_count'] ?>
                                </span>
                            </td>
                            <td>
                                <div class="action-btns">
                                    <a href="?page_layout=suachuyenmuc&id=<?= $row['category_id'] ?>" 
                                       class="action-btn edit" title="Sửa">
                                        <i class='bx bx-edit'></i>
                                    </a>
                                    <a href="?page_layout=xoachuyenmuc&id=<?= $row['category_id'] ?>" 
                                       class="action-btn delete" title="Xóa"
                                       onclick="return confirm('Xóa chuyên mục này? Các bài viết thuộc chuyên mục sẽ không bị xóa.');">
                                        <i class='bx bx-trash'></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6">
                                <div class="empty-state">
                                    <i class='bx bx-folder'></i>
                                    <p>Chưa có chuyên mục nào</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
