<?php
$clientRecords = isset($data) ? $data : array();
if ($clientRecords instanceof Traversable) {
    $clientRecords = iterator_to_array($clientRecords, false);
}
$clientRecords = is_array($clientRecords) ? array_values($clientRecords) : array();

$isAdmin = strtolower(trim((string) $this->session->userdata('level'))) === 'admin';
$totalClients = count($clientRecords);
$activeCount = 0;
$inactiveCount = 0;
$prospectCount = 0;
$donationCount = 0;
$portalEnabledCount = 0;

foreach ($clientRecords as $clientSummaryRow) {
    $clientStat = trim((string) ($clientSummaryRow->ClientStat ?? ''));
    if (strcasecmp($clientStat, 'Active') === 0) {
        $activeCount++;
    } elseif (strcasecmp($clientStat, 'Inactive') === 0) {
        $inactiveCount++;
    } elseif (strcasecmp($clientStat, 'Prospect') === 0) {
        $prospectCount++;
    } elseif (strcasecmp($clientStat, 'Donation') === 0) {
        $donationCount++;
    }

    if (!empty($clientSummaryRow->portal_enabled)) {
        $portalEnabledCount++;
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<?php include('includes/head.php'); ?>


<body>

    <div id="wrapper">

        <?php include('includes/top-nav-bar.php'); ?>
        <?php include('includes/sidebar.php'); ?>

        <div class="content-page">
            <div class="content">
                <div class="container-fluid client-list-page berps-page">

                    <?php if ($this->session->flashdata('success')): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?= htmlspecialchars((string) $this->session->flashdata('success'), ENT_QUOTES, 'UTF-8'); ?>
                            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                        </div>
                    <?php endif; ?>

                    <?php if ($this->session->flashdata('danger')): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?= htmlspecialchars((string) $this->session->flashdata('danger'), ENT_QUOTES, 'UTF-8'); ?>
                            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                        </div>
                    <?php endif; ?>

                    <div class="page-header">
                        <div>
                            <div class="page-eyebrow">Clients</div>
                            <h1 class="page-title">Client Directory</h1>
                            <div class="page-subtitle">Review company profiles, portal access, lead sources, and customer notes.</div>
                        </div>
                        <div class="page-actions">
                            <a href="<?= base_url(); ?>Page/clientEntry" class="btn-submit">
                                <i class="mdi mdi-account-plus-outline"></i>
                                Add Client
                            </a>
                        </div>
                    </div>

                    <div class="stats-grid">
                        <button type="button" class="stat-card stat-total filter-status-card is-active" data-status="">
                            <div class="stat-label">Total Clients</div>
                            <div class="stat-value"><?= number_format($totalClients); ?></div>
                            <div class="stat-meta">All profiles</div>
                        </button>
                        <button type="button" class="stat-card stat-active filter-status-card" data-status="active">
                            <div class="stat-label">Active</div>
                            <div class="stat-value"><?= number_format($activeCount); ?></div>
                            <div class="stat-meta">Active service accounts</div>
                        </button>
                        <button type="button" class="stat-card stat-prospect filter-status-card" data-status="prospect">
                            <div class="stat-label">Prospects</div>
                            <div class="stat-value"><?= number_format($prospectCount); ?></div>
                            <div class="stat-meta">Leads in progress</div>
                        </button>
                        <button type="button" class="stat-card filter-status-card" data-status="inactive">
                            <div class="stat-label">Inactive</div>
                            <div class="stat-value"><?= number_format($inactiveCount); ?></div>
                            <div class="stat-meta">Paused accounts</div>
                        </button>
                        <button type="button" class="stat-card filter-status-card" data-status="donation">
                            <div class="stat-label">Donation</div>
                            <div class="stat-value"><?= number_format($donationCount); ?></div>
                            <div class="stat-meta">Donation-based</div>
                        </button>
                        <button type="button" class="stat-card stat-portal">
                            <div class="stat-label">Portal Enabled</div>
                            <div class="stat-value"><?= number_format($portalEnabledCount); ?></div>
                            <div class="stat-meta">Portal access on</div>
                        </button>
                    </div>

                    <div class="card-stack">
                        <div class="theme-card">
                            <div class="theme-card-head">
                                <h5 class="theme-card-title">Client Records</h5>
                                <div class="theme-card-subtitle">Review company contact details, portal access, and status.</div>
                            </div>
                            <div class="theme-card-body">
                                <div class="table-responsive">
                                    <table id="client-table" class="table table-hover mb-0">
                                        <thead>
                                            <tr>
                                                <th>Client ID</th>
                                                <th>Client</th>
                                                <th>Address</th>
                                                <?php if ($isAdmin): ?>
                                                    <th>Contact Person</th>
                                                    <th>Contact No.</th>
                                                    <th>Company Email</th>
                                                    <th>Source</th>
                                                    <th>Portal</th>
                                                    <th>Status</th>
                                                    <th class="text-center">Actions</th>
                                                <?php endif; ?>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (!empty($clientRecords)): ?>
                                                <?php foreach ($clientRecords as $row): ?>
                                                    <?php
                                                    $clientId      = isset($row->CustID) ? (string)$row->CustID : '';
                                                    $clientName    = isset($row->Customer) ? (string)$row->Customer : '';
                                                    $address       = isset($row->Address) ? (string)$row->Address : '';
                                                    $contactPerson = isset($row->ContactPerson) ? (string)$row->ContactPerson : '';
                                                    $contactNumber = isset($row->ContactNos) ? (string)$row->ContactNos : '';
                                                    $companyEmail  = isset($row->CompanyEmail) ? (string)$row->CompanyEmail : '';
                                                    $clientStat    = isset($row->ClientStat) ? (string)$row->ClientStat : '';

                                                    $clientSource  = isset($row->client_source) ? (string)$row->client_source : '';
                                                    $salesAgent    = isset($row->sales_agent) ? (string)$row->sales_agent : '';
                                                    $facebookLink  = isset($row->facebook_link) ? (string)$row->facebook_link : '';
                                                    $clientEmail2  = isset($row->client_email) ? (string)$row->client_email : '';
                                                    $portalEnabled = !empty($row->portal_enabled);
                                                    $notes         = isset($row->notes) ? (string)$row->notes : '';
                                                    $clientProfileParams = array();
                                                    if ($clientId !== '') {
                                                        $clientProfileParams['cust_id'] = $clientId;
                                                    } elseif ($clientName !== '') {
                                                        $clientProfileParams['customer'] = $clientName;
                                                    }
                                                    $clientProfileUrl = base_url() . 'Page/clientProfile';
                                                    if (!empty($clientProfileParams)) {
                                                        $clientProfileUrl .= '?' . http_build_query($clientProfileParams);
                                                    }

                                                    $statusClass = 'status-prospect';
                                                    if (strcasecmp($clientStat, 'Active') === 0) {
                                                        $statusClass = 'status-active';
                                                    } elseif (strcasecmp($clientStat, 'Inactive') === 0) {
                                                        $statusClass = 'status-inactive';
                                                    } elseif (strcasecmp($clientStat, 'Donation') === 0) {
                                                        $statusClass = 'status-donation';
                                                    }
                                                    ?>
                                                    <tr data-client-status="<?= htmlspecialchars(strtolower(trim($clientStat)), ENT_QUOTES, 'UTF-8'); ?>">
                                                        <td class="font-weight-semibold"><?= htmlspecialchars($clientId, ENT_QUOTES, 'UTF-8'); ?></td>
                                                        <td>
                                                            <?php if ($clientName !== '' && !empty($clientProfileParams) && $isAdmin): ?>
                                                                <a class="client-name-link" href="<?= htmlspecialchars($clientProfileUrl, ENT_QUOTES, 'UTF-8'); ?>">
                                                                    <?= htmlspecialchars($clientName, ENT_QUOTES, 'UTF-8'); ?>
                                                                </a>
                                                            <?php elseif ($clientName !== ''): ?>
                                                                <?= htmlspecialchars($clientName, ENT_QUOTES, 'UTF-8'); ?>
                                                            <?php else: ?>
                                                                -
                                                            <?php endif; ?>
                                                        </td>
                                                        <td><?= $address !== '' ? htmlspecialchars($address, ENT_QUOTES, 'UTF-8') : '-'; ?></td>
                                                        <?php if ($isAdmin): ?>
                                                            <td><?= $contactPerson !== '' ? htmlspecialchars($contactPerson, ENT_QUOTES, 'UTF-8') : '-'; ?></td>
                                                            <td><?= $contactNumber !== '' ? htmlspecialchars($contactNumber, ENT_QUOTES, 'UTF-8') : '-'; ?></td>
                                                            <td><?= $companyEmail !== '' ? htmlspecialchars($companyEmail, ENT_QUOTES, 'UTF-8') : '-'; ?></td>
                                                            <td><?= $clientSource !== '' ? htmlspecialchars($clientSource, ENT_QUOTES, 'UTF-8') : '-'; ?></td>
                                                            <td data-order="<?= $portalEnabled ? 'Enabled' : 'Disabled'; ?>">
                                                                <span class="portal-text <?= $portalEnabled ? 'portal-enabled' : 'portal-disabled'; ?>">
                                                                    <?= $portalEnabled ? 'Enabled' : 'Disabled'; ?>
                                                                </span>
                                                            </td>
                                                            <td data-order="<?= htmlspecialchars($clientStat, ENT_QUOTES, 'UTF-8'); ?>">
                                                                <?php if ($clientStat !== ''): ?>
                                                                    <span class="client-status-text <?= $statusClass; ?>"><?= htmlspecialchars($clientStat, ENT_QUOTES, 'UTF-8'); ?></span>
                                                                <?php else: ?>
                                                                    <span class="text-muted">-</span>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td class="text-center">
                                                                <div class="client-actions">
                                                                    <?php if (!empty($clientProfileParams)): ?>
                                                                        <a href="<?= htmlspecialchars($clientProfileUrl, ENT_QUOTES, 'UTF-8'); ?>" class="action-btn view" data-label="View Company" title="View Company">
                                                                            <i class="mdi mdi-eye-outline"></i>
                                                                        </a>
                                                                    <?php endif; ?>

                                                                    <?php if ($isAdmin): ?>
                                                                        <button
                                                                            type="button"
                                                                            class="action-btn edit"
                                                                            data-label="Edit"
                                                                            title="Edit Client"
                                                                            data-toggle="modal"
                                                                            data-target="#editClientModal<?= htmlspecialchars($clientId, ENT_QUOTES, 'UTF-8'); ?>">
                                                                            <i class="mdi mdi-square-edit-outline"></i>
                                                                        </button>

                                                                        <form method="post" action="" class="inline-delete-form"
                                                                            data-berps-confirm="This permanently removes the client record. This action cannot be undone."
                                                                            data-berps-confirm-title="Delete client?"
                                                                            data-berps-confirm-label="Delete client">
                                                                            <input type="hidden" name="CustID" value="<?= htmlspecialchars($clientId, ENT_QUOTES, 'UTF-8'); ?>">
                                                                            <button type="submit" name="deleteclient" value="1" class="action-btn delete" data-label="Delete" title="Delete Client">
                                                                                <i class="mdi mdi-trash-can-outline"></i>
                                                                            </button>
                                                                        </form>
                                                                    <?php endif; ?>
                                                                </div>
                                                            </td>
                                                        <?php endif; ?>
                                                    </tr>

                                                    <?php if ($isAdmin): ?>
                                                        <div class="modal fade client-modal berps-form-modal" id="editClientModal<?= htmlspecialchars($clientId, ENT_QUOTES, 'UTF-8'); ?>" tabindex="-1" role="dialog" aria-labelledby="editClientModal<?= htmlspecialchars($clientId, ENT_QUOTES, 'UTF-8'); ?>Title" aria-hidden="true">
                                                            <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                                                                <div class="modal-content">
                                                                    <div class="modal-header">
                                                                        <div>
                                                                            <h2 class="modal-title mb-0" id="editClientModal<?= htmlspecialchars($clientId, ENT_QUOTES, 'UTF-8'); ?>Title">Update Client</h2>
                                                                            <p class="berps-modal-subtitle">Edit company, contact, access, and status information.</p>
                                                                        </div>
                                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                            <span>&times;</span>
                                                                        </button>
                                                                    </div>

                                                                    <form method="post" action="">
                                                                        <div class="modal-body">
                                                                            <div class="form-row">
                                                                                <div class="form-group col-md-4">
                                                                                    <label>Client ID</label>
                                                                                    <input type="text" class="form-control" name="CustID" value="<?= htmlspecialchars($clientId, ENT_QUOTES, 'UTF-8'); ?>" readonly>
                                                                                </div>
                                                                                <div class="form-group col-md-8">
                                                                                    <label>Client</label>
                                                                                    <input type="text" class="form-control" name="Customer" value="<?= htmlspecialchars($clientName, ENT_QUOTES, 'UTF-8'); ?>" required>
                                                                                </div>
                                                                            </div>

                                                                            <div class="form-group">
                                                                                <label>Address</label>
                                                                                <input type="text" class="form-control" name="Address" value="<?= htmlspecialchars($address, ENT_QUOTES, 'UTF-8'); ?>" required>
                                                                            </div>

                                                                            <div class="form-row">
                                                                                <div class="form-group col-md-4">
                                                                                    <label>Contact Person</label>
                                                                                    <input type="text" class="form-control" name="ContactPerson" value="<?= htmlspecialchars($contactPerson, ENT_QUOTES, 'UTF-8'); ?>">
                                                                                </div>
                                                                                <div class="form-group col-md-4">
                                                                                    <label>Contact Nos.</label>
                                                                                    <input type="text" class="form-control" name="Contact" value="<?= htmlspecialchars($contactNumber, ENT_QUOTES, 'UTF-8'); ?>">
                                                                                </div>
                                                                                <div class="form-group col-md-4">
                                                                                    <label>Company E-mail</label>
                                                                                    <input type="email" class="form-control" name="CompanyEmail" value="<?= htmlspecialchars($companyEmail, ENT_QUOTES, 'UTF-8'); ?>">
                                                                                </div>
                                                                            </div>

                                                                            <div class="form-row">
                                                                                <div class="form-group col-md-6">
                                                                                    <label>Client Source</label>
                                                                                    <select class="form-control" name="client_source">
                                                                                        <option value="">Select Source</option>
                                                                                        <option value="Facebook Ads" <?= strcasecmp(trim((string)$clientSource), 'Facebook Ads') === 0 ? 'selected' : ''; ?>>Facebook Ads</option>
                                                                                        <option value="E-mail Marketing" <?= strcasecmp(trim((string)$clientSource), 'E-mail Marketing') === 0 ? 'selected' : ''; ?>>E-mail Marketing</option>
                                                                                        <option value="Referral" <?= strcasecmp(trim((string)$clientSource), 'Referral') === 0 ? 'selected' : ''; ?>>Referral</option>
                                                                                        <option value="Others" <?= strcasecmp(trim((string)$clientSource), 'Others') === 0 ? 'selected' : ''; ?>>Others</option>
                                                                                    </select>
                                                                                </div>
                                                                                <div class="form-group col-md-6">
                                                                                    <label>Sales Agent</label>
                                                                                    <input type="text" class="form-control" name="sales_agent" value="<?= htmlspecialchars($salesAgent, ENT_QUOTES, 'UTF-8'); ?>">
                                                                                </div>
                                                                            </div>

                                                                            <div class="form-row">
                                                                                <div class="form-group col-md-6">
                                                                                    <label>Facebook Link</label>
                                                                                    <input type="text" class="form-control" name="facebook_link" value="<?= htmlspecialchars($facebookLink, ENT_QUOTES, 'UTF-8'); ?>">
                                                                                </div>
                                                                                <div class="form-group col-md-6">
                                                                                    <label>Client Email</label>
                                                                                    <input type="email" class="form-control" name="client_email" value="<?= htmlspecialchars($clientEmail2, ENT_QUOTES, 'UTF-8'); ?>">
                                                                                </div>
                                                                            </div>

                                                                            <div class="form-row">
                                                                                <div class="form-group col-md-6">
                                                                                    <label>Status</label>
                                                                                    <select class="form-control" name="ClientStat" required>
                                                                                        <option value="Active" <?= strcasecmp($clientStat, 'Active') === 0 ? 'selected' : ''; ?>>Active</option>
                                                                                        <option value="Inactive" <?= strcasecmp($clientStat, 'Inactive') === 0 ? 'selected' : ''; ?>>Inactive</option>
                                                                                        <option value="Prospect" <?= strcasecmp($clientStat, 'Prospect') === 0 ? 'selected' : ''; ?>>Prospect</option>
                                                                                        <option value="Donation" <?= strcasecmp($clientStat, 'Donation') === 0 ? 'selected' : ''; ?>>Donation</option>
                                                                                    </select>
                                                                                </div>
                                                                                <div class="form-group col-md-6">
                                                                                    <label>Portal Access</label>
                                                                                    <select class="form-control" name="portal_enabled">
                                                                                        <option value="0" <?= !$portalEnabled ? 'selected' : ''; ?>>Disabled</option>
                                                                                        <option value="1" <?= $portalEnabled ? 'selected' : ''; ?>>Enabled</option>
                                                                                    </select>
                                                                                </div>
                                                                                <div class="form-group col-md-6">
                                                                                    <label>Invoice Access</label>
                                                                                    <select class="form-control" name="invoice_access_enabled">
                                                                                        <option value="0" <?= !($row->invoice_access_enabled ?? 0) ? 'selected' : ''; ?>>Disabled</option>
                                                                                        <option value="1" <?= ($row->invoice_access_enabled ?? 0) ? 'selected' : ''; ?>>Enabled</option>
                                                                                    </select>
                                                                                    <small class="form-text text-muted">Enable/disable invoice features for client portal access.</small>
                                                                                </div>
                                                                            </div>

                                                                            <div class="form-row">
                                                                                <div class="form-group col-md-6">
                                                                                    <label>Portal Password</label>
                                                                                    <input type="password" class="form-control" name="portal_password" placeholder="Leave blank to keep the current password">
                                                                                    <small class="form-text text-muted">Clients sign in using their Client Email and this portal password.</small>
                                                                                </div>
                                                                                <div class="form-group col-md-6">
                                                                                    <label>Date Added</label>
                                                                                    <input type="date" class="form-control" name="created_at" value="<?= isset($row->created_at) ? date('Y-m-d', strtotime($row->created_at)) : date('Y-m-d'); ?>">
                                                                                </div>
                                                                            </div>

                                                                            <div class="form-group">
                                                                                <label>Notes</label>
                                                                                <textarea class="form-control" name="notes" rows="4"><?= htmlspecialchars($notes, ENT_QUOTES, 'UTF-8'); ?></textarea>
                                                                            </div>
                                                                        </div>
                                                                        <div class="modal-footer">
                                                                            <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancel</button>
                                                                            <button type="submit" name="updateclient" value="1" class="btn btn-primary">Update Client</button>
                                                                        </div>
                                                                    </form>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    <?php endif; ?>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="10"><div class="berps-empty-state"><i class="mdi mdi-account-search-outline berps-empty-state__icon" aria-hidden="true"></i><span>No clients recorded yet.</span></div></td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

            </div>
            <?php include('includes/footer.php'); ?>
        </div>

        <?php include('includes/themecustomizer.php'); ?>

        <script src="<?= base_url(); ?>assets/js/vendor.min.js"></script>
        <script src="<?= base_url(); ?>assets/libs/sweetalert2/sweetalert2.min.js"></script>
        <script src="<?= base_url(); ?>assets/js/app.min.js"></script>
        <script src="<?= base_url(); ?>assets/libs/datatables/jquery.dataTables.min.js"></script>
        <script src="<?= base_url(); ?>assets/libs/datatables/dataTables.bootstrap4.min.js"></script>
        <script src="<?= base_url(); ?>assets/libs/datatables/dataTables.responsive.min.js"></script>
        <script src="<?= base_url(); ?>assets/libs/datatables/responsive.bootstrap4.min.js"></script>

        <script>
            (function($) {
                'use strict';

                $(function() {
                    var clientTable = null;
                    var clientTableElement = $('#client-table');
                    var clientTableNode = clientTableElement.get(0) || null;
                    var selectedStatus = '';

                    if (clientTableElement.length) {
                        if ($.fn.DataTable.isDataTable('#client-table')) {
                            clientTableElement.DataTable().destroy();
                        }
                        clientTable = clientTableElement.DataTable({
                            responsive: true,
                            autoWidth: false,
                            order: [[1, 'asc']],
                            pageLength: 10,
                            lengthMenu: [10, 25, 50, 100],
                            dom: '<"row align-items-center mb-3"<"col-sm-6"l><"col-sm-6 text-sm-right"f>>' +
                                 'rt' +
                                 '<"row align-items-center mt-3"<"col-sm-6"i><"col-sm-6"p>>',
                            language: {
                                emptyTable: 'No clients recorded yet.',
                                search: 'Search:',
                                searchPlaceholder: 'Client name, ID, email...',
                                lengthMenu: 'Show _MENU_ entries',
                                info: 'Showing _START_ to _END_ of _TOTAL_ clients'
                            },
                            columnDefs: [{
                                targets: -1,
                                orderable: false,
                                searchable: false
                            }]
                        });
                    }

                    $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                        if (!clientTableNode || settings.nTable !== clientTableNode) return true;
                        if (!clientTable) return true;
                        if (!selectedStatus) return true;

                        var rowNode = settings.aoData[dataIndex] ? settings.aoData[dataIndex].nTr : null;
                        if (!rowNode) return true;

                        var rowStatus = ($(rowNode).attr('data-client-status') || '').toLowerCase();
                        return rowStatus === selectedStatus.toLowerCase();
                    });

                    $('.filter-status-card').on('click', function() {
                        if (!clientTable) return;

                        var status = $(this).data('status') || '';
                        selectedStatus = status;

                        $('.filter-status-card').removeClass('is-active');
                        $(this).addClass('is-active');

                        clientTable.draw();
                    });
                });
            })(jQuery);
        </script>

    </div>
</body>

</html>
