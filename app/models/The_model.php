<?php defined('BASEPATH') or exit('No direct script access allowed');

class The_model extends CI_Model
{

    public function __construct()
    {
        parent::__construct();
    }

   
    public function getTheByID($id)
    {
        $q = $this->db->get_where('payment_the', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    public function getSoDuDauKy($id)
    {
        $q = $this->db->get_where('payments', array('c_dauky_doitac' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row()->amount;
        }
        return 0;
    }
    public function addThe($data = array(),$payment=null)
    {
        
        if ($this->db->insert('payment_the', $data)) {
            $doitac_id = $this->db->insert_id();    
            if ($payment) {

                $payment['c_dauky_doitac']=$doitac_id;     
                $payment['c_dauky_doitac']=$id;    
                if ((int)$payment['warehouse_id']==0) {
                    $_warehouse_id=$this->session->userdata('warehouse_id');
                    if ($_warehouse_id==null) {
                        $_warehouse_id=$this->Settings->default_warehouse;
                    }
                    $payment['warehouse_id']=$_warehouse_id;
                } 
                if ($this->db->insert('payments', $payment)) {       
                // them khoan thu dau ky   
                    if ($this->site->getReference('thu') == $payment['reference_no']) {
                        $this->site->updateReference('thu');
                    }          
                }    
            }       
            return true;
        }
        return false;
    }

    public function updateThe($id, $data = array(),$payment=null)
    {
               
        if ($this->db->update('payment_the', $data, array('id' => $id))) {

            if ($payment) {
                if ($this->db->delete('payments', array('c_dauky_doitac' => $id))) {                
                //xoa khoan thu dau ky truoc do
                }
                $payment['c_dauky_doitac']=$id;    
                if ((int)$payment['warehouse_id']==0) {
                    $_warehouse_id=$this->session->userdata('warehouse_id');
                    if ($_warehouse_id==null) {
                        $_warehouse_id=$this->Settings->default_warehouse;
                    }
                    $payment['warehouse_id']=$_warehouse_id;
                } 

                if ($this->db->insert('payments', $payment)) {    
                // them khoan thu dau ky    
                    if ($this->site->getReference('thu') == $payment['reference_no']) {
                        $this->site->updateReference('thu');
                    }             
                }    
            }       
            return true;
        }

        return false;
    }

    public function deleteThe($id)
    {
    
        if ($this->db->delete('payment_the', array('id' => $id,'id>'=>6))) {
            if ($this->db->delete('payments', array('c_dauky_doitac' => $id))) {                
            //xoa khoan thu dau ky truoc do
            }
            return true;            
        }
        return false;
    }
     public function getTheSuggestions($term, $limit = 10)
    {
        $this->db->select("id, (CASE WHEN code != '' THEN CONCAT(code,'-',name) ELSE CONCAT(sotk, '-',name) END) as text", FALSE);
        $this->db->where(" (id LIKE '%" . $term . "%' OR name LIKE '%" . $term . "%' OR code LIKE '%" . $term . "%' OR sotk LIKE '%" . $term . "%' OR note LIKE '%" . $term . "%') ");
        $q = $this->db->get('payment_the', $limit);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }

            return $data;
        }
    }
}
