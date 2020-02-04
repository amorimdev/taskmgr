<template>
  <v-dialog
    v-model="show"
    :max-width="options.width"
    :style="{ zIndex: options.zIndex }"
    @keydown.esc="cancel"
  >
    <v-card dense v-if="task">
      <v-card-title>Editing task</v-card-title>
      <v-card-text class="p-4">
          <v-form ref="form" v-model="valid">
            <v-row>
                <v-col cols="12" md="12">
                <v-text-field dense outlined :rules="rules.required" v-model="task.description" label="Enter the task name" name="description" type="text" />
                <v-select dense outlined :rules="rules.required" :items="projectList" v-model="task.project_id" item-text="name" item-value="id" label="Assign to the project"></v-select>
                </v-col>
            </v-row>
        </v-form>
      </v-card-text>
      <v-card-actions class="pt-0">
        <v-spacer></v-spacer>
        <v-btn @click.native="agree" :disabled="!valid" :loading="task.saving" color="primary darken-1" text>Save</v-btn>
        <v-btn @click.native="cancel" color="grey" text>Cancel</v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>


<script>
export default {
  name: 'TaskEdit',
  data: () => ({
    valid: false,
    dialog: false,
    resolve: null,
    reject: null,
    task: null,
    original: {},
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
    open(task) {
      this.dialog = true
      this.task = task
      this.original = {
        description: task.description,
        project_id:task.project_id
      }
      return new Promise((resolve, reject) => {
        this.resolve = resolve
        this.reject = reject
      })
    },
    agree() {
      this.$set(this.task,'saving', true)
      this.$http({ url: `/task/${this.task.id}`, method: 'put', data: this.task}).then(response => {
        this.$showMessage(response.data.message, 'success')
        this.$delete(this.task,'saving')
        this.resolve(true)
        this.dialog = false
      }).catch(error => {
        this.$delete(this.task,'saving')
        let data = error.response.data || {}
        this.$showMessage(data.message || 'Fail to update task', 'error')
      })
    },
    cancel() {
      this.task.description = this.original.description
      this.task.project_id = this.original.project_id
      this.resolve(false)
      this.dialog = false
    }
  }
}
</script>
