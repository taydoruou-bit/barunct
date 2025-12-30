<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php

$v = "";
if ($this->input->post('reference_no')) {
    $v .= "&reference_no=" . $this->input->post('reference_no');
}
if ($this->input->post('category')) {
    $v .= "&category=" . $this->input->post('category');
}
if ($this->input->post('note')) {
    $v .= "&note=" . $this->input->post('note');
}
if ($this->input->post('user')) {
    $v .= "&user=" . $this->input->post('user');
}
if ($this->input->post('start_date')) {
    $v .= "&start_date=" . $this->input->post('start_date');
}
if ($this->input->post('end_date')) {
    $v .= "&end_date=" . $this->input->post('end_date');
}

?>
<script>
    $(document).ready(function () {
        var pb = <?= json_encode($pb); ?>;
        function paid_by(x) {
            return (x != null) ? (pb[x] ? pb[x] : x) : x;
        }

        function ref(x) {
            return (x != null) ? x : ' ';
        }
		 function attachment(x) {
            if (x != null) {
                return '<a href="' + site.base_url + 'assets/uploads/' + x + '" target="_blank"><i class="fa fa-chain"></i></a>';
            }
            return x;
        }
		function customlhson(x) {
			console.log(x);
            if (x != null) {
				if(x.indexOf("-THUKH") != -1){
					var split_x=x.split("-THUKH");
					//la xoa payment
					return str='<div class="text-center"><div class="btn-group text-left"><button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">Tác vụ <span class="caret"></span></button><ul class="dropdown-menu pull-right" role="menu">     <li><a href="<?= site_url('purchases/printphieuthu'); ?>/'+split_x[0]+'" data-toggle="modal" data-target="#myModal2"><i class="fa fa-print"></i> In phiếu thu</a></li><li><a href="<?= site_url('sales/payment_note'); ?>/'+split_x[0]+'" data-toggle="modal" data-target="#myModal2"><i class="fa fa-file-text-o"></i> Chi tiết</a></li> <li><a href="<?= site_url('sales/edit_payment_lhson'); ?>/'+split_x[0]+'" data-toggle="modal" data-target="#myModal"><i class="fa fa-edit"></i> Sửa thanh toán</a></li> <li><a href="#" class="po" title="<b>Xóa thanh toán</b>" data-content="<p>Bạn có chắc không?</p><a class=\'btn btn-danger po-delete\' href=\'<?= site_url('sales/delete_payment_lhson_ajax'); ?>/'+split_x[0]+'\'>Vâng tôi chắc chắn</a> <button class=\'btn po-close\'>Không</button>" rel="popover"><i class="fa fa-trash-o"></i> Xóa thanh toán</a></li>  </ul></div></div>';
				}else if(x.indexOf("-TIENCOC") != -1){
                    var split_x=x.split("-TIENCOC");
                    //la xoa payment
                    return str='<div class="text-center"><div class="btn-group text-left"><button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">Tác vụ <span class="caret"></span></button><ul class="dropdown-menu pull-right" role="menu">     <li><a href="<?= site_url('purchases/printphieuthu'); ?>/'+split_x[0]+'" data-toggle="modal" data-target="#myModal2"><i class="fa fa-print"></i> In phiếu thu tiền cọc</a></li><li><a href="<?= site_url('customers/edit_deposit'); ?>/'+split_x[0]+'" data-toggle="modal" data-target="#myModal"><i class="fa fa-edit"></i> Sửa đặt cọc</a></li> <li><a href="#" class="po" title="<b>Xóa đặt cọc</b>" data-content="<p>Bạn có chắc không?</p><a class=\'btn btn-danger po-delete\' href=\'<?= site_url('customers/delete_deposit'); ?>/'+split_x[0]+'\'>Vâng tôi chắc chắn</a> <button class=\'btn po-close\'>Không</button>" rel="popover"><i class="fa fa-trash-o"></i> Xóa đặt cọc</a></li>  </ul></div></div>';
                }
				else if(x.indexOf("-KHAC") != -1){
					var split_x=x.split("-KHAC");
					//la xoa payment
					return str='<div class="text-center"><div class="btn-group text-left"><button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">Tác vụ <span class="caret"></span></button><ul class="dropdown-menu pull-right" role="menu">     <li><a href="<?= site_url('purchases/printphieuthu'); ?>/'+split_x[0]+'" data-toggle="modal" data-target="#myModal2"><i class="fa fa-print"></i> In phiếu thu</a></li> <li><a href="<?= site_url('purchases/edit_phieuthu'); ?>/'+split_x[0]+'" data-toggle="modal" data-target="#myModal"><i class="fa fa-edit"></i> Sửa phiếu thu</a></li> <li><a href="#" class="po" title="<b>Xóa phiếu thu</b>" data-content="<p>Bạn có chắc không?</p><a class=\'btn btn-danger po-delete\' href=\'<?= site_url('purchases/delete_phieuthu_ajax'); ?>/'+split_x[0]+'\'>Vâng tôi chắc chắn</a> <button class=\'btn po-close\'>Không</button>" rel="popover"><i class="fa fa-trash-o"></i> Xóa phiếu thu</a></li>  </ul></div></div>';
				}else{
					return str='<div class="text-center"><div class="btn-group text-left"><button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">Tác vụ <span class="caret"></span></button><ul class="dropdown-menu pull-right" role="menu">     <li><a href="<?= site_url('purchases/printphieuthu'); ?>/'+x+'" data-toggle="modal" data-target="#myModal2"><i class="fa fa-print"></i> In phiếu thu</a></li><li><a href="<?= site_url('sales/payment_note'); ?>/'+x+'" data-toggle="modal" data-target="#myModal2"><i class="fa fa-file-text-o"></i> Chi tiết</a></li> <li><a href="<?= site_url('sales/edit_payment'); ?>/'+x+'" data-toggle="modal" data-target="#myModal"><i class="fa fa-edit"></i> Sửa thanh toán</a></li> <li><a href="#" class="po" title="<b>Xóa thanh toán</b>" data-content="<p>Bạn có chắc không?</p><a class=\'btn btn-danger po-delete\' href=\'<?= site_url('sales/delete_payment_ajax'); ?>/'+x+'\'>Vâng tôi chắc chắn</a> <button class=\'btn po-close\'>Không</button>" rel="popover"><i class="fa fa-trash-o"></i> Xóa thanh toán</a></li>  </ul></div></div>';
				}
            }
            return x;
        }
		
        oTable = $('#PhieuthuRData').dataTable({
            "aaSorting": [[0, "desc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= site_url('reports/getBaocaothuReport/?v=1' . $v) ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            "aoColumns": [{"mRender": fld}, null, {"mRender": ref},null,null,null, {"mRender": paid_by}, {"mRender": currencyFormat,"bSortable": true}, {"mRender": null,"bSortable": true},{
                "bSortable": false,
                "mRender": attachment
            },{"mRender": null},{
                "bSortable": false,
                "mRender": customlhson
            }],
            'fnRowCallback': function (nRow, aData, iDisplayIndex) {
                nRow.id = aData[13];
                nRow.className = "phieuthu_link";               
                return nRow;
            },
            "fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {
                var total = 0;
                for (var i = 0; i < aaData.length; i++) {                    
                        total += parseFloat(aaData[aiDisplay[i]][7]);
                }
                var nCells = nRow.getElementsByTagName('th');
                nCells[7].innerHTML = currencyFormat(parseFloat(total));
            }
        }).fnSetFilteringDelay().dtFilter([], "footer");

    });
