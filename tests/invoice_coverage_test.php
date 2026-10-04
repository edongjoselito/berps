<?php
define('BASEPATH', __DIR__);
require __DIR__ . '/../application/helpers/invoice_coverage_helper.php';

$cases = array(
    array('2026-10-01', 'monthly', 'previous', '2026-09-02', '2026-10-01'),
    array('2026-10-01', 'quarterly', 'previous', '2026-07-02', '2026-10-01'),
    array('2026-10-01', 'yearly', 'previous', '2025-10-02', '2026-10-01'),
    array('2026-10-01', 'monthly', 'coming', '2026-10-01', '2026-10-31'),
    array('2026-10-01', 'quarterly', 'coming', '2026-10-01', '2026-12-31'),
    array('2026-10-01', 'yearly', 'coming', '2026-10-01', '2027-09-30'),
    array('2026-11-15', 'quarterly', 'coming', '2026-11-15', '2027-02-14'),
    array('2026-11-15', 'quarterly', 'previous', '2026-08-16', '2026-11-15'),
    array('2026-03-31', 'monthly', 'previous', '2026-03-01', '2026-03-31'),
    array('2024-03-31', 'monthly', 'previous', '2024-03-01', '2024-03-31'),
    array('2026-01-31', 'monthly', 'coming', '2026-01-31', '2026-02-27'),
    array('2024-02-29', 'yearly', 'coming', '2024-02-29', '2025-02-27'),
    array('2024-02-29', 'yearly', 'previous', '2023-03-01', '2024-02-29'),
    array('2026-10-01', 'weekly', 'previous', '2026-09-25', '2026-10-01'),
    array('2026-10-01', 'daily', 'previous', '2026-10-01', '2026-10-01'),
);
foreach ($cases as $case) {
    list($due, $frequency, $timing, $start, $end) = $case;
    $actual = invoice_service_coverage($due, $frequency, $timing);
    if ($actual !== array('start' => $start, 'end' => $end)) {
        throw new RuntimeException(json_encode(array($case, $actual)));
    }
}
foreach (array('', '0000-00-00', '2026-02-30', 'invalid') as $date) {
    if (invoice_service_coverage($date, 'monthly') !== null) {
        throw new RuntimeException('Invalid date accepted: ' . $date);
    }
}
if (invoice_service_coverage('2026-10-01', 'none') !== null) {
    throw new RuntimeException('One-time invoice must not have recurring coverage.');
}
if (invoice_service_coverage_label('2026-10-01', 'monthly', 'previous') !== 'From Sep 02, 2026 To Oct 01, 2026') {
    throw new RuntimeException('Unexpected coverage label.');
}
echo "Passed 21 coverage checks.\n";
