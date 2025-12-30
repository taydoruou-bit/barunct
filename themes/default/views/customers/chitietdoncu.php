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
            <h4 class="modal-title" id="myModalLabel">Chi tiết hóa đơn: <?= $hoadon->mahoadon; ?>        
            </h4>
        </div>  
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo form_open_multipart("customers/docan/" . $hoadon->id, $attrib); ?>
        <div class="modal-body">          
               
                <div id="donhangcu" class="row">                        
                        <script>
                        $(document).ready(function () {
                            
                            oTable = $('#SlRDataDonHangCuCT').dataTable({
                                "responsive": true,
                                "aaSorting": [[3, "desc"]],
                                "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
                                "iDisplayLength": <?= $Settings->rows_per_page ?>,
                                'bProcessing': true, 'bServerSide': true,
                                'sAjaxSource': '<?= site_url('reports/getSalesReportByHoaDonCuChiTiet/' . $hoadon->mahoadon) ?>',
                                'fnServerData': function (sSource, aoData, fnCallback) {
                                    aoData.push({
                                        "mahoadon": "<?= $hoadon->mahoadon ?>",
                                        "name": "<?= $this->security->get_csrf_token_name() ?>",
                                        "value": "<?= $this->security->get_csrf_hash() ?>"
                                    });
                                    $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
                                },
                                'fnRowCallback': function (nRow, aData, iDisplayIndex) {
                                    nRow.id = aData[0]; 
                                    nRow.className = "dondoncu_link2 warning";
                                    return nRow;
                                },
                                "aoColumns": [{"mRender": null,"bVisible": false},null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,],
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
                                        <table id="SlRDataDonHangCuCT"
                                               class="table table-bordered table-hover table-striped table-condensed responsive reports-table">
                                            <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Chi nhánh</th><th>Mã hóa đơn</th><th>Mã vận đơn</th><th>Địa chỉ lấy hàng</th><th>Mã đối soát</th><th>Phí trả ĐTGH</th><th>Thời gian</th><th>Thời gian tạo</th><th>Ngày cập nhật</th><th>Mã đặt hàng</th><th>Mã YCSC</th><th>Mã trả hàng</th><th>Mã khách hàng</th><th>Tên khách hàng</th><th>Email</th><th>Điện thoại</th><th>Địa chỉ (Khách hàng)</th><th>Khu vực (Khách hàng)</th><th>Phường/Xã (Khách hàng)</th><th>Ngày sinh</th><th>Người bán</th><th>Kênh bán</th><th>Người tạo</th><th>Đối tác giao hàng</th><th>Người nhận</th><th>Điện thoại (Người nhận)</th><th>Địa chỉ (Người nhận)</th><th>Khu vực (Người nhận)</th><th>Phường/Xã (Người nhận)</th><th>Dịch vụ</th><th>Trọng lượng (gram)</th><th>Dài</th><th>Rộng</th><th>Cao</th><th>Ghi chú trạng thái giao hàng</th><th>Ghi chú</th><th>Tổng tiền hàng</th><th>Giảm giá hóa đơn</th><th>Khách cần trả</th><th>Khách đã trả</th><th>Tiền mặt</th><th>Thẻ</th><th>Chuyển khoản</th><th>Điểm</th><th>Voucher</th><th>Mã voucher</th><th>Còn cần thu (COD)</th><th>Thời gian giao hàng</th><th>Trạng thái</th><th>Trạng thái giao hàng</th><th>Mã hàng</th><th>Mã vạch</th><th>Tên hàng</th><th>Thương hiệu</th><th>ĐVT</th><th>Ghi chú hàng hóa</th><th>Số lượng</th><th>Đơn giá</th><th>Giảm giá %</th><th>Giảm giá</th><th>Giá bán</th><th>Thành tiền</th><th>Bảo hành</th><th>Định kỳ Bảo trì</th>

                                            </tr>
                                            </thead>
                                            <tbody>
                                            <tr>
                                                <td colspan="70" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
                                            </tr>
                                            </tbody>
                                            <tfoot class="dtFilter">
                                            <tr class="active">
                                                
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
        <div class="modal-footer no-print">
                <button type="button" class="btn btn-default pull-left" data-dismiss="modal"><?= lang('close'); ?></button>
                
                     
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
<script type="text/javascript">
   
</script>