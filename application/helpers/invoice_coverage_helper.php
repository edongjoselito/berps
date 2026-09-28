<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/** Shift a billing boundary, clamping dates such as January 31 to February 28. */
function invoice_coverage_shift_months(DateTimeImmutable $date, $months)
{
    $target = $date->modify('first day of this month')->modify(sprintf('%+d months', $months));
    return $target->setDate((int) $target->format('Y'), (int) $target->format('m'), min((int) $date->format('d'), (int) $target->format('t')));
}

/** Due date is the exclusive end in arrears, and inclusive start in advance. */
function invoice_service_coverage($dueDate, $frequency, $timing = 'coming')
{
    $due = DateTimeImmutable::createFromFormat('!Y-m-d', (string) $dueDate);
    if (!$due || $due->format('Y-m-d') !== (string) $dueDate || $dueDate === '0000-00-00') {
        return null;
    }
    $months = array('monthly' => 1, 'quarterly' => 3, 'yearly' => 12);
    $previous = $timing === 'previous';
    if (isset($months[$frequency])) {
        $boundary = invoice_coverage_shift_months($due, ($previous ? -1 : 1) * $months[$frequency]);
    } elseif ($frequency === 'daily' || $frequency === 'weekly') {
        $days = $frequency === 'daily' ? 1 : 7;
        $boundary = $due->modify(sprintf('%+d days', ($previous ? -1 : 1) * $days));
    } else {
        return null;
    }
    return array(
        'start' => ($previous ? $boundary : $due)->format('Y-m-d'),
        'end' => ($previous ? $due : $boundary)->modify('-1 day')->format('Y-m-d'),
    );
}

function invoice_service_coverage_label($dueDate, $frequency, $timing = 'coming')
{
    $period = invoice_service_coverage($dueDate, $frequency, $timing);
    return $period ? 'From ' . date('M d, Y', strtotime($period['start'])) . ' To ' . date('M d, Y', strtotime($period['end'])) : '';
}
