<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Purchases_model extends CI_Model
{

    public function __construct()
    {
        parent::__construct();
    }

    public function getProductNames($term, $limit = 5)
    {
        $this->db->where("type = 'standard' AND (name LIKE '%" . $term . "%' OR code LIKE '%" . $term . "%' OR  concat(name, ' (', code, ')') LIKE '%" . $term . "%')");
        $this->db->limit($limit);
        $q = $this->db->get('products');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getAllProducts()
    {
        $q = $this->db->get('products');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getProductByID($id)
    {
        $q = $this->db->get_where('products', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getProductsByCode($code)
    {
        $this->db->select('*')->from('products')->like('code', $code, 'both');
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }

    public function getProductByCode($code)
    {
        $q = $this->db->get_where('products', array('code' => $code), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getProductByName($name)
    {
        $q = $this->db->get_where('products', array('name' => $name), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getAllPurchases()
    {
        $q = $this->db->get('purchases');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }

    public function getAllPurchaseItems($purchase_id)
    {
        $this->db->select('purchase_items.*, tax_rates.code as tax_code, tax_rates.name as tax_name, tax_rates.rate as tax_rate, products.unit, products.details as details, product_variants.name as variant')
            ->join('products', 'products.id=purchase_items.product_id', 'left')
            ->join('product_variants', 'product_variants.id=purchase_items.option_id', 'left')
            ->join('tax_rates', 'tax_rates.id=purchase_items.tax_rate_id', 'left')
            ->group_by('purchase_items.id')
            ->order_by('id', 'asc');
        $q = $this->db->get_where('purchase_items', array('purchase_id' => $purchase_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getItemByID($id)
    {
        $q = $this->db->get_where('purchase_items', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getTaxRateByName($name)
    {
        $q = $this->db->get_where('tax_rates', array('name' => $name), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getPurchaseByID($id)
    {
        $q = $this->db->get_where('purchases', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getProductOptionByID($id)
    {
        $q = $this->db->get_where('product_variants', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getProductWarehouseOptionQty($option_id, $warehouse_id)
    {
        $q = $this->db->get_where('warehouses_products_variants', array('option_id' => $option_id, 'warehouse_id' => $warehouse_id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function addProductOptionQuantity($option_id, $warehouse_id, $quantity, $product_id)
    {
        if ($option = $this->getProductWarehouseOptionQty($option_id, $warehouse_id)) {
            $nq = $option->quantity + $quantity;
            if ($this->db->update('warehouses_products_variants', array('quantity' => $nq), array('option_id' => $option_id, 'warehouse_id' => $warehouse_id))) {
                return TRUE;
            }
        } else {
            if ($this->db->insert('warehouses_products_variants', array('option_id' => $option_id, 'product_id' => $product_id, 'warehouse_id' => $warehouse_id, 'quantity' => $quantity))) {
                return TRUE;
            }
        }
        return FALSE;
    }

    public function resetProductOptionQuantity($option_id, $warehouse_id, $quantity, $product_id)
    {
        if ($option = $this->getProductWarehouseOptionQty($option_id, $warehouse_id)) {
            $nq = $option->quantity - $quantity;
            if ($this->db->update('warehouses_products_variants', array('quantity' => $nq), array('option_id' => $option_id, 'warehouse_id' => $warehouse_id))) {
                return TRUE;
            }
        } else {
            $nq = 0 - $quantity;
            if ($this->db->insert('warehouses_products_variants', array('option_id' => $option_id, 'product_id' => $product_id, 'warehouse_id' => $warehouse_id, 'quantity' => $nq))) {
                return TRUE;
            }
        }
        return FALSE;
    }

    public function getOverSoldCosting($product_id)
    {
        $q = $this->db->get_where('costing', array('overselling' => 1));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function addPurchase($data, $items,$payment=null,$giaohang=array())
    {

        if ($this->db->insert('purchases', $data)) {
            $purchase_id = $this->db->insert_id();
            if ($this->site->getReference('po') == $data['reference_no']) {
                $this->site->updateReference('po');
            }
            foreach ($items as $item) {
                $item['purchase_id'] = $purchase_id;
                $this->db->insert('purchase_items', $item);
                if ($this->Settings->update_cost) {
                    $this->db->update('products', array('cost' => $item['real_unit_cost']), array('id' => $item['product_id']));
                }
                if($item['option_id']) {
                    $this->db->update('product_variants', array('cost' => $item['real_unit_cost']), array('id' => $item['option_id'], 'product_id' => $item['product_id']));
                }
                if ($data['status'] == 'received' || $data['status'] == 'returned') {
                    $this->updateAVCO(array('product_id' => $item['product_id'], 'warehouse_id' => $item['warehouse_id'], 'quantity' => $item['quantity'], 'cost' => $item['real_unit_cost']));
                }
            }

            if ($data['status'] == 'returned') {
                $this->db->update('purchases', array('return_purchase_ref' => $data['return_purchase_ref'], 'surcharge' => $data['surcharge'],'return_purchase_total' => $data['grand_total'], 'return_id' => $purchase_id), array('id' => $data['purchase_id']));
            }
			if ($data['payment_status'] == 'partial' || $data['payment_status'] == 'paid' && !empty($payment)) {
                if (empty($payment['reference_no'])) {
                    $payment['reference_no'] = $this->site->getReference('pay');
                }
                $payment['purchase_id'] = $purchase_id;
                $payment['note'].='Chi phiếu nhập hàng ['.$data['reference_no'].']';
                if ($payment['paid_by'] == 'gift_card') {
                    $this->db->update('gift_cards', array('balance' => $payment['gc_balance']), array('card_no' => $payment['cc_no']));
                    unset($payment['gc_balance']);
                    $this->db->insert('payments', $payment);
                } else {                   
                    if ((float)$payment['amount']>0) {
                        $this->db->insert('payments', $payment);    
                    }                    
                }
                if ($this->site->getReference('pay') == $payment['reference_no']) {
                    $this->site->updateReference('pay');
                }
				$this->site->syncPurchasePayments($purchase_id);
            }
			
            if ($data['status'] == 'received' || $data['status'] == 'returned') {
                $this->site->syncQuantity(NULL, $purchase_id);
            }
            foreach ($items as $item) {             
                //sysn stock, price by API lhson code 27/6/2021
                $this->site->sysnStockApiTMDT($item['product_id']);
            }
            $this->db->delete('deliveries', array('purchase_id' => $purchase_id));
            if (!empty($giaohang)&&$giaohang!=null) {             //delete                

                $giaohang['purchase_id']=$purchase_id;
                $this->addDelivery($giaohang);  

            }
            return true;
        }
        return false;
    }
     public function addDelivery($data = array())
    {
        if ($this->db->insert('deliveries', $data)) {
            if ($this->site->getReference('do') == $data['do_reference_no']) {
                $this->site->updateReference('do');
            }
            return true;
        }
        return false;
    }

    public function addPurchaseApi($data, $items,$payment=null,$giaohang=array())
    {

        if ($this->db->insert('purchases', $data)) {
            $purchase_id = $this->db->insert_id();
            if ($this->site->getReference('po') == $data['reference_no']) {
                $this->site->updateReference('po');
            }
            foreach ($items as $item) {
                $item['purchase_id'] = $purchase_id;
                $this->db->insert('purchase_items', $item);
                if ($this->Settings->update_cost) {
                    $this->db->update('products', array('cost' => $item['real_unit_cost']), array('id' => $item['product_id']));
                }
                if($item['option_id']) {
                    $this->db->update('product_variants', array('cost' => $item['real_unit_cost']), array('id' => $item['option_id'], 'product_id' => $item['product_id']));
                }
                if ($data['status'] == 'received' || $data['status'] == 'returned') {
                    $this->updateAVCO(array('product_id' => $item['product_id'], 'warehouse_id' => $item['warehouse_id'], 'quantity' => $item['quantity'], 'cost' => $item['real_unit_cost']));
                }
            }

            if ($data['status'] == 'returned') {
                $this->db->update('purchases', array('return_purchase_ref' => $data['return_purchase_ref'], 'surcharge' => $data['surcharge'],'return_purchase_total' => $data['grand_total'], 'return_id' => $purchase_id), array('id' => $data['purchase_id']));
            }
            if ($data['payment_status'] == 'partial' || $data['payment_status'] == 'paid' && !empty($payment)) {
                if (empty($payment['reference_no'])) {
                    $payment['reference_no'] = $this->site->getReference('pay');
                }
                $payment['purchase_id'] = $purchase_id;
                $payment['note'].='Chi phiếu nhập hàng ['.$data['reference_no'].']';
                if ($payment['paid_by'] == 'gift_card') {
                    $this->db->update('gift_cards', array('balance' => $payment['gc_balance']), array('card_no' => $payment['cc_no']));
                    unset($payment['gc_balance']);
                    $this->db->insert('payments', $payment);
                } else {                   
                    if ((float)$payment['amount']>0) {
                        $this->db->insert('payments', $payment);    
                    }                    
                }
                if ($this->site->getReference('pay') == $payment['reference_no']) {
                    $this->site->updateReference('pay');
                }
                $this->site->syncPurchasePayments($purchase_id);
            }
            
            if ($data['status'] == 'received' || $data['status'] == 'returned') {
                $this->site->syncQuantity(NULL, $purchase_id);
            }

            
            $this->db->delete('deliveries', array('purchase_id' => $purchase_id));
            $delivery_id=null;
            if (!empty($giaohang)&&$giaohang!=null) {             //delete                

                $giaohang['purchase_id']=$purchase_id;
                $delivery_id=$this->addDeliveryApi($giaohang);  

            }
            return ['purchase_id'=>$purchase_id,'delivery_id'=>$delivery_id];
        }
        return false;
    }
     public function addDeliveryApi($data = array())
    {
        if ($this->db->insert('deliveries', $data)) {
            if ($this->site->getReference('do') == $data['do_reference_no']) {
                $this->site->updateReference('do');
            }
            return $this->db->insert_id();
        }
        return false;
    }

    public function updatePurchase($id, $data, $items = array(),$giaohang=array())
    {
        $opurchase = $this->getPurchaseByID($id);
        $oitems = $this->getAllPurchaseItems($id);
        if ($this->db->update('purchases', $data, array('id' => $id)) && $this->db->delete('purchase_items', array('purchase_id' => $id))) {
            $purchase_id = $id;
            foreach ($items as $item) {
                $item['purchase_id'] = $id;
                $this->db->insert('purchase_items', $item);
                if ($data['status'] == 'received' || $data['status'] == 'partial') {
                    $this->updateAVCO(array('product_id' => $item['product_id'], 'warehouse_id' => $item['warehouse_id'], 'quantity' => $item['quantity'], 'cost' => $item['real_unit_cost']));
                }
            }
            $this->site->syncQuantity(NULL, NULL, $oitems);
            if ($data['status'] == 'received' || $data['status'] == 'partial') {
                $this->site->syncQuantity(NULL, $id);
                foreach ($oitems as $oitem) {
                    $this->updateAVCO(array('product_id' => $oitem->product_id, 'warehouse_id' => $oitem->warehouse_id, 'quantity' => (0-$oitem->quantity), 'cost' => $oitem->real_unit_cost));
                }
            }
             foreach ($items as $item) {             
                //sysn stock, price by API lhson code 27/6/2021
                $this->site->sysnStockApiTMDT($item['product_id']);
            }
            $this->db->delete('deliveries', array('purchase_id' => $id));
            if (!empty($giaohang)&&$giaohang!=null) {         //delete
                
                $giaohang['purchase_id']=$id;
                $this->addDelivery($giaohang);  

            }
            $this->site->syncPurchasePayments($id);
            return true;
        }

        return false;
    }

    public function updateStatus($id, $status, $note)
    {
        // $purchase = $this->getPurchaseByID($id);
        $items = $this->site->getAllPurchaseItems($id);

        if ($this->db->update('purchases', array('status' => $status, 'note' => $note), array('id' => $id))) {
            foreach ($items as $item) {
                $qb = $status == 'completed' ? ($item->quantity_balance + ($item->quantity - $item->quantity_received)) : $item->quantity_balance;
                $qr = $status == 'completed' ? $item->quantity : $item->quantity_received;
                $this->db->update('purchase_items', array('status' => $status, 'quantity_balance' => $qb, 'quantity_received' => $qr), array('id' => $item->id));
                $this->updateAVCO(array('product_id' => $item->product_id, 'warehouse_id' => $item->warehouse_id, 'quantity' => $item->quantity, 'cost' => $item->real_unit_cost));
            }
            $this->site->syncQuantity(NULL, NULL, $items);
            return true;
        }
        return false;
    }

    public function deletePurchase($id)
    {
        $purchase = $this->getPurchaseByID($id);
        $purchase_items = $this->site->getAllPurchaseItems($id);
        if ($this->db->delete('purchase_items', array('purchase_id' => $id)) && $this->db->delete('purchases', array('id' => $id))) {
            $this->db->delete('payments', array('purchase_id' => $id));

             $this->db->delete('deliveries', array('purchase_id' => $id));
             
            if ($purchase->status == 'received' || $purchase->status == 'partial') {
                foreach ($purchase_items as $oitem) {
                    $this->updateAVCO(array('product_id' => $oitem->product_id, 'warehouse_id' => $oitem->warehouse_id, 'quantity' => (0-$oitem->quantity), 'cost' => $oitem->real_unit_cost));
                    $received = $oitem->quantity_received ? $oitem->quantity_received : $oitem->quantity;
                    if ($oitem->quantity_balance < $received) {
                        $clause = array('purchase_id' => NULL, 'transfer_id' => NULL, 'product_id' => $oitem->product_id, 'warehouse_id' => $oitem->warehouse_id, 'option_id' => $oitem->option_id);
                        if ($pi = $this->site->getPurchasedItem($clause)) {
                            $quantity_balance = $pi->quantity_balance + ($oitem->quantity_balance - $received);
                            $this->db->update('purchase_items', array('quantity_balance' => $quantity_balance), $clause);
                        } else {
                            $clause['quantity'] = 0;
                            $clause['item_tax'] = 0;
                            $clause['quantity_balance'] = ($oitem->quantity_balance - $received);
                            $this->db->insert('purchase_items', $clause);
                        }
                    }
                }
            }
            $this->site->syncQuantity(NULL, NULL, $purchase_items);

            foreach ($purchase_items as $item) {             
                //sysn stock, price by API lhson code 27/6/2021
                $this->site->sysnStockApiTMDT($item->product_id);
            }

            return true;
        }
        return FALSE;
    }

    public function getWarehouseProductQuantity($warehouse_id, $product_id)
    {
        $q = $this->db->get_where('warehouses_products', array('warehouse_id' => $warehouse_id, 'product_id' => $product_id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getPurchasePayments($purchase_id)
    {
        $this->db->order_by('id', 'asc');
        $q = $this->db->get_where('payments', array('purchase_id' => $purchase_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }

    public function getPaymentByID($id)
    {
        $q = $this->db->get_where('payments', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }

        return FALSE;
    }

    public function getPaymentsForPurchase($purchase_id)
    {
        $this->db->select('payments.date, payments.paid_by, payments.amount, payments.reference_no, users.first_name, users.last_name, type')
            ->join('users', 'users.id=payments.created_by', 'left');
        $q = $this->db->get_where('payments', array('purchase_id' => $purchase_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function addPayment($data = array())
    {
        if ($this->db->insert('payments', $data)) {
            $payment_id=$this->db->insert_id();
            if ($this->site->getReference('ppay') == $data['reference_no']) {
                $this->site->updateReference('ppay');
            }
            $this->site->syncPurchasePayments($data['purchase_id']);
            
            /*luu lich su tao phieu thu lhson code 05/09/2021*/
            $order_history = $this->db->from('payments')->where('warehouse_id', $data['warehouse_id'])->where('id',$payment_id)->get()->row_array();
            $order_history['history']="Tạo mới phiếu chi";
            $order_history['history_auth']= $this->session->userdata('user_id');
            $this->db->insert('payments_history', $order_history);      
            $transaction_id=$this->db->insert_id();            

            $his_store_obj=$this->site->getWarehouseByID($data['warehouse_id']);
            $store_name_fb=$his_store_obj->name;

            $sent_rs=$this->site->sentNotificationAPI($title='Tạo mới phiếu chi thanh toán',$message=mb_strtoupper($this->session->userdata('username')).' vừa tạo mới phiếu chi thanh toán ['.mb_strtoupper($data['reference_no']).'] trị giá '.number_format($data['amount']).' tại kho '.mb_strtoupper($store_name_fb),$transaction_id,$theloai='PHIEUCHI',$permission='KETOAN',$data['warehouse_id'],true);
           
           /*luu lich su tao phieu thu lhson code 05/09/2021*/
            return true;
        }
        return false;
    }
	

    public function updatePayment($id, $data = array())
    {
        if ($this->db->update('payments', $data, array('id' => $id))) {
            $this->site->syncPurchasePayments($data['purchase_id']);

            /*luu lich su tao phieu thu lhson code 05/09/2021*/
            $order_history = $this->db->from('payments')->where('id',$id)->get()->row_array();
            $order_history['history']="Cập nhật phiếu chi";
            $order_history['history_auth']= $this->session->userdata('user_id');
            $this->db->insert('payments_history', $order_history);      
            $transaction_id=$this->db->insert_id();            

            $his_store_obj=$this->site->getWarehouseByID($data['warehouse_id']);
            $store_name_fb=$his_store_obj->name;

            $sent_rs=$this->site->sentNotificationAPI($title='Cập nhật phiếu chi thanh toán',$message=mb_strtoupper($this->session->userdata('username')).' vừa cập nhật phiếu chi thanh toán ['.mb_strtoupper($data['reference_no']).'] trị giá '.number_format($data['amount']).' tại kho '.mb_strtoupper($store_name_fb),$transaction_id,$theloai='PHIEUCHI',$permission='KETOAN',$data['warehouse_id'],true);
           
           /*luu lich su tao phieu thu lhson code 05/09/2021*/

            return true;
        }
        return false;
    }

    public function deletePayment($id)
    {
        $opay = $this->getPaymentByID($id);
        /*luu lich su tao phieu thu lhson code 05/09/2021*/
        $order_history = $this->db->from('payments')->where('id',$id)->get()->row_array();
        $order_history['history']="Xóa phiếu chi";
        $order_history['history_auth']= $this->session->userdata('user_id');
        $this->db->insert('payments_history', $order_history);      
        $transaction_id=$this->db->insert_id();            

        $his_store_obj=$this->site->getWarehouseByID($opay->warehouse_id);
        $store_name_fb=$his_store_obj->name;

        $sent_rs=$this->site->sentNotificationAPI($title='Xóa phiếu chi thanh toán',$message=mb_strtoupper($this->session->userdata('username')).' vừa xóa phiếu chi thanh toán ['.mb_strtoupper($data['reference_no']).'] trị giá '.number_format($opay->amount).' tại kho '.mb_strtoupper($store_name_fb),$transaction_id,$theloai='PHIEUCHI',$permission='KETOAN',$opay->warehouse_id,true);
       
       /*luu lich su tao phieu thu lhson code 05/09/2021*/

        if ($this->db->delete('payments', array('id' => $id))) {
            $this->site->syncPurchasePayments($opay->purchase_id);
            return true;
        }
        return FALSE;
    }
	 public function addPaymentLhson($data = array())
    {
        if ($this->db->insert('payments', $data)) {
            $payment_id=$this->db->insert_id();
            if ($this->site->getReference('thu') == $data['reference_no']) {
                $this->site->updateReference('thu');
            } 
			if($data['purchase_id']>0)
			{
				 $this->site->syncPurchasePayments($data['purchase_id']);
			}

            /*luu lich su tao phieu thu lhson code 05/09/2021*/
            $order_history = $this->db->from('payments')->where('warehouse_id', $data['warehouse_id'])->where('id',$payment_id)->get()->row_array();
            $order_history['history']="Tạo mới phiếu thu";
            $order_history['history_auth']= $this->session->userdata('user_id');
            $this->db->insert('payments_history', $order_history);      
            $transaction_id=$this->db->insert_id();            

            $his_store_obj=$this->site->getWarehouseByID($data['warehouse_id']);
            $store_name_fb=$his_store_obj->name;

            $sent_rs=$this->site->sentNotificationAPI($title='Tạo mới phiếu thu',$message=mb_strtoupper($this->session->userdata('username')).' vừa tạo mới phiếu thu ['.mb_strtoupper($data['reference_no']).'] trị giá '.number_format($data['amount']).' tại kho '.mb_strtoupper($store_name_fb),$transaction_id,$theloai='PHIEUTHU',$permission='KETOAN',$data['warehouse_id']);
           
           /*luu lich su tao phieu thu lhson code 05/09/2021*/

            return true;
        }
        return false;
    }
	public function updatePaymentLhson($id, $data = array())
    {
        if ($this->db->update('payments', $data, array('id' => $id))) {
			if($data['purchase_id']>0)
			{
				 $this->site->syncPurchasePayments($data['purchase_id']);
			}
            /*luu lich su tao phieu thu lhson code 05/09/2021*/
            $order_history = $this->db->from('payments')->where('id',$id)->get()->row_array();
            $order_history['history']="Cập nhật phiếu thu";
            $order_history['history_auth']= $this->session->userdata('user_id');
            $this->db->insert('payments_history', $order_history);      
            $transaction_id=$this->db->insert_id();            

            $his_store_obj=$this->site->getWarehouseByID($data['warehouse_id']);
            $store_name_fb=$his_store_obj->name;

            $sent_rs=$this->site->sentNotificationAPI($title='Cập nhật phiếu thu',$message=mb_strtoupper($this->session->userdata('username')).' vừa cập nhật phiếu thu ['.mb_strtoupper($data['reference_no']).'] trị giá '.number_format($data['amount']).' tại kho '.mb_strtoupper($store_name_fb),$transaction_id,$theloai='PHIEUTHU',$permission='KETOAN',$data['warehouse_id']);
           
           /*luu lich su tao phieu thu lhson code 05/09/2021*/
            return true;
        }
        return false;
    }
	public function deletePaymentLhson($id)
    {
        $opay = $this->getPaymentByID($id);
         /*luu lich su tao phieu thu lhson code 05/09/2021*/
        $order_history = $this->db->from('payments')->where('id',$id)->get()->row_array();
        $order_history['history']="Xóa phiếu thu";
        $order_history['history_auth']= $this->session->userdata('user_id');
        $this->db->insert('payments_history', $order_history);      
        $transaction_id=$this->db->insert_id();            

        $his_store_obj=$this->site->getWarehouseByID($opay->warehouse_id);
        $store_name_fb=$his_store_obj->name;

        $sent_rs=$this->site->sentNotificationAPI($title='Xóa phiếu thu',$message=mb_strtoupper($this->session->userdata('username')).' vừa xóa phiếu thu ['.mb_strtoupper($data['reference_no']).'] trị giá '.number_format($opay->amount).' tại kho '.mb_strtoupper($store_name_fb),$transaction_id,$theloai='PHIEUTHU',$permission='KETOAN',$opay->warehouse_id);
       
       /*luu lich su tao phieu thu lhson code 05/09/2021*/

        if ($this->db->delete('payments', array('id' => $id))) {
            if($opay->purchase_id>0){
				$this->site->syncPurchasePayments($opay->purchase_id);
			}
            return true;
        }
        return FALSE;
    }
    public function getProductOptions($product_id)
    {
        $q = $this->db->get_where('product_variants', array('product_id' => $product_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getProductVariantByName($name, $product_id)
    {
        $q = $this->db->get_where('product_variants', array('name' => $name, 'product_id' => $product_id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getExpenseByID($id)
    {
        $q = $this->db->get_where('expenses', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
	public function getExpensePaymentLhsonByID($id)
    {
        $chi_ncc_query="SELECT CONCAT(id,'-','NCC') as id,date,reference_no as reference,'Chi NCC' as category,amount,note,(SELECT CONCAT({$this->db->dbprefix('users')}.first_name, ' ', {$this->db->dbprefix('users')}.last_name) FROM {$this->db->dbprefix('users')} WHERE id=created_by) as user,attachment,(SELECT CONCAT({$this->db->dbprefix('companies')}.name, ' ', {$this->db->dbprefix('companies')}.phone) FROM {$this->db->dbprefix('companies')} WHERE id=id_ncc_id_kh) as nhanvien,CONCAT(id,'-','NCC') as custom FROM ".$this->db->dbprefix('payments')." WHERE id_ncc_id_kh>0 and type='sent' AND id={$id}";
		
		$q = $this->db->query($chi_ncc_query, false);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function addExpense($data = array())
    {
        if ((int)$data['warehouse_id']==0) {
            $_warehouse_id=$this->session->userdata('warehouse_id');
            if ($_warehouse_id==null) {
                $_warehouse_id=$this->Settings->default_warehouse;
            }
            $data['warehouse_id']=$_warehouse_id;
        }
        if ($this->db->insert('expenses', $data)) {
            $exp_id=$this->db->insert_id();
            if ($this->site->getReference('ex') == $data['reference']) {
                $this->site->updateReference('ex');
            }
            /*luu lich su tao phieu chi lhson code 05/09/2021*/
            $order_history = $this->db->from('expenses')->where('warehouse_id', $data['warehouse_id'])->where('id',$exp_id)->get()->row_array();
            $order_history['history']="Tạo mới phiếu chi";
            $order_history['history_auth']= $this->session->userdata('user_id');
            $this->db->insert('expenses_history', $order_history);      
            $transaction_id=$this->db->insert_id();            

            $his_store_obj=$this->site->getWarehouseByID($data['warehouse_id']);
            $store_name_fb=$his_store_obj->name;

            $sent_rs=$this->site->sentNotificationAPI($title='Tạo mới phiếu chi',$message=mb_strtoupper($this->session->userdata('username')).' vừa tạo mới phiếu chi ['.mb_strtoupper($data['reference_no']).'] trị giá '.number_format($data['amount']).' tại kho '.mb_strtoupper($store_name_fb),$transaction_id,$theloai='PHIEUCHI',$permission='KETOAN',$data['warehouse_id']);
           
           /*luu lich su tao phieu chi lhson code 05/09/2021*/

            return true;
        }
        return false;
    }

    public function updateExpense($id, $data = array())
    {
        if ((int)$data['warehouse_id']==0) {
            $_warehouse_id=$this->session->userdata('warehouse_id');
            if ($_warehouse_id==null) {
                $_warehouse_id=$this->Settings->default_warehouse;
            }
            $data['warehouse_id']=$_warehouse_id;
        }
        if ($this->db->update('expenses', $data, array('id' => $id))) {

            /*luu lich su tao phieu chi lhson code 05/09/2021*/
            $order_history = $this->db->from('expenses')->where('id',$id)->get()->row_array();
            $order_history['history']="Cập nhật phiếu chi";
            $order_history['history_auth']= $this->session->userdata('user_id');
            $this->db->insert('expenses_history', $order_history);      
            $transaction_id=$this->db->insert_id();            

            $his_store_obj=$this->site->getWarehouseByID($order_history['warehouse_id']);
            $store_name_fb=$his_store_obj->name;

            $sent_rs=$this->site->sentNotificationAPI($title='Cập nhật phiếu chi',$message=mb_strtoupper($this->session->userdata('username')).' vừa cập nhật phiếu chi ['.mb_strtoupper($order_history['reference_no']).'] trị giá '.number_format($order_history['amount']).' tại kho '.mb_strtoupper($store_name_fb),$transaction_id,$theloai='PHIEUCHI',$permission='KETOAN',$order_history['warehouse_id']);
           
           /*luu lich su tao phieu chi lhson code 05/09/2021*/

            return true;
        }
        return false;
    }

    public function deleteExpense($id)
    {
        /*luu lich su tao phieu chi lhson code 05/09/2021*/
        $order_history = $this->db->from('expenses')->where('id',$id)->get()->row_array();
        $order_history['history']="Xóa phiếu chi";
        $order_history['history_auth']= $this->session->userdata('user_id');
        $this->db->insert('expenses_history', $order_history);      
        $transaction_id=$this->db->insert_id();            

        $his_store_obj=$this->site->getWarehouseByID($order_history['warehouse_id']);
        $store_name_fb=$his_store_obj->name;

        $sent_rs=$this->site->sentNotificationAPI($title='Xóa phiếu chi',$message=mb_strtoupper($this->session->userdata('username')).' vừa xóa phiếu chi ['.mb_strtoupper($order_history['reference_no']).'] trị giá '.number_format($order_history['amount']).' tại kho '.mb_strtoupper($store_name_fb),$transaction_id,$theloai='PHIEUCHI',$permission='KETOAN',$order_history['warehouse_id']);
       
       /*luu lich su tao phieu chi lhson code 05/09/2021*/

        if ($this->db->delete('expenses', array('id' => $id))) {
            return true;
        }
        return FALSE;
    }

    public function getQuoteByID($id)
    {
        $q = $this->db->get_where('quotes', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getAllQuoteItems($quote_id)
    {
        $q = $this->db->get_where('quote_items', array('quote_id' => $quote_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getReturnByID($id)
    {
        $q = $this->db->get_where('return_purchases', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getAllReturnItems($return_id)
    {
        $this->db->select('return_purchase_items.*, products.details as details, product_variants.name as variant')
            ->join('products', 'products.id=return_purchase_items.product_id', 'left')
            ->join('product_variants', 'product_variants.id=return_purchase_items.option_id', 'left')
            ->group_by('return_purchase_items.id')
            ->order_by('id', 'asc');
        $q = $this->db->get_where('return_purchase_items', array('return_id' => $return_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }

    public function getPurcahseItemByID($id)
    {
        $q = $this->db->get_where('purchase_items', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function returnPurchase($data = array(), $items = array())
    {

        $purchase_items = $this->site->getAllPurchaseItems($data['purchase_id']);

        if ($this->db->insert('return_purchases', $data)) {
            $return_id = $this->db->insert_id();
            if ($this->site->getReference('rep') == $data['reference_no']) {
                $this->site->updateReference('rep');
            }
            foreach ($items as $item) {
                $item['return_id'] = $return_id;
                $this->db->insert('return_purchase_items', $item);

                if ($purchase_item = $this->getPurcahseItemByID($item['purchase_item_id'])) {
                    if ($purchase_item->quantity == $item['quantity']) {
                        $this->db->delete('purchase_items', array('id' => $item['purchase_item_id']));
                    } else {
                        $nqty = $purchase_item->quantity - $item['quantity'];
                        $bqty = $purchase_item->quantity_balance - $item['quantity'];
                        $rqty = $purchase_item->quantity_received - $item['quantity'];
                        $tax = $purchase_item->unit_cost - $purchase_item->net_unit_cost;
                        $discount = $purchase_item->item_discount / $purchase_item->quantity;
                        $item_tax = $tax * $nqty;
                        $item_discount = $discount * $nqty;
                        $subtotal = $purchase_item->unit_cost * $nqty;
                        $this->db->update('purchase_items', array('quantity' => $nqty, 'quantity_balance' => $bqty, 'quantity_received' => $rqty, 'item_tax' => $item_tax, 'item_discount' => $item_discount, 'subtotal' => $subtotal), array('id' => $item['purchase_item_id']));
                    }

                }
            }
            $this->calculatePurchaseTotals($data['purchase_id'], $return_id, $data['surcharge']);
            $this->site->syncQuantity(NULL, NULL, $purchase_items);
            $this->site->syncQuantity(NULL, $data['purchase_id']);
            return true;
        }
        return false;
    }

    public function calculatePurchaseTotals($id, $return_id, $surcharge)
    {
        $purchase = $this->getPurchaseByID($id);
        $items = $this->getAllPurchaseItems($id);
        if (!empty($items)) {
            $total = 0;
            $product_tax = 0;
            $order_tax = 0;
            $product_discount = 0;
            $order_discount = 0;
            foreach ($items as $item) {
                $product_tax += $item->item_tax;
                $product_discount += $item->item_discount;
                $total += $item->net_unit_cost * $item->quantity;
            }
            if ($purchase->order_discount_id) {
                $percentage = '%';
                $order_discount_id = $purchase->order_discount_id;
                $opos = strpos($order_discount_id, $percentage);
                if ($opos !== false) {
                    $ods = explode("%", $order_discount_id);
                    $order_discount = (($total + $product_tax) * (Float)($ods[0])) / 100;
                } else {
                    $order_discount = $order_discount_id;
                }
            }
            if ($purchase->order_tax_id) {
                $order_tax_id = $purchase->order_tax_id;
                if ($order_tax_details = $this->site->getTaxRateByID($order_tax_id)) {
                    if ($order_tax_details->type == 2) {
                        $order_tax = $order_tax_details->rate;
                    }
                    if ($order_tax_details->type == 1) {
                        $order_tax = (($total + $product_tax - $order_discount) * $order_tax_details->rate) / 100;
                    }
                }
            }
            $total_discount = $order_discount + $product_discount;
            $total_tax = $product_tax + $order_tax;
            $grand_total = $total + $total_tax + $purchase->shipping - $order_discount + $surcharge;
            $data = array(
                'total' => $total,
                'product_discount' => $product_discount,
                'order_discount' => $order_discount,
                'total_discount' => $total_discount,
                'product_tax' => $product_tax,
                'order_tax' => $order_tax,
                'total_tax' => $total_tax,
                'grand_total' => $grand_total,
                'return_id' => $return_id,
                'surcharge' => $surcharge
            );

            if ($this->db->update('purchases', $data, array('id' => $id))) {
                return true;
            }
        } else {
            $this->db->delete('purchases', array('id' => $id));
        }
        return FALSE;
    }

    public function getExpenseCategories()
    {
        $q = $this->db->get_where('expense_categories',array("type"=>0));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
	 public function getExpenseCategoriesThu()
    {
        $q = $this->db->get_where('expense_categories',array("type"=>1));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getExpenseCategoryByID($id)
    {
        $q = $this->db->get_where("expense_categories", array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function updateAVCO($data)
    {
        if ($wp_details = $this->getWarehouseProductQuantity($data['warehouse_id'], $data['product_id'])) {
            $total_cost = (($wp_details->quantity * $wp_details->avg_cost) + ($data['quantity'] * $data['cost']));
            $total_quantity = $wp_details->quantity + $data['quantity'];
            if (!empty($total_quantity)) {
                $avg_cost = ($total_cost / $total_quantity);
                $this->db->update('warehouses_products', array('avg_cost' => $avg_cost), array('product_id' => $data['product_id'], 'warehouse_id' => $data['warehouse_id']));
            }
        } else {
            $this->db->insert('warehouses_products', array('product_id' => $data['product_id'], 'warehouse_id' => $data['warehouse_id'], 'avg_cost' => $data['cost'], 'quantity' => 0));
        }
        
    }
    function  removePaymentByAPI($id=null)
    {
        $this->db->delete('payments', array('purchase_id' => $id));
    }
}
