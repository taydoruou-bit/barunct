<?php defined('BASEPATH') OR exit('No direct script access allowed');



class Khuyenmai_model extends CI_Model

{



    public function __construct()

    {

        parent::__construct();

    }



    public function getProductNames($term, $warehouse_id, $limit = 5)

    {

        $this->db->select('products.*, warehouses_products.quantity')

            ->join('warehouses_products', 'warehouses_products.product_id=products.id', 'left')

            ->group_by('products.id');



            $this->db->where("(name LIKE '%" . $term . "%' OR code LIKE '%" . $term . "%' OR  concat(name, ' (', code, ')') LIKE '%" . $term . "%')");

 

        $this->db->limit($limit);

        $q = $this->db->get('products');

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

	public function getProductById($code)

    {

        $q = $this->db->get_where('products', array('id' => $code), 1);

        if ($q->num_rows() > 0) {

            return $q->row();

        }

        return FALSE;

    }

    public function getWHProduct($id)

    {

        $this->db->select('products.id, code, name, warehouses_products.quantity, cost, tax_rate')

            ->join('warehouses_products', 'warehouses_products.product_id=products.id', 'left')

            ->group_by('products.id');

        $q = $this->db->get_where('products', array('warehouses_products.product_id' => $id), 1);

        if ($q->num_rows() > 0) {

            return $q->row();

        }

        return FALSE;

    }



    public function getItemByID($id)

    {

        $q = $this->db->get_where('khuyenmai_items', array('id' => $id), 1);

        if ($q->num_rows() > 0) {

            return $q->row();

        }

        return FALSE;

    }



    public function getAllQuoteItemsWithDetails($quote_id)

    {

        $this->db->select('khuyenmai_items.id, khuyenmai_items.product_name, quote_items.product_code, quote_items.quantity, quote_items.serial_no, quote_items.tax, quote_items.unit_price, quote_items.val_tax, quote_items.discount_val, quote_items.gross_total, products.details');

        $this->db->join('products', 'products.id=khuyenmai_items.product_id', 'left');

        $this->db->order_by('id', 'asc');

        $q = $this->db->get_where('khuyenmai_items', array('khuyenmai_id' => $quote_id));

        if ($q->num_rows() > 0) {

            foreach (($q->result()) as $row) {

                $data[] = $row;

            }

            return $data;

        }

    }



    public function getQuoteByID($id)

    {

        $q = $this->db->get_where('khuyenmai', array('id' => $id), 1);

        if ($q->num_rows() > 0) {

            return $q->row();

        }

        return FALSE;

    }



    public function getAllQuoteItems($quote_id)

    {

        $this->db->select('khuyenmai_items.*, products.name as product_name,products.code as product_code, products.image, products.details as details,products.promo_price as giakhuyenmai,products.price')

            ->join('products', 'products.id=khuyenmai_items.product_id', 'left')

            ->group_by('khuyenmai_items.id')

            ->order_by('id', 'asc');

        $q = $this->db->get_where('khuyenmai_items', array('khuyenmai_id' => $quote_id));

        if ($q->num_rows() > 0) {

            foreach (($q->result()) as $row) {

                $data[] = $row;

            }

            return $data;

        }

        return FALSE;

    }



    public function addKhuyenmai($data = array(), $items = array())

    {

        if ($this->db->insert('khuyenmai', $data)) {

            $khuyenmai_id = $this->db->insert_id();

            

            foreach ($items as $item) {

                $item['khuyenmai_id'] = $khuyenmai_id;

                $this->db->insert('khuyenmai_items', $item);

            }

            return true;

        }

        return false;

    }





    public function updateKhuyenmai($id, $data, $items = array())

    {

		//update all old product to none

		$this->db->select('khuyenmai_items.*, products.id as product_id')

            ->join('products', 'products.id=khuyenmai_items.product_id', 'left')

            ->group_by('khuyenmai_items.id')

            ->order_by('id', 'asc');

        $q = $this->db->get_where('khuyenmai_items', array('khuyenmai_id' => $id));

        if ($q->num_rows() > 0) {

            foreach (($q->result()) as $row) {

				$data_update=array('promotion' =>0,'promo_price' => 0,

					'start_date' => null,'end_date' => null);					

					

                $this->updateProduct($row->product_id, $data_update);

            }            

        }

		

		

        if ($this->db->update('khuyenmai', $data, array('id' => $id)) && $this->db->delete('khuyenmai_items', array('khuyenmai_id' => $id))) {

            foreach ($items as $item) {

                $item['khuyenmai_id'] = $id;

                $this->db->insert('khuyenmai_items', $item);

            }

            return true;

        }

        return false;

    }



    public function updateStatus($id, $status, $note)

    {

        if ($this->db->update('khuyenmai', array('status' => $status, 'note' => $note), array('id' => $id))) {

            return true;

        }

        return false;

    }





    public function deleteQuote($id)

    {
        //update all product in khuyenmai_items
        $query_update_product="UPDATE scodeweb_products sp,scodeweb_khuyenmai_items km SET promotion=0,promo_price=0,start_date=NULL,end_date=NULL WHERE sp.id=km.product_id AND khuyenmai_id=".(int)$id;
        $q= $this->db->query($query_update_product); 

        if ($this->db->delete('khuyenmai_items', array('khuyenmai_id' => $id)) && $this->db->delete('khuyenmai', array('id' => $id))) {

            return true;

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



    public function getWarehouseProductQuantity($warehouse_id, $product_id)

    {

        $q = $this->db->get_where('warehouses_products', array('warehouse_id' => $warehouse_id, 'product_id' => $product_id), 1);

        if ($q->num_rows() > 0) {

            return $q->row();

        }

        return FALSE;

    }



    public function getProductComboItems($pid, $warehouse_id)

    {

        $this->db->select('products.id as id, combo_items.item_code as code, combo_items.quantity as qty, products.name as name, products.type as type, warehouses_products.quantity as quantity')

            ->join('products', 'products.code=combo_items.item_code', 'left')

            ->join('warehouses_products', 'warehouses_products.product_id=products.id', 'left')

            ->where('warehouses_products.warehouse_id', $warehouse_id)

            ->group_by('combo_items.id');

        $q = $this->db->get_where('combo_items', array('combo_items.product_id' => $pid));

        if ($q->num_rows() > 0) {

            foreach (($q->result()) as $row) {

                $data[] = $row;

            }



            return $data;

        }

        return FALSE;

    }



    public function getProductOptions($product_id, $warehouse_id)

    {

        $this->db->select('product_variants.id as id, product_variants.name as name, product_variants.price as price, product_variants.quantity as total_quantity, warehouses_products_variants.quantity as quantity')

            ->join('warehouses_products_variants', 'warehouses_products_variants.option_id=product_variants.id', 'left')

            //->join('warehouses', 'warehouses.id=product_variants.warehouse_id', 'left')

            ->where('product_variants.product_id', $product_id)

            ->where('warehouses_products_variants.warehouse_id', $warehouse_id)

            ->where('warehouses_products_variants.quantity >', 0)

            ->group_by('product_variants.id');

        $q = $this->db->get('product_variants');

        if ($q->num_rows() > 0) {

            foreach (($q->result()) as $row) {

                $data[] = $row;

            }

            return $data;

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

	 public function updateProduct($id, $data)

    {

		$this->load->database();

        $this->db->where('id', $id);

        $this->db->update('products', $data);

		// echo var_dump($this->db->error());

		// $where=array();

		// foreach($data as $key=>$val){

			// $where[]=$key."='".$val."'";

		// }

		// if(count($where)>0){

			// $sql ="UPDATE scodeweb_products SET ".implode(",",$where)." WHERE id=".$id;

			// $query = $this->db->query($sql);

			// if(!$query){

				// echo "LOI:".$id;

				// echo $this->db->last_query();				

				// echo var_dump($this->db->error());

			// }

		// }		

	}

	public function getProductsSuggestions($term, $limit = 10)

    {

        $this->db->select("id,CONCAT(name, ' (', code, ')')as text", FALSE);

        $this->db->where(" (id LIKE '%" . $term . "%' OR name LIKE '%" . $term . "%' OR code LIKE '%" . $term . "%') ");

        $q = $this->db->get('products', array(), $limit);

        if ($q->num_rows() > 0) {

            foreach (($q->result()) as $row) {

                $data[] = $row;

            }

            return $data;

        }

    }

	

}

