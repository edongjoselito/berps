<nav class="navbar ms-navbar">
  <div class="ms-aside-toggler ms-toggler pl-0" data-target="#ms-side-nav" data-toggle="slideLeft">
    <span class="ms-toggler-bar bg-white"></span>
    <span class="ms-toggler-bar bg-white"></span>
    <span class="ms-toggler-bar bg-white"></span>
  </div>
  <div class="logo-sn logo-sm ms-d-block-sm">
    <a class="pl-0 ml-0 text-center navbar-brand mr-0" href="#"><img src="<?= base_url(); ?>assets/img/logo.png" alt="logo"> </a>
  </div>
  <ul class="ms-nav-list ms-inline mb-0" id="ms-nav-options">
    <?php if ($this->session->userdata('level') === 'Admin'): ?>
      <li class="ms-nav-item  ms-d-none">
        <a href="<?= base_url(); ?>Page/admin" class="text-white"><i class="fa fa-home mr-2"></i> Home</a>
      </li>

      <li class="ms-nav-item ms-d-none">
        <a href="<?= base_url(); ?>Page/businessDetails" class="text-white"><i class="far fa-address-book mr-2"></i> Business Details</a>
      </li>

      <li class="ms-nav-item ms-d-none">
        <a href="<?= base_url(); ?>Page/reports" class="text-white"><i class="far fa-address-book mr-2"></i> Reports</a>
      </li>

      <li class="ms-nav-item ms-nav-user dropdown">
        <a href="#" id="userDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"> <img class="ms-user-img ms-img-round float-right" src="<?= base_url(); ?>profimage/<?php echo $this->session->userdata('avatar'); ?>" alt="people"> </a>
        <ul class="dropdown-menu dropdown-menu-right user-dropdown" aria-labelledby="userDropdown">
          <li class="dropdown-menu-header">
            <h6 class="dropdown-header ms-inline m-0"><span class="text-disabled">Welcome, <?php echo $this->session->userdata('fname') . ' ' . $this->session->userdata('lname'); ?></span></h6>
          </li>
          <li class="dropdown-divider"></li>
          <li class="ms-dropdown-list">
            <a class="media fs-14 p-2" href="#"> <span><i class="flaticon-user mr-2"></i> Profile</span> </a>
            <a class="media fs-14 p-2" href="<?= base_url(); ?>Users/changepassword"> <span><i class="flaticon-gear mr-2"></i> Change Password</span> </a>
          </li>
          <li class="dropdown-divider"></li>
          <li class="dropdown-menu-footer">
            <a class="media fs-14 p-2" href="<?= base_url(); ?>Login/logout"> <span><i class="flaticon-shut-down mr-2"></i> Logout</span> </a>
          </li>
        </ul>
      </li>

    <?php elseif ($this->session->userdata('level') === 'Staff'): ?>

      <li class="ms-nav-item ms-nav-user dropdown">
        <a href="#" id="userDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"> <img class="ms-user-img ms-img-round float-right" src="<?= base_url(); ?>profimage/<?php echo $this->session->userdata('avatar'); ?>" alt="people"> </a>
        <ul class="dropdown-menu dropdown-menu-right user-dropdown" aria-labelledby="userDropdown">
          <li class="dropdown-menu-header">
            <h6 class="dropdown-header ms-inline m-0"><span class="text-disabled">Welcome, <?php echo $this->session->userdata('fname') . ' ' . $this->session->userdata('lname'); ?></span></h6>
          </li>
          <li class="dropdown-divider"></li>
          <li class="ms-dropdown-list">
            <a class="media fs-14 p-2" href="#"> <span><i class="flaticon-user mr-2"></i> Profile</span> </a>
            <a class="media fs-14 p-2" href="<?= base_url(); ?>Users/changepassword"> <span><i class="flaticon-gear mr-2"></i> Change Password</span> </a>
          </li>
          <li class="dropdown-divider"></li>
          <li class="dropdown-menu-footer">
            <a class="media fs-14 p-2" href="<?= base_url(); ?>Login/logout"> <span><i class="flaticon-shut-down mr-2"></i> Logout</span> </a>
          </li>
        </ul>
      </li>

    <?php endif; ?>
  </ul>
  <div class="ms-toggler ms-d-block-sm pr-0 ms-nav-toggler" data-toggle="slideDown" data-target="#ms-nav-options">
    <span class="ms-toggler-bar bg-white"></span>
    <span class="ms-toggler-bar bg-white"></span>
    <span class="ms-toggler-bar bg-white"></span>
  </div>
</nav>