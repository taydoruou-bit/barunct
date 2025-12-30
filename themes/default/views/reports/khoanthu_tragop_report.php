<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php

$v = "";
if ($this->input->post('reference_no')) {
    $v .= "&reference_no=" . $this->input->post('reference_no');
}
if ($this->input->post('category')) {
    $v .= "&category=" . $this->input->post('category');
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
		function customlhson(x) {
			
            if (x != null)
            {				
				return str='<div class="text-center"><div class="btn-group text-left"><button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">Tác vụ <span class="caret"></span></button><ul class="dropdown-menu pull-right" role="menu">     <li><a href="<?= site_url('purchases/printphieuthu'); ?>/'+x+'" data-toggle="modal" data-target="#myModal2"><i class="fa fa-print"></i> In phiếu thu</a></li><li><li><a href="<?= site_url('sales/printsalelhson'); ?>/'+x+'" data-toggle="modal" data-target="#myModal2"><i class="fa fa-print"></i> In hóa đơn bán hàng</a></li><li><a href="<?= site_url('sales/modal_view/'); ?>/'+x+'" data-toggle="modal" data-target="#myModal2"><i class="fa fa-file-text-o"></i> Chi tiết hóa đơn</a></li><li><a href="<?= site_url('sales/modal_thuno'); ?>/'+x+'" data-toggle="modal" data-target="#myModal2"><i class="fa fa-usd"></i> Thu nợ</a></li></ul></div></div>';
				
            }
            return x;
        }
		
        oTable = $('#PhieuthuRData').dataTable({
            "aaSorting": [[0, "desc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'bFilter': false,
            'sAjaxSource': '<?= site_url('reports/getBaocaothuTraGopReport/?v=1' . $v) ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            "aoColumns": [{"mRender": fld}, null, {"mRender": ref},null,null, {"mRender": paid_by}, {"mRender": currencyFormat,"bSortable": true},{"mRender": currencyFormat,"bSortable": true},{"mRender": currencyFormat,"bSortable": true},null,{"mRender": pay_status},{"mRender": null,"bSortable": true},{
                "bSortable": false,
                "mRender": customlhson
            }],
            'fnRowCallback': function (nRow, aData, iDisplayIndex) {
                nRow.id = aData[12];
                nRow.className = "phieuthu_link";               
                return nRow;
            },
            "fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {
                var total = 0;
                var total2 = 0;
                var total3 = 0;
                for (var i = 0; i < aaData.length; i++) {                    
                        total += parseFloat(aaData[aiDisplay[i]][6]);
                        total2 += parseFloat(aaData[aiDisplay[i]][7]);
                        total3 += parseFloat(aaData[aiDisplay[i]][8]);
                }
                var nCells = nRow.getElementsByTagName('th');
                nCells[6].innerHTML = currencyFormat(parseFloat(total));
                nCells[7].innerHTML = currencyFormat(parseFloat(total2));
                nCells[8].innerHTML = currencyFormat(parseFloat(total3));
            }
        }).fnSetFilteringDelay().dtFilter([], "footer");

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

<div class="box no-print">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-money"></i>Báo cáo các khoản trả góp
        </h2>
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
        <!--<div class="box-icon">
            <ul class="btn-tasks">
				
                <li class="dropdown">
                    <a href="#" id="xls" class="tip" title="<?= lang('download_xls') ?>">
                        <i class="icon fa fa-file-excel-o"></i>
                    </a>
                </li>
				
            </ul>
        </div> -->
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
				<div id="form">

                    <?php echo form_open("reports/baocaothutragop"); ?>
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
                                <label class="control-label" for="category"><?= lang("Đơn vị trả góp"); ?></label>
                                <?php
                                $ct[""] = lang('select').' '.lang('category');
                                foreach ($categories as $category) {
                                    $ct[$category->code] = $category->name;
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
                                <?php echo form_input('start_date', (isset($_POST['start_date']) ? $_POST['start_date'] : ""), 'class="form-control datetime" autocomplete="off" id="start_date"'); ?>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang("end_date", "end_date"); ?>
                                <?php echo form_input('end_date', (isset($_POST['end_date']) ? $_POST['end_date'] : ""), 'class="form-control datetime" autocomplete="off" id="end_date"'); ?>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div
                            class="controls"> <?php echo form_submit('submit_report', $this->lang->line("submit"), 'class="btn btn-primary"'); ?> </div>
                    </div>
                    <?php echo form_close(); ?>

                </div>
                <div class="clearfix"></div>
                <div class="table-responsive">
                    <table id="PhieuthuRData"
                           class="table table-bordered table-hover table-striped table-condensed reports-table">

                        <thead>
                        <tr>
                            <th class="col-xs-1"><?= lang("date"); ?></th>
                            <th><?= lang("ref"); ?></th>
                            <th>Họ tên</th>
                            <th>Điện thoại</th>
							<th>Nhân viên</th>
                            <th class="col-xs-1"><?= lang("paid_by"); ?></th>
                            <th class="col-xs-1"><?= lang("amount"); ?></th>
                            <th class="col-xs-1"><?= lang("Đã thu"); ?></th>
                            <th class="col-xs-1"><?= lang("Dư nợ"); ?></th>  
                            <th class="col-xs-1"><?= lang("Thu bởi"); ?></th>                         
							<th class="col-xs-1">Thanh toán</th>
							 <th class="col-xs-2"><?= lang("note"); ?></th>
							<th style="width:100px;"><?= lang("actions"); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="13" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
                        </tr>
                        </tbody>
                        <tfoot class="dtFilter">
                        <tr class="active">
                            <th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th>
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
            window.location.href = "<?=site_url('reports/getBaocaothuReport/pdf/?v=1'.$v)?>";
            return false;
        });
        $('#xls').click(function (event) {
            event.preventDefault();
            window.location.href = "<?=site_url('reports/getBaocaothuReport/0/xls/?v=1'.$v)?>";
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