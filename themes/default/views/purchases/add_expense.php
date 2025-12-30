<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog lhson_add_address">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('add_expense'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo form_open_multipart("purchases/add_expense", $attrib); ?>
        <div class="modal-body">
            <?php if ($Owner || $Admin) { ?>

                <div class="form-group">
                    <?= lang("date", "date"); ?>
                    <?= form_input('date', (isset($_POST['date']) ? $_POST['date'] : ""), 'class="form-control datetime" id="date" required="required"'); ?>
                </div>
            <?php } ?>

            <div class="form-group">
                <?= lang("reference", "reference"); ?>
                <?= form_input('reference', (isset($_POST['reference']) ? $_POST['reference'] : $exnumber), 'class="form-control tip" id="reference"'); ?>
            </div>

            <div class="form-group" id="div_danhmuc">
                <?= lang('category', 'category'); ?>
                <?php
                $ct[''] = lang('select').' '.lang('category');
                if ($categories) {
                    foreach ($categories as $category) {
                        $ct[$category->id] = $category->name;
                    }
                }
                ?>
                <?= form_dropdown('category', $ct, set_value('category'), 'class="form-control tip" id="category"'); ?>
            </div>
			 <div class="form-group">
                <?= lang("chi_doituong", "phan_loai_dt"); ?>
                <?php
                $doituong[''] = lang("select") . ' ' . lang("doituong");
				$doituong['0'] = "Đối tượng khác";
				$doituong['1'] = "Nhân viên";
				$doituong['2'] = "Khách hàng";
				$doituong['3'] = "Nhà cung cấp";
				$doituong['9'] = "Đối tác giao hàng";
                
                echo form_dropdown('phan_loai_dt', $doituong, (isset($_POST['phan_loai_dt']) ? $_POST['phan_loai_dt'] : ''), 'id="phan_loai_dt" class="form-control input-tip select" style="width:100%;" onchange="loadobjlhson()" required="required"');
                ?>
            </div>
			<div class="form-group hiddenlhson" id="div_suppliers_id">					
					<?php
					echo form_input('suppliers_id', (isset($_POST['suppliers_id']) ? $_POST['suppliers_id'] : ""), 'id="pppurchases_id" data-placeholder="' . lang("select") . ' ' . lang("suppliers") . '" class="form-control input-tip select2" style="width:100%;"');
					?>
			</div>
			<div class="clearfix"></div>
			<div class="form-group hiddenlhson" id="div_customer_id">					
					<?php
					echo form_input('customer_id', (isset($_POST['customer']) ? $_POST['customer'] : ""), 'id="slcustomeraddpt" data-placeholder="' . lang("select") . ' ' . lang("customer") . '" class="form-control input-tip select2" style="width:100%;"');
					?>
			</div>
			<div class="form-group hiddenlhson" id="div_doitac_id">					
					<?php
					echo form_input('doitac', (isset($_POST['doitac']) ? $_POST['doitac'] : ""), 'id="sldoitacaddpt" data-placeholder="' . lang("select") . ' ' . lang("đối tác") . '" class="form-control input-tip select2" style="width:100%;"');
					?>
			</div>
			<div class="clearfix"></div>
			<div class="form-group hiddenlhson" id="div_nhanvien_id">					
					<?php
					echo form_input('nhanvien_id', (isset($_POST['slnhanvien']) ? $_POST['slnhanvien'] : ""), 'id="slnhanvien" data-placeholder="' . lang("select") . ' ' . lang("users") . '" class="form-control input-tip select2" style="width:100%;"');
					?>
			</div>
			<div class="clearfix"></div>	
				 
            <div class="form-group" id="div_khohang" style="display:none">
                <?= lang("warehouse", "warehouse_add_ex"); ?>
                <?php
				$selected="";
                $wh[''] = lang("select") . ' ' . lang("warehouse");
                foreach ($warehouses as $warehouse) {
                    $wh[$warehouse->id] = $warehouse->name;
					$selected=$warehouse->id;
                }
                echo form_dropdown('warehouse', $wh,$selected, 'id="warehouse_add_ex" class="form-control input-tip select" style="width:100%;" required="required"');
                ?>
            </div>
            <div class="form-group">
                <?= lang("Tên", "c_name"); ?>
                <input name="c_name" type="text" id="c_name" value="" class="form-control c_name"
                       required="required"/>
            </div>
            <div class="form-group">
                <?= lang("Điện thoại", "c_phone"); ?>
                <input name="c_phone" type="text" id="c_phone" value="" class="form-control c_phone"
                       />
            </div>           
            <div class="form-group">
                <?= lang("Địa chỉ", "c_address"); ?>
                <?php echo form_textarea('c_address', (isset($_POST['c_address']) ? $_POST['c_address'] : ""), 'class="form-control skip" id="c_address"'); ?>
            </div>
            <div class="form-group">
                <?= lang("amount", "amount"); ?>
                <input name="amount" type="text" id="amount" value="" class="pa form-control kb-pad amount"
                       required="required"/>
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
                <?= lang("attachment", "attachment") ?>
                <input id="attachment" type="file" data-browse-label="<?= lang('browse'); ?>" name="userfile" data-show-upload="false" data-show-preview="false"
                       class="form-control file">
            </div>

            <div class="form-group">
                <?= lang("note", "note"); ?>
                <?php echo form_textarea('note', (isset($_POST['note']) ? $_POST['note'] : ""), 'class="form-control skip" id="note"'); ?>
            </div>
            <div class="form-group">
            	<input type="checkbox" checked="checked" class="checkbox" value="1" name="is_doanhthu" id="is_doanhthu" <?= $this->input->post('is_doanhthu') ? 'checked="checked"' : ''; ?>>
        		<?= lang('Hoạch toán vào kết quả hoạt động kinh doanh'); ?>
					
			</div>
			<div class="clearfix"></div>
        </div>
        <div class="modal-footer">
			<button type="submit" class="btn btn-primary btncls" name="add_expense" id="add_expense">
				<i class="fa fa-save"></i>
				<?= lang('add_expense');?>
			</button>
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
		$(document).on('change', '.paid_by', function () {
            var p_val = $(this).val();
            localStorage.setItem('paid_by', p_val);
            $('#rpaidby').val(p_val);
            if (p_val == 'cash') {
                $('.pcheque_1').hide();
                $('.pcc_1').hide();
                $('.pcash_1').show();
                $('#amount_1').focus();
            } else if (p_val == 'CC') {
                $('.pcheque_1').hide();
                $('.pcash_1').hide();
                $('.pcc_1').show();
                $('#pcc_no_1').focus();
            } else if (p_val == 'Cheque') {
                $('.pcc_1').hide();
                $('.pcash_1').hide();
                $('.pcheque_1').show();
                $('#cheque_no_1').focus();
            } else {
                $('.pcheque_1').hide();
                $('.pcc_1').hide();
                $('.pcash_1').hide();
            }
        });
        $('#pcc_no_1').change(function (e) {
            var pcc_no = $(this).val();
            localStorage.setItem('pcc_no_1', pcc_no);
            var CardType = null;
            var ccn1 = pcc_no.charAt(0);
            if (ccn1 == 4)
                CardType = 'Visa';
            else if (ccn1 == 5)
                CardType = 'MasterCard';
            else if (ccn1 == 3)
                CardType = 'Amex';
            else if (ccn1 == 6)
                CardType = 'Discover';
            else
                CardType = 'Visa';

            $('#pcc_type_1').select2("val", CardType);
        });
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
		}).on;
		$(".select2").change(function(){
			var str=$(this).select2('data').text;
			var sp=str.split('-');
			$("#c_name").val(sp[0]);
			if (sp.length>0) {
				$("#c_phone").val(sp[1]);
			}			
		})
		$("#phan_loai_dt").change(function(){
			$("#c_name").val('');
			$("#c_phone").val('');
			$("#c_address").val('');					
		});
		$('#sldoitacaddpt').select2({
			minimumInputLength: 1,
			allowClear:true,
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
		 $('#pppurchases_id').select2({
				minimumInputLength: 1,
				allowClear:true,
				ajax: {
					url: site.base_url + "suppliers/suggestions",
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
		 $('#slnhanvien').select2({
				minimumInputLength: 1,
				allowClear:true,
				ajax: {
					url: site.base_url + "customers/suggestionsNhanvien",
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
	function loadobjlhson(){
		var selected=jQuery("#phan_loai_dt").val();
		
		if(selected==3){
			jQuery("#div_suppliers_id").css("display","block");
			jQuery("#suppliers_id").attr("required","required");
			
			//jQuery("#div_khohang").css("display","none");
			jQuery("#warehouse_add_ex").removeAttr("required");
			jQuery("#warehouse_add_ex").prop('required',false);			
			
			jQuery("#div_danhmuc").css("display","none");
			jQuery("#category").removeAttr("required");
			
			jQuery("#div_customer_id").css("display","none");
			jQuery("#customer_id").removeAttr("required");
			
			jQuery("#div_nhanvien_id").css("display","none");
			jQuery("#nhanvien_id").removeAttr("required");
			
			jQuery("#div_doitac_id").css("display","none");
			jQuery("#doitac").removeAttr("required");
			
		}else if(selected==2){
			jQuery("#div_customer_id").css("display","block");
			jQuery("#customer_id").attr("required","required");
			
			jQuery("#div_suppliers_id").css("display","none");
			jQuery("#suppliers_id").removeAttr("required");
			
			jQuery("#div_nhanvien_id").css("display","none");
			jQuery("#nhanvien_id").removeAttr("required");
			
			jQuery("#div_khohang").css("display","none");
			jQuery("#warehouse_add_ex").removeAttr("required");
			
			jQuery("#div_doitac_id").css("display","none");
			jQuery("#doitac").removeAttr("required");
		}else if(selected==1){
			jQuery("#div_nhanvien_id").css("display","block");
			jQuery("#nhanvien_id").attr("required","required");
			
			jQuery("#div_suppliers_id").css("display","none");
			jQuery("#suppliers_id").removeAttr("required");
			
			jQuery("#div_customer_id").css("display","none");
			jQuery("#customer_id").removeAttr("required");
			
			jQuery("#div_khohang").css("display","none");
			jQuery("#warehouse_add_ex").removeAttr("required");
			
			jQuery("#div_doitac_id").css("display","none");
			jQuery("#doitac").removeAttr("required");
		}else if(selected==9){
			jQuery("#div_doitac_id").css("display","block");
			jQuery("#doitac").attr("required","required");
									
			jQuery("#div_suppliers_id").css("display","none");
			jQuery("#suppliers_id").removeAttr("required");
			
			jQuery("#div_khohang").css("display","none");
			jQuery("#warehouse_add_ex").attr("required");
			jQuery("#warehouse_add_ex").prop('required',true);			
			
			jQuery("#div_danhmuc").css("display","block");
			jQuery("#category").removeAttr("required");
			
			jQuery("#div_customer_id").css("display","none");
			jQuery("#customer_id").removeAttr("required");
			
			jQuery("#div_nhanvien_id").css("display","none");
			jQuery("#nhanvien_id").removeAttr("required");
			
		}else{
			
			jQuery("#div_nhanvien_id").css("display","none");
			jQuery("#nhanvien_id").removeAttr("required");
			
			jQuery("#div_suppliers_id").css("display","none");
			jQuery("#suppliers_id").removeAttr("required");
			
			jQuery("#div_customer_id").css("display","none");
			jQuery("#customer_id").removeAttr("required");
			
			jQuery("#div_khohang").css("display","none");
			jQuery("#warehouse_add_ex").attr("required","required");
			jQuery("#warehouse_add_ex").prop('required',true);
			
			jQuery("#div_danhmuc").css("display","block");
			jQuery("#category").attr("required","required");
			
			jQuery("#div_doitac_id").css("display","none");
			jQuery("#doitac").removeAttr("required");
			
		}			
	}
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
input#is_doanhthu {float: left;width: auto;line-height: 24px;margin: 0px;margin-right: 10px;cursor: pointer;}
</style>
