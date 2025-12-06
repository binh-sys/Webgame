<?php
require_once 'ketnoi.php';

// Lấy bài viết mới nhất cho footer (luôn query mới để tránh conflict)
$sql_footer_articles = "
    SELECT a.article_id, a.title, a.slug, a.featured_image, a.created_at, u.display_name AS author_name
    FROM articles a
    LEFT JOIN users u ON a.author_id = u.user_id
    WHERE a.status = 'published'
    ORDER BY a.created_at DESC
    LIMIT 3";
$footer_articles = $conn->query($sql_footer_articles);

// Lấy danh mục
$footer_categories = $conn->query("SELECT category_id, name, slug FROM categories ORDER BY name ASC LIMIT 6");
?>

<!-- Footer -->
<footer class="site-footer">
    <!-- Newsletter Section -->
    <div class="footer-newsletter">
        <div class="newsletter-container">
            <div class="newsletter-content">
                <div class="newsletter-icon">
                    <i class="fas fa-paper-plane"></i>
                </div>
                <div class="newsletter-text">
                    <h3>Đăng ký nhận tin</h3>
                    <p>Nhận thông báo về tin tức game mới nhất và các ưu đãi đặc biệt</p>
                </div>
            </div>
            <form class="newsletter-form" onsubmit="return subscribeNewsletter(event)">
                <input type="email" placeholder="Nhập email của bạn..." required>
                <button type="submit">
                    <span>Đăng ký</span>
                    <i class="fas fa-arrow-right"></i>
                </button>
            </form>
        </div>
    </div>

    <!-- Main Footer -->
    <div class="footer-main">
        <div class="footer-container">
            <div class="footer-grid">
                <!-- About Column -->
                <div class="footer-col footer-about">
                    <a href="index.php" class="footer-logo">
                        <img src="img/batvippromax.png" alt="GameBat">
                        <span>Game<span class="highlight">Bat</span></span>
                    </a>
                    <p class="footer-desc">
                        Cổng thông tin game hàng đầu Việt Nam. Cập nhật tin tức, đánh giá game và kết nối cộng đồng game thủ.
                    </p>
                    <div class="footer-social">
                        <a href="#" class="social-item facebook" title="Facebook">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="social-item youtube" title="YouTube">
                            <i class="fab fa-youtube"></i>
                        </a>
                        <a href="#" class="social-item discord" title="Discord">
                            <i class="fab fa-discord"></i>
                        </a>
                        <a href="#" class="social-item tiktok" title="TikTok">
                            <i class="fab fa-tiktok"></i>
                        </a>
                    </div>
                </div>

                <!-- Quick Links -->
                <div class="footer-col">
                    <h4 class="footer-title">Liên kết nhanh</h4>
                    <ul class="footer-links">
                        <li><a href="index.php"><i class="fas fa-chevron-right"></i> Trang chủ</a></li>
                        <li><a href="categories.php"><i class="fas fa-chevron-right"></i> Danh mục</a></li>
                        <li><a href="review.php"><i class="fas fa-chevron-right"></i> Đánh giá game</a></li>
                        <li><a href="community.php"><i class="fas fa-chevron-right"></i> Cộng đồng</a></li>
                        <li><a href="contact.php"><i class="fas fa-chevron-right"></i> Liên hệ</a></li>
                    </ul>
                </div>

                <!-- Categories -->
                <div class="footer-col">
                    <h4 class="footer-title">Danh mục</h4>
                    <ul class="footer-links">
                        <?php if ($footer_categories && $footer_categories->num_rows > 0): ?>
                            <?php while ($cat = $footer_categories->fetch_assoc()): ?>
                                <li>
                                    <a href="categories.php?cat=<?= $cat['category_id'] ?>">
                                        <i class="fas fa-chevron-right"></i> <?= htmlspecialchars($cat['name']) ?>
                                    </a>
                                </li>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </ul>
                </div>

                <!-- Latest Posts -->
                <div class="footer-col footer-posts">
                    <h4 class="footer-title">Bài viết mới</h4>
                    <div class="footer-post-list">
                        <?php if ($footer_articles && $footer_articles->num_rows > 0): ?>
                            <?php while ($art = $footer_articles->fetch_assoc()): ?>
                                <a href="article.php?slug=<?= htmlspecialchars($art['slug']) ?>" class="footer-post-item">
                                    <div class="post-thumb">
                                        <img src="<?= !empty($art['featured_image']) ? 'uploads/' . htmlspecialchars($art['featured_image']) : 'img/default-thumb.jpg' ?>" alt="">
                                    </div>
                                    <div class="post-info">
                                        <h5><?= htmlspecialchars($art['title']) ?></h5>
                                        <span><i class="far fa-calendar-alt"></i> <?= date('d/m/Y', strtotime($art['created_at'])) ?></span>
                                    </div>
                                </a>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <p class="no-posts">Chưa có bài viết mới</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer Bottom -->
    <div class="footer-bottom">
        <div class="footer-container">
            <div class="footer-bottom-content">
                <p class="copyright">
                    © <span id="currentYear"></span> GameBat. Thiết kế bởi 
                    <a href="#">GameBat Team</a>
                </p>
                <div class="footer-bottom-links">
                    <a href="#">Điều khoản sử dụng</a>
                    <a href="#">Chính sách bảo mật</a>
                    <a href="#">Sitemap</a>
                </div>
            </div>
        </div>
    </div>
