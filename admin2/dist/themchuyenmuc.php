<?php
require_once('ketnoi.php');

// --- Khi người dùng nhấn nút thêm ---
if (isset($_POST['add_category'])) {
    $name = trim($_POST['name']);
    $slug = trim($_POST['slug']);
    $description = trim($_POST['description']);

    // --- Sinh slug tự động nếu người dùng không nhập ---
    if (empty($slug)) {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
    }

    // --- Kiểm tra trùng slug và tự động thêm hậu tố nếu trùng ---
    $original_slug = $slug;
    $count = 1;
    while (mysqli_num_rows(mysqli_query($ketnoi, "SELECT * FROM categories WHERE slug = '$slug'")) > 0) {
        $slug = $original_slug . '-' . $count++;
    }

    // --- Thực hiện thêm chuyên mục ---
    $sql = "INSERT INTO categories (name, slug, description) 
            VALUES ('$name', '$slug', '$description')";
    
    if (mysqli_query($ketnoi, $sql)) {
        echo "<script>
            alert('✅ Thêm chuyên mục thành công!');
            window.location = 'index.php?page_layout=danhsachchuyenmuc';
        </script>";
        exit();
    } else {
        echo "<script>alert('❌ Lỗi khi thêm chuyên mục!');</script>";
    }
}
?>

<div class='container mt-4'>
    <div class='card shadow-sm'>
        <div class='card-header bg-primary text-white'>
            <h4 class='mb-0'>➕ Thêm chuyên mục mới</h4>
        </div>

        <div class='card-body'>
            <form method='POST'>
                <div class='mb-3'>
                    <label class='form-label fw-bold'>Tên chuyên mục</label>
                    <input type='text' name='name' class='form-control' required placeholder='Nhập tên chuyên mục...'>
                </div>

                <div class='mb-3'>
                    <label class='form-label fw-bold'>Slug (đường dẫn ngắn)</label>
                    <input type='text' name='slug' class='form-control' placeholder='Tự động sinh nếu để trống'>
                </div>

                <div class='mb-3'>
                    <label class='form-label fw-bold'>Mô tả</label>
                    <textarea name='description' class='form-control' rows='3' placeholder='Nhập mô tả ngắn...'></textarea>
                </div>

                <div class='d-flex justify-content-between mt-4'>
                    <button type='submit' name='add_category' class='btn btn-success px-4'>
                        <i class='bi bi-save'></i> Thêm chuyên mục
                    </button>
                    <a href='index.php?page_layout=danhsachchuyenmuc' class='btn btn-secondary px-4'>
                        <i class='bi bi-arrow-left'></i> Quay lại
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
