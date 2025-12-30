<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script>
    $(document).ready(function () {
       
        $("#start_date_soquy").datetimepicker({
                format:'dd-mm-yyyy',
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

        $("#end_date_soquy").datetimepicker({
                format:'dd-mm-yyyy',
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
                                                    
        oTableSoQuy = $('#DoiData').dataTable({
            "aaSorting": [[1, "desc"], [2, "desc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?=lang('all')?>"]],
            "iDisplayLength": <?=$Settings->rows_per_page?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?=site_url('the/getThes');?>/'+$("#warehouse_soquy").val()+'/'+$("#start_date_soquy").val()+'/'+$("#end_date_soquy").val(),
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?=$this->security->get_csrf_token_name()?>",
                    "value": "<?=$this->security->get_csrf_hash()?>"
                });
                var start_date=$("#start_date_soquy").val();
                if(start_date==''){
                    start_date=0;
                }
                var end_date=$("#end_date_soquy").val();
                if(end_date==''){
                    end_date=0;
                }
                var kho=$("#warehouse_soquy").val();
                if(kho==''){
                    kho=0;
                }                
            sSource='<?= site_url('the/getThes') ?>/'+kho+'/'+start_date+'/'+end_date;


                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            'fnRowCallback': function (nRow, aData, iDisplayIndex) {
                nRow.id = aData[0];                
                $(nRow).attr("code", aData[2]);
                nRow.className = "osoquy_link";
                return nRow;
            },
            "aoColumns": [{"bSortable": false,"mRender": checkbox}, {"mRender": fld}, null,null, null,{"mRender": currencyFormat},{"mRender": currencyFormat},
            {
                "mRender": function ( data, type, row ) {
                    var f=parseFloat(row[5])-parseFloat(row[6]);
                    return formatMoney(f);
                },
                "aTargets": [ 0 ]
            },
            null,{"bSortable": false}],
            "fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {
                var gtotal = 0, paid = 0, balance = 0;
                for (var i = 0; i < aaData.length; i++) {
                    gtotal += parseFloat(aaData[aiDisplay[i]][5]);
                    paid += parseFloat(aaData[aiDisplay[i]][6]);
                    balance += parseFloat(aaData[aiDisplay[i]][5]-aaData[aiDisplay[i]][6]);
                }
                var nCells = nRow.getElementsByTagName('th');
                nCells[5].innerHTML = currencyFormat(parseFloat(gtotal));
                nCells[6].innerHTML = currencyFormat(parseFloat(paid));
                nCells[7].innerHTML = currencyFormat(parseFloat(balance));
            }
        }).fnSetFilteringDelay().dtFilter([
            {column_number: 1, filter_default_label: "[<?=lang('date');?> (yyyy-mm-dd)]", filter_type: "text", data: []},
            {column_number: 2, filter_default_label: "[<?=lang('Mã');?>]", filter_type: "text", data: []},
            {column_number: 3, filter_default_label: "[<?=lang('Tên');?>]", filter_type: "text", data: []},
            {column_number: 4, filter_default_label: "[<?=lang('Số tài khoản');?>]", filter_type: "text", data: []},
            
        ], "footer");
        
        $("#start_date_soquy").change(function(e){    
                            
             if($("#start_date_soquy").val()!=''&&$("#end_date_soquy").val()!=''){
                 oTableSoQuy.fnClearTable();
                 oTableSoQuy.fnDraw();
             }
        });
        $("#end_date_soquy").change(function(e){                    
             if($("#start_date_soquy").val()!=''&&$("#end_date_soquy").val()!=''){
                 oTableSoQuy.fnClearTable();
                 oTableSoQuy.fnDraw();
             }
        });
        $("#warehouse_soquy").change(function(e){   
             oTableSoQuy.fnClearTable();
             oTableSoQuy.fnDraw();                        
        });
    });

</script>

<?php if ($Owner || $GP['bulk_actions']) {
        echo form_open('the/the_actions', 'id="action-form"');
    }
?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i
                class="fa-fw fa fa-credit-card"></i><?php echo ' Phương thức thanh toán - Sổ Quỹ';?>
        </h2>
    <div class="col-sm-6" id="fillter-soquy">
        <div class="col-sm-4">
            <div class="form-group">
                <?php echo form_input('start_date_soquy', (isset($_POST['start_date']) ? $_POST['start_date'] :date('d-m-Y')), 'class="form-control datetime" placeholder="Từ ngày" id="start_date_soquy"'); ?>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="form-group">
                <?php echo form_input('end_date_soquy', (isset($_POST['end_date']) ? $_POST['end_date'] :date('d-m-Y')), 'class="form-control datetime" placeholder="đến ngày" id="end_date_soquy"'); ?>
            </div>
        </div>
        <?php 
        if (!empty($warehouses)) {           
        
        ?>
        <div class="col-sm-4" >
            <div class="form-group">
                <?php
                
                     $wh[""] = lang('select').' '.lang('warehouse');
                    foreach ($warehouses as $warehouse) {
                        $wh[$warehouse->id] = $warehouse->name;
                    }
                    echo form_dropdown('warehouse_soquy', $wh, (isset($_POST['warehouse']) ? $_POST['warehouse'] : ""), 'class="form-control" id="warehouse_soquy" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("warehouse") . '"');

               
                ?>
            </div>
        </div>
    <?php }else{
        ?>
        <input type="hidden" name="warehouse_soquy" id="warehouse_soquy" value="<?=$warehouse_id?>">
        <?php
    } ?>

    </div>
    <?php 
    if ($this->Owner||$this->Admin) {       
    
    ?>
        <div class="box-icon">
            <ul class="btn-tasks">                
                
                <div class="main-task-lhson">
                    <a class="btn btn-primary btncls" href="<?= site_url('the/add') ?>">
                        <i class="fa fa-plus-circle"></i> Thêm phương thức TT
                    </a>
                    <a href="#" class="bpo btn btn-primary btncls" title="<b>Xóa</b>" data-content="<p><?=lang('r_u_sure')?></p><button type='button' class='btn btn-danger' id='delete' data-action='delete'><?=lang('i_m_sure')?></a> <button class='btn bpo-close'><?=lang('no')?></button>" data-html="true" data-placement="left">
                                <i class="fa fa-trash-o"></i> Xóa
                            </a>
                </div>
            </ul>
        </div>
    <?php } ?>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <div class="table-responsive">
                    <table id="DoiData" class="table table-bordered table-hover table-striped no-print">
                        <thead>
                        <tr>
                            <th style="min-width:30px; width: 30px; text-align: center;">
                                <input class="checkbox checkft" type="checkbox" name="check"/>
                            </th>
                            <th><?= lang("date"); ?></th>
                            <th><?= lang("Mã"); ?></th>
                            <th><?= lang("Tên"); ?></th>
                            <th><?= lang("Số tài khoản"); ?></th>
                            <th><?= lang("Tổng thu"); ?></th>
                            <th><?= lang("Tổng chi"); ?></th>
                            <th><?= lang("Tồn quỹ"); ?></th>
                            <th><?= lang("Ghi chú"); ?></th>
                            <th style="width:80px; text-align:center;"><?= lang("actions"); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="11" class="dataTables_empty"><?= lang("loading_data"); ?></td>
                        </tr>
                        </tbody>
                        <tfoot class="dtFilter">
                        <tr class="active">
                            <th style="min-width:30px; width: 30px; text-align: center;">
                                <input class="checkbox checkft" type="checkbox" name="check"/>
                            </th>
                            <th></th><th></th><th></th> <th></th>   <th></th>   <th></th>                           
                            <th></th>
                            <th style="min-width:100px; width: 100px; text-align: center;"></th>   
                            
                            <th style="width:80px; text-align:center;"><?= lang("actions"); ?></th>
                        </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php if ($Owner || $GP['bulk_actions']) {?>
    <div style="display: none;">
        <input type="hidden" name="form_action" value="" id="form_action"/>
        <?=form_submit('performAction', 'performAction', 'id="action-form-submit"')?>
    </div>
    <?=form_close()?>
<?php }
?>
<style type="text/css">
    div#fillter-soquy .form-group {
        margin: 0px;
        margin-top: 3px;
    }
</style>