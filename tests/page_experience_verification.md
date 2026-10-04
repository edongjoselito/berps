# Shared page loading verification

Checked locally on 2026-10-04 (Asia/Manila).

## Implementation

- One shared partial loads the stylesheet and script. All 175 full-page HTML templates are covered: 146 through `includes/head.php`, and 29 through their standalone heads. Email and framework error templates are outside the application UI.
- Real document requests remain native. No HTML replacement, script replay, link interception, form resubmission, or background polling hooks were introduced.
- Native page transitions run where supported. The topbar/sidebar remain visually steady; the desktop sidebar preference is retained per app in session storage.
- Navigation progress appears after 80 ms; a non-blocking spinner appears after 650 ms. Initial page initialization uses a 220 ms progress delay. Fast pages do not wait for an artificial animation.
- Loader cleanup covers completion, abort, Back/Forward restoration, printing, Escape, and dismissal. A 60-second visual watchdog handles responses that do not replace the document.
- Calendar event requests explicitly use the same spinner, including error cleanup. Notification polling stays silent.
- Print-preview screens receive navigation feedback on screen; print CSS hides it on paper. Invoice PDF rendering excludes the new assets.

## Validation

- 194 before/after page-and-role comparisons, including detail/edit screens. These are comparisons, not a count of unique pages. Six final standalone/public screen checks confirmed exactly one loader include each.
- 211 PHP view files passed syntax checks. The new JavaScript passed `node --check`; `git diff --check` passed.
- 25 browser regression checks passed in installed Google Chrome, including a simulated browser without the Navigation API. The tests use an isolated fixture server and never write to the application database.
- Live calendar checks passed with delayed success and failed event requests. No new calendar script errors; the spinner cleared in both cases.
- Desktop and mobile spinner placement were visually inspected.
- Application audits used local test sessions and GET requests. Business forms, payments, deletes, outgoing mail and uploads were not submitted against the application. Multipart uploads, validation, confirmations and single submissions were exercised on the fixture server.
- Safari and Firefox were not run. Record-specific pages without a usable local fixture received template/include and PHP syntax verification only; this is not a claim that every business workflow was exercised.

Run the browser regression suite with Playwright installed outside the application:

```sh
npm install --prefix /tmp/berps-navigation-qa playwright --no-audit --no-fund
NODE_PATH=/tmp/berps-navigation-qa/node_modules node tests/page_experience_browser_test.cjs
```

## Existing issues observed before the change

These were reproduced with the new assets disabled or in the original baseline. They are not resolved by this navigation change:

- `Page/annualGoals` redirects to a missing `Page/dashboard` for some tested roles (404). A sampled `Page/staffprofile` link also returned 404.
- `Page/reports` and `Settings/loginFormBanner` returned server errors (500).
- Some closed/all support lists, a delivery detail, and legacy collections/yearly-payment screens produced existing PHP warnings/errors.
- Existing frontend errors include missing graph containers, DataTables `_DT_CellIndex` failures on some tables, `esc is not defined` on the staff dashboard, missing `$` on legacy layouts, and missing Summernote initialization on article creation.
- Access-denied responses and existing server-error responses do not render the application layout. They cannot display an incoming application spinner.

## Page-by-page browser checks

“Existing issue” means an HTTP/PHP/script problem was already present. “Access response” means the endpoint returned without the app layout. Record query values are omitted from this report.

