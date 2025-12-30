<?php
    
class Api extends MY_Controller {
    
     /**
     * Get All Data from this method.
     *
     * @return Response
    */
    public function __construct() {
       parent::__construct();
       $this->load->database();       
       $this->load->model('sales_model');   
       $this->load->model('companies_model');       
       $this->load->model('settings_model');
       $this->digital_upload_path = 'files/';
        $this->upload_path = 'assets/uploads/';
        $this->thumbs_path = 'assets/uploads/thumbs/';
        $this->image_types = 'gif|jpg|jpeg|png|tif';
        $this->digital_file_types = 'zip|psd|ai|rar|pdf|doc|docx|xls|xlsx|ppt|pptx|gif|jpg|jpeg|png|tif|txt';
        $this->allowed_file_size = '1024';
        $this->data['logo'] = true;

    }
       
    /**
     * Get All Data from this method.
     *
     * @return Response
    */

      
    public function get_categories()
    {
        if($_SERVER['REQUEST_METHOD']=='POST')       
        {
            $ip_post=(string)$_SERVER['REMOTE_ADDR'];
            if($ip_post!="103.81.86.71"&&$ip_post!="103.1.237.254"){
                //exit(json_encode("IP NOT ALLOW ".$ip_post));
            }       
            
            $token=(string)json_decode($this->input->post('token')); 
           
            $post_data='ERROR';
            if($token!='CIwiFtn8QMfIq2MNJqU8Q2lBOhKzpTTIdBBABC'){
                exit(json_encode("INVALID_TOKEN_SECURE"));
            }else
            {                   
                
                exit();
            }
        }  
        exit('INVALID_TOKEN_SECURE');
    }    
    public function post_carts()
    {
        if($_SERVER['REQUEST_METHOD']=='POST')       
        {
            $ip_post=(string)$_SERVER['REMOTE_ADDR'];
            if($ip_post!="103.81.86.71"&&$ip_post!="103.1.237.254"){
                //exit(json_encode("IP NOT ALLOW ".$ip_post));
            }       
            
            $token=(string)json_decode($this->input->post('token')); 
            
            $Settings = $this->site->get_setting();

            $post_data='ERROR';
            if($token!='CIwiFtn8QMfIq2MNJqU8Q2lBOhKzpTTIdBBABC'){
                exit(json_encode("INVALID_TOKEN_SECURE"));
            }else
            {                   
                $subdomain=(string)json_decode($this->input->post('subdomain')); 
                
                if ($Settings->scodeweb_username!=$subdomain) {
                    exit(json_encode("NOT VALID SUBDOMAIN: ".$subdomain));
                }                
                $carts=json_decode($this->input->post("data"));
                
                $checkvalid=false;
                $customer_id=0;
                
                if (!empty($carts->order)&&!empty($carts->products))
                {
                    //tien hanh add sale
                    //get user info by phone
                    if($carts->order->email==""){
                        $email=$carts->order->dienthoai."@posbasic.net";
                    }else{
                        $email=$carts->order->email;
                    }
                    $full_name=$carts->order->tenkhach;
                    $address=$carts->order->diachi;
                    if (!empty($carts->order->address)) {
                        $address.=", ".implode(", ",$carts->order->address);
                    }
                    $phone=$carts->order->dienthoai;
                    if ($carts->order->buyer_id>0) {
                        $customer_id=$this->sales_model->checkCustomerIdSanTMDTApi($carts->order->buyer_id);    
                    }                    
                    if($customer_id>0){
                        //tien hanh update
                        $data_cus = array('name' => $full_name,
                            'address' => $address,
                            'phone' => $phone
                        );
                        $this->companies_model->updateCompany($customer_id, $data_cus);
                    }else{
                        $customer_id=$this->sales_model->checkCustomerByPhoneWooApi($phone);
                        if($customer_id>0){
                            //tien hanh update
                            $data_cus = array('name' => $full_name,
                                'address' => $address,
                                'phone' => $phone
                            );
                            $this->companies_model->updateCompany($customer_id, $data_cus);
                        }else{
                            //tien hanh them khach hang moi
                            $customer_group_default=$Settings->customer_group;
                            $cg = $this->site->getCustomerGroupByID($customer_group_default);       
                            
                            $data_cus = array('name' => $full_name,
                                'email' => $email,
                                'group_id' => '3',
                                'group_name' => 'customer',
                                'customer_group_id' => $customer_group_default,
                                'customer_group_name' => $cg->name,
                                'price_group_id' => NULL,
                                'price_group_name' => NULL,
                                'company' => 'ALOIT',
                                'address' => $address,
                                'vat_no' => '',
                                'city' => '',
                                'state' => '',
                                'postal_code' => '',
                                'country' => '',
                                'phone' => $phone,
                                'cf1' => '',
                                'cf2' => '',
                                'cf3' => '',
                                'cf4' => '',
                                'cf5' => '',
                                'cf6' => '',
                                'nobandau' =>0,
                                'santmdt_customer_id'=>$carts->order->buyer_id,
                            );
                            $customer_id = $this->companies_model->addCompany($data_cus);
                        }
                    }
                    if ($customer_id>0) {
                        $checkvalid=true;        
                    }
                }
                if ($checkvalid&&$customer_id>0) {
                    $order_id_santmdt=$carts->order->order_id;
                    $total_items=0;
                    $products=[];
                    $grand_total=0;
                    if (!empty($carts->products))
                    {
                        foreach ($carts->products as $item) {
                            $product_id=$item->posbasic_id;                                            
                            //tien hanh add sale_item  
                            $product=$this->site->getProductByID($product_id);
                            if (!empty($product)) 
                            {
                                $item_option=null;
                                $item_quantity=$item->product_quantity;
                                $real_unit_price=(float)$item->product_unit_price;
                                if ($real_unit_price>0) {
                                    $real_unit_price=$real_unit_price/100;
                                }
                                $option_text=$item->option_text;
                                $seller_shipping_cost=$item->seller_shipping_cost;

                                $subtotal=$real_unit_price*$item_quantity;
                                $grand_total+=$subtotal;

                                $unit=$this->site->getUnitByID($product->sale_unit);

                                
                                if ((int)$item->option_id>0) {
                                    //check option
                                    $check_option=$this->site->getProductOptionsById($product->id,$item->option_id);
                                    if ($check_option) 
                                    {
                                       $item_option =$item->option_id; 
                                    }    
                                }
                                $products[] = array(
                                    'product_id' => $product->id,
                                    'product_code' => $product->code,
                                    'product_name' => $product->name,
                                    'product_type' => $product->type,
                                    'option_id' => $item_option,
                                    'net_unit_price' => $real_unit_price,
                                    'unit_price' => $this->sma->formatDecimal($real_unit_price ),
                                    'quantity' => $item_quantity,
                                    'product_unit_id' => $product->sale_unit,
                                    'product_unit_code' => $unit ? $unit->code : NULL,
                                    'unit_quantity' => $item_quantity,
                                    'warehouse_id' => $Settings->default_warehouse,
                                    'item_tax' => 0,
                                    'tax_rate_id' => 0,
                                    'tax' => 0,
                                    'discount' => 0,
                                    'item_discount' => 0,
                                    'subtotal' => $this->sma->formatDecimal($subtotal),
                                    'serial_no' => $option_text,
                                    'real_unit_price' => $real_unit_price,
                                    'data_id_khuyenmai' => 0,
                                    'data_id_san_tmdt' => $order_id_santmdt,
                                );      
                                $total_items++;                                          
                            }                      
                        }
                    }       
                
                    $biller=$this->site->getCompanyByID($Settings->default_biller);
                    $shipping=(float)$carts->order->shipping;
                    
                    $total=$grand_total;

                    if ($shipping>0) {
                        $shipping=$shipping/100;
                        $grand_total=$grand_total+$shipping;
                    }

                    $data = array(
                            'date' => date("Y-m-d H:i:s",strtotime($carts->order->created_at)),
                            'reference_no' => "API-".$carts->order->reference,
                            'customer_id' => $customer_id,
                            'customer' => $full_name,
                            'biller_id' => $Settings->default_biller,
                            'biller' => $biller->name,
                            'doitac' => null,
                            'warehouse_id' => $Settings->default_warehouse,
                            'note' => $carts->order->ghichu,
                            'staff_note' => '',
                            'total' => $total,
                            'product_discount' => 0,
                            'order_discount_id' => 0,
                            'order_discount' => 0,
                            'total_discount' => 0,
                            'product_tax' => 0,
                            'order_tax_id' => 2,
                            'order_tax' => 0,
                            'total_tax' => 0,
                            'shipping' => $this->sma->formatDecimal($shipping),
                            'grand_total' => $grand_total,
                            'total_items' => $total_items,
                            'sale_status' => 'pending',
                            'payment_status' => 'pending',
                            'payment_term' => 0,
                            'due_date' => NULL,
                            'paid' => 0,
                            'created_by' => 1,
                            'total_weight' => 0,
                            'api_id' => $carts->order->order_id
                        );

                    if (!empty($products)&&!empty($data)) 
                    {
                        $sale_id=$this->sales_model->addSale($data, $products, null,null,null,null,true);
                        
                        $order=$this->site->getInvoiceByID($sale_id);

                        //tien hanh save history order by api
                        $history_order['order_id']=$order->id;
                        $history_order['order_code']=$order->reference_no;
                        $history_order['api_order_id']=$order->api_id;
                        $history_order['customer_name']=$order->customer;
                        $history_order['customer_id']=$order->customer_id;
                        $history_order['total_money']=$order->grand_total;
                        $history_order['total_item']=$order->total_items;
                        $history_order['type']='Đơn hàng mới';    
                        $history_order['created_by']=1;
                        $this->db->insert('history_api_orders', $history_order);

                         /*luu lich su tao hoa don lhson code 05/09/2021*/
                        $order_history = $this->db->from('sales')->where('warehouse_id', $data['warehouse_id'])->where('id',$sale_id)->get()->row_array();
                        $order_history['history']="Tạo mới hóa đơn";
                        $order_history['history_auth']=1;
                        $this->db->insert('sales_history', $order_history);      
                        $transaction_id=$this->db->insert_id();
                        //query all sale_items
                        $query_all_item_history="insert into scodeweb_sale_items_history select NULL,scodeweb_sale_items.* FROM scodeweb_sale_items WHERE sale_id=".$sale_id." AND warehouse_id=".$data['warehouse_id'];
                        $query = $this->db->query($query_all_item_history);    

                        $his_store_obj=$this->site->getWarehouseByID($data['warehouse_id']);
                        $store_name_fb=$his_store_obj->name;

                        $sent_rs=$this->site->sentNotificationAPI($title='Đơn hàng mới từ API',$message='Đơn hàng mới đã đồng bộ với hóa đơn ['.mb_strtoupper($data['reference_no']).'] trị giá '.number_format($data['grand_total']).' tại kho '.mb_strtoupper($store_name_fb),$transaction_id,$theloai='HOADON',$permission='',$data['warehouse_id']);
                      // echo var_dump($sent_rs);
                       /*luu lich su tao hoa don lhson code 05/09/2021*/

                    }
                }                       
                exit(json_encode('OK'));
            }
        }  
        exit('INVALID_TOKEN_SECURE');
    }
    public function update_payment_carts()
    {
        if($_SERVER['REQUEST_METHOD']=='POST')       
        {
            $ip_post=(string)$_SERVER['REMOTE_ADDR'];
            if($ip_post!="103.81.86.71"&&$ip_post!="103.1.237.254"){
                //exit(json_encode("IP NOT ALLOW ".$ip_post));
            }       
            
            $token=(string)json_decode($this->input->post('token')); 
            
            $Settings = $this->site->get_setting();

            $post_data='ERROR';
            if($token!='CIwiFtn8QMfIq2MNJqU8Q2lBOhKzpTTIdBBABC'){
                exit(json_encode("INVALID_TOKEN_SECURE"));
            }else
            {                   
                $subdomain=(string)json_decode($this->input->post('subdomain')); 
                
                if ($Settings->scodeweb_username!=$subdomain) {
                    exit(json_encode("NOT VALID SUBDOMAIN: ".$subdomain));
                }                
                $data=json_decode($this->input->post("data"));

                $order=$this->site->getInvoiceTMDTByID($data->order_id);
                if (!empty($order)) {
                    //tien hanh tinh toan so tien theo order id 
                    $order_items=$this->site->getAllSaleItemsByTMDT($data->order_id);   
                    if (!empty($order_items))
                    {
                        $total_money=0;
                        foreach ($order_items as $item) 
                        {
                            $total_money+=(float)$item->subtotal; 
                        }
                        if ($total_money>0) 
                        {
                            $customer_id=$order->customer_id;
                            $customer=$this->site->getCompanyByID($customer_id);
                            //tien hanh tao phieu thu bt sale_id
                            $payment = array(
                                'date' => date("Y-m-d H:i:s"),
                                'reference_no' =>$this->site->getReference('ppay'),
                                'amount' => $this->sma->formatDecimal($total_money),
                                'sale_id' => $order->id,
                                'paid_by' => 'api',
                                'cheque_no' => '',
                                'cc_no' => '',
                                'cc_holder' => '',
                                'cc_month' => '',
                                'cc_year' => '',
                                'cc_type' => '',
                                'created_by' => 1,
                                'note' =>'Payment by api ID:'.$data->order_id,
                                'type' => 'received',
                                'warehouse_id'=>$order->warehouse_id,
                                'c_name' => $customer->name,
                                'c_phone' => $customer->phone,
                                'c_address' => $customer->address,
                            );      
                            $payment_id=$this->sales_model->addPaymentApi($payment, $customer_id);  
                            
                            /*luu lich su tao phieu thu lhson code 05/09/2021*/
                            $order_history = $this->db->from('payments')->where('warehouse_id', $payment['warehouse_id'])->where('id',$payment_id)->get()->row_array();
                            $order_history['history']="Tạo mới phiếu thu";
                            $order_history['history_auth']= 1;
                            $this->db->insert('payments_history', $order_history);      
                            $transaction_id=$this->db->insert_id();            

                            $his_store_obj=$this->site->getWarehouseByID($payment['warehouse_id']);
                            $store_name_fb=$his_store_obj->name;

                            $sent_rs=$this->site->sentNotificationAPI($title='Tạo mới phiếu thu từ api',' Thanh toán online từ API đồng bộ với phiếu thu ['.mb_strtoupper($payment['reference_no']).'] trị giá '.number_format($payment['amount']).' tại kho '.mb_strtoupper($store_name_fb),$transaction_id,$theloai='PHIEUTHU',$permission='KETOAN',$payment['warehouse_id']);
                           
                           /*luu lich su tao phieu thu lhson code 05/09/2021*/

                        }    
                    }
                }
            }
        }
    }
}
