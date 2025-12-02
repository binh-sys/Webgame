<?php
// Hiển thị lỗi (chỉ khi DEV)
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
// File này chứa biến $conn kết nối CSDL
include 'ketnoi.php'; 

// =====================
// 🔹 HÀM XỬ LÝ LƯỢT THÍCH/KHÔNG THÍCH (AJAX Endpoint)
// =====================

/**
 * Lấy số lượt Like và Dislike mới nhất của một bình luận.
 */
function get_reaction_counts($conn, $comment_id) {
    // Lấy số lượt Like
    $stmt_l = $conn->prepare("SELECT COUNT(*) as total FROM comment_likes WHERE comment_id = ?");
    $stmt_l->bind_param("i", $comment_id);
    $stmt_l->execute();
    $likes = $stmt_l->get_result()->fetch_assoc()['total'];
    $stmt_l->close();

    // Lấy số lượt Dislike
    $stmt_d = $conn->prepare("SELECT COUNT(*) as total FROM comment_dislikes WHERE comment_id = ?");
    $stmt_d->bind_param("i", $comment_id);
    $stmt_d->execute();
    $dislikes = $stmt_d->get_result()->fetch_assoc()['total'];
    $stmt_d->close();
    
    return ['total_likes' => $likes, 'total_dislikes' => $dislikes];
}

