<?php

require "vendor/autoload.php";

function writeCSV($file, $data) {
    $handle = fopen($file, "w");

    foreach ($data as $datum) {
        fputcsv($handle, $datum);
    }

    fclose($handle);
}

print "It takes a while to produce about 50mb of CSV data...\n";

unlink("authors.csv");
unlink("categories.csv");
unlink("posts.csv");

$faker = Faker\Factory::create();

$authors = [];

for ($i = 0; $i < 100; $i++) {
    $authors[] = [
        $i,
        $faker->title,
        $faker->firstName,
        $faker->lastName,
        $faker->address,
        $faker->city,
        $faker->country,
        $faker->postcode,
        $faker->email,
    ];
}

writeCSV("authors.csv", $authors);

$categories = [];

for ($i = 0; $i < 50; $i++) {
    $categories[] = [
        $i,
        $faker->unique()->word
    ];
}

writeCSV("categories.csv", $categories);

$posts = [];

for ($i = 0; $i < 10000; $i++) {
    $posts[] = [
        $i,
        $faker->randomElement($authors)[0],
        $faker->randomElement($categories)[0],
        $faker->sentence,
        join("\n\n", $faker->paragraphs(3)),
        join("\n\n", $faker->paragraphs(30)),
        join(", ", $faker->words(5)),
    ];
}

writeCSV("posts.csv", $posts);
