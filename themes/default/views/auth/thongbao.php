<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script>
    $(document).ready(function () {
        oTable = $('#TBLTHONGBAO').dataTable({
            "aaSorting": [[1, "desc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= site_url('auth/getThongBaos') ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            'fnRowCallback': function (nRow, aData, iDisplayIndex) {
                var oSettings = oTable.fnSettings();
                nRow.id = aData[0];
                nRow.className = "thongbao_link";
                return nRow;
            },
            "aoColumns": [ null, {"mRender": fld}, null, null,{"bSortable": false}]
        }).fnSetFilteringDelay().dtFilter([
            {column_number: 0, filter_default_label: "[<?=lang('Tiêu đề');?>]", filter_type: "text", data: []},
            {column_number: 1, filter_default_label: "[<?=lang('date');?> (yyyy-mm-dd)]", filter_type: "text", data: []},          
            {column_number: 2, filter_default_label: "[<?=lang('Thể loại');?>]", filter_type: "text", data: []},
            {column_number: 3, filter_default_label: "[<?=lang('Đã xem');?>]", filter_type: "text", data: []},
        ], "footer");
        
    });

</script>

<?php if ($Owner || $GP['bulk_actions']) {
    echo form_open('auth/thongbao_actions', 'id="action-form"');
} ?>
<div class="box no-print">
    <div class="box-header">
        <h2 class="blue"><i
                class="fa-fw fa fa-envelope"></i><?= lang('Thông báo từ Server'); ?>
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
                    <table id="TBLTHONGBAO" class="table table-bordered table-hover table-striped">
                        <thead>
                        <tr class="active">                        
                            <th class="text-center">Tiêu đề</th>
                            <th class="text-center">Ngày gửi</th>
                            <th class="text-center">Thể loại</th>
                            <th class="text-center">Đã xem</th> 
                            <th class="text-center">#</th>

                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="6"
                                class="dataTables_empty"><?= lang("loading_data"); ?></td>
                        </tr>
                        </tbody>
                        <tfoot class="dtFilter">
                        <tr class="active">
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th style="width:115px; text-align:center;"><?= lang("actions"); ?></th>
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