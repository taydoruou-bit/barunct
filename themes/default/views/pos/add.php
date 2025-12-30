<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?><!DOCTYPE html>
<html>
<head>
   <meta charset="utf-8">
    <title><?=lang('pos_module') . " | " . $Settings->site_name;?></title>
    <script type="text/javascript">if(parent.frames.length !== 0){top.location = '<?=site_url('pos')?>';}</script>
	<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
   <base href="<?=base_url()?>"/>
    <meta http-equiv="cache-control" content="max-age=0"/>
    <meta http-equiv="cache-control" content="no-cache"/>
    <meta http-equiv="expires" content="0"/>
    <meta http-equiv="pragma" content="no-cache"/>
    <link rel="shortcut icon" href="<?=$assets?>images/favicon.ico"/>
    <link rel="stylesheet" href="<?=$assets?>styles/theme.css" type="text/css"/>
    
    <link rel="stylesheet" href="<?=$assets?>pos/css/posajax.css" type="text/css"/>
    <link rel="stylesheet" href="<?=$assets?>pos/css/print.css" type="text/css" media="print"/>
    <script type="text/javascript" src="<?=$assets?>js/jquery-2.0.3.min.js"></script>
    <script type="text/javascript" src="<?=$assets?>js/jquery-migrate-1.2.1.min.js"></script>
	  <!-- Bootstrap core CSS-->
	  <link href="<?= $assets ?>ace/css/bootstrap.min.css" rel="stylesheet">
	  <!-- Custom fonts for this template-->
	  <link href="<?= $assets ?>ace/css/font-awesome.min.css" rel="stylesheet" type="text/css">
	  <!-- Page level plugin CSS-->
	 <!-- <link href="<?= $assets ?>/sbadmin/vendor/datatables/dataTables.bootstrap4.css" rel="stylesheet">-->
	  <!-- Custom styles for this template-->
	  	<link rel="stylesheet" href="<?= $assets ?>ace/css/ace-skins.min.css" />
		<link rel="stylesheet" href="<?= $assets ?>ace/css/ace-rtl.min.css" />
		<link href="<?= $assets ?>ace/css/ace.min.css" rel="stylesheet">
    <!--[if lt IE 9]>
    <script src="<?=$assets?>js/jquery.js"></script>
    <![endif]-->
    <?php if ($Settings->user_rtl) {?>
        <link href="<?=$assets?>styles/helpers/bootstrap-rtl.min.css" rel="stylesheet"/>
        <link href="<?=$assets?>styles/style-rtl.css" rel="stylesheet"/>
        <script type="text/javascript"> 
            $(document).ready(function () {
                $('.pull-right, .pull-left').addClass('flip');
                
            });
        </script>
    <?php }    
    ?>
    <script type="text/javascript">
        var sub_product=<?php echo json_encode($sub_product); ?>;
        var khuyenmai_main=<?php echo json_encode($khuyenmai_main); ?>;
        var main_product=<?php echo json_encode($main_product); ?>;

        $(document).on('change', 'select#lhson_chinhanh', function(e) {
                    var id = $(this).val();       
                    $.ajax({
                        type: 'POST',
                        url: site.base_url+'auth/setNewPermission/'+id,
                        dataType: "json",
                        data: { id: id,'<?=$this->security->get_csrf_token_name()?>':'<?=$this->security->get_csrf_hash()?>' },
                        success: function (data) {
                            console.log(data);
                            if(data.trim() == 'OK') {
                                  window.location=site.base_url;
                            } else {
                                 addAlert('Có lỗi xảy ra', 'danger'); 
                            }
                        },error: function(data) { 
                            if(data.responseText.trim() == 'OK') {
                                  window.location=site.base_url;
                            } else {
                                 addAlert('Có lỗi xảy ra', 'danger'); 
                            }
                        }
                    });
                }); 
    </script>
    <style type="text/css">
        @media (min-width: 768px) and (max-width: 1024px) {
            div#content {
                padding: 0px;
            }

            .navbar-header.pull-left {
                width: 36%;
            }
            

                .navbar-buttons.navbar-header.pull-right ul.nav li a {
                    min-width: 26px;
                    padding: 0px;
                    border: 0px;
                }

            .navbar-header.pull-left .no-print {
                width: 100%;
            }

            div#pos {
                width: 100%;
                float: left;
            }

            .btn-group.btn-group-justified.pos-grid-nav {
                display: none;
            }

            #proContainer #ajaxproducts div#item-list button.product img {
                width: 100%!important;
                height: auto!important;
            }

            #proContainer #ajaxproducts div#item-list button.product {width: 23%!important;max-width: 23%!important;}

            #pos #leftdiv {
                padding: 0px;
                float: left;
                min-width: auto;
                width: 36%;
                max-width: 36%;
            }

            div#cp {
                float: left;
                width: 64%;
            }

            #pos #cp #cpinner {
                padding: 0px;
                width: 100%;
                margin: 0px;
                min-width: auto;
                max-width: 100%;
            }

            body {
                width: 766px;
                min-width: 766px;
            }

            div#pos2 {
                float: left;
                width: 100%;
            }

            div#navbar-container .no-print .form-group {
                margin: 0px;
            }

            div#navbar-container {
                padding: 0px;
            }
          
        }
        @media(max-width: 767px) {
            div#content {
                padding: 0px;
            }

            .navbar-header.pull-left {
                width: 100%!important;
            }

            .navbar-buttons.navbar-header.pull-right {
                display: none;
            }

            .navbar-header.pull-left .no-print {
                width: 100%;
            }

            div#pos {
                width: 100%;
                float: left;
            }

            .btn-group.btn-group-justified.pos-grid-nav {
                display: none;
            }

            #proContainer #ajaxproducts div#item-list button.product img {
                width: 100%!important;
                height: auto!important;
            }

            #proContainer #ajaxproducts div#item-list button.product {width: 32%!important;max-width: 32%!important;}

            #pos #leftdiv {
                padding: 0px;
                float: left;
                min-width: auto;
                width: 100%;
                max-width: 100%;
            }

            div#cp {
                display: none;
            }

            #pos #cp #cpinner {
                padding: 0px;
                width: 100%;
                margin: 0px;
                min-width: auto;
                max-width: 100%;
            }
            .btn-cat-con{
                display: none;
            }
            
            div#pos2 {
                float: left;
                width: 100%;
            }

            div#navbar-container .no-print .form-group {
                margin: 0px;
            }

            div#navbar-container {
                padding: 0px;
            }
          
        }
    </style>
</head>
<body>
<noscript>
    <div class="global-site-notice noscript">
        <div class="notice-inner">
            <p><strong>JavaScript seems to be disabled in your browser.</strong><br>You must have JavaScript enabled in
                your browser to utilize the functionality of this website.</p>
        </div>
    </div>
</noscript>
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
	<?php $attrib = array('data-toggle' => 'validator', 'role' => 'form', 'id' => 'pos-sale-form');
			echo form_open("pos", $attrib);?>
