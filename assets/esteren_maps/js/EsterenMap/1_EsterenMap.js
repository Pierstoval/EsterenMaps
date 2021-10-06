(function($, L, d, w, M){

    /**
     * @param {object} userMapOptions
     * @returns {EsterenMap}
     * @this {EsterenMap}
     * @constructor
     */
    var EsterenMap = function (userMapOptions) {
        if (!userMapOptions.id) {
            throw 'Map id must be defined';
        }

        if (userMapOptions.editMode && userMapOptions.canAddNotes) {
            throw 'Cannot enable edit mode and adding notes at the same time.';
        }

        if (!(this instanceof EsterenMap)) {
            throw 'Wrong scope check, incorrect instance.';
        }

        if (!L) {
            throw 'Leaflet must be activated.';
        }

        // Merge base options
        if (userMapOptions){
            this._mapOptions = this.cloneObject(this._mapOptions, userMapOptions);
        }

        if (!d.getElementById(this._mapOptions.container)) {
            throw 'Map could not initialize : wrong container id';
        }

        this.loadMapData();
    };

    /**
     * To be called ONLY after having loaded the settings.
     *
     * @returns {boolean}
     * @private
     */
    EsterenMap.prototype._initialize = function() {
        var sidebar, _this = this, mapOptions;

        mapOptions = this._mapOptions;

        if (this.initialized || d.initializedEsterenMap) {
            throw 'Map already initialized.';
        }

        if (!mapOptions.data.references) {
            throw 'No references were set, map cannot be initialized';
        }

        this.initialized = true;
        d.initializedEsterenMap = true;

        if (mapOptions.crs && !mapOptions.LeafletMapBaseOptions.crs && L.CRS[mapOptions.crs]) {
            mapOptions.LeafletMapBaseOptions.crs = L.CRS[mapOptions.crs];
        } else if (mapOptions.crs && !L.CRS[mapOptions.crs]) {
            console.warn('Could not find CRS "'+mapOptions.crs+'".');
        }

        // Create Leaflet map object.
        this._map = L.map(mapOptions.container, mapOptions.LeafletMapBaseOptions);

        // Create the layer that will show the tiles.
        this._tileLayer = L.tileLayer(mapOptions.apiUrls.tiles, mapOptions.LeafletLayerBaseOptions);
        this._map.addLayer(this._tileLayer);

        L.Icon.Default.imagePath = mapOptions.imgUrl.replace(/\/$/gi, '');

        // Add sidebar if configured.
        if (mapOptions.sidebarContainer) {
            this.initSidebar();
        }

        if (mapOptions.showMarkers === true) {
            // See EsterenMap_markers.js
            this.renderMarkers();
        }

        if (mapOptions.showZones === true) {
            // See EsterenMap_polygons.js
            this.renderZones();
        }

        if (mapOptions.showRoutes === true) {
            // See EsterenMap_polylines.js
            this.renderRoutes();
        }

        if (mapOptions.showFilters === true) {
            // See EsterenMap_filters.js
            this.initFilters();
        }

        if (mapOptions.showDirections === true) {
            // See EsterenMap_directions.js
            this.initDirections();
        }

        if (mapOptions.showSearchEngine === true) {
            // See EsterenMap_search_engine.js
            this.initSearch();
        }

        if (true === mapOptions.canAddNotes) {
            this.activateNotesEdition();
        }

        ////////////////////////////////
        /////////// Edit mode //////////
        ////////////////////////////////
        if (true === mapOptions.editMode) {
            this.activateEditMode();
        }

        this._mapOptions = this.cloneObject(mapOptions);

        Object.freeze(this._mapOptions);
    };

    EsterenMap.prototype.message = function(message, type) {
        var element;

        element = d.createElement('div');
        element.className = 'collection-item';

        element.innerHTML = message;

        var classesToAdd = 'rounded';
        switch (type) {
            case 'success':
                classesToAdd += ' green-text text-lighten-3';
                break;
            case 'error':
                classesToAdd += ' red-text text-lighten-3';
                break;
        }

        M.toast({
            html: element,
            displayLength: 4000,
            inDuration: 500,
            outDuration: 500,
            classes: classesToAdd
        });
    };

    EsterenMap.prototype.disableEditedElements = function(){
        var disabled = false;
        if (this._editedPolygon) {
            this._editedPolygon.disableEditMode();
            this._editedPolygon = null;
            disabled = true;
        }
        if (this._editedPolyline) {
            this._editedPolyline.disableEditMode();
            this._editedPolyline = null;
            disabled = true;
        }
        if (this._editedMarker) {
            this._editedMarker.disableEditMode();
            this._editedMarker = null;
            disabled = true;
        }
        if (disabled) {
            this._sidebar.getTab(this._mapOptions.sidebarInfoTabId).setContent('');
        }
    };

    EsterenMap.prototype.hasSelectedElement = function(){
        return this._editedPolygon || this._editedPolyline || this._editedMarker;
    };

    EsterenMap.prototype.mapReference = function(name, id, defaultValue) {
        var mapReferences = this._mapOptions.data.map, ref;

        defaultValue = defaultValue || null;

        if (!name || !id) {
            throw 'Please specify a map reference name or id.';
        }

        if (mapReferences[name]) {
            ref = mapReferences[name][id];
            if (ref) {
                return ref;
            }
        } else {
            console.warn('No map reference with name "'+name+'"');
        }

        return defaultValue;
    };

    EsterenMap.prototype.reference = function(name, id, defaultValue) {
        var references = this._mapOptions.data.references, ref;

        defaultValue = defaultValue || null;

        if (!name || !id) {
            throw 'Please specify a reference name or id.';
        }

        if (references[name]) {
            ref = references[name][id];
            if (ref) {
                return ref;
            }
        } else {
            throw 'No reference with name "'+name+'"';
        }

        return defaultValue;
    };

    EsterenMap.prototype.loadMapData = function(){
        var _this = this, data = {};

        return this._load({
            url: this._mapOptions.apiUrls.map,
            method: 'GET',
            data: data,
            callback: function(response){
                //callback "success"
                if (response.map && response.references && response.templates) {
                    _this._mapOptions.data = response;
                    _this._initialize();
                } else {
                    throw 'Map couldn\'t initialize because settings response was not correct.';
                }
            },
            callbackError: function(){
                //callback "Error"
                throw 'Error while loading settings';
            }
        });
    };

    /**
     * Merge two objects into a new one.
     *
     * @param {object} obj1 Le premier objet
     * @param {object} [obj2] Le deuxième objet
     * @returns {object}
     */
    EsterenMap.prototype.cloneObject = function(obj1, obj2){
        var newObject;

        // Crée un nouvel objet
        newObject = $.extend(true, {}, obj1);

        if (obj2) {
            // Fusionne le deuxième avec le premier objet
            newObject = $.extend(true, {}, newObject, obj2);
        }

        return newObject;
    };

    w.EsterenMap = EsterenMap;

})(jQuery, L, document, window, M);
