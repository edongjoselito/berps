<div class="content-page">
    <div class="content">
        <div class="container-fluid admin-dashboard-page">
            <!-- Page Title -->
            <div class="page-title-box">
                <div class="row align-items-center">
                    <div class="col-sm-6">
                        <h4 class="page-title">Department Management</h4>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?= base_url('CustomerSupport'); ?>">Customer Support</a></li>
                            <li class="breadcrumb-item active">Departments</li>
                        </ol>
                    </div>
                    <div class="col-sm-6">
                        <div class="float-right d-none d-md-block">
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDepartmentModal">
                                <i class="mdi mdi-plus"></i> Add Department
                            </button>
                            <a href="<?= base_url('CustomerSupport/employee_assignments'); ?>" class="btn btn-info ml-2">
                                <i class="mdi mdi-account-multiple"></i> Employee Assignments
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <style>
                .admin-dashboard-page {
                    --bg: #f5f7fb;
                    --surface: rgba(255, 255, 255, 0.96);
                    --surface-strong: #ffffff;
                    --surface-soft: #f8fbff;
                    --line: #e4ebf4;
                    --line-strong: #cfdbea;
                    --text: #142235;
                    --text-soft: #617489;
                    --text-faint: #8ea0b5;
                    --primary: #2563eb;
                    --primary-2: #1d4ed8;
                    --primary-soft: #eaf2ff;
                    --success: #059669;
                    --warning: #d97706;
                    --danger: #dc2626;
                }

                .admin-dashboard-page .card {
                    background: var(--surface);
                    border: 1px solid var(--line);
                    border-radius: 16px;
                    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
                }

                .admin-dashboard-page .card-body {
                    padding: 24px;
                }

                .admin-dashboard-page .card-title {
                    color: var(--text);
                    font-weight: 600;
                    font-size: 1.125rem;
                }

                .admin-dashboard-page .table {
                    color: var(--text);
                }

                .admin-dashboard-page .table thead th {
                    background: var(--surface-soft);
                    color: var(--text);
                    font-weight: 600;
                    font-size: 0.875rem;
                    padding: 12px 16px;
                    border-bottom: 2px solid var(--line);
                }

                .admin-dashboard-page .table tbody td {
                    padding: 12px 16px;
                    border-bottom: 1px solid var(--line);
                    vertical-align: middle;
                }

                .admin-dashboard-page .btn-primary {
                    background: var(--primary);
                    border-color: var(--primary);
                    color: white;
                    border-radius: 10px;
                    padding: 8px 16px;
                    font-weight: 500;
                }

                .admin-dashboard-page .btn-primary:hover {
                    background: var(--primary-2);
                    border-color: var(--primary-2);
                }

                .admin-dashboard-page .btn-info {
                    background: #0ea5e9;
                    border-color: #0ea5e9;
                    color: white;
                    border-radius: 10px;
                    padding: 8px 16px;
                    font-weight: 500;
                }

                .admin-dashboard-page .btn-outline-primary {
                    color: var(--primary);
                    border-color: var(--primary);
                    border-radius: 8px;
                }

                .admin-dashboard-page .btn-outline-info {
                    color: #0ea5e9;
                    border-color: #0ea5e9;
                    border-radius: 8px;
                }

                .admin-dashboard-page .btn-outline-warning {
                    color: var(--warning);
                    border-color: var(--warning);
                    border-radius: 8px;
                }

                .admin-dashboard-page .btn-outline-success {
                    color: var(--success);
                    border-color: var(--success);
                    border-radius: 8px;
                }

                .admin-dashboard-page .badge-primary {
                    background: var(--primary-soft);
                    color: var(--primary);
                    padding: 4px 10px;
                    border-radius: 6px;
                    font-weight: 500;
                    font-size: 0.75rem;
                }

                .admin-dashboard-page .badge-info {
                    background: #e0f2fe;
                    color: #0284c7;
                    padding: 4px 10px;
                    border-radius: 6px;
                    font-weight: 500;
                    font-size: 0.75rem;
                }

                .admin-dashboard-page .badge-warning {
                    background: #fef3c7;
                    color: #d97706;
                    padding: 4px 10px;
                    border-radius: 6px;
                    font-weight: 500;
                    font-size: 0.75rem;
                }

                .admin-dashboard-page .badge-success {
                    background: #d1fae5;
                    color: var(--success);
                    padding: 4px 10px;
                    border-radius: 6px;
                    font-weight: 500;
                    font-size: 0.75rem;
                }

                .admin-dashboard-page .badge-secondary {
                    background: #f1f5f9;
                    color: var(--text-soft);
                    padding: 4px 10px;
                    border-radius: 6px;
                    font-weight: 500;
                    font-size: 0.75rem;
                }

                .admin-dashboard-page .badge-danger {
                    background: #fee2e2;
                    color: var(--danger);
                    padding: 4px 10px;
                    border-radius: 6px;
                    font-weight: 500;
                    font-size: 0.75rem;
                }

                .admin-dashboard-page .modal-content {
                    border-radius: 16px;
                    border: 1px solid var(--line);
                }

                .admin-dashboard-page .modal-header {
                    background: var(--surface-soft);
                    border-bottom: 1px solid var(--line);
                    padding: 20px 24px;
                }

                .admin-dashboard-page .modal-title {
                    color: var(--text);
                    font-weight: 600;
                }

                .admin-dashboard-page .modal-body {
                    padding: 24px;
                }

                .admin-dashboard-page .form-label {
                    color: var(--text);
                    font-weight: 500;
                    font-size: 0.875rem;
                }

                .admin-dashboard-page .form-control {
                    border: 1px solid var(--line);
                    border-radius: 10px;
                    padding: 10px 14px;
                    font-size: 0.875rem;
                }

                .admin-dashboard-page .form-control:focus {
                    border-color: var(--primary);
                    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
                }

                .admin-dashboard-page .form-select {
                    border: 1px solid var(--line);
                    border-radius: 10px;
                    padding: 10px 14px;
                    font-size: 0.875rem;
                }
            </style>

            <!-- Flash Messages -->
            <?php if ($this->session->flashdata('success')): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="mdi mdi-check-circle"></i> <?= $this->session->flashdata('success'); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php if ($this->session->flashdata('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="mdi mdi-exclamation-triangle"></i> <?= $this->session->flashdata('error'); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Departments List -->
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-centered table-nowrap mb-0" id="departmentsTable">
                                    <thead>
                                        <tr>
                                            <th>Department Name</th>
                                            <th>Code</th>
                                            <th>Description</th>
                                            <th>Contact</th>
                                            <th>Employees</th>
                                            <th>Active Issues</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($departments)): ?>
                                            <?php foreach ($departments as $dept): ?>
                                                <tr>
                                                    <td>
                                                        <h5 class="font-size-14 mb-1"><?= htmlspecialchars($dept->department_name, ENT_QUOTES, 'UTF-8'); ?></h5>
                                                    </td>
                                                    <td>
                                                        <span class="badge badge-primary"><?= htmlspecialchars($dept->department_code, ENT_QUOTES, 'UTF-8'); ?></span>
                                                    </td>
                                                    <td>
                                                        <p class="text-muted mb-0"><?= htmlspecialchars(substr($dept->description, 0, 100), ENT_QUOTES, 'UTF-8'); ?>...</p>
                                                    </td>
                                                    <td>
                                                        <?php if ($dept->email): ?>
                                                            <p class="mb-1"><i class="mdi mdi-email"></i> <?= htmlspecialchars($dept->email, ENT_QUOTES, 'UTF-8'); ?></p>
                                                        <?php endif; ?>
                                                        <?php if ($dept->phone): ?>
                                                            <p class="mb-0"><i class="mdi mdi-phone"></i> <?= htmlspecialchars($dept->phone, ENT_QUOTES, 'UTF-8'); ?></p>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php 
                                                        $employees = $this->CustomerSupport_model->get_department_employees($dept->id);
                                                        $employee_count = count($employees);
                                                        ?>
                                                        <span class="badge badge-info"><?= $employee_count; ?> Employees</span>
                                                    </td>
                                                    <td>
                                                        <?php 
                                                        $active_issues = $this->db->where('department_id', $dept->id)
                                                                              ->where_in('status', ['open', 'assigned', 'in_progress'])
                                                                              ->count_all_results('support_issues');
                                                        ?>
                                                        <span class="badge badge-warning"><?= $active_issues; ?> Active</span>
                                                    </td>
                                                    <td>
                                                        <?php if ($dept->is_active): ?>
                                                            <span class="badge badge-success">Active</span>
                                                        <?php else: ?>
                                                            <span class="badge badge-secondary">Inactive</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <div class="btn-group btn-group-sm">
                                                            <a href="<?= base_url('CustomerSupport/edit_department/' . $dept->id); ?>" class="btn btn-outline-primary">
                                                                <i class="mdi mdi-pencil"></i>
                                                            </a>
                                                            <button type="button" class="btn btn-outline-info" onclick="viewEmployees(<?= $dept->id; ?>)">
                                                                <i class="mdi mdi-account-multiple"></i>
                                                            </button>
                                                            <?php if ($dept->is_active): ?>
                                                                <button type="button" class="btn btn-outline-warning" onclick="toggleDepartment(<?= $dept->id; ?>, 0)">
                                                                    <i class="mdi mdi-pause"></i>
                                                                </button>
                                                            <?php else: ?>
                                                                <button type="button" class="btn btn-outline-success" onclick="toggleDepartment(<?= $dept->id; ?>, 1)">
                                                                    <i class="mdi mdi-play"></i>
                                                                </button>
                                                            <?php endif; ?>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="8" class="text-center">
                                                    <div class="py-5">
                                                        <i class="mdi mdi-office-building d-block font-size-48 text-muted mb-3"></i>
                                                        <h5 class="text-muted">No departments found</h5>
                                                        <p class="text-muted">Create your first department to get started</p>
                                                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDepartmentModal">
                                                            <i class="mdi mdi-plus"></i> Add Department
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Department Statistics -->
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title mb-4">Department Overview</h4>
                            <div class="row">
                                <?php if (!empty($departments)): ?>
                                    <?php foreach ($departments as $dept): ?>
                                        <div class="col-md-4">
                                            <div class="card border">
                                                <div class="card-body">
                                                    <div class="d-flex">
                                                        <div class="flex-shrink-0">
                                                            <div class="avatar-sm bg-primary rounded">
                                                                <i class="mdi mdi-office-building avatar-title font-size-20"></i>
                                                            </div>
                                                        </div>
                                                        <div class="flex-grow-1 ms-3">
                                                            <h5 class="font-size-16 mb-1"><?= htmlspecialchars($dept->department_name, ENT_QUOTES, 'UTF-8'); ?></h5>
                                                            <p class="text-muted mb-2"><?= htmlspecialchars($dept->department_code, ENT_QUOTES, 'UTF-8'); ?></p>
                                                            
                                                            <?php 
                                                            $dept_stats = $this->db->where('department_id', $dept->id)
                                                                              ->select('COUNT(*) as total, 
                                                                                       SUM(CASE WHEN status = "open" THEN 1 ELSE 0 END) as open_issues,
                                                                                       SUM(CASE WHEN status = "resolved" THEN 1 ELSE 0 END) as resolved_issues')
                                                                              ->get('support_issues')
                                                                              ->row();
                                                            ?>
                                                            
                                                            <div class="row text-center">
                                                                <div class="col-4">
                                                                    <p class="mb-0 text-muted">Total</p>
                                                                    <h6><?= $dept_stats->total ?? 0; ?></h6>
                                                                </div>
                                                                <div class="col-4">
                                                                    <p class="mb-0 text-muted">Open</p>
                                                                    <h6 class="text-warning"><?= $dept_stats->open_issues ?? 0; ?></h6>
                                                                </div>
                                                                <div class="col-4">
                                                                    <p class="mb-0 text-muted">Resolved</p>
                                                                    <h6 class="text-success"><?= $dept_stats->resolved_issues ?? 0; ?></h6>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Department Modal -->
<div class="modal fade" id="addDepartmentModal" tabindex="-1" aria-labelledby="addDepartmentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addDepartmentModalLabel">
                    <i class="mdi mdi-plus"></i> Add New Department
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" action="<?= base_url('CustomerSupport/create_department'); ?>">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="department_name" class="form-label">Department Name *</label>
                                <input type="text" class="form-control" id="department_name" name="department_name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="department_code" class="form-label">Department Code *</label>
                                <input type="text" class="form-control" id="department_code" name="department_code" required placeholder="e.g., TECH, CS, BILL">
                                <div class="form-text">Unique code for department identification</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3" placeholder="Describe what this department handles..."></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="email" name="email" placeholder="department@company.com">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="phone" class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" id="phone" name="phone" placeholder="+1 234 567 8900">
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="manager_id" class="form-label">Department Manager</label>
                        <select class="form-select" id="manager_id" name="manager_id">
                            <option value="">Select Manager (Optional)</option>
                            <?php
                            $users = $this->db->where('position', 'Admin')->or_where('position', 'Manager')->get('users')->result();
                            foreach ($users as $user):
                                $user_name = trim(($user->fName ?? '') . ' ' . ($user->lName ?? ''));
                                if (empty($user_name)) {
                                    $user_name = $user->email ?? 'Unknown';
                                }
                            ?>
                                <option value="<?= $user->user_id; ?>"><?= htmlspecialchars($user_name, ENT_QUOTES, 'UTF-8'); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="mdi mdi-plus"></i> Create Department
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Employees Modal -->
<div class="modal fade" id="employeesModal" tabindex="-1" aria-labelledby="employeesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="employeesModalLabel">
                    <i class="mdi mdi-account-multiple"></i> Department Employees
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="employeesList"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <a href="<?= base_url('CustomerSupport/employee_assignments'); ?>" class="btn btn-primary">
                    <i class="mdi mdi-account-plus"></i> Manage Assignments
                </a>
            </div>
        </div>
    </div>
</div>

<script>
// Initialize DataTable
$(document).ready(function() {
    $('#departmentsTable').DataTable({
        responsive: true,
        order: [[0, 'asc']],
        columnDefs: [
            { orderable: false, targets: 7 }
        ]
    });
});

// View department employees
function viewEmployees(departmentId) {
    $.ajax({
        url: '<?= base_url('CustomerSupport/get_department_employees'); ?>/' + departmentId,
        type: 'GET',
        success: function(response) {
            let html = '<div class="table-responsive"><table class="table table-centered table-nowrap mb-0">';
            html += '<thead><tr><th>Employee Name</th><th>Role</th><th>Email</th><th>Assigned</th></tr></thead><tbody>';
            
            if (response.length > 0) {
                response.forEach(function(employee) {
                    let roleBadge = employee.role === 'manager' ? 'danger' : 
                                   employee.role === 'lead' ? 'warning' : 'info';
                    html += '<tr>';
                    html += '<td>' + employee.employee_name + '</td>';
                    html += '<td><span class="badge badge-' + roleBadge + '">' + employee.role + '</span></td>';
                    html += '<td>' + employee.employee_email + '</td>';
                    html += '<td>' + new Date(employee.assigned_at).toLocaleDateString() + '</td>';
                    html += '</tr>';
                });
            } else {
                html += '<tr><td colspan="4" class="text-center">No employees assigned to this department</td></tr>';
            }
            
            html += '</tbody></table></div>';
            $('#employeesList').html(html);
            $('#employeesModal').modal('show');
        },
        error: function() {
            $('#employeesList').html('<div class="alert alert-danger">Error loading employees</div>');
            $('#employeesModal').modal('show');
        }
    });
}

// Toggle department status
function toggleDepartment(departmentId, status) {
    if (confirm('Are you sure you want to ' + (status ? 'activate' : 'deactivate') + ' this department?')) {
        $.ajax({
            url: '<?= base_url('CustomerSupport/update_department'); ?>/' + departmentId,
            type: 'POST',
            data: {
                is_active: status,
                <?= csrf_token_name(); ?>: '<?= csrf_hash(); ?>'
            },
            success: function(response) {
                location.reload();
            },
            error: function() {
                alert('Error updating department status');
            }
        });
    }
}

// Auto-generate department code
$('#department_name').on('input', function() {
    const name = $(this).val();
    if (name && !$('#department_code').val()) {
        const code = name.toUpperCase()
                         .replace(/[^A-Z\s]/g, '')
                         .split(' ')
                         .map(word => word.substring(0, 3))
                         .join('')
                         .substring(0, 6);
        $('#department_code').val(code);
    }
});
</script>