<div id="wrapper">
 <div id="pos">
		
	<div id="navbar" class="navbar navbar-default  ace-save-state">

			<div class="navbar-container ace-save-state" id="navbar-container">
				<button type="button" class="navbar-toggle menu-toggler pull-left" id="menu-toggler" data-target="#sidebar">
					<span class="sr-only">Toggle sidebar</span>

					<span class="icon-bar"></span>

					<span class="icon-bar"></span>

					<span class="icon-bar"></span>
				</button>
				
				<div class="navbar-header pull-left">
					<div class="no-print">
							<div class="form-group" id="ui">
								<?php if ($Owner || $Admin || $GP['products-add']) { ?>
								<div class="input-group">
								<?php } ?>
								<?php echo form_input('add_item', '', 'class="form-control pos-tip" id="add_item" data-placement="top" data-trigger="focus" placeholder="' . $this->lang->line("search_product_by_name_code") ." (".$pos_settings->focus_add_item. ')" title="' . $this->lang->line("au_pr_name_tip") . '"'); ?>
								<?php if ($Owner || $Admin || $GP['products-add']) { ?>
									<div class="input-group-addon" style="padding: 2px 8px;">
										<a href="#" id="addManually">
											<i class="fa fa-plus" id="addIcon" style="font-size: 1.5em;"></i>
										</a>
									</div>
                                    
								</div>

								<?php } ?>
                               
								<div style="clear:both;"></div>
							</div>		
                                
					</div>
				</div>

				<div class="navbar-buttons navbar-header pull-right" role="navigation">
					<ul class="nav ace-nav">
						<li class="white">
							<a  class="" href="<?= site_url('welcome') ?>" title="<?= lang('dashboard') ?>">
								<i class="ace-icon fa fa-dashboard"></i>								
							</a>
						</li>
					    <?php if ($Owner) { ?>
						<li class="grey2 dropdown-modal">
							<a class="" title="<?= lang('settings') ?>" data-placement="bottom" href="<?= site_url('system_settings') ?>">
								<i class="fa fa-cogs"></i>
								
							</a>
						</li>
						<?php } ?>
                        <?php 
                            $thongbao=(int)$this->site->cms_Show_ThongBao();       
                            if ($thongbao>9) 
                            {
                                $thongbao="9+";                                         
                            }                         
                            ?>
                             <li>
                                <label style="margin: 0px;color: white;width: 50px;text-align: center;">
                                    <a href="auth/thongbao" class="blightOrange3" title="Thông báo">
                                    <span class="white icon" data-icon-label="<?=$thongbao?>">
                                        <i class="fa fa-envelope-o"></i>   
                                    </span></a>
                                </label>
                            </li>
                            <li>
                                <label style="margin: 0px;color: white;width: 70px;text-align: center;">
                                    <a href="auth/history" class="blightOrange3" title="Lịch sử giao dịch">
                                    <span class="white icon">
                                        <i class="fa fa-history"></i> Lịch sử
                                    </span></a>
                                </label>
                            </li>
						<li class="grey2 dropdown-modal">
							<a class="" title="<?= lang('calculator') ?>" data-placement="bottom" href="#" data-toggle="dropdown">
								<i class="fa fa-calculator"></i>
								
								
							</a>
							<ul class="dropdown-menu-right dropdown-navbar dropdown-menu dropdown-caret pull-right calc">
								<li class="dropdown-content">
									<span id="inlineCalc"></span>
								</li>
							</ul>
						</li>
						<li class="grey2">
                        <a class="" title="<?=lang('shortcuts')?>" data-placement="bottom" href="#" data-toggle="modal" data-target="#sckModal">
                            <i class="fa fa-key"></i>
							<span class="paddingpadding05"></span>
                        </a>
						</li>
						<li class="">
                        <a class="" title="<?=lang('view_bill_screen')?>" data-placement="bottom" href="<?=site_url('pos/view_bill')?>" target="_blank">
                            <i class="fa fa-laptop"></i>
							<span class="paddingpadding05"></span>
                        </a>
                    </li>
                    <li class="">
                        <a class="blightOrange2 pos-tip" id="opened_bills" title="<?=lang('suspended_sales')?>" data-placement="bottom" data-html="true" href="<?=site_url('pos/opened_bills')?>" data-toggle="ajax">
                            <i class="fa fa-th"></i>
							<span class="paddingpadding05"></span>
                        </a>
                    </li>
                                    
                    <li class="">
                        <a class="borange2 pos-tip" id="add_expense" title="<?=lang('add_expense')?>" data-placement="bottom" data-html="true" href="<?=site_url('purchases/add_expense')?>" data-toggle="modal" data-target="#myModal">
                            <i class="fa fa-dollar"></i>
							<span class="paddingpadding05"></span>
                        </a>
                    </li>
                    <?php if ($Owner) {?>
                        <li class="">
                            <a class="bdarkGreen2 pos-tip" id="today_profit" title="<?=lang('today_profit')?>" data-placement="bottom" data-html="true" href="<?=site_url('reports/profit')?>" data-toggle="modal" data-target="#myModal">
                                <i class="fa fa-hourglass-half"></i>
                            </a>
                        </li>
                    <?php }
                    ?>
                    <?php if ($Owner || $Admin) {?>
                        <li class="">
                            <a class="bdarkGreen2 pos-tip" id="today_sale" title="<?=lang('today_sale')?>" data-placement="bottom" data-html="true" href="<?=site_url('pos/today_sale')?>" data-toggle="modal" data-target="#myModal">
                                <i class="fa fa-heart"></i>
                            </a>
                        </li>
                        
                        <li class="">
                            <a class="bred2 pos-tip" title="<?=lang('clear_ls')?>" data-placement="bottom" id="clearLS" href="#">
                                <i class="fa fa-eraser"></i>
                            </a>
                        </li>
                    <?php }
                    ?>
						<li class="light-blue dropdown-modal">
							<a data-toggle="dropdown" href="#" class=" dropdown-toggle">
								<img class="nav-user-photo" src="<?= $this->session->userdata('avatar') ? site_url() . 'assets/uploads/avatars/thumbs/' . $this->session->userdata('avatar') : base_url('assets/images/' . $this->session->userdata('gender') . '.png'); ?>" alt="<?= $this->session->userdata('username'); ?>" />
								<span class="user-info">
									<small><?= lang('welcome') ?>,</small>
									 <?= $this->session->userdata('username'); ?>
								</span>

								<i class="ace-icon fa fa-caret-down"></i>
							</a>

							<ul class="user-menu dropdown-menu-right dropdown-menu dropdown-yellow dropdown-caret dropdown-close">
								<li>
									<a href="<?= site_url('users/profile/' . $this->session->userdata('user_id')); ?>">
										<i class="ace-icon fa fa-user"></i> <?= lang('profile'); ?>
										
									</a>
								</li>
								<li>
                                <a href="<?= site_url('users/profile/' . $this->session->userdata('user_id') . '/#cpassword'); ?>"><i class="ace-icon fa fa-key"></i> <?= lang('change_password'); ?>
                                </a>
                            </li>
							  <li>
                                <a href="<?= site_url('logout'); ?>">
                                    <i class="ace-icon fa fa-power-off"></i> <?= lang('logout'); ?>
                                </a>
                            </li>
								
							</ul>
						</li>
					   <li class="dropdown">
                                <?php                                 
                                if (!$Owner && !$Admin)
                                { 
                                    $user = $this->site->getUser();
                                    $_active_dfwh=(int)$user->group_id;

                                    $permission=(array)json_decode($user->new_permission);
                                    $permission[]=$_active_dfwh;
                                    $permission=array_unique($permission);
                                    
                                    foreach ($permission as $warehouse) { 
                                        $perobj=$this->site->getPerissionByID($warehouse);                                       
                                        $whlhson[$warehouse] =$perobj->description;                                      
                                    }
                                    echo form_dropdown('lhson_chinhanh', $whlhson, $_active_dfwh, 'id="lhson_chinhanh" class="form-control input-tip skip" data-placeholder="' . lang("select") . ' ' . lang("permission") . '" style="width:100%;" ');
                                }
                                ?>
                        </li>
					</ul>
				</div>
			</div><!-- /.navbar-container -->
	</div>
 
    <div id="content">
        <div class="c1">
            <div class="pos">
                <?php
                	if ($error) {
                	    echo "<div class=\"alert alert-danger\"><button type=\"button\" class=\"close fa-2x\" data-dismiss=\"alert\">&times;</button>" . $error . "</div>";
                	}
                ?>
                <?php
                	if ($message) {
                	    echo "<div class=\"alert alert-success\"><button type=\"button\" class=\"close fa-2x\" data-dismiss=\"alert\">&times;</button>" . $message . "</div>";
                	}
                ?>
                <div id="pos2">
                   
                    <div id="leftdiv">
                        <div id="printhead">
                            <h4 style="text-transform:uppercase;"><?php echo $Settings->site_name; ?></h4>
                            <?php
                            	echo "<h5 style=\"text-transform:uppercase;\">" . $this->lang->line('order_list') . "</h5>";
                            	echo $this->lang->line("date") . " " . $this->sma->hrld(date('Y-m-d H:i:s'));
                            ?>
                        </div>
                        <div id="left-top">
                            <div
                                style="position: absolute; <?=$Settings->user_rtl ? 'right:-9999px;' : 'left:-9999px;';?>"><?php echo form_input('test', '', 'id="test" class="kb-pad"'); ?></div>
                            
                            <div class="no-print row">								
                                <div class="col-md-6">
								<?php
                                $col_warehouse=6;
                                 if ($Owner || $Admin || !$this->session->userdata('warehouse_id')) {
                                    ?>
                                    <div class="form-group">
                                        <?php
                                        	
                                        	    foreach ($warehouses as $warehouse) {
                                        	        $wh[$warehouse->id] = $warehouse->name;
                                        	    }
                                        	    echo form_dropdown('warehouse', $wh, (isset($_POST['warehouse']) ? $_POST['warehouse'] : $Settings->default_warehouse), 'id="poswarehouse" class="form-control pos-input-tip" data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("warehouse") . '" required="required" onchange="loadProductAjax()" style="width:100%;" ');
                                            ?>
                                    </div>
                                <?php } else {
                                    $col_warehouse=12;

                                	    $warehouse_input = array(
                                	        'type' => 'hidden',
                                	        'name' => 'warehouse',
                                	        'id' => 'poswarehouse',
                                	        'value' => $this->session->userdata('warehouse_id'),
                                	    );

                                	    echo form_input($warehouse_input);
                                	}
                                ?>
								</div>
                                <div class="col-md-<?=$col_warehouse;?>">
									<div class="form-group" id="div_doitac_id">																			
											
                                            <?php                                     
                                                
                                                $dt["0"] = "Chọn đối tác";
                                                foreach ($doitacs as $tax) { 
                                                    $dt[$tax->id] = $tax->name; 
                                                }
                                                echo form_dropdown('doitac', $dt, (isset($_POST['doitac']) ? $_POST['doitac'] : ""), 'id="doitac" data-placeholder="' . lang("select") . ' ' . lang("doitac") . '" class="form-control input-tip select" style="width:100%;"');
                                                ?>
									</div>
								</div>
                            </div>
                        </div>
                        <div id="print">
                            <div id="left-middle">
                                <div id="product-list">
                                    <table class="table items table-striped table-bordered table-condensed table-hover sortable_table"
                                           id="posTable" style="margin-bottom: 0;">
                                        <thead>
                                        <tr>
                                            <th width="40%"><?=lang("product");?></th>
                                            <th width="15%"><?=lang("price");?></th>
                                            <th width="15%"><?=lang("qty");?></th>
                                            <th width="20%"><?=lang("subtotal");?></th>
                                            <th style="width: 5%; text-align: center;">
                                                <i class="fa fa-trash-o" style="opacity:0.5; filter:alpha(opacity=50);"></i>
                                            </th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                    </table>
                                    <div style="clear:both;"></div>
                                </div>
                            </div>
                            <div style="clear:both;"></div>
                            <div id="left-bottom">
                                <table id="totalTable"
                                       style="width:100%; float:right; padding:5px; color:#000; background: #FFF;">
                                    <tr>
                                        <td colspan="4" id="clmkhachhanglhson">
											<div class="form-group">
												<div class="input-group">
												<?php
													echo form_input('customer', (isset($_POST['customer']) ? $_POST['customer'] : ""), 'id="poscustomer" data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("customer") . '" required="required" class="form-control pos-input-tip" style="width:100%;"');
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
														<a href="<?=site_url('customers/add');?>" id="add-customer" class="external" data-toggle="modal" data-target="#myModal">
															<i class="fa fa-plus" id="addIcon" style="font-size: 1.5em;"></i>
														</a>
													</div>
												<?php } ?>
												</div>
												<div style="clear:both;"></div>
											</div>
										</td>
                                       
                                    </tr>
									<tr>
                                        <td style="padding: 5px 10px;border-top: 1px solid #DDD;"><?=lang('SL');?></td>
                                        <td class="text-right" style="padding: 5px 10px;font-size: 14px; font-weight:bold;border-top: 1px solid #DDD;">
                                            <span id="titems">0</span>
                                        </td>
                                        <td style="padding: 5px 10px;border-top: 1px solid #DDD;"><?=lang('total');?></td>
                                        <td class="text-right" style="padding: 5px 10px;font-size: 14px; font-weight:bold;border-top: 1px solid #DDD;">
                                            <span id="total">0.00</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 5px 10px;"><?=lang('Thuế');?>
                                            <a href="#" id="pptax2">
                                                <i class="fa fa-edit"></i>
                                            </a>
                                        </td>
                                        <td class="text-right" style="padding: 5px 10px;font-size: 14px; font-weight:bold;">
                                            <span id="ttax2">0.00</span>
                                            <span id="lhsonghichu">
                                                <i class="pull-right fa fa-comment-o tip pointer" id="ghichuup" data-item="160" title="Ghi chú đơn hàng" style="cursor:pointer;margin-right:5px;"></i>
                                            </span>
                                        </td>
                                        <td style="padding: 5px 10px;"><?=lang('Giảm');?>
                                            <?php if ($Owner || $Admin || $this->session->userdata('allow_discount')) { ?>
                                            <a href="#" id="ppdiscount">
                                                <i class="fa fa-edit"></i>
                                            </a>
                                            <?php } ?>
                                        </td>
                                        <td class="text-right" style="padding: 5px 10px;font-weight:bold;">
                                            <span id="tds">0.00</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 5px 10px; border-top: 1px solid #666; border-bottom: 1px solid #333; font-weight:bold; background:#ff0202; color:#FFF;" colspan="2">
                                            <!-- <?=lang('total_payable');?>--> T&#7893;ng c&#7897;ng:&nbsp; 
                                            <a href="#" id="pshipping">
                                                <font color="#fff"><i class="fa fa-truck"></i></font>
                                            </a>
                                            <span id="tship"></span>
                                        </td>
                                        <td class="text-right" style="padding:5px 10px 5px 10px; font-size: 14px;border-top: 1px solid #666; border-bottom: 1px solid #333; font-weight:bold; background:#ff0202; color:#FFF;" colspan="2">
                                            <span id="gtotal">0.00</span>
                                        </td>
                                    </tr>
                                </table>

                                <div class="clearfix"></div>
                                <div id="botbuttons" class="col-xs-12 text-center">
                                    <input type="hidden" name="biller" id="biller" value="<?= ($Owner || $Admin || !$this->session->userdata('biller_id')) ? $pos_settings->default_biller : $this->session->userdata('biller_id')?>"/>
                                    <div class="row">
                                        <div class="col-xs-6" style="padding: 0;">
                                            <div class="btn-group-vertical btn-block">
                                                <button type="button" class="btn btn-success btn-block btn-flat"
                                                id="suspend">
                                                    <?=lang('suspend'); ?> (<?=$pos_settings->suspend_sale?>)
                                                </button>
                                                <button type="button" class="btn btn-default btn-block btn-flat"
                                                id="reset">
                                                    <?= lang('cancel'); ?> (<?=$pos_settings->cancel_sale?>)
                                                </button>
                                            </div>

                                        </div>
                                        <div class="col-xs-4" style="padding: 0;display:none" >
                                            <div class="btn-group-vertical btn-block">
                                                <button type="button" class="btn btn-info btn-block" id="print_order">
                                                    <?=lang('order');?>
                                                </button>

                                                <button type="button" class="btn btn-primary btn-block" id="print_bill">
                                                    <?=lang('bill');?> 
                                                </button>
                                            </div>
                                        </div>
                                        <div class="col-xs-6" style="padding: 0;">
                                            <button type="button" class="btn btn-warning btn-block" id="payment" style="height:79px;">
                                                <i class="fa fa-money" style="margin-right: 5px;"></i><?=lang('payment');?> (<?=$pos_settings->finalize_sale?>)
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div style="clear:both; height:5px;"></div>
                                <div id="num">
                                    <div id="icon"></div>
                                </div>
                                <span id="hidesuspend"></span>
                                <input type="hidden" name="diemtichluy_total" value="" id="diemtichluy_total">
                                <input type="hidden" name="tiencoc_total" value="" id="tiencoc_total">
                                <input type="hidden" name="pos_note" value="" id="pos_note">
                                <input type="hidden" name="staff_note" value="" id="staff_note">

                                <input type="hidden" name="tmmtposhide" value="" id="tmmtposhide">
                                <input type="hidden" name="addmtposhide" value="" id="addmtposhide">
                                <input type="hidden" name="loanmtposhide" value="" id="loanmtposhide">
                                <input type="hidden" name="vienmtposhide" value="" id="vienmtposhide">                              
                                <input type="hidden" name="canmtposhide" value="" id="canmtposhide">

                                <input type="hidden" name="tmmpposhide" value="" id="tmmpposhide">
                                <input type="hidden" name="addmpposhide" value="" id="addmpposhide">
                                <input type="hidden" name="loanmpposhide" value="" id="loanmpposhide">
                                <input type="hidden" name="vienmpposhide" value="" id="vienmpposhide">
                                <input type="hidden" name="canmpposhide" value="" id="canmpposhide">

                                <input type="hidden" name="dpposhide" value="" id="dpposhide">


                                <div id="payment-con">
                                    <?php for ($i = 1; $i <= 10; $i++) {?>
                                        <input type="hidden" name="amount[]" id="amount_val_<?=$i?>" value=""/>
                                        <input type="hidden" name="balance_amount[]" id="balance_amount_<?=$i?>" value=""/>
                                        <input type="hidden" name="paid_by[]" id="paid_by_val_<?=$i?>" value="cash"/>
                                        <input type="hidden" name="cc_no[]" id="cc_no_val_<?=$i?>" value=""/>
                                        <input type="hidden" name="paying_gift_card_no[]" id="paying_gift_card_no_val_<?=$i?>" value=""/>
                                        <input type="hidden" name="cc_holder[]" id="cc_holder_val_<?=$i?>" value=""/>
                                        <input type="hidden" name="cheque_no[]" id="cheque_no_val_<?=$i?>" value=""/>
                                        <input type="hidden" name="cc_month[]" id="cc_month_val_<?=$i?>" value=""/>
                                        <input type="hidden" name="cc_year[]" id="cc_year_val_<?=$i?>" value=""/>
                                        <input type="hidden" name="cc_type[]" id="cc_type_val_<?=$i?>" value=""/>
                                        <input type="hidden" name="cc_cvv2[]" id="cc_cvv2_val_<?=$i?>" value=""/>
                                        <input type="hidden" name="payment_note[]" id="payment_note_val_<?=$i?>" value=""/>
                                    <?php }
                                    ?>
                                </div>
                                <input name="order_tax" type="hidden" value="<?=$suspend_sale ? $suspend_sale->order_tax_id : $Settings->default_tax_rate2;?>" id="postax2">
                                <input name="discount" type="hidden" value="<?=$suspend_sale ? $suspend_sale->order_discount_id : '';?>" id="posdiscount">
                                <input name="shipping" type="hidden" value="<?=$suspend_sale ? $suspend_sale->shipping : '0';?>" id="posshipping">
                                <input type="hidden" name="rpaidby" id="rpaidby" value="cash" style="display: none;"/>
                                <input type="hidden" name="total_items" id="total_items" value="0" style="display: none;"/>
                                <input type="submit" id="submit_sale" value="Submit Sale" style="display: none;"/>
                            </div>
                        </div>

                    </div>
                    <div id="cp">
                        <div id="cpinner">
                            <div class="quick-menu">
                                <div id="proContainer">
                                    <div id="ajaxproducts">
                                        <div id="item-list">
                                            <?php echo $products; ?>
                                        </div>
                                       <div class="btn-group btn-group-justified pos-grid-nav">
                                            
											<div class="shortcut hidden-1024">
												<span onclick="jQuery('#add_item').focus();" class="btn btn-default btn-sm hidden-768">Thêm hàng hóa(<?=$pos_settings->focus_add_item?>)</span>
												<span onclick="jQuery('#add-customer').trigger('click');" class="btn btn-default btn-sm hidden-768">Thêm khách(<?=$pos_settings->add_customer?>)</span>
												<span onclick="jQuery('#toogle-customer-read-attr').trigger('click');" class="btn btn-default btn-sm hidden-768">Chọn khách(<?=$pos_settings->customer_selection?>)</span>
												
												<span onclick="jQuery('#open-category').trigger('click');" class="btn btn-default btn-sm hidden-768">Danh mục(<?=$pos_settings->toggle_category_slider?>)</span>
												
												<span onclick="jQuery('#suspend').trigger('click');" class="btn btn-default btn-sm hidden-768">Chờ thanh toán(<?=$pos_settings->suspend_sale?>)</span>
												<span onclick="jQuery('#payment').trigger('click');" class="btn btn-default btn-sm hidden-768">Thanh toán(<?=$pos_settings->finalize_sale?>)</span>								
											</div>

                                        </div>
                                    </div>
                                    <div style="clear:both;"></div>
                                </div>
                            </div>
                        </div>
                        <div style="clear:both;"></div>
                    </div>
                    <div style="clear:both;"></div>
                </div>
                <div style="clear:both;"></div>
            </div>
        </div>
    </div> <!-- end main content lhson -->
	<!-- end main content lhson -->
	</div>
</div><!-- end wapper form lhson -->
<div class="rotate btn-cat-con">
    <button type="button" id="open-brands" class="btn btn-info open-brands"><?= lang('brands'); ?></button>
    <button type="button" id="open-subcategory" class="btn btn-warning open-subcategory"><?= lang('subcategories'); ?></button>
    <button type="button" id="open-category" class="btn btn-primary open-category"><?= lang('categories'); ?></button>
</div>
<div id="brands-slider">
    <div id="brands-list">
        <?php
            foreach ($brands as $brand) {
                echo "<button id=\"brand-" . $brand->id . "\" type=\"button\" value='" . $brand->id . "' class=\"btn-prni brand\" ><img src=\"assets/uploads/thumbs/" . ($brand->image ? $brand->image : 'no_image.png') . "\" class='img-rounded img-thumbnail' /><span>" . $brand->name . "</span></button>";
            }
        ?>
    </div>
</div>
<div id="category-slider">
    <button type="button" class="close open-category"><i class="fa fa-2x">&times;</i></button>
    <div id="category-list">
        <?php
        echo "<button id=\"category-0\" type=\"button\" value='0' class=\"btn-prni category\" >
        <img src=\"assets/uploads/thumbs/no_image.png\"  class='img-rounded img-thumbnail' /><span>Tất cả</span></button>";

        	//for ($i = 1; $i <= 40; $i++) {
        	foreach ($categories as $category) {
        	    echo "<button id=\"category-" . $category->id . "\" type=\"button\" value='" . $category->id . "' class=\"btn-prni category\" ><img src=\"assets/uploads/thumbs/" . ($category->image ? $category->image : 'no_image.png') . "\"  class='img-rounded img-thumbnail' /><span>" . $category->name . "</span></button>";
        	}
        	//}
        ?>
    </div>
</div>
<div id="subcategory-slider">
    <!--<button type="button" class="close open-category"><i class="fa fa-2x">&times;</i></button>-->
    <div id="subcategory-list">
        <?php
         echo "<button id=\"subcategory-0\" type=\"button\" value='0' class=\"btn-prni subcategory\" ><img src=\"assets/uploads/thumbs/no_image.png\" style='width:" . $Settings->twidth . "px;height:" . $Settings->theight . "px;' class='img-rounded img-thumbnail' /><span>Tất cả</span></button>";

        	if (!empty($subcategories)) {
        	    foreach ($subcategories as $category) {
        	        echo "<button id=\"subcategory-" . $category->id . "\" type=\"button\" value='" . $category->id . "' class=\"btn-prni subcategory\" ><img src=\"assets/uploads/thumbs/" . ($category->image ? $category->image : 'no_image.png') . "\" style='width:" . $Settings->twidth . "px;height:" . $Settings->theight . "px;' class='img-rounded img-thumbnail' /><span>" . $category->name . "</span></button>";
        	    }
        	}
        ?>
    </div>
</div>
<div class="modal fade in" id="paymentModal" tabindex="-1" role="dialog" aria-labelledby="payModalLabel"
     aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true"><i
                            class="fa fa-2x">&times;</i></span><span class="sr-only"><?=lang('close');?></span></button>
                <h4 class="modal-title" id="payModalLabel"><?=lang('finalize_sale');?></h4>
            </div>
            <div class="modal-body" id="payment_content">
                <div class="row">
                    <div class="col-md-10 col-sm-9">
                        <div class="row">
                            <div class="col-lg-6">
                                <label id="showKhachHang">Khách hàng</label>
                                <p id="showDiemTichLuy"></p>
                            </div>
                            <div class="col-lg-6">
                                <label id="showKhachHang">Tiền cọc</label>
                                <p id="showTiencoc"></p>
                                <?php if ($Owner || $Admin || !$this->session->userdata('biller_id')) { ?>
                                        <?php
                                            foreach ($billers as $biller) {
                                                $btest = ($biller->company && $biller->company != '-' ? $biller->company : $biller->name);
                                                $bl[$biller->id] = $btest;
                                                $posbillers[] = array('logo' => $biller->logo, 'company' => $btest);
                                                if ($biller->id == $pos_settings->default_biller) {
                                                    $posbiller = array('logo' => $biller->logo, 'company' => $btest);
                                                }
                                            }
                                            //echo form_dropdown('biller', $bl, (isset($_POST['biller']) ? $_POST['biller'] : $pos_settings->default_biller), 'class="form-control" id="posbiller" required="required"');
                                            
                                            $biller_input = array(
                                            'type' => 'hidden',
                                            'name' => 'biller',
                                            'id' => 'posbiller',
                                            'value' => (isset($_POST['biller']) ? $_POST['biller'] : $pos_settings->default_biller),
                                        );
                                            echo form_input($biller_input);
                                        ?>
                                <?php } else {
                                        $biller_input = array(
                                            'type' => 'hidden',
                                            'name' => 'biller',
                                            'id' => 'posbiller',
                                            'value' => $this->session->userdata('biller_id'),
                                        );

                                        echo form_input($biller_input);

                                        foreach ($billers as $biller) {
                                            $btest = ($biller->company && $biller->company != '-' ? $biller->company : $biller->name);
                                            $posbillers[] = array('logo' => $biller->logo, 'company' => $btest);
                                            if ($biller->id == $this->session->userdata('biller_id')) {
                                                $posbiller = array('logo' => $biller->logo, 'company' => $btest);
                                            }
                                        }
                                    }
                                ?>
                            </div>
                        </div>
                        

                        <div class="form-group">
                            <div class="row">                                
                                <div class="col-sm-12">
                                    <?=form_textarea('staffnote', '', 'id="staffnote" class="form-control kb-text skip" style="height: 100px;" placeholder="' . lang('staff_note') . '" maxlength="250"');?>
                                </div>
                            </div>
                        </div>
                        <div class="clearfir"></div>
                        <div id="payments">
                            <div class="well well-sm well_1">
                                <div class="payment">
                                    <div class="row">
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <?=lang("amount_sotien", "amount_1");?>
                                                <input name="amount[]" type="text" id="amount_1"
                                                       class="pa form-control kb-pad1 amount"/>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <?=lang("paying_by", "paid_by_1");?>
                                                <select name="paid_by[]" id="paid_by_1" class="form-control paid_by">
                                                    <?= $this->sma->paid_opts(); ?>
                                                    <?=$pos_settings->paypal_pro ? '<option value="ppp">' . lang("paypal_pro") . '</option>' : '';?>
                                                    <?=$pos_settings->stripe ? '<option value="stripe">' . lang("stripe") . '</option>' : '';?>
                                                    <?=$pos_settings->authorize ? '<option value="authorize">' . lang("authorize") . '</option>' : '';?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-12">
                                            <div class="col-md-12 diem_tichluy_no_1" style="display: none;">
                                                <div class="col-sm-6">
                                                    <div class="form-group">
                                                        <?=lang("Số điểm cần đổi", "diem_tichluy_no_1");?>
                                                        <input name="diem_tichluy_no[]" type="text" id="diem_tichluy_no_1"
                                                               class="pa form-control kb-pad diem_tichluy_no_1"/>
                                                        <div id="diem_tichluy_details_1"></div>
                                                    </div>
                                                </div>
                                                <div class="col-sm-6">
                                                    <div class="form-group">
                                                        <?=lang("Số tiền quy đổi", "diem_tichluy_tien_1");?>
                                                        <input name="diem_tichluy_tien[]" type="text" id="diem_tichluy_tien_1"
                                                               class="pa form-control kb-pad diem_tichluy_tien_1"/>
                                                        <div id="diem_tichluy_tien_details_1"></div>
                                                    </div>
                                                </div>
                                                <button type="button" data-no="1" class="btn btn-primary col-md-12 addButtonDoiDiem" tabindex="-1"><i class="fa fa-save"></i> Đổi điểm thành thẻ giảm giá</button>
                                            </div>                                           
                                            
                                            <div class="form-group gc_1" style="display: none;">
                                                <?=lang("gift_card_no", "gift_card_no_1");?>
                                                <input name="paying_gift_card_no[]" type="text" id="gift_card_no_1"
                                                       class="pa form-control kb-pad gift_card_no"/>

                                                <div id="gc_details_1"></div>
                                            </div>
                                            <div class="pcc_1" style="display:none;">
                                                <div class="form-group">
                                                    <input type="text" id="swipe_1" class="form-control swipe"
                                                           placeholder="<?=lang('swipe')?>"/>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <input name="cc_no[]" type="text" id="pcc_no_1"
                                                                   class="form-control"
                                                                   placeholder="<?=lang('cc_no')?>"/>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">

                                                            <input name="cc_holer[]" type="text" id="pcc_holder_1"
                                                                   class="form-control"
                                                                   placeholder="<?=lang('cc_holder')?>"/>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="form-group">
                                                            <select name="cc_type[]" id="pcc_type_1"
                                                                    class="form-control pcc_type"
                                                                    placeholder="<?=lang('card_type')?>">
                                                                <option value="Visa"><?=lang("Visa");?></option>
                                                                <option
                                                                    value="MasterCard"><?=lang("MasterCard");?></option>
                                                                <option value="Amex"><?=lang("Amex");?></option>
                                                                <option
                                                                    value="Discover"><?=lang("Discover");?></option>
                                                            </select>
                                                            <!-- <input type="text" id="pcc_type_1" class="form-control" placeholder="<?=lang('card_type')?>" />-->
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="form-group">
                                                            <input name="cc_month[]" type="text" id="pcc_month_1"
                                                                   class="form-control"
                                                                   placeholder="<?=lang('month')?>"/>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="form-group">

                                                            <input name="cc_year" type="text" id="pcc_year_1"
                                                                   class="form-control"
                                                                   placeholder="<?=lang('year')?>"/>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="form-group">

                                                            <input name="cc_cvv2" type="text" id="pcc_cvv2_1"
                                                                   class="form-control"
                                                                   placeholder="<?=lang('cvv2')?>"/>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="pcheque_1" style="display:none;">
                                                <div class="form-group"><?=lang("cheque_no", "cheque_no_1");?>
                                                    <input name="cheque_no[]" type="text" id="cheque_no_1"
                                                           class="form-control cheque_no"/>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <?=lang('payment_note', 'payment_note');?>
                                                <textarea name="payment_note[]" id="payment_note_1"
                                                          class="pa form-control kb-text payment_note"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div id="multi-payment"></div>
                        <button type="button" class="btn btn-primary col-md-12 addButton"><i
                                class="fa fa-plus"></i> <?=lang('add_more_payments')?></button>
                        <div style="clear:both; height:15px;"></div>
                        <div class="font16">
                            <table class="table table-bordered table-condensed table-striped" style="margin-bottom: 0;">
                                <tbody>
                                <tr>
                                    <td width="25%"><?=lang("total_items");?></td>
                                    <td width="25%" class="text-right"><span id="item_count">0.00</span></td>
                                    <td width="25%"><?=lang("total_payable");?></td>
                                    <td width="25%" class="text-right"><span id="twt">0.00</span></td>
                                </tr>
                                <tr>
                                    <td><?=lang("total_paying");?></td>
                                    <td class="text-right"><span id="total_paying">0.00</span></td>
                                    <td><?=lang("balance");?></td>
                                    <td class="text-right"><span id="balance">0.00</span></td>
                                </tr>
                                </tbody>
                            </table>
                            <div class="clearfix"></div>
                        </div>
                    </div>
                    <div class="col-md-2 col-sm-3 text-center">
                        <span style="font-size: 1.2em; font-weight: bold;"><?=lang('quick_cash');?></span>

                        <div class="btn-group btn-group-vertical">
                            <button type="button" class="btn btn-lg btn-info quick-cash" id="quick-payable">0.00
                            </button>
                            <?php
                            	foreach (lang('quick_cash_notes') as $cash_note_amount) {
                            	    echo '<button type="button" class="btn btn-lg btn-warning quick-cash">' . $cash_note_amount . '</button>';
                            	}
                            ?>
                            <button type="button" class="btn btn-lg btn-danger"
                                    id="clear-cash-notes"><?=lang('clear');?></button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-block btn-lg btn-primary" id="submit-sale"><?=lang('submit');?></button>
            </div>
        </div>
    </div>
</div>

<div class="modal" id="cmModal" tabindex="-1" role="dialog" aria-labelledby="cmModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">
                    <i class="fa fa-2x">&times;</i></span>
                    <span class="sr-only"><?=lang('close');?></span>
                </button>
                <h4 class="modal-title" id="cmModalLabel"></h4>
            </div>
            <div class="modal-body" id="pr_popover_content">
                <div class="form-group">
                    <?= lang('Ghi chú sản phẩm', 'icomment'); ?>
                    <?= form_textarea('comment', '', 'class="form-control" id="icomment" style="height:100px!important"'); ?>
                </div>
                <div class="form-group" style="display: none;">
                    <?= lang('ordered', 'iordered'); ?>
                    <?php
                    $opts = array(0 => lang('no'), 1 => lang('yes'));
                    ?>
                    <?= form_dropdown('ordered', $opts, '', 'class="form-control" id="iordered" style="width:100%;"'); ?>
                </div>
                <input type="hidden" id="irow_id" value=""/>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="editComment"><?=lang('submit')?></button>
            </div>
        </div>
    </div>
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
                    <?php if ($Settings->tax1) {
                        ?>
                        <div class="form-group">
                            <label class="col-sm-4 control-label"><?=lang('product_tax')?></label>
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
                            <label for="pserial" class="col-sm-4 control-label"><?=lang('serial_no')?>/Imei</label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control kb-text" id="pserial">
                            </div>
                        </div>
                    <?php } ?>
                    <div class="form-group">
                        <label for="pquantity" class="col-sm-4 control-label"><?=lang('quantity')?></label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control kb-pad" id="pquantity">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="punit" class="col-sm-4 control-label"><?= lang('product_unit') ?></label>
                        <div class="col-sm-8">
                            <div id="punits-div"></div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="poption" class="col-sm-4 control-label"><?=lang('product_option')?></label>
                        <div class="col-sm-8">
                            <div id="poptions-div"></div>
                        </div>
                    </div>
                    <?php if ($Settings->product_discount && ($Owner || $Admin || $this->session->userdata('allow_discount'))) { ?>
                        <div class="form-group">
                            <label for="pdiscount" class="col-sm-4 control-label"><?=lang('product_discount')?></label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control kb-pad" id="pdiscount">
                            </div>
                        </div>
                    <?php } ?>
                    <div class="form-group">
                        <label for="pprice" class="col-sm-4 control-label"><?=lang('unit_price')?></label>

                        <div class="col-sm-8">
                            <input type="text" class="form-control kb-pad" id="pprice" <?= ($Owner || $Admin || $GP['edit_price']) ? '' : 'readonly'; ?>>
                        </div>
                    </div>
					<?php if ($Settings->use_gia_si && ($Owner || $Admin || $this->session->userdata('allow_discount'))) { ?>
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
                            <th style="width:25%;"><?=lang('net_unit_price');?></th>
                            <th style="width:25%;"><span id="net_price"></span></th>
                            <th style="width:25%;"><?=lang('product_tax');?></th>
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
                <button type="button" class="btn btn-primary" id="editItem"><?=lang('submit')?></button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade in" id="gcModal" tabindex="-1" role="dialog" aria-labelledby="mModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i
                        class="fa fa-2x">&times;</i></button>
                <h4 class="modal-title" id="myModalLabel"><?=lang('sell_gift_card');?></h4>
            </div>
            <div class="modal-body">
                <p><?=lang('enter_info');?></p>

                <div class="alert alert-danger gcerror-con" style="display: none;">
                    <button data-dismiss="alert" class="close" type="button">×</button>
                    <span id="gcerror"></span>
                </div>
                <div class="form-group">
                    <?=lang("card_no", "gccard_no");?> *
                    <div class="input-group">
                        <?php echo form_input('gccard_no', '', 'class="form-control" id="gccard_no"'); ?>
                        <div class="input-group-addon" style="padding-left: 10px; padding-right: 10px;">
                            <a href="#" id="genNo"><i class="fa fa-cogs"></i></a>
                        </div>
                    </div>
                </div>
                <input type="hidden" name="gcname" value="<?=lang('gift_card')?>" id="gcname"/>

                <div class="form-group">
                    <?=lang("value", "gcvalue");?> *
                    <?php echo form_input('gcvalue', '', 'class="form-control" id="gcvalue"'); ?>
                </div>
                <div class="form-group">
                    <?=lang("price", "gcprice");?> *
                    <?php echo form_input('gcprice', '', 'class="form-control" id="gcprice"'); ?>
                </div>
                <div class="form-group">
                    <?=lang("customer", "gccustomer");?>
                    <?php echo form_input('gccustomer', '', 'class="form-control" id="gccustomer"'); ?>
                </div>
                <div class="form-group">
                    <?=lang("expiry_date", "gcexpiry");?>
                    <?php echo form_input('gcexpiry', $this->sma->hrsd(date("Y-m-d", strtotime("+2 year"))), 'class="form-control date" id="gcexpiry"'); ?>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" id="addGiftCard" class="btn btn-primary"><?=lang('sell_gift_card')?></button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade in" id="mModal" tabindex="-1" role="dialog" aria-labelledby="mModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true"><i
                            class="fa fa-2x">&times;</i></span><span class="sr-only"><?=lang('close');?></span></button>
                <h4 class="modal-title" id="mModalLabel"><?=lang('add_product_manually')?></h4>
            </div>
            <div class="modal-body" id="pr_popover_content">
                <form class="form-horizontal" role="form">
                    <div class="form-group">
                        <label for="mcode" class="col-sm-4 control-label"><?=lang('product_code')?> *</label>

                        <div class="col-sm-8">
                            <input type="text" class="form-control kb-text" id="mcode">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="mname" class="col-sm-4 control-label"><?=lang('product_name')?> *</label>

                        <div class="col-sm-8">
                            <input type="text" class="form-control kb-text" id="mname">
                        </div>
                    </div>
                    <?php if ($Settings->tax1) {
                        ?>
                        <div class="form-group">
                            <label for="mtax" class="col-sm-4 control-label"><?=lang('product_tax')?> *</label>

                            <div class="col-sm-8">
                                <?php
                                	$tr[""] = "";
                                	    foreach ($tax_rates as $tax) {
                                	        $tr[$tax->id] = $tax->name;
                                	    }
                                	    echo form_dropdown('mtax', $tr, "", 'id="mtax" class="form-control pos-input-tip" style="width:100%;"');
                                    ?>
                            </div>
                        </div>
                    <?php }
                    ?>
                    <div class="form-group">
                        <label for="mquantity" class="col-sm-4 control-label"><?=lang('quantity')?> *</label>

                        <div class="col-sm-8">
                            <input type="text" class="form-control kb-pad" id="mquantity">
                        </div>
                    </div>
                    <?php if ($Settings->product_discount && ($Owner || $Admin || $this->session->userdata('allow_discount'))) {?>
                        <div class="form-group">
                            <label for="mdiscount"
                                   class="col-sm-4 control-label"><?=lang('product_discount')?></label>

                            <div class="col-sm-8">
                                <input type="text" class="form-control kb-pad" id="mdiscount">
                            </div>
                        </div>
                    <?php }
                    ?>
                    <div class="form-group">
                        <label for="mprice" class="col-sm-4 control-label"><?=lang('unit_price')?> *</label>

                        <div class="col-sm-8">
                            <input type="text" class="form-control kb-pad" id="mprice">
                        </div>
                    </div>
                    <table class="table table-bordered table-striped">
                        <tr>
                            <th style="width:25%;"><?=lang('net_unit_price');?></th>
                            <th style="width:25%;"><span id="mnet_price"></span></th>
                            <th style="width:25%;"><?=lang('product_tax');?></th>
                            <th style="width:25%;"><span id="mpro_tax"></span></th>
                        </tr>
                    </table>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="addItemManually"><?=lang('submit')?></button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade in" id="sckModal" tabindex="-1" role="dialog" aria-labelledby="ModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">
                <i class="fa fa-2x">&times;</i></span><span class="sr-only"><?=lang('close');?></span>
                </button>
                <button type="button" class="btn btn-xs btn-default no-print pull-right" style="margin-right:15px;" onclick="window.print();">
                    <i class="fa fa-print"></i> <?= lang('print'); ?>
                </button>
                <h4 class="modal-title" id="mModalLabel"><?=lang('shortcut_keys')?></h4>
            </div>
            <div class="modal-body" id="pr_popover_content">
                <table class="table table-bordered table-striped table-condensed table-hover"
                       style="margin-bottom: 0px;">
                    <thead>
                    <tr>
                        <th><?=lang('shortcut_keys')?></th>
                        <th><?=lang('actions')?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td><?=$pos_settings->focus_add_item?></td>
                        <td><?=lang('focus_add_item')?></td>
                    </tr>
                    <tr>
                        <td><?=$pos_settings->add_manual_product?></td>
                        <td><?=lang('add_manual_product')?></td>
                    </tr>
                    <tr>
                        <td><?=$pos_settings->customer_selection?></td>
                        <td><?=lang('customer_selection')?></td>
                    </tr>
                    <tr>
                        <td><?=$pos_settings->add_customer?></td>
                        <td><?=lang('add_customer')?></td>
                    </tr>
                    <tr>
                        <td><?=$pos_settings->toggle_category_slider?></td>
                        <td><?=lang('toggle_category_slider')?></td>
                    </tr>
                    <tr>
                        <td><?=$pos_settings->toggle_subcategory_slider?></td>
                        <td><?=lang('toggle_subcategory_slider')?></td>
                    </tr>
                    <tr>
                        <td><?=$pos_settings->cancel_sale?></td>
                        <td><?=lang('cancel_sale')?></td>
                    </tr>
                    <tr>
                        <td><?=$pos_settings->suspend_sale?></td>
                        <td><?=lang('suspend_sale')?></td>
                    </tr>
                    <tr>
                        <td><?=$pos_settings->finalize_sale?></td>
                        <td><?=lang('finalize_sale')?></td>
                    </tr>
                    <tr>
                        <td><?=$pos_settings->today_sale?></td>
                        <td><?=lang('today_sale')?></td>
                    </tr>
                    <tr>
                        <td><?=$pos_settings->open_hold_bills?></td>
                        <td><?=lang('open_hold_bills')?></td>
                    </tr>
                    <tr>
                        <td><?=$pos_settings->close_register?></td>
                        <td><?=lang('close_register')?></td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade in" id="dsModal" tabindex="-1" role="dialog" aria-labelledby="dsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                    <i class="fa fa-2x">&times;</i>
                </button>
                <h4 class="modal-title" id="dsModalLabel"><?=lang('Giảm giá');?></h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <?=lang("order_discount", "order_discount_input");?>
                    <?php echo form_input('order_discount_input', '', 'class="form-control kb-pad" id="order_discount_input"'); ?>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" id="updateOrderDiscount" class="btn btn-primary"><?=lang('update')?></button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade in" id="sModal" tabindex="-1" role="dialog" aria-labelledby="sModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                    <i class="fa fa-2x">&times;</i>
                </button>
                <h4 class="modal-title" id="sModalLabel"><?=lang('shipping');?></h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <?=lang("shipping", "shipping_input");?>
                    <?php echo form_input('shipping_input', '', 'class="form-control kb-pad" id="shipping_input"'); ?>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" id="updateShipping" class="btn btn-primary"><?=lang('update')?></button>
            </div>
        </div>
    </div>
</div>
<div class="modal" id="cmModalDoCan" tabindex="-1" role="dialog" aria-labelledby="cmModalLabelgh" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">
                    <i class="fa fa-2x">&times;</i></span>
                    <span class="sr-only"><?=lang('close');?></span>
                </button>
                <h4 class="modal-title" id="cmModalLabelgh">Ghi độ cận khách hàng</h4>
            </div>
            <div class="modal-body" id="pr_popover_contentdocan">
               <div class="form-group-new-docan2">
                     <div class="input group">
                         <label>PD (Khoảng cách từ đồng tử)</label>
                         <input type="text" class="inputdocan" name="dppos" id="dppos"/>
                     </div>   
                    <table class="table table-bordered table-condensed table-hover table-striped">
                        <thead>
                            <tr>
                                <th></th>
                                <th>Cận</th>
                                <th>Viễn</th>
                                <th>Loạn</th>
                                <th>ADD (Người lớn tuổi)</th>
                                <th>AX (Trục mắt)</th>    

                            </tr>
                        </thead>
                        <tbody>
                            <tr>

                                <td style="width:100px;">Mắt phải</td>
                                <td><input class="inputdocan" type="text" id="canmppos" name="canmppos"></td>                                                   
                                <td><input class="inputdocan" type="text" id="vienmppos" name="vienmppos"></td>
                                <td><input class="inputdocan" type="text" id="loanmppos" name="loanmppos"></td>
                                <td><input class="inputdocan" type="text" id="addmppos" name="addmppos"></td>
                                <td><input class="inputdocan" type="text" id="tmmppos" name="tmmppos"></td>                                  
                            </tr>
                            <tr>

                                <td>Mắt trái</td>
                                <td><input class="inputdocan" type="text" id="canmtpos" name="canmtpos"></td>
                                
                                <td><input class="inputdocan" type="text" id="vienmtpos" name="vienmtpos"></td>
                                <td><input class="inputdocan" type="text" id="loanmtpos" name="loanmtpos"></td>
                                <td><input class="inputdocan" type="text" id="addmtpos" name="addmtpos"></td>
                                <td><input class="inputdocan" type="text" id="tmmtpos" name="tmmtpos"></td>                                   
                            </tr>

                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="editDoCan"><?=lang('submit')?></button>
            </div>
        </div>
    </div>
</div>
<style type="text/css">
    .tbl-docan tr th {font-weight: bold; color:#000; text-align:center;}

    .tbl-docan tr td{
        text-align:center;
    }
    button.btn.btn-small.btn-dn-fc {
        padding: 0px;
        display: flex;
        margin: 0 auto;
        margin-bottom: 5px;
        min-width: 50px;
    }
    table input.inputdocan {
        width: 100%;
    }
    a#docanpos {
        /* position: absolute; */
        /* right: -37px; */
        /* top: 0px; */
        color: #ffb752;
        background: #fff;
        padding: 9px 5px 4px 5px;
        margin-left: 2px;
    }

    div#ui {
        position: relative;
    }
</style>
<div class="modal" id="cmModalGhichu" tabindex="-1" role="dialog" aria-labelledby="cmModalLabelgh" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">
                    <i class="fa fa-2x">&times;</i></span>
                    <span class="sr-only"><?=lang('close');?></span>
                </button>
                <h4 class="modal-title" id="cmModalLabelgh">Ghi chú đơn hàng</h4>
            </div>
            <div class="modal-body" id="pr_popover_content2">
                <div class="form-group">
                    <?= lang('Ghi chú đơn hàng', 'sale_note'); ?>
                    <?= form_textarea('sale_note', '', 'class="form-control skip" id="sale_note" style="height:180px!important;"'); ?>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="editCommentGhichu"><?=lang('submit')?></button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade in" id="txModal" tabindex="-1" role="dialog" aria-labelledby="txModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i
                        class="fa fa-2x">&times;</i></button>
                <h4 class="modal-title" id="txModalLabel"><?=lang('Thuế');?></h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <?=lang("order_tax", "order_tax_input");?>
                        <?php
                        	$tr[""] = "";
                        	foreach ($tax_rates as $tax) {
                        	    $tr[$tax->id] = $tax->name;
                        	}
                        	echo form_dropdown('order_tax_input', $tr, "", 'id="order_tax_input" class="form-control pos-input-tip" style="width:100%;"');
                        ?>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" id="updateOrderTax" class="btn btn-primary"><?=lang('update')?></button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade in" id="susModal" tabindex="-1" role="dialog" aria-labelledby="susModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i
                        class="fa fa-2x">&times;</i></button>
                <h4 class="modal-title" id="susModalLabel"><?=lang('suspend_sale');?></h4>
            </div>
            <div class="modal-body">
                <p><?=lang('type_reference_note');?></p>

                <div class="form-group">
                    <?=lang("reference_note", "reference_note");?>
<?php echo form_input('reference_note', $reference_note, 'class="form-control kb-text" id="reference_note"'); ?>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" id="suspend_sale" class="btn btn-primary"><?=lang('submit')?></button>
            </div>
        </div>
    </div>
</div>
<div id="order_tbl"><span id="order_span"></span>
    <table id="order-table" class="prT table table-striped" style="margin-bottom:0;" width="100%"></table>
</div>
<div id="bill_tbl"><span id="bill_span"></span>
    <table id="bill-table" width="100%" class="prT table table-striped" style="margin-bottom:0;"></table>
    <table id="bill-total-table" class="prT table" style="margin-bottom:0;" width="100%"></table>
    <span id="bill_footer"></span>
</div>
<div class="modal fade in" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
     aria-hidden="true"></div>
<div class="modal fade in" id="myModal2" tabindex="-1" role="dialog" aria-labelledby="myModalLabel2"
     aria-hidden="true"></div>
<div id="modal-loading" style="display: none;">
    <div class="blackbg"></div>
    <div class="loader"></div>
</div>
	<?php echo form_close(); ?>
<?php unset($Settings->setting_id, $Settings->smtp_user, $Settings->smtp_pass, $Settings->smtp_port, $Settings->update, $Settings->reg_ver, $Settings->allow_reg, $Settings->default_email, $Settings->mmode, $Settings->timezone, $Settings->restrict_calendar, $Settings->restrict_user, $Settings->auto_reg, $Settings->reg_notification, $Settings->protocol, $Settings->mailpath, $Settings->smtp_crypto, $Settings->corn, $Settings->customer_group, $Settings->scodeweb_username, $Settings->purchase_code);?>
<script type="text/javascript">
var site = <?=json_encode(array('base_url' => base_url(), 'settings' => $Settings, 'dateFormats' => $dateFormats))?>, pos_settings = <?=json_encode($pos_settings);?>;
var lang = {
    unexpected_value: '<?=lang('unexpected_value');?>', 
    select_above: '<?=lang('select_above');?>', 
    r_u_sure: '<?=lang('r_u_sure');?>', 
    bill: '<?=lang('bill');?>', 
    order: '<?=lang('order');?>', 
    total: '<?=lang('total');?>',
    items: '<?=lang('items');?>',
    discount: '<?=lang('discount');?>',
    order_tax: '<?=lang('order_tax');?>',
    grand_total: '<?=lang('grand_total');?>',
    total_payable: '<?=lang('total_payable');?>',
    rounding: '<?=lang('rounding');?>',
    merchant_copy: '<?=lang('merchant_copy');?>'
};
</script>

<script type="text/javascript">
	

    var product_variant = 0, shipping = 0, p_page = 0, per_page = 0, tcp = "<?=$tcp?>", pro_limit = <?= $pos_settings->pro_limit; ?>,
        brand_id = 0, obrand_id = 0, cat_id = "<?=$pos_settings->default_category?>", ocat_id = "<?=$pos_settings->default_category?>", sub_cat_id = 0, osub_cat_id,
        count = 1, an = 1, DT = <?=$Settings->default_tax_rate?>,
        product_tax = 0, invoice_tax = 0, product_discount = 0, order_discount = 0, total_discount = 0, total = 0, total_paid = 0, grand_total = 0,
        KB = <?=$pos_settings->keyboard?>, tax_rates =<?php echo json_encode($tax_rates); ?>;
    var protect_delete = <?php if (!$Owner && !$Admin) {echo $pos_settings->pin_code ? '1' : '0';} else {echo '0';} ?>, billers = <?= json_encode($posbillers); ?>, biller = <?= json_encode($posbiller); ?>;
    var username = '<?=$this->session->userdata('username');?>', order_data = '', bill_data = '';

    
     function widthFunctions(e) {
        var wh = $(window).height();
         var w=$(window).width();
        
        $('.main-content, body, html').css("width", w);

        $('.main-content, body, html').css("min-width", w);


        var navbar=$('#navbar').height();    
        var top=$('#left-top').height();    
        var bottom=$('#left-bottom').height();    

        var l= wh-navbar-bottom-top;


        lth = $('#left-top').height(),
        lbh = $('#left-bottom').height();


        $('#item-list').css("height", wh - 79-15);
        $('#item-list').css("min-height", wh - 79-15);

        $('#left-middle').css("height", l);
        $('#left-middle').css("min-height", l);
        $('#product-list').css("height",l);
        $('#product-list').css("min-height",l);
    }
    $(window).bind("resize", widthFunctions);
	function set_tong_no_active_kh(){ 
		var active_cus_id=$("#poscustomer").val();			
		localStorage.removeItem('tong_no_kh'+active_cus_id);
		//ajax call 
	
		$.get("<?=site_url('pos/get_tong_no')?>/" + active_cus_id, function(data, status){			
			localStorage.setItem('tong_no_kh'+active_cus_id, data);		
		});
	}
    $(document).ready(function () {
        
        widthFunctions();

        $("#pr_popover_contentdocan .inputdocan").change(function(){
            $val=$(this).val();
            
            if ($val.indexOf('.') > -1) {

            }else if ($val!=''){
                if ($val.indexOf('+') > -1) {

                }else{
                    if ($(this).attr('name')=='canmppos'||$(this).attr('name')=='canmtpos'||$(this).attr('name')=='loanmppos'||$(this).attr('name')=='loanmtpos') {
                        if ($val<0) {
                            $val=abs($val);
                        }
                        $(this).val('-'+$val+'.00');
                    }else if ($(this).attr('name')=='vienmppos'||$(this).attr('name')=='vienmtpos'||$(this).attr('name')=='addmtpos'||$(this).attr('name')=='addmppos') {
                        if ($val<0) {
                            $val=abs($val);
                        }
                        $(this).val('+'+$val+'.00');
                    }else if ($(this).attr('name')!='tmmtpos'&&$(this).attr('name')!='dppos'&&$(this).attr('name')!='tmmppos') {
                        $(this).val($val+'.00');
                    }
                }
                
               
            } 
        });
		
		
        $('#view-customer').click(function(){
            $('#myModal').modal({remote: site.base_url + 'customers/view/' + $("input[name=customer]").val()});
            $('#myModal').modal('show');
        });
        $('textarea').keydown(function (e) {
            if (e.which == 13) {
               var s = $(this).val();
               $(this).val(s+'\n').focus();
               e.preventDefault();
               return false;
            }
        });
        <?php if ($sid) {?>
        localStorage.setItem('positems', JSON.stringify(<?=$items;?>));
        <?php }
        ?>
<?php if ($this->session->userdata('remove_posls')) {?>
        if (localStorage.getItem('positems')) {
            localStorage.removeItem('positems');
        }
        if (localStorage.getItem('posdiscount')) {
            localStorage.removeItem('posdiscount');
        }
        if (localStorage.getItem('postax2')) {
            localStorage.removeItem('postax2');
        }
        if (localStorage.getItem('posshipping')) {
            localStorage.removeItem('posshipping');
        }
        if (localStorage.getItem('poswarehouse')) {
            localStorage.removeItem('poswarehouse');
        }
        if (localStorage.getItem('posnote')) {
            localStorage.removeItem('posnote');
        }
        if (localStorage.getItem('poscustomer')) {
            localStorage.removeItem('poscustomer');
        }
        if (localStorage.getItem('posbiller')) {
            localStorage.removeItem('posbiller');
        }
        if (localStorage.getItem('poscurrency')) {
            localStorage.removeItem('poscurrency');
        }
        if (localStorage.getItem('posnote')) {
            localStorage.removeItem('posnote');
        }
        if (localStorage.getItem('staffnote')) {
            localStorage.removeItem('staffnote');
        }
        <?php $this->sma->unset_data('remove_posls');}
        ?>
        widthFunctions();
        <?php if ($suspend_sale) {?>
        localStorage.setItem('postax2', '<?=$suspend_sale->order_tax_id;?>');
        localStorage.setItem('posdiscount', '<?=$suspend_sale->order_discount_id;?>');
        localStorage.setItem('poswarehouse', '<?=$suspend_sale->warehouse_id;?>');
        localStorage.setItem('poscustomer', '<?=$suspend_sale->customer_id;?>');
        localStorage.setItem('posbiller', '<?=$suspend_sale->biller_id;?>');
        localStorage.setItem('posshipping', '<?=$suspend_sale->shipping;?>');
        <?php }

	if ($this->input->get('customer')) {	
	?>	
        if (!localStorage.getItem('positems')) {
            localStorage.setItem('poscustomer', <?=$this->input->get('customer');?>);
        } else if (!localStorage.getItem('poscustomer')) {
            localStorage.setItem('poscustomer', <?=$customer->id;?>);
        }
        <?php } else {?>
        if (!localStorage.getItem('poscustomer')) {
            localStorage.setItem('poscustomer', <?=$customer->id;?>);
        }
        <?php }
		
        ?>
		
        if (!localStorage.getItem('postax2')) {
            localStorage.setItem('postax2', <?=$Settings->default_tax_rate2;?>);
        }
        $('.select').select2({minimumResultsForSearch: 7});
        // var customers = [{
        //     id: <?=$customer->id;?>,
        //     text: '<?=$customer->company == '-' ? $customer->name : $customer->company;?>'
        // }];
        $('#poscustomer').val(localStorage.getItem('poscustomer')).select2({
            minimumInputLength: 1,
            data: [],
            initSelection: function (element, callback) {
                $.ajax({
                    type: "get", async: false,
                    url: "<?=site_url('customers/getCustomer')?>/" + $(element).val(),
                    dataType: "json",
                    success: function (data) {
                        callback(data[0]);
                    }
                });
                $.ajax({
                    type: "get", async: false,
                    url: "<?=site_url('customers/get_customer_details')?>/" + $(element).val(),
                    dataType: "json",
                    success: function (data) {
                        console.log(data);
                        $("#diemtichluy_total").val(data.award_points);
                        $("#tiencoc_total").val(data.deposit_amount);
                    }
                });
            },
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
        if (KB) {
            display_keyboards();

            var result = false, sct = '';
            $('#poscustomer').on('select2-opening', function () {
                sct = '';
                $('.select2-input').addClass('kb-text');
                display_keyboards();
                $('.select2-input').bind('change.keyboard', function (e, keyboard, el) {
                    if (el && el.value != '' && el.value.length > 0 && sct != el.value) {
                        sct = el.value;
                    }
                    if(!el && sct.length > 0) {
                        $('.select2-input').addClass('select2-active');
                        setTimeout(function() {
                            $.ajax({
                                type: "get",
                                async: false,
                                url: "<?=site_url('customers/suggestions')?>/?term=" + sct,
                                dataType: "json",
                                success: function (res) {
                                    if (res.results != null) {
                                        $('#poscustomer').select2({data: res}).select2('open');
                                        $('.select2-input').removeClass('select2-active');
                                    } else {
                                        // bootbox.alert('no_match_found');
                                        $('#poscustomer').select2('close');
                                        $('#test').click();
                                    }
                                }
                            });
                        }, 500);
                    }
                });
            });

            $('#poscustomer').on('select2-close', function () {
                $('.select2-input').removeClass('kb-text');
                $('#test').click();
                $('select, .select').select2('destroy');
                $('select, .select').select2({minimumResultsForSearch: 7});
            });
            $(document).bind('click', '#test', function () {
                var kb = $('#test').keyboard().getkeyboard();
                kb.close();
                //kb.destroy();
                $('#add-item').focus();
            });

        }

        $(document).on('change', '#posbiller', function () {
            var sb = $(this).val();
            $.each(billers, function () {
                if(this.id == sb) {
                    biller = this;
                }
            });
            $('#biller').val(sb);
        });

        <?php for ($i = 1; $i <= 5; $i++) {?>
        $('#paymentModal').on('change', '#amount_<?=$i?>', function (e) {
            $('#amount_val_<?=$i?>').val($(this).val());
        });
        $('#paymentModal').on('blur', '#amount_<?=$i?>', function (e) {
            $('#amount_val_<?=$i?>').val($(this).val());
        });
        $('#paymentModal').on('change', '#paid_by_<?=$i?>', function (e) {
            console.log($(this).val());
            console.log($(this).find('option:selected').val());
            $('#paid_by_val_<?=$i?>').val($(this).val());
        });
        $('#paymentModal').on('change', '#pcc_no_<?=$i?>', function (e) {
            $('#cc_no_val_<?=$i?>').val($(this).val());
        });
        $('#paymentModal').on('change', '#pcc_holder_<?=$i?>', function (e) {
            $('#cc_holder_val_<?=$i?>').val($(this).val());
        });
        $('#paymentModal').on('change', '#gift_card_no_<?=$i?>', function (e) {
            $('#paying_gift_card_no_val_<?=$i?>').val($(this).val());
        });
        $('#paymentModal').on('change', '#pcc_month_<?=$i?>', function (e) {
            $('#cc_month_val_<?=$i?>').val($(this).val());
        });
        $('#paymentModal').on('change', '#pcc_year_<?=$i?>', function (e) {
            $('#cc_year_val_<?=$i?>').val($(this).val());
        });
        $('#paymentModal').on('change', '#pcc_type_<?=$i?>', function (e) {
            $('#cc_type_val_<?=$i?>').val($(this).val());
        });
        $('#paymentModal').on('change', '#pcc_cvv2_<?=$i?>', function (e) {
            $('#cc_cvv2_val_<?=$i?>').val($(this).val());
        });
        $('#paymentModal').on('change', '#cheque_no_<?=$i?>', function (e) {
            $('#cheque_no_val_<?=$i?>').val($(this).val());
        });
        $('#paymentModal').on('change', '#payment_note_<?=$i?>', function (e) {
            $('#payment_note_val_<?=$i?>').val($(this).val());
        });
        <?php }
        ?>

        $('#payment').click(function () {
            <?php if ($sid) {?>
            suspend = $('<span></span>');
            suspend.html('<input type="hidden" name="delete_id" value="<?php echo $sid; ?>" />');
            suspend.appendTo("#hidesuspend");
            <?php }
            ?>
            var twt = formatDecimal((total + invoice_tax) - order_discount + shipping);
            if (count == 1) {
                bootbox.alert('<?=lang('x_total');?>');
                return false;
            }
            gtotal = formatDecimal(twt);
            <?php if ($pos_settings->rounding) {?>
            round_total = roundNumber(gtotal, <?=$pos_settings->rounding?>);
            var rounding = formatDecimal(0 - (gtotal - round_total));
            $('#twt').text(formatMoney(round_total) + ' (' + formatMoney(rounding) + ')');
            $('#quick-payable').text(round_total);
            <?php } else {?>
            $('#twt').text(formatMoney(gtotal));
            $('#quick-payable').text(gtotal);
            <?php }
            ?>
            $('#item_count').text(count - 1);
            $('#paymentModal').appendTo("body").modal('show');
            $('#amount_1').focus();

            //hien thi tong diem tich luy
            $("#showKhachHang").html($("#s2id_poscustomer").text());
            $("#showDiemTichLuy").html("<b>"+$("#diemtichluy_total").val()+"</b> điểm tích lũy");
            $("#showTiencoc").html("<b>"+formatMoney($("#tiencoc_total").val())+"</b> đ");
        });
        $('#paymentModal').on('show.bs.modal', function(e) {
            $('#submit-sale').text('<?=lang('submit');?>').attr('disabled', false);
        });
        $('#paymentModal').on('shown.bs.modal', function(e) {
            $('#amount_1').focus();
        });
        var pi = 'amount_1', pa = 2; 
        $(document).on('click', '.quick-cash', function () {
            var $quick_cash = $(this);
            var amt = $quick_cash.contents().filter(function () {
                return this.nodeType == 3;
            }).text();
            var th = ',';
            var $pi = $('#' + pi);
            amt = formatDecimal(amt.split(th).join("")) * 1 + $pi.val() * 1;
            $pi.val(formatDecimal(amt)).focus();
            var note_count = $quick_cash.find('span');
            if (note_count.length == 0) {
                $quick_cash.append('<span class="badge">1</span>');
            } else {
                note_count.text(parseInt(note_count.text()) + 1);
            }
        });
        
        $('.addButtonDoiDiem').click(function () {
            $data_no=$(this).attr('data-no');
            $ca_points=parseInt($("#diem_tichluy_no_"+$data_no).val());
            $amount=parseFloat($("#diem_tichluy_tien_"+$data_no).val());
            $tongdiem=parseInt($("#diemtichluy_total").val());
            if ($tongdiem==0) {
                alert('Chưa có điểm tích lũy');
                $("#paid_by_"+$data_no).val('cash');
                    $("#paid_by_"+$data_no).trigger('change');
                return;
            }
            if ($ca_points>$tongdiem)
            {
                $ca_points=$tongdiem;
                $("#diem_tichluy_no_"+$data_no).val($tongdiem);
            }
            if ($amount==0) {
                alert('Vui lòng nhập số tiền cần đổi điểm');
                return;
            }
            //tien hanh tao the giam gia auto ajax
            //ca_points, amount, customer_id , card_no();
            $card_no=generateCardNo();
            $customer_id=$("#poscustomer").val();

            $.ajax({
                type: 'POST',
                url: site.base_url+'sales/add_gift_card_by_ajax',
                dataType: "json",
                data: { card_no:$card_no,customer_id:$customer_id,amount:$amount,ca_points:$ca_points,token:$("input[name='token']").val()},
                success: function (data) {                    
                    if (data=='OK') {
                        $("#paid_by_"+$data_no).val('gift_card');
                        $("#paid_by_"+$data_no).trigger('change');
                        $("#gift_card_no_"+$data_no).val($card_no);
                        $("#payment_note_"+$data_no).val("Đổi "+$ca_points+" điểm tích lũy");
                        $("#gift_card_no_"+$data_no).trigger('change');
                    }
                }
            });

        });
        $(document).on('click', '#clear-cash-notes', function () {
            $('.quick-cash').find('.badge').remove();
            $('#' + pi).val('0').focus();
        });

        $(document).on('change', '.gift_card_no', function () {
            var cn = $(this).val() ? $(this).val() : '';
            var payid = $(this).attr('id'),
                id = payid.substr(payid.length - 1);
            if (cn != '') {
                $.ajax({
                    type: "get", async: false,
                    url: site.base_url + "sales/validate_gift_card/" + cn,
                    dataType: "json",
                    success: function (data) {
                        if (data === false) {
                            $('#gift_card_no_' + id).parent('.form-group').addClass('has-error');
                            bootbox.alert('<?=lang('incorrect_gift_card')?>');
                        } else if (data.customer_id !== null && data.customer_id !== $('#poscustomer').val()) {
                            $('#gift_card_no_' + id).parent('.form-group').addClass('has-error');
                            bootbox.alert('<?=lang('gift_card_not_for_customer')?>');
                        } else {
                            $('#gc_details_' + id).html('<small>Card No: ' + data.card_no + '<br>Value: ' + data.value + ' - Balance: ' + data.balance + '</small>');
                            $('#gift_card_no_' + id).parent('.form-group').removeClass('has-error');
                            //calculateTotals();
                            $('#amount_' + id).val(gtotal >= data.balance ? data.balance : gtotal).focus();
                        }
                    }
                });
            }
        });

        $(document).on('click', '.addButton', function () {
            if (pa <= 5) {
                $('#paid_by_1, #pcc_type_1').select2('destroy');
                var phtml = $('#payments').html(),
                    update_html = phtml.replace(/_1/g, '_' + pa);
                pi = 'amount_' + pa;
                $('#multi-payment').append('<button type="button" class="close close-payment" style="margin: -10px 0px 0 0;"><i class="fa fa-2x">&times;</i></button>' + update_html);
                $('#paid_by_1, #pcc_type_1, #paid_by_' + pa + ', #pcc_type_' + pa).select2({minimumResultsForSearch: 7});
                read_card();
                pa++;
            } else {
                bootbox.alert('<?=lang('max_reached')?>');
                return false;
            }
            if (KB) {
                display_keyboards();    
            }
            
            $('#paymentModal').css('overflow-y', 'scroll');
        });

        $(document).on('click', '.close-payment', function () {
            $(this).next().remove();
            $(this).remove();
            pa--;
        });

        $(document).on('focus', '.amount', function () {
            pi = $(this).attr('id');
            calculateTotals();
        }).on('blur', '.amount', function () {
            calculateTotals();
        });

        function calculateTotals() {
            var total_paying = 0;
            var ia = $(".amount");
            $.each(ia, function (i) {
                var this_amount = formatCNum($(this).val() ? $(this).val() : 0);
                total_paying += parseFloat(this_amount);
            });
            $('#total_paying').text(formatMoney(total_paying));
            <?php if ($pos_settings->rounding) {?>
            $('#balance').text(formatMoney(total_paying - round_total));
            $('#balance_' + pi).val(formatDecimal(total_paying - round_total));
            total_paid = total_paying;
            grand_total = round_total;
            <?php } else {?>
            $('#balance').text(formatMoney(total_paying - gtotal));
            $('#balance_' + pi).val(formatDecimal(total_paying - gtotal));
            total_paid = total_paying;
            grand_total = gtotal;
            <?php }
            ?>
        }

        $("#add_item").autocomplete({
            source: function (request, response) {
                if (!$('#poscustomer').val()) {
                    $('#add_item').val('').removeClass('ui-autocomplete-loading');
                    bootbox.alert('<?=lang('select_above');?>');
                    //response('');
                    $('#add_item').focus();
                    return false;
                }
                $.ajax({
                    type: 'get',
                    url: '<?=site_url('sales/suggestions');?>',
                    dataType: "json",
                    data: {
                        term: request.term,
                        warehouse_id: $("#poswarehouse").val(),
                        customer_id: $("#poscustomer").val()
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
                if ($(this).val().length >= 5 && ui.content[0].id == 0) {
                    bootbox.alert('<?=lang('no_match_found')?>', function () {
                        $('#add_item').focus();
                    });
                    $(this).val('');
                }
                else if (ui.content.length == 1 && ui.content[0].id != 0) {
					if($(this).val().length >= 5){
						ui.item = ui.content[0]; /*lhson code 16*/
						$(this).data('ui-autocomplete')._trigger('select', 'autocompleteselect', ui);
						$(this).autocomplete('close');
					}
                }
                else if (ui.content.length == 1 && ui.content[0].id == 0) {
                    // bootbox.alert('<?=lang('no_match_found')?>', function () {
                        // $('#add_item').focus();
                    // });
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
                    bootbox.alert('<?=lang('no_match_found')?>');
                }
            }
        });

        <?php if ($pos_settings->tooltips) {echo '$(".pos-tip").tooltip();';}
        ?>
        // $('#posTable').stickyTableHeaders({fixedOffset: $('#product-list')});
        $('#posTable').stickyTableHeaders({scrollableArea: $('#product-list')});
        $('#product-list, #category-list, #subcategory-list').perfectScrollbar({suppressScrollX: true});
        
        //$('select, .select').select2({minimumResultsForSearch: 7});

        $(document).on('click', '.product', function (e) {
            $('#modal-loading').show();
            code = $(this).val(),
                wh = $('#poswarehouse').val(),
                cu = $('#poscustomer').val();
            $.ajax({
                type: "get",
                url: "<?=site_url('pos/getProductDataByCode')?>",
                data: {code: code, warehouse_id: wh, customer_id: cu},
                dataType: "json",
                success: function (data) {
					
                    e.preventDefault();
                    if (data !== null) {
						
                        add_invoice_item(data);
                        $('#modal-loading').hide();
                    } else {
                        bootbox.alert('<?=lang('no_match_found')?>');
                        $('#modal-loading').hide();
                    }
                }
            });
        });

        $(document).on('click', '.category', function () {
            if (cat_id != $(this).val()) {
                $('#open-category').click();
                $('#modal-loading').show();
                cat_id = $(this).val();
                $.ajax({
                    type: "get",
                    url: "<?=site_url('pos/ajaxcategorydata');?>",
                    data: {category_id: cat_id},
                    dataType: "json",
                    success: function (data) {
                        $('#item-list').empty();
                        var newPrs = $('<div></div>');
                        newPrs.html(data.products);
                        newPrs.appendTo("#item-list");
                        $('#subcategory-list').empty();
                        var newScs = $('<div></div>');
                        newScs.html(data.subcategories);
                        newScs.appendTo("#subcategory-list");
                        tcp = data.tcp;
                        nav_pointer();
                    }
                }).done(function () {
                    p_page = 'n';
                    $('#category-' + cat_id).addClass('active');
                    $('#category-' + ocat_id).removeClass('active');
                    ocat_id = cat_id;
                    $('#modal-loading').hide();
                    nav_pointer();
                });
            }
        });
        $('#category-' + cat_id).addClass('active');

        $(document).on('click', '.brand', function () {
            if (brand_id != $(this).val()) {
                $('#open-brands').click();
                $('#modal-loading').show();
                brand_id = $(this).val();
                $.ajax({
                    type: "get",
                    url: "<?=site_url('pos/ajaxbranddata');?>",
                    data: {brand_id: brand_id},
                    dataType: "json",
                    success: function (data) {
                        $('#item-list').empty();
                        var newPrs = $('<div></div>');
                        newPrs.html(data.products);
                        newPrs.appendTo("#item-list");
                        tcp = data.tcp;
                        nav_pointer();
                    }
                }).done(function () {
                    p_page = 'n';
                    $('#brand-' + brand_id).addClass('active');
                    $('#brand-' + obrand_id).removeClass('active');
                    obrand_id = brand_id;
                    $('#category-' + cat_id).removeClass('active');
                    $('#subcategory-' + sub_cat_id).removeClass('active');
                    cat_id = 0; sub_cat_id = 0;
                    $('#modal-loading').hide();
                    nav_pointer();
                });
            }
        });

        $(document).on('click', '.subcategory', function () {
            if (sub_cat_id != $(this).val()) {
                $('#open-subcategory').click();
                $('#modal-loading').show();
                sub_cat_id = $(this).val();
                wh = $('#poswarehouse').val();
                $.ajax({
                    type: "get",
                    url: "<?=site_url('pos/ajaxproducts');?>",
                    data: {category_id: cat_id, subcategory_id: sub_cat_id, per_page: p_page,warehouse_id:wh},
                    dataType: "html",
                    success: function (data) {
                        $('#item-list').empty();
                        var newPrs = $('<div></div>');
                        newPrs.html(data);
                        newPrs.appendTo("#item-list");
                    }
                }).done(function () {
                    p_page = 'n';
                    $('#subcategory-' + sub_cat_id).addClass('active');
                    $('#subcategory-' + osub_cat_id).removeClass('active');
                    $('#modal-loading').hide();
                });
            }
        });
        $("#ghichuup").click(function(e) {
            e.preventDefault();
            $('#cmModalGhichu').modal();
        });
        $("#editCommentGhichu").click(function(e) {
            localStorage.setItem('posnote', $("#sale_note").val());     
            e.preventDefault();
            $('#cmModalGhichu').modal('hide');
        });
        $("#docanpos").click(function(e) {
            e.preventDefault();
            $('#cmModalDoCan').modal();
        });
        $("#editDoCan").click(function(e) {     
            localStorage.setItem('dppos', $("#dppos").val());            
            localStorage.setItem('canmppos', $("#canmppos").val());     
            localStorage.setItem('vienmppos', $("#vienmppos").val());     
            localStorage.setItem('loanmppos', $("#loanmppos").val());     
            localStorage.setItem('addmppos', $("#addmppos").val());     
            localStorage.setItem('tmmppos', $("#tmmppos").val());     
            localStorage.setItem('canmtpos', $("#canmtpos").val());  
            localStorage.setItem('vienmtpos', $("#vienmtpos").val());        
            localStorage.setItem('loanmtpos', $("#loanmtpos").val());     
            localStorage.setItem('addmtpos', $("#addmtpos").val());     
            localStorage.setItem('tmmtpos', $("#tmmtpos").val());     
            console.log('GHI DO CAN');

            /*hidden pos*/
            if (dppos = localStorage.getItem('dppos')) {
                $('#dpposhide').val(dppos);
            }
            if (canmppos = localStorage.getItem('canmppos')) {
                $('#canmpposhide').val(canmppos);
            }
            if (vienmppos = localStorage.getItem('vienmppos')) {
                $('#vienmpposhide').val(vienmppos);
            }
            if (loanmppos = localStorage.getItem('loanmppos')) {
                $('#loanmpposhide').val(loanmppos);
            }
            if (addmppos = localStorage.getItem('addmppos')) {
                $('#addmpposhide').val(addmppos);
            }
            if (tmmppos = localStorage.getItem('tmmppos')) {
                $('#tmmpposhide').val(tmmppos);
            }
            if (canmtpos = localStorage.getItem('canmtpos')) {
                $('#canmtposhide').val(canmtpos);
            }
            if (vienmtpos = localStorage.getItem('vienmtpos')) {
                $('#vienmtposhide').val(vienmtpos);
            }
            if (loanmtpos = localStorage.getItem('loanmtpos')) {
                $('#loanmtposhide').val(loanmtpos);
            }
            if (addmtpos = localStorage.getItem('addmtpos')) {
                $('#addmtposhide').val(addmtpos);
            }
            if (tmmtpos = localStorage.getItem('tmmtpos')) {
                $('#tmmtposhide').val(tmmtpos);
            }

            e.preventDefault();
            $('#cmModalDoCan').modal('hide');
        });
        $('#next').click(function () {
            if (p_page == 'n') {
                p_page = 0
            }
            p_page = p_page + pro_limit;
            if (tcp >= pro_limit && p_page < tcp) {
                $('#modal-loading').show();
                wh = $('#poswarehouse').val();
                $.ajax({
                    type: "get",
                    url: "<?=site_url('pos/ajaxproducts');?>",
                    data: {category_id: cat_id, subcategory_id: sub_cat_id, per_page: p_page,warehouse_id:wh},
                    dataType: "html",
                    success: function (data) {
                        $('#item-list').empty();
                        var newPrs = $('<div></div>');
                        newPrs.html(data);
                        newPrs.appendTo("#item-list");
                        nav_pointer();
                    }
                }).done(function () {
                    $('#modal-loading').hide();
                });
            } else {
                p_page = p_page - pro_limit;
            }
        });

        $('#previous').click(function () {
            if (p_page == 'n') {
                p_page = 0;
            }
            if (p_page != 0) {
                $('#modal-loading').show();
                p_page = p_page - pro_limit;
                if (p_page == 0) {
                    p_page = 'n'
                }
                wh = $('#poswarehouse').val();
                $.ajax({
                    type: "get",
                    url: "<?=site_url('pos/ajaxproducts');?>",
                    data: {category_id: cat_id, subcategory_id: sub_cat_id, per_page: p_page,warehouse_id:wh},
                    dataType: "html",
                    success: function (data) {
                        $('#item-list').empty();
                        var newPrs = $('<div></div>');
                        newPrs.html(data);
                        newPrs.appendTo("#item-list");
                        nav_pointer();
                    }

                }).done(function () {
                    $('#modal-loading').hide();
                });
            }
        });

        $(document).on('change', '.paid_by', function () {
            var p_val = $(this).val(),
                id = $(this).attr('id'),
                pa_no = id.substr(id.length - 1);
            $('#rpaidby').val(p_val);


            $('.diem_tichluy_no_' + pa_no).hide();
            if (p_val == 'cash' || p_val == 'other') {
                $('.pcheque_' + pa_no).hide();
                $('.pcc_' + pa_no).hide();
                $('.pcash_' + pa_no).show();
                $('#payment_note_' + pa_no).focus();
            } else if (p_val == 'CC' || p_val == 'stripe' || p_val == 'ppp' || p_val == 'authorize') {
                $('.pcheque_' + pa_no).hide();
                $('.pcash_' + pa_no).hide();
                $('.pcc_' + pa_no).show();
                $('#swipe_' + pa_no).focus();
            } else if (p_val == 'Cheque') {
                $('.pcc_' + pa_no).hide();
                $('.pcash_' + pa_no).hide();
                $('.pcheque_' + pa_no).show();
                $('#cheque_no_' + pa_no).focus();
            } else {
                $('.pcheque_' + pa_no).hide();
                $('.pcc_' + pa_no).hide();
                $('.pcash_' + pa_no).hide();
            }
            if (p_val == 'gift_card') {
                $('.gc_' + pa_no).show();
                $('.ngc_' + pa_no).hide();
                $('#gift_card_no_' + pa_no).focus();
                $('.diem_tichluy_no_' + pa_no).hide();
            } else {
                $('.ngc_' + pa_no).show();
                $('.gc_' + pa_no).hide();
                $('#gc_details_' + pa_no).html('');
                 $('.diem_tichluy_no_' + pa_no).hide();
            }
            if (p_val == 'gift_card_point') {
                $('.diem_tichluy_no_' + pa_no).show();
                $('.ngc_' + pa_no).hide();
                $('.gc_' + pa_no).hide();
                $('#diem_tichluy_no_' + pa_no).focus();
                
                $('.diem_tichluy_no_' + pa_no+" .addButtonDoiDiem").attr('data-no',pa_no);
            }
            if (p_val == 'deposit') {
                $('.diem_tichluy_no_' + pa_no).hide();
                $('.ngc_' + pa_no).hide();
                $('.gc_' + pa_no).hide();
                $('#amount_' + pa_no).focus();   

                $tiencoc=formatDecimal($('#tiencoc_total').val());   

                if ($tiencoc>grand_total) {
                    $tiencoc=grand_total;
                }
                $('#amount_' + pa_no).val($tiencoc); 
                $('#amount_val_'+pa_no).val($tiencoc);
                calculateTotals();
            }
        });

        $(document).on('click', '#submit-sale', function () {
            if (total_paid == 0 || total_paid < grand_total) {
                bootbox.confirm("<?=lang('paid_l_t_payable');?>", function (res) {
                    if (res == true) {
                        $('#pos_note').val(localStorage.getItem('posnote'));
                        $('#staff_note').val(localStorage.getItem('staffnote'));
                        $('#submit-sale').text('<?=lang('loading');?>').attr('disabled', true);
                        $('#pos-sale-form')[0].submit();
                    }
                });
                return false;
            } else {
                $('#pos_note').val(localStorage.getItem('posnote'));
                $('#staff_note').val(localStorage.getItem('staffnote'));
                $(this).text('<?=lang('loading');?>').attr('disabled', true);
                $('#pos-sale-form')[0].submit();
            }
        });
        $('#suspend').click(function () {
            if (count <= 1) {
                bootbox.alert('<?=lang('x_suspend');?>');
                return false;
            } else {
                $('#susModal').modal();
            }
        });
        $('#suspend_sale').click(function () {
            ref = $('#reference_note').val();
            if (!ref || ref == '') {
                bootbox.alert('<?=lang('type_reference_note');?>');
                return false;
            } else {
                suspend = $('<span></span>');
                <?php if ($sid) {?>
                suspend.html('<input type="hidden" name="delete_id" value="<?php echo $sid; ?>" /><input type="hidden" name="suspend" value="yes" /><input type="hidden" name="suspend_note" value="' + ref + '" />');
                <?php } else {?>
                suspend.html('<input type="hidden" name="suspend" value="yes" /><input type="hidden" name="suspend_note" value="' + ref + '" />');
                <?php }
                ?>
                suspend.appendTo("#hidesuspend");
                $('#total_items').val(count - 1);
                $('#pos-sale-form')[0].submit();

            }
        });
		
		set_tong_no_active_kh();
		
    });
 
    $(document).ready(function () {
        $('#print_order').click(function () {
			set_tong_no_active_kh();
            if (count == 1) {
                bootbox.alert('<?=lang('x_total');?>');
                return false;
            }
            <?php if ($pos_settings->remote_printing != 1) { ?>
                printOrder();
            <?php } else { ?>
                Popup($('#order_tbl').html());
            <?php } ?>
        });
        $('#print_bill').click(function () {
			set_tong_no_active_kh();
            if (count == 1) {
                bootbox.alert('<?=lang('x_total');?>');
                return false;
            }
            <?php if ($pos_settings->remote_printing != 1) { ?>
                printBill();
            <?php } else { ?>
                Popup($('#bill_tbl').html());
            <?php } ?>
        });
    });

    $(function () {
        $(".alert").effect("shake");
        setTimeout(function () {
            $(".alert").hide('blind', {}, 500)
        }, 15000);
        <?php if ($pos_settings->display_time) {?>
        var now = new moment();
        $('#display_time').text(now.format((site.dateFormats.js_sdate).toUpperCase() + " HH:mm"));
        setInterval(function () {
            var now = new moment();
            $('#display_time').text(now.format((site.dateFormats.js_sdate).toUpperCase() + " HH:mm"));
        }, 1000);
        <?php }
        ?>
    });
    <?php if ($pos_settings->remote_printing == 1) { ?>
    function Popup(data) {
        var mywindow = window.open('', 'sma_pos_print', 'height=500,width=300');
        mywindow.document.write('<html><head><title>Print</title>');
        mywindow.document.write('<link rel="stylesheet" href="<?=$assets?>styles/helpers/bootstrap.min.css" type="text/css" />');
        mywindow.document.write('</head><body >');
        mywindow.document.write(data);
        mywindow.document.write('</body></html>');
        mywindow.print();
        mywindow.close();
        return true;
    }
    <?php }
    ?>
    function loadProductAjax() {

        wh = $('#poswarehouse').val();
        $.ajax({
            type: "get",
            url: "<?=site_url('pos/ajaxproducts');?>",
            data: {category_id: '', subcategory_id: '', per_page: 'n',warehouse_id:wh},
            dataType: "html",
            success: function (data) {
                $('#item-list').empty();
                var newPrs = $('<div></div>');
                newPrs.html(data);
                newPrs.appendTo("#item-list");
            }
        }).done(function () {
            p_page = 'n';
        });;
    }
</script>
<?php
	$s2_lang_file = read_file('./assets/config_dumps/s2_lang.js');
	foreach (lang('select2_lang') as $s2_key => $s2_line) {
	    $s2_data[$s2_key] = str_replace(array('{', '}'), array('"+', '+"'), $s2_line);
	}
	$s2_file_date = $this->parser->parse_string($s2_lang_file, $s2_data, true);
?>
<script type="text/javascript" src="<?=$assets?>js/bootstrap.min.js"></script>
<script type="text/javascript" src="<?=$assets?>js/jquery-ui.min.js"></script>
<script type="text/javascript" src="<?=$assets?>js/perfect-scrollbar.min.js"></script>
<script type="text/javascript" src="<?=$assets?>js/select2.min.js"></script>
<script type="text/javascript" src="<?=$assets?>js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="<?=$assets?>js/custom.js"></script>
<script type="text/javascript" src="<?=$assets?>js/jquery.calculator.min.js"></script>
<script type="text/javascript" src="<?=$assets?>js/bootstrapValidator.min.js"></script>
<script type="text/javascript" src="<?=$assets?>pos/js/plugins.min.js"></script>
<script type="text/javascript" src="<?=$assets?>pos/js/parse-track-data.js"></script>
<script type="text/javascript" src="<?=$assets?>pos/js/pos.ajax.js"></script>
<?php
if ( ! $pos_settings->remote_printing) {
    ?>
    <script type="text/javascript">
        var order_printers = <?= json_encode($order_printers); ?>;
        function printOrder() {
            $.each(order_printers, function() {
                var socket_data = { 'printer': this, 
                'logo': (biller && biller.logo ? biller.logo : ''), 
                'text': order_data };
                $.get('<?= site_url('pos/p/order'); ?>', {data: JSON.stringify(socket_data)});
            });
            return false;
        }

        function printBill() {
            var socket_data = {
                'printer': <?= json_encode($printer); ?>,
                'logo': (biller && biller.logo ? biller.logo : ''),
                'text': bill_data
            };
            $.get('<?= site_url('pos/p'); ?>', {data: JSON.stringify(socket_data)});
            return false;
        }
    </script>
    <?php
} elseif ($pos_settings->remote_printing == 2) {
    ?>
    <script src="<?= $assets ?>js/socket.io.min.js" type="text/javascript"></script>
    <script type="text/javascript">
        socket = io.connect('http://localhost:6440', {'reconnection': false});

        function printBill() {
            if (socket.connected) {
                var socket_data = {'printer': <?= json_encode($printer); ?>, 'text': bill_data};
                socket.emit('print-now', socket_data);
                return false;
            } else {
                bootbox.alert('<?= lang('pos_print_error'); ?>');
                return false;
            }
        }

        var order_printers = <?= json_encode($order_printers); ?>;
        function printOrder() {
            if (socket.connected) {
                $.each(order_printers, function() {
                    var socket_data = {'printer': this, 'text': order_data};
                    socket.emit('print-now', socket_data);
                });
                return false;
            } else {
                bootbox.alert('<?= lang('pos_print_error'); ?>');
                return false;
            }
        }
    </script>
    <?php

} elseif ($pos_settings->remote_printing == 3) {

    ?>
    <script type="text/javascript">
        try {
            socket = new WebSocket('ws://127.0.0.1:6441');
            socket.onopen = function () {
                console.log('Connected');
                return;
            };
            socket.onclose = function () {
                console.log('Not Connected');
                return;
            };
        } catch (e) {
            console.log(e);
        }

        var order_printers = <?= json_encode($order_printers); ?>;
        function printOrder() {
            if (socket.readyState == 1) {
                $.each(order_printers, function() {
                    var socket_data = { 'printer': this, 
                    'logo': (biller && biller.logo ? site.base_url+'assets/uploads/logos/'+biller.logo : ''), 
                    'text': order_data };
                    socket.send(JSON.stringify({
                        type: 'print-receipt',
                        data: socket_data
                    }));
                });
                return false;
            } else {
                bootbox.alert('<?= lang('pos_print_error'); ?>');
                return false;
            }
        }

        function printBill() {
            if (socket.readyState == 1) {
                var socket_data = {
                    'printer': <?= json_encode($printer); ?>,
                    'logo': (biller && biller.logo ? site.base_url+'assets/uploads/logos/'+biller.logo : ''),
                    'text': bill_data
                };
                socket.send(JSON.stringify({
                    type: 'print-receipt',
                    data: socket_data
                }));
                return false;
            } else {
                bootbox.alert('<?= lang('pos_print_error'); ?>');
                return false;
            }
        }
    </script>
    <?php
}
?>
<script type="text/javascript">
$('.sortable_table tbody').sortable({
    containerSelector: 'tr'
});
</script>
<script type="text/javascript" charset="UTF-8"><?=$s2_file_date?></script>
<div id="ajaxCall"><i class="fa fa-spinner fa-pulse"></i></div>
<?php 
if (isset($print) && !empty($print)) {
    /* include FCPATH.'themes'.DIRECTORY_SEPARATOR.$Settings->theme.DIRECTORY_SEPARATOR.'views'.DIRECTORY_SEPARATOR.'pos'.DIRECTORY_SEPARATOR.'remote_printing.php'; */
    include 'remote_printing.php';
}
?>
</body>
</html>
<style>
img.img-rounded.img-thumbnail {
    width: 30px!important;
    height: 30px!important;
}


#pos div#leftdiv {
    padding: 0px;
}

div#botbuttons {
    overflow: hidden;
    margin: 0px;
    padding: 0px;
}

.col-lg-1, .col-lg-10, .col-lg-11, .col-lg-12, .col-lg-2, .col-lg-3, .col-lg-4, .col-lg-5, .col-lg-6, .col-lg-7, .col-lg-8, .col-lg-9, .col-md-1, .col-md-10, .col-md-11, .col-md-12, .col-md-2, .col-md-3, .col-md-4, .col-md-5, .col-md-6, .col-md-7, .col-md-8, .col-md-9, .col-sm-1, .col-sm-10, .col-sm-11, .col-sm-12, .col-sm-2, .col-sm-3, .col-sm-4, .col-sm-5, .col-sm-6, .col-sm-7, .col-sm-8, .col-sm-9, .col-xs-1, .col-xs-10, .col-xs-11, .col-xs-12, .col-xs-2, .col-xs-3, .col-xs-4, .col-xs-5, .col-xs-6, .col-xs-7, .col-xs-8, .col-xs-9 {
    padding: 0px!important;
    margin: 0px!important;
}

.row {
    margin: 0px;
}	
table#totalTable tr:nth-child(2) td,table#totalTable tr:nth-child(3) td {
    padding: 5px 0.5%!important;
    float: left;
    width: 25%!important;
}

table#totalTable tr{
    float: left;
    width: 100%;
}
table#totalTable tr:last-child td{
    float: left;
    padding: 5px 0.5%!important;
    float: left;
    width: 50%!important;
}

table#totalTable tbody {
    float: left;
    width: 100%;
}
		
td#clmkhachhanglhson {
    width: 100%;
    float: left;
} 
div#item-list {
    overflow-y: auto;
}
div#item-list::-webkit-scrollbar-thumb{background-color:#ff0202;background-image:-webkit-linear-gradient(45deg,rgba(255,255,255,.2) 25%,transparent 25%,transparent 50%,rgba(255,255,255,.2) 50%,rgba(255,255,255,.2) 75%,transparent 75%,transparent)}
div#item-list::-webkit-scrollbar-track{-webkit-box-shadow:inset 0 0 6px rgba(0,0,0,0.3);background-color:#F5F5F5}
div#item-list::-webkit-scrollbar{width:10px;background-color:#F5F5F5}
</style> 
