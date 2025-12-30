<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Sales_model extends CI_Model
{

    public function __construct()
    {
        parent::__construct();
    }

    public function getProductNames($term, $warehouse_id, $limit = 5)
    {
        $wp = "( SELECT product_id, warehouse_id, quantity as quantity from {$this->db->dbprefix('warehouses_products')} ) FWP";

        $this->db->select('products.*, FWP.quantity as quantity, categories.id as category_id, categories.name as category_name', FALSE)
            ->join($wp, 'FWP.product_id=products.id', 'left')
            ->join('categories', 'categories.id=products.category_id', 'left')
            ->group_by('products.id');
        if ($this->Settings->overselling) {
            $this->db->where("({$this->db->dbprefix('products')}.name LIKE '%" . $term . "%' OR {$this->db->dbprefix('products')}.code LIKE '%" . $term . "%' OR  concat({$this->db->dbprefix('products')}.name, ' (', {$this->db->dbprefix('products')}.code, ')') LIKE '%" . $term . "%')");
        } else {
            $this->db->where("(products.track_quantity = 0 OR FWP.quantity > 0) AND FWP.warehouse_id = '" . $warehouse_id . "' AND "
                . "({$this->db->dbprefix('products')}.name LIKE '%" . $term . "%' OR {$this->db->dbprefix('products')}.code LIKE '%" . $term . "%' OR  concat({$this->db->dbprefix('products')}.name, ' (', {$this->db->dbprefix('products')}.code, ')') LIKE '%" . $term . "%')");

        }
        $this->db->limit($limit);
        $q = $this->db->get('products');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }
public function getProductNamesById($term)

    {

        $wp = "( SELECT product_id, warehouse_id, quantity as quantity from {$this->db->dbprefix('warehouses_products')} ) FWP";



        $this->db->select('products.*, FWP.quantity as quantity, categories.id as category_id, categories.name as category_name', FALSE)

            ->join($wp, 'FWP.product_id=products.id', 'left')

            ->join('categories', 'categories.id=products.category_id', 'left')

            ->group_by('products.id');

            

        $this->db->where("({$this->db->dbprefix('products')}.id='" . $term . "')");

        

        

        $q = $this->db->get('products');

        if ($q->num_rows() > 0) {

            foreach (($q->result()) as $row) {

                $data[] = $row;

            }

            return $data;

        }

    }
    public function getProductComboItems($pid, $warehouse_id = NULL)
    {
        $this->db->select('products.id as id, combo_items.item_code as code, combo_items.quantity as qty, products.name as name,products.type as type, warehouses_products.quantity as quantity')
            ->join('products', 'products.code=combo_items.item_code', 'left')
            ->join('warehouses_products', 'warehouses_products.product_id=products.id', 'left')
            ->group_by('combo_items.id');
        if($warehouse_id) {
            $this->db->where('warehouses_products.warehouse_id', $warehouse_id);
        }
        $q = $this->db->get_where('combo_items', array('combo_items.product_id' => $pid));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }

            return $data;
        }
        return FALSE;
    }

    public function getProductByCode($code)
    {
        $q = $this->db->get_where('products', array('code' => $code), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function syncQuantity($sale_id)
    {
        if ($sale_items = $this->getAllInvoiceItems($sale_id)) {
            foreach ($sale_items as $item) {
                $this->site->syncProductQty($item->product_id, $item->warehouse_id);
                if (isset($item->option_id) && !empty($item->option_id)) {
                    $this->site->syncVariantQty($item->option_id, $item->warehouse_id);
                }
            }
        }
    }

    public function getProductQuantity($product_id, $warehouse)
    {
        $q = $this->db->get_where('warehouses_products', array('product_id' => $product_id, 'warehouse_id' => $warehouse), 1);
        if ($q->num_rows() > 0) {
            return $q->row_array(); //$q->row();
        }
        return FALSE;
    }

    public function getProductOptions($product_id, $warehouse_id, $all = NULL)
    {
        $wpv = "( SELECT option_id, warehouse_id, quantity from {$this->db->dbprefix('warehouses_products_variants')} WHERE product_id = {$product_id}) FWPV";
        $this->db->select('product_variants.id as id, product_variants.name as name, product_variants.price as price, product_variants.quantity as total_quantity, FWPV.quantity as quantity', FALSE)
            ->join($wpv, 'FWPV.option_id=product_variants.id', 'left')
            //->join('warehouses', 'warehouses.id=product_variants.warehouse_id', 'left')
            ->where('product_variants.product_id', $product_id)
            ->group_by('product_variants.id');

        if (! $this->Settings->overselling && ! $all) {
            $this->db->where('FWPV.warehouse_id', $warehouse_id);
            $this->db->where('FWPV.quantity >', 0);
        }
        $q = $this->db->get('product_variants');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getProductVariants($product_id)
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

    public function getItemByID($id)
    {

        $q = $this->db->get_where('sale_items', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }

        return FALSE;
    }

    public function getAllInvoiceItems($sale_id, $return_id = NULL)
    {
        $this->db->select('sale_items.*, tax_rates.code as tax_code, tax_rates.name as tax_name, tax_rates.rate as tax_rate, products.image, products.details as details, product_variants.name as variant,products.baohanh as baohanh')
            ->join('products', 'products.id=sale_items.product_id', 'left')
            ->join('product_variants', 'product_variants.id=sale_items.option_id', 'left')
            ->join('tax_rates', 'tax_rates.id=sale_items.tax_rate_id', 'left')
            ->group_by('sale_items.id')
            ->order_by('id', 'asc');
        if ($sale_id && !$return_id) {
            $this->db->where('sale_id', $sale_id);
        } elseif ($return_id) {
            $this->db->where('sale_id', $return_id);
        }
        $q = $this->db->get('sale_items');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
	public function getAllInvoiceItemsNoReturn($sale_id, $return_id = NULL)
    {
        $this->db->select('sale_items.*, tax_rates.code as tax_code, tax_rates.name as tax_name, tax_rates.rate as tax_rate, products.image, products.details as details, product_variants.name as variant')
            ->join('products', 'products.id=sale_items.product_id', 'left')
            ->join('product_variants', 'product_variants.id=sale_items.option_id', 'left')
            ->join('tax_rates', 'tax_rates.id=sale_items.tax_rate_id', 'left')
            ->group_by('sale_items.id')
            ->order_by('id', 'asc');
        if ($sale_id && !$return_id) {
            $this->db->where('sale_id', $sale_id);
        } elseif ($return_id) {
            $this->db->where('sale_id', $return_id);
        }
		$this->db->where('sale_items.quantity>',0);
        $q = $this->db->get('sale_items');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
	public function getAllInvoiceItemsReturn($sale_id, $return_id = NULL)
    {
        $this->db->select('sale_items.*, tax_rates.code as tax_code, tax_rates.name as tax_name, tax_rates.rate as tax_rate, products.image, products.details as details, product_variants.name as variant')
            ->join('products', 'products.id=sale_items.product_id', 'left')
            ->join('product_variants', 'product_variants.id=sale_items.option_id', 'left')
            ->join('tax_rates', 'tax_rates.id=sale_items.tax_rate_id', 'left')
            ->group_by('sale_items.id')
            ->order_by('id', 'asc');
        if ($sale_id && !$return_id) {
            $this->db->where('sale_id', $sale_id);
        } elseif ($return_id) {
            $this->db->where('sale_id', $return_id);
        }
		$this->db->where('sale_items.quantity<',0);
        $q = $this->db->get('sale_items');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
    public function getAllInvoiceItemsWithDetails($sale_id)
    {
        $this->db->select('sale_items.*, products.details, product_variants.name as variant');
        $this->db->join('products', 'products.id=sale_items.product_id', 'left')
        ->join('product_variants', 'product_variants.id=sale_items.option_id', 'left')
        ->group_by('sale_items.id');
        $this->db->order_by('id', 'asc');
        $q = $this->db->get_where('sale_items', array('sale_id' => $sale_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }

    public function getInvoiceByID($id)
    {
        $q = $this->db->get_where('sales', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    public function getAllInvoiceByCustomerID($id)
    {
        $q = $this->db->get_where('sales', array('customer_id' => $id), 1);
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
        $q = $this->db->get_where('sales', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getReturnBySID($sale_id)
    {
        $q = $this->db->get_where('sales', array('sale_id' => $sale_id), 1);
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

    public function updateOptionQuantity($option_id, $quantity)
    {
        if ($option = $this->getProductOptionByID($option_id)) {
            $nq = $option->quantity - $quantity;
            if ($this->db->update('product_variants', array('quantity' => $nq), array('id' => $option_id))) {
                return TRUE;
            }
        }
        return FALSE;
    }

    public function addOptionQuantity($option_id, $quantity)
    {
        if ($option = $this->getProductOptionByID($option_id)) {
            $nq = $option->quantity + $quantity;
            if ($this->db->update('product_variants', array('quantity' => $nq), array('id' => $option_id))) {
                return TRUE;
            }
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

    public function updateProductOptionQuantity($option_id, $warehouse_id, $quantity, $product_id)
    {
        if ($option = $this->getProductWarehouseOptionQty($option_id, $warehouse_id)) {
            $nq = $option->quantity - $quantity;
            if ($this->db->update('warehouses_products_variants', array('quantity' => $nq), array('option_id' => $option_id, 'warehouse_id' => $warehouse_id))) {
                $this->site->syncVariantQty($option_id, $warehouse_id);
                return TRUE;
            }
        } else {
            $nq = 0 - $quantity;
            if ($this->db->insert('warehouses_products_variants', array('option_id' => $option_id, 'product_id' => $product_id, 'warehouse_id' => $warehouse_id, 'quantity' => $nq))) {
                $this->site->syncVariantQty($option_id, $warehouse_id);
                return TRUE;
            }
        }
        return FALSE;
    }

    public function addSale($data = array(), $items = array(), $payment = array(), $si_return = array(),$lhso_return=array(),$giaohang=array(),$return_api=false)
    {
        $cost = $this->site->costing($items);
		//$this->sma->print_arrays($cost); 
		
        if ($this->db->insert('sales', $data)) {
            $sale_id = $this->db->insert_id();
            if ($this->site->getReference('so') == $data['reference_no']) {
                $this->site->updateReference('so');
            }
            foreach ($items as $item) { 
								
				
				//lhson code unset return_id
				unset($item['return_id']);
                $item['sale_id'] = $sale_id;
                $this->db->insert('sale_items', $item);
                $sale_item_id = $this->db->insert_id();
                if ($data['sale_status'] == 'completed') {

                    $item_costs = $this->site->item_costing($item);
                    foreach ($item_costs as $item_cost) {
                        if (isset($item_cost['date'])) {
                            $item_cost['sale_item_id'] = $sale_item_id;
                            $item_cost['sale_id'] = $sale_id;
                            $item_cost['date'] = date('Y-m-d', strtotime($data['date']));
                            if(! isset($item_cost['pi_overselling'])) {
                                $this->db->insert('costing', $item_cost);
                            }
                        } else {
                            foreach ($item_cost as $ic) {
                                $ic['sale_item_id'] = $sale_item_id;
                                $ic['sale_id'] = $sale_id;
                                $ic['date'] = date('Y-m-d', strtotime($data['date']));
                                if(! isset($ic['pi_overselling'])) {
									if(count($ic)==3){
										$this->db->insert('costing', $ic);
									}
                                }
                            }
                        }
                    }

                }
            }
            if ($data['sale_status'] == 'completed') {
                $this->site->syncPurchaseItems($cost);
            }
            if (!empty($giaohang)&&$giaohang!=null) {
                
                $giaohang['sale_id']=$sale_id;
                $this->addDelivery($giaohang); 

            }    
            if (!empty($si_return)) {
                foreach ($si_return as $return_item) { 
                    $product = $this->site->getProductByID($return_item['product_id']);
                    if ($product->type == 'combo') {
                        $combo_items = $this->site->getProductComboItems($return_item['product_id'], $return_item['warehouse_id']);
                        foreach ($combo_items as $combo_item) {
                            $this->updateCostingLine($return_item['id'], $combo_item->id, $return_item['quantity']);
                            $this->updatePurchaseItem(NULL,($return_item['quantity']*$combo_item->qty), NULL, $combo_item->id, $return_item['warehouse_id']);
                        }
                    } else {
                        $this->updateCostingLine($return_item['id'], $return_item['product_id'], $return_item['quantity']);
                        $this->updatePurchaseItem(NULL, $return_item['quantity'], $return_item['id']);
                    }
                }
                $this->db->update('sales', array('return_sale_ref' => $data['return_sale_ref'], 'surcharge' => $data['surcharge'],'return_sale_total' => $data['grand_total'], 'return_id' => $sale_id), array('id' => $data['sale_id']));
            }
			
			/*if (!empty($lhso_return)) {
                foreach ($lhso_return as $return_item) {
                    $product = $this->site->getProductByID($return_item['product_id']);
                    if ($product->type == 'combo') {
                        $combo_items = $this->site->getProductComboItems($return_item['product_id'], $return_item['warehouse_id']);
                        foreach ($combo_items as $combo_item) {
                            $this->updatePurchaseItem(NULL,($return_item['quantity']*$combo_item->qty), NULL, $combo_item->id, $return_item['warehouse_id']);
                        }
                    } else {
						//lhson code return
                        $this->updatePurchaseItem(NULL, $return_item['quantity'], NULL, $return_item['product_id'],$return_item['warehouse_id']);
                    }
                }
            }*/			 

            if ($data['payment_status'] == 'partial' || $data['payment_status'] == 'paid' && !empty($payment)) {
                if (empty($payment['reference_no'])) {
                    $payment['reference_no'] = $this->site->getReference('pay');
                }
                $payment['sale_id'] = $sale_id;
                if ((int)$payment['warehouse_id']==0) {
                    $_warehouse_id=$this->session->userdata('warehouse_id');
                    if ($_warehouse_id==null) {
                        $_warehouse_id=$this->Settings->default_warehouse;
                    }
                    $payment['warehouse_id']=$_warehouse_id;
                }

                if ($payment['paid_by'] == 'gift_card') {
                    $this->db->update('gift_cards', array('balance' => $payment['gc_balance']), array('card_no' => $payment['cc_no']));
                    unset($payment['gc_balance']);
                    $this->db->insert('payments', $payment);

                    $payment_id=$this->db->insert_id();
                    /*luu lich su tao phieu thu lhson code 05/09/2021*/
                    $order_history = $this->db->from('payments')->where('warehouse_id', $payment['warehouse_id'])->where('id',$payment_id)->get()->row_array();
                    $order_history['history']="Tạo mới phiếu thu";
                    $order_history['history_auth']= $this->session->userdata('user_id');
                    $this->db->insert('payments_history', $order_history);      
                    $transaction_id=$this->db->insert_id();            

                    $his_store_obj=$this->site->getWarehouseByID($payment['warehouse_id']);
                    $store_name_fb=$his_store_obj->name;

                    $sent_rs=$this->site->sentNotificationAPI($title='Tạo mới phiếu thu',$message=mb_strtoupper($this->session->userdata('username')).' vừa tạo mới phiếu thu ['.mb_strtoupper($payment['reference_no']).'] trị giá '.number_format($payment['amount']).' tại kho '.mb_strtoupper($store_name_fb),$transaction_id,$theloai='PHIEUTHU',$permission='KETOAN',$payment['warehouse_id']);
                   
                   /*luu lich su tao phieu thu lhson code 05/09/2021*/

                } else {
                    if ($payment['paid_by'] == 'deposit') {
                        $customer = $this->site->getCompanyByID($data['customer_id']);
                        $this->db->update('companies', array('deposit_amount' => ($customer->deposit_amount-$payment['amount'])), array('id' => $customer->id));
                    }
                    if ((float)$payment['amount']>0) {
                        $this->db->insert('payments', $payment);    

                        $payment_id=$this->db->insert_id();
                        /*luu lich su tao phieu thu lhson code 05/09/2021*/
                        $order_history = $this->db->from('payments')->where('warehouse_id', $payment['warehouse_id'])->where('id',$payment_id)->get()->row_array();
                        $order_history['history']="Tạo mới phiếu thu";
                        $order_history['history_auth']= $this->session->userdata('user_id');
                        $this->db->insert('payments_history', $order_history);      
                        $transaction_id=$this->db->insert_id();            

                        $his_store_obj=$this->site->getWarehouseByID($payment['warehouse_id']);
                        $store_name_fb=$his_store_obj->name;

                        $sent_rs=$this->site->sentNotificationAPI($title='Tạo mới phiếu thu',$message=mb_strtoupper($this->session->userdata('username')).' vừa tạo mới phiếu thu ['.mb_strtoupper($payment['reference_no']).'] trị giá '.number_format($payment['amount']).' tại kho '.mb_strtoupper($store_name_fb),$transaction_id,$theloai='PHIEUTHU',$permission='KETOAN',$payment['warehouse_id']);
                       
                       /*luu lich su tao phieu thu lhson code 05/09/2021*/

                    }  
                }
                if ($this->site->getReference('pay') == $payment['reference_no']) {
                    $this->site->updateReference('pay');
                }
                $this->site->syncSalePayments($sale_id);

            }            
            if (!$return_api) {
                $this->site->syncQuantity($sale_id);

                foreach ($items as $item) {             
                    //sysn stock, price by API lhson code 27/6/2021
                    $this->site->sysnStockApiTMDT($item['product_id']);
                }
                $this->sma->update_award_points($data['grand_total'], $data['customer_id'], $data['created_by'],null,$data['total_weight']);
                
            
            /*luu lich su tao hoa don lhson code 05/09/2021*/
            $order_history = $this->db->from('sales')->where('warehouse_id', $data['warehouse_id'])->where('id',$sale_id)->get()->row_array();
            $order_history['history']="Tạo mới hóa đơn";
            $order_history['history_auth']= $this->session->userdata('user_id');
            $this->db->insert('sales_history', $order_history);      
            $transaction_id=$this->db->insert_id();
            //query all sale_items
            $query_all_item_history="insert into scodeweb_sale_items_history select NULL,scodeweb_sale_items.* FROM scodeweb_sale_items WHERE sale_id=".$sale_id." AND warehouse_id=".$data['warehouse_id'];
            $query = $this->db->query($query_all_item_history);    

            $his_store_obj=$this->site->getWarehouseByID($data['warehouse_id']);
            $store_name_fb=$his_store_obj->name;

            $sent_rs=$this->site->sentNotificationAPI($title='Tạo mới hóa đơn',$message=mb_strtoupper($this->session->userdata('username')).' vừa tạo mới hóa đơn ['.mb_strtoupper($data['reference_no']).'] trị giá '.number_format($data['grand_total']).' tại kho '.mb_strtoupper($store_name_fb),$transaction_id,$theloai='HOADON',$permission='ALLADV',$data['warehouse_id']);
           }
           //echo var_dump($sent_rs);
           /*luu lich su tao hoa don lhson code 05/09/2021*/

           //exit('22');
            
            if ($return_api) {
                return $sale_id;
            }else{
                return true;    
            }           

        }

        return false;
    }

    public function updateSale($id, $data, $items = array(),$giaohang=array())
    {
        $this->resetSaleActions($id, FALSE, TRUE);

        if ($data['sale_status'] == 'completed') {
            $cost = $this->site->costing($items);
        }

        // $this->sma->print_arrays($cost);

        if ($this->db->update('sales', $data, array('id' => $id)) && 
            $this->db->delete('sale_items', array('sale_id' => $id)) && 
            $this->db->delete('costing', array('sale_id' => $id))) {

            foreach ($items as $item) {

                $item['sale_id'] = $id;
                $this->db->insert('sale_items', $item);
                $sale_item_id = $this->db->insert_id();
                if ($data['sale_status'] == 'completed' && $this->site->getProductByID($item['product_id'])) {
                    $item_costs = $this->site->item_costing($item);
                    foreach ($item_costs as $item_cost) {
                        if (isset($item_cost['date'])) {
                            $item_cost['sale_item_id'] = $sale_item_id;
                            $item_cost['sale_id'] = $id;
                            if(! isset($item_cost['pi_overselling'])) {
                                $this->db->insert('costing', $item_cost);
                            }
                        } else {
                            foreach ($item_cost as $ic) {
                                $ic['sale_item_id'] = $sale_item_id;
                                $ic['sale_id'] = $id;
                                if(!isset($item_cost['pi_overselling'])) {									
                                    $this->db->insert('costing', $ic);
                                }
                            }
                        }
                    }
                }

            }

            if ($data['sale_status'] == 'completed') {
                if($cost){
					$this->site->syncPurchaseItems($cost);
				}
            }
            $giaohang_obj=$this->getDeliveryBySaleID($id);
            if (!empty($giaohang_obj)) {
                $this->db->delete('deliveries', array('sale_id' => $id)); 
                if (!empty($giaohang)&&$giaohang!=null) {
                    $giaohang['id']=$giaohang_obj->id;   
                }
            }  
           
            $this->site->syncSalePayments($id);
            $this->site->syncQuantity($id);
            foreach ($items as $item) {             
                //sysn stock, price by API lhson code 27/6/2021
                $this->site->sysnStockApiTMDT($item['product_id']);                
            }
            if (!isset($data['created_by'])) {
                $data['created_by']=1;
            }
            $this->sma->update_award_points($data['grand_total'], $data['customer_id'], $data['created_by'],null,$data['total_weight']);
            
            //update satus by API Lhson code 30/6/2021
            $this->site->post_carts_update($id);

             if (!empty($giaohang)&&$giaohang!=null) {                               
                $giaohang['sale_id']=$id;
                $gh=$this->addDelivery($giaohang);
                echo var_dump($gh);
            }    

            /*luu lich su tao hoa don lhson code 05/09/2021*/
            $order_history = $this->db->from('sales')->where('warehouse_id', $data['warehouse_id'])->where('id',$id)->get()->row_array();
            $order_history['history']="Cập nhật hóa đơn";
            $order_history['history_auth']= $this->session->userdata('user_id');
            $this->db->insert('sales_history', $order_history);      
            $transaction_id=$this->db->insert_id();
            //query all sale_items
            $query_all_item_history="insert into scodeweb_sale_items_history select NULL,scodeweb_sale_items.* FROM scodeweb_sale_items WHERE sale_id=".$id." AND warehouse_id=".$data['warehouse_id'];
            $query = $this->db->query($query_all_item_history);    

            $his_store_obj=$this->site->getWarehouseByID($data['warehouse_id']);
            $store_name_fb=$his_store_obj->name;

            $sent_rs=$this->site->sentNotificationAPI($title='Cập nhật hóa đơn',$message=mb_strtoupper($this->session->userdata('username')).' vừa cập nhật hóa đơn ['.mb_strtoupper($data['reference_no']).'] trị giá '.number_format($data['grand_total']).' tại kho '.mb_strtoupper($store_name_fb),$transaction_id,$theloai='HOADON',$permission='ALLADV',$data['warehouse_id']);
           
           /*luu lich su tao hoa don lhson code 05/09/2021*/

            return true;

        }
        return false;
    }

    public function updateStatus($id, $status, $note)
    {

        $sale = $this->getInvoiceByID($id);
        $items = $this->getAllInvoiceItems($id);
        $cost = array();
        if ($status == 'completed' && $status != $sale->sale_status) {
            foreach ($items as $item) {
                $items_array[] = (array) $item;
            }
            $cost = $this->site->costing($items_array);
        }

        if ($this->db->update('sales', array('sale_status' => $status, 'note' => $note), array('id' => $id))) {

            if ($status == 'completed' && $status != $sale->sale_status) {

                foreach ($items as $item) {
                    $item = (array) $item;
                    if ($this->site->getProductByID($item['product_id'])) {
                        $item_costs = $this->site->item_costing($item);
                        foreach ($item_costs as $item_cost) {
                            $item_cost['sale_item_id'] = $item['id'];
                            $item_cost['sale_id'] = $id;
                            if(! isset($item_cost['pi_overselling'])) {
                                $this->db->insert('costing', $item_cost);
                            }
                        }
                    }
                }

            } elseif ($status != 'completed' && $sale->sale_status == 'completed') {
                $this->resetSaleActions($id);
            }

            if (!empty($cost)) { $this->site->syncPurchaseItems($cost); }

            //update satus by API Lhson code 30/6/2021
            $this->site->post_carts_update($id);

            /*luu lich su tao hoa don lhson code 05/09/2021*/
            $order_history = $this->db->from('sales')->where('warehouse_id', $sale->warehouse_id)->where('id',$id)->get()->row_array();
            $order_history['history']="Cập nhật hóa đơn";
            
            $order_history['history_auth']= $this->session->userdata('user_id');
            $this->db->insert('sales_history', $order_history);      
            $transaction_id=$this->db->insert_id();
            //query all sale_items
            $query_all_item_history="insert into scodeweb_sale_items_history select NULL,scodeweb_sale_items.* FROM scodeweb_sale_items WHERE sale_id=".$id." AND warehouse_id=".$sale->warehouse_id;
            $query = $this->db->query($query_all_item_history);    

            $his_store_obj=$this->site->getWarehouseByID($sale->warehouse_id);
            $store_name_fb=$his_store_obj->name;

            $sent_rs=$this->site->sentNotificationAPI($title='Cập nhật hóa đơn',$message=mb_strtoupper($this->session->userdata('username')).' vừa cập nhật hóa đơn ['.mb_strtoupper($sale->reference_no).'] trị giá '.number_format($sale->grand_total).' tại kho '.mb_strtoupper($store_name_fb),$transaction_id,$theloai='HOADON',$permission='ALLADV',$sale->warehouse_id);
           
           /*luu lich su tao hoa don lhson code 05/09/2021*/
            return true;
        }

        return false;
    }

    public function deleteSale($id)
    {
        
        
        $order=$this->getInvoiceByID($id);
        $sale_items = $this->resetSaleActions($id);
		/*luu lich su tao hoa don lhson code 05/09/2021*/
        $order_history = $this->db->from('sales')->where('warehouse_id', $order->warehouse_id)->where('id',$id)->get()->row_array();
        $order_history['history']="Hủy hóa đơn";
        $order_history['history_auth']= $this->session->userdata('user_id');
        $this->db->insert('sales_history', $order_history);      
        $transaction_id=$this->db->insert_id();
        //query all sale_items
        $query_all_item_history="insert into scodeweb_sale_items_history select NULL,scodeweb_sale_items.* FROM scodeweb_sale_items WHERE sale_id=".$id." AND warehouse_id=".$order->warehouse_id;
        $query = $this->db->query($query_all_item_history);    

        $his_store_obj=$this->site->getWarehouseByID($order->warehouse_id);
        $store_name_fb=$his_store_obj->name;

        $sent_rs=$this->site->sentNotificationAPI($title='Hủy hóa đơn',$message=mb_strtoupper($this->session->userdata('username')).' vừa hủy hóa đơn ['.mb_strtoupper($order->reference_no).'] trị giá '.number_format($order->grand_total).' tại kho '.mb_strtoupper($store_name_fb),$transaction_id,$theloai='HOADON',$permission='ALLADV',$order->warehouse_id);
       
       /*luu lich su tao hoa don lhson code 05/09/2021*/


		// $this->sma->print_arrays($sale_items);
		$items = $this->getAllInvoiceItems($id);
        if ($this->db->delete('sale_items', array('sale_id' => $id)) &&
        $this->db->delete('sales', array('id' => $id)) &&
        $this->db->delete('costing', array('sale_id' => $id))) {
            $this->db->delete('sales', array('sale_id' => $id));
            $this->db->delete('deliveries', array('sale_id' => $id));
            $this->db->delete('payments', array('sale_id' => $id));
            $this->site->syncQuantity(NULL, NULL, $sale_items);

            foreach ($items as $item) {             
                //sysn stock, price by API lhson code 27/6/2021
                $this->site->sysnStockApiTMDT($item->product_id);
            }
            
            //remove cart by API Lhson code 30/6/2021
            if ((int)$order->api_id>0) 
            {
                //tien hanh save history order by api
                $history_order['order_id']=$order->id;
                $history_order['order_code']=$order->reference_no;
                $history_order['api_order_id']=$order->api_id;
                $history_order['customer_name']=$order->customer;
                $history_order['customer_id']=$order->customer_id;
                $history_order['total_money']=$order->grand_total;
                $history_order['total_item']=$order->total_items;
                $history_order['type']='Xóa';    
                $history_order['created_by']= $this->session->userdata('user_id');
                $this->db->insert('history_api_orders', $history_order);

                $this->site->sysnapitmdtRemoveCarts($order->api_id);

            }

            

            
            return true;
        }
        return FALSE;
    }
	
	 public function deleteSaleReturn($id,$lhso_return=array())
    {
        if ($this->db->delete('sale_items', array('sale_id' => $id)) &&
			$this->db->delete('sales', array('id' => $id)) &&
			$this->db->delete('costing', array('sale_id' => $id))) {
            $this->db->delete('sales', array('sale_id' => $id));
            $this->db->delete('payments', array('sale_id' => $id));
            $this->site->syncQuantity(NULL, NULL, $id);
			
            return true;
        }
        return FALSE;
    }
		

    public function resetSaleActions($id, $return_id = NULL, $check_return = NULL)
    {
        if ($sale = $this->getInvoiceByID($id)) {
            if ($check_return && $sale->sale_status == 'returned') {
                $this->session->set_flashdata('warning', lang('sale_x_action'));
                redirect(isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : 'welcome');
            }

            if ($sale->sale_status == 'completed') {
                $items = $this->getAllInvoiceItems($id);
                foreach ($items as $item) {
                    if ($item->product_type == 'combo') {
                        $combo_items = $this->site->getProductComboItems($item->product_id, $item->warehouse_id);
                        foreach ($combo_items as $combo_item) {
                            if($combo_item->type == 'standard') {
                                $qty = ($item->quantity*$combo_item->qty);
                                $this->updatePurchaseItem(NULL, $qty, NULL, $combo_item->id, $item->warehouse_id);
                            }
                        }
                    } else {
                        $option_id = isset($item->option_id) && !empty($item->option_id) ? $item->option_id : NULL;
                       $this->updatePurchaseItem(NULL, $item->quantity, $item->id, $item->product_id, $item->warehouse_id, $option_id);
                    }
                }
                if ($sale->return_id || $return_id) {
                    $rid = $return_id ? $return_id : $sale->return_id;
                    $returned_items = $this->getAllInvoiceItems(FALSE, $rid);
                    foreach ($returned_items as $item) {

                        if ($item->product_type == 'combo') {
                            $combo_items = $this->site->getProductComboItems($item->product_id, $item->warehouse_id);
                            foreach ($combo_items as $combo_item) {
                                if($combo_item->type == 'standard') {
                                    $qty = ($item->quantity*$combo_item->qty);
                                    $this->updatePurchaseItem(NULL, $qty, NULL, $combo_item->id, $item->warehouse_id);
                                }
                            }
                        } else {
                            $option_id = isset($item->option_id) && !empty($item->option_id) ? $item->option_id : NULL;
                            $this->updatePurchaseItem(NULL, $item->quantity, $item->id, $item->product_id, $item->warehouse_id, $option_id);
                        }

                    }
                }
                $this->site->syncQuantity(NULL, NULL, $items);
                $this->sma->update_award_points($sale->grand_total, $sale->customer_id, $sale->created_by, TRUE,$sale->total_weight);
                return $items;
            }
        }
    }

    public function updatePurchaseItem($id, $qty, $sale_item_id, $product_id = NULL, $warehouse_id = NULL, $option_id = NULL)
    {
        if ($id) {
            if($pi = $this->getPurchaseItemByID($id)) {
                $pr = $this->site->getProductByID($pi->product_id);
                if ($pr->type == 'combo') {
                    $combo_items = $this->site->getProductComboItems($pr->id, $pi->warehouse_id);
                    foreach ($combo_items as $combo_item) {
                        if($combo_item->type == 'standard') {
                            $cpi = $this->site->getPurchasedItem(array('product_id' => $combo_item->id, 'warehouse_id' => $pi->warehouse_id, 'option_id' => NULL));
                            $bln = $pi->quantity_balance + ($qty*$combo_item->qty);
                            $this->db->update('purchase_items', array('quantity_balance' => $bln), array('id' => $combo_item->id));
                        }
                    }
                } else {
                    $bln = $pi->quantity_balance + $qty;
                    $this->db->update('purchase_items', array('quantity_balance' => $bln), array('id' => $id));
                }
            }
        } else {
            if ($sale_item_id) {
				
                if ($sale_item = $this->getSaleItemByID($sale_item_id)) {
					
                    $option_id = isset($sale_item->option_id) && !empty($sale_item->option_id) ? $sale_item->option_id : NULL;
                    $clause = array('product_id' => $sale_item->product_id, 'warehouse_id' => $sale_item->warehouse_id, 'option_id' => $option_id);
					
					if ($pi = $this->site->getPurchasedItem($clause)) {						
                        $quantity_balance = $pi->quantity_balance+$qty;
                        $this->db->update('purchase_items', array('quantity_balance' => $quantity_balance), array('id' => $pi->id));
                    } else {
                        $clause['purchase_id'] = NULL;
                        $clause['transfer_id'] = NULL;
                        $clause['quantity'] = 0;
                        $clause['quantity_balance'] = $qty;
                        $this->db->insert('purchase_items', $clause);
                    }
                }
            } else {
                if ($product_id && $warehouse_id) {
                    $pr = $this->site->getProductByID($product_id);
                    $clause = array('product_id' => $product_id, 'warehouse_id' => $warehouse_id, 'option_id' => $option_id);
                    if ($pr->type == 'standard') {
                        if ($pi = $this->site->getPurchasedItem($clause)) {
                            $quantity_balance = $pi->quantity_balance+$qty;
                            $this->db->update('purchase_items', array('quantity_balance' => $quantity_balance), array('id' => $pi->id));
                        } else {
                            $clause['purchase_id'] = NULL;
                            $clause['transfer_id'] = NULL;
                            $clause['quantity'] = 0;
                            $clause['quantity_balance'] = $qty;
                            $this->db->insert('purchase_items', $clause);
                        }
                    } elseif ($pr->type == 'combo') {
                        $combo_items = $this->site->getProductComboItems($pr->id, $warehouse_id);
                        foreach ($combo_items as $combo_item) {
                            $clause = array('product_id' => $combo_item->id, 'warehouse_id' => $warehouse_id, 'option_id' => NULL);
                            if($combo_item->type == 'standard') {
                                if ($pi = $this->site->getPurchasedItem($clause)) {
                                    $quantity_balance = $pi->quantity_balance+($qty*$combo_item->qty);
                                    $this->db->update('purchase_items', array('quantity_balance' => $quantity_balance), $clause);
                                } else {
                                    $clause['transfer_id'] = NULL;
                                    $clause['purchase_id'] = NULL;
                                    $clause['quantity'] = 0;
                                    $clause['quantity_balance'] = $qty;
                                    $this->db->insert('purchase_items', $clause);
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    public function getPurchaseItemByID($id)
    {
        $q = $this->db->get_where('purchase_items', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getCostingLines($sale_item_id, $product_id, $sale_id = NULL)
    {
        if ($sale_id) { $this->db->where('sale_id', $sale_id); }
        $this->db->order_by('id', 'asc');
        $q = $this->db->get_where('costing', array('sale_item_id' => $sale_item_id, 'product_id' => $product_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getSaleItemByID($id)
    {
        $q = $this->db->get_where('sale_items', array('id' => $id), 1);
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

    public function addDelivery($data = array())
    {
        if ($this->db->insert('deliveries', $data)) {
            $delivery_id=$this->db->insert_id();

            if ($this->site->getReference('do') == $data['do_reference_no']) {
                $this->site->updateReference('do');
            }

            

           $this->site->updateDeliveryApi($delivery_id,$data['status']);

            return true;
        }
        return false;
    }

    public function updateDelivery($id, $data = array())
    {
        if ($this->db->update('deliveries', $data, array('id' => $id))) {
            $rs=$this->site->updateDeliveryApi($id,$data['status']);
          
            return true;
        }
        return false;
    }

    public function getDeliveryByID($id)
    {
        $q = $this->db->get_where('deliveries', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getDeliveryBySaleID($sale_id)
    {
        $q = $this->db->get_where('deliveries', array('sale_id' => $sale_id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function deleteDelivery($id)
    {
        $delivery = $this->getDeliveryByID($id);
        if (!empty($delivery)) {

            if ($this->db->delete('deliveries', array('id' => $id))) {
                return true;
            }
        }
        
        return FALSE;
    }

    public function getInvoicePayments($sale_id)
    {
        $this->db->order_by('id', 'asc');
        $q = $this->db->get_where('payments', array('sale_id' => $sale_id));
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

    public function getPaymentsForSale($sale_id)
    {
        $this->db->select('payments.date, payments.paid_by, payments.amount, payments.cc_no, payments.cheque_no, payments.reference_no, users.first_name, users.last_name, type')
            ->join('users', 'users.id=payments.created_by', 'left');
        $q = $this->db->get_where('payments', array('sale_id' => $sale_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function addPayment($data = array(), $customer_id = null)
    {    
        if ((int)$data['warehouse_id']==0) {
            $_warehouse_id=$this->session->userdata('warehouse_id');
            if ($_warehouse_id==null) {
                $_warehouse_id=$this->Settings->default_warehouse;
            }
            $data['warehouse_id']=$_warehouse_id;
        } 

        if ($this->db->insert('payments', $data)) {
            $payment_id=$this->db->insert_id();
            if ($this->site->getReference('pay') == $data['reference_no']) {
                $this->site->updateReference('pay');
            }
            $this->site->syncSalePayments($data['sale_id']);
            if ($data['paid_by'] == 'gift_card') {
                $gc = $this->site->getGiftCardByNO($data['cc_no']);
                $this->db->update('gift_cards', array('balance' => ($gc->balance - $data['amount'])), array('card_no' => $data['cc_no']));
            } elseif ($customer_id && $data['paid_by'] == 'deposit') {
                $customer = $this->site->getCompanyByID($customer_id);
                $this->db->update('companies', array('deposit_amount' => ($customer->deposit_amount-$data['amount'])), array('id' => $customer_id));
            }
            //POST UPDATE PAYMENT STATUS BY API POSBASIC LHSON 30/6/2021
            if ($data['sale_id']>0) {
                $this->site->post_carts_update($data['sale_id']);
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

    public function addPaymentApi($data = array(), $customer_id = null)
    {
        if ((int)$data['warehouse_id']==0) {
            $_warehouse_id=$this->session->userdata('warehouse_id');
            if ($_warehouse_id==null) {
                $_warehouse_id=$this->Settings->default_warehouse;
            }
            $data['warehouse_id']=$_warehouse_id;
        }
        if ($this->db->insert('payments', $data)) {
            $pay_id=$this->db->insert_id();

            if ($this->site->getReference('pay') == $data['reference_no']) {
                $this->site->updateReference('pay');
            }
            $this->site->syncSalePayments($data['sale_id']);
            if ($data['paid_by'] == 'gift_card') {
                $gc = $this->site->getGiftCardByNO($data['cc_no']);
                $this->db->update('gift_cards', array('balance' => ($gc->balance - $data['amount'])), array('card_no' => $data['cc_no']));
            } elseif ($customer_id && $data['paid_by'] == 'deposit') {
                $customer = $this->site->getCompanyByID($customer_id);
                $this->db->update('companies', array('deposit_amount' => ($customer->deposit_amount-$data['amount'])), array('id' => $customer_id));
            }
            //POST UPDATE PAYMENT STATUS BY API POSBASIC LHSON 30/6/2021
            if ($data['sale_id']>0) {
                $this->site->post_carts_update($data['sale_id']);
            }
            /*luu lich su tao phieu thu lhson code 05/09/2021*/
            $order_history = $this->db->from('payments')->where('warehouse_id', $data['warehouse_id'])->where('id',$pay_id)->get()->row_array();
            $order_history['history']="Tạo mới phiếu thu";
            $order_history['history_auth']= $this->session->userdata('user_id');
            $this->db->insert('payments_history', $order_history);      
            $transaction_id=$this->db->insert_id();            

            $his_store_obj=$this->site->getWarehouseByID($data['warehouse_id']);
            $store_name_fb=$his_store_obj->name;

            $sent_rs=$this->site->sentNotificationAPI($title='Tạo mới phiếu thu',$message=mb_strtoupper($this->session->userdata('username')).' vừa tạo mới phiếu thu ['.mb_strtoupper($data['reference_no']).'] trị giá '.number_format($data['amount']).' tại kho '.mb_strtoupper($store_name_fb),$transaction_id,$theloai='PHIEUTHU',$permission='KETOAN',$data['warehouse_id']);
           
           /*luu lich su tao phieu thu lhson code 05/09/2021*/
            return $pay_id;
        }
        return 0;
    }

    public function updatePayment($id, $data = array(), $customer_id = null)
    {
        $opay = $this->getPaymentByID($id);
        if ($this->db->update('payments', $data, array('id' => $id))) {
            $this->site->syncSalePayments($data['sale_id']);
            if ($opay->paid_by == 'gift_card') {
                $gc = $this->site->getGiftCardByNO($opay->cc_no);
                $this->db->update('gift_cards', array('balance' => ($gc->balance+$opay->amount)), array('card_no' => $opay->cc_no));
            } elseif ($opay->paid_by == 'deposit') {
                if (!$customer_id) {
                    $sale = $this->getInvoiceByID($opay->sale_id);
                    $customer_id = $sale->customer_id;
                }
                $customer = $this->site->getCompanyByID($customer_id);
                $this->db->update('companies', array('deposit_amount' => ($customer->deposit_amount+$opay->amount)), array('id' => $customer->id));
            }
            if ($data['paid_by'] == 'gift_card') {
                $gc = $this->site->getGiftCardByNO($data['cc_no']);
                $this->db->update('gift_cards', array('balance' => ($gc->balance - $data['amount'])), array('card_no' => $data['cc_no']));
            } elseif ($customer_id && $data['paid_by'] == 'deposit') {
                $customer = $this->site->getCompanyByID($customer_id);
                $this->db->update('companies', array('deposit_amount' => ($customer->deposit_amount-$data['amount'])), array('id' => $customer_id));
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
	public function updatePaymentLhson($id, $data = array(), $customer_id = null)
    {
        $opay = $this->getPaymentByID($id);
        if ($this->db->update('payments', $data, array('id' => $id))) {
            if($data['sale_id']>0){
				$this->site->syncSalePayments($data['sale_id']);
			 }
            if ($opay->paid_by == 'gift_card') {
                $gc = $this->site->getGiftCardByNO($opay->cc_no);
                $this->db->update('gift_cards', array('balance' => ($gc->balance+$opay->amount)), array('card_no' => $opay->cc_no));
            } elseif ($opay->paid_by == 'deposit') {
               
                $customer = $this->site->getCompanyByID($customer_id);
                $this->db->update('companies', array('deposit_amount' => ($customer->deposit_amount+$opay->amount)), array('id' => $customer->id));
            }
            if ($data['paid_by'] == 'gift_card') {
                $gc = $this->site->getGiftCardByNO($data['cc_no']);
                $this->db->update('gift_cards', array('balance' => ($gc->balance - $data['amount'])), array('card_no' => $data['cc_no']));
            } elseif ($customer_id && $data['paid_by'] == 'deposit') {
                $customer = $this->site->getCompanyByID($customer_id);
                $this->db->update('companies', array('deposit_amount' => ($customer->deposit_amount-$data['amount'])), array('id' => $customer_id));
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

    public function deletePayment($id)
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
            $this->site->syncSalePayments($opay->sale_id);
            if ($opay->paid_by == 'gift_card') {
                $gc = $this->site->getGiftCardByNO($opay->cc_no);
                $this->db->update('gift_cards', array('balance' => ($gc->balance+$opay->amount)), array('card_no' => $opay->cc_no));
            } elseif ($opay->paid_by == 'deposit') {
                $sale = $this->getInvoiceByID($opay->sale_id);
                $customer = $this->site->getCompanyByID($sale->customer_id);
                $this->db->update('companies', array('deposit_amount' => ($customer->deposit_amount+$opay->amount)), array('id' => $customer->id));
            }
            return true;
        }
        return FALSE;
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
            if($opay->sale_id>0){
				$this->site->syncSalePayments($opay->sale_id);
			 }
            if ($opay->paid_by == 'gift_card') {
                $gc = $this->site->getGiftCardByNO($opay->cc_no);
                $this->db->update('gift_cards', array('balance' => ($gc->balance+$opay->amount)), array('card_no' => $opay->cc_no));
            } elseif ($opay->paid_by == 'deposit') {
                $sale = $this->getInvoiceByID($opay->sale_id);
                $customer = $this->site->getCompanyByID($sale->customer_id);
                $this->db->update('companies', array('deposit_amount' => ($customer->deposit_amount+$opay->amount)), array('id' => $customer->id));
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
	public function addPaymentLhson($data = array(), $customer_id = null)
    {
        if ((int)$data['warehouse_id']==0) {
            $_warehouse_id=$this->session->userdata('warehouse_id');
            if ($_warehouse_id==null) {
                $_warehouse_id=$this->Settings->default_warehouse;
            }
            $data['warehouse_id']=$_warehouse_id;
        }
        if ($this->db->insert('payments', $data)) {
            $payment_id=$this->db->insert_id();

           if ($this->site->getReference('thu') == $data['reference_no']) {
                $this->site->updateReference('thu');
            } 
            if ($data['paid_by'] == 'gift_card') {
                $gc = $this->site->getGiftCardByNO($data['cc_no']);
                $this->db->update('gift_cards', array('balance' => ($gc->balance - $data['amount'])), array('card_no' => $data['cc_no']));
            } elseif ($customer_id && $data['paid_by'] == 'deposit') {
                $customer = $this->site->getCompanyByID($customer_id);
                $this->db->update('companies', array('deposit_amount' => ($customer->deposit_amount-$data['amount'])), array('id' => $customer_id));
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

    /* ----------------- Gift Cards --------------------- */

    public function addGiftCard($data = array(), $ca_data = array(), $sa_data = array())
    {
        if ($this->db->insert('gift_cards', $data)) {
            if (!empty($ca_data)) {
                $this->db->update('companies', array('award_points' => $ca_data['points']), array('id' => $ca_data['customer']));
            } elseif (!empty($sa_data)) {
                $this->db->update('users', array('award_points' => $sa_data['points']), array('id' => $sa_data['user']));
            }
            return true;
        }
        return false;
    }

    public function updateGiftCard($id, $data = array())
    {
        $this->db->where('id', $id);
        if ($this->db->update('gift_cards', $data)) {
            return true;
        }
        return false;
    }

    public function deleteGiftCard($id)
    {
        if ($this->db->delete('gift_cards', array('id' => $id))) {
            return true;
        }
        return FALSE;
    }

    public function getPaypalSettings()
    {
        $q = $this->db->get_where('paypal', array('id' => 1));
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getSkrillSettings()
    {
        $q = $this->db->get_where('skrill', array('id' => 1));
        if ($q->num_rows() > 0) {
            return $q->row();
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

    public function getStaff()
    {
        if (!$this->Owner) {
            $this->db->where('group_id !=', 1);
        }
        $this->db->where('group_id !=', 3)->where('group_id !=', 4);
        $q = $this->db->get('users');
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

    public function getTaxRateByName($name)
    {
        $q = $this->db->get_where('tax_rates', array('name' => $name), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function updateCostingLine($sale_item_id, $product_id, $quantity)
    {
        if ($costings = $this->getCostingLines($sale_item_id, $product_id)) {
            foreach ($costings as $cost) {
                if ($cost->quantity >= $quantity) {
                    $qty = $cost->quantity - $quantity;
                    $bln = $cost->quantity_balance && $cost->quantity_balance >= $quantity ? $cost->quantity_balance - $quantity : 0;
                    $this->db->update('costing', array('quantity' => $qty, 'quantity_balance' => $bln), array('id' => $cost->id));
                    $quantity = 0;
                } elseif ($cost->quantity < $quantity) {
                    $qty = $quantity - $cost->quantity;
                    $this->db->delete('costing', array('id' => $cost->id));
                    $quantity = $qty;
                }
            }
            return TRUE;
        }
        return FALSE;
    }

    public function topupGiftCard($data = array(), $card_data = NULL)
    {
        if ($this->db->insert('gift_card_topups', $data)) {
            $this->db->update('gift_cards', $card_data, array('id' => $data['card_id']));
            return true;
        }
        return false;
    }

    public function getAllGCTopups($card_id)
    {
        $this->db->select("{$this->db->dbprefix('gift_card_topups')}.*, {$this->db->dbprefix('users')}.first_name, {$this->db->dbprefix('users')}.last_name, {$this->db->dbprefix('users')}.email")
        ->join('users', 'users.id=gift_card_topups.created_by', 'left')
        ->order_by('id', 'desc')->limit(10);
        $q = $this->db->get_where('gift_card_topups', array('card_id' => $card_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
	public function checkCoSanPhamTraHangTrongHD($id=null)
    {
		if($id>0){
			$_query="select count(*) as tong FROM {$this->db->dbprefix('sales')} a,{$this->db->dbprefix('sale_items')} b WHERE a.id=b.sale_id AND b.quantity<0 AND a.id=".(int)$id;
			$q= $this->db->query($_query);	
			
			if ($q->num_rows() > 0) {
				$tong=0;
				foreach (($q->result()) as $row) {
					$tong = $row->tong;
				}
				if($tong>0){
					return true;
				}
				return FALSE;
			}
			return FALSE;
		}
        return FALSE;
    }
	function getLastTimeOrderWooApi(){
		
		return date('c',strtotime("-1 days"));
	}
	function checkCustomerIdWooApi($customer_id){
		$query="SELECT id FROM scodeweb_companies WHERE woo_customer_id=".$customer_id;
		$q = $this->db->query($query);
		if ($q->num_rows() > 0) {
			return $q->row()->id;
        }
		return 0;
	}
    function checkCustomerIdSanTMDTApi($customer_id){
        $query="SELECT id FROM scodeweb_companies WHERE santmdt_customer_id=".$customer_id;
        $q = $this->db->query($query);
        if ($q->num_rows() > 0) {
            return $q->row()->id;
        }
        return 0;
    }
	function checkCustomerByPhoneWooApi($phone){
		$query="SELECT id FROM scodeweb_companies WHERE phone='".$phone."'";
		$q = $this->db->query($query);
		if ($q->num_rows() > 0) {
			return $q->row()->id;
			
        }
		return 0;
	}
	function checkProductBySkuWooApi($sku){
		$query="SELECT id FROM scodeweb_products WHERE code='".$sku."'";
		$q = $this->db->query($query);
		if ($q->num_rows() > 0) {
			return $q->row()->id;
			
        }
		return 0;
	}
	function getProductByIdWooApi($product_id){
		$query="SELECT * FROM scodeweb_products WHERE id='".$product_id."'";
		$q = $this->db->query($query);
		if ($q->num_rows() > 0) {
			return $q->row();
			
        }
		return 0;
	}
	function checkCartWooApi($id){
		$query="SELECT id FROM scodeweb_sales WHERE is_web='".$id."'";
		$q = $this->db->query($query);
		if ($q->num_rows() > 0) {
			return (int)$q->row()->id;
			
        }
		return 0;
	}
    function getWooOrderIdBySaleId($id){
        $query="SELECT is_web FROM scodeweb_sales WHERE id='".$id."'";
        $q = $this->db->query($query);
        if ($q->num_rows() > 0) {
            return (int)$q->row()->is_web;
            
        }
        return 0;
    }
    function getWooUrlBySaleId($id){
        $query="SELECT fromweb FROM scodeweb_sales WHERE is_web='".$id."'";
        $q = $this->db->query($query);
        if ($q->num_rows() > 0) {
            return $q->row()->fromweb;
            
        }
        return '';
    }
    public function getPaymentsForTraGop($sale_id)
    {
        $this->db->select("" . $this->db->dbprefix('payments') . ".date, " . $this->db->dbprefix('sales') . ".reference_no as payment_ref,c_name,c_phone, payment_the.name as paid_by, amount,sotien_tragop,(amount-sotien_tragop) as duno,(CASE WHEN sotien_tragop=amount THEN 'paid' WHEN sotien_tragop>0 AND sotien_tragop<amount THEN 'partial'  ELSE 'due' END) as status,".$this->db->dbprefix('payments') . ".note_last as note," . $this->db->dbprefix('payments') . ".id," . $this->db->dbprefix('payments') . ".warehouse_id")
            ->join('sales', 'payments.sale_id=sales.id', 'left')
            ->join('payment_the', 'payments.paid_by=payment_the.code', 'left');

        $q = $this->db->get_where('payments', array('scodeweb_payments.id' => $sale_id));
        if ($q->num_rows() > 0) {
            
            return $q->row_array();
        }
        return FALSE;
    }
    public function updateTraGop($id, $data = array(),$payment_tragop=null)
    {
        $opay = $this->getPaymentByID($id);
        if ($this->db->update('payments', $data, array('id' => $id))) {
           if ($this->db->insert('payment_tragop', $payment_tragop)) {
                $sale_id = $this->db->insert_id();
                return true;
            }
        }
        return false;
    }
    public function ListTraGop($payment_id)
    {
        $this->db->select("date,(select concat(first_name,' ',last_name) FROM scodeweb_users WHERE id={$this->db->dbprefix('payment_tragop')}.created_by) as nhanvien,amount,note,warehouse_id,scodeweb_warehouses.name as warehouse,created,payment_tragop.id as id")->join('warehouses', 'payment_tragop.warehouse_id=warehouses.id', 'left');       

        $q = $this->db->get_where('payment_tragop', array('payment_id' => $payment_id));
        if ($q->num_rows() > 0) {            
            return $q->result();
        }
        return FALSE;
    }
    public function getTraGopById($payment_id)
    {
        $this->db->select("date,(select concat(first_name,' ',last_name) FROM scodeweb_users WHERE id={$this->db->dbprefix('payment_tragop')}.created_by) as nhanvien,amount,note,warehouse_id,scodeweb_warehouses.name as warehouse,created,payment_tragop.id as id,payment_id")->join('warehouses', 'payment_tragop.warehouse_id=warehouses.id', 'left');       

        $q = $this->db->get_where('payment_tragop', array('scodeweb_payment_tragop.id' => $payment_id));
        if ($q->num_rows() > 0) {            
            return $q->row_array();
        }
        return FALSE;
    }
    public function XoaTraGop($id, $data = array(),$id_tragop=null,$payment_id=null)
    {
        $opay = $this->getPaymentByID($id);
        if ($this->db->update('payments', $data, array('id' => $payment_id))) {
           if ($this->db->delete('payment_tragop', array('id' => $id_tragop))) {                
                return true;
            }
        }
        return false;
    }
    function updatePaymentAPI($payments=null,$sale_id=0,$customer_id=0)
    {
        $this->db->delete('payments', array('sale_id' => $sale_id));
        $this->site->syncSalePayments($sale_id);
        if (!empty($payments)) 
        {
           $payment=$payments[0];

            if (empty($payment['reference_no'])) {
                $payment['reference_no'] = $this->site->getReference('pay');
            }
            $payment['sale_id'] = $sale_id;
            if ((int)$payment['warehouse_id']==0) {
                $_warehouse_id=$this->session->userdata('warehouse_id');
                if ($_warehouse_id==null) {
                    $_warehouse_id=$this->Settings->default_warehouse;
                }
                $payment['warehouse_id']=$_warehouse_id;
            }

            if ($payment['paid_by'] == 'gift_card') {
                $this->db->update('gift_cards', array('balance' => $payment['gc_balance']), array('card_no' => $payment['cc_no']));
                unset($payment['gc_balance']);
                $this->db->insert('payments', $payment);

                $payment_id=$this->db->insert_id();
                /*luu lich su tao phieu thu lhson code 05/09/2021*/
                $order_history = $this->db->from('payments')->where('warehouse_id', $payment['warehouse_id'])->where('id',$payment_id)->get()->row_array();
                $order_history['history']="Tạo mới phiếu thu";
                $order_history['history_auth']= $this->session->userdata('user_id');
                $this->db->insert('payments_history', $order_history);      
                $transaction_id=$this->db->insert_id();            

                $his_store_obj=$this->site->getWarehouseByID($payment['warehouse_id']);
                $store_name_fb=$his_store_obj->name;

                $sent_rs=$this->site->sentNotificationAPI($title='Tạo mới phiếu thu',$message=mb_strtoupper($this->session->userdata('username')).' vừa tạo mới phiếu thu ['.mb_strtoupper($payment['reference_no']).'] trị giá '.number_format($payment['amount']).' tại kho '.mb_strtoupper($store_name_fb),$transaction_id,$theloai='PHIEUTHU',$permission='KETOAN',$payment['warehouse_id']);
               
               /*luu lich su tao phieu thu lhson code 05/09/2021*/

            } else {
                if ($payment['paid_by'] == 'deposit') {
                    $customer = $this->site->getCompanyByID($customer_id);
                    $this->db->update('companies', array('deposit_amount' => ($customer->deposit_amount-$payment['amount'])), array('id' => $customer->id));
                }
                if ((float)$payment['amount']>0) {
                    $this->db->insert('payments', $payment);    

                    $payment_id=$this->db->insert_id();
                    /*luu lich su tao phieu thu lhson code 05/09/2021*/
                    $order_history = $this->db->from('payments')->where('warehouse_id', $payment['warehouse_id'])->where('id',$payment_id)->get()->row_array();
                    $order_history['history']="Tạo mới phiếu thu";
                    $order_history['history_auth']= $this->session->userdata('user_id');
                    $this->db->insert('payments_history', $order_history);      
                    $transaction_id=$this->db->insert_id();            

                    $his_store_obj=$this->site->getWarehouseByID($payment['warehouse_id']);
                    $store_name_fb=$his_store_obj->name;

                    $sent_rs=$this->site->sentNotificationAPI($title='Tạo mới phiếu thu',$message=mb_strtoupper($this->session->userdata('username')).' vừa tạo mới phiếu thu ['.mb_strtoupper($payment['reference_no']).'] trị giá '.number_format($payment['amount']).' tại kho '.mb_strtoupper($store_name_fb),$transaction_id,$theloai='PHIEUTHU',$permission='KETOAN',$payment['warehouse_id']);
                   
                   /*luu lich su tao phieu thu lhson code 05/09/2021*/

                }  
            }
            if ($this->site->getReference('pay') == $payment['reference_no']) {
                $this->site->updateReference('pay');
            }
            $this->site->syncSalePayments($sale_id);

        } 
    }
}
