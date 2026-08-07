<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $this->lang->line("purchase") . " " . $inv->reference_no; ?></title>
    <link href="<?php echo $assets ?>styles/style.css" rel="stylesheet">
    <style type="text/css">
* {
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
}

html, body {
    height: auto !important;
    background: #FFF !important;
    margin: 0 !important;
    padding: 0 !important;
}

body:before, body:after {
    display: none !important;
}

/* ========== CONTAINER CHÍNH ========== */
#wrap {
    width: 794px !important;
    padding: 10px !important;
    margin-left: auto !important;
    margin-right: auto !important;
}

/* ========== HEADER SECTION ========== */
.header-section {
    font-size: 10px !important;
}

.header-section h2 {
    font-size: 12px !important;
    margin: 0 0 3px 0 !important;
    line-height: 1.2 !important;
}

.header-section p {
    font-size: 10px !important;
    line-height: 1.4 !important;
    margin: 0 !important;
}

.company-logo {
    max-width: 80px !important;
}

/* ========== TIÊU ĐỀ CHÍNH ========== */
h1 {
    color: #0066cc !important;
    font-size: 14px !important;
    font-weight: bold !important;
    text-transform: uppercase !important;
    margin: 8px 0 !important;
    text-align: center !important;
}

/* ========== WELL BOXES ========== */
.well, .well-sm {
    background-color: #f9f9f9 !important;
    border: 1px solid #ddd !important;
    padding: 8px !important;
    margin-bottom: 8px !important;
    font-size: 10px !important;
}

.well p, .well-sm p {
    font-size: 10px !important;
    line-height: 1.4 !important;
    margin: 0 !important;
}

.well strong, .well-sm strong {
    font-size: 10px !important;
}

.well .data-text, .well-sm .data-text {
    font-size: 9px !important;
}

/* ========== ICONS VÀ QR CODE CĂN CHỈNH ========== */
div[style*="display: flex; align-items: center; justify-content: center"] img {
    height: 48px !important;
    width: auto !important;
    max-width: 50px !important;
    flex-shrink: 0 !important;
    object-fit: contain !important;
}

div[style*="display: flex; align-items: center; justify-content: center"] svg {
    height: 48px !important;
    width: 48px !important;
    max-width: 50px !important;
    flex-shrink: 0 !important;
    object-fit: contain !important;
}

/* ========== BẢNG SẢN PHẨM ========== */
.table {
    width: 100% !important;
    border-collapse: collapse !important;
    table-layout: fixed !important;
    margin-bottom: 15px !important;
    border: 2px solid #000 !important;
}

.table th {
    text-align: center !important;
    padding: 7px 4px !important;
    font-weight: bold !important;
    background-color: #B4C6E7 !important;
    border: 1px solid #000 !important;
    font-size: 11px !important;
    color: #000 !important;
    width: auto !important;
}

.table td {
    padding: 7px 4px !important;
    text-align: center !important;
    vertical-align: middle !important;
    border: 1px solid #000 !important;
    font-weight: bold !important;
    font-size: 11px !important;
    color: #000 !important;
}

.table tbody td {
    border: 1px solid #000 !important;
    color: #000 !important;
}

.table tfoot td {
    font-size: 11px !important;
    padding: 7px 4px !important;
    border: 1px solid #000 !important;
    color: #000 !important;
}

.table tfoot tr:nth-last-child(3) td,
.table tfoot tr:nth-last-child(2) td,
.table tfoot tr:last-child td {
    background-color: #B4C6E7 !important;
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
}

.table tfoot td[style*="color: #0066cc"] {
    color: #0066cc !important;
}

.table tfoot td[style*="color: #dc143c"] {
    color: #dc143c !important;
}

/* ========== HÌNH ẢNH SẢN PHẨM ========== */
.product-img {
    width: 90px !important;
    height: 90px !important;
    object-fit: cover !important;
    border: 1px solid #ddd !important;
    border-radius: 4px !important;
}

.variant-img {
    width: 60px !important;
    height: 60px !important;
    object-fit: cover !important;
    border: 1px solid #ddd !important;
    border-radius: 3px !important;
}

.product-cell {
    text-align: center !important;
    vertical-align: middle !important;
    word-break: break-word !important;
    overflow-wrap: break-word !important;
}

.quote-stt-col {
    width: 4% !important;
}

