<?php

/* =========================
   MDPRIME TELEGRAM BOT
   Versión con Mi Cuenta + API InfinityFree
========================= */

$token = "8445421276:AAEgTw6jjvEI98YgnN9wZsAzE6MM8ajj_AQ";
$admin_id = "372918983";
$state_file = "states.json";

$api_cliente_url = "https://zeppplay-guia-mdprime.page.gd/api/cliente.php";
$api_key = "MDPRIME_API_2026";

/* =========================
   FUNCIONES TELEGRAM
========================= */

function telegramRequest($method, $data = []) {
    global $token;

    $url = "https://api.telegram.org/bot".$token."/".$method;

    $options = [
        "http" => [
            "header"  => "Content-type: application/x-www-form-urlencoded",
            "method"  => "POST",
            "content" => http_build_query($data),
            "timeout" => 12
        ]
    ];

    $context = stream_context_create($options);
    $response = @file_get_contents($url, false, $context);

    return $response ? json_decode($response, true) : null;
}

function sendMessage($chat_id, $text, $keyboard = true) {
    $data = [
        "chat_id" => $chat_id,
        "text" => $text
    ];

    if ($keyboard) {
        $data["reply_markup"] = json_encode([
            "keyboard" => [
                [
                    ["text" => "/planes"],
                    ["text" => "/referidos"]
                ],
                [
                    ["text" => "/micuenta"],
                    ["text" => "/caducidad"]
                ],
                [
                    ["text" => "/misreferidos"],
                    ["text" => "/cambiarusuario"]
                ],
                [
                    ["text" => "/queesreferidos"],
                    ["text" => "/apps"]
                ],
                [
                    ["text" => "/agenda"],
                    ["text" => "/renovar"]
                ],
                [
                    ["text" => "/pagar"],
                    ["text" => "/soporte"]
                ]
            ],
            "resize_keyboard" => true,
            "one_time_keyboard" => false
        ]);
    }

    return telegramRequest("sendMessage", $data);
}

function sendLongMessage($chat_id, $text, $keyboard = true) {
    $max = 3900;

    if (mb_strlen($text, "UTF-8") <= $max) {
        sendMessage($chat_id, $text, $keyboard);
        return;
    }

    $parts = preg_split("/\n(?=━━━━━━━━━━━━━━)/u", $text);
    $chunk = "";

    foreach ($parts as $part) {
        if (mb_strlen($chunk."\n".$part, "UTF-8") > $max) {
            sendMessage($chat_id, trim($chunk), $keyboard);
            $chunk = $part;
        } else {
            $chunk .= "\n".$part;
        }
    }

    if (trim($chunk) !== "") {
        sendMessage($chat_id, trim($chunk), $keyboard);
    }
}

function deleteMessage($chat_id, $message_id) {
    telegramRequest("deleteMessage", [
        "chat_id" => $chat_id,
        "message_id" => $message_id
    ]);
}

/* =========================
   FUNCIONES STATES
========================= */

function loadStates($file) {
    if (!file_exists($file)) {
        return [];
    }

    $json = file_get_contents($file);
    $data = json_decode($json, true);

    return is_array($data) ? $data : [];
}

