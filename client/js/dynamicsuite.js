/*
 * Dynamic Suite
 * Copyright (C) 2020 Dynamic Suite Team
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation version 3.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software Foundation,
 * Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301  USA
 */

// noinspection JSUnusedGlobalSymbols

/**
 * Class DynamicSuite.
 */
class DynamicSuite
{

    /**
     * Call a Dynamic Suite API.
     *
     * @param package_id
     * @param api_id
     * @param data
     * @param callback
     * @returns void
     */
    static call(package_id, api_id, data, callback) {
        fetch(`/dynamicsuite/api/${package_id}/${api_id}`, {
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
     * Initialize a Dynamic Suite view.
     *
     * If the view does not show the navigation, this function will return false.
     *
     * @returns boolean
     */
    static initView() {
        this.nav = document.getElementById('ds-nav-container');
        if (this.nav === null) {
            return false;
        }
        this.view = document.getElementById('ds-view-container');
        this.groups = document.getElementsByClassName('ds-nav-group');
        this.mobile_toggle_btn = document.getElementById('ds-nav-mobile-toggle');
        window.onresize = () => {
            if (!this.isMobile()) {
                this.nav.classList.remove('ds-nav-show-mobile');
            }
        };
        for (let i = 0, c = this.groups.length; i < c; i++) {
            this.groups[i].addEventListener('click', () => {
                let hidden = this.groups[i].nextElementSibling.classList.contains("ds-hide"),
                    sublinks = this.groups[i].nextElementSibling.classList,
                    chevron = this.groups[i].lastElementChild.classList;
                this.clearNav();
                this.groups[i].classList.add('ds-nav-selected');
                sublinks.replace(
                    hidden ? 'ds-hide' : 'ds-show',
                    hidden ? 'ds-show' : 'ds-hide'
                );
                chevron.replace(
                    hidden ? 'fa-chevron-right' : 'fa-chevron-down',
                    hidden ? 'fa-chevron-down' : 'fa-chevron-right'
                );
            });
        }
        this.mobile_toggle_btn.addEventListener('click', () => {
            this.nav.classList.toggle('ds-nav-show-mobile');
        });
    }

    /**
     * Clear the navigation and reset all collapsable groups.
     *
     * @returns void
     */
    static clearNav() {
        for (let i = 0, c = this.groups.length; i < c; i++) {
            this.groups[i].classList.remove('ds-nav-selected');
            this.groups[i].nextElementSibling.classList.remove('ds-show');
            this.groups[i].nextElementSibling.classList.add('ds-hide');
            this.groups[i].lastElementChild.classList.replace('fa-chevron-down', 'fa-chevron-right');
        }
    }

    /**
     * If the current window size is the mobile or desktop version.
     *
     * @returns boolean
     */
    static isMobile() {
        return window.innerWidth < 1280;
    }

    /**
     * Get the page data initialized with the view.
     *
     * @returns Object
     */
    static getPageData() {
        if (typeof window.ds_page_data !== 'undefined') {
            return window.ds_page_data;
        } else {
            return false;
        }
    }

    /**
     * Clear the page data on the view.
     *
     * @returns void
     */
    static clearPageData() {
        window.ds_page_data = false;
    }

}

// Initialize the view
DynamicSuite.initView();

