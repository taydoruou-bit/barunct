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
                if ($(this).val().length >= 16 && ui.content[0].id == 0) {
                    bootbox.alert('<?= lang('no_match_found') ?>', function() {
                        $('#add_item').focus();
                    });
                    $(this).removeClass('ui-autocomplete-loading');
                    $(this).val('');
                } else if (ui.content.length == 1 && ui.content[0].id != 0) {
                    ui.item = ui.content[0];
                    $(this).data('ui-autocomplete')._trigger('select', 'autocompleteselect', ui);
                    $(this).autocomplete('close');
                    $(this).removeClass('ui-autocomplete-loading');
                } else if (ui.content.length == 1 && ui.content[0].id == 0) {
                    bootbox.alert('<?= lang('no_match_found') ?>', function() {
                        $('#add_item').focus();
                    });
                    $(this).removeClass('ui-autocomplete-loading');
                    $(this).val('');

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


<div class="box">
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
        <div class="row">
            <div class="col-lg-12">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="col-md-8">
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
                                                    <th>Ghi chú</th>
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
                        <div class="col-md-4 lhson_baogia_add">

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
                                        <?= lang("date", "qudate"); ?>
                                        <?php echo form_input('date', (isset($_POST['date']) ? $_POST['date'] : ""), 'class="form-control input-tip datetime" id="qudate" required="required"'); ?>
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
                                    <?= lang("status", "qustatus"); ?>
                                    <?php $st = array('pending' => lang('pending'), 'sent' => lang('sent'), 'completed' => lang('completed'));
                                    echo form_dropdown('status', $st, '', 'class="form-control input-tip" id="qustatus"'); ?>

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