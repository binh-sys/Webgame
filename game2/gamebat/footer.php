<?php
require_once 'ketnoi.php';

/* ==========================================================
   Lấy dữ liệu cho Footer
   - Nếu biến đã có thì không truy vấn lại (tối ưu hiệu năng)
   - Nếu chưa có thì tự động truy vấn 3 bài viết + 3 bình luận mới
========================================================== */

// --- Bài viết mới nhất ---
if (!isset($latest_articles)) {
    $sql_latest_articles = "
        SELECT a.article_id, a.title, a.excerpt, a.featured_image, a.created_at, au.name AS author_name
        FROM articles a
        LEFT JOIN authors au ON a.author_id = au.author_id
        ORDER BY a.created_at DESC
        LIMIT 3";
    $latest_articles = $conn->query($sql_latest_articles);
}

// --- Bình luận mới nhất ---
if (!isset($latest_comments)) {
    $sql_latest_comments = "
        SELECT c.comment_id, c.content, c.created_at,
               u.display_name AS user_name,
               a.title AS article_title
        FROM comments c
        LEFT JOIN users u ON c.user_id = u.user_id
        LEFT JOIN articles a ON c.article_id = a.article_id
        ORDER BY c.created_at DESC
        LIMIT 3";
    $latest_comments = $conn->query($sql_latest_comments);
}
?>

<!-- Footer -->
<section class="footer-top-section">
    <div class="container">
        <div class="footer-top-bg">
            <img src="img/footer-top-bg.png" alt="">
        </div>
        <div class="row">

            <!-- Cột Logo -->
            <div class="col-lg-4">
                <div class="footer-logo text-white">
                    <img src="img/batvippromax.png" alt="Game BAT" style="height:100px;">
                    <p>Trang tin tức game cập nhật nhanh nhất — nơi dành cho cộng đồng game thủ Việt Nam.</p>
                </div>
            </div>

            <!-- Cột Bài Viết Mới -->
            <div class="col-lg-4 col-md-6">
                <div class="footer-widget mb-5 mb-md-0">
                    <h4 class="fw-title">Bài Viết Mới Nhất</h4>
                    <div class="latest-blog">
                        <?php if ($latest_articles && $latest_articles->num_rows > 0): ?>
                            <?php while ($art = $latest_articles->fetch_assoc()): ?>
                                <div class="lb-item">
                                    <div class="lb-thumb set-bg"
                                        data-setbg="<?php echo !empty($art['featured_image']) ? 'uploads/' . htmlspecialchars($art['featured_image']) : 'img/latest-blog/default.jpg'; ?>">
                                    </div>
                                    <div class="lb-content">
                                        <div class="lb-date">
                                            <?php echo date('d/m/Y', strtotime($art['created_at'])); ?>
                                        </div>
                                        <p><?php echo htmlspecialchars($art['excerpt']); ?></p>
                                        <a href="index.php?act=chitiet_baiviet&id=<?php echo $art['article_id']; ?>" class="lb-author">
                                            Bởi <?php echo htmlspecialchars($art['author_name'] ?? 'Admin'); ?>
                                        </a>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <p>Chưa có bài viết mới.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Cột Bình Luận -->
            <div class="col-lg-4 col-md-6">
                <div class="footer-widget">
                    <h4 class="fw-title">Bình Luận Nổi Bật</h4>
                    <div class="top-comment">
                        <?php if ($latest_comments && $latest_comments->num_rows > 0): ?>
                            <?php while ($cm = $latest_comments->fetch_assoc()): ?>
                                <div class="tc-item">
                                    <div class="tc-thumb set-bg" data-setbg="img/authors/default.jpg"></div>
                                    <div class="tc-content">
                                        <p>
                                            <a href="#"><?php echo htmlspecialchars($cm['user_name']); ?></a>
                                            <span>bình luận về</span>
                                            <?php echo htmlspecialchars($cm['article_title']); ?>
                                        </p>
                                        <div class="tc-date">
                                            <?php echo date('d/m/Y', strtotime($cm['created_at'])); ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <p>Chưa có bình luận nào.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

<footer class="footer-section">
    <div class="container">
        <ul class="footer-menu">
            <li><a href="index.php">Trang chủ</a></li>
            <li><a href="categories.php">Danh mục</a></li>
            <li><a href="review.php">Đánh giá</a></li>
            <li><a href="community.php">Cộng đồng</a></li>
            <li><a href="contact.php">Liên hệ</a></li>
        </ul>
        <p class="copyright">
            Bản quyền &copy;<script>
                document.write(new Date().getFullYear());
            </script> |
            Thiết kế bởi <i class="fa fa-heart-o" aria-hidden="true"></i>
            <a href="https://colorlib.com" target="_blank">Colorlib</a>
        </p>
    </div>
</footer>

<script src="js/jquery-3.2.1.min.js"></script>
<script src="js/bootstrap.min.js"></script>
<script src="js/owl.carousel.min.js"></script>
<script src="js/jquery.marquee.min.js"></script>
<script src="js/main.js"></script>