| Role | Route | Result |
| --- | --- | --- |
| admin | `Calendar` | Loaded |
| admin | `Calendar/availability` | Loaded |
| admin | `Calendar/completion_stats` | Loaded |
| admin | `Calendar/event_types` | Loaded |
| admin | `Calendar/print_all` | Loaded; shared loader verified |
| admin | `Page/accomplishments` | Existing issue |
| admin | `Page/accountingReports` | Loaded |
| admin | `Page/addExpenses` | Loaded |
| admin | `Page/addPaymentJO (record)` | Loaded |
| admin | `Page/addProject` | Existing issue |
| admin | `Page/admin` | Loaded |
| admin | `Page/annualGoals` | Loaded |
| admin | `Page/attendanceList` | Loaded |
| admin | `Page/bday_month` | Existing issue |
| admin | `Page/bday_today` | Existing issue |
| admin | `Page/businessDetails` | Loaded |
| admin | `Page/cancelledTicketLogs` | Loaded |
| admin | `Page/changeDP` | Existing issue |
| admin | `Page/clientEntry` | Loaded |
| admin | `Page/clientList` | Loaded |
| admin | `Page/clientProfile (record)` | Loaded |
| admin | `Page/collectionsEmployee` | Existing issue |
| admin | `Page/customerDeliveryList` | Loaded |
| admin | `Page/dtr` | Existing issue |
| admin | `Page/editCustomerDelivery (record)` | Loaded |
| admin | `Page/empDTR` | Loaded |
| admin | `Page/empProfile (record)` | Existing issue |
| admin | `Page/employeeAccomplishment` | Existing issue |
| admin | `Page/employeeList` | Existing issue |
| admin | `Page/employeeTask` | Existing issue |
| admin | `Page/expensesList` | Existing issue |
| admin | `Page/expensesReport` | Loaded; shared loader verified |
| admin | `Page/invList` | Existing issue |
| admin | `Page/invoice (record)` | Loaded |
| admin | `Page/invoiceEntry` | Loaded |
| admin | `Page/invoiceStatusReport` | Existing issue |
| admin | `Page/joList` | Existing issue |
| admin | `Page/jobOrderEntry` | Loaded |
| admin | `Page/knowledgeBase` | Loaded |
| admin | `Page/knowledgeBaseCreate` | Existing issue |
| admin | `Page/knowledgeBaseEdit/1` | Loaded |
| admin | `Page/knowledgeBaseSettings` | Loaded |
| admin | `Page/knowledgeBaseView/1` | Loaded |
| admin | `Page/newCustomerDelivery` | Loaded |
| admin | `Page/noteList` | Loaded |
| admin | `Page/paymentHistory (record)` | Loaded |
| admin | `Page/paymentList` | Loaded |
| admin | `Page/paymentListYear` | Existing issue |
| admin | `Page/paymentsWithTax` | Loaded |
| admin | `Page/payrollModule` | Loaded |
| admin | `Page/payrollRuns` | Loaded |
| admin | `Page/payrollSetup` | Loaded |
| admin | `Page/priceList` | Existing issue |
| admin | `Page/priceListProduct` | Existing issue |
| admin | `Page/projectAddTask` | Loaded |
| admin | `Page/projectList` | Loaded |
| admin | `Page/ranking` | Loaded |
| admin | `Page/recurringCronSetup` | Loaded |
| admin | `Page/recurringInvoices` | Loaded |
| admin | `Page/reminders` | Loaded |
| admin | `Page/reports` | Existing issue |
| admin | `Page/revenueReports` | Loaded |
| admin | `Page/staffprofile (record)` | Existing issue |
| admin | `Page/supportDashboard` | Loaded |
| admin | `Page/supportIssueView (record)` | Loaded |
| admin | `Page/supportIssues?scope=awaiting_reply` | Loaded |
| admin | `Page/supportIssues?scope=closed` | Existing issue |
| admin | `Page/supportIssues?scope=open` | Loaded |
| admin | `Page/supportIssues?scope=unassigned` | Loaded |
| admin | `Page/taxSummaryReport` | Loaded |
| admin | `Page/todaysExpenses` | Existing issue |
| admin | `Page/topClientsReport` | Loaded; shared loader verified |
| admin | `Page/unifiedPayment` | Loaded |
| admin | `Page/unpaidInvoices` | Existing issue |
| admin | `Page/updateEmployee (record)` | Existing issue |
| admin | `Page/updateProject (record)` | Existing issue |
| admin | `Page/viewCustomerDelivery (record)` | Existing issue |
| admin | `Page/yearlyReport` | Loaded |
| admin | `Pos/posAdmin` | Loaded |
| admin | `Pos/posCategorySettings` | Loaded |
| admin | `Pos/posNewTransaction` | Loaded |
| admin | `Pos/posProductList` | Existing issue |
| admin | `Pos/posReports` | Loaded |
| admin | `Pos/posStockLevels` | Existing issue |
| admin | `Pos/posTransactionHistory` | Existing issue |
| admin | `Reminders` | Existing issue |
| admin | `Reminders/history` | Existing issue |
| admin | `Settings/Department` | Loaded |
| admin | `Settings/InvoiceUnits` | Loaded |
| admin | `Settings/Sections` | Loaded |
| admin | `Settings/invoiceUnits` | Loaded |
| admin | `Settings/loginFormBanner` | Existing issue |
| admin | `Settings/schoolInfo` | Loaded |
| admin | `Users/` | Loaded |
| admin | `ZohoMail/compose` | Loaded |
| admin | `ZohoMail/inbox` | Loaded |
| admin | `ZohoMail/settings` | Loaded |
| client | `Page/clientDashboard` | Loaded |
| client | `Page/clientMyTickets` | Loaded |
| client | `Page/clientProfile` | Loaded |
| client | `Page/clientReportIssue` | Loaded |
| client | `Page/knowledgeBase` | Loaded |
| client | `Page/knowledgeBase?type=article` | Loaded |
| client | `Page/knowledgeBase?type=faq` | Loaded |
| client | `client/cancelled-ticket-logs` | Loaded |
| client | `client/closed-task-report` | Loaded |
| client | `client/pending-tasks` | Loaded |
| client | `client/requested-today` | Loaded |
| pos | `Calendar` | Loaded |
| pos | `Page/annualGoals` | Existing issue |
| pos | `Page/bday_month` | Existing issue |
| pos | `Page/bday_today` | Existing issue |
| pos | `Page/knowledgeBase` | Loaded |
| pos | `Page/knowledgeBaseSettings` | Loaded |
| pos | `Page/supportDashboard` | Access response / separate layout |
| pos | `Page/supportIssues?scope=all` | Access response / separate layout |
| pos | `Page/supportIssues?scope=awaiting_reply` | Access response / separate layout |
| pos | `Page/supportIssues?scope=closed` | Access response / separate layout |
| pos | `Page/supportIssues?scope=open` | Access response / separate layout |
| pos | `Page/supportIssues?scope=unassigned` | Access response / separate layout |
| pos | `Pos/posAdmin` | Loaded |
| pos | `Pos/posExpiredProducts` | Existing issue |
| pos | `Pos/posExpiringSoon` | Existing issue |
| pos | `Pos/posLowStockItems` | Existing issue |
| pos | `Pos/posNewTransaction` | Loaded |
| pos | `Pos/posReports` | Loaded |
| pos | `Pos/posReturnsVoids` | Existing issue |
| pos | `Pos/posStockLevels` | Existing issue |
| pos | `Pos/posTransactionHistory` | Existing issue |
| pos | `Users/` | Loaded |
| pos | `ZohoMail/compose` | Loaded |
| pos | `ZohoMail/inbox` | Loaded |
| pos | `ZohoMail/settings` | Loaded |
| public | `/` | Loaded; shared loader verified |
| public | `Login/forgot` | Loaded; shared loader verified |
| public | `Login/signup_page` | Loaded; shared loader verified |
| staff | `Calendar` | Loaded |
| staff | `Page/accomplishments` | Existing issue |
| staff | `Page/annualGoals` | Existing issue |
| staff | `Page/attendanceList` | Existing issue |
| staff | `Page/bday_month` | Existing issue |
| staff | `Page/bday_today` | Existing issue |
| staff | `Page/clientList` | Loaded |
| staff | `Page/customerDeliveryList` | Existing issue |
| staff | `Page/expensesList` | Existing issue |
| staff | `Page/invList` | Existing issue |
| staff | `Page/joList` | Existing issue |
| staff | `Page/knowledgeBase` | Loaded |
| staff | `Page/knowledgeBaseSettings` | Loaded |
| staff | `Page/myDTR` | Loaded |
| staff | `Page/newCustomerDelivery` | Loaded |
| staff | `Page/noteList` | Loaded |
| staff | `Page/paymentList` | Loaded |
| staff | `Page/projectAddTask` | Loaded |
| staff | `Page/projectAddTask?status=open&scope=forwarded` | Loaded |
| staff | `Page/projectList` | Loaded |
| staff | `Page/ranking` | Loaded |
| staff | `Page/reminders` | Loaded |
| staff | `Page/staff` | Existing issue |
| staff | `Page/supportDashboard` | Loaded |
| staff | `Page/supportIssues?scope=all` | Existing issue |
| staff | `Page/supportIssues?scope=awaiting_reply` | Loaded |
| staff | `Page/supportIssues?scope=closed` | Existing issue |
| staff | `Page/supportIssues?scope=open` | Loaded |
| staff | `Page/supportIssues?scope=unassigned` | Loaded |
| staff | `ZohoMail/compose` | Loaded |
| staff | `ZohoMail/inbox` | Loaded |
| staff | `ZohoMail/settings` | Loaded |
| super | `Calendar` | Loaded |
| super | `Page/annualGoals` | Existing issue |
| super | `Page/bday_month` | Existing issue |
| super | `Page/bday_today` | Existing issue |
| super | `Page/knowledgeBase` | Access response / separate layout |
| super | `Page/knowledgeBaseSettings` | Access response / separate layout |
| super | `Page/reminders` | Loaded |
| super | `Page/superAdmin` | Loaded |
| super | `Page/superAdminAdmins` | Loaded |
| super | `Page/superAdminBilling` | Loaded |
| super | `Page/superAdminCompanies` | Loaded |
| super | `Page/superAdminRecaptchaSettings` | Loaded |
| super | `Page/superAdminSettings` | Loaded |
| super | `Page/superAdminSignupPackages` | Loaded |
| super | `Page/superAdminUsers` | Loaded |
| super | `Page/supportDashboard` | Access response / separate layout |
| super | `Page/supportIssues?scope=all` | Access response / separate layout |
| super | `Page/supportIssues?scope=awaiting_reply` | Access response / separate layout |
| super | `Page/supportIssues?scope=closed` | Access response / separate layout |
| super | `Page/supportIssues?scope=open` | Access response / separate layout |
| super | `Page/supportIssues?scope=unassigned` | Access response / separate layout |
| super | `ZohoMail/compose` | Loaded |
| super | `ZohoMail/inbox` | Loaded |
| super | `ZohoMail/settings` | Loaded |

