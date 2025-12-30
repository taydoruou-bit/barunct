<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script>
    $(document).ready(function () {
        oTable = $('#CusData').dataTable({
            "aaSorting": [[0, "asc"], [1, "asc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= site_url('reports/getCustomers/0/0/'.(int)$this->input->post('loai_fillter')) ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                var loai_fillter=$("#loai_fillter").val();
                if(loai_fillter==''){
                    loai_fillter=0;
                }
                var start_date=$("#start_date").val();
                if(start_date==''){
                    start_date=0;
                }
                var end_date=$("#end_date").val();
                if(end_date==''){
                    end_date=0;
                }
                sSource='<?= site_url('reports/getCustomers') ?>/0/0/'+loai_fillter+"/"+start_date+"/"+end_date;                     
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            "aoColumns": [null, null, {
                "mRender": decimalFormat,
                "bSearchable": false
            }, {"mRender": currencyFormat, "bSearchable": false}, {
                "mRender": currencyFormat,
                "bSearchable": false
            }, {"mRender": currencyFormat, "bSearchable": false},{"mRender": currencyFormat, "bSearchable": false},{"mRender": currencyFormat, "bSearchable": false},{"mRender": currencyFormat, "bSearchable": false},{"mRender": currencyFormat, "bSearchable": false},{"mRender": currencyFormat, "bSearchable": false}, {"bSortable": false}],
            "fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {
                var purchases = 0, total = 0, paid = 0, balance = 0; nobandau=0;
                for (var i = 0; i < aaData.length; i++) {
                    purchases += parseFloat(aaData[aiDisplay[i]][4]);
                    total += parseFloat(aaData[aiDisplay[i]][5]);
                    paid += parseFloat(aaData[aiDisplay[i]][6]);
                    nobandau += parseFloat(aaData[aiDisplay[i]][7]);
					balance += parseFloat(aaData[aiDisplay[i]][8]);
                }
                var nCells = nRow.getElementsByTagName('th');
                nCells[4].innerHTML = decimalFormat(parseFloat(purchases));
                nCells[5].innerHTML = currencyFormat(parseFloat(total));
                nCells[6].innerHTML = currencyFormat(parseFloat(paid));
				nCells[7].innerHTML = currencyFormat(parseFloat(paid));
                nCells[8].innerHTML = currencyFormat(parseFloat(balance));
            }
        }).fnSetFilteringDelay().dtFilter([
            {column_number: 0, filter_default_label: "[<?=lang('company');?>]", filter_type: "text", data: []},
            {column_number: 1, filter_default_label: "[<?=lang('name');?>]", filter_type: "text", data: []},
            {column_number: 2, filter_default_label: "[<?=lang('phone');?>]", filter_type: "text", data: []},
            {column_number: 3, filter_default_label: "[<?=lang('email_address');?>]", filter_type: "text", data: []},
        ], "footer");
        $("#loai_fillter").change(function(e){                                                   
            oTable.fnClearTable();
            oTable.fnDraw();                 
        });
        $("#end_date").change(function(e){                                                   
            oTable.fnClearTable();
            oTable.fnDraw();                 
        });
        $("#start_date").change(function(e){                                                   
            oTable.fnClearTable();
            oTable.fnDraw();                 
        });
    });
</script>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-users"></i><?= lang('customers'); ?></h2>

        <div class="box-icon">
            <?php
            $wh[""] = lang('select').' '.lang('Tất cả khách hàng');
            $wh["1"] = lang('Khách có công nợ');
            $wh["2"] = lang('Khách không công nợ');
            
            echo form_dropdown('loai_fillter', $wh, (isset($_POST['loai_fillter']) ? $_POST['loai_fillter'] : ""), 'class="form-control" id="loai_fillter" data-placeholder="' . $this->lang->line("Lọc công nợ") . '"');
            ?>
            
            <?php echo form_input('end_date', (isset($_POST['end_date']) ? $_POST['end_date'] : ""), 'class="form-control datetime" id="end_date" autocomplete="off" placeholder="Đến ngày"'); ?>
            <?php echo form_input('start_date', (isset($_POST['start_date']) ? $_POST['start_date'] : ""), 'class="form-control datetime" id="start_date" autocomplete="off" placeholder="Từ ngày"'); ?>
            
              

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
                            <th><?= lang("name"); ?></th>
                            <th><?= lang("Phone - Email"); ?></th>
                            <th><?= lang("Tổng"); ?></th>
                            <th><?= lang("total_amount"); ?></th>
                            <th><?= lang("paid"); ?></th>
                            <th><?= lang("balance"); ?></th>
							<th><?= lang("TC Thu hồi"); ?></th>
							<th><?= lang("TT Thu hồi"); ?></th>
							<th><?= lang("Đã TT TH"); ?></th>
							<th><?= lang("Còn nợ TH"); ?></th>
							<th><?= lang("Nợ ban đầu"); ?></th>
                            <th style="width:85px;"><?= lang("actions"); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="11" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
                        </tr>
                        </tbody>
                        <tfoot class="dtFilter">
                        <tr class="active">
                            <th></th>
                            <th></th>
                            <th class="text-center"><?= lang("total_sales"); ?></th>
                            <th class="text-center"><?= lang("total_amount"); ?></th>
                            <th class="text-center"><?= lang("paid"); ?></th>
                            <th class="text-center"><?= lang("balance"); ?></th>
							<th class="text-center"></th>
							<th class="text-center"></th>
							<th class="text-center"></th>
							<th class="text-center"></th>
							<th class="text-center"><?= lang("Nợ ban đầu"); ?></th>
                            <th style="width:50px;"><?= lang("actions"); ?></th>
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
            window.location.href = "<?=site_url('reports/getCustomers/pdf/')?>"+$("#loai_fillter").val()+"/"+$("#start_date").val()+"/"+$("#end_date").val();
            return false;
        });
        $('#xls').click(function (event) {
            event.preventDefault();
            window.location.href = "<?=site_url('reports/getCustomers/0/xls/')?>"+$("#loai_fillter").val()+"/"+$("#start_date").val()+"/"+$("#end_date").val();
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
        $("#start_date").datetimepicker({
                format:'yyyy-mm-dd',
                fontAwesome: true,
                language: 'sma',
                weekStart: 1,
                todayBtn: 1,
                autoclose: 1,
                todayHighlight: 1,
                startView: 2,
                forceParse: 0, 
                showTimepicker: false,
                minView: 2,
                showClear:true,
                showClose:true,
        }).datetimepicker('update','');
        $("#end_date").datetimepicker({
                format:'yyyy-mm-dd',
                fontAwesome: true,
                language: 'sma',
                weekStart: 1,
                todayBtn: 1,
                autoclose: 1,
                todayHighlight: 1,
                startView: 2,
                forceParse: 0, 
                showTimepicker: false,
                minView: 2,
                showClear:true,
                showClose:true,
        }).datetimepicker('update','');

        $("#start_date").change(function(){
            $("#end_date").datetimepicker({
                format:'yyyy-mm-dd',
                fontAwesome: true,
                language: 'sma',
                weekStart: 1,
                todayBtn: 1,
                autoclose: 1,
                todayHighlight: 1,
                startView: 2,
                forceParse: 0, 
                showTimepicker: false,
                minView: 2,
                showClear:true,
                showClose:true,
             }).datetimepicker('update',new Date());
        });
    });
</script>
<style type="text/css">
    .box .box-header .box-icon {
    width: 50%;
}

div#s2id_loai_fillter {
    float: right;
    width: 200px;
    margin-top: 3px;
}

input#start_date,input#end_date {
    float: right;
    width: 100px;
    margin-top: 3px;
    margin-right: 5px;
}

</style>