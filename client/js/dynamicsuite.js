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
        if (window['dynamicsuite'].hasOwnProperty('custom') && window['dynamicsuite']['custom'].hasOwnProperty(key)) {
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
     * Set the given key and given value to the URL data.
     *
     * @param {string} key - The key to add.
     * @param {string|number} value - The value to add.
     * @param {boolean} push_state - If the state should be pushed or replaced in history.
     * @returns {undefined}
     */
    static setURLSavedData(key, value, push_state = true) {
        const url = new URLSearchParams(window.location.search);
        url.set(key, value);
        const params = url.toString();
        const new_url = `${window.location.pathname}?${params}`;
        if (push_state) {
            history.pushState({}, null, new_url);
        } else {
            history.replaceState({}, null, new_url);
        }
    }
    
     /**
     * Delete the given key from the URL data.
     *
     * @param {string} key - The key to remove.
     * @param {boolean} push_state - If the state should be pushed or replaced in history.
     * @returns {undefined}
     */
     static deleteURLSavedData(key, push_state = true) {
        const url = new URLSearchParams(window.location.search);
        url.delete(key);
        const params = url.toString();
        const new_url = params
          ? `${window.location.pathname}?${params}`
          : window.location.pathname;
        if (push_state) {
            history.pushState({}, null, new_url);
        } else {
            history.replaceState({}, null, new_url);
        }
    }

    /**
     * Clear the given keys from the URL data.
     *
     * Replaces the current history state.
     *
     * @param {string[]} keys - The keys to clear.
     * @returns {undefined}
     */
    static clearURLSavedData(keys) {
        const url = new URLSearchParams(window.location.search);
        for (const key of keys) {
            url.delete(key);
        }
        const params = url.toString();
        const new_url = params
          ? `${window.location.pathname}?${params}`
          : window.location.pathname;
        history.replaceState({}, null, new_url);
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