.quote-model-col,
.quote-color-col,
.quote-lock-col {
    width: 11% !important;
}

.quote-note-col,
.quote-note-cell {
    text-align: center !important;
    vertical-align: middle !important;
    width: 11% !important;
    word-break: break-word !important;
    overflow-wrap: break-word !important;
}

@media screen {
    #wrap {
        width: 1120px !important;
        max-width: calc(100vw - 48px) !important;
    }

    .table {
        table-layout: fixed !important;
    }

    .table th,
    .table td {
        font-size: 10px !important;
        padding: 6px 3px !important;
    }

    .product-cell strong {
        font-size: 10px !important;
    }
}

.quote-direction-col,
.quote-direction-cell {
    width: 9% !important;
}

.quote-qty-col,
.quote-qty-cell {
    width: 7% !important;
}

.quote-price-col,
.quote-price-cell,
.quote-total-col,
.quote-total-cell {
    width: 10% !important;
}

.product-cell strong {
    font-weight: bold !important;
    font-size: 14px !important;
}

.product-img-container {
    display: flex !important;
    justify-content: center !important;
    align-items: center !important;
    margin: 8px 0 !important;
}

/* ========== THÔNG TIN SẢN PHẨM ========== */
.product-info-section {
    font-size: 10px !important;
    margin: 15px 0 !important;
}

.product-info-section p {
    font-size: 10px !important;
    line-height: 1.5 !important;
    margin: 0 0 5px 0 !important;
}

.product-info-section strong {
    font-size: 10px !important;
}

.product-info-section div {
    padding: 8px !important;
}

.product-info-wrapper {
    position: relative !important;
    isolation: isolate !important;
}

.product-info-wrapper * {
    position: relative !important;
    float: none !important;
    clear: both !important;
}

.product-info-content * {
    max-width: 100% !important;
    position: static !important;
}

.product-info-content p,
.product-info-content div,
.product-info-content span {
    position: relative !important;
    margin: 3px 0 !important;
    line-height: 1.4 !important;
}

.product-info-content table {
    width: 100% !important;
    border-collapse: collapse !important;
    table-layout: auto !important;
    margin: 8px 0 !important;
}

.product-info-content table td {
    padding: 5px !important;
    border: 1px solid #ddd !important;
    vertical-align: top !important;
    line-height: 1.4 !important;
    font-size: 10px !important;
}

.product-info-content table tr {
    page-break-inside: avoid !important;
}

.product-info-content table tbody tr td[rowspan] {
    height: auto !important;
    display: table-cell !important;
}

/* ========== FOOTER SECTION ========== */
.footer-section {
    font-size: 10px !important;
    margin-top: 15px !important;
    padding: 10px !important;
}

.footer-section p {
    font-size: 10px !important;
    line-height: 1.4 !important;
    margin: 0 0 8px 0 !important;
}

.footer-section strong {
    font-size: 10px !important;
}

.cert-img {
    max-height: 35px !important;
    width: auto !important;
}

.footer-section table {
    margin-top: 20px !important;
}

.footer-section table p {
    margin: 0 0 40px 0 !important;
    font-size: 10px !important;
}

/* ========== QR CHUYỂN KHOẢN ========== */
.footer-section div[style*="width: 100%; clear: both"] {
    margin: 8px 0 0 0 !important;
    padding: 0 !important;
    display: block !important;
}

.footer-section div[style*="width: 100%; clear: both"] table {
    width: 100% !important;
    border: none !important;
    margin: 0 !important;
    padding: 0 !important;
    border-collapse: collapse !important;
    table-layout: fixed !important;
}

.footer-section div[style*="width: 100%; clear: both"] table td {
    border: none !important;
    padding: 0 !important;
    margin: 0 !important;
    vertical-align: middle !important;
    box-sizing: border-box !important;
}

.footer-section div[style*="width: 100%; clear: both"] table td[style*="width: 70%"] {
    width: 70% !important;
    padding: 0 12px 0 0 !important;
    vertical-align: top !important;
}

.footer-section div[style*="width: 100%; clear: both"] table td[style*="width: 30%"] {
    width: 30% !important;
    text-align: center !important;
    vertical-align: middle !important;
}

.footer-section div[style*="width: 100%; clear: both"] table td[style*="width: 30%"] > div {
    display: flex !important;
    flex-direction: column !important;
    align-items: center !important;
    justify-content: center !important;
    min-height: 140px !important;
}

