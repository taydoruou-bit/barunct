<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>


<div class="modal-dialog" style="width:<?php echo $kich_thuoc;?>">	
	<?php $attrib = array();
        echo form_open("products/printkiem/" . $id, $attrib); ?>
    <div class="modal-content">
        <div class="modal-header no-print header_lhson_print">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
			 <button type="button" name="printsalelhson" onclick="inhoadon_set()" class="btn btn-primary"><i class="fa fa-print"></i> In hóa đơn</button>
            <h4 class="modal-title" id="myModalLabel"><?php echo "In Kiểm Kho"; ?></h4>
        </div>
        <div class="modal-body">
            <div class="print_value" style="margin:0px;padding:10px;width:calc(<?php echo $kich_thuoc;?> - 1cm)">
                <?php echo $note['noidung']; ?> 
            </div>
        </div> 
    </div>
    <?php echo form_close(); ?>
</div>
<?= $modal_js ?>
<style>
.print_value p{margin:0px;padding:0px;overflow-x: hidden;}
@page {
  margin: 0;
}
@media print {
    p{margin:0px;padding:0px;}
    html, body { overflow-x: hidden; }
}
</style>
<script type="text/javascript">
    setTimeout(function(){ window.print(); }, 2000);

    function inhoadon_set(){        
         setTimeout(function(){ window.print(); }, 1000);
    }

</script>