<?php defined('BASEPATH') or exit('No direct script access allowed');

class Quotes extends MY_Controller
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
        $this->load->model('quotes_model');
        $this->load->model('settings_model');

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

        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('quotes')));
        $meta = array('page_title' => lang('quotes'), 'bc' => $bc);
        $this->page_construct('quotes/index', $meta, $this->data);
    }

    public function getQuotes($warehouse_id = null)
    {
        $this->sma->checkPermissions('index');

        if ((!$this->Owner || !$this->Admin) && !$warehouse_id) {
            $user = $this->site->getUser();
            $warehouse_id = $user->warehouse_id;
        }
        // $print_link = anchor('quotes/printbaogia/$1', '<i class="fa fa-print"></i> ' . lang('In báo giá'), ' class="tip" data-toggle="modal" data-target="#myModal"');
        $detail_link = anchor('quotes/view/$1', '<i class="fa fa-file-text-o"></i> ' . lang('quote_details'));
        $email_link = anchor('quotes/email/$1', '<i class="fa fa-envelope"></i> ' . lang('email_quote'), 'data-toggle="modal" data-target="#myModal"');
        $edit_link = anchor('quotes/edit/$1', '<i class="fa fa-edit"></i> ' . lang('edit_quote'));
        $convert_link = anchor('sales/add/$1', '<i class="fa fa-heart"></i> ' . lang('create_sale'));
        $pc_link = anchor('purchases/add/$1', '<i class="fa fa-star"></i> ' . lang('create_purchase'));
        $pdf_link = anchor('quotes/pdf/$1', '<i class="fa fa-file-pdf-o"></i> ' . lang('download_pdf'));
        $delete_link = "<a href='#' class='po' title='<b>" . $this->lang->line("delete_quote") . "</b>' data-content=\"<p>"
            . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('quotes/delete/$1') . "'>"
            . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
            . lang('delete_quote') . "</a>";
        $action = '<div class="text-center"><div class="btn-group text-left">'
            . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
            . lang('actions') . ' <span class="caret"></span></button>
                    <ul class="dropdown-menu pull-right" role="menu">
                        <li>' . $detail_link . '</li>
                        <li>' . $edit_link . '</li>
                        <li>' . $convert_link . '</li>
			
                        <li>' . $pc_link . '</li>
                        <li>' . $pdf_link . '</li>
                        <li>' . $email_link . '</li>
                        <li>' . $delete_link . '</li>
                    </ul>
                </div></div>';
        //$action = '<div class="text-center">' . $detail_link . ' ' . $edit_link . ' ' . $email_link . ' ' . $delete_link . '</div>';

        $this->load->library('datatables');
        if ($warehouse_id) {
            $this->datatables
                ->select("id, date, reference_no, biller, customer, supplier, grand_total, status, attachment")
                ->from('quotes')
                ->where('warehouse_id', $warehouse_id);
        } else {
            $this->datatables
                ->select("id, date, reference_no, biller, customer, supplier, grand_total, status, attachment")
                ->from('quotes');
        }
        if (!$this->Customer && !$this->Supplier && !$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->datatables->where('created_by', $this->session->userdata('user_id'));
        } elseif ($this->Customer) {
            $this->datatables->where('customer_id', $this->session->userdata('user_id'));
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
    $inv = $this->quotes_model->getQuoteByID($quote_id);
    $inv->delivery_date = $inv->shipping_date;              // Ngày giao chành
    $inv->received_date = $inv->expected_delivery_date;     // Ngày khách nhận dự kiến
    $inv->install_date = $inv->expected_installation_date;
    $inv->shipping_info = $inv->shipping_info;              // Thông tin chành xe
    
    if (!$this->session->userdata('view_right')) {
        $this->sma->view_rights($inv->created_by, true);
    }
    $raw_rows = $this->quotes_model->getAllQuoteItems($quote_id);
    
    // ✅ CODE MỚI - NHÓM SẢN PHẨM CÓ group_id, SỰ DỰ LÀ SẢN PHẨM RIÊNG LẺ
    $grouped_rows = array();
    $current_main_index = -1;

    foreach ($raw_rows as $row) {
        // Nếu là sản phẩm chính (group_id = 30, 0, NULL)
        if ($row->group_id == 30 || $row->group_id == 0 || $row->group_id === NULL) {
            // Tạo nhóm mới cho sản phẩm chính
            $grouped_rows[] = array(
                'main' => $row,
                'color' => null,
                'lock' => null,
                'image' => $row->image
            );
            $current_main_index++;
        } 
        // Nếu là màu (group_id = 31)
        elseif ($row->group_id == 31 && $current_main_index >= 0) {
            $grouped_rows[$current_main_index]['color'] = $row;
        } 
        // Nếu là khóa (group_id = 32)
        elseif ($row->group_id == 32 && $current_main_index >= 0) {
            $grouped_rows[$current_main_index]['lock'] = $row;
        } 
        // ✅ THÊM: Nếu group_id không được nhận diện, coi nó là sản phẩm riêng lẻ
        else {
            $grouped_rows[] = array(
                'main' => $row,
                'color' => null,
                'lock' => null,
                'image' => $row->image
            );
            $current_main_index++;
        }
    }
    
    $this->data['rows'] = $grouped_rows;
    $this->data['custom_columns'] = $this->quotes_model->getCustomColumns();
    $this->data['customer'] = $this->site->getCompanyByID($inv->customer_id);
    $this->data['biller'] = $this->site->getCompanyByID($inv->biller_id);
    $this->data['created_by'] = $this->site->getUser($inv->created_by);
    $this->data['updated_by'] = $inv->updated_by ? $this->site->getUser($inv->updated_by) : null;
    $this->data['warehouse'] = $this->site->getWarehouseByID($inv->warehouse_id);
    $this->data['inv'] = $inv;

    $this->load->view($this->theme . 'quotes/modal_view', $this->data);
}

    public function view($quote_id = null)
{
    $this->sma->checkPermissions('index');

    if ($this->input->get('id')) {
        $quote_id = $this->input->get('id');
    }
    $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
    $inv = $this->quotes_model->getQuoteByID($quote_id);
    if (!$this->session->userdata('view_right')) {
        $this->sma->view_rights($inv->created_by);
    }
    $raw_rows = $this->quotes_model->getAllQuoteItems($quote_id);
    
    // ✅ CODE MỚI - NHÓM SẢN PHẨM CÓ group_id, SỰ DỰ LÀ SẢN PHẨM RIÊNG LẺ
    $grouped_rows = array();
    $current_main_index = -1;

    foreach ($raw_rows as $row) {
        if ($row->group_id == 30 || $row->group_id == 0 || $row->group_id === NULL) {
            $grouped_rows[] = array(
                'main' => $row,
                'color' => null,
                'lock' => null,
                'image' => $row->image
            );
            $current_main_index++;
        } 
        elseif ($row->group_id == 31 && $current_main_index >= 0) {
            $grouped_rows[$current_main_index]['color'] = $row;
        } 
        elseif ($row->group_id == 32 && $current_main_index >= 0) {
            $grouped_rows[$current_main_index]['lock'] = $row;
        } 
        // ✅ THÊM: Nếu group_id không được nhận diện, coi nó là sản phẩm riêng lẻ
        else {
            $grouped_rows[] = array(
                'main' => $row,
                'color' => null,
                'lock' => null,
                'image' => $row->image
            );
            $current_main_index++;
        }
    }

    $this->data['rows'] = $grouped_rows;
    $this->data['custom_columns'] = $this->quotes_model->getCustomColumns();
    $this->data['customer'] = $this->site->getCompanyByID($inv->customer_id);
    $this->data['biller'] = $this->site->getCompanyByID($inv->biller_id);
    $this->data['created_by'] = $this->site->getUser($inv->created_by);
    $this->data['updated_by'] = $inv->updated_by ? $this->site->getUser($inv->updated_by) : null;
    $this->data['warehouse'] = $this->site->getWarehouseByID($inv->warehouse_id);
    $this->data['inv'] = $inv;

    $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('quotes'), 'page' => lang('quotes')), array('link' => '#', 'page' => lang('view')));
    $meta = array('page_title' => lang('view_quote_details'), 'bc' => $bc);
    $this->page_construct('quotes/view', $meta, $this->data);
}

    // ============ FILE: Quotes.php (Controller) ============
