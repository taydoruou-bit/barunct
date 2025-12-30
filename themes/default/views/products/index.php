<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<style type="text/css" media="screen">
    #PRData td:nth-child(7) {
        text-align: right;
    }
    <?php if($Owner || $Admin || $this->session->userdata('show_cost')) { ?>
    #PRData td:nth-child(9) {
        text-align: right;
    }
    <?php } if($Owner || $Admin || $this->session->userdata('show_price')) { ?>
    #PRData td:nth-child(8) {
        text-align: right;
    }
    <?php } ?>
</style>
<script>
    var oTable;
    $(document).ready(function () {
        oTable = $('#PRData').dataTable({
            "aaSorting": [[0, "DESC"], [3, "asc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= site_url('products/getProducts'.($warehouse_id ? '/'.$warehouse_id : '').($supplier ? '?supplier='.$supplier->id : '')) ?>',
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
                nRow.className = "product_link";
                //if(aData[7] > aData[9]){ nRow.className = "product_link warning"; } else { nRow.className = "product_link"; }
				
                return nRow;
            },
            "aoColumns": [
                {"bSortable": true, "mRender": checkbox}, {"bSortable": false,"mRender": img_hl}, null, null,<?php echo $thuonghieuok==true?'null':'{"bVisible": false}';?>,<?php echo $xuatxuok==true?'null':'{"bVisible": false}';?>, null, <?php if($Owner || $Admin) { echo '{"mRender": null}, {"mRender": null},'; } else { if($this->session->userdata('show_cost')) { echo '{"mRender": null},';  } if($this->session->userdata('show_price')) { echo '{"mRender": null},';  } } ?><?php echo $use_gia_si==true?'{"mRender": formatQuantity}':'{"bVisible": false}';?>, {"mRender": baseToUnitQtyLhson}, {"bVisible": false}, <?php if(!$Settings->racks) { echo '{"bVisible": false},'; } else { echo '{"bSortable": true},'; } ?> <?php echo $baohanh==true?'null':'{"bVisible": false}';?>,<?php echo $canhbao==true?'{"mRender": formatQuantity}':'{"bVisible": false}';?>,<?php echo $khuyenmai==true?'{"mRender": formatQuantity}':'{"bVisible": false}';?>,{"bSortable": false}
            ]
        }).fnSetFilteringDelay().dtFilter([
            {column_number: 2, filter_default_label: "[<?=lang('code');?>]", filter_type: "text", data: []},
            {column_number: 3, filter_default_label: "[<?=lang('name');?>]", filter_type: "text", data: []},
            {column_number: 4, filter_default_label: "[<?=lang('brand');?>]", filter_type: "text", data: []},
			{column_number: 5, filter_default_label: "[<?=lang('xuatxu');?>]", filter_type: "text", data: []},
            {column_number: 6, filter_default_label: "[<?=lang('category');?>]", filter_type: "text", data: []},
            <?php $col = 6;
            if($Owner || $Admin) {
                echo '{column_number : 7, filter_default_label: "['.lang('cost').']", filter_type: "text", data: [] },';
                echo '{column_number : 8, filter_default_label: "['.lang('price').']", filter_type: "text", data: [] },';
                $col += 3;
            } else {
                if($this->session->userdata('show_cost')) { $col++; echo '{column_number : '.$col.', filter_default_label: "['.lang('cost').']", filter_type: "text", data: [] },'; }
                if($this->session->userdata('show_price')) { $col++; echo '{column_number : '.$col.', filter_default_label: "['.lang('price').']", filter_type: "text, data: []" },'; }
            }
            ?>
            {column_number: <?php $col++; echo $col; ?>, filter_default_label: "[<?=lang('Kho');?>]", filter_type: "text", data: []},
            {column_number: <?php $col++; echo $col; ?>, filter_default_label: "[<?=lang('unit');?>]", filter_type: "text", data: []},
            <?php $col++; if($warehouse_id && $Settings->racks) { echo '{column_number : '. $col.', filter_default_label: "['.lang('rack').']", filter_type: "text", data: [] },'; } ?>
            {column_number: <?php $col++; echo $col; ?>, filter_default_label: "[<?=lang('alert_quantity');?>]", filter_type: "text", data: []},
        ], "footer");

    });
</script>
<?php if ($Owner || $GP['bulk_actions']) {
    echo form_open('products/product_actions'.($warehouse_id ? '/'.$warehouse_id : ''), 'id="action-form"');
} ?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i
                class="fa-fw fa fa-barcode"></i><?= lang('products') . ' (' . ($warehouse_id ? $warehouse->name : lang('all_warehouses')) . ')'.($supplier ? ' ('.lang('supplier').': '.($supplier->company && $supplier->company != '-' ? $supplier->company : $supplier->name).')' : ''); ?>
        </h2>		
        <div class="box-icon">
            <ul class="btn-tasks">                
                <?php if (!empty($warehouses)) { ?>
                    <li class="dropdown">
                        <a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-building-o tip" data-placement="left" title="<?= lang("warehouses") ?>"></i></a>
                        <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                            <li><a href="<?= site_url('products') ?>"><i class="fa fa-building-o"></i> <?= lang('all_warehouses') ?></a></li>
                            <li class="divider"></li>
                            <?php
                            foreach ($warehouses as $warehouse) {
                                echo '<li><a href="' . site_url('products/' . $warehouse->id) . '"><i class="fa fa-building"></i>' . $warehouse->name . '</a></li>';
                            }
                            ?>
                        </ul>
                    </li>
                <?php } ?>
            </ul>
        </div>
		<div class="main-task-lhson">
			<a class="btn btn-primary btncls" href="<?= site_url('products/add') ?>">
				<i class="fa fa-plus-circle"></i> Thêm sản phẩm
			</a>
		<?php if(!$warehouse_id) { ?>
			<a class="btn btn-primary btncls" href="<?= site_url('products/update_price') ?>" data-toggle="modal" data-target="#myModal">
				<i class="fa fa-file-excel-o"></i> Sửa giá
			</a>
		<?php } ?>
        <?php if ($Owner || $GP['bulk_actions']) {?>
			<a class="btn btn-primary btncls" href="#" id="labelProducts" data-action="labels">
				<i class="fa fa-print"></i> In mã
			</a>
			<a class="btn btn-primary btncls" href="#" id="sync_quantity" data-action="sync_quantity">
				<i class="fa fa-arrows-v"></i> Đồng bộ kho
			</a>
            <a class="btn btn-primary btncls" href="/auth/SysnApiWoooProductsV3">
                <i class="fa fa-arrows-v"></i> Đồng bộ sản phẩm WEB API
            </a>
			<a class="btn btn-primary btncls" href="#" id="excel" data-action="export_excel">
				<i class="fa fa-file-excel-o"></i> Excel
			</a>
			<a class="btn btn-primary btncls" href="#" id="pdf" data-action="export_pdf">
				<i class="fa fa-file-pdf-o"></i> Pdf
			</a>
			<a class="btn btn-primary btncls bpo" href="#" title="<b><?= $this->lang->line("delete_products") ?></b>"
				data-content="<p><?= lang('r_u_sure') ?></p><button type='button' class='btn btn-danger' id='delete' data-action='delete'><?= lang('i_m_sure') ?></a> <button class='btn bpo-close'><?= lang('no') ?></button>"
				data-html="true" data-placement="left">
			<i class="fa fa-trash-o"></i> Xóa
			 </a>
            <?php } ?>
		</div>
    </div>
    <div class="box-content">
        <div class="row no-padding-lhson">
            <div class="col-lg-12">
                <div class="table-responsive">
                    <table id="PRData" class="table table-bordered table-condensed table-hover table-striped">
                        <thead>
                        <tr class="primary">
                            <th style="min-width:30px; width: 30px; text-align: center;">
                                <input class="checkbox checkth" type="checkbox" name="check"/>
                            </th>
                            <th style="min-width:40px; width: 40px; text-align: center;"><?php echo $this->lang->line("image"); ?></th>
                            <th><?= lang("code") ?></th>
                            <th><?= lang("name") ?></th>
                            <th><?= lang("brand") ?></th>
							<th><?= lang("xuatxu") ?></th>
                            <th><?= lang("category") ?></th>
                            <?php
                            if ($Owner || $Admin) {
                                echo '<th>' . lang("cost") . '</th>';
                                echo '<th>Giá bán</th>';
                            } else {
                                if ($this->session->userdata('show_cost')) {
                                    echo '<th>' . lang("cost") . '</th>';
                                }
                                if ($this->session->userdata('show_price')) {
                                    echo '<th>Giá bán</th>';
                                }
                            }
                            ?>
							
							<th><?= lang("Giá sỉ") ?></th>
                            <th>Tồn kho</th>
                            <th><?= lang("unit") ?></th>
                            <th><?= lang("rack") ?></th>
                            <th>Bảo hành</th>
							<th>Cảnh báo</th>
							<th>Giá KM</th>
                            <th style="min-width:65px; text-align:center;"><?= lang("actions") ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="12" class="dataTables_empty"><?= lang('loading_data_from_server'); ?></td>
                        </tr>
                        </tbody>

                        <tfoot class="dtFilter">
                        <tr class="active">
                            <th style="min-width:30px; width: 30px; text-align: center;">
                                <input class="checkbox checkft" type="checkbox" name="check"/>
                            </th>
                            <th style="min-width:40px; width: 40px; text-align: center;"><?php echo $this->lang->line("image"); ?></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
							<th></th>
							<th></th>
							<th></th>
							<th></th>
                            <?php
                            if ($Owner || $Admin) {
                                echo '<th></th>';
                                echo '<th></th>';
                            } else {
                                if ($this->session->userdata('show_cost')) {
                                    echo '<th></th>';
                                }
                                if ($this->session->userdata('show_price')) {
                                    echo '<th></th>';
                                }
                            }
                            ?>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th style="width:65px; text-align:center;"><?= lang("actions") ?></th>
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
