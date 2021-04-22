<template>
  <div id="ds-overlay">
    <header v-if="show_nav" class="interactive clip-text" @click="goto($root.overlay_nav_header_view)">
      {{$root.overlay_nav_header_text}}
    </header>
    <div id="ds-nav-button" class="interactive button" @click="toggleNav">
      <i class="fas fa-bars"></i>
    </div>
    <h1 id="ds-title" class="clip-text centered">
      {{$root.overlay_title}}
    </h1>
    <div id="ds-actions-button" class="interactive button">
      <i class="fas fa-user"></i>
    </div>
    <div id="ds-nav" :class="nav_classes">
      <ul>
        <li v-for="(super_link, super_path) in $root.overlay_nav_tree" :key="'superlink' + super_path">
          <span
            class="interactive"
            :class="activeClass(super_link)"
            @click="goto(super_path)"
          >
            <i :class="super_link.icon"></i>
            <span>{{super_link.name}}</span>
            <i v-if="isNavGroup(super_link) && !isActive(super_link)" class="fas fa-chevron-right"></i>
            <i v-if="isNavGroup(super_link) && isActive(super_link)" class="fas fa-chevron-down"></i>
          </span>
          <ul v-if="isNavGroup(super_link)">
            <li
              v-for="(sub_link, sub_path) in super_link['views']"
              :key="'sublink' + sub_path"
              class="interactive"
              :class="activeClass(sub_link)"
              @click="goto(sub_path)"
            >
              <i :class="sub_link.icon"></i>
              <span>{{sub_link.name}}</span>
            </li>
          </ul>
        </li>
      </ul>
      <footer class="interactive clip-text centered" @click="goto($root.overlay_nav_footer_view)">
        {{$root.overlay_nav_footer_text}}
      </footer>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      show_nav: false,
      view: null
    };
  },
  computed: {

    /**
     * Classes to append to the navigation bar.
     */
    nav_classes() {
      return {
        'show': this.show_nav
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
      if (!url.startsWith('/')) {
        return;
      }
      document.location = url;
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
     * Check if the given nav entry is a group.
     *
     * @param {object} entry - The nav entry.
     * @returns {boolean}
     */
    isNavGroup(entry) {
      return entry.hasOwnProperty('views');
    },

    /**
     * Check to see if the current nav entry is active.
     *
     * @param {object} entry - The nav entry.
     * @returns {boolean}
     */
    isActive(entry) {
      let active = false;
      if (entry.hasOwnProperty('path') && entry.path === this.view) {
        active = true;
      } else if (entry.hasOwnProperty('views')) {
        for (const key in entry.views) {
          if (!entry.views.hasOwnProperty(key)) {
            continue;
          }
          if (entry.views[key].path === this.view) {
            active = true;
          }
        }
      }
      return active;
    },

    /**
     * Get the active class state for the given nav entry.
     *
     * @param {object} entry - The nav entry.
     * @returns {{active: boolean}}
     */
    activeClass(entry) {
      return {
        'active': this.isActive(entry)
      };
    }

  },
  mounted() {
    const path = window.location.pathname.split('?')[0].split('#')[0];
    this.view = path === '/' ? this.$root.default_view : path;
    document.getElementById('ds-content').addEventListener('click', () => {
      this.show_nav = false;
    });
  }
}
</script>

<style lang="sass">

@import "../sass/dynamicsuite"

/* Overlay container */
#ds-overlay
  display: flex
  align-items: center
  position: fixed
  width: 100%
  height: $size-slim
  background: lighten($color-primary, 10%)
  color: $color-text-inverted

  /* Overlay interactive component */
  .interactive
    cursor: pointer
    user-select: none
    transition: background 0.2s ease
    color: inherit
    text-decoration: none

  /* Overlay text clipping container */
  .clip-text
    white-space: nowrap
    overflow: hidden
    text-overflow: ellipsis

  /* Overlay buttons */
  .button
    display: inline-flex
    min-width: $size-slim
    min-height: $size-slim
    justify-content: center
    align-items: center

    &:hover
      background: lighten($color-primary, 20%)

  /* Overlay centered */
  .centered
    text-align: center
    padding: 0 1rem
    margin: 0

  /* Nav header */
  header
    display: flex
    justify-content: center
    flex-shrink: 0
    width: $size-wide
    height: $size-slim
    background: $color-primary
    padding: 0

  /* Overlay title containers */
  #ds-title, header
    line-height: $size-slim
    font-size: $size-slim-third
    font-weight: bold

  /* Overlay title */
  #ds-title, #ds-nav > ul
    flex-grow: 1

  /* Navigation */
  #ds-nav
    display: none
    flex-direction: column
    position: fixed
    overflow: hidden
    width: $size-wide
    height: calc(100vh - #{$size-slim})
    top: $size-slim
    background: darken($color-primary, 20%)

    /* Nav toggle */
    &.show
      display: flex

    /* Nav lists */
    ul
      padding: 0
      margin: 0
      list-style: none

    /* Superlinks */
    & > ul > li
      display: flex
      flex-direction: column
      font-size: $size-slim-third

      /* Superlink content */
      & > span
        display: flex
        flex-shrink: 0
        align-items: center
        height: $size-slim

        &.active
          background: lighten($color-primary, 5%)

        &:hover
          background: lighten($color-primary, 5%)

        /* Superlink icons */
        & > i:first-of-type, & > i.fas.fa-chevron-right, & > i.fas.fa-chevron-down
          display: flex
          justify-content: center
          align-items: center
          height: $size-slim

        /* Superlink primary icon */
        & > i:first-of-type
          width: $size-slim
          font-size: calc(#{$size-slim-third} * 0.9)

        /* Superlink chevrons */
        & > i.fas.fa-chevron-right, & > i.fas.fa-chevron-down
          font-size: $size-slim-quarter
          width: $size-slim-two-third
          margin-left: auto

      /* Sublinks */
      li
        display: flex
        align-items: center
        height: $size-slim-two-third
        font-size: $size-slim-quarter
        background: darken($color-primary, 15%)

        &.active
          background: darken($color-primary, 8%)

        &:hover
          background: darken($color-primary, 5%)

        /* Sublink primary icon */
        & > i:first-of-type
          font-size: $size-slim-quarter
          text-align: center
          width: $size-slim-third
          margin: 0 0.75rem 0 2rem

    /* Nav footer */
    footer
      line-height: $size-slim-half
      font-size: $size-slim-quarter
      background: darken($color-primary, 25%)

      &:hover
        text-decoration: underline

</style>