// TÌM HÀM pdf() VÀ THAY TOÀN BỘ ĐOẠN XỬ LÝ NHÓM SẢN PHẨM:

public function pdf($quote_id = null, $view = null, $save_bufffer = null)
{
    $this->sma->checkPermissions();

    if ($this->input->get('id')) {
        $quote_id = $this->input->get('id');
    }
    $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
    $inv = $this->quotes_model->getQuoteByID($quote_id);
    if (!$this->session->userdata('view_right')) {
        $this->sma->view_rights($inv->created_by);
    }
    $raw_rows = $this->quotes_model->getAllQuoteItems($quote_id);

    // ✅ CODE MỚI - NHÓM SẢN PHẨM CÓ group_id, SỰ DỰ LÀ SẢN PHẨM RIÊNG LẺ
    $grouped_rows = array();
    $current_main_index = -1;

    foreach ($raw_rows as $row) {
        // Nếu là sản phẩm chính (group_id = 30, 0, NULL)
        if ($row->group_id == 30 || $row->group_id == 0 || $row->group_id === NULL) {
            // Tạo nhóm mới cho sản phẩm chính
            $grouped_rows[] = array(
                'main' => $row,
                'color' => null,
                'lock' => null,
                'image' => $row->image
            );
            $current_main_index++;
        } 
        // Nếu là màu (group_id = 31)
        elseif ($row->group_id == 31 && $current_main_index >= 0) {
            $grouped_rows[$current_main_index]['color'] = $row;
        } 
        // Nếu là khóa (group_id = 32)
        elseif ($row->group_id == 32 && $current_main_index >= 0) {
            $grouped_rows[$current_main_index]['lock'] = $row;
        } 
        // ✅ THÊM: Nếu group_id không được nhận diện, coi nó là sản phẩm riêng lẻ
        else {
            $grouped_rows[] = array(
                'main' => $row,
                'color' => null,
                'lock' => null,
                'image' => $row->image
            );
            $current_main_index++;
        }
    }

    $this->data['rows'] = $grouped_rows;
    $this->data['custom_columns'] = $this->quotes_model->getCustomColumns();
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
        $this->sma->generate_pdf($html, $name, 'I');
    } elseif ($save_bufffer) {
        return $this->sma->generate_pdf($html, $name, $save_bufffer);
    } else {
        $this->sma->generate_pdf($html, $name);
    }
}

    public function combine_pdf($quotes_id)
    {
        $this->sma->checkPermissions('pdf');

        foreach ($quotes_id as $quote_id) {

            $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
            $inv = $this->quotes_model->getQuoteByID($quote_id);
            if (!$this->session->userdata('view_right')) {
                $this->sma->view_rights($inv->created_by);
            }
            $this->data['rows'] = $this->quotes_model->getAllQuoteItems($quote_id);
            $this->data['customer'] = $this->site->getCompanyByID($inv->customer_id);
            $this->data['biller'] = $this->site->getCompanyByID($inv->biller_id);
            $this->data['user'] = $this->site->getUser($inv->created_by);
            $this->data['warehouse'] = $this->site->getWarehouseByID($inv->warehouse_id);
            $this->data['inv'] = $inv;

            $html[] = array(
                'content' => $this->load->view($this->theme . 'quotes/pdf', $this->data, true),
                'footer' => '',
            );
        }

        $name = lang("quotes") . ".pdf";
        $this->sma->generate_pdf($html, $name);
    }

    public function email($quote_id = null)
    {
        $this->sma->checkPermissions(false, true);

        if ($this->input->get('id')) {
            $quote_id = $this->input->get('id');
        }
        $inv = $this->quotes_model->getQuoteByID($quote_id);
        $inv->delivery_date = $inv->shipping_date;              // Ngày giao chành
$inv->received_date = $inv->expected_delivery_date;     // Ngày khách nhận dự kiến
$inv->install_date = $inv->expected_installation_date;  // Ngày lắp đặt dự kiến
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
            $this->session->set_flashdata('message', $this->lang->line("email_sent OK"));
            redirect("quotes");
        } else {

            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

            if (file_exists('./themes/' . $this->theme . '/views/email_templates/quote.html')) {
                $quote_temp = file_get_contents('themes/' . $this->theme . '/views/email_templates/quote.html');
            } else {
                $quote_temp = file_get_contents('./themes/default/views/email_templates/quote.html');
            }

            $this->data['subject'] = array(
                'name' => 'subject',
                'id' => 'subject',
                'type' => 'text',
                'value' => $this->form_validation->set_value('subject', lang('quote') . ' (' . $inv->reference_no . ') ' . lang('from') . ' ' . $this->Settings->site_name),
            );
            $this->data['note'] = array(
                'name' => 'note',
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

        $this->form_validation->set_message('is_natural_no_zero', $this->lang->line("no_zero_required"));
        $this->form_validation->set_rules('customer', $this->lang->line("customer"), 'required');

        if ($this->form_validation->run() == true) {

            $reference = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('qu');
            if ($this->Owner || $this->Admin) {
                $date = $this->sma->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }
            $warehouse_id = $this->input->post('warehouse');
            $customer_id = $this->input->post('customer');
            $biller_id = $this->input->post('biller');
            $supplier_id = $this->input->post('supplier');
            $status = $this->input->post('status');
            $shipping = $this->input->post('shipping') ? $this->input->post('shipping') : 0;
            $customer_details = $this->site->getCompanyByID($customer_id);
            $customer = $customer_details->name;
            $biller_details = $this->site->getCompanyByID($biller_id);
            $biller = $biller_details->company != '-' ? $biller_details->company : $biller_details->name;
            if ($supplier_id) {
                $supplier_details = $this->site->getCompanyByID($supplier_id);
                $supplier = $supplier_details->company != '-' ? $supplier_details->company : $supplier_details->name;
            } else {
                $supplier = NULL;
            }
            $note = $this->sma->clear_tags($this->input->post('note'));
            $custom_columns = $this->input->post('custom_columns');
            if (!empty($custom_columns)) {
                $this->quotes_model->saveCustomColumns($custom_columns);
            }
            $total = 0;
            $product_tax = 0;
            $order_tax = 0;
            $product_discount = 0;
            $order_discount = 0;
            $percentage = '%';
            $i = isset($_POST['product_code']) ? sizeof($_POST['product_code']) : 0;

            $products = array(); // KHỞI TẠO MẢNG PRODUCTS

            for ($r = 0; $r < $i; $r++) {
                $item_id = $_POST['product_id'][$r];
                $item_type = $_POST['product_type'][$r];
                $item_code = $_POST['product_code'][$r];
                $item_name = $_POST['product_name'][$r];
                $item_option = isset($_POST['product_option'][$r]) && $_POST['product_option'][$r] != 'false' ? $_POST['product_option'][$r] : null;
                $real_unit_price = $this->sma->formatDecimal($_POST['real_unit_price'][$r]);
                $unit_price = $this->sma->formatDecimal($_POST['unit_price'][$r]);
                $item_unit_quantity = $_POST['quantity'][$r];
                $item_tax_rate = isset($_POST['product_tax'][$r]) ? $_POST['product_tax'][$r] : null;
                $item_discount = isset($_POST['product_discount'][$r]) ? $_POST['product_discount'][$r] : null;
                $item_unit = $_POST['product_unit'][$r];
                $item_quantity = $_POST['product_base_quantity'][$r];

                if (isset($item_code) && isset($real_unit_price) && isset($unit_price) && isset($item_quantity)) {
                    $product_details = $item_type != 'manual' ? $this->quotes_model->getProductByCode($item_code) : null;
                    // $unit_price = $real_unit_price;
                    $pr_discount = 0;

                    if (isset($item_discount)) {
                        $discount = $item_discount;
                        $dpos = strpos($discount, $percentage);
                        if ($dpos !== false) {
                            $pds = explode("%", $discount);
                            $pr_discount = $this->sma->formatDecimal(((($this->sma->formatDecimal($unit_price)) * (float) ($pds[0])) / 100), 4);
                        } else {
                            $pr_discount = $this->sma->formatDecimal($discount);
                        }
                    }

                    $unit_price = $this->sma->formatDecimal($unit_price - $pr_discount);
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
                    $subtotal = (($item_net_price * $item_unit_quantity) + $pr_item_tax);
                    $unit = $this->site->getUnitByID($item_unit);

                    // ===== PHẦN NÀY LÀ CUSTOM FIELDS - THÊM ĐỒN NÀY =====
                    $custom_fields_data = array();
        
        // ✅ Lấy danh sách columns từ database
        $existing_columns = $this->quotes_model->getCustomColumns();
        if ($existing_columns) {
            foreach ($existing_columns as $col) {
                $col_name = $col->column_name;
                $field_name = 'custom_' . str_replace(' ', '_', $col_name);
                
                $field_value = '';
                if (isset($_POST[$field_name]) && is_array($_POST[$field_name])) {
                    $field_value = isset($_POST[$field_name][$r]) ? $this->sma->clear_tags($_POST[$field_name][$r]) : '';
                }
                
                $custom_fields_data[$col_name] = $field_value;
            }
        }
                    // ===== KẾT THÚC PHẦN CUSTOM FIELDS =====

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
                        'real_unit_price' => $real_unit_price,
                        'custom_fields' => $custom_fields_data,  // THÊM DÒNG NÀY
                        'notes' => isset($_POST['notes'][$r]) ? $this->sma->clear_tags($_POST['notes'][$r]) : '' // ← THÊM DÒNG NÀY
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
                $order_discount_id = $this->input->post('discount');
                $opos = strpos($order_discount_id, $percentage);
                if ($opos !== false) {
                    $ods = explode("%", $order_discount_id);
                    $order_discount = $this->sma->formatDecimal(((($total + $product_tax) * (float) ($ods[0])) / 100), 4);
                } else {
                    $order_discount = $this->sma->formatDecimal($order_discount_id);
                }
            } else {
                $order_discount_id = null;
            }
            $total_discount = $order_discount + $product_discount;

            if ($this->Settings->tax2 != 0) {
                $order_tax_id = $this->input->post('order_tax');
                if ($order_tax_details = $this->site->getTaxRateByID($order_tax_id)) {
                    if ($order_tax_details->type == 2) {
                        $order_tax = $order_tax_details->rate;
                    }
                    if ($order_tax_details->type == 1) {
                        $order_tax = (($total + $product_tax - $order_discount) * $order_tax_details->rate) / 100;
                    }
                }
            } else {
                $order_tax_id = null;
            }

            $total_tax = $this->sma->formatDecimal(($product_tax + $order_tax), 4);
            $grand_total = $this->sma->formatDecimal(($total + $total_tax + $this->sma->formatDecimal($shipping) - $order_discount), 4);
            $data = array(
                'date' => $date,
                'reference_no' => $reference,
                'shipping_date' => $this->input->post('shipping_date') ? $this->sma->fld($this->input->post('shipping_date')) : NULL,
    'expected_delivery_date' => $this->input->post('expected_delivery_date') ? $this->sma->fld($this->input->post('expected_delivery_date')) : NULL,
    'expected_installation_date' => $this->input->post('expected_installation_date') ? $this->sma->fld($this->input->post('expected_installation_date')) : NULL,
    'shipping_info' => $this->input->post('shipping_info') ? $this->sma->clear_tags($this->input->post('shipping_info')) : NULL,
    'construction_address' => $this->input->post('construction_address') ? $this->sma->clear_tags($this->input->post('construction_address')) : NULL,
    'deposit_amount' => $this->input->post('deposit_amount') ? $this->sma->formatDecimal($this->input->post('deposit_amount')) : 0,
                'customer_id' => $customer_id,
                'customer' => $customer,
                'biller_id' => $biller_id,
                'biller' => $biller,
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
        }

        if ($this->form_validation->run() == true && ($quote_id = $this->quotes_model->addQuote($data, $products))) {
            $this->session->set_userdata('remove_quls', 1);
            $this->session->set_flashdata('message', $this->lang->line("quote_added"));
            if ($this->input->post('add_quote_view')) {
                redirect('quotes/view/' . $quote_id);
            }
            redirect('quotes');
        } else {

            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

            $this->data['billers'] = ($this->Owner || $this->Admin || !$this->session->userdata('biller_id')) ? $this->site->getAllCompanies('biller') : null;
            //$this->data['currencies'] = $this->site->getAllCurrencies();
            $this->data['tax_rates'] = $this->site->getAllTaxRates();
            $this->data['warehouses'] = ($this->Owner || $this->Admin || !$this->session->userdata('biller_id')) ? $this->site->getAllWarehouses() : null;
            $this->data['qunumber'] = ''; //$this->site->getReference('qu');
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('quotes'), 'page' => lang('quotes')), array('link' => '#', 'page' => lang('add_quote')));
            $meta = array('page_title' => lang('add_quote'), 'bc' => $bc);
            $this->page_construct('quotes/add', $meta, $this->data);
        }
    }

    public function edit($id = null)
    {
        $this->sma->checkPermissions();

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $inv = $this->quotes_model->getQuoteByID($id);
        if (!$this->session->userdata('edit_right')) {
            $this->sma->view_rights($inv->created_by);
        }
        $this->form_validation->set_message('is_natural_no_zero', $this->lang->line("no_zero_required"));
        $this->form_validation->set_rules('reference_no', $this->lang->line("reference_no"), 'required');
        $this->form_validation->set_rules('customer', $this->lang->line("customer"), 'required');
        //$this->form_validation->set_rules('note', $this->lang->line("note"), 'xss_clean');

        if ($this->form_validation->run() == true) {
            $quantity = "quantity";
            $product = "product";
            $unit_cost = "unit_cost";
            $tax_rate = "tax_rate";
            $reference = $this->input->post('reference_no');
            if ($this->Owner || $this->Admin) {
                $date = $this->sma->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }
            $warehouse_id = $this->input->post('warehouse');
            $customer_id = $this->input->post('customer');
            $biller_id = $this->input->post('biller');
            $supplier_id = $this->input->post('supplier');
            $status = $this->input->post('status');
            $shipping = $this->input->post('shipping') ? $this->input->post('shipping') : 0;
            $customer_details = $this->site->getCompanyByID($customer_id);
            $customer =  $customer_details->name;
            $biller_details = $this->site->getCompanyByID($biller_id);
            $biller = $biller_details->company != '-' ? $biller_details->company : $biller_details->name;
            if ($supplier_id) {
                $supplier_details = $this->site->getCompanyByID($supplier_id);
                $supplier = $supplier_details->company != '-' ? $supplier_details->company : $supplier_details->name;
            } else {
                $supplier = NULL;
            }
            $note = $this->sma->clear_tags($this->input->post('note'));
            $custom_columns = $this->input->post('custom_columns');
            if (!empty($custom_columns)) {
                $this->quotes_model->saveCustomColumns($custom_columns);
            }
            $total = 0;
            $product_tax = 0;
            $order_tax = 0;
            $product_discount = 0;
            $order_discount = 0;
            $percentage = '%';
            $i = isset($_POST['product_code']) ? sizeof($_POST['product_code']) : 0;

            $products = array(); // KHỞI TẠO MẢNG PRODUCTS

            for ($r = 0; $r < $i; $r++) {
                $item_id = $_POST['product_id'][$r];
                $item_type = $_POST['product_type'][$r];
                $item_code = $_POST['product_code'][$r];
                $item_name = $_POST['product_name'][$r];
                $item_option = isset($_POST['product_option'][$r]) && $_POST['product_option'][$r] != 'false' ? $_POST['product_option'][$r] : null;
                $real_unit_price = $this->sma->formatDecimal($_POST['real_unit_price'][$r]);
                $unit_price = $this->sma->formatDecimal($_POST['unit_price'][$r]);
                $item_unit_quantity = $_POST['quantity'][$r];
                $item_tax_rate = isset($_POST['product_tax'][$r]) ? $_POST['product_tax'][$r] : null;
                $item_discount = isset($_POST['product_discount'][$r]) ? $_POST['product_discount'][$r] : null;
                $item_unit = $_POST['product_unit'][$r];
                $item_quantity = $_POST['product_base_quantity'][$r];

                if (isset($item_code) && isset($real_unit_price) && isset($unit_price) && isset($item_quantity)) {
                    $product_details = $item_type != 'manual' ? $this->quotes_model->getProductByCode($item_code) : null;
                    // $unit_price = $real_unit_price;
                    $pr_discount = 0;

                    if (isset($item_discount)) {
                        $discount = $item_discount;
                        $dpos = strpos($discount, $percentage);
                        if ($dpos !== false) {
                            $pds = explode("%", $discount);
                            $pr_discount = $this->sma->formatDecimal(((($this->sma->formatDecimal($unit_price)) * (float) ($pds[0])) / 100), 4);
                        } else {
                            $pr_discount = $this->sma->formatDecimal($discount);
                        }
                    }

                    $unit_price = $this->sma->formatDecimal($unit_price - $pr_discount);
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
                    $subtotal = (($item_net_price * $item_unit_quantity) + $pr_item_tax);
                    $unit = $this->site->getUnitByID($item_unit);

                    // ===== PHẦN NÀY LÀ CUSTOM FIELDS - THÊM ĐỒN NÀY =====
                    $custom_fields_data = array();
    
    // ✅ Lấy danh sách columns từ database
    $existing_columns = $this->quotes_model->getCustomColumns();
    if ($existing_columns) {
        foreach ($existing_columns as $col) {
            $col_name = $col->column_name; // "Rộng", "Dài", "Tường", "Tầng", "Hướng mở"
            $field_name = 'custom_' . str_replace(' ', '_', $col_name); // "custom_Rộng", "custom_Hướng_mở"
            
            // ✅ Lấy giá trị từ $_POST
            $field_value = '';
            if (isset($_POST[$field_name]) && is_array($_POST[$field_name])) {
                $field_value = isset($_POST[$field_name][$r]) ? $this->sma->clear_tags($_POST[$field_name][$r]) : '';
            }
            
            // ✅ Lưu theo tên cột gốc (không có prefix custom_)
            $custom_fields_data[$col_name] = $field_value;
        }
    }
                    // ===== KẾT THÚC PHẦN CUSTOM FIELDS =====

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
                        'real_unit_price' => $real_unit_price,
                        'custom_fields' => $custom_fields_data, // THÊM DÒNG NÀY
                        'notes' => isset($_POST['notes'][$r]) ? $this->sma->clear_tags($_POST['notes'][$r]) : '' // ← THÊM DÒNG NÀY
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
                $order_discount_id = $this->input->post('discount');
                $opos = strpos($order_discount_id, $percentage);
                if ($opos !== false) {
                    $ods = explode("%", $order_discount_id);
                    $order_discount = $this->sma->formatDecimal(((($total + $product_tax) * (float) ($ods[0])) / 100), 4);
                } else {
                    $order_discount = $this->sma->formatDecimal($order_discount_id);
                }
            } else {
                $order_discount_id = null;
            }
            $total_discount = $order_discount + $product_discount;

            if ($this->Settings->tax2 != 0) {
                $order_tax_id = $this->input->post('order_tax');
                if ($order_tax_details = $this->site->getTaxRateByID($order_tax_id)) {
                    if ($order_tax_details->type == 2) {
                        $order_tax = $order_tax_details->rate;
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
            $data = array(
                'date' => $date,
                'reference_no' => $reference,
                'shipping_date' => $this->input->post('shipping_date') ? $this->sma->fld($this->input->post('shipping_date')) : NULL,
    'expected_delivery_date' => $this->input->post('expected_delivery_date') ? $this->sma->fld($this->input->post('expected_delivery_date')) : NULL,
    'expected_installation_date' => $this->input->post('expected_installation_date') ? $this->sma->fld($this->input->post('expected_installation_date')) : NULL,
    'shipping_info' => $this->input->post('shipping_info') ? $this->sma->clear_tags($this->input->post('shipping_info')) : NULL,
    'construction_address' => $this->input->post('construction_address') ? $this->sma->clear_tags($this->input->post('construction_address')) : NULL,
    'deposit_amount' => $this->input->post('deposit_amount') ? $this->sma->formatDecimal($this->input->post('deposit_amount')) : 0,
                'customer_id' => $customer_id,
                'customer' => $customer,
                'biller_id' => $biller_id,
                'biller' => $biller,
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
                'shipping' => $shipping,
                'grand_total' => $grand_total,
                'status' => $status,
                'updated_by' => $this->session->userdata('user_id'),
                'updated_at' => date('Y-m-d H:i:s'),
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

        if ($this->form_validation->run() == true && $this->quotes_model->updateQuote($id, $data, $products)) {
            $this->session->set_userdata('remove_quls', 1);
            $this->session->set_flashdata('message', $this->lang->line("quote_added"));
            redirect('quotes');
        } else {

            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

            // ✅ THAY THẾ TOÀN BỘ ĐOẠN NÀY
            $this->data['inv'] = $this->quotes_model->getQuoteByID($id);
            $inv_items = $this->quotes_model->getAllQuoteItems($id);
            $this->data['inv']->shipping_date = $this->data['inv']->shipping_date ? date('Y-m-d', strtotime($this->data['inv']->shipping_date)) : '';
$this->data['inv']->expected_delivery_date = $this->data['inv']->expected_delivery_date ? date('Y-m-d', strtotime($this->data['inv']->expected_delivery_date)) : '';
$this->data['inv']->expected_installation_date = $this->data['inv']->expected_installation_date ? date('Y-m-d', strtotime($this->data['inv']->expected_installation_date)) : '';
            $this->data['custom_columns'] = $this->quotes_model->getCustomColumns();
            krsort($inv_items);

            $c = rand(100000, 9999999);
            $pr = array(); // ← KHỞI TẠO ARRAY

            foreach ($inv_items as $item) {
                $row = $this->site->getProductByID($item->product_id);
                if (!$row) {
                    $row = json_decode('{}');
                    $row->tax_method = 0;
                } else {
                    unset(
                        $row->details,
                        $row->product_details,
                        $row->cost,
                        $row->supplier1price,
                        $row->supplier2price,
                        $row->supplier3price,
                        $row->supplier4price,
                        $row->supplier5price
                    );
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
                $row->base_quantity = $item->quantity;
                $row->base_unit = $row->unit ? $row->unit : $item->product_unit_id;
                $row->base_unit_price = $row->price ? $row->price : $item->unit_price;
                $row->unit = $item->product_unit_id;
                $row->qty = $item->unit_quantity;
                $row->discount = $item->discount ? $item->discount : '0';
                $row->price = $item->real_unit_price;
$row->unit_price = $item->net_unit_price;
$row->real_unit_price = $item->real_unit_price;
                $row->tax_rate = $item->tax_rate_id;
                $row->option = $item->option_id;

                $options = $this->quotes_model->getProductOptions($row->id, $item->warehouse_id);
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
                    $combo_items = $this->quotes_model->getProductComboItems($row->id, $item->warehouse_id);
                    foreach ($combo_items as $combo_item) {
                        $combo_item->quantity = $combo_item->qty * $item->quantity;
                    }
                }

                $units = $this->site->getUnitsByBUID($row->base_unit);
                $tax_rate = $this->site->getTaxRateByID($row->tax_rate);

                // ===== ✅ PHẦN CONVERT CUSTOM FIELDS - QUAN TRỌNG =====
                // Dùng chính item->id làm key (từ database)
                $item_key = $item->id;
                $custom_fields_data = array();

                // Convert từ format database sang format JavaScript
                if (!empty($item->custom_fields)) {
                    foreach ($item->custom_fields as $field_name => $field_value) {
                        // Database: "Rộng" → JavaScript: "custom_Rộng"
                        // Database: "Dài" → JavaScript: "custom_Dài"
                        $field_key = 'custom_' . str_replace(' ', '_', $field_name);
                        $custom_fields_data[$field_key] = $field_value;
                    }
                }

                // Lưu vào array với item->id làm key
                $pr[$item_key] = array(
                    'id' => $item_key,  // ← Dùng item->id từ database
                    'item_id' => $row->id,
                    'label' => $row->name . " (" . $row->code . ")",
                    'row' => $row,
                    'combo_items' => $combo_items,
                    'tax_rate' => $tax_rate,
                    'units' => $units,
                    'options' => $options,
                    'custom_fields' => $custom_fields_data, // ← Đã convert sang format JavaScript
                    'notes' => isset($item->notes) ? $item->notes : ''
                );
                $c++;
            }

            $this->data['inv_items'] = json_encode($pr);
            $this->data['id'] = $id;
            //$this->data['currencies'] = $this->site->getAllCurrencies();
            $this->data['billers'] = ($this->Owner || $this->Admin || !$this->session->userdata('biller_id')) ? $this->site->getAllCompanies('biller') : null;
            $this->data['tax_rates'] = $this->site->getAllTaxRates();
            $this->data['warehouses'] = ($this->Owner || $this->Admin || !$this->session->userdata('biller_id')) ? $this->site->getAllWarehouses() : null;

            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('quotes'), 'page' => lang('quotes')), array('link' => '#', 'page' => lang('edit_quote')));
            $meta = array('page_title' => lang('edit_quote'), 'bc' => $bc);
            $this->page_construct('quotes/edit', $meta, $this->data);
        }
    }

    public function delete($id = null)
    {
        $this->sma->checkPermissions(NULL, true);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        if ($this->quotes_model->deleteQuote($id)) {
            if ($this->input->is_ajax_request()) {
                $this->sma->send_json(array('error' => 0, 'msg' => lang("quote_deleted")));
            }
            $this->session->set_flashdata('message', lang('quote_deleted'));
            redirect('welcome');
        }
    }

    public function suggestions()
    {
        $term = $this->input->get('term', true);
        $warehouse_id = $this->input->get('warehouse_id', true);
        $customer_id = $this->input->get('customer_id', true);

        if (strlen($term) < 1 || !$term) {
            die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . site_url('welcome') . "'; }, 10);</script>");
        }

        $analyzed = $this->sma->analyze_term($term);
        $sr = $analyzed['term'];
        $option_id = $analyzed['option_id'];
        $warehouse = $this->site->getWarehouseByID($warehouse_id);
        $customer = $this->site->getCompanyByID($customer_id);
        $customer_group = $this->site->getCustomerGroupByID($customer->customer_group_id);
        $rows = $this->quotes_model->getProductNames($sr, $warehouse_id);
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
                $options = $this->quotes_model->getProductOptions($row->id, $warehouse_id);
                if ($options) {
                    $opt = $option_id && $r == 0 ? $this->quotes_model->getProductOptionByID($option_id) : $options[0];
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
                $row->price = $row->price + (($row->price * $customer_group->percent) / 100);
                $row->real_unit_price = $row->price;
                $row->base_quantity = 1;
                $row->base_unit = $row->unit;
                $row->base_unit_price = $row->price;
                $row->unit = $row->sale_unit ? $row->sale_unit : $row->unit;
                $combo_items = false;
                if ($row->type == 'combo') {
                    $combo_items = $this->quotes_model->getProductComboItems($row->id, $warehouse_id);
                }
                $units = $this->site->getUnitsByBUID($row->base_unit);
                $tax_rate = $this->site->getTaxRateByID($row->tax_rate);

                $pr[] = array(
                    'id' => ($c + $r),
                    'item_id' => $row->id,
                    'label' => $row->name . " (" . $row->code . ")",
                    'category' => $row->category_id,
                    'row' => $row,
                    'combo_items' => $combo_items,
                    'tax_rate' => $tax_rate,
                    'units' => $units,
                    'options' => $options
                );
                $r++;
            }
            $this->sma->send_json($pr);
        } else {
            $this->sma->send_json(array(array('id' => 0, 'label' => lang('no_match_found'), 'value' => $term)));
        }
    }

    public function quote_actions()
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
                        $this->quotes_model->deleteQuote($id);
                    }
                    $this->session->set_flashdata('message', $this->lang->line("quotes_deleted"));
                    redirect($_SERVER["HTTP_REFERER"]);
                } elseif ($this->input->post('form_action') == 'combine') {

                    $html = $this->combine_pdf($_POST['val']);
                } elseif ($this->input->post('form_action') == 'export_excel' || $this->input->post('form_action') == 'export_pdf') {

                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('quotes'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('reference_no'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('biller'));
                    $this->excel->getActiveSheet()->SetCellValue('D1', lang('customer'));
                    $this->excel->getActiveSheet()->SetCellValue('E1', lang('total'));
                    $this->excel->getActiveSheet()->SetCellValue('F1', lang('status'));

                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $qu = $this->quotes_model->getQuoteByID($id);
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
                $this->session->set_flashdata('error', $this->lang->line("no_quote_selected"));
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

        if ($this->form_validation->run() == true && $this->quotes_model->updateStatus($id, $status, $note)) {
            $this->session->set_flashdata('message', lang('status_updated'));
            redirect(isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : 'sales');
        } else {

            $this->data['inv'] = $this->quotes_model->getQuoteByID($id);
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'quotes/update_status', $this->data);
        }
    }
    public function printbaogia($quote_id = NULL)
{
    $this->sma->checkPermissions('index');

    if ($this->input->get('id')) {
        $quote_id = $this->input->get('id');
    }
    $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
    $transfer = $inv = $this->quotes_model->getQuoteByID($quote_id);
    if (!$this->session->userdata('view_right')) {
        $this->sma->view_rights($inv->created_by);
    }
    $raw_rows = $this->quotes_model->getAllQuoteItems($quote_id);

    // ✅ CODE MỚI - NHÓM SẢN PHẨM CÓ group_id, SỰ DỰ LÀ SẢN PHẨM RIÊNG LẺ
    $grouped_rows = array();
    $current_main_index = -1;

    foreach ($raw_rows as $row) {
        if ($row->group_id == 30 || $row->group_id == 0 || $row->group_id === NULL) {
            $grouped_rows[] = array(
                'main' => $row,
                'color' => null,
                'lock' => null,
                'image' => $row->image
            );
            $current_main_index++;
        } 
        elseif ($row->group_id == 31 && $current_main_index >= 0) {
            $grouped_rows[$current_main_index]['color'] = $row;
        } 
        elseif ($row->group_id == 32 && $current_main_index >= 0) {
            $grouped_rows[$current_main_index]['lock'] = $row;
        } 
        // ✅ THÊM: Nếu group_id không được nhận diện, coi nó là sản phẩm riêng lẻ
        else {
            $grouped_rows[] = array(
                'main' => $row,
                'color' => null,
                'lock' => null,
                'image' => $row->image
            );
            $current_main_index++;
        }
    }

    $this->data['rows'] = $rows = $grouped_rows;
    $this->data['customer'] = $customer = $this->site->getCompanyByID($inv->customer_id);
    $this->data['biller'] = $this->site->getCompanyByID($inv->biller_id);
    $this->data['created_by'] = $created_by = $this->site->getUser($inv->created_by);
    $this->data['updated_by'] = $inv->updated_by ? $this->site->getUser($inv->updated_by) : null;
    $this->data['warehouse'] = $warehouse = $this->site->getWarehouseByID($inv->warehouse_id);
    $this->data['inv'] = $inv;


        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));


        $_tablhd = '<table border="1" style="width:100%;border-collapse:collapse;">        <tbody>        <tr>            <td style="text-align:center;width:5%;"><strong>STT</strong>            </td>            <td style="width:51%;padding-left:0.5%;"><strong>Tên hàng hóa</strong><br>            </td>  <td style="text-align:center;width:20%;"><strong>SL</strong><br>            </td> <td style="text-align:center;width:10%;"><strong>Báo Giá</strong>            </td>                     <td style="text-align:center;width:15%;"><strong>T.Tiền</strong><br>            </td>      </tr>';
        if ($this->Settings->tax1) {
            $_tablhd = '<table border="1" style="width:100%;border-collapse:collapse;">        <tbody>        <tr>            <td style="text-align:center;width:5%;"><strong>STT</strong>            </td>            <td style="width:51%;padding-left:0.5%;"><strong>Tên hàng hóa</strong><br>            </td>  <td style="text-align:center;width:10%;"><strong>SL</strong><br>            </td> <td style="text-align:center;width:10%;"><strong>Báo Giá</strong>            </td>   <td style="text-align:center;width:80px;"><strong>Thuế</strong>            </td>                      <td style="text-align:center;width:15%;"><strong>T.Tiền</strong><br>            </td>      </tr>';
        }
        $tong_so = 0;

        $r = 1;
        foreach ($rows as $row) {
            $_prod_detail = $row->product_code . ' - ' . $row->product_name . ($row->variant ? ' (' . $row->variant . ')' : '');

            $_add_str = '<tr><td style="text-align:center;vertical-align:middle;">' . $r . '</td><td style="vertical-align:middle;">' . $_prod_detail . '</td><td style="text-align:center; vertical-align:middle;">' . $this->sma->formatQuantity($row->unit_quantity) . '</td><td style="text-align:center; vertical-align:middle;">' . $this->sma->formatMoney($row->unit_price) . '</td>  <td style="text-align:center; vertical-align:middle;">' . $this->sma->formatMoney($row->subtotal) . '</td>    </tr>';


            if ($this->Settings->tax1) {
                $_add_str = '<tr><td style="text-align:center;vertical-align:middle;">' . $r . '</td><td style="vertical-align:middle;">' . $_prod_detail . '</td><td style="text-align:center; vertical-align:middle;">' . $this->sma->formatQuantity($row->unit_quantity) . '</td><td style="text-align:center; vertical-align:middle;">' . $this->sma->formatMoney($row->unit_price) . '</td><td style="width: 80px; text-align:right; vertical-align:middle;"><!--<small>(' . $row->tax . ')</small>--> ' . $this->sma->formatMoney($row->item_tax) . '</td>  <td style="text-align:center; vertical-align:middle;">' . $this->sma->formatMoney($row->subtotal) . '</td>    </tr>';
            }
            $_tablhd .= $_add_str;
            $tong_so += $row->unit_quantity;
            $r++;
        }
        $_tablhd .= '</table>';
        $_tongcong_bang_chu = $transfer->grand_total;
        $left_end = substr($_tongcong_bang_chu, strlen($_tongcong_bang_chu) - 5, strlen($_tongcong_bang_chu));
        if ($left_end == '.0000') {
            $_tongcong_bang_chu = str_replace($left_end, "", $_tongcong_bang_chu);
        }
        $_tongcong_bang_chu_text = $this->site->convert_number_to_words($_tongcong_bang_chu);

        $_dc_cuahang = str_replace("<p>", "", $warehouse->address);
        $_dc_cuahang = str_replace("</p>", "", $_dc_cuahang);



        $parse_data = array('So_Hoa_Don' => $transfer->reference_no, 'site_link' => base_url(), 'site_name' => $this->Settings->site_name, 'Ten_Kho' => $warehouse->name, 'Dia_Chi_Kho' => $_dc_cuahang, 'SDT_Kho' => $warehouse->phone, 'Email_Kho' => $warehouse->email, 'Ngay' => $this->sma->hrld($transfer->date), 'Nhan_Vien' => $created_by->first_name . ' ' . $created_by->last_name, 'Bang_Hoa_Don' => $_tablhd, 'Ghi_Chu' => $this->sma->decode_html($transfer->note), 'Tong_Cong' => $this->sma->formatMoney($transfer->grand_total), 'Tong_Cong_Bang_Chu' => $_tongcong_bang_chu_text, 'Nha_Cung_Cap' => $transfer->supplier, 'Trang_Thai' => lang($transfer->status), 'Ten_Khach' => $customer->name, 'SDT_Khach' => $customer->phone, 'Dia_Chi_Khach' => $customer->address, 'Email_Khach' => $customer->email);


        if (file_exists('./themes/' . $this->theme . '/views/print_khac/printbaogia.html')) {
            $sale_temp = file_get_contents('themes/' . $this->theme . '/views/print_khac/printbaogia.html');
        } else {
            $sale_temp = file_get_contents('./themes/default/views/print_khac/printbaogia.html');
        }
        $_get_active_baogia = $this->settings_model->get_print_value($sale_temp);
        $rs_ex_pos = explode(":", $_get_active_baogia);

        $_sizein_page = isset($rs_ex_pos[0]) && $rs_ex_pos[0] != ":" ? $rs_ex_pos[0] : "A5";
        $_chieuin_page = isset($rs_ex_pos[1]) && $rs_ex_pos[1] != ":" ? $rs_ex_pos[1] : "Portrait";

        $this->data['item_print'] = $this->Settings->item_print;
        $this->data['kich_thuoc'] = $this->settings_model->define_size_in($_sizein_page, $_chieuin_page);

        //replace value size print
        $sale_temp = $this->settings_model->define_print_replace($sale_temp);

        $message = $this->parser->parse_string($sale_temp, $parse_data, true);

        $this->data['note'] = array('noidung' => $message);
        $this->data['id'] = $transfer->id;
        $this->data['modal_js'] = $this->site->modal_js();
        $this->load->view($this->theme . 'quotes/printbaogia', $this->data);
    }
    // THÊM VÀO CUỐI CLASS, TRƯỚC DẤU ĐÓNG }

    public function get_custom_columns()
    {
        $columns = $this->quotes_model->getCustomColumns();
        if ($columns) {
            $result = array();
            foreach ($columns as $col) {
                $result[] = $col->column_name;
            }
            echo json_encode($result);
        } else {
            echo json_encode(array());
        }
    }
    // ==================== CONTROLLER: Quotes.php ====================
    // TÌM HÀM save_custom_columns() RỒI THAY TOÀN BỘ:

    public function save_custom_columns()
    {
        // Chỉ cho phép request từ AJAX
        if (!$this->input->is_ajax_request()) {
            exit('No direct script access allowed');
        }

        $this->sma->checkPermissions();

        $columns = $this->input->post('columns');

        // Cho phép lưu ngay cả khi mảng rỗng (xóa hết các cột)
        if (!is_array($columns)) {
            $columns = array(); // Chuyển thành mảng rỗng
        }

        // Lưu vào database
        if ($this->quotes_model->saveCustomColumns($columns)) {
            echo json_encode(array(
                'success' => true,
                'message' => count($columns) > 0
                    ? 'Đã lưu ' . count($columns) . ' cột tùy chỉnh thành công!'
                    : 'Đã xóa tất cả các cột tùy chỉnh!',
                'columns' => $columns
            ));
        } else {
            echo json_encode(array('success' => false, 'message' => 'Lỗi khi lưu vào database'));
        }
    }


    // ==================== MODEL: Quotes_model.php ====================
    // TÌM HÀM saveCustomColumns() RỒI THAY TOÀN BỘ:

    public function saveCustomColumns($columns = array())
    {
        // Xóa tất cả cột cũ (dù mảng $columns rỗng hay không)
        $this->db->truncate('quote_custom_columns');

        // Nếu có cột mới thì insert
        if (!empty($columns) && is_array($columns)) {
            foreach ($columns as $index => $col_name) {
                $col_name = trim($col_name);
                if (!empty($col_name)) {
                    $data = array(
                        'column_name' => $col_name,
                        'column_order' => $index
                    );
                    $this->db->insert('quote_custom_columns', $data);
                }
            }
        }
        // Nếu mảng rỗng, chỉ truncate mà không insert gì (đã xóa hết)

        return true;
    }
    // ============ THÊM HÀM NÀY VÀO FILE Quotes.php (Controller) ============
