(function($, L, d){

    /**
     * Initialise la surcharge des différents prototypes de LeafletDraw
     * Ceci dans le but d'adapter cette librairie à EsterenMap
     */
    EsterenMap.prototype.activateLeafletDraw = function(){
        var _this = this,
            mapOptions = this._mapOptions,
            drawControl, drawnItems, styleTag
        ;

        if (mapOptions.editMode !== true) {
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
        this._drawControl = drawControl;
        this._drawnItems = drawnItems;

        _this = this;

        this._map.on('draw:created', function(event) {
            var type = event.layerType,
                layer = event.propagatedFrom,
                mapOptions = _this._mapOptions,
                latlng,
                popupContent,
                options,
                editOptions
            ;

            if (type === 'marker') {
                popupContent = mapOptions.LeafletPopupMarkerBaseContent;
                options = mapOptions.CustomMarkerBaseOptionsEditMode;
                editOptions = mapOptions.LeafletMarkerBaseOptionsEditMode;

                options.popupContent = popupContent;
                options.markerName = '';
                options.markerType = '';
                options.markerFaction = '';
                options.popupIsSidebar = true;

                latlng = layer._latlng;

                _this.addMarker(latlng,
                    editOptions,
                    options
                );
            } else if (type === 'polyline') {
                options = mapOptions.CustomPolylineBaseOptionsEditMode;
                editOptions = mapOptions.LeafletPolylineBaseOptionsEditMode;

                options.polyline = event.layer;
                editOptions.editing = {}; // Strange that we must do this, else editing doesn't work...

                latlng = layer._latlngs;

                _this.addPolyline(latlng,
                    editOptions,
                    options
                );
            } else if (type === 'polygon') {
                options = mapOptions.CustomPolygonBaseOptionsEditMode;
                editOptions = mapOptions.LeafletPolygonBaseOptionsEditMode;

                latlng = layer._latlngs;

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

})(jQuery, L, document);
