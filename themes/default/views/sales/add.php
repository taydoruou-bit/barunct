<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script type="text/javascript">
$(document).ready(function() {
				$('form').preventDoubleSubmission();
			});
    var count = 1,count_tra=1, an = 1,an_tra=1, product_variant = 0,product_variant_tra=0, DT = <?= $Settings->default_tax_rate ?>,
        product_tax = 0, invoice_tax = 0, product_discount = 0, order_discount = 0, total_discount = 0, total = 0, allow_discount = <?= ($Owner || $Admin || $this->session->userdata('allow_discount')) ? 1 : 0; ?>,
        tax_rates = <?php echo json_encode($tax_rates); ?>;
        var sub_product=<?php echo json_encode($sub_product); ?>;
        var khuyenmai_main=<?php echo json_encode($khuyenmai_main); ?>;
        var main_product=<?php echo json_encode($main_product); ?>;
    //var audio_success = new Audio('<?=$assets?>sounds/sound2.mp3');
    //var audio_error = new Audio('<?=$assets?>sounds/sound3.mp3');
    $(document).ready(function () {
        if (localStorage.getItem('remove_slls')) {
            if (localStorage.getItem('slitems')) {
                localStorage.removeItem('slitems');
            }
			
			if (localStorage.getItem('slitems_tra')) {
                localStorage.removeItem('slitems_tra');
            }
            if (localStorage.getItem('sldiscount')) {
                localStorage.removeItem('sldiscount');
            }
			if (localStorage.getItem('sldiscount_tra')) {
                localStorage.removeItem('sldiscount_tra');
            }
            if (localStorage.getItem('sltax2')) {
                localStorage.removeItem('sltax2');
            }
            if (localStorage.getItem('slref')) {
                localStorage.removeItem('slref');
            }
            if (localStorage.getItem('slshipping')) {
                localStorage.removeItem('slshipping');
            }
            if (localStorage.getItem('slwarehouse')) {
                localStorage.removeItem('slwarehouse');
            }
            if (localStorage.getItem('slnote')) {
                localStorage.removeItem('slnote');
            }
            if (localStorage.getItem('slinnote')) {
                localStorage.removeItem('slinnote');
            }
            if (localStorage.getItem('slcustomer')) {
                localStorage.removeItem('slcustomer');
            }
            if (localStorage.getItem('slbiller')) {
                localStorage.removeItem('slbiller');
            }
            if (localStorage.getItem('slcurrency')) {
                localStorage.removeItem('slcurrency');
            }
            if (localStorage.getItem('sldate')) {
                localStorage.removeItem('sldate');
            }
            if (localStorage.getItem('slsale_status')) {
                localStorage.removeItem('slsale_status');
            }
            if (localStorage.getItem('slpayment_status')) {
                localStorage.removeItem('slpayment_status');
            }
            if (localStorage.getItem('paid_by')) {
                localStorage.removeItem('paid_by');
            }
            if (localStorage.getItem('amount_1')) {
                localStorage.removeItem('amount_1');
            }
            if (localStorage.getItem('paid_by_1')) {
                localStorage.removeItem('paid_by_1');
            }
            if (localStorage.getItem('pcc_holder_1')) {
                localStorage.removeItem('pcc_holder_1');
            }
            if (localStorage.getItem('pcc_type_1')) {
                localStorage.removeItem('pcc_type_1');
            }
            if (localStorage.getItem('pcc_month_1')) {
                localStorage.removeItem('pcc_month_1');
            }
            if (localStorage.getItem('pcc_year_1')) {
                localStorage.removeItem('pcc_year_1');
            }
            if (localStorage.getItem('pcc_no_1')) {
                localStorage.removeItem('pcc_no_1');
            }
            if (localStorage.getItem('cheque_no_1')) {
                localStorage.removeItem('cheque_no_1');
            }
            if (localStorage.getItem('payment_note_1')) {
                localStorage.removeItem('payment_note_1');
            }
            if (localStorage.getItem('slpayment_term')) {
                localStorage.removeItem('slpayment_term');
            }
            if (localStorage.getItem('custom_fields')) {
    localStorage.removeItem('custom_fields');
}
            localStorage.removeItem('remove_slls');
        }
        <?php if($quote_id) { ?>
        // localStorage.setItem('sldate', '<?= $this->sma->hrld($quote->date) ?>');
        localStorage.setItem('slcustomer', '<?= $quote->customer_id ?>');
        localStorage.setItem('slbiller', '<?= $quote->biller_id ?>');
        localStorage.setItem('slwarehouse', '<?= $quote->warehouse_id ?>');
        localStorage.setItem('slnote', '<?= str_replace(array("\r", "\n"), "", $this->sma->decode_html($quote->note)); ?>');
        localStorage.setItem('sldiscount', '<?= $quote->order_discount_id ?>');
        localStorage.setItem('sltax2', '<?= $quote->order_tax_id ?>');
        localStorage.setItem('slshipping', '<?= $quote->shipping ?>');
        localStorage.setItem('slitems', JSON.stringify(<?= $quote_items; ?>));
        <?php if (!empty($quote->custom_fields)) {
    $custom = json_decode($quote->custom_fields);
    if (isset($custom->fields) && is_array($custom->fields)) {
        $customData = array();
        foreach ($custom->fields as $field) {
            $customData[$field->name] = isset($field->value) ? $field->value : '0';
        }
        ?>
        localStorage.setItem('custom_fields', '<?= json_encode($customData); ?>');
        <?php
    }
} ?>

        <?php } ?>
        <?php if($this->input->get('customer')) { ?>
        if (!localStorage.getItem('slitems')) {
            localStorage.setItem('slcustomer', <?=$this->input->get('customer');?>);
        }
        <?php } ?>
        <?php if ($Owner || $Admin) { ?>

        if (!localStorage.getItem('sldate')) {
            $("#sldate").datetimepicker({
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
        }
		
		
        $(document).on('change', '#sldate', function (e) {
            localStorage.setItem('sldate', $(this).val());
        });
        if (sldate = localStorage.getItem('sldate')) {
            $('#sldate').val(sldate);
        }
        <?php } ?>
        if(!localStorage.getItem('slcustomer')||localStorage.getItem('slcustomer')==null){
            localStorage.setItem('slcustomer', <?php echo $pos_settings->default_customer;?>);
            
            //$('#slcustomer').select2('data', {id: '<?php echo $pos_settings->default_customer;?>', text: 'Khách lẻ'});
        }
        
        $(document).on('change', '#slbiller', function (e) {
            localStorage.setItem('slbiller', $(this).val());
        });
        if (slbiller = localStorage.getItem('slbiller')) {
            $('#slbiller').val(slbiller);
        }
        if (!localStorage.getItem('slref')) {
            localStorage.setItem('slref', '<?=$slnumber?>');
        }
        if (!localStorage.getItem('sltax2')) {
            localStorage.setItem('sltax2', <?=$Settings->default_tax_rate2;?>);
        }
        ItemnTotals();
        $('.bootbox').on('hidden.bs.modal', function (e) {
            $('#add_item').focus();
        });
        $("#add_item").autocomplete({
            source: function (request, response) {
                if (!$('#slcustomer').val()) {
                    $('#add_item').val('').removeClass('ui-autocomplete-loading');
                    bootbox.alert('Chọn khách hàng trước tiên');
                    $('#add_item').focus();
                    return false;
                }
                $.ajax({
                    type: 'get',
                    url: '<?= site_url('sales/suggestions'); ?>',
                    dataType: "json",
                    data: {
                        term: request.term,
                        warehouse_id: $("#slwarehouse").val(),
                        customer_id: $("#slcustomer").val()
                    },
                    success: function (data) {
                        $(this).removeClass('ui-autocomplete-loading');
                        response(data);
                    }
                });
            },
            minLength: 1,
            autoFocus: false,
            delay: 250,
            response: function (event, ui) {
                if ($(this).val().length >= 16 && ui.content[0].id == 0) {
                    bootbox.alert('<?= lang('no_match_found') ?>', function () {
                        $('#add_item').focus();
                    });
                    $(this).removeClass('ui-autocomplete-loading');
                    $(this).removeClass('ui-autocomplete-loading');
                    $(this).val('');
                }
                else if (ui.content.length ==1 && ui.content[0].id != 0) {
					if($(this).val().length >= 6){
						ui.item = ui.content[0];/*lhson code*/
						$(this).data('ui-autocomplete')._trigger('select', 'autocompleteselect', ui);
						$(this).autocomplete('close');
						$(this).removeClass('ui-autocomplete-loading');
					}
                }
                else if (ui.content.length == 1 && ui.content[0].id == 0) {
                    // bootbox.alert('<?= lang('no_match_found1') ?>', function () {
                        // $('#add_item').focus();
                    // });
                    // $(this).removeClass('ui-autocomplete-loading');
                    // $(this).val('');
                }
            },
            select: function (event, ui) {
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
		$("#add_item_tra").autocomplete({
            source: function (request, response) {
                if (!$('#slcustomer').val()) {
                    $('#add_item_tra').val('').removeClass('ui-autocomplete-loading');
                    bootbox.alert('Chọn khách hàng trước tiên');
                    $('#add_item_tra').focus();
                    return false;
                }
                $.ajax({
                    type: 'get',
                    url: '<?= site_url('sales/suggestions'); ?>',
                    dataType: "json",
                    data: {
                        term: request.term,
                        warehouse_id: $("#slwarehouse").val(),
                        customer_id: $("#slcustomer").val()
                    },
                    success: function (data) {
                        $(this).removeClass('ui-autocomplete-loading');
                        response(data);
                    }
                });
            },
            minLength: 1,
            autoFocus: false,
            delay: 250,
            response: function (event, ui) {
                if ($(this).val().length >= 16 && ui.content[0].id == 0) {
                    bootbox.alert('<?= lang('no_match_found') ?>', function () {
                        $('#add_item_tra').focus();
                    });
                    $(this).removeClass('ui-autocomplete-loading');
                    $(this).removeClass('ui-autocomplete-loading');
                    $(this).val('');
                }
                else if (ui.content.length == 1 && ui.content[0].id != 0) {
					if($(this).val().length >= 10){
						ui.item = ui.content[0];/*lhson code*/
						$(this).data('ui-autocomplete')._trigger('select', 'autocompleteselect', ui);
						$(this).autocomplete('close');
						$(this).removeClass('ui-autocomplete-loading');
					}
                }
                else if (ui.content.length == 1 && ui.content[0].id == 0) {
                    bootbox.alert('<?= lang('no_match_found') ?>', function () {
                        $('#add_item_tra').focus();
                    });
                    $(this).removeClass('ui-autocomplete-loading');
                    $(this).val('');
                }
            },
            select: function (event, ui) {
                event.preventDefault();
                if (ui.item.id !== 0) {
                    var row = add_invoice_item_tra(ui.item);
					console.log(row);
                    if (row)
                        $(this).val('');
                } else {
                    bootbox.alert('<?= lang('no_match_found') ?>');
                }
            }
        });
         $("#km_item_change").autocomplete({
            source: function (request, response) { 
               
                $.ajax({
                    type: 'get',
                    url: '<?= site_url('sales/suggestions'); ?>',
                    dataType: "json",
                    data: {
                        term: request.term,
                        warehouse_id: $("#slwarehouse").val(),
                        customer_id: $("#slcustomer").val()
                    },
                    success: function (data) {
                        $(this).removeClass('ui-autocomplete-loading');
                        response(data);
                    }
                });
            },
            minLength: 1,
            autoFocus: false,
            delay: 250,
            response: function (event, ui) {
                if ($(this).val().length >= 16 && ui.content[0].id == 0) {
                    bootbox.alert('<?= lang('no_match_found') ?>', function () {
                        $('#km_item_change').focus();
                    });
                    $(this).removeClass('ui-autocomplete-loading');
                    $(this).removeClass('ui-autocomplete-loading');
                    $(this).val('');
                }
                else if (ui.content.length ==1 && ui.content[0].id != 0) {
                    if($(this).val().length >= 10){
                        ui.item = ui.content[0];/*lhson code*/
                        $(this).data('ui-autocomplete')._trigger('select', 'autocompleteselect', ui);
                        $(this).autocomplete('close');
                        $(this).removeClass('ui-autocomplete-loading');
                    }
                }
                else if (ui.content.length == 1 && ui.content[0].id == 0) {
                    // bootbox.alert('<?= lang('no_match_found1') ?>', function () {
                        // $('#km_item_change').focus();
                    // });
                    // $(this).removeClass('ui-autocomplete-loading');
                    // $(this).val('');
                }
            },
            select: function (event, ui) {
                event.preventDefault();
                if (ui.item.id !== 0) {
                           
                    var olid=$('#MdKhuyenMai #km_edit_id').val();

                    itemkm = slitems[olid];      

                    slitems[olid].label= "[KHUYẾN MÃI]"+ui.item.row.name; 
                    slitems[olid].item_id = ui.item.row.id; 

                    slitems[olid].row.id = ui.item.row.id;
                    slitems[olid].row.name = ui.item.row.name; 
                    slitems[olid].row.qty = parseFloat(itemkm.row.qty); 
                    slitems[olid].row.code = itemkm.row.code; 
                    slitems[olid].row.quantity = parseFloat(itemkm.row.qty); 
                    slitems[olid].row.base_quantity = parseFloat(itemkm.row.base_quantity); 
                    slitems[olid].row.real_unit_price = itemkm.row.price; 
                    slitems[olid].row.unit_price = itemkm.row.unit_price;   
                    slitems[olid].row.unit = ui.item.row.unit; 
                    slitems[olid].row.sale_unit = ui.item.row.sale_unit; 
                    slitems[olid].row.purchase_unit = ui.item.row.purchase_unit; 
                    
                    slitems[olid].row.tax_rate = ui.item.row.tax_rate; 
                    slitems[olid].row.tax_rate = ui.item.row.tax_rate; 
                    slitems[olid].row.serial = ui.item.row.serial;    

                    localStorage.setItem('slitems', JSON.stringify(slitems));
                    loadItems();
                    $('#MdKhuyenMai #km_edit_id').val('');
                     $('#prKhuyenMai').text('');
                     $('#MdKhuyenMai').modal('hide');
                    if (row)
                        $(this).val('');
                } else {
                    bootbox.alert('<?= lang('no_match_found') ?>');
                }
            }
        });
        $(document).on('change', '#gift_card_no', function () {
            var cn = $(this).val() ? $(this).val() : '';
            if (cn != '') {
                $.ajax({
                    type: "get", async: false,
                    url: site.base_url + "sales/validate_gift_card/" + cn,
                    dataType: "json",
                    success: function (data) {
                        if (data === false) {
                            $('#gift_card_no').parent('.form-group').addClass('has-error');
                            bootbox.alert('<?=lang('incorrect_gift_card')?>');
                        } else if (data.customer_id !== null && data.customer_id !== $('#slcustomer').val()) {
                            $('#gift_card_no').parent('.form-group').addClass('has-error');
                            bootbox.alert('<?=lang('gift_card_not_for_customer')?>');

                        } else {
                            $('#gc_details').html('<small>Card No: ' + data.card_no + '<br>Value: ' + data.value + ' - Balance: ' + data.balance + '</small>');
                            $('#gift_card_no').parent('.form-group').removeClass('has-error');
                        }
                    }
                });
            }
        });
		
    });
</script>
<style>
input#p_gia_si {
    float: left;
    width: 75%;
}

.btn.btn-giasiapply {
    float: right;
    width: 24%;
    padding: 2px;
}
</style>
<div class="box">
	<?php
	$attrib = array('data-toggle' => 'validator', 'role' => 'form');
	echo form_open_multipart("sales/add", $attrib);
	if ($quote_id) {
		echo form_hidden('quote_id', $quote_id);
	}
	?>
	
    <div class="box-header">
        
		 <ul id="myTab" class="nav nav-tabs lhson_add_sp_tab">
					<li class=""><a href="#overview" class="tab-grey"><i class="fa-fw fa fa-plus"></i><?= lang('add_sale'); ?></a></li>
					<li class=""><a href="#trahang" class="tab-grey">Trả hàng</a></li>
		</ul>
		<div class="main-task-lhson themdonhang">
			<button type="submit" class="btn btn-primary btncls" name="add_sale" id="add_sale">
				<i class="fa fa-save"></i>
				Thêm đơn hàng
			</button>
			<button type="button" class="btn btn-default btncls" id="reset">
				<i class="fa fa-refresh"></i>
				<?= lang('reset') ?>
			</button>
		</div>	
    </div>
    <div class="box-content lhson_them_donhang">
        <div class="row no-padding-lhson">
            <div class="col-lg-12 tab-content">                
                <div class="row tab-pane fade in" id="overview">
                    <div class="col-lg-12">
						<div class="col-md-8 no-padding-lhson">
							<div class="col-md-12" id="sticker">
									<div class="form-group" style="margin-bottom:0;">
										<div class="input-group wide-tip">
											<?php echo form_input('add_item', '', 'class="form-control input-lg" id="add_item" placeholder="' . lang("add_product_to_order") . '"'); ?>
											<?php if ($Owner || $Admin || $GP['products-add']) { ?>
											<div class="input-group-addon" style="padding-left: 10px; padding-right: 10px;">
												<a href="#" id="addManually" class="tip" title="<?= lang('add_product_manually') ?>">
													<i class="fa fa-2x fa-plus-circle addIcon" id="addIcon"></i>
												</a>
											</div>
											<?php } if ($Owner || $Admin || $GP['sales-add_gift_card']) { ?>
											<div class="input-group-addon" style="padding-left: 10px; padding-right: 10px;">
												<a href="#" id="sellGiftCard" class="tip" title="<?= lang('sell_gift_card') ?>">
												   <i class="fa fa-2x fa-credit-card addIcon" id="addIcon"></i>
												</a>
											</div>
											<?php } ?>
										</div>
									</div>
							</div>

							<div class="col-md-12">
								<div class="control-group table-group">
									<div class="controls table-controls">
										<table id="slTable" class="table items table-striped table-bordered table-condensed table-hover sortable_table">
											<thead>
											<tr>
												<th class="col-md-4"><?= lang('product') . ' (' . lang('code') .' - '.lang('name') . ')'; ?></th>
												<?php
												if ($Settings->product_serial) {
													echo '<th class="col-md-2">' . lang("serial_no") . '</th>';
												}
												?>
												<th class="col-md-1">Giá</th>
												<th class="col-md-1"><?= lang("quantity"); ?></th>
												<?php
												if ($Settings->product_discount && ($Owner || $Admin || $this->session->userdata('allow_discount'))) {
													echo '<th class="col-md-1">Giảm</th>';
												}
												?>
												<?php
												if ($Settings->tax1) {
													echo '<th class="col-md-1">Thuế</th>';
												}
												?>
												<th>
													<?= lang("subtotal"); ?>
													(<span class="currency"><?= $default_currency->code ?></span>)
												</th>
												<th style="width: 30px !important; text-align: center;">
													<i class="fa fa-trash-o" style="opacity:0.5; filter:alpha(opacity=50);"></i>
												</th>
											</tr>
											</thead>
											<tbody></tbody>
											<tfoot></tfoot>
										</table>
									</div>
								</div>
							</div>
						</div>
						<div class="col-md-4 lhson_add_donhang">
							<div class="col-md-4">
								<?php if ($Owner || $Admin || !$this->session->userdata('warehouse_id')) { ?>
									<div class="col-md-4">
										<div class="form-group">
											<?php
											$wh[''] = '';
											foreach ($warehouses as $warehouse) {
												$wh[$warehouse->id] = $warehouse->name;
											}
											echo form_dropdown('warehouse', $wh, (isset($_POST['warehouse']) ? $_POST['warehouse'] : $Settings->default_warehouse), 'id="slwarehouse" class="form-control input-tip select" data-placeholder="' . lang("select") . ' ' . lang("warehouse") . '" required="required" style="width:100%;" ');
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
										<div class="input-group">
											<?php
											echo form_input('customer', (isset($_POST['customer']) ? $_POST['customer'] : $pos_settings->default_customer), 'id="slcustomer" data-placeholder="' . lang("select") . ' ' . lang("customer") . '" required="required" class="form-control input-tip" style="width:100%;"');
											?>
											<div class="input-group-addon no-print" style="padding: 2px 8px; border-left: 0;">
												<a href="#" id="toogle-customer-read-attr" class="external">
													<i class="fa fa-pencil" id="addIcon" style="font-size: 1.2em;"></i>
												</a>
											</div>
											<div class="input-group-addon no-print" style="padding: 2px 7px; border-left: 0;">
												<a href="#" id="view-customer" class="external" data-toggle="modal" data-target="#myModal">
													<i class="fa fa-eye" id="addIcon" style="font-size: 1.2em;"></i>
												</a>
											</div>
											<?php if ($Owner || $Admin || $GP['customers-add']) { ?>
											<div class="input-group-addon no-print" style="padding: 2px 8px;">
												<a href="<?= site_url('customers/add'); ?>" id="add-customer"class="external" data-toggle="modal" data-target="#myModal">
													<i class="fa fa-plus-circle" id="addIcon"  style="font-size: 1.2em;"></i>
												</a>
											</div>
											<?php } ?>
										</div>
									</div>
								</div>

							</div>
							<?php if ($Owner || $Admin) { ?>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang("date", "sldate"); ?>
                                    <?php echo form_input('date', (isset($_POST['date']) ? $_POST['date'] : ""), 'class="form-control input-tip datetime" id="sldate" required="required"'); ?>
                                </div>
                            </div>
							<?php } ?>

							<div class="col-md-4">
    <div class="form-group">
        <?= lang("reference_no", "slref"); ?>
        <?php 
        // ✅ LẤYMÃ TỰ ĐỀ XUẤT CỦA HỆ THỐNG
        $ref_value = $slnumber;
        // ✅ NẾU CÓ quote_reference_no, DÙNG MÃ CỦA BÁOCHỈ
        if (isset($quote_reference_no) && !empty($quote_reference_no)) {
            $ref_value = $quote_reference_no;
        }
        echo form_input('reference_no', (isset($_POST['reference_no']) ? $_POST['reference_no'] : $ref_value), 'class="form-control input-tip" id="slref"'); 
        ?>
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
										echo form_dropdown('biller', $bl, (isset($_POST['biller']) ? $_POST['biller'] : $Settings->default_biller), 'id="slbiller" data-placeholder="' . lang("select") . ' ' . lang("biller") . '" required="required" class="form-control input-tip select" style="width:100%;"');
										?>
									</div>
								</div>
							<?php } else {
								$biller_input = array(
									'type' => 'hidden',
									'name' => 'biller',
									'id' => 'slbiller',
									'value' => $this->session->userdata('biller_id'),
								);

								echo form_input($biller_input);
							} ?>

							<div class="clearfix"></div>
							
							<?php if ($Settings->tax2) { ?>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?php
                                    $tr[""] = "";
                                    foreach ($tax_rates as $tax) {
                                        $tr[$tax->id] = $tax->name;
                                    }
                                    echo form_dropdown('order_tax', $tr, (isset($_POST['order_tax']) ? $_POST['order_tax'] : $Settings->default_tax_rate2), 'id="sltax2" data-placeholder="' . lang("select") . ' ' . lang("order_tax") . '" class="form-control input-tip select" style="width:100%;"');
                                    ?>
                                </div>
                            </div>
                        <?php } ?>

                       <?php if ($Owner || $Admin || $this->session->userdata('allow_discount')) { ?>
    <div class="col-md-4">
        <div class="form-group">
            <?= lang("order_discount", "sldiscount"); ?>
            <?php echo form_input('order_discount', '', 'class="form-control input-tip" id="sldiscount"'); ?>
        </div>
    </div>
<?php } ?>
						 <div class="col-md-4">
							<div class="form-group" id="div_doitac_id">
								<?= lang("Đối tác giao hàng", "doitac"); ?>
																	
									<?php                                     
                                    
                                    $dt["0"] = "Chọn đối tác";
                                    foreach ($doitacs as $tax) { 
                                        $dt[$tax->id] = $tax->name; 
                                    }
                                    echo form_dropdown('doitac', $dt, (isset($_POST['doitac']) ? $_POST['doitac'] : ""), 'id="doitac" data-placeholder="' . lang("select") . ' ' . lang("doitac") . '" class="form-control input-tip select" style="width:100%;"');
                                    ?>
							</div>
						</div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang("shipping", "slshipping"); ?>
                                <?php echo form_input('shipping', '', 'class="form-control input-tip" id="slshipping"'); ?>

                            </div>
                        </div>
                         <div class="col-md-6">
    <div class="form-group">
        <?= lang("Phí nhà máy", "fee_nhamay"); ?>
        <?php echo form_input('custom_fee_nhamay', '', 'class="form-control input-tip custom-field" id="custom_fee_nhamay"'); ?>
    </div>
</div>

<div class="col-md-6">
    <div class="form-group">
        <?= lang("Phí phụ kiện", "fee_phukien"); ?>
        <?php echo form_input('custom_fee_phukien', '', 'class="form-control input-tip custom-field" id="custom_fee_phukien"'); ?>
    </div>
</div>

<div class="col-md-6">
    <div class="form-group">
        <?= lang("Phí lắp đặt", "fee_lapdat"); ?>
        <?php echo form_input('custom_fee_lapdat', '', 'class="form-control input-tip custom-field" id="custom_fee_lapdat"'); ?>
    </div>
</div>

<div class="col-md-6">
    <div class="form-group">
        <?= lang("Chành xe", "fee_chanhxe"); ?>
        <?php echo form_input('custom_fee_chanhxe', '', 'class="form-control input-tip custom-field" id="custom_fee_chanhxe"'); ?>
    </div>
</div>

<!-- Hidden field để lưu JSON -->
<input type="hidden" name="custom_fields" id="custom_fields_json" value="">           
                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang("document", "document") ?>
                                <input id="document" type="file" data-browse-label="<?= lang('browse'); ?>" name="document" data-show-upload="false"
                                       data-show-preview="false" class="form-control file">
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
    <?= lang("sale_status", "slsale_status"); ?>
    <?php $sst = array('completed' => lang('completed'), 'pending' => lang('pending'));
    echo form_dropdown('sale_status', $sst, 'pending', 'class="form-control input-tip" required="required" id="slsale_status"'); ?>
</div>
                        </div>
                        <div class="col-md-4" style="display:none">
                            <div class="form-group">
                                <?= lang("payment_term", "slpayment_term"); ?>
                                <?php echo form_input('payment_term', '', 'class="form-control tip" data-trigger="focus" data-placement="top" title="' . lang('payment_term_tip') . '" id="slpayment_term"'); ?>

                            </div>
                        </div>
                        <?php if ($Owner || $Admin || $GP['sales-payments']) { ?>
                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang("payment_status", "slpayment_status"); ?>
                                <?php $pst = array('pending' => lang('pending'), 'due' => lang('due'), 'partial' => lang('partial'), 'paid' => lang('paid'));
                                echo form_dropdown('payment_status', $pst, '', 'class="form-control input-tip" required="required" id="slpayment_status"'); ?>

                            </div>
                        </div>
                        <?php 
                        } else {
                            echo form_hidden('payment_status', 'pending');
                        }
                        ?>
                        <div class="clearfix"></div>

                        <div id="payments" style="display: none;">
                            <div class="col-md-12 no-padding-lhson">
                                <div class="well well-sm well_1">
                                    <div class="col-md-12">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <?= lang("payment_reference_no", "payment_reference_no"); ?>
                                                    <?= form_input('payment_reference_no', (isset($_POST['payment_reference_no']) ? $_POST['payment_reference_no'] : $payment_ref), 'class="form-control tip" id="payment_reference_no"'); ?>
                                                </div>
                                            </div>
                                            <div class="col-sm-4">
                                                <div class="payment">
                                                    <div class="form-group ngc">
                                                        <?= lang("amount", "amount_1"); ?>
                                                        <input name="amount-paid" type="text" id="amount_1"
                                                               class="pa form-control kb-pad amount"/>
                                                    </div>
                                                    <div class="form-group gc" style="display: none;">
                                                        <?= lang("gift_card_no", "gift_card_no"); ?>
                                                        <input name="gift_card_no" type="text" id="gift_card_no"
                                                               class="pa form-control kb-pad"/>

                                                        <div id="gc_details"></div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-sm-4">
                                                <div class="form-group">
                                                    <select name="paid_by" id="paid_by_1" class="form-control paid_by">
                                                        <?= $this->sma->paid_opts(); ?>
                                                    </select>
                                                </div>
                                            </div>

                                        </div>
                                        <div class="clearfix"></div>
                                        <div class="pcc_1" style="display:none;">
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <input name="pcc_no" type="text" id="pcc_no_1"
                                                               class="form-control" placeholder="<?= lang('cc_no') ?>"/>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <input name="pcc_holder" type="text" id="pcc_holder_1"
                                                               class="form-control"
                                                               placeholder="<?= lang('cc_holder') ?>"/>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <select name="pcc_type" id="pcc_type_1"
                                                                class="form-control pcc_type"
                                                                placeholder="<?= lang('card_type') ?>">
                                                            <option value="Visa"><?= lang("Visa"); ?></option>
                                                            <option
                                                                value="MasterCard"><?= lang("MasterCard"); ?></option>
                                                            <option value="Amex"><?= lang("Amex"); ?></option>
                                                            <option value="Discover"><?= lang("Discover"); ?></option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <input name="pcc_month" type="text" id="pcc_month_1"
                                                               class="form-control" placeholder="<?= lang('month') ?>"/>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">

                                                        <input name="pcc_year" type="text" id="pcc_year_1"
                                                               class="form-control" placeholder="<?= lang('year') ?>"/>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">

                                                        <input name="pcc_ccv" type="text" id="pcc_cvv2_1"
                                                               class="form-control" placeholder="<?= lang('cvv2') ?>"/>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="pcheque_1" style="display:none;">
                                            <div class="form-group"><?= lang("cheque_no", "cheque_no_1"); ?>
                                                <input name="cheque_no" type="text" id="cheque_no_1"
                                                       class="form-control cheque_no"/>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <?= lang('payment_note', 'payment_note_1'); ?>
                                            <textarea name="payment_note" id="payment_note_1"
                                                      class="pa form-control kb-text payment_note"></textarea>
                                        </div>
                                    </div>
                                    <div class="clearfix"></div>
                                </div>
                            </div>
                        </div>

                        <input type="hidden" name="total_items" value="" id="total_items" required="required"/>

                        <div class="row" id="bt">
                            <div class="col-md-12">
								<div class="form-group">
									<?= lang("sale_note", "slnote"); ?>
									<?php echo form_textarea('note', (isset($_POST['note']) ? $_POST['note'] : ""), 'class="form-control" id="slnote" style="margin-top: 10px; height: 100px;"'); ?>

								</div>
								<div class="form-group">
									<?= lang("staff_note", "slinnote"); ?>
									<?php echo form_textarea('staff_note', (isset($_POST['staff_note']) ? $_POST['staff_note'] : ""), 'class="form-control" id="slinnote" style="margin-top: 10px; height: 100px;"'); ?>

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
								<?php if ($Owner || $Admin || $this->session->userdata('allow_discount')) { ?>
								<td><?= lang('order_discount') ?> <span class="totals_val pull-right" id="tds">0.00</span></td>
								<?php }?>
								<?php if ($Settings->tax2) { ?>
									<td><?= lang('order_tax') ?> <span class="totals_val pull-right" id="ttax2">0.00</span></td>
								<?php } ?>
								<td><?= lang('shipping') ?> <span class="totals_val pull-right" id="tship">0.00</span></td>
								<td><?= lang('grand_total') ?> <span class="totals_val pull-right" id="gtotal">0.00</span></td>
							</tr>
						</table>
					</div>
                </div>
				<div class="row tab-pane" id="trahang">
					<div class="col-lg-12">
						<div class="col-md-12 no-padding-lhson">
							<div class="col-md-12" id="sticker_tra">
									<div class="form-group" style="margin-bottom:0;">
										<div class="input-group wide-tip">
											<?php echo form_input('add_item_tra', '', 'class="form-control input-lg" id="add_item_tra" placeholder="' . lang("add_product_to_order") . '"'); ?>											
										</div>
									</div>
							</div>

							<div class="col-md-12">
								<div class="control-group table-group">
									<div class="controls table-controls">
										<table id="slTable_tra" class="table items table-striped table-bordered table-condensed table-hover sortable_table">
											<thead>
											<tr>
												<th class="col-md-4"><?= lang('product') . ' (' . lang('code') .' - '.lang('name') . ')'; ?></th>
												<?php
												if ($Settings->product_serial) {
													echo '<th class="col-md-2">' . lang("serial_no") . '</th>';
												}
												?>
												<th class="col-md-1">Giá</th>
												<th class="col-md-1"><?= lang("quantity"); ?></th>
												<?php
												if ($Settings->product_discount && ($Owner || $Admin || $this->session->userdata('allow_discount'))) {
													echo '<th class="col-md-1">Giảm</th>';
												}
												?>
												<?php
												if ($Settings->tax1) {
													echo '<th class="col-md-1">Thuế</th>';
												}
												?>
												<th>
													<?= lang("subtotal"); ?>
													(<span class="currency"><?= $default_currency->code ?></span>)
												</th>
												<th style="width: 30px !important; text-align: center;">
													<i class="fa fa-trash-o" style="opacity:0.5; filter:alpha(opacity=50);"></i>
												</th>
											</tr>
											</thead>
											<tbody></tbody>
											<tfoot></tfoot>
										</table>
									</div>
								</div>
							</div>
						</div>
						<div class="col-md-4 lhson_add_donhang" style="display:none">					

                        <?php if ($Owner || $Admin || $this->session->userdata('allow_discount')) { ?>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="sldiscount_tra">Phụ thu (5/5%)</label>
                                    <?php echo form_input('order_discount_tra', '', 'class="form-control input-tip" id="sldiscount_tra"'); ?>
                                </div>
                            </div>
                        <?php } ?>

						</div>     
                    </div>
					 <div id="bottom-total_tra" class="well well-sm" style="margin-bottom: 0;">
						<table class="table table-bordered table-condensed totals" style="margin-bottom:0;">
							<tr class="warning">
								<td><?= lang('items') ?> <span class="totals_val pull-right" id="titems_tra">0</span></td>
								<td><?= lang('total') ?> <span class="totals_val pull-right" id="total_tra">0.00</span></td>
								
								<td><?= lang('grand_total') ?> <span class="totals_val pull-right" id="gtotal_tra">0.00</span></td>
							</tr>
						</table>
					</div>  
					<!-- end tra hang -->
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
                            class="fa fa-2x">&times;</i></span><span class="sr-only"><?=lang('close');?></span></button>
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
                    <?php if ($Settings->product_discount && ($Owner || $Admin || $this->session->userdata('allow_discount'))) { ?>
                        <div class="form-group">
                            <label for="pdiscount"
                                   class="col-sm-4 control-label"><?= lang('product_discount') ?></label>

                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="pdiscount">
                            </div>
                        </div>
                    <?php } ?>
                    <div class="form-group">
                        <label for="pprice" class="col-sm-4 control-label"><?= lang('unit_price') ?></label>

                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="pprice" <?= ($Owner || $Admin || $GP['edit_price']) ? '' : 'readonly'; ?>>
                        </div>
                    </div>
					 <?php if ($Settings->use_gia_si&&($Owner || $Admin || $this->session->userdata('allow_discount'))) { ?>
					 <div class="form-group">
                        <label for="p_gia_si" class="col-sm-4 control-label"><?= lang('product_gia_si') ?></label>

                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="p_gia_si" <?= ($Owner || $Admin || $GP['edit_price']) ? '' : 'readonly'; ?>>
							<div class="btn btn-giasiapply" onclick="apdunggiasi()">Áp dụng</div>
                        </div>
                    </div>
					 <?php } ?>
                    <table class="table table-bordered table-striped">
                        <tr>
                            <th style="width:25%;"><?= lang('net_unit_price'); ?></th>
                            <th style="width:25%;"><span id="net_price"></span></th>
                            <th style="width:25%;"><?= lang('product_tax'); ?></th>
                            <th style="width:25%;"><span id="pro_tax"></span></th>
                        </tr>
                    </table>
                    <input type="hidden" id="punit_price" value=""/>
                    <input type="hidden" id="old_tax" value=""/>
                    <input type="hidden" id="old_qty" value=""/>
                    <input type="hidden" id="old_price" value=""/>
                    <input type="hidden" id="row_id" value=""/>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="editItem"><?= lang('submit') ?></button>
            </div>
        </div>
    </div>
</div>
<div class="modal" id="prModal_tra" tabindex="-1" role="dialog" aria-labelledby="prModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true"><i
                            class="fa fa-2x">&times;</i></span><span class="sr-only"><?=lang('close');?></span></button>
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
                                echo form_dropdown('ptax_tra', $tr, "", 'id="ptax_tra" class="form-control pos-input-tip" style="width:100%;"');
                                ?>
                            </div>
                        </div>
                    <?php } ?>
                    <?php if ($Settings->product_serial) { ?>
                        <div class="form-group">
                            <label for="pserial_tra" class="col-sm-4 control-label"><?= lang('serial_no') ?></label>

                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="pserial_tra">
                            </div>
                        </div>
                    <?php } ?>
                    <div class="form-group">
                        <label for="pquantity_tra" class="col-sm-4 control-label"><?= lang('quantity') ?></label>

                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="pquantity_tra">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="punit_tra" class="col-sm-4 control-label"><?= lang('product_unit') ?></label>
                        <div class="col-sm-8">
                            <div id="punits-div_tra"></div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="poption_tra" class="col-sm-4 control-label"><?= lang('product_option') ?></label>
                        <div class="col-sm-8">
                            <div id="poptions-div_tra"></div>
                        </div>
                    </div>
                    <?php if ($Settings->product_discount && ($Owner || $Admin || $this->session->userdata('allow_discount'))) { ?>
                        <div class="form-group">
                            <label for="pdiscount_tra"
                                   class="col-sm-4 control-label"><?= lang('product_discount') ?></label>

                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="pdiscount_tra">
                            </div>
                        </div>
                    <?php } ?>
                    <div class="form-group">
                        <label for="pprice_tra" class="col-sm-4 control-label"><?= lang('unit_price') ?></label>

                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="pprice_tra" <?= ($Owner || $Admin || $GP['edit_price']) ? '' : 'readonly'; ?>>
                        </div>
                    </div>
                    <table class="table table-bordered table-striped">
                        <tr>
                            <th style="width:25%;"><?= lang('net_unit_price'); ?></th>
                            <th style="width:25%;"><span id="net_price"></span></th>
                            <th style="width:25%;"><?= lang('product_tax'); ?></th>
                            <th style="width:25%;"><span id="pro_tax_tra"></span></th>
                        </tr>
                    </table>
                    <input type="hidden" id="punit_price_tra" value=""/>
                    <input type="hidden" id="old_tax_tra" value=""/>
                    <input type="hidden" id="old_qty_tra" value=""/>
                    <input type="hidden" id="old_price_tra" value=""/>
                    <input type="hidden" id="row_id_tra" value=""/>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="editItem_tra"><?= lang('submit') ?></button>
            </div>
        </div>
    </div>
</div>

<div class="modal" id="mModal" tabindex="-1" role="dialog" aria-labelledby="mModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true"><i
                            class="fa fa-2x">&times;</i></span><span class="sr-only"><?=lang('close');?></span></button>
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
                    <?php if ($Settings->product_discount && ($Owner || $Admin || $this->session->userdata('allow_discount'))) { ?>
                        <div class="form-group">
                            <label for="mdiscount"
                                   class="col-sm-4 control-label"><?= lang('product_discount') ?></label>

                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="mdiscount">
                            </div>
                        </div>
                    <?php } ?>
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
<div class="modal" id="MdKhuyenMai" tabindex="-1" role="dialog" aria-labelledby="prKhuyenMai" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true"><i
                            class="fa fa-2x">&times;</i></span><span class="sr-only"><?=lang('close');?></span></button>
                <h4 class="modal-title" id="prKhuyenMai"></h4>
            </div>
            <div class="modal-body" id="pr_popover_content">
                <form class="form-horizontal" role="form">
                        <div class="form-group">
                            <input type="text" name="km_item_change" value="" class="form-control input-lg ui-autocomplete-input" id="km_item_change" placeholder="Tìm kiếm sản phẩm" autocomplete="off" tabindex="1">
                           
                        </div>
                    <input type="hidden" id="km_edit_id" value=""/>
                </form> 
            </div>
        </div>
    </div>
</div>
<div class="modal" id="gcModal" tabindex="-1" role="dialog" aria-labelledby="mModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i
                        class="fa fa-2x">&times;</i></button>
                <h4 class="modal-title" id="myModalLabel"><?= lang('sell_gift_card'); ?></h4>
            </div>
            <div class="modal-body">
                <p><?= lang('enter_info'); ?></p>

                <div class="alert alert-danger gcerror-con" style="display: none;">
                    <button data-dismiss="alert" class="close" type="button">×</button>
                    <span id="gcerror"></span>
                </div>
                <div class="form-group">
                    <?= lang("card_no", "gccard_no"); ?> *
                    <div class="input-group">
                        <?php echo form_input('gccard_no', '', 'class="form-control" id="gccard_no"'); ?>
                        <div class="input-group-addon" style="padding-left: 10px; padding-right: 10px;"><a href="#"
                                                                                                           id="genNo"><i
                                    class="fa fa-cogs"></i></a></div>
                    </div>
                </div>
                <input type="hidden" name="gcname" value="<?= lang('gift_card') ?>" id="gcname"/>

                <div class="form-group">
                    <?= lang("value", "gcvalue"); ?> *
                    <?php echo form_input('gcvalue', '', 'class="form-control" id="gcvalue"'); ?>
                </div>
                <div class="form-group">
                    <?= lang("price", "gcprice"); ?> *
                    <?php echo form_input('gcprice', '', 'class="form-control" id="gcprice"'); ?>
                </div>
                <div class="form-group">
                    <?= lang("customer", "gccustomer"); ?>
                    <?php echo form_input('gccustomer', '', 'class="form-control" id="gccustomer"'); ?>
                </div>
                <div class="form-group">
                    <?= lang("expiry_date", "gcexpiry"); ?>
                    <?php echo form_input('gcexpiry', $this->sma->hrsd(date("Y-m-d", strtotime("+2 year"))), 'class="form-control date" id="gcexpiry"'); ?>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" id="addGiftCard" class="btn btn-primary"><?= lang('sell_gift_card') ?></button>
            </div>
        </div>
    </div>
</div>
<style>
div#sticker_tra {
    float: left;
    width: 100%;
}

div#sticker .input-group.wide-tip,div#sticker_tra .input-group.wide-tip {
    float: left;
    width: 98%;
    margin: 1%;
}
div#div_doitac_id {
    border: 0px;
}

