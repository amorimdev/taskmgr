import Vue from 'vue'
import App from './App.vue'
import router from './router'
import store from './store'
import vuetify from './plugins/vuetify';
import http from './plugins/axios';
import './plugins/filters';

Vue.config.productionTip = false
Vue.prototype.$http = http

Vue.prototype.$showMessage = (text, color)  => {
  store.dispatch('notify', {
    text: text,
    color: color,
    show: true
  })
}

new Vue({
  router,
  store,
  vuetify,
  render: h => h(App)
}).$mount('#app')
