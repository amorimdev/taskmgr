import Vue from 'vue'
import Vuex from 'vuex'
import http from '../plugins/axios'

Vue.use(Vuex)

export default new Vuex.Store({
  state: {
    user: null,
    token: null,
    drawer: null,
    current_route: null,
    tasks: null,
    projects: null
  },
  mutations: {
    setDrawer(state, value) {
      state.drawer = value
    },
    setUser(state, value) {
      state.user = value
    },
    setCurrentRoute(state, value) {
      state.current_route = value
    },
    setToken(state, value) {
      state.token = value
    },
    setTasks(state, value) {
      state.tasks = value
    },
    setProjects(state, value) {
      state.projects = value
    }
  },
  getters: {
    currentRoute(state) {
      return state.current_route
    },
    openedTaskList(state) {
      return state.tasks ? state.tasks.filter(task => !task.is_closed) : []
    },
    taskList(state) {
      return state.tasks
    },
    projectList(state) {
      return state.projects
    },
    isAuthenticated(state) {
      return state.token !== null
    },
    getToken(state) {
      return state.token
    }
  },

  actions: {
    login({ commit }, credentials) {

      return new Promise((resolve, reject) => {
        http({ url: "/login", method: 'post', data: credentials }).then(response => {
          localStorage.setItem("token", response.data.token);
          commit('setToken', response.data.token)
          resolve()
        }).catch(error => {
          reject(error)
        })
      })

    },

    logout({ commit }) {

      return new Promise(resolve => {
        localStorage.removeItem('token')
        commit('setToken', null)
        resolve()
      })

    },

    loadTasks({ commit }) {
      return new Promise((resolve, reject) => {
        http({ url: "/tasks", method: 'get' }).then(response => {
          commit('setTasks', response.data.tasks)
          resolve()
        }).catch(error => {
          reject(error)
        })
      })
    },

    loadProjects({ commit }) {
      return new Promise((resolve, reject) => {
        http({ url: "/projects", method: 'get' }).then(response => {
          commit('setProjects', response.data.projects)
          resolve()
        }).catch(error => {
          reject(error)
        })
      })
    }

  },
})
