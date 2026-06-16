-- Customer Support System Database Schema
-- This script creates all necessary tables for the customer support system

-- 1. Departments Table
CREATE TABLE IF NOT EXISTS support_departments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    department_name VARCHAR(100) NOT NULL,
    department_code VARCHAR(20) NOT NULL UNIQUE,
    description TEXT,
    manager_id INT,
    email VARCHAR(100),
    phone VARCHAR(20),
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    settingsID INT NOT NULL,
    INDEX idx_department_code (department_code),
    INDEX idx_settingsID (settingsID),
    FOREIGN KEY (settingsID) REFERENCES settings(id) ON DELETE CASCADE
);

-- 2. Employee Department Assignments Table
CREATE TABLE IF NOT EXISTS employee_departments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL,
    department_id INT NOT NULL,
    role VARCHAR(50) DEFAULT 'member', -- 'manager', 'lead', 'member'
    is_active TINYINT(1) DEFAULT 1,
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    assigned_by INT,
    settingsID INT NOT NULL,
    UNIQUE KEY unique_employee_department (employee_id, department_id, settingsID),
    INDEX idx_employee_id (employee_id),
    INDEX idx_department_id (department_id),
    INDEX idx_settingsID (settingsID),
    FOREIGN KEY (settingsID) REFERENCES settings(id) ON DELETE CASCADE
);

-- 3. Support Issues/Tickets Table
CREATE TABLE IF NOT EXISTS support_issues (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ticket_number VARCHAR(20) NOT NULL UNIQUE,
    customer_id INT,
    customer_name VARCHAR(100) NOT NULL,
    customer_email VARCHAR(100) NOT NULL,
    customer_phone VARCHAR(20),
    department_id INT NOT NULL,
    assigned_employee_id INT,
    title VARCHAR(200) NOT NULL,
    description TEXT NOT NULL,
    category VARCHAR(50),
    priority ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium',
    status ENUM('open', 'assigned', 'in_progress', 'pending_customer', 'resolved', 'closed') DEFAULT 'open',
    resolution_details TEXT,
    resolution_date DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    due_date DATETIME,
    resolved_by INT,
    settingsID INT NOT NULL,
    INDEX idx_ticket_number (ticket_number),
    INDEX idx_customer_email (customer_email),
    INDEX idx_department_id (department_id),
    INDEX idx_assigned_employee (assigned_employee_id),
    INDEX idx_status (status),
    INDEX idx_priority (priority),
    INDEX idx_created_at (created_at),
    INDEX idx_settingsID (settingsID),
    FOREIGN KEY (department_id) REFERENCES support_departments(id) ON DELETE RESTRICT,
    FOREIGN KEY (assigned_employee_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (resolved_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (settingsID) REFERENCES settings(id) ON DELETE CASCADE
);

-- 4. Issue Comments/Updates Table
CREATE TABLE IF NOT EXISTS support_issue_comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    issue_id INT NOT NULL,
    employee_id INT,
    customer_comment TINYINT(1) DEFAULT 0,
    comment TEXT NOT NULL,
    internal_note TINYINT(1) DEFAULT 0,
    attachment_path VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    settingsID INT NOT NULL,
    INDEX idx_issue_id (issue_id),
    INDEX idx_employee_id (employee_id),
    INDEX idx_created_at (created_at),
    INDEX idx_settingsID (settingsID),
    FOREIGN KEY (issue_id) REFERENCES support_issues(id) ON DELETE CASCADE,
    FOREIGN KEY (employee_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (settingsID) REFERENCES settings(id) ON DELETE CASCADE
);

-- 5. Issue Attachments Table
CREATE TABLE IF NOT EXISTS support_issue_attachments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    issue_id INT NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    file_size INT,
    file_type VARCHAR(50),
    uploaded_by INT,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    settingsID INT NOT NULL,
    INDEX idx_issue_id (issue_id),
    INDEX idx_uploaded_by (uploaded_by),
    INDEX idx_settingsID (settingsID),
    FOREIGN KEY (issue_id) REFERENCES support_issues(id) ON DELETE CASCADE,
    FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (settingsID) REFERENCES settings(id) ON DELETE CASCADE
);

-- 6. Notifications Table
CREATE TABLE IF NOT EXISTS support_notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    issue_id INT,
    department_id INT,
    notification_type ENUM('new_issue', 'assigned', 'status_update', 'comment', 'urgent', 'overdue') NOT NULL,
    title VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    action_required TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    read_at DATETIME,
    expires_at DATETIME,
    settingsID INT NOT NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_issue_id (issue_id),
    INDEX idx_department_id (department_id),
    INDEX idx_is_read (is_read),
    INDEX idx_created_at (created_at),
    INDEX idx_settingsID (settingsID),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (issue_id) REFERENCES support_issues(id) ON DELETE CASCADE,
    FOREIGN KEY (department_id) REFERENCES support_departments(id) ON DELETE CASCADE,
    FOREIGN KEY (settingsID) REFERENCES settings(id) ON DELETE CASCADE
);

