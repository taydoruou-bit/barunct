<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
  <script type="text/javascript" src="<?= $assets ?>tinymce/tinymce.min.js"></script>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-envelope"></i><?= lang('Mẫu in hóa đơn khác'); ?></h2>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <div class="row">
                        <ul id="myTab" class="nav nav-tabs">
                            <li class=""><a href="#printreturn"><?= lang('Hóa Đơn Thu hồi') ?></a></li>
                            <li class=""><a href="#printnhap"><?= lang('Hóa Đơn Nhập') ?></a></li>
							<li class=""><a href="#printnhapncc"><?= lang('Hóa Đơn Trả NCC') ?></a></li>
							<li class=""><a href="#printgiao"><?= lang('Hóa Đơn Giao Hàng') ?></a></li>
							<li class=""><a href="#printkiem"><?= lang('Hóa Đơn Kiểm Kho') ?></a></li>
							<li class=""><a href="#printchuyen"><?= lang('Hóa Đơn Chuyển Kho') ?></a></li>
							<li class=""><a href="#printbaogia"><?= lang('Báo Giá') ?></a></li>
                            
                        </ul>

                        <div class="tab-content">
                            <div id="printreturn" class="tab-pane fade in">
							<?= form_open('system_settings/print_khac'); ?>
								<div class="col-md-8 col-sm-">									
									<div class="form-control _print_size">
										<?php 
										echo lang('print_size_page');									
										
										echo form_dropdown('size_print_return', $_print,$_active_print_return, 'id="size_print_return" class="form-control input-tip select" data-placeholder="' . lang("select") . ' ' . lang("size_print_return") . '" required="required" style="width:100%;" ');
										
										echo form_dropdown('chieu_in_return', $_chieuin,$_active_chieu_return, 'id="chieu_in_return" class="form-control input-tip select" data-placeholder="' . lang("select") . ' ' . lang("chieu_in_return") . '" required="required" style="width:100%;" ');
										
										?>
									</div>
									
									<?php echo form_textarea('mail_body', (isset($_POST['mail_body']) ? html_entity_decode($_POST['mail_body']) : html_entity_decode($printreturn)), 'class="form-control skip" id="comment"'); ?>

									
									
								</div>
								 <div class="col-md-4 col-sm-4">
									<div class="margin5">
										<input type="submit" name="submit" class="btn btn-primary" value="<?= lang('Lưu mẫu hóa đơn'); ?>"
										   style="margin-top:15px;"/>
										<h3 style="font-weight: bold;"><?= $this->lang->line('short_tags'); ?></h3>
										
										<pre>{Ten_Kho} {Dia_Chi_Kho} {SDT_Kho}</pre>
										<pre>{logo} {site_link} {site_name}</pre>
										<?= lang('print_info_customer') ?>
										<pre> {Khach_Hang} {Dien_thoai_kh} {Dia_chi_kh}</pre>                           
										<?= lang('print_info_sale') ?>
											<pre>{Ma_Don_Hang} {Bang_Hoa_Don}</pre>
											<pre>{Tong_Thanh_Toan} {Nhan_Vien_Thu_Ngan}</pre>
											<pre>{Ghi_Chu} {Cong_ty} {Ngay_Thu}</pre>
											<pre>{Chua_Thanh_Toan} {Da_Thanh_Toan}</pre>
											<pre>{Giam_Gia_Tren_Hoa_Don} {Tong_Tien_Hang}</pre>
											<pre>{Tong_Tien_Bang_Chu}</pre>
										<?= lang('Đối tác giao hàng') ?>
											<pre>{Ma_Doi_Tac} {Ten_Doi_Tac}</pre>
											<pre>{Dia_Chi_Doi_Tac} {Email_Doi_Tac} </pre>
											<pre>{Dien_Thoai_Doi_Tac} {No_Dau_Ky_Doi_Tac}</pre>	
									</div>
								</div>
								<?php echo form_close(); ?>
                            </div>

                            <div id="printnhap" class="tab-pane fade">
								<?= form_open('system_settings/print_khac/printnhap'); ?>
								<div class="col-md-8 col-sm-8">
									
									<div class="form-control _print_size">
										<?php 
										echo lang('print_size_page');									
										
										echo form_dropdown('size_print_nhap', $_print,$_active_print_nhap, 'id="size_print_nhap" class="form-control input-tip select" data-placeholder="' . lang("select") . ' ' . lang("size_print_nhap") . '" required="required" style="width:100%;" ');
										
										echo form_dropdown('chieu_in_nhap', $_chieuin,$_active_chieu_nhap, 'id="chieu_in_nhap" class="form-control input-tip select" data-placeholder="' . lang("select") . ' ' . lang("chieu_in_nhap") . '" required="required" style="width:100%;" ');
										
										?>
									</div>
									<textarea name="mail_body" class="skip" id="comment"><?php echo (isset($_POST['mail_body']) ? html_entity_decode($_POST['mail_body']) : html_entity_decode($printnhap));?></textarea>
									
									
								</div>
								<div class="col-md-4 col-sm-4">
									<div class="margin5">
									<input type="submit" name="submit" class="btn btn-primary" value="<?= lang('Lưu hóa đơn nhập'); ?>"
										   style="margin-top:15px;"/>

										<h3 style="font-weight: bold;"><?= $this->lang->line('short_tags'); ?></h3>
										
										<pre>{Ten_Kho} {Dia_Chi_Kho} {SDT_Kho}</pre>
										<pre>{site_link} {site_name}</pre>
										<?= lang('Thông tin nhà cung cấp') ?>
										<pre> {Nha_Cung_Cap} {Dien_thoai_ncc} {Dia_chi_ncc} {Email_ncc}</pre>                           
										<?= lang('Thông tin nhập hàng') ?>
											<pre>{Ma_Don_Hang} {Bang_Hoa_Don}</pre>
											<pre>{Tong_Thanh_Toan} {Nhan_Vien_Nhap}</pre>
											<pre>{Ghi_Chu} {Ngay_Nhap}</pre>
											<pre>{No_cu} {Chua_Thanh_Toan} {Da_Thanh_Toan}</pre>
											<pre>{Giam_Gia_Tren_Hoa_Don} {Tong_Tien_Hang}</pre>
											<pre>{Tong_No} {Tong_Tien_Bang_Chu}</pre>
										<?= lang('Trạng thái') ?>
											<pre>{Trang_Thai} {Trang_Thai_Thanh_Toan}</pre>
										<?= lang('Đối tác giao hàng') ?>
											<pre>{Ma_Doi_Tac} {Ten_Doi_Tac}</pre>
											<pre>{Dia_Chi_Doi_Tac} {Email_Doi_Tac} </pre>
											<pre>{Dien_Thoai_Doi_Tac} {No_Dau_Ky_Doi_Tac}</pre>	
									</div>
								</div>
								<?php echo form_close(); ?>
                            </div> 
							<div id="printnhapncc" class="tab-pane fade">
								<?= form_open('system_settings/print_khac/printnhapncc'); ?>
								<div class="col-md-8 col-sm-8">
									
									<div class="form-control _print_size">
										<?php 
										echo lang('print_size_page');									
										
										echo form_dropdown('size_print_nhapncc', $_print,$_active_print_nhapncc, 'id="size_print_nhapncc" class="form-control input-tip select" data-placeholder="' . lang("select") . ' ' . lang("size_print_nhapncc") . '" required="required" style="width:100%;" ');
										
										echo form_dropdown('chieu_in_nhapncc', $_chieuin,$_active_chieu_nhapncc, 'id="chieu_in_nhapncc" class="form-control input-tip select" data-placeholder="' . lang("select") . ' ' . lang("chieu_in_nhapncc") . '" required="required" style="width:100%;" ');
										
										?>
									</div>
									<textarea name="mail_body" class="skip" id="comment"><?php echo (isset($_POST['mail_body']) ? html_entity_decode($_POST['mail_body']) : html_entity_decode($printnhapncc));?></textarea>
									
									
								</div>
								<div class="col-md-4 col-sm-4">
									<div class="margin5">
									<input type="submit" name="submit" class="btn btn-primary" value="<?= lang('Lưu hóa đơn trả NCC'); ?>"
										   style="margin-top:15px;"/>

										<h3 style="font-weight: bold;"><?= $this->lang->line('short_tags'); ?></h3>
										
										<pre>{Ten_Kho} {Dia_Chi_Kho} {SDT_Kho}</pre>
										<pre>{site_link} {site_name}</pre>
										<?= lang('Thông tin nhà cung cấp') ?>
										<pre> {Nha_Cung_Cap} {Dien_thoai_ncc} {Dia_chi_ncc} {Email_ncc}</pre>                           
										<?= lang('Thông tin nhập hàng') ?>
											<pre>{Ma_Don_Hang} {Bang_Hoa_Don}</pre>
											<pre>{Tong_Thanh_Toan} {Nhan_Vien_Xuat}</pre>
											<pre>{Ghi_Chu} {Ngay_Xuat}</pre>
											<pre>{No_Cu} {Chua_Thanh_Toan} {Da_Thanh_Toan}</pre>
											<pre>{Giam_Gia_Tren_Hoa_Don} {Tong_Tien_Hang}</pre>
											<pre>{Tong_No} {Tong_Tien_Bang_Chu}</pre>
											<pre>{Chiec_Khau} {Phi_Ship}</pre>
										<?= lang('Trạng thái') ?>
											<pre>{Trang_Thai_Thanh_Toan} {Trang_Thai}</pre>
										<?= lang('Đối tác giao hàng') ?>
											<pre>{Ma_Doi_Tac} {Ten_Doi_Tac}</pre>
											<pre>{Dia_Chi_Doi_Tac} {Email_Doi_Tac} </pre>
											<pre>{Dien_Thoai_Doi_Tac} {No_Dau_Ky_Doi_Tac}</pre>	
									</div>
								</div>
								<?php echo form_close(); ?>
                            </div>
							<!--start print giao hang-->
							<div id="printgiao" class="tab-pane fade">
								<?= form_open('system_settings/print_khac/printgiao'); ?>
								<div class="col-md-8 col-sm-8">
									
									<div class="form-control _print_size">
										<?php 
										echo lang('print_size_page');									
										
										echo form_dropdown('size_print_giao', $_print,$_active_print_giao, 'id="size_print_giao" class="form-control input-tip select" data-placeholder="' . lang("select") . ' ' . lang("size_print_giao") . '" required="required" style="width:100%;" ');
										
										echo form_dropdown('chieu_in_giao', $_chieuin,$_active_chieu_giao, 'id="chieu_in_giao" class="form-control input-tip select" data-placeholder="' . lang("select") . ' ' . lang("chieu_in_giao") . '" required="required" style="width:100%;" ');
										
										?>
									</div>
									<textarea name="mail_body" class="skip" id="comment"><?php echo (isset($_POST['mail_body']) ? html_entity_decode($_POST['mail_body']) : html_entity_decode($printgiao));?></textarea>
									
									
								</div>
								<div class="col-md-4 col-sm-4">
									<div class="margin5">
									<input type="submit" name="submit" class="btn btn-primary" value="<?= lang('Lưu hóa đơn giao hàng'); ?>"
										   style="margin-top:15px;"/>

										<h3 style="font-weight: bold;"><?= $this->lang->line('short_tags'); ?></h3>
										
										<pre>{Ten_Kho} {Dia_Chi_Kho} {SDT_Kho}</pre>
										<pre>{site_link} {site_name}</pre>
										<?= lang('Thông tin khách hàng') ?>
										<pre> {Khach_Mua_Hang} {Dien_Thoai} {Email}</pre>  
										<pre> {Dia_Chi_Giao_Hang}</pre>  											
										<?= lang('Thông tin giao hàng') ?>
											<pre>{Ma_Giao_Hang} {Ma_Ban_Hang} {Bang_Hoa_Don}</pre>
											<pre>{Ngay_Tao} {Ngay_Giao} {Nhan_Vien}</pre>
											<pre>{Ghi_Chu} {Trang_Thai} {Phai_Thu}</pre>
											<pre>{Nhan_Vien_Giao} {Khach_Nhan_Hang}</pre>
										<?= lang('Đối tác giao hàng') ?>
											<pre>{Ma_Doi_Tac} {Ten_Doi_Tac}</pre>
											<pre>{Dia_Chi_Doi_Tac} {Email_Doi_Tac} </pre>
											<pre>{Dien_Thoai_Doi_Tac} {No_Dau_Ky_Doi_Tac}</pre>
									</div>
								</div>
								<?php echo form_close(); ?>
                            </div> 	
							<!-- end print giao hang -->
							<!--start print kiểm kho-->
							<div id="printkiem" class="tab-pane fade">
								<?= form_open('system_settings/print_khac/printkiem'); ?>
								<div class="col-md-8 col-sm-8">
									
									<div class="form-control _print_size">
										<?php 
										echo lang('print_size_page');									
										
										echo form_dropdown('size_print_kiem', $_print,$_active_print_kiem, 'id="size_print_kiem" class="form-control input-tip select" data-placeholder="' . lang("select") . ' ' . lang("size_print_kiem") . '" required="required" style="width:100%;" ');
										
										echo form_dropdown('chieu_in_kiem', $_chieuin,$_active_chieu_kiem, 'id="chieu_in_kiem" class="form-control input-tip select" data-placeholder="' . lang("select") . ' ' . lang("chieu_in_kiem") . '" required="required" style="width:100%;" ');
										
										?>
									</div>
									<textarea name="mail_body" class="skip" id="comment"><?php echo (isset($_POST['mail_body']) ? html_entity_decode($_POST['mail_body']) : html_entity_decode($printkiem));?></textarea>
									
									
								</div>
								<div class="col-md-4 col-sm-4">
									<div class="margin5">
									<input type="submit" name="submit" class="btn btn-primary" value="<?= lang('Lưu hóa đơn kiểm kho'); ?>"
										   style="margin-top:15px;"/>

										<h3 style="font-weight: bold;"><?= $this->lang->line('short_tags'); ?></h3>
										
										<pre>{Ten_Kho} {Dia_Chi_Kho} {SDT_Kho}</pre>
										<pre>{site_link} {site_name}</pre>
										<?= lang('Thông tin kho kiểm') ?>
										<pre> {Ngay_Kiem} {Ma_Hoa_Don} {Nhan_Vien}</pre>  
										<pre> {Ghi_Chu} {Bang_Hoa_Don} {Tong_So}</pre>  	
									</div>
								</div>
								<?php echo form_close(); ?>
                            </div> 	
							<!-- end print kiểm kho -->	
							<!--start print chuyển kho-->
							<div id="printchuyen" class="tab-pane fade">
								<?= form_open('system_settings/print_khac/printchuyen'); ?>
								<div class="col-md-8 col-sm-8">
									
									<div class="form-control _print_size">
										<?php 
										echo lang('print_size_page');									
										
										echo form_dropdown('size_print_chuyen', $_print,$_active_print_chuyen, 'id="size_print_chuyen" class="form-control input-tip select" data-placeholder="' . lang("select") . ' ' . lang("size_print_chuyen") . '" required="required" style="width:100%;" ');
										
										echo form_dropdown('chieu_in_chuyen', $_chieuin,$_active_chieu_chuyen, 'id="chieu_in_chuyen" class="form-control input-tip select" data-placeholder="' . lang("select") . ' ' . lang("chieu_in_chuyen") . '" required="required" style="width:100%;" ');
										
										?>
									</div>
									<textarea name="mail_body" class="skip" id="comment"><?php echo (isset($_POST['mail_body']) ? html_entity_decode($_POST['mail_body']) : html_entity_decode($printchuyen));?></textarea>
									
									
								</div>
								<div class="col-md-4 col-sm-4">
									<div class="margin5">
									<input type="submit" name="submit" class="btn btn-primary" value="<?= lang('Lưu hóa đơn chuyển kho'); ?>"
										   style="margin-top:15px;"/>

										<h3 style="font-weight: bold;"><?= $this->lang->line('short_tags'); ?></h3>
										<?= lang('Thông tin chuyển kho') ?>
										<pre>{So_Hoa_Don} {Ngay_Chuyen} {Trang_Thai}</pre>
										<pre>{Phi_Van_Chuyen} {Ghi_Chu} {Nhan_Vien}</pre>
										<pre>{Tong_Cong} {Phi_Van_Chuyen} {Bang_Chu}</pre>
										<pre>{Tong_So_Luong}</pre>
										<?= lang('Thông tin kho chuyển đi') ?>
										<pre> {Ten_Kho_Chuyen} {SDT_Kho_Chuyen}</pre> 
										<pre> {Dia_Chi_Kho_Chuyen}</pre> 										
										<?= lang('Thông tin kho nhận') ?>
										<pre> {Ten_Kho_Nhan} {SDT_Kho_Nhan}</pre>  	
										<pre> {Dia_Chi_Kho_Nhan}</pre>  	
										<?= lang('Đối tác giao hàng') ?>
											<pre>{Ma_Doi_Tac} {Ten_Doi_Tac} {Phi_Ship_Doi_Tac}</pre>
											<pre>{Dia_Chi_Doi_Tac} {Email_Doi_Tac} </pre>
											<pre>{Dien_Thoai_Doi_Tac} {No_Dau_Ky_Doi_Tac}</pre>
									</div>
								</div>
								<?php echo form_close(); ?>
                            </div> 	
							<!-- end print chuyển kho -->	
							<!--start print Báo giá-->
							<div id="printbaogia" class="tab-pane fade">
								<?= form_open('system_settings/print_khac/printbaogia'); ?>
								<div class="col-md-8 col-sm-8">
									
									<div class="form-control _print_size">
										<?php 
										echo lang('print_size_page');									
										
										echo form_dropdown('size_print_baogia', $_print,$_active_print_baogia, 'id="size_print_baogia" class="form-control input-tip select" data-placeholder="' . lang("select") . ' ' . lang("size_print_baogia") . '" required="required" style="width:100%;" ');
										
										echo form_dropdown('chieu_in_baogia', $_chieuin,$_active_chieu_baogia, 'id="chieu_in_baogia" class="form-control input-tip select" data-placeholder="' . lang("select") . ' ' . lang("chieu_in_baogia") . '" required="required" style="width:100%;" ');
										
										?>
									</div>
									<textarea name="mail_body" class="skip" id="comment"><?php echo (isset($_POST['mail_body']) ? html_entity_decode($_POST['mail_body']) : html_entity_decode($printbaogia));?></textarea>
									
									
								</div>
								<div class="col-md-4 col-sm-4">
									<div class="margin5">
									<input type="submit" name="submit" class="btn btn-primary" value="<?= lang('Lưu hóa đơn báo giá'); ?>"
										   style="margin-top:15px;"/>

										<h3 style="font-weight: bold;"><?= $this->lang->line('short_tags'); ?></h3>
										<?= lang('Thông tin báo giá') ?>
										<pre>{So_Hoa_Don} {Ngay} {Trang_Thai}</pre>
										<pre>{Nhan_Vien} {Nha_Cung_Cap} {Ghi_Chu}</pre>
										<pre>{Tong_Cong} {Tong_Cong_Bang_Chu}</pre>
										<?= lang('Thông tin kho') ?>
										<pre> {Ten_Kho} {SDT_Kho}</pre> 
										<pre> {Dia_Chi_Kho} {Email_Kho}</pre> 										
										<?= lang('Thông tin khách hàng') ?>
										<pre> {Ten_Khach} {SDT_Khach}</pre>  	
										<pre> {Dia_Chi_Khach} {Email_Khach}</pre>  	
									</div>
								</div>
								<?php echo form_close(); ?>
                            </div> 	
							<!-- end print báo giá -->	
                        </div>
                </div>
            </div>

        </div>
    </div>
