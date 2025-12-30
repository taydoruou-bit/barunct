<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
  <script type="text/javascript" src="<?= $assets ?>tinymce/tinymce.min.js"></script>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-envelope"></i><?= lang('print_templates'); ?></h2>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <div class="row">
                    <div class="col-md-8 col-sm-8">
                        <ul id="myTab" class="nav nav-tabs">
                            <!--<li class=""><a href="#printsale"><?= lang('print_sale') ?></a></li>
							<li class=""><a href="#printpos_nocu"><?= lang('HĐ bán chỉ số lượng') ?></a></li>
							<li class=""><a href="#printpos_kono"><?= lang('HĐ bán không nợ củ') ?></a></li>-->
                            <li class=""><a href="#printpos"><?= lang('print_pos') ?></a></li>
                            
                        </ul>

                        <div class="tab-content">
                            <div id="printsale" class="tab-pane fade in">
                                <?= form_open('system_settings/print_templates/printsale'); ?>
								<div class="form-control _print_size">
									<?php 
									echo lang('print_size_page');									
									
									echo form_dropdown('size_print_sale', $_print,$_active_print_sale, 'id="size_print_sale" class="form-control input-tip select" data-placeholder="' . lang("select") . ' ' . lang("size_print_sale") . '" required="required" style="width:100%;" ');
									
									echo form_dropdown('chieu_in_sale', $_chieuin,$_active_chieu_sale, 'id="chieu_in_sale" class="form-control input-tip select" data-placeholder="' . lang("select") . ' ' . lang("chieu_in_sale") . '" required="required" style="width:100%;" ');
									
									?>
								</div>
								
                                <?php echo form_textarea('mail_body', (isset($_POST['mail_body']) ? html_entity_decode($_POST['mail_body']) : html_entity_decode($printsale)), 'class="form-control skip" id="comment"'); ?>

                                <input type="submit" name="submit" class="btn btn-primary" value="<?= lang('save'); ?>"
                                       style="margin-top:15px;"/>
								
								
                                <?php echo form_close(); ?>
                            </div>
							<div id="printpos_nocu" class="tab-pane fade">
                                <?= form_open('system_settings/print_templates/printpos_nocu'); ?>
								<div class="form-control _print_size">
									<?php 
									echo lang('print_size_page');									
									
									echo form_dropdown('size_print_pos_nocu', $_print,$_active_print_pos_nocu, 'id="size_print_pos_nocu" class="form-control input-tip select" data-placeholder="' . lang("select") . ' ' . lang("size_print_pos") . '" required="required" style="width:100%;" ');
									
									echo form_dropdown('chieu_in_pos_nocu', $_chieuin,$_active_chieu_pos_nocu, 'id="chieu_in_pos_nocu" class="form-control input-tip select" data-placeholder="' . lang("select") . ' ' . lang("chieu_in_pos_nocu") . '" required="required" style="width:100%;" ');
									
									?>
								</div>
								<textarea name="mail_body" class="skip" id="comment"><?php echo (isset($_POST['mail_body']) ? html_entity_decode($_POST['mail_body']) : html_entity_decode($printpos_nocu));?></textarea>
                                <input type="submit" name="submit" class="btn btn-primary" value="<?= lang('save'); ?>"
                                       style="margin-top:15px;"/>

                                <?php echo form_close(); ?>
                            </div>
							<div id="printpos_kono" class="tab-pane fade">
                                <?= form_open('system_settings/print_templates/printpos_kono'); ?>
								<div class="form-control _print_size">
									<?php 
									echo lang('print_size_page');									
									
									echo form_dropdown('size_print_pos_kono', $_print,$_active_print_pos_kono, 'id="size_print_pos_kono" class="form-control input-tip select" data-placeholder="' . lang("select") . ' ' . lang("size_print_pos_kono") . '" required="required" style="width:100%;" ');
									
									echo form_dropdown('chieu_in_pos_kono', $_chieuin,$_active_chieu_pos_kono, 'id="chieu_in_pos_kono" class="form-control input-tip select" data-placeholder="' . lang("select") . ' ' . lang("chieu_in_pos_kono") . '" required="required" style="width:100%;" ');
									
									?>
								</div>
								<textarea name="mail_body" class="skip" id="comment"><?php echo (isset($_POST['mail_body']) ? html_entity_decode($_POST['mail_body']) : html_entity_decode($printpos_kono));?></textarea>
                                <input type="submit" name="submit" class="btn btn-primary" value="<?= lang('save'); ?>"
                                       style="margin-top:15px;"/>

                                <?php echo form_close(); ?>
                            </div>
                            <div id="printpos" class="tab-pane fade active">
                                <?= form_open('system_settings/print_templates/printpos'); ?>
								<div class="form-control _print_size">
									<?php 
									echo lang('print_size_page');									
									
									echo form_dropdown('size_print_pos', $_print,$_active_print_pos, 'id="size_print_pos" class="form-control input-tip select" data-placeholder="' . lang("select") . ' ' . lang("size_print_pos") . '" required="required" style="width:100%;" ');
									
									echo form_dropdown('chieu_in_pos', $_chieuin,$_active_chieu_pos, 'id="chieu_in_pos" class="form-control input-tip select" data-placeholder="' . lang("select") . ' ' . lang("chieu_in_pos") . '" required="required" style="width:100%;" ');
									
									?>
								</div>
								<textarea name="mail_body" class="skip" id="comment"><?php echo (isset($_POST['mail_body']) ? html_entity_decode($_POST['mail_body']) : html_entity_decode($printpos));?></textarea>
                                <input type="submit" name="submit" class="btn btn-primary" value="<?= lang('save'); ?>"
                                       style="margin-top:15px;"/>

                                <?php echo form_close(); ?>
                            </div>
                            
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-4">
                        <div class="margin5">
                            <h3 style="font-weight: bold;"><?= $this->lang->line('short_tags'); ?></h3>
							
                            <pre>{Ten_Kho} {Dia_Chi_Kho} {SDT_Kho}</pre>
							<pre>{logo} {site_link} {site_name}</pre>
                            <?= lang('print_info_customer') ?>
                            <pre> {Khach_Hang} {Dien_thoai_kh} {Dia_chi_kh} {CongTy_KH} {MST_KH}</pre>                           
                            <?= lang('print_info_sale') ?>
								<pre>{Ma_Don_Hang} {Bang_Hoa_Don}</pre>
								<pre>{Tong_Thanh_Toan} {Nhan_Vien_Thu_Ngan}</pre>
								<pre>{Phu_Phi} {Ngay_Xuat}</pre>
								<pre>{No_cu} {Chua_Thanh_Toan} {Da_Thanh_Toan}</pre>
								<pre>{Tong_Diem_Tich_Luy} {Diem_hoa_don}</pre>
								<pre>{Giam_Gia_Tren_Hoa_Don} {Tong_Tien_Hang}</pre>
								<pre>{Tong_No} {Tong_Tien_Bang_Chu} {THUE}</pre>
								<pre>{Tong_No_Bang_Chu} {Tong_Thanh_Toan_Bang_Chu}</pre>
                <pre>{Ghi_Chu} {Ghi_Chu_NV}</pre>
							 <?= lang('print_info_return') ?>
								<pre>{Tong_thu_hoi}</pre>
							<?= lang('Đối tác giao hàng') ?>
								<pre>{Ma_Doi_Tac} {Ten_Doi_Tac}</pre>
								<pre>{Dia_Chi_Doi_Tac} {Email_Doi_Tac} </pre>
								<pre>{Dien_Thoai_Doi_Tac} {No_Dau_Ky_Doi_Tac}</pre>		
                        </div>
                    </div>
                </div>


            </div>

        </div>
    </div>
</div>
<style>
#printsale textarea#comment,#printpos textarea#comment,#printpos_nocu textarea#comment,#printpos_kono textarea#comment {
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
 #printpos_nocu .form-control._print_size,#printpos_kono .form-control._print_size {
    float: left;
    height: 100px!important;
    padding: 1%;
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