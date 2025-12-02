<?php include 'header.php'; ?>
<?php require_once 'ketnoi.php'; ?>

<!-- Latest news section -->
<div class="latest-news-section">
    <div class="ln-title">Tin mới nhất</div>
    <div class="news-ticker">
        <div class="news-ticker-contant">
            <?php
            $sql = "SELECT title, slug, category_id FROM articles 
                    WHERE status='published' 
                    ORDER BY created_at DESC 
                    LIMIT 5";
            $result = $conn->query($sql);

            if($result->num_rows > 0){
                while($row = $result->fetch_assoc()){
                    echo '<div class="nt-item">';
                    echo '<span class="category">' . getCategoryName($row['category_id'], $conn) . '</span> ';
                    echo '<a href="article.php?slug=' . $row['slug'] . '">' . $row['title'] . '</a>';
                    echo '</div>';
                }
            }

            function getCategoryName($id, $conn){
                $sql = "SELECT name FROM categories WHERE category_id=$id";
                $res = $conn->query($sql);
                if($res && $res->num_rows > 0){
                    $cat = $res->fetch_assoc();
                    return $cat['name'];
                }
                return 'Chưa phân loại';
            }
            ?>
        </div>
    </div>
</div>
<!-- Latest news section end -->

<!-- Page info section -->
<section class="page-info-section set-bg" data-setbg="img/page-top-bg/5.jpg">
    <div class="pi-content">
        <div class="container">
            <div class="row">
                <div class="col-xl-5 col-lg-6 text-white">
                    <h2>Liên hệ chúng tôi</h2>
                    <p>Chúng tôi luôn sẵn sàng hỗ trợ bạn. Hãy gửi câu hỏi hoặc phản hồi, chúng tôi sẽ liên hệ lại trong thời gian sớm nhất.</p>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- Page info section end -->

<!-- Page section -->
<section class="page-section spad contact-page">
    <div class="container">
        <!-- Google Map -->
        <div class="map mb-4">
            <iframe 
                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3727.5356173320542!2d105.85620027387178!3d20.890759892506793!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3135b306c30f20b3%3A0xf0806e188cd4daea!2zVHLGsOG7nW5nIENhbyDEkeG6s25nIEvhu7kgdGh14bqtdCAtIEPDtG5nIG5naOG7hyBCw6FjaCBLaG9hIChDVEVDSCk!5e0!3m2!1svi!2s!4v1763461795997!5m2!1svi!2s" 
                width="100%" 
                height="450" 
                style="border:0;" 
                allowfullscreen="" 
                loading="lazy" 
                referrerpolicy="no-referrer-when-downgrade">
            </iframe>
        </div>

        <div class="row">
            <!-- Contact Info -->
            <div class="col-lg-4 mb-5 mb-lg-0">
                <h4 class="comment-title">Liên hệ chúng tôi</h4>
                <p>Chúng tôi luôn sẵn sàng hỗ trợ bạn. Hãy gửi câu hỏi hoặc phản hồi, chúng tôi sẽ liên hệ lại trong thời gian sớm nhất.</p>
                <div class="row">
                    <div class="col-md-9">
                        <ul class="contact-info-list">
                            <li><div class="cf-left">Địa chỉ</div><div class="cf-right">60 QL1A, Văn Bình, Thường Tín, Hà Nội, Việt Nam</div></li>
                            <li><div class="cf-left">Điện thoại</div><div class="cf-right">1800 6770</div></li>
                            <li><div class="cf-left">Email</div><div class="cf-right">contact@ctech.edu.vn</div></li>
                        </ul>
                    </div>
                </div>
                <div class="social-links">
                    <a href="#"><i class="fa fa-pinterest"></i></a>
                    <a href="#"><i class="fa fa-facebook"></i></a>
                    <a href="#"><i class="fa fa-twitter"></i></a>
                    <a href="#"><i class="fa fa-dribbble"></i></a>
                    <a href="#"><i class="fa fa-behance"></i></a>
                    <a href="#"><i class="fa fa-linkedin"></i></a>
                </div>
            </div>

            <!-- Contact Form -->
            <div class="col-lg-8">
                <div class="contact-form-warp">
                    <h4 class="comment-title">Gửi phản hồi</h4>
                    <form class="comment-form" action="send_contact.php" method="POST">
                        <div class="row">
                            <div class="col-md-6">
                                <input type="text" name="name" placeholder="Họ và tên" required>
                            </div>
                            <div class="col-md-6">
                                <input type="email" name="email" placeholder="Email" required>
                            </div>
                            <div class="col-lg-12">
                                <input type="text" name="subject" placeholder="Tiêu đề" required>
                                <textarea name="message" placeholder="Nội dung" required></textarea>
                                <button type="submit" class="site-btn btn-sm">Gửi</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- Page section end -->

<?php include 'footer.php'; ?>
