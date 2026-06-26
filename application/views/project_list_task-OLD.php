<!DOCTYPE html>
<html lang="en">

<head>

  <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>BERPS</title>
  <!-- Iconic Fonts -->
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  <link href="<?= base_url(); ?>vendors/iconic-fonts/font-awesome/css/all.min.css" rel="stylesheet">
  <link rel="stylesheet" href="<?= base_url(); ?>vendors/iconic-fonts/flat-icons/flaticon.css">
  <link rel="stylesheet" href="<?= base_url(); ?>vendors/iconic-fonts/cryptocoins/cryptocoins.css">
  <link rel="stylesheet" href="<?= base_url(); ?>vendors/iconic-fonts/cryptocoins/cryptocoins-colors.css">
  <!-- Bootstrap core CSS -->
  <link href="<?= base_url(); ?>assets/css/bootstrap.min.css" rel="stylesheet">
  <!-- jQuery UI -->
  <link href="<?= base_url(); ?>assets/css/jquery-ui.min.css" rel="stylesheet">
  <!-- Medjestic styles -->
  <link href="<?= base_url(); ?>assets/css/style.css" rel="stylesheet">
  <!-- Page Specific Css (Datatables.css) -->
  <link href="<?= base_url(); ?>assets/css/datatables.min.css" rel="stylesheet">
  <!-- Favicon -->
  <link rel="icon" type="image/png" sizes="32x32" href="<?= base_url(); ?>favicon.ico">

    <link rel="stylesheet" href="<?= base_url('assets/css/fonts.css'); ?>">
</head>

