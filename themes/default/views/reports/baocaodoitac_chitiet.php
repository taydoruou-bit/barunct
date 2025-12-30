<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="row">
    <div class="col-sm-12">
        <div class="row">
            <div class="col-sm-6">
                <div class="small-box padding1010 col-sm-4 bblue">
                    <h3><?= isset($tongcong) ? $this->sma->formatMoney($tongcong) : '0.00' ?></h3>

                    <p><?= lang('Tổng cộng') ?></p>
                </div>
                <div class="small-box padding1010 col-sm-4 blightOrange">
                    <h3><?= isset($dathanhtoan) ? $this->sma->formatMoney($dathanhtoan) : '0.00' ?></h3>

                    <p><?= lang('total_paid') ?></p>
                </div>
                <div class="small-box padding1010 col-sm-4 borange">
                    <h3><?= (isset($tongcong) || isset($dathanhtoan)) ? $this->sma->formatMoney($tongcong - $dathanhtoan + $nodauky) : $this->sma->formatMoney($nodauky); ?></h3>

                    <p><?= lang('due_amount') ?></p>
                </div>
            </div>
			<div class="col-sm-6">
                <div class="row">
					<div class="col-sm-4">
                        <div class="small-box padding1010 borange">
                            <div class="inner clearfix">
                                <a>
                                    <h3><?= $this->sma->formatMoney($nodauky) ?></h3>

                                    <p><?= lang('Nợ đầu kỳ') ?></p>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="small-box padding1010 bblue">
                            <div class="inner clearfix">
                                <a>
                                    <h3><?= $tongsl ?></h3>

                                    <p><?= lang('total_purchases') ?></p>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<ul id="myTab" class="nav nav-tabs no-print">
    <li class=""><a href="#purcahses-con" class="tab-grey"><?= lang('Đơn giao') ?></a></li>
    <li class=""><a href="#payments-con" class="tab-grey"><?= lang('payments') ?></a></li>
</ul>

