
'use strict';

var myCsrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
$.extend($.ajaxSettings, {
    headers: {
        'X-CSRF-TOKEN': myCsrfToken
    }
});

$(document).ready(function() {
    if (hasImage) {
        setGuil();
        return true;
    }
    let dtidval = $('#dtid').val();
    $.ajax({
        type: 'post',
        url: actionGetImage,
        data: {dtid: dtidval, stype: stype},
        dataType: 'json',
        success: function(data) {
            //console.log(data);
            if (data.result == 'success') {
                //$('#dt-img').addClass('d-none');
                $('#dt-img').attr('src', data.url);
                setGuil();
                //$('#dt-img').removeClass('d-none');
            }
            if (data.result == 'error') {
                showToast(getJsStr(data.message), 'error');
                //alert(getJsStr(data.message));
            }
            $('#data-loading').addClass('d-none');
        },
        error: function(data) {
            console.log('15');
            console.log(data);
            alert('SYSTEM FAILURE');
        }
    });
});

var picDet;

function setGuil() {
    $('#dt-img').addClass('d-none');
    let w = $('.panorama').width();
    let h = $('.panorama').height();
    console.log(w, h);
    picDet = $('#dt-img');
    //picDet.on('load', function() {
    setTimeout(function() {
        $('#dt-img').removeClass('d-none');
        picDet.guillotine({width: w, height: h});
        picDet.guillotine('zoomIn');
    }, 500);
    $('#controls').removeClass('disabled');
    //});
}

$('#controls button').click(function(e) {
    e.preventDefault();
    let action = this.id;
    picDet.guillotine(action);
});
