<template>
  <v-container v-if="isAuthenticated">
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
                      <v-list-item-subtitle :class="{'grey--text text--lighten-1': task.is_closed}">Created {{ task.created_at | str2date | relativeTime }} <v-chip x-small>{{ projectKeyVal[task.project_id] }}</v-chip></v-list-item-subtitle>
                    </v-list-item-content>

                    <v-list-item-avatar>
                      <v-tooltip left v-if="task.is_closed">
                        <template v-slot:activator="{ on }">
                          <v-avatar color="green" v-on="on">
                            <v-icon class="white--text">mdi-check</v-icon>
                          </v-avatar>
                        </template>
                        <span>Closed at {{ task.finished_at | str2date | formatdate('MMMM Do YYYY, h:mm:ss a') }}</span>
                      </v-tooltip>

                      <v-menu v-else bottom left>
                        <template v-slot:activator="{ on }">
                          <v-btn icon v-on="on">
                            <v-icon>mdi-dots-vertical</v-icon>
                          </v-btn>
                        </template>

                        <v-list>
                          <v-list-item>
                            <v-list-item-title>
                              <v-btn text small :loading="task.deleting" @click="removeTask(task, i)">
                                <v-icon class="red--text">mdi-delete</v-icon> Delete
                              </v-btn>
                            </v-list-item-title>
                          </v-list-item>
                          <v-list-item>
                            <v-list-item-title>
                              <v-btn  text small :loading="task.editing" @click="editTask(task)">
                                <v-icon class="primary--text">mdi-pen</v-icon> Edit
                              </v-btn>
                            </v-list-item-title>
                          </v-list-item>
                        </v-list>
                      </v-menu>

                    </v-list-item-avatar>
                </v-list-item>

                <v-divider v-if="i + 1 < taskList.length" :key="'_'+i"></v-divider>
              </template>

            </v-list>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>
    <v-confirm ref="confirm"></v-confirm>
    <task-edit ref="taskedit"></task-edit>
  </v-container>
</template>

<script>

  import { mapGetters } from 'vuex'
  import Confirm from '../components/util/Confirm'
  import TaskEdit from '../components/app/TaskEdit'

  export default {

    data: () => ({
      newTask: {},
      valid: false,
      rules: {
        required: [
          v => !!v || 'Required field'
        ]
      }
    }),

    components: {
      'v-confirm': Confirm,
      'task-edit': TaskEdit
    },

    computed: {
      ...mapGetters([
        'taskList',
        'projectList',
        'isAuthenticated'
      ]),
      projectKeyVal() {
        let map = {}
        this.$store.getters.projectList.forEach(project => {
          map[project.id] = project.name
        })
        return map
      }
    },

    methods: {

      closeTask(task) {
        this.$set(task,'closing', true)
        this.$http({ url: `/task/${task.id}/close`, method: 'put'}).then(response => {
          this.$showMessage(response.data.message, 'success')
          task.finished_at = response.data.task.finished_at
          this.$delete(task,'closing')
        }).catch(error => {
          this.$delete(task,'closing')
          let data = error.response.data || {}
          this.$showMessage(data.message || 'Fail to close task', 'error')
        })
      },

      createTask(task) {
        this.$set(task,'creating', true)
        this.$http({ url: '/task', method: 'post', data: task}).then(response => {
          this.taskList.unshift(response.data.task)
          this.$showMessage(response.data.message, 'success')
          this.$delete(task,'creating')
          this.$refs.form.reset()
        }).catch(error => {
          this.$delete(task,'creating')
          let data = error.response.data || {}
          this.$showMessage(data.message || 'Fail to create task', 'error')
        })
      },

      removeTask(task, index) {
        this.$refs.confirm.open('Are you sure to delete?', 'This record can\'t be recovered', { color: 'error' }).then(confirm => {
          if(confirm) {
            this.$set(task,'deleting', true)
            this.$http({ url: `/task/${task.id}`, method: 'delete'}).then(response => {
              this.$showMessage(response.data.message, 'success')
              this.taskList.splice(index, 1);
              this.$delete(task,'deleting')
            }).catch(error => {
              this.$delete(task,'deleting')
              let data = error.response.data || {}
              this.$showMessage(data.message || 'Fail to close task', 'error')
            })
          }
        })
      },

      editTask(task) {
        this.$refs.taskedit.open(task)
      }

    },
  }
</script>
