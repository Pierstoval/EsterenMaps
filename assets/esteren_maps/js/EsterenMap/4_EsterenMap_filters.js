(function($, L, d, w){

    EsterenMap.prototype.initFilters = function(){
        var filtersContainerId = 'esterenmaps-filters',
            mapOptions = this._mapOptions
        ;

        if (mapOptions.showFilters !== true) {
            console.error('Tried to initialize display filters but config option says no.');

            return false;
        }

        this.addTabToSidebar({
            tabContainerId: filtersContainerId,
            iconClass: 'fa-filter',
            htmlOrElement: createFiltersDOMContent(this)
        });
        setEvents(filtersContainerId);

        return this;
    };

    function createFiltersDOMContent(map) {
        if (!(map instanceof EsterenMap)) {
            console.error('An "EsterenMap" is required to initialize filters control.');
            return false;
        }

        var content,
            filtersMsgTitle = MSG_CONTROL_FILTERS_TITLE,
            filtersMsgRoutesTypes = MSG_ROUTES_TYPES,
            filtersMsgZonesTypes = MSG_ZONES_TYPES,
            filtersMsgMarkersTypes = MSG_MARKERS_TYPES,
            msgHideAllRoutesTypes = MSG_HIDE_ROUTES,
            msgHideAllZonesTypes = MSG_HIDE_ZONES,
            msgHideAllMarkersTypes = MSG_HIDE_MARKERS,
            listsClasses = 'list-unstyled',
            listsElementsClasses = '',
            listsElementsStyles = 'display: block;',
            nodesList = {
                "markersTypes": [],
                "routesTypes": [],
                "zonesTypes": [],
                "markersTypesUL": [],
                "routesTypesUL": [],
                "zonesTypesUL": []
            },
            captionStyle = 'width: 12px; height: 12px; vertical-align: text-top; margin-right: 3px; margin-left: 3px;',
            list, elements
        ;

        // ------------- MARKERS -------------
        list = L.DomUtil.create('ul', listsClasses);
        elements = map._mapOptions.data.references.markers_types;
        if (elements) {
            $.each(elements, function(index, markerType) {
                var node = L.DomUtil.create('li', listsElementsClasses, list);
                node.setAttribute('style', listsElementsStyles);
                node.innerHTML =
                    '<div>'
                        +'<label for="markerType'+markerType.id+'">'
                            +'<input id="markerType'+markerType.id+'" type="checkbox" class="leaflet-filter-checkbox" checked="checked" />'
                            +'<span>'
                                +'<img src="'+markerType.icon+'" class="ib" style="'+captionStyle+'"> '
                                +markerType.name
                            +'</span>'
                        +'</label>'
                    +'</div>'
                ;
                nodesList.markersTypes.push(node);
            });
        }
        nodesList.markersTypesUL = list;

        // ------------- ROUTES -------------
        list = L.DomUtil.create('ul', listsClasses);
        elements = map._mapOptions.data.references.routes_types;
        if (elements) {
            $.each(elements, function(index, routeType) {
                var node = L.DomUtil.create('li', listsElementsClasses, list);
                node.setAttribute('style', listsElementsStyles);
                node.innerHTML =
                    '<div>'
                        +'<label for="routeType'+routeType.id+'">'
                            +'<input id="routeType'+routeType.id+'" type="checkbox" class="leaflet-filter-checkbox" checked="checked" />'
                            +'<span>'
                                +'<span class="ib" style="'+captionStyle+' background: '+routeType.color+'"></span> '
                                +routeType.name
                            +'</span>'
                        +'</label>'
                    +'</div>'
                ;
                nodesList.routesTypes.push(node);
            });
        }
        nodesList.routesTypesUL = list;

        // ------------- ZONES -------------
        list = L.DomUtil.create('ul', listsClasses);
        elements = map._mapOptions.data.references.zones_types;
        if (elements) {
            $.each(elements, function(index, zoneType) {
                var node = L.DomUtil.create('li', listsElementsClasses, list);
                node.setAttribute('style', listsElementsStyles);
                node.innerHTML =
                    '<div>'
                        +'<label for="zoneType'+zoneType.id+'">'
                            +'<input id="zoneType'+zoneType.id+'" type="checkbox" class="leaflet-filter-checkbox" checked="checked" />'
                            +'<span>'
                                +'<span class="ib" style="'+captionStyle+' background: '+zoneType.color+'"></span> '
                                +zoneType.name
                            +'</span>'
                        +'</label>'
                    +'</div>'
                ;
                nodesList.zonesTypes.push(node);
            });
        }
        nodesList.zonesTypesUL = list;

        // Ajout des différents noeuds à l'objet Content
        // On persiste ici à utiliser le concept objet
        // pour être sûr que des listeners ne sont pas "perdus" en route

        content = L.DomUtil.create('div');

        content.innerHTML = '                                             \
            <h3 class="text-xxl">' + filtersMsgTitle + '</h3>             \
            <div class="row">                                             \
                <div class="col s12">                                     \
                    <h4 class="text-xxl">'+filtersMsgMarkersTypes+'</h4>  \
                    <button type="button" class="hide-all btn btn-small"> \
                        <i class="fa fa-eye"></i>                         \
                        '+msgHideAllMarkersTypes+'                        \
                    </button>                                             \
                    '+nodesList.markersTypesUL.outerHTML+'                \
                </div>                                                    \
                <div class="col s12">                                     \
                    <h4 class="text-xxl">'+filtersMsgRoutesTypes+'</h4>   \
                    <button type="button" class="hide-all btn btn-small"> \
                        <i class="fa fa-eye"></i>                         \
                        '+msgHideAllRoutesTypes+'                         \
                    </button>                                             \
                    '+nodesList.routesTypesUL.outerHTML+'                 \
                </div>                                                    \
                <div class="col s12">                                     \
                    <h4 class="text-xxl">'+ filtersMsgZonesTypes+'</h4>   \
                    <button type="button" class="hide-all btn btn-small"> \
                        <i class="fa fa-eye"></i>                         \
                        '+msgHideAllZonesTypes+'                          \
                    </button>                                             \
                    '+nodesList.zonesTypesUL.outerHTML+'                  \
                </div>                                                    \
            </div>                                                        \
        ';

        return content;
    }

    function setEvents(filtersContainerId) {
        var filtersContainer = $('#'+filtersContainerId);
        var inputs = filtersContainer.find('input.leaflet-filter-checkbox');
        var hideAllButtons = filtersContainer.find('button.hide-all');

        inputs.on('change', function(e){
            var styleContainer = d.getElementById('filtersStyle');
            if (!styleContainer) {
                styleContainer = d.createElement('style');
                styleContainer.setAttribute('id', 'filtersStyle');
                styleContainer.setAttribute('type', 'text/css');
                d.head.appendChild(styleContainer);
            }
            var cssCode = '';
            inputs.each(function(i,input){
                if (!$(input).is(':checked')) {
                    cssCode += '[data-leaflet-object-type="'+input.id+'"]{display:none;}';
                }
            });
            styleContainer.innerHTML = cssCode;
        });

        hideAllButtons.on('click', function (e) {
            var inputs = e.target._all_inputs;
            var isActive = e.target.classList.contains('btn-flat');

            if (!inputs) {
                var parent = e.target.parentElement;
                inputs = parent.querySelectorAll('input.leaflet-filter-checkbox');
                e.target._all_inputs = inputs;
            }

            for (var i = 0, l = inputs.length; i < l; i++) {
                var input = inputs[i];
                input.checked = isActive;
                var event = document.createEvent('HTMLEvents');
                event.initEvent('change', false, true);
                input.dispatchEvent(event);
            }

            if (isActive) {
                e.target.classList.remove('btn-flat');
            } else {
                e.target.classList.add('btn-flat');
            }
        });
    }

})(jQuery, L, document, window);
