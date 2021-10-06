/**
 * Here we absolutely need the canvas to be infinite.
 * This is why we create a specific CRS to handle latitudes and longitudes beyond -90/+90 and -180/+180 limits.
 *
 * Check this link for more info:
 * @see https://github.com/Leaflet/Leaflet/issues/210#issuecomment-3344944
 * @see http://leafletjs.com/reference.html#transformation
 */

/**
 * Transformation: Represents an affine transformation: a set of coefficients a, b, c, d for transforming a point of a
 * form (x, y) into (a*x + b, c*y + d) and doing the reverse.
 * (copy/paste from Leaflet documentation)
 */
L.CRS.XY = L.Util.extend({}, L.CRS.Simple, {
    code: 'XY',
    projection: L.Projection.LonLat,
    transformation: new L.Transformation(1, 0, 1, 0)
});
