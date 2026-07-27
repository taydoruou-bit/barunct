<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<script type="text/javascript">
    var count = 1,
        an = 1,
        DT = <?= $Settings->default_tax_rate ?>,
        allow_discount = <?= ($Owner || $Admin || $this->session->userdata('allow_discount')) ? 1 : 0; ?>,
        product_tax = 0,
        invoice_tax = 0,
        total_discount = 0,
        total = 0,
        shipping = 0,
        tax_rates = <?php echo json_encode($tax_rates); ?>;
    var audio_success = new Audio('<?= $assets ?>sounds/sound2.mp3');
    var audio_error = new Audio('<?= $assets ?>sounds/sound3.mp3');

    // ===== KHỞI TẠO CUSTOM COLUMNS =====
    window.customColumns = window.customColumns || [];
    var customColumns = [];
    // ===== KẾT THÚC KHỞI TẠO =====

    $(document).ready(function() {
        // ===== LOAD CUSTOM COLUMNS TỪ DATABASE =====
        $.ajax({
            type: 'GET',
            url: '<?= site_url("quotes/get_custom_columns") ?>',
            dataType: 'json',
            async: false, // Đồng bộ để chắc load xong trước khi xử lý
            success: function(existingColumns) {
                if (existingColumns && existingColumns.length > 0) {
                    window.customColumns = existingColumns;
                    customColumns = existingColumns;

                    // Add headers to table
                    var $headerRow = $('#quTable thead tr');
                    var $quantityHeader = $headerRow.find('th').eq(1); // ← Cột thứ 3 là "Quantity"

                    customColumns.forEach(function(columnName) {
                        $quantityHeader.before('<th class="custom-column-header">' + columnName + '</th>');
                    });
                }
            }
        });
        // ===== KẾT THÚC LOAD CUSTOM COLUMNS =====

        <?php if ($inv) { ?>
            localStorage.setItem('qudate', '<?= date($dateFormats['php_ldate'], strtotime($inv->date)) ?>');
            localStorage.setItem('qucustomer', '<?= $inv->customer_id ?>');
            localStorage.setItem('qubiller', '<?= $inv->biller_id ?>');
            localStorage.setItem('qusupplier', '<?= $inv->supplier_id ?>');
            localStorage.setItem('quref', '<?= $inv->reference_no ?>');
            localStorage.setItem('quwarehouse', '<?= $inv->warehouse_id ?>');
            localStorage.setItem('qustatus', '<?= $inv->status ?>');
            localStorage.setItem('qunote', '<?= str_replace(array("\r", "\n"), "", $this->sma->decode_html($inv->note)); ?>');
            localStorage.setItem('qutax2', '<?= $inv->order_tax_id ?>');
            localStorage.setItem('qushipping', '<?= $inv->shipping ?>');
            localStorage.setItem('deposit_amount', '<?= $inv->deposit_amount ?>');

            localStorage.setItem('quitems', JSON.stringify(<?= $inv_items; ?>));
        <?php } ?>




        // ===== LOAD ITEMS TỪ inv_items =====
        if ('<?= isset($inv_items) ? "yes" : "no" ?>' === 'yes') {
            var inv_data = <?= $inv_items; ?>;

            // Chuyển từ object sang mảng với key là ID
            quitems = {};
            $.each(inv_data, function(key, item) {
                if (item && item.row) {
                    // Dùng item.id làm key
                    quitems[item.id] = item;

                    // Khởi tạo custom_fields nếu chưa có
                    if (!quitems[item.id].custom_fields) {
                        quitems[item.id].custom_fields = {};
                    }

                    // Khởi tạo notes nếu chưa có
                    if (!quitems[item.id].notes) {
                        quitems[item.id].notes = '';
                    }
                }
            });

            // Lưu vào localStorage
            localStorage.setItem('quitems', JSON.stringify(quitems));

            // Gọi loadItems để hiển thị
            loadItems();
        }
        // ===== KẾT THÚC LOAD ITEMS =====
        $(document).on('change', '#qubiller', function(e) {
            localStorage.setItem('qubiller', $(this).val());
        });
        if (qubiller = localStorage.getItem('qubiller')) {
            $('#qubiller').val(qubiller);
        }
        ItemnTotals();
        $("#add_item").autocomplete({
            source: function(request, response) {
                if (!$('#qucustomer').val()) {
                    $('#add_item').val('').removeClass('ui-autocomplete-loading');
                    bootbox.alert('<?= lang('select_above'); ?>');
                    //response('');
                    $('#add_item').focus();
                    return false;
                }
                $.ajax({
                    type: 'get',
                    url: '<?= site_url('quotes/suggestions'); ?>',
                    dataType: "json",
                    data: {
                        term: request.term,
                        warehouse_id: $("#quwarehouse").val(),
                        customer_id: $("#qucustomer").val()
                    },
                    success: function(data) {
                        $(this).removeClass('ui-autocomplete-loading');
                        response(data);
                    }
                });
            },
            minLength: 1,
            autoFocus: false,
            delay: 250,
            response: function(event, ui) {
    // Không làm gì cả, chỉ remove loading indicator
    $(this).removeClass('ui-autocomplete-loading');
    
    // Nếu có kết quả duy nhất và không phải "no match", tự động chọn
    if (ui.content.length == 1 && ui.content[0].id != 0) {
        ui.item = ui.content[0];
        $(this).data('ui-autocomplete')._trigger('select', 'autocompleteselect', ui);
        $(this).autocomplete('close');
    }
},
            select: function(event, ui) {
                event.preventDefault();
                if (ui.item.id !== 0) {
                    var row = add_invoice_item(ui.item);
                    if (row)
                        $(this).val('');
                } else {
                    bootbox.alert('<?= lang('no_match_found') ?>');
                }
            }
        });
        // ===== XỬ LÝ CUSTOM FIELDS KHI THAY ĐỔI =====
        $(document).on('input', '.custom-field-input', function() {
            var $row = $(this).closest('tr');
            var item_id = $row.data('item-id');
            var field = $(this).attr('name');
            var value = $(this).val();

            var cleanField = field.replace('[]', '');

            if (!quitems[item_id]) {
                console.error('Item not found:', item_id);
                return;
            }

            if (!quitems[item_id].custom_fields) {
                quitems[item_id].custom_fields = {};
            }

            quitems[item_id].custom_fields[cleanField] = value;
            localStorage.setItem('quitems', JSON.stringify(quitems));
        });
        // ===== KẾT THÚC XỬ LÝ CUSTOM FIELDS =====
        // ===== KẾT THÚC XỬ LÝ CUSTOM FIELDS =====

// ===== XỬ LÝ MODAL CUSTOM OPTIONS =====
$('#addCustomOptions').click(function(e) {
    e.preventDefault();
    
    // Populate modal với existing columns
    $('#customFieldsContainer').empty();
    if (customColumns.length > 0) {
        customColumns.forEach(function(columnName) {
            var fieldHtml = '<div class="form-group custom-field-row">' +
                '<label class="col-sm-4 control-label">Tên trường</label>' +
                '<div class="col-sm-7">' +
                '<input type="text" class="form-control custom-field-name" value="' + columnName + '">' +
                '</div>' +
                '<div class="col-sm-1">' +
                '<button type="button" class="btn btn-danger btn-sm remove-field"><i class="fa fa-trash"></i></button>' +
                '</div>' +
                '</div>';
            $('#customFieldsContainer').append(fieldHtml);
        });
    } else {
        var fieldHtml = '<div class="form-group custom-field-row">' +
            '<label class="col-sm-4 control-label">Tên trường</label>' +
            '<div class="col-sm-7">' +
            '<input type="text" class="form-control custom-field-name" placeholder="Ví dụ: Màu sắc, Kích thước...">' +
            '</div>' +
            '<div class="col-sm-1">' +
            '<button type="button" class="btn btn-danger btn-sm remove-field"><i class="fa fa-trash"></i></button>' +
            '</div>' +
            '</div>';
        $('#customFieldsContainer').append(fieldHtml);
    }
    
    $('#customOptionsModal').modal('show');
});

$('#addMoreField').click(function(e) {
    e.preventDefault();
    var fieldHtml = '<div class="form-group custom-field-row">' +
        '<label class="col-sm-4 control-label">Tên trường</label>' +
        '<div class="col-sm-7">' +
        '<input type="text" class="form-control custom-field-name" placeholder="Ví dụ: Màu sắc, Kích thước...">' +
        '</div>' +
        '<div class="col-sm-1">' +
        '<button type="button" class="btn btn-danger btn-sm remove-field"><i class="fa fa-trash"></i></button>' +
        '</div>' +
        '</div>';
    $('#customFieldsContainer').append(fieldHtml);
});

$(document).on('click', '.remove-field', function(e) {
    e.preventDefault();
    var fieldName = $(this).closest('.custom-field-row').find('.custom-field-name').val();
    if (fieldName && customColumns.indexOf(fieldName) > -1) {
        customColumns.splice(customColumns.indexOf(fieldName), 1);
    }
    $(this).closest('.custom-field-row').remove();
});

$('#saveCustomOptions').click(function(e) {
    e.preventDefault();
    
    var newColumns = [];
    $('.custom-field-name').each(function() {
        var value = $(this).val().trim();
        if (value !== '') {
            newColumns.push(value);
        }
    });
    
    var $btn = $(this);
    $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Đang lưu...');
    
    $.ajax({
        type: 'POST',
        url: '<?= site_url("quotes/save_custom_columns") ?>',
        data: { columns: newColumns },
        dataType: 'json',
        success: function(response) {
            $btn.prop('disabled', false).html('Lưu');
            
            if (response.success) {
                window.customColumns = response.columns;
                customColumns = response.columns;
                
                // Xóa header cũ
                $('#quTable thead tr th.custom-column-header').remove();
                $('#quTable tbody tr td.custom-column-cell').remove();
                
                // Thêm header mới
                var $headerRow = $('#quTable thead tr');
                var $priceHeader = $headerRow.find('th').eq(1);
                
                customColumns.forEach(function(columnName) {
                    $priceHeader.before('<th class="custom-column-header">' + columnName + '</th>');
                });
                
                // Thêm cell cho hàng hiện có
                $('#quTable tbody tr').each(function() {
                    var $row = $(this);
                    var $priceTd = $row.find('td').eq(1);
                    
                    customColumns.forEach(function(columnName) {
                        var fieldName = 'custom_' + columnName.replace(/\s+/g, '_');
                        var cellHtml = '<td class="custom-column-cell">' +
                            '<input type="text" class="form-control custom-field-input" ' +
                            'name="' + fieldName + '[]" ' +
                            'placeholder="' + columnName + '">' +
                            '</td>';
                        $priceTd.before(cellHtml);
                    });
                });
                
                $('#customOptionsModal').modal('hide');
                bootbox.alert(response.message);
                audio_success.play();
            } else {
                bootbox.alert('Lỗi: ' + response.message);
                audio_error.play();
            }
        },
        error: function(xhr, status, error) {
            $btn.prop('disabled', false).html('Lưu');
            bootbox.alert('Lỗi kết nối: ' + error);
            audio_error.play();
        }
    });
});

        $(window).bind('beforeunload', function(e) {
            $.get('<?= site_url('welcome/set_data/remove_quls/1'); ?>');
            if (count > 1) {
                var message = "You will loss data!";
                return message;
            }
        });
        $('#reset').click(function(e) {
            $(window).unbind('beforeunload');
        });
        $('#edit_quote').click(function() {
            $(window).unbind('beforeunload');
            $('form.edit-qu-form').submit();
        });
    });
