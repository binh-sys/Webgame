<?php
require_once('ketnoi.php');

$sql = "SELECT * FROM users ORDER BY created_at DESC";
$query = mysqli_query($ketnoi, $sql);
$total = mysqli_num_rows($query);

// Đếm theo vai trò
$admin_count = mysqli_fetch_assoc(mysqli_query($ketnoi, "SELECT COUNT(*) as c FROM users WHERE role='admin'"))['c'];
$editor_count = mysqli_fetch_assoc(mysqli_query($ketnoi, "SELECT COUNT(*) as c FROM users WHERE role='editor'"))['c'];
$user_count = mysqli_fetch_assoc(mysqli_query($ketnoi, "SELECT COUNT(*) as c FROM users WHERE role='user'"))['c'];
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
.stat-icon.red { background: rgba(255, 71, 87, 0.15); color: var(--accent-red); }
.stat-icon.orange { background: rgba(255, 149, 0, 0.15); color: var(--accent-orange); }
.stat-icon.green { background: rgba(0, 255, 136, 0.15); color: var(--accent-green); }

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

/* User Cell */
.user-cell {
    display: flex;
    align-items: center;
    gap: 12px;
}

.user-avatar {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--primary), var(--accent-purple));
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-weight: 700;
    font-size: 16px;
    text-transform: uppercase;
}

.user-info h4 {
    margin: 0 0 2px;
    font-size: 14px;
    font-weight: 600;
}

.user-info span {
    font-size: 12px;
    color: var(--text-muted);
}

/* Role Badge */
.role-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}

.role-badge.admin {
    background: rgba(255, 71, 87, 0.15);
    color: var(--accent-red);
    border: 1px solid rgba(255, 71, 87, 0.3);
}

.role-badge.editor {
    background: rgba(255, 149, 0, 0.15);
    color: var(--accent-orange);
    border: 1px solid rgba(255, 149, 0, 0.3);
}

.role-badge.user {
    background: rgba(0, 212, 255, 0.15);
    color: var(--primary);
    border: 1px solid rgba(0, 212, 255, 0.3);
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
            <div class="stat-icon blue"><i class='bx bx-group'></i></div>
            <div class="stat-info">
                <h3><?= $total ?></h3>
                <p>Tổng người dùng</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon red"><i class='bx bx-shield'></i></div>
            <div class="stat-info">
                <h3><?= $admin_count ?></h3>
                <p>Quản trị viên</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon orange"><i class='bx bx-edit'></i></div>
            <div class="stat-info">
                <h3><?= $editor_count ?></h3>
                <p>Biên tập viên</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon green"><i class='bx bx-user'></i></div>
            <div class="stat-info">
                <h3><?= $user_count ?></h3>
                <p>Người dùng</p>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="table-card">
        <div class="table-header">
            <div class="table-title">
                <i class='bx bx-list-ul'></i>
                <span>Danh sách người dùng</span>
            </div>
            <a href="?page_layout=themus" class="btn btn-success">
                <i class='bx bx-plus'></i> Thêm người dùng
            </a>
        </div>

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width:60px">#</th>
                        <th>Người dùng</th>
                        <th>Email</th>
                        <th>Vai trò</th>
                        <th>Ngày tạo</th>
                        <th style="width:120px">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($total > 0): ?>
                        <?php $i = 1; while ($row = mysqli_fetch_assoc($query)): ?>
                        <tr>
                            <td><strong><?= $i++ ?></strong></td>
                            <td>
                                <div class="user-cell">
                                    <div class="user-avatar"><?= strtoupper(substr($row['username'], 0, 1)) ?></div>
                                    <div class="user-info">
                                        <h4><?= htmlspecialchars($row['display_name'] ?: $row['username']) ?></h4>
                                        <span>@<?= htmlspecialchars($row['username']) ?></span>
                                    </div>
                                </div>
                            </td>
                            <td style="color:var(--primary)"><?= htmlspecialchars($row['email']) ?></td>
                            <td>
                                <?php 
                                $role_icons = ['admin' => 'bx-shield', 'editor' => 'bx-edit', 'user' => 'bx-user'];
                                $role_names = ['admin' => 'Quản trị', 'editor' => 'Biên tập', 'user' => 'Người dùng'];
                                ?>
                                <span class="role-badge <?= $row['role'] ?>">
                                    <i class='bx <?= $role_icons[$row['role']] ?? 'bx-user' ?>'></i>
                                    <?= $role_names[$row['role']] ?? $row['role'] ?>
                                </span>
                            </td>
                            <td><?= date('d/m/Y', strtotime($row['created_at'])) ?></td>
                            <td>
                                <div class="action-btns">
                                    <a href="?page_layout=suaus&id=<?= $row['user_id'] ?>" 
                                       class="action-btn edit" title="Sửa">
                                        <i class='bx bx-edit'></i>
                                    </a>
                                    <a href="?page_layout=xoaus&id=<?= $row['user_id'] ?>" 
                                       class="action-btn delete" title="Xóa"
                                       onclick="return confirm('Xóa người dùng này?');">
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
                                    <i class='bx bx-user'></i>
                                    <p>Chưa có người dùng nào</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
