<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php

$v = "";
if ($this->input->post('reference_no')) {
    $v .= "&reference_no=" . $this->input->post('reference_no');
}
if ($this->input->post('warehouse')) {
    $v .= "&warehouse=" . $this->input->post('warehouse');
}
if ($this->input->post('customer')) {
    $v .= "&customer=" . $this->input->post('customer');
}
if ($this->input->post('user')) {
    $v .= "&user=" . $this->input->post('user');
}
if ($this->input->post('start_date')) {
    $v .= "&start_date=" . $this->input->post('start_date');
}
if ($this->input->post('end_date')) {
    $v .= "&end_date=" . $this->input->post('end_date');
}

?>
<script>
    $(document).ready(function () {
		$('#form').hide();
		
        oTable = $('#REData').dataTable({
            "aaSorting": [[1, "desc"], [2, "desc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?=lang('all')?>"]],
            "iDisplayLength": <?=$Settings->rows_per_page?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?=site_url('returns/getReturnsBaoCaoChiTiet?v=1' . $v); ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?=$this->security->get_csrf_token_name()?>",
                    "value": "<?=$this->security->get_csrf_hash()?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            'fnRowCallback': function (nRow, aData, iDisplayIndex) {
                nRow.id = aData[8];
                nRow.className = "oreturn_link";
                return nRow;
            },
            "aoColumns": [{"mRender": fld}, null,null,null, null, null,null,null,{"mRender": currencyFormat},{"mRender": currencyFormat},{"mRender": currencyFormat},{"mRender": currencyFormat}, {"mRender": currencyFormat}],
            "fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {
                var gtotal = 0,giam=0,sl=0, tt=0;
                for (var i = 0; i < aaData.length; i++) {
					sl += parseFloat(aaData[aiDisplay[i]][8]);
					tt += parseFloat(aaData[aiDisplay[i]][10]);					
					giam += parseFloat(aaData[aiDisplay[i]][11]);
                    gtotal += parseFloat(aaData[aiDisplay[i]][12]);
					
                }
                var nCells = nRow.getElementsByTagName('th');
                nCells[8].innerHTML = currencyFormat(parseFloat(sl));
				nCells[10].innerHTML = currencyFormat(parseFloat(tt));
				nCells[11].innerHTML = currencyFormat(parseFloat(giam));
				nCells[12].innerHTML = currencyFormat(parseFloat(gtotal));
            }
        }).fnSetFilteringDelay().dtFilter([
            {column_number: 0, filter_default_label: "[<?=lang('date');?> (yyyy-mm-dd)]", filter_type: "text", data: []},
            {column_number: 1, filter_default_label: "[<?=lang('reference_no');?>]", filter_type: "text", data: []},
            {column_number: 2, filter_default_label: "[<?=lang('Kho');?>]", filter_type: "text", data: []},
			{column_number: 3, filter_default_label: "[<?=lang('ĐVGH');?>]", filter_type: "text", data: []},
			{column_number: 4, filter_default_label: "[<?=lang('biller');?>]", filter_type: "text", data: []},
            {column_number: 5, filter_default_label: "[<?=lang('customer');?>]", filter_type: "text", data: []},
			{column_number: 6, filter_default_label: "[<?=lang('Sản phẩm');?>]", filter_type: "text", data: []},
        ], "footer");

       

        $(document).on('click', '.reedit', function (e) {
            if (localStorage.getItem('reitems')) {
                e.preventDefault();
                var href = $(this).attr('href');
                bootbox.confirm("<?=lang('you_will_loss_return_data')?>", function (result) {
                    if (result) {
                        window.location.href = href;
                    }
                });
            }
        });
		
		var pb = <?= json_encode($pb); ?>;
        function paid_by(x) {
            return (x != null) ? (pb[x] ? pb[x] : x) : x;
        }

        function ref(x) {
            return (x != null) ? x : ' ';
        }
		 function attachment(x) {
            if (x != null) {
                return '<a href="' + site.base_url + 'assets/uploads/' + x + '" target="_blank"><i class="fa fa-chain"></i></a>';
            }
            return x;
        }
		$('.toggle_down').click(function () {
            $("#form").slideDown();
            return false;
        });
        $('.toggle_up').click(function () {
            $("#form").slideUp();
            return false;
        });
		
    });

</script>

