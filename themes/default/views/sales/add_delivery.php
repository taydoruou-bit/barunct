<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('add_delivery'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo form_open_multipart("sales/add_delivery/".$inv->id, $attrib); ?>
        <div class="modal-body">
            <div class="row">
                <div class="col-md-6">
                    <?php if ($Owner || $Admin) { ?>
                        <div class="form-group">
                            <?= lang("date", "date"); ?>
                            <?= form_input('date', (isset($_POST['date']) ? $_POST['date'] : ""), 'class="form-control datetime" id="date" required="required"'); ?>
                        </div>
                    <?php } ?>
                    <div class="form-group">
                        <?= lang("Ngày giao hàng", "ngaynhan"); ?>
                        <?= form_input('ngaynhan',date("d/m/Y H:i",strtotime("+1 hours")), 'class="form-control datetime" id="ngaynhan" required="required"'); ?>
                    </div>
                    <div class="form-group">
                        <?= lang("do_reference_no", "do_reference_no"); ?>
                        <?= form_input('do_reference_no', (isset($_POST['do_reference_no']) ? $_POST['do_reference_no'] : $do_reference_no), 'class="form-control tip" id="do_reference_no"'); ?>
                    </div>

                    <div class="form-group">
                        <?= lang("sale_reference_no", "sale_reference_no"); ?>
                        <?= form_input('sale_reference_no', (isset($_POST['sale_reference_no']) ? $_POST['sale_reference_no'] : $inv->reference_no), 'class="form-control tip" id="sale_reference_no" required="required"'); ?>
                    </div>
                    <input type="hidden" value="<?php echo $inv->id; ?>" name="sale_id"/>

                    <div class="form-group">
                        <?= lang("Người nhận", "customer"); ?>
                        <?php echo form_input('customer', (isset($_POST['customer']) ? $_POST['customer'] : $customer->name), 'class="form-control" id="customer" required="required" '); ?>
                    </div>
                     <div class="form-group">
                        <?= lang("phone", "phone"); ?>
                        <?= form_input('phone', ($customer->phone), 'class="form-control" required="required" id="phone"'); ?>
                    </div>
                    <div class="form-group">
                        <?= lang("address", "address"); ?>
                        <?php echo form_textarea('address', (isset($_POST['address']) ? $_POST['address'] : $customer->address . " " . $customer->city . " " . $customer->state . " " . $customer->postal_code . " " . $customer->country . "<br>Tel: " . $customer->phone . " Email: " . $customer->email), 'class="form-control skip" id="address" required="required"'); ?>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <?= lang('status', 'status'); ?>
                        <?php
                        $opts = array('packing' => lang('packing'), 'delivering' => lang('delivering'), 'delivered' => lang('delivered'));
                        ?>
                        <?= form_dropdown('status', $opts, '', 'class="form-control" id="status" required="required" style="width:100%;"'); ?>
                    </div>

                    <div class="form-group" id="div_doitac_id">
                        <?= lang("delivered_by", "delivered_by"); ?>
                                                            
                            
                            <?php                                     
                                    
                                    $dt["0"] = "Chọn đối tác";
                                    foreach ($doitacs as $tax) { 
                                        $dt[$tax->id] = $tax->name; 
                                    }
                                    echo form_dropdown('delivered_by', $dt, (isset($_POST['delivered_by']) ? $_POST['delivered_by'] : ""), 'id="delivered_by" data-placeholder="' . lang("select") . ' ' . lang("doitac") . '" class="form-control input-tip select" style="width:100%;" required');
                                    ?>
                    </div>
                    <div class="form-group">
                        <?= lang("Phí giao hàng", "shipping"); ?>
                        <?= form_input('shipping', (isset($_POST['shipping']) ? $_POST['shipping'] : ''), 'class="form-control" id="shipping"'); ?>
                    </div>
                   

                    <div class="form-group">
                        <?= lang("attachment", "attachment") ?>
                        <input id="attachment" type="file" data-browse-label="<?= lang('browse'); ?>" name="document" data-show-upload="false" data-show-preview="false" class="form-control file">
                    </div>

                    <div class="form-group">
                        <?= lang("note", "note"); ?>
                        <?php echo form_textarea('note', (isset($_POST['note']) ? $_POST['note'] : ""), 'class="form-control" id="note"'); ?>
                    </div>
                </div>
            </div>

        </div>
        <div class="modal-footer">
            <?php echo form_submit('add_delivery', lang('add_delivery'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<script type="text/javascript" src="<?= $assets ?>js/custom.js"></script>
<script type="text/javascript" charset="UTF-8">
    $.fn.datetimepicker.dates['sma'] = <?=$dp_lang?>;
</script>
<?= $modal_js ?>
<script type="text/javascript" charset="UTF-8">
    $(document).ready(function () {
        $('#sldoitacaddpt').select2({
            minimumInputLength: 1,
            ajax: {
                url: site.base_url + "doitac/suggestions",
                dataType: 'json',
                quietMillis: 15,
                data: function (term, page) {
                    return {
                        term: term,
                        limit: 10
                    };
                },
                results: function (data, page) {
                    if (data.results != null) {
                        return {results: data.results};
                    } else {
                        return {results: [{id: '', text: 'No Match Found'}]};
                    }
                }
            }
        });
        
        $.fn.datetimepicker.dates['sma'] = <?=$dp_lang?>;
        $("#date").datetimepicker({
            format: site.dateFormats.js_ldate,
            fontAwesome: true,
            language: 'sma',
            weekStart: 1,
            todayBtn: 1,
            autoclose: 1,
            todayHighlight: 1,
            startView: 2,
            forceParse: 0
        }).datetimepicker('update', new Date());
    });
</script>
