(function($, L, d, w){

    // Rajoute qqs attributs à des éléments de Leaflet et LeafletSidebar
    L.Polyline.prototype._esterenMap = {};
    L.Polyline.prototype._esterenRoute = {};
    L.Polyline.prototype._markerStart = null;
    L.Polyline.prototype._markerEnd = null;
    L.Polyline.prototype._sidebar = null;
    L.Polyline.prototype._sidebarContent = '';
    L.Polyline.prototype._oldColor = '';
    L.Polyline.prototype.options.editing = null;

    L.Polyline.prototype.showSidebar = function(content){
        if (!this._sidebar) {
            console.warn('No sidebar to show');

            return;
        }

        var mapOptions = this._esterenMap._mapOptions;
        var tab = this._sidebar.getTab(mapOptions.sidebarInfoTabId);

        if (!tab) {
            console.error('Cannot display route information: info tab is not available.');
            return;
        }

        tab.setContent(content || this._sidebarContent);
        tab.focus();
    };

    L.Polyline.prototype.hideSidebar = function(){
        this._sidebar.hide();
        this._sidebar.setContent('');
        return this;
    };

    L.Polyline.prototype.bindSidebar = function(sidebar, content){
        this._sidebar = sidebar;
        this._sidebarContent = content;
        return this;
    };

    L.Polyline.prototype.disableEditMode = function() {
        this.editing.disable();
        this.updateDetails();
    };

    L.Polyline.prototype._delete = function () {
        var esterenRoute = EsterenMap.prototype.cloneObject.call(null, this._esterenRoute),
            _this = this,
            callbackMessage = '',
            callbackMessageType = 'success',
            msg = CONFIRM_DELETE || 'Supprimer ?',
            id = esterenRoute.id || null
        ;

        if (!confirm(msg)) {
            return;
        }

        if (this.launched) {
            return;
        }

        d.querySelector('#esterenmap_sidebar button[data-delete][data-delete-route]').classList.add('disabled');
        d.querySelector('#esterenmap_sidebar button[data-delete][data-delete-route] .progress').classList.add('active');

        if (esterenRoute && this._map && !this.launched) {
            this.launched = true;
            this._esterenMap._load({
                url: this._esterenMap._mapOptions.apiUrls.endpoint.replace(/\/$/, '')+"/routes" + (id ? '/'+id : ''),
                method: 'DELETE',
                data: esterenRoute.id,
                callback: function() {
                    var map = this, route = map._polylines[esterenRoute.id];
                    route.disableEditMode();
                    this._sidebar.getTab(this._mapOptions.sidebarInfoTabId).setContent('');
                    map._map.removeLayer(route);
                    map._polylines[esterenRoute.id] = null;
                    map._editedRoute = null;
                    delete map._polylines[esterenRoute.id];
                    callbackMessage = 'Route: ' + esterenRoute.id + ' - ' + esterenRoute.name;
                },
                callbackError: function() {
                    var msg = 'Could not make a request to '+(id?'update':'insert')+' a route.';
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
            console.error('Tried to update an empty route.');
        }
    };

    L.Polyline.prototype.updateDetails = function() {
        var route_type,
            esterenRoute = this._esterenRoute,
            id = esterenRoute.id,
            esterenMarkerStart = esterenRoute.marker_start,
            esterenMarkerEnd = esterenRoute.marker_end,
            markerStart = this._markerStart ? this._markerStart : (esterenMarkerStart ? this._esterenMap._markers[esterenMarkerStart.id || esterenMarkerStart] : null),
            markerEnd = this._markerEnd ? this._markerEnd : (esterenMarkerEnd ? this._esterenMap._markers[esterenMarkerEnd.id || esterenMarkerEnd] : null)
        ;

        route_type = this._esterenMap.reference('routes_types', esterenRoute.route_type);

        if (route_type.color) {
            // Change l'image de l'icône
            this._path.setAttribute('stroke', route_type.color);
        }

        // Met à jour l'attribut "data" pour les filtres
        this._path.setAttribute('data-leaflet-object-type', 'routeType'+esterenRoute.route_type);
        this._path.setAttribute('id', 'polyline_'+id+'_name');

        if (markerStart) {
            this._latlngs[0] = L.latLng(markerStart.getLatLng());
            markerStart._esterenRoutesStart[id] = this;
        }
        if (markerEnd) {
            this._latlngs[this._latlngs.length-1] = L.latLng(markerEnd.getLatLng());
            markerEnd._esterenRoutesEnd[id] = this;
        }

        this.redraw();
        if (this.editing && this.editing.enabled()) {
            this.editing.updateMarkers();
        }
    };

    L.Polyline.prototype._updateEM = function() {
        var baseRoute = this,
            esterenRoute = EsterenMap.prototype.cloneObject.call(null, this._esterenRoute || null),
            _this = this,
            callbackMessage = '',
            callbackMessageType = 'success',
            id = esterenRoute.id || null
        ;

        if (this.launched) {
            return;
        }

        if ((esterenRoute && esterenRoute.marker_start) || (esterenRoute && esterenRoute.marker_end)) {
            this.updateDetails();
        }

        d.querySelector('#esterenmap_sidebar button[data-save][data-save-route]').classList.add('disabled');
        d.querySelector('#esterenmap_sidebar button[data-save][data-save-route] .progress').classList.add('active');

        if (esterenRoute && this._map && !this.launched) {
            this.launched = true;
            var isNote = this._esterenMap._mapOptions.canAddNotes && (
                !esterenRoute.id
                || esterenRoute.isNoteFrom === this._esterenMap._mapOptions.visitor
            );
            this._esterenMap._load({
                url: this._esterenMap._mapOptions.apiUrls.endpoint.replace(/\/$/, '')+"/routes" + (id ? '/'+id : ''),
                method: 'POST',
                data: {
                    map: this._esterenMap._mapOptions.id,
                    name: esterenRoute.name,
                    description: esterenRoute.description,
                    coordinates: JSON.stringify(this._latlngs ? this._latlngs : {}),
                    routeType: esterenRoute.route_type,
                    markerStart: esterenRoute.marker_start,
                    markerEnd: esterenRoute.marker_end,
                    faction: esterenRoute.faction ? esterenRoute.faction : null,
                    guarded: !!esterenRoute.guarded,
                    forcedDistance: (isNaN(esterenRoute.forced_distance) ? null : parseInt(esterenRoute.forced_distance, 10)) || null,
                    isNote: isNote
                },
                callback: function(response) {
                    var map = _this._esterenMap,
                        msg,
                        route = response
                    ;
                    if (route && route.id) {
                        // New object is available
                        map._polylines[route.id] = baseRoute;
                        map._polylines[route.id]._esterenRoute = {
                            id: route.id,
                            name: route.name,
                            description: route.description,
                            coordinates: route.coordinates,
                            distance: route.distance,
                            forced_distance: route.forcedDistance,
                            guarded: route.guarded,
                            marker_start: route.markerStart,
                            marker_end: route.markerEnd,
                            map: route.map,
                            route_type: route.routeType,
                            faction: route.faction,
                        };
                        map._polylines[route.id].updateDetails();
                        callbackMessage = 'Route: ' + route.id + ' - ' + route.name;
                    } else {
                        msg = 'Api returned an error while attempting to '+(id?'update':'insert')+' a route.';
                        console.error(msg);
                        callbackMessage = msg + '<br>' + (response ? response.toString() : 'Unknown error...');
                        callbackMessageType = 'danger';
                    }
                },
                callbackError: function() {
                    var msg = 'Could not make a request to '+(id?'update':'insert')+' a route.';
                    console.error(msg);
                    callbackMessage = msg;
                    callbackMessageType = 'error';
                },
                callbackComplete: function(){
                    _this.launched = false;
                    d.querySelector('#esterenmap_sidebar button[data-save][data-save-route]').classList.remove('disabled');
                    d.querySelector('#esterenmap_sidebar button[data-save][data-save-route] .progress').classList.remove('active');
                    if (callbackMessage) {
                        _this._esterenMap.message(callbackMessage, callbackMessageType);
                    }
                }
            });
        } else if (esterenRoute.marker_start && esterenRoute.marker_end) {
            console.error('Tried to update an empty route.');
        }
    };

    EsterenMap.prototype.esterenRoutePrototype = {
        id: null,
        name: null,
        route_type: null,
        marker_start: null,
        marker_end: null,
        faction: null,
        distance: 0,
        forcedDistance: null,
        coordinates: null
    };

    EsterenMap.prototype._mapOptions.LeafletPolylineBaseOptions = {
        color: "#f66",
        opacity: 0.75,
        weight: 4,
        clickable: true
    };

    EsterenMap.prototype._mapOptions.LeafletPolylineBaseOptionsEditMode = {
        color: "#03f",
        opacity: 0.75,
        weight: 6
    };

    EsterenMap.prototype._mapOptions.CustomPolylineBaseOptions = {
        popupIsSidebar: true,
        clickCallback: function(e){
            var polyline = e.target,
                parser = new DOMParser(),
                esterenRoute = polyline._esterenRoute,
                content
            ;

            if (polyline._sidebar) {
                content = parser.parseFromString(this._sidebarContent, 'text/html');

                content.getElementById('polyline_popup_name').innerHTML = esterenRoute.name;
                content.getElementById('polyline_popup_type').innerHTML = this._esterenMap.reference('routes_types', esterenRoute.route_type).name;
                if (esterenRoute.marker_start) {
                    content.getElementById('polyline_popup_markerStart').innerHTML = this._esterenMap.mapReference('markers', esterenRoute.marker_start, {name: ''}).name;
                }
                if (esterenRoute.marker_end) {
                    content.getElementById('polyline_popup_markerEnd').innerHTML = this._esterenMap.mapReference('markers', esterenRoute.marker_end, {name:''}).name;
                }
                if (esterenRoute.faction) {
                    content.getElementById('polyline_popup_faction').parentElement.style.display = 'auto';
                    content.getElementById('polyline_popup_faction').innerHTML = this._esterenMap.reference('factions', esterenRoute.faction, {name:''}).name;
                } else {
                    content.getElementById('polyline_popup_faction').parentElement.style.display = 'none';
                }

                polyline.showSidebar(content.querySelector('body').innerHTML);
            }

            L.DomEvent.stopPropagation(e);
            L.DomEvent.preventDefault(e);

            return false;
        }
    };

    EsterenMap.prototype._mapOptions.CustomPolylineBaseOptionsEditMode = {
        clickCallback: function(e){
            var polyline = e.target,
                map = polyline._esterenMap,
                id = polyline.options.className.replace('drawn_polyline_',''),
                collectionStart, collectionEnd, markers, $markersStart, $markersEnd
            ;

            map.disableEditedElements();

            if (polyline.editing.enabled()) {
                return;
            }

            polyline.editing.enable();
            polyline.showSidebar();

            map._editedPolyline = polyline;

            $markersStart = $('#api_route_markerStart');
            $markersEnd = $('#api_route_markerEnd');

            markers = map._markers;
            collectionStart = $markersStart.filter('option[value!=""]');
            collectionEnd = $markersEnd.filter('option[value!=""]');

            $.each(markers, function(id,marker){
                if (!collectionEnd.filter('[value="'+id+'"]').length) {
                    collectionEnd.last().after('<option value="'+id+'">'+marker.name+'</option>');
                }
                if (!collectionStart.filter('[value="'+id+'"]').length) {
                    collectionStart.last().after('<option value="'+id+'">'+marker.name+'</option>');
                }
            });

            $('#api_route_name')
                .val(polyline._esterenRoute.name)
                .off('keyup').on('keyup', function(){
                    map._polylines[id]._esterenRoute.name = this.value;
                    return false;
                })
            ;
            $('#api_route_forcedDistance')
                .val(polyline._esterenRoute.forced_distance)
                .off('keyup').on('keyup', function(){
                    map._polylines[id]._esterenRoute.forced_distance = this.value;
                    return false;
                })
            ;
            $('#api_route_guarded')
                .val(!!polyline._esterenRoute.guarded)
                .prop('checked', !!polyline._esterenRoute.guarded)
                .off('change').on('change', function(){
                    map._polylines[id]._esterenRoute.guarded = $(this).is(':checked');
                    return false;
                })
            ;
            $('#api_route_routeType')
                .val(polyline._esterenRoute.route_type ? polyline._esterenRoute.route_type : '')
                .off('change').on('change', function(){
                    var route_type = map.reference('routes_types', this.value);
                    map._polylines[id]._esterenRoute.route_type = route_type ? route_type.id : null;
                    return false;
                })
            ;
            $('#api_route_faction')
                .val(polyline._esterenRoute.faction ? polyline._esterenRoute.faction : '')
                .off('change').on('change', function(){
                    var faction = map.reference('factions', this.value);
                    map._polylines[id]._esterenRoute.faction = faction ? faction.id : null;
                    return false;
                })
            ;
            $markersStart
                .val(polyline._esterenRoute.marker_start ? polyline._esterenRoute.marker_start : '')
                .off('change').on('change', function(){
                    var marker, latlngs;
                    marker = markers[this.value] || null;
                    latlngs = polyline._latlngs;
                    if (marker) {
                        latlngs[0] = marker._latlng;
                    }
                    polyline.setLatLngs(latlngs);
                    polyline._esterenRoute.coordinates = JSON.stringify(latlngs);
                    polyline._esterenRoute.marker_start = marker._esterenMarker;
                    return false;
                })
            ;
            $markersEnd
                .val(polyline._esterenRoute.marker_end ? polyline._esterenRoute.marker_end : '')
                .off('change').on('change', function(){
                    var marker, latlngs;
                    marker = markers[this.value] || null;
                    latlngs = polyline._latlngs;
                    if (marker) {
                        latlngs[latlngs.length - 1] = marker._latlng;
                    }
                    polyline.setLatLngs(latlngs);
                    polyline._esterenRoute.coordinates = JSON.stringify(latlngs);
                    polyline._esterenRoute.marker_end = marker._esterenMarker;
                    return false;
                })
            ;
            d.querySelector('#esterenmap_sidebar button[data-save][data-save-route]').addEventListener('click', function(){
                map._polylines[id]._updateEM();
            });
            d.querySelector('#esterenmap_sidebar button[data-delete][data-delete-route]').addEventListener('click', function(){
                map._polylines[id]._delete();
            });

            (e.preventDefault ? e : e.originalEvent).preventDefault();
            (e.stopPropagation ? e : e.originalEvent).stopPropagation();

            d.getElementById('api_route_name').focus();

            var sidebarInfoTab = map._sidebar.getTab(map._mapOptions.sidebarInfoTabId).getAsElement();
            M.FormSelect.init(sidebarInfoTab.querySelectorAll('select'));
        }
    };

    EsterenMap.prototype.renderRoutes = function() {
        var routes, i, route, route_type,
            finalLeafletOptions,
            mapOptions = this._mapOptions,
            baseOptions = mapOptions.CustomPolylineBaseOptions,
            leafletOptions = mapOptions.LeafletPolylineBaseOptions,
            coords
        ;

        for (i in this._polylines) {
            if (this._polylines.hasOwnProperty(i)) {
                this._map.removeLayer(this._polylines[i]);
                this._drawnItems.removeLayer(this._polylines[i]);
            }
        }

        if (mapOptions.editMode === true) {
            baseOptions = this.cloneObject(baseOptions, mapOptions.CustomPolylineBaseOptionsEditMode);
            leafletOptions = this.cloneObject(leafletOptions, mapOptions.LeafletPolylineBaseOptionsEditMode);
            // Fix for Leaflet-draw searching for this property when using edit mode
            if (!leafletOptions.editing) {
                leafletOptions.editing = {};
            }
            leafletOptions.editing.className = leafletOptions.className;
        }

        routes = mapOptions.data.map.routes;
        if (routes) {
            for (i in routes) {
                if (!routes.hasOwnProperty(i)) { continue; }
                var routeOptions = baseOptions;
                var routeLeafletOptions = leafletOptions;
                route = routes[i];
                coords = (typeof route.coordinates === 'string') ? JSON.parse(route.coordinates ? route.coordinates : "{}") : route.coordinates;
                finalLeafletOptions = this.cloneObject(leafletOptions, {id:route.id});

                route_type = this.reference('routes_types', route.route_type);

                if (mapOptions.canAddNotes && route.is_note_from && route.is_note_from === mapOptions.visitor) {
                    routeOptions = this.cloneObject(routeOptions, mapOptions.CustomPolylineBaseOptionsEditMode);
                    routeLeafletOptions = this.cloneObject(routeLeafletOptions, mapOptions.LeafletPolylineBaseOptionsEditMode);
                }

                routeOptions = this.cloneObject(routeOptions, {
                    esterenRoute: route,
                    polylineName: route.name,
                    polylineType: route.route_type,
                    polylineFaction: route.faction ? route.faction.id : '',
                    polylineMarkerStart: route.marker_start ? route.marker_start.id : '',
                    polylineMarkerEnd: route.marker_end ? route.marker_end.id : ''
                });

                this.addPolyline(coords, routeLeafletOptions, routeOptions);
            }//endfor
        }// endif handleResponse
    };

    EsterenMap.prototype._mapOptions.loaderCallbacks.routesTypes = function(response){
        if (response['routestypes'] && response['routestypes'].length > 0) {
            this._routesTypes = response['routestypes'];
        } else {
            console.error('Error while retrieving routes types');
        }
        return this;
    };

    /**
     * Ajoute un marqueur à la carte
     * @param latLng
     * @param leafletUserOptions
     * @param customUserOptions
     * @returns {EsterenMap}
     */
    EsterenMap.prototype.addPolyline = function(latLng, leafletUserOptions, customUserOptions) {
        var mapOptions = this._mapOptions,
            leafletOptions = mapOptions.LeafletPolylineBaseOptions,
            id, option, polyline, popupContent, routeType
        ;

        if (leafletUserOptions) {
            leafletOptions = this.cloneObject(leafletOptions, leafletUserOptions);
        }

        while (d.getElementById('polyline_'+this._mapOptions.maxPolylineId+'_name')) {
            this._mapOptions.maxPolylineId ++;
        }

        if (!leafletOptions.id) {
            id = this._mapOptions.maxPolylineId;
        } else {
            id = leafletOptions.id;
        }

        while (d.getElementById('polyline_'+id+'_name')) { id ++; }

        leafletOptions.className = 'drawn_polyline_'+id;

        if (customUserOptions.polyline) {
            // Don't recreate an existing polyline
            polyline = customUserOptions.polyline;
            polyline.initialize(latLng, leafletOptions);
        } else {
            polyline = L.polyline(latLng, leafletOptions);
        }

        polyline._esterenMap = this;
        if (customUserOptions.esterenRoute) {
            polyline._esterenRoute = customUserOptions.esterenRoute;
        } else {
            // Ici on tente de créer une nouvelle zone
            polyline._esterenRoute = this.esterenRoutePrototype;
            polyline._esterenRoute.route_type = 1;
        }

        // Création d'une popup
        popupContent = (mapOptions.editMode || customUserOptions.isNote || (polyline._esterenRoute.is_note_from && polyline._esterenRoute.is_note_from === mapOptions.visitor))
            ? mapOptions.data.templates.LeafletPopupPolylineEditContent
            : mapOptions.data.templates.LeafletPopupPolylineBaseContent
        ;

        if (popupContent && typeof popupContent === 'string') {
            polyline.bindSidebar(this._sidebar, popupContent);
        } else if (customUserOptions.popupContent && typeof customUserOptions.popupContent !== 'string') {
            console.error('popupContent parameter must be a string.');
        }

        // Application des events listeners
        for (option in customUserOptions) {
            if (customUserOptions.hasOwnProperty(option) && option.match(/Callback$/)) {
                polyline.addEventListener(option.replace('Callback',''), customUserOptions[option]);
            }
        }

        polyline.addTo(this._map);

        option = 'routeType'+polyline._esterenRoute.route_type;
        polyline._path.setAttribute('data-leaflet-object-type', option);

        routeType = this.reference('routes_types', polyline._esterenRoute.route_type);
        if (routeType.color) {
            polyline._path.setAttribute('stroke', routeType.color);
        }

        this._polylines[id] = polyline;

        return this;
    };


})(jQuery, L, document, window);
