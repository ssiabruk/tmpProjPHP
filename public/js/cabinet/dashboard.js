
'use strict';

var timerId;
var timerId2;
var timerId3;
var complexArray = [];
var mapsArray = [];

$(document).ready(function() {

    cids.forEach(function(item, index) {
        mapsView(item);
    });

    $.ajax({
        type: 'post',
        url: actionClient,
        data: {command: 'init'},
        dataType: 'json',
        success: function(data) {
            //console.log(data);
            if (data.result == 'success') {
                let tmp = JSON.parse(data.message);
                if (Array.isArray(tmp)) {
                    tmp.forEach(function(item, index) {
                        if (item.lat && item.lon) {
                            let cid = item.id;
                            let dmaps = mapsArray.find(item => item.cid == cid);
                            let iconx = item.cc + 'Marker';
                            item.marker = setMarker(iconx, item.cid, item.lat, item.lon, dmaps.dmap);
                            item.currentZoom = 0;
                        }
                        complexArray.push(item);
                    });
                } else {
                    showToast(getJsStr('error_complex_data'), 'error');
                }

                visualizeComplexes();
                visualizeInfo();
                visualizeTelemetry();

                //console.log(complexArray);
                timerId = setTimeout(function tick() {
                    visualizeComplexes();
                    timerId = setTimeout(tick, 2000);
                }, 2000);

                timerId2 = setTimeout(function alerts() {
                    visualizeAlerts();
                    timerId2 = setTimeout(alerts, 3000);
                }, 3000);

                timerId3 = setTimeout(function telemetry() {
                    visualizeInfo();
                    visualizeTelemetry();
                    timerId3 = setTimeout(telemetry, 7000);
                }, 7000);
            }
            if (data.result == 'error') {
                showToast(getJsStr(data.message), 'error');
            }
        },
        error: function(data) {
            console.log('01', data.responseText);
            //console.log(data);
            /*if (typeof(data) !== 'undefined') {
                if (data.responseJSON != 'undefined') {
                    showToast('SYSTEM FAILURE', 'error');
                }
            }*/
        }
    });
});

function mapsView(complex_id) {
    let tmp_map = new L.Map('map-area-' + complex_id, {
        center: new L.LatLng(50.450096, 30.524189),
        zoom: 11,
    });
    let LocalLayer = L.tileLayer(mapUrl + '/{z}/{x}/{y}.png', {maxZoom:16, minZoom:7});
    tmp_map.addLayer(LocalLayer);

    let southWest = L.latLng(43.379840, 22.023650),
    northEast = L.latLng(52.460855, 40.605299);
    let bounds = L.latLngBounds(southWest, northEast);
    tmp_map.setMaxBounds(bounds);
    tmp_map.on('drag', function() {
        tmp_map.panInsideBounds(bounds, {animate:false});
    });
    let tmp_data = {
        cid: complex_id,
        dmap: tmp_map
    }
    mapsArray.push(tmp_data);
}

function visualizeComplexes() {
    if (fixFollFlag) {
        return true;
    }
    $.ajax({
        type: 'post',
        url: actionClient,
        data: {command: 'visualdata'},
        dataType: 'json',
        success: function(data) {
            if (data.result == 'success') {
                let tmp = JSON.parse(data.message);
                complexArray.forEach(function(item, index) {
                    if (tmp[item.id].lat && tmp[item.id].lon) {
                        changeMarker(item.marker, tmp[item.id].lat, tmp[item.id].lon);
                        let cid = item.id;
                        let dmaps = mapsArray.find(item => item.cid == cid);
                        let cZoom = 0;
                        if (!item.currentZoom) {
                            cZoom = 12;
                        } else {
                            cZoom = dmaps.dmap.getZoom();
                        }
                        complexArray[index].currentZoom = cZoom;
                        dmaps.dmap.setView(new L.LatLng(tmp[item.id].lat, tmp[item.id].lon), cZoom);
                    }
                });
            }
            if (data.result == 'error') {
                showToast(getJsStr(data.message), 'error');
            }
        },
        error: function(data) {
            console.log('02', data.responseText);
            //console.log(data.statusText);
        }
    });
}

