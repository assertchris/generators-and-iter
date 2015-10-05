<?php

require "vendor/autoload.php";

function readCSV($file) {
    $rows = [];

    $handle = fopen($file, "r");

    while (!feof($handle)) {
        $rows[] = fgetcsv($handle);
    }

    fclose($handle);

    return $rows;
}

$authors = array_filter(
    readCSV("authors.csv")
);

$categories = array_filter(
    readCSV("categories.csv")
);

$posts = array_filter(
    readCSV("posts.csv")
);

function filterByColumn($array, $column, $value) {
    return array_filter(
        $array, function($item) use ($column, $value) {
            return $item[$column] == $value;
        }
    );
}

$authors = array_map(function($author) use ($posts) {
    $author["posts"] = filterByColumn(
        $posts, 1, $author[0]
    );

    // perform other changes to $author

    return $author;
}, $authors);

$categories = array_map(function($category) use ($posts) {
    $category["posts"] = filterByColumn(
        $posts, 2, $category[0]
    );

    // perform other changes to $category

    return $category;
}, $categories);

$posts = array_map(function($post) use ($authors, $categories) {
    foreach ($authors as $author) {
        if ($author[0] == $post[1]) {
            $post["author"] = $author;
            break;
        }
    }

    foreach ($categories as $category) {
        if ($category[0] == $post[1]) {
            $post["category"] = $category;
            break;
        }
    }

    // perform other changes to $post

    return $post;
}, $posts);

function formatBytes($bytes, $precision = 2) {
    $kilobyte = 1024;
    $megabyte = 1024 * 1024;

    if ($bytes >= 0 && $bytes < $kilobyte) {
        return $bytes . " b";
    }

    if ($bytes >= $kilobyte && $bytes < $megabyte) {
        return round($bytes / $kilobyte, $precision) . " kb";
    }

    return round($bytes / $megabyte, $precision) . " mb";
}

print "memory:" . formatBytes(memory_get_peak_usage());

function readCSVGenerator($file) {
    $handle = fopen($file, "r");

    while (!feof($handle)) {
        yield fgetcsv($handle);
    }

    fclose($handle);
}

foreach (readCSVGenerator("posts.csv") as $post) {
    // do something with $post
}

print "memory:" . formatBytes(memory_get_peak_usage());

// function getAuthors() {
//     $authors = readCSVGenerator("authors.csv");
//
//     foreach ($authors as $author) {
//         yield formatAuthor($author);
//     }
// }
//
// function formatAuthor($author) {
//     $author["posts"] = getPostsForAuthor($author);
//
//     // make other changes to $author
//
//     return $author;
// }
//
// function getPostsForAuthor($author) {
//     $posts = readCSVGenerator("posts.csv");
//
//     foreach ($posts as $post) {
//         if ($post[1] == $author[0]) {
//             yield formatPost($post);
//         }
//     }
// }
//
// function formatPost($post) {
//     foreach (getAuthors() as $author) {
//         if ($post[1] == $author[0]) {
//             $post["author"] = $author;
//             break;
//         }
//     }
//
//     foreach (getCategories() as $category) {
//         if ($post[2] == $category[0]) {
//             $post["category"] = $category;
//             break;
//         }
//     }
//
//     // make other changes to $post
//
//     return $post;
// }
//
// function getCategories() {
//     $categories = readCSVGenerator("categories.csv");
//
//     foreach ($categories as $category) {
//         yield formatCategory($category);
//     }
// }
//
// function formatCategory($category) {
//     $category["posts"] = getPostsForCategory($category);
//
//     // make other changes to $category
//
//     return $category;
// }
//
// function getPostsForCategory($category) {
//     $posts = readCSVGenerator("posts.csv");
//
//     foreach ($posts as $post) {
//         if ($post[2] == $category[0]) {
//             yield formatPost($post);
//         }
//     }
// }
//
// foreach (getAuthors() as $author) {
//     foreach ($author["posts"] as $post) {
//         var_dump($post["author"]);
//         break 2;
//     }
// }
//
// print "memory:" . formatBytes(memory_get_peak_usage());

function filterGenerator($generator, $column, $value) {
    return iter\filter(
        function($item) use ($column, $value) {
            return $item[$column] == $value;
        },
        $generator
    );
}

function getAuthors() {
    return iter\map(
        "formatAuthor",
        readCSVGenerator("authors.csv")
    );
}

function formatAuthor($author) {
    $author["posts"] = getPostsForAuthor($author);

    // make other changes to $author

    return $author;
}

function getPostsForAuthor($author) {
    return iter\map(
        "formatPost",
        filterGenerator(
            readCSVGenerator("posts.csv"), 1, $author[0]
        )
    );
}

function formatPost($post) {
    foreach (getAuthors() as $author) {
        if ($post[1] == $author[0]) {
            $post["author"] = $author;
            break;
        }
    }

    foreach (getCategories() as $category) {
        if ($post[2] == $category[0]) {
            $post["category"] = $category;
            break;
        }
    }

    // make other changes to $post

    return $post;
}

function getCategories() {
    return iter\map(
        "formatCategory",
        readCSVGenerator("categories.csv")
    );
}

function formatCategory($category) {
    $category["posts"] = getPostsForCategory($category);

    // make other changes to $category

    return $category;
}

function getPostsForCategory($category) {
    return iter\map(
        "formatPost",
        filterGenerator(
            readCSVGenerator("posts.csv"), 2, $category[0]
        )
    );
}

foreach (getAuthors() as $author) {
    foreach ($author["posts"] as $post) {
        var_dump($post["author"]);
        break 2;
    }
}

print "memory:" . formatBytes(memory_get_peak_usage());

$array = iter\toArray(
    iter\flatten(
        [1, 2, [3, 4, 5], 6, 7]
    )
);

print join(", ", $array);

$array = iter\toArray(
    iter\slice(
        [-3, -2, -1, 0, 1, 2, 3],
        2, 4
    )
);

print join(", ", $array);

$rewindable = iter\makeRewindable(function($max = 13) {
    $older = 0;
    $newer = 1;

    do {
        $number = $newer + $older;

        $older = $newer;
        $newer = $number;

        yield $number;
    }
    while($number < $max);
});

print join(", ", iter\toArray($rewindable()));
print join(", ", iter\toArray($rewindable()));
