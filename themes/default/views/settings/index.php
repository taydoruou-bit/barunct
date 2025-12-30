<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php
$wm = array('0' => lang('no'), '1' => lang('yes'));
$ps = array('0' => lang("disable"), '1' => lang("enable"));
?>
<script>
    $(document).ready(function () {
        <?php if(isset($message)) { echo 'localStorage.clear();'; } ?>
        var timezones = <?= json_encode(DateTimeZone::listIdentifiers(DateTimeZone::ALL)); ?>;
        $('#timezone').autocomplete({
            source: timezones
        });
        if ($('#protocol').val() == 'smtp') {
            $('#smtp_config').slideDown();
        } else if ($('#protocol').val() == 'sendmail') {
            $('#sendmail_config').slideDown();
        }
        $('#protocol').change(function () {
            if ($(this).val() == 'smtp') {
                $('#sendmail_config').slideUp();
                $('#smtp_config').slideDown();
            } else if ($(this).val() == 'sendmail') {
                $('#smtp_config').slideUp();
                $('#sendmail_config').slideDown();
            } else {
                $('#smtp_config').slideUp();
                $('#sendmail_config').slideUp();
            }
        });
        $('#overselling').change(function () {
            if ($(this).val() == 1) {
                if ($('#accounting_method').select2("val") != 2) {
                   // bootbox.alert('<?=lang('overselling_will_only_work_with_AVCO_accounting_method_only')?>');
                    //$('#accounting_method').select2("val", '2');
                }
            }
        });
        $('#accounting_method').change(function () {
            var oam = <?=$Settings->accounting_method?>, nam = $(this).val();
            if (oam != nam) {
                bootbox.alert('<?=lang('accounting_method_change_alert')?>');
            }
        });
        $('#accounting_method').change(function () {
            if ($(this).val() != 2) {
                if ($('#overselling').select2("val") == 1) {
                   // bootbox.alert('<?=lang('overselling_will_only_work_with_AVCO_accounting_method_only')?>');
                    //$('#overselling').select2("val", 0);
                }
            }
        });
        $('#item_addition').change(function () {
            if ($(this).val() == 1) {
                bootbox.alert('<?=lang('product_variants_feature_x')?>');
            }
        });
        var sac = $('#sac').val()
        if(sac == 1) {
            $('.nsac').slideUp();
        } else {
            $('.nsac').slideDown();
        }
        $('#sac').change(function () {
            if ($(this).val() == 1) {
                $('.nsac').slideUp();
            } else {
                $('.nsac').slideDown();
            }
        });
    });
