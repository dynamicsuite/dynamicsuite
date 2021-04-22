<template>
  <div id="ds-overlay">
    <div class="nav-button">
      <i class="fas fa-bars"></i>
    </div>
    <h1>
      {{$root.overlay_title}}
    </h1>
    <div class="actions-button">
      <i class="fas fa-user"></i>
    </div>
  </div>
</template>

<script>
export default {

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
  font-size: calc(#{$size-slim} * 0.33)
  font-weight: bold

  /* Overlay standards */
  & > .nav-button, & > .actions-button
    display: inline-flex
    justify-content: center
    align-items: center
    transition: background 0.2s ease
    cursor: pointer
    color: inherit
    text-decoration: none

    &:hover
        background: lighten($color-primary, 20%)

  /* Overlay buttons */
  & > .nav-button, & > .actions-button
    min-width: $size-slim
    min-height: $size-slim

  /* Overlay title */
  & > h1
    text-align: center
    flex-grow: 1
    font-size: 1rem
    padding: 0 1rem
    white-space: nowrap
    overflow: hidden
    text-overflow: ellipsis

</style>