</script>

<style>
    .quote-edit-workspace {
        background: transparent;
        border: 0;
        box-shadow: none;
    }
    .quote-edit-workspace > .box-header {
        align-items: center;
        background: linear-gradient(135deg, #007c89 0%, #00a6b2 54%, #2dd4bf 100%);
        border: 0;
        border-radius: 15px;
        box-shadow: 0 14px 34px rgba(0, 128, 137, .18);
        color: #fff;
        display: flex;
        justify-content: space-between;
        margin-bottom: 14px;
        padding: 12px 16px;
        position: sticky;
        top: 54px;
        z-index: 95;
    }
    .quote-edit-workspace > .box-header h2 {
        color: #fff !important;
        font-size: 18px;
        font-weight: 900;
        margin: 0;
    }
    .quote-edit-workspace .main-task-lhson {
        display: flex;
        gap: 8px;
        margin: 0;
        position: static;
    }
    .quote-edit-workspace .main-task-lhson .btn {
        border: 0;
        border-radius: 999px;
        font-weight: 800;
        padding: 9px 16px;
    }
    .quote-edit-workspace .main-task-lhson .btn-primary {
        background: #fff !important;
        color: #007c89 !important;
        box-shadow: 0 10px 24px rgba(15, 23, 42, .14);
    }
    .quote-edit-workspace .main-task-lhson .btn-default {
        background: rgba(255, 255, 255, .16) !important;
        color: #fff !important;
    }
    .quote-edit-workspace .box-content {
        padding: 0;
    }
    .quote-helper-strip {
        display: grid;
        gap: 10px;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        margin-bottom: 14px;
    }
    .quote-helper-item {
        background: rgba(255, 255, 255, .92);
        border: 1px solid #ccfbf1;
        border-radius: 12px;
        box-shadow: 0 10px 22px rgba(0, 128, 137, .08);
        color: #334155;
        font-weight: 800;
        padding: 10px 12px;
    }
    .quote-helper-item i {
        background: linear-gradient(135deg, #00a6b2, #2dd4bf);
        border-radius: 10px;
        color: #fff;
        height: 28px;
        line-height: 28px;
        margin-right: 8px;
        text-align: center;
        width: 28px;
    }
    .quote-editor-main {
        display: flex;
        gap: 14px;
        align-items: flex-start;
    }
    .quote-products-panel {
        flex: 1 1 auto;
        min-width: 0;
    }
    .quote-info-panel {
        flex: 0 0 285px;
        max-height: none;
        max-width: 285px;
        overflow: visible;
        position: sticky;
        top: 126px;
        width: 285px;
    }
    .quote-edit-workspace #sticker {
        background: rgba(255, 255, 255, .94);
        border: 1px solid #e0f2fe;
        border-radius: 14px;
        box-shadow: 0 12px 26px rgba(0, 128, 137, .1);
        margin-bottom: 12px;
        padding: 10px;
        position: sticky;
        top: 126px;
        z-index: 60;
    }
    .quote-edit-workspace #add_item {
        border: 0;
        border-radius: 12px 0 0 12px;
        box-shadow: none;
        font-size: 15px;
        height: 42px;
    }
    .quote-edit-workspace #sticker .input-group-addon {
        background: #e6fffb;
        border: 0;
        border-left: 1px solid #ccfbf1;
        border-radius: 0 12px 12px 0;
    }
    .quote-edit-workspace .table-group {
        background: #fff;
        border: 1px solid #e8eef5;
        border-radius: 14px;
        box-shadow: 0 12px 28px rgba(0, 128, 137, .08);
        overflow: hidden;
    }
    .quote-edit-workspace .table-controls {
        overflow-x: auto;
    }
    .quote-edit-workspace #quTable {
        table-layout: fixed;
        margin-bottom: 0;
        min-width: 860px;
    }
    .quote-edit-workspace #quTable thead th {
        background: #d8fbf7;
        border-color: #a7f3d0;
        color: #075985;
        font-size: 11px;
        font-weight: 900;
        padding: 8px 6px;
        vertical-align: middle;
    }
    .quote-edit-workspace #quTable tbody td {
        padding: 6px 6px;
        vertical-align: middle;
    }
    .quote-edit-workspace #quTable th:nth-child(1),
    .quote-edit-workspace #quTable td:nth-child(1) {
        width: 38%;
    }
    .quote-edit-workspace #quTable th:nth-child(2),
    .quote-edit-workspace #quTable td:nth-child(2),
    .quote-edit-workspace #quTable th:nth-child(3),
    .quote-edit-workspace #quTable td:nth-child(3),
    .quote-edit-workspace #quTable th:nth-child(4),
    .quote-edit-workspace #quTable td:nth-child(4),
    .quote-edit-workspace #quTable th:nth-child(5),
    .quote-edit-workspace #quTable td:nth-child(5) {
        width: 58px;
    }
    .quote-edit-workspace #quTable th:nth-child(6),
    .quote-edit-workspace #quTable td:nth-child(6) {
        width: 64px;
    }
    .quote-edit-workspace #quTable th:nth-child(7),
    .quote-edit-workspace #quTable td:nth-child(7) {
        width: 90px;
    }
    .quote-edit-workspace #quTable th:nth-child(8),
    .quote-edit-workspace #quTable td:nth-child(8) {
        width: 72px;
    }
    .quote-edit-workspace #quTable th:nth-child(9),
    .quote-edit-workspace #quTable td:nth-child(9) {
        width: 96px;
    }
    .quote-edit-workspace #quTable input.form-control,
    .quote-edit-workspace #quTable select.form-control,
    .quote-edit-workspace #quTable .select2-choice,
    .quote-edit-workspace #quTable .custom-field-input {
        border-radius: 7px !important;
        box-sizing: border-box;
        font-size: 13px;
        height: 32px;
        line-height: 20px;
        min-height: 32px;
        min-width: 0;
        padding: 4px 6px;
        text-align: center;
        width: 100% !important;
    }
    .quote-edit-workspace #quTable td:first-child input.form-control,
    .quote-edit-workspace #quTable .custom-field-input {
        text-align: left;
    }
    .quote-edit-workspace #quTable textarea.form-control,
    .quote-edit-workspace #quTable .rcomment,
    .quote-edit-workspace #quTable .product-note {
        font-size: 13px;
        min-height: 34px;
        padding: 6px 8px;
    }
    .quote-edit-workspace .lhson_baogia_add {
        background: rgba(255, 255, 255, .94);
        border: 1px solid #ccfbf1;
        border-radius: 14px;
        box-shadow: 0 14px 30px rgba(0, 128, 137, .1);
        padding: 12px 10px;
        width: 100%;
    }
    .quote-edit-workspace .lhson_baogia_add:before {
        color: #007c89;
        content: "Thông tin báo giá";
        display: block;
        font-size: 15px;
        font-weight: 900;
        margin: 0 6px 10px;
    }
    .quote-edit-workspace .lhson_baogia_add > .col-md-4,
    .quote-edit-workspace .lhson_baogia_add > .row > .col-sm-12 {
        padding-left: 6px;
        padding-right: 6px;
        width: 100%;
    }
    .quote-edit-workspace .lhson_baogia_add > .row,
    .quote-edit-workspace .lhson_baogia_add > #bt {
        clear: both;
        margin-left: 0;
        margin-right: 0;
    }
    .quote-edit-workspace .lhson_baogia_add > #bt > .col-sm-12,
    .quote-edit-workspace .lhson_baogia_add #construction_address {
        width: 100%;
    }
    .quote-edit-workspace .lhson_baogia_add .form-group {
        margin-bottom: 8px;
    }
    .quote-edit-workspace .lhson_baogia_add label,
    .quote-edit-workspace .lhson_baogia_add .form-group > label,
    .quote-edit-workspace .lhson_baogia_add .form-group > .control-label {
        color: #475569;
        display: block;
        font-size: 11px;
        font-weight: 800;
        margin-bottom: 5px;
    }
    .quote-edit-workspace .lhson_baogia_add .form-control,
    .quote-edit-workspace .lhson_baogia_add .select2-choice {
        border-color: #bfe9ee;
        border-radius: 8px !important;
        min-height: 34px;
    }
    .quote-edit-workspace #construction_address,
    .quote-edit-workspace #qunote {
        min-height: 78px;
        resize: vertical;
    }
    .quote-edit-workspace #bottom-total {
        background: linear-gradient(135deg, #fff 0%, #e6fffb 100%);
        border: 1px solid #ccfbf1;
        border-radius: 14px;
        bottom: 0;
        box-shadow: 0 -10px 26px rgba(0, 128, 137, .1);
        margin-top: 14px;
        position: sticky;
        z-index: 50;
    }
    .quote-edit-workspace #bottom-total .table {
        background: transparent;
    }
    .quote-edit-workspace #bottom-total td {
        border-color: #ccfbf1;
        color: #007c89;
        font-weight: 900;
    }
    @media (max-width: 1199px) {
        .quote-editor-main {
            display: block;
        }
        .quote-info-panel {
            max-height: none;
            max-width: 285px;
            position: static;
            width: 285px;
        }
        .quote-helper-strip {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }
    @media (max-width: 767px) {
        .quote-edit-workspace > .box-header {
            align-items: flex-start;
            display: block;
            position: static;
        }
        .quote-edit-workspace .main-task-lhson {
            margin-top: 10px;
        }
        .quote-helper-strip {
            grid-template-columns: 1fr;
        }
        .quote-edit-workspace .lhson_baogia_add > .col-md-4,
        .quote-edit-workspace .lhson_baogia_add > .row > .col-sm-12 {
            width: 100%;
        }
        .quote-info-panel {
            max-width: 100%;
            width: 100%;
        }
    }
</style>

<div class="box quote-edit-workspace">
    <?php
    $attrib = array('data-toggle' => 'validator', 'role' => 'form', 'class' => 'edit-qu-form');
    echo form_open_multipart("quotes/edit/" . $id, $attrib)
    ?>
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-edit"></i><?= lang('edit_quote'); ?></h2>
        <div class="main-task-lhson nhapkho">

            <button type="submit" class="btn btn-primary btncls" name="edit_quote" id="edit_quote">
                <i class="fa fa-save"></i>
                <?= lang('edit_quote'); ?>
            </button>
            <button type="button" class="btn btn-default btncls" id="reset">
                <i class="fa fa-refresh"></i>
                <?= lang('reset') ?>
            </button>
        </div>
    </div>
    <div class="box-content baogia_lhson">
        <div class="quote-helper-strip">
            <div class="quote-helper-item"><i class="fa fa-user"></i> 1. Chọn khách/kho</div>
            <div class="quote-helper-item"><i class="fa fa-search"></i> 2. Tìm & thêm sản phẩm</div>
            <div class="quote-helper-item"><i class="fa fa-calendar"></i> 3. Kiểm ngày giao/cọc</div>
            <div class="quote-helper-item"><i class="fa fa-save"></i> 4. Lưu báo giá</div>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="quote-editor-main">
                        <div class="quote-products-panel">
                            <div class="col-md-12" id="sticker">
                                <div class="input-group wide-tip">
                                    <?php echo form_input('add_item', '', 'class="form-control input-lg" id="add_item" placeholder="' . $this->lang->line("add_product_to_order") . '"'); ?>
                                    <?php if ($Owner || $Admin || $GP['products-add']) { ?>
                                        <div class="input-group-addon" style="padding-left: 10px; padding-right: 10px;">
                                            <a href="#" id="addCustomOptions" class="tip" title="Thêm tùy chọn">
                                                <i class="fa fa-2x fa-cog addIcon"></i>
                                            </a>
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>
                            <div class="clearfix"></div>
                            <div class="col-md-12">
                                <div class="control-group table-group">
                                    <div class="controls table-controls">
                                        <table id="quTable"
                                            class="table items table-striped table-bordered table-condensed table-hover sortable_table">
                                            <thead>
                                                <tr>
                                                    <th class="col-md-4"><?= lang('product') . ' (' . lang('code') . ' - ' . lang('name') . ')'; ?></th>
                                                    <th class="col-md-1"><?= lang("net_unit_price"); ?></th>
                                                    <th class="col-md-1"><?= lang("quantity"); ?></th>
<?php
if ($Settings->product_discount && ($Owner || $Admin || $this->session->userdata('allow_discount'))) {
    echo '<th class="col-md-1">' . lang("product_discount") . '</th>';
}
?>

                                                    <?php
                                                    if ($Settings->tax1) {
                                                        echo '<th class="col-md-2">' . $this->lang->line("product_tax") . '</th>';
                                                    }
                                                    ?>
                                                    <th><?= lang("subtotal"); ?> (<span class="currency"><?= $default_currency->code ?></span>)</th>
                                                    <th style="width: 30px !important; text-align: center;"><i class="fa fa-trash-o" style="opacity:0.5; filter:alpha(opacity=50);"></i></th>
                                                </tr>
                                            </thead>
                                            <tbody></tbody>
                                            <tfoot></tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <input type="hidden" name="total_items" value="" id="total_items" required="required" />
                        </div>
                        <div class="quote-info-panel">
                        <div class="lhson_baogia_add">

                            <?php if ($Owner || $Admin || !$this->session->userdata('warehouse_id')) { ?>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <?php
                                        $wh[''] = '';
                                        foreach ($warehouses as $warehouse) {
                                            $wh[$warehouse->id] = $warehouse->name;
                                        }
                                        echo form_dropdown('warehouse', $wh, (isset($_POST['warehouse']) ? $_POST['warehouse'] : $inv->warehouse_id), 'id="quwarehouse" class="form-control input-tip select" data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("warehouse") . '" required="required" style="width:100%;" ');
                                        ?>
                                    </div>
                                </div>
                            <?php } else {
                                $warehouse_input = array(
                                    'type' => 'hidden',
                                    'name' => 'warehouse',
                                    'id' => 'slwarehouse',
                                    'value' => $this->session->userdata('warehouse_id'),
                                );
                                echo form_input($warehouse_input);
                            } ?>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?php
                                    echo form_input('customer', (isset($_POST['customer']) ? $_POST['customer'] : ""), 'id="qucustomer" data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("customer") . '" required="required" class="form-control input-tip" style="width:100%;"');
                                    ?>
                                </div>
                            </div>
                            <?php if ($Owner || $Admin) { ?>
    <div class="col-md-4">
        <div class="form-group">
            <label for="qudate">Ngày nhận đơn</label>
            <?php echo form_input('date', (isset($_POST['date']) ? $_POST['date'] : ($inv->date ? date('Y-m-d', strtotime($inv->date)) : "")), 'class="form-control input-tip date" id="qudate" required="required" type="date"'); ?>
        </div>
    </div>
<div class="col-md-4">
    <div class="form-group">
        <label for="shipping_date">Ngày giao chành</label>
        <?php echo form_input('shipping_date', (isset($_POST['shipping_date']) ? $_POST['shipping_date'] : ($inv->shipping_date ? date('d/m/Y', strtotime($inv->shipping_date)) : "")), 'class="form-control input-tip date" id="shipping_date"'); ?>
    </div>
</div>

<div class="col-md-4">
    <div class="form-group">
        <label for="expected_delivery_date">Ngày khách nhận dự kiến</label>
        <?php echo form_input('expected_delivery_date', (isset($_POST['expected_delivery_date']) ? $_POST['expected_delivery_date'] : ($inv->expected_delivery_date ? date('d/m/Y', strtotime($inv->expected_delivery_date)) : "")), 'class="form-control input-tip date" id="expected_delivery_date"'); ?>
    </div>
</div>

<div class="col-md-4">
    <div class="form-group">
        <label for="expected_installation_date">Ngày lắp đặt dự kiến</label>
        <?php echo form_input('expected_installation_date', (isset($_POST['expected_installation_date']) ? $_POST['expected_installation_date'] : ($inv->expected_installation_date ? date('d/m/Y', strtotime($inv->expected_installation_date)) : "")), 'class="form-control input-tip date" id="expected_installation_date"'); ?>
    </div>
</div>
<div class="col-md-4">
    <div class="form-group">
        <label for="shipping_info">Thông tin chành xe</label>
        <?php echo form_input('shipping_info', (isset($_POST['shipping_info']) ? $_POST['shipping_info'] : $this->sma->decode_html($inv->shipping_info)), 'class="form-control input-tip" id="shipping_info" placeholder="Ví dụ: Vận tải XYZ, giá ship 500k..."'); ?>
    </div>
</div>
<div class="col-md-4">
    <div class="form-group">
        <label for="construction_address">Địa chỉ công trình</label>
        <?php echo form_textarea('construction_address', (isset($_POST['construction_address']) ? $_POST['construction_address'] : $this->sma->decode_html($inv->construction_address)), 'class="form-control input-tip" id="construction_address" rows="3" placeholder="Nhập địa chỉ công trình..."'); ?>
    </div>
</div>
                            <?php } ?>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang("reference_no", "quref"); ?>
                                    <?php echo form_input('reference_no', (isset($_POST['reference_no']) ? $_POST['reference_no'] : ''), 'class="form-control input-tip" id="quref" required="required"'); ?>
                                </div>
                            </div>
                            <?php if ($Owner || $Admin || !$this->session->userdata('biller_id')) { ?>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <?php
                                        $bl[""] = "";
                                        foreach ($billers as $biller) {
                                            $bl[$biller->id] = $biller->company != '-' ? $biller->company : $biller->name;
                                        }
                                        echo form_dropdown('biller', $bl, (isset($_POST['biller']) ? $_POST['biller'] : $inv->biller_id), 'id="qubiller" data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("biller") . '" required="required" class="form-control input-tip select" style="width:100%;"');
                                        ?>
                                    </div>
                                </div>
                            <?php } else {
                                $biller_input = array(
                                    'type' => 'hidden',
                                    'name' => 'biller',
                                    'id' => 'qubiller',
                                    'value' => $this->session->userdata('biller_id'),
                                );
                                echo form_input($biller_input);
                            } ?>

                            <?php if ($Settings->tax2) { ?>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <?php
                                        $tr[""] = "";
                                        foreach ($tax_rates as $tax) {
                                            $tr[$tax->id] = $tax->name;
                                        }
                                        echo form_dropdown('order_tax', $tr, (isset($_POST['tax2']) ? $_POST['tax2'] : $Settings->default_tax_rate2), 'id="qutax2" data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("order_tax") . '" required="required" class="form-control input-tip select" style="width:100%;"');
                                        ?>
                                    </div>
                                </div>
                            <?php } ?>

                        

                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang("shipping", "qushipping"); ?>
                                    <?php echo form_input('shipping', '', 'class="form-control input-tip" id="qushipping"'); ?>

                                </div>
                            </div>
                            <div class="col-md-4">
    <div class="form-group">
        <label for="deposit_amount">Số tiền đã cọc</label>
        <?php echo form_input('deposit_amount', (isset($_POST['deposit_amount']) ? $_POST['deposit_amount'] : $inv->deposit_amount), 'class="form-control input-tip" id="deposit_amount" type="number" step="0.01"'); ?>
    </div>
</div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang("status", "qustatus"); ?>
                                    <?php 
$st = array(
    'Đang báo giá' => 'Đang báo giá',
    'Đã chốt cọc' => 'Đã chốt cọc',
    'Đã đặt hàng' => 'Đã đặt hàng',
    'Đã giao chành' => 'Đã giao chành',
    'Khách đã nhận' => 'Khách đã nhận',
    'Hoàn thành' => 'Hoàn thành'
);
echo form_dropdown('status', $st, 'Đang báo giá', 'class="form-control input-tip" id="qustatus"'); 
?>

                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang("supplier", "qusupplier"); ?>
                                    <?php if ($Owner || $Admin || $GP['suppliers-add'] || $GP['suppliers-index']) { ?><div class="input-group"><?php } ?>
                                        <input type="hidden" name="supplier" value="" id="qusupplier"
                                            class="form-control" style="width:100%;"
                                            placeholder="<?= lang("select") . ' ' . lang("supplier") ?>">
                                        <input type="hidden" name="supplier_id" value="" id="supplier_id"
                                            class="form-control">
                                        <?php if ($Owner || $Admin || $GP['suppliers-index']) { ?>
                                            <div class="input-group-addon no-print" style="padding: 2px 5px; border-left: 0;">
                                                <a href="#" id="view-supplier" class="external" data-toggle="modal" data-target="#myModal">
                                                    <i class="fa fa-2x fa-user" id="addIcon"></i>
                                                </a>
                                            </div>
                                        <?php } ?>
                                        <?php if ($Owner || $Admin || $GP['suppliers-add']) { ?>
                                            <div class="input-group-addon no-print" style="padding: 2px 5px;">
                                                <a href="<?= site_url('suppliers/add'); ?>" id="add-supplier" class="external" data-toggle="modal" data-target="#myModal">
                                                    <i class="fa fa-2x fa-plus-circle" id="addIcon"></i>
                                                </a>
                                            </div>
                                        <?php } ?>
                                        <?php if ($Owner || $Admin || $GP['suppliers-add'] || $GP['suppliers-index']) { ?>
                                        </div><?php } ?>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang("document", "document") ?>
                                    <input id="document" type="file" data-browse-label="<?= lang('browse'); ?>" name="document" data-show-upload="false"
                                        data-show-preview="false" class="form-control file">
                                </div>
                            </div>


                            <div class="row" id="bt">
                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <?= lang("note", "qunote"); ?>
                                        <?php echo form_textarea('note', (isset($_POST['note']) ? $_POST['note'] : ""), 'class="form-control" id="qunote" style="margin-top: 10px; height: 100px;"'); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        </div>
                        </div>
                    </div>
                </div>
                <div id="bottom-total" class="well well-sm" style="margin-bottom: 0;">
                    <table class="table table-bordered table-condensed totals" style="margin-bottom:0;">
                        <tr class="warning">
                            <td><?= lang('items') ?> <span class="totals_val pull-right" id="titems">0</span></td>
                            <td><?= lang('total') ?> <span class="totals_val pull-right" id="total">0.00</span></td>
                            <?php if ($Settings->tax2) { ?>
                                <td><?= lang('order_tax') ?> <span class="totals_val pull-right" id="ttax2">0.00</span></td>
                            <?php } ?>
                            <td><?= lang('shipping') ?> <span class="totals_val pull-right" id="tship">0.00</span></td>
                            <td><?= lang('grand_total') ?> <span class="totals_val pull-right" id="gtotal">0.00</span></td>
                        </tr>
                    </table>
                </div>


            </div>

        </div>
    </div>

    <?php echo form_close(); ?>
</div>

<div class="modal" id="prModal" tabindex="-1" role="dialog" aria-labelledby="prModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true"><i
                            class="fa fa-2x">&times;</i></span><span class="sr-only"><?= lang('close'); ?></span></button>
                <h4 class="modal-title" id="prModalLabel"></h4>
            </div>
            <div class="modal-body" id="pr_popover_content">
                <form class="form-horizontal" role="form">
                    
                    <?php if ($Settings->tax1) { ?>
                        <div class="form-group">
                            <label class="col-sm-4 control-label"><?= lang('product_tax') ?></label>
                            <div class="col-sm-8">
                                <?php
                                $tr[""] = "";
                                foreach ($tax_rates as $tax) {
                                    $tr[$tax->id] = $tax->name;
                                }
                                echo form_dropdown('ptax', $tr, "", 'id="ptax" class="form-control pos-input-tip" style="width:100%;"');
                                ?>
                            </div>
                        </div>
                    <?php } ?>
                    <?php if ($Settings->product_serial) { ?>
                        <div class="form-group">
                            <label for="pserial" class="col-sm-4 control-label"><?= lang('serial_no') ?></label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="pserial">
                            </div>
                        </div>
                    <?php } ?>
                    <div class="form-group">
                        <label for="pquantity" class="col-sm-4 control-label"><?= lang('quantity') ?></label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="pquantity">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="punit" class="col-sm-4 control-label"><?= lang('product_unit') ?></label>
                        <div class="col-sm-8">
                            <div id="punits-div"></div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="poption" class="col-sm-4 control-label"><?= lang('product_option') ?></label>
                        <div class="col-sm-8">
                            <div id="poptions-div"></div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="pprice" class="col-sm-4 control-label"><?= lang('unit_price') ?></label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="pprice" <?= ($Owner || $Admin || $GP['edit_price']) ? '' : 'readonly'; ?>>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="popen_direction" class="col-sm-4 control-label">Hướng mở</label>
                        <div class="col-sm-8">
                            <select class="form-control" id="popen_direction">
                                <option value="">-- Chọn --</option>
                                <option value="H1 – Vào trái">H1 – Vào trái</option>
                                <option value="H2 – Vào phải">H2 – Vào phải</option>
                                <option value="H3 – Ra trái">H3 – Ra trái</option>
                                <option value="H4 – Ra phải">H4 – Ra phải</option>
                            </select>
                        </div>
                    </div>
                    <table class="table table-bordered table-striped">
                        <tr>
                            <th style="width:25%;"><?= lang('net_unit_price'); ?></th>
                            <th style="width:25%;"><span id="net_price"></span></th>
                            <th style="width:25%;"><?= lang('product_tax'); ?></th>
                            <th style="width:25%;"><span id="pro_tax"></span></th>
                        </tr>
                    </table>
                    <input type="hidden" id="punit_price" value="" />
                    <input type="hidden" id="old_tax" value="" />
                    <input type="hidden" id="old_qty" value="" />
                    <input type="hidden" id="old_price" value="" />
                    <input type="hidden" id="row_id" value="" />
                    <input type="hidden" id="item_id" value="" />
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="editItem"><?= lang('submit') ?></button>
            </div>
        </div>
    </div>
</div>

<div class="modal" id="mModal" tabindex="-1" role="dialog" aria-labelledby="mModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true"><i
                            class="fa fa-2x">&times;</i></span><span class="sr-only"><?= lang('close'); ?></span></button>
                <h4 class="modal-title" id="mModalLabel"><?= lang('add_product_manually') ?></h4>
            </div>
            <div class="modal-body" id="pr_popover_content">
                <form class="form-horizontal" role="form">
                    <div class="form-group">
                        <label for="mcode" class="col-sm-4 control-label"><?= lang('product_code') ?> *</label>

                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="mcode">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="mname" class="col-sm-4 control-label"><?= lang('product_name') ?> *</label>

                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="mname">
                        </div>
                    </div>
                    <?php if ($Settings->tax1) { ?>
                        <div class="form-group">
                            <label for="mtax" class="col-sm-4 control-label"><?= lang('product_tax') ?> *</label>

                            <div class="col-sm-8">
                                <?php
                                $tr[""] = "";
                                foreach ($tax_rates as $tax) {
                                    $tr[$tax->id] = $tax->name;
                                }
                                echo form_dropdown('mtax', $tr, "", 'id="mtax" class="form-control input-tip select" style="width:100%;"');
                                ?>
                            </div>
                        </div>
                    <?php } ?>
                    <div class="form-group">
                        <label for="mquantity" class="col-sm-4 control-label"><?= lang('quantity') ?> *</label>

                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="mquantity">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="mprice" class="col-sm-4 control-label"><?= lang('unit_price') ?> *</label>

                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="mprice">
                        </div>
                    </div>
                    <table class="table table-bordered table-striped">
                        <tr>
                            <th style="width:25%;"><?= lang('net_unit_price'); ?></th>
                            <th style="width:25%;"><span id="mnet_price"></span></th>
                            <th style="width:25%;"><?= lang('product_tax'); ?></th>
                            <th style="width:25%;"><span id="mpro_tax"></span></th>
                        </tr>
                    </table>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="addItemManually"><?= lang('submit') ?></button>
            </div>
        </div>
    </div>
</div>
<div class="modal" id="customOptionsModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true"><i class="fa fa-2x">&times;</i></span>
                </button>
                <h4 class="modal-title">Thêm tùy chỉnh trường</h4>
            </div>
            <div class="modal-body">
                <form class="form-horizontal" role="form" id="customOptionsForm">
                    <div id="customFieldsContainer">
                        <div class="form-group custom-field-row">
                            <label class="col-sm-4 control-label">Tên trường</label>
                            <div class="col-sm-7">
                                <input type="text" class="form-control custom-field-name" placeholder="Ví dụ: Màu sắc, Kích thước...">
                            </div>
                            <div class="col-sm-1">
                                <button type="button" class="btn btn-danger btn-sm remove-field">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-success btn-sm" id="addMoreField">
                        <i class="fa fa-plus"></i> Thêm trường
                    </button>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="saveCustomOptions">Lưu</button>
            </div>
        </div>
    </div>
</div>
