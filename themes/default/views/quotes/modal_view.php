<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="modal-dialog modal-lg no-modal-header">
    <div class="modal-content">
        <div class="modal-body">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                <i class="fa fa-2x">&times;</i>
            </button>
            <button type="button" class="btn btn-xs btn-default no-print pull-right" style="margin-right:15px;" onclick="window.print();">
                <i class="fa fa-print"></i> <?= lang('print'); ?>
            </button>
            <?php if ($logo) { ?>
    <div style=" padding: 15px; margin-bottom: 20px;">
        <div style="display: flex; align-items: center; justify-content: space-between;">
            <!-- Logo bên trái -->
            <div style="flex: 0 0 150px;">
                <img src="<?= base_url() . 'assets/uploads/logos/' . $biller->logo; ?>"
                    alt="<?= $biller->company != '-' ? $biller->company : $biller->name; ?>"
                    style="max-width: 150px; height: auto;">
            </div>
            
            <!-- Thông tin công ty bên phải -->
            <div style="flex: 1; text-align: center; padding-left: 20px;">
                <h2 style="color: #dc143c; margin: 0 0 8px 0; font-size: 18px; font-weight: bold; text-transform: uppercase; line-height: 1.4;">
                    CÔNG TY TNHH BARUN VIỆT NAM - VĂN PHÒNG ĐẠI DIỆN MIỀN TÂY
                </h2>
                <p style="margin: 0; font-size: 13px; line-height: 1.8;">
                    <strong>Địa chỉ:</strong> 165 đường D4, KDC Hồng Loan, P Hưng Thạnh, Q Cái Răng, TP Cần Thơ<br>
                    <strong>Điện thoại:</strong> 0763.882.285 (Văn phòng) - 0917.225.931 (Ms Hằng)
                </p>
            </div>
        </div>
    </div>
<?php } ?>
      <!-- Tiêu đề báo giá -->
<div style="text-align: center; margin: 20px 0;">
    <h1 style="color: #0066cc; font-size: 24px; font-weight: bold; text-transform: uppercase; margin: 0;">
        BẢNG BÁO GIÁ KIÊM XÁC NHẬN ĐẶT HÀNG
    </h1>
</div>

<!-- Thông tin đơn hàng và khách hàng -->
<div class="well well-sm">
    <div class="row">
        <!-- Cột trái: Thông tin đơn hàng -->
        <div class="col-xs-6">
            <p style="margin: 0; line-height: 1.8;">
                <strong><?= lang("ref"); ?>:</strong> <?= $inv->reference_no; ?><br>
                <strong><?= lang("date"); ?>:</strong> <?= $this->sma->hrld($inv->date); ?><br>
                <strong><?= lang("status"); ?>:</strong> <?= $inv->status; ?>
            </p>
        </div>
        
        <!-- Cột phải: Barcode & QR -->
        <div class="col-xs-6 text-right order_barcodes">
            <?= $this->sma->save_barcode($inv->reference_no, 'code128', 66, false); ?>
            <?= $this->sma->qrcode('link', urlencode(site_url('quotes/view/' . $inv->id)), 2); ?>
        </div>
    </div>
</div>

<!-- Thông tin khách hàng -->
<div class="well well-sm" style="background-color: #f9f9f9;">
    <div class="row">
        <div class="col-xs-12">
            <p style="margin: 0; line-height: 1.8;">
                <strong style="font-size: 16px;">Khách hàng: </strong>
<span style="font-size: 15px;"><?= $customer->company ? $customer->company : $customer->name; ?></span>
<?php if ($customer->company && $customer->name != $customer->company): ?>
    - <?= $customer->name; ?>
