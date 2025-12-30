<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Customers extends MY_Controller
{

    function __construct()
    {
        parent::__construct();

        if (!$this->loggedIn) {
            $this->session->set_userdata('requested_page', $this->uri->uri_string());
            $this->sma->md('login');
        }
        if ($this->Customer || $this->Supplier) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER["HTTP_REFERER"]);
        }
        $this->lang->load('customers', $this->Settings->user_language);
        $this->load->library('form_validation');
        $this->load->model('companies_model');
        $this->load->model('sales_model');
        $this->load->model('purchases_model');
        $this->load->model('reports_model');
        $this->data['pb'] = $this->site->getAllPTTT();
               
    }

    function index($action = NULL)
    {
        $this->sma->checkPermissions();

        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['action'] = $action;
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('customers')));
        $meta = array('page_title' => lang('customers'), 'bc' => $bc);
        $this->page_construct('customers/index', $meta, $this->data);
    }

    function getCustomers()
    {
        $this->sma->checkPermissions('index');
        $this->load->library('datatables');
        $this->datatables
            ->select("id, company, name, email, phone, price_group_name, customer_group_name, vat_no, deposit_amount, award_points,(SELECT COALESCE(sum(grand_total), 0) FROM {$this->db->dbprefix('sales')} WHERE customer_id={$this->db->dbprefix('companies')}.id AND sale_status!='returned') + nobandau - (SELECT COALESCE(sum(paid), 0) FROM {$this->db->dbprefix('sales')} WHERE customer_id={$this->db->dbprefix('companies')}.id AND sale_status!='returned') - (select COALESCE(sum(amount), 0) from scodeweb_payments WHERE id_ncc_id_kh={$this->db->dbprefix('companies')}.id and type='received' AND amount>0) as duno")
            ->from("companies")
            ->where('group_name', 'customer')
            ->add_column("Actions", "<div class=\"text-center\"><a class=\"tip\" title='" . lang("list_deposits") . "' href='" . site_url('customers/deposits/$1') . "' data-toggle='modal' data-target='#myModal'><i class=\"fa fa-money\"></i></a> <a class=\"tip\" title='" . lang("add_deposit") . "' href='" . site_url('customers/add_deposit/$1') . "' data-toggle='modal' data-target='#myModal'><i class=\"fa fa-plus\"></i></a>  <a class=\"tip\" title='" . lang("edit_customer") . "' href='" . site_url('customers/edit/$1') . "' data-toggle='modal' data-target='#myModal'><i class=\"fa fa-edit\"></i></a> <a href='#' class='tip po' title='<b>" . lang("delete_customer") . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('customers/delete/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></div>", "id");
        //->unset_column('id');
        echo $this->datatables->generate();
    }

    

    function add()
    {
        //$this->sma->checkPermissions(false, true);

       // $this->form_validation->set_rules('email', lang("email_address"), 'required');
        $this->form_validation->set_rules('name', $this->lang->line("name"), 'required');
        if ($this->form_validation->run('companies/add') == true) {
            $cg = $this->site->getCustomerGroupByID($this->input->post('customer_group'));
            $pg = $this->site->getPriceGroupByID($this->input->post('price_group'));
            $email= $this->input->post('email');
            if($email==""){
                $email=$this->input->post('phone')."@donghetuchon.com";
            }
            if ($this->companies_model->getCompanyByPhone($this->input->post('phone'))!=false) {
                $this->session->set_flashdata('error', lang("Số điện thoai đã tồn tại") . " (" . $this->input->post('phone') . "). " . lang("customer_already_exist"));
                redirect("customers");
            }
            $ngaysinh=$this->input->post('ngaysinh');
            //convert ngay sinh mm/dd/yyyy
             $ngaysinh=str_replace("/","-", $ngaysinh);
            $ngaysinh=Date("Y-m-d",strtotime($ngaysinh));

            $data = array('name' => $this->input->post('name'),
                'email' => $email,
                'group_id' => '3',
                'group_name' => 'customer',
                'customer_group_id' => $this->input->post('customer_group'),
                'customer_group_name' => $cg->name,
                'price_group_id' => $this->input->post('price_group') ? $this->input->post('price_group') : NULL,
                'price_group_name' => $this->input->post('price_group') ? $pg->name : NULL,
                'company' => $this->input->post('company'),
                'address' => $this->input->post('address'),
                'vat_no' => $this->input->post('vat_no'),
                'city' => $this->input->post('city'),
                'state' => $this->input->post('state'),
                'postal_code' => $this->input->post('postal_code'),
                'country' => $this->input->post('country'),
                'phone' => $this->input->post('phone'),
                'loaikhach' => (int)$this->input->post('loaikhach'),
                'facebook' => $this->input->post('facebook'),
                'gioitinh' => (int)$this->input->post('gioitinh'),
                'ngaysinh' => $ngaysinh,
                'ghichu' => $this->input->post('ghichu'),
                'cf1' => $this->input->post('cf1'),
                'cf2' => $this->input->post('cf2'),
                'nobandau' => $this->input->post('nobandau'),
            );
        } elseif ($this->input->post('add_customer')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('customers');
        }
        
        if ($this->form_validation->run() == true && $cid = $this->companies_model->addCompany($data)) {
            $this->session->set_flashdata('message', lang("customer_added"));
            $ref = isset($_SERVER["HTTP_REFERER"]) ? explode('?', $_SERVER["HTTP_REFERER"]) : NULL;
            redirect($ref[0] . '?customer=' . $cid);
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->data['customer_groups'] = $this->companies_model->getAllCustomerGroups();
            $this->data['price_groups'] = $this->companies_model->getAllPriceGroups();
            $this->load->view($this->theme . 'customers/add', $this->data);
            $this->session->set_flashdata('error', validation_errors());
           // redirect('customers'); 
        }
    }

    function editloi($id = NULL)
    {
        $this->sma->checkPermissions(false, true);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        $company_details = $this->companies_model->getCompanyByID($id);
        if ($this->input->post('email') != $company_details->email) {
           $this->form_validation->set_rules('code', lang("email_address"), 'is_unique[companies.email]');
        }
    
        if (!empty($_POST)) {
            $cg = $this->site->getCustomerGroupByID($this->input->post('customer_group'));
            $pg = $this->site->getPriceGroupByID($this->input->post('price_group'));
            $email= $this->input->post('email');
            if($email==""){
                $email=$this->input->post('phone')."@donghetuchon.com";
            }
            $data = array('name' => $this->input->post('name'),
                'email' => $email,
                'group_id' => '3',
                'group_name' => 'customer',
                'customer_group_id' => $this->input->post('customer_group'),
                'customer_group_name' => $cg->name,
                'price_group_id' => $this->input->post('price_group') ? $this->input->post('price_group') : NULL,
                'price_group_name' => $this->input->post('price_group') ? $pg->name : NULL,
                'company' => $this->input->post('company'),
                'address' => $this->input->post('address'),
                'vat_no' => $this->input->post('vat_no'),
                'city' => $this->input->post('city'),
                'state' => $this->input->post('state'),
                'postal_code' => $this->input->post('postal_code'),
                'country' => $this->input->post('country'),
                'phone' => $this->input->post('phone'),
                'cf1' => $this->input->post('cf1'),
                'cf2' => $this->input->post('cf2'),
                
                'nobandau' => $this->input->post('nobandau'),
                'award_points' => $this->input->post('award_points'),
            );
            
        } elseif ($this->input->post('edit_customer')) {
            $this->session->set_flashdata('error', validation_errors());            
            redirect($_SERVER["HTTP_REFERER"]);
        }
        
        if (!empty($_POST) && $this->companies_model->updateCompany($id, $data)) {
            $this->session->set_flashdata('message', lang("customer_updated"));
            redirect($_SERVER["HTTP_REFERER"]);
        } else {
            
            $this->data['customer'] = $company_details;
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->data['customer_groups'] = $this->companies_model->getAllCustomerGroups();
            $this->data['price_groups'] = $this->companies_model->getAllPriceGroups();
            $this->load->view($this->theme . 'customers/edit', $this->data);
        }
    }
    function edit($id = NULL)
    {
        $this->sma->checkPermissions(false, true);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        $company_details = $this->companies_model->getCompanyByID($id);
        if ($this->input->post('email') != $company_details->email) {
            $this->form_validation->set_rules('email', lang("email_address"), 'is_unique[companies.email]');
        }
        if ($this->input->post('phone') != $company_details->phone) {
            if ($this->companies_model->getCompanyByPhoneById($this->input->post('phone'),$id)) {
                $this->session->set_flashdata('error', lang("Số điện thoai đã tồn tại") . " (" . $this->input->post('phone') . "). " . lang("customer_already_exist"));
                redirect("customers");
            }
        }
        $this->form_validation->set_rules('name', $this->lang->line("name"), 'required');
        if ($this->form_validation->run('companies/add') == true) {
            $cg = $this->site->getCustomerGroupByID($this->input->post('customer_group'));
            $pg = $this->site->getPriceGroupByID($this->input->post('price_group'));
            $email= $this->input->post('email');
            if($email==""){
                $email=$this->input->post('phone')."@donghetuchon.com";
            }
             $ngaysinh=$this->input->post('ngaysinh');
             
             $ngaysinh=str_replace("/","-", $ngaysinh);
            //convert ngay sinh mm/dd/yyyy
            $ngaysinh=Date("Y-m-d",strtotime($ngaysinh));
            $data = array('name' => $this->input->post('name'),
                'email' => $email,
                'group_id' => '3',
                'group_name' => 'customer',
                'customer_group_id' => $this->input->post('customer_group'),
                'customer_group_name' => $cg->name,
                'price_group_id' => $this->input->post('price_group') ? $this->input->post('price_group') : NULL,
                'price_group_name' => $this->input->post('price_group') ? $pg->name : NULL,
                'company' => $this->input->post('company'),
                'address' => $this->input->post('address'),
                'vat_no' => $this->input->post('vat_no'),
                'city' => $this->input->post('city'),
                'state' => $this->input->post('state'),
                'postal_code' => $this->input->post('postal_code'),
                'country' => $this->input->post('country'),
                'phone' => $this->input->post('phone'),
                'cf1' => $this->input->post('cf1'),
                'cf2' => $this->input->post('cf2'),
                'loaikhach' => (int)$this->input->post('loaikhach'),
                'facebook' => $this->input->post('facebook'),
                'gioitinh' => (int)$this->input->post('gioitinh'),
                'ngaysinh' => $ngaysinh,
                'ghichu' => $this->input->post('ghichu'),
                'award_points' => $this->input->post('award_points'),
                'nobandau' => $this->input->post('nobandau'),
            );
        } elseif ($this->input->post('edit_customer')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }

        if ($this->form_validation->run() == true && $this->companies_model->updateCompany($id, $data)) {
            $this->session->set_flashdata('message', lang("customer_updated"));
            redirect($_SERVER["HTTP_REFERER"]);
        } else {
            $this->data['customer'] = $company_details;
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->data['customer_groups'] = $this->companies_model->getAllCustomerGroups();
            $this->data['price_groups'] = $this->companies_model->getAllPriceGroups();
            $this->load->view($this->theme . 'customers/edit', $this->data);
        }
    }
    function viewbackup($id = NULL)
    {
        $this->sma->checkPermissions('index', true);
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['customer'] = $company_details=$this->companies_model->getCompanyByID($id);
        $sotuoi=$this->site->getAgeNumber($company_details->ngaysinh);
            
        $this->data['sotuoi'] = $sotuoi;
        $this->load->view($this->theme.'customers/view',$this->data);
    }
    function view($id = NULL)
    {
       // $this->sma->checkPermissions(false, true);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        $company_details = $this->companies_model->getCompanyByID($id);
        if ($this->input->post('email') != $company_details->email) {
            $this->form_validation->set_rules('code', lang("email_address"), 'is_unique[companies.email]');
        }
        $this->form_validation->set_rules('name', $this->lang->line("name"), 'required');
        if ($this->form_validation->run('companies/add') == true) {
            $cg = $this->site->getCustomerGroupByID($this->input->post('customer_group'));
            $pg = $this->site->getPriceGroupByID($this->input->post('price_group'));
            $email= $this->input->post('email');
            if($email==""){
                $email=$this->input->post('phone')."@donghetuchon.com";
            }
             $ngaysinh=$this->input->post('ngaysinh');
             
             $ngaysinh=str_replace("/","-", $ngaysinh);
            //convert ngay sinh mm/dd/yyyy
            $ngaysinh=Date("Y-m-d",strtotime($ngaysinh));
            $data = array('name' => $this->input->post('name'),
                'email' => $email,
                'group_id' => '3',
                'group_name' => 'customer',
                'customer_group_id' => $this->input->post('customer_group'),
                'customer_group_name' => $cg->name,
                'price_group_id' => $this->input->post('price_group') ? $this->input->post('price_group') : NULL,
                'price_group_name' => $this->input->post('price_group') ? $pg->name : NULL,
                'company' => $this->input->post('company'),
                'address' => $this->input->post('address'),
                'vat_no' => $this->input->post('vat_no'),
                'city' => $this->input->post('city'),
                'state' => $this->input->post('state'),
                'postal_code' => $this->input->post('postal_code'),
                'country' => $this->input->post('country'),
                'phone' => $this->input->post('phone'),
                'cf1' => $this->input->post('cf1'),
                'cf2' => $this->input->post('cf2'),
                'loaikhach' => (int)$this->input->post('loaikhach'),
                'facebook' => $this->input->post('facebook'),
                'gioitinh' => (int)$this->input->post('gioitinh'),
                'ngaysinh' => $ngaysinh,
                'ghichu' => $this->input->post('ghichu'),
                'award_points' => $this->input->post('award_points'),
                'nobandau' => $this->input->post('nobandau'),
            );
        } elseif ($this->input->post('edit_customer')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }

        if ($this->form_validation->run() == true && $this->companies_model->updateCompany($id, $data)) {
            $this->session->set_flashdata('message', lang("customer_updated"));
            redirect($_SERVER["HTTP_REFERER"]);
        } else {
            $this->data['customer'] = $company_details;
            $sotuoi=$this->site->getAgeNumber($company_details->ngaysinh);
             $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['sotuoi'] = $sotuoi;
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->data['customer_groups'] = $this->companies_model->getAllCustomerGroups();
            $this->data['price_groups'] = $this->companies_model->getAllPriceGroups();
            $this->load->view($this->theme . 'customers/docan', $this->data);
        }
    }
    function users($company_id = NULL)
    {
        $this->sma->checkPermissions(false, true);

        if ($this->input->get('id')) {
            $company_id = $this->input->get('id');
        }


        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['modal_js'] = $this->site->modal_js();
        $this->data['company'] = $this->companies_model->getCompanyByID($company_id);
        $this->data['users'] = $this->companies_model->getCompanyUsers($company_id);
        $this->load->view($this->theme . 'customers/users', $this->data);

    }

    function add_user($company_id = NULL)
    {
        $this->sma->checkPermissions(false, true);

        if ($this->input->get('id')) {
            $company_id = $this->input->get('id');
        }
        $company = $this->companies_model->getCompanyByID($company_id);

        $this->form_validation->set_rules('email', lang("email_address"), 'is_unique[users.email]');
        $this->form_validation->set_rules('password', lang('password'), 'required|min_length[8]|max_length[20]|matches[password_confirm]');
        $this->form_validation->set_rules('password_confirm', lang('confirm_password'), 'required');

        if ($this->form_validation->run('companies/add_user') == true) {
            $active = $this->input->post('status');
            $notify = $this->input->post('notify');
            list($username, $domain) = explode("@", $this->input->post('email'));
            $email = strtolower($this->input->post('email'));
            $password = $this->input->post('password');
            $additional_data = array(
                'first_name' => $this->input->post('first_name'),
                'last_name' => $this->input->post('last_name'),
                'phone' => $this->input->post('phone'),
                'gender' => $this->input->post('gender'),
                'company_id' => $company->id,
                'company' => $company->company,
                'group_id' => 3
            );
            $this->load->library('ion_auth');
        } elseif ($this->input->post('add_user')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('customers');
        }

        if ($this->form_validation->run() == true && $this->ion_auth->register($username, $password, $email, $additional_data, $active, $notify)) {
            $this->session->set_flashdata('message', lang("user_added"));
            redirect("customers");
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->data['company'] = $company;
            $this->load->view($this->theme . 'customers/add_user', $this->data);
        }
    }

    function import_csv()
    {
        $this->sma->checkPermissions('add', true);
        $this->load->helper('security');
        $this->form_validation->set_rules('csv_file', lang("upload_file"), 'xss_clean');

        if ($this->form_validation->run() == true) {

            if (DEMO) {
                $this->session->set_flashdata('warning', lang("disabled_in_demo"));
                redirect($_SERVER["HTTP_REFERER"]);
            }

            if (isset($_FILES["csv_file"])) /* if($_FILES['userfile']['size'] > 0) */ {

                $this->load->library('upload');

                $config['upload_path'] = 'assets/uploads/csv/';
                $config['allowed_types'] = 'csv';
                $config['max_size'] = '2000';
                $config['overwrite'] = FALSE;
                $config['encrypt_name'] = TRUE;

                $this->upload->initialize($config);

                if (!$this->upload->do_upload('csv_file')) {

                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect("customers");
                }

                $csv = $this->upload->file_name;

                $arrResult = array();
                $handle = fopen("assets/uploads/csv/" . $csv, "r");
                if ($handle) {
                    while (($row = fgetcsv($handle, 5001, ",")) !== FALSE) {
                        $arrResult[] = $row;
                    }
                    fclose($handle);
                }
                $titles = array_shift($arrResult);

                $keys = array('company', 'name', 'email', 'phone', 'address', 'city', 'state', 'postal_code', 'country', 'vat_no', 'cf1', 'cf2', 'cf3', 'cf4', 'cf5', 'cf6');

                $final = array();
                foreach ($arrResult as $key => $value) {
                    $final[] = array_combine($keys, $value);
                }
                $rw = 2;
                foreach ($final as $csv) {
                    if ($this->companies_model->getCompanyByEmail($csv['email'])) {
                        $this->session->set_flashdata('error', lang("check_customer_email") . " (" . $csv['email'] . "). " . lang("customer_already_exist") . " (" . lang("line_no") . " " . $rw . ")");
                        redirect("customers");
                    }
                    $rw++;
                }
                foreach ($final as $record) {
                    $record['group_id'] = 3;
                    $record['group_name'] = 'customer';
                    $record['customer_group_id'] = 1;
                    $record['customer_group_name'] = 'General';
                    $data[] = $record;
                }
                //$this->sma->print_arrays($data);
            }

        } elseif ($this->input->post('import')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('customers');
        }

        if ($this->form_validation->run() == true && !empty($data)) {
            if ($this->companies_model->addCompanies($data)) {
                $this->session->set_flashdata('message', lang("customers_added"));
                redirect('customers');
            }
        } else {

            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'customers/import', $this->data);
        }
    }
     function import_xls()
    {
        $this->sma->checkPermissions('add', true);
        $this->load->helper('security');
        $this->form_validation->set_rules('userfile', lang("upload_file"), 'xss_clean');
        require_once APPPATH.'third_party/php_excel_reader/excel_reader2_patch_applied.php';
        
        $docan=[];
        if ($this->form_validation->run() == true) {

           
            if (isset($_FILES["userfile"])) /* if($_FILES['userfile']['size'] > 0) */ {
                $data_last=[];
                $this->load->library('upload');

                $config['upload_path'] = 'assets/uploads/csv/';
                $config['allowed_types'] = 'xls';
                $config['max_size'] = '1024';
                $config['overwrite'] = TRUE;
                $config['encrypt_name'] = TRUE;
                $config['max_filename'] = 25;

                $this->upload->initialize($config);
                
                if (!$this->upload->do_upload()) {

                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    
                    redirect("customers");
                }

                $csv = $this->upload->file_name;

                $arrResult = array();
                
                $csv_file = $_FILES['userfile']['tmp_name'];
                $data = new Spreadsheet_Excel_Reader($csv_file,true,"UTF-8");
                $data->read($csv_file);
                $size = $data->rowcount($sheet_index=0); // lay so hang cua sheet
                $countcol =14;  //$data->colcount($sheet_index=0); // lay so cot cua sheet toi da 19

             $keys = array('loaikhach', 'company', 'vat_no', 'name', 'email', 'phone', 'address', 'ngaysinh', 'gioitinh', 'facebook', 'ghichu', 'cf1', 'cf2','nobandau');          
               for ($i = 1; $i <= $size; $i++) {                
                    
                    $objsp=array();//khoi tao san pham                  
                    $k=0;
                    for ($j = 1; $j <= $countcol; $j++) {
                        $objsp[$keys[$k]]=$data->val($i,$j);    
                        $k++;
                    }                       
                    array_push($arrResult,$objsp);                  
                          
                }//sheet
                

                $titles = array_shift($arrResult);


                

                $final = array();
                foreach ($arrResult as $key => $value) {
                    $final[] = array_combine($keys, $value);
                }
                $final_ok=array();
                $rw = 2;
                foreach ($final as $csv) {
                    if($csv['name']!=''&&trim($csv['name'])!=''){    
                            if (strtolower($csv['loaikhach'])=='canhan'||strtolower($csv['loaikhach'])=='ca nhan'||strtolower($csv['loaikhach'])=='cá nhân') {
                                $csv['loaikhach']=1;
                            }else{
                                $csv['loaikhach']=0;
                            }
                            if (strtolower($csv['gioitinh'])=='nam'||strtolower($csv['gioitinh'])=='con trai') {
                                $csv['gioitinh']=1;
                            }else{
                                $csv['gioitinh']=0;
                            }
                            $ngaysinh=str_replace("/","-", $csv['ngaysinh']);
                            $ngaysinh=Date("Y-m-d",strtotime($ngaysinh));
                            $csv['ngaysinh']=$ngaysinh;

                            if (substr($csv['phone'],0,1)!='0'&&substr($csv['phone'],0,1)!='+') {
                                $csv['phone']="0".$csv['phone'];
                            }
                            if ($csv['email']=='') {
                                $csv['email']=rand(1,99).'_'.$csv['phone']."@donghetuchon.com";
                            }

                            $final_ok[]=$csv;

                            if ($this->companies_model->getCompanyByPhone($csv['phone'])) {
                                $this->session->set_flashdata('error', lang("Số điện thoai đã tồn tại") . " (" . $csv['phone'] . "). " . lang("customer_already_exist") . " (" . lang("line_no") . " " . $rw . ")");
                                redirect("customers");
                            }
                             if ($this->companies_model->getCompanyByEmail($csv['email'])) {
                                $this->session->set_flashdata('error', lang("check_customer_email") . " (" . $csv['email'] . "). " . lang("customer_already_exist") . " (" . lang("line_no") . " " . $rw . ")");
                                redirect("customers");
                            }
                            $rw++;
                    }
                } 
                $indexlhson=0;
                foreach ($final_ok as $record) {
                    $record['group_id'] = 3;
                    $record['group_name'] = 'customer';
                    $record['customer_group_id'] = 1;
                    $record['customer_group_name'] = 'Khách lẻ';
                    $record['price_group_id'] = 1;
                    $record['price_group_name'] = 'Giá bán lẻ';
                    
                    $record['idimport'] = $indexlhson;
                    
                    // $docan[$indexlhson]=array($record['mattrai'],$record['matphai'],$record['dp']);
                    
                    // unset($record['mattrai']);
                    // unset($record['matphai']);
                    // unset($record['dp']);

                    $data_last[] = $record;
                    $indexlhson++;
                }
                
            }

        } elseif ($this->input->post('import')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('customers');
        }

        if ($this->form_validation->run() == true && !empty($data_last)) {
            
            $checkok=0;
            foreach ($data_last as $kh) {
                $add_id=$this->companies_model->addCompany($kh);
                if ($add_id>0) {
                    $idimport=$kh['idimport'];
                    /*
                    $data_docan=$docan[$idimport];
                    $matrai=$data_docan[0];
                    $matphai=$data_docan[1];
                    $dp=$data_docan[2];
                    $ex_mattrai=explode("x",$matrai);
                    $ex_matphai=explode("x",$matphai);
                    
                    $ex_mattrai_l=[];
                    foreach ($ex_mattrai as $value) {
                        if ($value=='N'||$value=='n') {
                            $value='';   
                        }
                        $ex_mattrai_l[]=$value;
                    }
                    $ex_matphai_l=[];
                    foreach ($ex_matphai as $value) {
                        if ($value=='N'||$value=='n') {
                            $value='';   
                        }
                        $ex_matphai_l[]=$value;
                    }
                  
                    //add lich su do can
                    $data_dc['addmp']=$ex_matphai_l[3]?$ex_matphai_l[3]:'';
                    $data_dc['addmt']=$ex_mattrai_l[3]?$ex_mattrai_l[3]:'';
                    $data_dc['canmp']=$ex_matphai_l[0]?$ex_matphai_l[0]:'';
                    $data_dc['canmt']=$ex_mattrai_l[0]?$ex_mattrai_l[0]:'';
                    $data_dc['loanmp']=$ex_matphai_l[2]?$ex_matphai_l[2]:'';
                    $data_dc['loanmt']=$ex_mattrai_l[2]?$ex_mattrai_l[2]:'';
                    $data_dc['tmmp']=$ex_matphai_l[4]?$ex_matphai_l[4]:'';
                    $data_dc['tmmt']=$ex_mattrai_l[4]?$ex_mattrai_l[4]:'';
                    $data_dc['vienmp']=$ex_matphai_l[1]?$ex_matphai_l[1]:'';
                    $data_dc['vienmt']=$ex_mattrai_l[1]?$ex_mattrai_l[1]:'';
                    $data_dc['dp']=(string)$dp;
                    
                    $data_dc['customer_id']=$add_id;
                   
                    $data_dc['created_by']=$this->session->userdata('user_id');
                    $data_dc['created'] = date('Y-m-d H:i:s');
                    //them moi
                    $ciddocan = $this->companies_model->addDocan($data_dc);
                  
                    */
                     $checkok++;
                }
                
            }
            if ($checkok>0) {
                $this->session->set_flashdata('message', lang("customers_added"));
                redirect('customers');
            }else{
                 $this->session->set_flashdata('error', lang("Lỗi import khách hàng"));
                redirect('customers');
            }
            /*
            if ($this->companies_model->addCompaniesXls($data_last,$docan)) {
                $this->session->set_flashdata('message', lang("customers_added"));
                redirect('customers');
            }else{
                 $this->session->set_flashdata('error', lang("Lỗi import khách hàng"));
                redirect('customers');
            }*/
        } else {

            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'customers/import', $this->data_last);
        }
    }

    function delete($id = NULL)
    {
        $this->sma->checkPermissions(NULL, TRUE);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        if ($this->input->get('id') == 1) {
            $this->sma->send_json(array('error' => 1, 'msg' => lang("customer_x_deleted")));
        }

        if ($this->companies_model->deleteCustomer($id)) {
            $this->sma->send_json(array('error' => 0, 'msg' => lang("customer_deleted")));
        } else {
            $this->sma->send_json(array('error' => 1, 'msg' => lang("customer_x_deleted_have_sales")));
        }
    }

    function suggestions($term = NULL, $limit = NULL)
    {
        // $this->sma->checkPermissions('index');
        if ($this->input->get('term')) {
            $term = $this->input->get('term', TRUE);
        }
        if (strlen($term) < 1) {
            return FALSE;
        }
        $limit = $this->input->get('limit', TRUE);
        $rows['results'] = $this->companies_model->getCustomerSuggestions($term, $limit);
        $this->sma->send_json($rows);
    }
    function suggestionsNhanvien($term = NULL, $limit = NULL)
    {
        // $this->sma->checkPermissions('index');
        if ($this->input->get('term')) {
            $term = $this->input->get('term', TRUE);
        }
        if (strlen($term) < 1) {
            return FALSE;
        }
        $limit = $this->input->get('limit', TRUE);
        $rows['results'] = $this->companies_model->getCustomerSuggestionsNhanvien($term, $limit);
        $this->sma->send_json($rows);
    }
     function getNhanvien($id = NULL)
    {
        // $this->sma->checkPermissions('index');
        $row = $this->companies_model->getNhanvienByID($id);
        $this->sma->send_json(array(array('id' => $row->id, 'text' => ($row->phone != '' ? $row->first_name." ".$row->last_name."-".$row->phone : $row->first_name." ".$row->last_name))));
    }
    function getCustomer($id = NULL)
    {
        // $this->sma->checkPermissions('index');
        $row = $this->companies_model->getCompanyByID($id);
        $this->sma->send_json(array(array('id' => $row->id, 'text' =>$row->name.'-'.$row->phone)));
    }

    function get_customer_details($id = NULL)
    {
        $this->sma->send_json($this->companies_model->getCompanyByID($id));
    }

    function get_award_points($id = NULL)
    {
        $this->sma->checkPermissions('index');
        $row = $this->companies_model->getCompanyByID($id);
        $this->sma->send_json(array('ca_points' => $row->award_points));
    }

    function customer_actions()
    {
        if (!$this->Owner && !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER["HTTP_REFERER"]);
        }

        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');

        if ($this->form_validation->run() == true) {

            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    $this->sma->checkPermissions('delete');
                    $error = false;
                    foreach ($_POST['val'] as $id) {
                        if (!$this->companies_model->deleteCustomer($id)) {
                            $error = true;
                        }
                    }
                    if ($error) {
                        $this->session->set_flashdata('warning', lang('customers_x_deleted_have_sales'));
                    } else {
                        $this->session->set_flashdata('message', lang("customers_deleted"));
                    }
                    redirect($_SERVER["HTTP_REFERER"]);
                }

                if ($this->input->post('form_action') == 'export_excel' || $this->input->post('form_action') == 'export_pdf') {

                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('customer'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('company'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('name'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('email'));
                    $this->excel->getActiveSheet()->SetCellValue('D1', lang('phone'));
                    $this->excel->getActiveSheet()->SetCellValue('E1', lang('address'));
                    $this->excel->getActiveSheet()->SetCellValue('F1', lang('city'));
                    $this->excel->getActiveSheet()->SetCellValue('G1', lang('state'));
                    $this->excel->getActiveSheet()->SetCellValue('H1', lang('postal_code'));
                    $this->excel->getActiveSheet()->SetCellValue('I1', lang('country'));
                    $this->excel->getActiveSheet()->SetCellValue('J1', lang('vat_no'));
                    $this->excel->getActiveSheet()->SetCellValue('K1', lang('deposit_amount'));
                    $this->excel->getActiveSheet()->SetCellValue('L1', lang('ccf1'));
                    $this->excel->getActiveSheet()->SetCellValue('M1', lang('ccf2'));
                    $this->excel->getActiveSheet()->SetCellValue('N1', lang('ccf3'));
                    $this->excel->getActiveSheet()->SetCellValue('O1', lang('ccf4'));
                    $this->excel->getActiveSheet()->SetCellValue('P1', lang('ccf5'));
                    $this->excel->getActiveSheet()->SetCellValue('Q1', lang('ccf6'));

                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $customer = $this->site->getCompanyByID($id);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $customer->company);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $customer->name);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $customer->email);
                        $this->excel->getActiveSheet()->SetCellValue('D' . $row, $customer->phone);
                        $this->excel->getActiveSheet()->SetCellValue('E' . $row, $customer->address);
                        $this->excel->getActiveSheet()->SetCellValue('F' . $row, $customer->city);
                        $this->excel->getActiveSheet()->SetCellValue('G' . $row, $customer->state);
                        $this->excel->getActiveSheet()->SetCellValue('H' . $row, $customer->postal_code);
                        $this->excel->getActiveSheet()->SetCellValue('I' . $row, $customer->country);
                        $this->excel->getActiveSheet()->SetCellValue('J' . $row, $customer->vat_no);
                        $this->excel->getActiveSheet()->SetCellValue('K' . $row, $customer->deposit_amount);
                        $this->excel->getActiveSheet()->SetCellValue('L' . $row, $customer->cf1);
                        $this->excel->getActiveSheet()->SetCellValue('M' . $row, $customer->cf2);
                        $this->excel->getActiveSheet()->SetCellValue('N' . $row, $customer->cf3);
                        $this->excel->getActiveSheet()->SetCellValue('O' . $row, $customer->cf4);
                        $this->excel->getActiveSheet()->SetCellValue('P' . $row, $customer->cf5);
                        $this->excel->getActiveSheet()->SetCellValue('Q' . $row, $customer->cf6);
                        $row++;
                    }

                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'customers_' . date('Y_m_d_H_i_s');
                    if ($this->input->post('form_action') == 'export_pdf') {
                        $styleArray = array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)));
                        $this->excel->getDefaultStyle()->applyFromArray($styleArray);
                        $this->excel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
                        require_once(APPPATH . "third_party" . DIRECTORY_SEPARATOR . "MPDF" . DIRECTORY_SEPARATOR . "mpdf.php");
                        $rendererName = PHPExcel_Settings::PDF_RENDERER_MPDF;
                        $rendererLibrary = 'MPDF';
                        $rendererLibraryPath = APPPATH . 'third_party' . DIRECTORY_SEPARATOR . $rendererLibrary;
                        if (!PHPExcel_Settings::setPdfRenderer($rendererName, $rendererLibraryPath)) {
                            die('Please set the $rendererName: ' . $rendererName . ' and $rendererLibraryPath: ' . $rendererLibraryPath . ' values' .
                                PHP_EOL . ' as appropriate for your directory structure');
                        }

                        header('Content-Type: application/pdf');
                        header('Content-Disposition: attachment;filename="' . $filename . '.pdf"');
                        header('Cache-Control: max-age=0');

                        $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'PDF');
                        return $objWriter->save('php://output');
                    }
                    if ($this->input->post('form_action') == 'export_excel') {
                        header('Content-Type: application/vnd.ms-excel');
                        header('Content-Disposition: attachment;filename="' . $filename . '.xls"');
                        header('Cache-Control: max-age=0');

                        $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
                        return $objWriter->save('php://output');
                    }

                    redirect($_SERVER["HTTP_REFERER"]);
                }
            } else {
                $this->session->set_flashdata('error', lang("no_customer_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }

    function deposits($company_id = NULL)
    {
        $this->sma->checkPermissions(false, true);

        if ($this->input->get('id')) {
            $company_id = $this->input->get('id');
        }

        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['modal_js'] = $this->site->modal_js();
        $this->data['company'] = $this->companies_model->getCompanyByID($company_id);
        $this->load->view($this->theme . 'customers/deposits', $this->data);

    }

    function get_deposits($company_id = NULL)
    {
        $this->sma->checkPermissions('deposits');
        $this->load->library('datatables');
        $this->datatables
            ->select("deposits.id as id, date, amount, paid_by, CONCAT({$this->db->dbprefix('users')}.first_name, ' ', {$this->db->dbprefix('users')}.last_name) as created_by", false)
            ->from("deposits")
            ->join('users', 'users.id=deposits.created_by', 'left')
            ->where($this->db->dbprefix('deposits').'.company_id', $company_id)
            ->add_column("Actions", "<div class=\"text-center\"> <a class=\"tip\" title='" . lang("edit_deposit") . "' href='" . site_url('customers/edit_deposit/$1') . "' data-toggle='modal' data-target='#myModal2'><i class=\"fa fa-edit\"></i></a> <a href='#' class='tip po' title='<b>" . lang("delete_deposit") . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('customers/delete_deposit/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></div>", "id")
        ->unset_column('id');
        echo $this->datatables->generate();
    }

    function add_deposit($company_id = NULL)
    {
        $this->sma->checkPermissions('deposits', true);

        if ($this->input->get('id')) {
            $company_id = $this->input->get('id');
        }
        if ($this->input->post('customer_id')) {
            $company_id = $this->input->post('customer_id');
        }
        $company = $this->companies_model->getCompanyByID($company_id);

        if ($this->Owner || $this->Admin) {
            $this->form_validation->set_rules('date', lang("date"), 'required');
        }
        $this->form_validation->set_rules('amount', lang("amount"), 'required|numeric');
        $warehouse_id=$this->session->userdata('warehouse_id');
        if ($warehouse_id==null) {
            $warehouse_id=$this->Settings->default_warehouse;
        }


        if ($this->form_validation->run() == true) {

            if ($this->Owner || $this->Admin) {
                $date = $this->sma->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }
            $data = array(
                'date' => $date,
                'amount' => $this->input->post('amount'),
                'paid_by' => $this->input->post('paid_by'),
                'note' => $this->input->post('note'),
                'company_id' => $company->id,
                'created_by' => $this->session->userdata('user_id'),
            );

            $cdata = array(
                'deposit_amount' => ($company->deposit_amount+$this->input->post('amount'))
            );

            $payment = array(
                    'date' => $date,
                    'sale_id' => null,
                    'reference_no' => $this->site->getReference('thu'),
                    'amount' => $this->input->post('amount'),
                    'paid_by' =>$this->input->post('paid_by'),
                    'cheque_no' => $this->input->post('cheque_no'),
                    'cc_no' => $this->input->post('paid_by') == 'gift_card' ? $this->input->post('gift_card_no') : $this->input->post('pcc_no'),
                    'cc_holder' => $this->input->post('pcc_holder'),
                    'cc_month' => $this->input->post('pcc_month'),
                    'cc_year' => $this->input->post('pcc_year'),
                    'cc_type' => $this->input->post('pcc_type'),
                    'note' => $this->input->post('note'),
                    'created_by' => $this->session->userdata('user_id'),
                    'type' => 'received',
                    'id_ncc_id_kh' =>0,
                    'warehouse_id' => $warehouse_id,
                    'c_name' => $this->input->post('c_name'),
                    'c_phone' => $this->input->post('c_phone'),
                    'c_address' => $this->input->post('c_address'),
                    'is_doanhthu' =>0,
                    'tiencoc_id' =>null,
                                );


        } elseif ($this->input->post('add_deposit')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('customers');
        }

        if ($this->form_validation->run() == true) {
            $check=$this->companies_model->addDepositV2($data, $cdata);
            if ($check>0) {
                $payment['tiencoc_id']=$check;
                if ($this->form_validation->run() == true && $this->sales_model->addPaymentLhson($payment,null)) {
                    $this->session->set_flashdata('message','Thêm đặt cọc thành công');
                    redirect($_SERVER["HTTP_REFERER"]);
                }else{
                     $this->session->set_flashdata('error','Lỗi khi lưu tiền cọc');
                 }                
            }else{
                 $this->session->set_flashdata('error','Lỗi khi lưu tiền cọc');
             }            
            redirect("customers");
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->data['company'] = $company;
            $this->load->view($this->theme . 'customers/add_deposit', $this->data);
        }
    }

    function edit_deposit($id = NULL)
    {
        $this->sma->checkPermissions('deposits', true);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $deposit = $this->companies_model->getDepositByID($id);
        $company = $this->companies_model->getCompanyByID($deposit->company_id);
        $payment = $this->companies_model->getPaymentByDepositID($id);
        if ($this->Owner || $this->Admin) {
            $this->form_validation->set_rules('date', lang("date"), 'required');
        }
        $this->form_validation->set_rules('amount', lang("amount"), 'required|numeric');
        $warehouse_id=$this->session->userdata('warehouse_id');
        if ($warehouse_id==null) {
            $warehouse_id=$this->Settings->default_warehouse;
        }

        if ($this->form_validation->run() == true) {

            if ($this->Owner || $this->Admin) {
                $date = $this->sma->fld(trim($this->input->post('date')));
            } else {
                $date = $deposit->date;
            }
            $data = array(
                'date' => $date,
                'amount' => $this->input->post('amount'),
                'paid_by' => $this->input->post('paid_by'),
                'note' => $this->input->post('note'),
                'company_id' => $deposit->company_id,
                'updated_by' => $this->session->userdata('user_id'),
                'updated_at' => $date = date('Y-m-d H:i:s'),
            );

            $cdata = array(
                'deposit_amount' => (($company->deposit_amount-$deposit->amount)+$this->input->post('amount'))
            );

            $payment_data = array(
                    'date' => $date,
                    'sale_id' => null,
                    'reference_no' => $this->site->getReference('thu'),
                    'amount' => $this->input->post('amount'),
                    'paid_by' =>$this->input->post('paid_by'),
                    'cheque_no' => $this->input->post('cheque_no'),
                    'cc_no' => $this->input->post('paid_by') == 'gift_card' ? $this->input->post('gift_card_no') : $this->input->post('pcc_no'),
                    'cc_holder' => $this->input->post('pcc_holder'),
                    'cc_month' => $this->input->post('pcc_month'),
                    'cc_year' => $this->input->post('pcc_year'),
                    'cc_type' => $this->input->post('pcc_type'),
                    'note' => $this->input->post('note'),
                    'created_by' => $this->session->userdata('user_id'),
                    'type' => 'received',
                    'id_ncc_id_kh' =>0,
                    'warehouse_id' => $warehouse_id,
                    'c_name' => $this->input->post('c_name'),
                    'c_phone' => $this->input->post('c_phone'),
                    'c_address' => $this->input->post('c_address'),
                    'is_doanhthu' =>0,
                    'tiencoc_id' =>$id,
                                );

        } elseif ($this->input->post('edit_deposit')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }

        if ($this->form_validation->run() == true && $this->companies_model->updateDeposit($id, $data, $cdata)) {

            if ($payment->id>0&&$this->sales_model->updatePaymentLhson($payment->id, $payment_data, null)) {
                
            }
            $this->session->set_flashdata('message', lang("deposit_updated"));
            redirect($_SERVER["HTTP_REFERER"]);
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->data['company'] = $company;
            $this->data['deposit'] = $deposit;
            $this->data['payment'] = $payment;
            $this->load->view($this->theme . 'customers/edit_deposit', $this->data);
        }
    }

    public function delete_deposit($id)
    {
        $this->sma->checkPermissions(NULL, TRUE);
        $payment = $this->companies_model->getPaymentByDepositID($id);
        if ($this->companies_model->deleteDeposit($id)) {
            if ($payment->id>0&&$this->purchases_model->deletePaymentLhson($payment->id)) {
                
            }            
            $this->sma->send_json(array('error' => 0, 'msg' => lang("deposit_deleted")));
        }
    }

    public function deposit_note($id = null)
    {
        $this->sma->checkPermissions('deposits', true);
        $deposit = $this->companies_model->getDepositByID($id);
        $this->data['customer'] = $this->companies_model->getCompanyByID($deposit->company_id);
        $this->data['deposit'] = $deposit;
        $this->data['page_title'] = $this->lang->line("deposit_note");
        $this->load->view($this->theme . 'customers/deposit_note', $this->data);
    }

    function addresses($company_id = NULL)
    {
        $this->sma->checkPermissions('index', true);
        $this->data['modal_js'] = $this->site->modal_js();
        $this->data['company'] = $this->companies_model->getCompanyByID($company_id);
        $this->data['addresses'] = $this->companies_model->getCompanyAddresses($company_id);
        $this->load->view($this->theme . 'customers/addresses', $this->data);

    }

    function add_address($company_id = NULL)
    {
        $this->sma->checkPermissions('add', true);
        $company = $this->companies_model->getCompanyByID($company_id);

        $this->form_validation->set_rules('line1', lang("line1"), 'required');
        $this->form_validation->set_rules('city', lang("city"), 'required');
        $this->form_validation->set_rules('state', lang("state"), 'required');
        $this->form_validation->set_rules('country', lang("country"), 'required');
        //$this->form_validation->set_rules('phone', lang("phone"), 'required');

        if ($this->form_validation->run() == true) {

            $data = array(
                'line1' => $this->input->post('line1'),
                'line2' => $this->input->post('line2'),
                'city' => $this->input->post('city'),
                'postal_code' => $this->input->post('postal_code'),
                'state' => $this->input->post('state'),
                'country' => $this->input->post('country'),
                'phone' => $this->input->post('phone'),
                'company_id' => $company->id,
            );

        } elseif ($this->input->post('add_address')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('customers');
        }

        if ($this->form_validation->run() == true && $this->companies_model->addAddress($data)) {
            $this->session->set_flashdata('message', lang("address_added"));
            redirect("customers");
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->data['company'] = $company;
            $this->load->view($this->theme . 'customers/add_address', $this->data);
        }
    }

    function edit_address($id = NULL)
    {
        $this->sma->checkPermissions('edit', true);

        $this->form_validation->set_rules('line1', lang("line1"), 'required');
        $this->form_validation->set_rules('city', lang("city"), 'required');
        $this->form_validation->set_rules('state', lang("state"), 'required');
        $this->form_validation->set_rules('country', lang("country"), 'required');
        $this->form_validation->set_rules('phone', lang("phone"), 'required');

        if ($this->form_validation->run() == true) {

            $data = array(
                'line1' => $this->input->post('line1'),
                'line2' => $this->input->post('line2'),
                'city' => $this->input->post('city'),
                'postal_code' => $this->input->post('postal_code'),
                'state' => $this->input->post('state'),
                'country' => $this->input->post('country'),
                'phone' => $this->input->post('phone'),
                'updated_at' => date('Y-m-d H:i:s'),
            );

        } elseif ($this->input->post('edit_address')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('customers');
        }

        if ($this->form_validation->run() == true && $this->companies_model->updateAddress($id, $data)) {
            $this->session->set_flashdata('message', lang("address_updated"));
            redirect("customers");
        } else {            
            $this->data['modal_js'] = $this->site->modal_js();
            $this->data['address'] = $this->companies_model->getAddressByID($id);
            $this->load->view($this->theme . 'customers/edit_address', $this->data);
        }
    }

    public function delete_address($id)
    {
        $this->sma->checkPermissions('delete', TRUE);

        if ($this->companies_model->deleteAddress($id)) {
            $this->session->set_flashdata('message', lang("address_deleted"));
            redirect("customers");
        }
    }
    public function savedocan()
    {
        $data['addmp']=(string)$this->input->get('addmp','');
        $data['addmt']=(string)$this->input->get('addmt','');
        $data['canmp']=(string)$this->input->get('canmp','');
        $data['canmt']=(string)$this->input->get('canmt','');
        $data['loanmp']=(string)$this->input->get('loanmp','');
        $data['loanmt']=(string)$this->input->get('loanmt','');
        $data['tmmp']=(string)$this->input->get('tmmp','');
        $data['tmmt']=(string)$this->input->get('tmmt','');
        $data['vienmp']=(string)$this->input->get('vienmp','');
        $data['vienmt']=(string)$this->input->get('vienmt','');
        $data['dp']=(string)$this->input->get('dp','');
        
        $data['customer_id']=(string)$this->input->get('customerid');
                
        $iddocan=(double)$this->input->get('iddocan');
        
        if ($iddocan>0&&$data['customer_id']>0) {
            //check valid and update 
            $data['checked_out']=$this->session->userdata('user_id');
            $data['checked_out_time'] = date('Y-m-d H:i:s');
            $cid = $this->companies_model->updateDocan($iddocan,$data['customer_id'],$data);
            if ($cid) {
                //ok load lai danh sach do can
                exit('OK');
            }
        }else{
            $data['created_by']=$this->session->userdata('user_id');
            $data['created'] = date('Y-m-d H:i:s');
            //them moi
            $cid = $this->companies_model->addDocan($data);
            if ($cid>0) {
                //ok load lai danh sach do can
                exit('OK');
            }
        }
        exit('LỖI');        
    }
    public function loadHistoryDoCan($customer_id=0)
    {
        echo $rs=$this->companies_model->getAllDoCan($customer_id);

        exit();
    }
    public function xoadocan($customer_id=0,$id=0)
    {
        $rs=$this->companies_model->deleteDocan($customer_id,$id);
        if ($rs) {
            exit('OK');
        }
        exit('ERROR');
    }
    function payment_now()
    {
        $this->form_validation->set_rules('code', lang("Mã code kích hoạt"), 'required');

        if ($this->form_validation->run('companies/payment_now') == true) {

            $data = array('code' => $this->input->post('code'),
                'note' => $this->input->post('payment_note'),);
            
        } elseif ($this->input->post('payment_now')) {

            $this->session->set_flashdata('error', validation_errors());

            redirect($_SERVER["HTTP_REFERER"]);


        }

        if ($this->form_validation->run() == true) {
            //tien hanh POST API POST 
            $list=$this->site->addPaymentByCode(trim($data['code']),$data['note']);                            
            if (strpos($list,"|OK") == false) {    
                $this->session->set_flashdata('error',$list);
            }                         
            else
            {
                
                $this->session->set_flashdata('message',str_replace("|OK","",$list));
            }          
            redirect($_SERVER["HTTP_REFERER"]); 

        } else {

            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

            $this->data['modal_js'] = $this->site->modal_js();

            $this->load->view($this->theme . 'customers/payment_now', $this->data);


        }

    }
    function add_deposit_kh($company_id = NULL)
    {
        $this->sma->checkPermissions('deposits', true);

        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['modal_js'] = $this->site->modal_js();
        $this->load->view($this->theme . 'customers/add_deposit_kh', $this->data);
    }  
    function listdeposit()
    {
        $this->sma->checkPermissions('deposits', true);
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['users'] = $this->reports_model->getStaff();
       
        $this->data['categories'] = $this->site->getAllPTTT(true);

        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => 'Báo cáo các khoản tiền cọc'));
        $meta = array('page_title' => 'Báo cáo các khoản tiền cọc', 'bc' => $bc);
        $this->page_construct('customers/listdeposits', $meta, $this->data);
    }  
    function list_deposits($pdf = NULL, $xls = NULL)
    {
        $this->sma->checkPermissions('deposits');
        $this->load->library('datatables');

        $print_link = anchor('purchases/printphieuthu/$2', '<i class="fa fa-print"></i> '.lang('In phiếu thu'), 'data-toggle="modal" data-target="#myModal" title="In phiếu thu"');

        $user = $this->input->get('user') ? $this->input->get('user') : NULL;
        $category = $this->input->get('paid_by') ? $this->input->get('paid_by') : NULL;
        $reference_no = $this->input->get('reference_no') ? $this->input->get('reference_no') : NULL;
        
        
        $note = $this->input->get('note') ? $this->input->get('note') : NULL;       
        $start_date = $this->input->get('start_date') ? $this->input->get('start_date') : NULL;
        $end_date = $this->input->get('end_date') ? $this->input->get('end_date') : NULL;

        if ($start_date) {
            //lhson date
            $start_date = date("Y-m-d H:i:s", strtotime(str_replace('/', '-',$start_date)) );
            $end_date = date("Y-m-d H:i:s", strtotime(str_replace('/', '-',$end_date)) );
        }
        if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $user = $this->session->userdata('user_id');
        }

        

        if ($pdf || $xls)
        {

        }else{
            
             $this->datatables
                ->select("deposits.id as id, deposits.date,payments.reference_no, deposits.amount,companies.name,companies.phone, deposits.paid_by, CONCAT({$this->db->dbprefix('users')}.first_name, ' ', {$this->db->dbprefix('users')}.last_name) as created_by,payments.id as payment_id", false)
                ->from("deposits")
                ->join('companies', 'companies.id=deposits.company_id', 'left')
                ->join('users', 'users.id=deposits.created_by', 'left')
                ->join('payments', 'payments.tiencoc_id=deposits.id', 'left')
                ->add_column("Actions", "<div class=\"text-center\"><a class=\"tip\" title='" . lang("edit_deposit") . "' href='" . site_url('customers/edit_deposit/$1') . "' data-toggle='modal' data-target='#myModal2'><i class=\"fa fa-edit\"></i></a> <a href='#' class='tip po' title='<b>" . lang("delete_deposit") . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('customers/delete_deposit/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a> ".$print_link."</div>", "id,payment_id")
            ->unset_column('id');

            if ($user) {
                $this->db->where('deposits.created_by', $user);
            }
            if ($category) {
                $this->db->where('deposits.paid_by', $category);
            }
            if ($reference_no!='') {
                 $this->db->like('payments.reference_no', $reference_no);
            }
            if ($note!='') {
                 $this->db->like('payments.note', $note);
            }

            if ($start_date) {
                $this->db->where($this->db->dbprefix('payments').'.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
            }

            echo $this->datatables->generate();
        }
    }
    function viewdoncu($id = NULL)
    {
       
        $company_details = $this->site->getViewDonCu($id);
      
        $this->data['hoadon'] = $company_details;
        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['modal_js'] = $this->site->modal_js();

        $this->load->view($this->theme . 'customers/chitietdoncu', $this->data);
        
    }
}
