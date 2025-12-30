<?php defined('BASEPATH') or exit('No direct script access allowed');

class Purchases extends MY_Controller
{

    public function __construct()
    {
        parent::__construct();
        if (!$this->loggedIn) {
            $this->session->set_userdata('requested_page', $this->uri->uri_string());
            $this->sma->md('login');
        }
        if ($this->Customer) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER["HTTP_REFERER"]);
        }
        $this->lang->load('purchases', $this->Settings->user_language);
        $this->load->library('form_validation');
        $this->load->model('purchases_model');
		$this->load->model('settings_model');
		$this->load->model('reports_model');
		$this->load->model('doitac_model');
		$this->load->model('sales_model');	
		
		$this->load->model('companies_model');
        $this->digital_upload_path = 'files/';
        $this->upload_path = 'assets/uploads/';
        $this->thumbs_path = 'assets/uploads/thumbs/';
        $this->image_types = 'gif|jpg|jpeg|png|tif';
        $this->digital_file_types = 'zip|psd|ai|rar|pdf|doc|docx|xls|xlsx|ppt|pptx|gif|jpg|jpeg|png|tif|txt';
        $this->allowed_file_size = '1024';
        $this->data['logo'] = true;
		
		$this->data['pb'] = $this->site->getAllPTTT();

    }

    /* ------------------------------------------------------------------------- */

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

        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('purchases')));
        $meta = array('page_title' => lang('purchases'), 'bc' => $bc);
        $this->page_construct('purchases/index', $meta, $this->data);

    }

    public function getPurchases($warehouse_id = null)
    {
        $this->sma->checkPermissions('index');

        if ((!$this->Owner || !$this->Admin) && !$warehouse_id) {
            $user = $this->site->getUser();
            $warehouse_id = $user->warehouse_id;
        }
        $detail_link = anchor('purchases/view/$1', '<i class="fa fa-file-text-o"></i> ' . lang('purchase_details'));
        $payments_link = anchor('purchases/payments/$1', '<i class="fa fa-money"></i> ' . lang('view_payments'), 'data-toggle="modal" data-target="#myModal"');
        $add_payment_link = anchor('purchases/add_payment/$1', '<i class="fa fa-money"></i> ' . lang('add_payment'), 'data-toggle="modal" data-target="#myModal"');
        $email_link = anchor('purchases/email/$1', '<i class="fa fa-envelope"></i> ' . lang('email_purchase'), 'data-toggle="modal" data-target="#myModal"');
        $edit_link = anchor('purchases/edit/$1', '<i class="fa fa-edit"></i> ' . lang('edit_purchase'));
        $pdf_link = anchor('purchases/pdf/$1', '<i class="fa fa-file-pdf-o"></i> ' . lang('download_pdf'));
        $print_barcode = anchor('products/print_barcodes/?purchase=$1', '<i class="fa fa-print"></i> ' . lang('print_barcodes'));
        $return_link = anchor('purchases/return_purchase/$1', '<i class="fa fa-angle-double-left"></i> ' . lang('return_purchase'));
        $delete_link = "<a href='".site_url('purchases/delete/$1')."' class=''> <i class=\"fa fa-trash-o\"></i> Xóa </a>";
		$print_link = anchor('purchases/printnhap/$1', '<i class="fa fa-print"></i> '.lang('In hóa đơn'), 'data-toggle="modal" data-target="#myModal" title="In hóa đơn"');
        $action = '<div class="text-center"><div class="btn-group text-left">'
        . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
        . lang('actions') . ' <span class="caret"></span></button>
        <ul class="dropdown-menu pull-right" role="menu">
            <li>' . $detail_link . '</li>
            <li>' . $payments_link . '</li>
            <li>' . $add_payment_link . '</li>
            <li>' . $edit_link . '</li>
            <li>' . $pdf_link . '</li>
            <li>' . $email_link . '</li>
            <li>' . $print_barcode . '</li>
			<li>' . $print_link . '</li>
            <li>' . $return_link . '</li>
            <li>' . $delete_link . '</li>
        </ul>
    </div></div>';
        //$action = '<div class="text-center">' . $detail_link . ' ' . $edit_link . ' ' . $email_link . ' ' . $delete_link . '</div>';

        $this->load->library('datatables');
        if ($warehouse_id) {
            $this->datatables
                ->select("purchases.id as id, DATE_FORMAT(scodeweb_purchases.date, '%Y-%m-%d %T') as date, reference_no,warehouses.name as kho,doitac.name as dvgh,concat(scodeweb_users.first_name,' ',scodeweb_users.last_name) as nhanvien, supplier, status, grand_total, paid, (grand_total-paid) as balance, payment_status, purchases.attachment")
                ->from('purchases')
				->join('users', 'users.id=purchases.created_by', 'left')
				->join('doitac', 'doitac.id=purchases.doitac', 'left')
				->join('warehouses', 'warehouses.id=purchases.warehouse_id', 'left')
                ->where('purchases.warehouse_id', $warehouse_id);
        } else {
            $this->datatables
                ->select("purchases.id as id, DATE_FORMAT(scodeweb_purchases.date, '%Y-%m-%d %T') as date, reference_no,warehouses.name as kho,doitac.name as dvgh,concat(scodeweb_users.first_name,' ',scodeweb_users.last_name) as nhanvien, supplier, status, grand_total, paid, (grand_total-paid) as balance, payment_status, purchases.attachment")
                ->from('purchases')
				->join('users', 'users.id=purchases.created_by', 'left')
				->join('doitac', 'doitac.id=purchases.doitac', 'left')
				->join('warehouses', 'warehouses.id=purchases.warehouse_id', 'left');
        }
        $this->datatables->where('status !=', 'returned');
        if (!$this->Customer && !$this->Supplier && !$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->datatables->where('created_by', $this->session->userdata('user_id'));
        } elseif ($this->Supplier) {
            $this->datatables->where('supplier_id', $this->session->userdata('user_id'));
        }
        $this->datatables->add_column("Actions", $action, "id");
        echo $this->datatables->generate();
    }
	
	public function indextrahang($warehouse_id = null)
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

        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('Trả hàng NCC')));
        $meta = array('page_title' => lang('Trả hàng NCC'), 'bc' => $bc);
        $this->page_construct('purchases/indextrahang', $meta, $this->data);

    }

    public function getPurchasesTraHang($warehouse_id = null)
    {
        $this->sma->checkPermissions('index');

        if ((!$this->Owner || !$this->Admin) && !$warehouse_id) {
            $user = $this->site->getUser();
            $warehouse_id = $user->warehouse_id;
        }
        $detail_link = anchor('purchases/view/$1', '<i class="fa fa-file-text-o"></i> ' . lang('purchase_details'));
        $payments_link = anchor('purchases/payments/$1', '<i class="fa fa-money"></i> ' . lang('view_payments'), 'data-toggle="modal" data-target="#myModal"');
        $add_payment_link = anchor('purchases/add_payment/$1', '<i class="fa fa-money"></i> ' . lang('add_payment'), 'data-toggle="modal" data-target="#myModal"');
        $email_link = anchor('purchases/email/$1', '<i class="fa fa-envelope"></i> ' . lang('email_purchase'), 'data-toggle="modal" data-target="#myModal"');
        $edit_link = anchor('purchases/edit/$1', '<i class="fa fa-edit"></i> ' . lang('edit_purchase'));
        $pdf_link = anchor('purchases/pdf/$1', '<i class="fa fa-file-pdf-o"></i> ' . lang('download_pdf'));
        $print_barcode = anchor('products/print_barcodes/?purchase=$1', '<i class="fa fa-print"></i> ' . lang('print_barcodes'));
        $return_link = anchor('purchases/return_purchase/$1', '<i class="fa fa-angle-double-left"></i> ' . lang('return_purchase'));
        $delete_link = "<a href='#' class='po' title='<b>" . $this->lang->line("delete_purchase") . "</b>' data-content=\"<p>"
        . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('purchases/delete/$1') . "'>"
        . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
        . lang('delete_purchase') . "</a>";
		$print_link = anchor('purchases/printnhap/$1', '<i class="fa fa-print"></i> '.lang('In hóa đơn'), 'data-toggle="modal" data-target="#myModal" title="In hóa đơn"');
        $action = '<div class="text-center"><div class="btn-group text-left">'
        . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
        . lang('actions') . ' <span class="caret"></span></button>
        <ul class="dropdown-menu pull-right" role="menu">
            <li>' . $detail_link . '</li>
            <li>' . $payments_link . '</li>
            <li>' . $add_payment_link . '</li>
            <li>' . $edit_link . '</li>
            <li>' . $pdf_link . '</li>
            <li>' . $email_link . '</li>
            <li>' . $print_barcode . '</li>
			<li>' . $print_link . '</li>
            <li>' . $return_link . '</li>
            <li>' . $delete_link . '</li>
        </ul>
    </div></div>';
        //$action = '<div class="text-center">' . $detail_link . ' ' . $edit_link . ' ' . $email_link . ' ' . $delete_link . '</div>';

        $this->load->library('datatables');
        if ($warehouse_id) {
            $this->datatables
               ->select("purchases.id as id, DATE_FORMAT(scodeweb_purchases.date, '%Y-%m-%d %T') as date, reference_no,warehouses.name as kho,doitac.name as dvgh,concat(scodeweb_users.first_name,' ',scodeweb_users.last_name) as nhanvien, supplier, status, grand_total, paid, (grand_total-paid) as balance, payment_status, purchases.attachment")
                ->from('purchases')
				->join('users', 'users.id=purchases.created_by', 'left')
				->join('doitac', 'doitac.id=purchases.doitac', 'left')
				->join('warehouses', 'warehouses.id=purchases.warehouse_id', 'left')
                ->where('warehouse_id', $warehouse_id);
        } else {
            $this->datatables
                ->select("purchases.id as id, DATE_FORMAT(scodeweb_purchases.date, '%Y-%m-%d %T') as date, reference_no,warehouses.name as kho,doitac.name as dvgh,concat(scodeweb_users.first_name,' ',scodeweb_users.last_name) as nhanvien, supplier, status, grand_total, paid, (grand_total-paid) as balance, payment_status, purchases.attachment")
                ->from('purchases')
				->join('users', 'users.id=purchases.created_by', 'left')
				->join('doitac', 'doitac.id=purchases.doitac', 'left')
				->join('warehouses', 'warehouses.id=purchases.warehouse_id', 'left');
        }
        $this->datatables->where('status=', 'returned');
        if (!$this->Customer && !$this->Supplier && !$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->datatables->where('created_by', $this->session->userdata('user_id'));
        } elseif ($this->Supplier) {
            $this->datatables->where('supplier_id', $this->session->userdata('user_id'));
        }
        $this->datatables->add_column("Actions", $action, "id");
        echo $this->datatables->generate();
    }
    /* ----------------------------------------------------------------------------- */

    public function modal_view($purchase_id = null)
    {
        $this->sma->checkPermissions('index', true);

        if ($this->input->get('id')) {
            $purchase_id = $this->input->get('id');
        }
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $inv = $this->purchases_model->getPurchaseByID($purchase_id);
        if (!$this->session->userdata('view_right')) {
            $this->sma->view_rights($inv->created_by, true);
        }
        $this->data['rows'] = $this->purchases_model->getAllPurchaseItems($purchase_id);
        $this->data['supplier'] = $this->site->getCompanyByID($inv->supplier_id);
        $this->data['warehouse'] = $this->site->getWarehouseByID($inv->warehouse_id);
        $this->data['inv'] = $inv;
        $this->data['payments'] = $this->purchases_model->getPaymentsForPurchase($purchase_id);
        $this->data['created_by'] = $this->site->getUser($inv->created_by);
        $this->data['updated_by'] = $inv->updated_by ? $this->site->getUser($inv->updated_by) : null;
        $this->data['return_purchase'] = $inv->return_id ? $this->purchases_model->getPurchaseByID($inv->return_id) : NULL;
        $this->data['return_rows'] = $inv->return_id ? $this->purchases_model->getAllPurchaseItems($inv->return_id) : NULL;

        $this->load->view($this->theme . 'purchases/modal_view', $this->data);

    }

    public function view($purchase_id = null)
    {
        $this->sma->checkPermissions('index');

        if ($this->input->get('id')) {
            $purchase_id = $this->input->get('id');
        }
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $inv = $this->purchases_model->getPurchaseByID($purchase_id);
        if (!$this->session->userdata('view_right')) {
            $this->sma->view_rights($inv->created_by);
        }
        $this->data['rows'] = $this->purchases_model->getAllPurchaseItems($purchase_id);
        $this->data['supplier'] = $this->site->getCompanyByID($inv->supplier_id);
        $this->data['warehouse'] = $this->site->getWarehouseByID($inv->warehouse_id);
        $this->data['inv'] = $inv;
        $this->data['payments'] = $this->purchases_model->getPaymentsForPurchase($purchase_id);
        $this->data['created_by'] = $this->site->getUser($inv->created_by);
        $this->data['updated_by'] = $inv->updated_by ? $this->site->getUser($inv->updated_by) : null;
        $this->data['return_purchase'] = $inv->return_id ? $this->purchases_model->getPurchaseByID($inv->return_id) : NULL;
        $this->data['return_rows'] = $inv->return_id ? $this->purchases_model->getAllPurchaseItems($inv->return_id) : NULL;

        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('purchases'), 'page' => lang('purchases')), array('link' => '#', 'page' => lang('view')));
        $meta = array('page_title' => lang('view_purchase_details'), 'bc' => $bc);
        $this->page_construct('purchases/view', $meta, $this->data);

    }

    /* ----------------------------------------------------------------------------- */

