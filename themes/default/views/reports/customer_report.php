<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="row">
    <div class="col-sm-12">
        <div class="row">
            <div class="col-sm-6">
                <div class="small-box padding1010 col-sm-4 bblue">
                    <h3><?= isset($sales->total_amount) ? $this->sma->formatMoney($sales->total_amount) : '0.00' ?></h3>

                    <p>Tiền mua</p>
                </div>
                <div class="small-box padding1010 col-sm-4 bdarkGreen">
                    <h3><?= isset($sales->paid) ? $this->sma->formatMoney($sales->paid) : '0.00' ?></h3>

                    <p><?= lang('total_paid') ?></p>
                </div>
                <div class="small-box padding1010 col-sm-4 borange">
                    <h3><?= (isset($sales->total_amount) || isset($sales->paid)) ? $this->sma->formatMoney($sales->total_amount - $sales->paid + $khachhang->nobandau) : $this->sma->formatMoney($khachhang->nobandau); ?></h3>

                    <p>
                    <?= lang('due_amount') ?></p>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="row">
                    <div class="col-sm-4">
                        <div class="small-box padding1010 bblue">
                            <div class="inner clearfix">
                                <a>
                                    <h3><?= $total_sales ?></h3>

                                    <p><?= lang('total_sales') ?></p>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="small-box padding1010 blightBlue">
                            <div class="inner clearfix">
                                <a>
                                    <h3><?= $total_quotes ?></h3>

                                    <p><?= lang('total_quotes') ?></p>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="small-box padding1010 borange">
                            <div class="inner clearfix">
                                <a>
                                    <h3><?= $this->sma->formatMoney($khachhang->nobandau); ?></h3>

                                    <p> Nợ ban đầu</p>
                                </a>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-6">
                <div class="small-box padding1010 col-sm-4 bblue">
                    <h3><?= isset($sales->total_amount) ? $this->sma->formatMoney($thuhoi->total_amount) : '0.00' ?></h3>

                    <p>Tiền Trả Hàng</p>
                </div>
                <div class="small-box padding1010 col-sm-4 bdarkGreen">
                    <h3><?= isset($sales->paid) ? $this->sma->formatMoney($thuhoi->paid) : '0.00' ?></h3>

                    <p><?= lang('total_paid') ?></p>
                </div>
                <div class="small-box padding1010 col-sm-4 borange">
                    <h3><?= (isset($thuhoi->total_amount) || isset($thuhoi->paid)) ? $this->sma->formatMoney($thuhoi->total_amount - $thuhoi->paid ) : 0; ?></h3>

                    <p>
                    <?= lang('due_amount') ?></p>
                </div>
                
            </div>
            <div class="col-sm-6">
                <div class="row">
                    <div class="col-sm-4">
                            <div class="small-box padding1010 borange">
                                <div class="inner clearfix">
                                    <a>
                                        <h3><?= $this->sma->formatMoney($total_thuhoi); ?></h3>

                                        <p> Tổng trả hàng</p>
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
    <li class="<?=$this->input->post('start_date_theohoadon')?'':'';?>"><a href="#theongay" class="tab-grey"><?= lang('Theo ngày') ?></a></li>
    <li class="<?=$this->input->post('start_date_theohoadon')?'active':'';?>"><a href="#theohoadon" class="tab-grey"><?= lang('Hóa Đơn Công Nợ') ?></a></li>
    <li class=""><a href="#sales-con" class="tab-grey"><?= lang('sales') ?></a></li>
    <li class=""><a href="#sales-return" class="tab-grey">Thu hồi</a></li>
    <li class=""><a href="#payments-con" class="tab-grey"><?= lang('payments') ?></a></li>
    <li class=""><a href="#quotes-con" class="tab-grey"><?= lang('quotes') ?></a></li>
    <li class=""><a href="#deposits-con" class="tab-grey"><?= lang('deposits') ?></a></li>
</ul>

