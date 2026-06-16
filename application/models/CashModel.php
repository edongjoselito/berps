<?php
class CashModel extends CI_Model
{
	private function invoiceCustomerJoin($alias = 'i')
	{
		return "c.settingsID = {$alias}.settingsID AND (({$alias}.CustID IS NOT NULL AND {$alias}.CustID <> '' AND c.CustID = {$alias}.CustID) OR (({$alias}.CustID IS NULL OR {$alias}.CustID = '') AND {$alias}.Customer <> '' AND c.Customer = {$alias}.Customer))";
	}

	private function paymentCustomerJoin($alias = 'p')
	{
		return "c.settingsID = {$alias}.settingsID AND (({$alias}.CustID IS NOT NULL AND {$alias}.CustID <> '' AND c.CustID = {$alias}.CustID) OR (({$alias}.CustID IS NULL OR {$alias}.CustID = '') AND {$alias}.Customer <> '' AND c.Customer = {$alias}.Customer))";
	}

	private function invoiceSelect($alias = 'i')
	{
		return implode(', ', [
			"{$alias}.orderID",
			"{$alias}.InvoiceNo",
			"COALESCE({$alias}.CustID, c.CustID) AS CustID",
			"COALESCE(c.Customer, {$alias}.Customer) AS Customer",
			"COALESCE(c.Address, {$alias}.CustAddress) AS CustAddress",
			"COALESCE(c.ContactPerson, '') AS ContactPerson",
			"COALESCE(c.ContactNos, '') AS ContactNos",
			"COALESCE(c.CompanyEmail, '') AS CompanyEmail",
			"{$alias}.TransDate",
			"{$alias}.JobDescription",
			"{$alias}.itemQuantity",
			"{$alias}.itemDurationUnit",
			"{$alias}.itemUnitPrice",
			"{$alias}.TotalDue",
			"{$alias}.AmountPaid",
			"{$alias}.Balance",
			"{$alias}.ReceiveDate",
			"{$alias}.Notes",
			"{$alias}.settingsID",
			"{$alias}.invoiceStat",
			"{$alias}.invoiceSource",
			"{$alias}.invoiceBy",
			"COALESCE({$alias}.recurringFrequency, 'none') AS recurringFrequency",
			"{$alias}.recurringScheduleDate",
			"{$alias}.recurringTerminationDate",
			"{$alias}.invoiceExpirationDate",
			"{$alias}.recurringTemplateID",
			"{$alias}.lastRecurringGeneratedFor",
		]);
	}

	private function paymentSelect($alias = 'p')
	{
		return implode(', ', [
			"{$alias}.paymentID",
			"{$alias}.InvoiceNo",
			"{$alias}.PDate",
			"{$alias}.AmountPaid",
			"COALESCE({$alias}.TaxAmount, 0) AS TaxAmount",
			"({$alias}.AmountPaid + COALESCE({$alias}.TaxAmount, 0)) AS GrossAmountPaid",
			"{$alias}.ORNo",
			"{$alias}.PaymentReference",
			"{$alias}.Cashier",
			"{$alias}.PaymentSource",
			"COALESCE({$alias}.CustID, c.CustID) AS CustID",
			"COALESCE(c.Customer, {$alias}.Customer) AS Customer",
			"{$alias}.TransDescription",
			"{$alias}.ORStat",
			"{$alias}.TerminalNo",
			"{$alias}.settingsID",
		]);
	}

	private function invoiceBaseQuery($settingsID = null)
	{
		$this->db->select($this->invoiceSelect('i'), false);
		$this->db->from('invoice i');
		$this->db->join('customers c', $this->invoiceCustomerJoin('i'), 'left');
		if ($settingsID !== null) {
			$this->db->where('i.settingsID', $settingsID);
		}
	}

	private function paymentBaseQuery()
	{
		$this->db->select($this->paymentSelect('p'), false);
		$this->db->from('payments p');
		$this->db->join('customers c', $this->paymentCustomerJoin('p'), 'left');
	}

	private function applyOperationalInvoiceStatusFilter($alias = 'i')
	{
		$this->db->where("LOWER(COALESCE({$alias}.invoiceStat, '')) <> 'deleted'", null, false);
		$this->db->where("LOWER(COALESCE({$alias}.invoiceStat, '')) <> 'voided'", null, false);
	}

	private function applyPersonMatch($field, $value)
	{
		if (is_array($value)) {
			$matches = array_values(array_filter(array_map(function ($item) {
				return trim((string) $item);
			}, $value), function ($item) {
				return $item !== '';
			}));

			if (empty($matches)) {
				$this->db->where('1 = 0', null, false);
				return;
			}

			$this->db->where_in($field, $matches);
			return;
		}

		$value = trim((string) $value);
		if ($value === '') {
			$this->db->where('1 = 0', null, false);
			return;
		}

		$this->db->where($field, $value);
	}

	public function projectListForStaff($settingsID, $user_id)
	{
		return $this->db
			->select('p.*')
			->from('projects p')
			->join('project_deployment_status d', 'd.projectID = p.projectID', 'inner')
			->where('p.settingsID', $settingsID)
			->where('d.settingsID', $settingsID)
			->where('d.user_id', $user_id)
			->group_by('p.projectID')
			->order_by('p.projectID', 'DESC')
			->get()
			->result();
	}

	public function getPendingTasksByEmployee($user_id, $settingsID)
	{
		return $this->db
			->select('t.taskID, t.task, t.reportedDate, t.dueDate, t.projectID, t.taskStat, t.priority, t.assignedPerson, t.added_by, t.attachment_link, p.projectDescription')
			->from('projects_task t')
			->join('projects p', 't.projectID = p.projectID', 'left')
			->where('t.assignedPerson', $user_id)
			->where('t.settingsID', $settingsID)
			->where('t.taskStat', '1')
			->order_by('t.reportedDate', 'DESC')
			->order_by('t.taskID', 'DESC')
			->get()
			->result();
	}

	public function getAccomplishedTaskSummary($settingsID, $month, $year = null)
	{
		$pointsEnabled = $this->db->field_exists('points', 'projects_task_stat');
		$latestClose = '(SELECT taskID, MAX(ptsID) AS ptsID FROM projects_task_stat WHERE taskStat = 0 GROUP BY taskID) latest_close';

		if ($pointsEnabled) {
			$this->db->select('u.fName, u.lName, COALESCE(SUM(pts.points), 0) as total');
		} else {
			$this->db->select('u.fName, u.lName, COUNT(*) as total');
		}
		$this->db->from('projects_task pt');
		$this->db->join($latestClose, 'latest_close.taskID = pt.taskID', 'inner', false);
		$this->db->join('projects_task_stat pts', 'pts.ptsID = latest_close.ptsID');
		$this->db->join('users u', 'u.user_id = pt.assignedPerson');
		$this->db->where('pt.settingsID', $settingsID);
		$this->db->where('pt.taskStat', 0);
		$this->db->where('MONTH(pts.datePosted)', $month);
		if ($year !== null) {
			$this->db->where('YEAR(pts.datePosted)', $year);
		}
		$this->db->group_by('pt.assignedPerson');
		$this->db->order_by('total', 'DESC');

		return $this->db->get()->result();
	}

	public function getTaskRanking($settingsID, $year = null, $month = null)
	{
		$pointsEnabled = $this->db->field_exists('points', 'projects_task_stat');
		$latestClose = '(SELECT taskID, MAX(ptsID) AS ptsID FROM projects_task_stat WHERE taskStat = 0 GROUP BY taskID) latest_close';
		$accomplishedExpr = $pointsEnabled ? 'COALESCE(SUM(pts.points), 0)' : 'COUNT(pts.taskID)';

		$this->db->select([
			'pt.assignedPerson AS user_id',
			"CONCAT(COALESCE(u.fName, ''), ' ', COALESCE(u.lName, '')) AS name",
			'COALESCE(u.position, "") AS role',
			$accomplishedExpr . ' AS accomplished_count',
			'MAX(pts.datePosted) AS last_accomplished'
		]);
		$this->db->from('projects_task pt');
		$this->db->join($latestClose, 'latest_close.taskID = pt.taskID', 'inner', false);
		$this->db->join('projects_task_stat pts', 'pts.ptsID = latest_close.ptsID');
		$this->db->join('users u', 'u.user_id = pt.assignedPerson', 'left');
		$this->db->where('pt.settingsID', $settingsID);
		$this->db->where('pt.taskStat', 0);

		if (!empty($year)) {
			$this->db->where('YEAR(pts.datePosted)', (int) $year);
		}

		if (!empty($month)) {
			$this->db->where('MONTH(pts.datePosted)', (int) $month);
		}

		$this->db->group_by('pt.assignedPerson');
		$this->db->order_by('accomplished_count', 'DESC');
		$this->db->order_by('last_accomplished', 'DESC');

		return $this->db->get()->result();
	}




	//Daily Time Record
	function dtr($settingsID, $id)
	{
		$query = $this->db->query("select * from dtr where settingsID='" . $settingsID . "' and IDNumber='" . $id . "' order by logDate");
		return $query->result();
	}

	//Customer History
	function customerHistory($settingsID, $custID = '', $customer = '')
	{
		$this->paymentBaseQuery();
		$this->db->where('p.settingsID', $settingsID);
		$this->db->where('p.ORStat', 'Valid');
		if ($custID !== '') {
			$this->db->where('COALESCE(p.CustID, c.CustID) = ' . $this->db->escape($custID), null, false);
		} elseif ($customer !== '') {
			$this->db->where('COALESCE(c.Customer, p.Customer) = ' . $this->db->escape($customer), null, false);
		}
		$this->db->order_by('p.PDate', 'DESC');
		$this->db->order_by('p.paymentID', 'DESC');
		return $this->db->get()->result();
	}

	//Receivable Counts
	function receivableCounts($settingsID)
	{
		return $this->buildReceivableCountResult($settingsID);
	}

	function receivableCountsStaff($settingsID)
	{
		return $this->buildReceivableCountResult($settingsID);
	}

	private function buildReceivableCountResult($settingsID)
	{
		$yearStart = date('Y') . '-01-01';
		$asOfDate = date('Y-m-d');

		$invoices = $this->accountingInvoices($settingsID, $yearStart, $asOfDate);
		$payments = $this->accountingInvoicePayments($settingsID, null, $asOfDate);
		$creditByInvoice = array();

		foreach ((array) $payments as $payment) {
			$invoiceNo = trim((string) ($payment->InvoiceNo ?? ''));
			if ($invoiceNo === '') {
				continue;
			}

			$creditByInvoice[$invoiceNo] = (float) ($creditByInvoice[$invoiceNo] ?? 0)
				+ round((float) ($payment->AmountPaid ?? 0), 2)
				+ round((float) ($payment->TaxAmount ?? 0), 2);
		}

		$totalOpenReceivable = 0.0;

		foreach ((array) $invoices as $invoice) {
			$invoiceNo = trim((string) ($invoice->InvoiceNo ?? ''));
			$totalDue = round((float) ($invoice->TotalDue ?? 0), 2);
			$creditApplied = round((float) ($creditByInvoice[$invoiceNo] ?? 0), 2);
			$balance = round(max(0, $totalDue - $creditApplied), 2);

			if ($balance <= 0.00001) {
				continue;
			}

			$totalOpenReceivable += $balance;
		}

		$posSales = $this->accountingPosSales($settingsID, $yearStart, $asOfDate);
		$posPayments = $this->accountingPosPayments($settingsID, null, $asOfDate);
		$paymentsBySale = array();

		foreach ((array) $posPayments as $payment) {
			$saleId = (int) ($payment->sale_id ?? 0);
			if ($saleId <= 0) {
				continue;
			}

			$paymentsBySale[$saleId] = (float) ($paymentsBySale[$saleId] ?? 0)
				+ round((float) ($payment->amount ?? 0), 2);
		}

		foreach ((array) $posSales as $sale) {
			$saleId = (int) ($sale->id ?? 0);
			if ($saleId <= 0) {
				continue;
			}

			$grossAmount = round((float) ($sale->grand_total ?? 0), 2);
			$creditApplied = round((float) ($paymentsBySale[$saleId] ?? 0), 2);
			$balance = round(max(0, $grossAmount - $creditApplied), 2);

			if ($balance <= 0.00001) {
				continue;
			}

			$totalOpenReceivable += $balance;
		}

		return array((object) array(
			'Counts' => round($totalOpenReceivable, 2),
		));
	}

