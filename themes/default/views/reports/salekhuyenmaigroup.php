<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php

$v = "";
if ($this->input->post('sproduct')) {
    $v.= "&product=" . $this->input->post('sproduct');
}

if ($this->input->post('warehouse')) {
    $v.= "&warehouse=" . $this->input->post('warehouse');
}
if ($this->input->post('nhom')) {
    $v.= "&nhom=" . $this->input->post('nhom'); 
}
if ($this->input->post('start_date')) {
    $v.= "&start_date=" . $this->input->post('start_date');
}
if ($this->input->post('end_date')) {
    $v.= "&end_date=" . $this->input->post('end_date');
}

?> 

<script>
    $(document).ready(function () {
        oTable = $('#SLKMD').dataTable({
			"responsive": true,
            "aaSorting": [[0, "desc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= site_url('reports/getSalesReportBySanPhamKMGroup/?v=1' . $v) ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            'fnRowCallback': function (nRow, aData, iDisplayIndex) {
                nRow.id = aData[2]; 
                nRow.className = (aData[2] > 0) ? "invoice_link23" : "invoice_link23 warning";
                return nRow;
            },
            "aoColumns": [null,{"mRender": formatQuantity2}],
            "fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {
                var gtotal = 0, soluong = 0;
                for (var i = 0; i < aaData.length; i++) {
                    soluong += parseFloat(aaData[aiDisplay[i]][1]);
                }
                var nCells = nRow.getElementsByTagName('th');
                nCells[1].innerHTML = parseFloat(soluong);
				
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


<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-heart"></i><?= lang('Báo cáo GROUP sản phẩm khuyến mãi'); ?> <?php
            if ($this->input->post('start_date')) {
                echo "From " . $this->input->post('start_date') . " to " . $this->input->post('end_date');
            }
            ?>
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
         <a href="<?= site_url('reports/salesKhuyenMai') ?>" id="pdf2" class="tip" title="<?= lang('KM sản phẩm') ?>">
            <i class="icon fa fa-user-group"></i> KM sản phẩm
        </a>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <div id="form">

                    <?php echo form_open("reports/saleskhuyenmaigroup"); ?>
                    <div class="row">
                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang("product", "suggest_product"); ?>
                                <?php echo form_input('sproduct', (isset($_POST['sproduct']) ? $_POST['sproduct'] : ""), 'class="form-control" id="suggest_product"'); ?>
                                <input type="hidden" name="product" value="<?= isset($_POST['product']) ? $_POST['product'] : "" ?>" id="report_product_id"/>
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
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="warehouse"><?= lang("Nhóm"); ?></label>
                                <?php
                                $nhom[""] = lang('select').' '.lang('nhóm');
                                foreach ($nhoms as $nh) {
                                    $nhom[$nh->id] = $nh->name;
                                }
                                echo form_dropdown('nhom', $nhom, (isset($_POST['nhom']) ? $_POST['nhom'] : ""), 'class="form-control" id="nhom" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("Nhóm") . '"');
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
                <div class="clearfix"></div>

                <div class="table-responsive">
                    <table id="SLKMD"
                           class="table table-bordered table-hover table-striped table-condensed responsive reports-table">
                        <thead>
                        <tr>
                            <th ><?= lang("Sản phẩm"); ?></th>
                            <th class="col-xs-1"><?= lang("Số lượng"); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="2" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
                        </tr>
                        </tbody>
                        <tfoot class="dtFilter">
                        <tr class="active">
                            <th><?= lang("Sản phẩm"); ?></th>
                            <th><?= lang("Số lượng"); ?></th>
                        </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript" src="<?= $assets ?>js/html2canvas.min.js"></script>
<style type="text/css">
    a#pdf2 {
      float: right;
        margin: 10px;
        font-size: 13px;
    }
</style>