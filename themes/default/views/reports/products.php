
<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php 
if ($_GET['warehouse']) {
    $_POST['warehouse']=$_GET['warehouse'];
}else{
    $_POST['warehouse']=1;
}
?> 

 
<?php
$v = "";

if ($this->input->post('product')) {
    $v .= "&product=" . $this->input->post('product');
}
if ($this->input->post('category')) {
    $v .= "&category=" . $this->input->post('category');
}
if ($this->input->post('group_id')) {
    $v .= "&group_id=" . $this->input->post('group_id');
}
if ($this->input->post('brand')) {
    $v .= "&brand=" . $this->input->post('brand');
}
if ($this->input->post('subcategory')) {
    $v .= "&subcategory=" . $this->input->post('subcategory');
}
if ($this->input->post('warehouse')) {
    $v .= "&warehouse=" . $this->input->post('warehouse');
}
if ($this->input->post('start_date')) {
    $v .= "&start_date=" . $this->input->post('start_date');
}
if ($this->input->post('end_date')) {
    $v .= "&end_date=" . $this->input->post('end_date');
}
if ($this->input->post('cf1')) {
    $v .= "&cf1=" . $this->input->post('cf1');
}
if ($this->input->post('cf2')) {
    $v .= "&cf2=" . $this->input->post('cf2');
}
if ($this->input->post('cf3')) {
    $v .= "&cf3=" . $this->input->post('cf3');
}
if ($this->input->post('cf4')) {
    $v .= "&cf4=" . $this->input->post('cf4');
}
if ($this->input->post('cf5')) {
    $v .= "&cf5=" . $this->input->post('cf5');
}
if ($this->input->post('cf6')) {
    $v .= "&cf6=" . $this->input->post('cf6');
}
?>
<script>
    $(document).ready(function () {
        function spb(x) {
            v = x.split('__');
            return '('+v[0]+') <strong>'+formatMoney(v[1])+'</strong>';
        }
		$("#start_date").datetimepicker({
						format:'yyyy-mm-dd',
						fontAwesome: true,
						language: 'sma',
						weekStart: 1,
						todayBtn: 1,
						autoclose: 1,
						todayHighlight: 1,
						startView: 2,
						forceParse: 0, 
						showTimepicker: false,
						minView: 2,
						showClear:true,
						showClose:true,
				}).datetimepicker('update','');
		$("#end_date").datetimepicker({
				format:'yyyy-mm-dd',
				fontAwesome: true,
				language: 'sma',
				weekStart: 1,
				todayBtn: 1,
				autoclose: 1,
				todayHighlight: 1,
				startView: 2,
				forceParse: 0, 
				showTimepicker: false,
				minView: 2,
				showClear:true,
				showClose:true,
		}).datetimepicker('update','');
		
        oTable = $('#PrRData').dataTable({
            "aaSorting": [[9, "desc"], [2, "desc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= site_url('reports/getProductsReport/?v=1'.$v) ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            'fnRowCallback': function (nRow, aData, iDisplayIndex) {
                nRow.id = aData[15];
                nRow.className = "product_link2";
                return nRow;
            },
            "aoColumns": [null, null,{"bSearchable": false},
            {
                "mRender": function ( data, type, row ) {
                    return formatMoney(row[17]);
                },
                "aTargets": [ 4 ],"bSearchable": false
            },{
                "mRender": function ( data, type, row ) {
                    return row[3];
                },
                "aTargets": [ 0 ],"bSearchable": false
            },
            {
                "mRender": function ( data, type, row ) {
                    return formatMoney(row[9]);
                },
                "aTargets": [ 0 ],"bSearchable": false
            },{
                "mRender": function ( data, type, row ) {
                    return row[4];
                },
                "aTargets": [ 0 ],"bSearchable": false
            },{
                "mRender": function ( data, type, row ) {
                    return formatMoney(row[8]);
                },
                "aTargets": [ 0 ],"bSearchable": false
            },{
                "mRender": function ( data, type, row ) {
                    return formatMoney(row[5]);
                },
                "aTargets": [ 0 ],"bSearchable": false
            },{
                "mRender": function ( data, type, row ) {
                    return row[6];
                },
                "aTargets": [ 0 ],"bSearchable": false
            },{
                "mRender": function ( data, type, row ) {
                    return formatMoney(row[10]);
                },
                "aTargets": [ 0 ],"bSearchable": false
            },{
                "mRender": function ( data, type, row ) {
                    return formatQuantity2(row[13]);
                },
                "aTargets": [ 0 ],"bSearchable": false
            },{
                "mRender": function ( data, type, row ) {
                    return formatQuantity2(row[14]);
                },
                "aTargets": [ 0 ],"bSearchable": false
            },{
                "mRender": function ( data, type, row ) {
                    return formatQuantity2(row[11]);
                },
                "aTargets": [ 0 ],"bSearchable": false
            },{
                'bVisible':false
            },{
                "mRender": function ( data, type, row ) {
                    
                    return row[18];
                },
                "aTargets": [ 0 ],"bSearchable": false
            }],
            "fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {
                var pq = 0, sq = 0, bq = 0, pa = 0, sa = 0, ba = 0, pl = 0; 
				var banduoc=0;
				var  tienban=0; 
				var tongnhap=0;
				var tiennhap=0; 
				var tonkho=0;
				var tienton=0;
				var loinhuan=0;
				var thuhoi=0;
                for (var i = 0; i < aaData.length; i++) {
                   // p = (aaData[aiDisplay[i]][2]).split('__');
                   // s = (aaData[aiDisplay[i]][3]).split('__');
                    //b = (aaData[aiDisplay[i]][5]).split('__');
                   // pq += parseFloat(p[0]);
                   // pa += parseFloat(p[1]);
                   // sq += parseFloat(s[0]);
                   // sa += parseFloat(s[1]);
                   // bq += parseFloat(b[0]); 
                   // ba += parseFloat(b[1]);
				   console.log(aaData[aiDisplay[i]]);
     //                loinhuan=eval(loinhuan+"+"+parseFloat(aaData[aiDisplay[i]][5]));					
					// banduoc=eval(banduoc+"+"+parseFloat(aaData[aiDisplay[i]][8]));					
					// tienban+=aaData[aiDisplay[i]][8];
					// tongnhap=aaData[aiDisplay[i]][3];					
					// tiennhap= eval(tiennhap+"+"+parseFloat(aaData[aiDisplay[i]][9]));					
					// tienton=eval(tienton+"+"+parseFloat(aaData[aiDisplay[i]][10]));					
					// thuhoi=eval(thuhoi+"+"+parseFloat(aaData[aiDisplay[i]][12]));
                }
                var nCells = nRow.getElementsByTagName('th');
    //             nCells[3].innerHTML = '<div class="text-right">'+formatMoney(tiennhap)+'</div>';
    //             nCells[4].innerHTML = '<div class="text-right">'+formatMoney(banduoc)+'</div>';
    //             nCells[5].innerHTML = currencyFormat(parseFloat(loinhuan));
    //             nCells[6].innerHTML = '<div class="text-right">'+formatMoney(tienton)+'</div>';
				// nCells[7].innerHTML = '<div class="text-right">'+formatMoney(thuhoi)+'</div>';
            }
        }).fnSetFilteringDelay().dtFilter([
            {column_number: 0, filter_default_label: "[<?=lang('product_code');?>]", filter_type: "text", data: []},
            {column_number: 1, filter_default_label: "[<?=lang('product_name');?>]", filter_type: "text", data: []},
        ], "footer");
    });
