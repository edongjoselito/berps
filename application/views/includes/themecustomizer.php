        <div class="right-bar">
            <div class="rightbar-title">
                <a href="javascript:void(0);" class="right-bar-toggle float-right">
                    <i class="mdi mdi-close"></i>
                </a>
                <h4 class="font-17 m-0 text-white">System Configuration</h4>
            </div>
            <div class="slimscroll-menu">

                <div class="p-4">
                    <div class="alert alert-info" role="alert">
                        <strong>Quick Access </strong> to system configuration settings.
                    </div>

                    <a href="<?= base_url(); ?>Page/businessDetails" class="list-group-item list-group-item-action d-flex align-items-center mb-2 rounded">
                        <i class="mdi mdi-office-building mr-2 font-18"></i>
                        <div>
                            <div class="font-weight-bold">Business Details</div>
                            <small class="text-muted">Company profile and tax setup</small>
                        </div>
                    </a>

                    <a href="<?= base_url(); ?>Page/priceList" class="list-group-item list-group-item-action d-flex align-items-center mb-2 rounded">
                        <i class="mdi mdi-tag-multiple mr-2 font-18"></i>
                        <div>
                            <div class="font-weight-bold">Service Price List</div>
                            <small class="text-muted">Manage service pricing</small>
                        </div>
                    </a>

                    <a href="<?= base_url(); ?>Settings/InvoiceUnits" class="list-group-item list-group-item-action d-flex align-items-center mb-2 rounded">
                        <i class="mdi mdi-scale-balance mr-2 font-18"></i>
                        <div>
                            <div class="font-weight-bold">Invoice Units</div>
                            <small class="text-muted">Configure units of measure</small>
                        </div>
                    </a>

                </div>
            </div> <!-- end slimscroll-menu-->
        </div>