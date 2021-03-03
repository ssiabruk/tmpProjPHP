
'use strict';

$('#btn-submit-restore').on('click',function(e){
    e.preventDefault();
    let form = this.closest('form');
    let resChk = checkFields(form);
    if (!resChk) {
        return false;
    }
    formSubmit(form, actionRes, $(this));
});

$('#btn-submit-register').on('click',function(e){
    e.preventDefault();
    let form = this.closest('form');
    let resChk = checkFields(form);
    if (!resChk) {
        return false;
    }
    formSubmit(form, actionReg, $(this));
});
