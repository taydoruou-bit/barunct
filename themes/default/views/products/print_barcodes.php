<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="box">
    <div class="box-header no-print">
        <h2 class="blue"><i class="fa-fw fa fa-plus"></i><?= lang('print_barcode_label'); ?></h2>

        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a href="#" onclick="window.print();return false;" id="print-icon" class="tip" title="<?= lang('print') ?>">
                        <i class="icon fa fa-print"></i>
                    </a>
                </li>
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <p class="introtext"><?php echo sprintf(lang('print_barcode_heading'), 
                anchor('system_settings/categories', lang('categories')),
                anchor('system_settings/subcategories', lang('subcategories')),
                anchor('purchases', lang('purchases')),
                anchor('transfers', lang('transfers'))
                ); ?></p>

                <div class="well well-sm no-print">
                    <div class="form-group">
                        <?= lang("add_product", "add_item"); ?>
                        <?php echo form_input('add_item', '', 'class="form-control" id="add_item" placeholder="' . $this->lang->line("add_item") . '"'); ?>
                    </div>
                    <?= form_open("products/print_barcodes", 'id="barcode-print-form" data-toggle="validator"'); ?>
                    <div class="controls table-controls">
                        <table id="bcTable"
                               class="table items table-striped table-bordered table-condensed table-hover">
                            <thead>
                            <tr>
                                <th class="col-xs-4"><?= lang("product_name") . " (" . $this->lang->line("product_code") . ")"; ?></th>
                                <th class="col-xs-1"><?= lang("quantity"); ?></th>
                                <th class="col-xs-7"><?= lang("variants"); ?></th>
                                <th class="text-center" style="width:30px;">
                                    <i class="fa fa-trash-o" style="opacity:0.5; filter:alpha(opacity=50);"></i>
                                </th>
                            </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>

                        <div class="form-group">
                            <?= lang('style', 'style'); ?>
                            <?php $opts = array('' => lang('select').' '.lang('style'), 000 => lang('1 Tem 58*40mm'),100 => lang('1 Tem 50*30mm'),200 => lang('2 Tem 35*22mm (70mm)'),300 => lang('3 Tem 35*22mm (110mm)'),50 => lang('Tùy chọn')); ?>
                            <?= form_dropdown('style', $opts, set_value('style', 000), 'class="form-control tip" id="style" required="required"'); ?>
                            
                            <span class="help-block"><?= lang('barcode_tip'); ?></span>
                            <div class="clearfix"></div>
                        </div>
                        <div class="row cf-con" style="margin: 0px -12px!important; display: none;">
                                <div class="col-xs-4">
                                    <div class="form-group">
                                        <div class="input-group">
                                            <?= form_input('cf_width', '', 'class="form-control" id="cf_width" placeholder="' . lang("width") . '"'); ?>
                                            <span class="input-group-addon" style="padding-left:10px;padding-right:10px;"><?= lang('mm'); ?></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xs-4">
                                    <div class="form-group">
                                        <div class="input-group">
                                            <?= form_input('cf_height', '', 'class="form-control" id="cf_height" placeholder="' . lang("height") . '"'); ?>
                                            <span class="input-group-addon" style="padding-left:10px;padding-right:10px;"><?= lang('mm'); ?></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xs-4">
                                    <div class="form-group">
                                    <?php $oopts = array(0 => 'portrait', 1 => 'landscape'); ?>
                                        <?= form_dropdown('cf_orientation', $oopts , '', 'class="form-control" id="cf_orientation" placeholder="' . lang("orientation") . '"'); ?>
                                    </div>
                                </div>
                            </div>
                        <div class="form-group">
                            <span style="font-weight: bold; margin-right: 15px;"><?= lang('print'); ?>:</span>
                            <input name="site_name" type="checkbox" id="site_name" value="1" style="display:inline-block;" />
                            <label for="site_name" class="padding05"><?= lang('site_name'); ?></label>
                            <input name="product_name" type="checkbox" id="product_name" value="1" checked="checked" style="display:inline-block;" />
                            <label for="product_name" class="padding05"><?= lang('product_name'); ?></label>
                            <input name="price" type="checkbox" id="price" value="1" checked="checked" style="display:inline-block;" />
                            <label for="price" class="padding05"><?= lang('price'); ?></label>
                            <input name="currencies" type="checkbox" id="currencies" value="1" style="display:inline-block;" />
                            <label for="currencies" class="padding05"><?= lang('currencies'); ?></label>
                            <input name="unit" type="checkbox" id="unit" value="1" style="display:inline-block;" />
                            <label for="unit" class="padding05"><?= lang('unit'); ?></label>
                            <input name="category" type="checkbox" id="category" value="1" style="display:inline-block;" />
                            <label for="category" class="padding05"><?= lang('category'); ?></label>
                            <input name="variants" type="checkbox" id="variants" value="1" style="display:inline-block;" />
                            <label for="variants" class="padding05"><?= lang('variants'); ?></label>

                            <input name="check_promo" type="checkbox" id="check_promo" value="1" checked="checked" style="display:inline-block;" />
                            <label for="check_promo" class="padding05"><?= lang('check_promo'); ?></label>
                        </div>

                    <div class="form-group">
                        <?php echo form_submit('print', lang("update"), 'class="btn btn-primary"'); ?>
                        <button type="button" id="reset" class="btn btn-danger"><?= lang('reset'); ?></button>
                    </div>
                    <?= form_close(); ?>
                    <div class="clearfix"></div>
                </div>
                <?php 
 if (!empty($barcodes)) {
                                echo '<button type="button" onclick="window.print();return false;" class="no-print" style="width:100%" title="'.lang('print').'"><i class="icon fa fa-print"></i> '.lang('print').'</button>';
                            }
                ?>
                <div id="barcode-con">
                    <?php
                        if ($this->input->post('print')) {
                            if (!empty($barcodes)) {
                                $c = 1;
                                $tem="";
                                if ($style == 12 || $style == 18 || $style == 24 || $style == 40) {
                                    echo '<div class="barcodea4">';
                                } elseif ($style == 000) {
                                	foreach ($barcodes as $item) {
	                                    $cao+=$item['quantity'];
	                              		
	                                }
	                                if($item['variants']) {
                                        $cao+=$cao*count($item['variants']);
                                    }
                                    $tem="style='height:".($cao*40)."mm;width:58mm'";
                                    echo '<div class="barcodeatem000" '.$tem.'> ';
                                }elseif ($style == 100) {
                                	foreach ($barcodes as $item) {
	                                    $cao+=$item['quantity'];
	                              		
	                                }
	                                if($item['variants']) {
                                        $cao+=$cao*count($item['variants']);
                                    }
                                    $tem="style='height:".($cao*30)."mm;width:50mm'";
                                    echo '<div class="barcodeatem100" '.$tem.'> ';
                                }elseif ($style == 200) {
                                	foreach ($barcodes as $item) {
	                                    $cao+=$item['quantity']*2;	                              		
	                                }
	                                if($item['variants']) {
                                        $cao+=$cao*count($item['variants']);
                                    }
                                    $nguyen=$cao/2;
                                    $caochk=$cao%2;
                                    if ($caochk!=0) {
                                    	$cao=$nguyen+1;
                                    }else{
                                    	$cao=$nguyen;
                                    }

                                    $tem="style='height:".($cao*22)."mm;width:72mm'";
                                    echo '<div class="barcodeatem" '.$tem.'> ';
                                }elseif ($style == 300) {
                                	foreach ($barcodes as $item) {
	                                    $cao+=$item['quantity']*3;	                              		
	                                }
	                                if($item['variants']) {
                                        $cao+=$cao*count($item['variants']);
                                    }

                                    $nguyen=$cao/3;
                                    $caochk=$cao%3;
                                    if ($caochk!=0) {
                                    	$cao=$nguyen+1;
                                    }else{
                                    	$cao=$nguyen;
                                    }
                                    $tem="style='height:".($cao*22)."mm;width:110mm'";
                                    echo '<div class="barcodeatem300" '.$tem.'> ';
                                }elseif ($style != 50) {
                                    echo '<div class="barcode">';                                  

                                }
                                foreach ($barcodes as $item) {
                                    $tem_item="style='height:".($cao*22)."mm;width:56mm'";

                                    if ($style == 000) {
                                        $tem_item="style='height:38mm;width:56mm;margin:0px'";
                                    }else if ($style == 100) {
                                        $tem_item="style='height:28mm;width:48mm;margin:0px'";
                                    }else if ($style == 200) {
                                        $item['quantity']=$item['quantity']*2;
                                        $tem_item="style='height:22mm;width:34mm;margin:0px;'";
                                    }
                                    else if ($style == 300) {
                                        $item['quantity']=$item['quantity']*3;
                                        $tem_item="style='height:20mm;width:33mm;margin:1mm'";
                                    }else if($style==50){
                                        $tem_item=$this->input->post('cf_width') && $this->input->post('cf_height') ?
                                            'style="width:'.$this->input->post('cf_width').'mm;height:'.$this->input->post('cf_height').'mm;border:0;"' : '';
                                    }else{
                                        $item['quantity']=$item['quantity'];
                                        $tem_item="style='height:".($cao*22)."mm;width:72mm'";
                                    }

                                    for ($r = 1; $r <= $item['quantity']; $r++) {
                                    	
                                        echo '<div class="item style'.$style.'" '. $tem_item.'>';
                                        if ($style == 50) {
                                            if ($this->input->post('cf_orientation')) {
                                                $ty = (($this->input->post('cf_height')/$this->input->post('cf_width'))*100).'%';
                                                $landscape = '
                                                -webkit-transform-origin: 0 0;
                                                -moz-transform-origin:    0 0;
                                                -ms-transform-origin:     0 0;
                                                transform-origin:         0 0;
                                                -webkit-transform: translateY('.$ty.') rotate(-90deg);
                                                -moz-transform:    translateY('.$ty.') rotate(-90deg);
                                                -ms-transform:     translateY('.$ty.') rotate(-90deg);
                                                transform:         translateY('.$ty.') rotate(-90deg);
                                                ';
                                                echo '<div class="div50" style="width:'.$this->input->post('cf_height').'mm;height:'.$this->input->post('cf_width').'mm;border: 1px dotted #CCC;'.$landscape.'">';
                                            } else {
                                                echo '<div class="div50" style="width:'.$this->input->post('cf_width').'mm;height:'.$this->input->post('cf_height').'mm;border: 1px dotted #CCC;padding-top:0.025in;">';
                                            }
                                        }
                                        
                                        $style_span='style="float:left;text-align:center;margin:0 auto; font-size:12px;width:100%"';
                                        
                                        if($item['image']) {
                                            echo '<span class="product_image"><img src="'.base_url('assets/uploads/thumbs/'.$item['image']).'" alt="" /></span>';
                                        }
                                        if($item['site']) {
                                            echo '<span '.$style_span.' class="barcode_site">'.$item['site'].'</span>';
                                        }
                                        if($item['name'])
                                        {
                                            if ($style == 200) {
                                                $style_span1='style="float:left;text-align:center;margin:0 auto; font-size:12px;width:100%;height:3mm;line-height: 13px;max-height:3mm;overflow: hidden;white-space:normal;"';
                                            }   
                                            else if ($style == 300) {
                                                $style_span1='style="float:left;text-align:center;margin:0 auto; font-size:12px;width:100%;height:3mm;line-height: 13px;max-height:3mm;overflow: hidden;white-space:normal;"';
                                            } 
                                            else if ($style == 100) {
                                                $style_span1='style="float:left;text-align:center;margin:0 auto; font-size:13px;width:100%;height:8mm;line-height: 15px;max-height:8mm;overflow: hidden;white-space:normal;"';
                                            }else if ($style == 000) {
                                                $style_span1='style="float:left;text-align:center;margin:0 auto; font-size:14px;width:100%;height:12mm;line-height: 15px;max-height:12mm;overflow: hidden;white-space:normal;"';
                                            }else{
                                                $style_span1=$style_span;
                                            }                                       
                                            echo '<span '.$style_span1.' class="barcode_name">'.$item['name'].'</span>';
                                        }
                                        if($item['price']) {
                                            echo '<span '.$style_span.' class="barcode_price">'.lang('price').' ';
                                            if($item['currencies']) {
                                            	$rsc=array();
                                                foreach ($currencies as $currency) {
                                                    $rsc[]=$item['price']." ".$currency->name;
                                                }
                                                echo implode(",", $rsc);
                                            } else {
                                                echo $item['price'];
                                            }
                                            echo '</span> ';
                                        }
                                        if($item['unit']) {
                                            echo '<span '.$style_span.' class="barcode_unit">'.lang('unit').': '.$item['unit'].'</span>, ';
                                        }
                                        if($item['category']) {
                                            echo '<span '.$style_span.' class="barcode_category">'.lang('category').': '.$item['category'].'</span> ';
                                        }
                                        if($item['variants']) {
                                            echo '<span '.$style_span.' class="variants">'.lang('variants').': ';
                                            foreach ($item['variants'] as $variant) {
                                                echo $variant->name.', ';
                                            }
                                            echo '</span> ';
                                        }
                                        echo '<span '.$style_span.' >'.$item['barcode'].'</span>';
                                        if ($style == 50) {
                                            echo '</div>';
                                        }
                                        echo '</div>';
                                        if ($style == 40) {
                                            if ($c % 40 == 0) {
                                                echo '</div><div class="clearfix"></div><div class="barcodea4">';
                                            }
                                        } elseif ($style == 30) {
                                            if ($c % 30 == 0) {
                                                echo '</div><div class="clearfix"></div><div class="barcode">';
                                            }
                                        } elseif ($style == 24) {
                                            if ($c % 24 == 0) {
                                                echo '</div><div class="clearfix"></div><div class="barcodea4">';
                                            }
                                        } elseif ($style == 20) {
                                            if ($c % 20 == 0) {
                                                echo '</div><div class="clearfix"></div><div class="barcode">';
                                            }
                                        } elseif ($style == 18) {
                                            if ($c % 18 == 0) {
                                                echo '</div><div class="clearfix"></div><div class="barcodea4">';
                                            }
                                        } elseif ($style == 14) {
                                            if ($c % 14 == 0) {
                                                echo '</div><div class="clearfix"></div><div class="barcode">';
                                            }
                                        } elseif ($style == 12) {
                                            if ($c % 12 == 0) {
                                                echo '</div><div class="clearfix"></div><div class="barcodea4">';
                                            }
                                        } elseif ($style == 10) {
                                            if ($c % 10 == 0) {
                                                echo '</div><div class="clearfix"></div><div class="barcode">';
                                            }
                                        }
                                        $c++;
                                    }
                                }
                                if ($style != 50) {
                                    echo '</div>';
                                }
                            } else {
                                echo '<h3>'.lang('no_product_selected').'</h3>';
                            }
                        }
                    ?>
                </div>
                <?php 
 if (!empty($barcodes)) {
                                echo '<button type="button" onclick="window.print();return false;" class="no-print" style="width:100%" title="'.lang('print').'"><i class="icon fa fa-print"></i> '.lang('print').'</button>';
                            }
                ?>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    var ac = false; bcitems = {};
    if (localStorage.getItem('bcitems')) {
        bcitems = JSON.parse(localStorage.getItem('bcitems'));
    }
    <?php if($items) { ?>
    localStorage.setItem('bcitems', JSON.stringify(<?= $items; ?>));
    <?php } ?>
    $(document).ready(function() {
        <?php if ($this->input->post('print')) { ?>
            $( window ).load(function() {
                $('html, body').animate({
                    scrollTop: ($("#barcode-con").offset().top)-15
                }, 1000);
            });
        <?php } ?>
        if (localStorage.getItem('bcitems')) {
            loadItems();
        }
        $("#add_item").autocomplete({
            source: '<?= site_url('products/get_suggestions'); ?>',
            minLength: 1,
            autoFocus: false,
            delay: 250,
            response: function (event, ui) {
                if ($(this).val().length >= 16 && ui.content[0].id == 0) {
                    //audio_error.play();
                    bootbox.alert('<?= lang('no_product_found') ?>', function () {
                        $('#add_item').focus();
                    });
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
                    bootbox.alert('<?= lang('no_product_found') ?>', function () {
                        $('#add_item').focus();
                    });
                    $(this).val('');

                }
            },
            select: function (event, ui) {
                event.preventDefault();
                if (ui.item.id !== 0) {
                    var row = add_product_item(ui.item);
                    if (row) {
                        $(this).val('');
                    }
                } else {
                    //audio_error.play();
                    bootbox.alert('<?= lang('no_product_found') ?>');
                }
            }
        });
        check_add_item_val();

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

        $(document).on('ifChecked', '#site_name', function(event) {
            localStorage.setItem('bcsite_name', 1);
        });
        $(document).on('ifUnchecked', '#site_name', function(event) {
            localStorage.setItem('bcsite_name', 0);
        });
        if (site_name = localStorage.getItem('bcsite_name')) {
            if (site_name == 1)
                $('#site_name').iCheck('check');
            else
                $('#site_name').iCheck('uncheck');
        }

        $(document).on('ifChecked', '#product_name', function(event) {
            localStorage.setItem('bcproduct_name', 1);
        });
        $(document).on('ifUnchecked', '#product_name', function(event) {
            localStorage.setItem('bcproduct_name', 0);
        });
        if (product_name = localStorage.getItem('bcproduct_name')) {
            if (product_name == 1)
                $('#product_name').iCheck('check');
            else
                $('#product_name').iCheck('uncheck');
        }

        $(document).on('ifChecked', '#price', function(event) {
            localStorage.setItem('bcprice', 1);
        });
        $(document).on('ifUnchecked', '#price', function(event) {
            localStorage.setItem('bcprice', 0);
            $('#currencies').iCheck('uncheck');
        });
        if (price = localStorage.getItem('bcprice')) {
            if (price == 1)
                $('#price').iCheck('check');
            else
                $('#price').iCheck('uncheck');
        }

        $(document).on('ifChecked', '#currencies', function(event) {
            localStorage.setItem('bccurrencies', 1);
        });
        $(document).on('ifUnchecked', '#currencies', function(event) {
            localStorage.setItem('bccurrencies', 0);
        });
        if (currencies = localStorage.getItem('bccurrencies')) {
            if (currencies == 1)
                $('#currencies').iCheck('check');
            else
                $('#currencies').iCheck('uncheck');
        }

        $(document).on('ifChecked', '#unit', function(event) {
            localStorage.setItem('bcunit', 1);
        });
        $(document).on('ifUnchecked', '#unit', function(event) {
            localStorage.setItem('bcunit', 0);
        });
        if (unit = localStorage.getItem('bcunit')) {
            if (unit == 1)
                $('#unit').iCheck('check');
            else
                $('#unit').iCheck('uncheck');
        }

        $(document).on('ifChecked', '#category', function(event) {
            localStorage.setItem('bccategory', 1);
        });
        $(document).on('ifUnchecked', '#category', function(event) {
            localStorage.setItem('bccategory', 0);
        });
        if (category = localStorage.getItem('bccategory')) {
            if (category == 1)
                $('#category').iCheck('check');
            else
                $('#category').iCheck('uncheck');
        }
        $(document).on('ifChecked', '#check_promo', function(event) {
            localStorage.setItem('bccheck_promo', 1);
        });
        $(document).on('ifUnchecked', '#check_promo', function(event) {
            localStorage.setItem('bccheck_promo', 0);
        });
        if (check_promo = localStorage.getItem('bccheck_promo')) {
            if (check_promo == 1)
                $('#check_promo').iCheck('check');
            else
                $('#check_promo').iCheck('uncheck');
        }

        $(document).on('ifChecked', '#product_image', function(event) {
            localStorage.setItem('bcproduct_image', 1);
        });
        $(document).on('ifUnchecked', '#product_image', function(event) {
            localStorage.setItem('bcproduct_image', 0);
        });
        if (product_image = localStorage.getItem('bcproduct_image')) {
            if (product_image == 1)
                $('#product_image').iCheck('check');
            else
                $('#product_image').iCheck('uncheck');
        }

        $(document).on('ifChecked', '#variants', function(event) {
            localStorage.setItem('bcvariants', 1);
        });
        $(document).on('ifUnchecked', '#variants', function(event) {
            localStorage.setItem('bcvariants', 0);
        });
        if (variants = localStorage.getItem('bcvariants')) {
            if (variants == 1)
                $('#variants').iCheck('check');
            else
                $('#variants').iCheck('uncheck');
        }

        $(document).on('ifChecked', '.checkbox', function(event) {
            var item_id = $(this).attr('data-item-id');
            var vt_id = $(this).attr('id');
            bcitems[item_id]['selected_variants'][vt_id] = 1;
            localStorage.setItem('bcitems', JSON.stringify(bcitems));
        });
        $(document).on('ifUnchecked', '.checkbox', function(event) {
            var item_id = $(this).attr('data-item-id');
            var vt_id = $(this).attr('id');
            bcitems[item_id]['selected_variants'][vt_id] = 0;
            localStorage.setItem('bcitems', JSON.stringify(bcitems));
        });

        $(document).on('click', '.del', function () {
            var id = $(this).attr('id');
            delete bcitems[id];
            localStorage.setItem('bcitems', JSON.stringify(bcitems));
            $(this).closest('#row_' + id).remove();
        });

        $('#reset').click(function (e) {

            bootbox.confirm(lang.r_u_sure, function (result) {
                if (result) {
                    if (localStorage.getItem('bcitems')) {
                        localStorage.removeItem('bcitems');
                    }
                    if (localStorage.getItem('bcstyle')) {
                        localStorage.removeItem('bcstyle');
                    }
                    if (localStorage.getItem('bcsite_name')) {
                        localStorage.removeItem('bcsite_name');
                    }
                    if (localStorage.getItem('bcproduct_name')) {
                        localStorage.removeItem('bcproduct_name');
                    }
                    if (localStorage.getItem('bcprice')) {
                        localStorage.removeItem('bcprice');
                    }
                    if (localStorage.getItem('bccurrencies')) {
                        localStorage.removeItem('bccurrencies');
                    }
                    if (localStorage.getItem('bcunit')) {
                        localStorage.removeItem('bcunit');
                    }
                    if (localStorage.getItem('bccategory')) {
                        localStorage.removeItem('bccategory');
                    }
                    // if (localStorage.getItem('cf_width')) {
                    //     localStorage.removeItem('cf_width');
                    // }
                    // if (localStorage.getItem('cf_height')) {
                    //     localStorage.removeItem('cf_height');
                    // }
                    // if (localStorage.getItem('cf_orientation')) {
                    //     localStorage.removeItem('cf_orientation');
                    // }

                    $('#modal-loading').show();
                    window.location.replace("<?= site_url('products/print_barcodes'); ?>");
                }
            });
        });

        var old_row_qty;
        $(document).on("focus", '.quantity', function () {
            old_row_qty = $(this).val();
        }).on("change", '.quantity', function () {
            var row = $(this).closest('tr');
            if (!is_numeric($(this).val())) {
                $(this).val(old_row_qty);
                bootbox.alert(lang.unexpected_value);
                return;
            }
            var new_qty = parseFloat($(this).val()),
            item_id = row.attr('data-item-id');
            bcitems[item_id].qty = new_qty;
            localStorage.setItem('bcitems', JSON.stringify(bcitems));
        });

    });

    function add_product_item(item) {
        ac = true;
        if (item == null) {
            return false;
        }
        item_id = item.id;
        if (bcitems[item_id]) {
            bcitems[item_id].qty = parseFloat(bcitems[item_id].qty) + 1;
        } else {
            bcitems[item_id] = item;
            bcitems[item_id]['selected_variants'] = {};
            $.each(item.variants, function () {
                bcitems[item_id]['selected_variants'][this.id] = 1;
            });
        }

        localStorage.setItem('bcitems', JSON.stringify(bcitems));
        loadItems();
        return true;

    }

    function loadItems () {

        if (localStorage.getItem('bcitems')) {
            $("#bcTable tbody").empty();
            bcitems = JSON.parse(localStorage.getItem('bcitems'));

            $.each(bcitems, function () {

                var item = this;
                var row_no = item.id;
                var vd = '';
                var newTr = $('<tr id="row_' + row_no + '" class="row_' + item.id + '" data-item-id="' + item.id + '"></tr>');
                tr_html = '<td><input name="product[]" type="hidden" value="' + item.id + '"><span id="name_' + row_no + '">' + item.name + ' (' + item.code + ')</span></td>';
                tr_html += '<td><input class="form-control quantity text-center" name="quantity[]" type="text" value="' + formatDecimal(item.qty) + '" data-id="' + row_no + '" data-item="' + item.id + '" id="quantity_' + row_no + '" onClick="this.select();"></td>';
                if(item.variants) {
                    $.each(item.variants, function () {
                        vd += '<input name="vt_'+ item.id +'_'+ this.id +'" type="checkbox" class="checkbox" id="'+this.id+'" data-item-id="'+item.id+'" value="'+this.id+'" '+( item.selected_variants[this.id] == 1 ? 'checked="checked"' : '')+' style="display:inline-block;" /><label for="'+this.id+'" class="padding05">'+this.name+'</label>';
                    });
                }
                tr_html += '<td>'+vd+'</td>';
                tr_html += '<td class="text-center"><i class="fa fa-times tip del" id="' + row_no + '" title="Remove" style="cursor:pointer;"></i></td>';
                newTr.html(tr_html);
                newTr.appendTo("#bcTable");
            });
            $('input[type="checkbox"],[type="radio"]').not('.skip').iCheck({
                checkboxClass: 'icheckbox_square-blue',
                radioClass: 'iradio_square-blue',
                increaseArea: '20%'
            });
            return true;
        }
    }

</script>