<div class="tab-content">
    <div id="theohoadon" class="tab-pane fade in <?=$this->input->post('start_date_theohoadon')?'active':'';?>"> 

        <?php
        $vhoadon = "&customer=" . $user_id;
        if ($this->input->post('submit_hoadon_report')) {
            
            if ($this->input->post('warehouse_id_hoadon')) {
                $vhoadon .= "&warehouse_id=" . $this->input->post('warehouse_id_hoadon');
            }
          
            if ($this->input->post('start_date_theohoadon')) {
                $vhoadon .= "&start_date=" . $this->input->post('start_date_theohoadon');
            }
            if ($this->input->post('end_date_theohoadon')) {
                $vhoadon .= "&end_date=" . $this->input->post('end_date_theohoadon');
            }
        }
        ?>
        <script>
        $(document).ready(function () {
            $("#start_date_theohoadon").datetimepicker({
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
            $("#end_date_theohoadon").datetimepicker({
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

            oTable = $('#TheoHoaDonData').dataTable({
            "aaSorting": [[0, "desc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= site_url('reports/getSalesReportTheoHoaDon/?v=1' . $vhoadon) ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            'fnRowCallback': function (nRow, aData, iDisplayIndex) {
                
            },
            "aoColumns": [{"mRender": fsd}, null,{'bVisible':false},null,null,{"mRender": formatNumber}, {"mRender": currencyFormat}, {"mRender": currencyFormat}],
            "fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {
                var sl = 0, tt = 0, balance = 0;
                for (var i = 0; i < aaData.length; i++) {
                    sl += parseFloat(aaData[aiDisplay[i]][5]);
                    
                    tt += parseFloat(aaData[aiDisplay[i]][7]);
                    //balance += parseFloat(aaData[aiDisplay[i]][4]);
                }
                var nCells = nRow.getElementsByTagName('th');
                 nCells[4].innerHTML = sl;
                 nCells[6].innerHTML = currencyFormat(parseFloat(tt));
                // nCells[4].innerHTML = currencyFormat(parseFloat(balance));
            }
        }).fnSetFilteringDelay().dtFilter([
        ], "footer");
        });
        </script>
        <script type="text/javascript">
        $(document).ready(function () {
            $('#form').hide();
            $('#formtheongay').hide();
            $('#formtheohoadon').hide();
            $('.toggle_down').click(function () {
                $('#formtheongay').slideDown();
                $('#formtheohoadon').slideDown();
                $("#form").slideDown();
                return false;
            });
            $('.toggle_up').click(function () {
                $('#formtheongay').slideUp();
                $('#formtheohoadon').slideUp();
                $("#form").slideUp();
                return false;
            });
            if ('true'=='<?=$this->input->post('start_date_theohoadon')?'true':false?>') {
                $('#formtheohoadon').slideDown();
            }
            if ('true'=='<?=$this->input->post('start_date_theongay')?'true':false?>') {
                $('#formtheongay').slideDown();
            }
        });
        </script>

        <div class="box theohoadon-table">
            <div class="box-header">
                <h2 class="blue"><i class="fa-fw fa fa-heart nb"></i><?= lang('Báo cáo mua hàng theo ngày'); ?> <?php
                if ($this->input->post('start_date')) {
                    echo "Từ " . $this->input->post('start_date') . " đến " . $this->input->post('end_date');
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
                            <a href="#" id="pdftheohoadon" class="tip" title="<?= lang('download_pdf') ?>">
                                <i
                                class="icon fa fa-file-pdf-o"></i>
                            </a>
                        </li>
                        <li class="dropdown">
                            <a href="#" id="xlstheohoadon" class="tip" title="<?= lang('download_xls') ?>">
                                <i
                                class="icon fa fa-file-excel-o"></i>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="box-content">
                <div class="row">
                    <div class="col-lg-12">
                        <div id="formtheohoadon">
                            <?php echo form_open("reports/customer_report/" . $user_id); ?>
                            <div class="row">                                
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label class="control-label" for="warehouse"><?= lang("warehouse"); ?></label>
                                        <?php
                                        $wh[""] = lang('select').' '.lang('warehouse');
                                        foreach ($warehouses as $warehouse) {
                                            $wh[$warehouse->id] = $warehouse->name;
                                        }
                                        echo form_dropdown('warehouse_id_hoadon', $wh, (isset($_POST['warehouse_id_hoadon']) ? $_POST['warehouse_id_hoadon'] : ""), 'class="form-control" id="warehouse_id_hoadon" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("warehouse") . '"');
                                        ?>
                                    </div>
                                </div>
                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <?= lang("start_date", "start_date_theohoadon"); ?>
                                            <?php echo form_input('start_date_theohoadon', (isset($_POST['start_date_theohoadon']) ? $_POST['start_date_theohoadon'] : ""), 'class="form-control datetime" autocomplete="off" id="start_date_theohoadon"'); ?>
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <?= lang("end_date", "end_date_theohoadon"); ?>
                                            <?php echo form_input('end_date_theohoadon', (isset($_POST['end_date_theohoadon']) ? $_POST['end_date_theohoadon'] : ""), 'class="form-control datetime" autocomplete="off" id="end_date_theohoadon"'); ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div
                                    class="controls"> <?php echo form_submit('submit_hoadon_report', $this->lang->line("submit"), 'class="btn btn-primary"'); ?> </div>
                                </div>
                                <?php echo form_close(); ?>

                            </div>
                            <div class="clearfix"></div>

                            <div class="table-responsive">
                                <table id="TheoHoaDonData"
                                       class="table table-bordered table-hover table-striped table-condensed reports-table">
                                    <thead>
                                    <tr>
                                        <th class="col-xs-1"><?= lang("date"); ?></th>
                                        <th class="col-xs-2"><?= lang("Kho"); ?></th>
                                        <th class="col-xs-1"><?= lang("IDSP"); ?></th>
                                        <th class="col-xs-2"><?= lang("Tên sản phẩm"); ?></th>                            
                                        <th class="col-xs-1"><?= lang("ĐVT"); ?></th>                            
                                        <th class="col-xs-1" style="color: red;text-align: center;"><?= lang("Số lượng"); ?></th>                            
                                        <th class="col-xs-2"><?= lang("Giá bán"); ?></th>                            
                                        <th class="col-xs-2"><?= lang("Thành tiền"); ?></th>                            
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr>
                                        <td colspan="8" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
                                    </tr>
                                    </tbody>
                                    <tfoot class="dtFilter">
                                    <tr class="active">
                                       <th class="col-xs-1"><?= lang("date"); ?></th>
                                        <th class="col-xs-2"><?= lang("Kho"); ?></th>
                                        <th class="col-xs-1"><?= lang("IDSP"); ?></th>
                                        <th class="col-xs-2"><?= lang("Tên sản phẩm"); ?></th>                            
                                        <th class="col-xs-1"><?= lang("ĐVT"); ?></th>                            
                                        <th class="col-xs-1" style="color: red;text-align: center;"><?= lang("Số lượng"); ?></th>                            
                                        <th class="col-xs-2"><?= lang("Giá bán"); ?></th>                            
                                        <th class="col-xs-2"><?= lang("Thành tiền"); ?></th>       
                                    </tr>
                                    </tfoot>
                                </table>
                            </div>
                    </div>
                </div>
            </div>
        </div>

    </div> <!-- end theo hoa don -->
<div id="theongay" class="tab-pane fade in <?=$this->input->post('start_date_theohoadon')?'':'';?>"> 

        <?php
        $vtheongay = "&customer=" . $user_id;
        if ($this->input->post('submit_theongay')) {
            
            if ($this->input->post('warehouse_theongay')) {
                $vtheongay .= "&warehouse_id=" . $this->input->post('warehouse_theongay');
            }
            
            if ($this->input->post('start_date_theongay')) {
                $vtheongay .= "&start_date=" . $this->input->post('start_date_theongay');
            }
            if ($this->input->post('end_date_theongay')) {
                $vtheongay .= "&end_date=" . $this->input->post('end_date_theongay');
            }
        }
        ?>
        <script>
        $(document).ready(function () {
            $("#start_date_theongay").datetimepicker({
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
            $("#end_date_theongay").datetimepicker({
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

            oTable = $('#TheoNgayData').dataTable({
            "aaSorting": [[0, "desc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= site_url('reports/getSalesReportTheoNgay/?v=1' . $vtheongay) ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            'fnRowCallback': function (nRow, aData, iDisplayIndex) {
                
            },
            "aoColumns": [{"mRender": fsd}, null,null,{"mRender": currencyFormat}, {"mRender": currencyFormat}, {"mRender": currencyFormat}],
            "fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {
                var gtotal = 0, paid = 0, balance = 0;
                for (var i = 0; i < aaData.length; i++) {
                    gtotal += parseFloat(aaData[aiDisplay[i]][3]);
                    paid += parseFloat(aaData[aiDisplay[i]][4]);
                }
                var nCells = nRow.getElementsByTagName('th');
                nCells[3].innerHTML = currencyFormat(parseFloat(gtotal));
                nCells[4].innerHTML = currencyFormat(parseFloat(paid));
                nCells[5].innerHTML = currencyFormat(parseFloat(gtotal-paid));
            }
        }).fnSetFilteringDelay().dtFilter([], "footer");
        });
        </script>
        

        <div class="box theongay-table">
            <div class="box-header">
                <h2 class="blue"><i class="fa-fw fa fa-heart nb"></i><?= lang('Báo cáo mua hàng - thanh toán theo ngày'); ?> <?php
                if ($this->input->post('start_date')) {
                    echo "Từ " . $this->input->post('start_date') . " đến " . $this->input->post('end_date');
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
                            <a href="#" id="pdftheongay" class="tip" title="<?= lang('download_pdf') ?>">
                                <i
                                class="icon fa fa-file-pdf-o"></i>
                            </a>
                        </li>
                        <li class="dropdown">
                            <a href="#" id="xlstheongay" class="tip" title="<?= lang('download_xls') ?>">
                                <i
                                class="icon fa fa-file-excel-o"></i>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="box-content">
                <div class="row">
                    <div class="col-lg-12">
                        <div id="formtheongay">
                            <?php echo form_open("reports/customer_report/" . $user_id); ?>
                            <div class="row">                                
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label class="control-label" for="warehouse"><?= lang("warehouse"); ?></label>
                                        <?php
                                        $wh[""] = lang('select').' '.lang('warehouse');
                                        foreach ($warehouses as $warehouse) {
                                            $wh[$warehouse->id] = $warehouse->name;
                                        }
                                        echo form_dropdown('warehouse_theongay', $wh, (isset($_POST['warehouse_theongay']) ? $_POST['warehouse_theongay'] : ""), 'class="form-control" id="warehouse_theongay" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("warehouse") . '"');
                                        ?>
                                    </div>
                                </div>
                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <?= lang("start_date", "start_date_theongay"); ?>
                                            <?php echo form_input('start_date_theongay', (isset($_POST['start_date_theongay']) ? $_POST['start_date_theongay'] : ""), 'class="form-control datetime" autocomplete="off" id="start_date_theongay"'); ?>
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <?= lang("end_date", "end_date_theongay"); ?>
                                            <?php echo form_input('end_date_theongay', (isset($_POST['end_date_theongay']) ? $_POST['end_date_theongay'] : ""), 'class="form-control datetime" autocomplete="off" id="end_date_theongay"'); ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div
                                    class="controls"> <?php echo form_submit('submit_theongay', $this->lang->line("submit"), 'class="btn btn-primary"'); ?> </div>
                                </div>
                                <?php echo form_close(); ?>

                            </div>
                            <div class="clearfix"></div>

                            <div class="table-responsive">
                                <table id="TheoNgayData"
                                       class="table table-bordered table-hover table-striped table-condensed reports-table">
                                    <thead>
                                    <tr>
                                        <th class="col-xs-2"><?= lang("date"); ?></th>
                                        <th class="col-xs-4"><?= lang("Kho"); ?></th>
                                        <th class="col-xs-4"><?= lang("Sản phẩm"); ?></th>
                                        <th class="col-xs-2"><?= lang("Mua hàng"); ?></th>
                                        <th class="col-xs-2"><?= lang("Thanh toán"); ?></th>                            
                                        <th class="col-xs-2"><?= lang("Còn nợ"); ?></th>                            
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr>
                                        <td colspan="6" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
                                    </tr>
                                    </tbody>
                                    <tfoot class="dtFilter">
                                    <tr class="active">
                                        <th class="col-xs-2"><?= lang("date"); ?></th>
                                        <th class="col-xs-4"><?= lang("Kho"); ?></th>
                                        <th class="col-xs-4"><?= lang("Sản phẩm"); ?></th>
                                        <th class="col-xs-2"><?= lang("Mua hàng"); ?></th>
                                        <th class="col-xs-2"><?= lang("Thanh toán"); ?></th>                            
                                        <th class="col-xs-2"><?= lang("Còn nợ"); ?></th> 
                                    </tr>
                                    </tfoot>
                                </table>
                            </div>
                    </div>
                </div>
            </div>
        </div>

    </div> <!-- end theo ngay -->
    <div id="sales-con" class="tab-pane fade">

        <?php
        $v = "&customer=" . $user_id;
        if ($this->input->post('submit_sale_report')) {
            if ($this->input->post('biller')) {
                $v .= "&biller=" . $this->input->post('biller');
            }
            if ($this->input->post('warehouse')) {
                $v .= "&warehouse=" . $this->input->post('warehouse');
            }
            if ($this->input->post('user')) {
                $v .= "&user=" . $this->input->post('user');
            }
            if ($this->input->post('serial')) {
                $v .= "&serial=" . $this->input->post('serial');
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
            oTable = $('#SlRData').dataTable({
            "aaSorting": [[0, "desc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= site_url('reports/getSalesReport/?v=1' . $v) ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            'fnRowCallback': function (nRow, aData, iDisplayIndex) {
                nRow.id = aData[11]; 
                nRow.className = (aData[6] > 0) ? "invoice_link2" : "invoice_link2 warning";
                return nRow;
            },
            "aoColumns": [{"mRender": fld}, null,{"mRender": null,"bSearchable": false}, {"mRender": null,"bSearchable": false},{"mRender": null,"bSearchable": false}, {"mRender": null,"bSearchable": false,'bVisible':false}, {
                "bSearchable": false,
                "mRender": pqLhsonFormat
            }, {"mRender": currencyFormat}, {"mRender": currencyFormat}, {"mRender": currencyFormat}, {"mRender": row_status}],
            "fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {
                var gtotal = 0, paid = 0, balance = 0;
                for (var i = 0; i < aaData.length; i++) {
                    gtotal += parseFloat(aaData[aiDisplay[i]][7]);
                    paid += parseFloat(aaData[aiDisplay[i]][8]);
                    balance += parseFloat(aaData[aiDisplay[i]][9]);
                }
                var nCells = nRow.getElementsByTagName('th');
                nCells[7].innerHTML = currencyFormat(parseFloat(gtotal));
                nCells[8].innerHTML = currencyFormat(parseFloat(paid));
                nCells[9].innerHTML = currencyFormat(parseFloat(balance));
            }
        }).fnSetFilteringDelay().dtFilter([
            {column_number: 0, filter_default_label: "[<?=lang('date');?> (yyyy-mm-dd)]", filter_type: "text", data: []},
            {column_number: 1, filter_default_label: "[<?=lang('reference_no');?>]", filter_type: "text", data: []},
            {column_number: 2, filter_default_label: "[<?=lang('Kho');?>]", filter_type: "text", data: []},
            {column_number: 3, filter_default_label: "[<?=lang('ĐVGH');?>]", filter_type: "text", data: []},
            {column_number: 4, filter_default_label: "[<?=lang('biller');?>]", filter_type: "text", data: []},
            {column_number: 5, filter_default_label: "[<?=lang('customer');?>]", filter_type: "text", data: []},
            {column_number: 10, filter_default_label: "[<?=lang('payment_status');?>]", filter_type: "text", data: []},
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

        <div class="box sales-table">
            <div class="box-header">
                <h2 class="blue"><i class="fa-fw fa fa-heart nb"></i><?= lang('customer_sales_report'); ?> <?php
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
                                <i
                                class="icon fa fa-file-pdf-o"></i>
                            </a>
                        </li>
                        <li class="dropdown">
                            <a href="#" id="xls" class="tip" title="<?= lang('download_xls') ?>">
                                <i
                                class="icon fa fa-file-excel-o"></i>
                            </a>
                        </li>
                        <li class="dropdown">
                            <a href="#" id="image" class="tip" title="<?= lang('save_image') ?>">
                                <i
                                class="icon fa fa-file-picture-o"></i>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="box-content">
                <div class="row">
                    <div class="col-lg-12">
                        <div id="form">

                            <?php echo form_open("reports/customer_report/" . $user_id); ?>
                            <div class="row">

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
                                        <label class="control-label" for="biller"><?= lang("biller"); ?></label>
                                        <?php
                                        $bl[""] = lang('select').' '.lang('biller');
                                        foreach ($billers as $biller) {
                                            $bl[$biller->id] = $biller->company != '-' ? $biller->company : $biller->name;
                                        }
                                        echo form_dropdown('biller', $bl, (isset($_POST['biller']) ? $_POST['biller'] : ""), 'class="form-control" id="biller" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("biller") . '"');
                                        ?>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label class="control-label" for="warehouse"><?= lang("warehouse"); ?></label>
                                        <?php
                                        $wh[""] = lang('select').' '.lang('warehouse');
                                        foreach ($warehouses as $warehouse) {
                                            $wh[$warehouse->id] = $warehouse->name;
                                        }
                                        echo form_dropdown('warehouse', $wh, (isset($_POST['warehouse']) ? $_POST['warehouse'] : ""), 'class="form-control" id="warehouse" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("warehouse") . '"');
                                        ?>
                                    </div>
                                </div>
                                <?php if($Settings->product_serial) { ?>
                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <?= lang('serial_no', 'serial'); ?>
                                            <?= form_input('serial', '', 'class="form-control tip" id="serial"'); ?>
                                        </div>
                                    </div>
                                    <?php } ?>
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
                                    class="controls"> <?php echo form_submit('submit_sale_report', $this->lang->line("submit"), 'class="btn btn-primary"'); ?> </div>
                                </div>
                                <?php echo form_close(); ?>

                            </div>
                            <div class="clearfix"></div>

                            <div class="table-responsive">
                                <table id="SlRData"
                           class="table table-bordered table-hover table-striped table-condensed reports-table">
                        <thead>
                        <tr>
                            <th><?= lang("date"); ?></th>
                            <th><?= lang("reference_no"); ?></th>
                            <th class="col-xs-1"><?= lang("Kho"); ?></th>
                            <th class="col-xs-1"><?= lang("ĐVGH"); ?></th>
                            <th class="col-xs-1"><?= lang("biller"); ?></th>                            
                            <th class="col-xs-1"><?= lang("customer"); ?></th>
                            <th class="col-xs-2"><?= lang("product_qty"); ?></th>
                            <th><?= lang("grand_total"); ?></th>
                            <th><?= lang("paid"); ?></th>
                            <th><?= lang("balance"); ?></th>
                            <th><?= lang("payment_status"); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="11" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
                        </tr>
                        </tbody>
                        <tfoot class="dtFilter">
                        <tr class="active">
                            <th style="max-width:65px;width:65px!important; text-align:center;"></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th><?= lang("product_qty"); ?></th>
                            <th><?= lang("grand_total"); ?></th>
                            <th><?= lang("paid"); ?></th>
                            <th><?= lang("balance"); ?></th>
                            <th></th>
                        </tr>
                        </tfoot>
                    </table>
                        </div>

                    </div>
                </div>
            </div>
        </div>

    </div>
     <div id="sales-return" class="tab-pane fade in">

        <?php
        $v = "&customer=" . $user_id;
        if ($this->input->post('submit_thuhoi_report')) {
            if ($this->input->post('biller')) {
                $v .= "&biller=" . $this->input->post('biller');
            }
            if ($this->input->post('warehouse')) {
                $v .= "&warehouse=" . $this->input->post('warehouse');
            }
            if ($this->input->post('user')) {
                $v .= "&user=" . $this->input->post('user');
            }
            if ($this->input->post('serial')) {
                $v .= "&serial=" . $this->input->post('serial');
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
            oTable = $('#SlRDataThuHoi').dataTable({
            "aaSorting": [[0, "desc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= site_url('reports/getThuHoiReport/?v=1' . $v) ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            'fnRowCallback': function (nRow, aData, iDisplayIndex) {
                nRow.id = aData[11]; 
                nRow.className = (aData[6] > 0) ? "invoice_link2" : "invoice_link2 warning";
                return nRow;
            },
            "aoColumns": [{"mRender": fld}, null,null, null,null, null, {
                "bSearchable": false,
                "mRender": pqLhsonFormat
            }, {"mRender": currencyFormat}, {"mRender": currencyFormat}, {"mRender": currencyFormat}, {"mRender": row_status}],
            "fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {
                var gtotal = 0, paid = 0, balance = 0;
                for (var i = 0; i < aaData.length; i++) {
                    gtotal += parseFloat(aaData[aiDisplay[i]][7]);
                    paid += parseFloat(aaData[aiDisplay[i]][8]);
                    balance += parseFloat(aaData[aiDisplay[i]][9]);
                }
                var nCells = nRow.getElementsByTagName('th');
                nCells[7].innerHTML = currencyFormat(parseFloat(gtotal));
                nCells[8].innerHTML = currencyFormat(parseFloat(paid));
                nCells[9].innerHTML = currencyFormat(parseFloat(balance));
            }
        }).fnSetFilteringDelay().dtFilter([
            {column_number: 0, filter_default_label: "[<?=lang('date');?> (yyyy-mm-dd)]", filter_type: "text", data: []},
            {column_number: 1, filter_default_label: "[<?=lang('reference_no');?>]", filter_type: "text", data: []},
            {column_number: 2, filter_default_label: "[<?=lang('Kho');?>]", filter_type: "text", data: []},
            {column_number: 3, filter_default_label: "[<?=lang('ĐVGH');?>]", filter_type: "text", data: []},
            {column_number: 4, filter_default_label: "[<?=lang('biller');?>]", filter_type: "text", data: []},
            {column_number: 5, filter_default_label: "[<?=lang('customer');?>]", filter_type: "text", data: []},
            {column_number: 10, filter_default_label: "[<?=lang('payment_status');?>]", filter_type: "text", data: []},
        ], "footer");
        });
        </script>
        <script type="text/javascript">
        $(document).ready(function () {
            $('#formreturn').hide();
            $('.toggle_down').click(function () {
                $("#formreturn").slideDown();
                return false;
            });
            $('.toggle_up').click(function () {
                $("#formreturn").slideUp();
                return false;
            });
        });
        </script>

        <div class="box sales-table">
            <div class="box-header">
                <h2 class="blue"><i class="fa-fw fa fa-heart nb"></i>Thu hồi <?php
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
                            <a href="#" id="pdfre" class="tip" title="<?= lang('download_pdf') ?>">
                                <i
                                class="icon fa fa-file-pdf-o"></i>
                            </a>
                        </li>
                        <li class="dropdown">
                            <a href="#" id="xlsre" class="tip" title="<?= lang('download_xls') ?>">
                                <i
                                class="icon fa fa-file-excel-o"></i>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="box-content">
                <div class="row">
                    <div class="col-lg-12">

                        <div id="formreturn">

                            <?php echo form_open("reports/customer_report/" . $user_id); ?>
                            <div class="row">

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
                                        <label class="control-label" for="biller"><?= lang("biller"); ?></label>
                                        <?php
                                        $bl[""] = lang('select').' '.lang('biller');
                                        foreach ($billers as $biller) {
                                            $bl[$biller->id] = $biller->company != '-' ? $biller->company : $biller->name;
                                        }
                                        echo form_dropdown('biller', $bl, (isset($_POST['biller']) ? $_POST['biller'] : ""), 'class="form-control" id="biller" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("biller") . '"');
                                        ?>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label class="control-label" for="warehouse"><?= lang("warehouse"); ?></label>
                                        <?php
                                        $wh[""] = lang('select').' '.lang('warehouse');
                                        foreach ($warehouses as $warehouse) {
                                            $wh[$warehouse->id] = $warehouse->name;
                                        }
                                        echo form_dropdown('warehouse', $wh, (isset($_POST['warehouse']) ? $_POST['warehouse'] : ""), 'class="form-control" id="warehouse" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("warehouse") . '"');
                                        ?>
                                    </div>
                                </div>
                                <?php if($Settings->product_serial) { ?>
                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <?= lang('serial_no', 'serial'); ?>
                                            <?= form_input('serial', '', 'class="form-control tip" id="serial"'); ?>
                                        </div>
                                    </div>
                                    <?php } ?>
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
                                    class="controls"> <?php echo form_submit('submit_thuhoi_report', $this->lang->line("submit"), 'class="btn btn-primary"'); ?> </div>
                                </div>
                                <?php echo form_close(); ?>

                            </div>
                            <div class="clearfix"></div>

                            <div class="table-responsive">
                                <table id="SlRDataThuHoi"
                           class="table table-bordered table-hover table-striped table-condensed reports-table">
                        <thead>
                        <tr>
                            <th><?= lang("date"); ?></th>
                            <th><?= lang("reference_no"); ?></th>
                            <th class="col-xs-1"><?= lang("Kho"); ?></th>
                            <th class="col-xs-1"><?= lang("ĐVGH"); ?></th>
                            <th class="col-xs-1"><?= lang("biller"); ?></th>                            
                            <th class="col-xs-1"><?= lang("customer"); ?></th>
                            <th class="col-xs-2"><?= lang("product_qty"); ?></th>
                            <th><?= lang("grand_total"); ?></th>
                            <th><?= lang("paid"); ?></th>
                            <th><?= lang("balance"); ?></th>
                            <th><?= lang("payment_status"); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="11" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
                        </tr>
                        </tbody>
                        <tfoot class="dtFilter">
                        <tr class="active">
                            <th style="max-width:65px;width:65px!important; text-align:center;"></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th><?= lang("product_qty"); ?></th>
                            <th><?= lang("grand_total"); ?></th>
                            <th><?= lang("paid"); ?></th>
                            <th><?= lang("balance"); ?></th>
                            <th></th>
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
        $p = "&customer=" . $user_id;
        if ($this->input->post('submit_payment_report')) {
            if ($this->input->post('pay_user')) {
                $p .= "&user=" . $this->input->post('pay_user');
            }
            if ($this->input->post('pay_start_date')) {
                $p .= "&start_date=" . $this->input->post('pay_start_date');
            }
            if ($this->input->post('pay_end_date')) {
                $p .= "&end_date=" . $this->input->post('pay_end_date');
            }
        }
        ?>
        <script>
        $(document).ready(function () {
            var pb = <?= json_encode($pb); ?>;
            function paid_by(x) {
                return (x != null) ? (pb[x] ? pb[x] : x) : x;
            }

            oTable = $('#PayRData').dataTable({
                "aaSorting": [[0, "desc"]],
                "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
                "iDisplayLength": <?= $Settings->rows_per_page ?>,
                'bProcessing': true, 'bServerSide': true,
                'sAjaxSource': '<?= site_url('reports/getPaymentsReport/?v=1' . $p) ?>',
                'fnServerData': function (sSource, aoData, fnCallback) {
                    aoData.push({
                        "name": "<?= $this->security->get_csrf_token_name() ?>",
                        "value": "<?= $this->security->get_csrf_hash() ?>"
                    });
                    $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
                },
                "aoColumns": [{"mRender": fld}, null, null, null,{'bVisible':false},{'bVisible':false},{"mRender": paid_by}, {"mRender": currencyFormat}, {"mRender": paid_by}],
                'fnRowCallback': function (nRow, aData, iDisplayIndex) {
                    nRow.id = aData[9];
                    nRow.className = "payment_link";
                    if (aData[8] == 'returned') {
                        nRow.className = "payment_link danger";
                    }
                    return nRow;
                },
                "fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {
                    var total = 0;
                    for (var i = 0; i < aaData.length; i++) {
                        total += parseFloat(aaData[aiDisplay[i]][7]);
                    }
                    var nCells = nRow.getElementsByTagName('th');
                    nCells[5].innerHTML = currencyFormat(parseFloat(total));
                }
            }).fnSetFilteringDelay().dtFilter([
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
                <h2 class="blue"><i class="fa-fw fa fa-money nb"></i><?= lang('customer_payments_report'); ?> <?php
                if ($this->input->post('start_date')) {
                    echo "From " . $this->input->post('start_date') . " to " . $this->input->post('end_date');
                }
                ?></h2>

                <div class="box-icon">
                    <ul class="btn-tasks">
                        <li class="dropdown">
                            <a href="#" class="paytoggle_up tip" title="<?= lang('hide_form') ?>">
                                <i
                                class="icon fa fa-toggle-up"></i>
                            </a>
                        </li>
                        <li class="dropdown">
                            <a href="#" class="paytoggle_down tip" title="<?= lang('show_form') ?>">
                                <i
                                class="icon fa fa-toggle-down"></i>
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
                        <li class="dropdown">
                            <a href="#" id="image1" class="tip" title="<?= lang('save_image') ?>">
                                <i class="icon fa fa-file-picture-o"></i>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="box-content">
                <div class="row">
                    <div class="col-lg-12">
                        <p class="introtext"><?= lang('customize_report'); ?></p>

                        <div id="payform">

                            <?php echo form_open("reports/customer_report/" . $user_id."/#payments-con"); ?>
                            <div class="row">

                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label class="control-label" for="user"><?= lang("created_by"); ?></label>
                                        <?php
                                        $us[""] = lang('select').' '.lang('user');
                                        foreach ($users as $user) {
                                            $us[$user->id] = $user->first_name . " " . $user->last_name;
                                        }
                                        echo form_dropdown('pay_user', $us, (isset($_POST['pay_user']) ? $_POST['pay_user'] : ""), 'class="form-control" id="user" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("user") . '"');
                                        ?>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <?= lang("start_date", "start_date"); ?>
                                        <?php echo form_input('pay_start_date', (isset($_POST['pay_start_date']) ? $_POST['pay_start_date'] : ""), 'class="form-control date" id="start_date"'); ?>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <?= lang("end_date", "end_date"); ?>
                                        <?php echo form_input('pay_end_date', (isset($_POST['pay_end_date']) ? $_POST['pay_end_date'] : ""), 'class="form-control date" id="end_date"'); ?>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div
                                class="controls"> <?php echo form_submit('submit_payment_report', $this->lang->line("submit"), 'class="btn btn-primary"'); ?> </div>
                            </div>
                            <?php echo form_close(); ?>

                        </div>
                        <div class="clearfix"></div>

                        <div class="table-responsive">
                            <table id="PayRData"
                            class="table table-bordered table-hover table-striped table-condensed reports-table reports-table">

                            <thead>
                                <tr>
                                    <th><?= lang("date"); ?></th>
                                    <th><?= lang("payment_ref"); ?></th>
                                    <th><?= lang("sale_ref"); ?></th>
                                    <th><?= lang("Nhân viên"); ?></th>
                                    <th><?= lang("Kho"); ?></th>
                                    <th><?= lang("HD NHẬP"); ?></th>
                                    <th><?= lang("paid_by"); ?></th>
                                    <th><?= lang("amount"); ?></th>
                                    <th><?= lang("type"); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="9" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
                                </tr>
                            </tbody>
                            <tfoot class="dtFilter">
                                <tr class="active">
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th><?= lang("amount"); ?></th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
<div id="quotes-con" class="tab-pane fade in">
    <script type="text/javascript">
    $(document).ready(function () {
        oTable = $('#QuRData').dataTable({
            "aaSorting": [[0, "desc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= site_url('reports/getQuotesReport/?v=1&customer='.$user_id) ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({
                    'dataType': 'json',
                    'type': 'POST',
                    'url': sSource,
                    'data': aoData,
                    'success': fnCallback
                });
            },
            "aoColumns": [{"mRender": fld}, null, null, null, {
                "bSearchable": false,
                "mRender": pqFormat
            }, {"mRender": currencyFormat}, {"mRender": row_status}],
        }).fnSetFilteringDelay().dtFilter([
            {column_number: 0, filter_default_label: "[<?=lang('date');?> (yyyy-mm-dd)]", filter_type: "text", data: []},
            {column_number: 1, filter_default_label: "[<?=lang('reference_no');?>]", filter_type: "text", data: []},
            {column_number: 2, filter_default_label: "[<?=lang('biller');?>]", filter_type: "text", data: []},
            {column_number: 3, filter_default_label: "[<?=lang('customer');?>]", filter_type: "text", data: []},
            {column_number: 5, filter_default_label: "[<?=lang('grand_total');?>]", filter_type: "text", data: []},
            {column_number: 6, filter_default_label: "[<?=lang('status');?>]", filter_type: "text", data: []},
        ], "footer");
    });
    </script>
    <div class="box">
        <div class="box-header">
            <h2 class="blue"><i class="fa-fw fa fa-heart-o nb"></i><?=  lang('quotes'); ?>
            </h2>

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
                    <li class="dropdown">
                        <a href="#" id="image1" class="tip image" title="<?= lang('save_image') ?>">
                            <i class="icon fa fa-file-picture-o"></i>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
        <div class="box-content">
            <div class="row">
                <div class="col-lg-12">
                    <p class="introtext"><?php echo lang('list_results'); ?></p>

                    <div class="table-responsive">
                        <table id="QuRData" class="table table-bordered table-hover table-striped table-condensed reports-table">
                            <thead>
                                <tr>
                                    <th><?= lang("date"); ?></th>
                                    <th><?= lang("reference_no"); ?></th>
                                    <th><?= lang("biller"); ?></th>
                                    <th><?= lang("customer"); ?></th>
                                    <th><?= lang("product_qty"); ?></th>
                                    <th><?= lang("grand_total"); ?></th>
                                    <th><?= lang("status"); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="7"
                                    class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
                                </tr>
                            </tbody>
                            <tfoot class="dtFilter">
                                <tr class="active">
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th><?= lang("product_qty"); ?></th>
                                    <th></th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div id="deposits-con" class="tab-pane fade in">
    <script type="text/javascript">
    $(document).ready(function () {
        oTable = $('#DepData').dataTable({
                "aaSorting": [[0, "desc"]],
                "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
                "iDisplayLength": <?= $Settings->rows_per_page ?>,
                'bProcessing': true, 'bServerSide': true,
                'sAjaxSource': '<?= site_url('reports/get_deposits/'.$user_id) ?>',
                'fnServerData': function (sSource, aoData, fnCallback) {
                    aoData.push({
                        "name": "<?= $this->security->get_csrf_token_name() ?>",
                        "value": "<?= $this->security->get_csrf_hash() ?>"
                    });
                    $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
                },
                "aoColumns": [{"mRender": fld}, {"mRender": currencyFormat}, null, null, {"mRender": decode_html}]
            }).fnSetFilteringDelay().dtFilter([
            {column_number: 0, filter_default_label: "[<?=lang('date');?> (yyyy-mm-dd)]", filter_type: "text", data: []},
            {column_number: 1, filter_default_label: "[<?=lang('amount');?>]", filter_type: "text", data: []},
            {column_number: 2, filter_default_label: "[<?=lang('paid_by');?>]", filter_type: "text", data: []},
            {column_number: 3, filter_default_label: "[<?=lang('created_by');?>]", filter_type: "text", data: []},
            {column_number: 4, filter_default_label: "[<?=lang('note');?>]", filter_type: "text", data: []},
        ], "footer");
    });
    </script>
    <div class="box">
        <div class="box-header">
            <h2 class="blue"><i class="fa-fw fa fa-heart-o nb"></i><?=  lang('quotes'); ?>
            </h2>

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
                    <li class="dropdown">
                        <a href="#" id="image1" class="tip image" title="<?= lang('save_image') ?>">
                            <i class="icon fa fa-file-picture-o"></i>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
        <div class="box-content">
            <div class="row">
                <div class="col-lg-12">
                    <p class="introtext"><?php echo lang('list_results'); ?></p>

                    <div class="table-responsive">
                        <table id="DepData" class="table table-bordered table-condensed table-hover table-striped reports-table">
                            <thead>
                            <tr class="primary">
                                <th class="col-xs-2"><?= lang("date"); ?></th>
                                <th class="col-xs-1"><?= lang("amount"); ?></th>
                                <th class="col-xs-1"><?= lang("paid_by"); ?></th>
                                <th class="col-xs-2"><?= lang("created_by"); ?></th>
                                <th class="col-xs-6"><?= lang("note"); ?></th>
                            </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="5" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
                                </tr>
                            </tbody>
                            <tfoot>
                            <tr class="primary">
                                <th class="col-xs-2"></th>
                                <th class="col-xs-1"></th>
                                <th class="col-xs-1"></th>
                                <th class="col-xs-2"></th>
                                <th class="col-xs-6"></th>
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
   $('#pdftheongay').click(function (event) {
        event.preventDefault();
        window.location.href = "<?=site_url('reports/getSalesReportTheoNgay/pdf/?v=1'.$vtheongay)?>";
        return false;
    });
    $('#xlstheongay').click(function (event) {
        event.preventDefault();
        window.location.href = "<?=site_url('reports/getSalesReportTheoNgay/0/xls/?v=1'.$vtheongay)?>";
        return false;
    });

    $('#pdftheohoadon').click(function (event) {
        event.preventDefault();
        window.location.href = "<?=site_url('reports/getSalesReportTheoHoaDon/pdf/?v=1'.$vhoadon)?>";
        return false;
    });
    $('#xlstheohoadon').click(function (event) {
        event.preventDefault();
        window.location.href = "<?=site_url('reports/getSalesReportTheoHoaDon/0/xls/?v=1'.$vhoadon)?>";
        return false;
    });
    $('#pdf').click(function (event) {
        event.preventDefault();
        window.location.href = "<?=site_url('reports/getSalesReport/pdf/?v=1'.$v)?>";
        return false;
    });
    $('#xls').click(function (event) {
        event.preventDefault();
        window.location.href = "<?=site_url('reports/getSalesReport/0/xls/?v=1'.$v)?>";
        return false;
    });
    $('#pdfre').click(function (event) {
        event.preventDefault();
        window.location.href = "<?=site_url('reports/getThuHoiReport/pdf/?v=1'.$v)?>";
        return false;
    });
    $('#xlsre').click(function (event) {
        event.preventDefault();
        window.location.href = "<?=site_url('reports/getThuHoiReport/0/xls/?v=1'.$v)?>";
        return false;
    });
    $('#image').click(function (event) {
        event.preventDefault();
        html2canvas($('.sales-table'), {
            onrendered: function (canvas) {
                var img = canvas.toDataURL()
                window.open(img);
            }
        });
        return false;
    });
    $('#pdf1').click(function (event) {
        event.preventDefault();
        window.location.href = "<?=site_url('reports/getPaymentsReport/pdf/?v=1'.$p)?>";
        return false;
    });
    $('#xls1').click(function (event) {
        event.preventDefault();
        window.location.href = "<?=site_url('reports/getPaymentsReport/0/xls/?v=1'.$p)?>";
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
