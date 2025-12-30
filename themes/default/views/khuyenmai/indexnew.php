<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script>
    $(document).ready(function () {
        oTable = $('#QUData').dataTable({
            "aaSorting": [[1, "desc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= site_url('khuyenmai/getKhuyenmainews'. ($warehouse_id ? '/' . $warehouse_id : '')) ?>',
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
                nRow.className = "khuyenmai_linknew";
                return nRow;
            },
            "aoColumns": [{"bSortable": false,"mRender": checkbox}, {"mRender": fld},null,null,null,null, {"bSortable": false,"mRender": null},{"bSortable": false,"mRender": attachment}, {"bSortable": false}]
        }).fnSetFilteringDelay().dtFilter([
            {column_number: 1, filter_default_label: "[<?=lang('date');?> (yyyy-mm-dd)]", filter_type: "text", data: []},
            {column_number: 2, filter_default_label: "[Event name]", filter_type: "text", data: []},
            {column_number: 3, filter_default_label: "Start (yyyy-mm-dd)", filter_type: "text", data: []},
            {column_number: 4, filter_default_label: "End (yyyy-mm-dd)", filter_type: "text", data: []},
			{column_number: 5, filter_default_label: "Product name", filter_type: "text", data: []},
			{column_number: 6, filter_default_label: "Maximun quantity", filter_type: "text", data: []},
        ], "footer");
        <?php if($this->session->userdata('remove_quls')) { ?>
        if (localStorage.getItem('quitems')) {
            localStorage.removeItem('quitems');
        }
        if (localStorage.getItem('quwarehouse')) {
            localStorage.removeItem('quwarehouse');
        }
        if (localStorage.getItem('qunote')) {
            localStorage.removeItem('qunote');
        }
        if (localStorage.getItem('qudate')) {
            localStorage.removeItem('qudate');
        }
        if (localStorage.getItem('qustatus')) {
            localStorage.removeItem('qustatus');
        }
        <?php $this->sma->unset_data('remove_quls'); } ?>
    });

</script>

<?php if ($Owner || $GP['bulk_actions']) {
    echo form_open('khuyenmai/khuyenmai_actions', 'id="action-form"');
} ?>
<div class="box">
    
    <div class="box-header">
        <h2 class="blue"><i
                class="fa-fw fa fa-star"></i><?=lang('khuyenmai') . ' (' . ($warehouse_id ? $warehouse->name : lang('all_warehouses')) . ')';?>
        </h2>
        
        <div class="box-icon">
            <ul class="btn-tasks">                
                <?php if (!empty($warehouses)) {
                    ?>
                    <li class="dropdown">
                        <a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-building-o tip" data-placement="left" title="<?=lang("warehouses")?>"></i></a>
                        <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                            <li><a href="<?=site_url('khuyenmai')?>"><i class="fa fa-building-o"></i> <?=lang('all_warehouses')?></a></li>
                            <li class="divider"></li>
                            <?php
                                foreach ($warehouses as $warehouse) {
                                        echo '<li ' . ($warehouse_id && $warehouse_id == $warehouse->id ? 'class="active"' : '') . '><a href="' . site_url('khuyenmai/' . $warehouse->id) . '"><i class="fa fa-building"></i>' . $warehouse->name . '</a></li>';
                                    }
                                ?>
                        </ul>
                    </li>
                <?php }
                ?>
            </ul>
        </div>
        <div class="main-task-lhson">
            
            <a class="btn btn-primary btncls" href="<?= site_url('khuyenmai/addnew') ?>">
                <i class="fa fa-plus-circle"></i> Thêm khuyến mãi
            </a>
            
            <a class="btn btn-primary btncls bpo" href="#" title="<b><?=lang("Delete promotion")?></b>"
                data-content="<p><?=lang('r_u_sure')?></p><button type='button' class='btn btn-danger' id='delete' data-action='delete'><?=lang('i_m_sure')?></a> <button class='btn bpo-close'><?=lang('no')?></button>"
                data-html="true" data-placement="left">
                <i class="fa fa-trash-o"></i> Xóa
            </a>

        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <div class="table-responsive">
                    <table id="QUData" class="table table-bordered table-hover table-striped">
                        <thead>
                        <tr class="active">
                            <th style="min-width:30px; width: 30px; text-align: center;">
                                <input class="checkbox checkft" type="checkbox" name="check"/>
                            </th>
                            <th><?= lang("date"); ?></th>
                            <th><?= lang("khuyenmai_eventname"); ?></th>
                            <th><?= lang("khuyenmai_start"); ?></th>
                            <th><?= lang("khuyenmai_end"); ?></th>
							<th><?= lang("Tên sản phẩm"); ?></th>
							<th><?= lang("Số lượng"); ?></th>
                            <th style="min-width:30px; width: 30px; text-align: center;"><i class="fa fa-chain"></i></th>
                            <th style="width:20px; text-align:center;"><?= lang("actions"); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="8"
                                class="dataTables_empty"><?= lang("loading_data"); ?></td>
                        </tr>
                        </tbody>
                        <tfoot class="dtFilter">
                        <tr class="active">
                            <th style="min-width:30px; width: 30px; text-align: center;">
                                <input class="checkbox checkft" type="checkbox" name="check"/>
                            </th>
                            <th></th><th></th><th></th><th></th><th></th><th></th>
                            <th style="min-width:30px; width: 30px; text-align: center;"><i class="fa fa-chain"></i></th>
                            <th style="width:20px; text-align:center;"><?= lang("actions"); ?></th>
                        </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php if ($Owner || $GP['bulk_actions']) { ?>
    <div style="display: none;">
        <input type="hidden" name="form_action" value="" id="form_action"/>
        <?= form_submit('performAction', 'performAction', 'id="action-form-submit"') ?>
    </div>
    <?= form_close() ?>
<?php } ?>