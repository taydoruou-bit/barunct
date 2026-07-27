<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-file"></i><?= lang("quote_no") . '. ' . $inv->id; ?></h2>

        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-tasks tip" data-placement="left" title="<?= lang("actions") ?>"></i></a>
                    <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                        <?php if ($inv->attachment) { ?>
                            <li>
                                <a href="<?= site_url('welcome/download/' . $inv->attachment) ?>">
                                    <i class="fa fa-chain"></i> <?= lang('attachment') ?>
                                </a>
                            </li>
                        <?php } ?>
                        <li>
                            <a href="<?= site_url('quotes/edit/' . $inv->id) ?>">
                                <i class="fa fa-edit"></i> <?= lang('edit_quote') ?>
                            </a>
                        </li>
                        <li>
                            <a href="<?= site_url('sales/add/' . $inv->id) ?>">
                                <i class="fa fa-plus-circle"></i> <?= lang('create_invoice') ?>
                            </a>
                        </li>
                        <li>
                            <a href="<?= site_url('quotes/email/' . $inv->id) ?>" data-target="#myModal" data-toggle="modal">
                                <i class="fa fa-envelope-o"></i> <?= lang('send_email') ?>
                            </a>
                        </li>
                        <li>
                            <a href="<?= site_url('quotes/pdf/' . $inv->id . '/1') ?>" target="_blank">
                                <i class="fa fa-eye"></i> Xem thử báo giá
                            </a>
                        </li>
                        <li>
                            <a href="<?= site_url('quotes/pdf/' . $inv->id) ?>">
                                <i class="fa fa-file-pdf-o"></i> <?= lang('export_to_pdf') ?>
                            </a>
                        </li>
                        <!--<li><a href="<?= site_url('quotes/excel/' . $inv->id) ?>"><i class="fa fa-file-excel-o"></i> <?= lang('export_to_excel') ?></a></li>-->
                    </ul>
                </li>
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <div class="print-only col-xs-12">
                    <img src="<?= base_url() . 'assets/uploads/logos/' . $biller->logo; ?>" alt="<?= $biller->company != '-' ? $biller->company : $biller->name; ?>">
                </div>
                <div class="well well-sm">
                    <div class="col-xs-4 border-right">

                        <div class="col-xs-2"><i class="fa fa-3x fa-user padding010 text-muted"></i></div>
                        <div class="col-xs-10">
                            <h2 class=""><?= $customer->company ? $customer->company : $customer->name; ?></h2>
                            <?= $customer->company ? "" : "Attn: " . $customer->name ?>
                            <?php
                            echo $customer->address . "<br>" . $customer->city . " " . $customer->postal_code . " " . $customer->state . "<br>" . $customer->country;
                            echo "<p>";
                            if ($customer->vat_no != "-" && $customer->vat_no != "") {
                                echo "<br>" . lang("vat_no") . ": " . $customer->vat_no;
                            }
                            if ($customer->cf1 != "-" && $customer->cf1 != "") {
                                echo "<br>" . lang("ccf1") . ": " . $customer->cf1;
                            }
                            if ($customer->cf2 != "-" && $customer->cf2 != "") {
                                echo "<br>" . lang("ccf2") . ": " . $customer->cf2;
                            }
                            if ($customer->cf3 != "-" && $customer->cf3 != "") {
                                echo "<br>" . lang("ccf3") . ": " . $customer->cf3;
                            }
                            if ($customer->cf4 != "-" && $customer->cf4 != "") {
                                echo "<br>" . lang("ccf4") . ": " . $customer->cf4;
                            }
                            if ($customer->cf5 != "-" && $customer->cf5 != "") {
                                echo "<br>" . lang("ccf5") . ": " . $customer->cf5;
                            }
                            if ($customer->cf6 != "-" && $customer->cf6 != "") {
                                echo "<br>" . lang("ccf6") . ": " . $customer->cf6;
                            }
                            echo "</p>";
                            echo lang("tel") . ": " . $customer->phone . "<br>" . lang("email") . ": " . $customer->email;
                            ?>
                        </div>
                        <div class="clearfix"></div>

                    </div>
                    <div class="col-xs-4 border-right">

                        <div class="col-xs-2"><i class="fa fa-3x fa-building padding010 text-muted"></i></div>
                        <div class="col-xs-10">
                            <h2 class=""><?= $biller->company != '-' ? $biller->company : $biller->name; ?></h2>
                            <?= $biller->company ? "" : "Attn: " . $biller->name ?>
                            <?php
                            echo $biller->address . "<br>" . $biller->city . " " . $biller->postal_code . " " . $biller->state . "<br>" . $biller->country;
                            echo "<p>";
                            if ($biller->vat_no != "-" && $biller->vat_no != "") {
                                echo "<br>" . lang("vat_no") . ": " . $biller->vat_no;
                            }
                            if ($biller->cf1 != "-" && $biller->cf1 != "") {
                                echo "<br>" . lang("bcf1") . ": " . $biller->cf1;
                            }
                            if ($biller->cf2 != "-" && $biller->cf2 != "") {
                                echo "<br>" . lang("bcf2") . ": " . $biller->cf2;
                            }
                            if ($biller->cf3 != "-" && $biller->cf3 != "") {
                                echo "<br>" . lang("bcf3") . ": " . $biller->cf3;
                            }
                            if ($biller->cf4 != "-" && $biller->cf4 != "") {
                                echo "<br>" . lang("bcf4") . ": " . $biller->cf4;
                            }
                            if ($biller->cf5 != "-" && $biller->cf5 != "") {
                                echo "<br>" . lang("bcf5") . ": " . $biller->cf5;
                            }
                            if ($biller->cf6 != "-" && $biller->cf6 != "") {
                                echo "<br>" . lang("bcf6") . ": " . $biller->cf6;
                            }
                            echo "</p>";
                            echo lang("tel") . ": " . $biller->phone . "<br>" . lang("email") . ": " . $biller->email;
                            ?>
                        </div>
                        <div class="clearfix"></div>

                    </div>
                    <div class="col-xs-4">
                        <div class="col-xs-2"><i class="fa fa-3x fa-building-o padding010 text-muted"></i></div>
                        <div class="col-xs-10">
                            <h2 class=""><?= $Settings->site_name; ?></h2>
                            <?= $warehouse->name ?>
                            <?php
                            echo $warehouse->address . "<br>";
                            echo ($warehouse->phone ? lang("tel") . ": " . $warehouse->phone . "<br>" : '') . ($warehouse->email ? lang("email") . ": " . $warehouse->email : '');
                            ?>
                        </div>
                        <div class="clearfix"></div>
                    </div>
                    <div class="clearfix"></div>
                </div>
                <div class="clearfix"></div>
                <div class="col-xs-8 pull-right">
                    <div class="col-xs-12 text-right order_barcodes">
                        <?= $this->sma->save_barcode($inv->reference_no, 'code128', 66, false); ?>
                        <?= $this->sma->qrcode('link', urlencode(site_url('quotes/view/' . $inv->id)), 2); ?>
                    </div>
                    <div class="clearfix"></div>
                </div>

                <div class="col-xs-4">
                    <div class="col-xs-2"><i class="fa fa-3x fa-file-text-o padding010 text-muted"></i></div>
                    <div class="col-xs-10">
                        <h2 class=""><?= lang("ref"); ?>: <?= $inv->reference_no; ?></h2>

                        <p style="font-weight:bold;"><?= lang("date"); ?>
                            : <?= $this->sma->hrld($inv->date); ?></p>

                        <p style="font-weight:bold;"><?= lang("status"); ?>: <?= $inv->status; ?></p>

                        <p>&nbsp;</p>
                    </div>
                    <div class="clearfix"></div>
                </div>
                <div class="clearfix"></div>

                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-striped print-table order-table">
                        <thead>
    <tr>
        <th style="width:40px; text-align:center;">STT</th>
        <th style="width:120px; text-align:center;">Mẫu - Mã</th>
        <th style="width:120px; text-align:center;">Màu</th>
        <th style="width:120px; text-align:center;">Khóa</th>
        <!-- Custom columns -->
        <?php
        if (!empty($custom_columns)) {
            foreach ($custom_columns as $col) {
                echo '<th style="width:100px; text-align:center;">' . $col->column_name . '</th>';
            }
        }
        ?>
        <th style="width:100px; text-align:center;">Ghi chú</th>
        <th style="width:80px; text-align:center;"><?= lang("quantity"); ?></th>
        <th style="width:100px; text-align:center;"><?= lang("unit_price"); ?></th>
        <?php
        if ($Settings->tax1 && $inv->product_tax > 0) {
            echo '<th style="width:100px; text-align:center;">' . lang("tax") . '</th>';
        }
        if ($Settings->product_discount && $inv->product_discount != 0) {
            echo '<th style="width:100px; text-align:center;">' . lang("discount") . '</th>';
        }
        ?>
        <th style="width:120px; text-align:center;"><?= lang("subtotal"); ?></th>
    </tr>
