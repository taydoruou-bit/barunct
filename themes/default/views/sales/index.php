<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script>
    $(document).ready(function () {
        oTable = $('#SLData').dataTable({
            "aaSorting": [[0, "asc"], [1, "desc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?=lang('all')?>"]],
            "iDisplayLength": <?=$Settings->rows_per_page?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?=site_url('sales/getSales' . ($warehouse_id ? '/' . $warehouse_id : ''))?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?=$this->security->get_csrf_token_name()?>",
                    "value": "<?=$this->security->get_csrf_hash()?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            'fnRowCallback': function (nRow, aData, iDisplayIndex) {
                var oSettings = oTable.fnSettings();
                //$("td:first", nRow).html(oSettings._iDisplayStart+iDisplayIndex +1);
                nRow.id = aData[0];
                nRow.setAttribute('data-return-id', aData[13]);
                nRow.className = "invoice_link re"+aData[13];
                //if(aData[7] > aData[9]){ nRow.className = "product_link warning"; } else { nRow.className = "product_link"; }
                return nRow;
            },
            "aoColumns": [{"bSortable": false,"mRender": checkbox}, {"mRender": fld}, null,null,null, null, null, {"mRender": row_status}, {"mRender": currencyFormat}, {"mRender": currencyFormat}, {"mRender": currencyFormat}, {"mRender": pay_status}, {"bSortable": false,"mRender": attachment}, {"bVisible": false}, {"bSortable": false}],
            "fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {
                var gtotal = 0, paid = 0, balance = 0;
                for (var i = 0; i < aaData.length; i++) {
                    gtotal += parseFloat(aaData[aiDisplay[i]][7]);
                    paid += parseFloat(aaData[aiDisplay[i]][8]);
                    balance += parseFloat(aaData[aiDisplay[i]][9]);
                }
                var nCells = nRow.getElementsByTagName('th');
                nCells[7].innerHTML = currencyFormat(parseFloat(gtotal));
                nCells[8].innerHTML = currencyFormat(parseFloat(paid));
                nCells[9].innerHTML = currencyFormat(parseFloat(balance));
            }
        }).fnSetFilteringDelay().dtFilter([
            {column_number: 1, filter_default_label: "[<?=lang('date');?> (yyyy-mm-dd)]", filter_type: "text", data: []},
            {column_number: 2, filter_default_label: "[<?=lang('reference_no');?>]", filter_type: "text", data: []},
            {column_number: 3, filter_default_label: "[<?=lang('ĐVGH');?>]", filter_type: "text", data: []},
			{column_number: 4, filter_default_label: "[<?=lang('Kho');?>]", filter_type: "text", data: []},
			{column_number: 5, filter_default_label: "[<?=lang('biller');?>]", filter_type: "text", data: []},
            {column_number: 6, filter_default_label: "[<?=lang('customer');?>]", filter_type: "text", data: []},
            {column_number: 7, filter_default_label: "[<?=lang('sale_status');?>]", filter_type: "text", data: []},
            {column_number: 11, filter_default_label: "[<?=lang('payment_status');?>]", filter_type: "text", data: []},
        ], "footer");

        if (localStorage.getItem('remove_slls')) {
            if (localStorage.getItem('slitems')) {
                localStorage.removeItem('slitems');
            }
            if (localStorage.getItem('sldiscount')) {
                localStorage.removeItem('sldiscount');
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
            if (localStorage.getItem('slpayment_term')) {
                localStorage.removeItem('slpayment_term');
            }
            localStorage.removeItem('remove_slls');
        }

        <?php if ($this->session->userdata('remove_slls')) {?>
        if (localStorage.getItem('slitems')) {
            localStorage.removeItem('slitems');
        }
		if (localStorage.getItem('slitems_tra')) {
            localStorage.removeItem('slitems_tra');
        }
        if (localStorage.getItem('sldiscount_tra')) {
            localStorage.removeItem('sldiscount_tra');
        }
		
		if (localStorage.getItem('sldiscount')) {
            localStorage.removeItem('sldiscount');
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
        if (localStorage.getItem('slpayment_term')) {
            localStorage.removeItem('slpayment_term');
        }
        <?php $this->sma->unset_data('remove_slls');}
        ?>

        $(document).on('click', '.sledit', function (e) {
            if (localStorage.getItem('slitems')) {
                e.preventDefault();
                var href = $(this).attr('href');
                bootbox.confirm("<?=lang('you_will_loss_sale_data')?>", function (result) {
                    if (result) {
                        window.location.href = href;
                    }
                });
            }
        });
		var _lockq=jQuery("#locketqua").html();
		jQuery("#locketqua").remove();
		jQuery("#dtFilter-filter-wrapper--SLData-9").prepend(_lockq);
		
		jQuery("#_lockq").on("change", function(value){
			var This = jQuery(this);
			var selectedD = jQuery(this).val();
			jQuery("#dtFilter-filter--SLData-9").val(selectedD);
			jQuery("#dtFilter-filter--SLData-9").trigger("keyup");
		});
    });
	function trahangfunc(){
		var val=0;
		var count=0;
		jQuery("#SLData tbody tr").each(function(){
			var chk=jQuery(this).find("input[type='checkbox']:checked");						
			if(chk.length>0){
				val=jQuery(chk).val();
				count=count+1;
			}
		});
		if(count==0){
			alert("Bạn chưa chọn đơn hàng cần trả hàng");
			return false;
		}else if(count>1){
			alert("Chỉ trả 1 đơn hàng cùng lúc, bạn đang chọn "+count);
			return false;
		}else{
			if(val==0){
				alert("Không nhận được id đơn hàng");
				return false;
			}else{
				window.location="<?php echo site_url('sales/return_sale/');?>"+val;
			}
		}
	}
	function inhoadon(){
		var val=0;
		var count=0;
		jQuery("#SLData tbody tr").each(function(){
			var chk=jQuery(this).find("input[type='checkbox']:checked");						
			if(chk.length>0){
				val=jQuery(chk).val();
				count=count+1;
			}
		});
		if(count==0){
			alert("Bạn chưa chọn đơn hàng cần in");
			return false;
		}else if(count>1){
			alert("Chỉ in 1 đơn hàng cùng lúc, bạn đang chọn "+count);
			return false;
		}else{
			if(val==0){
				alert("Không nhận được id đơn hàng");
				return false;
			}else{
				jQuery("#SLData_wrapper a[href='<?php echo base_url();?>sales/printsalelhson/"+val+"']").trigger("click");
			}
		}
	}
	
	function giaohang(){
		var val=0;
		var count=0;
		jQuery("#SLData tbody tr").each(function(){
			var chk=jQuery(this).find("input[type='checkbox']:checked");						
			if(chk.length>0){
				val=jQuery(chk).val();
				count=count+1;
			}
		});
		if(count==0){
			alert("Bạn chưa chọn đơn hàng cần giao");
			return false;
		}else if(count>1){
			alert("Chỉ giao 1 đơn hàng cùng lúc, bạn đang chọn "+count);
			return false;
		}else{
			if(val==0){
				alert("Không nhận được id đơn hàng");
				return false;
			}else{
				jQuery("#SLData_wrapper a[href='<?php echo base_url();?>sales/add_delivery/"+val+"']").trigger("click");
			}
		}
	}
