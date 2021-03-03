
'use strict';

$(document).ready(function() {
    loadSessions();
});

$('#storage-types').on('change', function(e){
    let storage_type = $(this).val();
    $.ajax({
        type: 'post',
        url: actionChangeStorage,
        dataType: 'json',
        data: {stype: storage_type},
        success: function(data) {
            console.log(data);
            if (data.result == 'success') {
                loadSessions();
            }
        },
        error: function(data) {
            console.log('11');
            console.log(data);
            alert('SYSTEM FAILURE');
        }
    });
});

function loadSessions() {
    let has_cmplx = $('.sess-area').length;
    if (!has_cmplx) return;
    $('.act-local').addClass('d-none');
    $('.act-remote').addClass('d-none');
    $('.act-complex').addClass('d-none');
    $.ajax({
        type: 'post',
        url: actionLoadSessions,
        dataType: 'json',
        contentType: false,
        processData: false,
        beforeSend: function() {
            showSpinner('.sess-area');
        },
        success: function(data) {
            //console.log(data);
            if (data.result == 'success') {
                $('.act-' + data.stype).removeClass('d-none');
                if (data.sess) {
                    let tmp = Object.entries(data.sess);
                    tmp.forEach(function(item, index) {
                        $('#' + item[0]).html(item[1]);
                    });
                }
            }
        },
        error: function(data) {
            console.log('12');
            console.log(data);
            alert('SYSTEM FAILURE');
        }
    });
}

/*$(document).on('click', 'button.btn-del', function(e) {
    e.preventDefault();
    if (!confirm(getJsStr('confirm_delete'))) {
        return false;
    }
    let stype = $(this).data('stype');
    let sname = $(this).data('sname');
    let cid = $(this).data('cid');
    $.ajax({
        type: 'post',
        url: actionDeleteSessions,
        dataType: 'json',
        data: {stype: stype, sname: sname},
        success: function(data) {
            //console.log(data);
            if (data.result == 'success') {
                $('#' + sname).remove();
                let tmp = $.trim($('#t-' + cid).html());
                if (!tmp) {
                    $('#t-' + cid).html(sess_none)
                }
            } else {
                alert('Delete error');
            }
        },
        error: function(data) {
            console.log(data);
            alert('SYSTEM FAILURE');
        }
    });
});*/

$('a[id^="check"').on('click', function(e) {
    e.preventDefault();
    let cid = $(this).data('id');
    $('#s' + cid + ' .sess-chk').each(function() {
        $(this).prop('checked', true);
    });
});

$('a[id^="clear"').on('click', function(e) {
    e.preventDefault();
    let cid = $(this).data('id');
    $('#s' + cid + ' .sess-chk').each(function() {
        $(this).prop('checked', false);
    });
});

$('.act-local').on('click', function(e) {
    e.preventDefault();
    let cid = $(this).data('id');
    //let check_sess = $('#cl' + cid + ' input.sess-chk:checked').map(function() {return this.data('sname')}).get().join(',');
    //let check_sess = $('#t-' + cid + ' .sess-chk:checkbox:checked');
    let check_sess = [];
    $('#t-' + cid + ' input.sess-chk:checked').each(function () {
        if (this.checked) {
            check_sess.push($(this).data('sname'));
        }
    });
    console.log(check_sess);
    $.ajax({
        type: 'post',
        url: actionDeleteSessions,
        dataType: 'json',
        data: {sessions: check_sess, cid: cid, stype: 'local'},
        success: function(data) {
            console.log(data);
            if (data.result == 'success') {
                check_sess.forEach(function(item, index) {
                    $('#' + item).remove();
                });
                let tmp = $.trim($('#t-' + cid).html());
                if (!tmp) {
                    $('#t-' + cid).html(sess_none)
                }
            } else {
                showToast(getJsStr(data.message), 'error');
            }
        },
        error: function(data) {
            console.log('13');
            console.log(data);
            alert('SYSTEM FAILURE');
        }
    });
});

$('#act-remote').on('click', function(e) {
    e.preventDefault();
    let cid = $(this).data('id');
    $('#s' + cid + ' .sess-chk').each(function() {
        $(this).prop('checked', false);
    });
});

$('#act-complex').on('click', function(e) {
    e.preventDefault();
    let cid = $(this).data('id');
    $('#s' + cid + ' .sess-chk').each(function() {
        $(this).prop('checked', false);
    });
});
