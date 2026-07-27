<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?><!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <base href="<?= site_url() ?>"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?> - <?= $Settings->site_name ?></title>
    <link rel="shortcut icon" href="<?= $assets ?>images/favicon.ico"/>
	<link href="<?= $assets ?>styles/theme.css" rel="stylesheet"/>
   
	  <script type="text/javascript" src="<?= $assets ?>js/jquery-2.0.3.min.js"></script>
    <script type="text/javascript" src="<?= $assets ?>js/jquery-migrate-1.2.1.min.js"></script>
	<script type="text/javascript" src="<?= $assets ?>js/jquery.number.min.js"></script>
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
	  <link href="<?= $assets ?>styles/misa-sidebar.css?v=20260728c" rel="stylesheet">
		<script type="text/javascript">
			$(window).load(function () {
				$("#loading").fadeOut("slow");
			});
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
                              location.reload();
                        } else {
                             addAlert('Có lỗi xảy ra', 'danger'); 
                        }
                    },error: function(data) { 
                        if(data.responseText.trim() == 'OK') {
                              location.reload();
                        } else {
                             addAlert('Có lỗi xảy ra', 'danger'); 
                        }
                    }
                });
            });
		</script>
	<script src="<?= $assets ?>ace/js/ace-extra.min.js"></script>
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
	<style>
	  .sorting_asc{
        color:#000;
    }
	.thongbaoconlai {
	    position: fixed;
	    z-index: 99999999;
	    bottom: 10px;
	    left: 0px;
	    width: 190px;
	    background: #fff;
	}
	
	@media only screen and (max-width: 768px) {
		  /* For mobile phones: */
		#navbar-container .navbar-header.pull-left {
			float: left!important;
			width: 45%;
		}

		.btn-group.visible-xs.pull-center.btn-visible-sm {
			float: right!important;
			width: 40%;
			line-height: 42px;
			text-align: right;
		}

		div#navbar-container {
			float: left;
			width: 100%;
		}
	}

	</style>
</head>

<body class="no-skin misa-erp-shell">

