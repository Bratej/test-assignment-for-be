<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

## Installation

You must have <a href="https://www.docker.com/products/docker-desktop/">Docker</a> installed.

Copy the .env.example file to .env, enter the provided GOOGLE_CLIENT_ID and GOOGLE_CLIENT_SECRET.

Checkout the develop branch.

    git checkout develop

Install all required packages: 

    composer install

Install the Docker containers (takes a bit longer on first run):

    ./vendor/bin/sail up

Run the database migrations

    ./vendor/bin/sail php artisan migrate

## Use

The site is accessible at <a href="http://localhost">http://localhost</a>. The **log in** and **register** links are available in the header of the page.
You can also use the links: <a href="http://localhost/login">Login</a> or <a href="http://localhost/register">Register</a>.

Forgotten password emails are captured by Mailpit, which is accessible at <a href="http://localhost:8025/">http://localhost:8025</a>.

For manual fetch of a joke from the API, you can use the artisan command:

    ./vendor/bin/sail php artisan joke:fetch

If you want to test the fetching of jokes every 5 minutes you have to set up Cron or you can run the scheduler locally:

    ./vendor/bin/sail php artisan schedule:work

The API endpoint for fetching a stored joke closest to the provided date/time is available here:

    GET: http://localhost/api/v1/joke?datetime=2023-06-11 20:23:05

Date time must be in the Y-m-d H:i:s fromat, see example.
    
## Tests

Tests can be run by using the php artisan command:
    
    ./vendor/bin/sail php artisan test

## Docker

To stop the containers you can use

    ./vendor/bin/sail down

and to re-start again

    ./vendor/bin/sail up
