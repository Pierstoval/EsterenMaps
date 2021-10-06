(function($, L, d, w, Materialize) {
    var _currentHighlightedPath, map;

    EsterenMap.prototype.initDirections = function(){
        var directionsContainerId = 'esterenmaps-directions',
            mapOptions = this._mapOptions
        ;

        if (!mapOptions.showDirections) {
            console.error('Tried to initialize directions but config option says no.');

            return false;
        }

        map = this;

        this.addTabToSidebar({
            tabContainerId: directionsContainerId,
            iconClass: 'fa-location-arrow',
            htmlOrElement: createDirectionsDOMContent(this)
        });

        var content = d.getElementById(directionsContainerId);

        setEvents(this, content);
        configureAutocomplete(this, content);

        return this;
    };

    function cleanDirections (){
        var markers = map._markers,
            routes = map._polylines,
            i, l, step, marker, route, routeElement
        ;

        var path = _currentHighlightedPath;

        if (path && path.length) {
            for (i = 0, l = path.length; i < l; i++) {
                step = path[i];
                marker = markers[step.id];
                route = step.route ? routes[step.route.id] : null;
                marker._icon.classList.remove('selected');
                if (route) {
                    routeElement = route._path;
                    if (routeElement.classList.contains('highlighted')) {
                        routeElement.setAttribute('stroke-width', parseInt(routeElement.getAttribute('stroke-width')) / 2);
                        routeElement.setAttribute('stroke', route._oldColor);
                        routeElement.classList.remove('highlighted');
                    }
                }
            }
        }
        this._steps = [];
    }
    EsterenMap.prototype.cleanDirections = cleanDirections;

    function highlightPath (path){
        var markers = map._markers,
            routes = map._polylines,
            highlightColor = '#4444dd',
            i, l, step, marker, route, routeElement
        ;

        _currentHighlightedPath = path;

        for (i = 0, l = path.length; i < l; i++) {
            step = path[i];
            marker = markers[step.id];
            route = step.route ? routes[step.route.id] : null;
            marker._icon.classList.add('selected');
            if (route) {
                routeElement = route._path;
                if (!routeElement.classList.contains('highlighted')) {
                    routeElement.setAttribute('stroke-width', parseInt(routeElement.getAttribute('stroke-width')) * 2);
                    route._oldColor = routeElement.getAttribute('stroke');
                    routeElement.setAttribute('stroke', highlightColor);
                    routeElement.classList.add('highlighted');
                }
            }
        }
    }

    function createDirectionsDOMContent(map){
        if (!(map instanceof EsterenMap)) {
            console.error('An "EsterenMap" is required to initialize directions control.');
            return false;
        }

        var content,
            msgSend = FORM_SUBMIT,
            directionsMsgTitle = MSG_CONTROL_DIRECTIONS_TITLE,
            directionsMsgStart = MSG_CONTROL_DIRECTIONS_START,
            directionsMsgEnd = MSG_CONTROL_DIRECTIONS_END,
            directionsMsgTransport = MSG_CONTROL_TRANSPORTS
        ;

        // Ajout des différents noeuds à l'objet Content
        // On persiste ici à utiliser le concept objet
        // pour être sûr que des listeners ne sont pas "perdus" en route

        content = d.createElement('div');

        content.innerHTML =
            '<div id="directions_wait_overlay"></div>' +
            '<h3 class="text-xxl">' + directionsMsgTitle + '</h3>' +
            '<form action="#" id="directions_form">' +
                '<div class="input-field">' +
                    '<input type="text" name="start" class="autocomplete" id="directions_start" />' +
                    '<label for="directions_start">' + directionsMsgStart + '</label>' +
                    '<div class="directions_helper"></div>' +
                '</div>' +
                '<div class="input-field">' +
                    '<input type="text" name="end" class="autocomplete" id="directions_end" />' +
                    '<label for="directions_end">' + directionsMsgEnd + '</label>' +
                    '<div class="directions_helper"></div>' +
                '</div>' +
                '<div>' +
                    '<h3 class="text-xxl mt10">' + directionsMsgTransport + '</h3>' +
                    '<div id="directions_transport"></div>' +
                '</div>' +
                '<button class="btn" type="submit">' + msgSend + '</button>' +
                '<div id="directions_message"></div>' +
            '</form>'
        ;

        var transportsOptions = '';
        $.each(map._mapOptions.data.references.transports, function(i, e){
            transportsOptions +=
                '<div>' +
                    '<label for="directions_transport_' + e.id + '">' +
                    '<input name="directions_transport" type="radio" id="directions_transport_' + e.id + '" value="' + e.id + '">' +
                        '<span>' + e.name + '</span>' +
                    '</label>' +
                '</div>'
            ;
        });

        content.querySelector('#directions_transport').innerHTML = transportsOptions;

        return content;
    }

    function setEvents(map, content) {

        content.querySelector('#directions_form').addEventListener('submit', function(e){
            var data = $(this).serializeArray(),
                markers = map._markers,
                directionsMsgNotFound = MSG_CONTROL_DIRECTIONS_MARKER_NOT_FOUND,
                submitButton = this.querySelector('[type="submit"]'),
                messageBox = d.getElementById('directions_message'),
                overlay = d.getElementById('directions_wait_overlay'),
                start = data.filter(function(e){return e.name==='start';})[0].value,
                end = data.filter(function(e){return e.name==='end';})[0].value,
                transport = data.filter(function(e){return e && e.name==='directions_transport';})[0],
                markerStart, markerEnd, message
            ;

            if (transport) {
                transport = transport.value || 1;
            } else {
                transport = 1;
            }

            // Prevents submitting this form right away
            L.DomEvent.stop(e);

            if (submitButton.hasAttribute('disabled')) {
                return false;
            }

            submitButton.setAttribute('disabled', 'disabled');
            cleanDirections();

            for (var marker in markers) {
                if (!markers.hasOwnProperty(marker)) { continue; }
                marker = markers[marker]._esterenMarker;
                if (!markerStart && marker.name === start) {
                    markerStart = marker.id;
                }
                if (!markerEnd && marker.name === end) {
                    markerEnd = marker.id;
                }
                if (markerStart && markerEnd) {
                    break;
                }
            }

            if (markerStart && markerEnd) {
                overlay.style.display = "block";
                map._load({
                    url: map._mapOptions.apiUrls.directions.replace('{from}', markerStart).replace('{to}', markerEnd),
                    xhr_name: 'directions_calculate',
                    data: {
                        'transport': transport
                    },
                    callback: function(response) {
                        if (response.error && response.message) {
                            messageBox.innerHTML = response.message;
                            setTimeout(function(){$('#directions_message').text('');}, 3000);
                        } else if (response.path && response.path.length) {
                            highlightPath(response.path, map);
                            map._map.fitBounds(L.latLngBounds(response.bounds.northEast, response.bounds.southWest));
                            messageBox.innerHTML = response.path_view;
                        } else if (response.path_view) {
                            messageBox.innerHTML = response.path_view;
                        }
                    },
                    callbackComplete: function() {
                        submitButton.removeAttribute('disabled');
                        overlay.style.display = '';
                    },
                    callbackError: function() {
                        submitButton.removeAttribute('disabled');
                        overlay.style.display = '';
                    }
                });

                return;
            } else if (markerStart || markerEnd || start || end) {
                message = '';
                if (!markerStart && start) {
                    message += start;
                }
                if (!markerEnd && end) {
                    message += (message?', ':'') + end;
                }

                messageBox.innerHTML = directionsMsgNotFound + ' ' + message;
            }

            submitButton.removeAttribute('disabled');
            overlay.style.display = '';

            return false;
        });

    }

    function configureAutocomplete(map, content) {
        var autocompleteData = {};
        var callbackData = {};

        for (var marker in map._markers) {
            if (!map._markers.hasOwnProperty(marker)) { continue; }

            marker = map._markers[marker]._esterenMarker;
            var markerType = map.reference('markers_types', marker.marker_type);
            autocompleteData[marker.name] = markerType.icon;
            callbackData[marker.name] = marker;
        }

        var directionsInputs = content.querySelectorAll('.autocomplete');
        for (var i = 0, l = directionsInputs.length; i < l; i++) {
            var input = directionsInputs[i];
            input.addEventListener('blur', function () {
                checkInputs(directionsInputs, callbackData);
            });
        }

        Materialize.Autocomplete.init(directionsInputs, {
            data: autocompleteData,
            onAutocomplete: function(){
                checkInputs(directionsInputs, callbackData);
            }
        });
    }

    function checkInputs(directionsInputs, callbackData) {
        var numberOfInputs = directionsInputs.length;
        var numberOfValues = 0;

        for (var i = 0; i < numberOfInputs; i++) {
            var input = directionsInputs[i];
            if (input.value.trim()) {
                var value = input.value.trim();
                if (callbackData[value]) {
                    numberOfValues++;
                    input.classList.remove('invalid');
                    input.classList.add('valid');
                } else {
                    input.classList.remove('valid');
                    input.classList.add('invalid');
                }
            }
        }
    }
})(jQuery, L, document, window, M);
