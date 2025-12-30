<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script type="text/javascript">
    $(function () {
       // $(".toggle_form").slideDown('hide');
		window.print();
		//jQuery("#myModal").slideDown("hide");
    });
	function inhoadon_set(){
		
		window.print();
	}
</script>

<div class="modal-dialog" style="width:<?php echo $kich_thuoc;?>">	
	<?php $attrib = array();
        echo form_open("purchases/printphieuchi/" . $id, $attrib); ?>
    <div class="modal-content">
        <div class="modal-header no-print header_lhson_print">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
			 <button type="button" name="printphieuchi" onclick="inhoadon_set()" class="btn btn-primary"><i class="fa fa-print"></i> In Phiếu Thanh Toán</button>
            <h4 class="modal-title" id="myModalLabel">In Phiếu Thanh Toán Cho Nhà Cung Cấp</h4>
        </div>
        <div class="modal-body">
            <div class="print_value" style="margin:0px;padding:10px;width:calc(<?php echo $kich_thuoc;?> -1cm)">
                <?php echo $note['noidung']; ?> 
            </div>
        </div> 
    </div>
    <?php echo form_close(); ?>
</div>
<?= $modal_js ?>
<style>
.print_value p{margin:0px;padding:0px;}
@page {
  margin: 0;
}
@media print {
	p{margin:0px;padding:0px;}
}
</style>