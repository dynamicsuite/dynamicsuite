<!--
This file is part of the Dynamic Suite framework.

For the full copyright and license information, please view the LICENSE
file that was distributed with this source code.

@package DynamicSuite
@author Grant Martin <commgdog@gmail.com>
@copyright 2021 Dynamic Suite Team
-->

<template>
  <div class="ds-overlay">

    <!-- Nav button -->
    <div class="header-column">
      <div :class="nav_button_classes" @click="toggleNav">
        <i class="fas fa-bars"></i>
      </div>
    </div>

    <!-- Overlay title -->
    <h1 class="title header-column">
      {{overlay_title}}
    </h1>

    <!-- Actions button -->
    <div class="actions header-column">
      <!--suppress HtmlUnknownTag, JSUnusedLocalSymbols, JSUnresolvedVariable -->
      <component v-for="(name, key) in overlay_actions" :is="name" :key="'action' + key"></component>
    </div>

    <!-- Navigation container -->
    <div v-if="show_nav" class="nav">

      <!-- Nav links -->
      <div class="links">
        <div v-for="(superlink, super_id) in overlay_nav_tree" :key="superlink.key" class="link-group">
          <span :class="superlinkClasses(superlink)" @click="superlinkInteraction(superlink)">
            <i :class="navIconClass(superlink.icon, superlink.path)"></i>
            <span>{{superlink.name}}</span>
            <span class="alerts">
              <span v-if="getAlert(super_id, 'failure')" class="failure">{{getAlert(super_id, 'failure')}}</span>
              <span v-if="getAlert(super_id, 'warning')" class="warning">{{getAlert(super_id, 'warning')}}</span>
              <span v-if="getAlert(super_id, 'success')" class="success">{{getAlert(super_id, 'success')}}</span>
            </span>
            <i v-if="superlink.nav_group" :class="chevronClasses(superlink.nav_group)"></i>
          </span>
          <div v-if="superlink.nav_group === selected_group" class="sublinks">
            <span
              v-for="(sublink, sub_id) in superlink.views"
              :key="sublink.key"
              :class="sublinkClasses(sublink)"
              @click="goto(sublink.path)"
            >
              <i :class="navIconClass(sublink.icon, sublink.path)"></i>
              <span>{{sublink.name}}</span>
              <span class="alerts">
                <span v-if="getAlert(sub_id, 'failure')" class="failure">{{getAlert(sub_id, 'failure')}}</span>
                <span v-if="getAlert(sub_id, 'warning')" class="warning">{{getAlert(sub_id, 'warning')}}</span>
                <span v-if="getAlert(sub_id, 'success')" class="success">{{getAlert(sub_id, 'success')}}</span>
              </span>
            </span>
          </div>
        </div>
      </div>

      <!-- Nav footer -->
      <footer v-if="overlay_nav_footer_text" @click="goto(overlay_nav_footer_view)">
        {{overlay_nav_footer_text}}
      </footer>

    </div>

  </div>
</template>