function visualizeAlerts() {
    if (fixFollFlag) {
        return true;
    }
    $.ajax({
        type: 'post',
        url: actionAlarm,
        dataType: 'json',
        success: function(data) {
            //console.log(data);
            if (data.result == 'alarm') {
                stopAlert();
                data.list.forEach(function(item, index) {
                    startAlert(item);
                });
            } else {
                stopAlert();
            }
        },
        error: function(data) {
            console.log('03', data.responseText);
        }
    });
}

function visualizeInfo() {
    if (fixFollFlag) {
        return true;
    }
    $.ajax({
        type: 'post',
        url: actionInfo,
        dataType: 'json',
        success: function(data) {
            console.log(data);
            if (data.result == 'success') {
                let tmp = data.message;
                complexArray.forEach(function(item, index) {
                    //console.log(item.id);
                    $('#mode-' + item.id).html(tmp[item.id]);
                });
            }
            if (data.result == 'error') {
                showToast(getJsStr(data.message), 'error');
            }
        },
        error: function(data) {
            console.log('04', data.responseText);
            console.log(data);
        }
    });
}

function visualizeTelemetry() {
    if (fixFollFlag) {
        return true;
    }
    $.ajax({
        type: 'post',
        url: actionTele,
        dataType: 'json',
        success: function(data) {
            //console.log(data);
            if (data.result == 'success') {
                let tmp = data.message;
                complexArray.forEach(function(item, index) {
                    $('#telemetry-' + item.id).html(tmp[item.id]);
                });
            }
            if (data.result == 'error') {
                showToast(getJsStr(data.message), 'error');
            }
        },
        error: function(data) {
            console.log('05', data.responseText);
            console.log(data);
        }
    });
}

/*$('#complex-list').on('change', function(e){
    let complex_id = $(this).children('option:selected').data('id');
    showSpinner('#tele-data-area');
    $('#complex-list').addClass('blocked');
    complexAction(complex_id, 'device', fillComplexInfo);
});

$('#tele-update').on('click', function(e){
    e.preventDefault();
    let complex_id = $('#complex-list').children('option:selected').data('id');
    if (complex_id == null) {
        showToast(getJsStr('fields_required_empty'), 'error');
        return false;
    }
    showSpinner('#tele-data-area');
    $('#complex-list').addClass('blocked');
    complexAction(complex_id, 'device', fillComplexInfo);
});

function fillComplexInfo(data) {
    console.log(data);
    clearSpinner('#tele-data-area');
    $('#complex-list').removeClass('blocked');
    $('#tele-data-area').html(data.message);
    $('#btn-tele-update').removeClass('d-none');
    if (typeof(data.c) !== 'undefined') {
        map.setView(new L.LatLng(data.c.lat, data.c.lon), 16);
    }
}*/

/*function complexAction(cid, act, callback) {
    $.ajax({
        type: 'post',
        url: actionTele,
        data: {cid: cid, command: act},
        dataType: 'json',
        success: function(data) {
            //console.log(data);
            if (data.result == 'success') {
                if (typeof(callback) !== 'undefined') {
                    callback(data.message);
                }
            }
            if (data.result == 'error') {
                data.message = getJsStr(data.message);
                callback(data);
            }
        },
        error: function(data) {
            console.log(data);
            showToast('SYSTEM FAILURE', 'error');
            if (typeof(callback) !== 'undefined') {
                data.message = getJsStr('error_complex_data');
                callback(data);
            }
        }
    });
}*/

function startAlert(cid) {
    $('#alerts-' + cid).addClass('danger-alert');
}

function stopAlert() {
    $('.alerts-btn').removeClass('danger-alert');
}


