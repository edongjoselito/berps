<!DOCTYPE html>
<html lang="en">

<?php include('includes/head.php'); ?>

<body>

     <div id="wrapper">

          <?php include('includes/top-nav-bar.php'); ?>
          <?php include('includes/sidebar.php'); ?>

          <div class="content-page">
               <div class="content">
                    <div class="container-fluid employee-profile-page">
                         <style>
                              .employee-profile-page .breadcrumb {
                                   background: transparent;
                                   padding: 0;
                                   margin-bottom: 1.5rem;
                              }

                              .employee-profile-page .profile-cover {
                                   position: relative;
                                   border-radius: 20px;
                                   overflow: hidden;
                                   background: linear-gradient(135deg, #6366f1, #8b5cf6);
                                   color: #fff;
                                   margin-bottom: 2.5rem;
                                   box-shadow: 0 18px 40px rgba(79, 70, 229, 0.16);
                              }

                              .employee-profile-page .cover-overlay {
                                   position: absolute;
                                   inset: 0;
                                   background: rgba(15, 23, 42, 0.25);
                              }

                              .employee-profile-page .profile-header {
                                   position: relative;
                                   z-index: 1;
                                   padding: 38px 42px;
                                   display: flex;
                                   flex-wrap: wrap;
                                   align-items: center;
                                   gap: 28px;
                              }

                              .employee-profile-page .avatar-wrapper {
                                   position: relative;
                              }

                              .employee-profile-page .avatar-wrapper img {
                                   width: 128px;
                                   height: 128px;
                                   border-radius: 50%;
                                   border: 6px solid rgba(255, 255, 255, 0.85);
                                   box-shadow: 0 12px 28px rgba(15, 23, 42, 0.25);
                                   object-fit: cover;
                              }

                              .employee-profile-page .profile-meta h2 {
                                   font-size: 2rem;
                                   font-weight: 600;
                                   margin-bottom: 6px;
                              }

                              .employee-profile-page .profile-meta span {
                                   display: inline-flex;
                                   align-items: center;
                                   gap: 10px;
                                   font-size: 1rem;
                                   background: rgba(15, 23, 42, 0.18);
                                   padding: 6px 14px;
                                   border-radius: 999px;
                              }

                              .employee-profile-page .profile-actions {
                                   margin-left: auto;
                                   display: inline-flex;
                                   flex-wrap: wrap;
                                   gap: 12px;
                              }

                              .employee-profile-page .profile-actions .btn {
                                   display: inline-flex;
                                   align-items: center;
                                   gap: 8px;
                                   border-radius: 999px;
                                   padding: 10px 20px;
                              }

                              .employee-profile-page .nav-tabs {
                                   border-bottom: 1px solid rgba(255, 255, 255, 0.2);
                                   margin: 0;
                                   padding: 0 42px 18px;
                                   position: relative;
                                   z-index: 1;
                              }

                              .employee-profile-page .nav-tabs .nav-link {
                                   color: rgba(255, 255, 255, 0.7);
                                   border: none;
                                   font-weight: 600;
                                   padding: 12px 18px;
                              }

                              .employee-profile-page .nav-tabs .nav-link:hover,
                              .employee-profile-page .nav-tabs .nav-link:focus,
                              .employee-profile-page .nav-tabs .nav-link.active {
                                   color: #fff;
                                   background: rgba(15, 23, 42, 0.18);
                                   border-radius: 999px;
                              }

                              .employee-profile-page .info-card {
                                   border: none;
                                   border-radius: 16px;
                                   box-shadow: 0 16px 32px rgba(15, 23, 42, 0.08);
                              }

                              .employee-profile-page .info-card .card-body {
                                   padding: 28px 32px;
                              }

                              .employee-profile-page .info-card h4 {
                                   font-size: 1.15rem;
                                   font-weight: 600;
                                   margin-bottom: 1.25rem;
                              }

                              .employee-profile-page .stats-list {
                                   display: flex;
                                   gap: 24px;
                                   margin-bottom: 1.5rem;
                              }

                              .employee-profile-page .stats-list li {
                                   list-style: none;
                              }

                              .employee-profile-page .stats-list .stat-value {
                                   font-size: 1.75rem;
                                   font-weight: 600;
                                   margin-bottom: 4px;
                              }

                              .employee-profile-page .profile-information th {
                                   width: 160px;
                                   color: #6c757d;
                                   font-weight: 600;
                                   border-top: none;
                              }

                              .employee-profile-page .profile-information td {
                                   border-top: none;
                                   font-weight: 500;
                              }

                              @media (max-width: 767.98px) {
                                   .employee-profile-page .profile-header {
                                        padding: 28px 24px;
                                        text-align: center;
                                   }

                                   .employee-profile-page .profile-meta {
                                        width: 100%;
                                   }

                                   .employee-profile-page .profile-actions {
                                        margin-left: 0;
                                        justify-content: center;
                                   }

                                   .employee-profile-page .nav-tabs {
                                        padding: 0 24px 18px;
                                   }
                              }
                         </style>

                         <?php
                         $employee = isset($data2[0]) ? $data2[0] : null;
                         $employeeId = isset($_GET['id']) ? htmlspecialchars($_GET['id'], ENT_QUOTES, 'UTF-8') : '';
                         $employeeNameParam = $employee ? urlencode(trim($employee->fName . ' ' . $employee->mName . ' ' . $employee->lName)) : '';

                         $defaultAvatarUrl = base_url('assets/images/users/avatar.png');
                         $avatarUrl = $defaultAvatarUrl;

                         if ($employee) {
                              $avatarFilename = '';

                              if (isset($employee->avatar) && trim((string) $employee->avatar) !== '') {
                                   $avatarFilename = trim((string) $employee->avatar);
                              } else {
                                   $ci = &get_instance();
                                   $ci->load->database();

                                   $employeeKey = isset($employee->empID) ? $employee->empID : null;

                                   if ($employeeKey !== null) {
                                        $avatarRow = $ci->db->select('avatar')
                                             ->from('users')
                                             ->where('user_id', $employeeKey)
                                             ->get()
                                             ->row();

                                        if (!$avatarRow) {
                                             $avatarRow = $ci->db->select('avatar')
                                                  ->from('users')
                                                  ->where('username', $employeeKey)
                                                  ->get()
                                                  ->row();
                                        }

                                        if (!$avatarRow && $ci->db->table_exists('o_users')) {
                                             $avatarRow = $ci->db->select('avatar')
                                                  ->from('o_users')
                                                  ->where('user_id', $employeeKey)
                                                  ->get()
                                                  ->row();

                                             if (!$avatarRow) {
                                                  $avatarRow = $ci->db->select('avatar')
                                                       ->from('o_users')
                                                       ->where('username', $employeeKey)
                                                       ->get()
                                                       ->row();
                                             }
                                        }

                                        if ($avatarRow && !empty($avatarRow->avatar)) {
                                             $avatarFilename = trim((string) $avatarRow->avatar);
                                        }
                                   }
                              }

                              if ($avatarFilename !== '') {
                                   $avatarFilename = basename($avatarFilename);

                                   if (strcasecmp($avatarFilename, 'avatar.png') !== 0) {
                                        $avatarFilePath = FCPATH . 'upload/profile/' . $avatarFilename;

                                        if (is_file($avatarFilePath)) {
                                             $avatarUrl = base_url('upload/profile/' . rawurlencode($avatarFilename));
                                        }
                                   }
                              }
                         }
                         ?>

                         <div class="row">
                              <div class="col-12">
                                   <nav aria-label="breadcrumb">
                                        <ol class="breadcrumb pl-0">
                                             <li class="breadcrumb-item">
                                                  <a href="<?= base_url(); ?>Page/admin">
                                                       <i class="mdi mdi-home-outline"></i> Home
                                                  </a>
                                             </li>
                                             <li class="breadcrumb-item"><a href="<?= base_url(); ?>Page/empList">Employee</a></li>
                                             <li class="breadcrumb-item active" aria-current="page">Profile</li>
                                        </ol>
                                   </nav>
                              </div>
                         </div>

                         <div class="profile-cover">
                              <div class="cover-overlay"></div>
                              <div class="profile-header">
                                   <div class="avatar-wrapper">
                                        <img src="<?= htmlspecialchars($avatarUrl, ENT_QUOTES, 'UTF-8'); ?>" alt="employee avatar">
                                   </div>
                                   <div class="profile-meta">
                                        <h2><?= $employee ? htmlspecialchars(trim($employee->fName . ' ' . $employee->mName . ' ' . $employee->lName), ENT_QUOTES, 'UTF-8') : 'Employee Name'; ?></h2>
                                        <span>
                                             <i class="mdi mdi-briefcase-outline"></i>
                                             <?= $employee ? htmlspecialchars((string) $employee->empPosition, ENT_QUOTES, 'UTF-8') : 'Position'; ?>
                                        </span>
                                   </div>
                                   <div class="profile-actions">
                                        <a href="javascript:void(0);" class="btn btn-light text-primary">
                                             <i class="mdi mdi-account-plus-outline"></i> Follow
                                        </a>
                                        <a href="javascript:void(0);" class="btn btn-outline-light">
                                             <i class="mdi mdi-file-download-outline"></i> Download Resume
                                        </a>
                                   </div>
                              </div>
                              <ul class="nav nav-tabs" role="tablist">
                                   <li class="nav-item">
                                        <a class="nav-link active" href="javascript:void(0);">Overview</a>
                                   </li>
                                   <li class="nav-item">
                                        <a class="nav-link<?= empty($employeeId) ? ' disabled' : ''; ?>" href="<?= base_url(); ?>Page/empDTR?id=<?= $employeeId; ?>&amp;name=<?= $employeeNameParam; ?>">
                                             Daily Time Record
                                        </a>
                                   </li>
                                   <li class="nav-item">
                                        <a class="nav-link" href="javascript:void(0);">Accomplishments</a>
                                   </li>
                              </ul>
                         </div>

                         <div class="row">
                              <div class="col-xl-7 col-lg-7">
                                   <div class="card info-card mb-4">
                                        <div class="card-body">
                                             <h4>Brief Information</h4>
                                             <p class="mb-0 text-muted">
                                                  <?= $employee ? 'Get to know more about ' . htmlspecialchars($employee->fName, ENT_QUOTES, 'UTF-8') . ' and their role at the company.' : 'Employee information is unavailable.'; ?>
                                             </p>
                                        </div>
                                   </div>
                              </div>
                              <div class="col-xl-5 col-lg-5">
                                   <div class="card info-card mb-4">
                                        <div class="card-body">
                                             <ul class="stats-list">
                                                  <li>
                                                       <div class="stat-value">0</div>
                                                       <span class="text-muted">Followers</span>
                                                  </li>
                                                  <li>
                                                       <div class="stat-value">0</div>
                                                       <span class="text-muted">User Rating</span>
                                                  </li>
                                             </ul>
                                             <h4>Basic Information</h4>
                                             <table class="table profile-information mb-0">
                                                  <tbody>
                                                       <tr>
                                                            <th scope="row">Full Name</th>
                                                            <td><?= $employee ? htmlspecialchars(trim($employee->fName . ' ' . $employee->mName . ' ' . $employee->lName), ENT_QUOTES, 'UTF-8') : '-'; ?></td>
                                                       </tr>
                                                       <tr>
                                                            <th scope="row">Birthday</th>
                                                            <td><?= $employee && !empty($employee->bDate) ? htmlspecialchars($employee->bDate, ENT_QUOTES, 'UTF-8') : '-'; ?></td>
                                                       </tr>
                                                       <tr>
                                                            <th scope="row">Website</th>
                                                            <td>-</td>
                                                       </tr>
                                                       <tr>
                                                            <th scope="row">Phone Number</th>
                                                            <td>-</td>
                                                       </tr>
                                                       <tr>
                                                            <th scope="row">Email Address</th>
                                                            <td>-</td>
                                                       </tr>
                                                  </tbody>
                                             </table>
                                        </div>
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

</body>

</html>