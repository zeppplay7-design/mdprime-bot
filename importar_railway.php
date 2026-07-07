<?php
/*
   MDPRIME - MIGRADOR RAILWAY
   Ejecutar una sola vez:
   https://TU-URL-RENDER/importar_railway.php?key=MDPRIME_IMPORT_2026

   Deben estar en la misma carpeta:
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
    exit("❌ No encuentro el archivo SQL:\n".$sql_file."\n\nSube también if0_42072872_referidos.sql junto a este archivo.");
}

function limpiarSQL($sql) {
    $sql = preg_replace('/^\xEF\xBB\xBF/', '', $sql);
    $sql = preg_replace('/\/\*![0-9]+\s+SET\s+.*?\*\/;/is', '', $sql);
    $sql = preg_replace('/SET\s+SQL_MODE\s*=.*?;/is', '', $sql);
    $sql = preg_replace('/SET\s+AUTOCOMMIT\s*=.*?;/is', '', $sql);
    $sql = preg_replace('/START\s+TRANSACTION\s*;/is', '', $sql);
    $sql = preg_replace('/SET\s+time_zone\s*=.*?;/is', '', $sql);
    $sql = preg_replace('/CREATE\s+DATABASE\s+.*?;/is', '', $sql);
    $sql = preg_replace('/USE\s+`?[^`;]+`?\s*;/is', '', $sql);
    $sql = str_ireplace("TYPE=MyISAM", "ENGINE=MyISAM", $sql);
    return trim($sql);
}

function partirSQL($sql) {
    $sentencias = [];
    $actual = '';
    $len = strlen($sql);
    $in_single = false;
    $in_double = false;
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

        if ($ch === "'" && !$in_double) {
            $in_single = !$in_single;
            continue;
        }

        if ($ch === '"' && !$in_single) {
            $in_double = !$in_double;
            continue;
        }

        if ($ch === ';' && !$in_single && !$in_double) {
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
    echo "🚀 MDPRIME MIGRADOR RAILWAY\n";
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
    echo "Leyendo archivo SQL...\n\n";

    $sql = file_get_contents($sql_file);
    $sql = limpiarSQL($sql);

    echo "Limpiando base destino...\n";

    $pdo->exec("SET FOREIGN_KEY_CHECKS=0");
    $pdo->exec("DROP TABLE IF EXISTS referidos");
    $pdo->exec("DROP TABLE IF EXISTS clientes");
    $pdo->exec("DROP TABLE IF EXISTS configuracion_niveles");
    $pdo->exec("SET FOREIGN_KEY_CHECKS=1");

    echo "✅ Tablas antiguas eliminadas si existían.\n\n";

    $sentencias = partirSQL($sql);
    $total = count($sentencias);
    $ok = 0;
    $saltadas = 0;

    echo "Ejecutando ".$total." sentencias SQL...\n\n";

    foreach ($sentencias as $idx => $sentencia) {
        $s = trim($sentencia);

        if ($s === '' || str_starts_with($s, '--')) {
            $saltadas++;
            continue;
        }

        try {
            $pdo->exec($s);
            $ok++;
        } catch (Throwable $e) {
            $msg = $e->getMessage();

            if (
                stripos($msg, "collation_connection") !== false ||
                stripos($msg, "character_set_client") !== false ||
                stripos($msg, "already exists") !== false
            ) {
                $saltadas++;
                continue;
            }

            echo "\n❌ ERROR EN SENTENCIA ".($idx + 1)."\n";
            echo "Mensaje:\n".$msg."\n\n";
            echo "Sentencia:\n".substr($s, 0, 700)."\n";
            exit;
        }
    }

    echo "✅ SQL ejecutado.\n";
    echo "Sentencias OK: ".$ok."\n";
    echo "Saltadas: ".$saltadas."\n\n";

    echo "Comprobando tablas...\n\n";

    $tablas = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);

    foreach ($tablas as $tabla) {
        $count = $pdo->query("SELECT COUNT(*) FROM `$tabla`")->fetchColumn();
        echo "✅ ".$tabla." → ".$count." registros\n";
    }

    echo "\n━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "🎉 IMPORTACIÓN TERMINADA CORRECTAMENTE.\n";
    echo "Ya puedes borrar importar_railway.php por seguridad.\n";

} catch (Throwable $e) {
    echo "❌ ERROR GENERAL\n";
    echo $e->getMessage()."\n";
}
