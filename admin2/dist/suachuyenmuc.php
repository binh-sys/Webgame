<?php
require_once('ketnoi.php');

// --- Lấy ID chuyên mục cần sửa ---
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $result = mysqli_query($ketnoi, "SELECT * FROM categories WHERE category_id = $id");
    $row = mysqli_fetch_assoc($result);

    if (!$row) {
        echo "<script>alert('❌ Không tìm thấy chuyên mục!'); window.location='index.php?page_layout=danhsachchuyenmuc';</script>";
        exit();
    }
}

// --- Xử lý khi người dùng bấm Lưu ---
if (isset($_POST['update_category'])) {
    $name = trim($_POST['name']);
    $slug = trim($_POST['slug']);
    $description = trim($_POST['description']);

    // --- Sinh slug tự động nếu để trống ---
    if (empty($slug)) {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
    }

    // --- Kiểm tra trùng slug (trừ chính bản ghi đang sửa) ---
    $check = mysqli_query($ketnoi, "SELECT * FROM categories WHERE slug = '$slug' AND category_id != $id");
    if (mysqli_num_rows($check) > 0) {
        $original_slug = $slug;
        $count = 1;
        while (mysqli_num_rows(mysqli_query($ketnoi, "SELECT * FROM categories WHERE slug = '$slug' AND category_id != $id")) > 0) {
            $slug = $original_slug . '-' . $count++;
        }
    }

    // --- Cập nhật ---
    $sql = "UPDATE categories SET 
                name = '$name',
                slug = '$slug',
                description = '$description'
            WHERE category_id = $id";

    if (mysqli_query($ketnoi, $sql)) {
        echo "<script>
            alert('✅ Cập nhật chuyên mục thành công!');
            window.location = 'index.php?page_layout=danhsachchuyenmuc';
        </script>";
        exit();
    } else {
        echo "<script>alert('❌ Lỗi khi cập nhật chuyên mục!');</script>";
    }
}
?>

<div class='container mt-4'>
    <div class='card shadow-sm'>
        <div class='card-header bg-warning text-dark'>
            <h4 class='mb-0'>✏️ Sửa chuyên mục</h4>
        </div>

        <div class='card-body'>
            <form method='POST'>
                <div class='mb-3'>
                    <label class='form-label fw-bold'>Tên chuyên mục</label>
                    <input type='text' name='name' class='form-control' value='<?php echo htmlspecialchars($row['name']); ?>' required>
                </div>

                <div class='mb-3'>
                    <label class='form-label fw-bold'>Slug</label>
                    <input type='text' name='slug' class='form-control' value='<?php echo htmlspecialchars($row['slug']); ?>'>
                </div>

                <div class='mb-3'>
                    <label class='form-label fw-bold'>Mô tả</label>
                    <textarea name='description' class='form-control' rows='3'><?php echo htmlspecialchars($row['description']); ?></textarea>
                </div>

                <div class='d-flex justify-content-between mt-4'>
                    <button type='submit' name='update_category' class='btn btn-success px-4'>
                        <i class='bi bi-save'></i> Lưu thay đổi
                    </button>
                    <a href='index.php?page_layout=danhsachchuyenmuc' class='btn btn-secondary px-4'>
                        <i class='bi bi-arrow-left'></i> Quay lại
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
