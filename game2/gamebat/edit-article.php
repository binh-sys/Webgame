<?php
session_start();
require_once 'ketnoi.php';

// Kiểm tra đăng nhập và quyền
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['editor', 'admin'])) {
    header("Location: login.php");
    exit;
}

$user_id = intval($_SESSION['user_id']);
$user_role = $_SESSION['role'];

// Kiểm tra có ID bài viết không
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: author-history.php?error=invalid");
    exit;
}

$article_id = intval($_GET['id']);

// Lấy thông tin bài viết
$sql = "SELECT * FROM articles WHERE article_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $article_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: author-history.php?error=notfound");
    exit;
}

$article = $result->fetch_assoc();

// Kiểm tra quyền: chỉ tác giả hoặc admin mới được sửa
if ($user_role !== 'admin' && $article['author_id'] !== $user_id) {
    header("Location: author-history.php?error=permission");
    exit;
}

// Lấy danh mục
$categories = mysqli_query($conn, "SELECT * FROM categories ORDER BY name ASC");

// Xử lý cập nhật
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_article'])) {
    $title = mysqli_real_escape_string($conn, trim($_POST['title']));
    $content = mysqli_real_escape_string($conn, $_POST['content']);
    $category_id = intval($_POST['category_id']);
    
    if (empty($title) || empty($content) || $category_id <= 0) {
        $error = 'Vui lòng điền đầy đủ thông tin!';
    } else {
        // Xử lý upload ảnh mới nếu có
        $featured_image = $article['featured_image'];
        
        if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === 0) {
            $ext = strtolower(pathinfo($_FILES['thumbnail']['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            
            if (in_array($ext, $allowed)) {
                // Xóa ảnh cũ
                if (!empty($article['featured_image'])) {
                    $old_path = __DIR__ . '/../' . $article['featured_image'];
                    if (file_exists($old_path)) unlink($old_path);
                }
                
                // Upload ảnh mới
                $upload_dir = __DIR__ . '/../uploads/';
                if (!file_exists($upload_dir)) mkdir($upload_dir, 0777, true);
                
                $new_name = uniqid() . '.' . $ext;
                $featured_image = 'uploads/' . $new_name;
                move_uploaded_file($_FILES['thumbnail']['tmp_name'], $upload_dir . $new_name);
            }
        }
        
        // Cập nhật bài viết - đặt lại status về pending nếu editor sửa
        $new_status = ($user_role === 'admin') ? $article['status'] : 'pending';
        
        // Tạo excerpt tự động từ content
        $excerpt = mysqli_real_escape_string($conn, strip_tags(substr($_POST['content'], 0, 200)));
        
        $update_sql = "UPDATE articles SET title = ?, excerpt = ?, content = ?, category_id = ?, featured_image = ?, status = ? WHERE article_id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param('sssissi', $title, $excerpt, $content, $category_id, $featured_image, $new_status, $article_id);
        
        if ($update_stmt->execute()) {
            header("Location: author-history.php?success=updated");
            exit;
        } else {
            $error = 'Lỗi khi cập nhật bài viết!';
        }
    }
}
?>
<?php include 'header.php'; ?>

<style>
    .editor-page {
        min-height: calc(100vh - 200px);
        background: linear-gradient(135deg, #0a0a0a 0%, #1a1a2e 50%, #16213e 100%);
        padding: 40px 0;
        position: relative;
    }

    .editor-page::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background:
            radial-gradient(circle at 10% 20%, rgba(23, 162, 184, 0.08) 0%, transparent 40%),
            radial-gradient(circle at 90% 80%, rgba(255, 179, 0, 0.06) 0%, transparent 40%);
        pointer-events: none;
    }

    .editor-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 0 20px;
        position: relative;
        z-index: 10;
    }

    .editor-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 30px;
        flex-wrap: wrap;
        gap: 20px;
    }

    .editor-title {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .editor-title-icon {
        width: 60px;
        height: 60px;
        background: linear-gradient(135deg, #17a2b8, #20c997);
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 10px 30px rgba(23, 162, 184, 0.3);
    }

    .editor-title-icon i {
        font-size: 28px;
        color: #fff;
    }

    .editor-title h1 {
        color: #fff;
        font-size: 28px;
        font-weight: 700;
        margin: 0;
    }

    .editor-title p {
        color: #888;
        font-size: 14px;
        margin: 5px 0 0;
    }

    .editor-actions {
        display: flex;
        gap: 12px;
    }

    .btn-action {
        padding: 12px 24px;
        border-radius: 12px;
        font-weight: 600;
        font-size: 14px;
        cursor: pointer;
        transition: all 0.3s;
        display: flex;
        align-items: center;
        gap: 8px;
        border: none;
        text-decoration: none;
    }

    .btn-back {
        background: rgba(255, 255, 255, 0.1);
        color: #fff;
        border: 2px solid rgba(255, 255, 255, 0.2);
    }

    .btn-back:hover {
        background: rgba(255, 255, 255, 0.15);
        border-color: rgba(255, 255, 255, 0.3);
        color: #fff;
    }

    .btn-update {
        background: linear-gradient(135deg, #17a2b8, #20c997);
        color: #fff;
        box-shadow: 0 5px 20px rgba(23, 162, 184, 0.4);
    }

    .btn-update:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(23, 162, 184, 0.5);
    }

    .editor-layout {
        display: grid;
        grid-template-columns: 1fr 380px;
        gap: 30px;
    }

    @media (max-width: 1100px) {
        .editor-layout {
            grid-template-columns: 1fr;
        }
    }

    .editor-main {
        display: flex;
        flex-direction: column;
        gap: 25px;
    }

    .editor-card {
        background: rgba(20, 20, 35, 0.95);
        border-radius: 20px;
        border: 1px solid rgba(255, 255, 255, 0.08);
        overflow: hidden;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
    }

    .card-header {
        padding: 20px 25px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .card-header i {
        color: #17a2b8;
        font-size: 18px;
    }

    .card-header h3 {
        color: #fff;
        font-size: 16px;
        font-weight: 600;
        margin: 0;
    }

    .card-body {
        padding: 25px;
    }

    .title-input {
        width: 100%;
        background: transparent;
        border: none;
        color: #fff;
        font-size: 32px;
        font-weight: 700;
        padding: 0;
        outline: none;
        line-height: 1.3;
    }

    .title-input::placeholder {
        color: #555;
    }

    .title-counter {
        text-align: right;
        color: #666;
        font-size: 13px;
        margin-top: 10px;
    }

    .ck-editor__editable {
        min-height: 450px !important;
        background: rgba(30, 30, 50, 0.5) !important;
        color: #fff !important;
        border: none !important;
        font-size: 16px !important;
        line-height: 1.8 !important;
    }

    .ck-editor__editable:focus {
        border: none !important;
        box-shadow: none !important;
    }

    .ck.ck-toolbar {
        background: rgba(40, 40, 60, 0.95) !important;
        border: none !important;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1) !important;
        padding: 10px 15px !important;
    }

    .ck.ck-button {
        color: #aaa !important;
        border-radius: 8px !important;
    }

    .ck.ck-button:hover {
        background: rgba(23, 162, 184, 0.3) !important;
        color: #fff !important;
    }

    .ck.ck-button.ck-on {
        background: rgba(23, 162, 184, 0.5) !important;
        color: #fff !important;
    }

    .ck.ck-editor__main>.ck-editor__editable {
        border-radius: 0 0 16px 16px !important;
    }

    .editor-sidebar {
        display: flex;
        flex-direction: column;
        gap: 25px;
    }

    .category-select {
        width: 100%;
        padding: 15px 20px;
        background: rgba(40, 40, 60, 0.8);
        border: 2px solid rgba(255, 255, 255, 0.1);
        border-radius: 12px;
        color: #fff;
        font-size: 15px;
        cursor: pointer;
        outline: none;
        transition: all 0.3s;
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='none' stroke='%23888' stroke-width='2'%3E%3Cpolyline points='6,9 12,15 18,9'%3E%3C/polyline%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 15px center;
        background-size: 18px;
    }

    .category-select:focus {
        border-color: #17a2b8;
        box-shadow: 0 0 20px rgba(23, 162, 184, 0.2);
    }

    .category-select option {
        background: #1a1a2e;
        color: #fff;
        padding: 10px;
    }

    .thumbnail-upload {
        position: relative;
    }

    .thumbnail-preview {
        width: 100%;
        height: 200px;
        background: rgba(40, 40, 60, 0.5);
        border: 2px dashed rgba(255, 255, 255, 0.2);
        border-radius: 16px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s;
        overflow: hidden;
        position: relative;
    }

    .thumbnail-preview:hover {
        border-color: #17a2b8;
        background: rgba(23, 162, 184, 0.1);
    }

    .thumbnail-preview.has-image {
        border-style: solid;
        border-color: #17a2b8;
    }

    .thumbnail-preview img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: none;
    }

    .thumbnail-preview.has-image img {
        display: block;
    }

    .thumbnail-preview.has-image .upload-placeholder {
        display: none;
    }

    .upload-placeholder {
        text-align: center;
        color: #666;
    }

    .upload-placeholder i {
        font-size: 40px;
        margin-bottom: 15px;
        color: #17a2b8;
    }

    .upload-placeholder p {
        margin: 0;
        font-size: 14px;
    }

    .upload-placeholder span {
        color: #17a2b8;
        font-weight: 600;
    }

    .thumbnail-input {
        display: none;
    }

    .remove-thumbnail {
        position: absolute;
        top: 10px;
        right: 10px;
        width: 36px;
        height: 36px;
        background: rgba(220, 53, 69, 0.9);
        border: none;
        border-radius: 50%;
        color: #fff;
        cursor: pointer;
        display: none;
        align-items: center;
        justify-content: center;
        transition: all 0.3s;
    }

    .thumbnail-preview.has-image .remove-thumbnail {
        display: flex;
    }

    .remove-thumbnail:hover {
        background: #dc3545;
        transform: scale(1.1);
    }

    .current-image-info {
        margin-top: 10px;
        padding: 10px 15px;
        background: rgba(23, 162, 184, 0.1);
        border-radius: 8px;
        color: #17a2b8;
        font-size: 13px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .status-card {
        background: linear-gradient(135deg, rgba(23, 162, 184, 0.15), rgba(32, 201, 151, 0.1));
        border: 1px solid rgba(23, 162, 184, 0.3);
    }

    .status-info {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .status-badge {
        padding: 8px 16px;
        border-radius: 20px;
        font-size: 14px;
        font-weight: 600;
    }

    .status-badge.published {
        background: rgba(40, 167, 69, 0.2);
        color: #28a745;
    }

    .status-badge.pending {
        background: rgba(255, 193, 7, 0.2);
        color: #ffc107;
    }

    .status-badge.rejected {
        background: rgba(220, 53, 69, 0.2);
        color: #dc3545;
    }

    .status-note {
        color: #888;
        font-size: 13px;
        margin-top: 15px;
        padding: 12px;
        background: rgba(255, 255, 255, 0.05);
        border-radius: 8px;
        line-height: 1.5;
    }

    .status-note i {
        color: #ffc107;
        margin-right: 8px;
    }

    .alert-box {
        padding: 15px 20px;
        border-radius: 12px;
        margin-bottom: 25px;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .alert-box.error {
        background: rgba(220, 53, 69, 0.15);
        border: 1px solid rgba(220, 53, 69, 0.3);
        color: #ff6b6b;
    }

    .alert-box.success {
        background: rgba(40, 167, 69, 0.15);
        border: 1px solid rgba(40, 167, 69, 0.3);
        color: #66ff8c;
    }

    .alert-box i {
        font-size: 20px;
    }

    @media (max-width: 768px) {
        .editor-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .editor-actions {
            width: 100%;
        }

        .btn-action {
            flex: 1;
            justify-content: center;
        }

        .title-input {
            font-size: 24px;
        }
    }
</style>

<div class="editor-page">
    <div class="editor-container">
        <!-- Header -->
        <div class="editor-header">
            <div class="editor-title">
                <div class="editor-title-icon">
                    <i class="fas fa-edit"></i>
                </div>
                <div>
                    <h1>Chỉnh sửa bài viết</h1>
                    <p>Cập nhật nội dung bài viết của bạn</p>
                </div>
            </div>
            <div class="editor-actions">
                <a href="author-history.php" class="btn-action btn-back">
                    <i class="fas fa-arrow-left"></i> Quay lại
                </a>
                <button type="submit" form="editForm" name="update_article" class="btn-action btn-update">
                    <i class="fas fa-save"></i> Lưu thay đổi
                </button>
            </div>
        </div>

        <?php if ($error): ?>
            <div class="alert-box error">
                <i class="fas fa-exclamation-circle"></i>
                <span><?= htmlspecialchars($error) ?></span>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" id="editForm">
            <div class="editor-layout">
                <!-- Main Content -->
                <div class="editor-main">
                    <!-- Title Card -->
                    <div class="editor-card">
                        <div class="card-header">
                            <i class="fas fa-heading"></i>
                            <h3>Tiêu đề bài viết</h3>
                        </div>
                        <div class="card-body">
                            <input type="text" name="title" class="title-input" placeholder="Nhập tiêu đề hấp dẫn..." maxlength="200" required id="titleInput" value="<?= htmlspecialchars($article['title']) ?>">
                            <div class="title-counter"><span id="titleCount"><?= strlen($article['title']) ?></span>/200 ký tự</div>
                        </div>
                    </div>

                    <!-- Content Card -->
                    <div class="editor-card">
                        <div class="card-header">
                            <i class="fas fa-align-left"></i>
                            <h3>Nội dung bài viết</h3>
                        </div>
                        <div class="card-body" style="padding: 0;">
                            <textarea name="content" id="editor" required><?= htmlspecialchars($article['content']) ?></textarea>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="editor-sidebar">
                    <!-- Status Card -->
                    <div class="editor-card status-card">
                        <div class="card-header">
                            <i class="fas fa-info-circle"></i>
                            <h3>Trạng thái</h3>
                        </div>
                        <div class="card-body">
                            <div class="status-info">
                                <span>Hiện tại:</span>
                                <span class="status-badge <?= $article['status'] ?>">
                                    <?php
                                    switch ($article['status']) {
                                        case 'published':
                                            echo '<i class="fas fa-check-circle me-1"></i> Đã duyệt';
                                            break;
                                        case 'pending':
                                            echo '<i class="fas fa-clock me-1"></i> Chờ duyệt';
                                            break;
                                        case 'rejected':
                                            echo '<i class="fas fa-times-circle me-1"></i> Từ chối';
                                            break;
                                        default:
                                            echo $article['status'];
                                    }
                                    ?>
                                </span>
                            </div>
                            <?php if ($user_role !== 'admin'): ?>
                                <div class="status-note">
                                    <i class="fas fa-info-circle"></i>
                                    Sau khi chỉnh sửa, bài viết sẽ được chuyển về trạng thái "Chờ duyệt" để admin xem xét lại.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Category Card -->
                    <div class="editor-card">
                        <div class="card-header">
                            <i class="fas fa-folder"></i>
                            <h3>Danh mục</h3>
                        </div>
                        <div class="card-body">
                            <select name="category_id" class="category-select" required>
                                <option value="">-- Chọn danh mục --</option>
                                <?php while ($cat = mysqli_fetch_assoc($categories)): ?>
                                    <option value="<?= $cat['category_id'] ?>" <?= $cat['category_id'] == $article['category_id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cat['name']) ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Thumbnail Card -->
                    <div class="editor-card">
                        <div class="card-header">
                            <i class="fas fa-image"></i>
                            <h3>Ảnh đại diện</h3>
                        </div>
                        <div class="card-body">
                            <div class="thumbnail-upload">
                                <label class="thumbnail-preview <?= !empty($article['featured_image']) ? 'has-image' : '' ?>" id="thumbnailPreview">
                                    <input type="file" name="thumbnail" class="thumbnail-input" id="thumbnailInput" accept="image/*">
                                    <img src="<?= !empty($article['featured_image']) ? '../' . htmlspecialchars($article['featured_image']) : '' ?>" alt="Preview" id="previewImage">
                                    <div class="upload-placeholder">
                                        <i class="fas fa-cloud-upload-alt"></i>
                                        <p>Kéo thả hoặc <span>chọn ảnh mới</span></p>
                                        <p style="font-size: 12px; margin-top: 5px;">PNG, JPG (Tối đa 5MB)</p>
                                    </div>
                                    <button type="button" class="remove-thumbnail" id="removeThumbnail">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </label>
                            </div>
                            <?php if (!empty($article['featured_image'])): ?>
                                <div class="current-image-info">
                                    <i class="fas fa-image"></i>
                                    <span>Ảnh hiện tại sẽ được giữ nếu không chọn ảnh mới</span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Article Info -->
                    <div class="editor-card">
                        <div class="card-header">
                            <i class="fas fa-chart-bar"></i>
                            <h3>Thông tin bài viết</h3>
                        </div>
                        <div class="card-body">
                            <div style="display: grid; gap: 15px;">
                                <div style="display: flex; justify-content: space-between; color: #888; font-size: 14px;">
                                    <span><i class="fas fa-calendar me-2" style="color: #17a2b8;"></i>Ngày tạo:</span>
                                    <span style="color: #fff;"><?= date('d/m/Y H:i', strtotime($article['created_at'])) ?></span>
                                </div>
                                <div style="display: flex; justify-content: space-between; color: #888; font-size: 14px;">
                                    <span><i class="fas fa-eye me-2" style="color: #17a2b8;"></i>Lượt xem:</span>
                                    <span style="color: #fff;"><?= number_format($article['views']) ?></span>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- CKEditor 5 -->
<script src="https://cdn.ckeditor.com/ckeditor5/41.0.0/classic/ckeditor.js"></script>
<script>
    let editorInstance;

    // Initialize CKEditor
    ClassicEditor
        .create(document.querySelector('#editor'), {
            toolbar: {
                items: [
                    'heading', '|',
                    'bold', 'italic', 'underline', 'strikethrough', '|',
                    'link', 'blockQuote', 'code', '|',
                    'bulletedList', 'numberedList', '|',
                    'outdent', 'indent', '|',
                    'insertTable', '|',
                    'undo', 'redo'
                ]
            },
            placeholder: 'Nhập nội dung bài viết...',
            language: 'vi'
        })
        .then(editor => {
            editorInstance = editor;
            editor.model.document.on('change:data', () => {
                document.querySelector('#editor').value = editor.getData();
            });
        })
        .catch(error => {
            console.error(error);
        });

    // Title counter
    const titleInput = document.getElementById('titleInput');
    const titleCount = document.getElementById('titleCount');

    titleInput.addEventListener('input', function() {
        titleCount.textContent = this.value.length;
    });

    // Thumbnail preview
    const thumbnailInput = document.getElementById('thumbnailInput');
    const thumbnailPreview = document.getElementById('thumbnailPreview');
    const previewImage = document.getElementById('previewImage');
    const removeThumbnail = document.getElementById('removeThumbnail');

    thumbnailInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                previewImage.src = e.target.result;
                thumbnailPreview.classList.add('has-image');
            };
            reader.readAsDataURL(file);
        }
    });

    removeThumbnail.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        thumbnailInput.value = '';
        previewImage.src = '';
        thumbnailPreview.classList.remove('has-image');
    });

    // Drag and drop
    thumbnailPreview.addEventListener('dragover', function(e) {
        e.preventDefault();
        this.style.borderColor = '#17a2b8';
        this.style.background = 'rgba(23, 162, 184, 0.15)';
    });

    thumbnailPreview.addEventListener('dragleave', function(e) {
        e.preventDefault();
        this.style.borderColor = '';
        this.style.background = '';
    });

    thumbnailPreview.addEventListener('drop', function(e) {
        e.preventDefault();
        this.style.borderColor = '';
        this.style.background = '';

        const file = e.dataTransfer.files[0];
        if (file && file.type.startsWith('image/')) {
            thumbnailInput.files = e.dataTransfer.files;
            const reader = new FileReader();
            reader.onload = function(e) {
                previewImage.src = e.target.result;
                thumbnailPreview.classList.add('has-image');
            };
            reader.readAsDataURL(file);
        }
    });
</script>

<?php include 'footer.php'; ?>
