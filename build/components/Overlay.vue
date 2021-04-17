<template>
  <div id="ds-overlay">
    <div class="nav-button">
      <i class="fas fa-bars"></i>
    </div>
    <div class="title">
      {{$root.overlay_title}}
    </div>
    <div class="actions">
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
  width: 100%
  height: $size-slim
  background: lighten($color-primary, 10%)
  color: $color-text-inverted
  font-size: calc(#{$size-slim} * 0.33)
  font-weight: bold

  /* Overlay standards */
  & > .nav-button, & > .title, & > .actions
    display: inline-flex
    justify-content: center
    align-items: center

  /* Overlay buttons */
  & > .nav-button, & > .actions
    min-width: $size-slim
    min-height: $size-slim

  /* Overlay title */
  & > .title
    padding: 0 1rem
    flex: 1 1 0
    white-space: nowrap
    overflow: hidden
    text-overflow: ellipsis

</style>