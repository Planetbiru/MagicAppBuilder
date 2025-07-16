/**
 * Class to handle URL parsing and sorting parameters (orderby and ordertype).
 */
class UrlSorter {
    /**
     * Initializes the URL parser with the current or specified URL.
     * Parses the URL parameters and sets initial orderby and ordertype values.
     *
     * @param {string} [url=window.location.toString()] - The URL to parse. Defaults to the current window URL.
     */
    constructor(url = window.location.toString()) {
        this.originalUrl = url;
        this.originalQueryParams = {};
        this.currentOrderBy = '';
        this.currentOrderType = '';

        this.newOrderBy = '';
        this.newOrderType = '';

        this._parseUrl();
    }

    /**
     * Parses the URL and extracts base name and query parameters.
     * Sets current and new orderby and ordertype values.
     * @private
     */
    _parseUrl() {
        let cleanUrl = this.originalUrl.split("#")[0];
        let parts = cleanUrl.split("?");
        this.baseURL = parts[0];
        const queryString = parts[1] || "";

        const pathSegments = this.baseURL.split('/');
        this.baseName = pathSegments[pathSegments.length - 1] || '';

        let argArray = queryString.split("&");
        argArray.forEach(param => {
            let pair = param.split("=");
            if (pair[0] !== "") {
                this.originalQueryParams[pair[0]] = pair[1];
            }
        });

        this.currentOrderBy = this.originalQueryParams.orderby || '';
        this.currentOrderType = this.originalQueryParams.ordertype || '';

        this.newOrderBy = this.currentOrderBy;
        this.newOrderType = this.currentOrderType;
    }

    /**
     * Gets the current orderby value from the original URL.
     * @returns {string}
     */
    getOrderBy() {
        return this.currentOrderBy;
    }

    /**
     * Gets the current ordertype value from the original URL.
     * @returns {string}
     */
    getOrderType() {
        return this.currentOrderType;
    }

    /**
     * Sets a new orderby value.
     * @param {string} orderBy
     */
    setOrderBy(orderBy) {
        this.newOrderBy = orderBy;
    }

    /**
     * Sets a new ordertype value.
     * @param {string} orderType
     */
    setOrderType(orderType) {
        this.newOrderType = orderType;
    }

    /**
     * Returns original query parameters excluding orderby and ordertype.
     * @returns {Object}
     */
    getOriginalQueryParams() {
        const paramsWithoutOrder = { ...this.originalQueryParams };
        delete paramsWithoutOrder.orderby;
        delete paramsWithoutOrder.ordertype;
        return paramsWithoutOrder;
    }

    /**
     * Builds a relative URL using the base name and updated query parameters.
     * @returns {string}
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
            if (
                finalQueryParams.hasOwnProperty(key) &&
                finalQueryParams[key] !== undefined &&
                finalQueryParams[key] !== null &&
                finalQueryParams[key] !== ''
            ) {
                queryParts.push(`${key}=${finalQueryParams[key]}`);
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