-- 7. Issue Status History Table
CREATE TABLE IF NOT EXISTS support_issue_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    issue_id INT NOT NULL,
    old_status VARCHAR(20),
    new_status VARCHAR(20) NOT NULL,
    changed_by INT,
    change_reason TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    settingsID INT NOT NULL,
    INDEX idx_issue_id (issue_id),
    INDEX idx_changed_by (changed_by),
    INDEX idx_created_at (created_at),
    INDEX idx_settingsID (settingsID),
    FOREIGN KEY (issue_id) REFERENCES support_issues(id) ON DELETE CASCADE,
    FOREIGN KEY (changed_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (settingsID) REFERENCES settings(id) ON DELETE CASCADE
);

-- 8. Department Knowledge Base Table
CREATE TABLE IF NOT EXISTS support_knowledge_base (
    id INT AUTO_INCREMENT PRIMARY KEY,
    department_id INT,
    title VARCHAR(200) NOT NULL,
    content TEXT NOT NULL,
    category VARCHAR(50),
    tags VARCHAR(255),
    is_public TINYINT(1) DEFAULT 1,
    views INT DEFAULT 0,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    settingsID INT NOT NULL,
    INDEX idx_department_id (department_id),
    INDEX idx_category (category),
    INDEX idx_is_public (is_public),
    INDEX idx_settingsID (settingsID),
    FOREIGN KEY (department_id) REFERENCES support_departments(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (settingsID) REFERENCES settings(id) ON DELETE CASCADE
);

-- 9. Customer Satisfaction Ratings Table
CREATE TABLE IF NOT EXISTS support_satisfaction_ratings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    issue_id INT NOT NULL UNIQUE,
    rating TINYINT CHECK (rating >= 1 AND rating <= 5),
    feedback TEXT,
    rated_by_customer VARCHAR(100),
    rated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    settingsID INT NOT NULL,
    INDEX idx_issue_id (issue_id),
    INDEX idx_rating (rating),
    INDEX idx_settingsID (settingsID),
    FOREIGN KEY (issue_id) REFERENCES support_issues(id) ON DELETE CASCADE,
    FOREIGN KEY (settingsID) REFERENCES settings(id) ON DELETE CASCADE
);

-- 10. SLA (Service Level Agreement) Tracking Table
CREATE TABLE IF NOT EXISTS support_sla_tracking (
    id INT AUTO_INCREMENT PRIMARY KEY,
    issue_id INT NOT NULL UNIQUE,
    sla_type ENUM('response', 'resolution') NOT NULL,
    sla_hours INT NOT NULL,
    target_datetime DATETIME NOT NULL,
    actual_datetime DATETIME,
    met_sla TINYINT(1), -- 1: met, 0: missed, NULL: pending
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    settingsID INT NOT NULL,
    INDEX idx_issue_id (issue_id),
    INDEX idx_sla_type (sla_type),
    INDEX idx_target_datetime (target_datetime),
    INDEX idx_settingsID (settingsID),
    FOREIGN KEY (issue_id) REFERENCES support_issues(id) ON DELETE CASCADE,
    FOREIGN KEY (settingsID) REFERENCES settings(id) ON DELETE CASCADE
);

