<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\Models\Questionnaire;
use App\Models\Session;
use Faker\Generator as Faker;
use Faker\Factory as FakerFactory;

$fakerFr = FakerFactory::create('fr_FR');
$autoIncrement = autoIncrement();
$autoIncrement->rewind();

$factory->define(Questionnaire::class, function (Faker $faker) use ($fakerFr, $autoIncrement) {
    $autoIncrement->next();

    return [
        'published' => $faker->boolean(50),
        'level' => $faker->numberBetween(1, 3),
        'position' => $autoIncrement->current(),
        'active:fr' => $faker->boolean(100),
        'title:fr' => $fakerFr->sentence(5),
        'description:fr' => $fakerFr->realText(rand(80, 600)),
        'active:en' => $faker->boolean(50),
        'title:en' => $faker->sentence(5),
        'description:en' => $faker->realText(rand(80, 600)),
        'note_max' => 20,
        'session_id' => function () {
            // Get random session id
            return Session::inRandomOrder()->first()->id;
        }
    ];
});
