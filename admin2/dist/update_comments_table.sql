-- Thêm cột status vào bảng comments nếu chưa có
-- Chạy lệnh này trong phpMyAdmin hoặc MySQL

ALTER TABLE comments 
ADD COLUMN IF NOT EXISTS status ENUM('pending', 'approved', 'hidden') DEFAULT 'approved' AFTER content;

-- Cập nhật các bình luận cũ thành approved
UPDATE comments SET status = 'approved' WHERE status IS NULL;
