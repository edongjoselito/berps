<?php
defined('BASEPATH') or exit('No direct script access allowed');


$route['default_controller'] = 'Login';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;
$route['login/forgot'] = 'Login/forgot';
$route['login/reset/(:any)/(:any)'] = 'Login/reset/$1/$2';
$route['login/logout'] = 'Login/logout';
$route['upload-image'] = 'Page';
$route['store-image'] = 'Page/upload';
$route['client/requested-today'] = 'Page/clientRequestedToday';
$route['client/accomplished-tasks'] = 'Page/clientAccomplishedTasks';
$route['client/pending-tasks'] = 'Page/clientPendingTasks';
$route['client/closed-task-report'] = 'Page/clientClosedTaskReport';
$route['client/my-tickets'] = 'Page/clientMyTickets';
$route['client/report-issue'] = 'Page/clientReportIssue';
$route['client/cancelled-ticket-logs'] = 'Page/cancelledTicketLogs';
$route['client/ticket/(:num)'] = 'Page/clientTicketView?id=$1';
$route['client/dashboard'] = 'Page/clientDashboard';
$route['Page/clientDashboard'] = 'Page/clientDashboard';

// ── Mobile API ───────────────────────────────────────────────────────────────
$route['api/mobile/config']            = 'api/MobileAuth/config';
$route['api/mobile/auth/login']        = 'api/MobileAuth/login';
$route['api/mobile/auth/me']           = 'api/MobileAuth/me';
$route['api/mobile/auth/logout']       = 'api/MobileAuth/logout';
$route['api/mobile/auth/forgot-password'] = 'api/MobileAuth/forgotPassword';
$route['api/mobile/auth/verify-otp']      = 'api/MobileAuth/verifyOtp';
$route['api/mobile/auth/reset-password']  = 'api/MobileAuth/resetPassword';
$route['api/mobile/staff/dashboard']   = 'api/MobileStaff/dashboard';
$route['api/mobile/staff/attendance']  = 'api/MobileStaff/attendance';
$route['api/mobile/staff/attendance/time-in'] = 'api/MobileStaff/attendanceTimeIn';
$route['api/mobile/staff/attendance/time-out'] = 'api/MobileStaff/attendanceTimeOut';
$route['api/mobile/staff/tasks']       = 'api/MobileStaff/tasks';
$route['api/mobile/staff/tasks/create'] = 'api/MobileStaff/createTask';
$route['api/mobile/staff/tasks/(:num)'] = 'api/MobileStaff/taskDetail/$1';
$route['api/mobile/staff/tasks/(:num)/update'] = 'api/MobileStaff/updateTask/$1';
$route['api/mobile/staff/tasks/(:num)/checklist'] = 'api/MobileStaff/saveTaskChecklist/$1';
$route['api/mobile/staff/tasks/(:num)/status'] = 'api/MobileStaff/updateTaskStatus/$1';
$route['api/mobile/staff/tasks/(:num)/forward'] = 'api/MobileStaff/forwardTask/$1';
$route['api/mobile/staff/tasks/(:num)/delete'] = 'api/MobileStaff/deleteTask/$1';
$route['api/mobile/staff/ranking']     = 'api/MobileStaff/ranking';
$route['api/mobile/staff/support/issues']                  = 'api/MobileStaff/supportIssues';
$route['api/mobile/staff/support/issues/(:num)']           = 'api/MobileStaff/supportIssueView/$1';
$route['api/mobile/staff/support/issues/(:num)/comment']   = 'api/MobileStaff/supportIssueComment/$1';
$route['api/mobile/staff/support/issues/(:num)/close']     = 'api/MobileStaff/supportIssueClose/$1';
$route['api/mobile/staff/support/issues/(:num)/forward']   = 'api/MobileStaff/supportIssueForward/$1';
$route['api/mobile/staff/support/issues/(:num)/tag']       = 'api/MobileStaff/supportIssueTag/$1';
$route['api/mobile/staff/notifications']                   = 'api/MobileStaff/notifications';
$route['api/mobile/staff/notifications/mark-seen']         = 'api/MobileStaff/notificationsMarkSeen';
$route['api/mobile/staff/profile']                         = 'api/MobileStaff/profile';
$route['api/mobile/staff/profile/avatar']                  = 'api/MobileStaff/uploadAvatar';
$route['api/mobile/staff/calendar/events']                 = 'api/MobileStaff/calendarEvents';
$route['api/mobile/staff/calendar/events/create']          = 'api/MobileStaff/calendarEventCreate';
$route['api/mobile/staff/calendar/events/(:num)']          = 'api/MobileStaff/calendarEventDetail/$1';
$route['api/mobile/staff/calendar/events/(:num)/update']   = 'api/MobileStaff/calendarEventUpdate/$1';
$route['api/mobile/staff/calendar/events/(:num)/delete']   = 'api/MobileStaff/calendarEventDelete/$1';
$route['api/mobile/staff/annual-goals']                    = 'api/MobileStaff/annualGoals';
$route['api/mobile/staff/annual-goals/(:num)']             = 'api/MobileStaff/annualGoalDetail/$1';
$route['api/mobile/staff/support/dashboard']               = 'api/MobileStaff/supportDashboard';
$route['api/mobile/staff/my-dtr']                           = 'api/MobileStaff/myDTR';
$route['api/mobile/staff/profile/update']                   = 'api/MobileStaff/updateProfile';

// ── Mobile Admin endpoints (level "Admin" tokens only) ───────────────────────
$route['api/mobile/admin/dashboard']                        = 'api/MobileAdmin/dashboard';
$route['api/mobile/admin/tasks']                            = 'api/MobileAdmin/tasks';
$route['api/mobile/admin/tasks/create']                     = 'api/MobileAdmin/createTask';
$route['api/mobile/admin/employee-tasks']                   = 'api/MobileAdmin/employeeTasks';
$route['api/mobile/admin/accomplishments']                  = 'api/MobileAdmin/accomplishments';
$route['api/mobile/admin/employee-accomplishments']         = 'api/MobileAdmin/employeeAccomplishments';
$route['api/mobile/admin/employee-accomplishments/data']    = 'api/MobileAdmin/employeeAccomplishmentData';
$route['api/mobile/admin/attendance']                       = 'api/MobileAdmin/attendance';
$route['api/mobile/admin/dtr']                              = 'api/MobileAdmin/empDTR';
$route['api/mobile/admin/clients']                          = 'api/MobileAdmin/clients';
$route['api/mobile/admin/clients/create']                   = 'api/MobileAdmin/createClient';
$route['api/mobile/admin/clients/update']                   = 'api/MobileAdmin/updateClient';
$route['api/mobile/admin/clients/delete']                   = 'api/MobileAdmin/deleteClient';
