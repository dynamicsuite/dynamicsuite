<template>
  <div id="ds-overlay">
    <header v-if="show_nav" class="interactive clip-text" @click="goto($root.overlay_nav_header_view)">
      {{$root.overlay_nav_header_text}}
    </header>
    <div id="ds-nav-button" class="interactive button" @click="toggleNav">
      <i class="fas" :class="{'fa-bars': !show_nav, 'fa-caret-square-left': show_nav}"></i>
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
            :class="{active: super_link.active, selected: selected_group === super_path}"
            @click="handleSuperlinkInteraction(super_path)"
          >
            <i :class="super_link.icon"></i>
            <span>{{super_link.name}}</span>
            <i v-if="super_link['is_group']" class="fas" :class="chevronClasses(super_path)"></i>
          </span>
          <ul v-if="selected_group === super_path">
            <li
              v-for="(sub_link, sub_path) in super_link['views']"
              :key="'sublink' + sub_path"
              :class="{active: sub_link.active}"
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
      selected_group: null
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
     * Classes to apply to the nav group chevron.
     *
     * @returns {object}
     */
    chevronClasses(group) {
      return {
        'fa-chevron-right': group !== this.selected_group,
        'fa-chevron-down': group === this.selected_group
      }
    },

    /**
     * Handle the interaction of the given superlink entry.
     *
     * @param {string} path - The entry path.
     * @returns {undefined}
     */
    handleSuperlinkInteraction(path) {
      if (path.startsWith('/')) {
        this.goto(path);
      } else if (this.selected_group === path) {
        this.selected_group = null;
      } else {
        this.selected_group = path;
      }
    }

  },
  mounted() {

    // Set active nav entries
    const path = window.location.pathname.split('?')[0].split('#')[0];
    const view = path === '/' ? this.$root.default_view : path;
    for (const superlink in this.$root.overlay_nav_tree) {
      if (superlink.startsWith('/')) {
        this.$root.overlay_nav_tree[superlink].active = superlink === view;
      } else {
        for (const sublink in this.$root.overlay_nav_tree[superlink]['views']) {
          if (sublink === view) {
            this.$root.overlay_nav_tree[superlink].active = true;
            this.$root.overlay_nav_tree[superlink]['views'][sublink].active = true;
            this.selected_group = superlink;
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
      color: $color-text-inverted-soft
      cursor: pointer

    /* Superlink list */
    & > ul
      overflow-y: scroll
      overflow-x: hidden
      overflow: -moz-scrollbars-none
      -ms-overflow-style: none
      &::-webkit-scrollbar
        display: none

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
        & > i:not(:first-of-type).fa-chevron-right, & > i:not(:first-of-type).fa-chevron-down
          font-size: $size-slim-quarter
          width: $size-slim-two-third
          margin-left: auto

      /* Sublinks */
      ul

        /* Individual sublinks */
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
      flex-shrink: 0

      &:hover
        text-decoration: underline

</style>