<body class="ms-body ms-aside-left-open ms-primary-theme ms-has-quickbar">

  <!-- Setting Panel -->

  <!-- Sidebar Navigation Left -->
  <?php include('includes/sidebar.php'); ?>

  <!-- Sidebar Right -->

  <!-- Main Content -->
  <main class="body-content">

    <!-- Navigation Bar -->
    <?php include('includes/nav.php'); ?>


    <!-- Body Content Wrapper -->
    <div class="ms-content-wrapper">

      <?php if ($this->session->flashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
          <?= $this->session->flashdata('success'); ?>
          <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
      <?php endif; ?>

      <?php $currentUser = $this->session->userdata('username'); ?>


      <div class="row">
        <div class="col-lg-6 col-md-6">
          <a href="<?= base_url(); ?>Page/projectAddTask">
            <div class="ms-panel ms-widget ms-panel-hoverable has-border ms-has-new-msg ms-notification-widget">
              <!-- <span class="msg-count"><?php echo $data3[0]->pCounts; ?></span> -->
              <div class="ms-panel-body media">
                <i class="material-icons">show_chart</i>
                <div class="media-body">
                  <h6>Pending Task</h6>
                  <span>
                    <p><?= $openTaskCount ?></p>
                  </span>

                </div>
              </div>
            </div>
          </a>
        </div>
        <div class="col-lg-6 col-md-6">
          <a href="<?= base_url(); ?>Page/accomplishments">
            <div class="ms-panel ms-widget ms-panel-hoverable has-border ms-has-new-msg ms-notification-widget">
              <!-- <span class="msg-count"><?php echo $data4[0]->pCounts; ?></span> -->
              <div class="ms-panel-body media">
                <i class="material-icons">graphic_eq</i>
                <div class="media-body">
                  <h6>Accomplished Task</h6>
                  <p><?= $closedTaskCount ?></p>
                </div>
              </div>
            </div>
          </a>
        </div>
      </div>


      <div class="ms-panel">
        <div class="ms-panel-header ms-panel-custome">
          <h6>Task List</h6>
          <a href="#mymodal" class="text-blue" data-toggle="modal"><i class="flaticon-list mr-2"></i> Add New Task</a>
        </div>

        <div class="ms-panel-body">
          <div class="table-responsive">
            <table class="table table-striped table-bordered">
              <thead class="bg-primary text-white text-center">
                <tr>
                  <th class="text-left">Task</th>
                  <th>Reported Date</th>
                  <th>Assigned Person</th>
                  <th>Project</th>
                  <th>Status / Priority</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($data as $row): ?>
                  <tr>
                    <td class="text-left">
                      <a href="<?= base_url('Page/taskStat?id=') . $row->taskID; ?>">
                        <?= $row->task ?>
                      </a>
                    </td>
                    <td class="text-center"><?= $row->reportedDate ?></td>
                    <td class="text-center">
                      <a href="<?= base_url('Page/employeeTaskData2?name=') . $row->assignedPerson; ?>">
                        <?= $row->assignedPersonName ?>
                      </a>
                    </td>
                    <td class="text-center">
                      <a href="<?= base_url('Page/taskPerProject?projectID=') . $row->projectID; ?>">
                        <?= $row->projectDescription ?>
                      </a>
                    </td>
                    <td class="text-center">
                      <span class="badge badge-pill <?= ($row->taskStat == 1 ? 'badge-success' : 'badge-secondary') ?>">
                        <?= ($row->taskStat == 1 ? 'Open' : 'Closed') ?>
                      </span>
                      <?php
                      $priorityLabel = '';
                      $priorityClass = '';
                      if ($row->priority == 1) {
                        $priorityLabel = 'High';
                        $priorityClass = 'badge-danger';
                      } elseif ($row->priority == 2) {
                        $priorityLabel = 'Medium';
                        $priorityClass = 'badge-warning';
                      } else {
                        $priorityLabel = 'Low';
                        $priorityClass = 'badge-info';
                      }
                      ?>
                      <span class="badge badge-pill <?= $priorityClass ?>">
                        <?= $priorityLabel ?>
                      </span>
                    </td>
                    <td class="text-center">
                      <a href="<?= base_url('Page/taskStat?id=') . $row->taskID; ?>" class="badge badge-secondary">View Updates</a>
                      <a href="#addstatus" class="badge badge-primary"
                        data-id="<?= $row->taskID ?>"
                        onclick="$('#dataid').val($(this).data('id')); $('#addstatus').modal('show');"
                        data-toggle="modal">
                        Add Status
                      </a>

                      <a href="#" class="badge badge-info"
                        onclick="populateUpdateModal(this)"
                        data-id="<?= $row->taskID ?>"
                        data-task="<?= htmlspecialchars($row->task, ENT_QUOTES) ?>"
                        data-priority="<?= $row->priority ?>"
                        data-project="<?= $row->projectID ?>">
                        Update
                      </a>

                      <?php if ($row->added_by == $currentUser): ?>
                        <a href="<?= base_url('Page/deleteTask/') . $row->taskID; ?>"
                          class="badge badge-danger"
                          onclick="return confirm('Are you sure you want to delete this task?');">
                          Delete
                        </a>
                      <?php endif; ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>


          </div>
        </div>

      </div>
    </div>



    </div>

    </div>

  </main>

  <!-- SCRIPTS -->
  <!-- Global Required Scripts Start -->
  <script src="<?= base_url(); ?>assets/js/jquery-3.3.1.min.js"></script>
  <script src="<?= base_url(); ?>assets/js/popper.min.js"></script>
  <script src="<?= base_url(); ?>assets/js/bootstrap.min.js"></script>
  <script src="<?= base_url(); ?>assets/js/perfect-scrollbar.js"> </script>
  <script src="<?= base_url(); ?>assets/js/jquery-ui.min.js"> </script>
  <!-- Global Required Scripts End -->

  <!-- Page Specific Scripts Start -->
  <script src="<?= base_url(); ?>assets/js/Chart.bundle.min.js"> </script>
  <script src="<?= base_url(); ?>assets/js/client-managemenet.js"> </script>
  <!-- Page Specific Scripts Finish -->

  <!-- Medjestic core JavaScript -->
  <script src="<?= base_url(); ?>assets/js/framework.js"></script>

  <!-- Settings -->
  <script src="<?= base_url(); ?>assets/js/settings.js"></script>

  <!-- Page Specific Scripts Start -->
  <script src="<?= base_url(); ?>assets/js/datatables.min.js"> </script>
  <script src="<?= base_url(); ?>assets/js/data-tables.js"> </script>
  <!-- Page Specific Scripts End -->
  <script>
    $(document).ready(function() {
      $('#table').DataTable();
    });
  </script>

  <!-- Edit Task Modal -->
  <div class="modal fade" id="edit" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog ms-modal-dialog-width">
      <div class="modal-content ms-modal-content-width">
        <div class="modal-header  ms-modal-header-radius-0">
          <h4 class="modal-title text-white">Edit Task</h4>
          <button type="button" class="close text-white" data-dismiss="modal" aria-hidden="true">x</button>

        </div>
        <div class="modal-body p-0 text-left">
          <div class="col-xl-12 col-md-12">
            <div class="ms-panel ms-panel-bshadow-none">
              <div class="ms-panel-header">
                <h6>Task Details</h6>
              </div>
              <div class="ms-panel-body">
                <form class="needs-validation" method="post" novalidate>
                  <div class="form-row">
                    <div class="col-md-12 mb-3">
                      <label for="validationCustom02">Project</label>
                      <select class="form-control" name="project" required>
                        <option value="">-- Select Project --</option>
                        <?php foreach ($data1 as $row): ?>
                          <option value="<?= $row->projectID ?>">
                            <?= $row->Customer . ' - ' . $row->projectDescription ?>
                          </option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                  </div>

                  <div class="form-row">
                    <div class="col-md-12 mb-3">
                      <label for="validationCustom01">Task</label>
                      <input type="text" class="form-control" name="task" id="validationCustom01" required>
                    </div>
                  </div>

                  <div class="form-row">
                    <div class="col-md-4 mb-3">
                      <label for="validationCustom02">Reported Date</label>
                      <input type="date" name="reportedDate" class="form-control" id="validationCustom02" required>
                    </div>
                  </div>

                  <input type="submit" name="add_task" class="btn btn-primary mt-4 d-inline w-20" value="Add Task">
                  <button class="btn btn-warning mt-4 d-inline w-20" type="submit" name="resettask">Reset</button>
                </form>

              </div>

            </div>
          </div>
        </div>

      </div>
    </div>
  </div>

  <!-- Add Status Modal -->
  <div class="modal fade" id="addstatus" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog ms-modal-dialog-width">
      <div class="modal-content ms-modal-content-width">
        <div class="modal-header  ms-modal-header-radius-0">
          <h4 class="modal-title text-white">Add Task Status</h4>
          <button type="button" class="close text-white" data-dismiss="modal" aria-hidden="true">x</button>

        </div>
        <div class="modal-body p-0 text-left">
          <div class="col-xl-12 col-md-12">
            <div class="ms-panel ms-panel-bshadow-none">
              <div class="ms-panel-header">
                <h6>Status Details</h6>
              </div>
              <div class="ms-panel-body">
                <form class="needs-validation" method="post" action="<?= base_url(); ?>Page/addTaskNote" novalidate>
                  <input type="hidden" name="dataid" id="dataid" value="" />
                  <div class="form-row">
                    <div class="col-md-12 mb-3">
                      <label for="validationCustom02">Notes</label>
                      <textarea class="form-control" name="note" rows="3"></textarea>
                    </div>

                    <div class="col-md-6 mb-3">
                      <label for="validationCustom02">Current Status</label>
                      <select class="form-control" name="taskStat">
                        <option value="1">Open</option>
                        <option value="0">Closed</option>
                        <!-- <option value="2">Cancelled</option> -->
                      </select>
                    </div>

                  </div>

                  <input type="submit" name="add_task_stat" class="btn btn-primary mt-4 d-inline w-20" value="Submit">
                  <button class="btn btn-warning mt-4 d-inline w-20" type="submit" name="resettask">Reset</button>
                  <!-- <button class="btn btn-primary mt-4 d-inline w-20" type="submit" name="add_task">Add Task</button> -->
                </form>
              </div>

            </div>
          </div>
        </div>

      </div>
    </div>
  </div>


  <!-- Task Status Modal -->
  <div class="modal fade" id="taskstat" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog ms-modal-dialog-width">
      <div class="modal-content ms-modal-content-width">
        <div class="modal-header  ms-modal-header-radius-0">
          <h4 class="modal-title text-white">Task Status</h4>
          <button type="button" class="close text-white" data-dismiss="modal" aria-hidden="true">x</button>

        </div>
        <div class="modal-body p-0 text-left">
          <div class="col-xl-12 col-md-12">
            <div class="ms-panel ms-panel-bshadow-none">
              <div class="ms-panel-header">
                <h6>Task Status Details</h6>
              </div>
              <div class="ms-panel-body">


              </div>

            </div>
          </div>
        </div>

      </div>
    </div>
  </div>

  <!-- Add Task Modal -->
  <div class="modal fade" id="mymodal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog ms-modal-dialog-width">
      <div class="modal-content ms-modal-content-width">
        <div class="modal-header ms-modal-header-radius-0">
          <h4 class="modal-title text-white">Add New Task</h4>
          <button type="button" class="close text-white" data-dismiss="modal" aria-hidden="true">x</button>
        </div>

        <div class="modal-body p-0 text-left">
          <div class="col-xl-12 col-md-12">
            <div class="ms-panel ms-panel-bshadow-none">
              <div class="ms-panel-header">
                <h6>New Task Details</h6>
              </div>
              <div class="ms-panel-body">
                <form class="needs-validation" method="post" novalidate>
                  <!-- Project -->
                  <div class="form-row">
                    <div class="col-md-12 mb-3">
                      <label for="project">Project</label>
                      <select class="form-control" name="project" required>
                        <option value="">-- Select Project --</option>
                        <?php foreach ($data1 as $row): ?>
                          <option value="<?= $row->projectID ?>">
                            <?= $row->projectDescription ?>
                          </option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                  </div>

                  <!-- Task -->
                  <div class="form-row">
                    <div class="col-md-12 mb-3">
                      <label for="task">Task</label>
                      <input type="text" class="form-control" name="task" id="task" required>
                    </div>
                  </div>

                  <?php if ($this->session->userdata('level') === 'Admin'): ?>
                    <div class="form-row">
                      <!-- Reported Date -->
                      <div class="col-md-3 mb-3">
                        <label for="reportedDate">Reported Date</label>
                        <input type="date" name="reportedDate" class="form-control" id="reportedDate" value="<?= date('Y-m-d'); ?>" required>

                      </div>

                      <!-- Assigned Staff (use username) -->
                      <div class="col-md-6 mb-3">
                        <label for="assignedPerson">Assigned Staff</label>
                        <select class="form-control" name="assignedPerson" required>
                          <option value="">-- Select User --</option>
                          <?php foreach ($data2 as $row): ?>
                            <option value="<?= $row->user_id ?>"><?= $row->lName . ', ' . $row->fName ?></option>
                          <?php endforeach; ?>
                        </select>
                      </div>

                      <!-- Priority -->
                      <div class="col-md-3 mb-3">
                        <label for="priority">Priority</label>
                        <select class="form-control" name="priority" id="priority" required>
                          <option value="1">High</option>
                          <option value="2">Medium</option>
                          <option value="3">Low</option>
                        </select>

                      </div>
                    </div>

                  <?php elseif ($this->session->userdata('level') === 'Staff'): ?>
                    <div class="form-row">
                      <div class="col-md-6 mb-3">
                        <label for="reportedDate">Reported Date</label>
                        <input type="date" name="reportedDate" class="form-control" id="reportedDate" value="<?= date('Y-m-d'); ?>" required>

                      </div>
                      <div class="col-md-6 mb-3">
                        <label for="priority">Priority</label>
                        <select class="form-control" name="priority" id="priority" required>
                          <option value="1">High</option>
                          <option value="2">Medium</option>
                          <option value="3">Low</option>
                        </select>

                      </div>
                    </div>
                  <?php endif; ?>

                  <!-- Buttons -->
                  <input type="submit" name="add_task" class="btn btn-primary mt-4 d-inline w-20" value="Add Task">
                  <button class="btn btn-warning mt-4 d-inline w-20" type="submit" name="resettask">Reset</button>
                </form>
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>

  <!-- Update Modal -->
  <div class="modal fade" id="updateTaskModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog ms-modal-dialog-width">
      <div class="modal-content ms-modal-content-width">
        <div class="modal-header ms-modal-header-radius-0">
          <h4 class="modal-title text-white">Update Task</h4>
          <button type="button" class="close text-white" data-dismiss="modal" aria-hidden="true">x</button>
        </div>

        <div class="modal-body p-0 text-left">
          <div class="col-xl-12 col-md-12">
            <div class="ms-panel ms-panel-bshadow-none">
              <div class="ms-panel-header">
                <h6>Edit Task Details</h6>
              </div>
              <div class="ms-panel-body">
                <form class="needs-validation" method="POST" action="<?= base_url('Page/updateTask') ?>" novalidate>
                  <input type="hidden" name="taskID" id="update_taskID">

                  <!-- Project -->
                  <div class="form-row">
                    <div class="col-md-12 mb-3">
                      <label for="update_project">Project</label>
                      <select class="form-control" name="project" id="update_project" required>
                        <option value="">-- Select Project --</option>
                        <?php foreach ($data1 as $proj): ?>
                          <option value="<?= $proj->projectID ?>"><?= $proj->projectDescription ?></option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                  </div>

                  <!-- Task -->
                  <div class="form-row">
                    <div class="col-md-12 mb-3">
                      <label for="update_task">Task</label>
                      <input type="text" class="form-control" name="task" id="update_task" required>
                    </div>
                  </div>

                  <!-- Assigned Person (if Admin) -->
                  <?php if ($this->session->userdata('level') === 'Admin'): ?>
                    <div class="form-row">
                      <div class="col-md-12 mb-3">
                        <label for="update_assignedPerson">Assigned Person</label>
                        <select class="form-control" name="assignedPerson" id="update_assignedPerson" required>
                          <option value="">-- Select User --</option>
                          <?php foreach ($data2 as $row): ?>
                            <option value="<?= $row->user_id ?>">
                              <?= $row->lName . ', ' . $row->fName ?>
                            </option>
                          <?php endforeach; ?>
                        </select>
                      </div>
                    </div>
                  <?php endif; ?>

                  <!-- Priority -->
                  <div class="form-row">
                    <div class="col-md-6 mb-3">
                      <label for="update_priority">Priority</label>
                      <select class="form-control" name="priority" id="update_priority" required>
                        <option value="1">High</option>
                        <option value="2">Medium</option>
                        <option value="3">Low</option>
                      </select>
                    </div>
                  </div>

                  <!-- Buttons -->
                  <input type="submit" name="update_task" class="btn btn-primary mt-4 d-inline w-20" value="Update Task">
                  <button class="btn btn-secondary mt-4 d-inline w-20" type="button" data-dismiss="modal">Cancel</button>
                </form>
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>


  <script>
    function populateUpdateModal(el) {
      const id = el.getAttribute('data-id');
      const task = el.getAttribute('data-task');
      const priority = el.getAttribute('data-priority');
      const project = el.getAttribute('data-project');

      document.getElementById('update_taskID').value = id;
      document.getElementById('update_task').value = task;
      document.getElementById('update_priority').value = priority;
      document.getElementById('update_project').value = project;

      $('#updateTaskModal').modal('show');
    }
  </script>

</body>

</html>