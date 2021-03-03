
'use strict';

var map;
var startLat = 50.450096;
var startLon = 30.524189;
var markers100 = [];
var picDet;
var complexMarkerObj;
var baseMarkerObj;
var isBaseButton = 0;
var bsPolyline;
var cxPolyline;

var greenDetect = new L.Icon({iconUrl: '/map/detects/green.png', iconSize: [14, 14], shadowUrl: ''}),
    redDetect = new L.Icon({iconUrl: '/map/detects/red.png', iconSize: [14, 14], shadowUrl: ''}),
    blueDetect = new L.Icon({iconUrl: '/map/detects/blue.png', iconSize: [14, 14], shadowUrl: ''}),
    indigoDetect = new L.Icon({iconUrl: '/map/detects/indigo.png', iconSize: [14, 14], shadowUrl: ''}),
    pinkDetect = new L.Icon({iconUrl: '/map/detects/pink.png', iconSize: [14, 14], shadowUrl: ''}),
    purpleDetect = new L.Icon({iconUrl: '/map/detects/purple.png', iconSize: [14, 14], shadowUrl: ''}),
    yellowDetect = new L.Icon({iconUrl: '/map/detects/yellow.png', iconSize: [14, 14], shadowUrl: ''}),
    orangeDetect = new L.Icon({iconUrl: '/map/detects/orange.png', iconSize: [14, 14], shadowUrl: ''});

var greenPlane = new L.Icon({iconUrl: '/map/planes/green.png', iconSize: [42, 42], shadowUrl: ''}),
    redPlane = new L.Icon({iconUrl: '/map/planes/red.png', iconSize: [42, 42], shadowUrl: ''}),
    bluePlane = new L.Icon({iconUrl: '/map/planes/blue.png', iconSize: [42, 42], shadowUrl: ''}),
    indigoPlane = new L.Icon({iconUrl: '/map/planes/indigo.png', iconSize: [42, 42], shadowUrl: ''}),
    pinkPlane = new L.Icon({iconUrl: '/map/planes/pink.png', iconSize: [42, 42], shadowUrl: ''}),
    purplePlane = new L.Icon({iconUrl: '/map/planes/purple.png', iconSize: [42, 42], shadowUrl: ''}),
    yellowPlane = new L.Icon({iconUrl: '/map/planes/yellow.png', iconSize: [42, 42], shadowUrl: ''}),
    orangePlane = new L.Icon({iconUrl: '/map/planes/orange.png', iconSize: [42, 42], shadowUrl: ''});

var baseMarker = new L.Icon({iconUrl: '/map/markers/base.png', iconSize: [40, 54], iconAnchor: [20, 54], shadowUrl: ''});

$(document).ready(function() {
    if (currentPage == 'main') {
        map = new L.Map('one-map-area', {
            center: new L.LatLng(startLat, startLon),
            zoom: 11,
            zoomControl: false,
            fullscreenControl: true,
            fullscreenControlOptions: {
                position: 'topright'
            }
        });
        L.control.zoom({position: 'topright'}).addTo(map);
        var LocalLayer = L.tileLayer(mapUrl + '/{z}/{x}/{y}.png', {maxZoom:16, minZoom:7});
        map.addLayer(LocalLayer);

        var southWest = L.latLng(43.379840, 22.023650),
        northEast = L.latLng(52.460855, 40.605299);
        var bounds = L.latLngBounds(southWest, northEast);
        map.setMaxBounds(bounds);
        map.on('drag', function() {
            map.panInsideBounds(bounds, {animate:false});
        });

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
    }

    if (currentPage == 'page') {
        //if (typeof(hasImage)) !== 'undefined' && hasImage) {
        if (hasImage) {
            setGuil();
            return true;
        }
        loadImage();
    }
});

function getCookie(name) {
    let matches = document.cookie.match(new RegExp(
        "(?:^|; )" + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + "=([^;]*)"
    ));
    return matches?matches[1]:0;
}