</script>
<script type="text/javascript">
    $(document).ready(function () {
        $('#form').hide();
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
<script type="text/javascript">
    $(document).ready(function () {
        // $('#category').select2({allowClear: true, placeholder: "<?= lang('select'); ?>", minimumResultsForSearch: 7}).select2('destroy');
        $("#subcategory").select2("destroy").empty().attr("placeholder", "<?= lang('select_category_to_load') ?>").select2({
            allowClear: true,
            placeholder: "<?= lang('select_category_to_load') ?>", data: [
                {id: '', text: '<?= lang('select_category_to_load') ?>'}
            ]
        });
        $('#category').change(function () {
            var v = $(this).val();
            if (v) {
                $.ajax({
                    type: "get",
                    async: false,
                    url: "<?= site_url('products/getSubCategories') ?>/" + v,
                    dataType: "json",
                    success: function (scdata) {
                        if (scdata != null) {
                            $("#subcategory").select2("destroy").empty().attr("placeholder", "<?= lang('select_subcategory') ?>").select2({allowClear: true,
                                placeholder: "<?= lang('select_category_to_load') ?>",
                                data: scdata
                            });
                        } else {
                            $("#subcategory").select2("destroy").empty().attr("placeholder", "<?= lang('no_subcategory') ?>").select2({allowClear: true,
                                placeholder: "<?= lang('no_subcategory') ?>",
                                data: [{id: '', text: '<?= lang('no_subcategory') ?>'}]
                            });
                        }
                    },
                    error: function () {
                        bootbox.alert('<?= lang('ajax_error') ?>');
                    }
                });
            } else {
                $("#subcategory").select2("destroy").empty().attr("placeholder", "<?= lang('select_category_to_load') ?>").select2({allowClear: true,
                    placeholder: "<?= lang('select_category_to_load') ?>",
                    data: [{id: '', text: '<?= lang('select_category_to_load') ?>'}]
                });
            }
        });
        <?php if (isset($_POST['category']) && !empty($_POST['category'])) { ?>
        $.ajax({
            type: "get", async: false,
            url: "<?= site_url('products/getSubCategories') ?>/" + <?= $_POST['category'] ?>,
            dataType: "json",
            success: function (scdata) {
                if (scdata != null) {
                    $("#subcategory").select2("destroy").empty().attr("placeholder", "<?= lang('select_subcategory') ?>").select2({allowClear: true,
                        placeholder: "<?= lang('no_subcategory') ?>",
                        data: scdata
                    });
                }
            }
        });
        <?php } ?>
    });
</script>

<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-barcode"></i><?= lang('products_report'); ?> <?php
            if ($this->input->post('start_date')) {
                echo "Từ " . $this->input->post('start_date') . " đến " . $this->input->post('end_date');
            }
            ?></h2>
<div id="box-icon">
    
  <ul class="btn-tasks khohanglhson">
    <?php 
        

        foreach ($warehouses as $kho) {            
$__active="";
            if ($_POST['warehouse']==$kho->id) {
                $__active="selected";
            }
            ?>
              <li class="dropdown"><a class="<?=$__active;?>" href="/reports/products?warehouse=<?=$kho->id;?>"><?=$kho->name;?></a></li>  
            <?php
        }
    ?>    
  </ul>
  <?php echo form_open("reports/products",array('class' => 'myformsubmit', 'id' => 'myformsubmit')); ?>
        <div class="col-sm-3">
            <div class="form-group">
                <?= lang("Từ", "start_date"); ?>
                <?php echo form_input('start_date', (isset($_POST['start_date']) ? $_POST['start_date'] : ""), 'class="form-control datetime" id="start_date" autocomplete="off"'); ?>
            </div>
        </div>
        <div class="col-sm-3">
            <div class="form-group">
                <?= lang("Đến", "end_date"); ?>
                <?php echo form_input('end_date', (isset($_POST['end_date']) ? $_POST['end_date'] : ""), 'class="form-control datetime" id="end_date" autocomplete="off"'); ?>
            </div>
        </div>
        <div class="col-sm-5">
            <div class="form-group">
                <?= lang("category", "category") ?>
                <?php
                $cat[''] = lang('select').' '.lang('category');
                foreach ($categories as $category) {
                    $cat[$category->id] = $category->name;
                }
                echo form_dropdown('category', $cat, (isset($_POST['category']) ? $_POST['category'] : ''), 'class="form-control select" id="category" placeholder="' . lang("select") . " " . lang("category") . '" style="width:100%"')
                ?>
            </div>
            <div class="controls" id="subcat_data"> <?php
                echo form_input('subcategory', (isset($_POST['subcategory']) ? $_POST['subcategory'] : ''), 'class="form-control" id="subcategory"  placeholder="' . lang("select_category_to_load") . '"');
                ?>
            </div>
        </div>

        <div class="col-sm-1">
            <div class="form-group">
                <div class="controls"> <?php echo form_submit('submit_report', $this->lang->line("submit"), 'class="btn btn-primary"'); ?> </div>
            </div>
        </div>
    <?php echo form_close(); ?>
</div>
        <div class="box-icon" style="display: none">
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
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <div id="form">

                    <?php echo form_open("reports/products"); ?>
                    <div class="row">
                        <div class="col-sm-4" style="display: none">
                            <div class="form-group">
                                <?= lang("product", "suggest_product"); ?>
                                <?php echo form_input('sproduct', (isset($_POST['sproduct']) ? $_POST['sproduct'] : ""), 'class="form-control" id="suggest_product"'); ?>
                                <input type="hidden" name="product" value="<?= isset($_POST['product']) ? $_POST['product'] : "" ?>" id="report_product_id"/>
                            </div>
                        </div>
                        <div class="col-sm-4" style="display: none">
                            <div class="form-group">
                                <?= lang("category", "category") ?>
                                <?php
                                $cat[''] = lang('select').' '.lang('category');
                                foreach ($categories as $category) {
                                    $cat[$category->id] = $category->name;
                                }
                                echo form_dropdown('category', $cat, (isset($_POST['category']) ? $_POST['category'] : ''), 'class="form-control select" id="category" placeholder="' . lang("select") . " " . lang("category") . '" style="width:100%"')
                                ?>
                            </div>
                        </div>

                        <div class="col-md-4" style="display: none">
                            <div class="form-group">
                                <?= lang("subcategory", "subcategory") ?>
                                <div class="controls" id="subcat_data"> <?php
                                    echo form_input('subcategory', (isset($_POST['subcategory']) ? $_POST['subcategory'] : ''), 'class="form-control" id="subcategory"  placeholder="' . lang("select_category_to_load") . '"');
                                    ?>
                                </div>
                            </div>
                        </div>
						<div class="col-sm-4" style="display: none">
                            <div class="form-group">
                                <?= lang("Nhóm sản phẩm", "group_id") ?>
                                <?php
                                $nhom_sp[''] = lang('select').' '.lang('Nhóm sản phẩm');
                                foreach ($nhom as $nm) {
                                    $nhom_sp[$nm->id] = $nm->name;
                                }
                                echo form_dropdown('group_id', $nhom_sp, (isset($_POST['group_id']) ? $_POST['group_id'] : ''), 'class="form-control select" id="group_id" placeholder="' . lang("select") . " " . lang("Nhóm sản phẩm") . '" style="width:100%"')
                                ?>
                            </div>
                        </div>
                        <div class="col-sm-4" style="display: none">
                            <div class="form-group">
                                <?= lang("brand", "brand") ?>
                                <?php
                                $bt[''] = lang('select').' '.lang('brand');
                                foreach ($brands as $brand) {
                                    $bt[$brand->id] = $brand->name;
                                }
                                echo form_dropdown('brand', $bt, (isset($_POST['brand']) ? $_POST['brand'] : ''), 'class="form-control select" id="brand" placeholder="' . lang("select") . " " . lang("brand") . '" style="width:100%"')
                                ?>
                            </div>
                        </div>

                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="warehouse"><?= lang("warehouse"); ?></label>
                                <?php
                                $wh[""] = lang('select').' '.lang('warehouse');
                                foreach ($warehouses as $warehouse) {
                                    $wh[$warehouse->id] = $warehouse->name;
                                }
                                echo form_dropdown('warehouse', $wh, (isset($_POST['warehouse']) ? $_POST['warehouse'] : ""), 'class="form-control" id="warehouse" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("warehouse") . '"');
                                ?>
                            </div>
                        </div>

                        <div class="col-md-4" style="display: none">
                            <div class="form-group all">
                                <?= lang('pcf1', 'cf1') ?>
                                <?= form_input('cf1', (isset($_POST['cf1']) ? $_POST['cf1'] : ''), 'class="form-control tip" id="cf1"') ?>
                            </div>
                        </div>

                        <div class="col-md-4" style="display: none">
                            <div class="form-group all">
                                <?= lang('pcf2', 'cf2') ?>
                                <?= form_input('cf2', (isset($_POST['cf2']) ? $_POST['cf2'] : ''), 'class="form-control tip" id="cf2"') ?>
                            </div>
                        </div>

                        <div class="col-md-4" style="display: none">
                            <div class="form-group all">
                                <?= lang('pcf3', 'cf3') ?>
                                <?= form_input('cf3', (isset($_POST['cf3']) ? $_POST['cf3'] : ''), 'class="form-control tip" id="cf3"') ?>
                            </div>
                        </div>

                        <div class="col-md-4" style="display: none">
                            <div class="form-group all">
                                <?= lang('pcf4', 'cf4') ?>
                                <?= form_input('cf4', (isset($_POST['cf4']) ? $_POST['cf4'] : ''), 'class="form-control tip" id="cf4"') ?>
                            </div>
                        </div>

                        <div class="col-md-4" style="display: none">
                            <div class="form-group all">
                                <?= lang('pcf5', 'cf5') ?>
                                <?= form_input('cf5', (isset($_POST['cf5']) ? $_POST['cf5'] : ''), 'class="form-control tip" id="cf5"') ?>
                            </div>
                        </div>

                        <div class="col-md-4" style="display: none">
                            <div class="form-group all">
                                <?= lang('pcf6', 'cf6') ?>
                                <?= form_input('cf6', (isset($_POST['cf6']) ? $_POST['cf6'] : ''), 'class="form-control tip" id="cf6"') ?>
                            </div>
                        </div>
                        <div class="col-sm-4" style="display: none">
                            <div class="form-group">
                                <?= lang("start_date", "start_date"); ?>
                                <?php echo form_input('start_date', (isset($_POST['start_date']) ? $_POST['start_date'] : ""), 'class="form-control datetime" id="start_date" autocomplete="off"'); ?>
                            </div>
                        </div>
                        <div class="col-sm-4" style="display: none">
                            <div class="form-group">
                                <?= lang("end_date", "end_date"); ?>
                                <?php echo form_input('end_date', (isset($_POST['end_date']) ? $_POST['end_date'] : ""), 'class="form-control datetime" id="end_date" autocomplete="off"'); ?>
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
                    <table id="PrRData"
                           class="table table-striped table-bordered table-condensed table-hover dfTable reports-table"
                           style="margin-bottom:5px;">
                        <thead>
                        <tr class="active">
                            <th><?= lang("product_code"); ?></th>
                            <th><?= lang("product_name"); ?></th>
                            <th><?= lang("Tồn đầu"); ?></th>
                            <th><?= lang("Giá trị"); ?></th>
							<th><?= lang("SL Nhập"); ?></th>
                            <th><?= lang("Giá trị"); ?></th>
                            <th><?= lang("SL Bán"); ?></th>
                            <th><?= lang("Giá trị"); ?></th>
                            <th><?= lang("Lợi nhuận"); ?></th>
                            <th style="min-width:120px; width: 120px; text-align: center;color: red"><?= lang("SL Tồn"); ?></th>
                            <th style="min-width:120px; width: 120px; text-align: center;"><?= lang("Giá trị"); ?></th>
                            <th><?= lang("SL +"); ?></th>
                            <th><?= lang("SL -"); ?></th>
                            <th><?= lang("Thu hồi"); ?></th>
							<th><?= lang("Thu hồi"); ?></th>
                            <th><?= lang("Chuyển kho"); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="16" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
                        </tr>
                        </tbody>
                        <tfoot class="dtFilter">
                        <tr class="active">
                            <th><?= lang("product_code"); ?></th>
                            <th><?= lang("product_name"); ?></th>
                            <th><?= lang("Tồn đầu"); ?></th>
                            <th><?= lang("Giá trị"); ?></th>
                            <th><?= lang("SL Nhập"); ?></th>
                            <th><?= lang("Giá trị"); ?></th>
                            <th><?= lang("SL Bán"); ?></th>
                            <th><?= lang("Giá trị"); ?></th>
                            <th><?= lang("Lợi nhuận"); ?></th>
                            <th><?= lang("SL Tồn"); ?></th>
                            <th><?= lang("Giá trị"); ?></th>
                            <th><?= lang("SL +"); ?></th>
                            <th><?= lang("SL -"); ?></th>
                            <th><?= lang("Thu hồi"); ?></th>
                            <th><?= lang("Thu hồi"); ?></th>
                            <th><?= lang("Chuyển kho"); ?></th>
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
            window.location.href = "<?=site_url('reports/getProductsReport/pdf/?v=1'.$v)?>";
            return false;
        });
        $('#xls').click(function (event) {
            event.preventDefault();
            window.location.href = "<?=site_url('reports/getProductsReport/0/xls/?v=1'.$v)?>";
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
<style type="text/css">
    ul.btn-tasks.khohanglhson li {
        float: right;
        margin: 5px;
        font-size: 13px;
        font-weight: bold;
        background: #438eb9;
        color: #fff;
        border-radius: 5px;
        padding: 5px;
    }

    ul.btn-tasks.khohanglhson li a {
        color: #fff;
        padding: 5px;
    }

    ul.btn-tasks.khohanglhson li a.selected {
        background: #ffb752;
    }
    div#box-icon form {
    float: right;
    width: 30%;
    margin-top: 3px;
}

ul.btn-tasks.khohanglhson {
    float: right;
}

div#box-icon form .col-sm-5,div#box-icon form .col-sm-2 {
    float: left;
    margin: 0px;
}

div#box-icon form .col-sm-5 label {
    float: left;
    line-height: 32px;
    margin: 0px;
}

