<!DOCTYPE html>
<html lang="en">

<?php include('includes/head.php'); ?>


<body>

    <div id="wrapper">

        <?php include('includes/top-nav-bar.php'); ?>
        <?php include('includes/sidebar.php'); ?>

        <div class="content-page">
            <div class="content">
                <div class="container-fluid project-list-page berps-page">


                    <?php
                    $totalProjects = !empty($data) ? count($data) : 0;
                    $userLevel = strtolower(trim((string)$this->session->userdata('level')));
                    $isAdmin = ($userLevel === 'admin');
                    $isStaff = in_array($userLevel, ['staff', 'encoder'], true);

                    $yearSummary = [];
                    $allClientTracker = [];

                    if ($isAdmin && !empty($data)) {
                        foreach ($data as $srow) {
                            $yearKey = 'No Year';
                            if (!empty($srow->contractDate) && $srow->contractDate !== '0000-00-00') {
                                $ts = strtotime($srow->contractDate);
                                if ($ts) {
                                    $yearKey = date('Y', $ts);
                                }
                            }
                            $clientKey = isset($srow->Customer) ? trim((string)$srow->Customer) : '';
                            if ($clientKey === '') {
                                $clientKey = '[NO CLIENT]';
                            }
                            if (!isset($yearSummary[$yearKey])) {
                                $yearSummary[$yearKey] = [];
                            }
                            $yearSummary[$yearKey][$clientKey] = true;
                            $allClientTracker[$clientKey] = true;
                        }
                        if (!empty($yearSummary)) {
                            uksort($yearSummary, function ($a, $b) {
                                if ($a === 'No Year') return 1;
                                if ($b === 'No Year') return -1;
                                return strcmp($b, $a);
                            });
                        }
                    }
                    ?>

                    <?php if ($this->session->flashdata('success')): ?>
                        <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                            <?= html_escape($this->session->flashdata('success')); ?>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    <?php endif; ?>

                    <?php if ($this->session->flashdata('danger')): ?>
                        <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
                            <?= html_escape($this->session->flashdata('danger')); ?>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    <?php endif; ?>

                    <!-- Page header -->
                    <div class="page-header">
                        <div>
                            <div class="page-eyebrow">Project Management</div>
                            <h1 class="page-title">Projects</h1>
                            <div class="page-subtitle">Manage active engagements, view tasks, and track deployment status.</div>
                        </div>
                        <?php if ($isAdmin): ?>
                            <div class="page-actions">
                                <a href="<?= base_url(); ?>Page/addProject" class="btn-submit">
                                    <i class="mdi mdi-briefcase-plus-outline"></i>
                                    Add Project
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Project table card -->
                    <div class="card-stack">
                        <div class="theme-card">
                            <div class="theme-card-head">
                                <div>
                                    <h5 class="theme-card-title">Project Register</h5>
                                    <div class="theme-card-subtitle">All active project engagements with linked tasks and deployment records.</div>
                                </div>
                            </div>

                            <div class="theme-card-body">
                                <div class="table-responsive">
                                    <table id="project-table" class="table table-hover mb-0">
                                        <thead>
                                            <tr>
                                                <th>Category</th>
                                                <th>Project</th>
                                                <th>Client</th>
                                                <th>Contract Date</th>
                                                <?php if ($isAdmin): ?>
                                                    <th class="text-right">Cost</th>
                                                <?php endif; ?>
                                                <th class="text-center">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (!empty($data)): ?>
                                                <?php foreach ($data as $row): ?>
                                                    <?php
                                                    $projectID          = isset($row->projectID)          ? $row->projectID          : '';
                                                    $projectCategory    = isset($row->projectCategory)    ? $row->projectCategory    : '';
                                                    $projectDescription = isset($row->projectDescription) ? $row->projectDescription : '';
                                                    $clientName         = isset($row->Customer)           ? $row->Customer           : '';
                                                    $contractDate       = '—';
                                                    $contractYear       = 'No Year';

                                                    if (!empty($row->contractDate) && $row->contractDate !== '0000-00-00') {
                                                        $timestamp = strtotime($row->contractDate);
                                                        if ($timestamp) {
                                                            $contractDate = date('M d, Y', $timestamp);
                                                            $contractYear = date('Y', $timestamp);
                                                        } else {
                                                            $contractDate = htmlspecialchars($row->contractDate, ENT_QUOTES, 'UTF-8');
                                                        }
                                                    }

                                                    $costValue    = isset($row->projectCost) ? $row->projectCost : '';
                                                    $projectCost  = is_numeric($costValue) ? number_format((float)$costValue, 2) : htmlspecialchars((string)$costValue, ENT_QUOTES, 'UTF-8');
                                                    $projectParam = urlencode((string)$projectID);

                                                    $editUrl       = base_url('Page/updateProject?id=' . $projectParam);
                                                    $deleteUrl     = base_url('Page/deleteProject?id=' . $projectParam);
                                                    $tasksUrl      = base_url('Page/taskPerProject?projectID=' . $projectParam);
                                                    $deploymentUrl = base_url('Page/projectDeploymentStatus?projectID=' . $projectParam);
                                                    ?>
                                                    <tr data-contract-year="<?= htmlspecialchars($contractYear, ENT_QUOTES, 'UTF-8'); ?>">
                                                        <td>
                                                            <?php if ($projectCategory !== ''): ?>
                                                                <span class="category-text"><?= htmlspecialchars($projectCategory, ENT_QUOTES, 'UTF-8'); ?></span>
                                                            <?php else: ?>
                                                                <span class="cell-muted">—</span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td class="project-name"><?= htmlspecialchars($projectDescription, ENT_QUOTES, 'UTF-8'); ?></td>
                                                        <td class="cell-muted"><?= htmlspecialchars($clientName, ENT_QUOTES, 'UTF-8'); ?></td>
                                                        <td class="cell-muted"><?= $contractDate; ?></td>

                                                        <?php if ($isAdmin): ?>
                                                            <td class="text-right">
                                                                <?php if (is_numeric($costValue)): ?>
                                                                    <span class="cost-value"><?= $projectCost; ?></span>
                                                                <?php else: ?>
                                                                    <span class="cell-muted">—</span>
                                                                <?php endif; ?>
                                                            </td>
                                                        <?php endif; ?>

                                                        <td class="text-center">
                                                            <div class="project-actions">
                                                                <?php if ($isAdmin): ?>
                                                                    <a class="action-icon tasks" href="<?= $tasksUrl; ?>" title="View Tasks">
                                                                        <i class="mdi mdi-format-list-checks"></i>
                                                                    </a>
                                                                <?php endif; ?>

                                                                <a class="action-icon deployment" href="<?= $deploymentUrl; ?>" title="Deployment Status">
                                                                    <i class="mdi mdi-clipboard-check-outline"></i>
                                                                </a>

                                                                <?php if ($isAdmin): ?>
                                                                    <a class="action-icon edit" href="<?= $editUrl; ?>" title="Edit Project">
                                                                        <i class="mdi mdi-square-edit-outline"></i>
                                                                    </a>

                                                                    <a class="action-icon delete" href="<?= $deleteUrl; ?>"
                                                                        title="Delete Project"
                                                                        data-berps-confirm="This permanently removes the project record. This action cannot be undone."
                                                                        data-berps-confirm-title="Delete project?"
                                                                        data-berps-confirm-label="Delete project">
                                                                        <i class="mdi mdi-trash-can-outline"></i>
                                                                    </a>
                                                                <?php endif; ?>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Client summary by year (admin only) -->
                    <?php if ($isAdmin): ?>
                        <div class="card-stack">
                            <div class="theme-card">
                                <div class="theme-card-head">
                                    <div>
                                        <h5 class="theme-card-title">Client Summary by Year</h5>
                                        <div class="theme-card-subtitle">Click a year to filter the project table by contract year.</div>
                                    </div>
                                </div>
                                <div class="theme-card-body">
                                    <div class="filter-status-bar">
                                        <i class="mdi mdi-filter-outline"></i>
                                        Showing: <strong id="projectSummaryFilterStatus">All Years</strong>
                                    </div>
                                    <div class="year-grid">
                                        <!-- All years card -->
                                        <button type="button" class="year-card filter-year-card is-active" data-year="">
                                            <div class="year-card-icon"><i class="mdi mdi-calendar-range"></i></div>
                                            <div class="year-card-body">
                                                <div class="year-card-label">All Years</div>
                                                <div class="year-card-count"><?= count($allClientTracker); ?></div>
                                            </div>
                                        </button>

                                        <?php if (!empty($yearSummary)): ?>
                                            <?php foreach ($yearSummary as $yearKey => $clients): ?>
                                                <button type="button" class="year-card filter-year-card" data-year="<?= htmlspecialchars($yearKey, ENT_QUOTES, 'UTF-8'); ?>">
                                                    <div class="year-card-icon"><i class="mdi mdi-calendar-outline"></i></div>
                                                    <div class="year-card-body">
                                                        <div class="year-card-label"><?= htmlspecialchars($yearKey, ENT_QUOTES, 'UTF-8'); ?></div>
                                                        <div class="year-card-count"><?= count($clients); ?></div>
                                                    </div>
                                                </button>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                </div>

            </div>
            <?php include('includes/footer.php'); ?>
        </div>

        <?php include('includes/themecustomizer.php'); ?>

        <script src="<?= base_url(); ?>assets/js/vendor.min.js"></script>
        <script src="<?= base_url(); ?>assets/libs/datatables/jquery.dataTables.min.js"></script>
        <script src="<?= base_url(); ?>assets/libs/datatables/dataTables.bootstrap4.min.js"></script>
        <script src="<?= base_url(); ?>assets/libs/datatables/dataTables.responsive.min.js"></script>
        <script src="<?= base_url(); ?>assets/libs/datatables/responsive.bootstrap4.min.js"></script>
        <script src="<?= base_url(); ?>assets/libs/sweetalert2/sweetalert2.min.js"></script>
        <script src="<?= base_url(); ?>assets/js/app.min.js"></script>

        <script>
            (function($) {
                'use strict';
                $(function() {
                    var $projectTable = $('#project-table');
                    var selectedYear = '';
                    var projectTable = null;

                    $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                        if (!projectTable || !selectedYear) return true;
                        var rowNode = projectTable.row(dataIndex).node();
                        if (!rowNode) return true;
                        return ($(rowNode).attr('data-contract-year') || '') === selectedYear;
                    });

                    if ($.fn.DataTable.isDataTable('#project-table')) {
                        $projectTable.DataTable().destroy();
                    }
                    projectTable = $projectTable.DataTable({
                        responsive: true,
                        autoWidth: false,
                        order: [[1, 'asc']],
                        pageLength: 10,
                        lengthMenu: [10, 25, 50, 100],
                        dom: '<"row align-items-center mb-3"<"col-sm-6"l><"col-sm-6 text-sm-right"f>>' +
                             'rt' +
                             '<"row align-items-center mt-3"<"col-sm-6"i><"col-sm-6"p>>',
                        language: {
                            emptyTable: 'No projects recorded yet.',
                            search: 'Search:',
                            searchPlaceholder: 'Project, client, category...',
                            lengthMenu: 'Show _MENU_ entries',
                            info: 'Showing _START_ to _END_ of _TOTAL_ projects'
                        },
                        columnDefs: [{
                            targets: -1,
                            orderable: false,
                            searchable: false
                        }]
                    });

                    $('.filter-year-card').on('click', function() {
                        if (!projectTable) return;
                        var year = ($(this).data('year') || '').toString();
                        var label = year !== '' ? year : 'All Years';
                        selectedYear = year;
                        $('.filter-year-card').removeClass('is-active');
                        $(this).addClass('is-active');
                        projectTable.draw();
                        $('#projectSummaryFilterStatus').text(label);
                    });
                });
            })(jQuery);
        </script>

        <script>
            (function() {
                var type = "<?= $this->session->flashdata('toast_type'); ?>";
                var text = "<?= $this->session->flashdata('toast_text'); ?>";
                if (type && text && typeof Swal !== 'undefined') {
                    Swal.mixin({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 2200,
                        timerProgressBar: true
                    }).fire({
                        icon: type,
                        title: text
                    });
                }
            })();
        </script>

</body>

</html>