// Đặt ngay sau hàm pdf()

// ============ THÊM/THAY HÀM NÀY VÀO Quotes.php ============
public function image($quote_id = null)
{
    $this->sma->checkPermissions();

    if ($this->input->get('id')) {
        $quote_id = $this->input->get('id');
    }
    
    $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
    $inv = $this->quotes_model->getQuoteByID($quote_id);
    
    if (!$this->session->userdata('view_right')) {
        $this->sma->view_rights($inv->created_by);
    }
    
    $raw_rows = $this->quotes_model->getAllQuoteItems($quote_id);

    // Nhóm sản phẩm
    $grouped_rows = array();
    $current_main_index = -1;

    foreach ($raw_rows as $row) {
        if ($row->group_id == 30 || $row->group_id == 0 || $row->group_id === NULL) {
            $grouped_rows[] = array(
                'main' => $row,
                'color' => null,
                'lock' => null,
                'image' => $row->image
            );
            $current_main_index++;
        } 
        elseif ($row->group_id == 31 && $current_main_index >= 0) {
            $grouped_rows[$current_main_index]['color'] = $row;
        } 
        elseif ($row->group_id == 32 && $current_main_index >= 0) {
            $grouped_rows[$current_main_index]['lock'] = $row;
        } 
        else {
            $grouped_rows[] = array(
                'main' => $row,
                'color' => null,
                'lock' => null,
                'image' => $row->image
            );
            $current_main_index++;
        }
    }

    $this->data['rows'] = $grouped_rows;
    $this->data['custom_columns'] = $this->quotes_model->getCustomColumns();
    $this->data['customer'] = $this->site->getCompanyByID($inv->customer_id);
    $this->data['biller'] = $this->site->getCompanyByID($inv->biller_id);
    $this->data['user'] = $this->site->getUser($inv->created_by);
    $this->data['warehouse'] = $this->site->getWarehouseByID($inv->warehouse_id);
    $this->data['inv'] = $inv;
    
    // ✅ Load view image.php với JavaScript để tự động chụp ảnh
    $this->load->view($this->theme . 'quotes/image', $this->data);
}