input#start_date,input#end_date {
        width: 100px;
        margin-left: 10px;
        position: relative;
    }
    .box .box-header input.btn.btn-primary.input-xs {
    margin-top: -3px;
}
@media only screen and (max-width: 767px) {
    div#box-icon form .col-sm-5 .form-group,div#box-icon form .col-sm-2 .form-group {
        margin: 0px;
        float: left;
        width: 100%;
    }

    div#box-icon form .col-sm-5 .form-group input {
        float: right;
        width: auto;
    }

    input.btn.btn-primary.input-xs {
        padding: 0px 10px;
        height: 32px;
    }

    div#box-icon form {
        width: 100%;
        margin-bottom: 4px;
    }

    div#box-icon form .col-sm-5 {
        float: left;
        width: 40%;
    }

    div#box-icon form .col-sm-2 {
        width: 10%;
    }

    div#box-icon form .col-sm-5 input {
        width: 78%!important;
    }

    div#box-icon form .col-sm-5 label {
        font-size: 12px;
    }


    .box .box-header h2 {
        font-size: 12px;
    }
    .dataTables_wrapper .col-md-6.text-left {
        float: left;
    }
    .dataTables_wrapper .col-md-6.text-left {
        float: left;
    }

}

form#myformsubmit {
    width: 50%!important;
}

form#myformsubmit .col-sm-3 {
    padding: 0px;
}

form#myformsubmit label {
    float: left;
    margin: 0px;
    padding: 0px 5px;
}

form#myformsubmit .form-group {
    margin: 0px;
}

form#myformsubmit .col-sm-5 label {
    display: none;
}

form#myformsubmit div#s2id_category {
    float: left;
    width: 50%!important;
}

div#subcat_data {
    float: left;
    width: 50%;
}

</style>