<script>
// noinspection JSValidateTypes
export default {
  props: {

    /**
     * The default view for URL aliasing.
     *
     * @type {string | null}
     */
    default_view: {
      type: String | null,
      default: null
    },

    /**
     * The navigation tree to render.
     *
     * @type {{
     *  active: boolean,
     *  icon: string,
     *  nav_group: string | null,
     *  key: number,
     *  name: string,
     *  path: string,
     *  selected: boolean
     *  views: {
     *    active: boolean,
     *    icon: string,
     *    key: number,
     *    name: string,
     *    path: string
     *  }
     * }}
     */
    overlay_nav_tree: {
      type: Array | Object,
      default: () => []
    },

    /**
     * The text to display on the nav footer.
     *
     * @type {string | null}
     */
    overlay_nav_footer_text: {
      type: String | null,
      default: null
    },

    /**
     * The view URL to redirect to on footer click.
     *
     * @type {string | null}
     */
    overlay_nav_footer_view: {
      type: String | null,
      default: null
    },

    /**
     * Success alerts for the nav.
     *
     * @type {object | array}
     */
    overlay_nav_alert_success: {
      type: Object | Array,
      default: () => ({})
    },

    /**
     * Warning alerts for the nav.
     *
     * @type {object | array}
     */
    overlay_nav_alert_warning: {
      type: Object | Array,
      default: () => ({})
    },

    /**
     * Failure alerts for the nav.
     *
     * @type {object | array}
     */
    overlay_nav_alert_failure: {
      type: Object | Array,
      default: () => ({})
    },

    /**
     * The title to display on the overlay header.
     *
     * @type {string | null}
     */
    overlay_title: {
      type: String | null,
      default: null
    },

    /**
     * Actions to render in the overlay action area (top right of header).
     *
     * This is an array of component names of components to render.
     *
     * @type {string[]}
     */
    overlay_actions: {
      type:  Array,
      default: () => []
    }

  },
  data() {
    return {
      show_nav: false,
      selected_group: null,
      pending_path: null,
      loading: false
    };
  },
  computed: {

    /**
     * Classes to assign to the nav toggle button.
     *
     * @returns {{
     *   'button': boolean,
     *   'interactive': boolean,
     *   'active': boolean
     * }}
     */
    nav_button_classes() {
      return {
        'button': true,
        'interactive': true,
        'active': this.show_nav
      };
    }

  },
  methods: {

    /**
     * Goto the given URL.
     *
     * @param {string} url - The URL to redirect to.
     * @returns {undefined}
     */
    goto(url) {
      if (url) {
        this.pending_path = url;
        setTimeout(() => {
          this.loading = true;
        }, 100);
        document.location = url;
      }
    },

    /**
     * Toggle the navigation bar.
     *
     * @returns {undefined}
     */
    toggleNav() {
      if (this.pending_path) {
        return;
      }
      this.show_actions = false;
      this.show_nav = !this.show_nav;
    },

    /**
     * Classes to apply to the nav group chevron.
     *
     * @returns {
     *   {'fas': boolean},
     *   {'fa-chevron-right': boolean},
     *   {'fa-chevron-down': boolean}
     * }
     */
    chevronClasses(group) {
      return {
        'fas': true,
        'fa-chevron-right': group !== this.selected_group,
        'fa-chevron-down': group === this.selected_group
      }
    },

    /**
     * The classes assigned to the given superlink.
     *
     * @param {object} superlink - The superlink given.
     * @returns {{
     *   'superlink': boolean,
     *   'active': boolean,
     *   'selected': boolean
     * }}
     */
    superlinkClasses(superlink) {
      return {
        'superlink': true,
        'active': superlink.active,
        'selected': superlink.nav_group !== null && this.selected_group === superlink.nav_group
      };
    },

    /**
     * Handle the interaction of the given superlink.
     *
     * @param {object} superlink - The superlink given.
     * @returns {undefined}
     */
    superlinkInteraction(superlink) {
      if (superlink.hasOwnProperty('path')) {
        this.goto(superlink.path);
      } else if (this.selected_group === superlink.nav_group) {
        this.selected_group = null;
      } else {
        this.selected_group = superlink.nav_group;
      }
    },

    /**
     * The classes assigned to the given sublink.
     *
     * @param {object} sublink - The sublink given.
     * @returns {{
     *   'sublink': boolean,
     *   'active': boolean
     * }}
     */
    sublinkClasses(sublink) {
      return {
        'sublink': true,
        'active': sublink.active
      };
    },

    /**
     * Get the icon class for the given icon and clicked path.
     *
     * Used for high latency connections to display a spinner.
     *
     * @param {string} icon - The given icon.
     * @param {string} path - The given path.
     * @returns {string}
     */
    navIconClass(icon, path) {
      if (this.loading && path === this.pending_path) {
        return 'fas fa-circle-notch fa-spin';
      } else {
        return icon;
      }
    },

    /**
     * Get the alert for the given ID.
     *
     * Returns FALSE if no alert is found for the given ID and type.
     *
     * @param {string} id - The ID of the superlink, sublink, or nav group.
     * @param {string} type - The type of the alert (success, warning, failure).
     * @returns {boolean|*}
     */
    getAlert(id, type) {
      const key = `overlay_nav_alert_${type}`;
      if (typeof this[key] === 'object' && this[key].hasOwnProperty(id)) {
        return this[key][id];
      } else {
        return false;
      }
    },

    /**
     * Set the active nav path.
     *
     * @param {string|null} path - The path to set, if omitted, the current URL path will be used.
     * @returns {undefined}
     */
    setNavActive(path = null) {
      let internal_path;
      if (!path) {
        internal_path = window.location.pathname.split('?')[0].split('#')[0];
      } else {
        internal_path = path.split('?')[0].split('#')[0];
      }
      this.active_view = internal_path;
      const view = internal_path === '/' ? this.default_view : internal_path;
      if (path) {
        history.pushState({}, '', path);
      }
      this.selected_group = null;
      for (const superlink in this.overlay_nav_tree) {
        if (
            this.overlay_nav_tree[superlink].hasOwnProperty('path') &&
            this.overlay_nav_tree[superlink].path.startsWith('/')
        ) {
          this.$root.overlay_nav_tree[superlink].active = this.overlay_nav_tree[superlink].path === view;
        } else {
          for (const sublink in this.overlay_nav_tree[superlink].views) {
            const condition = this.overlay_nav_tree[superlink].views[sublink].path === view;
            this.$root.overlay_nav_tree[superlink].active = condition;
            this.$root.overlay_nav_tree[superlink].views[sublink].active = condition;
            if (condition) {
              this.selected_group = this.overlay_nav_tree[superlink].nav_group;
            }
          }
        }
      }
    }

  },
  mounted() {

    // Set the active nav entry.
    this.setNavActive();

    // Add click to hide nav/actions menu
    document.getElementById('ds-content').addEventListener('click', () => {
      if (this.pending_path) {
        return;
      }
      this.show_nav = false;
      this.show_actions = false;
    });

  }
}
</script>