-- Insert default departments
INSERT INTO support_departments (department_name, department_code, description, settingsID) VALUES
('Technical Support', 'TECH', 'Handles technical issues and software problems', 1),
('Customer Service', 'CS', 'General customer inquiries and account issues', 1),
('Billing Support', 'BILL', 'Payment and billing related issues', 1),
('Sales Support', 'SALES', 'Sales inquiries and product information', 1),
('IT Support', 'IT', 'Internal IT infrastructure and system issues', 1);

-- Create triggers for automatic ticket number generation
DELIMITER //
CREATE TRIGGER generate_ticket_number 
BEFORE INSERT ON support_issues
FOR EACH ROW
BEGIN
    DECLARE ticket_count INT;
    SET ticket_count = (SELECT COUNT(*) FROM support_issues WHERE DATE(created_at) = CURDATE() AND settingsID = NEW.settingsID);
    SET NEW.ticket_number = CONCAT('TICK-', DATE_FORMAT(CURDATE(), '%Y%m%d'), '-', LPAD(ticket_count + 1, 4, '0'));
END//
DELIMITER ;

-- Create trigger for status history tracking
DELIMITER //
CREATE TRIGGER track_status_change
BEFORE UPDATE ON support_issues
FOR EACH ROW
BEGIN
    IF OLD.status != NEW.status THEN
        INSERT INTO support_issue_history (issue_id, old_status, new_status, changed_by, settingsID)
        VALUES (NEW.id, OLD.status, NEW.status, NEW.resolved_by, NEW.settingsID);
    END IF;
END//
DELIMITER ;

-- Create view for department statistics
CREATE VIEW department_stats AS
SELECT 
    d.id as department_id,
    d.department_name,
    d.department_code,
    COUNT(si.id) as total_issues,
    COUNT(CASE WHEN si.status = 'open' THEN 1 END) as open_issues,
    COUNT(CASE WHEN si.status = 'in_progress' THEN 1 END) as in_progress_issues,
    COUNT(CASE WHEN si.status = 'resolved' THEN 1 END) as resolved_issues,
    COUNT(CASE WHEN si.priority = 'urgent' THEN 1 END) as urgent_issues,
    AVG(TIMESTAMPDIFF(HOUR, si.created_at, si.updated_at)) as avg_resolution_time_hours,
    d.settingsID
FROM support_departments d
LEFT JOIN support_issues si ON d.id = si.department_id
GROUP BY d.id, d.department_name, d.department_code, d.settingsID;

-- Create view for employee workload
CREATE VIEW employee_workload AS
SELECT 
    u.id as employee_id,
    u.name as employee_name,
    d.department_name,
    COUNT(si.id) as assigned_issues,
    COUNT(CASE WHEN si.status = 'open' THEN 1 END) as open_issues,
    COUNT(CASE WHEN si.status = 'in_progress' THEN 1 END) as in_progress_issues,
    COUNT(CASE WHEN si.priority = 'urgent' THEN 1 END) as urgent_issues,
    AVG(TIMESTAMPDIFF(HOUR, si.created_at, si.updated_at)) as avg_resolution_time_hours,
    ed.settingsID
FROM users u
JOIN employee_departments ed ON u.id = ed.employee_id
JOIN support_departments d ON ed.department_id = d.id
LEFT JOIN support_issues si ON u.id = si.assigned_employee_id
WHERE ed.is_active = 1
GROUP BY u.id, u.name, d.department_name, ed.settingsID;

-- 11. Support Settings Table (for system configuration)
CREATE TABLE IF NOT EXISTS support_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    setting_type ENUM('string', 'integer', 'boolean', 'json') DEFAULT 'string',
    description TEXT,
    is_editable TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    settingsID INT NOT NULL,
    INDEX idx_setting_key (setting_key),
    INDEX idx_settingsID (settingsID),
    FOREIGN KEY (settingsID) REFERENCES settings(id) ON DELETE CASCADE
);

