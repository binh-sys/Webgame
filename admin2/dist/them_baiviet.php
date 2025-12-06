<?php
// them_baiviet.php - Giao diện thêm bài viết chuyên nghiệp
require_once('ketnoi.php');

// Lấy danh mục và tác giả
$categories = mysqli_query($ketnoi, "SELECT * FROM categories ORDER BY name ASC");
$authors = mysqli_query($ketnoi, "SELECT user_id, display_name FROM users WHERE role IN ('editor', 'admin') ORDER BY display_name ASC");

$errors = [];
$success = false;

// Xử lý khi form được submit
if (isset($_POST['add_article'])) {
    $title = trim($_POST['title'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    $excerpt = trim($_POST['excerpt'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $category_id = intval($_POST['category_id'] ?? 0);
    $author_id = intval($_POST['author_id'] ?? 0);
    $status = in_array($_POST['status'] ?? '', ['draft','published']) ? $_POST['status'] : 'draft';
    $views = 0;
    $created_at = date('Y-m-d H:i:s');

    if ($title === '') $errors[] = 'Tiêu đề không được để trống.';
    if ($slug === '') $errors[] = 'Slug không được để trống.';
    if ($category_id <= 0) $errors[] = 'Vui lòng chọn danh mục.';
    if ($author_id <= 0) $errors[] = 'Vui lòng chọn tác giả.';

    // Xử lý ảnh đại diện
    $featured_image = '';
    if (!empty($_FILES['featured_image']['name'])) {
        $upload_dir = __DIR__ . '/../../game2/uploads/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $raw_name = basename($_FILES['featured_image']['name']);
        $raw_name = preg_replace('/[^a-zA-Z0-9\.\-_]/', '', $raw_name);
        $ext = strtolower(pathinfo($raw_name, PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','webp','gif'];

        if (!in_array($ext, $allowed)) {
            $errors[] = 'Định dạng ảnh không hợp lệ. Cho phép: jpg, jpeg, png, webp, gif.';
        } else {
            $file_name = $raw_name;
            $target_path = $upload_dir . $file_name;

            $i = 1;
            while (file_exists($target_path)) {
                $file_name = pathinfo($raw_name, PATHINFO_FILENAME) . "-copy{$i}." . $ext;
                $target_path = $upload_dir . $file_name;
                $i++;
            }

            if (move_uploaded_file($_FILES['featured_image']['tmp_name'], $target_path)) {
                $featured_image = $file_name;
            } else {
                $errors[] = 'Không thể upload ảnh. Vui lòng thử lại.';
            }
        }
    }

    if (empty($errors)) {
        $stmt = mysqli_prepare($ketnoi, "INSERT INTO articles (title, slug, excerpt, content, category_id, author_id, status, views, featured_image, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 'ssssiiisss', $title, $slug, $excerpt, $content, $category_id, $author_id, $status, $views, $featured_image, $created_at);
            if (mysqli_stmt_execute($stmt)) {
                echo '<script>alert("✅ Thêm bài viết thành công!"); window.location.href="index.php?page_layout=danhsachbaiviet";</script>';
                exit;
            } else {
                $errors[] = 'Lỗi khi lưu vào cơ sở dữ liệu.';
            }
            mysqli_stmt_close($stmt);
        } else {
            $errors[] = 'Chuẩn bị truy vấn thất bại.';
        }
    }
}
?>

<link rel="stylesheet" href="assets/css/admin-forms.css">

<div class="admin-form-container">
    <div class="admin-form-card">
        <!-- Header -->
        <div class="admin-form-header">
            <div>
                <h2><i class='bx bx-plus-circle'></i> Thêm bài viết mới</h2>
                <div class="header-breadcrumb">
                    <a href="index.php">Trang chủ</a> / <a href="?page_layout=danhsachbaiviet">Bài viết</a> / Thêm mới
                </div>
            </div>
            <div class="header-actions">
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
                <input type="hidden" name="add_article" value="1">
                
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
                                       value="<?= isset($title) ? htmlspecialchars($title) : '' ?>" required>
                                <div class="form-helper">
                                    <i class='bx bx-info-circle'></i> Tiêu đề nên ngắn gọn, hấp dẫn (50-70 ký tự)
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label required">Slug (URL)</label>
                                    <input type="text" name="slug" id="slug" class="form-input" 
                                           placeholder="vd: tin-tuc-game-moi" 
                                           value="<?= isset($slug) ? htmlspecialchars($slug) : '' ?>" required>
                                </div>
                                <div class="form-group">
                                    <label class="form-label required">Danh mục</label>
                                    <select name="category_id" id="category_id" class="form-select" required>
                                        <option value="">-- Chọn danh mục --</option>
                                        <?php mysqli_data_seek($categories, 0); while ($cat = mysqli_fetch_assoc($categories)): ?>
                                            <option value="<?= $cat['category_id'] ?>" <?= (isset($category_id) && $category_id == $cat['category_id']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($cat['name']) ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Tóm tắt</label>
                                <textarea name="excerpt" id="excerpt" class="form-textarea" rows="3" 
                                          placeholder="Mô tả ngắn gọn về nội dung bài viết..."><?= isset($excerpt) ? htmlspecialchars($excerpt) : '' ?></textarea>
                                <div class="char-counter"><span id="excerptCount">0</span>/200 ký tự</div>
                            </div>
                        </div>

                        <!-- Nội dung -->
                        <div class="form-section">
                            <div class="form-section-title">
                                <i class='bx bx-text'></i> Nội dung bài viết
                            </div>
                            <div class="form-group">
                                <textarea name="content" id="editor" class="form-textarea content-editor" 
                                          placeholder="Nhập nội dung chi tiết..."><?= isset($content) ? htmlspecialchars($content) : '' ?></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="form-sidebar">
                        <!-- Xuất bản -->
                        <div class="form-section">
                            <div class="form-section-title">
                                <i class='bx bx-send'></i> Xuất bản
                            </div>

                            <div class="form-group">
                                <label class="form-label required">Tác giả</label>
                                <select name="author_id" id="author_id" class="form-select" required>
                                    <option value="">-- Chọn tác giả --</option>
                                    <?php mysqli_data_seek($authors, 0); while ($au = mysqli_fetch_assoc($authors)): ?>
                                        <option value="<?= $au['user_id'] ?>" <?= (isset($author_id) && $author_id == $au['user_id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($au['display_name']) ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Trạng thái</label>
                                <select name="status" id="status" class="form-select">
                                    <option value="draft" <?= (isset($status) && $status == 'draft') ? 'selected' : '' ?>>📝 Bản nháp</option>
                                    <option value="published" <?= (isset($status) && $status == 'published') ? 'selected' : '' ?>>✅ Xuất bản ngay</option>
                                </select>
                            </div>

                            <div class="form-divider"></div>

                            <div class="btn-group btn-group-vertical">
                                <button type="button" class="btn btn-success btn-lg" onclick="confirmSubmit()">
                                    <i class='bx bx-check'></i> Lưu bài viết
                                </button>
                                <button type="reset" class="btn btn-secondary" onclick="resetForm()">
                                    <i class='bx bx-reset'></i> Đặt lại
                                </button>
                            </div>
                        </div>

                        <!-- Ảnh đại diện -->
                        <div class="form-section">
                            <div class="form-section-title">
                                <i class='bx bx-image'></i> Ảnh đại diện
                            </div>

                            <div class="file-upload-wrapper">
                                <div class="file-upload-area" id="dropZone">
                                    <i class='bx bx-cloud-upload'></i>
                                    <div class="upload-text">Kéo thả hoặc click để chọn ảnh</div>
                                    <div class="upload-hint">JPG, PNG, WebP, GIF (Tối đa 5MB)</div>
                                    <input type="file" name="featured_image" id="featured_image" accept="image/*">
                                </div>
                            </div>

                            <div class="image-preview" id="imagePreview">
                                <div class="image-preview-placeholder">
                                    <i class='bx bx-image-alt'></i>
                                    <span>Chưa có ảnh</span>
                                </div>
                            </div>

                            <div class="form-helper">
                                <i class='bx bx-info-circle'></i> Kích thước đề xuất: 1200x630px
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
        <div class="modal-title">Xác nhận lưu bài viết?</div>
        <div class="modal-message">Bài viết sẽ được lưu vào hệ thống. Bạn có thể chỉnh sửa sau.</div>
        <div class="modal-actions">
            <button class="btn btn-success" onclick="submitForm()">
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

// Auto generate slug
document.getElementById('title').addEventListener('input', function() {
    const slug = this.value
        .toLowerCase()
        .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
        .replace(/đ/g, 'd').replace(/Đ/g, 'd')
        .replace(/[^a-z0-9\s-]/g, '')
        .replace(/\s+/g, '-')
        .replace(/-+/g, '-')
        .trim();
    document.getElementById('slug').value = slug;
});

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
const preview = document.getElementById('imagePreview');

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
            preview.innerHTML = `<img src="${e.target.result}" alt="Preview">`;
        };
        reader.readAsDataURL(file);
    }
}

function resetForm() {
    document.getElementById('articleForm').reset();
    preview.innerHTML = '<div class="image-preview-placeholder"><i class="bx bx-image-alt"></i><span>Chưa có ảnh</span></div>';
    if (typeof tinymce !== 'undefined') tinymce.get('editor').setContent('');
}

// Modal functions
function confirmSubmit() {
    // Validate
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

// Close modal on outside click
document.getElementById('confirmModal').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});
</script>
