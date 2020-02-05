<?php

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Firebase\JWT\JWT;
use App\Models\User;

class ProjectTest extends TestCase
{
    use DatabaseTransactions;

    private function createUserAndGetToken() {
        $user = User::create([
            'name' => $this->faker()->name,
            'email' => $this->faker()->email,
            'password' => $this->faker()->password
        ]);

        return JWT::encode([
            'sub' => $user->id,
            'iss' => "taskmgr",
            'iat' => time(),
            'exp' => time() + 60*60
        ], env('JWT_KEY'));
    }

    public function testCreateProject()
    {
        $data = ['name' => $this->faker()->jobTitle];
        $headers = ['Authorization' => 'Bearer '.$this->createUserAndGetToken()];

        $this->json('POST', '/api/project', $data, $headers);
        $this->assertResponseOk();

        $this->seeJsonStructure(['project' => ['name', 'created_at', 'updated_at']]);
        $this->seeInDatabase('projects', ['name' => $data['name']]);
    }

    public function testUpdateProject()
    {
        $create_data = ['name' => $this->faker()->jobTitle];
        $headers = ['Authorization' => 'Bearer '.$this->createUserAndGetToken()];

        $request = $this->json('POST', '/api/project', $create_data, $headers);
        $this->assertResponseOk();

        $this->seeInDatabase('projects', ['name' => $create_data['name']]);

        $project_id = $request->response->getData()->project->id;
        $update_data = ['name' => $this->faker()->jobTitle];

        $this->json('PUT', '/api/project/'.$project_id, $update_data, $headers);
        $this->seeInDatabase('projects', ['name' => $update_data['name']]);
    }

    public function testViewProject()
    {
        $headers = ['Authorization' => 'Bearer '.$this->createUserAndGetToken()];

        $request = $this->json('POST', '/api/project', [
            'name' => $this->faker()->jobTitle
        ], $headers);

        $this->assertResponseOk();

        $project_id = $request->response->getData()->project->id;
        $this->json('GET', '/api/project/'.$project_id, [], $headers);

        $this->assertResponseOk();
        $this->seeJsonStructure(['project' => ['name', 'created_at', 'updated_at']]);
    }

    public function testListProjects()
    {
        $headers = ['Authorization' => 'Bearer '.$this->createUserAndGetToken()];

        $total = 0;
        foreach(range(1, rand(2,5)) as $index) {
            $this->json('POST', '/api/project', [
                'name' => $this->faker()->jobTitle
            ], $headers);
            $total++;
        }

        $request = $this->json('GET', '/api/projects', [], $headers);

        $this->assertResponseOk();
        $this->seeJsonStructure(['projects' => [
            '*' => ['name', 'created_at', 'updated_at']
        ]]);
        $this->assertCount($total, $request->response->getData()->projects);
    }

    public function testDeleteProject()
    {
        $headers = ['Authorization' => 'Bearer '.$this->createUserAndGetToken()];

        $request = $this->json('POST', '/api/project', [
            'name' => $this->faker()->jobTitle
        ], $headers);

        $this->assertResponseOk();

        $project_id = $request->response->getData()->project->id;
        $this->json('DELETE', '/api/project/'.$project_id, [], $headers);

        $this->assertResponseOk();
        $this->seeJson([
            'message' => 'Project successfully deleted',
        ]);
    }
}
