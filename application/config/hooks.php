<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| Hooks
| -------------------------------------------------------------------------
| This file lets you define "hooks" to extend CI without hacking the core
| files.  Please see the user guide for info:
|
|	https://codeigniter.com/user_guide/general/hooks.html
|
*/

// Apply Manila timezone for PHP as early as possible
$hook['pre_system'][] = array(
	'class'    => 'TimezoneHook',
	'function' => 'setPhpTimezone',
	'filename' => 'TimezoneHook.php',
	'filepath' => 'hooks',
);

// Apply Manila timezone to the active MySQL connection after CI bootstraps it
$hook['post_controller_constructor'][] = array(
	'class'    => 'TimezoneHook',
	'function' => 'setDbTimezone',
	'filename' => 'TimezoneHook.php',
	'filepath' => 'hooks',
);
