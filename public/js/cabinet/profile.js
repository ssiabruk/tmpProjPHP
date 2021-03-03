
'use strict';

$('#btn-submit-passwd').on('click',function(e){
    e.preventDefault();
    let form = this.closest('form');
    let resChk = checkFields(form);
    if (!resChk) {
        return false;
    }
    formSubmit(form, actionPasswd, $(this));
});

$('#btn-submit-lang').on('click',function(e){
    e.preventDefault();
    let form = this.closest('form');
    formSubmit(form, actionLang, $(this));
});

$('#btn-submit-userdata').on('click',function(e){
    e.preventDefault();
    let form = this.closest('form');
    formSubmit(form, actionSave, $(this));
});

$('#btn-submit-usersync').on('click',function(e){
    e.preventDefault();
    let btn = $(this);
    $.ajax({
        type: 'get',
        url: actionUsersync,
        beforeSend: function() {
            showPreloader(btn);
        },
        success: function(data) {
            hidePreloader(btn);
            if (data.result == 'success') {
                $('#sync-status > #sync-ok').removeClass('d-none');
                $('#sync-status > #sync-bad').addClass('d-none');
                $('#sync-status > button').remove();
                showToast(getJsStr(data.message), 'success');
            }
            if (data.result == 'error') {
                showToast(getJsStr(data.message), 'error');
            }
        },
        error: function(data) {
            hidePreloader(btn);
            console.log('14');
            console.log(data);
            showToast('SYSTEM FAILURE', 'error');
        }
    });
});
