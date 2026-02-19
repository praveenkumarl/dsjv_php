-- Migration: add 'lang' column to `user` table (default 'tamil')
-- File: migrations/20260219_add_lang_to_user.sql
-- Backup DB before running.

/*
Run (up):
mysql -u <user> -p <database> < migrations/20260219_add_lang_to_user.sql
*/

-- ========== UP ==========
START TRANSACTION;

-- Add the column (safe in MySQL 8+). Place after `user_type` which matches your schema.
ALTER TABLE `user`
  ADD COLUMN IF NOT EXISTS `lang` VARCHAR(10) NOT NULL
    COLLATE 'utf8mb4_unicode_ci'
    DEFAULT 'tamil'
    COMMENT 'preferred language: tamil | english'
    AFTER `user_type`;

-- Ensure any pre-existing rows (older MySQL versions may leave NULL) get a value
UPDATE `user` SET `lang` = 'tamil' WHERE `lang` IS NULL OR `lang` = '';

COMMIT;

-- ========== DOWN ==========
-- To rollback, remove the column. Backup first.

ALTER TABLE `user` DROP COLUMN IF EXISTS `lang`;