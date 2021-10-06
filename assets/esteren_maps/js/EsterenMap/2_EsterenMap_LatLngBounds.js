/**
 * WARNING!
 * You MUST read this doc.
 *
 * This contains a lot of copy/pasted code from Leaflet initial code.
 *
 * We here overrode 2 lines because we use a different CRS to manage coordinates.
 * Actually this is just a question about "min" and "max" when calculating the latitude/longitude of the southWest/northEast points.
 * In the original system, latitude increases at the top of the image.
 * In our system, it's more like a bitmap and latitude increases at the bottom of the image.
 * This is why min/max are reversed on 2 specific lines.
 *
 * Longitude management is the same so the code is unchanged.
 *
 * @return L.LatLngBounds
 */
L.LatLngBounds.prototype.extend = function (obj) { // (LatLng) or (LatLngBounds)
    if (!obj) { return this; }

    var latLng = L.latLng(obj);
    if (latLng !== null) {
        obj = latLng;
    } else {
        obj = L.latLngBounds(obj);
    }

    if (obj instanceof L.LatLng) {
        if (!this._southWest && !this._northEast) {
            this._southWest = new L.LatLng(obj.lat, obj.lng);
            this._northEast = new L.LatLng(obj.lat, obj.lng);
        } else {
            this._southWest.lat = Math.max(obj.lat, this._southWest.lat);// Here we set "max" instead of "min" in the original code
            this._southWest.lng = Math.min(obj.lng, this._southWest.lng);

            this._northEast.lat = Math.min(obj.lat, this._northEast.lat);// Here we set "min" instead of "max" in the original code
            this._northEast.lng = Math.max(obj.lng, this._northEast.lng);
        }
    } else if (obj instanceof L.LatLngBounds) {
        this.extend(obj._southWest);
        this.extend(obj._northEast);
    }
    return this;
};
