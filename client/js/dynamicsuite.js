/**
 * This file is part of the Dynamic Suite framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package DynamicSuite
 * @author Grant Martin <commgdog@gmail.com>
 * @copyright 2021 Dynamic Suite Team
 */

// noinspection JSUnusedGlobalSymbols

/**
 * Class DynamicSuite.
 */
class DynamicSuite
{

    /**
     * Call a Dynamic Suite API endpoint.
     *
     * @param {string} api_id - The API ID of the API to call.
     * @param {object} data - Data to send along with the request (POST).
     * @param {function} callback - Callback to execute on response.
     * @returns {undefined}
     */
    static call(api_id, data, callback) {
        fetch(`/dynamicsuite/api/${api_id}`, {
            method: 'POST',
            body: JSON.stringify(data ? data : [])
        })
        .then(response => {
            if (response.ok) {
                return response;
            }
            throw new Error('A server error has occurred');
        })
        .then(response => response.json())
        .then(json => callback(json), () => callback({
            status: 'SERVER_ERROR',
            message: 'A malformed response was returned',
            data: null
        }))
        .catch((e) => {
            console.log(e);
            callback({
                status: 'SERVER_ERROR',
                message: 'A malformed response was returned',
                data: null
            });
        });
    }

    /**
     * Read custom window data for the given key.
     *
     * @param {string} key - The key to read.
     * @returns {*}
     */
    static readCustomData(key) {
        if (typeof window['dynamicsuite']['custom'][key] !== 'undefined') {
            return window['dynamicsuite']['custom'][key];
        } else {
            return false;
        }
    }

    /**
     * Broadcast to the root event bus.
     *
     * @param {string} key - The key to broadcast.
     * @param {*} value - The value to broadcast.
     * @returns {undefined}
     */
    static broadcast(key, value) {
        if (typeof this.vm === 'undefined') {
            return;
        }
        this.vm.$emit(key, value);
    }

    /**
     * Listen for a keys on the root event bus.
     *
     * @param {string} key - The key to listen for.
     * @param {function} callback - The callback to execute when heard.
     * @returns {undefined}
     */
    static listen(key, callback) {
        if (typeof this.vm === 'undefined') {
            return;
        }
        this.vm.$on(key, callback);
    }

    /**
     * Get the given URL parameter value.
     *
     * @param {string} param - The search parameter to read.
     * @returns {string}
     */
    static readURLParam(param) {
        const params = new URLSearchParams(window.location.search);
        return params.get(param);
    }

    /**
     * Update the URL to include the value for the given key.
     *
     *
     * @param {string} key - The key for the value.
     * @param {string|boolean|number|null} value - The value to include.
     * @returns {string}
     */
    static #updateURL(key, value) {
        const url = new URLSearchParams(window.location.search);
        if (value === null) {
            url.delete(key);
        } else {
            url.set(key, value);
        }
        const params = url.toString();
        return params ? `${window.location.pathname}?${params}` : window.location.pathname;
    }

    /**
     * Push a new URL to the window history including the key, value pair.
     *
     * @param {string} key - The key for the value.
     * @param {string|boolean|number|null} value - The value to include.
     * @returns {undefined}
     */
    static pushURLHistory(key, value) {
        history.pushState({}, null, DynamicSuite.#updateURL(key, value));
    }

    /**
     * Replace the current URL in the window history including the key, value pair.
     *
     * @param {string} key - The key for the value.
     * @param {string|boolean|number|null} value - The value to include.
     * @returns {undefined}
     */
    static replaceURLHistory(key, value) {
        history.replaceState({}, null, DynamicSuite.#updateURL(key, value));
    }

}

/**
 * Initialize Dynamic Suite Vue.
 */
window.addEventListener('load', () => {
    DynamicSuite.vm = new Vue({
        name: 'DynamicSuite',
        el: '#dynamicsuite',
        data() {
            return {
                selected_group: null,
                has_session: false,
                hide_overlay: true,
                default_view: null,
                overlay_nav_tree: [],
                overlay_nav_footer_text: null,
                overlay_nav_footer_view: null,
                overlay_nav_alert_success: {},
                overlay_nav_alert_warning: {},
                overlay_nav_alert_failure: {},
                overlay_title: null,
                overlay_actions: []
            };
        },
        methods: {

            /**
             * Set a nav alert.
             *
             * @param {string} type - The alert type (success, warning, failure).
             * @param {string} id - The nav ID key.
             * @param {string|number} value - The alert value.
             * @returns {undefined}
             */
            setNavAlert(type, id, value) {
                const key = `overlay_nav_alert_${type}`;
                this.$set(this[key], id, value);
            },

            /**
             * Set the active nav path on the overlay nav to the given path.
             *
             * @param {string} path - The path to set.
             * @returns {undefined}
             */
            setNavActive(path) {
                this.$refs['ds_overlay'].setNavActive(path);
            }

        },
        mounted() {

            // Update from the window data
            for (const key of Object.keys(this._data)) {
                if (window['dynamicsuite'].hasOwnProperty(key)) {
                    this[key] = window['dynamicsuite'][key];
                }
            }

            // Display the content (hidden until all loaded)
            document.getElementById('dynamicsuite').style.display = 'flex';

            // Hash linking for targets
            if (location.hash) {
                location.href = location.hash;
            }

            // Dynamic Suite initialized
            document.dispatchEvent(new CustomEvent('dynamicsuite-init', {
                detail: {
                    vm: this
                }
            }));

        }
    });
});