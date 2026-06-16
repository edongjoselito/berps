<!DOCTYPE html>
<html lang="en">

<?php include('includes/head.php'); ?>

<body>

     <div id="wrapper">

          <?php include('includes/top-nav-bar.php'); ?>
          <?php include('includes/sidebar.php'); ?>

          <div class="content-page">
               <div class="content">
                    <div class="container-fluid product-list-page">
                         <style>
                              @import url('https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&display=swap');

                              .product-list-page {
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
                                   --success-soft: #ecfdf5;
                                   --warning: #d97706;
                                   --warning-soft: #fff7ed;
                                   --danger: #e11d48;
                                   --danger-soft: #fff1f2;
                                   --shadow: 0 16px 40px rgba(15, 23, 42, 0.06);
                                   --shadow-soft: 0 8px 24px rgba(15, 23, 42, 0.04);
                                   --radius-xl: 16px;
                                   --radius-lg: 12px;
                                   --radius-md: 10px;
                                   --radius-sm: 8px;
                                   --font-body: 'DM Sans', 'Segoe UI', Arial, sans-serif;
                                   --font-head: 'DM Sans', 'Segoe UI', Arial, sans-serif;
                                   --font-mono: 'DM Sans', 'SFMono-Regular', Consolas, monospace;
                                   background:
                                        radial-gradient(circle at top left, rgba(37, 99, 235, 0.08), transparent 28%),
                                        radial-gradient(circle at top right, rgba(16, 185, 129, 0.08), transparent 24%),
                                        linear-gradient(180deg, #f8fbff 0%, #f4f7fb 100%);
                                   min-height: 100vh;
                                   padding-bottom: 100px;
                                   font-family: var(--font-body);
                              }

                              .product-list-page * {
                                   box-sizing: border-box;
                              }

                              .product-list-page .content {
                                   margin-bottom: 40px;
                              }

                              .product-list-page .page-header {
                                   display: flex;
                                   justify-content: space-between;
                                   align-items: flex-end;
                                   gap: 16px;
                                   margin: 16px 0 16px;
                                   flex-wrap: wrap;
                              }

                              .product-list-page .page-eyebrow {
                                   display: inline-flex;
                                   align-items: center;
                                   gap: 8px;
                                   padding: 7px 12px;
                                   border-radius: 999px;
                                   background: rgba(37, 99, 235, 0.08);
                                   color: var(--primary-2);
                                   font-size: 0.74rem;
                                   font-weight: 700;
                                   letter-spacing: 0.08em;
                                   text-transform: uppercase;
                                   margin-bottom: 12px;
                              }

                              .product-list-page .page-eyebrow::before {
                                   content: '';
                                   width: 8px;
                                   height: 8px;
                                   border-radius: 50%;
                                   background: linear-gradient(135deg, var(--primary), var(--primary-2));
                                   box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.10);
                              }

                              .product-list-page .page-title {
                                   margin: 0;
                                   font-family: var(--font-head);
                                   font-size: 1.5rem;
                                   line-height: 1.2;
                                   letter-spacing: -0.02em;
                                   font-weight: 700;
                                   color: var(--text);
                              }

                              .product-list-page .page-actions {
                                   display: flex;
                                   gap: 12px;
                                   flex-wrap: wrap;
                              }

                              .product-list-page .btn-action,
                              .product-list-page .btn-submit {
                                   display: inline-flex;
                                   align-items: center;
                                   justify-content: center;
                                   gap: 8px;
                                   border-radius: 12px;
                                   font-size: 0.92rem;
                                   font-weight: 700;
                                   padding: 11px 18px;
                                   transition: all 0.16s ease;
                                   text-decoration: none;
                              }

                              .product-list-page .btn-action {
                                   border: 1px solid var(--line-strong);
                                   color: var(--text);
                                   background: #fff;
                              }

                              .product-list-page .btn-action:hover {
                                   color: var(--primary);
                                   border-color: #bfd3ef;
                                   background: #f9fbff;
                              }

                              .product-list-page .btn-submit {
                                   border: none;
                                   color: #fff;
                                   background: linear-gradient(135deg, var(--primary), var(--primary-2));
                                   box-shadow: 0 10px 24px rgba(37, 99, 235, 0.24);
                              }

                              .product-list-page .btn-submit:hover {
                                   transform: translateY(-1px);
                                   box-shadow: 0 14px 28px rgba(37, 99, 235, 0.28);
                              }

                              .product-list-page .theme-card {
                                   background: var(--surface);
                                   border: 1px solid rgba(255, 255, 255, 0.72);
                                   border-radius: var(--radius-xl);
                                   box-shadow: var(--shadow-soft);
                                   overflow: hidden;
                              }

                              .product-list-page .theme-card-head {
                                   display: flex;
                                   align-items: center;
                                   justify-content: space-between;
                                   gap: 12px;
                                   padding: 14px 18px;
                                   border-bottom: 1px solid var(--line);
                                   background: linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(249, 251, 255, 0.94));
                                   flex-wrap: wrap;
                              }

                              .product-list-page .theme-card-title {
                                   margin: 0;
                                   color: var(--text);
                                   font-size: 0.95rem;
                                   font-weight: 700;
                                   letter-spacing: -0.01em;
                              }

                              .product-list-page .theme-card-body {
                                   padding: 18px;
                              }

                              .product-list-page .table thead th {
                                   background: transparent;
                                   color: var(--text-faint);
                                   font-size: 0.72rem;
                                   font-weight: 800;
                                   text-transform: uppercase;
                                   letter-spacing: 0.08em;
                                   border-top: none;
                                   border-bottom: 1px solid var(--line);
                                   white-space: nowrap;
                                   vertical-align: middle;
                              }

                              .product-list-page .table td {
                                   vertical-align: middle;
                                   border-color: var(--line);
                                   color: var(--text);
                              }

                              .product-list-page .product-actions {
                                   display: inline-flex;
                                   gap: 4px;
                              }

                              .product-list-page .action-icon {
                                   width: 32px;
                                   height: 32px;
                                   border-radius: var(--radius-sm);
                                   display: inline-flex;
                                   align-items: center;
                                   justify-content: center;
                                   font-size: 16px;
                                   text-decoration: none;
                                   transition: all 0.16s ease;
                              }

                              .product-list-page .action-icon.edit {
                                   color: var(--primary);
                                   background: var(--primary-soft);
                              }

                              .product-list-page .action-icon.edit:hover {
                                   background: #dbeafe;
                              }

                              .product-list-page .action-icon.delete {
                                   color: var(--danger);
                                   background: var(--danger-soft);
                              }

                              .product-list-page .action-icon.delete:hover {
                                   background: #fecdd3;
                              }
                         </style>

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

                                   $clientKey = '';

                                   if (isset($srow->Customer) && trim((string)$srow->Customer) !== '') {
                                        $clientKey = trim((string)$srow->Customer);
                                   } elseif (isset($srow->customer_from_clients) && trim((string)$srow->customer_from_clients) !== '') {
                                        $clientKey = trim((string)$srow->customer_from_clients);
                                   }

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

                         <div class="row">
                              <div class="col-12">
                                   <div class="title-wrap">
                                        <h4 class="page-title">
                                             Projects<br>
                                             <span class="badge badge-purple title-sub">
                                                  <?= $isStaff ? 'Projects assigned to you' : 'Manage active engagements'; ?>
                                             </span>
                                        </h4>
                                   </div>
                                   <hr class="title-divider">
                              </div>
                         </div>

                         <div class="card mb-3">
                              <div class="card-body d-flex flex-wrap justify-content-between align-items-center">
                                   <h5 class="mb-0">
                                        Total Projects:
                                        <span class="font-weight-bold text-primary"><?= $totalProjects; ?></span>
                                   </h5>

                                   <?php if ($isAdmin): ?>
                                        <a href="<?= base_url(); ?>Page/addProject" class="btn btn-primary">
                                             <i class="mdi mdi-briefcase-plus mr-1"></i>Add Project
                                        </a>
                                   <?php endif; ?>
                              </div>
                         </div>

                         <?php if ($this->session->flashdata('success')): ?>
                              <div class="alert alert-success alert-dismissible fade show" role="alert">
                                   <?= html_escape($this->session->flashdata('success')); ?>
                                   <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                   </button>
                              </div>
                         <?php endif; ?>

                         <?php if ($this->session->flashdata('danger')): ?>
                              <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                   <?= html_escape($this->session->flashdata('danger')); ?>
                                   <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                   </button>
                              </div>
                         <?php endif; ?>

                         <div class="card">
                              <div class="card-body">
                                   <div class="table-responsive data-table-container loading">
                                        <table id="project-table" class="table table-hover table-striped table-centered mb-0 table-init-hidden">
                                             <thead class="bg-secondary text-white">
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
                                                            $projectID          = isset($row->projectID) ? $row->projectID : '';
                                                            $projectCategory    = isset($row->projectCategory) ? $row->projectCategory : '';
                                                            $projectDescription = isset($row->projectDescription) ? $row->projectDescription : '';

                                                            $clientName = '';
                                                            if (isset($row->Customer) && trim((string)$row->Customer) !== '') {
                                                                 $clientName = (string)$row->Customer;
                                                            } elseif (isset($row->customer_from_clients) && trim((string)$row->customer_from_clients) !== '') {
                                                                 $clientName = (string)$row->customer_from_clients;
                                                            }

                                                            $contractDate = '-';
                                                            $contractYear = 'No Year';

                                                            if (!empty($row->contractDate) && $row->contractDate !== '0000-00-00') {
                                                                 $timestamp = strtotime($row->contractDate);
                                                                 if ($timestamp) {
                                                                      $contractDate = date('M d, Y', $timestamp);
                                                                      $contractYear = date('Y', $timestamp);
                                                                 } else {
                                                                      $contractDate = htmlspecialchars($row->contractDate, ENT_QUOTES, 'UTF-8');
                                                                 }
                                                            }

                                                            $costValue       = isset($row->projectCost) ? $row->projectCost : '';
                                                            $projectCost     = is_numeric($costValue) ? number_format((float)$costValue, 2) : htmlspecialchars((string)$costValue, ENT_QUOTES, 'UTF-8');
                                                            $rankedCostClass = is_numeric($costValue) ? 'text-primary font-weight-semibold' : '';
                                                            $projectParam    = urlencode((string)$projectID);

                                                            $editUrl       = base_url('Page/updateProject?id=' . $projectParam);
                                                            $deleteUrl     = base_url('Page/deleteProject?id=' . $projectParam);
                                                            $tasksUrl      = base_url('Page/taskPerProject?projectID=' . $projectParam);
                                                            $deploymentUrl = base_url('Page/projectDeploymentStatus?projectID=' . $projectParam);
                                                            ?>
                                                            <tr data-contract-year="<?= htmlspecialchars($contractYear, ENT_QUOTES, 'UTF-8'); ?>">
                                                                 <td><?= htmlspecialchars($projectCategory, ENT_QUOTES, 'UTF-8'); ?></td>
                                                                 <td class="font-weight-semibold"><?= htmlspecialchars($projectDescription, ENT_QUOTES, 'UTF-8'); ?></td>
                                                                 <td><?= htmlspecialchars($clientName, ENT_QUOTES, 'UTF-8'); ?></td>
                                                                 <td><?= $contractDate; ?></td>

                                                                 <?php if ($isAdmin): ?>
                                                                      <td class="text-right <?= $rankedCostClass; ?>"><?= $projectCost; ?></td>
                                                                 <?php endif; ?>

                                                                 <td class="text-center">
                                                                      <div class="project-actions">
                                                                           <?php if ($isAdmin): ?>
                                                                                <a class="action-icon tasks" href="<?= $tasksUrl; ?>" data-label="View Tasks" title="View Tasks">
                                                                                     <i class="mdi mdi-format-list-checks"></i>
                                                                                     <span class="sr-only">View Tasks</span>
                                                                                </a>
                                                                           <?php endif; ?>

                                                                           <a class="action-icon deployment" href="<?= $deploymentUrl; ?>" data-label="Deployment Status" title="Deployment Status">
                                                                                <i class="mdi mdi-clipboard-check-outline"></i>
                                                                                <span class="sr-only">Deployment Status</span>
                                                                           </a>

                                                                           <?php if ($isAdmin): ?>
                                                                                <a class="action-icon edit" href="<?= $editUrl; ?>" data-label="Edit" title="Edit Project">
                                                                                     <i class="mdi mdi-square-edit-outline"></i>
                                                                                     <span class="sr-only">Edit</span>
                                                                                </a>

                                                                                <a class="action-icon delete" href="<?= $deleteUrl; ?>" onclick="return confirm('Are you sure you want to delete this project?');" data-label="Delete" title="Delete Project">
                                                                                     <i class="mdi mdi-trash-can-outline"></i>
                                                                                     <span class="sr-only">Delete</span>
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

                         <?php if ($isAdmin): ?>
                              <div class="summary-wrap">
                                   <div class="card">
                                        <div class="card-body">
                                             <h5 class="mb-1">Client Summary by Year</h5>
                                             <p class="text-muted mb-3">Click a year card to display the projects under that contract year.</p>
                                             <div id="projectSummaryFilterStatus" class="summary-filter-status mb-3">Showing: All Years</div>

                                             <div class="row">
                                                  <div class="col-md-3 mb-3">
                                                       <div class="summary-card-box all-box filter-year-card is-active" data-year="">
                                                            <h5>All Years</h5>
                                                            <div class="summary-count"><?= count($allClientTracker); ?></div>
                                                            <p class="summary-note">Total unique clients</p>
                                                       </div>
                                                  </div>

                                                  <?php if (!empty($yearSummary)): ?>
                                                       <?php foreach ($yearSummary as $yearKey => $clients): ?>
                                                            <div class="col-md-3 mb-3">
                                                                 <div class="summary-card-box year-box filter-year-card" data-year="<?= htmlspecialchars($yearKey, ENT_QUOTES, 'UTF-8'); ?>">
                                                                      <h5><?= htmlspecialchars($yearKey, ENT_QUOTES, 'UTF-8'); ?></h5>
                                                                      <div class="summary-count"><?= count($clients); ?></div>
                                                                      <p class="summary-note">Unique clients</p>
                                                                 </div>
                                                            </div>
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
     </div>

     <?php include('includes/themecustomizer.php'); ?>

     <script src="<?= base_url(); ?>assets/js/vendor.min.js"></script>
     <script src="<?= base_url(); ?>assets/libs/moment/moment.min.js"></script>
     <script src="<?= base_url(); ?>assets/libs/jquery-scrollto/jquery.scrollTo.min.js"></script>
     <script src="<?= base_url(); ?>assets/libs/sweetalert2/sweetalert2.min.js"></script>
     <script src="<?= base_url(); ?>assets/libs/fullcalendar/fullcalendar.min.js"></script>
     <script src="<?= base_url(); ?>assets/js/pages/calendar.init.js"></script>
     <script src="<?= base_url(); ?>assets/js/pages/jquery.chat.js"></script>
     <script src="<?= base_url(); ?>assets/js/pages/jquery.todo.js"></script>
     <script src="<?= base_url(); ?>assets/libs/morris-js/morris.min.js"></script>
     <script src="<?= base_url(); ?>assets/libs/raphael/raphael.min.js"></script>
     <script src="<?= base_url(); ?>assets/libs/jquery-sparkline/jquery.sparkline.min.js"></script>
     <script src="<?= base_url(); ?>assets/js/pages/dashboard.init.js"></script>
     <script src="<?= base_url(); ?>assets/js/app.min.js"></script>
     <script src="<?= base_url(); ?>assets/libs/jquery-ui/jquery-ui.min.js"></script>
     <script src="<?= base_url(); ?>assets/libs/datatables/jquery.dataTables.min.js"></script>
     <script src="<?= base_url(); ?>assets/libs/datatables/dataTables.bootstrap4.min.js"></script>
     <script src="<?= base_url(); ?>assets/libs/datatables/dataTables.buttons.min.js"></script>
     <script src="<?= base_url(); ?>assets/libs/datatables/buttons.bootstrap4.min.js"></script>
     <script src="<?= base_url(); ?>assets/libs/jszip/jszip.min.js"></script>
     <script src="<?= base_url(); ?>assets/libs/pdfmake/pdfmake.min.js"></script>
     <script src="<?= base_url(); ?>assets/libs/pdfmake/vfs_fonts.js"></script>
     <script src="<?= base_url(); ?>assets/libs/datatables/buttons.html5.min.js"></script>
     <script src="<?= base_url(); ?>assets/libs/datatables/buttons.print.min.js"></script>
     <script src="<?= base_url(); ?>assets/libs/datatables/dataTables.responsive.min.js"></script>
     <script src="<?= base_url(); ?>assets/libs/datatables/responsive.bootstrap4.min.js"></script>
     <script src="<?= base_url(); ?>assets/libs/datatables/dataTables.keyTable.min.js"></script>
     <script src="<?= base_url(); ?>assets/libs/datatables/dataTables.select.min.js"></script>

     <script>
          (function($) {
               'use strict';
               $(function() {
                    var $tableContainer = $('.project-list-page .data-table-container');
                    var $projectTable = $('#project-table');
                    var selectedYear = '';
                    var projectTable = null;

                    $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                         if (!projectTable) return true;
                         if (!selectedYear) return true;

                         var rowNode = projectTable.row(dataIndex).node();
                         if (!rowNode) return true;

                         var rowYear = ($(rowNode).attr('data-contract-year') || '').toString();
                         return rowYear === selectedYear;
                    });

                    projectTable = $projectTable.DataTable({
                         responsive: true,
                         autoWidth: false,
                         order: [
                              [1, 'asc']
                         ],
                         language: {
                              emptyTable: 'No projects recorded yet.'
                         },
                         columnDefs: [{
                              targets: -1,
                              orderable: false,
                              searchable: false
                         }],
                         initComplete: function() {
                              $projectTable.removeClass('table-init-hidden').addClass('table-init-ready');
                              $tableContainer.removeClass('loading').addClass('ready');
                         }
                    });

                    $('.filter-year-card').on('click', function() {
                         if (!projectTable) return;

                         var year = ($(this).data('year') || '').toString();
                         var label = year !== '' ? year : 'All Years';

                         selectedYear = year;

                         $('.filter-year-card').removeClass('is-active');
                         $(this).addClass('is-active');

                         projectTable.draw();
                         $('#projectSummaryFilterStatus').text('Showing: ' + label);
                    });
               });
          })(jQuery);
     </script>

     <script>
          (function() {
               var type = "<?= $this->session->flashdata('toast_type'); ?>";
               var text = "<?= $this->session->flashdata('toast_text'); ?>";
               if (type && text && typeof Swal !== 'undefined') {
                    const Toast = Swal.mixin({
                         toast: true,
                         position: 'top-end',
                         showConfirmButton: false,
                         timer: 2200,
                         timerProgressBar: true
                    });
                    Toast.fire({
                         icon: type,
                         title: text
                    });
               }
          })();
     </script>

</body>

</html>
