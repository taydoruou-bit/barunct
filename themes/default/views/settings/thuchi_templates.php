<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
  <script type="text/javascript" src="<?= $assets ?>tinymce/tinymce.min.js"></script>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-envelope"></i>Mẫu in phiếu thu - chi</h2>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <div class="row">
                    <div class="col-md-8 col-sm-8">
                        <ul id="myTab" class="nav nav-tabs">
                            <li class=""><a href="#phieuthu">Phiếu thu</a></li>
                            <li class=""><a href="#phieuchi">Phiếu chi</a></li>
                            
                        </ul>

                        <div class="tab-content">
                            <div id="phieuthu" class="tab-pane fade in">
                                <?= form_open('system_settings/thuchi_templates'); ?>
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

                            <div id="phieuchi" class="tab-pane fade">
                                <?= form_open('system_settings/thuchi_templates/phieuchi'); ?>
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
							
                            <pre>{Ten_Cua_Hang} {Dia_Chi_Cua_Hang} {SDT_Cua_Hang}</pre>
							<pre>{logo} {site_link} {site_name}</pre>
                            <?= lang('print_info_customer') ?>
                            <pre> {Khach_Hang} {Dien_thoai_kh} {Dia_chi_kh}</pre>                           
                            Thông tin thu - chi
								<pre>{So_Phieu} {Ly_Do} {Ngay}</pre>
								<pre>{So_Tien} {Nhan_Vien} {So_Tien_Bang_Chu}</pre>
							Ngày / tháng / năm
							<pre>{D_Ngay} {D_Thang} {D_Nam}</pre>	
               <?= lang('Kế toán theo danh mục thu chi') ?>
                            <pre> {KT_NO} {KT_CO}</pre> 
                        </div>
                    </div>
                </div>


            </div>

        </div>
    </div>
</div>
<style>
#phieuthu textarea#comment,#phieuchi textarea#comment {
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