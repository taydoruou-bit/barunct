<?php defined('BASEPATH') or exit('No direct script access allowed');

class Doitac extends MY_Controller
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
        $this->lang->load('doitac', $this->Settings->user_language);
        $this->load->library('form_validation');
        $this->load->model('doitac_model');
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
       

        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' =>'Danh sách đối tác giao hàng'));
        $meta = array('page_title' => 'Danh sách đối tác giao hàng', 'bc' => $bc);
        $this->page_construct('doitac/index', $meta, $this->data);
    }

    public function getDoitacs($warehouse_id = null)
    {
        //$this->sma->checkPermissions('index');

        if ((!$this->Owner || !$this->Admin) && !$warehouse_id) {
            $user = $this->site->getUser();
            $warehouse_id = $user->warehouse_id;
        }

        $this->load->library('datatables');
        
		$this->datatables->select("{$this->db->dbprefix('doitac')}.id as id, DATE_FORMAT({$this->db->dbprefix('doitac')}.date, '%Y-%m-%d %T') as date,code,name,diachi,dienthoai,email,note,nodauky,attachment")->from('doitac');
        

        if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->datatables->where('created_by', $this->session->userdata('user_id'));
        }
		
		$detail_link = anchor('doitac/view/$1', '<i class="fa fa-file-text-o"></i> ','class="tip" data-toggle="modal" data-target="#myModal" title="Xem chi tiết"');
		
        $this->datatables->add_column("Actions", "<div class=\"text-center\"><a href='" . site_url('doitac/edit/$1') . "' class='tip' title='" . lang("edit_doitac") . "'><i class=\"fa fa-edit\"></i></a>   <a href='#' class='tip po' title='<b>" . lang("delete_doitac") . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('doitac/delete_ajax/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></div>", "id");
        echo $this->datatables->generate();
    }

    public function view($id = null)
    {
        //$this->sma->checkPermissions('index', true);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $inv = $this->doitac_model->getReturnByID($id);
        if (!$this->session->userdata('view_right')) {
            $this->sma->view_rights($inv->created_by, true);
        }
        $this->data['customer'] = $this->site->getCompanyByID($inv->customer_id);
        $this->data['biller'] = $this->site->getCompanyByID($inv->biller_id);
        $this->data['created_by'] = $this->site->getUser($inv->created_by);
        $this->data['updated_by'] = $inv->updated_by ? $this->site->getUser($inv->updated_by) : null;
        $this->data['warehouse'] = $this->site->getWarehouseByID($inv->warehouse_id);
        $this->data['inv'] = $inv;
        $this->data['rows'] = $this->doitac_model->getReturnItems($id);

        $this->load->view($this->theme . 'doitac/view', $this->data);
    }

    public function add()
    {
       // $this->sma->checkPermissions();
        $this->form_validation->set_message('is_natural_no_zero', lang("no_zero_required"));
        $this->form_validation->set_rules('name', lang("name"), 'required');
		$this->form_validation->set_rules('code', lang("code"), 'required');
		
        if ($this->form_validation->run() == true) {
            $date = ($this->Owner || $this->Admin) ? $this->sma->fld(trim($this->input->post('date'))) : date('Y-m-d H:i:s');
			if($date=='00/00/0000 00:00:00'){
				$date=date('Y-m-d H:i:s');
			}
            
            $code = $this->input->post('code');
            $name = $this->input->post('name');
            $diachi = $this->input->post('diachi');
			$dienthoai = $this->input->post('dienthoai');
            $email = $this->input->post('email');
			$nodauky = $this->input->post('nodauky');			
            //$note = $this->sma->clear_tags($this->input->post('note'));
			$note = $this->input->post('note');
            $data = array('date' => $date,
                'code' => $code,
                'name' => $name,
                'diachi' => $diachi,
                'dienthoai' => $dienthoai,
                'email' => $email,
                'nodauky' => $nodauky,
                'note' => $note,
                'created_by' => $this->session->userdata('user_id'),
                'hash' => hash('sha256', microtime() . mt_rand()),
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

        if ($this->form_validation->run() == true && $this->doitac_model->addDoitac($data)) {
					
            $this->session->set_userdata('remove_rels', 1);
            $this->session->set_flashdata('message', lang("doitac_added"));
            redirect("doitac");
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('doitac'), 'page' => lang('doitac')), array('link' => '#', 'page' => lang('add_doitac')));
            $meta = array('page_title' => lang('add_doitac'), 'bc' => $bc);
            $this->page_construct('doitac/add', $meta, $this->data);
        }
    }

    public function edit($id = null)
    {
        //$this->sma->checkPermissions();
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $inv = $this->doitac_model->getDoitacByID($id);
        if (!$this->session->userdata('edit_right')) {
            $this->sma->view_rights($inv->created_by);
        }
        $this->form_validation->set_message('is_natural_no_zero', lang("no_zero_required"));
        $this->form_validation->set_rules('name', lang("name"), 'required');

        if ($this->form_validation->run() == true) {
            $date = ($this->Owner || $this->Admin) ? $this->sma->fld(trim($this->input->post('date'))) : $inv->date;
			if($date=='00/00/0000 00:00:00'){
				$date=$this->sma->fld(date('Y-m-d H:i:s'));
			}
            $code = $this->input->post('code');
            $name = $this->input->post('name');
            $diachi = $this->input->post('diachi');
			$dienthoai = $this->input->post('dienthoai');
            $email = $this->input->post('email');
			$nodauky = $this->input->post('nodauky');			
            //$note = $this->sma->clear_tags($this->input->post('note'));
			$note = $this->input->post('note');
            $data = array('date' => $date,
                'code' => $code,
                'name' => $name,
                'diachi' => $diachi,
                'dienthoai' => $dienthoai,
                'email' => $email,
                'nodauky' => $nodauky,
                'note' => $note,
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
        if ($this->form_validation->run() == true && $this->doitac_model->updateDoitac($id, $data)) {
			
            $this->session->set_userdata('remove_rels', 1);
            $this->session->set_flashdata('message', lang("doitac_updated"));
			
            redirect("doitac");
        } else {
            $this->data['inv'] = $inv;
           
            $this->data['id'] = $id;
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('doitac'), 'page' => lang('doitac')), array('link' => '#', 'page' => lang('edit_doitac')));
            $meta = array('page_title' => lang('edit_doitac'), 'bc' => $bc);
            $this->page_construct('doitac/edit', $meta, $this->data);
        }
    }

    public function delete($id = null)
    {
        //$this->sma->checkPermissions(null, true);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        if ($this->doitac_model->deleteDoitac($id)) {
            if ($this->input->is_ajax_request()) {
                $this->sma->send_json(array('error' => 0, 'msg' => lang("doitac_deleted")));
            }
            $this->session->set_flashdata('message', lang('doitac_deleted'));
            redirect('welcome');
        }
    }
	public function doitac_actions()
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
						$this->doitac_model->deleteDoitac($id);
					}
					$this->session->set_flashdata('message', lang("doitac_deleted"));
					redirect($_SERVER["HTTP_REFERER"]);
				}elseif ($this->input->post('form_action') == 'export_excel' || $this->input->post('form_action') == 'export_pdf') {

					$this->load->library('excel');
					$this->excel->setActiveSheetIndex(0);
					$this->excel->getActiveSheet()->setTitle(lang('doitac'));
					$this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
					$this->excel->getActiveSheet()->SetCellValue('B1', lang('code'));
					$this->excel->getActiveSheet()->SetCellValue('C1', lang('name'));
					$this->excel->getActiveSheet()->SetCellValue('D1', lang('Địa chỉ'));
					$this->excel->getActiveSheet()->SetCellValue('E1', lang('Điện thoại'));
					$this->excel->getActiveSheet()->SetCellValue('F1', lang('Email'));
					$this->excel->getActiveSheet()->SetCellValue('G1', lang('Ghi chú'));
					$this->excel->getActiveSheet()->SetCellValue('H1', lang('Nợ đầu kỳ'));

					$row = 2;
					foreach ($_POST['val'] as $id) {
						
						$this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->sma->hrld($res->date));
						$this->excel->getActiveSheet()->SetCellValue('B' . $row, $res->reference_no);
						$this->excel->getActiveSheet()->SetCellValue('C' . $row, $warehouse->name);
						$this->excel->getActiveSheet()->SetCellValue('D' . $row, $res->biller);
						$this->excel->getActiveSheet()->SetCellValue('E' . $row, $_customer);
						$this->excel->getActiveSheet()->SetCellValue('F' . $row, $res->grand_total);
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
				$this->session->set_flashdata('error', lang("doitac_deleted"));
				redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        
		}
	}
	public function delete_ajax($id = null)
    {
        //$this->sma->checkPermissions(null, true);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
		$rsdel=$this->doitac_model->deleteDoitac($id);
        if ($rsdel) {
            $this->sma->send_json(array('error' => 0, 'msg' => lang("doitac_deleted")));
        }else{
			$this->sma->send_json(array('error' => 0, 'msg' => 'Lỗi khi xóa dối tác'));
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
        $rows['results'] = $this->doitac_model->getDoitacSuggestions($term, $limit);
        $this->sma->send_json($rows);
    }
    function getDoiTacById($id = NULL)
    {
        // $this->sma->checkPermissions('index');
        $row = $this->doitac_model->getDoiTacById($id);
        $this->sma->send_json(array(array('id' => $row->id, 'text' => ($row->code != '-' ? $row->code.'-'.$row->name : $row->name.'-'.$row->phone))));
    }
	
}
