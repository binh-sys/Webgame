<?php
require_once('ketnoi.php');

$sql = "SELECT t.*, COUNT(at.article_id) as article_count 
        FROM tags t 
        LEFT JOIN article_tags at ON t.tag_id = at.tag_id 
        GROUP BY t.tag_id 
        ORDER BY t.tag_id DESC";
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
    background: rgba(0, 255, 136, 0.15);
    color: var(--accent-green);
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

.tag-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 14px;
    background: linear-gradient(135deg, rgba(0, 255, 136, 0.15), rgba(0, 212, 255, 0.1));
    border: 1px solid rgba(0, 255, 136, 0.3);
    border-radius: 20px;
    color: var(--accent-green);
    font-weight: 600;
}

.tag-badge i {
    font-size: 16px;
}

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
            <div class="stat-icon"><i class='bx bx-purchase-tag'></i></div>
            <div class="stat-info">
                <h3><?= $total ?></h3>
                <p>Tổng thẻ game</p>
            </div>
        </div>
    </div>

    <div class="table-card">
        <div class="table-header">
            <div class="table-title">
                <i class='bx bx-list-ul'></i>
                <span>Danh sách thẻ game</span>
            </div>
            <a href="?page_layout=themthegame" class="btn btn-success">
                <i class='bx bx-plus'></i> Thêm thẻ game
            </a>
        </div>

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width:60px">#</th>
                        <th>Tên thẻ</th>
                        <th>Slug</th>
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
                                <span class="tag-badge">
                                    <i class='bx bx-hash'></i>
                                    <?= htmlspecialchars($row['name']) ?>
                                </span>
                            </td>
                            <td><code style="color:var(--primary);background:rgba(0,212,255,0.1);padding:4px 8px;border-radius:4px;"><?= htmlspecialchars($row['slug']) ?></code></td>
                            <td>
                                <span class="count-badge">
                                    <i class='bx bx-news'></i> <?= $row['article_count'] ?>
                                </span>
                            </td>
                            <td>
                                <div class="action-btns">
                                    <a href="?page_layout=suathegame&id=<?= $row['tag_id'] ?>" class="action-btn edit" title="Sửa">
                                        <i class='bx bx-edit'></i>
                                    </a>
                                    <a href="?page_layout=xoathegame&id=<?= $row['tag_id'] ?>" class="action-btn delete" title="Xóa"
                                       onclick="return confirm('Xóa thẻ game này?');">
                                        <i class='bx bx-trash'></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5">
                                <div class="empty-state">
                                    <i class='bx bx-purchase-tag'></i>
                                    <p>Chưa có thẻ game nào</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