-- 12. Support Templates Table (for email templates and responses)
CREATE TABLE IF NOT EXISTS support_templates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    template_name VARCHAR(100) NOT NULL,
    template_type ENUM('email', 'response', 'notification') NOT NULL,
    subject VARCHAR(200),
    content TEXT NOT NULL,
    variables TEXT, -- JSON string of template variables
    is_active TINYINT(1) DEFAULT 1,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    settingsID INT NOT NULL,
    INDEX idx_template_type (template_type),
    INDEX idx_is_active (is_active),
    INDEX idx_settingsID (settingsID),
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (settingsID) REFERENCES settings(id) ON DELETE CASCADE
);

-- 13. Support Escalation Rules Table
CREATE TABLE IF NOT EXISTS support_escalation_rules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    rule_name VARCHAR(100) NOT NULL,
    department_id INT,
    priority_level ENUM('low', 'medium', 'high', 'urgent'),
    time_threshold_hours INT NOT NULL,
    escalation_action ENUM('notify_manager', 'reassign', 'escalate_department', 'send_email') NOT NULL,
    escalation_target VARCHAR(255), -- email or user ID depending on action
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    settingsID INT NOT NULL,
    INDEX idx_department_id (department_id),
    INDEX idx_priority_level (priority_level),
    INDEX idx_is_active (is_active),
    INDEX idx_settingsID (settingsID),
    FOREIGN KEY (department_id) REFERENCES support_departments(id) ON DELETE SET NULL,
    FOREIGN KEY (settingsID) REFERENCES settings(id) ON DELETE CASCADE
);

-- 14. Support Time Tracking Table
CREATE TABLE IF NOT EXISTS support_time_tracking (
    id INT AUTO_INCREMENT PRIMARY KEY,
    issue_id INT NOT NULL,
    employee_id INT NOT NULL,
    action_type ENUM('start_work', 'pause_work', 'resume_work', 'end_work') NOT NULL,
    duration_minutes INT DEFAULT 0,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    settingsID INT NOT NULL,
    INDEX idx_issue_id (issue_id),
    INDEX idx_employee_id (employee_id),
    INDEX idx_action_type (action_type),
    INDEX idx_created_at (created_at),
    INDEX idx_settingsID (settingsID),
    FOREIGN KEY (issue_id) REFERENCES support_issues(id) ON DELETE CASCADE,
    FOREIGN KEY (employee_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (settingsID) REFERENCES settings(id) ON DELETE CASCADE
);

-- 15. Support Customer Feedback Table
CREATE TABLE IF NOT EXISTS support_customer_feedback (
    id INT AUTO_INCREMENT PRIMARY KEY,
    issue_id INT NOT NULL UNIQUE,
    feedback_type ENUM('satisfaction', 'complaint', 'suggestion') NOT NULL,
    rating TINYINT CHECK (rating >= 1 AND rating <= 5),
    comments TEXT,
    follow_up_required TINYINT(1) DEFAULT 0,
    follow_up_notes TEXT,
    follow_up_date DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    settingsID INT NOT NULL,
    INDEX idx_issue_id (issue_id),
    INDEX idx_feedback_type (feedback_type),
    INDEX idx_rating (rating),
    INDEX idx_settingsID (settingsID),
    FOREIGN KEY (issue_id) REFERENCES support_issues(id) ON DELETE CASCADE,
    FOREIGN KEY (settingsID) REFERENCES settings(id) ON DELETE CASCADE
);

-- 16. Support Reports Table (for saved reports)
CREATE TABLE IF NOT EXISTS support_reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    report_name VARCHAR(100) NOT NULL,
    report_type ENUM('department_stats', 'employee_performance', 'issue_trends', 'satisfaction', 'custom') NOT NULL,
    report_config TEXT, -- JSON configuration
    report_data TEXT, -- Cached report data
    generated_by INT,
    generated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP,
    is_public TINYINT(1) DEFAULT 0,
    settingsID INT NOT NULL,
    INDEX idx_report_type (report_type),
    INDEX idx_generated_by (generated_by),
    INDEX idx_is_public (is_public),
    INDEX idx_settingsID (settingsID),
    FOREIGN KEY (generated_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (settingsID) REFERENCES settings(id) ON DELETE CASCADE
);

-- 17. Support Audit Log Table
CREATE TABLE IF NOT EXISTS support_audit_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action_type VARCHAR(50) NOT NULL,
    table_name VARCHAR(50) NOT NULL,
    record_id INT NOT NULL,
    old_values TEXT, -- JSON of old values
    new_values TEXT, -- JSON of new values
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    settingsID INT NOT NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_action_type (action_type),
    INDEX idx_table_name (table_name),
    INDEX idx_record_id (record_id),
    INDEX idx_created_at (created_at),
    INDEX idx_settingsID (settingsID),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (settingsID) REFERENCES settings(id) ON DELETE CASCADE
);

