<?php
session_start();
require_once('ketnoi.php');

// =========================
// CHỈ TÁC GIẢ / EDITOR ĐƯỢC VÀO
// =========================
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['author','editor'])) {
    echo '<script>alert("⚠️ Bạn không có quyền truy cập trang này!"); window.location.href="../index.php";</script>';
    exit();
}

// =========================
// HÀM TẠO SLUG
// =========================
function generateSlug($str) {
    $str = strtolower($str);
    $str = preg_replace('/[áàảãạăắằẳẵặâấầẩẫậ]/u','a',$str);
    $str = preg_replace('/[éèẻẽẹêếềểễệ]/u','e',$str);
    $str = preg_replace('/[íìỉĩị]/u','i',$str);
    $str = preg_replace('/[óòỏõọôốồổỗộơớờởỡợ]/u','o',$str);
    $str = preg_replace('/[úùủũụưứừửữự]/u','u',$str);
    $str = preg_replace('/[ýỳỷỹỵ]/u','y',$str);
    $str = preg_replace('/đ/','d',$str);
    $str = preg_replace('/[^a-z0-9]+/','-',$str);
    $str = trim($str, '-');
    return $str;
}

// =========================
// LẤY DANH MỤC
// =========================
$categories = mysqli_query($conn, "SELECT * FROM categories ORDER BY name ASC");

// =========================
// SUBMIT BÀI VIẾT
// =========================
$error = '';
if (isset($_POST['submit_article'])) {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $content = mysqli_real_escape_string($conn, $_POST['content']);
    $category_id = intval($_POST['category_id']);
    $author_id = $_SESSION['user_id'];
    $created_at = date('Y-m-d H:i:s');
    $status = 'pending';

    // Tạo slug
    $slug = generateSlug($title);
    $check = mysqli_query($conn, "SELECT article_id FROM articles WHERE slug='$slug'");
    if (mysqli_num_rows($check) > 0) $slug .= '-' . time();

    // Upload ảnh đại diện
    $featured_image = '';
    if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === 0) {
        $ext = pathinfo($_FILES['thumbnail']['name'], PATHINFO_EXTENSION);
        $upload_dir = __DIR__ . '/../uploads/';
        if (!file_exists($upload_dir)) mkdir($upload_dir, 0777, true);

        $new_name = uniqid() . '.' . $ext;
        $featured_image = 'uploads/' . $new_name;
        move_uploaded_file($_FILES['thumbnail']['tmp_name'], $upload_dir . $new_name);
    }

    // Insert bài viết
    $sql = "INSERT INTO articles (title, slug, content, category_id, author_id, featured_image, status, created_at)
            VALUES ('$title', '$slug', '$content', $category_id, $author_id, '$featured_image', '$status', '$created_at')";

    if (mysqli_query($conn, $sql)) {
        header("Location: new-article.php?success=1");
        exit();
    } else {
        $error = "Lỗi khi gửi bài!";
    }
}
?>

<?php include 'header.php'; ?>

<div class="container py-4">
    <div class="card p-4">
        <h3 class="mb-4">📝 Viết bài mới</h3>

        <form method="POST" enctype="multipart/form-data" action="">
            <div class="mb-3">
                <label>Tiêu đề bài viết</label>
                <input type="text" name="title" class="form-control" required>
            </div>

            <div class="mb-3">
                <label>Danh mục</label>
                <select name="category_id" class="form-control" required>
                    <option value="">-- Chọn danh mục --</option>
                    <?php while ($cat = mysqli_fetch_assoc($categories)): ?>
                        <option value="<?= $cat['category_id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="mb-3">
                <label>Nội dung bài viết</label>
                <textarea name="content" id="editor" class="form-control" rows="10" required></textarea>
            </div>

            <div class="mb-3">
                <label>Ảnh đại diện (tùy chọn)</label>
                <input type="file" name="thumbnail" class="form-control">
            </div>

            <button type="submit" name="submit_article" class="btn btn-success">Gửi bài viết</button>
        </form>
    </div>
</div>

<!-- CKEditor 5 -->
<script src="https://cdn.ckeditor.com/ckeditor5/38.1.0/classic/ckeditor.js"></script>
<script>
let editorInstance;
ClassicEditor
    .create(document.querySelector('#editor'), {
        toolbar: ['heading','|','bold','italic','underline','link','bulletedList','numberedList','blockQuote','|','insertTable','undo','redo'],
        image: { toolbar: ['imageTextAlternative','imageStyle:full','imageStyle:side'] }
    })
    .then(editor => {
        editorInstance = editor;
        editor.model.document.on('change:data', () => {
            document.querySelector('#editor').value = editor.getData();
        });
    })
    .catch(error => { console.error(error); });
</script>

<!-- Toast Notify -->
<div id="toastNotify" class="toast hidden"><span id="toastIcon"></span> <span id="toastMessage"></span></div>

<style>
.card { background:#fff; border-radius:15px; box-shadow:0 5px 20px rgba(0,0,0,0.1); }

/* Toast */
.toast {
    position: fixed; top: 20px; right: 20px;
    display: flex; align-items: center; gap: 10px;
    padding: 15px 22px;
    border-radius: 12px;
    color: #fff; font-size: 16px; font-weight: 500;
    box-shadow: 0 8px 25px rgba(0,0,0,0.35);
    opacity: 0; transform: translateX(100%); transition: 0.5s ease;
    z-index: 9999;
}
.toast.show { opacity: 1; transform: translateX(0); }
.toast.success {
    background: linear-gradient(45deg, #28a745, #7bed9f);
    animation: bounce 0.6s;
    box-shadow: 0 8px 25px rgba(40,167,69,0.5);
}
.toast.error {
    background: linear-gradient(45deg, #dc3545, #ff6b6b);
    animation: shake 0.6s;
    box-shadow: 0 8px 25px rgba(220,53,69,0.5);
}
.toast span#toastIcon { font-size: 20px; }

@keyframes bounce {
    0%, 20%, 50%, 80%, 100% { transform: translateX(100%) translateY(0); }
    40% { transform: translateX(0) translateY(-10px); }
    60% { transform: translateX(0) translateY(-5px); }
}
@keyframes shake {
    0%, 100% { transform: translateX(100%); }
    25% { transform: translateX(90%); }
    50% { transform: translateX(110%); }
    75% { transform: translateX(95%); }
}
</style>

<script>
function showToast(message, type = 'success') {
    const toast = document.getElementById('toastNotify');
    const icon = document.getElementById('toastIcon');
    const msg = document.getElementById('toastMessage');

    msg.innerHTML = message;
    icon.innerHTML = type === 'success' ? '🎉' : '❌';
    toast.className = 'toast ' + type + ' show';

    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.classList.add('hidden'), 500);
    }, 3000);
}

<?php if(isset($_GET['success'])): ?>
showToast("Bài viết đã được gửi chờ admin duyệt!", "success");
<?php endif; ?>

<?php if(!empty($error)): ?>
showToast("<?= $error ?>", "error");
<?php endif; ?>
</script>

<?php include 'footer.php'; ?>
