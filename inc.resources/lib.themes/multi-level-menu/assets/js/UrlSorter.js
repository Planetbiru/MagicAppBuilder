/**
 * Class to handle URL parsing and sorting parameters (orderby and ordertype), including support for array parameters.
 */
class UrlSorter {
    /**
     * Constructs the UrlSorter instance and parses the given URL.
     * 
     * @param {string} [url=window.location.toString()] - The URL to parse. Defaults to the current window URL.
     */
    constructor(url = window.location.toString()) {
        /**
         * The original full URL string.
         * @type {string}
         */
        this.originalUrl = url;

        /**
         * Parsed query parameters from the original URL.
         * Array parameters are stored as arrays.
         * @type {Object.<string, string|string[]>}
         */
        this.originalQueryParams = {};

        /**
         * Current `orderby` value extracted from the original URL.
         * @type {string}
         */
        this.currentOrderBy = '';

        /**
         * Current `ordertype` value extracted from the original URL.
         * @type {string}
         */
        this.currentOrderType = '';

        /**
         * New `orderby` value to be used in the built URL.
         * @type {string}
         */
        this.newOrderBy = '';

        /**
         * New `ordertype` value to be used in the built URL.
         * @type {string}
         */
        this.newOrderType = '';

        this._parseUrl();
    }

    /**
     * Parses the original URL and extracts the base path, filename, and query parameters.
     * Supports array parameters with the `[]` notation.
     * @private
     */
    _parseUrl() {
        let cleanUrl = this.originalUrl.split("#")[0];
        let parts = cleanUrl.split("?");
        this.baseURL = parts[0];

        const pathSegments = this.baseURL.split('/');
        /**
         * The base filename of the URL (e.g., 'track.php').
         * @type {string}
         */
        this.baseName = pathSegments[pathSegments.length - 1] || '';

        const queryString = parts[1] || "";
        const params = new URLSearchParams(queryString);

        for (const [key, value] of params.entries()) {
            const normalizedKey = key.endsWith('[]') ? key.slice(0, -2) : key;

            if (key.endsWith('[]')) {
                if (!Array.isArray(this.originalQueryParams[normalizedKey])) {
                    this.originalQueryParams[normalizedKey] = [];
                }
                this.originalQueryParams[normalizedKey].push(value);
            } else if (this.originalQueryParams.hasOwnProperty(normalizedKey)) {
                if (!Array.isArray(this.originalQueryParams[normalizedKey])) {
                    this.originalQueryParams[normalizedKey] = [this.originalQueryParams[normalizedKey]];
                }
                this.originalQueryParams[normalizedKey].push(value);
            } else {
                this.originalQueryParams[normalizedKey] = value;
            }
        }

        this.currentOrderBy = this.originalQueryParams.orderby || '';
        this.currentOrderType = this.originalQueryParams.ordertype || '';

        this.newOrderBy = this.currentOrderBy;
        this.newOrderType = this.currentOrderType;
    }

    /**
     * Returns the current `orderby` value extracted from the original URL.
     * 
     * @returns {string}
     */
    getOrderBy() {
        return this.currentOrderBy;
    }

    /**
     * Returns the current `ordertype` value extracted from the original URL.
     * 
     * @returns {string}
     */
    getOrderType() {
        return this.currentOrderType;
    }

    /**
     * Sets a new `orderby` value to be used in the final URL.
     * 
     * @param {string} orderBy - The new orderby field.
     */
    setOrderBy(orderBy) {
        this.newOrderBy = orderBy;
    }

    /**
     * Sets a new `ordertype` value to be used in the final URL.
     * 
     * @param {string} orderType - The new order type ('asc' or 'desc').
     */
    setOrderType(orderType) {
        this.newOrderType = orderType;
    }

    /**
     * Returns all query parameters from the original URL except `orderby` and `ordertype`.
     * 
     * @returns {Object.<string, string|string[]>}
     */
    getOriginalQueryParams() {
        const params = { ...this.originalQueryParams };
        delete params.orderby;
        delete params.ordertype;
        return params;
    }

    /**
     * Builds and returns a relative URL using the base filename and updated query parameters.
     * Array values are correctly encoded using `[]` notation.
     * 
     * @returns {string} The constructed relative URL.
     */
    buildRelativeUrl() {
        const finalQueryParams = { ...this.originalQueryParams };

        if (this.newOrderBy) {
            finalQueryParams.orderby = this.newOrderBy;
        } else {
            delete finalQueryParams.orderby;
        }

        if (this.newOrderType) {
            finalQueryParams.ordertype = this.newOrderType;
        } else {
            delete finalQueryParams.ordertype;
        }

        let queryParts = [];

        for (const key in finalQueryParams) {
            if (finalQueryParams.hasOwnProperty(key)) {
                const value = finalQueryParams[key];
                if (Array.isArray(value)) {
                    value.forEach(v => {
                        if (v !== undefined && v !== null && v !== '') {
                            queryParts.push(`${encodeURIComponent(key)}[]=${encodeURIComponent(v)}`);
                        }
                    });
                } else if (value !== undefined && value !== null && value !== '') {
                    queryParts.push(`${encodeURIComponent(key)}=${encodeURIComponent(value)}`);
                }
            }
        }

        let queryString = queryParts.join("&");
        let finalUrl = this.baseName;

        if (queryString) {
            finalUrl += "?" + queryString;
        }

        return finalUrl;
    }
}
