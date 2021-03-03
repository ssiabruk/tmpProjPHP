
'use strict';

$('#btn-start').on('click', function(e) {
    e.preventDefault();
    let btn = $(this);
    servAction(actionStart, btn)
});

$('#btn-stop').on('click', function(e) {
    e.preventDefault();
    let btn = $(this);
    servAction(actionStop, btn)
});

function servAction(action, btn) {
    $.ajax({
        type: 'post',
        url: action,
        dataType: 'json',
        contentType: false,
        processData: false,
        beforeSend: function() {
            showPreloader(btn);
        },
        success: function(data) {
            console.log(data);
            hidePreloader(btn);
            if (data.result == 'success') {
                showToast(getJsStr(data.message), 'success');
            } else {
                showToast(getJsStr(data.message), 'error');
            }
        },
        error: function(data) {
            console.log('16');
            console.log(data);
            let message = 'SYSTEM FAILURE';
            if (typeof(data.responseText)!== 'undefined') {
                try {
                    const obj = JSON.parse(data.responseText);
                    message = obj.full;
                } catch (e) {}
            }
            hidePreloader(btn);
            showToast(message, 'error');
        }
    });
}

$('#btn-diag-start').on('click', function(e) {
    e.preventDefault();
    $(this).addClass('blocked').addClass('disabled');
    $('#diag-result').html('');
    step1();
});

function addDiagText(text, suff) {
    let tmp_html = $('#diag-result').html();
    tmp_html = tmp_html + text + suff;
    $('#diag-result').html(tmp_html);
}

function step1() {
    let step_label = getJsStr('diag_step1');
    //$('#diag-result').add(step_label + '... ');
    addDiagText(step_label, '... ');
    $.ajax({
        type: 'post',
        url: actionDiag + '/step1',
        dataType: 'json',
        success: function(data) {
            let res_label = getJsStr(data.message);
            if (data.result == 'success') {
                let res_text = '<span class="text-success">' + res_label + '</span>';
                addDiagText(res_text, '<br />');
            } else {
                let res_text = '<span class="text-danger">' + res_label + '</span>';
                addDiagText(res_text, '<br />');
            }
            step2();
        },
        error: function(data) {
            console.log('17');
            console.log(data.responseText);
            showToast('SYSTEM FAILURE', 'error');
            $('#btn-diag-start').removeClass('blocked').removeClass('disabled');
        }
    });
}

function step2() {
    let step_label = getJsStr('diag_step2');
    //$('#diag-result').add(step_label + '... ');
    addDiagText(step_label, '... ');
    $.ajax({
        type: 'post',
        url: actionDiag + '/step2',
        dataType: 'json',
        success: function(data) {
            console.log(data);
            let res_label = getJsStr(data.message);
            if (data.result == 'success') {
                let res_text = '<span class="text-success">' + res_label + '</span>';
                addDiagText(res_text, '<br />');
            } else {
                let res_text = '<span class="text-danger">' + res_label + '</span>';
                addDiagText(res_text, '<br />');
            }
            step3();
        },
        error: function(data) {
            console.log('18');
            console.log(data.responseText);
            showToast('SYSTEM FAILURE', 'error');
            $('#btn-diag-start').removeClass('blocked').removeClass('disabled');
        }
    });
}

function step3() {
    let step_label = getJsStr('diag_step3');
    //$('#diag-result').add(step_label + '... ');
    addDiagText(step_label, '... ');
    $.ajax({
        type: 'post',
        url: actionDiag + '/step3',
        dataType: 'json',
        success: function(data) {
            console.log(data);
            let res_label = getJsStr(data.message);
            if (data.result == 'success') {
                let res_text = '<span class="text-success">' + res_label + '</span>';
                addDiagText(res_text, '<br />');
            } else {
                let res_text = '<span class="text-danger">' + res_label + '</span>';
                addDiagText(res_text, '<br />');
            }
            step4();
        },
        error: function(data) {
            console.log('19');
            console.log(data.responseText);
            showToast('SYSTEM FAILURE', 'error');
            $('#btn-diag-start').removeClass('blocked').removeClass('disabled');
        }
    });
}

function step4() {
    let step_label = getJsStr('diag_step4');
    //$('#diag-result').add(step_label + '<br />');
    addDiagText(step_label, '...<br />');
    $.ajax({
        type: 'post',
        url: actionDiag + '/step4',
        dataType: 'json',
        success: function(data) {
            console.log(data);
            if (data.result == 'success') {
                addDiagText(data.resdata, '');
            } else {
                let res_label = getJsStr(data.message);
                let res_text = '<span class="text-danger">' + res_label + '</span>';
                addDiagText(res_text, '');
            }
            $('#btn-diag-start').removeClass('blocked').removeClass('disabled');
        },
        error: function(data) {
            console.log('20');
            console.log(data.responseText);
            showToast('SYSTEM FAILURE', 'error');
            $('#btn-diag-start').removeClass('blocked').removeClass('disabled');
        }
    });
}
