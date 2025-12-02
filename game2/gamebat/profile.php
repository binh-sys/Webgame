<?php
session_start();
// Chứa biến $conn
require_once 'ketnoi.php'; 

// ===================================
// 🔹 CẤU HÌNH ĐƯỜNG DẪN UPLOAD (ĐÃ SỬA) 
// ===================================
// Đường dẫn tương đối (Dùng để hiển thị ảnh, chỉ là 'img/' theo cấu trúc của bạn)
$db_display_path = 'img/'; 
// Đường dẫn TUYỆT ĐỐI trên server (Dùng cho move_uploaded_file)
$server_upload_dir = __DIR__ . '/img/'; 

// Đảm bảo thư mục 'img' tồn tại và có quyền ghi
if (!is_dir($server_upload_dir)) {
    if (!mkdir($server_upload_dir, 0777, true)) {
        // Thông báo lỗi nếu không thể tạo thư mục
        error_log("Failed to create upload directory: " . $server_upload_dir);
    }
}

// ===================================
// 🔹 KIỂM TRA ĐĂNG NHẬP
// ===================================
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$message = '';

// ===================================
// 🔹 XỬ LÝ CẬP NHẬT HỒ SƠ (POST)
// ===================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_display_name = trim($_POST['display_name']);
    $new_description = trim($_POST['description']);
    $new_avatar_file = null;

    // 1. Xử lý Tải Lên Ảnh Đại Diện (Avatar)
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $file_tmp_name = $_FILES['avatar']['tmp_name'];
        $file_name = $_FILES['avatar']['name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];

        // Kiểm tra phần mở rộng file
        if (in_array($file_ext, $allowed_ext)) {
            // Tạo tên file duy nhất
            $unique_filename = md5(time() . $file_name) . '.' . $file_ext;
            
            // SỬ DỤNG ĐƯỜNG DẪN TUYỆT ĐỐI CHO move_uploaded_file
            $server_target_path = $server_upload_dir . $unique_filename; 

            // Dòng 41: Lỗi đã được khắc phục tại đây
            if (move_uploaded_file($file_tmp_name, $server_target_path)) {
                $new_avatar_file = $unique_filename; // Lưu tên file vào CSDL
                
                // Lấy avatar cũ để xóa (nếu không phải là ảnh mặc định)
                $old_avatar_filename = $_SESSION['avatar'] ?? '';
                // Giả định tên file mặc định là 'default-avatar.png'
                if (!empty($old_avatar_filename) && $old_avatar_filename !== 'default-avatar.png') {
                    $old_avatar_full_path = $server_upload_dir . $old_avatar_filename;
                    if (file_exists($old_avatar_full_path)) {
                        @unlink($old_avatar_full_path); // Xóa ảnh cũ
                    }
                }
            } else {
                $message = "<div class='alert alert-danger'>Lỗi khi tải lên file ảnh. Vui lòng kiểm tra quyền thư mục 'img'.</div>";
            }
        } else {
            $message = "<div class='alert alert-danger'>Chỉ cho phép các định dạng JPG, JPEG, PNG & GIF.</div>";
        }
    }

    // 2. Cập nhật vào CSDL
    if (empty($message)) {
        if ($new_avatar_file) {
            $stmt = $conn->prepare("UPDATE users SET display_name = ?, description = ?, avatar = ? WHERE user_id = ?");
            $stmt->bind_param("sssi", $new_display_name, $new_description, $new_avatar_file, $user_id);
        } else {
            $stmt = $conn->prepare("UPDATE users SET display_name = ?, description = ? WHERE user_id = ?");
            $stmt->bind_param("ssi", $new_display_name, $new_description, $user_id);
        }

        if ($stmt->execute()) {
            // Cập nhật session để header hiển thị đúng ngay lập tức
            $_SESSION['display_name'] = $new_display_name;
            if ($new_avatar_file) {
                $_SESSION['avatar'] = $new_avatar_file;
            }
            $message = "<div class='alert alert-success'>Cập nhật hồ sơ thành công!</div>";
            
            // --- Đồng bộ hóa Bảng `tacgia` (nếu cần) ---
            // Tạm thời bỏ qua phần này vì không có đủ thông tin về bảng tacgia trong code bạn cung cấp, 
            // nếu cần đồng bộ, bạn hãy thêm logic từ các bước trước vào đây.

        } else {
            $message = "<div class='alert alert-danger'>Lỗi CSDL khi cập nhật hồ sơ: " . $stmt->error . "</div>";
        }
        $stmt->close();
    }
}

