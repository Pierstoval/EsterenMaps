(function($, L, d, w, M){

    // Rajoute qqs attributs à des éléments de Leaflet et LeafletSidebar
    L.Polygon.prototype._esterenMap = {};
    L.Polygon.prototype._esterenZone = {};
    L.Polygon.prototype._sidebar = null;
    L.Polygon.prototype._sidebarContent = '';
    L.Polygon.prototype.options.editing = null;

    L.Polygon.prototype.showSidebar = function(content){
        if (!this._sidebar) {
            console.warn('No sidebar to show');

            return;
        }

        var mapOptions = this._esterenMap._mapOptions;
        var tab = this._sidebar.getTab(mapOptions.sidebarInfoTabId);

        if (!tab) {
            console.error('Cannot display zone information: info tab is not available.');
            return;
        }

        tab.setContent(content || this._sidebarContent);
        tab.focus();
    };

    L.Polygon.prototype.hideSidebar = function(){
        this._sidebar.hide();
        this._sidebar.setContent('');
    };

    L.Polygon.prototype.bindSidebar = function(sidebar, content){
        this._sidebar = sidebar;
        this._sidebarContent = content;
    };

    L.Polygon.prototype.disableEditMode = function() {
        this.editing.disable();
    };

    L.Polygon.prototype._delete = function () {
        var esterenZone = EsterenMap.prototype.cloneObject.call(null, this._esterenZone),
            _this = this,
            callbackMessage = '',
            callbackMessageType = 'success',
            msg = CONFIRM_DELETE || 'Supprimer ?',
            id = esterenZone.id || null
        ;

        if (!confirm(msg)) {
            return;
        }

        if (this.launched) {
            return;
        }

        d.querySelector('#esterenmap_sidebar button[data-delete][data-delete-zone]').classList.add('disabled');
        d.querySelector('#esterenmap_sidebar button[data-delete][data-delete-zone] .progress').classList.add('active');

        if (esterenZone && this._map && !this.launched) {
            this.launched = true;
            this._esterenMap._load({
                url: this._esterenMap._mapOptions.apiUrls.endpoint.replace(/\/$/, '')+"/zones" + (id ? '/'+id : ''),
                method: 'DELETE',
                data: esterenZone.id,
                callback: function() {
                    var map = this, zone = map._polygons[esterenZone.id];
                    zone.disableEditMode();
                    this._sidebar.getTab(this._mapOptions.sidebarInfoTabId).setContent('');
                    map._map.removeLayer(zone);
                    map._polygons[esterenZone.id] = null;
                    map._editedZone = null;
                    delete map._polygons[esterenZone.id];
                    callbackMessage = 'Zone: ' + esterenZone.id + ' - ' + esterenZone.name;
                },
                callbackError: function() {
                    var msg = 'Could not make a request to '+(id?'update':'insert')+' a zone.';
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
            console.error('Tried to update an empty zone.');
        }
    };

    L.Polygon.prototype.updateDetails = function() {
        var zone_type,
            esterenZone = this._esterenZone,
            id = esterenZone.id
        ;

        zone_type = this._esterenMap.reference('zones_types', esterenZone.zone_type);

        if (zone_type.color) {
            // Change l'image de l'icône
            this._path.setAttribute('stroke', zone_type.color);
        }

        // Met à jour l'attribut "data" pour les filtres
        $(this._path).attr('data-leaflet-object-type', 'zoneType'+esterenZone.zone_type);
        $(this._path).attr('id', 'polygon_'+id+'_name');
    };

    L.Polygon.prototype._updateEM = function() {
        var baseZone = EsterenMap.prototype.cloneObject.call(null, this),
            _this = this,
            callbackMessage = '',
            callbackMessageType = 'success',
            esterenZone = this._esterenZone || null,
            id = esterenZone.id || null
        ;

        if (this.launched) {
            return;
        }

        d.querySelector('#esterenmap_sidebar button[data-save][data-save-zone]').classList.add('disabled');
        d.querySelector('#esterenmap_sidebar button[data-save][data-save-zone] .progress').classList.add('active');

        if (esterenZone && this._map && !this.launched) {
            this.launched = true;
            var isNote = this._esterenMap._mapOptions.canAddNotes && (
                !esterenZone.id
                || esterenZone.isNoteFrom === this._esterenMap._mapOptions.visitor
            );
            this._esterenMap._load({
                url: this._esterenMap._mapOptions.apiUrls.endpoint.replace(/\/$/, '')+"/zones" + (id ? '/'+id : ''),
                method: 'POST', // Si on n'a pas d'ID, c'est qu'on crée une nouvelle zone
                data: {
                    map: this._esterenMap._mapOptions.id,
                    name: esterenZone.name,
                    description: esterenZone.description,
                    coordinates: JSON.stringify(this.getLatLngs() ? this._latlngs : {}),
                    zoneType: esterenZone.zone_type,
                    faction: esterenZone.faction ? esterenZone.faction : null,
                    isNote: isNote
                },
                callback: function(response) {
                    var map = this,
                        msg,
                        zone = response
                    ;
                    if (zone && zone.id) {
                        // New object is available
                        map._polygons[zone.id] = baseZone;
                        map._polygons[zone.id]._esterenZone = {
                            id: zone.id,
                            name: zone.name,
                            description: zone.description,
                            coordinates: zone.coordinates,
                            map: zone.map,
                            zone_type: zone.zoneType,
                            faction: zone.faction,
                        };
                        map._polygons[zone.id].updateDetails();
                        callbackMessage = 'Zone: ' + zone.id + ' - ' + zone.name;
                    } else {
                        msg = 'Api returned an error while attempting to '+(id?'update':'insert')+' a zone.';
                        console.error(msg);
                        callbackMessage = msg + '<br>' + (response ? response.toString() : 'Unknown error...');
                        callbackMessageType = 'danger';
                    }
                },
                callbackError: function() {
                    var msg = 'Could not make a request to '+(id?'update':'insert')+' a zone.';
                    console.error(msg);
                    callbackMessage = msg;
                    callbackMessageType = 'error';
                },
                callbackComplete: function(){
                    _this.launched = false;
                    d.querySelector('#esterenmap_sidebar button[data-save][data-save-zone]').classList.remove('disabled');
                    d.querySelector('#esterenmap_sidebar button[data-save][data-save-zone] .progress').classList.remove('active');
                    if (callbackMessage) {
                        _this._esterenMap.message(callbackMessage, callbackMessageType);
                    }
                }
            });
        } else if (!this.launched) {
            console.error('Tried to update an empty zone.');
        }
    };

    EsterenMap.prototype.esterenZonePrototype = {
        id: null,
        name: null,
        zone_type: null,
        faction: null,
        coordinates: null
    };

    EsterenMap.prototype._mapOptions.LeafletPolygonBaseOptions = {
        color: "#fff",
        opacity: 0.3,
        fillColor: '#eee',
        fillOpacity: 0.1,
        weight: 2,
        clickable: false
    };

    EsterenMap.prototype._mapOptions.LeafletPolygonBaseOptionsEditMode = {
        opacity: 0.5,
        clickable: true
    };

    EsterenMap.prototype._mapOptions.CustomPolygonBaseOptions = {
        popupIsSidebar: true,
        clickCallback: function(e){
            var polygon = e.target,
                parser = new DOMParser(),
                esterenZone = polygon._esterenZone,
                content
            ;

            if (polygon._sidebar) {
                content = parser.parseFromString(this._sidebarContent, 'text/html');

                content.getElementById('polygon_popup_name').innerHTML = esterenZone.name;
                content.getElementById('polygon_popup_type').innerHTML = this._esterenMap.reference('zones_types', esterenZone.zone_type, {name:''}).name;
                if (esterenZone.faction) {
                    content.getElementById('polygon_popup_faction').innerHTML = this._esterenMap.reference('factions', esterenZone.faction, {name:''}).name;
                }

                polygon.showSidebar(content.querySelector('body').innerHTML);
            }

            L.DomEvent.stopPropagation(e);
            L.DomEvent.preventDefault(e);

            return false;
        }
    };

    EsterenMap.prototype._mapOptions.CustomPolygonBaseOptionsEditMode = {
        clickCallback: function(e){
            var polygon = e.target,
                map = polygon._esterenMap,
                id = polygon.options.className.replace('drawn_polygon_','')
            ;

            map.disableEditedElements();

            if (polygon.editing.enabled()) {
                return;
            }

            polygon.editing.enable();
            polygon.showSidebar();

            map._editedPolygon = polygon;

            $('#api_zone_name')
                .val(polygon._esterenZone.name)
                .off('keyup').on('keyup', function(){
                    map._polygons[id]._esterenZone.name = this.value;
                    return false;
                })
            ;
            $('#api_zone_zoneType')
                .val(polygon._esterenZone.zone_type ? polygon._esterenZone.zone_type : '')
                .off('change').on('change', function(){
                    var zone_type = map.reference('zones_types', this.value);
                    map._polygons[id]._esterenZone.zone_type = zone_type ? zone_type.id : null;
                    return false;
                })
            ;
            $('#api_zone_faction')
                .val(polygon._esterenZone.faction ? polygon._esterenZone.faction : '')
                .off('change').on('change', function(){
                    var faction = map.reference('factions', this.value);
                    map._polygons[id]._esterenZone.faction = faction ? faction.id : null;
                    return false;
                })
            ;
            d.querySelector('#esterenmap_sidebar button[data-save][data-save-zone]').addEventListener('click', function(){
                map._polygons[id]._updateEM();
            });
            d.querySelector('#esterenmap_sidebar button[data-delete][data-delete-zone]').addEventListener('click', function(){
                map._polygons[id]._delete();
            });

            d.getElementById('api_zone_name').focus();

            var sidebarInfoTab = map._sidebar.getTab(map._mapOptions.sidebarInfoTabId).getAsElement();
            M.FormSelect.init(sidebarInfoTab.querySelectorAll('select'));
        },
        dblclickCallback: function(e){
            var polygon = e.target,
                msg = CONFIRM_DELETE || 'Supprimer ?',
                id = polygon._esterenZone ? polygon._esterenZone.id : null;
            if (polygon._esterenMap._mapOptions.editMode === true && id) {
                if (confirm(msg)) {
                    polygon._map.removeLayer(polygon);
                    polygon.fire('remove');
                }
            }

            e.preventDefault();
            e.stopPropagation();
        }
    };

    EsterenMap.prototype.renderZones = function() {
        var zones, i, zone, zonesToDisplay, hasToDisplaySpecificZones,
            mapOptions = this._mapOptions,
            baseOptions = mapOptions.CustomPolygonBaseOptions,
            leafletOptions = mapOptions.LeafletPolygonBaseOptions,
            zone_type, coords
        ;

        for (i in this._polygons) {
            if (!this._polygons.hasOwnProperty(i)) { continue; }
            this._map.removeLayer(this._polygons[i]);
            this._polygons[i].remove();
        }

        if (mapOptions.editMode === true) {
            baseOptions = this.cloneObject(baseOptions, mapOptions.CustomPolygonBaseOptionsEditMode);
            leafletOptions = this.cloneObject(leafletOptions, mapOptions.LeafletPolygonBaseOptionsEditMode);
        }

        zones = mapOptions.data.map.zones;
        zonesToDisplay = mapOptions.zonesToDisplay;
        hasToDisplaySpecificZones = zonesToDisplay.length > 0;

        if (zones) {
            for (i in zones) {
                if (!zones.hasOwnProperty(i)) { continue; }
                zone = zones[i];
                if (hasToDisplaySpecificZones && zonesToDisplay.indexOf(parseInt(zone.id, 10)) === -1) {
                    continue;
                }
                var zoneOptions = baseOptions;
                var zoneLeafletOptions = leafletOptions;
                coords = zone.coordinates;
                zoneLeafletOptions = this.cloneObject(leafletOptions, {id:zone.id});

                zone_type = this.reference('zones_types', zone.zone_type);

                if (zone_type.color) {
                    zoneLeafletOptions.color = zone_type.color;
                    zoneLeafletOptions.fillColor = zone_type.color;
                }

                if (mapOptions.canAddNotes && zone.is_note_from && zone.is_note_from === mapOptions.visitor) {
                    zoneOptions = this.cloneObject(zoneOptions, mapOptions.CustomPolygonBaseOptionsEditMode);
                    zoneLeafletOptions = this.cloneObject(zoneLeafletOptions, mapOptions.LeafletPolygonBaseOptionsEditMode);
                }

                zoneOptions = this.cloneObject(zoneOptions, {
                    esterenZone: zone,
                    polygonName: zone.name,
                    polygonType: zone.zone_type,
                    polygonFaction: zone.faction
                });

                this.addPolygon(coords,
                    zoneLeafletOptions,
                    zoneOptions
                );
            }//endfor
        }// endif response
    };

    /**
     * @param latLng
     * @param leafletUserOptions
     * @param customUserOptions
     * @returns {EsterenMap}
     */
    EsterenMap.prototype.addPolygon = function(latLng, leafletUserOptions, customUserOptions) {
        var mapOptions = this._mapOptions,
            className,
            id,
            option,
            leafletOptions = mapOptions.LeafletPolygonBaseOptions,
            polygon,popupContent;

        if (leafletUserOptions) {
            leafletOptions = this.cloneObject(leafletOptions, leafletUserOptions);
        }

        while (d.getElementById('polygon_'+this._mapOptions.maxPolygonId+'_name')) {
            this._mapOptions.maxPolygonId ++;
        }

        if (!leafletOptions.id) {
            id = this._mapOptions.maxPolygonId;
        } else {
            id = leafletOptions.id;
        }

        while (d.getElementById('polygon_'+id+'_name')) { id ++; }

        className = 'drawn_polygon_'+id;

        leafletOptions.className = className;

        // Fix for Leaflet-draw searching for this property when using edit mode
        if (!leafletOptions.editing) {
            leafletOptions.editing = {};
        }
        // Same here: Leaflet-draw needs this property, and it's not correctly set in base class...
        if (!leafletOptions.original) {
            leafletOptions.original = {};
        }
        leafletOptions.editing.className = leafletOptions.className;

        polygon = L.polygon(latLng, leafletOptions);

        polygon._esterenMap = this;
        if (customUserOptions.esterenZone) {
            polygon._esterenZone = customUserOptions.esterenZone;
        } else {
            // Ici on tente de créer une nouvelle zone
            polygon._esterenZone = this.esterenZonePrototype;
            polygon._esterenZone.zone_type = 1;
        }

        popupContent = (mapOptions.editMode || customUserOptions.isNote || (polygon._esterenZone.is_note_from && polygon._esterenZone.is_note_from === mapOptions.visitor))
            ? mapOptions.data.templates.LeafletPopupPolygonEditContent
            : mapOptions.data.templates.LeafletPopupPolygonBaseContent
        ;

        if (popupContent && typeof popupContent === 'string') {
            polygon.bindSidebar(this._sidebar, popupContent);
        } else if (customUserOptions.popupContent && typeof customUserOptions.popupContent !== 'string') {
            console.error('popupContent parameter must be a string.');
        }

        if (popupContent && typeof popupContent === 'string') {
            polygon.bindSidebar(this._sidebar, popupContent);
        } else if (customUserOptions.popupContent && typeof customUserOptions.popupContent !== 'string') {
            throw 'Popup content must be a string.';
        }

        // Application des events listeners
        for (option in customUserOptions) {
            if (customUserOptions.hasOwnProperty(option) && option.match(/Callback$/)) {
                polygon.addEventListener(option.replace('Callback',''), customUserOptions[option]);
            }
        }

        polygon.addTo(this._map);

        option = 'zoneType'+(typeof polygon._esterenZone.zone_type === 'object' ? polygon._esterenZone.zone_type.id : polygon._esterenZone.zone_type);
        polygon._path.setAttribute('data-leaflet-object-type', option);

        this._polygons[id] = polygon;

        return this;
    };
})(jQuery, L, document, window, M);