</div>
<style>
#printreturn textarea#comment,#printnhap textarea#comment {
    height: 675px!important;
}
.mce-container, .mce-container-body {
    display: block;
    float: left!important;
}
div.mce-edit-area {
    background: #FFF;
    filter: none;
    float: none!important;
}
.mce-stack-layout-item{
	float: none!important;
}
ul#myTab {
    float: left;
    width: 100%;
    padding: 0px;
    margin: 0px;
}

.tab-content {
    float: left;
    min-height: 750px;
    /* padding: 0px; */
}

div#printreturn {}

.tab-content .tab-pane {
    float: left;
    border: 0px;
}

.col-lg-12 {
    padding: 0px;
}

.col-lg-12 .row {
    /* padding: 0px; */
    margin: 0px;
}

form {
    float: left;
    width: 100%;
}

.form-control._print_size {
    float: left;
    width: 100%;
    height: auto!important;
    border: 0px;
    margin-bottom: 5px;
}

.mce-tinymce {
    float: left;
    width: 100%;
}
</style>
<script>
tinymce.init({
  selector: 'textarea',
  height: 600,
  theme: 'modern',
  plugins: 'print preview fullpage searchreplace autolink directionality visualblocks visualchars fullscreen image link media template codesample table charmap hr pagebreak nonbreaking anchor toc insertdatetime advlist lists textcolor wordcount imagetools  contextmenu colorpicker textpattern help',
  toolbar1: 'formatselect | bold italic strikethrough forecolor backcolor | link | alignleft aligncenter alignright alignjustify  | numlist bullist outdent indent  | removeformat',
  image_advtab: true,
  templates: [
    { title: 'Test template 1', content: 'Test 1' },
    { title: 'Test template 2', content: 'Test 2' }
  ],
  content_css: [
  ]
 });
</script>