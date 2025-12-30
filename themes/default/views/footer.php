<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="clearfix"></div>
<?= '</div></div></div></div>'; ?>

<div class="clearfix"></div>
<div class="modal fade" id="popup-quangcao" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content" style="float:left;width: 100%;">
            <div class="modal-header">
                <button type="button" id="clickHomeQc" class="close" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body" style="float:left;width: 100%;padding: 0px;">
                <div class="col-md-12" id="img-quangcao" style="text-align: center;padding: 0px;">
                    
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade in" id="myHistoryFull" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content" style="float:left;width: 100%;">
            <div class="modal-header">
                <button type="button" id="viewFullHistory" class="close" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body" style="float:left;width: 100%;padding: 0px;">
                <div class="col-md-12" id="modal_history_full" style="padding: 10px;">
                    
                </div>
                <input type="hidden" name="modal_history_full_id" id="modal_history_full_id" value="0">
                <input type="hidden" name="modal_history_full_loai" id="modal_history_full_loai" value="">
            </div>
        </div>
    </div>
</div>

<footer>
<a href="#" id="toTop" class="blue" style="position: fixed; bottom: 30px; right: 30px; font-size: 30px; display: none;">
    <i class="fa fa-chevron-circle-up"></i>
</a>

    <p style="text-align:center;" class="no-print">&copy; <?= date('Y') . " " . $Settings->site_name; ?> - <span><b>Power by <a href="https://alphasoftware.vn/" target="_blank"><font color="#ff0000">Alpha</font><font color="#fff200">group</font><font color="#ff0000">.vn</font></a></b></span>
        <?php if ($_SERVER["REMOTE_ADDR"] == '127.0.0.1') {
            echo ' - Page rendered in <strong>{elapsed_time}</strong> seconds';
        } ?></p>
</footer>
<?= '</div>'; ?>
<div class="modal fade in " id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true"></div>
<div class="modal fade in" id="myModal2" tabindex="-1" role="dialog" aria-labelledby="myModalLabel2" aria-hidden="true"></div>
<div id="modal-loading" class="no-print" style="display: none;">
    <div class="blackbg"></div>
    <div class="loader"></div>
</div>


<?php unset($Settings->setting_id, $Settings->smtp_user, $Settings->smtp_pass, $Settings->smtp_port, $Settings->update, $Settings->reg_ver, $Settings->allow_reg, $Settings->default_email, $Settings->mmode, $Settings->timezone, $Settings->restrict_calendar, $Settings->restrict_user, $Settings->auto_reg, $Settings->reg_notification, $Settings->protocol, $Settings->mailpath, $Settings->smtp_crypto, $Settings->corn, $Settings->customer_group, $Settings->scodeweb_username, $Settings->purchase_code); ?>

<script type="text/javascript">

var dt_lang = <?=$dt_lang?>;
var dp_lang = <?=$dp_lang?>;
var site = <?=json_encode(array('base_url' => base_url(), 'settings' => $Settings, 'dateFormats' => $dateFormats));?>;

var lang = {paid: '<?=lang('paid');?>', pending: '<?=lang('pending');?>', completed: '<?=lang('completed');?>', ordered: '<?=lang('ordered');?>', received: '<?=lang('received');?>', partial: '<?=lang('partial');?>', sent: '<?=lang('sent');?>', r_u_sure: '<?=lang('r_u_sure');?>', due: '<?=lang('due');?>', returned: '<?=lang('returned');?>', transferring: '<?=lang('transferring');?>', active: '<?=lang('active');?>', inactive: '<?=lang('inactive');?>', unexpected_value: '<?=lang('unexpected_value');?>', select_above: '<?=lang('select_above');?>', download: '<?=lang('download');?>'};
</script>
<?php
$s2_lang_file = read_file('./assets/config_dumps/s2_lang.js');
foreach (lang('select2_lang') as $s2_key => $s2_line) {
    $s2_data[$s2_key] = str_replace(array('{', '}'), array('"+', '+"'), $s2_line);
}
$s2_file_date = $this->parser->parse_string($s2_lang_file, $s2_data, true);
?>
<script type="text/javascript" src="<?= $assets ?>ace/js/ace-elements.min.js"></script>

<script type="text/javascript" src="<?= $assets ?>js/bootstrap.min.js"></script>

<script type="text/javascript" src="<?= $assets ?>js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="<?= $assets ?>js/jquery.dataTables.dtFilter.min.js"></script>
<script type="text/javascript" src="<?= $assets ?>js/select2.min.js"></script>
<script type="text/javascript" src="<?= $assets ?>js/jquery-ui.min.js"></script>
<script type="text/javascript" src="<?= $assets ?>js/bootstrapValidator.min.js"></script>
<script type="text/javascript" src="<?= $assets ?>js/custom.js"></script>
<script type="text/javascript" src="<?= $assets ?>js/jquery.calculator.min.js"></script>
<script type="text/javascript" src="<?= $assets ?>js/core.js"></script>
<script type="text/javascript" src="<?= $assets ?>js/perfect-scrollbar.min.js"></script>
<!-- Custom scripts for all pages-->
<script src="<?= $assets ?>ace/js/ace.min.js"></script>

