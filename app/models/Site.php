<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Site extends CI_Model
{

    public function __construct() {
        parent::__construct();
        $this->digital_upload_path = 'files/';
        $this->upload_path = 'assets/uploads/';
        $this->thumbs_path = 'assets/uploads/thumbs/';
        $this->image_types = 'gif|jpg|jpeg|png|tif';
        $this->digital_file_types = 'zip|psd|ai|rar|pdf|doc|docx|xls|xlsx|ppt|pptx|gif|jpg|jpeg|png|tif|txt';
        $this->allowed_file_size = '1024';

		$this->checkExprieLogin();
    }

    public function get_total_qty_alerts() {
        $this->db->where('quantity < alert_quantity', NULL, FALSE)->where('track_quantity', 1);
        return $this->db->count_all_results('products');
    }
	public function getAllDoitac() {  
        $q = $this->db->order_by('name')->get('doitac');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
     public function getAllSoQuy() {  
        $q = $this->db->order_by('name')->get('payment_the');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
     public function getAllGroups() {  
        $q = $this->db->order_by('description')->get('groups');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
    public function get_expiring_qty_alerts() {
        $date = date('Y-m-d', strtotime('+3 months'));
        $this->db->select('SUM(quantity_balance) as alert_num')
        ->where('expiry !=', NULL)->where('expiry !=', '0000-00-00')
        ->where('expiry <', $date);
        $q = $this->db->get('purchase_items');
        if ($q->num_rows() > 0) {
            $res = $q->row();
            return (INT) $res->alert_num;
        }
        return FALSE;
    }

    public function get_setting() {
        $q = $this->db->get('settings');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getDateFormat($id) {
        $q = $this->db->get_where('date_format', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getAllCompanies($group_name) {
        $q = $this->db->get_where('companies', array('group_name' => $group_name));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getCompanyByID($id) {
        $q = $this->db->get_where('companies', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    public function getKhuyenmaiById($id) {
        $q = $this->db->get_where('khuyenmai', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
     public function getDoitacByID($id) {
        $q = $this->db->get_where('doitac', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    public function getCustomerGroupByID($id) {
        $q = $this->db->get_where('customer_groups', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getUser($id = NULL) {
        if (!$id) {
            $id = $this->session->userdata('user_id');
        }
        $q = $this->db->get_where('users', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getProductByID($id) {
        $q = $this->db->get_where('products', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getAllCurrencies() {
        $q = $this->db->get('currencies');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getCurrencyByCode($code) {
        $q = $this->db->get_where('currencies', array('code' => $code), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getAllTaxRates() {
        $q = $this->db->get('tax_rates');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getTaxRateByID($id) {
        $q = $this->db->get_where('tax_rates', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getAllWarehouses($id=0) {
        $q = $this->db->get('warehouses');
        if ($id>0) {
            $q = $this->db->get_where('warehouses', array('id' => $id), 1);
        }
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getWarehouseByID($id) {
        $q = $this->db->get_where('warehouses', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getAllCategories() {
        $this->db->where('parent_id', NULL)->or_where('parent_id', 0)->order_by('name');
        $q = $this->db->get("categories");
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
    public function getAllCategoriesApi($term='') {
       

        if ($term!='') {
            $this->db->where("(" . $this->db->dbprefix('categories') . ".name LIKE '%" . $term . "%' OR code LIKE '%" . $term . "%')");
        }else{
            $this->db->where('parent_id', NULL)->or_where('parent_id', 0);
        }
        
        //$this->db->where('categories.id IN (SELECT (CASE WHEN subcategory_id IS NULL THEN category_id ELSE subcategory_id END ) FROM scodeweb_products)');


        $q = $this->db->order_by('name')->get("categories");
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return NULL;
    }

    public function getCategoryCha($parent_id=0) {
               
        $q = $this->db->get_where('categories', array('id' => $parent_id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

	 public function getAllNhomsanpham() {
        $q = $this->db->get("group_products");
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getSubCategories($parent_id) {
        $this->db->where('parent_id', $parent_id)->order_by('name');
        $q = $this->db->get("categories");
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
     public function getSubCategoriesV2($parent_id=0) {
        $this->db->where('parent_id', $parent_id)->or_where('id', $parent_id);
        $q = $this->db->get("categories");
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
    public function getSubCategoriesAPI($parent_id=NULL) {
        
       // $this->db->where('categories.id IN (SELECT (CASE WHEN subcategory_id IS NULL THEN category_id ELSE subcategory_id END ) FROM scodeweb_products)');

        $this->db->where('parent_id', $parent_id)->order_by('name');
        $q = $this->db->get("categories");
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

 
    public function getCategoryByID($id) {
        $q = $this->db->get_where('categories', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
	public function getExCategoryByID($id) {
        $q = $this->db->get_where('expense_categories', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getGiftCardByID($id) {
        $q = $this->db->get_where('gift_cards', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getGiftCardByNO($no) {
        $q = $this->db->get_where('gift_cards', array('card_no' => $no), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function updateInvoiceStatus() {
        $date = date('Y-m-d');
        $q = $this->db->get_where('invoices', array('status' => 'unpaid'));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                if ($row->due_date < $date) {
                    $this->db->update('invoices', array('status' => 'due'), array('id' => $row->id));
                }
            }
            $this->db->update('settings', array('update' => $date), array('setting_id' => '1'));
            return true;
        }
    }

    public function modal_js() {
        $arrContextOptions=array(
              "ssl"=>array(
                    "verify_peer"=>false,
                    "verify_peer_name"=>false,
                ),
            );  
        return '<script type="text/javascript">' . file_get_contents($this->data['assets'] . 'js/modal.js',false, stream_context_create($arrContextOptions)) . '</script>';
    }

    public function getReference($field) {
        $q = $this->db->get_where('order_ref', array('ref_id' => '1'), 1);
        if ($q->num_rows() > 0) {
            $ref = $q->row();
            switch ($field) {
                case 'so':
                    $prefix = $this->Settings->sales_prefix;
                    break;
                case 'pos':
                    $prefix = isset($this->Settings->sales_prefix) ? $this->Settings->sales_prefix . '/POS' : '';
                    break;
                case 'qu':
                    $prefix = $this->Settings->quote_prefix;
                    break;
                case 'po':
                    $prefix = $this->Settings->purchase_prefix;
                    break;
                case 'to':
                    $prefix = $this->Settings->transfer_prefix;
                    break;
                case 'do':
                    $prefix = $this->Settings->delivery_prefix;
                    break;
                case 'pay':
                    $prefix = $this->Settings->payment_prefix;
                    break;
                case 'ppay':
                    $prefix = $this->Settings->ppayment_prefix;
                    break;
                case 'ex':
                    $prefix = $this->Settings->expense_prefix;
                    break;
                case 're':
                    $prefix = $this->Settings->return_prefix;
                    break;
                case 'rep':
                    $prefix = $this->Settings->returnp_prefix;
                    break;
                case 'qa':
                    $prefix = $this->Settings->qa_prefix;
                    break;
				case 'thu':
                    $prefix = $this->Settings->thu_prefix;
                    break;
				case 'chi':
                    $prefix = $this->Settings->chi_prefix;
                    break;		
                default:
                    $prefix = '';
            }

            $ref_no = (!empty($prefix)) ? $prefix . '/' : '';

            if ($this->Settings->reference_format == 1) {
                $ref_no .= date('Y') . "/" . sprintf("%04s", $ref->{$field});
            } elseif ($this->Settings->reference_format == 2) {
                $ref_no .= date('Y') . "/" . date('m') . "/" . sprintf("%04s", $ref->{$field});
            } elseif ($this->Settings->reference_format == 3) {
                $ref_no .= sprintf("%04s", $ref->{$field});
            }elseif ($this->Settings->reference_format == 7) {
                 $ref_no .= date('Y') . "/" . date('m') . "/". date('d') . "/" . sprintf("%04s", $ref->{$field});
            } else {
                $ref_no .= $this->getRandomReference();
            }

            return $ref_no;
        }
        return FALSE;
    }

    public function getRandomReference($len = 12) {
        $result = '';
        for ($i = 0; $i < $len; $i++) {
            $result .= mt_rand(0, 9);
        }

        if ($this->getSaleByReference($result)) {
            $this->getRandomReference();
        }

        return $result;
    }

    public function getSaleByReference($ref) {
        $this->db->like('reference_no', $ref, 'before');
        $q = $this->db->get('sales', 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function updateReference($field) {
        $q = $this->db->get_where('order_ref', array('ref_id' => '1'), 1);
        if ($q->num_rows() > 0) {
            $ref = $q->row();
            $this->db->update('order_ref', array($field => $ref->{$field} + 1), array('ref_id' => '1'));
            return TRUE;
        }
        return FALSE;
    }

    public function checkPermissions() {
        $q = $this->db->get_where('permissions', array('group_id' => $this->session->userdata('group_id')), 1);
        if ($q->num_rows() > 0) {
            return $q->result_array();
        }
        return FALSE;
    }

    public function getNotifications() {
        $date = date('Y-m-d H:i:s', time());
        $this->db->where("from_date <=", $date);
        $this->db->where("till_date >=", $date);
        if (!$this->Owner) {
            if ($this->Supplier) {
                $this->db->where('scope', 4);
            } elseif ($this->Customer) {
                $this->db->where('scope', 1)->or_where('scope', 3);
            } elseif (!$this->Customer && !$this->Supplier) {
                $this->db->where('scope', 2)->or_where('scope', 3);
            }
        }
        $q = $this->db->get("notifications");
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }

    public function getUpcomingEvents() {
        $dt = date('Y-m-d');
        $this->db->where('start >=', $dt)->order_by('start')->limit(5);
        if ($this->Settings->restrict_calendar) {
            $this->db->where('user_id', $this->session->userdata('user_id'));
        }

        $q = $this->db->get('calendar');

        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getUserGroup($user_id = false) {
        if (!$user_id) {
            $user_id = $this->session->userdata('user_id');
        }
        $group_id = $this->getUserGroupID($user_id);
        $q = $this->db->get_where('groups', array('id' => $group_id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getUserGroupID($user_id = false) {
        $user = $this->getUser($user_id);
        return $user->group_id;
    }

    public function getWarehouseProductsVariants($option_id, $warehouse_id = NULL) {
        if ($warehouse_id) {
            $this->db->where('warehouse_id', $warehouse_id);
        }
        $q = $this->db->get_where('warehouses_products_variants', array('option_id' => $option_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
	public function setPurchaseItem($clause, $qty) {
        if ($product = $this->getProductByID($clause['product_id'])) {
            if ($pi = $this->getPurchasedItem($clause)) {
                $quantity_balance = $pi->quantity_balance+$qty;
                return $this->db->update('purchase_items', array('quantity_balance' => $quantity_balance), array('id' => $pi->id));
            } else {
                $unit = $this->getUnitByID($product->unit);
                $clause['product_unit_id'] = $product->unit;
                $clause['product_unit_code'] = $unit->code;
                $clause['product_code'] = $product->code;
                $clause['product_name'] = $product->name;
                $clause['purchase_id'] = $clause['transfer_id'] = $clause['item_tax'] = NULL;
                $clause['net_unit_cost'] = $clause['real_unit_cost'] = $clause['unit_cost'] = $product->cost;
                $clause['quantity_balance'] = $clause['quantity'] = $clause['unit_quantity'] = $clause['quantity_received'] = $qty;
                $clause['subtotal'] = ($product->cost * $qty);
                if (isset($product->tax_rate) && $product->tax_rate != 0) {
                    $tax_details = $this->site->getTaxRateByID($product->tax_rate);
                    $ctax = $this->calculateTax($product, $tax_details, $product->cost);
                    $item_tax = $clause['item_tax'] = $ctax['amount'];
                    $tax = $clause['tax'] = $ctax['tax'];
                    $clause['tax_rate_id'] = $tax_details->id;
                    if ($product->tax_method != 1) {
                        $clause['net_unit_cost'] = $product->cost - $item_tax;
                        $clause['unit_cost'] = $product->cost;
                    } else {
                        $clause['net_unit_cost'] = $product->cost;
                        $clause['unit_cost'] = $product->cost + $item_tax;
                    }
                    $pr_item_tax = $this->sma->formatDecimal($item_tax * $clause['unit_quantity'], 4);
                   
                    $clause['subtotal'] = (($clause['net_unit_cost'] * $clause['unit_quantity']) + $pr_item_tax);
                }
                $clause['status'] = 'received';
                $clause['date'] = date('Y-m-d');
                $clause['option_id'] = !empty($clause['option_id']) && is_numeric($clause['option_id']) ? $clause['option_id'] : NULL;
                return $this->db->insert('purchase_items', $clause);
            }
        }
        return FALSE;
    }
    public function getPurchasedItem($where_clause) {
        $orderby = ($this->Settings->accounting_method == 1) ? 'asc' : 'desc';
        $this->db->order_by('date', $orderby);
        $this->db->order_by('purchase_id', $orderby);
        $q = $this->db->get_where('purchase_items', $where_clause);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function syncVariantQty($variant_id, $warehouse_id, $product_id = NULL) {
		$balance_qty = $this->tonkhohientai_theobienthe($variant_id);
        $wh_balance_qty = $this->tonkhohientai_theobienthe($variant_id, $warehouse_id);
        if ($this->db->update('product_variants', array('quantity' => $balance_qty), array('id' => $variant_id))) {
            if ($this->getWarehouseProductsVariants($variant_id, $warehouse_id)) {
                $this->db->update('warehouses_products_variants', array('quantity' => $wh_balance_qty), array('option_id' => $variant_id, 'warehouse_id' => $warehouse_id));
            } else {
                if($wh_balance_qty) {
                    $this->db->insert('warehouses_products_variants', array('quantity' => $wh_balance_qty, 'option_id' => $variant_id, 'warehouse_id' => $warehouse_id, 'product_id' => $product_id));
                }
            }
            return TRUE;
        }
        /*$balance_qty = $this->getBalanceVariantQuantity($variant_id);
        $wh_balance_qty = $this->getBalanceVariantQuantity($variant_id, $warehouse_id);
        if ($this->db->update('product_variants', array('quantity' => $balance_qty), array('id' => $variant_id))) {
            if ($this->getWarehouseProductsVariants($variant_id, $warehouse_id)) {
                $this->db->update('warehouses_products_variants', array('quantity' => $wh_balance_qty), array('option_id' => $variant_id, 'warehouse_id' => $warehouse_id));
            } else {
                if($wh_balance_qty) {
                    $this->db->insert('warehouses_products_variants', array('quantity' => $wh_balance_qty, 'option_id' => $variant_id, 'warehouse_id' => $warehouse_id, 'product_id' => $product_id));
                }
            }
            return TRUE;
        }*/
        return FALSE;
    }

    public function getWarehouseProducts($product_id, $warehouse_id = NULL) {
        if ($warehouse_id) {
            $this->db->where('warehouse_id', $warehouse_id);
        }
        $q = $this->db->get_where('warehouses_products', array('product_id' => $product_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
	
    public function syncProductQty($product_id, $warehouse_id) {
        $balance_qty = $this->getBalanceQuantity($product_id);
        $wh_balance_qty = $this->getBalanceQuantity($product_id, $warehouse_id);		
		
		$tonkhohientai=$this->tonkhohientai($product_id, $warehouse_id);
		$tonkhohientai_allkho=$this->getongtonkhoallkho($product_id);
		//$this->sma->print_arrays($tonkhohientai);
		
		 if ($this->db->update('products', array('quantity' => $tonkhohientai_allkho), array('id' => $product_id))) {
            if ($this->getWarehouseProducts($product_id, $warehouse_id)) {
                $this->db->update('warehouses_products', array('quantity' => $tonkhohientai), array('product_id' => $product_id, 'warehouse_id' => $warehouse_id));
            } else {				
                if( ! $wh_balance_qty) { $wh_balance_qty = 0; }
                $product = $this->site->getProductByID($product_id);
                $this->db->insert('warehouses_products', array('quantity' => $tonkhohientai, 'product_id' => $product_id, 'warehouse_id' => $warehouse_id, 'avg_cost' => $product->cost));
            }
            return TRUE;
        }
        // if ($this->db->update('products', array('quantity' => $balance_qty), array('id' => $product_id))) {
            // if ($this->getWarehouseProducts($product_id, $warehouse_id)) {
                // $this->db->update('warehouses_products', array('quantity' => $wh_balance_qty), array('product_id' => $product_id, 'warehouse_id' => $warehouse_id));
            // } else {
                // if( ! $wh_balance_qty) { $wh_balance_qty = 0; }
                // $product = $this->site->getProductByID($product_id);
                // $this->db->insert('warehouses_products', array('quantity' => $wh_balance_qty, 'product_id' => $product_id, 'warehouse_id' => $warehouse_id, 'avg_cost' => $product->cost));
            // }
            // return TRUE;
        // }
        return FALSE;
    }

    public function getSaleByID($id) {
        $q = $this->db->get_where('sales', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getSalePayments($sale_id) {
        $q = $this->db->get_where('payments', array('sale_id' => $sale_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function syncSalePayments($id) {
        $sale = $this->getSaleByID($id);
        $payments = $this->getSalePayments($id);
        $paid = 0;
        $grand_total = $sale->grand_total+$sale->rounding;
        foreach ($payments as $payment) {
            $paid += $payment->amount;
        }

        $payment_status = $paid == 0 ? 'pending' : $sale->payment_status;
        if ($this->sma->formatDecimal($grand_total) == $this->sma->formatDecimal($paid)) {
            $payment_status = 'paid';
        } elseif ($paid != 0) {
            $payment_status = 'partial';
        } elseif ($sale->due_date <= date('Y-m-d') && !$sale->sale_id) {
            $payment_status = 'due';
        }

        if ($this->db->update('sales', array('paid' => $paid, 'payment_status' => $payment_status), array('id' => $id))) {
            return true;
        }

        return FALSE;
    }

    public function getPurchaseByID($id) {
        $q = $this->db->get_where('purchases', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    public function UpdateAllSaleToPaidByCustomer($customer_id)
    {
        if ($this->db->update('sales', ['payment_status' =>'paid','paid' =>'grand_total'], ['customer_id' => $customer_id])) {
            return TRUE;
        }        
        return FALSE;
    }

    public function getPurchasePayments($purchase_id) {
        $q = $this->db->get_where('payments', array('purchase_id' => $purchase_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function syncPurchasePayments($id) {
        $purchase = $this->getPurchaseByID($id);
        $payments = $this->getPurchasePayments($id);
        $paid = 0;
        foreach ($payments as $payment) {
            $paid += $payment->amount;
        }

        $payment_status = $paid <= 0 ? 'pending' : $purchase->payment_status;
        if ($this->sma->formatDecimal($purchase->grand_total) > $this->sma->formatDecimal($paid) && $paid > 0) {
            $payment_status = 'partial';
        } elseif ($this->sma->formatDecimal($purchase->grand_total) <= $this->sma->formatDecimal($paid)) {
            $payment_status = 'paid';
        }

        if ($this->db->update('purchases', array('paid' => $paid, 'payment_status' => $payment_status), array('id' => $id))) {
            return true;
        }

        return FALSE;
    }

    private function getBalanceQuantity($product_id, $warehouse_id = NULL) {
        $this->db->select('SUM(COALESCE(quantity_balance, 0)) as stock', False);
        $this->db->where('product_id', $product_id)->where('quantity_balance !=', 0);
        if ($warehouse_id>0) {
           $this->db->where('warehouse_id', $warehouse_id);
        }
        $this->db->group_start()->where('status', 'received')->or_where('status', 'partial')->group_end();    		
		$q = $this->db->get('purchase_items');		
        if ($q->num_rows() > 0) {
            $data = $q->row();
            $kho=$data->stock;
			if($kho==0){
				//kiem tra tong sl ban
				$tong_ban=(int)$this->getTongBanHang($product_id, $warehouse_id);
				
				if($tong_ban>0){
					$kho=$this->getBalanceQuantitySLBanNhieuHonKho($product_id, $warehouse_id);
				}
				return $kho;				 
			}else{
				//so luong ban nhieu hon so luong nhap
				$tong_ban=(int)$this->getTongBanHang($product_id, $warehouse_id);
				
				if($tong_ban>$kho){
					$kho=$this->getBalanceQuantitySLBanNhieuHonKho($product_id, $warehouse_id);
				}
			}
			return $kho;
        }
        return 0;
    }
	 private function getBalanceQuantitySLBanNhieuHonKho($product_id, $warehouse_id = NULL) {
        $this->db->select('SUM(COALESCE(quantity_balance, 0)) as stock', False);
        $this->db->where('product_id', $product_id);
        if ($warehouse_id) {
            $this->db->where('warehouse_id', $warehouse_id);
        }
        $q = $this->db->get('purchase_items');
        if ($q->num_rows() > 0) {
            $data = $q->row();
            return $data->stock;
        }
        return 0;
    }
	private function getTongBanHang($product_id, $warehouse_id = NULL) {
        $this->db->select('SUM(COALESCE(unit_quantity, 0)) as stock', False);
        $this->db->where('product_id', $product_id);
        if ($warehouse_id) {
            $this->db->where('warehouse_id', $warehouse_id);
        }
        $q = $this->db->get('sale_items');
        if ($q->num_rows() > 0) {
            $data = $q->row();
            return $data->stock;
        }
        return 0;
    }

    private function getBalanceVariantQuantity($variant_id, $warehouse_id = NULL) {
        $this->db->select('SUM(COALESCE(quantity_balance, 0)) as stock', False);
        $this->db->where('option_id', $variant_id)->where('quantity_balance !=', 0);
        if ($warehouse_id) {
            $this->db->where('warehouse_id', $warehouse_id);
        }
        $this->db->group_start()->where('status', 'received')->or_where('status', 'partial')->group_end();
        $q = $this->db->get('purchase_items');
        if ($q->num_rows() > 0) {
            $data = $q->row();
            return $data->stock;
        }
        return 0;
    }

    public function calculateAVCost($product_id, $warehouse_id, $net_unit_price, $unit_price, $quantity, $product_name, $option_id, $item_quantity) {
        $product = $this->getProductByID($product_id);
        $real_item_qty = $quantity;
        $wp_details = $this->getWarehouseProduct($warehouse_id, $product_id);
        $avg_net_unit_cost = $wp_details ? $wp_details->avg_cost : $product->cost;
        $avg_unit_cost = $wp_details ? $wp_details->avg_cost : $product->cost;
        if ($pis = $this->getPurchasedItems($product_id, $warehouse_id, $option_id)) {
            $cost_row = array();
            $quantity = $item_quantity;
            $balance_qty = $quantity;
            foreach ($pis as $pi) {
                if (!empty($pi) && $pi->quantity > 0 && $balance_qty <= $quantity) {
                    if ($pi->quantity_balance >= $quantity && $quantity > 0) {
                        $balance_qty = $pi->quantity_balance - $quantity;
                        $cost_row = array('date' => date('Y-m-d'), 'product_id' => $product_id, 'sale_item_id' => 'sale_items.id', 'purchase_item_id' => $pi->id, 'quantity' => $real_item_qty, 'purchase_net_unit_cost' => $avg_net_unit_cost, 'purchase_unit_cost' => $avg_unit_cost, 'sale_net_unit_price' => $net_unit_price, 'sale_unit_price' => $unit_price, 'quantity_balance' => $balance_qty, 'inventory' => 1, 'option_id' => $option_id);
                        $quantity = 0;
                    } elseif ($quantity > 0) {
                        $quantity = $quantity - $pi->quantity_balance;
                        $balance_qty = $quantity;
                        $cost_row = array('date' => date('Y-m-d'), 'product_id' => $product_id, 'sale_item_id' => 'sale_items.id', 'purchase_item_id' => $pi->id, 'quantity' => $pi->quantity_balance, 'purchase_net_unit_cost' => $avg_net_unit_cost, 'purchase_unit_cost' => $avg_unit_cost, 'sale_net_unit_price' => $net_unit_price, 'sale_unit_price' => $unit_price, 'quantity_balance' => 0, 'inventory' => 1, 'option_id' => $option_id);
                    }elseif ($quantity < 0) {
                        
						if($pi->quantity_balance>0){
							$quantity =  $pi->quantity_balance-$quantity;
						}else{
							$quantity =  $quantity-$pi->quantity_balance;
						}
						
                        $balance_qty = $quantity;
                        $cost_row = array('date' => date('Y-m-d'), 'product_id' => $product_id, 'sale_item_id' => 'sale_items.id', 'purchase_item_id' => $pi->id, 'quantity' => $pi->quantity_balance, 'purchase_net_unit_cost' => $avg_net_unit_cost, 'purchase_unit_cost' => $avg_unit_cost, 'sale_net_unit_price' => $net_unit_price, 'sale_unit_price' => $unit_price, 'quantity_balance' => $quantity, 'inventory' => 1, 'option_id' => $option_id);
                    }
                }
                if (empty($cost_row)) {
                    break;
                }
                $cost[] = $cost_row;
                if ($quantity == 0) {
                    break;
                }
            }
        }
        if ($quantity > 0 && !$this->Settings->overselling) {
            $this->session->set_flashdata('error', sprintf(lang("quantity_out_of_stock_for_%s"), ($pi->product_name ? $pi->product_name : $product_name)));
            redirect($_SERVER["HTTP_REFERER"]);
        } elseif ($quantity > 0) {
			
			if($item_quantity>0){
				$cost[] = array('date' => date('Y-m-d'), 'product_id' => $product_id, 'sale_item_id' => 'sale_items.id', 'purchase_item_id' => NULL, 'quantity' => $real_item_qty, 'purchase_net_unit_cost' => $avg_net_unit_cost, 'purchase_unit_cost' => $avg_unit_cost, 'sale_net_unit_price' => $net_unit_price, 'sale_unit_price' => $unit_price, 'quantity_balance' => NULL, 'overselling' => 1, 'inventory' => 1);
				$cost[] = array('pi_overselling' => 1, 'product_id' => $product_id, 'quantity_balance' => (0 - $quantity), 'warehouse_id' => $warehouse_id, 'option_id' => $option_id);
			}
			
        }elseif ($item_quantity <0) {
            $cost[] = array('date' => date('Y-m-d'), 'product_id' => $product_id, 'sale_item_id' => 'sale_items.id', 'purchase_item_id' => NULL, 'quantity' => $real_item_qty, 'purchase_net_unit_cost' => $avg_net_unit_cost, 'purchase_unit_cost' => $avg_unit_cost, 'sale_net_unit_price' => $net_unit_price, 'sale_unit_price' => $unit_price, 'quantity_balance' => NULL, 'overselling' => 1, 'inventory' => 1);
            $cost[] = array('pi_overselling' => 1, 'product_id' => $product_id, 'quantity_balance' => (0 - $item_quantity), 'warehouse_id' => $warehouse_id, 'option_id' => $option_id);
        }
        return $cost;
    }

    public function calculateCost($product_id, $warehouse_id, $net_unit_price, $unit_price, $quantity, $product_name, $option_id, $item_quantity) {
        $pis = $this->getPurchasedItems($product_id, $warehouse_id, $option_id);
        $real_item_qty = $quantity;
        $quantity = $item_quantity;
        $balance_qty = $quantity;
        foreach ($pis as $pi) {
            $cost_row = NULL;
            if (!empty($pi) && $balance_qty <= $quantity) {
                $purchase_unit_cost = $pi->unit_cost ? $pi->unit_cost : ($pi->net_unit_cost + ($pi->item_tax / $pi->quantity));
                if ($pi->quantity_balance >= $quantity && $quantity > 0) {
                    $balance_qty = $pi->quantity_balance - $quantity;
                    $cost_row = array('date' => date('Y-m-d'), 'product_id' => $product_id, 'sale_item_id' => 'sale_items.id', 'purchase_item_id' => $pi->id, 'quantity' => $real_item_qty, 'purchase_net_unit_cost' => $pi->net_unit_cost, 'purchase_unit_cost' => $purchase_unit_cost, 'sale_net_unit_price' => $net_unit_price, 'sale_unit_price' => $unit_price, 'quantity_balance' => $balance_qty, 'inventory' => 1, 'option_id' => $option_id);
                    $quantity = 0;
                }else if ($pi->quantity_balance >= $quantity && $quantity < 0) {
                    $balance_qty = $pi->quantity_balance - $quantity;
                    $cost_row = array('date' => date('Y-m-d'), 'product_id' => $product_id, 'sale_item_id' => 'sale_items.id', 'purchase_item_id' => $pi->id, 'quantity' => $real_item_qty, 'purchase_net_unit_cost' => $pi->net_unit_cost, 'purchase_unit_cost' => $purchase_unit_cost, 'sale_net_unit_price' => $net_unit_price, 'sale_unit_price' => $unit_price, 'quantity_balance' => $balance_qty, 'inventory' => 1, 'option_id' => $option_id);
                    $quantity = 0;
                } elseif ($pi->quantity_balance < $quantity && $quantity > 0) {
                    $quantity = $quantity - $pi->quantity_balance;
                    $balance_qty = $quantity;
                    $cost_row = array('date' => date('Y-m-d'), 'product_id' => $product_id, 'sale_item_id' => 'sale_items.id', 'purchase_item_id' => $pi->id, 'quantity' => $pi->quantity_balance, 'purchase_net_unit_cost' => $pi->net_unit_cost, 'purchase_unit_cost' => $purchase_unit_cost, 'sale_net_unit_price' => $net_unit_price, 'sale_unit_price' => $unit_price, 'quantity_balance' => 0, 'inventory' => 1, 'option_id' => $option_id);
                }elseif ($pi->quantity_balance < $quantity && $quantity < 0) {
                    $quantity = $quantity;
                    $balance_qty = abs($quantity);
                    $cost_row = array('date' => date('Y-m-d'), 'product_id' => $product_id, 'sale_item_id' => 'sale_items.id', 'purchase_item_id' => $pi->id, 'quantity' => $pi->quantity_balance, 'purchase_net_unit_cost' => $pi->net_unit_cost, 'purchase_unit_cost' => $purchase_unit_cost, 'sale_net_unit_price' => $net_unit_price, 'sale_unit_price' => $unit_price, 'quantity_balance' => $balance_qty, 'inventory' => 1, 'option_id' => $option_id);
                }
				//lhson code add 0944104004 31/3/2018
            }
            $cost[] = $cost_row;
            if ($quantity == 0) {
                break;
            }
        }
        if ($quantity > 0) {
            $this->session->set_flashdata('error', sprintf(lang("quantity_out_of_stock_for_%s"), ($pi->product_name ? $pi->product_name : $product_name)));
            redirect($_SERVER["HTTP_REFERER"]);
        }
        return $cost;
    }

    public function getPurchasedItems($product_id, $warehouse_id, $option_id = NULL)
    {
        $orderby = ($this->Settings->accounting_method == 1) ? 'asc' : 'desc';
        $this->db->select('id, quantity, quantity_balance, net_unit_cost, unit_cost, item_tax');
        $this->db->where('product_id', $product_id)->where('warehouse_id', $warehouse_id)->where('quantity_balance !=', 0);
        if ($option_id) {
            $this->db->where('option_id', $option_id);
        }
        $this->db->group_start()->where('status', 'received')->or_where('status', 'partial')->group_end();
        $this->db->group_by('id');
        $this->db->order_by('date', $orderby);
        $this->db->order_by('purchase_id', $orderby);
        $q = $this->db->get('purchase_items');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getProductComboItems($pid, $warehouse_id = NULL)
    {
        $this->db->select('products.id as id, combo_items.item_code as code, combo_items.quantity as qty, products.name as name, products.type as type, combo_items.unit_price as unit_price, warehouses_products.quantity as quantity')
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

    public function item_costing($item, $pi = NULL) {
        $item_quantity = $pi ? $item['aquantity'] : $item['quantity'];
        if (!isset($item['option_id']) || empty($item['option_id']) || $item['option_id'] == 'null') {
            $item['option_id'] = NULL;
        }
		//lhson code 0944104004 luu y loi sai loi nhuan khi ban am kho
        if ($this->Settings->accounting_method != 2 && !$this->Settings->overselling) {

            if ($this->getProductByID($item['product_id'])) {
                if ($item['product_type'] == 'standard') {
                    $unit = $this->getUnitByID($item['product_unit_id']);
                    $item['net_unit_price'] = $this->convertToBase($unit, $item['net_unit_price']);
                    $item['unit_price'] = $this->convertToBase($unit, $item['unit_price']);
                    $cost = $this->calculateCost($item['product_id'], $item['warehouse_id'], $item['net_unit_price'], $item['unit_price'], $item['quantity'], $item['product_name'], $item['option_id'], $item_quantity);
                } elseif ($item['product_type'] == 'combo') {
                    $combo_items = $this->getProductComboItems($item['product_id'], $item['warehouse_id']);
                    foreach ($combo_items as $combo_item) {
                        $pr = $this->getProductByCode($combo_item->code);
                        if ($pr->tax_rate) {
                            $pr_tax = $this->getTaxRateByID($pr->tax_rate);
                            if ($pr->tax_method) {
                                $item_tax = $this->sma->formatDecimal((($combo_item->unit_price) * $pr_tax->rate) / (100 + $pr_tax->rate));
                                $net_unit_price = $combo_item->unit_price - $item_tax;
                                $unit_price = $combo_item->unit_price;
                            } else {
                                $item_tax = $this->sma->formatDecimal((($combo_item->unit_price) * $pr_tax->rate) / 100);
                                $net_unit_price = $combo_item->unit_price;
                                $unit_price = $combo_item->unit_price + $item_tax;
                            }
                        } else {
                            $net_unit_price = $combo_item->unit_price;
                            $unit_price = $combo_item->unit_price;
                        }
                        if ($pr->type == 'standard') {
                            $cost[] = $this->calculateCost($pr->id, $item['warehouse_id'], $net_unit_price, $unit_price, ($combo_item->qty * $item['quantity']), $pr->name, NULL, $item_quantity);
                        } else {
                            $cost[] = array(array('date' => date('Y-m-d'), 'product_id' => $pr->id, 'sale_item_id' => 'sale_items.id', 'purchase_item_id' => NULL, 'quantity' => ($combo_item->qty * $item['quantity']), 'purchase_net_unit_cost' => 0, 'purchase_unit_cost' => 0, 'sale_net_unit_price' => $combo_item->unit_price, 'sale_unit_price' => $combo_item->unit_price, 'quantity_balance' => NULL, 'inventory' => NULL));
                        }
                    }
                } else {
                    $cost = array(array('date' => date('Y-m-d'), 'product_id' => $item['product_id'], 'sale_item_id' => 'sale_items.id', 'purchase_item_id' => NULL, 'quantity' => $item['quantity'], 'purchase_net_unit_cost' => 0, 'purchase_unit_cost' => 0, 'sale_net_unit_price' => $item['net_unit_price'], 'sale_unit_price' => $item['unit_price'], 'quantity_balance' => NULL, 'inventory' => NULL));
                }
            } elseif ($item['product_type'] == 'manual') {
                $cost = array(array('date' => date('Y-m-d'), 'product_id' => $item['product_id'], 'sale_item_id' => 'sale_items.id', 'purchase_item_id' => NULL, 'quantity' => $item['quantity'], 'purchase_net_unit_cost' => 0, 'purchase_unit_cost' => 0, 'sale_net_unit_price' => $item['net_unit_price'], 'sale_unit_price' => $item['unit_price'], 'quantity_balance' => NULL, 'inventory' => NULL));
            }

        } else {

            if ($this->getProductByID($item['product_id'])) {
                if ($item['product_type'] == 'standard') {
					
					$unit = $this->getUnitByID($item['product_unit_id']);
                    $item['net_unit_price'] = $this->convertToBase($unit, $item['net_unit_price']);
                    $item['unit_price'] = $this->convertToBase($unit, $item['unit_price']);
					
                    $cost = $this->calculateAVCost($item['product_id'], $item['warehouse_id'], $item['net_unit_price'], $item['unit_price'], $item['quantity'], $item['product_name'], $item['option_id'], $item_quantity);
                } elseif ($item['product_type'] == 'combo') {
                    $combo_items = $this->getProductComboItems($item['product_id'], $item['warehouse_id']);
                    foreach ($combo_items as $combo_item) {
                        $pr = $this->getProductByCode($combo_item->code);
                        if ($pr->tax_rate) {
                            $pr_tax = $this->getTaxRateByID($pr->tax_rate);
                            if ($pr->tax_method) {
                                $item_tax = $this->sma->formatDecimal((($combo_item->unit_price) * $pr_tax->rate) / (100 + $pr_tax->rate));
                                $net_unit_price = $combo_item->unit_price - $item_tax;
                                $unit_price = $combo_item->unit_price;
                            } else {
                                $item_tax = $this->sma->formatDecimal((($combo_item->unit_price) * $pr_tax->rate) / 100);
                                $net_unit_price = $combo_item->unit_price;
                                $unit_price = $combo_item->unit_price + $item_tax;
                            }
                        } else {
                            $net_unit_price = $combo_item->unit_price;
                            $unit_price = $combo_item->unit_price;
                        }
                        $cost[] = $this->calculateAVCost($combo_item->id, $item['warehouse_id'], $net_unit_price, $unit_price, ($combo_item->qty * $item['quantity']), $item['product_name'], $item['option_id'], $item_quantity);
                    }
                } else {
                    $cost = array(array('date' => date('Y-m-d'), 'product_id' => $item['product_id'], 'sale_item_id' => 'sale_items.id', 'purchase_item_id' => NULL, 'quantity' => $item['quantity'], 'purchase_net_unit_cost' => 0, 'purchase_unit_cost' => 0, 'sale_net_unit_price' => $item['net_unit_price'], 'sale_unit_price' => $item['unit_price'], 'quantity_balance' => NULL, 'inventory' => NULL));
                }
            } elseif ($item['product_type'] == 'manual') {
                $cost = array(array('date' => date('Y-m-d'), 'product_id' => $item['product_id'], 'sale_item_id' => 'sale_items.id', 'purchase_item_id' => NULL, 'quantity' => $item['quantity'], 'purchase_net_unit_cost' => 0, 'purchase_unit_cost' => 0, 'sale_net_unit_price' => $item['net_unit_price'], 'sale_unit_price' => $item['unit_price'], 'quantity_balance' => NULL, 'inventory' => NULL));
            }

        }
        return $cost;
    }

    public function costing($items) {
        $citems = array();
        foreach ($items as $item) {
            $option = (isset($item['option_id']) && !empty($item['option_id']) && $item['option_id'] != 'null' && $item['option_id'] != 'false') ? $item['option_id'] : '';
            $pr = $this->getProductByID($item['product_id']);
            $item['option_id'] = $option;
            if ($pr->type == 'standard') {
                if (isset($citems['p' . $item['product_id'] . 'o' . $item['option_id']])) {
                    $citems['p' . $item['product_id'] . 'o' . $item['option_id']]['aquantity'] += $item['quantity'];
                } else {
                    $citems['p' . $item['product_id'] . 'o' . $item['option_id']] = $item;
                    $citems['p' . $item['product_id'] . 'o' . $item['option_id']]['aquantity'] = $item['quantity'];
                }
            } elseif ($pr->type == 'combo') {
                $wh = $this->Settings->overselling ? NULL : $item['warehouse_id'];
                $combo_items = $this->getProductComboItems($item['product_id'], $wh);
                foreach ($combo_items as $combo_item) {
                    if ($combo_item->type == 'standard') {
                        if (isset($citems['p' . $combo_item->id . 'o' . $item['option_id']])) {
                            $citems['p' . $combo_item->id . 'o' . $item['option_id']]['aquantity'] += ($combo_item->qty*$item['quantity']);
                        } else {
                            $cpr = $this->getProductByID($combo_item->id);
                            if ($cpr->tax_rate) {
                                $cpr_tax = $this->getTaxRateByID($cpr->tax_rate);
                                if ($cpr->tax_method) {
                                    $item_tax = $this->sma->formatDecimal((($combo_item->unit_price) * $cpr_tax->rate) / (100 + $cpr_tax->rate));
                                    $net_unit_price = $combo_item->unit_price - $item_tax;
                                    $unit_price = $combo_item->unit_price;
                                } else {
                                    $item_tax = $this->sma->formatDecimal((($combo_item->unit_price) * $cpr_tax->rate) / 100);
                                    $net_unit_price = $combo_item->unit_price;
                                    $unit_price = $combo_item->unit_price + $item_tax;
                                }
                            } else {
                                $net_unit_price = $combo_item->unit_price;
                                $unit_price = $combo_item->unit_price;
                            }
                            $cproduct = array('product_id' => $combo_item->id, 'product_name' => $cpr->name, 'product_type' => $combo_item->type, 'quantity' => ($combo_item->qty*$item['quantity']), 'net_unit_price' => $net_unit_price, 'unit_price' => $unit_price, 'warehouse_id' => $item['warehouse_id'], 'item_tax' => $item_tax, 'tax_rate_id' => $cpr->tax_rate, 'tax' => ($cpr_tax->type == 1 ? $cpr_tax->rate.'%' : $cpr_tax->rate), 'option_id' => NULL, 'product_unit_id' => $cpr->unit);
                            $citems['p' . $combo_item->id . 'o' . $item['option_id']] = $cproduct;
                            $citems['p' . $combo_item->id . 'o' . $item['option_id']]['aquantity'] = ($combo_item->qty*$item['quantity']);
                        }
                    }
                }
            }
        }
        //$this->sma->print_arrays($combo_items, $citems);
		
        $cost = array();
        foreach ($citems as $item) {
            $item['aquantity'] = $citems['p' . $item['product_id'] . 'o' . $item['option_id']]['aquantity'];
            $cost[] = $this->item_costing($item, TRUE);
        }
		//$this->sma->print_arrays($cost);
		//lhson check 04/05
        return $cost;
    }

    public function syncQuantity($sale_id = NULL, $purchase_id = NULL, $oitems = NULL, $product_id = NULL) {
        if ($sale_id) {

            $sale_items = $this->getAllSaleItems($sale_id);
            foreach ($sale_items as $item) {
                if ($item->product_type == 'standard') {
                    $this->syncProductQty($item->product_id, $item->warehouse_id);
                    if (isset($item->option_id) && !empty($item->option_id)) {
                        $this->syncVariantQty($item->option_id, $item->warehouse_id, $item->product_id);
                    }
                } elseif ($item->product_type == 'combo') {
                    $wh = $this->Settings->overselling ? NULL : $item->warehouse_id;
                    $combo_items = $this->getProductComboItems($item->product_id, $wh);
                    foreach ($combo_items as $combo_item) {
                        if($combo_item->type == 'standard') {
                            $this->syncProductQty($combo_item->id, $item->warehouse_id);
                        }
                    }
                }
            }

        } elseif ($purchase_id) {

            $purchase_items = $this->getAllPurchaseItems($purchase_id);
            foreach ($purchase_items as $item) {
                $this->syncProductQty($item->product_id, $item->warehouse_id);
                if (isset($item->option_id) && !empty($item->option_id)) {
                    $this->syncVariantQty($item->option_id, $item->warehouse_id, $item->product_id);
                }
            }

        } elseif ($oitems) {

            foreach ($oitems as $item) {
                if (isset($item->product_type)) {
                    if ($item->product_type == 'standard') {
                        $this->syncProductQty($item->product_id, $item->warehouse_id);
                        if (isset($item->option_id) && !empty($item->option_id)) {
                            $this->syncVariantQty($item->option_id, $item->warehouse_id, $item->product_id);
                        }
                    } elseif ($item->product_type == 'combo') {
                        $combo_items = $this->getProductComboItems($item->product_id, $item->warehouse_id);
                        foreach ($combo_items as $combo_item) {
                            if($combo_item->type == 'standard') {
                                $this->syncProductQty($combo_item->id, $item->warehouse_id);
                            }
                        }
                    }
                } else {
                    $this->syncProductQty($item->product_id, $item->warehouse_id);
                    if (isset($item->option_id) && !empty($item->option_id)) {
                        $this->syncVariantQty($item->option_id, $item->warehouse_id, $item->product_id);
                    }
                }
            }

        } elseif ($product_id) {
            $warehouses = $this->getAllWarehouses();
            foreach ($warehouses as $warehouse) {
                $this->syncProductQty($product_id, $warehouse->id);				                
				if ($product_variants = $this->getProductVariants($product_id)) {
                    foreach ($product_variants as $pv) {
                        $this->syncVariantQty($pv->id, $warehouse->id, $product_id);
                    }
                }
            }
        }
		//lhson xoa cac nhap hang 0944104004 31/3/2018
		$this->DeletePurchaseItemsByProduct();
		//tien hanh dong bo kho theo cong thuc moi lhson 21/05/2018
		
    }
	function DeletePurchaseItemsByProduct($product_id=0,$warehouse_id=0){
		//tien hanh xoa toan bo scodeweb_purchase_items neu khong ton tai nhap kho cua san pham do theo tung kho hang
		$_query="DELETE FROM {$this->db->dbprefix('purchase_items')} WHERE purchase_id IS NULL AND transfer_id IS NULL AND (product_code is NULL OR product_code='')";
		$q= $this->db->query($_query);	
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

    public function getAllSaleItems($sale_id) {
        $q = $this->db->get_where('sale_items', array('sale_id' => $sale_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getAllPurchaseItems($purchase_id) {
        $q = $this->db->get_where('purchase_items', array('purchase_id' => $purchase_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function syncPurchaseItems($data = array()) {
        if (!empty($data)) {
            foreach ($data as $items) {
                foreach ($items as $item) {
                    if (isset($item['pi_overselling'])) {
                        unset($item['pi_overselling']);
                        $option_id = (isset($item['option_id']) && !empty($item['option_id'])) ? $item['option_id'] : NULL;
                        $clause = array('purchase_id' => NULL, 'transfer_id' => NULL, 'product_id' => $item['product_id'], 'warehouse_id' => $item['warehouse_id'], 'option_id' => $option_id);
                        if ($pi = $this->getPurchasedItem($clause)) {
                            $quantity_balance = $pi->quantity_balance + $item['quantity_balance'];
                            $this->db->update('purchase_items', array('quantity_balance' => $quantity_balance), array('id' => $pi->id));
                        } else {
                            $clause['quantity'] = 0;
                            $clause['item_tax'] = 0;
                            $clause['quantity_balance'] = $item['quantity_balance'];
                            $clause['status'] = 'received';
                            $this->db->insert('purchase_items', $clause);
                        }
                    } else {
                        if ($item['inventory']) {
                            $this->db->update('purchase_items', array('quantity_balance' => $item['quantity_balance']), array('id' => $item['purchase_item_id']));
                        }
                    }
                }
            }
            return TRUE;
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

    public function check_customer_deposit($customer_id, $amount)
    {
        $customer = $this->getCompanyByID($customer_id);
        return $customer->deposit_amount >= $amount;
    }

    public function getWarehouseProduct($warehouse_id, $product_id)
    {
        $q = $this->db->get_where('warehouses_products', array('product_id' => $product_id, 'warehouse_id' => $warehouse_id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getAllBaseUnits()
    {
       // $q = $this->db->get_where("units", array('base_unit' => NULL));
         $q = $this->db->get("units");
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getUnitsByBUID($base_unit)
    {
        $this->db->where('id', $base_unit)->or_where('base_unit', $base_unit);
        $q = $this->db->get("units");
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getUnitByID($id)
    {
        $q = $this->db->get_where("units", array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    public function getUnitNameByID($id)
    {
        $q = $this->db->get_where("units", array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row()->name;
        }
        return FALSE;
    }
    public function getPriceGroupByID($id)
    {
        $q = $this->db->get_where('price_groups', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getProductGroupPrice($product_id, $group_id)
    {
        $q = $this->db->get_where('product_prices', array('price_group_id' => $group_id, 'product_id' => $product_id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getAllBrands()
    {
        $q = $this->db->get("brands");
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
	public function getAllXuatxus()
    {
        $q = $this->db->get("xuatxu");
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getBrandByID($id)
    {
        $q = $this->db->get_where('brands', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
	
    public function convertToBase($unit, $value)
    {
        return $value;//lhson code add 15/5/2020
        switch($unit->operator) {
            case '*':
                return $value / $unit->operation_value;
                break;
            case '/':
                return $value * $unit->operation_value;
                break;
            case '+':
                return $value - $unit->operation_value;
                break;
            case '-':
                return $value + $unit->operation_value;
                break;
            default:
                return $value;
        }
    }
	public function calculateDiscount($discount = NULL, $amount) {
        if ($discount && $this->Settings->product_discount) {
            $dpos = strpos($discount, '%');
            if ($dpos !== false) {
                $pds = explode("%", $discount);
                return $this->sma->formatDecimal(((($this->sma->formatDecimal($amount)) * (Float) ($pds[0])) / 100), 4);
            } else {
                return $this->sma->formatDecimal($discount, 4);
            }
        }
        return 0;
    }
	public function calculateOrderTax($order_tax_id = NULL, $amount) {
        if ($this->Settings->tax2 != 0 && $order_tax_id) {
            if ($order_tax_details = $this->site->getTaxRateByID($order_tax_id)) {
                if ($order_tax_details->type == 1) {
                    return $this->sma->formatDecimal((($amount * $order_tax_details->rate) / 100), 4);
                } else {
                    return $this->sma->formatDecimal($order_tax_details->rate, 4);
                }
            }
        }
        return 0;
    }
	 public function getXuatxuById($name)
    {
        $q = $this->db->get_where('xuatxu', array('id' => $name), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
	
	 function calculateTax($product_details = NULL, $tax_details, $custom_value = NULL, $c_on = NULL) {
        $value = $custom_value ? $custom_value : (($c_on == 'cost') ? $product_details->cost : $product_details->price);
        $tax_amount = 0; $tax = 0;
        if ($tax_details && $tax_details->type == 1 && $tax_details->rate != 0) {
            if ($product_details && $product_details->tax_method == 1) {
                $tax_amount = $this->sma->formatDecimal((($value) * $tax_details->rate) / 100, 4);
                $tax = $this->sma->formatDecimal($tax_details->rate, 0) . "%";
            } else {
                $tax_amount = $this->sma->formatDecimal((($value) * $tax_details->rate) / (100 + $tax_details->rate), 4);
                $tax = $this->sma->formatDecimal($tax_details->rate, 0) . "%";
            }
        } elseif ($tax_details && $tax_details->type == 2) {
            $tax_amount = $this->sma->formatDecimal($tax_details->rate);
            $tax = $this->sma->formatDecimal($tax_details->rate, 0);
        }
        return array('id' => $tax_details->id, 'tax' => $tax, 'amount' => $tax_amount);
    }
	 public function getTongthuhoi($product_id=NULL,$warehouse_id=NULL,$start=null,$end=null)
    {
        $this->db->select('SUM(COALESCE(quantity, 0)) as stock', False);
        $this->db->where('product_id', $product_id);
        if ($warehouse_id) {
            $this->db->where('return_items.warehouse_id', $warehouse_id);
        }
        if ($start&&$end) {
            $this->db->join('returns', 'returns.id=return_items.return_id');
            $this->db->where("scodeweb_returns.date BETWEEN '".date('Y-m-d', strtotime($start))." 00:00:00' AND '".date('Y-m-d', strtotime($end))." 23:59:59'",null,false);
        }
        $q = $this->db->get('return_items');
        if ($q->num_rows() > 0) {
            $data = $q->row();
            return $data->stock;
        }
        return 0;
    }
	public function getTongSoluongBanra($product_id,$warehouse_id,$start=null,$end=null)
    {
        $this->db->select('SUM(COALESCE(unit_quantity, 0)) as stock', False);
        $this->db->where('product_id', $product_id);
        $this->db->where('sales.sale_status','completed');
        if ($warehouse_id) {
            $this->db->where('sale_items.warehouse_id', $warehouse_id);
        }
        $this->db->join('sales', 'sales.id=sale_items.sale_id');
		if ($start&&$end) {
            
            $this->db->where("date BETWEEN '".date('Y-m-d', strtotime($start))." 00:00:00' AND '".date('Y-m-d', strtotime($end))." 23:59:59'",null,false);
        }
        $q = $this->db->get('sale_items');
        if ($q->num_rows() > 0) {
            $data = $q->row();
            return $data->stock;
        }
        return 0;
    }
	private function _tongchinhkhoduong($product_id, $warehouse_id = NULL,$start=null,$end=null) {
        $this->db->select('SUM(COALESCE(quantity, 0)) as stock', False);
		$this->db->where('type','addition');
		$this->db->where('product_id', $product_id);
        if ($warehouse_id) {
            $this->db->where('adjustment_items.warehouse_id', $warehouse_id);
        }
        if ($start&&$end) {
            $this->db->join('adjustments', 'adjustments.id=adjustment_items.adjustment_id');
            $this->db->where("scodeweb_adjustments.date BETWEEN '".date('Y-m-d', strtotime($start))." 00:00:00' AND '".date('Y-m-d', strtotime($end))." 23:59:59'",null,false);
        }
        $q = $this->db->get('adjustment_items');
		if ($q->num_rows() > 0) {
            $data = $q->row();
            return $data->stock;
        }
		return 0;
	}
	private function _tongchinhkhoam($product_id, $warehouse_id = NULL,$start=null,$end=null) {
        $this->db->select('SUM(COALESCE(quantity, 0)) as stock', False);
		$this->db->where('type','subtraction');
		$this->db->where('product_id', $product_id);
        if ($warehouse_id) {
            $this->db->where('adjustment_items.warehouse_id', $warehouse_id);
        }
        if ($start&&$end) {
            $this->db->join('adjustments', 'adjustments.id=adjustment_items.adjustment_id');
            $this->db->where("scodeweb_adjustments.date BETWEEN '".date('Y-m-d', strtotime($start))." 00:00:00' AND '".date('Y-m-d', strtotime($end))." 23:59:59'",null,false);
        }
        $q = $this->db->get('adjustment_items');
		if ($q->num_rows() > 0) {
            $data = $q->row();
            return $data->stock;
        }
		return 0;
	}
    public function getTotalSalesSanPham($product_id=0,$warehouse_id = NULL,$start=NULL, $end=NULL )
    {
        $this->db->select('product_unit_id,sum(COALESCE(subtotal, 0)) as total_amount', FALSE)->where('sale_status !=', 'pending');
        if ($start&&$end) {
            $this->db->where("date BETWEEN '".date('Y-m-d', strtotime($start))." 00:00:00' AND '".date('Y-m-d', strtotime($end))." 23:59:59'",null,false);
        }               
        if ($warehouse_id) {
            $this->db->where('sales.warehouse_id', $warehouse_id);       
         }
        $this->db->join('sale_items', 'sales.id=sale_items.sale_id')->where('sale_items.product_id', $product_id); 
        $this->db->group_by('sale_items.product_id');
        $q = $this->db->get('sales'); 

        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
	private function _chuyenkhodi($product_id, $warehouse_id = NULL,$start=null,$end=null) {
        $query="SELECT SUM(COALESCE(quantity, 0)) as stock FROM `scodeweb_transfers` a, scodeweb_purchase_items b WHERE a.id=b.transfer_id AND b.product_id=".$product_id;
		//$warehouse_id=NULL;
		if($warehouse_id){
			$query.=" and a.from_warehouse_id=".$warehouse_id;
		}
        if ($start&&$end) {
           $query.=" AND a.date BETWEEN '".date('Y-m-d', strtotime($start))." 00:00:00' AND '".date('Y-m-d', strtotime($end))." 23:59:59'";
        }
		$q=$this->db->query($query);
		if ($q->num_rows() > 0) {
            $data = $q->row();
            return $data->stock;
        }
		return 0;
	}
    public function getTonDauByDate($product_id, $warehouse_id = NULL,$start=null,$end=null) {
        $this->db->select('SUM(COALESCE(quantity, 0)) as stock', False);
        if ($warehouse_id) {
            $this->db->where('warehouse_id', $warehouse_id);
        }
        $this->db->where('product_id', $product_id);
        $this->db->where('thuhoi_id',0);
        $this->db->where('bandau_id>',0);
        if ($start&&$end) {            
            $this->db->where("date BETWEEN '".date('Y-m-d', strtotime($start))." 00:00:00' AND '".date('Y-m-d', strtotime($end))." 23:59:59'",null,false);
        }
        $q = $this->db->get('purchase_items');
        $_nhapkho=0;
        if ($q->num_rows() > 0) {
            $data = $q->row();
            return $_nhapkho=(float)$data->stock;
        }
        return 0;
    }
	public function tonkhohientai($product_id, $warehouse_id = NULL,$start=null,$end=null) {
        $this->db->select('SUM(COALESCE(quantity, 0)) as stock', False);
        if ($warehouse_id) {
            $this->db->where('warehouse_id', $warehouse_id);
        }
        $this->db->where('status','received'); 
        $this->db->where('product_id', $product_id);
        $this->db->where('thuhoi_id',0);
        if ($start&&$end) {
            
            $this->db->where("date BETWEEN '".date('Y-m-d', strtotime($start))." 00:00:00' AND '".date('Y-m-d', strtotime($end))." 23:59:59'",null,false);
        }
        
        $q = $this->db->get('purchase_items');
        $_nhapkho=0;
        if ($q->num_rows() > 0) {
            $data = $q->row();
            $_nhapkho=(float)$data->stock;
        }

        
        $this->db->select('SUM(COALESCE(quantity_received, 0)) as stock', False);
        if ($warehouse_id) {
            $this->db->where('warehouse_id', $warehouse_id);
        }
        $this->db->where('product_id', $product_id);
        $this->db->where('thuhoi_id',0);
        if ($start&&$end) {
            
            $this->db->where("date BETWEEN '".date('Y-m-d', strtotime($start))." 00:00:00' AND '".date('Y-m-d', strtotime($end))." 23:59:59'",null,false);
        }
        $this->db->where('status','partial'); 

        $q = $this->db->get('purchase_items');
        
        if ($q->num_rows() > 0) {
            $data = $q->row();
            $_nhapkho+=(float)$data->stock;
        }
		//quy doi sang don vi ban
        $donvi_ban=$this->getdonvitinh($product_id);   
         $new_nhap=$_nhapkho;
        switch($donvi_ban['operator']) {                    
            case '*':           
                $new_nhap=(float)$_nhapkho/(float)$donvi_ban['operation_value'];
                break;          
             case '/':                      
                $new_nhap=(float)$_nhapkho*(float)$donvi_ban['operation_value'];
                break;      
             case '+':                      
                $new_nhap=(float)$_nhapkho-(float)$donvi_ban['operation_value'];
                break;
            case '-':                   
                $new_nhap=(float)$_nhapkho+(float)$donvi_ban['operation_value'];
                break;              
            }                   
         
        $_nhapkho=  $new_nhap;
          
		//điều chỉnh kho +
		$dieuchinhkhoduong=(float)$this->_tongchinhkhoduong($product_id, $warehouse_id,$start,$end);
		// thu hồi +
		$thuhoi=(float)$this->getTongthuhoi($product_id, $warehouse_id,$start,$end); 
		
		//bán ra
		$banra=(float)$this->getTongSoluongBanra($product_id, $warehouse_id,$start,$end);
		//điều chỉnh kho âm
		$dieuchinhkhoam=(float)$this->_tongchinhkhoam($product_id, $warehouse_id,$start,$end);
		//chuyển kho
		$chuyenkho=(float)$this->_chuyenkhodi($product_id, $warehouse_id,$start,$end);		
		
		//kho hien tai = tong nhap + dieu chinh kho duong + thu hoi - ban ra - dieu chinh kho am - chuyen kho
		//$this->sma->print_arrays(array($_nhapkho,$dieuchinhkhoduong,$thuhoi));
	 	//$this->sma->print_arrays(array($banra,$dieuchinhkhoam,$chuyenkho));
		$khohientai=($_nhapkho+$dieuchinhkhoduong+$thuhoi)-($banra+$dieuchinhkhoam+$chuyenkho);

        return $khohientai;
		// $this->sma->print_arrays(array($khohientai));
		//tien hanh update kho hien tai vao san pham va cac kho hang 		
    }
	public function getongtonkhoallkho($product_id, $warehouse_id = NULL){
		 $this->db->select('SUM(COALESCE(quantity, 0)) as stock', False);
        if ($warehouse_id) {
            $this->db->where('warehouse_id', $warehouse_id);
        }
		$this->db->where('product_id', $product_id);
        $q = $this->db->get('warehouses_products');
		$_nhapkho=0;
		if ($q->num_rows() > 0) {
            $data = $q->row();
            return $_nhapkho=(float)$data->stock;
        }
		return 0;
	}
	public function getongtonkhoallkho_bienthe($option_id, $warehouse_id = NULL){
		 $this->db->select('SUM(COALESCE(quantity, 0)) as stock', False);
        if ($warehouse_id) {
            $this->db->where('warehouse_id', $warehouse_id);
        }
		$this->db->where('option_id', $product_id);
        $q = $this->db->get('warehouses_products');
		$_nhapkho=0;
		if ($q->num_rows() > 0) {
            $data = $q->row();
            return $_nhapkho=(float)$data->stock;
        }
		return 0;
	}
    function getdonvitinh($idsp=0){   
    
        $query = $this->db->query("select sale_unit from scodeweb_products where id='$idsp'");        
        $rsdonvi=$query->row_array();       
        $id_donvi=$rsdonvi['sale_unit'];        
        if($id_donvi>0){    
            $query = $this->db->query("select * from scodeweb_units where id='$id_donvi'");     
            return $query->row_array();     
        }   
    }
	public function tonkhohientai_theobienthe($option_id, $warehouse_id = NULL) {
        $this->db->select('SUM(COALESCE(quantity, 0)) as stock', False);
        if ($warehouse_id) {
            $this->db->where('warehouse_id', $warehouse_id);
        }
		$this->db->where('option_id', $option_id);
		$this->db->where('thuhoi_id',0);
        $q = $this->db->get('purchase_items');
		$_nhapkho=0;
		if ($q->num_rows() > 0) {
            $data = $q->row();
            $_nhapkho=(float)$data->stock;
        }
		

		//điều chỉnh kho +
		$dieuchinhkhoduong=(float)$this->_tongchinhkhoduong_bienthe($option_id, $warehouse_id);
		// thu hồi +
		$thuhoi=(float)$this->getTongthuhoi_bienthe($option_id, $warehouse_id);
		
		//bán ra
		$banra=(float)$this->getTongSoluongBanra_bienthe($option_id, $warehouse_id);
		//điều chỉnh kho âm
		$dieuchinhkhoam=(float)$this->_tongchinhkhoam_bienthe($option_id, $warehouse_id);
		//chuyển kho
		$chuyenkho=(float)$this->_chuyenkhodi_bienthe($option_id, $warehouse_id);		
		
		//kho hien tai = tong nhap + dieu chinh kho duong + thu hoi - ban ra - dieu chinh kho am - chuyen kho
		//$this->sma->print_arrays(array($_nhapkho,$dieuchinhkhoduong,$thuhoi));
	 	//$this->sma->print_arrays(array($banra,$dieuchinhkhoam,$chuyenkho));
		return $khohientai=($_nhapkho+$dieuchinhkhoduong+$thuhoi)-($banra+$dieuchinhkhoam+$chuyenkho);
		// $this->sma->print_arrays(array($khohientai));
		//tien hanh update kho hien tai vao san pham va cac kho hang 		
    }
	private function _tongchinhkhoduong_bienthe($option_id, $warehouse_id = NULL) {
        $this->db->select('SUM(COALESCE(quantity, 0)) as stock', False);
		$this->db->where('type','addition');
		$this->db->where('option_id', $option_id);
        if ($warehouse_id) {
            $this->db->where('warehouse_id', $warehouse_id);
        }
        $q = $this->db->get('adjustment_items');
		if ($q->num_rows() > 0) {
            $data = $q->row();
            return $data->stock;
        }
		return 0;
	}
	public function getTongthuhoi_bienthe($option_id,$warehouse_id)
    {
        $this->db->select('SUM(COALESCE(quantity, 0)) as stock', False);
        $this->db->where('option_id', $option_id);
        if ($warehouse_id) {
            $this->db->where('warehouse_id', $warehouse_id);
        }
        $q = $this->db->get('return_items');
        if ($q->num_rows() > 0) {
            $data = $q->row();
            return $data->stock;
        }
        return 0;
    }
	public function getTongSoluongBanra_bienthe($option_id,$warehouse_id=NULL)
    {
        $this->db->select('SUM(COALESCE(unit_quantity, 0)) as stock', False);
        $this->db->where('option_id', $option_id);
        $this->db->where('sales.sale_status','completed');

        if ($warehouse_id) {
            $this->db->where('sales.warehouse_id', $warehouse_id);
        }
        $this->db->join('sales', 'sales.id=sale_items.sale_id', 'left');
        		
        $q = $this->db->get('sale_items');

        if ($q->num_rows() > 0) {
            $data = $q->row();
            return $data->stock;
        }
        return 0;
    }
	private function _tongchinhkhoam_bienthe($option_id, $warehouse_id = NULL) {
        $this->db->select('SUM(COALESCE(quantity, 0)) as stock', False);
		$this->db->where('type','subtraction');
		$this->db->where('option_id', $option_id);
        if ($warehouse_id) {
            $this->db->where('warehouse_id', $warehouse_id);
        }
        $q = $this->db->get('adjustment_items');
		if ($q->num_rows() > 0) {
            $data = $q->row();
            return $data->stock;
        }
		return 0;
	}
	private function _chuyenkhodi_bienthe($option_id, $warehouse_id = NULL) {
        $query="SELECT SUM(COALESCE(quantity, 0)) as stock FROM `scodeweb_transfers` a, scodeweb_purchase_items b WHERE a.id=b.transfer_id AND b.option_id=".$option_id;
		//$warehouse_id=NULL;
		if($warehouse_id){
			$query.=" and a.from_warehouse_id=".$warehouse_id;
		}
		$q=$this->db->query($query);
		if ($q->num_rows() > 0) {
            $data = $q->row();
            return $data->stock;
        }
		return 0;
	}
	public function tongNhapKhoTheoBienThe($option_id, $warehouse_id = NULL) {
        $this->db->select('SUM(COALESCE(unit_quantity, 0)) as stock', False);
        if ($warehouse_id) {
            $this->db->where('warehouse_id', $warehouse_id);
        }
		$this->db->where('option_id', $option_id);
		$this->db->where('thuhoi_id',0);
        $q = $this->db->get('purchase_items');
		$_nhapkho=0;
		if ($q->num_rows() > 0) {
            $data = $q->row();
            $_nhapkho=(float)$data->stock;
        }
		return $_nhapkho;
	}
	public function convert_number_to_words($number)
    {
        $number=round($number);
        
        $hyphen = ' ';
        $conjunction = '  ';
        $separator = ' ';
        $negative = 'âm ';
        $decimal = ' phẩy ';
        $dictionary = array(
            0 => 'Không',
            1 => 'Một',
            2 => 'Hai',
            3 => 'Ba',
            4 => 'Bốn',
            5 => 'Năm',
            6 => 'Sáu',
            7 => 'Bảy',
            8 => 'Tám',
            9 => 'Chín',
            10 => 'Mười',
            11 => 'Mười một',
            12 => 'Mười hai',
            13 => 'Mười ba',
            14 => 'Mười bốn',
            15 => 'Mười năm',
            16 => 'Mười sáu',
            17 => 'Mười bảy',
            18 => 'Mười tám',
            19 => 'Mười chín',
            20 => 'Hai mươi',
            30 => 'Ba mươi',
            40 => 'Bốn mươi',
            50 => 'Năm mươi',
            60 => 'Sáu mươi',
            70 => 'Bảy mươi',
            80 => 'Tám mươi',
            90 => 'Chín mươi',
            100 => 'trăm',
            1000 => 'ngàn',
            1000000 => 'triệu',
            1000000000 => 'tỷ',
            1000000000000 => 'nghìn tỷ',
            1000000000000000 => 'ngàn triệu triệu',
            1000000000000000000 => 'tỷ tỷ'
        );

        if (!is_numeric($number)) {
            return false;
        }

        if (($number >= 0 && (int)$number < 0) || (int)$number < 0 - PHP_INT_MAX) {
            trigger_error(
                'convert_number_to_words only accepts numbers between -' . PHP_INT_MAX . ' and ' . PHP_INT_MAX,
                E_USER_WARNING
            );
            return false;
        }

        if ($number < 0) {
            return $negative . $this->convert_number_to_words(abs($number));
        }

        $string = $fraction = null;

        if (strpos($number, '.') !== false) {
            list($number, $fraction) = explode('.', $number);
        }

        switch (true) {
            case $number < 21:
                $string = $dictionary[$number];
                break;
            case $number < 100:
                $tens = ((int)($number / 10)) * 10;
                $units = $number % 10;
                $string = $dictionary[$tens];
                if ($units) {
                    $string .= $hyphen . $dictionary[$units];
                }
                break;
            case $number < 1000:
                $hundreds = $number / 100;
                $remainder = $number % 100;
                $string = $dictionary[$hundreds] . ' ' . $dictionary[100];
                if ($remainder) {
                    $string .= $conjunction . $this->convert_number_to_words($remainder);
                }
                break;
            default:
                $baseUnit = pow(1000, floor(log($number, 1000)));
                $numBaseUnits = (int)($number / $baseUnit);
                $remainder = $number % $baseUnit;
                $string = $this->convert_number_to_words($numBaseUnits) . ' ' . $dictionary[$baseUnit];
                if ($remainder) {
                    $string .= $remainder < 100 ? $conjunction : $separator;
                    $string .= $this->convert_number_to_words($remainder);
                }
                break;
        }

        if (null !== $fraction && is_numeric($fraction)) {
            $string .= $decimal;
            $words = array();
            foreach (str_split((string)$fraction) as $number) {
                $words[] = $dictionary[$number];
            }
            $string .= implode(' ', $words);
        }

        return $string;
    }
	function phanloaiKhachHang(){
		$ct['0'] = lang('Default');
		
		return $ct;
	}
    function gianhaptrungbinh($product_id=0,$warehouse_id=0){
        $slbanbandau=$slbantong=$this->getTongSoluongBanra($product_id,$warehouse_id);
        $slnhap=$this->getTongSoluongNhapVao_QuyDoi($product_id,$warehouse_id);
        $slchuyenkho=$this->_chuyenkhodi($product_id,$warehouse_id);    
        $tonkho=0;
        $slton=$this->tonkhohientai($product_id,$warehouse_id);
        $giatrungbinh=0;
        $q = $this->db->select('unit_quantity as stock,unit_cost,subtotal,product_unit_id,id')->order_by('bandau_id', 'DESC')->order_by('id', 'ASC')->get_where('purchase_items', array('product_id' => $product_id,'purchase_id>' =>0));

        if ($q->num_rows() > 0) {
            
            $check=false;
            $giatri=0;
            $conlai_r=-1;
            foreach (($q->result()) as $row) {                
                // chuyen doi ve cung don vi ban
                $donvi=$this->getdonvitinh($product_id);   
                $slban=0;
                $gianhap=0;
               $donvi_ban=$this->getdonvitinhById($row->product_unit_id);                           

               $std=$slban_i=$row->stock;  
               $snhap=$gianhap_i=$row->unit_cost;
               if ($donvi_ban['id']==$donvi['base_unit']) {//don vi ban ra là bao cha của don vi ban mac dinh 1 cap
                    //quy doi sang don vi con
                    if ($donvi_ban['id']!=$donvi['id']) {//khac don vi ban
                                           
                        switch($donvi['operator']) {                    
                            case '*':           
                                $slban_i=(float)$std/(float)$donvi['operation_value'];
                                $gianhap_i=(float)$snhap*(float)$donvi['operation_value'];
                                break;          
                             case '/':                      
                                $slban_i=(float)$std*(float)$donvi['operation_value'];
                                $gianhap_i=(float)$snhap/(float)$donvi['operation_value'];
                                break;      
                             case '+':                      
                                $slban_i=(float)$std-(float)$donvi['operation_value'];
                                $gianhap_i=(float)$snhap+(float)$donvi['operation_value'];
                                break;
                            case '-':                   
                                $slban_i=(float)$std+(float)$donvi['operation_value'];
                                $gianhap_i=(float)$snhap-(float)$donvi['operation_value'];
                                break;              
                        } 
                    }  
               }else if ((int)$donvi_ban['base_unit']==0&&$donvi['base_unit']>0) {//don vi ban ra là TẤN cha của don vi ban mac dinh 2 cấp
                     //quy doi sang don vi con
                    $donvi_cap1=$this->getdonvitinhByParent($row->product_unit_id);  
                    
                    switch($donvi_cap1['operator']) {                    
                        case '*':           
                            $slban_i=(float)$std/(float)$donvi_cap1['operation_value'];
                            $gianhap_i=(float)$snhap*(float)$donvi['operation_value'];
                            break;          
                         case '/':                      
                            $slban_i=(float)$std*(float)$donvi_cap1['operation_value'];
                            $gianhap_i=(float)$snhap/(float)$donvi['operation_value'];
                            break;      
                         case '+':                      
                            $slban_i=(float)$std-(float)$donvi_cap1['operation_value'];
                            $gianhap_i=(float)$snhap+(float)$donvi['operation_value'];
                            break;
                        case '-':                   
                            $slban_i=(float)$std+(float)$donvi_cap1['operation_value'];
                            $gianhap_i=(float)$snhap-(float)$donvi['operation_value'];
                            break;              
                        } 

                    if ($donvi['id']!=$donvi_cap1['id']) {
                        switch($donvi['operator']) {                    
                            case '*':           
                                $slban_i=(float)$slban_i/(float)$donvi['operation_value'];
                                $gianhap_i=(float)$snhap*(float)$donvi['operation_value'];
                                break;          
                             case '/':                      
                                $slban_i=(float)$slban_i*(float)$donvi['operation_value'];
                                $gianhap_i=(float)$snhap/(float)$donvi['operation_value'];
                                break;      
                             case '+':                      
                                $slban_i=(float)$slban_i-(float)$donvi['operation_value'];
                                $gianhap_i=(float)$snhap+(float)$donvi['operation_value'];
                                break;
                            case '-':                   
                                $slban_i=(float)$slban_i+(float)$donvi['operation_value'];
                                $gianhap_i=(float)$snhap-(float)$donvi['operation_value'];
                                break;              
                        }
                    }  
                    
                }                
                //$slban+=$slban_i;     
                //$gianhap+=$gianhap_i;             
                // da quy doi sl nhap thanh cong
                if ($slton>0) {
                    $slbantong=$slbantong+$slchuyenkho;
                    if ($check) {
                        $giatri+=($slban_i*$gianhap_i);
                        $conlai_r= $slban_i;                   
                    }else{
                        if ($conlai_r!=-1) {
                            $giatri+=$conlai_r*$gianhap_i;
                        }else{
                            if ($slbantong>$slban_i) {
                                $conlai_r=$slbantong-$slban_i;
                            }else{
                                $conlai_r=$slban_i-$slbantong;
                                $giatri+=($conlai_r*$gianhap_i);
                                $check=true;
                            }
                        }                    
                    }
                }else{
                    $conlai_r=$slban_i;
                    $giatri+=($conlai_r*$gianhap_i);
                }
                
                if ($product_id==4) {
                   // echo "ID: ".$product_id." NHẬP ".$row->stock." QUY ĐỔI =>".$slban_i." GIA NHAP ".$gianhap_i." CON LAI = ".$conlai_r." Giá trị=".$giatri."<br/>"; 
                }                                             
                
            }
            if ($slton>0) {
                $gtb=$giatrungbinh=$giatri/$slton;    
            }else{
                $gtb=$giatrungbinh=$giatri/$slbantong;
            }
            
            //gia trung binh theo bao
            if ($donvi_ban['id']==$donvi['base_unit']) {//don vi ban ra là bao cha của don vi ban mac dinh 1 cap
                //quy doi sang don vi con
                if ($donvi_ban['id']!=$donvi['id']) {//khac don vi ban
                                       
                    switch($donvi['operator']) {                    
                        case '*':           
                            $giatrungbinh=(float)$gtb/(float)$donvi['operation_value'];
                            break;          
                         case '/':                      
                            $giatrungbinh=(float)$gtb*(float)$donvi['operation_value'];
                            break;      
                         case '+':                      
                            $giatrungbinh=(float)$gtb-(float)$donvi['operation_value'];
                            break;
                        case '-':                   
                            $giatrungbinh=(float)$gtb+(float)$donvi['operation_value'];
                            break;              
                    } 
                }  
           }else if ((int)$donvi_ban['base_unit']==0&&$donvi['base_unit']>0) {//don vi ban ra là TẤN cha của don vi ban mac dinh 2 cấp
                 //quy doi sang don vi con
                $donvi_cap1=$this->getdonvitinhByParent($row->product_unit_id);  
                
                switch($donvi_cap1['operator']) {                    
                    case '*':           
                            $giatrungbinh=(float)$gtb/(float)$donvi['operation_value'];
                            break;          
                         case '/':                      
                            $giatrungbinh=(float)$gtb*(float)$donvi['operation_value'];
                            break;      
                         case '+':                      
                            $giatrungbinh=(float)$gtb-(float)$donvi['operation_value'];
                            break;
                        case '-':                   
                            $giatrungbinh=(float)$gtb+(float)$donvi['operation_value'];
                            break;                 
                    } 

                if ($donvi['id']!=$donvi_cap1['id']) {
                    switch($donvi['operator']) {                    
                        case '*':           
                            $giatrungbinh=(float)$gtb/(float)$donvi['operation_value'];
                            break;          
                         case '/':                      
                            $giatrungbinh=(float)$gtb*(float)$donvi['operation_value'];
                            break;      
                         case '+':                      
                            $giatrungbinh=(float)$gtb-(float)$donvi['operation_value'];
                            break;
                        case '-':                   
                            $giatrungbinh=(float)$gtb+(float)$donvi['operation_value'];
                            break;                 
                    }
                }  
                
            }
        }
       
        if ($product_id==4) {
             //echo "ID: ".$product_id." NHẬP ".$slnhap." CHUYỂN KHO ".$slchuyenkho." SL BÁN: ".$slbantong." => SL tồn ".$slton." Giá TB =".$giatrungbinh."<br/>";
        }
        return $giatrungbinh;
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
     public function getGiaVonAllItemByWharehouse($warehouse_id=null, $start = NULL,$end=null)
    {
        $this->db->select('product_id,scodeweb_sale_items.warehouse_id as warehouse_id')
            ->join('products', 'products.id=sale_items.product_id', 'left')
            ->group_by('sale_items.product_id')
            ->order_by('product_id', 'asc');
        if ($warehouse_id) {
            $this->db->where('scodeweb_sale_items.warehouse_id', $warehouse_id);
        } 
        if ($start&&$end) {
            $this->db->join('sales', 'sales.id=sale_items.sale_id');
            $this->db->where("scodeweb_sales.date BETWEEN '".date('Y-m-d', strtotime($start))." 00:00:00' AND '".date('Y-m-d', strtotime($end))." 23:59:59'",null,false);
        }

        $q = $this->db->get('sale_items');

        $giavon=0;
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                //tien hanh lay gia von ban ra cua san pham
                $giavon_i=$this->giavonhangban($row->product_id,$row->warehouse_id,$start,$end);
                //echo "warehouse_id=>".$row->warehouse_id." ID:".$row->product_id." = ".$giavon_i;
                $giavon+=$giavon_i;
            }
            
        }
        return $giavon;
    }
    function giatritonkho($product_id=0,$warehouse_id=0,$start=null,$end=null){
        $slbanbandau=$slbantong=$this->getTongSoluongBanra($product_id,$warehouse_id,$start,$end);
        $slnhap=$this->getTongSoluongNhapVao_QuyDoi($product_id,$warehouse_id,$start,$end);
        $slchuyenkho=$this->_chuyenkhodi($product_id,$warehouse_id,$start,$end);    
        $slbantong+=$slchuyenkho;       
        
        if ($start&&$end) {
             $this->db->select('(CASE WHEN COALESCE(unit_quantity, 0)=0 THEN COALESCE(quantity, 0) ELSE COALESCE(unit_quantity, 0) END) as stock,unit_cost,subtotal,product_unit_id,id')->order_by('bandau_id', 'DESC')->order_by('id', 'ASC')->where(array('product_id' => $product_id,'thuhoi_id' =>0,'warehouse_id' =>$warehouse_id));
              
              $this->db->where("date BETWEEN '".date('Y-m-d', strtotime($start))." 00:00:00' AND '".date('Y-m-d', strtotime($end))." 23:59:59'",null,false);
              $q = $this->db->get_where('purchase_items');
        }else{
            $q = $this->db->select('(CASE WHEN COALESCE(unit_quantity, 0)=0 THEN COALESCE(quantity, 0) ELSE COALESCE(unit_quantity, 0) END) as stock,unit_cost,subtotal,product_unit_id,id')->order_by('bandau_id', 'DESC')->order_by('id', 'ASC')->get_where('purchase_items', array('product_id' => $product_id,'thuhoi_id' =>0,'warehouse_id' =>$warehouse_id));
        }
        $giatri=0;
        if ($q->num_rows() > 0) {
                       
            foreach (($q->result()) as $row) {   
                        
                // chuyen doi ve cung don vi ban
                $donvi=$this->getdonvitinh($product_id);               
                $donvi_ban=$this->getdonvitinhById($row->product_unit_id);                           
                
                $std=$slban_i=$row->stock;  

                $snhap=$gianhap_i=$row->unit_cost;
               if ($donvi_ban['id']==$donvi['base_unit']) {//don vi ban ra là bao cha của don vi ban mac dinh 1 cap
                    //quy doi sang don vi con
                    if ($donvi_ban['id']!=$donvi['id']) {//khac don vi ban
                                           
                        switch($donvi['operator']) {                    
                            case '*':           
                                $slban_i=(float)$std/(float)$donvi['operation_value'];
                                $gianhap_i=(float)$snhap*(float)$donvi['operation_value'];
                                break;          
                             case '/':                      
                                $slban_i=(float)$std*(float)$donvi['operation_value'];
                                $gianhap_i=(float)$snhap/(float)$donvi['operation_value'];
                                break;      
                             case '+':                      
                                $slban_i=(float)$std-(float)$donvi['operation_value'];
                                $gianhap_i=(float)$snhap+(float)$donvi['operation_value'];
                                break;
                            case '-':                   
                                $slban_i=(float)$std+(float)$donvi['operation_value'];
                                $gianhap_i=(float)$snhap-(float)$donvi['operation_value'];
                                break;              
                        } 
                    }  
               }else if ((int)$donvi_ban['base_unit']==0&&$donvi['base_unit']>0) {//don vi ban ra là TẤN cha của don vi ban mac dinh 2 cấp
                     //quy doi sang don vi con
                    $donvi_cap1=$this->getdonvitinhByParent($row->product_unit_id);  
                    
                    switch($donvi_cap1['operator']) {                    
                        case '*':           
                            $slban_i=(float)$std/(float)$donvi_cap1['operation_value'];
                            $gianhap_i=(float)$snhap*(float)$donvi['operation_value'];
                            break;          
                         case '/':                      
                            $slban_i=(float)$std*(float)$donvi_cap1['operation_value'];
                            $gianhap_i=(float)$snhap/(float)$donvi['operation_value'];
                            break;      
                         case '+':                      
                            $slban_i=(float)$std-(float)$donvi_cap1['operation_value'];
                            $gianhap_i=(float)$snhap+(float)$donvi['operation_value'];
                            break;
                        case '-':                   
                            $slban_i=(float)$std+(float)$donvi_cap1['operation_value'];
                            $gianhap_i=(float)$snhap-(float)$donvi['operation_value'];
                            break;              
                        } 

                    if ($donvi['id']!=$donvi_cap1['id']) {
                        switch($donvi['operator']) {                    
                            case '*':           
                                $slban_i=(float)$slban_i/(float)$donvi['operation_value'];
                                $gianhap_i=(float)$snhap*(float)$donvi['operation_value'];
                                break;          
                             case '/':                      
                                $slban_i=(float)$slban_i*(float)$donvi['operation_value'];
                                $gianhap_i=(float)$snhap/(float)$donvi['operation_value'];
                                break;      
                             case '+':                      
                                $slban_i=(float)$slban_i-(float)$donvi['operation_value'];
                                $gianhap_i=(float)$snhap+(float)$donvi['operation_value'];
                                break;
                            case '-':                   
                                $slban_i=(float)$slban_i+(float)$donvi['operation_value'];
                                $gianhap_i=(float)$snhap-(float)$donvi['operation_value'];
                                break;              
                        }
                    }  
                    
                }                
                //$slban+=$slban_i;     
                //$gianhap+=$gianhap_i;             
                // da quy doi sl nhap thanh cong
                if ($slban_i>=$slbantong) {
                    if ($slchuyenkho>0) {
                       // $slbantong=$slbantong-$slchuyenkho;
                    }
                    $slconlai=$slban_i-$slbantong;   
                    $giatri+=$slconlai*$gianhap_i; 
                    $slbantong=0; 
                }else{
                    $slconlai=$slban_i-$slbantong;                                  
                    if ($slconlai>0) {
                        $giatri+=$slban_i*$gianhap_i;    
                    }
                    if ($slconlai<0) {
                        $slconlai=abs($slconlai);
                    }
                    $slbantong=$slconlai; 
                }                    
               
                
                if ($product_id==3) {
                   // echo "product_unit_id: ".$row->product_unit_id." NHẬP ".$row->stock." SL NHẬP QUY ĐỔI =>".$slban_i." GIA NHAP ".$gianhap_i." CON LAI = ".$slconlai." Giá trị=".$giatri."<br/>";  
                }                                             
                
            }
            
            
        }
       
        if ($product_id==4) {
             //echo "ID: ".$product_id." NHẬP ".$slnhap." CHUYỂN KHO ".$slchuyenkho." SL BÁN: ".$slbanbandau." => Giá trị tồn kho =".$giatri."<br/>";
        }
        return $giatri; 
    }
    function giatrinhapkhobandau($product_id=0,$warehouse_id=0,$start=null,$end=null){
                   
        if ($start&&$end) {            
            $this->db->select('quantity as stock,unit_cost,subtotal,product_unit_id,id')->order_by('bandau_id', 'DESC')->order_by('id', 'ASC')->where(array('product_id' => $product_id,'bandau_id>' =>0,'thuhoi_id' =>0,'warehouse_id' =>$warehouse_id));
            $this->db->where("date BETWEEN '".date('Y-m-d', strtotime($start))." 00:00:00' AND '".date('Y-m-d', strtotime($end))." 23:59:59'",null,false);
             $q = $this->db->get('purchase_items');
        }else{
            $q = $this->db->select('quantity as stock,unit_cost,subtotal,product_unit_id,id')->order_by('bandau_id', 'DESC')->order_by('id', 'ASC')->get_where('purchase_items', array('product_id' => $product_id,'bandau_id>' =>0,'thuhoi_id' =>0,'warehouse_id' =>$warehouse_id));
        }
        $giavon=0;
        if ($q->num_rows() > 0) {                       
            foreach (($q->result()) as $row) {        
                
                // chuyen doi ve cung don vi ban
                $donvi=$this->getdonvitinh($product_id);               
                $donvi_ban=$this->getdonvitinhById($row->product_unit_id);                           
                $std=$slban_i=$row->stock;  
                $snhap=$gianhap_i=$row->unit_cost;
               if ($donvi_ban['id']==$donvi['base_unit']) {//don vi ban ra là bao cha của don vi ban mac dinh 1 cap
                    //quy doi sang don vi con
                    if ($donvi_ban['id']!=$donvi['id']) {//khac don vi ban
                                           
                        switch($donvi['operator']) {                    
                            case '*':           
                                $slban_i=(float)$std/(float)$donvi['operation_value'];
                                $gianhap_i=(float)$snhap*(float)$donvi['operation_value'];
                                break;          
                             case '/':                      
                                $slban_i=(float)$std*(float)$donvi['operation_value'];
                                $gianhap_i=(float)$snhap/(float)$donvi['operation_value'];
                                break;      
                             case '+':                      
                                $slban_i=(float)$std-(float)$donvi['operation_value'];
                                $gianhap_i=(float)$snhap+(float)$donvi['operation_value'];
                                break;
                            case '-':                   
                                $slban_i=(float)$std+(float)$donvi['operation_value'];
                                $gianhap_i=(float)$snhap-(float)$donvi['operation_value'];
                                break;              
                        } 
                    }  
               }else if ((int)$donvi_ban['base_unit']==0&&$donvi['base_unit']>0) {//don vi ban ra là TẤN cha của don vi ban mac dinh 2 cấp
                     //quy doi sang don vi con
                    $donvi_cap1=$this->getdonvitinhByParent($row->product_unit_id);  
                    
                    switch($donvi_cap1['operator']) {                    
                        case '*':           
                            $slban_i=(float)$std/(float)$donvi_cap1['operation_value'];
                            $gianhap_i=(float)$snhap*(float)$donvi['operation_value'];
                            break;          
                         case '/':                      
                            $slban_i=(float)$std*(float)$donvi_cap1['operation_value'];
                            $gianhap_i=(float)$snhap/(float)$donvi['operation_value'];
                            break;      
                         case '+':                      
                            $slban_i=(float)$std-(float)$donvi_cap1['operation_value'];
                            $gianhap_i=(float)$snhap+(float)$donvi['operation_value'];
                            break;
                        case '-':                   
                            $slban_i=(float)$std+(float)$donvi_cap1['operation_value'];
                            $gianhap_i=(float)$snhap-(float)$donvi['operation_value'];
                            break;              
                        } 

                    if ($donvi['id']!=$donvi_cap1['id']) {
                        switch($donvi['operator']) {                    
                            case '*':           
                                $slban_i=(float)$slban_i/(float)$donvi['operation_value'];
                                $gianhap_i=(float)$snhap*(float)$donvi['operation_value'];
                                break;          
                             case '/':                      
                                $slban_i=(float)$slban_i*(float)$donvi['operation_value'];
                                $gianhap_i=(float)$snhap/(float)$donvi['operation_value'];
                                break;      
                             case '+':                      
                                $slban_i=(float)$slban_i-(float)$donvi['operation_value'];
                                $gianhap_i=(float)$snhap+(float)$donvi['operation_value'];
                                break;
                            case '-':                   
                                $slban_i=(float)$slban_i+(float)$donvi['operation_value'];
                                $gianhap_i=(float)$snhap-(float)$donvi['operation_value'];
                                break;              
                        }
                    }  
                    
                }                
                $giavon+=$slban_i*$gianhap_i;                                                                 
            }           
            
        }       
       
        return $giavon; 
    } 
     
     function giavonhangban($product_id=0,$warehouse_id=0,$start=null,$end=null){
        $slbanbandau=$slbantong=$this->getTongSoluongBanra($product_id,$warehouse_id,$start,$end);
        $slnhap=$this->getTongSoluongNhapVao_QuyDoi($product_id,$warehouse_id,$start,$end);
        $slchuyenkho=0;//$this->_chuyenkhodi($product_id,$warehouse_id);    
               
        $q = $this->db->select('unit_quantity as stock,unit_cost,subtotal,product_unit_id,id')->order_by('bandau_id', 'DESC')->order_by('id', 'ASC')->get_where('purchase_items', array('product_id' => $product_id,'thuhoi_id' =>0,'warehouse_id' =>$warehouse_id));

        if ($start&&$end) {
            //$this->db->where("date BETWEEN '".date('Y-m-d', strtotime($start))." 00:00:00' AND '".date('Y-m-d', strtotime($end))." 23:59:59'",null,false);
        }
        $giavon=0;
        $giavonck=0;
        if ($q->num_rows() > 0) {
                       
            foreach (($q->result()) as $row) {                
                // chuyen doi ve cung don vi ban
                $donvi=$this->getdonvitinh($product_id);               
                $donvi_ban=$this->getdonvitinhById($row->product_unit_id);                           
                $std=$slban_i=$row->stock;  
                $snhap=$gianhap_i=$row->unit_cost;
               if ($donvi_ban['id']==$donvi['base_unit']) {//don vi ban ra là bao cha của don vi ban mac dinh 1 cap
                    //quy doi sang don vi con
                    if ($donvi_ban['id']!=$donvi['id']) {//khac don vi ban
                                           
                        switch($donvi['operator']) {                    
                            case '*':           
                                $slban_i=(float)$std/(float)$donvi['operation_value'];
                                $gianhap_i=(float)$snhap*(float)$donvi['operation_value'];
                                break;          
                             case '/':                      
                                $slban_i=(float)$std*(float)$donvi['operation_value'];
                                $gianhap_i=(float)$snhap/(float)$donvi['operation_value'];
                                break;      
                             case '+':                      
                                $slban_i=(float)$std-(float)$donvi['operation_value'];
                                $gianhap_i=(float)$snhap+(float)$donvi['operation_value'];
                                break;
                            case '-':                   
                                $slban_i=(float)$std+(float)$donvi['operation_value'];
                                $gianhap_i=(float)$snhap-(float)$donvi['operation_value'];
                                break;              
                        } 
                    }  
               }else if ((int)$donvi_ban['base_unit']==0&&$donvi['base_unit']>0) {//don vi ban ra là TẤN cha của don vi ban mac dinh 2 cấp
                     //quy doi sang don vi con
                    $donvi_cap1=$this->getdonvitinhByParent($row->product_unit_id);  
                    
                    switch($donvi_cap1['operator']) {                    
                        case '*':           
                            $slban_i=(float)$std/(float)$donvi_cap1['operation_value'];
                            $gianhap_i=(float)$snhap*(float)$donvi['operation_value'];
                            break;          
                         case '/':                      
                            $slban_i=(float)$std*(float)$donvi_cap1['operation_value'];
                            $gianhap_i=(float)$snhap/(float)$donvi['operation_value'];
                            break;      
                         case '+':                      
                            $slban_i=(float)$std-(float)$donvi_cap1['operation_value'];
                            $gianhap_i=(float)$snhap+(float)$donvi['operation_value'];
                            break;
                        case '-':                   
                            $slban_i=(float)$std+(float)$donvi_cap1['operation_value'];
                            $gianhap_i=(float)$snhap-(float)$donvi['operation_value'];
                            break;              
                        } 

                    if ($donvi['id']!=$donvi_cap1['id']) {
                        switch($donvi['operator']) {                    
                            case '*':           
                                $slban_i=(float)$slban_i/(float)$donvi['operation_value'];
                                $gianhap_i=(float)$snhap*(float)$donvi['operation_value'];
                                break;          
                             case '/':                      
                                $slban_i=(float)$slban_i*(float)$donvi['operation_value'];
                                $gianhap_i=(float)$snhap/(float)$donvi['operation_value'];
                                break;      
                             case '+':                      
                                $slban_i=(float)$slban_i-(float)$donvi['operation_value'];
                                $gianhap_i=(float)$snhap+(float)$donvi['operation_value'];
                                break;
                            case '-':                   
                                $slban_i=(float)$slban_i+(float)$donvi['operation_value'];
                                $gianhap_i=(float)$snhap-(float)$donvi['operation_value'];
                                break;              
                        }
                    }  
                    
                }                
                //$slban+=$slban_i;     
                //$gianhap+=$gianhap_i;             
                // da quy doi sl nhap thanh cong
                $slbantong=$slbantong+$slchuyenkho;
                if ($slban_i>=$slbantong) {
                    if ($slchuyenkho>0) {
                        $slbantong=$slbantong-$slchuyenkho;

                    }
                    $giavon+=$slbantong*$gianhap_i; 
                    $slbantong=0; 
                    $slconlai=$slbantong;

                }else{
                    $slconlai=$slbantong-$slban_i;                           
                    if ($slconlai>0) {
                        $giavon+=$slban_i*$gianhap_i;       
                    }                  
                    
                    $slbantong=$slconlai; 
                }      

                if ($product_id==1) {
                   // echo "product_unit_id: ".$row->product_unit_id." NHẬP ".$row->stock." SL NHẬP QUY ĐỔI =>".$slban_i." GIA NHAP ".$gianhap_i." SL TINH VON = ".$slban_i." Giá VON=".$giavon."<br/>";  
                }                                                             
            }           
            
        }       
        if ($product_id==1) {
             //echo "ID: ".$product_id." NHẬP ".$slnhap." CHUYỂN KHO ".$slchuyenkho." SL BÁN: ".$slbanbandau." => Giá VỐN =".$giavon."<br/>";
        }
        return $giavon; 
    } 
     function getDanhSachGiaVonTheoSanPham($sale_id=0){
        if ($sale_items = $this->getAllInvoiceItems($sale_id)) {
            foreach ($sale_items as $item) {
                //lay gia nhap trung binh
                $product_id=$item->product_id;

                $giavon=$this->giavonhangbantheohoadon($product_id,$item->warehouse_id,$item->unit_quantity,$item->product_unit_id);

            }
        }
    }
    public function getTongSoluongBanraTruocDo($product_id=0,$warehouse_id=NULL,$id_order=0)
    {
        
        $this->db->select('product_unit_id,SUM(COALESCE(unit_quantity, 0)) as stock', False);
        $this->db->where('product_id', $product_id);
        
        if ($warehouse_id) {
            $this->db->where('sale_items.warehouse_id', $warehouse_id);
        }
        $this->db->where('sale_items.id<', $id_order);
        $this->db->order_by('sale_items.id', 'DESC');
        $this->db->group_by('sale_items.product_unit_id');
        //tinh tong don vi ban ra quy doi ve don vi ban lhson code 20/5/2020
        
        $donvi=$this->getdonvitinh($product_id);   
        $slban=0;
        $this->db->where('sales.sale_status','completed');
        $this->db->join('sales', 'sales.id=sale_items.sale_id', 'left');

        $q = $this->db->select('sale_items.*')->get('sale_items');

        if ($q->num_rows() > 0) {
            $data = $q->result();
            foreach ($data as $row) {
               $donvi_ban=$this->getdonvitinhById($row->product_unit_id);                           

               $std=$slban_i=$row->stock;  

               if ($donvi_ban['id']==$donvi['base_unit']) {//don vi ban ra là bao cha của don vi ban mac dinh 1 cap
                    //quy doi sang don vi con
                    if ($donvi_ban['id']!=$donvi['id']) {//khac don vi ban
                                           
                        switch($donvi['operator']) {                    
                            case '*':           
                                $slban_i=(float)$slban_i/(float)$donvi['operation_value'];
                                break;          
                             case '/':                      
                                $slban_i=(float)$slban_i*(float)$donvi['operation_value'];
                                break;      
                             case '+':                      
                                $slban_i=(float)$slban_i-(float)$donvi['operation_value'];
                                break;
                            case '-':                   
                                $slban_i=(float)$slban_i+(float)$donvi['operation_value'];
                                break;              
                        } 
                    }
                        
               }else if ((int)$donvi_ban['base_unit']==0&&$donvi['base_unit']>0) {//don vi ban ra là TẤN cha của don vi ban mac dinh 2 cấp
                     //quy doi sang don vi con
                    $donvi_cap1=$this->getdonvitinhByParent($row->product_unit_id);  
                    
                    switch($donvi_cap1['operator']) {                    
                        case '*':           
                            $slban_i=(float)$slban_i/(float)$donvi_cap1['operation_value'];
                            break;          
                         case '/':                      
                            $slban_i=(float)$slban_i*(float)$donvi_cap1['operation_value'];
                            break;      
                         case '+':                      
                            $slban_i=(float)$slban_i-(float)$donvi_cap1['operation_value'];
                            break;
                        case '-':                   
                            $slban_i=(float)$slban_i+(float)$donvi_cap1['operation_value'];
                            break;              
                        } 

                    if ($donvi['id']!=$donvi_cap1['id']) {
                        switch($donvi['operator']) {                    
                            case '*':           
                                $slban_i/=(float)$std/(float)$donvi['operation_value'];
                                break;          
                             case '/':                      
                                $slban_i*=(float)$std*(float)$donvi['operation_value'];
                                break;      
                             case '+':                      
                                $slban_i-=(float)$std-(float)$donvi['operation_value'];
                                break;
                            case '-':                   
                                $slban_i+=(float)$std+(float)$donvi['operation_value'];
                                break;              
                        }
                    }  
                       
                }                
                $slban+=$slban_i;                   
            }
           
            return $slban;
        }
        return 0;
    }
     function giavonhangbantheohoadontruocdo($product_id=0,$warehouse_id=0,$slbantong=0,$donviban_id=0){

        $slbanbandau=$slbantong;

        $slnhap=$this->getTongSoluongNhapVao_QuyDoi($product_id,$warehouse_id);
        $slchuyenkho=0;//$this->_chuyenkhodi($product_id,$warehouse_id);    
               
        $q = $this->db->select('unit_quantity as stock,unit_cost,subtotal,product_unit_id,id')->order_by('bandau_id', 'DESC')->order_by('id', 'ASC')->get_where('purchase_items', array('product_id' => $product_id,'thuhoi_id' =>0));
        $giavon=0;
        $tongsldaban=0;
        if ($q->num_rows() > 0) {
                       
            foreach (($q->result()) as $row) {                
                // chuyen doi ve cung don vi ban
                $donvi=$this->getdonvitinh($product_id);               
                $donvi_ban=$this->getdonvitinhById($row->product_unit_id);                           
                $std=$slban_i=$row->stock;  
                if ($q->num_rows()==1) {
                    $slban_i=1;    
                }
                $snhap=$gianhap_i=$row->unit_cost;
               if ($donvi_ban['id']==$donvi['base_unit']) {//don vi ban ra là bao cha của don vi ban mac dinh 1 cap
                    //quy doi sang don vi con
                    if ($donvi_ban['id']!=$donvi['id']) {//khac don vi ban
                                           
                        switch($donvi['operator']) {                    
                            case '*':           
                                $slban_i=(float)$std/(float)$donvi['operation_value'];
                                $gianhap_i=(float)$snhap*(float)$donvi['operation_value'];
                                break;          
                             case '/':                      
                                $slban_i=(float)$std*(float)$donvi['operation_value'];
                                $gianhap_i=(float)$snhap/(float)$donvi['operation_value'];
                                break;      
                             case '+':                      
                                $slban_i=(float)$std-(float)$donvi['operation_value'];
                                $gianhap_i=(float)$snhap+(float)$donvi['operation_value'];
                                break;
                            case '-':                   
                                $slban_i=(float)$std+(float)$donvi['operation_value'];
                                $gianhap_i=(float)$snhap-(float)$donvi['operation_value'];
                                break;              
                        } 
                    }  
               }else if ((int)$donvi_ban['base_unit']==0&&$donvi['base_unit']>0) {//don vi ban ra là TẤN cha của don vi ban mac dinh 2 cấp
                     //quy doi sang don vi con
                    $donvi_cap1=$this->getdonvitinhByParent($row->product_unit_id);  
                    
                    switch($donvi_cap1['operator']) {                    
                        case '*':           
                            $slban_i=(float)$std/(float)$donvi_cap1['operation_value'];
                            $gianhap_i=(float)$snhap*(float)$donvi['operation_value'];
                            break;          
                         case '/':                      
                            $slban_i=(float)$std*(float)$donvi_cap1['operation_value'];
                            $gianhap_i=(float)$snhap/(float)$donvi['operation_value'];
                            break;      
                         case '+':                      
                            $slban_i=(float)$std-(float)$donvi_cap1['operation_value'];
                            $gianhap_i=(float)$snhap+(float)$donvi['operation_value'];
                            break;
                        case '-':                   
                            $slban_i=(float)$std+(float)$donvi_cap1['operation_value'];
                            $gianhap_i=(float)$snhap-(float)$donvi['operation_value'];
                            break;              
                        } 

                    if ($donvi['id']!=$donvi_cap1['id']) {
                        switch($donvi['operator']) {                    
                            case '*':           
                                $slban_i=(float)$slban_i/(float)$donvi['operation_value'];
                                $gianhap_i=(float)$snhap*(float)$donvi['operation_value'];
                                break;          
                             case '/':                      
                                $slban_i=(float)$slban_i*(float)$donvi['operation_value'];
                                $gianhap_i=(float)$snhap/(float)$donvi['operation_value'];
                                break;      
                             case '+':                      
                                $slban_i=(float)$slban_i-(float)$donvi['operation_value'];
                                $gianhap_i=(float)$snhap+(float)$donvi['operation_value'];
                                break;
                            case '-':                   
                                $slban_i=(float)$slban_i+(float)$donvi['operation_value'];
                                $gianhap_i=(float)$snhap-(float)$donvi['operation_value'];
                                break;              
                        }
                    }  
                    
                }                
               
                 $slbantong=$slbantong+$slchuyenkho;
                if ($slban_i>=$slbantong) {
 
                    $giavon+=$slbantong*$gianhap_i; 
                    $slbantong=0; 
                    $slconlai=$slbantong;
                }else{
                    $slconlai=$slbantong-$slban_i;                           
                    if ($slconlai>0) {
                        $giavon+=$slban_i*$gianhap_i;       
                    }else{

                    }
                    
                    
                    $slbantong=$slconlai; 
                }  
              
                if ($product_id==1) {
                    //echo "product_unit_id: ".$row->product_unit_id." NHẬP ".$row->stock." SL NHẬP QUY ĐỔI =>".$slban_i." GIA NHAP ".$gianhap_i." SL TINH VON = ".$slban_i." Giá VON=".$giavon."<br/>";  
                }                                                             
            }           
            
        }       
        if ($product_id==1) {
             //echo "ID: ".$product_id." NHẬP ".$slnhap." CHUYỂN KHO ".$slchuyenkho." SL BÁN: ".$slbanbandau." => Giá vốn trước đó =".$giavon."<br/>";
        }
        return $giavon; 
    }
     function giavonhangbantheohoadon($product_id=0,$warehouse_id=0,$slbantong=0,$donviban_id=0,$id_order=0){
        $slbantongtruocdo=$this->getTongSoluongBanraTruocDo($product_id,$warehouse_id,$id_order);
        $giavontruocdo=(float)$this->giavonhangbantheohoadontruocdo($product_id,$warehouse_id,$slbantongtruocdo,$donviban_id);
        //quy doi slbantong ve don vi ban mac dinh
        $std1=$slban_i1=$slbantong; 

        $donvi1=$this->getdonvitinh($product_id);               
        $donvi_ban1=$this->getdonvitinhById($donviban_id);   
         if ($donvi_ban1['id']==$donvi1['base_unit']) {//don vi ban ra là bao cha của don vi ban mac dinh 1 cap
            //quy doi sang don vi con
            if ($donvi_ban1['id']!=$donvi1['id']) {//khac don vi ban
                                   
                switch($donvi1['operator']) {                    
                    case '*':           
                        $slban_i1=(float)$std1/(float)$donvi1['operation_value'];
                        break;          
                     case '/':                      
                        $slban_i1=(float)$std1*(float)$donvi1['operation_value'];
                        break;      
                     case '+':                      
                        $slban_i1=(float)$std1-(float)$donvi1['operation_value'];
                        break;
                    case '-':                   
                        $slban_i1=(float)$std1+(float)$donvi1['operation_value'];
                        break;              
                } 
            }  
       }else if ((int)$donvi_ban1['base_unit']==0&&$donvi1['base_unit']>0) {//don vi ban ra là TẤN cha của don vi ban mac dinh 2 cấp
             //quy doi sang don vi con
            $donvi_cap1=$this->getdonvitinhByParent($donviban_id);  
            
            switch($donvi_cap1['operator']) {                    
                case '*':           
                    $slban_i1=(float)$std1/(float)$donvi_cap1['operation_value'];
                    break;          
                 case '/':                      
                    $slban_i1=(float)$std1*(float)$donvi_cap1['operation_value'];
                    break;      
                 case '+':                      
                    $slban_i1=(float)$std1-(float)$donvi_cap1['operation_value'];
                    break;
                case '-':                   
                    $slban_i1=(float)$std1+(float)$donvi_cap1['operation_value'];
                    break;              
                } 

            if ($donvi1['id']!=$donvi_cap1['id']) {
                switch($donvi['operator']) {                    
                    case '*':           
                        $slban_i1=(float)$slban_i1/(float)$donvi1['operation_value'];
                        break;          
                     case '/':                      
                        $slban_i1=(float)$slban_i1*(float)$donvi1['operation_value'];
                        break;      
                     case '+':                      
                        $slban_i1=(float)$slban_i1-(float)$donvi1['operation_value'];
                        break;
                    case '-':                   
                        $slban_i1=(float)$slban_i1+(float)$donvi1['operation_value'];
                        break;              
                }
            }  
            
        } 

        $slbantong=$slban_i1+(float)$slbantongtruocdo;
        $slbanbandau=$slbantong;

        $slnhap=$this->getTongSoluongNhapVao_QuyDoi($product_id,$warehouse_id);
        $slchuyenkho=0;//$this->_chuyenkhodi($product_id,$warehouse_id);
             
        $q = $this->db->select('unit_quantity as stock,unit_cost,subtotal,product_unit_id,id,date')->order_by('bandau_id', 'DESC')->order_by('id', 'ASC')->get_where('purchase_items', array('product_id' => $product_id,'thuhoi_id' =>0,'transfer_id' =>NULL));
        $giavon=0;
        $tongsldaban=0;
        $sodongnhap=$q->num_rows();
        if ( $sodongnhap> 0) {
                       
            foreach (($q->result()) as $row) {                
                // chuyen doi ve cung don vi ban
                $donvi=$this->getdonvitinh($product_id);               
                $donvi_ban=$this->getdonvitinhById($row->product_unit_id);                           
                $std=$slban_i=$row->stock;  
                if ($sodongnhap==1) {
                    $slban_i=1;    
                }
                $snhap=$gianhap_i=$row->unit_cost;
               if ($donvi_ban['id']==$donvi['base_unit']) {//don vi ban ra là bao cha của don vi ban mac dinh 1 cap
                    //quy doi sang don vi con
                    if ($donvi_ban['id']!=$donvi['id']) {//khac don vi ban
                                           
                        switch($donvi['operator']) {                    
                            case '*':           
                                $slban_i=(float)$std/(float)$donvi['operation_value'];
                                $gianhap_i=(float)$snhap*(float)$donvi['operation_value'];
                                break;          
                             case '/':                      
                                $slban_i=(float)$std*(float)$donvi['operation_value'];
                                $gianhap_i=(float)$snhap/(float)$donvi['operation_value'];
                                break;      
                             case '+':                      
                                $slban_i=(float)$std-(float)$donvi['operation_value'];
                                $gianhap_i=(float)$snhap+(float)$donvi['operation_value'];
                                break;
                            case '-':                   
                                $slban_i=(float)$std+(float)$donvi['operation_value'];
                                $gianhap_i=(float)$snhap-(float)$donvi['operation_value'];
                                break;              
                        } 
                    }  
               }else if ((int)$donvi_ban['base_unit']==0&&$donvi['base_unit']>0) {//don vi ban ra là TẤN cha của don vi ban mac dinh 2 cấp
                     //quy doi sang don vi con
                    $donvi_cap1=$this->getdonvitinhByParent($row->product_unit_id);  
                    
                    switch($donvi_cap1['operator']) {                    
                        case '*':           
                            $slban_i=(float)$std/(float)$donvi_cap1['operation_value'];
                            $gianhap_i=(float)$snhap*(float)$donvi['operation_value'];
                            break;          
                         case '/':                      
                            $slban_i=(float)$std*(float)$donvi_cap1['operation_value'];
                            $gianhap_i=(float)$snhap/(float)$donvi['operation_value'];
                            break;      
                         case '+':                      
                            $slban_i=(float)$std-(float)$donvi_cap1['operation_value'];
                            $gianhap_i=(float)$snhap+(float)$donvi['operation_value'];
                            break;
                        case '-':                   
                            $slban_i=(float)$std+(float)$donvi_cap1['operation_value'];
                            $gianhap_i=(float)$snhap-(float)$donvi['operation_value'];
                            break;              
                        } 

                    if ($donvi['id']!=$donvi_cap1['id']) {
                        switch($donvi['operator']) {                    
                            case '*':           
                                $slban_i=(float)$slban_i/(float)$donvi['operation_value'];
                                $gianhap_i=(float)$snhap*(float)$donvi['operation_value'];
                                break;          
                             case '/':                      
                                $slban_i=(float)$slban_i*(float)$donvi['operation_value'];
                                $gianhap_i=(float)$snhap/(float)$donvi['operation_value'];
                                break;      
                             case '+':                      
                                $slban_i=(float)$slban_i-(float)$donvi['operation_value'];
                                $gianhap_i=(float)$snhap+(float)$donvi['operation_value'];
                                break;
                            case '-':                   
                                $slban_i=(float)$slban_i+(float)$donvi['operation_value'];
                                $gianhap_i=(float)$snhap-(float)$donvi['operation_value'];
                                break;              
                        }
                    }  
                    
                }                
               
                 $slbantong=$slbantong+$slchuyenkho;
                if ($slban_i>=$slbantong) {
 
                    $giavon+=$slbantong*$gianhap_i; 
                    
                    if ($product_id==4) {
                        //echo "ID: ".$row->purchase_id." product_unit_id: ".$row->product_unit_id." NHẬP ".$row->stock." SL NHẬP QUY ĐỔI =>".$slban_i." GIA NHAP ".$gianhap_i." SL TINH VON = ".$slbantong." Giá VON=".$giavon."<br/>";  
                    }  
                    $slbantong=0; 
                    $slconlai=$slbantong;
                }else{
                    $slconlai=$slbantong-$slban_i;                           
                    if ($slconlai>0) {
                        $giavon+=$slban_i*$gianhap_i;       
                    }else{
                        $slban_i=$slbantong;
                        $giavon+=$slban_i*$gianhap_i;       
                    }                   
                    
                    $slbantong=$slconlai; 

                    if ($product_id==4) {
                        //echo "ID: ".$row->purchase_id." product_unit_id: ".$row->product_unit_id." NHẬP ".$row->stock." SL NHẬP QUY ĐỔI =>".$slban_i." GIA NHAP ".$gianhap_i." SL TINH VON = ".$slban_i." Giá VON=".$giavon."<br/>";  
                    }  
                }  
              
                                                                           
            
            } 
            if ($sodongnhap==1) {
                $giavontruocdo=0;    
            }          
            
        }       
        if ($product_id==4) {
            //echo "ID: ".$product_id." NHẬP ".$slnhap." CHUYỂN KHO ".$slchuyenkho." SL BÁN: ".$slbanbandau." => Giá vốn =".$giavon." gia truoc do =".$giavontruocdo."<br/>";
        }
        return $giavon-$giavontruocdo; 
    }
    public function getTongSoluongNhapVao_QuyDoi($product_id=0,$warehouse_id=null,$start=null,$end=null)
    {
        
        $this->db->select('product_unit_id,SUM( CASE WHEN COALESCE(unit_quantity, 0)=0 THEN COALESCE(quantity, 0) ELSE COALESCE(unit_quantity, 0) END ) as stock', False);
        $this->db->where('product_id', $product_id);
        if ($warehouse_id) {
            $this->db->where('warehouse_id', $warehouse_id);

        }
        if ($start&&$end) {            
            $this->db->where("date BETWEEN '".date('Y-m-d', strtotime($start))." 00:00:00' AND '".date('Y-m-d', strtotime($end))." 23:59:59'",null,false);
        }
        $this->db->group_by('product_unit_id');
        //tinh tong don vi ban ra quy doi ve don vi ban lhson code 20/5/2020
        
        $donvi=$this->getdonvitinh($product_id);   
        $slban=0;
        $q = $this->db->get('purchase_items');

        if ($q->num_rows() > 0) {
            $data = $q->result();
            foreach ($data as $row) {
               $donvi_ban=$this->getdonvitinhById($row->product_unit_id);                           

               $std=$slban_i=$row->stock;  

               if ($donvi_ban['id']==$donvi['base_unit']) {//don vi ban ra là bao cha của don vi ban mac dinh 1 cap
                    //quy doi sang don vi con
                    if ($donvi_ban['id']!=$donvi['id']) {//khac don vi ban
                                           
                        switch($donvi['operator']) {                    
                            case '*':           
                                $slban_i=(float)$std/(float)$donvi['operation_value'];
                                break;          
                             case '/':                      
                                $slban_i=(float)$std*(float)$donvi['operation_value'];
                                break;      
                             case '+':                      
                                $slban_i=(float)$std-(float)$donvi['operation_value'];
                                break;
                            case '-':                   
                                $slban_i=(float)$std+(float)$donvi['operation_value'];
                                break;              
                        } 
                    }  
               }else if ((int)$donvi_ban['base_unit']==0&&$donvi['base_unit']>0) {//don vi ban ra là TẤN cha của don vi ban mac dinh 2 cấp
                     //quy doi sang don vi con
                    $donvi_cap1=$this->getdonvitinhByParent($row->product_unit_id);  
                    
                    switch($donvi_cap1['operator']) {                    
                        case '*':           
                            $slban_i=(float)$std/(float)$donvi_cap1['operation_value'];
                            break;          
                         case '/':                      
                            $slban_i=(float)$std*(float)$donvi_cap1['operation_value'];
                            break;      
                         case '+':                      
                            $slban_i=(float)$std-(float)$donvi_cap1['operation_value'];
                            break;
                        case '-':                   
                            $slban_i=(float)$std+(float)$donvi_cap1['operation_value'];
                            break;              
                        } 

                    if ($donvi['id']!=$donvi_cap1['id']) {
                        switch($donvi['operator']) {                    
                            case '*':           
                                $slban_i=(float)$slban_i/(float)$donvi['operation_value'];
                                break;          
                             case '/':                      
                                $slban_i=(float)$slban_i*(float)$donvi['operation_value'];
                                break;      
                             case '+':                      
                                $slban_i=(float)$slban_i-(float)$donvi['operation_value'];
                                break;
                            case '-':                   
                                $slban_i=(float)$slban_i+(float)$donvi['operation_value'];
                                break;              
                        }
                    }  
                    
                }                
                $slban+=$slban_i;                   
            }
           
            return $slban;
        }
        return 0;
    }
    
    function getdonvitinhById($id_donvi=0){   
      
        if($id_donvi>0){    
            $query = $this->db->query("select * from scodeweb_units where id='$id_donvi'");     
            return $query->row_array();     
        }   
    }
    function getdonvitinhByParent($parent_id=0){   
      
        if($parent_id>0){    
            $query = $this->db->query("select * from scodeweb_units where base_unit='$parent_id'");     
            return $query->row_array();     
        }   
    }
    function getAgeNumber($birthdate = '0000-00-00') {
        if ($birthdate == '0000-00-00') return 'Unknown';
        
        $bits = explode('-', $birthdate);
        $age = date('Y') - $bits[0] - 1;
      
        $arr[1] = 'm';
        $arr[2] = 'd';
      
        for ($i = 1; $arr[$i]; $i++) {
            $n = date($arr[$i]);
            if ($n < $bits[$i])
                break;
            if ($n > $bits[$i]) {
                ++$age;
                break;
            }
        }
        return $age;
    }
    function getdonvitinh_capcha($idsp=0){   
    
        $query = $this->db->query("select unit from scodeweb_products where code='$idsp'");        
        $rsdonvi=$query->row_array();       
        $id_donvi=$rsdonvi['unit'];        
        if($id_donvi>0){    
            $query = $this->db->query("select * from scodeweb_units where id='$id_donvi'");     
            return $query->row_array();     
        }   
    }
     public function getAllPurchaseItemsPrint($purchase_id,$order_by=null)
    {
        
        if ($orderby=='m2') {
            $this->db->select('purchase_items.*, tax_rates.code as tax_code, tax_rates.name as tax_name, tax_rates.rate as tax_rate, products.unit, products.details as details, product_variants.name as variant')
            ->join('products', 'products.id=purchase_items.product_id', 'left')
            ->join('product_variants', 'product_variants.id=purchase_items.option_id', 'left')
            ->join('tax_rates', 'tax_rates.id=purchase_items.tax_rate_id', 'left')
            ->group_by('purchase_items.id')
            ->order_by('products.imei', 'desc');
        }else{
            $this->db->select('purchase_items.*, tax_rates.code as tax_code, tax_rates.name as tax_name, tax_rates.rate as tax_rate, products.unit, products.details as details, product_variants.name as variant')
            ->join('products', 'products.id=purchase_items.product_id', 'left')
            ->join('product_variants', 'product_variants.id=purchase_items.option_id', 'left')
            ->join('tax_rates', 'tax_rates.id=purchase_items.tax_rate_id', 'left')
            ->group_by('purchase_items.id')
            ->order_by('id', 'asc');
        }    
        $q = $this->db->get_where('purchase_items', array('purchase_id' => $purchase_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
	function checkExprieLogin(){
		$q = $this->db->select('discount_code')->get_where('settings', array('setting_id' => 1), 1);
        if ($q->num_rows() > 0) 
		{
            if((int)$q->row()->discount_code==1)
			{			
				$this->db->update('settings', array('discount_code' => 0), array('setting_id' => 1));
				$this->session->sess_destroy();
				$this->load->library('ion_auth');
				$this->ion_auth->logout();
				redirect('login');
			}
		}		
	}
    public function getProductPhotos($id)
    {
        $q = $this->db->get_where("product_photos", array('product_id' => $id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }
    public function getProductOptionsById($pid=0,$option_id=0)
    {
        $q = $this->db->get_where('product_variants', array('product_id' => $pid,'id'=>$option_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
    public function getProductOptions($pid)
    {
        $q = $this->db->get_where('product_variants', array('product_id' => $pid));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
    public function getInvoiceByID($id)
    {
        $q = $this->db->get_where('sales', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    function sysnapitmdtRemove($id=0)
    {
        
        //tien hanh xoa product API
        $url=SITE_TMDT_URL."/post-remove-product-api-posbasic-api";
        $api=$this->readPostUrl(array('product_id'=>$id),$url);
    }
    function sysnapitmdtRemoveCarts($api_id=0)
    {
        //tien hanh xoa cart API
        if ($api_id>0) {
            $post=array('order_id'=>$api_id,'sale_status'=>'canceled');
            $url=SITE_TMDT_URL."/post-remove-cart-api-posbasic-api";
            $api=$this->readPostUrl($post,$url); 

        }
    }
    function post_carts_update($sale_id='')
    { 
        //update cart
        
        $order=$this->getInvoiceByID($sale_id);
        if (!empty($order)&&$order->api_id>0) {

            //tien hanh save history order by api
            $history_order['order_id']=$order->id;
            $history_order['order_code']=$order->reference_no;
            $history_order['api_order_id']=$order->api_id;
            $history_order['customer_name']=$order->customer;
            $history_order['customer_id']=$order->customer_id;
            $history_order['total_money']=$order->grand_total;
            $history_order['total_item']=$order->total_items;
            $history_order['type']='Cập nhật';    
            $history_order['created_by']= $this->session->userdata('user_id')!=NULL?$this->session->userdata('user_id'):$order->created_by;
            $this->db->insert('history_api_orders', $history_order);

            $ghichu='';
            if ($order->note!='') {
                $ghichu=$this->strip_tags_content($order->note);
            }
            $post=array('order_id'=>$order->api_id,'sale_status'=>$order->sale_status,'payment_status'=>$order->payment_status,'ghichu'=>$ghichu);

            $url=SITE_TMDT_URL."/post-cart-api-posbasic-api";
            $api=$this->readPostUrl($post,$url); 
        }
    }
     public function getDeliveryByID($id)
    {
        $q = $this->db->get_where('deliveries', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    function updateDeliveryApi($delivery_id=NULL,$status=''){

        $delivery=$this->getDeliveryByID($delivery_id);
       
        if (!empty($delivery)&&$delivery->sale_id>0) {

            $order=$this->getInvoiceByID($delivery->sale_id);
            

            if (!empty($order)&&$order->api_id>0) {

                //tien hanh save history order by api
                $history_order['order_id']=$order->id;
                $history_order['order_code']=$order->reference_no;
                $history_order['api_order_id']=$order->api_id;
                $history_order['customer_name']=$order->customer;
                $history_order['customer_id']=$order->customer_id;
                $history_order['total_money']=$order->grand_total;
                $history_order['total_item']=$order->total_items;
                $history_order['type']='Cập nhật giao hàng';    
                $history_order['created_by']= $this->session->userdata('user_id')!=NULL?$this->session->userdata('user_id'):$order->created_by;
                $this->db->insert('history_api_orders', $history_order);

                $ghichu='';
                if ($order->note!='') {
                    $ghichu=$this->strip_tags_content($order->note);
                }
                $post=array('order_id'=>$order->api_id,'sale_status'=>$delivery->status,'payment_status'=>$order->payment_status,'ghichu'=>$ghichu);

                $url="https://alotoday.vn/post-cart-api-posbasic-api";
                $api=$this->readPostUrl($post,$url); 
            }
        }        
    }
    function strip_tags_content($string) { 
        // ----- remove HTML TAGs ----- 
        $string = preg_replace ('/<[^>]*>/', ' ', $string); 
        // ----- remove control characters ----- 
        $string = str_replace("\r", '', $string);
        $string = str_replace("\n", ' ', $string);
        $string = str_replace("\t", ' ', $string);
        // ----- remove multiple spaces ----- 
        $string = trim(preg_replace('/ {2,}/', ' ', $string));
        return $string; 

    }
   function sysnapitmdt($products=null)
    {        
        if (!empty($products)) {
            $list_products=[];
            foreach ($products as $item) 
            {
                //chuan bi danh san pham va hinh anh
                $options=$this->getProductOptions($item->id);
                $option_posts=[];
                if (!empty($options)) 
                {   

                    foreach ($options as $ioption) 
                    {
                        $stock_option=$this->tonkhohientai_theobienthe($ioption->id);
                        if ((float)$ioption->price>0) {
                            $ioption->price=(float)$ioption->price*100;
                        }
                        $option_posts[]=array('id'=>$ioption->id,'name'=>$ioption->name,'price'=>$ioption->price,'stock'=>$stock_option);        
                    }    
                }
                $datapost['options']=$option_posts;
                $datapost['posbasic_id']=$item->id;
                $datapost['sku']=$item->code;
                $datapost['name']=$item->name;
                $price=$item->price;
                if ((float)$item->promo_price>0) {
                    $price=$item->promo_price;
                }
                $datapost['price']=(float)$price*100;
                $category_id=$item->category_id;
                if ($item->subcategory_id>0) {
                    $category_id=$item->subcategory_id;
                }
                //get santmdt_id by category_id
                $cate_obj=$this->getCategoryByID($category_id);
                $category_id_api=0;
                if (!empty($cate_obj)) {
                    if ((int)$cate_obj->santmdt_id>0) {
                        $category_id_api=$cate_obj->santmdt_id;
                    }
                }
                if ($category_id_api>0) {
                    $datapost['category_id']=$category_id_api;
                    $image="";
                    if ($item->image!='') {
                        $image=base_url().$this->upload_path.$item->image;
                    }
                    $images = $this->getProductPhotos($item->id);
                    $images_list=[];
                    if (!empty($images)) {
                        foreach ($images as $img) {
                            $images_list[]=base_url().$this->upload_path.$img->photo;
                        }
                    }
                    $datapost['image_default']=array($image);
                    $datapost['image_list']=$images_list;
                    $stock=$this->getongtonkhoallkho($item->id);
                    $datapost['stock']=$stock;

                    //tien hanh save history api product
                    $history_product['product_id']=$item->id;
                    $history_product['product_name']=$item->name;
                    $history_product['price']=$price;
                    $history_product['stock']=$stock;
                    $history_product['type']='Đồng bộ';    
                                  
                    $history_product['created_by']=$this->session->userdata('user_id');
                    $this->db->insert('history_api_product', $history_product);

                    $list_products[]=json_encode($datapost);
                }
                
            }
            if (!empty($list_products)) {
                //dong bo san pham
                $url=SITE_TMDT_URL."/post-product-api-posbasic-api";

                $api=$this->readPostUrl($list_products,$url);
                
                if ($api!='OK') 
                {
                    return false;                    
                }
                return true;             
            }           
            
        }
        return false;
        
    }
    
    /*JSON POST API LHSON*/
    public function readPostUrl($json=null,$url='') {
             
        $accessToken="CIwiFtn8QMfIq2MNJqU8Q2lBOhKzpTTIdBBABC";
        $passtoken=md5('posbasic@2021#');   
   
        $http = curl_init($url);
        $data['data']=json_encode($json);
        $data['token']=json_encode($accessToken);
        $obj_setting=$this->get_setting();

        $data['subdomain']=json_encode($obj_setting->scodeweb_username);

        curl_setopt($http, CURLOPT_HEADER, false);
        curl_setopt($http, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($http, CURLOPT_POSTFIELDS, $data);
        curl_setopt($http, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($http, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($http, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($http, CURLOPT_VERBOSE, 0);
        curl_setopt($http, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($http, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);       
        curl_setopt($http, CURLOPT_USERPWD, $accessToken.':'.$passtoken);
            
        $result = curl_exec($http); 
        $errno = curl_errno($http);    
        //echo curl_error($http);
       // echo curl_errno($http);
        curl_close($http);
      // echo curl_strerror($errno);
       // echo var_dump($result); 
        return json_decode($result,true); 

    }
    function sysnStockApiTMDT($product_id=0)
    {
        //dong bo ton kho 
        $product=$this->getProductByID($product_id);
        if (!empty($product)&&$product->is_active_tmdt==1) {
         
            //tien hanh post update
            $product_post=$this->getProductByIDs($product_id);
            $this->sysnapitmdt($product_post);
        }
    }
    public function getProductByIDs($id)
    {
        $q = $this->db->get_where('products', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
    public function getProductHistoryApiById($id) {
        $q = $this->db->get_where('history_api_product', array('product_id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    public function getOrderHistoryApiById($id) {
        $q = $this->db->get_where('history_api_orders', array('order_id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
	public function getInvoiceTMDTByID($id)
    {
        $q = $this->db->get_where('sales', array('api_id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
     public function getAllSaleItemsByTMDT($data_id_san_tmdt=0) {
        $q = $this->db->get_where('sale_items', array('data_id_san_tmdt' => $data_id_san_tmdt));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
    function cms_Show_ThongBao()
    {
        return $this->db->where('daxem',0)->from('thongbao')->count_all_results();        
    }

    function sentNotificationAPI($title='',$message='',$transaction_id=0,$theloai=0,$permission_add='',$warehouse_id=null,$add_phieuchi=false) {

          // FCM API Url
          $url = 'https://fcm.googleapis.com/fcm/send';
          
          // Put your Server Response Key here
          $apiKey = FIREBASE_API_KEY;

          // Compile headers in one variable
          $headers = array (
            'Authorization:key=' . $apiKey,
            'Content-Type:application/json'
          );
          $image='';

          // Add notification content to a variable for easy reference
          $notifData = [
            'title' => $title,
            'body'=>$message,
            'icon'=>$image,
            'type'=>'history',
            'subdomain_type'=>'BANLE',
            'transaction_id'=>$transaction_id,
            'theloai'=>$theloai,
            'add'=>$add_phieuchi
          ];
          $link='';
          // Create the api body
          $apiBody = [
            'notification' => $notifData,
            'data' => $notifData,
            'webpush'=>[
              "fcm_options"=> [
                "link"=>$link
              ]
            ]
          ];
          // get danh sach user quan ly va admin de hien thi
        $list_all_users=$this->db->select('id,group_id,device_token,warehouse_id,view_right')->from('users')->where("device_token!=''",NULL,FALSE)->get()->result_array();
       // echo var_dump($list_all_users);
         $deviceToken=[];
        if (!empty($list_all_users)) 
        {
           
            if ($permission_add=='ADMIN'||$permission_add=='QUANLY'||$permission_add=='ALLADV'||$permission_add=='KETOAN') {
                foreach ($list_all_users as $user) {
                    //get permision
                    $permission = $this->db->where('group_id', $user['group_id'])->from('permissions')->get()->row_array();

                    $_list_permission=[];
                    if (!empty($permission)) 
                    {
                        foreach ($permission as $key=>$per) 
                        {
                            if ($per==1) {
                                $_list_permission[]=$key;                
                            }
                            
                        }    
                    }

                   
                    if ($permission_add=='ADMIN') {
                        if (in_array($user['group_id'], array(1,2)))
                        {        
                            $deviceToken[]=$user['device_token'];
                        }
                    }else if ($permission_add=='QUANLY')
                    {        
                        if ($user['view_right']==1)
                        {        
                            if ($user['warehouse_id']==$warehouse_id) {
                                $deviceToken[]=$user['device_token'];    
                            }
                        }
                    }else if ($permission_add=='KETOAN')
                    {        
                        if (in_array('thu-index', $_list_permission)||in_array('chi-index', $_list_permission))
                        {        
                            if ($user['warehouse_id']==$warehouse_id) {
                                $deviceToken[]=$user['device_token'];    
                            }
                        }
                    }else{
                        if (in_array($user['group_id'], array(1,2)))
                        {  
                            $deviceToken[]=$user['device_token'];    
                                                        
                        }else if ($user['view_right']==1)
                        {        
                            if ($user['warehouse_id']==$warehouse_id) 
                            {
                                $deviceToken[]=$user['device_token'];    
                            }                            
                        }
                    }
                }  
            }else{
                foreach ($list_all_users as $user) {
                    if (in_array($user['group_id'], array(1,2))) {
                        $deviceToken[]=$user['device_token']; 
                    }else{
                        if ($user['warehouse_id']==$warehouse_id) {
                            $deviceToken[]=$user['device_token'];    
                        } 
                    }
                                       
                }
            }
        }
        $deviceToken=array_unique($deviceToken);
        //echo var_dump($deviceToken);
        if (!empty($deviceToken)) {
            $apiBody['registration_ids'] = $deviceToken;
            // Initialize curl with the prepared headers and body
              $ch = curl_init();
              curl_setopt ($ch, CURLOPT_URL, $url );
              curl_setopt ($ch, CURLOPT_POST, true );
              curl_setopt ($ch, CURLOPT_HTTPHEADER, $headers);
              curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true );
              curl_setopt ($ch, CURLOPT_POSTFIELDS, json_encode($apiBody));

              // Execute call and save result
              $result = curl_exec ( $ch );
              //echo var_dump($result);
              // Close curl after call
              curl_close ( $ch );
              return $result;
        }               
       // echo "<hr/>";
    }
    function addPaymentByCode($code=NULL,$note=NULL)
    {     
        

    }
    function loadListUsingByUser($data=NULL)
    {             
    }
    public function DayUsingLeft() {              

    }
    public function getAllPackage() {              

    }
    public function getPackageByUser() {
                     

    }
    public function getAllPTTT($fillter=false) {  
        $q = $this->db->order_by('name')->get('payment_the');
        if ($fillter==false) {
            $data['gift_card_point'] = 'Điểm tích lũy';
            $data['deposit'] = 'Tiền cọc';
        }
        
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[$row->code] = $row->name;
            }
            return $data;
        }
        return FALSE;
    }  
    public function getPerissionByID($id) {
        $q = $this->db->get_where('groups', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }  
    public function getViewDonCu($id) {
        $q = $this->db->get_where('danhsachhoadon_old', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
}
