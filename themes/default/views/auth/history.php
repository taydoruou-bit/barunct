<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script>
    $(document).ready(function () {
        oTable = $('#TBLHISTORYFULL').dataTable({
            "aaSorting": [[1, "desc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength":10,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= site_url('auth/getHistorys') ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            'fnRowCallback': function (nRow, aData, iDisplayIndex) {
                var oSettings = oTable.fnSettings();
                var $nRow = $(nRow);
                nRow.id = aData[6];
                $nRow.attr("theloai", aData[2]);
                $nRow.attr("ADD", aData[7]);
                nRow.className = "history_full_link";
                return nRow;
            },
            "aoColumns": [{"mRender": fld}, null, null,null,null,null]
        }).fnSetFilteringDelay().dtFilter([
            {column_number: 0, filter_default_label: "[<?=lang('Ngày');?> (yyyy-mm-dd)]", filter_type: "text", data: []},
            {column_number: 1, filter_default_label: "[<?=lang('Bởi');?> ]", filter_type: "text", data: []},          
            {column_number: 2, filter_default_label: "[<?=lang('Thể loại');?>]", filter_type: "text", data: []},
            {column_number: 3, filter_default_label: "[<?=lang('Thao tác');?>]", filter_type: "text", data: []},
            {column_number: 4, filter_default_label: "[<?=lang('ID HĐ');?>]", filter_type: "text", data: []},
            {column_number: 5, filter_default_label: "[<?=lang('Mã hóa đơn');?>]", filter_type: "text", data: []},
        ], "footer");
        
    });

</script>

<?php if ($Owner || $GP['bulk_actions']) {
    echo form_open('auth/thongbao_actions', 'id="action-form"');
} ?>
<div class="box no-print">
    <div class="box-header">
        <h2 class="blue"><i
                class="fa-fw fa fa-history"></i><?= lang('Lịch sử giao dịch'); ?>
        </h2>

        <div class="box-icon no-print">
           
        </div>
        <div class="main-task-lhson no-print">
           
        </div>  
    </div>
    <div class="box-content no-print">
        <div class="row">
            <div class="col-lg-12">
                <div class="table-responsive">
                    <table id="TBLHISTORYFULL" class="table table-bordered table-hover table-striped">
                        <thead>
                        <tr class="active">                        
                            <th class="text-center">Ngày</th>
                            <th class="text-center">Bởi</th>
                            <th class="text-center">Thể loại</th>
                            <th class="text-center">Thao tác</th> 
                            <th class="text-center">ID HĐ</th>
                            <th class="text-center">Mã Hóa Đơn</th>

                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="7"
                                class="dataTables_empty"><?= lang("loading_data"); ?></td>
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
                        </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php if ($Owner || $GP['bulk_actions']) { ?>
    <div style="display: none;">
        <input type="hidden" name="form_action" value="" id="form_action"/>
        <?= form_submit('performAction', 'performAction', 'id="action-form-submit"') ?>
    </div>
    <?= form_close() ?>
<?php } ?>