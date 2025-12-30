<?php defined('BASEPATH') or exit('No direct script access allowed');

class The extends MY_Controller
{

    public function __construct()
    {
        parent::__construct();

        if (!$this->loggedIn) {
            $this->session->set_userdata('requested_page', $this->uri->uri_string());
            $this->sma->md('login');
        }
        if ($this->Supplier || $this->Customer) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER["HTTP_REFERER"]);
        }
        $this->lang->load('the', $this->Settings->user_language);
        $this->load->library('form_validation');
        $this->load->model('the_model');
        $this->load->model('settings_model');
        $this->digital_upload_path = 'files/';
        $this->upload_path = 'assets/uploads/';
        $this->thumbs_path = 'assets/uploads/thumbs/';
        $this->image_types = 'gif|jpg|jpeg|png|tif';
        $this->digital_file_types = 'zip|psd|ai|rar|pdf|doc|docx|xls|xlsx|ppt|pptx|gif|jpg|jpeg|png|tif|txt';
        $this->allowed_file_size = '1024';
        $this->data['logo'] = true;
    }

    public function index($warehouse_id = null)
    {

        //$this->sma->checkPermissions();

        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
       
        if ($this->Owner) {
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['warehouse_id'] = $warehouse_id;
            $this->data['warehouse'] = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : NULL;
        } else {
            $user = $this->site->getUser();
            $this->data['warehouses'] = NULL;
            $this->data['warehouse_id'] = $this->session->userdata('warehouse_id');
            $this->data['warehouse'] = $this->session->userdata('warehouse_id') ? $this->site->getWarehouseByID($this->session->userdata('warehouse_id')) : NULL;
        }


        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' =>'Danh sách phương thức thanh toán'));
        $meta = array('page_title' => 'Danh sách phương thức thanh toán - sổ quỹ', 'bc' => $bc);
        $this->page_construct('the/index', $meta, $this->data);
    }

    public function getThes($warehouse_id = null,$start_date=null,$end_date=null)
    {
        //$this->sma->checkPermissions('index');

        $this->load->library('datatables');
        $lshonquery='';
        if ($warehouse_id) {
            $lshonquery.=" AND warehouse_id='".$warehouse_id."'";                
        }
        if ($start_date&&$end_date) {
            $lshonquery.=" AND date BETWEEN '" . date("Y-m-d",strtotime($start_date)) . " 00:00:00' and '" . date("Y-m-d",strtotime($end_date)) . " 23:59:59'";
        }
        if (!$this->Admin&&!$this->Owner)
        {
            if (!$this->session->userdata('view_right')) {                
                $lshonquery.=" AND created_by='".$this->session->userdata('user_id')."'";  
            }   
        }
        $this->datatables->select("{$this->db->dbprefix('payment_the')}.id as id, DATE_FORMAT({$this->db->dbprefix('payment_the')}.date, '%Y-%m-%d %T') as date,code,name,sotk,((select COALESCE(sum(amount),0) as amount from scodeweb_payments where paid_by=scodeweb_payment_the.code and type='received' $lshonquery)+(select COALESCE(sum(amount),0) as amount from scodeweb_payments where paid_by=scodeweb_payment_the.code and type!='received' and type!='sent' $lshonquery)) as tongthu_the,((select COALESCE(sum(amount),0) as amount from scodeweb_expenses where paid_by=scodeweb_payment_the.code $lshonquery)+(select COALESCE(sum(amount),0) as amount from scodeweb_payments where paid_by=scodeweb_payment_the.code and type='sent' $lshonquery)) as tongchi_the,0 as soquy,note")->from('payment_the')->where('is_tragop!=',1);
        

       $this->datatables->add_column("Actions", "<div class=\"text-center\"><a href='" . site_url('the/edit/$1') . "' class='tip' title='" . lang("edit_the") . "'><i class=\"fa fa-edit\"></i></a>   <a href='#' class='tip po' title='<b>" . lang("delete_the") . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('the/delete_ajax/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></div>", "id"); 

        echo $this->datatables->generate();
    }
     public function getSoQuyDauKy($id = null,$start_date=null,$end_date=null,$warehouse_id=null)
    {
        //$this->sma->checkPermissions('index');
        $ton_dauky=0;
        //get tong thu truoc ngay dang chon
        $this->db->select("scodeweb_expenses.date as date,0 as Thu,amount as chi")->from('expenses');        
        if ($id)
        {
             $this->db->where('scodeweb_expenses.paid_by', $id);
        }
        if ($start_date) {
            $this->db->where($this->db->dbprefix('expenses').'.date<"' . date("Y-m-d",strtotime($start_date)) . '"');
        }else{
            $this->db->where("1",0);
        }
        if ((int)$warehouse_id>0) {     
            $this->db->where('scodeweb_expenses.warehouse_id', $warehouse_id);
        }
        
        if (!$this->Admin&&!$this->Owner)
        {
            if (!$this->session->userdata('view_right')) {        
                $this->db->where('scodeweb_expenses.created_by',$this->session->userdata('user_id'));                        
            }   
        }

        $report_query_chi=$this->db->get_compiled_select();
         
        $this->db->select("scodeweb_payments.date as date,amount as Thu,0 as chi")
            ->from('payments')
            ->join('sales', 'payments.sale_id=sales.id', 'left')
            ->group_by('payments.id');

        if ($id) {
            $this->db->where('payments.paid_by', $id);
        }
        if ($start_date) {
             $this->db->where('payments.type', 'received');
            $this->db->where($this->db->dbprefix('payments').'.date <= "' . $start_date . '"');
        }else{
            $this->db->where('payments.c_dauky_doitac>', '0');
        }
        if ((int)$warehouse_id>0) {     
            $this->db->where('payments.warehouse_id', $warehouse_id);
        }
        if (!$this->Admin&&!$this->Owner)
        {
            if (!$this->session->userdata('view_right')) {        
                $this->db->where('payments.created_by',$this->session->userdata('user_id'));                        
            }   
        }
        //$this->db->where('payments.c_dauky_doitac', '0');
        
        $report_query_ncc=$this->db->get_compiled_select();
       // $q2 = $this->db->get();
        $this->db->select("" . $this->db->dbprefix('payments') . ".date, '' as PTHU," . $this->db->dbprefix('payments') . ".reference_no as PCHI,c_name,'Chi nhà cung cấp' as diendai,(SELECT name from scodeweb_payment_the WHERE code=paid_by) as paid_by,0,amount as chi,0 as ton,scodeweb_payments.note,scodeweb_payments.id as id")
                ->from('payments')
                ->join('sales', 'payments.sale_id=sales.id', 'left')
                ->group_by('payments.id');

        if ($id) {
            $this->db->where('payments.paid_by', $id);
        }
        if ($start_date) {
            $this->db->where($this->db->dbprefix('payments').'.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
        }
        $this->db->where('payments.type', 'sent');
        
        $report_query_ncc_chi=$this->db->get_compiled_select();

        //them cac khoan thanh toan tong chp ncc lhson code
        $lshonquery="SELECT scodeweb_payments.date as date,amount as Thu,0 as chi FROM ".$this->db->dbprefix('payments');            
        $lshonquery.=" WHERE type!='sent' and type!='received'";                
        if ($id) {
            $lshonquery.=" AND scodeweb_payments.paid_by='".$id."'";                
        }
     
        if ($start_date) {
            $lshonquery.=" AND scodeweb_payments.date <= '" . date("Y-m-d",strtotime($start_date)) . "'";
        }else{
            $lshonquery.=" AND 1=0";
        }
        if ((int)$warehouse_id>0) {
            $lshonquery.=" AND scodeweb_payments.warehouse_id='".$warehouse_id."'";                
        }
        if (!$this->Admin&&!$this->Owner)
        {
            if (!$this->session->userdata('view_right')) {                        
                $lshonquery.=" AND scodeweb_payments.created_by='".$this->session->userdata('user_id')."'";                       
            }   
        }
        $query_ok="SELECT tbl.* FROM ($report_query_chi UNION $report_query_ncc UNION $lshonquery) as tbl ORDER BY tbl.date";
        $query2=$this->db->query($query_ok);
        
        if ($query2->num_rows() > 0) {
            $tongthu=0;
            $tongchi=0;
            foreach (($query2->result()) as $row) {
                $tongthu+= $row->Thu;
                $tongchi+= $row->chi;
            }
            return (float)($tongthu-$tongchi);             
        }
        return 0;
    } 
     public function getSoQuys($id = null,$start_date=null,$end_date=null,$warehouse_id=null,$xls=null)
    {

        //$this->sma->checkPermissions('index'); 

        $this->load->library('datatables');
        $this->db->select("{$this->db->dbprefix('expenses')}.date as date,'' as PTHU,reference as PCHI,c_name,(SELECT name FROM scodeweb_expense_categories WHERE id=category_id) as diendai,(SELECT name from scodeweb_payment_the WHERE code=paid_by) as paid_by,0 as Thu,amount as chi,0 as ton,scodeweb_expenses.note,CONCAT(scodeweb_expenses.id,'9999999') as id")->from('expenses');        
        if ($id) {            
            $id=rawurldecode($id);
            $this->db->where('scodeweb_expenses.paid_by', $id);
        }
        if ((int)$warehouse_id>0) {     
            $this->db->where('scodeweb_expenses.warehouse_id', $warehouse_id);
        }
        if (!$this->Admin&&!$this->Owner)
        {
            if (!$this->session->userdata('view_right')) {        
                $this->db->where('scodeweb_expenses.created_by',$this->session->userdata('user_id'));                        
            }   
        }
        if ($start_date) {
            $this->db->where($this->db->dbprefix('expenses').'.date BETWEEN "' . date("Y-m-d",strtotime($start_date)) . ' 00:00:00" and "' . date("Y-m-d",strtotime($end_date)) . ' 23:59:59"');
        }
        $report_query_chi=$this->db->get_compiled_select();
         
        $this->db->select("" . $this->db->dbprefix('payments') . ".date, " . $this->db->dbprefix('payments') . ".reference_no as PTHU,'' as PCHI,c_name,(CASE WHEN type='returned' THEN 'Chi thu hồi hàng bán' WHEN id_ncc_id_kh='0' THEN 'Thu bán hàng' WHEN id_ncc_id_kh>0 THEN 'Thu khách hàng' END) as diendai,(SELECT name from scodeweb_payment_the WHERE code=paid_by) as paid_by,amount,0 as chi,0 as ton,scodeweb_payments.note,scodeweb_payments.id as id")
                ->from('payments')
                ->join('sales', 'payments.sale_id=sales.id', 'left')
                ->group_by('payments.id');

        if ((int)$warehouse_id>0) {
            $this->db->where('payments.warehouse_id', $warehouse_id);
        }
        if (!$this->Admin&&!$this->Owner)
        {
            if (!$this->session->userdata('view_right')) {        
                $this->db->where('scodeweb_payments.created_by',$this->session->userdata('user_id'));                        
            }   
        }
        if ($id) {
            $this->db->where('payments.paid_by', $id);
        }
        if ($start_date) {
            $this->db->where($this->db->dbprefix('payments').'.date BETWEEN "' . date("Y-m-d",strtotime($start_date)) . ' 00:00:00" and "' . date("Y-m-d",strtotime($end_date)) . ' 23:59:59"');
        }
        $this->db->where('payments.type', 'received');
        $this->db->where('payments.c_dauky_doitac', '0');
        
        $report_query_ncc=$this->db->get_compiled_select();

         $this->db->select("" . $this->db->dbprefix('payments') . ".date, '' as PTHU," . $this->db->dbprefix('payments') . ".reference_no as PCHI,c_name,'Chi nhà cung cấp' as diendai,(SELECT name from scodeweb_payment_the WHERE code=paid_by) as paid_by,0,amount as chi,0 as ton,scodeweb_payments.note,scodeweb_payments.id as id")
                ->from('payments')
                ->join('sales', 'payments.sale_id=sales.id', 'left')
                ->group_by('payments.id');

        if ($id) {
            $this->db->where('payments.paid_by', $id);
        }
        if ($start_date) {
            $this->db->where($this->db->dbprefix('payments').'.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
        }
        $this->db->where('payments.type', 'sent');
        
        $report_query_ncc_chi=$this->db->get_compiled_select();

       // $q2 = $this->db->get();
        
        //them cac khoan thanh toan tong chp ncc lhson code
        $lshonquery="SELECT ".$this->db->dbprefix('payments') . ".date, " . $this->db->dbprefix('payments') . ".reference_no as PTHU,'' as PCHI,c_name,(CASE WHEN type='returned' THEN 'Chi thu hồi hàng bán' WHEN type='0' THEN (SELECT name FROM scodeweb_expense_categories WHERE id=type_cate) WHEN type='3' THEN 'Thu Nhà Cung Cấp' WHEN type='1' THEN 'Thu Nhân Viên' ELSE 'Thu bán hàng' END) as diendai,(SELECT name from scodeweb_payment_the WHERE code=paid_by) as paid_by,amount,0 as chi,0 as ton,scodeweb_payments.note,scodeweb_payments.id as id FROM ".$this->db->dbprefix('payments');
        
        $lshonquery.=" WHERE type!='sent' and type!='received' and c_dauky_doitac=0";                
        if ($id) {
            $lshonquery.=" AND scodeweb_payments.paid_by='".$id."'";                
        }
        if ((int)$warehouse_id>0) {
            $lshonquery.=" AND scodeweb_payments.warehouse_id='".$warehouse_id."'";                
        }
        if (!$this->Admin&&!$this->Owner)
        {
            if (!$this->session->userdata('view_right')) {        
                $lshonquery.=" AND scodeweb_payments.created_by='".$this->session->userdata('user_id')."'";                         
            }   
        }
        if ($start_date) {
            $lshonquery.=" AND scodeweb_payments.date BETWEEN '" . date("Y-m-d",strtotime($start_date)) . " 00:00:00' and '" . date("Y-m-d",strtotime($end_date)) . " 23:59:59'";
        }
        $ton_dauky=$this->getSoQuyDauKy($id,$start_date,$end_date,$warehouse_id);

        //echo var_dump($report_query_ncc);
        $query_ok="SELECT tbl.* FROM ($report_query_chi UNION $report_query_ncc UNION $report_query_ncc_chi UNION $lshonquery UNION (SELECT '0000-00-00 00:00:00' as `date`,'' as PTHU,'' as PCHI,'' as c_name,'TỒN ĐẦU KỲ' as diendai,'' as paid_by,0 as Thu,0 as chi,".$ton_dauky." as ton,'' as note,-1 as id)) as tbl ORDER BY tbl.date";

        if ($xls) 
        {            

            $q =  $this->db->query($query_ok);
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data[] = $row;
                }
            } else {
                $data = NULL;
            }

            if (!empty($data)) {

                $this->load->library('excel');
                $this->excel->setActiveSheetIndex(0);
                $this->excel->getActiveSheet()->setTitle(lang('Sổ Quỹ'));
                $this->excel->getActiveSheet()->SetCellValue('A1', lang('Ngày'));
                $this->excel->getActiveSheet()->SetCellValue('B1', lang('Thu'));
                $this->excel->getActiveSheet()->SetCellValue('C1', lang('Chi'));
                $this->excel->getActiveSheet()->SetCellValue('D1', lang('Đối tượng'));
                $this->excel->getActiveSheet()->SetCellValue('E1', lang('Diễn dãi'));
                $this->excel->getActiveSheet()->SetCellValue('F1', lang('Thanh toán bằng'));
                $this->excel->getActiveSheet()->SetCellValue('G1', lang('Thu'));
                $this->excel->getActiveSheet()->SetCellValue('H1', lang('Chi'));
                $this->excel->getActiveSheet()->SetCellValue('I1', lang('Tồn'));
                $this->excel->getActiveSheet()->SetCellValue('J1', lang('Ghi chú'));

                $row = 2;
                $tondauky=0;
                foreach ($data as $data_row) {
                    if ($row==2) {
                        $tondauky=(float)$data_row->ton;
                    }else{   
                        
                        $tondauky+=(float)$data_row->Thu-(float)$data_row->chi; 
                        $data_row->ton=$tondauky;
                    }   

                    $this->excel->getActiveSheet()->SetCellValue('A' . $row, $data_row->date);
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->PTHU);
                    $this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->PCHI);
                    $this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->c_name);
                    $this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->diendai);
                    $this->excel->getActiveSheet()->SetCellValue('F' . $row, $data_row->paid_by);
                    $this->excel->getActiveSheet()->SetCellValue('G' . $row, $data_row->Thu);
                    $this->excel->getActiveSheet()->SetCellValue('H' . $row, $data_row->chi);
                    $this->excel->getActiveSheet()->SetCellValue('I' . $row, $data_row->ton);
                    $this->excel->getActiveSheet()->SetCellValue('J' . $row, $data_row->note);
                    
                    $row++;
                }

                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('J')->setWidth(25);
                $filename = 'Bao_Cao_So_Quy';
                $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                if ($pdf) {
                    $styleArray = array(
                        'borders' => array(
                            'allborders' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN
                            )
                        )
                    );
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
                    $objWriter->save('php://output');
                    exit();
                }
                if ($xls) {
                    ob_start();
                    ob_clean();
                    header('Content-Type: application/vnd.ms-excel');
                    header('Content-Disposition: attachment;filename="' . $filename . '.xls"');
                    header('Cache-Control: max-age=0');
                    ob_clean();
                    $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
                    $objWriter->save('php://output');
                    exit();
                }

            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            redirect($_SERVER["HTTP_REFERER"]);                    
        }else
        {            
           echo $rs=$this->datatables->generate_querylhson($output = 'json', $charset = 'UTF-8',$query_ok);
        }      
    } 


    public function add()
    {
        if (!$this->Admin && !$this->Owner) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER["HTTP_REFERER"]);
        }

        $this->form_validation->set_message('is_natural_no_zero', lang("no_zero_required"));
        $this->form_validation->set_rules('name', lang("name"), 'required');
        $this->form_validation->set_rules('code', lang("code"), 'required');
        $this->form_validation->set_rules('code', lang("code"), 'is_unique[payment_the.code]');

        if ($this->form_validation->run() == true) {
            $date = ($this->Owner || $this->Admin) ? $this->sma->fld(trim($this->input->post('date'))) : date('Y-m-d H:i:s');
            if($date=='00/00/0000 00:00:00'){
                $date=date('Y-m-d H:i:s');
            }
            
            $code = $this->input->post('code');
            if (strlen($code)>50) {
                 $this->session->set_flashdata('error', lang("Mã phương thức không quá 50 ký tự"));
                 redirect("the");
            }
            $name = $this->input->post('name');
            $sotk = $this->input->post('sotk');
            $note = $this->input->post('note');
            $is_tragop = isset($_POST['is_tragop'])?1:0;
            $data = array('date' => $date,
                'code' => $code,
                'name' => $name,
                'sotk' => $sotk,
                'note' => $note,
                'is_tragop' => $is_tragop,
                'created_by' => $this->session->userdata('user_id'),
            );
             $warehouse_id=$this->session->userdata('warehouse_id');
                if ($warehouse_id==null) {
                    $warehouse_id=$this->Settings->default_warehouse;
                }
            if ($this->input->post('sodudauky')) {
                $payment = array(
                        'date' => $date,
                        'sale_id' => null,
                        'reference_no' => $this->site->getReference('thu'),
                        'amount' => $this->input->post('sodudauky'),
                        'paid_by' =>$code ,
                        'cheque_no' => '',
                        'cc_no' => '',
                        'cc_holder' => '',
                        'cc_month' => '',
                        'cc_year' => '',
                        'cc_type' => '',
                        'note' =>' Khai báo sổ quỹ đầu kỳ',
                        'created_by' => $this->session->userdata('user_id'),
                        'type' => '0',
                        'id_ncc_id_kh' =>0,
                        'warehouse_id' => $this->Settings->default_warehouse,
                        'c_name' => $name,
                        'c_phone' => '',
                        'warehouse_id'=>$warehouse_id,
                        'c_address' => '','c_dauky_doitac'=>0);

            }           

            // $this->sma->print_arrays($data, $products, $payment);
        }

        if ($this->form_validation->run() == true && $this->the_model->addThe($data,$payment)) {
                    
            $this->session->set_userdata('remove_rels', 1);
            $this->session->set_flashdata('message', lang("the_added"));
            redirect("the");
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('the'), 'page' => lang('the_title')), array('link' => '#', 'page' => lang('add_the')));
            $meta = array('page_title' => lang('add_the'), 'bc' => $bc);
            $this->page_construct('the/add', $meta, $this->data);
        }
    }

    public function edit($id = null)
    {
        if (!$this->Admin && !$this->Owner) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER["HTTP_REFERER"]);
        }

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $inv = $this->the_model->getTheByID($id);
        if (!$this->session->userdata('edit_right')) {
            $this->sma->view_rights($inv->created_by);
        }
        $sodudauky=(float)$this->the_model->getSoDuDauKy($id);
        $this->form_validation->set_message('is_natural_no_zero', lang("no_zero_required"));
        $this->form_validation->set_rules('name', lang("name"), 'required');
        if ($this->input->post('code') !== $inv->code) {
            $this->form_validation->set_rules('code', lang("code"), 'is_unique[payment_the.code]');
        }
        if ($this->form_validation->run() == true) {

            

            $date = ($this->Owner || $this->Admin) ? $this->sma->fld(trim($this->input->post('date'))) : $inv->date;
            
            if($date=='00/00/0000 00:00:00')
            {
                $date=$this->sma->fld(date('Y-m-d H:i:s'));
            }
            $code = $this->input->post('code');
            if (strlen($code)>50) {
                 $this->session->set_flashdata('error', lang("Mã phương thức không quá 50 ký tự"));
                 redirect("the");
            }
            $name = $this->input->post('name');
            $sotk = $this->input->post('sotk');
            $note = $this->input->post('note');
            $is_tragop = isset($_POST['is_tragop'])?1:0;
            $data = array('date' => $date,
                'code' => $code,
                'name' => $name,
                'sotk' => $sotk,
                'note' => $note,
                'is_tragop' => $is_tragop
             );

             $warehouse_id=$this->session->userdata('warehouse_id');
                if ($warehouse_id==null) {
                    $warehouse_id=$this->Settings->default_warehouse;
                }

            if ($this->input->post('sodudauky')&&$this->input->post('sodudauky') != $sodudauky) {
                $payment = array(
                        'date' => $date,
                        'sale_id' => null,
                        'reference_no' => $this->site->getReference('thu'),
                        'amount' => $this->input->post('sodudauky'),
                        'paid_by' =>$code ,
                        'cheque_no' => '',
                        'cc_no' => '',
                        'cc_holder' => '',
                        'cc_month' => '',
                        'cc_year' => '',
                        'cc_type' => '',
                        'note' =>' Khai báo sổ quỹ đầu kỳ',
                        'created_by' => $this->session->userdata('user_id'),
                        'type' => '0',
                        'id_ncc_id_kh' =>0,
                        'warehouse_id' => $this->Settings->default_warehouse,
                        'c_name' => $name,
                        'c_phone' => '',
                        'warehouse_id'=>$warehouse_id,
                        'c_address' => '','c_dauky_doitac'=>0);
            } 
            
            // $this->sma->print_arrays($data, $products,$payment);
        }       
        if ($this->form_validation->run() == true && $this->the_model->updateThe($id, $data,$payment)) {
            
            if ($this->input->post('code') !== $inv->code && $this->getCountThu($inv->code)>0) {                
                //tien hanh thay doi code thanh code moi
                $this->updateChangePTT($inv->code,$this->input->post('code'));
            }

            $this->session->set_userdata('remove_rels', 1);
            $this->session->set_flashdata('message', lang("the_updated"));
            
            redirect("the");
        } else {
            $this->data['inv'] = $inv;
           
            $this->data['sodudauky'] = $sodudauky;

            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('the'), 'page' => lang('the')), array('link' => '#', 'page' => lang('edit_the')));
            $meta = array('page_title' => lang('edit_the'), 'bc' => $bc); 
            $this->page_construct('the/edit', $meta, $this->data);
        }
    }
    public function updateChangePTT($paid_by='cash',$paid_by_new='cash')
    {

        if ($this->db->update('payments', array('paid_by' => $paid_by_new), array('paid_by' => $paid_by))) {
            return true;
        }
    }

    public function delete($id = null)
    {
        if (!$this->Admin && !$this->Owner) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER["HTTP_REFERER"]);
        }
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
       
        if ($id>6) {
            $inv = $this->the_model->getTheByID($id);
            if ($this->getCountThu($inv->code)>0) {
                $this->sma->send_json(array('error' => 0, 'msg' => lang("Không thể xóa phương thức thanh toán này vì có thu chi")));
            }else{
                if ($this->the_model->deleteThe($id)) {
                    if ($this->input->is_ajax_request()) {
                        $this->sma->send_json(array('error' => 0, 'msg' => lang("the_deleted")));
                    }
                    $this->session->set_flashdata('message', lang('the_deleted'));
                    redirect('welcome');
                }
            }
            
        }else{
            $this->sma->send_json(array('error' => 0, 'msg' => 'Không thể xóa phương thức thanh toán mặc định'));
        }
    }

    public function getCountThu($paid_by='cash')
    {
        $this->db->from('payments')->where('paid_by', $paid_by);
        $thu=(float)$this->db->count_all_results();
        $this->db->from('expenses')->where('paid_by', $paid_by);
        $chi=(float)$this->db->count_all_results();
        return $thu+$chi;
    }

    public function the_actions()
    {
        if (!$this->Owner && !$this->GP['bulk_actions'])
        {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER["HTTP_REFERER"]);
        }

        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');

        if ($this->form_validation->run() == true) {

            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {

                    $this->sma->checkPermissions('delete');
                    foreach ($_POST['val'] as $id) {
                        $this->the_model->deleteThe($id);
                    }
                    $this->session->set_flashdata('message', lang("the_deleted"));
                    redirect($_SERVER["HTTP_REFERER"]);
                }
            }
             else {
                $this->session->set_flashdata('error', lang("the_deleted"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        
        }
    }
    public function delete_ajax($id = null)
    {
        $this->sma->checkPermissions(null, true);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        if ($id>6) {
            $inv = $this->the_model->getTheByID($id);
            if ($this->getCountThu($inv->code)>0) {
                $this->sma->send_json(array('error' => 0, 'msg' => lang("Không thể xóa phương thức thanh toán này vì có thu chi")));
            }else{
                 $rsdel=$this->the_model->deleteThe($id);
                    if ($rsdel) {
                        $this->sma->send_json(array('error' => 0, 'msg' => lang("the_deleted")));
                    }else{
                        $this->sma->send_json(array('error' => 0, 'msg' => 'Lỗi khi xóa phương thức thanh toán'));
                    }
            }
           
        }else{
            $this->sma->send_json(array('error' => 0, 'msg' => 'Không thể xóa phương thức thanh toán mặc định'));
        }
        
    }
    function suggestions($term = NULL, $limit = NULL)
    {
        if ($this->input->get('term')) {
            $term = $this->input->get('term', TRUE);
        }
        if (strlen($term) < 1) {
            return FALSE;
        }
        $limit = $this->input->get('limit', TRUE);
        $rows['results'] = $this->the_model->getTheSuggestions($term, $limit);
        $this->sma->send_json($rows);
    }
    function getTheById($id = NULL)
    {
        // $this->sma->checkPermissions('index');
        $row = $this->the_model->getTheById($id);
        $this->sma->send_json(array(array('id' => $row->id, 'text' => ($row->code != '-' ? $row->code.'-'.$row->name : $row->name.'-'.$row->sotk))));
    }
    public function modal_view($soquy_id = null)
    {
       // $this->sma->checkPermissions('index', true);

        if ($this->input->get('soquy_id')) {
            $soquy_id = $this->input->get('id');
            
        }
        if ($this->Owner) {
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['warehouse_id'] = $warehouse_id;
            $this->data['warehouse'] = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : NULL;
        } else {
            $user = $this->site->getUser();
            $this->data['warehouses'] = NULL;
            $this->data['warehouse_id'] = $this->session->userdata('warehouse_id');
            $this->data['warehouse'] = $this->session->userdata('warehouse_id') ? $this->site->getWarehouseByID($this->session->userdata('warehouse_id')) : NULL;
        }

        $this->data['allsoquy']=$this->site->getAllSoQuy();
        $this->data['soquy_id']=$soquy_id;
        $this->load->view($this->theme . 'the/modal_view', $this->data);

    }
    public function tragop($warehouse_id = null)
    {

        //$this->sma->checkPermissions();

        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
       
        if ($this->Owner) {
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['warehouse_id'] = $warehouse_id;
            $this->data['warehouse'] = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : NULL;
        } else {
            $user = $this->site->getUser();
            $this->data['warehouses'] = NULL;
            $this->data['warehouse_id'] = $this->session->userdata('warehouse_id');
            $this->data['warehouse'] = $this->session->userdata('warehouse_id') ? $this->site->getWarehouseByID($this->session->userdata('warehouse_id')) : NULL;
        }

        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' =>'Danh sách phương thức trả góp'));
        $meta = array('page_title' => 'Danh sách phương thức trả góp', 'bc' => $bc);
        $this->page_construct('the/indextragop', $meta, $this->data);
    }

    public function getThesTraGop($warehouse_id = null,$start_date=null,$end_date=null)
    {
        //$this->sma->checkPermissions('index');

        $this->load->library('datatables');
        $lshonquery='';
        if ($warehouse_id) {
            $lshonquery.=" AND warehouse_id='".$warehouse_id."'";                
        }
        if ($start_date&&$end_date) {
            $lshonquery.=" AND date BETWEEN '" . date("Y-m-d",strtotime($start_date)) . " 00:00:00' and '" . date("Y-m-d",strtotime($end_date)) . " 23:59:59'";
        }
        if (!$this->Admin&&!$this->Owner)
        {
            if (!$this->session->userdata('view_right')) {                
                $lshonquery.=" AND created_by='".$this->session->userdata('user_id')."'";  
            }   
        }
        $this->datatables->select("{$this->db->dbprefix('payment_the')}.id as id, DATE_FORMAT({$this->db->dbprefix('payment_the')}.date, '%Y-%m-%d %T') as date,code,name,((select COALESCE(sum(amount),0) as amount from scodeweb_payments where paid_by=scodeweb_payment_the.code and type='received' $lshonquery)+(select COALESCE(sum(amount),0) as amount from scodeweb_payments where paid_by=scodeweb_payment_the.code and type!='received' and type!='sent' $lshonquery)) as tongthu_the,((select COALESCE(sum(sotien_tragop),0) as amount from scodeweb_payments where paid_by=scodeweb_payment_the.code and type='received' $lshonquery)) as tongchi_the,0 as soquy,note")->from('payment_the')->where('is_tragop',1);
        
         $this->datatables->add_column("Actions", "<div class=\"text-center\"><a href='" . site_url('the/edit/$1') . "' class='tip' title='" . lang("edit_the") . "'><i class=\"fa fa-edit\"></i></a>   <a href='#' class='tip po' title='<b>" . lang("delete_the") . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('the/delete_ajax/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></div>", "id"); 

        echo $this->datatables->generate();
    }
}
