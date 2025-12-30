<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php

$v = "";
if ($this->input->post('reference_no')) {
    $v .= "&reference_no=" . $this->input->post('reference_no');
}
if ($this->input->post('to_warehouse')) {
    $v .= "&to_warehouse=" . $this->input->post('to_warehouse');
}
if ($this->input->post('from_warehouse')) {
    $v .= "&from_warehouse=" . $this->input->post('from_warehouse');
}
if ($this->input->post('note')) {
    $v .= "&note=" . $this->input->post('note');
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
        oTable = $('#TODataBC').dataTable({
            "aaSorting": [[1, "desc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= site_url('transfers/getTransfersBaoCao?v=1' . $v) ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            "aoColumns": [{"mRender": fld},null, null,null, null, null,{"mRender": currencyFormat},{"mRender": currencyFormat}, {"mRender": currencyFormat}, {"mRender": row_status}],
            'fnRowCallback': function (nRow, aData, iDisplayIndex) {
                var oSettings = oTable.fnSettings();
                nRow.id = aData[10];
                nRow.className = "transfer_link";
                return nRow;
            },
            "fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {
                var row_total = 0, tax = 0, gtotal = 0;
                for (var i = 0; i < aaData.length; i++) {
                    row_total += parseFloat(aaData[aiDisplay[i]][6]);
                    tax += parseFloat(aaData[aiDisplay[i]][7]);
                    gtotal += parseFloat(aaData[aiDisplay[i]][8]);
				
                }
					console.log(gtotal);
                var nCells = nRow.getElementsByTagName('th');
                nCells[6].innerHTML = currencyFormat(formatMoney(row_total));
                nCells[7].innerHTML = currencyFormat(formatMoney(tax));
                nCells[8].innerHTML = currencyFormat(gtotal);
            }
        }).fnSetFilteringDelay().dtFilter([
            {column_number: 0, filter_default_label: "[<?=lang('date');?> (yyyy-mm-dd)]", filter_type: "text", data: []},
            {column_number: 1, filter_default_label: "[<?=lang('ref_no');?>]", filter_type: "text", data: []},
            {
                column_number: 2,
                filter_default_label: "[<?=lang("Đối tác");?>]",
                filter_type: "text", data: []
            },
			{
                column_number: 3,
                filter_default_label: "[Nhân viên]",
                filter_type: "text", data: []
            },
			{
                column_number: 4,
                filter_default_label: "[<?=lang("warehouse").' ('.lang('from').')';?>]",
                filter_type: "text", data: []
            },
            {
                column_number: 5,
                filter_default_label: "[<?=lang("warehouse").' ('.lang('to').')';?>]",
                filter_type: "text", data: []
            },
            {column_number: 9, filter_default_label: "[<?=lang('status');?>]", filter_type: "text", data: []},
        ], "footer");
		
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
        <h2 class="blue"><i class="fa-fw fa fa-star-o"></i>Báo cáo <?= lang('transfers'); ?> tổng quan</h2>
			
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
				<li class="dropdown"><a target="_blank" href="<?php echo site_url('transfers/baocaochuyenchitiet');?>" id="chitiet" class="tip" title="Báo cáo chi tiết"><i
                            class="icon fa fa-list-ol"></i>Báo cáo chi tiết </a></li>
            </ul>
        </div>
		
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
				<div id="form">
				<?php echo form_open("transfers/baocaochuyen"); ?>
						<div class="row">
							<div class="col-sm-4">
								<div class="form-group">
									<label class="control-label" for="reference_no"><?= lang("reference_no"); ?></label>
									<?php echo form_input('reference_no', (isset($_POST['reference_no']) ? $_POST['reference_no'] : ""), 'class="form-control tip" id="reference_no"'); ?>

								</div>
							</div>

							<div class="col-sm-4">
								<div class="form-group">
									<label class="control-label" for="from_warehouse">Kho Chuyển</label>
									<?php
									$ct2[""] = lang('select').' '.lang('Kho Chuyển');
									foreach ($warehouses as $warehouse) {
										$ct2[$warehouse->id] = $warehouse->name;
									}
																	
									echo form_dropdown('from_warehouse', $ct2, (isset($_POST['from_warehouse']) ? $_POST['from_warehouse'] : ""), 'class="form-control" id="from_warehouse" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("warehouse") . '"');
									?>
								</div>
							</div>
							<div class="col-sm-4">
								<div class="form-group">
									<label class="control-label" for="to_warehouse">Kho Nhận</label>
									<?php
									$ct[""] = lang('select').' '.lang('Kho Nhận');
									foreach ($warehouses as $warehouse) {
										$ct[$warehouse->id] = $warehouse->name;
									}
																	
									echo form_dropdown('to_warehouse', $ct, (isset($_POST['to_warehouse']) ? $_POST['to_warehouse'] : ""), 'class="form-control" id="to_warehouse" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("warehouse") . '"');
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
                    <table id="TODataBC" cellpadding="0" cellspacing="0" border="0"
                           class="table table-bordered table-condensed table-hover table-striped no-print">
                        <thead>
                        <tr class="active">
                            <th><?= lang("date"); ?></th>
                            <th><?= lang("ref_no"); ?></th>
							<th><?= lang("ĐVGH"); ?></th>
							<th><?= lang("Nhân viên"); ?></th>
                            <th><?= lang("warehouse") . ' (' . lang('from') . ')'; ?></th>
                            <th><?= lang("warehouse") . ' (' . lang('to') . ')'; ?></th>
                            <th><?= lang("total"); ?></th>
                            <th><?= lang("Phí VC"); ?></th>
                            <th><?= lang("grand_total"); ?></th>
                            <th><?= lang("status"); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="11" class="dataTables_empty"><?= lang('loading_data_from_server'); ?></td>
                        </tr>
                        </tbody>
                        <tfoot class="dtFilter">
							<tr class="active">
								<th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th>
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
            window.location.href = "<?=site_url('transfers/getTransfersBaoCao/pdf/?v=1'.$v)?>";
            return false;
        });
        $('#xls').click(function (event) {
            event.preventDefault();
            window.location.href = "<?=site_url('transfers/getTransfersBaoCao/0/xls/?v=1'.$v)?>";
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