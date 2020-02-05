<template>
  <v-container>
    <v-row justify="center">
      <v-col cols="12">
        <v-card dense class="mb-3">
          <v-card-title>New Project</v-card-title>
          <v-card-text>
            <v-form ref="form" v-model="valid">
                <v-row>
                  <v-col cols="12" md="11">
                    <v-text-field dense outlined :rules="rules.required" v-model="newProject.name" label="Enter the project name" name="name" type="text" />
                  </v-col>
                  <v-col cols="12" md="1">
                      <v-btn :disabled="!valid" @click="createProject(newProject)" fab small color="green" :loading="newProject.creating">
                        <v-icon class="white--text">mdi-plus</v-icon>
                      </v-btn>
                  </v-col>
                </v-row>
            </v-form>
          </v-card-text>
        </v-card>

        <v-card dense>

          <v-card-title>My Projects</v-card-title>

          <v-card-text>

            <v-list
              multiple
              two-line
            >
              <template v-for="(project, i) in projectList">
                <v-list-item :key="project.id">
                    <v-list-item-content>
                      <v-list-item-title>{{ project.name }}</v-list-item-title>
                      <v-list-item-subtitle>Created {{ project.created_at | str2date | relativeTime }}</v-list-item-subtitle>
                    </v-list-item-content>

                    <v-list-item-avatar>

                      <v-menu bottom left>
                        <template v-slot:activator="{ on }">
                          <v-btn icon v-on="on">
                            <v-icon>mdi-dots-vertical</v-icon>
                          </v-btn>
                        </template>

                        <v-list>
                          <v-list-item>
                            <v-list-item-title>
                              <v-btn  text small :loading="project.deleting" @click="deleteProject(project, i)">
                                <v-icon class="red--text">mdi-delete</v-icon> Delete
                              </v-btn>
                            </v-list-item-title>
                          </v-list-item>
                          <v-list-item>
                            <v-list-item-title>
                              <v-btn  text small :loading="project.editing" @click="editProject(project)">
                                <v-icon class="primary--text">mdi-pen</v-icon> Edit
                              </v-btn>
                            </v-list-item-title>
                          </v-list-item>
                        </v-list>
                      </v-menu>
                    </v-list-item-avatar>

                    <v-dialog v-model="project.editing" width="500">
                      <v-card>
                        <v-card-title class="headline grey lighten-2" primary-title>
                          Privacy Policy {{ project.name }}
                        </v-card-title>
                      </v-card>
                    </v-dialog>

                </v-list-item>

                <v-divider v-if="i + 1 < projectList.length" :key="'_'+i"></v-divider>
              </template>

            </v-list>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>
    <v-confirm ref="confirm"></v-confirm>
    <project-edit ref="projectedit"></project-edit>
  </v-container>
</template>

<script>

  import { mapGetters } from 'vuex'
  import Confirm from '../components/util/Confirm'
  import ProjectEdit from '../components/app/ProjectEdit'

  export default {

    data: () => ({
      newProject: {},
      valid: false,
      dialog: null,
      rules: {
        required: [
          v => !!v || 'Required field'
        ]
      }
    }),

    components: {
      'v-confirm': Confirm,
      'project-edit': ProjectEdit
    },

    computed: {
      ...mapGetters([
        'projectList',
      ]),
    },

    methods: {

      createProject(project) {
        this.$set(project,'creating', true)
        this.$http({ url: '/project', method: 'post', data: project}).then(response => {
          this.projectList.push(response.data.project)
          this.$showMessage(response.data.message, 'success')
          this.$delete(project,'creating')
          this.$refs.form.reset()
        }).catch(error => {
          this.$delete(project,'creating')
          let data = error.response.data || {}
          this.$showMessage(data.message || 'Fail to create project', 'error')
        })
      },

      deleteProject(project, index) {
        this.$refs.confirm.open('Are you sure to delete?', 'All tasks will be removed. This record can\'t be recovered', { color: 'red' }).then(confirm => {
          if(confirm) {
            this.$set(project,'deleting', true)
            this.$http({ url: `/project/${project.id}`, method: 'delete'}).then(response => {
              this.$showMessage(response.data.message, 'success')
              this.$store.commit('setTasks', this.$store.getters.taskList.filter(task => task.project_id !== project.id))
              this.projectList.splice(index, 1)
              this.$delete(project,'deleting')
            }).catch(error => {
              this.$delete(project,'deleting')
              let data = error.response.data || {}
              this.$showMessage(data.message || 'Fail to remove project', 'error')
            })
          }
        })
      },

      editProject(project) {
        this.$refs.projectedit.open(project)
      }
    },
  }
</script>