div#div_doitac_id label {
    display: none;
}

div#s2id_sldoitacaddpt,div#doitac {
    border: 0px;
}
ul#ui-id-3 {
    z-index: 99999999999;
}
</style>
<script type="text/javascript">
    $(document).ready(function () {
        $('#gccustomer').select2({
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
        $('#genNo').click(function () {
            var no = generateCardNo();
            $(this).parent().parent('.input-group').children('input').val(no);
            return false;
        });
        if (customFields = localStorage.getItem('custom_fields')) {
    try {
        var fields = JSON.parse(customFields);
        if (fields.fee_nhamay) $('#custom_fee_nhamay').val(fields.fee_nhamay);
        if (fields.fee_phukien) $('#custom_fee_phukien').val(fields.fee_phukien);
        if (fields.fee_lapdat) $('#custom_fee_lapdat').val(fields.fee_lapdat);
        if (fields.fee_chanhxe) $('#custom_fee_chanhxe').val(fields.fee_chanhxe);
    } catch(e) {
        console.log('Error loading custom fields:', e);
    }
}

// Lưu custom fields vào localStorage khi thay đổi
$(document).on('change blur', '.custom-field', function() {
    var customFieldsData = {
        fee_nhamay: $('#custom_fee_nhamay').val() || '0',
        fee_phukien: $('#custom_fee_phukien').val() || '0',
        fee_lapdat: $('#custom_fee_lapdat').val() || '0',
        fee_chanhxe: $('#custom_fee_chanhxe').val() || '0'
    };
    localStorage.setItem('custom_fields', JSON.stringify(customFieldsData));
});

// Trước khi submit, đóng gói custom fields thành JSON
$('form').on('submit', function(e) {
    var customFieldsData = {
        fields: [
            {
                name: "fee_nhamay",
                label: "Phí nhà máy",
                value: $('#custom_fee_nhamay').val() || '0'
            },
            {
                name: "fee_phukien",
                label: "Phí phụ kiện",
                value: $('#custom_fee_phukien').val() || '0'
            },
            {
                name: "fee_lapdat",
                label: "Phí lắp đặt",
                value: $('#custom_fee_lapdat').val() || '0'
            },
            {
                name: "fee_chanhxe",
                label: "Chành xe",
                value: $('#custom_fee_chanhxe').val() || '0'
            }
        ]
    };
    $('#custom_fields_json').val(JSON.stringify(customFieldsData));
});

// Reset custom fields khi click nút reset
$('#reset').on('click', function() {
    $('.custom-field').val('');
    localStorage.removeItem('custom_fields');
});
    });
</script>
