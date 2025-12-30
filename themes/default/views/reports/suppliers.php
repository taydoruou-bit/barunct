<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script>
    $(document).ready(function () {
        oTable = $('#CusData').dataTable({
            "aaSorting": [[1, "asc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= site_url('reports/getSuppliers') ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            "aoColumns": [null, null, {
                "mRender": decimalFormat,
                "bSearchable": false
            }, {"mRender": currencyFormat, "bSearchable": false}, {
                "mRender": currencyFormat,
                "bSearchable": false
            },{"mRender": currencyFormat, "bSearchable": false}, {"mRender": decimalFormat, "bSearchable": false},{"mRender": currencyFormat, "bSearchable": false},{"mRender": currencyFormat, "bSearchable": false},{"mRender": currencyFormat, "bSearchable": false},{"mRender": currencyFormat, "bSearchable": false},{"bSortable": false}],
            "fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {
                var tongsl = 0, tongtien = 0, datt = 0, duno = 0, nobandau=0;
				var tongsltra = 0, tongtientra = 0, datttra = 0, dunotra = 0;
				
                for (var i = 0; i < aaData.length; i++) {
					
                    tongsl += parseFloat(aaData[aiDisplay[i]][2]);
                    tongtien += parseFloat(aaData[aiDisplay[i]][3]);
                    datt += parseFloat(aaData[aiDisplay[i]][4]);
					duno += parseFloat(aaData[aiDisplay[i]][5]);
					
					tongsltra += parseFloat(aaData[aiDisplay[i]][6]);
                    tongtientra += parseFloat(aaData[aiDisplay[i]][7]);
                    datttra += parseFloat(aaData[aiDisplay[i]][8]);
					dunotra += parseFloat(aaData[aiDisplay[i]][9]);
					
					nobandau += parseFloat(aaData[aiDisplay[i]][10]);
                    
                }
                var nCells = nRow.getElementsByTagName('th');
                nCells[2].innerHTML = decimalFormat(parseFloat(tongsl));
                nCells[3].innerHTML = currencyFormat(parseFloat(tongtien));
                nCells[4].innerHTML = currencyFormat(parseFloat(datt));
				nCells[5].innerHTML = currencyFormat(parseFloat(duno));
                nCells[6].innerHTML = decimalFormat(parseFloat(tongsltra));
				nCells[7].innerHTML = currencyFormat(parseFloat(tongtientra));
				nCells[8].innerHTML = currencyFormat(parseFloat(datttra));
				nCells[9].innerHTML = currencyFormat(parseFloat(dunotra));
				nCells[10].innerHTML = currencyFormat(parseFloat(nobandau));
            }
        }).fnSetFilteringDelay().dtFilter([
            {column_number: 0, filter_default_label: "[<?=lang('company');?>]", filter_type: "text", data: []},
            {column_number: 1, filter_default_label: "[<?=lang('name');?>]", filter_type: "text", data: []},
        ], "footer");
    });
</script>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-users"></i><?= lang('suppliers'); ?></h2>

        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown"><a href="#" id="pdf" class="tip" title="<?= lang('download_pdf') ?>"><i
                            class="icon fa fa-file-pdf-o"></i></a></li>
                <li class="dropdown"><a href="#" id="xls" class="tip" title="<?= lang('download_xls') ?>"><i
                            class="icon fa fa-file-excel-o"></i></a></li>
                <li class="dropdown"><a href="#" id="image" class="tip" title="<?= lang('save_image') ?>"><i
                            class="icon fa fa-file-picture-o"></i></a></li>
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">


                <div class="table-responsive">
                    <table id="CusData" cellpadding="0" cellspacing="0" border="0"
                           class="table table-bordered table-condensed table-hover table-striped reports-table">
                        <thead>
                        <tr class="primary">
                            <th><?= lang("company"); ?> - <?= lang("name"); ?></th>
                            <th><?= lang("phone"); ?>-Email</th>
                            <th>Tổng SL</th>
                            <th><?= lang("total_amount"); ?></th>
                            <th><?= lang("paid"); ?></th>							
                            <th><?= lang("balance"); ?></th>
							<th><?= lang("SL trả"); ?></th>
							<th><?= lang("TT Trả"); ?></th>
							<th><?= lang("Đã TT Trả"); ?></th>
							<th><?= lang("Dư nợ trả"); ?></th>
							<th><?= lang("Nợ ban đầu"); ?></th>							
                            <th style="width:85px;"><?= lang("actions"); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="12" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
                        </tr>
                        </tbody>
                        <tfoot class="dtFilter">
                        <tr class="active">
                            <th></th>
                            <th></th>
                            <th class="text-center"><?= lang("total_purchases"); ?></th>
                            <th class="text-center"><?= lang("total_amount"); ?></th>
                            <th class="text-center"><?= lang("paid"); ?></th>
							<th class="text-center"><?= lang("balance"); ?></th>
							<th class="text-center"><?= lang("SL Trả"); ?></th>
							<th class="text-center"><?= lang("TT Trả"); ?></th>
							<th class="text-center"><?= lang("Đã TT Trả"); ?></th>
							<th class="text-center"><?= lang("Dư nợ trả"); ?></th>
							<th class="text-center"><?= lang("Nợ ban đầu"); ?></th>                            
                            <th style="width:55px; text-align: center;"><?= lang("actions"); ?></th>
                        </tr>
                        </tfoot>
                    </table>
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
            window.location.href = "<?=site_url('reports/getSuppliers/pdf')?>";
            return false;
        });
        $('#xls').click(function (event) {
            event.preventDefault();
            window.location.href = "<?=site_url('reports/getSuppliers/0/xls')?>";
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