$('.alerts-btn').each(function(){
    $(this).on('click',function(e){
        e.preventDefault();
        showSpinner('#alert-result');
        $('#alerts-view').modal('show');
        let cid = $(this).data('id');
        $.ajax({
            type: 'post',
            url: actionGetAlarms,
            data: {cid: cid},
            dataType: 'json',
            success: function(data) {
                console.log(data);
                if (data.result == 'success') {
                    $('#alert-result').html(data.alarms);
                } else {
                    $('#alert-result').html(data.result);
                }
            },
            error: function(data) {
                console.log('06');
                console.log(data);
                showToast('SYSTEM FAILURE', 'error');
            }
        });
    });
});

$('.start-btn').each(function(){
    $(this).on('click',function(e){
        e.preventDefault();
        let cid = $(this).data('id');
        let btn = $(this);
        /*let cam_res = getCamResolution(cid);
        if (!cam_res) {
            return false;
        }
        let recdet = $('#recdet-' + cid).is(':checked');*/
        let cam_mode = $('#complex-cam-modes-' + cid);
        if (cam_mode.val() == null) {
            showToast(getJsStr('fields_required_empty'), 'error');
            return false;
        }
        let mode = cam_mode.find(':selected').data('smode');
        let param = cam_mode.find(':selected').data('sparam');
        $('#complex-cam-resolution-' + cid).val(param);
        if (mode == 'recdet') {
            blockControlButtons();
            $('#complex-start-mode-' + cid).val('recdet');
            $.ajax({
                type: 'post',
                url: actionGetGPS,
                data: {cid: cid},
                dataType: 'json',
                success: function(data) {
                    console.log(data);
                    if (data.result == 'success') {
                        confirmDialog(getJsStr('confirm_start_nogps'), cid, btn);
                    } else {
                        sendStartCommand(cid, btn);
                    }
                },
                error: function(data) {
                    releaseControlButtons();
                    console.log('07');
                    console.log(data);
                    showToast('SYSTEM FAILURE', 'error');
                }
            });
        } else {
            $('#complex-start-mode-' + cid).val('record');
            sendStartCommand(cid, btn);
        }
    });
});

/*$('#start-record').on('click',function(e){
    e.preventDefault();
    $('#complex-start-mode').val('record');
    sendStartCommand($(this))
});*/

/*$('#start-detect').on('click',function(e){
    e.preventDefault();
    $('#complex-start-mode').val('detect');
    sendStartCommand($(this))
});*/

/*$('#start-recdet').on('click',function(e){
    e.preventDefault();
    $('#complex-start-mode').val('recdet');
    $.ajax({
        type: 'post',
        url: actionGetGPS,
        dataType: 'json',
        success: function(data) {
            console.log(data);
            if (data.result == 'success') {
                / *let no_gps_agree = confirm('Start without GPS?');
                if (no_gps_agree) {
                    sendStartCommand($(this));
                }* /
                confirmDialog(getJsStr('confirm_start_nogps'));
            }
        },
        error: function(data) {
            console.log(data);
            showToast('SYSTEM FAILURE', 'error');
        }
    });
});*/

function confirmDialog(message, cid, btn){
    let dialogHtml = '<div class="modal" id="confirmStartModal" role="dialog"><div class="modal-dialog"><div class="modal-content"><div class="modal-body" style="padding:20px;"><h4 class="text-center">' + message + '</h4><div class="text-center"><a class="btn btn-success btn-yes btn-sm text-white"><i class="material-icons small mr-3 ml-3">check</i></a><a class="btn btn-danger btn-no btn-sm text-white ml-5"><i class="material-icons small mr-3 ml-3">close</i></a></div></div></div></div></div>';
    $(dialogHtml).appendTo('body');
 
    $('#confirmStartModal').modal({
        backdrop: 'static',
        keyboard: false
    });
  
    $('.btn-yes').click(function () {
        $('#confirmStartModal').modal('hide');
        sendStartCommand(cid, btn);
    });
    
    $('.btn-no').click(function () {
        $('#confirmStartModal').modal('hide');
        releaseControlButtons();
    });

    $('#confirmModal').on('hidden.bs.modal', function () {
        $('#confirmStartModal').remove();
    });
}

