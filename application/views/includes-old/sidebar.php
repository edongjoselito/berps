<aside id="ms-side-nav" class="side-nav fixed ms-aside-scrollable ms-aside-left">

  <!-- Logo -->
  <div class="logo-sn ms-d-block-lg">
    <a class="pl-0 ml-0 text-center" href="#"> <img src="<?= base_url(); ?>assets/img/logo.png"" alt=" logo"> </a>
    <a href="#" class="text-center ms-logo-img-link"> <img src="<?= base_url(); ?>profimage/<?php echo $this->session->userdata('avatar'); ?>" alt="profile image"></a>
    <h5 class="text-center text-white mt-2"><?php echo $this->session->userdata('fname') . ' ' . $this->session->userdata('lname'); ?></h5>
    <!-- <h6 class="text-center text-white mb-3"><?php echo $this->session->userdata('position'); ?></h6> -->
  </div>

  <?php if ($this->session->userdata('level') === 'Admin'): ?>
    <!-- Navigation -->
    <ul class="accordion ms-main-aside fs-14" id="side-nav-accordion">
      <!-- Dashboard -->
      <li class="menu-item">
        <a href="<?= base_url(); ?>Page/admin" class="has-chevron" aria-expanded="false" aria-controls="dashboard">
          <span><i class="material-icons fs-16">dashboard</i>Dashboard </span>
        </a>
      </li>
      <!-- /Dashboard -->

      <li class="menu-item">
        <a href="#" class="has-chevron" data-toggle="collapse" data-target="#jo" aria-expanded="false" aria-controls="payments">
          <span><i class="fas fa-bars"></i>Invoice</span>
        </a>
        <ul id="jo" class="collapse" aria-labelledby="jo" data-parent="#side-nav-accordion">
          <li> <a href="<?= base_url(); ?>Page/invList">Invoice List</a> </li>
          <li> <a href="<?= base_url(); ?>Page/joList">Job Order</a> </li>
        </ul>
      </li>
 

      <!-- Payments -->
      <li class="menu-item">
        <a href="#" class="has-chevron" data-toggle="collapse" data-target="#payments" aria-expanded="false" aria-controls="payments">
          <span><i class="fas fa-credit-card"></i>Collections</span>
        </a>
        <ul id="payments" class="collapse" aria-labelledby="payments" data-parent="#side-nav-accordion">
          <li> <a href="<?= base_url(); ?>Page/paymentList">Payment List [ ALL ]</a> </li>
          <li> <a href="<?= base_url(); ?>Page/paymentRange">Payment List [ DATE ]</a> </li>
          <!-- <li> <a href="<?= base_url(); ?>Page/paymentListYear">Collections By Year</a> </li> -->
        </ul>
      </li>
      <!-- Payments -->


      <!-- Expenses -->
      <li class="menu-item">
        <a href="#" class="has-chevron" data-toggle="collapse" data-target="#expenses" aria-expanded="false" aria-controls="expenses">
          <span><i class="fas fa-minus-square"></i>Expenses</span>
        </a>
        <ul id="expenses" class="collapse" aria-labelledby="expenses" data-parent="#side-nav-accordion">
          <!-- <li> <a href="<?= base_url(); ?>Page/addExpenses">Add Expenses</a> </li> -->
          <li> <a href="<?= base_url(); ?>Page/expensesList">Expenses List [ ALL ]</a> </li>
          <li> <a href="<?= base_url(); ?>Page/expensesRange">Expenses List [ DATE ]</a> </li>
        </ul>
      </li>
      <!-- /Expenses -->

      <!-- Products -->
      <li class="menu-item">
        <a href="#" class="has-chevron" data-toggle="collapse" data-target="#product" aria-expanded="false" aria-controls="product">
          <span><i class="fas fa-list-alt"></i>Products</span>
        </a>
        <ul id="product" class="collapse" aria-labelledby="product" data-parent="#side-nav-accordion">
          <li> <a href="<?= base_url(); ?>Page/productList">Product List</a> </li>
          <!-- <li> <a href="<?= base_url(); ?>Page/projectList">Inventory List</a> </li> -->
        </ul>
      </li>
      <!-- Projects -->


      <!-- Projects -->
      <li class="menu-item">
        <a href="#" class="has-chevron" data-toggle="collapse" data-target="#project" aria-expanded="false" aria-controls="project">
          <span><i class="fas fa-list-alt"></i>Projects</span>
        </a>
        <ul id="project" class="collapse" aria-labelledby="project" data-parent="#side-nav-accordion">
          <!-- <li> <a href="<?= base_url(); ?>Page/addProject">Add Project</a> </li> -->
          <li> <a href="<?= base_url(); ?>Page/projectList">Project List</a> </li>

        </ul>
      </li>
      <!-- Projects -->

      <!-- Task -->
      <li class="menu-item">
        <a href="#" class="has-chevron" data-toggle="collapse" data-target="#task" aria-expanded="false" aria-controls="task">
          <span><i class="fas fa-list"></i>Task</span>
        </a>
        <ul id="task" class="collapse" aria-labelledby="task" data-parent="#side-nav-accordion">
          <li> <a href="<?= base_url(); ?>Page/projectAddTask">Task List </a> </li>
          <li> <a href="<?= base_url(); ?>Page/employeeTask">Task List - Per Employee</a> </li>
          <li> <a href="<?= base_url(); ?>Page/accomplishments">Accomplishments</a> </li>
          <li> <a href="<?= base_url(); ?>Page/employeeAccomplishment">Accomplishments - Per Employee</a> </li>
        </ul>
      </li>
      <!-- Task -->

      <!-- HR -->
      <li class="menu-item">
        <a href="#" class="has-chevron" data-toggle="collapse" data-target="#hr" aria-expanded="false" aria-controls="hr">
          <span><i class="fas fa-th-large"></i>Human Resource</span>
        </a>
        <ul id="hr" class="collapse" aria-labelledby="hr" data-parent="#side-nav-accordion">
          <!-- <li> <a href="<?= base_url(); ?>Page/attendanceList">Attendance List</a> </li> -->
          <li> <a href="<?= base_url(); ?>Page/employeeList">Employee List</a> </li>

        </ul>
      </li>
      <!-- HR -->

      <!-- clients -->
      <li class="menu-item">
        <a href="#" class="has-chevron" data-toggle="collapse" data-target="#clients" aria-expanded="false" aria-controls="clients">
          <span><i class="fas fa-square"></i>Clients</span>
        </a>
        <ul id="clients" class="collapse" aria-labelledby="clients" data-parent="#side-nav-accordion">
          <li> <a href="<?= base_url(); ?>Page/clientList">Client List</a> </li>
          <li> <a href="#">Inactive Clients</a> </li>
        </ul>
      </li>
      <!-- clients -->

      <!-- Notes -->
      <li class="menu-item">
        <a href="#" class="has-chevron" data-toggle="collapse" data-target="#notes" aria-expanded="false" aria-controls="clients">
          <span><i class="fas fa-square"></i>Notes</span>
        </a>
        <ul id="notes" class="collapse" aria-labelledby="notes" data-parent="#side-nav-accordion">
          <li> <a href="<?= base_url(); ?>Page/noteList">View Notes</a> </li>
        </ul>
      </li>
      <!-- Notes -->

      <!-- System Configuration -->
      <li class="menu-item">
        <a href="#" class="has-chevron" data-toggle="collapse" data-target="#config" aria-expanded="false" aria-controls="config">
          <span><i class="fas fa-square"></i>System Configuration</span>
        </a>
        <ul id="config" class="collapse" aria-labelledby="config" data-parent="#side-nav-accordion">
          <li> <a href="<?= base_url(); ?>Page/priceList">Service Price List</a> </li>
        </ul>
      </li>


      <!-- Manage Users -->
      <li class="menu-item">
        <a href="#" class="has-chevron" data-toggle="collapse" data-target="#users" aria-expanded="false" aria-controls="users">
          <span><i class="fas fa-square"></i>Manage Users</span>
        </a>
        <ul id="users" class="collapse" aria-labelledby="config" data-parent="#side-nav-accordion">
          <li> <a href="<?= base_url(); ?>Users/">User's List</a> </li>
        </ul>
      </li>


    </ul>

    <!-- STAFF ACCESS -->
  <?php elseif ($this->session->userdata('level') === 'Staff'): ?>
    <ul class="accordion ms-main-aside fs-14" id="side-nav-accordion">
      <!-- Dashboard -->
      <li class="menu-item">
        <a href="<?= base_url(); ?>Page/staff" class="has-chevron" aria-expanded="false" aria-controls="dashboard">
          <span><i class="material-icons fs-16">dashboard</i>Dashboard </span>
        </a>
      </li>
      <!-- /Dashboard -->


      <!-- <li class="menu-item">
        <a href="<?= base_url(); ?>Page/dtr">
          <span><i class="fas fa-bars"></i>Daily Time Record</span>
        </a>

      </li> -->

      <!-- <li class="menu-item">
        <a href="#" class="has-chevron" data-toggle="collapse" data-target="#jo" aria-expanded="false" aria-controls="payments">
          <span><i class="fas fa-bars"></i>Invoice</span>
        </a>
        <ul id="jo" class="collapse" aria-labelledby="jo" data-parent="#side-nav-accordion">
          <li> <a href="<?= base_url(); ?>Page/invList">Invoice List</a> </li>
          <li> <a href="<?= base_url(); ?>Page/joList">Job Order</a> </li>
        </ul>
      </li> -->


      <!-- <li class="menu-item">
        <a href="#" class="has-chevron" data-toggle="collapse" data-target="#payments" aria-expanded="false" aria-controls="payments">
          <span><i class="fas fa-credit-card"></i>Collections</span>
        </a>
        <ul id="payments" class="collapse" aria-labelledby="payments" data-parent="#side-nav-accordion">
          <li> <a href="<?= base_url(); ?>Page/paymentRangeData">Payment List</a> </li>
        </ul>
      </li> -->


      <!-- <li class="menu-item">
        <a href="#" class="has-chevron" data-toggle="collapse" data-target="#expenses" aria-expanded="false" aria-controls="expenses">
          <span><i class="fas fa-minus-square"></i>Expenses</span>
        </a>
        <ul id="expenses" class="collapse" aria-labelledby="expenses" data-parent="#side-nav-accordion">
          <li> <a href="<?= base_url(); ?>Page/addExpenses">Add Expenses</a> </li>
          <li> <a href="<?= base_url(); ?>Page/expensesList">Expenses List</a> </li>
        </ul>
      </li> -->


      <!-- Task -->
      <li class="menu-item">
        <a href="#" class="has-chevron" data-toggle="collapse" data-target="#task" aria-expanded="false" aria-controls="task">
          <span><i class="fas fa-list"></i>Task</span>
        </a>
        <ul id="task" class="collapse" aria-labelledby="task" data-parent="#side-nav-accordion">
          <li> <a href="<?= base_url(); ?>Page/projectAddTask">Task List</a> </li>
          <li> <a href="<?= base_url(); ?>Page/accomplishments">Accomplishments</a> </li>
        </ul>
      </li>

      <!-- Reminders -->
      <li class="menu-item">
        <a href="#" class="has-chevron" data-toggle="collapse" data-target="#reminders" aria-expanded="false" aria-controls="reminders">
          <span>
            <i class="fas fa-bell"></i>Reminders
            <?php if (!empty($dueToday)): ?>
              <span class="badge badge-danger ml-2"><?= count($dueToday) ?></span>
            <?php endif; ?>
          </span>
        </a>
        <ul id="reminders" class="collapse" aria-labelledby="reminders" data-parent="#side-nav-accordion">
          <li> <a href="<?= base_url(); ?>Reminders/">Reminder List</a> </li>
        </ul>
      </li>



      <!-- <li class="menu-item">
        <a href="#" class="has-chevron" data-toggle="collapse" data-target="#clients" aria-expanded="false" aria-controls="clients">
          <span><i class="fas fa-square"></i>Clients</span>
        </a>
        <ul id="clients" class="collapse" aria-labelledby="clients" data-parent="#side-nav-accordion">
          <li> <a href="<?= base_url(); ?>Page/clientList">Client List</a> </li>
          <li> <a href="#">Inactive Clients</a> </li>
        </ul>
      </li> -->



      <li class="menu-item">
        <a href="#" class="has-chevron" data-toggle="collapse" data-target="#notes" aria-expanded="false" aria-controls="clients">
          <span><i class="fas fa-square"></i>Notes</span>
        </a>
        <ul id="notes" class="collapse" aria-labelledby="notes" data-parent="#side-nav-accordion">
          <li> <a href="<?= base_url(); ?>Page/noteList">View Notes</a> </li>
        </ul>
      </li>
    </ul>

  <?php endif; ?>

</aside>