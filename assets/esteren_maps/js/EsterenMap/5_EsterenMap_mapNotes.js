(function (d) {

    EsterenMap.prototype._drawNotesControl = null;
    EsterenMap.prototype._drawnNotes = null;
    EsterenMap.prototype._editedMarkerNote = null;
    EsterenMap.prototype._editedPolylineNote = null;
    EsterenMap.prototype._editedPolygonNote = null;

    EsterenMap.prototype.activateNotesEdition = function () {
        var _this = this;

        this.activateLeafletDrawForNotes();

        this._map.on('draw:editvertex', function () {
            if (_this._editedMarkerNote) {
                _this._editedMarkerNote.updateDetails();
            }

            if (_this._editedPolylineNote) {
                _this._editedPolylineNote.updateDetails();
            }

            if (_this._editedPolygonNote) {
                _this._editedPolygonNote.updateDetails();
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

    EsterenMap.prototype.activateLeafletDrawForNotes = function(){
        var _this = this,
            mapOptions = this._mapOptions,
            drawControl, drawnItems, styleTag
        ;

        if (mapOptions.canAddNotes !== true) {
            return false;
        }

        // Doit contenir les nouveaux éléments ajoutés à la carte afin qu'ils soient éditables
        drawnItems = new L.FeatureGroup();
        this._map.addLayer(drawnItems);

        // Ajoute les boutons de contrôle
        drawControl = new L.Control.Draw({
            draw: {
                circle: false,
                circlemarker: false,
                rectangle: false,
                polygon: {
                    allowIntersection: false
                }
            },
            edit: {
                featureGroup: drawnItems,
                edit: {
                    selectedPathOptions: {
                        dashArray: '7, 7',
                        weight: 2,
                        maintainColor: true
                    }
                }
            }
        });

        this._map.addControl(drawControl);
        this._drawNotesControl = drawControl;
        this._drawnNotes = drawnItems;

        _this = this;

        this._map.on('draw:created', function(event) {
            var type = event.layerType,
                layer = event.propagatedFrom || event.layer,
                mapOptions = _this._mapOptions,
                latlng,
                options,
                editOptions
            ;

            if (type === 'marker') {
                options = mapOptions.CustomMarkerBaseOptionsEditMode;
                editOptions = mapOptions.LeafletMarkerBaseOptionsEditMode;

                options.popupContent = mapOptions.data.templates.LeafletPopupMarkerEditContent;
                options.markerName = '';
                options.markerType = '';
                options.markerFaction = '';
                options.popupIsSidebar = true;

                if (mapOptions.canAddNotes) {
                    options.isNote = true;
                }

                latlng = layer._latlng;

                _this.addMarker(latlng,
                    editOptions,
                    options
                );
            } else if (type === 'polyline') {
                options = mapOptions.CustomPolylineBaseOptionsEditMode;
                editOptions = mapOptions.LeafletPolylineBaseOptionsEditMode;

                options.polyline = layer;
                editOptions.editing = {}; // Strange that we must do this, else editing doesn't work...

                latlng = layer._latlngs;

                if (mapOptions.canAddNotes) {
                    options.isNote = true;
                }

                _this.addPolyline(latlng,
                    editOptions,
                    options
                );
            } else if (type === 'polygon') {
                options = mapOptions.CustomPolygonBaseOptionsEditMode;
                editOptions = mapOptions.LeafletPolygonBaseOptionsEditMode;

                options.polygon = layer;
                latlng = layer._latlngs;

                if (mapOptions.canAddNotes) {
                    options.isNote = true;
                }

                _this.addPolygon(latlng,
                    editOptions,
                    options
                );
            }
        });

        this._map.on('draw:edited', function(event) {
            var layers = event.layers;

            layers.eachLayer(function (layer) {
                if (layer._esterenRoute && layer._esterenRoute.id) {
                    // Route
                    layer._updateEM();
                } else if (layer._esterenZone && layer._esterenZone.id) {
                    // Zone
                    layer._updateEM();
                }
            });

            return true;
        });

        this._map.on('draw:deleted', function(event) {
            var layers = event.layers;

            layers.eachLayer(function (layer) {
                if (layer._esterenMarker && layer._esterenMarker.id) {
                    // Marqueur
                    $('input,textarea').filter(function(i,element){
                        return element.id.match('marker_'+layer._esterenMarker.id+'_');
                    }).remove();

                } else if (layer._esterenRoute && layer._esterenRoute.id) {
                    // Route
                    $('input,textarea').filter(function(i,element){
                        return element.id.match('polyline_'+layer._esterenRoute.id+'_');
                    }).remove();

                } else if (layer._esterenZone && layer._esterenZone.id) {
                    // Zone
                    $('input,textarea').filter(function(i,element){
                        return element.id.match('polygon_'+layer._esterenZone.id+'_');
                    }).remove();

                }
            });

            return true;
        });

        // Invisible markers for edit mode
        styleTag = d.createElement('style');
        styleTag.innerHTML = '[data-leaflet-object-type="markerType10"] { border: solid 1px rgb(194, 194, 194);background-color: rgba(0, 0, 0, 0.5);}';
        d.head.appendChild(styleTag);
    };

})(document);
