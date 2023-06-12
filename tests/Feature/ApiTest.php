<?php

namespace Tests\Feature;

use App\Models\Joke;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class ApiTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    private $testJoke;

    public function setUp(): void
    {
        parent::setUp();

        $this->testJoke = [
            'joke_api_id' => $this->faker->randomNumber(3),
            'type' => $this->faker->word(),
            'setup' => $this->faker->realText(250),
            'punchline' => $this->faker->realText(250)
        ];
    }

    public function testJokeCreated()
    {
        $joke = Joke::create($this->testJoke);
        $this->assertNotNull($joke->id);
        $this->assertEquals($this->testJoke['joke_api_id'], $joke->joke_api_id);
        $this->assertEquals($this->testJoke['type'], $joke->type);
        $this->assertEquals($this->testJoke['setup'], $joke->setup);
        $this->assertEquals($this->testJoke['punchline'], $joke->punchline);
    }

    public function testApiShowJokeSuccess()
    {
        Joke::create($this->testJoke);
        $response = $this->json('GET', '/api/v1/joke', ['datetime' => now()->format('Y-m-d H:i:s')]);
        $response->assertStatus(200);
        $response->assertJsonStructure([
            [
                'setup',
                'punchline',
                'datetime',
            ]
        ]);
    }

    public function testApiShowJokeErrorNoDate()
    {
        $response = $this->get('/api/v1/joke');
        $response->assertStatus(422);
        $response->assertJson([
            'errors' => [
                'The datetime field is required.'
            ]
        ]);
    }

    public function testApiShowJokeErrorWrongDate()
    {
        $response = $this->json('GET', '/api/v1/joke', ['datetime' => '2023']);
        $response->assertStatus(422);
        $response->assertJson([
            'errors' => [
                'The datetime field must match the format Y-m-d H:i:s.'
            ]
        ]);
    }

    public function testClosestJokeToTheProvidedDateTime()
    {
        $oldJoke = Joke::create([
            'joke_api_id' => 1,
            'type' => fake()->word(),
            'setup' => fake()->realText(250),
            'punchline' => fake()->realText(250),
        ]);
        $oldJoke->created_at = now()->subDay();
        $oldJoke->save();
        $newJoke = Joke::create($this->testJoke);
        $datetime = now();
        $result = Joke::query()
            ->orderBy(DB::raw("ABS(TIMESTAMPDIFF(SECOND, created_at, '$datetime'))"))
            ->first();

        $this->assertEquals($newJoke->joke_api_id, $result->joke_api_id);
    }
}
