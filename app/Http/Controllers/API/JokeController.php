<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\JokeResource;
use App\Models\Joke;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class JokeController extends Controller
{
    protected $apiUrl = 'https://official-joke-api.appspot.com/random_joke';
    public function fetch()
    {
       try {
            $response = Http::timeout(10)
                ->retry(3, 100)
                ->throw()
                ->acceptJson()
                ->get($this->apiUrl);

            if ($joke = json_decode($response)) {
                Joke::firstOrCreate(
                    [
                        'joke_api_id' => $joke->id
                    ],
                    [
                        'type' => $joke->type,
                        'setup' => $joke->setup,
                        'punchline' => $joke->punchline,
                    ]
                );
            }
        } catch (\Exception $e) {
            Log::error('Joke fetch from API error: ' . $e->getMessage());
        }
    }

    public function show(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'datetime' => 'required|date_format:Y-m-d H:i:s',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return response()->json([
                'errors' => $errors,
            ], 422);
        }

        $joke = Joke::query()
            ->orderBy(DB::raw("ABS(TIMESTAMPDIFF(SECOND, created_at, '$request->datetime'))"))
            ->first();

        if ($joke) {
            return response()->json(new JokeResource($joke));
        }

    }
}