<?= ($m == 'purchases' && ($v == 'add' || $v == 'purchase_by_csv')) ? '<script type="text/javascript" src="' . $assets . 'js/purchases.js"></script>' : ''; ?>
<?= ($m == 'purchases' && $v == 'edit') ? '<script type="text/javascript" src="' . $assets . 'js/purchases-edit.js"></script>' : ''; ?>

<?= ($m == 'purchases' && $v == 'return_purchase_ncc') ? '<script type="text/javascript" src="' . $assets . 'js/purchases_ncc.js"></script>' : ''; ?>

<?= ($m == 'transfers' && ($v == 'add' || $v == 'edit')) ? '<script type="text/javascript" src="' . $assets . 'js/transfers.js"></script>' : ''; ?>

<?= ($m == 'doitac') ? '<script type="text/javascript" src="' . $assets . 'js/doitac.js"></script>' : ''; ?>

<?= ($m == 'sales' && $v == 'add') ? '<script type="text/javascript" src="' . $assets . 'js/sales.js"></script>' : ''; ?>
<?= ($m == 'sales' && $v == 'addthuhoi') ? '<script type="text/javascript" src="' . $assets . 'js/sales-thuhoi.js"></script>' : ''; ?>
<?= ($m == 'sales' && $v == 'edit') ? '<script type="text/javascript" src="' . $assets . 'js/sales-edit.js"></script>' : ''; ?>
<?= ($m == 'sales' && $v == 'editthuhoi') ? '<script type="text/javascript" src="' . $assets . 'js/edit-thuhoi.js"></script>' : ''; ?>

<?= ($m == 'returns' && ($v == 'add' || $v == 'edit')) ? '<script type="text/javascript" src="' . $assets . 'js/returns.js"></script>' : ''; ?>
<?= ($m == 'quotes' && ($v == 'add' || $v == 'edit')) ? '<script type="text/javascript" src="' . $assets . 'js/quotes.js"></script>' : ''; ?>
<?= ($m == 'products' && ($v == 'add_adjustment' || $v == 'edit_adjustment')) ? '<script type="text/javascript" src="' . $assets . 'js/adjustments.js"></script>' : ''; ?>

<script type="text/javascript" charset="UTF-8">var oTable = '', r_u_sure = "<?=lang('r_u_sure')?>";
    <?=$s2_file_date?>
    $.extend(true, $.fn.dataTable.defaults, {"oLanguage":<?=$dt_lang?>});
    $.fn.datetimepicker.dates['sma'] = <?=$dp_lang?>;
    $(window).load(function () {
		jQuery(".no-skin .nav-list > li").click(function(){
			var obj=this;
			jQuery(".no-skin .nav-list > li").each(function(){
				if(this!=obj){
					jQuery(this).find("ul").removeAttr("style");	
				}
			});
		});
		jQuery(".no-skin .nav-list > li").each(function(){			
			jQuery(this).find("ul li").each(function(){
				var url=jQuery(this).find("a").attr("href");
				if(url=='<?php echo current_url();?>'){
					jQuery(this).parent().parent().addClass("active");
				}
			});			
		});
		
		$('#is_active_tmdt').on('ifChecked', function (e) {
	        $('#shopinfo').slideDown();
	        $("#btn-lienket a").slideDown();
	    });
	     $('#is_active_tmdt').on('ifUnchecked', function () {
	        $('#shopinfo').slideUp();
	        $("#btn-lienket a").slideUp();
	    });
    });
	jQuery(window).scroll(function (e) {
		if (jQuery(window).scrollTop() > 5) {
			jQuery(".sidebar.sidebar-fixed").attr("style","top:0px;");
		}else{
			jQuery(".sidebar.sidebar-fixed").removeAttr("style");
		}
	});
</script>
<script type="text/javascript">
    $.ajax({
            url : "<?=base_url()?>Auth/ThongBaoPopup",
            type : "post",  
            data:{'<?=$this->security->get_csrf_token_name();?>':'<?=$this->security->get_csrf_hash();?>'},          
            success : function (result)
            {   
                if (result!='') {
                    $tb=JSON.parse(result);
                    if ($tb!=undefined && $tb.ID>0) {
                        
                        $("#popup-quangcao #clickHomeQc").attr('onclick','dongQuangCao('+$tb.ID+')');
                        $("#popup-quangcao #img-quangcao").html('<a style="float:left;width:100%" target="_blank" href="'+$tb.lienket+'"><img style="width:100%;height:auto;max-width:100%;max-height:750px;" src="'+$tb.hinhanh+'" alt="Quảng cáo"/></a>');
                        $("#popup-quangcao").modal('show');
                    }
                }                

            }
        });
    function dongQuangCao($id=0) {	    
        $.ajax({
        	url : "<?=base_url()?>Auth/cms_close_quangcao/"+$id,
            type : "post",  
            data:{'<?=$this->security->get_csrf_token_name();?>':'<?=$this->security->get_csrf_hash();?>'},  
	        success : function (result)
            {  
	        	console.log(result);
            }
	    });
	}
</script>
</body>


</html>
