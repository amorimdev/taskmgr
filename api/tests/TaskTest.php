<?php

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Firebase\JWT\JWT;
use App\Models\User;
use App\Models\Project;

class TaskTest extends TestCase
{
    use DatabaseTransactions;

    private function resource() {
        $user = User::create([
            'name' => $this->faker()->name,
            'email' => $this->faker()->email,
            'password' => $this->faker()->password
        ]);

        $token = JWT::encode([
            'sub' => $user->id,
            'iss' => "taskmgr",
            'iat' => time(),
            'exp' => time() + 60*60
        ], env('JWT_KEY'));

        $project = new Project(['name' => $this->faker()->jobTitle]);
        $project->user()->associate($user);
        $project->save();

        return [$user, $token, $project];
    }

    public function testCreateTask()
    {
        list($user, $token, $project) = $this->resource();

        $headers = ['Authorization' => 'Bearer '.$token];
        $data = [
            'description' => $this->faker()->sentence(),
            'project_id' => $project->id
        ];

        $this->json('POST', '/api/task', $data, $headers);
        $this->assertResponseOk();

        $this->seeJsonStructure(['task' => [
            'description',
            'is_closed',
            'created_at',
            'updated_at'
        ]]);

        $this->seeInDatabase('tasks', [
            'description' => $data['description'],
            'project_id' => $data['project_id']
        ]);
    }

    public function testMissingDescriptionCreateTask()
    {
        list($user, $token, $project) = $this->resource();

        $headers = ['Authorization' => 'Bearer '.$token];
        $data = [
            'project_id' => $project->id
        ];

        $this->json('POST', '/api/task', $data, $headers);
        $this->assertResponseStatus(422);
        $this->receiveJson();

        $this->seeJsonStructure([
            'invalid_form' => ['description'],
            'code',
            'message'
        ]);

        $this->seeJson([
            'message' => 'The given data was invalid.',
            'invalid_form' => ['description' => [
                'The description field is required.'
            ]]
        ]);
    }

    public function testMissingProjectIdCreateTask()
    {
        list($user, $token, $project) = $this->resource();

        $headers = ['Authorization' => 'Bearer '.$token];
        $data = [
            'description' => $this->faker()->sentence(),
        ];

        $this->json('POST', '/api/task', $data, $headers);
        $this->assertResponseStatus(422);
        $this->receiveJson();

        $this->seeJsonStructure([
            'invalid_form' => ['project_id'],
            'code',
            'message'
        ]);

        $this->seeJson([
            'message' => 'The given data was invalid.',
            'invalid_form' => ['project_id' => [
                'The project id field is required.'
            ]]
        ]);
    }

    public function testNonExistentProjectCreateTask()
    {
        list($user, $token, $project) = $this->resource();

        $headers = ['Authorization' => 'Bearer '.$token];
        $data = [
            'description' => $this->faker()->sentence(),
            'project_id' => 5
        ];

        $this->json('POST', '/api/task', $data, $headers);
        $this->assertResponseStatus(404);

        $this->seeJson([
            'message' => 'Project not found',
        ]);
    }

    public function testNotOwnedProjectCreateTask()
    {
        list($user, $token, $project) = $this->resource();
        list($user_other, $token_other, $project_other) = $this->resource();

        $headers = ['Authorization' => 'Bearer '.$token];
        $data = [
            'description' => $this->faker()->sentence(),
            'project_id' => $project_other->id
        ];

        $this->json('POST', '/api/task', $data, $headers);
        $this->assertResponseStatus(404);

        $this->seeJson([
            'message' => 'Project not found',
        ]);
    }

    public function testViewTask()
    {
        list($user, $token, $project) = $this->resource();

        $headers = ['Authorization' => 'Bearer '.$token];
        $data = [
            'description' => $this->faker()->sentence(),
            'project_id' => $project->id
        ];

        $request = $this->json('POST', '/api/task', $data, $headers);
        $this->assertResponseOk();

        $task_id = $request->response->getData()->task->id;
        $this->json('GET', '/api/task/'.$task_id, [], $headers);

        $this->assertResponseOk();
        $this->seeJsonStructure(['task' => [
            'description', 'created_at', 'updated_at', 'finished_at', 'is_closed'
        ]]);

    }