<div class="tab-content">
    <div id="purcahses-con" class="tab-pane fade in">
        <?php
        $v = "&doitac=" . $user_id;
        if ($this->input->post('submit_giaohang_report')) {
            if ($this->input->post('biller')) {
                $v .= "&biller=" . $this->input->post('biller');
            }
            if ($this->input->post('warehouse')) {
                $v .= "&warehouse=" . $this->input->post('warehouse');
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
        }
        ?>
        <script>
        $(document).ready(function () {
            var dss = <?= json_encode(array('packing' => lang('Đang đóng gói'), 'delivering' => lang('Đang giao'), 'delivered' => lang('Đã giao hàng'))); ?>;
        function ds(x) {
            if (x == 'delivered') {
                return '<div class="text-center"><span class="label label-success">'+(dss[x] ? dss[x] : x)+'</span></div>';
            } else if (x == 'delivering') {
                return '<div class="text-center"><span class="label label-primary">'+(dss[x] ? dss[x] : x)+'</span></div>';
            } else if (x == 'packing') {
                return '<div class="text-center"><span class="label label-warning">'+(dss[x] ? dss[x] : x)+'</span></div>';
            }
            return x;
            return (x != null) ? (dss[x] ? dss[x] : x) : x;
        }
        oTable = $('#PoRData').dataTable({
            "aaSorting": [[1, "desc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= site_url('reports/getDeliveriesDoiTac/?v=1' . $v) ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            'fnRowCallback': function (nRow, aData, iDisplayIndex) {
                var oSettings = oTable.fnSettings();
                nRow.id = aData[9];
                nRow.className = "delivery_link";
                return nRow;
            },
            "aoColumns": [{"mRender": fld}, null,null, null, {"mRender": currencyFormat}, null,null, {"mRender": ds}, {"bSortable": false,"mRender": attachment}]
        }).fnSetFilteringDelay().dtFilter([
            {column_number: 0, filter_default_label: "[<?=lang('date');?> (yyyy-mm-dd)]", filter_type: "text", data: []},
            {column_number: 1, filter_default_label: "[<?=lang('do_reference_no');?>]", filter_type: "text", data: []},
            {column_number: 2, filter_default_label: "[<?=lang('sale_reference_no');?>]", filter_type: "text", data: []},
			{column_number: 3, filter_default_label: "[<?=lang('Đối tác');?>]", filter_type: "text", data: []},
			{column_number: 4, filter_default_label: "[<?=lang('Phí');?>]", filter_type: "text", data: []},
            {column_number: 5, filter_default_label: "[<?=lang('customer');?>]", filter_type: "text", data: []},
            {column_number: 6, filter_default_label: "[<?=lang('address');?>]", filter_type: "text", data: []},
            {column_number: 7, filter_default_label: "[<?=lang('status');?>]", filter_type: "text", data: []},
        ], "footer");
        });
        </script>
        <script type="text/javascript">
        $(document).ready(function () {
            $('#form').hide();
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

        <div class="box purchases-table">
            <div class="box-header">
                <h2 class="blue"><i class="fa-fw fa fa-star nb"></i> <?= lang('Báo cáo giao hàng'); ?> <?php
                if ($this->input->post('start_date')) {
                    echo "From " . $this->input->post('start_date') . " to " . $this->input->post('end_date');
                }
                ?></h2>

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
                    </ul>
                </div>
                <div class="box-icon">
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
            </div>
            <div class="box-content">
                <div class="row">
                    <div class="col-lg-12">
                        <div id="form">

                            <?php echo form_open("reports/doitac_report/" . $user_id); ?>
                            <div class="row">

                                
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
                                class="controls"> <?php echo form_submit('submit_giaohang_report', $this->lang->line("submit"), 'class="btn btn-primary"'); ?> </div>
                            </div>
                            <?php echo form_close(); ?>

                        </div>
                        <div class="clearfix"></div>


                        <div class="table-responsive">
                            <table id="PoRData"
                            class="table table-bordered table-hover table-striped table-condensed reports-table">
						   <thead>
							<tr>
								<th><?= lang("date"); ?></th>
								<th><?= lang("Mã Giao Hàng"); ?></th>
								<th><?= lang("Mã Bán Hàng"); ?></th>
								<th><?= lang("Đối tác"); ?></th>
								<th><?= lang("Phí GH"); ?></th>
								<th><?= lang("customer"); ?></th>
								<th><?= lang("address"); ?></th>
								<th><?= lang("status"); ?></th>
								<th style="min-width:30px; width: 30px; text-align: center;"><i class="fa fa-chain"></i></th>
							</tr>
							</thead>
							<tbody>
							<tr>
								<td colspan="9" class="dataTables_empty"><?= lang("loading_data"); ?></td>
							</tr>
							</tbody>
							<tfoot class="dtFilter">
							<tr class="active">
								<th style="min-width:30px; width: 30px; text-align: center;">
									<input class="checkbox checkft" type="checkbox" name="check"/>
								</th>
								<th></th><th></th><th></th>
								<th style="width:100px; text-align:center;"></th>
								<th style="min-width:100px;width:100px; text-align:center;"></th>
								<th></th><th></th>
								<th style="min-width:30px; width: 30px; text-align: center;"><i class="fa fa-chain"></i></th>
							</tr>
							</tfoot>
						</table>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<div id="payments-con" class="tab-pane fade in">
    <?php
    $p = "&doitac=" . $user_id;
    if ($this->input->post('submit_payment_report')) {
       
		if ($this->input->post('reference_no')) {
			$p .= "&reference_no=" . $this->input->post('reference_no');
		}
		if ($this->input->post('category')) {
			$p .= "&category=" . $this->input->post('category');
		}
		if ($this->input->post('note')) {
			$p .= "&note=" . $this->input->post('note');
		}
		if ($this->input->post('user')) {
			$p .= "&user=" . $this->input->post('user');
		}
		if ($this->input->post('start_date')) {
			$p .= "&start_date=" . $this->input->post('start_date');
		}
		if ($this->input->post('end_date')) {
			$p .= "&end_date=" . $this->input->post('end_date');
		}
    }
    ?>
    <script>
    $(document).ready(function () {
        var pb = <?= json_encode($pb); ?>;
        function paid_by(x) {
            return (x != null) ? (pb[x] ? pb[x] : x) : x;
        }

         function attachment(x) {
            if (x != null) {
                return '<a href="' + site.base_url + 'assets/uploads/' + x + '" target="_blank"><i class="fa fa-chain"></i></a>';
            }
            return x;
        }

        oTable = $('#PayRData').dataTable({
            "aaSorting": [[0, "desc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= site_url('reports/getPaymentsReportDoiTac/?v=1' . $p); ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            'fnRowCallback': function (nRow, aData, iDisplayIndex) {
                nRow.id = aData[7];
                nRow.className = "expense_link2";
                return nRow;
            },
            "aoColumns": [{"mRender": fld}, null, null, {"mRender": currencyFormat}, null, null, {"bSortable": false, "mRender": attachment}],
            "fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {
                var total = 0;
                for (var i = 0; i < aaData.length; i++) {
                    total += parseFloat(aaData[aiDisplay[i]][3]);
                }
                var nCells = nRow.getElementsByTagName('th');
                nCells[3].innerHTML = currencyFormat(total);
            }
        }).fnSetFilteringDelay().dtFilter([
            {column_number: 0, filter_default_label: "[<?=lang('date');?> (yyyy-mm-dd)]", filter_type: "text", data: []},
            {column_number: 1, filter_default_label: "[<?=lang('reference');?>]", filter_type: "text", data: []},
            {column_number: 2, filter_default_label: "[<?=lang('category');?>]", filter_type: "text", data: []},
            {column_number: 4, filter_default_label: "[<?=lang('note');?>]", filter_type: "text", data: []},
            {column_number: 5, filter_default_label: "[<?=lang('created_by');?>]", filter_type: "text", data: []},
        ], "footer");

    });
    </script>
    <script type="text/javascript">
    $(document).ready(function () {
        $('#payform').hide();
        $('.paytoggle_down').click(function () {
            $("#payform").slideDown();
            return false;
        });
        $('.paytoggle_up').click(function () {
            $("#payform").slideUp();
            return false;
        });
    });
    </script>

    <div class="box payments-table">
        <div class="box-header">
            <h2 class="blue"><i class="fa-fw fa fa-money nb"></i><?= lang('payments_report'); ?> <?php
            if ($this->input->post('start_date')) {
                echo "From " . $this->input->post('start_date') . " to " . $this->input->post('end_date');
            }
            ?></h2>

            <div class="box-icon">
                <ul class="btn-tasks">
                    <li class="dropdown">
                        <a href="#" class="paytoggle_up tip" title="<?= lang('hide_form') ?>">
                            <i class="icon fa fa-toggle-up"></i>
                        </a>
                    </li>
                    <li class="dropdown">
                        <a href="#" class="paytoggle_down tip" title="<?= lang('show_form') ?>">
                            <i class="icon fa fa-toggle-down"></i>
                        </a>
                    </li>
                </ul>
            </div>
            <div class="box-icon">
                <ul class="btn-tasks">
                    <li class="dropdown">
                        <a href="#" id="pdf1" class="tip" title="<?= lang('download_pdf') ?>">
                            <i class="icon fa fa-file-pdf-o"></i>
                        </a>
                    </li>
                    <li class="dropdown">
                        <a href="#" id="xls1" class="tip" title="<?= lang('download_xls') ?>">
                            <i class="icon fa fa-file-excel-o"></i>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
        <div class="box-content">
            <div class="row">
                <div class="col-lg-12">


                    <div id="payform">

                        <?php echo form_open("reports/doitac_report/" . $user_id."/#payments-con"); ?>
							<div class="row">
								<div class="col-sm-4">
									<div class="form-group">
										<label class="control-label" for="reference_no"><?= lang("reference_no"); ?></label>
										<?php echo form_input('reference_no', (isset($_POST['reference_no']) ? $_POST['reference_no'] : ""), 'class="form-control tip" id="reference_no"'); ?>

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
										<label class="control-label" for="category"><?= lang("category"); ?></label>
										<?php
										$ct[""] = lang('select').' '.lang('category');
										foreach ($categories as $category) {
											$ct[$category->id] = $category->name;
										}
										echo form_dropdown('category', $ct, (isset($_POST['category']) ? $_POST['category'] : ""), 'class="form-control" id="category" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("category") . '"');
										?>
									</div>
								</div>

								<div class="col-sm-4">
									<div class="form-group">
										<?= lang("note", "note"); ?>
										<?php echo form_input('note', (isset($_POST['note']) ? $_POST['note'] : ""), 'class="form-control" id="note"'); ?>
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
									class="controls"> <?php echo form_submit('submit_payment_report', $this->lang->line("submit"), 'class="btn btn-primary"'); ?> </div>
							</div>
							<?php echo form_close(); ?>

						</div>

                    </div>
                    <div class="clearfix"></div>

                    <div class="table-responsive">
                        <table id="PayRData"
                        class="table table-bordered table-hover table-striped table-condensed reports-table">
                        <thead>
                        <tr class="active">
                            <th class="col-xs-2"><?= lang("date"); ?></th>
                            <th class="col-xs-2"><?= lang("reference"); ?></th>
                            <th class="col-xs-2"><?= lang("category"); ?></th>
                            <th class="col-xs-1"><?= lang("amount"); ?></th>
                            <th class="col-xs-3"><?= lang("note"); ?></th>
                            <th class="col-xs-2"><?= lang("created_by"); ?></th>
                            <th style="min-width:30px; width: 30px; text-align: center;"><i class="fa fa-chain"></i>
                            </th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="8" class="dataTables_empty"><?= lang('loading_data_from_server'); ?></td>
                        </tr>
                        </tbody>
                        <tfoot class="dtFilter">
                        <tr class="active">
                            <th></th><th></th><th></th><th></th><th></th><th></th>
                            <th style="min-width:30px; width: 30px; text-align: center;"><i class="fa fa-chain"></i>
                            </th>
                        </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
<script type="text/javascript" src="<?= $assets ?>js/html2canvas.min.js"></script>
<script type="text/javascript">
$(document).ready(function () {
    $('#pdf').click(function (event) {
        event.preventDefault();
        window.location.href = "<?=site_url('reports/getDeliveriesDoiTac/pdf/?v=1'.$v)?>";
        return false;
    });
    $('#xls').click(function (event) {
        event.preventDefault();
        window.location.href = "<?=site_url('reports/getDeliveriesDoiTac/0/xls/?v=1'.$v)?>";
        return false;
    });
    $('#image').click(function (event) {
        event.preventDefault();
        html2canvas($('.purchases-table'), {
            onrendered: function (canvas) {
                var img = canvas.toDataURL()
                window.open(img);
            }
        });
        return false;
    });
    $('#pdf1').click(function (event) {
        event.preventDefault();
        window.location.href = "<?=site_url('reports/getPaymentsReportDoiTac/pdf/?v=1'.$p)?>";
        return false;
    });
    $('#xls1').click(function (event) {
        event.preventDefault();
        window.location.href = "<?=site_url('reports/getPaymentsReportDoiTac/0/xls/?v=1'.$p)?>";
        return false;
    });
    $('#image1').click(function (event) {
        event.preventDefault();
        html2canvas($('.payments-table'), {
            onrendered: function (canvas) {
                var img = canvas.toDataURL()
                window.open(img);
            }
        });
        return false;
    });
});
</script>