.footer-section div[style*="width: 100%; clear: both"] img[alt="QR Chuyển khoản"] {
    width: 100px !important;
    height: 100px !important;
    object-fit: contain !important;
    display: block !important;
    margin: 0 auto 8px auto !important;
}

.footer-section div[style*="width: 100%; clear: both"] table td[style*="width: 30%"] p {
    margin: 0 !important;
    font-size: 10px !important;
    font-weight: bold !important;
    color: #0066cc !important;
    text-align: center !important;
}

img[alt="QR Chuyển khoản"] {
    width: 100px !important;
    height: 100px !important;
    object-fit: contain !important;
    display: block !important;
    margin: 0 auto !important;
}

/* ========== HÌNH ẢNH CHUNG ========== */
img {
    max-width: 100% !important;
    height: auto !important;
}

/* ========== MEDIA PRINT ========== */
@media print {
    div[style*="display: flex; align-items: center; justify-content: center"] img {
        height: 48px !important;
        width: auto !important;
        max-width: 50px !important;
        flex-shrink: 0 !important;
    }

    div[style*="display: flex; align-items: center; justify-content: center"] svg {
        height: 48px !important;
        width: 48px !important;
        max-width: 50px !important;
        flex-shrink: 0 !important;
    }

    .table {
        border: 2px solid #000 !important;
    }

    .table th {
        border: 1px solid #000 !important;
    }

    .table td {
        border: 1px solid #000 !important;
    }

    .table tfoot tr:nth-last-child(3) td,
    .table tfoot tr:nth-last-child(2) td,
    .table tfoot tr:last-child td {
        background-color: #B4C6E7 !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
        border: 1px solid #000 !important;
    }
}
/* Ghi đè float từ PHP cho QR code trong flexbox */
div[style*="display: flex"] img.qrimg {
    float: none !important;
    height: 48px !important;
    width: 48px !important;
    max-width: 50px !important;
    flex-shrink: 0 !important;
    object-fit: contain !important;
}
/* Ghi đè float từ PHP cho QR code trong flexbox - PRIORITY CAO */
div[style*="display: flex"] .qrimg,
div[style*="display: flex"] img.qrimg,
.well-sm div[style*="display: flex"] img {
    float: none !important;
    height: 48px !important;
    width: 48px !important;
    max-width: 50px !important;
    flex-shrink: 0 !important;
    object-fit: contain !important;
    display: inline-block !important;
    margin: 0 !important;
}

/* Đảm bảo SVG QR cũng được xử lý */
div[style*="display: flex"] svg.qrimg {
    float: none !important;
    height: 48px !important;
    width: 48px !important;
    max-width: 50px !important;
    flex-shrink: 0 !important;
}
/* Ghi đè QR code - FORCE INLINE */
.well-sm img.qrimg,
img.qrimg {
    float: none !important;
    height: 38px !important;
    width: 38px !important;
    min-width: 38px !important;
    max-width: 38px !important;
    flex-shrink: 0 !important;
    object-fit: contain !important;
    display: inline-block !important;
    margin: 0 !important;
    vertical-align: middle !important;
}

/* QR trong flexbox */
div[style*="display: flex"] .qrimg,
div[style*="display: flex"] img.qrimg {
    float: none !important;
    height: 38px !important;
    width: 38px !important;
    min-width: 38px !important;
    max-width: 38px !important;
}

    </style>
</head>

