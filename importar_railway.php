<?php
/*
   MDPRIME - MIGRADOR RAILWAY V2
   Ejecutar una sola vez:
   https://mdprime-bot.onrender.com/importar_railway.php?key=MDPRIME_IMPORT_2026

   Archivos necesarios en el repo:
   - importar_railway.php
   - if0_42072872_referidos.sql
*/

header('Content-Type: text/plain; charset=utf-8');
date_default_timezone_set('Europe/Madrid');

$import_key = "MDPRIME_IMPORT_2026";

/* DATOS MYSQL RAILWAY */
$db_host = "reseau.proxy.rlwy.net";
$db_port = 39553;
$db_name = "railway";
$db_user = "root";
$db_pass = "ZRNWfdsxefUJrBMSJMchlLxzMHrAZjug";

$sql_file = __DIR__ . "/if0_42072872_referidos.sql";

if (($_GET["key"] ?? "") !== $import_key) {
    http_response_code(403);
    exit("❌ Acceso no autorizado.");
}

if (!file_exists($sql_file)) {
    exit("❌ No encuentro el archivo SQL:\n".$sql_file);
}

function limpiarSQL($sql) {
    $sql = preg_replace('/^\xEF\xBB\xBF/', '', $sql);

    // Quitar comentarios de línea phpMyAdmin
    $lineas = preg_split("/\R/", $sql);
    $limpias = [];

    foreach ($lineas as $linea) {
        $trim = trim($linea);

        if ($trim === '') {
            continue;
        }

        if (str_starts_with($trim, '--')) {
            continue;
        }

        if (str_starts_with($trim, '#')) {
            continue;
        }

        $limpias[] = $linea;
    }

    $sql = implode("\n", $limpias);

    // Quitar bloques especiales tipo /*!40101 ... */;
    $sql = preg_replace('/\/\*![0-9]+\s+.*?\*\/;/is', '', $sql);

    // Quitar comandos no necesarios o problemáticos
    $sql = preg_replace('/SET\s+SQL_MODE\s*=.*?;/is', '', $sql);
    $sql = preg_replace('/SET\s+AUTOCOMMIT\s*=.*?;/is', '', $sql);
    $sql = preg_replace('/START\s+TRANSACTION\s*;/is', '', $sql);
    $sql = preg_replace('/COMMIT\s*;/is', '', $sql);
    $sql = preg_replace('/SET\s+time_zone\s*=.*?;/is', '', $sql);
    $sql = preg_replace('/CREATE\s+DATABASE\s+.*?;/is', '', $sql);
    $sql = preg_replace('/USE\s+`?[^`;]+`?\s*;/is', '', $sql);

    // Compatibilidad MariaDB/phpMyAdmin -> Railway MySQL
    $sql = str_ireplace("ENGINE=MyISAM", "ENGINE=InnoDB", $sql);
    $sql = str_ireplace("DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci", "DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci", $sql);
    $sql = str_ireplace("CHARSET=latin1 COLLATE=latin1_swedish_ci", "CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci", $sql);
    $sql = str_ireplace("COLLATE=latin1_swedish_ci", "COLLATE=utf8mb4_unicode_ci", $sql);

    // Evitar fallo si se vuelve a ejecutar
    $sql = preg_replace('/CREATE TABLE\s+`/i', 'CREATE TABLE IF NOT EXISTS `', $sql);

    return trim($sql);
}

function partirSQL($sql) {
    $sentencias = [];
    $actual = '';
    $len = strlen($sql);
    $in_single = false;
    $in_double = false;
    $in_backtick = false;
    $escape = false;

    for ($i = 0; $i < $len; $i++) {
        $ch = $sql[$i];
        $actual .= $ch;

        if ($escape) {
            $escape = false;
            continue;
        }

        if ($ch === "\\") {
            $escape = true;
            continue;
        }

        if ($ch === '`' && !$in_single && !$in_double) {
            $in_backtick = !$in_backtick;
            continue;
        }

        if ($ch === "'" && !$in_double && !$in_backtick) {
            $in_single = !$in_single;
            continue;
        }

        if ($ch === '"' && !$in_single && !$in_backtick) {
            $in_double = !$in_double;
            continue;
        }

        if ($ch === ';' && !$in_single && !$in_double && !$in_backtick) {
            $sentencia = trim($actual);
            if ($sentencia !== '' && $sentencia !== ';') {
                $sentencias[] = $sentencia;
            }
            $actual = '';
        }
    }

    $resto = trim($actual);
    if ($resto !== '') {
        $sentencias[] = $resto;
    }

    return $sentencias;
}

try {
    echo "🚀 MDPRIME MIGRADOR RAILWAY V2\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "Conectando a Railway MySQL...\n\n";

    $pdo = new PDO(
        "mysql:host=$db_host;port=$db_port;dbname=$db_name;charset=utf8mb4",
        $db_user,
        $db_pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );

    echo "✅ Conectado a Railway.\n";
    echo "Leyendo SQL...\n\n";

    $sql_original = file_get_contents($sql_file);
    $sql = limpiarSQL($sql_original);
    $sentencias = partirSQL($sql);

    echo "Sentencias detectadas: ".count($sentencias)."\n\n";

    echo "Limpiando tablas destino...\n";

    $pdo->exec("SET FOREIGN_KEY_CHECKS=0");
    $pdo->exec("DROP TABLE IF EXISTS referidos");
    $pdo->exec("DROP TABLE IF EXISTS configuracion_niveles");
    $pdo->exec("DROP TABLE IF EXISTS clientes");
    $pdo->exec("SET FOREIGN_KEY_CHECKS=1");

    echo "✅ Limpieza completada.\n\n";

    $ok = 0;
    $errores = [];

    foreach ($sentencias as $idx => $sentencia) {
        $s = trim($sentencia);

        if ($s === '') {
            continue;
        }

        try {
            $pdo->exec($s);
            $ok++;
        } catch (Throwable $e) {
            $errores[] = [
                "num" => $idx + 1,
                "error" => $e->getMessage(),
                "sql" => substr($s, 0, 900)
            ];
        }
    }

    echo "Sentencias ejecutadas OK: ".$ok."\n";
    echo "Errores: ".count($errores)."\n\n";

    if (!empty($errores)) {
        foreach ($errores as $err) {
            echo "❌ Error sentencia ".$err["num"]."\n";
            echo $err["error"]."\n";
            echo $err["sql"]."\n\n";
        }
        exit("❌ Importación con errores. Revisa arriba.\n");
    }

    echo "Comprobando tablas...\n\n";

    $esperadas = ["clientes", "configuracion_niveles", "referidos"];
    $todo_ok = true;

    foreach ($esperadas as $tabla) {
        try {
            $count = $pdo->query("SELECT COUNT(*) FROM `$tabla`")->fetchColumn();
            echo "✅ ".$tabla." → ".$count." registros\n";
        } catch (Throwable $e) {
            echo "❌ ".$tabla." → NO EXISTE\n";
            $todo_ok = false;
        }
    }

    echo "\n━━━━━━━━━━━━━━━━━━━━━━\n";

    if ($todo_ok) {
        echo "🎉 IMPORTACIÓN TERMINADA CORRECTAMENTE.\n";
        echo "Ya puedes usar el bot con Railway.\n";
    } else {
        echo "⚠️ Importación incompleta.\n";
    }

} catch (Throwable $e) {
    echo "❌ ERROR GENERAL\n";
    echo $e->getMessage()."\n";
}
