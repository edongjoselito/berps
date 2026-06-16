<?php
$__sidebarCi = &get_instance();
$__sidebarLevel = strtolower(trim((string) $__sidebarCi->session->userdata('level')));
$__sidebarSettingsId = (int) $__sidebarCi->session->userdata('settingsID');
$__enabledCompanyFeatures = array();
$__hasCompanyFeatureRestrictions = false;
$__isPackage2 = false;

if ($__sidebarSettingsId > 0 && in_array($__sidebarLevel, array('admin', 'staff', 'account', 'manager', 'cashier', 'encoder'), true) && $__sidebarCi->db->table_exists('company_features')) {
    $__featureRows = $__sidebarCi->db
        ->select('feature_key')
        ->from('company_features')
        ->where('settingsID', $__sidebarSettingsId)
        ->where('is_enabled', 1)
        ->get()
        ->result();

    foreach ($__featureRows as $__featureRow) {
        $__featureKey = trim((string) ($__featureRow->feature_key ?? ''));
        if ($__featureKey !== '') {
            $__enabledCompanyFeatures[] = $__featureKey;
        }
    }

    $__enabledCompanyFeatures = array_values(array_unique($__enabledCompanyFeatures));
    $__hasCompanyFeatureRestrictions = !empty($__enabledCompanyFeatures);
    
    // Check if company is on Package 2 (Task Management Suite)
    // Package 2 features: tasks, notes, calendar
    $__package2Features = array('tasks', 'notes', 'calendar');
    $__isPackage2 = count($__enabledCompanyFeatures) === count($__package2Features) && 
                   count(array_intersect($__enabledCompanyFeatures, $__package2Features)) === count($__package2Features);
}

$__sidebarHasFeature = function ($featureKeys) use ($__hasCompanyFeatureRestrictions, $__enabledCompanyFeatures) {
    if (!$__hasCompanyFeatureRestrictions) {
        return true;
    }

    foreach ((array) $featureKeys as $__featureKey) {
        $__featureKey = trim((string) $__featureKey);
        if ($__featureKey !== '' && in_array($__featureKey, $__enabledCompanyFeatures, true)) {
            return true;
        }
    }

    return false;
};

$__showInvoiceMenu = $__sidebarHasFeature(array('invoice', 'job_order'));
$__showInvoiceFeature = $__sidebarHasFeature(array('invoice'));
$__showJobOrderFeature = $__sidebarHasFeature(array('job_order'));
$__showDeliveriesMenu = $__sidebarHasFeature(array('deliveries'));
$__showProjectsMenu = $__sidebarHasFeature(array('projects'));
$__showTasksMenu = $__sidebarHasFeature(array('tasks'));
$__showSupportMenu = $__sidebarHasFeature(array('support'));
$__showCalendarFeature = $__sidebarHasFeature(array('calendar'));
$__showPayrollMenu = $__sidebarHasFeature(array('payroll', 'employee_payroll', 'salary_computation', 'payroll_reports'));
$__showClientsMenu = !$__hasCompanyFeatureRestrictions || $__sidebarHasFeature(array('invoice', 'deliveries', 'projects', 'support'));
$__showNotesMenu = $__sidebarHasFeature(array('notes'));
$__showExpensesMenu = $__sidebarHasFeature(array('expenses'));
$__showPosMenu = $__sidebarHasFeature(array('pos'));
$__showAttendanceMenu = !$__hasCompanyFeatureRestrictions || $__showPayrollMenu;
?>
<style>
    .left-side-menu .metismenu li>ul {
        display: none !important;
    }

    .left-side-menu .metismenu li.mm-open>ul,
    .left-side-menu .metismenu li>ul.mm-show {
        display: block !important;
    }

    .left-side-menu .metismenu a {
        transition: background-color 0.18s ease, color 0.18s ease;
    }

    .left-side-menu .metismenu a:active {
        transform: translateY(0);
    }

    .left-side-menu .metismenu .waves-ripple {
        display: none !important;
    }