// Hàm helper để tạo ảnh từ HTML
private function generate_image($html, $filename)
{
    // Cài đặt thư viện wkhtmltoimage
    // Đường dẫn đến wkhtmltoimage (cần cài đặt trên server)
    $wkhtmltoimage_path = '/usr/local/bin/wkhtmltoimage'; // Linux
    // Hoặc: C:\Program Files\wkhtmltopdf\bin\wkhtmltoimage.exe // Windows
    
    // Tạo file HTML tạm
    $temp_html = sys_get_temp_dir() . '/' . uniqid() . '.html';
    file_put_contents($temp_html, $html);
    
    // Tạo file ảnh output
    $output_file = sys_get_temp_dir() . '/' . $filename . '.png';
    
    // Command để chuyển HTML sang PNG
    $cmd = sprintf(
        '%s --width 1200 --quality 95 --enable-local-file-access %s %s',
        $wkhtmltoimage_path,
        escapeshellarg($temp_html),
        escapeshellarg($output_file)
    );
    
    // Thực thi command
    exec($cmd, $output, $return_var);
    
    if ($return_var === 0 && file_exists($output_file)) {
        // Download file
        header('Content-Description: File Transfer');
        header('Content-Type: image/png');
        header('Content-Disposition: attachment; filename="' . $filename . '.png"');
        header('Content-Length: ' . filesize($output_file));
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        
        readfile($output_file);
        
        // Xóa file tạm
        unlink($temp_html);
        unlink($output_file);
    } else {
        // Nếu lỗi, sử dụng phương án dự phòng với PhantomJS hoặc thông báo lỗi
        $this->session->set_flashdata('error', 'Không thể tạo ảnh. Vui lòng cài đặt wkhtmltoimage.');
        redirect('quotes/view/' . $this->input->get('id'));
    }
}
}

