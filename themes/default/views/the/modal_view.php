<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog modal-lg no-modal-header" style="width: 1000px">
    <div class="modal-content">
        <div class="modal-body">
            <button type="button" class="close no-print" data-dismiss="modal" aria-hidden="true">
                <i class="fa fa-2x">&times;</i>
            </button>           
            <div class="well well-sm">
                <div class="row bold">
                    <div class="col-xs-12">
                        <h2 style="text-align: center;">SỔ QUỸ TIỀN MẶT</h2>
                            <div class="col-sm-2">
                                <div class="form-group">
                                    <?= lang("Từ", "start_date"); ?>
                                    <?php echo form_input('start_date', (isset($_POST['start_date']) ? $_POST['start_date'] : ""), 'class="form-control datetime" id="start_date" autocomplete="off"'); ?>
                                </div>
                            </div>
                            <div class="col-sm-2">
                                <div class="form-group">
                                    <?= lang("Đến", "end_date"); ?>
                                    <?php echo form_input('end_date', (isset($_POST['end_date']) ? $_POST['end_date'] : ""), 'class="form-control datetime" id="end_date" autocomplete="off"'); ?>
                                </div>
                            </div> 
                            <?php 
                            if (isset($soquy_id)) {
                                $_POST['soquy_fillter']=rawurldecode($soquy_id);
                            }
                            ?>
                            <div class="col-sm-3">
                                <div class="form-group">
                                   <?= lang("Phương thức thanh toán", "soquy_fillter"); ?>
                                   <?php
                                        $wh['0'] = 'Chọn PT thanh toán';
                                        foreach ($allsoquy as $soquy)
                                        {
                                            $wh[$soquy->code] = $soquy->name;
                                        }
                                        echo form_dropdown('soquy_fillter', $wh, (isset($_POST['soquy_fillter']) ? $_POST['soquy_fillter'] : ''), 'id="soquy_fillter" class="form-control input-tip" data-placeholder="' . lang("select") . ' ' . lang("phương thức") . '" style="width:100%;" ');
                                        ?>
                                </div>
                            </div>
                           
                            <?php 
                            if (!empty($warehouses)) {           
                            
                            ?>
                                 <div class="col-sm-3" >
                                <div class="form-group">
                                    <?= lang("Kho hàng", "warehouse_soquy_fillter"); ?>
                                    <?php
                                    $whkho[""] = lang('select').' '.lang('warehouse');
                                    foreach ($warehouses as $warehouse) {
                                        $whkho[$warehouse->id] = $warehouse->name;
                                    }
                                    echo form_dropdown('warehouse_soquy_fillter', $whkho, (isset($_POST['warehouse']) ? $_POST['warehouse'] : ""), 'class="form-control" id="warehouse_soquy_fillter" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("warehouse") . '"');
                                    ?>
                                </div>
                            </div>
                        <?php }else{
                            ?>
                            <input type="hidden" name="warehouse_soquy_fillter" id="warehouse_soquy_fillter" value="<?=$warehouse_id?>">
                            <?php } ?>
                            <div class="col-sm-2">
                                <div class="form-group">
                                    <label><a href="#" id="xls" class="tip" title="<?= lang('download_xls') ?>"><i
                            class="icon fa fa-file-excel-o"></i> Xuất Excel</a></label>
                                    <button id="btnxem" style="height: 32px;" class="btn btn-default btn-lg btn-block">Xem</button>
                                </div>                                    
                            </div>
                    </div>
                    </div>
                </div>
                <div class="clearfix"></div>
            </div>

            <div class="table-responsive">
                <table id="DoiDataSoQuy" class="table table-bordered table-hover table-striped">

                    <thead>
                    <tr>
                        <th rowspan="2"><?= lang("Ngày, tháng chứng từ"); ?></th>
                        <th colspan="2"><?= lang("Số hiệu chứng từ"); ?></th>
                        <th rowspan="2"><?= lang("Đối tượng"); ?></th>
                        <th rowspan="2"><?= lang("Diễn dãi"); ?></th>                        
                        <th rowspan="2"><?= lang("Thanh toán bằng"); ?></th>
                        <th colspan="3"><?= lang("Số tiền"); ?></th>
                        <th rowspan="2"><?= lang("Ghi chú"); ?></th>
                    </tr>
                    <tr>
                        <th><?= lang("Thu"); ?></th>
                        <th><?= lang("Chi"); ?></th>
                        <th><?= lang("Thu"); ?></th>                        
                        <th ><?= lang("Chi"); ?></th>
                        <th ><?= lang("Tồn"); ?></th>
                    </tr>
                    <tr>
                        <th>B</th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th>E</th>                        
                        <th></th>
                        <th>1</th>
                        <th>2</th>
                        <th>3</th>
                        <th>G</th>
                    </tr>

                    </thead>
                    
                    <tbody>      
                                     
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>
<script type="text/javascript">
    $(document).ready( function() {     
         $("#start_date").datetimepicker({
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
        $("#end_date").datetimepicker({
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
        

        var tondauky=0;
        oTable = $('#DoiDataSoQuy').dataTable({
            "aaSorting": [[0, "asc"], [3, "asc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?=lang('all')?>"]],
            "iDisplayLength": 10,
            'bProcessing': true, 'bServerSide': true,
            'bFilter': false, 'bInfo': false,"bSort":false,
            'sAjaxSource': '<?=site_url('the/getSoQuys/');?><?=$soquy_id;?>/'+$("#start_date").val()+'/'+$("#end_date").val()+'/'+$("#warehouse_soquy_fillter").val(),
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?=$this->security->get_csrf_token_name()?>",
                    "value": "<?=$this->security->get_csrf_hash()?>"
                });
                var start_date=$("#start_date").val();
                if(start_date==''){
                    start_date=0;
                }
                var end_date=$("#end_date").val();
                if(end_date==''){
                    end_date=0;
                }
                var kho=$("#warehouse_soquy_fillter").val();
                if(kho==''){
                    kho=0;
                }
                var soquy_fillter=$("#soquy_fillter").val();

                sSource='<?= site_url('the/getSoQuys') ?>/'+soquy_fillter+'/'+start_date+'/'+end_date+'/'+kho;

                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            'fnRowCallback': function (nRow, aData, iDisplayIndex) {
                if (iDisplayIndex==0) {
                    tondauky=parseFloat(aData[8]);
                }else{
                    //console.log(tondauky);                    
                    
                    tondauky=tondauky+parseFloat(aData[6])-parseFloat(aData[7]); 
                    $('td:eq(8)', nRow).html(currencyFormat(tondauky));
                }                
                return nRow;
            },
            "aoColumns": [{"mRender": fld}, null,null, null,null, null,{"mRender": currencyFormat},{"mRender": currencyFormat},
            {"mRender": currencyFormat},null],
            "fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {
                
            }
        }).fnSetFilteringDelay().dtFilter([], "footer");

        $("#end_date").change(function(e){                     
             if($("#start_date").val()!=''&&$("#end_date").val()!=''){
               
                 oTable.fnClearTable();
                 oTable.fnDraw();
             }
        });
        $("#btnxem").click(function(){
            oTable.fnClearTable();
            oTable.fnDraw();   
        });
        $("#soquy_fillter").change(function(){
            oTable.fnClearTable();
            oTable.fnDraw();   
        });
        $("#warehouse_soquy_fillter").change(function(){
            oTable.fnClearTable();
            oTable.fnDraw();   
        });

        $('#xls').click(function (event) {
            event.preventDefault();
            var start_date=$("#start_date").val();
        if(start_date==''){
            start_date=0;
        }
        var end_date=$("#end_date").val();
        if(end_date==''){
            end_date=0;
        }
        var kho=$("#warehouse_soquy_fillter").val();
                if(kho==''){
                    kho=0;
                }
        var soquy_fillter=$("#soquy_fillter").val();        
            window.location.href = "<?=site_url('the/getSoQuys/')?>"+soquy_fillter+'/'+start_date+'/'+end_date+'/'+kho+'/xls';
            return false;
        });
        
        
    });
</script>
