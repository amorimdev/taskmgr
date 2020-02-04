<template>
  <v-container>
    <v-row justify="center">
      <v-col cols="12">
        <v-card dense>
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
                      <v-btn fab color="red" :loading="task.deleting" @click="removeTask(i)" v-if="!task.is_closed">
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
      </v-col>
    </v-row>
  </v-container>
</template>

<script>

  import { mapGetters } from 'vuex'

  export default {

    data: () => ({
    }),

    computed: {
      ...mapGetters([
        'taskList'
      ]),
    },

    methods: {

      closeTask(task) {
        task.closing = true
        this.$http({ url: `/task/${task.id}/close`, method: 'put'}).then(() => {
          task.closing = false
          this.$delete(task,'closing')
        }).catch(error => {
          this.$delete(task,'closing')
          console.log(error.response.data)
        })
      },

      removeTask(index) {
        this.taskList.splice(index, 1);
      },

    },
  }
</script>
