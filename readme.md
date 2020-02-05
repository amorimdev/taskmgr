[![pipeline status](https://gitlab.com/eduardo-marcolino/taskmgr/badges/master/pipeline.svg)](https://gitlab.com/eduardo-marcolino/taskmgr/-/commits/master)

[![coverage report](https://gitlab.com/eduardo-marcolino/taskmgr/badges/master/coverage.svg)](https://gitlab.com/eduardo-marcolino/taskmgr/-/commits/master)

# This challenge involves the creation of a multi-user task manager web application.

The application should include the following features:

1. User Registration
2. User Authentication (login/logout)
3. Visualize, add, edit and remove user projects
4. Visualize, add, edit and remove tasks associated with the projects
Requirements:
1. One user may have several projects
2. One user can access his projects only
3. Each project may include multiple tasks
4. Each task must have a description, creation date and finish date
5. The user needs to have a simple option to mark the tasks as completed when accessing the task list
6. Each task should have its termination date visible as a tooltip, if available, and some visual way of identifying
its status
7. A task that was defined as finished should not be edited nor removed
8. When a task or Project is added our deleted, the page should not fully refresh, so that users have a good
experience

## Non funcional requirements

1. The application frontend should be written in Javascript/html/css. Javascript frameworks can be used
(Angular, React, Polymer or others).
2. The application backend can be written in any language the candidate is comfortable with.
3. The authentication and registration layers should be coded and not based on pre-existing modules (such as
Passport on node.js).
4. Ideally, components should be used to promote increased code reusage (react or angular components,
webcomponents or other alternatives)

## Extras:

Devops and online link to view the project
1. Build tools (e.g. grunt or gulp)
2. Unit tests


### Building instructions

```bash
docker build -t registry.gitlab.com/eduardo-marcolino/taskmgr/app:latest -f Dockerfile .
```

```bash
docker build -t registry.gitlab.com/eduardo-marcolino/taskmgr/api:latest -f api/Dockerfile ./api
```
