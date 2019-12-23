/*
 * Dynamic Suite
 * Copyright (C) 2019 Dynamic Suite Team
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
let ds = {
    api: {
        call: (package_id, api_id, data, callback) => {
            if (data instanceof FormData) {
                let form_data = {};
                data.forEach((v, k) => { form_data[k] = v });
                data = form_data;
            }
            fetch('/dynamicsuite/api', {
                method: 'POST',
                body: JSON.stringify({package_id: package_id, api_id: api_id, data: data})
            })
            .then(response => response.json())
            .then(json => callback(json), () => callback({
                status: 'SERVER_ERROR',
                message: 'A malformed response was returned',
                data: null
            }))
        },
        data: el => {
            return new FormData(document.querySelector(el))
        }
    },
    body: {
        nav: document.getElementById('ds-nav-container'),
        view: document.getElementById('ds-view-container'),
        groups: document.getElementsByClassName('ds-nav-group'),
        mobileToggleBtn: document.getElementById('ds-nav-mobile-toggle'),
        init: () => {
            if (ds.body.nav === null) return false;
            window.onresize = () => {
                if (!ds.body.isMobile()) ds.body.nav.classList.remove('ds-nav-show-mobile');
            };
            for (let i = 0, c = ds.body.groups.length; i < c; i++) {
                let group = ds.body.groups[i];
                group.addEventListener('click', () => {
                    let hidden = group.nextElementSibling.classList.contains("ds-hide"),
                        sublinks = group.nextElementSibling.classList,
                        chevron = group.lastElementChild.classList;
                    ds.body.clearNav();
                    group.classList.add('ds-nav-selected');
                    sublinks.replace(hidden ? 'ds-hide' : 'ds-show', hidden ? 'ds-show' : 'ds-hide');
                    chevron.replace(
                        hidden ? 'fa-chevron-right' : 'fa-chevron-down',
                        hidden ? 'fa-chevron-down' : 'fa-chevron-right'
                    );
                });
            }
            ds.body.mobileToggleBtn.addEventListener('click', () => {
                ds.body.nav.classList.toggle('ds-nav-show-mobile');
            });
        },
        clearNav: () => {
            for (let i = 0, c = ds.body.groups.length; i < c; i++) {
                let group = ds.body.groups[i];
                group.classList.remove('ds-nav-selected');
                let sublinks = group.nextElementSibling.classList,
                    chevron = group.lastElementChild.classList;
                sublinks.remove('ds-show');
                sublinks.add('ds-hide');
                chevron.replace('fa-chevron-down', 'fa-chevron-right');
            }
        },
        isMobile: () => {
            return window.innerWidth < 1280;
        }
    }
};
ds.body.init();

