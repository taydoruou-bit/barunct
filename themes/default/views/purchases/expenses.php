<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script>
    $(document).ready(function () {
		 var pb = <?= json_encode($pb); ?>;
        function paid_by(x) {
            return (x != null) ? (pb[x] ? pb[x] : x) : x;
        }
        function attachment(x) {
            if (x != null) {
                return '<a href="' + site.base_url + 'assets/uploads/' + x + '" target="_blank"><i class="fa fa-chain"></i></a>';
            }
            return x;
        }
		function customlhson(x) {
            if (x != null) {
				if(x.indexOf("-NCC") != -1){
					var split_x=x.split("-NCC");
					//la xoa payment
					return str='<div class="text-center"><div class="btn-group text-left"><button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">Tác vụ <span class="caret"></span></button><ul class="dropdown-menu pull-right" role="menu">     <li><a href="<?= site_url('purchases/payment_note'); ?>/'+split_x[0]+'" data-toggle="modal" data-target="#myModal"><i class="fa fa-file-text-o"></i> Chi tiết</a></li>  <li><a href="<?= site_url('purchases/printphieuchincc'); ?>/'+split_x[0]+'" data-toggle="modal" data-target="#myModal"><i class="fa fa-print"></i> In phiếu chi</a></li> <li><a href="<?= site_url('purchases/edit_payment_lhson'); ?>/'+split_x[0]+'" data-toggle="modal" data-target="#myModal"><i class="fa fa-edit"></i> Sửa chi phí</a></li> <li><a href="#" class="po" title="<b>Xóa chi phí</b>" data-content="<p>Bạn có chắc không?</p><a class=\'btn btn-danger po-delete\' href=\'<?= site_url('purchases/delete_payment_lhson_ajax'); ?>/'+split_x[0]+'\'>Vâng tôi chắc chắn</a> <button class=\'btn po-close\'>Không</button>" rel="popover"><i class="fa fa-trash-o"></i> Xóa chi phí</a></li>  </ul></div></div>';
				}if(x.indexOf("-RT") != -1){
					var split_x=x.split("-RT");
					return str='<div class="text-center"><div class="btn-group text-left"><button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">Tác vụ <span class="caret"></span></button><ul class="dropdown-menu pull-right" role="menu">     <li><a href="<?= site_url('purchases/printphieuchi'); ?>/'+split_x[0]+'" data-toggle="modal" data-target="#myModal"><i class="fa fa-print"></i> In phiếu chi</a></li><li><a href="<?= site_url('purchases/expense_note'); ?>/'+split_x[0]+'" data-toggle="modal" data-target="#myModal"><i class="fa fa-file-text-o"></i> Chi tiết</a></li> </ul></div></div>';
				}else{

					return str='<div class="text-center"><div class="btn-group text-left"><button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">Tác vụ <span class="caret"></span></button><ul class="dropdown-menu pull-right" role="menu">     <li><a href="<?= site_url('purchases/printphieuchi'); ?>/'+x+'" data-toggle="modal" data-target="#myModal"><i class="fa fa-print"></i> In phiếu chi</a></li><li><a href="<?= site_url('purchases/expense_note'); ?>/'+x+'" data-toggle="modal" data-target="#myModal"><i class="fa fa-file-text-o"></i> Chi tiết</a></li> <li><a href="<?= site_url('purchases/edit_expense'); ?>/'+x+'" data-toggle="modal" data-target="#myModal"><i class="fa fa-edit"></i> Sửa chi phí</a></li> <li><a href="#" class="po" title="<b>Xóa chi phí</b>" data-content="<p>Bạn có chắc không?</p><a class=\'btn btn-danger po-delete\' href=\'<?= site_url('purchases/delete_expense'); ?>/'+x+'\'>Vâng tôi chắc chắn</a> <button class=\'btn po-close\'>Không</button>" rel="popover"><i class="fa fa-trash-o"></i> Xóa chi phí</a></li>  </ul></div></div>';
				}
            }
            return x;
        }
		

        oTable = $('#EXPData').dataTable({
            "aaSorting": [[1, "desc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= site_url('purchases/getExpenses'); ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            "aoColumns": [{
                "bSortable": false,
                "mRender": checkbox
            }, {"mRender": fld},{"bSortable": false}, null, null,null, null,null,{"mRender": currencyFormat},{"mRender": paid_by}, null, {
                "bSortable": false,
                "mRender": attachment
            },{
                "bSortable": false,
                "mRender": customlhson
            }],
            'fnRowCallback': function (nRow, aData, iDisplayIndex) {
                var oSettings = oTable.fnSettings();
                nRow.id = aData[0];
                nRow.className = "expense_link";
                return nRow;
            },
            "fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {
                var total = 0;
                for (var i = 0; i < aaData.length; i++) {
                    total += parseFloat(aaData[aiDisplay[i]][8]);
                }
                var nCells = nRow.getElementsByTagName('th');
                nCells[8].innerHTML = currencyFormat(total);
            }
        }).fnSetFilteringDelay().dtFilter([            
        ], "footer");

    });
	function inhoadon(){
		var val=0;
		var count=0;
		jQuery("#EXPData tbody tr").each(function(){
			var chk=jQuery(this).find("input[type='checkbox']:checked");						
			if(chk.length>0){
				val=jQuery(chk).val();
				count=count+1;
			}
		});
		if(count==0){
			alert("Bạn chưa chọn phiếu chi cần in");
			return false;
		}else if(count>1){
			alert("Chỉ in 1 phiếu chi cùng lúc, bạn đang chọn "+count);
			return false;
		}else{
			if(val==0){
				alert("Không nhận được id phiếu chi");
				return false;
			}else{
				if(val.indexOf("-NCC") != -1){					
					var split_x=val.split("-NCC");
					jQuery("#EXPData a[href='<?php echo base_url();?>purchases/printphieuchincc/"+split_x[0]+"']").trigger("click");
				}else if(val.indexOf("-RT") != -1){					
					var split_x=val.split("-RT");
					jQuery("#EXPData a[href='<?php echo base_url();?>purchases/printphieuchi/"+split_x[0]+"']").trigger("click");
				}else{					
					jQuery("#EXPData a[href='<?php echo base_url();?>purchases/printphieuchi/"+val+"']").trigger("click");
				}
			}
		}
	}
