<?php
// Isolated regression test: no application boot or database writes.
class CI_Controller { public $db; }
require __DIR__ . '/../application/controllers/Page.php';

class OccurrenceQueryStub
{
    private $row;
    private $filters = array();
    public function __construct($row) { $this->row = $row; }
    public function select($value, $escape = null) {
        if (strpos($value, 'MAX(') !== false) {
            throw new RuntimeException('generation-reached');
        }
        return $this;
    }
    public function from($table) { return $this; }
    public function where($key, $value) { $this->filters[$key] = array($value); return $this; }
    public function where_in($key, $values) { $this->filters[$key] = $values; return $this; }
    public function group_start() { return $this; }
    public function or_where($key, $value) { return $this; }
    public function group_end() { return $this; }
    public function limit($limit) { return $this; }
    public function get() { return $this; }
    public function row() {
        foreach (array('settingsID', 'invoiceStat', 'recurringScheduleDate') as $key) {
            if (!in_array($this->row->$key, $this->filters[$key], true)) { return null; }
        }
        return $this->row;
    }
}

$reflection = new ReflectionClass('Page');
$method = $reflection->getMethod('_createRecurringInvoiceOccurrence');
$method->setAccessible(true);
foreach (array(
    array('active', 7, '2026-10-01', true),
    array('Deleted', 7, '2026-10-01', true),
    array('Deleted', 8, '2026-10-01', false),
    array('Deleted', 7, '2026-11-01', false),
    array('Voided', 7, '2026-10-01', false),
) as $case) {
    $page = $reflection->newInstanceWithoutConstructor();
    $page->db = new OccurrenceQueryStub((object) array(
        'orderID' => 12, 'InvoiceNo' => 'TEST', 'invoiceStat' => $case[0],
        'settingsID' => $case[1], 'recurringScheduleDate' => $case[2],
    ));
    $blocked = false;
    try {
        $result = $method->invoke($page, (object) array('orderID' => 1), 7, 'monthly', '2026-10-01');
        $blocked = $result['status'] === 'existing';
    } catch (RuntimeException $e) {
        if ($e->getMessage() !== 'generation-reached') { throw $e; }
    }
    if ($blocked !== $case[3]) { throw new RuntimeException('Unexpected generation decision: ' . json_encode($case)); }
}
echo "Passed 5 recurring occurrence checks.\n";