// XỬ LÝ THÍCH (LIKE)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'toggle_like') {
    header('Content-Type: application/json');
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'Bạn cần đăng nhập.']);
        die();
    }
    $comment_id = intval($_POST['comment_id']);
    $user_id = $_SESSION['user_id'];
    
    // 1. Kiểm tra đã thích chưa
    $stmt = $conn->prepare("SELECT like_id FROM comment_likes WHERE comment_id = ? AND user_id = ? LIMIT 1");
    $stmt->bind_param("ii", $comment_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
    $is_liked = false;

    if ($result->num_rows > 0) {
        // Đã thích -> Bỏ thích
        $stmt = $conn->prepare("DELETE FROM comment_likes WHERE comment_id = ? AND user_id = ?");
        $stmt->bind_param("ii", $comment_id, $user_id);
        $stmt->execute();
        $stmt->close();
    } else {
        // Chưa thích -> Thêm thích
        $stmt = $conn->prepare("INSERT INTO comment_likes (comment_id, user_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $comment_id, $user_id);
        $stmt->execute();
        $stmt->close();
        $is_liked = true;

        // LOẠI TRỪ LẪN NHAU: Xóa Dislike nếu có
        $stmt_del = $conn->prepare("DELETE FROM comment_dislikes WHERE comment_id = ? AND user_id = ?");
        $stmt_del->bind_param("ii", $comment_id, $user_id);
        $stmt_del->execute();
        $stmt_del->close();
    }

    $counts = get_reaction_counts($conn, $comment_id);
    echo json_encode([
        'success' => true, 
        'is_liked' => $is_liked, 
        'total_likes' => $counts['total_likes'],
        'total_dislikes' => $counts['total_dislikes']
    ]);
    die();
}

// XỬ LÝ KHÔNG THÍCH (DISLIKE)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'toggle_dislike') {
    header('Content-Type: application/json');
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'Bạn cần đăng nhập.']);
        die();
    }
    $comment_id = intval($_POST['comment_id']);
    $user_id = $_SESSION['user_id'];
    
    // 1. Kiểm tra đã Dislike chưa
    $stmt = $conn->prepare("SELECT dislike_id FROM comment_dislikes WHERE comment_id = ? AND user_id = ? LIMIT 1");
    $stmt->bind_param("ii", $comment_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
    $is_disliked = false;

    if ($result->num_rows > 0) {
        // Đã Dislike -> Bỏ Dislike
        $stmt = $conn->prepare("DELETE FROM comment_dislikes WHERE comment_id = ? AND user_id = ?");
        $stmt->bind_param("ii", $comment_id, $user_id);
        $stmt->execute();
        $stmt->close();
    } else {
        // Chưa Dislike -> Thêm Dislike
        $stmt = $conn->prepare("INSERT INTO comment_dislikes (comment_id, user_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $comment_id, $user_id);
        $stmt->execute();
        $stmt->close();
        $is_disliked = true;

        // LOẠI TRỪ LẪN NHAU: Xóa Like nếu có
        $stmt_del = $conn->prepare("DELETE FROM comment_likes WHERE comment_id = ? AND user_id = ?");
        $stmt_del->bind_param("ii", $comment_id, $user_id);
        $stmt_del->execute();
        $stmt_del->close();
    }

    $counts = get_reaction_counts($conn, $comment_id);
    echo json_encode([
        'success' => true, 
        'is_disliked' => $is_disliked, 
        'total_likes' => $counts['total_likes'],
        'total_dislikes' => $counts['total_dislikes']
    ]);
    die();
}
// END AJAX HANDLING

// =====================
// 🔹 LẤY THÔNG TIN BÀI VIẾT VÀ TĂNG LƯỢT XEM
// =====================
$article = null;

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $stmt = $conn->prepare("
        SELECT a.*, c.name AS category_name, au.name AS author_name, au.avatar AS author_avatar
        FROM articles a LEFT JOIN categories c ON a.category_id = c.category_id
        LEFT JOIN authors au ON a.author_id = au.author_id
        WHERE a.article_id = ? AND a.status = 'published' LIMIT 1
    ");
    $stmt->bind_param("i", $_GET['id']);
} elseif (isset($_GET['slug'])) {
    $stmt = $conn->prepare("
        SELECT a.*, c.name AS category_name, au.name AS author_name, au.avatar AS author_avatar
        FROM articles a LEFT JOIN categories c ON a.category_id = c.category_id
        LEFT JOIN authors au ON a.author_id = au.author_id
        WHERE a.slug = ? AND a.status = 'published' LIMIT 1
    ");
    $stmt->bind_param("s", $_GET['slug']);
} else {
    die("Bài viết không tồn tại hoặc ID/slug không hợp lệ.");
}

$stmt->execute();
$result = $stmt->get_result();
if (!$result || $result->num_rows == 0) {
    die("Không tìm thấy bài viết hoặc bài viết chưa được xuất bản.");
}
$article = $result->fetch_assoc();
$id = $article['article_id'];
$stmt->close();

// Tăng lượt xem
$update = $conn->prepare("UPDATE articles SET views = views + 1 WHERE article_id = ?");
$update->bind_param("i", $id);
$update->execute();
$update->close();

$image = !empty($article['featured_image']) ? 'img/' . $article['featured_image'] : 'img/default.jpg';

// =====================
// 🔹 GỬI BÌNH LUẬN HOẶC TRẢ LỜI
// =====================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment'])) {
    if (!isset($_SESSION['user_id'])) {
        echo "<script>alert('Bạn cần đăng nhập để bình luận!'); window.location='login.php';</script>";
        exit;
    }

    $content = trim($_POST['comment']);
    $parent_id = isset($_POST['parent_id']) ? intval($_POST['parent_id']) : NULL;
    
    if ($content !== '') {
        $user_id = $_SESSION['user_id'];
        
        if ($parent_id > 0) {
             // Trả lời bình luận (có parent_id)
             $stmt = $conn->prepare("INSERT INTO comments (article_id, user_id, content, parent_id, created_at) VALUES (?, ?, ?, ?, NOW())");
             $stmt->bind_param("iisi", $id, $user_id, $content, $parent_id);
        } else {
            // Bình luận gốc (không có parent_id)
            $stmt = $conn->prepare("INSERT INTO comments (article_id, user_id, content, created_at) VALUES (?, ?, ?, NOW())");
            $stmt->bind_param("iis", $id, $user_id, $content);
        }

        $stmt->execute();
        $stmt->close();

        // Chuyển hướng để tránh gửi lại form
        header("Location: article.php?id=$id#comments");
        exit;
    }
}

// =====================
// 🔹 LẤY DANH SÁCH BÌNH LUẬN, LIKES VÀ DISLIKES
// =====================
$stmt = $conn->prepare("
    SELECT 
        c.*, 
        u.display_name, 
        u.avatar, /* ⬅️ ĐÃ THÊM CỘT AVATAR */
        (SELECT COUNT(*) FROM comment_likes cl WHERE cl.comment_id = c.comment_id) as total_likes,
        (SELECT COUNT(*) FROM comment_dislikes cd WHERE cd.comment_id = c.comment_id) as total_dislikes,
        (SELECT COUNT(*) FROM comment_likes cl WHERE cl.comment_id = c.comment_id AND cl.user_id = ?) as user_liked,
        (SELECT COUNT(*) FROM comment_dislikes cd WHERE cd.comment_id = c.comment_id AND cd.user_id = ?) as user_disliked
    FROM comments c
    JOIN users u ON c.user_id = u.user_id
    WHERE c.article_id = ?
    ORDER BY c.created_at ASC
");
$current_user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
// Bind current_user_id hai lần cho user_liked và user_disliked
$stmt->bind_param("iii", $current_user_id, $current_user_id, $id); 
$stmt->execute();
$allComments = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// =====================
// 🔹 HÀM PHÂN CẤP BÌNH LUẬN (Hierarchy)
// =====================
function build_comments_tree($comments, $parent_id = NULL) {
    $tree = [];
    foreach ($comments as $comment) {
        if ($comment['parent_id'] == $parent_id) {
            $comment['replies'] = build_comments_tree($comments, $comment['comment_id']);
            $tree[] = $comment;
        }
    }
    return $tree;
}

$comments_tree = build_comments_tree($allComments);

// =====================
// 🔹 HÀM HIỂN THỊ BÌNH LUẬN (Recursive Display)
// =====================
function display_comment($comment, $is_logged_in, $article_id) {
    $is_reply = $comment['parent_id'] !== NULL;
    $dislike_color = $comment['user_disliked'] > 0 ? '#dc3545' : '#bbb'; 
    
    // Xử lý đường dẫn Avatar
    $avatar_file = !empty($comment['avatar']) ? $comment['avatar'] : 'default.png'; // Giả định avatar mặc định là default.png
    $avatar_path = 'img/avatars/' . htmlspecialchars($avatar_file); 
    ?>
    <div class="comment-box <?php echo $is_reply ? 'reply-box' : 'main-comment'; ?>" 
         data-comment-id="<?php echo $comment['comment_id']; ?>" 
         id="comment-<?php echo $comment['comment_id']; ?>">
        
        <div class="d-flex mb-2">
            <img src="<?php echo $avatar_path; ?>" class="comment-avatar me-3" alt="Avatar">
            
            <div class="comment-content-wrapper w-100">
                <div class="d-flex justify-content-between align-items-center">
                    <strong><?php echo htmlspecialchars($comment['display_name']); ?></strong>
                    <small><?php echo date('d/m/Y H:i', strtotime($comment['created_at'])); ?></small>
                </div>
                
                <div class="comment-content mt-1 mb-2"><?php echo nl2br(htmlspecialchars($comment['content'])); ?></div>
                
                <div class="comment-actions d-flex gap-3">
                    <span class="like-action <?php echo $comment['user_liked'] > 0 ? 'liked' : ''; ?>" 
                          data-id="<?php echo $comment['comment_id']; ?>" 
                          style="cursor: pointer; color: <?php echo $comment['user_liked'] > 0 ? '#ffc107' : '#bbb'; ?>;">
                        <i class="fa fa-thumbs-up"></i> 
                        <span class="like-count"><?php echo intval($comment['total_likes']); ?></span>
                    </span>

                    <span class="dislike-action <?php echo $comment['user_disliked'] > 0 ? 'disliked' : ''; ?>" 
                          data-id="<?php echo $comment['comment_id']; ?>" 
                          style="cursor: pointer; color: <?php echo $dislike_color; ?>;">
                        <i class="fa fa-thumbs-down"></i> 
                        <span class="dislike-count"><?php echo intval($comment['total_dislikes']); ?></span>
                    </span>

                    <?php if ($is_logged_in): ?>
                        <span class="reply-toggle" data-id="<?php echo $comment['comment_id']; ?>" style="cursor: pointer; color: #bbb;">
                            <i class="fa fa-reply"></i> Trả lời
                        </span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div id="reply-form-<?php echo $comment['comment_id']; ?>" class="reply-form mt-3" style="display:none;">
            <form method="POST">
                <input type="hidden" name="parent_id" value="<?php echo $comment['comment_id']; ?>">
                <textarea name="comment" rows="2" class="form-control mb-2" placeholder="Trả lời <?php echo htmlspecialchars($comment['display_name']); ?>..." required></textarea>
                <button type="submit" class="btn btn-sm btn-warning">Gửi Trả lời</button>
            </form>
        </div>
    </div>
    
    <?php if (!empty($comment['replies'])): ?>
        <div class="comment-replies"> 
            <?php foreach ($comment['replies'] as $reply) {
                display_comment($reply, $is_logged_in, $article_id);
            } ?>
        </div>
    <?php endif;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="description" content="Chi tiết bài viết - Web Tin Tức Game">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($article['title']); ?></title>
    <link href="img/bat.png" rel="shortcut icon" />
    <link rel="stylesheet" href="css/bootstrap.min.css" />
    <link rel="stylesheet" href="css/font-awesome.min.css" />
    <link rel="stylesheet" href="css/style.css" />
    <style>
        /* ========================= */
        /* CSS CHUNG BÌNH LUẬN */
        /* ========================= */
        .comment-box {background: #1c1c1c; border: 1px solid #333; padding: 12px 15px; border-radius: 8px; margin-bottom: 15px; color: #fff; position: relative;}
        .comment-box strong {color: #ffc107;}
        .comment-box small {color: #ccc;}
        .comment-actions {font-size: 0.9em; display: flex; gap: 15px; margin-top: 5px;}
        
        /* Reaction styles */
        .like-action, .dislike-action, .reply-toggle {cursor: pointer; transition: color 0.2s; user-select: none;}
        .like-action.liked {color: #ffc107 !important;}
        .dislike-action.disliked {color: #dc3545 !important;} /* Màu đỏ cho Dislike */
        
        /* ========================= */
        /* CSS AVATAR MỚI */
        /* ========================= */
        .comment-avatar {
            width: 40px; 
            height: 40px;
            border-radius: 50%; 
            object-fit: cover; 
            flex-shrink: 0; 
            border: 2px solid #ffc107; 
        }
        
        /* Điều chỉnh lại layout của các box */
        .comment-box .d-flex.mb-2 { 
            align-items: flex-start;
        }

        /* ========================= */
        /* CSS ĐA TẦNG (REPLIES) */
        /* ========================= */
        .comment-replies {
            /* Tăng độ thụt lề cho mỗi cấp độ */
            padding-left: 30px; 
            border-left: 3px solid #333; /* Thêm đường kẻ dọc để phân biệt */
            margin-top: 5px;
        }
        .reply-box {
            background: #222222; 
            border: 1px solid #444;
            padding: 10px 15px;
            margin-bottom: 10px;
        }
        
        /* ========================= */
        /* CSS FORMS */
        /* ========================= */
        .reply-form textarea, .comment-form textarea {
            background: #111; 
            color: #fff; 
            border: 1px solid #333; 
            border-radius: 6px; 
            resize: none;
        }
        .reply-form button {
             background: #ffc107; border: none; color: #000; font-weight: 600; border-radius: 4px; padding: 3px 15px;
        }
        
        .comment-form button {background: #ffc107; border: none; color: #000; font-weight: 600; border-radius: 4px; padding: 6px 20px;}
        .comment-form button:hover {background: #e0a800;}
        .comments-section h4 {color: #ffc107;}
        .text-secondary {color: #bbb !important;}
        
    </style>
</head>

<body>
    <?php include 'header.php'; ?>

    <section class="page-info-section set-bg" data-setbg="img/page-top-bg/1.jpg">
        <div class="pi-content">
            <div class="container text-white">
                <h2><?php echo htmlspecialchars($article['title']); ?></h2>
                <p>Danh mục: <?php echo htmlspecialchars($article['category_name']); ?> | 
                Tác giả: <?php echo htmlspecialchars($article['author_name']); ?> |
                Ngày đăng: <?php echo date('d/m/Y', strtotime($article['created_at'])); ?></p>
            </div>
        </div>
    </section>

    <section class="page-section spad">
        <div class="container">
            <div class="row">
                <div class="col-lg-8">
                    <div class="blog-post single-post">
                        <div class="post-thumb set-bg" data-setbg="<?php echo $image; ?>"></div>
                        <div class="post-content">
                            <h3><?php echo htmlspecialchars($article['title']); ?></h3>
                            <ul class="post-meta">
                                <li><i class="fa fa-user"></i> <?php echo htmlspecialchars($article['author_name']); ?></li>
                                <li><i class="fa fa-calendar"></i> <?php echo date('d/m/Y', strtotime($article['created_at'])); ?></li>
                                <li><i class="fa fa-eye"></i> <?php echo intval($article['views']); ?> lượt xem</li>
                            </ul>
                            <div class="post-text"><?php echo $article['content']; ?></div>
                        </div>
                    </div>

                    <div class="comments-section mt-5" id="comments">
                        <h4 class="mb-4 text-warning">💬 Bình luận</h4>
                        <div class="comment-list mb-4">
                            <?php if (!empty($comments_tree)): ?>
                                <?php foreach ($comments_tree as $comment): ?>
                                    <?php display_comment($comment, isset($_SESSION['user_id']), $id); ?>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-secondary">Chưa có bình luận nào. Hãy là người đầu tiên!</p>
                            <?php endif; ?>
                        </div>

                        <div class="comment-form">
                            <?php if (isset($_SESSION['user_id'])): ?>
                                <form method="POST">
                                    <h5 class="text-white mb-3">Thêm Bình Luận Mới:</h5>
                                    <input type="hidden" name="parent_id" value="">
                                    <textarea name="comment" rows="3" class="form-control mb-3" placeholder="Nhập bình luận của bạn..." required></textarea>
                                    <button type="submit">Gửi bình luận</button>
                                </form>
                            <?php else: ?>
                                <p class="text-secondary">
                                    Bạn cần <a href="login.php" class="text-warning">đăng nhập</a> để bình luận.
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="related-posts mt-5">
                            <h4>Bài viết liên quan</h4>
                            <div class="row">
                                <?php
                                $cat_id = intval($article['category_id']);
                                $stmt = $conn->prepare("
                                    SELECT article_id, title, featured_image
                                    FROM articles 
                                    WHERE category_id = ? AND article_id != ? AND status = 'published'
                                    ORDER BY created_at DESC LIMIT 3
                                ");
                                $stmt->bind_param("ii", $cat_id, $id);
                                $stmt->execute();
                                $related = $stmt->get_result();
                                $stmt->close();
                                
                                if ($related && $related->num_rows > 0):
                                    while ($r = $related->fetch_assoc()):
                                        $r_img = !empty($r['featured_image']) ? 'img/' . $r['featured_image'] : 'img/default.jpg'; ?>
                                        <div class="col-md-4">
                                            <div class="recent-game-item">
                                                <div class="rgi-thumb set-bg" data-setbg="<?php echo $r_img; ?>"></div>
                                                <div class="rgi-content">
                                                    <h5><a href="article.php?id=<?php echo intval($r['article_id']); ?>"><?php echo htmlspecialchars($r['title']); ?></a></h5>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endwhile; 
                                else: ?>
                                    <p>Chưa có bài viết liên quan.</p>
                                <?php endif; ?>
                            </div>
                    </div>
                </div>

                <div class="col-lg-4 col-md-7 sidebar pt-5 pt-lg-0">
                    <div class="widget-item">
                            <h4 class="widget-title">Bài viết mới</h4>
                            <div class="latest-blog">
                                 <?php
                                 $latest = $conn->query("
                                    SELECT article_id, title, featured_image, created_at 
                                    FROM articles WHERE status = 'published' ORDER BY created_at DESC LIMIT 3
                                 ");
                                 if ($latest && $latest->num_rows > 0):
                                     while ($l = $latest->fetch_assoc()):
                                         $l_img = !empty($l['featured_image']) ? 'img/' . $l['featured_image'] : 'img/default.jpg'; ?>
                                         <div class="lb-item">
                                             <div class="lb-thumb set-bg" data-setbg="<?php echo $l_img; ?>"></div>
                                             <div class="lb-content">
                                                 <div class="lb-date"><?php echo date("d/m/Y", strtotime($l['created_at'])); ?></div>
                                                 <p><a href="article.php?id=<?php echo intval($l['article_id']); ?>"><?php echo htmlspecialchars($l['title']); ?></a></p>
                                             </div>
                                         </div>
                                     <?php endwhile; endif; ?>
                            </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include 'footer.php'; ?>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    $(document).ready(function() {
        
        // =========================
        // 1. Logic Toggle Reply Form
        // =========================
        $('.reply-toggle').on('click', function() {
            var commentId = $(this).data('id');
            var $replyForm = $('#reply-form-' + commentId);
            
            // Ẩn tất cả các form trả lời khác đang mở
            $('.reply-form').not($replyForm).slideUp(200);

            // Bật/tắt form trả lời hiện tại
            $replyForm.slideToggle(200);
        });

        // ===================================
        // 2. Logic AJAX Reaction Handler (Like/Dislike)
        // ===================================
        $('.like-action').on('click', function() {
            handleReaction($(this), 'toggle_like');
        });

        $('.dislike-action').on('click', function() {
            handleReaction($(this), 'toggle_dislike');
        });

        // ===================================
        // 3. HÀM CHÍNH XỬ LÝ REACTION VÀ MUTUAL EXCLUSION UI
        // ===================================
        function handleReaction($this, action) {
            var commentId = $this.data('id');
            
            // Kiểm tra đăng nhập (được kiểm tra lại ở PHP nhưng tốt nhất là có ở đây)
            if (!<?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>) {
                alert('Bạn cần đăng nhập để thực hiện chức năng này!');
                window.location='login.php';
                return;
            }

            $.ajax({
                type: 'POST',
                url: 'article.php?id=<?php echo $id; ?>',
                data: {
                    action: action,
                    comment_id: commentId
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        var $parent = $this.closest('.comment-actions');
                        var $likeBtn = $parent.find('.like-action');
                        var $dislikeBtn = $parent.find('.dislike-action');

                        // Cập nhật số lượt Like và Dislike mới nhất
                        $likeBtn.find('.like-count').text(response.total_likes);
                        $dislikeBtn.find('.dislike-count').text(response.total_dislikes);

                        // Cập nhật trạng thái (MUTUAL EXCLUSION UI)
                        if (action === 'toggle_like') {
                            // Nếu Like, xóa trạng thái Dislike (UI)
                            $dislikeBtn.removeClass('disliked').css('color', '#bbb');
                            
                            // Cập nhật trạng thái Like
                            if (response.is_liked) {
                                $likeBtn.addClass('liked').css('color', '#ffc107');
                            } else {
                                $likeBtn.removeClass('liked').css('color', '#bbb');
                            }
                        } else if (action === 'toggle_dislike') {
                            // Nếu Dislike, xóa trạng thái Like (UI)
                            $likeBtn.removeClass('liked').css('color', '#bbb');
                            
                            // Cập nhật trạng thái Dislike
                            if (response.is_disliked) {
                                $dislikeBtn.addClass('disliked').css('color', '#dc3545');
                            } else {
                                $dislikeBtn.removeClass('disliked').css('color', '#bbb');
                            }
                        }
                    } else {
                        alert(response.message);
                    }
                },
                error: function() {
                    alert('Đã xảy ra lỗi khi gửi yêu cầu.');
                }
            });
        }
    });
    </script>
</body>
</html>