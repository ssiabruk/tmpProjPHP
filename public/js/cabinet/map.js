
'use strict';

var map;
var startLat = 50.450096;
var startLon = 30.524189;

$(document).ready(function() {
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
});