	//Payment List
	function joList($settingsID)
	{
		$this->invoiceBaseQuery($settingsID);
		$this->db->where('i.invoiceStat', 'active');
		$this->db->where('i.invoiceSource', 'Job Order');
		$this->db->order_by('i.InvoiceNo', 'DESC');
		return $this->db->get()->result();
	}

	function joListStaff($settingsID, $name)
	{
		$this->invoiceBaseQuery($settingsID);
		$this->db->where('i.invoiceStat', 'active');
		$this->db->where('i.invoiceSource', 'Job Order');
		$this->applyPersonMatch('i.invoiceBy', $name);
		$this->db->order_by('i.InvoiceNo', 'DESC');
		return $this->db->get()->result();
	}
	function invList($settingsID, $customerFilter = '')
	{
		$this->invoiceBaseQuery($settingsID);
		$this->db->where('i.invoiceStat', 'active');
		$this->db->where_in('i.invoiceSource', ['Others', 'Delivery']);
		$this->applyInvoiceCustomerFilter($customerFilter, 'i');
		$this->db->order_by('i.orderID', 'DESC');
		return $this->db->get()->result();
	}

	function invListStaff($settingsID, $name, $customerFilter = '')
	{
		$this->invoiceBaseQuery($settingsID);
		$this->db->where('i.invoiceStat', 'active');
		$this->db->where_in('i.invoiceSource', ['Others', 'Delivery']);
		$this->applyInvoiceCustomerFilter($customerFilter, 'i');

		// Show all unpaid invoices regardless of encoder, but only paid invoices encoded by staff
		$this->db->group_start()
			->where('i.Balance >', 0)  // Unpaid invoices - show all
			->or_group_start()
			->where('i.Balance <=', 0)  // Paid invoices - only show if encoded by staff
			->group_end()
			->group_end();

		// Apply staff filter for paid invoices
		if (is_array($name)) {
			$matches = array_values(array_filter(array_map(function ($item) {
				return trim((string) $item);
			}, $name), function ($item) {
				return $item !== '';
			}));

			if (!empty($matches)) {
				$this->db->where('(i.Balance > 0 OR i.invoiceBy IN (' . implode(',', array_map([$this->db, 'escape'], $matches)) . '))', null, false);
			}
		} else {
			$value = trim((string) $name);
			if ($value !== '') {
				$this->db->where('(i.Balance > 0 OR i.invoiceBy = ' . $this->db->escape($value) . ')', null, false);
			}
		}

		$this->db->order_by('i.orderID', 'DESC');
		return $this->db->get()->result();
	}

	private function applyInvoiceCustomerFilter($customerFilter, $alias = 'i')
	{
		$customerFilter = trim((string) $customerFilter);
		if ($customerFilter === '') {
			return;
		}

		$this->db->group_start()
			->where("COALESCE({$alias}.CustID, '') =", $customerFilter)
			->or_where("COALESCE(c.CustID, '') =", $customerFilter)
			->group_end();
	}

	function invoiceStatusReportList($settingsID, $name = '')
	{
		$this->invoiceBaseQuery($settingsID);
		$this->db->where('i.invoiceSource', 'Others');
		$this->db->where("COALESCE(i.invoiceStat, '') <> " . $this->db->escape('Deleted'), null, false);
		if ($name !== '') {
			$this->db->where('i.invoiceBy', $name);
		}
		$this->db->order_by('i.InvoiceNo', 'DESC');
		return $this->db->get()->result();
	}

	//Void Invoices List
	function voidInvoicesList($settingsID)
	{
		$this->invoiceBaseQuery($settingsID);
		$this->db->where('i.invoiceStat', 'Voided');
		$this->db->where('i.invoiceSource', 'Others');
		$this->db->order_by('i.voidDate', 'DESC');
		return $this->db->get()->result();
	}

	function voidInvoicesListStaff($settingsID, $name)
	{
		$this->invoiceBaseQuery($settingsID);
		$this->db->where('i.invoiceStat', 'Voided');
		$this->db->where('i.invoiceSource', 'Others');
		$this->applyPersonMatch('i.invoiceBy', $name);
		$this->db->order_by('i.voidDate', 'DESC');
		return $this->db->get()->result();
	}

	function unpaidInvoices($settingsID)
	{
		$this->invoiceBaseQuery($settingsID);
		$this->db->where('i.invoiceStat', 'active');
		$this->db->where('i.Balance >', 0);
		$this->db->order_by('i.InvoiceNo', 'DESC');
		return $this->db->get()->result();
	}

	function unpaidInvoicesStaff($settingsID, $name)
	{
		$this->invoiceBaseQuery($settingsID);
		$this->db->where('i.invoiceStat', 'active');
		$this->applyPersonMatch('i.invoiceBy', $name);
		$this->db->where('i.Balance >', 0);
		$this->db->order_by('i.InvoiceNo', 'DESC');
		return $this->db->get()->result();
	}

	function recurringInvoiceTemplates($settingsID, $frequency = '', $custID = '')
	{
		$this->invoiceBaseQuery($settingsID);
		$this->db->where('i.invoiceStat', 'active');
		$this->db->where_in('i.recurringFrequency', array('daily', 'weekly', 'monthly', 'quarterly', 'yearly'));
		$this->db->group_start();
		$this->db->where('i.recurringTemplateID IS NULL', null, false);
		$this->db->or_where('i.recurringTemplateID', 0);
		$this->db->group_end();
		$this->db->group_start();
		$this->db->where('i.recurringTerminationDate IS NULL', null, false);
		$this->db->or_where('i.recurringTerminationDate >=', date('Y-m-d'));
		$this->db->group_end();
		$this->db->group_start();
		$this->db->where('i.invoiceExpirationDate IS NULL', null, false);
		$this->db->or_where('i.invoiceExpirationDate >=', date('Y-m-d'));
		$this->db->group_end();

		if ($frequency !== '') {
			$this->db->where('i.recurringFrequency', $frequency);
		}

		if ($custID !== '') {
			$this->db->where('COALESCE(i.CustID, c.CustID) = ' . $this->db->escape($custID), null, false);
		}

		$this->db->order_by('i.recurringFrequency', 'ASC');
		$this->db->order_by('i.recurringScheduleDate', 'ASC');
		$this->db->order_by('i.orderID', 'DESC');
		return $this->db->get()->result();
	}

	function recurringGeneratedInvoices($settingsID, $templateOrderIDs = array())
	{
		$this->invoiceBaseQuery($settingsID);
		$this->db->where('i.invoiceStat', 'active');
		$this->db->where('i.invoiceSource', 'Others');
		$this->db->where('i.recurringTemplateID >', 0);

		$templateOrderIDs = array_values(array_unique(array_filter(array_map('intval', (array) $templateOrderIDs), function ($orderID) {
			return $orderID > 0;
		})));
		if (!empty($templateOrderIDs)) {
			$this->db->where_in('i.recurringTemplateID', $templateOrderIDs);
		}

		$this->db->order_by('i.recurringScheduleDate', 'DESC');
		$this->db->order_by('i.orderID', 'DESC');
		return $this->db->get()->result();
	}

	function recurringTerminatedInvoices($settingsID, $frequency = '', $custID = '')
	{
		$this->invoiceBaseQuery($settingsID);
		$this->db->where('i.invoiceStat', 'active');
		$this->db->where_in('i.recurringFrequency', array('daily', 'weekly', 'monthly', 'quarterly', 'yearly'));
		$this->db->group_start();
		$this->db->where('i.recurringTemplateID IS NULL', null, false);
		$this->db->or_where('i.recurringTemplateID', 0);
		$this->db->group_end();
		// Only get invoices that are terminated (termination date is in the past)
		$this->db->where('i.recurringTerminationDate IS NOT NULL', null, false);
		$this->db->where('i.recurringTerminationDate <', date('Y-m-d'));

		if ($frequency !== '') {
			$this->db->where('i.recurringFrequency', $frequency);
		}

		if ($custID !== '') {
			$this->db->where('COALESCE(i.CustID, c.CustID) = ' . $this->db->escape($custID), null, false);
		}

		$this->db->order_by('i.recurringTerminationDate', 'DESC');
		$this->db->order_by('i.recurringFrequency', 'ASC');
		$this->db->order_by('i.orderID', 'DESC');
		return $this->db->get()->result();
	}

	function getClients($settingsID)
	{
		$query = $this->db->query("select * from customers where settingsID='" . $settingsID . "' order by Customer");
		return $query->result();
	}

	function getClientsByStatus($settingsID, $status)
	{
		$query = $this->db->query("select * from customers where settingsID='" . $settingsID . "' and ClientStat='" . $status . "' order by Customer");
		return $query->result();
	}

	//Invoice No
	function joInvoiceNo($settingsID)
	{
		$query = $this->db->query("select * from invoice where settingsID='" . $settingsID . "' order by orderID desc limit 1");
		return $query->result();
	}

	function joInvoiceNoStaff($settingsID, $name)
	{
		$this->db->from('invoice');
		$this->db->where('settingsID', $settingsID);
		$this->db->where('invoiceStat', 'active');
		$this->db->where('invoiceSource', 'Others');

		// Apply staff filter for paid invoices only (same logic as invListStaff)
		if (is_array($name)) {
			$matches = array_values(array_filter(array_map(function ($item) {
				return trim((string) $item);
			}, $name), function ($item) {
				return $item !== '';
			}));

			if (!empty($matches)) {
				$this->db->where('(Balance > 0 OR invoiceBy IN (' . implode(',', array_map([$this->db, 'escape'], $matches)) . '))', null, false);
			}
		} else {
			$value = trim((string) $name);
			if ($value !== '') {
				$this->db->where('(Balance > 0 OR invoiceBy = ' . $this->db->escape($value) . ')', null, false);
			}
		}

		$this->db->order_by('orderID', 'DESC');
		$this->db->limit(1);
		return $this->db->get()->result();
	}

	function joInvoiceNoPayment($id, $settingsID)
	{
		$record = $this->getInvoiceByOrderID($id, $settingsID);
		return $record ? [$record] : [];
	}

	function getInvoiceByOrderID($orderID, $settingsID)
	{
		$this->invoiceBaseQuery($settingsID);
		$this->db->where('i.orderID', $orderID);
		return $this->db->get()->row();
	}

	function getJobOrderByID($orderID, $settingsID)
	{
		$this->invoiceBaseQuery($settingsID);
		$this->db->where('i.orderID', $orderID);
		return $this->db->get()->row();
	}

	function getInvoiceByInvoiceNo($invoiceNo, $settingsID)
	{
		$this->invoiceBaseQuery($settingsID);
		$this->db->where('i.InvoiceNo', $invoiceNo);
		$this->db->order_by('i.orderID', 'DESC');
		return $this->db->get()->row();
	}

	function getInvoiceItems($orderID, $settingsID)
	{
		if (!$this->db->table_exists('invoice_items')) {
			return [];
		}

		return $this->db
			->order_by('lineNo', 'ASC')
			->order_by('itemID', 'ASC')
			->get_where('invoice_items', array(
				'orderID' => (int) $orderID,
				'settingsID' => $settingsID,
			))
			->result_array();
	}

	function getInvoiceItemsByOrderIDs($orderIDs, $settingsID)
	{
		if (!$this->db->table_exists('invoice_items')) {
			return [];
		}

		$orderIDs = array_values(array_unique(array_filter(array_map('intval', (array) $orderIDs), function ($orderID) {
			return $orderID > 0;
		})));

		if (empty($orderIDs)) {
			return [];
		}

		$rows = $this->db
			->from('invoice_items')
			->where('settingsID', $settingsID)
			->where_in('orderID', $orderIDs)
			->order_by('orderID', 'ASC')
			->order_by('lineNo', 'ASC')
			->order_by('itemID', 'ASC')
			->get()
			->result_array();

		$grouped = [];
		foreach ($rows as $row) {
			$orderID = (int) ($row['orderID'] ?? 0);
			if ($orderID <= 0) {
				continue;
			}

			if (!isset($grouped[$orderID])) {
				$grouped[$orderID] = [];
			}

			$grouped[$orderID][] = $row;
		}

		return $grouped;
	}

