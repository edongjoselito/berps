<!DOCTYPE html>
<html lang="en">

<?php include('includes/head.php'); ?>

<body>

  <div id="wrapper">

    <?php include('includes/top-nav-bar.php'); ?>
    <?php include('includes/sidebar.php'); ?>

    <div class="content-page">
      <div class="content">
        <?php
        $productName = isset($_GET['productName']) ? htmlspecialchars($_GET['productName'], ENT_QUOTES, 'UTF-8') : 'Product';
        $deliveries = [];
        if (!empty($data)) {
          foreach ($data as $row) {
            $hasDetails = trim((string) $row->productCode) !== '' ||
              trim((string) $row->productDescription) !== '' ||
              (float) $row->purchasePrice > 0 ||
              (float) $row->sellingPrice > 0 ||
              (float) $row->QtyDelivered > 0;
            if (!$hasDetails) {
              continue;
            }
            $deliveries[] = $row;
          }
        }
        ?>
        <div class="container-fluid delivery-list-page">
          <style>
            .delivery-list-page .page-title-box {
              padding: 18px 0;
              margin-bottom: 20px;
              text-align: center;
            }

            .delivery-list-page .page-title-box h4 {
              margin-bottom: 8px;
              display: block;
              line-height: 1.3;
            }

            .delivery-list-page .page-title-box p {
              margin-bottom: 10px;
              color: #6c757d;
            }

            .delivery-list-page .page-title-box .breadcrumb {
              background: transparent;
              padding: 0;
              margin: 0;
              justify-content: center;
            }

            .delivery-list-page .card {
              border: 1px solid rgba(15, 23, 42, 0.08);
              border-radius: 14px;
            }

            .delivery-list-page .card-header {
              border-bottom: 1px solid rgba(15, 23, 42, 0.08);
              background: rgba(248, 250, 252, 0.65);
            }

            .delivery-list-page .table thead th {
              font-size: 0.85rem;
              letter-spacing: 0.01em;
            }

            .delivery-list-page .table td,
            .delivery-list-page .table th {
              vertical-align: middle;
            }

            .delivery-list-page .table-empty {
              padding: 36px 0;
            }
          </style>

          <div class="row">
            <div class="col-12">
              <div class="page-title-box">

              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-12">
              <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                  <div>
                    <h5 class="mb-0">Deliveries</h5>
                    <small class="text-muted"><?= $productName; ?></small>
                  </div>
                </div>
                <div class="card-body">
                  <div class="table-responsive">
                    <table id="delivery-table" class="table table-striped table-bordered w-100">
                      <thead>
                        <tr>
                          <th>Product Code</th>
                          <th>Product Description</th>
                          <th>Purchase Price</th>
                          <th>Selling Price</th>
                          <th>Quantity Delivered</th>
                          <th>Unit</th>
                          <th>Supplier</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php if (!empty($deliveries)): ?>
                          <?php foreach ($deliveries as $row): ?>
                            <tr>
                              <td><?= htmlspecialchars($row->productCode, ENT_QUOTES, 'UTF-8'); ?></td>
                              <td><?= htmlspecialchars($row->productDescription, ENT_QUOTES, 'UTF-8'); ?></td>
                              <td><?= htmlspecialchars($row->purchasePrice, ENT_QUOTES, 'UTF-8'); ?></td>
                              <td><?= htmlspecialchars($row->sellingPrice, ENT_QUOTES, 'UTF-8'); ?></td>
                              <td><?= htmlspecialchars($row->QtyDelivered, ENT_QUOTES, 'UTF-8'); ?></td>
                              <td><?= htmlspecialchars($row->prodUnit, ENT_QUOTES, 'UTF-8'); ?></td>
                              <td><?= htmlspecialchars($row->Supplier, ENT_QUOTES, 'UTF-8'); ?></td>
                            </tr>
                          <?php endforeach; ?>
                        <?php else: ?>
                          <tr>
                            <td colspan="7" class="text-center text-muted table-empty">No delivery records found for this product.</td>
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
        $('#delivery-table').DataTable({
          autoWidth: false,
          order: [
            [0, 'asc']
          ],
          language: {
            emptyTable: 'No delivery records captured yet.'
          }
        });
      });
    })(jQuery);
  </script>

</body>

</html>