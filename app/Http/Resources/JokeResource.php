<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JokeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'setup' => $this->setup,
            'punchline' => $this->punchline,
            'datetime' => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
