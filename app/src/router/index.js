import Vue from 'vue'
import VueRouter from 'vue-router'
import store from '../store'

Vue.use(VueRouter)

const routes = [
  {
    path: '/login',
    name: 'Login',
    component: () => import(/* webpackChunkName: "about" */ '../views/Login.vue')
  },
  {
    path: '/register',
    name: 'Register',
    component: () => import(/* webpackChunkName: "about" */ '../views/Register.vue')
  },
  {
    path: '/',
    name: 'Tasks',
    component: () => import(/* webpackChunkName: "tasklist" */ '../views/TaskList.vue'),
    meta: {
      secure: true
    }
  },
  {
    path: '/projects',
    name: 'Projects',
    component: () => import(/* webpackChunkName: "about" */ '../views/ProjectList.vue'),
    meta: {
      secure: true
    }
  }
]

const router = new VueRouter({
  mode: 'history',
  base: process.env.BASE_URL,
  routes
})

router.beforeEach((to, from, next) => {

  store.commit('setCurrentRoute', to)

  let token = localStorage.getItem('token')
  let user = localStorage.getItem('user')

  if (!store.getters.getToken && token) {
    store.commit('setToken', token)
    store.commit('setUser', JSON.parse(user))
  }

  if (store.getters.isAuthenticated) {
    if (!store.getters.taskList) {
      store.dispatch('loadTasks')
    }

    if (!store.getters.projectList) {
      store.dispatch('loadProjects')
    }
  }

  if (to.meta.secure && !store.getters.isAuthenticated) {
    next('/login')
  } else {
    next()
  }
})

export default router