/*$('#reboot-complex').on('click',function(e){
    e.preventDefault();
    sendControlComand('reboot', $(this));
});

$('#stop-complex').on('click',function(e){
    e.preventDefault();
    sendControlComand('stop', $(this));
});

$('#info-complex').on('click',function(e){
    e.preventDefault();
    let complex_id = getSelectedComplex();
    if (!complex_id) {
        return false;
    }
    $('#complex-command').val('info');
    $('#selected-complex').val(complex_id);
    let form = document.getElementById('action-form');
    blockControlButtons();
    formSubmit(form, actionInfo, $(this), releaseControlButtons);
});*/

/*$('#complex-list-control').on('change', function(e){
    let complex_id = getSelectedComplex();
    if (!complex_id) {
        return false;
    }
    $('#complex-cam-resolution').addClass('blocked');
    $.ajax({
        type: 'post',
        url: actionModes,
        data: {cid: complex_id},
        dataType: 'json',
        success: function(data) {
            console.log(data);
            if (data.result == 'success') {
                $('#complex-cam-resolution option').addClass('d-none');
                let tmp = data.modes;
                tmp.forEach(function(item, index) {
                    $('#complex-cam-resolution option[value="' + item + '"]').removeClass('d-none');
                });
                $('#complex-cam-resolution').removeClass('blocked');
            }
            if (data.result == 'error') {
                showToast(getJsStr(data.message), 'error');
            }
        },
        error: function(data) {
            console.log(data);
            showToast('SYSTEM FAILURE', 'error');
            $('#complex-cam-resolution').removeClass('blocked');
        }
    });
});

function getCamResolution() {
    let cam_res = $('#complex-cam-resolution').val();
    if (cam_res == null) {
        showToast(getJsStr('fields_required_empty'), 'error');
        return false;
    }
    return cam_res;
}

function getSelectedComplex() {
    let id = $('#complex-list-control option:selected').data('id');
    if (typeof(id) === 'undefined') {
        showToast(getJsStr('fields_required_empty'), 'error');
        return false;
    }
    return id;
}*/

/*function getCamResolution(cid) {
    let cam_res = $('#complex-cam-resolution-' + cid).val();
    if (cam_res == null) {
        showToast(getJsStr('fields_required_empty'), 'error');
        return false;
    }
    return cam_res;
}*/

function sendStartCommand(cid, btn) {
    /*let complex_id = getSelectedComplex();
    if (!complex_id) {
        return false;
    }*/
    /*let cam_res = getCamResolution(cid);
    if (!cam_res) {
        return false;
    }*/
    $('#complex-command-' + cid).val('start');
    $('#selected-complex-' + cid).val(cid);
    let form = document.getElementById('action-form-' + cid);
    //blockControlButtons();
    formSubmit(form, actionStart, btn, releaseControlButtons);
}

$('.stop-btn').each(function(){
    $(this).on('click',function(e){
        e.preventDefault();
        let cid = $(this).data('id');
        sendControlComand('stop', $(this), cid);
    });
});

$('.reboot-btn').each(function(){
    $(this).on('click',function(e){
        e.preventDefault();
        let cid = $(this).data('id');
        sendControlComand('reboot', $(this), cid);
    });
});

function sendControlComand(command, btn, cid) {
    /*let complex_id = getSelectedComplex();
    if (!complex_id) {
        return false;
    }*/
    $('#complex-command-' + cid).val(command);
    $('#selected-complex-' + cid).val(cid);
    let form = document.getElementById('action-form-' + cid);
    blockControlButtons();
    formSubmit(form, actionControl, btn, releaseControlButtons);
}

function blockControlButtons() {
    $('.complex-control').each(function(){
        $(this).addClass('btn-blocked');
    });
}

function releaseControlButtons() {
    $('.complex-control').each(function(){
        $(this).removeClass('btn-blocked');
    });
    visualizeInfo();
}
