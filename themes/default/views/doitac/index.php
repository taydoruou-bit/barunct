<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script>
    $(document).ready(function () {
        oTable = $('#DoiData').dataTable({
            "aaSorting": [[1, "desc"], [2, "desc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?=lang('all')?>"]],
            "iDisplayLength": <?=$Settings->rows_per_page?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?=site_url('doitac/getDoitacs');?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?=$this->security->get_csrf_token_name()?>",
                    "value": "<?=$this->security->get_csrf_hash()?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            'fnRowCallback': function (nRow, aData, iDisplayIndex) {
                nRow.id = aData[0];
                nRow.className = "oreturn_link";
                return nRow;
            },
            "aoColumns": [{"bSortable": false,"mRender": checkbox}, {"mRender": fld}, null,null, null,null,null, null, {"mRender": currencyFormat}, {"bSortable": false,"mRender": attachment},{"bSortable": false}],
            "fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {
				
            }
        }).fnSetFilteringDelay().dtFilter([
            {column_number: 1, filter_default_label: "[<?=lang('date');?> (yyyy-mm-dd)]", filter_type: "text", data: []},
            {column_number: 2, filter_default_label: "[<?=lang('Mã');?>]", filter_type: "text", data: []},
            {column_number: 3, filter_default_label: "[<?=lang('Tên');?>]", filter_type: "text", data: []},
            {column_number: 4, filter_default_label: "[<?=lang('Địa chỉ');?>]", filter_type: "text", data: []},
			{column_number: 5, filter_default_label: "[<?=lang('Điện thoại');?>]", filter_type: "text", data: []}
        ], "footer");

        if (localStorage.getItem('remove_rels')) {
            if (localStorage.getItem('doiitems')) {
                localStorage.removeItem('doiitems');
            }
            if (localStorage.getItem('reref')) {
                localStorage.removeItem('reref');
            }
            if (localStorage.getItem('redate')) {
                localStorage.removeItem('redate');
            }
            localStorage.removeItem('remove_rels');
        }

        <?php if ($this->session->userdata('remove_rels')) {?>
        if (localStorage.getItem('doiitems')) {
                localStorage.removeItem('doiitems');
            }
            if (localStorage.getItem('reref')) {
                localStorage.removeItem('reref');
            }
            if (localStorage.getItem('renote')) {
                localStorage.removeItem('renote');
            }
            if (localStorage.getItem('redate')) {
                localStorage.removeItem('redate');
            }
        <?php $this->sma->unset_data('remove_rels');}
        ?>

        $(document).on('click', '.reedit', function (e) {
            if (localStorage.getItem('doiitems')) {
                e.preventDefault();
                var href = $(this).attr('href');
                bootbox.confirm("<?=lang('you_will_loss_return_data')?>", function (result) {
                    if (result) {
                        window.location.href = href;
                    }
                });
            }
        });
    });

</script>

<?php if ($Owner || $GP['bulk_actions']) {
        echo form_open('doitac/doitac_actions', 'id="action-form"');
    }
?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i
                class="fa-fw fa fa-random"></i><?php echo ' Đối tác';?>
        </h2>

        <div class="box-icon">
            <ul class="btn-tasks">                
                
				<div class="main-task-lhson">
					<a class="btn btn-primary btncls" href="<?= site_url('doitac/add') ?>">
						<i class="fa fa-plus-circle"></i> Thêm đối tác
					</a>
					<a href="#" class="bpo btn btn-primary btncls" title="<b>Xóa</b>" data-content="<p><?=lang('r_u_sure')?></p><button type='button' class='btn btn-danger' id='delete' data-action='delete'><?=lang('i_m_sure')?></a> <button class='btn bpo-close'><?=lang('no')?></button>" data-html="true" data-placement="left">
                                <i class="fa fa-trash-o"></i> Xóa
                            </a>
				</div>
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <div class="table-responsive">
                    <table id="DoiData" class="table table-bordered table-hover table-striped no-print">
                        <thead>
                        <tr>
                            <th style="min-width:30px; width: 30px; text-align: center;">
                                <input class="checkbox checkft" type="checkbox" name="check"/>
                            </th>
                            <th><?= lang("date"); ?></th>
                            <th><?= lang("Mã"); ?></th>
                            <th><?= lang("Tên"); ?></th>
							<th><?= lang("Địa chỉ"); ?></th>
                            <th><?= lang("Điện thoại"); ?></th>
                            <th><?= lang("Email"); ?></th>
							<th><?= lang("Ghi chú"); ?></th>
							<th><?= lang("Nợ đầu kỳ"); ?></th>
							<th style="min-width:30px; width: 30px; text-align: center;"><i class="fa fa-chain"></i></th>
                            <th style="width:80px; text-align:center;"><?= lang("actions"); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="11" class="dataTables_empty"><?= lang("loading_data"); ?></td>
                        </tr>
                        </tbody>
                        <tfoot class="dtFilter">
                        <tr class="active">
                            <th style="min-width:30px; width: 30px; text-align: center;">
                                <input class="checkbox checkft" type="checkbox" name="check"/>
                            </th>
                            <th></th><th></th><th></th>								
							<th></th>
							<th style="min-width:100px; width: 100px; text-align: center;"></th>
							<th></th>
							<th></th>    
								
							<th style="min-width:100px; width: 100px; text-align: center;"></th>	
							<th style="min-width:30px; width: 30px; text-align: center;"><i class="fa fa-chain"></i></th>							
                            <th style="width:80px; text-align:center;"><?= lang("actions"); ?></th>
                        </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php if ($Owner || $GP['bulk_actions']) {?>
    <div style="display: none;">
        <input type="hidden" name="form_action" value="" id="form_action"/>
        <?=form_submit('performAction', 'performAction', 'id="action-form-submit"')?>
    </div>
    <?=form_close()?>
<?php }
?>
