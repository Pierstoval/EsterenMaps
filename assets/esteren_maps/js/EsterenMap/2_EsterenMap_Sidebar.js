(function ($, L, d, w, Materialize) {
    var _tabContainer, linksContainer, contentContainer;

    if (!Materialize) {
        throw 'Materialize must be enabled to use the map sidebar';
    }

    EsterenMap.prototype.initSidebar = function () {
        var mapOptions = this._mapOptions;

        if (!mapOptions.sidebarContainer) {
            return;
        }

        var sb = new Sidebar(mapOptions.sidebarContainer);

        this._sidebar = sb;

        this.addTabToSidebar({
            tabContainerId: mapOptions.sidebarInfoTabId,
            iconClass: 'fa-info',
            htmlOrElement: d.createElement('div')
        });

        sb.refreshTabs();
    };

    EsterenMap.prototype.addTabToSidebar = function (parameters) {
        if (!this._sidebar) {
            throw 'Cannot add tab to sidebar since we do not have a sidebar.';
        }

        return this._sidebar.createAndAddTab(parameters);
    };

    function Sidebar(containerId) {
        if (_tabContainer) {
            throw 'Sidebar already initialized';
        }

        var sidebar = d.getElementById(containerId);

        // Initialize the global var that we'll use for "private" methods.
        _tabContainer = sidebar;

        if (!sidebar) {
            throw 'Element with id "' + containerId + '" to create sidebar was not found.';
        }

        linksContainer = sidebar.querySelector('.sidebar-links');
        if (!linksContainer) {
            throw 'Current sidebar does not contain expected links container. It is mandatory to store all sidebar icon links that will allow tabs interaction.';
        }

        contentContainer = sidebar.querySelector('.sidebar-content-container');
        if (!contentContainer) {
            throw 'Current sidebar does not contain expected content container. It is mandatory to store all the sidebar contents that will allow tabs interaction';
        }
    };

    Sidebar.prototype._tabs = {};

    Sidebar.prototype.getTab = function (tabId) {
        if (this._tabs[tabId]) {
            return this._tabs[tabId];
        }
    };

    Sidebar.prototype.refreshTabs = function () {
        var tabInstance;

        if (!_tabContainer) {
            throw 'Cannot refresh tab that is not initialized';
        }

        tabInstance = Materialize.Tabs.getInstance(linksContainer);

        if (!tabInstance) {
            console.warn('Attempted to refresh tabs, but Materialize tabs are not initialized yet.');

            return;
        }

        tabInstance.updateTabIndicator();
    };

    Sidebar.prototype.createAndAddTab = function (parameters) {
        var tabContainerId = parameters.tabContainerId || null,
            iconClass = parameters.iconClass || 'fa-icon',
            htmlOrElement = parameters.htmlOrElement || null
        ;

        if (!_tabContainer) {
            throw 'Cannot add new tab to sidebar since it is not initialized.';
        }
        if (!tabContainerId) {
            throw 'Missing container id to create sidebar tab.';
        }
        if (this._tabs[tabContainerId]) {
            throw 'A tab named "' + tabContainerId + '" already exists.';
        }
        if (d.getElementById(tabContainerId)) {
            throw 'Container id "' + tabContainerId + '" already exists in the DOM. Please choose another ID for your sidebar tab.';
        }
        if (!htmlOrElement) {
            throw 'Missing html content or DOM element to create sidebar tab.';
        }

        var liTag = d.createElement('li');
        liTag.classList.add('tab');

        var link = d.createElement('a');
        link.href = '#' + tabContainerId;
        link.innerHTML = '<i class="fa ' + iconClass + '"></i>';
        liTag.appendChild(link);

        var contentElement = d.createElement('div');
        contentElement.id = tabContainerId;
        contentElement.style.display = 'none';

        if (typeof htmlOrElement === 'string') {
            contentElement.innerHTML = htmlOrElement;
        } else {
            // Sometimes it's an element. If it is, it means that it probably has initialized DOM events.
            // And having already initialized events is good :)
            contentElement.appendChild(htmlOrElement);
        }

        linksContainer.appendChild(liTag);
        contentContainer.appendChild(contentElement);

        this._tabs[tabContainerId] = new Tab(tabContainerId, link, contentElement);

        this.refreshTabs();
    };

    function Tab(tabContainerId, link, contentElement) {
        this.tabContainerId = tabContainerId;
        this.link = link;
        this.contentElement = contentElement;
    }

    Tab.prototype.tabContainerId = null;
    Tab.prototype.link = null;
    Tab.prototype.contentElement = null;

    Tab.prototype.setContent = function (content) {
        this.contentElement.innerHTML = content;
    };

    Tab.prototype.getAsElement = function () {
        return this.contentElement;
    };

    Tab.prototype.focus = function () {
        this.link.click();
    };

})(jQuery, L, document, window, M);
