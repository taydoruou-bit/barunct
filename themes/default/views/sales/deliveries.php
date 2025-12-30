<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script>
    $(document).ready(function () {
        var dss = <?= json_encode(array('packing' => lang('packing'), 'delivering' => lang('delivering'), 'delivered' => lang('delivered'))); ?>;
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
        oTable = $('#DODataGH').dataTable({
            "aaSorting": [[1, "desc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= site_url('sales/getDeliveries'.($warehouse_id ? '/' . $warehouse_id : '')) ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            'fnRowCallback': function (nRow, aData, iDisplayIndex) {
                var oSettings = oTable.fnSettings();
                nRow.id = aData[0];
                if (parseInt(aData[12])>0) {
                    $(nRow).attr('purchaseid', aData[12]);
                    nRow.className = "purchase_linkdel";
                }else{
                    nRow.className = "delivery_link";
                } 
                
                return nRow;
            },
            "aoColumns": [{"bSortable": false,"mRender": checkbox}, {"mRender": fld}, {"mRender": fld}, null,null, null, {"mRender": currencyFormat}, null,null,null, {"mRender": ds}, {"bSortable": false,"mRender": attachment},{"bVisible": false}, {"bSortable": false}]
        }).fnSetFilteringDelay().dtFilter([
            {column_number: 1, filter_default_label: "[<?=lang('date');?> (yyyy-mm-dd)]", filter_type: "text", data: []},
            {column_number: 2, filter_default_label: "[<?=lang('Ngày nhận');?> (yyyy-mm-dd)]", filter_type: "text", data: []},
            {column_number: 3, filter_default_label: "[<?=lang('do_reference_no');?>]", filter_type: "text", data: []},
            {column_number: 4, filter_default_label: "[<?=lang('sale_reference_no');?>]", filter_type: "text", data: []},
            {column_number: 5, filter_default_label: "[<?=lang('Đối tác');?>]", filter_type: "text", data: []},
            {column_number: 6, filter_default_label: "[<?=lang('Phí');?>]", filter_type: "text", data: []},
            {column_number: 7, filter_default_label: "[<?=lang('customer');?>]", filter_type: "text", data: []},
            {column_number: 8, filter_default_label: "[<?=lang('phone');?>]", filter_type: "text", data: []},
            {column_number: 9, filter_default_label: "[<?=lang('address');?>]", filter_type: "text", data: []},
            {column_number: 10, filter_default_label: "[<?=lang('status');?>]", filter_type: "text", data: []},
        ], "footer");
    }); 
</script>
<?php if ($Owner) { ?><?= form_open('sales/delivery_actions', 'id="action-form"') ?><?php } ?>
<div class="box no-print">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-truck"></i><?= lang('deliveries'); ?></h2>
        <div class="box-icon">
            <ul class="btn-tasks">
                
                <?php if (!empty($warehouses)) {
                    ?>
                    <li class="dropdown">
                        <a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-building-o tip" data-placement="left" title="<?=lang("warehouses")?>"></i></a>
                        <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                            <li><a href="<?=site_url('sales/deliveries')?>"><i class="fa fa-building-o"></i> <?=lang('all_warehouses')?></a></li>
                            <li class="divider"></li>
                            <?php
                                foreach ($warehouses as $warehouse) {
                                        echo '<li><a href="' . site_url('sales/deliveries/' . $warehouse->id) . '"><i class="fa fa-building"></i>' . $warehouse->name . '</a></li>';
                                    }
                                ?>
                        </ul>
                    </li>
                <?php }
                ?>
            </ul>
        </div>
        <div class="main-task-lhson">
             <a class="btn btn-primary btncls" href="#" id="excel" data-action="export_excel"><i class="fa fa-file-excel-o"></i> <?= lang('export_to_excel') ?></a>
               <a class="btn btn-primary btncls" href="#" id="pdf" data-action="export_pdf"><i class="fa fa-file-pdf-o"></i> <?= lang('export_to_pdf') ?></a>
                <a class="btn btn-primary btncls bpo" href="#" title="<b><?= $this->lang->line("delete_deliveries") ?></b>" 
                    data-content="<p><?= lang('r_u_sure') ?></p><button type='button' class='btn btn-danger' id='delete' data-action='delete'><?= lang('i_m_sure') ?></a> <button class='btn bpo-close'><?= lang('no') ?></button>" 
                    data-html="true" data-placement="left">
                    <i class="fa fa-trash-o"></i> <?= lang('delete_deliveries') ?>
                </a>
        </div>
        
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <table id="DODataGH" class="table table-bordered table-hover table-striped table-condensed">
                    <thead>
                    <tr>
                        <th style="min-width:30px; width: 30px; text-align: center;">
                            <input class="checkbox checkft" type="checkbox" name="check"/>
                        </th>
                        <th style="min-width:80px; width:80px;"><?= lang("date"); ?></th>
                        <th style="min-width:80px; width:80px;"><?= lang("Ngày nhận"); ?></th>
                        <th style="min-width:100px; width:100px;"><?= lang("do_reference_no"); ?></th>
                        <th style="min-width:100px; width:100px;"><?= lang("sale_reference_no"); ?></th>
                        <th style="min-width:100px; width:100px;"><?= lang("Đối tác"); ?></th>
                        <th style="min-width:80px; width: 80px;"><?= lang("Phí GH"); ?></th>
                        <th style="min-width:100px; width:100px;"><?= lang("customer"); ?></th>
                        <th style="min-width:100px; width:100px;"><?= lang("phone"); ?></th>
                        <th style="width:auto;"><?= lang("address"); ?></th>
                        <th style="min-width:80px; width:80px;"><?= lang("status"); ?></th>
                        <th style="min-width:30px; width:30px; text-align: center;"><i class="fa fa-chain"></i></th>
                        <th style="min-width:100px; width:100px;"><?= lang("status"); ?></th>
                        <th style="width:100px; text-align:center;"><?= lang("actions"); ?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td colspan="14" class="dataTables_empty"><?= lang("loading_data"); ?></td>
                    </tr>
                    </tbody>
                    <tfoot class="dtFilter">
                    <tr class="active">
                       <th style="min-width:30px; width: 30px; text-align: center;">
                            <input class="checkbox checkft" type="checkbox" name="check"/>
                        </th>
                        <th style="min-width:80px; width:80px;"><?= lang("date"); ?></th>
                        <th style="min-width:80px; width:80px;"><?= lang("Ngày nhận"); ?></th>
                        <th style="min-width:100px; width:100px;"><?= lang("do_reference_no"); ?></th>
                        <th style="min-width:100px; width:100px;"><?= lang("sale_reference_no"); ?></th>
                        <th style="min-width:100px; width:100px;"><?= lang("Đối tác"); ?></th>
                        <th style="min-width:80px; width: 80px;"><?= lang("Phí GH"); ?></th>
                        <th style="min-width:100px; width:100px;"><?= lang("customer"); ?></th>
                        <th style="min-width:100px; width:100px;"><?= lang("phone"); ?></th>
                        <th style="width:auto;"><?= lang("address"); ?></th>
                        <th style="min-width:80px; width:80px;"><?= lang("status"); ?></th>
                        <th style="min-width:30px; width:30px; text-align: center;"><i class="fa fa-chain"></i></th>
                        <th style="min-width:100px; width:100px;"><?= lang("status"); ?></th>
                        <th style="width:100px; text-align:center;"><?= lang("actions"); ?></th>
                    </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
<?php if ($Owner) { ?>
    <div style="display: none;">
        <input type="hidden" name="form_action" value="" id="form_action"/>
        <?= form_submit('perform_action', 'perform_action', 'id="action-form-submit"') ?>
    </div>
    <?= form_close() ?>
    <script type="text/javascript" charset="utf-8">
        $(document).ready(function() {
            $(document).on('click', '#delete', function(e) {
                e.preventDefault();
                $('#form_action').val($(this).attr('data-action'));
                //$('#action-form').submit();
                $('#action-form-submit').click();
            });
        });
    </script>
<?php } ?>
<style type="text/css" media="print">
 .no-print
 {
    display:none;
    visibility: hidden;
 }
</style>