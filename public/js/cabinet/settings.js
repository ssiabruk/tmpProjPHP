
'use strict';

$('#btn-submit-add').on('click',function(e){
    e.preventDefault();
    let form = this.closest('form');
    let resChk = checkFields(form);
    if (!resChk) {
        return false;
    }
    formSubmit(form, actionAdd, $(this));
});

$('.btn-test').each(function(){
    $(this).on('click', function(e) {
        e.preventDefault();
        $('#test-close').addClass('blocked');
        $('#test-close').addClass('disabled');
        $('#test-result').addClass('d-none');
        $('#test-wait').removeClass('d-none');
        $('#test').modal({backdrop: 'static', keyboard: false}, 'show');
        let id = $(this).data('id');
        $.ajax({
            type: 'post',
            url: actionTest,
            data: {id: id},
            dataType: 'json',
            success: function(data) {
                console.log(data);
                let text_type = '';
                if (data.result == 'success') {
                    text_type = 'text-success';
                } else {
                    text_type = 'text-danger';
                }
                $('#test-wait').addClass('d-none');
                $('#test-result').html('<span class="' + text_type + '">' + getJsStr(data.message) + '</span>');
                $('#test-result').removeClass('d-none');
                $('#test-close').removeClass('blocked');
                $('#test-close').removeClass('disabled');
            },
            error: function(data) {
                console.log('08');
                console.log(data);
                showToast('SYSTEM FAILURE', 'error');
                $('#test-close').removeClass('blocked');
                $('#test-close').removeClass('disabled');
            }
        });
    });
});

$('.btn-edit').each(function(){
    $(this).on('click', function(e) {
        e.preventDefault();
        let id = $(this).data('id');
        let cl = '#cl' + id;
        let cid = $(cl).find('.cid').html();
        let cip = $(cl).find('.cip').html();
        let cpt = $(cl).find('.cpt').html();
        let ckey = $(cl).find('.ckey').val();
        let color = $(cl).find('.color').val();
        let status = $(cl).find('.status').val();
        let cres = $(cl).find('.cres').html();
        $('#chcomplex #cid').val(cid);
        $('#chcomplex #cip').val(cip);
        $('#chcomplex #cpt').val(cpt);
        $('#chcomplex #ckey').val(ckey);
        $('#chcomplex #id').val(id);
        $('#chcomplex #colour').val(color);
        if (status == 'on') {
            $('#chcomplex #cstatus').prop('checked', true);
        } else {
            $('#chcomplex #cstatus').prop('checked', false);
        }
        $('#cres' + cres).prop('checked', true);
        $('#chcomplex').modal('show');
    });
});

$('#cledit').on('click',function(e){
    e.preventDefault();
    let form = this.closest('form');
    let resChk = checkFields(form);
    if (!resChk) {
        return false;
    }
    formSubmit(form, actionEdit, $(this), updateEditForm);
});

function updateEditForm(data) {
    if (data.result == 'error') {
        document.getElementById('edit-form').reset();
        $('#chcomplex').modal('hide');
        return false;
    }
    let cid = $('#chcomplex #cid').val();
    let cip = $('#chcomplex #cip').val();
    let cpt = $('#chcomplex #cpt').val();
    let cres = $('#chcomplex input[name=camres]:checked').val();
    let ckey = $('#chcomplex #ckey').val();
    let id = $('#chcomplex #id').val();
    let color = $('#chcomplex #colour').val();
    let status = $('#chcomplex #cstatus').is(':checked');
    let cl = '#cl' + id;
    $(cl).find('.cid').html(cid);
    $(cl).find('.cip').html(cip);
    $(cl).find('.cpt').html(cpt);
    $(cl).find('.cres').html(cres);
    $(cl).find('.ckey').val(ckey);
    let old_color = $(cl).find('.color').val();
    $(cl).find('.color').val(color);
    $(cl).find('.badge').removeClass('bgc-' + old_color);
    $(cl).find('.badge').addClass('bgc-' + color);
    if (status) {
        $(cl).find('.onoff').html('on');
        $(cl).find('.status').val('on');
    } else {
        $(cl).find('.onoff').html('off');
        $(cl).find('.status').val('off');
    }
    document.getElementById('edit-form').reset();
    $('#chcomplex').modal('hide');
}

$('.btn-delete').each(function(){
    $(this).on('click', function(e) {
        e.preventDefault();
        if (!confirm(getJsStr('confirm_delete'))) {
            return false;
        }

        let id = $(this).data('id');
        $.ajax({
            type: 'post',
            url: actionDel,
            data: {id: id},
            dataType: 'json',
            success: function(data) {
                console.log(data);
                if (data.result == 'redirect') {
                   allRedirect(data.message); return;
                }
                if (data.result == 'error') {
                    showToast(getJsStr(data.message), 'error');
            }
            },
            error: function(data) {
                console.log('08');
                console.log(data);
                showToast('SYSTEM FAILURE', 'error');
            }
        });
    });
});

/*$('.btn-save').each(function(){
    $(this).on('click', function(e) {
        e.preventDefault();
        let form = this.closest('form');
        formSubmit(form, actionModesSave, $(this));
    });
});*/

$('.btn-sync').each(function(){
    $(this).on('click', function(e) {
        e.preventDefault();
        let form = this.closest('form');
        formSubmit(form, actionModesSync, $(this), updateModes);
    });
});

