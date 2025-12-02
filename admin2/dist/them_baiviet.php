<?php
// add_article_pro.php - Thiết kế lại toàn bộ giao diện + nâng cấp bảo mật cơ bản
require_once('ketnoi.php');

// Lấy danh mục và tác giả để hiển thị trong select
$categories = mysqli_query($ketnoi, "SELECT * FROM categories ORDER BY name ASC");
$authors = mysqli_query($ketnoi, "SELECT * FROM authors ORDER BY name ASC");

// Xử lý khi form được submit
if (isset($_POST['add_article'])) {
    // Sử dụng prepared statements để an toàn hơn
    $title = trim($_POST['title'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    $excerpt = trim($_POST['excerpt'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $category_id = intval($_POST['category_id'] ?? 0);
    $author_id = intval($_POST['author_id'] ?? 0);
    $status = in_array($_POST['status'] ?? '', ['draft','published']) ? $_POST['status'] : 'draft';
    $views = 0;
    $created_at = date('Y-m-d H:i:s');

    // Kiểm tra sơ bộ
    $errors = [];
    if ($title === '') $errors[] = 'Tiêu đề không được để trống.';
    if ($slug === '') $errors[] = 'Slug không được để trống.';
    if ($category_id <= 0) $errors[] = 'Vui lòng chọn danh mục.';
    if ($author_id <= 0) $errors[] = 'Vui lòng chọn tác giả.';

   // Xử lý ảnh đại diện (giữ nguyên tên file, không đổi)
$featured_image = '';
if (!empty($_FILES['featured_image']['name'])) {
    $upload_dir = __DIR__ . '/../uploads/articles/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    // Tên file gốc (lọc bỏ ký tự nguy hiểm)
    $raw_name = basename($_FILES['featured_image']['name']);
    $raw_name = preg_replace('/[^a-zA-Z0-9\.\-_]/', '', $raw_name);

    $ext = strtolower(pathinfo($raw_name, PATHINFO_EXTENSION));
    $allowed = ['jpg','jpeg','png','webp','gif'];

    if (!in_array($ext, $allowed)) {
        $errors[] = 'Định dạng ảnh không hợp lệ. Cho phép: jpg, jpeg, png, webp, gif.';
    } else {
        $file_name = $raw_name;
        $target_path = $upload_dir . $file_name;

        // Nếu bị trùng tên → thêm -copy, -copy2,...
        $i = 1;
        while (file_exists($target_path)) {
            $file_name = pathinfo($raw_name, PATHINFO_FILENAME) . "-copy{$i}." . $ext;
            $target_path = $upload_dir . $file_name;
            $i++;
        }

        // Upload file
        if (move_uploaded_file($_FILES['featured_image']['tmp_name'], $target_path)) {
            $featured_image = $file_name; // Lưu tên gốc vào DB
        } else {
            $errors[] = 'Không thể upload ảnh. Vui lòng thử lại.';
        }
    }
}


    if (empty($errors)) {
        // Prepared statement
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

<!doctype html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Thêm bài viết — Admin</title>

  <!-- Boxicons -->
  <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
  <!-- TinyMCE -->
  <script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/7/tinymce.min.js" referrerpolicy="origin"></script>

  <style>
  :root{
    --bg:#0f1724; --card:#0b1220; --accent:#00e6d6; --muted:#98a0ad; --glass: rgba(255,255,255,0.04);
    --light-bg:#f6f7fb; --text:#e6eef6;
  }
  html,body{height:100%;margin:0;font-family: "Segoe UI", Roboto, Arial, sans-serif;background:linear-gradient(180deg,#071021 0%, #091428 100%);color:var(--text)}

  .container{max-width:1100px;margin:28px auto;padding:18px}
  .card{background:linear-gradient(180deg, rgba(255,255,255,0.02), rgba(255,255,255,0.01));border-radius:14px;box-shadow: 0 10px 30px rgba(2,6,23,.6);overflow:hidden}

  .card-header{display:flex;justify-content:space-between;align-items:center;padding:18px 22px;background:linear-gradient(90deg, rgba(0,230,214,0.12), rgba(0,180,168,0.08));border-bottom:1px solid rgba(255,255,255,0.03)}
  .card-header h1{margin:0;font-size:18px;letter-spacing:.2px}
  .breadcrumbs{font-size:13px;color:var(--muted)}

  .card-body{padding:22px;display:grid;grid-template-columns:1fr 360px;gap:22px}

  /* FORM */
  form .group{margin-bottom:14px}
  label{display:block;font-size:13px;color:var(--muted);margin-bottom:8px}
  input[type="text"], select, textarea, .form-file{width:100%;background:transparent;border:1px solid rgba(255,255,255,0.06);padding:12px;border-radius:10px;color:var(--text);box-sizing:border-box}
  input::placeholder, textarea::placeholder{color:rgba(255,255,255,0.35)}
  textarea{min-height:220px}
  .form-row{display:flex;gap:12px}
  .form-row .col{flex:1}

  /* Right column card */
  .card-side{background:linear-gradient(180deg, rgba(255,255,255,0.01), rgba(255,255,255,0.005));padding:18px;border-radius:10px;border:1px solid rgba(255,255,255,0.02)}
  .meta-item{margin-bottom:12px}
  .meta-item .small{font-size:13px;color:var(--muted)}

  /* Buttons */
  .btn-row{display:flex;gap:12px;margin-top:8px}
  .btn{padding:10px 14px;border-radius:10px;border:none;cursor:pointer;font-weight:700}
  .btn-primary{background:linear-gradient(90deg,var(--accent),#00b39f);color:#001;box-shadow:0 8px 24px rgba(0,180,159,0.12)}
  .btn-ghost{background:transparent;border:1px solid rgba(255,255,255,0.04);color:var(--text)}
  .btn-cancel{background:#ff4d6d;color:#fff}

  /* Thumbnail */
  .thumb{width:100%;height:180px;object-fit:cover;border-radius:8px;border:1px solid rgba(255,255,255,0.03)}
  .muted{color:var(--muted)}

  /* Floating save for mobile */
  @media (max-width:980px){
    .card-body{grid-template-columns:1fr;}
  }

  /* small helpers */
  .error-list{background:rgba(255,64,64,0.06);padding:10px;border-radius:8px;margin-bottom:12px;color:#ffd6d6}
  </style>
</head>
<body>

<div class="container">
  <div class="card">
    <div class="card-header">
      <div>
        <h1>Thêm bài viết (PRO)</h1>
        <div class="breadcrumbs">Trang chủ / Bài viết / Thêm mới</div>
      </div>

      <div style="display:flex;gap:10px;align-items:center">
        <button class="btn btn-ghost" onclick="history.back()"><i class='bx bx-arrow-back'></i>&nbsp;Quay lại</button>
        <button id="toggleDark" class="btn btn-ghost" title="Bật/Tắt giao diện tối"><i class='bx bx-moon'></i></button>
      </div>
    </div>

    <div class="card-body">

      <!-- MAIN FORM -->
      <div>
        <?php if (!empty($errors)) { ?>
          <div class="error-list">
            <strong>Lỗi:</strong>
            <ul>
              <?php foreach ($errors as $err) echo '<li>'.htmlspecialchars($err).'</li>'; ?>
            </ul>
          </div>
        <?php } ?>

        <form id="articleForm" method="POST" enctype="multipart/form-data" novalidate>
          <input type="hidden" name="add_article" value="1">

          <div class="group">
            <label for="title">Tiêu đề</label>
            <input id="title" name="title" type="text" placeholder="Ví dụ: Tin tức Esports hôm nay" value="<?= isset($title) ? htmlspecialchars($title) : '' ?>" required>
          </div>

          <div class="form-row">
            <div class="col">
              <div class="group">
                <label for="slug">Slug (đường dẫn)</label>
                <input id="slug" name="slug" type="text" placeholder="ví dụ: tin-tuc-game" value="<?= isset($slug) ? htmlspecialchars($slug) : '' ?>">
              </div>
            </div>
            <div class="col">
              <div class="group">
                <label for="category_id">Danh mục</label>
                <select id="category_id" name="category_id" required>
                  <option value="">-- Chọn danh mục --</option>
                  <?php mysqli_data_seek($categories, 0); while ($cat = mysqli_fetch_assoc($categories)) { ?>
                    <option value="<?= $cat['category_id'] ?>" <?= (isset($category_id) && $category_id == $cat['category_id'])? 'selected':'' ?>><?= htmlspecialchars($cat['name']) ?></option>
                  <?php } ?>
                </select>
              </div>
            </div>
          </div>

          <div class="group">
            <label for="excerpt">Tóm tắt</label>
            <textarea id="excerpt" name="excerpt" rows="3" placeholder="Tóm tắt ngắn gọn..."><?= isset($excerpt) ? htmlspecialchars($excerpt) : '' ?></textarea>
          </div>

          <div class="group">
            <label for="editor">Nội dung</label>
            <textarea id="editor" name="content"><?= isset($content) ? htmlspecialchars($content) : '' ?></textarea>
          </div>

        </form>
      </div>

      <!-- RIGHT META / ACTIONS -->
      <aside class="card-side">
        <div class="meta-item">
          <div class="small">Tác giả</div>
          <select name="author_id" id="author_id" form="articleForm" required>
            <option value="">-- Chọn tác giả --</option>
            <?php mysqli_data_seek($authors, 0); while ($au = mysqli_fetch_assoc($authors)) { ?>
              <option value="<?= $au['author_id'] ?>" <?= (isset($author_id) && $author_id == $au['author_id']) ? 'selected':'' ?>><?= htmlspecialchars($au['name']) ?></option>
            <?php } ?>
          </select>
        </div>

        <div class="meta-item">
          <div class="small">Trạng thái</div>
          <select name="status" id="status" form="articleForm">
            <option value="draft" <?= (isset($status) && $status=='draft')? 'selected':'' ?>>Nháp</option>
            <option value="published" <?= (isset($status) && $status=='published')? 'selected':'' ?>>Xuất bản</option>
          </select>
        </div>

        <div class="meta-item">
          <div class="small">Ảnh đại diện</div>
          <input type="file" name="featured_image" id="featured_image" form="articleForm" accept="image/*" onchange="previewImage(event)">
          <div style="height:10px"></div>
          <img id="thumb" class="thumb" src="/mnt/data/5b0825ec-1d0a-4d14-85b8-a62519c32a9a.png" alt="Preview" />
          <div class="muted" style="margin-top:8px;font-size:13px">Kích thước gợi ý: 1200x630, định dạng JPG/PNG/WebP</div>
        </div>

        <div style="margin-top:18px" class="btn-row">
          <button class="btn btn-primary" onclick="openConfirm(event)"><i class='bx bx-save'></i>&nbsp; Lưu</button>
          <button class="btn btn-ghost" onclick="document.getElementById('articleForm').reset();resetThumb();">Đặt lại</button>
          <button class="btn btn-ghost" onclick="history.back()"><i class='bx bx-arrow-back'></i>&nbsp; Hủy</button>
        </div>

        <div style="margin-top:14px;font-size:13px;color:var(--muted)">
          Lưu ý: Nội dung dài có thể ảnh hưởng tới hiển thị trang.
        </div>
      </aside>

    </div>
  </div>
</div>

<!-- Confirm modal -->
<div id="confirm" style="display:none;position:fixed;inset:0;background:rgba(2,6,23,0.6);z-index:9999;align-items:center;justify-content:center"> 
  <div style="background:linear-gradient(180deg,#071021,#0b1220);padding:18px;border-radius:10px;width:380px;box-shadow:0 14px 40px rgba(2,6,23,.7);text-align:center;color:var(--text)">
    <h3 style="margin:0 0 8px">Xác nhận lưu bài viết?</h3>
    <p style="color:var(--muted)">Hệ thống sẽ lưu bài viết vào cơ sở dữ liệu. Bạn có chắc chắn muốn lưu?</p>
    <div style="display:flex;gap:10px;justify-content:center;margin-top:14px">
      <button class="btn btn-primary" id="confirmSave">Đồng ý</button>
      <button class="btn btn-ghost" onclick="closeConfirm()">Hủy</button>
    </div>
  </div>
</div>

<script>
// Dark toggle (simple)
document.getElementById('toggleDark').addEventListener('click', function(){
  document.documentElement.classList.toggle('dark');
  // you can expand to remember preference via localStorage
});

// TinyMCE init
if (typeof tinymce !== 'undefined'){
  tinymce.init({
    selector: '#editor',
    height: 420,
    plugins: 'code image link lists media table fullscreen autolink advlist paste',
    toolbar: 'undo redo | bold italic underline | styleselect | alignleft aligncenter alignright | bullist numlist | link image | fullscreen | code',
    menubar: false,
    branding: false,
    paste_data_images: true
  });
}

// Preview image
function previewImage(e){
  const f = e.target.files && e.target.files[0];
  const t = document.getElementById('thumb');
  if (f){
    t.src = URL.createObjectURL(f);
  }
}
function resetThumb(){
  document.getElementById('thumb').src = '/mnt/data/5b0825ec-1d0a-4d14-85b8-a62519c32a9a.png';
}

// Confirmation modal
function openConfirm(e){
  e && e.preventDefault();
  // validate before open
  if (!validateBeforeSubmit()) return;
  document.getElementById('confirm').style.display = 'flex';
}
function closeConfirm(){ document.getElementById('confirm').style.display='none'; }

document.getElementById('confirmSave').addEventListener('click', function(){
  // trigger tinymce save
  if (typeof tinymce !== 'undefined') tinymce.triggerSave();
  document.getElementById('articleForm').submit();
});

// Basic client validation
function validateBeforeSubmit(){
  if (typeof tinymce !== 'undefined') tinymce.triggerSave();
  const title = document.getElementById('title').value.trim();
  const cat = document.getElementById('category_id').value;
  const author = document.getElementById('author_id').value;
  if (!title){ alert('Vui lòng nhập tiêu đề.'); document.getElementById('title').focus(); return false; }
  if (!cat){ alert('Vui lòng chọn danh mục.'); document.getElementById('category_id').focus(); return false; }
  if (!author){ alert('Vui lòng chọn tác giả.'); document.getElementById('author_id').focus(); return false; }
  return true;
}
</script>

</body>
</html>
