/**
 * This script was here to transform latlng to x,y when Mercator projection is activated.
 */

// Whole update script
var bounds = [[0, 0], [132.8, 169.75]];
var map = document.map;
var leafletMap = map._map;
var crs = leafletMap.options.crs;
var markers = map._markers;
var polylines = map._polylines;
var polygons = map._polygons;
var i, l, marker, polyline, polygon, latlng, latlngs, list, point;
var transformation = new L.Transformation(0.03128, 0, 0.03128, 0);

leafletMap.setZoom(2);
leafletMap.setView([0,0]);

for (i in markers) {
    if (!markers.hasOwnProperty(i)) { continue; }
    marker = markers[i];
    point = leafletMap.project(marker.getLatLng(), leafletMap.getMaxZoom());
    point = transformation.transform(point);
    //marker._latlng = L.latLng(point.y, point.x);
    //marker._updateEM();
}

for (i in polylines) {
    if (!polylines.hasOwnProperty(i)) { continue; }
    polyline = polylines[i];
    latlngs = [];
    for (list = polyline.getLatLngs(), i = 0, l = list.length; i < l; i++) {
        latlng = list[i];
        point = leafletMap.project(latlng, leafletMap.getMaxZoom());
        point = transformation.transform(point);
        list[i] = L.latLng(point.y, point.x);
    }
    //polyline._latlngs = list;
    //polyline._updateEM();
}

for (i in polygons) {
    if (!polygons.hasOwnProperty(i)) { continue; }
    polygon = polygons[i];
    latlngs = [];
    for (list = polygon.getLatLngs(), i = 0, l = list.length; i < l; i++) {
        latlng = list[i];
        point = leafletMap.project(latlng, leafletMap.getMaxZoom());
        point = transformation.transform(point);
        list[i] = L.latLng(point.y, point.x);
    }
    //polygon._latlngs = list;
    //polygon._updateEM();
}

// Single test
marker = markers[68];
//latlng = L.latLng([81.28171699935, -130.25390625]);
point = leafletMap.project(latlng);
latlng = L.latLng(point.y, point.x);
marker._latlng = latlng;
//marker._updateEM();


// Debug toolbar
$('#debugcoords').remove();
a=$('<div id="debugcoords" style="position: absolute; left: 50%; top: 0;background: rgba(0, 0, 0, 0.45);min-height: 20px;min-width: 300px;z-index: 5;text-align:left;color: white;padding-left: 15px;margin-left: -150px;">0,0</div>');
a.prependTo('#map');
document.map._map.off('mousemove');
document.map._map.on('mousemove', function(e){
    var str = '';
    str += 'latlng : ' + e.latlng.lat + ',' + e.latlng.lng + '<br>';
    str += 'layerpoint : ' + e.layerPoint.x + ',' + e.layerPoint.y + '<br>';
    str += 'containerpoint : ' + e.containerPoint.x + ',' + e.containerPoint.y + '<br>';
    document.getElementById('debugcoords').innerHTML = str;
});

// Bounds testing
var southWest = L.latLng(133, 0);
var northEast = L.latLng(0, 170);
var bounds = L.latLngBounds(southWest, northEast);
document.map._map.setMaxBounds(bounds);
document.map._map.fitBounds(bounds);
document.map._markers[68].setLatLng(bounds.getNorthEast());
console.info('southWest', southWest);
console.info('northEast', northEast);
console.info('bounds', bounds);
