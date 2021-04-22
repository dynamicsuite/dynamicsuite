<template>
  <div id="ds-overlay">
    <div id="ds-nav-button" class="interactive button">
      <i class="fas fa-bars"></i>
    </div>
    <h1 id="ds-title" class="clip-text centered">
      {{$root.overlay_title}}
    </h1>
    <div id="ds-actions-button" class="interactive button">
      <i class="fas fa-user"></i>
    </div>
    <div id="ds-nav">
      <header class="interactive clip-text centered" @click="goto($root.overlay_nav_header_view)">
        {{$root.overlay_nav_header_text}}
      </header>
      <nav>
        Nav
      </nav>
      <footer class="interactive clip-text centered" @click="goto($root.overlay_nav_footer_view)">
        {{$root.overlay_nav_footer_text}}
      </footer>
    </div>
  </div>
</template>

<script>
export default {
  methods: {

    /**
     * Goto the given URL.
     *
     * @param {string} url - The URL to redirect to.
     */
    goto(url) {
      document.location = url;
    }

  }
}
</script>

<style lang="sass">

@import "../sass/dynamicsuite"

/* Overlay container */
#ds-overlay
  display: flex
  align-items: center
  height: $size-slim
  background: lighten($color-primary, 10%)
  color: $color-text-inverted

  /* Overlay interactive component */
  .interactive
    cursor: pointer

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
    transition: background 0.2s ease
    color: inherit
    text-decoration: none

    &:hover
      background: lighten($color-primary, 20%)

  /* Overlay centered */
  .centered
    text-align: center
    padding: 0 1rem
    margin: 0

  /* Overlay title containers */
  #ds-title, #ds-nav header
    line-height: $size-slim
    font-size: calc(#{$size-slim} * 0.33)
    font-weight: bold

  /* Overlay title */
  #ds-title, #ds-nav nav
    flex-grow: 1

  /* Navigation */
  #ds-nav
    display: flex
    flex-direction: column
    position: absolute
    overflow: hidden
    width: $size-wide
    height: calc(100vh - #{$size-slim})
    top: $size-slim
    background: darken($color-primary, 20%)

    /* Nav header */
    header
      background: $color-primary

    /* Navigation footer */
    footer
      line-height: calc(#{$size-slim} / 2)
      font-size: calc(#{$size-slim} * 0.25)
      background: darken($color-primary, 25%)

      &:hover
        text-decoration: underline

</style>