</script>
<style>
fieldset.scheduler-border {
    float: left;
    width: 100%;
}
</style>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-cog"></i><?= lang('system_settings'); ?></h2>

        <!--<div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown"><a href="<?= site_url('system_settings/paypal') ?>" class="toggle_up"><i
                            class="icon fa fa-paypal"></i><span
                            class="padding-right-10"><?= lang('paypal'); ?></span></a></li>
                <li class="dropdown"><a href="<?= site_url('system_settings/skrill') ?>" class="toggle_down"><i
                            class="icon fa fa-bank"></i><span class="padding-right-10"><?= lang('skrill'); ?></span></a>
                </li>
            </ul>
        </div>-->
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <p class="introtext"><?= lang('update_info'); ?></p>

                <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
                echo form_open_multipart("system_settings", $attrib);
                ?>
                <div class="row">
                    <div class="col-lg-12">                        
                        <fieldset class="scheduler-border">
                            <legend class="scheduler-border"><?= lang('site_config') ?></legend>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang("site_name", "site_name"); ?>
                                    <?= form_input('site_name', $Settings->site_name, 'class="form-control tip" id="site_name"  required="required"'); ?>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang("language", "language"); ?>
                                    <?php
                               
									$lang = array(
                                        'vietnamese'                => 'Tiếng việt',
									); 
                                    echo form_dropdown('language', $lang, $Settings->language, 'class="form-control tip" id="language" required="required" style="width:100%;"');
                                    ?>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="currency"><?= lang("default_currency"); ?></label>

                                    <div class="controls"> <?php
                                        foreach ($currencies as $currency) {
                                            $cu[$currency->code] = $currency->name;
                                        }
                                        echo form_dropdown('currency', $cu, $Settings->default_currency, 'class="form-control tip" id="currency" required="required" style="width:100%;"');
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang("accounting_method", "accounting_method"); ?>
                                    <?php
                                    $am = array(0 => 'FIFO (Nhập trước xuất trước)');
                                    echo form_dropdown('accounting_method', $am, $Settings->accounting_method, 'class="form-control tip" id="accounting_method" required="required" style="width:100%;"');
                                    ?>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
									<label class="control-label" for="email"><?= lang("default_email"); ?></label>

									<?= form_input('email', $Settings->default_email, 'class="form-control tip" required="required" id="email"'); ?>
								</div>
							</div>
						<div class="col-md-4">
							<div class="form-group">
								<label class="control-label" for="customer_group"><?= lang("default_customer_group"); ?></label>
								<?php
									foreach ($customer_groups as $customer_group) {
										$pgs[$customer_group->id] = $customer_group->name;
									}
									echo form_dropdown('customer_group', $pgs, $Settings->customer_group, 'class="form-control tip" id="customer_group" style="width:100%;" required="required"');
								?>
							</div>
						</div>
						<div class="col-md-4">
							<div class="form-group">
								<label class="control-label" for="price_group"><?= lang("default_price_group"); ?></label>
								<?php
									foreach ($price_groups as $price_group) {
										$cgs[$price_group->id] = $price_group->name;
									}
									echo form_dropdown('price_group', $cgs, $Settings->price_group, 'class="form-control tip" id="price_group" style="width:100%;" required="required"');
								?>
							</div>
						</div>
						<div class="col-md-4">
							<div class="form-group">
								<?= lang('maintenance_mode', 'mmode'); ?>
								<div class="controls">  <?php
									echo form_dropdown('mmode', $wm, (isset($_POST['mmode']) ? $_POST['mmode'] : $Settings->mmode), 'class="tip form-control" required="required" id="mmode" style="width:100%;"');
									?> </div>
							</div>
						</div>
						<div class="col-md-4">
							<div class="form-group">
								<label class="control-label" for="theme"><?= lang("theme"); ?></label>

								<div class="controls">
									<?php
									$themes = array(
										'default' => 'Default'
									);
									echo form_dropdown('theme', $themes, $Settings->theme, 'id="theme" class="form-control tip" required="required" style="width:100%;"');
									?>
								</div>
							</div>
						</div>
						<div class="col-md-4">
							<div class="form-group">
								<label class="control-label" for="rtl"><?= lang("rtl_support"); ?></label>

								<div class="controls">
									<?php
									echo form_dropdown('rtl', $ps, $Settings->rtl, 'id="rtl" class="form-control tip" required="required" style="width:100%;"');
									?>
								</div>
							</div>
						</div>
						<div class="col-md-4">
							<div class="form-group">
								<label class="control-label" for="captcha"><?= lang("login_captcha"); ?></label>

								<div class="controls">
									<?php
									echo form_dropdown('captcha', $ps, $Settings->captcha, 'id="captcha" class="form-control tip" required="required" style="width:100%;"');
									?>
								</div>
							</div>
						</div>
						<div class="col-md-4">
							<div class="form-group">
								<label class="control-label" for="disable_editing"><?= lang("disable_editing"); ?></label>
								<?= form_input('disable_editing', $Settings->disable_editing, 'class="form-control tip" id="disable_editing" required="required"'); ?>
							</div>
						</div>
						<div class="col-md-4">
							<div class="form-group">
								<label class="control-label" for="rows_per_page"><?= lang("rows_per_page"); ?></label>
								<?= form_input('rows_per_page', $Settings->rows_per_page, 'class="form-control tip" id="rows_per_page" required="required"'); ?>
							</div>
						</div>
						<div class="col-md-4">
							<div class="form-group">
								<label class="control-label" for="dateformat"><?= lang("dateformat"); ?></label>

								<div class="controls">
									<?php
									foreach ($date_formats as $date_format) {
										$dt[$date_format->id] = $date_format->js;
									}
									echo form_dropdown('dateformat', $dt, $Settings->dateformat, 'id="dateformat" class="form-control tip" style="width:100%;" required="required"');
									?>
								</div>
							</div>
						</div>
						<div class="col-md-4">
							<div class="form-group">
								<label class="control-label" for="timezone"><?= lang("timezone"); ?></label>
								<?php
								$timezone_identifiers = DateTimeZone::listIdentifiers();
								foreach ($timezone_identifiers as $tzi) {
									$tz[$tzi] = $tzi;
								}
								?>
								<?= form_dropdown('timezone', $tz, TIMEZONE, 'class="form-control tip" id="timezone" required="required"'); ?>
							</div>
						</div>
						
						<div class="col-md-4">
							<div class="form-group">
								<label class="control-label"
									   for="restrict_calendar"><?= lang("calendar"); ?></label>

								<div class="controls">
									<?php
									$opt_cal = array(1 => lang('private'), 0 => lang('shared'));
									echo form_dropdown('restrict_calendar', $opt_cal, $Settings->restrict_calendar, 'class="form-control tip" required="required" id="restrict_calendar" style="width:100%;"');
									?>
								</div>
							</div>
						</div>
						<div class="col-md-4">
							<div class="form-group">
								<label class="control-label"
									   for="warehouse"><?= lang("default_warehouse"); ?></label>

								<div class="controls"> <?php
									foreach ($warehouses as $warehouse) {
										$wh[$warehouse->id] = $warehouse->name . ' (' . $warehouse->code . ')';
									}
									echo form_dropdown('warehouse', $wh, $Settings->default_warehouse, 'class="form-control tip" id="warehouse" required="required" style="width:100%;"');
									?>
								</div>
							</div>
						</div>
						<div class="col-md-4">
							<div class="form-group">
								<?= lang("default_biller", "biller"); ?>
								<?php
								$bl[""] = "";
								foreach ($billers as $biller) {
									$bl[$biller->id] = $biller->company != '-' ? $biller->company : $biller->name;
								}
								echo form_dropdown('biller', $bl, (isset($_POST['biller']) ? $_POST['biller'] : $Settings->default_biller), 'id="biller" data-placeholder="' . lang("select") . ' ' . lang("biller") . '" required="required" class="form-control input-tip select" style="width:100%;"');
								?>
							</div>
						</div>
                    </fieldset>

                    <fieldset class="scheduler-border">
                        <legend class="scheduler-border"><?= lang('products') ?></legend>
                        <div class="col-md-2" style="display: none">
                            <div class="form-group">
                                <?= lang("product_tax", "tax_rate"); ?>
                                <?php
                                echo form_dropdown('tax_rate', $ps, $Settings->default_tax_rate, 'class="form-control tip" id="tax_rate" required="required" style="width:100%;"');
                                ?>
                            </div>
                        </div>	
                        <div class="col-md-2">
								<div class="form-group">
									<?= lang('Khuyến mãi', 'khuyenmai'); ?>
									<div class="controls">
										<?php
										echo form_dropdown('khuyenmai', $ps, $Settings->khuyenmai, 'id="khuyenmai" class="form-control tip" required="required" style="width:100%;"');
										?>
									</div>
								</div>
							</div>					
						<div class="col-md-2">                          
							<div class="form-group">                               
							<label class="control-label" for="auto_mavach"><?= lang("product_auto_mavach"); ?></label>       
								<div class="controls">                                 
									<?php  echo form_dropdown('auto_mavach', $ps, $Settings->auto_mavach, 'id="auto_mavach" class="form-control tip" required="required" style="width:100%;"'); ?>                              
								</div>                          
							</div>                        
						</div>
                        <div class="col-md-4">
							<div class="col-md-6">
								<div class="form-group">
									<label class="control-label" for="racks"><?= lang("racks"); ?></label>

									<div class="controls">
										<?php
										echo form_dropdown('racks', $ps, $Settings->racks, 'id="racks" class="form-control tip" required="required" style="width:100%;"');
										?>
									</div>
								</div>
							</div>
							<div class="col-md-6">
								<div class="form-group">
									<label class="control-label" for="racks"><?= lang("trung_ten"); ?></label>

									<div class="controls">
										<?php
										echo form_dropdown('trung_ten', $ps, $Settings->trung_ten, 'id="trung_ten" class="form-control tip" required="required" style="width:100%;"');
										?>
									</div>
								</div>
							</div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label" for="attributes"><?= lang("attributes"); ?></label>

                                <div class="controls">
                                    <?php
                                    echo form_dropdown('attributes', $ps, $Settings->attributes, 'id="attributes" class="form-control tip"  required="required" style="width:100%;"');
                                    ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label"
                                       for="product_expiry"><?= lang("product_expiry"); ?></label>

                                <div class="controls">
                                    <?php
                                    echo form_dropdown('product_expiry', $ps, $Settings->product_expiry, 'id="product_expiry" class="form-control tip" required="required" style="width:100%;"');
                                    ?>
                                </div>
                            </div>
                        </div>						
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label"
                                       for="remove_expired"><?= lang("remove_expired"); ?></label>

                                <div class="controls">
                                    <?php
                                    $re_opts = array(0 => lang('no').', '.lang('i_ll_remove'), 1 => lang('yes').', '.lang('remove_automatically'));
                                    echo form_dropdown('remove_expired', $re_opts, $Settings->remove_expired, 'id="remove_expired" class="form-control tip" required="required" style="width:100%;"');
                                    ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label" for="image_size"><?= lang("image_size"); ?> (Width :
                                    Height) *</label>

                                <div class="row">
                                    <div class="col-xs-6">
                                        <?= form_input('iwidth', $Settings->iwidth, 'class="form-control tip" id="iwidth" placeholder="image width" required="required"'); ?>
                                    </div>
                                    <div class="col-xs-6">
                                        <?= form_input('iheight', $Settings->iheight, 'class="form-control tip" id="iheight" placeholder="image height" required="required"'); ?></div>
                                </div>
                                <div class="clearfix"></div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label" for="thumbnail_size"><?= lang("thumbnail_size"); ?>
                                    (Width : Height) *</label>

                                <div class="row">
                                    <div class="col-xs-6">
                                        <?= form_input('twidth', $Settings->twidth, 'class="form-control tip" id="twidth" placeholder="thumbnail width" required="required"'); ?>
                                    </div>
                                    <div class="col-xs-6">
                                        <?= form_input('theight', $Settings->theight, 'class="form-control tip" id="theight" placeholder="thumbnail height" required="required"'); ?>
                                    </div>
                                </div>
                                <div class="clearfix"></div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang('watermark', 'watermark'); ?>
                                <?php
                                    echo form_dropdown('watermark', $wm, (isset($_POST['watermark']) ? $_POST['watermark'] : $Settings->watermark), 'class="tip form-control" required="required" id="watermark" style="width:100%;"');
                                ?>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang('display_all_products', 'display_all_products'); ?>
                                <?php
                                    $dopts = array(0 => lang('hide_with_0_qty'), 1 => lang('show_with_0_qty'));
                                    echo form_dropdown('display_all_products', $dopts, (isset($_POST['display_all_products']) ? $_POST['display_all_products'] : $Settings->display_all_products), 'class="tip form-control" required="required" id="display_all_products" style="width:100%;"');
                                ?>
                            </div>
                        </div>
                        <div class="col-md-4">
							<div class="col-md-6">
								<div class="form-group">
									<?= lang('barcode_separator', 'barcode_separator'); ?>
									<?php
										$bcopts = array('-' => lang('-'), '.' => lang('.'), '~' => lang('~'), '_' => lang('_'));
										echo form_dropdown('barcode_separator', $bcopts, (isset($_POST['barcode_separator']) ? $_POST['barcode_separator'] : $Settings->barcode_separator), 'class="tip form-control" required="required" id="barcode_separator" style="width:100%;"');
									?>
								</div>
							</div>
							<div class="col-md-6">
									<div class="form-group">
										<?= lang('barcode_renderer', 'barcode_renderer'); ?>
										<?php
											$bcropts = array(1 => lang('image'), 0 => lang('svg'));
											echo form_dropdown('barcode_renderer', $bcropts, (isset($_POST['barcode_renderer']) ? $_POST['barcode_renderer'] : $Settings->barcode_img), 'class="tip form-control" required="required" id="barcode_renderer" style="width:100%;"');
										?>
									</div>
							</div>
                        </div>  
						<div class="col-md-4">
							<div class="col-md-6">
								<div class="form-group">
									<?= lang('Thương hiệu', 'thuonghieu'); ?>
									<div class="controls">
										<?php
										echo form_dropdown('thuonghieu', $ps, $Settings->thuonghieu, 'id="thuonghieu" class="form-control tip" required="required" style="width:100%;"');
										?>
									</div>
								</div>
							</div>
							<div class="col-md-6">
								<div class="form-group">
									<?= lang('Xuất xứ', 'xuatxu'); ?>
									<div class="controls">
										<?php
										echo form_dropdown('xuatxu', $ps, $Settings->xuatxu, 'id="xuatxu" class="form-control tip" required="required" style="width:100%;"');
										?>
									</div>
								</div>
							</div>
                        </div> 	
                        <div class="col-md-4">
							<div class="col-md-6">
									<div class="form-group">
										<?= lang('Bảo hành', 'baohanh'); ?>
										<div class="controls">
											<?php
											echo form_dropdown('baohanh', $ps, $Settings->baohanh, 'id="baohanh" class="form-control tip" required="required" style="width:100%;"');
											?>
										</div>
									</div>
								</div>
							<div class="col-md-6">
								<div class="form-group">
									<?= lang('Cập nhật giá nhập', 'update_cost'); ?>
									<?= form_dropdown('update_cost', $wm, $Settings->update_cost, 'class="form-control" id="update_cost" required="required"'); ?>
								</div>
							</div>
                        </div>
						<div class="col-md-4">
							
							<div class="col-md-6">
								<div class="form-group">
									<?= lang('Cảnh báo tồn kho', 'canhbao'); ?>
									<?= form_dropdown('canhbao', $wm, $Settings->canhbao, 'class="form-control" id="canhbao" required="required"'); ?>
								</div>
							</div>
                        </div>
                    </fieldset>

                    <fieldset class="scheduler-border">
                        <legend class="scheduler-border"><?= lang('sales') ?></legend>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label" for="overselling"><?= lang("over_selling"); ?></label>

                                <div class="controls">
                                    <?php
                                    $opt = array(1 => lang('yes'), 0 => lang('no'));
                                    echo form_dropdown('restrict_sale', $opt, $Settings->overselling, 'class="form-control tip" id="overselling" required="required" style="width:100%;"');
                                    ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label"
                                       for="reference_format"><?= lang("reference_format"); ?></label>

                                <div class="controls">
                                    <?php
                                    $ref = array(1 => lang('prefix_year_no'), 2 => lang('prefix_month_year_no'), 3 => lang('sequence_number'), 4 => lang('random_number'),7 => lang('Năm/Tháng/Ngày/STT (2018/12/17/001)'));
                                    echo form_dropdown('reference_format', $ref, $Settings->reference_format, 'class="form-control tip" required="required" id="reference_format" style="width:100%;"');
                                    ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang("invoice_tax", "tax_rate2"); ?>
                                <?php $tr['0'] = lang("disable");
                                foreach ($tax_rates as $rate) {
                                    $tr[$rate->id] = $rate->name;
                                }
                                echo form_dropdown('tax_rate2', $tr, $Settings->default_tax_rate2, 'id="tax_rate2" class="form-control tip" required="required" style="width:100%;"'); ?>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label"
                                       for="product_discount"><?= lang("product_level_discount"); ?></label>

                                <div class="controls">
                                    <?php
                                    echo form_dropdown('product_discount', $ps, $Settings->product_discount, 'id="product_discount" class="form-control tip" required="required" style="width:100%;"');
                                    ?>
                                </div>
                            </div>
                        </div>
						 <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label"
                                       for="product_serial"><?= lang("Serial/Imei:"); ?></label>

                                <div class="controls">
                                    <?php
                                    echo form_dropdown('product_serial', $ps, $Settings->product_serial, 'id="product_serial" class="form-control tip" required="required" style="width:100%;"');
                                    ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label"
                                       for="show_img_pos"><?= lang("show_img_pos"); ?></label>

                                <div class="controls">
                                    <?php
                                    echo form_dropdown('show_img_pos', $ps, $Settings->show_img_pos, 'id="show_img_pos" class="form-control tip" required="required" style="width:100%;"');
                                    ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label"
                                       for="detect_barcode"><?= lang("auto_detect_barcode"); ?></label>

                                <div class="controls">
                                    <?php
                                    echo form_dropdown('detect_barcode', $ps, $Settings->auto_detect_barcode, 'id="detect_barcode" class="form-control tip" required="required" style="width:100%;"');
                                    ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label" for="bc_fix"><?= lang("bc_fix"); ?></label>


                                <?= form_input('bc_fix', $Settings->bc_fix, 'class="form-control tip" required="required" id="bc_fix"'); ?>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label"
                                       for="item_addition"><?= lang("item_addition"); ?></label>

                                <div class="controls">
                                    <?php
                                    $ia = array(0 => lang('add_new_item'), 1 => lang('increase_quantity_if_item_exist'));
                                    echo form_dropdown('item_addition', $ia, $Settings->item_addition, 'id="item_addition" class="form-control tip" required="required" style="width:100%;"');
                                    ?>
                                </div>
                            </div>
                        </div>
						 <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label"
                                       for="item_print"><?= lang("In hóa đơn"); ?></label>

                                <div class="controls">
                                    <?php
                                    $ia = array(0 => lang('Mặc định theo mẫu in'), 1 => lang('In chỉ có số lượng'),2 => lang('In theo mẫu không có nợ củ'));
                                    echo form_dropdown('item_print', $ia, $Settings->item_print, 'id="item_print" class="form-control tip" required="required" style="width:100%;"');
                                    ?>
                                </div>
                            </div>
                        </div>
                        <!--<div class="col-md-4">
                            <div class="form-group">
                                <?= lang("set_focus", "set_focus"); ?>
                                <?php
                                $sfopts = array(0 => lang('add_item_input'), 1 => lang('last_order_item'));
                                echo form_dropdown('set_focus', $sfopts, (isset($_POST['set_focus']) ? $_POST['set_focus'] : $Settings->set_focus), 'id="set_focus" data-placeholder="' . lang("select") . ' ' . lang("set_focus") . '" required="required" class="form-control input-tip select" style="width:100%;"');
                                ?>
                            </div>
                        </div>-->
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label" for="invoice_view"><?= lang("invoice_view"); ?></label>

                                <div class="controls">
                                    <?php
                                    $opt_inv = array(1 => lang('tax_invoice'), 0 => lang('standard'));
                                    echo form_dropdown('invoice_view', $opt_inv, $Settings->invoice_view, 'class="form-control tip" required="required" id="invoice_view" style="width:100%;"');
                                    ?>
                                </div>
                            </div>
                        </div>
						 <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label"
                                       for="use_gia_si"><?= lang("use_gia_si"); ?></label>

                                <div class="controls">
                                    <?php
                                    echo form_dropdown('use_gia_si', $ps, $Settings->use_gia_si, 'id="use_gia_si" class="form-control tip" required="required" style="width:100%;"');
                                    ?>
                                </div>
                            </div>
                        </div>
                    </fieldset>
					<!--<fieldset class="scheduler-border">
                        <legend class="scheduler-border"><?= lang('purchases') ?></legend>
                        <div class="col-md-4">
                             <div class="form-group">
                                <label class="control-label"
                                       for="chiec_khau">Chiếc khấu (5/5%) nhập hàng sau giảm </label>

                                <div class="controls">
                                    <?php
                                    //echo form_dropdown('chiec_khau', $ps, $Settings->chiec_khau, 'id="chiec_khau" class="form-control tip" required="required" style="width:100%;"');
                                    ?>
                                </div>
                            </div>
                        </div>
                    </fieldset> -->
                    <fieldset class="scheduler-border">
                        <legend class="scheduler-border"><?= lang('prefix') ?></legend>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label" for="sales_prefix"><?= lang("sales_prefix"); ?></label>

                                <?= form_input('sales_prefix', $Settings->sales_prefix, 'class="form-control tip" id="sales_prefix"'); ?>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label"
                                       for="return_prefix"><?= lang("return_prefix"); ?></label>

                                <?= form_input('return_prefix', $Settings->return_prefix, 'class="form-control tip" id="return_prefix"'); ?>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label" for="payment_prefix"><?= lang("payment_prefix"); ?></label>
                                <?= form_input('payment_prefix', $Settings->payment_prefix, 'class="form-control tip" id="payment_prefix"'); ?>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label" for="ppayment_prefix"><?= lang("Thanh toán công nợ"); ?></label>
                                <?= form_input('ppayment_prefix', $Settings->ppayment_prefix, 'class="form-control tip" id="ppayment_prefix"'); ?>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label"
                                       for="delivery_prefix"><?= lang("delivery_prefix"); ?></label>

                                <?= form_input('delivery_prefix', $Settings->delivery_prefix, 'class="form-control tip" id="delivery_prefix"'); ?>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label" for="quote_prefix"><?= lang("quote_prefix"); ?></label>

                                <?= form_input('quote_prefix', $Settings->quote_prefix, 'class="form-control tip" id="quote_prefix"'); ?>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label"
                                       for="purchase_prefix"><?= lang("purchase_prefix"); ?></label>

                                <?= form_input('purchase_prefix', $Settings->purchase_prefix, 'class="form-control tip" id="purchase_prefix"'); ?>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label"
                                       for="returnp_prefix"><?= lang("returnp_prefix"); ?></label>

                                <?= form_input('returnp_prefix', $Settings->returnp_prefix, 'class="form-control tip" id="returnp_prefix"'); ?>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label"
                                       for="transfer_prefix"><?= lang("transfer_prefix"); ?></label>
                                <?= form_input('transfer_prefix', $Settings->transfer_prefix, 'class="form-control tip" id="transfer_prefix"'); ?>
                            </div>
                        </div>
						<div class="col-md-4">
                            <div class="form-group">
                                <?= lang('qa_prefix', 'qa_prefix'); ?>
                                <?= form_input('qa_prefix', $Settings->qa_prefix, 'class="form-control tip" id="qa_prefix"'); ?>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang('Phiếu chi', 'expense_prefix'); ?>
                                <?= form_input('expense_prefix', $Settings->expense_prefix, 'class="form-control tip" id="expense_prefix"'); ?>
                            </div>
                        </div>
                        
						<div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label"
                                       for="thu_prefix"><?= lang("Phiếu thu"); ?></label>

                                <?= form_input('thu_prefix', $Settings->thu_prefix, 'class="form-control tip" id="thu_prefix"'); ?>
                            </div>
                        </div>
                    </fieldset>

                    <fieldset class="scheduler-border">
                        <legend class="scheduler-border"><?= lang('money_number_format') ?></legend>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label" for="decimals"><?= lang("decimals"); ?></label>

                                <div class="controls"> <?php
                                    $decimals = array(0 => lang('disable'), 1 => '1', 2 => '2', 3 => '3', 4 => '4');
                                    echo form_dropdown('decimals', $decimals, $Settings->decimals, 'class="form-control tip" id="decimals"  style="width:100%;" required="required"');
                                    ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label" for="qty_decimals"><?= lang("qty_decimals"); ?></label>

                                <div class="controls"> <?php
                                    $qty_decimals = array(0 => lang('disable'), 1 => '1', 2 => '2', 3 => '3', 4 => '4');
                                    echo form_dropdown('qty_decimals', $qty_decimals, $Settings->qty_decimals, 'class="form-control tip" id="qty_decimals"  style="width:100%;" required="required"');
                                    ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang('sac', 'sac'); ?>
                                <?= form_dropdown('sac', $ps, set_value('sac', $Settings->sac), 'class="form-control tip" id="sac"  required="required"'); ?>
                            </div>
                        </div>
                        <div class="clearfix"></div>
                        <div class="nsac">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="decimals_sep"><?= lang("decimals_sep"); ?></label>

                                    <div class="controls"> <?php
                                        $dec_point = array('.' => lang('dot'), ',' => lang('comma'));
                                        echo form_dropdown('decimals_sep', $dec_point, $Settings->decimals_sep, 'class="form-control tip" id="decimals_sep"  style="width:100%;" required="required"');
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="thousands_sep"><?= lang("thousands_sep"); ?></label>
                                    <div class="controls"> <?php
                                        $thousands_sep = array('.' => lang('dot'), ',' => lang('comma'), '0' => lang('space'));
                                        echo form_dropdown('thousands_sep', $thousands_sep, $Settings->thousands_sep, 'class="form-control tip" id="thousands_sep"  style="width:100%;" required="required"');
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang('display_currency_symbol', 'display_symbol'); ?>
                                <?php $opts = array(0 => lang('disable'), 1 => lang('before'), 2 => lang('after')); ?>
                                <?= form_dropdown('display_symbol', $opts, $Settings->display_symbol, 'class="form-control" id="display_symbol" style="width:100%;" required="required"'); ?>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang('currency_symbol', 'symbol'); ?>
                                <?= form_input('symbol', $Settings->symbol, 'class="form-control" id="symbol" style="width:100%;"'); ?>
                            </div>
                        </div>
                    </fieldset>

                    <fieldset class="scheduler-border">
                        <legend class="scheduler-border"><?= lang('email') ?></legend>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label" for="protocol"><?= lang("email_protocol"); ?></label>

                                <div class="controls"> <?php
                                    //$popt = array('mail' => 'PHP Mail Function', 'sendmail' => 'Send Mail', 'smtp' => 'SMTP');
                                    $popt = array('smtp' => 'SMTP');
                                    echo form_dropdown('protocol', $popt, $Settings->protocol, 'class="form-control tip" id="protocol"  style="width:100%;" required="required"');
                                    ?>
                                </div>
                            </div>
                        </div>
                        <div class="clearfix"></div>
                        <div class="row" id="sendmail_config" style="display:none">
                            <div class="col-md-12">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="control-label" for="mailpath"><?= lang("mailpath"); ?></label>

                                        <?= form_input('mailpath', $Settings->mailpath, 'class="form-control tip" id="mailpath"'); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="clearfix"></div>
                        <div class="row" id="smtp_config" >
                            <div class="col-md-12">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="control-label"
                                               for="smtp_host"><?= lang("smtp_host"); ?></label>

                                        <?= form_input('smtp_host', $Settings->smtp_host, 'class="form-control tip" id="smtp_host"'); ?>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="control-label"
                                               for="smtp_user"><?= lang("smtp_user"); ?></label>

                                        <?= form_input('smtp_user', $Settings->smtp_user, 'class="form-control tip" id="smtp_user"'); ?> </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="control-label"
                                               for="smtp_pass"><?= lang("smtp_pass"); ?></label>

                                        <?= form_password('smtp_pass', $Settings->smtp_pass, 'class="form-control tip" id="smtp_pass"'); ?> </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="control-label"
                                               for="smtp_port"><?= lang("smtp_port"); ?></label>

                                        <?= form_input('smtp_port', $Settings->smtp_port, 'class="form-control tip" id="smtp_port"'); ?> </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="control-label"
                                               for="smtp_crypto"><?= lang("smtp_crypto"); ?></label>

                                        <div class="controls"> <?php
                                            $crypto_opt = array('' => lang('none'), 'tls' => 'TLS', 'ssl' => 'SSL');
                                            echo form_dropdown('smtp_crypto', $crypto_opt, $Settings->smtp_crypto, 'class="form-control tip" id="smtp_crypto"');
                                            ?> </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </fieldset>

                    <fieldset class="scheduler-border">
                        <legend class="scheduler-border"><?= lang('award_points') ?></legend>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="control-label"><?= lang("customer_award_points"); ?></label>

                                <div class="row">
                                    <div class="col-sm-4 col-xs-6">
                                        <?= lang('each_spent'); ?><br>
                                        <?= form_input('each_spent', $this->sma->formatDecimal($Settings->each_spent), 'class="form-control"'); ?>
                                    </div>
                                    <div class="col-sm-1 col-xs-1 text-center"><i class="fa fa-arrow-right"></i>
                                    </div>
                                    <div class="col-sm-4 col-xs-5">
                                        <?= lang('award_points'); ?><br>
                                        <?= form_input('ca_point', $Settings->ca_point, 'class="form-control"'); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="control-label"><?= lang("staff_award_points"); ?></label>

                                <div class="row">
                                    <div class="col-sm-4 col-xs-6">
                                        <?= lang('each_in_sale'); ?><br>
                                        <?= form_input('each_sale', $this->sma->formatDecimal($Settings->each_sale), 'class="form-control"'); ?>
                                    </div>
                                    <div class="col-sm-1 col-xs-1 text-center"><i class="fa fa-arrow-right"></i>
                                    </div>
                                    <div class="col-sm-4 col-xs-5">
                                        <?= lang('award_points'); ?><br>
                                        <?= form_input('sa_point', $Settings->sa_point, 'class="form-control"'); ?>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </fieldset>
                    <fieldset class="scheduler-border">
                        <legend class="scheduler-border"><?= lang('Tích điểm theo gram') ?></legend>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="control-label"><?= lang("customer_award_points"); ?></label>

                                <div class="row">
                                    <div class="col-sm-4 col-xs-6">
                                        <?= lang('Mỗi <i class="fa fa-arrow-down"></i> gram bán được bằng'); ?><br>
                                        <?= form_input('each_gram', $this->sma->formatDecimal($Settings->each_gram), 'class="form-control"'); ?>
                                    </div>
                                    <div class="col-sm-1 col-xs-1 text-center"><i class="fa fa-arrow-right"></i>
                                    </div>
                                    <div class="col-sm-4 col-xs-5">
                                        <?= lang('award_points'); ?><br>
                                        <?= form_input('ca_gram', $Settings->ca_gram, 'class="form-control"'); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </fieldset>
					<fieldset class="scheduler-border">
                        <legend class="scheduler-border">API Woocommerce Wordpress - Multiple Site V2</legend>
                        <div class="col-md-12">
                        	<div class="form-group">
								<input type="checkbox" class="checkbox" value="1" name="autosync" id="autosync" <?= $Settings->autosync==1 ? 'checked="checked"' : ''; ?>>
								<label for="promotion" class="padding05">
									<?= lang('Tự đồng đồng bộ kho và giá bán lên website'); ?>
								| CronJob: */5 * * * * curl <?=base_url()?>auth/autoSysnWooStock
								</label>
							</div>
                            <div class="form-group" id="divapiv2">
                            	<?php 
                            	$woo_url=json_decode($Settings->woo_url);
                            	$woo_key=json_decode($Settings->woo_key);
                            	$woo_sec=json_decode($Settings->woo_sec);
                            	$_ch=count($woo_url)+count($woo_key)+count($woo_sec);
                            	

                            	if ($woo_url[0]!='') {                           		
                            	
	                            	foreach ($woo_url as $index=>$value) {
	                            		if ($value!="") {

	                            			?>
	                            			<div class="row api_wp">
			                                    <div class="col-sm-4 col-xs-4">
			                                        Your Store URL<br>
			                                        <?= form_input('woo_url[]',$value, 'class="form-control"'); ?>
			                                    </div>
			                                    <div class="col-sm-4 col-xs-4">
			                                        Your API consumer key<br>
			                                        <?= form_input('woo_key[]', $woo_key[$index], 'class="form-control"'); ?>
			                                    </div>
												<div class="col-sm-4 col-xs-4">
			                                        Your API consumer secret<br>
			                                        <?= form_input('woo_sec[]', $woo_sec[$index], 'class="form-control"'); ?>	                                       
			                                    		                                
			                            			<?php
			                            			if ($index==0) {
			                            				?>
	 													<button id="b1wpapi" class="btn add-more-wp-api" type="button">+</button>
			                            				<?php
			                            			}else{
			                            				?>
			                            				<button class="btn remove-more-wp-api" onclick="removeapiwp(this)" type="button">-</button>
			                            				<?php
			                            			}
			                            			?>
		                            			</div>
	                            			</div>
	                            			<?php
	                            		}
	                            	}
	                            }else{
	                            	?>
	                            	<div class="row api_wp">
	                                    <div class="col-sm-4 col-xs-4">
	                                        Your Store URL<br>
	                                        <?= form_input('woo_url[]','', 'class="form-control"'); ?>
	                                    </div>
	                                    <div class="col-sm-4 col-xs-4">
	                                        Your API consumer key<br>
	                                        <?= form_input('woo_key[]', '', 'class="form-control"'); ?>
	                                    </div>
										<div class="col-sm-4 col-xs-4">
	                                        Your API consumer secret<br>
	                                        <?= form_input('woo_sec[]', '', 'class="form-control"'); ?>	
											<button id="b1wpapi" class="btn add-more-wp-api" type="button">+</button>
	                            				
                            			</div>
                        			</div>
	                            	<?php
	                            }
                            	?>
                                
        						
                            </div>
                        </div>
                    </fieldset>
                    <fieldset class="scheduler-border">
                        <legend class="scheduler-border">CRONJOB</legend>
                        <div class="col-md-12">
                        	<div class="form-group">
								<label for="promotion" class="padding05">
									<?= lang('Tự đồng đồng bộ sản phẩm từ website về hệ thống'); ?>
								| CronJob: * */1 * * * curl <?=base_url()?>auth/SysnApiWoooProductsV3
								</label>
							</div>
							<div class="form-group">
								<label for="promotion" class="padding05">
									<?= lang('Tự đồng đồng bộ đơn hàng từ website về hệ thống'); ?>
								| CronJob: */15 * * * * curl <?=base_url()?>auth/SysnApiWoooOrdersV3
								</label>
							</div>
							<div class="form-group">
								<label for="promotion" class="padding05">
									<?= lang('Tự đồng backup database gửi về email mỗi ngày'); ?>
								| CronJob: 0 23 * * * curl <?=base_url()?>auth/AutoBackup
								</label>
							</div>
						</div>
					</fieldset>
					<fieldset class="scheduler-border">
                        <legend class="scheduler-border">Print tem sản phẩm</legend>
                        <div class="col-md-12">
                        	<div class="form-group">
                            <?= lang('Mẫu in', 'style'); ?>
                            <?php $opts = array('' => lang('select').' '.lang('mẫu in'), 000 => lang('1 Tem 58*40mm'),100 => lang('1 Tem 50*30mm'),200 => lang('2 Tem 35*22mm (70mm)'),300 => lang('3 Tem 35*22mm (110mm)'),50 => lang('Tùy chọn')); ?>
                            <?php
                            $print_tem_json=json_decode($Settings->print_tem);
                            
                            $_style_default=000;
                            $cf_width='';
                            $cf_height='';
                            $cf_orientation='';
                            $p_site_name= '';
				            $p_product_name= '';
				            $p_price= '';
				            $p_currencies= '';
				            $p_unit= '';
				            $p_category= '';
				            $p_variants= '';
				            $p_product_image= '';
				            $p_check_promo= '';
                            if (isset($print_tem_json)) {
                            	$_style_default=$print_tem_json->style;
                            	$cf_width=$print_tem_json->width;
	                            $cf_height=$print_tem_json->height;
	                            $cf_orientation=$print_tem_json->orientation;

	                            $p_site_name= $print_tem_json->site_name==1?'checked="checked"':'';
					            $p_product_name= $print_tem_json->product_name==1?'checked="checked"':'';
					            $p_price= $print_tem_json->price==1?'checked="checked"':'';
					            $p_currencies= $print_tem_json->currencies==1?'checked="checked"':'';
					            $p_unit= $print_tem_json->unit==1?'checked="checked"':'';
					            $p_category= $print_tem_json->category==1?'checked="checked"':'';
					            $p_variants= $print_tem_json->variants==1?'checked="checked"':'';
					            $p_product_image= $print_tem_json->image==1?'checked="checked"':'';
					            $p_check_promo= $print_tem_json->check_promo==1?'checked="checked"':'';

                            }
                            ?>

                            <?= form_dropdown('style', $opts, set_value('style', $_style_default), 'class="form-control tip" id="style" required="required"'); ?>                           

	                            <div class="clearfix"></div>
	                        </div>
                        	<div class="row cf-con" style="margin: 0px -12px!important; display: none;">
                                <div class="col-xs-4">
                                    <div class="form-group">
                                        <div class="input-group">
                                            <?= form_input('cf_width', $cf_width, 'class="form-control" id="cf_width" placeholder="' . lang("width") . '"'); ?>
                                            <span class="input-group-addon" style="padding-left:10px;padding-right:10px;"><?= lang('mm'); ?></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xs-4">
                                    <div class="form-group">
                                        <div class="input-group">
                                            <?= form_input('cf_height', $cf_height, 'class="form-control" id="cf_height" placeholder="' . lang("height") . '"'); ?>
                                            <span class="input-group-addon" style="padding-left:10px;padding-right:10px;"><?= lang('mm'); ?></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xs-4">
                                    <div class="form-group">
                                    <?php $oopts = array(0 => lang('portrait'), 1 => lang('landscape')); ?>
                                        <?= form_dropdown('cf_orientation', $oopts , $cf_orientation, 'class="form-control" id="cf_orientation" placeholder="' . lang("orientation") . '"'); ?>
                                    </div>
                                </div>
                            </div>
	                        <div class="form-group">
	                            <span style="font-weight: bold; margin-right: 15px;"><?= lang('print'); ?>:</span>
	                            <input name="p_site_name" type="checkbox" id="p_site_name" value="1" <?=$p_site_name;?> style="display:inline-block;" />
	                            <label for="site_name" class="padding05"><?= lang('site_name'); ?></label>
	                            <input name="p_product_name" type="checkbox" id="p_product_name" value="1" <?=$p_product_name;?> style="display:inline-block;" />
	                            <label for="product_name" class="padding05"><?= lang('product_name'); ?></label>
	                            <input name="p_price" type="checkbox" id="p_price" value="1" <?=$p_price;?> style="display:inline-block;" />
	                            <label for="price" class="padding05"><?= lang('price'); ?></label>
	                            <input name="p_currencies" type="checkbox" id="p_currencies" value="1" <?=$p_currencies;?> style="display:inline-block;" />
	                            <label for="currencies" class="padding05"><?= lang('currencies'); ?></label>
	                            <input name="p_unit" type="checkbox" id="p_unit" value="1" <?=$p_unit;?> style="display:inline-block;" />
	                            <label for="unit" class="padding05"><?= lang('unit'); ?></label>
	                            <input name="p_category" type="checkbox" id="p_category" value="1" <?=$p_category;?> style="display:inline-block;" />
	                            <label for="category" class="padding05"><?= lang('category'); ?></label>
	                            <input name="p_variants" type="checkbox" id="p_variants" value="1" <?=$p_variants;?> style="display:inline-block;" />
	                            <label for="variants" class="padding05"><?= lang('variants'); ?></label>
	                            <input name="p_check_promo" type="checkbox" id="p_check_promo" value="1" <?=$p_check_promo;?> style="display:inline-block;" />
	                            <label for="check_promo" class="padding05"><?= lang('Giá khuyến mãi'); ?></label>
	                        </div>
						</div>
					</fieldset>
                </div>
            </div>
            <div style="clear: both; height: 10px;"></div>
            <div class="col-md-12">
                <div class="form-group">
                    <div class="controls">
                        <?= form_submit('update_settings', lang("update_settings"), 'class="btn btn-primary"'); ?>
                    </div>
                </div>
            </div>
            <?= form_close(); ?>
			</div>
		</div>
	</div>
