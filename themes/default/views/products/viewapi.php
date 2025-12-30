<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                <i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?= $product->name; ?> <?=$remove==true?'(Đã xóa)':'';?></h4>
        </div>
        <div class="modal-body">

            <div class="row">
                <div class="col-xs-12">
                    <div class="table-responsive">
                        <script>
                        $(document).ready(function () {
                            oTable = $('#tblHisDetail').dataTable({
                                "aaSorting": [[0, "desc"]],
                                "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
                                "iDisplayLength": <?= $Settings->rows_per_page ?>,
                                'bProcessing': true, 'bServerSide': true,
                                'sAjaxSource': '<?= site_url('reports/getHistoryApiProducts/?product_id='.$product->id) ?>',
                                'fnServerData': function (sSource, aoData, fnCallback) {
                                    aoData.push({
                                        "name": "<?= $this->security->get_csrf_token_name() ?>",
                                        "value": "<?= $this->security->get_csrf_hash() ?>"
                                    });
                                    $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
                                },
                                'fnRowCallback': function (nRow, aData, iDisplayIndex) {
                                    nRow.id = aData[1]; 
                                    nRow.className =  "";
                                    return nRow;
                                },
                                "aoColumns": [{"mRender": fld}, {"bVisible": false},{"bVisible": false},{"mRender": formatQuantity}, {"mRender": currencyFormat},null],
                                "fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {
                                   
                                }
                            }).fnSetFilteringDelay().dtFilter([], "footer");
                        });
                    </script>
                        <table id="tblHisDetail" class="table table-bordered table-hover table-striped table-condensed reports-table">
                            <thead>
                                <tr>
                                    <th class="col-xs-1"><?= lang("date"); ?></th>
                                    <th class="col-xs-1"><?= lang("IDSP"); ?></th>
                                    <th ><?= lang("Tên sản phẩm"); ?></th>
                                    <th class="col-xs-1"><?= lang("Tồn kho"); ?></th>
                                    <th class="col-xs-1"><?= lang("Giá bán"); ?></th>                           
                                    <th class="col-xs-1"><?= lang("Loại"); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td colspan="4" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
                            </tr>
                            </tbody>
                            <tfoot class="dtFilter">
                            <tr class="active">
                                <th class="col-xs-1"></th>
                                <th class="col-xs-1"><?= lang("IDSP"); ?></th>
                                <th ><?= lang("Tên sản phẩm"); ?></th>
                                <th class="col-xs-1"><?= lang("Tồn kho"); ?></th>
                                <th class="col-xs-1"><?= lang("Giá bán"); ?></th>                           
                                <th class="col-xs-1"><?= lang("Loại"); ?></th>
                            </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <div class="clearfix"></div>                   
            </div>
        </div>
    </div>   
</div>