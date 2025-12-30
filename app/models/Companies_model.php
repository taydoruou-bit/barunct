<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Companies_model extends CI_Model
{

    public function __construct()
    {
        parent::__construct();
    }

    public function getAllBillerCompanies()
    {
        $q = $this->db->get_where('companies', array('group_name' => 'biller'));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
public function getCompanyByPhoneById($phone='',$id=0)
    {
        $q = $this->db->get_where('companies', array('phone' => $phone,'id!=' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    public function getAllCustomerCompanies()
    {
        $q = $this->db->get_where('companies', array('group_name' => 'customer'));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getAllSupplierCompanies()
    {
        $q = $this->db->get_where('companies', array('group_name' => 'supplier'));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getAllCustomerGroups()
    {
        $q = $this->db->order_by('name', 'ASC')->get('customer_groups');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getCompanyUsers($company_id)
    {
        $q = $this->db->get_where('users', array('company_id' => $company_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getCompanyByID($id)
    {
        $q = $this->db->get_where('companies', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
	
	 public function getNhanvienByID($id)
    {
        $q = $this->db->get_where('users', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    public function getCompanyByEmail($email)
    {
        $q = $this->db->get_where('companies', array('email' => $email), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    public function getCompanyByPhone($phone='')
    {
        $q = $this->db->get_where('companies', array('phone' => $phone), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function addCompany($data = array())
    {
        if ($this->db->insert('companies', $data)) {
            $cid = $this->db->insert_id();
            return $cid;
        }
        return false;
    }

    public function updateCompany($id, $data = array())
    {
        $this->db->where('id', $id);
        if ($this->db->update('companies', $data)) {
            return true;
        }
        return false;
    }

    public function addCompanies($data = array())
    {
        if ($this->db->insert_batch('companies', $data)) {
            return true;
        }
        return false;
    }
    
    public function deleteCustomer($id)
    {
        if ($this->getCustomerSales($id)) {
            return false;
        }
        if ($this->db->delete('companies', array('id' => $id, 'group_name' => 'customer')) && $this->db->delete('users', array('company_id' => $id))) {
            return true;
        }
        return FALSE;
    }

    public function deleteSupplier($id)
    {
        if ($this->getSupplierPurchases($id)) {
            return false;
        }
        if ($this->db->delete('companies', array('id' => $id, 'group_name' => 'supplier')) && $this->db->delete('users', array('company_id' => $id))) {
            return true;
        }
        return FALSE;
    }

    public function deleteBiller($id)
    {
        if ($this->getBillerSales($id)) {
            return false;
        }
        if ($this->db->delete('companies', array('id' => $id, 'group_name' => 'biller'))) {
            return true;
        }
        return FALSE;
    }

    public function getBillerSuggestions($term, $limit = 10)
    {
        $this->db->select("id, company as text");
        $this->db->where(" (id LIKE '%" . $term . "%' OR name LIKE '%" . $term . "%' OR company LIKE '%" . $term . "%') ");
        $q = $this->db->get_where('companies', array('group_name' => 'biller'), $limit);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }

            return $data;
        }
    }

    public function getCustomerSuggestions($term, $limit = 10)
    {
        $this->db->select("id, (CASE WHEN company = '-' THEN CONCAT(name,'-',phone) ELSE CONCAT(name, '-',phone) END) as text", FALSE);
        $this->db->where(" (id LIKE '%" . $term . "%' OR name LIKE '%" . $term . "%' OR company LIKE '%" . $term . "%' OR email LIKE '%" . $term . "%' OR phone LIKE '%" . $term . "%') ");
        $q = $this->db->get_where('companies', array('group_name' => 'customer'), $limit);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }

            return $data;
        }
    }
	 public function getCustomerSuggestionsNhanvien($term, $limit = 10)
    {
        $this->db->select("id, CONCAT((CASE WHEN company = '-' THEN CONCAT(first_name,' ',last_name) ELSE CONCAT(first_name, ' ',last_name) END), '-',phone) as text", FALSE);
        $this->db->where(" (id='%" . $term ."%' OR first_name LIKE '%" . $term . "%' OR last_name LIKE '%" . $term . "%' OR company LIKE '%" . $term . "%' OR email LIKE '%" . $term . "%' OR phone LIKE '%" . $term . "%') ");
        $q = $this->db->get_where('users',null, $limit);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }

            return $data;
        }
    }

    public function getSupplierSuggestions($term, $limit = 10)
    {
        $this->db->select("id, (CASE WHEN phone != '' THEN name ELSE CONCAT(name, ' - ', phone) END) as text", FALSE);
        $this->db->where(" (id LIKE '%" . $term . "%' OR name LIKE '%" . $term . "%' OR company LIKE '%" . $term . "%' OR email LIKE '%" . $term . "%' OR phone LIKE '%" . $term . "%') ");
        $q = $this->db->get_where('companies', array('group_name' => 'supplier'), $limit);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }

            return $data;
        }
    }

    public function getCustomerSales($id)
    {
        $this->db->where('customer_id', $id)->from('sales');
        return $this->db->count_all_results();
    }

    public function getBillerSales($id)
    {
        $this->db->where('biller_id', $id)->from('sales');
        return $this->db->count_all_results();
    }

    public function getSupplierPurchases($id)
    {
        $this->db->where('supplier_id', $id)->from('purchases');
        return $this->db->count_all_results();
    }

    public function addDeposit($data, $cdata)
    {
        if ($this->db->insert('deposits', $data) && 
            $this->db->update('companies', $cdata, array('id' => $data['company_id']))) {
            return true;
        }
        return false;
    }
    public function addDepositV2($data, $cdata)
    {
        $check=$this->db->insert('deposits', $data);
        $add_id=$this->db->insert_id();
        if ($check && 
            $this->db->update('companies', $cdata, array('id' => $data['company_id']))) {
            return $add_id;
        }
        return false;
    }
    public function updateDeposit($id, $data, $cdata)
    {
        if ($this->db->update('deposits', $data, array('id' => $id)) && 
            $this->db->update('companies', $cdata, array('id' => $data['company_id']))) {
            return true;
        }
        return false;
    }

    public function getDepositByID($id)
    {
        $q = $this->db->get_where('deposits', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function deleteDeposit($id)
    {
        $deposit = $this->getDepositByID($id);
        $company = $this->getCompanyByID($deposit->company_id);
        $cdata = array(
                'deposit_amount' => ($company->deposit_amount-$deposit->amount)
            );
        if ($this->db->update('companies', $cdata, array('id' => $deposit->company_id)) &&
            $this->db->delete('deposits', array('id' => $id))) {
            return true;
        }
        return false;
    }
    public function getPaymentByDepositID($id)
    {
        $q = $this->db->get_where('payments', array('tiencoc_id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    public function getAllPriceGroups()
    {
        $q = $this->db->order_by('name', 'ASC')->get('price_groups');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getCompanyAddresses($company_id)
    {
        $q = $this->db->get_where('addresses', array('company_id' => $company_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function addAddress($data)
    {
        if ($this->db->insert('addresses', $data)) {
            return true;
        }
        return false;
    }

    public function updateAddress($id, $data)
    {
        if ($this->db->update('addresses', $data, array('id' => $id))) {
            return true;
        }
        return false;
    }

    public function deleteAddress($id)
    {
        if ($this->db->delete('addresses', array('id' => $id))) {
            return true;
        }
        return false;
    }

    public function getAddressByID($id)
    {
        $q = $this->db->get_where('addresses', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
     public function addDocan($data = array())
    {
        if ($this->db->insert('docan', $data)) {
            $cid = $this->db->insert_id();
            return $cid;
        }
        return false;
    }

    public function updateDocan($id=0,$customer_id=0, $data = array())
    {
        $this->db->where('id', $id);
        $this->db->where('customer_id', $customer_id);
        if ($this->db->update('docan', $data)) {
            return true;
        }
        return false;
    }
    public function getAllDoCan($customer_id=0)
    {
        $this->db->select('docan.*,CONCAT(scodeweb_users.first_name," ",scodeweb_users.last_name) as nv')
            ->join('users', 'users.id=docan.created_by', 'left')
            ->order_by('id', 'desc');        
        $this->db->where('customer_id', $customer_id);        
        $q = $this->db->get('docan');

        $html='';
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                //tien hanh load san pham by date and customer_id    
                $ngay=date("d/m/Y",strtotime($row->created));   
                $table=$this->getAllInvoiceItemsByDate($row->order_id,$customer_id);

                $html.=' <div class="table-responsive"><table class="table table-bordered table-condensed table-hover table-striped tbl-docan">
                    <thead>
                        <tr>
                            <th>Ngày</th>
                            <th>Loai</th>
                            <th>Cận</th>
                            <th>Viễn</th>
                            <th>Loạn</th>
                            <th>ADD (Người lớn tuổi)</th>
                            <th>AX (Trục mắt)</th>  
                            <th>PD</th>     
                            <th>#</th>                                 
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td rowspan="'.(count($table)+3).'">'.$ngay.' <br> ('.$row->nv.')</td>
                            <td>Mắt phải</td>
                            <td>'.$row->canmp.'</td>
                            <td>'.$row->vienmp.'</td>
                            <td>'.$row->loanmp.'</td>
                            <td>'.$row->addmp.'</td>
                            <td>'.$row->tmmp.'</td>
                            <td rowspan="2">'.$row->dp.'</td>  
                            <th rowspan="'.(count($table)+3).'">
                                <button class="btn btn-small btn-dn-fc" onclick="suadocan(\''.$row->id.'\',\''.$row->canmp.'\',\''.$row->vienmp.'\',\''.$row->loanmp.'\',\''.$row->addmp.'\',\''.$row->tmmp.'\',\''.$row->canmt.'\',\''.$row->vienmt.'\',\''.$row->loanmt.'\',\''.$row->addmt.'\',\''.$row->tmmt.'\',\''.$row->dp.'\')"><i class="fa fa-edit"></i>Sửa</button>
                                <button class="btn btn-small btn-dn-fc" onclick="xoadocan('.$row->id.')"><i class="fa fa-remove"></i>Xóa</button>
                            </th>                              
                        </tr>
                        <tr>
                            <td>Mắt trái</td>
                            <td>'.$row->canmt.'</td>
                            <td>'.$row->vienmt.'</td>
                            <td>'.$row->loanmt.'</td>
                            <td>'.$row->addmt.'</td>
                            <td>'.$row->tmmt.'</td>                                                           
                        </tr>';

                    

                        
                        
                        if ($table) {
                            $html.='<tr class="trsanpham">
                                    <td colspan="3">Sản phẩm</td>
                                    <td>Số lượng</td>
                                    <td>Giá bán</td>
                                    <td>Thành tiền</td>                                                            
                                    <td>Nhân viên</td>                                                            
                                </tr>';                                
                            foreach ($table as $tr) {
                                $html.='<tr>
                                    <td colspan="3">'.$tr->product_name.'</td>
                                    <td>'.$this->sma->formatDecimal($tr->unit_quantity,0).'</td>
                                    <td>'.$this->sma->formatMoney($tr->unit_price).'</td>
                                    <td>'.$this->sma->formatMoney($tr->subtotal).'</td>                                                            
                                    <td>'.$tr->created_by.'</td>                                                            
                                </tr>';
                            }
                        }

                $html.='</tbody>
                </table></div>';
            }
            return $html;
        }
        return '';
    }
    public function getAllInvoiceItemsByDate($order_id=NULL, $customer_id = NULL)
    {
        $this->db->select('sale_items.*, products.code as prd_code, products.name as prd_name,CONCAT(scodeweb_users.first_name," ",scodeweb_users.last_name) as created_by')
            ->join('products', 'products.id=sale_items.product_id', 'left')
            ->join('sales', 'sales.id=sale_items.sale_id', 'left')
            ->join('users', 'users.id=sales.created_by', 'left')
            ->group_by('sale_items.id')
            ->order_by('id', 'desc');
        $this->db->where('sales.id',$order_id);
        $this->db->where('customer_id', $customer_id);
        
        $q = $this->db->get('sale_items');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
    public function deleteDocan($customer_id=0,$id=0)
    {
        if ($this->db->delete('docan', array('id' => $id, 'customer_id' => $customer_id))) {
            return true;
        }
        return FALSE;
    }

}
