-- SQL to create announcement_msgs table
CREATE TABLE IF NOT EXISTS announcement_msgs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    message TEXT NOT NULL,
    show_date DATE NOT NULL,
    expiry_date DATE DEFAULT NULL,
    is_active TINYINT(1) DEFAULT 1
);
-- You can insert messages like:
-- INSERT INTO announcement_msgs (message, show_date, expiry_date, is_active) VALUES ('Site maintenance tonight!', '2025-10-19', '2025-10-19', 1);