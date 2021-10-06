(function($, L, d, w){

    // Rajoute qqs attributs à des éléments de Leaflet et LeafletSidebar
    L.Marker.prototype._esterenMap = {};
    L.Marker.prototype._esterenMarker = {};
    L.Marker.prototype._esterenRoutesStart = null;
    L.Marker.prototype._esterenRoutesEnd = null;
    L.Marker.prototype._sidebar = null;
    L.Marker.prototype._sidebarContent = '';
    L.Marker.prototype._clickedTime = 0;

    L.Marker.prototype.showSidebar = function(content){
        if (!this._sidebar) {
            return;
        }

        var mapOptions = this._esterenMap._mapOptions;
        var tab = this._sidebar.getTab(mapOptions.sidebarInfoTabId);

        if (!tab) {
            console.error('Cannot display marker information: info tab is not available.');
            return;
        }

        tab.setContent(content || this._sidebarContent);
        tab.focus();
    };

    L.Marker.prototype.hideSidebar = function(){
        if (!this._sidebar) {
            return;
        }
        this._sidebar.hide();
        this._sidebar.setContent('');
    };

    L.Marker.prototype.bindSidebar = function(sidebar, content){
        this._sidebar = sidebar;
        this._sidebarContent = content;
    };

    L.Marker.prototype.updateIcon = function(){
        var markerType = this._esterenMap.reference('markers_types', this._esterenMarker.marker_type);
        if (!markerType) {
            throw 'Undefined marker id '+this._esterenMarker.marker_type;
        }

        // Change icon image
        this._icon.src = markerType.icon;

        // Update "data" attribute for filters
        this._icon.setAttribute('data-leaflet-object-type', 'markerType'+markerType.id);
    };

    L.Marker.prototype.disableEditMode = function() {
        this.dragging.disable();
        this._icon.classList.remove('selected');
    };

    L.Marker.prototype.refreshRoutes = function() {
        var
            id = this._esterenMarker.id,
            latlng = this.getLatLng(),
            polylines = this._esterenMap._polylines,
            polyline, polylineLatLng, i
        ;
        for (i in polylines) {
            if (!polylines.hasOwnProperty(i)) { continue; }
            polyline = polylines[i];
            polylineLatLng = polyline.getLatLngs();
            if (polyline._esterenRoute.marker_start === id) {
                polylineLatLng[0] = L.latLng(latlng.lat, latlng.lng);
            } else if (polyline._esterenRoute.marker_end === id) {
                polylineLatLng[polylineLatLng.length - 1] = L.latLng(latlng.lat, latlng.lng);
            } else {
                continue;
            }
            polyline.setLatLngs(polylineLatLng);
        }
    };

    L.Marker.prototype._delete = function () {
        var esterenMarker = EsterenMap.prototype.cloneObject.call(null, this._esterenMarker),
            _this = this,
            callbackMessage = '',
            callbackMessageType = 'success',
            msg = CONFIRM_DELETE || 'Supprimer ?',
            id = esterenMarker.id || null
        ;

        if (!confirm(msg)) {
            return;
        }

        if (this.launched) {
            return;
        }

        d.querySelector('#esterenmap_sidebar button[data-delete][data-delete-marker]').classList.add('disabled');
        d.querySelector('#esterenmap_sidebar button[data-delete][data-delete-marker] .progress').classList.add('active');

        if (esterenMarker && this._map && !this.launched) {
            this.launched = true;
            this._esterenMap._load({
                url: this._esterenMap._mapOptions.apiUrls.endpoint.replace(/\/$/, '')+"/markers" + (id ? '/'+id : ''),
                method: 'DELETE',
                data: esterenMarker.id,
                callback: function() {
                    var map = this, marker = map._markers[esterenMarker.id];
                    marker.disableEditMode();
                    this._sidebar.getTab(this._mapOptions.sidebarInfoTabId).setContent('');
                    map._map.removeLayer(marker);
                    map._markers[esterenMarker.id] = null;
                    map._editedMarker = null;
                    delete map._markers[esterenMarker.id];
                    callbackMessage = 'Marker: ' + esterenMarker.id + ' - ' + esterenMarker.name;
                },
                callbackError: function() {
                    var msg = 'Could not make a request to '+(id?'update':'insert')+' a marker.';
                    console.error(msg);
                    callbackMessage = msg;
                    callbackMessageType = 'error';
                },
                callbackComplete: function(){
                    _this.launched = false;
                    if (callbackMessage) {
                        _this._esterenMap.message(callbackMessage, callbackMessageType);
                    }
                }
            });
        } else if (!this.launched) {
            console.error('Tried to update an empty marker.');
        }
    };

    L.Marker.prototype._updateEM = function() {
        var baseMarker = this,
            esterenMarker = EsterenMap.prototype.cloneObject.call(null, this._esterenMarker),
            _this = this,
            callbackMessage = '',
            callbackMessageType = 'success',
            id = esterenMarker.id || null
        ;

        if (this.launched) {
            return;
        }

        d.querySelector('#esterenmap_sidebar button[data-save][data-save-marker]').classList.add('disabled');
        d.querySelector('#esterenmap_sidebar button[data-save][data-save-marker] .progress').classList.add('active');

        if (esterenMarker && this._map && !this.launched) {
            this.launched = true;
            var isNote = this._esterenMap._mapOptions.canAddNotes && (
                !esterenMarker.id
                || esterenMarker.isNoteFrom === this._esterenMap._mapOptions.visitor
            );
            this._esterenMap._load({
                url: this._esterenMap._mapOptions.apiUrls.endpoint.replace(/\/$/, '')+"/markers" + (id ? '/'+id : ''),
                method: 'POST',
                data: {
                    name: esterenMarker.name,
                    description: esterenMarker.description,
                    altitude: esterenMarker.altitude || 0.0,
                    latitude: esterenMarker.latitude,
                    longitude: esterenMarker.longitude,
                    faction: esterenMarker.faction,
                    map: this._esterenMap._mapOptions.id,
                    markerType: esterenMarker.marker_type,
                    isNote: isNote
                },
                callback: function(response) {
                    var map = this,
                        msg,
                        marker = response
                    ;
                    if (marker && marker.id) {
                        map._markers[marker.id] = baseMarker;
                        map._markers[marker.id]._esterenMarker = {
                            id: marker.id,
                            name: marker.name,
                            description: marker.description,
                            altitude: marker.altitude,
                            latitude: marker.latitude,
                            longitude: marker.longitude,
                            faction: marker.faction,
                            map: marker.map,
                            marker_type: marker.markerType
                        };
                        map._markers[marker.id].updateIcon();
                        callbackMessage = 'Marker: ' + marker.id + ' - ' + marker.name;
                    } else {
                        msg = 'Api returned an error while attempting to '+(id?'update':'insert')+' a marker.';
                        console.error(msg);
                        callbackMessage = msg + '<br>' + (response ? response.toString() : 'Unknown error...');
                        callbackMessageType = 'danger';
                    }
                },
                callbackError: function() {
                    var msg = 'Could not make a request to '+(id?'update':'insert')+' a marker.';
                    console.error(msg);
                    callbackMessage = msg;
                    callbackMessageType = 'error';
                },
                callbackComplete: function(){
                    _this.launched = false;
                    d.querySelector('#esterenmap_sidebar button[data-save][data-save-marker]').classList.remove('disabled');
                    d.querySelector('#esterenmap_sidebar button[data-save][data-save-marker] .progress').classList.remove('active');
                    if (callbackMessage) {
                        _this._esterenMap.message(callbackMessage, callbackMessageType);
                    }
                }
            });
        } else if (!this.launched) {
            console.error('Tried to update an empty marker.');
        }
    };

    EsterenMap.prototype.esterenMarkerPrototype = {
        id: null,
        name: null,
        description: null,
        marker_type: null,
        faction: null,
        latitude: null,
        longitude: null
    };

    /**
     * @this {EsterenMap}
     */
    EsterenMap.prototype.renderMarkers = function(){
        var markers, i, marker,
            mapOptions = this._mapOptions,
            baseOptions = mapOptions.CustomMarkerBaseOptions,
            leafletOptions = mapOptions.LeafletMarkerBaseOptions,
            options, coords
        ;

        if (mapOptions.editMode === true) {
            baseOptions = this.cloneObject(baseOptions, mapOptions.CustomMarkerBaseOptionsEditMode);
            leafletOptions = this.cloneObject(leafletOptions, mapOptions.LeafletMarkerBaseOptionsEditMode);
        }

        for (i in this._markers) {
            if (this._markers.hasOwnProperty(i)) {
                this._map.removeLayer(this._markers[i]);
                this._markers[i].remove();
                delete this._markers[i];
            }
        }

        markers = mapOptions.data.map.markers;
        if (markers) {
            for (i in markers) {
                var markerOptions = baseOptions;
                var markerLeafletOptions = leafletOptions;
                if (!markers.hasOwnProperty(i)) { continue; }
                marker = markers[i];
                coords = {
                    lat: marker.latitude,
                    lng: marker.longitude,
                    altitude: marker.altitude
                };

                if (mapOptions.canAddNotes && marker.is_note_from && marker.is_note_from === mapOptions.visitor) {
                    markerOptions = this.cloneObject(markerOptions, mapOptions.CustomMarkerBaseOptionsEditMode);
                    markerLeafletOptions = this.cloneObject(markerLeafletOptions, mapOptions.LeafletMarkerBaseOptionsEditMode);
                }

                options = this.cloneObject(markerOptions);

                options.esterenMarker = marker;
                options.markerName = marker.name;
                options.markerType = marker.marker_type;
                options.markerFaction = marker.faction ? marker.faction : '';

                this.addMarker(coords, markerLeafletOptions, options);
            }
        }
    };

    EsterenMap.prototype._mapOptions.LeafletMarkerBaseOptions = {
        riseOnHover: true,
        draggable: false
    };

    EsterenMap.prototype._mapOptions.LeafletMarkerBaseOptionsEditMode = {
        draggable: false
    };

    EsterenMap.prototype._mapOptions.LeafletIconBaseOptions = {
        shadowUrl: '',
        shadowRetinaUrl: ''
    };

    EsterenMap.prototype._mapOptions.CustomMarkerBaseOptions = {
        popupIsSidebar: false,
        isNote: false,
        clickCallback: function(e){
            var marker = e.target,
                esterenMarker = marker._esterenMarker
            ;

            if (marker._sidebar) {
                marker.toggle();
                if (marker._sidebar.isVisible()) {
                    d.getElementById('marker_popup_name').innerHTML = esterenMarker.name;
                    d.getElementById('marker_popup_type').innerHTML = esterenMarker.marker_type.name;
                    d.getElementById('marker_popup_faction').innerHTML = esterenMarker.faction ? esterenMarker.faction.name : '';
                }
            }
        }
    };

    EsterenMap.prototype._mapOptions.CustomMarkerBaseOptionsEditMode = {
        popupIsSidebar: true,
        isNote: false,
        clickCallback: function(e){
            var marker = e.target,
                map = marker._esterenMap,
                esterenMarker = marker._esterenMarker,
                id = esterenMarker.id || marker.options.alt, clickedTime
            ;

            clickedTime = Date.now();
            if (this._clickedTime && clickedTime - this._clickedTime < 500) {
                marker._delete();
                e.stopPropagation();
            }
            this._clickedTime = clickedTime;

            map.disableEditedElements();
            marker.dragging.enable();
            marker.showSidebar();
            marker._icon.classList.add('selected');
            map._editedMarker = marker;

            if (esterenMarker) {
                d.getElementById('api_markers_name').value = esterenMarker.name;
                d.getElementById('api_markers_markerType').value = esterenMarker.marker_type ? (esterenMarker.marker_type.id ? esterenMarker.marker_type.id : esterenMarker.marker_type) : '';
                d.getElementById('api_markers_faction').value = esterenMarker.faction ? (esterenMarker.faction.id ? esterenMarker.faction.id : esterenMarker.faction) : '';

                $('#api_markers_name').off('keyup').on('keyup', function(){
                    map._markers[id]._esterenMarker.name = this.value;
                    return false;
                });
                $('#api_markers_markerType').off('change').on('change', function(){
                    map._markers[id]._esterenMarker.marker_type = map.reference('markers_types', this.value).id;
                    return false;
                });
                $('#api_markers_faction').off('change').on('change', function(){
                    map._markers[id]._esterenMarker.faction = map.reference('factions', this.value).id;
                    return false;
                });
                d.querySelector('#esterenmap_sidebar button[data-save][data-save-marker]').addEventListener('click', function(){
                    map._markers[id]._updateEM();
                });
                d.querySelector('#esterenmap_sidebar button[data-delete][data-delete-marker]').addEventListener('click', function(){
                    map._markers[id]._delete();
                });
            }

            var sidebarInfoTab = map._sidebar.getTab(map._mapOptions.sidebarInfoTabId).getAsElement();
            M.FormSelect.init(sidebarInfoTab.querySelectorAll('select'));
        },
        dragCallback: function(e) {
            var marker = e.target;
            marker.refreshRoutes();
        },
        dragendCallback: function(e) {
            var marker = e.target,
                latlng = marker.getLatLng();
            if (marker._esterenMarker) {
                marker._esterenMarker.latitude = latlng.lat;
                marker._esterenMarker.longitude = latlng.lng;
            }
        },
        addCallback: function(e){
            var marker = e.target,
                id = marker.options.alt;
            if (marker._esterenMap.editMode === true && id) {
                if (d.getElementById('marker_'+id+'_deleted')) {
                    d.getElementById('marker_'+id+'_deleted').value = 'false';
                } else {
                    $('<input type="hidden" value="false" />')
                        .attr({
                            'id':'marker_'+id+'_deleted',
                            'name':'marker['+id+'][deleted]'
                        }).appendTo('#inputs_container');
                }
            }
        }
    };

    /**
     * Ajoute un marqueur à la carte
     * @param latLng
     * @param leafletUserOptions
     * @param customUserOptions
     * @returns {EsterenMap}
     */
    EsterenMap.prototype.addMarker = function(latLng, leafletUserOptions, customUserOptions) {
        var mapOptions = this._mapOptions,
            leafletOptions = this.cloneObject(mapOptions.LeafletMarkerBaseOptions),
            iconOptions = this.cloneObject(mapOptions.LeafletIconBaseOptions),
            parser = new DOMParser(),
            id,option,icon,iconHeight,iconWidth,initialIconHeight,initialIconWidth,
            marker,popup,popupContent,popupOptions, markerType, doc;

        // Safety to be sure arguments are at least plain objects
        //   to avoid "cannot read property ... of undefined" errors.
        leafletUserOptions = leafletUserOptions || {};
        customUserOptions = customUserOptions || {};
        latLng = latLng || L.latLng(0, 0); // Default latlng to avoid problems

        // Merge Leaflet options
        if (leafletUserOptions) {
            leafletOptions = this.cloneObject(leafletOptions, leafletUserOptions);
        }

        // Merge EsterenMaps options
        if (customUserOptions.icon) {
            iconOptions = this.cloneObject(iconOptions, customUserOptions.icon);
        }

        while (d.getElementById('marker_'+this._mapOptions.maxMarkerId+'_name')) {
            this._mapOptions.maxMarkerId ++;
        }

        // Alt should contain the markers' ID
        if (!customUserOptions.esterenMarker || !customUserOptions.esterenMarker.id) {
            id = this._mapOptions.maxMarkerId;
        } else {
            id = customUserOptions.esterenMarker ? customUserOptions.esterenMarker.id : leafletUserOptions.alt;
        }
        while (d.getElementById('marker_'+id+'_name')) { id ++; }

        leafletOptions.alt = id;

        marker = L.marker(latLng, leafletOptions);

        marker._esterenMap = this;
        if (customUserOptions.esterenMarker) {
            marker._esterenMarker = customUserOptions.esterenMarker;
        } else if (mapOptions.editMode || mapOptions.canAddNotes) {
            // Let's try to create a new marker object, but only for edit mode
            marker._esterenMarker = this.esterenMarkerPrototype;
            marker._esterenMarker.marker_type = this.reference('markers_types', 1).id;
        }

        marker._esterenMarker.latitude = latLng.lat;
        marker._esterenMarker.longitude = latLng.lng;

        if (marker._esterenMarker.marker_type) {
            markerType = this.reference('markers_types', marker._esterenMarker.marker_type);
        } else {
            // Use first marker type as default.
            for (var type in mapOptions.data.references.markers_types) {
                if (!mapOptions.data.references.markers_types.hasOwnProperty(type)) continue;

                markerType = mapOptions.data.references.markers_types[type];
                break;
            }
        }

        if (!markerType) {
            throw 'Undefined marker id '+marker._esterenMarker.marker_type;
        }

        // Create a popup
        popupContent = (mapOptions.editMode || customUserOptions.isNote || (marker._esterenMarker.is_note_from && marker._esterenMarker.is_note_from === mapOptions.visitor))
            ? mapOptions.data.templates.LeafletPopupMarkerEditContent
            : mapOptions.data.templates.LeafletPopupMarkerBaseContent
        ;

        if (popupContent && typeof popupContent === 'string') {
            popupOptions = this.cloneObject(mapOptions.LeafletPopupBaseOptions || {}, customUserOptions.popupOptions);
            if (customUserOptions.popupIsSidebar === true) {
                marker.bindSidebar(this._sidebar, popupContent);
            } else {
                popup = L.popup(popupOptions);
                doc = parser.parseFromString(popupContent, 'text/html');
                doc.getElementById('marker_popup_name').innerHTML = marker._esterenMarker.name || '';
                doc.getElementById('marker_popup_type').innerHTML = this.reference('markers_types', marker._esterenMarker.marker_type).name;
                marker._esterenMarker.faction
                    ? doc.getElementById('marker_popup_faction').innerHTML = this.reference('factions', marker._esterenMarker.faction).name
                    : doc.getElementById('marker_popup_faction').parentElement.innerHTML = '';
                popup.setContent(doc.querySelector('body').innerHTML);
                marker.bindPopup(popup);
            }
        } else if (customUserOptions.popupContent && typeof customUserOptions.popupContent !== 'string') {
            throw 'popupContent parameter must be a string.';
        }

        // Add custom event listeners
        for (option in customUserOptions) {
            if (customUserOptions.hasOwnProperty(option) && option.match(/Callback$/)) {
                marker.addEventListener(option.replace('Callback',''), customUserOptions[option]);
            }
        }

        // Add the icon
        if (markerType && markerType.icon) {
            iconOptions.iconUrl = markerType.icon;

            initialIconWidth = markerType.icon_width;
            initialIconHeight = markerType.icon_height;
            iconWidth = initialIconWidth;
            iconHeight = initialIconHeight;
            if (iconWidth || iconHeight) {
                // N'applique une icône QUE si la hauteur ou la largeur sont définies

                if (!iconWidth) {
                    // Calcule la largeur de l'icône à partir du ratio largeur/largeur_icone si celle-ci n'est pas définie
                    iconWidth = parseInt(initialIconWidth / (initialIconHeight / iconHeight));
                }
                if (!iconHeight) {
                    // Calcule la hauteur de l'icône à partir du ratio largeur/largeur_icone si celle-ci n'est pas définie
                    iconHeight = parseInt(initialIconHeight / (initialIconWidth / iconWidth));
                }

                iconOptions.iconSize = [iconWidth, iconHeight];

                iconOptions.iconAnchor = [
                    markerType.icon_center_x ? markerType.icon_center_x : (iconWidth / 2),
                    markerType.icon_center_y ? markerType.icon_center_y : (iconHeight / 2)
                ];

                iconOptions.popupAnchor = [
                    0,
                    - (iconHeight / 2)
                ];

                icon = L.icon(iconOptions);
                marker.setIcon(icon);
            }
        }

        marker.addTo(this._map);

        option = 'markerType'+(customUserOptions.markerType?customUserOptions.markerType:'1');
        if (marker._icon.dataset) {
            marker._icon.dataset.leafletObjectType = option;
        }
        marker._icon.setAttribute('data-leaflet-object-type', option);
        marker._icon.setAttribute('data-leaflet-marker-id', id);

        marker._esterenRoutesStart = {};
        marker._esterenRoutesEnd = {};

        this._markers[id] = marker;

        return this;
    };

})(jQuery, L, document, window);
