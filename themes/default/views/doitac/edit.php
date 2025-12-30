<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

 <?php
$attrib = array('data-toggle' => 'validator', 'role' => 'form');
		echo form_open_multipart("doitac/edit/".$inv->id, $attrib);
		?>
<div class="box lhson_nhapkho_add">
   
	<div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-edit"></i>Chỉnh sửa</h2>
		<div class="main-task-lhson nhapkho">
			<button type="submit" class="btn btn-primary btncls" name="edit_doitac" id="edit_doitac">
				<i class="fa fa-save"></i>
				Chỉnh sửa
			</button>
			<button type="button" class="btn btn-default btncls" id="reset">
				<i class="fa fa-refresh"></i>
				<?= lang('reset') ?>
			</button>
		</div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12"> 
				
               
                <div class="row">
                    <div class="col-lg-12">
						<div class="col-lg-6">
							<?php if ($Owner || $Admin) { ?>
									<div class="form-group">
										<?= lang("date", "redate"); ?>
										<?php echo form_input('date', $this->sma->hrld($inv->date), 'class="form-control input-tip datetime" id="redate"'); ?>
									</div>
							<?php } ?>
							<div class="form-group">
								<?= lang("Mã", "code"); ?>
								<?php echo form_input('code', $inv->code, 'class="form-control input-tip" id="code"'); ?>
							</div>
							<div class="form-group">
								<?= lang("Tên", "name"); ?>
								<?php echo form_input('name', $inv->name, 'class="form-control input-tip" id="name"'); ?>
							</div>
							<div class="form-group">
								<?= lang("Địa chỉ", "diachi"); ?>
								<?php echo form_input('diachi', $inv->diachi, 'class="form-control input-tip" id="diachi"'); ?>
							</div>
							<div class="form-group">
								<?= lang("Nợ đầu kỳ", "nodauky"); ?>
								<?php echo form_input('nodauky', $inv->nodauky, 'class="form-control input-tip number" id="nodauky"'); ?>
							</div>
							<div class="form-group">
								<?= lang("Phí / 1 đơn vị (bao)", "phivanchuyen"); ?>
								<?php echo form_input('phivanchuyen', $inv->phivanchuyen, 'class="form-control input-tip number" id="phivanchuyen"'); ?>
							</div>
						</div>
						<div class="col-lg-6">
							<div class="form-group">
								<?= lang("Điện thoại", "dienthoai"); ?>
								<?php echo form_input('dienthoai', $inv->dienthoai, 'class="form-control input-tip" id="dienthoai"'); ?>
							</div>	
							<div class="form-group">
								<?= lang("Email", "email"); ?>
								<?php echo form_input('email',$inv->email, 'class="form-control input-tip" id="email"'); ?>
							</div>
							<div class="form-group">
								File
								<input id="document" type="file" data-browse-label="<?= lang('browse'); ?>" name="document" data-show-upload="false"
									   data-show-preview="false" class="form-control file">
							</div>
							<div class="clearfix"></div>						
							<div class="_note_add_i" id="bt">
									<div class="form-group">
										<?= lang("Ghi chú", "renote"); ?>
										<?php echo form_textarea('note',str_replace(array("\r", "\n"), "", $this->sma->decode_html($inv->note)), 'class="form-control" id="renote" style="margin-top: 10px; height: 100px;"'); ?>
									</div>
							</div>
						</div>	
                    </div>
                </div>                
            </div>

        </div>
    </div>
</div>

<?php echo form_close(); ?>
