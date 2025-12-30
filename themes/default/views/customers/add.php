<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog modal-lg lhson_add_biller">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('add_customer'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form', 'id' => 'add-customer-form');
        echo form_open_multipart("customers/add", $attrib); ?>
        <div class="modal-body">            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                    <label class="control-label" for="customer_group"><?php echo $this->lang->line("customer_group"); ?></label>
                        <?php
                        foreach ($customer_groups as $customer_group) {
                            $cgs[$customer_group->id] = $customer_group->name;
                        }
                        echo form_dropdown('customer_group', $cgs, $Settings->customer_group, 'class="form-control select" id="customer_group" style="width:100%;" required="required"');
                        ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="control-label" for="price_group"><?php echo $this->lang->line("price_group"); ?></label>
                        <?php
                        $pgs[''] = lang('select').' '.lang('price_group');
                        foreach ($price_groups as $price_group) {
                            $pgs[$price_group->id] = $price_group->name;
                        }
                        echo form_dropdown('price_group', $pgs, $Settings->price_group, 'class="form-control select" id="price_group" style="width:100%;"');
                        ?>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    
                    <div class="form-group person">
                        <?= lang("Loại khách", "loaikhach"); ?>
                        <div class="form-check form-check-inline">
                          <input class="form-check-input" checked type="radio" name="loaikhach" id="inlineRadiol1" value="1">
                          <label class="form-check-label" for="inlineRadio1">Cá Nhân</label>
                        </div>
                        <div class="form-check form-check-inline">
                          <input class="form-check-input"  type="radio" name="loaikhach" id="inlineRadiol2" value="0">
                          <label class="form-check-label" for="inlineRadio1">Công ty</label>
                        </div>
                    </div>
                    <div class="form-group company" style="display: none">
                        <?= lang("company", "company"); ?>
                        <?php echo form_input('company', '', 'class="form-control tip" id="company"'); ?>
                    </div>
                    <div class="form-group vat_no" style="display: none">
                        <?= lang("Mã số thuế", "vat_no"); ?>
                        <?php echo form_input('vat_no', '', 'class="form-control" id="vat_no"'); ?>
                    </div> 
                    <div class="form-group person">
                        <?= lang("Họ và Tên", "name"); ?>
                        <?php echo form_input('name', '', 'class="form-control tip" id="name" data-bv-notempty="true"'); ?>
                    </div>
                    <div class="form-group gioitinh">
                        <?= lang("Giới tính", "gioitinh"); ?>
                        <div class="form-check form-check-inline">
                          <input class="form-check-input" checked type="radio" name="gioitinh" id="inlineRadio1" value="1">
                          <label class="form-check-label" for="inlineRadio1">Nam</label>
                        </div>
                        <div class="form-check form-check-inline">
                          <input class="form-check-input" type="radio" name="gioitinh" id="inlineRadio2" value="0">
                          <label class="form-check-label" for="inlineRadio2">Nữ</label>
                        </div>
                    </div>

                    <div class="form-group ngaysinh">
                        <?= lang("Ngày sinh", "ngaysinh"); ?>
                        <?php echo form_input('ngaysinh', (isset($_POST['ngaysinh']) ? $_POST['ngaysinh'] : ""), 'class="form-control date" place_holder="Ngày-tháng-năm" id="ngaysinh" required="required" autocomplete="off"'); ?>
                    </div>
                    <div class="form-group">
                        <?= lang("phone", "phone"); ?>
                        <input type="tel" name="phone" class="form-control" required="required" id="phone"/>
                    </div>
                    <div class="form-group">
                        <?= lang("email_address", "email_address"); ?>
                        <input type="email" name="email" class="form-control" id="email_address"/>
                    </div>
                                      
                    
                    <div class="form-group">
                        <?= lang("address", "address"); ?>
                        <?php echo form_input('address', '', 'class="form-control" required="required" id="address"'); ?>
                    </div>

                </div>
                <div class="col-md-6">                    
                    <div class="form-group">
                        <?= lang("Facebook", "facebook"); ?>
                        <?php echo form_input('facebook', '', 'class="form-control" id="facebook"'); ?>
                    </div>
                    <div class="form-group">
                        <?= lang("ccf1", "cf1"); ?>
                        <?php echo form_input('cf1', '', 'class="form-control" id="cf1"'); ?>
                    </div>
                    <div class="form-group">
                        <?= lang("ccf2", "cf2"); ?>
                        <?php echo form_input('cf2', '', 'class="form-control" id="cf2"'); ?>

                    </div>
                    <div class="form-group">
                        <?= lang("Ghi chú", "cf5"); ?>
                        <textarea id="ghichu" name="ghichu"></textarea>

                    </div>
                    <div class="form-group">
                        <?= lang("Nợ ban đầu", "nobandau"); ?>
                        <?php echo form_input('nobandau', '', 'class="form-control" id="nobandau"'); ?>
                    </div>
                </div>
            </div>


        </div>
        <div class="modal-footer">
			<button type="submit" class="btn btn-primary btncls" name="add_customer" id="add_customer">
				<i class="fa fa-save"></i>
				<?php echo lang('add_customer');?>
			</button>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>

<script type="text/javascript">
    $(document).ready(function (e) {
        $("#ngaysinh").datetimepicker({
                 format:'dd/mm/yyyy',
                fontAwesome: true,
                language: 'sma',
                weekStart: 1,
                todayBtn: 1,
                autoclose: 1,
                todayHighlight: 1,
                startView: 2,
                forceParse: 0, 
                showTimepicker: false,
                minView: 2,
                showClear:true,
                showClose:true,
        }).datetimepicker('update','');

        $('#add-customer-form').bootstrapValidator({
            feedbackIcons: {
                valid: 'fa fa-check',
                invalid: 'fa fa-times',
                validating: 'fa fa-refresh'
            }, excluded: [':disabled']
        });
        $("input[name='loaikhach']").click(function(){
            var radioValue = $("input[name='loaikhach']:checked").val();
            console.log(radioValue);
            if(radioValue==0){
                $(".lhson_add_biller .form-group.company").attr("style","display:block");
                $(".lhson_add_biller .form-group.vat_no").attr("style","display:block");
            }else{
                $(".lhson_add_biller .form-group.company").attr("style","display:none");
                $(".lhson_add_biller .form-group.vat_no").attr("style","display:none");
            }
        });
        $('select.select').select2({minimumResultsForSearch: 7});
        fields = $('.modal-content').find('.form-control');
        $.each(fields, function () {
            var id = $(this).attr('id');
            var iname = $(this).attr('name');
            var iid = '#' + id;
            if (!!$(this).attr('data-bv-notempty') || !!$(this).attr('required')) {
                $("label[for='" + id + "']").append(' *');
                $(document).on('change', iid, function () {
                    $('form[data-toggle="validator"]').bootstrapValidator('revalidateField', iname);
                });
            }
        });
    });
</script>
