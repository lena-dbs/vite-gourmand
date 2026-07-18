<?php 
declare(strict_types=1);

spl_autoload_register(function (string $class): void {
    $paths = [

        ROOT_PATH . '/app/Controllers/' . $class . '.php',
        ROOT_PATH . '/app/Models/' . $class . '.php',
        ROOT_PATH . '/core/' . $class . '.php',
        ROOT_PATH . '/vendor/autoload.php',
    ];

    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
});