	function replaceInvoiceItems($orderID, $settingsID, $items)
	{
		if (!$this->db->table_exists('invoice_items')) {
			return;
		}

		$orderID = (int) $orderID;
		if ($orderID <= 0) {
			return;
		}

		$this->db
			->where('orderID', $orderID)
			->where('settingsID', $settingsID)
			->delete('invoice_items');

		$lineNo = 1;
		foreach ((array) $items as $item) {
			$description = trim((string) ($item['itemDescription'] ?? ''));
			if ($description === '') {
				continue;
			}

			$this->db->insert('invoice_items', array(
				'orderID' => $orderID,
				'settingsID' => $settingsID,
				'lineNo' => $lineNo,
				'itemDescription' => $description,
				'itemQuantity' => isset($item['itemQuantity']) ? (int) $item['itemQuantity'] : 1,
				'itemDurationUnit' => isset($item['itemDurationUnit']) ? (string) $item['itemDurationUnit'] : 'each',
				'itemUnitPrice' => isset($item['itemUnitPrice']) ? (float) $item['itemUnitPrice'] : 0,
				'lineTotal' => isset($item['lineTotal']) ? (float) $item['lineTotal'] : 0,
			));
			$lineNo++;
		}
	}

	function getClientByCustID($settingsID, $custID)
	{
		return $this->db
			->order_by('CustID', 'DESC')
			->get_where('customers', array(
				'settingsID' => $settingsID,
				'CustID' => $custID,
			), 1)
			->row();
	}

	function getClientByName($settingsID, $customer)
	{
		return $this->db
			->order_by('CustID', 'DESC')
			->get_where('customers', array(
				'settingsID' => $settingsID,
				'Customer' => $customer,
			), 1)
			->row();
	}

	function joInvoiceNoPaymentTotal($InvoiceNo, $settingsID, $custID = '', $customer = null)
	{
		$this->db->select('SUM(AmountPaid + COALESCE(TaxAmount, 0)) as TotalPayments', false);
		$this->db->from('payments p');
		$this->db->join('customers c', $this->paymentCustomerJoin('p'), 'left');
		$this->db->where('p.InvoiceNo', $InvoiceNo);
		$this->db->where('p.settingsID', $settingsID);
		$this->db->where('p.ORStat', 'Valid');
		if ($custID !== '') {
			$this->db->where('COALESCE(p.CustID, c.CustID) = ' . $this->db->escape($custID), null, false);
		} elseif ($customer !== null && $customer !== '') {
			$this->db->where('COALESCE(c.Customer, p.Customer) = ' . $this->db->escape($customer), null, false);
		}
		$this->db->group_by('p.InvoiceNo');
		return $this->db->get()->result();
	}

	//Payment List
	function paymentList($settingsID)
	{
		$this->paymentBaseQuery();
		$this->db->where('p.ORStat', 'valid');
		$this->db->where('p.settingsID', $settingsID);
		$this->db->order_by('p.PDate', 'DESC');
		$this->db->order_by('p.paymentID', 'DESC');
		return $this->db->get()->result();
	}

	function paymentListStaff($settingsID, $name)
	{
		$this->paymentBaseQuery();
		$this->db->where('p.ORStat', 'valid');
		$this->db->where('p.settingsID', $settingsID);
		$this->db->where('p.Cashier', $name);
		$this->db->where('DATE(p.PDate)=DATE(NOW())', null, false);
		$this->db->order_by('p.PDate', 'DESC');
		$this->db->order_by('p.paymentID', 'DESC');
		return $this->db->get()->result();
	}

	function paymentListByMonthYear($settingsID, $month, $year)
	{
		$month = (int) $month;
		$year = (int) $year;

		$this->paymentBaseQuery();
		$this->db->where('p.ORStat', 'valid');
		$this->db->where('p.settingsID', $settingsID);
		$this->db->where('MONTH(p.PDate)', $month);
		$this->db->where('YEAR(p.PDate)', $year);
		$this->db->order_by('p.PDate', 'DESC');
		$this->db->order_by('p.paymentID', 'DESC');
		return $this->db->get()->result();
	}

	function paymentListStaffByMonthYear($settingsID, $name, $month, $year)
	{
		$month = (int) $month;
		$year = (int) $year;

		$this->paymentBaseQuery();
		$this->db->where('p.ORStat', 'valid');
		$this->db->where('p.settingsID', $settingsID);
		$this->db->where('p.Cashier', $name);
		$this->db->where('MONTH(p.PDate)', $month);
		$this->db->where('YEAR(p.PDate)', $year);
		$this->db->order_by('p.PDate', 'DESC');
		$this->db->order_by('p.paymentID', 'DESC');
		return $this->db->get()->result();
	}


	function paymentListRange($from, $to, $settingsID)
	{
		$this->paymentBaseQuery();
		$this->db->where('p.ORStat', 'valid');
		$this->db->where('p.PDate >=', $from);
		$this->db->where('p.PDate <=', $to);
		$this->db->where('p.settingsID', $settingsID);
		$this->db->order_by('p.PDate', 'DESC');
		$this->db->order_by('p.paymentID', 'DESC');
		return $this->db->get()->result();
	}

	function paymentListRangeStaff($settingsID, $name, $from = null, $to = null)
	{
		$this->paymentBaseQuery();
		$this->db->where('p.ORStat', 'valid');
		$this->db->where('p.settingsID', $settingsID);
		$this->db->where('p.Cashier', $name);

		if ($from !== null && $to !== null) {
			$this->db->where('p.PDate >=', $from);
			$this->db->where('p.PDate <=', $to);
		} else {
			$this->db->where('DATE(p.PDate)=CURDATE()', null, false);
		}

		$this->db->order_by('p.PDate', 'DESC');
		$this->db->order_by('p.paymentID', 'DESC');
		return $this->db->get()->result();
	}

	//Void Payments List
	function voidPaymentsList($settingsID)
	{
		$this->paymentBaseQuery();
		$this->db->where('p.ORStat', 'Voided');
		$this->db->where('p.settingsID', $settingsID);
		$this->db->order_by('p.voidDate', 'DESC');
		return $this->db->get()->result();
	}

	function voidPaymentsListStaff($settingsID, $name)
	{
		$this->paymentBaseQuery();
		$this->db->where('p.ORStat', 'Voided');
		$this->db->where('p.settingsID', $settingsID);
		$this->db->where('p.Cashier', $name);
		$this->db->order_by('p.voidDate', 'DESC');
		return $this->db->get()->result();
	}

	function taxPaymentEntries($settingsID, $from = null, $to = null)
	{
		$this->paymentBaseQuery();
		$this->db->where('p.ORStat', 'Valid');
		$this->db->where('p.settingsID', $settingsID);
		$this->db->where('COALESCE(p.TaxAmount, 0) > 0', null, false);

		if ($from !== null && $to !== null) {
			$this->db->where('p.PDate >=', $from);
			$this->db->where('p.PDate <=', $to);
		}

		$this->db->order_by('p.PDate', 'DESC');
		$this->db->order_by('p.paymentID', 'DESC');
		return $this->db->get()->result();
	}

	function acceptedPaymentReportEntries($settingsID, $from = null, $to = null, $paymentSource = '', $cashier = '', $custID = '')
	{
		$this->paymentBaseQuery();
		$this->db->where('LOWER(p.ORStat) =', 'valid');
		$this->db->where('p.settingsID', $settingsID);
		$this->db->order_by('p.PDate', 'ASC');
		$this->db->order_by('p.paymentID', 'ASC');

		if ($from) {
			$this->db->where('p.PDate >=', $from);
		}
		if ($to) {
			$this->db->where('p.PDate <=', $to);
		}
		if ($paymentSource) {
			$this->db->where('LOWER(p.TransDescription) =', strtolower($paymentSource));
		}
		if ($cashier) {
			$this->db->where('LOWER(p.Cashier) =', strtolower($cashier));
		}
		if ($custID) {
			$this->db->where('c.CustID', $custID);
		}

		$regularPayments = $this->db->get()->result();

		// Get delivery payments
		$this->db->select('d.deliveryNo as InvoiceNo, d.customerName as Customer, d.amountPaid as AmountPaid, d.updatedAt as PDate, d.deliveredBy as Cashier, "Delivery Payment" as TransDescription, "Valid" as ORStat, 0 as TaxAmount');
		$this->db->from('customer_deliveries d');
		$this->db->where('d.settingsID', $settingsID);
		$this->db->where('d.amountPaid >', 0);
		$this->db->order_by('d.updatedAt', 'ASC');

		if ($from) {
			$this->db->where('DATE(d.updatedAt) >=', $from);
		}
		if ($to) {
			$this->db->where('DATE(d.updatedAt) <=', $to);
		}
		if ($paymentSource && strtolower($paymentSource) !== 'delivery payment') {
			$this->db->where('1 = 0'); // Exclude delivery payments if looking for specific payment source
		}

		$deliveryPayments = $this->db->get()->result();

		// Combine both payment types
		return array_merge($regularPayments, $deliveryPayments);
	}

	function accountingInvoices($settingsID, $from = null, $to = null)
	{
		$this->invoiceBaseQuery($settingsID);
		$this->applyOperationalInvoiceStatusFilter('i');

		if ($from !== null) {
			$this->db->where('i.TransDate >=', $from);
		}

		if ($to !== null) {
			$this->db->where('i.TransDate <=', $to);
		}

		$this->db->order_by('i.TransDate', 'DESC');
		$this->db->order_by('i.InvoiceNo', 'DESC');
		return $this->db->get()->result();
	}

	function accountingInvoicePayments($settingsID, $from = null, $to = null)
	{
		$this->paymentBaseQuery();
		$this->db->where('LOWER(p.ORStat) =', 'valid');
		$this->db->where('p.settingsID', $settingsID);

		if ($from !== null) {
			$this->db->where('p.PDate >=', $from);
		}

		if ($to !== null) {
			$this->db->where('p.PDate <=', $to);
		}

		$this->db->order_by('p.PDate', 'DESC');
		$this->db->order_by('p.paymentID', 'DESC');
		return $this->db->get()->result();
	}

	function accountingExpenses($settingsID, $from = null, $to = null)
	{
		$this->db->from('expenses');
		$this->db->where('settingsID', $settingsID);

		if ($from !== null) {
			$this->db->where('ExpenseDate >=', $from);
		}

		if ($to !== null) {
			$this->db->where('ExpenseDate <=', $to);
		}

		$this->db->order_by('ExpenseDate', 'DESC');
		$this->db->order_by('expensesid', 'DESC');
		return $this->db->get()->result();
	}

	function accountingPosSales($settingsID, $from = null, $to = null)
	{
		if (!$this->db->table_exists('pos_sales')) {
			return array();
		}

		$this->db->from('pos_sales');
		$this->db->where('settingsID', $settingsID);
		$this->db->where('status !=', 'Voided');

		if ($from !== null) {
			$this->db->where('transaction_date >=', $from);
		}

		if ($to !== null) {
			$this->db->where('transaction_date <=', $to);
		}

		$this->db->order_by('transaction_date', 'DESC');
		$this->db->order_by('id', 'DESC');
		return $this->db->get()->result();
	}

	function accountingPosPayments($settingsID, $from = null, $to = null)
	{
		if (!$this->db->table_exists('pos_payments') || !$this->db->table_exists('pos_sales')) {
			return array();
		}

		$this->db
			->select('p.*, s.sale_no, s.customer_name, s.payment_term, s.status AS sale_status, s.transaction_date', false)
			->from('pos_payments p')
			->join('pos_sales s', 's.id = p.sale_id AND s.settingsID = p.settingsID', 'inner')
			->where('p.settingsID', $settingsID)
			->where('s.status !=', 'Voided');

		if ($from !== null) {
			$this->db->where('p.payment_date >=', $from);
		}

		if ($to !== null) {
			$this->db->where('p.payment_date <=', $to);
		}

		return $this->db
			->order_by('p.payment_date', 'DESC')
			->order_by('p.id', 'DESC')
			->get()
			->result();
	}

