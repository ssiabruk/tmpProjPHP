
'use strict';

$(document).ready(function() {
    if (typeof(cids) !== 'undefined') {
        showSpinner('.video-area');
        cids.forEach(function(item, index) {
            stream(item);
        });
    }
});

function stream(cid) {
    $('#selected-complex').val(cid);
    let form = document.getElementById('stream-form');
    formSubmit(form, actionCheck, null, startStream);
}

/*$('#complex-list').on('change', function(e){
    $('#mjpeg').attr('src', '');
    let complex_id = $(this).children('option:selected').data('id');
    $('#selected-complex').val(complex_id);
    let form = document.getElementById('stream-form');
    showSpinner('.video-area');
    formSubmit(form, actionCheck, null, startStream);
});*/

function startStream(data) {
    //let complex_ip = $('#complex-list option:selected').data('ip');
    if (data.result == 'info') {
        $('#va-' + data.cid).html(getJsStr(data.message));
        return true;
    }
    if (data.result == 'success') {
        let complex_ip = data.cip;
        //let stream_url = 'http://' + complex_ip + ':8181/stream.mjpg?live=1&size=30&det=10';
        let stream_url = 'http://' + complex_ip + data.videourl;
        Console.log(stream_url);
        $('#mjpeg-' + data.cid).attr('src', stream_url);
        let timerId = setTimeout(function() {
            clearSpinner('#va-' + data.cid);
        }, 2000);
        return true;
    }
    //clearSpinner('#video-area');
    $('#va-' + data.cid).html(getJsStr('error_grabber_active'));
}
