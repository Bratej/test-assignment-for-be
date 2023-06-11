<?php

namespace App\Console\Commands;

use App\Http\Controllers\API\JokeController;
use Illuminate\Console\Command;

class FetchJoke extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'joke:fetch';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch a new joke from API';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        (new JokeController())->fetch();
    }
}
