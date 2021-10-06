(function (d) {

    EsterenMap.prototype.activateEditMode = function () {
        var _this = this;

        this.activateLeafletDraw();

        this._map.on('draw:editvertex', function () {
            if (_this._editedMarker) {
                _this._editedMarker.updateDetails();
            }

            if (_this._editedPolyline) {
                _this._editedPolyline.updateDetails();
            }

            if (_this._editedPolygon) {
                _this._editedPolygon.updateDetails();
            }
        });

        var clickCoords = null;
        this._map.addEventListener('mousedown', function (e) {
            if (!_this.hasSelectedElement()) {
                return;
            }
            if (e.latlng) {
                clickCoords = e.latlng;
            }
        });
        this._map.addEventListener('mouseup', function (e) {
            if (!_this.hasSelectedElement()) {
                return;
            }
            if (e.latlng && clickCoords && e.latlng.lat === clickCoords.lat && e.latlng.lng === clickCoords.lng) {
                _this.disableEditedElements();
            }
            clickCoords = null;
        });
    };

    // Depends on "toggleClass" helper.

    /**
     * Masquage des MARQUEURS
     */
    var markers = d.getElementById('hide_markers');
    if (markers) {
        markers.addEventListener('click', function () {
            var css = d.getElementById('map_add_style').innerHTML;
            this.toggleClass('active');
            if (this.className.match('active')) {
                css += '/*@MARKERS*/.leaflet-marker-icon:not(.leaflet-editing-icon){display:none;}/*MARKERS@*/' + "\n";
            } else {
                css = css.replace(/[\/]\*@MARKERS[^@]+@\*\//gi, '');
            }
            d.getElementById('map_add_style').innerHTML = css;
        });
    }

    /**
     * Masquage des ZONES
     */
    var zones = d.getElementById('hide_zones');
    if (zones) {
        zones.addEventListener('click', function () {
            var css = d.getElementById('map_add_style').innerHTML;
            this.toggleClass('active');
            if (this.className.match('active')) {
                css += '/*@ZONES*/[class*=drawn_polygon]{display:none;}/*ZONES@*/' + "\n";
            } else {
                css = css.replace(/[\/]\*@ZONES[^@]+@\*\//gi, '');
            }
            d.getElementById('map_add_style').innerHTML = css;
        });
    }

    /**
     * Masquage des ROUTES
     */
    var routes = d.getElementById('hide_routes');
    if (routes) {
        routes.addEventListener('click', function () {
            var css = d.getElementById('map_add_style').innerHTML;
            this.toggleClass('active');
            if (this.className.match('active')) {
                css += '/*@ROUTES*/[class*=drawn_polyline]{display:none;}/*ROUTES@*/' + "\n";
            } else {
                css = css.replace(/[\/]\*@ROUTES[^@]+@\*\//gi, '');
            }
            d.getElementById('map_add_style').innerHTML = css;
        });
    }

})(document);
