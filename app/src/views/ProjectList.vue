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
                      <v-list-item-subtitle>{{ project.created_at }}</v-list-item-subtitle>
                    </v-list-item-content>

                    <v-list-item-avatar>
                      <v-btn fab color="red" dark :loading="project.deleting" @click="deleteProject(project, i)">
                        <v-icon class="white--text">mdi-delete</v-icon>
                      </v-btn>
                    </v-list-item-avatar>
                </v-list-item>

                <v-divider v-if="i + 1 < projectList.length" :key="'_'+i"></v-divider>
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
      newProject: {},
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

      createProject(project) {
        this.$set(project,'creating', true)
        this.$http({ url: '/project', method: 'post', data: project}).then(response => {
          this.projectList.push(response.data.project)
          this.showMessage(response.data.message, 'success')
          this.$delete(project,'creating')
          this.$refs.form.reset()
        }).catch(error => {
          this.$delete(project,'creating')
          let data = error.response.data || {}
          this.showMessage(data.message || 'Fail to create project', 'error')
        })
      },

      deleteProject(project, index) {
        this.$set(project,'deleting', true)
        this.$http({ url: `/project/${project.id}`, method: 'delete'}).then(response => {
          this.showMessage(response.data.message, 'success')
          this.projectList.splice(index, 1);
          this.$delete(project,'deleting')
        }).catch(error => {
          this.$delete(project,'deleting')
          let data = error.response.data || {}
          this.showMessage(data.message || 'Fail to remove project', 'error')
        })
      },

    },
  }
</script>