	function paymentSources($settingsID)
	{
		return $this->db
			->distinct()
			->select('PaymentSource')
			->from('payments')
			->where('settingsID', $settingsID)
			->where('LOWER(ORStat) =', 'valid')
			->where('TRIM(COALESCE(PaymentSource, "")) <> ""', null, false)
			->order_by('PaymentSource', 'ASC')
			->get()
			->result();
	}

	function paymentCashiers($settingsID)
	{
		return $this->db
			->distinct()
			->select('Cashier')
			->from('payments')
			->where('settingsID', $settingsID)
			->where('LOWER(ORStat) =', 'valid')
			->where('TRIM(COALESCE(Cashier, "")) <> ""', null, false)
			->order_by('Cashier', 'ASC')
			->get()
			->result();
	}

	function totalPaymentsRange($from, $to, $settingsID)
	{
		$query = $this->db->query("SELECT sum(AmountPaid) as Total FROM payments where ORStat='Valid' and PDate>='" . $from . "' and PDate<='" . $to . "' and settingsID='" . $settingsID . "' order by PDate desc");
		return $query->result();
	}

	function totalPaymentsRangeStaff($settingsID, $name, $from = null, $to = null)
	{
		$this->db->select('SUM(AmountPaid) as Total');
		$this->db->from('payments');
		$this->db->where('ORStat', 'Valid');
		$this->db->where('settingsID', $settingsID);
		$this->db->where('Cashier', $name);

		if ($from !== null && $to !== null) {
			$this->db->where('PDate >=', $from);
			$this->db->where('PDate <=', $to);
		} else {
			$this->db->where('DATE(PDate)=CURDATE()', null, false);
		}

		return $this->db->get()->result();
	}


	function paymentHistory($invoiceNo, $settingsID, $custID = '', $customer = null)
	{
		$this->paymentBaseQuery();
		$this->db->where('p.InvoiceNo', $invoiceNo);
		$this->db->where('p.settingsID', $settingsID);
		$this->db->where('p.ORStat', 'Valid');
		if ($custID !== '') {
			$this->db->where('COALESCE(p.CustID, c.CustID) = ' . $this->db->escape($custID), null, false);
		} elseif ($customer !== null && $customer !== '') {
			$this->db->where('COALESCE(c.Customer, p.Customer) = ' . $this->db->escape($customer), null, false);
		}
		$this->db->order_by('p.PDate', 'DESC');
		$this->db->order_by('p.paymentID', 'DESC');
		return $this->db->get()->result();
	}

	function clientPayments($settingsID, $custID = '', $customer = '')
	{
		$this->paymentBaseQuery();
		$this->db->where('p.settingsID', $settingsID);
		$this->db->where('p.ORStat', 'Valid');
		if ($custID !== '') {
			$this->db->where('COALESCE(p.CustID, c.CustID) = ' . $this->db->escape($custID), null, false);
		} elseif ($customer !== '') {
			$this->db->where('COALESCE(c.Customer, p.Customer) = ' . $this->db->escape($customer), null, false);
		}
		$this->db->order_by('p.PDate', 'DESC');
		$this->db->order_by('p.paymentID', 'DESC');
		return $this->db->get()->result();
	}

	function topClientsByPayments($settingsID, $dateFrom = null, $dateTo = null, $limit = 20)
	{
		$this->db->select([
			'COALESCE(p.CustID, c.CustID) AS CustID',
			'COALESCE(c.Customer, p.Customer) AS Customer',
			'COUNT(p.paymentID) AS paymentCount',
			'SUM(p.AmountPaid) AS totalAmountPaid',
			'SUM(COALESCE(p.TaxAmount, 0)) AS totalTaxAmount',
			'SUM(p.AmountPaid + COALESCE(p.TaxAmount, 0)) AS totalGrossAmount',
			'MIN(p.PDate) AS firstPaymentDate',
			'MAX(p.PDate) AS lastPaymentDate',
		], false);
		$this->db->from('payments p');
		$this->db->join('customers c', $this->paymentCustomerJoin('p'), 'left');
		$this->db->where('p.settingsID', $settingsID);
		$this->db->where('p.ORStat', 'Valid');

		if ($dateFrom !== null && $dateFrom !== '') {
			$this->db->where('p.PDate >=', $dateFrom);
		}
		if ($dateTo !== null && $dateTo !== '') {
			$this->db->where('p.PDate <=', $dateTo);
		}

		$this->db->group_by(['COALESCE(p.CustID, c.CustID)', 'COALESCE(c.Customer, p.Customer)']);
		$this->db->order_by('totalAmountPaid', 'DESC');
		$this->db->limit((int) $limit);

		return $this->db->get()->result();
	}

	//Payment List
	function paymentListYear($year)
	{
		$this->paymentBaseQuery();
		$this->db->where('p.ORStat', 'valid');
		$this->db->where('YEAR(p.PDate)', $year);
		$this->db->order_by('p.PDate', 'DESC');
		$this->db->order_by('p.paymentID', 'DESC');
		return $this->db->get()->result();
	}

	//Latest Payment
	function latestPayment()
	{
		$this->paymentBaseQuery();
		$this->db->where('p.ORStat', 'valid');
		$this->db->order_by('p.paymentID', 'DESC');
		$this->db->limit(5);
		return $this->db->get()->result();
	}

	//totalClients
	function totalClients($settingsID)
	{
		$query = $this->db->query("SELECT count(Customer) as Total FROM customers WHERE settingsID='" . $settingsID . "'");
		return $query->result();
	}

	//Todays Payments
	function todaysPayments($settingsID, $date)
	{
		// Regular payments
		$query = $this->db->query("SELECT sum(AmountPaid) as Total FROM payments where settingsID='" . $settingsID . "' and ORStat='Valid' and PDate='" . $date . "'");
		$regularPayments = $query->result();

		// Delivery payments
		$deliveryQuery = $this->db->query("SELECT sum(amountPaid) as Total FROM customer_deliveries where settingsID='" . $settingsID . "' and DATE(updatedAt)='" . $date . "' and amountPaid > 0");
		$deliveryPayments = $deliveryQuery->result();

		// Combine results
		$total = ($regularPayments[0]->Total ?? 0) + ($deliveryPayments[0]->Total ?? 0);

		return [(object)['Total' => $total]];
	}

	function todaysPaymentsStaff($settingsID, $name)
	{
		$query = $this->db->query("SELECT sum(AmountPaid) as Total FROM payments where settingsID='" . $settingsID . "' and ORStat='Valid' and Cashier='" . $name . "' and DATE(PDate)=DATE(NOW())");
		return $query->result();
	}


	function totalPaymentsStaff($settingsID, $name)
	{
		$query = $this->db->query("SELECT sum(AmountPaid) as Total FROM payments where settingsID='" . $settingsID . "' and Cashier='" . $name . "' and ORStat='Valid' and DATE(PDate)=DATE(NOW())");
		return $query->result();
	}

	function totalPaymentsByMonthYear($settingsID, $month, $year)
	{
		$month = (int) $month;
		$year = (int) $year;

		$sql = "SELECT sum(AmountPaid) as Total FROM payments where ORStat='Valid' and settingsID=? and MONTH(PDate)=? and YEAR(PDate)=?";
		$query = $this->db->query($sql, array($settingsID, $month, $year));
		return $query->result();
	}

	function totalPaymentsStaffByMonthYear($settingsID, $name, $month, $year)
	{
		$month = (int) $month;
		$year = (int) $year;

		$sql = "SELECT sum(AmountPaid) as Total FROM payments where ORStat='Valid' and settingsID=? and Cashier=? and MONTH(PDate)=? and YEAR(PDate)=?";
		$query = $this->db->query($sql, array($settingsID, $name, $month, $year));
		return $query->result();
	}


	//TotalPayments
	function totalPayments($settingsID)
	{
		// Regular payments
		$query = $this->db->query("SELECT sum(AmountPaid) as Total FROM payments where ORStat='Valid' and settingsID='" . $settingsID . "'");
		$regularPayments = $query->result();

		// Delivery payments
		$deliveryQuery = $this->db->query("SELECT sum(amountPaid) as Total FROM customer_deliveries where settingsID='" . $settingsID . "' and amountPaid > 0");
		$deliveryPayments = $deliveryQuery->result();

		// Combine results
		$total = ($regularPayments[0]->Total ?? 0) + ($deliveryPayments[0]->Total ?? 0);

		return [(object)['Total' => $total]];
	}

	//TotalPayments Summary
	function totalPaymentsSummary($settingsID)
	{
		$query = $this->db->query("SELECT TransDescription, sum(AmountPaid) as Total FROM payments where ORStat='Valid' and settingsID='" . $settingsID . "' group by TransDescription");
		return $query->result();
	}

	//TotalPayments Summary
	function totalPaymentsRangeStaffDate($settingsID, $name)
	{
		$query = $this->db->query("SELECT TransDescription, sum(AmountPaid) as Total FROM payments where ORStat='Valid' and settingsID='" . $settingsID . "' and DATE(PDate)=CURDATE() and Cashier='" . $name . "' group by TransDescription");
		return $query->result();
	}


	function totalPaymentsPerCashier($from, $to, $settingsID)
	{
		$query = $this->db->query("SELECT Cashier, sum(AmountPaid) as Total FROM payments where ORStat='Valid' and settingsID='" . $settingsID . "' and PDate>='" . $from . "' and PDate<='" . $to . "' group by Cashier ");
		return $query->result();
	}


	function totalPaymentsRangeDate1($from, $to, $settingsID)
	{
		$query = $this->db->query("SELECT TransDescription, sum(AmountPaid) as Total FROM payments where ORStat='Valid' and settingsID='" . $settingsID . "' and PDate>='" . $from . "' and PDate<='" . $to . "' group by TransDescription");
		return $query->result();
	}

	//TotalPayments Summary
	function totalPaymentsRangeDate($settingsID)
	{
		$query = $this->db->query("SELECT TransDescription, sum(AmountPaid) as Total FROM payments where ORStat='Valid' and settingsID='" . $settingsID . "' and DATE(PDate)=CURDATE() group by TransDescription");
		return $query->result();
	}


	//TotalPayments
	function totalPaymentsYear($year)
	{
		$query = $this->db->query("SELECT sum(AmountPaid) as Total FROM payments where ORStat='Valid' and YEAR(PDate)='" . $year . "'");
		return $query->result();
	}

	//Expense List
	function expensesList($settingsID)
	{
		$query = $this->db->query("select * from expenses where YEAR(ExpenseDate)=YEAR(NOW()) and settingsID='" . $settingsID . "' order by ExpenseDate desc");
		return $query->result();
	}
	function expensesListStaff($settingsID, $name)
	{
		$query = $this->db->query("select * from expenses where YEAR(ExpenseDate)=YEAR(NOW()) and settingsID='" . $settingsID . "' and processedBy='" . $name . "' order by ExpenseDate desc");
		return $query->result();
	}

	function expensesListRange($from, $to, $settingsID)
	{
		$query = $this->db->query("select * from expenses where ExpenseDate>='" . $from . "' and ExpenseDate<='" . $to . "' and settingsID='" . $settingsID . "' order by ExpenseDate desc");
		return $query->result();
	}

	function expensesListRangeStaff($from, $to, $settingsID, $name)
	{
		$query = $this->db->query("select * from expenses where ExpenseDate>='" . $from . "' and ExpenseDate<='" . $to . "' and settingsID='" . $settingsID . "' and processedBy='" . $name . "' order by ExpenseDate desc");
		return $query->result();
	}

	//Total Expenses
	function totalExpensesRange($from, $to, $settingsID)
	{
		$query = $this->db->query("SELECT sum(Amount) as Total FROM expenses where ExpenseDate>='" . $from . "' and ExpenseDate<='" . $to . "' and settingsID='" . $settingsID . "'");
		return $query->result();
	}

	function totalExpensesRangeStaff($from, $to, $settingsID, $name)
	{
		$query = $this->db->query("SELECT sum(Amount) as Total FROM expenses where ExpenseDate>='" . $from . "' and ExpenseDate<='" . $to . "' and settingsID='" . $settingsID . "' and processedBy='" . $name . "'");
		return $query->result();
	}

