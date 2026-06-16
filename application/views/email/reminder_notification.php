<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reminder Notification</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: #f8f9fa; padding: 30px; border-radius: 10px; border: 1px solid #e9ecef;">
        <h2 style="color: #2563eb; margin-top: 0;">Reminder Notification</h2>
        
        <p>Hi <?php echo htmlspecialchars($user_name); ?>,</p>
        
        <p>This is a reminder about:</p>
        
        <div style="background: #fff; padding: 20px; border-left: 4px solid #2563eb; margin: 20px 0; border-radius: 5px;">
            <h3 style="margin-top: 0; color: #1d4ed8;"><?php echo htmlspecialchars($reminder_title); ?></h3>
            
            <?php if (!empty($reminder_description)): ?>
            <p style="margin-bottom: 15px;"><?php echo nl2br(htmlspecialchars($reminder_description)); ?></p>
            <?php endif; ?>
            
            <p><strong>Due Date:</strong> <?php echo date('F j, Y', strtotime($reminder_date)); ?></p>
            <p><strong>Frequency:</strong> <?php echo ucfirst($frequency); ?></p>
            
            <?php if ($days_remaining > 0): ?>
            <p style="color: #d97706; font-weight: bold;"><?php echo $days_remaining; ?> day<?php echo $days_remaining > 1 ? 's' : ''; ?> remaining</p>
            <?php elseif ($days_remaining == 0): ?>
            <p style="color: #dc2626; font-weight: bold;">Due today!</p>
            <?php endif; ?>
        </div>
        
        <p>Please make sure to complete this task on time.</p>
        
        <hr style="border: none; border-top: 1px solid #e9ecef; margin: 30px 0;">
        
        <p style="font-size: 12px; color: #6c757d;">
            This is an automated reminder from BERPS. Please do not reply to this email.
        </p>
    </div>
</body>
</html>
