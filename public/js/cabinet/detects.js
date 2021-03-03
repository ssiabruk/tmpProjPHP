
'use strict';

var complexMarkerObj;
var baseMarkerObj;
var isBaseButton = 0;
var bsPolyline;
var cxPolyline;
var markers100 = [];
/*var timerId;
var timerId2;*/
//var detectMarkers;

$(document).ready(function() {
    /*detectMarkers = L.markerClusterGroup({
        spiderfyDistanceMultiplier: 1.2,
        maxClusterRadius: 20
    });*/
    //detectMarkers = L.layerGroup();
    //map.addLayer(detectMarkers);
    let timerId = setTimeout(function mapTimer() {
        refreshMap(1);
        timerId = setTimeout(mapTimer, 5000);
    }, 5000);
    lastDetects();
    mapShow();

    let timerId2 = setTimeout(function cxTimer() {
        showDepartureComplex();
        timerId2 = setTimeout(cxTimer, 2000);
    }, 2000);

    let bslat = getCookie('bslat');
    let bslng = getCookie('bslng');

    if (bslat != 0 && bslng != 0) {
        baseMarkerObj = setMarker('baseMarker', null, bslat, bslng, map);
        map.setView(new L.LatLng(bslat, bslng), 12);
    }

    map.on('click', function(e) {
        if (isBaseButton == 0) {
            return false;
        }
        $('#set-base-marker').button('toggle');
        baseMarkerObj = setMarker('baseMarker', null, e.latlng.lat, e.latlng.lng, map);
        isBaseButton = 0;
        document.cookie = 'bslat=' + e.latlng.lat;
        document.cookie = 'bslng=' + e.latlng.lng;
    });
});

function showDepartureComplex() {
    $.ajax({
        type: 'post',
        url: departClient,
        data: {command: 'init'},
        dataType: 'json',
        success: function(data) {
            //console.log(data);
            if (data.result == 'success') {
                let tmp = JSON.parse(data.message);
                if (!tmp.lat || !tmp.lon) {
                    return false;
                }
                /*if (complexMarkerObj != undefined) {
                    changeMarker(complexMarkerObj, tmp.lat, tmp.lon);
                } else {
                    let iconx = tmp.cc + 'Plane';
                    complexMarkerObj = setMarker(iconx, '', tmp.lat, tmp.lon, map);
                }*/
                if (complexMarkerObj != undefined) {
                    map.removeLayer(complexMarkerObj);
                }
                let iconx = tmp.cc + 'Plane';
                complexMarkerObj = setMarker(iconx, '', tmp.lat, tmp.lon, map, true);

                if (cxPolyline != undefined) {
                    map.removeLayer(cxPolyline);
                }
                if (baseMarkerObj != undefined) {
                    let from = baseMarkerObj.getLatLng();
                    let to = complexMarkerObj.getLatLng();
                    let distance = from.distanceTo(to);
                    let latlngs = [[from.lat, from.lng],[to.lat, to.lng]];
                    cxPolyline = L.polyline(latlngs, {color: '#999999', weight: 1}).addTo(map);
                    let cxAngle = 0;
                    if (from.lng >= to.lng) {
                        cxAngle = 180;
                    }
                    cxPolyline.setText(Math.round(distance) + ' m', {center: true, orientation: cxAngle, attributes: {dy: -3, 'font-weight': 'bold'}});
                    //console.log('D: ' + distance);
                    setTimeout(function() {
                        map.removeLayer(cxPolyline);
                    }, 1000);
                }
            }
            if (data.result == 'error') {
                showToast(getJsStr(data.message), 'error');
            }
        },
        error: function(data) {
            console.log('32', data.responseText);
        }
    });
}

function getCookie(name) {
    let matches = document.cookie.match(new RegExp(
        "(?:^|; )" + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + "=([^;]*)"
    ));
    return matches?matches[1]:0;
}

$('#set-base-marker').on('click', function(e) {
    e.preventDefault();
    if (isBaseButton == 0) {
        isBaseButton = 1;
    } else {
        isBaseButton = 0;
    }
    if (baseMarkerObj != undefined) {
        map.removeLayer(baseMarkerObj);
        document.cookie = 'bslat=0';
        document.cookie = 'bslng=0';
        baseMarkerObj = undefined;
    };
    if (bsPolyline != undefined) {
        map.removeLayer(bsPolyline);
        bsPolyline = undefined;
    };
    if (cxPolyline != undefined) {
        map.removeLayer(cxPolyline);
        cxPolyline = undefined;
    }
});

function markerOnClick(e) {
    if (bsPolyline != undefined) {
        map.removeLayer(bsPolyline);
    }
    if (baseMarkerObj != undefined) {
        let from = baseMarkerObj.getLatLng();
        let to = e.latlng; // this.getLatLng();
        let distance = from.distanceTo(to);
        let latlngs = [[from.lat, from.lng],[to.lat, to.lng]];
        bsPolyline = L.polyline(latlngs, {color: 'blue', weight: 1}).addTo(map);
        let bsAngle = 0;
        if (from.lng >= to.lng) {
            bsAngle = 180;
        }
        bsPolyline.setText(Math.round(distance) + ' m', {center: true, orientation: bsAngle, attributes: {dy: -3, 'font-weight': 'bold'}});
        console.log('Distance: ' + distance);
        setTimeout(function() {
            map.removeLayer(bsPolyline);
        }, 3000);
    }
}

