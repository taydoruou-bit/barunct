$(document).ready(function (e) {
    $('body a, body button').attr('tabindex', -1);
    
});


$('#reset').click(function (e) {
    bootbox.confirm(lang.r_u_sure, function (result) {
        if (result) {
            if (localStorage.getItem('code')) {
                localStorage.removeItem('code');
            }
            if (localStorage.getItem('name')) {
                localStorage.removeItem('name');
            }
            if (localStorage.getItem('diachi')) {
                localStorage.removeItem('diachi');
            }
            if (localStorage.getItem('dienthoai')) {
                localStorage.removeItem('dienthoai');
            }
            if (localStorage.getItem('email')) {
                localStorage.removeItem('email');
            }
            if (localStorage.getItem('renote')) {
                localStorage.removeItem('renote');
            }
            if (localStorage.getItem('nodauky')) {
                localStorage.removeItem('nodauky');
            }
            if (localStorage.getItem('redate')) {
                localStorage.removeItem('redate');
            }

            $('#modal-loading').show();
            location.reload();
        }
    });
});


if (typeof (Storage) === "undefined") {
    $(window).bind('beforeunload', function (e) {
        if (count > 1) {
            var message = "You will loss data!";
            return message;
        }
    });
}