</script>

<?php if ($Owner || $GP['bulk_actions']) {
	    echo form_open('sales/sale_actions', 'id="action-form"');
	}
?>
<div class="box">
    <div class="box-header no-print">
        <h2 class="blue"><i
                class="fa-fw fa fa-heart"></i><?=lang('sales') . ' (' . ($warehouse_id ? $warehouse->name : lang('all_warehouses')) . ')';?>
        </h2>

        <div class="box-icon">
            <ul class="btn-tasks">
                
                <?php if (!empty($warehouses)) {
                    ?>
                    <li class="dropdown">
                        <a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-building-o tip" data-placement="left" title="<?=lang("warehouses")?>"></i></a>
                        <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                            <li><a href="<?=site_url('sales')?>"><i class="fa fa-building-o"></i> <?=lang('all_warehouses')?></a></li>
                            <li class="divider"></li>
                            <?php
                            	foreach ($warehouses as $warehouse) {
                            	        echo '<li><a href="' . site_url('sales/' . $warehouse->id) . '"><i class="fa fa-building"></i>' . $warehouse->name . '</a></li>';
                            	    }
                                ?>
                        </ul>
                    </li>
                <?php }
                ?>
            </ul>
        </div>
		<div class="main-task-lhson">
			<a class="btn btn-primary btncls" href="<?=site_url('sales/add')?>">
				<i class="fa fa-plus-circle"></i> Thêm đơn hàng
			</a>
			<a class="btn btn-primary btncls" href="#" id="excel" data-action="export_excel">
				<i class="fa fa-file-excel-o"></i> Excel
			</a>
			<a class="btn btn-primary btncls" href="#" id="pdf" data-action="export_pdf">
				<i class="fa fa-file-pdf-o"></i> Pdf
			</a>
			<a class="btn btn-primary btncls" href="#" id="combine" data-action="combine">
				<i class="fa fa-file-pdf-o"></i> <?=lang('combine_to_pdf')?>
			</a>
			<a class="btn btn-primary btncls bpo" href="#"
				title="<b><?=lang("delete_sales")?></b>"
				data-content="<p><?=lang('r_u_sure')?></p><button type='button' class='btn btn-danger' id='delete' data-action='delete'><?=lang('i_m_sure')?></a> <button class='btn bpo-close'><?=lang('no')?></button>"
				data-html="true" data-placement="left">
				<i class="fa fa-trash-o"></i> Xóa
			</a>
			<button type='button' class='btn btn-primary btncls' onclick="trahangfunc()">
				<i class="fa fa-angle-double-left"></i> 
				Trả hàng
			</button>	
			<button type='button' class='btn btn-primary btncls' onclick="inhoadon()">
				<i class="fa fa-print"></i> 
				In Hóa Đơn
			</button>	
			<button type='button' class='btn btn-primary btncls' onclick="giaohang()">
				<i class="fa fa-truck"></i> 
				Giao Hàng
			</button>	
		</div>
    </div>
    <div class="box-content no-print">
        <div class="row no-padding-lhson">
            <div class="col-lg-12"> 

                <div class="table-responsive">
                    <table id="SLData" class="table table-bordered table-hover table-striped" style="width:100%">
                        <thead>
                        <tr>
                            <th style="min-width:30px; width: 30px; text-align: center;">
                                <input class="checkbox checkft" type="checkbox" name="check"/>
                            </th>
                            <th><?= lang("date"); ?></th>
                            <th><?= lang("reference_no"); ?></th>
                            <th>ĐVGH</th>
							<th>Kho</th>
							<th>Nhân viên</th>
                            <th>Khách hàng</th>
                            <th>TT Đơn hàng</th>
                            <th><?= lang("grand_total"); ?></th>
                            <th>Đã trả</th>
                            <th><?= lang("balance"); ?></th>
                            <th>TT Thanh toán</th>
                            <th style="min-width:30px; width: 30px; text-align: center;"><i class="fa fa-chain"></i></th>
                            <th></th>
                            <th style="width:80px; text-align:center;"><?= lang("actions"); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="13" class="dataTables_empty"><?= lang("loading_data"); ?></td>
                        </tr>
                        </tbody>
                        <tfoot class="dtFilter">
                        <tr class="active">
                            <th style="min-width:30px; width: 30px; text-align: center;">
                                <input class="checkbox checkft" type="checkbox" name="check"/>
                            </th>
                            <th></th><th></th><th></th><th></th><th></th><th></th><th></th>
                            <th><?= lang("grand_total"); ?></th>
                            <th><?= lang("paid"); ?></th>
                            <th><?= lang("balance"); ?></th>
                            <th></th>
                            <th style="min-width:30px; width: 30px; text-align: center;"><i class="fa fa-chain"></i></th>
                            <th></th>
                            <th style="width:80px; text-align:center;"><?= lang("actions"); ?></th>
                        </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<div id="locketqua" class="form-group no-print">
		<?php
		$_lockq = array('' => lang('All'), 'paid' => lang('paid'), 'partial' => lang('partial'), 'due' => lang('due'));
		//echo form_dropdown('dllockq', $_lockq,'', 'id="_lockq" class="form-control input-tip select" data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("status") . '" style="width:100%;" ');
		?>
	</div>
<?php if ($Owner || $GP['bulk_actions']) {?>
    <div style="display: none;">
        <input type="hidden" name="form_action" value="" id="form_action"/>
        <?=form_submit('performAction', 'performAction', 'id="action-form-submit"')?>
    </div>
    <?=form_close()?>
<?php }
?>
