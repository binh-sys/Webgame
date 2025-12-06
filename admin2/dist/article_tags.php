<?php
require_once('ketnoi.php');

$sql = "SELECT at.*, a.title as article_title, t.name as tag_name 
        FROM article_tags at 
        LEFT JOIN articles a ON at.article_id = a.article_id 
        LEFT JOIN tags t ON at.tag_id = t.tag_id 
        ORDER BY at.article_id DESC, at.tag_id DESC";
$query = mysqli_query($ketnoi, $sql);
$total = mysqli_num_rows($query);
?>

<link rel="stylesheet" href="assets/css/admin-forms.css">

<style>
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
    background: rgba(0, 212, 255, 0.15);
    color: var(--primary);
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

.article-link {
    color: var(--primary);
    text-decoration: none;
    max-width: 300px;
    display: block;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.article-link:hover {
    text-decoration: underline;
}

.tag-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    background: linear-gradient(135deg, rgba(0, 255, 136, 0.15), rgba(0, 212, 255, 0.1));
    border: 1px solid rgba(0, 255, 136, 0.3);
    border-radius: 20px;
    color: var(--accent-green);
    font-weight: 600;
    font-size: 13px;
}

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
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon"><i class='bx bx-link'></i></div>
            <div class="stat-info">
                <h3><?= $total ?></h3>
                <p>Tổng liên kết thẻ</p>
            </div>
        </div>
    </div>

    <div class="table-card">
        <div class="table-header">
            <div class="table-title">
                <i class='bx bx-link-alt'></i>
                <span>Liên kết bài viết - Thẻ game</span>
            </div>
            <a href="?page_layout=themlienthethegame" class="btn btn-success">
                <i class='bx bx-plus'></i> Thêm liên kết
            </a>
        </div>

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width:60px">#</th>
                        <th>Bài viết</th>
                        <th>Thẻ game</th>
                        <th style="width:120px">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($total > 0): ?>
                        <?php $i = 1; while ($row = mysqli_fetch_assoc($query)): ?>
                        <tr>
                            <td><strong><?= $i++ ?></strong></td>
                            <td>
                                <a href="../../game2/gamebat/article.php?id=<?= $row['article_id'] ?>" target="_blank" class="article-link">
                                    <?= htmlspecialchars($row['article_title'] ?? 'Bài viết đã xóa') ?>
                                </a>
                            </td>
                            <td>
                                <span class="tag-badge">
                                    <i class='bx bx-hash'></i>
                                    <?= htmlspecialchars($row['tag_name'] ?? 'Thẻ đã xóa') ?>
                                </span>
                            </td>
                            <td>
                                <div class="action-btns">
                                    <a href="?page_layout=sualienthethegame&article_id=<?= $row['article_id'] ?>&tag_id=<?= $row['tag_id'] ?>" class="action-btn edit" title="Sửa">
                                        <i class='bx bx-edit'></i>
                                    </a>
                                    <a href="?page_layout=xoalienthethegame&article_id=<?= $row['article_id'] ?>&tag_id=<?= $row['tag_id'] ?>" class="action-btn delete" title="Xóa"
                                       onclick="return confirm('Xóa liên kết này?');">
                                        <i class='bx bx-trash'></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4">
                                <div class="empty-state">
                                    <i class='bx bx-link'></i>
                                    <p>Chưa có liên kết nào</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
