<template>
  <v-navigation-drawer
    id="app-drawer"
    v-if="isAuthenticated"
    v-model="inputValue"
    app
    color="grey darken-4"
    dark
    floating
    mobile-break-point="991"
    persistent
    width="260"
  >
    <v-list-item two-line>
      <v-list-item-title class="title">
        <v-icon :color="active_class">mdi-checkbox-marked-circle-outline</v-icon> TaskMgr
      </v-list-item-title>
    </v-list-item>

    <v-divider class="mx-3 mb-3" />

    <v-list nav dense flat>
      <div />

      <v-list-item  to="/" :active-class="active_class">
        <v-list-item-action>
          <v-badge color="red accent-4" overlap v-if="openedTaskList.length > 0">
            <template slot="badge">{{ openedTaskList.length }}</template>
            <v-icon>mdi-format-list-bulleted</v-icon>
          </v-badge>
          <v-icon v-else>mdi-format-list-bulleted</v-icon>
        </v-list-item-action>

        <v-list-item-title>Task</v-list-item-title>
      </v-list-item>

      <v-list-item  to="/projects" :active-class="active_class">
        <v-list-item-action>
            <v-icon>mdi-package</v-icon>
        </v-list-item-action>
        <v-list-item-title>Projects</v-list-item-title>
      </v-list-item>
    </v-list>

  </v-navigation-drawer>
</template>

<script>

  import { mapGetters } from 'vuex'

  export default {

    data: () => ({
      active_class: 'orange darken-1',
    }),

    computed: {
      ...mapGetters([
        'openedTaskList',
        'isAuthenticated'
      ]),
      inputValue: {
        get () {
          return this.$store.state.drawer
        },
        set (val) {
          this.$store.commit('setDrawer', val)
        }
      }
    },

    methods: {
    }
  }
</script>
