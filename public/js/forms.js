
'use strict';

var spinnerx = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> &nbsp; ';

$('.js-validate-form .validate-input > input').each(function(){
    $(this).focus(function(){
        hideValidate(this);
    });
});

function showSpinner(elem) {
    let spinnerY = '<div class="spinner-grow" role="status"></div>';
    //$(elem).html(spinnerY);
    $(elem).each(function(){
        $(this).html(spinnerY);
    });
}

function clearSpinner(elem) {
    //$(elem).html('');
    $(elem).each(function(){
        $(this).html('');
    });
}

function allRedirect(url) {
    window.location.replace(url);
    window.location.href = url;
    setTimeout(allRedirect, 4000, url);
}

function showPreloader(btn) {
    if (btn == null) return;
    //console.log('show');
    btn.addClass('blocked');
    let ehtml = btn.html();
    let rehtml = ehtml.replace(spinnerx,'');
    btn.html(spinnerx + rehtml);
}

function hidePreloader(btn) {
    if (btn == null) return;
    //console.log('hide');
    btn.removeClass('blocked');
    let ehtml = btn.html();
    let rehtml = ehtml.replace(spinnerx,'');
    btn.html(rehtml);
}

var myCsrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
$.extend($.ajaxSettings, {
    headers: {
        'X-CSRF-TOKEN': myCsrfToken
    }
});

function formSubmit(form, actionUrl, btn, callback) {
    let fData = new FormData(form);
    $.ajax({
        type: 'post',
        url: actionUrl,
        data: fData,
        dataType: 'json',
        contentType: false,
        processData: false,
        beforeSend: function() {
            showPreloader(btn);
        },
        success: function(data) {
            console.log(data);
            hidePreloader(btn);
            if (data.result == 'redirect') {
                allRedirect(data.message);
                return;
            }
            if (data.result == 'success') {
                let title = undefined;
                if (typeof(data.title) !== 'undefined') {
                    title = data.title;
                }
                if (typeof(data.update) !== 'undefined') {
                    form.reset();
                }
                if (typeof(data.message) !== 'undefined') {
                    showToast(getJsStr(data.message), 'success', title);
                }
            }
            if (data.result == 'error') {
                if (data.message) {
                    showToast(getJsStr(data.message), 'error');
                }
            }
            if (data.result == 'info') {
                console.log(data);
            }
            if (typeof(callback) !== 'undefined') {
                callback(data);
            }
        },
        error: function(data) {
            console.log('24');
            console.log(data);
            let errorText = 'SYSTEM FAILURE';
            if (typeof(data.responseJSON) !== 'undefined') {
                if (typeof(data.responseJSON.message) !== 'undefined') {
                    errorText = getJsStr(data.responseJSON.message);
                }
            }
            if (typeof(callback) !== 'undefined') {
                callback(data);
            }
            if (data.status === 504) {
                errorText = getJsStr('weird_timeout');
            }
            showToast(errorText, 'error');
            hidePreloader(btn);
        },
        done: function(data) {
            //console.log('done: ' + data);
        }
    });
}

function checkFields(formv) {
    let inputx = $(formv).find('.validate-input > input');
    let check = true;
    for(var i=0; i<inputx.length; i++) {
        if(validate(inputx[i]) == false){
            showValidate(inputx[i]);
            check=false;
        }
    }
    return check;
}

function validate (inputv) {
    if($(inputv).val().trim() == ''){
        return false;
    }
}

function showValidate(inputv) {
    var thisAlert = $(inputv).parent();
    $(thisAlert).addClass('alert-validate');
}

function hideValidate(inputv) {
    var thisAlert = $(inputv).parent();
    $(thisAlert).removeClass('alert-validate');
}

function showToast(text, attr, title, subtitle = '') {
    if (typeof(title) === 'undefined') {
        title = getJsStr('toast_' + attr);
    }
    $.toast({
        title: title,
        subtitle: subtitle,
        content: text,
        type: attr,
        delay: 5000
    });
}

function getJsStr(str) {
    if (typeof(JS_STRINGS[str]) !== 'undefined') {
        str = JS_STRINGS[str];
    }
    return str;
}

function setComplex(cid, callback) {
    $.ajax({
        type: 'post',
        url: '/client/setcomplex',
        dataType: 'json',
        data: {cid: cid},
        success: function(data) {
            console.log(data);
            if (data.result == 'success') {
                if (typeof(callback) !== 'undefined') {
                    callback();
                }
            }
        },
        error: function(data) {
            console.log('25');
            console.log(data);
            alert('SYSTEM FAILURE');
        }
    });
}
