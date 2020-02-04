<template>
  <v-app-bar v-if="isAuthenticated"
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

    <v-toolbar-items>
        <v-row align="center" class="mx-5">
          <v-tooltip left dark>
            <template v-slot:activator="{ on }">
              <v-avatar class="mr-5" color="red" size="32" v-on="on">
                <span class="white--text">{{ getUser.name.split(" ", 2).map((n)=>n[0]).join("") }}</span>
              </v-avatar>
            </template>
            <span>
              {{ getUser.name }}<br>
              {{ getUser.email }}
            </span>
          </v-tooltip>

          <v-btn icon dark @click="logout" class="ml-2">
            <v-icon color="tertiary">
              mdi-logout
            </v-icon>
            Logout
          </v-btn>
        </v-row>
    </v-toolbar-items>
  </v-app-bar>
</template>

<script>
  import { mapGetters } from 'vuex'

  export default {
    data: () => ({
      title: null,
      responsive: false
    }),

    computed: {
      ...mapGetters(['isAuthenticated', 'getUser']),
      currentRoute() {
        return this.$store.getters.currentRoute
      },
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
      },

      logout() {
        this.$store.dispatch('logout').then(() => {
          this.$router.push({ path: '/login' })
        })
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
