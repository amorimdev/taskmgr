import Vue from 'vue'
import moment from 'moment-timezone'

moment.tz.setDefault("UTC");

Vue.filter('str2date', function (value) {
  if ( value ) {
    return moment(String(value)).toDate()
  }
})

Vue.filter('relativeTime', function (value) {
  if ( value ) {
    return moment(String(value)).fromNow()
  }
})

Vue.filter('formatdate', function (value, format) {
  if ( value ) {
    return moment(String(value)).format(format)
  }
})
