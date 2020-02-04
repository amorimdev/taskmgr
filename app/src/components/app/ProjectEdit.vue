<template>
  <v-dialog
    v-model="show"
    :max-width="options.width"
    :style="{ zIndex: options.zIndex }"
    @keydown.esc="cancel"
  >
    <v-card dense v-if="project">
      <v-card-title>Editing project</v-card-title>
      <v-card-text class="pa-4">
          <v-form ref="form" v-model="valid">
            <v-row>
                <v-col cols="12" md="11">
                <v-text-field :rules="rules.required" dense outlined v-model="project.name" label="Enter the project name" name="name" type="text" />
                </v-col>
            </v-row>
        </v-form>
      </v-card-text>
      <v-card-actions class="pt-0">
        <v-spacer></v-spacer>
        <v-btn @click.native="agree" :disabled="!valid" :loading="project.saving" color="primary darken-1" text>Save</v-btn>
        <v-btn @click.native="cancel" color="grey" text>Cancel</v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>


<script>
export default {
  name: 'ProjectEdit',
  data: () => ({
    valid: false,
    dialog: false,
    resolve: null,
    reject: null,
    project: null,
    project_name: null,
    rules: {
        required: [
            v => !!v || 'Required field'
        ]
    },
    options: {
      color: 'primary',
      width: 500,
      zIndex: 200
    }
  }),
  computed: {
    show: {
      get() {
        return this.dialog
      },
      set(value) {
        this.dialog = value
        if (value === false) {
          this.cancel()
        }
      }
    },
    projectList() {
        return this.$store.getters.projectList
    }
  },
  methods: {
    open(project) {
      this.dialog = true
      this.project = project
      this.project_name = project.name
      return new Promise((resolve, reject) => {
        this.resolve = resolve
        this.reject = reject
      })
    },
    agree() {
      this.$set(this.project,'saving', true)
      this.$http({ url: `/project/${this.project.id}`, method: 'put', data: this.project}).then(response => {
        this.$showMessage(response.data.message, 'success')
        this.$delete(this.project,'saving')
        this.resolve(true)
        this.dialog = false
      }).catch(error => {
        this.$delete(this.project,'saving')
        let data = error.response.data || {}
        this.$showMessage(data.message || 'Fail to update project', 'error')
      })
    },
    cancel() {
      this.project.name = this.project_name
      this.resolve(false)
      this.dialog = false
    }
  }
}
</script>
