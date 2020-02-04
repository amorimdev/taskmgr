<template>
  <v-app id="inspire">
    <v-content>
  <v-container class="fill-height" fluid>
    <v-row align="center" justify="center">
      <v-col cols="12" sm="8" md="4">
        <v-card class="elevation-12">
          <v-toolbar color="orange darken-1" dark flat>
            <v-toolbar-title>Login form</v-toolbar-title>
            <v-spacer />
          </v-toolbar>
          <v-card-text>
            <v-form v-model="valid" ref="form">
              <v-text-field label="E-mail" :rules="rules.email" v-model="formData.email" name="email" type="email" />
              <v-text-field label="Password"  :rules="rules.password"  v-model="formData.password" name="password" type="password"/>
            </v-form>
          </v-card-text>
          <v-card-actions>
            <v-spacer />
            <v-btn :disabled="!valid" @click="login()" color="orange darken-1" :loading="loading">Login</v-btn>
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
    rules: {
      email: [
        v => !!v || 'E-mail is required',
        v => /.+@.+\..+/.test(v) || 'E-mail must be valid',
      ],
      password: [
        v => !!v || 'Password is required',
        v => (v && v.length >= 6) || 'Password must be greater than 6 characters',
      ]
    }
  }),

  methods: {
    login() {
      if (this.$refs.form.validate()) {
        this.loading = true

        this.$store.dispatch('login', this.formData).then(() => {
          this.loading = false
          this.$router.push({ path: '/' })
        }).catch(error => {
          this.loading = false
          console.log(error.response.data)
        })

      }
    }
  }
}
</script>
