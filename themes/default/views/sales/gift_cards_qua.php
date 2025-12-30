<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script>
    $(document).ready(function () {
        $('#GCData').dataTable({
            "aaSorting": [[3, "desc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= site_url('sales/getGiftCards/qua') ?>',
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
            }, {"bVisible": false},{"mRender": currencyFormat},null, {"mRender": currencyFormat}, {"mRender": currencyFormat,"bVisible": true},null, null,{"bVisible": false},{"bSortable": false}]
        });
    });
</script>
<?= form_open('sales/gift_card_actions', 'id="action-form"') ?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-gift"></i><?= lang('Đổi điểm tích lũy lấy quà') ?></h2>

        <div class="main-task-lhson">
            <a class="btn btn-primary btncls" href="<?php echo site_url('sales/add_gift_card_qua'); ?>" data-toggle="modal" data-target="#myModal">
                <i class="fa fa-plus"></i> <?= lang('Thêm quà tặng') ?>
            </a>
            <a class="btn btn-primary btncls" href="#" id="excel" data-action="export_excel">
                <i class="fa fa-file-excel-o"></i> Excel
            </a>
            <a class="btn btn-primary btncls" href="#" id="pdf" data-action="export_pdf">
                <i class="fa fa-file-pdf-o"></i> PDF
            </a>
            <a class="btn btn-primary btncls" href="#" id="delete" data-action="delete">
                <i class="fa fa-trash-o"></i>Xóa
            </a>
        </div>  
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <div class="table-responsive">
                    <table id="GCData" class="table table-bordered table-hover table-striped">
                        <thead>
                        <tr>
                            <th style="min-width:30px; width: 30px; text-align: center;">
                                <input class="checkbox checkth" type="checkbox" name="check"/>
                            </th>
                            <th><?php echo $this->lang->line("card_no"); ?></th>
                            <th><?php echo $this->lang->line("Tên quà tặng"); ?></th>
                            <th><?php echo $this->lang->line("Số điểm"); ?></th>
                            <th><?php echo $this->lang->line("value"); ?></th>
                            <th><?php echo $this->lang->line("balance"); ?></th>
                            <th><?php echo $this->lang->line("created_by"); ?></th>
                            <th><?php echo $this->lang->line("customer"); ?></th>
                            <th><?php echo $this->lang->line("date"); ?></th>
                            <th style="width:65px;"><?php echo $this->lang->line("actions"); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="7" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
                        </tr>

                        </tbody>
                    </table>
                </div>

            </div>

        </div>
    </div>
</div>

<div style="display: none;">
    <input type="hidden" name="form_action" value="" id="form_action"/>
    <?= form_submit('submit', 'submit', 'id="action-form-submit"') ?>
</div>
<?= form_close() ?>
<script language="javascript">
    $(document).ready(function () {

        $('#delete').click(function (e) {
            e.preventDefault();
            $('#form_action').val($(this).attr('data-action'));
            $('#action-form-submit').trigger('click');
        });

        $('#excel').click(function (e) {
            e.preventDefault();
            $('#form_action').val($(this).attr('data-action'));
            $('#action-form-submit').trigger('click');
        });

        $('#pdf').click(function (e) {
            e.preventDefault();
            $('#form_action').val($(this).attr('data-action'));
            $('#action-form-submit').trigger('click');
        });

    });
</script>
