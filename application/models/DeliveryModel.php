<?php
class DeliveryModel extends CI_Model
{
    // Get all deliveries for a settingsID (grouped by Delivery No. and Customer)
    public function get_deliveries($settingsID, $status = null)
    {
        // Group deliveries by Delivery No. and Customer Name with aggregated totals
        $this->db->select('d.deliveryNo, d.customerName, d.customerAddress, d.customerContact, 
                          d.deliveryStatus, d.paymentStatus, d.receivedBy, d.deliveredBy, d.notes, 
                          d.settingsID, d.createdAt, d.updatedAt,
                          COUNT(d.deliveryID) as delivery_count,
                          MIN(d.deliveryDate) as first_delivery_date,
                          MAX(d.deliveryDate) as last_delivery_date,
                          SUM(d.totalAmount) as total_amount,
                          SUM(d.amountPaid) as total_paid,
                          SUM(d.balance) as total_balance,
                          MAX(d.invoiceNo) as primary_invoice_no');
        $this->db->from('customer_deliveries d');
        $this->db->where('d.settingsID', $settingsID);
        
        // Add status filter if provided
        if ($status !== null) {
            $this->db->where('d.deliveryStatus', $status);
        }
        
        $this->db->group_by('d.deliveryNo, d.customerName');
        $this->db->order_by('d.createdAt', 'DESC');
        
        $result = $this->db->get()->result();
        
        return $result;
    }

    // Get deliveries for a specific user (who encoded the delivery)
    public function get_deliveries_by_user($settingsID, $username, $status = null)
    {
        // Group deliveries by Delivery No. and Customer Name with aggregated totals
        $this->db->select('d.deliveryNo, d.customerName, d.customerAddress, d.customerContact, 
                          d.deliveryStatus, d.paymentStatus, d.receivedBy, d.deliveredBy, d.notes, 
                          d.settingsID, d.createdAt, d.updatedAt,
                          COUNT(d.deliveryID) as delivery_count,
                          MIN(d.deliveryDate) as first_delivery_date,
                          MAX(d.deliveryDate) as last_delivery_date,
                          SUM(d.totalAmount) as total_amount,
                          SUM(d.amountPaid) as total_paid,
                          SUM(d.balance) as total_balance,
                          MAX(d.invoiceNo) as primary_invoice_no');
        $this->db->from('customer_deliveries d');
        $this->db->where('d.settingsID', $settingsID);
        $this->db->where('d.deliveredBy', $username); // Filter by user who encoded the delivery
        
        // Add status filter if provided
        if ($status !== null) {
            $this->db->where('d.deliveryStatus', $status);
        }
        
        $this->db->group_by('d.deliveryNo, d.customerName');
        $this->db->order_by('d.createdAt', 'DESC');
        
        $result = $this->db->get()->result();
        
        return $result;
    }

    // Get individual deliveries (for detailed view)
    public function get_individual_deliveries($settingsID)
    {
        $this->db->select('d.*, COUNT(di.itemID) as item_count');
        $this->db->from('customer_deliveries d');
        $this->db->join('customer_delivery_items di', 'd.deliveryID = di.deliveryID', 'left');
        $this->db->where('d.settingsID', $settingsID);
        $this->db->group_by('d.deliveryID');
        $this->db->order_by('d.createdAt', 'DESC');
        return $this->db->get()->result();
    }

    // Get delivery by ID
    public function get_delivery($deliveryID, $settingsID)
    {
        return $this->db->get_where('customer_deliveries', ['deliveryID' => $deliveryID, 'settingsID' => $settingsID])->row();
    }

    // Get delivery items
    public function get_delivery_items($deliveryID, $settingsID)
    {
        return $this->db->get_where('customer_delivery_items', ['deliveryID' => $deliveryID, 'settingsID' => $settingsID])->result();
    }

    // Create new delivery
    public function create_delivery($data)
    {
        $data['settingsID'] = $this->session->userdata('settingsID');
        $data['createdAt'] = date('Y-m-d H:i:s');
        $data['updatedAt'] = date('Y-m-d H:i:s');

        $this->db->insert('customer_deliveries', $data);
        return $this->db->insert_id();
    }

    // Add delivery item
    public function add_delivery_item($data)
    {
        $data['settingsID'] = $this->session->userdata('settingsID');
        $data['createdAt'] = date('Y-m-d H:i:s');

        $this->db->insert('customer_delivery_items', $data);
        return $this->db->insert_id();
    }

    // Update delivery totals
    public function update_delivery_totals($deliveryID, $totalAmount)
    {
        $data = [
            'totalAmount' => $totalAmount,
            'amountPaid' => 0, // Default to 0 for new deliveries
            'balance' => $totalAmount, // Balance equals total amount initially
            'updatedAt' => date('Y-m-d H:i:s')
        ];
        
        $this->db->where('deliveryID', $deliveryID);
        $this->db->where('settingsID', $this->session->userdata('settingsID'));
        return $this->db->update('customer_deliveries', $data);
    }

    // Recalculate totals for all existing deliveries
    public function recalculate_all_delivery_totals($settingsID)
    {
        // Get all deliveries for this settingsID
        $this->db->select('deliveryID');
        $this->db->where('settingsID', $settingsID);
        $deliveries = $this->db->get('customer_deliveries')->result();
        
        $updated = 0;
        foreach ($deliveries as $delivery) {
            // Calculate total from delivery items
            $this->db->select('SUM(itemQuantity * itemUnitPrice) as total');
            $this->db->where('deliveryID', $delivery->deliveryID);
            $this->db->where('settingsID', $settingsID);
            $result = $this->db->get('customer_delivery_items')->row();
            
            $totalAmount = $result->total ?? 0;
            
            // Update delivery with calculated total
            $this->db->where('deliveryID', $delivery->deliveryID);
            $this->db->where('settingsID', $settingsID);
            $this->db->update('customer_deliveries', [
                'totalAmount' => $totalAmount,
                'amountPaid' => 0,
                'balance' => $totalAmount,
                'updatedAt' => date('Y-m-d H:i:s')
            ]);
            
            $updated++;
        }
        
        return $updated;
    }

    // Update delivery
    public function update_delivery($deliveryID, $data)
    {
        $data['updatedAt'] = date('Y-m-d H:i:s');
        $this->db->where('deliveryID', $deliveryID);
        $this->db->where('settingsID', $this->session->userdata('settingsID'));
        return $this->db->update('customer_deliveries', $data);
    }

    // Update delivery status
    public function update_delivery_status($deliveryID, $deliveryStatus, $paymentStatus = null)
    {
        $data = [
            'deliveryStatus' => $deliveryStatus,
            'updatedAt' => date('Y-m-d H:i:s')
        ];

        if ($paymentStatus !== null) {
            $data['paymentStatus'] = $paymentStatus;
        }

        $this->db->where('deliveryID', $deliveryID);
        $this->db->where('settingsID', $this->session->userdata('settingsID'));
        return $this->db->update('customer_deliveries', $data);
    }

    // Update grouped delivery status (for multiple deliveries with same deliveryNo and customer)
    public function update_grouped_delivery_status($deliveryNo, $customerName, $deliveryStatus, $settingsID)
    {
        $data = [
            'deliveryStatus' => $deliveryStatus,
            'updatedAt' => date('Y-m-d H:i:s')
        ];

        $this->db->where('deliveryNo', $deliveryNo);
        $this->db->where('customerName', $customerName);
        $this->db->where('settingsID', $settingsID);
        return $this->db->update('customer_deliveries', $data);
    }

    // Get grouped delivery information
    public function get_grouped_delivery($deliveryNo, $customerName, $settingsID)
    {
        $this->db->select('d.deliveryNo, d.customerName, d.customerAddress, d.customerContact, 
                          d.deliveryStatus, d.paymentStatus, d.receivedBy, d.deliveredBy, d.notes, 
                          d.settingsID, d.createdAt, d.updatedAt,
                          COUNT(d.deliveryID) as delivery_count,
                          MIN(d.deliveryDate) as first_delivery_date,
                          MAX(d.deliveryDate) as last_delivery_date,
                          MIN(d.deliveryDate) as deliveryDate,
                          SUM(d.totalAmount) as totalAmount,
                          SUM(d.amountPaid) as amountPaid,
                          SUM(d.balance) as balance,
                          MAX(d.invoiceNo) as invoiceNo');
        $this->db->from('customer_deliveries d');
        $this->db->where('d.deliveryNo', $deliveryNo);
        $this->db->where('d.customerName', $customerName);
        $this->db->where('d.settingsID', $settingsID);
        $this->db->group_by('d.deliveryNo, d.customerName');
        
        return $this->db->get()->row();
    }

    // Update grouped delivery payment
    public function update_grouped_delivery_payment($deliveryNo, $customerName, $amountPaid, $settingsID)
    {
        // Update each delivery in the group
        $this->db->where('deliveryNo', $deliveryNo);
        $this->db->where('customerName', $customerName);
        $this->db->where('settingsID', $settingsID);
        
        $deliveries = $this->db->get('customer_deliveries')->result();
        
        foreach ($deliveries as $delivery) {
            $newAmountPaid = $delivery->amountPaid + $amountPaid;
            $totalAmount = $delivery->totalAmount ?? $delivery->totalamount ?? 0; // Handle both camelCase and lowercase
            $newBalance = $totalAmount - $newAmountPaid;
            
            $paymentStatus = 'unpaid';
            if ($newBalance <= 0) {
                $paymentStatus = 'paid';
            } elseif ($newAmountPaid > 0) {
                $paymentStatus = 'partial';
            }
            
            $this->db->where('deliveryID', $delivery->deliveryID);
            $this->db->update('customer_deliveries', [
                'amountPaid' => $newAmountPaid,
                'balance' => max(0, $newBalance),
                'paymentStatus' => $paymentStatus,
                'updatedAt' => date('Y-m-d H:i:s')
            ]);
        }
        
        return true;
    }

    // Get individual deliveries by group (for printing)
    public function get_deliveries_by_group($deliveryNo, $customerName, $settingsID)
    {
        $this->db->select('d.*');
        $this->db->from('customer_deliveries d');
        $this->db->where('d.deliveryNo', $deliveryNo);
        $this->db->where('d.customerName', $customerName);
        $this->db->where('d.settingsID', $settingsID);
        $this->db->order_by('d.deliveryDate', 'ASC');
        
        $deliveries = $this->db->get()->result();
        
        // Get items for each delivery
        foreach ($deliveries as $delivery) {
            $delivery->items = $this->get_delivery_items($delivery->deliveryID, $settingsID);
        }
        
        return $deliveries;
    }

    // Get next delivery number (auto-increment)
    public function get_next_delivery_no($settingsID)
    {
        // Get the highest delivery number for this settingsID
        $this->db->select('deliveryNo');
        $this->db->from('customer_deliveries');
        $this->db->where('settingsID', $settingsID);
        $this->db->order_by('deliveryNo', 'DESC');
        $this->db->limit(1);
        
        $result = $this->db->get()->row();
        
        if ($result && !empty($result->deliveryNo)) {
            // Extract numeric part and increment
            $lastDeliveryNo = $result->deliveryNo;
            
            // Try to extract numeric part (handles formats like DEL250416-001)
            if (preg_match('/(\d+)-(\d+)$/', $lastDeliveryNo, $matches)) {
                $datePart = $matches[1];
                $sequencePart = $matches[2];
                
                // Get current date
                $today = date('ymd');
                
                if ($datePart == $today) {
                    // Same day, increment sequence
                    $nextSequence = (int)$sequencePart + 1;
                    $nextSequence = str_pad($nextSequence, 3, '0', STR_PAD_LEFT);
                    return 'DEL' . $today . '-' . $nextSequence;
                } else {
                    // New day, start with sequence 001
                    return 'DEL' . $today . '-001';
                }
            } else {
                // Fallback: try to extract any numeric part
                if (preg_match('/(\d+)$/', $lastDeliveryNo, $matches)) {
                    $numericPart = (int)$matches[1] + 1;
                    return 'DEL' . $numericPart;
                }
            }
        }
        
        // No existing records or couldn't parse, create new one
        $today = date('ymd');
        return 'DEL' . $today . '-001';
    }

    // Update delivery status when invoice is paid
    public function update_delivery_status_by_invoice($invoiceNo, $settingsID)
    {
        // Get invoice payment status
        $this->db->select('Balance, AmountPaid, TotalDue');
        $this->db->from('invoice');
        $this->db->where('InvoiceNo', $invoiceNo);
        $this->db->where('settingsID', $settingsID);
        $this->db->limit(1);
        
        $invoice = $this->db->get()->row();
        
        if ($invoice) {
            $paymentStatus = 'unpaid';
            if ($invoice->Balance <= 0) {
                $paymentStatus = 'paid';
            } elseif ($invoice->AmountPaid > 0) {
                $paymentStatus = 'partial';
            }
            
            // Update all deliveries linked to this invoice
            $this->db->where('invoiceNo', $invoiceNo);
            $this->db->where('settingsID', $settingsID);
            $this->db->update('customer_deliveries', [
                'paymentStatus' => $paymentStatus,
                'amountPaid' => $invoice->AmountPaid,
                'balance' => $invoice->Balance,
                'updatedAt' => date('Y-m-d H:i:s')
            ]);
            
            return true;
        }
        
        return false;
    }

    // Update delivery payment
    public function update_delivery_payment($deliveryID, $amountPaid)
    {
        $this->db->set('amountPaid', 'amountPaid + ' . (float)$amountPaid, FALSE);
        $this->db->set('balance', 'totalAmount - (amountPaid + ' . (float)$amountPaid . ')', FALSE);

        // Update payment status based on balance
        $this->db->set('paymentStatus', 'CASE WHEN (totalAmount - (amountPaid + ' . (float)$amountPaid . ')) <= 0 THEN "paid" WHEN (amountPaid + ' . (float)$amountPaid . ') > 0 THEN "partial" ELSE "unpaid" END', FALSE);

        $this->db->where('deliveryID', $deliveryID);
        $this->db->where('settingsID', $this->session->userdata('settingsID'));
        return $this->db->update('customer_deliveries');
    }

    // Delete delivery
    public function delete_delivery($deliveryID)
    {
        $this->db->where('deliveryID', $deliveryID);
        $this->db->where('settingsID', $this->session->userdata('settingsID'));
        return $this->db->delete('customer_deliveries');
    }

    // Get grouped delivery info for validation
    public function get_grouped_delivery_info($deliveryNo, $customerName, $settingsID)
    {
        $this->db->select('deliveryStatus, amountPaid, totalAmount, balance');
        $this->db->from('customer_deliveries');
        $this->db->where('deliveryNo', $deliveryNo);
        $this->db->where('customerName', $customerName);
        $this->db->where('settingsID', $settingsID);
        $this->db->limit(1);
        
        return $this->db->get()->row();
    }

    // Delete grouped delivery (all deliveries with same deliveryNo and customer)
    public function delete_grouped_delivery($deliveryNo, $customerName, $settingsID)
    {
        // First get all delivery IDs for this group
        $this->db->select('deliveryID');
        $this->db->from('customer_deliveries');
        $this->db->where('deliveryNo', $deliveryNo);
        $this->db->where('customerName', $customerName);
        $this->db->where('settingsID', $settingsID);
        
        $deliveries = $this->db->get()->result();
        
        // Delete all delivery items for these deliveries
        foreach ($deliveries as $delivery) {
            $this->db->where('deliveryID', $delivery->deliveryID);
            $this->db->delete('customer_delivery_items');
        }
        
        // Delete all deliveries in the group
        $this->db->where('deliveryNo', $deliveryNo);
        $this->db->where('customerName', $customerName);
        $this->db->where('settingsID', $settingsID);
        return $this->db->delete('customer_deliveries');
    }

    // Update delivery item
    public function update_delivery_item($itemID, $data)
    {
        $data['updatedAt'] = date('Y-m-d H:i:s');
        $this->db->where('itemID', $itemID);
        $this->db->where('settingsID', $this->session->userdata('settingsID'));
        return $this->db->update('customer_delivery_items', $data);
    }

    // Delete delivery item
    public function delete_delivery_item($itemID)
    {
        $this->db->where('itemID', $itemID);
        $this->db->where('settingsID', $this->session->userdata('settingsID'));
        return $this->db->delete('customer_delivery_items');
    }

    // Get delivery statistics
    public function get_delivery_stats($settingsID)
    {
        $stats = [];

        try {
            // Check if table exists first
            if (!$this->db->table_exists('customer_deliveries')) {
                return [
                    'total_deliveries' => 0,
                    'total_amount' => 0,
                    'total_paid' => 0,
                    'total_balance' => 0,
                    'by_status' => [],
                    'by_payment_status' => []
                ];
            }

            // Total deliveries
            $stats['total_deliveries'] = $this->db->where('settingsID', $settingsID)
                                                  ->count_all_results('customer_deliveries');

            // By delivery status
            $this->db->select('deliveryStatus, COUNT(*) as count');
            $this->db->where('settingsID', $settingsID);
            $this->db->group_by('deliveryStatus');
            $query = $this->db->get('customer_deliveries');
            $stats['by_status'] = [];
            foreach ($query->result() as $row) {
                $stats['by_status'][$row->deliveryStatus] = $row->count;
            }

            // By payment status
            $this->db->select('paymentStatus, COUNT(*) as count');
            $this->db->where('settingsID', $settingsID);
            $this->db->group_by('paymentStatus');
            $query = $this->db->get('customer_deliveries');
            $stats['by_payment_status'] = [];
            foreach ($query->result() as $row) {
                $stats['by_payment_status'][$row->paymentStatus] = $row->count;
            }

            // Total amount
            $this->db->select('SUM(totalAmount) as total_amount, SUM(amountPaid) as total_paid, SUM(balance) as total_balance');
            $this->db->where('settingsID', $settingsID);
            $query = $this->db->get('customer_deliveries');
            $result = $query->row();
            $stats['total_amount'] = $result->total_amount ?? 0;
            $stats['total_paid'] = $result->total_paid ?? 0;
            $stats['total_balance'] = $result->total_balance ?? 0;

        } catch (Exception $e) {
            // Return empty stats on error
            return [
                'total_deliveries' => 0,
                'total_amount' => 0,
                'total_paid' => 0,
                'total_balance' => 0,
                'by_status' => [],
                'by_payment_status' => []
            ];
        }

        return $stats;
    }

    // Search deliveries
    public function search_deliveries($keyword, $settingsID)
    {
        $this->db->select('d.*');
        $this->db->from('customer_deliveries d');
        $this->db->where('d.settingsID', $settingsID);
        $this->db->group_start();
        $this->db->like('d.deliveryNo', $keyword);
        $this->db->or_like('d.customerName', $keyword);
        $this->db->or_like('d.invoiceNo', $keyword);
        $this->db->group_end();
        $this->db->order_by('d.createdAt', 'DESC');
        return $this->db->get()->result();
    }

    // Get deliveries by date range
    public function get_deliveries_by_date_range($startDate, $endDate, $settingsID)
    {
        $this->db->select('d.*');
        $this->db->from('customer_deliveries d');
        $this->db->where('d.settingsID', $settingsID);
        $this->db->where('d.deliveryDate >=', $startDate);
        $this->db->where('d.deliveryDate <=', $endDate);
        $this->db->order_by('d.deliveryDate', 'DESC');
        return $this->db->get()->result();
    }
}
