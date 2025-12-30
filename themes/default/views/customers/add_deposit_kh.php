<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('add_deposit'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo form_open("customers/add_deposit/", $attrib); ?>
        <div class="modal-body">
            <div class="row">
                <div class="col-sm-12">
                    <?php if ($Owner || $Admin) { ?>
                    <div class="form-group">
                        <?php echo lang('date', 'date'); ?>
                        <div class="controls">
                            <?php echo form_input('date', set_value('date', date($dateFormats['php_ldate'])), 'class="form-control datetime" id="date" required="required"'); ?>
                        </div>
                    </div>
                    <?php } ?>
            <div class="clearfix"></div>
            <div class="form-group" id="div_customer_id">                   
                    <?php
                    echo form_input('customer_id', (isset($_POST['customer']) ? $_POST['customer'] : ""), 'id="slcustomeraddpt" data-placeholder="' . lang("select") . ' ' . lang("customer") . '" class="form-control input-tip select2" style="width:100%;"');
                    ?>
            </div>        
            <div class="clearfix"></div>
            <div class="form-group">
                <?= lang("Tên", "c_name"); ?>
                <input name="c_name" type="text" id="c_name" value="<?=$company->name;?>" class="form-control c_name"
                       required="required"/>
            </div>
            <div class="form-group">
                <?= lang("Điện thoại", "c_phone"); ?>
                <input name="c_phone" type="text" id="c_phone" value="<?=$company->phone;?>" class="form-control c_phone"
                       />
            </div>           
            <div class="form-group">
                <?= lang("Địa chỉ", "c_address"); ?>
                <?php echo form_textarea('c_address', $company->address, 'class="form-control skip" id="c_address"'); ?>
            </div>

            <div class="form-group">
                <?php echo lang('amount', 'amount'); ?>
                <div class="controls">
                    <?php echo form_input('amount', set_value('amount'), 'class="form-control" id="amount" required="required"'); ?>
                </div>
            </div>

           
            <div class="form-group">
                <?= lang("paying_by", "paid_by_1"); ?>
                <select name="paid_by" id="paid_by_1" class="form-control paid_by" required="required">
                    <?= $this->sma->paid_opts_lhson(false, true); ?>
                </select>
                <div class="clearfix"></div>
                <div class="pcc_1" style="display:none;">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <input name="pcc_no" type="text" id="pcc_no_1" class="form-control"
                                       placeholder="<?= lang('cc_no') ?>"/>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">

                                <input name="pcc_holder" type="text" id="pcc_holder_1" class="form-control"
                                       placeholder="<?= lang('cc_holder') ?>"/>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <select name="pcc_type" id="pcc_type_1" class="form-control pcc_type"
                                        placeholder="<?= lang('card_type') ?>">
                                    <option value="Visa"><?= lang("Visa"); ?></option>
                                    <option value="MasterCard"><?= lang("MasterCard"); ?></option>
                                    <option value="Amex"><?= lang("Amex"); ?></option>
                                    <option value="Discover"><?= lang("Discover"); ?></option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <input name="pcc_month" type="text" id="pcc_month_1" class="form-control"
                                       placeholder="<?= lang('month') ?>"/>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">

                                <input name="pcc_year" type="text" id="pcc_year_1" class="form-control"
                                       placeholder="<?= lang('year') ?>"/>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">

                                <input name="pcc_ccv" type="text" id="pcc_cvv2_1" class="form-control"
                                       placeholder="<?= lang('cvv2') ?>"/>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="pcheque_1" style="display:none;">
                    <div class="form-group"><?= lang("cheque_no", "cheque_no_1"); ?>
                        <input name="cheque_no" type="text" id="cheque_no_1" class="form-control cheque_no"/>
                    </div>
                </div>
            </div>
                    <div class="form-group">
                        <?php echo lang('note', 'note'); ?>
                        <div class="controls">
                            <?php echo form_textarea('note', set_value('note'), 'class="form-control" id="note"'); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <?php echo form_submit('add_deposit', lang('add_deposit'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<?= $modal_js ?>
<script type="text/javascript" charset="UTF-8">
    $(document).ready(function() {
        $('form').preventDoubleSubmission();
    });
    $(document).ready(function () {
        $('#slcustomeraddpt').select2({
            minimumInputLength: 1,
            allowClear:true,
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
        $(".select2").change(function(){
            var str=$(this).select2('data').text;
            var sp=str.split('-');
            $("#c_name").val(sp[0]);
            if (sp.length>0) {
                $("#c_phone").val(sp[1]);
            }           
        })
    });
</script>
<style>
.hiddenlhson{
    display:none;
}
.hiddenlhson .form-group .select2-container{
    width:100%!important;
}
.pcc_1 input,.pcc_1 select, .pcc_1 .select2-container,#s2id_pcc_type_1{
    width: 100%!important;
    float:left;
}
input#is_doanhthu {width: auto;}
</style>