<div class="box">
    <div class="box-header">
        <h2 class="blue"><i
                class="fa-fw fa fa-random"></i> Báo cáo khách trả hàng chi tiết
        </h2>
		<div class="main-task-lhson box-icon">
			<ul class="btn-tasks">
				<li class="dropdown">
                    <a href="#" id="pdf" class="tip" title="<?= lang('download_pdf') ?>">
                        <i class="icon fa fa-file-pdf-o"></i>
                    </a>
                </li>
                <li class="dropdown">
                    <a href="#" id="xls" class="tip" title="<?= lang('download_xls') ?>">
                        <i class="icon fa fa-file-excel-o"></i>
                    </a>
                </li>
			</ul>
		</div>	
		<div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a href="#" class="toggle_up tip" title="<?= lang('hide_form') ?>">
                        <i class="icon fa fa-toggle-up"></i>
                    </a>
                </li>
                <li class="dropdown">
                    <a href="#" class="toggle_down tip" title="<?= lang('show_form') ?>">
                        <i class="icon fa fa-toggle-down"></i>
                    </a>
                </li>
				<li class="dropdown"><a target="_blank" href="<?php echo site_url('returns/baocao');?>" id="tongquan" class="tip" title="Báo cáo chi tiết"><i
                            class="icon fa fa-desktop"></i>Báo cáo tổng quan </a></li>
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
				<div id="form">
				<?php echo form_open("returns/baocaochitiet"); ?>
						<div class="row">
							<div class="col-sm-4">
								<div class="form-group">
									<label class="control-label" for="reference_no"><?= lang("reference_no"); ?></label>
									<?php echo form_input('reference_no', (isset($_POST['reference_no']) ? $_POST['reference_no'] : ""), 'class="form-control tip" id="reference_no"'); ?>

								</div>
							</div>

							<div class="col-sm-4">
								<div class="form-group">
									<label class="control-label" for="from_warehouse">Kho</label>
									<?php
									$ct2[""] = lang('select').' '.lang('Kho');
									foreach ($warehouses as $warehouse) {
										$ct2[$warehouse->id] = $warehouse->name;
									}
																	
									echo form_dropdown('warehouse', $ct2, (isset($_POST['warehouse']) ? $_POST['warehouse'] : ""), 'class="form-control" id="warehouse" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("warehouse") . '"');
									?>
								</div>
							</div>
							
							<div class="col-sm-4">
								<div class="form-group">
									<label class="control-label" for="user"><?= lang("created_by"); ?></label>
									<?php
									$us[""] = lang('select').' '.lang('user');
									foreach ($users as $user) {
										$us[$user->id] = $user->first_name . " " . $user->last_name;
									}
									echo form_dropdown('user', $us, (isset($_POST['user']) ? $_POST['user'] : ""), 'class="form-control" id="user" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("user") . '"');
									?>
								</div>
							</div>
							<div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="customer"><?= lang("customer"); ?></label>
                                <?php echo form_input('customer','', 'class="form-control" id="customer" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("customer") . '"'); ?>
                            </div>
                        </div>
							<div class="col-sm-4">
								<div class="form-group">
									<?= lang("start_date", "start_date"); ?>
									<?php echo form_input('start_date', (isset($_POST['start_date']) ? $_POST['start_date'] : ""), 'class="form-control datetime" id="start_date"'); ?>
								</div>
							</div>
							<div class="col-sm-4">
								<div class="form-group">
									<?= lang("end_date", "end_date"); ?>
									<?php echo form_input('end_date', (isset($_POST['end_date']) ? $_POST['end_date'] : ""), 'class="form-control datetime" id="end_date"'); ?>
								</div>
							</div>
						</div>
						<div class="form-group">
							<div
								class="controls"> <?php echo form_submit('submit_report', $this->lang->line("submit"), 'class="btn btn-primary"'); ?> </div>
						</div>
						<?php echo form_close(); ?>
					</div>
                </div>
                <div class="clearfix"></div>
                <div class="table-responsive">
                    <table id="REData" class="table table-bordered table-hover table-striped no-print">
                        <thead>
                        <tr>
                            <th><?= lang("date"); ?></th>
                            <th><?= lang("reference_no"); ?></th>
                            <th><?= lang("Kho"); ?></th>
							<th><?= lang("ĐVGH"); ?></th>
							<th><?= lang("biller"); ?></th>
                            <th><?= lang("customer"); ?></th>
							<th><?= lang("Sản phẩm"); ?></th>
							<th><?= lang("ĐVT"); ?></th>
							<th><?= lang("Số lượng"); ?></th>
							<th><?= lang("Giá bán"); ?></th>
							<th><?= lang("Thành tiền"); ?></th>
							<th><?= lang("Phụ Thu"); ?></th>
                            <th><?= lang("grand_total"); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="13" class="dataTables_empty"><?= lang("loading_data"); ?></td>
                        </tr>
                        </tbody>
                        <tfoot class="dtFilter">
                        <tr class="active">
                            <th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th>
                            <th><?= lang("grand_total"); ?></th>
                        </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        $('#pdf').click(function (event) {
            event.preventDefault();
            window.location.href = "<?=site_url('returns/getReturnsBaoCaoChiTiet/pdf/?v=1'.$v)?>";
            return false;
        });
        $('#xls').click(function (event) {
            event.preventDefault();
            window.location.href = "<?=site_url('returns/getReturnsBaoCaoChiTiet/0/xls/?v=1'.$v)?>";
            return false;
        });
        $('#image').click(function (event) {
            event.preventDefault();
            html2canvas($('.box'), {
                onrendered: function (canvas) {
                    var img = canvas.toDataURL()
                    window.open(img);
                }
            });
            return false;
        });
    });
</script>