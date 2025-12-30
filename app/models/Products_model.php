<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Products_model extends CI_Model
{

    public function __construct()
    {
        parent::__construct();
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

    public function getCategoryProducts($category_id)
    {
        $q = $this->db->get_where('products', array('category_id' => $category_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getSubCategoryProducts($subcategory_id)
    {
        $q = $this->db->get_where('products', array('subcategory_id' => $subcategory_id));
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

    public function getProductOptionsWithWH($pid)
    {
        $this->db->select($this->db->dbprefix('product_variants') . '.*,0 as tondau, ' . $this->db->dbprefix('warehouses') . '.name as wh_name,'.$this->db->dbprefix('warehouses') . '.id as wh_id,'. $this->db->dbprefix('warehouses') . '.id as warehouse_id, ' . $this->db->dbprefix('warehouses_products_variants') . '.quantity as wh_qty')
            ->join('warehouses_products_variants', 'warehouses_products_variants.option_id=product_variants.id', 'left')
            ->join('warehouses', 'warehouses.id=warehouses_products_variants.warehouse_id', 'left')
            ->group_by(array('' . $this->db->dbprefix('product_variants') . '.id', '' . $this->db->dbprefix('warehouses_products_variants') . '.warehouse_id'))
            ->order_by('product_variants.id');
        $q = $this->db->get_where('product_variants', array('product_variants.product_id' => $pid, 'warehouses_products_variants.quantity !=' => NULL));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
				$_nhap=$this->getTonKhoDauTienThepSPMAU($pid,$row->wh_id,$row->id);
				$row->tondau=(float)$_nhap->quantity;
                $data[] = $row;
            }
            return $data;
        }
    }

    public function getProductComboItems($pid)
    {
        $this->db->select($this->db->dbprefix('products') . '.id as id, ' . $this->db->dbprefix('products') . '.code as code, ' . $this->db->dbprefix('combo_items') . '.quantity as qty, ' . $this->db->dbprefix('products') . '.name as name, ' . $this->db->dbprefix('combo_items') . '.unit_price as price')->join('products', 'products.code=combo_items.item_code', 'left')->group_by('combo_items.id');
        $q = $this->db->get_where('combo_items', array('product_id' => $pid));
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

   

    public function getProductWithCategory($id)
    {
        $this->db->select($this->db->dbprefix('products') . '.*, ' . $this->db->dbprefix('categories') . '.name as category')
        ->join('categories', 'categories.id=products.category_id', 'left');
        $q = $this->db->get_where('products', array('products.id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function has_purchase($product_id, $warehouse_id = NULL)
    {
        if($warehouse_id) { $this->db->where('warehouse_id', $warehouse_id); }
        $q = $this->db->get_where('purchase_items', array('product_id' => $product_id), 1);
        if ($q->num_rows() > 0) {
            return TRUE;
        }
        return FALSE;
    }

    public function getProductDetails($id)
    {
        $this->db->select($this->db->dbprefix('products') . '.code, ' . $this->db->dbprefix('products') . '.name, ' . $this->db->dbprefix('categories') . '.code as category_code, cost, price, quantity, alert_quantity')
            ->join('categories', 'categories.id=products.category_id', 'left');
        $q = $this->db->get_where('products', array('products.id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getProductDetail($id)
    {
        $this->db->select($this->db->dbprefix('products') . '.*, ' . $this->db->dbprefix('tax_rates') . '.name as tax_rate_name, '.$this->db->dbprefix('tax_rates') . '.code as tax_rate_code, c.code as category_code, sc.code as subcategory_code', FALSE)
            ->join('tax_rates', 'tax_rates.id=products.tax_rate', 'left')
            ->join('categories c', 'c.id=products.category_id', 'left')
            ->join('categories sc', 'sc.id=products.subcategory_id', 'left');
        $q = $this->db->get_where('products', array('products.id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getSubCategories($parent_id) {
        $this->db->select('id as id, name as text')
        ->where('parent_id', $parent_id)->order_by('name');
        $q = $this->db->get("categories");
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getProductByCategoryID($id)
    {

        $q = $this->db->get_where('products', array('category_id' => $id), 1);
        if ($q->num_rows() > 0) {
            return true;
        }
        return FALSE;
    }

    public function getAllWarehousesWithPQ($product_id)
    {
        $this->db->select('' . $this->db->dbprefix('warehouses') . '.*,0 as tondau,0 as giatondau, ' . $this->db->dbprefix('warehouses_products') . '.quantity, ' . $this->db->dbprefix('warehouses_products') . '.rack')
            ->join('warehouses_products', 'warehouses_products.warehouse_id=warehouses.id', 'left')
            ->where('warehouses_products.product_id', $product_id)
            ->group_by('warehouses.id');
        $q = $this->db->get('warehouses');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
				$_nhap=$this->getTonKhoDauTienThepSP($product_id,$row->id);
				$row->tondau=isset($_nhap->quantity)?$_nhap->quantity:0;
                 $row->giatondau=isset($_nhap->unit_cost)?$_nhap->unit_cost:0;
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
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
	public function getTonKhoDauTienThepSP($idsp,$warehouse_id)
    {
        $q = $this->db->get_where('purchase_items', array('product_id' => $idsp,'option_id' => NULL,'warehouse_id' => $warehouse_id,'bandau_id' => $idsp),1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
	public function getTonKhoDauTienThepSPMAU($idsp,$warehouse_id,$option_id)
    {
        $q = $this->db->get_where('purchase_items', array('product_id' => $idsp,'warehouse_id' => $warehouse_id,'bandau_id' => $idsp,'option_id'=>$option_id),1);
        if ($q->num_rows() > 0) {
            return $q->row();
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

    public function addProduct($data, $items, $warehouse_qty, $product_attributes, $photos)
    {
        if ($this->db->insert('products', $data)) {
            $product_id = $this->db->insert_id();

            if ($items) {
                foreach ($items as $item) {
                    $item['product_id'] = $product_id;
                    $this->db->insert('combo_items', $item);
                }
            }

            $warehouses = $this->site->getAllWarehouses();
            if ($data['type'] != 'standard') {
                foreach ($warehouses as $warehouse) {
                    $this->db->insert('warehouses_products', array('product_id' => $product_id, 'warehouse_id' => $warehouse->id, 'quantity' => 0));
                }
            }

            $tax_rate = $this->site->getTaxRateByID($data['tax_rate']);

            if ($warehouse_qty && !empty($warehouse_qty)) {
                foreach ($warehouse_qty as $wh_qty) {
                    if (isset($wh_qty['quantity']) && ! empty($wh_qty['quantity'])) {
                        if ($wh_qty['price']>0) {
                            $data['cost']=$wh_qty['price'];
                        }
                        
                        $this->db->insert('warehouses_products', array('product_id' => $product_id, 'warehouse_id' => $wh_qty['warehouse_id'], 'quantity' => $wh_qty['quantity'], 'rack' => $wh_qty['rack'], 'avg_cost' => $data['cost']));

                        if (!$product_attributes) {
                            $tax_rate_id = $tax_rate ? $tax_rate->id : NULL;
                            $tax = $tax_rate ? (($tax_rate->type == 1) ? $tax_rate->rate . "%" : $tax_rate->rate) : NULL;
                            
                            $unit_cost = $data['cost'];
                            if ($tax_rate) {
                                if ($tax_rate->type == 1 && $tax_rate->rate != 0) {
                                    if ($data['tax_method'] == '0') {
										if($data['cost']==0){
											$pr_tax_val = 0;
											$net_item_cost = 0;
											$item_tax = 0;
										}else{
											$pr_tax_val = ($data['cost'] * $tax_rate->rate) / (100 + $tax_rate->rate);
											$net_item_cost = $data['cost'] - $pr_tax_val;
											$item_tax = $pr_tax_val * $wh_qty['quantity'];
										}
                                        
                                    } else {
                                        $net_item_cost = $data['cost'];
                                        $pr_tax_val = ($data['cost'] * $tax_rate->rate) / 100;
                                        $unit_cost = $data['cost'] + $pr_tax_val;
                                        $item_tax = $pr_tax_val * $wh_qty['quantity'];
                                    }
                                } else {
                                    $net_item_cost = $data['cost'];
                                    $item_tax = $tax_rate->rate;
                                }
                            } else {
                                $net_item_cost = $data['cost'];
                                $item_tax = 0;
                            }

                            $subtotal = (($net_item_cost * $wh_qty['quantity']) + $item_tax);

                            $item = array(
                                'product_id' => $product_id,
                                'product_code' => $data['code'],
                                'product_name' => $data['name'],
                                'net_unit_cost' => $net_item_cost,
                                'unit_cost' => $unit_cost,
                                'real_unit_cost' => $unit_cost,
                                'quantity' => $wh_qty['quantity'],
                                'unit_quantity' => $wh_qty['quantity'],
                                'quantity_balance' => $wh_qty['quantity'],
                                'item_tax' => $item_tax,
                                'tax_rate_id' => $tax_rate_id,
                                'tax' => $tax,
                                'subtotal' => $subtotal,
                                'warehouse_id' => $wh_qty['warehouse_id'],
                                'date' => date('Y-m-d'),
                                'status' => 'received',
								'bandau_id'=>$product_id
                            );
                            $this->db->insert('purchase_items', $item);
                            $this->site->syncProductQty($product_id, $wh_qty['warehouse_id']);
                        }
                    }
                }
            }

            if ($product_attributes) {
                foreach ($product_attributes as $pr_attr) {
                    $pr_attr_details = $this->getPrductVariantByPIDandName($product_id, $pr_attr['name']);

                    $pr_attr['product_id'] = $product_id;
                    $variant_warehouse_id = $pr_attr['warehouse_id'];
                    unset($pr_attr['warehouse_id']);
                    if ($pr_attr_details) {
                        $option_id = $pr_attr_details->id;
                    } else {
                        $this->db->insert('product_variants', $pr_attr);
                        $option_id = $this->db->insert_id();
                    }
                    if ($pr_attr['quantity'] != 0) {
                        $this->db->insert('warehouses_products_variants', array('option_id' => $option_id, 'product_id' => $product_id, 'warehouse_id' => $variant_warehouse_id, 'quantity' => $pr_attr['quantity']));

                        $tax_rate_id = $tax_rate ? $tax_rate->id : NULL;
                        $tax = $tax_rate ? (($tax_rate->type == 1) ? $tax_rate->rate . "%" : $tax_rate->rate) : NULL;
                        $unit_cost = $data['cost'];
                        if ($tax_rate) {
                            if ($tax_rate->type == 1 && $tax_rate->rate != 0) {
                                if ($data['tax_method'] == '0') {
                                    $pr_tax_val = ($data['cost'] * $tax_rate->rate) / (100 + $tax_rate->rate);
                                    $net_item_cost = $data['cost'] - $pr_tax_val;
                                    $item_tax = $pr_tax_val * $pr_attr['quantity'];
                                } else {
                                    $net_item_cost = $data['cost'];
                                    $pr_tax_val = ($data['cost'] * $tax_rate->rate) / 100;
                                    $unit_cost = $data['cost'] + $pr_tax_val;
                                    $item_tax = $pr_tax_val * $pr_attr['quantity'];
                                }
                            } else {
                                $net_item_cost = $data['cost'];
                                $item_tax = $tax_rate->rate;
                            }
                        } else {
                            $net_item_cost = $data['cost'];
                            $item_tax = 0;
                        }

                        $subtotal = (($net_item_cost * $pr_attr['quantity']) + $item_tax);
                        $item = array(
                            'product_id' => $product_id,
                            'product_code' => $data['code'],
                            'product_name' => $data['name'],
                            'net_unit_cost' => $net_item_cost,
                            'unit_cost' => $unit_cost,
                            'quantity' => $pr_attr['quantity'],
                            'unit_quantity' => $pr_attr['quantity'],
                            'option_id' => $option_id,
                            'quantity_balance' => $pr_attr['quantity'],
                            'item_tax' => $item_tax,
                            'tax_rate_id' => $tax_rate_id,
                            'tax' => $tax,
                            'subtotal' => $subtotal,
                            'warehouse_id' => $variant_warehouse_id,
                            'date' => date('Y-m-d'),
                            'status' => 'received',
							'bandau_id'=>$product_id
                        );
                        $this->db->insert('purchase_items', $item);

                    }

                    foreach ($warehouses as $warehouse) {
                        if (!$this->getWarehouseProductVariant($warehouse->id, $product_id, $option_id)) {
                            $this->db->insert('warehouses_products_variants', array('option_id' => $option_id, 'product_id' => $product_id, 'warehouse_id' => $warehouse->id, 'quantity' => 0));
                        }
                    }

                    $this->site->syncVariantQty($option_id, $variant_warehouse_id);
                }
            }

            if ($photos) {
                foreach ($photos as $photo) {
                    $this->db->insert('product_photos', array('product_id' => $product_id, 'photo' => $photo));
                }
            }
			if ($data['is_active_tmdt']==1){
                //tien hanh POST API UPLOAD
                $list_products=$this->getProductByIDs($product_id);                
                $this->site->sysnapitmdt($list_products);

            }
            return true;
        }
		
        return false;

    }

    public function getPrductVariantByPIDandName($product_id, $name)
    {
        $q = $this->db->get_where('product_variants', array('product_id' => $product_id, 'name' => $name), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function addAjaxProduct($data)
    {
        if ($this->db->insert('products', $data)) {
            $product_id = $this->db->insert_id();
            return $this->getProductByID($product_id);
        }
        return false;
    }

    public function add_products($products = array())
    {
        if (!empty($products)) {
            $warehouse=$this->site->getAllWarehouses();

            foreach ($products as $product) {
                $variants = explode('|', $product['variants']);
                unset($product['variants']);
                
                $rack=$product['rack'];
                unset($product['rack']);

                $tondau=(int)$product['tondau'];
                unset($product['tondau']);
                
                if ($this->db->insert('products', $product)) {
                    $product_id = $this->db->insert_id();
                    if ($rack!=''&&$warehouse[0]->id>0) {
                        //tao rack
                        $this->db->delete('warehouses_products', array('product_id' => $product_id)); 
                        //get default warehouse_id
                                               
                        $this->db->insert('warehouses_products', array('product_id' => $product_id, 'warehouse_id' => $warehouse[0]->id, 'quantity' => $tondau, 'rack' => $rack, 'avg_cost' => $product['cost']));
                    }
                    // end them ton dau xls lhson 3/2/2021
                    if ($tondau>0&&$warehouse[0]->id>0) {                              

                        $this->db->delete('purchase_items', array('bandau_id' => $product_id));
                        $subtotal = ($product['cost'] * $tondau);

                        $item_purchase = array(
                            'product_id' => $product_id,
                            'product_code' => $product['code'],
                            'product_name' => $product['name'],
                            'net_unit_cost' => $product['cost'],
                            'unit_cost' => $product['cost'],
                            'real_unit_cost' => $product['cost'],
                            'quantity' => $tondau,
                            'unit_quantity' => $tondau,
                            'quantity_balance' => $tondau,
                            'item_tax' => 0,
                            'tax_rate_id' => null,
                            'tax' => '',
                            'subtotal' => $subtotal,
                            'warehouse_id' => $warehouse[0]->id,
                            'date' => date('Y-m-d'),
                            'status' => 'received',
                            'bandau_id'=>$product_id
                        );
                        $this->db->insert('purchase_items', $item_purchase);
                        $this->site->syncProductQty($product_id, $warehouse[0]->id);
                    }
                    // end them ton dau xls lhson 3/2/2021

                    foreach ($variants as $variant) {
                        if ($variant && trim($variant) != '') {
                            $vat = array('product_id' => $product_id, 'name' => trim($variant));
                            $this->db->insert('product_variants', $vat);
                        }
                    }
                }
            }
            return true;
        }
        return false;
    }
    public function update_products($products = array())
    {
        if (!empty($products)) {

            $warehouse=$this->site->getAllWarehouses();

            foreach ($products as $product) {
                $variants = explode('|', $product['variants']);
                unset($product['variants']);
                
                $rack=$product['rack'];
                unset($product['rack']);
                
                $tondau=$product['tondau'];
                unset($product['tondau']);

                $product_obj=$this->getProductByCode($product['code']);
                if ($product_obj&&$product_obj->id>0) {
                    $product_id=$product_obj->id;
                    if ($this->db->update('products', $product, array('id' => $product_id))) {
                    
                        if ($rack!=''&&$warehouse[0]->id>0) {
                            //tao rack
                            $this->db->delete('warehouses_products', array('product_id' => $product_id)); 
                            //get default warehouse_id
                                                   
                            $this->db->insert('warehouses_products', array('product_id' => $product_id, 'warehouse_id' => $warehouse[0]->id, 'quantity' => 0, 'rack' => $rack, 'avg_cost' => $product['cost']));

                        }
                        // end them ton dau xls lhson 3/2/2021
                        if ($tondau>0&&$warehouse[0]->id>0) {                              
                                       
                            $this->db->delete('purchase_items', array('bandau_id' => $product_id));
                            $subtotal = ($product['cost'] * $tondau);

                            $item_purchase = array(
                                'product_id' => $product_id,
                                'product_code' => $product['code'],
                                'product_name' => $product['name'],
                                'net_unit_cost' => $product['cost'],
                                'unit_cost' => $product['cost'],
                                'real_unit_cost' => $product['cost'],
                                'quantity' => $tondau,
                                'unit_quantity' => $tondau,
                                'quantity_balance' => $tondau,
                                'item_tax' => 0,
                                'tax_rate_id' => null,
                                'tax' => '',
                                'subtotal' => $subtotal,
                                'warehouse_id' => $warehouse[0]->id,
                                'date' => date('Y-m-d'),
                                'status' => 'received',
                                'bandau_id'=>$product_id
                            );
                            $this->db->insert('purchase_items', $item_purchase);
                            $this->site->syncProductQty($product_id, $warehouse[0]->id);
                        }
                        // end them ton dau xls lhson 3/2/2021
                    
                        foreach ($variants as $variant) {
                            if ($variant && trim($variant) != '') {
                                $vat = array('product_id' => $product_id, 'name' => trim($variant));
                                $this->db->insert('product_variants', $vat);
                            }
                        }
                    }
                }
                
            }
            return true;
        }
        return false;
    }
    public function getProductNames($term, $limit = 5)
    {
        $this->db->select('' . $this->db->dbprefix('products') . '.id, code, ' . $this->db->dbprefix('products') . '.name as name, ' . $this->db->dbprefix('products') . '.price as price, ' . $this->db->dbprefix('product_variants') . '.name as vname')
            ->where("type != 'combo' AND "
                . "(" . $this->db->dbprefix('products') . ".name LIKE '%" . $term . "%' OR code LIKE '%" . $term . "%' OR
                concat(" . $this->db->dbprefix('products') . ".name, ' (', code, ')') LIKE '%" . $term . "%')");
        $this->db->join('product_variants', 'product_variants.product_id=products.id', 'left')
            ->where('' . $this->db->dbprefix('product_variants') . '.name', NULL)
            ->group_by('products.id')->limit($limit);
        $q = $this->db->get('products');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getQASuggestions($term, $limit = 5)
    {
        $this->db->select('' . $this->db->dbprefix('products') . '.id, code, ' . $this->db->dbprefix('products') . '.name as name')
            ->where("type != 'combo' AND "
                . "(" . $this->db->dbprefix('products') . ".name LIKE '%" . $term . "%' OR code LIKE '%" . $term . "%' OR
                concat(" . $this->db->dbprefix('products') . ".name, ' (', code, ')') LIKE '%" . $term . "%')")
            ->limit($limit);
        $q = $this->db->get('products');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getProductsForPrinting($term, $limit = 5)
    {
        $this->db->select('' . $this->db->dbprefix('products') . '.id, code, ' . $this->db->dbprefix('products') . '.name as name, ' . $this->db->dbprefix('products') . '.price as price')
            ->where("(" . $this->db->dbprefix('products') . ".name LIKE '%" . $term . "%' OR code LIKE '%" . $term . "%' OR
                concat(" . $this->db->dbprefix('products') . ".name, ' (', code, ')') LIKE '%" . $term . "%')")
            ->limit($limit);
        $q = $this->db->get('products');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function updateProduct($id, $data, $items, $warehouse_qty, $product_attributes, $photos, $update_variants,$warehouses=null)
    {
        if ($this->db->update('products', $data, array('id' => $id))) {
			$product_id=$id;
            if ($items) {
                $this->db->delete('combo_items', array('product_id' => $id));
                foreach ($items as $item) {
                    $item['product_id'] = $id;
                    $this->db->insert('combo_items', $item);
                }
            }

            $tax_rate = $this->site->getTaxRateByID($data['tax_rate']);
			if ($warehouse_qty && !empty($warehouse_qty)) {
				//tien hanh xoa nhap dau cu
				$this->db->delete('purchase_items', array('bandau_id' => $id));
				
                foreach ($warehouse_qty as $wh_qty) {
                    if (isset($wh_qty['quantity']) && ! empty($wh_qty['quantity'])) {
						//tien hanh xoa warehouses_products
						$this->db->delete('warehouses_products', array('product_id' => $id));
						
                        $this->db->insert('warehouses_products', array('product_id' => $product_id, 'warehouse_id' => $wh_qty['warehouse_id'], 'quantity' => $wh_qty['quantity'], 'rack' => $wh_qty['rack'], 'avg_cost' => $data['cost']));

                        if (!$product_attributes&&$wh_qty['quantity']>0) {
                            $tax_rate_id = $tax_rate ? $tax_rate->id : NULL;
                            $tax = $tax_rate ? (($tax_rate->type == 1) ? $tax_rate->rate . "%" : $tax_rate->rate) : NULL;
                            if ($wh_qty['price']>0) {
                                $data['cost']=$wh_qty['price'];
                            }
                            $unit_cost = $data['cost'];

                            if ($tax_rate) {
                                if ($tax_rate->type == 1 && $tax_rate->rate != 0) {
                                    if ($data['tax_method'] == '0') {
										if($data['cost']==0){
											$pr_tax_val = 0;
											$net_item_cost = 0;
											$item_tax = 0;
										}else{
											$pr_tax_val = ($data['cost'] * $tax_rate->rate) / (100 + $tax_rate->rate);
											$net_item_cost = $data['cost'] - $pr_tax_val;
											$item_tax = $pr_tax_val * $wh_qty['quantity'];
										}
                                        
                                    } else {
                                        $net_item_cost = $data['cost'];
                                        $pr_tax_val = ($data['cost'] * $tax_rate->rate) / 100;
                                        $unit_cost = $data['cost'] + $pr_tax_val;
                                        $item_tax = $pr_tax_val * $wh_qty['quantity'];
                                    }
                                } else {
                                    $net_item_cost = $data['cost'];
                                    $item_tax = $tax_rate->rate;
                                }
                            } else {
                                $net_item_cost = $data['cost'];
                                $item_tax = 0;
                            }

                            $subtotal = (($net_item_cost * $wh_qty['quantity']) + $item_tax);

                            $item = array(
                                'product_id' => $product_id,
                                'product_code' => $data['code'],
                                'product_name' => $data['name'],
                                'net_unit_cost' => $net_item_cost,
                                'unit_cost' => $unit_cost,
                                'real_unit_cost' => $unit_cost,
                                'quantity' => $wh_qty['quantity'],
                                'unit_quantity' => $wh_qty['quantity'],
                                'quantity_balance' => $wh_qty['quantity'],
                                'item_tax' => $item_tax,
                                'tax_rate_id' => $tax_rate_id,
                                'tax' => $tax,
                                'subtotal' => $subtotal,
                                'warehouse_id' => $wh_qty['warehouse_id'],
                                'date' => date('Y-m-d'),
                                'status' => 'received',
								'bandau_id'=>$product_id
                            );
                            $this->db->insert('purchase_items', $item);
                            $this->site->syncProductQty($product_id, $wh_qty['warehouse_id']);
                        }
                    }
                }
            }

            if ($product_attributes) {
				if (!empty($warehouse_qty)) {
					//tien hanh xoa nhap dau cu
					$this->db->delete('purchase_items', array('bandau_id' => $id));
				}
                foreach ($product_attributes as $pr_attr) {
					
                    $pr_attr_details = $this->getPrductVariantByPIDandName($product_id, $pr_attr['name']);

                    $pr_attr['product_id'] = $product_id;
                    $variant_warehouse_id = $pr_attr['warehouse_id'];
                    unset($pr_attr['warehouse_id']);
                    if ($pr_attr_details) {
                        $option_id = $pr_attr_details->id;
                        //update
                       

                    } else {
                        $this->db->insert('product_variants', $pr_attr);
                        $option_id = $this->db->insert_id();
                    }
                    if ($pr_attr['quantity'] != 0) {
                        $this->db->insert('warehouses_products_variants', array('option_id' => $option_id, 'product_id' => $product_id, 'warehouse_id' => $variant_warehouse_id, 'quantity' => $pr_attr['quantity']));

                        $tax_rate_id = $tax_rate ? $tax_rate->id : NULL;
                        $tax = $tax_rate ? (($tax_rate->type == 1) ? $tax_rate->rate . "%" : $tax_rate->rate) : NULL;
                        $unit_cost = $data['cost'];
                        if ($tax_rate) {
                            if ($tax_rate->type == 1 && $tax_rate->rate != 0) {
                                if ($data['tax_method'] == '0') {
                                    $pr_tax_val = ($data['cost'] * $tax_rate->rate) / (100 + $tax_rate->rate);
                                    $net_item_cost = $data['cost'] - $pr_tax_val;
                                    $item_tax = $pr_tax_val * $pr_attr['quantity'];
                                } else {
                                    $net_item_cost = $data['cost'];
                                    $pr_tax_val = ($data['cost'] * $tax_rate->rate) / 100;
                                    $unit_cost = $data['cost'] + $pr_tax_val;
                                    $item_tax = $pr_tax_val * $pr_attr['quantity'];
                                }
                            } else {
                                $net_item_cost = $data['cost'];
                                $item_tax = $tax_rate->rate;
                            }
                        } else {
                            $net_item_cost = $data['cost'];
                            $item_tax = 0;
                        }

                        $subtotal = (($net_item_cost * $pr_attr['quantity']) + $item_tax);
                        $item = array(
                            'product_id' => $product_id,
                            'product_code' => $data['code'],
                            'product_name' => $data['name'],
                            'net_unit_cost' => $net_item_cost,
                            'unit_cost' => $unit_cost,
                            'quantity' => $pr_attr['quantity'],
                            'unit_quantity' => $pr_attr['quantity'],
                            'option_id' => $option_id,
                            'quantity_balance' => $pr_attr['quantity'],
                            'item_tax' => $item_tax,
                            'tax_rate_id' => $tax_rate_id,
                            'tax' => $tax,
                            'subtotal' => $subtotal,
                            'warehouse_id' => $variant_warehouse_id,
                            'date' => date('Y-m-d'),
                            'status' => 'received',
							'bandau_id'=>$product_id
                        );
                        $this->db->insert('purchase_items', $item);

                    }

                    // foreach ($warehouses as $warehouse) {
                        // if (!$this->getWarehouseProductVariant($warehouse->id, $product_id, $option_id)) {
                            // $this->db->insert('warehouses_products_variants', array('option_id' => $option_id, 'product_id' => $product_id, 'warehouse_id' => $warehouse->id, 'quantity' => 0));
                        // }
                    // }

                    $this->site->syncVariantQty($option_id, $variant_warehouse_id);
                }
            }
				
			if ($update_variants) { //lhson 1406
                foreach ($update_variants as $pr_attr) {
                    
					$option_id=$pr_attr['id'];
                    $pr_attr['product_id'] = $product_id;
                    		
									
					$this->db->update('product_variants', array('price' => $pr_attr['price'],'name' => $pr_attr['name']), array('id' => $pr_attr['id'], 'product_id' => $product_id));

						
					
                }
            }
           /*
			if ($warehouse_qty && !empty($warehouse_qty)) {
                foreach ($warehouse_qty as $wh_qty) {
                    $this->db->update('warehouses_products', array('rack' => $wh_qty['rack']), array('product_id' => $id, 'warehouse_id' => $wh_qty['warehouse_id']));
					$this->db->update('warehouses_products', array('quantity' => $wh_qty['quantity']), array('product_id' => $id, 'warehouse_id' => $wh_qty['warehouse_id']));
                }
            }

           
			*/
            if ($photos) {
                foreach ($photos as $photo) {
                    $this->db->insert('product_photos', array('product_id' => $id, 'photo' => $photo));
                }
            }

            /*if ($product_attributes) {
                foreach ($product_attributes as $pr_attr) {

                    $pr_attr['product_id'] = $id;
                    $variant_warehouse_id = $pr_attr['warehouse_id'];
                    unset($pr_attr['warehouse_id']);
                    $this->db->insert('product_variants', $pr_attr);
                    $option_id = $this->db->insert_id();

                    if ($pr_attr['quantity'] != 0) {
                        $this->db->insert('warehouses_products_variants', array('option_id' => $option_id, 'product_id' => $id, 'warehouse_id' => $variant_warehouse_id, 'quantity' => $pr_attr['quantity']));

                        $tax_rate_id = $tax_rate ? $tax_rate->id : NULL;
                        $tax = $tax_rate ? (($tax_rate->type == 1) ? $tax_rate->rate . "%" : $tax_rate->rate) : NULL;
                        $unit_cost = $data['cost'];
                        if ($tax_rate) {
                            if ($tax_rate->type == 1 && $tax_rate->rate != 0) {
                                if ($data['tax_method'] == '0') {
                                    $pr_tax_val = ($data['cost'] * $tax_rate->rate) / (100 + $tax_rate->rate);
                                    $net_item_cost = $data['cost'] - $pr_tax_val;
                                    $item_tax = $pr_tax_val * $pr_attr['quantity'];
                                } else {
                                    $net_item_cost = $data['cost'];
                                    $pr_tax_val = ($data['cost'] * $tax_rate->rate) / 100;
                                    $unit_cost = $data['cost'] + $pr_tax_val;
                                    $item_tax = $pr_tax_val * $pr_attr['quantity'];
                                }
                            } else {
                                $net_item_cost = $data['cost'];
                                $item_tax = $tax_rate->rate;
                            }
                        } else {
                            $net_item_cost = $data['cost'];
                            $item_tax = 0;
                        }

                        $subtotal = (($net_item_cost * $pr_attr['quantity']) + $item_tax);
                        $item = array(
                            'product_id' => $id,
                            'product_code' => $data['code'],
                            'product_name' => $data['name'],
                            'net_unit_cost' => $net_item_cost,
                            'unit_cost' => $unit_cost,
                            'quantity' => $pr_attr['quantity'],
                            'option_id' => $option_id,
                            'quantity_balance' => $pr_attr['quantity'],
                            'item_tax' => $item_tax,
                            'tax_rate_id' => $tax_rate_id,
                            'tax' => $tax,
                            'subtotal' => $subtotal,
                            'warehouse_id' => $variant_warehouse_id,
                            'date' => date('Y-m-d'),
                            'status' => 'received',
                        );
                        $this->db->insert('purchase_items', $item);

                    }
                }
            }*/

            $this->site->syncQuantity(NULL, NULL, NULL, $id);
            if ($data['is_active_tmdt']==1){
                //tien hanh POST API UPLOAD
                $list_products=$this->getProductByIDs($product_id);                
                $this->site->sysnapitmdt($list_products);

            }

            return true;
        } else {
            return false;
        }
    }

    public function updateProductOptionQuantity($option_id, $warehouse_id, $quantity, $product_id)
    {
        if ($option = $this->getProductWarehouseOptionQty($option_id, $warehouse_id)) {
            if ($this->db->update('warehouses_products_variants', array('quantity' => $quantity), array('option_id' => $option_id, 'warehouse_id' => $warehouse_id))) {
                $this->site->syncVariantQty($option_id, $warehouse_id);
                return TRUE;
            }
        } else {
            if ($this->db->insert('warehouses_products_variants', array('option_id' => $option_id, 'product_id' => $product_id, 'warehouse_id' => $warehouse_id, 'quantity' => $quantity))) {
                $this->site->syncVariantQty($option_id, $warehouse_id);
                return TRUE;
            }
        }
        return FALSE;
    }

    public function updatePrice($data = array())
    {
        if ($this->db->update_batch('products', $data, 'code')) {
            return true;
        }
        return false;
    }

    public function deleteProduct($id)
    {
        $product=$this->site->getProductByID($id);

        if ($this->db->delete('products', array('id' => $id)) && $this->db->delete('warehouses_products', array('product_id' => $id))) {
            $this->db->delete('warehouses_products_variants', array('product_id' => $id));
            $this->db->delete('product_variants', array('product_id' => $id));
            $this->db->delete('product_photos', array('product_id' => $id));
            $this->db->delete('product_prices', array('product_id' => $id));

            if ($product->is_active_tmdt==1){
                 //tien hanh save history api product

                $stock=$this->site->getongtonkhoallkho($product->id);
                $price=$product->price;
                if ((float)$product->promo_price>0) {
                    $price=$product->promo_price;
                }
                $history_product['product_id']=$product->id;
                $history_product['product_name']=$product->name;
                $history_product['price']=$price;
                $history_product['stock']=$stock;
                $history_product['type']='Xóa';    
                              
                $history_product['created_by']= $this->session->userdata('user_id');
                $this->db->insert('history_api_product', $history_product);

                //tien hanh POST API UPLOAD                               
                $this->site->sysnapitmdtRemove($id);
                
            }
            return true;
        }
        return FALSE;
    }
	public function deleteAttrLhson($id){
		$tongnhap=$this->site->tongNhapKhoTheoBienThe($id);
		if((float)$tongnhap>0){
			return 'Biến thể có sản phẩm đã nhập hàng không thể xóa';
		}
		//check nhap hang
		$tongban=$this->site->getTongSoluongBanra_bienthe($id);
		if((float)$tongban>0){
			return 'Biến thể có sản phẩm đã bán ra không thể xóa';
		}
		//check ban hang
		if ($this->db->delete('product_variants', array('id' => $id))){
			return 'OK';
		}
		return FALSE;
	}


    public function totalCategoryProducts($category_id)
    {
        $q = $this->db->get_where('products', array('category_id' => $category_id));
        return $q->num_rows();
    }

    public function getCategoryByCode($code)
    {
        $q = $this->db->get_where('categories', array('code' => $code), 1);
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

    public function getAdjustmentByID($id)
    {
        $q = $this->db->get_where('adjustments', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getAdjustmentItems($adjustment_id)
    {
        $this->db->select('adjustment_items.*, products.code as product_code, products.name as product_name, products.image, products.details as details, product_variants.name as variant')
            ->join('products', 'products.id=adjustment_items.product_id', 'left')
            ->join('product_variants', 'product_variants.id=adjustment_items.option_id', 'left')
            ->group_by('adjustment_items.id')
            ->order_by('id', 'asc');

        $this->db->where('adjustment_id', $adjustment_id);

        $q = $this->db->get('adjustment_items');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function syncAdjustment($data = array())
    {
        if(! empty($data)) {
            $where_clause = array('product_id' => $data['product_id'], 'option_id' => $data['option_id'], 'warehouse_id' => $data['warehouse_id'], 'status' => 'received');
            if ($purchase_item = $this->site->getPurchasedItem($where_clause)) {
                $quantity_balance = $data['type'] == 'subtraction' ? $purchase_item->quantity_balance - $data['quantity'] : $purchase_item->quantity_balance + $data['quantity'];
                $this->db->update('purchase_items', array('quantity_balance' => $quantity_balance), array('id' => $purchase_item->id));
            } else {
                $pr = $this->site->getProductByID($data['product_id']);
                $item = array(
                    'product_id' => $data['product_id'],
                    'product_code' => $pr->code,
                    'product_name' => $pr->name,
                    'net_unit_cost' => 0,
                    'unit_cost' => 0,
                    'quantity' => 0,
                    'option_id' => $data['option_id'],
                    'quantity_balance' => $data['type'] == 'subtraction' ? (0 - $data['quantity']) : $data['quantity'],
                    'item_tax' => 0,
                    'tax_rate_id' => 0,
                    'tax' => '',
                    'subtotal' => 0,
                    'warehouse_id' => $data['warehouse_id'],
                    'date' => date('Y-m-d'),
                    'status' => 'received',
                );
                $this->db->insert('purchase_items', $item);
            }

            $this->site->syncProductQty($data['product_id'], $data['warehouse_id']);
            if ($data['option_id']) {
                $this->site->syncVariantQty($data['option_id'], $data['warehouse_id'], $data['product_id']);
            }

        }
    }

    public function reverseAdjustment($id)
    {
        if ($products = $this->getAdjustmentItems($id)) {
            foreach ($products as $adjustment) {
                $where_clause = array('product_id' => $adjustment->product_id, 'warehouse_id' => $adjustment->warehouse_id, 'option_id' => $adjustment->option_id, 'status' => 'received');
                if ($purchase_item = $this->site->getPurchasedItem($where_clause)) {
                    $quantity_balance = $adjustment->type == 'subtraction' ? $purchase_item->quantity_balance + $adjustment->quantity : $purchase_item->quantity_balance - $adjustment->quantity;
                    $this->db->update('purchase_items', array('quantity_balance' => $quantity_balance), array('id' => $purchase_item->id));
                }

                $this->site->syncProductQty($adjustment->product_id, $adjustment->warehouse_id);
                if ($adjustment->option_id) {
                    $this->site->syncVariantQty($adjustment->option_id, $adjustment->warehouse_id, $adjustment->product_id);
                }
            }
        }
    }

    public function addAdjustment($data, $products)
    {
        if ($this->db->insert('adjustments', $data)) {
            $adjustment_id = $this->db->insert_id();
            foreach ($products as $product) {
                $product['adjustment_id'] = $adjustment_id;
                $this->db->insert('adjustment_items', $product);
                $this->syncAdjustment($product);

                $this->site->sysnStockApiTMDT($product['product_id']);
            }
            if ($this->site->getReference('qa') == $data['reference_no']) {
                $this->site->updateReference('qa');
            }
            

            return true;
        }
        return false;
    }

    public function updateAdjustment($id, $data, $products)
    {
        $this->reverseAdjustment($id);
        if ($this->db->update('adjustments', $data, array('id' => $id)) && 
            $this->db->delete('adjustment_items', array('adjustment_id' => $id))) {
            foreach ($products as $product) {
                $product['adjustment_id'] = $id;
                $this->db->insert('adjustment_items', $product);
                $this->syncAdjustment($product);
                $this->site->sysnStockApiTMDT($product['product_id']);
            }
            return true;
        }
        return false;
    }

    public function deleteAdjustment($id)
    {
        $this->reverseAdjustment($id);

        if ( $this->db->delete('adjustments', array('id' => $id)) && 
            $this->db->delete('adjustment_items', array('adjustment_id' => $id))) {
            return true;
        }
        return false;
    }

    public function getProductQuantity($product_id, $warehouse)
    {
        $q = $this->db->get_where('warehouses_products', array('product_id' => $product_id, 'warehouse_id' => $warehouse), 1);
        if ($q->num_rows() > 0) {
            return $q->row_array(); //$q->row();
        }
        return FALSE;
    }

    public function addQuantity($product_id, $warehouse_id, $quantity, $rack = NULL)
    {

        if ($this->getProductQuantity($product_id, $warehouse_id)) {
            if ($this->updateQuantity($product_id, $warehouse_id, $quantity, $rack)) {
                return TRUE;
            }
        } else {
            if ($this->insertQuantity($product_id, $warehouse_id, $quantity, $rack)) {
                return TRUE;
            }
        }

        return FALSE;
    }

    public function insertQuantity($product_id, $warehouse_id, $quantity, $rack = NULL)
    {
        $product = $this->site->getProductByID($product_id);
        if ($this->db->insert('warehouses_products', array('product_id' => $product_id, 'warehouse_id' => $warehouse_id, 'quantity' => $quantity, 'rack' => $rack, 'avg_cost' => $product->cost))) {
            $this->site->syncProductQty($product_id, $warehouse_id);
            $this->site->sysnStockApiTMDT($product_id);
            return true;
        }
        return false;
    }

    public function updateQuantity($product_id, $warehouse_id, $quantity, $rack = NULL)
    {
        $data = $rack ? array('quantity' => $quantity, 'rack' => $rack) : $data = array('quantity' => $quantity);
        if ($this->db->update('warehouses_products', $data, array('product_id' => $product_id, 'warehouse_id' => $warehouse_id))) {
            $this->site->syncProductQty($product_id, $warehouse_id);
            $this->site->sysnStockApiTMDT($product_id);
            return true;
        }
        return false;
    }

    public function products_count($category_id, $subcategory_id = NULL)
    {
        if ($category_id) {
            $this->db->where('category_id', $category_id);
        }
        if ($subcategory_id) {
            $this->db->where('subcategory_id', $subcategory_id);
        }
        $this->db->from('products');
        return $this->db->count_all_results();
    }

    public function fetch_products($category_id, $limit, $start, $subcategory_id = NULL)
    {

        $this->db->limit($limit, $start);
        if ($category_id) {
            $this->db->where('category_id', $category_id);
        }
        if ($subcategory_id) {
            $this->db->where('subcategory_id', $subcategory_id);
        }
        $this->db->order_by("id", "asc");
        $query = $this->db->get("products");

        if ($query->num_rows() > 0) {
            foreach ($query->result() as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getProductWarehouseOptionQty($option_id, $warehouse_id)
    {
        $q = $this->db->get_where('warehouses_products_variants', array('option_id' => $option_id, 'warehouse_id' => $warehouse_id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function syncVariantQty($option_id)
    {
        $wh_pr_vars = $this->getProductWarehouseOptions($option_id);
        $qty = 0;
        foreach ($wh_pr_vars as $row) {
            $qty += $row->quantity;
        }
        if ($this->db->update('product_variants', array('quantity' => $qty), array('id' => $option_id))) {
            return TRUE;
        }
        return FALSE;
    }

    public function getProductWarehouseOptions($option_id)
    {
        $q = $this->db->get_where('warehouses_products_variants', array('option_id' => $option_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function setRack($data)
    {
        if ($this->db->update('warehouses_products', array('rack' => $data['rack']), array('product_id' => $data['product_id'], 'warehouse_id' => $data['warehouse_id']))) {
            return TRUE;
        }
        return FALSE;
    }

    public function getSoldQty($id)
    {
        $this->db->select("date_format(" . $this->db->dbprefix('sales') . ".date, '%Y-%M') month, SUM( " . $this->db->dbprefix('sale_items') . ".quantity ) as sold, SUM( " . $this->db->dbprefix('sale_items') . ".subtotal ) as amount")
            ->from('sales')
            ->join('sale_items', 'sales.id=sale_items.sale_id', 'left')
            ->group_by("date_format(" . $this->db->dbprefix('sales') . ".date, '%Y-%m')")
            ->where($this->db->dbprefix('sale_items') . '.product_id', $id)
            //->where('DATE(NOW()) - INTERVAL 1 MONTH')
            ->where('DATE_ADD(curdate(), INTERVAL 1 MONTH)')
            ->order_by("date_format(" . $this->db->dbprefix('sales') . ".date, '%Y-%m') desc")->limit(3);
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getPurchasedQty($id)
    {
        $this->db->select("date_format(" . $this->db->dbprefix('purchases') . ".date, '%Y-%M') month, SUM( " . $this->db->dbprefix('purchase_items') . ".quantity ) as purchased, SUM( " . $this->db->dbprefix('purchase_items') . ".subtotal ) as amount")
            ->from('purchases')
            ->join('purchase_items', 'purchases.id=purchase_items.purchase_id', 'left')
            ->group_by("date_format(" . $this->db->dbprefix('purchases') . ".date, '%Y-%m')")
            ->where($this->db->dbprefix('purchase_items') . '.product_id', $id)
            //->where('DATE(NOW()) - INTERVAL 1 MONTH')
            ->where('DATE_ADD(curdate(), INTERVAL 1 MONTH)')
            ->order_by("date_format(" . $this->db->dbprefix('purchases') . ".date, '%Y-%m') desc")->limit(3);
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getAllVariants()
    {
        $q = $this->db->get('variants');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getWarehouseProductVariant($warehouse_id, $product_id, $option_id = NULL)
    {
        $q = $this->db->get_where('warehouses_products_variants', array('product_id' => $product_id, 'option_id' => $option_id, 'warehouse_id' => $warehouse_id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getPurchaseItems($purchase_id)
    {
        $q = $this->db->get_where('purchase_items', array('purchase_id' => $purchase_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getTransferItems($transfer_id)
    {
        $q = $this->db->get_where('purchase_items', array('transfer_id' => $transfer_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getUnitByCode($code)
    {
        $q = $this->db->get_where("units", array('code' => $code), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getBrandByName($name)
    {
        $q = $this->db->get_where('brands', array('name' => $name), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getStockCountProducts($warehouse_id, $type, $categories = NULL, $brands = NULL)
    {
        $this->db->select("{$this->db->dbprefix('products')}.id as id, {$this->db->dbprefix('products')}.code as code, {$this->db->dbprefix('products')}.name as name, {$this->db->dbprefix('warehouses_products')}.quantity as quantity")
        ->join('warehouses_products', 'warehouses_products.product_id=products.id', 'left')
        ->where('warehouses_products.warehouse_id', $warehouse_id)
        ->where('products.type', 'standard')
        ->order_by('products.code', 'asc');
        if ($categories) {
            $r = 1;
            $this->db->group_start();
            foreach ($categories as $category) {
                if ($r == 1) {
                    $this->db->where('products.category_id', $category);
                } else {
                    $this->db->or_where('products.category_id', $category);
                }
                $r++;
            }
            $this->db->group_end();
        }
        if ($brands) {
            $r = 1;
            $this->db->group_start();
            foreach ($brands as $brand) {
                if ($r == 1) {
                    $this->db->where('products.brand', $brand);
                } else {
                    $this->db->or_where('products.brand', $brand);
                }
                $r++;
            }
            $this->db->group_end();
        }

        $q = $this->db->get('products');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getStockCountProductVariants($warehouse_id, $product_id)
    {
        $this->db->select("{$this->db->dbprefix('product_variants')}.name, {$this->db->dbprefix('warehouses_products_variants')}.quantity as quantity")
            ->join('warehouses_products_variants', 'warehouses_products_variants.option_id=product_variants.id', 'left');
        $q = $this->db->get_where('product_variants', array('product_variants.product_id' => $product_id, 'warehouses_products_variants.warehouse_id' => $warehouse_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }

    public function addStockCount($data)
    {
        if ($this->db->insert('stock_counts', $data)) {
            return TRUE;
        }
        return FALSE;
    }

    public function finalizeStockCount($id, $data, $products)
    {
        if ($this->db->update('stock_counts', $data, array('id' => $id))) {
            foreach ($products as $product) {
                $this->db->insert('stock_count_items', $product);
            }
            return TRUE;
        }
        return FALSE;
    }

    public function getStouckCountByID($id)
    {
        $q = $this->db->get_where("stock_counts", array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getStockCountItems($stock_count_id)
    {
        $q = $this->db->get_where("stock_count_items", array('stock_count_id' => $stock_count_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return NULL;
    }

    public function getAdjustmentByCountID($count_id)
    {
        $q = $this->db->get_where('adjustments', array('count_id' => $count_id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getProductVariantID($product_id, $name)
    {
        $q = $this->db->get_where("product_variants", array('product_id' => $product_id, 'name' => $name), 1);
        if ($q->num_rows() > 0) {
            $variant = $q->row();
            return $variant->id;
        }
        return NULL;
    }
	function getLastProductCode()
    {		
		$q = $this->db->query('SELECT code FROM '.$this->db->dbprefix('products').' ORDER BY ID DESC LIMIT 1');			
		if ($q->num_rows() > 0)
        {
			$variant = $q->row();
			return $variant->code;
		}
	}
	public function checkExistNameProduct($code='',$name='',$id=NULL)
    {
		if($name!='')
        {
            $q = $this->db->query('SELECT id FROM '.$this->db->dbprefix('products').' WHERE name='.$this->db->escape($name));
			
            if($id!=NULL)
            {
				$q = $this->db->query('SELECT id FROM '.$this->db->dbprefix('products').' WHERE id!='.$this->db->escape($id).' AND name='.$this->db->escape($name));
			}
			if ($q->num_rows()>0)
            {
				$variant = $q->row();
				return (int)$variant->id;
			}else{
                $q = $this->db->query('SELECT id FROM '.$this->db->dbprefix('products').' WHERE id!='.$this->db->escape($id).' AND code='.$this->db->escape($code));
                $variant = $q->row();
                return (int)$variant->id;
            }
			return 0;
		}
		return 0;
	}

	function getDetailXuatNhapTon($product_id=0,$warehouse_id=0)
    {
        $query="SELECT tbl.*,(select name from scodeweb_units where id=product_unit_id) as donvi FROM(SELECT quantity,unit_quantity,(select purchase_unit from scodeweb_products where id=product_id) as product_unit_id,product_unit_code,unit_cost as price,DATE_FORMAT(date, '%Y-%m-%d %T') as date,'BANDAU' as trangthai,bandau_id FROM `scodeweb_purchase_items` WHERE quantity!=0 and thuhoi_id=0 and bandau_id>0 AND product_id=".$product_id." AND warehouse_id=".$warehouse_id." UNION SELECT sp.quantity,sp.unit_quantity,sp.product_unit_id,sp.product_unit_code,sp.unit_cost as price,DATE_FORMAT(nh.date, '%Y-%m-%d %T') as date,'THUHOI' as trangthai,bandau_id FROM `scodeweb_purchase_items` sp,scodeweb_purchases nh WHERE nh.id=sp.purchase_id and sp.quantity!=0 and sp.thuhoi_id>0 and (sp.bandau_id=0 OR sp.bandau_id is NULL) AND sp.product_id=".$product_id." AND sp.warehouse_id=".$warehouse_id." UNION SELECT sp.quantity,sp.unit_quantity,sp.product_unit_id,sp.product_unit_code,sp.unit_cost as price,DATE_FORMAT(nh.date, '%Y-%m-%d %H:%i:%s') as date,'NHAPMOI' as trangthai,bandau_id FROM `scodeweb_purchase_items` sp,scodeweb_purchases nh WHERE sp.purchase_id=nh.id and sp.quantity!=0 and sp.thuhoi_id=0 and (sp.bandau_id=0 OR sp.bandau_id is NULL) AND sp.product_id=".$product_id." AND sp.warehouse_id=".$warehouse_id." UNION SELECT sp.quantity,sp.unit_quantity,sp.product_unit_id,sp.product_unit_code,sp.unit_cost as price,DATE_FORMAT(nh.date, '%Y-%m-%d %H:%i:%s') as date,'NHANCHUYENKHO' as trangthai,bandau_id FROM `scodeweb_purchase_items` sp,scodeweb_transfers nh WHERE sp.transfer_id=nh.id and sp.quantity!=0 AND sp.product_id=".$product_id." AND sp.warehouse_id=".$warehouse_id." UNION SELECT sp.quantity,sp.unit_quantity,sp.product_unit_id,sp.product_unit_code,sp.unit_cost as price,DATE_FORMAT(nh.date, '%Y-%m-%d %H:%i:%s') as date,'CHUYENKHODI' as trangthai,bandau_id FROM `scodeweb_purchase_items` sp,scodeweb_transfers nh WHERE sp.transfer_id=nh.id and sp.quantity!=0 AND sp.product_id=".$product_id." AND nh.from_warehouse_id=".$warehouse_id." UNION SELECT quantity,unit_quantity,product_unit_id,product_unit_code,unit_price as price,sale.date,'SALE' as trangthai,0 as bandau_id FROM `scodeweb_sales` as sale,scodeweb_sale_items as sales WHERE sale.id=sales.sale_id AND quantity!=0 AND product_id=".$product_id." AND sales.warehouse_id=".$warehouse_id." UNION SELECT quantity,quantity as unit_quantity,(select purchase_unit from scodeweb_products where id=product_id) as product_unit_id,0 as product_unit_code,0 as price,adjustments.date,'CHINHKHO+' as trangthai,0 as bandau_id FROM `scodeweb_adjustments` as adjustments,scodeweb_adjustment_items as adjustment_items WHERE adjustments.id=adjustment_items.adjustment_id AND quantity!=0 AND product_id=".$product_id." AND adjustments.warehouse_id=".$warehouse_id." AND type='addition' UNION SELECT quantity,quantity as unit_quantity,(select purchase_unit from scodeweb_products where id=product_id) as product_unit_id,0 as product_unit_code,0 as price,adjustments.date,'CHINHKHO-' as trangthai,0 as bandau_id FROM `scodeweb_adjustments` as adjustments,scodeweb_adjustment_items as adjustment_items WHERE adjustments.id=adjustment_items.adjustment_id AND quantity!=0 AND product_id=".$product_id." AND adjustments.warehouse_id=".$warehouse_id." AND type!='addition') as tbl ORDER BY bandau_id DESC, date";
        
        $q = $this->db->query($query); 
        if ($q->num_rows() > 0)
        {
            foreach (($q->result()) as $row)
            {
                $data[] = $row;
            }
            return $data;
        }
        return NULL;
    }

    public function getAllProductsSanTmdt()
    {
        $q = $this->db->where('is_active_tmdt',1)->where('type','standard')->get('products');
        if ($q->num_rows() > 0)
        {
            foreach (($q->result()) as $row)
            {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
    public function getProductByIDs($id)
    {
        $q = $this->db->get_where('products', array('id' => $id), 1);
        if ($q->num_rows() > 0)
        {
            foreach (($q->result()) as $row)
            {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
}