</style>

<div class="left-side-menu">
    <div class="slimscroll-menu">
        <!-- Super Administrator -->
        <?php if ($this->session->userdata('level') === 'Super Admin' || $this->session->userdata('level') === 'System Administrator'): ?>
            <div id="sidebar-menu">
                <ul class="metismenu" id="side-menu">

                    <li class="menu-title">SYSTEM ADMINISTRATOR</li>

                    <li>
                        <a href="<?= base_url(); ?>Page/superAdmin" class="waves-effect">
                            <i class="ph ph-chart-line"></i>
                            <span> Dashboard </span>
                        </a>
                    </li>

                    <li>
                        <a href="<?= base_url(); ?>Page/superAdminCompanies" class="waves-effect">
                            <i class="ph ph-domain"></i>
                            <span> Manage Companies </span>
                        </a>
                    </li>

                    <li>
                        <a href="<?= base_url(); ?>Page/superAdminBilling" class="waves-effect">
                            <i class="ph ph-credit-card"></i>
                            <span> Billing & Payments </span>
                        </a>
                    </li>

                    <li>
                        <a href="<?= base_url(); ?>Page/superAdminAdmins" class="waves-effect">
                            <i class="ph ph-account-key"></i>
                            <span> Company Admins </span>
                        </a>
                    </li>

                    <li>
                        <a href="<?= base_url(); ?>login/logout" class="waves-effect">
                            <i class="ph ph-sign-out"></i>
                            <span> Logout </span>
                        </a>
                    </li>

                </ul>

            </div>
            <!-- End Sidebar -->

        <!-- System Administrator -->
        <?php elseif ($this->session->userdata('level') === 'Admin'): ?>
            <div id="sidebar-menu">
                <ul class="metismenu" id="side-menu">

                    <li class="menu-title">ADMINISTRATION</li>

                    <li>
                        <a href="<?= base_url(); ?>Page/admin" class="waves-effect">
                            <i class="ph ph-chart-line"></i>
                            <span> Dashboard </span>
                        </a>
                    </li>

                    <?php if ($__showInvoiceMenu): ?>
                    <li>
                        <a href="javascript: void(0);" class="waves-effect">
                            <i class="ph ph-file-text"></i>
                            <span> Invoice </span>
                            <span class="menu-arrow"></span>
                        </a>
                        <ul class="nav-second-level" aria-expanded="false">
                            <?php if ($__showInvoiceFeature): ?>
                            <li> <a href="<?= base_url(); ?>Page/invList">Invoice List</a> </li>
                            <li> <a href="<?= base_url(); ?>Page/invoiceStatusReport">Invoice Status Report</a> </li>
                            <li> <a href="<?= base_url(); ?>Page/recurringInvoices">Recurring Invoices</a> </li>
                            <?php endif; ?>
                            <?php if ($__showJobOrderFeature): ?>
                            <li> <a href="<?= base_url(); ?>Page/joList">Job Order</a> </li>
                            <?php endif; ?>
                        </ul>
                    </li>
                    <?php endif; ?>

                    <?php if ($__showDeliveriesMenu): ?>
                    <li>
                        <a href="javascript: void(0);" class="waves-effect">
                            <i class="ph ph-cube"></i>
                            <span> Deliveries </span>
                            <span class="menu-arrow"></span>
                        </a>
                        <ul class="nav-second-level" aria-expanded="false">
                            <li> <a href="<?= base_url(); ?>Page/customerDeliveryList">Delivery List</a> </li>
                            <li> <a href="<?= base_url(); ?>Page/newCustomerDelivery">New Delivery</a> </li>
                        </ul>
                    </li>
                    <?php endif; ?>

                    <?php if ($__showInvoiceFeature): ?>
                    <li>
                        <a href="javascript: void(0);" class="waves-effect">
                            <i class="ph ph-archive"></i>
                            <span> Collections </span>
                            <span class="menu-arrow"></span>
                        </a>

                        <ul class="nav-second-level nav" aria-expanded="false">
                            <li> <a href="<?= base_url(); ?>Page/paymentList">Payment List</a> </li>
                            <li> <a href="<?= base_url(); ?>Page/revenueReports">Revenue Reports</a> </li>
                            <li> <a href="<?= base_url(); ?>Page/yearlyReport">Yearly Report</a> </li>
                            <li> <a href="<?= base_url(); ?>Page/accountingReports">Accounting Reports</a> </li>
                            <li> <a href="<?= base_url(); ?>Page/taxSummaryReport">Tax Summary Report</a> </li>
                            <li> <a href="<?= base_url(); ?>Page/paymentsWithTax">BIR Form 2307 Payments</a> </li>
                            <li> <a href="<?= base_url(); ?>Page/unifiedPayment">Unified Payment Center</a> </li>
                            <!-- <li> <a href="<?= base_url(); ?>Page/paymentRange">Payment List [ DATE ]</a> </li> -->
                        </ul>
                    </li>
                    <?php endif; ?>

                    <?php if ($__showPosMenu): ?>
                    <li>
                        <a href="javascript: void(0);" class="waves-effect">
                            <i class="ph ph-storefront"></i>
                            <span> POS </span>
                            <span class="menu-arrow"></span>
                        </a>
                        <ul class="nav-second-level" aria-expanded="false">
                            <li><a href="<?= base_url(); ?>Pos/posAdmin">POS Dashboard</a></li>
                            <li><a href="<?= base_url(); ?>Pos/posNewTransaction">New Sale</a></li>
                            <li><a href="<?= base_url(); ?>Pos/posTransactionHistory">Sales History</a></li>
                            <li><a href="<?= base_url(); ?>Pos/posReports">POS Reports</a></li>
                            <li><a href="<?= base_url(); ?>Pos/posProductList">POS Products</a></li>
                            <li><a href="<?= base_url(); ?>Pos/posCategorySettings">POS Categories</a></li>
                            <li><a href="<?= base_url(); ?>Pos/posStockLevels">POS Inventory</a></li>
                        </ul>
                    </li>
                    <?php endif; ?>

                    <?php if ($__showExpensesMenu): ?>
                    <li>
                        <a href="javascript: void(0);" class="waves-effect">
                            <i class="ph ph-wallet"></i>
                            <span> Expenses </span>
                            <span class="menu-arrow"></span>
                        </a>
                        <ul class="nav-second-level" aria-expanded="false">
                            <li> <a href="<?= base_url(); ?>Page/expensesList">Expenses List</a> </li>
                        </ul>
                    </li>
                    <?php endif; ?>

                    <?php if ($__showProjectsMenu): ?>
                    <li>
                        <a href="javascript: void(0);" class="waves-effect">
                            <i class="ph ph-clipboard"></i>
                            <span> Projects </span>
                            <span class="menu-arrow"></span>
                        </a>
                        <ul class="nav-second-level" aria-expanded="false">
                            <li> <a href="<?= base_url(); ?>Page/projectList">Project List</a> </li>
                        </ul>
                    </li>
                    <?php endif; ?>

                    <?php if ($__showTasksMenu): ?>
                    <li>
                        <a href="javascript: void(0);" class="waves-effect">
                            <i class="ph ph-list"></i>
                            <span> Tasks </span>
                            <span class="menu-arrow"></span>
                        </a>
                        <ul class="nav-second-level" aria-expanded="false">
                            <li> <a href="<?= base_url(); ?>Page/projectAddTask">Task List </a> </li>

                            <li> <a href="<?= base_url(); ?>Page/employeeTask">Task List - Per Employee</a> </li>
                            <li> <a href="<?= base_url(); ?>Page/accomplishments">Accomplishments</a> </li>
                            <li> <a href="<?= base_url(); ?>Page/employeeAccomplishment">Accomplishments - Per Employee</a> </li>
                            <li> <a href="<?= base_url(); ?>Page/ranking">Ranking</a> </li>

                        </ul>
                    </li>
                    <?php endif; ?>
                    <?php if ($__showAttendanceMenu || $__showPayrollMenu): ?>
                    <li>
                        <a href="javascript: void(0);" class="waves-effect">
                            <i class="ph ph-briefcase"></i>
                            <span> Human Resource </span>
                            <span class="menu-arrow"></span>
                        </a>
                        <ul class="nav-second-level" aria-expanded="false">
                            <?php if ($__showAttendanceMenu): ?>
                            <li> <a href="<?= base_url(); ?>Page/attendanceList">Attendance List</a> </li>
                            <li> <a href="<?= base_url(); ?>Page/employeeList">Employee List</a> </li>
                            <?php endif; ?>
                            <?php if ($__showPayrollMenu): ?>
                            <li>
                                <a href="javascript: void(0);" aria-expanded="false">Payroll <span class="menu-arrow"></span></a>
                                <ul class="nav-third-level" aria-expanded="false">
                                    <li><a href="<?= base_url(); ?>Page/payrollModule">Payroll Module</a></li>
                                    <li><a href="<?= base_url(); ?>Page/payrollSetup">Employee Setup</a></li>
                                    <li><a href="<?= base_url(); ?>Page/payrollRuns">Payroll Runs</a></li>
                                </ul>
                            </li>
                            <?php endif; ?>
                            <?php if ($__showAttendanceMenu): ?>
                            <li><a href="<?= base_url(); ?>Page/empDTR">View Employee's DTR</a></li>
                            <?php endif; ?>
                        </ul>
                    </li>
                    <?php endif; ?>

                    <?php if ($__showClientsMenu): ?>
                    <li>
                        <a href="javascript: void(0);" class="waves-effect">
                            <i class="ph ph-users"></i>
                            <span> Clients </span>
                            <span class="menu-arrow"></span>
                        </a>
                        <ul class="nav-second-level" aria-expanded="false">
                            <li> <a href="<?= base_url(); ?>Page/clientList">Client List</a> </li>
                            <li> <a href="<?= base_url(); ?>Page/topClientsReport">Top Clients Report</a> </li>
                            <li> <a href="#">Inactive Clients</a> </li>
                        </ul>
                    </li>
                    <?php endif; ?>

                    <?php if ($__showNotesMenu): ?>
                    <li>
                        <a href="javascript: void(0);" class="waves-effect">
                            <i class="ph ph-notebook"></i>
                            <span> Notes </span>
                            <span class="menu-arrow"></span>
                        </a>
                        <ul class="nav-second-level" aria-expanded="false">
                            <li> <a href="<?= base_url(); ?>Page/noteList">View Notes</a> </li>
                        </ul>
                    </li>
                    <?php endif; ?>

                    <li>
                        <a href="javascript: void(0);" class="waves-effect">
                            <i class="ph ph-gear"></i>
                            <span> System Configuration </span>
                            <span class="menu-arrow"></span>
                        </a>
                        <ul class="nav-second-level" aria-expanded="false">
                            <li> <a href="<?= base_url(); ?>Page/businessDetails">Business Details</a> </li>
                            <li> <a href="<?= base_url(); ?>Settings/Department">Branches / Stores</a> </li>
                            <li> <a href="<?= base_url(); ?>Page/priceList">Service Price List</a> </li>
                            <li> <a href="<?= base_url(); ?>Settings/InvoiceUnits">Invoice Units</a> </li>
                        </ul>
                    </li>

                    <li>
                        <a href="javascript: void(0);" class="waves-effect">
                            <i class="ph ph-user-plus"></i>
                            <span> Manage Users </span>
                            <span class="menu-arrow"></span>
                        </a>
                        <ul class="nav-second-level" aria-expanded="false">
                            <li> <a href="<?= base_url(); ?>Users/">User's List</a> </li>
                        </ul>
                    </li>

                </ul>

            </div>
            <!-- End Sidebar -->

        <?php elseif (in_array($__sidebarLevel, array('staff', 'encoder', 'account'), true)): ?>
            <div id="sidebar-menu">
                <ul class="metismenu" id="side-menu">

                    <li class="menu-title">Navigation</li>

                    <li>
                        <a href="<?= base_url(); ?>Page/staff" class="waves-effect">
                            <i class="ph ph-chart-line"></i>
                            <span> Dashboard </span>
                        </a>
                    </li>

                    <?php if ($__showInvoiceMenu): ?>
                    <li>
                        <a href="javascript: void(0);" class="waves-effect">
                            <i class="ph ph-file-text"></i>
                            <span> Invoice </span>
                            <span class="menu-arrow"></span>
                        </a>
                        <ul class="nav-second-level" aria-expanded="false">
                            <?php if ($__showInvoiceFeature): ?>
                            <li> <a href="<?= base_url(); ?>Page/invList">Invoice List</a> </li>
                            <li> <a href="<?= base_url(); ?>Page/paymentList">Payment List</a> </li>
                            <?php endif; ?>
                            <?php if ($__showJobOrderFeature): ?>
                            <li> <a href="<?= base_url(); ?>Page/joList">Job Order</a> </li>
                            <?php endif; ?>
                        </ul>
                    </li>
                    <?php endif; ?>

                    <?php if ($__showDeliveriesMenu): ?>
                    <li>
                        <a href="javascript: void(0);" class="waves-effect">
                            <i class="ph ph-cube"></i>
                            <span> Deliveries </span>
                            <span class="menu-arrow"></span>
                        </a>
                        <ul class="nav-second-level" aria-expanded="false">
                            <li> <a href="<?= base_url(); ?>Page/customerDeliveryList">Delivery List</a> </li>
                            <li> <a href="<?= base_url(); ?>Page/newCustomerDelivery">New Delivery</a> </li>
                        </ul>
                    </li>
                    <?php endif; ?>

                    <?php if ($__showClientsMenu): ?>
                    <li>
                        <a href="javascript: void(0);" class="waves-effect">
                            <i class="ph ph-users"></i>
                            <span> Clients </span>
                            <span class="menu-arrow"></span>
                        </a>
                        <ul class="nav-second-level" aria-expanded="false">
                            <li> <a href="<?= base_url(); ?>Page/clientList">Client List</a> </li>
                        </ul>
                    </li>
                    <?php endif; ?>

                    <?php if ($__showAttendanceMenu): ?>
                    <li>
                        <a href="<?= base_url(); ?>Page/attendanceList" class="waves-effect">
                            <i class="ph ph-alarm"></i>
                            <span> Attendance </span>
                        </a>
                    </li>
                    <?php if (!$__isPackage2): ?>
                    <li>
                        <a href="<?= base_url(); ?>Page/myDTR" class="waves-effect">
                            <i class="ph ph-clock"></i>
                            <span> My DTR </span>
                        </a>
                    </li>
                    <?php endif; ?>
                    <?php endif; ?>

                    <?php if ($__showExpensesMenu): ?>
                    <li>
                        <a href="<?= base_url(); ?>Page/expensesList" class="waves-effect">
                            <i class="ph ph-wallet"></i>
                            <span> Expenses </span>
                        </a>
                    </li>
                    <?php endif; ?>

                    <?php if ($__showTasksMenu): ?>
                    <li>
                        <a href="javascript: void(0);" class="waves-effect">
                            <i class="ph ph-clipboard"></i>
                            <span> Task </span>
                            <span class="menu-arrow"></span>
                        </a>
                        <ul class="nav-second-level" aria-expanded="false">
                            <li> <a href="<?= base_url(); ?>Page/projectAddTask">Task List</a> </li>
                            <?php if (!$__isPackage2): ?>
                            <li> <a href="<?= base_url(); ?>Page/unassignedTask">Unassigned</a> </li>
                            <li> <a href="<?= base_url(); ?>Page/forwardedTask">Forwarded Task</a> </li>
                            <?php endif; ?>
                            <li> <a href="<?= base_url(); ?>Page/accomplishments">Accomplishments</a> </li>
                        </ul>
                    </li>
                    <?php endif; ?>

                    <?php if ($__showProjectsMenu): ?>
                    <li>
                        <a href="javascript: void(0);" class="waves-effect">
                            <i class="ph ph-clipboard"></i>
                            <span> Projects </span>
                            <span class="menu-arrow"></span>
                        </a>
                        <ul class="nav-second-level" aria-expanded="false">
                            <li> <a href="<?= base_url(); ?>Page/projectList">Project List</a> </li>

                        </ul>
                    </li>
                    <?php endif; ?>

                    <?php if ($__showTasksMenu && !$__isPackage2): ?>
                    <li>
                        <a href="<?= base_url(); ?>Page/ranking" class="waves-effect">
                            <i class="ph ph-trophy"></i>
                            <span> Ranking </span>
                        </a>
                    </li>
                    <?php endif; ?>

                    <?php if ($__showCalendarFeature && !$__isPackage2): ?>
                    <li>
                        <a href="javascript: void(0);" class="waves-effect">
                            <i class="ph ph-alarm"></i>
                            <span> Reminders </span>
                            <span class="menu-arrow"></span>
                        </a>
                        <ul class="nav-second-level" aria-expanded="false">
                            <li> <a href="<?= base_url(); ?>Reminders/">Reminder List</a> </li>

                        </ul>
                    </li>
                    <?php endif; ?>

                    <?php if ($__showNotesMenu): ?>
                    <li>
                        <a href="javascript: void(0);" class="waves-effect">
                            <i class="ph ph-notebook"></i>
                            <span> Notes </span>
                            <span class="menu-arrow"></span>
                        </a>
                        <ul class="nav-second-level" aria-expanded="false">
                            <li> <a href="<?= base_url(); ?>Page/noteList">View Notes</a> </li>

                        </ul>
                    </li>
                    <?php endif; ?>

                </ul>
            </div>
            <!-- End Sidebar -->

        <?php elseif (strtolower((string)$this->session->userdata('level')) === 'client'): ?>
            <div id="sidebar-menu">
                <ul class="metismenu" id="side-menu">
                    <li class="menu-title">Client Portal</li>

                    <li>
                        <a href="<?= base_url(); ?>Page/clientDashboard">
                            <i class="ph ph-chart-line"></i>
                            <span> Dashboard </span>
                        </a>
                    </li>



                    <li>
                        <a href="<?= base_url(); ?>Page/clientProfile">
                            <i class="ph ph-buildings"></i>
                            <span> My Company </span>
                        </a>
                    </li>

                    <li>
                        <a href="javascript: void(0);">
                            <i class="ph ph-book-open"></i>
                            <span> Knowledge Base </span>
                            <span class="menu-arrow"></span>
                        </a>
                        <ul class="nav-second-level" aria-expanded="false">
                            <li><a href="<?= base_url(); ?>Page/knowledgeBase">View All</a></li>
                            <li><a href="<?= base_url(); ?>Page/knowledgeBase?type=article">Articles</a></li>
                            <li><a href="<?= base_url(); ?>Page/knowledgeBase?type=faq">FAQs</a></li>
                        </ul>
                    </li>

                    <li>
                        <a href="javascript: void(0);">
                            <i class="ph ph-headset"></i>
                            <span> Support </span>
                            <span class="menu-arrow"></span>
                        </a>
                        <ul class="nav-second-level" aria-expanded="false">
                            <li><a href="<?= base_url(); ?>Page/clientMyTickets">My Tickets</a></li>
                            <li><a href="<?= base_url(); ?>Page/clientReportIssue">Report an Issue</a></li>
                            <li><a href="<?= site_url('client/cancelled-ticket-logs'); ?>">Cancelled Ticket Logs</a></li>
                        </ul>
                    </li>

                    <li>
                        <a href="<?= site_url('client/requested-today'); ?>">
                            <i class="ph ph-clock"></i>
                            <span> Requested Today </span>
                        </a>
                    </li>

                    <li>
                        <a href="<?= site_url('client/closed-task-report'); ?>">
                            <i class="ph ph-file-text"></i>
                            <span> Closed Task Report </span>
                        </a>
                    </li>

                    <li>
                        <a href="<?= site_url('client/pending-tasks'); ?>">
                            <i class="ph ph-hourglass"></i>
                            <span> Pending Tasks </span>
                        </a>
                    </li>

                    <li>
                        <a href="<?= base_url(); ?>Users/changepassword">
                            <i class="ph ph-lock"></i>
                            <span> Change Password </span>
                        </a>
                    </li>

                    <li>
                        <a href="<?= base_url(); ?>login/logout">
                            <i class="ph ph-sign-out"></i>
                            <span> Logout </span>
                        </a>
                    </li>
                </ul>
            </div>
            <!-- End Sidebar -->

        <?php elseif (in_array(strtolower((string)$this->session->userdata('level')), array('pos admin', 'manager'), true)): ?>
            <div id="sidebar-menu">
                <ul class="metismenu" id="side-menu">
                    <li class="menu-title">POS Management</li>

                    <li>
                        <a href="<?= base_url(); ?>Pos/posAdmin" class="waves-effect">
                            <i class="ph ph-chart-line"></i>
                            <span> Dashboard </span>
                        </a>
                    </li>

                    <li>
                        <a href="javascript: void(0);" class="waves-effect">
                            <i class="ph ph-cash"></i>
                            <span> Sales </span>
                            <span class="menu-arrow"></span>
                        </a>
                        <ul class="nav-second-level" aria-expanded="false">
                            <li><a href="<?= base_url(); ?>Pos/posNewTransaction">New Sale</a></li>
                            <li><a href="<?= base_url(); ?>Pos/posTransactionHistory">Sales List</a></li>
                            <li><a href="<?= base_url(); ?>Pos/posReports">POS Reports</a></li>
                            <li><a href="<?= base_url(); ?>Pos/posReturnsVoids">Returns / Voids</a></li>
                        </ul>
                    </li>

                    <li>
                        <a href="javascript: void(0);" class="waves-effect">
                            <i class="ph ph-archive"></i>
                            <span> Inventory </span>
                            <span class="menu-arrow"></span>
                        </a>
                        <ul class="nav-second-level" aria-expanded="false">
                            <li><a href="<?= base_url(); ?>Pos/posStockLevels">Inventory List</a></li>
                            <li><a href="<?= base_url(); ?>Pos/posLowStockItems">Low Stock Items</a></li>
                        </ul>
                    </li>

                    <li>
                        <a href="javascript: void(0);" class="waves-effect">
                            <i class="ph ph-clock"></i>
                            <span> Expiry </span>
                            <span class="menu-arrow"></span>
                        </a>
                        <ul class="nav-second-level" aria-expanded="false">
                            <li><a href="<?= base_url(); ?>Pos/posExpiringSoon">Expiring Soon</a></li>
                            <li><a href="<?= base_url(); ?>Pos/posExpiredProducts">Expired Products</a></li>
                        </ul>
                    </li>

                    <li>
                        <a href="javascript: void(0);" class="waves-effect">
                            <i class="ph ph-user"></i>
                            <span> Users </span>
                            <span class="menu-arrow"></span>
                        </a>
                        <ul class="nav-second-level" aria-expanded="false">
                            <li><a href="<?= base_url(); ?>Users/">Manage POS User</a></li>
                        </ul>
                    </li>

                    <?php if (strtolower((string)$this->session->userdata('level')) === 'admin'): ?>
                        <li>
                            <a href="javascript: void(0);" class="waves-effect">
                                <i class="ph ph-gear"></i>
                                <span> Settings </span>
                                <span class="menu-arrow"></span>
                            </a>
                            <ul class="nav-second-level" aria-expanded="false">
                                <li><a href="<?= base_url(); ?>Settings/">System Settings</a></li>
                            </ul>
                        </li>
                    <?php endif; ?>

                    <li>
                        <a href="<?= base_url(); ?>login/logout" class="waves-effect">
                            <i class="ph ph-sign-out"></i>
                            <span> Logout </span>
                        </a>
                    </li>
                </ul>

            </div>
            <!-- End Sidebar -->

        <?php elseif (in_array(strtolower((string)$this->session->userdata('level')), array('pos staff', 'cashier'), true)): ?>
            <div id="sidebar-menu">
                <ul class="metismenu" id="side-menu">
                    <li class="menu-title">POS Cashier Panel</li>

                    <li>
                        <a href="<?= base_url(); ?>Pos/posStaff" class="waves-effect">
                            <i class="ph ph-chart-line"></i>
                            <span> Overview </span>
                        </a>
                    </li>

                    <li>
                        <a href="javascript: void(0);" class="waves-effect">
                            <i class="ph ph-cube"></i>
                            <span> Product Entry </span>
                            <span class="menu-arrow"></span>
                        </a>
                        <ul class="nav-second-level" aria-expanded="false">
                            <li><a href="<?= base_url(); ?>Pos/posProductList">Product List</a></li>
                        </ul>
                    </li>

                    <li>
                        <a href="javascript: void(0);" class="waves-effect">
                            <i class="ph ph-cash"></i>
                            <span> Point of Sale </span>
                            <span class="menu-arrow"></span>
                        </a>
                        <ul class="nav-second-level" aria-expanded="false">
                            <li><a href="<?= base_url(); ?>Pos/posNewTransaction">New Transaction</a></li>
                            <li><a href="<?= base_url(); ?>Pos/posTransactionHistory">Transaction History</a></li>
                            <li><a href="<?= base_url(); ?>Pos/posReports">POS Reports</a></li>
                            <li><a href="<?= base_url(); ?>Pos/posReturnsVoids">Returns / Voids</a></li>
                        </ul>
                    </li>

                    <li>
                        <a href="javascript: void(0);" class="waves-effect">
                            <i class="ph ph-archive"></i>
                            <span> Inventory </span>
                            <span class="menu-arrow"></span>
                        </a>
                        <ul class="nav-second-level" aria-expanded="false">
                            <li><a href="<?= base_url(); ?>Pos/posStockLevels">Stock Levels</a></li>
                            <li><a href="<?= base_url(); ?>Pos/posLowStockItems">Low Stock Items</a></li>
                        </ul>
                    </li>

                    <li>
                        <a href="javascript: void(0);" class="waves-effect">
                            <i class="ph ph-clock"></i>
                            <span> Expiry Monitoring </span>
                            <span class="menu-arrow"></span>
                        </a>
                        <ul class="nav-second-level" aria-expanded="false">
                            <li><a href="<?= base_url(); ?>Pos/posExpiringSoon">Expiring Soon</a></li>
                            <li><a href="<?= base_url(); ?>Pos/posExpiredProducts">Expired Products</a></li>
                        </ul>
                    </li>

                    <li>
                        <a href="javascript: void(0);" class="waves-effect">
                            <i class="ph ph-user"></i>
                            <span> My Account </span>
                            <span class="menu-arrow"></span>
                        </a>
                        <ul class="nav-second-level" aria-expanded="false">
                            <li><a href="<?= base_url(); ?>Page/staffprofile?id=<?= $this->session->userdata('IDNumber'); ?>">Profile</a></li>
                            <li><a href="<?= base_url(); ?>Users/changepassword">Change Password</a></li>
                            <li><a href="<?= base_url(); ?>login/logout">Logout</a></li>
                        </ul>
                    </li>
                </ul>

            </div>
            <!-- End Sidebar -->

        <?php endif; ?>



        <div class="clearfix"></div>

    </div>
    <!-- Sidebar -left -->

</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.left-side-menu .waves-effect').forEach(function(link) {
            link.classList.remove('waves-effect');
        });
    });
</script>