</footer>

<!-- Back to Top Button -->
<button class="back-to-top" id="backToTop" title="Lên đầu trang">
    <i class="fas fa-chevron-up"></i>
</button>

<!-- Chatbot -->
<div class="chatbot-wrapper" id="chatbotWrapper">
    <button class="chatbot-toggle" id="chatbotToggle">
        <i class="fas fa-comments"></i>
        <span class="chatbot-badge">1</span>
    </button>
    <div class="chatbot-box" id="chatbotBox">
        <div class="chatbot-header">
            <div class="chatbot-header-info">
                <div class="chatbot-avatar">
                    <i class="fas fa-robot"></i>
                </div>
                <div>
                    <h4>GameBot</h4>
                    <span class="status"><i class="fas fa-circle"></i> Online</span>
                </div>
            </div>
            <button class="chatbot-close" id="chatbotClose">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="chatbot-messages" id="chatbotMessages">
            <div class="chat-message bot">
                <div class="message-avatar"><i class="fas fa-robot"></i></div>
                <div class="message-content">
                    <p>Xin chào! 👋 Tôi là <strong>GameBot</strong>. Tôi có thể giúp gì cho bạn?</p>
                    <span class="message-time">Vừa xong</span>
                </div>
            </div>
        </div>
        <div class="chatbot-suggestions">
            <button onclick="sendQuickMessage('Tin tức mới')">📰 Tin tức mới</button>
            <button onclick="sendQuickMessage('Liên hệ')">📞 Liên hệ</button>
            <button onclick="sendQuickMessage('Hướng dẫn')">❓ Hướng dẫn</button>
        </div>
        <div class="chatbot-input">
            <input type="text" id="chatInput" placeholder="Nhập tin nhắn...">
            <button id="chatSend"><i class="fas fa-paper-plane"></i></button>
        </div>
    </div>
</div>


<style>
/* ===== FOOTER STYLES ===== */
.site-footer {
    background: linear-gradient(180deg, #0a0a0f 0%, #0f0f18 100%);
    position: relative;
    overflow: hidden;
}

.site-footer::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: 
        radial-gradient(circle at 10% 20%, rgba(59, 130, 246, 0.05) 0%, transparent 40%),
        radial-gradient(circle at 90% 80%, rgba(16, 185, 129, 0.05) 0%, transparent 40%);
    pointer-events: none;
}

.footer-container {
    max-width: 1300px;
    margin: 0 auto;
    padding: 0 20px;
    position: relative;
    z-index: 10;
}

/* Newsletter */
.footer-newsletter {
    background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
    padding: 50px 0;
    border-bottom: 1px solid rgba(255, 255, 255, 0.05);
}

.newsletter-container {
    max-width: 1300px;
    margin: 0 auto;
    padding: 0 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 40px;
    flex-wrap: wrap;
}

.newsletter-content {
    display: flex;
    align-items: center;
    gap: 20px;
}