function saveStates($file, $states) {
    file_put_contents($file, json_encode($states, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

function getUserMode($states, $chat_id) {
    if (!isset($states[$chat_id])) {
        return "";
    }

    if (is_array($states[$chat_id])) {
        return $states[$chat_id]["mode"] ?? "";
    }

    return $states[$chat_id];
}

function getSavedUsuario($states, $chat_id) {
    if (!isset($states[$chat_id]) || !is_array($states[$chat_id])) {
        return "";
    }

    return trim($states[$chat_id]["usuario_mdprime"] ?? "");
}

function setUserMode($file, &$states, $chat_id, $mode, $pending = "") {
    if (!isset($states[$chat_id]) || !is_array($states[$chat_id])) {
        $states[$chat_id] = [];
    }

    $states[$chat_id]["mode"] = $mode;

    if ($pending !== "") {
        $states[$chat_id]["pending_command"] = $pending;
    }

    saveStates($file, $states);
}

function clearUserMode($file, &$states, $chat_id) {
    if (!isset($states[$chat_id])) {
        return;
    }

    if (is_array($states[$chat_id])) {
        unset($states[$chat_id]["mode"]);
        unset($states[$chat_id]["pending_command"]);

        if (empty($states[$chat_id])) {
            unset($states[$chat_id]);
        }
    } else {
        unset($states[$chat_id]);
    }

    saveStates($file, $states);
}

function saveUsuarioMdprime($file, &$states, $chat_id, $usuario) {
    if (!isset($states[$chat_id]) || !is_array($states[$chat_id])) {
        $states[$chat_id] = [];
    }

    $states[$chat_id]["usuario_mdprime"] = trim($usuario);
    unset($states[$chat_id]["mode"]);
    unset($states[$chat_id]["pending_command"]);

    saveStates($file, $states);
}

/* =========================
   API MDPRIME
========================= */

function consultarClienteApi($usuario) {
    global $api_cliente_url, $api_key;

    $url = $api_cliente_url."?".http_build_query([
        "key" => $api_key,
        "usuario" => $usuario
    ]);

    $context = stream_context_create([
        "http" => [
            "timeout" => 15
        ]
    ]);

    $json = @file_get_contents($url, false, $context);

    if (!$json) {
        return [
            "ok" => false,
            "error" => "No se pudo conectar con el servidor MDPRIME"
        ];
    }

    $data = json_decode($json, true);

    if (!is_array($data)) {
        return [
            "ok" => false,
            "error" => "Respuesta no válida del servidor"
        ];
    }

    return $data;
}

function fmtDias($dias) {
    if ($dias === null || $dias === "") {
        return "Sin calcular";
    }

    $dias = (int)$dias;

    if ($dias > 0) {
        return $dias." días";
    }

    if ($dias === 0) {
        return "Caduca hoy";
    }

    return "Caducado hace ".abs($dias)." días";
}

function estadoIcono($estado) {
    return strtolower($estado) === "activo" ? "🟢" : "🔴";
}

function nivelIcono($nivel) {
    $nivel = strtoupper((string)$nivel);

    if ($nivel === "COBRE") return "🛡️";
    if ($nivel === "PLATA") return "⚜️";
    if ($nivel === "ORO") return "🏆";
    if ($nivel === "PLATINUM") return "💎";

    return "🔒";
}

function formatMiCuenta($data) {
    if (empty($data["ok"])) {
        return "❌ ".$data["error"];
    }

    $tipo = $data["tipo"] ?? "";

    if ($tipo === "referido" || isset($data["referido"])) {
        $ref = $data["referido"] ?? [];
        $referente = $data["referente"] ?? [];

        $estado = $ref["estado"] ?? "Sin estado";
        $caducidad = $ref["caducidad"] ?? ($ref["fecha_caducidad"] ?? "Sin fecha");
        $alta = $ref["fecha_alta"] ?? "Sin fecha";
        $dias = $ref["dias"] ?? null;

        return "👤 MI CUENTA MDPRIME

━━━━━━━━━━━━━━━━━━

🙋 Usuario:
".($ref["nombre"] ?? "Sin nombre")."

👥 Referente:
".($referente["nombre"] ?? "Sin referente")."

".estadoIcono($estado)." Estado:
".$estado."

📅 Alta:
".$alta."

📅 Caducidad:
".$caducidad."

⏳ Tiempo restante:
".fmtDias($dias)."

━━━━━━━━━━━━━━━━━━

⭐ Gracias por confiar en MDPRIME.";
    }

    $cliente = $data["cliente"] ?? [];
    $resumen = $data["resumen"] ?? [];
    $nivel = $data["nivel"] ?? [];
    $siguiente = $data["siguiente_nivel"] ?? null;

    $nivelNombre = $nivel["actual"] ?? "SIN NIVEL";

    $msg = "👤 MI CUENTA MDPRIME

━━━━━━━━━━━━━━━━━━

🙋 Referente:
".($cliente["nombre"] ?? "Sin nombre")."

📲 Telegram:
".(($cliente["telegram"] ?? "") !== "" ? "@".$cliente["telegram"] : "Sin Telegram")."

".nivelIcono($nivelNombre)." Nivel:
".$nivelNombre."

👥 Referidos totales:
".($resumen["total_referidos"] ?? 0)."

🟢 Activos:
".($resumen["activos"] ?? 0)."

🔴 Inactivos:
".($resumen["inactivos"] ?? 0)."

📅 Próxima caducidad:
".($resumen["proxima_caducidad"] ?? "Sin fecha")."

⏳ Tiempo restante:
".fmtDias($resumen["dias_proxima_caducidad"] ?? null)."

━━━━━━━━━━━━━━━━━━

💶 TUS TARIFAS

3 meses → ".($nivel["precio_3_meses"] ?? 0)."€
6 meses → ".($nivel["precio_6_meses"] ?? 0)."€
12 meses → ".($nivel["precio_12_meses"] ?? 0)."€";

    if ($siguiente) {
        $msg .= "

━━━━━━━━━━━━━━━━━━

🎯 Próximo nivel:
".nivelIcono($siguiente["nivel"] ?? "")." ".($siguiente["nivel"] ?? "")."

Te faltan:
".($siguiente["faltan"] ?? 0)." referidos";
    } else {
        $msg .= "

━━━━━━━━━━━━━━━━━━

💎 Ya estás en el nivel máximo.";
    }

    return $msg;
}

function formatCaducidad($data) {
    if (empty($data["ok"])) {
        return "❌ ".$data["error"];
    }

    $tipo = $data["tipo"] ?? "";

    if ($tipo === "referido" || isset($data["referido"])) {
        $ref = $data["referido"] ?? [];
        $estado = $ref["estado"] ?? "Sin estado";

        return "📅 CADUCIDAD MDPRIME

━━━━━━━━━━━━━━━━━━

👤 Usuario:
".($ref["nombre"] ?? "Sin nombre")."

".estadoIcono($estado)." Estado:
".$estado."

📅 Caduca:
".($ref["caducidad"] ?? "Sin fecha")."

⏳ Tiempo restante:
".fmtDias($ref["dias"] ?? null);
    }

    $cliente = $data["cliente"] ?? [];
    $resumen = $data["resumen"] ?? [];

    return "📅 CADUCIDAD REFERIDOS

━━━━━━━━━━━━━━━━━━

👤 Referente:
".($cliente["nombre"] ?? "Sin nombre")."

👥 Referidos activos:
".($resumen["activos"] ?? 0)."

📅 Próxima caducidad:
".($resumen["proxima_caducidad"] ?? "Sin fecha")."

⏳ Tiempo restante:
".fmtDias($resumen["dias_proxima_caducidad"] ?? null);
}

function formatMisReferidos($data) {
    if (empty($data["ok"])) {
        return "❌ ".$data["error"];
    }

    $tipo = $data["tipo"] ?? "";

    if ($tipo === "referido" || isset($data["referido"])) {
        return formatMiCuenta($data);
    }

    $cliente = $data["cliente"] ?? [];
    $resumen = $data["resumen"] ?? [];
    $referidos = $data["referidos"] ?? [];

    $msg = "👥 MIS REFERIDOS MDPRIME

━━━━━━━━━━━━━━━━━━

🙋 Referente:
".($cliente["nombre"] ?? "Sin nombre")."

👥 Total:
".($resumen["total_referidos"] ?? count($referidos))."

🟢 Activos:
".($resumen["activos"] ?? 0)."

🔴 Inactivos:
".($resumen["inactivos"] ?? 0)."

━━━━━━━━━━━━━━━━━━";

    if (empty($referidos)) {
        $msg .= "

No tienes referidos registrados.";
        return $msg;
    }

    foreach ($referidos as $i => $ref) {
        $estado = $ref["estado"] ?? "Sin estado";

        $msg .= "

━━━━━━━━━━━━━━
#".($i + 1)." ".estadoIcono($estado)." ".($ref["nombre"] ?? "Sin nombre")."

📌 Estado: ".$estado."
📅 Alta: ".($ref["fecha_alta"] ?? "Sin fecha")."
📅 Caduca: ".($ref["caducidad"] ?? "Sin fecha")."
⏳ ".fmtDias($ref["dias"] ?? null);

        if (!empty($ref["nota"])) {
            $msg .= "
📝 ".$ref["nota"];
        }
    }

    return $msg;
}

function procesarCuenta($chat_id, $usuario, $tipo = "/micuenta") {
    $espera = sendMessage($chat_id, "⏳ Consultando datos MDPRIME...", false);
    $espera_id = $espera["result"]["message_id"] ?? null;

    $data = consultarClienteApi($usuario);

    if ($espera_id) {
        deleteMessage($chat_id, $espera_id);
    }

    if (empty($data["ok"])) {
        sendMessage($chat_id, "❌ No he encontrado ese usuario guardado.

Pulsa:
/cambiarusuario

O prueba directo así:
/caducidad Brandon10

Recuerda escribirlo exactamente como aparece en el panel.");
        return;
    }

    if ($tipo === "/caducidad") {
        sendLongMessage($chat_id, formatCaducidad($data));
        return;
    }

    if ($tipo === "/misreferidos") {
        sendLongMessage($chat_id, formatMisReferidos($data));
        return;
    }

    sendLongMessage($chat_id, formatMiCuenta($data));
}

/* =========================
   RECIBIR UPDATE
========================= */

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
$text = trim($update["message"]["text"] ?? "");

if ($text === "") {
    http_response_code(200);
    exit;
}

$command = strtolower(trim(explode(" ", $text)[0]));
$command = explode("@", $command)[0];

$parts_text = explode(" ", $text, 2);
$command_arg = isset($parts_text[1]) ? trim($parts_text[1]) : "";

$states = loadStates($state_file);
$user_state = getUserMode($states, $chat_id);
$saved_usuario = getSavedUsuario($states, $chat_id);

/* =========================
   RESPONDER A CLIENTE ADMIN
========================= */

if ($command === "/reply") {

    $parts = explode(" ", $text, 3);

    if ((string)$chat_id !== (string)$admin_id) {
        sendMessage($chat_id, "❌ Comando reservado para administración.");
        http_response_code(200);
        exit;
    }

    if (count($parts) >= 3) {

        $reply_chat = trim($parts[1]);
        $reply_msg = trim($parts[2]);

        sendMessage($reply_chat, "📩 SOPORTE MDPRIME:

".$reply_msg, false);

        sendMessage($chat_id, "✅ Mensaje enviado correctamente.");

    } else {

        sendMessage($chat_id, "Uso correcto:
/reply CHATID mensaje");

    }

    http_response_code(200);
    exit;
}

/* =========================
   RESPUESTAS POR ESTADO
========================= */

if ($user_state === "esperando_usuario_mdprime") {

    $usuario = trim($text);
    $pending = is_array($states[$chat_id] ?? null) ? ($states[$chat_id]["pending_command"] ?? "/micuenta") : "/micuenta";

    $espera = sendMessage($chat_id, "⏳ Comprobando usuario MDPRIME...", false);
    $espera_id = $espera["result"]["message_id"] ?? null;

    $data = consultarClienteApi($usuario);

    if ($espera_id) {
        deleteMessage($chat_id, $espera_id);
    }

    if (empty($data["ok"])) {
        sendMessage($chat_id, "❌ No he encontrado ese usuario.

No lo he guardado.

Prueba escribiéndolo exactamente como aparece en el panel.

Ejemplos:
Canelobel
BELTROL
Brandon10");
        http_response_code(200);
        exit;
    }

    saveUsuarioMdprime($state_file, $states, $chat_id, $usuario);

    sendMessage($chat_id, "✅ Usuario guardado:
".$usuario."

A partir de ahora podrás consultar tu cuenta directamente.");

    if ($pending === "/caducidad") {
        sendLongMessage($chat_id, formatCaducidad($data));
    } elseif ($pending === "/misreferidos") {
        sendLongMessage($chat_id, formatMisReferidos($data));
    } else {
        sendLongMessage($chat_id, formatMiCuenta($data));
    }

    http_response_code(200);
    exit;
}

if ($user_state === "renovar") {

    $admin_msg = "🔄 NUEVA RENOVACIÓN

Usuario: ".$text."

Chat ID: ".$chat_id;

    sendMessage($admin_id, $admin_msg, false);

    clearUserMode($state_file, $states, $chat_id);

    sendMessage($chat_id, "✅ Solicitud de renovación enviada. Te responderemos pronto.");

    http_response_code(200);
    exit;
}

if ($user_state === "soporte") {

    $admin_msg = "🛠 NUEVO SOPORTE

Mensaje: ".$text."

Chat ID: ".$chat_id;

    sendMessage($admin_id, $admin_msg, false);

    clearUserMode($state_file, $states, $chat_id);

    sendMessage($chat_id, "✅ Soporte recibido. Te responderemos pronto.");

    http_response_code(200);
    exit;
}

/* =========================
   COMANDOS PRINCIPALES
========================= */

switch ($command) {

    case "/start":

        $msg = "🔥 BIENVENIDO A MDPRIME 🔥

📺 BOT AUTOMATIZADO

━━━━━━━━━━━━━━━━━━

📋 MENÚ PRINCIPAL

💎 /planes
Ver todos los planes disponibles.

👥 /referidos
Consultar tarifas del programa de referidos.

❓ /queesreferidos
¿Qué es el programa de referidos?

👤 /micuenta
Consultar tu cuenta MDPRIME.

📅 /caducidad
Ver caducidad de tu cuenta o referidos.

👥 /misreferidos
Ver tus referidos activos e inactivos.

🔄 /cambiarusuario
Cambiar el usuario guardado.

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

        sendMessage($chat_id, $msg);
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

        sendMessage($chat_id, $msg);
        break;

    case "/queesreferidos":

        $msg = "👥 ¿QUÉ ES REFERIDOS?

━━━━━━━━━━━━━━━━━━

📢 Planes de Referidos por Recomendación

Recomienda MDPRIME a tus amigos y gana recompensas por cada nuevo cliente que contrate gracias a ti.

🎁 Cuantos más referidos activos tengas, mayores serán tus beneficios.

━━━━━━━━━━━━━━━━━━

✅ Recomiendas MDPRIME
✅ Tu amigo contrata
✅ Ganas mejores beneficios";

        sendMessage($chat_id, $msg);
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

        sendMessage($chat_id, $msg);
        break;

    case "/apps":

        $msg = "📲 APPS POR DOWNLOADER

Elige la app que más te guste.
La V9 es la más nueva.

🔥 V9 → 6713896
📺 OTT → 7669716
⚡ V8 → 6541023";

        sendMessage($chat_id, $msg);
        break;

    case "/micuenta":
    case "/caducidad":
    case "/misreferidos":

        if ($command_arg !== "") {
            procesarCuenta($chat_id, $command_arg, $command);
        } elseif ($saved_usuario !== "") {
            procesarCuenta($chat_id, $saved_usuario, $command);
        } else {
            setUserMode($state_file, $states, $chat_id, "esperando_usuario_mdprime", $command);

            sendMessage($chat_id, "👤 Introduce tu usuario MDPRIME.

Puede ser:

• Tu nombre de referente
• Tu usuario de Telegram registrado
• El nombre del referido

Ejemplo:
Canelobel");
        }

        break;

    case "/cambiarusuario":

        setUserMode($state_file, $states, $chat_id, "esperando_usuario_mdprime", "/micuenta");

        sendMessage($chat_id, "🔄 CAMBIAR USUARIO

Introduce el nuevo usuario MDPRIME que quieres guardar.");

        break;

    case "/renovar":

        setUserMode($state_file, $states, $chat_id, "renovar");

        sendMessage($chat_id, "🔄 Envíame tu usuario MDPRIME para revisar tu renovación.");
        break;

    case "/pagar":

        $msg = "💳 PAGO SEGURO MDPRIME:

https://buy.stripe.com/7sYbJ19GFca2dBt8Qg6g80N

Después envía el comprobante.";

        sendMessage($chat_id, $msg);
        break;

    case "/soporte":

        setUserMode($state_file, $states, $chat_id, "soporte");

        sendMessage($chat_id, "🛠 Describe tu problema con detalle.");
        break;

    case "/agenda":

        $espera = sendMessage($chat_id, "⏳ Cargando agenda deportiva...", false);
        $espera_id = $espera["result"]["message_id"] ?? null;

        $json = @file_get_contents("https://agenda-mdprime.zeppplay7.workers.dev/json");

        if ($espera_id) {
            deleteMessage($chat_id, $espera_id);
        }

        if (!$json) {
            sendMessage($chat_id, "❌ No se pudo cargar la agenda deportiva.");
            break;
        }

        $agenda = json_decode($json, true);

        if (empty($agenda["events"])) {
            sendMessage($chat_id, "⚠️ No hay eventos disponibles.");
            break;
        }

        $primerDia = $agenda["events"][0]["fecha"];
        $eventos = [];

        foreach ($agenda["events"] as $evento) {
            if ($evento["fecha"] == $primerDia) {
                $eventos[] = $evento;
            }
        }

        $msg = "🏆 AGENDA DEPORTIVA MDPRIME\n";
        $msg .= "📡 Fuente: zeppplay\n";
        $msg .= "📅 ".$primerDia."\n";
        $msg .= "🎯 Eventos: ".count($eventos)."\n\n";

        foreach ($eventos as $evento) {

            $msg .= "🕒 ".$evento["hora"]."\n";
            $msg .= "🏅 ".$evento["deporte"]."\n";

            if (!empty($evento["competicion"])) {
                $msg .= "🏆 ".$evento["competicion"]."\n";
            }

            $msg .= "📌 ".$evento["evento"]."\n";

            if (!empty($evento["canal"])) {
                $msg .= "📺 ".$evento["canal"]."\n";
            }

            $msg .= "━━━━━━━━━━━━━━\n";

            if (mb_strlen($msg, "UTF-8") > 3500) {
                break;
            }
        }

        sendLongMessage($chat_id, $msg);
        break;

    case "/test":

        sendMessage($chat_id, ini_get("allow_url_fopen"));
        break;

    default:

        $msg = "❌ Comando no reconocido.

Usa:
/planes
/referidos
/micuenta
/caducidad
/misreferidos
/apps
/agenda
/renovar
/pagar
/soporte";

        sendMessage($chat_id, $msg);
        break;
}

http_response_code(200);
exit;

?>
