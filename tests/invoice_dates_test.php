<?php
// Exercise the real controller without application boot or database writes.
class CI_Controller { public $input, $session, $CashModel, $db; }
require __DIR__ . '/../application/controllers/Page.php';
function show_error($message, $code) { throw new RuntimeException((string) $code); }
function show_404() { throw new RuntimeException('404'); }
function redirect($url) { $GLOBALS['date_redirect'] = $url; }
class DateRequest {
    public $verb = 'post';
    public $values = array('id' => 4057, 'TransDate' => '2026-10-01', 'ReceiveDate' => '2026-10-31');
    function method() { return $this->verb; }
    function post($key) { return $this->values[$key] ?? null; }
}
class DateSession {
    public $role = 'Admin', $messages = array();
    function userdata($key) { return $key === 'level' ? $this->role : 7; }
    function set_flashdata($key, $value) { $this->messages[$key] = $value; }
}
class DateStore {
    public $missing = false, $ok = true, $payload = null, $filters = array();
    function getInvoiceByOrderID($id, $tenant) {
        if ($id !== 4057 || $tenant !== 7) { throw new RuntimeException('Invalid lookup scope'); }
        return $this->missing ? null : (object) array('invoiceStat' => 'active');
    }
    function where($key, $value) { $this->filters[$key] = $value; return $this; }
    function update($table, $values) { $this->payload = $values; return $this->ok; }
}
$r = new ReflectionClass('Page');
foreach (array('save', 'invalid', 'client', 'get', 'missing', 'failure') as $case) {
    $p = $r->newInstanceWithoutConstructor();
    $p->input = new DateRequest; $p->session = new DateSession;
    $p->db = $p->CashModel = new DateStore;
    if ($case === 'invalid') { $p->input->values['ReceiveDate'] = '2026-02-30'; }
    if ($case === 'client') { $p->session->role = 'Client'; }
    if ($case === 'get') { $p->input->verb = 'get'; }
    $p->db->missing = $case === 'missing'; $p->db->ok = $case !== 'failure';
    $error = null;
    try { $p->updateInvoiceDates(); } catch (RuntimeException $e) { $error = $e->getMessage(); }
    $expectedError = array('client' => '403', 'get' => '405', 'missing' => '404')[$case] ?? null;
    if ($error !== $expectedError) { throw new RuntimeException('Wrong response: ' . $case); }
    if (in_array($case, array('save', 'failure'), true)) {
        if ($p->db->payload !== array('TransDate' => '2026-10-01', 'ReceiveDate' => '2026-10-31')
            || $p->db->filters !== array('orderID' => 4057, 'settingsID' => 7)) {
            throw new RuntimeException('Unexpected update scope or fields');
        }
    } elseif ($p->db->payload !== null) { throw new RuntimeException('Rejected request wrote data'); }
    if ($expectedError === null && !isset($p->session->messages[$case === 'save' ? 'success' : 'danger'])) {
        throw new RuntimeException('Missing feedback');
    }
}
$method = $r->getMethod('_resolveInvoiceReportDueDate'); $method->setAccessible(true);
$p = $r->newInstanceWithoutConstructor();
if ($method->invoke($p, (object) array('ReceiveDate' => '2026-10-31', 'recurringScheduleDate' => '2026-10-01')) !== '2026-10-31') {
    throw new RuntimeException('Report ignored saved due date');
}
echo "Passed 7 invoice date checks.\n";
