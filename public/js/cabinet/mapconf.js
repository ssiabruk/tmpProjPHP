
'use strict';

var greenMarker = new L.Icon({iconUrl: '/map/markers/green.gif', iconSize: [42, 42], shadowUrl: ''}),
    redMarker = new L.Icon({iconUrl: '/map/markers/red.gif', iconSize: [42, 42], shadowUrl: ''}),
    blueMarker = new L.Icon({iconUrl: '/map/markers/blue.gif', iconSize: [42, 42], shadowUrl: ''}),
    indigoMarker = new L.Icon({iconUrl: '/map/markers/indigo.gif', iconSize: [42, 42], shadowUrl: ''}),
    pinkMarker = new L.Icon({iconUrl: '/map/markers/pink.gif', iconSize: [42, 42], shadowUrl: ''}),
    purpleMarker = new L.Icon({iconUrl: '/map/markers/purple.gif', iconSize: [42, 42], shadowUrl: ''}),
    yellowMarker = new L.Icon({iconUrl: '/map/markers/yellow.gif', iconSize: [42, 42], shadowUrl: ''}),
    orangeMarker = new L.Icon({iconUrl: '/map/markers/orange.gif', iconSize: [42, 42], shadowUrl: ''});

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

var baseMarker = new L.Icon({iconUrl: '/map/markers/base.png', iconSize: [40, 54], iconAnchor: [20, 54], shadowUrl: ''}),
    newDetect = new L.Icon({iconUrl: '/map/detects/new.png', iconSize: [28, 28], shadowUrl: ''});

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
    //console.log(lat, lon);
}

/*function setDetect(markers, iconx, popupx, lat, lon) {
    let marker = L.marker([lat, lon], {icon: eval(iconx)});
    let markerId = marker.getLayerId();
    if (popupx) {
        marker.bindPopup(popupx, {className:'imgWidth'});
    }
    markers.addLayer(marker);
}*/
