<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<style>
fieldset.scheduler-border {
    float: left;
    width: 100%;
}
.col-lg-12.no-padding-home-lhson {width: 100%;}
input#is_active_tmdt {
    float: left;
    margin: 0px;
    margin-right: 5px;
}
</style>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-cog"></i><?= lang('Liên kết sàn thương mại điên tử'); ?></h2>

          </div>
    <div class="box-content">
        <div class="row">
            <div class="col-md-12">
                <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
                echo form_open_multipart("system_settings/indextmdt", $attrib);
                ?>
                <div class="row">
                    <div class="col-md-12">                        
                        <fieldset class="scheduler-border" id="tmdt">
                        	<br/>
                            <div class="form-group">
								<input type="checkbox" class="checkbox" value="1" name="is_active_tmdt" id="is_active_tmdt" <?= $Settings->is_active_tmdt==1 ? 'checked="checked"' : ''; ?>>
								<label for="is_active_tmdt" class="padding05">
									<?= lang('Cho phép liên kết với sàn TMĐT'); ?>
								</label>
							</div>
                    	</fieldset>
                    	<fieldset class="scheduler-border" id="shopinfo" style="display: none;">
                            <legend class="scheduler-border"><?= lang('Cấu hình thông tin SHOP') ?></legend> 
                           	<div class="col-md-6">
                           		<div class="form-group"> 
	                                <?= lang("Tên shop", "shop_name"); ?>
	                                <?= form_input('shop_name', ($Settings->shop_name==''?$Settings->site_name:$Settings->shop_name), 'class="form-control tip" id="shop_name"  required="required"'); ?>
	                            </div>
		                        <div class="form-group">
		                        	<?= lang("Tên đăng nhập", "shop_username"); ?>
	                                <?= form_input('shop_username', ($Settings->shop_username==''?$this->session->userdata('username'):$Settings->shop_username), 'class="form-control tip" id="shop_username"  required="required"'); ?>                                
		                        </div>
		                        
		                        <div class="form-group">
		                        	<?= lang("Email", "shop_email"); ?>
	                                <?= form_input('shop_email',($Settings->shop_email==''?$Settings->default_email:$Settings->shop_email), 'class="form-control tip" id="shop_email"  required="required"'); ?>  
		                        </div>
		                        <div class="form-group">
		                        	<?= lang("Điện thoại", "shop_phone"); ?>
	                                <?= form_input('shop_phone',$Settings->shop_phone, 'class="form-control tip" id="shop_phone"  required="required" placeholder=" Điện thoại shop" '); ?>  
		                        </div>
		                        <div class="form-group">
		                        	<?= lang("Mật khẩu", "shop_password"); ?>
	                                <?= form_password('shop_password', '', ' autocomplete="new-password" class="form-control tip" id="shop_password"'); ?>  
		                        </div>
                           	</div>
                           	<div class="col-md-6">
                           		<div class="form-group">
									<label class="control-label" for="tinh_id"><?php echo $this->lang->line("Tỉnh / TP"); ?></label>
									<?php
									$tinh_ok=array(''=>'Chọn tỉnh / TP');
									if (!empty($list_tinh)) {
										foreach ($list_tinh as $item) {
											$tinh_ok[$item->id] = $item->name;
										}
									}
									
									echo form_dropdown('tinh_id', $tinh_ok, $Settings->tinh_id, 'class="form-control select" onchange="loadQuanHuyen(this)" required id="tinh_id" style="width:100%;"');
									?>
								</div>
								 <div class="form-group">
									<label class="control-label" for="quan_id"><?php echo $this->lang->line("Quận / Huyện"); ?></label>
									<?php
									$quan_ok=array();
									if (!empty($list_quan)) {
										foreach ($list_quan as $quan) {
											$quan_ok[$quan->id] = $quan->name;
										}
									}
									echo form_dropdown('quan_id', $quan_ok, $Settings->quan_id, 'onchange="loadphuongxa(this)" class="form-control select" required id="quan_id" style="width:100%;"');
									?>
								</div>
								<div class="form-group">
									<label class="control-label" for="phuong_id"><?php echo $this->lang->line("Phường / Xã"); ?></label>
									<?php
									$phuong_ok=array();

									if (!empty($list_phuong)) {
										foreach ($list_phuong as $phuong) {
											$phuong_ok[$phuong->id] = $phuong->name;
										}
									}
									echo form_dropdown('phuong_id', $phuong_ok, $Settings->phuong_id, 'class="form-control select" id="phuong_id" required style="width:100%;"');
									?>
								</div>

                           		<div class="form-group">
		                        	<?= lang("Địa chỉ shop", "shop_addr"); ?>
	                                <?= form_input('shop_addr',$Settings->shop_addr, ' placeholder=" Địa chỉ shop" class="form-control tip" id="shop_addr"'); ?>  
		                        </div>
		                        <div class="form-group">
		                        	<?= lang("Giới thiệu shop", "shop_info"); ?>
	                                <?= form_input('shop_info',$Settings->shop_info, ' placeholder=" Giới thiệu shop" class="form-control tip" id="shop_info"'); ?>  
		                        </div>
                           	</div>  
                    	</fieldset>
                </div>
            </div>
            <div style="clear: both; height: 10px;"></div>
            
            <div class="col-md-12">
                <div class="form-group">
                    <div class="controls" id="btn-lienket">
                        <?= form_submit('update_settings', lang("Lưu cài đặt"), 'class="btn btn-primary"'); ?>
                        <?php 
                        	if ($Settings->is_active_tmdt==1) {
                        		?>
                        		<a href="<?=SITE_TMDT_URL;?>" target="_blank" class="btn btn-default"> <i class="fa fa-globe"></i> Truy cập sàn TMĐT <?=SITE_TMDT_URL;?></a>
                        		
                        		<a href="system_settings/syn_api_categoris" class="btn btn-default"> <i class="fa fa-folder-open"></i> Đồng bộ danh mục</a>

                        		<a href="system_settings/sysnapitmdt" target="_blank" class="btn btn-default"> <i class="fa fa-refresh"></i> Đồng bộ sản phẩm lên sàn</a>
                        		<?php
                        	}
                        ?>
                    </div>
                </div>
            </div>
            <?= form_close(); ?>
			</div>
		</div>
	</div>
	<?php

	$v = "";
	
	if ($this->input->post('type_history')) {
	    $v .= "&type=" . $this->input->post('type_history');
	}	
	if ($this->input->post('start_date')) {
	    $v .= "&start_date=" . $this->input->post('start_date');
	}
	if ($this->input->post('end_date')) {
	    $v .= "&end_date=" . $this->input->post('end_date');
	}

	?>
    <ul id="myTab" class="nav nav-tabs">
        <li class=""><a href="#products" class="tab-grey">Lịch sử đồng bộ sản phẩm</a></li>
        <li class=""><a href="#orders" class="tab-grey">Lịch sử đồng bộ đơn hàng</a></li>
    </ul>
	<div class="tab-content">
	    <div id="products" class="tab-pane fade in">
	    	<script>
			    $(document).ready(function () {
			        oTable = $('#tblHisPrd').dataTable({
			            "aaSorting": [[0, "desc"]],
			            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
			            "iDisplayLength": <?= $Settings->rows_per_page ?>,
			            'bProcessing': true, 'bServerSide': true,
			            'sAjaxSource': '<?= site_url('reports/getHistoryApiProducts/?v=1' . $v) ?>',
			            'fnServerData': function (sSource, aoData, fnCallback) {
			                aoData.push({
			                    "name": "<?= $this->security->get_csrf_token_name() ?>",
			                    "value": "<?= $this->security->get_csrf_hash() ?>"
			                });
			                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
			            },
			            'fnRowCallback': function (nRow, aData, iDisplayIndex) {
			                nRow.id = aData[1]; 
			                nRow.className =  "history_product_link warning";
			                return nRow;
			            },
			            "aoColumns": [{"mRender": fld}, null,null,{"mRender": formatQuantity}, {"mRender": currencyFormat},null],
			            "fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {
			               
			            }
			        }).fnSetFilteringDelay().dtFilter([], "footer");
			    });
			</script>

    		<div class="table-responsive">
                <table id="tblHisPrd"
                       class="table table-bordered table-hover table-striped table-condensed reports-table">
                    <thead>
	                    <tr>
	                        <th class="col-xs-1"><?= lang("date"); ?></th>
	                        <th class="col-xs-1"><?= lang("IDSP"); ?></th>
	                        <th ><?= lang("Tên sản phẩm"); ?></th>
							<th class="col-xs-1"><?= lang("Tồn kho"); ?></th>
							<th class="col-xs-1"><?= lang("Giá bán"); ?></th>							
	                        <th class="col-xs-2"><?= lang("Loại"); ?></th>
	                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td colspan="6" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
                    </tr>
                    </tbody>
                    <tfoot class="dtFilter">
                    <tr class="active">
                        <th class="col-xs-1"></th>
                        <th class="col-xs-1"><?= lang("IDSP"); ?></th>
                        <th ><?= lang("Tên sản phẩm"); ?></th>
						<th class="col-xs-1"><?= lang("Tồn kho"); ?></th>
						<th class="col-xs-1"><?= lang("Giá bán"); ?></th>							
                        <th class="col-xs-2"><?= lang("Loại"); ?></th>
                    </tr>
                    </tfoot>
                </table>
            </div>
	    </div>
	    <div id="orders" class="tab-pane">
	    	<script>
			    $(document).ready(function () {
			        oTable = $('#tblHisOrder').dataTable({
			            "aaSorting": [[0, "desc"]],
			            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
			            "iDisplayLength": <?= $Settings->rows_per_page ?>,
			            'bProcessing': true, 'bServerSide': true,
			            'sAjaxSource': '<?= site_url('reports/getHistoryApiOrders/?v=1') ?>',
			            'fnServerData': function (sSource, aoData, fnCallback) {
			                aoData.push({
			                    "name": "<?= $this->security->get_csrf_token_name() ?>",
			                    "value": "<?= $this->security->get_csrf_hash() ?>"
			                });
			                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
			            },
			            'fnRowCallback': function (nRow, aData, iDisplayIndex) {
			                nRow.id = aData[1]; 
			                nRow.className =  "history_order_link warning";
			                return nRow;
			            },
			            "aoColumns": [{"mRender": fld}, null,null,null,null, {"mRender": currencyFormat},null],
			            "fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {
			               
			            }
			        }).fnSetFilteringDelay().dtFilter([], "footer");
			    });
			</script>

    		<div class="table-responsive">
                <table id="tblHisOrder"
                       class="table table-bordered table-hover table-striped table-condensed reports-table">
                    <thead>
	                    <tr>
	                        <th class="col-xs-1"><?= lang("date"); ?></th>
	                        <th class="col-xs-1"><?= lang("Order ID"); ?></th>
	                        <th ><?= lang("Mã hóa đơn"); ?></th>
							<th class="col-xs-1"><?= lang("API ID"); ?></th>
							<th class="col-xs-2"><?= lang("Khách hàng"); ?></th>							
	                        <th class="col-xs-1"><?= lang("Tổng tiền"); ?></th>
	                        <th class="col-xs-2"><?= lang("Loại"); ?></th>
	                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td colspan="7" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
                    </tr>
                    </tbody>
                    <tfoot class="dtFilter">
                    <tr class="active">
                        <th class="col-xs-1"><?= lang("date"); ?></th>
                        <th class="col-xs-1"><?= lang("Order ID"); ?></th>
                        <th ><?= lang("Mã hóa đơn"); ?></th>
						<th class="col-xs-1"><?= lang("API ID"); ?></th>
						<th class="col-xs-2"><?= lang("Khách hàng"); ?></th>							
                        <th class="col-xs-1"><?= lang("Tổng tiền"); ?></th>
                        <th class="col-xs-2"><?= lang("Loại"); ?></th>
                    </tr>
                    </tfoot>
                </table>
            </div>
	    </div>
	</div>