	//Expense Edit
	function updateProject($id)
	{
		$query = $this->db->query("select * from projects where projectID='" . $id . "'");
		return $query->result();
	}

	//Expense Edit
	function updateExpenses($id)
	{
		$query = $this->db->query("select * from expenses where expensesid='" . $id . "'");
		return $query->result();
	}

	function getExpenseCategories($settingsID)
	{
		if (!$this->db->table_exists('expensescategory')) {
			return array();
		}

		return $this->db
			->select('categoryID, Category')
			->from('expensescategory')
			->where('settingsID', $settingsID)
			->where("TRIM(COALESCE(Category, '')) <> ''", null, false)
			->order_by('Category', 'ASC')
			->get()
			->result();
	}

	//Edit Payment
	function updatePayment($id)
	{
		$this->paymentBaseQuery();
		$this->db->where('p.paymentID', $id);
		$result = $this->db->get()->row();
		return $result ? [$result] : [];
	}

	function updateJO($id)
	{
		$this->invoiceBaseQuery();
		$this->db->where('i.orderID', $id);
		$result = $this->db->get()->row();
		return $result ? [$result] : [];
	}

	function updateProduct($id)
	{
		$query = $this->db->query("select * from items where itemID='" . $id . "'");
		return $query->result();
	}

	//Update Client
	function updateClient($id)
	{
		$query = $this->db->query("select * from customers where CustID='" . $id . "'");
		return $query->result();
	}

	//Project Summary
	function projectSummary()
	{
		$query = $this->db->query("SELECT project, count(project) as pCounts FROM projects_task where taskStat='Open' group by project");
		return $query->result();
	}

	public function countOpenTasks($settingsID)
	{
		$this->db->select('COUNT(*) as count');
		$this->db->from('projects_task');
		$this->db->where('taskStat', 1);
		$this->db->where('settingsID', $settingsID);
		$result = $this->db->get()->row();
		return (int) $result->count;
	}

	public function countClosedTasks($settingsID)
	{
		$this->db->select('COUNT(*) as count');
		$this->db->from('projects_task');
		$this->db->where('taskStat', '0');
		$this->db->where('settingsID', $settingsID);
		$result = $this->db->get()->row();
		return (int) $result->count;
	}

	public function countOpenTasksStaff($settingsID, $name)
	{
		$this->db->select('COUNT(*) as count');
		$this->db->from('projects_task');
		$this->db->where('taskStat', 1);
		$this->db->where('settingsID', $settingsID);
		$this->db->where('assignedPerson', $name);
		$result = $this->db->get()->row();
		return (int) $result->count;
	}

	public function countClosedTasksStaff($settingsID, $name)
	{
		$this->db->select('COUNT(*) as count');
		$this->db->from('projects_task');
		$this->db->where('taskStat', '0');
		$this->db->where('settingsID', $settingsID);
		$this->db->where('assignedPerson', $name);
		$result = $this->db->get()->row();
		return (int) $result->count;
	}





	//Update Employee
	function updateEmployee($id)
	{
		$query = $this->db->query("select * from employee where empID='" . $id . "'");
		return $query->result();
	}

	//Today Expenses
	function todayExpenses($settingsID, $date)
	{
		$query = $this->db->query("SELECT sum(Amount) as Total FROM expenses where settingsID='" . $settingsID . "' and ExpenseDate='" . $date . "'");
		return $query->result();
	}
	//Today Expenses
	function todayExpensesStaff($settingsID, $name)
	{
		$query = $this->db->query("SELECT sum(Amount) as Total FROM expenses where settingsID='" . $settingsID . "' and processedBy='" . $name . "' and DATE(ExpenseDate)=DATE(NOW())");
		return $query->result();
	}

	//Total Expenses
	function totalExpenses($settingsID)
	{
		$query = $this->db->query("SELECT sum(Amount) as Total FROM expenses where settingsID='" . $settingsID . "'");
		return $query->result();
	}

	function totalExpensesStaff($settingsID, $name)
	{
		$query = $this->db->query("SELECT sum(Amount) as Total FROM expenses where settingsID='" . $settingsID . "' and processedBy='" . $name . "'");
		return $query->result();
	}


	public function projectList($settingsID)
	{
		return $this->db
			->select('p.*, c.Customer AS customer_from_clients')
			->from('projects p')
			->join('customers c', 'c.CustID = p.CustID AND c.settingsID = p.settingsID', 'left')
			->where('p.settingsID', $settingsID)
			->order_by('p.projectID', 'DESC')
			->get()
			->result();
	}


	function deliveryList($settingsID, $id)
	{
		$query = $this->db->query("select * from delivery where settingsID='" . $settingsID . "' and itemID='" . $id . "'");
		return $query->result();
	}

	//Product List
	function productList($settingsID)
	{
		$query = $this->db->query("select * from items where settingsID='" . $settingsID . "' order by productName");
		return $query->result();
	}

	//Client List
	function clientList($settingsID)
	{
		$query = $this->db->query("select * from customers where settingsID='" . $settingsID . "' order by Customer");
		return $query->result();
	}

	function clientInvoices($settingsID, $custID = '', $customer = '')
	{
		$this->invoiceBaseQuery($settingsID);
		$this->db->where('i.invoiceStat', 'active');
		if ($custID !== '') {
			$this->db->where('COALESCE(i.CustID, c.CustID) = ' . $this->db->escape($custID), null, false);
		} elseif ($customer !== '') {
			$this->db->where('COALESCE(c.Customer, i.Customer) = ' . $this->db->escape($customer), null, false);
		}
		$this->db->order_by('i.TransDate', 'DESC');
		$this->db->order_by('i.orderID', 'DESC');
		return $this->db->get()->result();
	}


	function noteList($user, $settingsID)
	{
		$query = $this->db->query("select * from notes where notedBy='" . $user . "' and settingsID='" . $settingsID . "' and noteStat='Active' order by noteDate DESC");
		return $query->result();
	}

	function priceList($settingsID)
	{
		if (!$this->db->table_exists('service_fees')) {
			return [];
		}

		$this->db->from('service_fees');
		$this->db->where('settingsID', $settingsID);
		$this->db->order_by('FeesDescription', 'ASC');
		if ($this->db->field_exists('subCategory', 'service_fees')) {
			$this->db->order_by('subCategory', 'ASC');
		}
		$this->db->order_by('feeDetails', 'ASC');
		$this->db->order_by('Amount', 'ASC');

		return $this->db->get()->result();
	}

	function priceListProduct($settingsID)
	{
		$query = $this->db->query("SELECT * FROM items i join delivery d on i.itemID=d.itemID   where d.settingsID='" . $settingsID . "'");
		return $query->result();
	}

