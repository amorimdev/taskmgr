<template>
  <v-app>
    <core-app-bar v-if="isAuthenticated" />

    <core-drawer v-if="isAuthenticated"/>

    <core-view />
  </v-app>
</template>

<script>
  import { mapGetters } from 'vuex'
  export default {
    components: {
      CoreDrawer: () => import('@/components/core/Drawer'),
      CoreAppBar: () => import('@/components/core/AppBar'),
      CoreView: () => import('@/components/core/View')
    },
    computed: {
      ...mapGetters(['isAuthenticated'])
    }
  }
</script>