// ===================================
// 🔹 LẤY DỮ LIỆU HIỆN TẠI
// ===================================
$current_user = null;
$stmt = $conn->prepare("SELECT display_name, email, avatar, description FROM users WHERE user_id = ? LIMIT 1");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $current_user = $result->fetch_assoc();
} else {
    // Trường hợp không tìm thấy user (rất hiếm)
    session_destroy();
    header("Location: login.php");
    exit;
}
$stmt->close();

// Tạo đường dẫn avatar hiển thị
$avatar_filename = $current_user['avatar'] ?? '';
$current_avatar = !empty($avatar_filename) 
    ? $db_display_path . htmlspecialchars($avatar_filename) 
    : 'img/default-avatar.png';
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hồ Sơ Cá Nhân</title>
    <link href="img/bat.png" rel="shortcut icon" />
    <link rel="stylesheet" href="css/bootstrap.min.css" />
    <link rel="stylesheet" href="css/font-awesome.min.css" />
    <link rel="stylesheet" href="css/style.css" /> 
    <style>
        .profile-card {
            background: #1e1e1e;
            border: 1px solid #333;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.5);
            color: #fff;
        }
        .avatar-preview {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            background-color: #333;
            border: 5px solid #ffc107;
            overflow: hidden;
            margin: 0 auto 20px;
            background-image: url('<?php echo $current_avatar; ?>');
            background-size: cover;
            background-position: center;
        }
        .form-control, .form-control:focus {
            background-color: #2a2a2a;
            color: #fff;
            border: 1px solid #555;
            box-shadow: none;
        }
        .btn-warning-custom {
            background-color: #ffc107;
            border: none;
            color: #000;
            font-weight: 600;
            transition: background-color 0.3s;
        }
        .btn-warning-custom:hover {
            background-color: #e0a800;
            color: #000;
        }
        .file-input-label {
            display: block;
            background-color: #333;
            color: #fff;
            padding: 8px 15px;
            border-radius: 6px;
            cursor: pointer;
            text-align: center;
            border: 1px dashed #ffc107;
        }
        .file-input {
            display: none;
        }
        .page-section {
            padding-top: 80px;
            padding-bottom: 80px;
            background: #111;
        }
    </style>
</head>

<body>
    <?php include 'header.php'; ?> 

    <section class="page-section spad">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-6">
                    <div class="profile-card text-center">
                        <h2 class="text-warning mb-4">Hồ Sơ Cá Nhân</h2>
                        
                        <?php echo $message; // Hiển thị thông báo ?>

                        <div id="avatar-preview" class="avatar-preview"></div>
                        
                        <form method="POST" enctype="multipart/form-data">
                            
                            <div class="mb-4">
                                <label for="avatar_upload" class="file-input-label">
                                    <i class="fa fa-camera me-2"></i> Chọn ảnh đại diện mới
                                </label>
                                <input type="file" name="avatar" id="avatar_upload" class="file-input" accept="image/*">
                            </div>

                            <div class="mb-3 text-start">
                                <label for="display_name" class="form-label">Tên hiển thị</label>
                                <input type="text" name="display_name" id="display_name" class="form-control" 
                                       value="<?php echo htmlspecialchars($current_user['display_name']); ?>" required>
                            </div>

                            <div class="mb-3 text-start">
                                <label for="email" class="form-label">Email (Không đổi)</label>
                                <input type="email" id="email" class="form-control" 
                                       value="<?php echo htmlspecialchars($current_user['email']); ?>" disabled>
                            </div>

                            <div class="mb-3 text-start">
                                <label for="description" class="form-label">Mô tả về bản thân (Tối đa 500 ký tự)</label>
                                <textarea name="description" id="description" rows="4" class="form-control" 
                                          maxlength="500"><?php echo htmlspecialchars($current_user['description'] ?? ''); ?></textarea>
                            </div>
                            
                            <button type="submit" class="btn btn-warning-custom mt-3 w-100">
                                <i class="fa fa-save me-2"></i> Lưu Thay Đổi
                            </button>
                        </form>
                        
                        <div class="mt-4">
                            <a href="change_password.php" class="text-warning small" style="text-decoration: none;">Đổi Mật Khẩu</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include 'footer.php'; ?> 

    <script>
        // Script xem trước ảnh đại diện
        document.getElementById('avatar_upload').addEventListener('change', function(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('avatar-preview').style.backgroundImage = 'url(' + e.target.result + ')';
                }
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>
</html>