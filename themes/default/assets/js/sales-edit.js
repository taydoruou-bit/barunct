function apdunggiasi(){
	var giasi=jQuery("#p_gia_si").val();
	if(!isNaN(giasi)&&giasi>0){
		jQuery("#pprice").val(giasi);
		jQuery("#pprice").trigger("change");
	}
}
$(document).ready(function (e) {
    $('body a, body button').attr('tabindex', -1);
    check_add_item_val();
    if (site.settings.set_focus != 1) {
        $('#add_item').focus();
    }
    var $customer = $('#slcustomer');
    $customer.change(function (e) {
        localStorage.setItem('slcustomer', $(this).val());
        //$('#slcustomer_id').val($(this).val());
        //
    });
    if (slcustomer = localStorage.getItem('slcustomer')) {
        $customer.val(slcustomer).select2({
            minimumInputLength: 1,
            data: [],
            initSelection: function (element, callback) {
                $.ajax({
                    type: "get", async: false,
                    url: site.base_url+"customers/getCustomer/" + $(element).val(),
                    dataType: "json",
                    success: function (data) {
                        callback(data[0]);
                    }
                });
            },
            ajax: {
                url: site.base_url + "customers/suggestions",
                dataType: 'json',
                quietMillis: 15,
                data: function (term, page) {
                    return {
                        term: term,
                        limit: 10
                    };
                },
                results: function (data, page) {
                    if (data.results != null) {
                        return {results: data.results};
                    } else {
                        return {results: [{id: '', text: 'No Match Found'}]};
                    }
                }
            }
        });
    } else {
        nsCustomer();
    }

// Order level shipping and discount localStorage
if (sldiscount = localStorage.getItem('sldiscount')) {
    $('#sldiscount').val(sldiscount);
}
if (sldiscount_tra = localStorage.getItem('sldiscount_tra')) {
    $('#sldiscount_tra').val(sldiscount_tra);
}
$('#sltax2').change(function (e) {
    localStorage.setItem('sltax2', $(this).val());
    $('#sltax2').val($(this).val());
});
if (sltax2 = localStorage.getItem('sltax2')) {
    $('#sltax2').select2("val", sltax2);
}
$('#slsale_status').change(function (e) {
    localStorage.setItem('slsale_status', $(this).val());
});
if (slsale_status = localStorage.getItem('slsale_status')) {
    $('#slsale_status').select2("val", slsale_status);
}
$('#slpayment_status').change(function (e) {
    var ps = $(this).val();
    localStorage.setItem('slpayment_status', ps);
    if (ps == 'partial' || ps == 'paid') {
        if(ps == 'paid') {
            $('#amount_1').val(formatDecimal(parseFloat(((total + invoice_tax) - order_discount) + shipping)));
        }
        $('#payments').slideDown();
        $('#pcc_no_1').focus();
    } else {
        $('#payments').slideUp();
    }
});
if (slpayment_status = localStorage.getItem('slpayment_status')) {
    $('#slpayment_status').select2("val", slpayment_status);
    var ps = slpayment_status;
    if (ps == 'partial' || ps == 'paid') {
        $('#payments').slideDown();
        $('#pcc_no_1').focus();
    } else {
        $('#payments').slideUp();
    }
}

$(document).on('change', '.paid_by', function () {
    var p_val = $(this).val();
    localStorage.setItem('paid_by', p_val);
    $('#rpaidby').val(p_val);
    if (p_val == 'cash' ||  p_val == 'other') {
        $('.pcheque_1').hide();
        $('.pcc_1').hide();
        $('.pcash_1').show();
        $('#payment_note_1').focus();
    } else if (p_val == 'CC') {
        $('.pcheque_1').hide();
        $('.pcash_1').hide();
        $('.pcc_1').show();
        $('#pcc_no_1').focus();
    } else if (p_val == 'Cheque') {
        $('.pcc_1').hide();
        $('.pcash_1').hide();
        $('.pcheque_1').show();
        $('#cheque_no_1').focus();
    } else {
        $('.pcheque_1').hide();
        $('.pcc_1').hide();
        $('.pcash_1').hide();
    }
    if (p_val == 'gift_card') {
        $('.gc').show();
        $('.ngc').hide();
        $('#gift_card_no').focus();
    } else {
        $('.ngc').show();
        $('.gc').hide();
        $('#gc_details').html('');
    }
});

if (paid_by = localStorage.getItem('paid_by')) {
    var p_val = paid_by;
    $('.paid_by').select2("val", paid_by);
    $('#rpaidby').val(p_val);
    if (p_val == 'cash' ||  p_val == 'other') {
        $('.pcheque_1').hide();
        $('.pcc_1').hide();
        $('.pcash_1').show();
        $('#payment_note_1').focus();
    } else if (p_val == 'CC') {
        $('.pcheque_1').hide();
        $('.pcash_1').hide();
        $('.pcc_1').show();
        $('#pcc_no_1').focus();
    } else if (p_val == 'Cheque') {
        $('.pcc_1').hide();
        $('.pcash_1').hide();
        $('.pcheque_1').show();
        $('#cheque_no_1').focus();
    } else {
        $('.pcheque_1').hide();
        $('.pcc_1').hide();
        $('.pcash_1').hide();
    }
    if (p_val == 'gift_card') {
        $('.gc').show();
        $('.ngc').hide();
        $('#gift_card_no').focus();
    } else {
        $('.ngc').show();
        $('.gc').hide();
        $('#gc_details').html('');
    }
}

if (gift_card_no = localStorage.getItem('gift_card_no')) {
    $('#gift_card_no').val(gift_card_no);
}
$('#gift_card_no').change(function (e) {
    localStorage.setItem('gift_card_no', $(this).val());
});

if (amount_1 = localStorage.getItem('amount_1')) {
    $('#amount_1').val(amount_1);
}
$('#amount_1').change(function (e) {
    localStorage.setItem('amount_1', $(this).val());
});

if (paid_by_1 = localStorage.getItem('paid_by_1')) {
    $('#paid_by_1').val( paid_by_1);
}
$('#paid_by_1').change(function (e) {
    localStorage.setItem('paid_by_1', $(this).val());
});

if (pcc_holder_1 = localStorage.getItem('pcc_holder_1')) {
    $('#pcc_holder_1').val(pcc_holder_1);
}
$('#pcc_holder_1').change(function (e) {
    localStorage.setItem('pcc_holder_1', $(this).val());
});

if (pcc_type_1 = localStorage.getItem('pcc_type_1')) {
    $('#pcc_type_1').select2("val", pcc_type_1);
}
$('#pcc_type_1').change(function (e) {
    localStorage.setItem('pcc_type_1', $(this).val());
});

if (pcc_month_1 = localStorage.getItem('pcc_month_1')) {
    $('#pcc_month_1').val( pcc_month_1);
}
$('#pcc_month_1').change(function (e) {
    localStorage.setItem('pcc_month_1', $(this).val());
});

if (pcc_year_1 = localStorage.getItem('pcc_year_1')) {
    $('#pcc_year_1').val(pcc_year_1);
}
$('#pcc_year_1').change(function (e) {
    localStorage.setItem('pcc_year_1', $(this).val());
});

if (pcc_no_1 = localStorage.getItem('pcc_no_1')) {
    $('#pcc_no_1').val(pcc_no_1);
}
$('#pcc_no_1').change(function (e) {
    var pcc_no = $(this).val();
    localStorage.setItem('pcc_no_1', pcc_no);
    var CardType = null;
    var ccn1 = pcc_no.charAt(0);
    if(ccn1 == 4)
        CardType = 'Visa';
    else if(ccn1 == 5)
        CardType = 'MasterCard';
    else if(ccn1 == 3)
        CardType = 'Amex';
    else if(ccn1 == 6)
        CardType = 'Discover';
    else
        CardType = 'Visa';

    $('#pcc_type_1').select2("val", CardType);
});

if (cheque_no_1 = localStorage.getItem('cheque_no_1')) {
    $('#cheque_no_1').val(cheque_no_1);
}
$('#cheque_no_1').change(function (e) {
    localStorage.setItem('cheque_no_1', $(this).val());
});

if (payment_note_1 = localStorage.getItem('payment_note_1')) {
    $('#payment_note_1').redactor('set', payment_note_1);
}
$('#payment_note_1').redactor('destroy');
$('#payment_note_1').redactor({
    buttons: ['formatting', '|', 'alignleft', 'aligncenter', 'alignright', 'justify', '|', 'bold', 'italic', 'underline', '|', 'unorderedlist', 'orderedlist', '|', 'link', '|', 'html'],
    formattingTags: ['p', 'pre', 'h3', 'h4'],
    minHeight: 100,
    changeCallback: function (e) {
        var v = this.get();
        localStorage.setItem('payment_note_1', v);
    }
});

var old_payment_term;
$('#slpayment_term').focus(function () {
    old_payment_term = $(this).val();
}).change(function (e) {
    var new_payment_term = $(this).val() ? parseFloat($(this).val()) : 0;
    if (!is_numeric($(this).val())) {
        $(this).val(old_payment_term);
        bootbox.alert(lang.unexpected_value);
        return;
    } else {
        localStorage.setItem('slpayment_term', new_payment_term);
        $('#slpayment_term').val(new_payment_term);
    }
});
if (slpayment_term = localStorage.getItem('slpayment_term')) {
    $('#slpayment_term').val(slpayment_term);
}

var old_shipping;
$('#slshipping').focus(function () {
    old_shipping = $(this).val();
}).change(function () {
    if (!is_numeric($(this).val())) {
        $(this).val(old_shipping);
        bootbox.alert(lang.unexpected_value);
        return;
    } else {
        shipping = $(this).val() ? parseFloat($(this).val()) : '0';
    }
    localStorage.setItem('slshipping', shipping);
    var gtotal = ((total + invoice_tax) - order_discount) + shipping;
    $('#gtotal').text(formatMoney(gtotal));
    $('#tship').text(formatMoney(shipping));
});
if (slshipping = localStorage.getItem('slshipping')) {
    shipping = parseFloat(slshipping);
    $('#slshipping').val(shipping);
} else {
    shipping = 0;
}
$('#add_sale, #edit_sale').attr('disabled', true);
$(document).on('change', '.rserial', function () {
    var item_id = $(this).closest('tr').attr('data-item-id');
    slitems[item_id].row.serial = $(this).val();
    localStorage.setItem('slitems', JSON.stringify(slitems));
});

// If there is any item in localStorage
if (localStorage.getItem('slitems')) {
	
    loadItems();
}

if (localStorage.getItem('slitems_tra')) {
    loadItems_Tra();
}

    // clear localStorage and reload
    $('#reset').click(function (e) {
        bootbox.confirm(lang.r_u_sure, function (result) {
            if (result) {
                if (localStorage.getItem('slitems')) {
                    localStorage.removeItem('slitems');
                }
				if (localStorage.getItem('slitems_tra')) {
                    localStorage.removeItem('slitems_tra');
                }
                if (localStorage.getItem('sldiscount_tra')) {
                    localStorage.removeItem('sldiscount_tra');
                }
				
				if (localStorage.getItem('sldiscount')) {
                    localStorage.removeItem('sldiscount');
                }
                if (localStorage.getItem('sltax2')) {
                    localStorage.removeItem('sltax2');
                }
                if (localStorage.getItem('slshipping')) {
                    localStorage.removeItem('slshipping');
                }
                if (localStorage.getItem('slref')) {
                    localStorage.removeItem('slref');
                }
                if (localStorage.getItem('slwarehouse')) {
                    localStorage.removeItem('slwarehouse');
                }
                if (localStorage.getItem('slnote')) {
                    localStorage.removeItem('slnote');
                }
                if (localStorage.getItem('slinnote')) {
                    localStorage.removeItem('slinnote');
                }
                if (localStorage.getItem('slcustomer')) {
                    localStorage.removeItem('slcustomer');
                }
                if (localStorage.getItem('slcurrency')) {
                    localStorage.removeItem('slcurrency');
                }
                if (localStorage.getItem('sldate')) {
                    localStorage.removeItem('sldate');
                }
                if (localStorage.getItem('slstatus')) {
                    localStorage.removeItem('slstatus');
                }
                if (localStorage.getItem('slbiller')) {
                    localStorage.removeItem('slbiller');
                }
                if (localStorage.getItem('gift_card_no')) {
                    localStorage.removeItem('gift_card_no');
                }

                $('#modal-loading').show();
                location.reload();
            }
        });
});

// save and load the fields in and/or from localStorage

$('#slref').change(function (e) {
    localStorage.setItem('slref', $(this).val());
});
if (slref = localStorage.getItem('slref')) {
    $('#slref').val(slref);
}

$('#slwarehouse').change(function (e) {
    localStorage.setItem('slwarehouse', $(this).val());
});
if (slwarehouse = localStorage.getItem('slwarehouse')) {
    $('#slwarehouse').select2("val", slwarehouse);
}

    $('#slnote').redactor('destroy');
    $('#slnote').redactor({
        buttons: ['formatting', '|', 'alignleft', 'aligncenter', 'alignright', 'justify', '|', 'bold', 'italic', 'underline', '|', 'unorderedlist', 'orderedlist', '|', 'link', '|', 'html'],
        formattingTags: ['p', 'pre', 'h3', 'h4'],
        minHeight: 100,
        changeCallback: function (e) {
            var v = this.get();
            localStorage.setItem('slnote', v);
        }
    });
    if (slnote = localStorage.getItem('slnote')) {
        $('#slnote').redactor('set', slnote);
    }
    $('#slinnote').redactor('destroy');
    $('#slinnote').redactor({
        buttons: ['formatting', '|', 'alignleft', 'aligncenter', 'alignright', 'justify', '|', 'bold', 'italic', 'underline', '|', 'unorderedlist', 'orderedlist', '|', 'link', '|', 'html'],
        formattingTags: ['p', 'pre', 'h3', 'h4'],
        minHeight: 100,
        changeCallback: function (e) {
            var v = this.get();
            localStorage.setItem('slinnote', v);
        }
    });
    if (slinnote = localStorage.getItem('slinnote')) {
        $('#slinnote').redactor('set', slinnote);
    }

    // prevent default action usln enter
    $('body').bind('keypress', function (e) {
        if ($(e.target).hasClass('redactor_editor')) {
            return true;
        }
        if (e.keyCode == 13) {
            e.preventDefault();
            return false;
        }
    });

    // Order tax calculation
    if (site.settings.tax2 != 0) {
        $('#sltax2').change(function () {
            localStorage.setItem('sltax2', $(this).val());
            loadItems();
            return;
        });
    }

    // Order discount calculation
    var old_sldiscount;
    $('#sldiscount').focus(function () {
        old_sldiscount = $(this).val();
    }).change(function () {
        var new_discount = $(this).val() ? $(this).val() : '0';
        if (is_valid_discount(new_discount)) {
            localStorage.removeItem('sldiscount');
            localStorage.setItem('sldiscount', new_discount);
            loadItems();
            return;
        } else {
            $(this).val(old_sldiscount);
            bootbox.alert(lang.unexpected_value);
            return;
        }

    });
	
	// Order discount_tra calculation
    var old_sldiscount_tra;
    $('#sldiscount_tra').focus(function () {
        old_sldiscount_tra = $(this).val();
    }).change(function () {
        var new_discount = $(this).val() ? $(this).val() : '0';
        if (is_valid_discount(new_discount)) {
            localStorage.removeItem('sldiscount_tra');
            localStorage.setItem('sldiscount_tra', new_discount);
            loadItems_Tra();
            return;
        } else {
            $(this).val(old_sldiscount_tra);
            bootbox.alert(lang.unexpected_value);
            return;
        }

    });

    /* ----------------------
     * Delete Row Method
     * ---------------------- */
    $(document).on('click', '.sldel', function () {
        var row = $(this).closest('tr');
        var item_id = row.attr('data-item-id');
        var id_sanpham = row.attr('data-id-sanpham');   
        //kiem tra xem co khuyen mai con thi xoa luon
        if(id_sanpham>0){
            $("#slTable tbody tr").each(function(e){
                var cls=$(this).attr("data-id-khuyenmai");                  
                if(id_sanpham==cls){
                    var xoa = $(this).attr('data-item-id-remove');              
                    delete slitems[xoa];
                    $(this).remove();
                    if(slitems.hasOwnProperty(xoa)) { } else {
                        localStorage.setItem('slitems', JSON.stringify(slitems));
                        loadItems();
                    }
                }
            }); 
        }

        delete slitems[item_id];
        row.remove();
        if(slitems.hasOwnProperty(item_id)) { } else {
            localStorage.setItem('slitems', JSON.stringify(slitems));
            loadItems();
            return;
        }
    });

  $(document).on('click', '.sldel_tra', function () {
        var row = $(this).closest('tr');
        var item_id = row.attr('data-item-id');
        delete slitems_tra[item_id];
        row.remove();
        if(slitems_tra.hasOwnProperty(item_id)) { } else {
            localStorage.setItem('slitems_tra', JSON.stringify(slitems_tra));
            loadItems_Tra();
            return;
        }
    });
	
    /* -----------------------
     * Edit Row Modal Hanlder
     ----------------------- */
     $(document).on('click', '.edit', function () {
        var row = $(this).closest('tr');
        var row_id = row.attr('id');
        item_id = row.attr('data-item-id');
		var gia_si = formatDecimal(row.children().children('.p_gia_set_giasi').val());
        item = slitems[item_id];
        var qty = row.children().children('.rquantity').val(),
        product_option = row.children().children('.roption').val(),
        unit_price = formatDecimal(row.children().children('.ruprice').val()),
        discount = row.children().children('.rdiscount').val();
        if(item.options !== false) {
            $.each(item.options, function () {
                if(this.id == item.row.option && this.price != 0 && this.price != '' && this.price != null) {
                    unit_price = parseFloat(item.row.real_unit_price)+parseFloat(this.price);
                }
            });
        }
        var real_unit_price = item.row.real_unit_price;
        var net_price = unit_price;
        $('#prModalLabel').text(item.row.name + ' (' + item.row.code + ')');
        if (site.settings.tax1) {
            $('#ptax').select2('val', item.row.tax_rate);
            $('#old_tax').val(item.row.tax_rate);
            var item_discount = 0, ds = discount ? discount : '0';
            if (ds.indexOf("%") !== -1) {
                var pds = ds.split("%");
                if (!isNaN(pds[0])) {
                    item_discount = formatDecimal(parseFloat(((unit_price) * parseFloat(pds[0])) / 100), 4);
                } else {
                    item_discount = parseFloat(ds);
                }
            } else {
                item_discount = parseFloat(ds);
            }
            net_price -= item_discount;
            var pr_tax = item.row.tax_rate, pr_tax_val = 0;
            if (pr_tax !== null && pr_tax != 0) {
                $.each(tax_rates, function () {
                    if(this.id == pr_tax){
                        if (this.type == 1) {

                            if (slitems[item_id].row.tax_method == 0) {
                                pr_tax_val = formatDecimal((((net_price) * parseFloat(this.rate)) / (100 + parseFloat(this.rate))), 4);
                                pr_tax_rate = formatDecimal(this.rate) + '%';
                                net_price -= pr_tax_val;
                            } else {
                                pr_tax_val = formatDecimal((((net_price) * parseFloat(this.rate)) / 100), 4);
                                pr_tax_rate = formatDecimal(this.rate) + '%';
                            }

                        } else if (this.type == 2) {

                            pr_tax_val = parseFloat(this.rate);
                            pr_tax_rate = this.rate;

                        }
                    }
                });
            }
        }
        if (site.settings.product_serial !== 0) {
            $('#pserial').val(row.children().children('.rserial').val());
        }
        var opt = '<p style="margin: 12px 0 0 0;">n/a</p>';
        if(item.options !== false) {
            var o = 1;
            opt = $("<select id=\"poption\" name=\"poption\" class=\"form-control select\" />");
            $.each(item.options, function () {
                if(o == 1) {
                    if(product_option == '') { product_variant = this.id; } else { product_variant = product_option; }
                }
                $("<option />", {value: this.id, text: this.name}).appendTo(opt);
                o++;
            });
        } else {
            product_variant = 0;
        }

        uopt = $("<select id=\"punit\" name=\"punit\" class=\"form-control select\" />");
        $.each(item.units, function () {
            if(this.id == item.row.unit) {
                $("<option />", {value: this.id, text: this.name, selected:true}).appendTo(uopt);
            } else {
                $("<option />", {value: this.id, text: this.name}).appendTo(uopt);
            }
        });

        $('#poptions-div').html(opt);
        $('#punits-div').html(uopt);
        $('select.select').select2({minimumResultsForSearch: 7});
        $('#pquantity').val(qty);
        $('#old_qty').val(qty);
        $('#pprice').val(unit_price);
		$('#p_gia_si').val(gia_si);
        $('#punit_price').val(formatDecimal(parseFloat(unit_price)+parseFloat(pr_tax_val)));
        $('#poption').select2('val', item.row.option);
        $('#old_price').val(unit_price);
        $('#row_id').val(row_id);
        $('#item_id').val(item_id);
        $('#pserial').val(row.children().children('.rserial').val());
        $('#pdiscount').val(discount);
        $('#net_price').text(formatMoney(net_price));
        $('#pro_tax').text(formatMoney(pr_tax_val));
        $('#prModal').appendTo("body").modal('show');

    });
     $(document).on('click', '.editkm', function () {
        var row = $(this).closest('tr');
        var row_id = row.attr('id');
        item_id = row.attr('data-item-id');      
        item = slitems[item_id];
        
        $('#MdKhuyenMai #km_edit_id').val(item_id);
        $('#prKhuyenMai').text(item.row.name + ' (' + item.row.code + ')');
        $('#MdKhuyenMai').appendTo("body").modal('show');

    });
	$(document).on('click', '.edit_tra', function () {
        var row = $(this).closest('tr');
        var row_id = row.attr('id');
        item_id = row.attr('data-item-id');		
        item = slitems_tra[item_id];
		
        var qty = row.children().children('.rquantity_tra').val(),
        product_option = row.children().children('.roption_tra').val(),
        unit_price = formatDecimal(row.children().children('.ruprice_tra').val()),
        discount = row.children().children('.rdiscount_tra').val();
        if(item.options !== false) {
            $.each(item.options, function () {
                if(this.id == item.row.option && this.price != 0 && this.price != '' && this.price != null) {
                    unit_price = parseFloat(item.row.real_unit_price)+parseFloat(this.price);
                }
            });
        }
        var real_unit_price = item.row.real_unit_price;
        var net_price = unit_price;
        $('#prModalLabel').text(item.row.name + ' (' + item.row.code + ')');
        if (site.settings.tax1) {
            $('#ptax_tra').select2('val', item.row.tax_rate);
            $('#old_tax_tra').val(item.row.tax_rate);
            var item_discount = 0, ds = discount ? discount : '0';
            if (ds.indexOf("%") !== -1) {
                var pds = ds.split("%");
                if (!isNaN(pds[0])) {
                    item_discount = formatDecimal(parseFloat(((unit_price) * parseFloat(pds[0])) / 100), 4);
                } else {
                    item_discount = parseFloat(ds);
                }
            } else {
                item_discount = parseFloat(ds);
            }
            net_price -= item_discount;
            var pr_tax = item.row.tax_rate, pr_tax_val = 0;
            if (pr_tax !== null && pr_tax != 0) {
                $.each(tax_rates, function () {
                    if(this.id == pr_tax){
                        if (this.type == 1) {

                            if (slitems_tra[item_id].row.tax_method == 0) {
                                pr_tax_val = formatDecimal((((net_price) * parseFloat(this.rate)) / (100 + parseFloat(this.rate))), 4);
                                pr_tax_rate = formatDecimal(this.rate) + '%';
                                net_price -= pr_tax_val;
                            } else {
                                pr_tax_val = formatDecimal((((net_price) * parseFloat(this.rate)) / 100), 4);
                                pr_tax_rate = formatDecimal(this.rate) + '%';
                            }

                        } else if (this.type == 2) {

                            pr_tax_val = parseFloat(this.rate);
                            pr_tax_rate = this.rate;

                        }
                    }
                });
            }
        }
        if (site.settings.product_serial !== 0) {
            $('#pserial_tra').val(row.children().children('.rserial_tra').val());
        }
        var opt = '<p style="margin: 12px 0 0 0;">n/a</p>';
        if(item.options !== false) {
            var o = 1;
            opt = $("<select id=\"poption_tra\" name=\"poption_tra\" class=\"form-control select\" />");
            $.each(item.options, function () {
                if(o == 1) {
                    if(product_option == '') { product_variant = this.id; } else { product_variant = product_option; }
                }
                $("<option />", {value: this.id, text: this.name}).appendTo(opt);
                o++;
            });
        } else {
            product_variant = 0;
        }

        uopt = $("<select id=\"punit_tra\" name=\"punit_tra\" class=\"form-control select\" />");
        $.each(item.units, function () {
            if(this.id == item.row.unit) {
                $("<option />", {value: this.id, text: this.name, selected:true}).appendTo(uopt);
            } else {
                $("<option />", {value: this.id, text: this.name}).appendTo(uopt);
            }
        });

        $('#poptions-div_tra').html(opt);
        $('#punits-div_tra').html(uopt);
        $('select.select').select2({minimumResultsForSearch: 7});
        $('#pquantity_tra').val(qty);
        $('#old_qty_tra').val(qty);
        $('#pprice_tra').val(unit_price);
		
        $('#punit_price_tra').val(formatDecimal(parseFloat(unit_price)+parseFloat(pr_tax_val)));
        $('#poption_tra').select2('val', item.row.option);
        $('#old_price_tra').val(unit_price);
        $('#row_id_tra').val(row_id);
        $('#item_id_tra').val(item_id);
        $('#pserial_tra').val(row.children().children('.rserial').val());
        $('#pdiscount_tra').val(discount);
        $('#net_price_tra').text(formatMoney(net_price));
        $('#pro_tax_tra').text(formatMoney(pr_tax_val));
        $('#prModal_tra').appendTo("body").modal('show');

    });
	
    $('#prModal').on('shown.bs.modal', function (e) {
        if($('#poption').select2('val') != '') {
            $('#poption').select2('val', product_variant);
            product_variant = 0;
        }
    });
	
	 $('#prModal_tra').on('shown.bs.modal', function (e) {
        if($('#poption').select2('val') != '') {
            $('#poption').select2('val', product_variant);
            product_variant = 0;
        }
    });
	
    $(document).on('change', '#pprice, #ptax, #pdiscount', function () {
        var row = $('#' + $('#row_id').val());
        var item_id = row.attr('data-item-id');
        var unit_price = parseFloat($('#pprice').val());
        var item = slitems[item_id];
        var ds = $('#pdiscount').val() ? $('#pdiscount').val() : '0';
        if (ds.indexOf("%") !== -1) {
            var pds = ds.split("%");
            if (!isNaN(pds[0])) {
                item_discount = parseFloat(((unit_price) * parseFloat(pds[0])) / 100);
            } else {
                item_discount = parseFloat(ds);
            }
        } else {
            item_discount = parseFloat(ds);
        }
        unit_price -= item_discount;
        var pr_tax = $('#ptax').val(), item_tax_method = item.row.tax_method;
        var pr_tax_val = 0, pr_tax_rate = 0;
        if (pr_tax !== null && pr_tax != 0) {
            $.each(tax_rates, function () {
                if(this.id == pr_tax){
                    if (this.type == 1) {

                        if (item_tax_method == 0) {
                            pr_tax_val = formatDecimal(((unit_price) * parseFloat(this.rate)) / (100 + parseFloat(this.rate)), 4);
                            pr_tax_rate = formatDecimal(this.rate) + '%';
                            unit_price -= pr_tax_val;
                        } else {
                            pr_tax_val = formatDecimal((((unit_price) * parseFloat(this.rate)) / 100), 4);
                            pr_tax_rate = formatDecimal(this.rate) + '%';
                        }

                    } else if (this.type == 2) {

                        pr_tax_val = parseFloat(this.rate);
                        pr_tax_rate = this.rate;

                    }
                }
            });
        }

        $('#net_price').text(formatMoney(unit_price));
        $('#pro_tax').text(formatMoney(pr_tax_val));
    });

    $(document).on('change', '#punit', function () {
        var row = $('#' + $('#row_id').val());
        var item_id = row.attr('data-item-id');
        var item = slitems[item_id];
        if (!is_numeric($('#pquantity').val()) || parseFloat($('#pquantity').val()) < 0) {
            $(this).val(old_row_qty);
            bootbox.alert(lang.unexpected_value);
            return;
        }
        var opt = $('#poption').val(), unit = $('#punit').val(), base_quantity = $('#pquantity').val(), aprice = 0;
        if(item.options !== false) {
            $.each(item.options, function () {
                if(this.id == opt && this.price != 0 && this.price != '' && this.price != null) {
                    aprice = parseFloat(this.price);
                }
            });
        }
        if(unit != slitems[item_id].row.base_unit) {
            $.each(item.units, function(){
                if (this.id == unit) {
                    base_quantity = unitToBaseQty($('#pquantity').val(), this);
                    $('#pprice').val(formatDecimal(((parseFloat(item.row.base_unit_price+aprice))*unitToBaseQty(1, this)), 4)).change();
                }
            });
        } else {
            $('#pprice').val(formatDecimal(item.row.base_unit_price+aprice)).change();
        }
    });

    /* -----------------------
     * Edit Row Method
     ----------------------- */
     $(document).on('click', '#editItem', function () {
        var row = $('#' + $('#row_id').val());
        var item_id = row.attr('data-item-id'), new_pr_tax = $('#ptax').val(), new_pr_tax_rate = false;
        if (new_pr_tax) {
            $.each(tax_rates, function () {
                if (this.id == new_pr_tax) {
                    new_pr_tax_rate = this;
                }
            });
        }
        var price = parseFloat($('#pprice').val());
        if(item.options !== false) {
            var opt = $('#poption').val();
            $.each(item.options, function () {
                if(this.id == opt && this.price != 0 && this.price != '' && this.price != null) {
                    price = price-parseFloat(this.price);
                }
            });
        }
        if (site.settings.product_discount == 1 && $('#pdiscount').val()) {
            if(!is_valid_discount($('#pdiscount').val()) || $('#pdiscount').val() > price) {
                bootbox.alert(lang.unexpected_value);
                return false;
            }
        }
        if (!is_numeric($('#pquantity').val()) || parseFloat($('#pquantity').val()) < 0) {
            $(this).val(old_row_qty);
            bootbox.alert(lang.unexpected_value);
            return;
        }
        var unit = $('#punit').val();
        var base_quantity = parseFloat($('#pquantity').val());
        if(unit != slitems[item_id].row.base_unit) {
            $.each(slitems[item_id].units, function(){
                if (this.id == unit) {
                    base_quantity = unitToBaseQty($('#pquantity').val(), this);
                }
            });
        }
        slitems[item_id].row.fup = 1,
        slitems[item_id].row.qty = parseFloat($('#pquantity').val()),
        slitems[item_id].row.base_quantity = parseFloat(base_quantity),
        slitems[item_id].row.real_unit_price = price,
        slitems[item_id].row.unit = unit,
        slitems[item_id].row.unit_price = price,    
        slitems[item_id].row.tax_rate = new_pr_tax,
        slitems[item_id].tax_rate = new_pr_tax_rate,
        slitems[item_id].row.discount = $('#pdiscount').val() ? $('#pdiscount').val() : '',
        slitems[item_id].row.option = $('#poption').val() ? $('#poption').val() : '',
        slitems[item_id].row.serial = $('#pserial').val();
        localStorage.setItem('slitems', JSON.stringify(slitems));
        $('#prModal').modal('hide');

        loadItems();
        return;
    });
	 $(document).on('click', '#editItem_tra', function () {
        var row = $('#' + $('#row_id_tra').val());
        var item_id = row.attr('data-item-id'), new_pr_tax = $('#ptax_tra').val(), new_pr_tax_rate = false;
        if (new_pr_tax) {
            $.each(tax_rates, function () {
                if (this.id == new_pr_tax) {
                    new_pr_tax_rate = this;
                }
            });
        }
		console.log(item_id);
        var price = parseFloat($('#pprice_tra').val());
        if(item.options !== false) {
            var opt = $('#poption_tra').val();
            $.each(item.options, function () {
                if(this.id == opt && this.price != 0 && this.price != '' && this.price != null) {
                    price = price-parseFloat(this.price);
                }
            });
        }
        if (site.settings.product_discount == 1 && $('#pdiscount_tra').val()) {
            if(!is_valid_discount($('#pdiscount_tra').val()) || $('#pdiscount_tra').val() > price) {
                bootbox.alert(lang.unexpected_value);
                return false;
            }
        }
        if (!is_numeric($('#pquantity_tra').val()) || parseFloat($('#pquantity_tra').val()) < 0) {
            $(this).val(old_row_qty);
            bootbox.alert(lang.unexpected_value);
            return;
        }
        var unit = $('#punit_tra').val();
        var base_quantity = parseFloat($('#pquantity_tra').val());
        if(unit != slitems_tra[item_id].row.base_unit) {
            $.each(slitems_tra[item_id].units, function(){
                if (this.id == unit) {
                    base_quantity = unitToBaseQty($('#pquantity_tra').val(), this);
                }
            });
        }
        slitems_tra[item_id].row.fup = 1,
        slitems_tra[item_id].row.qty = parseFloat($('#pquantity_tra').val()),
        slitems_tra[item_id].row.base_quantity = parseFloat(base_quantity),
        slitems_tra[item_id].row.real_unit_price = price,
        slitems_tra[item_id].row.unit = unit,
        slitems_tra[item_id].row.tax_rate = new_pr_tax,
        slitems_tra[item_id].tax_rate = new_pr_tax_rate,
        slitems_tra[item_id].row.discount = $('#pdiscount_tra').val() ? $('#pdiscount_tra').val() : '',
        slitems_tra[item_id].row.option = $('#poption_tra').val() ? $('#poption_tra').val() : '',
        slitems_tra[item_id].row.serial = $('#pserial_tra').val();
        localStorage.setItem('slitems_tra', JSON.stringify(slitems_tra));
        $('#prModal_tra').modal('hide');

        loadItems_Tra();
        return;
    });
	
    /* -----------------------
     * Product option change
     ----------------------- */
    $(document).on('change', '#poption', function () {
        var row = $('#' + $('#row_id').val()), opt = $(this).val();
        var item_id = row.attr('data-item-id');
        var item = slitems[item_id];
        var unit = $('#punit').val(), base_quantity = parseFloat($('#pquantity').val()), base_unit_price = item.row.base_unit_price;
        if(unit != slitems[item_id].row.base_unit) {
            $.each(slitems[item_id].units, function(){
                if (this.id == unit) {
                    base_unit_price = formatDecimal((parseFloat(item.row.base_unit_price)*(unitToBaseQty(1, this))), 4)
                    base_quantity = unitToBaseQty($('#pquantity').val(), this);
                }
            });
        }
        $('#pprice').val(parseFloat(base_unit_price)).trigger('change');
        if(item.options !== false) {
            $.each(item.options, function () {
                if(this.id == opt && this.price != 0 && this.price != '' && this.price != null) {
                    $('#pprice').val(parseFloat(base_unit_price)+(parseFloat(this.price))).trigger('change');
                }
            });
        }
    });

     /* ------------------------------
     * Sell Gift Card modal
     ------------------------------- */
     $(document).on('click', '#sellGiftCard', function (e) {
        if (count == 1) {
            slitems = {};
            if ($('#slwarehouse').val() && $('#slcustomer').val()) {
                $('#slcustomer').select2("readonly", true);
                $('#slwarehouse').select2("readonly", true);
            } else {
                bootbox.alert('Vui lòng chọn khách hàng');
                item = null;
                return false;
            }
        }
        $('#gcModal').appendTo("body").modal('show');
        return false;
    });

     $(document).on('click', '#addGiftCard', function (e) {
        var mid = (new Date).getTime(),
        gccode = $('#gccard_no').val(),
        gcname = $('#gcname').val(),
        gcvalue = $('#gcvalue').val(),
        gccustomer = $('#gccustomer').val(),
        gcexpiry = $('#gcexpiry').val() ? $('#gcexpiry').val() : '',
        gcprice = parseFloat($('#gcprice').val());
        if(gccode == '' || gcvalue == '' || gcprice == '' || gcvalue == 0 || gcprice == 0) {
            $('#gcerror').text('Please fill the required fields');
            $('.gcerror-con').show();
            return false;
        }

        var gc_data = new Array();
        gc_data[0] = gccode;
        gc_data[1] = gcvalue;
        gc_data[2] = gccustomer;
        gc_data[3] = gcexpiry;
        //if (typeof slitems === "undefined") {
        //    var slitems = {};
        //}

        $.ajax({
            type: 'get',
            url: site.base_url+'sales/sell_gift_card',
            dataType: "json",
            data: { gcdata: gc_data },
            success: function (data) {
                if(data.result === 'success') {
                    slitems[mid] = {"id": mid, "item_id": mid, "label": gcname + ' (' + gccode + ')', "row": {"id": mid, "code": gccode, "name": gcname, "quantity": 1, "price": gcprice, "real_unit_price": gcprice, "tax_rate": 0, "qty": 1, "type": "manual", "discount": "0", "serial": "", "option":""}, "tax_rate": false, "options":false};
                    localStorage.setItem('slitems', JSON.stringify(slitems));
                    loadItems();
                    $('#gcModal').modal('hide');
                    $('#gccard_no').val('');
                    $('#gcvalue').val('');
                    $('#gcexpiry').val('');
                    $('#gcprice').val('');
                } else {
                    $('#gcerror').text(data.message);
                    $('.gcerror-con').show();
                }
            }
        });
        return false;
    });

    /* ------------------------------
     * Show manual item addition modal
     ------------------------------- */
     $(document).on('click', '#addManually', function (e) {
        if (count == 1) {
            slitems = {};
            if ($('#slwarehouse').val() && $('#slcustomer').val()) {
                $('#slcustomer').select2("readonly", true);
                $('#slwarehouse').select2("readonly", true);
            } else {
                bootbox.alert('Vui lòng chọn khách hàng');
                item = null;
                return false;
            }
        }
        $('#mnet_price').text('0.00');
        $('#mpro_tax').text('0.00');
        $('#mModal').appendTo("body").modal('show');
        return false;
    });

     $(document).on('click', '#addItemManually', function (e) {
        var mid = (new Date).getTime(),
        mcode = $('#mcode').val(),
        mname = $('#mname').val(),
        mtax = parseInt($('#mtax').val()),
        mqty = parseFloat($('#mquantity').val()),
        mdiscount = $('#mdiscount').val() ? $('#mdiscount').val() : '0',
        unit_price = parseFloat($('#mprice').val()),
        mtax_rate = {};
        if (mcode && mname && mqty && unit_price) {
            $.each(tax_rates, function () {
                if (this.id == mtax) {
                    mtax_rate = this;
                }
            });

            slitems[mid] = {"id": mid, "item_id": mid, "label": mname + ' (' + mcode + ')', "row": {"id": mid, "code": mcode, "name": mname, "quantity": mqty, "price": unit_price, "unit_price": unit_price, "real_unit_price": unit_price, "tax_rate": mtax, "tax_method": 0, "qty": mqty, "type": "manual", "discount": mdiscount, "serial": "", "option":""}, "tax_rate": mtax_rate, 'units': false, "options":false};
            localStorage.setItem('slitems', JSON.stringify(slitems));
            loadItems();
        }
        $('#mModal').modal('hide');
        $('#mcode').val('');
        $('#mname').val('');
        $('#mtax').val('');
        $('#mquantity').val('');
        $('#mdiscount').val('');
        $('#mprice').val('');
        return false;
    });

    $(document).on('change', '#mprice, #mtax, #mdiscount', function () {
        var unit_price = parseFloat($('#mprice').val());
        var ds = $('#mdiscount').val() ? $('#mdiscount').val() : '0';
        if (ds.indexOf("%") !== -1) {
            var pds = ds.split("%");
            if (!isNaN(pds[0])) {
                item_discount = parseFloat(((unit_price) * parseFloat(pds[0])) / 100);
            } else {
                item_discount = parseFloat(ds);
            }
        } else {
            item_discount = parseFloat(ds);
        }
        unit_price -= item_discount;
        var pr_tax = $('#mtax').val(), item_tax_method = 0;
        var pr_tax_val = 0, pr_tax_rate = 0;
        if (pr_tax !== null && pr_tax != 0) {
            $.each(tax_rates, function () {
                if(this.id == pr_tax){
                    if (this.type == 1) {

                        if (item_tax_method == 0) {
                            pr_tax_val = formatDecimal((((unit_price) * parseFloat(this.rate)) / (100 + parseFloat(this.rate))), 4);
                            pr_tax_rate = formatDecimal(this.rate) + '%';
                            unit_price -= pr_tax_val;
                        } else {
                            pr_tax_val = formatDecimal((((unit_price) * parseFloat(this.rate)) / 100), 4);
                            pr_tax_rate = formatDecimal(this.rate) + '%';
                        }

                    } else if (this.type == 2) {

                        pr_tax_val = parseFloat(this.rate);
                        pr_tax_rate = this.rate;

                    }
                }
            });
        }

        $('#mnet_price').text(formatMoney(unit_price));
        $('#mpro_tax').text(formatMoney(pr_tax_val));
    });

    /* --------------------------
     * Edit Row Quantity Method
    --------------------------- */
    var old_row_qty;
    $(document).on("focus", '.rquantity', function () {
        old_row_qty = $(this).val();
    }).on("change", '.rquantity', function () {
        var row = $(this).closest('tr');
        if (!is_numeric($(this).val()) || parseFloat($(this).val()) < 0) {
            $(this).val(old_row_qty);
            bootbox.alert('Chỉ chấp nhận kiểu số');
            return;
        }
        var new_qty = parseFloat($(this).val()),
        item_id = row.attr('data-item-id');
        id_sanpham = row.attr('data-id-sanpham');
        id_km = row.attr('data-id-khuyenmai');
         var curn = row.find('.data_id_khuyenmai').val();
        console.log('data_id_khuyenmai:'+id_sanpham);
         
        //tien hanh clear san pham khuyen mai da add 
        if(id_sanpham>0){
            $("#slTable tbody tr").each(function(e){
                var cls=$(this).attr("data-id-khuyenmai");                  
                if(id_sanpham==cls&&curn==0){
                    console.log('Deleting :'+id_sanpham);
                    var xoa = $(this).attr('data-item-id-remove');              
                    delete slitems[xoa];
                    $(this).remove();
                    if(slitems.hasOwnProperty(xoa)) { } else {
                        localStorage.setItem('slitems', JSON.stringify(slitems));
                        loadItems();
                    }
                }
            }); 
        }

        slitems[item_id].row.base_quantity = new_qty;
        if(slitems[item_id].row.unit != slitems[item_id].row.base_unit) {
            $.each(slitems[item_id].units, function(){
                if (this.id == slitems[item_id].row.unit) {
                    slitems[item_id].row.base_quantity = unitToBaseQty(new_qty, this);
                }
            });
        }
        //console.log('id_sanpham:'+id_sanpham);
        /*tien hanh kiem tra khuyen mai xem co event nao dang dien ra hay khong*/
        if(khuyenmai_main){
            
            var check=jQuery.inArray(id_sanpham, main_product);
            
            /*kiem tra xem san pham co phai la san pham khuyen mai khong*/
            if(check!==-1){
                                
                /*kiem tra xem so luong >= main_quantity*/
                if(new_qty>=khuyenmai_main[id_sanpham].main_quantity){
                            
                    if (id_sanpham!=id_km) {                     
                        var khuyenmai_id=khuyenmai_main[id_sanpham].id;
                        var list_sub_pr=sub_product[khuyenmai_id];
                        
                        jQuery.each( list_sub_pr, function( i, val ) {
                            var sub_product_id=list_sub_pr[i].sub_product_id;
                            var sub_quantity=list_sub_pr[i].sub_quantity;
                            if (parseInt(new_qty/khuyenmai_main[id_sanpham].main_quantity)>0) {
                                sub_quantity=parseInt(new_qty/khuyenmai_main[id_sanpham].main_quantity)*sub_quantity;
                            }
                           // console.log(list_sub_pr[i]);
                            var giakhuyenmai=list_sub_pr[i].giakhuyenmai;
                            $.ajax({
                                type: 'get',
                                url:  site.base_url+'sales/suggestionsById',
                                dataType: "json",
                                data: {
                                    term:sub_product_id,quantity:sub_quantity,price:giakhuyenmai
                                },
                                success: function (ui) {
                                    ui.row.code='[KHUYẾN MÃI]';
                                    ui.row.khuyenmai_main=id_sanpham;
                                    add_invoice_item(ui,true,true);
                                }
                            });
                            
                        });
                    }
                    
                    /*auto load list sub product sub_product*/  
                }           
            }
        }

        slitems[item_id].row.qty = new_qty;
        localStorage.setItem('slitems', JSON.stringify(slitems));
        loadItems();
    });
	
	 var old_row_qty_tra;
    $(document).on("focus", '.rquantity_tra', function () {
        old_row_qty_tra = $(this).val();
    }).on("change", '.rquantity_tra', function () {
        var row = $(this).closest('tr');
        if (!is_numeric($(this).val()) || parseFloat($(this).val()) < 0) {
            $(this).val(old_row_qty_tra);
            bootbox.alert('Chỉ chấp nhận kiểu số');
            return;
        }
        var new_qty = parseFloat($(this).val()),
        item_id = row.attr('data-item-id');
        slitems_tra[item_id].row.base_quantity = new_qty;
        if(slitems_tra[item_id].row.unit != slitems_tra[item_id].row.base_unit) {
            $.each(slitems_tra[item_id].units, function(){
                if (this.id == slitems_tra[item_id].row.unit) {
                    slitems_tra[item_id].row.base_quantity = unitToBaseQty(new_qty, this);
                }
            });
        }
        slitems_tra[item_id].row.qty = new_qty;
        localStorage.setItem('slitems_tra', JSON.stringify(slitems_tra));
        loadItems_Tra();
    });

    /* --------------------------
     * Edit Row Price Method
     -------------------------- */
     var old_price;
     $(document).on("focus", '.rprice', function () {
        old_price = $(this).val();
    }).on("change", '.rprice', function () {
        var row = $(this).closest('tr');
        if (!is_numeric($(this).val())) {
            $(this).val(old_price);
            bootbox.alert(lang.unexpected_value);
            return;
        }
        var new_price = parseFloat($(this).val()),
        item_id = row.attr('data-item-id');
        slitems[item_id].row.price = new_price;
        localStorage.setItem('slitems', JSON.stringify(slitems));
        loadItems();
    });
	var old_price_tra;
     $(document).on("focus", '.rprice_tra', function () {
        old_price_tra = $(this).val();
    }).on("change", '.rprice_tra', function () {
        var row = $(this).closest('tr');
        if (!is_numeric($(this).val())) {
            $(this).val(old_price_tra);
            bootbox.alert(lang.unexpected_value);
            return;
        }
        var new_price = parseFloat($(this).val()),
        item_id = row.attr('data-item-id');
        slitems[item_id].row.price = new_price;
        localStorage.setItem('slitems_tra', JSON.stringify(slitems));
        loadItems_Tra();
    });

    $(document).on("click", '#removeReadonly', function () {
        $('#slcustomer').select2('readonly', false);
        //$('#slwarehouse').select2('readonly', false);
        return false;
    });


});
/* -----------------------
 * Misc Actions
 ----------------------- */