</div>
<style type="text/css">
	button#b1wpapi {
  		position: absolute;
	    right: 14px;
	    top: 23px;
	    padding: 0px 7px;
	}

	button.btn.remove-more-wp-api {
	    position: absolute;
	    right: 14px;
	    padding: 0px 7px;
	    top: 23px;
	}

	.row.api_wp {
	    position: relative;
	}
</style>
<script type="text/javascript">
	function removeapiwp(obj){
		$(obj).parent().parent().remove();
	};
	$(document).ready(function(){
		

	 	$("#b1wpapi").click(function(){
			var html='<div class="row api_wp"><div class="col-sm-4 col-xs-4">Your Store URL<br><input type="text" name="woo_url[]" value="" class="form-control"></div><div class="col-sm-4 col-xs-4">Your API consumer key<br><input type="text" name="woo_key[]" value="" class="form-control"></div><div class="col-sm-4 col-xs-4">Your API consumer secret<br><input type="text" name="woo_sec[]" value="" class="form-control"><button class="btn remove-more-wp-api" type="button" onclick="removeapiwp(this)">-</button></div></div>';

			$("#divapiv2").append(html);
			
		});

		$('#style').change(function (e) {
            localStorage.setItem('bcstyle', $(this).val());
            if ($(this).val() == 50) {
                $('.cf-con').slideDown();
            } else {
                $('.cf-con').slideUp();
            }
        });
        if (style = localStorage.getItem('bcstyle')) {
            $('#style').val(style);
            $('#style').select2("val", style);
            if (style == 50) {
                $('.cf-con').slideDown();
            } else {
                $('.cf-con').slideUp();
            }
        }

        $('#cf_width').change(function (e) {
            localStorage.setItem('cf_width', $(this).val());
        });
        if (cf_width = localStorage.getItem('cf_width')) {
            $('#cf_width').val(cf_width);
        }

        $('#cf_height').change(function (e) {
            localStorage.setItem('cf_height', $(this).val());
        });
        if (cf_height = localStorage.getItem('cf_height')) {
            $('#cf_height').val(cf_height);
        }

        $('#cf_orientation').change(function (e) {
            localStorage.setItem('cf_orientation', $(this).val());
        });
        if (cf_orientation = localStorage.getItem('cf_orientation')) {
            $('#cf_orientation').val(cf_orientation);
        }

        var p_site_name= '<?=$p_site_name;?>';
        var p_product_name= '<?=$p_product_name;?>';
        var p_price= '<?=$p_price;?>';
        var p_currencies= '<?=$p_currencies;?>';
        var p_unit= '<?=$p_unit;?>';
        var p_category= '<?=$p_category;?>';
        var p_variants= '<?=$p_variants;?>';
        var p_product_image= '<?=$p_product_image;?>';
        var p_check_promo= '<?=$p_check_promo;?>';

        if (p_site_name == 1){
			$('#p_site_name').iCheck('check');
		}
		if (p_product_name == 1){
			$('#p_product_name').iCheck('check');
		}
		if (p_price == 1){
			$('#p_price').iCheck('check');
		}
		if (p_currencies == 1){
			$('#p_currencies').iCheck('check');
		}
		if (p_unit == 1){
			$('#p_unit').iCheck('check');
		}
		if (p_category == 1){
			$('#p_category').iCheck('check');
		}
		if (p_variants == 1){
			$('#p_variants').iCheck('check');
		}
		if (p_product_image == 1){
			$('#p_product_image').iCheck('check');
		}
		if (p_check_promo == 1){
			$('#p_check_promo').iCheck('check');
		}
		
                
	});
</script>