</thead>
                        <tbody>
    <?php
    $r = 1;
    foreach ($rows as $group):
        $main_row = $group['main'];
        $color = $group['color'];
        $lock = $group['lock'];
        
        // Kiểm tra: Nếu không có color và lock, chỉ hiển thị một dòng (merge)
        $has_variants = ($color !== null || $lock !== null);
    ?>
        <tr>
            <!-- STT -->
            <td style="text-align:center; vertical-align:middle;"><?= $r; ?></td>

            <?php if ($has_variants): ?>
                <!-- TRƯỜNG HỢP CÓ COLOR/LOCK: Hiển thị bình thường -->
                <td style="text-align:center; vertical-align:middle; padding:8px;">
                    <div style="margin-bottom:8px;">
                        <strong><?= $main_row->product_code; ?></strong>
                        <?= $main_row->details ? '<br><small style="color:#777;">' . $main_row->details . '</small>' : ''; ?>
                    </div>
                    <?php
                    $image = $group['image'] && $group['image'] != 'no_image.png'
                        ? $group['image'] : 'no_image.png';
                    ?>
                    <div style="display:flex; justify-content:center; align-items:center;">
                        <img src="<?= base_url('assets/uploads/' . $image); ?>"
                            style="width:80px; height:80px; object-fit:cover; border:1px solid #ddd; border-radius:4px;">
                    </div>
                </td>

                <!-- Màu -->
                <td style="text-align:center; vertical-align:middle; padding:8px;">
                    <?php if ($color): ?>
                        <div style="margin-bottom:5px;">
                            <strong><?= $color->product_name; ?></strong>
                        </div>
                        <?php
                        $color_image = $color->image && $color->image != 'no_image.png'
                            ? $color->image : 'no_image.png';
                        ?>
                        <div style="display:flex; justify-content:center; align-items:center;">
                            <img src="<?= base_url('assets/uploads/' . $color_image); ?>"
                                style="width:50px; height:50px; object-fit:cover; border:1px solid #ddd; border-radius:3px;">
                        </div>
                    <?php else: ?>
                        <span style="color:#999;">-</span>
                    <?php endif; ?>
                </td>

                <!-- Khóa -->
                <td style="text-align:center; vertical-align:middle; padding:8px;">
                    <?php if ($lock): ?>
                        <div style="margin-bottom:5px;">
                            <strong><?= $lock->product_name; ?></strong>
                        </div>
                        <?php
                        $lock_image = $lock->image && $lock->image != 'no_image.png'
                            ? $lock->image : 'no_image.png';
                        ?>
                        <div style="display:flex; justify-content:center; align-items:center;">
                            <img src="<?= base_url('assets/uploads/' . $lock_image); ?>"
                                style="width:50px; height:50px; object-fit:cover; border:1px solid #ddd; border-radius:3px;">
                        </div>
                    <?php else: ?>
                        <span style="color:#999;">-</span>
                    <?php endif; ?>
                </td>

                <!-- Custom fields (khi có variants) -->
                <?php
                if (!empty($custom_columns)) {
                    foreach ($custom_columns as $col) {
                        $value = isset($main_row->custom_fields[$col->column_name])
                            ? $main_row->custom_fields[$col->column_name] : '';
                        echo '<td style="text-align:center; vertical-align:middle;">' . $value . '</td>';
                    }
                }
                ?>

                <!-- Ghi chú (khi có variants) -->
                <td style="text-align:center; vertical-align:middle;">
                    <?= !empty($main_row->notes) ? $main_row->notes : ''; ?>
                </td>

            <?php else: ?>
                <!-- TRƯỜNG HỢP KHÔNG CÓ COLOR/LOCK: MERGE TẤT CẢ CÁC CỘT LẠI -->
                <?php
                // TÍNH TOÁN SỐ CỘT CẦN MERGE
                $merge_cols = 3; // Mẫu-Mã + Màu + Khóa
                if (!empty($custom_columns)) {
                    $merge_cols += count($custom_columns);
                }
                $merge_cols += 1; // Ghi chú
                ?>
                <td colspan="<?= $merge_cols; ?>" style="text-align:right; vertical-align:middle; padding:8px;">
                    <strong><?= $main_row->product_name; ?></strong>
                    <?= $main_row->details ? '<br><small style="color:#777;">' . $main_row->details . '</small>' : ''; ?>
                    <?= !empty($main_row->notes) ? '<br><em style="color:#0066cc;">Ghi chú: ' . $main_row->notes . '</em>' : ''; ?>
                </td>
            <?php endif; ?>

            <!-- Số lượng -->
            <td style="text-align:center; vertical-align:middle;">
                <?php
                $total_qty = $main_row->unit_quantity;
                if ($color) $total_qty += $color->unit_quantity;
                if ($lock) $total_qty += $lock->unit_quantity;
                echo $this->sma->formatQuantity($total_qty) . ' ' . '';
                ?>
            </td>

            <!-- Đơn giá -->
            <td style="text-align:center; vertical-align:middle;">
                <?php
                $total_unit_price = $main_row->unit_price;
                if ($color) {
                    $total_unit_price += $color->unit_price;
                }
                if ($lock) {
                    $total_unit_price += $lock->unit_price;
                }
                echo $this->sma->formatMoney($total_unit_price);
                ?>
            </td>

            <!-- Thuế (nếu có) -->
            <?php if ($Settings->tax1 && $inv->product_tax > 0): ?>
                <td style="text-align:center; vertical-align:middle;">
                    <?php
                    $total_tax = $main_row->item_tax;
                    if ($color) $total_tax += $color->item_tax;
                    if ($lock) $total_tax += $lock->item_tax;
                    echo ($main_row->item_tax != 0 && $main_row->tax_code) ? '<small>(' . $main_row->tax_code . ')</small> ' : '';
                    echo $this->sma->formatMoney($total_tax);
                    ?>
                </td>
            <?php endif; ?>

            <!-- Giảm giá (nếu có) -->
            <?php if ($Settings->product_discount && $inv->product_discount != 0): ?>
                <td style="text-align:center; vertical-align:middle;">
                    <?php
                    $total_discount = $main_row->item_discount;
                    if ($color) $total_discount += $color->item_discount;
                    if ($lock) $total_discount += $lock->item_discount;
                    echo ($main_row->discount != 0) ? '<small>(' . $main_row->discount . ')</small> ' : '';
                    echo $this->sma->formatMoney($total_discount);
                    ?>
                </td>
            <?php endif; ?>

            <!-- Thành tiền -->
            <td style="text-align:center; vertical-align:middle;">
                <?php
                $total_subtotal = $main_row->subtotal;
                if ($color) {
                    $total_subtotal += $color->subtotal;
                }
                if ($lock) {
                    $total_subtotal += $lock->subtotal;
                }
                echo $this->sma->formatMoney($total_subtotal);
                ?>
            </td>
        </tr>
    <?php
        $r++;
    endforeach;
    ?>