## Full-page template coverage

Every row below passed PHP syntax and include-path checks. A template appearing here does not imply its entire workflow was tested interactively.

| View (under application/views) | Shared asset entry |
| --- | --- |
| `accomplishments.php` | Common head |
| `accomplishments_per_employee.php` | Common head |
| `accomplishments_today.php` | Common head |
| `accounting_reports.php` | Common head |
| `add_expense.php` | Common head |
| `add_payment.php` | Standalone head → shared partial |
| `add_payment_jo.php` | Common head |
| `add_project.php` | Common head |
| `annual_goals.php` | Common head |
| `annual_goals_report.php` | Common head |
| `attendance_list.php` | Common head |
| `auth_forgot.php` | Standalone head → shared partial |
| `auth_reset.php` | Standalone head → shared partial |
| `bday_month.php` | Common head |
| `bday_today.php` | Common head |
| `business_details.php` | Common head |
| `calendar.php` | Common head |
| `calendar_availability.php` | Common head |
| `calendar_booking.php` | Standalone head → shared partial |
| `calendar_completion_stats.php` | Common head |
| `calendar_event_types.php` | Common head |
| `calendar_events_list.php` | Common head |
| `calendar_print.php` | Standalone head → shared partial |
| `cancelled_ticket_logs.php` | Common head |
| `change_pass.php` | Common head |
| `client_accomplished_tasks.php` | Common head |
| `client_closed_task_report.php` | Common head |
| `client_dashboard.php` | Common head |
| `client_entry.php` | Common head |
| `client_list.php` | Common head |
| `client_pending_tasks.php` | Common head |
| `client_profile.php` | Common head |
| `client_report_issue.php` | Common head |
| `client_request_form.php` | Common head |
| `client_requested_today.php` | Common head |
| `client_support_ticket_view.php` | Common head |
| `client_support_tickets.php` | Common head |
| `client_task_list.php` | Common head |
| `collections_per_employee.php` | Standalone head → shared partial |
| `customer_delivery_list.php` | Common head |
| `customer_payment_history.php` | Common head |
| `customer_support/submit_issue.php` | Standalone head → shared partial |
| `dashboard_admin-old.php` | Standalone head → shared partial |
| `dashboard_admin.php` | Common head |
| `dashboard_pos_admin.php` | Common head |
| `dashboard_pos_staff.php` | Common head |
| `dashboard_reports.php` | Standalone head → shared partial |
| `dashboard_staff.php` | Common head |
| `dashboard_staff_OLD.php` | Standalone head → shared partial |
| `dashboard_super_admin.php` | Common head |
| `delivery_list.php` | Common head |
| `delivery_payment.php` | Common head |
| `dtr.php` | Common head |
| `dtr_employee.php` | Common head |
| `dtr_my.php` | Common head |
| `edit_customer_delivery.php` | Common head |
| `employee_documents.php` | Common head |
| `employee_education.php` | Common head |
| `employee_emergency_contacts.php` | Common head |
| `employee_list.php` | Common head |
| `employee_profile.php` | Common head |
| `employee_skills.php` | Common head |
| `employee_task_accomplishement.php` | Common head |
| `employee_task_all.php` | Common head |
| `employee_task_select.php` | Common head |
| `employment_history.php` | Common head |
| `expense_print.php` | Standalone head → shared partial |
| `expenses_list.php` | Common head |
| `expenses_list_range.php` | Common head |
| `expenses_list_range_data.php` | Standalone head → shared partial |
| `expenses_report.php` | Standalone head → shared partial |
| `home_page.php` | Standalone head → shared partial |
| `inv_list.php` | Common head |
| `invoice.php` | Standalone head → shared partial |
| `invoice_entry.php` | Common head |
| `invoice_status_report.php` | Common head |
| `invoices_unpaid.php` | Standalone head → shared partial |
| `jo_list.php` | Common head |
| `job_order_entry.php` | Common head |
| `knowledge_base.php` | Common head |
| `knowledge_base_client.php` | Common head |
| `knowledge_base_create.php` | Common head |
| `knowledge_base_edit.php` | Common head |
| `knowledge_base_search.php` | Common head |
| `knowledge_base_search_client.php` | Common head |
| `knowledge_base_settings.php` | Common head |
| `knowledge_base_view.php` | Common head |
| `knowledge_base_view_client.php` | Common head |
| `knowledge_base_view_public.php` | Standalone head → shared partial |
| `landing_page.php` | Standalone head → shared partial |
| `mobile_privacy.php` | Standalone head → shared partial |
| `new_customer_delivery.php` | Common head |
| `note_list.php` | Common head |
| `payment_history.php` | Common head |
| `payment_list.php` | Common head |
| `payment_list_range.php` | Common head |
| `payment_list_range_data.php` | Common head |
| `payment_list_year.php` | Standalone head → shared partial |
| `payments_with_tax.php` | Common head |
| `paymongo_return.php` | Standalone head → shared partial |
| `payroll_module.php` | Common head |
| `payroll_payslip.php` | Standalone head → shared partial |
| `payroll_run.php` | Common head |
| `payroll_runs.php` | Common head |
| `payroll_setup.php` | Common head |
| `pos_categories.php` | Common head |
| `pos_expiry_monitor.php` | Common head |
| `pos_fullscreen.php` | Common head |
| `pos_placeholder.php` | Common head |
| `pos_product_edit.php` | Common head |
| `pos_product_enhanced.php` | Common head |
| `pos_product_list.php` | Common head |
| `pos_reports.php` | Common head |
| `pos_stock_levels.php` | Common head |
| `pos_transaction_detail.php` | Common head |
| `pos_transaction_form.php` | Common head |
| `pos_transaction_history.php` | Common head |
| `price_list_product.php` | Standalone head → shared partial |
| `price_list_service.php` | Common head |
| `print_delivery_receipt.php` | Standalone head → shared partial |
| `print_job_order_form.php` | Standalone head → shared partial |
| `product_delivery.php` | Common head |
| `product_list.php` | Common head |
| `profile_page_staff.php` | Common head |
| `project_deployment_status.php` | Common head |
| `project_list.php` | Common head |
| `project_list_task-OLD.php` | Standalone head → shared partial |
| `project_list_task.php` | Common head |
| `recurring_cron_setup.php` | Common head |
| `recurring_invoices.php` | Common head |
| `reminders.php` | Common head |
| `reminders_edit.php` | Common head |
| `reminders_history.php` | Common head |
| `reminders_list.php` | Common head |
| `request_list.php` | Common head |
| `revenue_reports.php` | Common head |
| `settings_department.php` | Common head |
| `settings_invoice_units.php` | Common head |
| `settings_placeholder.php` | Common head |
| `settings_sections.php` | Common head |
| `signup_page.php` | Standalone head → shared partial |
| `super_admin_admins.php` | Common head |
| `super_admin_billing.php` | Common head |
| `super_admin_companies.php` | Common head |
| `super_admin_company_billing.php` | Common head |
| `super_admin_company_features.php` | Common head |
| `super_admin_recaptcha_settings.php` | Common head |
| `super_admin_settings.php` | Common head |
| `super_admin_signup_packages.php` | Common head |
| `super_admin_users.php` | Common head |
| `support_dashboard.php` | Common head |
| `support_issue_list.php` | Common head |
| `support_issue_view.php` | Common head |
| `task_ranking.php` | Common head |
| `tasks_list_project.php` | Common head |
| `taskstat_list_stat.php` | Common head |
| `tax_summary_report.php` | Common head |
| `top_clients_report.php` | Standalone head → shared partial |
| `unified_payment.php` | Common head |
| `update_client.php` | Common head |
| `update_employee.php` | Common head |
| `update_expense.php` | Common head |
| `update_jo.php` | Standalone head → shared partial |
| `update_payment.php` | Common head |
| `update_product.php` | Common head |
| `update_project.php` | Common head |
| `upload_profile_pic.php` | Common head |
| `users_list.php` | Common head |
| `void_invoice_report.php` | Common head |
| `void_payment_report.php` | Common head |
| `yearly_report.php` | Common head |
| `zoho_mail/compose.php` | Common head |
| `zoho_mail/inbox.php` | Common head |
| `zoho_mail/message.php` | Common head |
| `zoho_mail/settings.php` | Common head |

## Browser API references

- [Cross-document view transitions (Chrome)](https://developer.chrome.com/docs/web-platform/view-transitions/cross-document)
- [Navigation events and cancellation (MDN)](https://developer.mozilla.org/en-US/docs/Web/API/NavigateEvent)

The implementation progressively enhances native loads; browser support determines whether the cross-document animation itself is available.