<?php endif; ?>
                <br>
                <strong>Địa chỉ:</strong> <?php
    $address_parts = [];
    if (!empty($customer->address) && $customer->address != '-') {
        $address_parts[] = $customer->address;
    }
    
    $city_state = trim($customer->city . ' ' . $customer->postal_code . ' ' . $customer->state);
    if (!empty($city_state) && $city_state != '-') {
        $address_parts[] = $city_state;
    }
    
    if (!empty($customer->country) && $customer->country != '-') {
        $address_parts[] = $customer->country;
    }
    
    echo implode(', ', $address_parts);
?>
                <br>
                <strong>Điện thoại:</strong> <?= $customer->phone; ?>
                <?php if ($customer->email): ?>
                    | <strong>Email:</strong> <?= $customer->email; ?>
                <?php endif; ?>
                
                <?php
                // Hiển thị các trường custom nếu có
                $custom_info = [];
                if ($customer->vat_no != "-" && $customer->vat_no != "") {
                    $custom_info[] = lang("vat_no") . ": " . $customer->vat_no;
                }
                if ($customer->cf1 != "-" && $customer->cf1 != "") {
                    $custom_info[] = lang("ccf1") . ": " . $customer->cf1;
                }
                if ($customer->cf2 != "-" && $customer->cf2 != "") {
                    $custom_info[] = lang("ccf2") . ": " . $customer->cf2;
                }
                if ($customer->cf3 != "-" && $customer->cf3 != "") {
                    $custom_info[] = lang("ccf3") . ": " . $customer->cf3;
                }
                if ($customer->cf4 != "-" && $customer->cf4 != "") {
                    $custom_info[] = lang("ccf4") . ": " . $customer->cf4;
                }
                if ($customer->cf5 != "-" && $customer->cf5 != "") {
                    $custom_info[] = lang("ccf5") . ": " . $customer->cf5;
                }
                if ($customer->cf6 != "-" && $customer->cf6 != "") {
                    $custom_info[] = lang("ccf6") . ": " . $customer->cf6;
                }
                
                if (!empty($custom_info)) {
                    echo "<br>" . implode(" | ", $custom_info);
                }
                ?>
            </p>
        </div>
    </div>
</div>

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
                            <th style="width:100px; text-align:center;">Ghi chú</th> <!-- ← THÊM DÒNG NÀY -->
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

                    <!-- ============ CHỈNH SỬA FILE: modal_view.php ============ -->
<!-- TÌM PHẦN TBODY CỦA TABLE VÀ THAY TOÀN BỘ ĐOẠN NÀY: -->

<!-- ============ CHỈNH SỬA FILE: modal_view.php ============ -->
<!-- TÌM PHẦN TBODY CỦA TABLE VÀ THAY TOÀN BỘ ĐOẠN NÀY: -->

