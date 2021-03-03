
'use strict';

$('#btn-submit-login').on('click',function(e){
    e.preventDefault();
    let form = this.closest('form');
    let resChk = checkFields(form);
    if (!resChk) {
        return false;
    }
    formSubmit(form, actionLogin, $(this));
});
