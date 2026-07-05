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
$command = strtolower($text);

$username = isset($update["message"]["from"]["username"])
    ? "@".$update["message"]["from"]["username"]
    : "SIN USERNAME";

$states = file_exists($state_file)
    ? json_decode(file_get_contents($state_file), true)
    : [];

$user_state = $states[$chat_id] ?? "";

$msg = "";


/* EFECTO ESCRIBIENDO */
file_get_contents(
    "https://api.telegram.org/bot".$token."/sendChatAction?".
    http_build_query([
        "chat_id" => $chat_id,
        "action" => "typing"
    ])
);


switch($command){

    case "/start":
        $msg = "🔥 BIENVENIDO A MDPRIME OTT 🔥

📺 THE BEST STREAM

Selecciona una opción:

/planes
/referidos
/apps
/apkdescargas
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

    case "/apps":
        $msg = "📲 APPS POR DOWNLOADER

Elige la app que más te guste.
La V9 es la más nueva.

🔥 V9 → 6713896
📺 OTT → 7669716
⚡ V8 → 6541023";
    break;

    case "/apkdescargas":

        $msg = "📲 APK DESCARGAS MDPRIME

Selecciona la versión que quieras descargar:";

        $url = "https://api.telegram.org/bot".$token."/sendMessage";

        $data = [
            "chat_id" => $chat_id,
            "text" => $msg,
            "reply_markup" => json_encode([
                "inline_keyboard" => [
                    [
                        [
                            "text" => "🔥 V9",
                            "url" => "https://www.mediafire.com/file_premium/052b95nxpghvvxd/MDPRIME_V9.apk/file"
                        ]
                    ],
                    [
                        [
                            "text" => "📺 OTT",
                            "url" => "https://www.mediafire.com/file_premium/uqag8a35t45367k/ott-p2p.apk/file"
                        ]
                    ],
                    [
                        [
                            "text" => "⚡ V8",
                            "url" => "https://www.mediafire.com/file_premium/sh6zfsswt2tamdk/P2P_v8_V808M.apk/file"
                        ]
                    ]
                ]
            ])
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
    break;

    case "/renovar":
        $states[$chat_id] = "renovar";
        file_put_contents($state_file, json_encode($states));

        $msg = "⏳ Preparando renovación...

🔄 Envíame tu usuario MDPRIME para revisar tu renovación.";
    break;

    case "/pagar":
        $msg = "💳 PAGO SEGURO MDPRIME:

https://buy.stripe.com/7sYbJ19GFca2dBt8Qg6g80N

Después envía el comprobante.";
    break;

    case "/soporte":
        $states[$chat_id] = "soporte";
        file_put_contents($state_file, json_encode($states));

        $msg = "⏳ Preparando soporte...

🛠 Describe tu problema con detalle.";
    break;

    default:

        if($user_state == "renovar"){

            $admin_msg = "🔄 NUEVA RENOVACIÓN

Usuario MDPRIME: ".$text."

Usuario Telegram: ".$username;

            file_get_contents(
                "https://api.telegram.org/bot".$token."/sendMessage?".
                http_build_query([
                    "chat_id" => $admin_id,
                    "text" => $admin_msg
                ])
            );

            unset($states[$chat_id]);
            file_put_contents($state_file, json_encode($states));

            $msg = "✅ Solicitud enviada. Te responderemos pronto.";

        } elseif($user_state == "soporte"){

            $admin_msg = "🛠 NUEVO SOPORTE

Problema: ".$text."

Usuario Telegram: ".$username;

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

        } else {

            $msg = "❌ Comando no reconocido.

Usa:
/planes
/referidos
/apps
/apkdescargas
/renovar
/pagar
/soporte";
        }
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
