<?php
require_once('ketnoi.php');

// Lấy danh sách tác giả (chỉ editor/biên tập viên)
$sql = "SELECT u.*, COUNT(a.article_id) as article_count 
        FROM users u 
        LEFT JOIN articles a ON u.user_id = a.author_id 
        WHERE u.role = 'editor'
        GROUP BY u.user_id 
        ORDER BY article_count DESC";
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

.author-cell {
    display: flex;
    align-items: center;
    gap: 12px;
}

.author-avatar {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--accent-purple), var(--primary));
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-weight: 700;
    font-size: 16px;
}

.author-info h4 {
    margin: 0 0 2px;
    font-size: 14px;
    font-weight: 600;
}

.author-info span {
    font-size: 12px;
    color: var(--text-muted);
}

.role-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}

.role-badge.editor {
    background: rgba(168, 85, 247, 0.15);
    color: var(--accent-purple);
    border: 1px solid rgba(168, 85, 247, 0.3);
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
            <div class="stat-icon"><i class='bx bx-user-pin'></i></div>
            <div class="stat-info">
                <h3><?= $total ?></h3>
                <p>Tổng tác giả</p>
            </div>
        </div>
    </div>

    <div class="table-card">
        <div class="table-header">
            <div class="table-title">
                <i class='bx bx-id-card'></i>
                <span>Danh sách tác giả</span>
            </div>
            <a href="?page_layout=themtacgia" class="btn btn-success">
                <i class='bx bx-plus'></i> Thêm tác giả
            </a>
        </div>

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width:60px">#</th>
                        <th>Tác giả</th>
                        <th>Email</th>
                        <th>Vai trò</th>
                        <th>Số bài viết</th>
                        <th>Ngày tham gia</th>
                        <th style="width:120px">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($total > 0): ?>
                        <?php $i = 1; while ($row = mysqli_fetch_assoc($query)): ?>
                        <tr>
                            <td><strong><?= $i++ ?></strong></td>
                            <td>
                                <div class="author-cell">
                                    <div class="author-avatar"><?= strtoupper(substr($row['username'], 0, 1)) ?></div>
                                    <div class="author-info">
                                        <h4><?= htmlspecialchars($row['display_name'] ?: $row['username']) ?></h4>
                                        <span>@<?= htmlspecialchars($row['username']) ?></span>
                                    </div>
                                </div>
                            </td>
                            <td style="color:var(--primary)"><?= htmlspecialchars($row['email']) ?></td>
                            <td>
                                <span class="role-badge editor">
                                    <i class='bx bx-edit'></i> Biên tập viên
                                </span>
                            </td>
                            <td>
                                <span class="count-badge">
                                    <i class='bx bx-news'></i> <?= $row['article_count'] ?>
                                </span>
                            </td>
                            <td><?= date('d/m/Y', strtotime($row['created_at'])) ?></td>
                            <td>
                                <div class="action-btns">
                                    <a href="?page_layout=suatacgia&id=<?= $row['user_id'] ?>" class="action-btn edit" title="Sửa">
                                        <i class='bx bx-edit'></i>
                                    </a>
                                    <a href="?page_layout=xoatacgia&id=<?= $row['user_id'] ?>" class="action-btn delete" title="Xóa"
                                       onclick="return confirm('Xóa tác giả này?');">
                                        <i class='bx bx-trash'></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7">
                                <div class="empty-state">
                                    <i class='bx bx-user-pin'></i>
                                    <p>Chưa có tác giả nào</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
