<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<style>
div#div_doitac_id {
    border: 0px;
}

div#div_doitac_id label {
    display: none;
}

div#s2id_sldoitacaddpt,div#doitac {
    border: 0px;
}
</style>
<script type="text/javascript">
$(document).ready(function() {
				$('form').preventDoubleSubmission();
			});
    <?php if ($this->session->userdata('remove_pols')) { ?>
    if (localStorage.getItem('poitems')) {
        localStorage.removeItem('poitems');
    }
    if (localStorage.getItem('podiscount')) {
        localStorage.removeItem('podiscount');
    }
	if (site.settings.chiec_khau != 0) {
			if (localStorage.getItem('pchiec_khau')) {
				localStorage.removeItem('pchiec_khau');
			}
		}
    if (localStorage.getItem('potax2')) {
        localStorage.removeItem('potax2');
    }
    if (localStorage.getItem('poshipping')) {
        localStorage.removeItem('poshipping');
    }
    if (localStorage.getItem('poref')) {
        localStorage.removeItem('poref');
    }
    if (localStorage.getItem('powarehouse')) {
        localStorage.removeItem('powarehouse');
    }
    if (localStorage.getItem('ponote')) {
        localStorage.removeItem('ponote');
    }
    if (localStorage.getItem('posupplier')) {
        localStorage.removeItem('posupplier');
    }
    if (localStorage.getItem('pocurrency')) {
        localStorage.removeItem('pocurrency');
    }
    if (localStorage.getItem('poextras')) {
        localStorage.removeItem('poextras');
    }
    if (localStorage.getItem('podate')) {
        localStorage.removeItem('podate');
    }
    if (localStorage.getItem('postatus')) {
        localStorage.removeItem('postatus');
    }
    if (localStorage.getItem('popayment_term')) {
        localStorage.removeItem('popayment_term');
    }
    <?php $this->sma->unset_data('remove_pols');
} ?>
    <?php if($quote_id) { ?>
    localStorage.setItem('powarehouse', '<?= $quote->warehouse_id ?>');
    localStorage.setItem('ponote', '<?= str_replace(array("\r", "\n"), "", $this->sma->decode_html($quote->note)); ?>');
    localStorage.setItem('podiscount', '<?= $quote->order_discount_id ?>');
    localStorage.setItem('potax2', '<?= $quote->order_tax_id ?>');
    localStorage.setItem('poshipping', '<?= $quote->shipping ?>');
    <?php if ($quote->supplier_id) { ?>
        localStorage.setItem('posupplier', '<?= $quote->supplier_id ?>');
    <?php } ?>
    localStorage.setItem('poitems', JSON.stringify(<?= $quote_items; ?>));
    <?php } ?>

    var count = 1, an = 1, po_edit = false, product_variant = 0, DT = <?= $Settings->default_tax_rate ?>, DC = '<?= $default_currency->code ?>', shipping = 0,
        product_tax = 0, invoice_tax = 0, total_discount = 0, total = 0,tong_chiec_khau=0,
        tax_rates = <?php echo json_encode($tax_rates); ?>, poitems = {},
        audio_success = new Audio('<?= $assets ?>sounds/sound2.mp3'),
        audio_error = new Audio('<?= $assets ?>sounds/sound3.mp3');
    $(document).ready(function () {
        <?php if($this->input->get('supplier')) { ?>
        if (!localStorage.getItem('poitems')) {
            localStorage.setItem('posupplier', <?=$this->input->get('supplier');?>);
        }
        <?php } ?>
        <?php if ($Owner || $Admin) { ?>
        if (!localStorage.getItem('podate')) {
            $("#podate").datetimepicker({
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
        $(document).on('change', '#podate', function (e) {
            localStorage.setItem('podate', $(this).val());
        });
        if (podate = localStorage.getItem('podate')) {
            $('#podate').val(podate);
        }
        <?php } ?>
        if (!localStorage.getItem('potax2')) {
            localStorage.setItem('potax2', <?=$Settings->default_tax_rate2;?>);
            setTimeout(function(){ $('#extras').iCheck('check'); }, 1000);
        }
		$('#slpayment_status').change(function (e) {
			var ps = $(this).val();
			localStorage.setItem('slpayment_status', ps);
			if (ps == 'partial' || ps == 'paid') {
				if(ps == 'paid') {
                    var ds = podiscount;
                    if (ds.indexOf("%") !== -1) {
                        var pds = ds.split("%");
                        if (!isNaN(pds[0])) {
                            order_discount = formatDecimal(((total * parseFloat(pds[0])) / 100), 4);
                        } else {
                            order_discount = formatDecimal(ds);
                        }
                    } else {
                        order_discount = formatDecimal(ds);
                    }
                    
					$('#amount_1').val(formatDecimal(parseFloat(((total + invoice_tax) - order_discount) + shipping)));
				}
				$('#payments').slideDown();
				$('#pcc_no_1').focus();
			} else {
				$('#payments').slideUp();
			}
		});
		
        ItemnTotals();
        $("#add_item").autocomplete({
            // source: '<?= site_url('purchases/suggestions'); ?>',
            source: function (request, response) {
                $.ajax({
                    type: 'get',
                    url: '<?= site_url('purchases/suggestions'); ?>',
                    dataType: "json",
                    data: {
                        term: request.term,
                        supplier_id: $("#posupplier").val()
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
                    //audio_error.play();
                    bootbox.alert('<?= lang('no_match_found') ?>', function () {
                        $('#add_item').focus();
                    });
                    $(this).removeClass('ui-autocomplete-loading');
                    $(this).val('');
                }
                else if (ui.content.length == 1 && ui.content[0].id != 0) {
					if ($(this).val().length >=6){
						ui.item = ui.content[0];
						$(this).data('ui-autocomplete')._trigger('select', 'autocompleteselect', ui);
						$(this).autocomplete('close');
						$(this).removeClass('ui-autocomplete-loading');
					}
                }
                else if (ui.content.length == 1 && ui.content[0].id == 0) {
                    //audio_error.play();
                    bootbox.alert('<?= lang('no_match_found') ?>', function () {
                        $('#add_item').focus();
                    });
                    $(this).removeClass('ui-autocomplete-loading');
                    $(this).val('');
                }
            },
            select: function (event, ui) {
                event.preventDefault();
                if (ui.item.id !== 0) {
                    var row = add_purchase_item(ui.item);
                    if (row)
                        $(this).val('');
                } else {
                    //audio_error.play();
                    bootbox.alert('<?= lang('no_match_found') ?>');
                }
            }
        });

        $(document).on('click', '#addItemManually', function (e) {
            if (!$('#mcode').val()) {
                $('#mError').text('<?= lang('product_code_is_required') ?>');
                $('#mError-con').show();
                return false;
            }
            if (!$('#mname').val()) {
                $('#mError').text('<?= lang('product_name_is_required') ?>');
                $('#mError-con').show();
                return false;
            }
            if (!$('#mcategory').val()) {
                $('#mError').text('<?= lang('product_category_is_required') ?>');
                $('#mError-con').show();
                return false;
            }
            if (!$('#munit').val()) {
                $('#mError').text('<?= lang('product_unit_is_required') ?>');
                $('#mError-con').show();
                return false;
            }
            if (!$('#mcost').val()) {
                $('#mError').text('<?= lang('product_cost_is_required') ?>');
                $('#mError-con').show();
                return false;
            }
            if (!$('#mprice').val()) {
                $('#mError').text('<?= lang('product_price_is_required') ?>');
                $('#mError-con').show();
                return false;
            }

            var msg, row = null, product = {
                type: 'standard',
                code: $('#mcode').val(),
                name: $('#mname').val(),
                tax_rate: $('#mtax').val(),
                tax_method: $('#mtax_method').val(),
                category_id: $('#mcategory').val(),
                unit: $('#munit').val(),
                cost: $('#mcost').val(),
                price: $('#mprice').val()
            };

            $.ajax({
                type: "get", async: false,
                url: site.base_url + "products/addByAjax",
                data: {token: "<?= $csrf; ?>", product: product},
                dataType: "json",
                success: function (data) {
                    if (data.msg == 'success') {
                        row = add_purchase_item(data.result);
                    } else {
                        msg = data.msg;
                    }
                }
            });
            if (row) {
                $('#mModal').modal('hide');
                //audio_success.play();
            } else {
                $('#mError').text(msg);
                $('#mError-con').show();
            }
            return false;

        });
		
    });
    
$('#slpayment_status').change(function (e) {
    var ps = $(this).val();
    localStorage.setItem('slpayment_status', ps);
    if (ps == 'partial' || ps == 'paid') {
        if(ps == 'paid') {
            $('#amount_1').val(formatDecimal(parseFloat(((total + invoice_tax) - order_discount) + shipping)));
        }
        $('#payments').slideDown();
        $('#pcc_no_1').focus();
    } else {
        $('#payments').slideUp();
    }
});
if (slpayment_status = localStorage.getItem('slpayment_status')) {
    $('#slpayment_status').val(slpayment_status);
    var ps = slpayment_status;
    if (ps == 'partial' || ps == 'paid') {
        $('#payments').slideDown();
        $('#pcc_no_1').focus();
    } else {
        $('#payments').slideUp();
    }
}

$(document).on('change', '.paid_by', function () {
    var p_val = $(this).val();
    localStorage.setItem('paid_by', p_val);
    $('#rpaidby').val(p_val);
    if (p_val == 'cash' ||  p_val == 'other') {
        $('.pcheque_1').hide();
        $('.pcc_1').hide();
        $('.pcash_1').show();
        $('#payment_note_1').focus();
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
    if (p_val == 'gift_card') {
        $('.gc').show();
        $('.ngc').hide();
        $('#gift_card_no').focus();
    } else {
        $('.ngc').show();
        $('.gc').hide();
        $('#gc_details').html('');
    }
});

if (paid_by = localStorage.getItem('paid_by')) {
    var p_val = paid_by;
    $('.paid_by').val(paid_by);
    $('#rpaidby').val(p_val);
    if (p_val == 'cash' ||  p_val == 'other') {
        $('.pcheque_1').hide();
        $('.pcc_1').hide();
        $('.pcash_1').show();
        $('#payment_note_1').focus();
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
    if (p_val == 'gift_card') {
        $('.gc').show();
        $('.ngc').hide();
        $('#gift_card_no').focus();
    } else {
        $('.ngc').show();
        $('.gc').hide();
        $('#gc_details').html('');
    }
}

if (gift_card_no = localStorage.getItem('gift_card_no')) {
    $('#gift_card_no').val(gift_card_no);
}
$('#gift_card_no').change(function (e) {
    localStorage.setItem('gift_card_no', $(this).val());
});

if (amount_1 = localStorage.getItem('amount_1')) {
    $('#amount_1').val(amount_1);
}
$('#amount_1').change(function (e) {
    localStorage.setItem('amount_1', $(this).val());
});

if (paid_by_1 = localStorage.getItem('paid_by_1')) {
    $('#paid_by_1').val( paid_by_1);
}
$('#paid_by_1').change(function (e) {
    localStorage.setItem('paid_by_1', $(this).val());
});

if (pcc_holder_1 = localStorage.getItem('pcc_holder_1')) {
    $('#pcc_holder_1').val(pcc_holder_1);
}
$('#pcc_holder_1').change(function (e) {
    localStorage.setItem('pcc_holder_1', $(this).val());
});

if (pcc_type_1 = localStorage.getItem('pcc_type_1')) {
    $('#pcc_type_1').val(pcc_type_1);
}
$('#pcc_type_1').change(function (e) {
    localStorage.setItem('pcc_type_1', $(this).val());
});

if (pcc_month_1 = localStorage.getItem('pcc_month_1')) {
    $('#pcc_month_1').val( pcc_month_1);
}
$('#pcc_month_1').change(function (e) {
    localStorage.setItem('pcc_month_1', $(this).val());
});

if (pcc_year_1 = localStorage.getItem('pcc_year_1')) {
    $('#pcc_year_1').val(pcc_year_1);
}
$('#pcc_year_1').change(function (e) {
    localStorage.setItem('pcc_year_1', $(this).val());
});

if (pcc_no_1 = localStorage.getItem('pcc_no_1')) {
    $('#pcc_no_1').val(pcc_no_1);
}
$('#pcc_no_1').change(function (e) {
    var pcc_no = $(this).val();
    localStorage.setItem('pcc_no_1', pcc_no);
    var CardType = null;
    var ccn1 = pcc_no.charAt(0);
    if(ccn1 == 4)
        CardType = 'Visa';
    else if(ccn1 == 5)
        CardType = 'MasterCard';
    else if(ccn1 == 3)
        CardType = 'Amex';
    else if(ccn1 == 6)
        CardType = 'Discover';
    else
        CardType = 'Visa';

    $('#pcc_type_1').val(CardType);
});

if (cheque_no_1 = localStorage.getItem('cheque_no_1')) {
    $('#cheque_no_1').val(cheque_no_1);
}
$('#cheque_no_1').change(function (e) {
    localStorage.setItem('cheque_no_1', $(this).val());
});

if (payment_note_1 = localStorage.getItem('payment_note_1')) {
    $('#payment_note_1').redactor('set', payment_note_1);
}
$('#payment_note_1').redactor('destroy');
$('#payment_note_1').redactor({
    buttons: ['formatting', '|', 'alignleft', 'aligncenter', 'alignright', 'justify', '|', 'bold', 'italic', 'underline', '|', 'unorderedlist', 'orderedlist', '|', 'link', '|', 'html'],
    formattingTags: ['p', 'pre', 'h3', 'h4'],
    minHeight: 100,
    changeCallback: function (e) {
        var v = this.get();
        localStorage.setItem('payment_note_1', v);
    }
});

</script>

<div class="box lhson_nhapkho_add">
	<?php
		$attrib = array('data-toggle' => 'validator', 'role' => 'form');
		echo form_open_multipart("purchases/add", $attrib)
		?>
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-plus"></i><?= lang('add_purchase'); ?></h2>
		<div class="main-task-lhson nhapkho">
			<button type="submit" class="btn btn-primary btncls" name="add_pruchase" id="add_pruchase">
				<i class="fa fa-save"></i>
				Lưu kho
			</button>
			<button type="button" class="btn btn-default btncls" id="reset">
				<i class="fa fa-refresh"></i>
				<?= lang('reset') ?>
			</button>
		</div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <div class="row">
				<br/>
                    <div class="col-lg-12">						
						 <div class="col-lg-4">							
							<div class="col-md-4 _ncc_add_i">
								<div class="form-group">
									<?php if ($Owner || $Admin || $GP['suppliers-add'] || $GP['suppliers-index']) { ?><div class="input-group"><?php } ?>
										<input type="hidden" name="supplier" value="" id="posupplier"
											   class="form-control" style="width:100%;"
											   placeholder="<?= lang("select") . ' ' . lang("supplier") ?> (*)">
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
									<?php if ($Owner || $Admin || $GP['suppliers-add'] || $GP['suppliers-index']) { ?></div><?php } ?>
								</div>
								<div class="clearfix"></div>
							</div>
							<?php if ($Owner || $Admin) { ?>
								<div class="col-md-4">
									<div class="form-group">
										<?= lang("date", "podate"); ?>
										<?php echo form_input('date', (isset($_POST['date']) ? $_POST['date'] : ""), 'class="form-control input-tip datetime" id="podate" required="required"'); ?>
									</div>
								</div>
							<?php } ?>
							<div class="col-md-4">
								<div class="form-group">
									<?= lang("reference_no", "poref"); ?>
									<?php echo form_input('reference_no', (isset($_POST['reference_no']) ? $_POST['reference_no'] : $ponumber), 'class="form-control input-tip" id="poref"'); ?>
								</div>
							</div>
							<?php if ($Owner || $Admin || !$this->session->userdata('warehouse_id')) { ?>
								<div class="col-md-4">
									<div class="form-group">
										<?php
										$wh[''] = '';
										foreach ($warehouses as $warehouse) {
											$wh[$warehouse->id] = $warehouse->name;
										}
										echo form_dropdown('warehouse', $wh, (isset($_POST['warehouse']) ? $_POST['warehouse'] : $Settings->default_warehouse), 'id="powarehouse" class="form-control input-tip select" data-placeholder="' . lang("select") . ' ' . lang("warehouse") . '" required="required" style="width:100%;" ');
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
									$post = array('received' => lang('received'), 'pending' => lang('pending'), 'ordered' => lang('ordered'));
									echo form_dropdown('status', $post, (isset($_POST['status']) ? $_POST['status'] : ''), 'id="postatus" class="form-control input-tip select" data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("status") . '" required="required" style="width:100%;" ');
									?>
								</div>
							</div>
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
									<?= lang("payment_status", "slpayment_status"); ?>
									<?php $pst = array('pending' => lang('pending'), 'due' => lang('due'), 'partial' => lang('partial'), 'paid' => lang('paid'));
									echo form_dropdown('payment_status', $pst, '', 'class="form-control input-tip" required="required" id="slpayment_status"'); ?>

								</div>
							</div>
							<div class="col-md-4">
								<div class="form-group">
									<?= lang("File", "file"); ?>
									<input id="document" type="file" data-browse-label="<?= lang('browse'); ?>" name="document" data-show-upload="false"
										   data-show-preview="false" class="form-control file">
								</div>
							</div>
							<div class="clearfix"></div>
							<div id="payments" style="display: none;">
								<div class="col-md-12 no-padding-lhson">
									<div class="well well-sm well_1">
										<div class="col-md-12">
											<div class="row">
												<div class="col-md-12">
													<div class="form-group">
														<?= lang("payment_reference_no", "payment_reference_no"); ?>
														<?= form_input('payment_reference_no', (isset($_POST['payment_reference_no']) ? $_POST['payment_reference_no'] : $payment_ref), 'class="form-control tip" id="payment_reference_no"'); ?>
													</div>
												</div>
												<div class="col-sm-12">
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
												<div class="col-sm-12">
													<div class="form-group">
														<select name="paid_by" id="paid_by_1" class="form-control paid_by skip">
															<?= $this->sma->paid_opts_lhson(null,true); ?>
														</select>
													</div>
												</div>

											</div>
											<div class="clearfix"></div>
											<div class="pcc_1" style="display:none;">
												<div class="row">
													<div class="col-md-12">
														<div class="form-group">
															<input name="pcc_no" type="text" id="pcc_no_1"
																   class="form-control" placeholder="<?= lang('cc_no') ?>"/>
														</div>
													</div>
													<div class="col-md-12">
														<div class="form-group">
															<input name="pcc_holder" type="text" id="pcc_holder_1"
																   class="form-control"
																   placeholder="<?= lang('cc_holder') ?>"/>
														</div>
													</div>
													<div class="col-md-12">
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
													<div class="col-md-12">
														<div class="form-group">
															<input name="pcc_month" type="text" id="pcc_month_1"
																   class="form-control" placeholder="<?= lang('month') ?>"/>
														</div>
													</div>
													<div class="col-md-12">
														<div class="form-group">

															<input name="pcc_year" type="text" id="pcc_year_1"
																   class="form-control" placeholder="<?= lang('year') ?>"/>
														</div>
													</div>
													<div class="col-md-12">
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
							<div class="col-md-4">
								<div class="form-group">
									<input type="checkbox" class="checkbox" id="extras" value=""/>
									<label for="extras" class="padding05"><?= lang('more_options') ?></label>
								</div>
								<div class="row" id="extras-con" style="display: none;">
									<?php if ($Settings->tax2) { ?>
										<div class="col-md-4">
											<div class="form-group">
												<?php
												$tr[""] = "";
												foreach ($tax_rates as $tax) {
													$tr[$tax->id] = $tax->name;
												}
												echo form_dropdown('order_tax', $tr, "", 'id="potax2" class="form-control input-tip select" style="width:100%;"');
												?>
											</div>
										</div>
									<?php } ?>

									<div class="col-md-4">
										<div class="form-group">
											<?= lang("discount_label", "podiscount"); ?>
											<?php echo form_input('discount', '', 'class="form-control input-tip" id="podiscount"'); ?>
										</div>
									</div>
									<?php 
									if($Settings->chiec_khau){
									?>
									<div class="col-md-4">
										<div class="form-group">
											<?= lang("chiec_khau", "pchiec_khau"); ?>
											<?php echo form_input('chiec_khau', '', 'class="form-control input-tip" id="pchiec_khau"'); ?>
										</div>
									</div>
									<?php
									}
									?>
									
									<div class="col-md-4">
										<div class="form-group">
											<?= lang("shipping", "poshipping"); ?>
											<?php echo form_input('shipping', '', 'class="form-control input-tip" id="poshipping"'); ?>
										</div>
									</div>

									<div class="col-md-4" style="display:none">
										<div class="form-group">
											PT Thanh toán
											<?php echo form_input('payment_term', '', 'class="form-control tip" data-trigger="focus" data-placement="top" title="' . lang('payment_term_tip') . '" id="popayment_term"'); ?>
										</div>
									</div>
								</div>
								<div class="clearfix"></div>
								<div class="form-group _note_add_i">
									<?= lang("note", "ponote"); ?>
									<?php echo form_textarea('note', (isset($_POST['note']) ? $_POST['note'] : ""), 'class="form-control" id="ponote" style="margin-top: 10px; height: 100px;"'); ?>
								</div>
							</div>
						</div>
						<div class="col-md-8">
							<div class="col-md-12" id="sticker">
									<div class="form-group" style="margin-bottom:0;">
										<div class="input-group wide-tip">
											<?php echo form_input('add_item', '', 'class="form-control input-lg" id="add_item" placeholder="' . $this->lang->line("add_product_to_order") . '"'); ?>
											<?php if ($Owner || $Admin || $GP['products-add']) { ?>
											<div class="input-group-addon" style="padding-left: 10px; padding-right: 10px;">
												<a href="<?= site_url('products/add') ?>" id="addManually1"><i
														class="fa fa-2x fa-plus-circle addIcon" id="addIcon"></i></a></div>
											<?php } ?>
										</div>
									</div>
									<div class="clearfix"></div>
									<br/>
								</div>
							<div class="col-md-12">
							
								<div class="control-group table-group">
									<div class="controls table-controls">
										<table id="poTable"
											   class="table items table-striped table-bordered table-condensed table-hover sortable_table">
											<thead>
											<tr>
												<th class="col-md-4"><?= lang('product') . ' (' . lang('code') .' - '.lang('name') . ')'; ?></th>
												<?php
												if ($Settings->product_expiry) {
													echo '<th class="col-md-2">' . $this->lang->line("expiry_date") . '</th>';
												}
												?>
												<th class="col-md-1">Giá nhập</th>
												<th class="col-md-1"><?= lang("quantity"); ?></th>
												<?php
												if ($Settings->product_discount) {
													echo '<th class="col-md-1">Giảm</th>';
												}
												?>
												<?php
												if ($Settings->chiec_khau) {
													echo '<th class="col-md-1">CK %</th>';
												}
												?>
												<?php
												if ($Settings->tax1) {
													echo '<th class="col-md-1">Thuế</th>';
												}
												?>
												<th><?= lang("subtotal"); ?> (<span
														class="currency"><?= $default_currency->code ?></span>)
												</th>
												<th style="width: 30px !important; text-align: center;"><i
														class="fa fa-trash-o"
														style="opacity:0.5; filter:alpha(opacity=50);"></i></th>
											</tr>
											</thead>
											<tbody></tbody>
											<tfoot></tfoot>
										</table>
									</div>
								</div>
							</div>
							<div class="clearfix"></div>
							<input type="hidden" name="total_items" value="" id="total_items" required="required"/>

						</div>
						
                    </div>
                </div>
                <div id="bottom-total" class="well well-sm" style="margin-bottom: 0;">
                    <table class="table table-bordered table-condensed totals" style="margin-bottom:0;">
                        <tr class="warning">
                            <td><?= lang('items') ?> <span class="totals_val pull-right" id="titems">0</span></td>
                            <td><?= lang('total') ?> <span class="totals_val pull-right" id="total">0.00</span></td>
                            <td><?= lang('order_discount') ?> <span class="totals_val pull-right" id="tds">0.00</span></td>
                            <?php if ($Settings->tax2) { ?>
                                <td><?= lang('order_tax') ?> <span class="totals_val pull-right" id="ttax2">0.00</span></td>
                            <?php } ?>
							<?php if ($Settings->chiec_khau) { ?>
                                <td><?= lang('chiec_khau') ?> <span class="totals_val pull-right" id="tong_chiec_khau">0.00</span></td>
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
                    <div class="form-group">
                        <label for="pquantity" class="col-sm-4 control-label"><?= lang('quantity') ?></label>

                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="pquantity">
                        </div>
                    </div>
                    <?php if ($Settings->product_expiry) { ?>
                        <div class="form-group">
                            <label for="pexpiry" class="col-sm-4 control-label"><?= lang('product_expiry') ?></label>

                            <div class="col-sm-8">
                                <input type="text" class="form-control date" id="pexpiry">
                            </div>
                        </div>
                    <?php } ?>
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
                    <?php if ($Settings->product_discount) { ?>
                        <div class="form-group">
                            <label for="pdiscount" class="col-sm-4 control-label"><?= lang('product_discount') ?></label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="pdiscount">
                            </div>
                        </div>
                    <?php } ?>
					<?php if ($Settings->chiec_khau) { ?>
                        <div class="form-group">
                            <label for="pchiec_khauv2" class="col-sm-4 control-label"><?= lang('chiec_khau') ?></label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="pchiec_khauv2">
                            </div>
                        </div>
                    <?php } ?>
                    <div class="form-group">
                        <label for="pcost" class="col-sm-4 control-label"><?= lang('unit_cost') ?></label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="pcost">
                        </div>
                    </div>
                    <table class="table table-bordered table-striped">
                        <tr>
                            <th style="width:25%;"><?= lang('net_unit_cost'); ?></th>
                            <th style="width:25%;"><span id="net_cost"></span></th>
                            <th style="width:25%;"><?= lang('product_tax'); ?></th>
                            <th style="width:25%;"><span id="pro_tax"></span></th>
                        </tr>
                    </table>
                    <div class="panel panel-default">
                        <div class="panel-heading"><?= lang('calculate_unit_cost'); ?></div>
                        <div class="panel-body">

                            <div class="form-group">
                                <label for="pcost" class="col-sm-4 control-label"><?= lang('subtotal') ?></label>
                                <div class="col-sm-8">
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="psubtotal">
                                        <div class="input-group-addon" style="padding: 2px 8px;">
                                            <a href="#" id="calculate_unit_price" class="tip" title="<?= lang('calculate_unit_cost'); ?>">
                                                <i class="fa fa-calculator"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" id="punit_cost" value=""/>
                    <input type="hidden" id="old_tax" value=""/>
                    <input type="hidden" id="old_qty" value=""/>
                    <input type="hidden" id="old_cost" value=""/>
                    <input type="hidden" id="row_id" value=""/>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="editItem"><?= lang('submit') ?></button>
            </div>
        </div>
    </div>
</div>

<div class="modal" id="mModal" tabindex="-1" role="dialog" aria-labelledby="mModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true"><i
                            class="fa fa-2x">&times;</i></span><span class="sr-only"><?=lang('close');?></span></button>
                <h4 class="modal-title" id="mModalLabel"><?= lang('add_standard_product') ?></h4>
            </div>
            <div class="modal-body" id="pr_popover_content">
                <div class="alert alert-danger" id="mError-con" style="display: none;">
                    <!--<button data-dismiss="alert" class="close" type="button">×</button>-->
                    <span id="mError"></span>
                </div>
                <div class="row">
                    <div class="col-md-6 col-sm-6">
                        <div class="form-group">
                            <?= lang('product_code', 'mcode') ?> *
                            <input type="text" class="form-control" id="mcode">
                        </div>
                        <div class="form-group">
                            <?= lang('product_name', 'mname') ?> *
                            <input type="text" class="form-control" id="mname">
                        </div>
                        <div class="form-group">
                            <?= lang('category', 'mcategory') ?> *
                            <?php
                            $cat[''] = "";
                            foreach ($categories as $category) {
                                $cat[$category->id] = $category->name;
                            }
                            echo form_dropdown('category', $cat, '', 'class="form-control select" id="mcategory" placeholder="' . lang("select") . " " . lang("category") . '" style="width:100%"')
                            ?>
                        </div>
                        <div class="form-group">
                            <?= lang('unit', 'munit') ?> *
                            <input type="text" class="form-control" id="munit">
                        </div>
                    </div>
                    <div class="col-md-6 col-sm-6">
                        <div class="form-group">
                            <?= lang('cost', 'mcost') ?> *
                            <input type="text" class="form-control" id="mcost">
                        </div>
                        <div class="form-group">
                            <?= lang('price', 'mprice') ?> *
                            <input type="text" class="form-control" id="mprice">
                        </div>

                        <?php if ($Settings->tax1) { ?>
                            <div class="form-group">
                                <?= lang('product_tax', 'mtax') ?>
                                <?php
                                $tr[""] = "";
                                foreach ($tax_rates as $tax) {
                                    $tr[$tax->id] = $tax->name;
                                }
                                echo form_dropdown('mtax', $tr, "", 'id="mtax" class="form-control input-tip select" style="width:100%;"');
                                ?>
                            </div>
                            <div class="form-group all">
                                <?= lang("tax_method", "mtax_method") ?>
                                <?php
                                $tm = array('0' => lang('inclusive'), '1' => lang('exclusive'));
                                echo form_dropdown('tax_method', $tm, '', 'class="form-control select" id="mtax_method" placeholder="' . lang("select") . ' ' . lang("tax_method") . '" style="width:100%"')
                                ?>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="addItemManually"><?= lang('submit') ?></button>
            </div>
        </div>
    </div>
</div>