<body>
<div id="wrap">
    <div class="row">
        <div class="col-lg-12">
            <?php if ($logo) { ?>
            <table class="header-section" style="width: 100%; border: none; margin-bottom: 10px;">
                <tr>
                    <td style="width: 100px; vertical-align: middle; border: none;">
                        <img src="<?= base_url() . 'assets/uploads/logos/' . $biller->logo; ?>"
                            alt="<?= $biller->company != '-' ? $biller->company : $biller->name; ?>"
                            class="company-logo">
                    </td>
                    <td style="vertical-align: middle; text-align: center; border: none;">
                        <h2 style="color: red;">CÔNG TY TNHH BARUN VIỆT NAM - VĂN PHÒNG ĐẠI DIỆN MIỀN TÂY</h2>
                        <p style="font-weight: bold;">
                            <span style="color: #44546A;">Địa chỉ:</span> 165 đường D4, KDC Hồng Loan, P Hưng Thạnh, Q Cái Răng, TP Cần Thơ<br>
                            <span style="color: #44546A;">Điện thoại:</span> 0763.882.285 (Văn phòng) - 0917.225.931 (Ms Hằng)
                        </p>
                    </td>
                </tr>
            </table>
            <?php } ?>

            <h1>BẢNG BÁO GIÁ KIÊM XÁC NHẬN ĐẶT HÀNG</h1>

            <div class="well well-sm">
                <table style="width: 100%; border: none;">
                    <tr>
                        <td style="width: 65%; vertical-align: top; border: none;">
                            <p>
    <strong><?= lang("ref"); ?>:</strong> <span class="data-text"><?= $inv->reference_no; ?></span><br>
    <strong>Ngày nhận đơn:</strong> <span class="data-text"><?= $this->sma->hrsd($inv->date); ?></span><br>
    <strong>Ngày giao chành:</strong> <span class="data-text"><?= !empty($inv->shipping_date) ? $this->sma->hrsd($inv->shipping_date) : '-'; ?></span><br>
    <strong>Ngày khách nhận dự kiến:</strong> <span class="data-text"><?= !empty($inv->expected_delivery_date) ? $this->sma->hrsd($inv->expected_delivery_date) : '-'; ?></span><br>
    <strong>Ngày lắp đặt dự kiến:</strong> <span class="data-text"><?= !empty($inv->expected_installation_date) ? $this->sma->hrsd($inv->expected_installation_date) : '-'; ?></span><br>
    <strong><?= lang("status"); ?>:</strong> <span class="data-text"><?= $inv->status; ?></span>
</p>
                        </td>
                       <!-- THAY THẾ PHẦN NÀY TRONG HTML: -->

<td style="width: 35%; text-align: center; vertical-align: middle; border: none;">
    <div style="display: flex; align-items: center; justify-content: center; gap: 3px; flex-wrap: nowrap; width: 100%; overflow: visible;">
        <img src="<?= base_url('assets/uploads/ct1.png'); ?>" alt="ISO" style="height: 48px !important; width: auto; max-width: 50px; flex-shrink: 0; object-fit: contain;">
        <img src="<?= base_url('assets/uploads/ct2.png'); ?>" alt="Vietnam" style="height: 48px !important; width: auto; max-width: 50px; flex-shrink: 0; object-fit: contain;">
        <img src="<?= base_url('assets/uploads/ct3.png'); ?>" alt="Brand" style="height: 48px !important; width: auto; max-width: 50px; flex-shrink: 0; object-fit: contain;">
        <img src="<?= base_url('assets/uploads/ct4.png'); ?>" alt="Door" style="height: 48px !important; width: auto; max-width: 50px; flex-shrink: 0; object-fit: contain;">
        <?= $this->sma->qrcode('link', urlencode(site_url('quotes/view/' . $inv->id)), 1, 'H'); ?>
    </div>
</td>
                    </tr>
                </table>
            </div>

            <div class="well well-sm">
                <p>
                    <strong style="font-size: 11px;">Khách hàng: </strong>
                    <span style="font-size: 11px;"><?= $customer->company ? $customer->company : $customer->name; ?></span>
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
                    <br>

                        <?php if (!empty($inv->shipping_info)): ?>
    <strong>Thông tin chành xe:</strong> <span class="data-text"><?= $this->sma->decode_html($inv->shipping_info); ?></span>
<?php endif; ?>
                    <br>
<?php if (!empty($inv->construction_address)): ?>
    <strong>Địa chỉ công trình:</strong>
    <?= str_replace(['<p>', '</p>'], '', $this->sma->decode_html($inv->construction_address)); ?>
