<?php defined('BASEPATH') or exit('No direct script access allowed');

class Returns_model extends CI_Model
{

    public function __construct()
    {
        parent::__construct();
    }

    public function getProductNames($term, $limit = 5)
    {
        $this->db->where("(name LIKE '%" . $term . "%' OR code LIKE '%" . $term . "%' OR  concat(name, ' (', code, ')') LIKE '%" . $term . "%')");
        $this->db->limit($limit);
        $q = $this->db->get('products');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getReturnByID($id)
    {
        $q = $this->db->get_where('returns', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getReturnItems($return_id)
    {
        $this->db->select('return_items.*, tax_rates.code as tax_code, tax_rates.name as tax_name, tax_rates.rate as tax_rate, products.image, products.details as details, product_variants.name as variant')
            ->join('products', 'products.id=return_items.product_id', 'left')
            ->join('product_variants', 'product_variants.id=return_items.option_id', 'left')
            ->join('tax_rates', 'tax_rates.id=return_items.tax_rate_id', 'left')
            ->where('return_id', $return_id)
            ->group_by('return_items.id')
            ->order_by('id', 'asc');

        $q = $this->db->get('return_items');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function addReturn($data = array(), $items = array(),$payment=array(),$lhson_return=array())
    {
		
		$this->load->model('sales_model');
		$this->load->model('purchases_model');
		$sale_data=$data;
        if ($this->db->insert('returns', $data)) {
            $return_id = $this->db->insert_id();
            if ($this->site->getReference('re') == $data['reference_no']) {
                $this->site->updateReference('re');
            }
			$sale_data['return_sale_ref']=$return_id;
			$sale_data['return_id']=$return_id;
			$sale_data['grand_total']=(0-$sale_data['grand_total']);
			$sale_data['return_sale_total']=(0-$sale_data['grand_total']);
			$sale_data['sale_status']='returned';
			$sale_data['paid']=(0-$sale_data['grand_total']);
			$sale_data['payment_status']='paid';
			
			unset($sale_data['hash']);
			
			$items['quantity']=(0-$items['quantity']);
			$items['unit_quantity']=(0-$items['unit_quantity']);
			
			//tien hanh luu phieu chi
			$data_phieuchi = array(
				'date' => $data['date'],
				'reference' => "THUHOI-".$data['reference_no'],
				'amount' =>$data['grand_total'],
				'created_by' => $this->session->userdata('user_id'),
				'note' =>'[Chi thu hồi]'.$data['note'],
				'category_id' =>0,
				'warehouse_id' =>$data['warehouse_id'],
				'customer_id'=>$data['customer_id'],
				'id_return'=>$return_id,
			);
			
			$this->purchases_model->addExpense($data_phieuchi);
			
			//$this->sales_model->addSale($sale_data, $items, $payment,null,$lhson_return);
			
            foreach ($items as $item) {
				
                $item['return_id'] = $return_id;
				if(count($item)>1){
					if($this->db->insert('return_items', $item)){
						if ($item['product_type'] == 'standard') {
							$clause = ['product_id' => $item['product_id'], 'warehouse_id' => $item['warehouse_id'], 'purchase_id' => null, 'transfer_id' => null, 'option_id' => $item['option_id'],'status' =>'received','thuhoi_id'=>$return_id];
							$this->site->setPurchaseItem($clause, $item['quantity']);
							$this->site->syncQuantity(null, null, null, $item['product_id']);
						} elseif ($item['product_type'] == 'combo') {
							$combo_items = $this->site->getProductComboItems($item['product_id']);
							foreach ($combo_items as $combo_item) {
								$clause = ['product_id' => $combo_item->id, 'purchase_id' => null, 'transfer_id' => null, 'option_id' => null];
								$this->site->setPurchaseItem($clause, ($combo_item->qty*$item['quantity']));
								$this->site->syncQuantity(null, null, null, $combo_item->id);
							}
						}
					}
				}
            }
            return true;
        }

        return false;
    }

    public function updateReturn($id, $data = array(), $items = array(),$payment=array(),$lhson_return=array())
    {
		
        $this->resetSaleActions($id);
		$this->load->model('sales_model');
		$this->load->model('purchases_model');
		 $return_id = $id;
        if ($this->db->update('returns', $data, array('id' => $id)) && $this->db->delete('return_items', array('return_id' => $id))) {
            
			$sale_data= $data;
			$sale_data['date']=date('Y-m-d H:i:s');
			$sale_data['return_sale_ref']=$return_id;
			
			$sale_data['grand_total']=(0-$sale_data['grand_total']);
			$sale_data['return_sale_total']=(0-$sale_data['grand_total']);
			$sale_data['sale_status']='returned';
			$sale_data['paid']=(0-$sale_data['grand_total']);
			$sale_data['payment_status']='paid';
			
			unset($sale_data['hash']);
			unset($sale_data['id']);
			
			//tien hanh luu phieu chi
			$data_phieuchi = array(
				'date' => $data['date'],
				'reference' => "THUHOI-".$data['reference_no'],
				'amount' =>$data['grand_total'],
				'created_by' => $this->session->userdata('user_id'),
				'note' =>'[Chi thu hồi]'.$data['note'],
				'category_id' =>0,
				'warehouse_id' =>$data['warehouse_id'],
				'customer_id'=>$data['customer_id'],
				'id_return'=>$return_id,
			);
			$id_chi=(int)$this->getIdPhieuChiReturnId($return_id)->id;
						
			$this->purchases_model->updateExpense($id_chi,$data_phieuchi);
						
			//$this->sales_model->deleteSaleReturn($id_sale,$lhson_return);			
			
			$items['quantity']=(0-$items['quantity']);
			$items['unit_quantity']=(0-$items['unit_quantity']);
			
			//$this->sales_model->addSale($sale_data, $items, $payment,null,$lhson_return);
			
            foreach ($items as $item) {
                 $item['return_id'] = $return_id;
				 if(count($item)>1){
					 
					 $this->db->insert('return_items', $item);
					
					if ($item['product_type'] == 'standard') {
						$clause = ['product_id' => $item['product_id'], 'purchase_id' => null, 'transfer_id' => null, 'option_id' => $item['option_id'],'status' =>'received','thuhoi_id'=>$return_id];
					   $this->site->setPurchaseItem($clause, $item['quantity']);
						$this->site->syncQuantity(null, null, null, $item['product_id']);
					} elseif ($item['product_type'] == 'combo') {
						$combo_items = $this->site->getProductComboItems($item['product_id']);
						foreach ($combo_items as $combo_item) {
							$clause = ['product_id' => $combo_item->id, 'purchase_id' => null, 'transfer_id' => null, 'option_id' => null];
							$this->site->setPurchaseItem($clause, ($combo_item->qty*$item['quantity']));
							$this->site->syncQuantity(null, null, null, $combo_item->id);
						}
					}
				 }
            }
            return true;
        }

        return false;
    }

    public function resetSaleActions($id)
    {
        if ($items = $this->getReturnItems($id)) {
            foreach ($items as $item) {
                if ($item->product_type == 'standard') {
                    $clause = ['product_id' => $item->product_id, 'purchase_id' => null, 'transfer_id' => null, 'option_id' => $item->option_id];
                    $this->site->setPurchaseItem($clause, (0-$item->quantity));
                    $this->site->syncQuantity(null, null, null, $item->product_id);
                } elseif ($item->product_type == 'combo') {
                    $combo_items = $this->site->getProductComboItems($item->product_id);
                    foreach ($combo_items as $combo_item) {
                        $clause = ['product_id' => $combo_item->id, 'purchase_id' => null, 'transfer_id' => null, 'option_id' => null];
                        $this->site->setPurchaseItem($clause, (0-($combo_item->qty*$item->quantity)));
                        $this->site->syncQuantity(null, null, null, $combo_item->id);
                    }
                }
            }
        }
    }

    public function deleteReturn($id)
    {
		$this->load->model('sales_model');
		$this->load->model('purchases_model');
        $this->resetSaleActions($id);
		$id_sale=(int)$this->getIdSaleByReturnId($id)->id;
        if ($this->db->delete('return_items', array('return_id' => $id)) && $this->db->delete('returns', array('id' => $id))) {
			//DELETE SALE 
			//get id sale by return_id			
			// if($this->sales_model->deleteSale($id_sale)){
				// return true;
			// }
			$id_chi=(int)$this->getIdPhieuChiReturnId($id)->id;						
			$this->purchases_model->getExpenseByID($id_chi);
			// huy phiếu chi
			if ($this->db->delete('expenses', array('id' => $id_chi))) {
				
			}
			//huy purchase_items
			$_query="DELETE FROM {$this->db->dbprefix('purchase_items')} WHERE purchase_id IS NULL AND transfer_id IS NULL AND thuhoi_id=".$id;
			$q= $this->db->query($_query);	
			
			return true;			
        }
        return false;
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
        return false;
    }

    public function getProductOptionByID($id)
    {
        $q = $this->db->get_where('product_variants', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
	 public function getIdSaleByReturnId($return_id=0)
    {
        $q = $this->db->get_where('sales', array('return_id' => $return_id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
	 public function getIdPhieuChiReturnId($return_id=0)
    {
        $q = $this->db->get_where('expenses', array('id_return' => $return_id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
}
