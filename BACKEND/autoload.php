<?php
/* autoload.php */
spl_autoload_register(function (string $class) {

    // 1️⃣ Mapa de namespaces a sus carpetas
    $prefixes = [
        'Core\\' => __DIR__ . '/core/',
        'App\\'  => __DIR__ . '/app/',
        'Utils\\' => __DIR__ . '/utils/',
        'Scripts\\' => __DIR__ . '/scripts/',
        'Services\\' => __DIR__ . '/services/',
        'PHPMailer\\PHPMailer\\' => __DIR__ . '/libs/PHPMailer/src/',
        'Psr\\Log\\' => __DIR__ . '/libs/Psr/Log/',
             
        // agrega más si lo necesitas
    ];

    // 2️⃣ Recorre los prefijos registrados
    foreach ($prefixes as $prefix => $baseDir) {

        // El namespace de la clase empieza con este prefijo?
        $len = strlen($prefix);
        if (strncmp($class, $prefix, $len) !== 0) {
            continue;                       // siguiente prefijo
        }

        // 3️⃣ Construye la ruta relativa quitando el prefijo
        //    Core\Router → Router.php
        $relative = substr($class, $len);
        $file = $baseDir . str_replace('\\', '/', $relative) . '.php';

        // 4️⃣ Carga el archivo si existe
        if (file_exists($file)) {
            require_once $file;
        } else {
            // Para depurar ─ comenta en producción
            error_log("Autoload: {$class} no encontrado en {$file}");
        }
        return;    // ya lo encontró o no existe, pero no sigas buscando
    }

    // Si llega aquí, ningún prefijo coincidió
    error_log("Autoload: sin prefijo para {$class}");
});
