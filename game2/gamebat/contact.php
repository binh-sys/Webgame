<?php
require_once 'ketnoi.php';

$success = '';
$error = '';

// Xử lý form gửi liên hệ
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $error = 'Vui lòng điền đầy đủ thông tin!';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Email không hợp lệ!';
    } else {
        // Lưu vào database hoặc gửi email
        // Ở đây demo thành công
        $success = 'Cảm ơn bạn đã liên hệ! Chúng tôi sẽ phản hồi sớm nhất có thể.';
    }
}

include 'header.php';
?>

<style>
    /* ===== CONTACT PAGE STYLES ===== */
    .contact-page {
        background: linear-gradient(135deg, #0a0a0a 0%, #1a1a2e 50%, #16213e 100%);
        min-height: 100vh;
        padding: 40px 0 80px;
        position: relative;
    }

    .contact-page::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background:
            radial-gradient(circle at 20% 30%, rgba(16, 185, 129, 0.08) 0%, transparent 40%),
            radial-gradient(circle at 80% 70%, rgba(52, 211, 153, 0.06) 0%, transparent 40%);
        pointer-events: none;
    }

    .contact-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 0 20px;
        position: relative;
        z-index: 10;
    }

    /* Page Header */
    .contact-header {
        text-align: center;
        margin-bottom: 50px;
    }

    .contact-header-icon {
        width: 80px;
        height: 80px;
        background: linear-gradient(135deg, #10b981, #34d399);
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 25px;
        box-shadow: 0 15px 40px rgba(16, 185, 129, 0.3);
    }

    .contact-header-icon i {
        font-size: 36px;
        color: #fff;
    }

    .contact-header h1 {
        color: #fff;
        font-size: 42px;
        font-weight: 800;
        margin-bottom: 15px;
    }

    .contact-header p {
        color: #888;
        font-size: 18px;
        max-width: 600px;
        margin: 0 auto;
    }

    /* Contact Cards */
    .contact-cards {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 25px;
        margin-bottom: 50px;
    }

    @media (max-width: 1000px) {
        .contact-cards {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 600px) {
        .contact-cards {
            grid-template-columns: 1fr;
        }
    }

    .contact-card {
        background: rgba(20, 20, 35, 0.98);
        border-radius: 20px;
        border: 1px solid rgba(255, 255, 255, 0.08);
        padding: 30px;
        text-align: center;
        transition: all 0.3s;
    }

    .contact-card:hover {
        transform: translateY(-5px);
        border-color: rgba(16, 185, 129, 0.3);
    }

    .contact-card-icon {
        width: 60px;
        height: 60px;
        background: linear-gradient(135deg, #10b981, #34d399);
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 20px;
        font-size: 24px;
        color: #fff;
    }

    .contact-card h3 {
        color: #fff;
        font-size: 18px;
        font-weight: 600;
        margin-bottom: 10px;
    }

    .contact-card p {
        color: #888;
        font-size: 15px;
        line-height: 1.6;
        margin: 0;
    }

    .contact-card a {
        color: #10b981;
        text-decoration: none;
    }

    .contact-card a:hover {
        text-decoration: underline;
    }

    /* Main Layout */
    .contact-layout {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 40px;
    }

    @media (max-width: 1000px) {
        .contact-layout {
            grid-template-columns: 1fr;
        }
    }

    /* Contact Form */
    .contact-form-card {
        background: rgba(20, 20, 35, 0.98);
        border-radius: 24px;
        border: 1px solid rgba(255, 255, 255, 0.08);
        padding: 40px;
    }

    .form-title {
        color: #fff;
        font-size: 24px;
        font-weight: 700;
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .form-title i {
        color: #10b981;
    }

    .form-subtitle {
        color: #888;
        font-size: 15px;
        margin-bottom: 30px;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        color: #aaa;
        font-size: 13px;
        font-weight: 500;
        margin-bottom: 8px;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .form-input {
        width: 100%;
        padding: 16px 20px;
        background: rgba(0, 0, 0, 0.3);
        border: 2px solid rgba(255, 255, 255, 0.1);
        border-radius: 12px;
        color: #fff;
        font-size: 15px;
        outline: none;
        transition: all 0.3s;
    }

    .form-input:focus {
        border-color: #10b981;
        box-shadow: 0 0 20px rgba(16, 185, 129, 0.15);
    }

    .form-input::placeholder {
        color: #555;
    }

    .form-textarea {
        min-height: 150px;
        resize: vertical;
    }

    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }

    @media (max-width: 600px) {
        .form-row {
            grid-template-columns: 1fr;
        }
    }

    .form-submit {
        width: 100%;
        padding: 18px;
        background: linear-gradient(135deg, #10b981, #34d399);
        border: none;
        border-radius: 12px;
        color: #fff;
        font-size: 16px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.3s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        margin-top: 10px;
    }

    .form-submit:hover {
        transform: translateY(-3px);
        box-shadow: 0 15px 35px rgba(16, 185, 129, 0.4);
    }

    /* Alert Messages */
    .alert {
        padding: 15px 20px;
        border-radius: 12px;
        margin-bottom: 25px;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .alert-success {
        background: rgba(16, 185, 129, 0.15);
        border: 1px solid rgba(16, 185, 129, 0.3);
        color: #34d399;
    }

    .alert-error {
        background: rgba(239, 68, 68, 0.15);
        border: 1px solid rgba(239, 68, 68, 0.3);
        color: #f87171;
    }

    .alert i {
        font-size: 20px;
    }

    /* Map Section */
    .map-card {
        background: rgba(20, 20, 35, 0.98);
        border-radius: 24px;
        border: 1px solid rgba(255, 255, 255, 0.08);
        overflow: hidden;
    }

    .map-header {
        padding: 25px 30px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.08);
    }

    .map-title {
        color: #fff;
        font-size: 20px;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 12px;
        margin: 0;
    }

    .map-title i {
        color: #10b981;
    }

    .map-wrapper {
        height: 300px;
    }

    .map-wrapper iframe {
        width: 100%;
        height: 100%;
        border: none;
    }

    .map-info {
        padding: 25px 30px;
        display: flex;
        align-items: center;
        gap: 15px;
        background: rgba(16, 185, 129, 0.1);
    }

    .map-info i {
        color: #10b981;
        font-size: 20px;
    }

    .map-info p {
        color: #ccc;
        font-size: 14px;
        margin: 0;
    }

    /* Social Links */
    .social-section {
        margin-top: 30px;
        padding-top: 30px;
        border-top: 1px solid rgba(255, 255, 255, 0.08);
    }

    .social-title {
        color: #fff;
        font-size: 16px;
        font-weight: 600;
        margin-bottom: 20px;
    }

    .social-links {
        display: flex;
        gap: 12px;
    }

    .social-link {
        width: 45px;
        height: 45px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 18px;
        transition: all 0.3s;
        text-decoration: none;
    }

    .social-link.facebook { background: #1877f2; }
    .social-link.twitter { background: #1da1f2; }
    .social-link.youtube { background: #ff0000; }
    .social-link.discord { background: #5865f2; }
    .social-link.tiktok { background: #000; border: 1px solid #333; }

    .social-link:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
    }

    /* FAQ Section */
    .faq-section {
        margin-top: 60px;
    }

    .faq-title {
        color: #fff;
        font-size: 28px;
        font-weight: 700;
        text-align: center;
        margin-bottom: 40px;
    }

    .faq-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 25px;
    }

    @media (max-width: 800px) {
        .faq-grid {
            grid-template-columns: 1fr;
        }
    }

    .faq-item {
        background: rgba(20, 20, 35, 0.98);
        border-radius: 16px;
        border: 1px solid rgba(255, 255, 255, 0.08);
        padding: 25px;
        transition: all 0.3s;
    }

    .faq-item:hover {
        border-color: rgba(16, 185, 129, 0.3);
    }

    .faq-question {
        color: #fff;
        font-size: 16px;
        font-weight: 600;
        margin-bottom: 12px;
        display: flex;
        align-items: flex-start;
        gap: 12px;
    }

    .faq-question i {
        color: #10b981;
        margin-top: 3px;
    }

    .faq-answer {
        color: #888;
        font-size: 14px;
        line-height: 1.7;
        padding-left: 28px;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .contact-header h1 {
            font-size: 32px;
        }

        .contact-form-card {
            padding: 30px;
        }
    }
</style>

<div class="contact-page">
    <div class="contact-container">
        <!-- Header -->
        <div class="contact-header">
            <div class="contact-header-icon">
                <i class="fas fa-envelope"></i>
            </div>
            <h1>Liên Hệ Với Chúng Tôi</h1>
            <p>Chúng tôi luôn sẵn sàng lắng nghe và hỗ trợ bạn. Hãy gửi tin nhắn cho chúng tôi!</p>
        </div>

        <!-- Contact Cards -->
        <div class="contact-cards">
            <div class="contact-card">
                <div class="contact-card-icon"><i class="fas fa-map-marker-alt"></i></div>
                <h3>Địa chỉ</h3>
                <p>60 QL1A, Văn Bình,<br>Thường Tín, Hà Nội</p>
            </div>
            <div class="contact-card">
                <div class="contact-card-icon"><i class="fas fa-phone-alt"></i></div>
                <h3>Điện thoại</h3>
                <p><a href="tel:18006770">1800 6770</a><br>(Miễn phí)</p>
            </div>
            <div class="contact-card">
                <div class="contact-card-icon"><i class="fas fa-envelope"></i></div>
                <h3>Email</h3>
                <p><a href="mailto:contact@gamebat.vn">contact@gamebat.vn</a></p>
            </div>
            <div class="contact-card">
                <div class="contact-card-icon"><i class="fas fa-clock"></i></div>
                <h3>Giờ làm việc</h3>
                <p>Thứ 2 - Thứ 6<br>8:00 - 17:00</p>
            </div>
        </div>

        <!-- Main Layout -->
        <div class="contact-layout">
            <!-- Contact Form -->
            <div class="contact-form-card">
                <h2 class="form-title"><i class="fas fa-paper-plane"></i> Gửi tin nhắn</h2>
                <p class="form-subtitle">Điền thông tin bên dưới, chúng tôi sẽ phản hồi trong 24 giờ</p>

                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <span><?= htmlspecialchars($success) ?></span>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <span><?= htmlspecialchars($error) ?></span>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Họ và tên</label>
                            <input type="text" name="name" class="form-input" placeholder="Nhập họ và tên" required>
                        </div>
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="email" class="form-input" placeholder="example@email.com" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Tiêu đề</label>
                        <input type="text" name="subject" class="form-input" placeholder="Tiêu đề tin nhắn" required>
                    </div>
                    <div class="form-group">
                        <label>Nội dung</label>
                        <textarea name="message" class="form-input form-textarea" placeholder="Nhập nội dung tin nhắn..." required></textarea>
                    </div>
                    <button type="submit" class="form-submit">
                        <i class="fas fa-paper-plane"></i> Gửi tin nhắn
                    </button>
                </form>

                <!-- Social Links -->
                <div class="social-section">
                    <h4 class="social-title">Kết nối với chúng tôi</h4>
                    <div class="social-links">
                        <a href="#" class="social-link facebook"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="social-link twitter"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="social-link youtube"><i class="fab fa-youtube"></i></a>
                        <a href="#" class="social-link discord"><i class="fab fa-discord"></i></a>
                        <a href="#" class="social-link tiktok"><i class="fab fa-tiktok"></i></a>
                    </div>
                </div>
            </div>

            <!-- Map -->
            <div class="map-card">
                <div class="map-header">
                    <h3 class="map-title"><i class="fas fa-map-marked-alt"></i> Vị trí của chúng tôi</h3>
                </div>
                <div class="map-wrapper">
                    <iframe 
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3727.5356173320542!2d105.85620027387178!3d20.890759892506793!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3135b306c30f20b3%3A0xf0806e188cd4daea!2zVHLGsOG7nW5nIENhbyDEkeG6s25nIEvhu7kgdGh14bqtdCAtIEPDtG5nIG5naOG7hyBCw6FjaCBLaG9hIChDVEVDSCk!5e0!3m2!1svi!2s!4v1763461795997!5m2!1svi!2s" 
                        allowfullscreen="" 
                        loading="lazy">
                    </iframe>
                </div>
                <div class="map-info">
                    <i class="fas fa-info-circle"></i>
                    <p>Trường Cao đẳng Kỹ thuật - Công nghệ Bách Khoa (CTECH)</p>
                </div>
            </div>
        </div>

        <!-- FAQ Section -->
        <div class="faq-section">
            <h2 class="faq-title">Câu hỏi thường gặp</h2>
            <div class="faq-grid">
                <div class="faq-item">
                    <div class="faq-question">
                        <i class="fas fa-question-circle"></i>
                        <span>Làm thế nào để đăng bài viết?</span>
                    </div>
                    <p class="faq-answer">Bạn cần đăng ký tài khoản và được cấp quyền biên tập viên. Sau đó có thể viết bài từ menu người dùng.</p>
                </div>
                <div class="faq-item">
                    <div class="faq-question">
                        <i class="fas fa-question-circle"></i>
                        <span>Bài viết có được duyệt trước khi đăng không?</span>
                    </div>
                    <p class="faq-answer">Có, tất cả bài viết sẽ được admin xem xét và duyệt trước khi xuất bản công khai.</p>
                </div>
                <div class="faq-item">
                    <div class="faq-question">
                        <i class="fas fa-question-circle"></i>
                        <span>Tôi có thể quảng cáo trên website không?</span>
                    </div>
                    <p class="faq-answer">Có, vui lòng liên hệ qua email hoặc hotline để được tư vấn về các gói quảng cáo.</p>
                </div>
                <div class="faq-item">
                    <div class="faq-question">
                        <i class="fas fa-question-circle"></i>
                        <span>Làm sao để báo cáo nội dung vi phạm?</span>
                    </div>
                    <p class="faq-answer">Bạn có thể gửi báo cáo qua form liên hệ hoặc email trực tiếp cho chúng tôi.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