<div id="loading" class="no-print"></div>
	<div id="navbar" class="navbar navbar-default          ace-save-state">
			<div class="navbar-container ace-save-state" id="navbar-container">
				<button type="button" class="navbar-toggle menu-toggler pull-left" id="menu-toggler" data-target="#sidebar">
					<span class="sr-only">Toggle sidebar</span>

					<span class="icon-bar"></span>

					<span class="icon-bar"></span>

					<span class="icon-bar"></span>
				</button>

				<div class="navbar-header pull-left">
					<a href="<?= site_url() ?>" class="navbar-brand">
						<small>
							<i class="fa fa-cloud"></i>
							<span class="logo"><?= $Settings->site_name ?></span>
						</small>
					</a>
				</div>
				<div class="btn-group visible-xs pull-center btn-visible-sm">
					<a class="btn" href="<?= site_url('users/profile/' . $this->session->userdata('user_id')); ?>">
									<i class="ace-icon fa fa-user"></i>
					</a>
					<a href="<?= site_url('users/profile/' . $this->session->userdata('user_id') . '/#cpassword'); ?>" class="btn">
						<span class="fa fa-user"></span>
					</a>
					<a href="<?= site_url('logout'); ?>" class="btn">
						<span class="fa fa-sign-out"></span>
					</a>
					<?php if ($Owner) { ?>
	                    <a href="<?= site_url('users/payment/' . $this->session->userdata('user_id')); ?>" class="btn">
	                        <span class="fa fa-history"></span>
	                    </a>
	                <?php }?>
				</div>
				<div class="navbar-buttons navbar-header pull-right menu_posbasic hidden-xs" role="navigation">
					<ul class="nav ace-nav">
						<li class="white2">
							<a  class="" href="<?= site_url('welcome') ?>" title="<?= lang('dashboard') ?>">
								<i class="ace-icon fa fa-dashboard"></i>
								<span class="badge-blue"><?= lang('dashboard') ?></span>
							</a>
						</li>
					    <?php if ($Owner) { ?>
						<li class="grey2 dropdown-modal">
							<a class="" title="<?= lang('settings') ?>" data-placement="bottom" href="<?= site_url('system_settings') ?>">
								<i class="fa fa-cogs"></i>
								<span class=""><?= lang('settings') ?></span>
							</a>
						</li>
						<?php } ?>
						<li class="grey2 dropdown-modal">
							<a class="" title="<?= lang('calculator') ?>" data-placement="bottom" href="#" data-toggle="dropdown">
								<i class="fa fa-calculator"></i>
								<span class=""><?= lang('calculator') ?></span>
								
							</a>
							<ul class="dropdown-menu-right dropdown-navbar dropdown-menu dropdown-caret pull-right calc">
								<li class="dropdown-content">
									<span id="inlineCalc"></span>
								</li>
							</ul>
						</li>
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
						<?php if ($info) { ?>
                        <li class="grey2 dropdown-modal">
                            <a class="blightOrange" title="<?= lang('notifications') ?>" data-placement="bottom" href="#" data-toggle="dropdown">
                                <i class="fa fa-info-circle"></i>
                                <span class="number blightOrange black"><?= sizeof($info) ?></span>
                            </a>
                            <ul class="dropdown-menu pull-right content-scroll">
                                <li class="dropdown-header"><i class="fa fa-info-circle"></i> <?= lang('notifications'); ?></li>
                                <li class="dropdown-content">
                                    <div class="scroll-div">
                                        <div class="top-menu-scroll">
                                            <ol class="oe">
                                                <?php foreach ($info as $n) {
                                                    echo '<li>' . $n->comment . '</li>';
                                                } ?>
                                            </ol>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </li>
						<?php } ?>
						 <?php if ($events) { ?>
						<li class="grey2 dropdown-modal">
                            <a class="blightOrange" title="<?= lang('calendar') ?>" data-placement="bottom" href="#" data-toggle="dropdown">
                                <i class="fa fa-calendar"></i>
                                <span class="number blightOrange black"><?= sizeof($events) ?></span>
                            </a>
                            <ul class="dropdown-menu pull-right content-scroll">
                                <li class="dropdown-header">
                                <i class="fa fa-calendar"></i> <?= lang('upcoming_events'); ?>
                                </li>
                                <li class="dropdown-content">
                                    <div class="top-menu-scroll">
                                        <ol class="oe">
                                            <?php foreach ($events as $event) {
                                                echo '<li>' . date($dateFormats['php_ldate'], strtotime($event->start)) . ' <strong>' . $event->title . '</strong><br>'.$event->description.'</li>';
                                            } ?>
                                        </ol>
                                    </div>
                                </li>
                                <li class="dropdown-footer">
                                    <a href="<?= site_url('calendar') ?>" class="btn-block link">
                                        <i class="fa fa-calendar"></i> <?= lang('calendar') ?>
                                    </a>
                                </li>
                            </ul>
                        </li>
                    <?php } else { ?>
                    <li class="2 dropdown hidden-xs">
                        <a class="" title="<?= lang('calendar') ?>" data-placement="bottom" href="<?= site_url('calendar') ?>">
                            <i class="fa fa-calendar"></i>
							<span class="paddingpadding05"><?= lang('calendar') ?></span>
                        </a>
                    </li>
                    <?php } ?>
					
                    <?php if ($Owner && $Settings->update) { ?>
                    <li class="dropdown hidden-sm">
                        <a class="" title="<?= lang('update_available') ?>" 
                            data-placement="bottom" data-container="body" href="<?= site_url('system_settings/updates') ?>">
                            <i class="fa fa-download"></i>
							<span class="paddingpadding05"><?= lang('update_available') ?></span>
                        </a>
                    </li>
                        <?php } ?>
                    <?php if (($Owner || $Admin || $GP['reports-quantity_alerts'] || $GP['reports-expiry_alerts']) && ($qty_alert_num > 0 || $exp_alert_num > 0)) { ?>
                        <li class="dropdown hidden-sm">
                            <a class="blightOrange" title="<?= lang('alerts') ?>" 
                                data-placement="left" data-toggle="dropdown" href="#">
                                <i class="fa fa-exclamation-triangle"></i>
                            </a>
                            <ul class="dropdown-menu pull-right">
								<?php if ($Settings->khuyenmai) { ?>
                                <li>
                                    <a href="<?= site_url('reports/quantity_alerts') ?>" class="">
                                        <span class="label label-danger pull-right" style="margin-top:3px;"><?= $qty_alert_num; ?></span>
                                        <span style="padding-right: 35px;"><?= lang('quantity_alerts') ?></span>
                                    </a>
                                </li>
                                <?php
								}
								if ($Settings->product_expiry) { ?>
                                <li>
                                    <a href="<?= site_url('reports/expiry_alerts') ?>" class="">
                                        <span class="label label-danger pull-right" style="margin-top:3px;"><?= $exp_alert_num; ?></span>
                                        <span style="padding-right: 35px;"><?= lang('expiry_alerts') ?></span>
                                    </a>
                                </li>
                                <?php } ?>
                            </ul>
                        </li>
                    <?php } ?>
                   
                    <?php if ($Owner) { ?>
                        <li class="dropdown">
                            <a class="bdarkGreen2" id="today_profit" title="<?= lang('today_profit') ?>" 
                                data-placement="bottom" data-html="true" href="<?= site_url('reports/profit/today') ?>" 
                                data-toggle="modal" data-target="#myModal">
                                <i class="fa fa-hourglass-2"></i>
								<span class="paddingpadding05"><?= lang('today_profit') ?></span>
                            </a>
                        </li>
                    <?php } ?>
                    <?php if ($Owner || $Admin) { ?>
                 
                    <li class="dropdown hidden-xs">
                        <a class="bred2" title="<?= lang('clear_ls') ?>" data-placement="bottom" id="clearLS" href="#">
                            <i class="fa fa-eraser"></i>
							<span class="paddingpadding05"><?= lang('clear_ls') ?></span>
							
                        </a>
                    </li>
                    <?php } ?>

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
 
	<div class="main-container ace-save-state" id="main-container">
			<script type="text/javascript">
				try{ace.settings.loadState('main-container')}catch(e){}
			</script>
  		<div id="sidebar" class="sidebar responsive ace-save-state sidebar-fixed menu-lhson">
				<script type="text/javascript">
					try{ace.settings.loadState('sidebar')}catch(e){}
				</script>    
				<ul class="nav nav-list">
						<li class="mm_pos_lhson">
							<a class="btn btn-warning dropdown-s" title="<?= lang('pos') ?>" data-placement="bottom" href="<?= site_url('pos') ?>">
								<i class="fa fa-desktop" aria-hidden="true"></i><span class="padding05">POS - Bán hàng</span>
							</a>
						</li>			
                        <?php
                        if ($Owner || $Admin) {
                            ?>

							<li class="mm_products">
								<a href="#" class="dropdown-toggle">
									<i class="menu-icon fa fa-barcode"></i>
									<span class="menu-text">
									<?= lang('products'); ?>
									</span>

									<b class="arrow fa fa-angle-right"></b>
						
								</a>							
                             
                                <ul class="submenu">										
                                    <li id="products_index" class="">
                                        <a class="" href="<?= site_url('products'); ?>">
                                            <i class="fa fa-barcode"></i>
                                            <span class="text"> <?= lang('list_products'); ?></span>
                                        </a>
                                    </li>
                                    <li id="products_add">
                                        <a class="" href="<?= site_url('products/add'); ?>">
                                            <i class="fa fa-plus-circle"></i>
                                            <span class="text"> <?= lang('add_product'); ?></span>
                                        </a>
                                    </li>
                                    
									<li id="products_import_csv">
                                        <a class="" href="<?= site_url('products/import_xls'); ?>">
                                            <i class="fa fa-file-text"></i>
                                            <span class="text"> <?= lang('Import Excel 2003'); ?></span>
                                        </a>
                                    </li>
                                    <li id="products_print_barcodes">
                                        <a class="" href="<?= site_url('products/print_barcodes'); ?>">
                                            <i class="fa fa-tags"></i>
                                            <span class="text"> <?= lang('print_barcode_label'); ?></span>
                                        </a>
                                    </li>
                                   
                                </ul>
                            </li>

                            <li class="mm_sales <?= strtolower($this->router->fetch_method()) == 'sales' ? 'mm_pos' : '' ?>">
                                <a class="dropdown-toggle" href="#">
                                    <i class="menu-icon fa fa-shopping-cart"></i>
                                    <span class="menu-text"> <?= lang('sales'); ?>
                                    </span> 
									<b class="arrow fa fa-angle-right"></b>
                                </a>
                                <ul class="submenu">
                                    <li id="sales_index">
                                        <a class="" href="<?= site_url('sales'); ?>">
                                            <i class="fa fa-shopping-cart"></i>
                                            <span class="text"> <?= lang('list_sales'); ?>  tất cả</span>
                                        </a>
                                    </li>
                                   	<li id="sales_index_tmdt">
                                        <a class="" href="<?= site_url('sales/webtmdt'); ?>">
                                            <i class="fa fa-shopping-cart"></i>
                                            <span class="text"> <?= lang('Đơn sàn TMĐT'); ?></span>
                                        </a>
                                    </li>

									<li id="sales_index_web">
                                        <a class="" href="<?= site_url('sales/web'); ?>">
                                            <i class="fa fa-shopping-cart"></i>
                                            <span class="text"> <?= lang('Đơn web wordpress'); ?></span>
                                        </a>
                                    </li>
                                    <?php if (POS) { ?>
                                    <li id="pos_sales">
                                        <a class="" href="<?= site_url('pos/sales'); ?>">
                                            <i class="fa fa-shopping-cart"></i>
                                            <span class="text"> <?= lang('pos_sales'); ?></span>
                                        </a>
                                    </li>
                                    <?php } ?>
                                    <li id="sales_add">
                                        <a class="" href="<?= site_url('sales/add'); ?>">
                                            <i class="fa fa-plus-circle"></i>
                                            <span class="text"> <?= lang('add_sale'); ?></span>
                                        </a>
                                    </li>
                                    <li id="sales_sale_by_csv">
                                        <a class="" href="<?= site_url('sales/sale_by_csv'); ?>">
                                            <i class="fa fa-plus-circle"></i>
                                            <span class="text"> <?= lang('add_sale_by_csv'); ?></span>
                                        </a>
                                    </li>                                   
                                    
                                </ul>
                            </li>
                            <!-- <li class="mm_doideim">
                                <a class="dropdown-s" href="<?= site_url('sales/gift_cards_qua'); ?>">
                                    <i class="menu-icon fa fa-gift"></i>
                                    <span class="menu-text"> <?= lang('Đổi điểm quà tặng'); ?> 
                                    </span> 
                                </a>
                            </li>	
                            <li class="mm_doideim">
                                <a class="dropdown-s" href="<?= site_url('sales/gift_cards'); ?>">
                                    <i class="menu-icon fa fa-credit-card"></i>
                                    <span class="menu-text"> <?= lang('Thẻ Giảm Giá'); ?> 
                                    </span> 
                                </a>
                            </li>	 -->
							<li class="mm_doitac">
                                <a class="dropdown-toggle" href="#">
                                    <i class="menu-icon fa fa-truck"></i>
                                    <span class="menu-text text"> Giao Nhận </span> 
                                   	<b class="arrow fa fa-angle-right"></b>
                                </a>
                                <ul class="submenu">
                                    <li id="sales_deliveries">
                                        <a class="" href="<?= site_url('sales/deliveries'); ?>">
                                            <i class="fa fa-motorcycle"></i><span class="text"> <?= lang('DS giao hàng'); ?></span>
                                        </a>
                                    </li>
                                    <li id="quotes_add">
                                        <a class="" href="<?= site_url('doitac'); ?>">
                                            <i class="fa fa-users"></i>
                                            <span class="text"> Đối tác giao hàng</span>
                                        </a>
                                    </li>
                                </ul>
                            </li>
							<!-- <li class="mm_returns">
                                <a class="dropdown-toggle" href="#">
                                    <i class="menu-icon fa fa-random"></i>
                                    <span class="menu-text text"> Thu hồi </span> 
                                   	<b class="arrow fa fa-angle-right"></b>
                                </a>
                                <ul class="submenu">
                                    <li id="quotes_index">
                                        <a class="" href="<?= site_url('returns'); ?>">
                                            <i class="fa fa-random"></i>
                                            <span class="text"> DS thu hồi tiền mặt</span>
                                        </a>
                                    </li>
                                    <li id="quotes_add">
                                        <a class="" href="<?= site_url('returns/add'); ?>">
                                            <i class="fa fa-plus-circle"></i>
                                            <span class="text"> Thêm thu hồi tiền mặt</span>
                                        </a>
                                    </li>
									<li id="quotes_add">
                                        <a class="" href="<?= site_url('sales/addthuhoi'); ?>">
                                            <i class="fa fa-plus-circle"></i>
                                            <span class="text"> Thêm thu hồi</span>
                                        </a>
                                    </li>
									<li id="quotes_index">
                                        <a class="" href="<?= site_url('sales/danhsachthuhoi'); ?>">
                                            <i class="fa fa-random"></i>
                                            <span class="text"> Danh sách thu hồi</span>
                                        </a>
                                    </li>
                                </ul>
                            </li> -->
                            <li class="mm_quotes">
                                <a class="dropdown-toggle" href="#">
                                    <i class="menu-icon fa fa-files-o"></i>
                                    <span class="menu-text text"> <?= lang('quotes'); ?> </span> 
                                   	<b class="arrow fa fa-angle-right"></b>
                                </a>
                                <ul class="submenu">
                                    <li id="quotes_index">
                                        <a class="" href="<?= site_url('quotes'); ?>">
                                            <i class="fa fa-files-o"></i>
                                            <span class="text"> <?= lang('list_quotes'); ?></span>
                                        </a>
                                    </li>
                                    <li id="quotes_add">
                                        <a class="" href="<?= site_url('quotes/add'); ?>">
                                            <i class="fa fa-plus-circle"></i>
                                            <span class="text"> <?= lang('add_quote'); ?></span>
                                        </a>
                                    </li>
                                </ul>
                            </li>
							<!-- <?php if ($Settings->khuyenmai) { ?>
							<li class="mm_khuyenmai">
								<a class="dropdown-toggle" href="#">
                                    <i class="menu-icon fa fa-sort-numeric-desc"></i>
                                    <span class="menu-text"> Khuyến mãi </span> 
                                   <b class="arrow fa fa-angle-right"></b>
                                </a>
                                <ul class="submenu">
                                    <li id="khuyenmai_index">
                                        <a class="submenu2" href="<?= site_url('khuyenmai'); ?>">
                                            <i class="fa fa-sort-numeric-desc"></i>
                                            <span class="text">DS khuyến mãi</span>
                                        </a>
                                    </li>
                                    <li id="khuyenmai_add">
                                        <a class="submenu2" href="<?= site_url('khuyenmai/add'); ?>">
                                            <i class="fa fa-plus-circle"></i>
                                            <span class="text"> Thêm KM</span>
                                        </a>
                                    </li>
                                    <li id="khuyenmai_index">

                                        <a class="submenu2" href="<?= site_url('khuyenmai/indexnew'); ?>">

                                            <i class="fa fa-sort-numeric-desc"></i>

                                            <span class="text"><?= lang('Khuyến mãi sản phẩm'); ?></span>

                                        </a>

                                    </li>
                                    <li id="khuyenmai_index">

                                        <a class="submenu2" href="<?= site_url('reports/saleskhuyenmai'); ?>">

                                            <i class="fa fa-bar-chart-o"></i>

                                            <span class="text"><?= lang('Báo cáo khuyến mãi'); ?></span>

                                        </a>

                                    </li>
                                </ul>
                            </li>
							<?php }?> -->
                            <li class="mm_purchases">
                                <a class="dropdown-toggle" href="#">
                                    <i class="menu-icon fa fa-cart-plus"></i>
                                    <span class="menu-text"> <?= lang('purchases'); ?> </span>
                                   	<b class="arrow fa fa-angle-right"></b>
                                </a>
                                <ul class="submenu">
                                    <li id="purchases_index">
                                        <a class="" href="<?= site_url('purchases'); ?>">
                                            <i class="fa fa-cart-plus"></i>
                                            <span class="text"> <?= lang('list_purchases'); ?></span>
                                        </a>
                                    </li>
									<li id="purchases_index_trahang">
                                        <a class="" href="<?= site_url('purchases/indextrahang'); ?>">
                                            <i class="fa fa-cart-arrow-down"></i>
                                            <span class="text"> <?= lang('DS trả hàng NCC'); ?></span>
                                        </a>
                                    </li>
									<li id="purchases_add">
                                        <a class="" href="<?= site_url('purchases/return_purchase_ncc'); ?>">
                                            <i class="fa fa-random"></i><span class="text"> Trả hàng NCC</span>
                                        </a>
                                    </li>
                                    <li id="purchases_add">
                                        <a class="" href="<?= site_url('purchases/add'); ?>">
                                            <i class="fa fa-plus-circle"></i>
                                            <span class="text"> <?= lang('add_purchase'); ?></span>
                                        </a>
                                    </li>
                                    <li id="purchases_purchase_by_csv">
                                        <a class="" href="<?= site_url('purchases/purchase_by_csv'); ?>">
                                            <i class="fa fa-plus-circle"></i>
                                            <span class="text"> <?= lang('add_purchase_by_csv'); ?></span>
                                        </a>
                                    </li>
                                    
                                </ul>
                            </li>
							<li class="mm_chiphi">
                                <a class="dropdown-toggle" href="#">
                                    <i class="menu-icon fa-fw fa fa-dollar"></i>
                                    <span class="menu-text"> Thu - Chi </span> 
                                   <b class="arrow fa fa-angle-right"></b>
                                </a>
                                <ul class="submenu">
                                    <li id="chiphi_expenses">
                                        <a class="" href="<?= site_url('purchases/expenses'); ?>">
                                            <i class="fa fa-dollar"></i>
                                            <span class="text"> <?= lang('list_expenses'); ?></span>
                                        </a>
                                    </li>
									<li id="purchases_add_chincc">
										<a class="" href="<?= site_url('reports/khoanthu'); ?>">
                                            <i class="fa fa-dollar"></i><span class="text"> Liệt kê khoản thu</span>
                                        </a>
                                    </li>
                                    <li id="chiphi_add_expense">
                                        <a class="" href="<?= site_url('purchases/add_expense'); ?>" data-toggle="modal" data-target="#myModal">
                                            <i class="fa fa-plus-circle"></i>
                                            <span class="text"> <?= lang('add_expense'); ?></span>
                                        </a>
                                    </li>
									<li id="purchases_add_phieuthu">
                                        <a class="" href="<?= site_url('purchases/add_phieuthu'); ?>" 
                                            data-toggle="modal" data-target="#myModal">
                                            <i class="fa fa-plus-circle"></i><span class="text"> <?= lang('add_phieuthu'); ?></span>
                                        </a>
                                    </li>                                    
									<!--<li id="purchases_add_phieuthukh">
                                        <a class="" data-toggle="modal" data-target="#myModal" href="<?= site_url('sales/add_phieuthukh'); ?>">
                                            <i class="fa fa-dollar"></i><span class="text"> <?= lang('add_phieuthukh'); ?></span>
                                        </a>
                                    </li>-->	
                                    <li id="purchases_add_phieuthu_kh">
                                        <a class="" href="<?= site_url('customers/add_deposit_kh'); ?>" 
                                            data-toggle="modal" data-target="#myModal">
                                            <i class="fa fa-plus-circle"></i><span class="text"> <?= lang('Thêm tiền cọc'); ?></span>
                                        </a>
                                    </li>								
                                </ul>
                            </li>
                            <li class="mm_soquy">
                                <a class="dropdown-toggle" href="#">
                                    <i class="menu-icon fa-fw fa fa-dollar"></i>
                                    <span class="menu-text"> Sổ Quỹ - PTTT </span> 
                                   <b class="arrow fa fa-angle-right"></b>
                                </a>
                                <ul class="submenu">
                                    <li id="soquy_index">
                                        <a class="" href="<?= site_url('the'); ?>">
                                            <i class="fa fa-credit-card"></i>
                                            <span class="text"> <?= lang('Sổ Quỹ - PTTT'); ?></span>
                                        </a>
                                    </li>
                                     <li id="soquy_add">
                                        <a class="" href="<?= site_url('the/add'); ?>">
                                            <i class="fa fa-plus-circle"></i>
                                            <span class="text"> <?= lang('Thêm PTTT'); ?></span>
                                        </a>
                                    </li>	
                                    <li id="soquy_index_tragop">
                                        <a class="" href="<?= site_url('the/tragop'); ?>">
                                            <i class="fa fa-credit-card"></i>
                                            <span class="text"> <?= lang('Quản lý trả góp'); ?></span>
                                        </a>
                                    </li>	
                                    <li id="soquy_index_tragop">
                                        <a class="" href="<?= site_url('reports/baocaothutragop'); ?>">
                                            <i class="fa fa-list-ol"></i>
                                            <span class="text"> <?= lang('Danh sách trả góp'); ?></span>
                                        </a>
                                    </li>							
                                </ul>
                            </li>
                          
							<li class="mm_kiemkho">
                                <a class="dropdown-toggle" href="#">
                                    <i class="menu-icon fa-fw fa fa-search"></i>
                                    <span class="menu-text"> Kiểm kho </span> 
                                   <b class="arrow fa fa-angle-right"></b>
                                </a>
                                <ul class="submenu">
									 <li id="products_quantity_adjustments">
                                        <a class="" href="<?= site_url('products/quantity_adjustments'); ?>">
                                            <i class="fa fa-filter"></i>
                                            <span class="text"> <?= lang('quantity_adjustments'); ?></span>
                                        </a>
                                    </li>
                                    <li id="products_add_adjustment">
                                        <a class="" href="<?= site_url('products/add_adjustment'); ?>">
                                            <i class="fa fa-filter"></i>
                                            <span class="text"> <?= lang('add_adjustment'); ?></span>
                                        </a>
                                    </li>
                                    <li id="products_stock_counts">
                                        <a class="" href="<?= site_url('products/stock_counts'); ?>">
                                            <i class="fa fa-list-ol"></i>
                                            <span class="text"> <?= lang('stock_counts'); ?></span>
                                        </a>
                                    </li>
                                    <li id="products_count_stock">
                                        <a class="" href="<?= site_url('products/count_stock'); ?>">
                                            <i class="fa fa-plus-circle"></i>
                                            <span class="text"> <?= lang('count_stock'); ?></span>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                            <!-- <li class="mm_transfers">
                                <a class="dropdown-toggle" href="#">
                                    <i class="menu-icon fa fa-refresh"></i>
                                    <span class="menu-text"> <?= lang('transfers'); ?> </span> 
                                   <b class="arrow fa fa-angle-right"></b>
                                </a>
                                <ul class="submenu">
                                    <li id="transfers_index">
                                        <a class="" href="<?= site_url('transfers'); ?>">
                                            <i class="fa fa fa-refresh"></i><span class="text"> <?= lang('list_transfers'); ?></span>
                                        </a>
                                    </li>
                                    <li id="transfers_add">
                                        <a class="" href="<?= site_url('transfers/add'); ?>">
                                            <i class="fa fa-plus-circle"></i><span class="text"> <?= lang('add_transfer'); ?></span>
                                        </a>
                                    </li>
                                    <li id="transfers_purchase_by_csv">
                                        <a class="" href="<?= site_url('transfers/transfer_by_csv'); ?>">
                                            <i class="fa fa-plus-circle"></i><span class="text"> <?= lang('add_transfer_by_csv'); ?></span>
                                        </a>
                                    </li>
                                </ul>
                            </li> -->

                            <li class="mm_auth mm_customers mm_suppliers mm_billers">
                                <a class="dropmenu" href="#">
                                <i class="menu-icon fa fa-user-plus"></i>
                                <span class="menu-text"> <?= lang('people'); ?> </span> 
                                <b class="arrow fa fa-angle-right"></b>
                                </a>
                                <ul class="submenu">
                                    <?php if ($Owner) { ?>
                                    <li id="auth_users">
                                        <a class="" href="<?= site_url('users'); ?>">
                                            <i class="fa fa-user-plus"></i><span class="text"> <?= lang('list_users'); ?></span>
                                        </a>
                                    </li>
                                    <li id="auth_create_user">
                                        <a class="" href="<?= site_url('users/create_user'); ?>">
                                            <i class="fa fa-user-plus"></i><span class="text"> <?= lang('new_user'); ?></span>
                                        </a>
                                    </li>
                                    <li id="billers_index">
                                        <a class="" href="<?= site_url('billers'); ?>">
                                            <i class="fa fa-users"></i><span class="text"> <?= lang('list_billers'); ?></span>
                                        </a>
                                    </li>
                                    <li id="billers_index">
                                        <a class="" href="<?= site_url('billers/add'); ?>" data-toggle="modal" data-target="#myModal">
                                            <i class="fa fa-plus-circle"></i><span class="text"> <?= lang('add_biller'); ?></span>
                                        </a>
                                    </li>
                                    <?php } ?>
                                    
                                    
                                </ul>
                            </li>
							<li class="mm_khachhang">
                                <a class="dropdown-toggle" href="#">
                                    <i class="menu-icon fa fa-user"></i>
                                    <span class="menu-text"> Khách hàng </span> 
                                   <b class="arrow fa fa-angle-right"></b>
                                </a>
                                <ul class="submenu">
                                    <li id="customers_index">
                                        <a class="" href="<?= site_url('customers'); ?>">
                                            <i class="fa fa-users"></i><span class="text"> <?= lang('list_customers'); ?></span>
                                        </a>
                                    </li>
                                    <li id="customers_index">
                                        <a class="" href="<?= site_url('customers/add'); ?>" data-toggle="modal" data-target="#myModal">
                                            <i class="fa fa-plus-circle"></i><span class="text"> <?= lang('add_customer'); ?></span>
                                        </a>
                                    </li>
                                    <li id="deposit_index">
                                        <a class="" href="<?= site_url('customers/listdeposit'); ?>">
                                            <i class="fa fa-list-ol"></i><span class="text"> <?= lang('Danh sách đặt cọc'); ?></span>
                                        </a>
                                    </li>
                                </ul>
                            </li>
							<li class="mm_nhacungcap">
                                <a class="dropdown-toggle" href="#">
                                    <i class="menu-icon fa fa-home"></i>
                                    <span class="menu-text"> Nhà cung cấp </span> 
                                   <b class="arrow fa fa-angle-right"></b>
                                </a>
                                <ul class="submenu">
                                    <li id="suppliers_index">
                                        <a class="" href="<?= site_url('suppliers'); ?>">
                                            <i class="fa fa-users"></i><span class="text"> <?= lang('list_suppliers'); ?></span>
                                        </a>
                                    </li>
                                    <li id="suppliers_index">
                                        <a class="" href="<?= site_url('suppliers/add'); ?>" data-toggle="modal" data-target="#myModal">
                                            <i class="fa fa-plus-circle"></i><span class="text"> <?= lang('add_supplier'); ?></span>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                            <li class="mm_notifications">
                                <a class="submenu" href="<?= site_url('notifications'); ?>">
                                    <i class="menu-icon fa fa-info-circle"></i><span class="text"> <?= lang('notifications'); ?></span>
                                </a>
                            </li>
                            <?php if ($Owner) { ?>
                                <li class="mm_system_settings <?= strtolower($this->router->fetch_method()) == 'sales' ? '' : 'mm_pos' ?>">
                                    <a class="dropmenu" href="#">
                                        <i class="menu-icon fa fa-cog"></i><span class="text"> <?= lang('settings'); ?> </span> 
                                        <b class="arrow fa fa-angle-right"></b>
                                    </a>
                                    <ul class="submenu">
                                        <li id="system_settings_index">
                                            <a href="<?= site_url('system_settings') ?>">
                                                <i class="fa fa-cog"></i><span class="text"> <?= lang('system_settings'); ?></span>
                                            </a>
                                        </li>
                                      
                                        <?php if (POS) { ?>
                                        <li id="pos_settings">
                                            <a href="<?= site_url('pos/settings') ?>">
                                                <i class="fa fa-th-large"></i><span class="text"> <?= lang('pos_settings'); ?></span>
                                            </a>
                                        </li>    
                                         
                                        <?php } ?>
                                        <li id="pos_settings">
                                            <a href="<?= site_url('system_settings/indextmdt') ?>">
                                                <i class="fa fa-globe"></i><span class="text"> <?= lang('Liên kết sàn TMĐT'); ?></span>
                                            </a>
                                        </li>
                                        
                                        <li id="system_settings_change_logo">
                                            <a href="<?= site_url('system_settings/change_logo') ?>" data-toggle="modal" data-target="#myModal">
                                                <i class="fa fa-upload"></i><span class="text"> <?= lang('change_logo'); ?></span>
                                            </a>
                                        </li> 
                                        <li id="system_settings_customer_groups">
                                            <a href="<?= site_url('system_settings/customer_groups') ?>">
                                                <i class="fa fa-chain"></i><span class="text"> <?= lang('customer_groups'); ?></span>
                                            </a>
                                        </li>
                                        <li id="system_settings_price_groups">
                                            <a href="<?= site_url('system_settings/price_groups') ?>">
                                                <i class="fa fa-dollar"></i><span class="text"> <?= lang('price_groups'); ?></span>
                                            </a>
                                        </li>
                                        <li id="system_settings_categories">
                                            <a href="<?= site_url('system_settings/categories') ?>">
                                                <i class="fa fa-folder-open"></i><span class="text"> <?= lang('categories'); ?></span>
                                            </a>
                                        </li>
										<li id="system_settings_nhom">
                                            <a href="<?= site_url('system_settings/nhom') ?>">
                                                <i class="fa fa-folder-open"></i><span class="text"> <?= lang('Nhóm sản phẩm'); ?></span>
                                            </a>
                                        </li>
                                        <li id="system_settings_expense_categories">
                                            <a href="<?= site_url('system_settings/expense_categories') ?>">
                                                <i class="fa fa-folder-open"></i><span class="text"> <?= lang('expense_categories'); ?></span>
                                            </a>
                                        </li>
                                        <li id="system_settings_units">
                                            <a href="<?= site_url('system_settings/units') ?>">
                                                <i class="fa fa-wrench"></i><span class="text"> <?= lang('units'); ?></span>
                                            </a>
                                        </li>
                                        <li id="system_settings_brands">
                                            <a href="<?= site_url('system_settings/brands') ?>">
                                                <i class="fa fa-th-list"></i><span class="text"> <?= lang('brands'); ?></span>
                                            </a>
                                        </li>
										 <li id="system_settings_xuatxut">
                                            <a href="<?= site_url('system_settings/xuatxu') ?>">
                                                <i class="fa fa-th-list"></i><span class="text"> Xuất xứ</span>
                                            </a>
                                        </li>
                                        <li id="system_settings_variants">
                                            <a href="<?= site_url('system_settings/variants') ?>">
                                                <i class="fa fa-tags"></i><span class="text"> <?= lang('variants'); ?></span>
                                            </a>
                                        </li>
                                        <li id="system_settings_tax_rates">
                                            <a href="<?= site_url('system_settings/tax_rates') ?>">
                                                <i class="fa fa-plus-circle"></i><span class="text"> <?= lang('tax_rates'); ?></span>
                                            </a>
                                        </li>
                                        <li id="system_settings_warehouses">
                                            <a href="<?= site_url('system_settings/warehouses') ?>">
                                                <i class="fa fa-building-o"></i><span class="text"> <?= lang('warehouses'); ?></span>
                                            </a>
                                        </li>
                                        <li id="system_settings_email_templates">
                                            <a href="<?= site_url('system_settings/email_templates') ?>">
                                                <i class="fa fa-envelope"></i><span class="text"> <?= lang('email_templates'); ?></span>
                                            </a>
                                        </li>
										<li id="system_settings_print_khac">
                                            <a href="<?= site_url('system_settings/print_khac') ?>">
                                                <i class="fa fa-print"></i><span class="text"> <?= lang('Mẫu hóa đơn khác'); ?></span>
                                            </a>
                                        </li>
										<li id="system_settings_print_templates">
                                            <a href="<?= site_url('system_settings/print_templates') ?>">
                                                <i class="fa fa-print"></i><span class="text"> <?= lang('print_templates'); ?></span>
                                            </a>
                                        </li>
										<li id="system_settings_thuchi_templates">
                                            <a href="<?= site_url('system_settings/thuchi_templates') ?>">
                                                <i class="fa fa-print"></i><span class="text"> Mẫu in thu/chi</span>
                                            </a>
                                        </li>
                                        <li id="system_settings_user_groups">
                                            <a href="<?= site_url('system_settings/user_groups') ?>">
                                                <i class="fa fa-key"></i><span class="text"> <?= lang('group_permissions'); ?></span>
                                            </a>
                                        </li>
                                        <li id="system_settings_backups">
                                            <a href="<?= site_url('system_settings/backups') ?>">
                                                <i class="fa fa-database"></i><span class="text"> <?= lang('backups'); ?></span>
                                            </a>
                                        </li>
                                    </ul>
                                </li>
                            <?php } ?>
                            <li class="mm_reports">
                                <a class="dropmenu" href="#">
                                    <i class="menu-icon fa fa-bar-chart-o"></i>
                                    <span class="menu-text"> <?= lang('reports'); ?> </span> 
                                    <b class="arrow fa fa-angle-right"></b>
                                </a>
                                <ul class="submenu">
                                    <li id="reports_index">
                                        <a href="<?= site_url('reports') ?>">
                                            <i class="fa fa-bars"></i><span class="text"> <?= lang('overview_chart'); ?></span>
                                        </a>
                                    </li>
                                    <li id="reports_warehouse_stock">
                                        <a href="<?= site_url('reports/warehouse_stock') ?>">
                                            <i class="fa fa-building"></i><span class="text"> <?= lang('warehouse_stock'); ?></span>
                                        </a>
                                    </li>
                                    <li id="reports_best_sellers">
                                        <a href="<?= site_url('reports/best_sellers') ?>">
                                            <i class="fa fa-line-chart"></i><span class="text"> <?= lang('best_sellers'); ?></span>
                                        </a>
                                    </li>
                                   
                                    <li id="reports_quantity_alerts">
                                        <a href="<?= site_url('reports/quantity_alerts') ?>">
                                            <i class="fa fa-bar-chart-o"></i><span class="text"> <?= lang('product_quantity_alerts'); ?></span>
                                        </a>
                                    </li>
                                    <?php if ($Settings->product_expiry) { ?>
                                    <li id="reports_expiry_alerts">
                                        <a href="<?= site_url('reports/expiry_alerts') ?>">
                                            <i class="fa fa-bar-chart-o"></i><span class="text"> <?= lang('product_expiry_alerts'); ?></span>
                                        </a>
                                    </li>
                                    <?php } ?>
                                    <li id="reports_products">
                                        <a href="<?= site_url('reports/products') ?>">
                                            <i class="fa fa-barcode"></i><span class="text"> <?= lang('Báo cáo xuất nhập tồn'); ?></span>
                                        </a>
                                    </li>
                                    <li id="reports_adjustments">
                                        <a href="<?= site_url('reports/adjustments') ?>">
                                            <i class="fa fa-filter"></i><span class="text"> <?= lang('adjustments_report'); ?></span>
                                        </a>
                                    </li>
                                    <li id="reports_categories">
                                        <a href="<?= site_url('reports/categories') ?>">
                                            <i class="fa fa-folder-open"></i><span class="text"> <?= lang('categories_report'); ?></span>
                                        </a>
                                    </li>
									<li id="reports_nhom">
                                        <a href="<?= site_url('reports/nhom') ?>">
                                            <i class="fa fa-folder-open"></i><span class="text"> <?= lang('Báo cáo nhóm sản phẩm'); ?></span>
                                        </a>
                                    </li>
                                    <li id="reports_brands">
                                        <a href="<?= site_url('reports/brands') ?>">
                                            <i class="fa fa-cubes"></i><span class="text"> <?= lang('brands_report'); ?></span>
                                        </a>
                                    </li>
									<li id="reports_brands">
                                        <a href="<?= site_url('reports/xuatxu') ?>">
                                            <i class="fa fa-cubes"></i><span class="text"> Báo cáo xuất xứ</span>
                                        </a>
                                    </li>
                                    <li id="reports_daily_sales">
                                        <a href="<?= site_url('reports/daily_sales') ?>">
                                            <i class="fa fa-calendar"></i><span class="text"> <?= lang('daily_sales'); ?></span>
                                        </a>
                                    </li>
                                    <li id="reports_monthly_sales">
                                        <a href="<?= site_url('reports/monthly_sales') ?>">
                                            <i class="fa fa-calendar"></i><span class="text"> <?= lang('monthly_sales'); ?></span>
                                        </a>
                                    </li>
                                    <li id="reports_sales">
                                        <a href="<?= site_url('reports/sales') ?>">
                                            <i class="fa fa-heart"></i><span class="text"> <?= lang('sales_report'); ?></span>
                                        </a>
                                    </li>
									
                                    <li id="reports_payments">
                                        <a href="<?= site_url('reports/payments') ?>">
                                            <i class="fa fa-money"></i><span class="text"> <?= lang('payments_report'); ?></span>
                                        </a>
                                    </li>
                                    <li id="reports_profit_loss">
                                        <a href="<?= site_url('reports/profit_loss') ?>">
                                            <i class="fa fa-money"></i><span class="text"> <?= lang('Báo cáo lợi nhuận tất cả'); ?></span>
                                        </a>
                                    </li>
                                    <li id="reports_profit_loss_hd">
                                        <a href="<?= site_url('reports/salesloinhuan') ?>">
                                            <i class="fa fa-money"></i><span class="text"> <?= lang('Lợi nhuận theo hóa đơn'); ?></span>
                                        </a>
                                    </li>
									<li id="reports_profit_loss">
                                        <a href="<?= site_url('reports/profit_loss_thuan') ?>">
                                            <i class="fa fa-money"></i><span class="text"> <?= lang('Báo cáo kết quả hoạt động kinh doanh'); ?></span>
                                        </a>
                                    </li>
                                    <li id="reports_daily_purchases">
                                        <a href="<?= site_url('reports/daily_purchases') ?>">
                                            <i class="fa fa-calendar"></i><span class="text"> <?= lang('daily_purchases'); ?></span>
                                        </a>
                                    </li>
                                    <li id="reports_monthly_purchases">
                                        <a href="<?= site_url('reports/monthly_purchases') ?>">
                                            <i class="fa fa-calendar"></i><span class="text"> <?= lang('monthly_purchases'); ?></span>
                                        </a>
                                    </li>
                                    <li id="reports_purchases">
                                        <a href="<?= site_url('reports/purchases') ?>">
                                            <i class="fa fa-star"></i><span class="text"> <?= lang('purchases_report'); ?></span>
                                        </a>
                                    </li>
									<li id="reports_trahangncc">
                                        <a href="<?= site_url('reports/purchases_return') ?>">
                                            <i class="fa fa-star"></i><span class="text"> Báo cáo trả hàng NCC</span>
                                        </a>
                                    </li>
									<li id="reports_trahangkhach">
                                        <a href="<?= site_url('returns/baocao') ?>">
                                            <i class="fa fa-random"></i><span class="text"> Báo cáo thu hồi tiền mặt</span>
                                        </a>
                                    </li>
									<li id="reports_trahangkhach">
                                        <a href="<?= site_url('reports/baocaotralhson') ?>">
                                            <i class="fa fa-random"></i><span class="text"> Báo cáo trả hàng khách</span>
                                        </a>
                                    </li>
                                    <li id="reports_expenses">
                                        <a href="<?= site_url('reports/expenses') ?>">
                                            <i class="fa fa-dollar"></i><span class="text"> <?= lang('expenses_report'); ?></span>
                                        </a>
                                    </li>
									<li id="reports_thu">
                                        <a href="<?= site_url('reports/baocaothu') ?>">
                                            <i class="fa fa-dollar"></i><span class="text"> <?= lang('Báo cáo khoản thu'); ?></span>
                                        </a>
                                    </li>
									<li id="reports_chuyen">
                                        <a href="<?= site_url('transfers/baocaochuyen') ?>">
                                            <i class="fa fa-random"></i><span class="text"> <?= lang('Báo cáo chuyển kho'); ?></span>
                                        </a>
                                    </li>
                                    <li id="reports_customer_report">
                                        <a href="<?= site_url('reports/customers') ?>">
                                            <i class="fa fa-users"></i><span class="text"> <?= lang('customers_report'); ?></span>
                                        </a>
                                    </li>
                                    <li id="reports_supplier_report">
                                        <a href="<?= site_url('reports/suppliers') ?>">
                                            <i class="fa fa-users"></i><span class="text"> <?= lang('suppliers_report'); ?></span>
                                        </a>
                                    </li>
									<li id="reports_doitac_report">
                                        <a href="<?= site_url('reports/doitac') ?>">
                                            <i class="fa fa-truck"></i><span class="text"> <?= lang('Đối tác giao hàng'); ?></span>
                                        </a>
                                    </li>
                                    <li id="reports_staff_report">
                                        <a href="<?= site_url('reports/users') ?>">
                                            <i class="fa fa-users"></i><span class="text"> <?= lang('staff_report'); ?></span>
                                        </a>
                                    </li>
                                    <li id="reportskhuyenmai_index">

                                        <a class="submenu2" href="<?= site_url('reports/saleskhuyenmai'); ?>">

                                            <i class="fa fa-bar-chart-o"></i>

                                            <span class="text"><?= lang('Báo cáo khuyến mãi'); ?></span>

                                        </a>

                                    </li>
                                </ul>


                        <?php
                        } else { // not owner and not admin
                            ?>
                             
                            <li class="mm_bccn_lhson">
								<a class="btn btn-warning" title="Báo cáo cuối ngày" href="<?= site_url('the'); ?>">
									<i class="fa fa-money" aria-hidden="true"></i><span class="padding05">Báo cáo cuối ngày</span>
								</a>
							</li>
                            <?php if ($GP['products-index'] || $GP['products-add'] || $GP['products-barcode'] || $GP['products-adjustments'] || $GP['products-stock_count']) { ?>
                            <li class="mm_products">
                                <a class="dropmenu" href="#">
                                    <i class="menu-icon fa fa-barcode"></i>
                                    <span class="text"> <?= lang('products'); ?> 
                                    </span> <b class="arrow fa fa-angle-right"></b>
                                </a>
                                <ul class="submenu">
                                    <li id="products_index">
                                        <a class="" href="<?= site_url('products'); ?>">
                                            <i class="fa fa-barcode"></i><span class="text"> <?= lang('list_products'); ?></span>
                                        </a>
                                    </li>
                                    <?php if ($GP['products-add']) { ?>
                                    <li id="products_add">
                                        <a class="" href="<?= site_url('products/add'); ?>">
                                            <i class="fa fa-plus-circle"></i><span class="text"> <?= lang('add_product'); ?></span>
                                        </a>
                                    </li>
                                    <?php } ?>
                                    <?php if ($GP['products-barcode']) { ?>
                                    <li id="products_sheet">
                                        <a class="" href="<?= site_url('products/print_barcodes'); ?>">
                                            <i class="fa fa-tags"></i><span class="text"> <?= lang('print_barcode_label'); ?></span>
                                        </a>
                                    </li>
                                    <?php } ?>
                                    <?php if ($GP['products-adjustments']) { ?>
                                    <li id="products_quantity_adjustments">
                                        <a class="" href="<?= site_url('products/quantity_adjustments'); ?>">
                                            <i class="fa fa-filter"></i><span class="text"> <?= lang('quantity_adjustments'); ?></span>
                                        </a>
                                    </li>
                                    <li id="products_add_adjustment">
                                        <a class="" href="<?= site_url('products/add_adjustment'); ?>">
                                            <i class="fa fa-filter"></i>
                                            <span class="text"> <?= lang('add_adjustment'); ?></span>
                                        </a>
                                    </li>
                                    <?php } ?>
                                    <?php if ($GP['products-stock_count']) { ?>
                                    <li id="products_stock_counts">
                                        <a class="" href="<?= site_url('products/stock_counts'); ?>">
                                            <i class="fa fa-list-ol"></i>
                                            <span class="text"> <?= lang('stock_counts'); ?></span>
                                        </a>
                                    </li>
                                    <li id="products_count_stock">
                                        <a class="" href="<?= site_url('products/count_stock'); ?>">
                                            <i class="fa fa-plus-circle"></i>
                                            <span class="text"> <?= lang('count_stock'); ?></span>
                                        </a>
                                    </li>
                                    <?php } ?>
                                </ul>
                            </li>
                            <?php } ?>

                            <?php if ($GP['sales-index'] || $GP['sales-add'] || $GP['sales-gift_cards']) { ?>
                            <li class="mm_sales <?= strtolower($this->router->fetch_method()) == 'sales' ? 'mm_pos' : '' ?>">
                                <a class="dropmenu" href="#">
                                    <i class="menu-icon fa fa-heart"></i>
                                    <span class="menu-text"> <?= lang('sales'); ?> 
                                    </span> <b class="arrow fa fa-angle-right"></b>
                                </a>
                                <ul class="submenu">
                                    <li id="sales_index">
                                        <a class="" href="<?= site_url('sales'); ?>">
                                            <i class="fa fa-heart"></i><span class="text"> <?= lang('list_sales'); ?></span>
                                        </a>
                                    </li>
                                    <li id="sales_index_tmdt">
                                        <a class="" href="<?= site_url('sales/webtmdt'); ?>">
                                            <i class="fa fa-shopping-cart"></i>
                                            <span class="text"> <?= lang('Đơn sàn TMĐT'); ?></span>
                                        </a>
                                    </li>
									<li id="sales_index">
                                        <a class="" href="<?= site_url('sales/web'); ?>">
                                            <i class="fa fa-heart"></i>
                                            <span class="text"> <?= lang('Đơn web api woocommerce'); ?></span>
                                        </a>
                                    </li>
                                    
                                    <?php if (POS && $GP['pos-index']) { ?>
                                    <li id="pos_sales">
                                        <a class="" href="<?= site_url('pos/sales'); ?>">
                                            <i class="fa fa-heart"></i><span class="text"> <?= lang('pos_sales'); ?></span>
                                        </a>
                                    </li>
                                    <?php } ?>
                                    <?php if ($GP['sales-add']) { ?>
                                    <li id="sales_add">
                                        <a class="" href="<?= site_url('sales/add'); ?>">
                                            <i class="fa fa-plus-circle"></i><span class="text"> <?= lang('add_sale'); ?></span>
                                        </a>
                                    </li>
                                    <?php }
                                   
                                    if ($GP['sales-gift_cards']) { ?>
                                    <li id="sales_gift_cards">
                                        <a class="" href="<?= site_url('sales/gift_cards'); ?>">
                                            <i class="fa fa-gift"></i><span class="text"> <?= lang('gift_cards'); ?></span>
                                        </a>
                                    </li>
                                    <li id="sales_gift_cards_qua">
                                        <a class="" href="<?= site_url('sales/gift_cards_qua'); ?>">
                                            <i class="fa fa-gift"></i>
                                            <span class="text"> <?= lang('Đổi điểm quà tặng'); ?></span>
                                        </a>
                                    </li>
                                    <?php } ?>
                                </ul>
                            </li>
                            <?php } ?>
							<?php if ($GP['sales-deliveries']) { ?>
                           
							<li class="mm_doitac">
                                <a class="dropdown-toggle" href="#">
                                    <i class="menu-icon fa fa-truck"></i>
                                    <span class="menu-text text"> Giao Nhận </span> 
                                   	<b class="arrow fa fa-angle-right"></b>
                                </a>
                                <ul class="submenu">
                                    <li id="sales_deliveries">
                                        <a class="" href="<?= site_url('sales/deliveries'); ?>">
                                            <i class="fa fa-truck"></i><span class="text"> <?= lang('deliveries'); ?></span>
                                        </a>
                                    </li>
                                    <li id="quotes_add">
                                        <a class="" href="<?= site_url('doitac'); ?>">
                                            <i class="fa fa-place-of-worship"></i>
                                            <span class="text"> Đối tác giao hàng</span>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                            <?php } ?>
							<?php if ($GP['returns-index'] || $GP['returns-add']) { ?>
                           
							<li class="mm_returns">
                                <a class="dropdown-toggle" href="#">
                                    <i class="menu-icon fa fa-random"></i>
                                    <span class="menu-text text"> Thu hồi </span> 
                                   	<b class="arrow fa fa-angle-right"></b>
                                </a>
                                <ul class="submenu">
									<?php if ($GP['returns-index']) { ?>
                                    <li id="quotes_index">
                                        <a class="" href="<?= site_url('returns'); ?>">
                                            <i class="fa fa-random"></i>
                                            <span class="text"> DS thu hồi tiền mặt</span>
                                        </a>
                                    </li>
									<?php } ?>
									<?php if ($GP['returns-add']) { ?>
                                    <li id="quotes_add">
                                        <a class="" href="<?= site_url('returns/add'); ?>">
                                            <i class="fa fa-plus-circle"></i>
                                            <span class="text"> Thêm thu hồi tiền mặt</span>
                                        </a>
                                    </li>
									<?php } ?>
									<?php if ($GP['returns-index']) { ?>
                                    <li id="quotes_index">
                                        <a class="" href="<?= site_url('sales/danhsachthuhoi'); ?>">
                                            <i class="fa fa-random"></i>
                                            <span class="text"> Danh sách thu hồi</span>
                                        </a>
                                    </li>
									<?php } ?>
									<?php if ($GP['returns-add']) { ?>
                                    <li id="quotes_add">
                                        <a class="" href="<?= site_url('sales/addthuhoi'); ?>">
                                            <i class="fa fa-plus-circle"></i>
                                            <span class="text"> Thêm thu hồi</span>
                                        </a>
                                    </li>
									<?php } ?>
                                </ul>
                            </li>
                            <?php } ?>
                            <?php if ($GP['quotes-index'] || $GP['quotes-add']) { ?>
                            <li class="mm_quotes">
                                <a class="dropmenu" href="#">
                                    <i class="menu-icon fa fa-heart-o"></i>
                                    <span class="text"> <?= lang('quotes'); ?> </span> 
                                    <b class="arrow fa fa-angle-right"></b>
                                </a>
                                <ul class="submenu">
                                    <li id="sales_index">
                                        <a class="" href="<?= site_url('quotes'); ?>">
                                            <i class="fa fa-heart-o"></i><span class="text"> <?= lang('list_quotes'); ?></span>
                                        </a>
                                    </li>
                                    <?php if ($GP['quotes-add']) { ?>
                                    <li id="sales_add">
                                        <a class="" href="<?= site_url('quotes/add'); ?>">
                                            <i class="fa fa-plus-circle"></i><span class="text"> <?= lang('add_quote'); ?></span>
                                        </a>
                                    </li>
                                    <?php } ?>
                                </ul>
                            </li>
                            <?php } ?>
							<?php if ($GP['purchases-index'] || $GP['purchases-add']) { ?>
                            <li class="mm_purchases">
                                <a class="dropdown-toggle" href="#">
                                    <i class="menu-icon fa fa-star"></i>
                                    <span class="menu-text"> <?= lang('purchases'); ?> </span>
                                   	<b class="arrow fa fa-angle-right"></b>
                                </a>
                                <ul class="submenu">
								<?php if ($GP['purchases-add']) { ?>
                                    <li id="purchases_index">
                                        <a class="" href="<?= site_url('purchases'); ?>">
                                            <i class="fa fa-star"></i>
                                            <span class="text"> <?= lang('list_purchases'); ?></span>
                                        </a>
                                    </li>
									<li id="purchases_index_trahang">
                                        <a class="" href="<?= site_url('purchases/indextrahang'); ?>">
                                            <i class="fa fa-star"></i>
                                            <span class="text"> <?= lang('DS trả hàng NCC'); ?></span>
                                        </a>
                                    </li>
									<li id="purchases_add">
                                        <a class="" href="<?= site_url('purchases/return_purchase_ncc'); ?>">
                                            <i class="fa fa-random"></i><span class="text"> Trả hàng NCC</span>
                                        </a>
                                    </li>
									<?php
										}	
									if ($GP['purchases-add']) { ?>
                                    <li id="purchases_add">
                                        <a class="" href="<?= site_url('purchases/add'); ?>">
                                            <i class="fa fa-plus-circle"></i>
                                            <span class="text"> <?= lang('add_purchase'); ?></span>
                                        </a>
                                    </li>
                                    <li id="purchases_purchase_by_csv">
                                        <a class="" href="<?= site_url('purchases/purchase_by_csv'); ?>">
                                            <i class="fa fa-plus-circle"></i>
                                            <span class="text"> <?= lang('add_purchase_by_csv'); ?></span>
                                        </a>
                                    </li>
                                    <?php } ?>
                                </ul>
                            </li>
							  <?php } ?>
                            
							<?php if ($GP['thu-index'] || $GP['chi-index']||$GP['deposits']) { ?>
							<li class="mm_chiphi">
                                <a class="dropdown-toggle" href="#">
                                    <i class="menu-icon fa-fw fa fa-dollar"></i>
                                    <span class="menu-text"> Thu - Chi </span> 
                                   <b class="arrow fa fa-angle-right"></b>
                                </a>
                                <ul class="submenu">
									<?php if ($GP['chi-index']) { ?>
                                    <li id="chiphi_expenses">
                                        <a class="" href="<?= site_url('purchases/expenses'); ?>">
                                            <i class="fa fa-dollar"></i>
                                            <span class="text"> <?= lang('list_expenses'); ?></span>
                                        </a>
                                    </li>
									<?php } ?>	
									<?php if ($GP['thu-index']) { ?>
									<li id="purchases_add_chincc">
										<a class="" href="<?= site_url('reports/khoanthu'); ?>">
                                            <i class="fa fa-dollar"></i><span class="text"> Liệt kê khoản thu</span>
                                        </a>
                                    </li>
									<?php } ?>	
									<?php if ($GP['chi-add']) { ?>
                                    <li id="chiphi_add_expense">
                                        <a class="" href="<?= site_url('purchases/add_expense'); ?>" data-toggle="modal" data-target="#myModal">
                                            <i class="fa fa-plus-circle"></i>
                                            <span class="text"> <?= lang('add_expense'); ?></span>
                                        </a>
                                    </li>
									<?php } ?>		
									<?php if ($GP['thu-add']) { ?>
									<li id="purchases_add_phieuthu">
                                        <a class="" href="<?= site_url('purchases/add_phieuthu'); ?>" 
                                            data-toggle="modal" data-target="#myModal">
                                            <i class="fa fa-plus-circle"></i><span class="text"> <?= lang('add_phieuthu'); ?></span>
                                        </a>
                                    </li>
                                    <?php } ?>
                                    <?php if ($GP['deposits']) { ?>
                                    <li id="purchases_add_phieuthu_kh">
                                        <a class="" href="<?= site_url('customers/add_deposit_kh'); ?>" 
                                            data-toggle="modal" data-target="#myModal">
                                            <i class="fa fa-plus-circle"></i><span class="text"> <?= lang('Thêm tiền cọc'); ?></span>
                                        </a>
                                    </li>
									<?php } ?>									
                                </ul>
                            </li>
							
                            <?php } ?>
                           
                            <?php if ($GP['transfers-index'] || $GP['transfers-add']) { ?>
                            <li class="mm_transfers">
                                <a class="dropmenu" href="#">
                                    <i class="menu-icon fa fa-star-o"></i>
                                    <span class="text"> <?= lang('transfers'); ?> </span> 
                                    <b class="arrow fa fa-angle-right"></b>
                                </a>
                                <ul class="submenu">
                                    <li id="transfers_index">
                                        <a class="" href="<?= site_url('transfers'); ?>">
                                            <i class="fa fa-star-o"></i><span class="text"> <?= lang('list_transfers'); ?></span>
                                        </a>
                                    </li>
                                    <?php if ($GP['transfers-add']) { ?>
                                    <li id="transfers_add">
                                        <a class="" href="<?= site_url('transfers/add'); ?>">
                                            <i class="fa fa-plus-circle"></i><span class="text"> <?= lang('add_transfer'); ?></span>
                                        </a>
                                    </li>
                                    <?php } ?>
                                </ul>
                            </li>
                            <?php } ?>

                            <?php if ($GP['customers-index'] || $GP['customers-add'] || $GP['suppliers-index'] || $GP['suppliers-add']||$GP['deposits']) { ?>
                            <li class="mm_auth mm_customers mm_suppliers mm_billers">
                                <a class="dropmenu" href="#">
                                    <i class="menu-icon fa fa-users"></i>
                                    <span class="text"> <?= lang('people'); ?> </span> 
                                    <b class="arrow fa fa-angle-right"></b>
                                </a>
                                <ul class="submenu">
                                    <?php if ($GP['customers-index']) { ?>
                                    <li id="customers_index">
                                        <a class="" href="<?= site_url('customers'); ?>">
                                            <i class="fa fa-users"></i><span class="text"> <?= lang('list_customers'); ?></span>
                                        </a>
                                    </li>
                                    <?php }
                                    if ($GP['customers-add']) { ?>
                                    <li id="customers_index">
                                        <a class="" href="<?= site_url('customers/add'); ?>" data-toggle="modal" data-target="#myModal">
                                            <i class="fa fa-plus-circle"></i><span class="text"> <?= lang('add_customer'); ?></span>
                                        </a>
                                    </li>
                                    <?php }
                                    if ($GP['suppliers-index']) { ?>
                                    <li id="suppliers_index">
                                        <a class="" href="<?= site_url('suppliers'); ?>">
                                            <i class="fa fa-users"></i><span class="text"> <?= lang('list_suppliers'); ?></span>
                                        </a>
                                    </li>
                                    <?php }
                                    if ($GP['suppliers-add']) { ?>
                                    <li id="suppliers_index">
                                        <a class="" href="<?= site_url('suppliers/add'); ?>" data-toggle="modal" data-target="#myModal">
                                            <i class="fa fa-plus-circle"></i><span class="text"> <?= lang('add_supplier'); ?></span>
                                        </a>
                                    </li>
                                    <?php }
                                    if ($GP['deposits']) { ?>
                                    <li id="deposit_index">
                                        <a class="" href="<?= site_url('customers/listdeposit'); ?>">
                                            <i class="fa fa-list-ol"></i><span class="text"> <?= lang('Danh sách đặt cọc'); ?></span>
                                        </a>
                                    </li>
                                    <?php } ?>
                                </ul>
                            </li>
                            <?php } ?>

                            <?php if ($GP['reports-quantity_alerts'] || $GP['reports-expiry_alerts'] || $GP['reports-products'] || $GP['reports-monthly_sales'] || $GP['reports-sales'] || $GP['reports-payments'] || $GP['reports-purchases'] || $GP['reports-customers'] || $GP['reports-suppliers'] || $GP['reports-expenses']) { ?>
                            <li class="mm_reports">
                                <a class="dropmenu" href="#">
                                    <i class="menu-icon fa fa-bar-chart-o"></i>
                                    <span class="text"> <?= lang('reports'); ?> </span> 
                                    <b class="arrow fa fa-angle-right"></b>
                                </a>
                                <ul class="submenu">
                                    <?php if ($GP['reports-quantity_alerts']) { ?>
                                    <li id="reports_quantity_alerts">
                                        <a href="<?= site_url('reports/quantity_alerts') ?>">
                                            <i class="fa fa-bar-chart-o"></i><span class="text"> <?= lang('product_quantity_alerts'); ?></span>
                                        </a>
                                    </li>
                                    <?php }
                                    if ($GP['reports-expiry_alerts']) { ?>
                                    <?php if ($Settings->product_expiry) { ?>
                                    <li id="reports_expiry_alerts">
                                        <a href="<?= site_url('reports/expiry_alerts') ?>">
                                            <i class="fa fa-bar-chart-o"></i><span class="text"> <?= lang('product_expiry_alerts'); ?></span>
                                        </a>
                                    </li>
                                    <?php } ?>
                                    <?php }
                                    if ($GP['reports-products']) { ?>
                                    <li id="reports_products">
                                        <a href="<?= site_url('reports/products') ?>">
                                            <i class="fa fa-filter"></i><span class="text"> <?= lang('Báo cáo xuất nhập tồn'); ?></span>
                                        </a>
                                    </li>
                                    	
                                     <?php 
                                    if ($GP['purchases-index']) { ?>	
                                    <li id="reports_adjustments">
                                        <a href="<?= site_url('reports/adjustments') ?>">
                                            <i class="fa fa-barcode"></i><span class="text"> <?= lang('adjustments_report'); ?></span>
                                        </a>
                                    </li>
                                    <li id="reports_categories">
                                        <a href="<?= site_url('reports/categories') ?>">
                                            <i class="fa fa-folder-open"></i><span class="text"> <?= lang('categories_report'); ?></span>
                                        </a>
                                    </li>
									<li id="reports_nhom">
                                        <a href="<?= site_url('reports/nhom') ?>">
                                            <i class="fa fa-folder-open"></i><span class="text"> <?= lang('Báo cáo nhóm sản phẩm'); ?></span>
                                        </a>
                                    </li>
                                    <li id="reports_brands">
                                        <a href="<?= site_url('reports/brands') ?>">
                                            <i class="fa fa-cubes"></i><span class="text"> <?= lang('brands_report'); ?></span>
                                        </a>
                                    </li>
									<li id="reports_brands">
                                        <a href="<?= site_url('reports/xuatxu') ?>">
                                            <i class="fa fa-cubes"></i><span class="text"> Báo cáo xuất xứ</span>
                                        </a>
                                    </li>
                                    <?php
                                	}
                                     }
                                    if ($GP['reports-daily_sales']) { ?>
                                    <li id="reports_daily_sales">
                                        <a href="<?= site_url('reports/daily_sales') ?>">
                                            <i class="fa fa-calendar-o"></i><span class="text"> <?= lang('daily_sales'); ?></span>
                                        </a>
                                    </li>
                                    <?php }
                                    if ($GP['reports-monthly_sales']) { ?>
                                    <li id="reports_monthly_sales">
                                        <a href="<?= site_url('reports/monthly_sales') ?>">
                                            <i class="fa fa-calendar-o"></i><span class="text"> <?= lang('monthly_sales'); ?></span>
                                        </a>
                                    </li>
                                    <?php }
                                    if ($GP['reports-sales']) { ?>
                                    <li id="reports_sales">
                                        <a href="<?= site_url('reports/sales') ?>">
                                            <i class="fa fa-heart"></i><span class="text"> <?= lang('sales_report'); ?></span>
                                        </a>
                                    </li>
                                    <?php }
                                    if ($GP['reports-payments']) { ?>
                                    <li id="reports_payments">
                                        <a href="<?= site_url('reports/payments') ?>">
                                            <i class="fa fa-money"></i><span class="text"> <?= lang('payments_report'); ?></span>
                                        </a>
                                    </li>
                                    <?php }
                                    if ($GP['reports-daily_purchases']) { ?>
                                    <li id="reports_daily_purchases">
                                        <a href="<?= site_url('reports/daily_purchases') ?>">
                                            <i class="fa fa-calendar-o"></i><span class="text"> <?= lang('daily_purchases'); ?></span>
                                        </a>
                                    </li>
                                    <?php }
                                    if ($GP['reports-monthly_purchases']) { ?>
                                    <li id="reports_monthly_purchases">
                                        <a href="<?= site_url('reports/monthly_purchases') ?>">
                                            <i class="fa fa-calendar-o"></i><span class="text"> <?= lang('monthly_purchases'); ?></span>
                                        </a>
                                    </li>
                                    <?php }
                                    if ($GP['reports-purchases']) { ?>
                                    <li id="reports_purchases">
                                        <a href="<?= site_url('reports/purchases') ?>">
                                            <i class="fa fa-star"></i><span class="text"> <?= lang('purchases_report'); ?></span>
                                        </a>
                                    </li>
									<li id="reports_trahangncc">
                                        <a href="<?= site_url('reports/purchases_return') ?>">
                                            <i class="fa fa-star"></i><span class="text"> Báo cáo trả hàng NCC</span>
                                        </a>
                                    </li>
									<li id="reports_trahangkhach">
                                        <a href="<?= site_url('returns/baocao') ?>">
                                            <i class="fa fa-random"></i><span class="text"> Báo cáo trả hàng tiền</span>
                                        </a>
                                    </li>
									<li id="reports_trahangkhach">
                                        <a href="<?= site_url('reports/baocaotralhson') ?>">
                                            <i class="fa fa-random"></i><span class="text"> Báo cáo trả hàng công nợ</span>
                                        </a>
                                    </li>
                                    <?php }
                                    if ($GP['reports-expenses']) { ?>
                                	<?php 

                                	if ($GP['purchases-expenses']){
                                	?>
                                    <li id="reports_expenses">
                                        <a href="<?= site_url('reports/expenses') ?>">
                                            <i class="fa fa-dollar"></i><span class="text"> <?= lang('expenses_report'); ?></span>
                                        </a>
                                    </li>
                                	<?php } ?>
									<li id="reports_thu">
                                        <a href="<?= site_url('reports/baocaothu') ?>">
                                            <i class="fa fa-dollar"></i><span class="text"> <?= lang('Báo cáo khoản thu'); ?></span>
                                        </a>
                                    </li>
                                    <?php 

                                	if ($GP['transfers-index']){
                                	?>
									<li id="reports_chuyen">
                                        <a href="<?= site_url('transfers/baocaochuyen') ?>">
                                            <i class="fa fa-random"></i><span class="text"> <?= lang('Báo cáo chuyển kho'); ?></span>
                                        </a>
                                    </li>
                                    <?php
                                		}
                                     }
                                    if ($GP['reports-customers']) { ?>
                                    <li id="reports_customer_report">
                                        <a href="<?= site_url('reports/customers') ?>">
                                            <i class="fa fa-users"></i><span class="text"> <?= lang('customers_report'); ?></span>
                                        </a>
                                    </li>
                                    <?php }
                                    if ($GP['reports-suppliers']) { ?>
                                    <li id="reports_supplier_report">
                                        <a href="<?= site_url('reports/suppliers') ?>">
                                            <i class="fa fa-users"></i><span class="text"> <?= lang('suppliers_report'); ?></span>
                                        </a>
                                    </li>
									<li id="reports_doitac_report">
                                        <a href="<?= site_url('reports/doitac') ?>">
                                            <i class="fa fa-truck"></i><span class="text"> <?= lang('Đối tác giao hàng'); ?></span>
                                        </a>
                                    </li>
                                    <?php } ?>
                                </ul>
                            </li>
                            <?php } ?>

                        <?php } ?>	
				</ul>
				<div class="sidebar-toggle sidebar-collapse" id="sidebar-collapse">
					<i id="sidebar-toggle-icon" class="ace-icon fa fa-angle-double-left ace-save-state" data-icon1="ace-icon fa fa-angle-double-left" data-icon2="ace-icon fa fa-angle-double-right"></i>
				</div>
			</div>

      <div class="main-content">
			<div class="main-content-inner ">
				<div class="breadcrumbs ace-save-state hidden-xs no-print" id="breadcrumbs">
					<ul class="breadcrumb">
						
						 <?php
                            foreach ($bc as $b) {
                                if ($b['link'] === '#') {
                                    echo '<li class="active">' . $b['page'] . '</li>';
                                } else {
                                    echo '<li><a href="' . $b['link'] . '">' . $b['page'] . '</a></li>';
                                }
                            }
                            ?>
							
						<li class="active"> <?= lang('your_ip') . ' ' . $ip_address . " <span class='hidden-sm'>( " . lang('last_login_at') . ": " . date($dateFormats['php_ldate'], $this->session->userdata('old_last_login')) . " " . ($this->session->userdata('last_ip') != $ip_address ? lang('ip:') . ' ' . $this->session->userdata('last_ip') : '') . " )</span>" ?></li>
					</ul><!-- /.breadcrumb -->

					<div class="nav-search" id="nav-search">
						<form class="form-search">
							<span class="input-icon">
								<input type="text" placeholder="Tìm ..." class="nav-search-input" id="nav-search-input" autocomplete="off" />
								<i class="ace-icon fa fa-search nav-search-icon"></i>
							</span>
						</form>
					</div><!-- /.nav-search -->
			</div>
		<div class="page-content">         	   
                <div class="row no-padding-home-lhson">
                    <div class="col-lg-12 no-padding-home-lhson">
                        <?php if ($message) { ?>
                            <div class="alert alert-success">
                                <button data-dismiss="alert" class="close" type="button">×</button>
                                <?= $message; ?>
                            </div>
                        <?php } ?>
                        <?php if ($error) { ?>
                            <div class="alert alert-danger">
                                <button data-dismiss="alert" class="close" type="button">×</button>
                                <?= $error; ?>
                            </div>
                        <?php } ?>
                        <?php if ($warning) { ?>
                            <div class="alert alert-warning">
                                <button data-dismiss="alert" class="close" type="button">×</button>
                                <?= $warning; ?>
                            </div>
                        <?php } ?>
                        <?php
                        if ($info) {
                            foreach ($info as $n) {
                                if (!$this->session->userdata('hidden' . $n->id)) {
                                    ?>
                                    <div class="alert alert-info">
                                        <a href="#" id="<?= $n->id ?>" class="close hideComment external"
                                           data-dismiss="alert">&times;</a>
                                        <?= $n->comment; ?>
                                    </div>
                                <?php }
                            }
                        } ?>
                        <div class="alerts-con"></div>