function mapShow() {
    $.ajax({
        type: 'post',
        url: actionMap,
        dataType: 'json',
        success: function(data) {
            //console.log(data, '-----');
            if (data.result == 'error') {
                showToast(getJsStr(data.message), 'error');
                return;
            }
            //if (data.result == 'success') {
                //console.log(data.message, '=====');
                if (typeof(data.message) == 'undefined') {
                    clearMarkers();
                    return;
                }
                let detData = JSON.parse(data.message);
                //console.log(detData, '+++++');
                if (Array.isArray(detData)) {
                    /*detectMarkers.clearLayers();
                    tmp.forEach(function(item, index) {
                        let iconx = item.cc + 'Detect';
                        if (item.lat && item.lon) {
                            item.marker = setDetect(detectMarkers, iconx, item.popup, item.lat, item.lon);
                        }
                    });
                    map.addLayer(detectMarkers);*/
                    markers100.forEach(function(item, index) {
                        let dtid1 = item.dtid;
                        let tmp_det = detData.find(item => item.dtid == dtid1);
                        let has_det = (typeof(tmp_det) === 'undefined' || typeof(tmp_det.dtid) === 'undefined');
                        if (has_det) {
                            if (markers100[index].s == 'new') {
                                markers100[index].s == 'del';
                                let marker_id = markers100[index].mid;
                                map.removeLayer(marker_id);
                            }
                        }
                    });
                    let tmp_markers100 = markers100.filter(item => item.s == 'new');
                    markers100 = tmp_markers100;
                    detData.forEach(function(item, index) {
                        let dtid2 = item.dtid;
                        let tmp_marker = markers100.find(item => item.dtid == dtid2);
                        let has_marker = (typeof(tmp_marker) === 'undefined' || typeof(tmp_marker.dtid) === 'undefined');
                        if (item.lat && item.lon && has_marker) {
                            let iconx = item.cc + 'Detect';
                            let new_marker = L.marker([item.lat, item.lon], {icon: eval(iconx)}).on('click', markerOnClick);
                            //console.log(L.stamp(new_marker));
                            let markerId = new_marker; //L.stamp(new_marker);
                            new_marker.bindPopup(item.popup, {className:'imgWidth'});
                            map.addLayer(new_marker);
                            let new_item = {dtid: item.dtid, mid: markerId, s: 'new'};
                            markers100.push(new_item);
                        }
                    });
                } else {
                    showToast(getJsStr('error_complex_data'), 'error');
                }
            //}
        },
        error: function(data) {
            console.log('21', data);
            if (typeof(data) !== 'undefined') {
                showToast('SYSTEM FAILURE', 'error');
            }
        }
    });
}

function lastDetects() {
    $.ajax({
        type: 'post',
        url: actionLastDetects,
        dataType: 'json',
        success: function(data) {
            //console.log(data);
            if (data.result == 'success') {
                $('#last-detects').html(data.message);
            }
        },
        error: function(data) {
            console.log('22');
            console.log(data);
            if (typeof(data) !== 'undefined') {
                showToast('SYSTEM FAILURE', 'error');
            }
        }
    });
}


function refreshMap(map_only) {
    if (map_only !== 1) {
        $('#last-detects').html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>');
        lastDetects();
        mapShow();
        return true;
    }

    $.ajax({
        type: 'post',
        url: actionMapRefresh,
        dataType: 'json',
        success: function(data) {
            //console.log(data, '#####');
            if (data.result == 'success') {
                lastDetects();
                mapShow();
            }
        },
        error: function(data) {
            console.log('23');
            console.log(data);
            //alert('SYSTEM FAILURE');
        }
    });
}

$('#last-detects').on('click', '.detect-image', function(e) {
    e.preventDefault();
    let lat = $(this).data('lat');
    let lon = $(this).data('lon');
    if (lat && lon) {
        map.setView(new L.LatLng(lat, lon), 15);
    } else {
        showToast(getJsStr('error_coords'), 'error');
    }
    //console.log(lat, lon);
});

function clearMarkers() {
    //console.log(markers100, '*****');
    markers100.forEach(function(item, index) {
    let marker_id = markers100[index].mid;
        map.removeLayer(marker_id);
    });
    markers100 = [];
    if (complexMarkerObj != undefined) {
        map.removeLayer(complexMarkerObj);
    }
}

$('#stream-video-box').on('hide.bs.dropdown', function (e) {
    if (e.clickEvent) {
      e.preventDefault();
    }
});

$('#stream-video-view').on('click', function(e) {
    e.preventDefault();
    let complex_id = $('#complex-list').children('option:selected').data('id');
    stream(complex_id);
});

$('#complex-list').on('change', function(e){
    $('#stream-video-view').dropdown('hide');
    clearMarkers();
    map.setView(new L.LatLng(startLat, startLon), 11);
    let complex_id = $(this).children('option:selected').data('id');
    $('.va-info').attr('id', 'va-' + complex_id);
    $('.mjpeg-img').attr('id', 'mjpeg-' + complex_id);
    $('.mjpeg-img').attr('src', '');
    $('#va-' + complex_id).html('<span class="spinner-grow" role="status" aria-hidden="true" style="margin:15px;"></span>');
    setComplex(complex_id, refreshMap);
});

$('#notify-range').change(function(){
    $('#time-off').text($(this).val());
});

$('#btn-notify-off').on('click', function(e) {
    let tmp = $('#notify-range').val();
    $('#notify-time-off').val(tmp);
    let form = document.getElementById('notify-form');
    formSubmit(form, actionNotifyOff, $(this));
});

/*$('#clear-base').on('click', function(e) {
    e.preventDefault();
    if (!confirm('Clear detects')) {
        return false;
    }
    $.ajax({
        type: 'post',
        url: '/map/clear',
        dataType: 'json',
        success: function(data) {
            if (data.result == 'success') {
                document.location.reload();
            }
        },
        error: function(data) {
            console.log(data);
            alert('SYSTEM FAILURE');
        }
    });
});
*/