<template>
  <v-container>
    <v-row justify="center">
      <v-col cols="12">
        <v-card dense class="mb-3">
          <v-card-title>New Task</v-card-title>
          <v-card-text>
            <v-form ref="form" v-model="valid">
                <v-row>
                  <v-col cols="12" md="7">
                    <v-text-field dense outlined :rules="rules.required" v-model="newTask.description" label="Enter the task description" name="description" type="text" />
                  </v-col>
                  <v-col cols="12" md="4">
                    <v-select dense :rules="rules.required" :items="projectList" v-model="newTask.project_id" item-text="name" item-value="id" label="Assign to the project" outlined></v-select>
                  </v-col>
                  <v-col cols="12" md="1">
                      <v-btn :disabled="!valid" @click="createTask(newTask)" fab small color="green" :loading="newTask.creating">
                        <v-icon class="white--text">mdi-plus</v-icon>
                      </v-btn>
                  </v-col>
                </v-row>
            </v-form>
          </v-card-text>
        </v-card>

        <v-card dense>

          <v-card-title>My Tasks</v-card-title>

          <v-card-text>

            <v-list
              multiple
              two-line
            >
              <template v-for="(task, i) in taskList">
                <v-list-item :key="task.id">
                    <v-list-item-action>
                      <v-checkbox v-if="!task.closing" v-on:change="closeTask(task)" color="primary"  v-model="task.is_closed" :disabled="task.is_closed"></v-checkbox>
                      <v-progress-circular v-else :indeterminate="task.closing" :value="0" size="24"></v-progress-circular>
                    </v-list-item-action>

                    <v-list-item-content>
                      <v-list-item-title :class="{'grey--text text--lighten-1': task.is_closed}">{{ task.description }}</v-list-item-title>
                      <v-list-item-subtitle :class="{'grey--text text--lighten-1': task.is_closed}">{{ task.created_at }} - {{ task.finished_at }} <v-chip x-small>{{ task.project.name }}</v-chip></v-list-item-subtitle>
                    </v-list-item-content>

                    <v-list-item-avatar>
                      <v-btn fab color="red" :loading="task.deleting" @click="removeTask(task, i)" v-if="!task.is_closed">
                        <v-icon class="white--text">mdi-delete</v-icon>
                      </v-btn>
                      <v-avatar v-else  color="green">
                        <v-icon class="white--text">mdi-check</v-icon>
                      </v-avatar>
                    </v-list-item-avatar>
                </v-list-item>

                <v-divider v-if="i + 1 < taskList.length" :key="'_'+i"></v-divider>
              </template>

            </v-list>
          </v-card-text>
        </v-card>

        <v-snackbar v-if="snackbar" :top="true" v-model="snackbar.show" :multi-line="true" :color="snackbar.color">
          {{ snackbar.text }}
          <v-btn text @click="snackbar = null">Close</v-btn>
        </v-snackbar>

      </v-col>
    </v-row>
  </v-container>
</template>

<script>

  import { mapGetters } from 'vuex'

  export default {

    data: () => ({
      newTask: {},
      valid: false,
      rules: {
        required: [
          v => !!v || 'Required field'
        ]
      },
      snackbar: null
    }),

    computed: {
      ...mapGetters([
        'taskList',
        'projectList'
      ]),
    },

    methods: {

      showMessage(text, color) {
        this.snackbar = {
          text: text,
          color: color,
          show: true
        }
      },

      closeTask(task) {
        this.$set(task,'closing', true)
        this.$http({ url: `/task/${task.id}/close`, method: 'put'}).then(response => {
          this.showMessage(response.data.message, 'success')
          task.finished_at = response.data.task.finished_at
          this.$delete(task,'closing')
        }).catch(error => {
          this.$delete(task,'closing')
          let data = error.response.data || {}
          this.showMessage(data.message || 'Fail to close task', 'error')
        })
      },

      createTask(task) {
        this.$set(task,'creating', true)
        this.$http({ url: '/task', method: 'post', data: task}).then(response => {
          this.taskList.push(response.data.task)
          this.showMessage(response.data.message, 'success')
          this.$delete(task,'creating')
          this.$refs.form.reset()
        }).catch(error => {
          this.$delete(task,'creating')
          let data = error.response.data || {}
          this.showMessage(data.message || 'Fail to create task', 'error')
        })
      },

      removeTask(task, index) {
        this.$set(task,'deleting', true)
        this.$http({ url: `/task/${task.id}`, method: 'delete'}).then(response => {
          this.showMessage(response.data.message, 'success')
          this.taskList.splice(index, 1);
          this.$delete(task,'deleting')
        }).catch(error => {
          this.$delete(task,'deleting')
          let data = error.response.data || {}
          this.showMessage(data.message || 'Fail to close task', 'error')
        })
      },

    },
  }
</script>