function updateModes(data) {
    /*$('#form' + data.cid + 'input:checkbox').removeAttr('checked');
    if (Array.isArray(data.modes)) {
        data.modes.forEach(function(item, index) {
            let ch_box = '#' + item + '-' + data.cid;
            $(ch_box).prop('checked', true );
        });
    }*/
    let modes_box = '#modes-' + data.cid;
    $(modes_box).html(data.modes);
    console.log(modes_box);
}

$('.btn-reboot').each(function(){
    $(this).on('click',function(e){
        e.preventDefault();
        let cid = $(this).data('id');
        $('#selected-complex-' + cid).val(cid);
        $('#complex-command-' + cid).val('reboot');
        let form = document.getElementById('action-form-' + cid);
        blockControlButtons();
        formSubmit(form, actionControl, $(this), releaseControlButtons);
    });
});


function blockControlButtons() {
    $('.complex-control').each(function(){
        $(this).addClass('btn-blocked');
    });
}

function releaseControlButtons() {
    $('.complex-control').each(function(){
        $(this).removeClass('btn-blocked');
    });
}

$('input[type=radio][name=ctype]').change(function(){
    let tmp = this.value;
    $('#contact-input').attr('placeholder', getJsStr('enter_' + tmp));
});

$('#contact-add').on('click', function(e){
    e.preventDefault();
    let ctype = $('input[name=ctype]:checked', '#form-add-contact').val();
    let cdata = $('#contact-input').val();
    if (!ctype || !cdata) {
        showToast(getJsStr('fields_required_empty'), 'error');
        return false;
    }
    $.ajax({
        type: 'post',
        url: actionAddContact,
        data: {ctype: ctype, cdata: cdata},
        dataType: 'json',
        success: function(data) {
            console.log(data);
            if (data.result == 'success') {
                addContactBlock(data.message, cdata)
            }
            if (data.result == 'error') {
                showToast(getJsStr(data.message), 'error');
        }
        },
        error: function(data) {
            console.log('27');
            console.log(data);
            showToast('SYSTEM FAILURE', 'error');
        }
    });
});

function addContactBlock(id, cdata) {
    $('#no-contacts').addClass('d-none');
    //let h_item = $('#clone-contact').clone();
    //h_item.prependTo('#contacts-list');
    $('#clone-contact').clone().prependTo('#contacts-list');
    //$('#contacts-list').find('div:first').removeAttr('id');
    let new_elem = $('#contacts-list').find('div:first');
    new_elem.removeAttr('id');
    new_elem.find('.contact-data').html(cdata);
    new_elem.attr('data-id', id);
    new_elem.find('.remove-contact').attr('data-id', id);
    new_elem.removeClass('d-none');
}

$('#contacts-list').on('click', '.remove-contact', function(e) {
    e.preventDefault();
    if (!confirm(getJsStr('confirm_delete'))) {
        return false;
    }
    let id = $(this).data('id');
    if (!id) {
        showToast(getJsStr('fields_required_empty'), 'error');
        return false;
    }
    $.ajax({
        type: 'post',
        url: actionDelContact,
        data: {id: id},
        dataType: 'json',
        success: function(data) {
            console.log(data);
            if (data.result == 'success') {
                delContactBlock(data.message);
            }
            if (data.result == 'error') {
                showToast(getJsStr(data.message), 'error');
        }
        },
        error: function(data) {
            console.log('28');
            console.log(data);
            showToast('SYSTEM FAILURE', 'error');
        }
    });
});

function delContactBlock(id) {
    console.log(id);
    $('div[data-id^="' + id + '"]').remove();
    let contacts_count = $('#contacts-list').children().length;
    //console.log(contacts_count);
    if (contacts_count < 1) {
        $('#no-contacts').removeClass('d-none');
    }
}

$('.btn-user-save').each(function(){
    $(this).on('click', function(e) {
        e.preventDefault();
        let user_id = $(this).data('id');
        let user_role = $('#role-' + user_id).val();
        $.ajax({
            type: 'post',
            url: actionSetRole,
            data: {id: user_id, role: user_role},
            dataType: 'json',
            success: function(data) {
                console.log(data);
                if (data.result == 'success') {
                    showToast(getJsStr(data.message), 'success');
                } else {
                    showToast(getJsStr(data.message), 'error');
                }
            },
            error: function(data) {
                console.log('29');
                console.log(data);
                showToast('SYSTEM FAILURE', 'error');
            }
        });
    });
});

$('.btn-user-del').each(function(){
    $(this).on('click', function(e) {
        e.preventDefault();
        if (!confirm(getJsStr('confirm_delete'))) {
            return false;
        }
        let user_id = $(this).data('id');
        $.ajax({
            type: 'post',
            url: actionDelUser,
            data: {id: user_id},
            dataType: 'json',
            success: function(data) {
                console.log(data);
                if (data.result == 'success') {
                    $('tr[data-id^="' + user_id + '"]').remove();
                }
                if (data.result == 'error') {
                    showToast(getJsStr(data.message), 'error');
                }
            },
            error: function(data) {
                console.log('30');
                console.log(data);
                showToast('SYSTEM FAILURE', 'error');
            }
        });
    });
});

$('#logger').on('change', function(){
    let status = $('#logger').is(':checked') | 0;
    $.ajax({
        type: 'post',
        url: actionLogger,
        data: {status: status},
        dataType: 'json',
        success: function(data) {
            //console.log(data);
            if (data.result == 'success') {
                showToast(getJsStr(data.message), 'success');
            }
            if (data.result == 'error') {
                showToast(getJsStr(data.message), 'error');
            }
        },
        error: function(data) {
            console.log('31');
            console.log(data);
            showToast('SYSTEM FAILURE', 'error');
        }
    });
});
