<?php
header('Content-Type: text/plain; charset=utf-8');
date_default_timezone_set('Europe/Madrid');

$key = "MDPRIME_FIX_DATES_2026";

$db_host = "reseau.proxy.rlwy.net";
$db_port = 39553;
$db_name = "railway";
$db_user = "root";
$db_pass = "ZRNWfdsxefUJrBMSJMchlLxzMHrAZjug";

if (($_GET["key"] ?? "") !== $key) {
    http_response_code(403);
    exit("❌ Acceso no autorizado.");
}

try {
    $pdo = new PDO(
        "mysql:host=$db_host;port=$db_port;dbname=$db_name;charset=utf8mb4",
        $db_user,
        $db_pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );

    echo "🚀 MDPRIME FIX FECHAS RAILWAY\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "✅ Conectado a Railway.\n\n";

    $pdo->exec("UPDATE referidos SET fecha_alta = NULL WHERE fecha_alta = '0000-00-00'");
    $pdo->exec("UPDATE referidos SET fecha_caducidad = NULL WHERE fecha_caducidad = '0000-00-00'");

    $total = $pdo->query("SELECT COUNT(*) FROM referidos")->fetchColumn();
    $nulosAlta = $pdo->query("SELECT COUNT(*) FROM referidos WHERE fecha_alta IS NULL")->fetchColumn();
    $nulosCad = $pdo->query("SELECT COUNT(*) FROM referidos WHERE fecha_caducidad IS NULL")->fetchColumn();

    echo "✅ Fechas 0000-00-00 corregidas.\n\n";
    echo "Referidos totales: ".$total."\n";
    echo "Fecha alta sin fecha: ".$nulosAlta."\n";
    echo "Caducidad sin fecha: ".$nulosCad."\n\n";
    echo "🎉 FIX TERMINADO.\n";
    echo "Ahora prueba /debugmd Brandon10 en Telegram.\n";

} catch (Throwable $e) {
    echo "❌ ERROR\n";
    echo $e->getMessage()."\n";
}
