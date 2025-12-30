<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
require APPPATH . 'libraries/REST_Controller.php';
require __DIR__ . '/../vendor/autoload.php';
use Automattic\WooCommerce\Client;
use Automattic\WooCommerce\HttpClient\HttpClientException;
use Automattic\WooCommerce\HttpClient\HttpClient;

class Apiapp extends REST_Controller 
{
    private $auth;

    public function __construct()
    {
        parent::__construct();
        $this->load->database();

        $this->Settings = $this->site->get_setting();
        if($sma_language = $this->input->cookie('sma_language', TRUE)) {
            $this->config->set_item('language', $sma_language);
            $this->lang->load('sma', $sma_language);
            $this->Settings->user_language = $sma_language;
        } else {
            $this->config->set_item('language', $this->Settings->language);
            $this->lang->load('sma', $this->Settings->language);
            $this->Settings->user_language = $this->Settings->language;
        }
        if($rtl_support = $this->input->cookie('sma_rtl_support', TRUE)) {
            $this->Settings->user_rtl = $rtl_support;
        } else {
            $this->Settings->user_rtl = $this->Settings->rtl;
        }
        $this->theme = $this->Settings->theme.'/views/';
        if(is_dir(VIEWPATH.$this->Settings->theme.DIRECTORY_SEPARATOR.'assets'.DIRECTORY_SEPARATOR)) {
            $this->data['assets'] = base_url() . 'themes/' . $this->Settings->theme . '/assets/';
        } else {
            $this->data['assets'] = base_url() . 'themes/default/assets/';
        }

        //sale status, payment status
        $sst = array('completed' => lang('completed'), 'pending' => lang('pending'));
        $pst = array('pending' => lang('pending'), 'due' => lang('due'), 'partial' => lang('partial'), 'paid' => lang('paid'));
        $opts = array('packing' => lang('packing'), 'delivering' => lang('delivering'), 'delivered' => lang('delivered'));
        $sale_type = array('sale' =>'Tạo đơn hàng', 'pos' =>'POS bán hàng','web'=>'Web wordpress','tmdt'=>'Sàn TMĐT');

        $this->Settings->sale_type=$sale_type;

        $this->Settings->sale_status=$sst;
        $this->Settings->payment_status=$pst;
        $this->Settings->delivery_status=$opts;

        $this->data['Settings'] = $this->Settings;

        $this->loggedIn = $this->sma->logged_in();

        if($this->loggedIn) {
            $this->default_currency = $this->site->getCurrencyByCode($this->Settings->default_currency);
            $this->data['default_currency'] = $this->default_currency;
            $this->Owner = $this->sma->in_group('owner') ? TRUE : NULL;
            $this->data['Owner'] = $this->Owner;
            $this->Customer = $this->sma->in_group('customer') ? TRUE : NULL;
            $this->data['Customer'] = $this->Customer;
            $this->Supplier = $this->sma->in_group('supplier') ? TRUE : NULL;
            $this->data['Supplier'] = $this->Supplier;
            $this->Admin = $this->sma->in_group('admin') ? TRUE : NULL;
            $this->data['Admin'] = $this->Admin;

            if($sd = $this->site->getDateFormat($this->Settings->dateformat)) {
                $dateFormats = array(
                    'js_sdate' => $sd->js,
                    'php_sdate' => $sd->php,
                    'mysq_sdate' => $sd->sql,
                    'js_ldate' => $sd->js . ' hh:ii',
                    'php_ldate' => $sd->php . ' H:i',
                    'mysql_ldate' => $sd->sql . ' %H:%i'
                    );
            } else {
                $dateFormats = array(
                    'js_sdate' => 'mm-dd-yyyy',
                    'php_sdate' => 'm-d-Y',
                    'mysq_sdate' => '%m-%d-%Y',
                    'js_ldate' => 'mm-dd-yyyy hh:ii:ss',
                    'php_ldate' => 'm-d-Y H:i:s',
                    'mysql_ldate' => '%m-%d-%Y %T'
                    );
            }
            if(file_exists(APPPATH.'controllers'.DIRECTORY_SEPARATOR.'Pos.php')) {
                define("POS", 1);
            } else {
                define("POS", 0);
            }
            if(!$this->Owner && !$this->Admin) {
                $gp = $this->site->checkPermissions();
                $this->GP = $gp[0];
                $this->data['GP'] = $gp[0];
            } else {
                $this->data['GP'] = NULL;
            }
            $this->dateFormats = $dateFormats;
            $this->data['dateFormats'] = $dateFormats;
            $this->load->language('calendar');
            //$this->default_currency = $this->Settings->currency_code;
            //$this->data['default_currency'] = $this->default_currency;
            $this->m = strtolower($this->router->fetch_class());
            $this->v = strtolower($this->router->fetch_method());
            $this->data['m']= $this->m;
            $this->data['v'] = $this->v;
            $this->data['dt_lang'] = json_encode(lang('datatables_lang'));
            $this->data['dp_lang'] = json_encode(array('days' => array(lang('cal_sunday'), lang('cal_monday'), lang('cal_tuesday'), lang('cal_wednesday'), lang('cal_thursday'), lang('cal_friday'), lang('cal_saturday'), lang('cal_sunday')), 'daysShort' => array(lang('cal_sun'), lang('cal_mon'), lang('cal_tue'), lang('cal_wed'), lang('cal_thu'), lang('cal_fri'), lang('cal_sat'), lang('cal_sun')), 'daysMin' => array(lang('cal_su'), lang('cal_mo'), lang('cal_tu'), lang('cal_we'), lang('cal_th'), lang('cal_fr'), lang('cal_sa'), lang('cal_su')), 'months' => array(lang('cal_january'), lang('cal_february'), lang('cal_march'), lang('cal_april'), lang('cal_may'), lang('cal_june'), lang('cal_july'), lang('cal_august'), lang('cal_september'), lang('cal_october'), lang('cal_november'), lang('cal_december')), 'monthsShort' => array(lang('cal_jan'), lang('cal_feb'), lang('cal_mar'), lang('cal_apr'), lang('cal_may'), lang('cal_jun'), lang('cal_jul'), lang('cal_aug'), lang('cal_sep'), lang('cal_oct'), lang('cal_nov'), lang('cal_dec')), 'today' => lang('today'), 'suffix' => array(), 'meridiem' => array()));

        }
        
        $this->load->config('ion_auth', TRUE);
         //initialize data
        $this->identity_column = $this->config->item('identity', 'ion_auth');
        $this->store_salt = $this->config->item('store_salt', 'ion_auth');
        $this->salt_length = $this->config->item('salt_length', 'ion_auth');

        $this->lang->load('auth', $this->Settings->user_language);

        $this->load->model('auth_model');
        $this->load->model('site');
        $this->load->library('ion_auth');
        $this->load->model('Apiapp_model');
        $this->load->model('pos_model');
        $this->load->model('companies_model');
        $this->load->model('sales_model');  
        $this->load->model('doitac_model');  
        $this->load->model('reports_model'); 
        $this->load->model('settings_model');  
        $this->load->model('purchases_model');  
        $this->load->model('products_model');  
             

        $this->pos_settings = $this->pos_model->getSetting();
         
    }
    public function index_get()
    {
        // Display all books
          
            $rs=$this->Apiapp_model->checkActiveApi();
            $token_name=$this->security->get_csrf_token_name();
            $token_value=$this->security->get_csrf_hash();

            $this->response(array('status'=>($rs=='OK'?true:false),'mess'=>$rs,'token_name'=>$token_name,'token_value'=>$token_value),REST_Controller::HTTP_OK);
       
    }

    public function index_post()
    {
        $rs=array('status'=>false,'mess'=>'INVALID authentication');

        if ($_SERVER['PHP_AUTH_USER'] == 'CIwiFtn8QMfIq2MNJqU8Q2lBOhKzpTTIdBBABC' && $_SERVER['PHP_AUTH_PW'] == md5('posbasic@2017#')) 
        {   
                           
            if ($_POST['username']!=''&&$_POST['password']!=''&&$_POST['device_token']!='') {
                
                if ($this->ion_auth->login($this->input->post('username'), $this->input->post('password'), FALSE)) {

                        $token=$this->security->get_csrf_hash();

                        $this->db->where('id', $this->session->userdata('user_id'))->update('users', ['tokenlogin'=>$token,'device_token'=>$_POST['device_token']]);

                        $rs=array('status'=>true,'mess'=>'Đăng nhập thành công','tokenlogin'=>$token);

                        $this->response($rs);
                        return;
                    
                }else{
                    $rs=array('status'=>false,'mess'=>"Thông tin đăng nhập không hợp lệ");
                }
            }
        }        
        $this->response($rs);            
    }
    public function Logout_post()
    {
        $rs=array('status'=>false,'mess'=>'INVALID authentication');

        if ($_SERVER['PHP_AUTH_USER'] == 'CIwiFtn8QMfIq2MNJqU8Q2lBOhKzpTTIdBBABC' && $_SERVER['PHP_AUTH_PW'] == md5('posbasic@2017#')) 
        {   
            $rs=array('status'=>false,'mess'=>'ERROR');

            
            
            $_POST['token']=$this->security->get_csrf_hash();
            if ($_POST['token']!=''&&$_POST['device_token']!='') {
                //check login by token
                $rslogin=$this->checkloginByToken($_POST['token'],$_POST['device_token']);
                if ($rslogin)
                {
                    
                    $this->db->where('id', $this->session->userdata('user_id'))->update('users', ['tokenlogin'=>NULL]);

                    $this->ion_auth->logout();

                    $rs=array('status'=>true,'mess'=>true);   

                }else{
                    $rs=array('status'=>false,'mess'=>'Token không chính xác.');                    
                }
            }else{
                $rs=array('status'=>false,'mess'=>'Token không hợp lệ');
            }              
           
        }
        $this->response($rs);            
    }
     
    public function forgetpassword_post()
    {
        $rs=$rs=array('status'=>false,'mess'=>'INVALID authentication');
        if ($_SERVER['PHP_AUTH_USER'] == 'CIwiFtn8QMfIq2MNJqU8Q2lBOhKzpTTIdBBABC' && $_SERVER['PHP_AUTH_PW'] == md5('posbasic@2017#')) 
        {
            $rs=array('status'=>false,'mess'=>'ERROR');
            if ($_POST['email']!='') {
                $identity = $this->ion_auth->where('email', strtolower($_POST['email']))->users()->row();
                if (empty($identity)) {                    
                    $rs=array('status'=>false,'mess'=>'Thông tin email không chính xác');
                }else{
                    $forgotten = $this->ion_auth->forgotten_password($identity->email);

                    if ($forgotten) {
                        
                        $rs=array('status'=>true,'mess'=>$this->ion_auth->messages());
                    } else {
                        $rs=array('status'=>false,'mess'=>$this->ion_auth->errors());
                    }
                }

            }else{
                $rs=$rs=array('status'=>false,'mess'=>'Vui lòng nhập email hợp lệ');
            }       
        }
        $this->response($rs);
    }
    
    function checkloginByToken($device_token='',$tokenlogin='')
    {
        if ($device_token!=''&&$tokenlogin!='') {
            if (!$this->ion_auth->logged_in()) {
                //tien hanh login by token
                if ($this->ion_auth->login($this->input->post('username'), $this->input->post('password'), FALSE)) {

                    $token=$this->security->get_csrf_hash();

                    $this->db->where('id', $this->session->userdata('user_id'))->update('users', ['tokenlogin'=>$token,'device_token'=>$_POST['device_token']]);                    
                    return true;                    
                }
                return false;
            }else{
                
                return true;
            }
        }
        return false;                 
    }

    public function config_get()
    {
        // Display all books
        if ($_SERVER['PHP_AUTH_USER'] == 'CIwiFtn8QMfIq2MNJqU8Q2lBOhKzpTTIdBBABC' && $_SERVER['PHP_AUTH_PW'] == md5('posbasic@2017#')) 
        {             
            $pos_settings = $this->pos_model->getSetting();
            $check_promotion=false;
            if ($this->Settings->product_discount && ($this->Owner || $this->Admin || $this->session->userdata('allow_discount'))) {
                $check_promotion=true;
            }
            $this->Settings->check_promotion=$check_promotion;
            
            if (file_exists('./themes/' . $this->theme . '/views/print_templates/printpos.html')) {          
                $sale_temp = file_get_contents('themes/' . $this->theme . '/views/print_templates/printpos.html');            
            } else {             
                $sale_temp = file_get_contents('./themes/default/views/print_templates/printpos.html');          
            }        
            $_get_active_pos=$this->settings_model->get_print_value($sale_temp);    
            $rs_ex_pos=explode(":",$_get_active_pos);
            
            $_sizein_page=isset($rs_ex_pos[0])&&$rs_ex_pos[0]!=":"?$rs_ex_pos[0]:"A5";
            $_chieuin_page=isset($rs_ex_pos[1])&&$rs_ex_pos[1]!=":"?$rs_ex_pos[1]:"Portrait";
            
            $kich_thuoc=$this->settings_model->define_size_in($_sizein_page,$_chieuin_page);  
            
            $this->Settings->size_pager=$kich_thuoc;     

            $this->Settings->SITE_TMDT_URL=SITE_TMDT_URL;    
            $this->Settings->status_lang=array('completed'=>lang('completed'),'pending'=>lang('pending'),'paid'=>lang('paid'),'sent'=>lang('sent'),'received'=>lang('received'),'partial'=>lang('partial'),'transferring'=>lang('transferring'),'ordered'=>lang('ordered'),'due'=>lang('due'),'returned'=>lang('returned'));

            $this->Settings->default_customer=$pos_settings->default_customer;
            $this->response(array('status'=>true,'config'=>$this->Settings));
        }else{
            $this->response(array('status'=>false,'mess'=>'INVALID authentication'));
        }
    }
     public function User_get()
    {
        // Display all books
        if ($_SERVER['PHP_AUTH_USER'] == 'CIwiFtn8QMfIq2MNJqU8Q2lBOhKzpTTIdBBABC' && $_SERVER['PHP_AUTH_PW'] == md5('posbasic@2017#')) 
        {               

            $this->response(array('status'=>true,'user'=>$this->site->getUser()));
        }else{
            $this->response(array('status'=>false,'mess'=>'INVALID authentication'));
        }
    }
     public function CustomerGroup_get()
    {
        // Display all books
        if ($_SERVER['PHP_AUTH_USER'] == 'CIwiFtn8QMfIq2MNJqU8Q2lBOhKzpTTIdBBABC' && $_SERVER['PHP_AUTH_PW'] == md5('posbasic@2017#')) 
        {               

            $this->response(array('status'=>true,'groups'=>$this->companies_model->getAllCustomerGroups()));
        }else{
            $this->response(array('status'=>false,'mess'=>'INVALID authentication'));
        }
    }
     public function PriceGroups_get()
    {
        // Display all books
        if ($_SERVER['PHP_AUTH_USER'] == 'CIwiFtn8QMfIq2MNJqU8Q2lBOhKzpTTIdBBABC' && $_SERVER['PHP_AUTH_PW'] == md5('posbasic@2017#')) 
        {               

            $this->response(array('status'=>true,'groups'=>$this->companies_model->getAllPriceGroups()));
        }else{
            $this->response(array('status'=>false,'mess'=>'INVALID authentication'));
        }
    }

    public function stores_post()
    {
        $rs=array('status'=>false,'mess'=>'INVALID authentication');
        if ($_SERVER['PHP_AUTH_USER'] == 'CIwiFtn8QMfIq2MNJqU8Q2lBOhKzpTTIdBBABC' && $_SERVER['PHP_AUTH_PW'] == md5('posbasic@2017#')) 
        {
            $rs=array('status'=>false,'mess'=>'ERROR');
            
            $_POST['token']=$this->security->get_csrf_hash();
            if ($_POST['token']!=''&&$_POST['device_token']!='') {
                //check login by token
                $rslogin=$this->checkloginByToken($_POST['token'],$_POST['device_token']);
                if ($rslogin)
                {
                    $store=NULL;
                    if ($this->Admin||$this->Owner)
                    {
                        $store=$this->site->getAllWarehouses();
                    }else{
                        $store=$this->site->getAllWarehouses($this->session->userdata('warehouse_id'));
                    }
                    $rs=array('status'=>true,'mess'=>'Thành công.','store'=>$store);                    
                }else{
                    $rs=array('status'=>false,'mess'=>'Token không chính xác.');                    
                }
            }else{
                $rs=array('status'=>false,'mess'=>'Token không hợp lệ');
            }       
        }
        $this->response($rs);
    }
    public function checkPermissions_post()
    {
        $rs=array('status'=>false,'mess'=>'INVALID authentication');
        if ($_SERVER['PHP_AUTH_USER'] == 'CIwiFtn8QMfIq2MNJqU8Q2lBOhKzpTTIdBBABC' && $_SERVER['PHP_AUTH_PW'] == md5('posbasic@2017#')) 
        {
            $rs=array('status'=>false,'mess'=>'ERROR');
            $_POST['token']=$this->security->get_csrf_hash();
            if ($_POST['token']!=''&&$_POST['device_token']!='') {
                //check login by token
                $rslogin=$this->checkloginByToken($_POST['token'],$_POST['device_token']);

                if ($rslogin)
                {
                    if ($this->Admin) 
                    {
                        $rs=array('status'=>true,'mess'=>'Thành công.','permission'=>['Admin']);                                        
                    }else if ($this->Owner) 
                    {
                       $rs=array('status'=>true,'mess'=>'Thành công.','permission'=>['Owner']);                                     
                    }else{
                        $rs=array('status'=>true,'mess'=>'Thành công.','permission'=>$this->site->checkPermissions());                    
                    }                    
                    
                }else{
                    $rs=array('status'=>false,'mess'=>'Token không chính xác.');                    
                }
            }else{
                $rs=array('status'=>false,'mess'=>'Token không hợp lệ');
            }       
        }
        $this->response($rs);
    }
    public function Categories_post()
    {
        $rs=array('status'=>false,'mess'=>'INVALID authentication');
        if ($_SERVER['PHP_AUTH_USER'] == 'CIwiFtn8QMfIq2MNJqU8Q2lBOhKzpTTIdBBABC' && $_SERVER['PHP_AUTH_PW'] == md5('posbasic@2017#')) 
        {
            $rs=array('status'=>false,'mess'=>'ERROR');
            $_POST['token']=$this->security->get_csrf_hash();
            if ($_POST['token']!=''&&$_POST['device_token']!='') {
                //check login by token
                $rslogin=$this->checkloginByToken($_POST['token'],$_POST['device_token']);
                if ($rslogin)
                {                   
                    /*where*/
                    $list_cat=[];
                    $categories=$this->site->getAllCategoriesApi($_POST['keyword']);

                    $total_parent=0;
                    if (!empty($categories))
                    {
                        $checkdanhmuc=0;

                        foreach ($categories as $cat_child) 
                        {
                            $obj=[];
                            //tien hanh de quy danh muc
                            $parent_id=(int)$cat_child->parent_id;
                            if ($parent_id==0) {
                                //de quy danh muc con
                                $cat=$cat_child;
                            }else{
                                if ($_POST['keyword']!='') {
                                    $count_sp_all=$this->pos_model->products_count($cat_child->id);
                                    if ($count_sp_all>0) 
                                    {
                                        //lay danh muc cha
                                        $obj_parent=$level1=$this->site->getCategoryCha($parent_id);
                                        if ((int)$level1->parent_id>0) {
                                            $obj_parent=$level2=$this->site->getCategoryCha($level1->parent_id);
                                            
                                            if ((int)$level2->parent_id>0) {
                                                $obj_parent=$level3=$this->site->getCategoryCha($level2->parent_id);
                                                
                                                if ((int)$level3->parent_id>0) {
                                                     $obj_parent=$level4=$this->site->getCategoryCha($level3->parent_id); 
                                                                       
                                                }
                                            }
                                        }
                                        $cat=$obj_parent; 
                                     }else{
                                         $cat=$cat_child; 
                                     }
                                }                                               
                            }

                            
                            $img="assets/uploads/thumbs/" . ($cat->image ? $cat->image : 'no_image.png');
                            
                            $count_sp_all=$this->pos_model->products_count($cat->id);
                            $subcategories = $this->site->getSubCategoriesAPI($cat->id);
                            
                            $scats = [];                           
                            if ($subcategories) 
                            {
                                foreach ($subcategories as $sub_category) 
                                {
                                    $img="assets/uploads/thumbs/" . ($sub_category->image ? $sub_category->image : 'no_image.png');
                                        
                                    $count_sp=$this->pos_model->products_count($cat->id,$sub_category->id);
                                   
                                    //if ($count_sp>0) 
                                        {//khi danh muc co san pham moi hien thi

                                        

                                        $subcategories_3 = $this->site->getSubCategoriesAPI($sub_category->id);
                                        
                                        $scats_3 = []; 
                                        if ($subcategories_3) 
                                        {
                                            foreach ($subcategories_3 as $sub_category_3) 
                                            {
                                                $img_3="assets/uploads/thumbs/" . ($sub_category_3->image ? $sub_category_3->image : 'no_image.png');
                                                $count_sp_3=$this->pos_model->products_count($sub_category->id,$sub_category_3->id);
                                              
                                                //if ($count_sp_3>0) 
                                                {   
                                                    $subcategories_4 = $this->site->getSubCategoriesAPI($sub_category_3->id);
                                                    $scats_4 = []; 
                                                    $count_sp_4=0;
                                                     if ($subcategories_4) 
                                                    {
                                                        foreach ($subcategories_4 as $sub_category_4) 
                                                        {
                                                            $img_4="assets/uploads/thumbs/" . ($sub_category_4->image ? $sub_category_4->image : 'no_image.png');
                                                            $count_sp_3+=$count_sp_4=$this->pos_model->products_count($sub_category_3->id,$sub_category_4->id);

                                                            if ($count_sp_4>0) {
                                                                $scats_4[]=array('ID'=>$sub_category_4->id,'image'=>$img_4,'name'=>$sub_category_4->name,'count_items'=>$count_sp_4,'level'=>4);    
                                                            }                                                            

                                                        }
                                                        if ($count_sp_4==0) {
                                                            unset($scats_4);
                                                        }
                                                    }
                                                    $count_sp+=$count_sp_3;

                                                    $obj_3=array('ID'=>$sub_category_3->id,'image'=>$img_3,'name'=>$sub_category_3->name,'count_items'=>$count_sp_3,'level'=>3);

                                                    $obj_3['subcategories']=$scats_4;

                                                    if ($count_sp_3>0) {
                                                        $scats_3[]=$obj_3; 
                                                    }
                                                                                                       

                                                }   

                                            }
                                        }
                                        $count_sp_all+=$count_sp;

                                        $obj_2=array('ID'=>$sub_category->id,'image'=>$img,'name'=>$sub_category->name,'count_items'=>$count_sp,'level'=>2);  

                                        $obj_2['subcategories']=$scats_3;
                                        if ($count_sp>0) {
                                            $scats[]=$obj_2;    
                                        }
                                        
                                    }
                                        
                                }
                            }                                                        
                           

                            if ($count_sp_all>0) 
                            {
                                 $obj=array('ID'=>$cat->id,'image'=>$img,'name'=>$cat->name,'count_items'=>$count_sp_all,'level'=>1);
                                 $obj['subcategories']= $scats;
                                $list_cat[] =$obj;  
                                $total_parent++;  
                            }                                                       
                            
                        }   
                    } 
                    if ($_POST['keyword']!='') {
                        $list_cat=array_unique($list_cat);
                    }
                    $rs=array('status'=>true,'mess'=>'Thành công.','list'=>$list_cat,'total'=>count($total_parent));                    
                }else{
                    $rs=array('status'=>false,'mess'=>'Token không chính xác.');                    
                }
            }else{
                $rs=array('status'=>false,'mess'=>'Token không hợp lệ');
            }       
        }
        $this->response($rs);
    }
   
    public function products_post()
    {
        $rs=array('status'=>false,'mess'=>'INVALID authentication');
        if ($_SERVER['PHP_AUTH_USER'] == 'CIwiFtn8QMfIq2MNJqU8Q2lBOhKzpTTIdBBABC' && $_SERVER['PHP_AUTH_PW'] == md5('posbasic@2017#')) 
        {
            $rs=array('status'=>false,'mess'=>'ERROR');
            $_POST['token']=$this->security->get_csrf_hash();
            if ($_POST['token']!=''&&$_POST['device_token']!='') {
                //check login by token
                $rslogin=$this->checkloginByToken($_POST['token'],$_POST['device_token']);
                if ($rslogin)
                {
                    $page=isset($_POST['page'])?$_POST['page']:1;
                    $per_page=isset($_POST['per_page'])?$_POST['per_page']:10;
                    $offset= ($page - 1) * $per_page;

                    if (isset($_POST['brand_id'])) {
                        $brand_id = $_POST['brand_id'];
                    }
                    if (isset($_POST['category_id'])) {
                        $category_id = $_POST['category_id'];
                    } else {            
                        $category_id=0;
                    }
                    if (isset($_POST['subcategory_id'])) {
                        $subcategory_id = $_POST['subcategory_id'];
                    } else {
                        $subcategory_id = NULL;
                    }                   
                    
                    if ($this->Admin||$this->Owner)
                    {
                        if (isset($_POST['warehouse_id'])&&$_POST['warehouse_id']!='') {
                            $warehouse_id =$_POST['warehouse_id'];
                            $objwh=$this->site->getWarehouseByID($warehouse_id);
                            if (empty($objwh)||$objwh==false) {
                                $rs=array('status'=>false,'mess'=>'ID kho hàng (warehouse='.$warehouse_id.') không hợp lệ');  
                                $this->response($rs);
                                return;
                            }
                        } else {
                            $warehouse_id = $this->Settings->default_warehouse;
                        }     

                    }else{
                        $warehouse_id = $this->session->userdata('warehouse_id');
                    }

                    $total_prd = $this->pos_model->fetch_products_api($category_id,NULL,NULL, $subcategory_id, $brand_id,$_POST['keywords'],$_POST['order_by']);
                    $products=$this->ajaxproducts();

                                        

                    $rs=array('status'=>true,'mess'=>'Thành công.','page'=>$page,'per_page'=>$per_page,'count'=>$total_prd,'products'=>$products);                    
                }else{
                    $rs=array('status'=>false,'mess'=>'Token không chính xác.');                    
                }
            }else{
                $rs=array('status'=>false,'mess'=>'Token không hợp lệ');
            }       
        }
        $this->response($rs);
    }    
    public function ajaxproducts($category_id = NULL, $brand_id = NULL)
    {
        $page=isset($_POST['page'])?$_POST['page']:1;
        $per_page=isset($_POST['per_page'])?$_POST['per_page']:10;
        $offset= ($page - 1) * $per_page;
                    

        if (isset($_POST['brand_id'])) {
            $brand_id = $_POST['brand_id'];
        }
        if (isset($_POST['category_id'])) {
            $category_id = $_POST['category_id'];
        } else {            
            $category_id=0;
        }
        if (isset($_POST['subcategory_id'])) {
            $subcategory_id = $_POST['subcategory_id'];
        } else {
            $subcategory_id = NULL;
        }                   
        
        if ($this->Admin||$this->Owner)
        {
            if (isset($_POST['warehouse_id'])) {
                $warehouse_id =$_POST['warehouse_id'];

            } else {
                $warehouse_id = $this->Settings->default_warehouse;
            }     

        }else{
            $warehouse_id = $this->session->userdata('warehouse_id');
        }
               


        $products = $this->pos_model->fetch_products_api($category_id, $per_page, $offset, $subcategory_id, $brand_id,$_POST['keywords'],$_POST['order_by']);

        $pro = 1;
        $prods = [];
        if (!empty($products)) {
            foreach ($products as $product) {
                               
                
                $giaban=$product->price;
                if($product->promotion==1){
                    if(date("Y-m-d",strtotime($product->end_date))>=date("Y-m-d")){ 
                        if($product->promo_price>0){
                            $giaban=$product->promo_price;
                        }
                    }
                }
                if ($this->input->post('customer_id')) {
                    $warehouse = $this->site->getWarehouseByID($warehouse_id);

                    $customer_id = $this->input->post('customer_id', TRUE);
                    $customer = $this->site->getCompanyByID($customer_id);
                    $customer_group = $this->site->getCustomerGroupByID($customer->customer_group_id);

                    if ($product->promotion==1) {
                        if(date("Y-m-d",strtotime($product->end_date))>=date("Y-m-d")){ 
                            if($product->promo_price>0){
                                $giaban=$product->promo_price;
                            }
                        }
                    } elseif ($customer->price_group_id) {
                        if ($pr_group_price = $this->site->getProductGroupPrice($product->id, $customer->price_group_id)) {
                            $giaban = $pr_group_price->price;
                        }
                    } elseif ($warehouse->price_group_id) {
                        if ($pr_group_price = $this->site->getProductGroupPrice($product->id, $warehouse->price_group_id)) {
                            $giaban = $pr_group_price->price;
                        }
                    }

                    $giaban = $giaban + (($giaban * $customer_group->percent) / 100);
                }

                $_str_soluong=0; 
                $phan_nguyen=0; 
                $phan_du=0;                         
                $_str_phandu=0;     
                
                $objunit=$this->site->getdonvitinhById($product->sale_unit);

                $donvi=$this->site->getdonvitinh($product->id);
                $donvi_cha=$this->site->getdonvitinh_capcha($product->code);
                $stock=$this->site->tonkhohientai($product->id, $warehouse_id);
                
                if ($donvi['base_unit']==$donvi_cha['id']) {
                    $_str_phandu=$donvi['operation_value'];

                    switch($donvi['operator']) {                    
                        case '*':           
                            $_str_soluong=(float)$stock*(float)$donvi['operation_value'];    
                            $phan_nguyen=(int)$_str_soluong; 
                            $phan_du=round($_str_soluong,4)-$phan_nguyen;
                            $_str_phandu=$phan_du/(float)$donvi['operation_value']; 
                            $giaban=(float)$giaban*(float)$donvi['operation_value'];  
                            break;          
                         case '/':                      
                            $_str_soluong=(float)$stock/(float)$donvi['operation_value'];
                            $phan_nguyen=(int)$_str_soluong; 
                            $phan_du=round($_str_soluong,4)-$phan_nguyen;
                            $_str_phandu=$phan_du*(float)$donvi['operation_value'];  
                            $giaban=(float)$giaban/(float)$donvi['operation_value'];  
                            break;      
                         case '+':                      
                            $_str_soluong=(float)$stock+(float)$donvi['operation_value'];
                             $phan_nguyen=(int)$_str_soluong; 
                            $phan_du=round($_str_soluong,4)-$phan_nguyen;
                            $_str_phandu=$phan_du-(float)$donvi['operation_value'];  
                            $giaban=(float)$giaban+(float)$donvi['operation_value'];  
                            break;
                        case '-':                   
                            $_str_soluong=(float)$stock-(float)$donvi['operation_value'];
                             $phan_nguyen=(int)$_str_soluong; 
                            $phan_du=round($_str_soluong,4)-$phan_nguyen;
                            $_str_phandu=$phan_du+(float)$donvi['operation_value'];  
                            $giaban=(float)$giaban-(float)$donvi['operation_value'];  
                            break;  
                        break;  
                        }

                }
                   
                $tonkho=$stock;
                $donvitinh=$objunit['name'];

                if((int)$_str_soluong>0){
                    if($phan_du!=0){
                        $tonkho=$phan_nguyen.",".round($_str_phandu,2);
                        $donvitinh=$donvi_cha['name'];
                    }else{
                        $tonkho=$_str_soluong;
                        $donvitinh=$donvi_cha['name'];
                    }                   
                }
                $chophepbanam=false;
                if ($product->type == 'standard' && $this->Settings->overselling==1) {
                    $chophepbanam=true;
                }
                $gia_si=0;
                if ($this->Settings->use_gia_si==1) {
                    $gia_si=$product->gia_si;
                }
                $img=base_url() . "assets/uploads/" . ($product->image!=''?$product->image:'no_image.png');

                $options = $this->pos_model->getProductOptions($product->id, $warehouse_id);
                $options_return=[];
                if (!empty($options)) {

                    foreach ($options as $bienthe)
                    {
                        $tonkho_bienthe=$this->site->tonkhohientai_theobienthe($bienthe->id,$warehouse_id);
                        $giaban_bienthe=$giaban+(double)$bienthe->price;
                        $options_return[]=array('ID'=>$bienthe->id,'name'=>$bienthe->name,'price_addition'=>$this->sma->formatDecimal($bienthe->price),'tonkhohientai'=>$tonkho_bienthe,'price'=>$this->sma->formatDecimal($giaban_bienthe));
                    }
                }


                $prods[]=array('product_id'=>$product->id,'product_type'=>$product->type,'code'=>$product->code,'image'=>$img,'name'=>$product->name,'price'=>$this->sma->formatDecimal($giaban),'tonkhohientai'=>$tonkho,'donvitinh'=>$donvitinh,'chophepbanam'=>$chophepbanam,'gia_si'=>$this->sma->formatDecimal($gia_si),'options'=>$options_return);
               
               
                $pro++;
            }
        }       
        return $prods;
    
    }
    function ProductById_post()
    {        
        $rs=array('status'=>false,'mess'=>'INVALID authentication');
        if ($_SERVER['PHP_AUTH_USER'] == 'CIwiFtn8QMfIq2MNJqU8Q2lBOhKzpTTIdBBABC' && $_SERVER['PHP_AUTH_PW'] == md5('posbasic@2017#')) 
        {
            $rs=array('status'=>false,'mess'=>'ERROR');
            $_POST['token']=$this->security->get_csrf_hash();
            if ($_POST['token']!=''&&$_POST['device_token']!='') {
                //check login by token
                $rslogin=$this->checkloginByToken($_POST['token'],$_POST['device_token']);
                if ($rslogin)
                {
                     if ($this->Admin||$this->Owner)
                    {
                        if (isset($_POST['warehouse_id'])&&$_POST['warehouse_id']!='') {
                            $warehouse_id =$_POST['warehouse_id'];
                            $objwh=$this->site->getWarehouseByID($warehouse_id);
                            if (empty($objwh)||$objwh==false) {
                                $rs=array('status'=>false,'mess'=>'ID kho hàng (warehouse='.$warehouse_id.') không hợp lệ');  
                                $this->response($rs);
                                return;
                            }
                        } else {
                            $warehouse_id = $this->Settings->default_warehouse;
                        }     

                    }else{
                        $warehouse_id = $this->session->userdata('warehouse_id');
                    }
                    $option_id=NULL;
                    if ($_POST['option_id']) {
                        $option_id=$_POST['option_id'];
                    }
                    $product=$this->site->getProductByID($_POST['product_id']);
                    
                    if (isset($_POST['product_code'])&&$_POST['product_code']!='') {
                        
                        //expoild product
                        $check_bienthe=explode("_",$_POST['product_code']);
                        $bienthe_id_scan=0;
                        if (count($check_bienthe)>1)
                        {
                            //bien the = 0
                            $_POST['product_code']=$check_bienthe[0];
                            $bienthe_id_scan=$check_bienthe[1];
                        }
                        $product=$this->site->getProductByCode($_POST['product_code']); 
                        if (empty($product)) {
                            $rs=array('status'=>false,'mess'=>'Không tìm thấy sản phẩm '.$_POST['product_code']);  
                            $this->response($rs);
                            return;
                        }
                    }
                    

                    if ($product!=false) {
                        $giaban=$product->price;
                        if($product->promotion==1){
                            if(date("Y-m-d",strtotime($product->end_date))>=date("Y-m-d")){ 
                                if($product->promo_price>0){
                                    $giaban=$product->promo_price;
                                }
                            }
                        }

                        if ($this->input->post('customer_id')) {
                            $warehouse = $this->site->getWarehouseByID($warehouse_id);

                            $customer_id = $this->input->post('customer_id', TRUE);
                            $customer = $this->site->getCompanyByID($customer_id);
                            $customer_group = $this->site->getCustomerGroupByID($customer->customer_group_id);

                            if ($product->promotion==1) {
                                if(date("Y-m-d",strtotime($product->end_date))>=date("Y-m-d")){ 
                                    if($product->promo_price>0){
                                        $giaban=$product->promo_price;
                                    }
                                }
                            } elseif ($customer->price_group_id) {
                                if ($pr_group_price = $this->site->getProductGroupPrice($product->id, $customer->price_group_id)) {
                                    $giaban = $pr_group_price->price;
                                }
                            } elseif ($warehouse->price_group_id) {
                                if ($pr_group_price = $this->site->getProductGroupPrice($product->id, $warehouse->price_group_id)) {
                                    $giaban = $pr_group_price->price;
                                }
                            }

                            $giaban = $giaban + (($giaban * $customer_group->percent) / 100);
                        }

                        $_str_soluong=0; 
                        $phan_nguyen=0; 
                        $phan_du=0;                         
                        $_str_phandu=0;     
                        
                        $objunit=$this->site->getdonvitinhById($product->sale_unit);

                        $donvi=$this->site->getdonvitinh($product->id);
                        $donvi_cha=$this->site->getdonvitinh_capcha($product->code);
                        $stock=$this->site->tonkhohientai($product->id, $warehouse_id);
                        
                        if ($donvi['base_unit']==$donvi_cha['id']) {
                            $_str_phandu=$donvi['operation_value'];

                            switch($donvi['operator']) {                    
                                case '*':           
                                    $_str_soluong=(float)$stock*(float)$donvi['operation_value'];    
                                    $phan_nguyen=(int)$_str_soluong; 
                                    $phan_du=round($_str_soluong,4)-$phan_nguyen;
                                    $_str_phandu=$phan_du/(float)$donvi['operation_value']; 
                                    $giaban=(float)$giaban*(float)$donvi['operation_value'];  
                                    break;          
                                 case '/':                      
                                    $_str_soluong=(float)$stock/(float)$donvi['operation_value'];
                                    $phan_nguyen=(int)$_str_soluong; 
                                    $phan_du=round($_str_soluong,4)-$phan_nguyen;
                                    $_str_phandu=$phan_du*(float)$donvi['operation_value'];  
                                    $giaban=(float)$giaban/(float)$donvi['operation_value'];  
                                    break;      
                                 case '+':                      
                                    $_str_soluong=(float)$stock+(float)$donvi['operation_value'];
                                     $phan_nguyen=(int)$_str_soluong; 
                                    $phan_du=round($_str_soluong,4)-$phan_nguyen;
                                    $_str_phandu=$phan_du-(float)$donvi['operation_value'];  
                                    $giaban=(float)$giaban+(float)$donvi['operation_value'];  
                                    break;
                                case '-':                   
                                    $_str_soluong=(float)$stock-(float)$donvi['operation_value'];
                                     $phan_nguyen=(int)$_str_soluong; 
                                    $phan_du=round($_str_soluong,4)-$phan_nguyen;
                                    $_str_phandu=$phan_du+(float)$donvi['operation_value'];  
                                    $giaban=(float)$giaban-(float)$donvi['operation_value'];  
                                    break;  
                                break;  
                                }

                        }
                           
                        $tonkho=$stock;
                        $donvitinh=$objunit['name'];

                        if((int)$_str_soluong>0){
                            if($phan_du!=0){
                                $tonkho=$phan_nguyen.",".round($_str_phandu,2);
                                $donvitinh=$donvi_cha['name'];
                            }else{
                                $tonkho=$_str_soluong;
                                $donvitinh=$donvi_cha['name'];
                            }                   
                        }
                        $chophepbanam=false;
                        if ($product->type == 'standard' && $this->Settings->overselling==1) {
                            $chophepbanam=true;
                        }

                        $img=base_url() . "assets/uploads/" . $product->image;

                        $gia_si=0;
                        if ($this->Settings->use_gia_si==1) {
                            $gia_si=$product->gia_si;
                        }
                        $options = $this->pos_model->getProductOptions($product->id, $warehouse_id);
                        $options_return=[];
                        $bienthe_id_scan_barcode=0;
                        if (!empty($options)) {

                            foreach ($options as $bienthe)
                            {
                                $tonkho_bienthe=$this->site->tonkhohientai_theobienthe($bienthe->id,$warehouse_id);
                                $giaban_bienthe=$giaban+(double)$bienthe->price;

                                $options_return[]=array('ID'=>$bienthe->id,'name'=>$bienthe->name,'price_addition'=>$this->sma->formatDecimal($bienthe->price),'tonkhohientai'=>$tonkho_bienthe,'price'=>$this->sma->formatDecimal($giaban_bienthe));   
                                if ($bienthe_id_scan==$bienthe->id)
                                {
                                     $bienthe_id_scan_barcode=$bienthe->id;                                
                                }                             
                            }
                        }

                        $category = $this->site->getCategoryByID($product->category_id);
                        $subcategory = $product->subcategory_id ? $this->site->getCategoryByID($product->subcategory_id) : NULL;

                        $obj=array('product_id'=>$product->id,'product_type'=>$product->type,'code'=>$product->code,'image'=>$img,'name'=>$product->name,'price'=>$this->sma->formatDecimal($giaban),'tonkhohientai'=>$tonkho,'donvitinh'=>$donvitinh,'chophepbanam'=>$chophepbanam,'gia_si'=>$this->sma->formatDecimal($gia_si),'options'=>$options_return,'bienthe_scan'=>$bienthe_id_scan_barcode,'category'=>$category->name,'subcategory'=>($subcategory!=null?$subcategory->name:''));

                         $rs=array('status'=>true,'mess'=>'Thành công.','item'=>$obj); 
                    }else{
                        $rs=array('status'=>false,'mess'=>'Không tìm thấy sản phẩm.','item'=>null); 
                    }

                                       
                }else{
                    $rs=array('status'=>false,'mess'=>'Token không chính xác.');                    
                }
            }else{
                $rs=array('status'=>false,'mess'=>'Token không hợp lệ');
            }       
        }
        $this->response($rs);
    }
    function getProductByIdApi($product_id=0,$customer_id=0,$option_id=0,$warehouse_id=0,$price_promote=NULL)
    {      
        $product=$this->site->getProductByID($product_id);

        if ($product!=false) {
            $giaban=$product->price;
            if($product->promotion==1){
                if(date("Y-m-d",strtotime($product->end_date))>=date("Y-m-d")){ 
                    if($product->promo_price>0){
                        $giaban=$product->promo_price;
                    }
                }
            }
            if ($customer_id>0) {
                $warehouse = $this->site->getWarehouseByID($warehouse_id);

                $customer = $this->site->getCompanyByID($customer_id);
                $customer_group = $this->site->getCustomerGroupByID($customer->customer_group_id);

                if ($product->promotion==1) {
                    if(date("Y-m-d",strtotime($product->end_date))>=date("Y-m-d")){ 
                        if($product->promo_price>0){
                            $giaban=$product->promo_price;
                        }
                    }
                } elseif ($customer->price_group_id) {
                    if ($pr_group_price = $this->site->getProductGroupPrice($product->id, $customer->price_group_id)) {
                        $giaban = $pr_group_price->price;
                    }
                } elseif ($warehouse->price_group_id) {
                    if ($pr_group_price = $this->site->getProductGroupPrice($product->id, $warehouse->price_group_id)) {
                        $giaban = $pr_group_price->price;
                    }
                }

                $giaban = $giaban + (($giaban * $customer_group->percent) / 100);
            }
            if ($price_promote!=NULL) {
                $giaban = $price_promote;
            }            
            $_str_soluong=0; 
            $phan_nguyen=0; 
            $phan_du=0;                         
            $_str_phandu=0;     
            
            $objunit=$this->site->getdonvitinhById($product->sale_unit);

            $donvi=$this->site->getdonvitinh($product->id);
            $donvi_cha=$this->site->getdonvitinh_capcha($product->code);
            $stock=$this->site->tonkhohientai($product->id, $warehouse_id);
            
            if ($donvi['base_unit']==$donvi_cha['id']) {
                $_str_phandu=$donvi['operation_value'];

                switch($donvi['operator']) {                    
                    case '*':           
                        $_str_soluong=(float)$stock*(float)$donvi['operation_value'];    
                        $phan_nguyen=(int)$_str_soluong; 
                        $phan_du=round($_str_soluong,4)-$phan_nguyen;
                        $_str_phandu=$phan_du/(float)$donvi['operation_value']; 
                        $giaban=(float)$giaban*(float)$donvi['operation_value'];  
                        break;          
                     case '/':                      
                        $_str_soluong=(float)$stock/(float)$donvi['operation_value'];
                        $phan_nguyen=(int)$_str_soluong; 
                        $phan_du=round($_str_soluong,4)-$phan_nguyen;
                        $_str_phandu=$phan_du*(float)$donvi['operation_value'];  
                        $giaban=(float)$giaban/(float)$donvi['operation_value'];  
                        break;      
                     case '+':                      
                        $_str_soluong=(float)$stock+(float)$donvi['operation_value'];
                         $phan_nguyen=(int)$_str_soluong; 
                        $phan_du=round($_str_soluong,4)-$phan_nguyen;
                        $_str_phandu=$phan_du-(float)$donvi['operation_value'];  
                        $giaban=(float)$giaban+(float)$donvi['operation_value'];  
                        break;
                    case '-':                   
                        $_str_soluong=(float)$stock-(float)$donvi['operation_value'];
                         $phan_nguyen=(int)$_str_soluong; 
                        $phan_du=round($_str_soluong,4)-$phan_nguyen;
                        $_str_phandu=$phan_du+(float)$donvi['operation_value'];  
                        $giaban=(float)$giaban-(float)$donvi['operation_value'];  
                        break;  
                    break;  
                    }

            }
               
            $tonkho=$stock;
            $donvitinh=$objunit['name'];

            if((int)$_str_soluong>0){
                if($phan_du!=0){
                    $tonkho=$phan_nguyen.",".round($_str_phandu,2);
                    $donvitinh=$donvi_cha['name'];
                }else{
                    $tonkho=$_str_soluong;
                    $donvitinh=$donvi_cha['name'];
                }                   
            }
            $chophepbanam=false;
            if ($product->type == 'standard' && $this->Settings->overselling==1) {
                $chophepbanam=true;
            }

            $img=base_url() . "assets/uploads/" . $product->image;

            $gia_si=0;
            if ($this->Settings->use_gia_si==1) {
                $gia_si=$product->gia_si;
            }
            $options = $this->pos_model->getProductOptions($product->id, $warehouse_id);
            $options_return=[];
            if (!empty($options)) {

                foreach ($options as $bienthe)
                {
                    $tonkho_bienthe=$this->site->tonkhohientai_theobienthe($bienthe->id,$warehouse_id);
                    
                    $giaban_bienthe=$giaban+(double)$bienthe->price;

                    
                    if ($option_id>0) {
                        $options_return=array('ID'=>$bienthe->id,'name'=>$bienthe->name,'price_addition'=>$this->sma->formatDecimal($bienthe->price),'tonkhohientai'=>$tonkho_bienthe,'price'=>$this->sma->formatDecimal($giaban_bienthe),'donvitinh'=>$donvitinh,'chophepbanam'=>$chophepbanam);
                    }else{
                        $options_return[]=array('ID'=>$bienthe->id,'name'=>$bienthe->name,'price_addition'=>$this->sma->formatDecimal($bienthe->price),'tonkhohientai'=>$tonkho_bienthe,'price'=>$this->sma->formatDecimal($giaban_bienthe),'donvitinh'=>$donvitinh,'chophepbanam'=>$chophepbanam);
                    }
                    if ($option_id>0&&$option_id==$bienthe->id) {
                        return $options_return;
                    }
                }
            }

            return array('product_id'=>$product->id,'product_type'=>$product->type,'code'=>$product->code,'image'=>$img,'name'=>$product->name,'price'=>$this->sma->formatDecimal($giaban),'tonkhohientai'=>$tonkho,'donvitinh'=>$donvitinh,'chophepbanam'=>$chophepbanam,'gia_si'=>$this->sma->formatDecimal($gia_si),'options'=>$options_return,'edit_order_item'=>null);

        }
        return false;
    }
     
    function CustomerByID_post()
    {        
        $rs=array('status'=>false,'mess'=>'INVALID authentication');
        if ($_SERVER['PHP_AUTH_USER'] == 'CIwiFtn8QMfIq2MNJqU8Q2lBOhKzpTTIdBBABC' && $_SERVER['PHP_AUTH_PW'] == md5('posbasic@2017#')) 
        {
            $rs=array('status'=>false,'mess'=>'ERROR');
            $_POST['token']=$this->security->get_csrf_hash();
            if ($_POST['token']!=''&&$_POST['device_token']!='') {
                //check login by token
                $rslogin=$this->checkloginByToken($_POST['token'],$_POST['device_token']);
                if ($rslogin)
                {
                    $customer=$this->site->getCompanyByID($_POST['customer_id']);
                    if ($customer!=false) {
                        
                        $this->db->select("{$this->db->dbprefix('companies')}.*,(SELECT COALESCE(sum(grand_total), 0) FROM {$this->db->dbprefix('sales')} WHERE customer_id={$this->db->dbprefix('companies')}.id AND sale_status!='returned') + nobandau - (SELECT COALESCE(sum(paid), 0) FROM {$this->db->dbprefix('sales')} WHERE customer_id={$this->db->dbprefix('companies')}.id AND sale_status!='returned') - (select COALESCE(sum(amount), 0) from scodeweb_payments WHERE id_ncc_id_kh={$this->db->dbprefix('companies')}.id and type='received' AND amount>0) as duno");
                        $this->db->where('id',$customer->id);
                        $cus_obj=$this->db->get('companies')->row_array();

                        $rs=array('status'=>true,'mess'=>'Thành công.','item'=>$cus_obj);                    
                    }else{
                        $rs=array('status'=>false,'mess'=>'Không tìm thấy khách hàng.','item'=>null);                        
                    }
                    
                }else{
                    $rs=array('status'=>false,'mess'=>'Token không chính xác.');                    
                }
            }else{
                $rs=array('status'=>false,'mess'=>'Token không hợp lệ');
            }       
        }
        $this->response($rs);
    }
    public function fillter_customer()
    {

        $this->db->where('group_name', 'customer');
        if ($_POST['keywords']) {
            $this->db->where("(" . $this->db->dbprefix('companies') . ".name LIKE '%" . $_POST['keywords'] . "%' OR phone LIKE '%" . $_POST['keywords'] . "%')");
        }
        if (isset($_POST['fillter'])&&$_POST['fillter']=='TRUE') {
            $this->db->where("(SELECT COALESCE(sum(grand_total), 0) FROM {$this->db->dbprefix('sales')} WHERE customer_id={$this->db->dbprefix('companies')}.id AND sale_status!='returned') + nobandau - (SELECT COALESCE(sum(paid), 0) FROM {$this->db->dbprefix('sales')} WHERE customer_id={$this->db->dbprefix('companies')}.id AND sale_status!='returned') - (select COALESCE(sum(amount), 0) from scodeweb_payments WHERE id_ncc_id_kh={$this->db->dbprefix('companies')}.id and type='received' AND amount>0) >0",NULL,FALSE);            
        }
    }
    public function customers_post()
    {
        $rs=array('status'=>false,'mess'=>'INVALID authentication');
        if ($_SERVER['PHP_AUTH_USER'] == 'CIwiFtn8QMfIq2MNJqU8Q2lBOhKzpTTIdBBABC' && $_SERVER['PHP_AUTH_PW'] == md5('posbasic@2017#')) 
        {
            $rs=array('status'=>false,'mess'=>'ERROR');
            $_POST['token']=$this->security->get_csrf_hash();
            if ($_POST['token']!=''&&$_POST['device_token']!='') {
                //check login by token
                $rslogin=$this->checkloginByToken($_POST['token'],$_POST['device_token']);
                if ($rslogin)
                {
                    
                    $page=isset($_POST['page'])?$_POST['page']:1;
                    $per_page=isset($_POST['per_page'])?$_POST['per_page']:10;
                    $offset= ($page - 1) * $per_page;

                    if ($this->Admin||$this->Owner)
                    {
                        if (isset($_POST['warehouse_id'])&&$_POST['warehouse_id']!='') {
                            $warehouse_id =$_POST['warehouse_id'];
                        } else {
                            $warehouse_id = $this->Settings->default_warehouse;
                        }     

                    }else{
                        $warehouse_id = $this->session->userdata('warehouse_id');
                    } 


                    $this->fillter_customer();
                    $this->db->from("companies");
                    $total=$this->db->count_all_results();

                    $this->db->select("{$this->db->dbprefix('companies')}.*,(SELECT COALESCE(sum(grand_total), 0) FROM {$this->db->dbprefix('sales')} WHERE customer_id={$this->db->dbprefix('companies')}.id AND sale_status!='returned') + nobandau - (SELECT COALESCE(sum(paid), 0) FROM {$this->db->dbprefix('sales')} WHERE customer_id={$this->db->dbprefix('companies')}.id AND sale_status!='returned') - (select COALESCE(sum(amount), 0) from scodeweb_payments WHERE id_ncc_id_kh={$this->db->dbprefix('companies')}.id and type='received' AND amount>0) as duno");
                    
                    $this->fillter_customer();
                    $this->db->limit($per_page, $offset);
                    $this->db->order_by("name", "asc");
                    $query = $this->db->get("companies");

                    if ($query->num_rows() > 0) {
                        foreach ($query->result() as $row) {
                            $data[] = $row;
                        }
                    }
                    $rs=array('status'=>true,'mess'=>'Thành công.','page'=>$page,'per_page'=>$per_page,'total'=>$total,'list'=>$data); 

                }else{ 
                    $rs=array('status'=>false,'mess'=>'Token không chính xác.');                    
                }
            }else{
                $rs=array('status'=>false,'mess'=>'Token không hợp lệ');
            }       
        }
        $this->response($rs);
    }
    function UpdateCustomer_post()
    {        
        $rs=array('status'=>false,'mess'=>'INVALID authentication');
        if ($_SERVER['PHP_AUTH_USER'] == 'CIwiFtn8QMfIq2MNJqU8Q2lBOhKzpTTIdBBABC' && $_SERVER['PHP_AUTH_PW'] == md5('posbasic@2017#')) 
        {
            $rs=array('status'=>false,'mess'=>'ERROR');

            $_POST['token']=$this->security->get_csrf_hash();
            if ($_POST['token']!=''&&$_POST['device_token']!='') {
                //check login by token
                $rslogin=$this->checkloginByToken($_POST['token'],$_POST['device_token']);
                if ($rslogin)
                {
                    
                    if(!$this->Owner && !$this->Admin) {
                        if ($this->data['GP']['customers-edit']!=1) {
                            $rs=array('status'=>false,'mess'=>'Không có quyền');  
                            $this->response($rs);
                            return;
                        }
                    }
                    $rs_obj=$this->site->getCompanyByID($_POST['customer_id']);
                    if ($rs_obj!=false) 
                    {
                        if ($this->input->post('customer_group')=='') {
                            $rs=array('status'=>false,'mess'=>'Vui lòng chọn nhóm khách hàng');  
                        }else if ($this->input->post('price_group')=='') {
                            $rs=array('status'=>false,'mess'=>'Vui lòng chọn nhóm giá');  
                        }else if ($this->input->post('name')=='') {
                            $rs=array('status'=>false,'mess'=>'Vui lòng nhập tên khách hàng');  
                        }else if ($this->input->post('phone')=='') {
                            $rs=array('status'=>false,'mess'=>'Vui lòng nhập số điện thoại');  
                        }else{
                            $cg = $this->site->getCustomerGroupByID($this->input->post('customer_group'));
                            $pg = $this->site->getPriceGroupByID($this->input->post('price_group'));
                            if (empty($cg)) {
                                $rs=array('status'=>false,'mess'=>'Nhóm khách hàng không hợp lệ');  
                            }else if (empty($pg)) {
                                $rs=array('status'=>false,'mess'=>'Nhóm giá hàng không hợp lệ');  
                            }else{
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
                                    'ghichu' => $this->input->post('ghichu')
                                );

                                if ($this->Admin||$this->Owner) {
                                    $data['award_points']=$this->input->post('award_points');
                                    $data['nobandau']=$this->input->post('nobandau');
                                }
                                if ($this->companies_model->updateCompany($_POST['customer_id'], $data)) {
                                    $rs=array('status'=>true,'mess'=>'Thành công');  
                                }else{
                                    $rs=array('status'=>false,'mess'=>'Lỗi cập nhật khách hàng');      
                                }
                                
                            }                   

                        }                     

                    }else{
                        $rs=array('status'=>false,'mess'=>'Không tìm thấy khách hàng');                    
                    }                    
                    
                }else{
                    $rs=array('status'=>false,'mess'=>'Token không chính xác.');                    
                }
            }else{
                $rs=array('status'=>false,'mess'=>'Token không hợp lệ');
            }       
        }        
        
        $this->response($rs);
    }
    function AddCustomer_post()
    {        
        $rs=array('status'=>false,'mess'=>'INVALID authentication');
        if ($_SERVER['PHP_AUTH_USER'] == 'CIwiFtn8QMfIq2MNJqU8Q2lBOhKzpTTIdBBABC' && $_SERVER['PHP_AUTH_PW'] == md5('posbasic@2017#')) 
        {
            $rs=array('status'=>false,'mess'=>'ERROR');

            $_POST['token']=$this->security->get_csrf_hash();
            if ($_POST['token']!=''&&$_POST['device_token']!='') {
                //check login by token
                $rslogin=$this->checkloginByToken($_POST['token'],$_POST['device_token']);
                if ($rslogin)
                {
                    if(!$this->Owner && !$this->Admin) {
                        if ($this->data['GP']['customers-add']!=1) {
                            $rs=array('status'=>false,'mess'=>'Không có quyền');  
                            $this->response($rs);
                            return;
                        }
                    }
                    
                    if ($this->input->post('customer_group')=='') {
                            $rs=array('status'=>false,'mess'=>'Vui lòng chọn nhóm khách hàng');  
                    }else if ($this->input->post('price_group')=='') {
                            $rs=array('status'=>false,'mess'=>'Vui lòng chọn nhóm giá');  
                    }else if ($this->input->post('name')=='') {
                            $rs=array('status'=>false,'mess'=>'Vui lòng nhập tên khách hàng');  
                    }else if ($this->input->post('phone')=='') {
                            $rs=array('status'=>false,'mess'=>'Vui lòng nhập số điện thoại');  
                    }else
                    {
                        $cg = $this->site->getCustomerGroupByID($this->input->post('customer_group'));
                        $pg = $this->site->getPriceGroupByID($this->input->post('price_group'));
                        if (empty($cg)) {
                                $rs=array('status'=>false,'mess'=>'Nhóm khách hàng không hợp lệ');  
                            }else if (empty($pg)) {
                                $rs=array('status'=>false,'mess'=>'Nhóm giá hàng không hợp lệ');  
                            }else{
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
                                    'ghichu' => $this->input->post('ghichu')
                                );

                                if ($this->Admin||$this->Owner) {
                                    $data['award_points']=$this->input->post('award_points');
                                    $data['nobandau']=$this->input->post('nobandau');
                                }
                                if ($cid =$this->companies_model->addCompany($data)) {
                                    $rs=array('status'=>true,'mess'=>'Thành công','customer_id'=>$cid);  
                                }else{
                                    $rs=array('status'=>false,'mess'=>'Lỗi thêm khách hàng');      
                                }                                
                            }
                    }                     
                                                                   
                }else{
                    $rs=array('status'=>false,'mess'=>'Token không chính xác.');                    
                }
            }else{
                $rs=array('status'=>false,'mess'=>'Token không hợp lệ');
            }       
        }        
        
        $this->response($rs);
    }
   
    function DonViGiaoHang_post()
    {        
        $rs=array('status'=>false,'mess'=>'INVALID authentication');
        if ($_SERVER['PHP_AUTH_USER'] == 'CIwiFtn8QMfIq2MNJqU8Q2lBOhKzpTTIdBBABC' && $_SERVER['PHP_AUTH_PW'] == md5('posbasic@2017#')) 
        {
            $rs=array('status'=>false,'mess'=>'ERROR');

            $_POST['token']=$this->security->get_csrf_hash();
            if ($_POST['token']!=''&&$_POST['device_token']!='') {
                //check login by token
                $rslogin=$this->checkloginByToken($_POST['token'],$_POST['device_token']);
                if ($rslogin)
                {
                   
                    $list=$this->site->getAllDoitac();                       

                    $rs=array('status'=>true,'mess'=>'Thành công.','total'=>count($list),'list'=>$list);   
                                     
                }else{
                    $rs=array('status'=>false,'mess'=>'Token không chính xác.');                    
                }
            }else{
                $rs=array('status'=>false,'mess'=>'Token không hợp lệ');
            }       
        }        
        
        $this->response($rs);
    }
    function PhuongThucThanhToan_post()
    {        
        $rs=array('status'=>false,'mess'=>'INVALID authentication');
        if ($_SERVER['PHP_AUTH_USER'] == 'CIwiFtn8QMfIq2MNJqU8Q2lBOhKzpTTIdBBABC' && $_SERVER['PHP_AUTH_PW'] == md5('posbasic@2017#')) 
        {
            $rs=array('status'=>false,'mess'=>'ERROR');

            $_POST['token']=$this->security->get_csrf_hash();
            if ($_POST['token']!=''&&$_POST['device_token']!='') {
                //check login by token
                $rslogin=$this->checkloginByToken($_POST['token'],$_POST['device_token']);
                if ($rslogin)
                {
                   
                    $list=$this->site->getAllSoQuy();                       

                    $rs=array('status'=>true,'mess'=>'Thành công.','total'=>count($list),'list'=>$list);   
                                     
                }else{
                    $rs=array('status'=>false,'mess'=>'Token không chính xác.');                    
                }
            }else{
                $rs=array('status'=>false,'mess'=>'Token không hợp lệ');
            }       
        }        
        
        $this->response($rs);
    }
    function ListVAT_post()
    {        
        $rs=array('status'=>false,'mess'=>'INVALID authentication');
        if ($_SERVER['PHP_AUTH_USER'] == 'CIwiFtn8QMfIq2MNJqU8Q2lBOhKzpTTIdBBABC' && $_SERVER['PHP_AUTH_PW'] == md5('posbasic@2017#')) 
        {
            $rs=array('status'=>false,'mess'=>'ERROR');

            $_POST['token']=$this->security->get_csrf_hash();
            if ($_POST['token']!=''&&$_POST['device_token']!='') {
                //check login by token
                $rslogin=$this->checkloginByToken($_POST['token'],$_POST['device_token']);
                if ($rslogin)
                {
                   
                    $list=$this->site->getAllTaxRates();                       
                    if ($this->Settings->tax2==1) {
                        $rs=array('status'=>true,'mess'=>'Thành công.','total'=>count($list),'list'=>$list);       
                    }else{
                        $rs=array('status'=>false,'mess'=>'Không cài đặt thuế.','total'=>0,'list'=>null);   
                    }
                    
                                     
                }else{
                    $rs=array('status'=>false,'mess'=>'Token không chính xác.');                    
                }
            }else{
                $rs=array('status'=>false,'mess'=>'Token không hợp lệ');
            }       
        }        
        
        $this->response($rs);
    }
    function OrderThanhToan_post()
    {        
        $rs=array('status'=>false,'mess'=>'INVALID authentication');
        if ($_SERVER['PHP_AUTH_USER'] == 'CIwiFtn8QMfIq2MNJqU8Q2lBOhKzpTTIdBBABC' && $_SERVER['PHP_AUTH_PW'] == md5('posbasic@2017#')) 
        {
            $rs=array('status'=>false,'mess'=>'ERROR');

            $_POST['token']=$this->security->get_csrf_hash();
            if ($_POST['token']!=''&&$_POST['device_token']!='') {
                //check login by token
                $rslogin=$this->checkloginByToken($_POST['token'],$_POST['device_token']);
                if ($rslogin)
                {
                    if(!$this->Owner && !$this->Admin) {
                        if ($this->data['GP']['sales-add']!=1) {
                            $rs=array('status'=>false,'mess'=>'Không có quyền');  
                            $this->response($rs);
                            return;
                        }
                    }
                    if ($this->Admin||$this->Owner)
                    {
                        if (isset($_POST['warehouse'])&&$_POST['warehouse']!='') 
                        {
                            $warehouse_id =$_POST['warehouse'];
                            //check valid warehouse
                            $objwh=$this->site->getWarehouseByID($warehouse_id);
                            if (empty($objwh)||$objwh==false) {
                                $rs=array('status'=>false,'mess'=>'ID kho hàng (warehouse='.$warehouse_id.') không hợp lệ');  
                                $this->response($rs);
                                return;
                            }
                        } else {
                            $warehouse_id = $this->Settings->default_warehouse;
                        }     

                    }else{
                        $warehouse_id = $this->session->userdata('warehouse_id');
                    }
                    $biller_id=NULL;    
                    if (isset($_POST['biller'])&&$_POST['biller']>0) {
                        $biller_id =$_POST['biller'];
                    } else {
                        $biller_id = $this->Settings->default_biller;
                    }   

                    $customer_id=NULL;    
                    if (isset($_POST['customer'])&&$_POST['customer']>0) {
                        $customer_id =$_POST['customer'];
                    } else {
                        $customer_id = $this->pos_settings->default_customer;
                    } 
                   
                    $did=$sid = $this->input->post('suspend_id') ? $this->input->post('suspend_id') : NULL;

                    $update_id = $this->input->post('update_id') ? $this->input->post('update_id') : NULL;
                    $suspend = $this->input->post('suspend') ? $this->input->post('suspend') : NULL;
                    
                    if (strtolower($suspend)=='true') 
                    {
                        $suspend=true;                                                
                    }else{
                        $suspend=false;                                                
                    }                    
                    $date = date('Y-m-d H:i:s');

                    $doitac = (int)$this->input->post('doitac');
                    if ($doitac>0) {
                        $obj_doitac=$this->site->getDoitacByID($doitac);
                        if ($obj_doitac==false) {
                            $rs=array('status'=>false,'mess'=>'ID Đối tác giao hàng ('.$doitac.') không hợp lệ');  
                            $this->response($rs);
                            return;
                        }
                    }
                    $total_items = 0;
                    $sale_status = 'completed';
                    $payment_status = 'due';
                    $payment_term = 0;
                    $due_date = date('Y-m-d', strtotime('+' . $payment_term . ' days'));
                    $shipping = $this->input->post('shipping') ? $this->input->post('shipping') : 0;
                    $customer_details = $this->site->getCompanyByID($customer_id);
                    if ($customer_id>0&&$customer_details==false) 
                    {
                        $rs=array('status'=>false,'mess'=>'Không có khách hàng');  
                        $this->response($rs);
                        return;
                    }
                    $customer = $customer_details->company != ''  ? $customer_details->company : $customer_details->name;
                    $biller_details = $this->site->getCompanyByID($biller_id);
                    if ($biller_details==false) 
                    {
                        $rs=array('status'=>false,'mess'=>'Không có nhân viên bán hàng');  
                        $this->response($rs);
                        return;
                    }

                    $biller = $biller_details->company != '-' ? $biller_details->company : $biller_details->name;
                    $note = $this->sma->clear_tags($this->input->post('pos_note'));
                    $staff_note = $this->sma->clear_tags($this->input->post('staff_note'));
                    $reference = $this->site->getReference('pos');

                    $total = 0;
                    $total_weight=0;
                    $product_tax = 0;
                    $order_tax = 0;
                    $product_discount = 0;
                    $order_discount = 0;
                    $percentage = '%';
                    $digital = FALSE;
                    $tongsotienthanhtoan=0;

                    $i = isset($_POST['product_id']) ? sizeof($_POST['product_id']) : 0;

                    for ($r = 0; $r < $i; $r++) {
                        $item_id = $_POST['product_id'][$r];
                        //check san pham
                        $check_product=$this->site->getProductByID($item_id);
                        if (!empty($check_product)) {
                            $item_type = $check_product->type;
                            $item_code = $check_product->code;
                            $item_name = $check_product->name;
                        }else{
                            $rs=array('status'=>false,'mess'=>'Sản phẩm '.$item_id.' không tồn tại');  
                            $this->response($rs);
                            return;
                        }                       
                        
                        $item_comment = $_POST['product_comment'][$r];

                        $item_option = isset($_POST['product_option_id'][$r]) ? $_POST['product_option_id'][$r] : NULL;
                        //check option san pham
                        if ((int)$item_option>0)
                        {
                            $check_option=$this->site->getProductOptionsById($item_id,$item_option);
                            if (empty($check_option)) {                               
                                $rs=array('status'=>false,'mess'=>'Biến thể sản phẩm '.$item_option.' không tồn tại');  
                                $this->response($rs);
                                return;
                            }    
                        }
                        $check_price=$this->getProductByIdApi($item_id,$customer_id,$item_option,$warehouse_id);
                        
                        $giaban=(double)$_POST['product_price'][$r];
                        $data_id_khuyenmai = $_POST['data_id_khuyenmai'][$r];
                        //check gia ban by permission                        
                        if(!$this->Owner && !$this->Admin) {
                            if ($this->data['GP']['edit_price']!=1&&$data_id_khuyenmai>0)
                            {
                                //check gia ban by customer
                                
                                if ($giaban!=$check_price['price']) {
                                    $rs=array('status'=>false,'mess'=>'Giá bán sản phẩm '.$item_name.' là ('.number_format($check_price['price']).') bạn đã nhập không đúng '.number_format($giaban));  
                                    $this->response($rs);
                                    return;
                                }
                            }
                        }
                        $real_unit_price =$this->sma->formatDecimal($giaban);
                        $unit_price = $this->sma->formatDecimal($giaban);

                        $item_unit_quantity = $_POST['product_quantity'][$r];
                        $item_serial = isset($_POST['product_serial'][$r]) ? $_POST['product_serial'][$r] : '';
                        $item_tax_rate =  NULL;
                        $item_discount = NULL;
                        
                        if ($this->Settings->product_discount && ($this->Owner || $this->Admin || $this->session->userdata('allow_discount'))) {
                            $item_discount = isset($_POST['product_discount'][$r]) ? $_POST['product_discount'][$r] : NULL;
                        }

                        $item_unit = $check_product->sale_unit;

                        $item_quantity = $_POST['product_quantity'][$r];

                        //tien hanh check so luong ton kho
                        if ($check_price['tonkhohientai']<0&&$check_price['chophepbanam']==false) {
                            $rs=array('status'=>false,'mess'=>'Sản phẩm '.$item_name.' đã hết tồn kho và không được phép bán âm ');  
                            $this->response($rs);
                            return;
                        }
                        if ($check_price['tonkhohientai']<$item_quantity&&$check_price['chophepbanam']==false) {
                            $rs=array('status'=>false,'mess'=>'Sản phẩm '.$item_name.' chỉ còn tồn ['.($check_price['tonkhohientai']).'] và không được phép bán âm. Bạn được phép bán tối đa ['.$check_price['tonkhohientai'].']');  
                            $this->response($rs);
                            return;
                        }

                        if (isset($item_code) && isset($real_unit_price) && isset($unit_price) && isset($item_quantity)) {
                            $product_details = $check_product;
                            
                            $weight=(float)$product_details->weight;
                            $total_weight+=($weight*$item_quantity);

                            $unit_price = $real_unit_price;
                            $pr_discount = 0;
                           
                            if (isset($item_discount)) {
                                $discount = $item_discount;
                                $dpos = strpos($discount, $percentage);
                                if ($dpos !== FALSE) {
                                    $pds = explode("%", $discount);
                                    $pr_discount = $this->sma->formatDecimal(((($this->sma->formatDecimal($unit_price)) * (Float)($pds[0])) / 100), 4);
                                } else {
                                    $pr_discount = $this->sma->formatDecimal($discount);
                                }
                            }

                           // $unit_price = $this->sma->formatDecimal($unit_price - $pr_discount);
                            $item_net_price = $unit_price;
                            $pr_item_discount = $this->sma->formatDecimal($pr_discount * $item_unit_quantity);
                            $product_discount += $pr_item_discount;
                            $pr_tax = 0;
                            $pr_item_tax = 0;
                            $item_tax = 0;
                            $tax = "";

                            if (isset($item_tax_rate) && $item_tax_rate != 0) {
                                $pr_tax = $item_tax_rate;
                                $tax_details = $this->site->getTaxRateByID($pr_tax);
                                if ($tax_details->type == 1 && $tax_details->rate != 0) {

                                    if ($product_details && $product_details->tax_method == 1) {
                                        $item_tax = $this->sma->formatDecimal((($unit_price) * $tax_details->rate) / 100, 4);
                                        $tax = $tax_details->rate . "%";
                                    } else {
                                        $item_tax = $this->sma->formatDecimal((($unit_price) * $tax_details->rate) / (100 + $tax_details->rate), 4);
                                        $tax = $tax_details->rate . "%";
                                        $item_net_price = $unit_price - $item_tax;
                                    }

                                } elseif ($tax_details->type == 2) {

                                    if ($product_details && $product_details->tax_method == 1) {
                                        $item_tax = $this->sma->formatDecimal((($unit_price) * $tax_details->rate) / 100, 4);
                                        $tax = $tax_details->rate . "%";
                                    } else {
                                        $item_tax = $this->sma->formatDecimal((($unit_price) * $tax_details->rate) / (100 + $tax_details->rate), 4);
                                        $tax = $tax_details->rate . "%";
                                        $item_net_price = $unit_price - $item_tax;
                                    }

                                    $item_tax = $this->sma->formatDecimal($tax_details->rate);
                                    $tax = $tax_details->rate;

                                }
                                $pr_item_tax = $this->sma->formatDecimal(($item_tax * $item_unit_quantity), 4);

                            }

                            $product_tax += $pr_item_tax;
                            $subtotal = (($item_net_price * $item_unit_quantity) + $pr_item_tax)-$pr_item_discount;
                            $unit = $this->site->getUnitByID($item_unit);

                            $products[] = array(
                                'product_id'      => $item_id,
                                'product_code'    => $item_code,
                                'product_name'    => $item_name,
                                'product_type'    => $item_type,
                                'option_id'       => $item_option,
                                'net_unit_price'  => $item_net_price,
                                'unit_price'      => $this->sma->formatDecimal($item_net_price + $item_tax),
                                'quantity'        => $item_quantity,
                                'product_unit_id' => $item_unit,
                                'product_unit_code' => $unit ? $unit->code : NULL,
                                'unit_quantity' => $item_unit_quantity,
                                'warehouse_id'    => $warehouse_id,
                                'item_tax'        => $pr_item_tax,
                                'tax_rate_id'     => $pr_tax,
                                'tax'             => $tax,
                                'discount'        => $item_discount,
                                'item_discount'   => $pr_item_discount,
                                'subtotal'        => $this->sma->formatDecimal($subtotal),
                                'serial_no'       => $item_serial,
                                'real_unit_price' => $real_unit_price,
                                'comment'         => $item_comment,
                                'data_id_khuyenmai'=>$data_id_khuyenmai
                            );
                            $total_items++;
                            $total += $this->sma->formatDecimal(($item_net_price * $item_unit_quantity), 4)-$pr_item_discount;
                        }
                    }
                    
                    if (empty($products)) {
                        $rs=array('status'=>false,'mess'=>'Không có sản phẩm');  
                        $this->response($rs);
                        return;
                    } elseif ($this->pos_settings->item_order == 1) {
                        krsort($products);
                    }
                    if ($this->Settings->product_discount && ($this->Owner || $this->Admin || $this->session->userdata('allow_discount'))) {                            
                            
                        if ($this->input->post('discount')) {
                            $order_discount_id = $this->input->post('discount');
                            $opos = strpos($order_discount_id, $percentage);
                            if ($opos !== FALSE) {
                                $ods = explode("%", $order_discount_id);
                                $order_discount = $this->sma->formatDecimal(((($total + $product_tax) * (Float)($ods[0])) / 100), 4);
                            } else {
                                $order_discount = $this->sma->formatDecimal($order_discount_id);
                            }
                        } else {
                            $order_discount_id = NULL;
                        }
                    }else{
                        $order_discount_id = NULL;
                    }

                    $total_discount = $this->sma->formatDecimal($order_discount + $product_discount);

                    if ($this->Settings->tax2) {
                        $order_tax_id = $this->input->post('order_tax');
                        if ($order_tax_details = $this->site->getTaxRateByID($order_tax_id)) {
                            if ($order_tax_details->type == 2) {
                                $order_tax = $this->sma->formatDecimal($order_tax_details->rate);
                            }
                            if ($order_tax_details->type == 1) {
                                $order_tax = $this->sma->formatDecimal(((($total + $product_tax - $order_discount) * $order_tax_details->rate) / 100), 4);
                            }
                        }
                    } else {
                        $order_tax_id = NULL;
                    }

                    $total_tax = $this->sma->formatDecimal(($product_tax + $order_tax), 4); 
                    $grand_total = $this->sma->formatDecimal(($total + $total_tax + $this->sma->formatDecimal($shipping) - $order_discount), 4);
                    $rounding = 0;
                    if ($this->pos_settings->rounding) {
                        $round_total = $this->sma->roundNumber($grand_total, $this->pos_settings->rounding);
                        $rounding = $this->sma->formatMoney($round_total - $grand_total);
                    }
                    

                    if (!$suspend) {
                        $p = isset($_POST['amount']) ? sizeof($_POST['amount']) : 0;
                        
                        $paid = 0;
                        for ($r = 0; $r < $p; $r++) {

                            if ((float)$_POST['amount'][$r]>(float)$grand_total) {
                               $_POST['amount'][$r]=$grand_total;                            
                            }

                            if (isset($_POST['amount'][$r]) && !empty($_POST['amount'][$r]) && isset($_POST['paid_by'][$r]) && !empty($_POST['paid_by'][$r])) {

                                $amount =  $_POST['amount'][$r];
                                
                                $tongsotienthanhtoan+=$amount;

                                if ($_POST['paid_by'][$r] == 'deposit') {
                                    if ( ! $this->site->check_customer_deposit($customer_id, $amount)) {

                                        $rs=array('status'=>false,'mess'=>lang("amount_greater_than_deposit"));  
                                        $this->response($rs);
                                        return;
                                    }
                                } 
                                if ($_POST['paid_by'][$r] == 'gift_card') {
                                    $gc = $this->site->getGiftCardByNO($_POST['paying_gift_card_no'][$r]);
                                    $amount_paying = $_POST['amount'][$r] >= $gc->balance ? $gc->balance : $_POST['amount'][$r];
                                    $gc_balance = $gc->balance - $amount_paying;
                                    $payment[] = array(
                                        'date'         => $date,
                                        // 'reference_no' => $this->site->getReference('pay'),
                                        'amount'       => $amount,
                                        'paid_by'      => $_POST['paid_by'][$r],
                                        'cheque_no'    => $_POST['cheque_no'][$r],
                                        'cc_no'        => $_POST['paying_gift_card_no'][$r],
                                        'cc_holder'    => $_POST['cc_holder'][$r],
                                        'cc_month'     => $_POST['cc_month'][$r],
                                        'cc_year'      => $_POST['cc_year'][$r],
                                        'cc_type'      => $_POST['cc_type'][$r],
                                        'created_by'   => $this->session->userdata('user_id'),
                                        'warehouse_id'   => $warehouse_id,
                                        'type'         => 'received',
                                        'note'         => $_POST['payment_note'][$r],
                                        'pos_paid'     => $_POST['amount'][$r],
                                        'pos_balance'  => $_POST['balance_amount'][$r],
                                        'gc_balance'  => $gc_balance,
                                        'c_name' => $customer_details->name,
                                        'c_phone' => $customer_details->phone,
                                        'c_address' => $customer_details->address,
                                        );

                                } else {
                                    $payment[] = array(
                                        'date'         => $date,
                                        // 'reference_no' => $this->site->getReference('pay'),
                                        'amount'       => $amount,
                                        'paid_by'      => $_POST['paid_by'][$r],
                                        'cheque_no'    => $_POST['cheque_no'][$r],
                                        'cc_no'        => $_POST['cc_no'][$r],
                                        'cc_holder'    => $_POST['cc_holder'][$r],
                                        'cc_month'     => $_POST['cc_month'][$r],
                                        'cc_year'      => $_POST['cc_year'][$r],
                                        'cc_type'      => $_POST['cc_type'][$r],
                                        'created_by'   => $this->session->userdata('user_id'),
                                        'warehouse_id'   => $warehouse_id,
                                        'type'         => 'received',
                                        'note'         => $_POST['payment_note'][$r],
                                        'pos_paid'     => $_POST['amount'][$r],
                                        'pos_balance'  => $_POST['balance_amount'][$r],
                                        'c_name' => $customer_details->name,
                                        'c_phone' => $customer_details->phone,
                                        'c_address' => $customer_details->address,
                                        );

                                }
         
                            }
                        }
                    }
                    if ($tongsotienthanhtoan>$grand_total) {
                        $tongsotienthanhtoan=$grand_total;
                    }
                    $data = array('date'              => $date,
                                  'reference_no'      => $reference,
                                  'customer_id'       => $customer_id,
                                  'customer'          => $customer,
                                  'biller_id'         => $biller_id,
                                  'biller'            => $biller,
                                  'warehouse_id'      => $warehouse_id,
                                  'note'              => $note,
                                  'doitac'              => $doitac,
                                  'staff_note'        => $staff_note,
                                  'total'             => $total,
                                  'product_discount'  => $product_discount,
                                  'order_discount_id' => $order_discount_id,
                                  'order_discount'    => $order_discount,
                                  'total_discount'    => $total_discount,
                                  'product_tax'       => $product_tax,
                                  'order_tax_id'      => $order_tax_id,
                                  'order_tax'         => $order_tax,
                                  'total_tax'         => $total_tax,
                                  'shipping'          => $this->sma->formatDecimal($shipping),
                                  'grand_total'       => $grand_total,
                                  'total_items'       => $total_items,
                                  'sale_status'       => $sale_status,
                                  'payment_status'    => $payment_status,
                                  'payment_term'      => $payment_term,
                                  'rounding'          => $rounding,
                                  'suspend_note'      => $this->input->post('suspend_note'),
                                  'pos'               => 1,
                                  'paid'              => $tongsotienthanhtoan,
                                  'created_by'        => $this->session->userdata('user_id'),
                                  'total_weight'        => $total_weight,
                    );
                    if (!isset($payment) || empty($payment)) {
                        $payment = array();
                    }

                    //$this->sma->print_arrays($data, $products, $payment,$did);
                    $dlDetails=null;
                    if ((int)$doitac>0) {
                        if(!$this->Owner && !$this->Admin) {
                            if ($this->data['GP']['sales-add_delivery']!=1||$this->data['GP']['sales-edit_delivery']!=1) {
                                $rs=array('status'=>false,'mess'=>'Không có quyền thêm hoặc sửa giao hàng');  
                                $this->response($rs);
                                return;
                            }
                        }


                        //get customer address
                        $kh_obj=$this->site->getCompanyByID($customer_id); 
                        if ($_POST['ship_name']=='') {
                            $rs=array('status'=>false,'mess'=>lang("Vui lòng nhập tên khách nhận hàng"));  
                            $this->response($rs);
                            return;
                        }
                        if ($_POST['ship_phone']=='') {
                            $rs=array('status'=>false,'mess'=>lang("Vui lòng nhập số điện thoại khách nhận hàng"));  
                            $this->response($rs);
                            return;
                        }
                        if ($_POST['ship_address']=='') {
                            $rs=array('status'=>false,'mess'=>lang("Vui lòng nhập địa chỉ khách nhận hàng"));  
                            $this->response($rs);
                            return;
                        }
                        if ($_POST['ship_time']=='') {
                            $rs=array('status'=>false,'mess'=>lang("Vui lòng nhập thời gian khách nhận hàng"));  
                            $this->response($rs);
                            return;
                        }
                        if ($_POST['ship_date']=='') {
                            $rs=array('status'=>false,'mess'=>lang("Vui lòng nhập ngày khách nhận hàng"));  
                            $this->response($rs);
                            return;
                        }
                        $shiptime=date("H:i:s",strtotime($_POST['ship_time']));

                        $shipdate=date("Y-m-d",strtotime(str_replace("/","-",$_POST['ship_date'])));

                        $date_ship=date("Y-m-d H:i:s",strtotime($shipdate.' '.$shiptime));

                        $dlDetails = array(
                        'date' => $date,
                        'ngaynhan' => $date_ship,
                        'do_reference_no' =>$this->site->getReference('do'),
                        'sale_reference_no' => $reference,
                        'customer' => $_POST['ship_name'],
                        'address' => $_POST['ship_address'],
                        'phone' => $_POST['ship_phone'],
                        'status' => 'packing',
                        'delivered_by' => $doitac,
                        'shipping' => $this->sma->formatDecimal($shipping),
                        'received_by' => '',
                        'note' => $_POST['ship_note'],
                        'created_by' => $this->session->userdata('user_id'),
                        'warehouse_id' => $warehouse_id,
                        );
                    }

                    if (!empty($products) && !empty($data)) {
                        if ($suspend==true) {
                            if ($sid>0) {
                                $suspended_sale = $this->pos_model->getOpenBillByID($sid);
                                if ($suspended_sale==false)
                                {
                                    $rs=array('status'=>false,'mess'=>'Không tìm thấy hóa đơn chờ '.$sid);  
                                    $this->response($rs);
                                    return;           
                                }else if ($this->pos_model->suspendSaleApi($data, $products, $did)>0) {
                                    $this->session->set_userdata('remove_posls', 1);

                                    $rs=array('status'=>true,'mess'=>'Cập nhật hóa đơn chờ thành công');  
                                    $this->response($rs);
                                    return;
                                }else{
                                    $rs=array('status'=>false,'mess'=>'Lỗi khi lưu dữ liệu ');  
                                    $this->response($rs);
                                    return;  
                                }    
                            }else{
                                $add_id=$this->pos_model->suspendSaleApi($data, $products, $did);
                                if ($add_id>0) {

                                    $this->session->set_userdata('remove_posls', 1);
                                    $rs=array('status'=>true,'mess'=>'Đã thêm vào hóa đơn chờ thành công','suspend_id'=>$add_id);  
                                    $this->response($rs);
                                    return;
                                }else{
                                    $rs=array('status'=>false,'mess'=>'Lỗi khi lưu dữ liệu ');  
                                    $this->response($rs);
                                    return;  
                                }
                               
                            }

                        } else {
                            //check update
                            if ($update_id>0)
                            {
                                $update_obj=$this->site->getInvoiceByID($update_id);
                                if(!$this->Owner && !$this->Admin) {
                                    if (!empty($update_obj)) {
                                        
                                        if ($update_obj->api_id==0&&$update_obj->is_web==0) {
                                            if ($update_obj->created_by!=$this->session->userdata('user_id')) {
                                                $rs=array('status'=>false,'mess'=>'Không có quyền sửa đơn hàng này');  
                                                $this->response($rs);
                                                return;
                                            }
                                        }
                                    }                                    
                                }
                                $data['reference_no'] = $update_obj->reference_no;

                                if ($update_obj==false)
                                {
                                    $rs=array('status'=>false,'mess'=>'Không tìm thấy hóa đơn '.$update_id);  
                                    $this->response($rs);
                                    return;           
                                }else if ($this->sales_model->updateSale($update_id, $data, $products,$dlDetails)) {
                                    // update payment
                                    // $payment
                                    
                                    $this->sales_model->updatePaymentAPI($payment,$update_id,$data['customer_id']);

                                    $this->session->set_userdata('remove_posls', 1);

                                    if ($update_obj->api_id==0) {
                                        //cap nhat trang thai cho don hang web
                                        $this->SysnApiWoooUpdateStatusOrder($update_obj->sale_status,$update_id);
                                    }
                                   

                                    $rs=array('status'=>true,'mess'=>'Cập nhật hóa đơn thành công');  
                                    $this->response($rs);
                                    return;
                                }else{
                                    $rs=array('status'=>false,'mess'=>'Lỗi khi lưu dữ liệu ');  
                                    $this->response($rs);
                                    return;  
                                }
                                  
                            }
                            
                            if ($sale = $this->pos_model->addSale($data, $products, $payment, $did,$dlDetails)) {
                                $this->session->set_userdata('remove_posls', 1);
                                $msg = $this->lang->line("sale_added");
                                if (!empty($sale['message'])) {
                                    foreach ($sale['message'] as $m) {
                                        $msg .= '<br>' . $m;
                                    }
                                }
                               
                                $rs=array('status'=>true,'mess'=>$msg,'sale_id'=>$sale['sale_id'],'delivery_id'=>$sale['delivery_id']);  
                                $this->response($rs);
                                return;
                            }else{
                                $rs=array('status'=>false,'mess'=>'Lỗi khi lưu dữ liệu ');  
                                $this->response($rs);
                                return;  
                            }
                        }
                    } else {
                        
                        $rs=array('status'=>false,'mess'=>'Thông tin thanh toán hoặc sản phẩm không hợp lệ');  
                        $this->response($rs);
                        return;
                        
                    }
                                                                   
                }else{
                    $rs=array('status'=>false,'mess'=>'Token không chính xác.');                    
                }
            }else{
                $rs=array('status'=>false,'mess'=>'Token không hợp lệ');
            }       
        }        
        
        $this->response($rs);
    }
    function ListHoaDonTam_post()
    {        
        $rs=array('status'=>false,'mess'=>'INVALID authentication');
        if ($_SERVER['PHP_AUTH_USER'] == 'CIwiFtn8QMfIq2MNJqU8Q2lBOhKzpTTIdBBABC' && $_SERVER['PHP_AUTH_PW'] == md5('posbasic@2017#')) 
        {            

            $rs=array('status'=>false,'mess'=>'ERROR');

            $_POST['token']=$this->security->get_csrf_hash();
            if ($_POST['token']!=''&&$_POST['device_token']!='') {
                //check login by token
                $rslogin=$this->checkloginByToken($_POST['token'],$_POST['device_token']);
                if ($rslogin)
                { 
                    if ($this->Admin||$this->Owner)
                    {
                        if (isset($_POST['warehouse'])&&$_POST['warehouse']!='') 
                        {
                            $warehouse_id =$_POST['warehouse'];
                            $objwh=$this->site->getWarehouseByID($warehouse_id);
                            if (empty($objwh)||$objwh==false) {
                                $rs=array('status'=>false,'mess'=>'ID kho hàng (warehouse='.$warehouse_id.') không hợp lệ');  
                                $this->response($rs);
                                return;
                            }
                        } else {
                            $warehouse_id = 0;
                        }     

                    }else{
                        $warehouse_id = $this->session->userdata('warehouse_id');
                    }

                    $page=isset($_POST['page'])?$_POST['page']:1;
                    $per_page=isset($_POST['per_page'])?$_POST['per_page']:10;
                    $offset= ($page - 1) * $per_page;

                    //total
                    $bills_total = $this->pos_model->fetch_bills_api(0,0,$warehouse_id);
                    $total=count($bills_total);

                    $bills = $this->pos_model->fetch_bills_api($per_page, $offset,$warehouse_id);

                    if (!empty($bills)) {
                        foreach ($bills as $bill)
                        {
                            $bill->user_obj=$this->site->getUser($bill->created_by);
                        }
                    }
 
                    $rs=array('status'=>true,'mess'=>'Thành công.','page'=>$page,'per_page'=>$per_page,'count'=>$total,'list'=>$bills); 
                                                           
                }else{ 
                    $rs=array('status'=>false,'mess'=>'Token không chính xác.');                    
                }
            }else{
                $rs=array('status'=>false,'mess'=>'Token không hợp lệ');
            }       
        }       
        $this->response($rs);
    }
    function getTamTinhById_post()
    {
        $rs=array('status'=>false,'mess'=>'INVALID authentication');
        if ($_SERVER['PHP_AUTH_USER'] == 'CIwiFtn8QMfIq2MNJqU8Q2lBOhKzpTTIdBBABC' && $_SERVER['PHP_AUTH_PW'] == md5('posbasic@2017#')) 
        {
            $rs=array('status'=>false,'mess'=>'ERROR');
            $_POST['token']=$this->security->get_csrf_hash();
            if ($_POST['token']!=''&&$_POST['device_token']!='') {
                //check login by token
                $rslogin=$this->checkloginByToken($_POST['token'],$_POST['device_token']);
                if ($rslogin)
                {
                    
                    if (isset($_POST['sid'])) 
                    {
                        $sid=$_POST['sid'];
                        if ($suspended_sale = $this->pos_model->getOpenBillByID($sid)) {
                            $inv_items = $this->pos_model->getSuspendedSaleItems($sid);
                            krsort($inv_items);
                       
                            foreach ($inv_items as $item) {
                                $obj_prod = $this->getProductByIdApi($item->product_id,$suspended_sale->customer_id,NULL,$suspended_sale->warehouse_id);                                
                               
                                $_prod_detail=$obj_prod['name'];
                                if ($item->data_id_khuyenmai>0)
                                {
                                    $obj_km=$this->site->getKhuyenmaiById($item->data_id_khuyenmai);
                                    if ($obj_km!=false) {
                                        $_prod_detail.=" (".$obj_km->tenevent.")";    
                                    }else{
                                        $_prod_detail.=" (KHUYẾN MÃI)";
                                    }                                                                    
                                }                                
                                $obj_prod['name']=$_prod_detail;

                                $order_item['product_id']=$item->product_id;
                                $order_item['product_comments']=$item->comment;
                                $order_item['product_option_id']=$item->option_id;
                                $order_item['product_price']=$item->unit_price;
                                $order_item['product_quantity']=$item->unit_quantity;
                                $order_item['product_serial']=$item->serial_no;
                                $order_item['product_discount_value']=$item->discount;
                                $order_item['product_discount']=$item->item_discount;
                                $order_item['data_id_khuyenmai']=$item->data_id_khuyenmai;

                                $obj_prod['edit_order_item']=$order_item;
                                $pr[]=$obj_prod;
                            }

                            $this->data=null;
                            $this->data['saleitems'] = $pr; 
                            $this->data['sid'] = $sid;
                            $this->data['suspend_sale'] = $suspended_sale;                            
                            $this->data['user_obj'] = $this->site->getUser($suspended_sale->created_by);
                            $this->data['customer'] = $this->pos_model->getCompanyByID($suspended_sale->customer_id);
                            $this->data['ghichu'] = $suspended_sale->suspend_note;

                            $rs=array('status'=>true,'mess'=>'Thành công.','item'=>$this->data); 

                        } else {
                           $rs=array('status'=>false,'mess'=>'Không tìm thấy hóa đơn.','item'=>null); 
                        }
                    }else{
                        $rs=array('status'=>false,'mess'=>'Không tìm thấy hóa đơn.','item'=>null); 
                    }
                  
                    
                }else{
                    $rs=array('status'=>false,'mess'=>'Token không chính xác.');                    
                }
            }else{
                $rs=array('status'=>false,'mess'=>'Token không hợp lệ');
            }       
        }
        $this->response($rs);
    }
    function HuyTamTinh_post()
    {
        $rs=array('status'=>false,'mess'=>'INVALID authentication');
        if ($_SERVER['PHP_AUTH_USER'] == 'CIwiFtn8QMfIq2MNJqU8Q2lBOhKzpTTIdBBABC' && $_SERVER['PHP_AUTH_PW'] == md5('posbasic@2017#')) 
        {
            $rs=array('status'=>false,'mess'=>'ERROR');
            $_POST['token']=$this->security->get_csrf_hash();
            if ($_POST['token']!=''&&$_POST['device_token']!='') {
                //check login by token
                $rslogin=$this->checkloginByToken($_POST['token'],$_POST['device_token']);
                if ($rslogin)
                {
                    if(!$this->Owner && !$this->Admin) {
                        if ($this->data['GP']['sales-delete']!=1) {
                            $rs=array('status'=>false,'mess'=>'Không có quyền');  
                            $this->response($rs);
                            return;
                        }
                    }                    
                    if (isset($_POST['tamtinh_id'])) 
                    {
                        $sid=$_POST['tamtinh_id'];
                        if ($suspended_sale = $this->pos_model->getOpenBillByID($sid)) {                           
                            
                            $check=$this->pos_model->deleteBill($sid);
                            if ($check) {
                                $rs=array('status'=>true,'mess'=>'Thành công.','item'=>$suspended_sale); 
                            }else{
                                $rs=array('status'=>false,'mess'=>'Lỗi xóa hóa đơn tạm.','item'=>null);     
                            }                            
                        } else {
                           $rs=array('status'=>false,'mess'=>'Không tìm thấy hóa đơn.','item'=>null); 
                        }
                    }else{
                        $rs=array('status'=>false,'mess'=>'Không tìm thấy hóa đơn.','item'=>null); 
                    }
                  
                    
                }else{
                    $rs=array('status'=>false,'mess'=>'Token không chính xác.');                    
                }
            }else{
                $rs=array('status'=>false,'mess'=>'Token không hợp lệ');
            }       
        }
        $this->response($rs);
    }
    public function fillter_order()
    {
        
        if ($_POST['keywords']) {
            $this->db->where("(" . $this->db->dbprefix('sales') . ".reference_no LIKE '%" . $_POST['keywords'] . "%' OR customer LIKE '%" . $_POST['keywords'] . "%')");
        }
        if ($this->Admin||$this->Owner)
        {
            if (isset($_POST['warehouse'])&&$_POST['warehouse']!='') 
            {
                $warehouse_id =$_POST['warehouse'];                
            } else {
                $warehouse_id = 0;
            }     

        }else{
            $warehouse_id = $this->session->userdata('warehouse_id');
            //if view_right
            if (!$this->session->userdata('view_right')) {
                $this->db->where('sales.created_by', $this->session->userdata('user_id'));    
            }            
        }
        if ($warehouse_id>0) {
            $this->db->where('sales.warehouse_id', $warehouse_id);
        }
        $doitac=isset($_POST['doitac'])?$_POST['doitac']:NULL;
        if ($doitac>0) {
            $this->db->where('sales.doitac', $doitac);
        }
        $this->db->where('sales.sale_status <>','returned');
        $sale_status=isset($_POST['sale_status'])?$_POST['sale_status']:NULL;
        if ($sale_status!='') {
            $this->db->where('sales.sale_status', $sale_status);
        }
        $payment_status=isset($_POST['payment_status'])?$_POST['payment_status']:NULL;
        if ($payment_status!='') {
            $this->db->where('sales.payment_status', $payment_status);
        }
        $customer_id=isset($_POST['customer_id'])?$_POST['customer_id']:NULL;
        if ($customer_id>0) {
            $this->db->where('sales.customer_id', $customer_id);
        }

        $start='';
        if (isset($_POST['start'])&&$_POST['start']!='') {
            $start=date("Y-m-d",strtotime(str_replace("/","-",$_POST['start']))).' 00:00:00';
        }
        $end='';
        if (isset($_POST['end'])&&$_POST['end']!='') {
            $end=date("Y-m-d",strtotime(str_replace("/","-",$_POST['end']))).' 23:59:59';
        }
        if ($start!=''&&$end!='') {
            $this->db->where($this->db->dbprefix('sales').'.date BETWEEN "' . $start . '" and "' . $end . '"');
        }
        
    }
    function ListHoaDon_post()
    {        
        $rs=array('status'=>false,'mess'=>'INVALID authentication');
        if ($_SERVER['PHP_AUTH_USER'] == 'CIwiFtn8QMfIq2MNJqU8Q2lBOhKzpTTIdBBABC' && $_SERVER['PHP_AUTH_PW'] == md5('posbasic@2017#')) 
        {            

            $rs=array('status'=>false,'mess'=>'ERROR');

            $_POST['token']=$this->security->get_csrf_hash();
            if ($_POST['token']!=''&&$_POST['device_token']!='') {
                //check login by token
                $rslogin=$this->checkloginByToken($_POST['token'],$_POST['device_token']);
                if ($rslogin)
                { 
                    if ($this->Admin||$this->Owner)
                    {
                        if (isset($_POST['warehouse'])&&$_POST['warehouse']!='') 
                        {
                            $warehouse_id =$_POST['warehouse'];
                            $objwh=$this->site->getWarehouseByID($warehouse_id);
                            if (empty($objwh)||$objwh==false) {
                                $rs=array('status'=>false,'mess'=>'ID kho hàng (warehouse='.$warehouse_id.') không hợp lệ');  
                                $this->response($rs);
                                return;
                            }
                        } else {
                            $warehouse_id = $this->Settings->default_warehouse;
                        }     

                    }else{
                        $warehouse_id = $this->session->userdata('warehouse_id');
                    }

                    $page=isset($_POST['page'])?$_POST['page']:1;
                    $per_page=isset($_POST['per_page'])?$_POST['per_page']:10;
                    $offset= ($page - 1) * $per_page;

                    $doitac=isset($_POST['doitac'])?$_POST['doitac']:NULL;
                    $doitac_obj = $this->site->getDoitacByID($doitac);
                    if ($doitac>0&&$doitac_obj==false) 
                    {
                        $rs=array('status'=>false,'mess'=>'Đối tác không hợp lệ');  
                        $this->response($rs);
                        return;
                    }

                    $sale_status=isset($_POST['sale_status'])?$_POST['sale_status']:NULL;
                    $payment_status=isset($_POST['payment_status'])?$_POST['payment_status']:NULL;
                    $customer_id=isset($_POST['customer_id'])?$_POST['customer_id']:NULL;
                    $customer_details = $this->site->getCompanyByID($customer_id);
                    if ($customer_id>0&&$customer_details==false) 
                    {
                        $rs=array('status'=>false,'mess'=>'Không có khách hàng');  
                        $this->response($rs);
                        return;
                    }
                    //total
                    $this->db->select("sales.id as id")->from('sales');
                    $this->fillter_order();
                    $q = $this->db->get(); 
                    $total=$q->num_rows();

                    $this->db
                    ->select("sales.id as id, DATE_FORMAT(scodeweb_sales.date, '%Y-%m-%d %T') as date, reference_no,doitac.name as doitac_name,sales.doitac,warehouses.name as kho,CONCAT(" . $this->db->dbprefix('users') . ".first_name, ' ', " . $this->db->dbprefix('users') . ".last_name) as biller,scodeweb_sales.customer_id,customer, sale_status, grand_total, paid, (grand_total-paid) as balance, payment_status, sales.attachment, scodeweb_sales.created_by,scodeweb_sales.pos,scodeweb_sales.is_web,scodeweb_sales.api_id")
                    ->from('sales')
                    ->join('doitac', 'doitac.id=sales.doitac', 'left')
                    ->join('users', 'users.id=sales.created_by', 'left')
                    ->join('warehouses', 'warehouses.id=sales.warehouse_id', 'left');
                    
                    $this->fillter_order();

                    $this->db->limit($per_page,$offset);
                    
                    $this->db->order_by('sales.date','desc');

                    $q = $this->db->get();            
                    if ($q->num_rows() > 0) {
                        foreach (($q->result()) as $row) {
                            $bills[] = $row;
                        }
                    } else {
                        $bills = NULL;
                    }
                   // echo var_dump($bills);
                    if (!empty($bills)) {
                        foreach ($bills as $bill)
                        {
                            $type='sale';
                            if ($bill->pos==1) {
                                $type='pos';
                            }else if ($bill->is_web>0) {
                                $type='web';
                            }else if ($bill->api_id>0) {
                                $type='tmdt';
                            }
                            $bill->type=$type;
                            $bill->user_obj=$this->site->getUser($bill->created_by);
                        }
                    }
 
                    $rs=array('status'=>true,'mess'=>'Thành công.','page'=>$page,'per_page'=>$per_page,'count'=>$total,'list'=>$bills); 
                    
                                       
                }else{ 
                    $rs=array('status'=>false,'mess'=>'Token không chính xác.');                    
                }
            }else{
                $rs=array('status'=>false,'mess'=>'Token không hợp lệ');
            }       
        }       
        $this->response($rs);
    }
    function getOrderById_post()
    {
        $rs=array('status'=>false,'mess'=>'INVALID authentication');
        if ($_SERVER['PHP_AUTH_USER'] == 'CIwiFtn8QMfIq2MNJqU8Q2lBOhKzpTTIdBBABC' && $_SERVER['PHP_AUTH_PW'] == md5('posbasic@2017#')) 
        {
            $rs=array('status'=>false,'mess'=>'ERROR');
            $_POST['token']=$this->security->get_csrf_hash();
            if ($_POST['token']!=''&&$_POST['device_token']!='') {
                //check login by token
                $rslogin=$this->checkloginByToken($_POST['token'],$_POST['device_token']);
                if ($rslogin)
                {
                    
                    if (isset($_POST['order_id'])) 
                    {
                        $sid=$_POST['order_id'];
                        if ($order = $this->site->getInvoiceByID($sid)) {
                            $inv_items = $this->site->getAllInvoiceItems($sid);
                            krsort($inv_items);
                        
                            foreach ($inv_items as $item) {
                                
                                $obj_prod = $this->getProductByIdApi($item->product_id,$order->customer_id,NULL,$order->warehouse_id);
                                
                                $_prod_detail=$obj_prod['name'];
                                if ($item->data_id_khuyenmai>0)
                                {
                                    $obj_km=$this->site->getKhuyenmaiById($item->data_id_khuyenmai);
                                    if ($obj_km!=false) {
                                        $_prod_detail.=" (".$obj_km->tenevent.")";    
                                    }else{
                                        $_prod_detail.=" (KHUYẾN MÃI)";
                                    }                                                                    
                                }                                
                                $obj_prod['name']=$_prod_detail;

                                $order_item['product_id']=$item->product_id;
                                $order_item['product_comments']=$item->comment;
                                $order_item['product_option_id']=$item->option_id;
                                $order_item['product_price']=$item->unit_price;
                                $order_item['product_quantity']=$item->unit_quantity;
                                $order_item['product_serial']=$item->serial_no;
                                $order_item['product_discount_value']=$item->discount;
                                $order_item['product_discount']=$item->item_discount;
                                $order_item['data_id_khuyenmai']=$item->data_id_khuyenmai;

                                $obj_prod['edit_order_item']=$order_item;
                                $pr[]=$obj_prod;
                            }
                            $type='sale';
                            if ($order->pos==1) {
                                $type='pos';
                            }else if ($order->is_web>0) {
                                $type='web';
                            }else if ($order->api_id>0) {
                                $type='tmdt';
                            }
                            $order->type=$type;

                            $this->data=null;
                            $this->data['saleitems'] = $pr; 
                            $this->data['sid'] = $sid;
                            $this->data['order'] = $order;                            
                            $this->data['user_obj'] = $this->site->getUser($order->created_by);
                            $this->data['customer'] = $this->pos_model->getCompanyByID($order->customer_id);
                            $this->data['ghichu'] = $order->note;

                            $rs=array('status'=>true,'mess'=>'Thành công.','item'=>$this->data); 

                        } else {
                           $rs=array('status'=>false,'mess'=>'Không tìm thấy hóa đơn.','item'=>null); 
                        }
                    }else{
                        $rs=array('status'=>false,'mess'=>'Không tìm thấy hóa đơn.','item'=>null); 
                    }
                  
                    
                }else{
                    $rs=array('status'=>false,'mess'=>'Token không chính xác.');                    
                }
            }else{
                $rs=array('status'=>false,'mess'=>'Token không hợp lệ');
            }       
        }
        $this->response($rs);
    }
    function HuyOrder_post()
    {
        $rs=array('status'=>false,'mess'=>'INVALID authentication');
        if ($_SERVER['PHP_AUTH_USER'] == 'CIwiFtn8QMfIq2MNJqU8Q2lBOhKzpTTIdBBABC' && $_SERVER['PHP_AUTH_PW'] == md5('posbasic@2017#')) 
        {
            $rs=array('status'=>false,'mess'=>'ERROR');
            $_POST['token']=$this->security->get_csrf_hash();
            if ($_POST['token']!=''&&$_POST['device_token']!='') {
                //check login by token
                $rslogin=$this->checkloginByToken($_POST['token'],$_POST['device_token']);
                if ($rslogin)
                {
                    if(!$this->Owner && !$this->Admin) {
                        if ($this->data['GP']['sales-delete']!=1) {
                            $rs=array('status'=>false,'mess'=>'Không có quyền');  
                            $this->response($rs);
                            return;
                        }
                    }                    
                    if (isset($_POST['order_id'])) 
                    {
                        $sid=$_POST['order_id'];
                        if ($order = $this->site->getInvoiceByID($sid)) {                           
                            if(!$this->Owner && !$this->Admin) {
                                if ($order->created_by!=$this->session->userdata('user_id')) {
                                    $rs=array('status'=>false,'mess'=>'Không có quyền xóa đơn hàng này');  
                                    $this->response($rs);
                                    return;
                                }
                            } 

                            $check=$this->sales_model->deleteSale($sid);
                            if ($check) {
                                $rs=array('status'=>true,'mess'=>'Thành công.','item'=>$order); 
                            }else{
                                $rs=array('status'=>false,'mess'=>'Lỗi xóa hóa đơn.','item'=>null);     
                            }                            
                        } else {
                           $rs=array('status'=>false,'mess'=>'Không tìm thấy hóa đơn.','item'=>null); 
                        }
                    }else{
                        $rs=array('status'=>false,'mess'=>'Không tìm thấy hóa đơn.','item'=>null); 
                    }
                  
                    
                }else{
                    $rs=array('status'=>false,'mess'=>'Token không chính xác.');                    
                }
            }else{
                $rs=array('status'=>false,'mess'=>'Token không hợp lệ');
            }       
        }
        $this->response($rs);
    }
    function PrintOrderById_post()
    {
        $rs=array('status'=>false,'mess'=>'INVALID authentication');
        if ($_SERVER['PHP_AUTH_USER'] == 'CIwiFtn8QMfIq2MNJqU8Q2lBOhKzpTTIdBBABC' && $_SERVER['PHP_AUTH_PW'] == md5('posbasic@2017#')) 
        {
            $rs=array('status'=>false,'mess'=>'ERROR');
            $_POST['token']=$this->security->get_csrf_hash();
            if ($_POST['token']!=''&&$_POST['device_token']!='') {
                //check login by token
                $rslogin=$this->checkloginByToken($_POST['token'],$_POST['device_token']);
                if ($rslogin)
                {
                                     
                    if (isset($_POST['order_id'])) 
                    {
                        $sid=$_POST['order_id'];
                        if ($order = $this->site->getInvoiceByID($sid)) {                           
                            
                            $check=$this->print_order($sid);
                            if ($check!=false) {
                                $rs=array('status'=>true,'mess'=>'Thành công.','item'=>$check); 
                            }else{
                                $rs=array('status'=>false,'mess'=>'Lỗi in hóa đơn.','item'=>null);     
                            }                            
                        } else {
                           $rs=array('status'=>false,'mess'=>'Không tìm thấy hóa đơn.','item'=>null); 
                        }
                    }else{
                        $rs=array('status'=>false,'mess'=>'Không tìm thấy hóa đơn.','item'=>null); 
                    }
                  
                    
                }else{
                    $rs=array('status'=>false,'mess'=>'Token không chính xác.');                    
                }
            }else{
                $rs=array('status'=>false,'mess'=>'Token không hợp lệ');
            }       
        }
        $this->response($rs);
    }

    public function barcode($text = NULL, $bcs = 'code128', $height = 50)
    {
        return site_url('products/gen_barcode/' . $text . '/' . $bcs . '/' . $height);
    }
    public function fillter_order_web()
    {
        
        if ($_POST['keywords']) {
            $this->db->where("(" . $this->db->dbprefix('sales') . ".reference_no LIKE '%" . $_POST['keywords'] . "%' OR customer LIKE '%" . $_POST['keywords'] . "%')");
        }
        if ($this->Admin||$this->Owner)
        {
            if (isset($_POST['warehouse'])&&$_POST['warehouse']!='') 
            {
                $warehouse_id =$_POST['warehouse'];                
            } else {
                $warehouse_id = 0;
            }     

        }else{
           // $warehouse_id = $this->session->userdata('warehouse_id');
            //web hien thi tat ca don            
        }
        if ($warehouse_id>0) {
            $this->db->where('sales.warehouse_id', $warehouse_id);
        }
        $doitac=isset($_POST['doitac'])?$_POST['doitac']:NULL;

        if ($doitac>0) {
            $this->db->where('sales.doitac', $doitac);
        }
        //chi lay danh sach don hang chua xu ly
        $this->db->where('sales.sale_status','pending');        
        $this->db->where('sales.api_id>',0);
        $this->db->or_where('sales.is_web>',0);
        
        $payment_status=isset($_POST['payment_status'])?$_POST['payment_status']:NULL;
        if ($payment_status!='') {
            $this->db->where('sales.payment_status', $payment_status);
        }
        $customer_id=isset($_POST['customer_id'])?$_POST['customer_id']:NULL;
        if ($customer_id>0) {
            $this->db->where('sales.customer_id', $customer_id);
        }

        $start='';
        if (isset($_POST['start'])&&$_POST['start']!='') {
            $start=date("Y-m-d",strtotime(str_replace("/","-",$_POST['start']))).' 00:00:00';
        }
        $end='';
        if (isset($_POST['end'])&&$_POST['end']!='') {
            $end=date("Y-m-d",strtotime(str_replace("/","-",$_POST['end']))).' 23:59:59';
        }
        if ($start!=''&&$end!='') {
            $this->db->where($this->db->dbprefix('sales').'.date BETWEEN "' . $start . '" and "' . $end . '"');
        }

        
    }
    function ListHoaDonWeb_post()
    {        
        $rs=array('status'=>false,'mess'=>'INVALID authentication');
        if ($_SERVER['PHP_AUTH_USER'] == 'CIwiFtn8QMfIq2MNJqU8Q2lBOhKzpTTIdBBABC' && $_SERVER['PHP_AUTH_PW'] == md5('posbasic@2017#')) 
        {            

            $rs=array('status'=>false,'mess'=>'ERROR');

            $_POST['token']=$this->security->get_csrf_hash();
            if ($_POST['token']!=''&&$_POST['device_token']!='') {
                //check login by token
                $rslogin=$this->checkloginByToken($_POST['token'],$_POST['device_token']);
                if ($rslogin)
                { 
                    if ($this->Admin||$this->Owner)
                    {
                        if (isset($_POST['warehouse'])&&$_POST['warehouse']!='') 
                        {
                            $warehouse_id =$_POST['warehouse'];
                            $objwh=$this->site->getWarehouseByID($warehouse_id);
                            if (empty($objwh)||$objwh==false) {
                                $rs=array('status'=>false,'mess'=>'ID kho hàng (warehouse='.$warehouse_id.') không hợp lệ');  
                                $this->response($rs);
                                return;
                            }
                        } else {
                            $warehouse_id = $this->Settings->default_warehouse;
                        }     

                    }else{
                        $warehouse_id = $this->session->userdata('warehouse_id');
                    }

                    $page=isset($_POST['page'])?$_POST['page']:1;
                    $per_page=isset($_POST['per_page'])?$_POST['per_page']:10;
                    $offset= ($page - 1) * $per_page;
                    
                    $payment_status=isset($_POST['payment_status'])?$_POST['payment_status']:NULL;
                    $customer_id=isset($_POST['customer_id'])?$_POST['customer_id']:NULL;
                    $customer_details = $this->site->getCompanyByID($customer_id);
                    if ($customer_id>0&&$customer_details==false) 
                    {
                        $rs=array('status'=>false,'mess'=>'Không có khách hàng');  
                        $this->response($rs);
                        return;
                    }
                    //total
                    $this->db->select("sales.id as id")->from('sales');
                    $this->fillter_order_web();
                    $q = $this->db->get(); 
                    $total=$q->num_rows();

                    $this->db
                    ->select("sales.id as id, DATE_FORMAT(scodeweb_sales.date, '%Y-%m-%d %T') as date, reference_no,doitac.name as doitac_name,sales.doitac,warehouses.name as kho,CONCAT(" . $this->db->dbprefix('users') . ".first_name, ' ', " . $this->db->dbprefix('users') . ".last_name) as biller,scodeweb_sales.customer_id,customer, sale_status, grand_total, paid, (grand_total-paid) as balance, payment_status, sales.attachment, scodeweb_sales.created_by")
                    ->from('sales')
                    ->join('doitac', 'doitac.id=sales.doitac', 'left')
                    ->join('users', 'users.id=sales.created_by', 'left')
                    ->join('warehouses', 'warehouses.id=sales.warehouse_id', 'left');
                    
                    $this->fillter_order_web();

                    $this->db->limit($per_page,$offset);
                    
                    $this->db->order_by('sales.date','desc');

                    $q = $this->db->get();            
                    if ($q->num_rows() > 0) {
                        foreach (($q->result()) as $row) {
                            $bills[] = $row;
                        }
                    } else {
                        $bills = NULL;
                    }
                   // echo var_dump($bills);
                    if (!empty($bills)) {
                        foreach ($bills as $bill)
                        {
                            $bill->user_obj=$this->site->getUser($bill->created_by);
                        }
                    }
 
                    $rs=array('status'=>true,'mess'=>'Thành công.','page'=>$page,'per_page'=>$per_page,'count'=>$total,'list'=>$bills); 
                    
                                       
                }else{ 
                    $rs=array('status'=>false,'mess'=>'Token không chính xác.');                    
                }
            }else{
                $rs=array('status'=>false,'mess'=>'Token không hợp lệ');
            }       
        }       
        $this->response($rs);
    }
    
    function UpdateStatusById_post()
    {        
        $rs=array('status'=>false,'mess'=>'INVALID authentication');
        if ($_SERVER['PHP_AUTH_USER'] == 'CIwiFtn8QMfIq2MNJqU8Q2lBOhKzpTTIdBBABC' && $_SERVER['PHP_AUTH_PW'] == md5('posbasic@2017#')) 
        {
            $rs=array('status'=>false,'mess'=>'ERROR');

            $_POST['token']=$this->security->get_csrf_hash();
            if ($_POST['token']!=''&&$_POST['device_token']!='') {
                //check login by token
                $rslogin=$this->checkloginByToken($_POST['token'],$_POST['device_token']);
                if ($rslogin)
                {
                    
                    if(!$this->Owner && !$this->Admin) {
                        if ($this->data['GP']['sales-edit']!=1) {
                            $rs=array('status'=>false,'mess'=>'Không có quyền');  
                            $this->response($rs);
                            return;
                        }
                    }
                    $order=$this->site->getInvoiceByID($_POST['order_id']);
                    if ($order!=false) 
                    {
                        $sid=$_POST['order_id'];
                        if(!$this->Owner && !$this->Admin) 
                        {                                                           
                            if ($order->api_id==0&&$order->is_web==0) {
                                if ($order->created_by!=$this->session->userdata('user_id')) {
                                    $rs=array('status'=>false,'mess'=>'Không có quyền sửa đơn hàng này');  
                                    $this->response($rs);
                                    return;
                                }
                            }                                                                
                        }

                        $status=$_POST['sale_status'];
                        $note=$_POST['notes'];
                        
                        if (!array_key_exists($status,$this->Settings->sale_status)) {
                            $rs=array('status'=>false,'mess'=>'Sale Status không hợp lệ. '.$status);  
                            $this->response($rs);
                            return;
                        }

                        if ($this->sales_model->updateStatus($sid, $status, $note)) {
                            $this->SysnApiWoooUpdateStatusOrder($status,$sid);
                            $order=$this->site->getInvoiceByID($sid);
                            $rs=array('status'=>true,'mess'=>'Thành công','item'=>$order);  
                            $this->response($rs);
                            return;
                        }else{
                            $rs=array('status'=>false,'mess'=>'Lỗi cập nhật');
                        }                                            

                    }else{
                        $rs=array('status'=>false,'mess'=>'Không tìm thấy hóa đơn');                    
                    }                    
                    
                }else{
                    $rs=array('status'=>false,'mess'=>'Token không chính xác.');                    
                }
            }else{
                $rs=array('status'=>false,'mess'=>'Token không hợp lệ');
            }       
        }        
        
        $this->response($rs);
    }
    function SysnApiWoooUpdateStatusOrder($_status='completed',$order_id=0)
    {
        $status='completed';
        if($_status=='packing'){
            $status='on-hold';  
        }else if($_status=='delivering'){
            $status='processing';
        }else if($_status=='delete'){
            $status='cancelled';
        }else if($_status=='pending'){
            $status='processing';
        }
        //get order id by wooo 
        $order_woo_id=(int)$this->sales_model->getWooOrderIdBySaleId($order_id);
        $woo_url_id=$this->sales_model->getWooUrlBySaleId($order_woo_id);
        
        if($this->Settings->woo_url!=''&&$this->Settings->woo_key!=''&&$this->Settings->woo_sec!=''){
            $woo_url=json_decode($this->Settings->woo_url);
            $woo_key_p=json_decode($this->Settings->woo_key);
            $woo_sec_p=json_decode($this->Settings->woo_sec);
            foreach ($woo_url as $index=>$value) 
            {
                if ($value==$woo_url_id) {
                    $woo_key=$woo_key_p[$index];
                    $woo_sec=$woo_sec_p[$index];
                }
            }
            try{
                $woocommerce = new Client($woo_url_id,$woo_key,$woo_sec,
                    [
                        'version' => 'wc/v3',
                        'debug'           => true,
                        'return_as_array' => false,
                        'validate_url'    => false,
                        'timeout'         => 30,
                        'ssl_verify'      => false,
                    ]
                );
                
                if($order_woo_id>0){
                    
                    $data = array('status' => $status);
                    
                    $rs=$woocommerce->put('orders/'.$order_woo_id, $data);
                    
                    $decode=json_decode($rs);
                    
                    if($rs->status==$status){
                        return true;
                    }
                }
                return false;
                
            }catch (Exception $e ) {

                return $e->getMessage();
            }
        }
        return false;
    }
    function addPaymentOrder_post()
    {        
        $rs=array('status'=>false,'mess'=>'INVALID authentication');
        if ($_SERVER['PHP_AUTH_USER'] == 'CIwiFtn8QMfIq2MNJqU8Q2lBOhKzpTTIdBBABC' && $_SERVER['PHP_AUTH_PW'] == md5('posbasic@2017#')) 
        {
            $rs=array('status'=>false,'mess'=>'ERROR');

            $_POST['token']=$this->security->get_csrf_hash();
            if ($_POST['token']!=''&&$_POST['device_token']!='') {
                //check login by token
                $rslogin=$this->checkloginByToken($_POST['token'],$_POST['device_token']);
                if ($rslogin)
                {
                    
                    if(!$this->Owner && !$this->Admin) {
                        if ($this->data['GP']['sales-edit']!=1) {
                            $rs=array('status'=>false,'mess'=>'Không có quyền sửa đơn hàng');  
                            $this->response($rs);
                            return;
                        }
                    }
                    if(!$this->Owner && !$this->Admin) {
                        if ($this->data['GP']['thu-add']!=1) {
                            $rs=array('status'=>false,'mess'=>'Không có quyền thêm thanh toán');  
                            $this->response($rs);
                            return;
                        }
                    }
                    if ($this->input->post('tenkhach')=='') {
                        $rs=array('status'=>false,'mess'=>'Vui lòng cung cấp tên khách hàng');  
                        $this->response($rs);
                        return;
                    }
                    if ((float)$this->input->post('amount')<=0) {
                        $rs=array('status'=>false,'mess'=>'Vui lòng nhập số tiền');  
                        $this->response($rs);
                        return;
                    }
                    $amount=(float)$this->input->post('amount');

                    $order=$this->site->getInvoiceByID($_POST['order_id']);
                    if ($order!=false) 
                    {
                        $sid=$_POST['order_id'];
                        if ($order->payment_status == 'paid' && $order->grand_total == $order->paid) {
                            
                            $rs=array('status'=>false,'mess'=>'Đơn hàng đã thanh toán');  
                            $this->response($rs);
                            return;
                        }
                        $phaithu=(float)$order->grand_total-(float)$order->paid;
                        if ($amount>$phaithu) {
                            $amount= $phaithu;                           
                        }
                        //check PTTT
                        $pttt=$this->site->getAllSoQuy(); 
                        $payment_method=$this->input->post('paid_by');
                        $check_pttt=false;
                        foreach ($pttt as $pay_mt)
                        {
                            if ($pay_mt->code==$payment_method) {
                                $check_pttt=true;
                            }
                        }
                        if ($check_pttt==false) 
                        {
                            $rs=array('status'=>false,'mess'=>'Phương thức thanh toán không hợp lệ. '.$payment_method);  
                            $this->response($rs);
                            return;
                        }
                        if ($this->input->post('paid_by') == 'deposit') {
                            $customer_id = $order->customer_id;
                            if ( ! $this->site->check_customer_deposit($customer_id, $amount)) {                                
                                $rs=array('status'=>false,'mess'=>'Số tiền thanh toán thanh toán lớn hơn số tiền cọc');  
                                $this->response($rs);
                                return;
                            }
                        } else {
                            $customer_id = null;
                        }                        
                        $date = date('Y-m-d H:i:s');
                       
                        $payment = array(
                                'date' => $date,
                                'sale_id' => $this->input->post('order_id'),
                                'reference_no' => $this->site->getReference('pay'),
                                'amount' => $amount,
                                'paid_by' => $this->input->post('paid_by'),
                                'cheque_no' => $this->input->post('cheque_no'),
                                'cc_no' => $this->input->post('paid_by') == 'gift_card' ? $this->input->post('gift_card_no') : $this->input->post('pcc_no'),
                                'cc_holder' => $this->input->post('pcc_holder'),
                                'cc_month' => $this->input->post('pcc_month'),
                                'cc_year' => $this->input->post('pcc_year'),
                                'cc_type' => $this->input->post('pcc_type'),
                                'note' => $this->input->post('note'),
                                'created_by' => $this->session->userdata('user_id'),
                                'warehouse_id' => $order->warehouse_id,
                                'type' => 'received',
                                'c_name' => $this->input->post('tenkhach'),
                                'c_phone' => $this->input->post('dienthoai'),
                                'c_address' => $this->input->post('diachi'));
                                               
                        $pay_id=$this->sales_model->addPaymentApi($payment, $customer_id);

                        if ($pay_id>0) {                            
                            $rs=array('status'=>true,'mess'=>'Thành công','payment_id'=>$pay_id);  
                            $this->response($rs);
                            return;
                        }else{
                            $rs=array('status'=>false,'mess'=>'Lỗi thêm thanh toán cho hóa đơn '.$order->reference_no);
                        }                                            

                    }else{
                        $rs=array('status'=>false,'mess'=>'Không tìm thấy hóa đơn');                    
                    }                    
                    
                }else{
                    $rs=array('status'=>false,'mess'=>'Token không chính xác.');                    
                }
            }else{
                $rs=array('status'=>false,'mess'=>'Token không hợp lệ');
            }       
        }        
        
        $this->response($rs);
    }

    function print_orderbk($sale_id = NULL)
    {        
        $this->load->helper('pos');
        $inv = $this->pos_model->getInvoiceHistoryByID($sale_id);     
        if ($inv==false) {
            return false;
        }

        $this->data['rows'] = $rows=$this->pos_model->getAllInvoiceItems($sale_id);

        $tongsanpham=0;

        $biller_id = $inv->biller_id;
        $customer_id = $inv->customer_id;
        $biller=$this->pos_model->getCompanyByID($biller_id);
        $customer=$this->pos_model->getCompanyByID($customer_id);
        $payments=$this->pos_model->getInvoiceHistoryPayments($sale_id);
        
        $pos= $this->pos_model->getSetting();

        $barcode= $this->barcode($inv->reference_no, 'code128', 30);
        $return_sale=$inv->return_id ? $this->pos_model->getInvoiceHistoryByID($inv->return_id) : NULL;
        $return_rows=$inv->return_id ? $this->pos_model->getAllInvoiceItems($inv->return_id) : NULL;
        $return_payments=$return_sale ? $this->pos_model->getInvoiceHistoryPayments($return_sale->id) : NULL;
         $warehouse=$this->site->getWarehouseByID($inv->warehouse_id); 
        

        $created_by=$this->site->getUser($inv->created_by);
        $printer=$this->pos_model->getPrinterByID($this->pos_settings->printer);
           
        
        $_dc_cuahang=str_replace("<p>","",$warehouse->address);       
        $_dc_cuahang=str_replace("</p>","",$_dc_cuahang);

        $_tablhd='<table border="1" style="width:100%;border-collapse:collapse;">        <tbody>        <tr>            <td style="text-align:center;width:5%;"><strong>STT</strong>            </td>            <td style="width:51%;padding-left:0.5%;"><strong>Tên hàng hóa</strong><br>            </td>            <td style="text-align:right;padding-right:0.5%;width:12%;"><strong>Đơn giá</strong><br>            </td>            <td style="text-align:center;width:15%;"><strong>SL</strong><br>            </td>            <td style="text-align:right;padding-right:0.5%;width:15.5%;"><strong>Thành tiền</strong><br>            </td>        </tr>';
        
        $_tablhd_pos='<table border="1" style="width:100%;border-collapse:collapse;font-size:12px;">        <tbody>        <tr>            <td style="text-align:center;width:5%;"><strong>STT</strong>            </td>            <td style="width:63.5%;padding-left:0.5%;"><strong>Tên hàng hóa</strong><br>            </td> <td style="text-align:center;width:15%;"><strong>SL</strong><br>            </td>            <td style="text-align:right;padding-right:0.5%;width:15.5%;"><strong>Thành tiền</strong><br>            </td>        </tr>';
        
        $r = 1; $category = 0;
        $tax_summary = array();
        foreach ($rows as $row) {
            
            if((int)$row->unit_quantity!=0){
                $row->quantity=$row->unit_quantity;
            }
                    
            if ($this->Settings->invoice_view == 1) {
                if (isset($tax_summary[$row->tax_code])) {
                    $tax_summary[$row->tax_code]['items'] += $row->quantity;
                    $tax_summary[$row->tax_code]['tax'] += $row->item_tax;
                    $tax_summary[$row->tax_code]['amt'] += ($row->quantity * $row->net_unit_price) - $row->item_discount;
                } else {
                    $tax_summary[$row->tax_code]['items'] = $row->quantity;
                    $tax_summary[$row->tax_code]['tax'] = $row->item_tax;
                    $tax_summary[$row->tax_code]['amt'] = ($row->quantity * $row->net_unit_price) - $row->item_discount;
                    $tax_summary[$row->tax_code]['name'] = $row->tax_name;
                    $tax_summary[$row->tax_code]['code'] = $row->tax_code;
                    $tax_summary[$row->tax_code]['rate'] = $row->tax_rate;
                }
            }
            $units = $this->site->getUnitsByBUID($row->product_unit_id);
            $__tendvt="";
            $_doi_dv=0;
            foreach($units as $_unit){
                if($row->product_unit_id==$_unit->id){
                    $__tendvt=(float)$row->quantity."(".$_unit->name.")";
                    
                }
                if($row->product_unit_id==$_unit->base_unit){                   
                    
                    switch($_unit->operator) {
                        case '*':
                            $_doi_dv= (float)$row->quantity/(float)$_unit->operation_value;
                            break;
                        case '/':
                            $_doi_dv= (float)$row->quantity*(float)$_unit->operation_value;
                            break;
                        case '+':
                            $_doi_dv= (float)$row->quantity-(float)$_unit->operation_value;
                            break;
                        case '-':
                            $_doi_dv= (float)$row->quantity+(float)$_unit->operation_value;
                            break;
                        default:
                            $_doi_dv= (float)$row->quantity;
                            break;
                    }       
                    if((int)$_doi_dv>0){    
                        $__tendvt.="-".round($_doi_dv,0)."(".$_unit->name.")";
                    }
                }
            }
            
             $_prod_detail=$row->product_name;
            $_prod_detail.='<i style="font-size:11px;">'.($row->variant ? ' (' . $row->variant . ')' : '');   

            $_prod_detail.=$row->details ? '<br>' . html_entity_decode($row->details) : '';  
            $_prod_detail.=$row->baohanh ? '<br>Bảo hành:' . html_entity_decode($row->baohanh) : '';  
            $_prod_detail.=$row->serial_no ? '<br>Serial/Imei:' . html_entity_decode($row->serial_no) : ''; 
            $_prod_detail.='</i>';            
            $_strgia=$this->sma->formatMoney($row->unit_price);
            //if ($Settings->product_discount && $inv->product_discount != 0) 
            {
                $_strgia.=($row->discount != 0 ? "<br/>Giảm: (".$this->sma->formatMoney($row->item_discount).")":'');        
            } 

            $tongsanpham++;//=$row->unit_quantity;

            $_tablhd.='<tr><td style="text-align:center;vertical-align:middle;">'.$r.'</td><td style="vertical-align:middle;">'.$_prod_detail.'</td><td style="text-align:right; padding-right:10px;">'.$_strgia.'</td><td style="text-align:center; vertical-align:middle;">'.$this->sma->formatQuantity($row->unit_quantity).' '.$row->product_unit_code.'</td><td style="text-align:right; width:120px; padding-right:10px;">'.$this->sma->formatMoney($row->subtotal).'</td>            </tr>'; 
            $_tablhd_pos.='<tr><td style="text-align:center;vertical-align:middle;">'.$r.'</td><td style="vertical-align:middle;">'.$_prod_detail.'</td><td style="text-align:center; vertical-align:middle;">'.$this->sma->formatQuantity($row->unit_quantity).'</td><td style="text-align:right; width:120px; padding-right:10px;">'.$this->sma->formatMoney($row->subtotal).'</td>            </tr>';  

            $r++;
        }
        if ($return_rows) {
            $_tablhd.='<tr class="warning"><td colspan="100%" class="no-border"><strong>'.lang('returned_items').'</strong></td></tr>';
            foreach ($return_rows as $row) {
                
                if ($this->Settings->invoice_view == 1) {
                    if (isset($tax_summary[$row->tax_code])) {
                        $tax_summary[$row->tax_code]['items'] += $row->quantity;
                        $tax_summary[$row->tax_code]['tax'] += $row->item_tax;
                        $tax_summary[$row->tax_code]['amt'] += ($row->quantity * $row->net_unit_price) - $row->item_discount;
                    } else {
                        $tax_summary[$row->tax_code]['items'] = $row->quantity;
                        $tax_summary[$row->tax_code]['tax'] = $row->item_tax;
                        $tax_summary[$row->tax_code]['amt'] = ($row->quantity * $row->net_unit_price) - $row->item_discount;
                        $tax_summary[$row->tax_code]['name'] = $row->tax_name;
                        $tax_summary[$row->tax_code]['code'] = $row->tax_code;
                        $tax_summary[$row->tax_code]['rate'] = $row->tax_rate;
                    }
                }
                
                $_tablhd.='<tr class="warning">    <td style="text-align:center; vertical-align:middle;">'.$r.'</td>    <td style="vertical-align:middle;">'.$_rt_prod.'</td>    <td style="text-align:right; padding-right:10px;">'.$_rt_strgia.'</td>        <td style="text-align:center; vertical-align:middle;">'.$this->sma->formatQuantity($row->quantity).' '.$row->product_unit_code.'</td><td style="text-align:right;padding-right:10px;">'.$this->sma->formatMoney($row->subtotal).'</td></tr>';
                $_tablhd_pos.='<tr class="warning">    <td style="text-align:center; vertical-align:middle;">'.$r.'</td>    <td style="vertical-align:middle;">'.$_rt_prod.'</td>   <td style="text-align:center; vertical-align:middle;">'.$this->sma->formatQuantity($row->quantity).'</td><td style="text-align:right;padding-right:10px;">'.$this->sma->formatMoney($row->subtotal).'</td></tr>';
               
                $r++;
            }
        }
        
        $_tablhd.='</tbody><tfoot>';
    
        $tongcong=$this->sma->formatMoney($return_sale ? (($inv->total + $inv->product_tax)+($return_sale->total + $return_sale->product_tax)) : ($inv->total + $inv->product_tax));
        
                    
        $_giamgia=$this->sma->formatMoney($inv->order_discount) ;
        $_tongcong=0;
        $_tongcong_bang_chu=0;
        if ($this->pos_settings->rounding || $inv->rounding > 0) {
            $_tongcong=$this->sma->formatMoney($return_sale ? (($inv->grand_total + $inv->rounding)+$return_sale->grand_total) : ($inv->grand_total + $inv->rounding));
            $_tongcong_bang_chu=$return_sale ? (($inv->grand_total + $inv->rounding)+$return_sale->grand_total) : ($inv->grand_total + $inv->rounding);
        } else {
            $_tongcong=$this->sma->formatMoney($return_sale ? ($inv->grand_total+$return_sale->grand_total) : $inv->grand_total);
            $_tongcong_bang_chu=$return_sale ? ($inv->grand_total+$return_sale->grand_total) : $inv->grand_total;
        } 
        $left_end=substr($_tongcong_bang_chu,strlen($_tongcong_bang_chu)-5,strlen($_tongcong_bang_chu));
        if($left_end=='.0000'){
             $_tongcong_bang_chu=str_replace($left_end,"",$_tongcong_bang_chu);
         }
        $_tongcong_bang_chu_text=$this->site->convert_number_to_words($_tongcong_bang_chu);
        
        $_tongcong_bang_chu_text=strtolower($_tongcong_bang_chu_text);
        $_1_text=substr($_tongcong_bang_chu_text,0,1);
        $_2_text=substr($_tongcong_bang_chu_text,1,strlen($_tongcong_bang_chu_text));
        $_tongcong_bang_chu_text=strtoupper($_1_text).$_2_text." đồng";

        $_chuathanhtoan=0;
        $_chuathanhtoan_num=0;
        $tong_dathanhtoan=$this->sma->formatMoney($return_sale ? ($inv->paid+$return_sale->paid) : $inv->paid);
        if ($inv->paid < $inv->grand_total) {       
            
            $_chuathanhtoan=$this->sma->formatMoney(($return_sale ? (($inv->grand_total + $inv->rounding)+$return_sale->grand_total) : ($inv->grand_total + $inv->rounding)) - ($return_sale ? ($inv->paid+$return_sale->paid) : $inv->paid));
            
            $_chuathanhtoan_num=($return_sale ? (($inv->grand_total + $inv->rounding)+$return_sale->grand_total) : ($inv->grand_total + $inv->rounding)) - ($return_sale ? ($inv->paid+$return_sale->paid) : $inv->paid);
        } 
                
        $_tablhd.='</tbody><tfoot>';
        $_tablhd_pos.='</tbody><tfoot>'; 
        $_tongthuhoi=0;
        if ($return_sale) {        
            $_tongthuhoi=$this->sma->formatMoney($return_sale->grand_total);    
        } 
     
                
        $_diem_thuong=$customer->award_points != 0 && $this->Settings->each_spent > 0 ? '<p class="text-center">'.lang('this_sale').': '.floor(($inv->grand_total/$this->Settings->each_spent)*$this->Settings->ca_point):0;
        
        $_tong_diem=$customer->award_points;
        $_dathanhtoan= $this->reports_model->getSalesTotals($inv->customer_id);
        $company_details = $this->companies_model->getCompanyByID($inv->customer_id);       
        $no_cu=(float)$company_details->nobandau; 
        
        if(isset($_dathanhtoan->total_amount) || isset($_dathanhtoan->paid)){    
            $no_cu+=$_dathanhtoan->total_amount -  $_dathanhtoan->paid;    
        }
        $tong_no_all=$this->sma->formatMoney($no_cu+$_chuathanhtoan_num);
        $_tong_no_cu=$this->sma->formatMoney($no_cu); 
        $_tongthue=$this->sma->formatMoney($inv->order_tax); 
        $doitac=$this->doitac_model->getDoitacByID($inv->doitac);
        
        $parse_data = array('Ma_Don_Hang' => $inv->reference_no,'Khach_Hang' => $customer->name,'CongTy_KH' =>$customer->company,'MST_KH' =>$customer->vat_no,'site_link' => base_url(),'site_name' => $this->Settings->site_name,'logo' => '<img src="' . base_url() . 'assets/uploads/logos/' . $biller->logo . '" alt="' . ($biller->company != '-' ? $biller->company : $biller->name) . '"/>','Ten_Kho' => $warehouse->name,'Dia_Chi_Kho' =>$_dc_cuahang,'SDT_Kho' =>$warehouse->phone,'Dia_chi_kh' => $customer->address,'Dien_thoai_kh' => $customer->phone,'Ngay_Xuat' => $this->sma->hrld($inv->date),'Tong_Thanh_Toan' =>$_tongcong,'Nhan_Vien_Thu_Ngan' =>$created_by->first_name . ' ' . $created_by->last_name,'Phu_Phi' => $this->sma->formatMoney($inv->shipping),'Ghi_Chu' =>$this->sma->decode_html($inv->note),'Ghi_Chu_NV' =>$this->sma->decode_html($inv->staff_note),'No_cu' =>$_tong_no_cu,'Chua_Thanh_Toan' => $_chuathanhtoan,'Da_Thanh_Toan' => $tong_dathanhtoan,'Tong_Diem_Tich_Luy' =>$_tong_diem,'Diem_hoa_don' =>$_diem_thuong,'Giam_Gia_Tren_Hoa_Don' =>$_giamgia,'Tong_Tien_Hang' =>$tongcong,'Tong_Tien_Bang_Chu' =>$_tongcong_bang_chu_text,'Tong_thu_hoi' =>$_tongthuhoi,'Bang_Hoa_Don' =>$_tablhd,'Bang_Hoa_Don_POS' =>$_tablhd_pos,'Tong_No'=>$tong_no_all,'Ma_Doi_Tac' => $doitac->code,'Ten_Doi_Tac' => $doitac->name,'Dia_Chi_Doi_Tac' => $doitac->diachi,'Email_Doi_Tac' => $doitac->email,'Dien_Thoai_Doi_Tac' => $doitac->dienthoai,'No_Dau_Ky_Doi_Tac' => $doitac->nodauky,'THUE'=>$_tongthue);    
        
        
        if (file_exists('./themes/' . $this->theme . '/views/print_templates/printpos.html')) {          
            $sale_temp = file_get_contents('themes/' . $this->theme . '/views/print_templates/printpos.html');            
        } else {             
            $sale_temp = file_get_contents('./themes/default/views/print_templates/printpos.html');          
        }        
        $_get_active_pos=$this->settings_model->get_print_value($sale_temp);    
        $rs_ex_pos=explode(":",$_get_active_pos);
        
        $_sizein_page=isset($rs_ex_pos[0])&&$rs_ex_pos[0]!=":"?$rs_ex_pos[0]:"A5";
        $_chieuin_page=isset($rs_ex_pos[1])&&$rs_ex_pos[1]!=":"?$rs_ex_pos[1]:"Portrait";
        
        $kich_thuoc=$this->settings_model->define_size_in($_sizein_page,$_chieuin_page);  
        
        //replace value size print
        $sale_temp=$this->settings_model->define_print_replace($sale_temp);     
        

        $message = $this->parser->parse_string($sale_temp, $parse_data,false);
        $message=str_replace("\r","",$message);
        $message=str_replace("\n","",$message);
        $message=str_replace("\"","'",$message);
        //$message=json_encode($message);
        // tien hanh xu ly file pdf
        // xoa tat ca file in da co truoc do - 5 phut assets/print/
      
        $message=str_replace("<html>","<html style='width:".$kich_thuoc."'>",$message);

        return array('size'=>$kich_thuoc,'noidung'=>$message,'total'=>$tongsanpham);       
        
    }
    function print_order($sale_id = NULL)
    {        
        $this->load->helper('pos');
        $inv = $this->pos_model->getInvoiceHistoryByID($sale_id);     
        if ($inv==false) {
            return false;
        }

        $this->data['rows'] = $rows=$this->pos_model->getAllInvoiceItems($sale_id);

        $tongsanpham=0;

        $biller_id = $inv->biller_id;
        $customer_id = $inv->customer_id;
        $biller=$this->pos_model->getCompanyByID($biller_id);
        $customer=$this->pos_model->getCompanyByID($customer_id);
        $payments=$this->pos_model->getInvoiceHistoryPayments($sale_id);
        
        $pos= $this->pos_model->getSetting();

        $barcode= $this->barcode($inv->reference_no, 'code128', 30);
        $return_sale=$inv->return_id ? $this->pos_model->getInvoiceHistoryByID($inv->return_id) : NULL;
        $return_rows=$inv->return_id ? $this->pos_model->getAllInvoiceItems($inv->return_id) : NULL;
        $return_payments=$return_sale ? $this->pos_model->getInvoiceHistoryPayments($return_sale->id) : NULL;
         $warehouse=$this->site->getWarehouseByID($inv->warehouse_id); 
        

        $created_by=$this->site->getUser($inv->created_by);
        $printer=$this->pos_model->getPrinterByID($this->pos_settings->printer);
           
        
        $_dc_cuahang=str_replace("<p>","",$warehouse->address);       
        $_dc_cuahang=str_replace("</p>","",$_dc_cuahang);

        $_tablhd='<table border="0" style="width:100%;border-collapse:collapse;margin: 5px 0px;">        <tbody>        <tr>            <td style="text-align:center;width:auto;"><strong>STT</strong>            </td>            <td style="width:auto;padding-left:0.5%;"><strong>Tên hàng hóa</strong><br>            </td>            <td style="text-align:right;padding-right:0.5%;width:auto;"><strong>Đơn giá</strong><br>            </td>            <td style="text-align:center;width:auto;"><strong>SL</strong><br>            </td>            <td style="text-align:right;padding-right:0.5%;width:auto;"><strong>Thành tiền</strong><br>            </td>        </tr>';
        
        $_tablhd_pos='<table border="0" style="width:100%;border-collapse:collapse;font-size:12px;margin:5px 0px;">        <tbody>        <tr style="border-bottom: 1px solid;">            <td style="text-align:center;width:auto;"><strong>STT</strong>            </td>            <td style="width:auto;padding-left:0.5%;"><strong>Tên - giá</strong><br>            </td> <td style="text-align:center;width:auto;"><strong>SL</strong><br>            </td>            <td style="text-align:right;padding-right:0.5%;width:auto;"><strong>T. Tiền</strong><br>            </td>        </tr>';
        
        $r = 1; $category = 0;
        $tax_summary = array();
        foreach ($rows as $row) {
            
            if((int)$row->unit_quantity!=0){
                $row->quantity=$row->unit_quantity;
            }
                    
            if ($this->Settings->invoice_view == 1) {
                if (isset($tax_summary[$row->tax_code])) {
                    $tax_summary[$row->tax_code]['items'] += $row->quantity;
                    $tax_summary[$row->tax_code]['tax'] += $row->item_tax;
                    $tax_summary[$row->tax_code]['amt'] += ($row->quantity * $row->net_unit_price) - $row->item_discount;
                } else {
                    $tax_summary[$row->tax_code]['items'] = $row->quantity;
                    $tax_summary[$row->tax_code]['tax'] = $row->item_tax;
                    $tax_summary[$row->tax_code]['amt'] = ($row->quantity * $row->net_unit_price) - $row->item_discount;
                    $tax_summary[$row->tax_code]['name'] = $row->tax_name;
                    $tax_summary[$row->tax_code]['code'] = $row->tax_code;
                    $tax_summary[$row->tax_code]['rate'] = $row->tax_rate;
                }
            }
            $units = $this->site->getUnitsByBUID($row->product_unit_id);
            $__tendvt="";
            $_doi_dv=0;
            foreach($units as $_unit){
                if($row->product_unit_id==$_unit->id){
                    $__tendvt=(float)$row->quantity."(".$_unit->name.")";
                    
                }
                if($row->product_unit_id==$_unit->base_unit){                   
                    
                    switch($_unit->operator) {
                        case '*':
                            $_doi_dv= (float)$row->quantity/(float)$_unit->operation_value;
                            break;
                        case '/':
                            $_doi_dv= (float)$row->quantity*(float)$_unit->operation_value;
                            break;
                        case '+':
                            $_doi_dv= (float)$row->quantity-(float)$_unit->operation_value;
                            break;
                        case '-':
                            $_doi_dv= (float)$row->quantity+(float)$_unit->operation_value;
                            break;
                        default:
                            $_doi_dv= (float)$row->quantity;
                            break;
                    }       
                    if((int)$_doi_dv>0){    
                        $__tendvt.="-".round($_doi_dv,0)."(".$_unit->name.")";
                    }
                }
            }
            
             $_prod_detail=$row->product_name;
            $_prod_detail.='<i style="font-size:11px;">'.($row->variant ? ' (' . $row->variant . ')' : '');   

            $_prod_detail.=$row->details ? '<br>' . html_entity_decode($row->details) : '';  
            $_prod_detail.=$row->baohanh ? '<br>Bảo hành:' . html_entity_decode($row->baohanh) : '';  
            $_prod_detail.=$row->serial_no ? '<br>Serial/Imei:' . html_entity_decode($row->serial_no) : ''; 
            $_prod_detail.='</i>';            
            $_strgia=$this->sma->formatMoney($row->unit_price);
            //if ($Settings->product_discount && $inv->product_discount != 0) 
            {
                $_strgia.=($row->discount != 0 ? "<br/>Giảm: (".$this->sma->formatMoney($row->item_discount).")":'');        
            } 

            $tongsanpham+=$row->unit_quantity;

            $_tablhd.='<tr><td style="text-align:center;vertical-align:middle;">'.$r.'</td><td style="vertical-align:middle;">'.$_prod_detail.'</td><td style="text-align:right; padding-right:10px;">'.$_strgia.'</td><td style="text-align:center; vertical-align:middle;">'.$this->sma->formatQuantity($row->unit_quantity).' '.$row->product_unit_code.'</td><td style="text-align:right; width:120px; padding-right:10px;">'.$this->sma->formatMoney($row->subtotal).'</td>            </tr>'; 


             $_tablhd_pos.='<tr><td rowspan="2" style="text-align:center;vertical-align:middle;">'.$r.'</td>
                <td colspan="4" style="vertical-align:middle;">'.$_prod_detail.'</td>            
            </tr>
            <tr style="border-bottom: 1px solid;line-height: 25px;">
                <td style="text-align:left;">'.$_strgia.'</td>
                <td style="text-align:center; vertical-align:middle;">'.$this->sma->formatQuantity($row->unit_quantity).'</td>
                <td style="text-align:right; width:120px; padding-right:5px;">'.$this->sma->formatMoney($row->subtotal).'</td>            
            </tr>';  


            $r++;
        }
        if ($return_rows) {
            $_tablhd.='<tr class="warning"><td colspan="100%" class="no-border"><strong>'.lang('returned_items').'</strong></td></tr>';
            foreach ($return_rows as $row) {
                
                if ($this->Settings->invoice_view == 1) {
                    if (isset($tax_summary[$row->tax_code])) {
                        $tax_summary[$row->tax_code]['items'] += $row->quantity;
                        $tax_summary[$row->tax_code]['tax'] += $row->item_tax;
                        $tax_summary[$row->tax_code]['amt'] += ($row->quantity * $row->net_unit_price) - $row->item_discount;
                    } else {
                        $tax_summary[$row->tax_code]['items'] = $row->quantity;
                        $tax_summary[$row->tax_code]['tax'] = $row->item_tax;
                        $tax_summary[$row->tax_code]['amt'] = ($row->quantity * $row->net_unit_price) - $row->item_discount;
                        $tax_summary[$row->tax_code]['name'] = $row->tax_name;
                        $tax_summary[$row->tax_code]['code'] = $row->tax_code;
                        $tax_summary[$row->tax_code]['rate'] = $row->tax_rate;
                    }
                }
                
                $_tablhd.='<tr class="warning">    <td style="text-align:center; vertical-align:middle;">'.$r.'</td>    <td style="vertical-align:middle;">'.$_rt_prod.'</td>    <td style="text-align:right; padding-right:10px;">'.$_rt_strgia.'</td>        <td style="text-align:center; vertical-align:middle;">'.$this->sma->formatQuantity($row->quantity).' '.$row->product_unit_code.'</td><td style="text-align:right;padding-right:10px;">'.$this->sma->formatMoney($row->subtotal).'</td></tr>';
                $_tablhd_pos.='<tr class="warning">    <td style="text-align:center; vertical-align:middle;">'.$r.'</td>    <td style="vertical-align:middle;">'.$_rt_prod.'</td>   <td style="text-align:center; vertical-align:middle;">'.$this->sma->formatQuantity($row->quantity).'</td><td style="text-align:right;padding-right:10px;">'.$this->sma->formatMoney($row->subtotal).'</td></tr>';
               
                $r++;
            }
        }
        
        $_tablhd.='</tbody><tfoot>';
    
        $tongcong=$this->sma->formatMoney($return_sale ? (($inv->total + $inv->product_tax)+($return_sale->total + $return_sale->product_tax)) : ($inv->total + $inv->product_tax));
        
                    
        $_giamgia=$this->sma->formatMoney($inv->order_discount) ;
        $_tongcong=0;
        $_tongcong_bang_chu=0;
        if ($this->pos_settings->rounding || $inv->rounding > 0) {
            $_tongcong=$this->sma->formatMoney($return_sale ? (($inv->grand_total + $inv->rounding)+$return_sale->grand_total) : ($inv->grand_total + $inv->rounding));
            $_tongcong_bang_chu=$return_sale ? (($inv->grand_total + $inv->rounding)+$return_sale->grand_total) : ($inv->grand_total + $inv->rounding);
        } else {
            $_tongcong=$this->sma->formatMoney($return_sale ? ($inv->grand_total+$return_sale->grand_total) : $inv->grand_total);
            $_tongcong_bang_chu=$return_sale ? ($inv->grand_total+$return_sale->grand_total) : $inv->grand_total;
        } 
        $left_end=substr($_tongcong_bang_chu,strlen($_tongcong_bang_chu)-5,strlen($_tongcong_bang_chu));
        if($left_end=='.0000'){
             $_tongcong_bang_chu=str_replace($left_end,"",$_tongcong_bang_chu);
         }
        $_tongcong_bang_chu_text=$this->site->convert_number_to_words($_tongcong_bang_chu);
        
        $_tongcong_bang_chu_text=strtolower($_tongcong_bang_chu_text);
        $_1_text=substr($_tongcong_bang_chu_text,0,1);
        $_2_text=substr($_tongcong_bang_chu_text,1,strlen($_tongcong_bang_chu_text));
        $_tongcong_bang_chu_text=strtoupper($_1_text).$_2_text." đồng";

        $_chuathanhtoan=0;
        $_chuathanhtoan_num=0;
        $tong_dathanhtoan=$this->sma->formatMoney($return_sale ? ($inv->paid+$return_sale->paid) : $inv->paid);
        if ($inv->paid < $inv->grand_total) {       
            
            $_chuathanhtoan=$this->sma->formatMoney(($return_sale ? (($inv->grand_total + $inv->rounding)+$return_sale->grand_total) : ($inv->grand_total + $inv->rounding)) - ($return_sale ? ($inv->paid+$return_sale->paid) : $inv->paid));
            
            $_chuathanhtoan_num=($return_sale ? (($inv->grand_total + $inv->rounding)+$return_sale->grand_total) : ($inv->grand_total + $inv->rounding)) - ($return_sale ? ($inv->paid+$return_sale->paid) : $inv->paid);
        } 
                
        $_tablhd.='</tbody><tfoot>';
        $_tablhd_pos.='</tbody><tfoot>'; 
        $_tongthuhoi=0;
        if ($return_sale) {        
            $_tongthuhoi=$this->sma->formatMoney($return_sale->grand_total);    
        } 
     
                
        $_diem_thuong=$customer->award_points != 0 && $this->Settings->each_spent > 0 ? '<p class="text-center">'.lang('this_sale').': '.floor(($inv->grand_total/$this->Settings->each_spent)*$this->Settings->ca_point):0;
        
        $_tong_diem=$customer->award_points;
        $_dathanhtoan= $this->reports_model->getSalesTotals($inv->customer_id);
        $company_details = $this->companies_model->getCompanyByID($inv->customer_id);       
        $no_cu=(float)$company_details->nobandau; 
        
        if(isset($_dathanhtoan->total_amount) || isset($_dathanhtoan->paid)){    
            $no_cu+=$_dathanhtoan->total_amount -  $_dathanhtoan->paid;    
        }
        $tong_no_all=$this->sma->formatMoney($no_cu+$_chuathanhtoan_num);
        $_tong_no_cu=$this->sma->formatMoney($no_cu); 
        $_tongthue=$this->sma->formatMoney($inv->order_tax); 
        $order_tax_details = $this->site->getTaxRateByID($inv->order_tax_id);
        $_thue_no=$order_tax_details->name;

        $doitac=$this->doitac_model->getDoitacByID($inv->doitac);
        
        $parse_data = array('Ma_Don_Hang' => $inv->reference_no,'Khach_Hang' => $customer->name,'CongTy_KH' =>$customer->company,'MST_KH' =>$customer->vat_no,'site_link' => base_url(),'site_name' => $this->Settings->site_name,'logo' => '<img src="' . base_url() . 'assets/uploads/logos/' . $biller->logo . '" alt="' . ($biller->company != '-' ? $biller->company : $biller->name) . '"/>','Ten_Kho' => $warehouse->name,'Dia_Chi_Kho' =>$_dc_cuahang,'SDT_Kho' =>$warehouse->phone,'Dia_chi_kh' => $customer->address,'Dien_thoai_kh' => $customer->phone,'Ngay_Xuat' => $this->sma->hrld($inv->date),'Tong_Thanh_Toan' =>$_tongcong,'Nhan_Vien_Thu_Ngan' =>$created_by->first_name . ' ' . $created_by->last_name,'Phu_Phi' => $this->sma->formatMoney($inv->shipping),'Ghi_Chu' =>$this->sma->decode_html($inv->note),'Ghi_Chu_NV' =>$this->sma->decode_html($inv->staff_note),'No_cu' =>$_tong_no_cu,'Chua_Thanh_Toan' => $_chuathanhtoan,'Da_Thanh_Toan' => $tong_dathanhtoan,'Tong_Diem_Tich_Luy' =>$_tong_diem,'Diem_hoa_don' =>$_diem_thuong,'Giam_Gia_Tren_Hoa_Don' =>$_giamgia,'Tong_Tien_Hang' =>$tongcong,'Tong_Tien_Bang_Chu' =>$_tongcong_bang_chu_text,'Tong_thu_hoi' =>$_tongthuhoi,'Bang_Hoa_Don' =>$_tablhd,'Bang_Hoa_Don_POS' =>$_tablhd_pos,'Tong_No'=>$tong_no_all,'Ma_Doi_Tac' => $doitac->code,'Ten_Doi_Tac' => $doitac->name,'Dia_Chi_Doi_Tac' => $doitac->diachi,'Email_Doi_Tac' => $doitac->email,'Dien_Thoai_Doi_Tac' => $doitac->dienthoai,'No_Dau_Ky_Doi_Tac' => $doitac->nodauky,'THUE'=>$_tongthue,'THUE_NO'=>$_thue_no);    
        
        
        if (file_exists('./themes/' . $this->theme . '/views/print_templates/printpos.html')) {          
            $sale_temp = file_get_contents('themes/' . $this->theme . '/views/print_templates/printpos.html');            
        } else {             
            $sale_temp = file_get_contents('./themes/default/views/print_templates/printpos.html');          
        }        
        $_get_active_pos=$this->settings_model->get_print_value($sale_temp);    
        $rs_ex_pos=explode(":",$_get_active_pos);
        
        $_sizein_page=isset($rs_ex_pos[0])&&$rs_ex_pos[0]!=":"?$rs_ex_pos[0]:"A5";
        $_chieuin_page=isset($rs_ex_pos[1])&&$rs_ex_pos[1]!=":"?$rs_ex_pos[1]:"Portrait";
        
        $kich_thuoc=$this->settings_model->define_size_in($_sizein_page,$_chieuin_page);  
        
        //replace value size print
        $sale_temp=$this->settings_model->define_print_replace($sale_temp);     
        

        $message = $this->parser->parse_string($sale_temp, $parse_data,false);
        $pattern = "/width\:\s(.+)\%/";
        $message= preg_replace($pattern, 'width:auto', $message);

        $message=str_replace("\r","",$message);
        $message=str_replace("\n","",$message);
        $message=str_replace("\"","'",$message);

        

        $message=str_replace("<html>","<html style='SIZEPRINTAPP'>",$message);

        

        //$message=json_encode($message);
        // tien hanh xu ly file pdf
        // xoa tat ca file in da co truoc do - 5 phut assets/print/
      

        return array('size'=>$kich_thuoc,'noidung'=>$message,'total'=>$tongsanpham);       
        
    }
     public function fillter_giaohang()
    {
        
        if ($_POST['keywords']) {
            $this->db->where("(" . $this->db->dbprefix('deliveries') . ".do_reference_no LIKE '%" . $_POST['keywords'] . "%' OR sale_reference_no LIKE '%" . $_POST['keywords'] . "%'  OR scodeweb_deliveries.customer LIKE '%" . $_POST['keywords'] . "%'  OR scodeweb_deliveries.phone LIKE '%" . $_POST['keywords'] . "%')");
        }
        if ($this->Admin||$this->Owner)
        {
            if (isset($_POST['warehouse'])&&$_POST['warehouse']!='') 
            {
                $warehouse_id =$_POST['warehouse'];                
            } else {
                $warehouse_id = 0;
            }     

        }else{
            $warehouse_id = $this->session->userdata('warehouse_id');
            //if view_right
            if (!$this->session->userdata('view_right')) {
                $this->db->where('sales.created_by', $this->session->userdata('user_id'));    
            }            
        }
        if ($warehouse_id>0) {
            $this->db->where('deliveries.warehouse_id', $warehouse_id);
        }
        $doitac=isset($_POST['doitac'])?$_POST['doitac']:NULL;
        if ($doitac>0) {
            $this->db->where('deliveries.delivered_by', $doitac);
        }
        $status=isset($_POST['status'])?$_POST['status']:NULL;
        if ($status!='') {
            $this->db->where('deliveries.status', $status);
        }
        
       
        $start='';
        if (isset($_POST['start'])&&$_POST['start']!='') {
            $start=date("Y-m-d",strtotime(str_replace("/","-",$_POST['start']))).' 00:00:00';
        }
        $end='';
        if (isset($_POST['end'])&&$_POST['end']!='') {
            $end=date("Y-m-d",strtotime(str_replace("/","-",$_POST['end']))).' 23:59:59';
        }
        if ($start!=''&&$end!='') {
            $this->db->where($this->db->dbprefix('deliveries').'.date BETWEEN "' . $start . '" and "' . $end . '"');
        }
        
    }
    function ListGiaoHang_post()
    {        
        $rs=array('status'=>false,'mess'=>'INVALID authentication');
        if ($_SERVER['PHP_AUTH_USER'] == 'CIwiFtn8QMfIq2MNJqU8Q2lBOhKzpTTIdBBABC' && $_SERVER['PHP_AUTH_PW'] == md5('posbasic@2017#')) 
        {            

            $rs=array('status'=>false,'mess'=>'ERROR');

            $_POST['token']=$this->security->get_csrf_hash();
            if ($_POST['token']!=''&&$_POST['device_token']!='') {
                //check login by token
                $rslogin=$this->checkloginByToken($_POST['token'],$_POST['device_token']);
                if ($rslogin)
                { 
                    if ($this->Admin||$this->Owner)
                    {
                        if (isset($_POST['warehouse'])&&$_POST['warehouse']!='') 
                        {
                            $warehouse_id =$_POST['warehouse'];
                            $objwh=$this->site->getWarehouseByID($warehouse_id);
                            if (empty($objwh)||$objwh==false) {
                                $rs=array('status'=>false,'mess'=>'ID kho hàng (warehouse='.$warehouse_id.') không hợp lệ');  
                                $this->response($rs);
                                return;
                            }
                        } else {
                            $warehouse_id = $this->Settings->default_warehouse;
                        }     

                    }else{
                        $warehouse_id = $this->session->userdata('warehouse_id');
                    }

                    $page=isset($_POST['page'])?$_POST['page']:1;
                    $per_page=isset($_POST['per_page'])?$_POST['per_page']:10;
                    $offset= ($page - 1) * $per_page;

                    $doitac=isset($_POST['doitac'])?$_POST['doitac']:NULL;
                    $doitac_obj = $this->site->getDoitacByID($doitac);
                    if ($doitac>0&&$doitac_obj==false) 
                    {
                        $rs=array('status'=>false,'mess'=>'Đối tác không hợp lệ');  
                        $this->response($rs);
                        return;
                    }

                    $status=isset($_POST['status'])?$_POST['status']:NULL;
                    if ($status!=''&&!array_key_exists($status,$this->Settings->delivery_status)) {
                        $rs=array('status'=>false,'mess'=>'Trạng thái không hợp lệ. '.$status);  
                        $this->response($rs);
                        return;
                    }

                    //total
                    $this->db->select("deliveries.id as id")->from('deliveries')->join('sale_items', 'sale_items.sale_id=deliveries.sale_id', 'left')->join('sales', 'sale_items.sale_id=sales.id', 'left');
                    $this->fillter_giaohang();
                    $q = $this->db->get(); 
                    $total=$q->num_rows();

                    $this->db
                        ->select("deliveries.id as id, deliveries.date,deliveries.ngaynhan, deliveries.do_reference_no, sale_reference_no,(SELECT CONCAT(code, '-',name) FROM scodeweb_doitac WHERE id=delivered_by) as doitac_name,delivered_by as doitac,deliveries.shipping, deliveries.customer,deliveries.phone, deliveries.address, deliveries.status,deliveries.attachment,deliveries.created_by,deliveries.warehouse_id,sales.grand_total,deliveries.sale_id")
                        ->from('deliveries')
                        ->join('sale_items', 'sale_items.sale_id=deliveries.sale_id', 'left')
                        ->join('sales', 'sale_items.sale_id=sales.id', 'left')
                        ->group_by('deliveries.id');
                        
                    if ($warehouse_id) {
                        $this->db->where('deliveries.warehouse_id', $warehouse_id);
                    }
                    
                    $this->fillter_giaohang();

                    $this->db->limit($per_page,$offset);
                    
                    $this->db->order_by('deliveries.date','desc');

                    $q = $this->db->get();            
                    if ($q->num_rows() > 0) {
                        foreach (($q->result()) as $row) {
                            $bills[] = $row;
                        }
                    } else {
                        $bills = NULL;
                    }
                   // echo var_dump($bills);
                    if (!empty($bills)) {
                        foreach ($bills as $bill)
                        {
                            $bill->order=$this->site->getInvoiceByID($bill->sale_id);
                            $bill->user_obj=$this->site->getUser($bill->created_by);
                        }
                    }
 
                    $rs=array('status'=>true,'mess'=>'Thành công.','page'=>$page,'per_page'=>$per_page,'count'=>$total,'list'=>$bills); 
                    
                                       
                }else{ 
                    $rs=array('status'=>false,'mess'=>'Token không chính xác.');                    
                }
            }else{
                $rs=array('status'=>false,'mess'=>'Token không hợp lệ');
            }       
        }       
        $this->response($rs);
    }
    function getGiaoHangById_post()
    {
        $rs=array('status'=>false,'mess'=>'INVALID authentication');
        if ($_SERVER['PHP_AUTH_USER'] == 'CIwiFtn8QMfIq2MNJqU8Q2lBOhKzpTTIdBBABC' && $_SERVER['PHP_AUTH_PW'] == md5('posbasic@2017#')) 
        {
            $rs=array('status'=>false,'mess'=>'ERROR');
            $_POST['token']=$this->security->get_csrf_hash();
            $_POST['token']=$this->security->get_csrf_hash();
            if ($_POST['token']!=''&&$_POST['device_token']!='') {
                //check login by token
                $rslogin=$this->checkloginByToken($_POST['token'],$_POST['device_token']);
                if ($rslogin)
                {
                    
                    if (isset($_POST['delivery_id']))
                    {
                        $sid=$_POST['delivery_id'];
                        if ($delivery = $this->sales_model->getDeliveryByID($sid)) {
                            
                            $order=$this->site->getInvoiceByID($delivery->sale_id);
                            $this->data=null;
                            $this->data['cod'] =(float)$order->grand_total-(float)$order->paid;
                            $this->data['delivery'] = $delivery; 
                            $this->data['sid'] = $sid;      
                            
                            $order=$this->site->getInvoiceByID($order->id);
                            $inv_items=$this->site->getAllInvoiceItems($order->id);
                            
                            krsort($inv_items);
                        
                            foreach ($inv_items as $item) {
                                
                                $obj_prod = $this->getProductByIdApi($item->product_id,$order->customer_id,NULL,$order->warehouse_id);                                
                                $_prod_detail=$obj_prod['name'];
                                if ($item->data_id_khuyenmai>0)
                                {
                                    $obj_km=$this->site->getKhuyenmaiById($item->data_id_khuyenmai);
                                    if ($obj_km!=false) {
                                        $_prod_detail.=" (".$obj_km->tenevent.")";    
                                    }else{
                                        $_prod_detail.=" (KHUYẾN MÃI)";
                                    }                                                                    
                                }                                
                                $obj_prod['name']=$_prod_detail;

                                $order_item['product_id']=$item->product_id;
                                $order_item['product_comments']=$item->comment;
                                $order_item['product_option_id']=$item->option_id;
                                $order_item['product_price']=$item->unit_price;
                                $order_item['product_quantity']=$item->unit_quantity;
                                $order_item['product_serial']=$item->serial_no;
                                $order_item['product_discount_value']=$item->discount;
                                $order_item['product_discount']=$item->item_discount;
                                $order_item['data_id_khuyenmai']=$item->data_id_khuyenmai;

                                $obj_prod['edit_order_item']=$order_item;
                                $pr[]=$obj_prod;
                            }
                            $type='sale';
                            if ($order->pos==1) {
                                $type='pos';
                            }else if ($order->is_web>0) {
                                $type='web';
                            }else if ($order->api_id>0) {
                                $type='tmdt';
                            }
                            $order->type=$type;
                            
                            $this->data['saleitems'] = $pr; 
                            $this->data['sid'] = $sid;
                            $this->data['order'] = $order;                            
                            $this->data['user_obj'] = $this->site->getUser($order->created_by);
                            $this->data['customer'] = $this->pos_model->getCompanyByID($order->customer_id);
                            $this->data['ghichu'] = $order->note;

                            $rs=array('status'=>true,'mess'=>'Thành công.','item'=>$this->data); 

                        } else {
                           $rs=array('status'=>false,'mess'=>'Không tìm thấy hóa đơn giao hàng.','item'=>null); 
                        }
                    }else{
                        $rs=array('status'=>false,'mess'=>'Không tìm thấy hóa đơn giao hàng.','item'=>null); 
                    }                  
                    
                }else{
                    $rs=array('status'=>false,'mess'=>'Token không chính xác.');                    
                }
            }else{
                $rs=array('status'=>false,'mess'=>'Token không hợp lệ');
            }       
        }
        $this->response($rs);
    }
    function HuyGiaoHang_post()
    {
        $rs=array('status'=>false,'mess'=>'INVALID authentication');
        if ($_SERVER['PHP_AUTH_USER'] == 'CIwiFtn8QMfIq2MNJqU8Q2lBOhKzpTTIdBBABC' && $_SERVER['PHP_AUTH_PW'] == md5('posbasic@2017#')) 
        {
            $rs=array('status'=>false,'mess'=>'ERROR');
            $_POST['token']=$this->security->get_csrf_hash();
            if ($_POST['token']!=''&&$_POST['device_token']!='') {
                //check login by token
                $rslogin=$this->checkloginByToken($_POST['token'],$_POST['device_token']);
                if ($rslogin)
                {
                    if(!$this->Owner && !$this->Admin) {
                        if ($this->data['GP']['sales-delete']!=1) {
                            $rs=array('status'=>false,'mess'=>'Không có quyền');  
                            $this->response($rs);
                            return;
                        }
                    }                    
                    if (isset($_POST['delivery_id'])) 
                    {
                        $sid=$_POST['delivery_id'];
                        if ($delivery = $this->sales_model->getDeliveryByID($sid)) {                           
                            if(!$this->Owner && !$this->Admin) {
                                if ($delivery->created_by!=$this->session->userdata('user_id')) {
                                    $rs=array('status'=>false,'mess'=>'Không có quyền xóa đơn giao hàng này');  
                                    $this->response($rs);
                                    return;
                                }
                            } 
                            $check=$this->sales_model->deleteDelivery($sid);
                            if ($check) {
                                $rs=array('status'=>true,'mess'=>'Thành công.','item'=>$order); 
                            }else{
                                $rs=array('status'=>false,'mess'=>'Lỗi xóa hóa đơn giao hàng.','item'=>null);     
                            }                            
                        } else {
                           $rs=array('status'=>false,'mess'=>'Không tìm thấy hóa đơn giao hàng.','item'=>null); 
                        }
                    }else{
                        $rs=array('status'=>false,'mess'=>'Không tìm thấy hóa đơn giao hàng.','item'=>null); 
                    }
                  
                    
                }else{
                    $rs=array('status'=>false,'mess'=>'Token không chính xác.');                    
                }
            }else{
                $rs=array('status'=>false,'mess'=>'Token không hợp lệ');
            }       
        }
        $this->response($rs);
    }
    function PrintGiaoHangById_post()
    {
        $rs=array('status'=>false,'mess'=>'INVALID authentication');
        if ($_SERVER['PHP_AUTH_USER'] == 'CIwiFtn8QMfIq2MNJqU8Q2lBOhKzpTTIdBBABC' && $_SERVER['PHP_AUTH_PW'] == md5('posbasic@2017#')) 
        {
            $rs=array('status'=>false,'mess'=>'ERROR');
            $_POST['token']=$this->security->get_csrf_hash();
            if ($_POST['token']!=''&&$_POST['device_token']!='') {
                //check login by token
                $rslogin=$this->checkloginByToken($_POST['token'],$_POST['device_token']);
                if ($rslogin)
                {
                                     
                    if (isset($_POST['delivery_id'])) 
                    {
                        $sid=$_POST['delivery_id'];
                        if ($delivery = $this->sales_model->getDeliveryByID($sid)) {                           
                            
                            $check=$this->print_giaohang($sid);

                            if ($check!=false) {
                                $rs=array('status'=>true,'mess'=>'Thành công.','item'=>$check); 
                            }else{
                                $rs=array('status'=>false,'mess'=>'Lỗi in hóa đơn.','item'=>null);     
                            }                            
                        } else {
                           $rs=array('status'=>false,'mess'=>'Không tìm thấy hóa đơn.','item'=>null); 
                        }
                    }else{
                        $rs=array('status'=>false,'mess'=>'Không tìm thấy hóa đơn.','item'=>null); 
                    }
                  
                    
                }else{
                    $rs=array('status'=>false,'mess'=>'Token không chính xác.');                    
                }
            }else{
                $rs=array('status'=>false,'mess'=>'Token không hợp lệ');
            }       
        }
        $this->response($rs);
    }
    public function print_giaohang($id = null)
    {
       
        $deli = $this->sales_model->getDeliveryByID($id);
        $tongsanpham=0;
        if ((int)$deli->sale_id>0) {
            $sale = $this->sales_model->getInvoiceByID($deli->sale_id);
            
            $phaithu=$sale->grand_total-$sale->paid;

            if (!$sale) {
                $rs=array('status'=>false,'mess'=>'Không tìm thấy thông tin hóa đơn');  
                $this->response($rs);
                return;
            }

            $biller=$this->site->getCompanyByID($sale->biller_id);
            $doitac=$this->doitac_model->getDoitacByID($deli->delivered_by);
            $rows=$this->sales_model->getAllInvoiceItemsWithDetails($deli->sale_id);
            $user=$this->site->getUser($deli->created_by);
            $warehouse=$this->site->getWarehouseByID($sale->warehouse_id); 
            $customer= $this->site->getCompanyByID($sale->customer_id); 
            
                        
            $r = 1;
            $_tablhd='<table border="1" style="width:100%;border-collapse:collapse;">        <tbody>        <tr>            <td style="text-align:center;width:5%;"><strong>STT</strong>            </td>            <td style="width:51%;padding-left:0.5%;"><strong>Tên hàng hóa</strong><br>            </td>                        <td style="text-align:center;width:15%;"><strong>SL</strong><br>            </td>      </tr>';
            foreach ($rows as $row){
                $_prod_detail=$row->product_code.' - '.$row->product_name . ($row->variant ? ' (' . $row->variant . ')' : '');    
                $_prod_detail.=$row->details ? '<br>' . html_entity_decode($row->details) : '';    
                
                $_tablhd.='<tr><td style="text-align:center;vertical-align:middle;">'.$r.'</td><td style="vertical-align:middle;">'.$_prod_detail.'</td><td style="text-align:center; vertical-align:middle;">'.$this->sma->formatQuantity($row->unit_quantity).' '.$row->product_unit_code.'</td>      </tr>';  
                
                $r++;
            }
            $tongsanpham=($r-1);
            $_tablhd.='</table>';        
            $_dc_cuahang=str_replace("<p>","",$warehouse->address);       
            $_dc_cuahang=str_replace("</p>","",$_dc_cuahang);       
            $_khachhang=$customer->name;
            $_ghichu="";
            if ($deli->note!=""&&$this->sma->decode_html($deli->note)!="") {
                $_ghichu="<p> Ghi chú: ".$this->sma->decode_html($deli->note)."</p>";
            }

            
            $parse_data = array('Ma_Giao_Hang' => $deli->do_reference_no,'Ma_Ban_Hang' => $deli->sale_reference_no,'Khach_Mua_Hang' => $_khachhang,'site_link' => base_url(),'site_name' => $this->Settings->site_name,'logo' => '<img src="' . base_url() . 'assets/uploads/logos/' . $biller->logo . '" alt="' . ($biller->company != '' ? $biller->company : $biller->name) . '"/>','Ten_Kho' => $warehouse->name,'Dia_Chi_Kho' =>$_dc_cuahang,'SDT_Kho' =>$warehouse->phone,'Dia_Chi_Giao_Hang' => $this->sma->decode_html($deli->address),'Dien_Thoai' => $deli->phone,'Email' => $customer->email,'Ngay_Giao' => $this->sma->hrld($deli->ngaynhan),'Ngay_Tao' => $this->sma->hrld($deli->date),'Nhan_Vien' =>$user->first_name . ' ' . $user->last_name,'Bang_Hoa_Don' =>$_tablhd,'Ghi_Chu' => $_ghichu,'Trang_Thai' => lang($deli->status),'Ma_Doi_Tac' => $doitac->code,'Ten_Doi_Tac' => $doitac->name,'Phi_Ship_Doi_Tac' => $this->sma->formatMoney($deli->shipping),'Dia_Chi_Doi_Tac' => $doitac->diachi,'Email_Doi_Tac' => $doitac->email,'Dien_Thoai_Doi_Tac' => $doitac->dienthoai,'No_Dau_Ky_Doi_Tac' => $doitac->nodauky,'Khach_Nhan_Hang' =>$deli->customer,'Phai_Thu' => $this->sma->formatMoney($phaithu));    
            

        }else if ((int)$deli->purchase_id>0) {
            $purchaseobj = $this->site->getPurchaseByID($deli->purchase_id);
            if (!$purchaseobj) {
                
                $rs=array('status'=>false,'mess'=>'Không tìm thấy thông tin hóa đơn');  
                $this->response($rs);
                return;
            }
 
            $biller=$this->site->getCompanyByID($this->Settings->default_biller);
            $doitac=$this->doitac_model->getDoitacByID($deli->delivered_by);

            $rows=$this->site->getAllPurchaseItemsPrint($purchaseobj->id);

            $user=$this->site->getUser($deli->created_by);
            $warehouse=$this->site->getWarehouseByID($purchaseobj->warehouse_id); 

            $customer= $this->site->getCompanyByID($purchaseobj->supplier_id); 
                       
            
            $r = 1;
            $_tablhd='<table border="1" style="width:100%;border-collapse:collapse;">        <tbody>        <tr>            <td style="text-align:center;width:5%;"><strong>STT</strong>            </td>            <td style="width:51%;padding-left:0.5%;"><strong>Tên hàng hóa</strong><br>            </td>                        <td style="text-align:center;width:15%;"><strong>SL</strong><br>            </td>      </tr>';
            foreach ($rows as $row){
                $_prod_detail=$row->product_code.' - '.$row->product_name . ($row->variant ? ' (' . $row->variant . ')' : '');    
                $_prod_detail.=$row->details ? '<br>' . html_entity_decode($row->details) : '';    
                
                $_tablhd.='<tr><td style="text-align:center;vertical-align:middle;">'.$r.'</td><td style="vertical-align:middle;">'.$_prod_detail.'</td><td style="text-align:center; vertical-align:middle;">'.$this->sma->formatQuantity($row->unit_quantity).' '.$row->product_unit_code.'</td>      </tr>';  
                
                $r++;
            }

            $tongsanpham=($r-1);

            $_tablhd.='</table>';        
            $_dc_cuahang=str_replace("<p>","",$warehouse->address);       
            $_dc_cuahang=str_replace("</p>","",$_dc_cuahang);       
            $_khachhang= $customer->name;
            
            
            $parse_data = array('Ma_Giao_Hang' => $deli->do_reference_no,'Ma_Ban_Hang' => $deli->sale_reference_no,'Khach_Mua_Hang' => $_khachhang,'site_link' => base_url(),'site_name' => $this->Settings->site_name,'logo' => '<img src="' . base_url() . 'assets/uploads/logos/' . $biller->logo . '" alt="' . ($biller->company != '' ? $biller->company : $biller->name) . '"/>','Ten_Kho' => $warehouse->name,'Dia_Chi_Kho' =>$_dc_cuahang,'SDT_Kho' =>$warehouse->phone,'Dia_Chi_Giao_Hang' => $this->sma->decode_html($deli->address),'Dien_Thoai' => $customer->phone,'Email' => $customer->email,'Ngay_Giao' => $this->sma->hrld($deli->date),'Nhan_Vien' =>$user->first_name . ' ' . $user->last_name,'Bang_Hoa_Don' =>$_tablhd,'Ghi_Chu' => $this->sma->decode_html($deli->note),'Trang_Thai' => lang($deli->status),'Ma_Doi_Tac' => $doitac->code,'Ten_Doi_Tac' => $doitac->name,'Phi_Ship_Doi_Tac' => $this->sma->formatMoney($deli->shipping),'Dia_Chi_Doi_Tac' => $doitac->diachi,'Email_Doi_Tac' => $doitac->email,'Dien_Thoai_Doi_Tac' => $doitac->dienthoai,'No_Dau_Ky_Doi_Tac' => $doitac->nodauky,'Khach_Nhan_Hang' =>$deli->received_by);   

        }     


        if (file_exists('./themes/' . $this->theme . '/views/print_khac/printgiao.html')) {          
            $sale_temp = file_get_contents('themes/' . $this->theme . '/views/print_khac/printgiao.html');            
        } else {             
            $sale_temp = file_get_contents('./themes/default/views/print_khac/printgiao.html');          
        }        
        $_get_active_pos=$this->settings_model->get_print_value($sale_temp);    
        $rs_ex_pos=explode(":",$_get_active_pos);
        
        $_sizein_page=isset($rs_ex_pos[0])&&$rs_ex_pos[0]!=":"?$rs_ex_pos[0]:"A5";
        $_chieuin_page=isset($rs_ex_pos[1])&&$rs_ex_pos[1]!=":"?$rs_ex_pos[1]:"Portrait";
        
        $this->data['item_print']=$this->Settings->item_print;
        $kich_thuoc=$this->settings_model->define_size_in($_sizein_page,$_chieuin_page);  
        
        //replace value size print
        $sale_temp=$this->settings_model->define_print_replace($sale_temp);
            
        $message = $this->parser->parse_string($sale_temp, $parse_data,true);
        $message=str_replace("<html>","<html style='width:".$kich_thuoc."'>",$message);

        return array('size'=>$kich_thuoc,'noidung'=>$message,'total'=>$tongsanpham);   
    }
    function UpdateGiaoHang_post()
    {        
        $rs=array('status'=>false,'mess'=>'INVALID authentication');
        if ($_SERVER['PHP_AUTH_USER'] == 'CIwiFtn8QMfIq2MNJqU8Q2lBOhKzpTTIdBBABC' && $_SERVER['PHP_AUTH_PW'] == md5('posbasic@2017#')) 
        {
            $rs=array('status'=>false,'mess'=>'ERROR');

            $_POST['token']=$this->security->get_csrf_hash();
            if ($_POST['token']!=''&&$_POST['device_token']!='') {
                //check login by token
                $rslogin=$this->checkloginByToken($_POST['token'],$_POST['device_token']);
                if ($rslogin)
                {

                    if(!$this->Owner && !$this->Admin) {
                        if ($this->data['GP']['sales-edit_delivery']!=1) {
                            $rs=array('status'=>false,'mess'=>'Không có quyền sửa giao hàng');  
                            $this->response($rs);
                            return;
                        }
                    }
             
                    if ($_POST['ship_name']=='') {
                        $rs=array('status'=>false,'mess'=>lang("Vui lòng nhập tên khách nhận hàng"));  
                        $this->response($rs);
                        return;
                    }
                    if ($_POST['ship_phone']=='') {
                        $rs=array('status'=>false,'mess'=>lang("Vui lòng nhập số điện thoại khách nhận hàng"));  
                        $this->response($rs);
                        return;
                    }
                    if ($_POST['ship_address']=='') {
                        $rs=array('status'=>false,'mess'=>lang("Vui lòng nhập địa chỉ khách nhận hàng"));  
                        $this->response($rs);
                        return;
                    }
                    if ($_POST['ship_time']=='') {
                        $rs=array('status'=>false,'mess'=>lang("Vui lòng nhập thời gian khách nhận hàng"));  
                        $this->response($rs);
                        return;
                    }
                    if ($_POST['ship_date']=='') {
                        $rs=array('status'=>false,'mess'=>lang("Vui lòng nhập ngày khách nhận hàng"));  
                        $this->response($rs);
                        return;
                    }
                    $doitac = (int)$this->input->post('doitac');
                    if ($doitac=='') {
                        $rs=array('status'=>false,'mess'=>lang("Vui lòng chọn đối tác giao hàng"));  
                        $this->response($rs);
                        return;
                    }

                    if ($doitac>0) {
                        $obj_doitac=$this->site->getDoitacByID($doitac);
                        if ($obj_doitac==false) {
                            $rs=array('status'=>false,'mess'=>'ID Đối tác giao hàng ('.$doitac.') không hợp lệ');  
                            $this->response($rs);
                            return;
                        }
                    }
                    $ship_status=isset($_POST['ship_status'])?$_POST['ship_status']:NULL;
                    if ($ship_status!=''&&!array_key_exists($ship_status,$this->Settings->delivery_status)) {
                        $rs=array('status'=>false,'mess'=>'Trạng thái giao hàng không hợp lệ. '.$ship_status);  
                        $this->response($rs);
                        return;
                    }


                    $shiptime=date("H:i:s",strtotime($_POST['ship_time']));

                    $shipdate=date("Y-m-d",strtotime(str_replace("/","-",$_POST['ship_date'])));

                    $date_ship=date("Y-m-d H:i:s",strtotime($shipdate.' '.$shiptime));


                    $delivery=$this->sales_model->getDeliveryByID($_POST['delivery_id']);
                    if ($delivery==false) {
                        $rs=array('status'=>false,'mess'=>'Không tìm thấy hóa đơn giao hàng');  
                        $this->response($rs);
                        return;
                    }


                    $order=$this->site->getInvoiceByID($delivery->sale_id);
                    if ($order!=false) 
                    {
                        $phaithu=(float)$order->grand_total-(float)$order->paid;
                        $amount=(float)$this->input->post('amount');

                        if ($phaithu>0&&$amount>0)
                        {
                            if(!$this->Owner && !$this->Admin) {
                                if ($this->data['GP']['thu-add']!=1) {
                                    $rs=array('status'=>false,'mess'=>'Không có quyền thêm thanh toán');  
                                    $this->response($rs);
                                    return;
                                }
                            }

                            if ($order->payment_status == 'paid' && $order->grand_total == $order->paid) {
                                
                                $rs=array('status'=>false,'mess'=>'Đơn hàng đã thanh toán');  
                                $this->response($rs);
                                return;
                            }  
                            if ($amount>$phaithu) {
                                $amount=$phaithu;                         
                            }
                            //check PTTT
                            $pttt=$this->site->getAllSoQuy(); 
                            $payment_method=$this->input->post('paid_by');
                            $check_pttt=false;
                            foreach ($pttt as $pay_mt)
                            {
                                if ($pay_mt->code==$payment_method) {
                                    $check_pttt=true;
                                }
                            }
                            if ($check_pttt==false) 
                            {
                                $rs=array('status'=>false,'mess'=>'Phương thức thanh toán không hợp lệ. '.$payment_method);  
                                $this->response($rs);
                                return;
                            }
                            if ($this->input->post('paid_by') == 'deposit') {
                                $customer_id = $order->customer_id;
                                if ( ! $this->site->check_customer_deposit($customer_id, $amount)) {                                
                                    $rs=array('status'=>false,'mess'=>'Số tiền thanh toán thanh toán lớn hơn số tiền cọc');  
                                    $this->response($rs);
                                    return;
                                }
                            } else {
                                $customer_id = null;
                            }                        
                            $date = date('Y-m-d H:i:s');
                            
                            $payment = array(
                                    'date' => $date,
                                    'sale_id' => $delivery->sale_id,
                                    'reference_no' => $this->site->getReference('pay'),
                                    'amount' => $amount,
                                    'paid_by' => $this->input->post('paid_by'),
                                    'cheque_no' => $this->input->post('cheque_no'),
                                    'cc_no' => $this->input->post('paid_by') == 'gift_card' ? $this->input->post('gift_card_no') : $this->input->post('pcc_no'),
                                    'cc_holder' => $this->input->post('pcc_holder'),
                                    'cc_month' => $this->input->post('pcc_month'),
                                    'cc_year' => $this->input->post('pcc_year'),
                                    'cc_type' => $this->input->post('pcc_type'),
                                    'note' => $this->input->post('note'),
                                    'created_by' => $this->session->userdata('user_id'),
                                    'type' => 'received',
                                    'c_name' => $this->input->post('tenkhach'),
                                    'c_phone' => $this->input->post('dienthoai'),
                                    'c_address' => $this->input->post('diachi'));
                                                   
                            $pay_id=$this->sales_model->addPaymentApi($payment, $customer_id);

                            if ($pay_id<=0) {                           
                                
                                $rs=array('status'=>false,'mess'=>'Lỗi thêm thanh toán cho hóa đơn '.$order->reference_no);
                            } 
                        }  

                        $dlDetails = array(
                            'ngaynhan' => $date_ship,                          
                            'customer' => $this->input->post('ship_name'),
                            'address' => $this->input->post('ship_address'),
                            'phone' => $this->input->post('ship_phone'),
                            'status' => $ship_status,
                            'delivered_by' => $this->input->post('doitac'),
                            'shipping' => $this->input->post('shipping'),
                            'note' => $this->sma->clear_tags($this->input->post('ship_note')),
                            'updated_by' => $this->session->userdata('user_id'),
                            'updated_at' => date("Y-m-d H:i:s"),);
                        
                        if ($this->sales_model->updateDelivery($delivery->id, $dlDetails)) {

                             //update sale object
                            $shipping=(float)$this->input->post('shipping');
                            $total=(float)$order->grand_total-(float)$order->shipping;
                            $new_grandtotal=$total+$shipping;

                            $this->db->update('sales', array('grand_total' => $new_grandtotal, 'shipping' => $shipping), array('id' => $order->id));

                            $rs=array('status'=>true,'mess'=>'Thành công');
                        }
                    }else{
                        $rs=array('status'=>false,'mess'=>'Không tìm thấy hóa đơn');                    
                    }                    
                    
                }else{
                    $rs=array('status'=>false,'mess'=>'Token không chính xác.');                    
                }
            }else{
                $rs=array('status'=>false,'mess'=>'Token không hợp lệ');
            }       
        }        
        
        $this->response($rs);
    }
    function fillter_thongbao()
    {
       
        if (isset($_POST['date_from'])&&$_POST['date_from'] != '' && $_POST['date_to'] != '') {
            
            $_POST['date_to']= str_replace("/","-",$_POST['date_to']);
            $_POST['date_from']= str_replace("/","-",$_POST['date_from']);
            $_POST['date_to'] = date('Y-m-d', strtotime($_POST['date_to'] . ' +1 day'));
            $_POST['date_from'] = date('Y-m-d', strtotime($_POST['date_from']));

            $this->db->where('created >=', $_POST['date_from']);
            $this->db->where('created <=', $_POST['date_to']);
        }
        if (isset($_POST['daxem'])&&$_POST['daxem']!='') 
        {
            $this->db->where('daxem', $_POST['daxem']);
        }
        
        if (isset($_POST['theloai'])&&$_POST['theloai']!='') {
            $this->db->where('popup', $_POST['theloai']);
        }

        if (isset($_POST['closed'])&&$_POST['closed']!='') {
            $this->db->where('closed', $_POST['closed']);
        }

        if (isset($_POST['keyword'])&&$_POST['keyword']!='') {
            $this->db->where("(title LIKE '%" . $_POST['keyword'] . "%')", NULL, FALSE);
        }
    }
    function ReportThongBaoBackend_post()
    {        
        $rs=array('status'=>false,'mess'=>'INVALID authentication');
        if ($_SERVER['PHP_AUTH_USER'] == 'CIwiFtn8QMfIq2MNJqU8Q2lBOhKzpTTIdBBABC' && $_SERVER['PHP_AUTH_PW'] == md5('posbasic@2017#')) 
        {            

            $rs=array('status'=>false,'mess'=>'ERROR');

            $_POST['token']=$this->security->get_csrf_hash();
            if ($_POST['token']!=''&&$_POST['device_token']!='') {
                //check login by token
                $rslogin=$this->checkloginByToken($_POST['token'],$_POST['device_token']);
                if ($rslogin)
                {
                      
                    $theloai=isset($_POST['theloai'])?$_POST['theloai']:'';
                    $closed=isset($_POST['closed'])?$_POST['closed']:'';   
 
                    $page=isset($_POST['page'])?$_POST['page']:1;
                    $per_page=isset($_POST['per_page'])?$_POST['per_page']:10;
                    $offset= ($page - 1) * $per_page;

                   
                    $this->db->select('count(ID) as quantity')->from('thongbao');
                    $this->fillter_thongbao();
                    $total_receipt = $this->db->get()->row_array();

                    $this->db->from('thongbao')
                        ->limit($_POST['per_page'], ($_POST['page'] - 1) * $_POST['per_page'])
                        ->order_by('created', 'desc');
                    $this->fillter_thongbao();    
                    $data['_list_thongbao'] = $this->db->get()->result_array();
                                
                    if ($total_receipt['quantity']==0) {
                        $data['_list_thongbao']=null;
                    }            
     
                    $rs=array('status'=>true,'mess'=>'Thành công.','page'=>$page,'per_page'=>$per_page,'total'=>$total_receipt['quantity'],'list'=>$data['_list_thongbao']); 
                                                           
                }else{ 
                    $rs=array('status'=>false,'mess'=>'Token không chính xác.');                    
                }
            }else{
                $rs=array('status'=>false,'mess'=>'Token không hợp lệ');
            }       
        }       
        $this->response($rs);
    }
    function ReportThongBaoPopup_post()
    {        
        $rs=array('status'=>false,'mess'=>'INVALID authentication');
        if ($_SERVER['PHP_AUTH_USER'] == 'CIwiFtn8QMfIq2MNJqU8Q2lBOhKzpTTIdBBABC' && $_SERVER['PHP_AUTH_PW'] == md5('posbasic@2017#')) 
        {            

            $rs=array('status'=>false,'mess'=>'ERROR');

            $_POST['token']=$this->security->get_csrf_hash();
            if ($_POST['token']!=''&&$_POST['device_token']!='') {
                //check login by token
                $rslogin=$this->checkloginByToken($_POST['token'],$_POST['device_token']);
                if ($rslogin)
                {
                   
                       $thongbao=$this->db->select('ID,hinhanh,lienket')->from('thongbao')->where(['daxem'=>0,'closed'=>0,'popup'=>1])->order_by('created','desc')->limit(1)->get()->row_array();

                        if (!empty($thongbao)) {
                            $rs=array('status'=>true,'mess'=>'Thành công.','report'=>$thongbao); 
                        }else{
                            $rs=array('status'=>false,'mess'=>'Chưa có thông báo mới.','report'=>null);     
                        }
                                            
                                       
                }else{
                    $rs=array('status'=>false,'mess'=>'Token không chính xác.');                    
                }
            }else{
                $rs=array('status'=>false,'mess'=>'Token không hợp lệ');
            }       
        }       
        $this->response($rs);
    }
    function UpdateThongBao_post()
    {        
        $rs=array('status'=>false,'mess'=>'INVALID authentication');
        if ($_SERVER['PHP_AUTH_USER'] == 'CIwiFtn8QMfIq2MNJqU8Q2lBOhKzpTTIdBBABC' && $_SERVER['PHP_AUTH_PW'] == md5('posbasic@2017#')) 
        {            

            $rs=array('status'=>false,'mess'=>'ERROR');

            $_POST['token']=$this->security->get_csrf_hash();
            if ($_POST['token']!=''&&$_POST['device_token']!='') {
                //check login by token
                $rslogin=$this->checkloginByToken($_POST['token'],$_POST['device_token']);
                if ($rslogin)
                {                     
                        $id=isset($_POST['id'])?$_POST['id']:0;

                        $thongbao = $this->db->from('thongbao')->where('ID', $id)->get()->row_array();

                        if (!empty($thongbao) && count($thongbao)) {

                            if ($thongbao['daxem']==0) {
                                $updated=gmdate("Y:m:d H:i:s", time() + 7 * 3600);
                                $this->db->where('ID', $id)->update('thongbao', array('daxem'=>1,'updated'=>$updated));
                                $rs=array('status'=>true,'mess'=>'Cập nhật đã xem thành công.');                    
                            }else{
                                $rs=array('status'=>false,'mess'=>'Thông báo đã xem trước đó');                        
                            }
                            
                        }else
                        {
                            $rs=array('status'=>false,'mess'=>'Thông báo không hợp lệ.');                    
                        }
                                       
                }else{
                    $rs=array('status'=>false,'mess'=>'Token không chính xác.');                    
                }
            }else{
                $rs=array('status'=>false,'mess'=>'Token không hợp lệ');
            }       
        }       
        $this->response($rs);
    }
    function UpdateThongBaoClose_post()
    {        
        $rs=array('status'=>false,'mess'=>'INVALID authentication');
        if ($_SERVER['PHP_AUTH_USER'] == 'CIwiFtn8QMfIq2MNJqU8Q2lBOhKzpTTIdBBABC' && $_SERVER['PHP_AUTH_PW'] == md5('posbasic@2017#')) 
        {            

            $rs=array('status'=>false,'mess'=>'ERROR');

            $_POST['token']=$this->security->get_csrf_hash();
            if ($_POST['token']!=''&&$_POST['device_token']!='') {
                //check login by token
                $rslogin=$this->checkloginByToken($_POST['token'],$_POST['device_token']);
                if ($rslogin)
                {                     
                        $id=isset($_POST['id'])?$_POST['id']:0;

                        $thongbao = $this->db->from('thongbao')->where('ID', $id)->get()->row_array();

                        if (!empty($thongbao) && count($thongbao)) {

                            if ($thongbao['closed']==0) {
                                $updated=gmdate("Y:m:d H:i:s", time() + 7 * 3600);
                                $this->db->where('ID', $id)->update('thongbao', array('closed'=>1,'updated'=>$updated));
                                $rs=array('status'=>true,'mess'=>'Đóng thông báo thành công.');                    
                            }else{
                                $rs=array('status'=>false,'mess'=>'Đã đóng trước đó');                        
                            }
                            
                        }else
                        {
                            $rs=array('status'=>false,'mess'=>'Thông báo không hợp lệ.');                    
                        }
                                       
                }else{
                    $rs=array('status'=>false,'mess'=>'Token không chính xác.');                    
                }
            }else{
                $rs=array('status'=>false,'mess'=>'Token không hợp lệ');
            }       
        }       
        $this->response($rs);
    }
     function ViewThongBaoById_post()
    {        
        $rs=array('status'=>false,'mess'=>'INVALID authentication');
        if ($_SERVER['PHP_AUTH_USER'] == 'CIwiFtn8QMfIq2MNJqU8Q2lBOhKzpTTIdBBABC' && $_SERVER['PHP_AUTH_PW'] == md5('posbasic@2017#')) 
        {
            $rs=array('status'=>false,'mess'=>'ERROR');
            $_POST['token']=$this->security->get_csrf_hash();
            if ($_POST['token']!=''&&$_POST['device_token']!='') {
                //check login by token
                $rslogin=$this->checkloginByToken($_POST['token'],$_POST['device_token']);
                if ($rslogin)
                {
                   
                       if ($_POST['thongbao_id']==''){                            
                            $rs=array('status'=>false,'mess'=>'Vui lòng cung cấp thông báo id'); 
                        } else
                        {
                            $thongbao = $this->db->from('thongbao')->where('ID', $_POST['thongbao_id'])->get()->row_array();
                            if (empty($thongbao)) {
                                $rs=array('status'=>false,'mess'=>'Thông báo '.$_POST['thongbao_id'].' không hợp lệ');
                            }else{
                                $rs=array('status'=>true,'item'=>$thongbao);                                 
                            }
                            
                        }
                    
                                       
                }else{
                    $rs=array('status'=>false,'mess'=>'Token không chính xác.');                    
                }
            }else{
                $rs=array('status'=>false,'mess'=>'Token không hợp lệ');
            }       
        }
        $this->response($rs);
    }  
    function addPaymentCustomer_post()
    {        
        $rs=array('status'=>false,'mess'=>'INVALID authentication');
        if ($_SERVER['PHP_AUTH_USER'] == 'CIwiFtn8QMfIq2MNJqU8Q2lBOhKzpTTIdBBABC' && $_SERVER['PHP_AUTH_PW'] == md5('posbasic@2017#')) 
        {
            $rs=array('status'=>false,'mess'=>'ERROR');

            $_POST['token']=$this->security->get_csrf_hash();
            if ($_POST['token']!=''&&$_POST['device_token']!='') {
                //check login by token
                $rslogin=$this->checkloginByToken($_POST['token'],$_POST['device_token']);
                if ($rslogin)
                {
                    
                    if(!$this->Owner && !$this->Admin) {
                        if ($this->data['GP']['sales-payments']!=1) {
                            $rs=array('status'=>false,'mess'=>'Không có quyền tạo phiếu thu');  
                            $this->response($rs);
                            return;
                        }
                    }                   
                    if ($this->input->post('tenkhach')=='') {
                        $rs=array('status'=>false,'mess'=>'Vui lòng cung cấp tên khách hàng');  
                        $this->response($rs);
                        return;
                    }
                    if ((float)$this->input->post('amount')<=0) {
                        $rs=array('status'=>false,'mess'=>'Vui lòng nhập số tiền');  
                        $this->response($rs);
                        return;
                    }
                    $amount=(float)$this->input->post('amount');

                    $customer=$this->site->getCompanyByID($_POST['customer_id']);
                    if ($customer!=false) 
                    {
                        $customer_id=$sid=$_POST['customer_id'];
                        $this->db->select("nobandau,(SELECT COALESCE(sum(grand_total), 0) FROM {$this->db->dbprefix('sales')} WHERE customer_id={$this->db->dbprefix('companies')}.id AND sale_status!='returned') + nobandau - (SELECT COALESCE(sum(paid), 0) FROM {$this->db->dbprefix('sales')} WHERE customer_id={$this->db->dbprefix('companies')}.id AND sale_status!='returned') - (select COALESCE(sum(amount), 0) from scodeweb_payments WHERE id_ncc_id_kh={$this->db->dbprefix('companies')}.id and type='received' AND amount>0) as duno");
                        $this->db->where('id',$sid);
                        $cus_obj=$this->db->get('companies')->row_array();

                        if ((float)$cus_obj['duno'] == 0) {                            
                            $rs=array('status'=>false,'mess'=>'Khách hàng không có công nợ');  
                            $this->response($rs);
                            return;
                        }
                        $phaithu=(float)$cus_obj['duno'];
                        if ($amount>=$phaithu) {
                            $amount=$phaithu;                           
                            //tien hanh xu ly update all dơn hang sang trang thai da thanh toan
                            //syncSalePayments
                            $sales=$this->sales_model->getAllInvoiceByCustomerID($customer_id);
                            if (!empty($sales))
                            {
                                //update all to paid
                                $this->site->UpdateAllSaleToPaidByCustomer($customer_id);
                            }
                        }
                        //check PTTT
                        $pttt=$this->site->getAllSoQuy(); 
                        $payment_method=$this->input->post('paid_by');
                        $check_pttt=false;
                        foreach ($pttt as $pay_mt)
                        {
                            if ($pay_mt->code==$payment_method) {
                                $check_pttt=true;
                            }
                        }
                        if ($check_pttt==false) 
                        {
                            $rs=array('status'=>false,'mess'=>'Phương thức thanh toán không hợp lệ. '.$payment_method);  
                            $this->response($rs);
                            return;
                        }
                        if ($this->input->post('paid_by') == 'deposit') {
                            
                            if ( ! $this->site->check_customer_deposit($customer_id, $amount)) {                                
                                $rs=array('status'=>false,'mess'=>'Số tiền thanh toán thanh toán lớn hơn số tiền cọc');  
                                $this->response($rs);
                                return;
                            }
                        }                       
                        $date = date('Y-m-d H:i:s');
                        
                        $payment = array(
                                'date' => $date,
                                'id_ncc_id_kh' => $customer_id,
                                'sale_id' => $this->input->post('order_id'),
                                'reference_no' => $this->site->getReference('pay'),
                                'amount' => $amount,
                                'paid_by' => $this->input->post('paid_by'),
                                'cheque_no' => $this->input->post('cheque_no'),
                                'cc_no' => $this->input->post('paid_by') == 'gift_card' ? $this->input->post('gift_card_no') : $this->input->post('pcc_no'),
                                'cc_holder' => $this->input->post('pcc_holder'),
                                'cc_month' => $this->input->post('pcc_month'),
                                'cc_year' => $this->input->post('pcc_year'),
                                'cc_type' => $this->input->post('pcc_type'),
                                'note' => $this->input->post('note'),
                                'created_by' => $this->session->userdata('user_id'),
                                'type' => 'received',
                                'c_name' => $this->input->post('tenkhach'),
                                'c_phone' => $this->input->post('dienthoai'),
                                'c_address' => $this->input->post('diachi'));                                               
                        $pay_id=$this->sales_model->addPaymentApi($payment, $customer_id);

                        if ($pay_id>0) {                            
                            $rs=array('status'=>true,'mess'=>'Thành công','payment_id'=>$pay_id);  
                            $this->response($rs);
                            return;
                        }else{
                            $rs=array('status'=>false,'mess'=>'Lỗi thêm thanh toán cho khách hàng '.$customer->name);
                        }                                            

                    }else{
                        $rs=array('status'=>false,'mess'=>'Không tìm thấy khách hàng');                    
                    }                    
                    
                }else{
                    $rs=array('status'=>false,'mess'=>'Token không chính xác.');                    
                }
            }else{
                $rs=array('status'=>false,'mess'=>'Token không hợp lệ');
            }       
        }        
        
        $this->response($rs);
    }
    function ReportSoQuy_post()
    {        
        $rs=array('status'=>false,'mess'=>'INVALID authentication');
        if ($_SERVER['PHP_AUTH_USER'] == 'CIwiFtn8QMfIq2MNJqU8Q2lBOhKzpTTIdBBABC' && $_SERVER['PHP_AUTH_PW'] == md5('posbasic@2017#')) 
        {            

            $rs=array('status'=>false,'mess'=>'ERROR');

            $_POST['token']=$this->security->get_csrf_hash();
            if ($_POST['token']!=''&&$_POST['device_token']!='') {
                //check login by token
                $rslogin=$this->checkloginByToken($_POST['token'],$_POST['device_token']);
                if ($rslogin)
                {
                    if(!$this->Owner && !$this->Admin) {
                        if ($this->data['GP']['sales-index']!=1) {
                            $rs=array('status'=>false,'mess'=>'Không có quyền xem sổ quỹ');  
                            $this->response($rs);
                            return;
                        }
                    }                                

                    if ($this->Admin||$this->Owner)
                    {
                        if (isset($_POST['warehouse'])&&$_POST['warehouse']!='') 
                        {
                            $warehouse_id =$_POST['warehouse'];
                            $objwh=$this->site->getWarehouseByID($warehouse_id);
                            if (empty($objwh)||$objwh==false) {
                                $rs=array('status'=>false,'mess'=>'ID kho hàng (warehouse='.$warehouse_id.') không hợp lệ');  
                                $this->response($rs);
                                return;
                            }
                        } else {
                            $warehouse_id = $this->Settings->default_warehouse;
                        }     

                    }else{
                        $warehouse_id = $this->session->userdata('warehouse_id');
                    }


                    if (isset($_POST['fillter'])&&$_POST['fillter']==0) {
                        //to day  
                        $_POST['start']=date("Y-m-d");                          
                        $_POST['end']=date("Y-m-d");                          
                    }else if (isset($_POST['fillter'])&&$_POST['fillter']==1) {
                        //this week
                        $day = date('w');
                        $_POST['start'] = date('Y-m-d', strtotime('-'.($day-1).' days'));
                        $_POST['end'] = date('Y-m-d', strtotime('+'.(7-$day).' days'));

                    }else if (isset($_POST['fillter'])&&$_POST['fillter']==2) {
                        //this month    
                        $_POST['start'] = date('Y-m-d',strtotime('first day of this month', time()));
                        $_POST['end'] = date('Y-m-d',strtotime('last day of this month', time()));

                    }else if (isset($_POST['fillter'])&&$_POST['fillter']==3) {
                        //this quater
                        $current_quarter = ceil(date('n') / 3);
                        $_POST['start'] = date('Y-m-d', strtotime(date('Y') . '-' . (($current_quarter * 3) - 2) . '-1'));
                        $_month= date('F', strtotime(date('Y') . '-' . (($current_quarter * 3)) . '-1'));

                        $_POST['end'] = date('Y-m-d',strtotime('last day of '.$_month, time()));


                    }else if (isset($_POST['fillter'])&&$_POST['fillter']==4) {
                        //this year 
                        $_POST['start'] = date('Y-m-d',strtotime('first day of january ', time()));
                        $_POST['end'] = date('Y-m-d',strtotime('last day of december', time()));                         
                    }else{
                        $_POST['start'] = $_POST['start'];
                        $_POST['end'] = $_POST['end'];
                    }
                    if ($_POST['start']!='') {                    
                        $start=str_replace("/","-",$_POST['start']);
                        $start=date("Y-m-d",strtotime($start));
                    }
                    
                    if ($_POST['end']!='') {                           
                        $end=str_replace("/","-",$_POST['end']);
                        $end=date("Y-m-d",strtotime($end));
                    }

                    if ($this->Admin||$this->Owner)
                    { 
                        if ($_POST['user_id'])
                        {
                            $nhanvien=$_POST['user_id'];    
                        }                         
                    }else{
                        if (!$this->session->userdata('view_right')) {
                            $nhanvien=$this->session->userdata('user_id');    
                        }                         
                    }                     

                    $this->db->select('sum(amount) as total_money')->from('payments')->where(['warehouse_id' => $warehouse_id]);
                    if ($start!=''&&$end!='') 
                    {
                        $this->db->where('date(date) >=', $start);
                        $this->db->where('date(date) <=', $end);                        
                    }
                    if ($nhanvien!=0) {
                        $this->db->where('created_by', $nhanvien);
                    }                        
                    $receipt = $this->db->get()->row_array(); 
                    $thu=$receipt['total_money'];
                   

                    $this->db->select('sum(amount) as total_money')->from('expenses')->where(['warehouse_id' => $warehouse_id]);
                     if ($start!=''&&$end!='') 
                    {
                        $this->db->where('date(date) >=', $start);
                        $this->db->where('date(date) <=', $end);                        
                    }
                    if ($nhanvien!=0) {
                        $this->db->where('created_by', $nhanvien);
                    }
                    $payment = $this->db->get()->row_array(); 
                    $chi=$payment['total_money'];
                    $tonquy=((float)$thu-(float)$chi);                     
                    


                    $dauky=0;
                    $cuoiky=0;
                    //dau ky ton quy truoc ngay bat dau
                    $this->db->select('sum(amount) as total_money')->from('payments')->where('warehouse_id',$warehouse_id);                        
                    $this->db->where('date(date)<', $start);                    
                    
                    if ($nhanvien!=0) {
                        $this->db->where('created_by', $nhanvien);
                    }                        
                    $receipt_dauky = $this->db->get()->row_array(); 
                    $tiendauky=$receipt_dauky['total_money'];


                    $this->db->select('sum(amount) as total_money')->from('expenses')->where('warehouse_id',$warehouse_id);                        
                    $this->db->where('date(date) <', $start); 
                    if ($nhanvien!=0) {
                        $this->db->where('created_by', $nhanvien);
                    }
                    //cuoi ky ton quy sau ngay bat dau
                    $payment_dauky = $this->db->get()->row_array();
                    $dauky=((float)$tiendauky-(float)$payment_dauky['total_money']);

                    $cuoiky=$dauky+$tonquy;

                    $rs=array('status'=>true,'mess'=>'Thành công.','start_date'=>$start,'end_date'=>$end,'thu'=>$thu,'chi'=>$chi,'tonquy'=>$tonquy,'dauky'=>$dauky,'cuoiky'=>$cuoiky,'methods'=>NULL); 

                    $payment_method=$this->site->getAllSoQuy(); 
                    
                    foreach ($payment_method as $pttt=>$method) {
                        if (!isset($_POST['nhanvien'])||!$_POST['nhanvien']) {
                            $nhanvien=0;
                        }else{
                            $nhanvien=$_POST['nhanvien'];
                        }

                        $this->db->select('sum(amount) as total_money')->from('payments')->where(['paid_by' => $pttt, 'warehouse_id' => $warehouse_id]);
                        if ($start!=''&&$end!='') 
                        {
                            $this->db->where('date(date) >=', $start);
                            $this->db->where('date(date) <=', $end);                        
                        }
                        if ($nhanvien!=0) {
                            $this->db->where('created_by', $nhanvien);
                        }                        
                        $receipt = $this->db->get()->row_array(); 
                        $thu=$receipt['total_money'];
                       

                        $this->db->select('sum(amount) as total_money')->from('expenses')->where(['paid_by' => $pttt, 'warehouse_id' => $warehouse_id]);
                         if ($start!=''&&$end!='') 
                        {
                            $this->db->where('date(date) >=', $start);
                            $this->db->where('date(date) <=', $end);                        
                        }
                        if ($nhanvien!=0) {
                            $this->db->where('created_by', $nhanvien);
                        }
                        $payment = $this->db->get()->row_array(); 
                        $chi=$payment['total_money'];
                        $tonquy=((float)$thu-(float)$chi);                     
                        


                        $dauky=0;
                        $cuoiky=0;
                        //dau ky ton quy truoc ngay bat dau
                        $this->db->select('sum(amount) as total_money')->from('payments')->where(['paid_by' => $pttt, 'warehouse_id' => $warehouse_id]);                        
                        $this->db->where('date(date)<', $start);                    
                        
                        if ($nhanvien!=0) {
                            $this->db->where('created_by', $nhanvien);
                        }                        
                        $receipt_dauky = $this->db->get()->row_array(); 
                        $tiendauky=$receipt_dauky['total_money'];


                        $this->db->select('sum(amount) as total_money')->from('expenses')->where(['paid_by' => $pttt, 'warehouse_id' => $warehouse_id]);                        
                        $this->db->where('date(date) <', $start); 
                        if ($nhanvien!=0) {
                            $this->db->where('created_by', $nhanvien);
                        }
                        //cuoi ky ton quy sau ngay bat dau
                        $payment_dauky = $this->db->get()->row_array();
                        $dauky=((float)$tiendauky-(float)$payment_dauky['total_money']);

                        $cuoiky=$dauky+$tonquy;

                        if ($thu>0||$chi>0||$tonquy>0||$dauky>0||$cuoiky>0) {
                            $rs['methods'][$pttt]=array('method_name'=>$method,'thu'=>$thu,'chi'=>$chi,'tonquy'=>$tonquy,'dauky'=>$dauky,'cuoiky'=>$cuoiky);
                        }
                        
                    }
                                           
                                       
                }else{
                    $rs=array('status'=>false,'mess'=>'Token không chính xác.');                    
                }
            }else{
                $rs=array('status'=>false,'mess'=>'Token không hợp lệ');
            }       
        }       
        $this->response($rs);
    }
    function ReportTonKho_post()
    {        
        $rs=array('status'=>false,'mess'=>'INVALID authentication');
        if ($_SERVER['PHP_AUTH_USER'] == 'CIwiFtn8QMfIq2MNJqU8Q2lBOhKzpTTIdBBABC' && $_SERVER['PHP_AUTH_PW'] == md5('posbasic@2017#')) 
        {            

            $rs=array('status'=>false,'mess'=>'ERROR');

            $_POST['token']=$this->security->get_csrf_hash();
            if ($_POST['token']!=''&&$_POST['device_token']!='') {
                //check login by token
                $rslogin=$this->checkloginByToken($_POST['token'],$_POST['device_token']);
                if ($rslogin)
                {
                    if(!$this->Owner && !$this->Admin) {
                        if ($this->data['GP']['reports-products']!=1) {
                            $rs=array('status'=>false,'mess'=>'Không có quyền xem tồn kho');  
                            $this->response($rs);
                            return;
                        }
                    }                                

                    if ($this->Admin||$this->Owner)
                    {
                        if (isset($_POST['warehouse'])&&$_POST['warehouse']!='') 
                        {
                            $warehouse_id =$_POST['warehouse'];
                            $objwh=$this->site->getWarehouseByID($warehouse_id);
                            if (empty($objwh)||$objwh==false) {
                                $rs=array('status'=>false,'mess'=>'ID kho hàng (warehouse='.$warehouse_id.') không hợp lệ');  
                                $this->response($rs);
                                return;
                            }
                        } else {
                            $warehouse_id = null;
                        }     

                    }else{
                        $warehouse_id = $this->session->userdata('warehouse_id');
                    }
                    $page=isset($_POST['page'])?$_POST['page']:1;
                    $per_page=isset($_POST['per_page'])?$_POST['per_page']:10;
                    $offset= ($page - 1) * $per_page;

                    $this->db
                            ->select('products.id,sum(scodeweb_warehouses_products.quantity) as tonkhohientai')
                            ->from('products')
                            ->join('warehouses_products', 'warehouses_products.product_id = products.id', 'LEFT')
                            ->join('warehouses', 'warehouses.id = warehouses_products.warehouse_id', 'LEFT')
                            ->limit($per_page, $offset)
                            ->order_by('sum(scodeweb_warehouses_products.quantity)', 'desc')
                            ->group_by('products.id');
                    if ((int)$warehouse_id>0) 
                    {
                        $this->db->where('warehouses.id',$warehouse_id);
                    }         
                    $list_products =$this->db->get()->result_array();
                    if (!empty($list_products)) {
                        $return_list=[];
                        foreach ($list_products as $product) {
                            $return_list[]=$this->getProductByIdApi($product['id'],NULL,NULL,$warehouse_id);
                        }

                        $rs=array('status'=>true,'mess'=>'Thành công.','page'=>$page,'per_page'=>$per_page,'items'=>$return_list); 
                    }else{
                        $rs=array('status'=>false,'mess'=>'Không có dữ liệu.');   
                    }
                                        
                                       
                }else{
                    $rs=array('status'=>false,'mess'=>'Token không chính xác.');                    
                }
            }else{
                $rs=array('status'=>false,'mess'=>'Token không hợp lệ');
            }       
        }        
        
        $this->response($rs);
    }
     function ReportDoanhThuSanPham_post()
    {        
        $rs=array('status'=>false,'mess'=>'INVALID authentication');
        if ($_SERVER['PHP_AUTH_USER'] == 'CIwiFtn8QMfIq2MNJqU8Q2lBOhKzpTTIdBBABC' && $_SERVER['PHP_AUTH_PW'] == md5('posbasic@2017#')) 
        {            

            $rs=array('status'=>false,'mess'=>'ERROR');

            $_POST['token']=$this->security->get_csrf_hash();
            if ($_POST['token']!=''&&$_POST['device_token']!='') {
                //check login by token
                $rslogin=$this->checkloginByToken($_POST['token'],$_POST['device_token']);
                if ($rslogin)
                {
                    if(!$this->Owner && !$this->Admin) {
                        if ($this->data['GP']['reports-sales']!=1) {
                            $rs=array('status'=>false,'mess'=>'Không có quyền xem doanh số');  
                            $this->response($rs);
                            return;
                        }
                    }                                

                    if ($this->Admin||$this->Owner)
                    {
                        if (isset($_POST['warehouse'])&&$_POST['warehouse']!='') 
                        {
                            $warehouse_id =$_POST['warehouse'];
                            $objwh=$this->site->getWarehouseByID($warehouse_id);
                            if (empty($objwh)||$objwh==false) {
                                $rs=array('status'=>false,'mess'=>'ID kho hàng (warehouse='.$warehouse_id.') không hợp lệ');  
                                $this->response($rs);
                                return;
                            }
                        } else {
                            $warehouse_id = null;
                        }     

                    }else{
                        $warehouse_id = $this->session->userdata('warehouse_id');
                    }
                    $page=isset($_POST['page'])?$_POST['page']:1;
                    $per_page=isset($_POST['per_page'])?$_POST['per_page']:10;
                    $offset= ($page - 1) * $per_page;

                    $this->db
                            ->select('products.id,products.name,products.code,sum(scodeweb_sale_items.unit_quantity) as tongslban,sum(scodeweb_sale_items.subtotal) as tongtien,')
                            ->from('products')
                            ->join('scodeweb_sale_items', 'scodeweb_sale_items.product_id = products.id', 'LEFT')
                            ->join('scodeweb_sales', 'scodeweb_sales.id = scodeweb_sale_items.sale_id', 'LEFT')                        
                            ->limit($per_page, $offset)                            
                            ->group_by('products.id');
                    if ($_POST['order_by']=='total_money')
                    {
                        $this->db->order_by('sum(scodeweb_sale_items.subtotal)', 'desc');
                    }else{
                        $this->db->order_by('sum(scodeweb_sale_items.unit_quantity)', 'desc');
                    }   

                    if ((int)$warehouse_id>0)
                    {
                        $this->db->where('scodeweb_sales.warehouse_id',$warehouse_id);
                    }     
                    if ($this->Admin||$this->Owner)
                    { 
                        if ($_POST['user_id'])
                        {
                            $this->db->where('scodeweb_sales.created_by',$_POST['user_id']);      
                        }                         
                    }else{
                        if (!$this->session->userdata('view_right')) {
                            $this->db->where('sales.created_by', $this->session->userdata('user_id'));    
                        }                         
                    }      
                    if ($_POST['start']!=''&&$_POST['end']!='') 
                    {
                        $startdate=date("Y-m-d",strtotime(str_replace("/","-",$_POST['start'])));
                        $enddate=date("Y-m-d",strtotime(str_replace("/","-",$_POST['end'])));

                        $this->db->where("date(scodeweb_sales.date) BETWEEN '".$startdate."' AND '".$enddate."'",NULL,FALSE);

                    }
                    $list_products =$this->db->get()->result_array();
                    if (!empty($list_products)) {
                       
                        $rs=array('status'=>true,'mess'=>'Thành công.','page'=>$page,'per_page'=>$per_page,'items'=>$list_products); 
                    }else{
                        $rs=array('status'=>false,'mess'=>'Không có dữ liệu.');   
                    }
                                       
                }else{
                    $rs=array('status'=>false,'mess'=>'Token không chính xác.');                    
                }
            }else{
                $rs=array('status'=>false,'mess'=>'Token không hợp lệ');
            }       
        }        
        
        $this->response($rs);
    }
    function ReportDoanhThuTheoGio_post()
    {        
        $rs=array('status'=>false,'mess'=>'INVALID authentication');
        if ($_SERVER['PHP_AUTH_USER'] == 'CIwiFtn8QMfIq2MNJqU8Q2lBOhKzpTTIdBBABC' && $_SERVER['PHP_AUTH_PW'] == md5('posbasic@2017#')) 
        {            

            $rs=array('status'=>false,'mess'=>'ERROR');

            $_POST['token']=$this->security->get_csrf_hash();
            if ($_POST['token']!=''&&$_POST['device_token']!='') {
                //check login by token
                $rslogin=$this->checkloginByToken($_POST['token'],$_POST['device_token']);
                if ($rslogin)
                {
                    if(!$this->Owner && !$this->Admin) {
                        if ($this->data['GP']['reports-sales']!=1) {
                            $rs=array('status'=>false,'mess'=>'Không có quyền xem doanh số');  
                            $this->response($rs);
                            return;
                        }
                    }                                

                    if ($this->Admin||$this->Owner)
                    {
                        if (isset($_POST['warehouse'])&&$_POST['warehouse']!='') 
                        {
                            $warehouse_id =$_POST['warehouse'];
                            $objwh=$this->site->getWarehouseByID($warehouse_id);
                            if (empty($objwh)||$objwh==false) {
                                $rs=array('status'=>false,'mess'=>'ID kho hàng (warehouse='.$warehouse_id.') không hợp lệ');  
                                $this->response($rs);
                                return;
                            }
                        } else {
                            $warehouse_id = null;
                        }     

                    }else{
                        $warehouse_id = $this->session->userdata('warehouse_id');
                    }
                    

                    $this->db
                            ->select('sum(scodeweb_sale_items.unit_quantity) as tongslban,sum(scodeweb_sale_items.subtotal) as tongtien,TIME_FORMAT(date,"%H") as time')
                            ->from('scodeweb_sales')
                            ->join('scodeweb_sale_items', 'scodeweb_sale_items.sale_id = scodeweb_sales.id', 'LEFT')
                            ->group_by("TIME_FORMAT(date,'%H')")
                            ->order_by("TIME_FORMAT(date,'%H')");
                    

                    if ((int)$warehouse_id>0)
                    {
                        $this->db->where('scodeweb_sales.warehouse_id',$warehouse_id);
                    }   
                    if ($this->Admin||$this->Owner)
                    { 
                        if ($_POST['user_id'])
                        {
                            $this->db->where('scodeweb_sales.created_by',$_POST['user_id']);      
                        }                         
                    }else{
                        if (!$this->session->userdata('view_right')) {
                            $this->db->where('sales.created_by', $this->session->userdata('user_id'));    
                        }                         
                    }      
                    $startdate='';  
                    if ($_POST['date']!='') 
                    {
                        $startdate=date("Y-m-d",strtotime(str_replace("/","-",$_POST['date'])));
                        $this->db->where("date(scodeweb_sales.date)",$startdate);
                    }

                    $list_products =$this->db->get()->result_array();
                    if (!empty($list_products)) {
                       
                        $rs=array('status'=>true,'mess'=>'Thành công.','date'=>$startdate,'items'=>$list_products); 
                    }else{
                        $rs=array('status'=>false,'mess'=>'Không có dữ liệu.');   
                    }
                                       
                }else{
                    $rs=array('status'=>false,'mess'=>'Token không chính xác.');                    
                }
            }else{
                $rs=array('status'=>false,'mess'=>'Token không hợp lệ');
            }       
        }        
        
        $this->response($rs);
    }    
    function ReportDoanhThuTheoNgay_post()
    {        
        $rs=array('status'=>false,'mess'=>'INVALID authentication');
        if ($_SERVER['PHP_AUTH_USER'] == 'CIwiFtn8QMfIq2MNJqU8Q2lBOhKzpTTIdBBABC' && $_SERVER['PHP_AUTH_PW'] == md5('posbasic@2017#')) 
        {            

            $rs=array('status'=>false,'mess'=>'ERROR');

            $_POST['token']=$this->security->get_csrf_hash();
            if ($_POST['token']!=''&&$_POST['device_token']!='') {
                //check login by token
                $rslogin=$this->checkloginByToken($_POST['token'],$_POST['device_token']);
                if ($rslogin)
                {
                    if(!$this->Owner && !$this->Admin) {
                        if ($this->data['GP']['reports-sales']!=1) {
                            $rs=array('status'=>false,'mess'=>'Không có quyền xem doanh số');  
                            $this->response($rs);
                            return;
                        }
                    }                                

                    if ($this->Admin||$this->Owner)
                    {
                        if (isset($_POST['warehouse'])&&$_POST['warehouse']!='') 
                        {
                            $warehouse_id =$_POST['warehouse'];
                            $objwh=$this->site->getWarehouseByID($warehouse_id);
                            if (empty($objwh)||$objwh==false) {
                                $rs=array('status'=>false,'mess'=>'ID kho hàng (warehouse='.$warehouse_id.') không hợp lệ');  
                                $this->response($rs);
                                return;
                            }
                        } else {
                            $warehouse_id = null;
                        }     

                    }else{
                        $warehouse_id = $this->session->userdata('warehouse_id');
                    }
                    

                    $this->db
                            ->select('sum(scodeweb_sale_items.unit_quantity) as tongslban,sum(scodeweb_sale_items.subtotal) as tongtien,date(date) as day')
                            ->from('scodeweb_sales')
                            ->join('scodeweb_sale_items', 'scodeweb_sale_items.sale_id = scodeweb_sales.id', 'LEFT')
                            ->group_by("date(date)")
                            ->order_by("date(date)");
                    

                    if ((int)$warehouse_id>0)
                    {
                        $this->db->where('scodeweb_sales.warehouse_id',$warehouse_id);
                    }     
                    if ($this->Admin||$this->Owner)
                    { 
                        if ($_POST['user_id'])
                        {
                            $this->db->where('scodeweb_sales.created_by',$_POST['user_id']);      
                        }                         
                    }else{
                        if (!$this->session->userdata('view_right')) {
                            $this->db->where('sales.created_by', $this->session->userdata('user_id'));    
                        }                         
                    }    
                    $startdate='';  
                    $enddate='';
                    if ($_POST['from'] == '' || $_POST['to'] == '') 
                    {
                        $startdate=date("Y-m-d",strtotime('first day of this month'));
                        $enddate=date("Y-m-d",strtotime('last day of this month'));
                    }else{
                        if ($_POST['from']!=''&&$_POST['to']!='') 
                        {
                            $startdate=date("Y-m-d",strtotime(str_replace("/","-",$_POST['from'])));
                            $enddate=date("Y-m-d",strtotime(str_replace("/","-",$_POST['to'])));                            
                        }
                    }
                    $this->db->where("date(scodeweb_sales.date) BETWEEN '".$startdate."' AND '".$enddate."'",NULL,FALSE);

                    $list_products =$this->db->get()->result_array();
                    if (!empty($list_products)) {
                       
                        $rs=array('status'=>true,'mess'=>'Thành công.','startdate'=>$startdate,'enddate'=>$enddate,'items'=>$list_products); 
                    }else{
                        $rs=array('status'=>false,'mess'=>'Không có dữ liệu.');   
                    }
                                       
                }else{
                    $rs=array('status'=>false,'mess'=>'Token không chính xác.');                    
                }
            }else{
                $rs=array('status'=>false,'mess'=>'Token không hợp lệ');
            }       
        }        
        
        $this->response($rs);
    }
    function ReportDoanhThuTheoCuaHang_post()
    {        
        $rs=array('status'=>false,'mess'=>'INVALID authentication');
        if ($_SERVER['PHP_AUTH_USER'] == 'CIwiFtn8QMfIq2MNJqU8Q2lBOhKzpTTIdBBABC' && $_SERVER['PHP_AUTH_PW'] == md5('posbasic@2017#')) 
        {            

            $rs=array('status'=>false,'mess'=>'ERROR');

            $_POST['token']=$this->security->get_csrf_hash();
            if ($_POST['token']!=''&&$_POST['device_token']!='') {
                //check login by token
                $rslogin=$this->checkloginByToken($_POST['token'],$_POST['device_token']);
                if ($rslogin)
                {
                    if(!$this->Owner && !$this->Admin) {
                        if ($this->data['GP']['reports-sales']!=1) {
                            $rs=array('status'=>false,'mess'=>'Không có quyền xem doanh số');  
                            $this->response($rs);
                            return;
                        }
                    }                                

                    if ($this->Admin||$this->Owner)
                    {
                        if (isset($_POST['warehouse'])&&$_POST['warehouse']!='') 
                        {
                            $warehouse_id =$_POST['warehouse'];
                            $objwh=$this->site->getWarehouseByID($warehouse_id);
                            if (empty($objwh)||$objwh==false) {
                                $rs=array('status'=>false,'mess'=>'ID kho hàng (warehouse='.$warehouse_id.') không hợp lệ');  
                                $this->response($rs);
                                return;
                            }
                        } else {
                            $warehouse_id = null;
                        }     

                    }else{
                        $warehouse_id = $this->session->userdata('warehouse_id');
                    }
                    

                    $this->db
                            ->select('warehouses.id as warehouse_id,warehouses.name as store_name,sum(scodeweb_sale_items.unit_quantity) as tongslban,sum(scodeweb_sale_items.subtotal) as tongtien')
                            ->from('scodeweb_sales')                            
                            ->join('scodeweb_sale_items', 'scodeweb_sale_items.sale_id = scodeweb_sales.id', 'LEFT')
                            ->join('warehouses', 'warehouses.id = scodeweb_sales.warehouse_id', 'LEFT')
                            ->group_by("scodeweb_sales.warehouse_id")
                            ->order_by("warehouses.name");
                    

                    if ((int)$warehouse_id>0)
                    {
                        $this->db->where('scodeweb_sales.warehouse_id',$warehouse_id);
                    }      
                    if ($this->Admin||$this->Owner)
                    { 
                        if ($_POST['user_id'])
                        {
                            $this->db->where('scodeweb_sales.created_by',$_POST['user_id']);      
                        }                         
                    }else{
                        if (!$this->session->userdata('view_right')) {
                            $this->db->where('sales.created_by', $this->session->userdata('user_id'));    
                        }                         
                    }   
                    $startdate='';  
                    $enddate='';
                    if ($_POST['from'] == '' || $_POST['to'] == '') 
                    {
                        $startdate=date("Y-m-d",strtotime('first day of this month'));
                        $enddate=date("Y-m-d",strtotime('last day of this month'));
                    }else{
                        if ($_POST['from']!=''&&$_POST['to']!='') 
                        {
                            $startdate=date("Y-m-d",strtotime(str_replace("/","-",$_POST['from'])));
                            $enddate=date("Y-m-d",strtotime(str_replace("/","-",$_POST['to'])));                            
                        }
                    }
                    $this->db->where("date(scodeweb_sales.date) BETWEEN '".$startdate."' AND '".$enddate."'",NULL,FALSE);

                    $list_products =$this->db->get()->result_array();
                    if (!empty($list_products)) {
                       
                        $rs=array('status'=>true,'mess'=>'Thành công.','startdate'=>$startdate,'enddate'=>$enddate,'items'=>$list_products); 
                    }else{
                        $rs=array('status'=>false,'mess'=>'Không có dữ liệu.');   
                    }
                                       
                }else{
                    $rs=array('status'=>false,'mess'=>'Token không chính xác.');                    
                }
            }else{
                $rs=array('status'=>false,'mess'=>'Token không hợp lệ');
            }       
        }        
        
        $this->response($rs);
    }
     function ReportDoanhThuTheoNhanVien_post()
    {        
        $rs=array('status'=>false,'mess'=>'INVALID authentication');
        if ($_SERVER['PHP_AUTH_USER'] == 'CIwiFtn8QMfIq2MNJqU8Q2lBOhKzpTTIdBBABC' && $_SERVER['PHP_AUTH_PW'] == md5('posbasic@2017#')) 
        {            

            $rs=array('status'=>false,'mess'=>'ERROR');

            $_POST['token']=$this->security->get_csrf_hash();
            if ($_POST['token']!=''&&$_POST['device_token']!='') {
                //check login by token
                $rslogin=$this->checkloginByToken($_POST['token'],$_POST['device_token']);
                if ($rslogin)
                {
                    if(!$this->Owner && !$this->Admin) {
                        if ($this->data['GP']['reports-sales']!=1) {
                            $rs=array('status'=>false,'mess'=>'Không có quyền xem doanh số');  
                            $this->response($rs);
                            return;
                        }
                    }                                

                    if ($this->Admin||$this->Owner)
                    {
                        if (isset($_POST['warehouse'])&&$_POST['warehouse']!='') 
                        {
                            $warehouse_id =$_POST['warehouse'];
                            $objwh=$this->site->getWarehouseByID($warehouse_id);
                            if (empty($objwh)||$objwh==false) {
                                $rs=array('status'=>false,'mess'=>'ID kho hàng (warehouse='.$warehouse_id.') không hợp lệ');  
                                $this->response($rs);
                                return;
                            }
                        } else {
                            $warehouse_id = null;
                        }     

                    }else{
                        $warehouse_id = $this->session->userdata('warehouse_id');

                    }                    

                    $this->db
                            ->select('users.id as user_id,users.first_name,users.last_name,groups.id as group_id,groups.description as group,sum(scodeweb_sale_items.unit_quantity) as tongslban,sum(scodeweb_sale_items.subtotal) as tongtien')
                            ->from('scodeweb_sales')                            
                            ->join('scodeweb_sale_items', 'scodeweb_sale_items.sale_id = scodeweb_sales.id', 'LEFT')
                            ->join('users', 'users.id = scodeweb_sales.created_by', 'LEFT')
                            ->join('groups', 'groups.id = users.group_id', 'LEFT')
                            ->group_by("scodeweb_sales.created_by")
                            ->order_by("groups.name");
                    

                    if ((int)$warehouse_id>0)
                    {
                        $this->db->where('scodeweb_sales.warehouse_id',$warehouse_id);
                    } 
                    if ($this->Admin||$this->Owner)
                    { 
                        if ($_POST['user_id'])
                        {
                            $this->db->where('scodeweb_sales.created_by',$_POST['user_id']);      
                        } 
                        if ($_POST['group_id'])
                        {
                            $this->db->where('scodeweb_groups.id',$_POST['group_id']);      
                        } 
                    }else{
                        if (!$this->session->userdata('view_right')) {
                            $this->db->where('sales.created_by', $this->session->userdata('user_id'));    
                        }                         
                    }    
                    $startdate='';  
                    $enddate='';
                    if ($_POST['from'] == '' || $_POST['to'] == '') 
                    {
                        $startdate=date("Y-m-d",strtotime('first day of this month'));
                        $enddate=date("Y-m-d",strtotime('last day of this month'));
                    }else{
                        if ($_POST['from']!=''&&$_POST['to']!='') 
                        {
                            $startdate=date("Y-m-d",strtotime(str_replace("/","-",$_POST['from'])));
                            $enddate=date("Y-m-d",strtotime(str_replace("/","-",$_POST['to'])));                            
                        }
                    }

                    $this->db->where("date(scodeweb_sales.date) BETWEEN '".$startdate."' AND '".$enddate."'",NULL,FALSE);

                    $list_products =$this->db->get()->result_array();
                    if (!empty($list_products)) {
                        //get list group
                        $groups=$this->site->getAllGroups();
                        $return_list=[];
                        foreach ($list_products as $nhanvien) {
                            foreach ($groups as $grp)
                            {
                                if ($nhanvien['group_id']==$grp->id)
                                {
                                    $return_list[$nhanvien['group']][]= $nhanvien;                  
                                }    
                            }
                        }
                        $rs=array('status'=>true,'mess'=>'Thành công.','startdate'=>$startdate,'enddate'=>$enddate,'items'=>$return_list); 
                    }else{
                        $rs=array('status'=>false,'mess'=>'Không có dữ liệu.');   
                    }
                                       
                }else{
                    $rs=array('status'=>false,'mess'=>'Token không chính xác.');                    
                }
            }else{
                $rs=array('status'=>false,'mess'=>'Token không hợp lệ');
            }       
        }        
        
        $this->response($rs);
    }
    function ViewHistoryById_post()
    {        
        $rs=array('status'=>false,'mess'=>'INVALID authentication');
        if ($_SERVER['PHP_AUTH_USER'] == 'CIwiFtn8QMfIq2MNJqU8Q2lBOhKzpTTIdBBABC' && $_SERVER['PHP_AUTH_PW'] == md5('posbasic@2017#')) 
        {
            $rs=array('status'=>false,'mess'=>'ERROR');
            $_POST['token']=$this->security->get_csrf_hash();
            if ($_POST['token']!=''&&$_POST['device_token']!='') {
                //check login by token
                $rslogin=$this->checkloginByToken($_POST['token'],$_POST['device_token']);
                if ($rslogin)
                {
                   

                    if (!in_array($_POST['loai'],['HOADON','PHIEUTHU','PHIEUCHI'])){
                        
                        $rs=array('status'=>false,'mess'=>'Loại hóa đơn không hợp lệ '.$_POST['loai']); 

                    }else if ($_POST['transaction_id']==''){
                        
                        $rs=array('status'=>false,'mess'=>'Vui lòng cung cấp transaction_id '.$_POST['transaction_id']); 

                    } else
                    {
                        $kq=$this->cms_view_history_full_post(true);     
                        $rs=array('status'=>true,'print'=>$kq); 
                        
                    }
                    
                                       
                }else{
                    $rs=array('status'=>false,'mess'=>'Token không chính xác.');                    
                }
            }else{
                $rs=array('status'=>false,'mess'=>'Token không hợp lệ');
            }       
        }
        $this->response($rs);
    }
    public function cms_view_history_full_post($return=false)
    {        
        $id = (int)$_POST['transaction_id'];

        if ($this->Admin||$this->Owner)
        {
            if (isset($_POST['warehouse_id'])&&$_POST['warehouse_id']!='') {
                $warehouse_id =$_POST['warehouse_id'];
                $objwh=$this->site->getWarehouseByID($warehouse_id);
                if (empty($objwh)||$objwh==false) {
                    $rs=array('status'=>false,'mess'=>'ID kho hàng (warehouse='.$warehouse_id.') không hợp lệ');  
                    $this->response($rs);
                    return;
                }
            } else {
                $warehouse_id = $this->Settings->default_warehouse;
            }     

        }else{
            $warehouse_id = $this->session->userdata('warehouse_id');
        }

        if ($id==0) {
            if ($return) {
                return "Vui lòng cung cấp ID";
            }else{
                exit('Vui lòng cung cấp ID');
                return;
            }
        }
        $loai=$_POST['loai'];
        $add=(boolean)$_POST['add'];
        

        if ($loai=='HOADON')
        {
            $order = $this->db->from('sales_history')->where('idauto', $id)->get()->row_array();
            
            if (!empty($order) && count($order)) {
                
                //load print order
                
                if ($return) {
                    return $this->print_order($order['id']);
                }else{
                   $print=$this->print_order($order['id']);
                   echo "<div style='width:".$print['size'].";margin:auto;'>".$print['noidung']."</div><style>img{ 	max-width:100%; 	height:auto; 	} 	<style>";
                   exit();
                   return; 
                }
            }else{
                if ($return) {
                    return "Không tìm thấy hóa đơn ".$id;
                }else{
                    exit("Không tìm thấy hóa đơn ".$id);
                    return;
                }
            } 
        }
        else if ($loai=='PHIEUTHU')
        {
            $order = $this->db->from('payments_history')->where('idauto', $id)->get()->row_array();
            
            if (!empty($order) && count($order)) {
                
                //load print order
                
                if ($return) {
                    return $this->printphieuthu($order['id']);
                }else{
                   $print=$this->printphieuthu($order['id']);
                   echo "<div style='width:".$print['size'].";margin:auto;'>".$print['noidung']."</div>";
                   exit();
                   return; 
                }
            }else{
                if ($return) {
                    return "Không tìm thấy phiếu thu ".$id;
                }else{
                    exit("Không tìm thấy phiếu thu".$id);
                    return;
                }
            } 
        }else if ($loai=='PHIEUCHI')
        {          
            
            //load print order
            if ($add==true)
            {
                $order = $this->db->from('payments_history')->where('idauto', $id)->get()->row_array();
        
                if (!empty($order) && count($order)) {
                    if ($return) {
                        return $this->printphieuchincc($order['id']);
                    }else{
                       $print=$this->printphieuchincc($order['id']);
                       echo "<div style='width:".$print['size'].";margin:auto;'>".$print['noidung']."</div>";
                       exit();
                       return; 
                    } 
                }else{
                    if ($return) {
                        return "Không tìm thấy phiếu chi ".$id;
                    }else{
                        exit("Không tìm thấy phiếu chi".$id);
                        return;
                    }
                } 
                   
            }else{
                $order = $this->db->from('expenses_history')->where('idauto', $id)->get()->row_array();
            
                if (!empty($order) && count($order)) {
                    if ($return) {
                        return $this->printphieuchi($order['id']);
                    }else{
                       $print=$this->printphieuchi($order['id']);
                       echo $print['noidung'];
                       exit();
                       return; 
                    } 
                }else{
                    if ($return) {
                        return "Không tìm thấy phiếu chi ".$id;
                    }else{
                        exit("Không tìm thấy phiếu chi".$id);
                        return;
                    }
                } 
            }               
             
        }        
               
    }
    function ReportThongBao_post()
    {        
        $rs=array('status'=>false,'mess'=>'INVALID authentication');
        if ($_SERVER['PHP_AUTH_USER'] == 'CIwiFtn8QMfIq2MNJqU8Q2lBOhKzpTTIdBBABC' && $_SERVER['PHP_AUTH_PW'] == md5('posbasic@2017#')) 
        {            

            $rs=array('status'=>false,'mess'=>'ERROR');

            $_POST['token']=$this->security->get_csrf_hash();
            if ($_POST['token']!=''&&$_POST['device_token']!='') {
                //check login by token
                $rslogin=$this->checkloginByToken($_POST['token'],$_POST['device_token']);
                if ($rslogin)
                {
                    
                        $page=isset($_POST['page'])?$_POST['page']:1;
                        $per_page=isset($_POST['per_page'])?$_POST['per_page']:10;
                        
                        $phanloai=isset($_POST['phanloai'])?$_POST['phanloai']:'';

                        $phanloai_array=isset($_POST['phanloai_array'])?$_POST['phanloai_array']:'';


                        $option=$_POST['data'];

                        if ($option['from'] == '' || $option['to'] == '') 
                        {
                            $option['from']=date("Y-m-d",strtotime('first day of this month'));
                            $option['to']=date("Y-m-d",strtotime('last day of this month'));
                        }
                        $option['to']= str_replace("/","-",$option['to']);
                        $option['from']= str_replace("/","-",$option['from']);
                        $option['to'] = date('Y-m-d', strtotime($option['to']));
                        $option['from'] = date('Y-m-d', strtotime($option['from']));

                        $where_add_date=" WHERE date(created) BETWEEN '".$option['from']."' AND '".$option['to']."'";
                        $where_add_his=" WHERE date(history_date) BETWEEN '".$option['from']."' AND '".$option['to']."'";     

                        if ($this->Admin||$this->Owner)
                        {                            

                        }else{                            
                            if (!$this->session->userdata('view_right'))
                            {                                                            
                                $where_add_date.=" AND created_by=".$this->session->userdata('user_id')." AND warehouse_id=".$this->session->userdata('warehouse_id');
                                $where_add_his.=" AND created_by=".$this->session->userdata('user_id')." AND warehouse_id=".$this->session->userdata('warehouse_id');   
                            }else{
                                $where_add_date.=" AND warehouse_id=".$this->session->userdata('warehouse_id');
                                $where_add_his.=" AND warehouse_id=".$this->session->userdata('warehouse_id');
                            }            
                        }

                        $report=[];
                        //get report san pham
                     
                        $query_phieuchi="SELECT history_date,history_auth,`reference` as transaction_code,idauto as transaction_id,history as loai, 'PHIEUCHI' as PHANLOAI,NULL as `ADD` FROM `scodeweb_expenses_history`".$where_add_his;

                        $query_phieuchi_ncc="SELECT history_date,history_auth,`reference_no` as transaction_code,idauto as transaction_id,history as loai, 'PHIEUCHI' as PHANLOAI,'TRUE' as `ADD` FROM `scodeweb_payments_history`".$where_add_his." AND type='sent'";

                        $query_phieuthu="SELECT history_date,history_auth,`reference_no` as transaction_code,idauto as transaction_id,history as loai, 'PHIEUTHU' as PHANLOAI,NULL as `ADD` FROM `scodeweb_payments_history`".$where_add_his." AND type!='sent'";
                       
                        $query_order="SELECT history_date,history_auth,`reference_no` as transaction_code,idauto as transaction_id,history as loai, 'HOADON' as PHANLOAI,NULL as `ADD` FROM `scodeweb_sales_history`".$where_add_his;

                        if ($phanloai_array=='') {
                            if ($phanloai=='PHIEUTHU') {
                                $report[]=$query_phieuthu;
                            }else if ($phanloai=='PHIEUCHI') {
                                $report[]=$query_phieuchi;
                                $report[]=$query_phieuchi_ncc;
                            }else if ($phanloai=='HOADON') {
                                $report[]=$query_order;
                            }else
                            {                              
                               $report=[$query_phieuthu,$query_phieuchi,$query_phieuchi_ncc,$query_order];                            
                            }
                        }else if ($phanloai_array!='') {
                            $phanloai_array=explode(",",$phanloai_array);
                            if (!empty($phanloai_array)){
                                foreach ($phanloai_array as $loai)
                                {
                                   if ($loai=='PHIEUTHU') {
                                        $report[]=$query_phieuthu;
                                    }else if ($loai=='PHIEUCHI') {
                                        $report[]=$query_phieuchi;
                                        $report[]=$query_phieuchi_ncc;
                                    }else if ($loai=='HOADON') {
                                        $report[]=$query_order;
                                    }                                   
                                }
                            }
                        }
                        
                        //UNION
                        $offset= ($page - 1) * $per_page;

                        $query_all="SELECT thongbao.* FROM(".implode(" UNION ",$report).") AS thongbao ORDER BY history_date DESC limit ".$per_page." OFFSET ".$offset;   

                        $query=$this->db->query($query_all);
                        $query_all=$query->result_array();  

                        $rs=array('status'=>true,'mess'=>'Thành công.','page'=>$page,'per_page'=>$per_page,'report'=>$query_all); 
                    
                                       
                }else{
                    $rs=array('status'=>false,'mess'=>'Token không chính xác.');                    
                }
            }else{
                $rs=array('status'=>false,'mess'=>'Token không hợp lệ');
            }       
        }       
        $this->response($rs);
    }
    public function printphieuchincc($id = null)
    {      

        $inv =  $this->purchases_model->getPaymentByID($id);        
             
        $this->data['barcode'] = "<img src='" . site_url('products/gen_barcode/' . $inv->reference_no) . "' alt='" . $inv->reference_no . "' class='pull-left' />";    
        $_tenkhach= "Khách lẻ";
        $_diachi=  "";  
        $_dienthoai="";
        $_congty="";
        if($inv->id_ncc_id_kh>0){
            $customer=$this->site->getCompanyByID($inv->id_ncc_id_kh); 
            $_tenkhach= $customer->name;
            $_diachi=  $customer->address;  
            $_dienthoai=$customer->phone;
            $_congty=$customer->company;
        }
        if($inv->purchase_id>0){
            $ncc_id=$this->purchases_model->getPurchaseByID($inv->purchase_id);
            
            $customer=$this->site->getCompanyByID($ncc_id->supplier_id); 
            $_tenkhach= $customer->name;
            $_diachi=  $customer->address;  
            $_dienthoai=$customer->phone;
            $_congty=$customer->company;
        }
        $ketoan=$this->site->getExCategoryByID($inv->type_cate); 
        $_t_no="";
        $_t_co="";
        if ($ketoan) {
            $_t_no=$ketoan->no;
            $_t_co=$ketoan->co;
        }
        $biller=$this->site->getCompanyByID($inv->created_by);       
        
        $created_by=$this->site->getUser($inv->created_by); 
        
        $warehouse=$this->site->getWarehouseByID($inv->warehouse_id);      
               
        $_dc_cuahang=str_replace("<p>","",$warehouse->address);       
        $_dc_cuahang=str_replace("</p>","",$_dc_cuahang);
        
        $ghichu=str_replace("<p>","",$inv->note);
        $ghichu=str_replace("</p>","",$ghichu);
        $bang_chu=$this->site->convert_number_to_words((float)$inv->amount)." đồng";
        
        $parse_data = array('So_Phieu' => $inv->reference_no,'Khach_Hang' =>$_tenkhach,'Cong_ty' => $_congty,'site_link' => base_url(),'site_name' => $this->Settings->site_name,'logo' => '<img src="' . base_url() . 'assets/uploads/logos/' . $this->Settings->logo . '" alt="' . ($biller->company != '-' ? $biller->company : $biller->name) . '"/>','Ten_Cua_Hang' => $warehouse->name,'Dia_Chi_Cua_Hang' =>$_dc_cuahang,'SDT_Cua_Hang' =>$warehouse->phone,'Dia_chi_kh' =>$_diachi,'Dien_thoai_kh' => $_dienthoai,'Ngay' => $this->sma->hrld($inv->date),'So_Tien' =>$this->sma->formatMoney($inv->amount),'Nhan_Vien' =>$created_by->first_name . ' ' . $created_by->last_name,'Ly_Do' =>$this->sma->decode_html($ghichu),'D_Ngay' =>date("d"),'D_Thang' =>date("m"),'D_Nam' =>date("Y"),'So_Tien_Bang_Chu'=>$bang_chu,'KT_NO'=>$_t_no,'KT_CO'=>$_t_co);    
        
        if (file_exists('./themes/' . $this->theme . '/views/thuchi_templates/phieuchi.html')) {          
            $sale_temp = file_get_contents('themes/' . $this->theme . '/views/thuchi_templates/phieuchi.html');            
        } else {             
            $sale_temp = file_get_contents('./themes/default/views/thuchi_templates/phieuchi.html');          
        }        
        $_get_active_pos=$this->settings_model->get_print_value($sale_temp);    
        $rs_ex_pos=explode(":",$_get_active_pos);
        
        $_sizein_page=isset($rs_ex_pos[0])&&$rs_ex_pos[0]!=":"?$rs_ex_pos[0]:"A5";
        $_chieuin_page=isset($rs_ex_pos[1])&&$rs_ex_pos[1]!=":"?$rs_ex_pos[1]:"Portrait";
        
        $kich_thuoc=$this->data['kich_thuoc']=$this->settings_model->define_size_in($_sizein_page,$_chieuin_page);  
        
        //replace value size print
        $sale_temp=$this->settings_model->define_print_replace($sale_temp);
            
        $message = $this->parser->parse_string($sale_temp, $parse_data,true);
        $message=str_replace("\r","",$message);
        $message=str_replace("\n","",$message);
        $message=str_replace("\"","'",$message);
         $message= "<div style='width:".$kich_thuoc.";margin:auto;'>".$message."</div>";
         return array('size'=>$kich_thuoc,'noidung'=>$message);     
    }
    public function printphieuthu($id = null)
    {               
        $inv =  $this->purchases_model->getPaymentByID($id);        
               
        $this->data['barcode'] = "<img src='" . site_url('products/gen_barcode/' . $inv->reference_no) . "' alt='" . $inv->reference_no . "' class='pull-left' />";    
        $_tenkhach= $inv->c_name;
        $_diachi=  $inv->c_address; 
        $_dienthoai=$inv->c_phone;
        $_congty="";
        if($inv->id_ncc_id_kh>0){
             $customer=$this->site->getCompanyByID($inv->id_ncc_id_kh); 
            // $_tenkhach= $customer->name;
            // $_diachi=  $customer->address;   
            // $_dienthoai=$customer->phone;
            $_congty=$customer->company;
        }
        if($inv->sale_id>0){
            $ncc_id=$this->sales_model->getInvoiceByID($inv->sale_id);          
            // $customer=$this->site->getCompanyByID($ncc_id->customer_id); 
            // $_tenkhach= $customer->name;
            // $_diachi=  $customer->address;   
            // $_dienthoai=$customer->phone;
            $_congty=$customer->company;
        }
        $_tenkhach= $_tenkhach!=''?$_tenkhach:'Khách lẻ';

        $biller=$this->site->getCompanyByID($inv->created_by);       
        $ketoan=$this->site->getExCategoryByID($inv->type_cate); 
        $_t_no="";
        $_t_co="";
        if ($ketoan) {
            $_t_no=$ketoan->no;
            $_t_co=$ketoan->co;
        }
        $created_by=$this->site->getUser($inv->created_by); 
        
        $warehouse=$this->site->getWarehouseByID($inv->warehouse_id);      
               
        $_dc_cuahang=str_replace("<p>","",$warehouse->address);       
        $_dc_cuahang=str_replace("</p>","",$_dc_cuahang);
        
        $ghichu=str_replace("<p>","",$inv->note);
        $ghichu=str_replace("</p>","",$ghichu);
        $bang_chu=$this->site->convert_number_to_words((float)$inv->amount)." đồng";
        
        $parse_data = array('So_Phieu' => $inv->reference_no,'Khach_Hang' =>$_tenkhach,'Cong_ty' => $_congty,'site_link' => base_url(),'site_name' => $this->Settings->site_name,'logo' => '<img src="' . base_url() . 'assets/uploads/logos/' . $this->Settings->logo . '" alt="' . ($biller->company != '-' ? $biller->company : $biller->name) . '"/>','Ten_Cua_Hang' => $warehouse->name,'Dia_Chi_Cua_Hang' =>$_dc_cuahang,'SDT_Cua_Hang' =>$warehouse->phone,'Dia_chi_kh' =>$_diachi,'Dien_thoai_kh' => $_dienthoai,'Ngay' => $this->sma->hrld($inv->date),'So_Tien' =>$this->sma->formatMoney($inv->amount),'Nhan_Vien' =>$created_by->first_name . ' ' . $created_by->last_name,'Ly_Do' =>$this->sma->decode_html($ghichu),'D_Ngay' =>date("d"),'D_Thang' =>date("m"),'D_Nam' =>date("Y"),'So_Tien_Bang_Chu'=>$bang_chu,'KT_NO'=>$_t_no,'KT_CO'=>$_t_co);    
        
        if (file_exists('./themes/' . $this->theme . '/views/thuchi_templates/phieuthu.html')) {          
            $sale_temp = file_get_contents('themes/' . $this->theme . '/views/thuchi_templates/phieuthu.html');            
        } else {             
            $sale_temp = file_get_contents('./themes/default/views/thuchi_templates/phieuthu.html');          
        }        
        $_get_active_pos=$this->settings_model->get_print_value($sale_temp);    
        $rs_ex_pos=explode(":",$_get_active_pos);
        
        $_sizein_page=isset($rs_ex_pos[0])&&$rs_ex_pos[0]!=":"?$rs_ex_pos[0]:"A5";
        $_chieuin_page=isset($rs_ex_pos[1])&&$rs_ex_pos[1]!=":"?$rs_ex_pos[1]:"Portrait";
        
        $kich_thuoc=$this->data['kich_thuoc']=$this->settings_model->define_size_in($_sizein_page,$_chieuin_page);  
        
        //replace value size print
        $sale_temp=$this->settings_model->define_print_replace($sale_temp);
            
        $message = $this->parser->parse_string($sale_temp, $parse_data,true);
        $message=str_replace("\r","",$message);
        $message=str_replace("\n","",$message);
        $message=str_replace("\"","'",$message);

        $message= "<div style='width:".$kich_thuoc.";margin:auto;'>".$message."</div>";

        return array('size'=>$kich_thuoc,'noidung'=>$message);    
    }
    public function printphieuchi($id = null)
    {                
        $inv =  $this->purchases_model->getExpenseByID($id);
                        
        $this->data['barcode'] = "<img src='" . site_url('products/gen_barcode/' . $inv->reference) . "' alt='" . $inv->reference . "' class='pull-left' />";    
        $_tenkhach= $inv->c_name;
        $_diachi=  $inv->c_address; 
        $_dienthoai=$inv->c_phone;
        $_congty="";
        if($inv->customer_id>0){
             $customer=$this->site->getCompanyByID($inv->customer_id); 
            // $_tenkhach= $customer->name;
            // $_diachi=  $customer->address;   
            // $_dienthoai=$customer->phone;
            $_congty=$customer->company;
        }
        if($inv->nhanvien_id>0){
             $customer=$this->site->getUser($inv->nhanvien_id);   
            // $_tenkhach= $customer->first_name . ' ' . $customer->last_name;
            // $_diachi=  $customer->company;   
            // $_dienthoai=$customer->phone;
            $_congty=$customer->company;
        }
        $_tenkhach=$_tenkhach!=''?$_tenkhach:"Khách lẻ";

        $ketoan=$this->site->getExCategoryByID($inv->category_id);       
        $_t_no="";
        $_t_co="";
        if ($ketoan) {
            $_t_no=$ketoan->no;
            $_t_co=$ketoan->co;
        }
        $biller=$this->site->getCompanyByID($inv->created_by);       
        
        $created_by=$this->site->getUser($inv->created_by); 
        
        $warehouse=$this->site->getWarehouseByID($inv->warehouse_id);      
               
        $_dc_cuahang=str_replace("<p>","",$warehouse->address);       
        $_dc_cuahang=str_replace("</p>","",$_dc_cuahang);
        
        $ghichu=str_replace("<p>","",$inv->note);
        $ghichu=str_replace("</p>","",$ghichu);
        $bang_chu=$this->site->convert_number_to_words((float)$inv->amount)." đồng";
        
        $parse_data = array('So_Phieu' => $inv->reference,'Khach_Hang' =>$_tenkhach,'Cong_ty' => $_congty,'site_link' => base_url(),'site_name' => $this->Settings->site_name,'logo' => '<img src="' . base_url() . 'assets/uploads/logos/' . $this->Settings->logo . '" alt="' . ($biller->company != '-' ? $biller->company : $biller->name) . '"/>','Ten_Cua_Hang' => $warehouse->name,'Dia_Chi_Cua_Hang' =>$_dc_cuahang,'SDT_Cua_Hang' =>$warehouse->phone,'Dia_chi_kh' =>$_diachi,'Dien_thoai_kh' => $_dienthoai,'Ngay' => $this->sma->hrld($inv->date),'So_Tien' =>$this->sma->formatMoney($inv->amount),'Nhan_Vien' =>$created_by->first_name . ' ' . $created_by->last_name,'Ly_Do' =>$this->sma->decode_html($ghichu),'D_Ngay' =>date("d"),'D_Thang' =>date("m"),'D_Nam' =>date("Y"),'So_Tien_Bang_Chu'=>$bang_chu,'KT_NO'=>$_t_no,'KT_CO'=>$ketoan->co);    
        
        if (file_exists('./themes/' . $this->theme . '/views/thuchi_templates/phieuchi.html')) {          
            $sale_temp = file_get_contents('themes/' . $this->theme . '/views/thuchi_templates/phieuchi.html');            
        } else {             
            $sale_temp = file_get_contents('./themes/default/views/thuchi_templates/phieuchi.html');          
        }        
        $_get_active_pos=$this->settings_model->get_print_value($sale_temp);    
        $rs_ex_pos=explode(":",$_get_active_pos);
        
        $_sizein_page=isset($rs_ex_pos[0])&&$rs_ex_pos[0]!=":"?$rs_ex_pos[0]:"A5";
        $_chieuin_page=isset($rs_ex_pos[1])&&$rs_ex_pos[1]!=":"?$rs_ex_pos[1]:"Portrait";
        
        $kich_thuoc=$this->data['kich_thuoc']=$this->settings_model->define_size_in($_sizein_page,$_chieuin_page);    
        
        //replace value size print
        $sale_temp=$this->settings_model->define_print_replace($sale_temp);
            
        $message = $this->parser->parse_string($sale_temp, $parse_data,true);
        $message=str_replace("\r","",$message);
        $message=str_replace("\n","",$message);
        $message=str_replace("\"","'",$message);
         $message= "<div style='width:".$kich_thuoc.";margin:auto;'>".$message."</div>";
         return array('size'=>$kich_thuoc,'noidung'=>$message);   
    }
    function getConfigKhuyenmai_post()
    {        
        $rs=array('status'=>false,'mess'=>'INVALID authentication');
        if ($_SERVER['PHP_AUTH_USER'] == 'CIwiFtn8QMfIq2MNJqU8Q2lBOhKzpTTIdBBABC' && $_SERVER['PHP_AUTH_PW'] == md5('posbasic@2017#')) 
        {
            $rs=array('status'=>false,'mess'=>'ERROR');
            $_POST['token']=$this->security->get_csrf_hash();
            if ($_POST['token']!=''&&$_POST['device_token']!='') {
                //check login by token
                $rslogin=$this->checkloginByToken($_POST['token'],$_POST['device_token']);
                if ($rslogin)
                {
                   //tien hanh lay danh sach khuyen mai theo san pham neu co 
                    $khuyenmai_main=$this->getKhuyenmainewsByNow();
                    $khuyenmai_product=$this->getKhuyenmainewsProductByNow();      
                    $main_product=array();
                    if(count($khuyenmai_main)>0){               
                        foreach($khuyenmai_main as $main_pr){
                            $main_product[]=$khuyenmai_product[$main_pr->id];

                        }               
                    }
                         

                    $rs=array('status'=>true,'khuyenmai'=>$main_product); 
                                                            
                                       
                }else{
                    $rs=array('status'=>false,'mess'=>'Token không chính xác.');                    
                }
            }else{
                $rs=array('status'=>false,'mess'=>'Token không hợp lệ');
            }       
        }
        $this->response($rs);
    }
     public function getKhuyenmainewsByNow($warehouse_id = null)
    {       
        $add="";
        
        if ($this->Admin||$this->Owner)
        {
            if (isset($_POST['warehouse'])&&$_POST['warehouse']!='') 
            {
                $warehouse_id =$_POST['warehouse'];
                $objwh=$this->site->getWarehouseByID($warehouse_id);
                if (empty($objwh)||$objwh==false) {
                    $rs=array('status'=>false,'mess'=>'ID kho hàng (warehouse='.$warehouse_id.') không hợp lệ');  
                    $this->response($rs);
                    return;
                }
                $add=" AND scodeweb_khuyenmai.warehouse_id=".$warehouse_id; 
            } else {
                $warehouse_id = $this->Settings->default_warehouse;
                $add=" AND scodeweb_khuyenmai.warehouse_id=".$warehouse_id; 
            }     

        }else{
            $warehouse_id = $this->session->userdata('warehouse_id');
            $add=" AND scodeweb_khuyenmai.warehouse_id=".$warehouse_id; 
        }
        
        $today=date("Y-m-d H:i:s");     
        $add=" AND (startdate<'".$today."' AND enddate>='".$today."')"; 
        
        $query="SELECT id,tenevent,main_product_id,main_quantity FROM scodeweb_khuyenmai WHERE type=1 $add";    
        $q = $this->db->query($query,false);
        if ($q->num_rows() > 0)  
        { 
            foreach (($q->result()) as $row) {
                $data[$row->main_product_id] = $row;

            }
            return $data; 
        }
    }
    public function getKhuyenmainewsProductByNow($warehouse_id = null)
    {       
        $add="";
        if ($this->Admin||$this->Owner)
        {
            if (isset($_POST['warehouse'])&&$_POST['warehouse']!='') 
            {
                $warehouse_id =$_POST['warehouse'];
                $objwh=$this->site->getWarehouseByID($warehouse_id);
                if (empty($objwh)||$objwh==false) {
                    $rs=array('status'=>false,'mess'=>'ID kho hàng (warehouse='.$warehouse_id.') không hợp lệ');  
                    $this->response($rs);
                    return;
                }
                $add=" AND scodeweb_khuyenmai.warehouse_id=".$warehouse_id; 
            } else {
                $warehouse_id = $this->Settings->default_warehouse;
                $add=" AND scodeweb_khuyenmai.warehouse_id=".$warehouse_id; 
            }     

        }else{
            $warehouse_id = $this->session->userdata('warehouse_id');
            $add=" AND scodeweb_khuyenmai.warehouse_id=".$warehouse_id; 
        }

        
        $today=date("Y-m-d H:i:s"); 
        $add=" AND (scodeweb_khuyenmai.startdate<'".$today."' AND scodeweb_khuyenmai.enddate>='".$today."')";   
        
        $query="SELECT scodeweb_khuyenmai.id,scodeweb_khuyenmai.tenevent,main_product_id,main_quantity,scodeweb_khuyenmai_items.product_id as sub_product_id,scodeweb_khuyenmai_items.giakhuyenmai as giakhuyenmai,scodeweb_khuyenmai_items.sub_quantity as sub_quantity FROM scodeweb_khuyenmai,scodeweb_khuyenmai_items WHERE scodeweb_khuyenmai_items.khuyenmai_id=scodeweb_khuyenmai.id AND scodeweb_khuyenmai.type=1 $add";    
        
        $q = $this->db->query($query,false);
        if ($q->num_rows() > 0) 
        { 
            foreach (($q->result()) as $row) {
                
                $obj_prod = $this->getProductByIdApi($row->sub_product_id,NULL,NULL,$warehouse_id,$row->giakhuyenmai);                
                $row->proobj=$obj_prod;
                $data[$row->id][] = $row;

            }
            return $data; 
        }
    }
    function addPaymentByCodeApi_post()
    {        
        $rs=array('status'=>false,'mess'=>'INVALID authentication');
        if ($_SERVER['PHP_AUTH_USER'] == 'CIwiFtn8QMfIq2MNJqU8Q2lBOhKzpTTIdBBABC' && $_SERVER['PHP_AUTH_PW'] == md5('posbasic@2017#')) 
        {
            $rs=array('status'=>false,'mess'=>'ERROR');
            $_POST['token']=$this->security->get_csrf_hash();
            if ($_POST['token']!=''&&$_POST['device_token']!='') {
                //check login by token
                $rslogin=$this->checkloginByToken($_POST['token'],$_POST['device_token']);
                if ($rslogin)
                {
                   //tien hanh lay danh sach khuyen mai theo san pham neu co 
                    if ($_POST['code']=='')
                    {                        
                        $rs=array('status'=>false,'mess'=>'Vui lòng nhập chính xác mã thanh toán '.$_POST['code']); 

                    }else{
                        
                        $list=$this->site->addPaymentByCode($_POST['code'],$_POST['note']); 
                        if (strpos($list,"|OK") == false) {
                            $rs=array('status'=>false,'mess'=>$list);     
                        }                         
                        else{
                            $rs=array('status'=>true,'mess'=>str_replace("|OK","",$list));     
                            
                        }
                                                                                     
                    }                  
                                                            
                                       
                }else{
                    $rs=array('status'=>false,'mess'=>'Token không chính xác.');                    
                }
            }else{
                $rs=array('status'=>false,'mess'=>'Token không hợp lệ');
            }       
        }
        $this->response($rs);
    }
    function getPaymentByApi_post()
    {        
        $rs=array('status'=>false,'mess'=>'INVALID authentication');
        if ($_SERVER['PHP_AUTH_USER'] == 'CIwiFtn8QMfIq2MNJqU8Q2lBOhKzpTTIdBBABC' && $_SERVER['PHP_AUTH_PW'] == md5('posbasic@2017#')) 
        {
            $rs=array('status'=>false,'mess'=>'ERROR');
            $_POST['token']=$this->security->get_csrf_hash();
            if ($_POST['token']!=''&&$_POST['device_token']!='') {
                //check login by token
                $rslogin=$this->checkloginByToken($_POST['token'],$_POST['device_token']);
                if ($rslogin)
                {
                    $page=isset($_POST['page'])?$_POST['page']:1;
                    $per_page=isset($_POST['per_page'])?$_POST['per_page']:10;
                    $offset= ($page - 1) * $per_page;

                    $list=$this->site->loadListUsingByUser(['per_page'=>$per_page,'offset'=>$offset]);
                                        
                    $rs=array('status'=>true,'list'=>$list); 
                                                            
                                       
                }else{
                    $rs=array('status'=>false,'mess'=>'Token không chính xác.');                    
                }
            }else{
                $rs=array('status'=>false,'mess'=>'Token không hợp lệ');
            }       
        }
        $this->response($rs);
    }
    function getAllPackage_post() 
    {        
        $rs=array('status'=>false,'mess'=>'INVALID authentication');
        if ($_SERVER['PHP_AUTH_USER'] == 'CIwiFtn8QMfIq2MNJqU8Q2lBOhKzpTTIdBBABC' && $_SERVER['PHP_AUTH_PW'] == md5('posbasic@2017#')) 
        {
            $rs=array('status'=>false,'mess'=>'ERROR');
            $_POST['token']=$this->security->get_csrf_hash();
            if ($_POST['token']!=''&&$_POST['device_token']!='') {
                //check login by token
                $rslogin=$this->checkloginByToken($_POST['token'],$_POST['device_token']);
                if ($rslogin)
                {
                    $list=$this->site->getAllPackage();                                        
                    $rs=array('status'=>true,'list'=>$list); 
                                                            
                                       
                }else{
                    $rs=array('status'=>false,'mess'=>'Token không chính xác.');                    
                }
            }else{
                $rs=array('status'=>false,'mess'=>'Token không hợp lệ');
            }       
        }
        $this->response($rs);
    }
    function getConfigHetHan_post() 
    {        
        $rs=array('status'=>false,'mess'=>'INVALID authentication');
        if ($_SERVER['PHP_AUTH_USER'] == 'CIwiFtn8QMfIq2MNJqU8Q2lBOhKzpTTIdBBABC' && $_SERVER['PHP_AUTH_PW'] == md5('posbasic@2017#')) 
        {
            $rs=array('status'=>false,'mess'=>'ERROR');
            $_POST['token']=$this->security->get_csrf_hash();
            if ($_POST['token']!=''&&$_POST['device_token']!='') {
                //check login by token
                $rslogin=$this->checkloginByToken($_POST['token'],$_POST['device_token']);
                if ($rslogin)
                {
                    
                    $hethan=$this->site->DayUsingLeft(); 
                    $package_rs = $this->site->getPackageByUser();   

                    $package=array('title'=>$package_rs['title'],'sotien'=>$package_rs['sotien_to']==0?'Miễn phí':$package_rs['sotien_to']);
                    $rs=array('status'=>true,'hethan'=>date("d/m/Y",strtotime($hethan)),'package'=>$package);


                                                            
                                       
                }else{
                    $rs=array('status'=>false,'mess'=>'Token không chính xác.');                    
                }
            }else{
                $rs=array('status'=>false,'mess'=>'Token không hợp lệ');
            }       
        }
        $this->response($rs);
    }
    public function status_ios_get()
    {
        
        $this->response(array('status'=>true,'mess'=>''),REST_Controller::HTTP_OK);
        
    }

    /*module nhap kho lhson 06/04/2022*/
    public function fillter_purchares()
    {
        
        if ($_POST['keywords']) {
            $this->db->where("(" . $this->db->dbprefix('purchases') . ".reference_no LIKE '%" . $_POST['keywords'] . "%' OR supplier LIKE '%" . $_POST['keywords'] . "%')");
        }
        if ($this->Admin||$this->Owner)
        {
            if (isset($_POST['warehouse'])&&$_POST['warehouse']!='') 
            {
                $warehouse_id =$_POST['warehouse'];                
            } else {
                $warehouse_id = 0;
            }     

        }else{
            $warehouse_id = $this->session->userdata('warehouse_id');
            //if view_right
            if (!$this->session->userdata('view_right')) {
                $this->db->where('purchases.created_by', $this->session->userdata('user_id'));    
            }            
        }
        if ($warehouse_id>0) {
            $this->db->where('purchases.warehouse_id', $warehouse_id);
        }
        $doitac=isset($_POST['doitac'])?$_POST['doitac']:NULL;
        if ($doitac>0) {
            $this->db->where('purchases.doitac', $doitac);
        }
        $purchase_status=isset($_POST['purchase_status'])?$_POST['purchase_status']:NULL;
        if ($purchase_status!='') {
            $this->db->where('purchases.purchase_status', $purchase_status);
        }
        $payment_status=isset($_POST['payment_status'])?$_POST['payment_status']:NULL;
        if ($payment_status!='') {
            $this->db->where('purchases.payment_status', $payment_status);
        }
        $supplier_id=isset($_POST['supplier_id'])?$_POST['supplier_id']:NULL;
        if ($supplier_id>0) {
            $this->db->where('purchases.supplier_id', $supplier_id);
        }

        $start='';
        if (isset($_POST['start'])&&$_POST['start']!='') {
            $start=date("Y-m-d",strtotime(str_replace("/","-",$_POST['start']))).' 00:00:00';
        }
        $end='';
        if (isset($_POST['end'])&&$_POST['end']!='') {
            $end=date("Y-m-d",strtotime(str_replace("/","-",$_POST['end']))).' 23:59:59';
        }
        if ($start!=''&&$end!='') {
            $this->db->where($this->db->dbprefix('purchases').'.date BETWEEN "' . $start . '" and "' . $end . '"');
        }
        
    }
    function ListNhaphang_post()
    {        
        $rs=array('status'=>false,'mess'=>'INVALID authentication');
        if ($_SERVER['PHP_AUTH_USER'] == 'CIwiFtn8QMfIq2MNJqU8Q2lBOhKzpTTIdBBABC' && $_SERVER['PHP_AUTH_PW'] == md5('posbasic@2017#')) 
        {            

            $rs=array('status'=>false,'mess'=>'ERROR');

            $_POST['token']=$this->security->get_csrf_hash();
            if ($_POST['token']!=''&&$_POST['device_token']!='') {
                //check login by token
                $rslogin=$this->checkloginByToken($_POST['token'],$_POST['device_token']);
                if ($rslogin)
                { 
                    if ($this->Admin||$this->Owner)
                    {
                        if (isset($_POST['warehouse'])&&$_POST['warehouse']!='') 
                        {
                            $warehouse_id =$_POST['warehouse'];
                            $objwh=$this->site->getWarehouseByID($warehouse_id);
                            if (empty($objwh)||$objwh==false) {
                                $rs=array('status'=>false,'mess'=>'ID kho hàng (warehouse='.$warehouse_id.') không hợp lệ');  
                                $this->response($rs);
                                return;
                            }
                        } else {
                            $warehouse_id = $this->Settings->default_warehouse;
                        }     

                    }else{
                        $warehouse_id = $this->session->userdata('warehouse_id');
                    }

                    $page=isset($_POST['page'])?$_POST['page']:1;
                    $per_page=isset($_POST['per_page'])?$_POST['per_page']:10;
                    $offset= ($page - 1) * $per_page;

                    $doitac=isset($_POST['doitac'])?$_POST['doitac']:NULL;
                    $doitac_obj = $this->site->getDoitacByID($doitac);
                    if ($doitac>0&&$doitac_obj==false) 
                    {
                        $rs=array('status'=>false,'mess'=>'Đối tác không hợp lệ');  
                        $this->response($rs);
                        return;
                    }

                    $purchase_status=isset($_POST['purchase_status'])?$_POST['purchase_status']:NULL;
                    $payment_status=isset($_POST['payment_status'])?$_POST['payment_status']:NULL;
                    $supplier_id=isset($_POST['supplier_id'])?$_POST['supplier_id']:NULL;
                    $customer_details = $this->site->getCompanyByID($supplier_id);
                    if ($supplier_id>0&&$customer_details==false) 
                    {
                        $rs=array('status'=>false,'mess'=>'Không có nhà cung cấp');  
                        $this->response($rs);
                        return;
                    }
                    //total
                    $this->db->select("purchases.id as id")->from('purchases');
                    $this->fillter_purchares();
                    $q = $this->db->get(); 
                    $total=$q->num_rows();

                    $this->db
                        ->select("purchases.id as id, DATE_FORMAT(scodeweb_purchases.date, '%Y-%m-%d %T') as date, reference_no,warehouses.name as kho,doitac.name as dvgh,concat(scodeweb_users.first_name,' ',scodeweb_users.last_name) as nhanvien, supplier, status, grand_total, paid, (grand_total-paid) as balance, payment_status, purchases.attachment")
                        ->from('purchases')
                        ->join('users', 'users.id=purchases.created_by', 'left')
                        ->join('doitac', 'doitac.id=purchases.doitac', 'left')
                        ->join('warehouses', 'warehouses.id=purchases.warehouse_id', 'left')
                        ->where('purchases.warehouse_id', $warehouse_id);
            
                    $this->db->where('status !=', 'returned');
                
                    
                    $this->fillter_purchares();

                    $this->db->limit($per_page,$offset);
                    
                    $this->db->order_by('purchases.date','desc');

                    $q = $this->db->get();            
                    if ($q->num_rows() > 0) {
                        foreach (($q->result()) as $row) {
                            $bills[] = $row;
                        }
                    } else {
                        $bills = NULL;
                    }
                   // echo var_dump($bills);
                    if (!empty($bills)) {
                        foreach ($bills as $bill)
                        {
                            $bill->supplier_obj=$this->site->getCompanyByID($bill->supplier_id);
                            $bill->user_obj=$this->site->getUser($bill->created_by);
                        }
                    }
 
                    $rs=array('status'=>true,'mess'=>'Thành công.','page'=>$page,'per_page'=>$per_page,'count'=>$total,'list'=>$bills); 
                    
                                       
                }else{ 
                    $rs=array('status'=>false,'mess'=>'Token không chính xác.');                    
                }
            }else{
                $rs=array('status'=>false,'mess'=>'Token không hợp lệ');
            }       
        }       
        $this->response($rs);
    }
    function getPurcharseById_post()
    {
        $rs=array('status'=>false,'mess'=>'INVALID authentication');
        if ($_SERVER['PHP_AUTH_USER'] == 'CIwiFtn8QMfIq2MNJqU8Q2lBOhKzpTTIdBBABC' && $_SERVER['PHP_AUTH_PW'] == md5('posbasic@2017#')) 
        {
            $rs=array('status'=>false,'mess'=>'ERROR');
            $_POST['token']=$this->security->get_csrf_hash();
            if ($_POST['token']!=''&&$_POST['device_token']!='') {
                //check login by token
                $rslogin=$this->checkloginByToken($_POST['token'],$_POST['device_token']);
                if ($rslogin)
                {
                    
                    if (isset($_POST['purchase_id'])) 
                    {
                        $sid=$_POST['purchase_id'];
                        if ($inv = $this->site->getPurchaseByID($sid)) {
                            $inv_items = $this->purchases_model->getAllPurchaseItems($sid);
                            krsort($inv_items);
                        
                            

                            $this->data=null;
                            
                            $this->data['invoice'] = $inv;
                            $this->data['items'] = $inv_items;
                            $this->data['supplier'] = $this->site->getCompanyByID($inv->supplier_id);
                            $this->data['warehouse'] = $this->site->getWarehouseByID($inv->warehouse_id);                            
                            $this->data['payments'] = $this->purchases_model->getPaymentsForPurchase($inv->id);
                            $this->data['created_by'] = $this->site->getUser($inv->created_by);
                            $this->data['updated_by'] = $inv->updated_by ? $this->site->getUser($inv->updated_by) : null;
                            

                            $rs=array('status'=>true,'mess'=>'Thành công.','item'=>$this->data); 

                        } else {
                           $rs=array('status'=>false,'mess'=>'Không tìm thấy hóa đơn.','item'=>null); 
                        }
                    }else{
                        $rs=array('status'=>false,'mess'=>'Không tìm thấy hóa đơn.','item'=>null); 
                    }
                  
                    
                }else{
                    $rs=array('status'=>false,'mess'=>'Token không chính xác.');                    
                }
            }else{
                $rs=array('status'=>false,'mess'=>'Token không hợp lệ');
            }       
        }
        $this->response($rs);
    }
    function HuyPurchare_post()
    {
        $rs=array('status'=>false,'mess'=>'INVALID authentication');
        if ($_SERVER['PHP_AUTH_USER'] == 'CIwiFtn8QMfIq2MNJqU8Q2lBOhKzpTTIdBBABC' && $_SERVER['PHP_AUTH_PW'] == md5('posbasic@2017#')) 
        {
            $rs=array('status'=>false,'mess'=>'ERROR');
            $_POST['token']=$this->security->get_csrf_hash();
            if ($_POST['token']!=''&&$_POST['device_token']!='') {
                //check login by token
                $rslogin=$this->checkloginByToken($_POST['token'],$_POST['device_token']);
                if ($rslogin)
                {
                    if(!$this->Owner && !$this->Admin) {
                        if ($this->data['GP']['purchase-delete']!=1) {
                            $rs=array('status'=>false,'mess'=>'Không có quyền');  
                            $this->response($rs);
                            return;
                        }
                    }                    
                    if (isset($_POST['purchase_id'])) 
                    {
                        $sid=$_POST['purchase_id'];
                        if ($order = $this->site->getPurchaseByID($sid)) {                           
                            if(!$this->Owner && !$this->Admin) {
                                if ($order->created_by!=$this->session->userdata('user_id')) {
                                    $rs=array('status'=>false,'mess'=>'Không có quyền xóa đơn hàng này');  
                                    $this->response($rs);
                                    return;
                                }
                            } 

                            $check=$this->purchases_model->deletePurchase($sid);
                            if ($check) {
                                $rs=array('status'=>true,'mess'=>'Thành công.','item'=>$order); 
                            }else{
                                $rs=array('status'=>false,'mess'=>'Lỗi xóa hóa đơn.','item'=>null);     
                            }                            
                        } else {
                           $rs=array('status'=>false,'mess'=>'Không tìm thấy hóa đơn.','item'=>null); 
                        }
                    }else{
                        $rs=array('status'=>false,'mess'=>'Không tìm thấy hóa đơn.','item'=>null); 
                    }
                  
                    
                }else{
                    $rs=array('status'=>false,'mess'=>'Token không chính xác.');                    
                }
            }else{
                $rs=array('status'=>false,'mess'=>'Token không hợp lệ');
            }       
        }
        $this->response($rs);
    }
    function PrintPurchareById_post()
    {
        $rs=array('status'=>false,'mess'=>'INVALID authentication');
        if ($_SERVER['PHP_AUTH_USER'] == 'CIwiFtn8QMfIq2MNJqU8Q2lBOhKzpTTIdBBABC' && $_SERVER['PHP_AUTH_PW'] == md5('posbasic@2017#')) 
        {
            $rs=array('status'=>false,'mess'=>'ERROR');
            $_POST['token']=$this->security->get_csrf_hash();
            if ($_POST['token']!=''&&$_POST['device_token']!='') {
                //check login by token
                $rslogin=$this->checkloginByToken($_POST['token'],$_POST['device_token']);
                if ($rslogin)
                {
                                     
                    if (isset($_POST['purchase_id'])) 
                    {
                        $sid=$_POST['purchase_id'];
                        if ($order = $this->site->getPurchaseByID($sid)) {                           
                            
                            $check=$this->print_purchase($sid);
                            if ($check!=false) {
                                $rs=array('status'=>true,'mess'=>'Thành công.','item'=>$check); 
                            }else{
                                $rs=array('status'=>false,'mess'=>'Lỗi in hóa đơn.','item'=>null);     
                            }                            
                        } else {
                           $rs=array('status'=>false,'mess'=>'Không tìm thấy hóa đơn.','item'=>null); 
                        }
                    }else{
                        $rs=array('status'=>false,'mess'=>'Không tìm thấy hóa đơn.','item'=>null); 
                    }
                  
                    
                }else{
                    $rs=array('status'=>false,'mess'=>'Token không chính xác.');                    
                }
            }else{
                $rs=array('status'=>false,'mess'=>'Token không hợp lệ');
            }       
        }
        $this->response($rs);
    }
    function print_purchase($purchase_id = NULL)
    {                
        $inv = $this->purchases_model->getPurchaseByID($purchase_id);        
        $rows=$this->purchases_model->getAllPurchaseItems($purchase_id);
        $supplier=$this->site->getCompanyByID($inv->supplier_id);
        $warehouse=$this->site->getWarehouseByID($inv->warehouse_id);
        
        $payments=$this->purchases_model->getPaymentsForPurchase($purchase_id);
        $created_by=$this->site->getUser($inv->created_by);
        $updated_by=$inv->updated_by ? $this->site->getUser($inv->updated_by) : null;
        $inv->return_id ? $this->purchases_model->getPurchaseByID($inv->return_id) : NULL;
        $inv->return_id ? $this->purchases_model->getAllPurchaseItems($inv->return_id) : NULL;
        $doitac=$this->doitac_model->getDoitacByID($inv->doitac);
        
        //tong no cu
        $_dathanhtoan= $this->reports_model->getPurchasesTotals($supplier->id);                     
        $_dathanhtoanlhson= $this->reports_model->getPurchasesTotalsLhson($supplier->id);
        
        $_dathanhtoan->paid=$_dathanhtoan->paid+$_dathanhtoanlhson;
        
        $no_cu=isset($_dathanhtoan->total_amount) || isset($_dathanhtoan->paid) ? $_dathanhtoan->total_amount -  $_dathanhtoan->paid:0;
        
        $company_details = $this->companies_model->getCompanyByID($supplier->id);       
        $no_cu+=(float)$company_details->nobandau;    
        //end tong no cu
        
        $_tablhd='<table border="1" style="width:100%;border-collapse:collapse;">        <tbody>        <tr>            <td style="text-align:center;width:5%;"><strong>STT</strong>            </td>            <td style="width:51%;padding-left:0.5%;"><strong>Tên hàng hóa</strong><br>            </td>          <td style="text-align:right;padding-right:0.5%;width:10%;"><strong>SL</strong>         </td>             <td style="text-align:center;width:15%;"><strong>Đơn giá</strong><br>            </td>            <td style="text-align:right;padding-right:0.5%;width:15.5%;"><strong>Thành tiền</strong><br>            </td>        </tr>';
        
        $r = 1;
        $tongsanpham=0;
        foreach ($rows as $row){
            
            $_prod_detail=$row->product_code.' - '.$row->product_name . ($row->variant ? ' (' . $row->variant . ')' : ''); 
            $_prod_detail.=($row->expiry && $row->expiry != '0000-00-00') ? '<br>' .lang('expiry').': ' . $this->sma->hrsd($row->expiry) : '';
                
                
                $_quan=$this->sma->formatQuantity($row->unit_quantity).' '.$row->product_unit_code;
                
                if ($inv->status == 'partial') {
                    $_quan=$this->sma->formatQuantity($row->quantity_received).' '.$row->product_unit_code;
                }
                
                
                $_tablhd.='<tr>
                <td style="text-align:center; width:40px; vertical-align:middle;">'.$r.'</td>
                <td style="vertical-align:middle;">'.$_prod_detail.'</td>';
                
                $_tablhd.='
                <td style="width: 80px; text-align:center; vertical-align:middle;">'.$_quan.'</td>
                <td style="text-align:right; width:100px;">'.$this->sma->formatMoney($row->net_unit_cost).'</td>';;
                
                $_bs='';
                if ($Settings->tax1 && $inv->product_tax > 0) {
                    $_bs.="Thuế: ".($row->item_tax != 0 && $row->tax_code? '<small>(' . $row->tax_code . ')</small> ' : '') . $this->sma->formatMoney($row->item_tax);
                }
                if ($Settings->product_discount != 0 && $inv->product_discount != 0) {
                    $_bs.="Giảm: ".($row->discount != 0 ? '<small>('.$row->discount.')</small>' : '') . ' ' . $this->sma->formatMoney($row->item_discount);
                }
                
                
                
                $_bs=$_bs!=""?"<br>".$_bs:"";
                $_tablhd.='<td style="text-align:right; width:120px;">'.$this->sma->formatMoney($row->subtotal).$_bs.'</td></tr>';
                
                $tongsanpham++;

            $r++;
        }
        if ($return_rows) {
                            
        $_tablhd.='<tr class="warning"><td colspan="100%" class="no-border"><strong>'.lang('returned_items').'</strong></td></tr>';
            foreach ($return_rows as $row){
                
                $_prod_detail=$row->product_code.' - '.$row->product_name . ($row->variant ? ' (' . $row->variant . ')' : ''); 
                $_prod_detail.=($row->expiry && $row->expiry != '0000-00-00') ? '<br>' .lang('expiry').': ' . $this->sma->hrsd($row->expiry) : '';              
                    
                $_quan=$this->sma->formatQuantity($row->unit_quantity).' '.$row->product_unit_code;         
                if ($inv->status == 'partial') {
                    $_quan=$this->sma->formatQuantity($row->quantity_received).' '.$row->product_unit_code;
                }
                $_tablhd.='<tr>
                <td style="text-align:center; width:40px; vertical-align:middle;">'.$r.'</td>
                <td style="vertical-align:middle;">'.$_prod_detail.'</td>';
                
                $_tablhd.='
                <td style="width: 80px; text-align:center; vertical-align:middle;">'.$_quan.'</td>
                <td style="text-align:right; width:100px;">'.$this->sma->formatMoney($row->net_unit_cost).'</td>';;
                
                $_bs='';
                if ($Settings->tax1 && $inv->product_tax > 0) {
                    $_bs.="Thuế: ".($row->item_tax != 0 && $row->tax_code? '<small>(' . $row->tax_code . ')</small> ' : '') . $this->sma->formatMoney($row->item_tax);
                }
                if ($Settings->product_discount != 0 && $inv->product_discount != 0) {
                    $_bs.="Giảm: ".($row->discount != 0 ? '<small>('.$row->discount.')</small>' : '') . ' ' . $this->sma->formatMoney($row->item_discount);
                }
                
                
                $_bs=$_bs!=""?"<br>".$_bs:"";
                $_tablhd.='<td style="text-align:right; width:120px;">'.$this->sma->formatMoney($row->subtotal).$_bs.'</td></tr>';
                $r++;
            }
        }
        $_tablhd.='</table>';           
        $tongcong=0;
        $thue=0;
        $giamgia=0;
        $_giamgia=0;
        if ($inv->grand_total != $inv->total) {
        
            if ($Settings->tax1 && $inv->product_tax > 0) {
                $thue=$this->sma->formatMoney($return_purchase ? ($inv->product_tax+$return_purchase->product_tax) : $inv->product_tax);
            }
            if ($Settings->product_discount && $inv->product_discount != 0) {
                $giamgia=$this->sma->formatMoney($return_purchase ? ($inv->product_discount+$return_purchase->product_discount) : $inv->product_discount);
                $_giamgia=$return_purchase ? ($inv->product_discount+$return_purchase->product_discount) : $inv->product_discount;
            }
            
            $tongcong=$this->sma->formatMoney($return_purchase ? (($inv->total + $inv->product_tax)+($return_purchase->total + $return_purchase->product_tax)) : ($inv->total + $inv->product_tax));
        }
        $tongtrahang=0; 
        if ($return_purchase) {
            $tongtrahang=$this->sma->formatMoney($return_purchase->grand_total);
        }
        $phitrahang=0;
        if ($inv->surcharge != 0) {
            $phitrahang=$this->sma->formatMoney($inv->surcharge);
        }
        $giamthem=0;
        $_giamthem=0;
        if ($inv->order_discount != 0) {
            $giamthem=($inv->order_discount_id ? '<small>('.$inv->order_discount_id.')</small> ' : '') . $this->sma->formatMoney($return_purchase ? ($inv->order_discount+$return_purchase->order_discount) : $inv->order_discount) ;
            $_giamthem=$return_purchase ? ($inv->order_discount+$return_purchase->order_discount) : $inv->order_discount;
        }
        $chieckhau=0;
        if ($inv->chiec_khau != 0) {
            $chieckhau=($inv->chiec_khau_id ? '<small>('.$inv->chiec_khau_id.')</small> ' : '') . $this->sma->formatMoney($return_purchase ? ($inv->order_discount+$return_purchase->order_discount+$inv->chiec_khau) : $inv->chiec_khau);
        }
         if ($Settings->tax2 && $inv->order_tax != 0) {
            $thue=$this->sma->formatMoney($return_purchase ? ($inv->order_tax+$return_purchase->order_tax) : $inv->order_tax);
        }
        $shipping=0;
        if ($inv->shipping != 0) {
            $shipping=$this->sma->formatMoney($inv->shipping);
        }
        $tonggiam=$_giamgia+$_giamthem;
        $tonggiam=$this->sma->formatMoney($tonggiam);
        
        $tongcong=$this->sma->formatMoney($return_purchase ? ($inv->grand_total+$return_purchase->grand_total) : $inv->grand_total);$dathanhtoan=$this->sma->formatMoney($return_purchase ? ($inv->paid+$return_purchase->paid) : $inv->paid); 
        
        $conlai=$this->sma->formatMoney(($return_purchase ? ($inv->grand_total+$return_purchase->grand_total) : $inv->grand_total) - ($return_purchase ? ($inv->paid+$return_purchase->paid) : $inv->paid));
        
        //$tong_no=$no_cu + ($return_purchase ? ($inv->grand_total+$return_purchase->grand_total) : $inv->grand_total) - ($return_purchase ? ($inv->paid+$return_purchase->paid) : $inv->paid);
        $tong_no=$no_cu;
        
        $tong_no=$this->sma->formatMoney($tong_no);
        
        $_tongcong_bang_chu=$return_purchase ? ($inv->grand_total+$return_purchase->grand_total) : $inv->grand_total;
        
        $left_end=substr($_tongcong_bang_chu,strlen($_tongcong_bang_chu)-5,strlen($_tongcong_bang_chu));
        if($left_end=='.0000'){
             $_tongcong_bang_chu=str_replace($left_end,"",$_tongcong_bang_chu);
         }
        $_tongcong_bang_chu_text=$this->site->convert_number_to_words($_tongcong_bang_chu);
        
        $_dc_cuahang=str_replace("<p>","",$warehouse->address);       
        $_dc_cuahang=str_replace("</p>","",$_dc_cuahang);
        $ten_ncc=$supplier->company ? $supplier->company : $supplier->name;
        $trangthai=lang($inv->status);
        $trangthaithanhtoan=lang($inv->payment_status);     
        
        $_ghichu="";
        if ($inv->note!=""&&$this->sma->decode_html($inv->note)!="") {
            $_ghichu="<p> Ghi chú: ".$this->sma->decode_html($inv->note)."</p>";
        }

        $parse_data = array('Ma_Don_Hang' => $inv->reference_no,'Nha_Cung_Cap' => $ten_ncc,'site_link' => base_url(),'site_name' => $this->Settings->site_name,'Ten_Kho' => $warehouse->name,'Dia_Chi_Kho' =>$_dc_cuahang,'SDT_Kho' =>$warehouse->phone,'Dia_chi_ncc' => $supplier->address,'Dien_thoai_ncc' => $supplier->phone,'Email_ncc' => $supplier->email,'Ngay_Nhap' => $this->sma->hrld($inv->date),'Ngay_Xuat' => $this->sma->hrld($inv->date),'Tong_Thanh_Toan' =>$tongcong,'Nhan_Vien_Nhap' =>$created_by->first_name . ' ' . $created_by->last_name,'Nhan_Vien_Xuat' =>$created_by->first_name . ' ' . $created_by->last_name,'Ghi_Chu' =>$_ghichu,'Chua_Thanh_Toan' => $conlai,'Da_Thanh_Toan' => $dathanhtoan,'Giam_Gia_Tren_Hoa_Don' =>$tonggiam,'Tong_Tien_Hang' =>$tongcong,'Phi_Ship' =>$shipping,'No_Cu' =>$no_cu,'Tong_No' =>$tong_no,'Chiec_Khau' =>$chieckhau,'Trang_Thai' =>$trangthai,'Trang_Thai_Thanh_Toan' =>$trangthaithanhtoan,'Tong_Tien_Bang_Chu' =>$_tongcong_bang_chu_text,'Bang_Hoa_Don' =>$_tablhd,'Ma_Doi_Tac' => $doitac->code,'Ten_Doi_Tac' => $doitac->name,'Dia_Chi_Doi_Tac' => $doitac->diachi,'Email_Doi_Tac' => $doitac->email,'Dien_Thoai_Doi_Tac' => $doitac->dienthoai,'No_Dau_Ky_Doi_Tac' => $doitac->nodauky);    
        
        if (file_exists('./themes/' . $this->theme . '/views/print_khac/printnhap.html')) {          
            $sale_temp = file_get_contents('themes/' . $this->theme . '/views/print_khac/printnhap.html');            
        } else {             
            $sale_temp = file_get_contents('./themes/default/views/print_khac/printnhap.html');          
        }        
        if($inv->status=='returned'){
            $sale_temp = file_get_contents('./themes/default/views/print_khac/printnhapncc.html');     
        }
        $_get_active_pos=$this->settings_model->get_print_value($sale_temp);    
        $rs_ex_pos=explode(":",$_get_active_pos);
        
        $_sizein_page=isset($rs_ex_pos[0])&&$rs_ex_pos[0]!=":"?$rs_ex_pos[0]:"A5";
        $_chieuin_page=isset($rs_ex_pos[1])&&$rs_ex_pos[1]!=":"?$rs_ex_pos[1]:"Portrait";
                
        //replace value size print

        $kich_thuoc=$this->settings_model->define_size_in($_sizein_page,$_chieuin_page);  
        
        //replace value size print
        $sale_temp=$this->settings_model->define_print_replace($sale_temp);     
        

        $message = $this->parser->parse_string($sale_temp, $parse_data,false);
        $message=str_replace("\r","",$message);
        $message=str_replace("\n","",$message);
        $message=str_replace("\"","'",$message);
        //$message=json_encode($message);
        // tien hanh xu ly file pdf
        // xoa tat ca file in da co truoc do - 5 phut assets/print/
      

        //return array('size'=>$kich_thuoc,'noidung'=>$message);       
        return array('size'=>$kich_thuoc,'noidung'=>$message,'total'=>$tongsanpham);   
        
    }
    function SavePurchase_post()
    {        
        $rs=array('status'=>false,'mess'=>'INVALID authentication');
        if ($_SERVER['PHP_AUTH_USER'] == 'CIwiFtn8QMfIq2MNJqU8Q2lBOhKzpTTIdBBABC' && $_SERVER['PHP_AUTH_PW'] == md5('posbasic@2017#')) 
        {
            $rs=array('status'=>false,'mess'=>'ERROR');

            $_POST['token']=$this->security->get_csrf_hash();
            if ($_POST['token']!=''&&$_POST['device_token']!='') {
                //check login by token
                $rslogin=$this->checkloginByToken($_POST['token'],$_POST['device_token']);
                if ($rslogin)
                {
                    if(!$this->Owner && !$this->Admin) {
                        if ($this->data['GP']['purchase-add']!=1) {
                            $rs=array('status'=>false,'mess'=>'Không có quyền');  
                            $this->response($rs);
                            return;
                        }
                    }
                    if ($this->Admin||$this->Owner)
                    {
                        if (isset($_POST['warehouse'])&&$_POST['warehouse']!='') 
                        {
                            $warehouse_id =$_POST['warehouse'];
                            $objwh=$this->site->getWarehouseByID($warehouse_id);
                            if (empty($objwh)||$objwh==false) {
                                $rs=array('status'=>false,'mess'=>'ID kho hàng (warehouse='.$warehouse_id.') không hợp lệ');  
                                $this->response($rs);
                                return;
                            }
                        } else {
                            $warehouse_id = $this->Settings->default_warehouse;
                        }     

                    }else{
                        $warehouse_id = $this->session->userdata('warehouse_id');
                    }
                                       
                    
                    $payment_status=isset($_POST['payment_status'])?$_POST['payment_status']:NULL;

                    $supplier_id=isset($_POST['supplier_id'])?$_POST['supplier_id']:NULL;
                    $customer_details = $this->site->getCompanyByID($supplier_id);
                    if ($supplier_id>0&&$customer_details==false) 
                    {
                        $rs=array('status'=>false,'mess'=>'Không có nhà cung cấp');  
                        $this->response($rs);
                        return;
                    }
                   
                    $update_id = $this->input->post('update_id') ? $this->input->post('update_id') : NULL;
                                   
                    $date = date('Y-m-d H:i:s');
                    $doitac = (int)$this->input->post('doitac');
                    if ($doitac>0) {
                        $obj_doitac=$this->site->getDoitacByID($doitac);
                        if ($obj_doitac==false) {
                            $rs=array('status'=>false,'mess'=>'ID Đối tác giao hàng ('.$doitac.') không hợp lệ');  
                            $this->response($rs);
                            return;
                        }
                    }


                    $reference = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('po');

                    
                    $date = date('Y-m-d H:i:s');                    
                    $status = 'received';//received,ordered,returned,pending

                    $note = $this->input->post('note');
                    $shipping = $this->input->post('shipping') ? $this->input->post('shipping') : 0;
                    
                    $supplier_details = $this->site->getCompanyByID($supplier_id);
                    $supplier = $supplier_details->company != '-'  ? $supplier_details->company : $supplier_details->name;
                    
                    $payment_term = $this->input->post('payment_term');
                    $due_date = $payment_term ? date('Y-m-d', strtotime('+' . $payment_term . ' days', strtotime($date))) : null;

                    $total = 0;
                    $product_tax = 0;
                    $order_tax = 0;
                    $product_discount = 0;
                    $product_chieckhau = 0;
                    $order_discount = 0;
                    $percentage = '%';
                    $tong_chiec_khau=0;
                    
                    $i = isset($_POST['product_id']) ? sizeof($_POST['product_id']) : 0;


                    for ($r = 0; $r < $i; $r++) {

                        $item_code = $_POST['product_id'][$r];
                        
                        $real_unit_cost = $unit_cost = $item_net_cost = $this->sma->formatDecimal($_POST['product_cost'][$r]);
                        
                        $item_unit_quantity = $_POST['product_quantity'][$r];

                        $item_option = isset($_POST['product_option_id'][$r]) && $_POST['product_option_id'][$r] != 'false' ? $_POST['product_option_id'][$r] : null;
                        $item_tax_rate = isset($_POST['product_tax'][$r]) ? $_POST['product_tax'][$r] : null;
                        
                        $item_discount = isset($_POST['product_discount'][$r]) ? $_POST['product_discount'][$r] : null;

                        
                        $item_chieckhau=null;   
                        
                        $item_expiry = (isset($_POST['product_expiry'][$r]) && !empty($_POST['product_expiry'][$r])) ? $this->sma->fsd($_POST['product_expiry'][$r]) : null;
                        
                        $supplier_part_no = null;

                        $item_unit = $_POST['product_unit'][$r];

                        //$item_quantity = $_POST['product_base_quantity'][$r];
                        
                        $item_quantity = $item_unit_quantity;

                        if($item_option=='0'||$item_option==0){
                            $item_option=null;  
                        }
                        
                        if (isset($item_code) && isset($item_unit_quantity)) {
                            
                            $product_details = $this->site->getProductByID($item_code);
                            if ($item_expiry) {
                                $today = date('Y-m-d');
                                if ($item_expiry <= $today) {                                    
                                    $rs=array('status'=>false,'mess'=>lang('product_expiry_date_issue') . ' (' . $product_details->name . ')');  
                                    $this->response($rs);
                                    return;
                                }
                            }
                            $unit_cost = $real_unit_cost;
                            $pr_discount = 0;

                            if (isset($item_discount)) {
                                $discount = $item_discount;
                                $dpos = strpos($discount, $percentage);
                                if ($dpos !== false) {
                                    $pds = explode("%", $discount);
                                    $pr_discount = $this->sma->formatDecimal(((($this->sma->formatDecimal($unit_cost)) * (Float) ($pds[0])) / 100), 4);
                                } else {
                                    $pr_discount = $this->sma->formatDecimal($discount);
                                }
                            }
                            

                            //$unit_cost = $this->sma->formatDecimal($unit_cost - $pr_discount);
                            //$item_net_cost = $unit_cost;
                            $pr_item_discount = $this->sma->formatDecimal($pr_discount * $item_unit_quantity);
                            $product_discount += $pr_item_discount;
                            
                            $pr_chieckhau = 0;

                            if (isset($item_chieckhau)) {
                                $discount_v2 = $item_chieckhau;
                                $dpos = strpos($discount_v2, $percentage);
                                if ($dpos !== false) {
                                    $pds = explode("%", $discount_v2);
                                    $pr_chieckhau = $this->sma->formatDecimal(((($this->sma->formatDecimal($unit_cost-$pr_discount)) * (Float) ($pds[0])) / 100), 4);
                                } else {
                                    $pr_chieckhau = $this->sma->formatDecimal($discount_v2);
                                }
                            }
                            $pr_item_chieckhau = $this->sma->formatDecimal($pr_chieckhau * $item_unit_quantity);
                            $product_chieckhau += $pr_item_chieckhau;
                            
                            $unit_cost = $this->sma->formatDecimal($unit_cost - $pr_discount - $pr_chieckhau);
                            $item_net_cost = $this->sma->formatDecimal($unit_cost);
                            
                            $pr_tax = 0;
                            $pr_item_tax = 0;
                            $item_tax = 0;
                            $tax = "";

                            if (isset($item_tax_rate) && $item_tax_rate != 0) {
                                $pr_tax = $item_tax_rate;
                                $tax_details = $this->site->getTaxRateByID($pr_tax);
                                if ($tax_details->type == 1 && $tax_details->rate != 0) {

                                    if ($product_details && $product_details->tax_method == 1) {
                                        $item_tax = $this->sma->formatDecimal((($unit_cost) * $tax_details->rate) / 100, 4);
                                        $tax = $tax_details->rate . "%";
                                    } else {
                                        $item_tax = $this->sma->formatDecimal((($unit_cost) * $tax_details->rate) / (100 + $tax_details->rate), 4);
                                        $tax = $tax_details->rate . "%";
                                        $item_net_cost = $unit_cost - $item_tax;
                                    }

                                } elseif ($tax_details->type == 2) {

                                    if ($product_details && $product_details->tax_method == 1) {
                                        $item_tax = $this->sma->formatDecimal((($unit_cost) * $tax_details->rate) / 100, 4);
                                        $tax = $tax_details->rate . "%";
                                    } else {
                                        $item_tax = $this->sma->formatDecimal((($unit_cost) * $tax_details->rate) / (100 + $tax_details->rate), 4);
                                        $tax = $tax_details->rate . "%";
                                        $item_net_cost = $unit_cost - $item_tax;
                                    }

                                    $item_tax = $this->sma->formatDecimal($tax_details->rate);
                                    $tax = $tax_details->rate;

                                }
                                $pr_item_tax = $this->sma->formatDecimal($item_tax * $item_unit_quantity, 4);

                            }

                            $product_tax += $pr_item_tax;
                            $subtotal = (($item_net_cost * $item_unit_quantity) + $pr_item_tax);
                            $unit = $this->site->getUnitByID($item_unit);
                            $quantity_received=$item_quantity;
                            if ($status!='completed') {
                                $quantity_received=0;
                            }
                            $products[] = array(
                                'product_id' => $product_details->id,
                                'product_code' => $product_details->code,
                                'product_name' => $product_details->name,
                                'option_id' => $item_option,
                                'net_unit_cost' => $real_unit_cost,
                                'unit_cost' => $this->sma->formatDecimal($real_unit_cost + $item_tax),
                                'quantity' => $item_quantity,
                                'product_unit_id' => $item_unit,
                                'product_unit_code' => $unit->code,
                                'unit_quantity' => $item_unit_quantity,
                                'quantity_balance' => $item_quantity,
                                'quantity_received' => $quantity_received,
                                'warehouse_id' => $warehouse_id,
                                'item_tax' => $pr_item_tax,
                                'tax_rate_id' => $pr_tax,
                                'tax' => $tax,
                                'discount' => $item_discount,
                                'item_discount' => $pr_item_discount,
                                'chiec_khau' => $item_chieckhau,
                                'items_chiec_khau' => $pr_item_chieckhau,
                                'subtotal' => $this->sma->formatDecimal($subtotal),
                                'expiry' => $item_expiry,
                                'real_unit_cost' => $real_unit_cost,
                                'date' => date('Y-m-d', strtotime($date)),
                                'status' => $status,
                                'supplier_part_no' => $supplier_part_no,
                            );

                            $total += $this->sma->formatDecimal(($item_net_cost * $item_unit_quantity), 4);
                        }
                    }
                    if (empty($products)) {

                        $rs=array('status'=>false,'mess'=>lang("Không có danh sách sản phẩm"));  
                        $this->response($rs);
                        return;

                    } else {
                        krsort($products);
                    }

                    if ($this->input->post('discount')) {
                        $order_discount_id = $this->input->post('discount');
                        $opos = strpos($order_discount_id, $percentage);
                        if ($opos !== false) {
                            $ods = explode("%", $order_discount_id);
                            $order_discount = $this->sma->formatDecimal(((($total + $product_tax) * (Float) ($ods[0])) / 100), 4);

                        } else {
                            $order_discount = $this->sma->formatDecimal($order_discount_id);
                        }
                    } else {
                        $order_discount_id = null;
                    }
                    
                    if ($this->input->post('chiec_khau')) {
                        $chiec_khau_id = $this->input->post('chiec_khau');
                        $opos_ck = strpos($chiec_khau_id, $percentage);
                        if ($opos_ck !== false) {
                            $ods_ck = explode("%", $chiec_khau_id);
                            $tong_chiec_khau = $this->sma->formatDecimal((($order_discount * (Float) ($ods_ck[0])) / 100), 4);

                        } else {
                            $tong_chiec_khau = $this->sma->formatDecimal($chiec_khau_id);
                        }
                    } else {
                        $chiec_khau_id = null;
                    }
                    
                    $total_discount = $this->sma->formatDecimal($order_discount + $product_discount + $tong_chiec_khau);

                    if ($this->Settings->tax2 != 0) {
                        $order_tax_id = $this->input->post('order_tax');
                        if ($order_tax_details = $this->site->getTaxRateByID($order_tax_id)) {
                            if ($order_tax_details->type == 2) {
                                $order_tax = $this->sma->formatDecimal($order_tax_details->rate);
                            }
                            if ($order_tax_details->type == 1) {
                                $order_tax = $this->sma->formatDecimal(((($total + $product_tax - $order_discount) * $order_tax_details->rate) / 100), 4);
                            }
                        }
                    } else {
                        $order_tax_id = null;
                    }

                    $total_tax = $this->sma->formatDecimal(($product_tax + $order_tax), 4);
                    $grand_total = $this->sma->formatDecimal(($total + $total_tax + $this->sma->formatDecimal($shipping) - $order_discount - $tong_chiec_khau), 4);


                    $sotienthanhtoan=(float)$this->input->post('amount-paid');
                    $payment_status='due';
                    if ($sotienthanhtoan>0)
                    {
                        if ($sotienthanhtoan>=$grand_total) {
                            $sotienthanhtoan=$grand_total;
                            $payment_status='paid';
                        }else{
                            $payment_status='partial';
                        }
                    }

                    $data = array('reference_no' => $reference,
                        'date' => $date,
                        'supplier_id' => $supplier_id,
                        'supplier' => $supplier,
                        'warehouse_id' => $warehouse_id,
                        'doitac' => $doitac,
                        'note' => $note,
                        'total' => $total,
                        'product_discount' => $product_discount,
                        'order_discount_id' => $order_discount_id,
                        'order_discount' => $order_discount,
                        'chiec_khau_id' => $chiec_khau_id,
                        'chiec_khau' => $tong_chiec_khau,
                        'total_discount' => $total_discount,
                        'product_tax' => $product_tax,
                        'order_tax_id' => $order_tax_id,
                        'order_tax' => $order_tax,
                        'total_tax' => $total_tax,
                        'shipping' => $this->sma->formatDecimal($shipping),
                        'grand_total' => $grand_total,
                        'status' => $status,
                        'payment_status'=>$payment_status,
                        'created_by' => $this->session->userdata('user_id'),
                        'payment_term' => $payment_term,
                        'due_date' => $due_date,
                    );
                    $payment = array();
                               
                    if ((float)$this->input->post('amount-paid')>0)
                    {                                          
                        $payment = array(
                            'date' => $date,
                            'reference_no' =>$this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('ppay'),
                            'amount' => $this->sma->formatDecimal($sotienthanhtoan),
                            'paid_by' => $this->input->post('paid_by'),
                            'cheque_no' => $this->input->post('cheque_no'),
                            'cc_no' => $this->input->post('pcc_no'),
                            'cc_holder' => $this->input->post('pcc_holder'),
                            'cc_month' => $this->input->post('pcc_month'),
                            'cc_year' => $this->input->post('pcc_year'),
                            'cc_type' => $this->input->post('pcc_type'),
                            'created_by' => $this->session->userdata('user_id'),
                            'note' => $this->input->post('payment_note'),
                            'type' => 'sent',
                            'warehouse_id' => $warehouse_id,
                            'c_name' => $supplier_details->name,
                            'c_phone' => $supplier_details->phone,
                            'c_address' => $supplier_details->address,
                        );
                    }
                        
                   
                    $dlDetails=null;
                    if ((int)$doitac>0) {
                        //get customer address
                        $kh_obj=$this->site->getCompanyByID($supplier_id);
                        $dlDetails = array(
                        'date' => $date,
                        'ngaynhan' => $date,
                        'do_reference_no' =>$this->site->getReference('do'),
                        'sale_reference_no' => $reference,
                        'customer' => $supplier,
                        'address' => $kh_obj->address,
                        'phone' => $kh_obj->phone,
                        'status' => 'packing',
                        'delivered_by' => $doitac,
                        'shipping' => (float)str_replace(",","",$this->input->post('shipping')),
                        'received_by' => '',
                        'note' => '',
                        'created_by' => $this->session->userdata('user_id'),
                        );
                    }
                   
                    //$this->sma->print_arrays($data, $products,$payment);
                
                   
                    if ($sale = $this->purchases_model->addPurchaseApi($data, $products,$payment,$dlDetails)) {
                        $this->session->set_userdata('remove_posls', 1);                       
                       
                        $rs=array('status'=>true,'purchase_id'=>$sale['purchase_id'],'delivery_id'=>$sale['delivery_id']);  
                        $this->response($rs);
                        return;
                    }else{
                        $rs=array('status'=>false,'mess'=>'Lỗi khi lưu dữ liệu ');  
                        $this->response($rs);
                        return;  
                    }
               
                }else{
                    $rs=array('status'=>false,'mess'=>'Token không chính xác.');                    
                }
            }else{
                $rs=array('status'=>false,'mess'=>'Token không hợp lệ');
            }       
        }        
        
        $this->response($rs);
    }
    function UpdatePurchase_post()
    {        
        $rs=array('status'=>false,'mess'=>'INVALID authentication');
        if ($_SERVER['PHP_AUTH_USER'] == 'CIwiFtn8QMfIq2MNJqU8Q2lBOhKzpTTIdBBABC' && $_SERVER['PHP_AUTH_PW'] == md5('posbasic@2017#')) 
        {
            $rs=array('status'=>false,'mess'=>'ERROR');

            $_POST['token']=$this->security->get_csrf_hash();
            if ($_POST['token']!=''&&$_POST['device_token']!='') {
                //check login by token
                $rslogin=$this->checkloginByToken($_POST['token'],$_POST['device_token']);
                if ($rslogin)
                {
                    if(!$this->Owner && !$this->Admin) {
                        if ($this->data['GP']['purchase-add']!=1) {
                            $rs=array('status'=>false,'mess'=>'Không có quyền');  
                            $this->response($rs);
                            return;
                        }
                    }
                    if ($this->Admin||$this->Owner)
                    {
                        if (isset($_POST['warehouse'])&&$_POST['warehouse']!='') 
                        {
                            $warehouse_id =$_POST['warehouse'];
                            $objwh=$this->site->getWarehouseByID($warehouse_id);
                            if (empty($objwh)||$objwh==false) {
                                $rs=array('status'=>false,'mess'=>'ID kho hàng (warehouse='.$warehouse_id.') không hợp lệ');  
                                $this->response($rs);
                                return;
                            }
                        } else {
                            $warehouse_id = $this->Settings->default_warehouse;
                        }     

                    }else{
                        $warehouse_id = $this->session->userdata('warehouse_id');
                    }
                                       
                    
                    

                    $supplier_id=isset($_POST['supplier_id'])?$_POST['supplier_id']:NULL;
                    $customer_details = $this->site->getCompanyByID($supplier_id);
                    if ($supplier_id>0&&$customer_details==false) 
                    {
                        $rs=array('status'=>false,'mess'=>'Không có nhà cung cấp');  
                        $this->response($rs);
                        return;
                    }
                   
                    $update_id = $this->input->post('update_id') ? $this->input->post('update_id') : NULL;
                    
                    $inv = $this->purchases_model->getPurchaseByID($id);
                    if ($inv->status == 'returned' || $inv->return_id || $inv->return_purchase_ref) {
                        $this->session->set_flashdata('error', lang('Đơn nhập hàng có trả hàng, vui lòng hủy hóa đơn'));
                        redirect(isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : 'welcome');
                    }
                    if (!$this->session->userdata('edit_right')) {
                        $this->sma->view_rights($inv->created_by);
                    }

                    $date = date('Y-m-d H:i:s');
                    $doitac = (int)$this->input->post('doitac');
                    if ($doitac>0) {
                        $obj_doitac=$this->site->getDoitacByID($doitac);
                        if ($obj_doitac==false) {
                            $rs=array('status'=>false,'mess'=>'ID Đối tác giao hàng ('.$doitac.') không hợp lệ');  
                            $this->response($rs);
                            return;
                        }
                    }


                    $reference = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('po');

                    $payment_status=$inv->payment_status;
                    
                    $date = date('Y-m-d H:i:s');                    
                    $status = 'received';//received,ordered,returned,pending

                    $note = $this->input->post('note');
                    $shipping = $this->input->post('shipping') ? $this->input->post('shipping') : 0;
                    
                    $supplier_details = $this->site->getCompanyByID($supplier_id);
                    $supplier = $supplier_details->company != '-'  ? $supplier_details->company : $supplier_details->name;
                    
                    $payment_term = $this->input->post('payment_term');
                    $due_date = $payment_term ? date('Y-m-d', strtotime('+' . $payment_term . ' days', strtotime($date))) : null;

                    $total = 0;
                    $product_tax = 0;
                    $order_tax = 0;
                    $product_discount = 0;
                    $product_chieckhau = 0;
                    $order_discount = 0;
                    $percentage = '%';
                    $tong_chiec_khau=0;
                    
                    $i = isset($_POST['product_id']) ? sizeof($_POST['product_id']) : 0;


                    for ($r = 0; $r < $i; $r++) {

                        $item_code = $_POST['product_id'][$r];
                        
                        $real_unit_cost = $unit_cost = $item_net_cost = $this->sma->formatDecimal($_POST['product_cost'][$r]);
                        
                        $item_unit_quantity = $_POST['product_quantity'][$r];

                        $item_option = isset($_POST['product_option_id'][$r]) && $_POST['product_option_id'][$r] != 'false' ? $_POST['product_option_id'][$r] : null;
                        $item_tax_rate = isset($_POST['product_tax'][$r]) ? $_POST['product_tax'][$r] : null;
                        
                        $item_discount = isset($_POST['product_discount'][$r]) ? $_POST['product_discount'][$r] : null;

                        
                        $item_chieckhau=null;   
                        
                        $item_expiry = (isset($_POST['product_expiry'][$r]) && !empty($_POST['product_expiry'][$r])) ? $this->sma->fsd($_POST['product_expiry'][$r]) : null;
                        
                        $supplier_part_no = null;

                        $item_unit = $_POST['product_unit'][$r];

                        //$item_quantity = $_POST['product_base_quantity'][$r];
                        
                        $item_quantity = $item_unit_quantity;

                        if($item_option=='0'||$item_option==0){
                            $item_option=null;  
                        }
                        
                        if (isset($item_code) && isset($item_unit_quantity)) {
                            
                            $product_details = $this->site->getProductByID($item_code);
                            if ($item_expiry) {
                                $today = date('Y-m-d');
                                if ($item_expiry <= $today) {                                    
                                    $rs=array('status'=>false,'mess'=>lang('product_expiry_date_issue') . ' (' . $product_details->name . ')');  
                                    $this->response($rs);
                                    return;
                                }
                            }
                            $unit_cost = $real_unit_cost;
                            $pr_discount = 0;

                            if (isset($item_discount)) {
                                $discount = $item_discount;
                                $dpos = strpos($discount, $percentage);
                                if ($dpos !== false) {
                                    $pds = explode("%", $discount);
                                    $pr_discount = $this->sma->formatDecimal(((($this->sma->formatDecimal($unit_cost)) * (Float) ($pds[0])) / 100), 4);
                                } else {
                                    $pr_discount = $this->sma->formatDecimal($discount);
                                }
                            }
                            

                            //$unit_cost = $this->sma->formatDecimal($unit_cost - $pr_discount);
                            //$item_net_cost = $unit_cost;
                            $pr_item_discount = $this->sma->formatDecimal($pr_discount * $item_unit_quantity);
                            $product_discount += $pr_item_discount;
                            
                            $pr_chieckhau = 0;

                            if (isset($item_chieckhau)) {
                                $discount_v2 = $item_chieckhau;
                                $dpos = strpos($discount_v2, $percentage);
                                if ($dpos !== false) {
                                    $pds = explode("%", $discount_v2);
                                    $pr_chieckhau = $this->sma->formatDecimal(((($this->sma->formatDecimal($unit_cost-$pr_discount)) * (Float) ($pds[0])) / 100), 4);
                                } else {
                                    $pr_chieckhau = $this->sma->formatDecimal($discount_v2);
                                }
                            }
                            $pr_item_chieckhau = $this->sma->formatDecimal($pr_chieckhau * $item_unit_quantity);
                            $product_chieckhau += $pr_item_chieckhau;
                            
                            $unit_cost = $this->sma->formatDecimal($unit_cost - $pr_discount - $pr_chieckhau);
                            $item_net_cost = $this->sma->formatDecimal($unit_cost);
                            
                            $pr_tax = 0;
                            $pr_item_tax = 0;
                            $item_tax = 0;
                            $tax = "";

                            if (isset($item_tax_rate) && $item_tax_rate != 0) {
                                $pr_tax = $item_tax_rate;
                                $tax_details = $this->site->getTaxRateByID($pr_tax);
                                if ($tax_details->type == 1 && $tax_details->rate != 0) {

                                    if ($product_details && $product_details->tax_method == 1) {
                                        $item_tax = $this->sma->formatDecimal((($unit_cost) * $tax_details->rate) / 100, 4);
                                        $tax = $tax_details->rate . "%";
                                    } else {
                                        $item_tax = $this->sma->formatDecimal((($unit_cost) * $tax_details->rate) / (100 + $tax_details->rate), 4);
                                        $tax = $tax_details->rate . "%";
                                        $item_net_cost = $unit_cost - $item_tax;
                                    }

                                } elseif ($tax_details->type == 2) {

                                    if ($product_details && $product_details->tax_method == 1) {
                                        $item_tax = $this->sma->formatDecimal((($unit_cost) * $tax_details->rate) / 100, 4);
                                        $tax = $tax_details->rate . "%";
                                    } else {
                                        $item_tax = $this->sma->formatDecimal((($unit_cost) * $tax_details->rate) / (100 + $tax_details->rate), 4);
                                        $tax = $tax_details->rate . "%";
                                        $item_net_cost = $unit_cost - $item_tax;
                                    }

                                    $item_tax = $this->sma->formatDecimal($tax_details->rate);
                                    $tax = $tax_details->rate;

                                }
                                $pr_item_tax = $this->sma->formatDecimal($item_tax * $item_unit_quantity, 4);

                            }

                            $product_tax += $pr_item_tax;
                            $subtotal = (($item_net_cost * $item_unit_quantity) + $pr_item_tax);
                            $unit = $this->site->getUnitByID($item_unit);
                            $quantity_received=$item_quantity;
                            if ($status!='completed') {
                                $quantity_received=0;
                            }
                            $products[] = array(
                                'product_id' => $product_details->id,
                                'product_code' => $product_details->code,
                                'product_name' => $product_details->name,
                                'option_id' => $item_option,
                                'net_unit_cost' => $real_unit_cost,
                                'unit_cost' => $this->sma->formatDecimal($real_unit_cost + $item_tax),
                                'quantity' => $item_quantity,
                                'product_unit_id' => $item_unit,
                                'product_unit_code' => $unit->code,
                                'unit_quantity' => $item_unit_quantity,
                                'quantity_balance' => $item_quantity,
                                'quantity_received' => $quantity_received,
                                'warehouse_id' => $warehouse_id,
                                'item_tax' => $pr_item_tax,
                                'tax_rate_id' => $pr_tax,
                                'tax' => $tax,
                                'discount' => $item_discount,
                                'item_discount' => $pr_item_discount,
                                'chiec_khau' => $item_chieckhau,
                                'items_chiec_khau' => $pr_item_chieckhau,
                                'subtotal' => $this->sma->formatDecimal($subtotal),
                                'expiry' => $item_expiry,
                                'real_unit_cost' => $real_unit_cost,
                                'date' => date('Y-m-d', strtotime($date)),
                                'status' => $status,
                                'supplier_part_no' => $supplier_part_no,
                            );

                            $total += $this->sma->formatDecimal(($item_net_cost * $item_unit_quantity), 4);
                        }
                    }
                    if (empty($products)) {

                        $rs=array('status'=>false,'mess'=>lang("Không có danh sách sản phẩm"));  
                        $this->response($rs);
                        return;

                    } else {
                        krsort($products);
                    }

                    if ($this->input->post('discount')) {
                        $order_discount_id = $this->input->post('discount');
                        $opos = strpos($order_discount_id, $percentage);
                        if ($opos !== false) {
                            $ods = explode("%", $order_discount_id);
                            $order_discount = $this->sma->formatDecimal(((($total + $product_tax) * (Float) ($ods[0])) / 100), 4);

                        } else {
                            $order_discount = $this->sma->formatDecimal($order_discount_id);
                        }
                    } else {
                        $order_discount_id = null;
                    }
                    
                    if ($this->input->post('chiec_khau')) {
                        $chiec_khau_id = $this->input->post('chiec_khau');
                        $opos_ck = strpos($chiec_khau_id, $percentage);
                        if ($opos_ck !== false) {
                            $ods_ck = explode("%", $chiec_khau_id);
                            $tong_chiec_khau = $this->sma->formatDecimal((($order_discount * (Float) ($ods_ck[0])) / 100), 4);

                        } else {
                            $tong_chiec_khau = $this->sma->formatDecimal($chiec_khau_id);
                        }
                    } else {
                        $chiec_khau_id = null;
                    }
                    
                    $total_discount = $this->sma->formatDecimal($order_discount + $product_discount + $tong_chiec_khau);

                    if ($this->Settings->tax2 != 0) {
                        $order_tax_id = $this->input->post('order_tax');
                        if ($order_tax_details = $this->site->getTaxRateByID($order_tax_id)) {
                            if ($order_tax_details->type == 2) {
                                $order_tax = $this->sma->formatDecimal($order_tax_details->rate);
                            }
                            if ($order_tax_details->type == 1) {
                                $order_tax = $this->sma->formatDecimal(((($total + $product_tax - $order_discount) * $order_tax_details->rate) / 100), 4);
                            }
                        }
                    } else {
                        $order_tax_id = null;
                    }

                    $total_tax = $this->sma->formatDecimal(($product_tax + $order_tax), 4);
                    $grand_total = $this->sma->formatDecimal(($total + $total_tax + $this->sma->formatDecimal($shipping) - $order_discount - $tong_chiec_khau), 4);

                    $sotienthanhtoan=(float)$this->input->post('amount-paid');
                    
                    if ($sotienthanhtoan>0)
                    {
                        if ($sotienthanhtoan>=$grand_total) {
                            $sotienthanhtoan=$grand_total;
                            $payment_status='paid';
                        }else{
                            $payment_status='partial';
                        }
                    }

                    $data = array('reference_no' => $reference,
                        'date' => $date,
                        'supplier_id' => $supplier_id,
                        'supplier' => $supplier,
                        'warehouse_id' => $warehouse_id,
                        'doitac' => $doitac,
                        'note' => $note,
                        'total' => $total,
                        'product_discount' => $product_discount,
                        'order_discount_id' => $order_discount_id,
                        'order_discount' => $order_discount,
                        'chiec_khau_id' => $chiec_khau_id,
                        'chiec_khau' => $tong_chiec_khau,
                        'total_discount' => $total_discount,
                        'product_tax' => $product_tax,
                        'order_tax_id' => $order_tax_id,
                        'order_tax' => $order_tax,
                        'total_tax' => $total_tax,
                        'shipping' => $this->sma->formatDecimal($shipping),
                        'grand_total' => $grand_total,
                        'status' => $status,
                        'payment_status'=>$payment_status,
                        'created_by' => $this->session->userdata('user_id'),
                        'payment_term' => $payment_term,
                        'due_date' => $due_date,
                    );
                    $payment = array();
                           
                    if ($sotienthanhtoan>0)
                    {                                          
                        $payment = array(
                            'date' => $date,
                            'purchase_id' => $this->input->post('update_id'),
                            'reference_no' => $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('ppay'),
                            'amount' => $this->sma->formatDecimal($sotienthanhtoan),
                            'paid_by' => $this->input->post('paid_by'),
                            'cheque_no' => $this->input->post('cheque_no'),
                            'cc_no' => $this->input->post('pcc_no'),
                            'cc_holder' => $this->input->post('pcc_holder'),
                            'cc_month' => $this->input->post('pcc_month'),
                            'cc_year' => $this->input->post('pcc_year'),
                            'cc_type' => $this->input->post('pcc_type'),
                            'created_by' => $this->session->userdata('user_id'),
                            'note' => $this->input->post('payment_note'),
                            'type' => 'sent',
                            'warehouse_id' => $warehouse_id,
                            'c_name' => $supplier_details->name,
                            'c_phone' => $supplier_details->phone,
                            'c_address' => $supplier_details->address,
                        );
                    }                        
                    else {
                        $payment = array();
                    }
                    $dlDetails=null;
                    if ((int)$doitac>0) {
                        //get customer address
                        $kh_obj=$this->site->getCompanyByID($supplier_id);
                        $dlDetails = array(
                        'date' => $date,
                        'ngaynhan' => $date,
                        'do_reference_no' =>$this->site->getReference('do'),
                        'sale_reference_no' => $reference,
                        'customer' => $supplier,
                        'address' => $kh_obj->address,
                        'phone' => $kh_obj->phone,
                        'status' => 'packing',
                        'delivered_by' => $doitac,
                        'shipping' => (float)str_replace(",","",$this->input->post('shipping')),
                        'received_by' => '',
                        'note' => '',
                        'created_by' => $this->session->userdata('user_id'),
                        );
                    }
                   
                    //$this->sma->print_arrays($data, $products,$payment);
                    $update_obj= $this->purchases_model->getPurchaseByID($update_id);
                    if(!$this->Owner && !$this->Admin) {
                        if (!empty($update_obj)) {                            
                            if ($update_obj->created_by!=$this->session->userdata('user_id')) {
                                $rs=array('status'=>false,'mess'=>'Không có quyền sửa đơn hàng này');  
                                $this->response($rs);
                                return;
                            }                            
                        }                                    
                    }
                    
                    if ($update_obj==false)
                    {
                        $rs=array('status'=>false,'mess'=>'Không tìm thấy hóa đơn '.$update_id);  
                        $this->response($rs);
                        return;           
                    }else if ($this->purchases_model->updatePurchase($update_id, $data, $products,$dlDetails)) {

                        //tien hanh xoa thanh toan truoc do
                        $this->purchases_model->removePaymentByAPI($update_id);

                        if (!empty($payment))
                        {
                            if ($update_obj->payment_status=='paid') {
                                $rs=array('status'=>true,'mess'=>'Cập nhật hóa đơn thành công, Hóa đơn đã được thanh toán');  
                                $this->response($rs);
                                return;   
                            }else{
                                $conno=$update_obj->grand_total;
                                if ($payment['amount']>$conno) 
                                {
                                    $payment['amount']=$conno;
                                }
                                //tien hanh thanh toan 
                                $this->purchases_model->addPayment($payment);

                                $rs=array('status'=>true,'mess'=>'Cập nhật hóa đơn và thanh toán thành công');  
                                $this->response($rs);
                                return;   
                            }
                        }
                        $this->session->set_userdata('remove_posls', 1);

                        $rs=array('status'=>true,'mess'=>'Cập nhật hóa đơn thành công');  
                        $this->response($rs);
                        return;
                    }else{
                        $rs=array('status'=>false,'mess'=>'Lỗi khi lưu dữ liệu ');  
                        $this->response($rs);
                        return;  
                    }
               
                }else{
                    $rs=array('status'=>false,'mess'=>'Token không chính xác.');                    
                }
            }else{
                $rs=array('status'=>false,'mess'=>'Token không hợp lệ');
            }       
        }        
        
        $this->response($rs);
    }
    public function suppliers_post()
    {
        $rs=array('status'=>false,'mess'=>'INVALID authentication');
        if ($_SERVER['PHP_AUTH_USER'] == 'CIwiFtn8QMfIq2MNJqU8Q2lBOhKzpTTIdBBABC' && $_SERVER['PHP_AUTH_PW'] == md5('posbasic@2017#')) 
        {
            $rs=array('status'=>false,'mess'=>'ERROR');
            $_POST['token']=$this->security->get_csrf_hash();
            if ($_POST['token']!=''&&$_POST['device_token']!='') {
                //check login by token
                $rslogin=$this->checkloginByToken($_POST['token'],$_POST['device_token']);
                if ($rslogin)
                {
                    
                    $page=isset($_POST['page'])?$_POST['page']:1;
                    $per_page=isset($_POST['per_page'])?$_POST['per_page']:10;
                    $offset= ($page - 1) * $per_page;

                    if ($this->Admin||$this->Owner)
                    {
                        if (isset($_POST['warehouse_id'])&&$_POST['warehouse_id']!='') {
                            $warehouse_id =$_POST['warehouse_id'];
                        } else {
                            $warehouse_id = $this->Settings->default_warehouse;
                        }     

                    }else{
                        $warehouse_id = $this->session->userdata('warehouse_id');
                    } 


                    $this->fillter_supplier();
                    $this->db->from("companies");
                    $total=$this->db->count_all_results();

                    $this->db->select("{$this->db->dbprefix('companies')}.*,(SELECT COALESCE(sum(grand_total), 0) FROM {$this->db->dbprefix('sales')} WHERE customer_id={$this->db->dbprefix('companies')}.id AND sale_status!='returned') + nobandau - (SELECT COALESCE(sum(paid), 0) FROM {$this->db->dbprefix('sales')} WHERE customer_id={$this->db->dbprefix('companies')}.id AND sale_status!='returned') - (select COALESCE(sum(amount), 0) from scodeweb_payments WHERE id_ncc_id_kh={$this->db->dbprefix('companies')}.id and type='received' AND amount>0) as duno");
                    
                    $this->fillter_supplier();
                    $this->db->limit($per_page, $offset);
                    $this->db->order_by("name", "asc");
                    $query = $this->db->get("companies");

                    if ($query->num_rows() > 0) {
                        foreach ($query->result() as $row) {
                            $data[] = $row;
                        }
                    }
                    $rs=array('status'=>true,'mess'=>'Thành công.','page'=>$page,'per_page'=>$per_page,'total'=>$total,'list'=>$data); 

                }else{ 
                    $rs=array('status'=>false,'mess'=>'Token không chính xác.');                    
                }
            }else{
                $rs=array('status'=>false,'mess'=>'Token không hợp lệ');
            }       
        }
        $this->response($rs);
    }
    public function fillter_supplier()
    {

        $this->db->where('group_name', 'supplier');
        if ($_POST['keywords']) {
            $this->db->where("(" . $this->db->dbprefix('companies') . ".name LIKE '%" . $_POST['keywords'] . "%' OR phone LIKE '%" . $_POST['keywords'] . "%')");
        }
        if (isset($_POST['fillter'])&&$_POST['fillter']=='TRUE') {
            $this->db->where("(SELECT COALESCE(sum(grand_total), 0) FROM {$this->db->dbprefix('sales')} WHERE customer_id={$this->db->dbprefix('companies')}.id AND sale_status!='returned') + nobandau - (SELECT COALESCE(sum(paid), 0) FROM {$this->db->dbprefix('sales')} WHERE customer_id={$this->db->dbprefix('companies')}.id AND sale_status!='returned') - (select COALESCE(sum(amount), 0) from scodeweb_payments WHERE id_ncc_id_kh={$this->db->dbprefix('companies')}.id and type='received' AND amount>0) >0",NULL,FALSE);            
        }
    }
    function PrintBarcodeProduct_post()
    {
        $rs=array('status'=>false,'mess'=>'INVALID authentication');
        if ($_SERVER['PHP_AUTH_USER'] == 'CIwiFtn8QMfIq2MNJqU8Q2lBOhKzpTTIdBBABC' && $_SERVER['PHP_AUTH_PW'] == md5('posbasic@2017#')) 
        {
            $rs=array('status'=>false,'mess'=>'ERROR');
            $_POST['token']=$this->security->get_csrf_hash();
            if ($_POST['token']!=''&&$_POST['device_token']!='') {
                //check login by token
                $rslogin=$this->checkloginByToken($_POST['token'],$_POST['device_token']);
                if ($rslogin)
                {                                     
                    if (isset($_POST['product_id'])) 
                    {
                        $product_id=$_POST['product_id'];
                        $quantity=(int)$_POST['quantity'];

                        $product=$this->site->getProductByID($product_id);

                        if ($product!=false) {                           
                                                        
                            $checkrs=$this->print_barcodes_Byid($product_id,$quantity);


                            if ($checkrs['html']!='') {
                                $check=str_replace("\r","",$checkrs['html']);
                                $check=str_replace("\n","",$check);
                                $check=str_replace("\"","'",$check);


                                $rs=array('status'=>true,'mess'=>'Thành công.','item'=>$checkrs); 
                            }else{
                                $rs=array('status'=>false,'mess'=>'Lỗi in barcode sản phẩm.','item'=>null);     
                            }                            
                        } else {
                           $rs=array('status'=>false,'mess'=>'Không tìm thấy sản phẩm.','item'=>null); 
                        }
                    }else{
                        $rs=array('status'=>false,'mess'=>'Không tìm thấy sản phẩm.','item'=>null); 
                    }
                  
                    
                }else{
                    $rs=array('status'=>false,'mess'=>'Token không chính xác.');                    
                }
            }else{
                $rs=array('status'=>false,'mess'=>'Token không hợp lệ');
            }       
        }
        $this->response($rs);
    }
    function print_barcodes_Byid($product_id = NULL,$quantity=1)
    {
        $print_tem_json=json_decode($this->Settings->print_tem);
                            
        $style=000;
        $cf_width='';
        $cf_height='';
        $cf_orientation='';
        $p_site_name= false;
        $p_product_name= false;
        $p_price= false;
        $p_currencies= false;
        $p_unit= false;
        $p_category= false;
        $p_variants= false;
        $p_product_image= false;
        $p_check_promo= false;
        if (isset($print_tem_json)) {
            $style=$print_tem_json->style;
            $cf_width=$print_tem_json->width;
            $cf_height=$print_tem_json->height;
            $cf_orientation=$print_tem_json->orientation;

            $p_site_name= $print_tem_json->site_name==1?true:false;
            $p_product_name= $print_tem_json->product_name==1?true:false;
            $p_price= $print_tem_json->price==1?true:false;
            $p_currencies= $print_tem_json->currencies==1?true:false;
            $p_unit= $print_tem_json->unit==1?true:false;
            $p_category= $print_tem_json->category==1?true:false;
            $p_variants= $print_tem_json->variants==1?true:false;
            $p_product_image= $print_tem_json->image==1?true:false;
            $p_check_promo= $print_tem_json->check_promo==1?true:false;

        }


        $bci_size = ($style == 10 || $style == 12 ? 50 : ($style == 14 || $style == 18 ? 30 : 20));
        if ($style==200) {
            $bci_size  = 35;
        }
        else if ($style==300) {
            $bci_size  = 35;
        }else if ($style==100) {
            $bci_size  = 42;
        }else if ($style==000) {
            $bci_size  = 65;
        }
        $currencies = $this->site->getAllCurrencies();       
        $product = $this->products_model->getProductWithCategory($product_id);
       
        $product->price = $p_check_promo? ($product->promotion ? $product->promo_price : $product->price) : $product->price;
        
        $barcodes=null;

        if ($variants = $this->products_model->getProductOptions($product_id)) {
            foreach ($variants as $option) {
                if ($p_variants!='') {
                    $barcodes[] = array(
                        'site' => $p_site_name ? $this->Settings->site_name : FALSE,
                        'name' => $p_product_name ? $product->name.' - '.$option->name : FALSE,
                        'barcode' => $this->product_barcode($product->code . $this->Settings->barcode_separator . $option->id, 'code128', $bci_size),
                        'price' => $p_price ?  $this->sma->formatMoney($option->price != 0 ? $option->price : $product->price) : FALSE,
                        'unit' => $p_unit ? $this->site->getUnitNameByID($product->unit) : FALSE,
                        'category' => $p_category ? $product->category : FALSE,
                        'currencies' => $p_currencies,
                        'variants' => $p_variants ? $variants : FALSE,
                        'quantity' => $quantity
                        );
                }
            }
        } else {
            $barcodes[] = array(
                'site' => $p_site_name ? $this->Settings->site_name : FALSE,
                        'name' => $p_product_name ? $product->name : FALSE,
                        'barcode' => $this->product_barcode($product->code, 'code128', $bci_size),
                        'price' => $p_price ?  $this->sma->formatMoney($product->price) : FALSE,
                        'unit' => $p_unit ? $this->site->getUnitNameByID($product->unit) : FALSE,
                        'category' => $p_category ? $product->category : FALSE,
                        'currencies' => $p_currencies,
                        'variants' => FALSE,
                        'quantity' => $quantity
                );
        }

        $html='';
        if (!empty($barcodes)) {

            $c = 1;
            $tem="";
            $cao=0;
            $cao2=0;
            $rong2=0;
            if ($style == 12 || $style == 18 || $style == 24 || $style == 40) {
                $html.= '<div>';
            } elseif ($style == 000) {
                foreach ($barcodes as $item) {
                    $cao+=$item['quantity'];
                    
                }
                if($item['variants']) {
                    $cao+=$cao*count($item['variants']);
                }
                $tem="style='height:".($cao*40)."mm;width:58mm'";
                $html.= '<div '.$tem.'> ';
                $cao2=$cao*40;
                $rong2=58;
            }elseif ($style == 100) {
                foreach ($barcodes as $item) {
                    $cao+=$item['quantity'];
                    
                }
                if($item['variants']) {
                    $cao+=$cao*count($item['variants']);
                }
                $tem="style='height:".($cao*30)."mm;width:50mm'";
                $html.= '<div'.$tem.'> ';

                $cao2=$cao*30;
                $rong2=50;

            }elseif ($style == 200) {
                foreach ($barcodes as $item) {
                    $cao+=$item['quantity']*2;                                      
                }
                if($item['variants']) {
                    $cao+=$cao*count($item['variants']);
                }
                $nguyen=$cao/2;
                $caochk=$cao%2;
                if ($caochk!=0) {
                    $cao=$nguyen+1;
                }else{
                    $cao=$nguyen;
                }

                $tem="style='height:".($cao*22)."mm;width:72mm'";
                $html.= '<div'.$tem.'> ';

                $cao2=$cao*22;
                $rong2=72;

            }elseif ($style == 300) {
                foreach ($barcodes as $item) {
                    $cao+=$item['quantity']*3;                                      
                }
                if($item['variants']) {
                    $cao+=$cao*count($item['variants']);
                }

                $nguyen=$cao/3;
                $caochk=$cao%3;
                if ($caochk!=0) {
                    $cao=$nguyen+1;
                }else{
                    $cao=$nguyen;
                }
                $tem="style='height:".($cao*22)."mm;width:110mm'";
                $html.= '<div'.$tem.'> ';

                $cao2=$cao*22;
                $rong2=110;

            }elseif ($style != 50) {
                
                $cao2=$item['quantity']*$cf_height;
                $rong2=$cf_width;

                $html.= '<div>';                                  

            }
            foreach ($barcodes as $item) {
                $tem_item="style='height:".$cao."mm;width:56mm'";

                if ($style == 000) {
                    $tem_item="style='height:38mm;width:56mm;margin:0px'";
                }else if ($style == 100) {
                    $tem_item="style='height:28mm;width:48mm;margin:0px'";
                }else if ($style == 200) {
                    $item['quantity']=$item['quantity']*2;
                    $tem_item="style='height:22mm;width:34mm;margin:0px;float:left'";
                }
                else if ($style == 300) {
                    $item['quantity']=$item['quantity']*3;
                    $tem_item="style='height:20mm;width:33mm;margin:1mm;float:left'";
                }else if($style==50){
                    $cao=$cf_height;
                    $tem_item=$cf_width!='' && $cf_height!='' ?
                        'style="width:'.$cf_width.'mm;height:'.$cf_height.'mm;border:0;"' : '';
                }else{
                    $item['quantity']=$item['quantity'];
                    $tem_item="style='height:".$cao."mm;width:72mm'";
                }

                for ($r = 1; $r <= $item['quantity']; $r++) {
                    
                    $html.= '<div '. $tem_item.'>';
                    if ($style == 50) {
                        if ($cf_orientation) {
                            $ty = (($cf_height/$cf_width)*100).'%';
                            $landscape = '
                            -webkit-transform-origin: 0 0;
                            -moz-transform-origin:    0 0;
                            -ms-transform-origin:     0 0;
                            transform-origin:         0 0;
                            -webkit-transform: translateY('.$ty.') rotate(-90deg);
                            -moz-transform:    translateY('.$ty.') rotate(-90deg);
                            -ms-transform:     translateY('.$ty.') rotate(-90deg);
                            transform:         translateY('.$ty.') rotate(-90deg);
                            ';
                            $html.= '<div style="width:'.$cf_height.'mm;height:'.$cf_width.'mm;border: 1px dotted #CCC;'.$landscape.'">';
                        } else {
                            $html.= '<div style="width:'.$cf_width.'mm;height:'.$cf_height.'mm;border: 1px dotted #CCC;padding-top:0.025in;">';
                        }
                    }
                    
                    $style_span='style="float:left;text-align:center;margin:0 auto; font-size:12px;width:100%"';
                    
                   
                    if($item['site']) {
                        $html.= '<span '.$style_span.'>'.$item['site'].'</span>';
                    }
                    if($item['name'])
                    {
                        if ($style == 200) {
                            $style_span1='style="float:left;text-align:center;margin:0 auto; font-size:12px;width:100%;height:3mm;line-height: 13px;max-height:3mm;overflow: hidden;white-space:normal;"';
                        }   
                        else if ($style == 300) {
                            $style_span1='style="float:left;text-align:center;margin:0 auto; font-size:12px;width:100%;height:3mm;line-height: 13px;max-height:3mm;overflow: hidden;white-space:normal;"';
                        } 
                        else if ($style == 100) {
                            $style_span1='style="float:left;text-align:center;margin:0 auto; font-size:13px;width:100%;height:8mm;line-height: 15px;max-height:8mm;overflow: hidden;white-space:normal;"';
                        }else if ($style == 000) {
                            $style_span1='style="float:left;text-align:center;margin:0 auto; font-size:14px;width:100%;height:12mm;line-height: 15px;max-height:12mm;overflow: hidden;white-space:normal;"';
                        }else{
                            $style_span1=$style_span;
                        }                                       
                        $html.= '<span '.$style_span1.'>'.$item['name'].'</span>';
                    }
                    if($item['price']) {
                        $html.= '<span '.$style_span.'>'.lang('price').' ';
                        if($item['currencies']) {
                            $rsc=array();
                            foreach ($currencies as $currency) {
                                $rsc[]=$item['price']." ".$currency->name;
                            }
                            $html.= implode(",", $rsc);
                        } else {
                            $html.= $item['price'];
                        }
                        $html.= '</span> ';
                    }
                    if($item['unit']) {
                        $html.= '<span '.$style_span.'>'.lang('unit').': '.$item['unit'].'</span>, ';
                    }
                    if($item['category']) {
                        $html.= '<span '.$style_span.'>'.lang('category').': '.$item['category'].'</span> ';
                    }
                    if($item['variants']) {
                        $html.= '<span '.$style_span.'>'.lang('variants').': ';
                        foreach ($item['variants'] as $variant) {
                            $html.= $variant->name.', ';
                        }
                        $html.= '</span> ';
                    }
                    $html.= '<span '.$style_span.' >'.$item['barcode'].'</span>';
                    if ($style == 50) {
                        $html.= '</div>';
                    }
                    $html.= '</div>';
                    if ($style == 40) {
                        if ($c % 40 == 0) {
                            $html.= '</div><div></div><div>';
                        }
                    } elseif ($style == 30) {
                        if ($c % 30 == 0) {
                            $html.= '</div><div></div><div>';
                        }
                    } elseif ($style == 24) {
                        if ($c % 24 == 0) {
                            $html.= '</div><div></div><div>';
                        }
                    } elseif ($style == 20) {
                        if ($c % 20 == 0) {
                            $html.= '</div><div></div><div>';
                        }
                    } elseif ($style == 18) {
                        if ($c % 18 == 0) {
                            $html.= '</div><div></div><div>';
                        }
                    } elseif ($style == 14) {
                        if ($c % 14 == 0) {
                            $html.= '</div><div></div><div>';
                        }
                    } elseif ($style == 12) {
                        if ($c % 12 == 0) {
                            $html.= '</div><div></div><div>';
                        }
                    } elseif ($style == 10) {
                        if ($c % 10 == 0) {
                            $html.= '</div><div></div><div>';
                        }
                    }
                    $c++;
                }
            }
            if ($style != 50) {
                $html.= '</div>';
            }
        
        }
        $message=str_replace("\r","",$html);
        $message=str_replace("\n","",$message);
        $message=str_replace("\"","'",$message);


        return ['height'=>$cao2.'mm','width'=>$rong2.'mm','html'=>$message];
    }
    function product_barcode($product_code = NULL, $bcs = 'code128', $height = 60)
    {       

        // if ($this->Settings->barcode_img) {
        
            return "<img src='" . site_url('auth/gen_barcode/' . $product_code . '/' . $bcs . '/' . $height) . "' alt='{$product_code}' />";

           // return "<img src='data:image/png;base64,".base64_encode(file_get_contents(site_url('auth/gen_barcode/' . $product_code . '/' . $bcs . '/' . $height)))."'/>";
            

        // } else {
        //     return $this->gen_barcode($product_code, $bcs, $height);
        // }
    }

    
    function gen_barcode($product_code = NULL, $bcs = 'code128', $height = 60, $text = 1)
    {
        $drawText = ($text != 1) ? FALSE : TRUE;
        $this->load->library('zend');
        $this->zend->load('Zend/Barcode');
        $barcodeOptions = array('text' => $product_code, 'barHeight' => $height, 'drawText' => $drawText, 'factor' => 1.0);
        if ($this->Settings->barcode_img) { 
            $rendererOptions = array('imageType' => 'jpg', 'horizontalPosition' => 'center', 'verticalPosition' => 'middle');
            $imageResource = Zend_Barcode::render($bcs, 'image', $barcodeOptions, $rendererOptions);
            return $imageResource;
        } else {
            $rendererOptions = array('renderer' => 'svg', 'horizontalPosition' => 'center', 'verticalPosition' => 'middle');
            $imageResource = Zend_Barcode::render($bcs, 'svg', $barcodeOptions, $rendererOptions);
            header("Content-Type: image/svg+xml");
            echo $imageResource;
        }
    }
     function SaveTonKho_post()
    {
        $rs=array('status'=>false,'mess'=>'INVALID authentication');
        if ($_SERVER['PHP_AUTH_USER'] == 'CIwiFtn8QMfIq2MNJqU8Q2lBOhKzpTTIdBBABC' && $_SERVER['PHP_AUTH_PW'] == md5('posbasic@2017#')) 
        {
            $rs=array('status'=>false,'mess'=>'ERROR');
            $_POST['token']=$this->security->get_csrf_hash();
            if ($_POST['token']!=''&&$_POST['device_token']!='') {
                //check login by token
                $rslogin=$this->checkloginByToken($_POST['token'],$_POST['device_token']);
                if ($rslogin)
                {
                    // if(!$this->Owner && !$this->Admin) {
                    //     if ($this->data['GP']['purchase-delete']!=1) {
                    //         $rs=array('status'=>false,'mess'=>'Không có quyền');  
                    //         $this->response($rs);
                    //         return;
                    //     }
                    // }                    
                    if (isset($_POST['product_id'])) 
                    {
                        $product_id=$_POST['product_id'];
                        $option_id=$_POST['option_id']?$_POST['option_id']:NULL;
                        $quantity=$_POST['quantity']?$_POST['quantity']:0;

                        if ($this->Admin||$this->Owner)
                        {
                            if (isset($_POST['warehouse_id'])&&$_POST['warehouse_id']!='') {
                                $warehouse_id =$_POST['warehouse_id'];
                                $objwh=$this->site->getWarehouseByID($warehouse_id);
                                if (empty($objwh)||$objwh==false) {
                                    $rs=array('status'=>false,'mess'=>'ID kho hàng (warehouse='.$warehouse_id.') không hợp lệ');  
                                    $this->response($rs);
                                    return;
                                }
                            } else {
                                $warehouse_id = $this->Settings->default_warehouse;
                            }     

                        }else{
                            $warehouse_id = $this->session->userdata('warehouse_id');
                        }                        
                        $product = $this->getProductByIdApi($product_id,$customer_id=0,$option_id,$warehouse_id,NULL);
                        $soluong=0;
                      
                        if ($product) {  

                            $type='subtraction';
                            
                            $note='Điều chỉnh tồn kho từ ['.(float)$product['tonkhohientai'].'] thành ['.$quantity.'] lúc '.date('d/m/Y H:i').' bởi ['.$this->session->userdata('fullname').']';
                            $data = array(
                            'date' => date('Y-m-d H:s:i'),
                            'reference_no' => $this->site->getReference('qa'),
                            'warehouse_id' => $warehouse_id,
                            'note' => $note,
                            'created_by' => $this->session->userdata('user_id'),
                            'count_id' =>NULL,
                            );                          

                            if ($quantity>$product['tonkhohientai']) {
                                //tien hanh them dieu chinh
                                $soluong=$quantity-$product['tonkhohientai'];                                
                                $type='addition';                                  

                            }else if ($quantity<$product['tonkhohientai']) {
                                //tien hanh giam dieu chinh
                                $soluong=$product['tonkhohientai']-$quantity;
                                $type='subtraction';
                            }else{
                                $rs=array('status'=>true,'mess'=>'Thành công.','item'=>'Số lượng bằng tồn kho hiện tại không điều chỉnh'); 
                                $this->response($rs);
                            } 
                            if ($soluong>0) {
                                 $products[] = array(
                                    'product_id' => $product_id,
                                    'type' => $type,
                                    'quantity' => $soluong,
                                    'warehouse_id' => $warehouse_id,
                                    'option_id' => $option_id,
                                    'serial_no' => $_POST['serial'],
                                    );

                                
                                $check=$this->products_model->addAdjustment($data,$products);

                                if ($check) {
                                    //tien hanh notification
                                    $message='';

                                    $noti=$this->site->sentNotificationAPI('Điều chỉnh tồn kho',$note,$transaction_id=0,'KIEMKHO','ALL',$warehouse_id);

                                    $rs=array('status'=>true,'mess'=>'Thành công.','tonkhohientai'=>$quantity); 
                                }else{
                                    $rs=array('status'=>false,'mess'=>'Lỗi điều chỉnh tồn kho sản phẩm.','item'=>null);     
                                }  
                            }else{
                                $rs=array('status'=>true,'mess'=>'Thành công.','item'=>'Số lượng bằng tồn kho hiện tại không điều chỉnh'); 
                                $this->response($rs);
                            } 
                           

                        } else {
                           $rs=array('status'=>false,'mess'=>'Không tìm thấy sản phẩm.','item'=>null); 
                        }
                    }else{
                        $rs=array('status'=>false,'mess'=>'Không tìm thấy sản phẩm.','item'=>null); 
                    }
                  
                    
                }else{
                    $rs=array('status'=>false,'mess'=>'Token không chính xác.');                    
                }
            }else{
                $rs=array('status'=>false,'mess'=>'Token không hợp lệ');
            }       
        }
        $this->response($rs);
    }
}