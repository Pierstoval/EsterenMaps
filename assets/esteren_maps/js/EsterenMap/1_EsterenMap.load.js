(function($){

    /**
     * Executes an AJAX HTTP request to load elements.
     * Mostly used as an enhanced wrapper around jQuery's $.ajax() function.
     * The advantage is that all callbacks' "this" variable correspond to the EsterenMap object.
     *
     * @param parameters                  Parameters to send to AJAX request.
     * @param parameters.url              URL to load.
     * @param parameters.data             Options to send as AJAX data.
     * @param parameters.method           HTTP verb.
     * @param parameters.xhr_name         The name of an XHR var stored in memory, to abort unfinished HTTP requests.
     * @param parameters.callback         Called when HTTP request is successful (2xx HTTP code).
     * @param parameters.callbackError    Called when HTTP requet fails.
     * @param parameters.callbackComplete Called after every other XHR callback.
     */
    EsterenMap.prototype._load = function(parameters) {
        var url, xhr_object, ajaxObject, xhr_name, data, method,
            callback, callbackComplete, callbackError,
            _this = this,
            allowedMethods = ['GET']
        ;

        if (this._mapOptions.editMode || this._mapOptions.canAddNotes) {
            allowedMethods.push('POST');
            allowedMethods.push('PUT');
            allowedMethods.push('DELETE');
        }

        if (!$.isPlainObject(parameters)) {
            console.error('Malformed load request.', parameters);
            return;
        }

        xhr_name = parameters.xhr_name || null;
        data = parameters.data || {};
        method = parameters.method || 'GET';
        callback = parameters.callback || null;
        callbackComplete = parameters.callbackComplete || null;
        callbackError = parameters.callbackError || null;
        url = parameters.url || null;

        method = method ? method.toUpperCase() : 'GET';
        if (allowedMethods.indexOf(method) === -1) {
            console.error('Wrong HTTP method for _load() method. Allowed : '+allowedMethods.join(', '));
            return false;
        }

        if (!data) {
            data = {};
        }

        ajaxObject = {
            url: url,
            type: method,
            dataType: 'json',
            xhrFields: {
                withCredentials: true
            },
            crossDomain: true,
            contentType: method === 'GET' ? 'application/x-www-form-urlencoded' : 'application/json',
            jsonp: false,
            data: method === 'GET' ? data : JSON.stringify(data ? data : {})
        };

        // Apply the different callbacks
        if (typeof(callback) === 'function') {
            ajaxObject.success = function(response) {
                callback.call(_this, response);
            }
        }

        if (typeof(callbackComplete) === 'function') {
            ajaxObject.complete = function(){
                callbackComplete.call(_this);
            }
        }

        if (typeof(callbackError) === 'function') {
            ajaxObject.error = function(){
                callbackError.call(_this);
            }
        }

        if (xhr_name && this._xhr_saves[xhr_name]) {
            this._xhr_saves[xhr_name].abort();
        }

        xhr_object = $.ajax(ajaxObject);

        if (xhr_name) {
            this._xhr_saves[xhr_name] = xhr_object;
        }
    };
})(jQuery);