    public function testListTasks()
    {
        list($user, $token, $project) = $this->resource();
        $headers = ['Authorization' => 'Bearer '.$token];

        $total = 0;
        foreach(range(1, rand(2,5)) as $index) {
            $this->json('POST', '/api/task', [
                'description' => $this->faker()->sentence(),
                'project_id' => $project->id
            ], $headers);
            $total++;
        }

        $request = $this->json('GET', '/api/tasks', [], $headers);

        $this->assertResponseOk();
        $this->seeJsonStructure(['tasks' => [
            '*' => ['description', 'created_at', 'updated_at', 'finished_at', 'is_closed']
        ]]);
        $this->assertCount($total, $request->response->getData()->tasks);
    }

    public function testUpdateTask()
    {
        list($user, $token, $project) = $this->resource();

        $headers = ['Authorization' => 'Bearer '.$token];
        $created_data = [
            'description' => $this->faker()->sentence(),
            'project_id' => $project->id
        ];

        $request = $this->json('POST', '/api/task', $created_data, $headers);
        $this->assertResponseOk();

        $updated_data = [
            'description' => $this->faker()->sentence(),
            'project_id' => $project->id
        ];

        $task_id = $request->response->getData()->task->id;

        $this->json('PUT', '/api/task/'.$task_id, $updated_data, $headers);

        $this->assertResponseOk();

        $this->seeJsonStructure(['task' => [
            'description',
            'is_closed',
            'created_at',
            'updated_at',
            'finished_at'
        ]]);

        $this->seeInDatabase('tasks', [
            'description' => $updated_data['description'],
            'project_id' => $updated_data['project_id']
        ]);

    }

    public function testCloseTask()
    {
        list($user, $token, $project) = $this->resource();

        $headers = ['Authorization' => 'Bearer '.$token];
        $created_data = [
            'description' => $this->faker()->sentence(),
            'project_id' => $project->id
        ];

        $request = $this->json('POST', '/api/task', $created_data, $headers);
        $this->assertResponseOk();

        $task_id = $request->response->getData()->task->id;
        $this->json('PUT', '/api/task/'.$task_id.'/close', [], $headers);

        $this->assertResponseOk();
        $this->seeJson([
            'message' => 'Task successfully closed',
        ]);
    }

    public function testCloseAlreadyClosedTask()
    {
        list($user, $token, $project) = $this->resource();

        $headers = ['Authorization' => 'Bearer '.$token];
        $created_data = [
            'description' => $this->faker()->sentence(),
            'project_id' => $project->id
        ];

        $request = $this->json('POST', '/api/task', $created_data, $headers);
        $this->assertResponseOk();

        $task_id = $request->response->getData()->task->id;
        $this->json('PUT', '/api/task/'.$task_id.'/close', [], $headers);

        $this->assertResponseOk();


        $task_id = $request->response->getData()->task->id;
        $this->json('PUT', '/api/task/'.$task_id.'/close', [], $headers);
        $this->assertResponseStatus(500);

        $this->seeJson([
            'message' => 'Operation requires an open task',
        ]);
    }

    public function testDeleteTask()
    {
        list($user, $token, $project) = $this->resource();

        $headers = ['Authorization' => 'Bearer '.$token];
        $created_data = [
            'description' => $this->faker()->sentence(),
            'project_id' => $project->id
        ];

        $request = $this->json('POST', '/api/task', $created_data, $headers);
        $this->assertResponseOk();

        $task_id = $request->response->getData()->task->id;
        $this->json('DELETE', '/api/task/'.$task_id, [], $headers);

        $this->assertResponseOk();

        $this->seeJson([
            'message' => 'Task successfully deleted',
        ]);
    }

    public function testClosedTaskDelete()
    {
        list($user, $token, $project) = $this->resource();

        $headers = ['Authorization' => 'Bearer '.$token];
        $created_data = [
            'description' => $this->faker()->sentence(),
            'project_id' => $project->id
        ];

        $request = $this->json('POST', '/api/task', $created_data, $headers);
        $this->assertResponseOk();

        $task_id = $request->response->getData()->task->id;

        $this->json('PUT', '/api/task/'.$task_id.'/close', [], $headers);
        $this->assertResponseOk();

        $this->json('DELETE', '/api/task/'.$task_id, [], $headers);
        $this->assertResponseStatus(500);

        $this->seeJson([
            'message' => 'Operation requires an open task',
        ]);
    }
}