</tbody>
                        <tfoot>
                        <?php
                        $col = 7; // STT + Mẫu-Mã + Màu + Khóa + Ghi chú + Quantity + Unit Price
if (!empty($custom_columns)) {
    $col += count($custom_columns);
}
if ($Settings->product_discount && $inv->product_discount != 0) {
    $col++;
}
if ($Settings->tax1 && $inv->product_tax > 0) {
    $col++;
}
                        if ($Settings->product_discount && $inv->product_discount != 0 && $Settings->tax1 && $inv->product_tax > 0) {
                            $tcol = $col - 2;
                        } elseif ($Settings->product_discount && $inv->product_discount != 0) {
                            $tcol = $col - 1;
                        } elseif ($Settings->tax1 && $inv->product_tax > 0) {
                            $tcol = $col - 1;
                        } else {
                            $tcol = $col;
                        }
                        ?>
                        <tr>
                            <td colspan="<?= $tcol; ?>"
                                style="text-align:right; padding-right:10px;"><?= lang("total"); ?>
                                (<?= $default_currency->code; ?>)
                            </td>
                            <?php
                            if ($Settings->tax1 && $inv->product_tax > 0) {
                                echo '<td style="text-align:right;">' . $this->sma->formatMoney($inv->product_tax) . '</td>';
                            }
                            if ($Settings->product_discount && $inv->product_discount != 0) {
                                echo '<td style="text-align:right;">' . $this->sma->formatMoney($inv->product_discount) . '</td>';
                            }
                            ?>
                            <td style="text-align:right; padding-right:10px;"><?= $this->sma->formatMoney($inv->total + $inv->product_tax); ?></td>
                        </tr>
                        <?php
                        if ($inv->order_discount != 0) {
                            echo '<tr><td colspan="' . $col . '" style="text-align:right; padding-right:10px;;">' . lang("order_discount") . ' (' . $default_currency->code . ')</td><td style="text-align:right; padding-right:10px;">'.($inv->order_discount_id ? '<small>('.$inv->order_discount_id.')</small> ' : '') . $this->sma->formatMoney($inv->order_discount) . '</td></tr>';
                        }
                        if ($Settings->tax2 && $inv->order_tax != 0) {
                            echo '<tr><td colspan="' . $col . '" style="text-align:right; padding-right:10px;;">' . lang("order_tax") . ' (' . $default_currency->code . ')</td><td style="text-align:right; padding-right:10px;">' . $this->sma->formatMoney($inv->order_tax) . '</td></tr>';
                        }
                        if ($inv->shipping != 0) {
                            echo '<tr><td colspan="' . $col . '" style="text-align:right; padding-right:10px;;">' . lang("shipping") . ' (' . $default_currency->code . ')</td><td style="text-align:right; padding-right:10px;">' . $this->sma->formatMoney($inv->shipping) . '</td></tr>';
                        }
                        ?>
                        <tr>
                            <td colspan="<?= $col; ?>"
                                style="text-align:right; padding-right:10px; font-weight:bold;"><?= lang("total_amount"); ?>
                                (<?= $default_currency->code; ?>)
                            </td>
                            <td style="text-align:right; padding-right:10px; font-weight:bold;"><?= $this->sma->formatMoney($inv->grand_total); ?></td>
                        </tr>

                        </tfoot>
                    </table>
                </div>

                <div class="row">
                    <div class="col-xs-7">
                        <?php if ($inv->note || $inv->note != "") { ?>
                            <div class="well well-sm">
                                <p class="bold"><?= lang("note"); ?>:</p>

                                <div><?= $this->sma->decode_html($inv->note); ?></div>
                            </div>
                        <?php } ?>
                    </div>

                    <div class="col-xs-4 col-xs-offset-1">
                        <div class="well well-sm">
                            <p><?= lang("created_by"); ?>
                                : <?= $created_by->first_name . ' ' . $created_by->last_name; ?> </p>

                            <p><?= lang("date"); ?>: <?= $this->sma->hrld($inv->date); ?></p>
                            <?php if ($inv->updated_by) { ?>
                                <p><?= lang("updated_by"); ?>
                                    : <?= $updated_by->first_name . ' ' . $updated_by->last_name;; ?></p>
                                <p><?= lang("update_at"); ?>: <?= $this->sma->hrld($inv->updated_at); ?></p>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php if (!$Supplier || !$Customer) { ?>
            <div class="buttons">
                <?php if ($inv->attachment) { ?>
                    <div class="btn-group">
                        <a href="<?= site_url('welcome/download/' . $inv->attachment) ?>" class="tip btn btn-primary" title="<?= lang('attachment') ?>">
                            <i class="fa fa-chain"></i>
                            <span class="hidden-sm hidden-xs"><?= lang('attachment') ?></span>
                        </a>
                    </div>
                <?php } ?>
                <div class="btn-group btn-group-justified">
                    <div class="btn-group">
                        <a href="<?= site_url('sales/add/' . $inv->id) ?>" class="tip btn btn-primary" title="<?= lang('create_invoice') ?>">
                            <i class="fa fa-plus-circle"></i> <span class="hidden-sm hidden-xs"><?= lang('create_invoice') ?></span>
                        </a>
                    </div>
                    <div class="btn-group">
                        <a href="<?= site_url('quotes/pdf/' . $inv->id . '/1') ?>" target="_blank" class="tip btn btn-success" title="Xem thử báo giá">
                            <i class="fa fa-eye"></i> <span class="hidden-sm hidden-xs">Xem thử</span>
                        </a>
                    </div>
                    <div class="btn-group">
                        <a href="<?= site_url('quotes/pdf/' . $inv->id) ?>" class="tip btn btn-primary" title="<?= lang('download_pdf') ?>">
                            <i class="fa fa-download"></i> <span class="hidden-sm hidden-xs"><?= lang('pdf') ?></span>
                        </a>
                    </div>
                    <div class="btn-group">
                        <a href="<?= site_url('quotes/email/' . $inv->id) ?>" data-toggle="modal" data-target="#myModal" class="tip btn btn-info tip" title="<?= lang('email') ?>">
                            <i class="fa fa-envelope-o"></i> <span class="hidden-sm hidden-xs"><?= lang('email') ?></span>
                        </a>
                    </div>
                    <div class="btn-group">
                        <a href="<?= site_url('quotes/edit/' . $inv->id) ?>" class="tip btn btn-warning tip" title="<?= lang('edit') ?>">
                            <i class="fa fa-edit"></i> <span class="hidden-sm hidden-xs"><?= lang('edit') ?></span>
                        </a>
                    </div>
                    <div class="btn-group">
                        <a href="#" class="tip btn btn-danger bpo" title="<b><?= $this->lang->line("delete_quote") ?></b>" 
                            data-content="<div style='width:150px;'><p><?= lang('r_u_sure') ?></p><a class='btn btn-danger' href='<?= site_url('quotes/delete/' . $inv->id) ?>'><?= lang('i_m_sure') ?></a> <button class='btn bpo-close'><?= lang('no') ?></button></div>" 
                            data-html="true" data-placement="top">
                            <i class="fa fa-trash-o"></i> <span class="hidden-sm hidden-xs"><?= lang('delete') ?></span>
                        </a>
                    </div>
                </div>
            </div>
        <?php } ?>
    </div>
</div>
<style>
    @media print {
        .order-table td img {
            width: 40px !important;
            height: 40px !important;
        }
    }
</style>