-- Insert default support settings
INSERT INTO support_settings (setting_key, setting_value, setting_type, description, settingsID) VALUES
('auto_assign_enabled', '1', 'boolean', 'Automatically assign issues to available employees', 1),
('default_priority', 'medium', 'string', 'Default priority for new issues', 1),
('notification_email_enabled', '1', 'boolean', 'Enable email notifications', 1),
('sla_response_hours', '24', 'integer', 'Default SLA response time in hours', 1),
('allow_customer_attachments', '1', 'boolean', 'Allow customers to upload attachments', 1),
('max_attachment_size', '5', 'integer', 'Maximum attachment size in MB', 1),
('auto_close_days', '30', 'integer', 'Auto-close resolved issues after X days', 1),
('require_customer_email', '1', 'boolean', 'Require customer email for issue submission', 1);

-- Insert default email templates
INSERT INTO support_templates (template_name, template_type, subject, content, variables, settingsID) VALUES
('New Issue Confirmation', 'email', 'Support Request Received - Ticket #{ticket_number}', 
 'Dear {customer_name},\n\nThank you for contacting our support team. Your support request has been received and assigned ticket number #{ticket_number}.\n\nIssue Details:\nTitle: {title}\nDepartment: {department_name}\nPriority: {priority}\n\nWe will review your request and get back to you shortly. You can track the status of your issue by referencing your ticket number.\n\nBest regards,\n{company_name} Support Team', 
 '{"customer_name","ticket_number","title","department_name","priority","company_name"}', 1),

('Issue Assigned', 'notification', 'Issue #{ticket_number} Assigned to You', 
 'You have been assigned a new support issue:\n\nTicket: #{ticket_number}\nCustomer: {customer_name}\nTitle: {title}\nPriority: {priority}\n\nPlease review and take appropriate action.', 
 '{"ticket_number","customer_name","title","priority"}', 1),

('Issue Resolved', 'email', 'Your Support Issue #{ticket_number} Has Been Resolved', 
 'Dear {customer_name},\n\nGood news! Your support issue #{ticket_number} has been resolved.\n\nResolution Details:\n{resolution_details}\n\nIf you are satisfied with the resolution, no further action is needed. If you have any questions or if the issue persists, please reply to this email or contact our support team.\n\nThank you for your patience.\n\nBest regards,\n{company_name} Support Team', 
 '{"customer_name","ticket_number","resolution_details","company_name"}', 1);

-- Insert default escalation rules
INSERT INTO support_escalation_rules (rule_name, department_id, priority_level, time_threshold_hours, escalation_action, escalation_target, settingsID) VALUES
('Urgent Issue Escalation', NULL, 'urgent', 1, 'notify_manager', '', 1),
('High Priority Escalation', NULL, 'high', 4, 'notify_manager', '', 1),
('Medium Priority Follow-up', NULL, 'medium', 24, 'send_email', 'manager@company.com', 1),
('Department Manager Escalation', NULL, 'urgent', 2, 'escalate_department', '', 1);
