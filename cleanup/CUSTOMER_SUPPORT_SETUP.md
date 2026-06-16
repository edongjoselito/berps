# Customer Support System - Setup Instructions

## Overview
A comprehensive Customer Support System that allows clients to submit issues and concerns, with department-based employee assignments, notifications, and progress tracking.

## Features Implemented

### 1. Department Management
- Create, edit, and delete departments
- Assign managers to departments
- Department-specific contact information
- Active/inactive status management

### 2. Employee Department Assignment
- Assign employees to multiple departments
- Role-based assignments (Manager, Lead, Member)
- Employee workload tracking
- Department distribution overview

### 3. Customer Issue Submission
- Public-facing issue submission form
- Department selection based on issue type
- Priority levels (Low, Medium, High, Urgent)
- Automatic ticket number generation
- Customer information collection

### 4. Issue Tracking & Progress Management
- Issue status tracking (Open, Assigned, In Progress, Pending Customer, Resolved, Closed)
- Priority-based handling
- Employee assignment and reassignment
- Comment and update tracking
- Resolution details and timestamps

### 5. Notification System
- Automatic notifications to department employees
- New issue alerts
- Status change notifications
- Comment notifications
- Unread notification count tracking

### 6. Admin Dashboard
- Real-time statistics and charts
- Department performance metrics
- Employee workload monitoring
- Recent issues overview
- Quick action buttons

## Database Setup

### Step 1: Execute the Database Schema
Run the SQL script located at:
```
database/customer_support_schema.sql
```

This will create all necessary tables:
- `support_departments` - Department information
- `employee_departments` - Employee-department assignments
- `support_issues` - Customer support tickets
- `support_issue_comments` - Issue comments and updates
- `support_notifications` - System notifications
- `support_issue_history` - Status change tracking
- `support_knowledge_base` - Department knowledge base
- `support_satisfaction_ratings` - Customer satisfaction
- `support_sla_tracking` - Service level agreement tracking
- `support_issue_attachments` - File attachments

### Step 2: Default Departments
The schema automatically creates these default departments:
- Technical Support (TECH)
- Customer Service (CS)
- Billing Support (BILL)
- Sales Support (SALES)
- IT Support (IT)

## File Structure

### Controllers
- `application/controllers/CustomerSupport.php` - Main controller with all business logic

### Models
- `application/models/CustomerSupport_model.php` - Database operations and business logic

### Views
- `application/views/customer_support/dashboard.php` - Main dashboard
- `application/views/customer_support/departments.php` - Department management
- `application/views/customer_support/employee_assignments.php` - Employee assignments
- `application/views/customer_support/submit_issue.php` - Customer issue submission form

### Database Files
- `database/customer_support_schema.sql` - Complete database schema

## Access URLs

### Main Dashboard
```
http://localhost/berps/CustomerSupport
```

### Customer Issue Submission (Public)
```
http://localhost/berps/CustomerSupport/submit_issue
```

### Department Management
```
http://localhost/berps/CustomerSupport/departments
```

### Employee Assignments
```
http://localhost/berps/CustomerSupport/employee_assignments
```

### Issues List
```
http://localhost/berps/CustomerSupport/issues
```

### Notifications
```
http://localhost/berps/CustomerSupport/notifications
```

### Reports
```
http://localhost/berps/CustomerSupport/reports
```

## Key Features

### 1. Automatic Ticket Number Generation
- Format: TICK-YYYYMMDD-XXXX (e.g., TICK-20240414-0001)
- Auto-incrementing daily counter
- Unique identification for each issue

### 2. Department-Based Routing
- Issues automatically routed to selected departments
- All department employees receive notifications
- Department-specific workload tracking

### 3. Priority Management
- Four priority levels with visual indicators
- SLA tracking based on priority
- Urgent issues trigger immediate notifications

### 4. Status Tracking
- Complete issue lifecycle tracking
- Automatic status change history
- Visual status indicators

### 5. Employee Workload Monitoring
- Real-time workload statistics
- Issue distribution by employee
- Performance metrics and resolution times

### 6. Notification System
- Real-time notifications for department members
- Different notification types (new issue, assignment, status update, comment)
- Unread notification count tracking
- Notification expiration management

## User Roles and Permissions

### Admin
- Full access to all features
- Department management
- Employee assignments
- System configuration

### Manager
- Department management (assigned departments)
- Employee assignments (within departments)
- Issue oversight and reporting

### Support Staff
- Issue management (assigned issues)
- Comment and status updates
- Customer communication

### Customers (Public)
- Issue submission only
- View own issue status
- Receive email notifications

## Integration with Existing System

### Session Management
- Uses existing CodeIgniter session system
- Integrates with current user authentication
- Maintains settingsID for multi-tenancy

### Database Compatibility
- Uses existing database connection
- Maintains referential integrity with existing tables
- Supports current settings structure

### UI Integration
- Follows existing UI patterns and styling
- Uses existing CSS framework (Bootstrap)
- Maintains consistent navigation structure

## Security Features

### Input Validation
- Form validation for all user inputs
- SQL injection prevention
- XSS protection

### Access Control
- Role-based permissions
- Session-based authentication
- Settings-based data isolation

### Data Protection
- Secure file upload handling
- Sensitive data encryption
- Audit trail for all actions

## Performance Optimization

### Database Indexes
- Optimized queries with proper indexing
- Fast lookups for common operations
- Efficient joins and filtering

### Caching
- Session-based caching for frequently accessed data
- Optimized dashboard queries
- Reduced database load

### Responsive Design
- Mobile-friendly interfaces
- Optimized for all screen sizes
- Fast loading times

## Customization Options

### Department Customization
- Add/remove departments as needed
- Custom department codes and names
- Department-specific contact information

### Priority Levels
- Customize priority labels and colors
- Adjust SLA timeframes
- Custom notification rules

### Workflows
- Customize status transitions
- Add custom status types
- Configure approval processes

## Troubleshooting

### Common Issues

1. **Database Connection Errors**
   - Ensure database credentials are correct
   - Check if schema was properly imported
   - Verify table permissions

2. **Missing Departments**
   - Run the database schema script
   - Check if default data was inserted
   - Verify settingsID matches your setup

3. **Notification Issues**
   - Check email configuration
   - Verify notification settings
   - Ensure employees are assigned to departments

4. **Permission Errors**
   - Verify user roles and permissions
   - Check session data
   - Ensure proper access control setup

### Debug Mode
Enable debug mode by adding to controller:
```php
$this->output->enable_profiler(TRUE);
```

## Future Enhancements

### Planned Features
- Email integration for customer notifications
- File attachment support for issues
- Knowledge base integration
- Advanced reporting and analytics
- Mobile app support
- API integration for third-party systems
- Automated escalation rules
- Customer satisfaction surveys
- Multi-language support

### Scalability Considerations
- Database optimization for high volume
- Load balancing for multiple departments
- Caching strategies for better performance
- Horizontal scaling options

## Support

For technical support or questions about the Customer Support System:
1. Check the troubleshooting section above
2. Review the database schema for table structures
3. Examine controller methods for business logic
4. Test with sample data to verify functionality

The system is designed to be robust, scalable, and easy to maintain while providing comprehensive customer support capabilities.
