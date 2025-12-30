<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script type="text/javascript">
    
    $(document).ready(function () {
       
		<?php if ($Owner || $Admin) { ?>
				if (!localStorage.getItem('redate')) {
					$("#redate").datetimepicker({
						format: site.dateFormats.js_ldate,
						fontAwesome: true,
						language: 'sma',
						weekStart: 1,
						todayBtn: 1,
						autoclose: 1,
						todayHighlight: 1,
						startView: 2,
						forceParse: 0
					}).datetimepicker('update', new Date());
				}
				$(document).on('change', '#redate', function (e) {
					localStorage.setItem('redate', $(this).val());
				});
				if (redate = localStorage.getItem('redate')) {
					$('#redate').val(redate);
				}
        <?php } ?>;
    });
</script>

<div class="box lhson_nhapkho_add">
	 <?php
		$attrib = array('data-toggle' => 'validator', 'role' => 'form');
		echo form_open_multipart("the/add", $attrib);
		?>
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-plus"></i>Thêm phương thức thanh toán</h2>
		<div class="main-task-lhson nhapkho">
			<button type="submit" class="btn btn-primary btncls" name="add_the" id="add_the">
				<i class="fa fa-save"></i>
				Thêm mới
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
										<?php echo form_input('date', (isset($_POST['date']) ? $_POST['date'] : ''), 'class="form-control input-tip datetime" id="redate"'); ?>
									</div>
							<?php } ?>
							<div class="form-group">
								<?= lang("Mã", "code"); ?>
								<?php echo form_input('code', (isset($_POST['code']) ? $_POST['code'] : ''), 'class="form-control input-tip required" id="code"'); ?>
							</div>
							<div class="form-group">
								<?= lang("Tên", "name"); ?>
								<?php echo form_input('name', (isset($_POST['name']) ? $_POST['name'] : ''), 'class="form-control input-tip required" id="name"'); ?>
							</div>
							<div class="form-group">
								<?= lang("Số tài khoản", "sotk"); ?>
								<?php echo form_input('sotk', (isset($_POST['sotk']) ? $_POST['sotk'] : ''), 'class="form-control input-tip" id="sotk"'); ?>
							</div>
							<div class="clearfix"></div>						
							<div class="_note_add_i" id="bt">
									<div class="form-group">
										<?= lang("Ghi chú", "renote"); ?>
										<?php echo form_textarea('note', (isset($_POST['note']) ? $_POST['note'] : ""), 'class="form-control" id="renote" style="margin-top: 10px; height: 100px;"'); ?>
									</div>
							</div>
							<div class="clearfix"></div>	
							<div class="form-group">
				            	<input type="checkbox" checked="checked" class="checkbox" value="1" name="is_tragop" id="is_tragop" <?= $this->input->post('is_tragop') ? 'checked="checked"' : ''; ?>>
				        		<?= lang('Phương thức thanh toán trả góp'); ?>
									
							</div>
						</div>
							
                    </div>
                </div>
            </div>

        </div>
    </div>
	 <?php echo form_close(); ?>
</div>