<style>
#chatbot-container {
  position: fixed;
  bottom: 25px;
  right: 25px;
  z-index: 9999;
  font-family: "Roboto", sans-serif;
}

#chatbot-button {
  background: #ffc107;
  color: #000;
  width: 60px;
  height: 60px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 26px;
  cursor: pointer;
  box-shadow: 0 0 10px rgba(0, 0, 0, 0.3);
  transition: 0.3s;
}

#chatbot-button:hover {
  background: #ffdb4d;
}

#chatbot-box {
  position: absolute;
  bottom: 80px;
  right: 0;
  width: 320px;
  background: #1c1c1c;
  border-radius: 10px;
  border: 1px solid #333;
  box-shadow: 0 0 10px rgba(0,0,0,0.5);
  overflow: hidden;
}

#chatbot-header {
  background: #ffc107;
  color: #000;
  padding: 10px;
  font-weight: bold;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

#chatbot-close {
  cursor: pointer;
  font-size: 20px;
}

#chatbot-messages {
  height: 280px;
  overflow-y: auto;
  background: #222;
  padding: 10px;
  color: #fff;
}

.bot-msg, .user-msg {
  margin: 8px 0;
  padding: 8px 12px;
  border-radius: 10px;
  display: inline-block;
  max-width: 85%;
  word-wrap: break-word;
}

.bot-msg {
  background: #333;
  align-self: flex-start;
}

.user-msg {
  background: #ffc107;
  color: #000;
  align-self: flex-end;
  float: right;
  clear: both;
}

#chatbot-input {
  display: flex;
  border-top: 1px solid #333;
}

#chatbot-input input {
  flex: 1;
  border: none;
  padding: 10px;
  background: #111;
  color: #fff;
}

#chatbot-input button {
  background: #ffc107;
  border: none;
  padding: 0 15px;
  cursor: pointer;
  color: #000;
}
.hidden {
  display: none;
}

</style>

<!-- Chatbot Floating Button -->
<div id="chatbot-container">
    <div id="chatbot-button">
        💬
    </div>
    <div id="chatbot-box" class="hidden">
        <div id="chatbot-header">
            <strong>Hỗ Trợ Game Bot</strong>
            <span id="chatbot-close">&times;</span>
        </div>
        <div id="chatbot-messages">
            <div class="bot-msg">Xin chào 👋<br>Tôi là <strong>GameBot</strong>! Bạn cần giúp gì nào?</div>
        </div>
        <div id="chatbot-input">
            <input type="text" id="userInput" placeholder="Nhập tin nhắn...">
            <button id="sendBtn"><i class="fa fa-paper-plane"></i></button>
        </div>
    </div>
</div>
<script>
    document.addEventListener("DOMContentLoaded", () => {
        const chatbotBtn = document.getElementById("chatbot-button");
        const chatbotBox = document.getElementById("chatbot-box");
        const closeBtn = document.getElementById("chatbot-close");
        const sendBtn = document.getElementById("sendBtn");
        const userInput = document.getElementById("userInput");
        const chatMessages = document.getElementById("chatbot-messages");

        chatbotBtn.onclick = () => chatbotBox.classList.toggle("hidden");
        closeBtn.onclick = () => chatbotBox.classList.add("hidden");

        function addMessage(content, type) {
            const msg = document.createElement("div");
            msg.classList.add(type === "bot" ? "bot-msg" : "user-msg");
            msg.textContent = content;
            chatMessages.appendChild(msg);
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        function botReply(userText) {
            let reply = "Xin lỗi, tôi chưa hiểu rõ câu hỏi của bạn.";
            userText = userText.toLowerCase();

            if (userText.includes("xin chào") || userText.includes("hello")) {
                reply = "Chào bạn 👋 Tôi là GameBot — trợ lý ảo của trang web!";
            } else if (userText.includes("liên hệ")) {
                reply = "Bạn có thể vào trang Liên hệ hoặc email: support@gameweb.vn nhé!";
            } else if (userText.includes("bài viết") || userText.includes("tin tức")) {
                reply = "Các bài viết mới nhất có ở mục Trang chủ hoặc Danh mục.";
            } else if (userText.includes("đăng nhập")) {
                reply = "Bạn có thể đăng nhập tại góc trên cùng bên phải.";
            } else if (userText.includes("game")) {
                reply = "Bạn đang muốn hỏi về game nào? Ví dụ: Liên Quân, Valorant, GTA...";
            }

            setTimeout(() => addMessage(reply, "bot"), 600);
        }

        sendBtn.onclick = () => {
            const text = userInput.value.trim();
            if (text !== "") {
                addMessage(text, "user");
                botReply(text);
                userInput.value = "";
            }
        };

        userInput.addEventListener("keypress", (e) => {
            if (e.key === "Enter") {
                sendBtn.click();
            }
        });
    });
</script>