</script>

<?php if ($Owner) {
    echo form_open('purchases/expense_actions', 'id="action-form"');
} ?>
<div class="box no-print">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-dollar"></i><?= lang('expenses'); ?></h2>

		<div class="main-task-lhson">
			<a class="btn btn-primary btncls" href="<?= site_url('purchases/add_expense') ?>" data-toggle="modal" data-target="#myModal">
				<i class="fa fa-plus-circle"></i> <?= lang('add_expense') ?>
			</a>
			<button type='button' class='btn btn-primary btncls' onclick="inhoadon()">
				<i class="fa fa-print"></i> 
				In Phiếu Chi
			</button>	
			<a class="btn btn-primary btncls" href="#" id="excel" data-action="export_excel">
				<i class="fa fa-file-excel-o"></i> <?= lang('export_to_excel') ?>
			</a>
			<a class="btn btn-primary btncls" href="#" id="pdf" data-action="export_pdf">
				<i class="fa fa-file-pdf-o"></i> <?= lang('export_to_pdf') ?>
			</a>
			<a class="btn btn-primary btncls bpo" href="#"
				title="<b><?=lang("delete_sales")?></b>"
				data-content="<p><?=lang('r_u_sure')?></p><button type='button' class='btn btn-danger' id='delete' data-action='delete'><?=lang('i_m_sure')?></a> <button class='btn bpo-close'><?=lang('no')?></button>"
				data-html="true" data-placement="left">
				<i class="fa fa-trash-o"></i> Xóa
			</a>
		</div>	
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <div class="table-responsive">
                    <table id="EXPData" cellpadding="0" cellspacing="0" border="0"
                           class="table table-bordered table-hover table-striped">
                        <thead>
                        <tr class="active">
                            <th style="min-width:30px; width: 30px; text-align: center;">
                                <input class="checkbox checkft" type="checkbox" name="check"/>
                            </th>
                            <th class="col-xs-1"><?= lang("date"); ?></th>
                            <th class="col-xs-1"><?= lang("reference"); ?></th>
							<th ><?= lang("Nhân viên"); ?></th>
							
                            <th ><?= lang("Tên"); ?></th>
                            <th ><?= lang("Điện thoại"); ?></th>
                            <th class="col-xs-2"><?= lang("Địa chỉ"); ?></th>

                            <th ><?= lang("category"); ?></th>
                            <th class="col-xs-1"><?= lang("amount"); ?></th>
							<th ><?= lang("paid_by"); ?></th>
                            <th class="col-xs-2"><?= lang("note"); ?></th>                            
                            <th style="min-width:30px; width: 30px; text-align: center;"><i class="fa fa-chain"></i>
                            </th>
							
                            <th style="width:100px;"><?= lang("actions"); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="13" class="dataTables_empty"><?= lang('loading_data_from_server'); ?></td>
                        </tr>
                        </tbody>
                        <tfoot class="dtFilter">
                        <tr class="active">
                            <th style="min-width:30px; width: 30px; text-align: center;">
                                <input class="checkbox checkft" type="checkbox" name="check"/>
                            </th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
							<th></th>
							<th style="min-width:30px; width: 30px; text-align: center;"><i class="fa fa-chain"></i>
                            </th>
                            <th style="width:100px; text-align: center;"><?= lang("actions"); ?></th>
                        </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php if ($Owner) { ?>
    <div style="display: none;">
        <input type="hidden" name="form_action" value="" id="form_action"/>
        <?= form_submit('performAction', 'performAction', 'id="action-form-submit"') ?>
    </div>
    <?= form_close() ?>
<?php } ?>