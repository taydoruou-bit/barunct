<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script>
    $(document).ready(function () {
        // Function để hiển thị status với màu sắc
    function row_status(data, type, full) {
    var status_map = {
        'Đang báo giá': {
            label: 'Đang báo giá',
            class: 'status-dang-bao-gia'
        },
        'Đã chốt cọc': {  // ← THAY ĐỔI Ở ĐÂY
            label: 'Đã chốt cọc',
            class: 'status-da-chot-coc'
        },
        'Đã đặt hàng': {
            label: 'Đã đặt hàng',
            class: 'status-da-dat-hang'
        },
        'Đã giao chành': {
            label: 'Đá giao chành',
            class: 'status-da-giao-chanh'
        },
        'Khách đã nhận': {
            label: 'Khách đã nhận',
            class: 'status-khach-da-nhan'
        },
        'Hoàn thành': {
            label: 'Hoàn thành',
            class: 'status-hoan-thanh'
        }
    };
    
    // Kiểm tra nếu status tồn tại trong map
    if (status_map[data]) {
        return '<span class="status-badge ' + status_map[data].class + '">' + 
               status_map[data].label + '</span>';
    }
    
    // Nếu không có trong map, hiển thị text thường
    return '<span class="status-badge">' + data + '</span>';
}function formatDateOnly(data, type, full) {
            if (data && type === 'display') {
                // Tách lấy phần ngày, bỏ phần giờ
                var dateOnly = data.split(' ')[0];
                
                // Chuyển đổi từ yyyy-mm-dd sang dd/mm/yyyy
                var parts = dateOnly.split('-');
                if (parts.length === 3) {
                    return parts[2] + '/' + parts[1] + '/' + parts[0];
                }
                return dateOnly;
            }
            return data;
        }
        oTable = $('#QUData').dataTable({
            "aaSorting": [[1, "desc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= site_url('quotes/getQuotes'. ($warehouse_id ? '/' . $warehouse_id : '')) ?>',
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
                nRow.className = "quote_link";
                return nRow;
            },
            "aoColumns": [{"bSortable": false,"mRender": checkbox}, {"mRender": formatDateOnly}, null, null, null, null, {"mRender": currencyFormat}, {"mRender": row_status}, {"bSortable": false,"mRender": attachment}, {"bSortable": false}]
        }).fnSetFilteringDelay().dtFilter([
            {column_number: 1, filter_default_label: "[<?=lang('date');?> (yyyy-mm-dd)]", filter_type: "text", data: []},
            {column_number: 2, filter_default_label: "[<?=lang('reference_no');?>]", filter_type: "text", data: []},
            {column_number: 3, filter_default_label: "[<?=lang('biller');?>]", filter_type: "text", data: []},
            {column_number: 4, filter_default_label: "[<?=lang('customer');?>]", filter_type: "text", data: []},
            {column_number: 5, filter_default_label: "[<?=lang('supplier');?>]", filter_type: "text", data: []},
            {column_number: 6, filter_default_label: "[<?=lang('total');?>]", filter_type: "text", data: []},
            {column_number: 7, filter_default_label: "[<?=lang('status');?>]", filter_type: "text", data: []},
        ], "footer");
        <?php if($this->session->userdata('remove_quls')) { ?>
        if (localStorage.getItem('quitems')) {
            localStorage.removeItem('quitems');
        }
        if (localStorage.getItem('qudiscount')) {
            localStorage.removeItem('qudiscount');
        }
        if (localStorage.getItem('qutax2')) {
            localStorage.removeItem('qutax2');
        }
        if (localStorage.getItem('qushipping')) {
            localStorage.removeItem('qushipping');
        }
        if (localStorage.getItem('quref')) {
            localStorage.removeItem('quref');
        }
        if (localStorage.getItem('quwarehouse')) {
            localStorage.removeItem('quwarehouse');
        }
        if (localStorage.getItem('qusupplier')) {
            localStorage.removeItem('qusupplier');
        }
        if (localStorage.getItem('qunote')) {
            localStorage.removeItem('qunote');
        }
        if (localStorage.getItem('qucustomer')) {
            localStorage.removeItem('qucustomer');
        }
        if (localStorage.getItem('qubiller')) {
            localStorage.removeItem('qubiller');
        }
        if (localStorage.getItem('qucurrency')) {
            localStorage.removeItem('qucurrency');
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
    echo form_open('quotes/quote_actions', 'id="action-form"');
} ?>
<div class="box no-print">
    <div class="box-header">
        <h2 class="blue"><i
                class="fa-fw fa fa-heart-o"></i><?= lang('quotes') . ' (' . ($warehouse_id ? $warehouse->name : lang('all_warehouses')) . ')'; ?>
        </h2>

        <div class="box-icon no-print">
            <ul class="btn-tasks">                
                <?php if (!empty($warehouses)) { ?>
                    <li class="dropdown">
                        <a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-building-o tip" data-placement="left" title="<?= lang("warehouses") ?>"></i></a>
                        <ul class="dropdown-menu pull-right" class="tasks-menus" role="menu" aria-labelledby="dLabel">
                            <li><a href="<?= site_url('quotes') ?>"><i class="fa fa-building-o"></i> <?= lang('all_warehouses') ?></a></li>
                            <li class="divider"></li>
                            <?php
                            foreach ($warehouses as $warehouse) {
                                echo '<li><a href="' . site_url('quotes/' . $warehouse->id) . '"><i class="fa fa-building"></i>' . $warehouse->name . '</a></li>';
                            }
                            ?>
                        </ul>
                    </li>
                <?php } ?>
            </ul>
        </div>
		<div class="main-task-lhson no-print">
			<a class="btn btn-primary btncls" href="<?= site_url('quotes/add') ?>"><i class="fa fa-plus-circle"></i> <?= lang('add_quote') ?>
			</a>
			<!-- <a class="btn btn-primary btncls" href="#" id="excel" data-action="export_excel"><i class="fa fa-file-excel-o"></i> <?= lang('export_to_excel') ?>
			</a>
			<a class="btn btn-primary btncls" href="#" id="pdf" data-action="export_pdf"><i class="fa fa-file-pdf-o"></i> <?= lang('export_to_pdf') ?>
			</a> -->
			<!-- <a class="btn btn-primary btncls" href="#" id="combine" data-action="combine">
				<i class="fa fa-file-pdf-o"></i> <?=lang('combine_to_pdf')?>
			</a> -->
			<a class="btn btn-primary btncls bpo" href="#" title="<b><?= $this->lang->line("delete_quotes") ?></b>" 
				data-content="<p><?= lang('r_u_sure') ?></p><button type='button' class='btn btn-danger' id='delete' data-action='delete'><?= lang('i_m_sure') ?></a> <button class='btn bpo-close'><?= lang('no') ?></button>" 
				data-html="true" data-placement="left"><i class="fa fa-trash-o"></i> Xóa
			</a>
		</div>	
    </div>
    <div class="box-content no-print">
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
                            <th><?= lang("reference_no"); ?></th>
                            <th><?= lang("biller"); ?></th>
                            <th><?= lang("customer"); ?></th>
                            <th><?= lang("supplier"); ?></th>
                            <th><?= lang("total"); ?></th>
                            <th><?= lang("status"); ?></th>
                            <th style="min-width:30px; width: 30px; text-align: center;"><i class="fa fa-chain"></i></th>
                            <th style="width:115px; text-align:center;"><?= lang("actions"); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="10"
                                class="dataTables_empty"><?= lang("loading_data"); ?></td>
                        </tr>
                        </tbody>
                        <tfoot class="dtFilter">
                        <tr class="active">
                            <th style="min-width:30px; width: 30px; text-align: center;">
                                <input class="checkbox checkft" type="checkbox" name="check"/>
                            </th>
                            <th></th><th></th><th></th><th></th><th></th><th></th><th></th>
                            <th style="min-width:30px; width: 30px; text-align: center;"><i class="fa fa-chain"></i></th>
                            <th style="width:115px; text-align:center;"><?= lang("actions"); ?></th>
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
<style>
/* Style cho status badges trong danh sách */
.status-badge {
    padding: 8px 12px;
    border-radius: 3px;
    font-weight: 500;
    font-size: 11px;
    display: inline-block;
    color: white;
    text-align: center;
    min-width: 140px;
    max-width: 200px;
    white-space: normal; /* Cho phép xuống dòng */
    line-height: 1.4; /* Khoảng cách giữa các dòng */
    word-wrap: break-word; /* Tự động xuống dòng */
}

.status-dang-bao-gia {
    background-color: #5bc0de;
}

.status-da-chot-coc {
    background-color: #f0ad4e;
}

.status-da-dat-hang {
    background-color: #337ab7;
}

.status-da-giao-chanh {
    background-color: #9b59b6;
}

.status-khach-da-nhan {
    background-color: #27ae60;
}

.status-hoan-thanh {
    background-color: #5cb85c;
}

/* Tăng chiều rộng cột status trong bảng */
#QUData thead th:nth-child(8),
#QUData tbody td:nth-child(8) {
    min-width: 160px !important;
    max-width: 200px !important;
}
</style>