// hellper function for customer if no localStorage value
function nsCustomer() {
    $('#slcustomer').select2({
        minimumInputLength: 1,
        ajax: {
            url: site.base_url + "customers/suggestions",
            dataType: 'json',
            quietMillis: 15,
            data: function (term, page) {
                return {
                    term: term,
                    limit: 10
                };
            },
            results: function (data, page) {
                if (data.results != null) {
                    return {results: data.results};
                } else {
                    return {results: [{id: '', text: 'No Match Found'}]};
                }
            }
        }
    });
}
//localStorage.clear();
function loadItems() {

    if (localStorage.getItem('slitems')) {
        total = 0;
        count = 1;
        an = 1;
        product_tax = 0;
        invoice_tax = 0;
        product_discount = 0;
        order_discount = 0;
        total_discount = 0;

        $("#slTable tbody").empty();
        slitems = JSON.parse(localStorage.getItem('slitems'));
        sortedItems = (site.settings.item_addition == 1) ? _.sortBy(slitems, function(o){return [parseInt(o.order)];}) :   slitems;
        $('#add_sale, #edit_sale').attr('disabled', false);
        $.each(sortedItems, function () {

            var item = this;
            console.log(item);
        //    var item_id = site.settings.item_addition == 1 ? item.item_id : item.id;
          var idsanpham=item.item_id;

            var item_id = item.id;//site.settings.item_addition == 1 ? item.item_id : item.id;

            item.order = item.order ? item.order : new Date().getTime();
            var product_id = item.row.id, item_type = item.row.type, combo_items = item.combo_items, item_price = item.row.price, item_qty = item.row.qty, item_aqty = item.row.quantity, item_tax_method = item.row.tax_method, item_ds = item.row.discount, item_discount = 0, item_option = item.row.option, item_code = item.row.code, item_serial = item.row.serial, item_name = item.row.name.replace(/"/g, "&#034;").replace(/'/g, "&#039;");
            var product_unit = item.row.unit, base_quantity = item.row.base_quantity;
            
			var p_gia_set_giasi=item.row.gia_si;
			var unit_price=0;
			if(item.row.price){
				unit_price = item.row.price;
			}
			
			if(item.row.unit_price)
			{
				unit_price = item.row.unit_price;
			}
			
            if(item.row.fup != 1 && product_unit != item.row.base_unit) {
                $.each(item.units, function(){
                    if (this.id == product_unit) {
                        base_quantity = formatDecimal(unitToBaseQty(item.row.qty, this), 4);
                        unit_price = formatDecimal((parseFloat(item.row.base_unit_price)*(unitToBaseQty(1, this))), 4);
                        item.row.real_unit_price=unit_price;
                    }
                });
            }
            if(item.options !== false) {
                $.each(item.options, function () {
                    if(this.id == item.row.option && this.price != 0 && this.price != '' && this.price != null) {
                        item_price = item.row.base_unit_price+(parseFloat(this.price));
                        unit_price = item_price;
                        item.row.real_unit_price=unit_price;
                    }
                });
            }

            var ds = item_ds ? item_ds : '0';
            if (ds.indexOf("%") !== -1) {
                var pds = ds.split("%");
                if (!isNaN(pds[0])) {
                    item_discount = formatDecimal((((unit_price) * parseFloat(pds[0])) / 100), 4);
                } else {
                    item_discount = formatDecimal(ds);
                }
            } else {
                 item_discount = formatDecimal(ds);
            }
            product_discount += parseFloat(item_discount * item_qty);

            unit_price = formatDecimal(unit_price);
			//lhson code 17/03/2018 0944104004 unit_price = formatDecimal(unit_price-item_discount);
            var pr_tax = item.tax_rate;
            var pr_tax_val = 0, pr_tax_rate = 0;
            if (site.settings.tax1 == 1) {
                if (pr_tax !== false) {
                    if (pr_tax.type == 1) {

                        if (item_tax_method == '0') {
                            pr_tax_val = formatDecimal((((unit_price) * parseFloat(pr_tax.rate)) / (100 + parseFloat(pr_tax.rate))), 4);
                            pr_tax_rate = formatDecimal(pr_tax.rate) + '%';
                        } else {
                            pr_tax_val = formatDecimal((((unit_price) * parseFloat(pr_tax.rate)) / 100), 4);
                            pr_tax_rate = formatDecimal(pr_tax.rate) + '%';
                        }

                    } else if (pr_tax.type == 2) {

                        pr_tax_val = parseFloat(pr_tax.rate);
                        pr_tax_rate = pr_tax.rate;

                    }
                    product_tax += pr_tax_val * item_qty;
                }
            }
            item_price = item_tax_method == 0 ? formatDecimal(unit_price-pr_tax_val, 4) : formatDecimal(unit_price);
            unit_price = formatDecimal(unit_price, 4);
            var sel_opt = '';
            $.each(item.options, function () {
                if(this.id == item_option) {
                    sel_opt = this.name;
                }
            });
            var row_no = (new Date).getTime();
              var khuyenmai_main=0;
            if(item.row.khuyenmai_main){
                khuyenmai_main=item.row.khuyenmai_main;
            }

            var newTr = $('<tr id="row_' + row_no + '" class="row_' + item_id + '" data-item-id-remove="' + item_id + '" data-id-khuyenmai="' + khuyenmai_main + '" data-item-id="' + item_id + '" data-id-sanpham="' + idsanpham + '"></tr>');
            tr_html = '<td><input name="product_id[]" type="hidden" class="rid" value="' + product_id + '"><input name="product_type[]" type="hidden" class="rtype" value="' + item_type + '"><input name="product_code[]" type="hidden" class="rcode" value="' + item_code + '"><input name="product_name[]" type="hidden" class="rname" value="' + item_name + '"><input name="product_option[]" type="hidden" class="roption" value="' + item_option + '"><span class="sname" id="name_' + row_no + '">' + item_code +' - '+ item_name +(sel_opt != '' ? ' ('+sel_opt+')' : '')+'</span> ';

             if (item.row.khuyenmai_main>0&&item.row.code=='[KHUYẾN MÃI]') {
                tr_html +=' <i class="pull-right fa fa-edit tip pointer editkm" id="' + row_no + '" data-item="' + item_id + '" title="Thay đổi KM" style="cursor:pointer;"></i></td>';
            }else{
                tr_html +=' <i class="pull-right fa fa-edit tip pointer edit" id="' + row_no + '" data-item="' + item_id + '" title="Edit" style="cursor:pointer;"></i></td>';
            }

            if (site.settings.product_serial == 1) {
                tr_html += '<td class="text-right"><input class="form-control input-sm rserial" name="serial[]" type="text" id="serial_' + row_no + '" value="'+item_serial+'"></td>';
            }
            tr_html += '<td class="text-right"><input class="form-control input-sm text-right rprice" name="net_price[]" type="hidden" id="price_' + row_no + '" value="' + item_price + '"><input class="ruprice" name="unit_price[]" type="hidden" value="' + unit_price + '"><input class="p_gia_set_giasi" name="p_gia_set_giasi[]" type="hidden" value="' + p_gia_set_giasi + '"><input class="realuprice" name="real_unit_price[]" type="hidden" value="' + item.row.real_unit_price + '"><span class="text-right sprice" id="sprice_' + row_no + '">' + formatMoney(item_price) + '</span></td>';
            tr_html += '<td><input class="form-control text-center rquantity" tabindex="'+((site.settings.set_focus == 1) ? an : (an+1))+'" name="quantity[]" type="text" value="' + parseFloat(item_qty) + '" data-id="' + row_no + '" data-item="' + item_id + '" id="quantity_' + row_no + '" onClick="this.select();"><input name="product_unit[]" type="hidden" class="runit" value="' + product_unit + '"><input name="product_base_quantity[]" type="hidden" class="rbase_quantity" value="' + base_quantity + '"></td>';
            if ((site.settings.product_discount == 1 && allow_discount == 1) || item_discount) {
                tr_html += '<td class="text-right"><input class="form-control input-sm rdiscount" name="product_discount[]" type="hidden" id="discount_' + row_no + '" value="' + item_ds + '"><span class="text-right sdiscount text-danger" id="sdiscount_' + row_no + '">' + formatMoney(0 - (item_discount * item_qty)) + '</span></td>';
            }
            if (site.settings.tax1 == 1) {
                tr_html += '<td class="text-right"><input class="form-control input-sm text-right rproduct_tax" name="product_tax[]" type="hidden" id="product_tax_' + row_no + '" value="' + pr_tax.id + '"><span class="text-right sproduct_tax" id="sproduct_tax_' + row_no + '">' + (parseFloat(pr_tax_rate) != 0 ? '(' + pr_tax_rate + ')' : '') + ' ' + formatMoney(pr_tax_val * item_qty) + '</span></td>';
            }
            tr_html += '<td class="text-right"><span class="text-right ssubtotal" id="subtotal_' + row_no + '">' + formatMoney(((parseFloat(item_price) + parseFloat(pr_tax_val)) * parseFloat(item_qty))-(item_qty*item_discount)) + '</span></td>';
            tr_html += '<td class="text-center"><i class="fa fa-times tip pointer sldel" id="' + row_no + '" title="Remove" style="cursor:pointer;"></i><input name="data_id_khuyenmai[]" type="hidden" class="data_id_khuyenmai" value="' + khuyenmai_main + '"></td>';
            newTr.html(tr_html);
            newTr.prependTo("#slTable");
            total += formatDecimal(((parseFloat(item_price) + parseFloat(pr_tax_val)) * parseFloat(item_qty))-(item_discount*parseFloat(item_qty)), 4);
            count += parseFloat(item_qty);
            an++;

            if (item_type == 'standard' && item.options !== false) {
                $.each(item.options, function () {
                    if(this.id == item_option && base_quantity > this.quantity) {
                        $('#row_' + row_no).addClass('danger');
                        if(site.settings.overselling != 1) { $('#add_sale, #edit_sale').attr('disabled', true); }
                    }
                });
            } else if(item_type == 'standard' && base_quantity > item_aqty) {
                $('#row_' + row_no).addClass('danger');
                if(site.settings.overselling != 1) { $('#add_sale, #edit_sale').attr('disabled', true); }
            } else if (item_type == 'combo') {
                if(combo_items === false) {
                    $('#row_' + row_no).addClass('danger');
                    if(site.settings.overselling != 1) { $('#add_sale, #edit_sale').attr('disabled', true); }
                } else {
                    $.each(combo_items, function() {
                       if(parseFloat(this.quantity) < (parseFloat(this.qty)*base_quantity) && this.type == 'standard') {
                           $('#row_' + row_no).addClass('danger');
                           if(site.settings.overselling != 1) { $('#add_sale, #edit_sale').attr('disabled', true); }
                       }
                   });
                }
            }

        });

        var col = 2;
        if (site.settings.product_serial == 1) { col++; }
        var tfoot = '<tr id="tfoot" class="tfoot active"><th colspan="'+col+'">Total</th><th class="text-center">' + formatNumber(parseFloat(count) - 1) + '</th>';
        if ((site.settings.product_discount == 1 && allow_discount == 1) || product_discount) {
            tfoot += '<th class="text-right">'+formatMoney(product_discount)+'</th>';
        }
        if (site.settings.tax1 == 1) {
            tfoot += '<th class="text-right">'+formatMoney(product_tax)+'</th>';
        }
        tfoot += '<th class="text-right">'+formatMoney(total)+'</th><th class="text-center"><i class="fa fa-trash-o" style="opacity:0.5; filter:alpha(opacity=50);"></i></th></tr>';
        $('#slTable tfoot').html(tfoot);

        // Order level discount calculations
        if (sldiscount = localStorage.getItem('sldiscount')) {
            var ds = sldiscount;
            if (ds.indexOf("%") !== -1) {
                var pds = ds.split("%");
                if (!isNaN(pds[0])) {
                    order_discount = formatDecimal((((total) * parseFloat(pds[0])) / 100), 4);
                } else {
                    order_discount = formatDecimal(ds);
                }
            } else {
                order_discount = formatDecimal(ds);
            }

            //total_discount += parseFloat(order_discount);
        }

        // Order level tax calculations
        if (site.settings.tax2 != 0) {
            if (sltax2 = localStorage.getItem('sltax2')) {
                $.each(tax_rates, function () {
                    if (this.id == sltax2) {
                        if (this.type == 2) {
                            invoice_tax = formatDecimal(this.rate);
                        } else if (this.type == 1) {
                            invoice_tax = formatDecimal((((total - order_discount) * this.rate) / 100), 4);
                        }
                    }
                });
            }
        }

        total_discount = parseFloat(order_discount + product_discount);
        // Totals calculations after item addition
        var gtotal = parseFloat(((total + invoice_tax) - order_discount) + shipping);
        $('#total').text(formatMoney(total));
        $('#titems').text((an - 1) + ' (' + formatNumber(parseFloat(count) - 1) + ')');
        $('#total_items').val((parseFloat(count) - 1));
        //$('#tds').text('('+formatMoney(product_discount)+'+'+formatMoney(order_discount)+')'+formatMoney(total_discount));
        $('#tds').text(formatMoney(order_discount));
        if (site.settings.tax2 != 0) {
            $('#ttax2').text(formatMoney(invoice_tax));
        }
        $('#tship').text(formatMoney(shipping));
        $('#gtotal').text(formatMoney(gtotal));
        if (an > parseInt(site.settings.bc_fix) && parseInt(site.settings.bc_fix) > 0) {
            $("html, body").animate({scrollTop: $('#sticker').offset().top}, 500);
            $(window).scrollTop($(window).scrollTop() + 1);
        }
        if (count > 1) {
            $('#slcustomer').select2("readonly", true);
            $('#slwarehouse').select2("readonly", true);
        }
        set_page_focus();
    }
}

/* -----------------------------
 * Add Sale Order Item Function
 * @param {json} item
 * @returns {Boolean}
 ---------------------------- */
 function add_invoice_item(item,checkedit=null) {
     
    if (count == 1) {
        slitems = {};
        if ($('#slwarehouse').val() && $('#slcustomer').val()) {
            $('#slcustomer').select2("readonly", true);
            $('#slwarehouse').select2("readonly", true);
        } else {
            bootbox.alert('Vui lòng chọn khách hàng');
            item = null;
            return;
        }
    }
    if (item == null)
        return;

    //var item_id = site.settings.item_addition == 1 ? item.item_id : item.id;
     var item_id = item.id;
    if (slitems[item_id]) {

        var new_qty = parseFloat(slitems[item_id].row.qty) + 1;
        slitems[item_id].row.base_quantity = new_qty;
        if(slitems[item_id].row.unit != slitems[item_id].row.base_unit) {
            $.each(slitems[item_id].units, function(){
                if (this.id == slitems[item_id].row.unit) {
                    slitems[item_id].row.base_quantity = unitToBaseQty(new_qty, this);
                }
            });
        }
        slitems[item_id].row.qty = new_qty;

    } else {
        slitems[item_id] = item;
    }

    slitems[item_id].order = new Date().getTime();
     /*tien hanh kiem tra khuyen mai xem co event nao dang dien ra hay khong*/
    if(khuyenmai_main&&checkedit==null){
        var id_sanpham=item.item_id;
        if(id_sanpham>0){
            $("#slTable tbody tr").each(function(e){
                var cls=$(this).attr("data-id-khuyenmai");  
                var sanpham=$(this).attr("data-id-sanpham");    
                if(sanpham==id_sanpham&&cls==0){
                    var new_qty = parseFloat(slitems[item_id].row.qty) + 1;
                    slitems[item_id].row.qty = new_qty;
                    
                    var xoa = $(this).attr('data-item-id-remove');              
                    delete slitems[xoa];
                    $(this).remove();
                    if(slitems.hasOwnProperty(xoa)) { } else {
                        localStorage.setItem('slitems', JSON.stringify(slitems));
                        loadItems();
                    }
                    
                }
                if(id_sanpham==cls){
                    var xoa = $(this).attr('data-item-id-remove');              
                    delete slitems[xoa];
                    $(this).remove();
                    if(slitems.hasOwnProperty(xoa)) { } else {
                        localStorage.setItem('slitems', JSON.stringify(slitems));
                        loadItems();
                    }
                }
            }); 
        }
    
        var check=jQuery.inArray(id_sanpham, main_product);
        
        /*kiem tra xem san pham co phai la san pham khuyen mai khong*/
        if(check!==-1){
                            
            /*kiem tra xem so luong >= main_quantity*/
            if(slitems[item_id].row.qty>=khuyenmai_main[id_sanpham].main_quantity){
                                    
                var khuyenmai_id=khuyenmai_main[id_sanpham].id;
                console.log(khuyenmai_id);
                var list_sub_pr=sub_product[khuyenmai_id];
                
                jQuery.each( list_sub_pr, function( i, val ) {
                    var sub_product_id=list_sub_pr[i].sub_product_id;
                    var sub_quantity=list_sub_pr[i].sub_quantity;
                    var giakhuyenmai=list_sub_pr[i].giakhuyenmai;
                    $.ajax({
                        type: 'get',
                        url:  site.base_url+'sales/suggestionsById',
                        dataType: "json",
                        data: {
                            term:sub_product_id,quantity:sub_quantity,price:giakhuyenmai
                        },
                        success: function (ui) {
                            ui.row.code='[KHUYẾN MÃI]';
                            ui.row.khuyenmai_main=id_sanpham;
                            add_invoice_item(ui,true);
                        }
                    });
                    
                });
                                
                /*auto load list sub product sub_product*/  
            }           
        }
    }    
    localStorage.setItem('slitems', JSON.stringify(slitems));
    loadItems();
    return true;
}
function add_invoice_item_tra(item) {
	
    if (count_tra == 1) {
        slitems_tra = {};
        if ($('#slwarehouse').val() && $('#slcustomer').val()) {
            $('#slcustomer').select2("readonly", true);
            $('#slwarehouse').select2("readonly", true);
        } else {
            bootbox.alert('Vui lòng chọn khách hàng');
            item = null;
            return;
        } 
    }
	
    if (item == null)
        return;

    var item_id = site.settings.item_addition == 1 ? item.item_id : item.id;
    if (slitems_tra[item_id]) {

        var new_qty = parseFloat(slitems_tra[item_id].row.qty) + 1;
        slitems_tra[item_id].row.base_quantity = new_qty;
        if(slitems_tra[item_id].row.unit != slitems_tra[item_id].row.base_unit) {
            $.each(slitems_tra[item_id].units, function(){
                if (this.id == slitems_tra[item_id].row.unit) {
                    slitems_tra[item_id].row.base_quantity = unitToBaseQty(new_qty, this);
                }
            });
        }
        slitems_tra[item_id].row.qty = new_qty;

    } else {
        slitems_tra[item_id] = item;
    }
    slitems_tra[item_id].order = new Date().getTime();
    localStorage.setItem('slitems_tra', JSON.stringify(slitems_tra));
	console.log(slitems_tra);
    loadItems_Tra();
    return true;
}
if (typeof (Storage) === "undefined") {
    $(window).bind('beforeunload', function (e) {
        if (count > 1) {
            var message = "You will loss data!";
            return message;
        }
    });
}

function loadItems_Tra() {

    if (localStorage.getItem('slitems_tra')) {
		
        total = 0;
        count_tra = 1;
        an_tra = 1;
        product_tax = 0;
        invoice_tax = 0;
        product_discount = 0;
        order_discount = 0;
        total_discount = 0;

        $("#slTable_tra tbody").empty();
        slitems_tra = JSON.parse(localStorage.getItem('slitems_tra'));
		
        sortedItems = (site.settings.item_addition == 1) ? _.sortBy(slitems_tra, function(o){return [parseInt(o.order)];}) :   slitems_tra;
        $('#add_sale, #edit_sale').attr('disabled', false);
        $.each(sortedItems, function () {
            var item = this;
            var item_id = site.settings.item_addition == 1 ? item.item_id : item.id;
            item.order = item.order ? item.order : new Date().getTime();
            var product_id = item.row.id, item_type = item.row.type, combo_items = item.combo_items, item_price = item.row.price, item_qty = item.row.qty, item_aqty = item.row.quantity, item_tax_method = item.row.tax_method, item_ds = item.row.discount, item_discount = 0, item_option = item.row.option, item_code = item.row.code, item_serial = item.row.serial, item_name = item.row.name.replace(/"/g, "&#034;").replace(/'/g, "&#039;");
            var product_unit = item.row.unit, base_quantity = item.row.base_quantity;
            var unit_price = item.row.price;
			var p_gia_set_giasi=item.row.gia_si;
			
            if(item.row.fup != 1 && product_unit != item.row.base_unit) {
                $.each(item.units, function(){
                    if (this.id == product_unit) {
                        base_quantity = formatDecimal(unitToBaseQty(item.row.qty, this), 4);
                        unit_price = formatDecimal((parseFloat(item.row.base_unit_price)*(unitToBaseQty(1, this))), 4);
                    }
                });
            }
            if(item.options !== false) {
                $.each(item.options, function () {
                    if(this.id == item.row.option && this.price != 0 && this.price != '' && this.price != null) {
                        item_price = unit_price+(parseFloat(this.price));
                        unit_price = item_price;
                    }
                });
            }

            var ds = item_ds ? item_ds : '0';
            if (ds.indexOf("%") !== -1) {
                var pds = ds.split("%");
                if (!isNaN(pds[0])) {
                    item_discount = formatDecimal((((unit_price) * parseFloat(pds[0])) / 100), 4);
                } else {
                    item_discount = formatDecimal(ds);
                }
            } else {
                 item_discount = formatDecimal(ds);
            }
            product_discount += parseFloat(item_discount * item_qty);

            unit_price = formatDecimal(unit_price);
			//lhson code 17/03/2018 0944104004 unit_price = formatDecimal(unit_price-item_discount);
            var pr_tax = item.tax_rate;
            var pr_tax_val = 0, pr_tax_rate = 0;
            if (site.settings.tax1 == 1) {
                if (pr_tax !== false) {
                    if (pr_tax.type == 1) {

                        if (item_tax_method == '0') {
                            pr_tax_val = formatDecimal((((unit_price) * parseFloat(pr_tax.rate)) / (100 + parseFloat(pr_tax.rate))), 4);
                            pr_tax_rate = formatDecimal(pr_tax.rate) + '%';
                        } else {
                            pr_tax_val = formatDecimal((((unit_price) * parseFloat(pr_tax.rate)) / 100), 4);
                            pr_tax_rate = formatDecimal(pr_tax.rate) + '%';
                        }

                    } else if (pr_tax.type == 2) {

                        pr_tax_val = parseFloat(pr_tax.rate);
                        pr_tax_rate = pr_tax.rate;

                    }
                    product_tax += pr_tax_val * item_qty;
                }
            }
            item_price = item_tax_method == 0 ? formatDecimal(unit_price-pr_tax_val, 4) : formatDecimal(unit_price);
            unit_price = formatDecimal(unit_price, 4);
            var sel_opt = '';
            $.each(item.options, function () {
                if(this.id == item_option) {
                    sel_opt = this.name;
                }
            });
            var row_no = (new Date).getTime();
            var newTr = $('<tr id="row_' + row_no + '" class="row_' + item_id + '" data-item-id="' + item_id + '"></tr>');
            tr_html = '<td><input name="product_id_tra[]" type="hidden" class="rid" value="' + product_id + '"><input name="product_type_tra[]" type="hidden" class="rtype" value="' + item_type + '"><input name="product_code_tra[]" type="hidden" class="rcode" value="' + item_code + '"><input name="product_name_tra[]" type="hidden" class="rname" value="' + item_name + '"><input name="product_option_tra[]" type="hidden" class="roption" value="' + item_option + '"><span class="sname" id="name_tra_' + row_no + '">' + item_code +' - '+ item_name +(sel_opt != '' ? ' ('+sel_opt+')' : '')+'</span> <i class="pull-right fa fa-edit tip pointer edit_tra" id="' + row_no + '" data-item="' + item_id + '" title="Edit" style="cursor:pointer;"></i></td>';
            if (site.settings.product_serial == 1) {
                tr_html += '<td class="text-right"><input class="form-control input-sm rserial" name="serial_tra[]" type="text" id="serial_' + row_no + '" value="'+item_serial+'"></td>';
            }
            tr_html += '<td class="text-right"><input class="form-control input-sm text-right rprice_tra" name="net_price_tra[]" type="hidden" id="price_tra_' + row_no + '" value="' + item_price + '"><input class="ruprice_tra" name="unit_price_tra[]" type="hidden" value="' + unit_price + '"><input class="p_gia_set_giasi" name="p_gia_set_giasi_tra[]" type="hidden" value="' + p_gia_set_giasi + '"><input class="realuprice" name="real_unit_price_tra[]" type="hidden" value="' + item.row.real_unit_price + '"><span class="text-right sprice" id="sprice_tra_' + row_no + '">' + formatMoney(item_price) + '</span></td>';
            tr_html += '<td><input class="form-control text-center rquantity_tra" tabindex="'+((site.settings.set_focus == 1) ? an_tra : (an_tra+1))+'" name="quantity_tra[]" type="text" value="' + parseFloat(item_qty) + '" data-id="' + row_no + '" data-item="' + item_id + '" id="quantity_tra_' + row_no + '" onClick="this.select();"><input name="product_unit_tra[]" type="hidden" class="runit" value="' + product_unit + '"><input name="product_base_quantity_tra[]" type="hidden" class="rbase_quantity" value="' + base_quantity + '"></td>';
            if ((site.settings.product_discount == 1 && allow_discount == 1) || item_discount) {
                tr_html += '<td class="text-right"><input class="form-control input-sm rdiscount" name="product_discount_tra[]" type="hidden" id="discount_tra_' + row_no + '" value="' + item_ds + '"><span class="text-right sdiscount text-danger" id="sdiscount_tra_' + row_no + '">' + formatMoney(0 - (item_discount * item_qty)) + '</span></td>';
            }
            if (site.settings.tax1 == 1) {
                tr_html += '<td class="text-right"><input class="form-control input-sm text-right rproduct_tax" name="product_tax_tra[]" type="hidden" id="product_tax_tra_' + row_no + '" value="' + pr_tax.id + '"><span class="text-right sproduct_tax" id="sproduct_tax_tra_' + row_no + '">' + (parseFloat(pr_tax_rate) != 0 ? '(' + pr_tax_rate + ')' : '') + ' ' + formatMoney(pr_tax_val * item_qty) + '</span></td>';
            }
            tr_html += '<td class="text-right"><span class="text-right ssubtotal" id="subtotal_tra_' + row_no + '">' + formatMoney(((parseFloat(item_price) + parseFloat(pr_tax_val)) * parseFloat(item_qty))-(item_qty*item_discount)) + '</span></td>';
            tr_html += '<td class="text-center"><i class="fa fa-times tip pointer sldel_tra" id_tra="' + row_no + '" title="Remove" style="cursor:pointer;"></i></td>';
            newTr.html(tr_html);
            newTr.prependTo("#slTable_tra");
            total += formatDecimal(((parseFloat(item_price) + parseFloat(pr_tax_val)) * parseFloat(item_qty))-(item_discount*parseFloat(item_qty)), 4);
            count_tra += parseFloat(item_qty);
            an_tra++;

            if (item_type == 'standard' && item.options !== false) {
                $.each(item.options, function () {
                    if(this.id == item_option && base_quantity > this.quantity) {
                        $('#row_' + row_no).addClass('danger');
                        if(site.settings.overselling != 1) { $('#add_sale, #edit_sale').attr('disabled', true); }
                    }
                });
            } else if(item_type == 'standard' && base_quantity > item_aqty) {
                $('#row_' + row_no).addClass('danger');
                if(site.settings.overselling != 1) { $('#add_sale, #edit_sale').attr('disabled', true); }
            } else if (item_type == 'combo') {
                if(combo_items === false) {
                    $('#row_' + row_no).addClass('danger');
                    if(site.settings.overselling != 1) { $('#add_sale, #edit_sale').attr('disabled', true); }
                } else {
                    $.each(combo_items, function() {
                       if(parseFloat(this.quantity) < (parseFloat(this.qty)*base_quantity) && this.type == 'standard') {
                           $('#row_' + row_no).addClass('danger');
                           if(site.settings.overselling != 1) { $('#add_sale, #edit_sale').attr('disabled', true); }
                       }
                   });
                }
            }

        });

        var col = 2;
        if (site.settings.product_serial == 1) { col++; }
        var tfoot = '<tr id="tfoot" class="tfoot active"><th colspan="'+col+'">Total</th><th class="text-center">' + formatNumber(parseFloat(count_tra) - 1) + '</th>';
        if ((site.settings.product_discount == 1 && allow_discount == 1) || product_discount) {
            tfoot += '<th class="text-right">'+formatMoney(product_discount)+'</th>';
        }
        if (site.settings.tax1 == 1) {
            tfoot += '<th class="text-right">'+formatMoney(product_tax)+'</th>';
        }
        tfoot += '<th class="text-right">'+formatMoney(total)+'</th><th class="text-center"><i class="fa fa-trash-o" style="opacity:0.5; filter:alpha(opacity=50);"></i></th></tr>';
        $('#slTable_tra tfoot').html(tfoot);

        // Order level discount calculations
        if (sldiscount = localStorage.getItem('sldiscount_tra')) {
            var ds = sldiscount;
            if (ds.indexOf("%") !== -1) {
                var pds = ds.split("%");
                if (!isNaN(pds[0])) {
                    order_discount = formatDecimal((((total) * parseFloat(pds[0])) / 100), 4);
                } else {
                    order_discount = formatDecimal(ds);
                }
            } else {
                order_discount = formatDecimal(ds);
            }

            //total_discount += parseFloat(order_discount);
        }

        // Order level tax calculations
        if (site.settings.tax2 != 0) {
            if (sltax2 = localStorage.getItem('sltax2')) {
                $.each(tax_rates, function () {
                    if (this.id == sltax2) {
                        if (this.type == 2) {
                            invoice_tax = formatDecimal(this.rate);
                        } else if (this.type == 1) {
                            invoice_tax = formatDecimal((((total - order_discount) * this.rate) / 100), 4);
                        }
                    }
                });
            }
        }

        total_discount = parseFloat(order_discount + product_discount);
        // Totals calculations after item addition
        var gtotal = parseFloat(((total + invoice_tax) - order_discount) + shipping);
        $('#total_tra').text(formatMoney(total));
        $('#titems_tra').text((an_tra - 1) + ' (' + formatNumber(parseFloat(count_tra) - 1) + ')');
        
        $('#tds_tra').text(formatMoney(order_discount));
		
		$('#gtotal_tra').text(formatMoney(gtotal));
      	  
        if (an_tra > parseInt(site.settings.bc_fix) && parseInt(site.settings.bc_fix) > 0) {
            $("html, body").animate({scrollTop: $('#sticker_tra').offset().top}, 500);
            $(window).scrollTop($(window).scrollTop() + 1);
        }
        if (count_tra > 1) {
            $('#slcustomer').select2("readonly", true);
            $('#slwarehouse').select2("readonly", true);
        }
        set_page_focus();
    }
}
