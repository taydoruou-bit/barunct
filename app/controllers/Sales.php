<?php defined('BASEPATH') or exit('No direct script access allowed');
require __DIR__ . '/../vendor/autoload.php';
use Automattic\WooCommerce\Client;
use Automattic\WooCommerce\HttpClient\HttpClientException;
use Automattic\WooCommerce\HttpClient\HttpClient;
class Sales extends MY_Controller
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
        $this->lang->load('sales', $this->Settings->user_language);
        $this->load->library('form_validation');
        $this->load->model('sales_model');		
		$this->load->model('doitac_model');	
		$this->load->model('pos_model');			
		$this->load->model('reports_model');
        
		$this->load->model('companies_model');		
		$this->load->model('settings_model');
        $this->digital_upload_path = 'files/';
        $this->upload_path = 'assets/uploads/';
        $this->thumbs_path = 'assets/uploads/thumbs/';
        $this->image_types = 'gif|jpg|jpeg|png|tif';
        $this->digital_file_types = 'zip|psd|ai|rar|pdf|doc|docx|xls|xlsx|ppt|pptx|gif|jpg|jpeg|png|tif|txt';
        $this->allowed_file_size = '1024';
        $this->data['logo'] = true;
		
		$this->pos_settings = $this->pos_model->getSetting();
        $this->data['pos_settings'] = $this->pos_settings;
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

        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('sales')));
        $meta = array('page_title' => lang('sales'), 'bc' => $bc);
        $this->page_construct('sales/index', $meta, $this->data);
    }

    public function getSales($warehouse_id = null)
    {
        $this->sma->checkPermissions('index');

        if ((!$this->Owner || !$this->Admin) && !$warehouse_id) {
            $user = $this->site->getUser();
            $warehouse_id = $user->warehouse_id;
        }
        $detail_link = anchor('sales/view/$1', '<i class="fa fa-file-text-o"></i> ' . lang('sale_details'));
        $duplicate_link = anchor('sales/add?sale_id=$1', '<i class="fa fa-plus-circle"></i> ' . lang('duplicate_sale'));
        $payments_link = anchor('sales/payments/$1', '<i class="fa fa-money"></i> ' . lang('view_payments'), 'data-toggle="modal" data-target="#myModal"');
        $add_payment_link = anchor('sales/add_payment/$1', '<i class="fa fa-money"></i> ' . lang('add_payment'), 'data-toggle="modal" data-target="#myModal"');
        $add_delivery_link = anchor('sales/add_delivery/$1', '<i class="fa fa-truck"></i> ' . lang('add_delivery'), 'data-toggle="modal" data-target="#myModal"');
        $email_link = anchor('sales/email/$1', '<i class="fa fa-envelope"></i> ' . lang('email_sale'), 'data-toggle="modal" data-target="#myModal"');
        $edit_link = anchor('sales/edit/$1', '<i class="fa fa-edit"></i> ' . lang('edit_sale'), 'class="sledit"');
        $pdf_link = anchor('sales/pdf/$1', '<i class="fa fa-file-pdf-o"></i> ' . lang('download_pdf'));
        $return_link = anchor('sales/return_sale/$1', '<i class="fa fa-angle-double-left"></i> ' . lang('return_sale'));		
		$print_link = anchor('sales/printsalelhson/$1', '<i class="fa fa-print"></i> ' . lang('print_hoadon'), 'data-toggle="modal" data-target="#myModal"');
        $delete_link = "<a href='#' class='po' title='<b>" . lang("delete_sale") . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('sales/delete/$1') . "'>"
        . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
        . lang('delete_sale') . "</a>";
        $action = '<div class="text-center"><div class="btn-group text-left">'
        . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
        . lang('actions') . ' <span class="caret"></span></button>
        <ul class="dropdown-menu pull-right" role="menu">
            <li>' . $detail_link . '</li>
            <li>' . $duplicate_link . '</li>
            <li>' . $payments_link . '</li>
            <li>' . $add_payment_link . '</li>
            <li>' . $add_delivery_link . '</li>
            <li>' . $edit_link . '</li>
            <li>' . $pdf_link . '</li>
            <li>' . $email_link . '</li>
            <li>' . $return_link . '</li>			<li>' . $print_link . '</li>
            <li>' . $delete_link . '</li>
        </ul>
    </div></div>';
        //$action = '<div class="text-center">' . $detail_link . ' ' . $edit_link . ' ' . $email_link . ' ' . $delete_link . '</div>';

        $this->load->library('datatables');
        if ($warehouse_id) {
            $this->datatables
				->select("sales.id as id, DATE_FORMAT(scodeweb_sales.date, '%Y-%m-%d %T') as date, reference_no,doitac.name as doitac,warehouses.name as kho,CONCAT(" . $this->db->dbprefix('users') . ".first_name, ' ', " . $this->db->dbprefix('users') . ".last_name) as biller, concat(customer,'<br/>',scodeweb_companies.name) as customer, sale_status, grand_total, paid, (grand_total-paid) as balance, payment_status, sales.attachment, return_id")
				->from('sales')
				->join('companies', 'companies.id=sales.customer_id', 'left')
				->join('doitac', 'doitac.id=sales.doitac', 'left')
                ->join('users', 'users.id=sales.created_by', 'left')
				->join('warehouses', 'warehouses.id=sales.warehouse_id', 'left')
				->where('sales.warehouse_id', $warehouse_id);
        } else {
            $this->datatables
			->select("sales.id as id, DATE_FORMAT(scodeweb_sales.date, '%Y-%m-%d %T') as date, reference_no,doitac.name as doitac,warehouses.name as kho,CONCAT(" . $this->db->dbprefix('users') . ".first_name, ' ', " . $this->db->dbprefix('users') . ".last_name) as biller, concat(customer,'<br/>',scodeweb_companies.name) as customer, sale_status, grand_total, paid, (grand_total-paid) as balance, payment_status, sales.attachment, return_id")
				->from('sales')
				->join('companies', 'companies.id=sales.customer_id', 'left')
				->join('doitac', 'doitac.id=sales.doitac', 'left')
                ->join('users', 'users.id=sales.created_by', 'left')
				->join('warehouses', 'warehouses.id=sales.warehouse_id', 'left')
			->from('sales');
        }
       // $this->datatables->where('pos !=', 1); 
		$this->datatables->where('sale_status !=', 'returned');
        if (!$this->Customer && !$this->Supplier && !$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->datatables->where('sales.created_by', $this->session->userdata('user_id'));
        } elseif ($this->Customer) {
            $this->datatables->where('customer_id', $this->session->userdata('user_id'));
        }
        $this->datatables->add_column("Actions", $action, "id");
        echo $this->datatables->generate();
    }

    public function modal_view($id = null)
    {
        $this->sma->checkPermissions('index', true);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $inv = $this->sales_model->getInvoiceByID($id);
        if (!$this->session->userdata('view_right')) {
            $this->sma->view_rights($inv->created_by, true);
        }
        $this->data['customer'] = $this->site->getCompanyByID($inv->customer_id);
        $this->data['biller'] = $this->site->getCompanyByID($inv->biller_id);
        $this->data['created_by'] = $this->site->getUser($inv->created_by);
        $this->data['updated_by'] = $inv->updated_by ? $this->site->getUser($inv->updated_by) : null;
        $this->data['warehouse'] = $this->site->getWarehouseByID($inv->warehouse_id);
        $this->data['inv'] = $inv;
        $this->data['rows'] = $this->sales_model->getAllInvoiceItems($id);
        $this->data['return_sale'] = $inv->return_id ? $this->sales_model->getInvoiceByID($inv->return_id) : NULL;
        $this->data['return_rows'] = $inv->return_id ? $this->sales_model->getAllInvoiceItems($inv->return_id) : NULL;

        $this->load->view($this->theme . 'sales/modal_view', $this->data);
    }

    public function view($id = null)
    {
        $this->sma->checkPermissions('index');

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $inv = $this->sales_model->getInvoiceByID($id);
        if (!$this->session->userdata('view_right')) {
            $this->sma->view_rights($inv->created_by);
        }
        $this->data['barcode'] = "<img src='" . site_url('products/gen_barcode/' . $inv->reference_no) . "' alt='" . $inv->reference_no . "' class='pull-left' />";
        $this->data['customer'] = $this->site->getCompanyByID($inv->customer_id);
        $this->data['payments'] = $this->sales_model->getPaymentsForSale($id);
        $this->data['biller'] = $this->site->getCompanyByID($inv->biller_id);
        $this->data['created_by'] = $this->site->getUser($inv->created_by);
        $this->data['updated_by'] = $inv->updated_by ? $this->site->getUser($inv->updated_by) : null;
        $this->data['warehouse'] = $this->site->getWarehouseByID($inv->warehouse_id);
        $this->data['inv'] = $inv;
        $this->data['rows'] = $this->sales_model->getAllInvoiceItems($id);
        $this->data['return_sale'] = $inv->return_id ? $this->sales_model->getInvoiceByID($inv->return_id) : NULL;
        $this->data['return_rows'] = $inv->return_id ? $this->sales_model->getAllInvoiceItems($inv->return_id) : NULL;
        $this->data['paypal'] = $this->sales_model->getPaypalSettings();
        $this->data['skrill'] = $this->sales_model->getSkrillSettings();

        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('sales'), 'page' => lang('sales')), array('link' => '#', 'page' => lang('view')));
        $meta = array('page_title' => lang('view_sales_details'), 'bc' => $bc);
        $this->page_construct('sales/view', $meta, $this->data);
    }
    function viewapi($id = NULL)
    {
        $this->sma->checkPermissions('index', TRUE);

        $pr_details = $this->site->getInvoiceByID($id);
        $this->data['remove']=false;
        if (!$pr_details) {
            $detail=$this->site->getOrderHistoryApiById($id); 
           
            $pr_details->id=$detail->order_id;
            $pr_details->reference_no=$detail->order_code;

            $this->data['remove']=true;          
        }

        $this->data['order'] = $pr_details;
       

        $this->load->view($this->theme.'sales/viewapi', $this->data);
    }
    public function pdf($id = null, $view = null, $save_bufffer = null)
    {
        $this->sma->checkPermissions();

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $inv = $this->sales_model->getInvoiceByID($id);
        if (!$this->session->userdata('view_right')) {
            $this->sma->view_rights($inv->created_by);
        }
        $this->data['barcode'] = "<img src='" . site_url('products/gen_barcode/' . $inv->reference_no) . "' alt='" . $inv->reference_no . "' class='pull-left' />";
        $this->data['customer'] = $this->site->getCompanyByID($inv->customer_id);
        $this->data['payments'] = $this->sales_model->getPaymentsForSale($id);
        $this->data['biller'] = $this->site->getCompanyByID($inv->biller_id);
        $this->data['user'] = $this->site->getUser($inv->created_by);
        $this->data['warehouse'] = $this->site->getWarehouseByID($inv->warehouse_id);
        $this->data['inv'] = $inv;
        $this->data['rows'] = $this->sales_model->getAllInvoiceItems($id);
        $this->data['return_sale'] = $inv->return_id ? $this->sales_model->getInvoiceByID($inv->return_id) : NULL;
        $this->data['return_rows'] = $inv->return_id ? $this->sales_model->getAllInvoiceItems($inv->return_id) : NULL;
        //$this->data['paypal'] = $this->sales_model->getPaypalSettings();
        //$this->data['skrill'] = $this->sales_model->getSkrillSettings();

        $name = lang("sale") . "_" . str_replace('/', '_', $inv->reference_no) . ".pdf";
        $html = $this->load->view($this->theme . 'sales/pdf', $this->data, true);
        if (! $this->Settings->barcode_img) {
            $html = preg_replace("'\<\?xml(.*)\?\>'", '', $html);
        }

        if ($view) {
            $this->load->view($this->theme . 'sales/pdf', $this->data);
        } elseif ($save_bufffer) {
            return $this->sma->generate_pdf($html, $name, $save_bufffer, $this->data['biller']->invoice_footer);
        } else {
            $this->sma->generate_pdf($html, $name, false, $this->data['biller']->invoice_footer);
        }
    }

    public function combine_pdf($sales_id)
    {
        $this->sma->checkPermissions('pdf');

        foreach ($sales_id as $id) {

            $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
            $inv = $this->sales_model->getInvoiceByID($id);
            if (!$this->session->userdata('view_right')) {
				$this->sma->view_rights($inv->created_by);
            }
            $this->data['barcode'] = "<img src='" . site_url('products/gen_barcode/' . $inv->reference_no) . "' alt='" . $inv->reference_no . "' class='pull-left' />";
            $this->data['customer'] = $this->site->getCompanyByID($inv->customer_id);
            $this->data['payments'] = $this->sales_model->getPaymentsForSale($id);
            $this->data['biller'] = $this->site->getCompanyByID($inv->biller_id);
            $this->data['user'] = $this->site->getUser($inv->created_by);
            $this->data['warehouse'] = $this->site->getWarehouseByID($inv->warehouse_id);
            $this->data['inv'] = $inv;
            $this->data['rows'] = $this->sales_model->getAllInvoiceItems($id);
            $this->data['return_sale'] = $inv->return_id ? $this->sales_model->getInvoiceByID($inv->return_id) : NULL;
            $this->data['return_rows'] = $inv->return_id ? $this->sales_model->getAllInvoiceItems($inv->return_id) : NULL;
            $html_data = $this->load->view($this->theme . 'sales/pdf', $this->data, true);
            if (! $this->Settings->barcode_img) {
				$html_data = preg_replace("'\<\?xml(.*)\?\>'", '', $html_data);
            }

            $html[] = array(
				'content' => $html_data,
				'footer' => $this->data['biller']->invoice_footer,
            );
        }

        $name = lang("sales") . ".pdf";
        $this->sma->generate_pdf($html, $name);

    }

    public function email($id = null)
    {
        $this->sma->checkPermissions(false, true);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $inv = $this->sales_model->getInvoiceByID($id);
        $this->form_validation->set_rules('to', lang("to") . " " . lang("email"), 'trim|required|valid_email');
        $this->form_validation->set_rules('subject', lang("subject"), 'trim|required');
        $this->form_validation->set_rules('cc', lang("cc"), 'trim|valid_emails');
        $this->form_validation->set_rules('bcc', lang("bcc"), 'trim|valid_emails');
        $this->form_validation->set_rules('note', lang("message"), 'trim');

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
            $paypal = $this->sales_model->getPaypalSettings();
            $skrill = $this->sales_model->getSkrillSettings();
            $btn_code = '<div id="payment_buttons" class="text-center margin010">';
            if ($paypal->active == "1" && $inv->grand_total != "0.00") {
				if (trim(strtolower($customer->country)) == $biller->country) {
					$paypal_fee = $paypal->fixed_charges + ($inv->grand_total * $paypal->extra_charges_my / 100);
				} else {
					$paypal_fee = $paypal->fixed_charges + ($inv->grand_total * $paypal->extra_charges_other / 100);
				}
				$btn_code .= '<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_xclick&business=' . $paypal->account_email . '&item_name=' . $inv->reference_no . '&item_number=' . $inv->id . '&image_url=' . base_url() . 'assets/uploads/logos/' . $this->Settings->logo . '&amount=' . (($inv->grand_total - $inv->paid) + $paypal_fee) . '&no_shipping=1&no_note=1&currency_code=' . $this->default_currency->code . '&bn=FC-BuyNow&rm=2&return=' . site_url('sales/view/' . $inv->id) . '&cancel_return=' . site_url('sales/view/' . $inv->id) . '&notify_url=' . site_url('payments/paypalipn') . '&custom=' . $inv->reference_no . '__' . ($inv->grand_total - $inv->paid) . '__' . $paypal_fee . '"><img src="' . base_url('assets/images/btn-paypal.png') . '" alt="Pay by PayPal"></a> ';

            }
            if ($skrill->active == "1" && $inv->grand_total != "0.00") {
				if (trim(strtolower($customer->country)) == $biller->country) {
					$skrill_fee = $skrill->fixed_charges + ($inv->grand_total * $skrill->extra_charges_my / 100);
				} else {
					$skrill_fee = $skrill->fixed_charges + ($inv->grand_total * $skrill->extra_charges_other / 100);
				}
				$btn_code .= ' <a href="https://www.moneybookers.com/app/payment.pl?method=get&pay_to_email=' . $skrill->account_email . '&language=EN&merchant_fields=item_name,item_number&item_name=' . $inv->reference_no . '&item_number=' . $inv->id . '&logo_url=' . base_url() . 'assets/uploads/logos/' . $this->Settings->logo . '&amount=' . (($inv->grand_total - $inv->paid) + $skrill_fee) . '&return_url=' . site_url('sales/view/' . $inv->id) . '&cancel_url=' . site_url('sales/view/' . $inv->id) . '&detail1_description=' . $inv->reference_no . '&detail1_text=Payment for the sale invoice ' . $inv->reference_no . ': ' . $inv->grand_total . '(+ fee: ' . $skrill_fee . ') = ' . $this->sma->formatMoney($inv->grand_total + $skrill_fee) . '&currency=' . $this->default_currency->code . '&status_url=' . site_url('payments/skrillipn') . '"><img src="' . base_url('assets/images/btn-skrill.png') . '" alt="Pay by Skrill"></a>';
            }

            $btn_code .= '<div class="clearfix"></div>
    </div>';
            $message = $message . $btn_code;

            $attachment = $this->pdf($id, null, 'S');
        } elseif ($this->input->post('send_email')) {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->session->set_flashdata('error', $this->data['error']);
            redirect($_SERVER["HTTP_REFERER"]);
        }

        if ($this->form_validation->run() == true && $this->sma->send_email($to, $subject, $message, null, null, $attachment, $cc, $bcc)) {
            delete_files($attachment);
            $this->session->set_flashdata('message', lang("email_sent"));
            redirect("sales");
        } else {

            if (file_exists('./themes/' . $this->theme . '/views/email_templates/sale.html')) {
				$sale_temp = file_get_contents('themes/' . $this->theme . '/views/email_templates/sale.html');
            } else {
				$sale_temp = file_get_contents('./themes/default/views/email_templates/sale.html');
            }

            $this->data['subject'] = array('name' => 'subject',
			'id' => 'subject',
			'type' => 'text',
			'value' => $this->form_validation->set_value('subject', lang('invoice') . ' (' . $inv->reference_no . ') ' . lang('from') . ' ' . $this->Settings->site_name),
            );
            $this->data['note'] = array('name' => 'note',
			'id' => 'note',
			'type' => 'text',
			'value' => $this->form_validation->set_value('note', $sale_temp),
            );
            $this->data['customer'] = $this->site->getCompanyByID($inv->customer_id);

            $this->data['id'] = $id;
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'sales/email', $this->data);
        }
    }

    /* ------------------------------------------------------------------ */

    public function add($quote_id = null)
    {
        $this->sma->checkPermissions();
        $sale_id = $this->input->get('sale_id') ? $this->input->get('sale_id') : NULL;

        $this->form_validation->set_message('is_natural_no_zero', lang("no_zero_required"));
        $this->form_validation->set_rules('customer', lang("customer"), 'required');
        $this->form_validation->set_rules('biller', lang("biller"), 'required');
        $this->form_validation->set_rules('sale_status', lang("sale_status"), 'required');
        $this->form_validation->set_rules('payment_status', lang("payment_status"), 'required');

        if ($this->form_validation->run() == true) {

            $reference = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('so');
			//return
			$reference_return = $this->site->getReference('rt');
            
			if ($this->Owner || $this->Admin) {
				$date = $this->sma->fld(trim($this->input->post('date')));
            } else {
				$date = date('Y-m-d H:i:s');
            }
            $warehouse_id = $this->input->post('warehouse');
            $customer_id = $this->input->post('customer');
            $biller_id = $this->input->post('biller');
            $total_items = $this->input->post('total_items');
            $sale_status = $this->input->post('sale_status');
            $payment_status = $this->input->post('payment_status');
            $payment_term = $this->input->post('payment_term');
			$doitac = $this->input->post('doitac');
            $due_date = $payment_term ? date('Y-m-d', strtotime('+' . $payment_term . ' days', strtotime($date))) : null;
            $shipping = $this->input->post('shipping') ? $this->input->post('shipping') : 0;
            $customer_details = $this->site->getCompanyByID($customer_id);
            $customer = $customer_details->company != '' ? $customer_details->company : $customer_details->name;
            $biller_details = $this->site->getCompanyByID($biller_id);
            $biller = $biller_details->company != '-' ? $biller_details->company : $biller_details->name;
            $note = $this->sma->clear_tags($this->input->post('note'));
            $staff_note = $this->sma->clear_tags($this->input->post('staff_note'));
            $quote_id = $this->input->post('quote_id') ? $this->input->post('quote_id') : null;

            $total = 0;
            $total_weight=0;
            $product_tax = 0;
            $order_tax = 0;
            $product_discount = 0;
            $order_discount = 0;
            $percentage = '%';
            $digital = FALSE;
            $i = isset($_POST['product_code']) ? sizeof($_POST['product_code']) : 0;
            for ($r = 0; $r < $i; $r++) {
				$item_id = $_POST['product_id'][$r];
				$item_type = $_POST['product_type'][$r];
				$item_code = $_POST['product_code'][$r];
				$item_name = $_POST['product_name'][$r];
				$item_option = isset($_POST['product_option'][$r]) && $_POST['product_option'][$r] != 'false' && $_POST['product_option'][$r] != 'null' ? $_POST['product_option'][$r] : null;
				$real_unit_price = $this->sma->formatDecimal($_POST['real_unit_price'][$r]);
				$unit_price =$unit_price_old= $this->sma->formatDecimal($_POST['unit_price'][$r]);
				$item_unit_quantity = $_POST['quantity'][$r];
				$item_serial = isset($_POST['serial'][$r]) ? $_POST['serial'][$r] : '';
				$item_tax_rate = isset($_POST['product_tax'][$r]) ? $_POST['product_tax'][$r] : null;
				$item_discount = isset($_POST['product_discount'][$r]) ? $_POST['product_discount'][$r] : null;
				$item_unit = $_POST['product_unit'][$r];
				$item_quantity = $_POST['product_base_quantity'][$r];
                $data_id_khuyenmai = $_POST['data_id_khuyenmai'][$r];

				if (isset($item_code) && isset($real_unit_price) && isset($unit_price) && isset($item_quantity)) {
					$product_details = $item_type != 'manual' ? $this->sales_model->getProductByCode($item_code) : null;
					$weight=(float)$product_details->weight;
                    $total_weight+=($weight*$item_quantity);

                    $unit_price = $real_unit_price;
					$pr_discount = 0;
					if ($item_type == 'digital') {
						$digital = TRUE;
					}

					if (isset($item_discount)) {
						$discount = $item_discount;
						$dpos = strpos($discount, $percentage);
						if ($dpos !== false) {
							$pds = explode("%", $discount);
							$pr_discount = $this->sma->formatDecimal(((($this->sma->formatDecimal($unit_price)) * (Float) ($pds[0])) / 100), 4);
						} else {
							$pr_discount = $this->sma->formatDecimal($discount);
						}
					}

					//$unit_price = $this->sma->formatDecimal($unit_price - $pr_discount);
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
						$pr_item_tax = $this->sma->formatDecimal($item_tax * $item_unit_quantity, 4);

					}

					$product_tax += $pr_item_tax;
					$subtotal = (($item_net_price * $item_unit_quantity) + $pr_item_tax)-$pr_item_discount;
					$unit = $this->site->getUnitByID($item_unit);

					$products[] = array(
						'product_id' => $item_id,
						'product_code' => $item_code,
						'product_name' => $item_name,
						'product_type' => $item_type,
						'option_id' => $item_option,
						'net_unit_price' => $item_net_price,
						'unit_price' => $this->sma->formatDecimal($item_net_price + $item_tax),
						'quantity' => $item_quantity,
						'product_unit_id' => $item_unit,
						'product_unit_code' => $unit ? $unit->code : NULL,
						'unit_quantity' => $item_unit_quantity,
						'warehouse_id' => $warehouse_id,
						'item_tax' => $pr_item_tax,
						'tax_rate_id' => $pr_tax,
						'tax' => $tax,
						'discount' => $item_discount,
						'item_discount' => $pr_item_discount,
						'subtotal' => $this->sma->formatDecimal($subtotal),
						'serial_no' => $item_serial,
						'real_unit_price' => $real_unit_price,
                        'data_id_khuyenmai' => $data_id_khuyenmai,
					);

					$total += $this->sma->formatDecimal(($item_net_price * $item_unit_quantity), 4)-$pr_item_discount;
				}
            }
            if (empty($products)) {
				$this->form_validation->set_rules('product', lang("order_items"), 'required');
            } else {
				krsort($products);
            }

            if ($this->input->post('order_discount')) {
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
            $total_discount = $this->sma->formatDecimal($order_discount + $product_discount);

            if ($this->Settings->tax2) {
				$order_tax_id = $this->input->post('order_tax');
				if ($order_tax_details = $this->site->getTaxRateByID($order_tax_id)) {
					if ($order_tax_details->type == 2) {
						$order_tax = $this->sma->formatDecimal($order_tax_details->rate);
					} elseif ($order_tax_details->type == 1) {
						$order_tax = $this->sma->formatDecimal(((($total + $product_tax - $order_discount) * $order_tax_details->rate) / 100), 4);
					}
				}
            } else {
				$order_tax_id = null;
            }

            $total_tax = $this->sma->formatDecimal(($product_tax + $order_tax), 4); 
            $grand_total = $this->sma->formatDecimal(($total + $total_tax + $this->sma->formatDecimal($shipping) - $order_discount), 4);
            
			/*ap dung cho cac san pham tra hang*/
			$total_tra = 0;
			$product_tax_tra = 0;
			$order_tax_tra = 0;
			$product_discount_tra = 0;
			$order_discount_tra = 0;
			$percentage_tra = '%';
			$digital_tra = FALSE;
			$i_tra = isset($_POST['product_code_tra']) ? sizeof($_POST['product_code_tra']) : 0;
			
			for ($m = 0; $m < $i_tra; $m++) {
                $item_id = $_POST['product_id_tra'][$m];
				//kiem tra tong thu hoi so voi tong sl ban ra
				$item_unit_quantity = $_POST['quantity_tra'][$m];
				$tongthuhoi=(float)$this->site->getTongthuhoi($item_id,$warehouse_id)+$item_unit_quantity;
				
				$tongbanra=(float)$this->site->getTongSoluongBanra($item_id,$warehouse_id);
				
				if($tongbanra<=0){
					$this->session->set_userdata('remove_rels', 1);
					$this->session->set_flashdata('error','Lỗi: Sản phẩm ['.$_POST['product_name_tra'][$m].'] bán ra ['.$tongbanra.'] thu hồi ['.$tongthuhoi.']');
					redirect("sales");
				}				
			}
			
			for ($r = 0; $r < $i_tra; $r++) {
				$item_id = $_POST['product_id_tra'][$r];
				$item_type = $_POST['product_type_tra'][$r];
				$item_code = $_POST['product_code_tra'][$r];
				$item_name = $_POST['product_name_tra'][$r];
				$item_option = isset($_POST['product_option_tra'][$r]) && $_POST['product_option_tra'][$r] != 'false' && $_POST['product_option_tra'][$r] != 'null' ? $_POST['product_option_tra'][$r] : null;
				$real_unit_price_tra = $this->sma->formatDecimal($_POST['real_unit_price_tra'][$r]);
				$unit_price_tra = $this->sma->formatDecimal($_POST['unit_price_tra'][$r]);
				$item_unit_quantity = (0-$_POST['quantity_tra'][$r]);
				$item_serial = isset($_POST['serial_tra'][$r]) ? $_POST['serial_tra'][$r] : '';
				$item_tax_rate = isset($_POST['product_tax_tra'][$r]) ? $_POST['product_tax_tra'][$r] : null;
				$item_discount = isset($_POST['product_discount_tra'][$r]) ? $_POST['product_discount_tra'][$r] : null;
				$item_unit = $_POST['product_unit_tra'][$r];
				$item_quantity = (0-$_POST['product_base_quantity_tra'][$r]);

				if (isset($item_code) && isset($real_unit_price_tra) && isset($unit_price_tra) && isset($item_quantity)) {
					$product_details = $item_type != 'manual' ? $this->sales_model->getProductByCode($item_code) : null;
					 $unit_price_tra = $real_unit_price_tra;
					$pr_discount = 0;
					if ($item_type == 'digital') {
						$digital_tra = TRUE;
					}

					if (isset($item_discount)) {
						$discount = $item_discount;
						$dpos = strpos($discount, $percentage_tra);
						if ($dpos !== false) {
							$pds = explode("%", $discount);
							$pr_discount = $this->sma->formatDecimal(((($this->sma->formatDecimal($unit_price_tra)) * (Float) ($pds[0])) / 100), 4);
						} else {
							$pr_discount = $this->sma->formatDecimal($discount);
						}
					}

					$unit_price_tra = $this->sma->formatDecimal($unit_price_tra - $pr_discount);
					$item_net_price = $unit_price_tra;
					$pr_item_discount = $this->sma->formatDecimal($pr_discount * $item_unit_quantity);
					$product_discount_tra += $pr_item_discount;
					$pr_tax = 0;
					$pr_item_tax = 0;
					$item_tax = 0;
					$tax = "";

					if (isset($item_tax_rate) && $item_tax_rate != 0) {
						$pr_tax = $item_tax_rate;
						$tax_details = $this->site->getTaxRateByID($pr_tax);
						if ($tax_details->type == 1 && $tax_details->rate != 0) {

							if ($product_details && $product_details->tax_method == 1) {
								$item_tax = $this->sma->formatDecimal((($unit_price_tra) * $tax_details->rate) / 100, 4);
								$tax = $tax_details->rate . "%";
							} else {
								$item_tax = $this->sma->formatDecimal((($unit_price_tra) * $tax_details->rate) / (100 + $tax_details->rate), 4);
								$tax = $tax_details->rate . "%";
								$item_net_price = $unit_price_tra - $item_tax;
							}

						} elseif ($tax_details->type == 2) {

							if ($product_details && $product_details->tax_method == 1) {
								$item_tax = $this->sma->formatDecimal((($unit_price_tra) * $tax_details->rate) / 100, 4);
								$tax = $tax_details->rate . "%";
							} else {
								$item_tax = $this->sma->formatDecimal((($unit_price_tra) * $tax_details->rate) / (100 + $tax_details->rate), 4);
								$tax = $tax_details->rate . "%";
								$item_net_price = $unit_price_tra - $item_tax;
							}

							$item_tax = $this->sma->formatDecimal($tax_details->rate);
							$tax = $tax_details->rate;

						}
						$pr_item_tax = $this->sma->formatDecimal($item_tax * $item_unit_quantity, 4);

					}

					$product_tax_tra += $pr_item_tax;
					$subtotal = (($item_net_price * $item_unit_quantity) + $pr_item_tax)-$pr_discount;
					$unit = $this->site->getUnitByID($item_unit);

					$products[] = array(
						'product_id' => $item_id,
						'product_code' => $item_code,
						'product_name' => $item_name,
						'product_type' => $item_type,
						'option_id' => $item_option,
						'net_unit_price' => $item_net_price,
						'unit_price' => $this->sma->formatDecimal($item_net_price + $item_tax),
						'quantity' => $item_quantity,
						'product_unit_id' => $item_unit,
						'product_unit_code' => $unit ? $unit->code : NULL,
						'unit_quantity' => $item_unit_quantity,
						'warehouse_id' => $warehouse_id,
						'item_tax' => $pr_item_tax,
						'tax_rate_id' => $pr_tax,
						'tax' => $tax,
						'discount' => $item_discount,
						'item_discount' => $pr_item_discount,
						'subtotal' => $this->sma->formatDecimal($subtotal),
						'serial_no' => $item_serial,
						'real_unit_price' => $real_unit_price_tra,
					);

					$total_tra += $this->sma->formatDecimal(($item_net_price * $item_unit_quantity), 4)-$pr_item_discount;
				}
			}
					
			krsort($products);
			

			if ($this->input->post('order_discount_tra')) {
				$order_discount_tra_id = $this->input->post('order_discount_tra');
				$opos = strpos($order_discount_tra_id, $percentage_tra);
				if ($opos !== false) {
					$ods = explode("%", $order_discount_tra_id);
					$order_discount_tra = $this->sma->formatDecimal(((($total_tra + $product_tax_tra) * (Float) ($ods[0])) / 100), 4);
				} else {
					$order_discount_tra = $this->sma->formatDecimal($order_discount_tra_id);
				}
			} else {
				$order_discount_tra_id = null;
			}
			
			$total_tra_discount = $this->sma->formatDecimal($order_discount_tra + $product_discount_tra);
			
			$order_tax_tra_id = null;	
			$total_tra_tax = $this->sma->formatDecimal(($product_tax_tra + $order_tax_tra), 4); 
			$grand_total_tra = $this->sma->formatDecimal(($total_tra + $total_tra_tax  - $order_discount_tra), 4);
			
			
			
			/* end san pham tra hang*/
			
			/*tinh tong 2 hoa don*/
			$product_discount+=$product_discount_tra;
			$order_discount+=$order_discount_tra;
			$total_discount=$total_tra_discount;
			$grand_total+=$grand_total_tra;
			$total+=$total_tra;
			/*end tinh tong 2 hoa don*/
			
			$data = array('date' => $date,
					'reference_no' => $reference,
					'customer_id' => $customer_id,
					'customer' => $customer,
					'biller_id' => $biller_id,
					'biller' => $biller,
					'doitac' => $doitac,
					'warehouse_id' => $warehouse_id,
					'note' => $note,
					'staff_note' => $staff_note,
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
					'total_items' => $total_items,
					'sale_status' => $sale_status,
					'payment_status' => $payment_status,
					'payment_term' => $payment_term,
					'due_date' => $due_date,
					'paid' => 0,
					'created_by' => $this->session->userdata('user_id'),
                    'total_weight' => $total_weight,
								);

			if ($payment_status == 'partial' || $payment_status == 'paid') {
				if ($this->input->post('paid_by') == 'deposit') {
					if ( ! $this->site->check_customer_deposit($customer_id, $this->input->post('amount-paid'))) {
						$this->session->set_flashdata('error', lang("amount_greater_than_deposit"));
						redirect($_SERVER["HTTP_REFERER"]);
					}
				}
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
                        'warehouse_id' => $warehouse_id,
						'note' => $this->input->post('payment_note'),
						'type' => 'received',
						'gc_balance' => $gc_balance,
                        'c_name' => $customer_details->name,
                        'c_phone' => $customer_details->phone,
                        'c_address' => $customer_details->address,
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
                        'warehouse_id' => $warehouse_id,
						'type' => 'received',
                        'c_name' => $customer_details->name,
                        'c_phone' => $customer_details->phone,
                        'c_address' => $customer_details->address,
					);
				}
			} else {
				$payment = array();
			}
			$dlDetails=null;
            if ((int)$doitac>0) {
                //get customer address
                $kh_obj=$this->site->getCompanyByID($customer_id); 
                $dlDetails = array(
                'date' => $date,
                'ngaynhan' => $date,
                'do_reference_no' =>$this->site->getReference('do'),
                'sale_reference_no' => $reference,
                'customer' => $customer,
                'address' => $kh_obj->address,
                'phone' => $kh_obj->phone,
                'status' => 'packing',
                'delivered_by' => $doitac,
                'shipping' => (float)str_replace(",","",$this->input->post('shipping')),
                'received_by' => '',
                'note' => '',
                'created_by' => $this->session->userdata('user_id'),
                'warehouse_id'=>$warehouse_id
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

            //$this->sma->print_arrays($data, $products, $payment);
        }

        if ($this->form_validation->run() == true && $this->sales_model->addSale($data, $products, $payment,null,null,$dlDetails)) {
            $this->session->set_userdata('remove_slls', 1);
            if ($quote_id) {
				$this->db->update('quotes', array('status' => 'completed'), array('id' => $quote_id));
            }


            $this->session->set_flashdata('message', lang("sale_added"));
            redirect("sales");
        } else {

            if ($quote_id || $sale_id) {
				if ($quote_id) {
					$this->data['quote'] = $this->sales_model->getQuoteByID($quote_id);
					$items = $this->sales_model->getAllQuoteItems($quote_id);
				} elseif ($sale_id) {
					$this->data['quote'] = $this->sales_model->getInvoiceByID($sale_id);
					$items = $this->sales_model->getAllInvoiceItems($sale_id);
				}
				krsort($items);
				$c = rand(100000, 9999999);
				foreach ($items as $item) {
					$row = $this->site->getProductByID($item->product_id);
					if (!$row) {
						$row = json_decode('{}');
						$row->tax_method = 0;
					} else {
						unset($row->cost, $row->details, $row->product_details, $row->image, $row->barcode_symbology, $row->cf1, $row->cf2, $row->cf3, $row->cf4, $row->cf5, $row->cf6, $row->supplier1price, $row->supplier2price, $row->cfsupplier3price, $row->supplier4price, $row->supplier5price, $row->supplier1, $row->supplier2, $row->supplier3, $row->supplier4, $row->supplier5, $row->supplier1_part_no, $row->supplier2_part_no, $row->supplier3_part_no, $row->supplier4_part_no, $row->supplier5_part_no);
					}
					$row->quantity = 0;
					$pis = $this->site->getPurchasedItems($item->product_id, $item->warehouse_id, $item->option_id);
					if ($pis) {
						foreach ($pis as $pi) {
							$row->quantity += $pi->quantity_balance;
						}
					}
					$row->id = $item->product_id;
					$row->code = $item->product_code;
					$row->name = $item->product_name;
					$row->type = $item->product_type;
					$row->qty = $item->quantity;
					$row->base_quantity = $item->quantity;
					$row->base_unit = $row->unit ? $row->unit : $item->product_unit_id;
					$row->base_unit_price = $row->price ? $row->price : $item->unit_price;
					$row->unit = $item->product_unit_id;
					$row->qty = $item->unit_quantity;
					$row->discount = $item->discount ? $item->discount : '0';
					$row->price = $this->sma->formatDecimal($item->net_unit_price + $this->sma->formatDecimal($item->item_discount / $item->quantity));
					$row->unit_price = $row->tax_method ? $item->unit_price + $this->sma->formatDecimal($item->item_discount / $item->quantity) + $this->sma->formatDecimal($item->item_tax / $item->quantity) : $item->unit_price + ($item->item_discount / $item->quantity);
					$row->real_unit_price = $item->real_unit_price;
					$row->tax_rate = $item->tax_rate_id;
					$row->serial = '';
					$row->option = $item->option_id;
					$options = $this->sales_model->getProductOptions($row->id, $item->warehouse_id);
					if ($options) {
						$option_quantity = 0;
						foreach ($options as $option) {
							$pis = $this->site->getPurchasedItems($row->id, $item->warehouse_id, $item->option_id);
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
					$combo_items = false;
					if ($row->type == 'combo') {
						$combo_items = $this->sales_model->getProductComboItems($row->id, $item->warehouse_id);
					}
					$units = $this->site->getUnitsByBUID($row->base_unit);
					$tax_rate = $this->site->getTaxRateByID($row->tax_rate);
				   
					$pr[$c] = array('id' => $c, 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 
							'row' => $row, 'combo_items' => $combo_items, 'tax_rate' => $tax_rate, 'units' => $units, 'options' => $options);
					$c++;
				}
				$this->data['quote_items'] = json_encode($pr);
            }
            //tien hanh lay danh sach khuyen mai theo san pham neu co 
            $khuyenmai_main=$this->getKhuyenmainewsByNow();
            $main_product=array();
            if(count($khuyenmai_main)>0){               
                foreach($khuyenmai_main as $main_pr){
                    $main_product[]=$main_pr->main_product_id;
                }               
            }
            $this->data['khuyenmai_main'] = $khuyenmai_main;

            $khuyenmai_product=$this->getKhuyenmainewsProductByNow();

            $this->data['doitacs'] = $this->site->getAllDoitac(); 
            
            $this->data['sub_product'] = $khuyenmai_product;
            $this->data['main_product'] = $main_product;

            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['quote_id'] = $quote_id ? $quote_id : $sale_id;
            $this->data['billers'] = $this->site->getAllCompanies('biller');
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['tax_rates'] = $this->site->getAllTaxRates();
            //$this->data['currencies'] = $this->sales_model->getAllCurrencies();
            $this->data['slnumber'] = ''; //$this->site->getReference('so');
            $this->data['payment_ref'] = ''; //$this->site->getReference('pay');
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('sales'), 'page' => lang('sales')), array('link' => '#', 'page' => lang('add_sale')));
            $meta = array('page_title' => lang('add_sale'), 'bc' => $bc);
            $this->page_construct('sales/add', $meta, $this->data);
        }
    }

    /* ------------------------------------------------------------------------ */

    public function edit($id = null)
    {
        $this->sma->checkPermissions();

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $inv = $this->sales_model->getInvoiceByID($id);
        if ($inv->sale_status == 'returned' || $inv->return_id || $inv->return_sale_ref) {
            $this->session->set_flashdata('error', lang('Đơn hàng có thu hồi sản phẩm không thể cập nhật, vui lòng hủy đơn hàng'));
            redirect(isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : 'welcome');
        }
		if($this->sales_model->checkCoSanPhamTraHangTrongHD($id)){
			$this->session->set_flashdata('error', lang('Đơn hàng có sản phẩm trả hàng không thể cập nhật, vui lòng hủy đơn hàng'));
            redirect(isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : 'welcome');
		}
        
        if ($inv->sale_status!='pending'||$inv->api_id==0) {
            if (!$this->session->userdata('edit_right')) {
                $this->sma->view_rights($inv->created_by);
            }
        }
        

        $this->form_validation->set_message('is_natural_no_zero', lang("no_zero_required"));
        $this->form_validation->set_rules('reference_no', lang("reference_no"), 'required');
        $this->form_validation->set_rules('customer', lang("customer"), 'required');
        $this->form_validation->set_rules('biller', lang("biller"), 'required');
        $this->form_validation->set_rules('sale_status', lang("sale_status"), 'required');
        $this->form_validation->set_rules('payment_status', lang("payment_status"), 'required');

        if ($this->form_validation->run() == true) {

            $reference = $this->input->post('reference_no');
            if ($this->Owner || $this->Admin) {
				$date = $this->sma->fld(trim($this->input->post('date')));
            } else {
				$date = $inv->date;	
            }
            $warehouse_id = $this->input->post('warehouse');
            $customer_id = $this->input->post('customer');
            $biller_id = $this->input->post('biller');
            $total_items = $this->input->post('total_items');
            $sale_status = $this->input->post('sale_status');
            $payment_status = $this->input->post('payment_status');
            $payment_term = $this->input->post('payment_term');
			$doitac = $this->input->post('doitac');
            $due_date = $payment_term ? date('Y-m-d', strtotime('+' . $payment_term . ' days', strtotime($date))) : null;
            $shipping = $this->input->post('shipping') ? $this->input->post('shipping') : 0;
            $customer_details = $this->site->getCompanyByID($customer_id);
            $customer = $customer_details->company != '-'  ? $customer_details->company : $customer_details->name;
            $biller_details = $this->site->getCompanyByID($biller_id);
            $biller = $biller_details->company != '-' ? $biller_details->company : $biller_details->name;
            $note = $this->sma->clear_tags($this->input->post('note'));
            $staff_note = $this->sma->clear_tags($this->input->post('staff_note'));
            $products=[];
            $total = 0;
            $total_weight=0;
            $product_tax = 0;
            $order_tax = 0;
            $product_discount = 0;
            $order_discount = 0;
            $percentage = '%';
            $i = isset($_POST['product_code']) ? sizeof($_POST['product_code']) : 0;
            for ($r = 0; $r < $i; $r++) {
				$item_id = $_POST['product_id'][$r];
				$item_type = $_POST['product_type'][$r];
				$item_code = $_POST['product_code'][$r];
				$item_name = $_POST['product_name'][$r];
                $data_id_khuyenmai=$_POST['data_id_khuyenmai'][$r];
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
				$product_details = $item_type != 'manual' ? $this->sales_model->getProductByCode($item_code) : null;
				$weight=(float)$product_details->weight;
                $total_weight+=($weight*$item_quantity);
                // $unit_price = $real_unit_price;
				$pr_discount = 0;

				if (isset($item_discount)) {
					$discount = $item_discount;
					$dpos = strpos($discount, $percentage);
					if ($dpos !== false) {
						$pds = explode("%", $discount);
						$pr_discount = $this->sma->formatDecimal(((($this->sma->formatDecimal($unit_price)) * (Float) ($pds[0])) / 100), 4);
					} else {
						$pr_discount = $this->sma->formatDecimal($discount);
					}
				}

				//$unit_price = $this->sma->formatDecimal($unit_price - $pr_discount);
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
					$pr_item_tax = $this->sma->formatDecimal($item_tax * $item_unit_quantity, 4);

				}

				$product_tax += $pr_item_tax;
				$subtotal = (($item_net_price * $item_unit_quantity) + $pr_item_tax)-$pr_item_discount;
				$unit = $this->site->getUnitByID($item_unit);

				$products[] = array(
					'product_id' => $item_id,
					'product_code' => $item_code,
					'product_name' => $item_name,
					'product_type' => $item_type,
					'option_id' => $item_option,
					'net_unit_price' => $item_net_price,
					'unit_price' => $this->sma->formatDecimal($item_net_price + $item_tax),
					'quantity' => $item_quantity,
					'product_unit_id' => $item_unit,
					'product_unit_code' => $unit->code,
					'unit_quantity' => $item_unit_quantity,
					'warehouse_id' => $warehouse_id,
					'item_tax' => $pr_item_tax,
					'tax_rate_id' => $pr_tax,
					'tax' => $tax,
					'discount' => $item_discount,
					'item_discount' => $pr_item_discount,
					'subtotal' => $this->sma->formatDecimal($subtotal),
					'serial_no' => $item_serial,
					'real_unit_price' => $real_unit_price,
                    'data_id_khuyenmai' => $data_id_khuyenmai,
				);

				$total += $this->sma->formatDecimal(($item_net_price * $item_unit_quantity), 4)-$pr_item_discount;
			}
            }
            if (empty($products)) {
				$this->form_validation->set_rules('product', lang("order_items"), 'required');
            } else {
				krsort($products);
            }
            if ($this->input->post('order_discount')) {
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
				$order_tax_id = null;
            }

            $total_tax = $this->sma->formatDecimal(($product_tax + $order_tax), 4);
            $grand_total = $this->sma->formatDecimal(($total + $total_tax + $this->sma->formatDecimal($shipping) - $order_discount), 4);
            $data = array('date' => $date,
				'reference_no' => $reference,
				'customer_id' => $customer_id,
				'customer' => $customer,
				'biller_id' => $biller_id,
				'biller' => $biller,
				'doitac' => $doitac,
				'warehouse_id' => $warehouse_id,
				'note' => $note,
				'staff_note' => $staff_note,
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
				'total_items' => $total_items,
				'sale_status' => $sale_status,
				'payment_status' => $payment_status,
				'payment_term' => $payment_term,
				'due_date' => $due_date,
				'updated_by' => $this->session->userdata('user_id'),
				'updated_at' => date('Y-m-d H:i:s'),
                'total_weight' =>$total_weight,
							);
            $dlDetails=null;
            if ((int)$doitac>0) {
                //get customer address
                $kh_obj=$this->site->getCompanyByID($customer_id);
                $dlDetails = array(
                'date' => $date,
                'ngaynhan' => $date,
                'do_reference_no' =>$this->site->getReference('do'),
                'sale_reference_no' => $reference,
                'customer' => $customer, 
                'address' => $kh_obj->address,
                'phone' => $kh_obj->phone,
                'status' => 'packing',
                'delivered_by' => $doitac,
                'shipping' => (float)str_replace(",","",$this->input->post('shipping')),
                'received_by' => '',
                'note' => '',
                'created_by' => $this->session->userdata('user_id'),
                'warehouse_id' => $warehouse_id,
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

        if ($this->form_validation->run() == true && $this->sales_model->updateSale($id, $data, $products,$dlDetails)) {
            
            $this->SysnApiWoooUpdateStatusOrder($sale_status,$id);

            $this->session->set_userdata('remove_slls', 1);
            $this->session->set_flashdata('message', lang("sale_updated"));
            redirect($inv->pos ? 'pos/sales' : 'sales');
        } else {

            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

            $this->data['inv'] = $this->sales_model->getInvoiceByID($id);
            if ($this->Settings->disable_editing) {
				if ($this->data['inv']->date <= date('Y-m-d', strtotime('-'.$this->Settings->disable_editing.' days'))) {
					$this->session->set_flashdata('error', sprintf(lang("sale_x_edited_older_than_x_days"), $this->Settings->disable_editing));
					redirect($_SERVER["HTTP_REFERER"]);
				}
            }
            $inv_items = $this->sales_model->getAllInvoiceItemsNoReturn($id);
			
            krsort($inv_items);
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
				$pis = $this->site->getPurchasedItems($item->product_id, $item->warehouse_id, $item->option_id);
				if ($pis) {
					foreach ($pis as $pi) {
						$row->quantity += $pi->quantity_balance;
					}
				}
				$row->id = $item->product_id;
				$row->code = $item->product_code;
				$row->name = $item->product_name;
				$row->type = $item->product_type;
				$row->base_quantity = $item->quantity;
				$row->base_unit = $row->unit ? $row->unit : $item->product_unit_id;
				$row->base_unit_price = $row->price ? $row->price : $item->unit_price;
				$row->unit = $item->product_unit_id;
				$row->qty = $item->unit_quantity;
				$row->quantity += $item->quantity;
				$row->discount = $item->discount ? $item->discount : '0';
				$row->price = $this->sma->formatDecimal($item->net_unit_price + $this->sma->formatDecimal($item->item_discount / $item->quantity));
				$row->khuyenmai_main = $item->data_id_khuyenmai;
				$row->unit_price = $item->unit_price; 
				
				//$row->tax_method ? $item->unit_price + $this->sma->formatDecimal($item->item_discount / $item->quantity) + $this->sma->formatDecimal($item->item_tax / $item->quantity) : $item->unit_price + ($item->item_discount / $item->quantity);
				
				$row->real_unit_price = $item->real_unit_price;
				$row->tax_rate = $item->tax_rate_id;
				$row->serial = $item->serial_no;
				$row->option = $item->option_id;
				$options = $this->sales_model->getProductOptions($row->id, $item->warehouse_id);

				if ($options) {
					$option_quantity = 0;
					foreach ($options as $option) {
						$pis = $this->site->getPurchasedItems($row->id, $item->warehouse_id, $item->option_id);
						if ($pis) {
							foreach ($pis as $pi) {
				                $option_quantity += $pi->quantity_balance;
							}
						}
						$option_quantity += $item->quantity;
						if ($option->quantity > $option_quantity) {
							$option->quantity = $option_quantity;
						}
					}
				}

				$combo_items = false;
				if ($row->type == 'combo') {
					$combo_items = $this->sales_model->getProductComboItems($row->id, $item->warehouse_id);
					$te = $combo_items;
					foreach ($combo_items as $combo_item) {
						$combo_item->quantity = $combo_item->qty * $item->quantity;
					}
				}
				$units = $this->site->getUnitsByBUID($row->base_unit);
				$tax_rate = $this->site->getTaxRateByID($row->tax_rate);
				
                //$ri = $this->Settings->item_addition ? $row->id : $c;
				$ri =$c;   
				$pr[$ri] = array('id' => $c, 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 
					'row' => $row, 'combo_items' => $combo_items, 'tax_rate' => $tax_rate, 'units' => $units, 'options' => $options);
				$c++;
            }
			/*load san pham tra hang de cap nhat*/
			$inv_items_tra = $this->sales_model->getAllInvoiceItemsNoReturn($id);
			
            krsort($inv_items_tra);
            $c_tra = rand(100000, 9999999);
            foreach ($inv_items_tra as $item) {
				$item->quantity=abs($item->quantity);
				
				$row = $this->site->getProductByID($item->product_id);
				if (!$row) {
					$row = json_decode('{}');
					$row->tax_method = 0;
					$row->quantity = 0;
				} else {
					unset($row->cost, $row->details, $row->product_details, $row->image, $row->barcode_symbology, $row->cf1, $row->cf2, $row->cf3, $row->cf4, $row->cf5, $row->cf6, $row->supplier1price, $row->supplier2price, $row->cfsupplier3price, $row->supplier4price, $row->supplier5price, $row->supplier1, $row->supplier2, $row->supplier3, $row->supplier4, $row->supplier5, $row->supplier1_part_no, $row->supplier2_part_no, $row->supplier3_part_no, $row->supplier4_part_no, $row->supplier5_part_no);
				}
				$pis = $this->site->getPurchasedItems($item->product_id, $item->warehouse_id, $item->option_id);
				if ($pis) {
					foreach ($pis as $pi) {
						$row->quantity += $pi->quantity_balance;
					}
				}
				$row->id = $item->product_id;
				$row->code = $item->product_code;
				$row->name = $item->product_name;
				$row->type = $item->product_type;
				$row->base_quantity = $item->quantity;
				$row->base_unit = $row->unit ? $row->unit : $item->product_unit_id;
				$row->base_unit_price = $row->price ? $row->price : $item->unit_price;
				$row->unit = $item->product_unit_id;
				$row->qty = $item->unit_quantity;
				$row->quantity += $item->quantity;
				$row->discount = $item->discount ? $item->discount : '0';
				$row->price = $this->sma->formatDecimal($item->net_unit_price + $this->sma->formatDecimal($item->item_discount / $item->quantity));
				$row->unit_price = $row->tax_method ? $item->unit_price + $this->sma->formatDecimal($item->item_discount / $item->quantity) + $this->sma->formatDecimal($item->item_tax / $item->quantity) : $item->unit_price + ($item->item_discount / $item->quantity);
				$row->real_unit_price = $item->real_unit_price;
				$row->tax_rate = $item->tax_rate_id;
				$row->serial = $item->serial_no;
				$row->option = $item->option_id;
				$options = $this->sales_model->getProductOptions($row->id, $item->warehouse_id);
                //get parent main_product
                $row->khuyenmai_main = $item->data_id_khuyenmai;
				if ($options) {
					$option_quantity = 0;
					foreach ($options as $option) {
						$pis = $this->site->getPurchasedItems($row->id, $item->warehouse_id, $item->option_id);
						if ($pis) {
							foreach ($pis as $pi) {
				$option_quantity += $pi->quantity_balance;
							}
						}
						$option_quantity += $item->quantity;
						if ($option->quantity > $option_quantity) {
							$option->quantity = $option_quantity;
						}
					}
				}

				$combo_items = false;
				if ($row->type == 'combo') {
					$combo_items = $this->sales_model->getProductComboItems($row->id, $item->warehouse_id);
					$te = $combo_items;
					foreach ($combo_items as $combo_item) {
						$combo_item->quantity = $combo_item->qty * $item->quantity;
					}
				}
				$units = $this->site->getUnitsByBUID($row->base_unit);
				$tax_rate = $this->site->getTaxRateByID($row->tax_rate);
				$ri = $this->Settings->item_addition ? $row->id : $c_tra;
				   
				$pr_tra[$ri] = array('id' => $c_tra, 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 
					'row' => $row, 'combo_items' => $combo_items, 'tax_rate' => $tax_rate, 'units' => $units, 'options' => $options);
				$c_tra++;
            }
			/*end load san pham tra hang de cap nhat*/
			$this->data['inv_items_tra'] = json_encode($pr_tra);
			//tien hanh lay danh sach khuyen mai theo san pham neu co 
            $khuyenmai_main=$this->getKhuyenmainewsByNow();
            $main_product=array();
            if(count($khuyenmai_main)>0){               
                foreach($khuyenmai_main as $main_pr){
                    $main_product[]=$main_pr->main_product_id;
                }               
            }
            $this->data['khuyenmai_main'] = $khuyenmai_main;

            $khuyenmai_product=$this->getKhuyenmainewsProductByNow();
            

            $this->data['sub_product'] = $khuyenmai_product;
            $this->data['main_product'] = $main_product;

            $this->data['inv_items'] = json_encode($pr);
            $this->data['id'] = $id;
            //$this->data['currencies'] = $this->site->getAllCurrencies();
            $this->data['billers'] = ($this->Owner || $this->Admin || !$this->session->userdata('biller_id')) ? $this->site->getAllCompanies('biller') : null;
            $this->data['tax_rates'] = $this->site->getAllTaxRates();
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['doitacs'] = $this->site->getAllDoitac(); 
            

            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('sales'), 'page' => lang('sales')), array('link' => '#', 'page' => lang('edit_sale')));
            $meta = array('page_title' => lang('edit_sale'), 'bc' => $bc);
            $this->page_construct('sales/edit', $meta, $this->data);
        }
    }

    /* ------------------------------- */

    public function return_sale($id = null)
    {
        $this->sma->checkPermissions('return_sales');

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $sale = $this->sales_model->getInvoiceByID($id);
        if ($sale->return_id) {
            $this->session->set_flashdata('error', lang("sale_already_returned"));
            redirect($_SERVER["HTTP_REFERER"]);
        }
		if($sale->sale_status=='returned'){					
			$this->session->set_flashdata('error', 'Không thể hoàn lại đơn hoàn, vui lòng xóa');
			redirect($_SERVER["HTTP_REFERER"]);
		}
            
			
        $this->form_validation->set_rules('return_surcharge', lang("return_surcharge"), 'required');

        if ($this->form_validation->run() == true) {

            $reference = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('re');
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
            $i = isset($_POST['product_code']) ? sizeof($_POST['product_code']) : 0;
            for ($r = 0; $r < $i; $r++) {
				$item_id = $_POST['product_id'][$r];
				$item_type = $_POST['product_type'][$r];
				$item_code = $_POST['product_code'][$r];
				$item_name = $_POST['product_name'][$r];
				$sale_item_id = $_POST['sale_item_id'][$r];
				$item_option = isset($_POST['product_option'][$r]) && $_POST['product_option'][$r] != 'false' ? $_POST['product_option'][$r] : null;
				$real_unit_price = $this->sma->formatDecimal($_POST['real_unit_price'][$r]);
				$unit_price = $this->sma->formatDecimal($_POST['unit_price'][$r]);
				$item_unit_quantity = (0-$_POST['quantity'][$r]);
				$item_serial = isset($_POST['serial'][$r]) ? $_POST['serial'][$r] : '';
				$item_tax_rate = isset($_POST['product_tax'][$r]) ? $_POST['product_tax'][$r] : null;
				$item_discount = isset($_POST['product_discount'][$r]) ? $_POST['product_discount'][$r] : null;
				$item_unit = $_POST['product_unit'][$r];
				$item_quantity = (0-$_POST['product_base_quantity'][$r]);

				if (isset($item_code) && isset($real_unit_price) && isset($unit_price) && isset($item_quantity)) {
					$product_details = $item_type != 'manual' ? $this->sales_model->getProductByCode($item_code) : null;
					// $unit_price = $real_unit_price;
					$pr_discount = 0;

					if (isset($item_discount)) {
						$discount = $item_discount;
						$dpos = strpos($discount, $percentage);
						if ($dpos !== false) {
							$pds = explode("%", $discount);
							$pr_discount = $this->sma->formatDecimal(((($this->sma->formatDecimal($unit_price)) * (Float) ($pds[0])) / 100), 4);
						} else {
							$pr_discount = $this->sma->formatDecimal($discount, 4);
						}
					}

					$unit_price = $this->sma->formatDecimal(($unit_price - $pr_discount), 4);
					$item_net_price = $unit_price;
					$pr_item_discount = $this->sma->formatDecimal($pr_discount * $item_unit_quantity, 4);
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
					$subtotal = $this->sma->formatDecimal((($item_net_price * $item_unit_quantity) + $pr_item_tax), 4);
					$unit = $item_unit ? $this->site->getUnitByID($item_unit) : FALSE;

					$products[] = array(
						'product_id' => $item_id,
						'product_code' => $item_code,
						'product_name' => $item_name,
						'product_type' => $item_type,
						'option_id' => $item_option,
						'net_unit_price' => $item_net_price,
						'unit_price' => $this->sma->formatDecimal($item_net_price + $item_tax),
						'quantity' => $item_quantity,
						'product_unit_id' => $item_unit,
						'product_unit_code' => $unit ? $unit->code : NULL,
						'unit_quantity' => $item_unit_quantity,
						'warehouse_id' => $sale->warehouse_id,
						'item_tax' => $pr_item_tax,
						'tax_rate_id' => $pr_tax,
						'tax' => $tax,
						'discount' => $item_discount,
						'item_discount' => $pr_item_discount,
						'subtotal' => $this->sma->formatDecimal($subtotal),
						'serial_no' => $item_serial,
						'real_unit_price' => $real_unit_price,
						'sale_item_id' => $sale_item_id,
					);

					$si_return[] = array(
						'id' => $sale_item_id,
						'sale_id' => $id,
						'product_id' => $item_id,
						'option_id' => $item_option,
						'quantity' => (0-$item_quantity),
						'warehouse_id' => $sale->warehouse_id,
						);

					$total += $this->sma->formatDecimal(($item_net_price * $item_unit_quantity), 4);
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
					$order_discount = $this->sma->formatDecimal($order_discount_id, 4);
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

            $total_tax = $this->sma->formatDecimal($product_tax + $order_tax, 4);
            $grand_total = $this->sma->formatDecimal(($total + $total_tax + $this->sma->formatDecimal($return_surcharge) - $order_discount), 4);
            $data = array('date' => $date,
				'sale_id' => $id,
				'reference_no' => $sale->reference_no,
				'customer_id' => $sale->customer_id,
				'customer' => $sale->customer,
				'biller_id' => $sale->biller_id,
				'biller' => $sale->biller,
				'warehouse_id' => $sale->warehouse_id,
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
				'return_sale_ref' => $reference,
				'sale_status' => 'returned',
				'pos' => $sale->pos,
				'payment_status' => $sale->payment_status == 'paid' ? 'due' : 'pending',
							);
            if ($this->input->post('amount-paid') && $this->input->post('amount-paid') > 0) {
				$pay_ref = $this->input->post('payment_reference_no') ? $this->input->post('payment_reference_no') : $this->site->getReference('pay');
				$payment = array(
					'date' => $date,
					'reference_no' => $pay_ref,
					'amount' => (0-$this->input->post('amount-paid')),
					'paid_by' => $this->input->post('paid_by'),
					'cheque_no' => $this->input->post('cheque_no'),
					'cc_no' => $this->input->post('pcc_no'),
					'cc_holder' => $this->input->post('pcc_holder'),
					'cc_month' => $this->input->post('pcc_month'),
					'cc_year' => $this->input->post('pcc_year'),
					'cc_type' => $this->input->post('pcc_type'),
					'created_by' => $this->session->userdata('user_id'),
                    'warehouse_id' => $sale->warehouse_id,
					'type' => 'returned',
				);
				$data['payment_status'] = $grand_total == $this->input->post('amount-paid') ? 'paid' : 'partial';
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

            // $this->sma->print_arrays($data, $products, $si_return, $payment);
        }

        if ($this->form_validation->run() == true && $this->sales_model->addSale($data, $products, $payment, $si_return)) {
            $this->session->set_flashdata('message', lang("return_sale_added"));
            redirect($sale->pos ? "pos/sales" : "sales");
        } else {

            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

            $this->data['inv'] = $sale;
            if ($this->data['inv']->sale_status != 'completed') {
				$this->session->set_flashdata('error', lang("sale_status_x_competed"));
				redirect($_SERVER["HTTP_REFERER"]);
            }
            if ($this->data['inv']->date <= date('Y-m-d', strtotime('-3 months'))) {
				$this->session->set_flashdata('error', lang("sale_x_edited_older_than_3_months"));
				redirect($_SERVER["HTTP_REFERER"]);
            }
            $inv_items = $this->sales_model->getAllInvoiceItems($id);
            krsort($inv_items);
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
				$pis = $this->site->getPurchasedItems($item->product_id, $item->warehouse_id, $item->option_id);
				if ($pis) {
					foreach ($pis as $pi) {
						$row->quantity += $pi->quantity_balance;
					}
				}
				$row->id = $item->product_id;
				$row->sale_item_id = $item->id;
				$row->code = $item->product_code;
				$row->name = $item->product_name;
				$row->type = $item->product_type;
				$row->base_quantity = $item->quantity;
				$row->base_unit = $row->unit ? $row->unit : $item->product_unit_id;
				$row->base_unit_price = $row->price ? $row->price : $item->unit_price;
				$row->unit = $item->product_unit_id;
				$row->qty = $item->unit_quantity;
				$row->oqty = $item->unit_quantity;
				$row->discount = $item->discount ? $item->discount : '0';
				$row->price = $this->sma->formatDecimal($item->net_unit_price + $this->sma->formatDecimal($item->item_discount / $item->quantity));
				$row->unit_price = $row->tax_method ? $item->unit_price + $this->sma->formatDecimal($item->item_discount / $item->quantity) + $this->sma->formatDecimal($item->item_tax / $item->quantity) : $item->unit_price + ($item->item_discount / $item->quantity);
				$row->real_unit_price = $item->real_unit_price;
				$row->tax_rate = $item->tax_rate_id;
				$row->serial = $item->serial_no;
				$row->option = $item->option_id;
				$options = $this->sales_model->getProductOptions($row->id, $item->warehouse_id, true);
				$units = $this->site->getUnitsByBUID($row->base_unit);
				$tax_rate = $this->site->getTaxRateByID($row->tax_rate);
				$ri = $this->Settings->item_addition ? $row->id : $c;
				

				$pr[$ri] = array('id' => $c, 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 'row' => $row, 'units' => $units, 'tax_rate' => $tax_rate, 'options' => $options);
				$c++;
            }
            $this->data['inv_items'] = json_encode($pr);
            $this->data['id'] = $id;
            $this->data['payment_ref'] = '';
            $this->data['reference'] = ''; // $this->site->getReference('re');
            $this->data['tax_rates'] = $this->site->getAllTaxRates();
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('sales'), 'page' => lang('sales')), array('link' => '#', 'page' => lang('return_sale')));
            $meta = array('page_title' => lang('return_sale'), 'bc' => $bc);
            $this->page_construct('sales/return_sale', $meta, $this->data);
        }
    }

    /* ------------------------------- */

    public function delete($id = null)
    {
        $this->sma->checkPermissions(null, true);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        $inv = $this->sales_model->getInvoiceByID($id);
        if ($inv->sale_status == 'returned') {
           $this->delete_return($id);
        }

        if ($this->sales_model->deleteSale($id)) {
            if ($this->input->is_ajax_request()) {
				$this->sma->send_json(array('error' => 0, 'msg' => lang("sale_deleted")));
            }
            $this->session->set_flashdata('message', lang('sale_deleted'));
            redirect('welcome');
        }
    }

    public function delete_return($id = null)
    {
        $this->sma->checkPermissions(null, true);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        if ($this->sales_model->deleteSaleReturn($id)) {
            if ($this->input->is_ajax_request()) {
				$this->sma->send_json(array('error' => 0, 'msg' => lang("return_sale_deleted")));
            }
            $this->session->set_flashdata('message', lang('return_sale_deleted'));
            redirect('welcome');
        }
    }

    public function sale_actions()
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
						$this->sales_model->deleteSale($id);
					}
					$this->session->set_flashdata('message', lang("sales_deleted"));
					redirect($_SERVER["HTTP_REFERER"]);

				} elseif ($this->input->post('form_action') == 'combine') {

					$html = $this->combine_pdf($_POST['val']); 

				} elseif ($this->input->post('form_action') == 'export_excel' || $this->input->post('form_action') == 'export_pdf') {

					$this->load->library('excel');
					$this->excel->setActiveSheetIndex(0);
					$this->excel->getActiveSheet()->setTitle(lang('sales'));
					$this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
					$this->excel->getActiveSheet()->SetCellValue('B1', lang('reference_no'));
					$this->excel->getActiveSheet()->SetCellValue('C1', lang('Kho'));
					$this->excel->getActiveSheet()->SetCellValue('D1', lang('ĐV GH'));
					$this->excel->getActiveSheet()->SetCellValue('E1', lang('biller'));
					$this->excel->getActiveSheet()->SetCellValue('F1', lang('customer'));
					$this->excel->getActiveSheet()->SetCellValue('G1', lang('grand_total'));
					$this->excel->getActiveSheet()->SetCellValue('H1', lang('paid'));
					$this->excel->getActiveSheet()->SetCellValue('I1', lang('payment_status'));

					$row = 2;
					foreach ($_POST['val'] as $id) {
						$sale = $this->sales_model->getInvoiceByID($id);
						$warehouse=$this->site->getWarehouseByID($sale->warehouse_id);	
						$customer= $this->site->getCompanyByID($sale->customer_id); 
						$_dvgh= $this->doitac_model->getDoiTacByID($sale->doitac); 
						$_customer=$customer->phone."-".$customer->name;
						
						$gvgh=$_dvgh->name!=""?$_dvgh->name:"";
						
						$this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->sma->hrld($sale->date));
						$this->excel->getActiveSheet()->SetCellValue('B' . $row, $sale->reference_no);
						$this->excel->getActiveSheet()->SetCellValue('C' . $row, $warehouse->name);
						$this->excel->getActiveSheet()->SetCellValue('D' . $row, $gvgh);
						$this->excel->getActiveSheet()->SetCellValue('E' . $row, $sale->biller);
						$this->excel->getActiveSheet()->SetCellValue('F' . $row, $_customer);
						$this->excel->getActiveSheet()->SetCellValue('G' . $row, $sale->grand_total);
						$this->excel->getActiveSheet()->SetCellValue('H' . $row, lang($sale->paid));
						$this->excel->getActiveSheet()->SetCellValue('I' . $row, lang($sale->payment_status));
						$row++;
					}

					$this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
					$this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
					$filename = 'sales_' . date('Y_m_d_H_i_s');
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
				$this->session->set_flashdata('error', lang("no_sale_selected"));
				redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }

    /* ------------------------------- */

    public function deliveries($warehouse_id=null)
    {
        $this->sma->checkPermissions();
         if ($this->Owner || $this->Admin || !$this->session->userdata('warehouse_id')) {
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['warehouse_id'] = $warehouse_id;
            $this->data['warehouse'] = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : null;
        } else {
            $this->data['warehouses'] = null;
            $this->data['warehouse_id'] = $this->session->userdata('warehouse_id');
            $this->data['warehouse'] = $this->session->userdata('warehouse_id') ? $this->site->getWarehouseByID($this->session->userdata('warehouse_id')) : null;
        }

        $data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('sales'), 'page' => lang('sales')), array('link' => '#', 'page' => lang('deliveries')));
        $meta = array('page_title' => lang('deliveries'), 'bc' => $bc);
        $this->page_construct('sales/deliveries', $meta, $this->data);

    }

    public function getDeliveries($warehouse_id=null)
    {
        $this->sma->checkPermissions('deliveries');

        $detail_link = anchor('sales/view_delivery/$1', '<i class="fa fa-file-text-o"></i> ' . lang('delivery_details'), 'data-toggle="modal" data-target="#myModal"');
        $email_link = anchor('sales/email_delivery/$1', '<i class="fa fa-envelope"></i> ' . lang('email_delivery'), 'data-toggle="modal" data-target="#myModal"');
        $edit_link = anchor('sales/edit_delivery/$1', '<i class="fa fa-edit"></i> ' . lang('edit_delivery'), 'data-toggle="modal" data-target="#myModal"');
        $pdf_link = anchor('sales/pdf_delivery/$1', '<i class="fa fa-file-pdf-o"></i> ' . lang('download_pdf'));
        $delete_link = "<a href='#' class='po' title='<b>" . lang("delete_delivery") . "</b>' data-content=\"<p>"
        . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('sales/delete_delivery/$1') . "'>"
        . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
        . lang('delete_delivery') . "</a>";
        $print_link = anchor('sales/printgiao/$1', '<i class="fa fa-print"></i> ' . lang('In giao hàng'), 'data-toggle="modal" data-target="#myModal"');
        $action = '<div class="text-center"><div class="btn-group text-left">'
        . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
        . lang('actions') . ' <span class="caret"></span></button>
    <ul class="dropdown-menu pull-right" role="menu">
        <li>' . $edit_link . '</li>
        <li>' . $print_link . '</li>
        <li>' . $delete_link . '</li>
    </ul>
</div></div>';

        $this->load->library('datatables');
        //GROUP_CONCAT(CONCAT('Name: ', sale_items.product_name, ' Qty: ', sale_items.quantity ) SEPARATOR '<br>')
        $this->datatables
            ->select("deliveries.id as id, date,ngaynhan, do_reference_no, sale_reference_no,(SELECT CONCAT(code, '-',name) FROM scodeweb_doitac WHERE id=delivered_by) as doitac,shipping, customer,phone, address, status, attachment,purchase_id")
            ->from('deliveries')
            ->join('sale_items', 'sale_items.sale_id=deliveries.sale_id', 'left')
            ->group_by('deliveries.id');

        if ($warehouse_id) {
            $this->datatables->where('deliveries.warehouse_id', $warehouse_id);
        }
        $this->datatables->add_column("Actions", $action, "id");

        echo $this->datatables->generate();
    }

    public function pdf_delivery($id = null, $view = null, $save_bufffer = null)
    {
        $this->sma->checkPermissions();

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $deli = $this->sales_model->getDeliveryByID($id);

        $this->data['delivery'] = $deli;
        $sale = $this->sales_model->getInvoiceByID($deli->sale_id);
        $this->data['biller'] = $this->site->getCompanyByID($sale->biller_id);
        $this->data['rows'] = $this->sales_model->getAllInvoiceItemsWithDetails($deli->sale_id);
        $this->data['user'] = $this->site->getUser($deli->created_by);

        $name = lang("delivery") . "_" . str_replace('/', '_', $deli->do_reference_no) . ".pdf";
        $html = $this->load->view($this->theme . 'sales/pdf_delivery', $this->data, true);
        if (! $this->Settings->barcode_img) {
            $html = preg_replace("'\<\?xml(.*)\?\>'", '', $html);
        }
        if ($view) {
            $this->load->view($this->theme . 'sales/pdf_delivery', $this->data);
        } elseif ($save_bufffer) {
            return $this->sma->generate_pdf($html, $name, $save_bufffer);
        } else {
            $this->sma->generate_pdf($html, $name);
        }
    }

    public function view_delivery($id = null)
    {
        $this->sma->checkPermissions('deliveries');

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $deli = $this->sales_model->getDeliveryByID($id);
        $sale = $this->sales_model->getInvoiceByID($deli->sale_id);
        if (!$sale) {
            $this->session->set_flashdata('error', lang('sale_not_found'));
            $this->sma->md();
        }
        $this->data['delivery'] = $deli;
        $this->data['biller'] = $this->site->getCompanyByID($sale->biller_id);
        $this->data['rows'] = $this->sales_model->getAllInvoiceItemsWithDetails($deli->sale_id);
        $this->data['user'] = $this->site->getUser($deli->created_by);
        $this->data['page_title'] = lang("delivery_order");

        $this->load->view($this->theme . 'sales/view_delivery', $this->data);
    }

    public function add_delivery($id = null)
    {
        $this->sma->checkPermissions();

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $sale = $this->sales_model->getInvoiceByID($id);
        if ($sale->sale_status != 'completed') {
            $this->session->set_flashdata('error', lang('Đơn hàng chưa hoàn thành không thể giao'));
            $this->sma->md();
        }

        if ($delivery = $this->sales_model->getDeliveryBySaleID($id)) {
            $this->edit_delivery($delivery->id);
        } else {

            $this->form_validation->set_rules('sale_reference_no', lang("sale_reference_no"), 'required');
            $this->form_validation->set_rules('customer', lang("customer"), 'required');
            $this->form_validation->set_rules('phone', lang("phone"), 'required');
            $this->form_validation->set_rules('address', lang("address"), 'required');
            $this->form_validation->set_rules('delivered_by', lang("delivered_by"), 'required');

            if ($this->form_validation->run() == true) {
    			if ($this->Owner || $this->Admin) {
    				$date = $this->sma->fld(trim($this->input->post('date')));
    			} else {
    				$date = date('Y-m-d H:i:s');
    			}
                $order=$this->site->getInvoiceByID($this->input->post('sale_id'));
                if ($order==false) {
                    $this->session->set_flashdata('error','Không tìm thấy hóa đơn');
                    redirect($_SERVER["HTTP_REFERER"]);
                }
    			$dlDetails = array(
    				'date' => $date,
                    'ngaynhan' => date("Y-m-d H:i:s",strtotime(str_replace("/","-",$this->input->post('ngaynhan')))),
    				'sale_id' => $this->input->post('sale_id'),
    				'do_reference_no' => $this->input->post('do_reference_no') ? $this->input->post('do_reference_no') : $this->site->getReference('do'),
    				'sale_reference_no' => $this->input->post('sale_reference_no'),
    				'customer' => $this->input->post('customer'),
    				'address' => $this->input->post('address'),
                    'phone' => $this->input->post('phone'),
    				'status' => $this->input->post('status'),
    				'delivered_by' => $this->input->post('delivered_by'),
    				'shipping' => $this->input->post('shipping'),
    				'received_by' => $this->input->post('received_by'),
    				'note' => $this->sma->clear_tags($this->input->post('note')),
    				'created_by' => $this->session->userdata('user_id'),
                    'warehouse_id' => $order->warehouse_id,
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

                
                

			} elseif ($this->input->post('add_delivery')) {
					$this->session->set_flashdata('error', validation_errors());
					redirect($_SERVER["HTTP_REFERER"]);
			}

			if ($this->form_validation->run() == true && $this->sales_model->addDelivery($dlDetails)) {
                //update sale object

                $shipping=(float)$this->input->post('shipping');
                $total=(float)$sale->grand_total-(float)$sale->shipping;
                $new_grandtotal=$total+$shipping;

                $this->db->update('sales', array('grand_total' =>$new_grandtotal, 'shipping' => $shipping), array('id' => $sale->id));


				$this->session->set_flashdata('message', lang("delivery_added"));
				redirect("sales/deliveries");
			} else {

    			$this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
    			$this->data['customer'] = $this->site->getCompanyByID($sale->customer_id);
    			$this->data['inv'] = $sale;
    			$this->data['do_reference_no'] = ''; //$this->site->getReference('do');
    			$this->data['modal_js'] = $this->site->modal_js();
                $this->data['doitacs'] = $this->site->getAllDoitac(); 

    			$this->load->view($this->theme . 'sales/add_delivery', $this->data);
			}
		}
	}

    public function edit_delivery($id = null)
    {
        $this->sma->checkPermissions();

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        $this->form_validation->set_rules('do_reference_no', lang("do_reference_no"), 'required');
        $this->form_validation->set_rules('sale_reference_no', lang("sale_reference_no"), 'required');
        $this->form_validation->set_rules('customer', lang("customer"), 'required');
        $this->form_validation->set_rules('phone', lang("phone"), 'required');
        $this->form_validation->set_rules('address', lang("address"), 'required');
        $this->form_validation->set_rules('delivered_by', lang("delivered_by"), 'required');

        if ($this->form_validation->run() == true) {

            //check sale id
            if ($this->input->post('sale_id')>0) {
                $order=$this->site->getInvoiceByID($this->input->post('sale_id'));
                if ($order==false) {
                    $this->session->set_flashdata('error','Không tìm thấy hóa đơn');
                    redirect("sales/deliveries");
                }
            }
            if ($this->input->post('purchase_id')>0) {
                $order=$this->site->getPurchaseByID($this->input->post('purchase_id'));
                if ($order==false) {
                    $this->session->set_flashdata('error','Không tìm thấy hóa đơn nhập hàng');
                    redirect("sales/deliveries");
                }
            }

            $dlDetails = array(
            'ngaynhan' => date("Y-m-d H:i:s",strtotime(str_replace("/","-",$this->input->post('ngaynhan')))),
			'do_reference_no' => $this->input->post('do_reference_no'),
			'sale_reference_no' => $this->input->post('sale_reference_no'),
			'customer' => $this->input->post('customer'),
			'address' => $this->input->post('address'),
            'phone' => $this->input->post('phone'),
			'status' => $this->input->post('status'),
			'delivered_by' => $this->input->post('delivered_by'),
			'shipping' => $this->input->post('shipping'),
			'received_by' => $this->input->post('received_by'),
			'note' => $this->sma->clear_tags($this->input->post('note')),
			'updated_by' => $this->session->userdata('user_id'),
            'updated_at' => date('Y-m-d H:i:s'),
            'warehouse_id' => $order->warehouse_id,
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

            if ($this->Owner || $this->Admin) {
				$date = $this->sma->fld(trim($this->input->post('date')));
				$dlDetails['date'] = $date;
            }
        } elseif ($this->input->post('edit_delivery')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }

        if ($this->form_validation->run() == true && $this->sales_model->updateDelivery($id, $dlDetails)) {

             //update sale object
            $shipping=(float)$this->input->post('shipping');
            if ($this->input->post('sale_id')>0) {
                $total=(float)$order->grand_total-(float)$order->shipping;
                $new_grandtotal=$total+$shipping;
                $this->db->update('sales', array('grand_total' => $new_grandtotal, 'shipping' => $shipping), array('id' => $order->id));
            }            
            $this->session->set_flashdata('message', lang("delivery_updated"));
            redirect("sales/deliveries");
        } else {

            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['delivery'] = $this->sales_model->getDeliveryByID($id);
            $this->data['modal_js'] = $this->site->modal_js();
            $this->data['doitacs'] = $this->site->getAllDoitac(); 
            $this->load->view($this->theme . 'sales/edit_delivery', $this->data);
        }
    }

    public function delete_delivery($id = null)
    {
        $this->sma->checkPermissions(null, true);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        if ($this->sales_model->deleteDelivery($id)) {
            $this->sma->send_json(array('error' => 0, 'msg' => lang("delivery_deleted")));
        }

    }

    public function delivery_actions()
    {
        if (!$this->Owner && !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER["HTTP_REFERER"]);
        }

        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');

        if ($this->form_validation->run() == true) {

            if (!empty($_POST['val'])) {
				if ($this->input->post('form_action') == 'delete') {
					$this->sma->checkPermissions('delete_delivery');
					foreach ($_POST['val'] as $id) {
						$this->sales_model->deleteDelivery($id);
					}
					$this->session->set_flashdata('message', lang("deliveries_deleted"));
					redirect($_SERVER["HTTP_REFERER"]);
				}

		if ($this->input->post('form_action') == 'export_excel' || $this->input->post('form_action') == 'export_pdf') {

			$this->load->library('excel');
			$this->excel->setActiveSheetIndex(0);
			$this->excel->getActiveSheet()->setTitle(lang('deliveries'));
			$this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
            $this->excel->getActiveSheet()->SetCellValue('B1', lang('Ngày nhận'));
			$this->excel->getActiveSheet()->SetCellValue('C1', lang('do_reference_no'));
			$this->excel->getActiveSheet()->SetCellValue('D1', lang('sale_reference_no'));
			$this->excel->getActiveSheet()->SetCellValue('E1', lang('Đối tác'));
			$this->excel->getActiveSheet()->SetCellValue('F1', lang('Phí'));
			$this->excel->getActiveSheet()->SetCellValue('G1', lang('customer'));
            $this->excel->getActiveSheet()->SetCellValue('H1', lang('phone'));
			$this->excel->getActiveSheet()->SetCellValue('I1', lang('address'));
			$this->excel->getActiveSheet()->SetCellValue('J1', lang('status'));

			$row = 2;
			foreach ($_POST['val'] as $id) {
				$delivery = $this->sales_model->getDeliveryByID($id);
				$doitac=$this->doitac_model->getDoitacByID($delivery->delivered_by);				
				$_doitac=$doitac->name;
				$this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->sma->hrld($delivery->date));
                $this->excel->getActiveSheet()->SetCellValue('B' . $row, $this->sma->hrld($delivery->ngaynhan));
				$this->excel->getActiveSheet()->SetCellValue('C' . $row, $delivery->do_reference_no);
				$this->excel->getActiveSheet()->SetCellValue('D' . $row, $delivery->sale_reference_no);
				$this->excel->getActiveSheet()->SetCellValue('E' . $row, $_doitac);
				$this->excel->getActiveSheet()->SetCellValue('F' . $row, $delivery->shipping);
				$this->excel->getActiveSheet()->SetCellValue('G' . $row, $delivery->customer);
                $this->excel->getActiveSheet()->SetCellValue('H' . $row, $delivery->phone);
				$this->excel->getActiveSheet()->SetCellValue('I' . $row, $delivery->address);
				$this->excel->getActiveSheet()->SetCellValue('J' . $row, lang($delivery->status));
				$row++;
			}

			$this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
			$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);

			$filename = 'deliveries_' . date('Y_m_d_H_i_s');
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
				$this->session->set_flashdata('error', lang("no_delivery_selected"));
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
        $this->data['payments'] = $this->sales_model->getInvoicePayments($id);
        $this->data['inv'] = $this->sales_model->getInvoiceByID($id);
        $this->load->view($this->theme . 'sales/payments', $this->data);
    }

    public function payment_note($id = null)
    {
        $this->sma->checkPermissions('payments', true);
        $payment = $this->sales_model->getPaymentByID($id);
        $inv = $this->sales_model->getInvoiceByID($payment->sale_id);
		if($payment->id_ncc_id_kh>0){
			$this->data['biller'] = $this->site->getCompanyByID($payment->id_ncc_id_kh);		
			$this->data['customer'] = $this->site->getCompanyByID($payment->id_ncc_id_kh);
		}else{
			$this->data['biller'] = $this->site->getCompanyByID($inv->biller_id);		
			$this->data['customer'] = $this->site->getCompanyByID($inv->customer_id);
			$this->data['inv'] = $inv;
		}
        $this->data['payment'] = $payment;
        $this->data['page_title'] = lang("payment_note");

        $this->load->view($this->theme . 'sales/payment_note', $this->data);
    }

    public function email_payment($id = null)
    {
        $this->sma->checkPermissions('payments', true);
        $payment = $this->sales_model->getPaymentByID($id);
        $inv = $this->sales_model->getInvoiceByID($payment->sale_id);
        $this->data['biller'] = $this->site->getCompanyByID($inv->biller_id);
        $customer = $this->site->getCompanyByID($inv->customer_id);
        if ( ! $customer->email) {
            $this->sma->send_json(array('msg' => lang("update_customer_email")));
        }
        $this->data['inv'] = $inv;
        $this->data['payment'] = $payment;
        $this->data['customer'] =$customer;
        $this->data['page_title'] = lang("payment_note");
        $html = $this->load->view($this->theme . 'sales/payment_note', $this->data, TRUE);

        $html = str_replace(array('<i class="fa fa-2x">&times;</i>', 'modal-', '<p>&nbsp;</p>', '<p style="border-bottom: 1px solid #666;">&nbsp;</p>', '<p>'.lang("stamp_sign").'</p>'), '', $html);
        $html = preg_replace("/<img[^>]+\>/i", '', $html);
        // $html = '<div style="border:1px solid #DDD; padding:10px; margin:10px 0;">'.$html.'</div>';

        $this->load->library('parser');
        $parse_data = array(
            'stylesheet' => '<link href="'.$this->data['assets'].'styles/helpers/bootstrap.min.css" rel="stylesheet"/>',
            'name' => $customer->company && $customer->company != '-' ? $customer->company :  $customer->name,
            'email' => $customer->email,
            'heading' => lang('payment_note').'<hr>',
            'msg' => $html,
            'site_link' => base_url(),
            'site_name' => $this->Settings->site_name,
            'logo' => '<img src="' . base_url('assets/uploads/logos/' . $this->Settings->logo) . '" alt="' . $this->Settings->site_name . '"/>'
        );
        $msg = file_get_contents('./themes/' . $this->Settings->theme . '/views/email_templates/email_con.html');
        $message = $this->parser->parse_string($msg, $parse_data);
        $subject = lang('payment_note') . ' - ' . $this->Settings->site_name;

        if ($this->sma->send_email($customer->email, $subject, $message)) {
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
        $sale = $this->sales_model->getInvoiceByID($id);
        if ($sale->payment_status == 'paid' && $sale->grand_total == $sale->paid) {
            $this->session->set_flashdata('error', lang("sale_already_paid"));
            $this->sma->md();
        }

        //$this->form_validation->set_rules('reference_no', lang("reference_no"), 'required');
        $this->form_validation->set_rules('amount-paid', lang("amount"), 'required');
        $this->form_validation->set_rules('paid_by', lang("paid_by"), 'required');
        $this->form_validation->set_rules('userfile', lang("attachment"), 'xss_clean');
        if ($this->form_validation->run() == true) {
            if ($this->input->post('paid_by') == 'deposit') {
				$sale = $this->sales_model->getInvoiceByID($this->input->post('sale_id'));
				$customer_id = $sale->customer_id;
    			if ( ! $this->site->check_customer_deposit($customer_id, $this->input->post('amount-paid'))) {
    				$this->session->set_flashdata('error', lang("amount_greater_than_deposit"));
    				redirect($_SERVER["HTTP_REFERER"]);
    			}
            } else {
				$customer_id = null;
            }
            if ($this->Owner || $this->Admin) {
				$date = $this->sma->fld(trim($this->input->post('date')));
            } else {
				$date = date('Y-m-d H:i:s');
            }
            $payment = array(
					'date' => $date,
					'sale_id' => $this->input->post('sale_id'),
					'reference_no' => $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('pay'),
					'amount' => $this->input->post('amount-paid'),
					'paid_by' => $this->input->post('paid_by'),
					'cheque_no' => $this->input->post('cheque_no'),
					'cc_no' => $this->input->post('paid_by') == 'gift_card' ? $this->input->post('gift_card_no') : $this->input->post('pcc_no'),
					'cc_holder' => $this->input->post('pcc_holder'),
					'cc_month' => $this->input->post('pcc_month'),
					'cc_year' => $this->input->post('pcc_year'),
					'cc_type' => $this->input->post('pcc_type'),
					'note' => $this->input->post('note'),
					'created_by' => $this->session->userdata('user_id'),
                    'warehouse_id' => $sale->warehouse_id,
					'type' => 'received',
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

        if ($this->form_validation->run() == true && $this->sales_model->addPayment($payment, $customer_id)) {
            $this->session->set_flashdata('message', lang("payment_added"));
            redirect($_SERVER["HTTP_REFERER"]);
        } else {

            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            if ($sale->sale_status == 'returned' && $sale->paid == $sale->grand_total) {
				$this->session->set_flashdata('warning', lang('payment_was_returned'));
				$this->sma->md();
            }
            $this->data['inv'] = $sale;
            //get customer info by purchase_id
            $ncc=$this->site->getCompanyByID($sale->customer_id);
            $this->data['ncc'] = $ncc;

            $this->data['payment_ref'] = ''; //$this->site->getReference('pay');
            $this->data['modal_js'] = $this->site->modal_js();

            $this->load->view($this->theme . 'sales/add_payment', $this->data);
        }
    }
	

    public function edit_payment($id = null)
    {
        $this->sma->checkPermissions('edit', true);
        $this->load->helper('security');
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $payment = $this->sales_model->getPaymentByID($id);
        if ($payment->paid_by == 'ppp' || $payment->paid_by == 'stripe') {
            $this->session->set_flashdata('error', lang('x_edit_payment'));
            $this->sma->md();
        }
        $this->form_validation->set_rules('reference_no', lang("reference_no"), 'required');
        $this->form_validation->set_rules('amount-paid', lang("amount"), 'required');
        $this->form_validation->set_rules('paid_by', lang("paid_by"), 'required');
        $this->form_validation->set_rules('userfile', lang("attachment"), 'xss_clean');
        if ($this->form_validation->run() == true) {
            if ($this->input->post('paid_by') == 'deposit') {
			$sale = $this->sales_model->getInvoiceByID($this->input->post('sale_id'));
			$customer_id = $sale->customer_id;
			$amount = $this->input->post('amount-paid')-$payment->amount;
			if ( ! $this->site->check_customer_deposit($customer_id, $amount)) {
				$this->session->set_flashdata('error', lang("amount_greater_than_deposit"));
				redirect($_SERVER["HTTP_REFERER"]);
			}
            } else {
				$customer_id = null;
            }
            if ($this->Owner || $this->Admin) {
				$date = $this->sma->fld(trim($this->input->post('date')));
            } else {
				$date = $payment->date;
            }
            $payment = array(
				'date' => $date,
				'sale_id' => $this->input->post('sale_id'),
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
				'created_by' => $this->session->userdata('user_id'),
                'warehouse_id' => $sale->warehouse_id,
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

        if ($this->form_validation->run() == true && $this->sales_model->updatePayment($id, $payment, $customer_id)) {
            $this->session->set_flashdata('message', lang("payment_updated"));
            redirect("sales");
        } else {

            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['payment'] = $payment;

            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'sales/edit_payment', $this->data);
        }
    }
	 public function add_phieuthukh($id = null)
    {
        $this->sma->checkPermissions('payments', true);
        $this->load->helper('security');
       
		$customer_id=$this->input->post('customer_id');
		
        //$this->form_validation->set_rules('reference_no', lang("reference_no"), 'required');
        $this->form_validation->set_rules('amount-paid', lang("amount"), 'required');
        $this->form_validation->set_rules('paid_by', lang("paid_by"), 'required');
        $this->form_validation->set_rules('userfile', lang("attachment"), 'xss_clean');
        if ($this->form_validation->run() == true) {
            if ($this->input->post('paid_by') == 'deposit') {
				if ( ! $this->site->check_customer_deposit($customer_id, $this->input->post('amount-paid'))) {
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
					'reference_no' => $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('pay'),
					'amount' => $this->input->post('amount-paid'),
					'paid_by' => $this->input->post('paid_by'),
					'cheque_no' => $this->input->post('cheque_no'),
					'cc_no' => $this->input->post('paid_by') == 'gift_card' ? $this->input->post('gift_card_no') : $this->input->post('pcc_no'),
					'cc_holder' => $this->input->post('pcc_holder'),
					'cc_month' => $this->input->post('pcc_month'),
					'cc_year' => $this->input->post('pcc_year'),
					'cc_type' => $this->input->post('pcc_type'),
					'note' => $this->input->post('note'),
					'created_by' => $this->session->userdata('user_id'),
                    'warehouse_id' => $warehouse_id,
					'type' => 'received',
					'id_ncc_id_kh' =>$customer_id,
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

        } elseif ($this->input->post('add_phieuthukh')) {
			
            $this->session->set_flashdata('error', validation_errors());
           redirect($_SERVER["HTTP_REFERER"]);
        }
        if ($this->form_validation->run() == true && $this->sales_model->addPaymentLhson($payment, $customer_id)) {
            $this->session->set_flashdata('message', lang("payment_added"));
		
			redirect($_SERVER["HTTP_REFERER"]);
        } else {

            $this->data['customers']=$this->companies_model->getAllCustomerCompanies();
			$this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));      
            //get customer info by purchase_id
            $ncc=$this->site->getCompanyByID($customer_id);
            $this->data['ncc'] = $ncc;

            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'sales/add_payment_lhson', $this->data);
        }
		
    }
	public function edit_payment_lhson($id = null)
    {
        $this->sma->checkPermissions('edit', true);
        $this->load->helper('security');
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $payment = $this->sales_model->getPaymentByID($id);
		
        if ($payment->paid_by == 'ppp' || $payment->paid_by == 'stripe') {
            $this->session->set_flashdata('error', lang('x_edit_payment'));
            $this->sma->md();
        }
        $this->form_validation->set_rules('reference_no', lang("reference_no"), 'required');
        $this->form_validation->set_rules('amount-paid', lang("amount"), 'required');
        $this->form_validation->set_rules('paid_by', lang("paid_by"), 'required');
        $this->form_validation->set_rules('userfile', lang("attachment"), 'xss_clean');
        if ($this->form_validation->run() == true) {
            if ($this->input->post('paid_by') == 'deposit') {
			$customer_id = $this->input->post('customer_id');
			$amount = $this->input->post('amount-paid')-$payment->amount;
			if ( ! $this->site->check_customer_deposit($customer_id, $amount)) {
				$this->session->set_flashdata('error', lang("amount_greater_than_deposit"));
				redirect($_SERVER["HTTP_REFERER"]);
			}
            } 
            if ($this->Owner || $this->Admin) {
				$date = $this->sma->fld(trim($this->input->post('date')));
            } else {
				$date = $payment->date;
            }
            $warehouse_id=$this->session->userdata('warehouse_id');
            if ($warehouse_id==null) {
                $warehouse_id=$this->Settings->default_warehouse;
            }
            $payment = array(
				'date' => $date,
				'sale_id' => $this->input->post('sale_id'),
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
                'warehouse_id' => $warehouse_id,			
				'id_ncc_id_kh' =>$this->input->post('customer_id'),
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

          //  $this->sma->print_arrays($payment);
			

        } elseif ($this->input->post('edit_payment_lhson')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }

        if ($this->form_validation->run() == true && $this->sales_model->updatePaymentLhson($id, $payment, $customer_id)) {
            $this->session->set_flashdata('message', lang("payment_updated"));
			
            redirect("sales");
        } else {

            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['payment'] = $payment;		

            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'sales/edit_payment_lhson', $this->data);
        }
    }
    public function delete_payment($id = null)
    {
        $this->sma->checkPermissions('delete');

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        if ($this->sales_model->deletePayment($id)) {
            //echo lang("payment_deleted");
            $this->session->set_flashdata('message', lang("payment_deleted"));
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }
	public function delete_payment_ajax($id = null)
    {
        $this->sma->checkPermissions('delete');

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        if ($this->sales_model->deletePayment($id)) {
           $this->sma->send_json(array('error' => 0, 'msg' => 'Xóa thanh toán thành công'));
        }
    }
	
	public function delete_payment_lhson($id = null)
    {
        $this->sma->checkPermissions('delete');

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        if ($this->sales_model->deletePaymentLhson($id)) {
            //echo lang("payment_deleted");
            $this->session->set_flashdata('message', lang("payment_deleted"));
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }
	public function delete_payment_lhson_ajax($id = null)
    {
        $this->sma->checkPermissions('delete');

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        if ($this->sales_model->deletePaymentLhson($id)) {
            $this->sma->send_json(array('error' => 0, 'msg' => 'Xóa thanh toán thành công'));
        }
    }

    /* --------------------------------------------------------------------------------------------- */

    public function suggestions()
    {
        $term = $this->input->get('term', true);
        $warehouse_id = $this->input->get('warehouse_id', true);
        $customer_id = $this->input->get('customer_id', true);

        if (strlen($term) < 1 || !$term) {
            die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . site_url('welcome') . "'; }, 10);</script>");
        }
        $check_bienthe=explode("_",$term);
        $bienthe_id_scan=0;
        if (count($check_bienthe)>1)
        {
            //bien the = 0
            $bienthe_id_scan=$check_bienthe[1];
        }

        $analyzed = $this->sma->analyze_term($term);
        $sr = $analyzed['term'];
        $option_id = $analyzed['option_id'];

        $warehouse = $this->site->getWarehouseByID($warehouse_id);
        $customer = $this->site->getCompanyByID($customer_id);
        
        $customer_group = $this->site->getCustomerGroupByID($customer->customer_group_id);
        $rows = $this->sales_model->getProductNames($sr, $warehouse_id);
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
                $row->serial = '';
                $options = $this->sales_model->getProductOptions($row->id, $warehouse_id);
                if ($options) {
                    $opt = $option_id && $r == 0 ? $this->sales_model->getProductOptionByID($option_id) : $options[0];
                    if (!$option_id || $r > 0) {
                        $option_id = $opt->id;
                    }

                } else {
                    $opt = json_decode('{}');
                    $opt->price = 0;
                    $option_id = FALSE;
                }

                $row->option = $option_id;
                $pis = $this->site->getPurchasedItems($row->id, $warehouse_id, $row->option);
                if ($pis) {
                    foreach ($pis as $pi) {
                        $row->quantity += $pi->quantity_balance;
                    }
                }


                $bienthe_id_scan_barcode=0;
                if ($options) {
                    $option_quantity = 0;
                    foreach ($options as $option) {
                        if ($bienthe_id_scan==$option->id)
                        {
                             $bienthe_id_scan_barcode=$option->id;                                
                        }
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
                if ($bienthe_id_scan_barcode>0) {
                    
                    $option_id=$bienthe_id_scan_barcode;

                    $row->option = $option_id;
                }

                $stock=$this->site->tonkhohientai($row->id, $warehouse_id);
                
                $row->quantity=$stock;

                
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
                $row->price = $row->price + (($row->price * $customer_group->percent) / 100);
                $row->real_unit_price = $row->price;
                $row->base_quantity = 1;
                $row->base_unit = $row->unit;
                $row->base_unit_price = $row->price;
                $row->unit = $row->sale_unit ? $row->sale_unit : $row->unit;
                $row->comment = '';
                $combo_items = false;
                if ($row->type == 'combo') {
                    $combo_items = $this->sales_model->getProductComboItems($row->id, $warehouse_id);
                }
                $units = $this->site->getUnitsByBUID($row->base_unit);
                $units_nhap = $this->site->getUnitsByBUID($row->purchase_unit);
                $tax_rate = $this->site->getTaxRateByID($row->tax_rate);

                $pr[] = array('id' => ($c + $r), 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 'category' => $row->category_id, 
                    'row' => $row, 'combo_items' => $combo_items, 'tax_rate' => $tax_rate, 'units' => $units,'units_nhap' => $units_nhap, 'options' => $options); 
                $r++;
            }
            $this->sma->send_json($pr);
        } else {
            $this->sma->send_json(array(array('id' => 0, 'label' => lang('no_match_found'), 'value' => $term)));
        }
    }

    /* ------------------------------------ Gift Cards ---------------------------------- */
     public function gift_cards()
    {
        $this->sma->checkPermissions();

        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');

        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('sales'), 'page' => lang('sales')), array('link' => '#', 'page' => lang('gift_cards')));
        $meta = array('page_title' => lang('gift_cards'), 'bc' => $bc);
        $this->page_construct('sales/gift_cards', $meta, $this->data);
    }
    
    public function gift_cards_qua()
    {
        $this->sma->checkPermissions();

        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');

        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('sales'), 'page' => lang('sales')), array('link' => '#', 'page' => lang('Đổi quà tặng')));
        $meta = array('page_title' => lang('Đổi quà tặng'), 'bc' => $bc);
        $this->page_construct('sales/gift_cards_qua', $meta, $this->data);
    }
    public function getGiftCards($type='card')
    {

        $this->load->library('datatables');
        $this->datatables
            ->select($this->db->dbprefix('gift_cards') . ".id as id, card_no,tenquatang,ca_points, value, balance, CONCAT(" . $this->db->dbprefix('users') . ".first_name, ' ', " . $this->db->dbprefix('users') . ".last_name) as created_by, customer, expiry", false)
            ->join('users', 'users.id=gift_cards.created_by', 'left')
            ->from("gift_cards")->where('type',$type);
            if ($type=='card') {
                $this->datatables->add_column("Actions", "<div class=\"text-center\"><a href='" . site_url('sales/view_gift_card/$1') . "' class='tip' title='" . lang("view_gift_card") . "' data-toggle='modal' data-target='#myModal'><i class=\"fa fa-eye\"></i></a> <a href='" . site_url('sales/topup_gift_card/$1') . "' class='tip' title='" . lang("topup_gift_card") . "' data-toggle='modal' data-target='#myModal'><i class=\"fa fa-dollar\"></i></a> <a href='" . site_url('sales/edit_gift_card/$1') . "' class='tip' title='" . lang("edit_gift_card") . "' data-toggle='modal' data-target='#myModal'><i class=\"fa fa-edit\"></i></a> <a href='#' class='tip po' title='<b>" . lang("delete_gift_card") . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('sales/delete_gift_card/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></div>", "id");
            }else{
                $this->datatables->add_column("Actions", "<div class=\"text-center\"><a href='#' class='tip po' title='<b>" . lang("Xóa quà tặng") . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('sales/delete_gift_card/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></div>", "id");
            }
            
        //->unset_column('id');
        
        echo $this->datatables->generate();
    }

    public function view_gift_card($id = null)
    {
        $this->data['page_title'] = lang('gift_card');
        $gift_card = $this->site->getGiftCardByID($id);
        $this->data['gift_card'] = $this->site->getGiftCardByID($id);
        $this->data['customer'] = $this->site->getCompanyByID($gift_card->customer_id);
        $this->data['topups'] = $this->sales_model->getAllGCTopups($id);
        $this->load->view($this->theme . 'sales/view_gift_card', $this->data);
    }

    public function topup_gift_card($card_id)
    {
        $this->sma->checkPermissions('add_gift_card', true);
        $card = $this->site->getGiftCardByID($card_id);
        $this->form_validation->set_rules('amount', lang("amount"), 'trim|integer|required');

        if ($this->form_validation->run() == true) {
            $data = array('card_id' => $card_id,
				'amount' => $this->input->post('amount'),
				'date' => date('Y-m-d H:i:s'),
				'created_by' => $this->session->userdata('user_id'),
							);
				$card_data['balance'] = ($this->input->post('amount')+$card->balance);
				// $card_data['value'] = ($this->input->post('amount')+$card->value);
				if ($this->input->post('expiry')) {
					$card_data['expiry'] = $this->sma->fld(trim($this->input->post('expiry')));
				}
			} elseif ($this->input->post('topup')) {
				$this->session->set_flashdata('error', validation_errors());
				redirect("sales/gift_cards");
			}

        if ($this->form_validation->run() == true && $this->sales_model->topupGiftCard($data, $card_data)) {
            $this->session->set_flashdata('message', lang("topup_added"));
            redirect("sales/gift_cards");
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->data['card'] = $card;
            $this->data['page_title'] = lang("topup_gift_card");
            $this->load->view($this->theme . 'sales/topup_gift_card', $this->data);
        }
    }

    public function validate_gift_card($no)
    {
        //$this->sma->checkPermissions();
        if ($gc = $this->site->getGiftCardByNO($no)) {
            if ($gc->expiry) {
				if ($gc->expiry >= date('Y-m-d')) {
					$this->sma->send_json($gc);
				} else {
					$this->sma->send_json(false);
				}
			} else {
				$this->sma->send_json($gc);
			}
		} else {
			$this->sma->send_json(false);
		}
	}

    public function add_gift_card()
    {
        $this->sma->checkPermissions(false, true);

        $this->form_validation->set_rules('card_no', lang("card_no"), 'trim|is_unique[gift_cards.card_no]|required');
        $this->form_validation->set_rules('value', lang("value"), 'required');

        if ($this->form_validation->run() == true) {
            $customer_details = $this->input->post('customer') ? $this->site->getCompanyByID($this->input->post('customer')) : null;
            $customer = $customer_details ? $customer_details->name : $customer_details->company;
            

            $data = array('card_no' => $this->input->post('card_no'),
                'value' => $this->input->post('value'),
                'customer_id' => $this->input->post('customer') ? $this->input->post('customer') : null,
                'customer' => $customer,
                'balance' => $this->input->post('value'),
                'expiry' => $this->input->post('expiry') ? $this->sma->fsd($this->input->post('expiry')) : null,
                'created_by' => $this->session->userdata('user_id')
                ,'ca_points'=>$this->input->post('ca_points')
                            );
            $sa_data = array();
            $ca_data = array();
            if ($this->input->post('staff_points')) {
                $sa_points = $this->input->post('sa_points');
                $user = $this->site->getUser($this->input->post('user'));
                if ($user->award_points < $sa_points) {
                    $this->session->set_flashdata('error', lang("award_points_wrong"));
                    redirect("sales/gift_cards");
                }
                $sa_data = array('user' => $user->id, 'points' => ($user->award_points - $sa_points));
            } elseif ($customer_details && $this->input->post('use_points')) {
                $ca_points = $this->input->post('ca_points');
                if ($customer_details->award_points < $ca_points) {
                    $this->session->set_flashdata('error', lang("award_points_wrong"));
                    redirect("sales/gift_cards");
                }
                $ca_data = array('customer' => $this->input->post('customer'), 'points' => ($customer_details->award_points - $ca_points));
            }
            // $this->sma->print_arrays($data, $ca_data, $sa_data);
        } elseif ($this->input->post('add_gift_card')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect("sales/gift_cards");
        }

        if ($this->form_validation->run() == true && $this->sales_model->addGiftCard($data, $ca_data, $sa_data)) {
            $this->session->set_flashdata('message', lang("gift_card_added"));
            redirect("sales/gift_cards");
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->data['users'] = $this->sales_model->getStaff();
            $this->data['page_title'] = lang("new_gift_card");
            $this->load->view($this->theme . 'sales/add_gift_card', $this->data);
        }
    }
    public function add_gift_card_qua()
    {
        $this->sma->checkPermissions(false, true);

        $this->form_validation->set_rules('tenquatang', lang("Tên quà tặng"), 'trim|required');

        if ($this->form_validation->run() == true) {
            $customer_details = $this->input->post('customer') ? $this->site->getCompanyByID($this->input->post('customer')) : null;
            $customer = $customer_details ? $customer_details->name : $customer_details->company;

           

            $data = array('card_no' => time().rand(0,100),
                'value' => $this->input->post('value'),
                'customer_id' => $this->input->post('customer') ? $this->input->post('customer') : null,
                'customer' => $customer,
                'balance' => $this->input->post('value'),
                'expiry' => $this->input->post('expiry') ? $this->sma->fsd($this->input->post('expiry')) : null,
                'created_by' => $this->session->userdata('user_id'),'tenquatang' => $this->input->post('tenquatang'),'type' =>'qua','ca_points'=>$this->input->post('ca_points')
                            );
                $sa_data = array();
                $ca_data = array();
                if ($customer_details && $this->input->post('use_points')) {
                    $ca_points = $this->input->post('ca_points');
                    if ($customer_details->award_points < $ca_points) {
                        $this->session->set_flashdata('error', lang("award_points_wrong"));
                        redirect("sales/gift_cards_qua");
                    }
                    $ca_data = array('customer' => $this->input->post('customer'), 'points' => ($customer_details->award_points - $ca_points));
                }
            // $this->sma->print_arrays($data, $ca_data, $sa_data);
        } elseif ($this->input->post('add_gift_card')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect("sales/gift_cards_qua");
        }

        if ($this->form_validation->run() == true && $this->sales_model->addGiftCard($data, $ca_data, $sa_data)) {
            $this->session->set_flashdata('message', lang("Thêm quà tặng thành công"));
            redirect("sales/gift_cards_qua");
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->data['users'] = $this->sales_model->getStaff();
            $this->data['page_title'] = lang("Thêm quà tặng");
            $this->load->view($this->theme . 'sales/add_gift_card_qua', $this->data);
        }
    }

    public function edit_gift_card($id = null)
    {
        $this->sma->checkPermissions(false, true);

        $this->form_validation->set_rules('card_no', lang("card_no"), 'trim|required');
        $gc_details = $this->site->getGiftCardByID($id);
        if ($this->input->post('card_no') != $gc_details->card_no) {
            $this->form_validation->set_rules('card_no', lang("card_no"), 'is_unique[gift_cards.card_no]');
        }
        $this->form_validation->set_rules('value', lang("value"), 'required');
        //$this->form_validation->set_rules('customer', lang("customer"), 'xss_clean');

        if ($this->form_validation->run() == true) {
            $gift_card = $this->site->getGiftCardByID($id);
            $customer_details = $this->input->post('customer') ? $this->site->getCompanyByID($this->input->post('customer')) : null;
            $customer = $customer_details ? $customer_details->company : null;
            $data = array('card_no' => $this->input->post('card_no'),
				'value' => $this->input->post('value'),
				'customer_id' => $this->input->post('customer') ? $this->input->post('customer') : null,
				'customer' => $customer,
				'balance' => ($this->input->post('value') - $gift_card->value) + $gift_card->balance,
				'expiry' => $this->input->post('expiry') ? $this->sma->fsd($this->input->post('expiry')) : null,
            );
        } elseif ($this->input->post('edit_gift_card')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect("sales/gift_cards");
        }

        if ($this->form_validation->run() == true && $this->sales_model->updateGiftCard($id, $data)) {
            $this->session->set_flashdata('message', lang("gift_card_updated"));
            redirect("sales/gift_cards");
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['gift_card'] = $this->site->getGiftCardByID($id);
            $this->data['id'] = $id;
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'sales/edit_gift_card', $this->data);
        }
    }

    public function sell_gift_card()
    {
        $this->sma->checkPermissions('gift_cards', true);
        $error = null;
        $gcData = $this->input->get('gcdata');
        if (empty($gcData[0])) {
            $error = lang("value") . " " . lang("is_required");
        }
        if (empty($gcData[1])) {
            $error = lang("card_no") . " " . lang("is_required");
        }

        $customer_details = (!empty($gcData[2])) ? $this->site->getCompanyByID($gcData[2]) : null;
        $customer = $customer_details ? $customer_details->company : null;
        $data = array('card_no' => $gcData[0],
            'value' => $gcData[1],
            'customer_id' => (!empty($gcData[2])) ? $gcData[2] : null,
            'customer' => $customer,
            'balance' => $gcData[1],
            'expiry' => (!empty($gcData[3])) ? $this->sma->fsd($gcData[3]) : null,
            'created_by' => $this->session->userdata('user_id'),
        );

        if (!$error) {
            if ($this->sales_model->addGiftCard($data)) {
				$this->sma->send_json(array('result' => 'success', 'message' => lang("gift_card_added")));
            }
        } else {
            $this->sma->send_json(array('result' => 'failed', 'message' => $error));
        }

    }

    public function delete_gift_card($id = null)
    {
        $this->sma->checkPermissions();

        if ($this->sales_model->deleteGiftCard($id)) {
            $this->sma->send_json(array('error' => 0, 'msg' => lang("gift_card_deleted")));
        }
    }

    public function gift_card_actions()
    {
        if (!$this->Owner && !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER["HTTP_REFERER"]);
        }

        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');

        if ($this->form_validation->run() == true) {

            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {

                    $this->sma->checkPermissions('delete_gift_card');
                    foreach ($_POST['val'] as $id) {
                        $this->sales_model->deleteGiftCard($id);
                    }
                    $this->session->set_flashdata('message', lang("gift_cards_deleted"));
                    redirect($_SERVER["HTTP_REFERER"]);
                }

                if ($this->input->post('form_action') == 'export_excel' || $this->input->post('form_action') == 'export_pdf') {

                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('gift_cards'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('card_no'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('tên quà'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('value'));
                    $this->excel->getActiveSheet()->SetCellValue('D1', lang('customer'));

                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $sc = $this->site->getGiftCardByID($id);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $sc->card_no);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $sc->tenquatang);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $sc->value);
                        $this->excel->getActiveSheet()->SetCellValue('D' . $row, $sc->customer);
                        $row++;
                    }

                    $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'gift_cards_' . date('Y_m_d_H_i_s');
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
                $this->session->set_flashdata('error', lang("Chưa chọn quà / thẻ giảm giá"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }

    public function get_award_points($id = null)
    {
        $this->sma->checkPermissions('index');

        $row = $this->site->getUser($id);
        $this->sma->send_json(array('sa_points' => $row->award_points));
    }

    /* -------------------------------------------------------------------------------------- */

    public function sale_by_csv()
    {
        $this->sma->checkPermissions('csv');
        $this->load->helper('security');
        $this->form_validation->set_rules('userfile', lang("upload_file"), 'xss_clean');
        $this->form_validation->set_message('is_natural_no_zero', lang("no_zero_required"));
        $this->form_validation->set_rules('customer', lang("customer"), 'required');
        $this->form_validation->set_rules('biller', lang("biller"), 'required');
        $this->form_validation->set_rules('sale_status', lang("sale_status"), 'required');
        $this->form_validation->set_rules('payment_status', lang("payment_status"), 'required');

        if ($this->form_validation->run() == true) {

            $reference = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('so');
            if ($this->Owner || $this->Admin) {
				$date = $this->sma->fld(trim($this->input->post('date')));
            } else {
					$date = date('Y-m-d H:i:s');
            }
            $warehouse_id = $this->input->post('warehouse');
            $customer_id = $this->input->post('customer');
            $biller_id = $this->input->post('biller');
            $total_items = $this->input->post('total_items');
            $sale_status = $this->input->post('sale_status');
            $payment_status = $this->input->post('payment_status');
            $payment_term = $this->input->post('payment_term');
            $due_date = $payment_term ? date('Y-m-d', strtotime('+' . $payment_term . ' days')) : null;
            $shipping = $this->input->post('shipping') ? $this->input->post('shipping') : 0;
            $customer_details = $this->site->getCompanyByID($customer_id);
            $customer = $customer_details->company != '-'  ? $customer_details->company : $customer_details->name;
            $biller_details = $this->site->getCompanyByID($biller_id);
            $biller = $biller_details->company != '-' ? $biller_details->company : $biller_details->name;
            $note = $this->sma->clear_tags($this->input->post('note'));
            $staff_note = $this->sma->clear_tags($this->input->post('staff_note'));

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
						redirect("sales/sale_by_csv");
					}

					$csv = $this->upload->file_name;
					$data['attachment'] = $csv;

					$arrResult = array();
					$handle = fopen($this->digital_upload_path . $csv, "r");
					if ($handle) {
						while (($row = fgetcsv($handle, 1000, ",")) !== false) {
							$arrResult[] = $row;
						}
						fclose($handle);
					}
					$titles = array_shift($arrResult);

					$keys = array('code', 'net_unit_price', 'quantity', 'variant', 'item_tax_rate', 'discount', 'serial');
					$final = array();
					foreach ($arrResult as $key => $value) {
						$final[] = array_combine($keys, $value);
					}
					$rw = 2;
					foreach ($final as $csv_pr) {

						if (isset($csv_pr['code']) && isset($csv_pr['net_unit_price']) && isset($csv_pr['quantity'])) {

							if ($product_details = $this->sales_model->getProductByCode($csv_pr['code'])) {

								if ($csv_pr['variant']) {
					$item_option = $this->sales_model->getProductVariantByName($csv_pr['variant'], $product_details->id);
					if (!$item_option) {
						$this->session->set_flashdata('error', lang("pr_not_found") . " ( " . $product_details->name . " - " . $csv_pr['variant'] . " ). " . lang("line_no") . " " . $rw);
						redirect($_SERVER["HTTP_REFERER"]);
					}
								} else {
					$item_option = json_decode('{}');
					$item_option->id = null;
								}

								$item_id = $product_details->id;
								$item_type = $product_details->type;
								$item_code = $product_details->code;
								$item_name = $product_details->name;
								$item_net_price = $this->sma->formatDecimal($csv_pr['net_unit_price']);
								$item_quantity = $csv_pr['quantity'];
								$item_tax_rate = $csv_pr['item_tax_rate'];
								$item_discount = $csv_pr['discount'];
								$item_serial = $csv_pr['serial'];

								if (isset($item_code) && isset($item_net_price) && isset($item_quantity)) {
					$product_details = $this->sales_model->getProductByCode($item_code);

					if (isset($item_discount)) {
						$discount = $item_discount;
						$dpos = strpos($discount, $percentage);
						if ($dpos !== false) {
							$pds = explode("%", $discount);
							$pr_discount = $this->sma->formatDecimal(((($this->sma->formatDecimal($item_net_price)) * (Float) ($pds[0])) / 100), 4);
						} else {
							$pr_discount = $this->sma->formatDecimal($discount);
						}
					} else {
						$pr_discount = 0;
					}
					$item_net_price = $this->sma->formatDecimal(($item_net_price - $pr_discount), 4);
					$pr_item_discount = $this->sma->formatDecimal(($pr_discount * $item_quantity), 4);
					$product_discount += $pr_item_discount;

					if (isset($item_tax_rate) && $item_tax_rate != 0) {

						if ($tax_details = $this->sales_model->getTaxRateByName($item_tax_rate)) {
							$pr_tax = $tax_details->id;
							if ($tax_details->type == 1) {

								$item_tax = $this->sma->formatDecimal((($item_net_price) * $tax_details->rate) / 100, 4);
								$tax = $tax_details->rate . "%";

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

							$item_tax = $this->sma->formatDecimal((($item_net_price) * $tax_details->rate) / 100, 4);
							$tax = $tax_details->rate . "%";

						} elseif ($tax_details->type == 2) {

							$item_tax = $this->sma->formatDecimal($tax_details->rate);
							$tax = $tax_details->rate;

						}
						$pr_item_tax = $this->sma->formatDecimal(($item_tax * $item_quantity), 4);

					} else {
						$item_tax = 0;
						$pr_tax = 0;
						$pr_item_tax = 0;
						$tax = "";
					}
					$product_tax += $pr_item_tax;
					$subtotal = $this->sma->formatDecimal((($item_net_price * $item_quantity) + $pr_item_tax), 4);
					$unit = $this->site->getUnitByID($product_details->unit);

					$products[] = array(
						'product_id' => $product_details->id,
						'product_code' => $item_code,
						'product_name' => $item_name,
						'product_type' => $item_type,
						'option_id' => $item_option->id,
						'net_unit_price' => $item_net_price,
						'quantity' => $item_quantity,
						'product_unit_id' => $product_details->unit,
						'product_unit_code' => $unit->code,
						'unit_quantity' => $item_quantity,
						'warehouse_id' => $warehouse_id,
						'item_tax' => $pr_item_tax,
						'tax_rate_id' => $pr_tax,
						'tax' => $tax,
						'discount' => $item_discount,
						'item_discount' => $pr_item_discount,
						'subtotal' => $subtotal,
						'serial_no' => $item_serial,
						'unit_price' => $this->sma->formatDecimal(($item_net_price + $item_tax), 4),
						'real_unit_price' => $this->sma->formatDecimal(($item_net_price + $item_tax + $pr_discount), 4),
					);

					$total += $this->sma->formatDecimal(($item_net_price * $item_quantity), 4);
								}

							} else {
								$this->session->set_flashdata('error', lang("pr_not_found") . " ( " . $csv_pr['code'] . " ). " . lang("line_no") . " " . $rw);
								redirect($_SERVER["HTTP_REFERER"]);
							}
							$rw++;
						}

					}
            }

            if ($this->input->post('order_discount')) {
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
							$total_discount = $this->sma->formatDecimal(($order_discount + $product_discount), 4);

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
            $grand_total = $this->sma->formatDecimal(($total + $total_tax + $this->sma->formatDecimal($shipping) - $order_discount), 4);
            $data = array('date' => $date,
				'reference_no' => $reference,
				'customer_id' => $customer_id,
				'customer' => $customer,
				'biller_id' => $biller_id,
				'biller' => $biller,
				'warehouse_id' => $warehouse_id,
				'note' => $note,
				'staff_note' => $staff_note,
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
				'total_items' => $total_items,
				'sale_status' => $sale_status,
				'payment_status' => $payment_status,
				'payment_term' => $payment_term,
				'due_date' => $due_date,
				'paid' => 0,
				'created_by' => $this->session->userdata('user_id'),
            );

            if ($payment_status == 'paid') {

				$payment = array(
					'date' => $date,
					'reference_no' => $this->site->getReference('pay'),
					'amount' => $grand_total,
					'paid_by' => 'cash',
					'cheque_no' => '',
					'cc_no' => '',
					'cc_holder' => '',
					'cc_month' => '',
					'cc_year' => '',
					'cc_type' => '',
					'created_by' => $this->session->userdata('user_id'),
                    'warehouse_id' => $warehouse_id,
					'note' => lang('auto_added_for_sale_by_csv') . ' (' . lang('sale_reference_no') . ' ' . $reference . ')',
					'type' => 'received',
				);

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

            //$this->sma->print_arrays($data, $products, $payment);
        }

        if ($this->form_validation->run() == true && $this->sales_model->addSale($data, $products, $payment)) {
            $this->session->set_userdata('remove_slls', 1);
            $this->session->set_flashdata('message', lang("sale_added"));
            redirect("sales");
        } else {

            $data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['tax_rates'] = $this->site->getAllTaxRates();
            $this->data['billers'] = $this->site->getAllCompanies('biller');
            $this->data['slnumber'] = $this->site->getReference('so');

            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('sales'), 'page' => lang('sales')), array('link' => '#', 'page' => lang('add_sale_by_csv')));
            $meta = array('page_title' => lang('add_sale_by_csv'), 'bc' => $bc);
            $this->page_construct('sales/sale_by_csv', $meta, $this->data);

        }
    }

    public function update_status($id)
    {

        $this->form_validation->set_rules('status', lang("sale_status"), 'required');

        if ($this->form_validation->run() == true) {
            $status = $this->input->post('status');
            $note = $this->sma->clear_tags($this->input->post('note'));
        } elseif ($this->input->post('update')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect(isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : 'sales');
        }

        if ($this->form_validation->run() == true && $rs=$this->sales_model->updateStatus($id, $status, $note)) {
            $this->SysnApiWoooUpdateStatusOrder($status,$id);
            
            $this->session->set_flashdata('message', lang('status_updated'));
            redirect(isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : 'sales');
        } else {

            $this->data['inv'] = $this->sales_model->getInvoiceByID($id);
            $this->data['returned'] = FALSE;
            if ($this->data['inv']->sale_status == 'returned' || $this->data['inv']->return_id) {
				$this->data['returned'] = TRUE;
            }
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme.'sales/update_status', $this->data);

        }
    } 
	public function printsalelhsonbk($id = null)    {      
		$this->sma->checkPermissions('index');       
		if ($this->input->get('id')) {        
			$id = $this->input->get('id');     
		}     
		$this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');    
		$inv = $this->sales_model->getInvoiceByID($id);      
		if (!$this->session->userdata('view_right')) {           
			$this->sma->view_rights($inv->created_by);       
		}        
		$this->data['barcode'] = "<img src='" . site_url('products/gen_barcode/' . $inv->reference_no) . "' alt='" . $inv->reference_no . "' class='pull-left' />";        
		$this->data['customer'] =$customer= $this->site->getCompanyByID($inv->customer_id);        
		$this->data['payments'] = $this->sales_model->getPaymentsForSale($id);
		$this->data['biller'] = $biller=$this->site->getCompanyByID($inv->biller_id);       
		$this->data['created_by'] = $created_by=$this->site->getUser($inv->created_by); 
		$this->data['updated_by'] = $inv->updated_by ? $this->site->getUser($inv->updated_by) : null;        
		$this->data['warehouse'] = $warehouse=$this->site->getWarehouseByID($inv->warehouse_id); 
		$this->data['inv'] = $inv;      
		$this->data['rows'] =$rows= $this->sales_model->getAllInvoiceItems($id);  
		$this->data['return_sale'] = $return_sale=$inv->return_id ? $this->sales_model->getInvoiceByID($inv->return_id) : NULL;  
		$this->data['return_rows'] = $return_rows=$inv->return_id ? $this->sales_model->getAllInvoiceItems($inv->return_id) : NULL;
		$tong_thue=0;        
		
		if ($this->Settings->tax2 && $inv->order_tax != 0) {        
			$tong_thue= $this->sma->formatMoney($return_sale ? ($inv->order_tax+$return_sale->order_tax) : $inv->order_tax);    
		}        
		$_giamgia=0;        
		if ($inv->order_discount != 0) {    
			$_giamgia=$this->sma->formatMoney($return_sale ? ($inv->order_discount+$return_sale->order_discount) : $inv->order_discount);   
		}
		$tong_dathanhtoan=$this->sma->formatMoney($return_sale ? ($inv->paid+$return_sale->paid) : $inv->paid);    
		
		$tong_dathanhtoan_num=$return_sale ? ($inv->grand_total+$return_sale->grand_total) : $inv->grand_total;
		
		$_chuathanhtoan=$this->sma->formatMoney(($return_sale ? ($inv->grand_total+$return_sale->grand_total) : $inv->grand_total) - ($return_sale ? ($inv->paid+$return_sale->paid) : $inv->paid));  
		$_chuathanhtoan_num=($return_sale ? ($inv->grand_total+$return_sale->grand_total) : $inv->grand_total) - ($return_sale ? ($inv->paid+$return_sale->paid) : $inv->paid);   
		
		$_tongthanhtoan=$this->sma->formatMoney($return_sale ? ($inv->grand_total+$return_sale->grand_total) : $inv->grand_total);
		$_tongcong=0;   
		$_tongcong_bang_chu=0;
		if ($inv->grand_total != $inv->total) {        
			$_tongcong=$this->sma->formatMoney($return_sale ? (($inv->total + $inv->product_tax)+($return_sale->total + $return_sale->product_tax)) : ($inv->total + $inv->product_tax)); 
			$_tongcong_bang_chu=$return_sale ? (($inv->total + $inv->product_tax)+($return_sale->total + $return_sale->product_tax)) : ($inv->total + $inv->product_tax); 		
		}else{
			$_tongcong=$this->sma->formatMoney($inv->grand_total);   
			$_tongcong_bang_chu=$inv->grand_total;
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
		
		$_tongthuhoi=0;        
		if ($return_sale) {        
			$_tongthuhoi=$this->sma->formatMoney($return_sale->grand_total);    
		}        
		$_phiphat=0;        
		if ($inv->surcharge != 0) {    
			$_phiphat=$this->sma->formatMoney($inv->surcharge);        
		}    
		$_diem_thuong=0;    
		$_tong_diem=0;        
		if ($customer->award_points != 0 && $this->Settings->each_spent > 0) {     
			$_diem_thuong=floor(($inv->grand_total/$this->Settings->each_spent)*$this->Settings->ca_point);    
			$_tong_diem=$_diem_thuong+floor($customer->award_points);        
		}    
		$_dathanhtoan= $this->reports_model->getSalesTotals($inv->customer_id);    
		$company_details = $this->companies_model->getCompanyByID($inv->customer_id);		
		$no_cu=(float)$company_details->nobandau;    
		if(isset($_dathanhtoan->total_amount) || isset($_dathanhtoan->paid)){    
			$no_cu+=$_dathanhtoan->total_amount -  $_dathanhtoan->paid;    
		}
        $no_cu=$no_cu-$inv->grand_total;
        
		$_tong_no_cu=$this->sma->formatMoney($no_cu);   
		$tong_no_all=$this->sma->formatMoney($no_cu+$_chuathanhtoan_num);
		$_tongno_bang_chu=($no_cu+$_chuathanhtoan_num);
		
		
		
		$left_end_tongno=substr($_tongno_bang_chu,strlen($_tongno_bang_chu)-5,strlen($_tongno_bang_chu));
		if($left_end_tongno=='.0000'){
			 $_tongno_bang_chu=str_replace($left_end_tongno,"",$_tongno_bang_chu);
		 }
		$_tongno_bang_chu_text=$this->site->convert_number_to_words($_tongno_bang_chu);
		
		
		$left_end_tongdathanhtoan=substr($tong_dathanhtoan_num,strlen($tong_dathanhtoan_num)-5,strlen($tong_dathanhtoan_num));
		if($left_end_tongdathanhtoan=='.0000'){
			 $tong_dathanhtoan_num=str_replace($left_end_tongdathanhtoan,"",$tong_dathanhtoan_num);
		 }
		$_tongdathanhtoan_bang_chu_text=$this->site->convert_number_to_words($tong_dathanhtoan_num);	
		
        $_tongdathanhtoan_bang_chu_text=strtolower($_tongdathanhtoan_bang_chu_text);
        $_1_text=substr($_tongdathanhtoan_bang_chu_text,0,1);
        $_2_text=substr($_tongdathanhtoan_bang_chu_text,1,strlen($_tongdathanhtoan_bang_chu_text));
        $_tongdathanhtoan_bang_chu_text=strtoupper($_1_text).$_2_text." đồng";
		
		$r = 1;       
		$tax_summary = array();    
		$_tablhd='<table border="1" style="width:100%;border-collapse:collapse;">        <tbody>        <tr>            <td style="text-align:center;width:5%;"><strong>STT</strong>            </td>            <td style="width:51%;padding-left:0.5%;"><strong>Tên hàng hóa</strong><br>            </td>            <td style="text-align:right;padding-right:0.5%;width:12%;"><strong>Đơn giá</strong><br>            </td>            <td style="text-align:center;width:15%;"><strong>SL</strong><br>            </td>            <td style="text-align:right;padding-right:0.5%;width:15.5%;"><strong>Thành tiền</strong><br>            </td>        </tr>';
		
        $_tablhd_pos='<table border="1" style="width:100%;border-collapse:collapse;font-size:12px;">        <tbody>        <tr>            <td style="text-align:center;width:5%;"><strong>STT</strong>            </td>            <td style="width:63.5%;padding-left:0.5%;"><strong>Tên hàng hóa</strong><br>            </td> <td style="text-align:center;width:15%;"><strong>SL</strong><br>            </td>            <td style="text-align:right;padding-right:0.5%;width:15.5%;"><strong>Thành tiền</strong><br>            </td>        </tr>';

		if ($this->Settings->item_print==1){
			$_tablhd='<table border="1" style="width:100%;border-collapse:collapse;">        <tbody>        <tr>            <td style="text-align:center;width:5%;"><strong>STT</strong>            </td>            <td style="width:44%;padding-left:0.5%;text-align:center"><strong>Tên hàng hóa</strong><br>            </td>            <td style="text-align:center;width:20%;" colspan="2"><strong>Số Lượng</strong><br>            </td>  <td style="text-align:center;width:15%;"><strong style="text-align:center;">Kho Xuất</strong><br>            </td>        </tr><tr><td></td><td></td><td style="text-align:center;">Yêu cầu</td><td style="text-align:center;">Thực tế</td><td></td></tr>';
		}
		
		foreach ($rows as $row):        
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
			$_prod_detail=$row->product_name;
            if ($row->data_id_khuyenmai>0) {
                //get event name 
                $obj_km=$this->site->getKhuyenmaiById($row->data_id_khuyenmai);
                if ($obj_km!=false) {
                    $_prod_detail.=" <i>(".$obj_km->tenevent.")</i>";    
                }else{
                    $_prod_detail.=" <i>(KHUYẾN MÃI)</i>";
                }
                
            } 

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
			
			if ($this->Settings->item_print==0){
				$_tablhd.='<tr><td style="text-align:center;vertical-align:middle;">'.$r.'</td><td style="vertical-align:middle;">'.$_prod_detail.'</td><td style="text-align:right; padding-right:10px;">'.$_strgia.'</td><td style="text-align:center; vertical-align:middle;">'.$this->sma->formatQuantity($row->unit_quantity).' '.$row->product_unit_code.'</td><td style="text-align:right; width:120px; padding-right:10px;">'.$this->sma->formatMoney($row->subtotal).'</td>            </tr>';  
			}else if ($this->Settings->item_print==1){
				$_tablhd.='<tr><td style="text-align:center;vertical-align:middle;">'.$r.'</td><td style="vertical-align:middle;">'.$_prod_detail.'</td><td style="text-align:center; vertical-align:middle;">'.$this->sma->formatQuantity($row->unit_quantity).' '.$row->product_unit_code.'</td><td></td><td></td></tr>';  
			}else{
				$_tablhd.='<tr><td style="text-align:center;vertical-align:middle;">'.$r.'</td><td style="vertical-align:middle;">'.$_prod_detail.'</td><td style="text-align:right; padding-right:10px;">'.$_strgia.'</td><td style="text-align:center; vertical-align:middle;">'.$this->sma->formatQuantity($row->unit_quantity).' '.$row->product_unit_code.'</td><td style="text-align:right; width:120px; padding-right:10px;">'.$this->sma->formatMoney($row->subtotal).'</td>            </tr>';  
			}

            $_tablhd_pos.='<tr><td style="text-align:center;vertical-align:middle;">'.$r.'</td><td style="vertical-align:middle;">'.$_prod_detail.'</td><td style="text-align:center; vertical-align:middle;">'.$this->sma->formatQuantity($row->unit_quantity).'</td><td style="text-align:right; width:120px; padding-right:10px;">'.$this->sma->formatMoney($row->subtotal).'</td>            </tr>';  

			$r++;    
		endforeach;
		
		if ($return_rows) {        
			$_tablhd.='<tr class="warning"><td colspan="100%" class="no-border"><strong>'.lang('returned_items').'</strong></td></tr>';
			foreach ($return_rows as $row):        
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
			$_rt_prod=$row->product_name . ($row->variant ? ' (' . $row->variant . ')' : '');
			$_rt_prod.=$row->details ? '<br>' .html_entity_decode($row->details): '';       
			$_rt_prod.=$row->baohanh ? '<br>Bảo hành:' . html_entity_decode($row->baohanh) : '';  
			$_rt_prod.=$row->serial_no ? '<br>Serial/Imei:' . html_entity_decode($row->serial_no) : '';  				
			$_rt_strgia=$this->sma->formatMoney($row->unit_price);        
			//if ($Settings->product_discount && $inv->product_discount != 0) 
			{    
				$_rt_strgia.=($row->discount != 0 ? "<br/> Giảm: (".$this->sma->formatMoney($row->item_discount).")":'');        
			}            
			
			if ($this->Settings->item_print==0){
				$_tablhd.='<tr class="warning">    <td style="text-align:center; vertical-align:middle;">'.$r.'</td>    <td style="vertical-align:middle;">'.$_rt_prod.'</td>    <td style="text-align:right; padding-right:10px;">'.$_rt_strgia.'</td>        <td style="text-align:center; vertical-align:middle;">'.$this->sma->formatQuantity($row->quantity).' '.$row->product_unit_code.'</td><td style="text-align:right;padding-right:10px;">'.$this->sma->formatMoney($row->subtotal).'</td></tr>';
			}else if ($this->Settings->item_print==1){
				$_tablhd.='<tr class="warning">    <td style="text-align:center; vertical-align:middle;">'.$r.'</td>    <td style="vertical-align:middle;">'.$_rt_prod.'</td>    <td style="text-align:center; vertical-align:middle;">'.$this->sma->formatQuantity($row->quantity).' '.$row->product_unit_code.'</td></tr>';  
			}else{
				$_tablhd.='<tr class="warning">    <td style="text-align:center; vertical-align:middle;">'.$r.'</td>    <td style="vertical-align:middle;">'.$_rt_prod.'</td>    <td style="text-align:right; padding-right:10px;">'.$_rt_strgia.'</td>        <td style="text-align:center; vertical-align:middle;">'.$this->sma->formatQuantity($row->quantity).' '.$row->product_unit_code.'</td><td style="text-align:right;padding-right:10px;">'.$this->sma->formatMoney($row->subtotal).'</td></tr>';
			}	
            $_tablhd_pos.='<tr class="warning">    <td style="text-align:center; vertical-align:middle;">'.$r.'</td>    <td style="vertical-align:middle;">'.$_rt_prod.'</td>   <td style="text-align:center; vertical-align:middle;">'.$this->sma->formatQuantity($row->quantity).'</td><td style="text-align:right;padding-right:10px;">'.$this->sma->formatMoney($row->subtotal).'</td></tr>';
			$r++;            
			endforeach;    
		}        
		$_tablhd.='</table>';      
        $_tablhd_pos.='</table>';        

		$_dc_cuahang=str_replace("<p>","",$warehouse->address);       
		$_dc_cuahang=str_replace("</p>","",$_dc_cuahang);
		
        $_ghichu="";
        if ($inv->note!=""&&$this->sma->decode_html($inv->note)!="") {
            $_ghichu="<p> Ghi chú: ".$this->sma->decode_html($inv->note)."</p>";
        }
        $_tongthue=$this->sma->formatMoney($inv->order_tax); 

		$this->data['doitac'] = $doitac=$this->doitac_model->getDoitacByID($inv->doitac);
		
		$parse_data = array('Ma_Don_Hang' => $inv->reference_no,'Khach_Hang' => $customer->name,'site_link' => base_url(),'site_name' => $this->Settings->site_name,'logo' => '<img src="' . base_url() . 'assets/uploads/logos/' . $biller->logo . '" alt="' . ($biller->company != '-' ? $biller->company : $biller->name) . '"/>','Ten_Kho' => $warehouse->name,'Dia_Chi_Kho' =>$_dc_cuahang,'SDT_Kho' =>$warehouse->phone,'Dia_chi_kh' => $customer->address,'Dien_thoai_kh' => $customer->phone,'Ngay_Xuat' => $this->sma->hrld($inv->date),'Tong_Thanh_Toan' =>$_tongthanhtoan,'Nhan_Vien_Thu_Ngan' =>$created_by->first_name . ' ' . $created_by->last_name,'Phu_Phi' => $this->sma->formatMoney($inv->shipping),'Ghi_Chu' =>$_ghichu,'Ghi_Chu_NV' =>$this->sma->decode_html($inv->staff_note),'No_cu' =>$_tong_no_cu,'Chua_Thanh_Toan' => $_chuathanhtoan,'Da_Thanh_Toan' => $tong_dathanhtoan,'Tong_Diem_Tich_Luy' =>$_tong_diem,'CongTy_KH' =>$customer->company,'MST_KH' =>$customer->vat_no,'Diem_hoa_don' =>$_diem_thuong,'Giam_Gia_Tren_Hoa_Don' =>$_giamgia,'Tong_Tien_Hang' =>$_tongcong,'Tong_Tien_Bang_Chu' =>$_tongcong_bang_chu_text,'Tong_thu_hoi' =>$_tongthuhoi,'Tong_No'=>$tong_no_all,'THUE'=>$_tongthue,'Bang_Hoa_Don' =>$_tablhd,'Bang_Hoa_Don_POS' =>$_tablhd_pos,'Ma_Doi_Tac' => $doitac->code,'Ten_Doi_Tac' => $doitac->name,'Dia_Chi_Doi_Tac' => $doitac->diachi,'Email_Doi_Tac' => $doitac->email,'Dien_Thoai_Doi_Tac' => $doitac->dienthoai,'No_Dau_Ky_Doi_Tac' => $doitac->nodauky,'Tong_No_Bang_Chu'=>$_tongno_bang_chu_text,'Tong_Thanh_Toan_Bang_Chu'=>$_tongdathanhtoan_bang_chu_text);    
				    
		
		if($this->Settings->item_print==1){
			$sale_temp = file_get_contents('./themes/default/views/print_templates/printpos_nocu.html');   
		}else if($this->Settings->item_print==2){
			$sale_temp = file_get_contents('./themes/default/views/print_templates/printpos_kono.html');   
		}else{
			if (file_exists('./themes/' . $this->theme . '/views/print_templates/printpos.html')) {          
				$sale_temp = file_get_contents('themes/' . $this->theme . '/views/print_templates/printpos.html');            
			} else {             
				$sale_temp = file_get_contents('./themes/default/views/print_templates/printpos.html');          
			}    
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
		$this->load->view($this->theme . 'sales/print', $this->data);  	
    }
	public function printgiaobk($id = null)
    {
        $this->sma->checkPermissions('deliveries');

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $deli = $this->sales_model->getDeliveryByID($id);
        if ((int)$deli->sale_id>0) {
            $sale = $this->sales_model->getInvoiceByID($deli->sale_id);
            
            $phaithu=$sale->grand_total-$sale->paid;

            if (!$sale) {
                $this->session->set_flashdata('error', lang('Không tìm thấy thông tin hóa đơn'));
                $this->sma->md();
            }

            $this->data['delivery'] = $deli;
            $this->data['biller'] = $biller=$this->site->getCompanyByID($sale->biller_id);
            $this->data['doitac'] = $doitac=$this->doitac_model->getDoitacByID($deli->delivered_by);
            $this->data['rows'] = $rows=$this->sales_model->getAllInvoiceItemsWithDetails($deli->sale_id);
            $this->data['user'] = $user=$this->site->getUser($deli->created_by);
            $this->data['warehouse'] = $warehouse=$this->site->getWarehouseByID($sale->warehouse_id); 
            $this->data['customer'] =$customer= $this->site->getCompanyByID($sale->customer_id); 
            
            
            
            $r = 1;
            $_tablhd='<table border="1" style="width:100%;border-collapse:collapse;">        <tbody>        <tr>            <td style="text-align:center;width:5%;"><strong>STT</strong>            </td>            <td style="width:51%;padding-left:0.5%;"><strong>Tên hàng hóa</strong><br>            </td>                        <td style="text-align:center;width:15%;"><strong>SL</strong><br>            </td>      </tr>';
            foreach ($rows as $row){
                $_prod_detail=$row->product_name . ($row->variant ? ' (' . $row->variant . ')' : '');   
                if ($row->data_id_khuyenmai>0) {
                    //get event name 
                    $obj_km=$this->site->getKhuyenmaiById($row->data_id_khuyenmai);
                    if ($obj_km!=false) {
                        $_prod_detail.=" <i>(".$obj_km->tenevent.")</i>";    
                    }else{
                        $_prod_detail.=" <i>(KHUYẾN MÃI)</i>";
                    }
                    
                } 
                $_prod_detail.=$row->details ? '<br>' . html_entity_decode($row->details) : '';    
                
                $_tablhd.='<tr><td style="text-align:center;vertical-align:middle;">'.$r.'</td><td style="vertical-align:middle;">'.$_prod_detail.'</td><td style="text-align:center; vertical-align:middle;">'.$this->sma->formatQuantity($row->unit_quantity).' '.$row->product_unit_code.'</td>      </tr>';  
                
                $r++;
            }
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
                $this->session->set_flashdata('error', lang('Không tìm thấy thông tin hóa đơn'));
                $this->sma->md();
            }
 
            $this->data['delivery'] = $deli;
            $this->data['biller'] = $biller=$this->site->getCompanyByID($this->Settings->default_biller);
            $this->data['doitac'] = $doitac=$this->doitac_model->getDoitacByID($deli->delivered_by);


            $this->data['rows'] = $rows=$this->site->getAllPurchaseItemsPrint($purchaseobj->id);

            $this->data['user'] = $user=$this->site->getUser($deli->created_by);
            $this->data['warehouse'] = $warehouse=$this->site->getWarehouseByID($purchaseobj->warehouse_id); 

            $this->data['customer'] =$customer= $this->site->getCompanyByID($purchaseobj->supplier_id); 
            
            
            
            $r = 1;
            $_tablhd='<table border="1" style="width:100%;border-collapse:collapse;">        <tbody>        <tr>            <td style="text-align:center;width:5%;"><strong>STT</strong>            </td>            <td style="width:51%;padding-left:0.5%;"><strong>Tên hàng hóa</strong><br>            </td>                        <td style="text-align:center;width:15%;"><strong>SL</strong><br>            </td>      </tr>';
            foreach ($rows as $row){
                $_prod_detail=$row->product_code.' - '.$row->product_name . ($row->variant ? ' (' . $row->variant . ')' : '');    
                $_prod_detail.=$row->details ? '<br>' . html_entity_decode($row->details) : '';    
                
                $_tablhd.='<tr><td style="text-align:center;vertical-align:middle;">'.$r.'</td><td style="vertical-align:middle;">'.$_prod_detail.'</td><td style="text-align:center; vertical-align:middle;">'.$this->sma->formatQuantity($row->unit_quantity).' '.$row->product_unit_code.'</td>      </tr>';  
                
                $r++;
            }
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
        $this->data['kich_thuoc']=$this->settings_model->define_size_in($_sizein_page,$_chieuin_page);  
        
        //replace value size print
        $sale_temp=$this->settings_model->define_print_replace($sale_temp);
            
        $message = $this->parser->parse_string($sale_temp, $parse_data,true);
        
        $this->data['note'] = array('noidung' =>$message);
        $this->data['id'] = $deli->id;  
        $this->data['giaohang'] = true;     
        $this->data['modal_js'] = $this->site->modal_js();  
        $this->load->view($this->theme . 'sales/printgiao', $this->data);   
    }

    public function printsalelhson($id = null)    {      
        $this->sma->checkPermissions('index');       
        if ($this->input->get('id')) {        
            $id = $this->input->get('id');     
        }     
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');    
        $inv = $this->sales_model->getInvoiceByID($id);      
        if (!$this->session->userdata('view_right')) {           
            $this->sma->view_rights($inv->created_by);       
        }        
        $this->data['barcode'] = "<img src='" . site_url('products/gen_barcode/' . $inv->reference_no) . "' alt='" . $inv->reference_no . "' class='pull-left' />";        
        $this->data['customer'] =$customer= $this->site->getCompanyByID($inv->customer_id);        
        $this->data['payments'] = $this->sales_model->getPaymentsForSale($id);
        $this->data['biller'] = $biller=$this->site->getCompanyByID($inv->biller_id);       
        $this->data['created_by'] = $created_by=$this->site->getUser($inv->created_by); 
        $this->data['updated_by'] = $inv->updated_by ? $this->site->getUser($inv->updated_by) : null;        
        $this->data['warehouse'] = $warehouse=$this->site->getWarehouseByID($inv->warehouse_id); 
        $this->data['inv'] = $inv;      
        $this->data['rows'] =$rows= $this->sales_model->getAllInvoiceItems($id);  
        $this->data['return_sale'] = $return_sale=$inv->return_id ? $this->sales_model->getInvoiceByID($inv->return_id) : NULL;  
        $this->data['return_rows'] = $return_rows=$inv->return_id ? $this->sales_model->getAllInvoiceItems($inv->return_id) : NULL;
        $tong_thue=0;        
        
        if ($this->Settings->tax2 && $inv->order_tax != 0) {        
            $tong_thue= $this->sma->formatMoney($return_sale ? ($inv->order_tax+$return_sale->order_tax) : $inv->order_tax);    
        }        
        $_giamgia=0;        
        if ($inv->order_discount != 0) {    
            $_giamgia=$this->sma->formatMoney($return_sale ? ($inv->order_discount+$return_sale->order_discount) : $inv->order_discount);   
        }
        $tong_dathanhtoan=$this->sma->formatMoney($return_sale ? ($inv->paid+$return_sale->paid) : $inv->paid);    
        
        $tong_dathanhtoan_num=$return_sale ? ($inv->grand_total+$return_sale->grand_total) : $inv->grand_total;
        
        $_chuathanhtoan=$this->sma->formatMoney(($return_sale ? ($inv->grand_total+$return_sale->grand_total) : $inv->grand_total) - ($return_sale ? ($inv->paid+$return_sale->paid) : $inv->paid));  
        $_chuathanhtoan_num=($return_sale ? ($inv->grand_total+$return_sale->grand_total) : $inv->grand_total) - ($return_sale ? ($inv->paid+$return_sale->paid) : $inv->paid);   
        
        $_tongthanhtoan=$this->sma->formatMoney($return_sale ? ($inv->grand_total+$return_sale->grand_total) : $inv->grand_total);
        $_tongcong=0;   
        $_tongcong_bang_chu=0;
        if ($inv->grand_total != $inv->total) {        
            $_tongcong=$this->sma->formatMoney($return_sale ? (($inv->total + $inv->product_tax)+($return_sale->total + $return_sale->product_tax)) : ($inv->total + $inv->product_tax)); 
            $_tongcong_bang_chu=$return_sale ? (($inv->total + $inv->product_tax)+($return_sale->total + $return_sale->product_tax)) : ($inv->total + $inv->product_tax);       
        }else{
            $_tongcong=$this->sma->formatMoney($inv->grand_total);   
            $_tongcong_bang_chu=$inv->grand_total;
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
        
        $_tongthuhoi=0;        
        if ($return_sale) {        
            $_tongthuhoi=$this->sma->formatMoney($return_sale->grand_total);    
        }        
        $_phiphat=0;        
        if ($inv->surcharge != 0) {    
            $_phiphat=$this->sma->formatMoney($inv->surcharge);        
        }    
        $_diem_thuong=0;    
        $_tong_diem=0;        
        if ($customer->award_points != 0 && $this->Settings->each_spent > 0) {     
            $_diem_thuong=floor(($inv->grand_total/$this->Settings->each_spent)*$this->Settings->ca_point);    
            $_tong_diem=$_diem_thuong+floor($customer->award_points);        
        }    
        $_dathanhtoan= $this->reports_model->getSalesTotals($inv->customer_id);    
        $company_details = $this->companies_model->getCompanyByID($inv->customer_id);       
        $no_cu=(float)$company_details->nobandau;    
        if(isset($_dathanhtoan->total_amount) || isset($_dathanhtoan->paid)){    
            $no_cu+=$_dathanhtoan->total_amount -  $_dathanhtoan->paid;    
        }
        $no_cu=$no_cu-$inv->grand_total;
        
        $_tong_no_cu=$this->sma->formatMoney($no_cu);   
        $tong_no_all=$this->sma->formatMoney($no_cu+$_chuathanhtoan_num);
        $_tongno_bang_chu=($no_cu+$_chuathanhtoan_num);
        
        
        
        $left_end_tongno=substr($_tongno_bang_chu,strlen($_tongno_bang_chu)-5,strlen($_tongno_bang_chu));
        if($left_end_tongno=='.0000'){
             $_tongno_bang_chu=str_replace($left_end_tongno,"",$_tongno_bang_chu);
         }
        $_tongno_bang_chu_text=$this->site->convert_number_to_words($_tongno_bang_chu);
        
        
        $left_end_tongdathanhtoan=substr($tong_dathanhtoan_num,strlen($tong_dathanhtoan_num)-5,strlen($tong_dathanhtoan_num));
        if($left_end_tongdathanhtoan=='.0000'){
             $tong_dathanhtoan_num=str_replace($left_end_tongdathanhtoan,"",$tong_dathanhtoan_num);
         }
        $_tongdathanhtoan_bang_chu_text=$this->site->convert_number_to_words($tong_dathanhtoan_num);    
        
        $_tongdathanhtoan_bang_chu_text=strtolower($_tongdathanhtoan_bang_chu_text);
        $_1_text=substr($_tongdathanhtoan_bang_chu_text,0,1);
        $_2_text=substr($_tongdathanhtoan_bang_chu_text,1,strlen($_tongdathanhtoan_bang_chu_text));
        $_tongdathanhtoan_bang_chu_text=strtoupper($_1_text).$_2_text." đồng";
        
        $r = 1;       
        $tax_summary = array();    
        $_tablhd='<table border="1" style="width:100%;border-collapse:collapse;">        <tbody>        <tr>            <td style="text-align:center;width:5%;"><strong>STT</strong>            </td>            <td style="width:51%;padding-left:0.5%;"><strong>Tên hàng hóa</strong><br>            </td>            <td style="text-align:right;padding-right:0.5%;width:12%;"><strong>Đơn giá</strong><br>            </td>            <td style="text-align:center;width:15%;"><strong>SL</strong><br>            </td>            <td style="text-align:right;padding-right:0.5%;width:15.5%;"><strong>Thành tiền</strong><br>            </td>        </tr>';
        
        $_tablhd_pos='<table border="0" style="width:100%;border-collapse:collapse;font-size:12px;margin:5px 0px">        <tbody>        <tr style="border-bottom: 1px solid;">            <td style="text-align:center;width:5%;"><strong>STT</strong>            </td>            <td style="width:63.5%;padding-left:0.5%;"><strong>Tên - Giá</strong><br>            </td> <td style="text-align:center;width:15%;"><strong>SL</strong><br>            </td>            <td style="text-align:right;padding-right:0.5%;width:15.5%;"><strong>T. Tiền</strong><br>            </td>        </tr>';

        if ($this->Settings->item_print==1){
            $_tablhd='<table border="1" style="width:100%;border-collapse:collapse;">        <tbody>        <tr>            <td style="text-align:center;width:5%;"><strong>STT</strong>            </td>            <td style="width:44%;padding-left:0.5%;text-align:center"><strong>Tên hàng hóa</strong><br>            </td>            <td style="text-align:center;width:20%;" colspan="2"><strong>Số Lượng</strong><br>            </td>  <td style="text-align:center;width:15%;"><strong style="text-align:center;">Kho Xuất</strong><br>            </td>        </tr><tr><td></td><td></td><td style="text-align:center;">Yêu cầu</td><td style="text-align:center;">Thực tế</td><td></td></tr>';
        }
        
        foreach ($rows as $row):        
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
            $_prod_detail=$row->product_name;
            if ($row->data_id_khuyenmai>0) {
                //get event name 
                $obj_km=$this->site->getKhuyenmaiById($row->data_id_khuyenmai);
                if ($obj_km!=false) {
                    $_prod_detail.=" <i>(".$obj_km->tenevent.")</i>";    
                }else{
                    $_prod_detail.=" <i>(KHUYẾN MÃI)</i>";
                }
                
            } 

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
            
            if ($this->Settings->item_print==0){
                $_tablhd.='<tr><td rowspan="2" style="text-align:center;vertical-align:middle;">'.$r.'</td>
                <td colspan="4" style="vertical-align:middle;">'.$_prod_detail.'</td>
                </tr>
                <tr>
                    <td style="text-align:right; padding-right:10px;">'.$_strgia.'</td>
                    <td style="text-align:center; vertical-align:middle;">'.$this->sma->formatQuantity($row->unit_quantity).' '.$row->product_unit_code.'</td>
                    <td style="text-align:right; width:120px; padding-right:10px;">'.$this->sma->formatMoney($row->subtotal).'</td>
                </tr>';  
            }else if ($this->Settings->item_print==1){
                $_tablhd.='<tr><td style="text-align:center;vertical-align:middle;">'.$r.'</td><td style="vertical-align:middle;">'.$_prod_detail.'</td><td style="text-align:center; vertical-align:middle;">'.$this->sma->formatQuantity($row->unit_quantity).' '.$row->product_unit_code.'</td><td></td><td></td></tr>';  
            }else{
                $_tablhd.='<tr><td style="text-align:center;vertical-align:middle;">'.$r.'</td><td style="vertical-align:middle;">'.$_prod_detail.'</td><td style="text-align:right; padding-right:10px;">'.$_strgia.'</td><td style="text-align:center; vertical-align:middle;">'.$this->sma->formatQuantity($row->unit_quantity).' '.$row->product_unit_code.'</td><td style="text-align:right; width:120px; padding-right:10px;">'.$this->sma->formatMoney($row->subtotal).'</td>            </tr>';  
            }

            $_tablhd_pos.='<tr><td rowspan="2" style="text-align:center;vertical-align:middle;">'.$r.'</td>
                <td colspan="4" style="vertical-align:middle;">'.$_prod_detail.'</td>            
            </tr>
            <tr style="border-bottom: 1px solid;line-height: 25px;">
                <td style="text-align:left; padding-right:10px;">'.$_strgia.'</td>
                <td style="text-align:center; vertical-align:middle;">'.$this->sma->formatQuantity($row->unit_quantity).'</td>
                <td style="text-align:right; width:120px; padding-right:5px;">'.$this->sma->formatMoney($row->subtotal).'</td>            
            </tr>';  

            $r++;    
        endforeach;
        
        if ($return_rows) {        
            $_tablhd.='<tr class="warning"><td colspan="100%" class="no-border"><strong>'.lang('returned_items').'</strong></td></tr>';
            foreach ($return_rows as $row):        
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
            $_rt_prod=$row->product_name . ($row->variant ? ' (' . $row->variant . ')' : '');
            $_rt_prod.=$row->details ? '<br>' .html_entity_decode($row->details): '';       
            $_rt_prod.=$row->baohanh ? '<br>Bảo hành:' . html_entity_decode($row->baohanh) : '';  
            $_rt_prod.=$row->serial_no ? '<br>Serial/Imei:' . html_entity_decode($row->serial_no) : '';                 
            $_rt_strgia=$this->sma->formatMoney($row->unit_price);        
            //if ($Settings->product_discount && $inv->product_discount != 0) 
            {    
                $_rt_strgia.=($row->discount != 0 ? "<br/> Giảm: (".$this->sma->formatMoney($row->item_discount).")":'');        
            }            
            
            if ($this->Settings->item_print==0){
                $_tablhd.='<tr class="warning">    <td style="text-align:center; vertical-align:middle;">'.$r.'</td>    <td style="vertical-align:middle;">'.$_rt_prod.'</td>    <td style="text-align:right; padding-right:10px;">'.$_rt_strgia.'</td>        <td style="text-align:center; vertical-align:middle;">'.$this->sma->formatQuantity($row->quantity).' '.$row->product_unit_code.'</td><td style="text-align:right;padding-right:10px;">'.$this->sma->formatMoney($row->subtotal).'</td></tr>';
            }else if ($this->Settings->item_print==1){
                $_tablhd.='<tr class="warning">    <td style="text-align:center; vertical-align:middle;">'.$r.'</td>    <td style="vertical-align:middle;">'.$_rt_prod.'</td>    <td style="text-align:center; vertical-align:middle;">'.$this->sma->formatQuantity($row->quantity).' '.$row->product_unit_code.'</td></tr>';  
            }else{
                $_tablhd.='<tr class="warning">    <td style="text-align:center; vertical-align:middle;">'.$r.'</td>    <td style="vertical-align:middle;">'.$_rt_prod.'</td>    <td style="text-align:right; padding-right:10px;">'.$_rt_strgia.'</td>        <td style="text-align:center; vertical-align:middle;">'.$this->sma->formatQuantity($row->quantity).' '.$row->product_unit_code.'</td><td style="text-align:right;padding-right:10px;">'.$this->sma->formatMoney($row->subtotal).'</td></tr>';
            }   
            
             $_tablhd_pos.='<tr><td rowspan="2" style="text-align:center;vertical-align:middle;">'.$r.'</td>
                    <td colspan="4" style="vertical-align:middle;">'.$_rt_prod.'</td>            
                </tr>
                <tr>
                    <td style="text-align:left; padding-right:10px;">'.$row->net_unit_price.'</td>
                    <td style="text-align:center; vertical-align:middle;">'.$this->sma->formatQuantity($row->quantity).'</td>
                    <td style="text-align:right; width:120px; padding-right:5px;">'.$this->sma->formatMoney($row->subtotal).'</td>            
                </tr>';  

            $r++;            
            endforeach;    
        }        
        $_tablhd.='</table>';      
        $_tablhd_pos.='</table>';        

        $_dc_cuahang=str_replace("<p>","",$warehouse->address);       
        $_dc_cuahang=str_replace("</p>","",$_dc_cuahang);
        
        $_ghichu="";
        if ($inv->note!=""&&$this->sma->decode_html($inv->note)!="") {
            $_ghichu="<p> Ghi chú: ".$this->sma->decode_html($inv->note)."</p>";
        }
        $_tongthue=$this->sma->formatMoney($inv->order_tax); 
        
        $order_tax_details = $this->site->getTaxRateByID($inv->order_tax_id);
        $_thue_no=$order_tax_details->name;

        $this->data['doitac'] = $doitac=$this->doitac_model->getDoitacByID($inv->doitac);
        
        $parse_data = array('Ma_Don_Hang' => $inv->reference_no,'Khach_Hang' => $customer->name,'site_link' => base_url(),'site_name' => $this->Settings->site_name,'logo' => '<img src="' . base_url() . 'assets/uploads/logos/' . $biller->logo . '" alt="' . ($biller->company != '-' ? $biller->company : $biller->name) . '"/>','Ten_Kho' => $warehouse->name,'Dia_Chi_Kho' =>$_dc_cuahang,'SDT_Kho' =>$warehouse->phone,'Dia_chi_kh' => $customer->address,'Dien_thoai_kh' => $customer->phone,'Ngay_Xuat' => $this->sma->hrld($inv->date),'Tong_Thanh_Toan' =>$_tongthanhtoan,'Nhan_Vien_Thu_Ngan' =>$created_by->first_name . ' ' . $created_by->last_name,'Phu_Phi' => $this->sma->formatMoney($inv->shipping),'Ghi_Chu' =>$_ghichu,'Ghi_Chu_NV' =>$this->sma->decode_html($inv->staff_note),'No_cu' =>$_tong_no_cu,'Chua_Thanh_Toan' => $_chuathanhtoan,'Da_Thanh_Toan' => $tong_dathanhtoan,'Tong_Diem_Tich_Luy' =>$_tong_diem,'CongTy_KH' =>$customer->company,'MST_KH' =>$customer->vat_no,'Diem_hoa_don' =>$_diem_thuong,'Giam_Gia_Tren_Hoa_Don' =>$_giamgia,'Tong_Tien_Hang' =>$_tongcong,'Tong_Tien_Bang_Chu' =>$_tongcong_bang_chu_text,'Tong_thu_hoi' =>$_tongthuhoi,'Tong_No'=>$tong_no_all,'THUE_NO'=>$_thue_no,'THUE'=>$_tongthue,'Bang_Hoa_Don' =>$_tablhd,'Bang_Hoa_Don_POS' =>$_tablhd_pos,'Ma_Doi_Tac' => $doitac->code,'Ten_Doi_Tac' => $doitac->name,'Dia_Chi_Doi_Tac' => $doitac->diachi,'Email_Doi_Tac' => $doitac->email,'Dien_Thoai_Doi_Tac' => $doitac->dienthoai,'No_Dau_Ky_Doi_Tac' => $doitac->nodauky,'Tong_No_Bang_Chu'=>$_tongno_bang_chu_text,'Tong_Thanh_Toan_Bang_Chu'=>$_tongdathanhtoan_bang_chu_text);    
                    
        
        if($this->Settings->item_print==1){
            $sale_temp = file_get_contents('./themes/default/views/print_templates/printpos_nocu.html');   
        }else if($this->Settings->item_print==2){
            $sale_temp = file_get_contents('./themes/default/views/print_templates/printpos_kono.html');   
        }else{
            if (file_exists('./themes/' . $this->theme . '/views/print_templates/printpos.html')) {          
                $sale_temp = file_get_contents('themes/' . $this->theme . '/views/print_templates/printpos.html');            
            } else {             
                $sale_temp = file_get_contents('./themes/default/views/print_templates/printpos.html');          
            }    
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
        $this->load->view($this->theme . 'sales/print', $this->data);   
    }
    public function printgiao($id = null)
    {
        $this->sma->checkPermissions('deliveries');

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $deli = $this->sales_model->getDeliveryByID($id);
        if ((int)$deli->sale_id>0) {
            $sale = $this->sales_model->getInvoiceByID($deli->sale_id);
            
            $phaithu=$sale->grand_total-$sale->paid;

            if (!$sale) {
                $this->session->set_flashdata('error', lang('Không tìm thấy thông tin hóa đơn'));
                $this->sma->md();
            }

            $this->data['delivery'] = $deli;
            $this->data['biller'] = $biller=$this->site->getCompanyByID($sale->biller_id);
            $this->data['doitac'] = $doitac=$this->doitac_model->getDoitacByID($deli->delivered_by);
            $this->data['rows'] = $rows=$this->sales_model->getAllInvoiceItemsWithDetails($deli->sale_id);
            $this->data['user'] = $user=$this->site->getUser($deli->created_by);
            $this->data['warehouse'] = $warehouse=$this->site->getWarehouseByID($sale->warehouse_id); 
            $this->data['customer'] =$customer= $this->site->getCompanyByID($sale->customer_id); 
            
            
            
            $r = 1;
            $_tablhd='<table border="1" style="width:100%;border-collapse:collapse;">        <tbody>        <tr>            <td style="text-align:center;width:5%;"><strong>STT</strong>            </td>            <td style="width:51%;padding-left:0.5%;"><strong>Tên hàng hóa</strong><br>            </td>                        <td style="text-align:center;width:15%;"><strong>SL</strong><br>            </td>      </tr>';
            foreach ($rows as $row){
                $_prod_detail=$row->product_name . ($row->variant ? ' (' . $row->variant . ')' : '');   
                if ($row->data_id_khuyenmai>0) {
                    //get event name 
                    $obj_km=$this->site->getKhuyenmaiById($row->data_id_khuyenmai);
                    if ($obj_km!=false) {
                        $_prod_detail.=" <i>(".$obj_km->tenevent.")</i>";    
                    }else{
                        $_prod_detail.=" <i>(KHUYẾN MÃI)</i>";
                    }
                    
                } 
                $_prod_detail.=$row->details ? '<br>' . html_entity_decode($row->details) : '';    
                
                $_tablhd.='<tr><td style="text-align:center;vertical-align:middle;">'.$r.'</td><td style="vertical-align:middle;">'.$_prod_detail.'</td><td style="text-align:center; vertical-align:middle;">'.$this->sma->formatQuantity($row->unit_quantity).' '.$row->product_unit_code.'</td>      </tr>';  
                
                $r++;
            }
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
                $this->session->set_flashdata('error', lang('Không tìm thấy thông tin hóa đơn'));
                $this->sma->md();
            }
 
            $this->data['delivery'] = $deli;
            $this->data['biller'] = $biller=$this->site->getCompanyByID($this->Settings->default_biller);
            $this->data['doitac'] = $doitac=$this->doitac_model->getDoitacByID($deli->delivered_by);


            $this->data['rows'] = $rows=$this->site->getAllPurchaseItemsPrint($purchaseobj->id);

            $this->data['user'] = $user=$this->site->getUser($deli->created_by);
            $this->data['warehouse'] = $warehouse=$this->site->getWarehouseByID($purchaseobj->warehouse_id); 

            $this->data['customer'] =$customer= $this->site->getCompanyByID($purchaseobj->supplier_id); 
            
            
            
            $r = 1;
            $_tablhd='<table border="1" style="width:100%;border-collapse:collapse;">        <tbody>        <tr>            <td style="text-align:center;width:5%;"><strong>STT</strong>            </td>            <td style="width:51%;padding-left:0.5%;"><strong>Tên hàng hóa</strong><br>            </td>                        <td style="text-align:center;width:15%;"><strong>SL</strong><br>            </td>      </tr>';
            foreach ($rows as $row){
                $_prod_detail=$row->product_code.' - '.$row->product_name . ($row->variant ? ' (' . $row->variant . ')' : '');    
                $_prod_detail.=$row->details ? '<br>' . html_entity_decode($row->details) : '';    
                
                $_tablhd.='<tr><td style="text-align:center;vertical-align:middle;">'.$r.'</td><td style="vertical-align:middle;">'.$_prod_detail.'</td><td style="text-align:center; vertical-align:middle;">'.$this->sma->formatQuantity($row->unit_quantity).' '.$row->product_unit_code.'</td>      </tr>';  
                
                $r++;
            }
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
        $this->data['kich_thuoc']=$this->settings_model->define_size_in($_sizein_page,$_chieuin_page);  
        
        //replace value size print
        $sale_temp=$this->settings_model->define_print_replace($sale_temp);
            
        $message = $this->parser->parse_string($sale_temp, $parse_data,true);
        
        $this->data['note'] = array('noidung' =>$message);
        $this->data['id'] = $deli->id;  
        $this->data['giaohang'] = true;     
        $this->data['modal_js'] = $this->site->modal_js();  
        $this->load->view($this->theme . 'sales/printgiao', $this->data);   
    }
    
	function suggestionsDoitac($term = NULL, $limit = NULL)
    {
        if ($this->input->get('term')) {
            $term = $this->input->get('term', TRUE);
        }
        if (strlen($term) < 1) {
            return FALSE;
        }
        $limit = $this->input->get('limit', TRUE);
        $rows['results'] = $this->doitac_model->getDoitacSuggestions($term, $limit);
        $this->sma->send_json($rows);
    }
    function getDoiTacById($id = NULL)
    {
        // $this->sma->checkPermissions('index');
        $row = $this->doitac_model->getDoiTacById($id);
        $this->sma->send_json(array(array('id' => $row->id, 'text' => ($row->code != '-' ? $row->code.'-'.$row->name : $row->name.'-'.$row->phone))));
    }
	/*LHSON Bo SUNG TRA HANG CONG NO*/
	public function addthuhoi($quote_id = null)
    {
        $this->sma->checkPermissions();
        $sale_id = $this->input->get('sale_id') ? $this->input->get('sale_id') : NULL;

        $this->form_validation->set_message('is_natural_no_zero', lang("no_zero_required"));
        $this->form_validation->set_rules('customer', lang("customer"), 'required');
        $this->form_validation->set_rules('biller', lang("biller"), 'required');
        $this->form_validation->set_rules('payment_status', lang("payment_status"), 'required');

        if ($this->form_validation->run() == true) {

            $reference = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('re');
			//return
			$reference_return = $this->site->getReference('rt');
            
			if ($this->Owner || $this->Admin) {
				$date = $this->sma->fld(trim($this->input->post('date')));
            } else {
				$date = date('Y-m-d H:i:s');
            }
            $warehouse_id = $this->input->post('warehouse');
            $customer_id = $this->input->post('customer');
            $biller_id = $this->input->post('biller');
            $total_items = $this->input->post('total_items');
            $sale_status = "returned";
            $payment_status = $this->input->post('payment_status');
            $payment_term = $this->input->post('payment_term');
			$doitac = $this->input->post('doitac');
            $due_date = $payment_term ? date('Y-m-d', strtotime('+' . $payment_term . ' days', strtotime($date))) : null;
            $shipping = $this->input->post('shipping') ? $this->input->post('shipping') : 0;
            $customer_details = $this->site->getCompanyByID($customer_id);
            $customer = $customer_details->company != '-' ? $customer_details->company : $customer_details->name;
            $biller_details = $this->site->getCompanyByID($biller_id);
            $biller = $biller_details->company != '-' ? $biller_details->company : $biller_details->name;
            $note = $this->input->post('note');
            $staff_note = $this->sma->clear_tags($this->input->post('staff_note'));
            $quote_id = $this->input->post('quote_id') ? $this->input->post('quote_id') : null;

            $total = 0;
            $product_tax = 0;
            $order_tax = 0;
            $product_discount = 0;
            $order_discount = 0;
            $percentage = '%';
            $digital = FALSE;
			
			
			for ($m = 0; $m < $i_tra; $m++) {
                $item_id = $_POST['product_id'][$m];
				//kiem tra tong thu hoi so voi tong sl ban ra
				$item_unit_quantity = $_POST['quantity_tra'][$m];
				$tongthuhoi=(float)$this->site->getTongthuhoi($item_id,$warehouse_id)+$item_unit_quantity;
				
				$tongbanra=(float)$this->site->getTongSoluongBanra($item_id,$warehouse_id);
				
				if($tongbanra<=0){
					$this->session->set_userdata('remove_rels', 1);
					$this->session->set_flashdata('error','Lỗi: Sản phẩm ['.$_POST['product_name'][$m].'] bán ra ['.$tongbanra.'] thu hồi ['.$tongthuhoi.']');
					redirect("sales");
				}				
			}
					
			
            $i = isset($_POST['product_code']) ? sizeof($_POST['product_code']) : 0;
            for ($r = 0; $r < $i; $r++) {
				$item_id = $_POST['product_id'][$r];
				$item_type = $_POST['product_type'][$r];
				$item_code = $_POST['product_code'][$r];
				$item_name = $_POST['product_name'][$r];
				$item_option = isset($_POST['product_option'][$r]) && $_POST['product_option'][$r] != 'false' && $_POST['product_option'][$r] != 'null' ? $_POST['product_option'][$r] : null;
				$real_unit_price = $this->sma->formatDecimal($_POST['real_unit_price'][$r]);
				$unit_price =$unit_price_old= $this->sma->formatDecimal($_POST['unit_price'][$r]);
				$item_unit_quantity =  (0-$_POST['quantity'][$r]);
				$item_serial = isset($_POST['serial'][$r]) ? $_POST['serial'][$r] : '';
				$item_tax_rate = isset($_POST['product_tax'][$r]) ? $_POST['product_tax'][$r] : null;
				$item_discount = isset($_POST['product_discount'][$r]) ? $_POST['product_discount'][$r] : null;
				$item_unit = $_POST['product_unit'][$r];
				$item_quantity =  (0-$_POST['product_base_quantity'][$r]);

				if (isset($item_code) && isset($real_unit_price) && isset($unit_price) && isset($item_quantity)) {
					$product_details = $item_type != 'manual' ? $this->sales_model->getProductByCode($item_code) : null;
					$unit_price = $real_unit_price;
					$pr_discount = 0;
					if ($item_type == 'digital') {
						$digital = TRUE;
					}

					if (isset($item_discount)) {
						$discount = $item_discount;
						$dpos = strpos($discount, $percentage);
						if ($dpos !== false) {
							$pds = explode("%", $discount);
							$pr_discount = $this->sma->formatDecimal(((($this->sma->formatDecimal($unit_price)) * (Float) ($pds[0])) / 100), 4);
						} else {
							$pr_discount = $this->sma->formatDecimal($discount);
						}
					}

					//$unit_price = $this->sma->formatDecimal($unit_price - $pr_discount);
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
						$pr_item_tax = $this->sma->formatDecimal($item_tax * $item_unit_quantity, 4);

					}

					$product_tax += $pr_item_tax;
					$subtotal = (($item_net_price * $item_unit_quantity) + $pr_item_tax)-$pr_discount;
					$unit = $this->site->getUnitByID($item_unit);

					$products[] = array(
						'product_id' => $item_id,
						'product_code' => $item_code,
						'product_name' => $item_name,
						'product_type' => $item_type,
						'option_id' => $item_option,
						'net_unit_price' => $item_net_price,
						'unit_price' => $this->sma->formatDecimal($item_net_price + $item_tax),
						'quantity' => $item_quantity,
						'product_unit_id' => $item_unit,
						'product_unit_code' => $unit ? $unit->code : NULL,
						'unit_quantity' => $item_unit_quantity,
						'warehouse_id' => $warehouse_id,
						'item_tax' => $pr_item_tax,
						'tax_rate_id' => $pr_tax,
						'tax' => $tax,
						'discount' => $item_discount,
						'item_discount' => $pr_item_discount,
						'subtotal' => $this->sma->formatDecimal($subtotal),
						'serial_no' => $item_serial,
						'real_unit_price' => $real_unit_price,
					);

					$total += $this->sma->formatDecimal(($item_net_price * $item_unit_quantity), 4)-$pr_item_discount;
				}
            }
            if (empty($products)) {
				$this->form_validation->set_rules('product', lang("order_items"), 'required');
            } else {
				krsort($products);
            }

            if ($this->input->post('order_discount')) {
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
            $total_discount = $this->sma->formatDecimal($order_discount + $product_discount);

            if ($this->Settings->tax2) {
				$order_tax_id = $this->input->post('order_tax');
				if ($order_tax_details = $this->site->getTaxRateByID($order_tax_id)) {
					if ($order_tax_details->type == 2) {
						$order_tax = $this->sma->formatDecimal($order_tax_details->rate);
					} elseif ($order_tax_details->type == 1) {
						$order_tax = $this->sma->formatDecimal(((($total + $product_tax - $order_discount) * $order_tax_details->rate) / 100), 4);
					}
				}
            } else {
				$order_tax_id = null;
            }

            $total_tax = $this->sma->formatDecimal(($product_tax + $order_tax), 4); 
            $grand_total = $this->sma->formatDecimal(($total + $total_tax + $this->sma->formatDecimal($shipping) + $order_discount), 4);
            
					
			krsort($products);
			

			$data = array('date' => $date,
					'reference_no' => $reference,
					'customer_id' => $customer_id,
					'customer' => $customer,
					'biller_id' => $biller_id,
					'biller' => $biller,
					'doitac' => $doitac,
					'warehouse_id' => $warehouse_id,
					'note' => $note,
					'staff_note' => $staff_note,
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
					'total_items' => $total_items,
					'sale_status' => $sale_status,
					'payment_status' => $payment_status,
					'payment_term' => $payment_term,
					'due_date' => $due_date,
					'paid' => 0,
					'created_by' => $this->session->userdata('user_id'),
								);

			if ($payment_status == 'partial' || $payment_status == 'paid') {
				if ($this->input->post('paid_by') == 'deposit') {
					if ( ! $this->site->check_customer_deposit($customer_id, $this->input->post('amount-paid'))) {
						$this->session->set_flashdata('error', lang("amount_greater_than_deposit"));
						redirect($_SERVER["HTTP_REFERER"]);
					}
				}
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
                        'warehouse_id' => $warehouse_id,
						'type' => 'returned',
						'gc_balance' => $gc_balance,
						'id_ncc_id_kh' => $customer_id,
						
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
						'type' => 'returned',
						'id_ncc_id_kh' => $customer_id,
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

            //$this->sma->print_arrays($data, $products, $payment);
        }

        if ($this->form_validation->run() == true && $this->sales_model->addSale($data, $products, $payment)) {
            $this->session->set_userdata('remove_slls', 1);
            if ($quote_id) {
				$this->db->update('quotes', array('status' => 'completed'), array('id' => $quote_id));
            }
            $this->session->set_flashdata('message', lang("Thêm thu hồi công nợ thành công"));
            redirect("sales/danhsachthuhoi");
        } else {

            if ($quote_id || $sale_id) {
				if ($quote_id) {
					$this->data['quote'] = $this->sales_model->getQuoteByID($quote_id);
					$items = $this->sales_model->getAllQuoteItems($quote_id);
				} elseif ($sale_id) {
					$this->data['quote'] = $this->sales_model->getInvoiceByID($sale_id);
					$items = $this->sales_model->getAllInvoiceItems($sale_id);
				}
				krsort($items);
				$c = rand(100000, 9999999);
				foreach ($items as $item) {
					$row = $this->site->getProductByID($item->product_id);
					if (!$row) {
						$row = json_decode('{}');
						$row->tax_method = 0;
					} else {
						unset($row->cost, $row->details, $row->product_details, $row->image, $row->barcode_symbology, $row->cf1, $row->cf2, $row->cf3, $row->cf4, $row->cf5, $row->cf6, $row->supplier1price, $row->supplier2price, $row->cfsupplier3price, $row->supplier4price, $row->supplier5price, $row->supplier1, $row->supplier2, $row->supplier3, $row->supplier4, $row->supplier5, $row->supplier1_part_no, $row->supplier2_part_no, $row->supplier3_part_no, $row->supplier4_part_no, $row->supplier5_part_no);
					}
					$row->quantity = 0;
					$pis = $this->site->getPurchasedItems($item->product_id, $item->warehouse_id, $item->option_id);
					if ($pis) {
						foreach ($pis as $pi) {
							$row->quantity += $pi->quantity_balance;
						}
					}
					$row->id = $item->product_id;
					$row->code = $item->product_code;
					$row->name = $item->product_name;
					$row->type = $item->product_type;
					$row->qty = $item->quantity;
					$row->base_quantity = $item->quantity;
					$row->base_unit = $row->unit ? $row->unit : $item->product_unit_id;
					$row->base_unit_price = $row->price ? $row->price : $item->unit_price;
					$row->unit = $item->product_unit_id;
					$row->qty = $item->unit_quantity;
					$row->discount = $item->discount ? $item->discount : '0';
					$row->price = $this->sma->formatDecimal($item->net_unit_price + $this->sma->formatDecimal($item->item_discount / $item->quantity));
					$row->unit_price = $row->tax_method ? $item->unit_price + $this->sma->formatDecimal($item->item_discount / $item->quantity) + $this->sma->formatDecimal($item->item_tax / $item->quantity) : $item->unit_price + ($item->item_discount / $item->quantity);
					$row->real_unit_price = $item->real_unit_price;
					$row->tax_rate = $item->tax_rate_id;
					$row->serial = '';
					$row->option = $item->option_id;
					$options = $this->sales_model->getProductOptions($row->id, $item->warehouse_id);
					if ($options) {
						$option_quantity = 0;
						foreach ($options as $option) {
							$pis = $this->site->getPurchasedItems($row->id, $item->warehouse_id, $item->option_id);
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
					$combo_items = false;
					if ($row->type == 'combo') {
						$combo_items = $this->sales_model->getProductComboItems($row->id, $item->warehouse_id);
					}
					$units = $this->site->getUnitsByBUID($row->base_unit);
					$tax_rate = $this->site->getTaxRateByID($row->tax_rate);
					$ri = $this->Settings->item_addition ? $row->id : $c;
				   
					$pr[$ri] = array('id' => $c, 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 
							'row' => $row, 'combo_items' => $combo_items, 'tax_rate' => $tax_rate, 'units' => $units, 'options' => $options);
					$c++;
				}
				$this->data['quote_items'] = json_encode($pr);
            }

            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['quote_id'] = $quote_id ? $quote_id : $sale_id;
            $this->data['billers'] = $this->site->getAllCompanies('biller');
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['tax_rates'] = $this->site->getAllTaxRates();
            //$this->data['currencies'] = $this->sales_model->getAllCurrencies();
            $this->data['slnumber'] = ''; //$this->site->getReference('so');
            $this->data['payment_ref'] = ''; //$this->site->getReference('pay');
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('sales'), 'page' => lang('Thêm thu hồi')), array('link' => '#', 'page' => lang('Thêm thu hồi')));
            $meta = array('page_title' => lang('Thêm thu hồi'), 'bc' => $bc);
            $this->page_construct('sales/thuhoi', $meta, $this->data);
        }
    }	
	public function editthuhoi($id = null)
    {
        $this->sma->checkPermissions();

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $inv = $this->sales_model->getInvoiceByID($id);
        if ($inv->sale_status == 'returned' || $inv->return_id || $inv->return_sale_ref) {
            $this->session->set_flashdata('error', lang('Đơn hàng có thu hồi sản phẩm không thể cập nhật, vui lòng hủy đơn hàng'));
            redirect(isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : 'welcome');
        }
		if($this->sales_model->checkCoSanPhamTraHangTrongHD($id)){
			$this->session->set_flashdata('error', lang('Đơn hàng có sản phẩm trả hàng không thể cập nhật, vui lòng hủy đơn hàng'));
            redirect(isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : 'welcome');
		}
        
    }
	 public function danhsachthuhoi($warehouse_id = null)
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

        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('Danh sách thu hồi')));
        $meta = array('page_title' => lang('Danh sách thu hồi'), 'bc' => $bc);
        $this->page_construct('sales/indexthuhoi', $meta, $this->data);
    }

    public function getThuHoi($warehouse_id = null)
    {
        $this->sma->checkPermissions('index');

        if ((!$this->Owner || !$this->Admin) && !$warehouse_id) {
            $user = $this->site->getUser();
            $warehouse_id = $user->warehouse_id;
        }
        $detail_link = anchor('sales/view/$1', '<i class="fa fa-file-text-o"></i> ' . lang('sale_details'));
        $duplicate_link = anchor('sales/add?sale_id=$1', '<i class="fa fa-plus-circle"></i> ' . lang('duplicate_sale'));
        $payments_link = anchor('sales/payments/$1', '<i class="fa fa-money"></i> ' . lang('view_payments'), 'data-toggle="modal" data-target="#myModal"');
        $add_payment_link = anchor('sales/add_payment/$1', '<i class="fa fa-money"></i> ' . lang('add_payment'), 'data-toggle="modal" data-target="#myModal"');
        $add_delivery_link = anchor('sales/add_delivery/$1', '<i class="fa fa-truck"></i> ' . lang('add_delivery'), 'data-toggle="modal" data-target="#myModal"');
        $email_link = anchor('sales/email/$1', '<i class="fa fa-envelope"></i> ' . lang('email_sale'), 'data-toggle="modal" data-target="#myModal"');
        $edit_link = anchor('sales/edit/$1', '<i class="fa fa-edit"></i> ' . lang('edit_sale'), 'class="sledit"');
        $pdf_link = anchor('sales/pdf/$1', '<i class="fa fa-file-pdf-o"></i> ' . lang('download_pdf'));
        $return_link = anchor('sales/return_sale/$1', '<i class="fa fa-angle-double-left"></i> ' . lang('return_sale'));		$print_link = anchor('sales/printhoadon/$1', '<i class="fa fa-print"></i> ' . lang('print_hoadon'), 'data-toggle="modal" data-target="#myModal"');
        $delete_link = "<a href='#' class='po' title='<b>" . lang("delete_sale") . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('sales/delete/$1') . "'>"
        . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
        . lang('delete_sale') . "</a>";
        $action = '<div class="text-center"><div class="btn-group text-left">'
        . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
        . lang('actions') . ' <span class="caret"></span></button>
        <ul class="dropdown-menu pull-right" role="menu">
            <li>' . $payments_link . '</li>
            <li>' . $pdf_link . '</li>
            <li>' . $email_link . '</li>	<li>' . $print_link . '</li>
            <li>' . $delete_link . '</li>
        </ul>
    </div></div>';
        //$action = '<div class="text-center">' . $detail_link . ' ' . $edit_link . ' ' . $email_link . ' ' . $delete_link . '</div>';

        $this->load->library('datatables');
        if ($warehouse_id) {
            $this->datatables
				->select("id, DATE_FORMAT(date, '%Y-%m-%d %T') as date, reference_no,(select name from scodeweb_doitac where id=doitac) as doitac,(select name from scodeweb_warehouses where id=warehouse_id) as kho, biller, concat(customer,'<br/>',(select phone from scodeweb_companies where id=customer_id)) as customer, sale_status, grand_total, paid, (grand_total-paid) as balance, payment_status, attachment, return_id")
				->from('sales')
				->where('warehouse_id', $warehouse_id);
        } else {
            $this->datatables
			->select("id, DATE_FORMAT(date, '%Y-%m-%d %T') as date, reference_no,(select name from scodeweb_warehouses where id=warehouse_id) as kho,(select name from scodeweb_doitac where id=doitac) as doitac, biller, concat(customer,'<br/>',(select phone from scodeweb_companies where id=customer_id)) as customer, sale_status, grand_total, paid, (grand_total-paid) as balance, payment_status, attachment, return_id")
			->from('sales');
        }
        $this->datatables->where('pos !=', 1);
		$this->datatables->where('sale_status =', 'returned');
		
        if (!$this->Customer && !$this->Supplier && !$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->datatables->where('created_by', $this->session->userdata('user_id'));
        } elseif ($this->Customer) {
            $this->datatables->where('customer_id', $this->session->userdata('user_id'));
        }
        $this->datatables->add_column("Actions", $action, "id");
        echo $this->datatables->generate();
    }
	public function printhoadon($id = null)
    {
        $this->sma->checkPermissions('index', true);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $inv = $this->sales_model->getInvoiceByID($id);
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
		
        $this->data['rows'] =$rows= $this->sales_model->getAllInvoiceItems($id);
		
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
		$parse_data = array('Ma_Don_Hang' => $inv->reference_no,'Khach_Hang' => $_tenkhach,'site_link' => base_url(),'site_name' => $this->Settings->site_name,'logo' => '<img src="' . base_url() . 'assets/uploads/logos/' . $biller->logo . '" alt="' . ($biller->company != '-' ? $biller->company : $biller->name) . '"/>','Ten_Kho' => $warehouse->name,'Dia_Chi_Kho' =>$_dc_cuahang,'SDT_Kho' =>$warehouse->phone,'Dia_chi_kh' => $customer->address,'Dien_thoai_kh' => $customer->phone,'Ngay_Thu' => $this->sma->hrld($inv->date),'Tong_Thanh_Toan' =>$tongcong,'Nhan_Vien_Thu_Ngan' =>$created_by->first_name . ' ' . $created_by->last_name,'CongTy_KH' =>$customer->company,'MST_KH' =>$customer->vat_no,'Ghi_Chu' =>$this->sma->decode_html($inv->note),'Chua_Thanh_Toan' => $conlai,'Da_Thanh_Toan' => $dathanhtoan,'Giam_Gia_Tren_Hoa_Don' =>$tonggiam,'Tong_Tien_Hang' =>$tongcong,'Tong_Tien_Bang_Chu' =>$_tongcong_bang_chu_text,'Bang_Hoa_Don' =>$_tablhd,'Ma_Doi_Tac' => $doitac->code,'Ten_Doi_Tac' => $doitac->name,'Dia_Chi_Doi_Tac' => $doitac->diachi,'Email_Doi_Tac' => $doitac->email,'Dien_Thoai_Doi_Tac' => $doitac->dienthoai,'No_Dau_Ky_Doi_Tac' => $doitac->nodauky);    
		
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
	public function web($warehouse_id = null)
    {
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
		$sysncart=$this->SysnApiWoooOrdersMsgV3();
		$this->session->set_flashdata('error',$sysncart);
		
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('Đơn hàng API Woocommerce Wordpress')));
        $meta = array('page_title' => lang('Đơn hàng API Woocommerce Wordpress'), 'bc' => $bc);
        $this->page_construct('sales/indexweb', $meta, $this->data);
    }

    public function getSalesWeb($warehouse_id = null)
    {
        
        if ((!$this->Owner || !$this->Admin) && !$warehouse_id) {
            $user = $this->site->getUser();
            $warehouse_id = $user->warehouse_id;
        }
        $detail_link = anchor('sales/view/$1', '<i class="fa fa-file-text-o"></i> ' . lang('sale_details'));
        $duplicate_link = anchor('sales/add?sale_id=$1', '<i class="fa fa-plus-circle"></i> ' . lang('duplicate_sale'));
        $payments_link = anchor('sales/payments/$1', '<i class="fa fa-money"></i> ' . lang('view_payments'), 'data-toggle="modal" data-target="#myModal"');
        $add_payment_link = anchor('sales/add_payment/$1', '<i class="fa fa-money"></i> ' . lang('add_payment'), 'data-toggle="modal" data-target="#myModal"');
        $add_delivery_link = anchor('sales/add_delivery/$1', '<i class="fa fa-truck"></i> ' . lang('add_delivery'), 'data-toggle="modal" data-target="#myModal"');
        $email_link = anchor('sales/email/$1', '<i class="fa fa-envelope"></i> ' . lang('email_sale'), 'data-toggle="modal" data-target="#myModal"');
        $edit_link = anchor('sales/edit/$1', '<i class="fa fa-edit"></i> ' . lang('edit_sale'), 'class="sledit"');
        $pdf_link = anchor('sales/pdf/$1', '<i class="fa fa-file-pdf-o"></i> ' . lang('download_pdf'));
        $return_link = anchor('sales/return_sale/$1', '<i class="fa fa-angle-double-left"></i> ' . lang('return_sale'));		$print_link = anchor('sales/printsalelhson/$1', '<i class="fa fa-print"></i> ' . lang('print_hoadon'), 'data-toggle="modal" data-target="#myModal"');
        $delete_link = "<a href='#' class='po' title='<b>" . lang("delete_sale") . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('sales/delete/$1') . "'>"
        . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
        . lang('delete_sale') . "</a>";
        $action = '<div class="text-center"><div class="btn-group text-left">'
        . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
        . lang('actions') . ' <span class="caret"></span></button>
        <ul class="dropdown-menu pull-right" role="menu">
            <li>' . $detail_link . '</li>
            <li>' . $duplicate_link . '</li>
            <li>' . $payments_link . '</li>
            <li>' . $add_payment_link . '</li>
            <li>' . $add_delivery_link . '</li>
            <li>' . $edit_link . '</li>
            <li>' . $pdf_link . '</li>
            <li>' . $email_link . '</li>
            <li>' . $return_link . '</li>			<li>' . $print_link . '</li>
            <li>' . $delete_link . '</li>
        </ul>
    </div></div>';
        //$action = '<div class="text-center">' . $detail_link . ' ' . $edit_link . ' ' . $email_link . ' ' . $delete_link . '</div>';

        $this->load->library('datatables');
       if ($warehouse_id) {
            $this->datatables
				->select("sales.id as id, DATE_FORMAT(scodeweb_sales.date, '%Y-%m-%d %T') as date, reference_no,doitac.name as doitac,warehouses.name as kho, biller, concat(customer,'<br/>',scodeweb_companies.name) as customer, sale_status, grand_total, paid, (grand_total-paid) as balance, payment_status, sales.attachment, return_id")
				->from('sales')
				->join('companies', 'companies.id=sales.customer_id', 'left')
				->join('doitac', 'doitac.id=sales.doitac', 'left')
				->join('warehouses', 'warehouses.id=sales.warehouse_id', 'left')
				->where('warehouse_id', $warehouse_id)->where('is_web>',0)->where('api_id',0);
        } else {
            $this->datatables
			->select("sales.id as id, DATE_FORMAT(scodeweb_sales.date, '%Y-%m-%d %T') as date, reference_no,doitac.name as doitac,warehouses.name as kho, biller, concat(customer,'<br/>',scodeweb_companies.name) as customer, sale_status, grand_total, paid, (grand_total-paid) as balance, payment_status, sales.attachment, return_id")
				->from('sales')
				->join('companies', 'companies.id=sales.customer_id', 'left')
				->join('doitac', 'doitac.id=sales.doitac', 'left')
				->join('warehouses', 'warehouses.id=sales.warehouse_id', 'left')
			->from('sales')->where('is_web>',0)->where('api_id',0);
        }
        $this->datatables->where('pos !=', 1); 
		$this->datatables->where('sale_status !=', 'returned');
        if (!$this->Customer && !$this->Supplier && !$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
           // $this->datatables->where('created_by', $this->session->userdata('user_id'));
           //hien thi tat ca don doi voi don web
        } elseif ($this->Customer) {
            $this->datatables->where('customer_id', $this->session->userdata('user_id'));
        }
        $this->datatables->add_column("Actions", $action, "id");
        echo $this->datatables->generate();
    }
    public function webtmdt($warehouse_id = null)
    {
        //$this->sma->checkPermissions();

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
        $sysncart=$this->SysnApiWoooOrdersMsgV3();
        $this->session->set_flashdata('error',$sysncart);
        
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('Đơn hàng sàn TMĐT')));
        $meta = array('page_title' => lang('Đơn hàng sàn TMĐT'), 'bc' => $bc);
        $this->page_construct('sales/indextmdt', $meta, $this->data);
    }

    public function getSalesTMDT($warehouse_id = null)
    {
        //$this->sma->checkPermissions('index');

        // if ((!$this->Owner || !$this->Admin) && !$warehouse_id) {
        //     $user = $this->site->getUser();
        //     $warehouse_id = $user->warehouse_id;
        // }
         if ($this->Admin||$this->Owner)
        {
            if (isset($warehouse_id)&&$warehouse_id!=null) 
            {
                $warehouse_id =$warehouse_id;                
            } else {
                $warehouse_id = NULL;
            }     
        }else{
            $warehouse_id = NULL;
        }

        $detail_link = anchor('sales/view/$1', '<i class="fa fa-file-text-o"></i> ' . lang('sale_details'));
        $duplicate_link = anchor('sales/add?sale_id=$1', '<i class="fa fa-plus-circle"></i> ' . lang('duplicate_sale'));
        $payments_link = anchor('sales/payments/$1', '<i class="fa fa-money"></i> ' . lang('view_payments'), 'data-toggle="modal" data-target="#myModal"');
        $add_payment_link = anchor('sales/add_payment/$1', '<i class="fa fa-money"></i> ' . lang('add_payment'), 'data-toggle="modal" data-target="#myModal"');
        $add_delivery_link = anchor('sales/add_delivery/$1', '<i class="fa fa-truck"></i> ' . lang('add_delivery'), 'data-toggle="modal" data-target="#myModal"');
        $email_link = anchor('sales/email/$1', '<i class="fa fa-envelope"></i> ' . lang('email_sale'), 'data-toggle="modal" data-target="#myModal"');
        $edit_link = anchor('sales/edit/$1', '<i class="fa fa-edit"></i> ' . lang('edit_sale'), 'class="sledit"');
        $pdf_link = anchor('sales/pdf/$1', '<i class="fa fa-file-pdf-o"></i> ' . lang('download_pdf'));
        $return_link = anchor('sales/return_sale/$1', '<i class="fa fa-angle-double-left"></i> ' . lang('return_sale'));        $print_link = anchor('sales/printsalelhson/$1', '<i class="fa fa-print"></i> ' . lang('print_hoadon'), 'data-toggle="modal" data-target="#myModal"');
        $delete_link = "<a href='#' class='po' title='<b>" . lang("delete_sale") . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('sales/delete/$1') . "'>"
        . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
        . lang('delete_sale') . "</a>";
        $action = '<div class="text-center"><div class="btn-group text-left">'
        . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
        . lang('actions') . ' <span class="caret"></span></button>
        <ul class="dropdown-menu pull-right" role="menu">
            <li>' . $detail_link . '</li>
            <li>' . $duplicate_link . '</li>
            <li>' . $payments_link . '</li>
            <li>' . $add_payment_link . '</li>
            <li>' . $add_delivery_link . '</li>
            <li>' . $edit_link . '</li>
            <li>' . $pdf_link . '</li>
            <li>' . $email_link . '</li>
            <li>' . $return_link . '</li>           <li>' . $print_link . '</li>
            <li>' . $delete_link . '</li>
        </ul>
    </div></div>';
        //$action = '<div class="text-center">' . $detail_link . ' ' . $edit_link . ' ' . $email_link . ' ' . $delete_link . '</div>';

        $this->load->library('datatables');
       if ($warehouse_id) {
            $this->datatables
                ->select("sales.id as id, DATE_FORMAT(scodeweb_sales.date, '%Y-%m-%d %T') as date, reference_no,doitac.name as doitac,warehouses.name as kho, biller, concat(customer,'<br/>',scodeweb_companies.name) as customer, sale_status, grand_total, paid, (grand_total-paid) as balance, payment_status, sales.attachment, return_id")
                ->from('sales')
                ->join('companies', 'companies.id=sales.customer_id', 'left')
                ->join('doitac', 'doitac.id=sales.doitac', 'left')
                ->join('warehouses', 'warehouses.id=sales.warehouse_id', 'left')
                ->where('warehouse_id', $warehouse_id)->where('api_id>',0)->where('is_web',0);
        } else {
            $this->datatables
            ->select("sales.id as id, DATE_FORMAT(scodeweb_sales.date, '%Y-%m-%d %T') as date, reference_no,doitac.name as doitac,warehouses.name as kho, biller, concat(customer,'<br/>',scodeweb_companies.name) as customer, sale_status, grand_total, paid, (grand_total-paid) as balance, payment_status, sales.attachment, return_id")
                ->from('sales')
                ->join('companies', 'companies.id=sales.customer_id', 'left')
                ->join('doitac', 'doitac.id=sales.doitac', 'left')
                ->join('warehouses', 'warehouses.id=sales.warehouse_id', 'left')
            ->from('sales')->where('api_id>',0)->where('is_web',0);
        }
        $this->datatables->where('pos !=', 1); 
        $this->datatables->where('sale_status !=', 'returned');

        if (!$this->Customer && !$this->Supplier && !$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            //$this->datatables->where('sales.created_by', $this->session->userdata('user_id'));
        } elseif ($this->Customer) {
            $this->datatables->where('sales.customer_id', $this->session->userdata('user_id'));
        }
        $this->datatables->add_column("Actions", $action, "id");
        echo $this->datatables->generate();
    }
	function CountApiWoooCart($woo_url='',$woo_key='',$woo_sec=''){			
		if($woo_url!=''&&$woo_key!=''&&$woo_sec!=''){
			try{
				$woocommerce = new Client($woo_url,$woo_key,$woo_sec,
					[
						'version' => 'wc/v3',
					]
				);
				
				$last_time=$this->sales_model->getLastTimeOrderWooApi();
				$ex=explode("+",$last_time);
				$last_time=$ex[0];
				$orders=$woocommerce->get('orders?after='.$last_time);
				
				$tong_don=count($orders);	
				$rs=0;	
				if($tong_don>0){
					foreach($orders as $cart){	
						//kienm tra xem woo_id co ton tai tren he thong hay chua
						if($this->sales_model->checkCartWooApi($cart->id)==0){	
							$rs++;
						}
					}
				}
				echo $rs;					
			}catch (Exception $e ) {

				return $e->getMessage();
			}
		}else{
			echo 0;
		}
	}
    function SysnApiWoooOrdersMsgV3(){

        $woo_url=json_decode($this->Settings->woo_url);
        $woo_key=json_decode($this->Settings->woo_key);
        $woo_sec=json_decode($this->Settings->woo_sec);
        $str='';
        foreach ($woo_url as $index=>$value) 
        {
            if ($value!="") {
                $this->SysnApiWoooOrdersMsg($value,$woo_key[$index],$woo_sec[$index]);
            }
        }
        //return $str;
    }
    function SysnApiWoooOrdersMsg($woo_url='',$woo_key='',$woo_sec=''){   

        if($woo_url!=''&&$woo_key!=''&&$woo_sec!=''){
            try{
                $woocommerce = new Client($woo_url,$woo_key,$woo_sec,
                    [
                        'version' => 'wc/v3',
                        'debug'           => true,
                        'return_as_array' => false,
                        'validate_url'    => false,
                        'timeout'         => 30,
                        'ssl_verify'      => false,
                    ]
                );
                                
                $error=array();
                $success=array();
                $last_time=$this->sales_model->getLastTimeOrderWooApi();
                $ex=explode("+",$last_time);
                $last_time=$ex[0];
                $orders=$woocommerce->get('orders?after='.$last_time);
                $tong_don=count($orders);           
                //tien hanh xu ly dong bo don hang 
                return "Có $tong_don đơn hàng web ".$woo_url." chưa xử lý";
            }catch (Exception $e ) {

                return $e->getMessage();
            }
        }
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
    public function getKhuyenmainewsByNow($warehouse_id = null)
    {       
        $add="";
        
        if ((!$this->Owner || !$this->Admin) && !$warehouse_id) {
            $user = $this->site->getUser(); 
            $warehouse_id = $user->warehouse_id;
            if((int)$warehouse_id>0){
                $add=" AND warehouse_id=".$warehouse_id;    
            }
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
        
        if ((!$this->Owner || !$this->Admin) && !$warehouse_id) {
            $user = $this->site->getUser(); 
            $warehouse_id = $user->warehouse_id;
            if((int)$warehouse_id>0){
                $add=" AND scodeweb_khuyenmai.warehouse_id=".$warehouse_id; 
            }
        }
        $today=date("Y-m-d H:i:s"); 
        $add=" AND (scodeweb_khuyenmai.startdate<'".$today."' AND scodeweb_khuyenmai.enddate>='".$today."')";   
        
        $query="SELECT scodeweb_khuyenmai.id,scodeweb_khuyenmai.tenevent,main_product_id,main_quantity,scodeweb_khuyenmai_items.product_id as sub_product_id,scodeweb_khuyenmai_items.giakhuyenmai as giakhuyenmai,scodeweb_khuyenmai_items.sub_quantity as sub_quantity FROM scodeweb_khuyenmai,scodeweb_khuyenmai_items WHERE scodeweb_khuyenmai_items.khuyenmai_id=scodeweb_khuyenmai.id AND scodeweb_khuyenmai.type=1 $add";    
        
        $q = $this->db->query($query,false);
        if ($q->num_rows() > 0) 
        { 
            foreach (($q->result()) as $row) {
                $data[$row->id][$row->sub_product_id] = $row;
            }
            return $data; 
        }
    }
     public function suggestionsById()
    {
        $term = $this->input->get('term', true);
        $quantity = $this->input->get('quantity', true);
        $giakhuyenmai = $this->input->get('price', true);
        $khuyenmai_main = $this->input->get('khuyenmai_main', true);

        if (strlen($term) < 1 || !$term) {
            die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . site_url('welcome') . "'; }, 10);</script>");
        }

        $option_id = null;

        $warehouse = $this->site->getWarehouseByID($warehouse_id);
        $customer = $this->site->getCompanyByID($customer_id);
        $customer_group = $this->site->getCustomerGroupByID($customer->customer_group_id);
        $rows = $this->sales_model->getProductNamesById($term);
        if ($rows) {
            $c = str_replace(".", "", microtime(true));
            $r = 0;
            foreach ($rows as $row) {
                unset($row->cost, $row->details, $row->product_details, $row->image, $row->barcode_symbology, $row->cf1, $row->cf2, $row->cf3, $row->cf4, $row->cf5, $row->cf6, $row->supplier1price, $row->supplier2price, $row->cfsupplier3price, $row->supplier4price, $row->supplier5price, $row->supplier1, $row->supplier2, $row->supplier3, $row->supplier4, $row->supplier5, $row->supplier1_part_no, $row->supplier2_part_no, $row->supplier3_part_no, $row->supplier4_part_no, $row->supplier5_part_no);
                $option = false;
                $row->quantity = $quantity;
                $row->item_tax_method = $row->tax_method;
                $row->qty = $quantity;
                $row->discount = '0';
                $row->serial = '';
                $options = $this->sales_model->getProductOptions($row->id, $warehouse_id);
                if ($options) {
                    $opt = $option_id && $r == 0 ? $this->sales_model->getProductOptionByID($option_id) : $options[0];
                    if (!$option_id || $r > 0) {
                        $option_id = $opt->id;
                    }
                } else {
                    $opt = json_decode('{}');
                    $opt->price = 0;
                    $option_id = FALSE;
                }
                $row->option = $option_id;
               
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
                
                $row->price = $giakhuyenmai;
                $row->real_unit_price = $giakhuyenmai;
                $row->base_quantity = 1;
                $row->base_unit = $row->unit;
                $row->base_unit_price = $giakhuyenmai;
                $row->unit = $row->sale_unit ? $row->sale_unit : $row->unit;
                $row->comment = '';
                $row->khuyenmai_main = $khuyenmai_main;
                $combo_items = false;
                if ($row->type == 'combo') {
                    $combo_items = $this->sales_model->getProductComboItems($row->id, $warehouse_id);
                }
                $units = $this->site->getUnitsByBUID($row->base_unit);
                $tax_rate = $this->site->getTaxRateByID($row->tax_rate);

                $pr = array('id' => ($c + $r), 'item_id' => $row->id, 'label' => '[KHUYẾN MÃI] '.$row->name . " (" . $row->code . ")", 'category' => $row->category_id, 
                    'row' => $row, 'combo_items' => $combo_items, 'tax_rate' => $tax_rate, 'units' => $units, 'options' => $options);
                $r++;
            }
            $this->sma->send_json($pr);
        } else {
            $this->sma->send_json(array(array('id' => 0, 'label' => lang('no_match_found'), 'value' => $term)));
        }
    }
    public function add_gift_card_by_ajax()
    {


            $customer_details = $this->input->post('customer_id') ? $this->site->getCompanyByID($this->input->post('customer_id')) : null;
            $customer = $customer_details ? $customer_details->name : $customer_details->company;
            
            $data = array('card_no' => $this->input->post('card_no'),
                'value' => $this->input->post('amount'),
                'customer_id' => $this->input->post('customer_id'),
                'customer' => $customer,
                'balance' => $this->input->post('amount'),
                'expiry' => date("Y-m-d",strtotime("+1 year")),
                'created_by' => $this->session->userdata('user_id'),'ca_points'=>$this->input->post('ca_points'));
            $sa_data = array();
            $ca_data = array();
        $check=false;
            if ($customer_details) {
                $ca_points = $this->input->post('ca_points');
                if ($customer_details->award_points < $ca_points) {
                    $this->session->set_flashdata('error', lang("award_points_wrong"));
                }else{
                    $check=true;
                }
                $ca_data = array('customer' => $this->input->post('customer_id'), 'points' => ($customer_details->award_points - $ca_points));
            }       

        if ($check&&$this->sales_model->addGiftCard($data, $ca_data, $sa_data)) {
            $this->sma->send_json(['OK']);
        }
    }

    public function modal_thuno($id = null)
    {
        $this->sma->checkPermissions('payments', true);
        $this->load->helper('security');
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $sale = $this->sales_model->getPaymentsForTraGop($id);
        if (empty($sale)) {
            $this->session->set_flashdata('error','Empty');
            redirect($_SERVER["HTTP_REFERER"]);
        }
        $this->form_validation->set_rules('amount-paid', lang("amount"), 'required');
        $sotienmoi=(float)$this->input->post('amount-paid');
        
        $sotienmoi=(float)$sale['sotien_tragop']+$sotienmoi;

        if ($this->form_validation->run() == true) {
            if ($this->Owner || $this->Admin) {
                $date = $this->sma->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }

            $payment = array(
                    'sotien_tragop' => $sotienmoi,
                    'note_last' => $this->input->post('note'),
                    'thu_by' => $this->session->userdata('user_id'),
                    'thu_at' => date("Y-m-d H:i:s"),
                                );
            $payment_tragop = array(    
                    'date'=>$date,
                    'warehouse_id' => $sale['warehouse_id'],                
                    'payment_id' => $sale['id'],
                    'amount' => (float)$this->input->post('amount-paid'),
                    'note' => $this->input->post('note'),
                    'created_by' => $this->session->userdata('user_id'),
                    'created' => date("Y-m-d H:i:s"));

           // $this->sma->print_arrays($payment,$payment_tragop);

        } elseif ($this->input->post('add_payment')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }

        if ($this->form_validation->run() == true && $this->sales_model->updateTraGop($id,$payment,$payment_tragop)) {
            $this->session->set_flashdata('message', lang("Thêm thanh toán thành công"));
            redirect($_SERVER["HTTP_REFERER"]);
        } else {

            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            
            $this->data['inv'] = $sale;
            $this->data['list_tragop'] = $this->sales_model->ListTraGop($sale['id']);
            //get customer info by purchase_id
            $this->data['modal_js'] = $this->site->modal_js();

            $this->load->view($this->theme . 'sales/payment_tragop', $this->data);
        }
    }
    function delete_payment_tragop($payment_id='')
    {
        $this->sma->checkPermissions('payments', true);
        $this->load->helper('security');
        if ($this->input->get('id')) {
            $payment_id = $this->input->get('id');
        }
        $tragop = $this->sales_model->getTraGopById($payment_id);
        if (empty($tragop)) {
            $this->session->set_flashdata('error','Empty');
            redirect($_SERVER["HTTP_REFERER"]);
        }else{
            //tien hanh xoa va update
            $amount=(float)$tragop['amount'];
            $sale = $this->sales_model->getPaymentsForTraGop($tragop['payment_id']);
            if (!empty($sale)) {
                $sotienmoi=$sale['sotien_tragop']-$amount;

                $payment = array(
                    'sotien_tragop' => $sotienmoi,                    
                    'thu_by' => $this->session->userdata('user_id'),
                    'thu_at' => date("Y-m-d H:i:s"));
                if ($this->sales_model->XoaTraGop($id,$payment,$tragop['id'],$sale['id'])) {
                    $this->session->set_flashdata('message', lang("Xóa thành công"));
                    redirect($_SERVER["HTTP_REFERER"]);
                }
            }
        }
        redirect($_SERVER["HTTP_REFERER"]);
    }
}
