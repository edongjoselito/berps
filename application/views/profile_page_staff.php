<!DOCTYPE html>
<html lang="en">
<?php
$staff = (isset($data) && is_object($data)) ? $data : null;

$avatarFile = 'default_avatar.png'; // make sure this exists in upload/profile/
if (isset($data1) && is_array($data1) && !empty($data1) && isset($data1[0]->avatar) && $data1[0]->avatar !== '') {
    $avatarFile = $data1[0]->avatar;
}

function staff_field($obj, $prop, $default = '')
{
    if (is_object($obj) && isset($obj->$prop) && $obj->$prop !== null) {
        return $obj->$prop;
    }

    return $default;
}

function staff_field_html($obj, $prop, $default = '')
{
    return htmlspecialchars((string) staff_field($obj, $prop, $default), ENT_QUOTES, 'UTF-8');
}

$fullNameParts = array_filter(array_map('trim', [
    staff_field($staff, 'fName'),
    staff_field($staff, 'mName'),
    staff_field($staff, 'lName'),
]));
$staffFullName = trim(implode(' ', $fullNameParts));
if ($staffFullName === '') {
    $staffFullName = 'Staff Member';
}
$staffFullNameHtml = htmlspecialchars($staffFullName, ENT_QUOTES, 'UTF-8');
$staffPositionHtml = staff_field_html($staff, 'position', 'Staff');

$empIDRaw = isset($empID) ? (string) $empID : '';
$editProfileUrl = $empIDRaw !== ''
    ? base_url('Page/updateEmployee?id=' . rawurlencode($empIDRaw))
    : base_url('Page/employeeList');
$editProfileUrl = htmlspecialchars($editProfileUrl, ENT_QUOTES, 'UTF-8');

$addressParts = array_filter(array_map('trim', [
    staff_field($staff, 'resStreet'),
    staff_field($staff, 'resVillage'),
    staff_field($staff, 'resBarangay'),
]));
$regionParts = array_filter(array_map('trim', [
    staff_field($staff, 'resCity'),
    staff_field($staff, 'resProvince'),
]));
$formattedAddress = '';
if (!empty($addressParts)) {
    $formattedAddress .= implode(' ', $addressParts);
}
if (!empty($regionParts)) {
    if ($formattedAddress !== '') {
        $formattedAddress .= ', ';
    }
    $formattedAddress .= implode(', ', $regionParts);
}
if ($formattedAddress === '') {
    $formattedAddress = '-';
}
$formattedAddressHtml = htmlspecialchars($formattedAddress, ENT_QUOTES, 'UTF-8');
?>

<?php include('includes/head.php'); ?>

<style>
    .staff-profile-shell {
        padding-bottom: 24px;
    }

    .staff-profile-cover {
        border-radius: 16px;
        overflow: hidden;
        position: relative;
        background: linear-gradient(135deg, rgba(30,60,114,0.95) 0%, rgba(42,82,152,0.92) 100%);
        min-height: 180px;
        box-shadow: 0 14px 32px rgba(15, 23, 42, 0.18);
    }

    .staff-profile-cover .picture-bg-overlay {
        background: radial-gradient(circle at 18% 22%, rgba(255,255,255,0.16), transparent 50%),
            radial-gradient(circle at 86% 34%, rgba(255,255,255,0.12), transparent 55%),
            linear-gradient(180deg, rgba(15,23,42,0.15), rgba(15,23,42,0.55));
    }

    .staff-profile-meta {
        margin-top: -70px;
    }

    .staff-profile-meta .profile-user-box {
        border-radius: 16px;
        box-shadow: 0 12px 28px rgba(15, 23, 42, 0.12);
        border: 1px solid rgba(226, 232, 240, 0.85);
        padding: 18px 18px;
        background: rgba(255, 255, 255, 0.96);
        backdrop-filter: blur(6px);
    }

    .staff-profile-meta .profile-user-img img {
        border: 4px solid rgba(255,255,255,0.95);
        box-shadow: 0 10px 22px rgba(15, 23, 42, 0.18);
        object-fit: cover;
    }

    .staff-profile-title {
        font-weight: 700;
        letter-spacing: -0.02em;
    }

    .staff-profile-subtitle {
        color: #64748b;
        margin-bottom: 0;
    }

    .staff-profile-badges {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 10px;
    }

    .staff-profile-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 600;
        background: #f1f5f9;
        color: #0f172a;
        border: 1px solid rgba(148, 163, 184, 0.35);
    }

    .staff-profile-card {
        border-radius: 16px;
        box-shadow: 0 12px 28px rgba(15, 23, 42, 0.08);
        border: 1px solid rgba(226, 232, 240, 0.85);
        overflow: hidden;
    }

    .staff-profile-card .nav-tabs {
        background: #ffffff;
    }

    .staff-profile-card .nav-tabs .nav-link {
        font-weight: 700;
        color: #475569;
        padding: 14px 12px;
    }

    .staff-profile-card .nav-tabs .nav-link.active {
        color: #1e3c72;
    }

    .staff-profile-section-title {
        margin-top: 22px;
        font-weight: 800;
        letter-spacing: -0.01em;
    }

    .staff-profile-table th {
        width: 42%;
        color: #334155;
        font-weight: 700;
        background: rgba(148, 163, 184, 0.08);
    }

    .staff-profile-table td {
        color: #0f172a;
        font-weight: 600;
    }
