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
    <div :class="nav_button_classes" @click="toggleNav">
      <i class="fas fa-bars"></i>
    </div>

    <!-- Overlay title -->
    <h1 class="title">
      {{overlay_title}}
    </h1>

    <!-- Actions area -->
    <div class="button interactive">
      <i class="fas fa-user"></i>
    </div>

    <!-- Navigation container -->
    <div v-if="show_nav" class="nav">

      <!-- Nav links -->
      <div class="links">
        <div v-for="superlink in overlay_nav_tree" :key="superlink.key" class="link-group">
          <span :class="superlinkClasses(superlink)" @click="superlinkInteraction(superlink)">
            <i :class="superlink.icon"></i>
            <span>{{superlink.name}}</span>
            <i v-if="superlink.nav_group" :class="chevronClasses(superlink.nav_group)"></i>
          </span>
          <div v-if="superlink.nav_group === selected_group" class="sublinks">
            <span
              v-for="sublink in superlink.views"
              :key="sublink.key"
              :class="sublinkClasses(sublink)"
              @click="goto(sublink.path)"
            >
              <i :class="sublink.icon"></i>
              <span>{{sublink.name}}</span>
            </span>
          </div>
        </div>
      </div>

      <!-- Nav footer -->
      <footer @click="goto(overlay_nav_footer_view)">
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
      type: Array,
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
     * The title to display on the overlay header.
     *
     * @type {string | null}
     */
    overlay_title: {
      type: String | null,
      default: null
    },

    /**
     * Actions to render in the overlay action area.
     *
     * TODO
     */
    overlay_actions: {
      type:  Array,
      default: () => []
    }

  },
  data() {
    return {
      show_nav: false,
      selected_group: null
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
        document.location = url;
      }
    },

    /**
     * Toggle the navigation bar.
     *
     * @returns {undefined}
     */
    toggleNav() {
      this.show_nav = !this.show_nav;
    },

    /**
     * Classes to apply to the nav group chevron.
     *
     * @returns {{
     *   'fas': boolean,
     *   'fa-chevron-right': boolean,
     *   'fa-chevron-down': boolean
     * }}
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
    }

  },
  mounted() {

    // Set active nav entries
    const path = window.location.pathname.split('?')[0].split('#')[0];
    const view = path === '/' ? this.default_view : path;
    for (const superlink in this.overlay_nav_tree) {
      if (
          this.overlay_nav_tree[superlink].hasOwnProperty('path') &&
          this.overlay_nav_tree[superlink].path.startsWith('/')
      ) {
        this.overlay_nav_tree[superlink].active = this.overlay_nav_tree[superlink].path === view;
      } else {
        for (const sublink in this.overlay_nav_tree[superlink].views) {
          if (this.overlay_nav_tree[superlink].views[sublink].path === view) {
            this.overlay_nav_tree[superlink].active = true;
            this.overlay_nav_tree[superlink].views[sublink].active = true;
            this.selected_group = this.overlay_nav_tree[superlink].nav_group;
          }
        }
      }
    }

    // Add click to hide nav menu
    document.getElementById('ds-content').addEventListener('click', () => {
      this.show_nav = false;
    });

  }
}
</script>

<style lang="sass">

@import "../sass/dynamicsuite"

/* Overlay container */
.ds-overlay
  display: flex
  align-items: center
  position: fixed
  width: 100%
  height: $size-slim
  background: lighten($color-primary, 10%)
  color: $color-text-inverted

  /* Interactive elements */
  .interactive
    cursor: pointer
    user-select: none
    transition: background 0.2s ease

  /* Nav header and title */
  .title
    flex-grow: 1
    line-height: $size-slim
    white-space: nowrap
    overflow: hidden
    text-overflow: ellipsis
    text-align: center
    font-size: $size-slim-third
    font-weight: bold
    padding: 0 1rem
    margin: 0

  /* Overlay buttons */
  .button
    display: inline-flex
    font-size: $size-slim-third
    min-width: $size-slim
    min-height: $size-slim
    justify-content: center
    align-items: center

    &:hover, &.active
      background: lighten($color-primary, 20%)

  /* Nav container */
  .nav
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
      margin-left: auto

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