<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

 <?php
$attrib = array('data-toggle' => 'validator', 'role' => 'form');
		echo form_open_multipart("the/edit/".$inv->id, $attrib);
		?>
<div class="box lhson_nhapkho_add">
   
	<div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-edit"></i>Chỉnh sửa</h2>
		<div class="main-task-lhson nhapkho">
			<button type="submit" class="btn btn-primary btncls" name="edit_the" id="edit_the">
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
								<?= lang("Số tài khoản", "sotk"); ?>
								<?php echo form_input('sotk', $inv->sotk, 'class="form-control input-tip" id="sotk"'); ?>
							</div>
							<div class="form-group">
								<?= lang("Số dư đầu kỳ", "sodudauky"); ?>
								<?php echo form_input('sodudauky', (isset($sodudauky) ? (float)$sodudauky : 0), 'class="form-control input-tip" id="sodudauky"'); ?>
							</div>
							<div class="clearfix"></div>						
							<div class="_note_add_i" id="bt">
									<div class="form-group">
										<?= lang("Ghi chú", "renote"); ?>
										<?php echo form_textarea('note',str_replace(array("\r", "\n"), "", $this->sma->decode_html($inv->note)), 'class="form-control" id="renote" style="margin-top: 10px; height: 100px;"'); ?>
									</div>
							</div>
							<div class="clearfix"></div>	
							<div class="form-group">
				            	<input type="checkbox" <?=$inv->is_tragop==1?'checked':''?> class="checkbox" value="1" name="is_tragop" id="is_tragop" <?= $this->input->post('is_tragop') ? ' checked="checked" ' : ''; ?>>
				        		<label><?= lang('Phương thức thanh toán trả góp'); ?></label>
									
							</div>
						</div>
						
                    </div>
                </div>                
            </div>

        </div>
    </div>
</div>
<input type="hidden" name="id" value="<?=$inv->id;?>">
<?php echo form_close(); ?>