	//Client List
	function employeeList($settingsID, $statusFilter = 'Active')
	{
		$statusFilter = trim((string) $statusFilter);
		
		$whereClause = "e.settingsID='" . $this->db->escape_str($settingsID) . "'";
		
		if ($statusFilter !== '' && strtolower($statusFilter) !== 'all') {
			$whereClause .= " AND e.empStat='" . $this->db->escape_str($statusFilter) . "'";
		}
		
		$query = $this->db->query("
			SELECT e.*, u.user_id
			FROM employee e
			LEFT JOIN users u ON u.email = e.email AND u.settingsID = e.settingsID
			WHERE " . $whereClause . "
			ORDER BY e.lName
		");
		return $query->result();
	}

	//Annual Goals
	function getAnnualGoals($settingsID)
	{
		return $this->db
			->where('settingsID', $settingsID)
			->order_by('goalYear', 'DESC')
			->get('annual_goals')
			->result();
	}

	function getAnnualGoalByYear($settingsID, $year)
	{
		return $this->db
			->where('settingsID', $settingsID)
			->where('goalYear', $year)
			->get('annual_goals')
			->row();
	}

	function saveAnnualGoal($data)
	{
		$existing = $this->getAnnualGoalByYear($data['settingsID'], $data['goalYear']);
		if ($existing) {
			$this->db->where('goalID', $existing->goalID);
			return $this->db->update('annual_goals', $data);
		}
		return $this->db->insert('annual_goals', $data);
	}

	function deleteAnnualGoal($goalID, $settingsID)
	{
		return $this->db
			->where('goalID', $goalID)
			->where('settingsID', $settingsID)
			->delete('annual_goals');
	}

	function getYearlyProgress($settingsID, $year)
	{
		// Get actual clients added this year
		$clientCount = $this->db
			->where('settingsID', $settingsID)
			->where('YEAR(created_at)', $year)
			->where("COALESCE(ClientStat, '') !=", 'Deleted')
			->count_all_results('customers');

		// Get actual income this year (from paid invoices)
		$income = $this->db
			->select('SUM(AmountPaid) as total')
			->where('settingsID', $settingsID)
			->where('YEAR(PDate)', $year)
			->where('ORStat', 'valid')
			->get('payments')
			->row();

		return array(
			'actualClients' => (int) $clientCount,
			'actualIncome' => (float) ($income->total ?? 0)
		);
	}

	//Client List
	function attendanceList($settingsID)
	{
		$this->db->select('d.*, u.user_id, u.username, u.fName, u.mName, u.lName');
		$this->db->from('dtr d');
		$this->db->join(
			'users u',
			'u.settingsID = d.settingsID AND (u.username = d.IDNumber OR u.user_id = d.IDNumber)',
			'left'
		);
		$this->db->where('d.settingsID', $settingsID);
		$this->db->order_by('d.logDate', 'DESC');

		return $this->db->get()->result();
	}

	function attendanceListByEmployee($settingsID, $empID)
	{
		$this->db->select('d.*, u.user_id, u.username, u.fName, u.mName, u.lName');
		$this->db->from('dtr d');
		$this->db->join(
			'users u',
			'u.settingsID = d.settingsID AND (u.username = d.IDNumber OR u.user_id = d.IDNumber)',
			'left'
		);
		$this->db->where('d.settingsID', $settingsID);
		$this->db->where('d.IDNumber', $empID);
		$this->db->order_by('d.logDate', 'DESC');

		return $this->db->get()->result();
	}

	//Order Customer No.
	function getEmpID($settingsID)
	{
		$query = $this->db->query("select * from employee where settingsID='" . $settingsID . "' order by empID desc limit 1");
		return $query->result();
	}

	public function getBirthdayCelebrantsToday($settingsID)
	{
		$this->db->select("empID, fName, mName, lName, empPosition, department, bDate, TIMESTAMPDIFF(YEAR, bDate, CURDATE()) AS age", false);
		$this->db->from('employee');
		$this->db->where('settingsID', $settingsID);
		$this->db->where("DATE_FORMAT(bDate, '%m-%d') = DATE_FORMAT(CURDATE(), '%m-%d')", null, false);
		$this->db->order_by('lName');
		$this->db->order_by('fName');

		return $this->db->get()->result();
	}

	public function getBirthdayCelebrantsMonth($settingsID)
	{
		$this->db->select("empID, fName, mName, lName, empPosition, department, bDate, TIMESTAMPDIFF(YEAR, bDate, CURDATE()) AS age, DAY(bDate) AS birth_day", false);
		$this->db->from('employee');
		$this->db->where('settingsID', $settingsID);
		$this->db->where("MONTH(bDate) = MONTH(CURDATE())", null, false);
		$this->db->order_by('birth_day', 'ASC');
		$this->db->order_by('lName');
		$this->db->order_by('fName');

		return $this->db->get()->result();
	}

	public function generateNextEmployeeId($settingsID)
	{
		$year = date('Y');
		$prefix = $settingsID . $year;

		$this->db->select('empID');
		$this->db->from('employee');
		$this->db->like('empID', $prefix, 'after');
		$this->db->order_by('empID', 'DESC');
		$this->db->limit(1);
		$latest = $this->db->get()->row();

		$sequence = 0;
		if ($latest && isset($latest->empID)) {
			$suffix = substr((string) $latest->empID, strlen($prefix));
			if ($suffix !== '' && ctype_digit($suffix)) {
				$sequence = (int) $suffix;
			}
		}

		do {
			$sequence++;
			$candidate = $prefix . str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
			$exists = $this->db
				->select('empID')
				->from('employee')
				->where('empID', $candidate)
				->limit(1)
				->get()
				->num_rows() > 0;
		} while ($exists);

		return $candidate;
	}

	//Accomplishments
	function accomplishments($settingsID)
	{
		$this->db->select('t.*, p.projectDescription');
		$this->db->from('projects_task t');
		$this->db->join('projects p', 't.projectID = p.projectID', 'left');
		$this->db->where('t.taskStat', '0');
		$this->db->where('t.settingsID', $settingsID);
		$this->db->order_by('t.taskID', 'desc');

		$query = $this->db->get();
		return $query->result();
	}

	function accomplishmentsStaff($settingsID, $name)
	{
		$latestClosedSubquery = "(SELECT taskID, MAX(ptsID) AS ptsID FROM projects_task_stat WHERE taskStat = '0' GROUP BY taskID) latest_closed";

		$select = 't.*, p.projectDescription, s.datePosted AS accomplishedDate, s.note, s.ptsID';
		$fields = $this->db->list_fields('projects_task_stat');
		if (in_array('points', $fields)) {
			$select .= ', s.points';
		}
		$this->db->select($select);
		$this->db->from('projects_task t');
		$this->db->join('projects p', 't.projectID = p.projectID', 'left');
		$this->db->join($latestClosedSubquery, 'latest_closed.taskID = t.taskID', 'left', false);
		$this->db->join('projects_task_stat s', 's.ptsID = latest_closed.ptsID', 'left');
		$this->db->where('t.taskStat', '0');
		$this->db->where('s.ptsID IS NOT NULL', null, false);
		$this->db->group_start();
		$this->db->where('t.completed_by', $name);
		$this->db->or_where('t.assignedPerson', $name);
		$this->db->or_where("t.completed_by IN (SELECT user_id FROM users WHERE username = " . $this->db->escape($name) . ")", null, false);
		$this->db->or_where("t.assignedPerson IN (SELECT user_id FROM users WHERE username = " . $this->db->escape($name) . ")", null, false);
		$this->db->group_end();
		$this->db->where('t.settingsID', $settingsID);
		$this->db->order_by('s.datePosted', 'desc');

		return $this->db->get()->result();
	}

	public function accomplishmentsStaffFiltered($settingsID, $name, $month, $year, $endDate = null)
	{
		$latestClosedSubquery = "(SELECT taskID, MAX(ptsID) AS ptsID FROM projects_task_stat WHERE taskStat = '0' GROUP BY taskID) latest_closed";

		$select = "t.*, p.projectDescription, s.ptsID, s.note, s.datePosted, CONCAT(u.fName, ' ', u.lName) AS assignedPersonName, 'task' AS accomplishment_type";
		$fields = $this->db->list_fields('projects_task_stat');
		if (in_array('points', $fields)) {
			$select .= ", s.points";
		}
		$this->db->select($select);
		$this->db->from('projects_task t');
		$this->db->join('projects p', 't.projectID = p.projectID', 'left');
		$this->db->join($latestClosedSubquery, 'latest_closed.taskID = t.taskID', 'left', false);
		$this->db->join('projects_task_stat s', 's.ptsID = latest_closed.ptsID', 'left');
		$this->db->join('users u', 'u.user_id = IF(t.completed_by IS NOT NULL, t.completed_by, t.assignedPerson)', 'left');
		$this->db->where('t.taskStat', '0');
		$this->db->where('s.ptsID IS NOT NULL', null, false);
		if (!empty($month)) {
			$this->db->where('MONTH(s.datePosted)', $month);
		}
		if (!empty($year)) {
			$this->db->where('YEAR(s.datePosted)', $year);
		}
		if (!empty($endDate)) {
			$this->db->where('DATE(s.datePosted) <=', $endDate);
		}
		$this->db->group_start();
		$this->db->where('t.completed_by', $name);
		$this->db->or_where('t.assignedPerson', $name);
		$this->db->or_where("t.completed_by IN (SELECT user_id FROM users WHERE username = " . $this->db->escape($name) . ")", null, false);
		$this->db->or_where("t.assignedPerson IN (SELECT user_id FROM users WHERE username = " . $this->db->escape($name) . ")", null, false);
		$this->db->group_end();
		$this->db->where('t.settingsID', $settingsID);
		
		$task_results = $this->db->get()->result();
		
		// Get calendar event completions
		$calendar_results = array();
		if ($this->db->table_exists('calendar_event_completions')) {
			$select_cal = "c.*, CONCAT(u.fName, ' ', u.lName) AS assignedPersonName, 'calendar' AS accomplishment_type";
			$this->db->select($select_cal);
			$this->db->from('calendar_event_completions c');
			$this->db->join('users u', 'u.user_id = c.user_id', 'left');
			$this->db->where('c.settingsID', $settingsID);
			$this->db->group_start();
			$this->db->where('c.user_id', $name);
			$this->db->or_where('c.username', $name);
			$this->db->group_end();
			if (!empty($month)) {
				$this->db->where('MONTH(c.completed_at)', $month);
			}
			if (!empty($year)) {
				$this->db->where('YEAR(c.completed_at)', $year);
			}
			if (!empty($endDate)) {
				$this->db->where('DATE(c.completed_at) <=', $endDate);
			}
			$this->db->order_by('c.completed_at', 'desc');
			$calendar_results = $this->db->get()->result();
		}
		
		// Merge results
		$all_results = array_merge($task_results, $calendar_results);
		
		// Sort by date descending
		usort($all_results, function($a, $b) {
			$date_a = isset($a->datePosted) ? $a->datePosted : (isset($a->completed_at) ? $a->completed_at : '');
			$date_b = isset($b->datePosted) ? $b->datePosted : (isset($b->completed_at) ? $b->completed_at : '');
			return strtotime($date_b) - strtotime($date_a);
		});
		
		return $all_results;
	}

	public function accomplishmentsAdminFiltered($settingsID, $month, $year)
	{
		$latestClosedSubquery = "(SELECT taskID, MAX(ptsID) AS ptsID FROM projects_task_stat WHERE taskStat = '0' GROUP BY taskID) latest_closed";

		$select = "t.*, p.projectDescription, s.ptsID, s.note, s.datePosted, CONCAT(u.fName, ' ', u.lName) AS assignedPersonName, 'task' AS accomplishment_type";
		if ($this->db->field_exists('points', 'projects_task_stat')) {
			$select .= ", s.points";
		}
		$this->db->select($select);
		$this->db->from('projects_task t');
		$this->db->join('projects p', 't.projectID = p.projectID', 'left');
		$this->db->join($latestClosedSubquery, 'latest_closed.taskID = t.taskID', 'inner', false);
		$this->db->join('projects_task_stat s', 's.ptsID = latest_closed.ptsID');
		$this->db->join('users u', 'u.user_id = t.assignedPerson', 'left');
		$this->db->where('t.taskStat', '0');
		$this->db->where('MONTH(s.datePosted)', $month);
		$this->db->where('YEAR(s.datePosted)', $year);
		$this->db->where('t.settingsID', $settingsID);
		
		$task_results = $this->db->get()->result();
		
		// Get calendar event completions
		$calendar_results = array();
		if ($this->db->table_exists('calendar_event_completions')) {
			$select_cal = "c.*, CONCAT(u.fName, ' ', u.lName) AS assignedPersonName, 'calendar' AS accomplishment_type";
			$this->db->select($select_cal);
			$this->db->from('calendar_event_completions c');
			$this->db->join('users u', 'u.user_id = c.user_id', 'left');
			$this->db->where('c.settingsID', $settingsID);
			if (!empty($month)) {
				$this->db->where('MONTH(c.completed_at)', $month);
			}
			if (!empty($year)) {
				$this->db->where('YEAR(c.completed_at)', $year);
			}
			$this->db->order_by('c.completed_at', 'desc');
			$calendar_results = $this->db->get()->result();
		}
		
		// Merge results
		$all_results = array_merge($task_results, $calendar_results);
		
		// Sort by date descending
		usort($all_results, function($a, $b) {
			$date_a = isset($a->datePosted) ? $a->datePosted : (isset($a->completed_at) ? $a->completed_at : '');
			$date_b = isset($b->datePosted) ? $b->datePosted : (isset($b->completed_at) ? $b->completed_at : '');
			return strtotime($date_b) - strtotime($date_a);
		});
		
		return $all_results;
	}




	function accomplishmentsStaffbyDate($settingsID, $name, $date)
	{
		$this->db->select('p.*, s.*, u.user_id, u.username, u.fName, u.mName, u.lName');
		$this->db->from('projects_task p');
		$this->db->join('projects_task_stat s', 'p.taskID = s.taskID');
		// Use completed_by if available (who actually completed), otherwise fall back to assignedPerson
		$this->db->join('users u', 'u.user_id = IF(p.completed_by IS NOT NULL, p.completed_by, p.assignedPerson)', 'left');
		$this->db->where('p.settingsID', $settingsID);
		$this->db->where('p.taskStat', '0');
		$this->db->where('s.taskStat', '0');
		$this->db->where('DATE(s.datePosted)', $date);

		// Filter by who actually completed the task (completed_by), not who was assigned
		$this->db->group_start();
		$this->db->where('u.user_id', $name);
		$this->db->or_where('u.username', $name);
		$this->db->or_where("TRIM(CONCAT(u.fName, ' ', u.mName, ' ', u.lName)) =", $name);
		$this->db->or_where("TRIM(CONCAT(u.fName, ' ', u.lName)) =", $name);
		$this->db->group_end();

		$this->db->order_by('s.taskID', 'desc');
		return $this->db->get()->result();
	}

	private function taskDueDateNumberExpression($field = 'projects_task.dueDate')
	{
		return 'IFNULL(' . $field . ' + 0, 0)';
	}

	private function taskDateStringToNumber($date)
	{
		return (int) str_replace('-', '', (string) $date);
	}

	//Task List
	public function taskList($settingsID)
	{
		$latestCommentSubquery = "(SELECT taskID, MAX(ptsID) AS ptsID FROM projects_task_stat GROUP BY taskID) latest_comment";

		$this->db->select("
			projects_task.*,
			COALESCE(NULLIF(TRIM(projects.projectDescription), ''), support_project.projectDescription) AS projectDescription,
			CONCAT(users.fName, ' ', users.lName) AS assignedPersonName,
			si.id AS supportIssueId,
			si.ticket_number AS supportTicketNumber,
			COALESCE(NULLIF(projects_task.projectID, 0), si.project_id) AS linkedProjectId,
			pts.note AS latestAdminComment,
			pts.datePosted AS latestAdminCommentDate,
			pts.ptsID AS latestAdminCommentId
		");
		$this->db->from('projects_task');
		$this->db->join('projects', 'projects.projectID = projects_task.projectID', 'left');
		$this->db->join('users', 'users.user_id = projects_task.assignedPerson', 'left');
		$this->db->join('support_issues si', 'si.task_id = projects_task.taskID AND si.settingsID = projects_task.settingsID', 'left');
		$this->db->join('projects support_project', 'support_project.projectID = si.project_id', 'left');
		$this->db->join($latestCommentSubquery, 'latest_comment.taskID = projects_task.taskID', 'left', false);
		$this->db->join('projects_task_stat pts', 'pts.ptsID = latest_comment.ptsID', 'left');
		$this->db->where('projects_task.settingsID', $settingsID);
		// Removed taskStat filter to allow viewing all tasks (open and closed)
		$this->db->order_by('projects_task.reportedDate', 'DESC');
		$this->db->order_by('projects_task.taskID', 'DESC');

		return $this->db->get()->result();
	}

	public function taskListStaff($settingsID, $user_id)
	{
		$latestCommentSubquery = "(SELECT taskID, MAX(ptsID) AS ptsID FROM projects_task_stat GROUP BY taskID) latest_comment";

		$this->db->select("
			projects_task.*,
			COALESCE(NULLIF(TRIM(projects.projectDescription), ''), support_project.projectDescription) AS projectDescription,
			CONCAT(users.fName, ' ', users.lName) AS assignedPersonName,
			si.id AS supportIssueId,
			si.ticket_number AS supportTicketNumber,
			COALESCE(NULLIF(projects_task.projectID, 0), si.project_id) AS linkedProjectId,
			pts.note AS latestAdminComment,
			pts.datePosted AS latestAdminCommentDate,
			pts.ptsID AS latestAdminCommentId
		");
		$this->db->from('projects_task');
		$this->db->join('projects', 'projects.projectID = projects_task.projectID', 'left');
		$this->db->join('users', 'users.user_id = projects_task.assignedPerson', 'left');
		$this->db->join('support_issues si', 'si.task_id = projects_task.taskID AND si.settingsID = projects_task.settingsID', 'left');
		$this->db->join('projects support_project', 'support_project.projectID = si.project_id', 'left');
		$this->db->join($latestCommentSubquery, 'latest_comment.taskID = projects_task.taskID', 'left', false);
		$this->db->join('projects_task_stat pts', 'pts.ptsID = latest_comment.ptsID', 'left');
		$this->db->where('projects_task.settingsID', $settingsID);
		$this->db->where('projects_task.assignedPerson', $user_id);
		// Removed taskStat filter to allow viewing all tasks (open and closed)
		$this->db->order_by('projects_task.reportedDate', 'DESC');
		$this->db->order_by('projects_task.taskID', 'DESC');

		return $this->db->get()->result();
	}

	public function countOpenTasksDueToday($settingsID, $user_id = null, $today = null)
	{
		$today = $today ?: date('Y-m-d');
		$todayNumber = $this->taskDateStringToNumber($today);
		$dueExpr = $this->taskDueDateNumberExpression('dueDate');

		$this->db->from('projects_task');
		$this->db->where('settingsID', $settingsID);
		$this->db->where('taskStat', '1');
		$this->db->where($dueExpr . ' = ' . $todayNumber, null, false);
		if ($user_id !== null) {
			$this->db->where('assignedPerson', $user_id);
		}

		return (int) $this->db->count_all_results();
	}

	public function countOpenTasksDueSoon($settingsID, $days = 7, $user_id = null, $today = null)
	{
		$today = $today ?: date('Y-m-d');
		$endDate = date('Y-m-d', strtotime($today . ' +' . max(0, (int) $days) . ' days'));
		$todayNumber = $this->taskDateStringToNumber($today);
		$endDateNumber = $this->taskDateStringToNumber($endDate);
		$dueExpr = $this->taskDueDateNumberExpression('dueDate');

		$this->db->from('projects_task');
		$this->db->where('settingsID', $settingsID);
		$this->db->where('taskStat', '1');
		$this->db->where($dueExpr . ' >= ' . $todayNumber, null, false);
		$this->db->where($dueExpr . ' <= ' . $endDateNumber, null, false);
		if ($user_id !== null) {
			$this->db->where('assignedPerson', $user_id);
		}

		return (int) $this->db->count_all_results();
	}

	public function countOpenTasksOverdue($settingsID, $user_id = null, $today = null)
	{
		$today = $today ?: date('Y-m-d');
		$todayNumber = $this->taskDateStringToNumber($today);
		$dueExpr = $this->taskDueDateNumberExpression('dueDate');

		$this->db->from('projects_task');
		$this->db->where('settingsID', $settingsID);
		$this->db->where('taskStat', '1');
		$this->db->where($dueExpr . ' > 0', null, false);
		$this->db->where($dueExpr . ' < ' . $todayNumber, null, false);
		if ($user_id !== null) {
			$this->db->where('assignedPerson', $user_id);
		}

		return (int) $this->db->count_all_results();
	}

	public function getOverdueTasks($settingsID, $user_id = null, $today = null)
	{
		$today = $today ?: date('Y-m-d');
		$todayNumber = $this->taskDateStringToNumber($today);
		$dueExpr = $this->taskDueDateNumberExpression('dueDate');

		$this->db->select('projects_task.taskID, projects_task.task, projects_task.dueDate, projects_task.priority, projects_task.reportedDate');
		$this->db->from('projects_task');
		$this->db->where('projects_task.settingsID', $settingsID);
		$this->db->where('projects_task.taskStat', '1');
		$this->db->where($dueExpr . ' > 0', null, false);
		$this->db->where($dueExpr . ' < ' . $todayNumber, null, false);
		if ($user_id !== null) {
			$this->db->where('projects_task.assignedPerson', $user_id);
		}
		$this->db->order_by('dueDate', 'ASC');
		$this->db->limit(10);

		$result = $this->db->get()->result();
		// Ensure task field is not null/empty
		foreach ($result as $row) {
			if (empty($row->task)) {
				$row->task = 'Task #' . $row->taskID;
			}
		}
		return $result;
	}

	public function getDueTodayTasks($settingsID, $user_id = null, $today = null)
	{
		$today = $today ?: date('Y-m-d');
		$todayNumber = $this->taskDateStringToNumber($today);
		$dueExpr = $this->taskDueDateNumberExpression('dueDate');

		$this->db->select('projects_task.taskID, projects_task.task, projects_task.dueDate, projects_task.priority, projects_task.reportedDate');
		$this->db->from('projects_task');
		$this->db->where('projects_task.settingsID', $settingsID);
		$this->db->where('projects_task.taskStat', '1');
		$this->db->where($dueExpr . ' = ' . $todayNumber, null, false);
		if ($user_id !== null) {
			$this->db->where('projects_task.assignedPerson', $user_id);
		}
		$this->db->order_by('dueDate', 'ASC');
		$this->db->limit(10);

		$result = $this->db->get()->result();
		// Ensure task field is not null/empty
		foreach ($result as $row) {
			if (empty($row->task)) {
				$row->task = 'Task #' . $row->taskID;
			}
		}
		return $result;
	}

	public function countOpenTasksWithoutDueDate($settingsID, $user_id = null)
	{
		$dueExpr = $this->taskDueDateNumberExpression('dueDate');

		$this->db->from('projects_task');
		$this->db->where('settingsID', $settingsID);
		$this->db->where('taskStat', '1');
		$this->db->where($dueExpr . ' = 0', null, false);
		if ($user_id !== null) {
			$this->db->where('assignedPerson', $user_id);
		}

		return (int) $this->db->count_all_results();
	}

	public function openTaskDueQueue($settingsID, $user_id = null, $limit = 6, $days = 7, $today = null)
	{
		$today = $today ?: date('Y-m-d');
		$endDate = date('Y-m-d', strtotime($today . ' +' . max(0, (int) $days) . ' days'));
		$endDateNumber = $this->taskDateStringToNumber($endDate);
		$dueExpr = $this->taskDueDateNumberExpression('projects_task.dueDate');

		$this->db->select("
			projects_task.taskID,
			projects_task.task,
			projects_task.reportedDate,
			projects_task.dueDate,
			projects_task.priority,
			projects.projectDescription,
			CONCAT(users.fName, ' ', users.lName) AS assignedPersonName
		");
		$this->db->from('projects_task');
		$this->db->join('projects', 'projects.projectID = projects_task.projectID', 'left');
		$this->db->join('users', 'users.user_id = projects_task.assignedPerson', 'left');
		$this->db->where('projects_task.settingsID', $settingsID);
		$this->db->where('projects_task.taskStat', '1');
		$this->db->where($dueExpr . ' > 0', null, false);
		$this->db->where($dueExpr . ' <= ' . $endDateNumber, null, false);
		if ($user_id !== null) {
			$this->db->where('projects_task.assignedPerson', $user_id);
		}
		$this->db->order_by('projects_task.dueDate', 'ASC');
		$this->db->order_by('projects_task.priority', 'ASC');
		$this->db->order_by('projects_task.taskID', 'DESC');
		$this->db->limit((int) $limit);

		return $this->db->get()->result();
	}

	//Get Project Categories
	function getprojectcat()
	{
		$query = $this->db->query("select * from projects_cat order by Category");
		return $query->result();
	}


	//Get Project 
	public function getProjectName($settingsID)
	{
		$this->db->select('*');
		$this->db->from('projects');
		$this->db->where('settingsID', $settingsID);
		$this->db->order_by('projectDescription', 'ASC'); // Updated line

		$query = $this->db->get();
		return $query->result();
	}

	//Get Staff
	function getStaff($settingsID)
	{
		$this->db->select('*');
		$this->db->from('users');
		$this->db->where('settingsID', $settingsID);
		$this->db->where('position !=', 'Admin');
		$this->db->where('position !=', 'POS Admin');
		$this->db->order_by('lName');

		$query = $this->db->get();
		return $query->result();
	}

	public function getUserById($settingsID, $userId)
	{
		return $this->db
			->select('user_id, username, position, fName, mName, lName, email, avatar')
			->from('users')
			->where('settingsID', $settingsID)
			->where('user_id', $userId)
			->limit(1)
			->get()
			->row();
	}

	public function getUserFlexible($settingsID, $identifier)
	{
		// Try by numeric id first
		if (is_numeric($identifier)) {
			$user = $this->getUserById($settingsID, (int) $identifier);
			if ($user) {
				return $user;
			}
		}

		$this->db
			->select('user_id, username, position, fName, mName, lName, email, avatar')
			->from('users')
			->where('settingsID', $settingsID)
			->group_start()
			->where('username', $identifier)
			->or_where("TRIM(CONCAT(fName, ' ', mName, ' ', lName)) =", $identifier)
			->or_where("TRIM(CONCAT(fName, ' ', lName)) =", $identifier)
			->group_end()
			->limit(1);

		$query = $this->db->get();
		return $query->row();
	}

	public function accomplishmentCountForDate($settingsID, $identifier, $date)
	{
		$userJoin = 'u.user_id = p.assignedPerson';
		if ($this->db->field_exists('completed_by', 'projects_task')) {
			$userJoin = 'u.user_id = IF(p.completed_by IS NOT NULL, p.completed_by, p.assignedPerson)';
		}

		$this->db->from('projects_task p');
		$this->db->join('projects_task_stat s', 'p.taskID = s.taskID');
		$this->db->join('users u', $userJoin, 'left', false);
		$this->db->where('p.settingsID', $settingsID);
		$this->db->where('p.taskStat', '0');
		$this->db->where('s.taskStat', '0');
		$this->db->where('DATE(s.datePosted)', $date);

		// Only count accomplishments for user who actually completed the task
		$this->db->group_start();
		$this->db->where('u.user_id', $identifier);
		$this->db->or_where('u.username', $identifier);
		$this->db->group_end();

		return (int) $this->db->count_all_results();
	}

	public function accomplishmentsByDateAll($settingsID, $date)
	{
		$userJoin = 'u.user_id = p.assignedPerson';
		if ($this->db->field_exists('completed_by', 'projects_task')) {
			$userJoin = 'u.user_id = IF(p.completed_by IS NOT NULL, p.completed_by, p.assignedPerson)';
		}

		$this->db->select('p.*, s.*, u.user_id, u.username, u.fName, u.mName, u.lName, u.position, proj.projectDescription');
		$this->db->from('projects_task p');
		$this->db->join('projects_task_stat s', 'p.taskID = s.taskID');
		$this->db->join('projects proj', 'proj.projectID = p.projectID', 'left');
		$this->db->join('users u', $userJoin, 'left', false);
		$this->db->where('p.settingsID', $settingsID);
		$this->db->where('p.taskStat', '0');
		$this->db->where('s.taskStat', '0');
		$this->db->where('DATE(s.datePosted)', $date);
		$this->db->order_by('u.lName');
		$this->db->order_by('u.fName');
		$this->db->order_by('s.datePosted', 'desc');

		return $this->db->get()->result();
	}


	//Order Customer No.
	function getCustID()
	{
		$query = $this->db->query("select * from customers order by CustID desc limit 1");
		return $query->result();
	}

	public function taskStat($taskID)
	{
		$this->db->select("p.task, p.reportedDate, p.dueDate, p.priority, s.note, s.datePosted, s.postedBy, s.taskStat, pr.projectDescription, CONCAT(u.fName, ' ', u.lName) AS assignedPersonName");
		$this->db->from('projects_task p');
		$this->db->join('projects_task_stat s', 'p.taskID = s.taskID', 'left');
		$this->db->join('projects pr', 'p.projectID = pr.projectID', 'left');
		$this->db->join('users u', 'p.assignedPerson = u.user_id', 'left');
		$this->db->where('s.taskID', $taskID);

		$query = $this->db->get();
		return $query->result();
	}



	public function taskPerProject($settingsID, $user_id = null, $projectID = null, $taskStatus = 1)
	{
		$latestCommentSubquery = "(SELECT taskID, MAX(ptsID) AS ptsID FROM projects_task_stat GROUP BY taskID) latest_comment";

		$this->db->select('
        projects_task.*,
        projects.projectDescription,
        CONCAT(u.lName, ", ", u.fName) AS assignedPersonName,
	        pts.note AS latestAdminComment,
	        pts.datePosted AS latestAdminCommentDate,
	        pts.ptsID AS latestAdminCommentId
	    ');
		$this->db->from('projects_task');
		$this->db->join('projects', 'projects.projectID = projects_task.projectID', 'left');
		$this->db->join('users u', 'u.user_id = projects_task.assignedPerson', 'left'); // change "users" if needed
		$this->db->join($latestCommentSubquery, 'latest_comment.taskID = projects_task.taskID', 'left', false);
		$this->db->join('projects_task_stat pts', 'pts.ptsID = latest_comment.ptsID', 'left');

		$this->db->where('projects_task.settingsID', $settingsID);

		// show tasks under this project
		if (!empty($projectID)) {
			$this->db->where('projects_task.projectID', $projectID);
		}

		// 🔹 status filter: 1 = Open, 0 = Closed
		$this->db->where('projects_task.taskStat', (int) $taskStatus);

		// OPTIONAL: if one day you want a "My tasks under this project" view
		// if (!empty($user_id)) {
		//     $this->db->where('projects_task.assignedPerson', $user_id);
		// }

		$this->db->order_by('projects_task.reportedDate', 'DESC');
		$this->db->order_by('projects_task.taskID', 'DESC');

		return $this->db->get()->result();
	}




	//PASSWORD ---------------------------------------------------------------------------------
	function is_current_password($username, $currentpass)
	{
		$this->db->select();
		$this->db->from('users');
		$this->db->where('username', $username);
		$this->db->where('password', $currentpass);
		$this->db->where('acctStat', 'active');
		$query = $this->db->get();
		$row = $query->row();
		if ($row) {
			return TRUE;
		} else {
			return FALSE;
		}
	}

	function reset_userpassword($username, $newpass)
	{
		$data = array(
			'password' => $newpass
		);
		$this->db->where('username', $username);
		if ($this->db->update('users', $data)) {
			return TRUE;
		} else {
			return FALSE;
		}
	}

	function businessDetails($settingsID)
	{
		$query = $this->db->query("select * from pos_settings where settingsID='" . $settingsID . "'");
		return $query->result();
	}

	function invoiceFooterSettings($settingsID)
	{
		// Ensure table exists before querying
		if (!$this->db->table_exists('invoice_footer_settings')) {
			$this->db->query("
				CREATE TABLE IF NOT EXISTS `invoice_footer_settings` (
					`id` int(11) NOT NULL AUTO_INCREMENT,
					`settingsID` int(11) NOT NULL,
					`bank_name_1` varchar(255) DEFAULT NULL,
					`bank_account_name_1` varchar(255) DEFAULT NULL,
					`bank_account_no_1` varchar(255) DEFAULT NULL,
					`bank_name_2` varchar(255) DEFAULT NULL,
					`bank_account_name_2` varchar(255) DEFAULT NULL,
					`bank_account_no_2` varchar(255) DEFAULT NULL,
					`contact_email` varchar(255) DEFAULT NULL,
					`contact_phone` varchar(100) DEFAULT NULL,
					`footer_disclaimer` text DEFAULT NULL,
					`updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
					PRIMARY KEY (`id`),
					UNIQUE KEY `idx_settings` (`settingsID`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci
			");
		}

		$query = $this->db->query("select * from invoice_footer_settings where settingsID='" . $settingsID . "'");
		return $query->result();
	}
	public function attendanceListByDate($settingsID, $date)
	{
		$this->db->select('d.*, u.user_id, u.username, u.fName, u.mName, u.lName');
		$this->db->from('dtr d');
		$this->db->join(
			'users u',
			'u.settingsID = d.settingsID AND (u.username = d.IDNumber OR u.user_id = d.IDNumber)',
			'left'
		);
		$this->db->where('d.settingsID', $settingsID);
		$this->db->where('DATE(d.logDate) =', $date, false); // ✅ filter the day
		$this->db->order_by('d.logDate', 'DESC');

		return $this->db->get()->result();
	}

	public function attendanceListByEmployeeDate($settingsID, $empID, $date)
	{
		$this->db->select('d.*, u.user_id, u.username, u.fName, u.mName, u.lName');
		$this->db->from('dtr d');
		$this->db->join(
			'users u',
			'u.settingsID = d.settingsID AND (u.username = d.IDNumber OR u.user_id = d.IDNumber)',
			'left'
		);
		$this->db->where('d.settingsID', $settingsID);
		$this->db->where('d.IDNumber', $empID);
		$this->db->where('DATE(d.logDate) =', $date, false); // ✅ filter the day
		$this->db->order_by('d.logDate', 'DESC');

		return $this->db->get()->result();
	}
	private function _applyDtrDateRangeFilter($from, $to)
	{
		$from = (string)$from; // Y-m-d
		$to   = (string)$to;   // Y-m-d

		if ($from === '' || $to === '') return;

		// normalize if user swapped dates
		if (strtotime($from) > strtotime($to)) {
			$tmp = $from;
			$from = $to;
			$to = $tmp;
		}

		$start = $from . ' 00:00:00';
		$end   = date('Y-m-d', strtotime($to . ' +1 day')) . ' 00:00:00'; // exclusive end

		$this->db->group_start()

			// 1) If logDate is real DATE/DATETIME
			->or_where("(d.logDate >= " . $this->db->escape($start) . " AND d.logDate < " . $this->db->escape($end) . ")", null, false)

			// 2) If logDate needs DATE() conversion (still works for DATETIME)
			->or_where("(DATE(d.logDate) >= " . $this->db->escape($from) . " AND DATE(d.logDate) <= " . $this->db->escape($to) . ")", null, false)

			// 3) If logDate is VARCHAR like "Jan. 15, 2026"
			->or_where("(DATE(STR_TO_DATE(d.logDate, '%b. %d, %Y')) >= " . $this->db->escape($from) .
				" AND DATE(STR_TO_DATE(d.logDate, '%b. %d, %Y')) <= " . $this->db->escape($to) . ")", null, false)

			// 4) If logDate is VARCHAR like "January 15, 2026"
			->or_where("(DATE(STR_TO_DATE(d.logDate, '%M %d, %Y')) >= " . $this->db->escape($from) .
				" AND DATE(STR_TO_DATE(d.logDate, '%M %d, %Y')) <= " . $this->db->escape($to) . ")", null, false)

			->group_end();
	}
	public function attendanceListByRange($settingsID, $from, $to)
	{
		$this->db->select('d.*, u.user_id, u.username, u.fName, u.mName, u.lName');
		$this->db->from('dtr d');
		$this->db->join(
			'users u',
			'u.settingsID = d.settingsID AND (u.username = d.IDNumber OR u.user_id = d.IDNumber)',
			'left'
		);
		$this->db->where('d.settingsID', $settingsID);

		$this->_applyDtrDateRangeFilter($from, $to);

		$this->db->order_by('d.logDate', 'DESC');
		return $this->db->get()->result();
	}

	public function attendanceListByEmployeeRange($settingsID, $empID, $from, $to)
	{
		$this->db->select('d.*, u.user_id, u.username, u.fName, u.mName, u.lName');
		$this->db->from('dtr d');
		$this->db->join(
			'users u',
			'u.settingsID = d.settingsID AND (u.username = d.IDNumber OR u.user_id = d.IDNumber)',
			'left'
		);
		$this->db->where('d.settingsID', $settingsID);
		$this->db->where('d.IDNumber', $empID);

		$this->_applyDtrDateRangeFilter($from, $to);

		$this->db->order_by('d.logDate', 'DESC');
		return $this->db->get()->result();
	}

	public function getTodayPersonnelWithTimeIn($settingsID, $date)
	{
		// Get all staff members with their DTR records for today
		$this->db->select('u.user_id, u.username, u.fName, u.lName, u.avatar, 
			d.amTimeIn, d.amTimeOut, d.pmTimeIn, d.pmTimeOut, d.logDate');
		$this->db->from('users u');
		$this->db->join(
			'dtr d',
			'd.settingsID = u.settingsID AND (d.IDNumber = u.username OR d.IDNumber = u.user_id) AND DATE(d.logDate) = ' . $this->db->escape($date),
			'left'
		);
		$this->db->where('u.settingsID', $settingsID);
		$this->db->where('u.position !=', 'Admin');
		// Only include users who have time-in today (amTimeIn OR pmTimeIn is not empty)
		$this->db->group_start();
		$this->db->where('d.amTimeIn IS NOT NULL', null, false);
		$this->db->or_where('d.pmTimeIn IS NOT NULL', null, false);
		$this->db->group_end();
		$this->db->order_by('u.lName', 'ASC');
		$this->db->order_by('u.fName', 'ASC');
		return $this->db->get()->result();
	}

	public function hasTimeInToday($settingsID, $userIdentifier, $date)
	{
		$userIdentifier = trim((string) $userIdentifier);
		if ($userIdentifier === '') {
			return false;
		}

		// Check if user has time-in today (either AM or PM). Keep this aligned
		// with attendanceListByEmployeeRange(), which supports older logDate
		// string formats and DTR rows keyed by either username or user_id.
		$this->db->select('d.amTimeIn, d.pmTimeIn');
		$this->db->from('dtr d');
		$this->db->join('users u', 'u.settingsID = d.settingsID AND (u.username = d.IDNumber OR u.user_id = d.IDNumber)', 'left');
		$this->db->where('d.settingsID', $settingsID);
		$this->_applyDtrDateRangeFilter($date, $date);
		$this->db->group_start();
		$this->db->where('d.IDNumber', $userIdentifier);
		$this->db->or_where('u.username', $userIdentifier);
		$this->db->or_where('u.user_id', $userIdentifier);
		$this->db->group_end();
		$this->db->group_start();
		$this->db->where("(d.amTimeIn IS NOT NULL AND TRIM(d.amTimeIn) != '')", null, false);
		$this->db->or_where("(d.pmTimeIn IS NOT NULL AND TRIM(d.pmTimeIn) != '')", null, false);
		$this->db->group_end();
		$this->db->limit(1);
		$result = $this->db->get()->row();
		return $result !== null;
	}

	// Get next invoice number (auto-increment)
	public function get_next_invoice_no($settingsID)
	{
		// Get the highest invoice number for this settingsID
		$this->db->select('InvoiceNo');
		$this->db->from('invoices');
		$this->db->where('settingsID', $settingsID);
		$this->db->order_by('InvoiceNo', 'DESC');
		$this->db->limit(1);

		$result = $this->db->get()->row();

		if ($result && !empty($result->InvoiceNo)) {
			// Extract numeric part and increment
			$lastInvoiceNo = $result->InvoiceNo;

			// Try to extract numeric part (handles formats like INV250416-001)
			if (preg_match('/(\d+)-(\d+)$/', $lastInvoiceNo, $matches)) {
				$datePart = $matches[1];
				$sequencePart = $matches[2];

				// Get current date
				$today = date('ymd');

				if ($datePart == $today) {
					// Same day, increment sequence
					$nextSequence = (int)$sequencePart + 1;
					$nextSequence = str_pad($nextSequence, 3, '0', STR_PAD_LEFT);
					return 'INV' . $today . '-' . $nextSequence;
				} else {
					// New day, start with sequence 001
					return 'INV' . $today . '-001';
				}
			} else {
				// Fallback: try to extract any numeric part
				if (preg_match('/(\d+)$/', $lastInvoiceNo, $matches)) {
					$numericPart = (int)$matches[1] + 1;
					return 'INV' . $numericPart;
				}
			}
		}

		// No existing records or couldn't parse, create new one
		$today = date('ymd');
		return 'INV' . $today . '-001';
	}
}
