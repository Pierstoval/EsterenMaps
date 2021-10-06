(function($, L, d, w, Materialize) {
    var FORM_ID = 'search_form';
    var INPUT_QUERY_ID = 'search_query';
    var BUTTON_SUBMIT_ID = 'search_button_submit';
    var BOX_MESSAGE_ID = 'search_message';
    var DATA_ROUTE_ID = 'route-id';
    var DATA_ZONE_ID = 'zone-id';
    var DATA_MARKER_ID = 'marker-id';

    var notFoundElementSearchMessage = MSG_CONTROL_SEARCH_NOT_FOUND || 'Not found';

    var pathColor = '#4444dd';

    var map;

    var searchForm;
    var searchContainer;
    var searchInput;
    var selectedMapElement;
    var searchHelper;

    /**
     * Keys = names (must be identical to input value)
     * Values = Leaflet elements
     */
    var searchData = {
        markers: {},
        routes: {},
        zones: {}
    };

    EsterenMap.prototype.initSearch = function(){
        var searchEngineContainerId = 'esterenmaps-search',
            mapOptions = this._mapOptions
        ;

        if (!mapOptions.showSearchEngine) {
            console.error('Tried to initialize search engine but config option says no.');

            return false;
        }

        map = this;

        searchContainer = createSearchDOMContent();

        this.addTabToSidebar({
            tabContainerId: searchEngineContainerId,
            iconClass: 'fa-search',
            htmlOrElement: searchContainer
        });

        searchInput = searchContainer.querySelector('#search_query');
        if (!searchInput) {
            throw 'Search input was incorrectly initialized.';
        }
        searchHelper = searchContainer.querySelector('.search_helper');
        if (!searchHelper) {
            throw 'Search helper element was incorrectly initialized.';
        }
        searchForm = d.getElementById(FORM_ID);
        if (!searchForm) {
            throw 'Search form was incorrectly initialized.';
        }

        setEvents();
        configureAutocomplete(searchContainer);

        return this;
    };

    function createSearchDOMContent() {
        if (!(map instanceof EsterenMap)) {
            console.error('An "EsterenMap" is required to initialize search control.');
            return false;
        }

        var content = d.createElement('div'),
            msgSend = typeof FORM_SUBMIT !== 'undefined' ? FORM_SUBMIT : 'FORM_SUBMIT',
            searchMsgTitle = typeof MSG_CONTROL_SEARCH_TITLE !== 'undefined' ? MSG_CONTROL_SEARCH_TITLE : 'MSG_CONTROL_SEARCH_TITLE',
            searchMsgPlaceholder = typeof MSG_CONTROL_SEARCH_PLACEHOLDER !== 'undefined' ? MSG_CONTROL_SEARCH_PLACEHOLDER : 'MSG_CONTROL_SEARCH_PLACEHOLDER'
        ;

        // Ajout des différents noeuds à l'objet Content
        // On persiste ici à utiliser le concept objet
        // pour être sûr que des listeners ne sont pas "perdus" en route

        content.innerHTML =
            '<div id="maps_search_wait_overlay"></div>' +
                '<form action="#" id="'+FORM_ID+'">' +
                    '<h3 class="text-xxl">' + searchMsgTitle + '</h3>' +
                    '<div class="input-field">' +
                        '<input type="text" class="autocomplete" name="'+INPUT_QUERY_ID+'" id="'+INPUT_QUERY_ID+'" />' +
                        '<label for="'+INPUT_QUERY_ID+'">' + searchMsgPlaceholder + '</label>' +
                        '<div class="search_helper"></div>' +
                    '</div>' +
                    '<div id="'+BOX_MESSAGE_ID+'"></div>' +
                    '<button id="'+BUTTON_SUBMIT_ID+'" class="btn" type="submit">' + msgSend + '</button>' +
                '</div>' +
            '</form>'
        ;

        return content;
    }

    function setEvents() {
        if (!searchForm) {
            throw 'Search form does not exist.';
        }

        searchForm.addEventListener('submit', function (event) {
            event.preventDefault();
            checkInput();
            return false;
        });
    }

    function configureAutocomplete(content) {
        var autocompleteData = {};

        for (var marker in map._markers) {
            if (!map._markers.hasOwnProperty(marker)) { continue; }
            marker = map._markers[marker];

            var esterenMarker = marker._esterenMarker;
            var markerType = map.reference('markers_types', esterenMarker.marker_type);

            autocompleteData[esterenMarker.name] = markerType.icon;
            searchData.markers[esterenMarker.name] = marker;
        }

        for (var polyline in map._polylines) {
            if (!map._polylines.hasOwnProperty(polyline)) { continue; }
            polyline = map._polylines[polyline];

            var esterenRoute = polyline._esterenRoute;
            var routeType = map.reference('routes_types', esterenRoute.route_type);

            autocompleteData[esterenRoute.name] = routeType.icon;
            searchData.routes[esterenRoute.name] = polyline;
        }

        for (var polygon in map._polygons) {
            if (!map._polygons.hasOwnProperty(polygon)) { continue; }
            polygon = map._polygons[polygon];

            var esterenZone = polygon._esterenZone;
            var zoneType = map.reference('zones_types', esterenZone.zone_type);

            autocompleteData[esterenZone.name] = zoneType.icon;
            searchData.zones[esterenZone.name] = polygon;
        }

        if (!searchInput.classList.contains('autocomplete')) {
            throw 'No autocompleteable input in search engine container.';
        }
        searchInput.addEventListener('blur', function () {
            searchHelper.innerHTML = '';
            checkInput();
        });
        searchInput.addEventListener('keyup', function () {
            if (selectedMapElement) {
                unFocusSelectedElement();
            }
        });

        Materialize.Autocomplete.init(content.querySelectorAll('.autocomplete'), {
            data: autocompleteData,
            onAutocomplete: function(){
                searchHelper.innerHTML = '';
                checkInput();
            }
        });
    }

    function checkInput() {
        if (!searchInput) {
            throw 'Can\'t check input if there is no input.';
        }

        if (!searchData) {
            throw 'Can\'t check input if there is no data to check.';
        }

        if (searchInput.value.trim()) {
            var value = searchInput.value.trim();
            if (
                searchData.markers[value] ||
                searchData.routes[value] ||
                searchData.zones[value]
            ) {
                searchInput.classList.remove('invalid');
                searchInput.classList.add('valid');
                focusElement();
            } else {
                searchInput.classList.remove('valid');
                searchInput.classList.add('invalid');
            }
        }
    }

    function unFocusSelectedElement() {
        var element = selectedMapElement, path;

        if (!element) {
            return;
        }

        if (element._esterenMarker) {
            // Remove class in icon
            element._icon.classList.remove('selected');
        } else if (element._esterenRoute || element._esterenZone) {
            // Remove class in svg path, and rollback the stroke color
            path = element._path;

            if (!path) {
                throw 'No path was found for this element.';
            }

            path.setAttribute('stroke-width', parseInt(path.getAttribute('stroke-width')) / 2);
            path.setAttribute('stroke', element._oldColor);
            path.classList.remove('highlighted');
        }

        selectedMapElement = null;
    }

    function focusElement() {
        var elementName = searchInput.value.trim(),
            element = searchData.markers[elementName]
                || searchData.routes[elementName]
                || searchData.zones[elementName]
                || null,
            path
        ;

        if (!map) {
            throw 'Tried to focus on an element, but... There is no map!';
        }

        map.cleanDirections();

        if (elementName && !element) {
            console.error('Could not find element "'+elementName+'" despite many checks.');

            return;
        }

        if (selectedMapElement) {
            unFocusSelectedElement();
        }

        if (element._esterenMarker) {
            // Focus on the marker and add class to icon
            element._icon.classList.add('selected');
            map._map.setView(element.getLatLng(), map._map.getMaxZoom(), {animate: true});
        } else if (element._esterenRoute || element._esterenZone) {
            path = element._path;

            if (!path) {
                throw 'No path was found for this element.';
            }

            // Focus on the route or zone and add class to svg path
            path.classList.add('highlighted');

            path.setAttribute('stroke-width', parseInt(path.getAttribute('stroke-width')) * 2);
            element._oldColor = path.getAttribute('stroke');
            path.setAttribute('stroke', pathColor);

            bounds = L.latLngBounds(element.getLatLngs());

            if (!bounds.isValid()) {
                searchHelper.innerHTML = notFoundElementSearchMessage;

                return;
            }

            map._map.fitBounds(bounds, {animate: true});
        } else {
            throw 'No element to focus on.';
        }

        selectedMapElement = element;
    }
})(jQuery, L, document, window, M);
