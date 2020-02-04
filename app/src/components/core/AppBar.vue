<template>
  <v-app-bar
    id="core-app-bar"
    absolute
    app
    color="orange darken-1"
    flat
    height="65"
  >
    <v-toolbar-title class="align-self-center white--text">

      <v-app-bar-nav-icon dark v-if="responsive" @click.stop="toggleDrawer"></v-app-bar-nav-icon>

      {{ currentRoute.name }}
    </v-toolbar-title>

    <v-spacer />
  </v-app-bar>
</template>

<script>

  export default {
    data: () => ({
      title: null,
      responsive: false
    }),

    watch: {
      '$route' (val) {
        this.title = val.name
      }
    },

    computed: {
      currentRoute() {
        return this.$store.getters.currentRoute
      }
    },

    mounted () {
      this.onResponsiveInverted()
      window.addEventListener('resize', this.onResponsiveInverted)
    },
    beforeDestroy () {
      window.removeEventListener('resize', this.onResponsiveInverted)
    },

    methods: {

      toggleDrawer () {
        this.$store.commit('setDrawer', !this.$store.state.drawer)
      },

      onResponsiveInverted () {
        if (window.innerWidth < 991) {
          this.responsive = true
        } else {
          this.responsive = false
        }
      }
    }
  }
</script>

<style>
  #core-app-bar {
    width: auto;
  }

  #core-app-bar a {
    text-decoration: none;
  }
</style>