<?php endif; ?>
                    <?php
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

            <table class="table table-bordered">
                <tbody>
    <tr>
        <th class="quote-stt-col"><strong>STT</strong></th>
        <th class="quote-model-col"><strong>Mẫu - Mã</strong></th>
        <th class="quote-color-col"><strong>Màu</strong></th>
        <th class="quote-lock-col"><strong>Khóa</strong></th>
        <?php
        if (!empty($custom_columns)) {
            foreach ($custom_columns as $col) {
                $column_class = (strpos($col->column_name, 'Hướng') !== false || strpos($col->column_name, 'huong') !== false) ? ' quote-direction-col' : '';
                echo '<th class="' . $column_class . '"><strong>' . $col->column_name . '</strong></th>';
            }
        }
        ?>
        <th class="quote-note-col"><strong>Ghi chú</strong></th>
        <th class="quote-qty-col"><strong><?= lang("quantity"); ?></strong></th>
        <th class="quote-price-col"><strong><?= lang("unit_price"); ?></strong></th>
        <?php
        if ($Settings->tax1 && $inv->product_tax > 0) {
            echo '<th style="width:100px;"><strong>' . lang("tax") . '</strong></th>';
        }
        if ($Settings->product_discount && $inv->product_discount != 0) {
            echo '<th style="width:100px;"><strong>' . lang("discount") . '</strong></th>';
        }
        ?>
        <th class="quote-total-col"><strong><?= lang("subtotal"); ?></strong></th>
    </tr>
                <tbody>
                    <?php
                    $r = 1;
                    $products_with_variants = [];
                    $products_without_variants = [];

                    foreach ($rows as $group) {
                        $has_variants = ($group['color'] !== null || $group['lock'] !== null);
                        if ($has_variants) {
                            $products_with_variants[] = $group;
                        } else {
                            $products_without_variants[] = $group;
                        }
                    }

                    foreach ($products_with_variants as $group):
                        $main_row = $group['main'];
                        $color = $group['color'];
                        $lock = $group['lock'];
                    ?>
                    <tr>
                        <td class="product-cell"><strong><?= $r; ?></strong></td>
                        <td class="product-cell">
                            <strong><?= $main_row->product_code; ?></strong>
                            <?= $main_row->details ? '<br><small><strong>' . $main_row->details . '</strong></small>' : ''; ?>
                            <br>
                            <?php
                            $image = $group['image'] && $group['image'] != 'no_image.png'
                                ? $group['image'] : 'no_image.png';
                            ?>
                            <div class="product-img-container">
                                <img src="<?= base_url('assets/uploads/' . $image); ?>" class="product-img">
                            </div>
                        </td>
                        <td class="product-cell">
                            <?php if ($color): ?>
                                <strong><?= $color->product_name; ?></strong><br>
                                <?php
                                $color_image = $color->image && $color->image != 'no_image.png'
                                    ? $color->image : 'no_image.png';
                                ?>
                                <div class="product-img-container">
                                    <img src="<?= base_url('assets/uploads/' . $color_image); ?>" class="variant-img">
                                </div>
                            <?php else: ?>
                                <strong>-</strong>
                            <?php endif; ?>
                        </td>
                        <td class="product-cell">
                            <?php if ($lock): ?>
                                <strong><?= $lock->product_name; ?></strong><br>
                                <?php
                                $lock_image = $lock->image && $lock->image != 'no_image.png'
                                    ? $lock->image : 'no_image.png';
                                ?>
                                <div class="product-img-container">
                                    <img src="<?= base_url('assets/uploads/' . $lock_image); ?>" class="variant-img">
                                </div>
                            <?php else: ?>
                                <strong>-</strong>
                            <?php endif; ?>
                        </td>
                        <?php