</style>


<body>

    <!-- Begin page -->
    <div id="wrapper">

        <!-- Topbar Start -->
        <?php include('includes/top-nav-bar.php'); ?>
        <!-- end Topbar --> <!-- ========== Left Sidebar Start ========== -->

        <!-- Lef Side bar -->
        <?php include('includes/sidebar.php'); ?>
        <!-- Left Sidebar End -->

        <!-- ============================================================== -->
        <!-- Start Page Content here -->
        <!-- ============================================================== -->

        <div class="content-page">
            <div class="content">

                <!-- Start Content-->
                <div class="container-fluid staff-profile-shell">
                    <!-- start page title -->
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="profile-bg-picture staff-profile-cover" style="background-image:url('<?= base_url(); ?>assets/images/bg-profile.jpg')">
                                <span class="picture-bg-overlay"></span>
                            </div>
                            <!-- meta -->
                            <div class="staff-profile-meta">
                                <div class="profile-user-box">
                                    <div class="row align-items-center">
                                    <div class="col-sm-6">
                                        <div class="profile-user-img"><img src="<?= base_url('upload/profile/' . $avatarFile); ?>" alt="" class="avatar-lg rounded-circle">
                                        </div>
                                        <div class="">
                                            <h4 class="mt-5 font-18 ellipsis staff-profile-title"><?= $staffFullNameHtml; ?></h4>
                                            <p class="font-13 staff-profile-subtitle"> <?= $staffPositionHtml; ?></p>

                                            <div class="staff-profile-badges">
                                                <span class="staff-profile-badge"><i class="mdi mdi-badge-account"></i><?= staff_field_html($staff, 'IDNumber', '-'); ?></span>
                                                <span class="staff-profile-badge"><i class="mdi mdi-office-building"></i><?= staff_field_html($staff, 'Department', '-'); ?></span>
                                                <span class="staff-profile-badge"><i class="mdi mdi-calendar"></i><?= staff_field_html($staff, 'dateHired', '-'); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="text-right">
                                            <a href="<?= $editProfileUrl; ?>">
                                                <button type="button" class="btn btn-success waves-effect waves-light"> <i class="far fa-edit mr-1"></i> <span>Edit Profile</span> </button>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                            <!--/ meta -->
                        </div>
                    </div>
                    <!-- end row -->

                    <div class="row mt-4">
                        <div class="col-sm-12">
                            <div class="card p-0 staff-profile-card">
                                <div class="card-body p-0">

                                    <?php if ($this->session->flashdata('success')) : ?>

                                        <?= '<div class="alert alert-success alert-dismissible fade show" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>'
                                            . $this->session->flashdata('success') .
                                            '</div>';
                                        ?>
                                    <?php endif; ?>

                                    <?php if ($this->session->flashdata('danger')) : ?>
                                        <?= '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>'
                                            . $this->session->flashdata('danger') .
                                            '</div>';
                                        ?>
                                    <?php endif;  ?>

                                    <ul class=" nav nav-tabs tabs-bordered nav-justified">
                                        <li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#aboutme">About</a></li>
                                        <!-- <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#family">Family</a></li>
                                            <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#education">Education</a></li>
                                            <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#cs">Civil Service</a></li>
											<li class="nav-item"><a class="nav-link" data-toggle="tab" href="#trainings">Trainings</a></li> -->
                                        <!--<li class="nav-item"><a class="nav-link" data-toggle="tab" href="#work">Work Experience</a></li>-->
                                    </ul>

                                    <div class="tab-content m-0 p-4">

                                        <div id="aboutme" class="tab-pane active">
                                            <div class="profile-desk">
                                                <!--<h5 class="text-uppercase font-weight-bold"><?= $staffFullNameHtml; ?></h5>
                                                    <div class="designation mb-4"><?= $staffPositionHtml; ?></div>
                                                    <p class="text-muted">
                                                        I have 10 years of experience designing for the web, and specialize in the areas of user interface design, interaction design, visual design and prototyping. I’ve worked with notable startups including Pearl Street Software.
                                                    </p> -->

                                                <h5 class="staff-profile-section-title">Official Information</h5>
                                                <div class="row">
                                                    <div class="col-sm-4">
                                                        <!--<h5 class="mt-4">Official Information</h5>-->
                                                        <table class="table table-condensed mb-0 staff-profile-table">

                                                            <tbody>
                                                                <tr>
                                                                    <th scope="row">Employee No.</th>
                                                                    <td>
                                                                        <?= staff_field_html($staff, 'IDNumber'); ?>
                                                                    </td>
                                                                </tr>
                                                                <!-- <tr>
                                                                    <th scope="row">Job Title</th>
                                                                    <td>
                                                                        <?= staff_field_html($staff, 'jobTitle'); ?>
                                                                    </td>
                                                                </tr> -->

                                                                <tr>
                                                                    <th scope="row">Position</th>
                                                                    <td>
                                                                        <?= staff_field_html($staff, 'position'); ?>
                                                                    </td>
                                                                </tr>

                                                                <tr>
                                                                    <th scope="row">Department</th>
                                                                    <td>
                                                                        <?= staff_field_html($staff, 'Department'); ?>
                                                                    </td>
                                                                </tr>

                                                            </tbody>

                                                        </table>
                                                    </div>


                                                    <div class="col-sm-4">
                                                        <!--<h5 class="mt-4">Contact Person</h5>-->
                                                        <table class="table table-condensed mb-0 staff-profile-table">

                                                            <tbody>

                                                                <tr>
                                                                    <th scope="row">Date Hired</th>
                                                                    <td>
                                                                        <?= staff_field_html($staff, 'dateHired'); ?>
                                                                    </td>
                                                                </tr>


                                                                <tr>
                                                                    <th scope="row">TIN</th>
                                                                    <td>
                                                                        <?= staff_field_html($staff, 'tinNo'); ?>
                                                                    </td>
                                                                </tr>

                                                                <tr>
                                                                    <th scope="row">GSIS BP No.</th>
                                                                    <td>
                                                                        <?= staff_field_html($staff, 'gsis'); ?>
                                                                    </td>
                                                                </tr>
                                                            </tbody>

                                                        </table>
                                                    </div>
                                                    <div class="col-sm-4">
                                                        <!--<h5 class="mt-4">Contact Person</h5>-->
                                                        <table class="table table-condensed mb-0">

                                                            <tbody>

                                                                <tr>
                                                                    <th scope="row">PAG-IBIG No.</th>
                                                                    <td>
                                                                        <?= staff_field_html($staff, 'pagibig'); ?>
                                                                    </td>
                                                                </tr>

                                                                <tr>
                                                                    <th scope="row">SSS</th>
                                                                    <td>
                                                                        <?= staff_field_html($staff, 'sssNo'); ?>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <th scope="row">PhilHealth No.</th>
                                                                    <td>
                                                                        <?= staff_field_html($staff, 'philHealth'); ?>
                                                                    </td>
                                                                </tr>
                                                            </tbody>

                                                        </table>
                                                    </div>
                                                </div>

                                                <h5 class="staff-profile-section-title">Personal Information</h5>
                                                <div class="row">
                                                    <div class="col-sm-4">
                                                        <!--<h5 class="mt-4">Official Information</h5>-->
                                                        <table class="table table-condensed mb-0 staff-profile-table">

                                                            <tbody>
                                                                <tr>
                                                                    <th scope="row">Gender</th>
                                                                    <td>
                                                                        <?= staff_field_html($staff, 'Sex'); ?>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <th scope="row">Birth Date</th>
                                                                    <td>
                                                                        <?= staff_field_html($staff, 'BirthDate'); ?>
                                                                    </td>
                                                                </tr>

                                                                <tr>
                                                                    <th scope="row">Birth Place</th>
                                                                    <td>
                                                                        <?= staff_field_html($staff, 'BirthPlace'); ?>
                                                                    </td>
                                                                </tr>

                                                            </tbody>
                                                        </table>
                                                    </div>


                                                    <div class="col-sm-4">
                                                        <!--<h5 class="mt-4">Contact Person</h5>-->
                                                        <table class="table table-condensed mb-0">

                                                            <tbody>
                                                                <tr>
                                                                    <th scope="row">Blood Type</th>
                                                                    <td>
                                                                        <?= staff_field_html($staff, 'bloodType'); ?>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <th scope="row">Marital Status</th>
                                                                    <td>
                                                                        <?= staff_field_html($staff, 'MaritalStatus'); ?>
                                                                    </td>
                                                                </tr>


                                                            </tbody>

                                                        </table>
                                                    </div>
                                                    <div class="col-sm-4">
                                                        <!--<h5 class="mt-4">Contact Person</h5>-->
                                                        <table class="table table-condensed mb-0">

                                                            <tbody>


                                                                <tr>
                                                                    <th scope="row">Height</th>
                                                                    <td>
                                                                        <?= staff_field_html($staff, 'height'); ?>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <th scope="row">Weight</th>
                                                                    <td>
                                                                        <?= staff_field_html($staff, 'weight'); ?>
                                                                    </td>
                                                                </tr>
                                                            </tbody>

                                                        </table>
                                                    </div>
                                                </div>

                                                <div class="row">
                                                    <div class="col-sm-6">
                                                        <h5 class="staff-profile-section-title">Contact Information</h5>
                                                        <table class="table table-condensed mb-0 staff-profile-table">

                                                            <tbody>
                                                                <tr>
                                                                    <th scope="row">Contact No.</th>
                                                                    <td>
                                                                        <?= staff_field_html($staff, 'empMobile'); ?>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <th scope="row">Official Email</th>
                                                                    <td>
                                                                        <?= staff_field_html($staff, 'empEmail'); ?>
                                                                    </td>
                                                                </tr>

                                                                <tr>
                                                                    <th scope="row">Address</th>
                                                                    <td class="ng-binding"><?= $formattedAddressHtml; ?></td>
                                                                </tr>



                                                            </tbody>

                                                        </table>
                                                    </div>


                                                    <div class="col-sm-6">
                                                    </div>
                                                </div>

                                            </div> <!-- end profile-desk -->
                                        </div> <!-- about-me -->

                                        <!-- Family -->
                                        <div id="family" class="tab-pane">

                                            <table class="table mb-0">
                                                <thead>
                                                    <tr>
                                                        <th style="text-align: center;">Name</th>
                                                        <th style="text-align: center;">Relationship</th>
                                                        <th style="text-align: center;">Birth Date</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                    $i = 1;
                                                    foreach ($data2 as $row) {
                                                        echo "<tr>";
                                                        echo "<td style='text-align:center'>" . $row->fullName . "</td>";
                                                        echo "<td style='text-align:center'>" . $row->relationship . "</td>";
                                                        echo "<td style='text-align:center'>" . $row->bDate . "</td>";

                                                        echo "</tr>";
                                                    }
                                                    ?>
                                                </tbody>

                                            </table>
                                            <br />
                                            <button type="button" class="btn btn-primary btn-xs">Add</button></a>

                                        </div>

                                        <!-- Education -->
                                        <div id="education" class="tab-pane">

                                            <table class="table mb-0">
                                                <thead>
                                                    <tr>
                                                        <th style="text-align: center;">Level</th>
                                                        <th style="text-align: center;">School Name</th>
                                                        <th style="text-align: center;">Course</th>
                                                        <th style="text-align: center;">Year Started</th>
                                                        <th style="text-align: center;">Year Graduated</th>
                                                        <th style="text-align: center;">Scholarship</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                    $i = 1;
                                                    foreach ($data3 as $row) {
                                                        echo "<tr>";
                                                        echo "<td>" . $row->level . "</td>";
                                                        echo "<td>" . $row->schoolName . "</td>";
                                                        echo "<td>" . $row->course . "</td>";
                                                        echo "<td style='text-align:center'>" . $row->yearStarted . "</td>";
                                                        echo "<td style='text-align:center'>" . $row->yearGraduated . "</td>";
                                                        echo "<td style='text-align:center'>" . $row->scholarship . "</td>";

                                                        echo "</tr>";
                                                    }
                                                    ?>
                                                </tbody>

                                            </table>
                                            <br />
                                            <button type="button" class="btn btn-primary btn-xs">Add</button></a>
                                        </div>

                                        <!-- Civil Service -->
                                        <div id="cs" class="tab-pane">
                                            <table class="table mb-0">
                                                <thead>
                                                    <tr>
                                                        <th style="text-align: center;">Title</th>
                                                        <th style="text-align: center;">Rating</th>
                                                        <th style="text-align: center;">Date of Exam</th>
                                                        <th style="text-align: center;">Place of Exam</th>
                                                        <th style="text-align: center;">License No.</th>
                                                        <th style="text-align: center;">Validity</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                    $i = 1;
                                                    foreach ($data4 as $row) {
                                                        echo "<tr>";
                                                        echo "<td style='text-align:center'>" . $row->licenseTitle . "</td>";
                                                        echo "<td style='text-align:center'>" . $row->rating . "</td>";
                                                        echo "<td style='text-align:center'>" . $row->examDate . "</td>";
                                                        echo "<td style='text-align:center'>" . $row->examPlace . "</td>";
                                                        echo "<td style='text-align:center'>" . $row->licenseNo . "</td>";
                                                        echo "<td style='text-align:center'>" . $row->validity . "</td>";
                                                        echo "</tr>";
                                                    }
                                                    ?>
                                                </tbody>

                                            </table>
                                            <br />
                                            <button type="button" class="btn btn-primary btn-xs">Add</button></a>
                                        </div>
                                        <!-- Trainings -->
                                        <div id="trainings" class="tab-pane">
                                            <table class="table mb-0">
                                                <thead>
                                                    <tr>
                                                        <th style="text-align: center;">Training Title</th>
                                                        <th style="text-align: center;">Date Started</th>
                                                        <th style="text-align: center;">Date Finished</th>
                                                        <th style="text-align: center;">Hours</th>
                                                        <th style="text-align: center;">Type</th>
                                                        <th style="text-align: center;">Conducted By</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                    $i = 1;
                                                    foreach ($data5 as $row) {
                                                        echo "<tr>";
                                                        echo "<td>" . $row->trainingTitle . "</td>";
                                                        echo "<td style='text-align:center'>" . $row->dateStarted . "</td>";
                                                        echo "<td style='text-align:center'>" . $row->dateFinished . "</td>";
                                                        echo "<td style='text-align:center'>" . $row->noHours . "</td>";
                                                        echo "<td style='text-align:center'>" . $row->ldType . "</td>";
                                                        echo "<td style='text-align:center'>" . $row->sponsor . "</td>";

                                                        echo "</tr>";
                                                    }
                                                    ?>
                                                </tbody>

                                            </table>
                                            <br />
                                            <button type="button" class="btn btn-primary btn-xs">Add</button></a>

                                        </div>
                                        <!-- Work Experience -->
                                        <div id="work" class="tab-pane">
                                            <table id="example4" class="table table-striped table-bordered">
                                                <thead>
                                                    <tr>
                                                        <th>Company Name</th>
                                                        <th>Designation</th>
                                                        <th>From</th>
                                                        <th>To</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                </tbody>
                                            </table>

                                            <br />
                                            <button type="button" class="btn btn-primary btn-xs">Add</button></a>

                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                        <!-- end page title -->

                    </div>
                    <!-- end row -->

                </div>
            </div>
        </div>
    </div>
    <!-- end container-fluid -->

    </div>
    <!-- end content -->

    <!-- Footer Start -->
    <?php include('includes/footer.php'); ?>
    <!-- end Footer -->
    </div>

    <!-- ============================================================== -->
    <!-- End Page content -->
    <!-- ============================================================== -->

    </div>
    <!-- END wrapper -->


    <!-- Right Sidebar -->
    <?php include('includes/themecustomizer.php'); ?>
    <!-- /Right-bar -->


    <!-- Vendor js -->
    <script src="<?= base_url(); ?>assets/js/vendor.min.js"></script>

    <script src="<?= base_url(); ?>assets/libs/moment/moment.min.js"></script>
    <script src="<?= base_url(); ?>assets/libs/jquery-scrollto/jquery.scrollTo.min.js"></script>
    <script src="<?= base_url(); ?>assets/libs/sweetalert2/sweetalert2.min.js"></script>

    <!-- Chat app -->
    <script src="<?= base_url(); ?>assets/js/pages/jquery.chat.js"></script>

    <!-- Todo app -->
    <script src="<?= base_url(); ?>assets/js/pages/jquery.todo.js"></script>

    <!--Morris Chart-->
    <script src="<?= base_url(); ?>assets/libs/morris-js/morris.min.js"></script>
    <script src="<?= base_url(); ?>assets/libs/raphael/raphael.min.js"></script>

    <!-- Sparkline charts -->
    <script src="<?= base_url(); ?>assets/libs/jquery-sparkline/jquery.sparkline.min.js"></script>

    <!-- Dashboard init JS -->
    <script src="<?= base_url(); ?>assets/js/pages/dashboard.init.js"></script>

    <!-- App js -->
    <script src="<?= base_url(); ?>assets/js/app.min.js"></script>

    <!-- Required datatable js -->
    <script src="<?= base_url(); ?>assets/libs/datatables/jquery.dataTables.min.js"></script>
    <script src="<?= base_url(); ?>assets/libs/datatables/dataTables.bootstrap4.min.js"></script>
    <!-- Buttons examples -->
    <script src="<?= base_url(); ?>assets/libs/datatables/dataTables.buttons.min.js"></script>
    <script src="<?= base_url(); ?>assets/libs/datatables/buttons.bootstrap4.min.js"></script>
    <script src="<?= base_url(); ?>assets/libs/jszip/jszip.min.js"></script>
    <script src="<?= base_url(); ?>assets/libs/pdfmake/pdfmake.min.js"></script>
    <script src="<?= base_url(); ?>assets/libs/pdfmake/vfs_fonts.js"></script>
    <script src="<?= base_url(); ?>assets/libs/datatables/buttons.html5.min.js"></script>
    <script src="<?= base_url(); ?>assets/libs/datatables/buttons.print.min.js"></script>
    <script src="<?= base_url(); ?>assets/libs/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
    <!-- Responsive examples -->
    <script src="<?= base_url(); ?>assets/libs/datatables/dataTables.responsive.min.js"></script>
    <script src="<?= base_url(); ?>assets/libs/datatables/responsive.bootstrap4.min.js"></script>

    <script src="<?= base_url(); ?>assets/libs/datatables/dataTables.keyTable.min.js"></script>
    <script src="<?= base_url(); ?>assets/libs/datatables/dataTables.select.min.js"></script>

    <!-- Datatables init -->
    <script src="<?= base_url(); ?>assets/js/pages/datatables.init.js"></script>

    <!-- Sparkline charts -->
    <script src="<?= base_url(); ?>assets/libs/jquery-sparkline/jquery.sparkline.min.js"></script>

    <!-- Dashboard init JS -->
    <script src="<?= base_url(); ?>assets/js/pages/dashboard.init.js"></script>

    <!-- App js -->
    <script src="<?= base_url(); ?>assets/js/app.min.js"></script>

    <!-- Required datatable js -->
    <script src="<?= base_url(); ?>assets/libs/datatables/jquery.dataTables.min.js"></script>
    <script src="<?= base_url(); ?>assets/libs/datatables/dataTables.bootstrap4.min.js"></script>
    <!-- Buttons examples -->
    <script src="<?= base_url(); ?>assets/libs/datatables/dataTables.buttons.min.js"></script>
    <script src="<?= base_url(); ?>assets/libs/datatables/buttons.bootstrap4.min.js"></script>
    <script src="<?= base_url(); ?>assets/libs/jszip/jszip.min.js"></script>
    <script src="<?= base_url(); ?>assets/libs/pdfmake/pdfmake.min.js"></script>
    <script src="<?= base_url(); ?>assets/libs/pdfmake/vfs_fonts.js"></script>
    <script src="<?= base_url(); ?>assets/libs/datatables/buttons.html5.min.js"></script>
    <script src="<?= base_url(); ?>assets/libs/datatables/buttons.print.min.js"></script>

    <!-- Responsive examples -->
    <script src="<?= base_url(); ?>assets/libs/datatables/dataTables.responsive.min.js"></script>
    <script src="<?= base_url(); ?>assets/libs/datatables/responsive.bootstrap4.min.js"></script>

    <script src="<?= base_url(); ?>assets/libs/datatables/dataTables.keyTable.min.js"></script>
    <script src="<?= base_url(); ?>assets/libs/datatables/dataTables.select.min.js"></script>

    <!-- Datatables init -->
    <script src="<?= base_url(); ?>assets/js/pages/datatables.init.js"></script>

</body>

</html>