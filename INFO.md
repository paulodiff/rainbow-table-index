igienepubblica.rn@auslromagna.it
dipsan.rn@auslromagna.it


// https://github.com/austinheap/laravel-database-encryption/blob/master/tests/TestCase.php


// https://www.youtube.com/watch?v=H-euNqEKACA&t=1045s


create packages/username/package_name
cd packages/username/package_name
composer init
mkdir src
mkdir tests


create package_nameServiceProvider.php

add autoload to packages/username/package_name/composer.json
"autoload": {
    "psr-4": {
        "RainbowTableIndex\\": "src/"
    }
}

add autoload-dev to /roo/composer.json
"autoload-dev": {
    "psr-4": {
        "Tests\\": "tests/",
        "RainbowTableIndex\\": "packages/paulodiff/rainbow-table-index/src"
    }
}

cd /root project
composer dump-autoload

"autoload": {
    "psr-4": {
        "RainbowTableIndex\\": "src/"
    }
},
