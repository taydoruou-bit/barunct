<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Reports extends MY_Controller
{

    function __construct()
    {
        parent::__construct();

        if (!$this->loggedIn) {
            $this->session->set_userdata('requested_page', $this->uri->uri_string());
            $this->sma->md('login');
        }

        $this->lang->load('reports', $this->Settings->user_language);
        $this->load->library('form_validation');
        $this->load->model('reports_model');
		$this->load->model('companies_model');
		$this->load->model('Doitac_model');
		
        $this->data['pb'] = $this->site->getAllPTTT();


    }

    function index()
    {
        $this->sma->checkPermissions();
        $data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['monthly_sales'] = $this->reports_model->getChartData();
        $this->data['stock'] = $this->reports_model->getStockValue();
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('reports')));
        $meta = array('page_title' => lang('reports'), 'bc' => $bc);
        $this->page_construct('reports/index', $meta, $this->data);

    }

    function warehouse_stock($warehouse = NULL)
    {
        $this->sma->checkPermissions('index', TRUE);
        $data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        if ($this->input->get('warehouse')) {
            $warehouse = $this->input->get('warehouse');
        }

        $this->data['stock'] = $warehouse ? $this->reports_model->getWarehouseStockValue($warehouse) : $this->reports_model->getStockValue();
        $this->data['warehouses'] = $this->site->getAllWarehouses();
        $this->data['warehouse_id'] = $warehouse;
        $this->data['warehouse'] = $warehouse ? $this->site->getWarehouseByID($warehouse) : NULL;
        $this->data['totals'] = $this->reports_model->getWarehouseTotals($warehouse);
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('reports')));
        $meta = array('page_title' => lang('reports'), 'bc' => $bc);
        $this->page_construct('reports/warehouse_stock', $meta, $this->data);

    }

    function expiry_alerts($warehouse_id = NULL)
    {
        $this->sma->checkPermissions('expiry_alerts');
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');

        if ($this->Owner || $this->Admin || !$this->session->userdata('warehouse_id')) {
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['warehouse_id'] = $warehouse_id;
            $this->data['warehouse'] = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : NULL;
        } else {
            $user = $this->site->getUser();
            $this->data['warehouses'] = NULL;
            $this->data['warehouse_id'] = $user->warehouse_id;
            $this->data['warehouse'] = $user->warehouse_id ? $this->site->getWarehouseByID($user->warehouse_id) : NULL;
        }

        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('product_expiry_alerts')));
        $meta = array('page_title' => lang('product_expiry_alerts'), 'bc' => $bc);
        $this->page_construct('reports/expiry_alerts', $meta, $this->data);
    }

    function getExpiryAlerts($warehouse_id = NULL)
    {
        $this->sma->checkPermissions('expiry_alerts', TRUE);
        $date = date('Y-m-d', strtotime('+3 months'));

        if (!$this->Owner && !$warehouse_id) {
            $user = $this->site->getUser();
            $warehouse_id = $user->warehouse_id;
        }

        $this->load->library('datatables');
        if ($warehouse_id) {
            $this->datatables
                ->select("image, product_code, product_name, quantity_balance, warehouses.name, expiry")
                ->from('purchase_items')
                ->join('products', 'products.id=purchase_items.product_id', 'left')
                ->join('warehouses', 'warehouses.id=purchase_items.warehouse_id', 'left')
                ->where('warehouse_id', $warehouse_id)
                ->where('expiry !=', NULL)->where('expiry !=', '0000-00-00')
                ->where('expiry <', $date);
        } else {
            $this->datatables
                ->select("image, product_code, product_name, quantity_balance, warehouses.name, expiry")
                ->from('purchase_items')
                ->join('products', 'products.id=purchase_items.product_id', 'left')
                ->join('warehouses', 'warehouses.id=purchase_items.warehouse_id', 'left')
                ->where('expiry !=', NULL)->where('expiry !=', '0000-00-00')
                ->where('expiry <', $date);
        }
        echo $this->datatables->generate();
    }

    function quantity_alerts($warehouse_id = NULL)
    {
        $this->sma->checkPermissions('quantity_alerts');
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');

        if ($this->Owner || $this->Admin || !$this->session->userdata('warehouse_id')) {
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['warehouse_id'] = $warehouse_id;
            $this->data['warehouse'] = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : NULL;
        } else {
            $user = $this->site->getUser();
            $this->data['warehouses'] = NULL;
            $this->data['warehouse_id'] = $user->warehouse_id;
            $this->data['warehouse'] = $user->warehouse_id ? $this->site->getWarehouseByID($user->warehouse_id) : NULL;
        }

        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('product_quantity_alerts')));
        $meta = array('page_title' => lang('product_quantity_alerts'), 'bc' => $bc);
        $this->page_construct('reports/quantity_alerts', $meta, $this->data);
    }

    function getQuantityAlerts($warehouse_id = NULL, $pdf = NULL, $xls = NULL)
    {
        $this->sma->checkPermissions('quantity_alerts', TRUE);
        if (!$this->Owner && !$warehouse_id) {
            $user = $this->site->getUser();
            $warehouse_id = $user->warehouse_id;
        }

        if ($pdf || $xls) {

            if ($warehouse_id) {
                $this->db
                    ->select('products.image as image, products.code, products.name, warehouses_products.quantity, alert_quantity')
                    ->from('products')->join('warehouses_products', 'warehouses_products.product_id=products.id', 'left')
                    ->where('alert_quantity > warehouses_products.quantity', NULL)
                    ->where('warehouse_id', $warehouse_id)
                    ->where('track_quantity', 1)
                    ->order_by('products.code desc');
            } else {
                $this->db
                    ->select('image, code, name, quantity, alert_quantity')
                    ->from('products')
                    ->where('alert_quantity > quantity', NULL)
                    ->where('track_quantity', 1)
                    ->order_by('code desc');
            }

            $q = $this->db->get();
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
                $this->excel->getActiveSheet()->setTitle(lang('product_quantity_alerts'));
                $this->excel->getActiveSheet()->SetCellValue('A1', lang('product_code'));
                $this->excel->getActiveSheet()->SetCellValue('B1', lang('product_name'));
                $this->excel->getActiveSheet()->SetCellValue('C1', lang('quantity'));
                $this->excel->getActiveSheet()->SetCellValue('D1', lang('alert_quantity'));

                $row = 2;
                foreach ($data as $data_row) {
                    $this->excel->getActiveSheet()->SetCellValue('A' . $row, $data_row->code);
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->name);
                    $this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->quantity);
                    $this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->alert_quantity);
                    $row++;
                }

                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(35);
                $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(35);
                $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(25);

                $filename = 'product_quantity_alerts';
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

        } else {

            $this->load->library('datatables');
            if ($warehouse_id) {
                $this->datatables
                    ->select('image, code, name, wp.quantity, alert_quantity')
                    ->from('products')
                    ->join("( SELECT * from {$this->db->dbprefix('warehouses_products')} WHERE warehouse_id = {$warehouse_id}) wp", 'products.id=wp.product_id', 'left')
                    ->where('alert_quantity > wp.quantity', NULL)
                    ->or_where('wp.quantity', NULL)
                    ->where('track_quantity', 1)
                    ->group_by('products.id');
            } else {
                $this->datatables
                    ->select('image, code, name, quantity, alert_quantity')
                    ->from('products')
                    ->where('alert_quantity > quantity', NULL)
                    ->where('track_quantity', 1);
            }

            echo $this->datatables->generate();

        }

    }

    function suggestions()
    {
        $term = $this->input->get('term', TRUE);
        if (strlen($term) < 1) {
            die();
        }

        $rows = $this->reports_model->getProductNames($term);
        if ($rows) {
            foreach ($rows as $row) {
                $pr[] = array('id' => $row->id, 'label' => $row->name . " (" . $row->code . ")");

            }
            $this->sma->send_json($pr);
        } else {
            echo FALSE;
        }
    }

    public function best_sellers($warehouse_id = NULL)
    {
        date_default_timezone_set('Asia/Ho_Chi_Minh');
        $this->sma->checkPermissions('products');

        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $y1 = date('Y', strtotime('-1 month'));
        $m1 = date('m', strtotime('-1 month'));
        $m1sdate = $y1.'-'.$m1.'-01 00:00:00';
        $m1edate = $y1.'-'.$m1.'-'. days_in_month($m1, $y1) . ' 23:59:59';
        $this->data['m1'] = date('M Y', strtotime($y1.'-'.$m1));
        $this->data['m1bs'] = $this->reports_model->getBestSeller($m1sdate, $m1edate, $warehouse_id);
        $y2 = date('Y', strtotime('-2 months'));
        $m2 = date('m', strtotime('-2 months'));
        $m2sdate = $y2.'-'.$m2.'-01 00:00:00';
        $m2edate = $y2.'-'.$m2.'-'. days_in_month($m2, $y2) . ' 23:59:59';
        $this->data['m2'] = date('M Y', strtotime($y2.'-'.$m2));
        $this->data['m2bs'] = $this->reports_model->getBestSeller($m2sdate, $m2edate, $warehouse_id);
        $y3 = date('Y', strtotime('-3 months'));
        $m3 = date('m', strtotime('-3 months'));
        $m3sdate = $y3.'-'.$m3.'-01 23:59:59';
        $this->data['m3'] = date('M Y', strtotime($y3.'-'.$m3)).' - '.$this->data['m1'];
        $this->data['m3bs'] = $this->reports_model->getBestSeller($m3sdate, $m1edate, $warehouse_id);
        $y4 = date('Y', strtotime('-12 months'));
        $m4 = date('m', strtotime('-12 months'));
        $m4sdate = $y4.'-'.$m4.'-01 23:59:59';
        $this->data['m4'] = date('M Y', strtotime($y4.'-'.$m4)).' - '.$this->data['m1'];
        $this->data['m4bs'] = $this->reports_model->getBestSeller($m4sdate, $m1edate, $warehouse_id);
        // $this->sma->print_arrays($this->data['m1bs'], $this->data['m2bs'], $this->data['m3bs'], $this->data['m4bs']);
        $this->data['warehouses'] = $this->site->getAllWarehouses();
        $this->data['warehouse'] = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : NULL;
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('best_sellers')));
        $meta = array('page_title' => lang('best_sellers'), 'bc' => $bc);
        $this->page_construct('reports/best_sellers', $meta, $this->data);

    }

    function products()
    {
        $this->sma->checkPermissions();
        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['categories'] = $this->site->getAllCategories();
		$this->data['nhom'] = $this->site->getAllNhomsanpham();
        $this->data['brands'] = $this->site->getAllBrands();
        
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

        if ($this->input->post('start_date')) {
            $dt = "From " . $this->input->post('start_date') . " to " . $this->input->post('end_date');
        } else {
            $dt = "Till " . $this->input->post('end_date');
        }
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('products_report')));
        $meta = array('page_title' => lang('products_report'), 'bc' => $bc);
        if ($this->Owner||$this->Admin) {
            $this->page_construct('reports/products', $meta, $this->data);
    
        } else {
            $this->page_construct('reports/products_empty', $meta, $this->data);
        }
 }      
 function getProductsReport($pdf = NULL, $xls = NULL)
    {
        $this->sma->checkPermissions('products', TRUE);

        $product = $this->input->get('product') ? $this->input->get('product') : NULL;
        $category = $this->input->get('category') ? $this->input->get('category') : NULL;
        $group_id = $this->input->get('group_id') ? $this->input->get('group_id') : NULL;
        $brand = $this->input->get('brand') ? $this->input->get('brand') : NULL;
        $subcategory = $this->input->get('subcategory') ? $this->input->get('subcategory') : NULL;
        $warehouse = $this->input->get('warehouse') ? $this->input->get('warehouse') : NULL;
        $cf1 = $this->input->get('cf1') ? $this->input->get('cf1') : NULL;
        $cf2 = $this->input->get('cf2') ? $this->input->get('cf2') : NULL;
        $cf3 = $this->input->get('cf3') ? $this->input->get('cf3') : NULL;
        $cf4 = $this->input->get('cf4') ? $this->input->get('cf4') : NULL;
        $cf5 = $this->input->get('cf5') ? $this->input->get('cf5') : NULL;
        $cf6 = $this->input->get('cf6') ? $this->input->get('cf6') : NULL;
        $start_date = $this->input->get('start_date') ? $this->input->get('start_date') : NULL;
        $end_date = $this->input->get('end_date') ? $this->input->get('end_date') : NULL;

        $pp = "( SELECT product_id, SUM(CASE WHEN pi.purchase_id IS NOT NULL THEN unit_quantity ELSE 0 END) as purchasedQty,SUM(CASE WHEN pi.bandau_id>0 THEN ( CASE WHEN unit_quantity!=0 THEN unit_quantity ELSE quantity END) ELSE 0 END) as tondau, SUM(quantity_balance) as balacneQty, (SUM(unit_quantity*unit_cost)/SUM(unit_quantity)) as balacneValue, SUM(CASE WHEN pi.purchase_id IS NOT NULL THEN pi.subtotal WHEN pi.transfer_id IS NOT NULL THEN pi.subtotal ELSE 0 END) as totalPurchase,SUM(CASE WHEN pi.purchase_id IS NULL AND pi.bandau_id>0 THEN pi.subtotal ELSE 0 END) as totalTondau from {$this->db->dbprefix('purchase_items')} pi LEFT JOIN {$this->db->dbprefix('purchases')} p on p.id = pi.purchase_id ";
        
        $sp = "( SELECT si.product_id, SUM( si.unit_quantity ) soldQty, SUM( si.subtotal ) totalSale from " . $this->db->dbprefix('sales') . " s JOIN " . $this->db->dbprefix('sale_items') . " si on s.id = si.sale_id ";
        
        $spreturn = "( SELECT re.product_id, SUM( re.quantity ) soldQty, SUM( re.subtotal ) totalReturn from " . $this->db->dbprefix('returns') . " ret JOIN " . $this->db->dbprefix('return_items') . " re on ret.id = re.return_id ";
        
        $khoduong = "( SELECT adi.product_id, SUM( adi.quantity ) slduong from " . $this->db->dbprefix('adjustments') . " adj JOIN " . $this->db->dbprefix('adjustment_items') . " adi on adj.id = adi.adjustment_id AND adi.type='addition'";
        
        $khoam = "( SELECT adia.product_id, SUM( adia.quantity ) slam from " . $this->db->dbprefix('adjustments') . " adja JOIN " . $this->db->dbprefix('adjustment_items') . " adia on adja.id = adia.adjustment_id AND adia.type!='addition'";
        
        if ($start_date || $warehouse) {
            $pp .= " WHERE ";
            $sp .= " WHERE ";
            $spreturn .= " WHERE ";
            $khoduong .= " WHERE ";
            $khoam .= " WHERE ";
            
            if ($start_date) {
                
                $start_date = date("Y-m-d",strtotime($start_date))." 00:00:00";
                $end_date = date("Y-m-d",strtotime($end_date))." 23:59:59";
                
                $pp .= " p.date >= '{$start_date}' AND p.date < '{$end_date}' ";
                $sp .= " s.date >= '{$start_date}' AND s.date < '{$end_date}' ";
                $spreturn .= " ret.date >= '{$start_date}' AND ret.date < '{$end_date}' ";
                $khoduong .= " adj.date >= '{$start_date}' AND adj.date < '{$end_date}' ";
                $khoam .= " adja.date >= '{$start_date}' AND adja.date < '{$end_date}' ";
                
                if ($warehouse) {
                    $pp .= " AND ";
                    $sp .= " AND ";
                    $spreturn .= " AND ";
                    $khoduong .= " AND ";
                    $khoam .= " AND ";
                }
            }
            if ($warehouse) {
                $pp .= " pi.warehouse_id = '{$warehouse}' ";
                $sp .= " si.warehouse_id = '{$warehouse}' ";
                $spreturn .= " ret.warehouse_id = '{$warehouse}' ";
                $khoduong .= " adj.warehouse_id = '{$warehouse}' ";
                $khoam .= " adja.warehouse_id = '{$warehouse}' ";
            }
        }
        $pp .= " GROUP BY pi.product_id ) PCosts";
        $sp .= " GROUP BY si.product_id ) PSales";
        $spreturn .= " GROUP BY re.product_id ) PReturn";
        $khoduong .= " GROUP BY adi.product_id ) PKDUONG";
        $khoam .= " GROUP BY adia.product_id ) PKAM";
        
        if ($pdf || $xls) {

             $this->db
                ->select($this->db->dbprefix('products') . ".code, " . $this->db->dbprefix('products') . ".name,
                COALESCE( PCosts.tondau, 0 ) as tondau,COALESCE( PCosts.purchasedQty, 0 ) as purchased,
                COALESCE( PSales.soldQty, 0 ) as sold,
                (COALESCE( PSales.totalSale, 0 ) - COALESCE( PCosts.totalPurchase, 0 )-COALESCE( PReturn.totalReturn, 0 )) as Profit,
                (COALESCE( PCosts.purchasedQty, 0 )-COALESCE( PSales.soldQty, 0 )+COALESCE( PReturn.soldQty, 0 )+COALESCE( PCosts.tondau, 0 )+COALESCE(PKDUONG.slduong, 0 )-COALESCE(PKAM.slam, 0 )) as balance, {$this->db->dbprefix('products')}.id as id,COALESCE(PSales.totalSale, 0 ) as tienban,COALESCE(PCosts.totalPurchase, 0 ) as tiennhap,((COALESCE( PCosts.purchasedQty, 0 )-COALESCE( PSales.soldQty, 0 )+COALESCE( PReturn.soldQty, 0 )+COALESCE( PCosts.tondau, 0 ))* balacneValue) tienton,COALESCE( PReturn.soldQty, 0 ) as thuhoi,COALESCE( PReturn.totalReturn, 0 ) tienthuhoi,COALESCE(PKDUONG.slduong, 0 ) as slduong,COALESCE(PKAM.slam, 0 ) as slam,".$this->db->dbprefix('products') . ".id as product_id,balacneValue,{$this->db->dbprefix('products')}.quantity as xuattonkho,COALESCE(PCosts.totalTondau, 0 ) as tientondau", FALSE)
                ->from('products') 
                ->join($sp, 'products.id = PSales.product_id', 'left')
                ->join($pp, 'products.id = PCosts.product_id', 'left') 
                ->join($spreturn, 'products.id = PReturn.product_id', 'left') 
                ->join($khoduong, 'products.id = PKDUONG.product_id', 'left')
                ->join($khoam, 'products.id = PKAM.product_id', 'left') 
                ->group_by('products.id');

            if ($product) {
                $this->db->where($this->db->dbprefix('products') . ".id", $product);
            }
            if ($cf1) {
                $this->db->where($this->db->dbprefix('products') . ".cf1", $cf1);
            }
            if ($cf2) {
                $this->db->where($this->db->dbprefix('products') . ".cf2", $cf2);
            }
            if ($cf3) {
                $this->db->where($this->db->dbprefix('products') . ".cf3", $cf3);
            }
            if ($cf4) {
                $this->db->where($this->db->dbprefix('products') . ".cf4", $cf4);
            }
            if ($cf5) {
                $this->db->where($this->db->dbprefix('products') . ".cf5", $cf5);
            }
            if ($cf6) {
                $this->db->where($this->db->dbprefix('products') . ".cf6", $cf6);
            }
            if ($category) {
                $this->db->where($this->db->dbprefix('products') . ".category_id", $category);
            }
            if ($group_id) {
                $this->db->where($this->db->dbprefix('products') . ".group_id", $group_id);
            }
            if ($subcategory) {
                $this->db->where($this->db->dbprefix('products') . ".subcategory_id", $subcategory);
            }
            if ($brand) {
                $this->db->where($this->db->dbprefix('products') . ".brand", $brand);
            }

            $q = $this->db->get();
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
                $this->excel->getActiveSheet()->setTitle(lang('products_report'));
                $this->excel->getActiveSheet()->SetCellValue('A1', lang('product_code'));
                $this->excel->getActiveSheet()->SetCellValue('B1', lang('product_name'));
                $this->excel->getActiveSheet()->SetCellValue('C1', lang('Đơn vị'));
                $this->excel->getActiveSheet()->SetCellValue('D1', lang('Tồn đầu'));
                $this->excel->getActiveSheet()->SetCellValue('E1', lang('Giá trị'));
                $this->excel->getActiveSheet()->SetCellValue('F1', lang('Nhập hàng'));
                $this->excel->getActiveSheet()->SetCellValue('G1', lang('Giá trị'));
                $this->excel->getActiveSheet()->SetCellValue('H1', lang('Bán hàng'));
                $this->excel->getActiveSheet()->SetCellValue('I1', lang('Giá trị'));
                $this->excel->getActiveSheet()->SetCellValue('K1', lang('Lợi nhuận'));
                $this->excel->getActiveSheet()->SetCellValue('L1', lang('Tồn kho'));
                $this->excel->getActiveSheet()->SetCellValue('M1', lang('Giá trị'));
                $this->excel->getActiveSheet()->SetCellValue('N1', lang('Tăng kho'));
                $this->excel->getActiveSheet()->SetCellValue('O1', lang('Giảm kho'));


                foreach ($data as $data_row) {

                    $banra=(float)$this->site->getTongSoluongBanra($data_row['product_id'], $warehouse); 

                    $this->excel->getActiveSheet()->SetCellValue('A' . $row, $data_row->code);
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->name);
                    $this->excel->getActiveSheet()->SetCellValue('C' . $row, $donvi['name']);
                    $this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->tondau);
                    $this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->tientondau);
                    $this->excel->getActiveSheet()->SetCellValue('F' . $row, $data_row->purchased);
                    $this->excel->getActiveSheet()->SetCellValue('G' . $row, $data_row->tiennhap);
                    $this->excel->getActiveSheet()->SetCellValue('H' . $row, $banra);
                    $this->excel->getActiveSheet()->SetCellValue('I' . $row, $data_row->tienban);
                    $this->excel->getActiveSheet()->SetCellValue('K' . $row, $data_row->Profit);
                    $this->excel->getActiveSheet()->SetCellValue('L' . $row, $data_row->balance);
                    $this->excel->getActiveSheet()->SetCellValue('M' . $row, $data_row->tienton);
                    $this->excel->getActiveSheet()->SetCellValue('N' . $row, $data_row->slduong);
                    $this->excel->getActiveSheet()->SetCellValue('O' . $row, $data_row->slam);
                    
                                      
                    

                      $row++;
                }
                $this->excel->getActiveSheet()->getStyle("D" . $row . ":O" . $row)->getBorders()
                    ->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);           

                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(35);
                $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('K')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('L')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('M')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('N')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('O')->setWidth(15);

                $filename = 'products_report';
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
                    $this->excel->getActiveSheet()->getStyle('C2:G' . $row)->getAlignment()->setWrapText(true);
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

        } else {

            $this->load->library('datatables');
            $this->datatables
                ->select($this->db->dbprefix('products') . ".code, " . $this->db->dbprefix('products') . ".name,
                COALESCE( PCosts.tondau, 0 ) as tondau,COALESCE( PCosts.purchasedQty, 0 ) as purchased,
                COALESCE( PSales.soldQty, 0 ) as sold,
                (COALESCE( PSales.totalSale, 0 ) - COALESCE( PCosts.totalPurchase, 0 )-COALESCE( PReturn.totalReturn, 0 )) as Profit,
                (COALESCE( PCosts.purchasedQty, 0 )-COALESCE( PSales.soldQty, 0 )+COALESCE( PReturn.soldQty, 0 )+COALESCE( PCosts.tondau, 0 )+COALESCE(PKDUONG.slduong, 0 )-COALESCE(PKAM.slam, 0 )) as balance, {$this->db->dbprefix('products')}.id as id,COALESCE(PSales.totalSale, 0 ) as tienban,COALESCE(PCosts.totalPurchase, 0 ) as tiennhap,((COALESCE( PCosts.purchasedQty, 0 )-COALESCE( PSales.soldQty, 0 )+COALESCE( PReturn.soldQty, 0 )+COALESCE( PCosts.tondau, 0 ))* balacneValue) tienton,COALESCE( PReturn.soldQty, 0 ) as thuhoi,COALESCE( PReturn.totalReturn, 0 ) tienthuhoi,COALESCE(PKDUONG.slduong, 0 ) as slduong,COALESCE(PKAM.slam, 0 ) as slam,".$this->db->dbprefix('products') . ".id as product_id,balacneValue,COALESCE(PCosts.totalTondau, 0 ) as tientondau,'xxx' as lhsonchuyenkho", FALSE)
                ->from('products')
                ->join($sp, 'products.id = PSales.product_id', 'left')
                ->join($pp, 'products.id = PCosts.product_id', 'left') 
                ->join($spreturn, 'products.id = PReturn.product_id', 'left') 
                ->join($khoduong, 'products.id = PKDUONG.product_id', 'left')
                ->join($khoam, 'products.id = PKAM.product_id', 'left') 
                ->group_by('products.id');

            if ($product) {
                $this->datatables->where($this->db->dbprefix('products') . ".id", $product);
            }
            if ($cf1) {
                $this->datatables->where($this->db->dbprefix('products') . ".cf1", $cf1);
            }
            if ($cf2) {
                $this->datatables->where($this->db->dbprefix('products') . ".cf2", $cf2);
            }
            if ($cf3) {
                $this->datatables->where($this->db->dbprefix('products') . ".cf3", $cf3);
            }
            if ($cf4) {
                $this->datatables->where($this->db->dbprefix('products') . ".cf4", $cf4);
            }
            if ($cf5) {
                $this->datatables->where($this->db->dbprefix('products') . ".cf5", $cf5);
            }
            if ($cf6) {
                $this->datatables->where($this->db->dbprefix('products') . ".cf6", $cf6);
            }
            if ($category) {
                $this->datatables->where($this->db->dbprefix('products') . ".category_id", $category);
            }
            if ($group_id) {
                $this->datatables->where($this->db->dbprefix('products') . ".group_id", $group_id);
            }
            if ($subcategory) {
                $this->datatables->where($this->db->dbprefix('products') . ".subcategory_id", $subcategory);
            }
            if ($brand) {
                $this->datatables->where($this->db->dbprefix('products') . ".brand", $brand);
            }
            //echo $this->db->get_compiled_select();    
            echo $rs=$this->datatables->generate($output = 'json', $charset = 'UTF-8',$warehouse,$start_date,$end_date); 
            
        }

    }
function getProductsReportBK($pdf = NULL, $xls = NULL)
    {
        $this->sma->checkPermissions('products', TRUE);

        $product = $this->input->get('product') ? $this->input->get('product') : NULL;
        $category = $this->input->get('category') ? $this->input->get('category') : NULL;
        $group_id = $this->input->get('group_id') ? $this->input->get('group_id') : NULL;
        $brand = $this->input->get('brand') ? $this->input->get('brand') : NULL;
        $subcategory = $this->input->get('subcategory') ? $this->input->get('subcategory') : NULL;
        $warehouse = $this->input->get('warehouse') ? $this->input->get('warehouse') : NULL;
        $cf1 = $this->input->get('cf1') ? $this->input->get('cf1') : NULL;
        $cf2 = $this->input->get('cf2') ? $this->input->get('cf2') : NULL;
        $cf3 = $this->input->get('cf3') ? $this->input->get('cf3') : NULL;
        $cf4 = $this->input->get('cf4') ? $this->input->get('cf4') : NULL;
        $cf5 = $this->input->get('cf5') ? $this->input->get('cf5') : NULL;
        $cf6 = $this->input->get('cf6') ? $this->input->get('cf6') : NULL;
        $start_date = $this->input->get('start_date') ? $this->input->get('start_date') : NULL;
        $end_date = $this->input->get('end_date') ? $this->input->get('end_date') : NULL;

        $pp = "( SELECT product_id, SUM(CASE WHEN pi.purchase_id IS NOT NULL THEN unit_quantity ELSE 0 END) as purchasedQty,SUM(CASE WHEN pi.bandau_id>0 THEN ( CASE WHEN unit_quantity!=0 THEN unit_quantity ELSE quantity END) ELSE 0 END) as tondau, SUM(quantity_balance) as balacneQty, unit_cost as balacneValue, SUM( (CASE WHEN pi.purchase_id IS NOT NULL THEN (pi.subtotal) ELSE 0 END) ) totalPurchase from {$this->db->dbprefix('purchase_items')} pi LEFT JOIN {$this->db->dbprefix('purchases')} p on p.id = pi.purchase_id ";
        
        $sp = "( SELECT si.product_id, SUM( si.unit_quantity ) soldQty, SUM( si.subtotal ) totalSale from " . $this->db->dbprefix('sales') . " s JOIN " . $this->db->dbprefix('sale_items') . " si on s.id = si.sale_id ";
        
        $spreturn = "( SELECT re.product_id, SUM( re.quantity ) soldQty, SUM( re.subtotal ) totalReturn from " . $this->db->dbprefix('returns') . " ret JOIN " . $this->db->dbprefix('return_items') . " re on ret.id = re.return_id ";
        
        $khoduong = "( SELECT adi.product_id, SUM( adi.quantity ) slduong from " . $this->db->dbprefix('adjustments') . " adj JOIN " . $this->db->dbprefix('adjustment_items') . " adi on adj.id = adi.adjustment_id AND adi.type='addition'";
        
        $khoam = "( SELECT adia.product_id, SUM( adia.quantity ) slam from " . $this->db->dbprefix('adjustments') . " adja JOIN " . $this->db->dbprefix('adjustment_items') . " adia on adja.id = adia.adjustment_id AND adia.type!='addition'";
        
        if ($start_date || $warehouse) {
            $pp .= " WHERE ";
            $sp .= " WHERE ";
            $spreturn .= " WHERE ";
            $khoduong .= " WHERE ";
            $khoam .= " WHERE ";
            
            if ($start_date) {
                
                $start_date = date("Y-m-d",strtotime($start_date))." 00:00:00";
                $end_date = date("Y-m-d",strtotime($end_date))." 23:59:59";
                
                $pp .= " p.date >= '{$start_date}' AND p.date < '{$end_date}' ";
                $sp .= " s.date >= '{$start_date}' AND s.date < '{$end_date}' ";
                $spreturn .= " ret.date >= '{$start_date}' AND ret.date < '{$end_date}' ";
                $khoduong .= " adj.date >= '{$start_date}' AND adj.date < '{$end_date}' ";
                $khoam .= " adja.date >= '{$start_date}' AND adja.date < '{$end_date}' ";
                
                if ($warehouse) {
                    $pp .= " AND ";
                    $sp .= " AND ";
                    $spreturn .= " AND ";
                    $khoduong .= " AND ";
                    $khoam .= " AND ";
                }
            }
            if ($warehouse) {
                $pp .= " pi.warehouse_id = '{$warehouse}' ";
                $sp .= " si.warehouse_id = '{$warehouse}' ";
                $spreturn .= " ret.warehouse_id = '{$warehouse}' ";
                $khoduong .= " adj.warehouse_id = '{$warehouse}' ";
                $khoam .= " adja.warehouse_id = '{$warehouse}' ";
            }
        }
        $pp .= " GROUP BY pi.product_id ) PCosts";
        $sp .= " GROUP BY si.product_id ) PSales";
        $spreturn .= " GROUP BY re.product_id ) PReturn";
        $khoduong .= " GROUP BY adi.product_id ) PKDUONG";
        $khoam .= " GROUP BY adia.product_id ) PKAM";
        
        if ($pdf || $xls) {

             $this->db
                ->select($this->db->dbprefix('products') . ".code, " . $this->db->dbprefix('products') . ".name,
                COALESCE( PCosts.tondau, 0 ) as tondau,COALESCE( PCosts.purchasedQty, 0 ) as purchased,
                COALESCE( PSales.soldQty, 0 ) as sold,
                (COALESCE( PSales.totalSale, 0 ) - COALESCE( PCosts.totalPurchase, 0 )-COALESCE( PReturn.totalReturn, 0 )) as Profit,
                (COALESCE( PCosts.purchasedQty, 0 )-COALESCE( PSales.soldQty, 0 )+COALESCE( PReturn.soldQty, 0 )+COALESCE( PCosts.tondau, 0 )+COALESCE(PKDUONG.slduong, 0 )-COALESCE(PKAM.slam, 0 )) as balance, {$this->db->dbprefix('products')}.id as id,COALESCE(PSales.totalSale, 0 ) as tienban,COALESCE(PCosts.totalPurchase, 0 ) as tiennhap,((COALESCE( PCosts.purchasedQty, 0 )-COALESCE( PSales.soldQty, 0 )+COALESCE( PReturn.soldQty, 0 )+COALESCE( PCosts.tondau, 0 ))* balacneValue) tienton,COALESCE( PReturn.soldQty, 0 ) as thuhoi,COALESCE( PReturn.totalReturn, 0 ) tienthuhoi,COALESCE(PKDUONG.slduong, 0 ) as slduong,COALESCE(PKAM.slam, 0 ) as slam,".$this->db->dbprefix('products') . ".id as product_id,balacneValue,{$this->db->dbprefix('products')}.quantity as xuattonkho", FALSE)
                ->from('products')
                ->join($sp, 'products.id = PSales.product_id', 'left')
                ->join($pp, 'products.id = PCosts.product_id', 'left') 
                ->join($spreturn, 'products.id = PReturn.product_id', 'left') 
                ->join($khoduong, 'products.id = PKDUONG.product_id', 'left')
                ->join($khoam, 'products.id = PKAM.product_id', 'left') 
                ->group_by('products.id');

            if ($product) {
                $this->db->where($this->db->dbprefix('products') . ".id", $product);
            }
            if ($cf1) {
                $this->db->where($this->db->dbprefix('products') . ".cf1", $cf1);
            }
            if ($cf2) {
                $this->db->where($this->db->dbprefix('products') . ".cf2", $cf2);
            }
            if ($cf3) {
                $this->db->where($this->db->dbprefix('products') . ".cf3", $cf3);
            }
            if ($cf4) {
                $this->db->where($this->db->dbprefix('products') . ".cf4", $cf4);
            }
            if ($cf5) {
                $this->db->where($this->db->dbprefix('products') . ".cf5", $cf5);
            }
            if ($cf6) {
                $this->db->where($this->db->dbprefix('products') . ".cf6", $cf6);
            }
            if ($category) {
                $this->db->where($this->db->dbprefix('products') . ".category_id", $category);
            }
            if ($group_id) {
                $this->db->where($this->db->dbprefix('products') . ".group_id", $group_id);
            }
            if ($subcategory) {
                $this->db->where($this->db->dbprefix('products') . ".subcategory_id", $subcategory);
            }
            if ($brand) {
                $this->db->where($this->db->dbprefix('products') . ".brand", $brand);
            }

            $q = $this->db->get();
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
                $this->excel->getActiveSheet()->setTitle(lang('products_report'));
                $this->excel->getActiveSheet()->SetCellValue('A1', lang('product_code'));
                $this->excel->getActiveSheet()->SetCellValue('B1', lang('product_name'));
                $this->excel->getActiveSheet()->SetCellValue('C1', lang('Đơn vị'));
                $this->excel->getActiveSheet()->SetCellValue('D1', lang('tồn đầu'));
                $this->excel->getActiveSheet()->SetCellValue('E1', lang('purchased'));
                $this->excel->getActiveSheet()->SetCellValue('F1', lang('sold'));
                $this->excel->getActiveSheet()->SetCellValue('G1', lang('Trả hàng'));
                $this->excel->getActiveSheet()->SetCellValue('H1', lang('SL Tồn'));

                $row = 2;
                $nhap = 0;
                $ban = 0;
                $ton = 0;
                $_thuhoi=0;
                $dau=0;
                foreach ($data as $data_row) {

                    $donvi=$this->getdonvitinh($data_row->code);
                    //quy doi sl nhap ve cung sl ban
                    $_str_soluong=$data_row->purchased;                

                     switch($donvi['operator']) {                   
                        case '*':           
                            $_str_soluong=(float)$data_row->purchased/(float)$donvi['operation_value'];
                            break;          
                         case '/':                      
                            $_str_soluong=(float)$data_row->purchased*(float)$donvi['operation_value'];
                            break;      
                         case '+':                      
                            $_str_soluong=(float)$data_row->purchased-(float)$donvi['operation_value'];
                            break;
                        case '-':                   
                            $_str_soluong=(float)$data_row->purchased+(float)$donvi['operation_value'];
                            break;              
                        }
                    $data_row->purchased=$_str_soluong;    

                    $this->excel->getActiveSheet()->SetCellValue('A' . $row, $data_row->code);
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->name);
                    $this->excel->getActiveSheet()->SetCellValue('C' . $row, $donvi['name']);
                    $this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->tondau);
                    $this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->purchased);
                    $this->excel->getActiveSheet()->SetCellValue('F' . $row, $data_row->sold);
                    $this->excel->getActiveSheet()->SetCellValue('G' . $row, $data_row->ReturthuhoinQty);
                    $this->excel->getActiveSheet()->SetCellValue('H' . $row, $data_row->xuattonkho);
                   
                    
                    

                    $dau += $data_row->tondau;
                    $nhap += $data_row->purchased;
                    $ban += $data_row->sold;
                    $ton += $data_row->xuattonkho;                    
                    $_thuhoi +=$data_row->thuhoi;

                    $row++;
                }
                $this->excel->getActiveSheet()->getStyle("D" . $row . ":H" . $row)->getBorders()
                    ->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);
                $this->excel->getActiveSheet()->SetCellValue('D' . $row, $dau);
                $this->excel->getActiveSheet()->SetCellValue('E' . $row, $nhap);
                $this->excel->getActiveSheet()->SetCellValue('F' . $row, $ban);
                $this->excel->getActiveSheet()->SetCellValue('G' . $row, $_thuhoi);
                $this->excel->getActiveSheet()->SetCellValue('H' . $row, $ton);             

                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(35);
                $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(35);
                $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(25);

                $filename = 'products_report';
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
                    $this->excel->getActiveSheet()->getStyle('C2:G' . $row)->getAlignment()->setWrapText(true);
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

        } else {

            $this->load->library('datatables');
            $this->datatables
                ->select($this->db->dbprefix('products') . ".code, " . $this->db->dbprefix('products') . ".name,
                COALESCE( PCosts.tondau, 0 ) as tondau,COALESCE( PCosts.purchasedQty, 0 ) as purchased,
                COALESCE( PSales.soldQty, 0 ) as sold,
                (COALESCE( PSales.totalSale, 0 ) - COALESCE( PCosts.totalPurchase, 0 )-COALESCE( PReturn.totalReturn, 0 )) as Profit,
                (COALESCE( PCosts.purchasedQty, 0 )-COALESCE( PSales.soldQty, 0 )+COALESCE( PReturn.soldQty, 0 )+COALESCE( PCosts.tondau, 0 )+COALESCE(PKDUONG.slduong, 0 )-COALESCE(PKAM.slam, 0 )) as balance, {$this->db->dbprefix('products')}.id as id,COALESCE(PSales.totalSale, 0 ) as tienban,COALESCE(PCosts.totalPurchase, 0 ) as tiennhap,((COALESCE( PCosts.purchasedQty, 0 )-COALESCE( PSales.soldQty, 0 )+COALESCE( PReturn.soldQty, 0 )+COALESCE( PCosts.tondau, 0 ))* balacneValue) tienton,COALESCE( PReturn.soldQty, 0 ) as thuhoi,COALESCE( PReturn.totalReturn, 0 ) tienthuhoi,COALESCE(PKDUONG.slduong, 0 ) as slduong,COALESCE(PKAM.slam, 0 ) as slam,".$this->db->dbprefix('products') . ".id as product_id,balacneValue", FALSE)
                ->from('products')
                ->join($sp, 'products.id = PSales.product_id', 'left')
                ->join($pp, 'products.id = PCosts.product_id', 'left') 
                ->join($spreturn, 'products.id = PReturn.product_id', 'left') 
                ->join($khoduong, 'products.id = PKDUONG.product_id', 'left')
                ->join($khoam, 'products.id = PKAM.product_id', 'left') 
                ->group_by('products.id');

            if ($product) {
                $this->datatables->where($this->db->dbprefix('products') . ".id", $product);
            }
            if ($cf1) {
                $this->datatables->where($this->db->dbprefix('products') . ".cf1", $cf1);
            }
            if ($cf2) {
                $this->datatables->where($this->db->dbprefix('products') . ".cf2", $cf2);
            }
            if ($cf3) {
                $this->datatables->where($this->db->dbprefix('products') . ".cf3", $cf3);
            }
            if ($cf4) {
                $this->datatables->where($this->db->dbprefix('products') . ".cf4", $cf4);
            }
            if ($cf5) {
                $this->datatables->where($this->db->dbprefix('products') . ".cf5", $cf5);
            }
            if ($cf6) {
                $this->datatables->where($this->db->dbprefix('products') . ".cf6", $cf6);
            }
            if ($category) {
                $this->datatables->where($this->db->dbprefix('products') . ".category_id", $category);
            }
            if ($group_id) {
                $this->datatables->where($this->db->dbprefix('products') . ".group_id", $group_id);
            }
            if ($subcategory) {
                $this->datatables->where($this->db->dbprefix('products') . ".subcategory_id", $subcategory);
            }
            if ($brand) {
                $this->datatables->where($this->db->dbprefix('products') . ".brand", $brand);
            }
            //echo $this->db->get_compiled_select();    
            //echo $rs=$this->datatables->generate(); 
            echo $rs=$this->datatables->generate($output = 'json', $charset = 'UTF-8',$warehouse,$start_date,$end_date);
            
        }

    }
    function getdonvitinh($idsp=0){   
    
        $query = $this->db->query("select sale_unit from scodeweb_products where code='$idsp'");        
        $rsdonvi=$query->row_array();       
        $id_donvi=$rsdonvi['sale_unit'];        
        if($id_donvi>0){    
            $query = $this->db->query("select * from scodeweb_units where id='$id_donvi'");     
            return $query->row_array();     
        }   
    }

    function categories()
    {
        $this->sma->checkPermissions('products');
        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['categories'] = $this->site->getAllCategories();
        $this->data['warehouses'] = $this->site->getAllWarehouses();
        if ($this->input->post('start_date')) {
            $dt = "From " . $this->input->post('start_date') . " to " . $this->input->post('end_date');
        } else {
            $dt = "Till " . $this->input->post('end_date');
        }
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('categories_report')));
        $meta = array('page_title' => lang('categories_report'), 'bc' => $bc);
        $this->page_construct('reports/categories', $meta, $this->data);
    }

    function getCategoriesReport($pdf = NULL, $xls = NULL)
    {
        $this->sma->checkPermissions('products', TRUE);
        $warehouse = $this->input->get('warehouse') ? $this->input->get('warehouse') : NULL;
        $category = $this->input->get('category') ? $this->input->get('category') : NULL;
        $start_date = $this->input->get('start_date') ? $this->input->get('start_date') : NULL;
        $end_date = $this->input->get('end_date') ? $this->input->get('end_date') : NULL;

        $pp = "( SELECT pp.category_id as category, SUM( pi.quantity ) purchasedQty, SUM( pi.subtotal ) totalPurchase from {$this->db->dbprefix('products')} pp
                left JOIN " . $this->db->dbprefix('purchase_items') . " pi ON pp.id = pi.product_id
                left join " . $this->db->dbprefix('purchases') . " p ON p.id = pi.purchase_id ";
        $sp = "( SELECT sp.category_id as category, SUM( si.quantity ) soldQty, SUM( si.subtotal ) totalSale from {$this->db->dbprefix('products')} sp
                left JOIN " . $this->db->dbprefix('sale_items') . " si ON sp.id = si.product_id
                left join " . $this->db->dbprefix('sales') . " s ON s.id = si.sale_id ";
        if ($start_date || $warehouse) {
            $pp .= " WHERE ";
            $sp .= " WHERE ";
            if ($start_date) {
                $start_date = $this->sma->fld($start_date);
                $end_date = $end_date ? $this->sma->fld($end_date) : date('Y-m-d');
                $pp .= " p.date >= '{$start_date}' AND p.date < '{$end_date}' ";
                $sp .= " s.date >= '{$start_date}' AND s.date < '{$end_date}' ";
                if ($warehouse) {
                    $pp .= " AND ";
                    $sp .= " AND ";
                }
            }
            if ($warehouse) {
                $pp .= " pi.warehouse_id = '{$warehouse}' ";
                $sp .= " si.warehouse_id = '{$warehouse}' ";
            }
        }
        $pp .= " GROUP BY pp.category_id ) PCosts";
        $sp .= " GROUP BY sp.category_id ) PSales";

        if ($pdf || $xls) {

            $this->db->select($this->db->dbprefix('categories') . ".code, " . $this->db->dbprefix('categories') . ".name,
                    SUM( COALESCE( PCosts.purchasedQty, 0 ) ) as PurchasedQty,
                    SUM( COALESCE( PSales.soldQty, 0 ) ) as SoldQty,
                    SUM( COALESCE( PCosts.totalPurchase, 0 ) ) as TotalPurchase,
                    SUM( COALESCE( PSales.totalSale, 0 ) ) as TotalSales,
                    (SUM( COALESCE( PSales.totalSale, 0 ) )- SUM( COALESCE( PCosts.totalPurchase, 0 ) ) ) as Profit", FALSE)
                ->from('categories')
                ->join($sp, 'categories.id = PSales.category', 'left')
                ->join($pp, 'categories.id = PCosts.category', 'left')
                ->group_by('categories.id, categories.code, categories.name')
                ->order_by('categories.code', 'asc');

            if ($category) {
                $this->db->where($this->db->dbprefix('categories') . ".id", $category);
            }

            $q = $this->db->get();
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
                $this->excel->getActiveSheet()->setTitle(lang('categories_report'));
                $this->excel->getActiveSheet()->SetCellValue('A1', lang('category_code'));
                $this->excel->getActiveSheet()->SetCellValue('B1', lang('category_name'));
                $this->excel->getActiveSheet()->SetCellValue('C1', lang('purchased'));
                $this->excel->getActiveSheet()->SetCellValue('D1', lang('sold'));
                $this->excel->getActiveSheet()->SetCellValue('E1', lang('purchased_amount'));
                $this->excel->getActiveSheet()->SetCellValue('F1', lang('sold_amount'));
                $this->excel->getActiveSheet()->SetCellValue('G1', lang('profit_loss'));

                $row = 2;
                $sQty = 0;
                $pQty = 0;
                $sAmt = 0;
                $pAmt = 0;
                $pl = 0;
                foreach ($data as $data_row) {
                    $profit = $data_row->TotalSales - $data_row->TotalPurchase;
                    $this->excel->getActiveSheet()->SetCellValue('A' . $row, $data_row->code);
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->name);
                    $this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->PurchasedQty);
                    $this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->SoldQty);
                    $this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->TotalPurchase);
                    $this->excel->getActiveSheet()->SetCellValue('F' . $row, $data_row->TotalSales);
                    $this->excel->getActiveSheet()->SetCellValue('G' . $row, $profit);
                    $pQty += $data_row->PurchasedQty;
                    $sQty += $data_row->SoldQty;
                    $pAmt += $data_row->TotalPurchase;
                    $sAmt += $data_row->TotalSales;
                    $pl += $profit;
                    $row++;
                }
                $this->excel->getActiveSheet()->getStyle("C" . $row . ":G" . $row)->getBorders()
                    ->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);
                $this->excel->getActiveSheet()->SetCellValue('C' . $row, $pQty);
                $this->excel->getActiveSheet()->SetCellValue('D' . $row, $sQty);
                $this->excel->getActiveSheet()->SetCellValue('E' . $row, $pAmt);
                $this->excel->getActiveSheet()->SetCellValue('F' . $row, $sAmt);
                $this->excel->getActiveSheet()->SetCellValue('G' . $row, $pl);

                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(35);
                $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(35);
                $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(25);

                $filename = 'categories_report';
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
                    $this->excel->getActiveSheet()->getStyle('C2:G' . $row)->getAlignment()->setWrapText(true);
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

        } else {


            $this->load->library('datatables');
            $this->datatables
                ->select($this->db->dbprefix('categories') . ".id as cid, " .$this->db->dbprefix('categories') . ".code, " . $this->db->dbprefix('categories') . ".name,
                    SUM( COALESCE( PCosts.purchasedQty, 0 ) ) as PurchasedQty,
                    SUM( COALESCE( PSales.soldQty, 0 ) ) as SoldQty,
                    SUM( COALESCE( PCosts.totalPurchase, 0 ) ) as TotalPurchase,
                    SUM( COALESCE( PSales.totalSale, 0 ) ) as TotalSales,
                    (SUM( COALESCE( PSales.totalSale, 0 ) )- SUM( COALESCE( PCosts.totalPurchase, 0 ) ) ) as Profit", FALSE)
                ->from('categories')
                ->join($sp, 'categories.id = PSales.category', 'left')
                ->join($pp, 'categories.id = PCosts.category', 'left');
                
            if ($category) {
                $this->datatables->where('categories.id', $category);
            }
            $this->datatables->group_by('categories.id, categories.code, categories.name, PSales.SoldQty, PSales.totalSale, PCosts.purchasedQty, PCosts.totalPurchase');
            $this->datatables->unset_column('cid');
            echo $this->datatables->generate();

        }

    }

    function brands()
    {
        $this->sma->checkPermissions('products');
        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['brands'] = $this->site->getAllBrands();
        $this->data['warehouses'] = $this->site->getAllWarehouses();
        if ($this->input->post('start_date')) {
            $dt = "From " . $this->input->post('start_date') . " to " . $this->input->post('end_date');
        } else {
            $dt = "Till " . $this->input->post('end_date');
        }
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('brands_report')));
        $meta = array('page_title' => lang('brands_report'), 'bc' => $bc);
        $this->page_construct('reports/brands', $meta, $this->data);
    }

    function getBrandsReport($pdf = NULL, $xls = NULL)
    {
        $this->sma->checkPermissions('products', TRUE);
        $warehouse = $this->input->get('warehouse') ? $this->input->get('warehouse') : NULL;
        $brand = $this->input->get('brand') ? $this->input->get('brand') : NULL;
        $start_date = $this->input->get('start_date') ? $this->input->get('start_date') : NULL;
        $end_date = $this->input->get('end_date') ? $this->input->get('end_date') : NULL;

        $pp = "( SELECT pp.brand as brand, SUM( pi.quantity ) purchasedQty, SUM( pi.subtotal ) totalPurchase from {$this->db->dbprefix('products')} pp
                left JOIN " . $this->db->dbprefix('purchase_items') . " pi ON pp.id = pi.product_id
                left join " . $this->db->dbprefix('purchases') . " p ON p.id = pi.purchase_id ";
        $sp = "( SELECT sp.brand as brand, SUM( si.quantity ) soldQty, SUM( si.subtotal ) totalSale from {$this->db->dbprefix('products')} sp
                left JOIN " . $this->db->dbprefix('sale_items') . " si ON sp.id = si.product_id
                left join " . $this->db->dbprefix('sales') . " s ON s.id = si.sale_id ";
        if ($start_date || $warehouse) {
            $pp .= " WHERE ";
            $sp .= " WHERE ";
            if ($start_date) {
                $start_date = $this->sma->fld($start_date);
                $end_date = $end_date ? $this->sma->fld($end_date) : date('Y-m-d');
                $pp .= " p.date >= '{$start_date}' AND p.date < '{$end_date}' ";
                $sp .= " s.date >= '{$start_date}' AND s.date < '{$end_date}' ";
                if ($warehouse) {
                    $pp .= " AND ";
                    $sp .= " AND ";
                }
            }
            if ($warehouse) {
                $pp .= " pi.warehouse_id = '{$warehouse}' ";
                $sp .= " si.warehouse_id = '{$warehouse}' ";
            }
        }
        $pp .= " GROUP BY pp.brand ) PCosts";
        $sp .= " GROUP BY sp.brand ) PSales";

        if ($pdf || $xls) {

            $this->db->select($this->db->dbprefix('brands') . ".name,
                    SUM( COALESCE( PCosts.purchasedQty, 0 ) ) as PurchasedQty,
                    SUM( COALESCE( PSales.soldQty, 0 ) ) as SoldQty,
                    SUM( COALESCE( PCosts.totalPurchase, 0 ) ) as TotalPurchase,
                    SUM( COALESCE( PSales.totalSale, 0 ) ) as TotalSales,
                    (SUM( COALESCE( PSales.totalSale, 0 ) )- SUM( COALESCE( PCosts.totalPurchase, 0 ) ) ) as Profit", FALSE)
                ->from('brands')
                ->join($sp, 'brands.id = PSales.brand', 'left')
                ->join($pp, 'brands.id = PCosts.brand', 'left')
                ->group_by('brands.id, brands.name')
                ->order_by('brands.code', 'asc');

            if ($brand) {
                $this->db->where($this->db->dbprefix('brands') . ".id", $brand);
            }

            $q = $this->db->get();
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
                $this->excel->getActiveSheet()->setTitle(lang('brands_report'));
                $this->excel->getActiveSheet()->SetCellValue('A1', lang('brands'));
                $this->excel->getActiveSheet()->SetCellValue('B1', lang('purchased'));
                $this->excel->getActiveSheet()->SetCellValue('C1', lang('sold'));
                $this->excel->getActiveSheet()->SetCellValue('D1', lang('purchased_amount'));
                $this->excel->getActiveSheet()->SetCellValue('E1', lang('sold_amount'));
                $this->excel->getActiveSheet()->SetCellValue('F1', lang('profit_loss'));

                $row = 2; $sQty = 0; $pQty = 0; $sAmt = 0; $pAmt = 0; $pl = 0;
                foreach ($data as $data_row) {
                    $profit = $data_row->TotalSales - $data_row->TotalPurchase;
                    $this->excel->getActiveSheet()->SetCellValue('A' . $row, $data_row->name);
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->PurchasedQty);
                    $this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->SoldQty);
                    $this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->TotalPurchase);
                    $this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->TotalSales);
                    $this->excel->getActiveSheet()->SetCellValue('F' . $row, $profit);
                    $pQty += $data_row->PurchasedQty;
                    $sQty += $data_row->SoldQty;
                    $pAmt += $data_row->TotalPurchase;
                    $sAmt += $data_row->TotalSales;
                    $pl += $profit;
                    $row++;
                }
                $this->excel->getActiveSheet()->getStyle("B" . $row . ":F" . $row)->getBorders()
                    ->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);
                $this->excel->getActiveSheet()->SetCellValue('B' . $row, $pQty);
                $this->excel->getActiveSheet()->SetCellValue('C' . $row, $sQty);
                $this->excel->getActiveSheet()->SetCellValue('D' . $row, $pAmt);
                $this->excel->getActiveSheet()->SetCellValue('E' . $row, $sAmt);
                $this->excel->getActiveSheet()->SetCellValue('F' . $row, $pl);

                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(35);
                $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(25);

                $filename = 'brands_report';
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
                    $this->excel->getActiveSheet()->getStyle('C2:G' . $row)->getAlignment()->setWrapText(true);
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

        } else {


            $this->load->library('datatables');
            $this->datatables
                ->select($this->db->dbprefix('brands') . ".id as id, " . $this->db->dbprefix('brands') . ".name,
                    SUM( COALESCE( PCosts.purchasedQty, 0 ) ) as PurchasedQty,
                    SUM( COALESCE( PSales.soldQty, 0 ) ) as SoldQty,
                    SUM( COALESCE( PCosts.totalPurchase, 0 ) ) as TotalPurchase,
                    SUM( COALESCE( PSales.totalSale, 0 ) ) as TotalSales,
                    (SUM( COALESCE( PSales.totalSale, 0 ) )- SUM( COALESCE( PCosts.totalPurchase, 0 ) ) ) as Profit", FALSE)
                ->from('brands')
                ->join($sp, 'brands.id = PSales.brand', 'left')
                ->join($pp, 'brands.id = PCosts.brand', 'left');
                
            if ($brand) {
                $this->datatables->where('brands.id', $brand);
            }
            $this->datatables->group_by('brands.id, brands.name, PSales.SoldQty, PSales.totalSale, PCosts.purchasedQty, PCosts.totalPurchase');
            $this->datatables->unset_column('id');
            echo $this->datatables->generate();

        }

    }
	function xuatxu()
    {
        $this->sma->checkPermissions('products');
        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['xuatxu'] = $this->site->getAllXuatxus();
        $this->data['warehouses'] = $this->site->getAllWarehouses();
        if ($this->input->post('start_date')) {
            $dt = "From " . $this->input->post('start_date') . " to " . $this->input->post('end_date');
        } else {
            $dt = "Till " . $this->input->post('end_date');
        }
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('reports'), 'page' => 'Xuất xứ'), array('link' => '#', 'page' => lang('brands_report')));
        $meta = array('page_title' => 'Báo cáo xuất xứ', 'bc' => $bc);
        $this->page_construct('reports/xuatxu', $meta, $this->data);
    }

    function getXuatXusReport($pdf = NULL, $xls = NULL)
    {
        $this->sma->checkPermissions('products', TRUE);
        $warehouse = $this->input->get('warehouse') ? $this->input->get('warehouse') : NULL;
        $brand = $this->input->get('brand') ? $this->input->get('brand') : NULL;
        $start_date = $this->input->get('start_date') ? $this->input->get('start_date') : NULL;
        $end_date = $this->input->get('end_date') ? $this->input->get('end_date') : NULL;

        $pp = "( SELECT pp.brand as brand, SUM( pi.quantity ) purchasedQty, SUM( pi.subtotal ) totalPurchase from {$this->db->dbprefix('products')} pp
                left JOIN " . $this->db->dbprefix('purchase_items') . " pi ON pp.id = pi.product_id
                left join " . $this->db->dbprefix('purchases') . " p ON p.id = pi.purchase_id ";
        $sp = "( SELECT sp.brand as brand, SUM( si.quantity ) soldQty, SUM( si.subtotal ) totalSale from {$this->db->dbprefix('products')} sp
                left JOIN " . $this->db->dbprefix('sale_items') . " si ON sp.id = si.product_id
                left join " . $this->db->dbprefix('sales') . " s ON s.id = si.sale_id ";
        if ($start_date || $warehouse) {
            $pp .= " WHERE ";
            $sp .= " WHERE ";
            if ($start_date) {
                $start_date = $this->sma->fld($start_date);
                $end_date = $end_date ? $this->sma->fld($end_date) : date('Y-m-d');
                $pp .= " p.date >= '{$start_date}' AND p.date < '{$end_date}' ";
                $sp .= " s.date >= '{$start_date}' AND s.date < '{$end_date}' ";
                if ($warehouse) {
                    $pp .= " AND ";
                    $sp .= " AND ";
                }
            }
            if ($warehouse) {
                $pp .= " pi.warehouse_id = '{$warehouse}' ";
                $sp .= " si.warehouse_id = '{$warehouse}' ";
            }
        }
        $pp .= " GROUP BY pp.brand ) PCosts";
        $sp .= " GROUP BY sp.brand ) PSales";

        if ($pdf || $xls) {

            $this->db->select($this->db->dbprefix('xuatxu') . ".name,
                    SUM( COALESCE( PCosts.purchasedQty, 0 ) ) as PurchasedQty,
                    SUM( COALESCE( PSales.soldQty, 0 ) ) as SoldQty,
                    SUM( COALESCE( PCosts.totalPurchase, 0 ) ) as TotalPurchase,
                    SUM( COALESCE( PSales.totalSale, 0 ) ) as TotalSales,
                    (SUM( COALESCE( PSales.totalSale, 0 ) )- SUM( COALESCE( PCosts.totalPurchase, 0 ) ) ) as Profit", FALSE)
                ->from('xuatxu')
                ->join($sp, 'xuatxu.id = PSales.brand', 'left')
                ->join($pp, 'xuatxu.id = PCosts.brand', 'left')
                ->group_by('xuatxu.id, xuatxu.name')
                ->order_by('xuatxu.code', 'asc');

            if ($brand) {
                $this->db->where($this->db->dbprefix('xuatxu') . ".id", $brand);
            }
			//echo $this->db->get_compiled_select();
            $q = $this->db->get();
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
                $this->excel->getActiveSheet()->setTitle('Báo cáo xuất xứ');
                $this->excel->getActiveSheet()->SetCellValue('A1', lang('xuatxu'));
                $this->excel->getActiveSheet()->SetCellValue('B1', lang('purchased'));
                $this->excel->getActiveSheet()->SetCellValue('C1', lang('sold'));
                $this->excel->getActiveSheet()->SetCellValue('D1', lang('purchased_amount'));
                $this->excel->getActiveSheet()->SetCellValue('E1', lang('sold_amount'));
                $this->excel->getActiveSheet()->SetCellValue('F1', lang('profit_loss'));

                $row = 2; $sQty = 0; $pQty = 0; $sAmt = 0; $pAmt = 0; $pl = 0;
                foreach ($data as $data_row) {
                    $profit = $data_row->TotalSales - $data_row->TotalPurchase;
                    $this->excel->getActiveSheet()->SetCellValue('A' . $row, $data_row->name);
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->PurchasedQty);
                    $this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->SoldQty);
                    $this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->TotalPurchase);
                    $this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->TotalSales);
                    $this->excel->getActiveSheet()->SetCellValue('F' . $row, $profit);
                    $pQty += $data_row->PurchasedQty;
                    $sQty += $data_row->SoldQty;
                    $pAmt += $data_row->TotalPurchase;
                    $sAmt += $data_row->TotalSales;
                    $pl += $profit;
                    $row++;
                }
                $this->excel->getActiveSheet()->getStyle("B" . $row . ":F" . $row)->getBorders()
                    ->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);
                $this->excel->getActiveSheet()->SetCellValue('B' . $row, $pQty);
                $this->excel->getActiveSheet()->SetCellValue('C' . $row, $sQty);
                $this->excel->getActiveSheet()->SetCellValue('D' . $row, $pAmt);
                $this->excel->getActiveSheet()->SetCellValue('E' . $row, $sAmt);
                $this->excel->getActiveSheet()->SetCellValue('F' . $row, $pl);

                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(35);
                $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(25);

                $filename = 'xuatxu_report';
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
                    $this->excel->getActiveSheet()->getStyle('C2:G' . $row)->getAlignment()->setWrapText(true);
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

        } else {


            $this->load->library('datatables');
            $this->datatables
                ->select($this->db->dbprefix('xuatxu') . ".id as id, " . $this->db->dbprefix('xuatxu') . ".name,
                    SUM( COALESCE( PCosts.purchasedQty, 0 ) ) as PurchasedQty,
                    SUM( COALESCE( PSales.soldQty, 0 ) ) as SoldQty,
                    SUM( COALESCE( PCosts.totalPurchase, 0 ) ) as TotalPurchase,
                    SUM( COALESCE( PSales.totalSale, 0 ) ) as TotalSales,
                    (SUM( COALESCE( PSales.totalSale, 0 ) )- SUM( COALESCE( PCosts.totalPurchase, 0 ) ) ) as Profit", FALSE)
                ->from('xuatxu')
                ->join($sp, 'xuatxu.id = PSales.brand', 'left')
                ->join($pp, 'xuatxu.id = PCosts.brand', 'left');
                
            if ($brand) {
                $this->datatables->where('xuatxu.id', $brand);
            }
            $this->datatables->group_by('xuatxu.id, xuatxu.name, PSales.SoldQty, PSales.totalSale, PCosts.purchasedQty, PCosts.totalPurchase');
            $this->datatables->unset_column('id');
			//echo $this->db->get_compiled_select();
            echo $this->datatables->generate();

        }

    }

    function profit($date = NULL, $warehouse_id = NULL)
    {
        if ( ! $this->Owner) {
            $this->session->set_flashdata('error', lang('access_denied'));
            $this->sma->md();
        }
        if($date=='today'){
            $date = date('Y-m-d');
        }
        if ( ! $date) { $date = date('Y-m-d'); }

        //$this->data['costing'] = $this->reports_model->getCosting($date, $warehouse_id);

        $this->data['costing']->sales = $this->reports_model->getTotalSales($this->db->escape($date.' 00:00:00'),$this->db->escape($date.' 23:59:59'),$warehouse_id)->total_amount;         
       // $this->data['costing']->cost=$this->reports_model->getTotalPurchasesThuan($date,$date, $warehouse_id)->total_amount;

         $this->data['costing']->cost = $this->reports_model->getTotalPurchasesThuanV2020($date, $date,$warehouse_id);

        //echo var_dump($this->data['costing']);

        $this->data['thukhac'] = $this->reports_model->getTongThuKhacLN($date, $warehouse_id);
        
        $this->data['discount'] = $this->reports_model->getOrderDiscount($date, $warehouse_id);
        $this->data['expenses'] = $this->reports_model->getExpenses($date, $warehouse_id);
        $this->data['returns'] = $this->reports_model->getReturns($date, $warehouse_id);
        $this->data['date'] = $date;
        $this->data['warehouse_id'] = $warehouse_id;
        $this->data['warehouses'] = $this->site->getAllWarehouses();
        $this->load->view($this->theme . 'reports/profit', $this->data);
    }
    function monthly_profit($year, $month, $warehouse_id = NULL)
    {
        if ( ! $this->Owner) {
            $this->session->set_flashdata('error', lang('access_denied'));
            $this->sma->md();
        }
        
        $this->data['costing'] = $this->reports_model->getCosting(NULL, $warehouse_id, $year, $month);
		
		$this->data['thukhac'] = $this->reports_model->getTongThuKhacLN($date, $warehouse_id);
		
        $this->data['discount'] = $this->reports_model->getOrderDiscount(NULL, $warehouse_id, $year, $month);
        $this->data['expenses'] = $this->reports_model->getExpenses(NULL, $warehouse_id, $year, $month);
        $this->data['returns'] = $this->reports_model->getReturns(NULL, $warehouse_id, $year, $month);
        $this->data['date'] = date('F Y', strtotime($year.'-'.$month.'-'.'01'));
        $this->load->view($this->theme . 'reports/monthly_profit', $this->data);
    }

    function daily_sales($warehouse_id = NULL, $year = NULL, $month = NULL, $pdf = NULL, $user_id = NULL)
    {
        $this->sma->checkPermissions();
        if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
            $warehouse_id = $this->session->userdata('warehouse_id');
        }
        if (!$year) {
            $year = date('Y');
        }
        if (!$month) {
            $month = date('m');
        }
        if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $user_id = $this->session->userdata('user_id');
        }
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $config = array(
            'show_next_prev' => TRUE,
            'next_prev_url' => site_url('reports/daily_sales/'.($warehouse_id ? $warehouse_id : 0)),
            'month_type' => 'long',
            'day_type' => 'long'
        );

        $config['template'] = '{table_open}<div class="table-responsive"><table border="0" cellpadding="0" cellspacing="0" class="table table-bordered dfTable">{/table_open}
		{heading_row_start}<tr>{/heading_row_start}
		{heading_previous_cell}<th><a href="{previous_url}">&lt;&lt;</a></th>{/heading_previous_cell}
		{heading_title_cell}<th colspan="{colspan}" id="month_year">{heading}</th>{/heading_title_cell}
		{heading_next_cell}<th><a href="{next_url}">&gt;&gt;</a></th>{/heading_next_cell}
		{heading_row_end}</tr>{/heading_row_end}
		{week_row_start}<tr>{/week_row_start}
		{week_day_cell}<td class="cl_wday">{week_day}</td>{/week_day_cell}
		{week_row_end}</tr>{/week_row_end}
		{cal_row_start}<tr class="days">{/cal_row_start}
		{cal_cell_start}<td class="day">{/cal_cell_start}
		{cal_cell_content}
		<div class="day_num">{day}</div>
		<div class="content">{content}</div>
		{/cal_cell_content}
		{cal_cell_content_today}
		<div class="day_num highlight">{day}</div>
		<div class="content">{content}</div>
		{/cal_cell_content_today}
		{cal_cell_no_content}<div class="day_num">{day}</div>{/cal_cell_no_content}
		{cal_cell_no_content_today}<div class="day_num highlight">{day}</div>{/cal_cell_no_content_today}
		{cal_cell_blank}&nbsp;{/cal_cell_blank}
		{cal_cell_end}</td>{/cal_cell_end}
		{cal_row_end}</tr>{/cal_row_end}
		{table_close}</table></div>{/table_close}';

        $this->load->library('calendar', $config);
        $sales = $user_id ? $this->reports_model->getStaffDailySales($user_id, $year, $month, $warehouse_id) : $this->reports_model->getDailySales($year, $month, $warehouse_id);

        if (!empty($sales)) {
            foreach ($sales as $sale) {
                $daily_sale[$sale->date] = "<table class='table table-bordered table-hover table-striped table-condensed data' style='margin:0;'><tr><td>" . lang("discount") . "</td><td>" . $this->sma->formatMoney($sale->discount) . "</td></tr><tr><td>" . lang("shipping") . "</td><td>" . $this->sma->formatMoney($sale->shipping) . "</td></tr><tr><td>" . lang("product_tax") . "</td><td>" . $this->sma->formatMoney($sale->tax1) . "</td></tr><tr><td>" . lang("order_tax") . "</td><td>" . $this->sma->formatMoney($sale->tax2) . "</td></tr><tr><td>" . lang("total") . "</td><td>" . $this->sma->formatMoney($sale->total) . "</td></tr></table>";
            }
        } else {
            $daily_sale = array();
        }

        $this->data['calender'] = $this->calendar->generate($year, $month, $daily_sale);
        $this->data['year'] = $year;
        $this->data['month'] = $month;
        if ($pdf) {
            $html = $this->load->view($this->theme . 'reports/daily', $this->data, true);
            $name = lang("daily_sales") . "_" . $year . "_" . $month . ".pdf";
            $html = str_replace('<p class="introtext">' . lang("reports_calendar_text") . '</p>', '', $html);
            $this->sma->generate_pdf($html, $name, null, null, null, null, null, 'L');
        }
        $this->data['warehouses'] = $this->site->getAllWarehouses();
        $this->data['warehouse_id'] = $warehouse_id;
        $this->data['sel_warehouse'] = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : NULL;
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('daily_sales_report')));
        $meta = array('page_title' => lang('daily_sales_report'), 'bc' => $bc);
        $this->page_construct('reports/daily', $meta, $this->data);

    }


    function monthly_sales($warehouse_id = NULL, $year = NULL, $pdf = NULL, $user_id = NULL)
    {
        $this->sma->checkPermissions();
        if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
            $warehouse_id = $this->session->userdata('warehouse_id');
        }
        if (!$year) {
            $year = date('Y');
        }
        if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $user_id = $this->session->userdata('user_id');
        }
        $this->load->language('calendar');
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['year'] = $year;
        $this->data['sales'] = $user_id ? $this->reports_model->getStaffMonthlySales($user_id, $year, $warehouse_id) : $this->reports_model->getMonthlySales($year, $warehouse_id);
        if ($pdf) {
            $html = $this->load->view($this->theme . 'reports/monthly', $this->data, true);
            $name = lang("monthly_sales") . "_" . $year . ".pdf";
            $html = str_replace('<p class="introtext">' . lang("reports_calendar_text") . '</p>', '', $html);
            $this->sma->generate_pdf($html, $name, null, null, null, null, null, 'L');
        }
        $this->data['warehouses'] = $this->site->getAllWarehouses();
        $this->data['warehouse_id'] = $warehouse_id;
        $this->data['sel_warehouse'] = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : NULL;
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('monthly_sales_report')));
        $meta = array('page_title' => lang('monthly_sales_report'), 'bc' => $bc);
        $this->page_construct('reports/monthly', $meta, $this->data);

    }

    function sales()
    {
        $this->sma->checkPermissions('sales');
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['users'] = $this->reports_model->getStaff();
        $this->data['warehouses'] = $this->site->getAllWarehouses();
        $this->data['billers'] = $this->site->getAllCompanies('biller');
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('sales_report')));
        $meta = array('page_title' => lang('sales_report'), 'bc' => $bc);
        $this->page_construct('reports/sales', $meta, $this->data);
    }
	function defineTenSanPhamExport($x=null){
		$d = '';
		$pqc = explode("___",$x);
        for($index = 0; $index < count($pqc); $index++) {
            $pq = $pqc[$index];
            $v = explode("___",$pq);
			if($v[2]){
				$d.= $v[0].' ('.$this->sma->formatDecimal($v[1]).' * '.$this->sma->formatMoney(v[2]).')<br>';
			}else{
				$d.= $v[0].' ('.$this->sma->formatDecimal($v[1]).')<br>';
			}
        }
        return $d;
	}
    function getSalesReport($pdf = NULL, $xls = NULL)
    {
        $this->sma->checkPermissions('sales', TRUE);
        $product = $this->input->get('product') ? $this->input->get('product') : NULL;
        $user = $this->input->get('user') ? $this->input->get('user') : NULL;
        $customer = $this->input->get('customer') ? $this->input->get('customer') : NULL;
        $biller = $this->input->get('biller') ? $this->input->get('biller') : NULL;
        $warehouse = $this->input->get('warehouse') ? $this->input->get('warehouse') : NULL;
        $reference_no = $this->input->get('reference_no') ? $this->input->get('reference_no') : NULL;
        $start_date = $this->input->get('start_date') ? $this->input->get('start_date') : NULL;
        $end_date = $this->input->get('end_date') ? $this->input->get('end_date') : NULL;
        $serial = $this->input->get('serial') ? $this->input->get('serial') : NULL;
        $payment_status = $this->input->get('payment_status') ? $this->input->get('payment_status') : NULL;

        if ($start_date) {
            $start_date = $this->sma->fld($start_date);
            $end_date = $this->sma->fld($end_date);
        }
        if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $user = $this->session->userdata('user_id');
        }

        if ($pdf || $xls) {

            $this->db->select("date, reference_no,(select name from scodeweb_warehouses where id=scodeweb_sales.warehouse_id) as kho,(SELECT CONCAT({$this->db->dbprefix('doitac')}.code, '-', {$this->db->dbprefix('doitac')}.name) FROM {$this->db->dbprefix('doitac')} WHERE id=doitac) as doitac, biller, sales.customer_id, GROUP_CONCAT(CONCAT(" . $this->db->dbprefix('sale_items') . ".product_name, ' (',CONCAT(scodeweb_sale_items.unit_quantity,'*',scodeweb_sale_items.unit_price), ')') SEPARATOR '\n') as iname, grand_total, paid, payment_status", FALSE)
                ->from('sales')
                ->join('sale_items', 'sale_items.sale_id=sales.id', 'left')
                ->join('warehouses', 'warehouses.id=sales.warehouse_id', 'left')
                ->group_by('sales.id')
                ->order_by('sales.date desc');
			$this->db->where('sales.sale_status !=','returned');
            if ($user) {
                $this->db->where('sales.created_by', $user);
            }
            if ($product) {
                $this->db->where('sale_items.product_id', $product);
            }
            if ($serial) {
                $this->db->like('sale_items.serial_no', $serial);
            }
            if ($biller) {
                $this->db->where('sales.biller_id', $biller);
            }
            if ($customer) {
                $this->db->where('sales.customer_id', $customer);
            }
            if ($warehouse) {
                $this->db->where('sales.warehouse_id', $warehouse);
            }
            if ($payment_status) {
                $this->datatables->where('sales.payment_status', $payment_status);
            }
            if ($reference_no) {
                $this->db->like('sales.reference_no', $reference_no, 'both');
            }
            if ($start_date) {
                $this->db->where($this->db->dbprefix('sales').'.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
            }
			
			//$this->db->_compile_select(); 
            $q = $this->db->get();
			
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
                $this->excel->getActiveSheet()->setTitle(lang('sales_report'));
                $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                $this->excel->getActiveSheet()->SetCellValue('B1', lang('reference_no'));
                $this->excel->getActiveSheet()->SetCellValue('C1', lang('Kho'));
				$this->excel->getActiveSheet()->SetCellValue('D1', lang('ĐVGH'));
				$this->excel->getActiveSheet()->SetCellValue('E1', lang('biller'));
                $this->excel->getActiveSheet()->SetCellValue('F1', lang('customer'));
                $this->excel->getActiveSheet()->SetCellValue('G1', lang('product_qty'));
                $this->excel->getActiveSheet()->SetCellValue('H1', lang('grand_total'));
                $this->excel->getActiveSheet()->SetCellValue('I1', lang('paid'));
                $this->excel->getActiveSheet()->SetCellValue('J1', lang('balance'));
                $this->excel->getActiveSheet()->SetCellValue('K1', lang('payment_status'));

                $row = 2;
                $total = 0;
                $paid = 0;
                $balance = 0;
                foreach ($data as $data_row) {
					
					$customer= $this->site->getCompanyByID($data_row->customer_id); 
					$_customer=$customer->phone."-".$customer->name;
					
					//$tensanpham=$this->defineTenSanPhamExport($data_row->iname);
					
                    $this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->sma->hrld($data_row->date));
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->reference_no);
                    $this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->kho);
					$this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->doitac);
					$this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->biller);
                    $this->excel->getActiveSheet()->SetCellValue('F' . $row, $_customer);
                    $this->excel->getActiveSheet()->SetCellValue('G' . $row, $data_row->iname);
                    $this->excel->getActiveSheet()->SetCellValue('H' . $row, $data_row->grand_total);
                    $this->excel->getActiveSheet()->SetCellValue('I' . $row, $data_row->paid);
                    $this->excel->getActiveSheet()->SetCellValue('J' . $row, ($data_row->grand_total - $data_row->paid));
                    $this->excel->getActiveSheet()->SetCellValue('K' . $row, lang($data_row->payment_status));
                    $total += $data_row->grand_total;
                    $paid += $data_row->paid;
                    $balance += ($data_row->grand_total - $data_row->paid);
                    $row++;
                }
                $this->excel->getActiveSheet()->getStyle("H" . $row . ":J" . $row)->getBorders()
                    ->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);
                $this->excel->getActiveSheet()->SetCellValue('H' . $row, $total);
                $this->excel->getActiveSheet()->SetCellValue('I' . $row, $paid);
                $this->excel->getActiveSheet()->SetCellValue('J' . $row, $balance);

                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(35);
                $this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('I')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('J')->setWidth(20);
                $filename = 'sales_report';
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
                    $this->excel->getActiveSheet()->getStyle('E2:E' . $row)->getAlignment()->setWrapText(true);
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

        } else {

            $si = "( SELECT sale_id, product_id, serial_no, GROUP_CONCAT(CONCAT(CONCAT({$this->db->dbprefix('sale_items')}.product_name,'-',{$this->db->dbprefix('sale_items')}.product_unit_code), '__',CONCAT(scodeweb_sale_items.unit_quantity,'__',scodeweb_sale_items.unit_price)) SEPARATOR '___') as item_nane from {$this->db->dbprefix('sale_items')} ";
            if ($product) {
                $si .= " WHERE {$this->db->dbprefix('sale_items')}.product_id = {$product} ";
            }
            $si .= " GROUP BY {$this->db->dbprefix('sale_items')}.sale_id ) FSI";
            $this->load->library('datatables');
            $this->datatables
                ->select("DATE_FORMAT(date, '%Y-%m-%d %T') as date, reference_no,(select name from scodeweb_warehouses where id=scodeweb_sales.warehouse_id) as kho,(SELECT CONCAT({$this->db->dbprefix('doitac')}.code, '-', {$this->db->dbprefix('doitac')}.name) FROM {$this->db->dbprefix('doitac')} WHERE id=doitac) as doitac, biller, (SELECT CONCAT(scodeweb_companies.name,'-',scodeweb_companies.phone) FROM scodeweb_companies WHERE id=scodeweb_sales.customer_id) as customer, FSI.item_nane as iname, grand_total, paid, (grand_total-paid) as balance, payment_status, {$this->db->dbprefix('sales')}.id as id", FALSE)
                ->from('sales')
                ->join($si, 'FSI.sale_id=sales.id', 'left')
                ->join('warehouses', 'warehouses.id=sales.warehouse_id', 'left');
                // ->group_by('sales.id');
			$this->datatables->where('sales.sale_status !=','returned');
            if ($user) {
                $this->datatables->where('sales.created_by', $user);
            }
            if ($product) {
                $this->datatables->where('FSI.product_id', $product, FALSE);
            }
            if ($serial) {
                $this->datatables->like('FSI.serial_no', $serial, FALSE);
            }
            if ($biller) {
                $this->datatables->where('sales.biller_id', $biller);
            }
            if ($customer) {
                $this->datatables->where('sales.customer_id', $customer);
            }
            if ($warehouse) {
                $this->datatables->where('sales.warehouse_id', $warehouse);
            }
            if ($payment_status) {
                $this->datatables->where('sales.payment_status', $payment_status);
            }
            if ($reference_no) {
                $this->datatables->like('sales.reference_no', $reference_no, 'both');
            }
            if ($start_date) {
                $this->datatables->where($this->db->dbprefix('sales').'.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
            }

            echo $this->datatables->generate();

        }

    }

    function getQuotesReport($pdf = NULL, $xls = NULL)
    {

        if ($this->input->get('product')) {
            $product = $this->input->get('product');
        } else {
            $product = NULL;
        }
        if ($this->input->get('user')) {
            $user = $this->input->get('user');
        } else {
            $user = NULL;
        }
        if ($this->input->get('customer')) {
            $customer = $this->input->get('customer');
        } else {
            $customer = NULL;
        }
        if ($this->input->get('biller')) {
            $biller = $this->input->get('biller');
        } else {
            $biller = NULL;
        }
        if ($this->input->get('warehouse')) {
            $warehouse = $this->input->get('warehouse');
        } else {
            $warehouse = NULL;
        }
        if ($this->input->get('reference_no')) {
            $reference_no = $this->input->get('reference_no');
        } else {
            $reference_no = NULL;
        }
        if ($this->input->get('start_date')) {
            $start_date = $this->input->get('start_date');
        } else {
            $start_date = NULL;
        }
        if ($this->input->get('end_date')) {
            $end_date = $this->input->get('end_date');
        } else {
            $end_date = NULL;
        }
        if ($start_date) {
            $start_date = $this->sma->fld($start_date);
            $end_date = $this->sma->fld($end_date);
        }
        if ($pdf || $xls) {

            $this->db->select("date, reference_no, biller, customer, GROUP_CONCAT(CONCAT(" . $this->db->dbprefix('quote_items') . ".product_name, ' (', " . $this->db->dbprefix('quote_items') . ".quantity, ')') SEPARATOR '<br>') as iname, grand_total, status", FALSE)
                ->from('quotes')
                ->join('quote_items', 'quote_items.quote_id=quotes.id', 'left')
                ->join('warehouses', 'warehouses.id=quotes.warehouse_id', 'left')
                ->group_by('quotes.id');

            if ($user) {
                $this->db->where('quotes.created_by', $user);
            }
            if ($product) {
                $this->db->where('quote_items.product_id', $product);
            }
            if ($biller) {
                $this->db->where('quotes.biller_id', $biller);
            }
            if ($customer) {
                $this->db->where('quotes.customer_id', $customer);
            }
            if ($warehouse) {
                $this->db->where('quotes.warehouse_id', $warehouse);
            }
            if ($reference_no) {
                $this->db->like('quotes.reference_no', $reference_no, 'both');
            }
            if ($start_date) {
                $this->db->where($this->db->dbprefix('quotes').'.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
            }

            $q = $this->db->get();
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
                $this->excel->getActiveSheet()->setTitle(lang('quotes_report'));
                $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                $this->excel->getActiveSheet()->SetCellValue('B1', lang('reference_no'));
                $this->excel->getActiveSheet()->SetCellValue('C1', lang('biller'));
                $this->excel->getActiveSheet()->SetCellValue('D1', lang('customer'));
                $this->excel->getActiveSheet()->SetCellValue('E1', lang('product_qty'));
                $this->excel->getActiveSheet()->SetCellValue('F1', lang('grand_total'));
                $this->excel->getActiveSheet()->SetCellValue('G1', lang('status'));

                $row = 2;
                foreach ($data as $data_row) {
                    $this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->sma->hrld($data_row->date));
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->reference_no);
                    $this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->biller);
                    $this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->customer);
                    $this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->iname);
                    $this->excel->getActiveSheet()->SetCellValue('F' . $row, $data_row->grand_total);
                    $this->excel->getActiveSheet()->SetCellValue('G' . $row, $data_row->status);
                    $row++;
                }

                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(30);
                $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
                $filename = 'quotes_report';
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
                    $this->excel->getActiveSheet()->getStyle('E2:E' . $row)->getAlignment()->setWrapText(true);
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

        } else {

            $qi = "( SELECT quote_id, product_id, GROUP_CONCAT(CONCAT({$this->db->dbprefix('quote_items')}.product_name, '__', {$this->db->dbprefix('quote_items')}.quantity) SEPARATOR '___') as item_nane from {$this->db->dbprefix('quote_items')} ";
            if ($product) {
                $qi .= " WHERE {$this->db->dbprefix('quote_items')}.product_id = {$product} ";
            }
            $qi .= " GROUP BY {$this->db->dbprefix('quote_items')}.quote_id ) FQI";
            $this->load->library('datatables');
            $this->datatables
                ->select("date, reference_no, biller, customer, FQI.item_nane as iname, grand_total, status, {$this->db->dbprefix('quotes')}.id as id", FALSE)
                ->from('quotes')
                ->join($qi, 'FQI.quote_id=quotes.id', 'left')
                ->join('warehouses', 'warehouses.id=quotes.warehouse_id', 'left')
                ->group_by('quotes.id');

            if ($user) {
                $this->datatables->where('quotes.created_by', $user);
            }
            if ($product) {
                $this->datatables->where('FQI.product_id', $product, FALSE);
            }
            if ($biller) {
                $this->datatables->where('quotes.biller_id', $biller);
            }
            if ($customer) {
                $this->datatables->where('quotes.customer_id', $customer);
            }
            if ($warehouse) {
                $this->datatables->where('quotes.warehouse_id', $warehouse);
            }
            if ($reference_no) {
                $this->datatables->like('quotes.reference_no', $reference_no, 'both');
            }
            if ($start_date) {
                $this->datatables->where($this->db->dbprefix('quotes').'.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
            }

            echo $this->datatables->generate();

        }

    }

    function getTransfersReport($pdf = NULL, $xls = NULL)
    {
        if ($this->input->get('product')) {
            $product = $this->input->get('product');
        } else {
            $product = NULL;
        }

        if ($pdf || $xls) {

            $this->db->select($this->db->dbprefix('transfers') . ".date, transfer_no, (CASE WHEN " . $this->db->dbprefix('transfers') . ".status = 'completed' THEN  GROUP_CONCAT(CONCAT(" . $this->db->dbprefix('purchase_items') . ".product_name, ' (', " . $this->db->dbprefix('purchase_items') . ".quantity, ')') SEPARATOR '<br>') ELSE GROUP_CONCAT(CONCAT(" . $this->db->dbprefix('transfer_items') . ".product_name, ' (', " . $this->db->dbprefix('transfer_items') . ".quantity, ')') SEPARATOR '<br>') END) as iname, from_warehouse_name as fname, from_warehouse_code as fcode, to_warehouse_name as tname,to_warehouse_code as tcode, grand_total, " . $this->db->dbprefix('transfers') . ".status")
                ->from('transfers')
                ->join('transfer_items', 'transfer_items.transfer_id=transfers.id', 'left')
                ->join('purchase_items', 'purchase_items.transfer_id=transfers.id', 'left')
                ->group_by('transfers.id')->order_by('transfers.date desc');
            if ($product) {
                $this->db->where($this->db->dbprefix('purchase_items') . ".product_id", $product);
                $this->db->or_where($this->db->dbprefix('transfer_items') . ".product_id", $product);
            }

            $q = $this->db->get();
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
                $this->excel->getActiveSheet()->setTitle(lang('transfers_report'));
                $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                $this->excel->getActiveSheet()->SetCellValue('B1', lang('transfer_no'));
                $this->excel->getActiveSheet()->SetCellValue('C1', lang('product_qty'));
                $this->excel->getActiveSheet()->SetCellValue('D1', lang('warehouse') . ' (' . lang('from') . ')');
                $this->excel->getActiveSheet()->SetCellValue('E1', lang('warehouse') . ' (' . lang('to') . ')');
                $this->excel->getActiveSheet()->SetCellValue('F1', lang('grand_total'));
                $this->excel->getActiveSheet()->SetCellValue('G1', lang('status'));

                $row = 2;
                foreach ($data as $data_row) {
                    $this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->sma->hrld($data_row->date));
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->transfer_no);
                    $this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->iname);
                    $this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->fname . ' (' . $data_row->fcode . ')');
                    $this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->tname . ' (' . $data_row->tcode . ')');
                    $this->excel->getActiveSheet()->SetCellValue('F' . $row, $data_row->grand_total);
                    $this->excel->getActiveSheet()->SetCellValue('G' . $row, $data_row->status);
                    $row++;
                }

                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(30);
                $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
                $filename = 'transfers_report';
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
                    $this->excel->getActiveSheet()->getStyle('C2:C' . $row)->getAlignment()->setWrapText(true);
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

        } else {

            $this->load->library('datatables');
            $this->datatables
                ->select("{$this->db->dbprefix('transfers')}.date, transfer_no, (CASE WHEN {$this->db->dbprefix('transfers')}.status = 'completed' THEN  GROUP_CONCAT(CONCAT({$this->db->dbprefix('purchase_items')}.product_name, '__', {$this->db->dbprefix('purchase_items')}.quantity) SEPARATOR '___') ELSE GROUP_CONCAT(CONCAT({$this->db->dbprefix('transfer_items')}.product_name, '__', {$this->db->dbprefix('transfer_items')}.quantity) SEPARATOR '___') END) as iname, from_warehouse_name as fname, from_warehouse_code as fcode, to_warehouse_name as tname,to_warehouse_code as tcode, grand_total, {$this->db->dbprefix('transfers')}.status, {$this->db->dbprefix('transfers')}.id as id", FALSE)
                ->from('transfers')
                ->join('transfer_items', 'transfer_items.transfer_id=transfers.id', 'left')
                ->join('purchase_items', 'purchase_items.transfer_id=transfers.id', 'left')
                ->group_by('transfers.id');
            if ($product) {
                $this->datatables->where(" (({$this->db->dbprefix('purchase_items')}.product_id = {$product}) OR ({$this->db->dbprefix('transfer_items')}.product_id = {$product})) ", NULL, FALSE);
            }
            $this->datatables->edit_column("fname", "$1 ($2)", "fname, fcode")
                ->edit_column("tname", "$1 ($2)", "tname, tcode")
                ->unset_column('fcode')
                ->unset_column('tcode');
            echo $this->datatables->generate();

        }

    }

    function purchases()
    {
        $this->sma->checkPermissions('purchases');
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['users'] = $this->reports_model->getStaff();
        $this->data['warehouses'] = $this->site->getAllWarehouses();
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('purchases_report')));
        $meta = array('page_title' => lang('purchases_report'), 'bc' => $bc);
        $this->page_construct('reports/purchases', $meta, $this->data);
    }

    function getPurchasesReport($pdf = NULL, $xls = NULL)
    {
        $this->sma->checkPermissions('purchases', TRUE);

        $product = $this->input->get('product') ? $this->input->get('product') : NULL;
        $user = $this->input->get('user') ? $this->input->get('user') : NULL;
        $supplier = $this->input->get('supplier') ? $this->input->get('supplier') : NULL;
        $warehouse = $this->input->get('warehouse') ? $this->input->get('warehouse') : NULL;
        $reference_no = $this->input->get('reference_no') ? $this->input->get('reference_no') : NULL;
        $start_date = $this->input->get('start_date') ? $this->input->get('start_date') : NULL;
        $end_date = $this->input->get('end_date') ? $this->input->get('end_date') : NULL;

        if ($start_date) {
            $start_date = $this->sma->fld($start_date);
            $end_date = $this->sma->fld($end_date);
        }
        if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $user = $this->session->userdata('user_id');
        }

        if ($pdf || $xls) {

            $this->db->select("" . $this->db->dbprefix('purchases') . ".date, reference_no, " . $this->db->dbprefix('warehouses') . ".name as wname,(SELECT CONCAT({$this->db->dbprefix('doitac')}.code, '-', {$this->db->dbprefix('doitac')}.name) FROM {$this->db->dbprefix('doitac')} WHERE id=doitac) as doitac, supplier,(select concat(first_name,' ',last_name) FROM scodeweb_users WHERE id={$this->db->dbprefix('purchases')}.created_by) as nhanvien, GROUP_CONCAT(CONCAT(" . $this->db->dbprefix('purchase_items') . ".product_name, ' (', " . $this->db->dbprefix('purchase_items') . ".unit_quantity, ')') SEPARATOR '\n') as iname, grand_total, paid, " . $this->db->dbprefix('purchases') . ".status," . $this->db->dbprefix('purchase_items') . ".product_unit_code", FALSE)
                ->from('purchases')
                ->join('purchase_items', 'purchase_items.purchase_id=purchases.id', 'left')
                ->join('warehouses', 'warehouses.id=purchases.warehouse_id', 'left')
                ->group_by('purchases.id')
                ->order_by('purchases.date desc');
				
			$this->db->where('purchases.status !=', 'returned');
			
            if ($user) {
                $this->db->where('purchases.created_by', $user);
            }
            if ($product) {
                $this->db->where('purchase_items.product_id', $product);
            }
            if ($supplier) {
                $this->db->where('purchases.supplier_id', $supplier);
            }
            if ($warehouse) {
                $this->db->where('purchases.warehouse_id', $warehouse);
            }
            if ($reference_no) {
                $this->db->like('purchases.reference_no', $reference_no, 'both');
            }
            if ($start_date) {
                $this->db->where($this->db->dbprefix('purchases').'.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
            }
			if(!$supplier){
				$this->db->where('purchases.status !=','returned');
			}
			
            $q = $this->db->get();
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
                $this->excel->getActiveSheet()->setTitle(lang('purchase_report'));
                $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                $this->excel->getActiveSheet()->SetCellValue('B1', lang('reference_no'));
                $this->excel->getActiveSheet()->SetCellValue('C1', lang('warehouse'));
				$this->excel->getActiveSheet()->SetCellValue('D1', lang('ĐVGH'));
                $this->excel->getActiveSheet()->SetCellValue('E1', lang('supplier'));
				$this->excel->getActiveSheet()->SetCellValue('F1', lang('Nhân viên'));
                $this->excel->getActiveSheet()->SetCellValue('G1', lang('product_qty'));
                $this->excel->getActiveSheet()->SetCellValue('H1', lang('grand_total'));
                $this->excel->getActiveSheet()->SetCellValue('I1', lang('paid'));
                $this->excel->getActiveSheet()->SetCellValue('J1', lang('balance'));
                $this->excel->getActiveSheet()->SetCellValue('K1', lang('status'));
				$this->excel->getActiveSheet()->SetCellValue('L1', lang('unit'));

                $row = 2;
                $total = 0;
                $paid = 0;
                $balance = 0;
                foreach ($data as $data_row) {
                    $this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->sma->hrld($data_row->date));
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->reference_no);
                    $this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->wname);
                    $this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->doitac);
					$this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->supplier);
					$this->excel->getActiveSheet()->SetCellValue('F' . $row, $data_row->nhanvien);
                    $this->excel->getActiveSheet()->SetCellValue('G' . $row, $data_row->iname);
                    $this->excel->getActiveSheet()->SetCellValue('H' . $row, $data_row->grand_total);
                    $this->excel->getActiveSheet()->SetCellValue('I' . $row, $data_row->paid);
                    $this->excel->getActiveSheet()->SetCellValue('J' . $row, ($data_row->grand_total - $data_row->paid));
                    $this->excel->getActiveSheet()->SetCellValue('K' . $row, lang($data_row->status));
					$this->excel->getActiveSheet()->SetCellValue('L' . $row, $data_row->product_unit_code);
                    $total += $data_row->grand_total;
                    $paid += $data_row->paid;
                    $balance += ($data_row->grand_total - $data_row->paid);
                    $row++;
                }
                $this->excel->getActiveSheet()->getStyle("H" . $row . ":J" . $row)->getBorders()
                    ->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);
                $this->excel->getActiveSheet()->SetCellValue('H' . $row, $total);
                $this->excel->getActiveSheet()->SetCellValue('I' . $row, $paid);
                $this->excel->getActiveSheet()->SetCellValue('J' . $row, $balance);

                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(30);
                $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('I')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('J')->setWidth(20);
                $filename = 'purchase_report';
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
                    $this->excel->getActiveSheet()->getStyle('E2:E' . $row)->getAlignment()->setWrapText(true);
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

        } else {

            $pi = "( SELECT purchase_id, product_id, (GROUP_CONCAT(CONCAT(CONCAT({$this->db->dbprefix('purchase_items')}.product_name,'-',{$this->db->dbprefix('purchase_items')}.product_unit_code), '__', {$this->db->dbprefix('purchase_items')}.unit_quantity) SEPARATOR '___')) as item_nane from {$this->db->dbprefix('purchase_items')} ";
            if ($product) {
                $pi .= " WHERE {$this->db->dbprefix('purchase_items')}.product_id = {$product} ";
            }
            $pi .= " GROUP BY {$this->db->dbprefix('purchase_items')}.purchase_id ) FPI";

            $this->load->library('datatables');
            $this->datatables
                ->select("DATE_FORMAT({$this->db->dbprefix('purchases')}.date, '%Y-%m-%d %T') as date, reference_no, {$this->db->dbprefix('warehouses')}.name as wname,(SELECT CONCAT({$this->db->dbprefix('doitac')}.code, '-', {$this->db->dbprefix('doitac')}.name) FROM {$this->db->dbprefix('doitac')} WHERE id=doitac) as doitac, supplier,(select concat(first_name,' ',last_name) FROM scodeweb_users WHERE id={$this->db->dbprefix('purchases')}.created_by) as nhanvien, (FPI.item_nane) as iname, grand_total, paid, (grand_total-paid) as balance, {$this->db->dbprefix('purchases')}.status, {$this->db->dbprefix('purchases')}.id as id", FALSE)
                ->from('purchases')
                ->join($pi, 'FPI.purchase_id=purchases.id', 'left')
                ->join('warehouses', 'warehouses.id=purchases.warehouse_id', 'left');
                // ->group_by('purchases.id');
			
			$this->datatables->where('purchases.status !=', 'returned');
			
            if ($user) {
                $this->datatables->where('purchases.created_by', $user);
            }
            if ($product) {
                $this->datatables->where('FPI.product_id', $product, FALSE);
            }
            if ($supplier) {
                $this->datatables->where('purchases.supplier_id', $supplier);
            }
            if ($warehouse) {
                $this->datatables->where('purchases.warehouse_id', $warehouse);
            }
            if ($reference_no) {
                $this->datatables->like('purchases.reference_no', $reference_no, 'both');
            }
            if ($start_date) {
                $this->datatables->where($this->db->dbprefix('purchases').'.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
            }
			if(!$supplier){
				$this->datatables->where('purchases.status !=','returned');
			}
            echo $this->datatables->generate();

        }
    }
	function purchases_chitiet()
    {
        $this->sma->checkPermissions('purchases');
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['users'] = $this->reports_model->getStaff();
        $this->data['warehouses'] = $this->site->getAllWarehouses();
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('purchases_report')));
        $meta = array('page_title' => lang('purchases_report'), 'bc' => $bc);
        $this->page_construct('reports/purchases_chitiet', $meta, $this->data);
    }

    function getPurchasesReportChiTiet($pdf = NULL, $xls = NULL)
    {
        $this->sma->checkPermissions('purchases', TRUE);

        $product = $this->input->get('product') ? $this->input->get('product') : NULL;
        $user = $this->input->get('user') ? $this->input->get('user') : NULL;
        $supplier = $this->input->get('supplier') ? $this->input->get('supplier') : NULL;
        $warehouse = $this->input->get('warehouse') ? $this->input->get('warehouse') : NULL;
        $reference_no = $this->input->get('reference_no') ? $this->input->get('reference_no') : NULL;
        $start_date = $this->input->get('start_date') ? $this->input->get('start_date') : NULL;
        $end_date = $this->input->get('end_date') ? $this->input->get('end_date') : NULL;

        if ($start_date) {
            $start_date = $this->sma->fld($start_date);
            $end_date = $this->sma->fld($end_date);
        }
        if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $user = $this->session->userdata('user_id');
        }

        if ($pdf || $xls) {

            $this->db->select("" . $this->db->dbprefix('purchases') . ".date, reference_no, " . $this->db->dbprefix('warehouses') . ".name as wname,(SELECT CONCAT({$this->db->dbprefix('doitac')}.code, '-', {$this->db->dbprefix('doitac')}.name) FROM {$this->db->dbprefix('doitac')} WHERE id=doitac) as doitac, supplier,(select concat(first_name,' ',last_name) FROM scodeweb_users WHERE id={$this->db->dbprefix('purchases')}.created_by) as nhanvien,{$this->db->dbprefix('purchase_items')}.product_name as item_nane,{$this->db->dbprefix('purchase_items')}.product_unit_code as donvitinh,{$this->db->dbprefix('purchase_items')}.unit_quantity as soluong,{$this->db->dbprefix('purchase_items')}.unit_cost as giaban, ({$this->db->dbprefix('purchase_items')}.unit_cost*{$this->db->dbprefix('purchase_items')}.unit_quantity) as thanhtien,total_tax, shipping,total_discount,grand_total,paid,(grand_total-paid) as duno," . $this->db->dbprefix('purchases') . ".status," . $this->db->dbprefix('purchase_items') . ".product_unit_code", FALSE)
                ->from('purchases')
                ->join('purchase_items', 'purchase_items.purchase_id=purchases.id', 'left')
                ->join('warehouses', 'warehouses.id=purchases.warehouse_id', 'left')
                ->order_by('purchases.date desc');

            if ($user) {
                $this->db->where('purchases.created_by', $user);
            }
            if ($product) {
                $this->db->where('purchase_items.product_id', $product);
            }
            if ($supplier) {
                $this->db->where('purchases.supplier_id', $supplier);
            }
            if ($warehouse) {
                $this->db->where('purchases.warehouse_id', $warehouse);
            }
            if ($reference_no) {
                $this->db->like('purchases.reference_no', $reference_no, 'both');
            }
            if ($start_date) {
                $this->db->where($this->db->dbprefix('purchases').'.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
            }
			
			$this->db->where('purchases.status !=','returned');
			
            $q = $this->db->get();
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
                $this->excel->getActiveSheet()->setTitle(lang('purchase_report'));
                $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                $this->excel->getActiveSheet()->SetCellValue('B1', lang('reference_no'));
                $this->excel->getActiveSheet()->SetCellValue('C1', lang('warehouse'));
                $this->excel->getActiveSheet()->SetCellValue('D1', lang('ĐVGH'));
				$this->excel->getActiveSheet()->SetCellValue('E1', lang('supplier'));
				$this->excel->getActiveSheet()->SetCellValue('F1', lang('Nhân viên'));
                $this->excel->getActiveSheet()->SetCellValue('G1', lang('product_qty'));
                $this->excel->getActiveSheet()->SetCellValue('H1', lang('ĐVT'));
                $this->excel->getActiveSheet()->SetCellValue('I1', lang('Số lượng'));
                $this->excel->getActiveSheet()->SetCellValue('J1', lang('Giá bán'));
				$this->excel->getActiveSheet()->SetCellValue('K1', lang('Thuế'));
				$this->excel->getActiveSheet()->SetCellValue('L1', lang('Phí VC'));
				$this->excel->getActiveSheet()->SetCellValue('M1', lang('Giảm'));
				$this->excel->getActiveSheet()->SetCellValue('N1', lang('Tổng'));
				$this->excel->getActiveSheet()->SetCellValue('O1', lang('Đã TT'));
				$this->excel->getActiveSheet()->SetCellValue('P1', lang('Dư Nợ'));
                $this->excel->getActiveSheet()->SetCellValue('Q1', lang('status'));

                $row = 2;
                $total = 0;
                $sl = 0;
                $shipping = 0;
				$thue=0; $giam=0; $datt=0;$duno=0;
                foreach ($data as $data_row) {
                    $this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->sma->hrld($data_row->date));
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->reference_no);
                    $this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->wname);
                    $this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->doitac);
					$this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->supplier);
					$this->excel->getActiveSheet()->SetCellValue('F' . $row, $data_row->nhanvien);
                    $this->excel->getActiveSheet()->SetCellValue('G' . $row, $data_row->item_nane);
                    $this->excel->getActiveSheet()->SetCellValue('H' . $row, $data_row->donvitinh);
                    $this->excel->getActiveSheet()->SetCellValue('I' . $row, $data_row->soluong);
                    $this->excel->getActiveSheet()->SetCellValue('J' . $row, ($data_row->thanhtien));
                    $this->excel->getActiveSheet()->SetCellValue('K' . $row, $data_row->total_tax);
					$this->excel->getActiveSheet()->SetCellValue('L' . $row, $data_row->shipping);
					$this->excel->getActiveSheet()->SetCellValue('M' . $row, $data_row->total_discount);
					$this->excel->getActiveSheet()->SetCellValue('N' . $row, $data_row->grand_total);
					$this->excel->getActiveSheet()->SetCellValue('O' . $row, $data_row->paid);
					$this->excel->getActiveSheet()->SetCellValue('P' . $row, ($data_row->grand_total-$data_row->paid));
					$this->excel->getActiveSheet()->SetCellValue('Q' . $row, lang($data_row->status));
					
                    $total += $data_row->grand_total;
                    $sl += $data_row->soluong;
                    $shipping += $data_row->shipping;
					$thue += $data_row->total_tax;
					$giam += $data_row->total_discount;
					$datt += $data_row->paid;
					$duno += ($data_row->grand_total-$data_row->paid);
                    $row++;
                }
                $this->excel->getActiveSheet()->getStyle("I" . $row . ":P" . $row)->getBorders()
                    ->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);
                $this->excel->getActiveSheet()->SetCellValue('I' . $row, $sl);
                $this->excel->getActiveSheet()->SetCellValue('K' . $row, $thue);
                $this->excel->getActiveSheet()->SetCellValue('L' . $row, $shipping);
				$this->excel->getActiveSheet()->SetCellValue('M' . $row, $giam);
				$this->excel->getActiveSheet()->SetCellValue('N' . $row, $total);
				$this->excel->getActiveSheet()->SetCellValue('O' . $row, $datt);
				$this->excel->getActiveSheet()->SetCellValue('P' . $row, $duno);

                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(30);
                $this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(10);
                $this->excel->getActiveSheet()->getColumnDimension('I')->setWidth(10);
				$this->excel->getActiveSheet()->getColumnDimension('J')->setWidth(10);
                $filename = 'BaoCao_NhapHang_ChiTiet_';
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
                    $this->excel->getActiveSheet()->getStyle('E2:E' . $row)->getAlignment()->setWrapText(true);
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

        } else {

            $pi = "( SELECT purchase_id, product_id, {$this->db->dbprefix('purchase_items')}.product_name as item_nane,{$this->db->dbprefix('purchase_items')}.product_unit_code as donvitinh, {$this->db->dbprefix('purchase_items')}.unit_quantity as soluong,{$this->db->dbprefix('purchase_items')}.unit_cost as giaban from {$this->db->dbprefix('purchase_items')} ";
            if ($product) {
                $pi .= " WHERE {$this->db->dbprefix('purchase_items')}.product_id = {$product} ";
            }
           // $pi .= " GROUP BY {$this->db->dbprefix('purchase_items')}.purchase_id ) FPI";
		    $pi .= ") FPI";

            $this->load->library('datatables');
            $this->datatables
                ->select("DATE_FORMAT({$this->db->dbprefix('purchases')}.date, '%Y-%m-%d %T') as date, reference_no, {$this->db->dbprefix('warehouses')}.name as wname,(SELECT CONCAT({$this->db->dbprefix('doitac')}.code, '-', {$this->db->dbprefix('doitac')}.name) FROM {$this->db->dbprefix('doitac')} WHERE id=doitac) as doitac, supplier,(select concat(first_name,' ',last_name) FROM scodeweb_users WHERE id={$this->db->dbprefix('purchases')}.created_by) as nhanvien, (FPI.item_nane) as iname, FPI.donvitinh,FPI.soluong,FPI.giaban,(FPI.soluong*FPI.giaban) as thanhtien,total_tax,shipping,total_discount,grand_total,paid,(grand_total-paid) as duno, {$this->db->dbprefix('purchases')}.status, {$this->db->dbprefix('purchases')}.id as id", FALSE)
                ->from('purchases')
                ->join($pi, 'FPI.purchase_id=purchases.id', 'left')
                ->join('warehouses', 'warehouses.id=purchases.warehouse_id', 'left');
                // ->group_by('purchases.id');
			
			$this->datatables->where('purchases.status !=','returned');
			
            if ($user) {
                $this->datatables->where('purchases.created_by', $user);
            }
            if ($product) {
                $this->datatables->where('FPI.product_id', $product, FALSE);
            }
            if ($supplier) {
                $this->datatables->where('purchases.supplier_id', $supplier);
            }
            if ($warehouse) {
                $this->datatables->where('purchases.warehouse_id', $warehouse);
            }
            if ($reference_no) {
                $this->datatables->like('purchases.reference_no', $reference_no, 'both');
            }
            if ($start_date) {
                $this->datatables->where($this->db->dbprefix('purchases').'.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
            }

            echo $this->datatables->generate();

        }

    }
	/*Bo sung bao cao tra hang ncc*/
	 function purchases_return()
    {
        $this->sma->checkPermissions('purchases');
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['users'] = $this->reports_model->getStaff();
        $this->data['warehouses'] = $this->site->getAllWarehouses();
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('Báo cáo trả hàng nhà cung cấp')));
        $meta = array('page_title' => lang('Báo cáo trả hàng nhà cung cấp'), 'bc' => $bc);
        $this->page_construct('reports/purchases_return', $meta, $this->data);
    }

    function getPurchasesReturnReport($pdf = NULL, $xls = NULL)
    {
        $this->sma->checkPermissions('purchases', TRUE);

        $product = $this->input->get('product') ? $this->input->get('product') : NULL;
        $user = $this->input->get('user') ? $this->input->get('user') : NULL;
        $supplier = $this->input->get('supplier') ? $this->input->get('supplier') : NULL;
        $warehouse = $this->input->get('warehouse') ? $this->input->get('warehouse') : NULL;
        $reference_no = $this->input->get('reference_no') ? $this->input->get('reference_no') : NULL;
        $start_date = $this->input->get('start_date') ? $this->input->get('start_date') : NULL;
        $end_date = $this->input->get('end_date') ? $this->input->get('end_date') : NULL;

        if ($start_date) {
            $start_date = $this->sma->fld($start_date);
            $end_date = $this->sma->fld($end_date);
        }
        if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $user = $this->session->userdata('user_id');
        }

        if ($pdf || $xls) {

            $this->db->select("" . $this->db->dbprefix('purchases') . ".date, reference_no, " . $this->db->dbprefix('warehouses') . ".name as wname,(SELECT CONCAT({$this->db->dbprefix('doitac')}.code, '-', {$this->db->dbprefix('doitac')}.name) FROM {$this->db->dbprefix('doitac')} WHERE id=doitac) as doitac, supplier,(select concat(first_name,' ',last_name) FROM scodeweb_users WHERE id={$this->db->dbprefix('purchases')}.created_by) as nhanvien, GROUP_CONCAT(CONCAT(" . $this->db->dbprefix('purchase_items') . ".product_name, ' (', " . $this->db->dbprefix('purchase_items') . ".unit_quantity, ')') SEPARATOR '\n') as iname, grand_total, paid, " . $this->db->dbprefix('purchases') . ".status," . $this->db->dbprefix('purchase_items') . ".product_unit_code", FALSE)
                ->from('purchases')
                ->join('purchase_items', 'purchase_items.purchase_id=purchases.id', 'left')
                ->join('warehouses', 'warehouses.id=purchases.warehouse_id', 'left')
                ->group_by('purchases.id')
                ->order_by('purchases.date desc');

            if ($user) {
                $this->db->where('purchases.created_by', $user);
            }
            if ($product) {
                $this->db->where('purchase_items.product_id', $product);
            }
            if ($supplier) {
                $this->db->where('purchases.supplier_id', $supplier);
            }
            if ($warehouse) {
                $this->db->where('purchases.warehouse_id', $warehouse);
            }
            if ($reference_no) {
                $this->db->like('purchases.reference_no', $reference_no, 'both');
            }
            if ($start_date) {
                $this->db->where($this->db->dbprefix('purchases').'.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
            }
			$this->db->where('purchases.status','returned');

            $q = $this->db->get();
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
                $this->excel->getActiveSheet()->setTitle(lang('purchase_report'));
                $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                $this->excel->getActiveSheet()->SetCellValue('B1', lang('reference_no'));
                $this->excel->getActiveSheet()->SetCellValue('C1', lang('warehouse'));
                $this->excel->getActiveSheet()->SetCellValue('D1', lang('ĐVGH'));
				$this->excel->getActiveSheet()->SetCellValue('E1', lang('supplier'));
				$this->excel->getActiveSheet()->SetCellValue('F1', lang('Nhân viên'));
                $this->excel->getActiveSheet()->SetCellValue('G1', lang('product_qty'));
                $this->excel->getActiveSheet()->SetCellValue('H1', lang('grand_total'));
                $this->excel->getActiveSheet()->SetCellValue('I1', lang('paid'));
                $this->excel->getActiveSheet()->SetCellValue('J1', lang('balance'));
                $this->excel->getActiveSheet()->SetCellValue('K1', lang('status'));
				$this->excel->getActiveSheet()->SetCellValue('L1', lang('unit'));

                $row = 2;
                $total = 0;
                $paid = 0;
                $balance = 0;
                foreach ($data as $data_row) {
                    $this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->sma->hrld($data_row->date));
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->reference_no);
                    $this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->wname);
                    $this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->doitac);
					$this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->supplier);
					$this->excel->getActiveSheet()->SetCellValue('F' . $row, $data_row->nhanvien);
                    $this->excel->getActiveSheet()->SetCellValue('G' . $row, $data_row->iname);
                    $this->excel->getActiveSheet()->SetCellValue('H' . $row, $data_row->grand_total);
                    $this->excel->getActiveSheet()->SetCellValue('I' . $row, $data_row->paid);
                    $this->excel->getActiveSheet()->SetCellValue('J' . $row, ($data_row->grand_total - $data_row->paid));
                    $this->excel->getActiveSheet()->SetCellValue('K' . $row, lang($data_row->status));
					$this->excel->getActiveSheet()->SetCellValue('L' . $row, $data_row->product_unit_code);
                    $total += $data_row->grand_total;
                    $paid += $data_row->paid;
                    $balance += ($data_row->grand_total - $data_row->paid);
                    $row++;
                }
                $this->excel->getActiveSheet()->getStyle("H" . $row . ":J" . $row)->getBorders()
                    ->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);
                $this->excel->getActiveSheet()->SetCellValue('H' . $row, $total);
                $this->excel->getActiveSheet()->SetCellValue('I' . $row, $paid);
                $this->excel->getActiveSheet()->SetCellValue('J' . $row, $balance);

                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(30);
                $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('I')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('J')->setWidth(20);
                $filename = 'BaoCao_TraHang_NCC_';
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
                    $this->excel->getActiveSheet()->getStyle('E2:E' . $row)->getAlignment()->setWrapText(true);
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

        } else {

            $pi = "( SELECT purchase_id, product_id, (GROUP_CONCAT(CONCAT(CONCAT({$this->db->dbprefix('purchase_items')}.product_name,'-',{$this->db->dbprefix('purchase_items')}.product_unit_code), '__', {$this->db->dbprefix('purchase_items')}.unit_quantity) SEPARATOR '___')) as item_nane from {$this->db->dbprefix('purchase_items')} ";
            if ($product) {
                $pi .= " WHERE {$this->db->dbprefix('purchase_items')}.product_id = {$product} ";
            }
            $pi .= " GROUP BY {$this->db->dbprefix('purchase_items')}.purchase_id ) FPI";

            $this->load->library('datatables');
            $this->datatables
                ->select("DATE_FORMAT({$this->db->dbprefix('purchases')}.date, '%Y-%m-%d %T') as date, reference_no, {$this->db->dbprefix('warehouses')}.name as wname,(SELECT CONCAT({$this->db->dbprefix('doitac')}.code, '-', {$this->db->dbprefix('doitac')}.name) FROM {$this->db->dbprefix('doitac')} WHERE id=doitac) as doitac, supplier,(select concat(first_name,' ',last_name) FROM scodeweb_users WHERE id={$this->db->dbprefix('purchases')}.created_by) as nhanvien, (FPI.item_nane) as iname, grand_total, paid, (grand_total-paid) as balance, {$this->db->dbprefix('purchases')}.status, {$this->db->dbprefix('purchases')}.id as id", FALSE)
                ->from('purchases')
                ->join($pi, 'FPI.purchase_id=purchases.id', 'left')
                ->join('warehouses', 'warehouses.id=purchases.warehouse_id', 'left');
                // ->group_by('purchases.id');

            if ($user) {
                $this->datatables->where('purchases.created_by', $user);
            }
            if ($product) {
                $this->datatables->where('FPI.product_id', $product, FALSE);
            }
            if ($supplier) {
                $this->datatables->where('purchases.supplier_id', $supplier);
            }
            if ($warehouse) {
                $this->datatables->where('purchases.warehouse_id', $warehouse);
            }
            if ($reference_no) {
                $this->datatables->like('purchases.reference_no', $reference_no, 'both');
            }
            if ($start_date) {
                $this->datatables->where($this->db->dbprefix('purchases').'.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
            }
			$this->datatables->where('purchases.status','returned');
			
            echo $this->datatables->generate();

        }

    }
	function purchases_return_chitiet()
    {
        $this->sma->checkPermissions('purchases');
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['users'] = $this->reports_model->getStaff();
        $this->data['warehouses'] = $this->site->getAllWarehouses();
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('Báo cáo trả hàng nhà cung cấp chi tiết')));
        $meta = array('page_title' => lang('Báo cáo trả hàng nhà cung cấp chi tiết'), 'bc' => $bc);
        $this->page_construct('reports/purchases_return_chitiet', $meta, $this->data);
    }

    function getPurchasesReturnReportChiTiet($pdf = NULL, $xls = NULL)
    {
        $this->sma->checkPermissions('purchases', TRUE);

        $product = $this->input->get('product') ? $this->input->get('product') : NULL;
        $user = $this->input->get('user') ? $this->input->get('user') : NULL;
        $supplier = $this->input->get('supplier') ? $this->input->get('supplier') : NULL;
        $warehouse = $this->input->get('warehouse') ? $this->input->get('warehouse') : NULL;
        $reference_no = $this->input->get('reference_no') ? $this->input->get('reference_no') : NULL;
        $start_date = $this->input->get('start_date') ? $this->input->get('start_date') : NULL;
        $end_date = $this->input->get('end_date') ? $this->input->get('end_date') : NULL;

        if ($start_date) {
            $start_date = $this->sma->fld($start_date);
            $end_date = $this->sma->fld($end_date);
        }
        if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $user = $this->session->userdata('user_id');
        }

        if ($pdf || $xls) {

            $this->db->select("" . $this->db->dbprefix('purchases') . ".date, reference_no, " . $this->db->dbprefix('warehouses') . ".name as wname,(SELECT CONCAT({$this->db->dbprefix('doitac')}.code, '-', {$this->db->dbprefix('doitac')}.name) FROM {$this->db->dbprefix('doitac')} WHERE id=doitac) as doitac, supplier,(select concat(first_name,' ',last_name) FROM scodeweb_users WHERE id={$this->db->dbprefix('purchases')}.created_by) as nhanvien,{$this->db->dbprefix('purchase_items')}.product_name as item_nane,{$this->db->dbprefix('purchase_items')}.product_unit_code as donvitinh,{$this->db->dbprefix('purchase_items')}.unit_quantity as soluong,{$this->db->dbprefix('purchase_items')}.unit_cost as giaban, ({$this->db->dbprefix('purchase_items')}.unit_cost*{$this->db->dbprefix('purchase_items')}.unit_quantity) as thanhtien, {$this->db->dbprefix('purchases')}.order_discount,grand_total,paid,(grand_total-paid) as duno," . $this->db->dbprefix('purchases') . ".status," . $this->db->dbprefix('purchases') . ".note", FALSE)
                ->from('purchases')
                ->join('purchase_items', 'purchase_items.purchase_id=purchases.id', 'left')
                ->join('warehouses', 'warehouses.id=purchases.warehouse_id', 'left')
                
                ->order_by('purchases.date desc');

            if ($user) {
                $this->db->where('purchases.created_by', $user);
            }
            if ($product) {
                $this->db->where('purchase_items.product_id', $product);
            }
            if ($supplier) {
                $this->db->where('purchases.supplier_id', $supplier);
            }
            if ($warehouse) {
                $this->db->where('purchases.warehouse_id', $warehouse);
            }
            if ($reference_no) {
                $this->db->like('purchases.reference_no', $reference_no, 'both');
            }
            if ($start_date) {
                $this->db->where($this->db->dbprefix('purchases').'.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
            }
			
			$this->db->where('purchases.status','returned');
			
            $q = $this->db->get();
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
                $this->excel->getActiveSheet()->setTitle(lang('purchase_report'));
                $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                $this->excel->getActiveSheet()->SetCellValue('B1', lang('reference_no'));
                $this->excel->getActiveSheet()->SetCellValue('C1', lang('warehouse'));
				$this->excel->getActiveSheet()->SetCellValue('D1', lang('ĐVGH'));
                $this->excel->getActiveSheet()->SetCellValue('E1', lang('supplier'));
				$this->excel->getActiveSheet()->SetCellValue('F1', lang('Nhân viên'));
                $this->excel->getActiveSheet()->SetCellValue('G1', lang('product_qty'));
                $this->excel->getActiveSheet()->SetCellValue('H1', lang('ĐVT'));
                $this->excel->getActiveSheet()->SetCellValue('I1', lang('Số lượng'));
                $this->excel->getActiveSheet()->SetCellValue('J1', lang('Giá bán'));
				$this->excel->getActiveSheet()->SetCellValue('K1', lang('Phụ Thu'));
				$this->excel->getActiveSheet()->SetCellValue('L1', lang('Tổng'));
				$this->excel->getActiveSheet()->SetCellValue('M1', lang('Đã TT'));
				$this->excel->getActiveSheet()->SetCellValue('N1', lang('Dư Nợ'));
                $this->excel->getActiveSheet()->SetCellValue('O1', lang('status'));
				$this->excel->getActiveSheet()->SetCellValue('P1', lang('Ghi chú'));

                $row = 2;
                $total = 0;
                $sl = 0;
                $shipping = 0; $datt=0;$duno=0;
                foreach ($data as $data_row) {
                    $this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->sma->hrld($data_row->date));
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->reference_no);
                    $this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->wname);
                    $this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->doitac);
					$this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->supplier);
					$this->excel->getActiveSheet()->SetCellValue('F' . $row, $data_row->nhanvien);
                    $this->excel->getActiveSheet()->SetCellValue('G' . $row, $data_row->item_nane);
                    $this->excel->getActiveSheet()->SetCellValue('H' . $row, $data_row->donvitinh);
                    $this->excel->getActiveSheet()->SetCellValue('I' . $row, $data_row->soluong);
                    $this->excel->getActiveSheet()->SetCellValue('J' . $row, ($data_row->thanhtien));
                    $this->excel->getActiveSheet()->SetCellValue('K' . $row, $data_row->shipping);
					$this->excel->getActiveSheet()->SetCellValue('L' . $row, $data_row->grand_total);
					$this->excel->getActiveSheet()->SetCellValue('M' . $row, $data_row->paid);
					$this->excel->getActiveSheet()->SetCellValue('N' . $row, ($data_row->grand_total-$data_row->paid));
					$this->excel->getActiveSheet()->SetCellValue('O' . $row, lang($data_row->status));
					$this->excel->getActiveSheet()->SetCellValue('P' . $row, $data_row->note);
					
                    $total += $data_row->grand_total;
                    $sl += $data_row->soluong;
                    $shipping += $data_row->shipping;
					$datt += $data_row->paid;
					$duno += ($data_row->grand_total-$data_row->paid);
                    $row++;
                }
                $this->excel->getActiveSheet()->getStyle("I" . $row . ":N" . $row)->getBorders()
                    ->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);
                $this->excel->getActiveSheet()->SetCellValue('I' . $row, $sl);
                $this->excel->getActiveSheet()->SetCellValue('K' . $row, $shipping);
                $this->excel->getActiveSheet()->SetCellValue('L' . $row, $total);
				$this->excel->getActiveSheet()->SetCellValue('M' . $row, $datt);
				$this->excel->getActiveSheet()->SetCellValue('N' . $row, $duno);

                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('J')->setWidth(15);
                $filename = 'BaoCao_TraHang_NCC_ChiTiet_';
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
                    $this->excel->getActiveSheet()->getStyle('E2:E' . $row)->getAlignment()->setWrapText(true);
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

        } else {

            $pi = "( SELECT purchase_id, product_id, {$this->db->dbprefix('purchase_items')}.product_name as item_nane,{$this->db->dbprefix('purchase_items')}.product_unit_code as donvitinh, {$this->db->dbprefix('purchase_items')}.unit_quantity as soluong,{$this->db->dbprefix('purchase_items')}.unit_cost as giaban from {$this->db->dbprefix('purchase_items')} ";
            if ($product) {
                $pi .= " WHERE {$this->db->dbprefix('purchase_items')}.product_id = {$product} ";
            }
           // $pi .= " GROUP BY {$this->db->dbprefix('purchase_items')}.purchase_id ) FPI";
            $pi .= " ) FPI";
            $this->load->library('datatables');
            $this->datatables
                ->select("CONCAT(DATE_FORMAT({$this->db->dbprefix('purchases')}.date, '%Y-%m-%d %T'),'<br/>',reference_no) as date, {$this->db->dbprefix('warehouses')}.name as wname,(SELECT CONCAT({$this->db->dbprefix('doitac')}.code, '-', {$this->db->dbprefix('doitac')}.name) FROM {$this->db->dbprefix('doitac')} WHERE id=doitac) as doitac, supplier,(select concat(first_name,' ',last_name) FROM scodeweb_users WHERE id={$this->db->dbprefix('purchases')}.created_by) as nhanvien, (FPI.item_nane) as iname, FPI.donvitinh,FPI.soluong,FPI.giaban,(FPI.soluong*FPI.giaban) as thanhtien,{$this->db->dbprefix('purchases')}.order_discount,grand_total,paid,(grand_total-paid) as duno, {$this->db->dbprefix('purchases')}.status,{$this->db->dbprefix('purchases')}.note, {$this->db->dbprefix('purchases')}.id as id", FALSE)
                ->from('purchases')
                ->join($pi, 'FPI.purchase_id=purchases.id', 'left')
                ->join('warehouses', 'warehouses.id=purchases.warehouse_id', 'left');
                // ->group_by('purchases.id');

            if ($user) {
                $this->datatables->where('purchases.created_by', $user);
            }
            if ($product) {
                $this->datatables->where('FPI.product_id', $product, FALSE);
            }
            if ($supplier) {
                $this->datatables->where('purchases.supplier_id', $supplier);
            }
            if ($warehouse) {
                $this->datatables->where('purchases.warehouse_id', $warehouse);
            }
            if ($reference_no) {
                $this->datatables->like('purchases.reference_no', $reference_no, 'both');
            }
            if ($start_date) {
                $this->datatables->where($this->db->dbprefix('purchases').'.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
            }
			
			$this->datatables->where('purchases.status','returned');
			
            echo $this->datatables->generate();

        }

    }
	/*end bao cao tra hang ncc*/
    function payments()
    {
        $this->sma->checkPermissions('payments');
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['users'] = $this->reports_model->getStaff();
        $this->data['billers'] = $this->site->getAllCompanies('biller');
        $this->data['pos_settings'] = POS ? $this->reports_model->getPOSSetting('biller') : FALSE;
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('payments_report')));
        $meta = array('page_title' => lang('payments_report'), 'bc' => $bc);
        $this->page_construct('reports/payments', $meta, $this->data);
    }

    function getPaymentsReport($pdf = NULL, $xls = NULL)
    {
        $this->sma->checkPermissions('payments', TRUE);

        $user = $this->input->get('user') ? $this->input->get('user') : NULL;
        $supplier = $this->input->get('supplier') ? $this->input->get('supplier') : NULL;
        $customer = $this->input->get('customer') ? $this->input->get('customer') : NULL;
        $biller = $this->input->get('biller') ? $this->input->get('biller') : NULL;
        $payment_ref = $this->input->get('payment_ref') ? $this->input->get('payment_ref') : NULL;
        $paid_by = $this->input->get('paid_by') ? $this->input->get('paid_by') : NULL;
        $sale_ref = $this->input->get('sale_ref') ? $this->input->get('sale_ref') : NULL;
        $purchase_ref = $this->input->get('purchase_ref') ? $this->input->get('purchase_ref') : NULL;
        $card = $this->input->get('card') ? $this->input->get('card') : NULL;
        $cheque = $this->input->get('cheque') ? $this->input->get('cheque') : NULL;
        $transaction_id = $this->input->get('tid') ? $this->input->get('tid') : NULL;
        $start_date = $this->input->get('start_date') ? $this->input->get('start_date') : NULL;
        $end_date = $this->input->get('end_date') ? $this->input->get('end_date') : NULL;
		$locdk="";
        if ($start_date) {
            $start_date = $this->sma->fsd($start_date);
            $end_date = $this->sma->fsd($end_date);
        }
        if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $user = $this->session->userdata('user_id');
        }
        if ($pdf || $xls) {

            $this->db->select("".$this->db->dbprefix('payments') . ".id,".$this->db->dbprefix('payments') . ".date, " . $this->db->dbprefix('payments') . ".reference_no as payment_ref,(CASE WHEN id_ncc_id_kh>0 THEN (SELECT CONCAT(name,'-',phone) FROM ".$this->db->dbprefix('companies') . " WHERE id=id_ncc_id_kh) ELSE null END) as sale_ref,(select concat(first_name,' ',last_name) FROM scodeweb_users WHERE id={$this->db->dbprefix('payments')}.created_by) as nhanvien,(select name from scodeweb_warehouses where id=scodeweb_payments.warehouse_id) as kho, " . $this->db->dbprefix('purchases') . ".reference_no as purchase_ref, paid_by, amount, (CASE WHEN type='sent' THEN 'Chi nhà cung cấp' WHEN amount<0 THEN 'Chi thu hồi hàng bán' ELSE 'Thu bán hàng' END) as type")
                ->from('payments')
                ->join('sales', 'payments.sale_id=sales.id', 'left')
                ->join('purchases', 'payments.purchase_id=purchases.id', 'left')
                ->group_by('payments.id');

            if ($user) {
                $this->db->where('payments.created_by', $user);
                $locdk.='AND created_by= "'.$user.'"';
            }
            if ($card) {
				$locdk.='AND cc_no like "%'.$card.'%"';
            }
            if ($cheque) {
				$locdk.='AND cheque_no like "%'.$cheque.'%"';
            }
            if ($transaction_id) {                
				$locdk.='AND transaction_id="'.$cheque.'"';
            }
            if ($customer) {
                $this->db->where('sales.customer_id', $customer);
            }
            if ($supplier) {
                $this->db->where('purchases.supplier_id', $supplier);
            }
            if ($biller) {
                $this->db->where('sales.biller_id', $biller);
            }
            if ($payment_ref) {
				$locdk.='AND payment_ref like "%'.$payment_ref.'%"';
            }
            if ($paid_by) {
                $this->db->where('payments.paid_by', $paid_by);
            }
            if ($sale_ref) {
				$locdk.='AND sale_ref like "%'.$sale_ref.'%"';
            }
            if ($purchase_ref) {
                $locdk.='AND purchase_ref like "%'.$purchase_ref.'%"';
            }
            if ($start_date) {
                $locdk.='AND date BETWEEN "' . $start_date . '" and "' . $end_date . '"';
            }
			
			// $this->db->where('payments.sale_id >',0);
			// $this->db->where('payments.return_id >',0);
			// $this->db->where('payments.purchase_id >',0);
			
			$report_query_ncc=$this->db->get_compiled_select();
            //$q2 = $this->db->get();
			
			//them cac khoan thanh toan tong chp ncc lhson code
			$lshonquery="SELECT ".$this->db->dbprefix('payments') . ".id,".$this->db->dbprefix('payments') . ".date, " . $this->db->dbprefix('payments') . ".reference_no as payment_ref,(CASE WHEN id_ncc_id_kh>0 THEN (SELECT CONCAT(name,'-',phone) FROM ".$this->db->dbprefix('companies') . " WHERE id=id_ncc_id_kh) ELSE null END) as sale_ref,(select concat(first_name,' ',last_name) FROM scodeweb_users WHERE id={$this->db->dbprefix('payments')}.created_by) as nhanvien,(select name from scodeweb_warehouses where id=scodeweb_payments.warehouse_id) as kho, null as purchase_ref, paid_by, amount, (CASE WHEN type='sent' THEN 'Chi nhà cung cấp' WHEN amount<0 THEN 'Chi thu hồi hàng bán' ELSE 'Thu bán hàng' END) as type,scodeweb_payments.created_by FROM ".$this->db->dbprefix('payments');
			
			if ($supplier) {
                $lshonquery.=" WHERE type='sent' AND ".$this->db->dbprefix('payments').".id_ncc_id_kh=".$supplier;
            }	
			if ($customer) {
                $lshonquery.=" WHERE type='received' AND ".$this->db->dbprefix('payments').".id_ncc_id_kh=".$customer;
            }	
			
			$this->db->where('payments.id_ncc_id_kh >',0);
			
			$query_ok="SELECT tbl.* FROM ($report_query_ncc UNION $lshonquery) as tbl WHERE id>0 $locdk ORDER BY tbl.date desc";
			  
			$q=$this->db->query($query_ok);		
			 
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
                $this->excel->getActiveSheet()->setTitle(lang('payments_report'));
                $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                $this->excel->getActiveSheet()->SetCellValue('B1', lang('payment_reference'));
                $this->excel->getActiveSheet()->SetCellValue('C1', lang('Đối tượng'));
				$this->excel->getActiveSheet()->SetCellValue('D1', lang('Nhân viên'));
				$this->excel->getActiveSheet()->SetCellValue('E1', lang('Kho'));
                $this->excel->getActiveSheet()->SetCellValue('F1', lang('purchase_reference'));
                $this->excel->getActiveSheet()->SetCellValue('G1', lang('paid_by'));
                $this->excel->getActiveSheet()->SetCellValue('H1', lang('amount'));
                $this->excel->getActiveSheet()->SetCellValue('I1', lang('type'));

                $row = 2;
                $total = 0;
                foreach ($data as $data_row) {
                    $this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->sma->hrld($data_row->date));
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->payment_ref);
                    $this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->sale_ref);
					$this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->nhanvien);
					$this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->kho);
                    $this->excel->getActiveSheet()->SetCellValue('F' . $row, $data_row->purchase_ref);
                    $this->excel->getActiveSheet()->SetCellValue('G' . $row, lang($data_row->paid_by));
                    $this->excel->getActiveSheet()->SetCellValue('H' . $row, $data_row->amount);
                    $this->excel->getActiveSheet()->SetCellValue('I' . $row, lang($data_row->type));
                    if ($data_row->type == 'returned' || $data_row->type == 'sent') {
                        $total -= $data_row->amount;
                    } else {
                        $total += $data_row->amount;
                    }
                    $row++;
                }
                $this->excel->getActiveSheet()->getStyle("F" . $row)->getBorders()
                    ->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);
                $this->excel->getActiveSheet()->SetCellValue('F' . $row, $total);

                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(20);
                $filename = 'payments_report';
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

        } else {

            $this->load->library('datatables');
            $this->db->select("" . $this->db->dbprefix('payments') . ".date, " . $this->db->dbprefix('payments') . ".reference_no as payment_ref, (CASE WHEN id_ncc_id_kh>0 THEN (SELECT CONCAT(name,'-',phone) FROM ".$this->db->dbprefix('companies') . " WHERE id=id_ncc_id_kh) ELSE " . $this->db->dbprefix('sales') . ".reference_no END) as sale_ref,(select concat(first_name,' ',last_name) FROM scodeweb_users WHERE id={$this->db->dbprefix('payments')}.created_by) as nhanvien,(select name from scodeweb_warehouses where id=scodeweb_payments.warehouse_id) as kho, " . $this->db->dbprefix('purchases') . ".reference_no as purchase_ref, paid_by, amount,(CASE WHEN type='sent' THEN 'Chi nhà cung cấp' WHEN amount<0 THEN 'Chi thu hồi hàng bán' ELSE 'Thu bán hàng' END) as type,". $this->db->dbprefix('payments') . ".id as id,scodeweb_payments.created_by")
                ->from('payments')
                ->join('sales', 'payments.sale_id=sales.id', 'left')
                ->join('purchases', 'payments.purchase_id=purchases.id', 'left')
                ->group_by('payments.id');
				
            if ($user) {
                $this->db->where('payments.created_by', $user);
                $locdk.='AND created_by= "'.$user.'"';
            }
            if ($card) {
				$locdk.='AND cc_no like "%'.$card.'%"';
            }
            if ($cheque) {
				$locdk.='AND cheque_no like "%'.$cheque.'%"';
            }
            if ($transaction_id) {                
				$locdk.='AND transaction_id="'.$cheque.'"';
            }
            if ($customer) {
                $this->db->where('sales.customer_id', $customer);
            }
            if ($supplier) {
                $this->db->where('purchases.supplier_id', $supplier);
            }
            if ($biller) {
                $this->db->where('sales.biller_id', $biller);
            }
            if ($payment_ref) {
				$locdk.='AND payment_ref like "%'.$payment_ref.'%"';
            }
            if ($paid_by) {
                $this->db->where('payments.paid_by', $paid_by);
            }
            if ($sale_ref) {
				$locdk.='AND sale_ref like "%'.$sale_ref.'%"';
            }
            if ($purchase_ref) {
                $locdk.='AND purchase_ref like "%'.$purchase_ref.'%"';
            }
            if ($start_date) {
                $locdk.='AND date BETWEEN "' . $start_date . '" and "' . $end_date . '"';
            }
			
			// $this->db->where('payments.sale_id >',0);
			// $this->db->where('payments.return_id >',0);
			// $this->db->where('payments.purchase_id >',0);
			
			$report_query_ncc=$this->db->get_compiled_select();
            //$q2 = $this->db->get();
			
			//them cac khoan thanh toan tong cho ncc lhson code
			$lshonquery="SELECT ".$this->db->dbprefix('payments') . ".date, " . $this->db->dbprefix('payments') . ".reference_no as payment_ref, (CASE WHEN id_ncc_id_kh>0 THEN (SELECT CONCAT(name,'-',phone) FROM ".$this->db->dbprefix('companies') . " WHERE id=id_ncc_id_kh) ELSE null END) as sale_ref,(select concat(first_name,' ',last_name) FROM scodeweb_users WHERE id={$this->db->dbprefix('payments')}.created_by) as nhanvien,(select name from scodeweb_warehouses where id=scodeweb_payments.warehouse_id) as kho, null as purchase_ref, paid_by, amount, (CASE WHEN type='0' THEN 'Đối tượng khác' WHEN type='2' THEN 'Thu khách hàng' WHEN type='3' THEN 'Thu NCC' WHEN type='1' THEN 'Thu nhân viên' WHEN amount<0 THEN 'Chi thu hồi hàng bán' WHEN type='sent' THEN 'Chi nhà cung cấp' ELSE 'Thu bán hàng' END) as type,id,scodeweb_payments.created_by FROM ".$this->db->dbprefix('payments');
			 			 
			if ($supplier) {
                $lshonquery.=" WHERE type='sent' AND ".$this->db->dbprefix('payments').".id_ncc_id_kh=".$supplier;
            }	
			if ($customer) {
                $lshonquery.=" WHERE type='received' AND ".$this->db->dbprefix('payments').".id_ncc_id_kh=".$customer;
            }
			
			$this->db->where('payments.id_ncc_id_kh >',0);
			
			$query_ok="SELECT tbl.* FROM ($report_query_ncc UNION $lshonquery) as tbl WHERE id>0 $locdk GROUP BY tbl.id ORDER BY tbl.date desc";
			  
						
			echo $rs=$this->datatables->generate_querylhson($output = 'json', $charset = 'UTF-8',$query_ok);
		
			
        }

    }
	function khoanthu()
    {
        $this->sma->checkPermissions('index',false,'thu');
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['users'] = $this->reports_model->getStaff();
        $this->data['billers'] = $this->site->getAllCompanies('biller');
        $this->data['pos_settings'] = POS ? $this->reports_model->getPOSSetting('biller') : FALSE;
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => 'Danh sách các khoản thu'));
        $meta = array('page_title' => 'Danh sách các khoản thu', 'bc' => $bc);
        $this->page_construct('reports/khoanthu', $meta, $this->data);
    }

    function getKhoanThuReport($pdf = NULL, $xls = NULL)
    {
        $this->sma->checkPermissions('index',TRUE,'thu');

        $user = $this->input->get('user') ? $this->input->get('user') : NULL;
        $customer = $this->input->get('customer') ? $this->input->get('customer') : NULL;
        $biller = $this->input->get('biller') ? $this->input->get('biller') : NULL;
        $payment_ref = $this->input->get('payment_ref') ? $this->input->get('payment_ref') : NULL;
        $paid_by = $this->input->get('paid_by') ? $this->input->get('paid_by') : NULL;
        $sale_ref = $this->input->get('sale_ref') ? $this->input->get('sale_ref') : NULL;
        $card = $this->input->get('card') ? $this->input->get('card') : NULL;
        $cheque = $this->input->get('cheque') ? $this->input->get('cheque') : NULL;
        $transaction_id = $this->input->get('tid') ? $this->input->get('tid') : NULL;
        $start_date = $this->input->get('start_date') ? $this->input->get('start_date') : NULL;
        $end_date = $this->input->get('end_date') ? $this->input->get('end_date') : NULL;
        $warehouse_id=NULL;
        if ($start_date) {
            $start_date = $this->sma->fsd($start_date);
            $end_date = $this->sma->fsd($end_date);
        }
        if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $user = $this->session->userdata('user_id');
        }else if (!$this->Owner && !$this->Admin && $this->session->userdata('view_right')) {
            $warehouse_id = $this->session->userdata('warehouse_id');
        }
        if ($pdf || $xls) {

            $this->db->select("" . $this->db->dbprefix('payments') . ".date, " . $this->db->dbprefix('payments') . ".reference_no as payment_ref, (CASE WHEN id_ncc_id_kh>0 THEN (SELECT CONCAT(name,'-',phone) FROM ".$this->db->dbprefix('companies') . " WHERE id=id_ncc_id_kh) ELSE " . $this->db->dbprefix('sales') . ".reference_no END) as sale_ref,(select concat(first_name,' ',last_name) FROM scodeweb_users WHERE id={$this->db->dbprefix('payments')}.created_by) as nhanvien, paid_by, amount,(CASE WHEN type='returned' THEN 'Chi thu hồi hàng bán' WHEN id_ncc_id_kh='0' AND tiencoc_id=0 THEN 'Thu bán hàng' WHEN id_ncc_id_kh>0 THEN 'Thu khách hàng' WHEN tiencoc_id>0 THEN 'Thu tiền cọc' END) as type")
                ->from('payments')
                ->join('sales', 'payments.sale_id=sales.id', 'left')
                ->group_by('payments.id');

            if ($user) {
                $this->db->where('payments.created_by', $user);
            }
            if ($warehouse_id) {
                $this->db->where('payments.warehouse_id', $warehouse_id);
            }
            if ($card) {
                $this->db->like('payments.cc_no', $card, 'both');
            }
            if ($cheque) {
                $this->db->where('payments.cheque_no', $cheque);
            }
            if ($transaction_id) {
                $this->db->where('payments.transaction_id', $transaction_id);
            }
            if ($customer) {
                $this->db->where('sales.customer_id', $customer);
            }
            if ($biller) {
                $this->db->where('sales.biller_id', $biller);
            }
            if ($customer) {
                $this->db->where('sales.customer_id', $customer);
            }
            if ($payment_ref) {
                $this->db->like('payments.reference_no', $payment_ref, 'both');
            }
            if ($paid_by) {
                $this->db->where('payments.paid_by', $paid_by);
            }
            if ($sale_ref) {
                $this->db->like('sales.reference_no', $sale_ref, 'both');
            }
            if ($start_date) {
                $this->db->where($this->db->dbprefix('payments').'.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
            }
			
			$this->db->where('payments.type', 'received');
			
			$report_query_ncc=$this->db->get_compiled_select();
           // $q2 = $this->db->get();
			
			//them cac khoan thanh toan tong chp ncc lhson code
			$lshonquery="SELECT ".$this->db->dbprefix('payments') . ".date, " . $this->db->dbprefix('payments') . ".reference_no as payment_ref, (CASE WHEN type='3' THEN (SELECT CONCAT(name,'-',phone) FROM ".$this->db->dbprefix('companies') . " WHERE id=id_ncc_id_kh) WHEN type='1' THEN (SELECT CONCAT(first_name,' ',last_name) FROM ".$this->db->dbprefix('users') . " WHERE id=id_ncc_id_kh) ELSE (SELECT CONCAT(name,'-',phone) FROM ".$this->db->dbprefix('companies') . " WHERE id=id_ncc_id_kh) END) as sale_ref,(select concat(first_name,' ',last_name) FROM scodeweb_users WHERE id={$this->db->dbprefix('payments')}.created_by) as nhanvien, paid_by, amount, (CASE WHEN type='returned' THEN 'Chi thu hồi hàng bán' WHEN type='0' THEN (SELECT name FROM scodeweb_expense_categories WHERE id=type_cate) WHEN type='3' THEN 'Thu Nhà Cung Cấp' WHEN type='1' THEN 'Thu Nhân Viên' ELSE 'Thu bán hàng' END) as type FROM ".$this->db->dbprefix('payments');
			
			$addwhere=" WHERE type!='sent' and type!='received'";
                
            if ($customer) {
                $addwhere=" WHERE type='received' AND ".$this->db->dbprefix('payments').".id_ncc_id_kh=".$customer;
                if ($warehouse_id) {
                    $addwhere=" WHERE type='received' AND ".$this->db->dbprefix('payments').".id_ncc_id_kh=".$customer." AND warehouse_id=".$warehouse_id;
                }
            }else{
                if ($warehouse_id) {
                    $addwhere=" WHERE type!='sent' and type!='received' AND warehouse_id=".$warehouse_id;
                }
            }   
            $lshonquery.=$addwhere;	
			
			
			$query_ok="SELECT tbl.* FROM ($report_query_ncc UNION $lshonquery) as tbl ORDER BY tbl.date desc";
			  
			$q=$this->db->query($query_ok);		
			 
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
                $this->excel->getActiveSheet()->setTitle(lang('Danh sách phiếu thu'));
                $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                $this->excel->getActiveSheet()->SetCellValue('B1', lang('HĐ Thu'));
                $this->excel->getActiveSheet()->SetCellValue('C1', lang('HĐ Bán'));
                $this->excel->getActiveSheet()->SetCellValue('D1', lang('Nhân viên'));
				$this->excel->getActiveSheet()->SetCellValue('E1', lang('paid_by'));
                $this->excel->getActiveSheet()->SetCellValue('F1', lang('amount'));
                $this->excel->getActiveSheet()->SetCellValue('G1', lang('type'));

                $row = 2;
                $total = 0;
                foreach ($data as $data_row) {
                    $this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->sma->hrld($data_row->date));
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->payment_ref);
                    $this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->sale_ref);
					$this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->nhanvien);
                    $this->excel->getActiveSheet()->SetCellValue('E' . $row, lang($data_row->paid_by));
                    $this->excel->getActiveSheet()->SetCellValue('F' . $row, $data_row->amount);
                    $this->excel->getActiveSheet()->SetCellValue('G' . $row, $data_row->type);
                    if ($data_row->type == 'returned' || $data_row->type == 'sent') {
                        $total -= $data_row->amount;
                    } else {
                        $total += $data_row->amount;
                    }
                    $row++;
                }
                $this->excel->getActiveSheet()->getStyle("F" . $row)->getBorders()
                    ->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);
                $this->excel->getActiveSheet()->SetCellValue('F' . $row, $total);

                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
                $filename = 'PhieuThu_report';
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

        } else {

            $this->load->library('datatables');
            $this->db->select("" . $this->db->dbprefix('payments') . ".date, " . $this->db->dbprefix('payments') . ".reference_no as payment_ref,c_name,c_phone,c_address,(select concat(first_name,' ',last_name) FROM scodeweb_users WHERE id={$this->db->dbprefix('payments')}.created_by) as nhanvien, paid_by, amount,(CASE WHEN type='returned' THEN 'Chi thu hồi hàng bán' WHEN id_ncc_id_kh='0' AND tiencoc_id=0 THEN 'Thu bán hàng' WHEN id_ncc_id_kh>0 THEN 'Thu khách hàng' WHEN tiencoc_id>0 THEN 'Thu tiền cọc' END) as type,".$this->db->dbprefix('payments') . ".attachment,".$this->db->dbprefix('payments') . ".note,(CASE WHEN id_ncc_id_kh>0 THEN CONCAT(" . $this->db->dbprefix('payments') . ".id,'-','THUKH') WHEN tiencoc_id>0 THEN CONCAT(" . $this->db->dbprefix('payments') . ".tiencoc_id,'-','TIENCOC') ELSE " . $this->db->dbprefix('payments') . ".id END) as id")
                ->from('payments')
                ->join('sales', 'payments.sale_id=sales.id', 'left')
                ->group_by('payments.id');
            if ($user) {
                $this->db->where('payments.created_by', $user);
            }
            if ($warehouse_id) {
                $this->db->where('payments.warehouse_id', $warehouse_id);
            }
            if ($card) {
                $this->db->like('payments.cc_no', $card, 'both');
            }
            if ($cheque) {
                $this->db->where('payments.cheque_no', $cheque);
            }
            if ($transaction_id) {
                $this->db->where('payments.transaction_id', $transaction_id);
            }
            if ($customer) {
                $this->db->where('sales.customer_id', $customer);
            }
            if ($biller) {
                $this->db->where('sales.biller_id', $biller);
            }
            if ($customer) {
                $this->db->where('sales.customer_id', $customer);
            }
            if ($payment_ref) {
                $this->db->like('payments.reference_no', $payment_ref, 'both');
            }
            if ($paid_by) {
                $this->db->where('payments.paid_by', $paid_by);
            }
            if ($sale_ref) {
                $this->db->like('sales.reference_no', $sale_ref, 'both');
            }
            if ($start_date) {
                $this->db->where($this->db->dbprefix('payments').'.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
            }
			$this->db->where('payments.type', 'received');
			$report_query_ncc=$this->db->get_compiled_select();
           // $q2 = $this->db->get();
			
			//them cac khoan thanh toan tong chp ncc lhson code
			$lshonquery="SELECT ".$this->db->dbprefix('payments') . ".date, " . $this->db->dbprefix('payments') . ".reference_no as payment_ref,c_name,c_phone,c_address,(select concat(first_name,' ',last_name) FROM scodeweb_users WHERE id={$this->db->dbprefix('payments')}.created_by) as nhanvien, paid_by, amount,(CASE WHEN type='returned' THEN 'Chi thu hồi hàng bán' WHEN type='0' THEN (SELECT name FROM scodeweb_expense_categories WHERE id=type_cate) WHEN type='3' THEN 'Thu Nhà Cung Cấp' WHEN type='1' THEN 'Thu Nhân Viên' ELSE 'Thu bán hàng' END) as type,".$this->db->dbprefix('payments') . ".attachment,".$this->db->dbprefix('payments') . ".note,CONCAT(id,'-','KHAC') as id FROM ".$this->db->dbprefix('payments');
			
			$addwhere=" WHERE type!='sent' and type!='received'";
                
            if ($customer) {
                $addwhere=" WHERE type='received' AND ".$this->db->dbprefix('payments').".id_ncc_id_kh=".$customer;
                if ($warehouse_id) {
                    $addwhere=" WHERE type='received' AND ".$this->db->dbprefix('payments').".id_ncc_id_kh=".$customer." AND warehouse_id=".$warehouse_id;
                }
            }else{
                if ($warehouse_id) {
                    $addwhere=" WHERE type!='sent' and type!='received' AND warehouse_id=".$warehouse_id;
                }
            }   
            $lshonquery.=$addwhere;	
			
			
			$query_ok="SELECT tbl.* FROM ($report_query_ncc UNION $lshonquery) as tbl ORDER BY tbl.date desc";
			  
						
			echo $rs=$this->datatables->generate_querylhson($output = 'json', $charset = 'UTF-8',$query_ok);
		
			
        }

    }
	function baocaothu()
    {
        $this->sma->checkPermissions('index',false,'thu');
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['users'] = $this->reports_model->getStaff();
        $this->data['billers'] = $this->site->getAllCompanies('biller');
		$this->data['categories'] = $this->reports_model->getExpenseCategories(1);
        $this->data['pos_settings'] = POS ? $this->reports_model->getPOSSetting('biller') : FALSE;
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => 'Báo cáo các khoản thu'));
        $meta = array('page_title' => 'Báo cáo các khoản thu', 'bc' => $bc);
        $this->page_construct('reports/khoanthu_report', $meta, $this->data);
    }

    function getBaocaothuReport($pdf = NULL, $xls = NULL)
    {
        $this->sma->checkPermissions('index',TRUE,'thu');

        $user = $this->input->get('user') ? $this->input->get('user') : NULL;
		$customer = $this->input->get('customer') ? $this->input->get('customer') : NULL;
        $category = $this->input->get('category') ? $this->input->get('category') : NULL;
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

		$_where=" WHERE tbl.id>0";
        if ($pdf || $xls) {

            $this->db->select($this->db->dbprefix('payments') . ".id," . $this->db->dbprefix('payments') . ".date, " . $this->db->dbprefix('payments') . ".reference_no as payment_ref, (CASE WHEN id_ncc_id_kh>0 THEN (SELECT CONCAT(name,'-',phone) FROM ".$this->db->dbprefix('companies') . " WHERE id=id_ncc_id_kh) ELSE " . $this->db->dbprefix('sales') . ".reference_no END) as sale_ref,(select concat(first_name,' ',last_name) FROM scodeweb_users WHERE id={$this->db->dbprefix('payments')}.created_by) as nhanvien, paid_by, amount,(CASE WHEN type='returned' THEN 'Chi thu hồi hàng bán hàng bán ' WHEN id_ncc_id_kh='0' AND tiencoc_id=0 THEN 'Thu bán hàng' WHEN id_ncc_id_kh>0 THEN 'Thu khách hàng' WHEN tiencoc_id>0 THEN 'Thu tiền cọc' END) as type,{$this->db->dbprefix('payments')}.note,type_cate,id_ncc_id_kh,scodeweb_payments.created_by")
                ->from('payments')
                ->join('sales', 'payments.sale_id=sales.id', 'left')
                ->group_by('payments.id');

            if ($user) {
                $this->db->where('payments.created_by', $user);
                $_where.=" AND tbl.created_by='".$user."'";
            }
            
            if ($reference_no!='') {
                $_where.=" AND tbl.reference_no LIKE '%".$reference_no."%'";
            }
			if ($note!='') {
				$_where.=" AND tbl.note LIKE '%".$note."%'";
                
            }
			
            if ($start_date) {
				$_where.=" AND tbl.date BETWEEN '".$start_date."' AND '".$end_date."'";                          
            }
			
			$this->db->where('payments.type', 'received');
			
			$report_query_ncc=$this->db->get_compiled_select();
           // $q2 = $this->db->get();
			
			//them cac khoan thanh toan tong chp ncc lhson code
			$lshonquery="SELECT ".$this->db->dbprefix('payments') . ".id,".$this->db->dbprefix('payments') . ".date, " . $this->db->dbprefix('payments') . ".reference_no as payment_ref, (CASE WHEN type='3' THEN (SELECT CONCAT(name,'-',phone) FROM ".$this->db->dbprefix('companies') . " WHERE id=id_ncc_id_kh) WHEN type='1' THEN (SELECT CONCAT(first_name,' ',last_name) FROM ".$this->db->dbprefix('users') . " WHERE id=id_ncc_id_kh) ELSE (SELECT CONCAT(name,'-',phone) FROM ".$this->db->dbprefix('companies') . " WHERE id=id_ncc_id_kh) END) as sale_ref,(select concat(first_name,' ',last_name) FROM scodeweb_users WHERE id={$this->db->dbprefix('payments')}.created_by) as nhanvien, paid_by, amount, (CASE WHEN type='returned' THEN 'Chi thu hồi hàng bán' WHEN id_ncc_id_kh='0' AND tiencoc_id=0 THEN 'Thu bán hàng' WHEN id_ncc_id_kh>0 THEN 'Thu khách hàng' WHEN amount<0 THEN 'Chi thu hồi hàng bán hàng bán' WHEN tiencoc_id>0 THEN 'Thu tiền cọc' END) as type,scodeweb_payments.note,type_cate,id_ncc_id_kh,scodeweb_payments.created_by FROM ".$this->db->dbprefix('payments');
			
			$lshonquery.=" WHERE type!='sent' AND type!='received'";
            
			if ($customer) {
                $lshonquery.=" AND type='received' AND ".$this->db->dbprefix('payments').".id_ncc_id_kh=".$customer;
            }			
			if ($user) {
                $lshonquery.=" AND payments.created_by=".$user;
            }

			$query_ok="SELECT tbl.* FROM ($report_query_ncc UNION $lshonquery) as tbl $_where ORDER BY tbl.date desc";
			
			 if ((int)$category>0) {
				$_where.=" AND type_cate=".$category;				 
				$query_ok="SELECT tbl.* FROM ($lshonquery) as tbl $_where ORDER BY tbl.date desc";
			 }
			 else if ($category=="banhang") {
				 $_where.=" AND id_ncc_id_kh=0 and tiencoc_id=0";				 
				$query_ok="SELECT tbl.*FROM ($report_query_ncc) as tbl $_where ORDER BY tbl.date desc";
			 }else if ($category=="khachhang") {
				 $_where.=" AND id_ncc_id_kh>0";
				$query_ok="SELECT tbl.* FROM ($report_query_ncc) as tbl $_where ORDER BY tbl.date desc";
			 }else if ($category=="nhanvien") {
				 $_where.=" AND type='Thu Nhân Viên'";
				$query_ok="SELECT tbl.* FROM ($lshonquery) as tbl $_where ORDER BY tbl.date desc";
			 }else if ($category=="nhacungcap") {
				 $_where.=" AND type='Thu Nhà Cung Cấp'";
				$query_ok="SELECT tbl.* FROM ($lshonquery) as tbl $_where ORDER BY tbl.date desc";
			 }
             else if ($category=="tiencoc") {
                 $_where.=" AND tiencoc_id>0";
                $query_ok="SELECT tbl.* FROM ($lshonquery) as tbl $_where ORDER BY tbl.date desc";
             }
			$q=$this->db->query($query_ok);		
			 
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
                $this->excel->getActiveSheet()->setTitle(lang('Báo cáo khoản thu'));
                $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                $this->excel->getActiveSheet()->SetCellValue('B1', lang('HĐ Thu'));
                $this->excel->getActiveSheet()->SetCellValue('C1', lang('HĐ Bán - Đối tượng'));
                $this->excel->getActiveSheet()->SetCellValue('D1', lang('Nhân viên'));
				$this->excel->getActiveSheet()->SetCellValue('E1', lang('paid_by'));
                $this->excel->getActiveSheet()->SetCellValue('F1', lang('amount'));
                $this->excel->getActiveSheet()->SetCellValue('G1', lang('type'));

                $row = 2;
                $total = 0;
                foreach ($data as $data_row) {
                    $this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->sma->hrld($data_row->date));
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->payment_ref);
                    $this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->sale_ref);
					$this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->nhanvien);
                    $this->excel->getActiveSheet()->SetCellValue('E' . $row, lang($data_row->paid_by));
                    $this->excel->getActiveSheet()->SetCellValue('F' . $row, $data_row->amount);
                    $this->excel->getActiveSheet()->SetCellValue('G' . $row, $data_row->type);
                    if ($data_row->type == 'returned' || $data_row->type == 'sent') {
                        $total -= $data_row->amount;
                    } else {
                        $total += $data_row->amount;
                    }
                    $row++;
                }
                $this->excel->getActiveSheet()->getStyle("F" . $row)->getBorders()
                    ->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);
                $this->excel->getActiveSheet()->SetCellValue('F' . $row, $total);

                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
                $filename = 'BaoCao_KhoanThu_report';
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

        } else {

            $this->load->library('datatables');
            $this->db->select("" . $this->db->dbprefix('payments') . ".date, " . $this->db->dbprefix('payments') . ".reference_no as payment_ref,c_name,c_phone,c_address,(select concat(first_name,' ',last_name) FROM scodeweb_users WHERE id={$this->db->dbprefix('payments')}.created_by) as nhanvien, paid_by, amount,(CASE WHEN type='returned' THEN 'Chi thu hồi hàng bán' WHEN id_ncc_id_kh='0' AND tiencoc_id=0 THEN 'Thu bán hàng' WHEN id_ncc_id_kh>0 THEN 'Thu khách hàng' WHEN amount<0 THEN 'Chi thu hồi hàng bán hàng bán' WHEN tiencoc_id>0 THEN 'Thu tiền cọc' END) as type,".$this->db->dbprefix('payments') . ".attachment,".$this->db->dbprefix('payments') . ".note,type_cate,id_ncc_id_kh,(CASE WHEN id_ncc_id_kh>0 THEN CONCAT(" . $this->db->dbprefix('payments') . ".id,'-','THUKH') WHEN tiencoc_id>0 THEN CONCAT(" . $this->db->dbprefix('payments') . ".tiencoc_id,'-','TIENCOC') ELSE " . $this->db->dbprefix('payments') . ".id END) as id,scodeweb_payments.created_by,scodeweb_payments.tiencoc_id")
                ->from('payments')
                ->join('sales', 'payments.sale_id=sales.id', 'left')
                ->group_by('payments.id');
            if ($user) {
                $this->db->where('payments.created_by', $user);
                $_where.=" AND tbl.created_by = '".$user."'";
            }
           
             if ($reference_no!='') {
                $_where.=" AND tbl.payment_ref LIKE '%".$reference_no."%'";
            }
			if ($note!='') {
				$_where.=" AND tbl.note LIKE '%".$note."%'";
                
            }
			
            if ($start_date) {
				$_where.=" AND tbl.date BETWEEN '".$start_date."' AND '".$end_date."'";                
            }
			
			$this->db->where('payments.type', 'received');
            
			
			$report_query_ncc=$this->db->get_compiled_select();
           // $q2 = $this->db->get();
			
			//them cac khoan thanh toan tong chp ncc lhson code
			$lshonquery="SELECT ".$this->db->dbprefix('payments') . ".date, " . $this->db->dbprefix('payments') . ".reference_no as payment_ref,c_name,c_phone,c_address,(select concat(first_name,' ',last_name) FROM scodeweb_users WHERE id={$this->db->dbprefix('payments')}.created_by) as nhanvien, paid_by, amount,(CASE WHEN type='0' THEN (SELECT name FROM scodeweb_expense_categories WHERE id=type_cate) WHEN type='3' THEN 'Thu Nhà Cung Cấp' WHEN type='1' THEN 'Thu Nhân Viên' WHEN amount<0 THEN 'Chi thu hồi hàng bán hàng bán' ELSE 'Thu bán hàng' END) as type,".$this->db->dbprefix('payments') . ".attachment,".$this->db->dbprefix('payments') . ".note,type_cate,id_ncc_id_kh,CONCAT(id,'-','KHAC') as id,scodeweb_payments.created_by,scodeweb_payments.tiencoc_id FROM ".$this->db->dbprefix('payments');
			
			$lshonquery.=" WHERE type!='sent' and type!='received'";
            	
			if ($customer) {
                $lshonquery.=" WHERE type='received' AND ".$this->db->dbprefix('payments').".id_ncc_id_kh=".$customer;
            }	
			$query_ok="SELECT tbl.date, tbl.payment_ref, tbl.c_name,tbl.c_phone,tbl.c_address, tbl.nhanvien, tbl.paid_by, tbl.amount, tbl.type, tbl.attachment, tbl.note, tbl.id FROM ($report_query_ncc UNION $lshonquery) as tbl $_where ORDER BY tbl.date desc";
			
			 if ((int)$category>0) {
				$_where.=" AND type_cate=".$category;
				 
				$query_ok="SELECT tbl.date, tbl.payment_ref, tbl.c_name,tbl.c_phone,tbl.c_address, tbl.nhanvien, tbl.paid_by, tbl.amount, tbl.type, tbl.attachment, tbl.note,tbl.id FROM ($lshonquery) as tbl $_where ORDER BY tbl.date desc";
			 }
			 else if ($category=="banhang") {
				 $_where.=" AND id_ncc_id_kh=0 AND tiencoc_id=0";				 
				$query_ok="SELECT tbl.date, tbl.payment_ref, tbl.c_name,tbl.c_phone,tbl.c_address, tbl.nhanvien, tbl.paid_by, tbl.amount, tbl.type, tbl.attachment, tbl.note,tbl.id FROM ($report_query_ncc) as tbl $_where ORDER BY tbl.date desc";
			 }else if ($category=="khachhang") {
				 $_where.=" AND id_ncc_id_kh>0";
				$query_ok="SELECT tbl.date, tbl.payment_ref, tbl.c_name,tbl.c_phone,tbl.c_address, tbl.nhanvien, tbl.paid_by, tbl.amount, tbl.type, tbl.attachment, tbl.note, tbl.id FROM ($report_query_ncc) as tbl $_where ORDER BY tbl.date desc";
			 }else if ($category=="nhanvien") {
				 $_where.=" AND type='Thu Nhân Viên'";
				$query_ok="SELECT tbl.date, tbl.payment_ref, tbl.c_name,tbl.c_phone,tbl.c_address, tbl.nhanvien, tbl.paid_by, tbl.amount, tbl.type, tbl.attachment, tbl.note, tbl.id FROM ($lshonquery) as tbl $_where ORDER BY tbl.date desc";
			 }else if ($category=="nhacungcap") {
				 $_where.=" AND type='Thu Nhà Cung Cấp'";
				$query_ok="SELECT tbl.date, tbl.payment_ref, tbl.c_name,tbl.c_phone,tbl.c_address, tbl.nhanvien, tbl.paid_by, tbl.amount, tbl.type, tbl.attachment, tbl.note, tbl.id FROM ($lshonquery) as tbl $_where ORDER BY tbl.date desc";
			 }
			else if ($category=="tiencoc") {
                 $_where.=" AND tiencoc_id>0";                 
                $query_ok="SELECT tbl.date, tbl.payment_ref, tbl.c_name,tbl.c_phone,tbl.c_address, tbl.nhanvien, tbl.paid_by, tbl.amount, tbl.type, tbl.attachment, tbl.note,tbl.id FROM ($report_query_ncc) as tbl $_where ORDER BY tbl.date desc";
             }
			//echo $query_ok;
			  
						
			echo $rs=$this->datatables->generate_querylhson($output = 'json', $charset = 'UTF-8',$query_ok);
		
			
        }

    }
    function customers()
    {
        $this->sma->checkPermissions('customers');
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');

        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('customers_report')));
        $meta = array('page_title' => lang('customers_report'), 'bc' => $bc);
        $this->page_construct('reports/customers', $meta, $this->data);
    }

    function getCustomers($pdf = NULL, $xls = NULL,$fillter=0,$start_date=0,$end_date=0)
    {
        $this->sma->checkPermissions('customers', TRUE);
        if ($start_date=='') {
            $start_date=0;
        }
        if ($end_date=='') {
            $end_date=0;
        }
        if ($start_date!=0&&$end_date==0) {
            $end_date=$start_date;
        }
        $sql_add_date='';
        if ($start_date!=0&&$end_date!=0) {
             $sql_add_date=" AND DATE_FORMAT(date, '%Y-%m-%d') BETWEEN '$start_date' AND '$end_date' ";                        
        }  
        if ($pdf || $xls) {

            /*$this->db->select($this->db->dbprefix('companies') . ".id as id, company, name, phone, email, count(" . $this->db->dbprefix('sales') . ".id) as total, COALESCE(sum(grand_total), 0) as total_amount, COALESCE(sum(paid), 0)+(select sum(amount) from scodeweb_payments WHERE id_ncc_id_kh=customer_id and type='received') as paid, ( COALESCE(sum(grand_total), 0) - COALESCE(sum(paid), 0)-(select sum(amount) from scodeweb_payments WHERE id_ncc_id_kh=customer_id and type='received')) as balance", FALSE)
                ->from("companies")
                ->join('sales', 'sales.customer_id=companies.id')
                ->where('companies.group_name', 'customer')
                ->order_by('companies.company asc')
                ->group_by('companies.id');*/
				
			$this->db->select($this->db->dbprefix('companies') . ".id as id, company, name, phone, email,(SELECT count(" . $this->db->dbprefix('sales') . ".id) FROM {$this->db->dbprefix('sales')} WHERE customer_id={$this->db->dbprefix('companies')}.id AND sale_status!='returned' $sql_add_date) as total,(SELECT COALESCE(sum(grand_total), 0) FROM {$this->db->dbprefix('sales')} WHERE customer_id={$this->db->dbprefix('companies')}.id AND sale_status!='returned' $sql_add_date) as total_amount,(SELECT COALESCE(sum(paid), 0) FROM {$this->db->dbprefix('sales')} WHERE customer_id={$this->db->dbprefix('companies')}.id AND sale_status!='returned' $sql_add_date) + (select COALESCE(sum(amount), 0) from scodeweb_payments WHERE id_ncc_id_kh={$this->db->dbprefix('companies')}.id and type='received' AND amount>0 $sql_add_date) as paid,((SELECT COALESCE(sum(grand_total), 0) FROM {$this->db->dbprefix('sales')} WHERE customer_id={$this->db->dbprefix('companies')}.id AND sale_status!='returned' $sql_add_date) - (SELECT COALESCE(sum(paid), 0) FROM {$this->db->dbprefix('sales')} WHERE customer_id={$this->db->dbprefix('companies')}.id AND sale_status!='returned' $sql_add_date) - (select COALESCE(sum(amount), 0) from scodeweb_payments WHERE id_ncc_id_kh={$this->db->dbprefix('companies')}.id and type='received' AND amount>0 $sql_add_date)) as balance,(SELECT count(" . $this->db->dbprefix('sales') . ".id) FROM {$this->db->dbprefix('sales')} WHERE customer_id={$this->db->dbprefix('companies')}.id AND sale_status='returned' $sql_add_date) as tongslthu,(SELECT COALESCE(sum(grand_total), 0) FROM {$this->db->dbprefix('sales')} WHERE customer_id={$this->db->dbprefix('companies')}.id AND sale_status='returned' $sql_add_date) as tongthu,(SELECT COALESCE(sum(paid), 0) FROM {$this->db->dbprefix('sales')} WHERE customer_id={$this->db->dbprefix('companies')}.id AND sale_status='returned' $sql_add_date) as dattthu,nobandau", FALSE)->from("companies")
                ->where('companies.group_name', 'customer');
			if ($fillter==1) {
                $this->db->where("((SELECT COALESCE(sum(grand_total), 0) FROM {$this->db->dbprefix('sales')} WHERE customer_id={$this->db->dbprefix('companies')}.id AND sale_status!='returned') - (SELECT COALESCE(sum(paid), 0) FROM {$this->db->dbprefix('sales')} WHERE customer_id={$this->db->dbprefix('companies')}.id AND sale_status!='returned') - (select COALESCE(sum(amount), 0) from scodeweb_payments WHERE id_ncc_id_kh={$this->db->dbprefix('companies')}.id and type='received' AND amount>0))>",0);
                               
            }else if ($fillter==2) {
                $this->db->where("((SELECT COALESCE(sum(grand_total), 0) FROM {$this->db->dbprefix('sales')} WHERE customer_id={$this->db->dbprefix('companies')}.id AND sale_status!='returned') - (SELECT COALESCE(sum(paid), 0) FROM {$this->db->dbprefix('sales')} WHERE customer_id={$this->db->dbprefix('companies')}.id AND sale_status!='returned') - (select COALESCE(sum(amount), 0) from scodeweb_payments WHERE id_ncc_id_kh={$this->db->dbprefix('companies')}.id and type='received' AND amount>0))=",0);                 
            }   	
            $q = $this->db->get();
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
                $this->excel->getActiveSheet()->setTitle(lang('customers_report'));
                $this->excel->getActiveSheet()->SetCellValue('A1', lang('company'));
                $this->excel->getActiveSheet()->SetCellValue('B1', lang('name'));
                $this->excel->getActiveSheet()->SetCellValue('C1', lang('phone'));
                $this->excel->getActiveSheet()->SetCellValue('D1', lang('email'));
                $this->excel->getActiveSheet()->SetCellValue('E1', lang('total_sales'));
                $this->excel->getActiveSheet()->SetCellValue('F1', lang('total_amount'));
                $this->excel->getActiveSheet()->SetCellValue('G1', lang('paid'));
				$this->excel->getActiveSheet()->SetCellValue('H1', lang('Tổng SL Thu'));
				$this->excel->getActiveSheet()->SetCellValue('I1', lang('Tổng tiền thu'));
				$this->excel->getActiveSheet()->SetCellValue('K1', lang('Tổng thanh toán thu'));
				$this->excel->getActiveSheet()->SetCellValue('L1', lang('Tổng nợ thu'));
                $this->excel->getActiveSheet()->SetCellValue('M1', lang('Nợ ban đầu'));
				$this->excel->getActiveSheet()->SetCellValue('N1', lang('balance'));

                $row = 2;
                foreach ($data as $data_row) {
                    $this->excel->getActiveSheet()->SetCellValue('A' . $row, $data_row->company);
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->name);
                    $this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->phone);
                    $this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->email);
                    $this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->total);
                    $this->excel->getActiveSheet()->SetCellValue('F' . $row, $data_row->total_amount);
                    $this->excel->getActiveSheet()->SetCellValue('G' . $row, $data_row->paid);
					
					$this->excel->getActiveSheet()->SetCellValue('H' . $row, $data_row->tongslthu);
					$this->excel->getActiveSheet()->SetCellValue('I' . $row, $data_row->tongthu);
					$this->excel->getActiveSheet()->SetCellValue('K' . $row, $data_row->dattthu);
					$this->excel->getActiveSheet()->SetCellValue('L' . $row, $data_row->tongthu-$data_row->dattthu);
					
					$this->excel->getActiveSheet()->SetCellValue('M' . $row, $data_row->nobandau);
                    $this->excel->getActiveSheet()->SetCellValue('N' . $row, $data_row->balance);
                    $row++;
                }

                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('K')->setWidth(15);
                $filename = 'customers_report';
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

        } else {
			
			$this->load->library('datatables');
           /* $s = "( SELECT customer_id, count(" . $this->db->dbprefix('sales') . ".id) as total, COALESCE(sum(grand_total), 0) as total_amount, COALESCE(sum(paid), 0)+(select sum(amount) from scodeweb_payments WHERE id_ncc_id_kh=customer_id and type='received') as paid, ( COALESCE(sum(grand_total), 0) - COALESCE(sum(paid), 0)-(select sum(amount) from scodeweb_payments WHERE id_ncc_id_kh=customer_id and type='received')) as balance,0 from {$this->db->dbprefix('sales')} GROUP BY {$this->db->dbprefix('sales')}.customer_id ) FS";

           
            $this->datatables
                ->select($this->db->dbprefix('companies') . ".id as id, company, name, phone, email, FS.total, FS.total_amount, FS.paid, FS.balance", FALSE)
                ->from("companies")
                ->join($s, 'FS.customer_id=companies.id')
                ->where('companies.group_name', 'customer')
                ->group_by('companies.id')
                ->add_column("Actions", "<div class='text-center'><a class=\"tip\" title='" . lang("view_report") . "' href='" . site_url('reports/customer_report/$1') . "'><span class='label label-primary'>" . lang("view_report") . "</span></a></div>", "id")
                ->unset_column('id');
				*/
			$this->datatables
                ->select($this->db->dbprefix('companies') . ".id as id, name, CONCAT(phone,'<br/>',email) as dt,(SELECT count(" . $this->db->dbprefix('sales') . ".id) FROM {$this->db->dbprefix('sales')} WHERE customer_id={$this->db->dbprefix('companies')}.id AND sale_status!='returned' $sql_add_date) as total,(SELECT COALESCE(sum(grand_total), 0) FROM {$this->db->dbprefix('sales')} WHERE customer_id={$this->db->dbprefix('companies')}.id AND sale_status!='returned' $sql_add_date) as total_amount,(SELECT COALESCE(sum(paid), 0) FROM {$this->db->dbprefix('sales')} WHERE customer_id={$this->db->dbprefix('companies')}.id AND sale_status!='returned' $sql_add_date) + (select COALESCE(sum(amount), 0) from scodeweb_payments WHERE id_ncc_id_kh={$this->db->dbprefix('companies')}.id and type='received' and amount>0 $sql_add_date) as paid,
                    ((SELECT COALESCE(sum(grand_total), 0) FROM {$this->db->dbprefix('sales')} WHERE customer_id={$this->db->dbprefix('companies')}.id AND sale_status!='returned' $sql_add_date) + nobandau - (SELECT COALESCE(sum(paid), 0) FROM {$this->db->dbprefix('sales')} WHERE customer_id={$this->db->dbprefix('companies')}.id AND sale_status!='returned' $sql_add_date) - (select COALESCE(sum(amount), 0) from scodeweb_payments WHERE id_ncc_id_kh={$this->db->dbprefix('companies')}.id and type='received' AND amount>0 $sql_add_date)) as balance,
                    (SELECT count(" . $this->db->dbprefix('sales') . ".id) FROM {$this->db->dbprefix('sales')} WHERE customer_id={$this->db->dbprefix('companies')}.id AND sale_status='returned' $sql_add_date) as slthu,
                    (SELECT COALESCE(sum(grand_total), 0) FROM {$this->db->dbprefix('sales')} WHERE customer_id={$this->db->dbprefix('companies')}.id AND sale_status='returned' $sql_add_date) as tongthu,
                    (SELECT COALESCE(sum(paid), 0) FROM {$this->db->dbprefix('sales')} WHERE customer_id={$this->db->dbprefix('companies')}.id AND sale_status='returned' $sql_add_date) as dattthu,
                    (SELECT COALESCE(sum(grand_total), 0) FROM {$this->db->dbprefix('sales')} WHERE customer_id={$this->db->dbprefix('companies')}.id AND sale_status='returned' $sql_add_date)-(SELECT COALESCE(sum(paid), 0) FROM {$this->db->dbprefix('sales')} WHERE customer_id={$this->db->dbprefix('companies')}.id AND sale_status='returned' $sql_add_date) as dunothu,
                    nobandau", FALSE)->from("companies")
                ->where('companies.group_name', 'customer')
				->add_column("Actions", "<div class='text-center'><a class=\"tip\" title='" . lang("view_report") . "' href='" . site_url('reports/customer_report/$1') . "'><span class='label label-primary'>" . lang("Xem") . "</span></a></div>", "id")
                ->unset_column('id');	

            if ($fillter==1) {
                $this->datatables->where("((SELECT COALESCE(sum(grand_total), 0) FROM {$this->db->dbprefix('sales')} WHERE customer_id={$this->db->dbprefix('companies')}.id AND sale_status!='returned') + nobandau - (SELECT COALESCE(sum(paid), 0) FROM {$this->db->dbprefix('sales')} WHERE customer_id={$this->db->dbprefix('companies')}.id AND sale_status!='returned') - (select COALESCE(sum(amount), 0) from scodeweb_payments WHERE id_ncc_id_kh={$this->db->dbprefix('companies')}.id and type='received' AND amount>0))>",0);
                               
            }else if ($fillter==2) {
                $this->datatables->where("((SELECT COALESCE(sum(grand_total), 0) FROM {$this->db->dbprefix('sales')} WHERE customer_id={$this->db->dbprefix('companies')}.id AND sale_status!='returned') + nobandau - (SELECT COALESCE(sum(paid), 0) FROM {$this->db->dbprefix('sales')} WHERE customer_id={$this->db->dbprefix('companies')}.id AND sale_status!='returned') - (select COALESCE(sum(amount), 0) from scodeweb_payments WHERE id_ncc_id_kh={$this->db->dbprefix('companies')}.id and type='received' AND amount>0))=",0);                 
            }    
            if ($start_date!=0&&$end_date!=0) {
                 $this->datatables->where("(SELECT COALESCE(count(id), 0) FROM {$this->db->dbprefix('sales')} WHERE customer_id={$this->db->dbprefix('companies')}.id $sql_add_date)>",0);                       
            }			
			//lhsoncustomer	
			//echo $this->db->get_compiled_select();
          echo $this->datatables->generate();

        }

    }

    function customer_report($user_id = NULL)
    {
        $this->sma->checkPermissions('customers', TRUE);
        if (!$user_id) {
            $this->session->set_flashdata('error', lang("no_customer_selected"));
            redirect('reports/customers');
        }

        $sales= $this->reports_model->getSalesTotals($user_id);
		$thuhoi= $this->reports_model->getThuHoiTotals($user_id);
		
		$sales->paid=$sales->paid+$this->reports_model->getSalesTotalsLhson($user_id);
		//$sales->paid=$this->reports_model->getSalesTotalsLhson($user_id);
		$this->data['sales'] =$sales;
		$this->data['thuhoi'] =$thuhoi;
        $this->data['total_sales'] = $this->reports_model->getCustomerSales($user_id);
		$this->data['total_thuhoi'] = $this->reports_model->getCustomerThuHoi($user_id);
		
        $this->data['total_quotes'] = $this->reports_model->getCustomerQuotes($user_id);
        $this->data['total_returns'] = $this->reports_model->getCustomerReturns($user_id);
        $this->data['users'] = $this->reports_model->getStaff();
        $this->data['warehouses'] = $this->site->getAllWarehouses();
        $this->data['billers'] = $this->site->getAllCompanies('biller');
		
		$khachhang = $this->companies_model->getCompanyByID($user_id); 
		$this->data['khachhang']=$khachhang;
        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');

        $this->data['user_id'] = $user_id;
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('customers_report')));
        $meta = array('page_title' => lang('customers_report'), 'bc' => $bc);
        $this->page_construct('reports/customer_report', $meta, $this->data);

    }

    function suppliers()
    {
        $this->sma->checkPermissions('suppliers');
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');

        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('suppliers_report')));
        $meta = array('page_title' => lang('suppliers_report'), 'bc' => $bc);
        $this->page_construct('reports/suppliers', $meta, $this->data);
    }

    function getSuppliers($pdf = NULL, $xls = NULL)
    {
        $this->sma->checkPermissions('suppliers', TRUE);

        if ($pdf || $xls) {

           /* $this->db->select($this->db->dbprefix('companies') . ".id as id, company, name, phone, email, count({$this->db->dbprefix('purchases')}.id) as total, COALESCE(sum(grand_total), 0) as total_amount, COALESCE(sum(paid), 0)+(select sum(amount) from scodeweb_payments WHERE id_ncc_id_kh=supplier_id and type='sent') as paid, ( COALESCE(sum(grand_total), 0) - COALESCE(sum(paid), 0)-(select sum(amount) from scodeweb_payments WHERE id_ncc_id_kh=supplier_id and type='sent')) as balance,nobandau", FALSE)
                ->from("companies")
                ->join('purchases', 'purchases.supplier_id=companies.id')
                ->where('companies.group_name', 'supplier')
                ->order_by('companies.company asc')
                ->group_by('companies.id');*/
				$this->db->select($this->db->dbprefix('companies') . ".id as id, company, name, phone, email,(SELECT count(" . $this->db->dbprefix('purchases') . ".id) FROM {$this->db->dbprefix('purchases')} WHERE supplier_id={$this->db->dbprefix('companies')}.id and status!='returned') as total,(SELECT COALESCE(sum(grand_total), 0) FROM {$this->db->dbprefix('purchases')} WHERE supplier_id={$this->db->dbprefix('companies')}.id and status!='returned') as total_amount,(SELECT COALESCE(sum(paid), 0) FROM {$this->db->dbprefix('purchases')} WHERE supplier_id={$this->db->dbprefix('companies')}.id and status!='returned') + (select COALESCE(sum(amount), 0) from scodeweb_payments WHERE id_ncc_id_kh={$this->db->dbprefix('companies')}.id and type='sent') as paid,((SELECT COALESCE(sum(grand_total), 0) FROM {$this->db->dbprefix('purchases')} WHERE supplier_id={$this->db->dbprefix('companies')}.id and status!='returned') - (SELECT COALESCE(sum(paid), 0) FROM {$this->db->dbprefix('purchases')} WHERE supplier_id={$this->db->dbprefix('companies')}.id and status!='returned') - (select COALESCE(sum(amount), 0) from scodeweb_payments WHERE id_ncc_id_kh={$this->db->dbprefix('companies')}.id and type='sent')+nobandau) as balance,(SELECT count(" . $this->db->dbprefix('purchases') . ".id) FROM {$this->db->dbprefix('purchases')} WHERE supplier_id={$this->db->dbprefix('companies')}.id and status='returned') as tongsltra,(SELECT COALESCE(sum(grand_total), 0) FROM {$this->db->dbprefix('purchases')} WHERE supplier_id={$this->db->dbprefix('companies')}.id and status='returned') as tongtientra,((SELECT COALESCE(sum(paid), 0) FROM {$this->db->dbprefix('purchases')} WHERE supplier_id={$this->db->dbprefix('companies')}.id and status='returned') + (select COALESCE(sum(amount), 0) from scodeweb_payments WHERE id_ncc_id_kh={$this->db->dbprefix('companies')}.id and type='returned')) as dattra, ((SELECT COALESCE(sum(grand_total), 0) FROM {$this->db->dbprefix('purchases')} WHERE supplier_id={$this->db->dbprefix('companies')}.id and status='returned') - (SELECT COALESCE(sum(paid), 0) FROM {$this->db->dbprefix('purchases')} WHERE supplier_id={$this->db->dbprefix('companies')}.id and status='returned') - (select COALESCE(sum(amount), 0) from scodeweb_payments WHERE id_ncc_id_kh={$this->db->dbprefix('companies')}.id and type='returned')) as congnotra,nobandau", FALSE)->from("companies")
                ->where('companies.group_name', 'supplier');

            $q = $this->db->get();
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
                $this->excel->getActiveSheet()->setTitle(lang('suppliers_report'));
                $this->excel->getActiveSheet()->SetCellValue('A1', lang('company'));
                $this->excel->getActiveSheet()->SetCellValue('B1', lang('name'));
                $this->excel->getActiveSheet()->SetCellValue('C1', lang('phone'));
                $this->excel->getActiveSheet()->SetCellValue('D1', lang('email'));
                $this->excel->getActiveSheet()->SetCellValue('E1', lang('total_purchases'));
                $this->excel->getActiveSheet()->SetCellValue('F1', lang('total_amount'));
                $this->excel->getActiveSheet()->SetCellValue('G1', lang('paid'));
				$this->excel->getActiveSheet()->SetCellValue('H1', lang('balance'));
				$this->excel->getActiveSheet()->SetCellValue('I1', lang('Tổng SL trả'));
				$this->excel->getActiveSheet()->SetCellValue('J1', lang('Tổng tiền trả'));
				$this->excel->getActiveSheet()->SetCellValue('K1', lang('Đã TT Trả'));
				$this->excel->getActiveSheet()->SetCellValue('L1', lang('Dư nợ trả'));
				$this->excel->getActiveSheet()->SetCellValue('M1', lang('Nợ ban đầu'));
                

                $row = 2;
                foreach ($data as $data_row) {
                    $this->excel->getActiveSheet()->SetCellValue('A' . $row, $data_row->company);
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->name);
                    $this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->phone);
                    $this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->email);
                    $this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->total);
                    $this->excel->getActiveSheet()->SetCellValue('F' . $row, $data_row->total_amount);
                    $this->excel->getActiveSheet()->SetCellValue('G' . $row, $data_row->paid);
                    $this->excel->getActiveSheet()->SetCellValue('H' . $row, $data_row->balance);
					$this->excel->getActiveSheet()->SetCellValue('I' . $row, $data_row->tongsltra);
					$this->excel->getActiveSheet()->SetCellValue('J' . $row, $data_row->tongtientra);
					$this->excel->getActiveSheet()->SetCellValue('K' . $row, $data_row->dattra);
					$this->excel->getActiveSheet()->SetCellValue('L' . $row, $data_row->congnotra);
					$this->excel->getActiveSheet()->SetCellValue('M' . $row, $data_row->nobandau);
					
                    $row++;
                }

                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('J')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('K')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('L')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('M')->setWidth(15);
                $filename = 'suppliers_report';
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

        } else {

			/*
           $p = "( SELECT supplier_id, count(" . $this->db->dbprefix('purchases') . ".id) as total, COALESCE(sum(grand_total), 0) as total_amount, COALESCE(sum(paid), 0)+(select sum(amount) from scodeweb_payments WHERE id_ncc_id_kh=supplier_id and type='sent') as paid, ( COALESCE(sum(grand_total), 0) - COALESCE(sum(paid), 0)-(select sum(amount) from scodeweb_payments WHERE id_ncc_id_kh=supplier_id and type='sent')) as balance from {$this->db->dbprefix('purchases')} GROUP BY {$this->db->dbprefix('purchases')}.supplier_id ) FP";

            $this->load->library('datatables');
			
            $this->datatables
                ->select($this->db->dbprefix('companies') . ".id as id, company, name, phone, email, FP.total, FP.total_amount, FP.paid, FP.balance,nobandau", FALSE)
                ->from("companies")
                ->join($p, 'FP.supplier_id=companies.id')
                ->where('companies.group_name', 'supplier')
                ->group_by('companies.id')
               ->add_column("Actions", "<div class='text-center'><a class=\"tip\" title='" . lang("view_report") . "' href='" . site_url('reports/supplier_report/$1') . "'><span class='label label-primary'>" . lang("view_report") . "</span></a></div>", "id")
                ->unset_column('id');	
			*/

            $this->load->library('datatables');
			
            $this->datatables
                ->select($this->db->dbprefix('companies') . ".id as id, CONCAT(company,'<br/>',name),CONCAT(phone,'<br/>', email) as phone,(SELECT count(" . $this->db->dbprefix('purchases') . ".id) FROM {$this->db->dbprefix('purchases')} WHERE supplier_id={$this->db->dbprefix('companies')}.id and status!='returned') as total,(SELECT COALESCE(sum(grand_total), 0) FROM {$this->db->dbprefix('purchases')} WHERE supplier_id={$this->db->dbprefix('companies')}.id and status!='returned') as total_amount,(SELECT COALESCE(sum(paid), 0) FROM {$this->db->dbprefix('purchases')} WHERE supplier_id={$this->db->dbprefix('companies')}.id and status!='returned') + (select COALESCE(sum(amount), 0) from scodeweb_payments WHERE id_ncc_id_kh={$this->db->dbprefix('companies')}.id and type='sent') as paid,((SELECT COALESCE(sum(grand_total), 0) FROM {$this->db->dbprefix('purchases')} WHERE supplier_id={$this->db->dbprefix('companies')}.id and status!='returned') - (SELECT COALESCE(sum(paid), 0) FROM {$this->db->dbprefix('purchases')} WHERE supplier_id={$this->db->dbprefix('companies')}.id and status!='returned') - (select COALESCE(sum(amount), 0) from scodeweb_payments WHERE id_ncc_id_kh={$this->db->dbprefix('companies')}.id and type='sent')+nobandau) as balance,(SELECT count(" . $this->db->dbprefix('purchases') . ".id) FROM {$this->db->dbprefix('purchases')} WHERE supplier_id={$this->db->dbprefix('companies')}.id and status='returned') as sltra,(SELECT COALESCE(sum(grand_total), 0) FROM {$this->db->dbprefix('purchases')} WHERE supplier_id={$this->db->dbprefix('companies')}.id and status='returned') as tongtientra, ((SELECT COALESCE(sum(paid), 0) FROM {$this->db->dbprefix('purchases')} WHERE supplier_id={$this->db->dbprefix('companies')}.id and status='returned') + (select COALESCE(sum(amount), 0) from scodeweb_payments WHERE id_ncc_id_kh={$this->db->dbprefix('companies')}.id and type='returned')) as tttra,((SELECT COALESCE(sum(grand_total), 0) FROM {$this->db->dbprefix('purchases')} WHERE supplier_id={$this->db->dbprefix('companies')}.id and status='returned') - (SELECT COALESCE(sum(paid), 0) FROM {$this->db->dbprefix('purchases')} WHERE supplier_id={$this->db->dbprefix('companies')}.id and status='returned') - (select COALESCE(sum(amount), 0) from scodeweb_payments WHERE id_ncc_id_kh={$this->db->dbprefix('companies')}.id and type='returned')) as dunotra,nobandau", FALSE)->from("companies")
                ->where('companies.group_name', 'supplier')
              ->add_column("Actions", "<div class='text-center'><a class=\"tip\" title='" . lang("view_report") . "' href='" . site_url('reports/supplier_report/$1') . "'><span class='label label-primary'>" . lang("Xem") . "</span></a></div>", "id")->unset_column('id');
			//echo $this->db->get_compiled_select();		
			//lhson code 24/03/2018
           echo $this->datatables->generate();

        }

    }

    function supplier_report($user_id = NULL)
    {
        $this->sma->checkPermissions('suppliers', TRUE);
        if (!$user_id) {
            $this->session->set_flashdata('error', lang("no_supplier_selected"));
            redirect('reports/suppliers');
        }
		$company_details = $this->companies_model->getCompanyByID($user_id);		 
		
		$purchases=$this->reports_model->getPurchasesTotals($user_id);	
		$purchases->paid=$purchases->paid+$this->reports_model->getPurchasesTotalsLhson($user_id);		
		$this->data['purchases'] = $purchases;
		
		$returned=$this->reports_model->getPurchasesTotalsReturn($user_id);	
		$returned->paid=$returned->paid+$this->reports_model->getPurchasesTotalsLhsonReturn($user_id);		
		$this->data['returned'] = $returned;
		
		$this->data['nobandau']=$company_details->nobandau;
        $this->data['total_purchases'] = $this->reports_model->getSupplierPurchases($user_id);
		$this->data['total_return'] = $this->reports_model->getSupplierPurchasesReturn($user_id);
		
        $this->data['users'] = $this->reports_model->getStaff();
        $this->data['warehouses'] = $this->site->getAllWarehouses();
		
        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');

        $this->data['user_id'] = $user_id;
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('suppliers_report')));
        $meta = array('page_title' => lang('suppliers_report'), 'bc' => $bc);
        $this->page_construct('reports/supplier_report', $meta, $this->data);

    }

    function users()
    {
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('staff_report')));
        $meta = array('page_title' => lang('staff_report'), 'bc' => $bc);
        $this->page_construct('reports/users', $meta, $this->data);
    }

    function getUsers()
    {
        $this->load->library('datatables');
        $this->datatables
            ->select($this->db->dbprefix('users').".id as id, first_name, last_name, email, company, ".$this->db->dbprefix('groups').".name, active")
            ->from("users")
            ->join('groups', 'users.group_id=groups.id', 'left')
            ->group_by('users.id')
            ->where('company_id', NULL);
        if (!$this->Owner) {
            $this->datatables->where('group_id !=', 1);
        }
        $this->datatables
            ->edit_column('active', '$1__$2', 'active, id')
            ->add_column("Actions", "<div class='text-center'><a class=\"tip\" title='" . lang("view_report") . "' href='" . site_url('reports/staff_report/$1') . "'><span class='label label-primary'>" . lang("view_report") . "</span></a></div>", "id")
            ->unset_column('id');
        echo $this->datatables->generate();
    }

    function staff_report($user_id = NULL, $year = NULL, $month = NULL, $pdf = NULL, $cal = 0)
    {

        if (!$user_id) {
            $this->session->set_flashdata('error', lang("no_user_selected"));
            redirect('reports/users');
        }
        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
        $this->data['purchases'] = $this->reports_model->getStaffPurchases($user_id);
        $this->data['sales'] = $this->reports_model->getStaffSales($user_id);
        $this->data['billers'] = $this->site->getAllCompanies('biller');
        $this->data['warehouses'] = $this->site->getAllWarehouses();

        if (!$year) {
            $year = date('Y');
        }
        if (!$month || $month == '#monthly-con') {
            $month = date('m');
        }
        if ($pdf) {
            if ($cal) {
                $this->monthly_sales($year, $pdf, $user_id);
            } else {
                $this->daily_sales($year, $month, $pdf, $user_id);
            }
        }
        $config = array(
            'show_next_prev' => TRUE,
            'next_prev_url' => site_url('reports/staff_report/'.$user_id),
            'month_type' => 'long',
            'day_type' => 'long'
        );

        $config['template'] = '{table_open}<div class="table-responsive"><table border="0" cellpadding="0" cellspacing="0" class="table table-bordered dfTable reports-table">{/table_open}
		{heading_row_start}<tr>{/heading_row_start}
		{heading_previous_cell}<th class="text-center"><a href="{previous_url}">&lt;&lt;</a></th>{/heading_previous_cell}
		{heading_title_cell}<th class="text-center" colspan="{colspan}" id="month_year">{heading}</th>{/heading_title_cell}
		{heading_next_cell}<th class="text-center"><a href="{next_url}">&gt;&gt;</a></th>{/heading_next_cell}
		{heading_row_end}</tr>{/heading_row_end}
		{week_row_start}<tr>{/week_row_start}
		{week_day_cell}<td class="cl_wday">{week_day}</td>{/week_day_cell}
		{week_row_end}</tr>{/week_row_end}
		{cal_row_start}<tr class="days">{/cal_row_start}
		{cal_cell_start}<td class="day">{/cal_cell_start}
		{cal_cell_content}
		<div class="day_num">{day}</div>
		<div class="content">{content}</div>
		{/cal_cell_content}
		{cal_cell_content_today}
		<div class="day_num highlight">{day}</div>
		<div class="content">{content}</div>
		{/cal_cell_content_today}
		{cal_cell_no_content}<div class="day_num">{day}</div>{/cal_cell_no_content}
		{cal_cell_no_content_today}<div class="day_num highlight">{day}</div>{/cal_cell_no_content_today}
		{cal_cell_blank}&nbsp;{/cal_cell_blank}
		{cal_cell_end}</td>{/cal_cell_end}
		{cal_row_end}</tr>{/cal_row_end}
		{table_close}</table></div>{/table_close}';

        $this->load->library('calendar', $config);
        $sales = $this->reports_model->getStaffDailySales($user_id, $year, $month);

        if (!empty($sales)) {
            foreach ($sales as $sale) {
                $daily_sale[$sale->date] = "<table class='table table-bordered table-hover table-striped table-condensed data' style='margin:0;'><tr><td>" . lang("discount") . "</td><td>" . $this->sma->formatMoney($sale->discount) . "</td></tr><tr><td>" . lang("product_tax") . "</td><td>" . $this->sma->formatMoney($sale->tax1) . "</td></tr><tr><td>" . lang("order_tax") . "</td><td>" . $this->sma->formatMoney($sale->tax2) . "</td></tr><tr><td>" . lang("total") . "</td><td>" . $this->sma->formatMoney($sale->total) . "</td></tr></table>";
            }
        } else {
            $daily_sale = array();
        }
        $this->data['calender'] = $this->calendar->generate($year, $month, $daily_sale);
        if ($this->input->get('pdf')) {

        }
        $this->data['year'] = $year;
        $this->data['month'] = $month;
        $this->data['msales'] = $this->reports_model->getStaffMonthlySales($user_id, $year);
        $this->data['user_id'] = $user_id;
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('staff_report')));
        $meta = array('page_title' => lang('staff_report'), 'bc' => $bc);
        $this->page_construct('reports/staff_report', $meta, $this->data);

    }

    function getUserLogins($id = NULL, $pdf = NULL, $xls = NULL)
    {
        if ($this->input->get('start_date')) {
            $login_start_date = $this->input->get('start_date');
        } else {
            $login_start_date = NULL;
        }
        if ($this->input->get('end_date')) {
            $login_end_date = $this->input->get('end_date');
        } else {
            $login_end_date = NULL;
        }
        if ($login_start_date) {
            $login_start_date = $this->sma->fld($login_start_date);
            $login_end_date = $login_end_date ? $this->sma->fld($login_end_date) : date('Y-m-d H:i:s');
        }
        if ($pdf || $xls) {

            $this->db->select("login, ip_address, time")
                ->from("user_logins")
                ->where('user_id', $id)
                ->order_by('time desc');
            if ($login_start_date) {
                $this->db->where("time BETWEEN '{$login_start_date}' and '{$login_end_date}'", NULL, FALSE);
            }

            $q = $this->db->get();
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
                $this->excel->getActiveSheet()->setTitle(lang('staff_login_report'));
                $this->excel->getActiveSheet()->SetCellValue('A1', lang('email'));
                $this->excel->getActiveSheet()->SetCellValue('B1', lang('ip_address'));
                $this->excel->getActiveSheet()->SetCellValue('C1', lang('time'));

                $row = 2;
                foreach ($data as $data_row) {
                    $this->excel->getActiveSheet()->SetCellValue('A' . $row, $data_row->login);
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->ip_address);
                    $this->excel->getActiveSheet()->SetCellValue('C' . $row, $this->sma->hrld($data_row->time));
                    $row++;
                }

                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(35);
                $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(35);
                $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(35);

                $filename = 'staff_login_report';
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
                    $this->excel->getActiveSheet()->getStyle('C2:C' . $row)->getAlignment()->setWrapText(true);
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

        } else {

            $this->load->library('datatables');
            $this->datatables
                ->select("login, ip_address, DATE_FORMAT(time, '%Y-%m-%d %T') as time")
                ->from("user_logins")
                ->where('user_id', $id);
            if ($login_start_date) {
                $this->datatables->where("time BETWEEN '{$login_start_date}' and '{$login_end_date}'", NULL, FALSE);
            }
            echo $this->datatables->generate();

        }

    }

    function getCustomerLogins($id = NULL)
    {
        if ($this->input->get('login_start_date')) {
            $login_start_date = $this->input->get('login_start_date');
        } else {
            $login_start_date = NULL;
        }
        if ($this->input->get('login_end_date')) {
            $login_end_date = $this->input->get('login_end_date');
        } else {
            $login_end_date = NULL;
        }
        if ($login_start_date) {
            $login_start_date = $this->sma->fld($login_start_date);
            $login_end_date = $login_end_date ? $this->sma->fld($login_end_date) : date('Y-m-d H:i:s');
        }
        $this->load->library('datatables');
        $this->datatables
            ->select("login, ip_address, time")
            ->from("user_logins")
            ->where('customer_id', $id);
        if ($login_start_date) {
            $this->datatables->where('time BETWEEN "' . $login_start_date . '" and "' . $login_end_date . '"');
        }
        echo $this->datatables->generate();
    }

    function profit_loss($start_date = NULL, $end_date = NULL)
    {
        $this->sma->checkPermissions('profit_loss');
        if (!$start_date) {
            $start = $this->db->escape(date('Y-m-d').' 00:00');
            $start_date = date('Y-m-d');
        } else {
            $start = $this->db->escape(urldecode($start_date));
        }
        if (!$end_date) {
            $end = $this->db->escape(date('Y-m-d').' 23:59');
            $end_date = date('Y-m-d');
        } else {
            $end = $this->db->escape(urldecode($end_date));
        }

        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');

        $this->data['total_purchases'] = $this->reports_model->getTotalPurchases($start, $end);
        $this->data['total_sales'] = $this->reports_model->getTotalSales($start, $end);
        $this->data['total_expenses'] = $this->reports_model->getTotalExpenses($start, $end);
        $this->data['total_paid'] = $this->reports_model->getTotalPaidAmount($start, $end);
        
		$this->data['total_received'] = $this->reports_model->getTotalReceivedAmount($start, $end);
		$this->data['total_thukhac'] = $this->reports_model->getTotalDoanhthukhac($start, $end);
		
        $this->data['total_received_cash'] = $this->reports_model->getTotalReceivedCashAmount($start, $end);
        $this->data['total_received_cc'] = $this->reports_model->getTotalReceivedCCAmount($start, $end);
        $this->data['total_received_cheque'] = $this->reports_model->getTotalReceivedChequeAmount($start, $end);
        $this->data['total_received_ppp'] = $this->reports_model->getTotalReceivedPPPAmount($start, $end);
        $this->data['total_received_stripe'] = $this->reports_model->getTotalReceivedStripeAmount($start, $end);
        $this->data['total_returned'] = $this->reports_model->getTotalReturnedAmount($start, $end);
        $this->data['start'] = urldecode($start_date);
        $this->data['end'] = urldecode($end_date);

        $warehouses = $this->site->getAllWarehouses();
        foreach ($warehouses as $warehouse) {
            $total_purchases = $this->reports_model->getTotalPurchases($start, $end, $warehouse->id);
            $total_sales = $this->reports_model->getTotalSales($start, $end, $warehouse->id);
			$total_thu_khac= $this->reports_model->getTotalDoanhthukhac($start, $end, $warehouse->id);
            $total_expenses = $this->reports_model->getTotalExpenses($start, $end, $warehouse->id);
            $warehouses_report[] = array(
                'warehouse' => $warehouse,
                'total_purchases' => $total_purchases,
                'total_sales' => $total_sales,
                'total_expenses' => $total_expenses,
				'total_doanhthu_khac'=>$total_thu_khac,
                );
        }
        $this->data['warehouses_report'] = $warehouses_report;

        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('profit_loss')));
        $meta = array('page_title' => lang('profit_loss'), 'bc' => $bc);
        $this->page_construct('reports/profit_loss', $meta, $this->data);
    }

    function profit_loss_pdf($start_date = NULL, $end_date = NULL)
    {
        $this->sma->checkPermissions('profit_loss');
        if (!$start_date) {
            $start = $this->db->escape(date('Y-m') . '-1');
            $start_date = date('Y-m') . '-1';
        } else {
            $start = $this->db->escape(urldecode($start_date));
        }
        if (!$end_date) {
            $end = $this->db->escape(date('Y-m-d H:i'));
            $end_date = date('Y-m-d H:i');
        } else {
            $end = $this->db->escape(urldecode($end_date));
        }

        $this->data['total_purchases'] = $this->reports_model->getTotalPurchases($start, $end);
        $this->data['total_sales'] = $this->reports_model->getTotalSales($start, $end);
        $this->data['total_expenses'] = $this->reports_model->getTotalExpenses($start, $end);
        $this->data['total_paid'] = $this->reports_model->getTotalPaidAmount($start, $end);
        $this->data['total_received'] = $this->reports_model->getTotalReceivedAmount($start, $end);
		$this->data['total_thukhac'] = $this->reports_model->getTotalDoanhthukhac($start, $end);
        $this->data['total_received_cash'] = $this->reports_model->getTotalReceivedCashAmount($start, $end);
        $this->data['total_received_cc'] = $this->reports_model->getTotalReceivedCCAmount($start, $end);
        $this->data['total_received_cheque'] = $this->reports_model->getTotalReceivedChequeAmount($start, $end);
        $this->data['total_received_ppp'] = $this->reports_model->getTotalReceivedPPPAmount($start, $end);
        $this->data['total_received_stripe'] = $this->reports_model->getTotalReceivedStripeAmount($start, $end);
        $this->data['total_returned'] = $this->reports_model->getTotalReturnedAmount($start, $end);
        $this->data['start'] = urldecode($start_date);
        $this->data['end'] = urldecode($end_date);

        $warehouses = $this->site->getAllWarehouses();
        foreach ($warehouses as $warehouse) {
            $total_purchases = $this->reports_model->getTotalPurchases($start, $end, $warehouse->id);
            $total_sales = $this->reports_model->getTotalSales($start, $end, $warehouse->id);
            $warehouses_report[] = array(
                'warehouse' => $warehouse,
                'total_purchases' => $total_purchases,
                'total_sales' => $total_sales,
                );
        }
        $this->data['warehouses_report'] = $warehouses_report;

        $html = $this->load->view($this->theme . 'reports/profit_loss_pdf', $this->data, true);
        $name = lang("profit_loss") . "-" . str_replace(array('-', ' ', ':'), '_', $this->data['start']) . "-" . str_replace(array('-', ' ', ':'), '_', $this->data['end']) . ".pdf";
        $this->sma->generate_pdf($html, $name, false, false, false, false, false, 'L');
    }

    function register()
    {
        $this->sma->checkPermissions('register');
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['users'] = $this->reports_model->getStaff();
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('register_report')));
        $meta = array('page_title' => lang('register_report'), 'bc' => $bc);
        $this->page_construct('reports/register', $meta, $this->data);
    }

    function getRrgisterlogs($pdf = NULL, $xls = NULL)
    {
        $this->sma->checkPermissions('register', TRUE);
        if ($this->input->get('user')) {
            $user = $this->input->get('user');
        } else {
            $user = NULL;
        }
        if ($this->input->get('start_date')) {
            $start_date = $this->input->get('start_date');
        } else {
            $start_date = NULL;
        }
        if ($this->input->get('end_date')) {
            $end_date = $this->input->get('end_date');
        } else {
            $end_date = NULL;
        }
        if ($start_date) {
            $start_date = $this->sma->fld($start_date);
            $end_date = $this->sma->fld($end_date);
        }

        if ($pdf || $xls) {

            $this->db->select("date, closed_at, CONCAT(" . $this->db->dbprefix('users') . ".first_name, ' ', " . $this->db->dbprefix('users') . ".last_name, ' (', users.email, ')') as user, cash_in_hand, total_cc_slips, total_cheques, total_cash, total_cc_slips_submitted, total_cheques_submitted,total_cash_submitted, note", FALSE)
                ->from("pos_register")
                ->join('users', 'users.id=pos_register.user_id', 'left')
                ->order_by('date desc');
            //->where('status', 'close');

            if ($user) {
                $this->db->where('pos_register.user_id', $user);
            }
            if ($start_date) {
                $this->db->where('date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
            }

            $q = $this->db->get();
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
                $this->excel->getActiveSheet()->setTitle(lang('register_report'));
                $this->excel->getActiveSheet()->SetCellValue('A1', lang('open_time'));
                $this->excel->getActiveSheet()->SetCellValue('B1', lang('close_time'));
                $this->excel->getActiveSheet()->SetCellValue('C1', lang('user'));
                $this->excel->getActiveSheet()->SetCellValue('D1', lang('cash_in_hand'));
                $this->excel->getActiveSheet()->SetCellValue('E1', lang('cc_slips'));
                $this->excel->getActiveSheet()->SetCellValue('F1', lang('cheques'));
                $this->excel->getActiveSheet()->SetCellValue('G1', lang('total_cash'));
                $this->excel->getActiveSheet()->SetCellValue('H1', lang('cc_slips_submitted'));
                $this->excel->getActiveSheet()->SetCellValue('I1', lang('cheques_submitted'));
                $this->excel->getActiveSheet()->SetCellValue('J1', lang('total_cash_submitted'));
                $this->excel->getActiveSheet()->SetCellValue('K1', lang('note'));

                $row = 2;
                foreach ($data as $data_row) {
                    $this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->sma->hrld($data_row->date));
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->closed_at);
                    $this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->user);
                    $this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->cash_in_hand);
                    $this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->total_cc_slips);
                    $this->excel->getActiveSheet()->SetCellValue('F' . $row, $data_row->total_cheques);
                    $this->excel->getActiveSheet()->SetCellValue('G' . $row, $data_row->total_cash);
                    $this->excel->getActiveSheet()->SetCellValue('H' . $row, $data_row->total_cc_slips_submitted);
                    $this->excel->getActiveSheet()->SetCellValue('I' . $row, $data_row->total_cheques_submitted);
                    $this->excel->getActiveSheet()->SetCellValue('J' . $row, $data_row->total_cash_submitted);
                    $this->excel->getActiveSheet()->SetCellValue('K' . $row, $data_row->note);
                    if($data_row->total_cash_submitted < $data_row->total_cash || $data_row->total_cheques_submitted < $data_row->total_cheques || $data_row->total_cc_slips_submitted < $data_row->total_cc_slips) {
                        $this->excel->getActiveSheet()->getStyle('A'.$row.':K'.$row)->applyFromArray(
                                array( 'fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID, 'color' => array('rgb' => 'F2DEDE')) )
                                );
                    }
                    $row++;
                }

                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('J')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('K')->setWidth(35);
                $filename = 'register_report';
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

        } else {

            $this->load->library('datatables');
            $this->datatables
                ->select("date, closed_at, CONCAT(" . $this->db->dbprefix('users') . ".first_name, ' ', " . $this->db->dbprefix('users') . ".last_name, '<br>', " . $this->db->dbprefix('users') . ".email) as user, cash_in_hand, CONCAT(total_cc_slips, ' (', total_cc_slips_submitted, ')'), CONCAT(total_cheques, ' (', total_cheques_submitted, ')'), CONCAT(total_cash, ' (', total_cash_submitted, ')'), note", FALSE)
                ->from("pos_register")
                ->join('users', 'users.id=pos_register.user_id', 'left');

            if ($user) {
                $this->datatables->where('pos_register.user_id', $user);
            }
            if ($start_date) {
                $this->datatables->where('date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
            }

            echo $this->datatables->generate();

        }

    }

    public function expenses($id = null)
    {
        $this->sma->checkPermissions();
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['users'] = $this->reports_model->getStaff();
        $this->data['categories'] = $this->reports_model->getExpenseCategories();
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('expenses')));
        $meta = array('page_title' => lang('expenses'), 'bc' => $bc);
        $this->page_construct('reports/expenses', $meta, $this->data);
    }

    public function getExpensesReport($pdf = NULL, $xls = NULL)
    {
        $this->sma->checkPermissions('expenses');

        $reference_no = $this->input->get('reference_no') ? $this->input->get('reference_no') : NULL;
        $category = $this->input->get('category') ? $this->input->get('category') : NULL;
        $note = $this->input->get('note') ? $this->input->get('note') : NULL;
        $user = $this->input->get('user') ? $this->input->get('user') : NULL;
        $start_date = $this->input->get('start_date') ? $this->input->get('start_date') : NULL;
        $end_date = $this->input->get('end_date') ? $this->input->get('end_date') : NULL;

        if ($start_date) {
            $start_date = $this->sma->fld($start_date);
            $end_date = $this->sma->fld($end_date);
        }
		$locdk='';
        if ($pdf || $xls) {

            $this->db->select("date, reference,CONCAT({$this->db->dbprefix('users')}.first_name, ' ', {$this->db->dbprefix('users')}.last_name) as user,(CASE WHEN customer_id>0 THEN (SELECT CONCAT({$this->db->dbprefix('companies')}.name, ' ', {$this->db->dbprefix('companies')}.phone) FROM {$this->db->dbprefix('companies')} WHERE id=customer_id) WHEN doitac>0 THEN (SELECT CONCAT({$this->db->dbprefix('doitac')}.code, '-', {$this->db->dbprefix('doitac')}.name) FROM {$this->db->dbprefix('doitac')} WHERE id=doitac) ELSE (SELECT CONCAT({$this->db->dbprefix('users')}.first_name, ' ', {$this->db->dbprefix('users')}.last_name) FROM {$this->db->dbprefix('users')} WHERE id=nhanvien_id) END) as nhanvien, {$this->db->dbprefix('expense_categories')}.name as category, amount,paid_by, note, attachment, {$this->db->dbprefix('expenses')}.id as id,created_by", false)
            ->from('expenses')
            ->join('users', 'users.id=expenses.created_by', 'left')
            ->join('expense_categories', 'expense_categories.id=expenses.category_id', 'left')
            ->group_by('expenses.id');
			
			

            if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
                $this->db->where('created_by', $this->session->userdata('user_id'));
            }

            if ($note) {
                //$this->db->like('note', $note, 'both');
				$locdk.="AND note like '%".$note."%'";
            }
            if ($reference_no) {
                //$this->db->like('reference', $reference_no, 'both');
				$locdk.="AND reference like '%".$reference_no."%'";
            }
            if ($category) {
              //  $this->db->where('category_id', $category);
				$locdk.="AND category_id='".$category."'";
            }
            if ($user) {
                //$this->db->where('created_by', $user);
				$locdk.="AND created_by='".$user."'";
            }
            if ($start_date) {
                //$this->db->where('date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
				$locdk.='AND date BETWEEN "' . $start_date . '" and "' . $end_date . '"';
            }
			
			$chikhac_query=$this->db->get_compiled_select();
			
			$chi_ncc_query="SELECT date,reference_no as reference,(SELECT CONCAT({$this->db->dbprefix('users')}.first_name, ' ', {$this->db->dbprefix('users')}.last_name) FROM {$this->db->dbprefix('users')} WHERE id=created_by) as user,(SELECT CONCAT({$this->db->dbprefix('companies')}.name, ' ', {$this->db->dbprefix('companies')}.phone) FROM {$this->db->dbprefix('companies')} WHERE id=id_ncc_id_kh) as nhanvien,'Chi NCC' as category,amount,paid_by,note,attachment,CONCAT('pay','_',id),created_by FROM ".$this->db->dbprefix('payments')." WHERE type='sent'";
			
			$query_ok="SELECT tbl.* FROM ($chikhac_query UNION $chi_ncc_query) as tbl WHERE id!='' $locdk ORDER BY tbl.date desc";
			
            $q = $this->db->query($query_ok);
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
                $this->excel->getActiveSheet()->setTitle(lang('expenses_report'));
                $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                $this->excel->getActiveSheet()->SetCellValue('B1', lang('reference_no'));
                $this->excel->getActiveSheet()->SetCellValue('C1', lang('Nhân viên'));
				$this->excel->getActiveSheet()->SetCellValue('D1', lang('Đối tượng'));
				$this->excel->getActiveSheet()->SetCellValue('E1', lang('category'));
                $this->excel->getActiveSheet()->SetCellValue('F1', lang('amount'));
				$this->excel->getActiveSheet()->SetCellValue('G1', lang('paid_by'));
                $this->excel->getActiveSheet()->SetCellValue('H1', lang('note'));
                $this->excel->getActiveSheet()->SetCellValue('I1', lang('created_by'));

                $row = 2; $total = 0;
                foreach ($data as $data_row) {
                    $this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->sma->hrld($data_row->date));
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->reference);
                    $this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->user);
					$this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->nhanvien);
					$this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->category);
                    $this->excel->getActiveSheet()->SetCellValue('F' . $row, $data_row->amount);
					$this->excel->getActiveSheet()->SetCellValue('G' . $row, lang($data_row->paid_by));
                    $this->excel->getActiveSheet()->SetCellValue('H' . $row, $data_row->note);
                    $this->excel->getActiveSheet()->SetCellValue('I' . $row, $data_row->created_by);
                    $total += $data_row->amount;
                    $row++;
                }
                $this->excel->getActiveSheet()->getStyle("F" . $row)->getBorders()
                    ->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);
                $this->excel->getActiveSheet()->SetCellValue('F' . $row, $total);

                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(35);
                $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(25);

                $filename = 'ChiPhi_report';
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

        } else {

            $this->load->library('datatables');
            $this->db->select("DATE_FORMAT(date, '%Y-%m-%d %T') as date, reference, CONCAT({$this->db->dbprefix('users')}.first_name, ' ', {$this->db->dbprefix('users')}.last_name) as user,c_name,c_phone,c_address,{$this->db->dbprefix('expense_categories')}.name as category, amount,paid_by, note, attachment, {$this->db->dbprefix('expenses')}.id as id,created_by", false)
            ->from('expenses')
            ->join('users', 'users.id=expenses.created_by', 'left')
            ->join('expense_categories', 'expense_categories.id=expenses.category_id', 'left')
            ->group_by('expenses.id');

            if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
                $this->db->where('created_by', $this->session->userdata('user_id'));
            }

            if ($note) {
                //$this->db->like('note', $note, 'both');
				$locdk.="AND note like '%".$note."%'";
            }
            if ($reference_no) {
                //$this->db->like('reference', $reference_no, 'both');
				$locdk.="AND reference like '%".$reference_no."%'";
            }
            if ($category) {
                //$this->db->where('category_id', $category);
				$locdk.="AND category_id='".$category."'";
            }
            if ($user) {
                //$this->db->where('created_by', $user);
				$locdk.="AND created_by='".$user."'";
            }
            if ($start_date) {
                //$this->db->where('date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
				$locdk.='AND date BETWEEN "' . $start_date . '" and "' . $end_date . '"';
            }
			
			$chikhac_query=$this->db->get_compiled_select();
			
			$chi_ncc_query="SELECT date,reference_no as reference,(SELECT CONCAT({$this->db->dbprefix('users')}.first_name, ' ', {$this->db->dbprefix('users')}.last_name) FROM {$this->db->dbprefix('users')} WHERE id=created_by) as user,c_name,c_phone,c_address,'Chi NCC' as category,amount,paid_by,note,attachment,CONCAT('pay','_',id),created_by FROM ".$this->db->dbprefix('payments')." WHERE type='sent'";
			
			$query_ok="SELECT tbl.* FROM ($chikhac_query UNION $chi_ncc_query) as tbl WHERE id!='' $locdk ORDER BY tbl.date desc";
		
			echo $this->datatables->generate_querylhson($output = 'json', $charset = 'UTF-8',$query_ok);

           // echo $this->datatables->generate();
        }
    }

    function daily_purchases($warehouse_id = NULL, $year = NULL, $month = NULL, $pdf = NULL, $user_id = NULL)
    {
        $this->sma->checkPermissions();
        if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
            $warehouse_id = $this->session->userdata('warehouse_id');
        }
        if (!$year) {
            $year = date('Y');
        }
        if (!$month) {
            $month = date('m');
        }
        if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $user_id = $this->session->userdata('user_id');
        }
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $config = array(
            'show_next_prev' => TRUE,
            'next_prev_url' => site_url('reports/daily_purchases/'.($warehouse_id ? $warehouse_id : 0)),
            'month_type' => 'long',
            'day_type' => 'long'
        );

        $config['template'] = '{table_open}<div class="table-responsive"><table border="0" cellpadding="0" cellspacing="0" class="table table-bordered dfTable">{/table_open}
        {heading_row_start}<tr>{/heading_row_start}
        {heading_previous_cell}<th><a href="{previous_url}">&lt;&lt;</a></th>{/heading_previous_cell}
        {heading_title_cell}<th colspan="{colspan}" id="month_year">{heading}</th>{/heading_title_cell}
        {heading_next_cell}<th><a href="{next_url}">&gt;&gt;</a></th>{/heading_next_cell}
        {heading_row_end}</tr>{/heading_row_end}
        {week_row_start}<tr>{/week_row_start}
        {week_day_cell}<td class="cl_wday">{week_day}</td>{/week_day_cell}
        {week_row_end}</tr>{/week_row_end}
        {cal_row_start}<tr class="days">{/cal_row_start}
        {cal_cell_start}<td class="day">{/cal_cell_start}
        {cal_cell_content}
        <div class="day_num">{day}</div>
        <div class="content">{content}</div>
        {/cal_cell_content}
        {cal_cell_content_today}
        <div class="day_num highlight">{day}</div>
        <div class="content">{content}</div>
        {/cal_cell_content_today}
        {cal_cell_no_content}<div class="day_num">{day}</div>{/cal_cell_no_content}
        {cal_cell_no_content_today}<div class="day_num highlight">{day}</div>{/cal_cell_no_content_today}
        {cal_cell_blank}&nbsp;{/cal_cell_blank}
        {cal_cell_end}</td>{/cal_cell_end}
        {cal_row_end}</tr>{/cal_row_end}
        {table_close}</table></div>{/table_close}';

        $this->load->library('calendar', $config);
        $purchases = $user_id ? $this->reports_model->getStaffDailyPurchases($user_id, $year, $month, $warehouse_id) : $this->reports_model->getDailyPurchases($year, $month, $warehouse_id);

        if (!empty($purchases)) {
            foreach ($purchases as $purchase) {
                $daily_purchase[$purchase->date] = "<table class='table table-bordered table-hover table-striped table-condensed data' style='margin:0;'><tr><td>" . lang("discount") . "</td><td>" . $this->sma->formatMoney($purchase->discount) . "</td></tr><tr><td>" . lang("shipping") . "</td><td>" . $this->sma->formatMoney($purchase->shipping) . "</td></tr><tr><td>" . lang("product_tax") . "</td><td>" . $this->sma->formatMoney($purchase->tax1) . "</td></tr><tr><td>" . lang("order_tax") . "</td><td>" . $this->sma->formatMoney($purchase->tax2) . "</td></tr><tr><td>" . lang("total") . "</td><td>" . $this->sma->formatMoney($purchase->total) . "</td></tr></table>";
            }
        } else {
            $daily_purchase = array();
        }

        $this->data['calender'] = $this->calendar->generate($year, $month, $daily_purchase);
        $this->data['year'] = $year;
        $this->data['month'] = $month;
        if ($pdf) {
            $html = $this->load->view($this->theme . 'reports/daily', $this->data, true);
            $name = lang("daily_purchases") . "_" . $year . "_" . $month . ".pdf";
            $html = str_replace('<p class="introtext">' . lang("reports_calendar_text") . '</p>', '', $html);
            $this->sma->generate_pdf($html, $name, null, null, null, null, null, 'L');
        }
        $this->data['warehouses'] = $this->site->getAllWarehouses();
        $this->data['warehouse_id'] = $warehouse_id;
        $this->data['sel_warehouse'] = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : NULL;
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('daily_purchases_report')));
        $meta = array('page_title' => lang('daily_purchases_report'), 'bc' => $bc);
        $this->page_construct('reports/daily_purchases', $meta, $this->data);

    }


    function monthly_purchases($warehouse_id = NULL, $year = NULL, $pdf = NULL, $user_id = NULL)
    {
        $this->sma->checkPermissions();
        if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
            $warehouse_id = $this->session->userdata('warehouse_id');
        }
        if (!$year) {
            $year = date('Y');
        }
        if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $user_id = $this->session->userdata('user_id');
        }
        $this->load->language('calendar');
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['year'] = $year;
        $this->data['purchases'] = $user_id ? $this->reports_model->getStaffMonthlyPurchases($user_id, $year, $warehouse_id) : $this->reports_model->getMonthlyPurchases($year, $warehouse_id);
        if ($pdf) {
            $html = $this->load->view($this->theme . 'reports/monthly', $this->data, true);
            $name = lang("monthly_purchases") . "_" . $year . ".pdf";
            $html = str_replace('<p class="introtext">' . lang("reports_calendar_text") . '</p>', '', $html);
            $this->sma->generate_pdf($html, $name, null, null, null, null, null, 'L');
        }
        $this->data['warehouses'] = $this->site->getAllWarehouses();
        $this->data['warehouse_id'] = $warehouse_id;
        $this->data['sel_warehouse'] = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : NULL;
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('monthly_purchases_report')));
        $meta = array('page_title' => lang('monthly_purchases_report'), 'bc' => $bc);
        $this->page_construct('reports/monthly_purchases', $meta, $this->data);

    }

    function adjustments($warehouse_id = NULL)
    {
        $this->sma->checkPermissions('products');

        $this->data['users'] = $this->reports_model->getStaff();
        $this->data['warehouses'] = $this->site->getAllWarehouses();

        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('adjustments_report')));
        $meta = array('page_title' => lang('adjustments_report'), 'bc' => $bc);
        $this->page_construct('reports/adjustments', $meta, $this->data);
    }

    function getAdjustmentReport($pdf = NULL, $xls = NULL)
    {
        $this->sma->checkPermissions('products', TRUE);

        $product = $this->input->get('product') ? $this->input->get('product') : NULL;
        $warehouse = $this->input->get('warehouse') ? $this->input->get('warehouse') : NULL;
        $user = $this->input->get('user') ? $this->input->get('user') : NULL;
        $reference_no = $this->input->get('reference_no') ? $this->input->get('reference_no') : NULL;
        $start_date = $this->input->get('start_date') ? $this->input->get('start_date') : NULL;
        $end_date = $this->input->get('end_date') ? $this->input->get('end_date') : NULL;
        $serial = $this->input->get('serial') ? $this->input->get('serial') : NULL;

        if ($start_date) {
            $start_date = $this->sma->fld($start_date);
            $end_date = $this->sma->fld($end_date);
        }
        if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $user = $this->session->userdata('user_id');
        }

        if ($pdf || $xls) {

            $ai = "( SELECT adjustment_id, product_id, serial_no, GROUP_CONCAT(CONCAT({$this->db->dbprefix('products')}.name, ' (', (CASE WHEN {$this->db->dbprefix('adjustment_items')}.type  = 'subtraction' THEN (0-{$this->db->dbprefix('adjustment_items')}.quantity) ELSE {$this->db->dbprefix('adjustment_items')}.quantity END), ')') SEPARATOR '\n') as item_nane from {$this->db->dbprefix('adjustment_items')} LEFT JOIN {$this->db->dbprefix('products')} ON {$this->db->dbprefix('products')}.id={$this->db->dbprefix('adjustment_items')}.product_id GROUP BY {$this->db->dbprefix('adjustment_items')}.adjustment_id ) FAI";

            $this->db->select("DATE_FORMAT(date, '%Y-%m-%d %T') as date, reference_no, warehouses.name as wh_name, CONCAT({$this->db->dbprefix('users')}.first_name, ' ', {$this->db->dbprefix('users')}.last_name) as created_by, note, FAI.item_nane as iname, {$this->db->dbprefix('adjustments')}.id as id", FALSE)
            ->from('adjustments')
            ->join($ai, 'FAI.adjustment_id=adjustments.id', 'left')
            ->join('users', 'users.id=adjustments.created_by', 'left')
            ->join('warehouses', 'warehouses.id=adjustments.warehouse_id', 'left');

            if ($user) {
                $this->db->where('adjustments.created_by', $user);
            }
            if ($product) {
                $this->db->where('FAI.product_id', $product, FALSE);
            }
            if ($serial) {
                $this->db->like('FAI.serial_no', $serial, FALSE);
            }
            if ($warehouse) {
                $this->db->where('adjustments.warehouse_id', $warehouse);
            }
            if ($reference_no) {
                $this->db->like('adjustments.reference_no', $reference_no, 'both');
            }
            if ($start_date) {
                $this->db->where($this->db->dbprefix('adjustments').'.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
            }

            $q = $this->db->get();
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
                $this->excel->getActiveSheet()->setTitle(lang('adjustments_report'));
                $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                $this->excel->getActiveSheet()->SetCellValue('B1', lang('reference_no'));
                $this->excel->getActiveSheet()->SetCellValue('C1', lang('warehouse'));
                $this->excel->getActiveSheet()->SetCellValue('D1', lang('created_by'));
                $this->excel->getActiveSheet()->SetCellValue('E1', lang('note'));
                $this->excel->getActiveSheet()->SetCellValue('F1', lang('products'));

                $row = 2;
                foreach ($data as $data_row) {
                    $this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->sma->hrld($data_row->date));
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->reference_no);
                    $this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->wh_name);
                    $this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->created_by);
                    $this->excel->getActiveSheet()->SetCellValue('E' . $row, $this->sma->decode_html($data_row->note));
                    $this->excel->getActiveSheet()->SetCellValue('F' . $row, $data_row->iname);
                    $row++;
                }

                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(40);
                $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(30);
                $filename = 'adjustments_report';
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
                    $this->excel->getActiveSheet()->getStyle('E2:E' . $row)->getAlignment()->setWrapText(true);
                    $this->excel->getActiveSheet()->getStyle('F2:F' . $row)->getAlignment()->setWrapText(true);
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

        } else {

            $ai = "( SELECT adjustment_id, product_id, serial_no, GROUP_CONCAT(CONCAT({$this->db->dbprefix('products')}.name, '__', (CASE WHEN {$this->db->dbprefix('adjustment_items')}.type  = 'subtraction' THEN (0-{$this->db->dbprefix('adjustment_items')}.quantity) ELSE {$this->db->dbprefix('adjustment_items')}.quantity END)) SEPARATOR '___') as item_nane from {$this->db->dbprefix('adjustment_items')} LEFT JOIN {$this->db->dbprefix('products')} ON {$this->db->dbprefix('products')}.id={$this->db->dbprefix('adjustment_items')}.product_id ";
            if ($product) {
                $ai .= " WHERE {$this->db->dbprefix('adjustment_items')}.product_id = {$product} ";
            }
            $ai .= " GROUP BY {$this->db->dbprefix('adjustment_items')}.adjustment_id ) FAI";
            $this->load->library('datatables');
            $this->datatables
            ->select("DATE_FORMAT(date, '%Y-%m-%d %T') as date, reference_no, warehouses.name as wh_name, CONCAT({$this->db->dbprefix('users')}.first_name, ' ', {$this->db->dbprefix('users')}.last_name) as created_by, note, FAI.item_nane as iname, {$this->db->dbprefix('adjustments')}.id as id", FALSE)
            ->from('adjustments')
            ->join($ai, 'FAI.adjustment_id=adjustments.id', 'left')
            ->join('users', 'users.id=adjustments.created_by', 'left')
            ->join('warehouses', 'warehouses.id=adjustments.warehouse_id', 'left');

            if ($user) {
                $this->datatables->where('adjustments.created_by', $user);
            }
            if ($product) {
                $this->datatables->where('FAI.product_id', $product, FALSE);
            }
            if ($serial) {
                $this->datatables->like('FAI.serial_no', $serial, FALSE);
            }
            if ($warehouse) {
                $this->datatables->where('adjustments.warehouse_id', $warehouse);
            }
            if ($reference_no) {
                $this->datatables->like('adjustments.reference_no', $reference_no, 'both');
            }
            if ($start_date) {
                $this->datatables->where($this->db->dbprefix('adjustments').'.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
            }

            echo $this->datatables->generate();
        }

    }

    function get_deposits($company_id = NULL)
    {
        $this->sma->checkPermissions('customers', TRUE);
        $this->load->library('datatables');
        $this->datatables
            ->select("date, amount, paid_by, CONCAT({$this->db->dbprefix('users')}.first_name, ' ', {$this->db->dbprefix('users')}.last_name) as created_by, note", false)
            ->from("deposits")
            ->join('users', 'users.id=deposits.created_by', 'left')
            ->where($this->db->dbprefix('deposits').'.company_id', $company_id);
        echo $this->datatables->generate();
    }
	function profit_loss_thuan($start_date = NULL, $end_date = NULL)
    {
        $this->sma->checkPermissions('profit_loss');
        if (!$start_date) {
            $start = $this->db->escape(date('Y-m-d').' 00:00');
            $start_date = date('Y-m-d');
        } else {
            $start = $this->db->escape(urldecode($start_date));
            $start_date = urldecode($start_date);
            $ex=explode(" ", $start_date);

            $start_date=$ex[0];
        }
        if (!$end_date) {
            $end = $this->db->escape(date('Y-m-d').' 23:59');
            $end_date = date('Y-m-d');
        } else {
            $end = $this->db->escape(urldecode($end_date));
            $end_date = urldecode($end_date);
            $ex=explode(" ", $end_date);

            $end_date=$ex[0];
        }
 
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        

        $this->data['total_purchases'] = $this->reports_model->getTotalPurchasesThuanV2020($start_date, $end_date);

        $this->data['total_sales'] = $this->reports_model->getTotalSales($start, $end);
        $this->data['total_expenses'] = $this->reports_model->getTotalExpenses($start, $end);
        $this->data['total_paid'] = $this->reports_model->getTotalPaidAmount($start, $end);
        $this->data['total_received'] = $this->reports_model->getTotalReceivedAmount($start, $end);
        
        $this->data['list_expenses'] = $this->reports_model->getListExpensesByPTTT($start, $end, $warehouse_id = NULL);
        $this->data['list_thukhac'] = $this->reports_model->getListThuKhacByPTTT($start, $end, $warehouse_id = NULL);

        $this->data['total_thukhac'] = $this->reports_model->getTotalDoanhthukhacBC($start, $end);
        
        $this->data['total_received_cash'] = $this->reports_model->getTotalReceivedCashAmount($start, $end);
        $this->data['total_received_cc'] = $this->reports_model->getTotalReceivedCCAmount($start, $end);
        $this->data['total_received_cheque'] = $this->reports_model->getTotalReceivedChequeAmount($start, $end);
        $this->data['total_received_ppp'] = $this->reports_model->getTotalReceivedPPPAmount($start, $end);
        $this->data['total_received_stripe'] = $this->reports_model->getTotalReceivedStripeAmount($start, $end);
        $this->data['total_returned'] = $this->reports_model->getTotalReturnedAmount($start, $end);
        $this->data['start'] = urldecode($start_date);
        $this->data['end'] = urldecode($end_date);
                        
                
        $warehouses = $this->site->getAllWarehouses();
        foreach ($warehouses as $warehouse) {
            $total_purchases = $this->reports_model->getTotalPurchases($start, $end, $warehouse->id);
            $total_sales = $this->reports_model->getTotalSales($start, $end, $warehouse->id);
            
            $total_thu_khac= $this->reports_model->getTotalDoanhthukhacBC($start, $end, $warehouse->id);
            $total_expenses = $this->reports_model->getTotalExpenses($start, $end, $warehouse->id);
            //lay danh sach tat ca san pham da ban theo kho, theo ngay
            $total_cost=$this->site->getGiaVonAllItemByWharehouse($warehouse->id,$start_date, $end_date);

            //$total_cost=$this->reports_model->getCostingFromDate($start_date, $end_date, $warehouse->id);
 
              $warehouses_report[] = array(
                'warehouse' => $warehouse,
                'total_purchases' => $total_cost,
                'total_sales' => $total_sales,
                'total_expenses' => $total_expenses,
                'total_cost' => $total_purchases,
                'total_doanhthu_khac'=>$total_thu_khac,
                );
        }
        $this->data['warehouses_report'] = $warehouses_report;
        
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('Báo cáo kết quả hoạt động kinh doanh')));
        $meta = array('page_title' => lang('Báo cáo kết quả hoạt động kinh doanh'), 'bc' => $bc);
        $this->page_construct('reports/profit_loss_thuan', $meta, $this->data);
    }
	 function nhom()
    {
        $this->sma->checkPermissions('products');
        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['categories'] = $this->site->getAllNhomsanpham();
        $this->data['warehouses'] = $this->site->getAllWarehouses();
        if ($this->input->post('start_date')) {
            $dt = "From " . $this->input->post('start_date') . " to " . $this->input->post('end_date');
        } else {
            $dt = "Till " . $this->input->post('end_date');
        }
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('Báo cáo nhóm sản phẩm')));
        $meta = array('page_title' => lang('Báo cáo nhóm sản phẩm'), 'bc' => $bc);
        $this->page_construct('reports/nhom', $meta, $this->data);
    }

    function getNhomReport($pdf = NULL, $xls = NULL)
    {
        $this->sma->checkPermissions('products', TRUE);
        $warehouse = $this->input->get('warehouse') ? $this->input->get('warehouse') : NULL;
        $category = $this->input->get('group_by') ? $this->input->get('group_by') : NULL;
        $start_date = $this->input->get('start_date') ? $this->input->get('start_date') : NULL;
        $end_date = $this->input->get('end_date') ? $this->input->get('end_date') : NULL;

        $pp = "( SELECT pp.group_id as category, SUM( pi.quantity ) purchasedQty, SUM( pi.subtotal ) totalPurchase from {$this->db->dbprefix('products')} pp
                left JOIN " . $this->db->dbprefix('purchase_items') . " pi ON pp.id = pi.product_id
                left join " . $this->db->dbprefix('purchases') . " p ON p.id = pi.purchase_id ";
        $sp = "( SELECT sp.group_id as category, SUM( si.quantity ) soldQty, SUM( si.subtotal ) totalSale from {$this->db->dbprefix('products')} sp
                left JOIN " . $this->db->dbprefix('sale_items') . " si ON sp.id = si.product_id
                left join " . $this->db->dbprefix('sales') . " s ON s.id = si.sale_id ";
        if ($start_date || $warehouse) {
            $pp .= " WHERE ";
            $sp .= " WHERE ";
            if ($start_date) {
                $start_date = $this->sma->fld($start_date);
                $end_date = $end_date ? $this->sma->fld($end_date) : date('Y-m-d');
                $pp .= " p.date >= '{$start_date}' AND p.date < '{$end_date}' ";
                $sp .= " s.date >= '{$start_date}' AND s.date < '{$end_date}' ";
                if ($warehouse) {
                    $pp .= " AND ";
                    $sp .= " AND ";
                }
            }
            if ($warehouse) {
                $pp .= " pi.warehouse_id = '{$warehouse}' ";
                $sp .= " si.warehouse_id = '{$warehouse}' ";
            }
        }
        $pp .= " GROUP BY pp.group_id ) PCosts";
        $sp .= " GROUP BY sp.group_id ) PSales";

        if ($pdf || $xls) {

            $this->db->select($this->db->dbprefix('group_products') . ".code, " . $this->db->dbprefix('group_products') . ".name,
                    SUM( COALESCE( PCosts.purchasedQty, 0 ) ) as PurchasedQty,
                    SUM( COALESCE( PSales.soldQty, 0 ) ) as SoldQty,
                    SUM( COALESCE( PCosts.totalPurchase, 0 ) ) as TotalPurchase,
                    SUM( COALESCE( PSales.totalSale, 0 ) ) as TotalSales,
                    (SUM( COALESCE( PSales.totalSale, 0 ) )- SUM( COALESCE( PCosts.totalPurchase, 0 ) ) ) as Profit", FALSE)
                ->from('group_products')
                ->join($sp, 'group_products.id = PSales.category', 'left')
                ->join($pp, 'group_products.id = PCosts.category', 'left')
                ->group_by('group_products.id, group_products.code, group_products.name')
                ->order_by('group_products.code', 'asc');

            if ($category) {
                $this->db->where($this->db->dbprefix('group_products') . ".id", $category);
            }

            $q = $this->db->get();
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
                $this->excel->getActiveSheet()->setTitle(lang('Báo cáo nhóm sản phẩm'));
                $this->excel->getActiveSheet()->SetCellValue('A1', lang('Mã'));
                $this->excel->getActiveSheet()->SetCellValue('B1', lang('Tên nhóm'));
                $this->excel->getActiveSheet()->SetCellValue('C1', lang('purchased'));
                $this->excel->getActiveSheet()->SetCellValue('D1', lang('sold'));
                $this->excel->getActiveSheet()->SetCellValue('E1', lang('purchased_amount'));
                $this->excel->getActiveSheet()->SetCellValue('F1', lang('sold_amount'));
                $this->excel->getActiveSheet()->SetCellValue('G1', lang('profit_loss'));

                $row = 2;
                $sQty = 0;
                $pQty = 0;
                $sAmt = 0;
                $pAmt = 0;
                $pl = 0;
				$ton = 0;
                foreach ($data as $data_row) {
                    $profit = $data_row->TotalSales - $data_row->TotalPurchase;
                    $this->excel->getActiveSheet()->SetCellValue('A' . $row, $data_row->code);
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->name);
                    $this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->PurchasedQty);
                    $this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->SoldQty);
                    $this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->TotalPurchase);
                    $this->excel->getActiveSheet()->SetCellValue('F' . $row, $data_row->TotalSales);
                    $this->excel->getActiveSheet()->SetCellValue('G' . $row, $profit);
                    $pQty += $data_row->PurchasedQty;
                    $sQty += $data_row->SoldQty;
                    $pAmt += $data_row->TotalPurchase;
                    $sAmt += $data_row->TotalSales;
                    $pl += $profit;
                    $row++;
                }
                $this->excel->getActiveSheet()->getStyle("C" . $row . ":G" . $row)->getBorders()
                    ->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);
                $this->excel->getActiveSheet()->SetCellValue('C' . $row, $pQty);
                $this->excel->getActiveSheet()->SetCellValue('D' . $row, $sQty);
                $this->excel->getActiveSheet()->SetCellValue('E' . $row, $pAmt);
                $this->excel->getActiveSheet()->SetCellValue('F' . $row, $sAmt);
                $this->excel->getActiveSheet()->SetCellValue('G' . $row, $pl);

                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(35);
                $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(35);
                $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(25);

                $filename = 'baocao_nhom_';
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
                    $this->excel->getActiveSheet()->getStyle('C2:G' . $row)->getAlignment()->setWrapText(true);
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

        } else {


            $this->load->library('datatables');
            $this->datatables
                ->select($this->db->dbprefix('group_products') . ".id as cid, " .$this->db->dbprefix('group_products') . ".code, " . $this->db->dbprefix('group_products') . ".name,
                    SUM( COALESCE( PCosts.purchasedQty, 0 ) ) as PurchasedQty,
                    SUM( COALESCE( PSales.soldQty, 0 ) ) as SoldQty,
                    SUM( COALESCE( PCosts.totalPurchase, 0 ) ) as TotalPurchase,
                    SUM( COALESCE( PSales.totalSale, 0 ) ) as TotalSales,
                    (SUM( COALESCE( PSales.totalSale, 0 ) )- SUM( COALESCE( PCosts.totalPurchase, 0 ) ) ) as Profit", FALSE)
                ->from('group_products')
                ->join($sp, 'group_products.id = PSales.category', 'left')
                ->join($pp, 'group_products.id = PCosts.category', 'left');
                
            if ($category) {
                $this->datatables->where('group_products.id', $category);
            }
            $this->datatables->group_by('group_products.id, group_products.code, group_products.name, PSales.SoldQty, PSales.totalSale, PCosts.purchasedQty, PCosts.totalPurchase');
            $this->datatables->unset_column('cid');
            echo $this->datatables->generate();

        }

    }
	function getTonDauReport()
    {
        $this->sma->checkPermissions('purchases', TRUE);

        $product = $this->input->get('product') ? $this->input->get('product') : NULL;
        $warehouse = $this->input->get('warehouse') ? $this->input->get('warehouse') : NULL;
       
         
		$this->load->library('datatables');
		$this->datatables
			->select("DATE_FORMAT({$this->db->dbprefix('purchase_items')}.date, '%Y-%m-%d %T') as date,{$this->db->dbprefix('warehouses')}.name as wname,{$this->db->dbprefix('purchase_items')}.product_name as product_name,SUM({$this->db->dbprefix('purchase_items')}.quantity) as sl,(SELECT name from {$this->db->dbprefix('units')} WHERE id=scodeweb_products.purchase_unit) as donvi,{$this->db->dbprefix('purchase_items')}.id as id", FALSE)
			->from('purchase_items')
			->join('products', 'scodeweb_products.id=scodeweb_purchase_items.product_id', 'left')
			->join('warehouses', 'warehouses.id=scodeweb_purchase_items.warehouse_id', 'left');
		
		if ($product) {
			$this->datatables->where('scodeweb_purchase_items.product_id', $product, FALSE);
			$this->datatables->where('scodeweb_purchase_items.bandau_id',$product);
		}
		if ($warehouse) {
			$this->datatables->where('scodeweb_purchase_items.warehouse_id', $warehouse);
		}
		$this->datatables->group_by(array('scodeweb_purchase_items.warehouse_id','scodeweb_purchase_items.option_id'));
		 //$this->datatables->unset_column('cid');
		//echo $this->db->get_compiled_select();
		echo $this->datatables->generate();        

    }
	function doitac()
    {
        $this->sma->checkPermissions('suppliers');
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');

        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('Báo cáo đối tác')));
        $meta = array('page_title' => lang('Báo cáo đối tác'), 'bc' => $bc);
        $this->page_construct('reports/baocaodoitac', $meta, $this->data);
    }

    function getDoitacs($pdf = NULL, $xls = NULL)
    {
        $this->sma->checkPermissions('suppliers', TRUE);

        if ($pdf || $xls) {

				$this->db->select($this->db->dbprefix('companies') . ".id as id, company, name, phone, email,(SELECT count(" . $this->db->dbprefix('purchases') . ".id) FROM {$this->db->dbprefix('purchases')} WHERE supplier_id={$this->db->dbprefix('companies')}.id) as total,(SELECT COALESCE(sum(grand_total), 0) FROM {$this->db->dbprefix('purchases')} WHERE supplier_id={$this->db->dbprefix('companies')}.id) as total_amount,(SELECT COALESCE(sum(paid), 0) FROM {$this->db->dbprefix('purchases')} WHERE supplier_id={$this->db->dbprefix('companies')}.id) + (select COALESCE(sum(amount), 0) from scodeweb_payments WHERE id_ncc_id_kh={$this->db->dbprefix('companies')}.id and type='sent') as paid,((SELECT COALESCE(sum(grand_total), 0) FROM {$this->db->dbprefix('purchases')} WHERE supplier_id={$this->db->dbprefix('companies')}.id) - (SELECT COALESCE(sum(paid), 0) FROM {$this->db->dbprefix('purchases')} WHERE supplier_id={$this->db->dbprefix('companies')}.id) - (select COALESCE(sum(amount), 0) from scodeweb_payments WHERE id_ncc_id_kh={$this->db->dbprefix('companies')}.id and type='sent')+nobandau) as balance,nobandau", FALSE)->from("companies")
                ->where('companies.group_name', 'supplier');

            $q = $this->db->get();
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
                $this->excel->getActiveSheet()->setTitle(lang('suppliers_report'));
                $this->excel->getActiveSheet()->SetCellValue('A1', lang('company'));
                $this->excel->getActiveSheet()->SetCellValue('B1', lang('name'));
                $this->excel->getActiveSheet()->SetCellValue('C1', lang('phone'));
                $this->excel->getActiveSheet()->SetCellValue('D1', lang('email'));
                $this->excel->getActiveSheet()->SetCellValue('E1', lang('total_purchases'));
                $this->excel->getActiveSheet()->SetCellValue('F1', lang('total_amount'));
                $this->excel->getActiveSheet()->SetCellValue('G1', lang('paid'));
				$this->excel->getActiveSheet()->SetCellValue('H1', lang('Nợ ban đầu'));
                $this->excel->getActiveSheet()->SetCellValue('I1', lang('balance'));

                $row = 2;
                foreach ($data as $data_row) {
                    $this->excel->getActiveSheet()->SetCellValue('A' . $row, $data_row->company);
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->name);
                    $this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->phone);
                    $this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->email);
                    $this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->total);
                    $this->excel->getActiveSheet()->SetCellValue('F' . $row, $data_row->total_amount);
                    $this->excel->getActiveSheet()->SetCellValue('G' . $row, $data_row->paid);
                    $this->excel->getActiveSheet()->SetCellValue('H' . $row, $data_row->nobandau);
					$this->excel->getActiveSheet()->SetCellValue('I' . $row, $data_row->balance);
                    $row++;
                }

                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
                $filename = 'suppliers_report';
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

        } else {

			
            $this->load->library('datatables');
			
            $this->datatables
                ->select($this->db->dbprefix('doitac') . ".id as id, code, name, diachi, dienthoai,email,note,(SELECT count(" . $this->db->dbprefix('deliveries') . ".id) FROM {$this->db->dbprefix('deliveries')} WHERE delivered_by={$this->db->dbprefix('doitac')}.id) as total,(SELECT COALESCE(sum(shipping), 0) FROM {$this->db->dbprefix('deliveries')} WHERE delivered_by={$this->db->dbprefix('doitac')}.id) as total_amount,(SELECT COALESCE(sum(amount), 0) FROM {$this->db->dbprefix('expenses')} WHERE doitac={$this->db->dbprefix('doitac')}.id) as paid,(((SELECT COALESCE(sum(shipping), 0) FROM {$this->db->dbprefix('deliveries')} WHERE delivered_by={$this->db->dbprefix('doitac')}.id)+nodauky)-(SELECT COALESCE(sum(amount), 0) FROM {$this->db->dbprefix('expenses')} WHERE doitac={$this->db->dbprefix('doitac')}.id)) as balance,nodauky", FALSE)->from("doitac")
              ->add_column("Actions", "<div class='text-center'><a class=\"tip\" title='" . lang("view_report") . "' href='" . site_url('reports/doitac_report/$1') . "'><span class='label label-primary'>" . lang("view_report") . "</span></a></div>", "id")
                ->unset_column('id');
			//echo $this->db->get_compiled_select();		
			//lhson code 24/03/2018
           echo $this->datatables->generate();

        }

    }
	
    function doitac_report($user_id = NULL)
    {
        $this->sma->checkPermissions('suppliers', TRUE);
        if (!$user_id) {
            $this->session->set_flashdata('error', lang("Chưa chọn đối tác"));
            redirect('reports/doitac');
        }
		$tongcong=$this->reports_model->getDoiTacTotalAmount($user_id);		
        $doitac = $this->Doitac_model->getDoitacByID($user_id);
		 				
		$this->data['nodauky']=$doitac->nodauky;		
        $this->data['tongcong'] = $tongcong;
		$this->data['dathanhtoan']=$this->reports_model->getDoiTacTotalPaid($user_id);
		$this->data['tongsl']=$this->reports_model->getDoiTacTotalQuantity($user_id);
		
        $this->data['users'] = $this->reports_model->getStaff();
		
        $this->data['warehouses'] = $this->site->getAllWarehouses();		
        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
		$this->data['categories'] = $this->reports_model->getExpenseCategories();
        $this->data['user_id'] = $user_id;
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('Báo cáo đối tác - '.$doitac->name)));
		
        $meta = array('page_title' => lang('Báo cáo đối tác - '.$doitac->name), 'bc' => $bc);
        $this->page_construct('reports/baocaodoitac_chitiet', $meta, $this->data);

    }
	function getPaymentsReportDoiTac($pdf = NULL, $xls = NULL)
    {
        $this->sma->checkPermissions('expenses');

        $reference_no = $this->input->get('reference_no') ? $this->input->get('reference_no') : NULL;
        $category = $this->input->get('category') ? $this->input->get('category') : NULL;
        $note = $this->input->get('note') ? $this->input->get('note') : NULL;
        $user = $this->input->get('user') ? $this->input->get('user') : NULL;
		$doitac = $this->input->get('doitac') ? $this->input->get('doitac') : NULL;
        $start_date = $this->input->get('start_date') ? $this->input->get('start_date') : NULL;
        $end_date = $this->input->get('end_date') ? $this->input->get('end_date') : NULL;

        if ($start_date) {
            $start_date = $this->sma->fld($start_date);
            $end_date = $this->sma->fld($end_date);
        }

        if ($pdf || $xls) {

            $this->db->select("date, reference, {$this->db->dbprefix('expense_categories')}.name as category, amount, note, CONCAT({$this->db->dbprefix('users')}.first_name, ' ', {$this->db->dbprefix('users')}.last_name) as user, attachment, {$this->db->dbprefix('expenses')}.id as id", false)
            ->from('expenses')
            ->join('users', 'users.id=expenses.created_by', 'left')
            ->join('expense_categories', 'expense_categories.id=expenses.category_id', 'left')
            ->group_by('expenses.id');

            if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
                $this->db->where('created_by', $this->session->userdata('user_id'));
            }

            if ($note) {
                $this->db->like('note', $note, 'both');
            }
            if ($reference_no) {
                $this->db->like('reference', $reference_no, 'both');
            }
            if ($category) {
                $this->db->where('category_id', $category);
            }
            if ($user) {
                $this->db->where('created_by', $user);
            }
			 
			$this->db->where('doitac', $doitac);
            
            if ($start_date) {
                $this->db->where('date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
            }

            $q = $this->db->get();
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
                $this->excel->getActiveSheet()->setTitle(lang('Báo cáo thanh toán đối tác'));
                $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                $this->excel->getActiveSheet()->SetCellValue('B1', lang('reference_no'));
                $this->excel->getActiveSheet()->SetCellValue('C1', lang('category'));
                $this->excel->getActiveSheet()->SetCellValue('D1', lang('amount'));
                $this->excel->getActiveSheet()->SetCellValue('E1', lang('note'));
                $this->excel->getActiveSheet()->SetCellValue('F1', lang('created_by'));

                $row = 2; $total = 0;
                foreach ($data as $data_row) {
                    $this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->sma->hrld($data_row->date));
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->reference);
                    $this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->category);
                    $this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->amount);
                    $this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->note);
                    $this->excel->getActiveSheet()->SetCellValue('F' . $row, $data_row->created_by);
                    $total += $data_row->amount;
                    $row++;
                }
                $this->excel->getActiveSheet()->getStyle("D" . $row)->getBorders()
                    ->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);
                $this->excel->getActiveSheet()->SetCellValue('D' . $row, $total);

                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(35);
                $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(25);

                $filename = 'thanhtoan_doitac_report';
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

        } else {

            $this->load->library('datatables');
            $this->datatables
            ->select("DATE_FORMAT(date, '%Y-%m-%d %T') as date, reference, {$this->db->dbprefix('expense_categories')}.name as category, amount, note, CONCAT({$this->db->dbprefix('users')}.first_name, ' ', {$this->db->dbprefix('users')}.last_name) as user, attachment, {$this->db->dbprefix('expenses')}.id as id", false)
            ->from('expenses')
            ->join('users', 'users.id=expenses.created_by', 'left')
            ->join('expense_categories', 'expense_categories.id=expenses.category_id', 'left')
            ->group_by('expenses.id');

            if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
                $this->datatables->where('created_by', $this->session->userdata('user_id'));
            }
			$this->datatables->where('doitac', $doitac);
			
            if ($note) {
                $this->datatables->like('note', $note, 'both');
            }
            if ($reference_no) {
                $this->datatables->like('reference', $reference_no, 'both');
            }
            if ($category) {
                $this->datatables->where('category_id', $category);
            }
            if ($user) {
                $this->datatables->where('created_by', $user);
            }
            if ($start_date) {
                $this->datatables->where('date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
            }

            echo $this->datatables->generate();
        }
    }
	public function getDeliveriesDoiTac($pdf = NULL, $xls = NULL)
    {
        $this->sma->checkPermissions('deliveries');
      
		
		$doitac = $this->input->get('doitac') ? $this->input->get('doitac') : NULL;
        $start_date = $this->input->get('start_date') ? $this->input->get('start_date') : NULL;
        $end_date = $this->input->get('end_date') ? $this->input->get('end_date') : NULL;

        if ($start_date) {
            $start_date = $this->sma->fld($start_date);
            $end_date = $this->sma->fld($end_date);
        }
		if ($pdf || $xls) {

            $this->db->select("date, do_reference_no, sale_reference_no,(SELECT CONCAT(code, '-',name) FROM scodeweb_doitac WHERE id=delivered_by) as doitac,shipping, customer, address, status, attachment,deliveries.id as id", false)
            ->from('deliveries')
            ->join('sale_items', 'sale_items.sale_id=deliveries.sale_id', 'left')
            ->group_by('deliveries.id');
						
            if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
                $this->db->where('created_by', $this->session->userdata('user_id'));
            }

           
			 
			$this->db->where('delivered_by', $doitac);
            
            if ($start_date) {
                $this->db->where('date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
            }

            $q = $this->db->get();
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
                $this->excel->getActiveSheet()->setTitle(lang('Báo cáo giao hàng đối tác'));
                $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
				$this->excel->getActiveSheet()->SetCellValue('B1', lang('Mã GH'));
				$this->excel->getActiveSheet()->SetCellValue('C1', lang('Mã Bán Hàng'));
				$this->excel->getActiveSheet()->SetCellValue('D1', lang('Đối tác'));
				$this->excel->getActiveSheet()->SetCellValue('E1', lang('Phí'));
				$this->excel->getActiveSheet()->SetCellValue('F1', lang('customer'));
				$this->excel->getActiveSheet()->SetCellValue('G1', lang('address'));
				$this->excel->getActiveSheet()->SetCellValue('H1', lang('status'));

                $row = 2; $total = 0;
                foreach ($data as $data_row) {
                    $this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->sma->hrld($data_row->date));
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->do_reference_no);
                    $this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->sale_reference_no);
                    $this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->doitac);
                    $this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->shipping);
                    $this->excel->getActiveSheet()->SetCellValue('F' . $row, $data_row->customer);
					$this->excel->getActiveSheet()->SetCellValue('G' . $row, $data_row->address);
					$this->excel->getActiveSheet()->SetCellValue('H' . $row, lang($data_row->status));
                    $total += $data_row->shipping;
                    $row++;
                }
                $this->excel->getActiveSheet()->getStyle("E" . $row)->getBorders()
                    ->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);
                $this->excel->getActiveSheet()->SetCellValue('E' . $row, $total);

                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(35);
                $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(25);

                $filename = 'giaohang_doitac_report';
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

        } else {
			$this->load->library('datatables');
			
			$this->datatables
				->select("date, do_reference_no, sale_reference_no,(SELECT CONCAT(code, '-',name) FROM scodeweb_doitac WHERE id=delivered_by) as doitac,shipping, customer, address, status, attachment,deliveries.id as id")
				->from('deliveries')
				->join('sale_items', 'sale_items.sale_id=deliveries.sale_id', 'left')
				->group_by('deliveries.id');
				
			$this->datatables->where('delivered_by', $doitac);
				
			
			if ($start_date) {
				$this->datatables->where('date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
			}	
			
			echo $this->datatables->generate();
		}
    }
	function getSalesReportBySanPham($pdf = NULL, $xls = NULL)
    {
        //$this->sma->checkPermissions('sales', TRUE);
        $product = $this->input->get('product') ? $this->input->get('product') : NULL;
        $user = $this->input->get('user') ? $this->input->get('user') : NULL;
        $customer = $this->input->get('customer') ? $this->input->get('customer') : NULL;
        $biller = $this->input->get('biller') ? $this->input->get('biller') : NULL;
        $warehouse = $this->input->get('warehouse') ? $this->input->get('warehouse') : NULL;
        $reference_no = $this->input->get('reference_no') ? $this->input->get('reference_no') : NULL;
        $start_date = $this->input->get('start_date') ? $this->input->get('start_date') : NULL;
        $end_date = $this->input->get('end_date') ? $this->input->get('end_date') : NULL;
        $serial = $this->input->get('serial') ? $this->input->get('serial') : NULL;

        if ($start_date) {
            $start_date = $this->sma->fld($start_date);
            $end_date = $this->sma->fld($end_date);
        }
        if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $user = $this->session->userdata('user_id');
        }

        if ($pdf || $xls) {

            $this->db->select("date, reference_no,(select name from scodeweb_warehouses where id=scodeweb_sales.warehouse_id) as kho,(select name from scodeweb_doitac where id=scodeweb_sales.doitac) as doitac, biller, sales.customer_id,CONCAT({$this->db->dbprefix('sale_items')}.product_name,'-',{$this->db->dbprefix('sale_items')}.product_unit_code) as item_nane,scodeweb_sale_items.unit_quantity as soluong,scodeweb_sale_items.unit_price as giaban,grand_total,paid,(grand_total-paid) as balance,total_tax,shipping,(total_discount+order_discount) as total_discount, payment_status", FALSE)
                ->from('sales')
                ->join('sale_items', 'sale_items.sale_id=sales.id', 'left')
                ->join('warehouses', 'warehouses.id=sales.warehouse_id', 'left')
                ->order_by('sales.date desc');
			$this->db->where('sales.sale_status !=','returned');
            if ($user) {
                $this->db->where('sales.created_by', $user);
            }
            if ($product) {
                $this->db->where('sale_items.product_id', $product);
            }
            if ($serial) {
                $this->db->like('sale_items.serial_no', $serial);
            }
            if ($biller) {
                $this->db->where('sales.biller_id', $biller);
            }
            if ($customer) {
                $this->db->where('sales.customer_id', $customer);
            }
            if ($warehouse) {
                $this->db->where('sales.warehouse_id', $warehouse);
            }
            if ($reference_no) {
                $this->db->like('sales.reference_no', $reference_no, 'both');
            }
            if ($start_date) {
                $this->db->where($this->db->dbprefix('sales').'.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
            }
			
			$q = $this->db->get();
			
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
                $this->excel->getActiveSheet()->setTitle(lang('sales_report'));
                $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                $this->excel->getActiveSheet()->SetCellValue('B1', lang('reference_no'));
                $this->excel->getActiveSheet()->SetCellValue('C1', lang('Kho'));
				$this->excel->getActiveSheet()->SetCellValue('D1', lang('ĐVGH'));
				$this->excel->getActiveSheet()->SetCellValue('E1', lang('biller'));
                $this->excel->getActiveSheet()->SetCellValue('F1', lang('customer'));
                $this->excel->getActiveSheet()->SetCellValue('G1', lang('Sản phẩm'));
                $this->excel->getActiveSheet()->SetCellValue('H1', lang('Số lượng'));
                $this->excel->getActiveSheet()->SetCellValue('I1', lang('Giá Bán'));
                $this->excel->getActiveSheet()->SetCellValue('J1', lang('Thành tiền'));				
				$this->excel->getActiveSheet()->SetCellValue('K1', lang('Thuế'));				
				$this->excel->getActiveSheet()->SetCellValue('L1', lang('Ship'));
				$this->excel->getActiveSheet()->SetCellValue('M1', lang('Giảm'));				
				$this->excel->getActiveSheet()->SetCellValue('N1', lang('Tổng cộng'));
				$this->excel->getActiveSheet()->SetCellValue('O1', lang('Đã thanh toán'));
				$this->excel->getActiveSheet()->SetCellValue('P1', lang('Dư nợ'));				
				$this->excel->getActiveSheet()->SetCellValue('Q1', lang('payment_status'));

                $row = 2;
                $total = 0;
                $paid = 0;
                $balance = 0;
				$dathanhtoan=0;
				$duno=0;
				$tongcong=0;
				$_giam=0; $_thue=0; $_ship=0;
                foreach ($data as $data_row) {
					
					$customer= $this->site->getCompanyByID($data_row->customer_id); 
					$_customer=$customer->phone."-".$customer->name;
					
											
					//$tensanpham=$this->defineTenSanPhamExport($data_row->iname);
					
                    $this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->sma->hrld($data_row->date));
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->reference_no);
                    $this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->kho);
					$this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->doitac);
					$this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->biller);
                    $this->excel->getActiveSheet()->SetCellValue('F' . $row, $_customer);
                    $this->excel->getActiveSheet()->SetCellValue('G' . $row, $data_row->item_nane);
                    $this->excel->getActiveSheet()->SetCellValue('H' . $row, $data_row->soluong);
                    $this->excel->getActiveSheet()->SetCellValue('I' . $row,$data_row->giaban);
                    $this->excel->getActiveSheet()->SetCellValue('J' . $row,($data_row->soluong*$data_row->giaban));				
					$this->excel->getActiveSheet()->SetCellValue('K' . $row,$data_row->total_tax);
					$this->excel->getActiveSheet()->SetCellValue('L' . $row,$data_row->shipping);
					$this->excel->getActiveSheet()->SetCellValue('M' . $row,$data_row->total_discount);					
					$this->excel->getActiveSheet()->SetCellValue('N' . $row,$data_row->grand_total);
					$this->excel->getActiveSheet()->SetCellValue('O' . $row,$data_row->paid);
					$this->excel->getActiveSheet()->SetCellValue('P' . $row,$data_row->balance);					
                    $this->excel->getActiveSheet()->SetCellValue('Q' . $row, lang($data_row->payment_status));
                    $total += $data_row->soluong;
                    
                    $balance += ($data_row->soluong * $data_row->giaban);
					$_giam+=$data_row->total_discount; $_thue+=$data_row->total_tax; $_ship+=$data_row->shipping;
					$dathanhtoan+= $data_row->paid;
					$duno+= ($data_row->grand_total - $data_row->paid);
					$tongcong+= $data_row->grand_total;
						
                    $row++;
                }
                $this->excel->getActiveSheet()->getStyle("H" . $row . ":P" . $row)->getBorders()
                    ->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);
                $this->excel->getActiveSheet()->SetCellValue('H' . $row, $total); 		
				$this->excel->getActiveSheet()->SetCellValue('K' . $row, $_thue);
				$this->excel->getActiveSheet()->SetCellValue('L' . $row, $_ship);
				$this->excel->getActiveSheet()->SetCellValue('M' . $row, $_giam);	
								
                $this->excel->getActiveSheet()->SetCellValue('P' . $row, $balance);
				$this->excel->getActiveSheet()->SetCellValue('N' . $row, $tongcong);
				$this->excel->getActiveSheet()->SetCellValue('O' . $row, $dathanhtoan);
				$this->excel->getActiveSheet()->SetCellValue('P' . $row, $duno);
				

                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(30);
                $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(35);
                $this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('I')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('J')->setWidth(20);
                $filename = 'BaocaoDoanhSoTheoSanPham_';
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
                    $this->excel->getActiveSheet()->getStyle('E2:E' . $row)->getAlignment()->setWrapText(true);
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

        } else {

            $si = "( SELECT sale_id, product_id, serial_no,CONCAT({$this->db->dbprefix('sale_items')}.product_name,'-',{$this->db->dbprefix('sale_items')}.product_unit_code) as item_nane,scodeweb_sale_items.unit_quantity as soluong,scodeweb_sale_items.unit_price as giaban FROM {$this->db->dbprefix('sale_items')} ";
            if ($product) {
                $si .= " WHERE {$this->db->dbprefix('sale_items')}.product_id = {$product} ";
            }
           // $si .= " GROUP BY {$this->db->dbprefix('sale_items')}.sale_id ) FSI";
			 $si .= "  ) FSI";
            $this->load->library('datatables');
            $this->datatables
                ->select("DATE_FORMAT(scodeweb_sales.date, '%Y-%m-%d %T') as date, reference_no,(select name from scodeweb_warehouses where id=scodeweb_sales.warehouse_id) as kho,scodeweb_doitac.name as doitac, biller, (SELECT CONCAT(scodeweb_companies.name,'-',scodeweb_companies.phone) FROM scodeweb_companies WHERE id=scodeweb_sales.customer_id) as customer, FSI.item_nane as iname,FSI.soluong as soluong,FSI.giaban as giaban,(FSI.soluong*FSI.giaban) as thanhtien,total_tax,shipping,(total_discount+order_discount) as total_discount,grand_total, paid,(grand_total-paid) as balance,payment_status, {$this->db->dbprefix('sales')}.id as id", FALSE)
                ->from('sales')
                ->join('doitac', 'doitac.id=sales.doitac', 'left')
                ->join($si, 'FSI.sale_id=sales.id', 'left')
                ->join('warehouses', 'warehouses.id=sales.warehouse_id', 'left');
                // ->group_by('sales.id');
			$this->datatables->where('sales.sale_status !=','returned');
            if ($user) {
                $this->datatables->where('sales.created_by', $user);
            }
            if ($product) {
                $this->datatables->where('FSI.product_id', $product, FALSE);
            }
            if ($serial) {
                $this->datatables->like('FSI.serial_no', $serial, FALSE);
            }
            if ($biller) {
                $this->datatables->where('sales.biller_id', $biller);
            }
            if ($customer) {
                $this->datatables->where('sales.customer_id', $customer);
            }
            if ($warehouse) {
                $this->datatables->where('sales.warehouse_id', $warehouse);
            }
            if ($reference_no) {
                $this->datatables->like('sales.reference_no', $reference_no, 'both');
            }
            if ($start_date) {
                $this->datatables->where($this->db->dbprefix('sales').'.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
            }
			
			//echo $this->db->_compile_select(); 
            echo $this->datatables->generate();

        }

    }
    function getSalesReportBySanPhamByCustomer()
    {
        $customer = $this->input->get('customer') ? $this->input->get('customer') : NULL;
       

        $si = "( SELECT sale_id, product_id, serial_no,CONCAT({$this->db->dbprefix('sale_items')}.product_name,'-',{$this->db->dbprefix('sale_items')}.product_unit_code) as item_nane,scodeweb_sale_items.unit_quantity as soluong,scodeweb_sale_items.unit_price as giaban FROM {$this->db->dbprefix('sale_items')} ";
        if ($product) {
            $si .= " WHERE {$this->db->dbprefix('sale_items')}.product_id = {$product} ";
        }
       // $si .= " GROUP BY {$this->db->dbprefix('sale_items')}.sale_id ) FSI";
         $si .= "  ) FSI";
        $this->load->library('datatables');
        $this->datatables
            ->select("DATE_FORMAT(date, '%Y-%m-%d %T') as date, reference_no,(select name from scodeweb_warehouses where id=scodeweb_sales.warehouse_id) as kho,(select name from scodeweb_doitac where id=scodeweb_sales.doitac) as doitac, biller, (SELECT CONCAT(scodeweb_companies.name,'-',scodeweb_companies.phone) FROM scodeweb_companies WHERE id=scodeweb_sales.customer_id) as customer, FSI.item_nane as iname,FSI.soluong as soluong,FSI.giaban as giaban,(FSI.soluong*FSI.giaban) as thanhtien,total_tax,shipping,(total_discount+order_discount) as total_discount,grand_total, paid,(grand_total-paid) as balance,payment_status, {$this->db->dbprefix('sales')}.id as id", FALSE)
            ->from('sales')
            ->join($si, 'FSI.sale_id=sales.id', 'left')
            ->join('warehouses', 'warehouses.id=sales.warehouse_id', 'left');
            // ->group_by('sales.id');
        $this->datatables->where('sales.sale_status !=','returned');
       
        if ($customer) {
            $this->datatables->where('sales.customer_id', $customer);
        }
        
        //echo $this->db->_compile_select(); 
        echo $this->datatables->generate();

    }
	function salesSanPham()
    {
        $this->sma->checkPermissions('sales');
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['users'] = $this->reports_model->getStaff();
        $this->data['warehouses'] = $this->site->getAllWarehouses();
        $this->data['billers'] = $this->site->getAllCompanies('biller');
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('reports'), 'page' => lang('Báo cáo doanh số theo sản phẩm')), array('link' => '#', 'page' => lang('Báo cáo doanh số theo sản phẩm')));
        $meta = array('page_title' => lang('Báo cáo doanh số theo sản phẩm'), 'bc' => $bc);
        $this->page_construct('reports/salessanpham', $meta, $this->data);
    }
	function baocaotralhson()
    {
        $this->sma->checkPermissions('sales');
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['users'] = $this->reports_model->getStaff();
        $this->data['warehouses'] = $this->site->getAllWarehouses();
        $this->data['billers'] = $this->site->getAllCompanies('biller');
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('reports'), 'page' => lang('Báo cáo doanh số theo sản phẩm')), array('link' => '#', 'page' => lang('Báo cáo Thu hồi công nợ')));
        $meta = array('page_title' => lang('Báo cáo Thu hồi công nợ'), 'bc' => $bc);
        $this->page_construct('reports/thuhoicongno', $meta, $this->data);
    }
	function getThuHoiReport($pdf = NULL, $xls = NULL)
    {
        $this->sma->checkPermissions('sales', TRUE);
        $product = $this->input->get('product') ? $this->input->get('product') : NULL;
        $user = $this->input->get('user') ? $this->input->get('user') : NULL;
        $customer = $this->input->get('customer') ? $this->input->get('customer') : NULL;
        $biller = $this->input->get('biller') ? $this->input->get('biller') : NULL;
        $warehouse = $this->input->get('warehouse') ? $this->input->get('warehouse') : NULL;
        $reference_no = $this->input->get('reference_no') ? $this->input->get('reference_no') : NULL;
        $start_date = $this->input->get('start_date') ? $this->input->get('start_date') : NULL;
        $end_date = $this->input->get('end_date') ? $this->input->get('end_date') : NULL;
        $serial = $this->input->get('serial') ? $this->input->get('serial') : NULL;

        if ($start_date) {
            $start_date = $this->sma->fld($start_date);
            $end_date = $this->sma->fld($end_date);
        }
        if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $user = $this->session->userdata('user_id');
        }

        if ($pdf || $xls) {

            $this->db->select("date, reference_no,(select name from scodeweb_warehouses where id=scodeweb_sales.warehouse_id) as kho,(SELECT CONCAT({$this->db->dbprefix('doitac')}.code, '-', {$this->db->dbprefix('doitac')}.name) FROM {$this->db->dbprefix('doitac')} WHERE id=doitac) as doitac, biller, sales.customer_id, GROUP_CONCAT(CONCAT(" . $this->db->dbprefix('sale_items') . ".product_name, ' (',CONCAT(scodeweb_sale_items.unit_quantity,'*',scodeweb_sale_items.unit_price), ')') SEPARATOR '\n') as iname, grand_total, paid, payment_status", FALSE)
                ->from('sales')
                ->join('sale_items', 'sale_items.sale_id=sales.id', 'left')
                ->join('warehouses', 'warehouses.id=sales.warehouse_id', 'left')
                ->group_by('sales.id')
                ->order_by('sales.date desc');
			$this->db->where('sales.sale_status','returned');
            if ($user) {
                $this->db->where('sales.created_by', $user);
            }
            if ($product) {
                $this->db->where('sale_items.product_id', $product);
            }
            if ($serial) {
                $this->db->like('sale_items.serial_no', $serial);
            }
            if ($biller) {
                $this->db->where('sales.biller_id', $biller);
            }
            if ($customer) {
                $this->db->where('sales.customer_id', $customer);
            }
            if ($warehouse) {
                $this->db->where('sales.warehouse_id', $warehouse);
            }
            if ($reference_no) {
                $this->db->like('sales.reference_no', $reference_no, 'both');
            }
            if ($start_date) {
                $this->db->where($this->db->dbprefix('sales').'.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
            }
			
			//$this->db->_compile_select(); 
            $q = $this->db->get();
			
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
                $this->excel->getActiveSheet()->setTitle(lang('sales_report'));
                $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                $this->excel->getActiveSheet()->SetCellValue('B1', lang('reference_no'));
                $this->excel->getActiveSheet()->SetCellValue('C1', lang('Kho'));
				$this->excel->getActiveSheet()->SetCellValue('D1', lang('ĐVGH'));
				$this->excel->getActiveSheet()->SetCellValue('E1', lang('biller'));
                $this->excel->getActiveSheet()->SetCellValue('F1', lang('customer'));
                $this->excel->getActiveSheet()->SetCellValue('G1', lang('product_qty'));
                $this->excel->getActiveSheet()->SetCellValue('H1', lang('grand_total'));
                $this->excel->getActiveSheet()->SetCellValue('I1', lang('paid'));
                $this->excel->getActiveSheet()->SetCellValue('J1', lang('balance'));
                $this->excel->getActiveSheet()->SetCellValue('K1', lang('payment_status'));

                $row = 2;
                $total = 0;
                $paid = 0;
                $balance = 0;
                foreach ($data as $data_row) {
					
					$customer= $this->site->getCompanyByID($data_row->customer_id); 
					$_customer=$customer->phone."-".$customer->name;
					
					//$tensanpham=$this->defineTenSanPhamExport($data_row->iname);
					
                    $this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->sma->hrld($data_row->date));
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->reference_no);
                    $this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->kho);
					$this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->doitac);
					$this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->biller);
                    $this->excel->getActiveSheet()->SetCellValue('F' . $row, $_customer);
                    $this->excel->getActiveSheet()->SetCellValue('G' . $row, $data_row->iname);
                    $this->excel->getActiveSheet()->SetCellValue('H' . $row, $data_row->grand_total);
                    $this->excel->getActiveSheet()->SetCellValue('I' . $row, $data_row->paid);
                    $this->excel->getActiveSheet()->SetCellValue('J' . $row, ($data_row->grand_total - $data_row->paid));
                    $this->excel->getActiveSheet()->SetCellValue('K' . $row, lang($data_row->payment_status));
                    $total += $data_row->grand_total;
                    $paid += $data_row->paid;
                    $balance += ($data_row->grand_total - $data_row->paid);
                    $row++;
                }
                $this->excel->getActiveSheet()->getStyle("H" . $row . ":J" . $row)->getBorders()
                    ->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);
                $this->excel->getActiveSheet()->SetCellValue('H' . $row, $total);
                $this->excel->getActiveSheet()->SetCellValue('I' . $row, $paid);
                $this->excel->getActiveSheet()->SetCellValue('J' . $row, $balance);

                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(35);
                $this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('I')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('J')->setWidth(20);
                $filename = 'BaoCaoThuHoiCongNo';
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
                    $this->excel->getActiveSheet()->getStyle('E2:E' . $row)->getAlignment()->setWrapText(true);
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

        } else {

            $si = "( SELECT sale_id, product_id, serial_no, GROUP_CONCAT(CONCAT(CONCAT({$this->db->dbprefix('sale_items')}.product_name,'-',{$this->db->dbprefix('sale_items')}.product_unit_code), '__',CONCAT(scodeweb_sale_items.unit_quantity,'__',scodeweb_sale_items.unit_price)) SEPARATOR '___') as item_nane from {$this->db->dbprefix('sale_items')} ";
            if ($product) {
                $si .= " WHERE {$this->db->dbprefix('sale_items')}.product_id = {$product} ";
            }
            $si .= " GROUP BY {$this->db->dbprefix('sale_items')}.sale_id ) FSI";
            $this->load->library('datatables');
            $this->datatables
                ->select("DATE_FORMAT(date, '%Y-%m-%d %T') as date, reference_no,(select name from scodeweb_warehouses where id=scodeweb_sales.warehouse_id) as kho,(SELECT CONCAT({$this->db->dbprefix('doitac')}.code, '-', {$this->db->dbprefix('doitac')}.name) FROM {$this->db->dbprefix('doitac')} WHERE id=doitac) as doitac, biller, (SELECT CONCAT(scodeweb_companies.name,'-',scodeweb_companies.phone) FROM scodeweb_companies WHERE id=scodeweb_sales.customer_id) as customer, FSI.item_nane as iname, grand_total, paid, (grand_total-paid) as balance, payment_status, {$this->db->dbprefix('sales')}.id as id", FALSE)
                ->from('sales')
                ->join($si, 'FSI.sale_id=sales.id', 'left')
                ->join('warehouses', 'warehouses.id=sales.warehouse_id', 'left');
                // ->group_by('sales.id');
				
			$this->datatables->where('sales.sale_status','returned');
            if ($user) {
                $this->datatables->where('sales.created_by', $user);
            }
            if ($product) {
                $this->datatables->where('FSI.product_id', $product, FALSE);
            }
            if ($serial) {
                $this->datatables->like('FSI.serial_no', $serial, FALSE);
            }
            if ($biller) {
                $this->datatables->where('sales.biller_id', $biller);
            }
            if ($customer) {
                $this->datatables->where('sales.customer_id', $customer);
            }
            if ($warehouse) {
                $this->datatables->where('sales.warehouse_id', $warehouse);
            }
            if ($reference_no) {
                $this->datatables->like('sales.reference_no', $reference_no, 'both');
            }
            if ($start_date) {
                $this->datatables->where($this->db->dbprefix('sales').'.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
            }

            echo $this->datatables->generate();

        }

    }
	function thuhoicongnochitiet()
    {
        $this->sma->checkPermissions('sales');
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['users'] = $this->reports_model->getStaff();
        $this->data['warehouses'] = $this->site->getAllWarehouses();
        $this->data['billers'] = $this->site->getAllCompanies('biller');
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('reports'), 'page' => lang('Báo cáo doanh số theo sản phẩm')), array('link' => '#', 'page' => lang('Báo cáo Thu hồi công nợ chi tiết')));
        $meta = array('page_title' => lang('Báo cáo Thu hồi công nợ chi tiết'), 'bc' => $bc);
        $this->page_construct('reports/thuhoicongnochitiet', $meta, $this->data);
    }
	function getThuHoiCongNoBySanPham($pdf = NULL, $xls = NULL)
    {
        $this->sma->checkPermissions('sales', TRUE);
        $product = $this->input->get('product') ? $this->input->get('product') : NULL;
        $user = $this->input->get('user') ? $this->input->get('user') : NULL;
        $customer = $this->input->get('customer') ? $this->input->get('customer') : NULL;
        $biller = $this->input->get('biller') ? $this->input->get('biller') : NULL;
        $warehouse = $this->input->get('warehouse') ? $this->input->get('warehouse') : NULL;
        $reference_no = $this->input->get('reference_no') ? $this->input->get('reference_no') : NULL;
        $start_date = $this->input->get('start_date') ? $this->input->get('start_date') : NULL;
        $end_date = $this->input->get('end_date') ? $this->input->get('end_date') : NULL;
        $serial = $this->input->get('serial') ? $this->input->get('serial') : NULL;

        if ($start_date) {
            $start_date = $this->sma->fld($start_date);
            $end_date = $this->sma->fld($end_date);
        }
        if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $user = $this->session->userdata('user_id');
        }

        if ($pdf || $xls) {

            $this->db->select("date, reference_no,(select name from scodeweb_warehouses where id=scodeweb_sales.warehouse_id) as kho,(select name from scodeweb_doitac where id=scodeweb_sales.doitac) as doitac, biller, sales.customer_id,CONCAT({$this->db->dbprefix('sale_items')}.product_name,'-',{$this->db->dbprefix('sale_items')}.product_unit_code) as item_nane,scodeweb_sale_items.unit_quantity as soluong,scodeweb_sale_items.unit_price as giaban,grand_total,paid,(grand_total-paid) as balance,total_tax,shipping,(total_discount+order_discount) as total_discount, payment_status,note", FALSE)
                ->from('sales')
                ->join('sale_items', 'sale_items.sale_id=sales.id', 'left')
                ->join('warehouses', 'warehouses.id=sales.warehouse_id', 'left')
                ->order_by('sales.date desc');
			$this->db->where('sales.sale_status','returned');
            if ($user) {
                $this->db->where('sales.created_by', $user);
            }
            if ($product) {
                $this->db->where('sale_items.product_id', $product);
            }
            if ($serial) {
                $this->db->like('sale_items.serial_no', $serial);
            }
            if ($biller) {
                $this->db->where('sales.biller_id', $biller);
            }
            if ($customer) {
                $this->db->where('sales.customer_id', $customer);
            }
            if ($warehouse) {
                $this->db->where('sales.warehouse_id', $warehouse);
            }
            if ($reference_no) {
                $this->db->like('sales.reference_no', $reference_no, 'both');
            }
            if ($start_date) {
                $this->db->where($this->db->dbprefix('sales').'.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
            }
			
			$q = $this->db->get();
			
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
                $this->excel->getActiveSheet()->setTitle(lang('sales_report'));
                $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                $this->excel->getActiveSheet()->SetCellValue('B1', lang('reference_no'));
                $this->excel->getActiveSheet()->SetCellValue('C1', lang('Kho'));
				$this->excel->getActiveSheet()->SetCellValue('D1', lang('ĐVGH'));
				$this->excel->getActiveSheet()->SetCellValue('E1', lang('biller'));
                $this->excel->getActiveSheet()->SetCellValue('F1', lang('customer'));
                $this->excel->getActiveSheet()->SetCellValue('G1', lang('Sản phẩm'));
                $this->excel->getActiveSheet()->SetCellValue('H1', lang('Số lượng'));
                $this->excel->getActiveSheet()->SetCellValue('I1', lang('Giá Bán'));
                $this->excel->getActiveSheet()->SetCellValue('J1', lang('Thành tiền'));				
				$this->excel->getActiveSheet()->SetCellValue('K1', lang('Thuế'));				
				$this->excel->getActiveSheet()->SetCellValue('L1', lang('Ship'));
				$this->excel->getActiveSheet()->SetCellValue('M1', lang('Phụ Thu'));				
				$this->excel->getActiveSheet()->SetCellValue('N1', lang('Tổng cộng'));
				$this->excel->getActiveSheet()->SetCellValue('O1', lang('Đã thanh toán'));
				$this->excel->getActiveSheet()->SetCellValue('P1', lang('Dư nợ'));				
				$this->excel->getActiveSheet()->SetCellValue('Q1', lang('payment_status'));
				$this->excel->getActiveSheet()->SetCellValue('R1', lang('Ghi Chú'));

                $row = 2;
                $total = 0;
                $paid = 0;
                $balance = 0;
				$dathanhtoan=0;
				$duno=0;
				$tongcong=0;
				$_giam=0; $_thue=0; $_ship=0;
                foreach ($data as $data_row) {
					
					$customer= $this->site->getCompanyByID($data_row->customer_id); 
					$_customer=$customer->phone."-".$customer->name;
					
											
					//$tensanpham=$this->defineTenSanPhamExport($data_row->iname);
					
                    $this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->sma->hrld($data_row->date));
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->reference_no);
                    $this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->kho);
					$this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->doitac);
					$this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->biller);
                    $this->excel->getActiveSheet()->SetCellValue('F' . $row, $_customer);
                    $this->excel->getActiveSheet()->SetCellValue('G' . $row, $data_row->item_nane);
                    $this->excel->getActiveSheet()->SetCellValue('H' . $row, $data_row->soluong);
                    $this->excel->getActiveSheet()->SetCellValue('I' . $row,$data_row->giaban);
                    $this->excel->getActiveSheet()->SetCellValue('J' . $row,($data_row->soluong*$data_row->giaban));				
					$this->excel->getActiveSheet()->SetCellValue('K' . $row,$data_row->total_tax);
					$this->excel->getActiveSheet()->SetCellValue('L' . $row,$data_row->shipping);
					$this->excel->getActiveSheet()->SetCellValue('M' . $row,$data_row->total_discount);					
					$this->excel->getActiveSheet()->SetCellValue('N' . $row,$data_row->grand_total);
					$this->excel->getActiveSheet()->SetCellValue('O' . $row,$data_row->paid);
					$this->excel->getActiveSheet()->SetCellValue('P' . $row,$data_row->balance);					
                    $this->excel->getActiveSheet()->SetCellValue('Q' . $row, lang($data_row->payment_status));
					$this->excel->getActiveSheet()->SetCellValue('R' . $row, $data_row->note);
                    $total += $data_row->soluong;
                    
                    $balance += ($data_row->soluong * $data_row->giaban);
					$_giam+=$data_row->total_discount; $_thue+=$data_row->total_tax; $_ship+=$data_row->shipping;
					$dathanhtoan+= $data_row->paid;
					$duno+= ($data_row->grand_total - $data_row->paid);
					$tongcong+= $data_row->grand_total;
						
                    $row++;
                }
                $this->excel->getActiveSheet()->getStyle("H" . $row . ":P" . $row)->getBorders()
                    ->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);
                $this->excel->getActiveSheet()->SetCellValue('H' . $row, $total); 		
				$this->excel->getActiveSheet()->SetCellValue('K' . $row, $_thue);
				$this->excel->getActiveSheet()->SetCellValue('L' . $row, $_ship);
				$this->excel->getActiveSheet()->SetCellValue('M' . $row, $_giam);	
								
                $this->excel->getActiveSheet()->SetCellValue('P' . $row, $balance);
				$this->excel->getActiveSheet()->SetCellValue('N' . $row, $tongcong);
				$this->excel->getActiveSheet()->SetCellValue('O' . $row, $dathanhtoan);
				$this->excel->getActiveSheet()->SetCellValue('P' . $row, $duno);
				

                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(30);
                $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(35);
                $this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('I')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('J')->setWidth(20);
                $filename = 'BaocaoThuHoiCongNoChiTiet';
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
                    $this->excel->getActiveSheet()->getStyle('E2:E' . $row)->getAlignment()->setWrapText(true);
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

        } else {

            $si = "( SELECT sale_id, product_id, serial_no,CONCAT({$this->db->dbprefix('sale_items')}.product_name,'-',{$this->db->dbprefix('sale_items')}.product_unit_code) as item_nane,scodeweb_sale_items.unit_quantity as soluong,scodeweb_sale_items.unit_price as giaban FROM {$this->db->dbprefix('sale_items')} ";
            if ($product) {
                $si .= " WHERE {$this->db->dbprefix('sale_items')}.product_id = {$product} ";
            }
           // $si .= " GROUP BY {$this->db->dbprefix('sale_items')}.sale_id ) FSI";
			 $si .= "  ) FSI";
            $this->load->library('datatables');
            $this->datatables
                ->select("CONCAT(DATE_FORMAT(date, '%Y-%m-%d %T'),'<br/>',reference_no) as date,(select name from scodeweb_warehouses where id=scodeweb_sales.warehouse_id) as kho,(select name from scodeweb_doitac where id=scodeweb_sales.doitac) as doitac, biller, (SELECT CONCAT(scodeweb_companies.name,'-',scodeweb_companies.phone) FROM scodeweb_companies WHERE id=scodeweb_sales.customer_id) as customer, FSI.item_nane as iname,FSI.soluong as soluong,FSI.giaban as giaban,(FSI.soluong*FSI.giaban) as thanhtien,total_tax,shipping,(total_discount+order_discount) as total_discount,grand_total, paid,(grand_total-paid) as balance,payment_status,note, {$this->db->dbprefix('sales')}.id as id", FALSE)
                ->from('sales')
                ->join($si, 'FSI.sale_id=sales.id', 'left')
                ->join('warehouses', 'warehouses.id=sales.warehouse_id', 'left');
                // ->group_by('sales.id');
			$this->datatables->where('sales.sale_status','returned');
            if ($user) {
                $this->datatables->where('sales.created_by', $user);
            }
            if ($product) {
                $this->datatables->where('FSI.product_id', $product, FALSE);
            }
            if ($serial) {
                $this->datatables->like('FSI.serial_no', $serial, FALSE);
            }
            if ($biller) {
                $this->datatables->where('sales.biller_id', $biller);
            }
            if ($customer) {
                $this->datatables->where('sales.customer_id', $customer);
            }
            if ($warehouse) {
                $this->datatables->where('sales.warehouse_id', $warehouse);
            }
            if ($reference_no) {
                $this->datatables->like('sales.reference_no', $reference_no, 'both');
            }
            if ($start_date) {
                $this->datatables->where($this->db->dbprefix('sales').'.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
            }
			
			//echo $this->db->_compile_select(); 
            echo $this->datatables->generate();

        }
    }
	function getPurchasesReturnLhsonReport($pdf = NULL, $xls = NULL)
    {
        $this->sma->checkPermissions('purchases', TRUE);

        $product = $this->input->get('product') ? $this->input->get('product') : NULL;
        $user = $this->input->get('user') ? $this->input->get('user') : NULL;
        $supplier = $this->input->get('supplier') ? $this->input->get('supplier') : NULL;
        $warehouse = $this->input->get('warehouse') ? $this->input->get('warehouse') : NULL;
        $reference_no = $this->input->get('reference_no') ? $this->input->get('reference_no') : NULL;
        $start_date = $this->input->get('start_date') ? $this->input->get('start_date') : NULL;
        $end_date = $this->input->get('end_date') ? $this->input->get('end_date') : NULL;

        if ($start_date) {
            $start_date = $this->sma->fld($start_date);
            $end_date = $this->sma->fld($end_date);
        }
        if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $user = $this->session->userdata('user_id');
        }

        if ($pdf || $xls) {

            $this->db->select("" . $this->db->dbprefix('purchases') . ".date, reference_no, " . $this->db->dbprefix('warehouses') . ".name as wname,(SELECT CONCAT({$this->db->dbprefix('doitac')}.code, '-', {$this->db->dbprefix('doitac')}.name) FROM {$this->db->dbprefix('doitac')} WHERE id=doitac) as doitac, supplier,(select concat(first_name,' ',last_name) FROM scodeweb_users WHERE id={$this->db->dbprefix('purchases')}.created_by) as nhanvien, GROUP_CONCAT(CONCAT(" . $this->db->dbprefix('purchase_items') . ".product_name, ' (', " . $this->db->dbprefix('purchase_items') . ".unit_quantity, ')') SEPARATOR '\n') as iname, grand_total, paid, " . $this->db->dbprefix('purchases') . ".status," . $this->db->dbprefix('purchase_items') . ".product_unit_code", FALSE)
                ->from('purchases')
                ->join('purchase_items', 'purchase_items.purchase_id=purchases.id', 'left')
                ->join('warehouses', 'warehouses.id=purchases.warehouse_id', 'left')
                ->group_by('purchases.id')
                ->order_by('purchases.date desc');
				
			$this->db->where('purchases.status', 'returned');
			
            if ($user) {
                $this->db->where('purchases.created_by', $user);
            }
            if ($product) {
                $this->db->where('purchase_items.product_id', $product);
            }
            if ($supplier) {
                $this->db->where('purchases.supplier_id', $supplier);
            }
            if ($warehouse) {
                $this->db->where('purchases.warehouse_id', $warehouse);
            }
            if ($reference_no) {
                $this->db->like('purchases.reference_no', $reference_no, 'both');
            }
            if ($start_date) {
                $this->db->where($this->db->dbprefix('purchases').'.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
            }
			
            $q = $this->db->get();
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
                $this->excel->getActiveSheet()->setTitle(lang('purchase_report'));
                $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                $this->excel->getActiveSheet()->SetCellValue('B1', lang('reference_no'));
                $this->excel->getActiveSheet()->SetCellValue('C1', lang('warehouse'));
				$this->excel->getActiveSheet()->SetCellValue('D1', lang('ĐVGH'));
                $this->excel->getActiveSheet()->SetCellValue('E1', lang('supplier'));
				$this->excel->getActiveSheet()->SetCellValue('F1', lang('Nhân viên'));
                $this->excel->getActiveSheet()->SetCellValue('G1', lang('product_qty'));
                $this->excel->getActiveSheet()->SetCellValue('H1', lang('grand_total'));
                $this->excel->getActiveSheet()->SetCellValue('I1', lang('paid'));
                $this->excel->getActiveSheet()->SetCellValue('J1', lang('balance'));
                $this->excel->getActiveSheet()->SetCellValue('K1', lang('status'));
				$this->excel->getActiveSheet()->SetCellValue('L1', lang('unit'));

                $row = 2;
                $total = 0;
                $paid = 0;
                $balance = 0;
                foreach ($data as $data_row) {
                    $this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->sma->hrld($data_row->date));
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->reference_no);
                    $this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->wname);
                    $this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->doitac);
					$this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->supplier);
					$this->excel->getActiveSheet()->SetCellValue('F' . $row, $data_row->nhanvien);
                    $this->excel->getActiveSheet()->SetCellValue('G' . $row, $data_row->iname);
                    $this->excel->getActiveSheet()->SetCellValue('H' . $row, $data_row->grand_total);
                    $this->excel->getActiveSheet()->SetCellValue('I' . $row, $data_row->paid);
                    $this->excel->getActiveSheet()->SetCellValue('J' . $row, ($data_row->grand_total - $data_row->paid));
                    $this->excel->getActiveSheet()->SetCellValue('K' . $row, lang($data_row->status));
					$this->excel->getActiveSheet()->SetCellValue('L' . $row, $data_row->product_unit_code);
                    $total += $data_row->grand_total;
                    $paid += $data_row->paid;
                    $balance += ($data_row->grand_total - $data_row->paid);
                    $row++;
                }
                $this->excel->getActiveSheet()->getStyle("H" . $row . ":J" . $row)->getBorders()
                    ->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);
                $this->excel->getActiveSheet()->SetCellValue('H' . $row, $total);
                $this->excel->getActiveSheet()->SetCellValue('I' . $row, $paid);
                $this->excel->getActiveSheet()->SetCellValue('J' . $row, $balance);

                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(30);
                $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('I')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('J')->setWidth(20);
                $filename = 'BaoCaoTraHangNCC';
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
                    $this->excel->getActiveSheet()->getStyle('E2:E' . $row)->getAlignment()->setWrapText(true);
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

        } else {

            $pi = "( SELECT purchase_id, product_id, (GROUP_CONCAT(CONCAT(CONCAT({$this->db->dbprefix('purchase_items')}.product_name,'-',{$this->db->dbprefix('purchase_items')}.product_unit_code), '__', {$this->db->dbprefix('purchase_items')}.unit_quantity) SEPARATOR '___')) as item_nane from {$this->db->dbprefix('purchase_items')} ";
            if ($product) {
                $pi .= " WHERE {$this->db->dbprefix('purchase_items')}.product_id = {$product} ";
            }
            $pi .= " GROUP BY {$this->db->dbprefix('purchase_items')}.purchase_id ) FPI";

            $this->load->library('datatables');
            $this->datatables
                ->select("DATE_FORMAT({$this->db->dbprefix('purchases')}.date, '%Y-%m-%d %T') as date, reference_no, {$this->db->dbprefix('warehouses')}.name as wname,(SELECT CONCAT({$this->db->dbprefix('doitac')}.code, '-', {$this->db->dbprefix('doitac')}.name) FROM {$this->db->dbprefix('doitac')} WHERE id=doitac) as doitac, supplier,(select concat(first_name,' ',last_name) FROM scodeweb_users WHERE id={$this->db->dbprefix('purchases')}.created_by) as nhanvien, (FPI.item_nane) as iname, grand_total, paid, (grand_total-paid) as balance, {$this->db->dbprefix('purchases')}.status, {$this->db->dbprefix('purchases')}.id as id", FALSE)
                ->from('purchases')
                ->join($pi, 'FPI.purchase_id=purchases.id', 'left')
                ->join('warehouses', 'warehouses.id=purchases.warehouse_id', 'left');
                // ->group_by('purchases.id');
			
			$this->datatables->where('purchases.status', 'returned');
			
            if ($user) {
                $this->datatables->where('purchases.created_by', $user);
            }
            if ($product) {
                $this->datatables->where('FPI.product_id', $product, FALSE);
            }
            if ($supplier) {
                $this->datatables->where('purchases.supplier_id', $supplier);
            }
            if ($warehouse) {
                $this->datatables->where('purchases.warehouse_id', $warehouse);
            }
            if ($reference_no) {
                $this->datatables->like('purchases.reference_no', $reference_no, 'both');
            }
            if ($start_date) {
                $this->datatables->where($this->db->dbprefix('purchases').'.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
            }
		
            echo $this->datatables->generate();

        }
    }
    function getSalesReportTheoNgay($pdf = NULL, $xls = NULL)
    {
        $this->sma->checkPermissions('payments', TRUE);

        $customer = $this->input->get('customer') ? $this->input->get('customer') : NULL;
        
        $warehouse_id = $this->input->get('warehouse_id') ? $this->input->get('warehouse_id') : NULL;
        $start_date = $this->input->get('start_date') ? $this->input->get('start_date') : NULL;
        $end_date = $this->input->get('end_date') ? $this->input->get('end_date') : NULL;
        
        $locdk="";
        if ($start_date) {
            $start_date = $this->sma->fsd($start_date);
            $end_date = $this->sma->fsd($end_date);
        }
        if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $user = $this->session->userdata('user_id');
        }
        if ($pdf || $xls) {

            $this->load->library('datatables');
            $this->db->select("date(" . $this->db->dbprefix('payments') . ".date) as date,(select name from scodeweb_warehouses where id=scodeweb_sales.warehouse_id) as kho,0 as 'muahang',amount,customer_id,". $this->db->dbprefix('payments') . ".id as id,0 as warehouse_id")
                ->from('payments')
                ->join('sales', 'payments.sale_id=sales.id', 'left')
                ->group_by('payments.id');
          
            
            if ($customer) {
                $this->db->where('sales.customer_id', $customer);
            }
            if ($warehouse_id) {
                $this->db->where('payments.warehouse_id', $warehouse_id);
            }
            if ($start_date) {
                $locdk.=' AND date(date) BETWEEN "' . $start_date . '" and "' . $end_date . '"';
                $addwhere.=' AND date(date) BETWEEN "' . $start_date . '" and "' . $end_date . '"';
            }
             
            $report_query_ncc=$this->db->get_compiled_select();
                        
            $lshonquery="SELECT date(".$this->db->dbprefix('payments') . ".date) as date,(select name from scodeweb_warehouses where id=scodeweb_payments.warehouse_id) as kho,0 as 'muahang', amount,id_ncc_id_kh as customer_id,scodeweb_payments.id as id,0 as warehouse_id FROM ".$this->db->dbprefix('payments');
                         
            
            if ($customer) {
                $lshonquery.=" WHERE type='received' AND ".$this->db->dbprefix('payments').".id_ncc_id_kh=".$customer;
            }
            if ($warehouse_id) {
                $lshonquery.=" AND ".$this->db->dbprefix('payments').".warehouse_id=".$warehouse_id;
            }   
            $this->db->where('payments.id_ncc_id_kh >',0);
            
          
            
            
            if ($warehouse_id) {
                $addwhere.=" AND warehouse_id=".$warehouse_id;
            }
               
         
            $query_ban="SELECT DATE_FORMAT(date, '%Y-%m-%d') as date, (select name from scodeweb_warehouses where id=scodeweb_sales.warehouse_id) as kho,sum(grand_total) as 'muahang', 0 as 'amount', customer_id, {$this->db->dbprefix('sales')}.id as id,warehouse_id FROM scodeweb_sales WHERE sale_status !='returned' AND customer_id='".$customer."' $addwhere GROUP BY date(scodeweb_sales.date)";
            
            
            $query_ok="SELECT tbl.date,tbl.kho,sum(muahang) as muahang,sum(tbl.amount) as amount,0 as conno_theongay_lhson,customer_id FROM ($report_query_ncc UNION $lshonquery UNION $query_ban) as tbl WHERE id>0 $locdk GROUP BY tbl.date ORDER BY tbl.date desc";              

            $q=$this->db->query($query_ok);     
             
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
                $this->excel->getActiveSheet()->setTitle(lang('thong_ke_khach_hang_chi_tiet'));
                $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                $this->excel->getActiveSheet()->SetCellValue('B1', lang('Kho'));
                $this->excel->getActiveSheet()->SetCellValue('C1', lang('Sản phẩm'));
                $this->excel->getActiveSheet()->SetCellValue('D1', lang('Mua Hàng'));
                $this->excel->getActiveSheet()->SetCellValue('E1', lang('Thanh toán'));
                $this->excel->getActiveSheet()->SetCellValue('F1', lang('Công nợ'));

                $row = 2;
                $total_mua = 0;
                $total_tt = 0;
                $total_cn = 0;

                foreach ($data as $data_row) {

                    $sanpham=$this->getTenSPTheoNgayCusReport($data_row->customer_id,$data_row->date,$data_row->warehouse_id);

                    $this->excel->getActiveSheet()->SetCellValue('A' . $row, $data_row->date);
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->kho);
                    $this->excel->getActiveSheet()->SetCellValue('C' . $row, $sanpham);
                    $this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->muahang);
                    $this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->amount);
                    

                    $dathanhtoan=(float)$this->reports_model->getPaymentFrom($data_row->customer_id,$data_row->date);
                    $dathanhtoan_theohd=$this->reports_model->getDathanhtoanhd($data_row->customer_id,$data_row->date);
                    
                    $damuahang=(float)$dathanhtoan_theohd->grand_total;

                    $dathanhtoan+=(float)$dathanhtoan_theohd->total_paid;

                    $conno=$damuahang-$dathanhtoan;

                    $total_mua+= $data_row->muahang;
                    $total_tt+= $data_row->amount;
                    

                    $this->excel->getActiveSheet()->SetCellValue('F' . $row, $conno);

                    $row++;
                }
                $this->excel->getActiveSheet()->getStyle("D" . $row)->getBorders()
                    ->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);
                $this->excel->getActiveSheet()->SetCellValue('D' . $row, $total_mua);

                $this->excel->getActiveSheet()->getStyle("E" . $row)->getBorders()
                    ->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);
                $this->excel->getActiveSheet()->SetCellValue('E' . $row, $total_tt);
                
                $this->excel->getActiveSheet()->getStyle("F" . $row)->getBorders()
                    ->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);
                $this->excel->getActiveSheet()->SetCellValue('F' . $row, ($total_mua-$total_tt)); 
                

                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(35);
                $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(20);
                $filename = 'thong_ke_khach_hang';
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

        } else {

            $this->load->library('datatables');
            $this->db->select("date(" . $this->db->dbprefix('payments') . ".date) as date,(select name from scodeweb_warehouses where id=scodeweb_sales.warehouse_id) as kho,'' as sanphamtheongaycus, 0 as 'muahang',amount,customer_id,". $this->db->dbprefix('payments') . ".id as id,0 as warehouse_id")
                ->from('payments')
                ->join('sales', 'payments.sale_id=sales.id', 'left')
                ->group_by('payments.id');
          
            
            if ($customer) {
                $this->db->where('sales.customer_id', $customer);
            }
            if ($warehouse_id) {
                $this->db->where('payments.warehouse_id', $warehouse_id);
            }
            if ($start_date) {
                $locdk.=' AND date(date) BETWEEN "' . $start_date . '" and "' . $end_date . '"';
                $addwhere.=' AND date(date) BETWEEN "' . $start_date . '" and "' . $end_date . '"';
            }
             
            $report_query_ncc=$this->db->get_compiled_select();
                        
            $lshonquery="SELECT date(".$this->db->dbprefix('payments') . ".date) as date,(select name from scodeweb_warehouses where id=scodeweb_payments.warehouse_id) as kho,'' as sanphamtheongaycus, 0 as 'muahang', amount,id_ncc_id_kh as customer_id,scodeweb_payments.id as id,0 as warehouse_id FROM ".$this->db->dbprefix('payments');                         
            
            if ($customer) {
                $lshonquery.=" WHERE type='received' AND ".$this->db->dbprefix('payments').".id_ncc_id_kh=".$customer;
            }
            if ($warehouse_id) {
                $lshonquery.=" AND ".$this->db->dbprefix('payments').".warehouse_id=".$warehouse_id;
            }   
            $this->db->where('payments.id_ncc_id_kh >',0);
                     
            

           if ($warehouse_id) {
                $addwhere.=" AND warehouse_id=".$warehouse_id;
            }
            
         
           $query_ban="SELECT DATE_FORMAT(date, '%Y-%m-%d') as date, (select name from scodeweb_warehouses where id=scodeweb_sales.warehouse_id) as kho,'' as sanphamtheongaycus, sum(grand_total) as 'muahang', 0 as 'amount', customer_id, {$this->db->dbprefix('sales')}.id as id,warehouse_id FROM scodeweb_sales WHERE sale_status !='returned' AND customer_id='".$customer."' $addwhere GROUP BY date(scodeweb_sales.date)";

            
                           
            
            $query_ok="SELECT tbl.date,tbl.kho,tbl.sanphamtheongaycus,sum(muahang) as muahang,sum(tbl.amount) as amount,0 as conno_theongay_lhson,customer_id FROM ($report_query_ncc UNION $lshonquery UNION $query_ban) as tbl WHERE id>0 $locdk GROUP BY tbl.date ORDER BY tbl.date desc";              
            
            echo $rs=$this->datatables->generate_querylhson($output = 'json', $charset = 'UTF-8',$query_ok);
        }

    }
    function getTenSPTheoNgayCusReport($customer_id=0,$date='',$warehouse_id=null){
       $sql="SELECT GROUP_CONCAT(CONCAT(`product_name`,' (',`product_unit_code`,') ',FORMAT(`unit_quantity`,1),'*',FORMAT(`unit_price`,0),'=',FORMAT(`subtotal`,0)) SEPARATOR '\n\t') as sanpham FROM `scodeweb_sale_items` i,scodeweb_sales f where i.sale_id=f.id and f.customer_id=$customer_id and date(f.date)='$date'";
        $query = $this->db->query($sql);  
         if ($warehouse_id) {
                $this->db->where('sales.warehouse_id', $warehouse_id);
            }     
        return $query->row_array()['sanpham'];
    }
     function salesloinhuan()
    {
        $this->sma->checkPermissions('sales');
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['users'] = $this->reports_model->getStaff();
        $this->data['warehouses'] = $this->site->getAllWarehouses();
        $this->data['billers'] = $this->site->getAllCompanies('biller');
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('Lợi nhuận HĐ')));
        $meta = array('page_title' => lang('Lợi nhuận HĐ'), 'bc' => $bc);
        $this->page_construct('reports/salesloinhuan', $meta, $this->data); 
    }
    function getSalesReportLoiNhuan($pdf = NULL, $xls = NULL)
    {
        $this->sma->checkPermissions('sales', TRUE);
        $product = $this->input->get('product') ? $this->input->get('product') : NULL;
        $user = $this->input->get('user') ? $this->input->get('user') : NULL;
        $customer = $this->input->get('customer') ? $this->input->get('customer') : NULL;
        $biller = $this->input->get('biller') ? $this->input->get('biller') : NULL;
        $warehouse = $this->input->get('warehouse') ? $this->input->get('warehouse') : NULL;
        $reference_no = $this->input->get('reference_no') ? $this->input->get('reference_no') : NULL;
        $start_date = $this->input->get('start_date') ? $this->input->get('start_date') : NULL;
        $end_date = $this->input->get('end_date') ? $this->input->get('end_date') : NULL;
        $serial = $this->input->get('serial') ? $this->input->get('serial') : NULL;

        if ($start_date) {
            $start_date = $this->sma->fld($start_date);
            $end_date = $this->sma->fld($end_date);
        }
        if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $user = $this->session->userdata('user_id');
        }

        if ($pdf || $xls) {

            

        } else {

            $si = "( SELECT sale_id, product_id, serial_no, GROUP_CONCAT(CONCAT(CONCAT({$this->db->dbprefix('sale_items')}.product_name,'-',{$this->db->dbprefix('sale_items')}.product_unit_code), '__',CONCAT(scodeweb_sale_items.unit_quantity,'__',scodeweb_sale_items.unit_price)) SEPARATOR '___') as item_nane from {$this->db->dbprefix('sale_items')} ";
            if ($product) {
                $si .= " WHERE {$this->db->dbprefix('sale_items')}.product_id = {$product} ";
            }
            $si .= " GROUP BY {$this->db->dbprefix('sale_items')}.sale_id ) FSI";
            $this->load->library('datatables');
            $this->datatables
                ->select("DATE_FORMAT(date, '%Y-%m-%d %T') as date, reference_no,(select name from scodeweb_warehouses where id=scodeweb_sales.warehouse_id) as kho,(SELECT CONCAT({$this->db->dbprefix('doitac')}.code, '-', {$this->db->dbprefix('doitac')}.name) FROM {$this->db->dbprefix('doitac')} WHERE id=doitac) as doitac, biller, scodeweb_companies.name as customer, FSI.item_nane as iname, grand_total, paid, (grand_total-paid) as balance, 0 as giavondt,0 as loinhuan,payment_status, {$this->db->dbprefix('sales')}.id as id", FALSE)
                ->from('sales')
                ->join($si, 'FSI.sale_id=sales.id', 'left')
                ->join('companies', 'companies.id=sales.customer_id', 'left')
                ->join('warehouses', 'warehouses.id=sales.warehouse_id', 'left');
                // ->group_by('sales.id');
            $this->datatables->where('sales.sale_status !=','returned');
            if ($user) {
                $this->datatables->where('sales.created_by', $user);
            }
            if ($product) {
                $this->datatables->where('FSI.product_id', $product, FALSE);
            }
            if ($serial) {
                $this->datatables->like('FSI.serial_no', $serial, FALSE);
            }
            if ($biller) {
                $this->datatables->where('sales.biller_id', $biller);
            }
            if ($customer) {
                $this->datatables->where('sales.customer_id', $customer);
            }
            if ($warehouse) {
                $this->datatables->where('sales.warehouse_id', $warehouse);
            }
            if ($reference_no) {
                $this->datatables->like('sales.reference_no', $reference_no, 'both');
            }
            if ($start_date) {
                $this->datatables->where($this->db->dbprefix('sales').'.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
            }

            echo $this->datatables->generate();

        }

    }
     function getSalesReportTheoHoaDon($pdf = NULL, $xls = NULL)
    {
        $this->sma->checkPermissions('payments', TRUE);
        $customer = $this->input->get('customer') ? $this->input->get('customer') : NULL;        
        $warehouse_id = $this->input->get('warehouse_id') ? $this->input->get('warehouse_id') : NULL;
        $start_date = $this->input->get('start_date') ? $this->input->get('start_date') : NULL;
        $end_date = $this->input->get('end_date') ? $this->input->get('end_date') : NULL;
        
        $locdk="";
        if ($start_date) {
            $start_date = date("Y-m-d",strtotime($start_date));
            $end_date = date("Y-m-d",strtotime($end_date));
        }
        if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $user = $this->session->userdata('user_id');
        }
        if ($pdf || $xls) {

            $this->load->library('datatables');
            if ($start_date) {
               $addwhere.=' AND date(a.date) BETWEEN "' . $start_date . '" and "' . $end_date . '"';
            }
             if ($customer) {
                $addwhere.=" AND a.customer_id=".$customer;
            }
            if ($warehouse_id) {
                $addwhere.=" AND a.warehouse_id=".$warehouse_id;
            } 

           $lshonquery="SELECT date(a.date) as date,(select name from scodeweb_warehouses where id=a.warehouse_id) as kho,product_id,product_name,product_unit_code,unit_quantity,unit_price,(unit_quantity*unit_price) as total,a.id as id FROM ".$this->db->dbprefix('sales')." as a,scodeweb_sale_items as b WHERE a.id=b.sale_id $addwhere order by a.id desc,b.id desc";         

            $q=$this->db->query($lshonquery);     
             
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

                $this->excel->getActiveSheet()->setTitle(lang('thong_ke_hoadon_chi_tiet'));
                
                $styleArray = array(
                    'font' => array(
                        'bold' => true,
                        'size' => 24,
                        'color' => array('rgb' => '2F4F4F')
                    ),
                    'alignment' => array(
                        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    )
                );
                $styleArray2 = array(
                    'font' => array(
                        'bold' => true,
                        'size' => 18,
                        'color' => array('rgb' => '2F4F4F')
                    ),
                    'alignment' => array(
                        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    )
                );

                $styleArrayBold = array(
                    'font' => array(
                        'bold' => true
                    ),
                    'alignment' => array(
                        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
                    )
                );
                $styleArrayRight = array(
                    'font' => array(
                    ),
                    'alignment' => array(
                        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
                    )
                );
                $styleArrayBoldCenter = array(
                    'font' => array(
                        'bold' => true
                    ),
                    'alignment' => array(
                        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    )
                );

                $khachhang = $this->companies_model->getCompanyByID($customer); 
                $nocu=$khachhang->nobandau;
                $giamgia=0;
                $dathanhtoan=0;
                if ($start_date!=''&&$end_date!='') {
                    
                    $_date=date("Y-m-d",strtotime($start_date." -1 days"));
                    //tinh no cu den truoc ngay cuoi cung
                    $sales= $this->reports_model->getSalesTotals($customer,$_date);

                    $sales->paid=$sales->paid+$this->reports_model->getSalesTotalsLhson($customer,$_date);                    
                    $nocu=($sales->total_amount-$sales->paid)+$nocu;

                    
                    $sales2= $this->reports_model->getSalesTotalsL($customer,$start_date,$end_date);                    
                    $dathanhtoan=$sales2->paid;
                    $giamgia=$sales2->total_discount;
                                      

                }else{
                    $sales= $this->reports_model->getSalesTotalsL($customer,NULL);   
                    $sales->paid=$sales->paid+$this->reports_model->getSalesTotalsLhson($customer,NULL);     
                    $dathanhtoan=$sales->paid;             
                    $giamgia=$sales->total_discount; 
                }
            
                $this->excel->getActiveSheet()->SetCellValue('A1', 'HÓA ĐƠN BÁN HÀNG');
                $this->excel->getActiveSheet()->mergeCells('A1:G1');
                $this->excel->getActiveSheet()->getStyle('A1:G1')->applyFromArray($styleArray);

                $this->excel->getActiveSheet()->SetCellValue('B2', $khachhang->name);
                $this->excel->getActiveSheet()->mergeCells('B2:F2');
                $this->excel->getActiveSheet()->getStyle('B2:F2')->applyFromArray($styleArray2);

                $this->excel->getActiveSheet()->SetCellValue('A3', lang('Ngày tạo').": ".date("d/m/Y"));
                $this->excel->getActiveSheet()->SetCellValue('B3', '');
                $this->excel->getActiveSheet()->SetCellValue('C3','');
                $this->excel->getActiveSheet()->SetCellValue('D3', '');
                $this->excel->getActiveSheet()->SetCellValue('E3', '');
                $this->excel->getActiveSheet()->SetCellValue('F3', '');
                $this->excel->getActiveSheet()->SetCellValue('G3', lang('Nợ cũ').": ".number_format($nocu)."đ");

                if ($start_date&&$end_date!='') {
                    
                    $this->excel->getActiveSheet()->SetCellValue('C3'," Từ ".date("d/m/Y",strtotime($start_date))." đến ".date("d/m/Y",strtotime($end_date)));
                    $this->excel->getActiveSheet()->mergeCells('C3:E3');
                    $this->excel->getActiveSheet()->getStyle('C3:E3')->applyFromArray($styleArrayBoldCenter);

                }


                $this->excel->getActiveSheet()->getStyle('G3')->applyFromArray($styleArrayBold);

                $this->excel->getActiveSheet()->setTitle(lang('thong_ke_hoadon_chi_tiet'));
                $this->excel->getActiveSheet()->SetCellValue('A4', lang('date'));
                $this->excel->getActiveSheet()->SetCellValue('B4', lang('Kho'));
                $this->excel->getActiveSheet()->SetCellValue('C4', lang('Tên sản phẩm'));
                $this->excel->getActiveSheet()->SetCellValue('D4', lang('ĐVT'));
                $this->excel->getActiveSheet()->SetCellValue('E4', lang('Số lượng'));
                $this->excel->getActiveSheet()->SetCellValue('F4', lang('Giá bán'));
                $this->excel->getActiveSheet()->SetCellValue('G4', lang('Thành tiền'));
                $this->excel->getActiveSheet()->getStyle('A4:G4')->applyFromArray($styleArrayBoldCenter);

                $row = 5;
                $sl = 0;
                $total_tt = 0;

                foreach ($data as $data_row) {
                    $this->excel->getActiveSheet()->SetCellValue('A' . $row, $data_row->date);
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->kho);
                    $this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->product_name);
                    $this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->product_unit_code);
                    $this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->unit_quantity);
                    $this->excel->getActiveSheet()->SetCellValue('F' . $row, number_format($data_row->unit_price));
                    $this->excel->getActiveSheet()->SetCellValue('G' . $row, number_format($data_row->total));
                    
                    $this->excel->getActiveSheet()->getStyle('F' . $row.':'.'G' . $row)->applyFromArray($styleArrayRight);

                    $sl+= $data_row->unit_quantity;
                    $total_tt+= $data_row->total;                    


                    $row++;
                }

                $total_tt+=$nocu;
                
                $this->excel->getActiveSheet()->getStyle('F' . $row)->applyFromArray($styleArrayBold);
                $this->excel->getActiveSheet()->SetCellValue('F' . $row, 'Giảm giá: ');
                                              
                $this->excel->getActiveSheet()->getStyle('G' . $row)->applyFromArray($styleArrayBold);
                $this->excel->getActiveSheet()->SetCellValue('G' . $row, number_format($giamgia)); 
                $total_tt=$total_tt-$giamgia-$dathanhtoan;
                $row++;
                $this->excel->getActiveSheet()->getStyle('F' . $row)->applyFromArray($styleArrayBold);
                $this->excel->getActiveSheet()->SetCellValue('F' . $row, 'Đã TT: ');
                                              
                $this->excel->getActiveSheet()->getStyle('G' . $row)->applyFromArray($styleArrayBold);
                $this->excel->getActiveSheet()->SetCellValue('G' . $row, number_format($dathanhtoan)); 
                
                $row++;

                $this->excel->getActiveSheet()->getStyle("E" . $row)->getBorders()
                    ->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);
                $this->excel->getActiveSheet()->SetCellValue('E' . $row, number_format($sl));
                
                $this->excel->getActiveSheet()->getStyle("G" . $row)->getBorders()
                    ->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);
                $this->excel->getActiveSheet()->SetCellValue('G' . $row, number_format($total_tt)); 

                $this->excel->getActiveSheet()->getStyle('G' . $row)->applyFromArray($styleArrayBold);
                

                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
                $filename = 'thong_ke_khach_hang_sanpham';
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

        } else {

            $this->load->library('datatables');
           
            if ($start_date) {
                $addwhere.=' AND date(a.date) BETWEEN "' . $start_date . '" and "' . $end_date . '"';
            }
             if ($customer) {
                $addwhere.=" AND a.customer_id=".$customer;
            }
            if ($warehouse_id) {
                $addwhere.=" AND a.warehouse_id=".$warehouse_id;
            } 

            $lshonquery="SELECT date(a.date) as date,(select name from scodeweb_warehouses where id=a.warehouse_id) as kho,product_id,product_name,product_unit_code,unit_quantity,unit_price,(unit_quantity*unit_price) as total,a.id as id FROM ".$this->db->dbprefix('sales')." as a,scodeweb_sale_items as b WHERE a.id=b.sale_id $addwhere order by a.id desc,b.id desc";     
            
            echo $rs=$this->datatables->generate_querylhson($output = 'json', $charset = 'UTF-8',$lshonquery);
        }

    }
    function getSalesReportBySanPhamKM($pdf = NULL, $xls = NULL)
    {
        $this->sma->checkPermissions('sales', TRUE);
        $product = $this->input->get('product') ? $this->input->get('product') : NULL;
        
        $warehouse = $this->input->get('warehouse') ? $this->input->get('warehouse') : NULL;

              
        $start_date = $this->input->get('start_date') ? $this->input->get('start_date') : NULL;
        $end_date = $this->input->get('end_date') ? $this->input->get('end_date') : NULL;

        if ($start_date) {
            $start_date = $this->sma->fld($start_date);
            $end_date = $this->sma->fld($end_date);
        }
        if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $user = $this->session->userdata('user_id');
        }

        

            $si = "( SELECT sale_id, product_id, data_id_khuyenmai,CONCAT({$this->db->dbprefix('sale_items')}.product_id,' - ',{$this->db->dbprefix('sale_items')}.product_name,'-',{$this->db->dbprefix('sale_items')}.product_unit_code) as item_nane,scodeweb_sale_items.unit_quantity as soluong,scodeweb_sale_items.unit_price as giaban FROM {$this->db->dbprefix('sale_items')} ";
            if ($product) {
               $si .= " WHERE {$this->db->dbprefix('sale_items')}.product_id = {$product} ";
            }
            
            $si .= "  ) FSI";   
                       

            $this->load->library('datatables');
            $this->datatables
                ->select("DATE_FORMAT(date, '%Y-%m-%d %T') as date,reference_no,(select name from scodeweb_warehouses where id=scodeweb_sales.warehouse_id) as kho, (SELECT CONCAT(scodeweb_companies.name,'-',scodeweb_companies.phone) FROM scodeweb_companies WHERE id=scodeweb_sales.customer_id) as customer, FSI.item_nane as iname,FSI.soluong as soluong,FSI.giaban as giaban,{$this->db->dbprefix('sales')}.id as id", FALSE)
                ->from('sales')
                ->join($si, 'FSI.sale_id=sales.id', 'left')
                ->join('warehouses', 'warehouses.id=sales.warehouse_id', 'left');
                // ->group_by('FSI.product_id');
                
              
            $this->datatables->where('sales.sale_status !=','returned');
            $this->datatables->where('FSI.data_id_khuyenmai>','0');

            if ($user) {
                $this->datatables->where('sales.created_by', $user);
            }
            if ($product) {
                $this->datatables->where('FSI.product_id', $product, FALSE);
            }
            
            if ($warehouse) {
                $this->datatables->where('sales.warehouse_id', $warehouse);
            }
           
            if ($start_date) {
                $this->datatables->where($this->db->dbprefix('sales').'.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
            }
            
            //echo $this->db->_compile_select(); 
            echo $this->datatables->generate();

        

    }
    function salesKhuyenMai()
    {
        $this->sma->checkPermissions('sales');
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['users'] = $this->reports_model->getStaff();
        $this->data['warehouses'] = $this->site->getAllWarehouses();
        $this->data['billers'] = $this->site->getAllCompanies('biller');
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('reports'), 'page' => lang('Báo cáo khuyến mãi sản phẩm')), array('link' => '#', 'page' => lang('Báo cáo khuyến mãi theo sản phẩm')));
        $meta = array('page_title' => lang('Báo cáo khuyến mãi theo sản phẩm'), 'bc' => $bc);
        $this->page_construct('reports/salekhuyenmai', $meta, $this->data);
    }
    function getSalesReportBySanPhamKMGroup($pdf = NULL, $xls = NULL)
    {
        $this->sma->checkPermissions('sales', TRUE);
        $product = $this->input->get('product') ? $this->input->get('product') : NULL;
        
        $warehouse = $this->input->get('warehouse') ? $this->input->get('warehouse') : NULL;

        $nhom = $this->input->get('nhom') ? $this->input->get('nhom') : NULL;

              
        $start_date = $this->input->get('start_date') ? $this->input->get('start_date') : NULL;
        $end_date = $this->input->get('end_date') ? $this->input->get('end_date') : NULL;

        if ($start_date) {
            $start_date = $this->sma->fld($start_date);
            $end_date = $this->sma->fld($end_date);
        }
        if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $user = $this->session->userdata('user_id');
        }

        

            $si = "( SELECT sp.group_id,data_id_khuyenmai,product_id,sale_id,warehouse_id,CONCAT({$this->db->dbprefix('sale_items')}.product_id,' - ',{$this->db->dbprefix('sale_items')}.product_name,'-',{$this->db->dbprefix('sale_items')}.product_unit_code) as item_nane,scodeweb_sale_items.unit_quantity as soluong FROM {$this->db->dbprefix('sale_items')}, scodeweb_products as sp WHERE sp.id=product_id";
            if ($product) {
               $si .= " AND {$this->db->dbprefix('sale_items')}.product_id = {$product}";
            }        
             if ($nhom) {
                $si .= " AND sp.group_id=".$nhom;
            }    
            $si .= " ) FSI";                          

            $this->load->library('datatables');
            $this->datatables
                ->select("FSI.item_nane as iname,sum(FSI.soluong) as soluong,{$this->db->dbprefix('sales')}.id as id", FALSE)
                ->from('sales')
                ->join($si, 'FSI.sale_id=sales.id', 'left')
                ->join('warehouses', 'warehouses.id=sales.warehouse_id', 'left')
                 ->group_by('FSI.product_id');                
              
            $this->datatables->where('sales.sale_status !=','returned');
            $this->datatables->where('FSI.data_id_khuyenmai>','0');

            if ($product) {
                $this->datatables->where('FSI.product_id', $product, FALSE);
            }
            
            if ($warehouse) {
                $this->datatables->where('sales.warehouse_id', $warehouse);
            }
             if ($nhom) {
                $this->datatables->where('FSI.group_id', $nhom); 
            }
           
            if ($start_date) {
                $this->datatables->where($this->db->dbprefix('sales').'.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
            }
            
            echo $this->datatables->generate(); 
            

        

    }
    function salesKhuyenMaiGroup() 
    {
        $this->sma->checkPermissions('sales');
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['users'] = $this->reports_model->getStaff();
        $this->data['warehouses'] = $this->site->getAllWarehouses();
        $this->data['nhoms'] = $this->site->getAllNhomsanpham();
        
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('reports'), 'page' => lang('Báo cáo khuyến mãi group sản phẩm')), array('link' => '#', 'page' => lang('Báo cáo khuyến mãi group sản phẩm')));
        $meta = array('page_title' => lang('Báo cáo khuyến mãi group sản phẩm'), 'bc' => $bc);
        $this->page_construct('reports/salekhuyenmaigroup', $meta, $this->data);
    }
    function getHistoryApiProducts()
    {        
        $type = $this->input->get('type') ? $this->input->get('type') : NULL;
        $start_date = $this->input->get('start_date') ? $this->input->get('start_date') : NULL;
        $end_date = $this->input->get('end_date') ? $this->input->get('end_date') : NULL;

        $product_id = $this->input->get('product_id') ? $this->input->get('product_id') : NULL;

        if ($start_date) {
            $start_date = $this->sma->fld($start_date);
            $end_date = $this->sma->fld($end_date);
        }

        $this->load->library('datatables');
        $this->datatables
            ->select("DATE_FORMAT(created, '%Y-%m-%d %T') as date,product_id as id,product_name,stock,price,type", FALSE)
            ->from('history_api_product');

        if ($type) {
            $this->datatables->where('type', $type);
        }
        if ($product_id) {
            $this->datatables->where('product_id', $product_id);
        }
        
        if ($start_date) {
            $this->datatables->where($this->db->dbprefix('history_api_product').'.created BETWEEN "' . $start_date . '" and "' . $end_date . '"');
        }
        echo $this->datatables->generate();       

    }
    function getHistoryApiOrders()
    {        
        $type = $this->input->get('type') ? $this->input->get('type') : NULL;
        $start_date = $this->input->get('start_date') ? $this->input->get('start_date') : NULL;
        $end_date = $this->input->get('end_date') ? $this->input->get('end_date') : NULL;

        $order_id = $this->input->get('order_id') ? $this->input->get('order_id') : NULL;

        if ($start_date) {
            $start_date = $this->sma->fld($start_date);
            $end_date = $this->sma->fld($end_date);
        }

        $this->load->library('datatables');
        $this->datatables
            ->select("DATE_FORMAT(created, '%Y-%m-%d %T') as date,order_id as id,order_code,api_order_id,customer_name,total_money,type", FALSE)
            ->from('history_api_orders');

        if ($type) {
            $this->datatables->where('type', $type);
        }
        if ($order_id) {
            $this->datatables->where('order_id', $order_id);
        }
        
        if ($start_date) {
            $this->datatables->where($this->db->dbprefix('history_api_orders').'.created BETWEEN "' . $start_date . '" and "' . $end_date . '"');
        }
        echo $this->datatables->generate();       

    }
    function baocaothutragop()
    {
        $this->sma->checkPermissions('index',false,'thu');
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['users'] = $this->reports_model->getStaff();
        $this->data['billers'] = $this->site->getAllCompanies('biller');
        $this->data['categories'] = $this->reports_model->getAllPTTTTraGop(1);
        $this->data['pos_settings'] = POS ? $this->reports_model->getPOSSetting('biller') : FALSE;
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => 'Báo cáo các khoản thu trả góp'));
        $meta = array('page_title' => 'Báo cáo các khoản thu trả góp', 'bc' => $bc);
        $this->page_construct('reports/khoanthu_tragop_report', $meta, $this->data);
    }

    function getBaocaothuTraGopReport($pdf = NULL, $xls = NULL)
    {
        $this->sma->checkPermissions('index',TRUE,'thu');

        $user = $this->input->get('user') ? $this->input->get('user') : NULL;
        $customer = $this->input->get('customer') ? $this->input->get('customer') : NULL;
        $category = $this->input->get('category') ? $this->input->get('category') : NULL;
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

        $_where=" WHERE tbl.id>0";
        if ($pdf || $xls) {

            $this->db->select($this->db->dbprefix('payments') . ".id," . $this->db->dbprefix('payments') . ".date, " . $this->db->dbprefix('payments') . ".reference_no as payment_ref, (CASE WHEN id_ncc_id_kh>0 THEN (SELECT CONCAT(name,'-',phone) FROM ".$this->db->dbprefix('companies') . " WHERE id=id_ncc_id_kh) ELSE " . $this->db->dbprefix('sales') . ".reference_no END) as sale_ref,(select concat(first_name,' ',last_name) FROM scodeweb_users WHERE id={$this->db->dbprefix('payments')}.created_by) as nhanvien, paid_by, amount,(CASE WHEN type='returned' THEN 'Chi thu hồi hàng bán hàng bán ' WHEN id_ncc_id_kh='0' THEN 'Thu bán hàng' WHEN id_ncc_id_kh>0 THEN 'Thu khách hàng' END) as type,{$this->db->dbprefix('payments')}.note,type_cate,id_ncc_id_kh,scodeweb_payments.created_by")
                ->from('payments')
                ->join('sales', 'payments.sale_id=sales.id', 'left')
                ->group_by('payments.id');

            if ($user) {
                $this->db->where('payments.created_by', $user);
                $_where.=" AND tbl.created_by='".$user."'";
            }
            
            if ($reference_no!='') {
                $_where.=" AND tbl.reference_no LIKE '%".$reference_no."%'";
            }
            if ($note!='') {
                $_where.=" AND tbl.note LIKE '%".$note."%'";
                
            }
            
            if ($start_date) {
                $_where.=" AND tbl.date BETWEEN '".$start_date."' AND '".$end_date."'";                          
            }
            
            $this->db->where('payments.type', 'received');
            
            $report_query_ncc=$this->db->get_compiled_select();
           // $q2 = $this->db->get();
            
            //them cac khoan thanh toan tong chp ncc lhson code
            $lshonquery="SELECT ".$this->db->dbprefix('payments') . ".id,".$this->db->dbprefix('payments') . ".date, " . $this->db->dbprefix('payments') . ".reference_no as payment_ref, (CASE WHEN type='3' THEN (SELECT CONCAT(name,'-',phone) FROM ".$this->db->dbprefix('companies') . " WHERE id=id_ncc_id_kh) WHEN type='1' THEN (SELECT CONCAT(first_name,' ',last_name) FROM ".$this->db->dbprefix('users') . " WHERE id=id_ncc_id_kh) ELSE (SELECT CONCAT(name,'-',phone) FROM ".$this->db->dbprefix('companies') . " WHERE id=id_ncc_id_kh) END) as sale_ref,(select concat(first_name,' ',last_name) FROM scodeweb_users WHERE id={$this->db->dbprefix('payments')}.created_by) as nhanvien, paid_by, amount, (CASE WHEN type='0' THEN (SELECT name FROM scodeweb_expense_categories WHERE id=type_cate) WHEN type='3' THEN 'Thu Nhà Cung Cấp' WHEN type='1' THEN 'Thu Nhân Viên' WHEN amount<0 THEN 'Chi thu hồi hàng bán hàng bán ' ELSE 'Thu bán hàng' END) as type,scodeweb_payments.note,type_cate,id_ncc_id_kh,scodeweb_payments.created_by FROM ".$this->db->dbprefix('payments');
            
            $lshonquery.=" WHERE type!='sent' AND type!='received'";
            
            if ($customer) {
                $lshonquery.=" AND type='received' AND ".$this->db->dbprefix('payments').".id_ncc_id_kh=".$customer;
            }           
            if ($user) {
                $lshonquery.=" AND payments.created_by=".$user;
            }

            $query_ok="SELECT tbl.* FROM ($report_query_ncc UNION $lshonquery) as tbl $_where ORDER BY tbl.date desc";
            
             if ((int)$category>0) {
                $_where.=" AND type_cate=".$category;                
                $query_ok="SELECT tbl.* FROM ($lshonquery) as tbl $_where ORDER BY tbl.date desc";
             }
             else if ($category=="banhang") {
                 $_where.=" AND id_ncc_id_kh=0";                 
                $query_ok="SELECT tbl.*FROM ($report_query_ncc) as tbl $_where ORDER BY tbl.date desc";
             }else if ($category=="khachhang") {
                 $_where.=" AND id_ncc_id_kh>0";
                $query_ok="SELECT tbl.* FROM ($report_query_ncc) as tbl $_where ORDER BY tbl.date desc";
             }else if ($category=="nhanvien") {
                 $_where.=" AND type='Thu Nhân Viên'";
                $query_ok="SELECT tbl.* FROM ($lshonquery) as tbl $_where ORDER BY tbl.date desc";
             }else if ($category=="nhacungcap") {
                 $_where.=" AND type='Thu Nhà Cung Cấp'";
                $query_ok="SELECT tbl.* FROM ($lshonquery) as tbl $_where ORDER BY tbl.date desc";
             }
            $q=$this->db->query($query_ok);     
             
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
                $this->excel->getActiveSheet()->setTitle(lang('Báo cáo khoản thu'));
                $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                $this->excel->getActiveSheet()->SetCellValue('B1', lang('HĐ Thu'));
                $this->excel->getActiveSheet()->SetCellValue('C1', lang('HĐ Bán - Đối tượng'));
                $this->excel->getActiveSheet()->SetCellValue('D1', lang('Nhân viên'));
                $this->excel->getActiveSheet()->SetCellValue('E1', lang('paid_by'));
                $this->excel->getActiveSheet()->SetCellValue('F1', lang('amount'));
                $this->excel->getActiveSheet()->SetCellValue('G1', lang('type'));

                $row = 2;
                $total = 0;
                foreach ($data as $data_row) {
                    $this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->sma->hrld($data_row->date));
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->payment_ref);
                    $this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->sale_ref);
                    $this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->nhanvien);
                    $this->excel->getActiveSheet()->SetCellValue('E' . $row, lang($data_row->paid_by));
                    $this->excel->getActiveSheet()->SetCellValue('F' . $row, $data_row->amount);
                    $this->excel->getActiveSheet()->SetCellValue('G' . $row, $data_row->type);
                    if ($data_row->type == 'returned' || $data_row->type == 'sent') {
                        $total -= $data_row->amount;
                    } else {
                        $total += $data_row->amount;
                    }
                    $row++;
                }
                $this->excel->getActiveSheet()->getStyle("F" . $row)->getBorders()
                    ->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);
                $this->excel->getActiveSheet()->SetCellValue('F' . $row, $total);

                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
                $filename = 'BaoCao_KhoanThu_report';
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

        } else {

            $this->load->library('datatables');
            $this->db->select("" . $this->db->dbprefix('payments') . ".date, " . $this->db->dbprefix('sales') . ".reference_no as payment_ref,c_name,c_phone,(select concat(first_name,' ',last_name) FROM scodeweb_users WHERE id={$this->db->dbprefix('payments')}.created_by) as nhanvien, payment_the.name as paid_by, amount,sotien_tragop,(amount-sotien_tragop) as duno,(select concat(first_name,' ',last_name) FROM scodeweb_users WHERE id={$this->db->dbprefix('payments')}.thu_by) as thuboi,(CASE WHEN sotien_tragop=amount THEN 'paid' WHEN sotien_tragop>0 AND sotien_tragop<amount THEN 'partial'  ELSE 'due' END) as status,".$this->db->dbprefix('payments') . ".note_last," . $this->db->dbprefix('payments') . ".id")
                ->from('payments')
                ->join('sales', 'payments.sale_id=sales.id', 'left')
                ->join('payment_the', 'payments.paid_by=payment_the.code', 'left')
                ->group_by('payments.id');
            $this->db->where('payment_the.is_tragop',1);    
            if ($user) {
                $this->db->where('payments.created_by', $user);
                $_where.=" AND tbl.created_by = '".$user."'";
            }

            if ($category) {
                $this->db->where('payments.paid_by', $category);
            }
           
            if ($reference_no!='') {
                $_where.=" AND tbl.payment_ref LIKE '%".$reference_no."%'";
            }
            if ($note!='') {
                $_where.=" AND tbl.note_last LIKE '%".$note."%'";
                
            }
            
            if ($start_date) {
                $_where.=" AND tbl.date BETWEEN '".$start_date."' AND '".$end_date."'";                
            }
            
            $this->db->where('payments.type', 'received');
            
            
            $report_query_ncc=$this->db->get_compiled_select();
           // $q2 = $this->db->get();
       
              
                        
            echo $rs=$this->datatables->generate_querylhson($output = 'json', $charset = 'UTF-8',$report_query_ncc);
        
            
        }

    }
    function getSalesReportByHoaDonCu($MaKhachHang='')
    {
             
        $this->load->library('datatables');
        $this->datatables
            ->select("scodeweb_danhsachhoadon_old.`id` as id,scodeweb_danhsachhoadon_old.`mahoadon` as mahoadon,scodeweb_danhsachhoadon_old.`mavandon` as mavandon,scodeweb_danhsachhoadon_old.`trangthaigiaohang` as trangthaigiaohang,scodeweb_danhsachhoadon_old.`madoisoat` as madoisoat,scodeweb_danhsachhoadon_old.`thoigian` as thoigian,scodeweb_danhsachhoadon_old.`thoigiantao` as thoigiantao,scodeweb_danhsachhoadon_old.`ngaycapnhat` as ngaycapnhat,scodeweb_danhsachhoadon_old.`madathang` as madathang,scodeweb_danhsachhoadon_old.`matrahang` as matrahang,scodeweb_danhsachhoadon_old.`maycsc` as maycsc,scodeweb_danhsachhoadon_old.`khachhang` as khachhang,scodeweb_danhsachhoadon_old.`email` as email,scodeweb_danhsachhoadon_old.`dienthoai` as dienthoai,scodeweb_danhsachhoadon_old.`diachi_khachhang` as diachi_khachhang,scodeweb_danhsachhoadon_old.`khuvuc` as khuvuc,scodeweb_danhsachhoadon_old.`phuong_xa_khachhang` as phuong_xa_khachhang,scodeweb_danhsachhoadon_old.`ngaysinh` as ngaysinh,scodeweb_danhsachhoadon_old.`chinhanh` as chinhanh,scodeweb_danhsachhoadon_old.`nguoiban` as nguoiban,scodeweb_danhsachhoadon_old.`nguoitao` as nguoitao,scodeweb_danhsachhoadon_old.`kenhban` as kenhban,scodeweb_danhsachhoadon_old.`doitacgiaohang` as doitacgiaohang,scodeweb_danhsachhoadon_old.`ghichu` as ghichu,scodeweb_danhsachhoadon_old.`tongtienhang` as tongtienhang,scodeweb_danhsachhoadon_old.`giamgia` as giamgia,scodeweb_danhsachhoadon_old.`khachcantra` as khachcantra,scodeweb_danhsachhoadon_old.`khachdatra` as khachdatra,scodeweb_danhsachhoadon_old.`concanthu_cod` as concanthu_cod,scodeweb_danhsachhoadon_old.`phitradtgh` as phitradtgh,scodeweb_danhsachhoadon_old.`ghichutrangthaigiaohang` as ghichutrangthaigiaohang,scodeweb_danhsachhoadon_old.`thoigiangiaohang` as thoigiangiaohang,scodeweb_danhsachhoadon_old.`trangthai` as trangthai")
            ->from('danhsachhoadon_old')
            ->join('danhsachchitiethoadon_old', 'danhsachchitiethoadon_old.MaHoaDon=danhsachhoadon_old.MaHoaDon', 'left')->group_by('danhsachchitiethoadon_old.mahoadon');

        $this->datatables->where('danhsachchitiethoadon_old.makhachhang', $MaKhachHang);        
        
        
        //echo $this->db->_compile_select(); 
        echo $this->datatables->generate();       

    }
    function getSalesReportByHoaDonCuChitiet($mahoadon='')
    {             
        $this->load->library('datatables');
        $this->datatables
            ->select("scodeweb_danhsachchitiethoadon_old.`id` as id,scodeweb_danhsachchitiethoadon_old.`chinhanh` as chinhanh,scodeweb_danhsachchitiethoadon_old.`mahoadon` as mahoadon,scodeweb_danhsachchitiethoadon_old.`mavandon` as mavandon,scodeweb_danhsachchitiethoadon_old.`diachilayhang` as diachilayhang,scodeweb_danhsachchitiethoadon_old.`madoisoat` as madoisoat,scodeweb_danhsachchitiethoadon_old.`phitradtgh` as phitradtgh,scodeweb_danhsachchitiethoadon_old.`thoigian` as thoigian,scodeweb_danhsachchitiethoadon_old.`thoigiantao` as thoigiantao,scodeweb_danhsachchitiethoadon_old.`ngaycapnhat` as ngaycapnhat,scodeweb_danhsachchitiethoadon_old.`madathang` as madathang,scodeweb_danhsachchitiethoadon_old.`maycsc` as maycsc,scodeweb_danhsachchitiethoadon_old.`matrahang` as matrahang,scodeweb_danhsachchitiethoadon_old.`makhachhang` as makhachhang,scodeweb_danhsachchitiethoadon_old.`tenkhachhang` as tenkhachhang,scodeweb_danhsachchitiethoadon_old.`email` as email,scodeweb_danhsachchitiethoadon_old.`dienthoai` as dienthoai,scodeweb_danhsachchitiethoadon_old.`diachi_khachhang` as diachi_khachhang,scodeweb_danhsachchitiethoadon_old.`khuvuc_khachhang` as khuvuc_khachhang,scodeweb_danhsachchitiethoadon_old.`phuong_xa_khachhang` as phuong_xa_khachhang,scodeweb_danhsachchitiethoadon_old.`ngaysinh` as ngaysinh,scodeweb_danhsachchitiethoadon_old.`nguoiban` as nguoiban,scodeweb_danhsachchitiethoadon_old.`kenhban` as kenhban,scodeweb_danhsachchitiethoadon_old.`nguoitao` as nguoitao,scodeweb_danhsachchitiethoadon_old.`doitacgiaohang` as doitacgiaohang,scodeweb_danhsachchitiethoadon_old.`nguoinhan` as nguoinhan,scodeweb_danhsachchitiethoadon_old.`dienthoai_nguoinhan` as dienthoai_nguoinhan,scodeweb_danhsachchitiethoadon_old.`diachi_nguoinhan` as diachi_nguoinhan,scodeweb_danhsachchitiethoadon_old.`khuvuc_nguoinhan` as khuvuc_nguoinhan,scodeweb_danhsachchitiethoadon_old.`phuong_xa_nguoinhan` as phuong_xa_nguoinhan,scodeweb_danhsachchitiethoadon_old.`dichvu` as dichvu,scodeweb_danhsachchitiethoadon_old.`trongluong_gram` as trongluong_gram,scodeweb_danhsachchitiethoadon_old.`dai` as dai,scodeweb_danhsachchitiethoadon_old.`rong` as rong,scodeweb_danhsachchitiethoadon_old.`cao` as cao,scodeweb_danhsachchitiethoadon_old.`ghichutrangthaigiaohang` as ghichutrangthaigiaohang,scodeweb_danhsachchitiethoadon_old.`ghichu` as ghichu,scodeweb_danhsachchitiethoadon_old.`tongtienhang` as tongtienhang,scodeweb_danhsachchitiethoadon_old.`giamgiahoadon` as giamgiahoadon,scodeweb_danhsachchitiethoadon_old.`khachcantra` as khachcantra,scodeweb_danhsachchitiethoadon_old.`khachdatra` as khachdatra,scodeweb_danhsachchitiethoadon_old.`tienmat` as tienmat,scodeweb_danhsachchitiethoadon_old.`the` as the,scodeweb_danhsachchitiethoadon_old.`chuyenkhoan` as chuyenkhoan,scodeweb_danhsachchitiethoadon_old.`diem` as diem,scodeweb_danhsachchitiethoadon_old.`voucher` as voucher,scodeweb_danhsachchitiethoadon_old.`mavoucher` as mavoucher,scodeweb_danhsachchitiethoadon_old.`concanthu_cod` as concanthu_cod,scodeweb_danhsachchitiethoadon_old.`thoigiangiaohang` as thoigiangiaohang,scodeweb_danhsachchitiethoadon_old.`trangthai` as trangthai,scodeweb_danhsachchitiethoadon_old.`trangthaigiaohang` as trangthaigiaohang,scodeweb_danhsachchitiethoadon_old.`mahang` as mahang,scodeweb_danhsachchitiethoadon_old.`mavach` as mavach,scodeweb_danhsachchitiethoadon_old.`tenhang` as tenhang,scodeweb_danhsachchitiethoadon_old.`thuonghieu` as thuonghieu,scodeweb_danhsachchitiethoadon_old.`dvt` as dvt,scodeweb_danhsachchitiethoadon_old.`ghichuhanghoa` as ghichuhanghoa,scodeweb_danhsachchitiethoadon_old.`soluong` as soluong,scodeweb_danhsachchitiethoadon_old.`dongia` as dongia,scodeweb_danhsachchitiethoadon_old.`giamgia_p` as giamgia_p,scodeweb_danhsachchitiethoadon_old.`giamgia` as giamgia,scodeweb_danhsachchitiethoadon_old.`giaban` as giaban,scodeweb_danhsachchitiethoadon_old.`thanhtien` as thanhtien,scodeweb_danhsachchitiethoadon_old.`baohanh` as baohanh,scodeweb_danhsachchitiethoadon_old.`dinhkybaotri` as dinhkybaotri")
            ->from('danhsachchitiethoadon_old')->where('mahoadon',$mahoadon);                  
        
        
        //echo $this->db->_compile_select(); 
        echo $this->datatables->generate();       

    }
}
