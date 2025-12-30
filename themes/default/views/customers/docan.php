<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script type="text/javascript" src="<?= $assets ?>js/custom.js"></script>
<script type="text/javascript" src="<?= $assets ?>js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="<?= $assets ?>js/jquery.dataTables.dtFilter.min.js"></script>
<script type="text/javascript" charset="UTF-8">var oTable = '', r_u_sure = "<?=lang('r_u_sure')?>";
    <?=$s2_file_date?>
    $.extend(true, $.fn.dataTable.defaults, {"oLanguage":<?=$dt_lang?>});
    $.fn.datetimepicker.dates['sma'] = <?=$dp_lang?>;
</script>
<div class="modal-dialog modal-lg lhson_add_biller">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?= $customer->company && $customer->company != '-' ? $customer->company. " (".$customer->name.")" : $customer->name; ?> 
            <?php 
            if ($customer->ngaysinh!='0000-00-00') {
                echo Date("d/m/Y",strtotime($customer->ngaysinh));
                if ($sotuoi!='Unknown') {
                    echo " (".$sotuoi." tuổi)";    
                }                
            }
            ?>
            </h4>
        </div>  
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo form_open_multipart("customers/docan/" . $customer->id, $attrib); ?>
        <div class="modal-body">
            <ul id="myTab" class="nav nav-tabs no-print">
                <li class="active"><a data-toggle="tab" href="<?=site_url('customers/docan')?>#view_kh" class="tab-grey"><?= lang('Thông tin') ?></a></li>
                <li class=""><a data-toggle="tab" href="<?=site_url('customers/docan')?>#theohoadon" class="tab-grey"><?= lang('Lịch sử mua hàng') ?></a></li>
                 <li class=""><a data-toggle="tab" href="<?=site_url('customers/docan')?>#donhangcu" class="tab-grey"><?= lang('Đơn hàng củ') ?></a></li>
            </ul>
            <div class="tab-content">
                <div id="view_kh" class="tab-pane fade in active">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered" style="margin-bottom:0;">
                            <tbody>
                            <tr>
                                <td><strong><?= lang("customer_group"); ?></strong></td>
                                <td><?= $customer->customer_group_name; ?></td>
                            </tr>
                            <tr>
                                <td><strong><?= lang("Loại khách"); ?></strong></td>
                                <td>
                                    <?php
                                    $style="style='display:none'";
                                        if ($customer->loaikhach==0) {
                                            $style="";
                                           echo "Công ty";
                                        }else{
                                            echo "Cá nhân";
                                        } 
                                        ?>
                                    
                                </td>
                            </tr>
                            <tr <?=$style?>>
                                <td><strong><?= lang("company"); ?></strong></td>
                                <td><?= $customer->company; ?></strong></td>
                            </tr>
                            <tr <?=$style?>>
                                <td><strong><?= lang("Mã số thuế"); ?></strong></td>
                                <td><?= $customer->vat_no; ?></td>
                            </tr>
                            <tr>
                                <td><strong><?= lang("Họ và Tên"); ?></strong></td>
                                <td><?= $customer->name; ?></td>
                            </tr>
                            <tr>
                                <td><strong><?= lang("Ngày sinh"); ?></strong></td>
                                <td><?php 
                                 $ngaysinh=Date("d/m/Y",strtotime($customer->ngaysinh));
                                 if ($customer->ngaysinh=='0000-00-00') {
                                     $ngaysinh='';
                                 }; 
                                 echo $ngaysinh;
                                 ?>
                                  <?php 
                                if ($customer->ngaysinh!='0000-00-00') {
                                    if ($sotuoi!='Unknown') {
                                        echo " (".$sotuoi." tuổi)";    
                                    }                
                                }
                                ?>
                             </td>
                            </tr>
                            <tr>
                                <td><strong><?= lang("Giới tính"); ?></strong></td>
                                <td>
                                    <?php
                                        if ($customer->gioitinh==0) {
                                           echo "Nữ";
                                        }else{
                                            echo "Nam";
                                        } 
                                        ?>
                                    
                                </td>
                            </tr>
                            <tr>
                                <td><strong><?= lang("Facebook"); ?></strong></td>
                                <td><a href="https://facebook.com/<?= $customer->facebook; ?>" target="_blank"><?= $customer->facebook; ?></a></td>
                            </tr>

                            <tr>
                                <td><strong><?= lang("email"); ?></strong></td>
                                <td><?= $customer->email; ?></td>
                            </tr>
                            <tr>
                                <td><strong><?= lang("phone"); ?></strong></td>
                                <td><strong><?= $customer->phone; ?></strong></td>
                            </tr>
                            <tr>
                                <td><strong><?= lang("address"); ?></strong></td>
                                <td><?= $customer->address; ?></td>
                            </tr>
                             <tr>
                                <td><strong><?= lang("Ghi chú"); ?></strong></td>
                                <td><textarea id="ghichu" style="width: 100%;" name="ghichu" class="skip"><?=$customer->ghichu;?></textarea></td>
                            </tr>
                            <tr>
                                <td><strong><?= lang("Nợ ban đầu"); ?></strong></td>
                                <td><?= number_format($customer->nobandau); ?></td>
                            </tr>
                           
                            <tr>
                                <td><strong><?= lang("award_points"); ?></strong></td>
                                <td><?= $customer->award_points; ?></td>
                            </tr>
                            <tr>
                                <td><strong><?= lang("ccf1"); ?></strong></td>
                                <td><?= $customer->cf1; ?></td>
                            </tr>
                            <tr>
                                <td><strong><?= lang("ccf2"); ?></strong></td>
                                <td><?= $customer->cf2; ?></td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                 <div id="theohoadon" class="tab-pane fade in <?=$this->input->post('start_date_theohoadon')?'active':'';?>"> 

                        <?php
                        $vhoadon = "&customer=" . $customer->id;                        
                        ?>
                        <script>
                        $(document).ready(function () {
                            function fld(oObj) {
                                if (oObj != null) {
                                    var aDate = oObj.split('-');
                                    var bDate = aDate[2].split(' ');
                                    year = aDate[0], month = aDate[1], day = bDate[0], time = bDate[1];
                                    if (site.dateFormats.js_sdate == 'dd-mm-yyyy')
                                        return day + "-" + month + "-" + year + " " + time;
                                    else if (site.dateFormats.js_sdate === 'dd/mm/yyyy')
                                        return day + "/" + month + "/" + year + " " + time;
                                    else if (site.dateFormats.js_sdate == 'dd.mm.yyyy')
                                        return day + "." + month + "." + year + " " + time;
                                    else if (site.dateFormats.js_sdate == 'mm/dd/yyyy')
                                        return month + "/" + day + "/" + year + " " + time;
                                    else if (site.dateFormats.js_sdate == 'mm-dd-yyyy')
                                        return month + "-" + day + "-" + year + " " + time;
                                    else if (site.dateFormats.js_sdate == 'mm.dd.yyyy')
                                        return month + "." + day + "." + year + " " + time;
                                    else
                                        return oObj;
                                } else {
                                    return '';
                                }
                            }
                            oTable = $('#SlRDataDoCan').dataTable({
                                "responsive": true,
                                "aaSorting": [[0, "desc"]],
                                "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
                                "iDisplayLength": <?= $Settings->rows_per_page ?>,
                                'bProcessing': true, 'bServerSide': true,
                                'sAjaxSource': '<?= site_url('reports/getSalesReportBySanPham/?customer=' .$customer->id) ?>',
                                'fnServerData': function (sSource, aoData, fnCallback) {
                                    aoData.push({
                                        "name": "<?= $this->security->get_csrf_token_name() ?>",
                                        "value": "<?= $this->security->get_csrf_hash() ?>"
                                    });
                                    $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
                                },
                                'fnRowCallback': function (nRow, aData, iDisplayIndex) {
                                    nRow.id = aData[17]; 
                                    nRow.className = (aData[14] > 0) ? "invoice_link2" : "invoice_link2 warning";
                                    return nRow;
                                },
                                "aoColumns": [{"mRender": fld}, null,{"mRender": null,"bSearchable": false},{"mRender": null,"bVisible": false,'bSearchable':false}, {"mRender": null,"bVisible": false}, {"mRender": null,"bVisible": false}, {"mRender": null,"bSearchable": false}, {"mRender": formatQuantity}, {"mRender": currencyFormat},{"mRender": currencyFormat,"bVisible": false},{"mRender": currencyFormat,"bVisible": false},{"mRender": currencyFormat,"bVisible": false}, {"mRender": currencyFormat,"bVisible": true},{"mRender": currencyFormat},{"mRender": currencyFormat,"bVisible": false},{"mRender": currencyFormat,"bVisible": false}, {"mRender": null,"bVisible": false}],
                                "fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {
                                   
                                    
                                }
                            }).fnSetFilteringDelay().dtFilter([], "footer");
                        });
                    </script>
                    

                    <div class="box">
                       
                        <div class="box-content">
                            <div class="row">
                                <div class="col-lg-12">

                                    <div class="clearfix"></div>

                                    <div class="table-responsive">
                                        <table id="SlRDataDoCan"
                                               class="table table-bordered table-hover table-striped table-condensed responsive reports-table">
                                            <thead>
                                            <tr>
                                                <th style="min-width:80px; width: 80px;"><?= lang("date"); ?></th>
                                                <th><?= lang("reference_no"); ?></th>
                                                <th style="min-width:100px; width: 100px;"><?= lang("Kho"); ?></th>
                                                <th><?= lang("ĐVGH"); ?></th>
                                                <th><?= lang("biller"); ?></th>
                                                <th ><?= lang("customer"); ?></th>
                                                <th><?= lang("Sản phẩm"); ?></th>
                                                <th style="min-width:80px; width: 80px;"><?= lang("Số lượng"); ?></th>
                                                <th style="min-width:80px; width: 80px;"><?= lang("Giá bán"); ?></th>
                                                <th style="min-width:80px; width: 80px;"><?= lang("Thành tiền"); ?></th>
                                                <th><?= lang("Thuế"); ?></th>
                                                <th><?= lang("Ship"); ?></th>
                                                <th><?= lang("Giảm"); ?></th>
                                                <th style="min-width:80px; width: 80px;"><?= lang("Tổng cộng"); ?></th>
                                                <th><?= lang("Đã TT"); ?></th>
                                                <th><?= lang("Dư nợ"); ?></th>
                                                <th><?= lang("Trạng thái"); ?></th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <tr>
                                                <td colspan="17" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
                                            </tr>
                                            </tbody>
                                            <tfoot class="dtFilter">
                                            <tr class="active">
                                                <th></th>
                                                <th></th>
                                                <th></th>
                                                <th></th>
                                                <th></th>
                                                <th></th>
                                                <th><?= lang("Sản phẩm"); ?></th>
                                                <th><?= lang("Số lượng"); ?></th>
                                                <th><?= lang("Giá bán"); ?></th>
                                                <th><?= lang("Thành tiền"); ?></th>
                                                <th><?= lang("Thuế"); ?></th>
                                                <th><?= lang("Ship"); ?></th>
                                                <th><?= lang("Giảm"); ?></th>
                                                 <th><?= lang("Tổng cộng"); ?></th>
                                                <th><?= lang("Đã thanh toán"); ?></th>
                                                <th><?= lang("Dư nợ"); ?></th>
                                                <th></th>
                                            </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div> <!-- end theo hoa don -->
                <div id="donhangcu" class="tab-pane fade in <?=$this->input->post('start_date_donhangcu')?'active':'';?>"> 

                        <?php
                        $vhoadon = "&customer=" . $customer->id;                        
                        ?>
                        <script>
                        $(document).ready(function () {
                            function fld(oObj) {
                                if (oObj != null) {
                                    var aDate = oObj.split('-');
                                    var bDate = aDate[2].split(' ');
                                    year = aDate[0], month = aDate[1], day = bDate[0], time = bDate[1];
                                    if (site.dateFormats.js_sdate == 'dd-mm-yyyy')
                                        return day + "-" + month + "-" + year + " " + time;
                                    else if (site.dateFormats.js_sdate === 'dd/mm/yyyy')
                                        return day + "/" + month + "/" + year + " " + time;
                                    else if (site.dateFormats.js_sdate == 'dd.mm.yyyy')
                                        return day + "." + month + "." + year + " " + time;
                                    else if (site.dateFormats.js_sdate == 'mm/dd/yyyy')
                                        return month + "/" + day + "/" + year + " " + time;
                                    else if (site.dateFormats.js_sdate == 'mm-dd-yyyy')
                                        return month + "-" + day + "-" + year + " " + time;
                                    else if (site.dateFormats.js_sdate == 'mm.dd.yyyy')
                                        return month + "." + day + "." + year + " " + time;
                                    else
                                        return oObj;
                                } else {
                                    return '';
                                }
                            }
                            oTable = $('#SlRDataDonHangCu').dataTable({
                                "responsive": true,
                                "aaSorting": [[3, "desc"]],
                                "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
                                "iDisplayLength": <?= $Settings->rows_per_page ?>,
                                'bProcessing': true, 'bServerSide': true,
                                'sAjaxSource': '<?= site_url('reports/getSalesReportByHoaDonCu/' . $customer->cf1) ?>',
                                'fnServerData': function (sSource, aoData, fnCallback) {
                                    aoData.push({
                                        "name": "<?= $this->security->get_csrf_token_name() ?>",
                                        "value": "<?= $this->security->get_csrf_hash() ?>"
                                    });
                                    $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
                                },
                                'fnRowCallback': function (nRow, aData, iDisplayIndex) {
                                    nRow.id = aData[0]; 
                                    nRow.className = "doncu_link warning";
                                    return nRow;
                                },
                                "aoColumns": [{"mRender": null,"bVisible": false},{"mRender": null,"bSearchable": true},{"mRender": null,"bSearchable": true},{"mRender": null,"bSearchable": true},{"mRender": null,"bSearchable": true},{"mRender": null},{"mRender": null},{"mRender": null},{"mRender": null,"bSearchable": true},{"mRender": null,"bSearchable": true},{"mRender": null,"bSearchable": true},{"mRender": null,"bSearchable": true},{"mRender": null,"bSearchable": true},{"mRender": null,"bSearchable": true},{"mRender": null,"bSearchable": true},{"mRender": null,"bSearchable": true},{"mRender": null,"bSearchable": true},{"mRender": null,"bSearchable": true},{"mRender": null,"bSearchable": true},{"mRender": null,"bSearchable": true},{"mRender": null,"bSearchable": true},{"mRender": null,"bSearchable": true},{"mRender": null,"bSearchable": true},{"mRender": null,"bSearchable": true},{"mRender": null,"bSearchable": true},{"mRender": null,"bSearchable": true},{"mRender": null,"bSearchable": true},{"mRender": null,"bSearchable": true},{"mRender": null,"bSearchable": true},{"mRender": null,"bSearchable": true},{"mRender": null,"bSearchable": true},{"mRender": null,"bSearchable": true},{"mRender": null,"bSearchable": true}],
                                "fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {
                                   
                                    
                                }
                            }).fnSetFilteringDelay().dtFilter([], "footer");
                        });
                    </script>
                    

                    <div class="box">
                       
                        <div class="box-content">
                            <div class="row">
                                <div class="col-lg-12">

                                    <div class="clearfix"></div>

                                    <div class="table-responsive">
                                        <table id="SlRDataDonHangCu"
                                               class="table table-bordered table-hover table-striped table-condensed responsive reports-table">
                                            <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Mã hóa đơn</th>
                                                <th>Mã vận đơn</th>
                                                <th >Trạng thái giao hàng</th>
                                                <th>Mã đối soát</th>
                                                <th style="min-width:80px; width: 80px;"><?= lang("Thời gian"); ?></th>
                                                <th style="min-width:80px; width: 80px;"><?= lang("Thời gian tạo"); ?></th>
                                                <th style="min-width:80px; width: 80px;"><?= lang("Ngày cập nhật"); ?></th>
                                                <th>Mã đặt hàng</th>
                                                <th>Mã trả hàng</th>
                                                <th>Mã YCSC</th>
                                                <th>Khách hàng</th>
                                                <th>Email</th>
                                                <th>Điện thoại</th>
                                                <th>Địa chỉ</th>
                                                <th>Khu vực</th>
                                                <th>Phường/Xã</th>
                                                <th>Ngày sinh</th>
                                                <th>Chi nhánh</th>
                                                <th>Người bán</th>
                                                <th>Người tạo</th>
                                                <th>Kênh bán</th>
                                                <th>Đối tác giao hàng</th>
                                                <th>Ghi chú</th>
                                                <th>Tổng tiền hàng</th>
                                                <th>Giảm giá</th>
                                                <th>Khách cần trả</th>
                                                <th>Khách đã trả</th>
                                                <th>Còn cần thu (COD)</th>
                                                <th>Phí trả ĐTGH</th>
                                                <th>Ghi chú giao</th>
                                                <th>Thời gian giao</th>
                                                <th>Trạng thái</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <tr>
                                                <td colspan="35" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
                                            </tr>
                                            </tbody>
                                            <tfoot class="dtFilter">
                                            <tr class="active">
                                                <th>ID</th>
                                                <th>Mã hóa đơn</th>
                                                <th>Mã vận đơn</th>
                                                <th >Trạng thái giao hàng</th>
                                                <th>Mã đối soát</th>
                                                <th style="min-width:80px; width: 80px;"><?= lang("Thời gian"); ?></th>
                                                <th style="min-width:80px; width: 80px;"><?= lang("Thời gian tạo"); ?></th>
                                                <th style="min-width:80px; width: 80px;"><?= lang("Ngày cập nhật"); ?></th>
                                                <th>Mã đặt hàng</th>
                                                <th>Mã trả hàng</th>
                                                <th>Mã YCSC</th>
                                                <th>Khách hàng</th>
                                                <th>Email</th>
                                                <th>Điện thoại</th>
                                                <th>Địa chỉ</th>
                                                <th>Khu vực</th>
                                                <th>Phường/Xã</th>
                                                <th>Ngày sinh</th>
                                                <th>Chi nhánh</th>
                                                <th>Người bán</th>
                                                <th>Người tạo</th>
                                                <th>Kênh bán</th>
                                                <th>Đối tác giao hàng</th>
                                                <th>Ghi chú</th>
                                                <th>Tổng tiền hàng</th>
                                                <th>Giảm giá</th>
                                                <th>Khách cần trả</th>
                                                <th>Khách đã trả</th>
                                                <th>Còn cần thu (COD)</th>
                                                <th>Phí trả ĐTGH</th>
                                                <th>Ghi chú giao</th>
                                                <th>Thời gian giao</th>
                                                <th>Trạng thái</th>
                                            </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div> <!-- end theo don hang cu -->
            </div>            
        </div>
        <div class="modal-footer no-print">
                <button type="button" class="btn btn-default pull-left" data-dismiss="modal"><?= lang('close'); ?></button>
                
                <?php if ($Owner || $Admin || $GP['reports-customers']) { ?>
                    <a href="<?=site_url('reports/customer_report/'.$customer->id);?>" target="_blank" class="btn btn-primary"><?= lang('customers_report'); ?></a>
                <?php } ?>
                <?php if ($Owner || $Admin || $GP['customers-edit']) { ?>
                    <a href="<?=site_url('customers/edit/'.$customer->id);?>" data-toggle="modal" data-target="#myModal2" class="btn btn-primary"><?= lang('edit_customer'); ?></a>
                <?php } ?>               
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<?= $modal_js ?>

<style type="text/css">
    input.inputdocan {
        width: 70px;
    }
    button#btn-ghidocan-new {
        float: right;
        margin-left: 10px;
    }
    div#result {
        height: 323px;
        overflow-y: scroll;
        overflow-x: hidden;
        float: left;
        width: 100%;
    }
    tr.trsanpham td {
        font-weight: bold;
        color:#000;
    }

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

    button.btn.btn-small.btn-dn-fc i {
        margin-right: 2px;
        margin-top: 3px;
    }
    table input.inputdocan {
        width: 100%;
    }
    div#SlRDataDoCan_filter {
        float: left;
        width: 100%;
    }

    div#SlRDataDoCan_filter label {
        float: left;
        width: 100%;
        line-height: 30px;
    }
    @media only screen and (max-width: 767px) {
        .nav-tabs>li a {
            font-size: 12px;
        }
    }
</style>