if (!empty($custom_columns)) {
    foreach ($custom_columns as $col) {
        $value = isset($main_row->custom_fields[$col->column_name])
            ? $main_row->custom_fields[$col->column_name] : '';
        $column_class = (strpos($col->column_name, 'Hướng') !== false || strpos($col->column_name, 'huong') !== false) ? ' quote-direction-cell' : '';
        echo '<td class="product-cell' . $column_class . '"><strong>' . $this->sma->decode_html($value) . '</strong></td>';
    }
}
?>
                        <td class="product-cell quote-note-cell">
                            <strong><?= !empty($main_row->notes) ? $this->sma->decode_html($main_row->notes) : ''; ?></strong>
                        </td>
                        <td class="product-cell quote-qty-cell">
                            <strong><?= $this->sma->formatQuantity($main_row->unit_quantity); ?></strong>
                        </td>
                        <td class="product-cell quote-price-cell">
                            <strong><?php
                            $total_unit_price = $main_row->unit_price;
                            if ($color) {
                                $total_unit_price += $color->unit_price;
                            }
                            echo $this->sma->formatMoney($total_unit_price);
                            ?></strong>
                        </td>
                        <?php if ($Settings->tax1 && $inv->product_tax > 0): ?>
                            <td class="product-cell">
                                <strong><?php
                                $total_tax = $main_row->item_tax;
                                if ($color) $total_tax += $color->item_tax;
                                echo ($main_row->item_tax != 0 && $main_row->tax_code) ? '<small>(' . $main_row->tax_code . ')</small> ' : '';
                                echo $this->sma->formatMoney($total_tax);
                                ?></strong>
                            </td>
                        <?php endif; ?>
                        <?php if ($Settings->product_discount && $inv->product_discount != 0): ?>
                            <td class="product-cell">
                                <strong><?php
                                $total_discount = $main_row->item_discount;
                                if ($color) $total_discount += $color->item_discount;
                                echo ($main_row->discount != 0) ? '<small>(' . $main_row->discount . ')</small> ' : '';
                                echo $this->sma->formatMoney($total_discount);
                                ?></strong>
                            </td>
                        <?php endif; ?>
                        <td class="product-cell quote-total-cell">
                            <strong><?php
                            $total_subtotal = $main_row->subtotal;
                            if ($color) {
                                $total_subtotal += $color->subtotal;
                            }
                            echo $this->sma->formatMoney($total_subtotal);
                            ?></strong>
                        </td>
                    </tr>
                    <?php
                        $r++;
                    endforeach;

                    $has_any_lock = false;
                    $total_lock_qty = 0;
                    $total_lock_price = 0;

                    foreach ($rows as $group) {
                        if ($group['lock']) {
                            $has_any_lock = true;
                            $total_lock_qty += $group['lock']->unit_quantity;
                            $total_lock_price += $group['lock']->subtotal;
                        }
                    }

                    if ($has_any_lock && $total_lock_price > 0): ?>
                        <tr>
                            <td class="product-cell"><strong><?= $r; ?></strong></td>
                            <?php
                            $lock_merge_cols = 3;
                            if (!empty($custom_columns)) {
                                $lock_merge_cols += count($custom_columns);
                            }
                            ?>
                            <td colspan="<?= $lock_merge_cols; ?>" style="text-align:right;">
                                <strong>Khóa</strong>
                            </td>
                            <td class="product-cell quote-note-cell"></td>
                            <td class="product-cell quote-qty-cell">
                                <strong><?= $this->sma->formatQuantity($total_lock_qty); ?></strong>
                            </td>
                            <td class="product-cell quote-price-cell">
                                <strong><?= $this->sma->formatMoney($total_lock_price / $total_lock_qty); ?></strong>
                            </td>
                            <?php if ($Settings->tax1 && $inv->product_tax > 0): ?>
                                <td class="product-cell">
                                    <strong>-</strong>
                                </td>
                            <?php endif; ?>
                            <?php if ($Settings->product_discount && $inv->product_discount != 0): ?>
                                <td class="product-cell">
                                    <strong>-</strong>
                                </td>
                            <?php endif; ?>
                            <td class="product-cell quote-total-cell">
                                <strong><?= $this->sma->formatMoney($total_lock_price); ?></strong>
                            </td>
                        </tr>
                    <?php
                        $r++;
                    endif;

                    foreach ($products_without_variants as $group):
                        $main_row = $group['main'];
                        $merge_cols = 3;
                        if (!empty($custom_columns)) {
                            $merge_cols += count($custom_columns);
                        }
                        $merge_cols += 1;
                    ?>
                        <tr>
                            <td class="product-cell"><strong><?= $r; ?></strong></td>
                            <td colspan="<?= $merge_cols - 1; ?>" style="text-align:right;">
                                <strong><?= $main_row->product_name; ?></strong>
                                <?= $main_row->details ? '<br><small><strong>' . $main_row->details . '</strong></small>' : ''; ?>
                            </td>
                            <td class="product-cell quote-note-cell">
                                <strong><?= !empty($main_row->notes) ? $this->sma->decode_html($main_row->notes) : ''; ?></strong>
                            </td>
                            <td class="product-cell quote-qty-cell">
                                <strong><?= $this->sma->formatQuantity($main_row->unit_quantity); ?></strong>
                            </td>
                            <td class="product-cell quote-price-cell">
                                <strong><?= $this->sma->formatMoney($main_row->unit_price); ?></strong>
                            </td>
                            <?php if ($Settings->tax1 && $inv->product_tax > 0): ?>
                                <td class="product-cell">
                                    <strong><?= $this->sma->formatMoney($main_row->item_tax); ?></strong>
                                </td>
                            <?php endif; ?>
                            <?php if ($Settings->product_discount && $inv->product_discount != 0): ?>
                                <td class="product-cell">
                                    <strong><?= $this->sma->formatMoney($main_row->item_discount); ?></strong>
                                </td>
                            <?php endif; ?>
                            <td class="product-cell quote-total-cell">
                                <strong><?= $this->sma->formatMoney($main_row->subtotal); ?></strong>
                            </td>
                        </tr>
                    <?php
                        $r++;
                    endforeach;
                    ?>
                </tbody>
                <tfoot>
                    <?php
                    $col = 8;
                    if (!empty($custom_columns)) {
                        $col += count($custom_columns);
                    }
                    $adjustment_cols = 0;
                    if ($Settings->product_discount && $inv->product_discount != 0) {
                        $col++;
                        $adjustment_cols++;
                    }
                    if ($Settings->tax1 && $inv->product_tax > 0) {
                        $col++;
                        $adjustment_cols++;
                    }
                    $tcol = $col - 1;
                    $total_detail_colspan = $tcol - $adjustment_cols;
                    ?>
                    <?php if ($inv->grand_total != $inv->total) { ?>
                        <tr>
                            <td colspan="<?= $total_detail_colspan; ?>" style="text-align:right; font-weight:bold;">
                                <?= lang("total"); ?> (<?= $default_currency->code; ?>)
                            </td>
                            <?php
                            if ($Settings->tax1 && $inv->product_tax > 0) {
                                echo '<td style="text-align:right; font-weight:bold;">' . $this->sma->formatMoney($inv->product_tax) . '</td>';
                            }
                            if ($Settings->product_discount && $inv->product_discount != 0) {
                                echo '<td style="text-align:right; font-weight:bold;">' . $this->sma->formatMoney($inv->product_discount) . '</td>';
                            }
                            ?>
                            <td style="text-align:right; font-weight:bold;"><?= $this->sma->formatMoney($inv->total + $inv->product_tax); ?></td>
                        </tr>
                    <?php } ?>

                    <?php if ($inv->order_discount != 0) {
                        echo '<tr><td colspan="' . ($tcol) . '" style="text-align:right; font-weight:bold;">' . lang("order_discount") . ' (' . $default_currency->code . ')</td><td style="text-align:right; font-weight:bold;">' . ($inv->order_discount_id ? '<small>(' . $inv->order_discount_id . ')</small> ' : '') . $this->sma->formatMoney($inv->order_discount) . '</td></tr>';
                    }
                    ?>
                    <?php if ($Settings->tax2 && $inv->order_tax != 0) {
                        echo '<tr><td colspan="' . ($tcol) . '" style="text-align:right; font-weight:bold;">' . lang("order_tax") . ' (' . $default_currency->code . ')</td><td style="text-align:right; font-weight:bold;">' . $this->sma->formatMoney($inv->order_tax) . '</td></tr>';
                    }
                    ?>
                    <?php if ($inv->shipping != 0) {
                        echo '<tr><td colspan="' . ($tcol) . '" style="text-align:right; font-weight:bold;">' . lang("shipping") . ' (' . $default_currency->code . ')</td><td style="text-align:right; font-weight:bold;">' . $this->sma->formatMoney($inv->shipping) . '</td></tr>';
                    }
                    ?>
                    <tr>
                        <td colspan="<?= $tcol; ?>" style="text-align:right; font-weight:bold; color: #0066cc; background-color: #B4C6E7 !important;">
                            <?= lang("total_amount"); ?>
                        </td>
                        <td style="text-align:center; font-weight:bold; color: #dc143c;  background-color: #B4C6E7 !important;"><?= $this->sma->formatMoney($inv->grand_total); ?></td>
                    </tr>
                    <?php if ($inv->deposit_amount && $inv->deposit_amount > 0) { ?>
<tr>
    <td colspan="<?= $tcol; ?>" style="text-align:right; font-weight:bold; color: #0066cc; background-color: #B4C6E7 !important;">
        Tiền đặt cọc
    </td>
    <td style="text-align:center; font-weight:bold; color: #dc143c;  background-color: #B4C6E7 !important;"><?= $this->sma->formatMoney($inv->deposit_amount); ?></td>
</tr>
<tr>
    <td colspan="<?= $tcol; ?>" style="text-align:right; font-weight:bold; color: #0066cc; background-color: #B4C6E7 !important;">
        Còn lại
    </td>
    <td style="text-align:center; font-weight:bold; color: #dc143c;  background-color: #B4C6E7 !important;">
        <?= $this->sma->formatMoney($inv->grand_total - $inv->deposit_amount); ?>
    </td>
</tr>

<?php } ?>
                </tfoot>
            </table>



            <div class="footer-section" style="border-top: 2px solid #ddd; margin-top: 5px !important;">
    <p style="font-weight: bold !important;">
        <span style="color: #dc143c;">THÔNG TIN CHUYỂN TIỀN:</span>
        BÙI THỊ HẰNG - STK: 7420179458 tại Ngân hàng TMCP Đầu tư và Phát triển Việt Nam (BIDV Bank)
    </p>

    <div style="width: 100%; clear: both; margin-top: 3px !important;">
        <table style="width: 100%; border: none; margin: 0; border-collapse: collapse; table-layout: fixed;">
            <tr>
                <!-- CỘT TRÁI: THÔNG TIN SẢN PHẨM -->
                <td style="width: 70%; vertical-align: top; border: none; padding-right: 15px; box-sizing: border-box;">
                    <p style=" margin: 0 0 3px 0;">
                        <strong style="color: #dc143c;">THÔNG TIN SẢN PHẨM: </strong>
                    </p>
                    <div style="font-size: 10px; line-height: 1.3; margin-bottom: 15px !important;">
                        * Cánh cửa ABS trơn phẳng, dày 40mm (+-2mm).<br>
                        * Bề mặt cánh hoàn thiện là 2 tấm nhựa ABS (loại nhựa đặc biệt có khả năng chịu va đập)<br>
                        * Vật liệu cánh: Xung quanh bao bọc thanh nhựa PVC Bar chống nước, thanh LVL và honeycomb.<br>
                        * Khung cửa bằng nhựa rộng 110mm, thép gia cường dày 0.7-1mm<br>
                        * Nẹp khung bao di động 02 mặt: Lắp đặt linh động cho cửa tường dày 110-160mm<br>
                        * Màu cửa hoàn thiện: theo màu tiêu chuẩn của NSX
                    </div>

                    <!-- BẢO HÀNH SẢN PHẨM -->
                    <p style="margin: 5px 0 0 0; line-height: 1.3; font-size: 10px; margin-top: 15px !important;">
                        <strong style="color: #dc143c;">BẢO HÀNH SẢN PHẨM CỬA NHỰA ABS HÀN QUỐC BARUN VIỆT NAM</strong><br>
                        <em style="font-size: 9px;">02 năm kể từ ngày mua đối với cánh, khung, nẹp cửa</em><br>
                        <em style="font-size: 9px;">01 năm kể từ ngày mua đối với phụ kiện đi kèm</em>
                    </p>
                </td>

                <!-- CỘT PHẢI: MÃ QR - CĂNG GIỮA -->
                <td style="width: 30%; text-align: center; vertical-align: middle; border: none; padding: 0; box-sizing: border-box;">
                    <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 140px; width: 100%;">
                        <img src="<?= base_url('assets/uploads/qr_chuyen_khoan.jpg'); ?>"
                            alt="QR Chuyển khoản"
                            style="width: 100px !important; height: 100px !important; object-fit: contain; display: block; margin: 0 auto 8px auto;">
                        <p style="margin: 0; font-size: 10px; font-weight: bold !important; color: #0066cc; text-align: center; width: 100%;">
                            QUÉT MÃ CHUYỂN KHOẢN
                        </p>
                    </div>
                </td>
            </tr>
        </table>
    </div>

                <table style="width: 100%; margin-top: 20px; border: none;">
                    <tr>
                        <td style="width: 50%; text-align: center; border: none; vertical-align: top;">
                            <p style="margin: 0 0 60px 0;">
                                <strong>CÔNG TY TNHH BARUN VIỆT NAM</strong>
                            </p>
                        </td>
                        <td style="width: 50%; text-align: center; border: none; vertical-align: top;">
                            <p style="margin: 0;">
                                <strong>NGƯỜI LẬP PHIẾU</strong>
                            </p>
                            <br><br><br>
                            <p style="margin: 0;">
                                <strong><?= $biller->name; ?></strong>
                            </p>
                        </td>
                    </tr>
                </table>
            </div>

        </div>
    </div>
</div>

</body>
</html>
