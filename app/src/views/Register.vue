<template>
  <v-app id="inspire">
    <v-content>
      <v-container class="fill-height" fluid>
        <v-row align="center" justify="center">
          <v-col cols="12" sm="8" md="4" class="ml-5">
            <v-card class="elevation-12">
              <v-toolbar color="orange darken-1" dark flat>
                <v-toolbar-title>Register form</v-toolbar-title>
                <v-spacer />
              </v-toolbar>
              <v-card-text>
                <v-form v-model="valid" ref="form">
                  <v-text-field label="Name" :rules="rules.required" v-model="formData.name" name="name" type="text" ref="name"/>
                  <v-text-field label="E-mail" :rules="rules.email" v-model="formData.email" name="email" type="email" ref="email"/>
                  <v-text-field label="Password" :rules="rules.password"  v-model="formData.password" name="password" type="password" ref="password"/>
                </v-form>
              </v-card-text>
              <v-card-actions>
                <v-btn to="/login" text>Login</v-btn>
                <v-spacer />
                <v-btn :disabled="!valid" @click="register()" color="orange darken-1" :loading="loading">Register</v-btn>
              </v-card-actions>
            </v-card>
          </v-col>
        </v-row>
      </v-container>
    </v-content>
  </v-app>
</template>

<script>
export default {
  data: () => ({
    loading: false,
    valid: false,
    formData: {},
    remote_errors: {},
    rules: {
      email: [
        v => !!v || 'E-mail is required',
        v => /.+@.+\..+/.test(v) || 'E-mail must be valid',
      ],
      password: [
        v => !!v || 'Password is required',
        v => (v && v.length >= 6) || 'Password must be greater than 6 characters',
      ],
      required: [
        v => !!v || 'Required field'
      ]
    }
  }),

  methods: {

    register() {
      if (this.$refs.form.validate()) {
        this.remote_errors = {}
        this.loading = true
        this.remote_errors

        this.$http({ url: '/register', method: 'post', data: this.formData}).then(response => {
          this.loading = false
          this.$refs.form.reset()
          this.$showMessage(response.data.message, 'success')
          this.$router.push({ path: '/' })
        }).catch(error => {
          this.loading = false
          let data = error.response.data || {}

          let fields_with_error = ' '

          if (data.invalid_form) {
            for (const [key, value] of Object.entries(data.invalid_form)) {
              fields_with_error += key.replace(key, '') + value.join(',')
            }
          }

          this.$showMessage(data.message + fields_with_error || 'Fail to register new user', 'error')
        })

      }
    }
  }
}
</script>
