<?php

$token = "8445421276:AAEgTw6jjvEI98YgnN9wZsAzE6MM8ajj_AQ";

$content = file_get_contents("php://input");

if (!$content) {
    http_response_code(200);
    exit;
}

$update = json_decode($content, true);

if (!isset($update["message"])) {
    http_response_code(200);
    exit;
}

$chat_id = $update["message"]["chat"]["id"];
$text = strtolower(trim($update["message"]["text"]));

$msg = "";

switch($text){

    case "/start":
        $msg = "🔥 BIENVENIDO A MDPRIME OTT 🔥

📺 THE BEST STREAM

Selecciona una opción:

/planes
/referidos
/iptv
/renovar
/pagar
/soporte";
    break;

    case "/planes":
        $msg = "💎 PLANES PREMIUM

👤 1 Usuario
3 Meses → 35€
6 Meses → 55€
12 Meses → 80€

👥 2 Usuarios
3 Meses → 55€
6 Meses → 85€
12 Meses → 120€

👨‍👩‍👦 3 Usuarios
3 Meses → 80€
6 Meses → 125€
12 Meses → 165€";
    break;

    case "/referidos":
        $msg = "🏆 REFERIDOS VIP

🥉 COBRE
Clientes 4+
3 Meses → 30€
6 Meses → 45€
12 Meses → 65€

🥈 PLATA
Clientes 8+
3 Meses → 27€
6 Meses → 40€
12 Meses → 58€

🥇 ORO
Clientes 12+
3 Meses → 25€
6 Meses → 37€
12 Meses → 52€

💠 PLATINUM
Clientes 20+
3 Meses → 22€
6 Meses → 33€
12 Meses → 45€";
    break;


    case "/renovar":
        $msg = "🔄 ENVÍA TU USUARIO PARA RENOVAR TU CUENTA.";
    break;

    case "/pagar":
        $msg = "💳 PAGO SEGURO MDPRIME:

https://buy.stripe.com/7sYbJ19GFca2dBt8Qg6g80N

Después envía el comprobante.";
    break;

    case "/soporte":
        $msg = "🛠 Escribe tu consulta y te responderemos.";
    break;

    default:
        $msg = "❌ Comando no reconocido.

Usa:
/planes
/referidos
/iptv
/renovar
/pagar
/soporte";
}

$url = "https://api.telegram.org/bot".$token."/sendMessage";

$data = [
    "chat_id" => $chat_id,
    "text" => $msg
];

$options = [
    "http" => [
        "header"  => "Content-type: application/x-www-form-urlencoded",
        "method"  => "POST",
        "content" => http_build_query($data),
    ]
];

$context = stream_context_create($options);

file_get_contents($url, false, $context);

http_response_code(200);
exit;

?>
