import axios from 'axios'
import store from '../store'

const http = axios.create({
  baseURL: '/api',
  timeout: 0
})

http.interceptors.request.use(config => {
  if (store.getters.getToken) {
    config.headers['Authorization'] = 'Bearer ' + store.getters.getToken
  }
  return config
}, error => {
  console.log(error) // for debug
  Promise.reject(error)
})

http.interceptors.response.use(
  response => response,
  error => {
    if ([400, 401].includes(error.response.status)) {
      store.dispatch('logout')
      window.location.href = '/'
      return
    } else {
      return Promise.reject(error)
    }
  }
)

export default http
