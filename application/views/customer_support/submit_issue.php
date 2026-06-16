<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Support Request - Customer Support</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .support-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            margin: 40px auto;
            max-width: 800px;
            overflow: hidden;
        }
        
        .support-header {
            background: linear-gradient(135deg, #4a90e2 0%, #357abd 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .support-header h1 {
            margin: 0;
            font-size: 2.5rem;
            font-weight: 300;
        }
        
        .support-header p {
            margin: 10px 0 0;
            opacity: 0.9;
            font-size: 1.1rem;
        }
        
        .support-body {
            padding: 40px;
        }
        
        .form-group label {
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
        }
        
        .form-control, .form-select {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 12px 15px;
            font-size: 16px;
            transition: all 0.3s ease;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: #4a90e2;
            box-shadow: 0 0 0 3px rgba(74, 144, 226, 0.1);
            outline: none;
        }
        
        .priority-badge {
            display: inline-block;
            padding: 8px 12px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
            margin-right: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }
        
        .priority-badge:hover {
            transform: translateY(-2px);
        }
        
        .priority-badge.selected {
            border-color: #333;
        }
        
        .priority-low {
            background: #e9ecef;
            color: #6c757d;
        }
        
        .priority-medium {
            background: #d1ecf1;
            color: #0c5460;
        }
        
        .priority-high {
            background: #fff3cd;
            color: #856404;
        }
        
        .priority-urgent {
            background: #f8d7da;
            color: #721c24;
        }
        
        .department-card {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .department-card:hover {
            border-color: #4a90e2;
            background: #f8f9fa;
        }
        
        .department-card.selected {
            border-color: #4a90e2;
            background: #e3f2fd;
        }
        
        .department-card h5 {
            margin: 0 0 5px 0;
            color: #333;
        }
        
        .department-card p {
            margin: 0;
            color: #6c757d;
            font-size: 14px;
        }
        
        .btn-submit {
            background: linear-gradient(135deg, #4a90e2 0%, #357abd 100%);
            color: white;
            border: none;
            border-radius: 10px;
            padding: 15px 30px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
        }
        
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(74, 144, 226, 0.3);
        }
        
        .btn-submit:disabled {
            background: #6c757d;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }
        
        .help-text {
            font-size: 14px;
            color: #6c757d;
            margin-top: 5px;
        }
        
        .alert {
            border-radius: 10px;
            border: none;
            padding: 15px 20px;
        }
        
        .loading-spinner {
            display: none;
            text-align: center;
            padding: 20px;
        }
        
        .loading-spinner .spinner-border {
            width: 3rem;
            height: 3rem;
        }
        
        .success-animation {
            display: none;
            text-align: center;
            padding: 40px;
        }
        
        .success-animation i {
            font-size: 4rem;
            color: #28a745;
            margin-bottom: 20px;
        }
        
        @media (max-width: 768px) {
            .support-container {
                margin: 20px;
                border-radius: 15px;
            }
            
            .support-header {
                padding: 20px;
            }
            
            .support-header h1 {
                font-size: 2rem;
            }
            
            .support-body {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="support-container">
        <div class="support-header">
            <h1><i class="bi bi-headset"></i> Customer Support</h1>
            <p>We're here to help. Submit your support request and we'll get back to you soon.</p>
        </div>
        
        <div class="support-body">
            <?php if ($this->session->flashdata('success')): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle"></i> <?= $this->session->flashdata('success'); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php if ($this->session->flashdata('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle"></i> <?= $this->session->flashdata('error'); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle"></i> <?= $error; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <!-- Success Animation -->
            <div class="success-animation" id="successAnimation">
                <i class="bi bi-check-circle-fill"></i>
                <h3>Support Request Submitted!</h3>
                <p>Your request has been successfully submitted. We'll review it and get back to you shortly.</p>
                <p><strong>Ticket Number:</strong> <span id="ticketNumber"></span></p>
                <button class="btn btn-primary mt-3" onclick="resetForm()">Submit Another Request</button>
            </div>
            
            <!-- Support Form -->
            <form id="supportForm" method="post" action="<?= base_url('CustomerSupport/customer_submit_issue'); ?>">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="customer_name">Your Name *</label>
                            <input type="text" class="form-control" id="customer_name" name="customer_name" required>
                            <div class="help-text">Please provide your full name</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="customer_email">Email Address *</label>
                            <input type="email" class="form-control" id="customer_email" name="customer_email" required>
                            <div class="help-text">We'll send updates to this email</div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="customer_phone">Phone Number</label>
                            <input type="tel" class="form-control" id="customer_phone" name="customer_phone" placeholder="Optional">
                            <div class="help-text">Optional - for urgent matters</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="category">Category</label>
                            <select class="form-select" id="category" name="category">
                                <option value="">Select a category</option>
                                <option value="technical">Technical Issue</option>
                                <option value="billing">Billing/Payment</option>
                                <option value="account">Account Issue</option>
                                <option value="feature">Feature Request</option>
                                <option value="bug">Bug Report</option>
                                <option value="general">General Inquiry</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Select Department *</label>
                    <div class="help-text">Choose the department that can best help with your issue</div>
                    <div id="departmentSelection">
                        <?php if (!empty($departments)): ?>
                            <?php foreach ($departments as $dept): ?>
                                <div class="department-card" data-department-id="<?= $dept->id; ?>">
                                    <h5><?= htmlspecialchars($dept->department_name, ENT_QUOTES, 'UTF-8'); ?></h5>
                                    <p><?= htmlspecialchars($dept->description, ENT_QUOTES, 'UTF-8'); ?></p>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="alert alert-warning">
                                <i class="bi bi-exclamation-triangle"></i> No departments available at the moment. Please try again later.
                            </div>
                        <?php endif; ?>
                    </div>
                    <input type="hidden" id="department_id" name="department_id" required>
                </div>
                
                <div class="form-group">
                    <label for="title">Issue Title *</label>
                    <input type="text" class="form-control" id="title" name="title" required placeholder="Brief summary of your issue">
                    <div class="help-text">Keep it concise and descriptive</div>
                </div>
                
                <div class="form-group">
                    <label for="description">Detailed Description *</label>
                    <textarea class="form-control" id="description" name="description" rows="6" required placeholder="Please provide as much detail as possible about your issue..."></textarea>
                    <div class="help-text">Include any relevant details, error messages, or steps to reproduce the issue</div>
                </div>
                
                <div class="form-group">
                    <label>Priority Level *</label>
                    <div class="help-text">How urgent is this issue?</div>
                    <div class="priority-selection">
                        <div class="priority-badge priority-low" data-priority="low">
                            <i class="bi bi-arrow-down"></i> Low
                        </div>
                        <div class="priority-badge priority-medium" data-priority="medium">
                            <i class="bi bi-dash"></i> Medium
                        </div>
                        <div class="priority-badge priority-high" data-priority="high">
                            <i class="bi bi-arrow-up"></i> High
                        </div>
                        <div class="priority-badge priority-urgent" data-priority="urgent">
                            <i class="bi bi-exclamation-triangle"></i> Urgent
                        </div>
                    </div>
                    <input type="hidden" id="priority" name="priority" required>
                </div>
                
                <div class="loading-spinner" id="loadingSpinner">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-3">Submitting your support request...</p>
                </div>
                
                <button type="submit" class="btn-submit" id="submitBtn">
                    <i class="bi bi-send"></i> Submit Support Request
                </button>
            </form>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Department selection
        document.querySelectorAll('.department-card').forEach(card => {
            card.addEventListener('click', function() {
                // Remove previous selection
                document.querySelectorAll('.department-card').forEach(c => c.classList.remove('selected'));
                
                // Add selection to clicked card
                this.classList.add('selected');
                
                // Set hidden input value
                document.getElementById('department_id').value = this.dataset.departmentId;
            });
        });
        
        // Priority selection
        document.querySelectorAll('.priority-badge').forEach(badge => {
            badge.addEventListener('click', function() {
                // Remove previous selection
                document.querySelectorAll('.priority-badge').forEach(b => b.classList.remove('selected'));
                
                // Add selection to clicked badge
                this.classList.add('selected');
                
                // Set hidden input value
                document.getElementById('priority').value = this.dataset.priority;
            });
        });
        
        // Form submission
        document.getElementById('supportForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Validate department selection
            if (!document.getElementById('department_id').value) {
                alert('Please select a department');
                return;
            }
            
            // Validate priority selection
            if (!document.getElementById('priority').value) {
                alert('Please select a priority level');
                return;
            }
            
            // Show loading spinner
            document.getElementById('loadingSpinner').style.display = 'block';
            document.getElementById('submitBtn').disabled = true;
            
            // Submit form
            this.submit();
        });
        
        // Reset form function
        function resetForm() {
            document.getElementById('supportForm').reset();
            document.getElementById('successAnimation').style.display = 'none';
            document.getElementById('supportForm').style.display = 'block';
            document.getElementById('submitBtn').disabled = false;
            
            // Clear selections
            document.querySelectorAll('.department-card').forEach(c => c.classList.remove('selected'));
            document.querySelectorAll('.priority-badge').forEach(b => b.classList.remove('selected'));
        }
        
        // Auto-resize textarea
        document.getElementById('description').addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
        });
        
        // Form validation
        function validateForm() {
            const required = ['customer_name', 'customer_email', 'title', 'description'];
            
            for (let field of required) {
                const element = document.getElementById(field);
                if (!element.value.trim()) {
                    element.focus();
                    return false;
                }
            }
            
            if (!document.getElementById('department_id').value) {
                alert('Please select a department');
                return false;
            }
            
            if (!document.getElementById('priority').value) {
                alert('Please select a priority level');
                return false;
            }
            
            return true;
        }
        
        // Character counter for description
        document.getElementById('description').addEventListener('input', function() {
            const charCount = this.value.length;
            const maxLength = 2000;
            
            if (charCount > maxLength) {
                this.value = this.value.substring(0, maxLength);
            }
        });
    </script>
</body>
</html>
