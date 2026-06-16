<div class="content-page">
    <div class="content">
        <div class="container-fluid">
            <!-- Page Title -->
            <div class="page-title-box">
                <div class="row align-items-center">
                    <div class="col-sm-6">
                        <h4 class="page-title">Employee Department Assignments</h4>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?= base_url('CustomerSupport'); ?>">Customer Support</a></li>
                            <li class="breadcrumb-item active">Employee Assignments</li>
                        </ol>
                    </div>
                    <div class="col-sm-6">
                        <div class="float-right d-none d-md-block">
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#assignEmployeeModal">
                                <i class="mdi mdi-account-plus"></i> Assign Employee
                            </button>
                            <a href="<?= base_url('CustomerSupport/departments'); ?>" class="btn btn-info ml-2">
                                <i class="mdi mdi-office-building"></i> Departments
                            </a>
                        </div>
                    </div>
                </div>
            </div>

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

            <!-- Assignment Statistics -->
            <div class="row">
                <div class="col-xl-3 col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="avatar-sm bg-primary rounded">
                                        <i class="mdi mdi-account-multiple avatar-title font-size-24"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <p class="text-muted mb-1">Total Employees</p>
                                    <h4 class="mb-0"><?= count($employees); ?></h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-3 col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="avatar-sm bg-success rounded">
                                        <i class="mdi mdi-office-building avatar-title font-size-24"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <p class="text-muted mb-1">Departments</p>
                                    <h4 class="mb-0"><?= count($departments); ?></h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-3 col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="avatar-sm bg-info rounded">
                                        <i class="mdi mdi-account-check avatar-title font-size-24"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <p class="text-muted mb-1">Assigned Employees</p>
                                    <h4 class="mb-0">
                                        <?php
                                        $assigned_count = 0;
                                        foreach ($employees as $emp) {
                                            $assignments = $this->CustomerSupport_model->get_employee_departments($emp->user_id);
                                            if (!empty($assignments)) $assigned_count++;
                                        }
                                        echo $assigned_count;
                                        ?>
                                    </h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-3 col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="avatar-sm bg-warning rounded">
                                        <i class="mdi mdi-account-remove avatar-title font-size-24"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <p class="text-muted mb-1">Unassigned Employees</p>
                                    <h4 class="mb-0"><?= count($employees) - $assigned_count; ?></h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Employee Assignments Table -->
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-centered table-nowrap mb-0" id="assignmentsTable">
                                    <thead>
                                        <tr>
                                            <th>Employee</th>
                                            <th>Role</th>
                                            <th>Departments</th>
                                            <th>Workload</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($employees)): ?>
                                            <?php foreach ($employees as $employee): ?>
                                                <?php
                                                $assignments = $this->CustomerSupport_model->get_employee_departments($employee->user_id);
                                                $dept_names = [];
                                                $total_issues = 0;
                                                $open_issues = 0;

                                                foreach ($assignments as $assignment) {
                                                    $dept_names[] = $assignment->department_name;

                                                    // Get workload stats
                                                    $workload = $this->db->where('assigned_employee_id', $employee->user_id)
                                                                      ->select('COUNT(*) as total,
                                                                               SUM(CASE WHEN status IN ("open", "assigned", "in_progress") THEN 1 ELSE 0 END) as open')
                                                                      ->get('support_issues')
                                                                      ->row();
                                                    $total_issues = $workload->total ?? 0;
                                                    $open_issues = $workload->open ?? 0;
                                                }

                                                $employee_name = trim(($employee->fName ?? '') . ' ' . ($employee->lName ?? ''));
                                                if (empty($employee_name)) {
                                                    $employee_name = $employee->email ?? 'Unknown';
                                                }
                                                $employee_initials = strtoupper(substr($employee_name, 0, 2));
                                                ?>
                                                <tr>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <div class="flex-shrink-0">
                                                                <div class="avatar-sm bg-primary rounded">
                                                                    <span class="avatar-title font-size-16">
                                                                        <?= htmlspecialchars($employee_initials, ENT_QUOTES, 'UTF-8'); ?>
                                                                    </span>
                                                                </div>
                                                            </div>
                                                            <div class="flex-grow-1 ms-3">
                                                                <h5 class="font-size-14 mb-1"><?= htmlspecialchars($employee_name, ENT_QUOTES, 'UTF-8'); ?></h5>
                                                                <p class="text-muted mb-0"><?= htmlspecialchars($employee->email ?? '', ENT_QUOTES, 'UTF-8'); ?></p>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <span class="badge badge-<?= ($employee->position ?? '') === 'Admin' ? 'danger' : (($employee->position ?? '') === 'Manager' ? 'warning' : 'info'); ?>">
                                                            <?= htmlspecialchars($employee->position ?? 'Staff', ENT_QUOTES, 'UTF-8'); ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <?php if (!empty($dept_names)): ?>
                                                            <?php foreach ($dept_names as $dept_name): ?>
                                                                <span class="badge badge-primary me-1"><?= htmlspecialchars($dept_name, ENT_QUOTES, 'UTF-8'); ?></span>
                                                            <?php endforeach; ?>
                                                        <?php else: ?>
                                                            <span class="text-muted">No departments assigned</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if ($total_issues > 0): ?>
                                                            <div class="d-flex align-items-center">
                                                                <span class="badge badge-info me-2"><?= $total_issues; ?> Total</span>
                                                                <span class="badge badge-warning"><?= $open_issues; ?> Open</span>
                                                            </div>
                                                        <?php else: ?>
                                                            <span class="text-muted">No issues</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if (!empty($assignments)): ?>
                                                            <span class="badge badge-success">Active</span>
                                                        <?php else: ?>
                                                            <span class="badge badge-secondary">Unassigned</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <div class="btn-group btn-group-sm">
                                                            <button type="button" class="btn btn-outline-primary" onclick="editAssignments(<?= $employee->user_id; ?>)">
                                                                <i class="mdi mdi-pencil"></i>
                                                            </button>
                                                            <button type="button" class="btn btn-outline-info" onclick="viewWorkload(<?= $employee->user_id; ?>)">
                                                                <i class="mdi mdi-chart-line"></i>
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="6" class="text-center">
                                                    <div class="py-5">
                                                        <i class="mdi mdi-account-multiple d-block font-size-48 text-muted mb-3"></i>
                                                        <h5 class="text-muted">No employees found</h5>
                                                        <p class="text-muted">Add employees to the system first</p>
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

            <!-- Department Overview -->
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title mb-4">Department Employee Distribution</h4>
                            <div class="row">
                                <?php if (!empty($departments)): ?>
                                    <?php foreach ($departments as $dept): ?>
                                        <?php 
                                        $dept_employees = $this->CustomerSupport_model->get_department_employees($dept->id);
                                        $employee_count = count($dept_employees);
                                        ?>
                                        <div class="col-md-3">
                                            <div class="card border">
                                                <div class="card-body text-center">
                                                    <i class="mdi mdi-office-building d-block font-size-32 text-primary mb-2"></i>
                                                    <h5 class="font-size-16"><?= htmlspecialchars($dept->department_name, ENT_QUOTES, 'UTF-8'); ?></h5>
                                                    <div class="mt-3">
                                                        <h3 class="mb-0"><?= $employee_count; ?></h3>
                                                        <p class="text-muted">Employees</p>
                                                    </div>
                                                    
                                                    <?php if ($employee_count > 0): ?>
                                                        <div class="mt-2">
                                                            <?php foreach (array_slice($dept_employees, 0, 3) as $emp): ?>
                                                                <small class="d-block text-muted"><?= htmlspecialchars($emp->employee_name, ENT_QUOTES, 'UTF-8'); ?></small>
                                                            <?php endforeach; ?>
                                                            <?php if ($employee_count > 3): ?>
                                                                <small class="text-muted">+<?= $employee_count - 3; ?> more</small>
                                                            <?php endif; ?>
                                                        </div>
                                                    <?php endif; ?>
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

<!-- Assign Employee Modal -->
<div class="modal fade" id="assignEmployeeModal" tabindex="-1" aria-labelledby="assignEmployeeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="assignEmployeeModalLabel">
                    <i class="mdi mdi-account-plus"></i> Assign Employee to Department
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" action="<?= base_url('CustomerSupport/assign_employee'); ?>">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="employee_id" class="form-label">Select Employee *</label>
                                <select class="form-select" id="employee_id" name="employee_id" required>
                                    <option value="">Choose an employee</option>
                                    <?php foreach ($employees as $employee): ?>
                                        <?php
                                        $emp_name = trim(($employee->fName ?? '') . ' ' . ($employee->lName ?? ''));
                                        if (empty($emp_name)) {
                                            $emp_name = $employee->email ?? 'Unknown';
                                        }
                                        ?>
                                        <option value="<?= $employee->user_id; ?>"><?= htmlspecialchars($emp_name, ENT_QUOTES, 'UTF-8'); ?> (<?= htmlspecialchars($employee->position ?? 'Staff', ENT_QUOTES, 'UTF-8'); ?>)</option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="department_id" class="form-label">Select Department *</label>
                                <select class="form-select" id="department_id" name="department_id" required>
                                    <option value="">Choose a department</option>
                                    <?php foreach ($departments as $dept): ?>
                                        <option value="<?= $dept->id; ?>"><?= htmlspecialchars($dept->department_name, ENT_QUOTES, 'UTF-8'); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="role" class="form-label">Role in Department</label>
                        <select class="form-select" id="role" name="role">
                            <option value="member">Team Member</option>
                            <option value="lead">Team Lead</option>
                            <option value="manager">Department Manager</option>
                        </select>
                        <div class="form-text">Manager role gives administrative privileges for this department</div>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="mdi mdi-information"></i>
                        <strong>Note:</strong> Employees can be assigned to multiple departments. Each assignment can have different roles.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="mdi mdi-account-plus"></i> Assign Employee
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Assignments Modal -->
<div class="modal fade" id="editAssignmentsModal" tabindex="-1" aria-labelledby="editAssignmentsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editAssignmentsModalLabel">
                    <i class="mdi mdi-pencil"></i> Edit Employee Assignments
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="editAssignmentsContent"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Workload Modal -->
<div class="modal fade" id="workloadModal" tabindex="-1" aria-labelledby="workloadModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="workloadModalLabel">
                    <i class="mdi mdi-chart-line"></i> Employee Workload
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="workloadContent"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
// Initialize DataTable
$(document).ready(function() {
    $('#assignmentsTable').DataTable({
        responsive: true,
        order: [[0, 'asc']],
        columnDefs: [
            { orderable: false, targets: 5 }
        ]
    });
});

// Edit employee assignments
function editAssignments(employeeId) {
    $.ajax({
        url: '<?= base_url('CustomerSupport/get_employee_assignments'); ?>/' + employeeId,
        type: 'GET',
        success: function(response) {
            let html = '<div class="table-responsive"><table class="table table-centered table-nowrap mb-0">';
            html += '<thead><tr><th>Department</th><th>Role</th><th>Assigned Date</th><th>Actions</th></tr></thead><tbody>';
            
            if (response.assignments && response.assignments.length > 0) {
                response.assignments.forEach(function(assignment) {
                    let roleBadge = assignment.role === 'manager' ? 'danger' : 
                                   assignment.role === 'lead' ? 'warning' : 'info';
                    html += '<tr>';
                    html += '<td>' + assignment.department_name + '</td>';
                    html += '<td><span class="badge badge-' + roleBadge + '">' + assignment.role + '</span></td>';
                    html += '<td>' + new Date(assignment.assigned_at).toLocaleDateString() + '</td>';
                    html += '<td>';
                    html += '<button type="button" class="btn btn-sm btn-danger" onclick="removeAssignment(' + employeeId + ', ' + assignment.department_id + ')">';
                    html += '<i class="mdi mdi-delete"></i> Remove';
                    html += '</button>';
                    html += '</td>';
                    html += '</tr>';
                });
            } else {
                html += '<tr><td colspan="4" class="text-center">No department assignments found</td></tr>';
            }
            
            html += '</tbody></table></div>';
            $('#editAssignmentsContent').html(html);
            $('#editAssignmentsModal').modal('show');
        },
        error: function() {
            $('#editAssignmentsContent').html('<div class="alert alert-danger">Error loading assignments</div>');
            $('#editAssignmentsModal').modal('show');
        }
    });
}

// View employee workload
function viewWorkload(employeeId) {
    $.ajax({
        url: '<?= base_url('CustomerSupport/get_employee_workload'); ?>/' + employeeId,
        type: 'GET',
        success: function(response) {
            let html = '<div class="row">';
            html += '<div class="col-md-6">';
            html += '<div class="card">';
            html += '<div class="card-body">';
            html += '<h5 class="card-title">Issue Statistics</h5>';
            html += '<div class="row text-center">';
            html += '<div class="col-4"><p class="mb-0 text-muted">Total</p><h4>' + response.total_issues + '</h4></div>';
            html += '<div class="col-4"><p class="mb-0 text-muted">Open</p><h4 class="text-warning">' + response.open_issues + '</h4></div>';
            html += '<div class="col-4"><p class="mb-0 text-muted">Resolved</p><h4 class="text-success">' + response.resolved_issues + '</h4></div>';
            html += '</div>';
            html += '</div>';
            html += '</div>';
            html += '</div>';
            
            html += '<div class="col-md-6">';
            html += '<div class="card">';
            html += '<div class="card-body">';
            html += '<h5 class="card-title">Performance Metrics</h5>';
            html += '<p class="mb-2"><strong>Average Resolution Time:</strong> ' + response.avg_resolution_time + ' hours</p>';
            html += '<p class="mb-2"><strong>Issues This Week:</strong> ' + response.issues_this_week + '</p>';
            html += '<p class="mb-0"><strong>Urgent Issues:</strong> ' + response.urgent_issues + '</p>';
            html += '</div>';
            html += '</div>';
            html += '</div>';
            html += '</div>';
            
            if (response.recent_issues && response.recent_issues.length > 0) {
                html += '<div class="col-12 mt-3">';
                html += '<h5>Recent Issues</h5>';
                html += '<div class="table-responsive"><table class="table table-sm">';
                html += '<thead><tr><th>Ticket #</th><th>Title</th><th>Status</th><th>Priority</th></tr></thead><tbody>';
                
                response.recent_issues.forEach(function(issue) {
                    html += '<tr>';
                    html += '<td>' + issue.ticket_number + '</td>';
                    html += '<td>' + issue.title + '</td>';
                    html += '<td><span class="badge badge-info">' + issue.status + '</span></td>';
                    html += '<td><span class="badge badge-warning">' + issue.priority + '</span></td>';
                    html += '</tr>';
                });
                
                html += '</tbody></table></div>';
                html += '</div>';
            }
            
            html += '</div>';
            $('#workloadContent').html(html);
            $('#workloadModal').modal('show');
        },
        error: function() {
            $('#workloadContent').html('<div class="alert alert-danger">Error loading workload data</div>');
            $('#workloadModal').modal('show');
        }
    });
}

// Remove assignment
function removeAssignment(employeeId, departmentId) {
    if (confirm('Are you sure you want to remove this employee from the department?')) {
        window.location.href = '<?= base_url('CustomerSupport/remove_employee_assignment'); ?>/' + employeeId + '/' + departmentId;
    }
}

// Check for duplicate assignments
$('#employee_id, #department_id').on('change', function() {
    const employeeId = $('#employee_id').val();
    const departmentId = $('#department_id').val();
    
    if (employeeId && departmentId) {
        // Check if assignment already exists
        $.ajax({
            url: '<?= base_url('CustomerSupport/check_assignment'); ?>',
            type: 'POST',
            data: {
                employee_id: employeeId,
                department_id: departmentId
            },
            success: function(response) {
                if (response.exists) {
                    $('.modal-footer .btn-primary').prop('disabled', true);
                    $('.modal-body').append('<div class="alert alert-warning mt-3">This employee is already assigned to this department.</div>');
                } else {
                    $('.modal-footer .btn-primary').prop('disabled', false);
                    $('.modal-body .alert').remove();
                }
            }
        });
    }
});
</script>
