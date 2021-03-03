
'use strict';

//var detectMarkers;
var marker1 = null;

$(document).ready(function(){
    //detectMarkers = L.layerGroup();
    loadTracks();
    loadCarusel();
});

function loadTracks() {
    let has_trkx = $('#tracks').length;
    if (!has_trkx) return;
    $.ajax({
        type: 'post',
        url: actionLoadTracks,
        data: {cid: cid, sid: sid},
        dataType: 'json',
        success: function(data) {
            //console.log(data);
            if (data.message) {
                if (data.result == 'success') {
                    $('tbody#tracklist').html(data.message);
                    $('tbody#trackcount').html(data.tcount);
                    $('#tracks-spinner').addClass('d-none');
                    $('#tracks').removeClass('d-none');
                    $('#tracks-data').removeClass('d-none');
                    $('#tracks-count').removeClass('d-none');
                    $('#one-map-area').removeClass('d-none');
                    map.invalidateSize();
                    map.setZoom(7);
                } else {
                    $('#tracks-spinner').html(data.message);
                }
            } else {
                //$('#tracks').html(notracks);
            }
        },
        error: function(data) {
            console.log('26');
            console.log(data);
            alert('SYSTEM FAILURE');
        }
    });
}

$('#tracklist').on('click', '.track', function(){
    let tid = $(this).data('id');
    let lat = $(this).data('lat');
    let lon = $(this).data('lon');
    showTrackOnMap(lat, lon);
    //console.log(lat, lon);
    loadTrackImage(tid);
    if (lat && lon) {
        $('#detect-coords').html(lat + ', ' + lon);
    }
});

function showTrackOnMap(lat, lon) {
    //detectMarkers.clearLayers();
    if (marker1) {
        map.removeLayer(marker1);
    }
    if (lat && lon) {
        let iconx = ccolor + 'Detect';
        let marker_label = lat + ', ' + lon;
        //let marker = setDetect(detectMarkers, iconx, marker_label, lat, lon);
        //map.addLayer(detectMarkers);
        marker1 = L.marker([lat, lon], {icon: eval(iconx)});
        marker1.bindPopup(marker_label, {className:'imgWidth'});
        map.addLayer(marker1);
        map.setView(new L.LatLng(lat, lon), 15);
    }
}

function loadCarusel() {
    const carusel = $('.owl-carousel');
    if (carusel.length) {
        carusel.owlCarousel({
            loop:false,
            margin:10,
            responsiveClass:true,
            nav:false,
            dots:true,
            lazyLoad:true,
            responsive:{
                0:{
                    items:1
                },
                600:{
                    items:3
                },
                1000:{
                    items:5
                }
            }
        });
        carusel.on('drag.owl.carousel', function(event) {
            console.log('start');
            $('.owl-carousel').addClass('owl-carousel-bg');
        });
        carusel.on('dragged.owl.carousel', function(event) {
            console.log('stop');
            $('.owl-carousel').removeClass('owl-carousel-bg');
        });
        carusel.on('translate.owl.carousel', function(event) {
            console.log('start');
            $('.owl-carousel').addClass('owl-carousel-bg');
        });
        carusel.on('translated.owl.carousel', function(event) {
            console.log('stop');
            $('.owl-carousel').removeClass('owl-carousel-bg');
        });
    }
}

$('.det-img > img').each(function(){
    $(this).on('click', function(e) {
        e.preventDefault();
        let lat = $(this).data('lat');
        let lon = $(this).data('lon');
        let trackid = $(this).data('track');
        $.ajax({
            type: 'post',
            url: actionLoadTrack,
            data: {tid: trackid, stype: stype},
            dataType: 'json',
            success: function(data) {
                //console.log(data);
                if (data.result == 'success') {
                    $('#detect-date').html(data.dtime);
                    $('#detect-objects').html(data.track);
                    if (lat && lon) {
                        $('#detect-coords').html(lat + ', ' + lon);
                    }
                }
                if (data.result == 'error') {
                    showToast(getJsStr(data.message), 'error');
                }
            },
            error: function(data) {
                console.log('09');
                console.log(data);
                alert('SYSTEM FAILURE');
            }
        });

        let dtidval = $(this).data('dtid');
        loadTrackImage(dtidval);
        showTrackOnMap(lat, lon);
    });
});

function loadTrackImage(tid) {
    $.ajax({
        type: 'post',
        url: actionGetImage,
        data: {dtid: tid, stype: stype},
        dataType: 'json',
        beforeSend: function() {
            $('#dt-img-bg').addClass('pic-load');
            $('#dt-img').addClass('d-none');
            $('#controls').addClass('disabled');
        },
        success: function(data) {
            $('#dt-img-bg').removeClass('pic-load');
            //console.log(data);
            if (data.result == 'success') {
                $('#dt-img').attr('src', data.url);
                setGuil();
                $('#dt-img').removeClass('d-none');
            }
            if (data.result == 'error') {
                showToast(getJsStr(data.message), 'error');
            }
        },
        error: function(data) {
            console.log('10');
            console.log(data);
            alert('SYSTEM FAILURE');
            $('#dt-img-bg').removeClass('pic-load');
        }
    });
}

var picDet;

function setGuil() {
    let w = $('.panorama').width();
    let h = $('.panorama').height();
    picDet = $('#dt-img');
    picDet.on('load', function() {
        $('#controls').removeClass('disabled');
        picDet.guillotine({width: w, height: h});
    });
    //console.log(picDet);
}

$('#controls button').click(function(e) {
    e.preventDefault();
    let action = this.id;
    picDet.guillotine(action);
});
