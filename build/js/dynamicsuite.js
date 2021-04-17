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

}

/**
 * Initialize Dynamic Suite Vue.
 */
DynamicSuite.vm = new Vue({
    name: 'DynamicSuite',
    el: '#dynamicsuite',
    data() {
        return {
            has_session: false,
            hide_overlay: true,
            overlay_nav_tree: null,
            overlay_title: null,
            overlay_actions: null
        };
    },
    mounted() {
        if (typeof window['ds_window_data'] !== 'object') {
            return;
        }
        for (const key of ['has_session', 'hide_overlay', 'overlay_nav_tree', 'overlay_title', 'overlay_actions']) {
            if (typeof window['ds_window_data'][key] !== 'undefined') {
                this[key] = window['ds_window_data'][key];
            }
        }
    }
});