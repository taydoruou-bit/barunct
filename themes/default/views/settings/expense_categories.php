<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script>
    $(document).ready(function () {
        $('#CategoryTable').dataTable({
            "aaSorting": [[1, "asc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= site_url('system_settings/getExpenseCategories') ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            "aoColumns": [{"bSortable": false, "mRender": checkbox}, null, null,null,null,null, {"bSortable": false}]
        });
    });
</script>
<?= form_open('system_settings/expense_category_actions', 'id="action-form"') ?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-folder-open"></i>Danh mục thu / chi</h2>

       
		<div class="main-task-lhson">
			<a class="btn btn-primary btncls" href="<?php echo site_url('system_settings/add_expense_category'); ?>" data-toggle="modal" data-target="#myModal">
				<i class="fa fa-plus"></i> Thêm loại
			</a>
			<a class="btn btn-primary btncls" href="<?php echo site_url('system_settings/import_expense_categories'); ?>" data-toggle="modal" data-target="#myModal">
				<i class="fa fa-plus"></i>Import
			</a>
			<a class="btn btn-primary btncls" href="#" id="excel" data-action="export_excel">
				<i class="fa fa-file-excel-o"></i> Excel
			</a>
			<a class="btn btn-primary btncls" href="#" id="pdf" data-action="export_pdf">
				<i class="fa fa-file-pdf-o"></i> PDF
			</a>
			<a class="btn btn-primary btncls" href="#" id="delete" data-action="delete">
				<i class="fa fa-trash-o"></i> Xóa
			</a>
		</div>	
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <div class="table-responsive">
                    <table id="CategoryTable" class="table table-bordered table-hover table-striped">
                        <thead>
                            <tr>
                                <th style="min-width:30px; width: 30px; text-align: center;">
                                    <input class="checkbox checkth" type="checkbox" name="check"/>
                                </th>
                                <th><?= $this->lang->line("category_code"); ?></th>
                                <th><?= $this->lang->line("category_name"); ?></th>
								<th>Loại danh mục</th>
                                <th>Nợ </th>
                                <th>Có</th>
                                <th style="width:100px;"><?= $this->lang->line("actions"); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="7" class="dataTables_empty">
                                    <?= lang('loading_data_from_server') ?>
                                </td>
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

