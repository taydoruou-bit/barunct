<?php defined('BASEPATH') or exit('No direct script access allowed');

class Khuyenmai extends MY_Controller
{

    public function __construct()
    {
        parent::__construct();
        if (!$this->loggedIn) {
            $this->session->set_userdata('requested_page', $this->uri->uri_string());
            $this->sma->md('login');
        }
        if ($this->Supplier) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER["HTTP_REFERER"]);
        }
        $this->lang->load('quotations', $this->Settings->user_language);
        $this->load->library('form_validation');
        $this->load->model('khuyenmai_model');
		$this->load->model('products_model');
		
        $this->digital_upload_path = 'files/';
        $this->digital_file_types = 'zip|psd|ai|rar|pdf|doc|docx|xls|xlsx|ppt|pptx|gif|jpg|jpeg|png|tif|txt';
        $this->allowed_file_size = '1024';
        $this->data['logo'] = true;

    }

    public function index($warehouse_id = null)
    {
        $this->sma->checkPermissions();

        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        if ($this->Owner || $this->Admin || !$this->session->userdata('warehouse_id')) {
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['warehouse_id'] = $warehouse_id;
            $this->data['warehouse'] = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : null;
        } else {
            $this->data['warehouses'] = null;
            $this->data['warehouse_id'] = $this->session->userdata('warehouse_id');
            $this->data['warehouse'] = $this->session->userdata('warehouse_id') ? $this->site->getWarehouseByID($this->session->userdata('warehouse_id')) : null;
        }

        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('khuyenmai')));
        $meta = array('page_title' => lang('khuyenmai'), 'bc' => $bc);
        $this->page_construct('khuyenmai/index', $meta, $this->data);

    }

    public function getKhuyenmais($warehouse_id = null)
    {
        $this->sma->checkPermissions('index');

        if ((!$this->Owner || !$this->Admin) && !$warehouse_id) {
            $user = $this->site->getUser();
            $warehouse_id = $user->warehouse_id;
        }
        $detail_link = anchor('khuyenmai/view/$1', '<i class="fa fa-file-text-o"></i> ' . lang('quote_details'));
        $email_link = anchor('khuyenmai/email/$1', '<i class="fa fa-envelope"></i> ' . lang('email_quote'), 'data-toggle="modal" data-target="#myModal"');
        $edit_link = anchor('khuyenmai/edit/$1', '<i class="fa fa-edit"></i> ' . lang('khuyenmai_update_text'));      
        
        
        $delete_link = "<a href='#' class='po' title='<b>" . $this->lang->line("Delete promotion") . "</b>' data-content=\"<p>"
        . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('khuyenmai/delete/$1') . "'>"
        . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
        . lang('Delete promotion') . "</a>";
        $action = '<div class="text-center"><div class="btn-group text-left">'
        . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
        . lang('actions') . ' <span class="caret"></span></button>
                    <ul class="dropdown-menu pull-right" role="menu">                       
                        <li>' . $edit_link . '</li>                       
                        <li>' . $delete_link . '</li>
                    </ul>
                </div></div>';

        $this->load->library('datatables');
        if ($warehouse_id) {
            $this->datatables
                ->select("id, date, tenevent, startdate,enddate, attachment")
                ->from('khuyenmai')->where('type', 0)
                ->where('warehouse_id', $warehouse_id);
        } else {
            $this->datatables
                ->select("id, date, tenevent,startdate,enddate, attachment")
                ->from('khuyenmai')->where('type', 0);
        }
        $this->datatables->add_column("Actions", $action, "id");
        echo $this->datatables->generate();
    }

    public function modal_view($quote_id = null)
    {
        $this->sma->checkPermissions('index', true);

        if ($this->input->get('id')) {
            $quote_id = $this->input->get('id');
        }
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $inv = $this->khuyenmai_model->getQuoteByID($quote_id);
        if (!$this->session->userdata('view_right')) {
            $this->sma->view_rights($inv->created_by, true);
        }
        $this->data['rows'] = $this->khuyenmai_model->getAllQuoteItems($quote_id);
        $this->data['tenevent'] =$inv->tenevent;
        $this->data['startdate'] =$inv->startdate;
		$this->data['enddate'] =$inv->enddate;
        $this->data['created_by'] = $this->site->getUser($inv->created_by);
        $this->data['updated_by'] = $inv->updated_by ? $this->site->getUser($inv->updated_by) : null;
        $this->data['warehouse'] = $this->site->getWarehouseByID($inv->warehouse_id);
        $this->data['inv'] = $inv;

        $this->load->view($this->theme . 'khuyenmai/modal_view', $this->data);

    }

    public function view($quote_id = null)
    {
        $this->sma->checkPermissions('index');

        if ($this->input->get('id')) {
            $quote_id = $this->input->get('id');
        }
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $inv = $this->khuyenmai_model->getQuoteByID($quote_id);
        if (!$this->session->userdata('view_right')) {
            $this->sma->view_rights($inv->created_by);
        }
        $this->data['rows'] = $this->khuyenmai_model->getAllQuoteItems($quote_id);
        $this->data['customer'] = $this->site->getCompanyByID($inv->customer_id);
        $this->data['biller'] = $this->site->getCompanyByID($inv->biller_id);
        $this->data['created_by'] = $this->site->getUser($inv->created_by);
        $this->data['updated_by'] = $inv->updated_by ? $this->site->getUser($inv->updated_by) : null;
        $this->data['warehouse'] = $this->site->getWarehouseByID($inv->warehouse_id);
        $this->data['inv'] = $inv;

        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('khuyenmai'), 'page' => 'Khuyến mãi'), array('link' => '#', 'page' => lang('view')));
        $meta = array('page_title' =>lang('khuyenmai_detail_text'), 'bc' => $bc);
        $this->page_construct('khuyenmai/view', $meta, $this->data);

    }

    public function pdf($quote_id = null, $view = null, $save_bufffer = null)
    {
        $this->sma->checkPermissions();

        if ($this->input->get('id')) {
            $quote_id = $this->input->get('id');
        }
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $inv = $this->khuyenmai_model->getQuoteByID($quote_id);
        if (!$this->session->userdata('view_right')) {
            $this->sma->view_rights($inv->created_by);
        }
        $this->data['rows'] = $this->khuyenmai_model->getAllQuoteItems($quote_id);
        $this->data['customer'] = $this->site->getCompanyByID($inv->customer_id);
        $this->data['biller'] = $this->site->getCompanyByID($inv->biller_id);
        $this->data['user'] = $this->site->getUser($inv->created_by);
        $this->data['warehouse'] = $this->site->getWarehouseByID($inv->warehouse_id);
        $this->data['inv'] = $inv;
        $name = $this->lang->line("quote") . "_" . str_replace('/', '_', $inv->reference_no) . ".pdf";
        $html = $this->load->view($this->theme . 'quotes/pdf', $this->data, true);
        if (! $this->Settings->barcode_img) {
            $html = preg_replace("'\<\?xml(.*)\?\>'", '', $html);
        }
        if ($view) {
            $this->load->view($this->theme . 'quotes/pdf', $this->data);
        } elseif ($save_bufffer) {
            return $this->sma->generate_pdf($html, $name, $save_bufffer);
        } else {
            $this->sma->generate_pdf($html, $name);
        }
    }

    

    public function email($quote_id = null)
    {
        $this->sma->checkPermissions(false, true);

        if ($this->input->get('id')) {
            $quote_id = $this->input->get('id');
        }
        $inv = $this->khuyenmai_model->getQuoteByID($quote_id);
        $this->form_validation->set_rules('to', $this->lang->line("to") . " " . $this->lang->line("email"), 'trim|required|valid_email');
        $this->form_validation->set_rules('subject', $this->lang->line("subject"), 'trim|required');
        $this->form_validation->set_rules('cc', $this->lang->line("cc"), 'trim|valid_emails');
        $this->form_validation->set_rules('bcc', $this->lang->line("bcc"), 'trim|valid_emails');
        $this->form_validation->set_rules('note', $this->lang->line("message"), 'trim');

        if ($this->form_validation->run() == true) {
            if (!$this->session->userdata('view_right')) {
                $this->sma->view_rights($inv->created_by);
            }
            $to = $this->input->post('to');
            $subject = $this->input->post('subject');
            if ($this->input->post('cc')) {
                $cc = $this->input->post('cc');
            } else {
                $cc = null;
            }
            if ($this->input->post('bcc')) {
                $bcc = $this->input->post('bcc');
            } else {
                $bcc = null;
            }
            $customer = $this->site->getCompanyByID($inv->customer_id);
            $biller = $this->site->getCompanyByID($inv->biller_id);
            $this->load->library('parser');
            $parse_data = array(
                'reference_number' => $inv->reference_no,
                'contact_person' => $customer->name,
                'company' => $customer->company,
                'site_link' => base_url(),
                'site_name' => $this->Settings->site_name,
                'logo' => '<img src="' . base_url() . 'assets/uploads/logos/' . $biller->logo . '" alt="' . ($biller->company != '-' ? $biller->company : $biller->name) . '"/>',
            );
            $msg = $this->input->post('note');
            $message = $this->parser->parse_string($msg, $parse_data);
            $attachment = $this->pdf($quote_id, null, 'S'); //delete_files($attachment);
        } elseif ($this->input->post('send_email')) {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->session->set_flashdata('error', $this->data['error']);
            redirect($_SERVER["HTTP_REFERER"]);
        }

        if ($this->form_validation->run() == true && $this->sma->send_email($to, $subject, $message, null, null, $attachment, $cc, $bcc)) {
            delete_files($attachment);
            $this->db->update('quotes', array('status' => 'sent'), array('id' => $quote_id));
            $this->session->set_flashdata('message', $this->lang->line("email_sent"));
            redirect("quotes");
        } else {

            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

            if (file_exists('./themes/' . $this->theme . '/views/email_templates/quote.html')) {
                $quote_temp = file_get_contents('themes/' . $this->theme . '/views/email_templates/quote.html');
            } else {
                $quote_temp = file_get_contents('./themes/default/views/email_templates/quote.html');
            }

            $this->data['subject'] = array('name' => 'subject',
                'id' => 'subject',
                'type' => 'text',
                'value' => $this->form_validation->set_value('subject', lang('quote').' (' . $inv->reference_no . ') '.lang('from').' '.$this->Settings->site_name),
            );
            $this->data['note'] = array('name' => 'note',
                'id' => 'note',
                'type' => 'text',
                'value' => $this->form_validation->set_value('note', $quote_temp),
            );
            $this->data['customer'] = $this->site->getCompanyByID($inv->customer_id);

            $this->data['id'] = $quote_id;
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'quotes/email', $this->data);

        }
    }

    public function add()
    {
        $this->sma->checkPermissions();

       // $this->form_validation->set_message('is_natural_no_zero', $this->lang->line("no_zero_required"));      


            $reference = $this->input->post('tenevent') ? $this->input->post('tenevent') : $this->site->getReference('qu');
            if ($this->Owner || $this->Admin) {
                $date = $this->sma->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }
            $warehouse_id = $this->input->post('warehouse');
            $tenevent = $this->input->post('tenevent');           
			$startdate = $this->sma->fld(trim($this->input->post('startdate')));
            $enddate = $this->sma->fld(trim($this->input->post('enddate')));						
            $note = $this->sma->clear_tags($this->input->post('note'));

            $total = 0;
            $product_tax = 0;
            $order_tax = 0;
            $product_discount = 0;
            $order_discount = 0;
            $percentage = '%';
            $i = isset($_POST['product_code']) ? sizeof($_POST['product_code']) : 0;
            for ($r = 0; $r < $i; $r++) {
                $item_id = $_POST['product_id'][$r];
                $giakhuyenmai = $_POST['giakhuyenmai'][$r];

                if (isset($item_id) && isset($giakhuyenmai)) {
                    
                    $products[] = array(
                        'product_id' => $item_id,
                        'startdate' => $startdate,
                        'enddate' => $enddate,
                        'giakhuyenmai' => $giakhuyenmai,
						'warehouse_id' => $warehouse_id,
                    );

                }
            }
            if (empty($products)) {
                $this->form_validation->set_rules('product', lang("order_items"), 'required');
            } else {
                krsort($products);
            }

            $data = array('date' => $date,
                'tenevent' => $reference,
                'warehouse_id' => $warehouse_id,
                'note' => $note,
                'status' => $status,
				'startdate' => $startdate,
				'enddate' => $enddate,
                'created_by' => $this->session->userdata('user_id'),
            );

            if ($_FILES['document']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] = $this->digital_upload_path;
                $config['allowed_types'] = $this->digital_file_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = false;
                $config['encrypt_name'] = true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('document')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo = $this->upload->file_name;
                $data['attachment'] = $photo;
            }

            // $this->sma->print_arrays($data, $products);
      
        if ($_POST&&$this->khuyenmai_model->addKhuyenmai($data, $products)) {
			
			foreach($products as $sp){
				$date_product_update=array('promotion' =>1,'promo_price' => $this->sma->formatDecimal($sp["giakhuyenmai"]),
					'start_date' => $startdate,'end_date' => $enddate);
				$this->khuyenmai_model->updateProduct($sp["product_id"], $date_product_update);			
				
			}			
			$this->session->set_userdata('remove_quls', 1);
			$this->session->set_flashdata('message',lang("khuyenmai_add_success"));
			redirect('khuyenmai');
        } else {

            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

            $this->data['warehouses'] = ($this->Owner || $this->Admin || !$this->session->userdata('biller_id')) ? $this->site->getAllWarehouses() : null;
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('khuyenmai'), 'page' => 'Khuyến mãi'), array('link' => '#', 'page' => 'Thêm khuyến mãi'));
            $meta = array('page_title' => lang('Thêm khuyến mãi'), 'bc' => $bc);
            $this->page_construct('khuyenmai/add', $meta, $this->data);
        }
    }

    public function edit($id = null)
    {
        $this->sma->checkPermissions();

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $inv = $this->khuyenmai_model->getQuoteByID($id);
        if (!$this->session->userdata('edit_right')) {
            $this->sma->view_rights($inv->created_by);
        }
        
			$reference = $this->input->post('tenevent') ? $this->input->post('tenevent') : $this->site->getReference('qu');
           
            $warehouse_id = $this->input->post('warehouse');
            $tenevent = $this->input->post('tenevent');           
			$startdate = $this->sma->fld(trim($this->input->post('startdate')));
            $enddate = $this->sma->fld(trim($this->input->post('enddate')));						
            $note = $this->sma->clear_tags($this->input->post('note'));

            $total = 0;
            $product_tax = 0;
            $order_tax = 0;
            $product_discount = 0;
            $order_discount = 0;
            $percentage = '%';
            $i = isset($_POST['product_code']) ? sizeof($_POST['product_code']) : 0;
			
            for ($r = 0; $r < $i; $r++) {
                $item_id = $_POST['product_id'][$r];
                $giakhuyenmai = $_POST['giakhuyenmai'][$r];
				
                if ($this->khuyenmai_model->getProductById($item_id) && isset($giakhuyenmai)) {                    
                    $products[] = array(
                        'product_id' => $item_id,
                        'giakhuyenmai' => $giakhuyenmai,
						'warehouse_id' => $warehouse_id,
                    );					

                }
            }
            if (empty($products)) {
                $this->form_validation->set_rules('product', lang("order_items"), 'required');
            } else {
                krsort($products);
            }

            $data = array(
                'tenevent' => $reference,
                'warehouse_id' => $warehouse_id,
                'note' => $note,
                'updated_by' => $this->session->userdata('user_id'),
                'updated_at' => date('Y-m-d H:i:s')
            );
			if($this->input->post('startdate')!=""){
				$data['startdate']=$startdate;
			}
			if($this->input->post('enddate')!=""){
				$data['enddate']=$enddate;
			}
            if ($_FILES['document']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] = $this->digital_upload_path;
                $config['allowed_types'] = $this->digital_file_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = false;
                $config['encrypt_name'] = true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('document')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo = $this->upload->file_name;
                $data['attachment'] = $photo;
            }

        if ($_POST && $this->khuyenmai_model->updateKhuyenmai($id, $data, $products)) {
			
			foreach($products as $sp){
				$date_product_update=array('promotion' =>1,'promo_price' => $this->sma->formatDecimal($sp["giakhuyenmai"]));
				if($this->input->post('startdate')!=""){
					$date_product_update['start_date']=date("Y-m-d",strtotime($startdate));
				}
				if($this->input->post('enddate')!=""){
					
					echo $date_product_update['end_date']=date("Y-m-d",strtotime($enddate));
				}	
				$this->khuyenmai_model->updateProduct($sp["product_id"], $date_product_update);			
				
			}			
            $this->session->set_userdata('remove_quls', 1);
            $this->session->set_flashdata('message', lang('khuyenmai_update_success'));
            redirect('khuyenmai');
        } else {

            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

            $this->data['inv'] = $this->khuyenmai_model->getQuoteByID($id);
            $inv_items = $this->khuyenmai_model->getAllQuoteItems($id);
            krsort($inv_items);
            $c = rand(100000, 9999999);
            foreach ($inv_items as $item) {
                $row = $this->site->getProductByID($item->product_id);
                if (!$row) {
                    $row = json_decode('{}');
                    $row->tax_method = 0;
                } else {
                    unset($row->details, $row->product_details, $row->cost, $row->supplier1price, $row->supplier2price, $row->supplier3price, $row->supplier4price, $row->supplier5price);
                }
                $row->id = $item->product_id;
                $row->code = $item->product_code;
                $row->name = $item->product_name;
				$row->promo_price = $item->giakhuyenmai;	
				$row->price = $item->price;					
                $ri = $this->Settings->item_addition ? $row->id : $c;
				
				$options = $this->khuyenmai_model->getProductOptions($row->id, $item->warehouse_id);				
				
                $pr[$ri] = array('id' => $ri, 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 'row' => $row, 'options' => $options);
                $c++;
            }
			$this->data['warehouses'] = ($this->Owner || $this->Admin || !$this->session->userdata('biller_id')) ? $this->site->getAllWarehouses() : null;
            $this->data['inv_items'] = json_encode($pr);
            $this->data['id'] = $id;
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('khuyenmai'), 'page' => lang('Promotion')), array('link' => '#', 'page' => lang('khuyenmai_update_text')));
            $meta = array('page_title' =>lang('khuyenmai_update_text'), 'bc' => $bc);
            $this->page_construct('khuyenmai/edit', $meta, $this->data);
        }
    }

    public function delete($id = null)
    {
        $this->sma->checkPermissions(NULL, true);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        if ($this->khuyenmai_model->deleteQuote($id)) {
            if ($this->input->is_ajax_request()) {
                $this->sma->send_json(array('error' => 0, 'msg' => lang("khuyenmai_deleted")));
            }
            $this->session->set_flashdata('message', lang('khuyenmai_deleted'));
            redirect('khuyenmai');
        }
    }
	
    public function suggestions()
    {
        $term = $this->input->get('term', true);
        $warehouse_id = $this->input->get('warehouse_id', true);
        

        if (strlen($term) < 1 || !$term) {
            die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . site_url('khuyenmai') . "'; }, 10);</script>");
        }

        $analyzed = $this->sma->analyze_term($term);
        $sr = $analyzed['term'];
        $option_id = $analyzed['option_id'];
        $warehouse = $this->site->getWarehouseByID($warehouse_id);
        $rows = $this->khuyenmai_model->getProductNames($sr, $warehouse_id);
        if ($rows) {
            $c = str_replace(".", "", microtime(true));
            $r = 0;
            foreach ($rows as $row) {
                unset($row->cost, $row->details, $row->product_details, $row->image, $row->barcode_symbology, $row->cf1, $row->cf2, $row->cf3, $row->cf4, $row->cf5, $row->cf6, $row->supplier1price, $row->supplier2price, $row->cfsupplier3price, $row->supplier4price, $row->supplier5price, $row->supplier1, $row->supplier2, $row->supplier3, $row->supplier4, $row->supplier5, $row->supplier1_part_no, $row->supplier2_part_no, $row->supplier3_part_no, $row->supplier4_part_no, $row->supplier5_part_no);
                $option = false;
                $row->quantity = 0;
                $row->item_tax_method = $row->tax_method;
                $row->qty = 1;
                $row->discount = '0';
                $options = $this->khuyenmai_model->getProductOptions($row->id, $warehouse_id);
                if ($options) {
                    $opt = $option_id && $r == 0 ? $this->khuyenmai_model->getProductOptionByID($option_id) : $options[0];
                    if (!$option_id || $r > 0) {
                        $option_id = $opt->id;
                    }
                } else {
                    $opt = json_decode('{}');
                    $opt->price = 0;
                    $option_id = FALSE;
                }
				$row->id=$row->id;
                $row->option = $option_id;
                $pis = $this->site->getPurchasedItems($row->id, $warehouse_id, $row->option);
                if ($pis) {
                    foreach ($pis as $pi) {
                        $row->quantity += $pi->quantity_balance;
                    }
                }
                if ($options) {
                    $option_quantity = 0;
                    foreach ($options as $option) {
                        $pis = $this->site->getPurchasedItems($row->id, $warehouse_id, $row->option);
                        if ($pis) {
                            foreach ($pis as $pi) {
                                $option_quantity += $pi->quantity_balance;
                            }
                        }
                        if ($option->quantity > $option_quantity) {
                            $option->quantity = $option_quantity;
                        }
                    }
                }
                if ($row->promotion) {
                    $row->price = $row->promo_price;
                } elseif ($customer->price_group_id) {
                    if ($pr_group_price = $this->site->getProductGroupPrice($row->id, $customer->price_group_id)) {
                        $row->price = $pr_group_price->price;
                    }
                } elseif ($warehouse->price_group_id) {
                    if ($pr_group_price = $this->site->getProductGroupPrice($row->id, $warehouse->price_group_id)) {
                        $row->price = $pr_group_price->price;
                    }
                }
                $row->price = $row->price ;
                $row->real_unit_price = $row->price;
                $row->base_quantity = 1;
                $row->base_unit = $row->unit;
                $row->base_unit_price = $row->price;
                $row->unit = $row->sale_unit ? $row->sale_unit : $row->unit;
                $combo_items = false;
                if ($row->type == 'combo') {
                    $combo_items = $this->khuyenmai_model->getProductComboItems($row->id, $warehouse_id);
                }
                $units = $this->site->getUnitsByBUID($row->base_unit);
                $tax_rate = $this->site->getTaxRateByID($row->tax_rate);

                $pr[] = array('id' => ($c + $r), 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 'category' => $row->category_id, 
                    'row' => $row, 'combo_items' => $combo_items, 'tax_rate' => $tax_rate, 'units' => $units, 'options' => $options);
                $r++;
            }
            $this->sma->send_json($pr);
        } else {
            $this->sma->send_json(array(array('id' => 0, 'label' => lang('no_match_found'), 'value' => $term)));
        }
    }

    public function khuyenmai_actions()
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
                    foreach ($_POST['val'] as $id) {
                        $this->khuyenmai_model->deleteQuote($id);
                    }
                    $this->session->set_flashdata('message', $this->lang->line("Promotion deleted"));
                    redirect($_SERVER["HTTP_REFERER"]);

                } elseif ($this->input->post('form_action') == 'combine') {

                    $html = $this->combine_pdf($_POST['val']);

                } elseif ($this->input->post('form_action') == 'export_excel' || $this->input->post('form_action') == 'export_pdf') {

                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('Promotion'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('reference_no'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('biller'));
                    $this->excel->getActiveSheet()->SetCellValue('D1', lang('customer'));
                    $this->excel->getActiveSheet()->SetCellValue('E1', lang('total'));
                    $this->excel->getActiveSheet()->SetCellValue('F1', lang('status'));

                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $qu = $this->khuyenmai_model->getQuoteByID($id);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->sma->hrld($qu->date));
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $qu->reference_no);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $qu->biller);
                        $this->excel->getActiveSheet()->SetCellValue('D' . $row, $qu->customer);
                        $this->excel->getActiveSheet()->SetCellValue('E' . $row, $qu->total);
                        $this->excel->getActiveSheet()->SetCellValue('F' . $row, $qu->status);
                        $row++;
                    }

                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'quotations_' . date('Y_m_d_H_i_s');
                    if ($this->input->post('form_action') == 'export_pdf') {
                        $styleArray = array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)));
                        $this->excel->getDefaultStyle()->applyFromArray($styleArray);
                        $this->excel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
                        require_once APPPATH . "third_party" . DIRECTORY_SEPARATOR . "MPDF" . DIRECTORY_SEPARATOR . "mpdf.php";
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
                $this->session->set_flashdata('error', $this->lang->line("Không có Khuyến mãi được chọn"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }

    public function update_status($id)
    {

        $this->form_validation->set_rules('status', lang("status"), 'required');

        if ($this->form_validation->run() == true) {
            $status = $this->input->post('status');
            $note = $this->sma->clear_tags($this->input->post('note'));
        } elseif ($this->input->post('update')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect(isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : 'sales');
        }

        if ($this->form_validation->run() == true && $this->khuyenmai_model->updateStatus($id, $status, $note)) {
            $this->session->set_flashdata('message', lang('status_updated'));
            redirect(isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : 'sales');
        } else {

            $this->data['inv'] = $this->khuyenmai_model->getQuoteByID($id);
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme.'quotes/update_status', $this->data);

        }
    }
	
	public function addnew()
    {
        $this->sma->checkPermissions();

       // $this->form_validation->set_message('is_natural_no_zero', $this->lang->line("no_zero_required"));      


            $reference = $this->input->post('tenevent') ? $this->input->post('tenevent') : $this->site->getReference('km');
            if ($this->Owner || $this->Admin) {
                $date = $this->sma->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }
            $warehouse_id = $this->input->post('warehouse');
            $tenevent = $this->input->post('tenevent');           
			$main_product_id = $this->input->post('main_product');           
			$main_quantity = $this->input->post('main_quantity');           
			$startdate = $this->sma->fld(trim($this->input->post('startdate')));
            $enddate = $this->sma->fld(trim($this->input->post('enddate')));						
            $note = $this->sma->clear_tags($this->input->post('note'));

            $total = 0;
            $product_tax = 0;
            $order_tax = 0;
            $product_discount = 0;
            $order_discount = 0;
            $percentage = '%';
            $i = isset($_POST['product_code']) ? sizeof($_POST['product_code']) : 0;
            for ($r = 0; $r < $i; $r++) {
                $item_id = $_POST['product_id'][$r];
                $giakhuyenmai = $_POST['giakhuyenmai'][$r];
				$sub_quantity=$_POST['kmquan'][$r];
                if (isset($item_id) && isset($giakhuyenmai)) {
                    
                    $products[] = array(
                        'product_id' => $item_id,
                        'startdate' => $startdate,
                        'enddate' => $enddate,
                        'giakhuyenmai' => $giakhuyenmai,
						'sub_quantity' => $sub_quantity,
						'warehouse_id' => $warehouse_id,						
                    );

                }
            }
            if (empty($products)) {
                $this->form_validation->set_rules('product', lang("order_items"), 'required');
            } else {
                krsort($products);
            }

            $data = array('date' => $date,
                'tenevent' => $reference,
                'warehouse_id' => $warehouse_id,
                'note' => $note,
                'status' => $status,
				'startdate' => $startdate,
				'enddate' => $enddate,
                'created_by' => $this->session->userdata('user_id'),
				'main_product_id' => $main_product_id,
				'main_quantity' => $main_quantity,
				'type' => 1,
            );

            if ($_FILES['document']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] = $this->digital_upload_path;
                $config['allowed_types'] = $this->digital_file_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = false;
                $config['encrypt_name'] = true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('document')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo = $this->upload->file_name;
                $data['attachment'] = $photo;
            }

            // $this->sma->print_arrays($data, $products);
      
        if ($_POST&&$this->khuyenmai_model->addKhuyenmai($data, $products)) {
			
			
			$this->session->set_userdata('remove_quls', 1);
			$this->session->set_flashdata('message',lang("khuyenmai_add_success"));
			redirect('khuyenmai/indexnew');
        } else {

            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

            $this->data['warehouses'] = ($this->Owner || $this->Admin || !$this->session->userdata('biller_id')) ? $this->site->getAllWarehouses() : null;
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('khuyenmai'), 'page' => 'Khuyến mãi kèm sản phẩm'), array('link' => '#', 'page' => 'Thêm mới KM kèm sản phẩm'));
            $meta = array('page_title' => lang('Thêm mới KM kèm sản phẩm'), 'bc' => $bc);
            $this->page_construct('khuyenmai/addnew', $meta, $this->data);
        }
    }

    public function editnew($id = null)
    {
        $this->sma->checkPermissions();

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $inv = $this->khuyenmai_model->getQuoteByID($id);
        if (!$this->session->userdata('edit_right')) {
            $this->sma->view_rights($inv->created_by);
        }
        
			$reference = $this->input->post('tenevent') ? $this->input->post('tenevent') : $this->site->getReference('qu');
           
            $warehouse_id = $this->input->post('warehouse');
            $tenevent = $this->input->post('tenevent');       
			$main_product_id = $this->input->post('main_product');           
			$main_quantity = $this->input->post('main_quantity');     	
			$startdate = $this->sma->fld(trim($this->input->post('startdate')));
            $enddate = $this->sma->fld(trim($this->input->post('enddate')));						
            $note = $this->sma->clear_tags($this->input->post('note'));

            $total = 0;
            $product_tax = 0;
            $order_tax = 0;
            $product_discount = 0;
            $order_discount = 0;
            $percentage = '%';
            $i = isset($_POST['product_code']) ? sizeof($_POST['product_code']) : 0;
			
            for ($r = 0; $r < $i; $r++) {
                $item_id = $_POST['product_id'][$r];
                $giakhuyenmai = $_POST['giakhuyenmai'][$r];
				$sub_quantity=$_POST['kmquan'][$r];
                if ($this->khuyenmai_model->getProductById($item_id) && isset($giakhuyenmai)) {                    
                    $products[] = array(
                        'product_id' => $item_id,
                        'giakhuyenmai' => $giakhuyenmai,
						'sub_quantity' => $sub_quantity,
						'warehouse_id' => $warehouse_id,
                    );					

                }
            }
            if (empty($products)) {
                $this->form_validation->set_rules('product', lang("order_items"), 'required');
            } else {
                krsort($products);
            }

            $data = array(
                'tenevent' => $reference,
                'warehouse_id' => $warehouse_id,
                'note' => $note,
                'updated_by' => $this->session->userdata('user_id'),
                'updated_at' => date('Y-m-d H:i:s'),
				'main_product_id' => $main_product_id,
				'main_quantity' => $main_quantity,
				'type' => 1,
            );
			if($this->input->post('startdate')!=""){
				$data['startdate']=$startdate;
			}
			if($this->input->post('enddate')!=""){
				$data['enddate']=$enddate;
			}
            if ($_FILES['document']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] = $this->digital_upload_path;
                $config['allowed_types'] = $this->digital_file_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = false;
                $config['encrypt_name'] = true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('document')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo = $this->upload->file_name;
                $data['attachment'] = $photo;
            }

        if ($_POST && $this->khuyenmai_model->updateKhuyenmai($id, $data, $products)) {
								
            $this->session->set_userdata('remove_quls', 1);
            $this->session->set_flashdata('message', lang('khuyenmai_update_success'));
            redirect('khuyenmai/indexnew');
        } else {

            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

            $this->data['inv'] = $this->khuyenmai_model->getQuoteByID($id);
            $inv_items = $this->khuyenmai_model->getAllQuoteItems($id);
            krsort($inv_items);
            $c = rand(100000, 9999999);
            foreach ($inv_items as $item) {
                $row = $this->site->getProductByID($item->product_id);
                if (!$row) {
                    $row = json_decode('{}');
                    $row->tax_method = 0;
                } else {
                    unset($row->details, $row->product_details, $row->cost, $row->supplier1price, $row->supplier2price, $row->supplier3price, $row->supplier4price, $row->supplier5price);
                }
                $row->id = $item->product_id;
                $row->code = $item->product_code;
                $row->name = $item->product_name;
				$row->promo_price = $item->giakhuyenmai;	
				$row->sub_quantity = $item->sub_quantity;	
				$row->price = $item->price;					
                $ri = $this->Settings->item_addition ? $row->id : $c;
				
				$options = $this->khuyenmai_model->getProductOptions($row->id, $item->warehouse_id);				
				
                $pr[$ri] = array('id' => $ri, 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 'row' => $row, 'options' => $options);
                $c++;
            }
			$this->data['warehouses'] = ($this->Owner || $this->Admin || !$this->session->userdata('biller_id')) ? $this->site->getAllWarehouses() : null;
            $this->data['inv_items'] = json_encode($pr);
            $this->data['id'] = $id;
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('khuyenmai'), 'page' => lang('Promotion by product')), array('link' => '#', 'page' => lang('khuyenmai_update_text')));
            $meta = array('page_title' =>lang('khuyenmai_update_text'), 'bc' => $bc);
            $this->page_construct('khuyenmai/editnew', $meta, $this->data);
        }
    }
	
	function suggestionsProducts($term = NULL, $limit = NULL)
    {
        // $this->sma->checkPermissions('index');
        if ($this->input->get('term')) {
            $term = $this->input->get('term', TRUE);
        }
        $limit = $this->input->get('limit', TRUE);
        $rows['results'] = $this->khuyenmai_model->getProductsSuggestions($term, $limit);
        $this->sma->send_json($rows);
    }
	public function indexnew($warehouse_id = null)
    {
        $this->sma->checkPermissions();

        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        if ($this->Owner || $this->Admin || !$this->session->userdata('warehouse_id')) {
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['warehouse_id'] = $warehouse_id;
            $this->data['warehouse'] = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : null;
        } else {
            $this->data['warehouses'] = null;
            $this->data['warehouse_id'] = $this->session->userdata('warehouse_id');
            $this->data['warehouse'] = $this->session->userdata('warehouse_id') ? $this->site->getWarehouseByID($this->session->userdata('warehouse_id')) : null;
        }

        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('Khuyến mãi kèm sản phẩm')));
        $meta = array('page_title' => lang('Khuyến mãi kèm sản phẩm'), 'bc' => $bc);
        $this->page_construct('khuyenmai/indexnew', $meta, $this->data);

    }

    public function getKhuyenmainews($warehouse_id = null)
    {
        $this->sma->checkPermissions('index');

        if ((!$this->Owner || !$this->Admin) && !$warehouse_id) {
            $user = $this->site->getUser();
            $warehouse_id = $user->warehouse_id;
        }
        $detail_link = anchor('khuyenmai/view/$1', '<i class="fa fa-file-text-o"></i> ' . lang('quote_details'));
        $email_link = anchor('khuyenmai/email/$1', '<i class="fa fa-envelope"></i> ' . lang('email_quote'), 'data-toggle="modal" data-target="#myModal"');
        $edit_link = anchor('khuyenmai/editnew/$1', '<i class="fa fa-edit"></i> ' . lang('khuyenmai_update_text'));      
        
        
        $delete_link = "<a href='#' class='po' title='<b>" . $this->lang->line("Delete promotion") . "</b>' data-content=\"<p>"
        . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('khuyenmai/delete/$1') . "'>"
        . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
        . lang('Delete promotion') . "</a>";
        $action = '<div class="text-center"><div class="btn-group text-left">'
        . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
        . lang('actions') . ' <span class="caret"></span></button>
                    <ul class="dropdown-menu pull-right" role="menu">                       
                        <li>' . $edit_link . '</li>                       
                        <li>' . $delete_link . '</li>
                    </ul>
                </div></div>';

        $this->load->library('datatables');
        if ($warehouse_id) {
            $this->datatables
                ->select("id, date, tenevent, startdate,enddate,(select name from scodeweb_products where id=main_product_id) as product,main_quantity,attachment")
                ->from('khuyenmai')
				->where('type', 1)
                ->where('warehouse_id', $warehouse_id);
        } else {
            $this->datatables
                ->select("id, date, tenevent,startdate,enddate,(select name from scodeweb_products where id=main_product_id) as product,main_quantity, attachment")
                ->from('khuyenmai')->where('type', 1);
        }
        $this->datatables->add_column("Actions", $action, "id");
        echo $this->datatables->generate();
    }

    public function modal_viewnew($quote_id = null)
    {
        $this->sma->checkPermissions('index', true);

        if ($this->input->get('id')) {
            $quote_id = $this->input->get('id');
        }
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $inv = $this->khuyenmai_model->getQuoteByID($quote_id);
        if (!$this->session->userdata('view_right')) {
            $this->sma->view_rights($inv->created_by, true);
        }
		$productname=$this->cgetProductByID($inv->main_product_id);
        $this->data['rows'] = $this->khuyenmai_model->getAllQuoteItems($quote_id);
        $this->data['productname'] =$productname;
		$this->data['tenevent'] =$inv->tenevent;
        $this->data['startdate'] =$inv->startdate;
		$this->data['enddate'] =$inv->enddate;
        $this->data['created_by'] = $this->site->getUser($inv->created_by);
        $this->data['updated_by'] = $inv->updated_by ? $this->site->getUser($inv->updated_by) : null;
        $this->data['warehouse'] = $this->site->getWarehouseByID($inv->warehouse_id);
        $this->data['inv'] = $inv;

        $this->load->view($this->theme . 'khuyenmai/modal_viewnew', $this->data);

    }
	function cgetProductByID($id=0){
		if($id==0){
			$id = $this->input->post('id');
		}
		 $product = $this->site->getProductByID($id);
		 return $product->name." (".$product->code.")";
	}
	
}
