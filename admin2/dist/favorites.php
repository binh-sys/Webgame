<?php
require_once('ketnoi.php');

$sql = "SELECT f.*, u.display_name, u.username, a.title as article_title 
        FROM favorites f 
        LEFT JOIN users u ON f.user_id = u.user_id 
        LEFT JOIN articles a ON f.article_id = a.article_id 
        ORDER BY f.created_at DESC";
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
    background: rgba(255, 71, 107, 0.15);
    color: #ff476b;
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

.user-cell {
    display: flex;
    align-items: center;
    gap: 10px;
}

.user-avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: linear-gradient(135deg, #ff476b, #ff8fa3);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-weight: 700;
    font-size: 14px;
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
            <div class="stat-icon"><i class='bx bx-heart'></i></div>
            <div class="stat-info">
                <h3><?= $total ?></h3>
                <p>Tổng lượt yêu thích</p>
            </div>
        </div>
    </div>

    <div class="table-card">
        <div class="table-header">
            <div class="table-title">
                <i class='bx bx-heart'></i>
                <span>Danh sách yêu thích</span>
            </div>
        </div>

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width:60px">#</th>
                        <th>Người dùng</th>
                        <th>Bài viết</th>
                        <th>Ngày yêu thích</th>
                        <th style="width:80px">Xóa</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($total > 0): ?>
                        <?php $i = 1; while ($row = mysqli_fetch_assoc($query)): ?>
                        <tr>
                            <td><strong><?= $i++ ?></strong></td>
                            <td>
                                <div class="user-cell">
                                    <div class="user-avatar"><?= strtoupper(substr($row['username'] ?? 'U', 0, 1)) ?></div>
                                    <div>
                                        <div style="font-weight:600"><?= htmlspecialchars($row['display_name'] ?? $row['username'] ?? 'N/A') ?></div>
                                        <div style="font-size:12px;color:var(--text-muted)">@<?= htmlspecialchars($row['username'] ?? '') ?></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <a href="../../game2/gamebat/article.php?id=<?= $row['article_id'] ?>" target="_blank" class="article-link">
                                    <?= htmlspecialchars($row['article_title'] ?? 'Bài viết đã xóa') ?>
                                </a>
                            </td>
                            <td><?= date('d/m/Y H:i', strtotime($row['created_at'])) ?></td>
                            <td>
                                <a href="?page_layout=xoayeuthich&id=<?= $row['favorite_id'] ?>" class="action-btn delete" title="Xóa"
                                   onclick="return confirm('Xóa lượt yêu thích này?');">
                                    <i class='bx bx-trash'></i>
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5">
                                <div class="empty-state">
                                    <i class='bx bx-heart'></i>
                                    <p>Chưa có lượt yêu thích nào</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
