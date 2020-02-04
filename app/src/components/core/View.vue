<template>
  <v-content class="grey lighten-3">
    <div id="core-view">
      <v-fade-transition mode="out-in">
        <router-view />
      </v-fade-transition>
    </div>

    <v-snackbar v-if="notification" :top="true" v-model="notification.show" :multi-line="true" :color="notification.color">
      {{ notification.text }}
      <v-btn text @click="closeNotification">Close</v-btn>
    </v-snackbar>
  </v-content>
</template>

<script>
  import { mapGetters } from 'vuex'

  export default {
    name: 'CoreView',

    metaInfo () {
      return {
        title: 'TaskMgr'
      }
    },

    methods: {
      closeNotification() {
        this.$store.commit('addNotification', null)
      }
    },

    computed: {
      ...mapGetters(['notification']),
    },
  }
</script>
