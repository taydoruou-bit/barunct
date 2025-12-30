<?php defined('BASEPATH') or exit('No direct script access allowed');

class Doitac_model extends CI_Model
{

    public function __construct()
    {
        parent::__construct();
    }

   
    public function getDoitacByID($id)
    {
        $q = $this->db->get_where('doitac', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function addDoitac($data = array())
    {
		
        if ($this->db->insert('doitac', $data)) {
            $return_id = $this->db->insert_id();			
            return true;
        }
        return false;
    }

    public function updateDoitac($id, $data = array())
    {
		       
        if ($this->db->update('doitac', $data, array('id' => $id))) {
            			
            return true;
        }

        return false;
    }

    public function deleteDoitac($id)
    {
	
        if ($this->db->delete('doitac', array('id' => $id))) {
			
			return true;			
        }
        return false;
    }
	 public function getDoitacSuggestions($term, $limit = 10) 
    {
        $this->db->select("id, (CASE WHEN dienthoai != '' THEN CONCAT(name,'-',dienthoai) ELSE CONCAT(name, '-',code) END) as text", FALSE);
        $this->db->where(" (id LIKE '%" . $term . "%' OR name LIKE '%" . $term . "%' OR code LIKE '%" . $term . "%' OR email LIKE '%" . $term . "%' OR dienthoai LIKE '%" . $term . "%') ");
        $q = $this->db->get('doitac', $limit);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }

            return $data;
        }
    }
}
