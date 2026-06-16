-- Add attachment column to expenses table
-- Migration: 2026_04_14_add_expense_attachments.sql
-- Description: Add attachment column to store file paths for expense attachments

ALTER TABLE `expenses` 
ADD COLUMN `attachment` VARCHAR(255) NULL DEFAULT NULL 
AFTER `processedBy`;

-- Add index for better performance if needed
-- ALTER TABLE `expenses` ADD INDEX `idx_expenses_attachment` (`attachment`);
