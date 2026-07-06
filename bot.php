<?php

$token = "8445421276:AAEgTw6jjvEI98YgnN9wZsAzE6MM8ajj_AQ";
$admin_id = "372918983";
$state_file = "states.json";

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
$text = trim($update["message"]["text"]);
$command = strtolower(trim(explode(" ", $text)[0]));
$command = explode("@", $command)[0];

$states = file_exists($state_file) ? json_decode(file_get_contents($state_file), true) : [];
$user_state = $states[$chat_id] ?? "";

$msg = "";
/* =========================
   MENSAJE DE ESPERA
========================= */

$response = file_get_contents(
    "https://api.telegram.org/bot".$token."/sendMessage?".
    http_build_query([
        "chat_id" => $chat_id,
        "text" => "⏳ Procesando tu solicitud..."
    ])
);

$temp = json_decode($response, true);
$tempMessageId = $temp["result"]["message_id"] ?? null;


/* =========================
   RESPONDER A CLIENTE
========================= */
if(strpos($command, "/reply ") === 0){

    $parts = explode(" ", $text, 3);

    if(count($parts) >= 3){

        $reply_chat = trim($parts[1]);
        $reply_msg = trim($parts[2]);

        $reply_url = "https://api.telegram.org/bot".$token."/sendMessage";

        $reply_data = [
            "chat_id" => $reply_chat,
            "text" => "📩 SOPORTE MDPRIME:\n\n".$reply_msg
        ];

        $reply_options = [
            "http" => [
                "header"  => "Content-type: application/x-www-form-urlencoded",
                "method"  => "POST",
                "content" => http_build_query($reply_data),
            ]
        ];

        $reply_context = stream_context_create($reply_options);

        file_get_contents($reply_url, false, $reply_context);

        $msg = "✅ Mensaje enviado correctamente.";

    } else {

        $msg = "Uso correcto:
/reply CHATID mensaje";

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
}


/* =========================
   COMANDOS PRINCIPALES
========================= */
switch($command){

case "/start":

    $msg = "🔥 BIENVENIDO A MDPRIME 🔥

📺 BOT AUTOMATIZADO

━━━━━━━━━━━━━━━━━━

📋 MENÚ PRINCIPAL

💎 /planes
Ver todos los planes disponibles.

👥 /referidos
Programa de referidos.

📲 /apps
Descargar aplicaciones.

🏆 /agenda
Agenda deportiva actualizada.

🔄 /renovar
Solicitar una renovación.

💳 /pagar
Realizar un pago.

🛠 /soporte
Contactar con soporte.

━━━━━━━━━━━━━━━━━━

⭐ Gracias por confiar en MDPRIME.";

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

    case "/apps":
        $msg = "📲 APPS POR DOWNLOADER

Elige la app que más te guste.
La V9 es la más nueva.

🔥 V9 → 6713896
📺 OTT → 7669716
⚡ V8 → 6541023";
    break;

    case "/renovar":
        $states[$chat_id] = "renovar";
        file_put_contents($state_file, json_encode($states));

        $msg = "🔄 Envíame tu usuario MDPRIME para revisar tu renovación.";
    break;

    case "/pagar":
        $msg = "💳 PAGO SEGURO MDPRIME:

https://buy.stripe.com/7sYbJ19GFca2dBt8Qg6g80N

Después envía el comprobante.";
    break;

case "/soporte":
        $states[$chat_id] = "soporte";
        file_put_contents($state_file, json_encode($states));

        $msg = "🛠 Describe tu problema con detalle.";
    break;

/* =========================
   AGENDA DEPORTIVA
========================= */

case "/agenda":

    $json = @file_get_contents("https://agenda-mdprime.zeppplay7.workers.dev/json");

    if(!$json){
        $msg = "❌ No se pudo cargar la agenda deportiva.";
        break;
    }

    $agenda = json_decode($json, true);

    if(empty($agenda["events"])){
        $msg = "⚠️ No hay eventos disponibles.";
        break;
    }

    $msg = "🏆 AGENDA DEPORTIVA MDPRIME\n";
    $msg .= "📡 Fuente: zeppplay\n";
    $msg .= "📅 Eventos encontrados: ".$agenda["events_count"]."\n\n";
date_default_timezone_set("Europe/Madrid");
    $hoy = date("j");
$eventos_hoy = [];

foreach($agenda["events"] as $evento){
    if(strpos($evento["fecha"], " ".$hoy." de ") !== false){
        $eventos_hoy[] = $evento;
    }
}

    if(empty($eventos_hoy)){
    $msg = "⚠️ No hay eventos disponibles para hoy.";
    break;
}
    
    foreach($eventos_hoy as $evento){

    if(!empty($evento["fecha"])){
        $msg .= "📅 ".$evento["fecha"]."\n";
    }

    $msg .= "🕒 ".$evento["hora"]."\n";
    $msg .= "🏅 ".$evento["deporte"]."\n";

    if(!empty($evento["competicion"])){
        $msg .= "🏆 ".$evento["competicion"]."\n";
    }

    $msg .= "📌 ".$evento["evento"]."\n";

    if(!empty($evento["canal"])){
        $msg .= "📺 ".$evento["canal"]."\n";
    }

    $msg .= "━━━━━━━━━━━━━━\n";

    if(strlen($msg) > 3500){
        break;
    }
}

break;


case "/test":

    $msg = ini_get("allow_url_fopen");

break;


default:

        /* MODO RENOVAR */
        if($user_state == "renovar"){

            $admin_msg = "🔄 NUEVA RENOVACIÓN

Usuario: ".$text."

Chat ID: ".$chat_id;

            file_get_contents(
                "https://api.telegram.org/bot".$token."/sendMessage?".
                http_build_query([
                    "chat_id" => $admin_id,
                    "text" => $admin_msg
                ])
            );

            unset($states[$chat_id]);
            file_put_contents($state_file, json_encode($states));

            $msg = "✅ Solicitud de renovación enviada. Te responderemos pronto.";

        }

        /* MODO SOPORTE */
        elseif($user_state == "soporte"){

            $admin_msg = "🛠 NUEVO SOPORTE

Mensaje: ".$text."

Chat ID: ".$chat_id;

            file_get_contents(
                "https://api.telegram.org/bot".$token."/sendMessage?".
                http_build_query([
                    "chat_id" => $admin_id,
                    "text" => $admin_msg
                ])
            );

            unset($states[$chat_id]);
            file_put_contents($state_file, json_encode($states));

            $msg = "✅ Soporte recibido. Te responderemos pronto.";

        }

        else {

            $msg = "❌ Comando no reconocido.

Usa:
/planes
/referidos
/apps
/agenda
/renovar
/pagar
/soporte";

        }
}
/* =========================
   BORRAR MENSAJE DE ESPERA
========================= */

if($tempMessageId){

    file_get_contents(
        "https://api.telegram.org/bot".$token."/deleteMessage?".
        http_build_query([
            "chat_id" => $chat_id,
            "message_id" => $tempMessageId
        ])
    );

}

/* =========================
   RESPUESTA FINAL
========================= */

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
