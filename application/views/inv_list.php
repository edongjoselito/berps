<!DOCTYPE html>
<html lang="en">

<?php include('includes/head.php'); ?>

<body>

     <div id="wrapper">

          <?php include('includes/top-nav-bar.php'); ?>
          <?php include('includes/sidebar.php'); ?>

          <div class="content-page">
               <div class="content">
                    <div class="container-fluid invoice-list-page">

                         <?php if ($this->session->flashdata('success')): ?>
                              <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                                   <?= htmlspecialchars($this->session->flashdata('success'), ENT_QUOTES, 'UTF-8'); ?>
                                   <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                   </button>
                              </div>
                         <?php endif; ?>

                         <?php if ($this->session->flashdata('danger')): ?>
                              <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
                                   <?= htmlspecialchars($this->session->flashdata('danger'), ENT_QUOTES, 'UTF-8'); ?>
                                   <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                   </button>
                              </div>
                         <?php endif; ?>

                         <?php
                         $selectedCustomerId = isset($selectedCustomerId) ? trim((string) $selectedCustomerId) : '';
                         $clientOptions = isset($data2) && is_array($data2) ? $data2 : array();
                         $selectedCustomerName = 'All customers';
                         foreach ($clientOptions as $clientOption) {
                              $optionCustId = trim((string) ($clientOption->CustID ?? ''));
                              if ($optionCustId !== '' && $optionCustId === $selectedCustomerId) {
                                   $selectedCustomerName = trim((string) ($clientOption->Customer ?? ''));
                                   break;
                              }
                         }
                         ?>

                         <style>
                              @import url('https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&display=swap');

                              .invoice-list-page {
                                   --bg: #f5f7fb;
                                   --surface: rgba(255, 255, 255, 0.96);
                                   --surface-strong: #ffffff;
                                   --surface-soft: #f8fbff;
                                   --line: #e4ebf4;
                                   --line-strong: #cfdbea;
                                   --text: #142235;
                                   --text-soft: #617489;
                                   --text-faint: #8ea0b5;
                                   --primary: #2563eb;
                                   --primary-2: #1d4ed8;
                                   --primary-soft: #eaf2ff;
                                   --success: #059669;
                                   --success-soft: #ecfdf5;
                                   --warning: #d97706;
                                   --warning-soft: #fff7ed;
                                   --danger: #e11d48;
                                   --danger-soft: #fff1f2;
                                   --shadow: 0 16px 40px rgba(15, 23, 42, 0.06);
                                   --shadow-soft: 0 8px 24px rgba(15, 23, 42, 0.04);
                                   --radius-xl: 16px;
                                   --radius-lg: 12px;
                                   --radius-md: 10px;
                                   --radius-sm: 8px;
                                   --font-body: 'DM Sans', 'Segoe UI', Arial, sans-serif;
                                   --font-head: 'DM Sans', 'Segoe UI', Arial, sans-serif;
                                   --font-mono: 'DM Sans', 'SFMono-Regular', Consolas, monospace;
                                   background:
                                        radial-gradient(circle at top left, rgba(37, 99, 235, 0.08), transparent 28%),
                                        radial-gradient(circle at top right, rgba(16, 185, 129, 0.08), transparent 24%),
                                        linear-gradient(180deg, #f8fbff 0%, #f4f7fb 100%);
                                   min-height: 100vh;
                                   padding-bottom: 100px;
                                   font-family: var(--font-body);
                              }

                              .invoice-list-page * {
                                   box-sizing: border-box;
                              }

                              .invoice-list-page .content {
                                   margin-bottom: 40px;
                              }

                              .invoice-list-page .page-header {
                                   display: flex;
                                   justify-content: space-between;
                                   align-items: flex-end;
                                   gap: 16px;
                                   margin: 16px 0 16px;
                                   flex-wrap: wrap;
                              }

                              .invoice-list-page .page-eyebrow {
                                   display: inline-flex;
                                   align-items: center;
                                   gap: 8px;
                                   padding: 7px 12px;
                                   border-radius: 999px;
                                   background: rgba(37, 99, 235, 0.08);
                                   color: var(--primary-2);
                                   font-size: 0.74rem;
                                   font-weight: 700;
                                   letter-spacing: 0.08em;
                                   text-transform: uppercase;
                                   margin-bottom: 12px;
                              }

                              .invoice-list-page .page-eyebrow::before {
                                   content: '';
                                   width: 8px;
                                   height: 8px;
                                   border-radius: 50%;
                                   background: linear-gradient(135deg, var(--primary), var(--primary-2));
                                   box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.10);
                              }

                              .invoice-list-page .page-title {
                                   margin: 0;
                                   font-family: var(--font-head);
                                   font-size: 1.5rem;
                                   line-height: 1.2;
                                   letter-spacing: -0.02em;
                                   font-weight: 700;
                                   color: var(--text);
                              }

                              .invoice-list-page .page-subtitle {
                                   margin-top: 6px;
                                   color: var(--text-soft);
                                   font-size: 0.9rem;
                                   max-width: 760px;
                              }

                              .invoice-list-page .page-actions {
                                   display: flex;
                                   gap: 12px;
                                   flex-wrap: wrap;
                              }

                              .invoice-list-page .stats-grid {
                                   display: grid;
                                   grid-template-columns: repeat(4, minmax(0, 1fr));
                                   gap: 12px;
                                   margin-bottom: 16px;
                              }

                              .invoice-list-page .stat-card {
                                   position: relative;
                                   overflow: hidden;
                                   background: var(--surface);
                                   border: 1px solid rgba(255, 255, 255, 0.72);
                                   border-radius: var(--radius-xl);
                                   box-shadow: var(--shadow-soft);
                                   padding: 14px 16px 14px;
                              }

                              .invoice-list-page .stat-card::before {
                                   content: '';
                                   position: absolute;
                                   inset: 0 0 auto 0;
                                   height: 4px;
                              }

                              .invoice-list-page .stat-total::before {
                                   background: linear-gradient(90deg, #3b82f6, #60a5fa);
                              }

                              .invoice-list-page .stat-paid::before {
                                   background: linear-gradient(90deg, #10b981, #34d399);
                              }

                              .invoice-list-page .stat-partial::before {
                                   background: linear-gradient(90deg, #f59e0b, #fbbf24);
                              }

                              .invoice-list-page .stat-unpaid::before {
                                   background: linear-gradient(90deg, #ef4444, #fb7185);
                              }

                              .invoice-list-page .stat-label {
                                   color: var(--text-faint);
                                   font-size: 0.65rem;
                                   font-weight: 600;
                                   text-transform: uppercase;
                                   letter-spacing: 0.06em;
                                   margin-bottom: 8px;
                              }

                              .invoice-list-page .stat-value {
                                   color: var(--text);
                                   font-size: 1.25rem;
                                   font-weight: 700;
                                   line-height: 1.2;
                                   letter-spacing: -0.02em;
                                   font-family: var(--font-head);
                              }

                              .invoice-list-page .stat-meta {
                                   color: var(--text-soft);
                                   font-size: 0.72rem;
                                   margin-top: 4px;
                              }

                              .invoice-list-page .card-stack {
                                   display: grid;
                                   gap: 16px;
                              }

                              .invoice-list-page .filter-card {
                                   background: var(--surface);
                                   border: 1px solid rgba(255, 255, 255, 0.72);
                                   border-radius: var(--radius-xl);
                                   box-shadow: var(--shadow-soft);
                                   padding: 16px 18px;
                                   margin-bottom: 16px;
                              }

                              .invoice-list-page .filter-card-head {
                                   display: flex;
                                   justify-content: space-between;
                                   align-items: flex-start;
                                   gap: 14px;
                                   flex-wrap: wrap;
                                   margin-bottom: 14px;
                              }

                              .invoice-list-page .filter-card-title {
                                   margin: 0;
                                   color: var(--text);
                                   font-size: 1rem;
                                   font-weight: 700;
                              }

                              .invoice-list-page .filter-card-subtitle {
                                   margin-top: 4px;
                                   color: var(--text-soft);
                                   font-size: 0.82rem;
                              }

                              .invoice-list-page .filter-active-chip {
                                   display: inline-flex;
                                   align-items: center;
                                   gap: 8px;
                                   padding: 8px 12px;
                                   border-radius: 999px;
                                   background: var(--primary-soft);
                                   color: var(--primary-2);
                                   font-size: 0.78rem;
                                   font-weight: 700;
                              }

                              .invoice-list-page .filter-form {
                                   display: grid;
                                   grid-template-columns: minmax(280px, 1fr) auto auto;
                                   gap: 12px;
                                   align-items: end;
                              }

                              .invoice-list-page .filter-field label {
                                   display: block;
                                   margin-bottom: 8px;
                                   color: var(--text-soft);
                                   font-size: 0.8rem;
                                   font-weight: 700;
                                   text-transform: uppercase;
                                   letter-spacing: 0.05em;
                              }

                              .invoice-list-page .filter-actions {
                                   display: flex;
                                   gap: 10px;
                                   flex-wrap: wrap;
                              }

                              .invoice-list-page .filter-btn {
                                   display: inline-flex;
                                   align-items: center;
                                   justify-content: center;
                                   gap: 8px;
                                   min-height: 44px;
                                   padding: 0 16px;
                                   border-radius: 12px;
                                   font-size: 0.9rem;
                                   font-weight: 700;
                                   text-decoration: none;
                                   transition: all 0.16s ease;
                              }

                              .invoice-list-page .filter-btn-primary {
                                   border: none;
                                   color: #fff;
                                   background: linear-gradient(135deg, var(--primary), var(--primary-2));
                                   box-shadow: 0 10px 24px rgba(37, 99, 235, 0.20);
                              }

                              .invoice-list-page .filter-btn-primary:hover {
                                   color: #fff;
                                   transform: translateY(-1px);
                              }

                              .invoice-list-page .filter-btn-secondary {
                                   border: 1px solid var(--line-strong);
                                   color: var(--text);
                                   background: #fff;
                              }

                              .invoice-list-page .filter-btn-secondary:hover {
                                   color: var(--primary);
                                   border-color: #bfd3ef;
                                   background: #f9fbff;
                              }

                              .invoice-list-page .customer-filter-select+.select2-container {
                                   width: 100% !important;
                              }

                              .invoice-list-page .customer-filter-select+.select2-container .select2-selection--single {
                                   height: 44px;
                                   border-radius: 12px;
                                   border: 1px solid var(--line-strong);
                                   background: #fff;
                                   box-shadow: none;
                              }

                              .invoice-list-page .customer-filter-select+.select2-container .select2-selection__rendered {
                                   line-height: 42px;
                                   padding-left: 14px;
                                   padding-right: 42px;
                                   color: var(--text);
                                   font-size: 0.92rem;
                              }

                              .invoice-list-page .customer-filter-select+.select2-container .select2-selection__placeholder {
                                   color: var(--text-faint);
                              }

                              .invoice-list-page .customer-filter-select+.select2-container .select2-selection__arrow {
                                   height: 42px;
                                   right: 10px;
                              }

                              .invoice-list-page .customer-filter-select+.select2-container.select2-container--focus .select2-selection--single,
                              .invoice-list-page .customer-filter-select+.select2-container.select2-container--open .select2-selection--single {
                                   border-color: rgba(37, 99, 235, 0.5);
                                   box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.12);
                              }

                              .invoice-list-page .filter-field .select2-dropdown {
                                   border: 1px solid var(--line-strong);
                                   border-radius: 14px;
                                   overflow: hidden;
                                   box-shadow: 0 18px 40px rgba(15, 23, 42, 0.12);
                              }

                              .invoice-list-page .filter-field .select2-search--dropdown {
                                   padding: 10px;
                                   background: #f8fbff;
                              }

                              .invoice-list-page .filter-field .select2-search__field {
                                   border-radius: 10px;
                                   border: 1px solid var(--line-strong);
                                   padding: 8px 10px;
                              }

                              .invoice-list-page .filter-field .select2-results__option {
                                   padding: 10px 14px;
                              }

                              .invoice-list-page .filter-field .select2-results__option--highlighted[aria-selected] {
                                   background: linear-gradient(135deg, var(--primary), var(--primary-2));
                              }

                              .invoice-list-page .theme-card {
                                   background: var(--surface);
                                   border: 1px solid rgba(255, 255, 255, 0.72);
                                   border-radius: var(--radius-xl);
                                   box-shadow: var(--shadow-soft);
                                   overflow: hidden;
                              }

                              .invoice-list-page .theme-card-head {
                                   padding: 14px 18px;
                                   border-bottom: 1px solid var(--line);
                                   background: linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(249, 251, 255, 0.94));
                              }

                              .invoice-list-page .theme-card-title {
                                   margin: 0;
                                   color: var(--text);
                                   font-size: 0.95rem;
                                   font-weight: 700;
                                   letter-spacing: -0.01em;
                              }

                              .invoice-list-page .theme-card-body {
                                   padding: 18px;
                              }

                              .invoice-list-page .btn-action,
                              .invoice-list-page .btn-submit {
                                   display: inline-flex;
                                   align-items: center;
                                   justify-content: center;
                                   gap: 8px;
                                   border-radius: 12px;
                                   font-size: 0.92rem;
                                   font-weight: 700;
                                   padding: 11px 18px;
                                   transition: all 0.16s ease;
                                   text-decoration: none;
                              }

                              .invoice-list-page .btn-action {
                                   border: 1px solid var(--line-strong);
                                   color: var(--text);
                                   background: #fff;
                              }

                              .invoice-list-page .btn-action:hover {
                                   color: var(--primary);
                                   border-color: #bfd3ef;
                                   background: #f9fbff;
                              }

                              .invoice-list-page .btn-submit {
                                   border: none;
                                   color: #fff;
                                   background: linear-gradient(135deg, var(--primary), var(--primary-2));
                                   box-shadow: 0 10px 24px rgba(37, 99, 235, 0.24);
                              }

                              .invoice-list-page .btn-submit:hover {
                                   transform: translateY(-1px);
                                   box-shadow: 0 14px 28px rgba(37, 99, 235, 0.28);
                              }

                              .invoice-list-page .table-responsive {
                                   border-radius: 18px;
                              }

                              .invoice-list-page #invoice-table {
                                   border-collapse: separate !important;
                                   border-spacing: 0 10px !important;
                                   margin-top: -10px !important;
                              }

                              .invoice-list-page #invoice-table thead th {
                                   background: transparent !important;
                                   color: var(--text-faint) !important;
                                   border: none !important;
                                   font-size: 0.74rem;
                                   font-weight: 700;
                                   text-transform: uppercase;
                                   letter-spacing: 0.08em;
                                   padding: 6px 14px 10px !important;
                                   white-space: nowrap;
                              }

                              .invoice-list-page #invoice-table tbody tr {
                                   box-shadow: var(--shadow-soft);
                              }

                              .invoice-list-page #invoice-table tbody td {
                                   background: #fff !important;
                                   border-top: 1px solid var(--line) !important;
                                   border-bottom: 1px solid var(--line) !important;
                                   border-left: none !important;
                                   border-right: none !important;
                                   padding: 16px 14px !important;
                                   vertical-align: middle;
                                   color: var(--text);
                                   font-size: 0.9rem;
                              }

                              .invoice-list-page #invoice-table tbody td:first-child {
                                   border-left: 1px solid var(--line) !important;
                                   border-top-left-radius: 16px;
                                   border-bottom-left-radius: 16px;
                              }

                              .invoice-list-page #invoice-table tbody td:last-child {
                                   border-right: 1px solid var(--line) !important;
                                   border-top-right-radius: 16px;
                                   border-bottom-right-radius: 16px;
                              }

                              .invoice-list-page #invoice-table tbody tr:hover td {
                                   background: #fbfdff !important;
                              }

                              .invoice-list-page .inv-no-link {
                                   display: inline-flex;
                                   align-items: center;
                                   gap: 8px;
                                   color: var(--primary-2);
                                   font-weight: 800;
                                   text-decoration: none;
                                   font-family: var(--font-mono);
                                   font-size: 0.87rem;
                              }

                              .invoice-list-page .inv-no-link:hover {
                                   color: var(--text);
                                   text-decoration: none;
                              }

                              .invoice-list-page .inv-no-link::before {
                                   content: '';
                                   width: 10px;
                                   height: 10px;
                                   border-radius: 50%;
                                   background: linear-gradient(135deg, var(--primary), #60a5fa);
                                   box-shadow: 0 0 0 5px rgba(37, 99, 235, 0.08);
                              }

                              .invoice-list-page .num-cell {
                                   font-family: var(--font-mono);
                                   font-weight: 700;
                                   color: var(--text);
                              }

                              .invoice-list-page .action-link {
                                   color: var(--primary);
                                   text-decoration: none;
                                   font-weight: 700;
                              }

                              .invoice-list-page .action-link:hover {
                                   color: var(--primary-2);
                                   text-decoration: none;
                              }

                              .invoice-list-page .payment-state {
                                   display: inline-flex;
                                   align-items: center;
                                   gap: 6px;
                                   padding: 6px 10px;
                                   border-radius: 999px;
                                   font-size: 0.72rem;
                                   font-weight: 700;
                                   letter-spacing: 0.03em;
                                   border: 1px solid transparent;
                                   white-space: nowrap;
                              }

                              .invoice-list-page .payment-state::before {
                                   content: '';
                                   width: 7px;
                                   height: 7px;
                                   border-radius: 50%;
                                   flex-shrink: 0;
                              }

                              .invoice-list-page .payment-state--paid {
                                   background: var(--success-soft);
                                   color: var(--success);
                                   border-color: #b7f0d9;
                              }

                              .invoice-list-page .payment-state--paid::before {
                                   background: var(--success);
                              }

                              .invoice-list-page .payment-state--partial {
                                   background: var(--warning-soft);
                                   color: var(--warning);
                                   border-color: #fed7aa;
                              }

                              .invoice-list-page .payment-state--partial::before {
                                   background: var(--warning);
                              }

                              .invoice-list-page .payment-state--unpaid {
                                   background: var(--danger-soft);
                                   color: var(--danger);
                                   border-color: #fecdd3;
                              }

                              .invoice-list-page .payment-state--unpaid::before {
                                   background: var(--danger);
                              }

                              .invoice-list-page .tbl-actions {
                                   display: flex;
                                   align-items: center;
                                   justify-content: center;
                                   gap: 6px;
                                   flex-wrap: wrap;
                              }

                              .invoice-list-page .tbl-btn {
                                   display: inline-flex;
                                   align-items: center;
                                   justify-content: center;
                                   min-height: 34px;
                                   padding: 7px 12px;
                                   border-radius: 10px;
                                   border: 1px solid transparent;
                                   font-size: 0.76rem;
                                   font-weight: 700;
                                   text-decoration: none;
                                   transition: all 0.16s ease;
                                   white-space: nowrap;
                                   background: #fff;
                              }

                              .invoice-list-page .tbl-btn:hover {
                                   transform: translateY(-1px);
                                   text-decoration: none;
                              }

                              .invoice-list-page .tbl-btn-print {
                                   background: #eff6ff;
                                   color: var(--primary);
                                   border-color: #bfdbfe;
                              }

                              .invoice-list-page .tbl-btn-print:hover {
                                   background: var(--primary);
                                   border-color: var(--primary);
                                   color: #fff;
                              }

                              .invoice-list-page .tbl-btn-payment {
                                   background: #ecfdf5;
                                   color: var(--success);
                                   border-color: #a7f3d0;
                              }

                              .invoice-list-page .tbl-btn-payment:hover {
                                   background: var(--success);
                                   border-color: var(--success);
                                   color: #fff;
                              }

                              .invoice-list-page .tbl-btn-edit {
                                   background: #fff7ed;
                                   color: var(--warning);
                                   border-color: #fdba74;
                              }

                              .invoice-list-page .tbl-btn-edit:hover {
                                   background: var(--warning);
                                   border-color: var(--warning);
                                   color: #fff;
                              }

                              .invoice-list-page .tbl-btn-delete {
                                   background: #fff1f2;
                                   color: var(--danger);
                                   border-color: #fda4af;
                              }

                              .invoice-list-page .tbl-btn-delete:hover {
                                   background: var(--danger);
                                   border-color: var(--danger);
                                   color: #fff;
                              }

                              .modal-content {
                                   border: 1px solid rgba(255, 255, 255, 0.7);
                                   border-radius: 22px;
                                   overflow: hidden;
                                   box-shadow: 0 28px 60px rgba(15, 23, 42, 0.18);
                              }

                              .modal-header.inv-modal-header {
                                   border: none;
                                   padding: 22px 24px;
                                   background: linear-gradient(135deg, var(--primary), #0ea5e9);
                              }

                              .modal-header.inv-modal-header .modal-title {
                                   color: #fff;
                                   font-size: 1.08rem;
                                   font-weight: 800;
                                   letter-spacing: -0.02em;
                              }

                              .modal-header.inv-modal-header .close {
                                   color: #fff;
                                   opacity: 1;
                                   text-shadow: none;
                                   background: rgba(255, 255, 255, 0.14);
                                   border: 1px solid rgba(255, 255, 255, 0.22);
                                   width: 38px;
                                   height: 38px;
                                   border-radius: 50%;
                                   padding: 0;
                                   margin: 0;
                                   line-height: 1;
                                   transition: 0.25s ease;
                              }

                              .modal-header.inv-modal-header .close:hover {
                                   background: rgba(255, 255, 255, 0.24);
                                   transform: rotate(90deg);
                              }

                              .modal-body.inv-modal-body {
                                   background: linear-gradient(180deg, #fbfdff 0%, #f6f9fc 100%);
                                   padding: 24px;
                              }

                              .inv-section-card {
                                   background: #fff;
                                   border: 1px solid #e2e8f0;
                                   border-radius: 18px;
                                   padding: 18px;
                                   box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
                                   margin-bottom: 18px;
                              }

                              .inv-section-title {
                                   font-size: 0.98rem;
                                   font-weight: 700;
                                   color: #0f172a;
                                   margin-bottom: 14px;
                                   display: flex;
                                   align-items: center;
                                   gap: 10px;
                              }

                              .inv-section-title .badge-dot {
                                   width: 11px;
                                   height: 11px;
                                   border-radius: 50%;
                                   background: linear-gradient(135deg, var(--primary), #0ea5e9);
                                   display: inline-block;
                              }

                              .modal-body.inv-modal-body .form-group {
                                   margin-bottom: 16px;
                              }

                              .modal-body.inv-modal-body label {
                                   display: block;
                                   margin-bottom: 7px;
                                   color: #334155;
                                   font-size: 0.82rem;
                                   font-weight: 700;
                                   letter-spacing: 0.02em;
                              }

                              .modal-body.inv-modal-body .form-control,
                              .modal-body.inv-modal-body .select2-container--default .select2-selection--single {
                                   min-height: 46px;
                                   border-radius: 12px !important;
                                   border: 1px solid var(--line-strong) !important;
                                   background: #fff !important;
                                   color: var(--text) !important;
                                   box-shadow: none !important;
                                   font-size: 0.94rem;
                                   padding-left: 14px;
                                   padding-right: 14px;
                              }

                              .modal-body.inv-modal-body .form-control:focus {
                                   border-color: #93c5fd !important;
                                   box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.10) !important;
                              }

                              .modal-body.inv-modal-body .form-control[readonly] {
                                   background: #f8fafc !important;
                                   color: var(--text-soft) !important;
                              }

                              .modal-body.inv-modal-body .select2-container {
                                   width: 100% !important;
                              }

                              .modal-body.inv-modal-body .select2-container .select2-selection__rendered {
                                   line-height: 44px !important;
                                   padding-left: 0 !important;
                                   color: var(--text) !important;
                              }

                              .modal-body.inv-modal-body .select2-container .select2-selection__arrow {
                                   height: 44px !important;
                                   right: 10px !important;
                              }

                              .invoice-list-page .item-breakdown {
                                   display: block;
                                   margin-top: 6px;
                                   color: var(--text-soft);
                                   font-size: 0.78rem;
                              }

                              .invoice-list-page .item-preview {
                                   display: block;
                                   margin-top: 6px;
                                   color: var(--text-soft);
                                   font-size: 0.82rem;
                                   line-height: 1.45;
                              }

                              .invoice-list-page .item-builder {
                                   border: 1px solid var(--line);
                                   border-radius: 18px;
                                   background: #fff;
                                   padding: 18px;
                                   margin-bottom: 18px;
                                   box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
                              }

                              .invoice-list-page .item-builder-head {
                                   display: flex;
                                   justify-content: space-between;
                                   align-items: center;
                                   gap: 12px;
                                   margin-bottom: 14px;
                                   flex-wrap: wrap;
                                   padding-bottom: 12px;
                                   border-bottom: 1px solid #eef2f7;
                              }

                              .invoice-list-page .item-builder-title {
                                   font-weight: 700;
                                   color: var(--text);
                              }

                              .invoice-list-page .item-builder-subtitle {
                                   color: var(--text-soft);
                                   font-size: 0.85rem;
                              }

                              .invoice-list-page .btn-add-entry {
                                   background: linear-gradient(135deg, var(--primary), #0ea5e9);
                                   color: #fff;
                                   border: none;
                                   border-radius: 12px;
                                   padding: 11px 16px;
                                   font-weight: 600;
                                   font-size: 0.88rem;
                                   box-shadow: 0 8px 22px rgba(37, 99, 235, 0.20);
                                   transition: 0.25s ease;
                              }

                              .invoice-list-page .btn-add-entry:hover {
                                   transform: translateY(-1px);
                                   box-shadow: 0 12px 24px rgba(37, 99, 235, 0.24);
                                   color: #fff;
                              }

                              .invoice-list-page .btn-remove-entry {
                                   border: 1px solid #fecaca;
                                   border-radius: 12px;
                                   background: #fff1f2;
                                   color: #dc2626;
                                   font-size: 0.82rem;
                                   font-weight: 700;
                                   padding: 10px 14px;
                                   transition: all 0.18s ease;
                                   width: 100%;
                              }

                              .invoice-list-page .btn-remove-entry:hover {
                                   background: #dc2626;
                                   border-color: #dc2626;
                                   color: #fff;
                              }

                              .invoice-list-page .item-row {
                                   border: 1px solid #e8eef6;
                                   border-radius: 16px;
                                   background: linear-gradient(180deg, #ffffff, #fbfdff);
                                   padding: 16px;
                                   position: relative;
                              }

                              .invoice-list-page .item-row+.item-row {
                                   margin-top: 12px;
                              }

                              .invoice-list-page .item-row-head {
                                   display: flex;
                                   justify-content: space-between;
                                   align-items: center;
                                   gap: 10px;
                                   margin-bottom: 10px;
                                   flex-wrap: wrap;
                              }

                              .invoice-list-page .item-row-title {
                                   font-size: 0.78rem;
                                   font-weight: 800;
                                   letter-spacing: 0.08em;
                                   text-transform: uppercase;
                                   color: var(--text-faint);
                              }

                              .invoice-list-page .item-row-total {
                                   font-family: var(--font-mono);
                                   font-size: 0.88rem;
                                   font-weight: 700;
                                   color: var(--text);
                              }

                              .invoice-list-page .item-breakdown-inline,
                              .invoice-list-page .item-total-warning,
                              .invoice-list-page .item-extra-summary {
                                   display: block;
                                   margin-top: 6px;
                                   font-size: 0.82rem;
                              }

                              .invoice-list-page .item-breakdown-inline,
                              .invoice-list-page .item-extra-summary {
                                   color: var(--text-soft);
                              }

                              .invoice-list-page .item-total-warning {
                                   color: var(--danger);
                              }

                              .invoice-summary-box {
                                   background: linear-gradient(135deg, #eff6ff, #f0f9ff);
                                   border: 1px solid #bfdbfe;
                                   border-radius: 18px;
                                   padding: 18px;
                              }

                              .invoice-summary-label {
                                   font-size: 0.84rem;
                                   color: var(--text-soft);
                                   font-weight: 600;
                              }

                              .invoice-summary-value {
                                   font-size: 1.65rem;
                                   font-weight: 800;
                                   color: var(--primary-2);
                                   margin-top: 4px;
                                   letter-spacing: 0.3px;
                              }

                              .invoice-notes {
                                   min-height: 90px;
                                   resize: vertical;
                              }

                              .inv-helper {
                                   display: block;
                                   margin-top: 6px;
                                   font-size: 0.78rem;
                                   color: var(--text-soft);
                              }

                              .inv-modal-footer {
                                   display: flex;
                                   justify-content: space-between;
                                   align-items: center;
                                   gap: 12px;
                                   margin-top: 20px;
                                   flex-wrap: wrap;
                              }

                              .inv-footer-note {
                                   font-size: 0.82rem;
                                   color: var(--text-soft);
                              }

                              .btn-invoice-cancel {
                                   background: #fff !important;
                                   border: 1px solid #cbd5e1 !important;
                                   color: #334155 !important;
                                   border-radius: 12px !important;
                                   padding: 11px 18px !important;
                                   font-weight: 600 !important;
                              }

                              .btn-invoice-cancel:hover {
                                   background: #f8fafc !important;
                              }

                              .btn-invoice-save {
                                   background: linear-gradient(135deg, var(--primary), #0ea5e9) !important;
                                   color: #fff !important;
                                   border: none !important;
                                   border-radius: 12px !important;
                                   padding: 12px 22px !important;
                                   font-weight: 700 !important;
                                   box-shadow: 0 10px 24px rgba(37, 99, 235, 0.22) !important;
                                   transition: 0.25s ease !important;
                              }

                              .btn-invoice-save:hover {
                                   transform: translateY(-1px);
                                   box-shadow: 0 14px 28px rgba(37, 99, 235, 0.28) !important;
                              }

                              .invoice-list-page .dataTables_wrapper .dataTables_length,
                              .invoice-list-page .dataTables_wrapper .dataTables_filter {
                                   margin-bottom: 14px;
                              }

                              .invoice-list-page .dataTables_wrapper .dataTables_length label,
                              .invoice-list-page .dataTables_wrapper .dataTables_filter label {
                                   color: var(--text-soft);
                                   font-size: 0.84rem;
                                   font-weight: 600;
                              }

                              .invoice-list-page .dataTables_wrapper .dataTables_filter input,
                              .invoice-list-page .dataTables_wrapper .dataTables_length select {
                                   border: 1px solid var(--line-strong);
                                   border-radius: 10px;
                                   background: #fff;
                                   color: var(--text);
                                   min-height: 38px;
                                   padding: 6px 12px;
                              }

                              .invoice-list-page .dataTables_wrapper .dataTables_info {
                                   color: var(--text-faint);
                                   font-size: 0.82rem;
                                   padding-top: 14px;
                              }

                              .invoice-list-page .dataTables_wrapper .dataTables_paginate {
                                   padding-top: 10px;
                              }

                              .invoice-list-page .dataTables_wrapper .paginate_button {
                                   border-radius: 10px !important;
                                   border: 1px solid transparent !important;
                                   min-width: 36px;
                                   min-height: 36px;
                                   padding: 7px 10px !important;
                                   color: var(--text-soft) !important;
                                   background: transparent !important;
                              }

                              .invoice-list-page .dataTables_wrapper .paginate_button:hover {
                                   background: #eef4ff !important;
                                   color: var(--primary) !important;
                                   border-color: #dbeafe !important;
                              }

                              .invoice-list-page .dataTables_wrapper .paginate_button.current,
                              .invoice-list-page .dataTables_wrapper .paginate_button.current:hover {
                                   background: linear-gradient(135deg, var(--primary), var(--primary-2)) !important;
                                   color: #fff !important;
                                   border-color: transparent !important;
                              }

                              @media (max-width: 991px) {
                                   .invoice-list-page .stat-strip {
                                        grid-template-columns: repeat(2, minmax(0, 1fr));
                                   }

                                   .invoice-list-page .filter-form {
                                        grid-template-columns: 1fr;
                                   }
                              }

                              @media (max-width: 767px) {
                                   .invoice-list-page .inv-title {
                                        font-size: 1.65rem;
                                   }

                                   .invoice-list-page .stat-strip {
                                        grid-template-columns: 1fr;
                                   }

                                   .invoice-list-page .filter-actions {
                                        width: 100%;
                                   }

                                   .invoice-list-page .filter-btn {
                                        flex: 1 1 auto;
                                   }

                                   .invoice-list-page .inv-card-header,
                                   .invoice-list-page .inv-card-body,
                                   .modal-body.inv-modal-body {
                                        padding-left: 16px;
                                        padding-right: 16px;
                                   }

                                   .invoice-list-page #invoice-table tbody td {
                                        padding: 13px 12px !important;
                                   }

                                   .inv-modal-footer {
                                        flex-direction: column;
                                        align-items: stretch;
                                   }

                                   .inv-modal-footer .text-right {
                                        width: 100%;
                                        display: flex;
                                        gap: 10px;
                                   }

                                   .inv-modal-footer .text-right button {
                                        width: 100%;
                                   }

                                   .invoice-list-page .item-builder-head {
                                        flex-direction: column;
                                        align-items: stretch;
                                   }

                                   .invoice-list-page .btn-add-entry {
                                        width: 100%;
                                   }
                              }
                         </style>

                         <div class="page-header">
                              <div>
                                   <div class="page-eyebrow">Invoice Management</div>
                                   <h4 class="page-title">Invoices</h4>
                              </div>
                              <div class="page-actions">
                                   <a href="<?= base_url(); ?>Page/invoiceEntry" class="btn-submit">
                                        <i class="mdi mdi-plus-circle-outline"></i>
                                        Add New Invoice
                                   </a>
                              </div>
                         </div>

                         <?php
                         // Helper function to calculate covered period for recurring invoices
                         function getCoveredMonths($invoice)
                         {
                              $frequency = $invoice->recurringFrequency ?? 'none';
                              $scheduleDate = $invoice->recurringScheduleDate ?? '';

                              if ($frequency === 'none' || empty($scheduleDate)) {
                                   return '';
                              }

                              $startDate = new DateTime($scheduleDate);
                              $endDate = clone $startDate;

                              // Calculate the covered period based on frequency
                              switch ($frequency) {
                                   case 'daily':
                                        // Daily: just the single day (schedule date)
                                        $endDate = $startDate;
                                        break;

                                   case 'weekly':
                                        // Weekly: 7 days from schedule date
                                        $endDate->modify('+6 days');
                                        break;

                                   case 'monthly':
                                        // Monthly: from schedule date to schedule date + 1 month - 1 day
                                        $endDate->modify('+1 month');
                                        $endDate->modify('-1 day');
                                        break;

                                   case 'quarterly':
                                        // Quarterly: from schedule date to schedule date + 3 months - 1 day
                                        $endDate->modify('+3 months');
                                        $endDate->modify('-1 day');
                                        break;

                                   case 'yearly':
                                        // Yearly: from schedule date to schedule date + 1 year - 1 day
                                        $endDate->modify('+1 year');
                                        $endDate->modify('-1 day');
                                        break;

                                   default:
                                        return '';
                              }

                              return 'From ' . date('M d, Y', $startDate->getTimestamp()) . ' To ' . date('M d, Y', $endDate->getTimestamp());
                         }

                         // Safely calculate next invoice number, handling non-numeric formats
                         $nextInvoiceNo = 100001;
                         if (!empty($data1) && isset($data1[0]->InvoiceNo)) {
                              $lastInvoiceNo = $data1[0]->InvoiceNo;
                              // Extract numeric portion if it ends with digits
                              if (preg_match('/(\d+)$/', $lastInvoiceNo, $matches)) {
                                   $nextInvoiceNo = (int)$matches[1] + 1;
                              } else {
                                   // If no numeric ending, use timestamp-based number
                                   $nextInvoiceNo = (int)date('Ymd') . '001';
                              }
                         }

                         $totalCount   = !empty($data) ? count($data) : 0;
                         $paidCount    = 0;
                         $partialCount = 0;
                         $unpaidCount = 0;
                         if (!empty($data)) {
                              foreach ($data as $r) {
                                   $b = (float)$r->Balance;
                                   $p = (float)$r->AmountPaid;
                                   if ($b <= 0.00001)      $paidCount++;
                                   elseif ($p > 0)          $partialCount++;
                                   else                     $unpaidCount++;
                              }
                         }
                         ?>

                         <div class="stats-grid">
                              <div class="stat-card stat-total">
                                   <div class="stat-label">Total</div>
                                   <div class="stat-value"><?= $totalCount; ?></div>
                                   <div class="stat-meta">All invoices</div>
                              </div>
                              <div class="stat-card stat-paid">
                                   <div class="stat-label">Fully Paid</div>
                                   <div class="stat-value"><?= $paidCount; ?></div>
                                   <div class="stat-meta">Completed payments</div>
                              </div>
                              <div class="stat-card stat-partial">
                                   <div class="stat-label">Partial</div>
                                   <div class="stat-value"><?= $partialCount; ?></div>
                                   <div class="stat-meta">Partially paid</div>
                              </div>
                              <div class="stat-card stat-unpaid">
                                   <div class="stat-label">Unpaid</div>
                                   <div class="stat-value"><?= $unpaidCount; ?></div>
                                   <div class="stat-meta">Awaiting payment</div>
                              </div>
                         </div>

                         <div class="filter-card">
                              <div class="filter-card-head">
                                   <div>
                                        <h5 class="filter-card-title">Filter By Customer</h5>
                                        <div class="filter-card-subtitle">Search the company name quickly with Select2 and narrow the invoice list before reviewing records.</div>
                                   </div>
                                   <div class="filter-active-chip">
                                        <i class="mdi mdi-domain"></i>
                                        <?= htmlspecialchars($selectedCustomerName !== '' ? $selectedCustomerName : 'All customers', ENT_QUOTES, 'UTF-8'); ?>
                                   </div>
                              </div>

                              <form method="get" action="<?= base_url('Page/invList'); ?>" class="filter-form">
                                   <div class="filter-field">
                                        <label for="invoice-customer-filter">Customer</label>
                                        <select name="customer" id="invoice-customer-filter" class="form-control customer-filter-select">
                                             <option value="">All customers</option>
                                             <?php foreach ($clientOptions as $clientOption): ?>
                                                  <?php
                                                  $optionCustId = trim((string) ($clientOption->CustID ?? ''));
                                                  $optionCustomer = trim((string) ($clientOption->Customer ?? ''));
                                                  if ($optionCustId === '' && $optionCustomer === '') {
                                                       continue;
                                                  }
                                                  ?>
                                                  <option value="<?= htmlspecialchars($optionCustId, ENT_QUOTES, 'UTF-8'); ?>" <?= $optionCustId === $selectedCustomerId ? 'selected' : ''; ?>>
                                                       <?= htmlspecialchars($optionCustomer !== '' ? $optionCustomer : $optionCustId, ENT_QUOTES, 'UTF-8'); ?>
                                                  </option>
                                             <?php endforeach; ?>
                                        </select>
                                   </div>

                                   <div class="filter-actions">
                                        <button type="submit" class="filter-btn filter-btn-primary">
                                             <i class="mdi mdi-filter-variant"></i> Apply Filter
                                        </button>
                                   </div>

                                   <div class="filter-actions">
                                        <a href="<?= base_url('Page/invList'); ?>" class="filter-btn filter-btn-secondary">
                                             <i class="mdi mdi-refresh"></i> Clear
                                        </a>
                                   </div>
                              </form>
                         </div>

                         <div class="card-stack">
                              <div class="theme-card">

                                   <div class="theme-card-head">
                                        <h5 class="theme-card-title">Invoice List</h5>
                                   </div>

                                   <div class="theme-card-body">
                                        <div class="table-responsive">
                                             <table id="invoice-table" class="table table-hover mb-0">
                                                  <thead>
                                                       <tr>
                                                            <th>Invoice No.</th>
                                                            <th>Customer</th>
                                                            <th>Date</th>
                                                            <th>Description</th>
                                                            <th class="text-right">Total Due</th>
                                                            <th class="text-right">Amount Paid</th>
                                                            <th class="text-right">Balance</th>
                                                            <th class="text-center">Actions</th>
                                                       </tr>
                                                  </thead>
                                                  <tbody>
                                                       <?php if (!empty($data)): ?>
                                                            <?php foreach ($data as $row): ?>
                                                                 <?php
                                                                 $balance = (float) $row->Balance;
                                                                 $amountPaid = (float) $row->AmountPaid;
                                                                 $isFullyPaid = $balance <= 0.00001;
                                                                 $hasPayment = $amountPaid > 0;
                                                                 $paymentHistoryHref = base_url() . 'Page/paymentHistory?id=' . rawurlencode((string) $row->orderID);
                                                                 $paymentStateClass = 'payment-state--unpaid';
                                                                 $paymentStateLabel = 'Unpaid';
                                                                 $invoiceItems = isset($row->invoiceItems) && is_array($row->invoiceItems) ? $row->invoiceItems : array();
                                                                 $primaryItem = !empty($invoiceItems) ? $invoiceItems[0] : array(
                                                                      'itemDescription' => (string) ($row->JobDescription ?? ''),
                                                                      'itemQuantity' => (isset($row->itemQuantity) && is_numeric($row->itemQuantity) && (int) $row->itemQuantity > 0) ? (int) $row->itemQuantity : 1,
                                                                      'itemDurationUnit' => (string) ($row->itemDurationUnit ?? 'each'),
                                                                      'itemUnitPrice' => (isset($row->itemUnitPrice) && is_numeric($row->itemUnitPrice)) ? (float) $row->itemUnitPrice : ((float) $row->TotalDue),
                                                                      'lineTotal' => (float) $row->TotalDue,
                                                                 );
                                                                 $itemQuantity = (int) ($primaryItem['itemQuantity'] ?? 1);
                                                                 $itemDurationUnit = trim((string) ($primaryItem['itemDurationUnit'] ?? ''));
                                                                 $itemUnitPrice = (float) ($primaryItem['itemUnitPrice'] ?? 0);
                                                                 $showItemBreakdown = !empty($primaryItem);
                                                                 $unitLabel = $itemDurationUnit;
                                                                 if ($unitLabel !== '' && $unitLabel !== 'each' && $itemQuantity !== 1 && !preg_match('/s$/i', $unitLabel)) {
                                                                      $unitLabel .= 's';
                                                                 }
                                                                 $rateUnitLabel = $itemDurationUnit !== '' ? $itemDurationUnit : 'each';
                                                                 if ($unitLabel === '' || $unitLabel === 'each') {
                                                                      $itemBreakdown = $itemQuantity . ' x PHP ' . number_format($itemUnitPrice, 2) . ' / ' . $rateUnitLabel;
                                                                 } else {
                                                                      $itemBreakdown = $itemQuantity . ' ' . $unitLabel . ' x PHP ' . number_format($itemUnitPrice, 2) . ' / ' . $rateUnitLabel;
                                                                 }

                                                                 $descriptionLabel = trim((string) ($primaryItem['itemDescription'] ?? $row->JobDescription ?? ''));
                                                                 if ($descriptionLabel === '') {
                                                                      $summaryText = trim((string) ($row->invoiceSummary ?? 'Invoice item'));
                                                                      // Take only the first line if summary contains multiple lines
                                                                      $descriptionLabel = explode("\n", $summaryText)[0];
                                                                 }
                                                                 $extraItemCount = max(count($invoiceItems) - 1, 0);
                                                                 $itemJsonPayload = json_encode($invoiceItems, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
                                                                 $itemJson = htmlspecialchars($itemJsonPayload !== false ? $itemJsonPayload : '[]', ENT_QUOTES, 'UTF-8');

                                                                 if ($isFullyPaid) {
                                                                      $paymentStateClass = 'payment-state--paid';
                                                                      $paymentStateLabel = 'Fully Paid';
                                                                 } elseif ($amountPaid > 0) {
                                                                      $paymentStateClass = 'payment-state--partial';
                                                                      $paymentStateLabel = 'Partially Paid';
                                                                 }
                                                                 ?>
                                                                 <tr>
                                                                      <td>
                                                                           <a class="inv-no-link" href="<?= base_url(); ?>Page/invoice?id=<?= $row->orderID; ?>">
                                                                                #<?= $row->InvoiceNo; ?>
                                                                           </a>
                                                                      </td>
                                                                      <td><?= $row->Customer; ?></td>
                                                                      <td style="font-family:var(--font-mono);font-size:0.8rem;color:var(--text-soft);"><?= $row->TransDate; ?></td>
                                                                      <td style="max-width:260px;">
                                                                           <div style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= htmlspecialchars($descriptionLabel, ENT_QUOTES, 'UTF-8'); ?></div>
                                                                           <?php if ($showItemBreakdown): ?>
                                                                                <small class="item-breakdown"><?= htmlspecialchars($itemBreakdown, ENT_QUOTES, 'UTF-8'); ?></small>
                                                                           <?php endif; ?>
                                                                           <?php if ($extraItemCount > 0): ?>
                                                                                <small class="item-extra-summary">+<?= $extraItemCount; ?> more entr<?= $extraItemCount === 1 ? 'y' : 'ies'; ?></small>
                                                                           <?php endif; ?>
                                                                           <?php if (($row->recurringFrequency ?? 'none') !== 'none'): ?>
                                                                                <small class="text-muted">
                                                                                     <?= ucfirst((string) $row->recurringFrequency); ?> recurring
                                                                                     <?php if (!empty($row->recurringScheduleDate)): ?>
                                                                                          · Schedule <?= date('M d, Y', strtotime($row->recurringScheduleDate)); ?>
                                                                                     <?php endif; ?>
                                                                                     <?php
                                                                                     $coveredMonths = getCoveredMonths($row);
                                                                                     if (!empty($coveredMonths)): ?>
                                                                                          · Covers <?= htmlspecialchars($coveredMonths, ENT_QUOTES, 'UTF-8'); ?>
                                                                                     <?php endif; ?>
                                                                                </small>
                                                                           <?php endif; ?>
                                                                      </td>
                                                                      <td class="text-right num-cell"><?= number_format($row->TotalDue, 2); ?></td>
                                                                      <td class="text-right">
                                                                           <?php if ($hasPayment): ?>
                                                                                <a class="action-link num-cell" href="<?= htmlspecialchars($paymentHistoryHref, ENT_QUOTES, 'UTF-8'); ?>">
                                                                                     <?= number_format($row->AmountPaid, 2); ?>
                                                                                </a>
                                                                           <?php else: ?>
                                                                                <span class="num-cell"><?= number_format($row->AmountPaid, 2); ?></span>
                                                                           <?php endif; ?>
                                                                      </td>
                                                                      <td class="text-right">
                                                                           <div style="display:flex;flex-direction:column;align-items:flex-end;gap:6px;">
                                                                                <span class="num-cell"><?= number_format(max($balance, 0), 2); ?></span>
                                                                                <span class="payment-state <?= $paymentStateClass; ?>"><?= $paymentStateLabel; ?></span>
                                                                           </div>
                                                                      </td>
                                                                      <td class="text-center">
                                                                           <div class="dropdown">
                                                                                <button class="tbl-btn tbl-btn-print dropdown-toggle" type="button" id="dropdownMenu<?= $row->orderID; ?>" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                                                     Actions
                                                                                </button>

                                                                                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenu<?= $row->orderID; ?>">
                                                                                     <a class="dropdown-item" href="<?= base_url(); ?>Page/invoice?id=<?= $row->orderID; ?>&print=1" target="_blank" rel="noopener">
                                                                                          <i class="fa fa-print"></i> Print Invoice
                                                                                     </a>

                                                                                     <a class="dropdown-item"
                                                                                          href="javascript:void(0);"
                                                                                          data-toggle="modal"
                                                                                          data-target="#emailInvoiceModal"
                                                                                          data-orderid="<?= $row->orderID; ?>"
                                                                                          data-invoiceno="<?= $row->InvoiceNo; ?>"
                                                                                          data-client="<?= htmlspecialchars($row->Customer, ENT_QUOTES, 'UTF-8'); ?>"
                                                                                          data-email="<?= htmlspecialchars($row->client_email ?? $row->customer_email ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                                                                          onclick="prepareEmailModal(this)">
                                                                                          <i class="fa fa-envelope"></i> Send via Email
                                                                                     </a>

                                                                                     <?php if ($hasPayment): ?>
                                                                                          <a class="dropdown-item" href="<?= htmlspecialchars($paymentHistoryHref, ENT_QUOTES, 'UTF-8'); ?>">
                                                                                               <i class="fa fa-credit-card"></i> View Payment Details
                                                                                          </a>
                                                                                     <?php endif; ?>

                                                                                     <?php if (!$isFullyPaid): ?>
                                                                                          <a class="dropdown-item" href="<?= base_url(); ?>Page/addPaymentJO?id=<?= $row->orderID; ?>&InvoiceNo=<?= $row->InvoiceNo; ?>&PaymentSource=Others">
                                                                                               <i class="fa fa-plus"></i> Add Payment
                                                                                          </a>
                                                                                     <?php endif; ?>

                                                                                     <?php if (in_array($this->session->userdata('level'), ['Admin', 'Staff', 'Encoder'], true)): ?>
                                                                                          <div class="dropdown-divider"></div>

                                                                                          <a class="dropdown-item"
                                                                                               href="<?= base_url(); ?>Page/invoiceEntry?id=<?= (int) $row->orderID; ?>">
                                                                                               <i class="fa fa-edit"></i> Edit Record
                                                                                          </a>

                                                                                          <a class="dropdown-item"
                                                                                               href="<?= base_url(); ?>Page/duplicateInvoice?id=<?= (int) $row->orderID; ?>"
                                                                                               onclick="return confirm('Create a duplicate copy of this invoice?');">
                                                                                               <i class="fa fa-copy"></i> Duplicate Invoice
                                                                                          </a>

                                                                                          <a class="dropdown-item text-warning"
                                                                                               href="javascript:void(0);"
                                                                                               data-toggle="modal"
                                                                                               data-target="#voidInvoiceModal"
                                                                                               data-orderid="<?= $row->orderID; ?>"
                                                                                               data-invoiceno="<?= $row->InvoiceNo; ?>"
                                                                                               onclick="prepareVoidModal(this)">
                                                                                               <i class="fa fa-ban"></i> Void Invoice
                                                                                          </a>

                                                                                          <a class="dropdown-item text-danger"
                                                                                               href="<?= base_url(); ?>Page/deleteJO?id=<?= $row->orderID; ?>&return_to=invList"
                                                                                               onclick="return confirm('Are you sure you want to delete this record?');">
                                                                                               <i class="fa fa-trash"></i> Delete
                                                                                          </a>
                                                                                     <?php endif; ?>
                                                                                </div>
                                                                           </div>
                                                                      </td>
                                                                 </tr>
                                                            <?php endforeach; ?>
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

     </div>

     <?php include('includes/themecustomizer.php'); ?>

     <div class="modal fade" id="invoiceModal" tabindex="-1" role="dialog" aria-hidden="true">
          <div class="modal-dialog modal-lg" role="document">
               <div class="modal-content">
                    <div class="modal-header inv-modal-header">
                         <h5 class="modal-title">Create New Invoice</h5>
                         <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                              <span aria-hidden="true">&times;</span>
                         </button>
                    </div>

                    <div class="modal-body inv-modal-body">
                         <form class="needs-validation" method="post" action="<?= base_url(); ?>Page/addInvoice" novalidate id="invoiceForm" data-balance-form data-item-form>

                              <div class="inv-section-card">
                                   <div class="inv-section-title">
                                        <span class="badge-dot"></span>
                                        Customer Information
                                   </div>

                                   <div class="form-row">
                                        <div class="form-group col-md-3">
                                             <label for="invoice-number">Invoice No.</label>
                                             <input type="text" class="form-control" id="invoice-number" name="InvoiceNo" value="<?= $nextInvoiceNo; ?>" readonly required>
                                        </div>
                                        <div class="form-group col-md-9">
                                             <label for="invoice-customer">Customer</label>
                                             <select class="form-control" id="invoice-customer" name="CustID" required>
                                                  <option value="" data-address=""></option>
                                                  <?php if (!empty($data2)): ?>
                                                       <?php foreach ($data2 as $row): ?>
                                                            <option
                                                                 value="<?= htmlspecialchars($row->CustID, ENT_QUOTES, 'UTF-8'); ?>"
                                                                 data-address="<?= htmlspecialchars($row->Address ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                                                 data-name="<?= htmlspecialchars($row->Customer, ENT_QUOTES, 'UTF-8'); ?>">
                                                                 <?= htmlspecialchars($row->Customer, ENT_QUOTES, 'UTF-8'); ?> · <?= htmlspecialchars($row->CustID, ENT_QUOTES, 'UTF-8'); ?>
                                                            </option>
                                                       <?php endforeach; ?>
                                                  <?php endif; ?>
                                             </select>
                                        </div>
                                   </div>

                                   <div class="form-group mb-0">
                                        <label for="invoice-customer-address">Customer Address</label>
                                        <input type="text" class="form-control" id="invoice-customer-address" name="CustAddress" placeholder="Customer address will populate automatically" readonly>
                                        <small class="inv-helper">This field is automatically filled once a customer is selected.</small>
                                   </div>
                              </div>

                              <div class="item-builder" data-item-builder>
                                   <div class="item-builder-head">
                                        <div>
                                             <div class="item-builder-title">Invoice Entries</div>
                                             <div class="item-builder-subtitle">Add one or more billable entries. The total invoice amount updates automatically.</div>
                                        </div>
                                        <button type="button" class="btn-add-entry" data-add-item-row>+ Add Entry</button>
                                   </div>

                                   <div data-item-rows></div>
                                   <small class="item-total-warning" data-total-warning></small>
                              </div>

                              <div class="inv-section-card">
                                   <div class="inv-section-title">
                                        <span class="badge-dot"></span>
                                        Billing Schedule
                                   </div>

                                   <div class="form-row">
                                        <div class="form-group col-md-6">
                                             <label for="invoice-recurring-frequency">Recurring</label>
                                             <select class="form-control" id="invoice-recurring-frequency" name="recurringFrequency">
                                                  <option value="none" selected>No</option>
                                                  <option value="daily">Daily</option>
                                                  <option value="weekly">Weekly</option>
                                                  <option value="monthly">Monthly</option>
                                                  <option value="quarterly">Quarterly</option>
                                                  <option value="yearly">Yearly</option>
                                             </select>
                                        </div>
                                        <div class="form-group col-md-6">
                                             <label for="invoice-recurring-schedule-date">Schedule Date</label>
                                             <input type="date" class="form-control" id="invoice-recurring-schedule-date" name="recurringScheduleDate">
                                        </div>
                                   </div>
                              </div>

                              <div class="inv-section-card">
                                   <div class="inv-section-title">
                                        <span class="badge-dot"></span>
                                        Invoice Summary
                                   </div>

                                   <div class="form-row align-items-end">
                                        <div class="form-group col-md-7">
                                             <label for="invoice-total-due">Invoice Amount</label>
                                             <input type="number" class="form-control" id="invoice-total-due" name="TotalDue" min="0" step="0.01" required readonly>
                                             <small class="inv-helper">This is the sum of all invoice entries.</small>
                                        </div>
                                        <div class="form-group col-md-5">
                                             <div class="invoice-summary-box">
                                                  <div class="invoice-summary-label">Total Due</div>
                                                  <div class="invoice-summary-value" id="invoice-total-preview">₱0.00</div>
                                             </div>
                                        </div>
                                   </div>

                                   <input type="hidden" name="AmountPaid" id="invoice-amount-paid" value="0.00">
                                   <input type="hidden" name="Balance" id="invoice-balance" value="0.00">
                                   <input type="hidden" name="PaymentReference" id="invoice-payment-reference" value="">

                                   <div class="form-group mb-0">
                                        <label for="invoice-notes">Notes</label>
                                        <textarea class="form-control invoice-notes" id="invoice-notes" name="Notes" placeholder="Write additional billing notes here..."></textarea>
                                   </div>
                              </div>

                              <div class="inv-modal-footer">
                                   <div class="inv-footer-note">
                                        Review the invoice details before saving.
                                   </div>

                                   <div class="text-right">
                                        <button type="button" class="btn btn-invoice-cancel" data-dismiss="modal">Cancel</button>
                                        <button type="submit" name="submit" class="btn btn-invoice-save ml-2">Save Invoice</button>
                                   </div>
                              </div>
                         </form>
                    </div>
               </div>
          </div>
     </div>

     <div class="modal fade" id="invoiceEditModal" tabindex="-1" role="dialog" aria-hidden="true">
          <div class="modal-dialog modal-lg" role="document">
               <div class="modal-content">
                    <div class="modal-header inv-modal-header">
                         <h5 class="modal-title">Update Invoice</h5>
                         <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                              <span aria-hidden="true">&times;</span>
                         </button>
                    </div>

                    <div class="modal-body inv-modal-body">
                         <form class="needs-validation" method="post" action="<?= base_url(); ?>Page/updateJO" novalidate data-balance-form data-edit-form data-item-form>
                              <input type="hidden" name="id" value="">
                              <input type="hidden" name="return_to" value="invList">

                              <div class="inv-section-card">
                                   <div class="inv-section-title">
                                        <span class="badge-dot"></span>
                                        Customer Information
                                   </div>

                                   <div class="form-row">
                                        <div class="form-group col-md-3">
                                             <label for="invoice-edit-number">Invoice No.</label>
                                             <input type="text" class="form-control" id="invoice-edit-number" name="InvoiceNo" value="" readonly required>
                                        </div>
                                        <div class="form-group col-md-9">
                                             <label for="invoice-edit-customer">Customer</label>
                                             <select class="form-control" id="invoice-edit-customer" name="CustID" required>
                                                  <option value="" data-address=""></option>
                                                  <?php if (!empty($data2)): ?>
                                                       <?php foreach ($data2 as $row): ?>
                                                            <option
                                                                 value="<?= htmlspecialchars($row->CustID, ENT_QUOTES, 'UTF-8'); ?>"
                                                                 data-address="<?= htmlspecialchars($row->Address ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                                                 data-name="<?= htmlspecialchars($row->Customer, ENT_QUOTES, 'UTF-8'); ?>">
                                                                 <?= htmlspecialchars($row->Customer, ENT_QUOTES, 'UTF-8'); ?> · <?= htmlspecialchars($row->CustID, ENT_QUOTES, 'UTF-8'); ?>
                                                            </option>
                                                       <?php endforeach; ?>
                                                  <?php endif; ?>
                                             </select>
                                        </div>
                                   </div>

                                   <div class="form-group mb-0">
                                        <label for="invoice-edit-customer-address">Customer Address</label>
                                        <input type="text" class="form-control" id="invoice-edit-customer-address" name="CustAddress" value="" readonly>
                                        <small class="inv-helper">This field updates automatically based on the selected customer.</small>
                                   </div>
                              </div>

                              <div class="item-builder" data-item-builder>
                                   <div class="item-builder-head">
                                        <div>
                                             <div class="item-builder-title">Invoice Entries</div>
                                             <div class="item-builder-subtitle">Update the encoded line items below. The invoice total recalculates automatically.</div>
                                        </div>
                                        <button type="button" class="btn-add-entry" data-add-item-row>+ Add Entry</button>
                                   </div>
                                   <div data-item-rows></div>
                                   <small class="item-total-warning" data-total-warning></small>
                              </div>

                              <div class="inv-section-card">
                                   <div class="inv-section-title">
                                        <span class="badge-dot"></span>
                                        Billing Schedule
                                   </div>

                                   <div class="form-row">
                                        <div class="form-group col-md-6">
                                             <label for="invoice-edit-recurring-frequency">Recurring</label>
                                             <select class="form-control" id="invoice-edit-recurring-frequency" name="recurringFrequency">
                                                  <option value="none">No</option>
                                                  <option value="daily">Daily</option>
                                                  <option value="weekly">Weekly</option>
                                                  <option value="monthly">Monthly</option>
                                                  <option value="quarterly">Quarterly</option>
                                                  <option value="yearly">Yearly</option>
                                             </select>
                                        </div>
                                        <div class="form-group col-md-6">
                                             <label for="invoice-edit-recurring-schedule-date">Schedule Date</label>
                                             <input type="date" class="form-control" id="invoice-edit-recurring-schedule-date" name="recurringScheduleDate" value="">
                                             <small class="inv-helper" id="invoice-edit-recurring-help">
                                                  Recurring invoices generate 10 days before the schedule date.
                                             </small>
                                        </div>
                                   </div>
                              </div>

                              <div class="inv-section-card">
                                   <div class="inv-section-title">
                                        <span class="badge-dot"></span>
                                        Invoice Summary
                                   </div>

                                   <div class="form-row align-items-end">
                                        <div class="form-group col-md-4">
                                             <label for="invoice-edit-total-due">Total Due</label>
                                             <input type="number" class="form-control" id="invoice-edit-total-due" name="TotalDue" min="0" step="0.01" value="" required readonly>
                                             <small class="inv-helper">This is the sum of all invoice entries and cannot be lower than the amount already paid.</small>
                                        </div>
                                        <div class="form-group col-md-4">
                                             <label for="invoice-edit-amount-paid">Amount Paid</label>
                                             <input type="number" class="form-control" id="invoice-edit-amount-paid" name="AmountPaid" value="" step="0.01" readonly required>
                                        </div>
                                        <div class="form-group col-md-4">
                                             <label for="invoice-edit-balance">Balance</label>
                                             <input type="text" class="form-control" id="invoice-edit-balance" name="Balance" value="" readonly required>
                                        </div>
                                   </div>

                                   <div class="form-row">
                                        <div class="form-group col-md-7">
                                             <label for="invoice-edit-notes">Notes</label>
                                             <textarea class="form-control invoice-notes" id="invoice-edit-notes" name="Notes" placeholder="Write additional billing notes here..."></textarea>
                                        </div>
                                        <div class="form-group col-md-5">
                                             <div class="invoice-summary-box">
                                                  <div class="invoice-summary-label">Updated Total Due</div>
                                                  <div class="invoice-summary-value" id="invoice-edit-total-preview">₱0.00</div>
                                             </div>
                                        </div>
                                   </div>
                              </div>

                              <div class="inv-modal-footer">
                                   <div class="inv-footer-note">
                                        Make sure all invoice entries and billing details are correct before updating.
                                   </div>

                                   <div class="text-right">
                                        <button type="button" class="btn btn-invoice-cancel" data-dismiss="modal">Close</button>
                                        <button type="submit" name="submit" class="btn btn-invoice-save ml-2">Update Invoice</button>
                                   </div>
                              </div>
                         </form>
                    </div>
               </div>
          </div>
     </div>

     <div class="modal fade" id="addpayment" tabindex="-1" role="dialog" aria-hidden="true">
          <div class="modal-dialog modal-lg" role="document">
               <div class="modal-content">
                    <div class="modal-header inv-modal-header">
                         <h5 class="modal-title">New Payment</h5>
                         <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                              <span aria-hidden="true">&times;</span>
                         </button>
                    </div>
                    <div class="modal-body inv-modal-body">
                         <form class="needs-validation" method="post" action="<?= base_url(); ?>Page/addJO" novalidate data-balance-form>
                              <input type="hidden" name="dataid" id="dataid" value="">
                              <div class="form-row">
                                   <div class="form-group col-md-3">
                                        <label for="payment-invoice-number">Invoice No.</label>
                                        <input type="text" class="form-control" id="payment-invoice-number" name="InvoiceNo" value="<?= $nextInvoiceNo; ?>" readonly required>
                                   </div>
                                   <div class="form-group col-md-9">
                                        <label for="payment-customer">Customer</label>
                                        <input type="text" class="form-control" id="payment-customer" name="Customer" required>
                                   </div>
                              </div>

                              <div class="form-group">
                                   <label for="payment-address">Customer Address</label>
                                   <input type="text" class="form-control" id="payment-address" name="CustAddress">
                              </div>

                              <div class="form-row">
                                   <div class="form-group col-md-6">
                                        <label for="payment-description">Job Description</label>
                                        <input type="text" class="form-control" id="payment-description" name="JobDescription" required>
                                   </div>
                                   <div class="form-group col-md-6">
                                        <label for="payment-notes">Notes</label>
                                        <input type="text" class="form-control" id="payment-notes" name="Notes">
                                   </div>
                              </div>

                              <div class="form-row">
                                   <div class="form-group col-md-4">
                                        <label>Total Due</label>
                                        <input type="number" class="form-control" name="TotalDue" min="0" step="0.01" required>
                                   </div>
                                   <div class="form-group col-md-4">
                                        <label>Amount Paid</label>
                                        <input type="number" class="form-control" name="AmountPaid" min="0" step="0.01" required>
                                   </div>
                                   <div class="form-group col-md-4">
                                        <label>Balance</label>
                                        <input type="text" class="form-control" name="Balance" readonly required>
                                   </div>
                              </div>

                              <div class="text-right">
                                   <button type="submit" name="submit" class="btn btn-invoice-save">Save Job Order</button>
                                   <button type="reset" class="btn btn-invoice-cancel ml-2">Reset</button>
                              </div>
                         </form>
                    </div>
               </div>
          </div>
     </div>

     <!-- Void Invoice Modal -->
     <div class="modal fade" id="voidInvoiceModal" tabindex="-1" role="dialog" aria-hidden="true">
          <div class="modal-dialog" role="document">
               <div class="modal-content" style="border-radius: 22px; overflow: hidden; box-shadow: 0 28px 60px rgba(15, 23, 42, 0.18);">
                    <div class="modal-header" style="background: linear-gradient(135deg, #dc2626, #ef4444); border: none; padding: 22px 24px;">
                         <h5 class="modal-title" style="color: #fff; font-size: 1.08rem; font-weight: 800; letter-spacing: -0.02em;">
                              <i class="fa fa-ban mr-2"></i>Void Invoice
                         </h5>
                         <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: #fff; opacity: 1; text-shadow: none; background: rgba(255,255,255,0.14); border: 1px solid rgba(255,255,255,0.22); width: 38px; height: 38px; border-radius: 50%; padding: 0; margin: 0; line-height: 1;">
                              <span aria-hidden="true">&times;</span>
                         </button>
                    </div>
                    <div class="modal-body" style="background: linear-gradient(180deg, #fbfdff 0%, #f6f9fc 100%); padding: 24px;">
                         <form id="voidInvoiceForm" method="post" action="<?= base_url(); ?>Page/voidInvoice">
                              <input type="hidden" name="orderID" id="voidOrderID" value="">
                              <input type="hidden" name="return_to" value="invList">

                              <div class="alert alert-warning" style="border: none; border-radius: 14px; background: #fffbeb; color: #92400e; font-size: 0.9rem;">
                                   <i class="fa fa-exclamation-triangle mr-2"></i>
                                   <strong>Warning:</strong> Voiding an invoice will permanently cancel it and set the balance to zero. This action cannot be undone.
                              </div>

                              <div class="form-group" style="margin-top: 20px;">
                                   <label style="display: block; margin-bottom: 8px; color: #334155; font-size: 0.85rem; font-weight: 700;">
                                        Invoice Number
                                   </label>
                                   <input type="text" id="voidInvoiceNo" class="form-control" readonly style="background: #f8fafc; color: #64748b; border: 1px solid #e2e8f0; border-radius: 12px; padding: 12px 14px; font-family: monospace;">
                              </div>

                              <div class="form-group" style="margin-top: 16px;">
                                   <label style="display: block; margin-bottom: 8px; color: #334155; font-size: 0.85rem; font-weight: 700;">
                                        Reason for Voiding <span style="color: #dc2626;">*</span>
                                   </label>
                                   <textarea name="voidReason" id="voidReason" class="form-control" rows="3" required placeholder="Enter reason for voiding this invoice..." style="border: 1px solid #e2e8f0; border-radius: 12px; padding: 12px 14px; resize: vertical;"></textarea>
                              </div>

                              <div class="text-right" style="margin-top: 24px; padding-top: 20px; border-top: 1px solid #e2e8f0;">
                                   <button type="button" class="btn" data-dismiss="modal" style="background: #fff; color: #334155; border: 1px solid #e2e8f0; border-radius: 10px; padding: 10px 20px; font-weight: 600; margin-right: 10px;">
                                        Cancel
                                   </button>
                                   <button type="submit" class="btn" style="background: linear-gradient(135deg, #dc2626, #ef4444); color: #fff; border: none; border-radius: 10px; padding: 10px 24px; font-weight: 700; box-shadow: 0 4px 14px rgba(220, 38, 38, 0.3);">
                                        <i class="fa fa-ban mr-1"></i>Void Invoice
                                   </button>
                              </div>
                         </form>
                    </div>
               </div>
          </div>
     </div>

     <!-- Email Invoice Modal -->
     <div class="modal fade" id="emailInvoiceModal" tabindex="-1" role="dialog" aria-hidden="true">
          <div class="modal-dialog" role="document">
               <div class="modal-content" style="border-radius: 22px; overflow: hidden; box-shadow: 0 28px 60px rgba(15, 23, 42, 0.18);">
                    <div class="modal-header" style="background: linear-gradient(135deg, #2563eb, #3b82f6); border: none; padding: 22px 24px;">
                         <h5 class="modal-title" style="color: #fff; font-size: 1.08rem; font-weight: 800; letter-spacing: -0.02em;">
                              <i class="fa fa-envelope mr-2"></i>Send Invoice via Email
                         </h5>
                         <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: #fff; opacity: 1; text-shadow: none; background: rgba(255,255,255,0.14); border: 1px solid rgba(255,255,255,0.22); width: 38px; height: 38px; border-radius: 50%; padding: 0; margin: 0; line-height: 1;">
                              <span aria-hidden="true">&times;</span>
                         </button>
                    </div>
                    <div class="modal-body" style="background: linear-gradient(180deg, #fbfdff 0%, #f6f9fc 100%); padding: 24px;">
                         <form id="emailInvoiceForm" method="post" action="<?= base_url(); ?>Page/emailInvoicePDF">
                              <input type="hidden" name="orderID" id="emailOrderID" value="">

                              <div class="form-group">
                                   <label style="display: block; margin-bottom: 8px; color: #334155; font-size: 0.85rem; font-weight: 700;">
                                        Invoice Number
                                   </label>
                                   <input type="text" id="emailInvoiceNo" class="form-control" readonly style="background: #f8fafc; color: #64748b; border: 1px solid #e2e8f0; border-radius: 12px; padding: 12px 14px; font-family: monospace;">
                              </div>

                              <div class="form-group" style="margin-top: 16px;">
                                   <label style="display: block; margin-bottom: 8px; color: #334155; font-size: 0.85rem; font-weight: 700;">
                                        Client Name
                                   </label>
                                   <input type="text" id="emailClientName" class="form-control" readonly style="background: #f8fafc; color: #64748b; border: 1px solid #e2e8f0; border-radius: 12px; padding: 12px 14px;">
                              </div>

                              <div class="form-group" style="margin-top: 16px;">
                                   <label style="display: block; margin-bottom: 8px; color: #334155; font-size: 0.85rem; font-weight: 700;">
                                        Recipient Email <span style="color: #dc2626;">*</span>
                                   </label>
                                   <input type="email" name="recipientEmail" id="recipientEmail" class="form-control" required placeholder="Enter email address..." style="border: 1px solid #e2e8f0; border-radius: 12px; padding: 12px 14px;">
                                   <small class="form-text text-muted" style="margin-top: 6px;">The invoice will be sent in the email.</small>
                              </div>

                              <div class="form-group" style="margin-top: 16px;">
                                   <label style="display: block; margin-bottom: 8px; color: #334155; font-size: 0.85rem; font-weight: 700;">
                                        Additional Message (Optional)
                                   </label>
                                   <textarea name="emailMessage" id="emailMessage" class="form-control" rows="3" placeholder="Enter a custom message to include in the email..." style="border: 1px solid #e2e8f0; border-radius: 12px; padding: 12px 14px; resize: vertical;"></textarea>
                              </div>

                              <div class="text-right" style="margin-top: 24px; padding-top: 20px; border-top: 1px solid #e2e8f0;">
                                   <button type="button" class="btn" data-dismiss="modal" style="background: #fff; color: #334155; border: 1px solid #e2e8f0; border-radius: 10px; padding: 10px 20px; font-weight: 600; margin-right: 10px;">
                                        Cancel
                                   </button>
                                   <button type="submit" class="btn" style="background: linear-gradient(135deg, #2563eb, #3b82f6); color: #fff; border: none; border-radius: 10px; padding: 10px 24px; font-weight: 700; box-shadow: 0 4px 14px rgba(37, 99, 235, 0.3);">
                                        <i class="fa fa-paper-plane mr-1"></i>Send Email
                                   </button>
                              </div>
                         </form>
                    </div>
               </div>
          </div>
     </div>

     <script src="<?= base_url(); ?>assets/js/vendor.min.js"></script>
     <script src="<?= base_url(); ?>assets/libs/select2/select2.min.js"></script>
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
     <script src="<?= base_url(); ?>assets/js/pages/datatables.init.js"></script>

     <script>
          (function($) {
               'use strict';

               function attachBalanceCalculator(form) {
                    var $form = $(form);
                    var $total = $form.find('input[name="TotalDue"]');
                    var $paid = $form.find('input[name="AmountPaid"]');
                    var $balance = $form.find('input[name="Balance"]');

                    function computeBalance() {
                         var total = parseFloat($total.val()) || 0;
                         var paid = parseFloat($paid.val()) || 0;
                         var balance = total - paid;
                         $balance.val(balance.toFixed(2));
                    }

                    $total.off('input.invoiceBalance').on('input.invoiceBalance', computeBalance);
                    $paid.off('input.invoiceBalance').on('input.invoiceBalance', computeBalance);
                    computeBalance();
               }

               function attachCustomerAddressSync(form) {
                    var $form = $(form);
                    var $customer = $form.find('select[name="CustID"]');
                    var $address = $form.find('input[name="CustAddress"]');

                    if (!$customer.length || !$address.length) {
                         return;
                    }

                    function syncAddress() {
                         var selectedValue = $customer.val() || '';
                         var selectedAddress = $customer.find('option:selected').data('address') || '';
                         if (selectedValue || !$address.val()) {
                              $address.val(selectedAddress);
                         }
                    }

                    $customer.off('change.invoiceAddress').on('change.invoiceAddress', syncAddress);
                    $form.off('reset.invoiceAddress').on('reset.invoiceAddress', function() {
                         window.setTimeout(function() {
                              $customer.trigger('change');
                         }, 0);
                    });
                    syncAddress();
               }

               function initializeInvoiceCustomerSelect() {
                    initializeCustomerSelect('#invoice-customer', '#invoiceModal');
               }

               function initializeEditCustomerSelect() {
                    initializeCustomerSelect('#invoice-edit-customer', '#invoiceEditModal');
               }

               function initializeInvoiceCustomerFilter() {
                    var $filter = $('#invoice-customer-filter');

                    if (!$filter.length || !$.fn || typeof $.fn.select2 !== 'function') {
                         return false;
                    }

                    if ($filter.hasClass('select2-hidden-accessible')) {
                         $filter.select2('destroy');
                    }

                    $filter.select2({
                         width: '100%',
                         placeholder: 'All customers',
                         allowClear: true,
                         dropdownParent: $filter.closest('.filter-field')
                    });

                    $filter.off('change.autoFilter').on('change.autoFilter', function() {
                         $(this).closest('form').trigger('submit');
                    });

                    return true;
                }

               function initializeCustomerSelect(selector, modalSelector) {
                    var $customer = $(selector);

                    if (!$customer.length || !$.fn || typeof $.fn.select2 !== 'function') {
                         return false;
                    }

                    if ($customer.hasClass('select2-hidden-accessible')) {
                         $customer.select2('destroy');
                    }

                    $customer.select2({
                         width: '100%',
                         dropdownParent: $(modalSelector),
                         placeholder: 'Select customer'
                    });

                    return true;
               }

               function initializeInvoiceSelect2(attempt) {
                    attempt = attempt || 0;

                    if (!window.jQuery || !$.fn || typeof $.fn.select2 !== 'function') {
                         if (attempt < 20) {
                              window.setTimeout(function() {
                                   initializeInvoiceSelect2(attempt + 1);
                              }, 100);
                         }
                         return;
                    }

                    initializeInvoiceCustomerSelect();
                    initializeEditCustomerSelect();
                    initializeInvoiceCustomerFilter();
                }

               function normalizeAmount(value) {
                    var parsed = parseFloat(value);
                    return isFinite(parsed) ? parsed.toFixed(2) : '0.00';
               }

               function escapeHtml(value) {
                    return $('<div>').text(value == null ? '' : String(value)).html();
               }

               function defaultInvoiceItem() {
                    return {
                         itemDescription: '',
                         itemQuantity: 1,
                         itemDurationUnit: 'each',
                         itemUnitPrice: '0.00'
                    };
               }

               function currency(value) {
                    var parsed = parseFloat(value) || 0;
                    return new Intl.NumberFormat('en-PH', {
                         style: 'currency',
                         currency: 'PHP'
                    }).format(parsed);
               }

               function formatQuantity(value) {
                    var parsed = parseFloat(value) || 0;
                    if (Math.round(parsed) === parsed) {
                         return String(Math.round(parsed));
                    }

                    return parsed.toFixed(2);
               }

               function formatDurationLabel(quantity, unitValue) {
                    if (!unitValue || unitValue === 'each') {
                         return '';
                    }

                    return quantity === 1 || /s$/i.test(unitValue) ? unitValue : unitValue + 's';
               }

               function formatRateUnit(unitValue) {
                    return unitValue ? String(unitValue) : 'each';
               }

               function normalizeInvoiceItem(item) {
                    var normalized = $.extend({}, defaultInvoiceItem(), item || {});
                    var quantity = parseFloat(normalized.itemQuantity);
                    var unitPrice = parseFloat(normalized.itemUnitPrice);

                    normalized.itemQuantity = isFinite(quantity) && quantity > 0 ? String(Math.round(quantity)) : '1';
                    normalized.itemDurationUnit = normalized.itemDurationUnit ? String(normalized.itemDurationUnit) : 'each';
                    normalized.itemUnitPrice = isFinite(unitPrice) && unitPrice >= 0 ? unitPrice.toFixed(2) : '0.00';
                    normalized.itemDescription = normalized.itemDescription ? String(normalized.itemDescription) : '';

                    return normalized;
               }

               function parseInvoiceItems(rawItems) {
                    var items = rawItems;

                    if (typeof items === 'string') {
                         try {
                              items = JSON.parse(items);
                         } catch (error) {
                              items = [];
                         }
                    }

                    if (!Array.isArray(items)) {
                         items = [];
                    }

                    return items.map(normalizeInvoiceItem);
               }

               function buildInvoiceItemRow(item, index) {
                    var normalized = normalizeInvoiceItem(item);

                    return '' +
                         '<div class="item-row" data-item-row>' +
                         '  <div class="item-row-head">' +
                         '    <div class="item-row-title">Entry ' + (index + 1) + '</div>' +
                         '    <div class="item-row-total" data-item-line-total>PHP 0.00</div>' +
                         '  </div>' +
                         '  <div class="form-row">' +
                         '    <div class="form-group col-md-12">' +
                         '      <label>Description</label>' +
                         '      <input type="text" class="form-control" name="itemDescription[]" value="' + escapeHtml(normalized.itemDescription) + '" required>' +
                         '    </div>' +
                         '  </div>' +
                         '  <div class="form-row">' +
                         '    <div class="form-group col-md-3">' +
                         '      <label>Rate</label>' +
                         '      <input type="number" class="form-control" name="itemUnitPrice[]" min="0" step="0.01" value="' + escapeHtml(normalized.itemUnitPrice) + '">' +
                         '    </div>' +
                         '    <div class="form-group col-md-3">' +
                         '      <label>Qty</label>' +
                         '      <input type="number" class="form-control" name="itemQuantity[]" min="1" step="1" value="' + escapeHtml(normalized.itemQuantity) + '">' +
                         '    </div>' +
                         '    <div class="form-group col-md-3">' +
                         '      <label>Unit</label>' +
                         '      <select class="form-control" name="itemDurationUnit[]">' +
                         '        <option value="each"' + (normalized.itemDurationUnit === 'each' ? ' selected' : '') + '>Each</option>' +
                         '        <option value="day"' + (normalized.itemDurationUnit === 'day' ? ' selected' : '') + '>Day</option>' +
                         '        <option value="week"' + (normalized.itemDurationUnit === 'week' ? ' selected' : '') + '>Week</option>' +
                         '        <option value="month"' + (normalized.itemDurationUnit === 'month' ? ' selected' : '') + '>Month</option>' +
                         '        <option value="year"' + (normalized.itemDurationUnit === 'year' ? ' selected' : '') + '>Year</option>' +
                         '      </select>' +
                         '    </div>' +
                         '    <div class="form-group col-md-3">' +
                         '      <label>&nbsp;</label>' +
                         '      <button type="button" class="btn-remove-entry" data-remove-item-row>Remove</button>' +
                         '    </div>' +
                         '  </div>' +
                         '  <small class="item-breakdown-inline" data-item-breakdown></small>' +
                         '</div>';
               }

               function attachInvoiceItemBuilder(form, initialItems) {
                    var $form = $(form);
                    var $rows = $form.find('[data-item-rows]');
                    var $total = $form.find('input[name="TotalDue"]');
                    var $warning = $form.find('[data-total-warning]');

                    if (!$rows.length || !$total.length) {
                         return;
                    }

                    function updatePreviewBox() {
                         var totalValue = parseFloat($total.val()) || 0;
                         var previewId = $form.closest('.modal').attr('id') === 'invoiceEditModal' ?
                              '#invoice-edit-total-preview' :
                              '#invoice-total-preview';
                         $(previewId).text('₱' + totalValue.toLocaleString('en-PH', {
                              minimumFractionDigits: 2,
                              maximumFractionDigits: 2
                         }));
                    }

                    function collectItems() {
                         var items = [];

                         $rows.find('[data-item-row]').each(function() {
                              var $row = $(this);
                              items.push(normalizeInvoiceItem({
                                   itemDescription: $row.find('input[name="itemDescription[]"]').val() || '',
                                   itemQuantity: $row.find('input[name="itemQuantity[]"]').val() || '1',
                                   itemDurationUnit: $row.find('select[name="itemDurationUnit[]"]').val() || 'each',
                                   itemUnitPrice: $row.find('input[name="itemUnitPrice[]"]').val() || '0.00'
                              }));
                         });

                         return items;
                    }

                    function renderRows(items) {
                         var normalizedItems = Array.isArray(items) && items.length ? items.map(normalizeInvoiceItem) : [defaultInvoiceItem()];
                         var markup = normalizedItems.map(function(item, index) {
                              return buildInvoiceItemRow(item, index);
                         }).join('');

                         $rows.html(markup);
                         refreshTotals();
                    }

                    function refreshTotals() {
                         var totalAmount = 0;
                         var rowCount = 0;

                         $rows.find('[data-item-row]').each(function(index) {
                              rowCount++;

                              var $row = $(this);
                              var quantity = parseFloat($row.find('input[name="itemQuantity[]"]').val()) || 0;
                              var unitPrice = parseFloat($row.find('input[name="itemUnitPrice[]"]').val()) || 0;
                              var unitValue = ($row.find('select[name="itemDurationUnit[]"]').val() || 'each').toString();
                              var lineTotal = Math.max(0, quantity * unitPrice);
                              var durationLabel = formatDurationLabel(quantity, unitValue);
                              var rateUnitLabel = formatRateUnit(unitValue);
                              var breakdownPrefix = durationLabel ?
                                   formatQuantity(quantity) + ' ' + durationLabel + ' x ' + currency(unitPrice) + ' / ' + rateUnitLabel :
                                   formatQuantity(quantity) + ' x ' + currency(unitPrice) + ' / ' + rateUnitLabel;

                              $row.find('.item-row-title').text('Entry ' + (index + 1));
                              $row.find('[data-item-line-total]').text(currency(lineTotal));
                              $row.find('[data-item-breakdown]').text(quantity > 0 ? (breakdownPrefix + ' = ' + currency(lineTotal)) : '');
                              totalAmount += lineTotal;
                         });

                         if (rowCount === 0) {
                              renderRows([defaultInvoiceItem()]);
                              return;
                         }

                         $rows.find('[data-remove-item-row]').prop('disabled', rowCount === 1);
                         $total.val(totalAmount.toFixed(2)).trigger('input');
                         updatePreviewBox();

                         var amountPaid = parseFloat($form.find('input[name="AmountPaid"]').val()) || 0;
                         if ($total.length && $total.get(0)) {
                              if (totalAmount + 0.00001 < amountPaid) {
                                   $total.get(0).setCustomValidity('Total due cannot be lower than the amount already paid.');
                                   $warning.text('Total due cannot be lower than the amount already paid.');
                              } else {
                                   $total.get(0).setCustomValidity('');
                                   $warning.text('');
                              }
                         }
                    }

                    $form.off('.invoiceItems');
                    $form.on('click.invoiceItems', '[data-add-item-row]', function() {
                         var items = collectItems();
                         items.push(defaultInvoiceItem());
                         renderRows(items);
                    });

                    $form.on('click.invoiceItems', '[data-remove-item-row]', function() {
                         var items = collectItems();
                         var rowIndex = $(this).closest('[data-item-row]').index();

                         if (items.length <= 1) {
                              renderRows([defaultInvoiceItem()]);
                              return;
                         }

                         items.splice(rowIndex, 1);
                         renderRows(items);
                    });

                    $form.on('input.invoiceItems change.invoiceItems', 'input[name="itemDescription[]"], input[name="itemQuantity[]"], select[name="itemDurationUnit[]"], input[name="itemUnitPrice[]"]', function() {
                         refreshTotals();
                    });

                    $form.on('reset.invoiceItems', function() {
                         window.setTimeout(function() {
                              renderRows([defaultInvoiceItem()]);
                         }, 0);
                    });

                    $form.on('submit.invoiceItems', function() {
                         refreshTotals();
                    });

                    renderRows(Array.isArray(initialItems) && initialItems.length ? initialItems : collectItems());
               }

               function populateInvoiceEditModal(trigger) {
                    var $trigger = $(trigger);
                    var $modal = $('#invoiceEditModal');
                    var $form = $modal.find('form[data-edit-form]');
                    var amountPaid = parseFloat($trigger.data('paid')) || 0;
                    var invoiceItems = parseInvoiceItems($trigger.attr('data-items'));

                    if (!$form.length) {
                         return;
                    }

                    $form.find('input[name="id"]').val($trigger.data('id') || '');
                    $form.find('input[name="InvoiceNo"]').val($trigger.data('invoiceNo') || '');
                    $form.find('select[name="CustID"]').val($trigger.data('custId') || '').trigger('change');
                    $form.find('input[name="CustAddress"]').val($trigger.data('custAddress') || '');
                    $form.find('textarea[name="Notes"]').val($trigger.data('notes') || '');
                    $form.find('input[name="TotalDue"]')
                         .attr('min', normalizeAmount(amountPaid))
                         .val(normalizeAmount($trigger.data('totalDue')));
                    $form.find('input[name="AmountPaid"]').val(normalizeAmount(amountPaid));
                    $form.find('input[name="Balance"]').val(normalizeAmount($trigger.data('balance')));
                    $form.find('select[name="recurringFrequency"]').val($trigger.data('recurringFrequency') || 'none');
                    $form.find('input[name="recurringScheduleDate"]').val($trigger.data('recurringScheduleDate') || '');

                    var isGeneratedOccurrence = parseInt($trigger.data('recurringTemplateId'), 10) > 0;
                    $form.find('select[name="recurringFrequency"], input[name="recurringScheduleDate"]').prop('disabled', isGeneratedOccurrence);
                    $('#invoice-edit-recurring-help').text(
                         isGeneratedOccurrence ?
                         'This invoice was generated from a recurring template. Edit the original template to change the recurrence.' :
                         'Recurring invoices generate 10 days before the schedule date for daily, weekly, monthly, quarterly, or yearly schedules.'
                    );

                    attachBalanceCalculator($form.get(0));
                    attachCustomerAddressSync($form.get(0));
                    attachInvoiceItemBuilder($form.get(0), invoiceItems);
                    $form.removeClass('was-validated');
               }

               $(function() {
                    initializeInvoiceSelect2();

                    if ($.fn.DataTable.isDataTable('#invoice-table')) {
                         $('#invoice-table').DataTable().destroy();
                    }
                    $('#invoice-table').DataTable({
                         responsive: true,
                         autoWidth: false,
                         order: [],
                         searching: true,
                         paging: true,
                         info: true,
                         lengthChange: true,
                         dom: '<"row align-items-center mb-2"<"col-sm-6"l><"col-sm-6"f>>' +
                              'rt' +
                              '<"row align-items-center mt-2"<"col-sm-6"i><"col-sm-6"p>>',
                         language: {
                              emptyTable: 'No invoices found.',
                              search: 'Search:',
                              searchPlaceholder: 'Invoice number or description...'
                         },
                         columnDefs: [{
                              targets: [4, 5, 6],
                              className: 'text-right'
                         }, {
                              targets: -1,
                              orderable: false,
                              searchable: false
                         }]
                    });

                    $('[data-balance-form]').each(function() {
                         attachBalanceCalculator(this);
                         attachCustomerAddressSync(this);
                    });

                    $('[data-item-form]').each(function() {
                         attachInvoiceItemBuilder(this);
                    });

                    $('#invoiceModal, #addpayment, #invoiceEditModal').on('shown.bs.modal', function() {
                         var form = $(this).find('form[data-balance-form]');
                         if (form.length) {
                              attachBalanceCalculator(form.get(0));
                              attachCustomerAddressSync(form.get(0));
                         }

                         var itemForm = $(this).find('form[data-item-form]');
                         if (itemForm.length) {
                              attachInvoiceItemBuilder(itemForm.get(0));
                         }

                         if ($(this).attr('id') === 'invoiceModal') {
                              initializeInvoiceCustomerSelect();
                         }

                         if ($(this).attr('id') === 'invoiceEditModal') {
                              initializeEditCustomerSelect();
                         }
                    });

                    $('#invoiceEditModal').on('show.bs.modal', function(event) {
                         if (event.relatedTarget) {
                              populateInvoiceEditModal(event.relatedTarget);
                         }
                    });

                    $('#invoice-customer').on('change', function() {
                         var selected = this.options[this.selectedIndex];
                         $('#invoice-customer-address').val(selected.getAttribute('data-address') || '');
                    });

                    $('#invoice-edit-customer').on('change', function() {
                         var selected = this.options[this.selectedIndex];
                         $('#invoice-edit-customer-address').val(selected.getAttribute('data-address') || '');
                    });
               });

               // Void Invoice Modal Handler
               window.prepareVoidModal = function(element) {
                    var orderID = $(element).data('orderid');
                    var invoiceNo = $(element).data('invoiceno');
                    $('#voidOrderID').val(orderID);
                    $('#voidInvoiceNo').val('#' + invoiceNo);
                    $('#voidReason').val('');
               };

               // Email Invoice Modal Handler
               window.prepareEmailModal = function(element) {
                    var orderID = $(element).data('orderid');
                    var invoiceNo = $(element).data('invoiceno');
                    var clientName = $(element).data('client');
                    var clientEmail = $(element).data('email');
                    $('#emailOrderID').val(orderID);
                    $('#emailInvoiceNo').val('#' + invoiceNo);
                    $('#emailClientName').val(clientName);
                    $('#recipientEmail').val(clientEmail || '');
                    $('#emailMessage').val('');
               };
          })(jQuery);
     </script>

</body>

</html>
