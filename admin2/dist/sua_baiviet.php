<?php
// sua_baiviet.php - Giao diện sửa bài viết chuyên nghiệp
require_once('ketnoi.php');

if (!isset($_GET['id'])) {
    echo '<script>alert("Thiếu ID bài viết!"); window.location.href="index.php?page_layout=danhsachbaiviet";</script>';
    exit();
}

$id = intval($_GET['id']);
$sql = "SELECT * FROM articles WHERE article_id = $id";
$result = mysqli_query($ketnoi, $sql);

if (!$result || mysqli_num_rows($result) == 0) {
    echo '<script>alert("Không tìm thấy bài viết!"); window.location.href="index.php?page_layout=danhsachbaiviet";</script>';
    exit();
}

$article = mysqli_fetch_assoc($result);
$categories = mysqli_query($ketnoi, "SELECT * FROM categories ORDER BY name ASC");
$authors = mysqli_query($ketnoi, "SELECT user_id, display_name FROM users WHERE role IN ('editor', 'admin') ORDER BY display_name ASC");

$errors = [];

// Khi nhấn nút cập nhật
if (isset($_POST['update_article'])) {
    $title = trim($_POST['title'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    $excerpt = trim($_POST['excerpt'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $category_id = intval($_POST['category_id'] ?? 0);
    $author_id = intval($_POST['author_id'] ?? 0);
    $status = in_array($_POST['status'] ?? '', ['draft','published']) ? $_POST['status'] : 'draft';
    $featured_image = $article['featured_image'];

    if ($title === '') $errors[] = 'Tiêu đề không được để trống.';
    if ($slug === '') $errors[] = 'Slug không được để trống.';
    if ($category_id <= 0) $errors[] = 'Vui lòng chọn danh mục.';
    if ($author_id <= 0) $errors[] = 'Vui lòng chọn tác giả.';

    // Nếu có ảnh mới thì thay ảnh cũ
    if (!empty($_FILES['featured_image']['name'])) {
        $upload_dir = __DIR__ . '/../../game2/uploads/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $original_name = basename($_FILES['featured_image']['name']);
        $extension = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
        $filename_only = pathinfo($original_name, PATHINFO_FILENAME);
        $safe_name = preg_replace("/[^a-zA-Z0-9_-]/", "_", $filename_only);
        $target_file = $upload_dir . $safe_name . '.' . $extension;

        $counter = 1;
        while (file_exists($target_file)) {
            $target_file = $upload_dir . $safe_name . '-copy' . $counter . '.' . $extension;
            $counter++;
        }

        $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (in_array($extension, $allowed_types)) {
            if (move_uploaded_file($_FILES['featured_image']['tmp_name'], $target_file)) {
                // Xóa ảnh cũ nếu có
                $old_image_path = $upload_dir . $article['featured_image'];
                if (!empty($article['featured_image']) && file_exists($old_image_path)) {
                    unlink($old_image_path);
                }
                $featured_image = basename($target_file);
            } else {
                $errors[] = 'Lỗi khi upload ảnh!';
            }
        } else {
            $errors[] = 'Loại file không hợp lệ! Chỉ nhận jpg, png, gif, webp.';
        }
    }

    if (empty($errors)) {
        $stmt = mysqli_prepare($ketnoi, "UPDATE articles SET title=?, slug=?, excerpt=?, content=?, category_id=?, author_id=?, status=?, featured_image=? WHERE article_id=?");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 'ssssiiisi', $title, $slug, $excerpt, $content, $category_id, $author_id, $status, $featured_image, $id);
            if (mysqli_stmt_execute($stmt)) {
                echo '<script>alert("✅ Cập nhật bài viết thành công!"); window.location.href="index.php?page_layout=danhsachbaiviet";</script>';
                exit();
            } else {
                $errors[] = 'Lỗi khi cập nhật bài viết!';
            }
            mysqli_stmt_close($stmt);
        }
    }
    
    // Cập nhật lại article để hiển thị
    $article['title'] = $title;
    $article['slug'] = $slug;
    $article['excerpt'] = $excerpt;
    $article['content'] = $content;
    $article['category_id'] = $category_id;
    $article['author_id'] = $author_id;
    $article['status'] = $status;
}
?>

<link rel="stylesheet" href="assets/css/admin-forms.css">

<div class="admin-form-container">
    <div class="admin-form-card">
        <!-- Header -->
        <div class="admin-form-header">
            <div>
                <h2><i class='bx bx-edit'></i> Chỉnh sửa bài viết</h2>
                <div class="header-breadcrumb">
                    <a href="index.php">Trang chủ</a> / <a href="?page_layout=danhsachbaiviet">Bài viết</a> / Chỉnh sửa
                </div>
            </div>
            <div class="header-actions">
                <a href="../../game2/gamebat/article.php?id=<?= $id ?>" target="_blank" class="btn btn-ghost">
                    <i class='bx bx-link-external'></i> Xem bài viết
                </a>
                <a href="?page_layout=danhsachbaiviet" class="btn btn-ghost">
                    <i class='bx bx-arrow-back'></i> Quay lại
                </a>
            </div>
        </div>

        <!-- Body -->
        <div class="admin-form-body">
            <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <i class='bx bx-error-circle'></i>
                <div class="alert-content">
                    <div class="alert-title">Có lỗi xảy ra!</div>
                    <ul style="margin:0;padding-left:18px;">
                        <?php foreach ($errors as $err): ?>
                            <li><?= htmlspecialchars($err) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
            <?php endif; ?>

            <form id="articleForm" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="update_article" value="1">
                
                <div class="form-grid">
                    <!-- Main Content -->
                    <div class="form-main">
                        <!-- Thông tin cơ bản -->
                        <div class="form-section">
                            <div class="form-section-title">
                                <i class='bx bx-edit'></i> Thông tin bài viết
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label required">Tiêu đề</label>
                                <input type="text" name="title" id="title" class="form-input" 
                                       placeholder="Nhập tiêu đề bài viết..." 
                                       value="<?= htmlspecialchars($article['title']) ?>" required>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label required">Slug (URL)</label>
                                    <input type="text" name="slug" id="slug" class="form-input" 
                                           placeholder="vd: tin-tuc-game-moi" 
                                           value="<?= htmlspecialchars($article['slug']) ?>" required>
                                </div>
                                <div class="form-group">
                                    <label class="form-label required">Danh mục</label>
                                    <select name="category_id" id="category_id" class="form-select" required>
                                        <option value="">-- Chọn danh mục --</option>
                                        <?php while ($cat = mysqli_fetch_assoc($categories)): ?>
                                            <option value="<?= $cat['category_id'] ?>" <?= ($cat['category_id'] == $article['category_id']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($cat['name']) ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Tóm tắt</label>
                                <textarea name="excerpt" id="excerpt" class="form-textarea" rows="3" 
                                          placeholder="Mô tả ngắn gọn về nội dung bài viết..."><?= htmlspecialchars($article['excerpt']) ?></textarea>
                                <div class="char-counter"><span id="excerptCount"><?= strlen($article['excerpt']) ?></span>/200 ký tự</div>
                            </div>
                        </div>

                        <!-- Nội dung -->
                        <div class="form-section">
                            <div class="form-section-title">
                                <i class='bx bx-text'></i> Nội dung bài viết
                            </div>
                            <div class="form-group">
                                <textarea name="content" id="editor" class="form-textarea content-editor"><?= htmlspecialchars($article['content']) ?></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="form-sidebar">
                        <!-- Trạng thái hiện tại -->
                        <div class="form-section">
                            <div class="form-section-title">
                                <i class='bx bx-info-circle'></i> Thông tin
                            </div>
                            
                            <div class="status-indicator <?= $article['status'] ?>">
                                <i class='bx <?= $article['status'] == 'published' ? 'bx-check-circle' : 'bx-time' ?>'></i>
                                <span><?= $article['status'] == 'published' ? 'Đã xuất bản' : 'Bản nháp' ?></span>
                            </div>

                            <div style="font-size:13px;color:var(--text-muted);margin-bottom:8px;">
                                <i class='bx bx-calendar'></i> Ngày tạo: <?= date('d/m/Y H:i', strtotime($article['created_at'])) ?>
                            </div>
                            <div style="font-size:13px;color:var(--text-muted);">
                                <i class='bx bx-show'></i> Lượt xem: <?= number_format($article['views']) ?>
                            </div>
                        </div>

                        <!-- Xuất bản -->
                        <div class="form-section">
                            <div class="form-section-title">
                                <i class='bx bx-send'></i> Xuất bản
                            </div>

                            <div class="form-group">
                                <label class="form-label required">Tác giả</label>
                                <select name="author_id" id="author_id" class="form-select" required>
                                    <option value="">-- Chọn tác giả --</option>
                                    <?php while ($au = mysqli_fetch_assoc($authors)): ?>
                                        <option value="<?= $au['user_id'] ?>" <?= ($au['user_id'] == $article['author_id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($au['display_name']) ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Trạng thái</label>
                                <select name="status" id="status" class="form-select">
                                    <option value="draft" <?= ($article['status'] == 'draft') ? 'selected' : '' ?>>📝 Bản nháp</option>
                                    <option value="published" <?= ($article['status'] == 'published') ? 'selected' : '' ?>>✅ Xuất bản</option>
                                </select>
                            </div>

                            <div class="form-divider"></div>

                            <div class="btn-group btn-group-vertical">
                                <button type="button" class="btn btn-primary btn-lg" onclick="confirmSubmit()">
                                    <i class='bx bx-save'></i> Lưu thay đổi
                                </button>
                                <a href="?page_layout=xoa_baiviet&id=<?= $id ?>" class="btn btn-danger" 
                                   onclick="return confirm('Bạn có chắc chắn muốn xóa bài viết này?');">
                                    <i class='bx bx-trash'></i> Xóa bài viết
                                </a>
                            </div>
                        </div>

                        <!-- Ảnh đại diện -->
                        <div class="form-section">
                            <div class="form-section-title">
                                <i class='bx bx-image'></i> Ảnh đại diện
                            </div>

                            <div class="image-preview" id="currentImage">
                                <?php if (!empty($article['featured_image'])): ?>
                                    <img src="../../game2/uploads/<?= htmlspecialchars($article['featured_image']) ?>" alt="Featured Image">
                                <?php else: ?>
                                    <div class="image-preview-placeholder">
                                        <i class='bx bx-image-alt'></i>
                                        <span>Chưa có ảnh</span>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="file-upload-wrapper" style="margin-top:12px;">
                                <div class="file-upload-area" id="dropZone">
                                    <i class='bx bx-cloud-upload'></i>
                                    <div class="upload-text">Thay đổi ảnh đại diện</div>
                                    <div class="upload-hint">JPG, PNG, WebP, GIF (Tối đa 5MB)</div>
                                    <input type="file" name="featured_image" id="featured_image" accept="image/*">
                                </div>
                            </div>

                            <div class="form-helper">
                                <i class='bx bx-info-circle'></i> Để trống nếu không muốn thay đổi
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Confirm Modal -->
<div class="modal-overlay" id="confirmModal">
    <div class="modal-content">
        <div class="modal-icon info"><i class='bx bx-save'></i></div>
        <div class="modal-title">Xác nhận cập nhật?</div>
        <div class="modal-message">Các thay đổi sẽ được lưu vào hệ thống.</div>
        <div class="modal-actions">
            <button class="btn btn-primary" onclick="submitForm()">
                <i class='bx bx-check'></i> Đồng ý
            </button>
            <button class="btn btn-secondary" onclick="closeModal()">
                <i class='bx bx-x'></i> Hủy
            </button>
        </div>
    </div>
</div>

<!-- TinyMCE -->
<script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/7/tinymce.min.js" referrerpolicy="origin"></script>
<script>
// TinyMCE Init
if (typeof tinymce !== 'undefined') {
    tinymce.init({
        selector: '#editor',
        height: 400,
        plugins: 'code image link lists media table fullscreen autolink advlist',
        toolbar: 'undo redo | bold italic underline | styleselect | alignleft aligncenter alignright | bullist numlist | link image | fullscreen code',
        menubar: false,
        branding: false,
        content_style: 'body { font-family: "Segoe UI", sans-serif; font-size: 15px; color: #333; }'
    });
}

// Character counter
document.getElementById('excerpt').addEventListener('input', function() {
    const count = this.value.length;
    const counter = document.getElementById('excerptCount');
    counter.textContent = count;
    counter.parentElement.className = 'char-counter' + (count > 200 ? ' danger' : count > 150 ? ' warning' : '');
});

// Image preview
const fileInput = document.getElementById('featured_image');
const dropZone = document.getElementById('dropZone');
const currentImage = document.getElementById('currentImage');

fileInput.addEventListener('change', handleFile);

dropZone.addEventListener('dragover', (e) => {
    e.preventDefault();
    dropZone.classList.add('dragover');
});

dropZone.addEventListener('dragleave', () => {
    dropZone.classList.remove('dragover');
});

dropZone.addEventListener('drop', (e) => {
    e.preventDefault();
    dropZone.classList.remove('dragover');
    if (e.dataTransfer.files.length) {
        fileInput.files = e.dataTransfer.files;
        handleFile();
    }
});

function handleFile() {
    const file = fileInput.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = (e) => {
            currentImage.innerHTML = `<img src="${e.target.result}" alt="Preview">`;
        };
        reader.readAsDataURL(file);
    }
}

// Modal functions
function confirmSubmit() {
    const title = document.getElementById('title').value.trim();
    const category = document.getElementById('category_id').value;
    const author = document.getElementById('author_id').value;
    
    if (!title) { alert('Vui lòng nhập tiêu đề!'); return; }
    if (!category) { alert('Vui lòng chọn danh mục!'); return; }
    if (!author) { alert('Vui lòng chọn tác giả!'); return; }
    
    document.getElementById('confirmModal').classList.add('active');
}

function closeModal() {
    document.getElementById('confirmModal').classList.remove('active');
}

function submitForm() {
    if (typeof tinymce !== 'undefined') tinymce.triggerSave();
    document.getElementById('articleForm').submit();
}

document.getElementById('confirmModal').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});
</script>