<style lang="sass">

@import "../sass/dynamicsuite"

/* Overlay container */
.ds-overlay
  display: flex
  justify-content: space-between
  align-items: center
  position: fixed
  width: 100%
  height: $size-slim
  background: lighten($color-primary, 10%)
  color: $color-text-inverted
  user-select: none
  z-index: 10

  /* Header columns */
  & > .header-column
    flex: 1 0 0

    /* Overlay buttons */
    & > .button
      display: inline-flex
      font-size: $size-slim-third
      background: inherit
      min-width: $size-slim
      min-height: $size-slim
      justify-content: center
      align-items: center

      &:hover, &.active
        cursor: pointer
        transition: background 0.2s ease
        background: lighten($color-primary, 20%)

  /* Nav header and title */
  & > .title
    display: inline-flex
    justify-content: center
    align-items: center
    font-size: $size-slim-third
    padding: 0 1rem
    white-space: nowrap
    text-overflow: ellipsis

  /* Overlay actions */
  & > .actions
    display: inline-flex
    justify-content: flex-end
    align-items: center
    height: $size-slim

  /* Nav container */
  & > .nav
    position: fixed
    display: flex
    flex-direction: column
    top: $size-slim
    width: $size-wide
    height: calc(100% - #{$size-slim})
    background: darken($color-primary, 20%)
    color: $color-text-inverted-soft

    /* Nav links */
    .links
      display: flex
      flex-grow: 1
      flex-direction: column
      overflow-y: scroll
      overflow-x: hidden
      overflow: -moz-scrollbars-none
      -ms-overflow-style: none

      &::-webkit-scrollbar
        display: none

      /* Nav alerts */
      .alerts
        font-size: $size-slim-quarter
        margin-left: auto
        white-space: nowrap

        /* When not part of a group */
        &:last-child
          margin-right: $size-slim-eight

        /* Alert content */
        span
          display: inline-flex
          justify-content: center
          align-items: center
          padding: $size-slim-sixteenth
          margin-right: $size-slim-sixteenth
          min-width: $size-slim-third
          border-radius: 15%

        .success
          color: $color-text-inverted
          background: $color-success

        .warning
          color: $color-text
          background: $color-warning

        .failure
          color: $color-text-inverted
          background: $color-failure

    /* Nav link group */
    .link-group
      display: flex
      flex-direction: column
      font-size: $size-slim-third
      cursor: pointer

    /* Superlink */
    .superlink
      display: flex
      flex-shrink: 0
      align-items: center
      height: $size-slim

      /* Link content (text) */
      & > span:first-of-type
        white-space: nowrap
        overflow: hidden
        text-overflow: ellipsis
        padding-right: $size-slim-eight

      &:hover
        background: lighten($color-primary, 5%)

      &.active
        background: lighten($color-primary, 5%)

      &.selected
        background: lighten($color-primary, 10%)

      /* Superlink icons */
      & > i
        display: flex
        justify-content: center
        flex-shrink: 0
        align-items: center
        height: $size-slim

      /* Superlink primary icon */
      & > i:first-of-type
        width: $size-slim
        font-size: calc(#{$size-slim-third} * 0.9)

      /* Superlink chevrons */
      & > i:not(:first-of-type).fa-chevron-right,
      & > i:not(:first-of-type).fa-chevron-down
        font-size: $size-slim-quarter
        width: $size-slim-two-third

    /* Sublink container */
    .sublinks
      display: flex
      flex-direction: column

    /* Sublink */
    .sublink
      display: flex
      align-items: center
      height: $size-slim-two-third
      font-size: $size-slim-quarter
      background: darken($color-primary, 15%)

      &.active
        background: darken($color-primary, 8%)

      &:hover
        background: darken($color-primary, 5%)

      /* Link content (text) */
      & > span:first-of-type
        white-space: nowrap
        overflow: hidden
        text-overflow: ellipsis
        padding-right: $size-slim-eight

      /* Sublink icon */
      & > i
        font-size: $size-slim-quarter
        text-align: center
        width: $size-slim-third
        margin: 0 $size-slim-quarter 0 $size-slim-two-third

    /* Nav footer */
    footer
      text-align: center
      justify-self: flex-end
      line-height: $size-slim-half
      font-size: $size-slim-quarter
      background: darken($color-primary, 25%)
      flex-shrink: 0
      cursor: pointer

      &:hover
        text-decoration: underline

</style>