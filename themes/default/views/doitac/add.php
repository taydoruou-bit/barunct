<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script type="text/javascript">
    var count = 1, an = 1, product_variant = 0, DT = <?= $Settings->default_tax_rate ?>,
        product_tax = 0, invoice_tax = 0, product_discount = 0, order_discount = 0, total_discount = 0, total = 0, allow_discount = <?= ($Owner || $Admin || $this->session->userdata('allow_discount')) ? 1 : 0; ?>,
        tax_rates = <?php echo json_encode($tax_rates); ?>;

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
		echo form_open_multipart("doitac/add", $attrib);
		?>
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-plus"></i>Thêm đối tác</h2>
		<div class="main-task-lhson nhapkho">
			<button type="submit" class="btn btn-primary btncls" name="add_doitac" id="add_doitac">
				<i class="fa fa-save"></i>
				Thêm đối tác
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
								<?php echo form_input('code', (isset($_POST['code']) ? $_POST['code'] : ''), 'class="form-control input-tip" id="code"'); ?>
							</div>
							<div class="form-group">
								<?= lang("Tên", "name"); ?>
								<?php echo form_input('name', (isset($_POST['name']) ? $_POST['name'] : ''), 'class="form-control input-tip" id="name"'); ?>
							</div>
							<div class="form-group">
								<?= lang("Địa chỉ", "diachi"); ?>
								<?php echo form_input('diachi', (isset($_POST['diachi']) ? $_POST['diachi'] : ''), 'class="form-control input-tip" id="diachi"'); ?>
							</div>
							<div class="form-group">
								<?= lang("Nợ đầu kỳ", "nodauky"); ?>
								<?php echo form_input('nodauky', (isset($_POST['nodauky']) ? $_POST['nodauky'] : ''), 'class="form-control input-tip number" id="nodauky"'); ?>
							</div>
						</div>
						<div class="col-lg-6">
							<div class="form-group">
								<?= lang("Điện thoại", "dienthoai"); ?>
								<?php echo form_input('dienthoai', (isset($_POST['dienthoai']) ? $_POST['dienthoai'] : ''), 'class="form-control input-tip" id="dienthoai"'); ?>
							</div>	
							<div class="form-group">
								<?= lang("Email", "email"); ?>
								<?php echo form_input('email', (isset($_POST['email']) ? $_POST['email'] : ''), 'class="form-control input-tip" id="email"'); ?>
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
										<?php echo form_textarea('note', (isset($_POST['note']) ? $_POST['note'] : ""), 'class="form-control" id="renote" style="margin-top: 10px; height: 100px;"'); ?>
									</div>
							</div>
						</div>	
                    </div>
                </div>
            </div>

        </div>
    </div>
	 <?php echo form_close(); ?>
</div>

