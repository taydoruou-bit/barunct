<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Quotes_model extends CI_Model
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
        $q = $this->db->get_where('quote_items', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getAllQuoteItemsWithDetails($quote_id)
    {
        $this->db->select('quote_items.id, quote_items.product_name, quote_items.product_code, quote_items.quantity, quote_items.serial_no, quote_items.tax, quote_items.unit_price, quote_items.val_tax, quote_items.discount_val, quote_items.gross_total, products.details, products.group_id');
        $this->db->join('products', 'products.id=quote_items.product_id', 'left');
        $this->db->order_by('id', 'asc');
        $q = $this->db->get_where('quotes_items', array('quote_id' => $quote_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }

    public function getQuoteByID($id)
    {
        $q = $this->db->get_where('quotes', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

// ✅ TÌM HÀM getAllQuoteItems() TRONG Quotes_model.php
public function getAllQuoteItems($quote_id)
{
    $this->db->select('quote_items.*, tax_rates.code as tax_code, tax_rates.name as tax_name, tax_rates.rate as tax_rate, products.unit, products.image, products.details as details, products.group_id, product_variants.name as variant')
        ->join('products', 'products.id=quote_items.product_id', 'left')
        ->join('product_variants', 'product_variants.id=quote_items.option_id', 'left')
        ->join('tax_rates', 'tax_rates.id=quote_items.tax_rate_id', 'left')
        ->group_by('quote_items.id')
        ->order_by('id', 'asc');
    $q = $this->db->get_where('quote_items', array('quote_id' => $quote_id));
    
    if ($q->num_rows() > 0) {
        foreach (($q->result()) as $row) {
            // Lấy custom fields cho item này
            $custom_fields_query = $this->db
                ->select('qcc.column_name, qicf.value')
                ->from('quote_item_custom_fields qicf')
                ->join('quote_custom_columns qcc', 'qcc.id = qicf.column_id')
                ->where('qicf.quote_item_id', $row->id)
                ->order_by('qcc.column_order', 'ASC')
                ->get();
            
            // ✅ QUAN TRỌNG: Lưu theo format gốc (không có prefix)
            $row->custom_fields = array();
            if ($custom_fields_query->num_rows() > 0) {
                foreach ($custom_fields_query->result() as $cf) {
                    // Lưu theo tên cột gốc: "Rộng", "Dài", "Tường"...
                    $row->custom_fields[$cf->column_name] = $cf->value;
                }
            }
            
            $data[] = $row;
        }
        return $data;
    }
    return FALSE;
}

    public function addQuote($data = array(), $items = array())
{
    if ($this->db->insert('quotes', $data)) {
        $quote_id = $this->db->insert_id();
        if ($this->site->getReference('qu') == $data['reference_no']) {
            $this->site->updateReference('qu');
        }
        
        foreach ($items as $item) {
    // Lưu custom_fields và notes trước khi insert
    $custom_fields = isset($item['custom_fields']) ? $item['custom_fields'] : array();
    $notes = isset($item['notes']) ? $item['notes'] : ''; // ← THÊM DÒNG NÀY
    unset($item['custom_fields']);
    unset($item['notes']); // ← THÊM DÒNG NÀY
            
            $item['quote_id'] = $quote_id;
            $this->db->insert('quote_items', $item);
            $item_id = $this->db->insert_id();
             if (!empty($notes)) { // ← THÊM KHỐI NÀY
        $this->db->update('quote_items', array('notes' => $notes), array('id' => $item_id));
    }
            // Lưu custom fields nếu có
            if (!empty($custom_fields)) {
                $columns = $this->db->order_by('column_order', 'ASC')->get('quote_custom_columns')->result();
                foreach ($columns as $col) {
                    if (isset($custom_fields[$col->column_name])) {
                        $cf_data = array(
                            'quote_item_id' => $item_id,
                            'column_id' => $col->id,
                            'value' => $custom_fields[$col->column_name]
                        );
                        $this->db->insert('quote_item_custom_fields', $cf_data);
                    }
                }
            }
        }
        return true;
    }
    return false;
}


    public function updateQuote($id, $data, $items = array())
{
    if ($this->db->update('quotes', $data, array('id' => $id))) {
        // Lấy danh sách quote_item_id cũ để xóa custom fields
        $old_items = $this->db->select('id')->get_where('quote_items', array('quote_id' => $id))->result();
        foreach ($old_items as $old_item) {
            $this->db->delete('quote_item_custom_fields', array('quote_item_id' => $old_item->id));
        }
        
        // Xóa quote items cũ
        $this->db->delete('quote_items', array('quote_id' => $id));
        
        // Insert lại items mới
        foreach ($items as $item) {
    // Lưu custom_fields và notes trước khi insert
    $custom_fields = isset($item['custom_fields']) ? $item['custom_fields'] : array();
    $notes = isset($item['notes']) ? $item['notes'] : ''; // ← THÊM DÒNG NÀY
    unset($item['custom_fields']);
    unset($item['notes']); // ← THÊM DÒNG NÀY
            
            $item['quote_id'] = $id;
            $this->db->insert('quote_items', $item);
            $item_id = $this->db->insert_id();
             if (!empty($notes)) { // ← THÊM KHỐI NÀY
        $this->db->update('quote_items', array('notes' => $notes), array('id' => $item_id));
    }
            // Lưu custom fields nếu có
            if (!empty($custom_fields)) {
                $columns = $this->db->order_by('column_order', 'ASC')->get('quote_custom_columns')->result();
                foreach ($columns as $col) {
                    if (isset($custom_fields[$col->column_name])) {
                        $cf_data = array(
                            'quote_item_id' => $item_id,
                            'column_id' => $col->id,
                            'value' => $custom_fields[$col->column_name]
                        );
                        $this->db->insert('quote_item_custom_fields', $cf_data);
                    }
                }
            }
        }
        return true;
    }
    return false;
}

    public function updateStatus($id, $status, $note)
    {
        if ($this->db->update('quotes', array('status' => $status, 'note' => $note), array('id' => $id))) {
            return true;
        }
        return false;
    }


    public function deleteQuote($id)
{
    // Lấy danh sách quote_item_id để xóa custom fields
    $items = $this->db->select('id')->get_where('quote_items', array('quote_id' => $id))->result();
    foreach ($items as $item) {
        $this->db->delete('quote_item_custom_fields', array('quote_item_id' => $item->id));
    }
    
    if ($this->db->delete('quote_items', array('quote_id' => $id)) && $this->db->delete('quotes', array('id' => $id))) {
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
    // THÊM VÀO CUỐI FILE, TRƯỚC DẤU ĐÓNG }

public function getCustomColumns()
{
    $q = $this->db->order_by('column_order', 'ASC')->get('quote_custom_columns');
    if ($q->num_rows() > 0) {
        return $q->result();
    }
    return FALSE;
}

public function saveCustomColumns($columns = array())
{
    // Xóa tất cả cột cũ
    $this->db->truncate('quote_custom_columns');
    
    // Insert cột mới
    foreach ($columns as $index => $col_name) {
        if (!empty($col_name)) {
            $data = array(
                'column_name' => $col_name,
                'column_order' => $index
            );
            $this->db->insert('quote_custom_columns', $data);
        }
    }
    return true;
}
}