</script>
<script type="text/javascript">
    $(document).ready(function () {
        $('#form').hide();
        <?php if ($this->input->post('biller')) { ?>
        $('#rbiller').select2({ allowClear: true });
        <?php } ?>
		
        <?php if ($this->input->post('supplier')) { ?>
        $('#rsupplier').val(<?= $this->input->post('supplier') ?>).select2({
            minimumInputLength: 1,
            allowClear: true,
            initSelection: function (element, callback) {
                $.ajax({
                    type: "get", async: false,
                    url: "<?= site_url('suppliers/getSupplier') ?>/" + $(element).val(),
                    dataType: "json",
                    success: function (data) {
                        callback(data[0]);
                    }
                });
            },
            ajax: {
                url: site.base_url + "suppliers/suggestions",
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
        $('#rsupplier').val(<?= $this->input->post('supplier') ?>);
        <?php } ?>
        <?php if ($this->input->post('customer')) { ?>
        $('#rcustomer').val(<?= $this->input->post('customer') ?>).select2({
            minimumInputLength: 1,
            allowClear: true,
            initSelection: function (element, callback) {
                $.ajax({
                    type: "get", async: false,
                    url: "<?= site_url('customers/getCustomer') ?>/" + $(element).val(),
                    dataType: "json",
                    success: function (data) {
                        callback(data[0]);
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
        <?php } ?>
        $('.toggle_down').click(function () {
            $("#form").slideDown();
            return false;
        });
        $('.toggle_up').click(function () {
            $("#form").slideUp();
            return false;
        });
    });
</script>

<div class="box no-print">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-money"></i>Báo cáo các khoản thu
        </h2>
		<div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a href="#" class="toggle_up tip" title="<?= lang('hide_form') ?>">
                        <i class="icon fa fa-toggle-up"></i>
                    </a>
                </li>
                <li class="dropdown">
                    <a href="#" class="toggle_down tip" title="<?= lang('show_form') ?>">
                        <i class="icon fa fa-toggle-down"></i>
                    </a>
                </li>
            </ul>
        </div>
        <div class="box-icon">
            <ul class="btn-tasks">
				<a class="btn btn-primary btncls" href="<?= site_url('purchases/add_phieuthu') ?>" data-toggle="modal" data-target="#myModal">
					<i class="fa fa-plus-circle"></i> <?= lang('add_phieuthu') ?>
				</a>
                <li class="dropdown">
                    <a href="#" id="pdf" class="tip" title="<?= lang('download_pdf') ?>">
                        <i class="icon fa fa-file-pdf-o"></i>
                    </a>
                </li>
                <li class="dropdown">
                    <a href="#" id="xls" class="tip" title="<?= lang('download_xls') ?>">
                        <i class="icon fa fa-file-excel-o"></i>
                    </a>
                </li>
                <li class="dropdown">
                    <a href="#" id="image" class="tip" title="<?= lang('save_image') ?>">
                        <i class="icon fa fa-file-picture-o"></i>
                    </a>
                </li>
				
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
				<div id="form">

                    <?php echo form_open("reports/baocaothu"); ?>
                    <div class="row">
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="reference_no"><?= lang("reference_no"); ?></label>
                                <?php echo form_input('reference_no', (isset($_POST['reference_no']) ? $_POST['reference_no'] : ""), 'class="form-control tip" id="reference_no"'); ?>

                            </div>
                        </div>

                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="user"><?= lang("created_by"); ?></label>
                                <?php
                                $us[""] = lang('select').' '.lang('user');
                                foreach ($users as $user) {
                                    $us[$user->id] = $user->first_name . " " . $user->last_name;
                                }
                                echo form_dropdown('user', $us, (isset($_POST['user']) ? $_POST['user'] : ""), 'class="form-control" id="user" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("user") . '"');
                                ?>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="category"><?= lang("category"); ?></label>
                                <?php
                                $ct[""] = lang('select').' '.lang('category');
                                foreach ($categories as $category) {
                                    $ct[$category->id] = $category->name;
                                }
								$ct['banhang'] = "Thu Bán Hàng";
								$ct['khachhang'] = "Thu Khách Hàng";
								$ct['nhanvien'] = "Thu Nhân viên";
								$ct['nhacungcap'] = "Thu Nhà cung cấp";
                                $ct['tiencoc'] = "Thu tiền đặt cọc";
								
                                echo form_dropdown('category', $ct, (isset($_POST['category']) ? $_POST['category'] : ""), 'class="form-control" id="category" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("category") . '"');
                                ?>
                            </div>
                        </div>

                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang("note", "note"); ?>
                                <?php echo form_input('note', (isset($_POST['note']) ? $_POST['note'] : ""), 'class="form-control" id="note"'); ?>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang("start_date", "start_date"); ?>
                                <?php echo form_input('start_date', (isset($_POST['start_date']) ? $_POST['start_date'] : ""), 'class="form-control datetime" id="start_date"'); ?>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang("end_date", "end_date"); ?>
                                <?php echo form_input('end_date', (isset($_POST['end_date']) ? $_POST['end_date'] : ""), 'class="form-control datetime" id="end_date"'); ?>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div
                            class="controls"> <?php echo form_submit('submit_report', $this->lang->line("submit"), 'class="btn btn-primary"'); ?> </div>
                    </div>
                    <?php echo form_close(); ?>

                </div>
                <div class="clearfix"></div>
                <div class="table-responsive">
                    <table id="PhieuthuRData"
                           class="table table-bordered table-hover table-striped table-condensed reports-table">

                        <thead>
                        <tr>
                            <th class="col-xs-1"><?= lang("date"); ?></th>
                            <th><?= lang("payment_ref"); ?></th>
                            <th>Họ tên</th>
                            <th>Điện thoại</th>
                            <th class="col-xs-2">Địa chỉ</th>
							<th>Nhân viên</th>
                            <th class="col-xs-1"><?= lang("paid_by"); ?></th>
                            <th class="col-xs-1"><?= lang("amount"); ?></th>
                            <th class="col-xs-2"><?= lang("type"); ?></th>                           
							 <th style="min-width:30px; width: 30px; text-align: center;"><i class="fa fa-chain"></i>
                            </th>
							 <th class="col-xs-2"><?= lang("note"); ?></th>
							<th style="width:100px;"><?= lang("actions"); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="12" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
                        </tr>
                        </tbody>
                        <tfoot class="dtFilter">
                        <tr class="active">
                            <th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th>
                        </tr>
                        </tfoot>
                    </table>
                </div>

            </div>
        </div>
    </div>
</div>
<script type="text/javascript" src="<?= $assets ?>js/html2canvas.min.js"></script>
<script type="text/javascript">
    $(document).ready(function () {
        $('#pdf').click(function (event) {
            event.preventDefault();
            window.location.href = "<?=site_url('reports/getBaocaothuReport/pdf/?v=1'.$v)?>";
            return false;
        });
        $('#xls').click(function (event) {
            event.preventDefault();
            window.location.href = "<?=site_url('reports/getBaocaothuReport/0/xls/?v=1'.$v)?>";
            return false;
        });
        $('#image').click(function (event) {
            event.preventDefault();
            html2canvas($('.box'), {
                onrendered: function (canvas) {
                    var img = canvas.toDataURL()
                    window.open(img);
                }
            });
            return false;
        });
    });
</script>