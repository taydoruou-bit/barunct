<?php defined('BASEPATH') or exit('No direct script access allowed');

class Pos extends MY_Controller
{

    public function __construct()
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

        $this->load->model('pos_model');		
		$this->load->model('reports_model');		
		$this->load->model('settings_model');
		$this->load->model('doitac_model');		
		$this->load->model('companies_model');	
		
        $this->load->helper('text');
        $this->pos_settings = $this->pos_model->getSetting();
        $this->pos_settings->pin_code = $this->pos_settings->pin_code ? md5($this->pos_settings->pin_code) : NULL;
        $this->data['pos_settings'] = $this->pos_settings;
        $this->lang->load('pos', $this->Settings->user_language);
        $this->load->library('form_validation');
    }

    public function sales($warehouse_id = NULL)
    {
        $this->sma->checkPermissions('index');

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

        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('pos'), 'page' => lang('pos')), array('link' => '#', 'page' => lang('pos_sales')));
        $meta = array('page_title' => lang('pos_sales'), 'bc' => $bc);
        $this->page_construct('pos/sales', $meta, $this->data);
    }

    public function getSales($warehouse_id = NULL)
    {
        $this->sma->checkPermissions('index');

        if ((!$this->Owner || !$this->Admin) && !$warehouse_id) {
            $user = $this->site->getUser();
            $warehouse_id = $user->warehouse_id;
        }
        $print_link = anchor('sales/printsalelhson/$1', '<i class="fa fa-print"></i> ' . lang('print_hoadon'), 'data-toggle="modal" data-target="#myModal"');
        $detail_link = anchor('pos/view/$1', '<i class="fa fa-file-text-o"></i> ' . lang('view_receipt'));
        $detail_link2 = anchor('sales/modal_view/$1', '<i class="fa fa-file-text-o"></i> ' . lang('sale_details_modal'), 'data-toggle="modal" data-target="#myModal"');
        $detail_link3 = anchor('sales/view/$1', '<i class="fa fa-file-text-o"></i> ' . lang('sale_details'));
        $payments_link = anchor('sales/payments/$1', '<i class="fa fa-money"></i> ' . lang('view_payments'), 'data-toggle="modal" data-target="#myModal"');
        $add_payment_link = anchor('pos/add_payment/$1', '<i class="fa fa-money"></i> ' . lang('add_payment'), 'data-toggle="modal" data-target="#myModal"');
        $add_delivery_link = anchor('sales/add_delivery/$1', '<i class="fa fa-truck"></i> ' . lang('add_delivery'), 'data-toggle="modal" data-target="#myModal"');
        $email_link = anchor('#', '<i class="fa fa-envelope"></i> ' . lang('email_sale'), 'class="email_receipt" data-id="$1" data-email-address="$2"');
        $edit_link = anchor('sales/edit/$1', '<i class="fa fa-edit"></i> ' . lang('edit_sale'), 'class="sledit"');
        $return_link = anchor('sales/return_sale/$1', '<i class="fa fa-angle-double-left"></i> ' . lang('return_sale'));
        $delete_link = "<a href='#' class='po' title='<b>" . lang("delete_sale") . "</b>' data-content=\"<p>"
            . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('sales/delete/$1') . "'>"
            . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
            . lang('delete_sale') . "</a>";
        $action = '<div class="text-center"><div class="btn-group text-left">'
            . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
            . lang('actions') . ' <span class="caret"></span></button>
    <ul class="dropdown-menu pull-right" role="menu">
        <li>' . $print_link . '</li>
        <li>' . $detail_link2 . '</li>
        <li>' . $detail_link3 . '</li>
        <li>' . $payments_link . '</li>
        <li>' . $add_payment_link . '</li>
        <li>' . $add_delivery_link . '</li>
        <li>' . $edit_link . '</li>
        <li>' . $email_link . '</li>
        <li>' . $return_link . '</li>
        <li>' . $delete_link . '</li>
    </ul>
</div></div>';
        //$action = '<div class="text-center">' . $detail_link . ' ' . $edit_link . ' ' . $email_link . ' ' . $delete_link . '</div>';

        $this->load->library('datatables');
        if ($warehouse_id) {
            $this->datatables
                ->select($this->db->dbprefix('sales') . ".id as id, sales.date, reference_no,doitac.name as doitac,warehouses.name as kho, biller, concat(customer,'<br/>',(select phone from scodeweb_companies where id=customer_id)) as customer, (grand_total+COALESCE(rounding, 0)), paid, (grand_total+rounding-paid) as balance, sale_status, payment_status, companies.email as cemail")
                ->from('sales')
                ->join('companies', 'companies.id=sales.customer_id', 'left')
				->join('doitac', 'doitac.id=sales.doitac', 'left')
				->join('warehouses', 'warehouses.id=sales.warehouse_id', 'left')
                ->where('scodeweb_sales.warehouse_id', $warehouse_id)
                ->group_by('sales.id');
        } else {
            $this->datatables
                ->select($this->db->dbprefix('sales') . ".id as id, sales.date, reference_no,doitac.name as doitac,warehouses.name as kho, biller, concat(customer,'<br/>',(select phone from scodeweb_companies where id=customer_id)) as customer, (grand_total+COALESCE(rounding, 0)), paid, (grand_total+rounding-paid) as balance, sale_status, payment_status, companies.email as cemail")
                ->from('sales')
                ->join('companies', 'companies.id=sales.customer_id', 'left')
				->join('doitac', 'doitac.id=sales.doitac', 'left')
				->join('warehouses', 'warehouses.id=sales.warehouse_id', 'left')
                ->group_by('sales.id');
        }
        $this->datatables->where('pos', 1);
        if (!$this->Customer && !$this->Supplier && !$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->datatables->where('scodeweb_sales.created_by', $this->session->userdata('user_id'));
        } elseif ($this->Customer) {
            $this->datatables->where('scodeweb_sales.customer_id', $this->session->userdata('user_id'));
        }
        $this->datatables->add_column("Actions", $action, "id, cemail")->unset_column('cemail');
        echo $this->datatables->generate();
    }

    /* ---------------------------------------------------------------------------------------------------- */

    public function index($sid = NULL)
    {
        $this->sma->checkPermissions();

        if (!$this->pos_settings->default_biller || !$this->pos_settings->default_customer || !$this->pos_settings->default_category) {
            $this->session->set_flashdata('warning', lang('please_update_settings'));
            redirect('pos/settings');
        }
        if ($register = $this->pos_model->registerData($this->session->userdata('user_id'))) {
            $register_data = array('register_id' => $register->id, 'cash_in_hand' => $register->cash_in_hand, 'register_open_time' => $register->date);
            $this->session->set_userdata($register_data);
        } else {
            
            $data = array(
                'date' => date('Y-m-d H:i:s'),
                'cash_in_hand' => 0,
                'user_id'      => $this->session->userdata('user_id'),
                'status'       => 'open',
                );            
            $this->pos_model->openRegister($data);            

        }

        $this->data['sid'] = $this->input->get('suspend_id') ? $this->input->get('suspend_id') : $sid;
        $did = $this->input->post('delete_id') ? $this->input->post('delete_id') : NULL;
        $suspend = $this->input->post('suspend') ? TRUE : FALSE;
        $count = $this->input->post('count') ? $this->input->post('count') : NULL;

        //validate form input
        $this->form_validation->set_rules('customer', $this->lang->line("customer"), 'trim|required');
        $this->form_validation->set_rules('warehouse', $this->lang->line("warehouse"), 'required');
        $this->form_validation->set_rules('biller', $this->lang->line("biller"), 'required');

        if ($this->form_validation->run() == TRUE) {

            $date = date('Y-m-d H:i:s');
            $warehouse_id = $this->input->post('warehouse');
            $customer_id = $this->input->post('customer');
            $biller_id = $this->input->post('biller');
			$doitac = $this->input->post('doitac');
            $total_items = $this->input->post('total_items');
            $sale_status = 'completed';
            $payment_status = 'due';
            $payment_term = 0;
            $due_date = date('Y-m-d', strtotime('+' . $payment_term . ' days'));
            $shipping = $this->input->post('shipping') ? $this->input->post('shipping') : 0;
            $customer_details = $this->site->getCompanyByID($customer_id);
            $customer = $customer_details->company != ''  ? $customer_details->company : $customer_details->name;
            $biller_details = $this->site->getCompanyByID($biller_id);
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
            $i = isset($_POST['product_code']) ? sizeof($_POST['product_code']) : 0;
            for ($r = 0; $r < $i; $r++) {
                $item_id = $_POST['product_id'][$r];
                $item_type = $_POST['product_type'][$r];
                $item_code = $_POST['product_code'][$r];
                $item_name = $_POST['product_name'][$r];
                $item_comment = $_POST['product_comment'][$r];
                $item_option = isset($_POST['product_option'][$r]) && $_POST['product_option'][$r] != 'false' ? $_POST['product_option'][$r] : NULL;
                $real_unit_price = $this->sma->formatDecimal($_POST['real_unit_price'][$r]);
                $unit_price = $this->sma->formatDecimal($_POST['unit_price'][$r]);
                $item_unit_quantity = $_POST['quantity'][$r];
                $item_serial = isset($_POST['serial'][$r]) ? $_POST['serial'][$r] : '';
                $item_tax_rate = isset($_POST['product_tax'][$r]) ? $_POST['product_tax'][$r] : NULL;
                $item_discount = isset($_POST['product_discount'][$r]) ? $_POST['product_discount'][$r] : NULL;
                $item_unit = $_POST['product_unit'][$r];
                $item_quantity = $_POST['product_base_quantity'][$r];
                $data_id_khuyenmai = $_POST['data_id_khuyenmai'][$r];

                if (isset($item_code) && isset($real_unit_price) && isset($unit_price) && isset($item_quantity)) {
                    $product_details = $item_type != 'manual' ? $this->pos_model->getProductByCode($item_code) : NULL;
                    
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
                        'data_id_khuyenmai' => $data_id_khuyenmai,
                    );

                    $total += $this->sma->formatDecimal(($item_net_price * $item_unit_quantity), 4)-$pr_item_discount;
                }
            }

            if (empty($products)) {
                $this->form_validation->set_rules('product', lang("order_items"), 'required');
            } elseif ($this->pos_settings->item_order == 1) {
                krsort($products);
            }

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
                          'paid'              => $this->input->post('amount-paid') ? $this->input->post('amount-paid') : 0,
                          'created_by'        => $this->session->userdata('user_id'),
                          'total_weight'        => $total_weight,
            );
			
            if (!$suspend) {
                $p = isset($_POST['amount']) ? sizeof($_POST['amount']) : 0;
                $paid = 0;
                for ($r = 0; $r < $p; $r++) {
					
					
                    if (isset($_POST['amount'][$r]) && !empty($_POST['amount'][$r]) && isset($_POST['paid_by'][$r]) && !empty($_POST['paid_by'][$r])) {
                        $amount = $this->sma->formatDecimal($_POST['balance_amount'][$r] > 0 ? $_POST['amount'][$r] - $_POST['balance_amount'][$r] : $_POST['amount'][$r]);
                        if ($_POST['paid_by'][$r] == 'deposit') {
                            if ( ! $this->site->check_customer_deposit($customer_id, $amount)) {
                                $this->session->set_flashdata('error', lang("amount_greater_than_deposit"));
                                redirect($_SERVER["HTTP_REFERER"]);
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
                                'cc_cvv2'      => $_POST['cc_cvv2'][$r],
                                'created_by'   => $this->session->userdata('user_id'),
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
                                'cc_cvv2'      => $_POST['cc_cvv2'][$r],
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
            if (!isset($payment) || empty($payment)) {
                $payment = array();
            }
            $dlDetails=null;
            if ((int)$doitac>0) {
                //get customer address
                $kh_obj=$this->site->getCompanyByID($customer_id); 
                $dlDetails = array(
                'date' => $date,
                'do_reference_no' =>$this->site->getReference('do'),
                'sale_reference_no' => $reference,
                'customer' => $customer,
                'address' => $kh_obj->address,
                'phone' => $kh_obj->phone,
                'status' => 'packing',
                'delivered_by' => $doitac,
                'shipping' => $this->sma->formatDecimal($shipping),
                'received_by' => '',
                'note' => '',
                'created_by' => $this->session->userdata('user_id'),
                'warehouse_id'=>$warehouse_id
                );
            }
             //$this->sma->print_arrays($data, $products, $payment);
        }

        if ($this->form_validation->run() == TRUE && !empty($products) && !empty($data)) {
            if ($suspend) {
                if ($this->pos_model->suspendSale($data, $products, $did)) {
                    $this->session->set_userdata('remove_posls', 1);
                    $this->session->set_flashdata('message', $this->lang->line("sale_suspended"));
                    redirect("pos");
                }
            } else {
                if ($sale = $this->pos_model->addSale($data, $products, $payment, $did,$dlDetails)) {
                    $this->session->set_userdata('remove_posls', 1);
                    $msg = $this->lang->line("sale_added");
                    if (!empty($sale['message'])) {
                        foreach ($sale['message'] as $m) {
                            $msg .= '<br>' . $m;
                        }
                    }                   

                    $this->session->set_flashdata('message', $msg);
                    $redirect_to = $this->pos_settings->after_sale_page ? "pos" : "pos/view/" . $sale['sale_id'];
                    if ($this->pos_settings->auto_print) {
                        if ($this->Settings->remote_printing != 1) {
                            $redirect_to .= '?print='.$sale['sale_id'];
                        }
                    }
                    redirect($redirect_to);
                }
            }
        } else {
            $this->data['suspend_sale'] = NULL;
            if ($sid) {
                if ($suspended_sale = $this->pos_model->getOpenBillByID($sid)) {
                    $inv_items = $this->pos_model->getSuspendedSaleItems($sid);
                    krsort($inv_items);
                    $c = rand(100000, 9999999);
                    foreach ($inv_items as $item) {
                        $row = $this->site->getProductByID($item->product_id);
                        if (!$row) {
                            $row = json_decode('{}');
                            $row->tax_method = 0;
                            $row->quantity = 0;
                        } else {
                            $category = $this->site->getCategoryByID($row->category_id);
                            $row->category_name = $category->name;
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
                        $row->quantity += $item->quantity;
                        $row->discount = $item->discount ? $item->discount : '0';
                        $row->price = $this->sma->formatDecimal($item->net_unit_price + $this->sma->formatDecimal($item->item_discount / $item->quantity));
                        $row->unit_price = $row->tax_method ? $item->unit_price + $this->sma->formatDecimal($item->item_discount / $item->quantity) + $this->sma->formatDecimal($item->item_tax / $item->quantity) : $item->unit_price + ($item->item_discount / $item->quantity);
                        $row->real_unit_price = $item->real_unit_price;
                        $row->base_quantity = $item->quantity;
                        $row->base_unit = isset($row->unit) ? $row->unit : $item->product_unit_id;
                        $row->base_unit_price = $row->price ? $row->price : $item->unit_price;
                        $row->unit = $item->product_unit_id;
                        $row->qty = $item->unit_quantity;
                        $row->tax_rate = $item->tax_rate_id;
                        $row->serial = $item->serial_no;
                        $row->option = $item->option_id;
                        $options = $this->pos_model->getProductOptions($row->id, $item->warehouse_id);

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

                        $row->comment = isset($item->comment) ? $item->comment : '';
                        $row->ordered = 1;
                        $combo_items = false;
                        if ($row->type == 'combo') {
                            $combo_items = $this->pos_model->getProductComboItems($row->id, $item->warehouse_id);
                        }
                        $units = $this->site->getUnitsByBUID($row->base_unit);
                        $tax_rate = $this->site->getTaxRateByID($row->tax_rate);
                        $ri = $this->Settings->item_addition ? $row->id : $c;
                        
                        $pr[$ri] = array('id' => $c, 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 
                                'row' => $row, 'combo_items' => $combo_items, 'tax_rate' => $tax_rate, 'units' => $units, 'options' => $options);
                        $c++;
                    }

                    $this->data['items'] = json_encode($pr);
                    $this->data['sid'] = $sid;
                    $this->data['suspend_sale'] = $suspended_sale;
                    $this->data['message'] = lang('suspended_sale_loaded');
                    $this->data['customer'] = $this->pos_model->getCompanyByID($suspended_sale->customer_id);
                    $this->data['reference_note'] = $suspended_sale->suspend_note;
                } else {
                    $this->session->set_flashdata('error', lang("bill_x_found"));
                    redirect("pos");
                }
            } else {
                $this->data['customer'] = $this->pos_model->getCompanyByID($this->pos_settings->default_customer);
                $this->data['reference_note'] = NULL;
            }

            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['message'] = isset($this->data['message']) ? $this->data['message'] : $this->session->flashdata('message');

            // $this->data['biller'] = $this->site->getCompanyByID($this->pos_settings->default_biller);
            $this->data['billers'] = $this->site->getAllCompanies('biller');
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['tax_rates'] = $this->site->getAllTaxRates();
            $this->data['user'] = $this->site->getUser();
            $this->data["tcp"] = $this->pos_model->products_count($this->pos_settings->default_category);
            $this->data['products'] = $this->ajaxproducts($this->pos_settings->default_category);
            $this->data['categories'] = $this->site->getAllCategories();
            $this->data['brands'] = $this->site->getAllBrands();
            
            $this->data['doitacs'] = $this->site->getAllDoitac(); 

            $this->data['subcategories'] = $this->site->getSubCategories($this->pos_settings->default_category);
            $this->data['printer'] = $this->pos_model->getPrinterByID($this->pos_settings->printer);
            $order_printers = json_decode($this->pos_settings->order_printers);
            if (!empty($order_printers)) {
                foreach ($order_printers as $printer_id) {
                    $printers[] = $this->pos_model->getPrinterByID($printer_id);
                }    
            }
            
            $this->data['order_printers'] = isset($printers)?$printers:null;
            $this->data['pos_settings'] = $this->pos_settings;

            if ($this->pos_settings->after_sale_page && $saleid = $this->input->get('print', true)) {
                if ($inv = $this->pos_model->getInvoiceByID($saleid)) {
                    $this->load->helper('pos');
                    if (!$this->session->userdata('view_right')) {
                        $this->sma->view_rights($inv->created_by, true);
                    }
                    $this->data['rows'] = $this->pos_model->getAllInvoiceItems($inv->id);
                    $this->data['biller'] = $this->pos_model->getCompanyByID($inv->biller_id);
                    $this->data['customer'] = $this->pos_model->getCompanyByID($inv->customer_id);
                    $this->data['payments'] = $this->pos_model->getInvoicePayments($inv->id);
                    $this->data['return_sale'] = $inv->return_id ? $this->pos_model->getInvoiceByID($inv->return_id) : NULL;
                    $this->data['return_rows'] = $inv->return_id ? $this->pos_model->getAllInvoiceItems($inv->return_id) : NULL;
                    $this->data['return_payments'] = $this->data['return_sale'] ? $this->pos_model->getInvoicePayments($this->data['return_sale']->id) : NULL;
                    $this->data['inv'] = $inv;
                    $this->data['print'] = $inv->id;
                    $this->data['created_by'] = $this->site->getUser($inv->created_by);
                }
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

            $this->data['sub_product'] = $khuyenmai_product;
            $this->data['main_product'] = $main_product;

            $this->load->view($this->theme . 'pos/add', $this->data);
        }
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
    public function view_bill()
    {
        $this->sma->checkPermissions('index');
        $this->data['tax_rates'] = $this->site->getAllTaxRates();
        $this->load->view($this->theme . 'pos/view_bill', $this->data);
    }

    public function stripe_balance()
    {
        if (!$this->Owner) {
            return FALSE;
        }
        $this->load->model('stripe_payments');

        return $this->stripe_payments->get_balance();
    }

    public function paypal_balance()
    {
        if (!$this->Owner) {
            return FALSE;
        }
        $this->load->model('paypal_payments');

        return $this->paypal_payments->get_balance();
    }

    public function registers()
    {
        $this->sma->checkPermissions();

        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['registers'] = $this->pos_model->getOpenRegisters();
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('pos'), 'page' => lang('pos')), array('link' => '#', 'page' => lang('open_registers')));
        $meta = array('page_title' => lang('open_registers'), 'bc' => $bc);
        $this->page_construct('pos/registers', $meta, $this->data);
    }

    public function open_register()
    {
        $this->sma->checkPermissions('index');
        $this->form_validation->set_rules('cash_in_hand', lang("cash_in_hand"), 'trim|required|numeric');

        if ($this->form_validation->run() == TRUE) {
            $data = array(
                'date' => date('Y-m-d H:i:s'),
                'cash_in_hand' => $this->input->post('cash_in_hand'),
                'user_id'      => $this->session->userdata('user_id'),
                'status'       => 'open',
                );
        }
        if ($this->form_validation->run() == TRUE && $this->pos_model->openRegister($data)) {
            $this->session->set_flashdata('message', lang("welcome_to_pos"));
            redirect("pos");
        } else {

            $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('open_register')));
            $meta = array('page_title' => lang('open_register'), 'bc' => $bc);
            $this->page_construct('pos/open_register', $meta, $this->data);
        }
    }

    public function close_register($user_id = NULL)
    {
        $this->sma->checkPermissions('index');
        if (!$this->Owner && !$this->Admin) {
            $user_id = $this->session->userdata('user_id');
        }
        $this->form_validation->set_rules('total_cash', lang("total_cash"), 'trim|required|numeric');
        $this->form_validation->set_rules('total_cheques', lang("total_cheques"), 'trim|required|numeric');
        $this->form_validation->set_rules('total_cc_slips', lang("total_cc_slips"), 'trim|required|numeric');

        if ($this->form_validation->run() == TRUE) {
            if ($this->Owner || $this->Admin) {
                $user_register = $user_id ? $this->pos_model->registerData($user_id) : NULL;
                $rid = $user_register ? $user_register->id : $this->session->userdata('register_id');
                $user_id = $user_register ? $user_register->user_id : $this->session->userdata('user_id');
            } else {
                $rid = $this->session->userdata('register_id');
                $user_id = $this->session->userdata('user_id');
            }
            $data = array(
                'closed_at'                => date('Y-m-d H:i:s'),
                'total_cash'               => $this->input->post('total_cash'),
                'total_cheques'            => $this->input->post('total_cheques'),
                'total_cc_slips'           => $this->input->post('total_cc_slips'),
                'total_cash_submitted'     => $this->input->post('total_cash_submitted'),
                'total_cheques_submitted'  => $this->input->post('total_cheques_submitted'),
                'total_cc_slips_submitted' => $this->input->post('total_cc_slips_submitted'),
                'note'                     => $this->input->post('note'),
                'status'                   => 'close',
                'transfer_opened_bills'    => $this->input->post('transfer_opened_bills'),
                'closed_by'                => $this->session->userdata('user_id'),
                );
        } elseif ($this->input->post('close_register')) {
            $this->session->set_flashdata('error', (validation_errors() ? validation_errors() : $this->session->flashdata('error')));
            redirect("pos");
        }

        if ($this->form_validation->run() == TRUE && $this->pos_model->closeRegister($rid, $user_id, $data)) {
            $this->session->set_flashdata('message', lang("register_closed"));
            redirect("welcome");
        } else {
            if ($this->Owner || $this->Admin) {
                $user_register = $user_id ? $this->pos_model->registerData($user_id) : NULL;
                $register_open_time = $user_register ? $user_register->date : NULL;
                $this->data['cash_in_hand'] = $user_register ? $user_register->cash_in_hand : NULL;
                $this->data['register_open_time'] = $user_register ? $register_open_time : NULL;
            } else {
                $register_open_time = $this->session->userdata('register_open_time');
                $this->data['cash_in_hand'] = NULL;
                $this->data['register_open_time'] = NULL;
            }
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['ccsales'] = $this->pos_model->getRegisterCCSales($register_open_time, $user_id);
            $this->data['cashsales'] = $this->pos_model->getRegisterCashSales($register_open_time, $user_id);
            $this->data['chsales'] = $this->pos_model->getRegisterChSales($register_open_time, $user_id);
            $this->data['gcsales'] = $this->pos_model->getRegisterGCSales($register_open_time);
            $this->data['pppsales'] = $this->pos_model->getRegisterPPPSales($register_open_time, $user_id);
            $this->data['stripesales'] = $this->pos_model->getRegisterStripeSales($register_open_time, $user_id);
            $this->data['authorizesales'] = $this->pos_model->getRegisterAuthorizeSales($register_open_time, $user_id);
            $this->data['totalsales'] = $this->pos_model->getRegisterSales($register_open_time, $user_id);
            $this->data['refunds'] = $this->pos_model->getRegisterRefunds($register_open_time, $user_id);
            $this->data['cashrefunds'] = $this->pos_model->getRegisterCashRefunds($register_open_time, $user_id);
            $this->data['expenses'] = $this->pos_model->getRegisterExpenses($register_open_time, $user_id);
            $this->data['users'] = $this->pos_model->getUsers($user_id);
            $this->data['suspended_bills'] = $this->pos_model->getSuspendedsales($user_id);
            $this->data['user_id'] = $user_id;
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'pos/close_register', $this->data);
        }
    }
    public function getProductDataByCode($code = NULL, $warehouse_id = NULL)
    {
        $this->sma->checkPermissions('index');
        if ($this->input->get('code')) {
            $code = $this->input->get('code', TRUE);
        }
        if ($this->input->get('warehouse_id')) {
            $warehouse_id = $this->input->get('warehouse_id', TRUE);
        }
        if ($this->input->get('customer_id')) {
            $customer_id = $this->input->get('customer_id', TRUE);
        }
        
        if (!$code) {
            echo NULL;
            die();
        }
        $warehouse = $this->site->getWarehouseByID($warehouse_id);
        $customer = $this->site->getCompanyByID($customer_id);
        $customer_group = $this->site->getCustomerGroupByID($customer->customer_group_id);

        $row = $this->pos_model->getWHProduct($code, $warehouse_id);
        $option = false;
        
        if ($row) {
            unset($row->cost, $row->details, $row->product_details, $row->image, $row->barcode_symbology, $row->cf1, $row->cf2, $row->cf3, $row->cf4, $row->cf5, $row->cf6, $row->supplier1price, $row->supplier2price, $row->cfsupplier3price, $row->supplier4price, $row->supplier5price, $row->supplier1, $row->supplier2, $row->supplier3, $row->supplier4, $row->supplier5, $row->supplier1_part_no, $row->supplier2_part_no, $row->supplier3_part_no, $row->supplier4_part_no, $row->supplier5_part_no);
            $row->item_tax_method = $row->tax_method;
            $row->qty = 1;
            $row->discount = '0';
            $row->serial = '';
            $options = $this->pos_model->getProductOptions($row->id, $warehouse_id);
            if ($options) {
                $opt = current($options);
                if (!$option) {
                    $option = $opt->id;
                }
            } else {
                $opt = json_decode('{}');
                $opt->price = 0;
            }
            $row->option = $option;
            
            
            $stock=$this->site->tonkhohientai($row->product_id, $warehouse_id);

            $row->quantity =  $stock;
            if ($row->type == 'standard' && (!$this->Settings->overselling && $row->quantity <= 0)) {
                echo NULL; die();
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
            $row->comment = '';
            $row->khuyenmai_main = '';
            $combo_items = false;
            if ($row->type == 'combo') {
                $combo_items = $this->pos_model->getProductComboItems($row->id, $warehouse_id);
            }
            $units = $this->site->getUnitsByBUID($row->base_unit);
            $units_nhap = $this->site->getUnitsByBUID($row->purchase_unit);
            
            $tax_rate = $this->site->getTaxRateByID($row->tax_rate);
            foreach($units as $_unit){
                if($row->base_unit==$_unit->base_unit){                 
                    $_doi_dv="";
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
                    $row->comment="<br/>".$_unit->name.":".round($_doi_dv,0);
                }
            }
            $pr = array('id' => str_replace(".", "", microtime(true)), 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 'category' => $row->category_id, 'row' => $row, 'combo_items' => $combo_items, 'tax_rate' => $tax_rate, 'units_nhap' => $units_nhap, 'units' => $units, 'options' => $options);
            
            $this->sma->send_json($pr);
        } else {
            echo NULL;
        }
        
    }
    public function getProductDataByCodeBK($code = NULL, $warehouse_id = NULL)
    {
        $this->sma->checkPermissions('index');
        if ($this->input->get('code')) {
            $code = $this->input->get('code', TRUE);
        }
        if ($this->input->get('warehouse_id')) {
            $warehouse_id = $this->input->get('warehouse_id', TRUE);
        }
        if ($this->input->get('customer_id')) {
            $customer_id = $this->input->get('customer_id', TRUE);
        }
		
        if (!$code) {
            echo NULL;
            die();
        }
        $warehouse = $this->site->getWarehouseByID($warehouse_id);
        $customer = $this->site->getCompanyByID($customer_id);
        $customer_group = $this->site->getCustomerGroupByID($customer->customer_group_id);
        $row = $this->pos_model->getWHProduct($code, $warehouse_id);
        $option = false;
		
        if ($row) {
            unset($row->cost, $row->details, $row->product_details, $row->image, $row->barcode_symbology, $row->cf1, $row->cf2, $row->cf3, $row->cf4, $row->cf5, $row->cf6, $row->supplier1price, $row->supplier2price, $row->cfsupplier3price, $row->supplier4price, $row->supplier5price, $row->supplier1, $row->supplier2, $row->supplier3, $row->supplier4, $row->supplier5, $row->supplier1_part_no, $row->supplier2_part_no, $row->supplier3_part_no, $row->supplier4_part_no, $row->supplier5_part_no);
            $row->item_tax_method = $row->tax_method;
            $row->qty = 1;
            $row->discount = '0';
            $row->serial = '';
            $options = $this->pos_model->getProductOptions($row->id, $warehouse_id);
            if ($options) {
                $opt = current($options);
                if (!$option) {
                    $option = $opt->id;
                }
            } else {
                $opt = json_decode('{}');
                $opt->price = 0;
            }
            $row->option = $option;
            $row->quantity = 0;
            $pis = $this->site->getPurchasedItems($row->id, $warehouse_id, $row->option);
            if ($pis) {
                foreach ($pis as $pi) {
                    $row->quantity += $pi->quantity_balance;
                }
            }
            if ($row->type == 'standard' && (!$this->Settings->overselling && $row->quantity <= 0)) {
                echo NULL; die();
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
            $row->comment = '';
            $combo_items = false;
            if ($row->type == 'combo') {
                $combo_items = $this->pos_model->getProductComboItems($row->id, $warehouse_id);
            }
            $units = $this->site->getUnitsByBUID($row->base_unit);
			
            $tax_rate = $this->site->getTaxRateByID($row->tax_rate);
			foreach($units as $_unit){
				if($row->base_unit==$_unit->base_unit){					
					$_doi_dv="";
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
					$row->comment="<br/>".$_unit->name.":".round($_doi_dv,0);
				}
			}
            $pr = array('id' => str_replace(".", "", microtime(true)), 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 'category' => $row->category_id, 'row' => $row, 'combo_items' => $combo_items, 'tax_rate' => $tax_rate, 'units' => $units, 'options' => $options);
			
            $this->sma->send_json($pr);
        } else {
            echo NULL;
        }
		
    }

    public function ajaxproducts($category_id = NULL, $brand_id = NULL)
    {
        $this->sma->checkPermissions('index');
        if ($this->input->get('brand_id')) {
            $brand_id = $this->input->get('brand_id');
        }
        if ($this->input->get('category_id')) {
            $category_id = $this->input->get('category_id');
        } else {
			// if($category_id>0){
				// $category_id = $this->pos_settings->default_category;
			// }
			$category_id=0;
        }
        if ($this->input->get('subcategory_id')) {
            $subcategory_id = $this->input->get('subcategory_id');
        } else {
            $subcategory_id = NULL;
        }
        if ($this->input->get('per_page') == 'n') {
            $page = 0;
        } else {
            $page = $this->input->get('per_page');
        }
        if ($this->input->get('warehouse_id')) {
            $warehouse_id = $this->input->get('warehouse_id');
        } else {
            $warehouse_id = $this->session->userdata('warehouse_id');
        }
        $this->load->library("pagination");

        $config = array();
        $config["base_url"] = base_url() . "pos/ajaxproducts";
        $config["total_rows"] = $this->pos_model->products_count($category_id, $subcategory_id, $brand_id);
        $config["per_page"] = $this->pos_settings->pro_limit;
        $config['prev_link'] = FALSE;
        $config['next_link'] = FALSE;
        $config['display_pages'] = TRUE;
        $config['first_link'] = FALSE;
        $config['last_link'] = FALSE;

        $this->pagination->initialize($config);

        $products = $this->pos_model->fetch_products($category_id, $config["per_page"], $page, $subcategory_id, $brand_id);
        $pro = 1;
        $prods = '<div>';
        if (!empty($products)) {
            foreach ($products as $product) {
                $count = $product->id;
                if ($count < 10) {
                    $count = "0" . ($count / 100) * 100;
                }
                if ($category_id < 10) {
                    $category_id = "0" . ($category_id / 100) * 100;
                }
				
				
				$giaban=$product->price;
                if($product->promotion==1){
                    if(date("Y-m-d",strtotime($product->end_date))>=date("Y-m-d")){ 
                        if($product->promo_price>0){
                            $giaban=$product->promo_price;
                        }
                    }
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
                   
                $giaban=$this->sma->formatMoney($giaban);

                if((int)$_str_soluong>0){
                    if($phan_du!=0){
                        $stock=$donvi_cha['name'].": <b>".(float)$phan_nguyen."</b> - ".$donvi['name'].":".round($_str_phandu,2);     
                    }else{
                        $stock=$donvi_cha['name'].": <b>".(float)$_str_soluong."</b>";
                    }                   
                }else{                  
                    $stock=$objunit['name'].": <b>".(float)$stock."</b>";                   
                }
                $product->comment=$stock;
                
                //quy doi ve don vi ban le neu co
				
				if($this->Settings->show_img_pos==1)
                {
				
					$prods .= "<button id=\"product-" . $category_id . $count . "\" type=\"button\" value='" . $product->code . "' title=\"" . $product->name . "\" class=\"btn-prni btn-" . $this->pos_settings->product_button_color . " product pos-tip\" data-container=\"body\">										<img src=\"" . base_url() . "assets/uploads/" . $product->image . "\" alt=\"" . $product->name . "\" style='width:" . $this->Settings->twidth . "px;height:" . $this->Settings->theight . "px;' class='img-rounded' />										<span>" . character_limiter($product->name, 40) . "</span><div class='mapossp'>".$product->comment. " - <b>".$giaban."</b></div></button>";
				}else
                {					
					$prods .= "<button id=\"product-" . $category_id . $count . "\" type=\"button\" value='" . $product->code . "' title=\"" . $product->name . "\" class=\"btn-prni btn-" . $this->pos_settings->product_button_color . " product pos-no-img pos-tip\" data-container=\"body\"><span>" . character_limiter($product->name, 40) . "</span><div class='mapossp'>".$product->comment . " - <b>".$giaban."</b></div></button>";				
				}
                $pro++;
            }
        }
        $prods .= "</div>";

        if ($this->input->get('per_page')) {
            echo $prods;
        } else {
            return $prods;
        }
    }

    public function ajaxcategorydata($category_id = NULL)
    {
        $this->sma->checkPermissions('index');
        if ($this->input->get('category_id')) {
            $category_id = $this->input->get('category_id');
        } else {
            $category_id = $this->pos_settings->default_category;
        }

        $subcategories = $this->site->getSubCategories($category_id);
        $scats = '';
        $scats .= "<button id=\"subcategory-0\" type=\"button\" value='0' class=\"btn-prni subcategory\" ><img src=\"assets/uploads/thumbs/no_image.png\" style='width:" . $this->Settings->twidth . "px;height:" . $this->Settings->theight . "px;' class='img-rounded img-thumbnail' /><span>Tất c</span></button>";
        if ($subcategories) {
            foreach ($subcategories as $category) {
                $scats .= "<button id=\"subcategory-" . $category->id . "\" type=\"button\" value='" . $category->id . "' class=\"btn-prni subcategory\" ><img src=\"assets/uploads/thumbs/" . ($category->image ? $category->image : 'no_image.png') . "\" style='width:" . $this->Settings->twidth . "px;height:" . $this->Settings->theight . "px;' class='img-rounded img-thumbnail' /><span>" . $category->name . "</span></button>";
            }
        }

        $products = $this->ajaxproducts($category_id);

        if (!($tcp = $this->pos_model->products_count($category_id))) {
            $tcp = 0;
        }

        $this->sma->send_json(array('products' => $products, 'subcategories' => $scats, 'tcp' => $tcp));
    }

    public function ajaxbranddata($brand_id = NULL)
    {
        $this->sma->checkPermissions('index');
        if ($this->input->get('brand_id')) {
            $brand_id = $this->input->get('brand_id');
        }

        $products = $this->ajaxproducts(FALSE, $brand_id);

        if (!($tcp = $this->pos_model->products_count(FALSE, FALSE, $brand_id))) {
            $tcp = 0;
        }

        $this->sma->send_json(array('products' => $products, 'tcp' => $tcp));
    }

    /* ------------------------------------------------------------------------------------ */

    public function viewbk($sale_id = NULL, $modal = NULL)
    {
        $this->sma->checkPermissions('index');
        if ($this->input->get('id')) {
            $sale_id = $this->input->get('id');
        }
        $this->load->helper('pos');
        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['message'] = $this->session->flashdata('message');
        $inv = $this->pos_model->getInvoiceByID($sale_id);
        if (!$this->session->userdata('view_right')) {
            $this->sma->view_rights($inv->created_by, true);
        }
        $this->data['rows'] = $rows=$this->pos_model->getAllInvoiceItems($sale_id);
        $biller_id = $inv->biller_id;
        $customer_id = $inv->customer_id;
        $this->data['biller'] = $biller=$this->pos_model->getCompanyByID($biller_id);
        $this->data['customer'] = $customer=$this->pos_model->getCompanyByID($customer_id);
        $this->data['payments'] = $payments=$this->pos_model->getInvoicePayments($sale_id);
        $this->data['pos'] =$pos= $this->pos_model->getSetting();
        $this->data['barcode'] =$barcode= $this->barcode($inv->reference_no, 'code128', 30);
        $this->data['return_sale'] = $return_sale=$inv->return_id ? $this->pos_model->getInvoiceByID($inv->return_id) : NULL;
        $this->data['return_rows'] = $return_rows=$inv->return_id ? $this->pos_model->getAllInvoiceItems($inv->return_id) : NULL;
        $this->data['return_payments'] = $return_payments=$this->data['return_sale'] ? $this->pos_model->getInvoicePayments($this->data['return_sale']->id) : NULL;
		$this->data['warehouse'] = $warehouse=$this->site->getWarehouseByID($inv->warehouse_id); 
        $this->data['inv'] = $inv;
        $this->data['sid'] = $sale_id;
        $this->data['modal'] = $modal;
        $this->data['created_by'] = $created_by=$this->site->getUser($inv->created_by);
        $this->data['printer'] = $printer=$this->pos_model->getPrinterByID($this->pos_settings->printer);
        $this->data['page_title'] = $this->lang->line("invoice");	
		
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
            if ($row->data_id_khuyenmai>0) {
                //get event name 
                $obj_km=$this->site->getKhuyenmaiById($row->data_id_khuyenmai);
                if ($obj_km!=false) {
                    $_prod_detail=$row->product_name." <i>(".$obj_km->tenevent.")</i>";    
                }else{
                    $_prod_detail=$row->product_name." <i>(KHUYẾN MÃI)</i>";
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
	    $_tablhd_pos.='</tbody><tfoot>'; 

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
		$_tongcong_bang_chu_text=$this->convert_number_to_words($_tongcong_bang_chu);
        
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
				
		$_tablhd.="</table>";
        $_tablhd_pos.="</table>";
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

		$this->data['doitac'] = $doitac=$this->doitac_model->getDoitacByID($inv->doitac);
		
		$parse_data = array('Ma_Don_Hang' => $inv->reference_no,'Khach_Hang' => $customer->name,'CongTy_KH' =>$customer->company,'MST_KH' =>$customer->vat_no,'site_link' => base_url(),'site_name' => $this->Settings->site_name,'logo' => '<img src="' . base_url() . 'assets/uploads/logos/' . $biller->logo . '" alt="' . ($biller->company != '-' ? $biller->company : $biller->name) . '"/>','Ten_Kho' => $warehouse->name,'Dia_Chi_Kho' =>$_dc_cuahang,'SDT_Kho' =>$warehouse->phone,'Dia_chi_kh' => $customer->address,'Dien_thoai_kh' => $customer->phone,'Ngay_Xuat' => $this->sma->hrld($inv->date),'Tong_Thanh_Toan' =>$_tongcong,'Nhan_Vien_Thu_Ngan' =>$created_by->first_name . ' ' . $created_by->last_name,'Phu_Phi' => $this->sma->formatMoney($inv->shipping),'Ghi_Chu' =>$this->sma->decode_html($inv->note),'Ghi_Chu_NV' =>$this->sma->decode_html($inv->staff_note),'No_cu' =>$_tong_no_cu,'Chua_Thanh_Toan' => $_chuathanhtoan,'Da_Thanh_Toan' => $tong_dathanhtoan,'Tong_Diem_Tich_Luy' =>$_tong_diem,'Diem_hoa_don' =>$_diem_thuong,'Giam_Gia_Tren_Hoa_Don' =>$_giamgia,'Tong_Tien_Hang' =>$tongcong,'Tong_Tien_Bang_Chu' =>$_tongcong_bang_chu_text,'Tong_thu_hoi' =>$_tongthuhoi,'THUE' =>$_tongthue,'Bang_Hoa_Don' =>$_tablhd,'Bang_Hoa_Don_POS' =>$_tablhd_pos,'Tong_No'=>$tong_no_all,'Ma_Doi_Tac' => $doitac->code,'Ten_Doi_Tac' => $doitac->name,'Dia_Chi_Doi_Tac' => $doitac->diachi,'Email_Doi_Tac' => $doitac->email,'Dien_Thoai_Doi_Tac' => $doitac->dienthoai,'No_Dau_Ky_Doi_Tac' => $doitac->nodauky);    
		
		
		if (file_exists('./themes/' . $this->theme . '/views/print_templates/printpos.html')) {          
			$sale_temp = file_get_contents('themes/' . $this->theme . '/views/print_templates/printpos.html');            
		} else {             
			$sale_temp = file_get_contents('./themes/default/views/print_templates/printpos.html');          
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
		
        $this->load->view($this->theme . 'pos/view', $this->data);
    }
	public function view($sale_id = NULL, $modal = NULL)
    {
        $this->sma->checkPermissions('index');
        if ($this->input->get('id')) {
            $sale_id = $this->input->get('id');
        }
        $this->load->helper('pos');
        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['message'] = $this->session->flashdata('message');
        $inv = $this->pos_model->getInvoiceByID($sale_id);
        if (!$this->session->userdata('view_right')) {
            $this->sma->view_rights($inv->created_by, true);
        }
        $this->data['rows'] = $rows=$this->pos_model->getAllInvoiceItems($sale_id);
        $biller_id = $inv->biller_id;
        $customer_id = $inv->customer_id;
        $this->data['biller'] = $biller=$this->pos_model->getCompanyByID($biller_id);
        $this->data['customer'] = $customer=$this->pos_model->getCompanyByID($customer_id);
        $this->data['payments'] = $payments=$this->pos_model->getInvoicePayments($sale_id);
        $this->data['pos'] =$pos= $this->pos_model->getSetting();
        $this->data['barcode'] =$barcode= $this->barcode($inv->reference_no, 'code128', 30);
        $this->data['return_sale'] = $return_sale=$inv->return_id ? $this->pos_model->getInvoiceByID($inv->return_id) : NULL;
        $this->data['return_rows'] = $return_rows=$inv->return_id ? $this->pos_model->getAllInvoiceItems($inv->return_id) : NULL;
        $this->data['return_payments'] = $return_payments=$this->data['return_sale'] ? $this->pos_model->getInvoicePayments($this->data['return_sale']->id) : NULL;
        $this->data['warehouse'] = $warehouse=$this->site->getWarehouseByID($inv->warehouse_id); 
        $this->data['inv'] = $inv;
        $this->data['sid'] = $sale_id;
        $this->data['modal'] = $modal;
        $this->data['created_by'] = $created_by=$this->site->getUser($inv->created_by);
        $this->data['printer'] = $printer=$this->pos_model->getPrinterByID($this->pos_settings->printer);
        $this->data['page_title'] = $this->lang->line("invoice");   
        
        $_dc_cuahang=str_replace("<p>","",$warehouse->address);       
        $_dc_cuahang=str_replace("</p>","",$_dc_cuahang);

        $_tablhd='<table border="1" style="width:100%;border-collapse:collapse;">        <tbody>        <tr>            <td style="text-align:center;width:5%;"><strong>STT</strong>            </td>            <td style="width:51%;padding-left:0.5%;"><strong>Tên hàng hóa</strong><br>            </td>            <td style="text-align:right;padding-right:0.5%;width:12%;"><strong>Đơn giá</strong><br>            </td>            <td style="text-align:center;width:15%;"><strong>SL</strong><br>            </td>            <td style="text-align:right;padding-right:0.5%;width:15.5%;"><strong>Thành tiền</strong><br>            </td>        </tr>';
        
        $_tablhd_pos='<table border="0" style="width:100%;border-collapse:collapse;font-size:12px;margin:5px 0px;">        <tbody>        <tr style="border-bottom: 1px solid;">            <td style="text-align:center;width:5%;"><strong>STT</strong>            </td>            <td style="width:63.5%;padding-left:0.5%;"><strong>Tên - Giá</strong><br>            </td> <td style="text-align:center;width:15%;"><strong>SL</strong><br>            </td>            <td style="text-align:right;padding-right:0.5%;width:15.5%;"><strong>T. Tiền</strong><br>            </td>        </tr>';

        
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
            if ($row->data_id_khuyenmai>0) {
                //get event name 
                $obj_km=$this->site->getKhuyenmaiById($row->data_id_khuyenmai);
                if ($obj_km!=false) {
                    $_prod_detail=$row->product_name." <i>(".$obj_km->tenevent.")</i>";    
                }else{
                    $_prod_detail=$row->product_name." <i>(KHUYẾN MÃI)</i>";
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

            $_tablhd.='<tr><td style="text-align:center;vertical-align:middle;">'.$r.'</td><td style="vertical-align:middle;">'.$_prod_detail.'</td><td style="text-align:right; padding-right:10px;">'.$_strgia.'</td><td style="text-align:center; vertical-align:middle;">'.$this->sma->formatQuantity($row->unit_quantity).' '.$row->product_unit_code.'</td><td style="text-align:right; width:120px; padding-right:10px;">'.$this->sma->formatMoney($row->subtotal).'</td>            </tr>'; 
             $_tablhd_pos.='<tr><td rowspan="2" style="text-align:center;vertical-align:middle;">'.$r.'</td>
                <td colspan="4" style="vertical-align:middle;">'.$_prod_detail.'</td>            
            </tr>
            <tr style="border-bottom: 1px solid;line-height: 30px;">
                <td style="text-align:left; padding-right:10px;">'.$_strgia.'</td>
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

               

                 $_tablhd_pos.='<tr><td rowspan="2" style="text-align:center;vertical-align:middle;">'.$r.'</td>
                    <td colspan="4" style="vertical-align:middle;">'.$_rt_prod.'</td>            
                </tr>
                <tr style="border-bottom: 1px solid;line-height: 25px;">
                    <td style="text-align:left; padding-right:10px;">'.$row->net_unit_price.'</td>
                    <td style="text-align:center; vertical-align:middle;">'.$this->sma->formatQuantity($row->quantity).'</td>
                    <td style="text-align:right; width:120px; padding-right:5px;">'.$this->sma->formatMoney($row->subtotal).'</td>            
                </tr>';  
               
                $r++;
            }
        }
        
        $_tablhd.='</tbody><tfoot>';
        $_tablhd_pos.='</tbody><tfoot>'; 

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
        $_tongcong_bang_chu_text=$this->convert_number_to_words($_tongcong_bang_chu);
        
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
                
        $_tablhd.="</table>";
        $_tablhd_pos.="</table>";
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

        $this->data['doitac'] = $doitac=$this->doitac_model->getDoitacByID($inv->doitac);
        
        $parse_data = array('Ma_Don_Hang' => $inv->reference_no,'Khach_Hang' => $customer->name,'CongTy_KH' =>$customer->company,'MST_KH' =>$customer->vat_no,'site_link' => base_url(),'site_name' => $this->Settings->site_name,'logo' => '<img src="' . base_url() . 'assets/uploads/logos/' . $biller->logo . '" alt="' . ($biller->company != '-' ? $biller->company : $biller->name) . '"/>','Ten_Kho' => $warehouse->name,'Dia_Chi_Kho' =>$_dc_cuahang,'SDT_Kho' =>$warehouse->phone,'Dia_chi_kh' => $customer->address,'Dien_thoai_kh' => $customer->phone,'Ngay_Xuat' => $this->sma->hrld($inv->date),'Tong_Thanh_Toan' =>$_tongcong,'Nhan_Vien_Thu_Ngan' =>$created_by->first_name . ' ' . $created_by->last_name,'Phu_Phi' => $this->sma->formatMoney($inv->shipping),'Ghi_Chu' =>$this->sma->decode_html($inv->note),'Ghi_Chu_NV' =>$this->sma->decode_html($inv->staff_note),'No_cu' =>$_tong_no_cu,'Chua_Thanh_Toan' => $_chuathanhtoan,'Da_Thanh_Toan' => $tong_dathanhtoan,'Tong_Diem_Tich_Luy' =>$_tong_diem,'Diem_hoa_don' =>$_diem_thuong,'Giam_Gia_Tren_Hoa_Don' =>$_giamgia,'Tong_Tien_Hang' =>$tongcong,'Tong_Tien_Bang_Chu' =>$_tongcong_bang_chu_text,'Tong_thu_hoi' =>$_tongthuhoi,'THUE_NO' =>$_thue_no,'THUE' =>$_tongthue,'Bang_Hoa_Don' =>$_tablhd,'Bang_Hoa_Don_POS' =>$_tablhd_pos,'Tong_No'=>$tong_no_all,'Ma_Doi_Tac' => $doitac->code,'Ten_Doi_Tac' => $doitac->name,'Dia_Chi_Doi_Tac' => $doitac->diachi,'Email_Doi_Tac' => $doitac->email,'Dien_Thoai_Doi_Tac' => $doitac->dienthoai,'No_Dau_Ky_Doi_Tac' => $doitac->nodauky);    
        
        
        if (file_exists('./themes/' . $this->theme . '/views/print_templates/printpos.html')) {          
            $sale_temp = file_get_contents('themes/' . $this->theme . '/views/print_templates/printpos.html');            
        } else {             
            $sale_temp = file_get_contents('./themes/default/views/print_templates/printpos.html');          
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
        
        $this->load->view($this->theme . 'pos/view', $this->data);
    }
	function get_tong_no(){				
		$user_id=(int)$this->uri->segment('3');	
		$_dathanhtoan= $this->reports_model->getSalesTotals($user_id);	
		
		$company_details = $this->companies_model->getCompanyByID($user_id);	
		
		$no_cu=(float)$_dathanhtoan->total_amount - (float)$_dathanhtoan->paid + (float)$company_details->nobandau;	
		echo $this->sma->formatMoney($no_cu);	
		exit();
	}
    public function register_details()
    {
        $this->sma->checkPermissions('index');
        $register_open_time = $this->session->userdata('register_open_time');
        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['ccsales'] = $this->pos_model->getRegisterCCSales($register_open_time);
        $this->data['cashsales'] = $this->pos_model->getRegisterCashSales($register_open_time);
        $this->data['chsales'] = $this->pos_model->getRegisterChSales($register_open_time);
        $this->data['gcsales'] = $this->pos_model->getRegisterGCSales($register_open_time);
        $this->data['pppsales'] = $this->pos_model->getRegisterPPPSales($register_open_time);
        $this->data['stripesales'] = $this->pos_model->getRegisterStripeSales($register_open_time);
        $this->data['authorizesales'] = $this->pos_model->getRegisterAuthorizeSales($register_open_time);
        $this->data['totalsales'] = $this->pos_model->getRegisterSales($register_open_time);
        $this->data['refunds'] = $this->pos_model->getRegisterRefunds($register_open_time);
        $this->data['expenses'] = $this->pos_model->getRegisterExpenses($register_open_time);
        $this->load->view($this->theme . 'pos/register_details', $this->data);
    }

    public function today_sale()
    {
        if (!$this->Owner && !$this->Admin) {
            $this->session->set_flashdata('error', lang('access_denied'));
            $this->sma->md();
        }

        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['ccsales'] = $this->pos_model->getTodayCCSales();
        $this->data['cashsales'] = $this->pos_model->getTodayCashSales();
        $this->data['chsales'] = $this->pos_model->getTodayChSales();
        $this->data['pppsales'] = $this->pos_model->getTodayPPPSales();
        $this->data['stripesales'] = $this->pos_model->getTodayStripeSales();
        $this->data['authorizesales'] = $this->pos_model->getTodayAuthorizeSales();
        $this->data['totalsales'] = $this->pos_model->getTodaySales();
        $this->data['refunds'] = $this->pos_model->getTodayRefunds();
        $this->data['expenses'] = $this->pos_model->getTodayExpenses();
        $this->load->view($this->theme . 'pos/today_sale', $this->data);
    }

    public function check_pin()
    {
        $pin = $this->input->post('pw', TRUE);
        if ($pin == $this->pos_pin) {
            $this->sma->send_json(array('res' => 1));
        }
        $this->sma->send_json(array('res' => 0));
    }

    public function barcode($text = NULL, $bcs = 'code128', $height = 50)
    {
        return site_url('products/gen_barcode/' . $text . '/' . $bcs . '/' . $height);
    }

    public function settings()
    {
        if (!$this->Owner) {
            $this->session->set_flashdata('error', lang('access_denied'));
            redirect("welcome");
        }
        $this->form_validation->set_message('is_natural_no_zero', $this->lang->line('no_zero_required'));
        $this->form_validation->set_rules('pro_limit', $this->lang->line('pro_limit'), 'required|is_natural_no_zero');
        $this->form_validation->set_rules('pin_code', $this->lang->line('delete_code'), 'numeric');
        $this->form_validation->set_rules('category', $this->lang->line('default_category'), 'required|is_natural_no_zero');
        $this->form_validation->set_rules('customer', $this->lang->line('default_customer'), 'required|is_natural_no_zero');
        $this->form_validation->set_rules('biller', $this->lang->line('default_biller'), 'required|is_natural_no_zero');

        if ($this->form_validation->run() == TRUE) {

            $data = array(
                'pro_limit'                 => $this->input->post('pro_limit'),
                'pin_code'                  => $this->input->post('pin_code') ? $this->input->post('pin_code') : NULL,
                'default_category'          => $this->input->post('category'),
                'default_customer'          => $this->input->post('customer'),
                'default_biller'            => $this->input->post('biller'),
                'display_time'              => $this->input->post('display_time'),
                'receipt_printer'           => $this->input->post('receipt_printer'),
                'cash_drawer_codes'         => $this->input->post('cash_drawer_codes'),
                'cf_title1'                 => $this->input->post('cf_title1'),
                'cf_title2'                 => $this->input->post('cf_title2'),
                'cf_value1'                 => $this->input->post('cf_value1'),
                'cf_value2'                 => $this->input->post('cf_value2'),
                'focus_add_item'            => $this->input->post('focus_add_item'),
                'add_manual_product'        => $this->input->post('add_manual_product'),
                'customer_selection'        => $this->input->post('customer_selection'),
                'add_customer'              => $this->input->post('add_customer'),
                'toggle_category_slider'    => $this->input->post('toggle_category_slider'),
                'toggle_subcategory_slider' => $this->input->post('toggle_subcategory_slider'),
                'toggle_brands_slider'      => $this->input->post('toggle_brands_slider'),
                'cancel_sale'               => $this->input->post('cancel_sale'),
                'suspend_sale'              => $this->input->post('suspend_sale'),
                'print_items_list'          => $this->input->post('print_items_list'),
                'finalize_sale'             => $this->input->post('finalize_sale'),
                'today_sale'                => $this->input->post('today_sale'),
                'open_hold_bills'           => $this->input->post('open_hold_bills'),
                'close_register'            => $this->input->post('close_register'),
                'tooltips'                  => $this->input->post('tooltips'),
                'keyboard'                  => $this->input->post('keyboard'),
                'pos_printers'              => $this->input->post('pos_printers'),
                'java_applet'               => $this->input->post('enable_java_applet'),
                'product_button_color'      => $this->input->post('product_button_color'),
                'paypal_pro'                => $this->input->post('paypal_pro'),
                'stripe'                    => $this->input->post('stripe'),
                'authorize'                 => $this->input->post('authorize'),
                'rounding'                  => $this->input->post('rounding'),
                'item_order'                => $this->input->post('item_order'),
                'after_sale_page'           => $this->input->post('after_sale_page'),
                'printer'                   => $this->input->post('receipt_printer'),
                'order_printers'            => json_encode($this->input->post('order_printers')),
                'auto_print'                => $this->input->post('auto_print'),
                'remote_printing'           => DEMO ? 1 : $this->input->post('remote_printing'),
                'customer_details'          => $this->input->post('customer_details'),
            );
            $payment_config = array(
                'APIUsername'            => $this->input->post('APIUsername'),
                'APIPassword'            => $this->input->post('APIPassword'),
                'APISignature'           => $this->input->post('APISignature'),
                'stripe_secret_key'      => $this->input->post('stripe_secret_key'),
                'stripe_publishable_key' => $this->input->post('stripe_publishable_key'),
                'api_login_id'           => $this->input->post('api_login_id'),
                'api_transaction_key'    => $this->input->post('api_transaction_key'),
            );
        } elseif ($this->input->post('update_settings')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect("pos/settings");
        }

        if ($this->form_validation->run() == TRUE && $this->pos_model->updateSetting($data)) {
            if (DEMO) {
                $this->session->set_flashdata('message', $this->lang->line('pos_setting_updated'));
                redirect("pos/settings");
            }
            if ($this->write_payments_config($payment_config)) {
                $this->session->set_flashdata('message', $this->lang->line('pos_setting_updated'));
                redirect("pos/settings");
            } else {
                $this->session->set_flashdata('error', $this->lang->line('pos_setting_updated_payment_failed'));
                redirect("pos/settings");
            }
        } else {

            $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');

            $this->data['pos'] = $this->pos_model->getSetting();
            $this->data['categories'] = $this->site->getAllCategories();
            //$this->data['customer'] = $this->pos_model->getCompanyByID($this->pos_settings->default_customer);
            $this->data['billers'] = $this->pos_model->getAllBillerCompanies();
            $this->config->load('payment_gateways');
            $this->data['stripe_secret_key'] = $this->config->item('stripe_secret_key');
            $this->data['stripe_publishable_key'] = $this->config->item('stripe_publishable_key');
            $authorize = $this->config->item('authorize');
            $this->data['api_login_id'] = $authorize['api_login_id'];
            $this->data['api_transaction_key'] = $authorize['api_transaction_key'];
            $this->data['APIUsername'] = $this->config->item('APIUsername');
            $this->data['APIPassword'] = $this->config->item('APIPassword');
            $this->data['APISignature'] = $this->config->item('APISignature');
            $this->data['printers'] = $this->pos_model->getAllPrinters();
            $this->data['paypal_balance'] = NULL; // $this->pos_settings->paypal_pro ? $this->paypal_balance() : NULL;
            $this->data['stripe_balance'] = NULL; // $this->pos_settings->stripe ? $this->stripe_balance() : NULL;
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('pos_settings')));
            $meta = array('page_title' => lang('pos_settings'), 'bc' => $bc);
            $this->page_construct('pos/settings', $meta, $this->data);
        }
    }

    public function write_payments_config($config)
    {
        if (!$this->Owner) {
            $this->session->set_flashdata('error', lang('access_denied'));
            redirect("welcome");
        }
        if (DEMO) {
            return TRUE;
        }
        $file_contents = file_get_contents('./assets/config_dumps/payment_gateways.php');
        $output_path = APPPATH . 'config/payment_gateways.php';
        $this->load->library('parser');
        $parse_data = array(
            'APIUsername'            => $config['APIUsername'],
            'APIPassword'            => $config['APIPassword'],
            'APISignature'           => $config['APISignature'],
            'stripe_secret_key'      => $config['stripe_secret_key'],
            'stripe_publishable_key' => $config['stripe_publishable_key'],
            'api_login_id'           => $config['api_login_id'],
            'api_transaction_key'    => $config['api_transaction_key'],
        );
        $new_config = $this->parser->parse_string($file_contents, $parse_data);

        $handle = fopen($output_path, 'w+');
        @chmod($output_path, 0777);

        if (is_writable($output_path)) {
            if (fwrite($handle, $new_config)) {
                @chmod($output_path, 0644);

                return TRUE;
            } else {
                return FALSE;
            }
        } else {
            return FALSE;
        }
    }

    public function opened_bills($per_page = 0)
    {
        $this->load->library('pagination');

        //$this->table->set_heading('Id', 'The Title', 'The Content');
        if ($this->input->get('per_page')) {
            $per_page = $this->input->get('per_page');
        }

        $config['base_url'] = site_url('pos/opened_bills');
        $config['total_rows'] = $this->pos_model->bills_count();
        $config['per_page'] = 6;
        $config['num_links'] = 3;

        $config['full_tag_open'] = '<ul class="pagination pagination-sm">';
        $config['full_tag_close'] = '</ul>';
        $config['first_tag_open'] = '<li>';
        $config['first_tag_close'] = '</li>';
        $config['last_tag_open'] = '<li>';
        $config['last_tag_close'] = '</li>';
        $config['next_tag_open'] = '<li>';
        $config['next_tag_close'] = '</li>';
        $config['prev_tag_open'] = '<li>';
        $config['prev_tag_close'] = '</li>';
        $config['num_tag_open'] = '<li>';
        $config['num_tag_close'] = '</li>';
        $config['cur_tag_open'] = '<li class="active"><a>';
        $config['cur_tag_close'] = '</a></li>';

        $this->pagination->initialize($config);
        $data['r'] = TRUE;
        $bills = $this->pos_model->fetch_bills($config['per_page'], $per_page);
        if (!empty($bills)) {
            $html = "";
            $html .= '<ul class="ob">';
            foreach ($bills as $bill) {
                $html .= '<li><button type="button" class="btn btn-info sus_sale" id="' . $bill->id . '"><p>' . $bill->suspend_note . '</p><strong>' . $bill->customer . '</strong><br>'.lang('date').': ' . $bill->date . '<br>'.lang('items').': ' . $bill->count . '<br>'.lang('total').': ' . $this->sma->formatMoney($bill->total) . '</button></li>';
            }
            $html .= '</ul>';
        } else {
            $html = "<h3>" . lang('no_opeded_bill') . "</h3><p>&nbsp;</p>";
            $data['r'] = FALSE;
        }

        $data['html'] = $html;

        $data['page'] = $this->pagination->create_links();
        echo $this->load->view($this->theme . 'pos/opened', $data, TRUE);

    }

    public function delete($id = NULL)
    {

        $this->sma->checkPermissions('index');

        if ($this->pos_model->deleteBill($id)) {
            $this->sma->send_json(array('error' => 0, 'msg' => lang("suspended_sale_deleted")));
        }
    }

    public function email_receipt($sale_id = NULL)
    {
        $this->sma->checkPermissions('index');
        if ($this->input->post('id')) {
            $sale_id = $this->input->post('id');
        } 
        if ( ! $sale_id) {
            die('No sale selected.');
        }
        if ($this->input->post('email')) {
            $to = $this->input->post('email');
        }
        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['message'] = $this->session->flashdata('message');

        $this->data['rows'] = $this->pos_model->getAllInvoiceItems($sale_id);
        $inv = $this->pos_model->getInvoiceByID($sale_id);
        $biller_id = $inv->biller_id;
        $customer_id = $inv->customer_id;
        $this->data['biller'] = $this->pos_model->getCompanyByID($biller_id);
        $this->data['customer'] = $this->pos_model->getCompanyByID($customer_id);

        $this->data['payments'] = $this->pos_model->getInvoicePayments($sale_id);
        $this->data['pos'] = $this->pos_model->getSetting();
        $this->data['barcode'] = $this->barcode($inv->reference_no, 'code128', 30);
        $this->data['inv'] = $inv;
        $this->data['sid'] = $sale_id;
        $this->data['page_title'] = $this->lang->line("invoice");

        if (!$to) {
            $to = $this->data['customer']->email;
        }
        if (!$to) {
            $this->sma->send_json(array('msg' => $this->lang->line("no_meil_provided")));
        }
        $receipt = $this->load->view($this->theme . 'pos/email_receipt', $this->data, TRUE);

        if ($this->sma->send_email($to, 'Receipt from ' . $this->data['biller']->company, $receipt)) {
            $this->sma->send_json(array('msg' => $this->lang->line("email_sent")));
        } else {
            $this->sma->send_json(array('msg' => $this->lang->line("email_failed")));
        }

    }

    public function active()
    {
        $this->session->set_userdata('last_activity', now());
        if ((now() - $this->session->userdata('last_activity')) <= 20) {
            die('Successfully updated the last activity.');
        } else {
            die('Failed to update last activity.');
        }
    }

    public function add_payment($id = NULL)
    {
        $this->sma->checkPermissions('payments', TRUE, 'sales');
        $this->load->helper('security');
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        $this->form_validation->set_rules('reference_no', lang("reference_no"), 'required');
        $this->form_validation->set_rules('amount-paid', lang("amount"), 'required');
        $this->form_validation->set_rules('paid_by', lang("paid_by"), 'required');
        $this->form_validation->set_rules('userfile', lang("attachment"), 'xss_clean');
        $sale = $this->pos_model->getInvoiceByID($this->input->post('sale_id'));
        if ($this->form_validation->run() == TRUE) {
            if ($this->input->post('paid_by') == 'deposit') {
                
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
            $customer_details = $this->site->getCompanyByID($sale->customer_id);

            $payment = array(
                'date'         => $date,
                'sale_id'      => $this->input->post('sale_id'),
                'reference_no' => $this->input->post('reference_no'),
                'amount'       => $this->input->post('amount-paid'),
                'paid_by'      => $this->input->post('paid_by'),
                'cheque_no'    => $this->input->post('cheque_no'),
                'cc_no'        => $this->input->post('paid_by') == 'gift_card' ? $this->input->post('gift_card_no') : $this->input->post('pcc_no'),
                'cc_holder'    => $this->input->post('pcc_holder'),
                'cc_month'     => $this->input->post('pcc_month'),
                'cc_year'      => $this->input->post('pcc_year'),
                'cc_type'      => $this->input->post('pcc_type'),
                'cc_cvv2'      => $this->input->post('pcc_ccv'),
                'note'         => $this->input->post('note'),
                'created_by'   => $this->session->userdata('user_id'),
                'warehouse_id'   => $sale->warehouse_id,
                'type'         => 'received',
                'c_name' => $customer_details->name,
                'c_phone' => $customer_details->phone,
                'c_address' => $customer_details->address,
            );

            if ($_FILES['userfile']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] = $this->digital_upload_path;
                $config['allowed_types'] = $this->digital_file_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = FALSE;
                $config['encrypt_name'] = TRUE;
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

        if ($this->form_validation->run() == TRUE && $msg = $this->pos_model->addPayment($payment, $customer_id)) {
            if ($msg) {
                if ($msg['status'] == 0) {
                    unset($msg['status']);
                    $error = '';
                    foreach ($msg as $m) {
                        if (is_array($m)) {
                            foreach ($m as $e) {
                                $error .= '<br>'.$e;
                            }
                        } else {
                            $error .= '<br>'.$m;
                        }
                    }
                    $this->session->set_flashdata('error', '<pre>' . $error . '</pre>');
                } else {
                    $this->session->set_flashdata('message', lang("payment_added"));
                }
            } else {
                $this->session->set_flashdata('error', lang("payment_failed"));
            }
            redirect("pos/sales");
        } else {

            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

            $sale = $this->pos_model->getInvoiceByID($id);
            $this->data['inv'] = $sale;
            $this->data['payment_ref'] = $this->site->getReference('pay');
            $this->data['modal_js'] = $this->site->modal_js();

            $this->load->view($this->theme . 'pos/add_payment', $this->data);
        }
    }

    public function updates()
    {
        if (DEMO) {
            $this->session->set_flashdata('warning', lang('disabled_in_demo'));
            redirect($_SERVER["HTTP_REFERER"]);
        }
        if (!$this->Owner) {
            $this->session->set_flashdata('error', lang('access_denied'));
            redirect("welcome");
        }
        $this->form_validation->set_rules('purchase_code', lang("purchase_code"), 'required');
        $this->form_validation->set_rules('scodeweb_username', lang("scodeweb_username"), 'required');
        if ($this->form_validation->run() == TRUE) {
            $this->db->update('pos_settings', array('purchase_code' => $this->input->post('purchase_code', TRUE), 'scodeweb_username' => $this->input->post('scodeweb_username', TRUE)), array('pos_id' => 1));
            redirect('pos/updates');
        } else {
            $fields = array('version' => $this->pos_settings->version, 'code' => $this->pos_settings->purchase_code, 'username' => $this->pos_settings->scodeweb_username, 'site' => base_url());
            $this->load->helper('update');
            $protocol = is_https() ? 'https://' : 'http://';
            $updates = get_remote_contents($protocol . 'scodeweb.com/api/v1/update/', $fields);
            $this->data['updates'] = json_decode($updates);
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('updates')));
            $meta = array('page_title' => lang('updates'), 'bc' => $bc);
            $this->page_construct('pos/updates', $meta, $this->data);
        }
    }

    public function install_update($file, $m_version, $version)
    {
        if (DEMO) {
            $this->session->set_flashdata('warning', lang('disabled_in_demo'));
            redirect($_SERVER["HTTP_REFERER"]);
        }
        if (!$this->Owner) {
            $this->session->set_flashdata('error', lang('access_denied'));
            redirect("welcome");
        }
        $this->load->helper('update');
        save_remote_file($file . '.zip');
        $this->sma->unzip('./files/updates/' . $file . '.zip');
        if ($m_version) {
            $this->load->library('migration');
            if (!$this->migration->latest()) {
                $this->session->set_flashdata('error', $this->migration->error_string());
                redirect("pos/updates");
            }
        }
        $this->db->update('pos_settings', array('version' => $version, 'update' => 0), array('pos_id' => 1));
        unlink('./files/updates/' . $file . '.zip');
        $this->session->set_flashdata('success', lang('update_done'));
        redirect("pos/updates");
    }

    function open_drawer() {

        $data = json_decode($this->input->get('data'));
        $this->load->library('escpos');
        $this->escpos->load($data->printer);
        $this->escpos->open_drawer();

    }

    function p() {

        $data = json_decode($this->input->get('data'));
        $this->load->library('escpos');
        $this->escpos->load($data->printer);
        $this->escpos->print_receipt($data);

    }

    function printers()
    {
        if (!$this->Owner) {
            $this->session->set_flashdata('error', lang('access_denied'));
            redirect("pos");
        }
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['page_title'] = lang('printers');
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('pos'), 'page' => lang('pos')), array('link' => '#', 'page' => lang('printers')));
        $meta = array('page_title' => lang('list_printers'), 'bc' => $bc);
        $this->page_construct('pos/printers', $meta, $this->data);
    }

    function get_printers()
    {
        if (!$this->Owner) {
            $this->session->set_flashdata('error', lang('access_denied'));
            $this->sma->md();
        }

        $this->load->library('datatables');
        $this->datatables
        ->select("id, title, type, profile, path, ip_address, port")
        ->from("printers")
        ->add_column("Actions", "<div class='text-center'> <a href='" . site_url('pos/edit_printer/$1') . "' class='btn-warning btn-xs tip' title='".lang("edit_printer")."'><i class='fa fa-edit'></i></a> <a href='#' class='btn-danger btn-xs tip po' title='<b>" . lang("delete_printer") . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('pos/delete_printer/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></div>", "id")
        ->unset_column('id');
        echo $this->datatables->generate();

    }

    function add_printer()
    {

        if (!$this->Owner) {
            $this->session->set_flashdata('error', lang('access_denied'));
            redirect("pos");
        }

        $this->form_validation->set_rules('title', $this->lang->line("title"), 'required');
        $this->form_validation->set_rules('type', $this->lang->line("type"), 'required');
        $this->form_validation->set_rules('profile', $this->lang->line("profile"), 'required');
        $this->form_validation->set_rules('char_per_line', $this->lang->line("char_per_line"), 'required');
        if ($this->input->post('type') == 'network') {
            $this->form_validation->set_rules('ip_address', $this->lang->line("ip_address"), 'required|is_unique[printers.ip_address]');
            $this->form_validation->set_rules('port', $this->lang->line("port"), 'required');
        } else {
            $this->form_validation->set_rules('path', $this->lang->line("path"), 'required|is_unique[printers.path]');
        }

        if ($this->form_validation->run() == true) {

            $data = array('title' => $this->input->post('title'),
                'type' => $this->input->post('type'),
                'profile' => $this->input->post('profile'),
                'char_per_line' => $this->input->post('char_per_line'),
                'path' => $this->input->post('path'),
                'ip_address' => $this->input->post('ip_address'),
                'port' => ($this->input->post('type') == 'network') ? $this->input->post('port') : NULL,
            );

        }

        if ( $this->form_validation->run() == true && $cid = $this->pos_model->addPrinter($data)) {

            $this->session->set_flashdata('message', $this->lang->line("printer_added"));
            redirect("pos/printers");

        } else {
            if($this->input->is_ajax_request()) {
                echo json_encode(array('status' => 'failed', 'msg' => validation_errors())); die();
            }

            $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
            $this->data['page_title'] = lang('add_printer');
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('pos'), 'page' => lang('pos')), array('link' => site_url('pos/printers'), 'page' => lang('printers')), array('link' => '#', 'page' => lang('add_printer')));
            $meta = array('page_title' => lang('add_printer'), 'bc' => $bc);
            $this->page_construct('pos/add_printer', $meta, $this->data);
        }
    }

    function edit_printer($id = NULL)
    {

        if (!$this->Owner) {
            $this->session->set_flashdata('error', lang('access_denied'));
            redirect("pos");
        }
        if($this->input->get('id')) { $id = $this->input->get('id', TRUE); }

        $printer = $this->pos_model->getPrinterByID($id);
        $this->form_validation->set_rules('title', $this->lang->line("title"), 'required');
        $this->form_validation->set_rules('type', $this->lang->line("type"), 'required');
        $this->form_validation->set_rules('profile', $this->lang->line("profile"), 'required');
        $this->form_validation->set_rules('char_per_line', $this->lang->line("char_per_line"), 'required');
        if ($this->input->post('type') == 'network') {
            $this->form_validation->set_rules('ip_address', $this->lang->line("ip_address"), 'required');
            if ($this->input->post('ip_address') != $printer->ip_address) {
                $this->form_validation->set_rules('ip_address', $this->lang->line("ip_address"), 'is_unique[printers.ip_address]');
            }
            $this->form_validation->set_rules('port', $this->lang->line("port"), 'required');
        } else {
            $this->form_validation->set_rules('path', $this->lang->line("path"), 'required');
            if ($this->input->post('path') != $printer->path) {
                $this->form_validation->set_rules('path', $this->lang->line("path"), 'is_unique[printers.path]');
            }
        }

        if ($this->form_validation->run() == true) {

            $data = array('title' => $this->input->post('title'),
                'type' => $this->input->post('type'),
                'profile' => $this->input->post('profile'),
                'char_per_line' => $this->input->post('char_per_line'),
                'path' => $this->input->post('path'),
                'ip_address' => $this->input->post('ip_address'),
                'port' => ($this->input->post('type') == 'network') ? $this->input->post('port') : NULL,
            );

        }

        if ( $this->form_validation->run() == true && $this->pos_model->updatePrinter($id, $data)) {

            $this->session->set_flashdata('message', $this->lang->line("printer_updated"));
            redirect("pos/printers");

        } else {

            $this->data['printer'] = $printer;
            $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
            $this->data['page_title'] = lang('edit_printer');
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('pos'), 'page' => lang('pos')), array('link' => site_url('pos/printers'), 'page' => lang('printers')), array('link' => '#', 'page' => lang('edit_printer')));
            $meta = array('page_title' => lang('edit_printer'), 'bc' => $bc);
            $this->page_construct('pos/edit_printer', $meta, $this->data);

        }
    }

    function delete_printer($id = NULL)
    {
        if(DEMO) {
            $this->session->set_flashdata('error', $this->lang->line("disabled_in_demo"));
            $this->sma->md();
        }
        if (!$this->Owner) {
            $this->session->set_flashdata('error', lang('access_denied'));
            $this->sma->md();
        }

        if ($this->input->get('id')) { $id = $this->input->get('id', TRUE); }

        if ($this->pos_model->deletePrinter($id)) {
            $this->sma->send_json(array('error' => 0, 'msg' => lang("printer_deleted")));
        }

    }
	public function convert_number_to_words($number)
    {
        $hyphen = ' ';
        $conjunction = '  ';
        $separator = ' ';
        $negative = 'âm ';
        $decimal = ' phẩy ';
        $dictionary = array(
            0 => 'Không',
            1 => 'Một',
            2 => 'Hai',
            3 => 'Ba',
            4 => 'Bốn',
            5 => 'Năm',
            6 => 'Sáu',
            7 => 'Bảy',
            8 => 'Tám',
            9 => 'Chín',
            10 => 'Mười',
            11 => 'Mười một',
            12 => 'Mười hai',
            13 => 'Mười ba',
            14 => 'Mười bốn',
            15 => 'Mười năm',
            16 => 'Mười sáu',
            17 => 'Mười bảy',
            18 => 'Mười tám',
            19 => 'Mười chín',
            20 => 'Hai mươi',
            30 => 'Ba mươi',
            40 => 'Bốn mươi',
            50 => 'Năm mươi',
            60 => 'Sáu mươi',
            70 => 'Bảy mươi',
            80 => 'Tám mươi',
            90 => 'Chín mươi',
            100 => 'trăm',
            1000 => 'ngàn',
            1000000 => 'triệu',
            1000000000 => 'tỷ',
            1000000000000 => 'nghìn tỷ',
            1000000000000000 => 'ngàn triệu triệu',
            1000000000000000000 => 'tỷ tỷ'
        );

        if (!is_numeric($number)) {
            return false;
        }

        if (($number >= 0 && (int)$number < 0) || (int)$number < 0 - PHP_INT_MAX) {
            trigger_error(
                'convert_number_to_words only accepts numbers between -' . PHP_INT_MAX . ' and ' . PHP_INT_MAX,
                E_USER_WARNING
            );
            return false;
        }

        if ($number < 0) {
            return $negative . $this->convert_number_to_words(abs($number));
        }

        $string = $fraction = null;

        if (strpos($number, '.') !== false) {
            list($number, $fraction) = explode('.', $number);
        }

        switch (true) {
            case $number < 21:
                $string = $dictionary[$number];
                break;
            case $number < 100:
                $tens = ((int)($number / 10)) * 10;
                $units = $number % 10;
                $string = $dictionary[$tens];
                if ($units) {
                    $string .= $hyphen . $dictionary[$units];
                }
                break;
            case $number < 1000:
                $hundreds = $number / 100;
                $remainder = $number % 100;
                $string = $dictionary[$hundreds] . ' ' . $dictionary[100];
                if ($remainder) {
                    $string .= $conjunction . $this->convert_number_to_words($remainder);
                }
                break;
            default:
                $baseUnit = pow(1000, floor(log($number, 1000)));
                $numBaseUnits = (int)($number / $baseUnit);
                $remainder = $number % $baseUnit;
                $string = $this->convert_number_to_words($numBaseUnits) . ' ' . $dictionary[$baseUnit];
                if ($remainder) {
                    $string .= $remainder < 100 ? $conjunction : $separator;
                    $string .= $this->convert_number_to_words($remainder);
                }
                break;
        }

        if (null !== $fraction && is_numeric($fraction)) {
            $string .= $decimal;
            $words = array();
            foreach (str_split((string)$fraction) as $number) {
                $words[] = $dictionary[$number];
            }
            $string .= implode(' ', $words);
        }

        return $string;
    }
}
