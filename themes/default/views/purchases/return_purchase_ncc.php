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
 var count = 1, an = 1, po_edit = false, product_variant = 0, DT = <?= $Settings->default_tax_rate ?>, DC = '<?= $default_currency->code ?>', shipping = 0,
        product_tax = 0, invoice_tax = 0, total_discount = 0, total = 0,
        tax_rates = <?php echo json_encode($tax_rates); ?>, poitems = {},
        audio_success = new Audio('<?= $assets ?>sounds/sound2.mp3'),
        audio_error = new Audio('<?= $assets ?>sounds/sound3.mp3');
		
    $(document).ready(function () {
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
		
		<?php if($this->input->get('supplier')) { ?>
        if (!localStorage.getItem('poitems')) {
            localStorage.setItem('posupplier', <?=$this->input->get('supplier');?>);
        }
        <?php } ?>
        if (!localStorage.getItem('redate')) {
            $("#redate").datetimepicker({
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
                    ui.item = ui.content[0];
                    $(this).data('ui-autocomplete')._trigger('select', 'autocompleteselect', ui);
                    $(this).autocomplete('close');
                    $(this).removeClass('ui-autocomplete-loading');
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
    });
	
	
	 // $('#posupplier').val('<?= $this->input->post('supplier') ?>').select2({
            // minimumInputLength: 1,
            // data: [],
            // initSelection: function (element, callback) {
                // $.ajax({
                    // type: "get", async: false,
                    // url: site.base_url + "suppliers/suggestions/" + $(element).val(),
                    // dataType: "json",
                    // success: function (data) {
                        // callback(data.results[0]);
                    // }
                // });
            // },
            // ajax: {
                // url:'<?= site_url('suppliers/suggestions'); ?>',
                // dataType: 'json',
                // quietMillis: 15,
                // data: function (term, page) {
                    // return {
                        // term: term,
                        // limit: 10
                    // };
                // },
                // results: function (data, page) {
                    // if (data.results != null) {
                        // return {results: data.results};
                    // } else {
                        // return {results: [{id: '', text: 'No Match Found'}]};
                    // }
                // }
            // }
        // });
		
</script>


<div class="box lhson_nhapkho_add">
<?php
	$attrib = array('data-toggle' => 'validator', 'role' => 'form','class'=>'');
	echo form_open_multipart("purchases/return_purchase_ncc/", $attrib)
	?>
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-minus-circle"></i>Trả hàng cho nhà cung cấp</h2>
		<div class="main-task-lhson nhapkho">
			<button type="submit" class="btn btn-primary" id="add_pruchase">Trả hàng</button>
		</div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">                
				<br/>
                <div class="row">
                    <div class="col-lg-12">
						 <div class="col-lg-4">				
							<?php if ($Owner || $Admin) { ?>
								<div class="col-md-4">
									<div class="form-group">
										<?= lang("date", "redate"); ?>
										<?php echo form_input('date', (isset($_POST['date']) ? $_POST['date'] : ""), 'class="form-control input-tip datetime" id="redate" required="required"'); ?>
									</div>
								</div>
							<?php } ?>
							<div class="col-md-4">
								<div class="form-group">
									<?= lang("reference_no", "reref"); ?>
									<?php echo form_input('reference_no', (isset($_POST['reference_no']) ? $_POST['reference_no'] : ''), 'class="form-control input-tip" id="reref"'); ?>
								</div>
							</div> 
							<div class="col-md-4 _ncc_add_i">
								<div class="form-group">
									<?php echo form_input('supplier', (isset($_POST['supplier']) ? $_POST['supplier'] : ""), 'class="form-control" id="posupplier" data-placeholder="' . lang("select") . ' ' . lang("suppliers") . '"'); ?> </div>
							</div>
							<div class="col-md-4 _ncc_add_i">
									<div class="form-group">
										<?php
										$wh[''] = '';
										foreach ($warehouses as $warehouse) {
											$wh[$warehouse->id] = $warehouse->name;
										}
										echo form_dropdown('warehouse', $wh, (isset($_POST['warehouse']) ? $_POST['warehouse'] : $purchase->warehouse_id), 'id="powarehouse" class="form-control input-tip select" data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("warehouse") . '" required="required" style="width:100%;" ');
										?>
									</div>
							</div>
							<div class="col-md-4">
								<div class="form-group" id="div_doitac_id">
									<?= lang("Đối tác giao hàng", "doitac"); ?>
																		
										<?php
										echo form_input('doitac', (isset($_POST['doitac']) ? $_POST['doitac'] : ""), 'id="sldoitacaddpt" data-placeholder="' . lang("select") . ' ' . lang("đối tác") . '" class="form-control input-tip" style="width:100%;"');
										?>
								</div>
							</div>	
							<div class="col-md-4">
								<div class="form-group">
									<?= lang("Phụ Thu", "podiscount"); ?>
									<?php echo form_input('discountok', '', 'class="form-control input-tip" id="podiscount"'); ?>
								</div>
							</div>
							<div class="col-md-4">
								<div class="form-group">
									File
									<input id="document" type="file" data-browse-label="<?= lang('browse'); ?>" name="document" data-show-upload="false"
										   data-show-preview="false" class="form-control file">
								</div>
							</div>
							<div class="col-md-4">
								<div class="form-group">
									<?= lang("payment_status", "slpayment_status"); ?>
									<?php $pst = array('pending' => lang('pending'), 'due' => lang('due'), 'partial' => lang('partial'), 'paid' => lang('paid'));
									echo form_dropdown('payment_status', $pst, '', 'class="form-control input-tip" required="required" id="slpayment_status"'); ?>

								</div>
							</div>
							
							<div class="col-md-4" style="display:none">
								<div class="form-group">
									<?= lang("payment_term", "slpayment_term"); ?>
									<?php echo form_input('payment_term', '', 'class="form-control tip" data-trigger="focus" data-placement="top" title="' . lang('payment_term_tip') . '" id="slpayment_term"'); ?>

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
														<select name="paid_by" id="paid_by_1" class="form-control paid_by">
															<?= $this->sma->paid_opts_lhson(); ?>
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
							<div class="col-md-4" id="bt">
								<div class="form-group _note_add_i">
									<?= lang("return_note", "renote"); ?>
									<?php echo form_textarea('note', (isset($_POST['note']) ? $_POST['note'] : ""), 'class="form-control" id="renote" style="margin-top: 10px; height: 100px;"'); ?>

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
							<input type="hidden" name="total_items" value="" id="total_items"/>

						</div>

                        <div style="height:15px; clear: both;"></div>
                       
                        <input type="hidden" name="total_items" value="" id="total_items"/>
                        <input type="hidden" name="order_tax" value="" id="retax2" />
                        <input type="hidden" name="discount" value="" id="rediscount"/>

                    </div>
                </div>
            </div>
        </div>
    </div>
<?php echo form_close(); ?>	
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
                
</div>
<style>
div#s2id_posupplier a,div#s2id_powarehouse a {
    width: 100%;
    float: left;
}

div#s2id_posupplier,div#s2id_powarehouse {
    width: 100%!important;
    float: right;
}

</style>