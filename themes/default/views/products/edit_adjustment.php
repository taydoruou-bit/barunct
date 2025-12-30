<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script type="text/javascript">
    var count = 1, an = 1;
    var type_opt = {'addition': '<?= lang('addition'); ?>', 'subtraction': '<?= lang('subtraction'); ?>'};
    $(document).ready(function () {
        if (localStorage.getItem('remove_qals')) {
            if (localStorage.getItem('qaitems')) {
                localStorage.removeItem('qaitems');
            }
            if (localStorage.getItem('qaref')) {
                localStorage.removeItem('qaref');
            }
            if (localStorage.getItem('qawarehouse')) {
                localStorage.removeItem('qawarehouse');
            }
            if (localStorage.getItem('qanote')) {
                localStorage.removeItem('qanote');
            }
            if (localStorage.getItem('qadate')) {
                localStorage.removeItem('qadate');
            }
            localStorage.removeItem('remove_qals');
        }
        <?php if ($adjustment) { ?>
        localStorage.setItem('qadate', '<?= $this->sma->hrld($adjustment->date); ?>');
        localStorage.setItem('qaref', '<?= $adjustment->reference_no; ?>');
        localStorage.setItem('qawarehouse', '<?= $adjustment->warehouse_id; ?>');
        localStorage.setItem('qanote', '<?= str_replace(array("\r", "\n"), "", $this->sma->decode_html($adjustment->note)); ?>');
        localStorage.setItem('qaitems', JSON.stringify(<?= $adjustment_items; ?>));
        localStorage.setItem('remove_qals', '1');
        <?php } ?>
        
        $("#add_item").autocomplete({
            source: '<?= site_url('products/qa_suggestions'); ?>',
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
                else if (ui.content.length == 1 && ui.content[0].id != 0) {
                    ui.item = ui.content[0];
                    $(this).data('ui-autocomplete')._trigger('select', 'autocompleteselect', ui);
                    $(this).autocomplete('close');
                    $(this).removeClass('ui-autocomplete-loading');
                }
                else if (ui.content.length == 1 && ui.content[0].id == 0) {
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
                    var row = add_adjustment_item(ui.item);
                    if (row)
                        $(this).val('');
                } else {
                    bootbox.alert('<?= lang('no_match_found') ?>');
                }
            }
        });
    });
</script>

<div class="box lhson_dieuchinh_add">
	<?php
		$attrib = array('data-toggle' => 'validator', 'role' => 'form');
		echo form_open_multipart("products/edit_adjustment/".$adjustment->id, $attrib);
		?>
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-plus"></i><?= lang('edit_adjustment'); ?></h2>
		<div class="main-task-lhson nhapkho">
			<button type="submit" class="btn btn-primary btncls" name="edit_adjustment" id="edit_adjustment">
				<i class="fa fa-save"></i>
				Cập nhật
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
                    <div class="col-lg-12 no-padding-lhson">
						<div class="col-md-8">
							<div class="col-md-12" id="sticker">
								<div class="form-group" style="margin-bottom:0;">
									<div class="input-group wide-tip full-width">
										<?php echo form_input('add_item', '', 'class="form-control input-lg" id="add_item" placeholder="' . lang("add_product_to_order") . '"'); ?>
									</div>
								</div>
							</div>

							<div class="col-md-12">
								<div class="control-group table-group">
									<div class="controls table-controls">
										<table id="qaTable" class="table items table-striped table-bordered table-condensed table-hover">
											<thead>
											<tr>
												<th><?= lang("product_name") . " (" . lang("product_code") . ")"; ?></th>
												<th class="col-md-2"><?= lang("variant"); ?></th>
												<th class="col-md-1"><?= lang("type"); ?></th>
												<th class="col-md-1"><?= lang("quantity"); ?></th>
												<?php
												if ($Settings->product_serial) {
													echo '<th class="col-md-4">' . lang("serial_no") . '</th>';
												}
												?>
												<th style="max-width: 30px !important; text-align: center;">
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
						<div class="col-md-4 _dieuchinh_add">
							<?php if ($Owner || $Admin) { ?>
								<div class="col-md-4">
									<div class="form-group">
										<?= lang("date", "qadate"); ?>
										<?php echo form_input('date', (isset($_POST['date']) ? $_POST['date'] : $this->sma->hrld($adjustment->date)), 'class="form-control input-tip datetime" id="qadate" required="required"'); ?>
									</div>
								</div>
							<?php } ?>

							<div class="col-md-4">
								<div class="form-group">
									<?= lang("reference_no", "qaref"); ?>
									<?php echo form_input('reference_no', (isset($_POST['reference_no']) ? $_POST['reference_no'] : $adjustment->reference_no), 'class="form-control input-tip" id="qaref"'); ?>
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
										echo form_dropdown('warehouse', $wh, (isset($_POST['warehouse']) ? $_POST['warehouse'] : $adjustment->warehouse_id), 'id="qawarehouse" class="form-control input-tip select" data-placeholder="' . lang("select") . ' ' . lang("warehouse") . '" required="required" style="width:100%;" ');
										?>
									</div>
								</div>
								<?php } else {
									$warehouse_input = array(
										'type' => 'hidden',
										'name' => 'warehouse',
										'id' => 'qawarehouse',
										'value' => $this->session->userdata('warehouse_id'),
										);

									echo form_input($warehouse_input);
								} ?>
							<div class="col-md-4">
								<div class="form-group">
									<?= lang("document", "document") ?>
									<input id="document" type="file" data-browse-label="<?= lang('browse'); ?>" name="document" data-show-upload="false"
										   data-show-preview="false" class="form-control file">
								</div>
							</div>
							<div class="col-md-4">
								<div class="form-group">
									<?= lang("note", "qanote"); ?>
									<?php echo form_textarea('note', (isset($_POST['note']) ? $_POST['note'] : ""), 'class="form-control" id="qanote" style="margin-top: 10px; height: 100px;"'); ?>
								</div>
							</div>
							<div class="clearfix"></div>
						</div>	
                    </div>
                </div>         

            </div>

        </div>
    </div>
	 <?php echo form_close(); ?>
</div>