</div>
<script>      
	$(document).ready(function () { 
		
	    $('#is_active_tmdt').on('ifChecked', function (e) {
	    	$('#shopinfo').slideDown();
	    	$("#btn-lienket a").slideDown();
        });
         $('#is_active_tmdt').on('ifUnchecked', function () {
            $('#shopinfo').slideUp();
            $("#btn-lienket a").slideUp();
        });

	    if (1==<?=$Settings->is_active_tmdt?>) {
	    	$("#is_active_tmdt").attr('checked','checked');
	    	$('#shopinfo').slideDown();
	    }
	    
    });
    function loadQuanHuyen(obj){
		var tinh=$(obj).val();
		if(tinh!=''){			
			 $.ajax({
				url: site.base_url + "system_settings/get_list_district_ajax/"+tinh,
				type: 'get',
				dataType: 'json',
				contentType: 'application/json',
				success: function (ketqua) {
					
					if(ketqua){						
						$('#quan_id').empty();
						
						var quan_id = $('#quan_id');
						for(var i=0;i<ketqua.length;i++){
							 var option = new Option(ketqua[i].name, ketqua[i].id, true, true);
							quan_id.append(option);
							if(i==ketqua.length-1){
								quan_id.trigger('change');
							}
						}
					}
				}
				
			 });
		}
	}

	function loadphuongxa(obj){
		var quan=$(obj).val();
		if (quan==undefined) {
			quan=$("quan_id option:selected").val();
		}
		if(quan!=''){
			$('#phuong_id').empty();	
			 $.ajax({
				url: site.base_url + "system_settings/get_list_phuongxa_ajax/"+quan,
				type: 'get',
				dataType: 'json',
				contentType: 'application/json',
				success: function (ketqua) {
					
					if(ketqua){						
						var check=true;					
						var phuong_id = $('#phuong_id');
						for(var i=0;i<ketqua.length;i++){
							 var option = new Option(ketqua[i].name, ketqua[i].id, true, true);
							
							phuong_id.append(option);
							if(ketqua[i].id=='<?=$Settings->phuong_id;?>'){
								phuong_id.trigger('change');
								check=false;
							}else if(check){
								phuong_id.trigger('change');	
							}
						}
					}
				}
				
			 });
		}
	}
</script>
