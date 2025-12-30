<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php if ($modal) { ?>
<div class="modal-dialog no-modal-header" role="document"><div class="modal-content"><div class="modal-body">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i></button>
    <?php 
} else {
    ?><!doctype html>
    <html>
    <head>
        <meta charset="utf-8">
        <title><?=$page_title . " " . lang("no") . " " . $inv->id;?></title>
        <base href="<?=base_url()?>"/>
        <meta http-equiv="cache-control" content="max-age=0"/>
        <meta http-equiv="cache-control" content="no-cache"/>
        <meta http-equiv="expires" content="0"/>
        <meta http-equiv="pragma" content="no-cache"/>
        <link rel="shortcut icon" href="<?=$assets?>images/icon.png"/>
        <link rel="stylesheet" href="<?=$assets?>styles/theme.css" type="text/css"/>
        <style type="text/css" media="all">
            body { color: #000;font-size:13px;}
            .print_value p{
                margin:0px;
                padding:0px;
            }
            table tr td {
            font-size: 13px;
            padding: 0px 2px!important;
        }
            #wrapper { max-width: 480px; margin: 0 auto; padding-top: 20px; }
            .btn { border-radius: 0; margin-bottom: 5px; }
            .bootbox .modal-footer { border-top: 0; text-align: center; }
            h3 { margin: 5px 0; }
            .order_barcodes img { float: none !important; margin-top: 5px; }
            @media print {
                .no-print { display: none; }
                #wrapper {width: 100%; min-width: 250px; margin: 0 auto; }
                .no-border { border: none !important; }
                .border-bottom { border-bottom: 1px solid #ddd !important; }
            }
        </style>
    </head>

    <body>
        <?php 
    } ?>
    <div id="wrapper">
        <div id="receiptData">
            <div class="no-print">
                <?php 
                if ($message) { 
                    ?>
                    <div class="alert alert-success">
                        <button data-dismiss="alert" class="close" type="button">×</button>
                        <?=is_array($message) ? print_r($message, true) : $message;?>
                    </div>
                    <?php 
                } ?>
            </div>
            <div id="receipt-data">               
                <div class="print_value" id="print_value" style="margin:0px;padding:0px;width:<?php echo $kich_thuoc;?>">
                <?php echo $note['noidung']; ?> 
                </div>         
            </div>
            <div style="clear:both;"></div>
        </div>

        <div id="buttons" style="padding-top:10px; text-transform:uppercase;" class="no-print">
            <hr>
            <?php 
            if ($message) { 
                ?>
                <div class="alert alert-success">
                    <button data-dismiss="alert" class="close" type="button">×</button>
                    <?=is_array($message) ? print_r($message, true) : $message;?>
                </div>
                <?php 
            } ?>
            <?php 
            if ($modal) {
                ?>
                <div class="btn-group btn-group-justified" role="group" aria-label="...">
                    <div class="btn-group" role="group">
                        <?php
                        if ($pos->remote_printing == 1) {
                            echo '<button onclick="batdauin();" class="btn btn-block btn-primary">'.lang("print").'</button>';
                        } else {
                            echo '<button onclick="return printReceipt()" class="btn btn-block btn-primary">'.lang("print").'</button>';
                        }

                        ?>
                    </div>
                    <div class="btn-group" role="group">
                        <a class="btn btn-block btn-success" href="#" id="email"><?= lang("email"); ?></a>
                    </div>
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-default" data-dismiss="modal"><?= lang('close'); ?></button>
                    </div>
                </div>
                <?php 
            } else { 
                ?>
                <span class="pull-right col-xs-12">
                    <?php 
                    if ($pos->remote_printing == 1) {
                        echo '<button onclick="batdauin()" class="btn btn-block btn-primary">'.lang("print").'</button>';
                    } else {
                        echo '<button onclick="return printReceipt()" class="btn btn-block btn-primary">'.lang("print").'</button>';
                        echo '<button onclick="return openCashDrawer()" class="btn btn-block btn-default">'.lang("open_cash_drawer").'</button>';
                    }
                    ?>
                </span>
                <span class="pull-left col-xs-12"><a class="btn btn-block btn-success" href="#" id="email"><?= lang("email"); ?></a></span>
                <span class="col-xs-12">
                    <a class="btn btn-block btn-warning" href="<?= site_url('pos'); ?>"><?= lang("back_to_pos"); ?></a>
                </span>
                <?php 
            }?>
            <div style="clear:both;"></div>
        </div>
    </div>
    <script>
        function batdauin(){
            var size='<?php echo $kich_thuoc;?>';
            var data=document.getElementById("print_value").innerHTML;      
            var mywindow = window.open('', 'new div','width=650,height=650,top=50,left=50,toolbars=no,scrollbars=yes,status=no,resizable=yes');
            
            mywindow.document.write('<html><head><title>Hóa đơn bán hàng</title><style>body{margin-block-start:0px;margin-block-end:0px;margin:0px;padding:0px;}img{width:100%}</style>');
                    
            mywindow.document.write('</head><body style="width:<?php echo $kich_thuoc;?>;margin:0px;padding:0px;">');
            mywindow.document.write(data);          
            mywindow.document.write('</body></html>');
            mywindow.focus();           
            setTimeout(() => { mywindow.print();mywindow.close(); }, 1000);
            
        }
        </script>
    <?php
    if( ! $modal) {
        ?>
        <script type="text/javascript" src="<?= $assets ?>js/jquery-2.0.3.min.js"></script>
        <script type="text/javascript" src="<?= $assets ?>js/bootstrap.min.js"></script>
        <script type="text/javascript" src="<?= $assets ?>js/jquery.dataTables.min.js"></script>
        <script type="text/javascript" src="<?= $assets ?>js/custom.js"></script>
        <?php
    }
    ?>
    <script type="text/javascript">
        $(document).ready(function () {
            var size='<?php echo $kich_thuoc;?>';
            var data=document.getElementById("print_value").innerHTML;      
            var mywindow = window.open('', 'new div','width=650,height=650,top=50,left=50,toolbars=no,scrollbars=yes,status=no,resizable=yes');
            
            mywindow.document.write('<html><head><title>Hóa đơn bán hàng</title><style>body{margin-block-start:0px;margin-block-end:0px;margin:0px;padding:0px;}img{width:100%}</style>');
                    
            mywindow.document.write('</head><body style="width:<?php echo $kich_thuoc;?>;margin:0px;padding:0px;">');
            mywindow.document.write(data);          
            mywindow.document.write('</body></html>');
            mywindow.focus();           
            setTimeout(() => { mywindow.print();mywindow.close(); }, 1000);
            
            $('#myModal').modal('hide');
            
            $('#email').click(function () {
                bootbox.prompt({
                    title: "<?= lang("email_address"); ?>",
                    inputType: 'email',
                    value: "<?= $customer->email; ?>",
                    callback: function (email) {
                        if (email != null) {
                            $.ajax({
                                type: "post",
                                url: "<?= site_url('pos/email_receipt') ?>",
                                data: {<?= $this->security->get_csrf_token_name(); ?>: "<?= $this->security->get_csrf_hash(); ?>", email: email, id: <?= $inv->id; ?>},
                                dataType: "json",
                                success: function (data) {
                                    bootbox.alert({message: data.msg, size: 'small'});
                                },
                                error: function () {
                                    bootbox.alert({message: '<?= lang('ajax_request_failed'); ?>', size: 'small'});
                                    return false;
                                }
                            });
                        }
                    }
                });
                return false;
            });
        });

        <?php
        if ($pos_settings->remote_printing == 1) {
            ?>
            $(window).load(function () {
                //window.print();
               // return false;
            });
            <?php
        }
        ?>

    </script>
    <?php /* include FCPATH.'themes'.DIRECTORY_SEPARATOR.$Settings->theme.DIRECTORY_SEPARATOR.'views'.DIRECTORY_SEPARATOR.'pos'.DIRECTORY_SEPARATOR.'remote_printing.php'; */ ?>
    <?php include 'remote_printing.php'; ?>
    <?php
    if($modal) {
        ?>
    </div>
</div>
</div>
<?php 
} else {
    ?>
</body>
</html>
<?php
}
?>