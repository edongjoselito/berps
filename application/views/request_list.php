<!DOCTYPE html>
<html lang="en">

<?php include('includes/head.php'); ?>

<body>

     <div id="wrapper">

          <?php include('includes/top-nav-bar.php'); ?>
          <?php include('includes/sidebar.php'); ?>

          <div class="content-page">
               <div class="content">
                    <div class="container-fluid request-page">
                         <style>
                              .request-page .page-header-card {
                                   border: none;
                                   border-radius: 18px;
                                   box-shadow: 0 16px 32px rgba(15, 23, 42, 0.08);
                                   margin-bottom: 28px;
                                   overflow: hidden;
                              }

                              .request-page .page-header-card .card-header {
                                   background: linear-gradient(135deg, #4c6ef5, #845ef7);
                                   color: #fff;
                                   border-bottom: none;
                                   padding: 28px 32px;
                              }

                              .request-page .page-header-card .card-header h3 {
                                   margin: 0;
                                   font-size: 1.6rem;
                                   font-weight: 600;
                              }

                              .request-page .page-header-card .card-body {
                                   padding: 24px 32px;
                                   background: #fff;
                              }

                              .request-page .page-header-card .breadcrumb {
                                   background: transparent;
                                   padding: 0;
                                   margin-bottom: 0;
                              }

                              .request-page .card {
                                   border: none;
                                   border-radius: 18px;
                                   box-shadow: 0 14px 28px rgba(15, 23, 42, 0.07);
                              }

                              .request-page .card table th {
                                   text-transform: uppercase;
                                   letter-spacing: 0.05em;
                                   font-size: 0.78rem;
                                   white-space: nowrap;
                              }

                              .request-page .notification-row.unread {
                                   background: rgba(255, 193, 7, 0.12);
                              }

                              .request-page .badge-status {
                                   font-size: 0.75rem;
                                   letter-spacing: 0.03em;
                                   padding: 0.4rem 0.65rem;
                                   border-radius: 999px;
                              }

                              .request-page .badge-status.pending {
                                   background: rgba(255, 193, 7, 0.18);
                                   color: #b8860b;
                                   border: 1px solid rgba(255, 193, 7, 0.45);
                              }

                              .request-page .badge-status.resolved {
                                   background: rgba(40, 167, 69, 0.18);
                                   color: #1b6f37;
                                   border: 1px solid rgba(40, 167, 69, 0.45);
                              }
                         </style>

                         <div class="card page-header-card">
                              <div class="card-header">
                                   <h3>Recent Accomplishment Notifications</h3>
                                   <p class="mb-0 mt-2 text-white-75">Review the latest task completions submitted by your team.</p>
                              </div>
                              <div class="card-body">
                                   <ol class="breadcrumb mb-0">
                                        <li class="breadcrumb-item"><a href="<?= base_url('Page/admin'); ?>">Home</a></li>
                                        <li class="breadcrumb-item active">Notifications</li>
                                   </ol>
                              </div>
                         </div>

                         <div class="card">
                              <div class="card-body">
                                   <?php
                                   $unreadCount = 0;
                                   $readCount = 0;
                                   $resolvedCount = 0;
                                   if (!empty($notifications)) {
                                        foreach ($notifications as $row) {
                                             $statusVal = isset($row->status) ? strtolower($row->status) : 'pending';
                                             $isSeenVal = isset($row->is_seen) ? (int) $row->is_seen : 0;
                                             if ($statusVal === 'pending') {
                                                  if ($isSeenVal === 0) {
                                                       $unreadCount++;
                                                  } else {
                                                       $readCount++;
                                                  }
                                             } else {
                                                  $resolvedCount++;
                                             }
                                        }
                                   }
                                   ?>
                                   <div class="d-flex flex-wrap align-items-center justify-content-between mb-3">
                                        <div class="text-muted">
                                             <span class="badge badge-warning badge-status pending mr-2">Unread: <?= $unreadCount; ?></span>
                                             <span class="badge badge-success badge-status resolved mr-2">Read: <?= $readCount; ?></span>
                                             <span class="badge badge-success badge-status resolved">Resolved: <?= $resolvedCount; ?></span>
                                        </div>
                                        <small class="text-muted">Most recent first</small>
                                   </div>
                                   <div class="table-responsive">
                                        <table class="table table-striped table-bordered mb-0">
                                             <thead class="thead-light">
                                                  <tr>
                                                       <th>When</th>
                                                       <th>Team Member</th>
                                                       <th>Update</th>
                                                       <th>Details</th>
                                                       <th>Status</th>
                                                  </tr>
                                             </thead>
                                             <tbody>
                                                  <?php if (!empty($notifications)): ?>
                                                       <?php foreach ($notifications as $row): ?>
                                                            <?php
                                                            $nameParts = array_filter([
                                                                 isset($row->fName) ? $row->fName : '',
                                                                 isset($row->lName) ? $row->lName : ''
                                                            ]);
                                                            $fullName = trim(implode(' ', $nameParts));
                                                            $title = isset($row->title) ? $row->title : 'Task update';
                                                            $message = isset($row->message) ? $row->message : '';
                                                            $status = isset($row->status) ? strtolower($row->status) : 'pending';
                                                            $isSeen = isset($row->is_seen) ? (int) $row->is_seen : 0;
                                                            $rowClass = 'notification-row';
                                                            $statusLabel = 'Unread';
                                                            $badgeClass = 'badge-status pending';
                                                            if ($status === 'pending') {
                                                                 if ($isSeen === 0) {
                                                                      $rowClass .= ' unread';
                                                                      $statusLabel = 'Unread';
                                                                      $badgeClass = 'badge-status pending';
                                                                 } else {
                                                                      $statusLabel = 'Read';
                                                                      $badgeClass = 'badge-status resolved';
                                                                 }
                                                            } else {
                                                                 $statusLabel = 'Resolved';
                                                                 $badgeClass = 'badge-status resolved';
                                                            }
                                                            ?>
                                                            <tr class="<?= $rowClass; ?>">
                                                                 <td><?= $row->created_at ? date('M d, Y h:i A', strtotime($row->created_at)) : '-'; ?></td>
                                                                 <td><?= $fullName !== '' ? htmlspecialchars($fullName, ENT_QUOTES, 'UTF-8') : 'Unknown'; ?></td>
                                                                 <td><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?></td>
                                                                 <td><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></td>
                                                                 <td><span class="badge <?= $badgeClass; ?>"><?= $statusLabel; ?></span></td>
                                                            </tr>
                                                       <?php endforeach; ?>
                                                  <?php else: ?>
                                                       <tr>
                                                            <td colspan="5" class="text-center text-muted py-4">No notifications recorded yet.</td>
                                                       </tr>
                                                  <?php endif; ?>
                                             </tbody>
                                        </table>
                                   </div>
                              </div>
                         </div>
                    </div>

                    <?php include('includes/footer.php'); ?>
               </div>
          </div>
     </div>

     <?php include('includes/themecustomizer.php'); ?>

     <script src="<?= base_url(); ?>assets/js/vendor.min.js"></script>
     <script src="<?= base_url(); ?>assets/js/app.min.js"></script>

</body>

</html>
