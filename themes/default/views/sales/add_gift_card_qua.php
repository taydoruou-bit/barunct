<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script type="text/javascript" src="<?= $assets ?>js/custom.js"></script>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?= lang('Đổi quà tặng'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo form_open("sales/add_gift_card_qua", $attrib); ?>
        <div class="modal-body">
            <div class="form-group">
                <?= lang("Quà tặng", "tenquatang"); ?>
                    <?php echo form_input('tenquatang', '', 'class="form-control fullwidth" id="tenquatang" required="required"'); ?>
                    
                
            </div>
            <div class="form-group">
                <?= lang("value", "value"); ?>
                <?php echo form_input('value', '', 'class="form-control" id="value"'); ?>
            </div>
                       
            <div id="customer-con">
                <div class="form-group">
                    <?= lang("customer", "customer"); ?>
                    <?php echo form_input('customer', '', 'class="form-control" id="customer"'); ?>
                </div>
                <div class="well well-sm" id="award-points-con" style="display:none;">
                    <div class="form-group">
                        <p class="bold"><?= lang("award_points"); ?>: <span id="award_points"></span></p>
                        <input type="checkbox" class="checkbox" name="use_points" id="use_points"><label
                            for="use_points" class="padding05"><?= lang('use_award_points'); ?></label>
                    </div>
                    <div class="form-group" id="ca-points-con" style="display:none;">
                        <?= lang("use_points", "ca_points"); ?>
                        <?php echo form_input('ca_points', '', 'class="form-control" id="ca_points"'); ?>
                    </div>
                </div>
            </div>
            <div class="form-group" style="display: none">
                <?= lang("expiry_date", "expiry"); ?>
                <?php echo form_input('expiry', $this->sma->hrsd(date("Y-m-d", strtotime("-1 days"))), 'class="form-control date" id="expiry"'); ?>
            </div>

        </div>
        <div class="modal-footer">
            <?php echo form_submit('add_gift_card', lang('Thêm quà'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<?= $modal_js ?>
<script type="text/javascript">
    $(document).ready(function () {
        $('input[type="checkbox"],[type="radio"]').not('.skip').iCheck({
            checkboxClass: 'icheckbox_square-blue',
            radioClass: 'iradio_square-blue',
            increaseArea: '20%'
        });
        $('textarea').not('.skip').redactor({
            buttons: ['formatting', '|', 'alignleft', 'aligncenter', 'alignright', 'justify', '|', 'bold', 'italic', 'underline', '|', 'unorderedlist', 'orderedlist', '|', /*'image', 'video',*/ 'link', '|', 'html'],
            formattingTags: ['p', 'pre', 'h3', 'h4'],
            minHeight: 100,
            changeCallback: function(e) {
                var editor = this.$editor.next('textarea');
                if($(editor).attr('required')){
                    $('form[data-toggle="validator"]').bootstrapValidator('revalidateField', $(editor).attr('name'));
                }
            }
        });
        
        $.fn.datetimepicker.dates['sma'] = <?=$dp_lang?>;
        $('#customer').select2({
            minimumInputLength: 1,
            ajax: {
                url: site.base_url + "customers/suggestions",
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
        var customer_points = 0;
        $('#customer').on('select2-close', function () {
            var selected_customer = $(this).val();
            $.ajax({
                type: "get", async: false,
                url: site.base_url + "customers/get_award_points/" + selected_customer,
                dataType: 'json',
                success: function (data) {
                    if (data != null) {
                        $('#award_points').html(data.ca_points);
                        $('#ca_points').val(data.ca_points);
                        customer_points = parseInt(data.ca_points);
                        if (data.ca_points > 0) {
                            $('#award-points-con').slideDown();
                        } else {
                            $('#award-points-con').slideUp();
                        }
                    } else {
                        $('#award-points-con').slideUp();
                    }
                }
            });
        });
        $(document).on('change', '#ca_points', function () {
            if (parseInt($(this).val()) <= customer_points) {
                $("[name='add_gift_card']").attr('disabled', false);
            } else {
                $("[name='add_gift_card']").attr('disabled', true);
            }
        });
        $(document).on('ifChecked', '#use_points', function (event) {
            $('#ca-points-con').slideDown();
        });
        $(document).on('ifUnchecked', '#use_points', function (event) {
            $('#ca-points-con').slideUp();
        });
        
        $('#ca-points-con').slideDown();

        
    });

</script>    