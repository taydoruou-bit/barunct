<?php defined('BASEPATH') or exit('No direct script access allowed');

class Returns extends MY_Controller
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
        $this->lang->load('returns', $this->Settings->user_language);
        $this->load->library('form_validation');
        $this->load->model('returns_model');
		$this->load->model('doitac_model');		
		$this->load->model('reports_model');		
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

        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' =>'Danh sách thu hồi sản phẩm'));
        $meta = array('page_title' => 'Danh sách thu hồi sản phẩm', 'bc' => $bc);
        $this->page_construct('returns/index', $meta, $this->data);
    }

    public function getReturns($warehouse_id = null)
    {
        $this->sma->checkPermissions('index');

        if ((!$this->Owner || !$this->Admin) && !$warehouse_id) {
            $user = $this->site->getUser();
            $warehouse_id = $user->warehouse_id;
        }

        $this->load->library('datatables');
        if ($warehouse_id) {
            $this->datatables
                ->select("{$this->db->dbprefix('returns')}.id as id, DATE_FORMAT({$this->db->dbprefix('returns')}.date, '%Y-%m-%d %T') as date, reference_no,(select name from scodeweb_warehouses where id=warehouse_id) as kho,(select name from scodeweb_doitac where id=doitac) as doitac, biller, {$this->db->dbprefix('returns')}.customer, grand_total, {$this->db->dbprefix('returns')}.attachment")
                ->from('returns')
                ->where('warehouse_id', $warehouse_id);
        } else {
            $this->datatables
                ->select("{$this->db->dbprefix('returns')}.id as id, DATE_FORMAT({$this->db->dbprefix('returns')}.date, '%Y-%m-%d %T') as date, reference_no,(select name from scodeweb_warehouses where id=warehouse_id) as kho,(select name from scodeweb_doitac where id=doitac) as doitac, biller, {$this->db->dbprefix('returns')}.customer, grand_total, {$this->db->dbprefix('returns')}.attachment")
                ->from('returns');
        }

        if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->datatables->where('created_by', $this->session->userdata('user_id'));
        }
		$print_link = anchor('returns/printhoadon/$1', '<i class="fa fa-print"></i> ', 'data-toggle="modal" data-target="#myModal" title="In hóa đơn"');
		
		$detail_link = anchor('returns/view/$1', '<i class="fa fa-file-text-o"></i> ','data-toggle="modal" data-target="#myModal" title="Xem chi tiết"');
		
        $this->datatables->add_column("Actions", "<div class=\"text-center\"><a href='" . site_url('returns/edit/$1') . "' class='tip' title='" . lang("edit_return") . "'><i class=\"fa fa-edit\"></i></a> ".$detail_link.$print_link."<a href='#' class='tip po' title='<b>" . lang("delete_return") . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('returns/delete_ajax/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></div>", "id");
        echo $this->datatables->generate();
    }
	public function getReturnsByProduct($warehouse_id = null)
    {
        $this->sma->checkPermissions('index');
        
		if ($this->input->get('product')) {
            $product = $this->input->get('product');
        }

        $this->load->library('datatables');
        
		$this->datatables
			->select("{$this->db->dbprefix('returns')}.id as id, DATE_FORMAT({$this->db->dbprefix('returns')}.date, '%Y-%m-%d %T') as date, reference_no, biller,GROUP_CONCAT(CONCAT(CONCAT(" . $this->db->dbprefix('return_items') . ".product_name, ' (', " . $this->db->dbprefix('return_items') . ".unit_quantity, ')'),' ',product_unit_code) SEPARATOR '\n') as iname, {$this->db->dbprefix('returns')}.customer, grand_total, {$this->db->dbprefix('returns')}.attachment")
			->from('returns')
			->join('return_items', 'return_items.return_id=returns.id', 'left')			
			->group_by('returns.id');
			if($product>0){
				$this->db->where('return_items.product_id', $product);
			}
			$this->db->order_by("returns.date", "desc");
		//echo $this->db->get_compiled_select();				
        echo $this->datatables->generate();
    }

    public function view($id = null)
    {
        $this->sma->checkPermissions('index', true);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $inv = $this->returns_model->getReturnByID($id);
        if (!$this->session->userdata('view_right')) {
            $this->sma->view_rights($inv->created_by, true);
        }
        $this->data['customer'] = $this->site->getCompanyByID($inv->customer_id);
        $this->data['biller'] = $this->site->getCompanyByID($inv->biller_id);
        $this->data['created_by'] = $this->site->getUser($inv->created_by);
        $this->data['updated_by'] = $inv->updated_by ? $this->site->getUser($inv->updated_by) : null;
        $this->data['warehouse'] = $this->site->getWarehouseByID($inv->warehouse_id);
        $this->data['inv'] = $inv;
        $this->data['rows'] = $this->returns_model->getReturnItems($id);

        $this->load->view($this->theme . 'returns/view', $this->data);
    }

    public function add()
    {
        $this->sma->checkPermissions();
        $this->form_validation->set_message('is_natural_no_zero', lang("no_zero_required"));
        //$this->form_validation->set_rules('customer', lang("customer"), 'required');
        $this->form_validation->set_rules('biller', lang("biller"), 'required');

        if ($this->form_validation->run() == true) {
            $date = ($this->Owner || $this->Admin) ? $this->sma->fld(trim($this->input->post('date'))) : date('Y-m-d H:i:s');
			if($date=='00/00/0000 00:00:00'){
				$date=date('Y-m-d H:i:s');
			}
            $reference = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('re');
            $warehouse_id = $this->input->post('warehouse');
            $customer_id = $this->input->post('customer');
            $biller_id = $this->input->post('biller');
            $total_items = $this->input->post('total_items');
			$doitac = $this->input->post('doitac');
			$customer="Khách lẻ";
			if($customer_id>0){
				$customer_details = $this->site->getCompanyByID($customer_id);
				$customer = !empty($customer_details->company) && $customer_details->company != '-' ? $customer_details->company : $customer_details->name;
			}
			
			$biller_details = $this->site->getCompanyByID($biller_id);
            $biller = !empty($biller_details->company) && $biller_details->company != '-' ? $biller_details->company : $biller_details->name;
            $note = $this->sma->clear_tags($this->input->post('note'));
            $staff_note = $this->sma->clear_tags($this->input->post('staff_note'));

            $total = 0;
            $product_tax = 0;
            $product_discount = 0;
            $gst_data = [];
            $total_cgst = $total_sgst = $total_igst = 0;
            $i = isset($_POST['product_code']) ? sizeof($_POST['product_code']) : 0;
			for ($m = 0; $m < $i; $m++) {
                $item_id = $_POST['product_id'][$m];
				//kiem tra tong thu hoi so voi tong sl ban ra
				$item_unit_quantity = $_POST['quantity'][$m];
				$tongthuhoi=(float)$this->site->getTongthuhoi($item_id,$warehouse_id)+$item_unit_quantity;
				
				$tongbanra=(float)$this->site->getTongSoluongBanra($item_id,$warehouse_id);
				
				if($tongbanra<=0){
					$this->session->set_userdata('remove_rels', 1);
					$this->session->set_flashdata('error','Lỗi: Sản phẩm ['.$_POST['product_name'][$m].'] bán ra ['.$tongbanra.'] thu hồi ['.$tongthuhoi.']');
					redirect("returns");
				}				
			}	
            for ($r = 0; $r < $i; $r++) {
                $item_id = $_POST['product_id'][$r];
												
                $item_type = $_POST['product_type'][$r];
                $item_code = $_POST['product_code'][$r];
                $item_name = $_POST['product_name'][$r];
                $item_option = isset($_POST['product_option'][$r]) && $_POST['product_option'][$r] != 'false' && $_POST['product_option'][$r] != 'null' ? $_POST['product_option'][$r] : null;
                $real_unit_price = $this->sma->formatDecimal($_POST['real_unit_price'][$r]);
                $unit_price = $this->sma->formatDecimal($_POST['unit_price'][$r]);
                $item_unit_quantity = $_POST['quantity'][$r];
                $item_serial = isset($_POST['serial'][$r]) ? $_POST['serial'][$r] : '';
                $item_tax_rate = isset($_POST['product_tax'][$r]) ? $_POST['product_tax'][$r] : null;
                $item_discount = isset($_POST['product_discount'][$r]) ? $_POST['product_discount'][$r] : null;
                $item_unit = $_POST['product_unit'][$r];
                $item_quantity = $_POST['product_base_quantity'][$r];

                if (isset($item_code) && isset($real_unit_price) && isset($unit_price) && isset($item_quantity)) {
                    $product_details = $item_type != 'manual' ? $this->site->getProductByCode($item_code) : null;
                    $pr_discount = $this->site->calculateDiscount($item_discount, $unit_price);
                    $unit_price = $this->sma->formatDecimal($unit_price - $pr_discount);
                    $item_net_price = $unit_price;
                    $pr_item_discount = $this->sma->formatDecimal($pr_discount * $item_unit_quantity);
                    $product_discount += $pr_item_discount;
                    $pr_item_tax = $item_tax = 0;
                    $tax = "";

                    if (isset($item_tax_rate) && $item_tax_rate != 0) {

                        $tax_details = $this->site->getTaxRateByID($item_tax_rate);
                        $ctax = $this->site->calculateTax($product_details, $tax_details, $unit_price);
                        $item_tax = $ctax['amount'];
                        $tax = $ctax['tax'];
                        if (!$product_details || (!empty($product_details) && $product_details->tax_method != 1)) {
                            $item_net_price = $unit_price - $item_tax;
                        }
                        $pr_item_tax = $this->sma->formatDecimal(($item_tax * $item_unit_quantity), 4);
                        if ($this->Settings->indian_gst && $gst_data = $this->gst->calculteIndianGST($pr_item_tax, ($biller_details->state == $customer_details->state), $tax_details)) {
                            $total_cgst += $gst_data['cgst'];
                            $total_sgst += $gst_data['sgst'];
                            $total_igst += $gst_data['igst'];
                        }
                    }

                    $product_tax += $pr_item_tax;
                    $subtotal = (($item_net_price * $item_unit_quantity) + $pr_item_tax);
                    $unit = $this->site->getUnitByID($item_unit);

                    $product = array(
                        'product_id' => $item_id,
                        'product_code' => $item_code,
                        'product_name' => $item_name,
                        'product_type' => $item_type,
                        'option_id' => $item_option,
                        'net_unit_price' => $item_net_price,
                        'unit_price' => $this->sma->formatDecimal($item_net_price + $item_tax),
                        'quantity' => $item_quantity,
                        'product_unit_id' => $unit ? $unit->id : null,
                        'product_unit_code' => $unit ? $unit->code : null,
                        'unit_quantity' => $item_unit_quantity,
                        'warehouse_id' => $warehouse_id,
                        'item_tax' => $pr_item_tax,
                        'tax_rate_id' => $item_tax_rate,
                        'tax' => $tax,
                        'discount' => $item_discount,
                        'item_discount' => $pr_item_discount,
                        'subtotal' => $this->sma->formatDecimal($subtotal),
                        'serial_no' => $item_serial,
                        'real_unit_price' => $real_unit_price,
                    );				
					$lhson_return[] = array(
						'product_id' => $item_id,
						'option_id' => $item_option,
						'quantity' => $item_quantity,
						'warehouse_id' => $warehouse_id,
						);	
                    $products[] = ($product + $gst_data);
                    $total += $this->sma->formatDecimal(($item_net_price * $item_unit_quantity), 4);
                }
            }
            if (empty($products)) {
                $this->form_validation->set_rules('product', lang("order_items"), 'required');
            } else {
                krsort($products);
            }

            $order_discount = $this->site->calculateDiscount($this->input->post('order_discount'), ($total + $product_tax));
            $total_discount = $this->sma->formatDecimal(($order_discount + $product_discount), 4);
            $order_tax = $this->site->calculateOrderTax($this->input->post('order_tax'), ($total + $product_tax - $order_discount));
            $total_tax = $this->sma->formatDecimal(($product_tax + $order_tax), 4);
            $grand_total = $this->sma->formatDecimal(($total + $total_tax - $order_discount), 4);
            $data = array('date' => $date,
                'reference_no' => $reference,
                'customer_id' => $customer_id,
                'customer' => $customer,
                'biller_id' => $biller_id,
                'biller' => $biller,
                'warehouse_id' => $warehouse_id,
                'note' => $note,
                'staff_note' => $staff_note,
				'doitac' => $doitac,
                'total' => $total,
                'product_discount' => $product_discount,
                'order_discount_id' => $this->input->post('order_discount'),
                'order_discount' => $order_discount,
                'total_discount' => $total_discount,
                'product_tax' => $product_tax,
                'order_tax_id' => $this->input->post('order_tax'),
                'order_tax' => $order_tax,
                'total_tax' => $total_tax,
                'grand_total' => $grand_total,
                'total_items' => $total_items,
                'paid' => $grand_total,
                'created_by' => $this->session->userdata('user_id'),
                'hash' => hash('sha256', microtime() . mt_rand()),
            );
								
				$pay_ref = $this->input->post('payment_reference_no') ? $this->input->post('payment_reference_no') : $this->site->getReference('pay');
				
				$payment = array(
					'date' => $date,
					'reference_no' => $pay_ref,
					'amount' => (0-$grand_total),
					'paid_by' => 'cash',
					'cheque_no' => $this->input->post('cheque_no'),
					'cc_no' => $this->input->post('pcc_no'),
					'cc_holder' => $this->input->post('pcc_holder'),
					'cc_month' => $this->input->post('pcc_month'),
					'cc_year' => $this->input->post('pcc_year'),
					'cc_type' => $this->input->post('pcc_type'),
					'created_by' => $this->session->userdata('user_id'),
					'type' => 'returned',
                    'warehouse_id'=>$warehouse_id
				);
					
						
			//$data['payment_status'] = $grand_total == $this->input->post('amount-paid') ? 'paid' : 'partial';
				

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
        }

        if ($this->form_validation->run() == true && $this->returns_model->addReturn($data, $products,$payment,$lhson_return)) {
					
            $this->session->set_userdata('remove_rels', 1);
            $this->session->set_flashdata('message', lang("return_added"));
            redirect("returns");
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['billers'] = $this->site->getAllCompanies('biller');
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['tax_rates'] = $this->site->getAllTaxRates();
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('returns'), 'page' => lang('returns')), array('link' => '#', 'page' => lang('add_return')));
            $meta = array('page_title' => lang('add_return'), 'bc' => $bc);
            $this->page_construct('returns/add', $meta, $this->data);
        }
    }

    public function edit($id = null)
    {
        $this->sma->checkPermissions();
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $inv = $this->returns_model->getReturnByID($id);
        if (!$this->session->userdata('edit_right')) {
            $this->sma->view_rights($inv->created_by);
        }
        $this->form_validation->set_message('is_natural_no_zero', lang("no_zero_required"));
        $this->form_validation->set_rules('customer', lang("customer"), 'required');
        $this->form_validation->set_rules('biller', lang("biller"), 'required');

        if ($this->form_validation->run() == true) {
            $date = ($this->Owner || $this->Admin) ? $this->sma->fld(trim($this->input->post('date'))) : $inv->date;
			if($date=='00/00/0000 00:00:00'){
				$date=$this->sma->fld(date('Y-m-d H:i:s'));
			}
            $reference = $this->input->post('reference_no');
            $warehouse_id = $this->input->post('warehouse');
            $customer_id = $this->input->post('customer');
            $biller_id = $this->input->post('biller');
			$doitac = $this->input->post('doitac');
            $total_items = $this->input->post('total_items');
            $customer_details = $this->site->getCompanyByID($customer_id);
            $customer = !empty($customer_details->company) && $customer_details->company != '-' ? $customer_details->company : $customer_details->name;
            $biller_details = $this->site->getCompanyByID($biller_id);
            $biller = !empty($biller_details->company) && $biller_details->company != '-' ? $biller_details->company : $biller_details->name;
            $note = $this->sma->clear_tags($this->input->post('note'));
            $staff_note = $this->sma->clear_tags($this->input->post('staff_note'));

            $total = 0;
            $product_tax = 0;
            $product_discount = 0;
            $gst_data = [];
            $total_cgst = $total_sgst = $total_igst = 0;
            $i = isset($_POST['product_code']) ? sizeof($_POST['product_code']) : 0;
            for ($r = 0; $r < $i; $r++) {
                $item_id = $_POST['product_id'][$r];
                $item_type = $_POST['product_type'][$r];
                $item_code = $_POST['product_code'][$r];
                $item_name = $_POST['product_name'][$r];
                $item_option = isset($_POST['product_option'][$r]) && $_POST['product_option'][$r] != 'false' && $_POST['product_option'][$r] != 'null' ? $_POST['product_option'][$r] : null;
                $real_unit_price = $this->sma->formatDecimal($_POST['real_unit_price'][$r]);
                $unit_price = $this->sma->formatDecimal($_POST['unit_price'][$r]);
                $item_unit_quantity = $_POST['quantity'][$r];
                $item_serial = isset($_POST['serial'][$r]) ? $_POST['serial'][$r] : '';
                $item_tax_rate = isset($_POST['product_tax'][$r]) ? $_POST['product_tax'][$r] : null;
                $item_discount = isset($_POST['product_discount'][$r]) ? $_POST['product_discount'][$r] : null;
                $item_unit = $_POST['product_unit'][$r];
                $item_quantity = $_POST['product_base_quantity'][$r];

                if (isset($item_code) && isset($real_unit_price) && isset($unit_price) && isset($item_quantity)) {
                    $product_details = $item_type != 'manual' ? $this->site->getProductByCode($item_code) : null;
                    $pr_discount = $this->site->calculateDiscount($item_discount, $unit_price);
                    $unit_price = $this->sma->formatDecimal($unit_price - $pr_discount);
                    $item_net_price = $unit_price;
                    $pr_item_discount = $this->sma->formatDecimal($pr_discount * $item_unit_quantity);
                    $product_discount += $pr_item_discount;
                    $pr_item_tax = $item_tax = 0;
                    $tax = "";

                    if (isset($item_tax_rate) && $item_tax_rate != 0) {

                        $tax_details = $this->site->getTaxRateByID($item_tax_rate);
                        $ctax = $this->site->calculateTax($product_details, $tax_details, $unit_price);
                        $item_tax = $ctax['amount'];
                        $tax = $ctax['tax'];
                        if (!$product_details || (!empty($product_details) && $product_details->tax_method != 1)) {
                            $item_net_price = $unit_price - $item_tax;
                        }
                        $pr_item_tax = $this->sma->formatDecimal(($item_tax * $item_unit_quantity), 4);
                        if ($this->Settings->indian_gst && $gst_data = $this->gst->calculteIndianGST($pr_item_tax, ($biller_details->state == $customer_details->state), $tax_details)) {
                            $total_cgst += $gst_data['cgst'];
                            $total_sgst += $gst_data['sgst'];
                            $total_igst += $gst_data['igst'];
                        }
                    }

                    $product_tax += $pr_item_tax;
                    $subtotal = (($item_net_price * $item_unit_quantity) + $pr_item_tax);
                    $unit = $this->site->getUnitByID($item_unit);

                    $product = array(
                        'return_id' => $id,
                        'product_id' => $item_id,
                        'product_code' => $item_code,
                        'product_name' => $item_name,
                        'product_type' => $item_type,
                        'option_id' => $item_option,
                        'net_unit_price' => $item_net_price,
                        'unit_price' => $this->sma->formatDecimal($item_net_price + $item_tax),
                        'quantity' => $item_quantity,
                        'product_unit_id' => $unit ? $unit->id : null,
                        'product_unit_code' => $unit ? $unit->code : null,
                        'unit_quantity' => $item_unit_quantity,
                        'warehouse_id' => $warehouse_id,
                        'item_tax' => $pr_item_tax,
                        'tax_rate_id' => $item_tax_rate,
                        'tax' => $tax,
                        'discount' => $item_discount,
                        'item_discount' => $pr_item_discount,
                        'subtotal' => $this->sma->formatDecimal($subtotal),
                        'serial_no' => $item_serial,
                        'real_unit_price' => $real_unit_price,
                    );
					
					$lhson_return[] = array(
						'product_id' => $item_id,
						'option_id' => $item_option,
						'quantity' =>$item_quantity,
						'warehouse_id' => $warehouse_id,
						);	
						
                    $products[] = ($product + $gst_data);
                    $total += $this->sma->formatDecimal(($item_net_price * $item_unit_quantity), 4);
                }
            }
            if (empty($products)) {
                $this->form_validation->set_rules('product', lang("order_items"), 'required');
            } else {
                krsort($products);
            }

            $order_discount = $this->site->calculateDiscount($this->input->post('order_discount'), ($total + $product_tax));
            $total_discount = $this->sma->formatDecimal(($order_discount + $product_discount), 4);
            $order_tax = $this->site->calculateOrderTax($this->input->post('order_tax'), ($total + $product_tax - $order_discount));
            $total_tax = $this->sma->formatDecimal(($product_tax + $order_tax), 4);
            $grand_total = $this->sma->formatDecimal(($total + $total_tax - $order_discount), 4);
            $data = array('date' => $date,
                'reference_no' => $reference,
                'customer_id' => $customer_id,
                'customer' => $customer,
                'biller_id' => $biller_id,
                'biller' => $biller,
                'warehouse_id' => $warehouse_id,
                'note' => $note,
                'staff_note' => $staff_note,
				'doitac' => $doitac,
                'total' => $total,
                'product_discount' => $product_discount,
                'order_discount_id' => $this->input->post('order_discount'),
                'order_discount' => $order_discount,
                'total_discount' => $total_discount,
                'product_tax' => $product_tax,
                'order_tax_id' => $this->input->post('order_tax'),
                'order_tax' => $order_tax,
                'total_tax' => $total_tax,
                'grand_total' => $grand_total,
                'total_items' => $total_items,
                'updated_by' => $this->session->userdata('user_id'),
                'updated_at' => date('Y-m-d H:i:s'),
            );
				
			$pay_ref = $this->input->post('payment_reference_no') ? $this->input->post('payment_reference_no') : $this->site->getReference('pay');
				$payment = array(
					'date' => $date,
					'reference_no' => $pay_ref,
					'amount' => (0-$grand_total),
					'paid_by' => 'cash',
					'cheque_no' => $this->input->post('cheque_no'),
					'cc_no' => $this->input->post('pcc_no'),
					'cc_holder' => $this->input->post('pcc_holder'),
					'cc_month' => $this->input->post('pcc_month'),
					'cc_year' => $this->input->post('pcc_year'),
					'cc_type' => $this->input->post('pcc_type'),
					'created_by' => $this->session->userdata('user_id'),
					'type' => 'returned',
                    'warehouse_id'=>$warehouse_id
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
        }		
        if ($this->form_validation->run() == true && $this->returns_model->updateReturn($id, $data, $products,$payment,$lhson_return)) {
			
            $this->session->set_userdata('remove_rels', 1);
            $this->session->set_flashdata('message', lang("return_updated"));
			
            redirect("returns");
        } else {
            $this->data['inv'] = $inv;
            if ($this->Settings->disable_editing) {
                if ($this->data['inv']->date <= date('Y-m-d', strtotime('-'.$this->Settings->disable_editing.' days'))) {
                    $this->session->set_flashdata('error', sprintf(lang("return_x_edited_older_than_x_days"), $this->Settings->disable_editing));
                    redirect($_SERVER["HTTP_REFERER"]);
                }
            }
            $inv_items = $this->returns_model->getReturnItems($id);
            $c = rand(100000, 9999999);
            foreach ($inv_items as $item) {
                $row = $this->site->getProductByID($item->product_id);
                if (!$row) {
                    $row = json_decode('{}');
                    $row->tax_method = 0;
                    $row->quantity = 0;
                } else {
                    unset($row->cost, $row->details, $row->product_details, $row->image, $row->barcode_symbology, $row->cf1, $row->cf2, $row->cf3, $row->cf4, $row->cf5, $row->cf6, $row->supplier1price, $row->supplier2price, $row->cfsupplier3price, $row->supplier4price, $row->supplier5price, $row->supplier1, $row->supplier2, $row->supplier3, $row->supplier4, $row->supplier5, $row->supplier1_part_no, $row->supplier2_part_no, $row->supplier3_part_no, $row->supplier4_part_no, $row->supplier5_part_no);
                }

                $row->id = $item->product_id;
                $row->code = $item->product_code;
                $row->name = $item->product_name;
                $row->type = $item->product_type;
                $row->item_tax_method = $row->tax_method;
                $options = $this->returns_model->getProductOptions($row->id);
                if ($options) {
                    $opt = $option_id && $r == 0 ? $this->returns_model->getProductOptionByID($option_id) : current($options);
                    if (!$option_id || $r > 0) {
                        $option_id = $opt->id;
                    }
                } else {
                    $opt = json_decode('{}');
                    $opt->price = 0;
                    $option_id = false;
                }
                if ($row->promotion) {
                    $row->price = $row->promo_price;
                }
                $row->real_unit_price = $row->price;
                $row->base_quantity = $item->quantity;
                $row->base_unit = !empty($row->unit) ? $row->unit : $item->product_unit_id;
                $row->base_unit_price = !empty($row->price) ? $row->price : $item->unit_price;
                $row->unit = $item->product_unit_id;
                $row->qty = $item->unit_quantity;
                $row->discount = $item->discount ? $item->discount : '0';
                $row->serial = $item->serial_no;
                $row->option = $item->option_id;
                $row->tax_rate = $item->tax_rate_id;
                $row->comment = '';
				$row->price = $this->sma->formatDecimal($item->net_unit_price + $this->sma->formatDecimal($item->item_discount / $item->quantity));
				$row->unit_price = $row->tax_method ? $item->unit_price + $this->sma->formatDecimal($item->item_discount / $item->quantity) + $this->sma->formatDecimal($item->item_tax / $item->quantity) : $item->unit_price + ($item->item_discount / $item->quantity);
				$row->real_unit_price = $item->real_unit_price;
				
                $combo_items = false;
                if ($row->type == 'combo') {
                    $combo_items = $this->site->getProductComboItems($row->id);
                }
                $units = $this->site->getUnitsByBUID($row->base_unit);
                $tax_rate = $this->site->getTaxRateByID($row->tax_rate);
                $ri = $this->Settings->item_addition ? $row->id : $c;

                $pr[$ri] = array('id' => $c, 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")",
                    'row' => $row, 'combo_items' => $combo_items, 'tax_rate' => $tax_rate, 'units' => $units, 'options' => $options);
                $c++;
            }
            $this->data['inv_items'] = json_encode($pr);
            $this->data['id'] = $id;
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['billers'] = $this->site->getAllCompanies('biller');
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['tax_rates'] = $this->site->getAllTaxRates();
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('returns'), 'page' => lang('returns')), array('link' => '#', 'page' => lang('edit_return')));
            $meta = array('page_title' => lang('edit_return'), 'bc' => $bc);
            $this->page_construct('returns/edit', $meta, $this->data);
        }
    }

    public function delete($id = null)
    {
        $this->sma->checkPermissions(null, true);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        if ($this->returns_model->deleteReturn($id)) {
            if ($this->input->is_ajax_request()) {
                $this->sma->send_json(array('error' => 0, 'msg' => lang("return_deleted")));
            }
            $this->session->set_flashdata('message', lang('return_deleted'));
            redirect('welcome');
        }
    }
	public function return_actions()
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
						$this->returns_model->deleteReturn($id);
					}
					$this->session->set_flashdata('message', lang("return_deleted"));
					redirect($_SERVER["HTTP_REFERER"]);
				}elseif ($this->input->post('form_action') == 'export_excel' || $this->input->post('form_action') == 'export_pdf') {

					$this->load->library('excel');
					$this->excel->setActiveSheetIndex(0);
					$this->excel->getActiveSheet()->setTitle(lang('sales'));
					$this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
					$this->excel->getActiveSheet()->SetCellValue('B1', lang('reference_no'));
					$this->excel->getActiveSheet()->SetCellValue('C1', lang('Kho'));
					$this->excel->getActiveSheet()->SetCellValue('D1', lang('ĐVGH'));
					$this->excel->getActiveSheet()->SetCellValue('E1', lang('biller'));
					$this->excel->getActiveSheet()->SetCellValue('F1', lang('customer'));
					$this->excel->getActiveSheet()->SetCellValue('G1', lang('grand_total'));

					$row = 2;
					foreach ($_POST['val'] as $id) {
						$res = $this->returns_model->getReturnByID($id);
						$_dvgh= $this->doitac_model->getDoiTacByID($res->doitac); 
						$dvgh=$_dvgh->name!=""?$_dvgh->name:"";
						
						
						$warehouse=$this->site->getWarehouseByID($res->warehouse_id);	
						$customer= $this->site->getCompanyByID($res->customer_id); 
						$_customer=$customer->phone."-".$customer->name;
						$this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->sma->hrld($res->date));
						$this->excel->getActiveSheet()->SetCellValue('B' . $row, $res->reference_no);
						$this->excel->getActiveSheet()->SetCellValue('C' . $row, $warehouse->name);
						$this->excel->getActiveSheet()->SetCellValue('D' . $row, $dvgh);
						$this->excel->getActiveSheet()->SetCellValue('E' . $row, $res->biller);
						$this->excel->getActiveSheet()->SetCellValue('F' . $row, $_customer);
						$this->excel->getActiveSheet()->SetCellValue('G' . $row, $res->grand_total);
						$row++;
					}

					$this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
					$this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
					$filename = 'return_' . date('Y_m_d_H_i_s');
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
			}
			 else {
				$this->session->set_flashdata('error', lang("return_deleted"));
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
		$rsdel=$this->returns_model->deleteReturn($id);
        if ($rsdel) {
            $this->sma->send_json(array('error' => 0, 'msg' => lang("return_deleted")));
        }else{
			$this->sma->send_json(array('error' => 0, 'msg' => 'Lỗi khi xóa thu hồi'));
		}
    }

    public function suggestions()
    {
        $term = $this->input->get('term', true);

        if (strlen($term) < 1 || !$term) {
            die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . site_url('welcome') . "'; }, 10);</script>");
        }

        $analyzed = $this->sma->analyze_term($term);
        $sr = $analyzed['term'];
        $option_id = $analyzed['option_id'];

        $rows = $this->returns_model->getProductNames($sr);
        if ($rows) {
            $c = str_replace(".", "", microtime(true));
            $r = 0;
            foreach ($rows as $row) {
                $option = false;
                unset($row->cost, $row->details, $row->product_details, $row->image, $row->barcode_symbology, $row->cf1, $row->cf2, $row->cf3, $row->cf4, $row->cf5, $row->cf6, $row->supplier1price, $row->supplier2price, $row->cfsupplier3price, $row->supplier4price, $row->supplier5price, $row->supplier1, $row->supplier2, $row->supplier3, $row->supplier4, $row->supplier5, $row->supplier1_part_no, $row->supplier2_part_no, $row->supplier3_part_no, $row->supplier4_part_no, $row->supplier5_part_no);
                $row->item_tax_method = $row->tax_method;
                $options = $this->returns_model->getProductOptions($row->id);
                if ($options) {
                    $opt = $option_id && $r == 0 ? $this->returns_model->getProductOptionByID($option_id) : current($options);
                    if (!$option_id || $r > 0) {
                        $option_id = $opt->id;
                    }
                } else {
                    $opt = json_decode('{}');
                    $opt->price = 0;
                    $option_id = false;
                }
                $row->option = $option_id;
                if ($row->promotion) {
                    $row->price = $row->promo_price;
                }
                $row->real_unit_price = $row->price;
                $row->base_quantity = 1;
                $row->base_unit = $row->unit;
                $row->unit = $row->sale_unit ? $row->sale_unit : $row->unit;
                $row->qty = 1;
                $row->discount = '0';
                $row->serial = '';
                $row->comment = '';
                $combo_items = false;
                if ($row->type == 'combo') {
                    $combo_items = $this->site->getProductComboItems($row->id);
                }
                $units = $this->site->getUnitsByBUID($row->base_unit);
                $tax_rate = $this->site->getTaxRateByID($row->tax_rate);

                $pr[] = array('id' => ($c + $r), 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")",
                    'row' => $row, 'tax_rate' => $tax_rate, 'units' => $units, 'options' => $options);
                $r++;
            }
            $this->sma->send_json($pr);
        } else {
            $this->sma->send_json(array(array('id' => 0, 'label' => lang('no_match_found'), 'value' => $term)));
        }
    }
	public function printhoadon($id = null)
    {
        $this->sma->checkPermissions('index', true);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $inv = $this->returns_model->getReturnByID($id);
        if (!$this->session->userdata('view_right')) {
            $this->sma->view_rights($inv->created_by, true);
        }
        $this->data['customer'] = $customer=$this->site->getCompanyByID($inv->customer_id);
        $this->data['biller'] = $biller=$this->site->getCompanyByID($inv->biller_id);
        $this->data['created_by'] = $created_by=$this->site->getUser($inv->created_by);
        $this->data['updated_by'] = $updated_by=$inv->updated_by ? $this->site->getUser($inv->updated_by) : null;
        $this->data['warehouse'] = $warehouse=$this->site->getWarehouseByID($inv->warehouse_id);
        $this->data['inv'] = $inv;
		$this->data['doitac'] = $doitac=$this->doitac_model->getDoitacByID($inv->doitac);
		
        $this->data['rows'] =$rows= $this->returns_model->getReturnItems($id);
		
		$_tablhd='<table border="1" style="width:100%;border-collapse:collapse;">        <tbody>        <tr>            <td style="text-align:center;width:5%;"><strong>STT</strong>            </td>            <td style="width:51%;padding-left:0.5%;"><strong>Tên hàng hóa</strong><br>            </td>            <td style="text-align:right;padding-right:0.5%;width:12%;"><strong>Đơn giá</strong><br>            </td>            <td style="text-align:center;width:15%;"><strong>SL</strong><br>            </td>            <td style="text-align:right;padding-right:0.5%;width:15.5%;"><strong>Thành tiền</strong><br>            </td>        </tr>';
		
		$r = 1;
		foreach ($rows as $row){
			$_prod_detail=$row->product_code.' - '.$row->product_name . ($row->variant ? ' (' . $row->variant . ')' : ''); 
			$_tablhd.='<tr>
				<td style="text-align:center; width:40px; vertical-align:middle;">'.$r.'</td>
				<td style="vertical-align:middle;">'.$_prod_detail.'</td>
				<td style="width: 80px; text-align:center; vertical-align:middle;">'.$this->sma->formatQuantity($row->unit_quantity).' '.$row->product_unit_code.'</td>
				<td style="text-align:right; width:100px;">'.$this->sma->formatMoney($row->unit_price).'</td>';
				$_bs='';
				if ($Settings->tax1 && $inv->product_tax > 0) {
					$_bs.='Thuế:'.($row->item_tax != 0 ? '<small>('.$row->tax_code.')</small>' : '') . ' ' . $this->sma->formatMoney($row->item_tax);
				}
				if ($Settings->product_discount && $inv->product_discount != 0) {
					$_bs.='Giảm:'. ($row->discount != 0 ? '<small>(' . $row->discount . ')</small> ' : '') . $this->sma->formatMoney($row->item_discount);
				}
				$_bs=$_bs!=""?"<br>".$_bs:"";
				
				$_tablhd.='<td style="text-align:right; width:120px;">'.$this->sma->formatMoney($row->subtotal).$_bs.'</td></tr>';
			
			
			$r++;
		}
		$_tablhd.='</table>';   
		$_total=0;
		$_chieckhau=0;
		if ($inv->grand_total != $inv->total) {		
				
				if ($Settings->product_discount && $inv->product_discount != 0) {
					$_chieckhau=$this->sma->formatMoney($inv->product_discount);
				}
				$_total=$this->sma->formatMoney(($inv->total + $inv->product_tax));			
		 }
		$_surcharge=0;
		if ($inv->surcharge != 0) {
			$_surcharge=$this->sma->formatMoney($inv->surcharge);
		}

		$_giamkhac=0;
		if ($inv->order_discount != 0) {
			$_giamkhac=$this->sma->formatMoney($inv->order_discount);
		}
		$_thue=0;
		if ($Settings->tax2 && $inv->order_tax != 0) {
			$_thue=$this->sma->formatMoney($inv->order_tax);
		}
		$tongcong=$this->sma->formatMoney($inv->grand_total);
		$_tongcong_bang_chu=$inv->grand_total;
		$left_end=substr($_tongcong_bang_chu,strlen($_tongcong_bang_chu)-5,strlen($_tongcong_bang_chu));
		if($left_end=='.0000'){
			 $_tongcong_bang_chu=str_replace($left_end,"",$_tongcong_bang_chu);
		 }
		$_tongcong_bang_chu_text=$this->site->convert_number_to_words($_tongcong_bang_chu);
				
		$dathanhtoan=$this->sma->formatMoney($inv->paid); 
		$conlai=$this->sma->formatMoney($inv->grand_total-$inv->paid);
		
		$tonggiam=$this->sma->formatMoney($_chieckhau+$inv->order_discount);
		
        $_dc_cuahang=str_replace("<p>","",$warehouse->address);       
		$_dc_cuahang=str_replace("</p>","",$_dc_cuahang);
		$_tenkhach=$customer->name?$customer->name:"Khách lẻ";		
		$parse_data = array('Ma_Don_Hang' => $inv->reference_no,'Khach_Hang' => $_tenkhach,'Cong_ty' => $customer->company,'site_link' => base_url(),'site_name' => $this->Settings->site_name,'logo' => '<img src="' . base_url() . 'assets/uploads/logos/' . $biller->logo . '" alt="' . ($biller->company != '-' ? $biller->company : $biller->name) . '"/>','Ten_Kho' => $warehouse->name,'Dia_Chi_Kho' =>$_dc_cuahang,'SDT_Kho' =>$warehouse->phone,'Dia_chi_kh' => $customer->address,'Dien_thoai_kh' => $customer->phone,'Ngay_Thu' => $this->sma->hrld($inv->date),'Tong_Thanh_Toan' =>$tongcong,'Nhan_Vien_Thu_Ngan' =>$created_by->first_name . ' ' . $created_by->last_name,'Ghi_Chu' =>$this->sma->decode_html($inv->note),'Chua_Thanh_Toan' => $conlai,'Da_Thanh_Toan' => $dathanhtoan,'Giam_Gia_Tren_Hoa_Don' =>$tonggiam,'Tong_Tien_Hang' =>$tongcong,'Tong_Tien_Bang_Chu' =>$_tongcong_bang_chu_text,'Bang_Hoa_Don' =>$_tablhd,'Ma_Doi_Tac' => $doitac->code,'Ten_Doi_Tac' => $doitac->name,'Dia_Chi_Doi_Tac' => $doitac->diachi,'Email_Doi_Tac' => $doitac->email,'Dien_Thoai_Doi_Tac' => $doitac->dienthoai,'No_Dau_Ky_Doi_Tac' => $doitac->nodauky);    
		
		if (file_exists('./themes/' . $this->theme . '/views/print_khac/printreturn.html')) {          
			$sale_temp = file_get_contents('themes/' . $this->theme . '/views/print_khac/printreturn.html');            
		} else {             
			$sale_temp = file_get_contents('./themes/default/views/print_khac/printreturn.html');          
		}        
		$_get_active_pos=$this->settings_model->get_print_value($sale_temp);	
		$rs_ex_pos=explode(":",$_get_active_pos);
		
		$_sizein_page=isset($rs_ex_pos[0])&&$rs_ex_pos[0]!=":"?$rs_ex_pos[0]:"A5";
		$_chieuin_page=isset($rs_ex_pos[1])&&$rs_ex_pos[1]!=":"?$rs_ex_pos[1]:"Portrait";
		
		$this->data['item_print']=$this->Settings->item_print;
		$this->data['kich_thuoc']=$this->settings_model->define_size_in($_sizein_page,$_chieuin_page);	
		
		//replace value size print
		$sale_temp=$this->settings_model->define_print_replace($sale_temp);
			
		$message = $this->parser->parse_string($sale_temp, $parse_data,true);
		
		$this->data['note'] = array('noidung' =>$message);  
		$this->data['id'] = $inv->id;        
		$this->data['modal_js'] = $this->site->modal_js();      
		$this->load->view($this->theme . 'returns/print', $this->data); 
    }
	public function baocao($warehouse_id = null)
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
		$this->data['users'] = $this->reports_model->getStaff();
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' =>'Báo cáo thu hồi sản phẩm'));
        $meta = array('page_title' => 'Báo cáo thu hồi sản phẩm', 'bc' => $bc);
        $this->page_construct('returns/indexbaocao', $meta, $this->data);
    }

    public function getReturnsBaoCao($pdf = NULL, $xls = NULL)
    {
        $this->sma->checkPermissions('index');       
		
		$user = $this->input->get('user') ? $this->input->get('user') : NULL;
		$customer = $this->input->get('customer') ? $this->input->get('customer') : NULL;
        $warehouse_id = $this->input->get('warehouse') ? $this->input->get('warehouse') : NULL;
		$reference_no = $this->input->get('reference_no') ? $this->input->get('reference_no') : NULL;
			
        $start_date = $this->input->get('start_date') ? $this->input->get('start_date') : NULL;
        $end_date = $this->input->get('end_date') ? $this->input->get('end_date') : NULL;

        if ($start_date) {
			//lhson date
            $start_date = date("Y-m-d H:i:s", strtotime(str_replace('/', '-',$start_date)) );
            $end_date = date("Y-m-d H:i:s", strtotime(str_replace('/', '-',$end_date)) );
        }
        if ((!$this->Owner || !$this->Admin) && !$warehouse_id) {
            $warehouse_id = $user->warehouse_id;
        }
		$_where=" WHERE tbl.id>0";
		if ($pdf || $xls) {
			$this->load->library('datatables');						
			$where="";			
			if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
				$where.=" AND scodeweb_returns.created_by=".$this->session->userdata('user_id');
			}
            if ($reference_no) {
                $where.=" AND scodeweb_returns.reference_no LIKE '%".$reference_no."%'";
            }
            if ($warehouse_id) {
                $where.=" AND scodeweb_returns.warehouse_id=".$warehouse_id;
            }
			if ($customer) {
                $where.=" AND scodeweb_returns.customer_id=".$customer;
            }
            if ($user) {
                $where.=" AND scodeweb_returns.created_by=".$user;
            }
            if ($start_date) {
                $where.=" AND scodeweb_returns.date BETWEEN '" . $start_date . "' AND '" . $end_date . "'";
            }
			
			$_sql="SELECT DATE_FORMAT({$this->db->dbprefix('returns')}.date, '%Y-%m-%d %T') as date, reference_no,(select name from scodeweb_warehouses where id=warehouse_id) as kho,(select name from scodeweb_doitac where id=doitac) as doitac, (select concat(first_name,' ',last_name) FROM scodeweb_users WHERE id={$this->db->dbprefix('returns')}.created_by) as nhanvien, {$this->db->dbprefix('returns')}.customer, grand_total, {$this->db->dbprefix('returns')}.attachment,{$this->db->dbprefix('returns')}.id as id FROM scodeweb_returns WHERE {$this->db->dbprefix('returns')}.id>0 $where";
			$q=$this->db->query($_sql);
			
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
                $this->excel->getActiveSheet()->setTitle(lang('Báo cáo khách trả hàng'));
                $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                $this->excel->getActiveSheet()->SetCellValue('B1', lang('reference_no'));
                $this->excel->getActiveSheet()->SetCellValue('C1', lang('Nhân viên'));
				$this->excel->getActiveSheet()->SetCellValue('D1', lang('ĐVGH'));
				$this->excel->getActiveSheet()->SetCellValue('E1', lang('Khách hàng'));
				$this->excel->getActiveSheet()->SetCellValue('F1', lang('Kho'));
                $this->excel->getActiveSheet()->SetCellValue('G1', lang('Tổng cộng'));

                $row = 2; $total=0;
                foreach ($data as $data_row) {
															
                    $this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->sma->hrld($data_row->date));
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->reference_no);
                    $this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->nhanvien);
					$this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->doitac);
					$this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->customer);
					$this->excel->getActiveSheet()->SetCellValue('F' . $row, $data_row->kho);
                    $this->excel->getActiveSheet()->SetCellValue('G' . $row, $data_row->grand_total);
                    $total += $data_row->grand_total;
                    $row++;
                }
                $this->excel->getActiveSheet()->getStyle("G" . $row)->getBorders()
                    ->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);
				$this->excel->getActiveSheet()->SetCellValue('G' . $row, $total);

                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(20);

                $filename = 'BaoCao_Khach_Tra_Hang';
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
                    //$this->excel->getActiveSheet()->getStyle('C2:C' . $row)->getAlignment()->setWrapText(true);
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
		}else{
			$this->load->library('datatables');						
			$where="";			
			if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
				$where.=" AND scodeweb_returns.created_by=".$this->session->userdata('user_id');
			}
            if ($reference_no) {
                $where.=" AND scodeweb_returns.reference_no LIKE '%".$reference_no."%'";
            }
            if ($warehouse_id) {
                $where.=" AND scodeweb_returns.warehouse_id=".$warehouse_id;
            }
			if ($customer) {
                $where.=" AND scodeweb_returns.customer_id=".$customer;
            }
            if ($user) {
                $where.=" AND scodeweb_returns.created_by=".$user;
            }
            if ($start_date) {
                $where.=" AND scodeweb_returns.date BETWEEN '" . $start_date . "' AND '" . $end_date . "'";
            }
			
			$_sql="SELECT DATE_FORMAT({$this->db->dbprefix('returns')}.date, '%Y-%m-%d %T') as date, reference_no,(select name from scodeweb_warehouses where id=warehouse_id) as kho,(select name from scodeweb_doitac where id=doitac) as doitac, (select concat(first_name,' ',last_name) FROM scodeweb_users WHERE id={$this->db->dbprefix('returns')}.created_by) as nhanvien, {$this->db->dbprefix('returns')}.customer, grand_total, {$this->db->dbprefix('returns')}.attachment,{$this->db->dbprefix('returns')}.id as id FROM scodeweb_returns WHERE {$this->db->dbprefix('returns')}.id>0 $where";		
			
			echo $rs=$this->datatables->generate_querylhson($output = 'json', $charset = 'UTF-8',$_sql);
			
		}
    }	
	public function baocaochitiet($warehouse_id = null)
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
		$this->data['users'] = $this->reports_model->getStaff();
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' =>'Báo cáo thu hồi sản phẩm chi tiết'));
        $meta = array('page_title' => 'Báo cáo thu hồi sản phẩm chi tiết', 'bc' => $bc);
        $this->page_construct('returns/indexbaocaochitiet', $meta, $this->data);
    }

    public function getReturnsBaoCaoChiTiet($pdf = NULL, $xls = NULL)
    {
        $this->sma->checkPermissions('index');       
		
		$user = $this->input->get('user') ? $this->input->get('user') : NULL;
		$customer = $this->input->get('customer') ? $this->input->get('customer') : NULL;
        $warehouse_id = $this->input->get('warehouse') ? $this->input->get('warehouse') : NULL;
		$reference_no = $this->input->get('reference_no') ? $this->input->get('reference_no') : NULL;
			
        $start_date = $this->input->get('start_date') ? $this->input->get('start_date') : NULL;
        $end_date = $this->input->get('end_date') ? $this->input->get('end_date') : NULL;

        if ($start_date) {
			//lhson date
            $start_date = date("Y-m-d H:i:s", strtotime(str_replace('/', '-',$start_date)) );
            $end_date = date("Y-m-d H:i:s", strtotime(str_replace('/', '-',$end_date)) );
        }
        if ((!$this->Owner || !$this->Admin) && !$warehouse_id) {
            $warehouse_id = $user->warehouse_id;
        }
		$_where=" WHERE tbl.id>0";
		if ($pdf || $xls) {
			$this->load->library('datatables');						
			$where="";			
			if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
				$where.=" AND scodeweb_returns.created_by=".$this->session->userdata('user_id');
			}
            if ($reference_no) {
                $where.=" AND scodeweb_returns.reference_no LIKE '%".$reference_no."%'";
            }
            if ($warehouse_id) {
                $where.=" AND scodeweb_returns.warehouse_id=".$warehouse_id;
            }
			if ($customer) {
                $where.=" AND scodeweb_returns.customer_id=".$customer;
            }
            if ($user) {
                $where.=" AND scodeweb_returns.created_by=".$user;
            }
            if ($start_date) {
                $where.=" AND scodeweb_returns.date BETWEEN '" . $start_date . "' AND '" . $end_date . "'";
            }
			
			$_sql="SELECT DATE_FORMAT({$this->db->dbprefix('returns')}.date, '%Y-%m-%d %T') as date, reference_no,(select name from scodeweb_warehouses where id=scodeweb_returns.warehouse_id) as kho,(select name from scodeweb_doitac where id=doitac) as doitac, (select concat(first_name,' ',last_name) FROM scodeweb_users WHERE id={$this->db->dbprefix('returns')}.created_by) as nhanvien, {$this->db->dbprefix('returns')}.customer,scodeweb_return_items.product_name as sanpham, scodeweb_return_items.product_unit_code as dvt,scodeweb_return_items.unit_quantity as sl,scodeweb_return_items.unit_price as giaban, (scodeweb_return_items.unit_price*scodeweb_return_items.unit_quantity) as thanhtien,scodeweb_returns.total_discount as total_discount,scodeweb_returns.grand_total,{$this->db->dbprefix('returns')}.id as id FROM scodeweb_returns,scodeweb_return_items WHERE {$this->db->dbprefix('returns')}.id=scodeweb_return_items.return_id $where";
			
			$q=$this->db->query($_sql);
			
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
                $this->excel->getActiveSheet()->setTitle(lang('Báo cáo khách trả hàng'));
                $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                $this->excel->getActiveSheet()->SetCellValue('B1', lang('reference_no'));
                $this->excel->getActiveSheet()->SetCellValue('C1', lang('Nhân viên'));
				$this->excel->getActiveSheet()->SetCellValue('D1', lang('ĐVGH'));
				$this->excel->getActiveSheet()->SetCellValue('E1', lang('Khách hàng'));
				$this->excel->getActiveSheet()->SetCellValue('F1', lang('Kho'));
				$this->excel->getActiveSheet()->SetCellValue('G1', lang('Sản phẩm'));
				$this->excel->getActiveSheet()->SetCellValue('H1', lang('ĐVT'));
				$this->excel->getActiveSheet()->SetCellValue('I1', lang('Số lượng'));
				$this->excel->getActiveSheet()->SetCellValue('K1', lang('Giá bán'));
				$this->excel->getActiveSheet()->SetCellValue('L1', lang('Thành tiền'));
				$this->excel->getActiveSheet()->SetCellValue('M1', lang('Tổng Phụ Thu'));
                $this->excel->getActiveSheet()->SetCellValue('N1', lang('Tổng cộng'));

                $row = 2; $total=0; $sl=0; $tt=0; $giam=0;
                foreach ($data as $data_row) {
															
                    $this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->sma->hrld($data_row->date));
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->reference_no);
                    $this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->nhanvien);
					$this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->doitac);
					$this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->customer);
					$this->excel->getActiveSheet()->SetCellValue('F' . $row, $data_row->kho);
					$this->excel->getActiveSheet()->SetCellValue('G' . $row, $data_row->sanpham);
					$this->excel->getActiveSheet()->SetCellValue('H' . $row, $data_row->dvt);
					$this->excel->getActiveSheet()->SetCellValue('I' . $row, $data_row->sl);
					$this->excel->getActiveSheet()->SetCellValue('K' . $row, $data_row->giaban);
					$this->excel->getActiveSheet()->SetCellValue('L' . $row, $data_row->thanhtien);
					$this->excel->getActiveSheet()->SetCellValue('M' . $row, $data_row->total_discount);
                    $this->excel->getActiveSheet()->SetCellValue('N' . $row, $data_row->grand_total);
                    $total += $data_row->grand_total;
					$sl += $data_row->sl;
					$tt += $data_row->thanhtien;
					$giam += $data_row->total_discount;
                    $row++;
                }
                $this->excel->getActiveSheet()->getStyle("I" . $row . ":N" . $row)->getBorders()
                    ->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);
				$this->excel->getActiveSheet()->SetCellValue('I' . $row, $sl);
				$this->excel->getActiveSheet()->SetCellValue('L' . $row, $tt);
				$this->excel->getActiveSheet()->SetCellValue('M' . $row, $giam);
				$this->excel->getActiveSheet()->SetCellValue('N' . $row, $total);

                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(35);

                $filename = 'BaoCao_Khach_Tra_Hang_ChiTiet';
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
                    //$this->excel->getActiveSheet()->getStyle('C2:C' . $row)->getAlignment()->setWrapText(true);
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
		}else{
			$this->load->library('datatables');						
			$where="";			
			if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
				$where.=" AND scodeweb_returns.created_by=".$this->session->userdata('user_id');
			}
            if ($reference_no) {
                $where.=" AND scodeweb_returns.reference_no LIKE '%".$reference_no."%'";
            }
            if ($warehouse_id) {
                $where.=" AND scodeweb_returns.warehouse_id=".$warehouse_id;
            }
			if ($customer) {
                $where.=" AND scodeweb_returns.customer_id=".$customer;
            }
            if ($user) {
                $where.=" AND scodeweb_returns.created_by=".$user;
            }
            if ($start_date) {
                $where.=" AND scodeweb_returns.date BETWEEN '" . $start_date . "' AND '" . $end_date . "'";
            }
			
			$_sql="SELECT DATE_FORMAT({$this->db->dbprefix('returns')}.date, '%Y-%m-%d %T') as date, reference_no,(select name from scodeweb_warehouses where id=scodeweb_returns.warehouse_id) as kho,(select name from scodeweb_doitac where id=doitac) as doitac, (select concat(first_name,' ',last_name) FROM scodeweb_users WHERE id={$this->db->dbprefix('returns')}.created_by) as nhanvien, {$this->db->dbprefix('returns')}.customer,scodeweb_return_items.product_name as sanpham, scodeweb_return_items.product_unit_code as dvt,scodeweb_return_items.unit_quantity as sl,scodeweb_return_items.unit_price as giaban, (scodeweb_return_items.unit_price*scodeweb_return_items.unit_quantity) as thanhtien,scodeweb_returns.total_discount,scodeweb_returns.grand_total,{$this->db->dbprefix('returns')}.id as id FROM scodeweb_returns,scodeweb_return_items WHERE {$this->db->dbprefix('returns')}.id=scodeweb_return_items.return_id $where";		
			
			echo $rs=$this->datatables->generate_querylhson($output = 'json', $charset = 'UTF-8',$_sql);
			
		}
    }
}