//generate pdf and force to download

    public function pdf($purchase_id = null, $view = null, $save_bufffer = null)
    {
        $this->sma->checkPermissions();

        if ($this->input->get('id')) {
            $purchase_id = $this->input->get('id');
        }

        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $inv = $this->purchases_model->getPurchaseByID($purchase_id);
        if (!$this->session->userdata('view_right')) {
            $this->sma->view_rights($inv->created_by);
        }
        $this->data['rows'] = $this->purchases_model->getAllPurchaseItems($purchase_id);
        $this->data['supplier'] = $this->site->getCompanyByID($inv->supplier_id);
        $this->data['warehouse'] = $this->site->getWarehouseByID($inv->warehouse_id);
        $this->data['created_by'] = $this->site->getUser($inv->created_by);
        $this->data['inv'] = $inv;
        $this->data['return_purchase'] = $inv->return_id ? $this->purchases_model->getPurchaseByID($inv->return_id) : NULL;
        $this->data['return_rows'] = $inv->return_id ? $this->purchases_model->getAllPurchaseItems($inv->return_id) : NULL;
        $name = $this->lang->line("purchase") . "_" . str_replace('/', '_', $inv->reference_no) . ".pdf";
        $html = $this->load->view($this->theme . 'purchases/pdf', $this->data, true);
        if (! $this->Settings->barcode_img) {
            $html = preg_replace("'\<\?xml(.*)\?\>'", '', $html);
        }
        if ($view) {
            $this->load->view($this->theme . 'purchases/pdf', $this->data);
        } elseif ($save_bufffer) {
            return $this->sma->generate_pdf($html, $name, $save_bufffer);
        } else {
            $this->sma->generate_pdf($html, $name);
        }

    }

    public function combine_pdf($purchases_id)
    {
        $this->sma->checkPermissions('pdf');

        foreach ($purchases_id as $purchase_id) {

            $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
            $inv = $this->purchases_model->getPurchaseByID($purchase_id);
            if (!$this->session->userdata('view_right')) {
                $this->sma->view_rights($inv->created_by);
            }
            $this->data['rows'] = $this->purchases_model->getAllPurchaseItems($purchase_id);
            $this->data['supplier'] = $this->site->getCompanyByID($inv->supplier_id);
            $this->data['warehouse'] = $this->site->getWarehouseByID($inv->warehouse_id);
            $this->data['created_by'] = $this->site->getUser($inv->created_by);
            $this->data['inv'] = $inv;
            $this->data['return_purchase'] = $inv->return_id ? $this->purchases_model->getPurchaseByID($inv->return_id) : NULL;
            $this->data['return_rows'] = $inv->return_id ? $this->purchases_model->getAllPurchaseItems($inv->return_id) : NULL;
            $inv_html = $this->load->view($this->theme . 'purchases/pdf', $this->data, true);
            if (! $this->Settings->barcode_img) {
                $inv_html = preg_replace("'\<\?xml(.*)\?\>'", '', $inv_html);
            }
            $html[] = array(
                'content' => $inv_html,
                'footer' => '',
            );
        }

        $name = lang("purchases") . ".pdf";
        $this->sma->generate_pdf($html, $name);

    }

    public function email($purchase_id = null)
    {
        $this->sma->checkPermissions(false, true);

        if ($this->input->get('id')) {
            $purchase_id = $this->input->get('id');
        }
        $inv = $this->purchases_model->getPurchaseByID($purchase_id);
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
            $supplier = $this->site->getCompanyByID($inv->supplier_id);
            $this->load->library('parser');
            $parse_data = array(
                'reference_number' => $inv->reference_no,
                'contact_person' => $supplier->name,
                'company' => $supplier->company,
                'site_link' => base_url(),
                'site_name' => $this->Settings->site_name,
                'logo' => '<img src="' . base_url() . 'assets/uploads/logos/' . $this->Settings->logo . '" alt="' . $this->Settings->site_name . '"/>',
            );
            $msg = $this->input->post('note');
            $message = $this->parser->parse_string($msg, $parse_data);
            $attachment = $this->pdf($purchase_id, null, 'S');
        } elseif ($this->input->post('send_email')) {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->session->set_flashdata('error', $this->data['error']);
            redirect($_SERVER["HTTP_REFERER"]);
        }

        if ($this->form_validation->run() == true && $this->sma->send_email($to, $subject, $message, null, null, $attachment, $cc, $bcc)) {
            delete_files($attachment);
            $this->db->update('purchases', array('status' => 'ordered'), array('id' => $purchase_id));
            $this->session->set_flashdata('message', $this->lang->line("email_sent"));
            redirect("purchases");
        } else {

            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

            if (file_exists('./themes/' . $this->theme . '/views/email_templates/purchase.html')) {
                $purchase_temp = file_get_contents('themes/' . $this->theme . '/views/email_templates/purchase.html');
            } else {
                $purchase_temp = file_get_contents('./themes/default/views/email_templates/purchase.html');
            }
            $this->data['subject'] = array('name' => 'subject',
                'id' => 'subject',
                'type' => 'text',
                'value' => $this->form_validation->set_value('subject', lang('purchase_order').' (' . $inv->reference_no . ') '.lang('from').' ' . $this->Settings->site_name),
            );
            $this->data['note'] = array('name' => 'note',
                'id' => 'note',
                'type' => 'text',
                'value' => $this->form_validation->set_value('note', $purchase_temp),
            );
            $this->data['supplier'] = $this->site->getCompanyByID($inv->supplier_id);

            $this->data['id'] = $purchase_id;
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'purchases/email', $this->data);

        }
    }

    /* -------------------------------------------------------------------------------------------------------------------------------- */

    public function add($quote_id = null)
    {
        $this->sma->checkPermissions();

        $this->form_validation->set_message('is_natural_no_zero', $this->lang->line("no_zero_required"));
        //$this->form_validation->set_rules('reference_no', $this->lang->line("ref_no"), 'required');
        $this->form_validation->set_rules('warehouse', $this->lang->line("warehouse"), 'required|is_natural_no_zero');
        $this->form_validation->set_rules('supplier', $this->lang->line("supplier"), 'required');

        $this->session->unset_userdata('csrf_token');
        if ($this->form_validation->run() == true) {

            $reference = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('po');
            if ($this->Owner || $this->Admin) {
                $date = $this->sma->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }
            $warehouse_id = $this->input->post('warehouse');
            $supplier_id = $this->input->post('supplier');
            $status = $this->input->post('status');
			$doitac = $this->input->post('doitac');
			$payment_status= $this->input->post('payment_status');
            $shipping = $this->input->post('shipping') ? $this->input->post('shipping') : 0;
            $supplier_details = $this->site->getCompanyByID($supplier_id);
            $supplier = $supplier_details->company != '-'  ? $supplier_details->company : $supplier_details->name;
            $note = $this->input->post('note');
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
            $i = sizeof($_POST['product']);
            for ($r = 0; $r < $i; $r++) {
                $item_code = $_POST['product'][$r];
                $item_net_cost = $this->sma->formatDecimal($_POST['net_cost'][$r]);
                $unit_cost = $this->sma->formatDecimal($_POST['unit_cost'][$r]);
                $real_unit_cost = $this->sma->formatDecimal($_POST['real_unit_cost'][$r]);
                $item_unit_quantity = $_POST['quantity'][$r];
                $item_option = isset($_POST['product_option'][$r]) && $_POST['product_option'][$r] != 'false' ? $_POST['product_option'][$r] : null;
                $item_tax_rate = isset($_POST['product_tax'][$r]) ? $_POST['product_tax'][$r] : null;
                
				$item_discount = isset($_POST['product_discount'][$r]) ? $_POST['product_discount'][$r] : null;
				$item_chieckhau = isset($_POST['product_chieckhau'][$r]) ? $_POST['product_chieckhau'][$r] : null;
				if($item_chieckhau=='undefined'){
					$item_chieckhau=null;	
				}
                $item_expiry = (isset($_POST['expiry'][$r]) && !empty($_POST['expiry'][$r])) ? $this->sma->fsd($_POST['expiry'][$r]) : null;
                $supplier_part_no = (isset($_POST['part_no'][$r]) && !empty($_POST['part_no'][$r])) ? $_POST['part_no'][$r] : null;
                $item_unit = $_POST['product_unit'][$r];
                $item_quantity = $_POST['product_base_quantity'][$r];
				
				if($item_option=='0'||$item_option==0){
					$item_option=null;	
				}
				
                if (isset($item_code) && isset($real_unit_cost) && isset($unit_cost) && isset($item_quantity)) {
                    $product_details = $this->purchases_model->getProductByCode($item_code);
                    if ($item_expiry) {
                        $today = date('Y-m-d');
                        if ($item_expiry <= $today) {
                            $this->session->set_flashdata('error', lang('product_expiry_date_issue') . ' (' . $product_details->name . ')');
                            redirect($_SERVER["HTTP_REFERER"]);
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
                        'product_code' => $item_code,
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
                $this->form_validation->set_rules('product', lang("order_items"), 'required');
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
			if ($payment_status == 'partial' || $payment_status == 'paid') {
				
				if ($this->input->post('paid_by') == 'gift_card') {
					$gc = $this->site->getGiftCardByNO($this->input->post('gift_card_no'));
					$amount_paying = $grand_total >= $gc->balance ? $gc->balance : $grand_total;
					$gc_balance = $gc->balance - $amount_paying;
					$payment = array(
						'date' => $date,
						'reference_no' => $this->input->post('payment_reference_no'),
						'amount' => $this->sma->formatDecimal($amount_paying),
						'paid_by' => $this->input->post('paid_by'),
						'cheque_no' => $this->input->post('cheque_no'),
						'cc_no' => $this->input->post('gift_card_no'),
						'cc_holder' => $this->input->post('pcc_holder'),
						'cc_month' => $this->input->post('pcc_month'),
						'cc_year' => $this->input->post('pcc_year'),
						'cc_type' => $this->input->post('pcc_type'),
						'created_by' => $this->session->userdata('user_id'),
						'note' => $this->input->post('payment_note'),
						'type' => 'sent',
                        'warehouse_id' => $warehouse_id,
						'gc_balance' => $gc_balance,
                        'c_name' => $supplier_details->name,
                        'c_phone' => $supplier_details->phone,
                        'c_address' => $supplier_details->address,
						
					);
					
				} else {
					$payment = array(
						'date' => $date,
						'reference_no' => $this->input->post('payment_reference_no'),
						'amount' => $this->sma->formatDecimal($this->input->post('amount-paid')),
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
			} else {
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

           //  $this->sma->print_arrays($data, $products,$payment);
        }

        if ($this->form_validation->run() == true && $this->purchases_model->addPurchase($data, $products,$payment,$dlDetails)) {
            $this->session->set_userdata('remove_pols', 1);
            $this->session->set_flashdata('message', $this->lang->line("purchase_added"));
            $data=null;
            redirect('purchases');
        } else {

            if ($quote_id) {
                $this->data['quote'] = $this->purchases_model->getQuoteByID($quote_id);
                $supplier_id = $this->data['quote']->supplier_id;
                $items = $this->purchases_model->getAllQuoteItems($quote_id);
                krsort($items);
                $c = rand(100000, 9999999);
                foreach ($items as $item) {
                    $row = $this->site->getProductByID($item->product_id);
                    if ($row->type == 'combo') {
                        $combo_items = $this->site->getProductComboItems($row->id, $item->warehouse_id);
                        foreach ($combo_items as $citem) {
                            $crow = $this->site->getProductByID($citem->id);
                            if (!$crow) {
                                $crow = json_decode('{}');
                                $crow->qty = $item->quantity;
                            } else {
                                unset($crow->details, $crow->product_details, $crow->price);
                                $crow->qty = $citem->qty*$item->quantity;
                            }
                            $crow->base_quantity = $item->quantity;
                            $crow->base_unit = $crow->unit ? $crow->unit : $item->product_unit_id;
                            $crow->base_unit_cost = $crow->cost ? $crow->cost : $item->unit_cost;
                            $crow->unit = $item->product_unit_id;
                            $crow->discount = $item->discount ? $item->discount : '0';
                            $supplier_cost = $supplier_id ? $this->getSupplierCost($supplier_id, $crow) : $crow->cost;
                            $crow->cost = $supplier_cost ? $supplier_cost : 0;
                            $crow->tax_rate = $item->tax_rate_id;
                            $crow->real_unit_cost = $crow->cost ? $crow->cost : 0;
                            $crow->expiry = '';
                            $options = $this->purchases_model->getProductOptions($crow->id);
                            $units = $this->site->getUnitsByBUID($row->base_unit);
                            $tax_rate = $this->site->getTaxRateByID($crow->tax_rate);
                            $ri = $this->Settings->item_addition ? $crow->id : $c;

                            $pr[$ri] = array('id' => $c, 'item_id' => $crow->id, 'label' => $crow->name . " (" . $crow->code . ")", 'row' => $crow, 'tax_rate' => $tax_rate, 'units' => $units, 'options' => $options);
                            $c++;
                        }
                    } elseif ($row->type == 'standard') {
                        if (!$row) {
                            $row = json_decode('{}');
                            $row->quantity = 0;
                        } else {
                            unset($row->details, $row->product_details);
                        }

                        $row->id = $item->product_id;
                        $row->code = $item->product_code;
                        $row->name = $item->product_name;
                        $row->base_quantity = $item->quantity;
                        $row->base_unit = $row->unit ? $row->unit : $item->product_unit_id;
                        $row->base_unit_cost = $row->cost ? $row->cost : $item->unit_cost;
                        $row->unit = $item->product_unit_id;
                        $row->qty = $item->unit_quantity;
                        $row->option = $item->option_id;
                        $row->discount = $item->discount ? $item->discount : '0';
                        $supplier_cost = $supplier_id ? $this->getSupplierCost($supplier_id, $row) : $row->cost;
                        $row->cost = $supplier_cost ? $supplier_cost : 0;
                        $row->tax_rate = $item->tax_rate_id;
                        $row->expiry = '';
                        $row->real_unit_cost = $row->cost ? $row->cost : 0;
                        $options = $this->purchases_model->getProductOptions($row->id);

                        $units = $this->site->getUnitsByBUID($row->base_unit);
                        $tax_rate = $this->site->getTaxRateByID($row->tax_rate);
                        $ri = $this->Settings->item_addition ? $row->id : $c;

                        $pr[$ri] = array('id' => $c, 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 
                            'row' => $row, 'tax_rate' => $tax_rate, 'units' => $units, 'options' => $options);
                        $c++;
                    }
                }
                $this->data['quote_items'] = json_encode($pr);
            }

            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			
            $this->data['quote_id'] = $quote_id;
            $this->data['suppliers'] = $this->site->getAllCompanies('supplier');
            $this->data['categories'] = $this->site->getAllCategories();
            $this->data['tax_rates'] = $this->site->getAllTaxRates();
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['ponumber'] = ''; //$this->site->getReference('po');
            $this->data['doitacs'] = $this->site->getAllDoitac(); 
            $this->load->helper('string');
            $value = random_string('alnum', 20);
            $this->session->set_userdata('user_csrf', $value);
            $this->data['csrf'] = $this->session->userdata('user_csrf');
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('purchases'), 'page' => lang('purchases')), array('link' => '#', 'page' => lang('add_purchase')));
            $meta = array('page_title' => lang('add_purchase'), 'bc' => $bc);
            $this->page_construct('purchases/add', $meta, $this->data);
        }
    }

    /* ------------------------------------------------------------------------------------- */

    public function edit($id = null)
    {
        $this->sma->checkPermissions();

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $inv = $this->purchases_model->getPurchaseByID($id);
        if ($inv->status == 'returned' || $inv->return_id || $inv->return_purchase_ref) {
            $this->session->set_flashdata('error', lang('Đơn nhập hàng có trả hàng, vui lòng hủy hóa đơn'));
            redirect(isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : 'welcome');
        }
        if (!$this->session->userdata('edit_right')) {
            $this->sma->view_rights($inv->created_by);
        }
        $this->form_validation->set_message('is_natural_no_zero', $this->lang->line("no_zero_required"));
        $this->form_validation->set_rules('reference_no', $this->lang->line("ref_no"), 'required');
        $this->form_validation->set_rules('warehouse', $this->lang->line("warehouse"), 'required|is_natural_no_zero');
        $this->form_validation->set_rules('supplier', $this->lang->line("supplier"), 'required');

        $this->session->unset_userdata('csrf_token');
        if ($this->form_validation->run() == true) {

            $reference = $this->input->post('reference_no');
            if ($this->Owner || $this->Admin) {
                $date = $this->sma->fld(trim($this->input->post('date')));
            } else {
                $date = $inv->date;
            }
            $warehouse_id = $this->input->post('warehouse');
            $supplier_id = $this->input->post('supplier');
            $status = $this->input->post('status');
			$doitac = $this->input->post('doitac');
            $shipping = $this->input->post('shipping') ? $this->input->post('shipping') : 0;
            $supplier_details = $this->site->getCompanyByID($supplier_id);
            $supplier = $supplier_details->company != '-'  ? $supplier_details->company : $supplier_details->name;
            $note = $this->input->post('note');
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
            $partial = false;
            $i = sizeof($_POST['product']);
            for ($r = 0; $r < $i; $r++) {
                $item_code = $_POST['product'][$r];
                $item_net_cost = $this->sma->formatDecimal($_POST['net_cost'][$r]);
                $unit_cost = $this->sma->formatDecimal($_POST['unit_cost'][$r]);
                $real_unit_cost = $this->sma->formatDecimal($_POST['real_unit_cost'][$r]);
                $item_unit_quantity = $_POST['quantity'][$r];
                $quantity_received = $_POST['received_base_quantity'][$r];
                $item_option = isset($_POST['product_option'][$r]) && $_POST['product_option'][$r] != 'false' ? $_POST['product_option'][$r] : null;
                $item_tax_rate = isset($_POST['product_tax'][$r]) ? $_POST['product_tax'][$r] : null;
                $item_discount = isset($_POST['product_discount'][$r]) ? $_POST['product_discount'][$r] : null;
				$item_chieckhau = isset($_POST['product_chieckhau'][$r]) ? $_POST['product_chieckhau'][$r] : null;
                $item_expiry = (isset($_POST['expiry'][$r]) && !empty($_POST['expiry'][$r])) ? $this->sma->fsd($_POST['expiry'][$r]) : null;
                $supplier_part_no = (isset($_POST['part_no'][$r]) && !empty($_POST['part_no'][$r])) ? $_POST['part_no'][$r] : null;
                $quantity_balance = $_POST['quantity_balance'][$r];
                $ordered_quantity = $_POST['ordered_quantity'][$r];
                $item_unit = $_POST['product_unit'][$r];
                $item_quantity = $_POST['product_base_quantity'][$r];
				if($item_chieckhau=='undefined'){
					$item_chieckhau=null;	
				}
				if($item_option=='0'||$item_option==0){
					$item_option=null;	
				}
				
                if ($status == 'received' || $status == 'partial') {
                    if ($quantity_received < $item_quantity) {
                        $partial = 'partial';
                    } elseif ($quantity_received > $item_quantity) {
                        $this->session->set_flashdata('error', lang("received_more_than_ordered"));
                        redirect($_SERVER["HTTP_REFERER"]);
                    }
                    $balance_qty =  $quantity_received - ($ordered_quantity - $quantity_balance);
                } else {
                    $balance_qty = $item_quantity;
                    $quantity_received = $item_quantity;
                }
                if (isset($item_code) && isset($real_unit_cost) && isset($unit_cost) && isset($item_quantity) && isset($quantity_balance)) {
                    $product_details = $this->purchases_model->getProductByCode($item_code);
                    // $unit_cost = $real_unit_cost;
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

                   // $unit_cost = $this->sma->formatDecimal($unit_cost - $pr_discount);
                   // $item_net_cost = $unit_cost;
                    $pr_item_discount = $this->sma->formatDecimal($pr_discount * $item_unit_quantity);
                    $product_discount += $pr_item_discount;
					
					$pr_chieckhau = 0;

                    if (isset($item_chieckhau)) {
                        $discount_ck = $item_chieckhau;
                        $dpos = strpos($discount_ck, $percentage);
                        if ($dpos !== false) {
                            $pds = explode("%", $discount_ck);
                            $pr_chieckhau = $this->sma->formatDecimal(((($this->sma->formatDecimal($unit_cost-$pr_discount)) * (Float) ($pds[0])) / 100), 4);
                        } else {
                            $pr_chieckhau = $this->sma->formatDecimal($discount_ck);
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

                    $items[] = array(
                        'product_id' => $product_details->id,
                        'product_code' => $item_code,
                        'product_name' => $product_details->name,
                        'option_id' => $item_option,
                        'net_unit_cost' => $real_unit_cost,
                        'unit_cost' => $this->sma->formatDecimal($real_unit_cost + $item_tax),
                        'quantity' => $item_quantity,
                        'product_unit_id' => $item_unit,
                        'product_unit_code' => $unit->code,
                        'unit_quantity' => $item_unit_quantity,
                        'quantity_balance' => $balance_qty,
						'chiec_khau' => $item_chieckhau,
                        'items_chiec_khau' => $pr_item_chieckhau,
                        'quantity_received' => $quantity_received,
                        'warehouse_id' => $warehouse_id,
                        'item_tax' => $pr_item_tax,
                        'tax_rate_id' => $pr_tax,
                        'tax' => $tax,
                        'discount' => $item_discount,
                        'item_discount' => $pr_item_discount,
                        'subtotal' => $this->sma->formatDecimal($subtotal),
                        'expiry' => $item_expiry,
                        'real_unit_cost' => $real_unit_cost,
                        'supplier_part_no' => $supplier_part_no,
                        'date' => date('Y-m-d', strtotime($date)),
                    );

                    $total += $item_net_cost * $item_unit_quantity;
                }
            }
            if ($status == 'received' || $status == 'partial') {
                $status = $partial ? $partial : 'received';
            }
            if (empty($items)) {
                $this->form_validation->set_rules('product', lang("order_items"), 'required');
            } else {
                foreach ($items as $item) {
                    $item["status"] = $status;
                    $products[] = $item;
                }
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
            $data = array('reference_no' => $reference,
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
                'updated_by' => $this->session->userdata('user_id'),
                'updated_at' => date('Y-m-d H:i:s'),
                'payment_term' => $payment_term,
                'due_date' => $due_date,
            );
            if ($date) {
                $data['date'] = $date;
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

        if ($this->form_validation->run() == true && $this->purchases_model->updatePurchase($id, $data, $products,$dlDetails)) {
            $this->session->set_userdata('remove_pols', 1);
            $this->session->set_flashdata('message', $this->lang->line("purchase_added"));
            redirect('purchases');
        } else {

            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['inv'] = $inv;
            if ($this->Settings->disable_editing) {
                if ($this->data['inv']->date <= date('Y-m-d', strtotime('-'.$this->Settings->disable_editing.' days'))) {
                    $this->session->set_flashdata('error', lang("Hết thời gian chỉnh sửa hóa đơn: ".$this->Settings->disable_editing." ngày"));
                    redirect($_SERVER["HTTP_REFERER"]);
                }
            }
            $inv_items = $this->purchases_model->getAllPurchaseItems($id);
            krsort($inv_items);
            $c = rand(100000, 9999999);
            foreach ($inv_items as $item) {
                $row = $this->site->getProductByID($item->product_id);
                $row->expiry = (($item->expiry && $item->expiry != '0000-00-00') ? $this->sma->hrsd($item->expiry) : '');
                $row->base_quantity = $item->quantity;
                $row->base_unit = $row->unit ? $row->unit : $item->product_unit_id;
                $row->base_unit_cost = $row->cost ? $row->cost : $item->unit_cost;
                $row->unit = $item->product_unit_id;
                $row->qty = $item->unit_quantity;
                $row->oqty = $item->quantity;
                $row->supplier_part_no = $item->supplier_part_no;
                $row->received = $item->quantity_received ? $item->quantity_received : $item->quantity;
                $row->quantity_balance = $item->quantity_balance + ($item->quantity-$row->received);
                $row->discount = $item->discount ? $item->discount : '0';
				$row->chiec_khau = $item->chiec_khau ? $item->chiec_khau : '0';
                $options = $this->purchases_model->getProductOptions($row->id);
                $row->option = $item->option_id;
                $row->real_unit_cost = $item->real_unit_cost;
                $row->cost = $this->sma->formatDecimal($item->net_unit_cost + ($item->item_discount / $item->quantity));
                $row->tax_rate = $item->tax_rate_id;
                unset($row->details, $row->product_details, $row->price, $row->file, $row->product_group_id);
                $units = $this->site->getUnitsByBUID($row->base_unit);
                $tax_rate = $this->site->getTaxRateByID($row->tax_rate);
                $ri = $this->Settings->item_addition ? $row->id : $c;

                $pr[$ri] = array('id' => $c, 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 
                    'row' => $row, 'tax_rate' => $tax_rate, 'units' => $units, 'options' => $options);
                $c++;
            }

            
            $this->data['inv_items'] = json_encode($pr);
            $this->data['id'] = $id;
            $this->data['suppliers'] = $this->site->getAllCompanies('supplier');
            $this->data['purchase'] = $this->purchases_model->getPurchaseByID($id);
            $this->data['categories'] = $this->site->getAllCategories();
            $this->data['tax_rates'] = $this->site->getAllTaxRates();
             $this->data['doitacs'] = $this->site->getAllDoitac(); 
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->load->helper('string');
            $value = random_string('alnum', 20);
            $this->session->set_userdata('user_csrf', $value);
            $this->session->set_userdata('remove_pols', 1);
            $this->data['csrf'] = $this->session->userdata('user_csrf');
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('purchases'), 'page' => lang('purchases')), array('link' => '#', 'page' => lang('edit_purchase')));
            $meta = array('page_title' => lang('edit_purchase'), 'bc' => $bc);
            $this->page_construct('purchases/edit', $meta, $this->data);
        }
    }

    /* ----------------------------------------------------------------------------------------------------------- */

    public function purchase_by_csv()
    {
        $this->sma->checkPermissions('csv');
        $this->load->helper('security');
        $this->form_validation->set_message('is_natural_no_zero', $this->lang->line("no_zero_required"));
        $this->form_validation->set_rules('warehouse', $this->lang->line("warehouse"), 'required|is_natural_no_zero');
        $this->form_validation->set_rules('supplier', $this->lang->line("supplier"), 'required|is_natural_no_zero');
        $this->form_validation->set_rules('userfile', $this->lang->line("upload_file"), 'xss_clean');

        if ($this->form_validation->run() == true) {
            $quantity = "quantity";
            $product = "product";
            $unit_cost = "unit_cost";
            $tax_rate = "tax_rate";
            $reference = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('po');
            if ($this->Owner || $this->Admin) {
                $date = $this->sma->fld(trim($this->input->post('date')));
            } else {
                $date = null;
            }
            $warehouse_id = $this->input->post('warehouse');
            $supplier_id = $this->input->post('supplier');
            $status = $this->input->post('status');
            $shipping = $this->input->post('shipping') ? $this->input->post('shipping') : 0;
            $supplier_details = $this->site->getCompanyByID($supplier_id);
            $supplier = $supplier_details->company != '-'  ? $supplier_details->company : $supplier_details->name;
            $note = $this->sma->clear_tags($this->input->post('note'));

            $total = 0;
            $product_tax = 0;
            $order_tax = 0;
            $product_discount = 0;
            $order_discount = 0;
            $percentage = '%';

            if (isset($_FILES["userfile"])) {

                $this->load->library('upload');

                $config['upload_path'] = $this->digital_upload_path;
                $config['allowed_types'] = 'csv';
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = true;

                $this->upload->initialize($config);

                if (!$this->upload->do_upload()) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect("purchases/purchase_by_csv");
                }

                $csv = $this->upload->file_name;

                $arrResult = array();
                $handle = fopen($this->digital_upload_path . $csv, "r");
                if ($handle) {
                    while (($row = fgetcsv($handle, 1000, ",")) !== false) {
                        $arrResult[] = $row;
                    }
                    fclose($handle);
                }
                $titles = array_shift($arrResult);

                $keys = array('code', 'net_unit_cost', 'quantity', 'variant', 'item_tax_rate', 'discount', 'expiry');
                $final = array();
                foreach ($arrResult as $key => $value) {
                    $final[] = array_combine($keys, $value);
                }
                $rw = 2;
                foreach ($final as $csv_pr) {

                    if (isset($csv_pr['code']) && isset($csv_pr['net_unit_cost']) && isset($csv_pr['quantity'])) {

                        if ($product_details = $this->purchases_model->getProductByCode($csv_pr['code'])) {

                            if ($csv_pr['variant']) {
                                $item_option = $this->purchases_model->getProductVariantByName($csv_pr['variant'], $product_details->id);
                                if (!$item_option) {
                                    $this->session->set_flashdata('error', lang("pr_not_found") . " ( " . $product_details->name . " - " . $csv_pr['variant'] . " ). " . lang("line_no") . " " . $rw);
                                    redirect($_SERVER["HTTP_REFERER"]);
                                }
                            } else {
                                $item_option = json_decode('{}');
                                $item_option->id = null;
                            }

                            $item_code = $csv_pr['code'];
                            $item_net_cost = $this->sma->formatDecimal($csv_pr['net_unit_cost']);
                            $item_quantity = $csv_pr['quantity'];
                            $quantity_balance = $csv_pr['quantity'];
                            $item_tax_rate = $csv_pr['item_tax_rate'];
                            $item_discount = $csv_pr['discount'];
                            $item_expiry = isset($csv_pr['expiry']) ? $this->sma->fsd($csv_pr['expiry']) : null;

                            if (isset($item_discount) && $this->Settings->product_discount) {
                                $discount = $item_discount;
                                $dpos = strpos($discount, $percentage);
                                if ($dpos !== false) {
                                    $pds = explode("%", $discount);
                                    $pr_discount = $this->sma->formatDecimal(((($this->sma->formatDecimal($item_net_cost)) * (Float) ($pds[0])) / 100), 4);
                                } else {
                                    $pr_discount = $this->sma->formatDecimal($discount);
                                }
                            } else {
                                $pr_discount = 0;
                            }
                            $pr_item_discount = $this->sma->formatDecimal(($pr_discount * $item_quantity), 4);
                            $product_discount += $pr_item_discount;

                            if (isset($item_tax_rate) && $item_tax_rate != 0) {

                                if ($tax_details = $this->purchases_model->getTaxRateByName($item_tax_rate)) {
                                    $pr_tax = $tax_details->id;
                                    if ($tax_details->type == 1) {
                                        if (!$product_details->tax_method) {
                                            $item_tax = $this->sma->formatDecimal((($item_net_cost - $pr_discount) * $tax_details->rate) / (100 + $tax_details->rate), 4);
                                            $tax = $tax_details->rate . "%";
                                            $item_net_cost -= $item_tax;
                                        } else {
                                            $item_tax = $this->sma->formatDecimal((($item_net_cost - $pr_discount) * $tax_details->rate) / 100, 4);
                                            $tax = $tax_details->rate . "%";
                                        }
                                    } elseif ($tax_details->type == 2) {
                                        $item_tax = $this->sma->formatDecimal($tax_details->rate);
                                        $tax = $tax_details->rate;
                                    }
                                    $pr_item_tax = $this->sma->formatDecimal(($item_tax * $item_quantity), 4);
                                } else {
                                    $this->session->set_flashdata('error', lang("tax_not_found") . " ( " . $item_tax_rate . " ). " . lang("line_no") . " " . $rw);
                                    redirect($_SERVER["HTTP_REFERER"]);
                                }

                            } elseif ($product_details->tax_rate) {

                                $pr_tax = $product_details->tax_rate;
                                $tax_details = $this->site->getTaxRateByID($pr_tax);
                                if ($tax_details->type == 1) {
                                    if (!$product_details->tax_method) {
                                        $item_tax = $this->sma->formatDecimal((($item_net_cost - $pr_discount) * $tax_details->rate) / (100 + $tax_details->rate), 4);
                                        $tax = $tax_details->rate . "%";
                                        $item_net_cost -= $item_tax;
                                    } else {
                                        $item_tax = $this->sma->formatDecimal((($item_net_cost - $pr_discount) * $tax_details->rate) / 100, 4);
                                        $tax = $tax_details->rate . "%";
                                    }
                                } elseif ($tax_details->type == 2) {

                                    $item_tax = $this->sma->formatDecimal($tax_details->rate);
                                    $tax = $tax_details->rate;

                                }
                                $pr_item_tax = $this->sma->formatDecimal(($item_tax * $item_quantity), 4);

                            } else {
                                $pr_tax = 0;
                                $pr_item_tax = 0;
                                $tax = "";
                            }
                            $product_tax += $pr_item_tax;
                            $subtotal = $this->sma->formatDecimal(((($item_net_cost * $item_quantity) + $pr_item_tax) - $pr_item_discount), 4);
                            $unit = $this->site->getUnitByID($product_details->unit);
                            $products[] = array(
                                'product_id' => $product_details->id,
                                'product_code' => $item_code,
                                'product_name' => $product_details->name,
                                'option_id' => $item_option->id,
                                'net_unit_cost' => $item_net_cost,
                                'quantity' => $item_quantity,
                                'product_unit_id' => $product_details->unit,
                                'product_unit_code' => $unit->code,
                                'unit_quantity' => $item_quantity,
                                'quantity_balance' => $quantity_balance,
                                'warehouse_id' => $warehouse_id,
                                'item_tax' => $pr_item_tax,
                                'tax_rate_id' => $pr_tax,
                                'tax' => $tax,
                                'discount' => $item_discount,
                                'item_discount' => $pr_item_discount,
                                'expiry' => $item_expiry,
                                'subtotal' => $subtotal,
                                'date' => date('Y-m-d', strtotime($date)),
                                'status' => $status,
                                'unit_cost' => $this->sma->formatDecimal(($item_net_cost + $item_tax), 4),
                                'real_unit_cost' => $this->sma->formatDecimal(($item_net_cost + $item_tax + $pr_discount), 4),
                            );

                            $total += $this->sma->formatDecimal(($item_net_cost * $item_quantity), 4);

                        } else {
                            $this->session->set_flashdata('error', $this->lang->line("pr_not_found") . " ( " . $csv_pr['code'] . " ). " . $this->lang->line("line_no") . " " . $rw);
                            redirect($_SERVER["HTTP_REFERER"]);
                        }
                        $rw++;
                    }

                }
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
            $total_discount = $this->sma->formatDecimal(($order_discount + $product_discount), 4);

            if ($this->Settings->tax2 != 0) {
                $order_tax_id = $this->input->post('order_tax');
                if ($order_tax_details = $this->site->getTaxRateByID($order_tax_id)) {
                    if ($order_tax_details->type == 2) {
                        $order_tax = $this->sma->formatDecimal($order_tax_details->rate);
                    }
                    if ($order_tax_details->type == 1) {
                        $order_tax = $this->sma->formatDecimal((($total + $product_tax - $total_discount) * $order_tax_details->rate) / 100);
                    }
                }
            } else {
                $order_tax_id = null;
            }

            $total_tax = $this->sma->formatDecimal(($product_tax + $order_tax), 4);
            $grand_total = $this->sma->formatDecimal(($total + $total_tax + $this->sma->formatDecimal($shipping) - $total_discount), 4);
            $data = array('reference_no' => $reference,
                'date' => $date,
                'supplier_id' => $supplier_id,
                'supplier' => $supplier,
                'warehouse_id' => $warehouse_id,
                'note' => $note,
                'total' => $total,
                'product_discount' => $product_discount,
                'order_discount_id' => $order_discount_id,
                'order_discount' => $order_discount,
                'total_discount' => $total_discount,
                'product_tax' => $product_tax,
                'order_tax_id' => $order_tax_id,
                'order_tax' => $order_tax,
                'total_tax' => $total_tax,
                'shipping' => $this->sma->formatDecimal($shipping),
                'grand_total' => $grand_total,
                'status' => $status,
                'created_by' => $this->session->userdata('username'),
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

            //$this->sma->print_arrays($data, $products);
        }

        if ($this->form_validation->run() == true && $this->purchases_model->addPurchase($data, $products)) {

            $this->session->set_flashdata('message', $this->lang->line("purchase_added"));
            redirect("purchases");
        } else {

            $data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['tax_rates'] = $this->site->getAllTaxRates();
            $this->data['ponumber'] = ''; // $this->site->getReference('po');

            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('purchases'), 'page' => lang('purchases')), array('link' => '#', 'page' => lang('add_purchase_by_csv')));
            $meta = array('page_title' => lang('add_purchase_by_csv'), 'bc' => $bc);
            $this->page_construct('purchases/purchase_by_csv', $meta, $this->data);

        }
    }

    /* --------------------------------------------------------------------------- */

    public function delete($id = null)
    {
        $this->sma->checkPermissions(null, true);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        if ($this->purchases_model->deletePurchase($id)) {
            if ($this->input->is_ajax_request()) {
                $this->sma->send_json(array('error' => 0, 'msg' => lang("purchase_deleted")));
            }
            $this->session->set_flashdata('message', lang('purchase_deleted'));
            redirect('purchases');
        }
    }

    /* --------------------------------------------------------------------------- */

    public function suggestions()
    {
        $term = $this->input->get('term', true);
        $supplier_id = $this->input->get('supplier_id', true);

        if (strlen($term) < 1 || !$term) {
            die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . site_url('welcome') . "'; }, 10);</script>");
        }

        $analyzed = $this->sma->analyze_term($term);
        $sr = $analyzed['term'];
        $option_id = $analyzed['option_id'];

        $rows = $this->purchases_model->getProductNames($sr);
        if ($rows) {
            $c = str_replace(".", "", microtime(true));
            $r = 0;
            foreach ($rows as $row) {
                $option = false;
                $row->item_tax_method = $row->tax_method;
                $options = $this->purchases_model->getProductOptions($row->id);
                if ($options) {
                    $opt = $option_id && $r == 0 ? $this->purchases_model->getProductOptionByID($option_id) : current($options);
                    if (!$option_id || $r > 0) {
                        $option_id = $opt->id;
                    }
                } else {
                    $opt = json_decode('{}');
                    $opt->cost = 0;
                    $option_id = FALSE;
                }
                $row->option = $option_id;
                $row->supplier_part_no = '';
                if ($opt->cost != 0) {
                    $row->cost = $opt->cost;
                }
                $row->cost = $supplier_id ? $this->getSupplierCost($supplier_id, $row) : $row->cost;
                $row->real_unit_cost = $row->cost;
                $row->base_quantity = 1;
				$row->qty = 1;
				$row->new_entry = 1;
				if($row->quantity==0){
					$row->base_quantity = 0;
					$row->qty = 0;
					$row->new_entry = 0;
				}
				
                $row->base_unit = $row->unit;
                $row->base_unit_cost = $row->cost;
                $row->unit = $row->purchase_unit ? $row->purchase_unit : $row->unit;
                
                $row->expiry = '';
                
                $row->quantity_balance = '';
                $row->discount = '0';
                unset($row->details, $row->product_details, $row->price, $row->file, $row->supplier1price, $row->supplier2price, $row->supplier3price, $row->supplier4price, $row->supplier5price, $row->supplier1_part_no, $row->supplier2_part_no, $row->supplier3_part_no, $row->supplier4_part_no, $row->supplier5_part_no);

                $units = $this->site->getUnitsByBUID($row->base_unit);

                $tax_rate = $this->site->getTaxRateByID(1);

                $pr[] = array('id' => ($c + $r), 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 
                    'row' => $row, 'tax_rate' => $tax_rate, 'units' => $units, 'options' => $options);
                $r++;
            }
            $this->sma->send_json($pr);
        } else {
            $this->sma->send_json(array(array('id' => 0, 'label' => lang('no_match_found'), 'value' => $term)));
        }
    }

    /* -------------------------------------------------------------------------------- */

    public function purchase_actions()
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
                        $this->purchases_model->deletePurchase($id);
                    }
                    $this->session->set_flashdata('message', $this->lang->line("purchases_deleted"));
                    redirect($_SERVER["HTTP_REFERER"]);

                } elseif ($this->input->post('form_action') == 'combine') {

                    $html = $this->combine_pdf($_POST['val']);

                } elseif ($this->input->post('form_action') == 'export_excel' || $this->input->post('form_action') == 'export_pdf') {

                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('purchases'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('reference_no'));
					$this->excel->getActiveSheet()->SetCellValue('C1', lang('ĐVGH'));
					$this->excel->getActiveSheet()->SetCellValue('D1', lang('Kho'));
					$this->excel->getActiveSheet()->SetCellValue('E1', lang('Nhân viên'));
                    $this->excel->getActiveSheet()->SetCellValue('F1', lang('supplier'));
                    $this->excel->getActiveSheet()->SetCellValue('G1', lang('status'));
                    $this->excel->getActiveSheet()->SetCellValue('H1', lang('grand_total'));

                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $purchase = $this->purchases_model->getPurchaseByID($id);
						$warehouse=$this->site->getWarehouseByID($purchase->warehouse_id);	
						$_dvgh= $this->doitac_model->getDoiTacByID($purchase->doitac); 
						$gvgh=$_dvgh->name!=""?$_dvgh->name:"";
						$nv=$this->site->getUser($warehouse->created_by);
						$nhanvien=$nv->first_name.' '.$nv->last_name;
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->sma->hrld($purchase->date));
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $purchase->reference_no);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $gvgh);
						$this->excel->getActiveSheet()->SetCellValue('D' . $row, $warehouse->name);
						$this->excel->getActiveSheet()->SetCellValue('E' . $row, $nhanvien);
						$this->excel->getActiveSheet()->SetCellValue('F' . $row, $purchase->supplier);
                        $this->excel->getActiveSheet()->SetCellValue('G' . $row, lang($purchase->status));
                        $this->excel->getActiveSheet()->SetCellValue('H' . $row, $this->sma->formatMoney($purchase->grand_total));
                        $row++;
                    }

                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'NhapHang_' . date('Y_m_d_H_i_s');
                    if ($this->input->post('form_action') == 'export_pdf') {
                        $styleArray = array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)));
                        $this->excel->getDefaultStyle()->applyFromArray($styleArray);
                        $this->excel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
                        require_once APPPATH . "third_party" . DIRECTORY_SEPARATOR . "mpdf" . DIRECTORY_SEPARATOR . "mpdf.php";
                        $rendererName = PHPExcel_Settings::PDF_RENDERER_MPDF;
                        $rendererLibrary = 'mpdf';
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
                $this->session->set_flashdata('error', $this->lang->line("no_purchase_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }

    /* -------------------------------------------------------------------------------- */

    public function payments($id = null)
    {
        $this->sma->checkPermissions(false, true);

        $this->data['payments'] = $this->purchases_model->getPurchasePayments($id);
        $this->data['inv'] = $this->purchases_model->getPurchaseByID($id);
        $this->load->view($this->theme . 'purchases/payments', $this->data);
    }

    public function payment_note($id = null)
    {
        $this->sma->checkPermissions('payments', true);
        $payment = $this->purchases_model->getPaymentByID($id);
		
        
		if($payment->id_ncc_id_kh>0){
			$this->data['supplier'] = $this->site->getCompanyByID($payment->id_ncc_id_kh);
			$this->data['warehouse'] = $this->site->getWarehouseByID($payment->warehouse_id);	
		}else{
			$inv = $this->purchases_model->getPurchaseByID($payment->purchase_id);
			$this->data['supplier'] = $this->site->getCompanyByID($inv->supplier_id);
			$this->data['warehouse'] = $this->site->getWarehouseByID($inv->warehouse_id);		
			$this->data['inv'] = $inv;
		}
        
		
        $this->data['payment'] = $payment;
        $this->data['page_title'] = $this->lang->line("payment_note");

        $this->load->view($this->theme . 'purchases/payment_note', $this->data);
    }

    public function email_payment($id = null)
    {
        $this->sma->checkPermissions('payments', true);
        $payment = $this->purchases_model->getPaymentByID($id);
        $inv = $this->purchases_model->getPurchaseByID($payment->purchase_id);
        $supplier = $this->site->getCompanyByID($inv->supplier_id);
        $this->data['inv'] = $inv;
        $this->data['payment'] = $payment;
        if ( ! $supplier->email) {
            $this->sma->send_json(array('msg' => lang("update_supplier_email")));
        }
        $this->data['supplier'] =$supplier;
        $this->data['warehouse'] = $this->site->getWarehouseByID($inv->warehouse_id);
        $this->data['inv'] = $inv;
        $this->data['payment'] = $payment;
        $this->data['page_title'] = lang("payment_note");
        $html = $this->load->view($this->theme . 'purchases/payment_note', $this->data, TRUE);

        $html = str_replace(array('<i class="fa fa-2x">&times;</i>', 'modal-', '<p>&nbsp;</p>', '<p style="border-bottom: 1px solid #666;">&nbsp;</p>', '<p>'.lang("stamp_sign").'</p>'), '', $html);
        $html = preg_replace("/<img[^>]+\>/i", '', $html);
        // $html = '<div style="border:1px solid #DDD; padding:10px; margin:10px 0;">'.$html.'</div>';

        $this->load->library('parser');
        $parse_data = array(
            'stylesheet' => '<link href="'.$this->data['assets'].'styles/helpers/bootstrap.min.css" rel="stylesheet"/>',
            'name' => $supplier->company && $supplier->company != '-' ? $supplier->company :  $supplier->name,
            'email' => $supplier->email,
            'heading' => lang('payment_note').'<hr>',
            'msg' => $html,
            'site_link' => base_url(),
            'site_name' => $this->Settings->site_name,
            'logo' => '<img src="' . base_url('assets/uploads/logos/' . $this->Settings->logo) . '" alt="' . $this->Settings->site_name . '"/>'
        );
        $msg = file_get_contents('./themes/' . $this->Settings->theme . '/views/email_templates/email_con.html');
        $message = $this->parser->parse_string($msg, $parse_data);
        $subject = lang('payment_note') . ' - ' . $this->Settings->site_name;

        if ($this->sma->send_email($supplier->email, $subject, $message)) {
            $this->sma->send_json(array('msg' => lang("email_sent")));
        } else {
            $this->sma->send_json(array('msg' => lang("email_failed")));
        }
    }

    public function add_payment($id = null)
    {
        $this->sma->checkPermissions('payments', true);
        $this->load->helper('security');
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $purchase = $this->purchases_model->getPurchaseByID($id);
        if ($purchase->payment_status == 'paid' && $purchase->grand_total == $purchase->paid) {
            $this->session->set_flashdata('error', lang("purchase_already_paid"));
            $this->sma->md();
        }

        //$this->form_validation->set_rules('reference_no', lang("reference_no"), 'required');
        $this->form_validation->set_rules('amount-paid', lang("amount"), 'required');
        $this->form_validation->set_rules('paid_by', lang("paid_by"), 'required');
        $this->form_validation->set_rules('userfile', lang("attachment"), 'xss_clean');
        if ($this->form_validation->run() == true) {
            if ($this->Owner || $this->Admin) {
                $date = $this->sma->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }
			$note=$this->input->post('note');
			if($note==''){
				$note='Chi phiếu nhập hàng ['.$purchase->reference_no.']';
			}
            $payment = array(
                'date' => $date,
                'purchase_id' => $this->input->post('purchase_id'),
                'reference_no' => $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('ppay'),
                'amount' => $this->input->post('amount-paid'),
                'paid_by' => $this->input->post('paid_by'),
                'cheque_no' => $this->input->post('cheque_no'),
                'cc_no' => $this->input->post('pcc_no'),
                'cc_holder' => $this->input->post('pcc_holder'),
                'cc_month' => $this->input->post('pcc_month'),
                'cc_year' => $this->input->post('pcc_year'),
                'cc_type' => $this->input->post('pcc_type'),
                'note' => $note,
                'created_by' => $this->session->userdata('user_id'),
                'type' => 'sent',
                'warehouse_id' => $purchase->warehouse_id,
                'c_name' => $this->input->post('c_name'),
                'c_phone' => $this->input->post('c_phone'),
                'c_address' => $this->input->post('c_address'),
            );

            if ($_FILES['userfile']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] = $this->digital_upload_path;
                $config['allowed_types'] = $this->digital_file_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = false;
                $config['encrypt_name'] = true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload()) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo = $this->upload->file_name;
                $payment['attachment'] = $photo;
            }

            //$this->sma->print_arrays($payment);

        } elseif ($this->input->post('add_payment')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }

        if ($this->form_validation->run() == true && $this->purchases_model->addPayment($payment)) {
            $this->session->set_flashdata('message', lang("payment_added"));
            redirect($_SERVER["HTTP_REFERER"]);
        } else {

            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['inv'] = $purchase;

            //get ncc info by purchase_id
            $ncc=$this->site->getCompanyByID($purchase->supplier_id);
            $this->data['ncc'] = $ncc;
            $this->data['payment_ref'] = ''; //$this->site->getReference('ppay');
            $this->data['modal_js'] = $this->site->modal_js();

            $this->load->view($this->theme . 'purchases/add_payment', $this->data);
        }
    }

    public function edit_payment($id = null)
    {
        $this->sma->checkPermissions('edit', true);
        $this->load->helper('security');
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $this->form_validation->set_rules('reference_no', lang("reference_no"), 'required');
        $this->form_validation->set_rules('amount-paid', lang("amount"), 'required');
        $this->form_validation->set_rules('paid_by', lang("paid_by"), 'required');
        $this->form_validation->set_rules('userfile', lang("attachment"), 'xss_clean');
       
        if ($this->form_validation->run() == true) {
            if ($this->Owner || $this->Admin) {
                $date = $this->sma->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }
            $payment = array(
                'date' => $date,
                'purchase_id' => $this->input->post('purchase_id'),
                'reference_no' => $this->input->post('reference_no'),
                'amount' => $this->input->post('amount-paid'),
                'paid_by' => $this->input->post('paid_by'),
                'cheque_no' => $this->input->post('cheque_no'),
                'cc_no' => $this->input->post('pcc_no'),
                'cc_holder' => $this->input->post('pcc_holder'),
                'cc_month' => $this->input->post('pcc_month'),
                'cc_year' => $this->input->post('pcc_year'),
                'cc_type' => $this->input->post('pcc_type'),
                'note' => $this->input->post('note'),
                'c_name' => $this->input->post('c_name'),
                'c_phone' => $this->input->post('c_phone'),
                'c_address' => $this->input->post('c_address'),
            );

            if ($_FILES['userfile']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] = $this->digital_upload_path;
                $config['allowed_types'] = $this->digital_file_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = false;
                $config['encrypt_name'] = true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload()) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo = $this->upload->file_name;
                $payment['attachment'] = $photo;
            }

            //$this->sma->print_arrays($payment);

        } elseif ($this->input->post('edit_payment')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }

        if ($this->form_validation->run() == true && $this->purchases_model->updatePayment($id, $payment)) {
            $this->session->set_flashdata('message', lang("payment_updated"));
            redirect("purchases");
        } else {

            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            
            $this->data['payment'] = $this->purchases_model->getPaymentByID($id);
            $this->data['modal_js'] = $this->site->modal_js();

            $this->load->view($this->theme . 'purchases/edit_payment', $this->data);
        }
    }

    public function delete_payment($id = null)
    {
        $this->sma->checkPermissions('delete', true);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        if ($this->purchases_model->deletePayment($id)) {
            //echo lang("payment_deleted");
            $this->session->set_flashdata('message', lang("payment_deleted"));
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }
	public function add_payment_lhson($id = null)
    {
        $this->sma->checkPermissions('payments', true);
        $this->load->helper('security');               

        $purchase = $this->purchases_model->getPurchaseByID($id);

        $this->form_validation->set_rules('amount-paid', lang("amount"), 'required');
        $this->form_validation->set_rules('paid_by', lang("paid_by"), 'required');
        $this->form_validation->set_rules('userfile', lang("attachment"), 'xss_clean');
		
        if ($this->form_validation->run() == true) {
            if ($this->Owner || $this->Admin) {
                $date = $this->sma->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }
            $warehouse_id=$this->session->userdata('warehouse_id');
            if ($warehouse_id==null) {
                $warehouse_id=$this->Settings->default_warehouse;
            }

            $payment = array(
                'date' => $date,
                'purchase_id' => $this->input->post('purchase_id'),
                'reference_no' => $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('ppay'),
                'amount' => $this->input->post('amount-paid'),
                'paid_by' => $this->input->post('paid_by'),
                'cheque_no' => $this->input->post('cheque_no'),
                'cc_no' => $this->input->post('pcc_no'),
                'cc_holder' => $this->input->post('pcc_holder'),
                'cc_month' => $this->input->post('pcc_month'),
                'cc_year' => $this->input->post('pcc_year'),
                'cc_type' => $this->input->post('pcc_type'),
                'note' =>$this->input->post('note'),
                'created_by' => $this->session->userdata('user_id'),
                'type' => 'sent',
                'warehouse_id'=>$warehouse_id,
				'id_ncc_id_kh' => $this->input->post('supplier_id'),
                'c_name' => $this->input->post('c_name'),
                'c_phone' => $this->input->post('c_phone'),
                'c_address' => $this->input->post('c_address'),
            );

            if ($_FILES['userfile']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] = $this->digital_upload_path;
                $config['allowed_types'] = $this->digital_file_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = false;
                $config['encrypt_name'] = true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload()) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo = $this->upload->file_name;
                $payment['attachment'] = $photo;
            }

            //$this->sma->print_arrays($payment);

        } elseif ($this->input->post('add_payment_lhson')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }

        if ($this->form_validation->run() == true && $this->purchases_model->addPaymentLhson($payment)) {
            $this->session->set_flashdata('message', lang("payment_added"));
            redirect($_SERVER["HTTP_REFERER"]);
        } else {

            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['suppliers'] = $this->site->getAllCompanies('supplier');
            $this->data['payment_ref'] = ''; //$this->site->getReference('ppay');

            //get ncc info by purchase_id
            $ncc=$this->site->getCompanyByID($purchase->supplier_id);
            $this->data['ncc'] = $ncc;

            $this->data['modal_js'] = $this->site->modal_js();

            $this->load->view($this->theme . 'purchases/add_payment_lhson', $this->data);
        }
    }
	public function edit_payment_lhson($id = null)
    {
        $this->sma->checkPermissions('edit', true);
        $this->load->helper('security');
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        
        $this->form_validation->set_rules('reference_no', lang("reference_no"), 'required');
        $this->form_validation->set_rules('amount-paid', lang("amount"), 'required');
        $this->form_validation->set_rules('paid_by', lang("paid_by"), 'required');
        $this->form_validation->set_rules('userfile', lang("attachment"), 'xss_clean');
        if ($this->form_validation->run() == true) {
            if ($this->Owner || $this->Admin) {
                $date = $this->sma->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }
             $warehouse_id=$this->session->userdata('warehouse_id');
                if ($warehouse_id==null) {
                    $warehouse_id=$this->Settings->default_warehouse;
                }
            $payment = array(
                'date' => $date,
                'purchase_id' => $this->input->post('purchase_id'),
                'reference_no' => $this->input->post('reference_no'),
                'amount' => $this->input->post('amount-paid'),
                'paid_by' => $this->input->post('paid_by'),
                'cheque_no' => $this->input->post('cheque_no'),
                'cc_no' => $this->input->post('pcc_no'),
                'cc_holder' => $this->input->post('pcc_holder'),
                'cc_month' => $this->input->post('pcc_month'),
                'cc_year' => $this->input->post('pcc_year'),
                'cc_type' => $this->input->post('pcc_type'),
                'note' => $this->input->post('note'),
				'id_ncc_id_kh' => $this->input->post('supplier_id'),
                'warehouse_id'=>$warehouse_id,
                'c_name' => $this->input->post('c_name'),
                'c_phone' => $this->input->post('c_phone'),
                'c_address' => $this->input->post('c_address'),
				
            );

            if ($_FILES['userfile']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] = $this->digital_upload_path;
                $config['allowed_types'] = $this->digital_file_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = false;
                $config['encrypt_name'] = true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload()) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo = $this->upload->file_name;
                $payment['attachment'] = $photo;
            }

            //$this->sma->print_arrays($payment);

        } elseif ($this->input->post('edit_payment')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }

        if ($this->form_validation->run() == true && $this->purchases_model->updatePaymentLhson($id, $payment)) {
            $this->session->set_flashdata('message', lang("payment_updated"));
            redirect("purchases");
        } else {

            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$this->data['suppliers'] = $this->site->getAllCompanies('supplier');
            $this->data['payment'] = $this->purchases_model->getPaymentByID($id);

           
            $this->data['modal_js'] = $this->site->modal_js();

            $this->load->view($this->theme . 'purchases/edit_payment_lhson', $this->data);
        }
    }
	public function delete_payment_lhson($id = null)
    {
        $this->sma->checkPermissions('delete', true);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        if ($this->purchases_model->deletePaymentLhson($id)) {
            //echo lang("payment_deleted");
            $this->session->set_flashdata('message', lang("payment_deleted"));
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }
	public function delete_payment_lhson_ajax($id = null)
    {
        $this->sma->checkPermissions('delete', true);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        if ($this->purchases_model->deletePaymentLhson($id)) {
            $this->sma->send_json(array('error' => 0, 'msg' => lang("expense_deleted")));
        }
		exit();
    }
    /* -------------------------------------------------------------------------------- */

    public function expenses($id = null)
    {
        $this->sma->checkPermissions('index',false,'chi');

        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('expenses')));
        $meta = array('page_title' => lang('expenses'), 'bc' => $bc);
        $this->page_construct('purchases/expenses', $meta, $this->data);
    }

    public function getExpenses()
    {
        $this->sma->checkPermissions('index',false,'chi');

        $detail_link = anchor('purchases/expense_note/$1', '<i class="fa fa-file-text-o"></i> ' . 'Chi tiết', 'data-toggle="modal" data-target="#myModal2"');
        $edit_link = anchor('purchases/edit_expense/$1', '<i class="fa fa-edit"></i> ' . lang('edit_expense'), 'data-toggle="modal" data-target="#myModal"');
		
		 $print_link = anchor('purchases/printphieuchi/$1', '<i class="fa fa-print"></i> In phiếu chi', 'data-toggle="modal" data-target="#myModal"');
        
        $delete_link = "<a href='#' class='po' title='<b>" . $this->lang->line("delete_expense") . "</b>' data-content=\"<p>"
        . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('purchases/delete_expense/$1') . "'>"
        . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
        . lang('delete_expense') . "</a>";
		
        $action = '<div class="text-center"><div class="btn-group text-left">'
        . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
        . lang('actions') . ' <span class="caret"></span></button>
        <ul class="dropdown-menu pull-right" role="menu">
            <li>' . $detail_link . '</li>
			<li>' . $print_link . '</li>
            <li>' . $edit_link . '</li>
            <li>' . $delete_link . '</li>
        </ul> 
    </div></div>';
	
	

        $this->load->library('datatables');

        $this->db->select("(CASE WHEN {$this->db->dbprefix('expenses')}.id_return!=0 THEN CONCAT({$this->db->dbprefix('expenses')}.id,'-','RT') ELSE {$this->db->dbprefix('expenses')}.id END)as id, date, reference,CONCAT({$this->db->dbprefix('users')}.first_name, ' ', {$this->db->dbprefix('users')}.last_name) as user,c_name,c_phone,c_address, {$this->db->dbprefix('expense_categories')}.name as category, amount,paid_by, note, attachment,(CASE WHEN {$this->db->dbprefix('expenses')}.id_return!=0 THEN CONCAT({$this->db->dbprefix('expenses')}.id,'-','RT') ELSE {$this->db->dbprefix('expenses')}.id END) as custom", false)
            ->from('expenses')
            ->join('users', 'users.id=expenses.created_by', 'left')
            ->join('expense_categories', 'expense_categories.id=expenses.category_id', 'left')
            ->group_by('expenses.id');
			
		$chikhac_query=$this->db->get_compiled_select();
		
		$chi_ncc_query="SELECT CONCAT(id,'-','NCC') as id,date,reference_no as reference,(SELECT CONCAT({$this->db->dbprefix('users')}.first_name, ' ', {$this->db->dbprefix('users')}.last_name) FROM {$this->db->dbprefix('users')} WHERE id=created_by) as user,c_name,c_phone,c_address,'Chi NCC' as category,amount,paid_by,note,attachment,CONCAT(id,'-','NCC') as custom FROM ".$this->db->dbprefix('payments')." WHERE type='sent'";
			
		$query_ok="SELECT tbl.* FROM ($chikhac_query UNION $chi_ncc_query) as tbl ORDER BY tbl.date desc";
		
        if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->datatables->where('created_by', $this->session->userdata('user_id'));
        }else if (!$this->Owner && !$this->Admin && $this->session->userdata('view_right')) {
            $this->datatables->where('warehouse_id', $this->session->userdata('warehouse_id'));
        }
		
        //$this->datatables->edit_column("attachment", $attachment_link, "attachment");
       // $this->datatables->add_column("Actions", $action, "id");
        echo $this->datatables->generate_querylhson($output = 'json', $charset = 'UTF-8',$query_ok);
    }

    public function expense_note($id = null)
    {
        $expense = $this->purchases_model->getExpenseByID($id);
        $this->data['user'] = $this->site->getUser($expense->created_by);
        $this->data['category'] = $expense->category_id ? $this->purchases_model->getExpenseCategoryByID($expense->category_id) : NULL;
        $this->data['warehouse'] = $expense->warehouse_id ? $this->site->getWarehouseByID($expense->warehouse_id) : NULL;
        $this->data['expense'] = $expense;
        $this->data['page_title'] = $this->lang->line("expense_note");
        $this->load->view($this->theme . 'purchases/expense_note', $this->data);
    }

    public function add_expense()
    {
        $this->sma->checkPermissions('add',true,'chi');
        $this->load->helper('security');

        //$this->form_validation->set_rules('reference', lang("reference"), 'required');
        $this->form_validation->set_rules('amount', lang("amount"), 'required');
        $this->form_validation->set_rules('userfile', lang("attachment"), 'xss_clean');
		
		$phan_loai_dt=(int)$this->input->post('phan_loai_dt');
		if($phan_loai_dt==3){
			//chi nha cung cap
			if ($this->form_validation->run() == true) {
				if ($this->Owner || $this->Admin) {
					$date = $this->sma->fld(trim($this->input->post('date')));
				} else {
					$date = date('Y-m-d H:i:s');
				}
                 $warehouse_id=$this->session->userdata('warehouse_id');
                if ($warehouse_id==null) {
                    $warehouse_id=$this->Settings->default_warehouse;
                }
				$payment = array(
					'date' => $date,
					'purchase_id' => $this->input->post('purchase_id'),
					'reference_no' => $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('ex'),
					'amount' => $this->input->post('amount'),
					'paid_by' => $this->input->post('paid_by'),
					'cheque_no' => $this->input->post('cheque_no'),
					'cc_no' => $this->input->post('pcc_no'),
					'cc_holder' => $this->input->post('pcc_holder'),
					'cc_month' => $this->input->post('pcc_month'),
					'cc_year' => $this->input->post('pcc_year'),
					'cc_type' => $this->input->post('pcc_type'),
					'note' => $this->sma->clear_tags($this->input->post('note')),
					'created_by' => $this->session->userdata('user_id'),
					'type' => 'sent',
                    'warehouse_id'=>$warehouse_id,
					'id_ncc_id_kh' => $this->input->post('suppliers_id'),
                    'c_name' => $this->input->post('c_name'),
                    'c_phone' => $this->input->post('c_phone'),
                    'c_address' => $this->input->post('c_address'),
				);

				if ($_FILES['userfile']['size'] > 0) {
					$this->load->library('upload');
					$config['upload_path'] = $this->digital_upload_path;
					$config['allowed_types'] = $this->digital_file_types;
					$config['max_size'] = $this->allowed_file_size;
					$config['overwrite'] = false;
					$config['encrypt_name'] = true;
					$this->upload->initialize($config);
					if (!$this->upload->do_upload()) {
						$error = $this->upload->display_errors();
						$this->session->set_flashdata('error', $error);
						redirect($_SERVER["HTTP_REFERER"]);
					}
					$photo = $this->upload->file_name;
					$payment['attachment'] = $photo;
				}

				//$this->sma->print_arrays($payment);

			} elseif ($this->input->post('add_expense')) {
				$this->session->set_flashdata('error', validation_errors());
				redirect($_SERVER["HTTP_REFERER"]);
			}

			if ($this->form_validation->run() == true && $this->purchases_model->addPaymentLhson($payment)) {
				$this->session->set_flashdata('message', lang("expense_added"));
				redirect($_SERVER["HTTP_REFERER"]);
			} else {
				$this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
				$this->data['exnumber'] = ''; //$this->site->getReference('ex');
				$this->data['warehouses'] = $this->site->getAllWarehouses();
				$this->data['categories'] = $this->purchases_model->getExpenseCategories();
				$this->data['modal_js'] = $this->site->modal_js();
				$this->load->view($this->theme . 'purchases/add_expense', $this->data);
			}
			
		}else{
			
			if ($this->form_validation->run() == true) {
				if ($this->Owner || $this->Admin) {
					$date = $this->sma->fld(trim($this->input->post('date')));
				} else {
					$date = date('Y-m-d H:i:s');
				}
				 $warehouse_id=$this->session->userdata('warehouse_id');
                if ($warehouse_id==null) {
                    $warehouse_id=$this->Settings->default_warehouse;
                }

				$data = array(
					'date' => $date,
					'reference' => $this->input->post('reference') ? $this->input->post('reference') : $this->site->getReference('ex'),
					'amount' => $this->input->post('amount'),
					'created_by' => $this->session->userdata('user_id'),
					'note' => $this->input->post('note', true),
					'category_id' => $this->input->post('category', true),
					'warehouse_id' => $warehouse_id,
					'paid_by' => $this->input->post('paid_by'),
					'cheque_no' => $this->input->post('cheque_no'),
					'cc_no' => $this->input->post('pcc_no'),
					'cc_holder' => $this->input->post('pcc_holder'),
					'cc_month' => $this->input->post('pcc_month'),
					'cc_year' => $this->input->post('pcc_year'),
					'cc_type' => $this->input->post('pcc_type'),
                    'c_name' => $this->input->post('c_name'),
                    'c_phone' => $this->input->post('c_phone'),
                    'c_address' => $this->input->post('c_address'),                    
                    'is_doanhthu' => isset($_POST['is_doanhthu'])?1:0,
				);
				if($phan_loai_dt==1){
					$data['nhanvien_id']=$this->input->post('nhanvien_id', true);
				}else if($phan_loai_dt==2){
					$data['customer_id']=$this->input->post('customer_id', true);
				}else if($phan_loai_dt==9){
					$data['doitac']=$this->input->post('doitac', true);
				}
				if ($_FILES['userfile']['size'] > 0) {
					$this->load->library('upload');
					$config['upload_path'] = $this->upload_path;
					$config['allowed_types'] = $this->digital_file_types;
					$config['max_size'] = $this->allowed_file_size;
					$config['overwrite'] = false;
					$config['encrypt_name'] = true;
					$this->upload->initialize($config);
					if (!$this->upload->do_upload()) {
						$error = $this->upload->display_errors();
						$this->session->set_flashdata('error', $error);
						redirect($_SERVER["HTTP_REFERER"]);
					}
					$photo = $this->upload->file_name;
					$data['attachment'] = $photo;
				}

				//$this->sma->print_arrays($data);

			} elseif ($this->input->post('add_expense')) {
				$this->session->set_flashdata('error', validation_errors());
				redirect($_SERVER["HTTP_REFERER"]);
			}

			if ($this->form_validation->run() == true && $this->purchases_model->addExpense($data)) {
				$this->session->set_flashdata('message', lang("expense_added"));
				redirect($_SERVER["HTTP_REFERER"]);
			} else {
				$this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
				$this->data['exnumber'] = ''; //$this->site->getReference('ex');
				$this->data['warehouses'] = $this->site->getAllWarehouses();
				$this->data['categories'] = $this->purchases_model->getExpenseCategories();
				$this->data['modal_js'] = $this->site->modal_js();
				$this->load->view($this->theme . 'purchases/add_expense', $this->data);
			}
		}
    }

    public function edit_expense($id = null)
    {
        $this->sma->checkPermissions('edit',true,'chi');
        $this->load->helper('security');
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
		$kiemtra_thuhoi=$this->purchases_model->getExpenseByID($id);
		if($kiemtra_thuhoi->id_return>0){
			$this->session->set_flashdata('error','Phiếu chi thu hồi không thể chỉnh sửa, vui lòng chỉnh sửa thu hồi');
			redirect($_SERVER["HTTP_REFERER"]);
		}else{
		
			$this->form_validation->set_rules('reference', lang("reference"), 'required');
			$this->form_validation->set_rules('amount', lang("amount"), 'required');
			$this->form_validation->set_rules('userfile', lang("attachment"), 'xss_clean');
			$phan_loai_dt=(int)$this->input->post('phan_loai_dt');
			if($phan_loai_dt==3){
				//huy chi hien tai
				if(!$this->purchases_model->deleteExpense($id)){
					$this->session->set_flashdata('error','Lỗi hủy bỏ phiếu chi');
					redirect($_SERVER["HTTP_REFERER"]);
				}else{
					//them payment moi
					if ($this->form_validation->run() == true) {
						if ($this->Owner || $this->Admin) {
							$date = $this->sma->fld(trim($this->input->post('date')));
						} else {
							$date = date('Y-m-d H:i:s');
						}
                        $warehouse_id=$this->session->userdata('warehouse_id');
                        if ($warehouse_id==null) {
                            $warehouse_id=$this->Settings->default_warehouse;
                        }

						$payment = array(
							'date' => $date,
							'purchase_id' => $this->input->post('purchase_id'),
							'reference_no' => $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('ex'),
							'amount' => $this->input->post('amount'),
							'paid_by' => $this->input->post('paid_by'),
							'cheque_no' => $this->input->post('cheque_no'),
							'cc_no' => $this->input->post('pcc_no'),
							'cc_holder' => $this->input->post('pcc_holder'),
							'cc_month' => $this->input->post('pcc_month'),
							'cc_year' => $this->input->post('pcc_year'),
							'cc_type' => $this->input->post('pcc_type'),
							'note' => $this->sma->clear_tags($this->input->post('note')),
							'created_by' => $this->session->userdata('user_id'),
							'type' => 'sent',
                            'warehouse_id'=>$warehouse_id,
							'id_ncc_id_kh' => $this->input->post('suppliers_id'),
                            'c_name' => $this->input->post('c_name'),
                            'c_phone' => $this->input->post('c_phone'),
                            'c_address' => $this->input->post('c_address'),
						);

						if ($_FILES['userfile']['size'] > 0) {
							$this->load->library('upload');
							$config['upload_path'] = $this->digital_upload_path;
							$config['allowed_types'] = $this->digital_file_types;
							$config['max_size'] = $this->allowed_file_size;
							$config['overwrite'] = false;
							$config['encrypt_name'] = true;
							$this->upload->initialize($config);
							if (!$this->upload->do_upload()) {
								$error = $this->upload->display_errors();
								$this->session->set_flashdata('error', $error);
								redirect($_SERVER["HTTP_REFERER"]);
							}
							$photo = $this->upload->file_name;
							$payment['attachment'] = $photo;
						}

						//$this->sma->print_arrays($payment);

					} elseif ($this->input->post('add_expense')) {
						$this->session->set_flashdata('error', validation_errors());
						redirect($_SERVER["HTTP_REFERER"]);
					}

					if ($this->form_validation->run() == true && $this->purchases_model->addPaymentLhson($payment)) {
						$this->session->set_flashdata('message', lang("expense_added"));
						redirect($_SERVER["HTTP_REFERER"]);
					} else {

						$this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
						$this->data['exnumber'] = ''; //$this->site->getReference('ex');
						$this->data['warehouses'] = $this->site->getAllWarehouses();
						$this->data['categories'] = $this->purchases_model->getExpenseCategories();
						$this->data['modal_js'] = $this->site->modal_js();
						$this->load->view($this->theme . 'purchases/add_expense', $this->data);
					}
				}
			}
			else{
				
				if ($this->form_validation->run() == true) {
					if ($this->Owner || $this->Admin) {
						$date = $this->sma->fld(trim($this->input->post('date')));
					} else {
						$date = date('Y-m-d H:i:s');
					}

                    $warehouse_id=$this->session->userdata('warehouse_id');
                    if ($warehouse_id==null) {
                        $warehouse_id=$this->Settings->default_warehouse;
                    }
					$data = array(
						'date' => $date,
						'reference' => $this->input->post('reference'),
						'amount' => $this->input->post('amount'),
						'note' => $this->input->post('note', true),
						'category_id' => $this->input->post('category', true),
						'warehouse_id' => $warehouse_id,
						'paid_by' => $this->input->post('paid_by'),
						'cheque_no' => $this->input->post('cheque_no'),
						'cc_no' => $this->input->post('pcc_no'),
						'cc_holder' => $this->input->post('pcc_holder'),
						'cc_month' => $this->input->post('pcc_month'),
						'cc_year' => $this->input->post('pcc_year'),
						'cc_type' => $this->input->post('pcc_type'),
                        'c_name' => $this->input->post('c_name'),
                        'c_phone' => $this->input->post('c_phone'),
                        'c_address' => $this->input->post('c_address'),                        
                        'is_doanhthu' => isset($_POST['is_doanhthu'])?1:0,
					);
					if($phan_loai_dt==1){
						$data['nhanvien_id']=$this->input->post('nhanvien_id', true);
					}else if($phan_loai_dt==2){
						$data['customer_id']=$this->input->post('customer_id', true);
					}else if($phan_loai_dt==9){
						$data['doitac']=$this->input->post('doitac', true);
					}
					
					if ($_FILES['userfile']['size'] > 0) {
						$this->load->library('upload');
						$config['upload_path'] = $this->upload_path;
						$config['allowed_types'] = $this->digital_file_types;
						$config['max_size'] = $this->allowed_file_size;
						$config['overwrite'] = false;
						$config['encrypt_name'] = true;
						$this->upload->initialize($config);
						if (!$this->upload->do_upload()) {
							$error = $this->upload->display_errors();
							$this->session->set_flashdata('error', $error);
							redirect($_SERVER["HTTP_REFERER"]);
						}
						$photo = $this->upload->file_name;
						$data['attachment'] = $photo;
					}

				  //  $this->sma->print_arrays($data);

				} elseif ($this->input->post('edit_expense')) {
					$this->session->set_flashdata('error', validation_errors());
					redirect($_SERVER["HTTP_REFERER"]);
				}

				if ($this->form_validation->run() == true && $this->purchases_model->updateExpense($id, $data)) {
					$this->session->set_flashdata('message', lang("expense_updated"));
					redirect("purchases/expenses");
				} else {
					$this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
					$this->data['expense'] = $this->purchases_model->getExpenseByID($id);
					$this->data['warehouses'] = $this->site->getAllWarehouses();
					$this->data['modal_js'] = $this->site->modal_js();
					$this->data['categories'] = $this->purchases_model->getExpenseCategories();
					$this->load->view($this->theme . 'purchases/edit_expense', $this->data);
				}
			}
		}
    }

    public function delete_expense($id = null)
    {
        $this->sma->checkPermissions('delete',true,'chi');

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $expense = $this->purchases_model->getExpenseByID($id);
		if($expense->id_return>0){
			$this->sma->send_json(array('error' => 0, 'msg' =>'Phiếu chi thu hồi không thể chỉnh sửa, vui lòng chỉnh sửa thu hồi'));
			
		}else{
		
			if ($this->purchases_model->deleteExpense($id)) {
				if ($expense->attachment) {
					unlink($this->upload_path . $expense->attachment);
				}
				$this->sma->send_json(array('error' => 0, 'msg' => lang("expense_deleted")));
			}
		}
    }
	 public function add_phieuthu()
    {
        $this->sma->checkPermissions('index',true,'thu');
        $this->load->helper('security');

        //$this->form_validation->set_rules('reference', lang("reference"), 'required');
        $this->form_validation->set_rules('amount', lang("amount"), 'required');
        $this->form_validation->set_rules('userfile', lang("attachment"), 'xss_clean');
		
		$phan_loai_dt=(int)$this->input->post('phan_loai_dt');
		if($phan_loai_dt==2){
			//Thu cong no khach hang	
			$customer_id=$data['id_ncc_id_kh']=$this->input->post('customer_id', true);	
			 if ($this->form_validation->run() == true) {
				if ($this->input->post('paid_by') == 'deposit') {
					if ( ! $this->site->check_customer_deposit($customer_id, $this->input->post('amount'))) {
						$this->session->set_flashdata('error', lang("amount_greater_than_deposit"));
						redirect($_SERVER["HTTP_REFERER"]);
					}
				}
				if ($this->Owner || $this->Admin) {
					$date = $this->sma->fld(trim($this->input->post('date')));
				} else {
					$date = date('Y-m-d H:i:s');
				}
				$warehouse_id=$this->session->userdata('warehouse_id');
                if ($warehouse_id==null) {
                    $warehouse_id=$this->Settings->default_warehouse;
                }

				$payment = array(
						'date' => $date,
						'sale_id' => $this->input->post('sale_id'),
						'reference_no' => $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('thu'),
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
						'id_ncc_id_kh' =>$customer_id,
						'warehouse_id' => $warehouse_id,
                        'c_name' => $this->input->post('c_name'),
                        'c_phone' => $this->input->post('c_phone'),
                        'c_address' => $this->input->post('c_address'),
                        'is_doanhthu' => isset($_POST['is_doanhthu'])?1:0,
									);

				if ($_FILES['userfile']['size'] > 0) {
					$this->load->library('upload');
					$config['upload_path'] = $this->digital_upload_path;
					$config['allowed_types'] = $this->digital_file_types;
					$config['max_size'] = $this->allowed_file_size;
					$config['overwrite'] = false;
					$config['encrypt_name'] = true;
					$this->upload->initialize($config);
					if (!$this->upload->do_upload()) {
						$error = $this->upload->display_errors();
						$this->session->set_flashdata('error', $error);
						redirect($_SERVER["HTTP_REFERER"]);
					}
					$photo = $this->upload->file_name;
					$payment['attachment'] = $photo;
				}

				//$this->sma->print_arrays($payment);

			} elseif ($this->input->post('add_phieuthukh')) {
				
				$this->session->set_flashdata('error', validation_errors());
			   redirect($_SERVER["HTTP_REFERER"]);
			}
			if ($this->form_validation->run() == true && $this->sales_model->addPaymentLhson($payment, $customer_id)) {
				$this->session->set_flashdata('message','Thêm khoản thu thành công');
				redirect($_SERVER["HTTP_REFERER"]);
			} else {

				$this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
				$this->data['categories'] = $this->purchases_model->getExpenseCategoriesThu();
				$this->data['modal_js'] = $this->site->modal_js();
				$this->data['warehouses'] = $this->site->getAllWarehouses();
				$this->load->view($this->theme . 'purchases/add_phieuthu', $this->data);
			}
			
		}else{
			
			if ($this->form_validation->run() == true) {
				if ($this->Owner || $this->Admin) {
					$date = $this->sma->fld(trim($this->input->post('date')));
				} else {
					$date = date('Y-m-d H:i:s');
				}
                 $warehouse_id=$this->session->userdata('warehouse_id');
                if ($warehouse_id==null) {
                    $warehouse_id=$this->Settings->default_warehouse;
                }

				$data = array(
					'date' => $date,
					'sale_id' => $this->input->post('sale_id'),
					'reference_no' => $this->input->post('reference') ? $this->input->post('reference') : $this->site->getReference('thu'),
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
					'type' => $this->input->post('phan_loai_dt', true),
					'type_cate' => $this->input->post('category', true),
					'warehouse_id' => $warehouse_id,
                    'c_name' => $this->input->post('c_name'),
                    'c_phone' => $this->input->post('c_phone'),
                    'c_address' => $this->input->post('c_address'),
                    'is_doanhthu' => isset($_POST['is_doanhthu'])?1:0,
				);
				$customer_id=0;
				if($phan_loai_dt==1){
					$customer_id=$data['id_ncc_id_kh']=$this->input->post('nhanvien_id', true);
				}else if($phan_loai_dt==3){
					$customer_id=$data['id_ncc_id_kh']=$this->input->post('suppliers_id', true);
				}
				if ($_FILES['userfile']['size'] > 0) {
					$this->load->library('upload');
					$config['upload_path'] = $this->upload_path;
					$config['allowed_types'] = $this->digital_file_types;
					$config['max_size'] = $this->allowed_file_size;
					$config['overwrite'] = false;
					$config['encrypt_name'] = true;
					$this->upload->initialize($config);
					if (!$this->upload->do_upload()) {
						$error = $this->upload->display_errors();
						$this->session->set_flashdata('error', $error);
						redirect($_SERVER["HTTP_REFERER"]);
					}
					$photo = $this->upload->file_name;
					$data['attachment'] = $photo;
				}

				//$this->sma->print_arrays($data);

			} elseif ($this->input->post('add_phieuthu')) {
				$this->session->set_flashdata('error', validation_errors());
				redirect($_SERVER["HTTP_REFERER"]);
			}

			if ($this->form_validation->run() == true && $this->sales_model->addPaymentLhson($data, $customer_id)) {
				$this->session->set_flashdata('message', 'Thêm khoản thu thành công');
				redirect($_SERVER["HTTP_REFERER"]);
			} else {
				$this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
				
				$this->data['categories'] = $this->purchases_model->getExpenseCategoriesThu();
				$this->data['modal_js'] = $this->site->modal_js();
				$this->data['warehouses'] = $this->site->getAllWarehouses();
				$this->load->view($this->theme . 'purchases/add_phieuthu', $this->data);
			}
		}
    }
	public function edit_phieuthu($id = null)
    {
        $this->sma->checkPermissions('edit',true,'thu');
		
        $this->load->helper('security');
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        $this->form_validation->set_rules('reference', lang("reference"), 'required');
        $this->form_validation->set_rules('amount', lang("amount"), 'required');
        $this->form_validation->set_rules('userfile', lang("attachment"), 'xss_clean');
		$phan_loai_dt=(int)$this->input->post('phan_loai_dt');
		if($phan_loai_dt==2){
			//la thu khach hang
			$customer_id=$data['id_ncc_id_kh']=$this->input->post('customer_id', true);	
			 if ($this->form_validation->run() == true) {
				if ($this->input->post('paid_by') == 'deposit') {
					if ( ! $this->site->check_customer_deposit($customer_id, $this->input->post('amount'))) {
						$this->session->set_flashdata('error', lang("amount_greater_than_deposit"));
						redirect($_SERVER["HTTP_REFERER"]);
					}
				}
				if ($this->Owner || $this->Admin) {
					$date = $this->sma->fld(trim($this->input->post('date')));
				} else {
					$date = date('Y-m-d H:i:s');
				}
				 $warehouse_id=$this->session->userdata('warehouse_id');
                if ($warehouse_id==null) {
                    $warehouse_id=$this->Settings->default_warehouse;
                }
				$payment = array(
						'date' => $date,
						'sale_id' => $this->input->post('sale_id'),
						'reference_no' => $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('thu'),
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
						'id_ncc_id_kh' =>$customer_id,
						'warehouse_id' => $warehouse_id,
                        'c_name' => $this->input->post('c_name'),
                        'c_phone' => $this->input->post('c_phone'),
                        'c_address' => $this->input->post('c_address'),
                        'is_doanhthu' => isset($_POST['is_doanhthu'])?1:0,
									);

				if ($_FILES['userfile']['size'] > 0) {
					$this->load->library('upload');
					$config['upload_path'] = $this->digital_upload_path;
					$config['allowed_types'] = $this->digital_file_types;
					$config['max_size'] = $this->allowed_file_size;
					$config['overwrite'] = false;
					$config['encrypt_name'] = true;
					$this->upload->initialize($config);
					if (!$this->upload->do_upload()) {
						$error = $this->upload->display_errors();
						$this->session->set_flashdata('error', $error);
						redirect($_SERVER["HTTP_REFERER"]);
					}
					$photo = $this->upload->file_name;
					$payment['attachment'] = $photo;
				}

				//$this->sma->print_arrays($payment);

			} elseif ($this->input->post('add_phieuthukh')) {
				
				$this->session->set_flashdata('error', validation_errors());
			   redirect($_SERVER["HTTP_REFERER"]);
			}
			if ($this->form_validation->run() == true && $this->sales_model->updatePaymentLhson($id, $payment, $customer_id)) {
				$this->session->set_flashdata('message', 'Cập nhật phiếu thu thành công');
				redirect("reports/khoanthu");
			} else {
				$this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
				$this->data['expense'] = $payment = $this->sales_model->getPaymentByID($id);				
				$this->data['modal_js'] = $this->site->modal_js();
				$this->data['warehouses'] = $this->site->getAllWarehouses();
				$this->data['categories'] = $this->purchases_model->getExpenseCategoriesThu();
				$this->load->view($this->theme . 'purchases/edit_phieuthu', $this->data);
			}			
		}
		else{
			
			if ($this->form_validation->run() == true) {
				if ($this->Owner || $this->Admin) {
					$date = $this->sma->fld(trim($this->input->post('date')));
				} else {
					$date = date('Y-m-d H:i:s');
				}
                 $warehouse_id=$this->session->userdata('warehouse_id');
                if ($warehouse_id==null) {
                    $warehouse_id=$this->Settings->default_warehouse;
                }

				$data = array(
					'date' => $date,
					'sale_id' => $this->input->post('sale_id'),
					'reference_no' => $this->input->post('reference') ? $this->input->post('reference') : $this->site->getReference('thu'),
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
					'type' => $this->input->post('phan_loai_dt', true),
					'type_cate' => $this->input->post('category', true),
					'warehouse_id' => $warehouse_id,
                    'c_name' => $this->input->post('c_name'),
                    'c_phone' => $this->input->post('c_phone'),
                    'c_address' => $this->input->post('c_address'),
                    'is_doanhthu' => isset($_POST['is_doanhthu'])?1:0,
				);
				$customer_id=0;
				if($phan_loai_dt==1){
					$customer_id=$data['id_ncc_id_kh']=$this->input->post('nhanvien_id', true);
				}else if($phan_loai_dt==3){
					$customer_id=$data['id_ncc_id_kh']=$this->input->post('suppliers_id', true);
				}
				if ($_FILES['userfile']['size'] > 0) {
					$this->load->library('upload');
					$config['upload_path'] = $this->upload_path;
					$config['allowed_types'] = $this->digital_file_types;
					$config['max_size'] = $this->allowed_file_size;
					$config['overwrite'] = false;
					$config['encrypt_name'] = true;
					$this->upload->initialize($config);
					if (!$this->upload->do_upload()) {
						$error = $this->upload->display_errors();
						$this->session->set_flashdata('error', $error);
						redirect($_SERVER["HTTP_REFERER"]);
					}
					$photo = $this->upload->file_name;
					$data['attachment'] = $photo;
				}

				//$this->sma->print_arrays($data);

			} elseif ($this->input->post('edit_phieuthu')) {
				$this->session->set_flashdata('error', validation_errors());
				redirect($_SERVER["HTTP_REFERER"]);
			}

			if ($this->form_validation->run() == true && $this->sales_model->updatePaymentLhson($id, $data, $customer_id)) {
				$this->session->set_flashdata('message', 'Cập nhật phiếu thu thành công');
				redirect("reports/khoanthu");
			} else {
				$this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
				$this->data['expense'] = $payment = $this->sales_model->getPaymentByID($id);				
				$this->data['modal_js'] = $this->site->modal_js();
				$this->data['warehouses'] = $this->site->getAllWarehouses();
				$this->data['categories'] = $this->purchases_model->getExpenseCategoriesThu();
				$this->load->view($this->theme . 'purchases/edit_phieuthu', $this->data);
			}
		}
    }
	public function delete_phieuthu_ajax($id = null)
    {
        $this->sma->checkPermissions('delete',true,'thu');

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $expense = $this->sales_model->getPaymentByID($id);	
		
        if ($this->sales_model->deletePayment($id)) {
            if ($expense->attachment) {
                unlink($this->upload_path . $expense->attachment);
            }
            $this->sma->send_json(array('error' => 0, 'msg' => 'Xóa phiếu thu thành công'));
        }
    }
    public function expense_actions()
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
						$flag=true;
						if (strpos($id, '-NCC') !== false) {
							//tien hanh xoa payment
							$flag=false;	
							$ex_id=explode("-NCC",$id);
							$this->purchases_model->deletePaymentLhson($ex_id[0]);
						}
						if (strpos($id, '-RT') !== false) {
							 $this->session->set_flashdata('message','Không thể xóa phiếu chi thu hồi, vui lòng xóa thu hồi');
							redirect($_SERVER["HTTP_REFERER"]);
						}
						if($flag){
							$this->purchases_model->deleteExpense($id);
						}
                    }
                    $this->session->set_flashdata('message', $this->lang->line("expenses_deleted"));
                    redirect($_SERVER["HTTP_REFERER"]);
                }

                if ($this->input->post('form_action') == 'export_excel' || $this->input->post('form_action') == 'export_pdf') {

                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('expenses'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('reference'));
					$this->excel->getActiveSheet()->SetCellValue('C1', lang('Nhân viên'));
                    $this->excel->getActiveSheet()->SetCellValue('D1', lang('Đối tượng'));
					$this->excel->getActiveSheet()->SetCellValue('E1', lang('amount'));
					$this->excel->getActiveSheet()->SetCellValue('F1', lang('TT Bằng'));
                    $this->excel->getActiveSheet()->SetCellValue('G1', lang('note'));

                    $row = 2;
                    foreach ($_POST['val'] as $id) {
						 $expense = null;
						$flag=true;
						if (strpos($id, '-NCC') !== false) {
							//tien hanh xoa payment
							$flag=false;	
							$ex_id=explode("-NCC",$id);
							$expense = $this->purchases_model->getExpensePaymentLhsonByID($ex_id[0]);
						}
						if($flag){
							 $expense = $this->purchases_model->getExpenseByID($id);
						}
                       
					   $user = $this->site->getUser($expense->created_by);
					   $doituong="";
					   if($expense->customer_id>0){
							$customer_id =$this->companies_model->getCompanyByID($expense->customer_id);
							$doituong=$customer_id->name.'-'.$customer_id->phone;
					   }else if($expense->doitac>0){
							$customer_id =$this->doitac_model->getDoitacByID($expense->doitac);
							$doituong=$customer_id->code.'-'.$customer_id->name;
					   }else if($expense->nhanvien_id>0){
						   $customer_id = $this->site->getUser($expense->nhanvien_id);
							$doituong=$customer_id->first_name.' '.$customer_id->last_name;
					   }
						
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->sma->hrld($expense->date));
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $expense->reference);
						$this->excel->getActiveSheet()->SetCellValue('C' . $row, $user->first_name . ' ' . $user->last_name);
                        $this->excel->getActiveSheet()->SetCellValue('D' . $row, $doituong);
						$this->excel->getActiveSheet()->SetCellValue('E' . $row, $this->sma->formatMoney($expense->amount));
						$this->excel->getActiveSheet()->SetCellValue('F' . $row, lang($expense->paid_by));
                        $this->excel->getActiveSheet()->SetCellValue('G' . $row, $expense->note);
                        $row++;
                    }

                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(30);
                    $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'expenses_' . date('Y_m_d_H_i_s');
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
                $this->session->set_flashdata('error', $this->lang->line("no_expense_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }

    public function view_return($id = null)
    {
        $this->sma->checkPermissions('return_purchases');

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $inv = $this->purchases_model->getReturnByID($id);
        if (!$this->session->userdata('view_right')) {
            $this->sma->view_rights($inv->created_by);
        }
        $this->data['barcode'] = "<img src='" . site_url('products/gen_barcode/' . $inv->reference_no) . "' alt='" . $inv->reference_no . "' class='pull-left' />";
        $this->data['supplier'] = $this->site->getCompanyByID($inv->supplier_id);
        $this->data['payments'] = $this->purchases_model->getPaymentsForPurchase($id);
        $this->data['user'] = $this->site->getUser($inv->created_by);
        $this->data['warehouse'] = $this->site->getWarehouseByID($inv->warehouse_id);
        $this->data['inv'] = $inv;
        $this->data['rows'] = $this->purchases_model->getAllReturnItems($id);
        $this->data['purchase'] = $this->purchases_model->getPurchaseByID($inv->purchase_id);
        $this->load->view($this->theme.'purchases/view_return', $this->data);
    }

    public function return_purchase($id = null)
    {
        $this->sma->checkPermissions('return_purchases');

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        $purchase = $this->purchases_model->getPurchaseByID($id);
        if ($purchase->return_id) {
            $this->session->set_flashdata('error','Đơn hàng đã được hoàn lại trước đó');
            redirect($_SERVER["HTTP_REFERER"]);
        }
        $this->form_validation->set_rules('return_surcharge', lang("return_surcharge"), 'required');
		$doitac=$this->input->post('doitac');
		$payment_status=$this->input->post('payment_status');
        if ($this->form_validation->run() == true) {
				
            $reference = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('rep');
            if ($this->Owner || $this->Admin) {
                $date = $this->sma->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }

            $return_surcharge = $this->input->post('return_surcharge') ? $this->input->post('return_surcharge') : 0;
            $note = $this->sma->clear_tags($this->input->post('note'));

            $total = 0;
            $product_tax = 0;
            $order_tax = 0;
            $product_discount = 0;
            $order_discount = 0;
            $percentage = '%';
            $i = isset($_POST['product']) ? sizeof($_POST['product']) : 0;
            for ($r = 0; $r < $i; $r++) {
                $item_id = $_POST['product_id'][$r];
                $item_code = $_POST['product'][$r];
                $purchase_item_id = $_POST['purchase_item_id'][$r];
                $item_option = isset($_POST['product_option'][$r]) && !empty($_POST['product_option'][$r]) && $_POST['product_option'][$r] != 'false' ? $_POST['product_option'][$r] : null;
                $real_unit_cost = $this->sma->formatDecimal($_POST['real_unit_cost'][$r]);
                $unit_cost = $this->sma->formatDecimal($_POST['unit_cost'][$r]);
                $item_unit_quantity = (0-$_POST['quantity'][$r]);
                $item_expiry = isset($_POST['expiry'][$r]) ? $_POST['expiry'][$r] : '';
                $item_tax_rate = isset($_POST['product_tax'][$r]) ? $_POST['product_tax'][$r] : null;
                $item_discount = isset($_POST['product_discount'][$r]) ? $_POST['product_discount'][$r] : null;
                $item_unit = $_POST['product_unit'][$r];
                $item_quantity = (0-$_POST['product_base_quantity'][$r]);

                if (isset($item_code) && isset($real_unit_cost) && isset($unit_cost) && isset($item_quantity)) {
                    $product_details = $this->purchases_model->getProductByCode($item_code);

                    $item_type = $product_details->type;
                    $item_name = $product_details->name;

                    if (isset($item_discount)) {
                        $discount = $item_discount;
                        $dpos = strpos($discount, $percentage);
                        if ($dpos !== false) {
                            $pds = explode("%", $discount);
                            $pr_discount = $this->sma->formatDecimal(((($this->sma->formatDecimal($unit_cost)) * (Float) ($pds[0])) / 100), 4);
                        } else {
                            $pr_discount = $this->sma->formatDecimal($discount);
                        }
                    } else {
                        $pr_discount = 0;
                    }
                    // $unit_cost = $this->sma->formatDecimal($unit_cost - $pr_discount);
                    $pr_item_discount = $this->sma->formatDecimal(($pr_discount * $item_unit_quantity), 4);
                    $product_discount += $pr_item_discount;

                    if (isset($item_tax_rate) && $item_tax_rate != 0) {
                        $pr_tax = $item_tax_rate;
                        $tax_details = $this->site->getTaxRateByID($pr_tax);
                        if ($tax_details->type == 1 && $tax_details->rate != 0) {

                            if (!$product_details->tax_method) {
                                $item_tax = $this->sma->formatDecimal((($unit_cost) * $tax_details->rate) / (100 + $tax_details->rate), 4);
                                $tax = $tax_details->rate . "%";
                            } else {
                                $item_tax = $this->sma->formatDecimal((($unit_cost) * $tax_details->rate) / 100, 4);
                                $tax = $tax_details->rate . "%";
                            }

                        } elseif ($tax_details->type == 2) {

                            $item_tax = $this->sma->formatDecimal($tax_details->rate);
                            $tax = $tax_details->rate;

                        }
                        $pr_item_tax = $this->sma->formatDecimal($item_tax * $item_unit_quantity, 4);

                    } else {
                        $pr_tax = 0;
                        $pr_item_tax = 0;
                        $tax = "";
                    }

                    $item_net_cost = $product_details->tax_method ? $this->sma->formatDecimal(($unit_cost - $pr_discount), 4) : $this->sma->formatDecimal(($unit_cost - $item_tax - $pr_discount), 4);
                    $product_tax += $pr_item_tax;
                    $subtotal = $this->sma->formatDecimal((($item_net_cost * $item_unit_quantity) + $pr_item_tax), 4);
                    $unit = $this->site->getUnitByID($item_unit);

                    $products[] = array(
                        'product_id' => $item_id,
                        'product_code' => $item_code,
                        'product_name' => $item_name,
                        'option_id' => $item_option,
                        'net_unit_cost' => $item_net_cost,
                        'unit_cost' => $this->sma->formatDecimal($item_net_cost + $item_tax),
                        'quantity' => $item_quantity,
                        'product_unit_id' => $item_unit,
                        'product_unit_code' => $unit->code,
                        'unit_quantity' => $item_unit_quantity,
                        'quantity_balance' => $item_quantity,
                        'warehouse_id' => $purchase->warehouse_id,
                        'item_tax' => $pr_item_tax,
                        'tax_rate_id' => $pr_tax,
                        'tax' => $tax,
                        'discount' => $item_discount,
                        'item_discount' => $pr_item_discount,
                        'subtotal' => $this->sma->formatDecimal($subtotal),
                        'real_unit_cost' => $real_unit_cost,
                        'purchase_item_id' => $purchase_item_id,
                        'status' => 'received',
                    );

                    $total += $this->sma->formatDecimal(($item_net_cost * $item_unit_quantity), 4);
                }
            }
            if (empty($products)) {
                $this->form_validation->set_rules('product', lang("order_items"), 'required');
            } else {
                krsort($products);
            }

            if ($this->input->post('discount')) {
                $order_discount_id = $this->input->post('order_discount');
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
            $total_discount = $order_discount + $product_discount;

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
                $order_tax_id = null;
            }

            $total_tax = $this->sma->formatDecimal(($product_tax + $order_tax), 4);
            $grand_total = $this->sma->formatDecimal(($total + $total_tax + $this->sma->formatDecimal($return_surcharge) - $order_discount), 4);
            $data = array('date' => $date,
                'purchase_id' => $id,
                'reference_no' => $purchase->reference_no,
                'supplier_id' => $purchase->supplier_id,
                'supplier' => $purchase->supplier,
                'warehouse_id' => $purchase->warehouse_id,
				'doitac' => $doitac,
                'note' => $note,
                'total' => $total,
                'product_discount' => $product_discount,
                'order_discount_id' => $order_discount_id,
                'order_discount' => $order_discount,
                'total_discount' => $total_discount,
                'product_tax' => $product_tax,
                'order_tax_id' => $order_tax_id,
                'order_tax' => $order_tax,
                'total_tax' => $total_tax,
                'surcharge' => $this->sma->formatDecimal($return_surcharge),
                'grand_total' => $grand_total,
                'created_by' => $this->session->userdata('user_id'),
                'return_purchase_ref' => $reference,
                'status' => 'returned',
                'payment_status' => $payment_status,
            );
			$payment = array();
			if ($payment_status == 'partial' || $payment_status == 'paid') {
				
				if ($this->input->post('paid_by') == 'gift_card') {
					$gc = $this->site->getGiftCardByNO($this->input->post('gift_card_no'));
					$amount_paying = $grand_total >= $gc->balance ? $gc->balance : $grand_total;
					$gc_balance = $gc->balance - $amount_paying;
					$payment = array(
						'date' => $date,
						'reference_no' => $this->input->post('payment_reference_no'),
						'amount' => $this->sma->formatDecimal($amount_paying),
						'paid_by' => $this->input->post('paid_by'),
						'cheque_no' => $this->input->post('cheque_no'),
						'cc_no' => $this->input->post('gift_card_no'),
						'cc_holder' => $this->input->post('pcc_holder'),
						'cc_month' => $this->input->post('pcc_month'),
						'cc_year' => $this->input->post('pcc_year'),
						'cc_type' => $this->input->post('pcc_type'),
						'created_by' => $this->session->userdata('user_id'),
						'note' => $this->input->post('payment_note'),
						'type' => 'sent',
                        'warehouse_id'=>$purchase->warehouse_id,
						'gc_balance' => $gc_balance,
						'id_ncc_id_kh' =>$purchase->supplier_id,
						
					);
					
				} else {
					$payment = array(
						'date' => $date,
						'reference_no' => $this->input->post('payment_reference_no'),
						'amount' => $this->sma->formatDecimal(0-$this->input->post('amount-paid')),
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
                        'warehouse_id'=>$purchase->warehouse_id,
						'id_ncc_id_kh' =>$purchase->supplier_id,
					);
				}
			} else {
				$payment = array();
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

            // $this->sma->print_arrays($data, $products);
        }

        if ($this->form_validation->run() == true && $this->purchases_model->addPurchase($data, $products,$payment)) {
            $this->session->set_flashdata('message', lang("Trả hàng nhà cung cấp thành công"));
            redirect("purchases/indextrahang");
        } else {

            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

            $this->data['inv'] = $purchase;
            if ($this->data['inv']->status != 'received' && $this->data['inv']->status != 'partial') {
				if($this->data['inv']->status=='returned'){					
					$this->session->set_flashdata('error', 'Không thể hoàn lại đơn hoàn, vui lòng xóa');
					redirect($_SERVER["HTTP_REFERER"]);
				}
            }
            
            if ($this->data['inv']->date <= date('Y-m-d', strtotime('-3 months'))) {
                $this->session->set_flashdata('error', lang("purchase_x_edited_older_than_3_months"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
            $inv_items = $this->purchases_model->getAllPurchaseItems($id);
            krsort($inv_items);
            $c = rand(100000, 9999999);
            foreach ($inv_items as $item) {
                $row = $this->site->getProductByID($item->product_id);
                $row->expiry = (($item->expiry && $item->expiry != '0000-00-00') ? $this->sma->hrsd($item->expiry) : '');
                $row->base_quantity = $item->quantity;
                $row->base_unit = $row->unit ? $row->unit : $item->product_unit_id;
                $row->base_unit_cost = $row->cost ? $row->cost : $item->unit_cost;
                $row->unit = $item->product_unit_id;
                $row->qty = $item->unit_quantity;
                $row->oqty = $item->unit_quantity;
                $row->purchase_item_id = $item->id;
                $row->supplier_part_no = $item->supplier_part_no;
                $row->received = $item->quantity_received ? $item->quantity_received : $item->quantity;
                $row->quantity_balance = $item->quantity_balance + ($item->quantity-$row->received);
                $row->discount = $item->discount ? $item->discount : '0';
                $options = $this->purchases_model->getProductOptions($row->id);
                $row->option = !empty($item->option_id) ? $item->option_id : '';
                $row->real_unit_cost = $item->real_unit_cost;
                $row->cost = $this->sma->formatDecimal($item->net_unit_cost + ($item->item_discount / $item->quantity));
                $row->tax_rate = $item->tax_rate_id;
                unset($row->details, $row->product_details, $row->price, $row->file, $row->product_group_id);
                $units = $this->site->getUnitsByBUID($row->base_unit);
                $tax_rate = $this->site->getTaxRateByID($row->tax_rate);
                $ri = $this->Settings->item_addition ? $row->id : $c;

                $pr[$ri] = array('id' => $c, 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 'row' => $row, 'units' => $units, 'tax_rate' => $tax_rate, 'options' => $options);

                $c++;
            }

            $this->data['inv_items'] = json_encode($pr);
            $this->data['id'] = $id;
            $this->data['reference'] = '';
            $this->data['tax_rates'] = $this->site->getAllTaxRates();
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('purchases'), 'page' => lang('purchases')), array('link' => '#', 'page' => lang('return_purchase')));
            $meta = array('page_title' => lang('return_purchase'), 'bc' => $bc);
            $this->page_construct('purchases/return_purchase', $meta, $this->data);
        }
    }
	public function return_purchase_ncc()
    {
        $this->sma->checkPermissions('return_purchases');

        if ($this->input->post('supplier')) {
            $supplier_id=$id = $this->input->post('supplier');
        }

        $this->form_validation->set_rules('supplier', lang("supplier"), 'required');
		$this->form_validation->set_rules('warehouse', lang("warehouse"), 'required');
		$payment_status=$this->input->post('payment_status');	
		
        if ($this->form_validation->run() == true) {
			
			if ((int)$supplier_id==0) {
				$this->session->set_flashdata('error',var_dump($this->input->post()));
				redirect($_SERVER["HTTP_REFERER"]);
			}
			$purchase= $this->site->getCompanyByID($supplier_id);
			
			$warehouse_id=$this->input->post('warehouse');	
			$doitac=$this->input->post('doitac');				
            $reference_no = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('rep');
           
		   if ($this->Owner || $this->Admin) {
                $date = $this->sma->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }

            $return_surcharge = $this->input->post('return_surcharge') ? $this->input->post('return_surcharge') : 0;
            
			$note = $this->sma->clear_tags($this->input->post('note'));
			
			
            $total = 0;
            $product_tax = 0;
            $order_tax = 0;
            $product_discount = 0;
            $order_discount = 0;
            $percentage = '%';
            $i = isset($_POST['product']) ? sizeof($_POST['product']) : 0;
            for ($r = 0; $r < $i; $r++) {
                $item_id = $_POST['product_id'][$r];
                $item_code = $_POST['product'][$r];
                $purchase_item_id = $_POST['purchase_item_id'][$r];
                $item_option = isset($_POST['product_option'][$r]) && !empty($_POST['product_option'][$r]) && $_POST['product_option'][$r] != 'false' ? $_POST['product_option'][$r] : null;
                $real_unit_cost = $this->sma->formatDecimal($_POST['real_unit_cost'][$r]);
                $unit_cost = $this->sma->formatDecimal($_POST['unit_cost'][$r]);
                $item_unit_quantity = (0-$_POST['quantity'][$r]);
                $item_expiry = isset($_POST['expiry'][$r]) ? $_POST['expiry'][$r] : '';
                $item_tax_rate = isset($_POST['product_tax'][$r]) ? $_POST['product_tax'][$r] : null;
                $item_discount = isset($_POST['product_discount'][$r]) ? $_POST['product_discount'][$r] : null;
                $item_unit = $_POST['product_unit'][$r];
                $item_quantity = (0-$_POST['product_base_quantity'][$r]);

                if (isset($item_code) && isset($real_unit_cost) && isset($unit_cost) && isset($item_quantity)) {
                    $product_details = $this->purchases_model->getProductByCode($item_code);

                    $item_type = $product_details->type;
                    $item_name = $product_details->name;

                    if (isset($item_discount)) {
                        $discount = $item_discount;
                        $dpos = strpos($discount, $percentage);
                        if ($dpos !== false) {
                            $pds = explode("%", $discount);
                            $pr_discount = $this->sma->formatDecimal(((($this->sma->formatDecimal($unit_cost)) * (Float) ($pds[0])) / 100), 4);
                        } else {
                            $pr_discount = $this->sma->formatDecimal($discount);
                        }
                    } else {
                        $pr_discount = 0;
                    }
                    // $unit_cost = $this->sma->formatDecimal($unit_cost - $pr_discount);
                    $pr_item_discount = $this->sma->formatDecimal(($pr_discount * $item_unit_quantity), 4);
                    $product_discount += $pr_item_discount;

                    if (isset($item_tax_rate) && $item_tax_rate != 0) {
                        $pr_tax = $item_tax_rate;
                        $tax_details = $this->site->getTaxRateByID($pr_tax);
                        if ($tax_details->type == 1 && $tax_details->rate != 0) {

                            if (!$product_details->tax_method) {
                                $item_tax = $this->sma->formatDecimal((($unit_cost) * $tax_details->rate) / (100 + $tax_details->rate), 4);
                                $tax = $tax_details->rate . "%";
                            } else {
                                $item_tax = $this->sma->formatDecimal((($unit_cost) * $tax_details->rate) / 100, 4);
                                $tax = $tax_details->rate . "%";
                            }

                        } elseif ($tax_details->type == 2) {

                            $item_tax = $this->sma->formatDecimal($tax_details->rate);
                            $tax = $tax_details->rate;

                        }
                        $pr_item_tax = $this->sma->formatDecimal($item_tax * $item_unit_quantity, 4);

                    } else {
                        $pr_tax = 0;
                        $pr_item_tax = 0;
                        $tax = "";
                    }

                    $item_net_cost = $product_details->tax_method ? $this->sma->formatDecimal(($unit_cost - $pr_discount), 4) : $this->sma->formatDecimal(($unit_cost - $item_tax - $pr_discount), 4);
                    $product_tax += $pr_item_tax;
                    $subtotal = $this->sma->formatDecimal((($item_net_cost * $item_unit_quantity) + $pr_item_tax), 4);
                    $unit = $this->site->getUnitByID($item_unit);

                    $products[] = array(
                        'product_id' => $item_id,
                        'product_code' => $item_code,
                        'product_name' => $item_name,
                        'option_id' => $item_option,
                        'net_unit_cost' => $item_net_cost,
                        'unit_cost' => $this->sma->formatDecimal($item_net_cost + $item_tax),
                        'quantity' => $item_quantity,
                        'product_unit_id' => $item_unit,
                        'product_unit_code' => $unit->code,
                        'unit_quantity' => $item_unit_quantity,
                        'quantity_balance' => $item_quantity,
                        'warehouse_id' => $warehouse_id,
                        'item_tax' => $pr_item_tax,
                        'tax_rate_id' => $pr_tax,
                        'tax' => $tax,
                        'discount' => $item_discount,
                        'item_discount' => $pr_item_discount,
                        'subtotal' => $this->sma->formatDecimal($subtotal),
                        'real_unit_cost' => $real_unit_cost,
                        'purchase_item_id' => $purchase_item_id,
                        'status' => 'received',
                    );

                    $total += $this->sma->formatDecimal(($item_net_cost * $item_unit_quantity), 4);
                }
            }
            if (empty($products)) {
                $this->form_validation->set_rules('product', lang("order_items"), 'required');
            } else {
                krsort($products);
            }

            if ($this->input->post('discountok')) {
                $order_discount_id = $this->input->post('discountok');
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
            $total_discount = $order_discount + $product_discount;

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
                $order_tax_id = null;
            }

            $total_tax = $this->sma->formatDecimal(($product_tax + $order_tax), 4);
            $grand_total = $this->sma->formatDecimal(($total + $total_tax + $this->sma->formatDecimal($return_surcharge) + $order_discount), 4);
            $data = array('date' => $date,
                'reference_no' => $reference_no,
                'supplier_id' => $supplier_id,
                'supplier' => $purchase->name,
                'warehouse_id' => $warehouse_id,
				'doitac' => $doitac,
                'note' => $note,
                'total' => $total,
                'product_discount' => $product_discount,
                'order_discount_id' => $order_discount_id,
                'order_discount' => $order_discount,
                'total_discount' => $total_discount,
                'product_tax' => $product_tax,
                'order_tax_id' => $order_tax_id,
                'order_tax' => $order_tax,
                'total_tax' => $total_tax,
                'surcharge' => $this->sma->formatDecimal($return_surcharge),
                'grand_total' => $grand_total,
                'created_by' => $this->session->userdata('user_id'),
                'return_purchase_ref' => $reference,
                'status' => 'returned',
                'payment_status' =>$payment_status,
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

            //$this->sma->print_arrays($data, $products);
        }
		
		$payment = array();
		if ($payment_status == 'partial' || $payment_status == 'paid') {
			
			if ($this->input->post('paid_by') == 'gift_card') {
				$gc = $this->site->getGiftCardByNO($this->input->post('gift_card_no'));
				$amount_paying = $grand_total >= $gc->balance ? $gc->balance : $grand_total;
				$gc_balance = $gc->balance - $amount_paying;
				$payment = array(
					'date' => $date,
					'reference_no' => $this->input->post('payment_reference_no'),
					'amount' => $this->sma->formatDecimal($amount_paying),
					'paid_by' => $this->input->post('paid_by'),
					'cheque_no' => $this->input->post('cheque_no'),
					'cc_no' => $this->input->post('gift_card_no'),
					'cc_holder' => $this->input->post('pcc_holder'),
					'cc_month' => $this->input->post('pcc_month'),
					'cc_year' => $this->input->post('pcc_year'),
					'cc_type' => $this->input->post('pcc_type'),
					'created_by' => $this->session->userdata('user_id'),
					'note' => $this->input->post('payment_note'),
					'type' => 'sent',
                    'warehouse_id'=>$warehouse_id,
					'gc_balance' => $gc_balance,
					'id_ncc_id_kh' => $supplier_id,
					
				);
				
			} else {
				$payment = array(
					'date' => $date,
					'reference_no' => $this->input->post('payment_reference_no'),
					'amount' => $this->sma->formatDecimal(0-$this->input->post('amount-paid')),
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
                    'warehouse_id'=>$warehouse_id,
					'id_ncc_id_kh' => $supplier_id,
				);
			}
		} else {
			$payment = array();
		}
        if ($this->form_validation->run() == true && $this->purchases_model->addPurchase($data, $products,$payment)) {
            $this->session->set_flashdata('message', lang("Trả hàng cho nhà cung cấp thành công"));
            redirect("purchases/indextrahang");
        } else {

            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            
			$this->data['suppliers'] = $this->site->getAllCompanies('supplier');
            $this->data['warehouses'] = $this->site->getAllWarehouses();

            $this->data['id'] = $id;
            $this->data['reference'] = '';
            $this->data['tax_rates'] = $this->site->getAllTaxRates();
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('purchases'), 'page' => lang('purchases')), array('link' => '#', 'page' => lang('return_purchase')));
            $meta = array('page_title' => lang('return_purchase'), 'bc' => $bc);
            $this->page_construct('purchases/return_purchase_ncc', $meta, $this->data);
        }
    }

    public function getSupplierCost($supplier_id, $product)
    {
        switch ($supplier_id) {
            case $product->supplier1:
                $cost =  $product->supplier1price > 0 ? $product->supplier1price : $product->cost;
                break;
            case $product->supplier2:
                $cost =  $product->supplier2price > 0 ? $product->supplier2price : $product->cost;
                break;
            case $product->supplier3:
                $cost =  $product->supplier3price > 0 ? $product->supplier3price : $product->cost;
                break;
            case $product->supplier4:
                $cost =  $product->supplier4price > 0 ? $product->supplier4price : $product->cost;
                break;
            case $product->supplier5:
                $cost =  $product->supplier5price > 0 ? $product->supplier5price : $product->cost;
                break;
            default:
                $cost = $product->cost;
        }
        return $cost;
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

        if ($this->form_validation->run() == true && $this->purchases_model->updateStatus($id, $status, $note)) {
            $this->session->set_flashdata('message', lang('status_updated'));
            redirect(isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : 'sales');
        } else {

            $this->data['inv'] = $this->purchases_model->getPurchaseByID($id);
            $this->data['returned'] = FALSE;
            if ($this->data['inv']->status == 'returned' || $this->data['inv']->return_id) {
                $this->data['returned'] = TRUE;
            }
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme.'purchases/update_status', $this->data);
        }
    }
	public function printphieuchi($id = null)
	{      
		$this->sma->checkPermissions('index');       
		if ($this->input->get('id')) {        
			$id = $this->input->get('id');     
		}     
		$this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');    
		$inv =  $this->purchases_model->getExpenseByID($id);
		
		if (!$this->session->userdata('view_right')) {           
			$this->sma->view_rights($inv->created_by);       
		}        
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
		
		$this->data['kich_thuoc']=$this->settings_model->define_size_in($_sizein_page,$_chieuin_page);	
		
		//replace value size print
		$sale_temp=$this->settings_model->define_print_replace($sale_temp);
			
		$message = $this->parser->parse_string($sale_temp, $parse_data,true);
		
		$this->data['note'] = array('noidung' =>$message);  
		$this->data['id'] = $inv->id;        
		$this->data['modal_js'] = $this->site->modal_js();      
		$this->load->view($this->theme . 'purchases/printphieuchi', $this->data);  	
    }
	public function printphieuchincc($id = null)
	{      
		$this->sma->checkPermissions('index');       
		if ($this->input->get('id')) {        
			$id = $this->input->get('id');     
		}     
		$this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');    
		$inv =  $this->purchases_model->getPaymentByID($id);
		
		if (!$this->session->userdata('view_right')) {           
			$this->sma->view_rights($inv->created_by);       
		}        
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
		
		$this->data['kich_thuoc']=$this->settings_model->define_size_in($_sizein_page,$_chieuin_page);	
		
		//replace value size print
		$sale_temp=$this->settings_model->define_print_replace($sale_temp);
			
		$message = $this->parser->parse_string($sale_temp, $parse_data,true);
		
		$this->data['note'] = array('noidung' =>$message);  
		$this->data['id'] = $inv->id;        
		$this->data['modal_js'] = $this->site->modal_js();      
		$this->load->view($this->theme . 'purchases/printphieuchincc', $this->data);  	
    }
	public function printphieuthu($id = null)
	{      
		$this->sma->checkPermissions('index');       
		if ($this->input->get('id')) {        
			$id = $this->input->get('id');     
		}     
		$this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');    
		$inv =  $this->purchases_model->getPaymentByID($id);
		
		if (!$this->session->userdata('view_right')) {           
			$this->sma->view_rights($inv->created_by);       
		}        
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
		
		$this->data['kich_thuoc']=$this->settings_model->define_size_in($_sizein_page,$_chieuin_page);	
		
		//replace value size print
		$sale_temp=$this->settings_model->define_print_replace($sale_temp);
			
		$message = $this->parser->parse_string($sale_temp, $parse_data,true);
		
		$this->data['note'] = array('noidung' =>$message);  
		$this->data['id'] = $inv->id;        
		$this->data['modal_js'] = $this->site->modal_js();      
		$this->load->view($this->theme . 'purchases/printphieuthu', $this->data);  	
    }
	public function printnhap($purchase_id = null)
    {
        $this->sma->checkPermissions('index');

        if ($this->input->get('id')) {
            $purchase_id = $this->input->get('id');
        }
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $inv = $this->purchases_model->getPurchaseByID($purchase_id);
        if (!$this->session->userdata('view_right')) {
            $this->sma->view_rights($inv->created_by);
        }
        $this->data['rows'] = $rows=$this->purchases_model->getAllPurchaseItems($purchase_id);
        $this->data['supplier'] = $supplier=$this->site->getCompanyByID($inv->supplier_id);
        $this->data['warehouse'] = $warehouse=$this->site->getWarehouseByID($inv->warehouse_id);
        $this->data['inv'] = $inv;
        $this->data['payments'] = $payments=$this->purchases_model->getPaymentsForPurchase($purchase_id);
        $this->data['created_by'] = $created_by=$this->site->getUser($inv->created_by);
        $this->data['updated_by'] = $updated_by=$inv->updated_by ? $this->site->getUser($inv->updated_by) : null;
        $this->data['return_purchase'] = $inv->return_id ? $this->purchases_model->getPurchaseByID($inv->return_id) : NULL;
        $this->data['return_rows'] = $inv->return_id ? $this->purchases_model->getAllPurchaseItems($inv->return_id) : NULL;
        $this->data['doitac'] = $doitac=$this->doitac_model->getDoitacByID($inv->doitac);
		
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
		
		$this->data['item_print']=$this->Settings->item_print;
		$this->data['kich_thuoc']=$this->settings_model->define_size_in($_sizein_page,$_chieuin_page);	
		
		//replace value size print
		$sale_temp=$this->settings_model->define_print_replace($sale_temp);
			
		$message = $this->parser->parse_string($sale_temp, $parse_data,true);
		$this->data['status']=$inv->status;
		$this->data['note'] = array('noidung' =>$message);  
		$this->data['id'] = $inv->id;        
		$this->data['modal_js'] = $this->site->modal_js();      
		$this->load->view($this->theme . 'purchases/print', $this->data);
    }
}