function setMarker(iconx, titlex, lat, lon, dmap, toback) {
    let marker = L.marker([lat, lon], {icon: eval(iconx), title: titlex});
    if (toback === true) {
        marker.setZIndexOffset(0);
    }
    marker.addTo(dmap);
    return marker;
}

function changeMarker(marker, lat, lon) {
    let newLatLng = new L.LatLng(lat, lon);
    marker.setLatLng(newLatLng).update();
}

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
            if (data.result == 'success') {
                lastDetects();
                mapShow();
            }
        },
        error: function(data) {
            console.log('101');
            console.log(data);
        }
    });
}

function lastDetects() {
    $.ajax({
        type: 'post',
        url: actionLastDetects,
        dataType: 'json',
        success: function(data) {
            if (data.result == 'success') {
                $('#last-detects').html(data.message);
            }
        },
        error: function(data) {
            console.log('102');
            console.log(data);
            if (typeof(data) !== 'undefined') {
                showToast('SYSTEM FAILURE', 'error');
            }
        }
    });
}

function mapShow() {
    $.ajax({
        type: 'post',
        url: actionMap,
        dataType: 'json',
        success: function(data) {
            if (data.result == 'error') {
                showToast(getJsStr(data.message), 'error');
                return;
            }
            if (typeof(data.message) == 'undefined') {
                clearMarkers();
                return;
            }
            let detData = JSON.parse(data.message);
            if (Array.isArray(detData)) {
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
                        let new_marker = L.marker([item.lat, item.lon], {icon: eval(iconx)});
                        let markerId = new_marker;
                        new_marker.bindPopup(item.popup, {className:'imgWidth'});
                        map.addLayer(new_marker);
                        let new_item = {dtid: item.dtid, mid: markerId, s: 'new'};
                        markers100.push(new_item);
                    }
                });
            } else {
                showToast(getJsStr('error_complex_data'), 'error');
            }
        },
        error: function(data) {
            console.log('103', data);
            if (typeof(data) !== 'undefined') {
                showToast('SYSTEM FAILURE', 'error');
            }
        }
    });
}

function clearMarkers() {
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

function stream(cid) {
    $('#selected-complex').val(cid);
    let form = document.getElementById('stream-form');
    formSubmit(form, actionCheck, null, startStream);
}

function startStream(data) {
    if (data.result == 'info') {
        $('#va-' + data.cid).html(getJsStr(data.message));
        return true;
    }
    if (data.result == 'success') {
        let complex_ip = data.cip;
        let stream_url = 'http://' + complex_ip + data.videourl;
        $('#mjpeg-' + data.cid).attr('src', stream_url);
        let timerId = setTimeout(function() {
            clearSpinner('#va-' + data.cid);
        }, 2000);
        return true;
    }
    $('#va-' + data.cid).html(getJsStr('error_grabber_active'));
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

function loadImage() {
    let dtidval = $('#dtid').val();
    $.ajax({
        type: 'post',
        url: actionGetImage,
        data: {dtid: dtidval, stype: stype},
        dataType: 'json',
        success: function(data) {
            if (data.result == 'success') {
                $('#dt-img').attr('src', data.url);
                setGuil();
            }
            if (data.result == 'error') {
                showToast(getJsStr(data.message), 'error');
            }
            $('#data-loading').addClass('d-none');
        },
        error: function(data) {
            console.log('15');
            console.log(data);
            alert('SYSTEM FAILURE');
        }
    });
}

function setGuil() {
    $('#dt-img').addClass('d-none');
    let w = $('.panorama').width();
    let h = $('.panorama').height();
    console.log(w, h);
    picDet = $('#dt-img');
    setTimeout(function() {
        $('#dt-img').removeClass('d-none');
        picDet.guillotine({width: w, height: h});
        picDet.guillotine('zoomIn');
    }, 500);
    $('#controls').removeClass('disabled');
}

$('#controls button').click(function(e) {
    e.preventDefault();
    let action = this.id;
    picDet.guillotine(action);
});