.newsletter-icon {
    width: 70px;
    height: 70px;
    background: linear-gradient(135deg, #3b82f6, #2563eb);
    border-radius: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 28px;
    color: #fff;
}

.newsletter-text h3 {
    color: #fff;
    font-size: 22px;
    font-weight: 700;
    margin-bottom: 5px;
}

.newsletter-text p {
    color: #94a3b8;
    font-size: 14px;
    margin: 0;
}

.newsletter-form {
    display: flex;
    gap: 10px;
    flex: 1;
    max-width: 450px;
}

.newsletter-form input {
    flex: 1;
    padding: 16px 24px;
    background: rgba(255, 255, 255, 0.05);
    border: 2px solid rgba(255, 255, 255, 0.1);
    border-radius: 14px;
    color: #fff;
    font-size: 15px;
    outline: none;
    transition: all 0.3s;
}

.newsletter-form input:focus {
    border-color: #3b82f6;
    background: rgba(255, 255, 255, 0.08);
}

.newsletter-form input::placeholder {
    color: #64748b;
}

.newsletter-form button {
    padding: 16px 28px;
    background: linear-gradient(135deg, #10b981, #059669);
    border: none;
    border-radius: 14px;
    color: #fff;
    font-size: 15px;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 10px;
    transition: all 0.3s;
    white-space: nowrap;
}

.newsletter-form button:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 30px rgba(16, 185, 129, 0.3);
}

/* Main Footer */
.footer-main {
    padding: 70px 0 50px;
}

.footer-grid {
    display: grid;
    grid-template-columns: 1.5fr 1fr 1fr 1.5fr;
    gap: 50px;
}

/* Footer About */
.footer-logo {
    display: flex;
    align-items: center;
    gap: 12px;
    text-decoration: none;
    margin-bottom: 20px;
}

.footer-logo img {
    height: 50px;
}

.footer-logo span {
    font-size: 24px;
    font-weight: 800;
    color: #fff;
}

.footer-logo .highlight {
    color: #3b82f6;
}

.footer-desc {
    color: #94a3b8;
    font-size: 14px;
    line-height: 1.8;
    margin-bottom: 25px;
}

.footer-social {
    display: flex;
    gap: 12px;
}

.social-item {
    width: 42px;
    height: 42px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 12px;
    color: #94a3b8;
    font-size: 18px;
    text-decoration: none;
    transition: all 0.3s;
}

.social-item:hover {
    transform: translateY(-3px);
}

.social-item.facebook:hover { background: #1877f2; color: #fff; border-color: #1877f2; }
.social-item.youtube:hover { background: #ff0000; color: #fff; border-color: #ff0000; }
.social-item.discord:hover { background: #5865f2; color: #fff; border-color: #5865f2; }
.social-item.tiktok:hover { background: #000; color: #fff; border-color: #fff; }

/* Footer Columns */
.footer-title {
    color: #fff;
    font-size: 18px;
    font-weight: 700;
    margin-bottom: 25px;
    position: relative;
    padding-bottom: 12px;
}

.footer-title::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 40px;
    height: 3px;
    background: linear-gradient(135deg, #3b82f6, #2563eb);
    border-radius: 3px;
}

.footer-links {
    list-style: none;
    padding: 0;
    margin: 0;
}

.footer-links li {
    margin-bottom: 12px;
}

.footer-links a {
    color: #94a3b8;
    text-decoration: none;
    font-size: 14px;
    display: flex;
    align-items: center;
    gap: 10px;
    transition: all 0.3s;
}

.footer-links a i {
    font-size: 10px;
    color: #3b82f6;
    transition: transform 0.3s;
}

.footer-links a:hover {
    color: #fff;
    padding-left: 5px;
}

.footer-links a:hover i {
    transform: translateX(3px);
}

/* Footer Posts */
.footer-post-list {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.footer-post-item {
    display: flex;
    gap: 15px;
    text-decoration: none;
    padding: 12px;
    background: rgba(255, 255, 255, 0.02);
    border-radius: 12px;
    transition: all 0.3s;
}

.footer-post-item:hover {
    background: rgba(255, 255, 255, 0.05);
    transform: translateX(5px);
}

.post-thumb {
    width: 70px;
    height: 55px;
    border-radius: 8px;
    overflow: hidden;
    flex-shrink: 0;
}

.post-thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.post-info h5 {
    color: #e2e8f0;
    font-size: 13px;
    font-weight: 500;
    line-height: 1.4;
    margin-bottom: 5px;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.post-info span {
    color: #64748b;
    font-size: 12px;
}

.post-info span i {
    margin-right: 5px;
    color: #3b82f6;
}

.no-posts {
    color: #64748b;
    font-size: 14px;
}

/* Footer Bottom */
.footer-bottom {
    background: rgba(0, 0, 0, 0.3);
    padding: 25px 0;
    border-top: 1px solid rgba(255, 255, 255, 0.05);
}

.footer-bottom-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 15px;
}

.copyright {
    color: #64748b;
    font-size: 14px;
    margin: 0;
}

.copyright a {
    color: #3b82f6;
    text-decoration: none;
}

.copyright a:hover {
    text-decoration: underline;
}

.footer-bottom-links {
    display: flex;
    gap: 25px;
}

.footer-bottom-links a {
    color: #64748b;
    font-size: 14px;
    text-decoration: none;
    transition: color 0.3s;
}

.footer-bottom-links a:hover {
    color: #fff;
}


/* Back to Top */
.back-to-top {
    position: fixed;
    bottom: 100px;
    right: 25px;
    width: 50px;
    height: 50px;
    background: linear-gradient(135deg, #3b82f6, #2563eb);
    border: none;
    border-radius: 14px;
    color: #fff;
    font-size: 18px;
    cursor: pointer;
    opacity: 0;
    visibility: hidden;
    transform: translateY(20px);
    transition: all 0.3s;
    z-index: 999;
    box-shadow: 0 5px 20px rgba(59, 130, 246, 0.3);
}

.back-to-top.show {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}

.back-to-top:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(59, 130, 246, 0.4);
}

/* Chatbot */
.chatbot-wrapper {
    position: fixed;
    bottom: 25px;
    right: 25px;
    z-index: 1000;
}

.chatbot-toggle {
    width: 60px;
    height: 60px;
    background: linear-gradient(135deg, #10b981, #059669);
    border: none;
    border-radius: 50%;
    color: #fff;
    font-size: 24px;
    cursor: pointer;
    box-shadow: 0 5px 25px rgba(16, 185, 129, 0.4);
    transition: all 0.3s;
    position: relative;
}

.chatbot-toggle:hover {
    transform: scale(1.1);
}

.chatbot-badge {
    position: absolute;
    top: -5px;
    right: -5px;
    width: 22px;
    height: 22px;
    background: #ef4444;
    border-radius: 50%;
    font-size: 12px;
    font-weight: 600;
    display: flex;
    align-items: center;
    justify-content: center;
}

.chatbot-box {
    position: absolute;
    bottom: 75px;
    right: 0;
    width: 360px;
    background: #12121a;
    border-radius: 20px;
    border: 1px solid rgba(255, 255, 255, 0.1);
    box-shadow: 0 20px 50px rgba(0, 0, 0, 0.5);
    overflow: hidden;
    opacity: 0;
    visibility: hidden;
    transform: translateY(20px) scale(0.95);
    transition: all 0.3s;
}

.chatbot-box.active {
    opacity: 1;
    visibility: visible;
    transform: translateY(0) scale(1);
}

.chatbot-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 18px 20px;
    background: linear-gradient(135deg, #10b981, #059669);
}

.chatbot-header-info {
    display: flex;
    align-items: center;
    gap: 12px;
}

.chatbot-avatar {
    width: 42px;
    height: 42px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    color: #fff;
}

.chatbot-header h4 {
    color: #fff;
    font-size: 16px;
    font-weight: 600;
    margin: 0;
}

.chatbot-header .status {
    color: rgba(255, 255, 255, 0.8);
    font-size: 12px;
}

.chatbot-header .status i {
    font-size: 8px;
    margin-right: 5px;
    color: #a7f3d0;
}

.chatbot-close {
    background: rgba(255, 255, 255, 0.2);
    border: none;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    color: #fff;
    cursor: pointer;
    transition: all 0.3s;
}

.chatbot-close:hover {
    background: rgba(255, 255, 255, 0.3);
}

.chatbot-messages {
    height: 300px;
    overflow-y: auto;
    padding: 20px;
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.chat-message {
    display: flex;
    gap: 10px;
    max-width: 85%;
}

.chat-message.bot {
    align-self: flex-start;
}

.chat-message.user {
    align-self: flex-end;
    flex-direction: row-reverse;
}

.message-avatar {
    width: 32px;
    height: 32px;
    background: rgba(16, 185, 129, 0.2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    color: #10b981;
    flex-shrink: 0;
}

.chat-message.user .message-avatar {
    background: rgba(59, 130, 246, 0.2);
    color: #3b82f6;
}

.message-content {
    background: rgba(255, 255, 255, 0.05);
    padding: 12px 16px;
    border-radius: 16px;
    border-top-left-radius: 4px;
}

.chat-message.user .message-content {
    background: rgba(59, 130, 246, 0.2);
    border-radius: 16px;
    border-top-right-radius: 4px;
}

.message-content p {
    color: #e2e8f0;
    font-size: 14px;
    line-height: 1.5;
    margin: 0;
}

.message-time {
    display: block;
    color: #64748b;
    font-size: 11px;
    margin-top: 5px;
}

.chatbot-suggestions {
    padding: 10px 20px;
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    border-top: 1px solid rgba(255, 255, 255, 0.05);
}

.chatbot-suggestions button {
    padding: 8px 14px;
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 20px;
    color: #94a3b8;
    font-size: 12px;
    cursor: pointer;
    transition: all 0.3s;
}

.chatbot-suggestions button:hover {
    background: rgba(16, 185, 129, 0.2);
    border-color: rgba(16, 185, 129, 0.3);
    color: #10b981;
}

.chatbot-input {
    display: flex;
    padding: 15px 20px;
    gap: 10px;
    border-top: 1px solid rgba(255, 255, 255, 0.05);
}

.chatbot-input input {
    flex: 1;
    padding: 12px 18px;
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 25px;
    color: #fff;
    font-size: 14px;
    outline: none;
    transition: all 0.3s;
}

.chatbot-input input:focus {
    border-color: #10b981;
}

.chatbot-input input::placeholder {
    color: #64748b;
}

.chatbot-input button {
    width: 45px;
    height: 45px;
    background: linear-gradient(135deg, #10b981, #059669);
    border: none;
    border-radius: 50%;
    color: #fff;
    cursor: pointer;
    transition: all 0.3s;
}

.chatbot-input button:hover {
    transform: scale(1.05);
}

/* Responsive */
@media (max-width: 1100px) {
    .footer-grid {
        grid-template-columns: 1fr 1fr;
        gap: 40px;
    }
}

@media (max-width: 768px) {
    .newsletter-container {
        flex-direction: column;
        text-align: center;
    }
    
    .newsletter-content {
        flex-direction: column;
    }
    
    .newsletter-form {
        width: 100%;
        max-width: 100%;
        flex-direction: column;
    }
    
    .footer-grid {
        grid-template-columns: 1fr;
        gap: 35px;
    }
    
    .footer-about {
        text-align: center;
    }
    
    .footer-logo {
        justify-content: center;
    }
    
    .footer-social {
        justify-content: center;
    }
    
    .footer-bottom-content {
        flex-direction: column;
        text-align: center;
    }
    
    .chatbot-box {
        width: calc(100vw - 40px);
        right: -5px;
    }
}
</style>


<script src="js/jquery-3.2.1.min.js"></script>
<script src="js/bootstrap.min.js"></script>
<script src="js/owl.carousel.min.js"></script>
<script src="js/jquery.marquee.min.js"></script>
<script src="js/main.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Set current year
    document.getElementById('currentYear').textContent = new Date().getFullYear();
    
    // Back to top button
    const backToTop = document.getElementById('backToTop');
    
    window.addEventListener('scroll', function() {
        if (window.scrollY > 300) {
            backToTop.classList.add('show');
        } else {
            backToTop.classList.remove('show');
        }
    });
    
    backToTop.addEventListener('click', function() {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });
    
    // Chatbot
    const chatbotToggle = document.getElementById('chatbotToggle');
    const chatbotBox = document.getElementById('chatbotBox');
    const chatbotClose = document.getElementById('chatbotClose');
    const chatInput = document.getElementById('chatInput');
    const chatSend = document.getElementById('chatSend');
    const chatMessages = document.getElementById('chatbotMessages');
    
    chatbotToggle.addEventListener('click', function() {
        chatbotBox.classList.toggle('active');
        if (chatbotBox.classList.contains('active')) {
            document.querySelector('.chatbot-badge').style.display = 'none';
        }
    });
    
    chatbotClose.addEventListener('click', function() {
        chatbotBox.classList.remove('active');
    });
    
    function addMessage(text, type) {
        const time = new Date().toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' });
        const icon = type === 'bot' ? 'fa-robot' : 'fa-user';
        
        const messageHTML = `
            <div class="chat-message ${type}">
                <div class="message-avatar"><i class="fas ${icon}"></i></div>
                <div class="message-content">
                    <p>${text}</p>
                    <span class="message-time">${time}</span>
                </div>
            </div>
        `;
        
        chatMessages.insertAdjacentHTML('beforeend', messageHTML);
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }
    
    function getBotReply(text) {
        text = text.toLowerCase();
        
        if (text.includes('xin chào') || text.includes('hello') || text.includes('hi')) {
            return 'Chào bạn! 👋 Rất vui được hỗ trợ bạn. Bạn cần giúp gì?';
        } else if (text.includes('tin tức') || text.includes('bài viết')) {
            return 'Bạn có thể xem tin tức mới nhất tại <a href="index.php" style="color:#10b981">Trang chủ</a> hoặc <a href="categories.php" style="color:#10b981">Danh mục</a> nhé!';
        } else if (text.includes('liên hệ') || text.includes('contact')) {
            return 'Bạn có thể liên hệ qua email: <strong>support@gamebat.vn</strong> hoặc truy cập <a href="contact.php" style="color:#10b981">trang Liên hệ</a>.';
        } else if (text.includes('đăng nhập') || text.includes('đăng ký')) {
            return 'Bạn có thể <a href="login.php" style="color:#10b981">Đăng nhập</a> hoặc <a href="register.php" style="color:#10b981">Đăng ký</a> tài khoản mới.';
        } else if (text.includes('hướng dẫn') || text.includes('help')) {
            return 'Tôi có thể giúp bạn: <br>• Tìm tin tức game<br>• Hướng dẫn đăng ký/đăng nhập<br>• Thông tin liên hệ<br>• Và nhiều hơn nữa!';
        } else if (text.includes('game')) {
            return 'Bạn muốn tìm hiểu về game nào? Hãy truy cập <a href="review.php" style="color:#10b981">Đánh giá game</a> để xem các bài review chi tiết!';
        } else if (text.includes('cảm ơn') || text.includes('thank')) {
            return 'Không có gì! 😊 Rất vui được giúp đỡ bạn. Chúc bạn có trải nghiệm tuyệt vời!';
        }
        
        return 'Xin lỗi, tôi chưa hiểu rõ câu hỏi của bạn. Bạn có thể hỏi về: tin tức, đăng nhập, liên hệ, hoặc hướng dẫn sử dụng.';
    }
    
    function sendMessage() {
        const text = chatInput.value.trim();
        if (text === '') return;
        
        addMessage(text, 'user');
        chatInput.value = '';
        
        setTimeout(function() {
            const reply = getBotReply(text);
            addMessage(reply, 'bot');
        }, 800);
    }
    
    chatSend.addEventListener('click', sendMessage);
    chatInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') sendMessage();
    });
    
    // Quick message function
    window.sendQuickMessage = function(text) {
        chatInput.value = text;
        sendMessage();
    };
});

// Newsletter subscription
function subscribeNewsletter(e) {
    e.preventDefault();
    const email = e.target.querySelector('input').value;
    alert('Cảm ơn bạn đã đăng ký! Email: ' + email);
    e.target.reset();
    return false;
}
</script>

</body>
</html>