<tbody>
    <?php
    $r = 1;
    foreach ($rows as $group):
        $main_row = $group['main'];
        $color = $group['color'];
        $lock = $group['lock'];
        
        // ✅ KIỂM TRA: Nếu không có color và lock, chỉ hiển thị một dòng (merge)
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
                // ✅ TÍNH TOÁN SỐ CỘT CẦN MERGE
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
    // ✅ CHỈ LẤY SỐ LƯỢNG TỪ MAIN, KHÔNG CỘNG COLOR VÀ LOCK
    echo $this->sma->formatQuantity($main_row->unit_quantity);
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
                    <?php
                    // TÍNH TOÁN SỐ CỘT COLSPAN
                    $col = 7; // STT + Mẫu-Mã + Màu + Khóa + Quantity + Unit Price
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
                    <?php if ($inv->grand_total != $inv->total) { ?>
                        <tr>
                            <td colspan="<?= $tcol; ?>"
                                style="text-align:center; padding-right:10px;"><?= lang("total"); ?>
                                (<?= $default_currency->code; ?>)
                            </td>
                            <?php
                            if ($Settings->tax1 && $inv->product_tax > 0) {
                                echo '<td style="text-align:center;">' . $this->sma->formatMoney($inv->product_tax) . '</td>';
                            }
                            if ($Settings->product_discount && $inv->product_discount != 0) {
                                echo '<td style="text-align:center;">' . $this->sma->formatMoney($inv->product_discount) . '</td>';
                            }
                            ?>
                            <td style="text-align:center; padding-right:10px;"><?= $this->sma->formatMoney($inv->total + $inv->product_tax); ?></td>
                        </tr>
                    <?php } ?>

                    <?php if ($inv->order_discount != 0) {
                        echo '<tr><td colspan="' . $col . '" style="text-align:center; padding-right:10px;">' . lang("order_discount") . ' (' . $default_currency->code . ')</td><td style="text-align:center; padding-right:10px;">' . ($inv->order_discount_id ? '<small>(' . $inv->order_discount_id . ')</small> ' : '') . $this->sma->formatMoney($inv->order_discount) . '</td></tr>';
                    }
                    ?>
                    <?php if ($Settings->tax2 && $inv->order_tax != 0) {
                        echo '<tr><td colspan="' . $col . '" style="text-align:center; padding-right:10px;">' . lang("order_tax") . ' (' . $default_currency->code . ')</td><td style="text-align:center; padding-right:10px;">' . $this->sma->formatMoney($inv->order_tax) . '</td></tr>';
                    }
                    ?>
                    <?php if ($inv->shipping != 0) {
                        echo '<tr><td colspan="' . $col . '" style="text-align:center; padding-right:10px;">' . lang("shipping") . ' (' . $default_currency->code . ')</td><td style="text-align:center; padding-right:10px;">' . $this->sma->formatMoney($inv->shipping) . '</td></tr>';
                    }
                    ?>
                    <tr>
                        <td colspan="<?= $col; ?>"
                            style="text-align:center; font-weight:bold;"><?= lang("total_amount"); ?>
                            (<?= $default_currency->code; ?>)
                        </td>
                        <td style="text-align:center; padding-right:10px; font-weight:bold;"><?= $this->sma->formatMoney($inv->grand_total); ?></td>
                    </tr>
                    </tfoot>
                </table>
            </div>

            <div class="row">
                <div class="col-xs-12">
                    <?php
                    if ($inv->note || $inv->note != "") { ?>
                        <div class="well well-sm">
                            <p class="bold"><?= lang("note"); ?>:</p>
                            <div><?= $this->sma->decode_html($inv->note); ?></div>
                        </div>
                    <?php } ?>
                </div>

                
            </div>

            <div style="margin-top: 30px; padding: 20px; border-top: 2px solid #ddd;">
                <!-- Thông tin chuyển tiền - 1 dòng -->
                <div style="margin-bottom: 15px;">
                    <p style="margin: 0; font-size: 13px; line-height: 1.6;">
                        <strong style="color: #dc143c;">Thông tin chuyển tiền:</strong> 
                        <strong>BÙI THỊ HẰNG - STK: 7420179458</strong> tại Ngân hàng TMCP Đầu tư và Phát triển Việt Nam (BIDV Bank)
                    </p>
                </div>

                <!-- Bảo hành và Chứng chỉ cùng hàng -->
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px;">
                    <!-- Bảo hành bên trái -->
                    <div style="flex: 1;">
                        <p style="margin: 0; line-height: 1.8; font-size: 13px;">
                            <strong style="color: #dc143c;">BẢO HÀNH SẢN PHẨM CỬA NHỰA ABS HÀN QUỐC BARUN VIỆT NAM</strong><br>
                            <em>02 năm kể từ ngày mua đối với cánh, khung, nẹp cửa</em><br>
                            <em>01 năm kể từ ngày mua đối với phụ kiện đi kèm</em>
                        </p>
                    </div>

                    <!-- Chứng chỉ bên phải -->
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <img src="<?= base_url('assets/uploads/ct1.png'); ?>" 
                             alt="ISO Certification" 
                             style="height: 60px; width: auto;">
                        <img src="<?= base_url('assets/uploads/ct2.png'); ?>" 
                             alt="Vietnam Certification" 
                             style="height: 60px; width: auto;">
                        <img src="<?= base_url('assets/uploads/ct3.png'); ?>" 
                             alt="Vietnam No.1 Brand Awards 2025" 
                             style="height: 60px; width: auto;">
                        <img src="<?= base_url('assets/uploads/ct4.png'); ?>" 
                             alt="Door Certification" 
                             style="height: 60px; width: auto;">
                    </div>
                </div>

                <!-- Công ty và Người lập phiếu ở dưới cùng -->
                <div style="display: flex; justify-content: space-between; margin-top: 40px;">
                    <div style="text-align: center; flex: 1;">
                        <p style="margin: 0 0 80px 0; font-size: 13px;">
                            <strong>CÔNG TY TNHH BARUN VIỆT NAM</strong>
                        </p>
                    </div>
                    <div style="text-align: center; flex: 1;">
                        <p style="margin: 0 0 5px 0; font-size: 13px;">
                            <strong>NGƯỜI LẬP PHIẾU</strong>
                        </p>
                        <p style="margin: 75px 0 0 0; font-size: 13px;">
                            <strong><?= $biller->name; ?></strong>
                        </p>
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
                            <a href="<?= site_url('sales/add/' . $inv->id) ?>" class="tip btn btn-primary" title="<?= lang('create_sale') ?>">
                                <i class="fa fa-heart"></i>
                                <span class="hidden-sm hidden-xs"><?= lang('create_sale') ?></span>
                            </a>
                        </div>
                        <div class="btn-group">
                            <a href="<?= site_url('purchases/add/' . $inv->id) ?>" class="tip btn btn-primary" title="<?= lang('create_purchase') ?>">
                                <i class="fa fa-star"></i>
                                <span class="hidden-sm hidden-xs"><?= lang('create_purchase') ?></span>
                            </a>
                        </div>
                        <div class="btn-group">
                            <a href="<?= site_url('quotes/email/' . $inv->id) ?>" data-toggle="modal" data-target="#myModal2" class="tip btn btn-primary" title="<?= lang('email') ?>">
                                <i class="fa fa-envelope-o"></i>
                                <span class="hidden-sm hidden-xs"><?= lang('email') ?></span>
                            </a>
                        </div>
                        <div class="btn-group">
                            <a href="<?= site_url('quotes/pdf/' . $inv->id) ?>" class="tip btn btn-primary" title="<?= lang('download_pdf') ?>">
                                <i class="fa fa-download"></i>
                                <span class="hidden-sm hidden-xs"><?= lang('pdf') ?></span>
                            </a>
                        </div>
                        <div class="btn-group">
                            <a href="<?= site_url('quotes/edit/' . $inv->id) ?>" class="tip btn btn-warning sledit" title="<?= lang('edit') ?>">
                                <i class="fa fa-edit"></i>
                                <span class="hidden-sm hidden-xs"><?= lang('edit') ?></span>
                            </a>
                        </div>
                        <div class="btn-group">
                            <a href="#" class="tip btn btn-danger bpo" title="<b><?= $this->lang->line("delete") ?></b>"
                                data-content="<div style='width:150px;'><p><?= lang('r_u_sure') ?></p><a class='btn btn-danger' href='<?= site_url('quotes/delete/' . $inv->id) ?>'><?= lang('i_m_sure') ?></a> <button class='btn bpo-close'><?= lang('no') ?></button></div>"
                                data-html="true" data-placement="top">
                                <i class="fa fa-trash-o"></i>
                                <span class="hidden-sm hidden-xs"><?= lang('delete') ?></span>
                            </a>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>
</div>
<script type="text/javascript">
    $(document).ready(function() {
        $('.tip').tooltip();
    });
</script>
<style>
    @media print {
        .order-table td img {
            width: 40px !important;
            height: 40px !important;
        }
    }
</style>