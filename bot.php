<?php

/* =========================
   RUTAS TEMPORALES WEB
========================= */
if (isset($_SERVER["REQUEST_URI"])) {

    if (strpos($_SERVER["REQUEST_URI"], "ping") !== false) {
        header("Content-Type: text/plain; charset=utf-8");
        echo "OK";
        exit;
    }


    if (strpos($_SERVER["REQUEST_URI"], "fix_fechas_railway.php") !== false) {
        header("Content-Type: text/plain; charset=utf-8");

        if (($_GET["key"] ?? "") !== "MDPRIME_FIX_DATES_2026") {
            http_response_code(403);
            exit("❌ Acceso no autorizado.");
        }

        $db_host_tmp = "reseau.proxy.rlwy.net";
        $db_port_tmp = 39553;
        $db_name_tmp = "railway";
        $db_user_tmp = "root";
        $db_pass_tmp = "ZRNWfdsxefUJrBMSJMchlLxzMHrAZjug";

        try {
            $pdo = new PDO(
                "mysql:host=$db_host_tmp;port=$db_port_tmp;dbname=$db_name_tmp;charset=utf8mb4",
                $db_user_tmp,
                $db_pass_tmp,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]
            );

            echo "🚀 MDPRIME FIX FECHAS INTEGRADO\n";
            echo "━━━━━━━━━━━━━━━━━━━━━━\n";
            echo "✅ Conectado a Railway.\n\n";

            $pdo->exec("UPDATE referidos SET fecha_alta = NULL WHERE fecha_alta IS NOT NULL AND YEAR(fecha_alta) = 0");
            $pdo->exec("UPDATE referidos SET fecha_caducidad = NULL WHERE fecha_caducidad IS NOT NULL AND YEAR(fecha_caducidad) = 0");

            $total = $pdo->query("SELECT COUNT(*) FROM referidos")->fetchColumn();
            $cadNulas = $pdo->query("SELECT COUNT(*) FROM referidos WHERE fecha_caducidad IS NULL")->fetchColumn();

            echo "✅ Fechas inválidas corregidas.\n";
            echo "Referidos: ".$total."\n";
            echo "Caducidades sin fecha: ".$cadNulas."\n\n";
            echo "🎉 FIX TERMINADO.\n";
            echo "Ahora prueba /debugmd Brandon10 en Telegram.\n";
            exit;

        } catch (Throwable $e) {
            echo "❌ ERROR FIX\n";
            echo $e->getMessage()."\n";
            exit;
        }
    }

    if (strpos($_SERVER["REQUEST_URI"], "importar_railway.php") !== false) {
        if (file_exists(__DIR__ . "/importar_railway.php")) {
            require __DIR__ . "/importar_railway.php";
            exit;
        }
        header("Content-Type: text/plain; charset=utf-8");
        exit("❌ No encuentro importar_railway.php en Render.");
    }
}

/* =========================
   MDPRIME TELEGRAM BOT
   Versión con Mi Cuenta + API InfinityFree
========================= */

$token = "8445421276:AAEgTw6jjvEI98YgnN9wZsAzE6MM8ajj_AQ";
$admin_id = "372918983";
$bot_username = "MDPRIME_SUPPOR_BOT";
$bot_link = "https://t.me/MDPRIME_SUPPOR_BOT";
$payment_link = "https://buy.stripe.com/7sYbJ19GFca2dBt8Qg6g80N";
$state_file = "states.json";
$agenda_cache_file = __DIR__ . "/agenda_cache.json";

$api_cliente_url = "https://zeppplay-guia-mdprime.page.gd/api/cliente.php";
$api_key = "MDPRIME_API_2026";

$db_host = "reseau.proxy.rlwy.net";
$db_port = 39553;
$db_name = "railway";
$db_user = "root";
$db_pass = "ZRNWfdsxefUJrBMSJMchlLxzMHrAZjug";
$bot_version = "MDPRIME-BOT-V65-CONVERSION-MYSQL-20260712";

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

function telegramHtml($text) {
    return htmlspecialchars((string)$text, ENT_QUOTES | ENT_SUBSTITUTE, "UTF-8");
}

function configurarComandosTelegram() {
    $commands = [
        ["command" => "start", "description" => "🏠 Abrir MDPRIME"],
        ["command" => "identificate", "description" => "👤 Identificar cuenta"],
        ["command" => "nuevo", "description" => "🆕 Crear nuevo usuario"],
        ["command" => "cancelar", "description" => "❌ Cancelar proceso"]
    ];

    telegramRequest("deleteMyCommands", ["scope" => json_encode(["type" => "default"])]);
    telegramRequest("deleteMyCommands", ["scope" => json_encode(["type" => "all_private_chats"])]);

    $global = telegramRequest("setMyCommands", [
        "commands" => json_encode($commands, JSON_UNESCAPED_UNICODE),
        "scope" => json_encode(["type" => "default"])
    ]);
    $privado = telegramRequest("setMyCommands", [
        "commands" => json_encode($commands, JSON_UNESCAPED_UNICODE),
        "scope" => json_encode(["type" => "all_private_chats"])
    ]);

    return (!empty($global["ok"]) || !empty($privado["ok"]))
        ? ["ok" => true, "global" => $global, "privado" => $privado]
        : ["ok" => false, "global" => $global, "privado" => $privado];
}

function sendMessage($chat_id, $text, $keyboard = true, $parse_mode = null) {
    $data = [
        "chat_id" => $chat_id,
        "text" => $text,
        "disable_notification" => ((string)$chat_id !== (string)abs((int)$chat_id))
    ];

    if ($parse_mode !== null && $parse_mode !== "") {
        $data["parse_mode"] = $parse_mode;
    }

    if ($keyboard) {
        $data["reply_markup"] = json_encode([
            "keyboard" => [
                [["text" => "/start"]],
                [["text" => "/identificate"]],
                [["text" => "🆕 Nuevo usuario"]],
                [["text" => "/cancelar"]]
            ],
            "resize_keyboard" => true,
            "one_time_keyboard" => false
        ]);
    }

    return telegramRequest("sendMessage", $data);
}



function tecladoConfirmarUsuarioNoEncontrado() {
    return [
        "inline_keyboard" => [
            [["text" => "✅ Sí, continuar con precio normal", "callback_data" => "ren_no_encontrado_si"]],
            [["text" => "✏️ No, escribir de nuevo", "callback_data" => "ren_no_encontrado_no"]]
        ]
    ];
}

function tecladoConfirmarNombreProceso($tipo) {
    if ($tipo === "nuevo") {
        return [
            "inline_keyboard" => [
                [["text" => "✅ Sí, crear esta cuenta", "callback_data" => "confirmar_nuevo_si"]],
                [["text" => "✏️ No, cambiar el nombre", "callback_data" => "confirmar_nuevo_no"]]
            ]
        ];
    }

    return [
        "inline_keyboard" => [
            [["text" => "✅ Sí, continuar", "callback_data" => "confirmar_renovar_si"]],
            [["text" => "✏️ No, escribir de nuevo", "callback_data" => "confirmar_renovar_no"]]
        ]
    ];
}

function pedirConfirmacionNombreProceso($state_file, &$states, $chat_id, $usuario, $tipo) {
    $usuario = trim((string)$usuario);

    if (!isset($states[$chat_id]) || !is_array($states[$chat_id])) {
        $states[$chat_id] = [];
    }

    $states[$chat_id]["mode"] = $tipo === "nuevo" ? "confirmando_nombre_nuevo" : "confirmando_nombre_renovar";
    $states[$chat_id]["nombre_proceso_pendiente"] = $usuario;
    saveStates($state_file, $states);

    if ($tipo === "nuevo") {
        $texto = "⚠️ CONFIRMA EL NOMBRE

━━━━━━━━━━━━━━━━━━

Vas a crear la cuenta:

👤 ".$usuario."

Comprueba que está escrito exactamente como deseas.

¿Es correcto?";
    } else {
        $texto = "⚠️ CONFIRMA EL USUARIO

━━━━━━━━━━━━━━━━━━

Has escrito el usuario:

👤 ".$usuario."

Comprueba que está correctamente escrito antes de renovar.

¿Es correcto?";
    }

    sendInlineMessage($chat_id, $texto, tecladoConfirmarNombreProceso($tipo));
}

function tecladoConfirmarUsuarioMdprime() {
    return [
        "inline_keyboard" => [
            [
                ["text" => "✅ Sí, es mi usuario", "callback_data" => "confirm_usuario_si"]
            ],
            [
                ["text" => "✏️ No, escribir otro", "callback_data" => "confirm_usuario_no"]
            ]
        ]
    ];
}

function resumenConfirmacionUsuario($usuario, $data) {
    if (!empty($data["referido"])) {
        $r = $data["referido"];
        $referente = $data["referente"]["nombre"] ?? "Sin referente";
        $estado = $r["estado"] ?? "Sin estado";
        return "🔎 USUARIO ENCONTRADO

━━━━━━━━━━━━━━━━━━

👤 Usuario:
".($r["nombre"] ?? $usuario)."

👥 Referente:
".$referente."

".estadoIcono($estado)." Estado:
".$estado."

📅 Caduca:
".($r["caducidad"] ?? "Sin fecha")."

━━━━━━━━━━━━━━━━━━

¿Confirmas que esta cuenta es tuya?

⚠️ Si no es correcta, pulsa No y escribe tu usuario nuevamente.";
    }

    if (!empty($data["cliente_normal"])) {
        $n = $data["cliente_normal"];
        $estado = $n["estado"] ?? "Sin estado";
        return "🔎 USUARIO ENCONTRADO

━━━━━━━━━━━━━━━━━━

👤 Usuario:
".($n["nombre"] ?? $usuario)."

💳 Tipo de cuenta:
Cliente normal / no referido

".estadoIcono($estado)." Estado:
".$estado."

📅 Caduca:
".($n["caducidad"] ?? "Sin fecha")."

━━━━━━━━━━━━━━━━━━

¿Confirmas que esta cuenta es tuya?

⚠️ Si no es correcta, pulsa No y escribe tu usuario nuevamente.";
    }

    if (!empty($data["cliente"])) {
        $c = $data["cliente"];
        return "🔎 USUARIO ENCONTRADO

━━━━━━━━━━━━━━━━━━

👤 Usuario:
".($c["nombre"] ?? $usuario)."

👑 Tipo de cuenta:
Referente VIP

👥 Referidos activos:
".($data["resumen"]["activos"] ?? "0")."

🏆 Nivel:
".($data["nivel"]["actual"] ?? "Sin nivel")."

━━━━━━━━━━━━━━━━━━

¿Confirmas que esta cuenta es tuya?

⚠️ Si no es correcta, pulsa No y escribe tu usuario nuevamente.";
    }

    return "🔎 Hemos encontrado este usuario.

👤 Usuario:
".$usuario."

¿Confirmas que esta cuenta es tuya?";
}

function mdprimeSesionActivaTexto($chat_id, $states) {
    $usuario = "";
    if (isset($states[$chat_id]) && is_array($states[$chat_id])) {
        $usuario = trim($states[$chat_id]["usuario_mdprime"] ?? "");
    }

    if ($usuario === "") {
        return "";
    }

    return "👤 Cuenta vinculada actualmente:
".$usuario."

Si esta no es tu cuenta, pulsa:
/cambiarusuario

━━━━━━━━━━━━━━━━━━

";
}

function mensajeAvisoCaducidadMdprime($usuario, $tipo, $dias, $fecha) {
    $fechaTxt = fechaBonita($fecha);

    if ($dias === 7) {
        $titulo = "⏳ Tu cuenta caduca en 7 días.";
        $extra = "Puedes renovarla cómodamente desde el bot antes de que caduque.";
    } elseif ($dias === 3) {
        $titulo = "⚠️ Tu cuenta caduca en 3 días.";
        $extra = "Te recomendamos renovarla para evitar interrupciones.";
    } elseif ($dias === 1) {
        $titulo = "🚨 Tu cuenta caduca mañana.";
        $extra = "Renueva hoy para mantener tu cuenta activa.";
    } else {
        $titulo = "❌ Tu cuenta caduca hoy.";
        $extra = "Puedes renovarla ahora mismo desde el bot.";
    }

    return $titulo."

━━━━━━━━━━━━━━━━━━

👤 Usuario:
".$usuario."

💳 Tipo:
".$tipo."

📅 Fecha de caducidad:
".$fechaTxt."

".$extra."

━━━━━━━━━━━━━━━━━━

Para renovar pulsa:
/renovar

Si tienes cualquier problema, pulsa:
/soporte";
}

function enviarAvisosCaducidadMdprime() {
    global $state_file, $bot_version;

    $resumen = [
        "ok" => true,
        "version" => $bot_version,
        "enviados" => 0,
        "errores" => 0,
        "detalle" => []
    ];

    try {
        $pdo = getRailwayPdo();

        try {
            $pdo->exec("CREATE TABLE IF NOT EXISTS avisos_caducidad_bot(
                id INT AUTO_INCREMENT PRIMARY KEY,
                usuario VARCHAR(150) NOT NULL,
                tipo_tabla VARCHAR(40) NOT NULL,
                dias INT NOT NULL,
                fecha_caducidad DATE NOT NULL,
                chat_id VARCHAR(80) NOT NULL,
                enviado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY uniq_aviso(usuario,tipo_tabla,dias,fecha_caducidad,chat_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        } catch (Throwable $e) {}

        $states = loadStates($state_file);

        $tablas = [
            ["tabla" => "referidos", "tipo" => "Referido VIP"],
            ["tabla" => "clientes_normales", "tipo" => "Cliente normal / no referido"]
        ];

        foreach ($tablas as $info) {
            $tabla = $info["tabla"];
            $tipo = $info["tipo"];

            try {
                $rows = $pdo->query("
                    SELECT id, nombre, fecha_caducidad, estado
                    FROM ".$tabla."
                    WHERE fecha_caducidad IS NOT NULL
                      AND fecha_caducidad <> '0000-00-00'
                      AND DATEDIFF(fecha_caducidad, CURDATE()) IN (0,1,3,7)
                ")->fetchAll();
            } catch (Throwable $e) {
                $resumen["errores"]++;
                $resumen["detalle"][] = "Error tabla ".$tabla.": ".$e->getMessage();
                continue;
            }

            foreach ($rows as $row) {
                $usuario = trim($row["nombre"] ?? "");
                $cad = $row["fecha_caducidad"] ?? "";

                if ($usuario === "" || $cad === "") {
                    continue;
                }

                $dias = (int)floor((strtotime($cad) - strtotime(date("Y-m-d"))) / 86400);

                if (!in_array($dias, [0, 1, 3, 7], true)) {
                    continue;
                }

                foreach ($states as $chatId => $st) {
                    if (!is_array($st)) continue;

                    $guardado = trim($st["usuario_mdprime"] ?? "");
                    if ($guardado === "") continue;

                    if (mb_strtolower($guardado, "UTF-8") !== mb_strtolower($usuario, "UTF-8")) {
                        continue;
                    }

                    $chk = $pdo->prepare("SELECT id FROM avisos_caducidad_bot WHERE usuario=? AND tipo_tabla=? AND dias=? AND fecha_caducidad=? AND chat_id=? LIMIT 1");
                    $chk->execute([$usuario, $tabla, $dias, $cad, (string)$chatId]);

                    if ($chk->fetch()) {
                        continue;
                    }

                    $sent = sendMessage($chatId, mensajeAvisoCaducidadMdprime($usuario, $tipo, $dias, $cad));

                    if (!empty($sent["ok"])) {
                        $ins = $pdo->prepare("INSERT IGNORE INTO avisos_caducidad_bot(usuario,tipo_tabla,dias,fecha_caducidad,chat_id) VALUES(?,?,?,?,?)");
                        $ins->execute([$usuario, $tabla, $dias, $cad, (string)$chatId]);

                        $resumen["enviados"]++;
                        $resumen["detalle"][] = $usuario." · ".$tipo." · ".$dias." días";
                    } else {
                        $resumen["errores"]++;
                        $resumen["detalle"][] = "No enviado a ".$usuario." · chat ".$chatId;
                    }
                }
            }
        }

    } catch (Throwable $e) {
        $resumen["ok"] = false;
        $resumen["error"] = $e->getMessage();
    }

    return $resumen;
}


function asegurarTablaSolicitudesConversion() {
    $pdo = getRailwayPdo();
    $pdo->exec("CREATE TABLE IF NOT EXISTS solicitudes_conversion (
        id INT AUTO_INCREMENT PRIMARY KEY,
        chat_id VARCHAR(80) NOT NULL,
        normal_id INT NOT NULL,
        normal_nombre VARCHAR(150) NOT NULL,
        referente_id INT NOT NULL,
        referente_nombre VARCHAR(150) NOT NULL,
        estado ENUM('pendiente','aprobada','rechazada') NOT NULL DEFAULT 'pendiente',
        creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        resuelto_en TIMESTAMP NULL DEFAULT NULL,
        resuelto_por VARCHAR(80) DEFAULT NULL,
        UNIQUE KEY uniq_conversion_pendiente (normal_id, referente_id, estado),
        KEY idx_conversion_estado (estado),
        KEY idx_conversion_chat (chat_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    return $pdo;
}

function crearSolicitudConversionMysql($chat_id, $normal_id, $normal_nombre, $referente_id, $referente_nombre) {
    try {
        $pdo = asegurarTablaSolicitudesConversion();
        $chk = $pdo->prepare("SELECT id FROM solicitudes_conversion WHERE normal_id=? AND estado='pendiente' ORDER BY id DESC LIMIT 1");
        $chk->execute([(int)$normal_id]);
        $existente = $chk->fetchColumn();
        if ($existente) return ["ok"=>true,"id"=>(int)$existente,"existente"=>true];
        $ins = $pdo->prepare("INSERT INTO solicitudes_conversion(chat_id,normal_id,normal_nombre,referente_id,referente_nombre) VALUES(?,?,?,?,?)");
        $ins->execute([(string)$chat_id,(int)$normal_id,trim((string)$normal_nombre),(int)$referente_id,trim((string)$referente_nombre)]);
        return ["ok"=>true,"id"=>(int)$pdo->lastInsertId(),"existente"=>false];
    } catch (Throwable $e) {
        return ["ok"=>false,"error"=>$e->getMessage()];
    }
}

function tecladoAdminConvertirNormalAReferido($solicitud_id) {
    $solicitud_id = (int)$solicitud_id;
    return [
        "inline_keyboard" => [
            [["text" => "✅ Aprobar conversión", "callback_data" => "adm_refconv_si_".$solicitud_id]],
            [["text" => "❌ Rechazar solicitud", "callback_data" => "adm_refconv_no_".$solicitud_id]]
        ]
    ];
}

function tecladoAdminPromoverNormalAReferente($cliente_chat_id) {
    $cliente_chat_id = preg_replace('/[^0-9\-]/', '', (string)$cliente_chat_id);
    return [
        "inline_keyboard" => [
            [["text" => "✅ Aprobar como Referente VIP", "callback_data" => "adm_refprom_si_".$cliente_chat_id]],
            [["text" => "❌ Rechazar solicitud", "callback_data" => "adm_refprom_no_".$cliente_chat_id]]
        ]
    ];
}

function sendInlineMessage($chat_id, $text, $reply_markup = null) {
    $data = [
        "chat_id" => $chat_id,
        "text" => $text,
        "disable_notification" => ((string)$chat_id !== (string)abs((int)$chat_id))
    ];

    if ($reply_markup) {
        $data["reply_markup"] = json_encode($reply_markup);
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

function forwardMessage($to_chat_id, $from_chat_id, $message_id) {
    return telegramRequest("forwardMessage", [
        "chat_id" => $to_chat_id,
        "from_chat_id" => $from_chat_id,
        "message_id" => $message_id,
        "disable_notification" => false
    ]);
}

function answerCallbackQuery($callback_query_id, $text = "") {
    $data = [
        "callback_query_id" => $callback_query_id
    ];

    if ($text !== "") {
        $data["text"] = $text;
    }

    return telegramRequest("answerCallbackQuery", $data);
}

function editMessageText($chat_id, $message_id, $text, $reply_markup = null, $parse_mode = null) {
    $data = [
        "chat_id" => $chat_id,
        "message_id" => $message_id,
        "text" => $text
    ];

    if ($reply_markup) {
        $data["reply_markup"] = json_encode($reply_markup);
    }

    if ($parse_mode !== null && $parse_mode !== "") {
        $data["parse_mode"] = $parse_mode;
    }

    return telegramRequest("editMessageText", $data);
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
        unset($states[$chat_id]["renovar_data"]);
        unset($states[$chat_id]["nuevo_data"]);
        unset($states[$chat_id]["comprobante_nuevo"]);

        if (empty($states[$chat_id])) {
            unset($states[$chat_id]);
        }
    } else {
        unset($states[$chat_id]);
    }

    saveStates($file, $states);
}

function resetUserProcessState($file, &$states, $chat_id) {
    $usuarioVinculado = "";

    if (isset($states[$chat_id]) && is_array($states[$chat_id])) {
        $usuarioVinculado = trim((string)($states[$chat_id]["usuario_mdprime"] ?? ""));
    }

    if ($usuarioVinculado !== "") {
        $states[$chat_id] = ["usuario_mdprime" => $usuarioVinculado];
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


function getRailwayPdo() {
    static $pdo = null;
    global $db_host, $db_port, $db_name, $db_user, $db_pass;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $pdo = new PDO(
        "mysql:host=$db_host;port=$db_port;dbname=$db_name;charset=utf8mb4",
        $db_user,
        $db_pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_TIMEOUT => 8,
            PDO::ATTR_PERSISTENT => false
        ]
    );

    return $pdo;
}

function optimizarIndicesRailway() {
    try {
        $pdo = getRailwayPdo();

        $indices = [
            "CREATE INDEX idx_clientes_nombre ON clientes(nombre)",
            "CREATE INDEX idx_clientes_telegram ON clientes(telegram)",
            "CREATE INDEX idx_clientes_contacto ON clientes(contacto)",
            "CREATE INDEX idx_referidos_nombre ON referidos(nombre)",
            "CREATE INDEX idx_referidos_cliente_id ON referidos(cliente_id)",
            "CREATE INDEX idx_referidos_estado_caducidad ON referidos(estado, fecha_caducidad)"
        ];

        foreach ($indices as $sql) {
            try {
                $pdo->exec($sql);
            } catch (Throwable $e) {
                // Ignorar si ya existe
            }
        }

        return true;
    } catch (Throwable $e) {
        return false;
    }
}

function getAgendaJsonCache() {
    global $agenda_cache_file;

    $ttl = 300; // 5 minutos
    $url = "https://agenda-mdprime.zeppplay7.workers.dev/json";

    if (file_exists($agenda_cache_file) && (time() - filemtime($agenda_cache_file)) < $ttl) {
        $cached = file_get_contents($agenda_cache_file);
        if ($cached) {
            return $cached;
        }
    }

    $json = @file_get_contents($url);

    if ($json) {
        @file_put_contents($agenda_cache_file, $json);
        return $json;
    }

    if (file_exists($agenda_cache_file)) {
        return file_get_contents($agenda_cache_file);
    }

    return false;
}



function buscarReferidoFlexibleV24($pdo, $usuario) {
    $usuario = trim((string)$usuario);

    try {
        $stmt = $pdo->prepare("
            SELECT 
                r.*,
                c.id AS referente_id,
                c.nombre AS referente_nombre,
                c.telegram AS referente_telegram,
                c.contacto AS referente_contacto
            FROM referidos r
            INNER JOIN clientes c ON c.id = r.cliente_id
            WHERE LOWER(TRIM(r.nombre)) = LOWER(TRIM(?))
               OR REPLACE(LOWER(TRIM(r.nombre)), ' ', '') = REPLACE(LOWER(TRIM(?)), ' ', '')
            ORDER BY 
                CASE 
                    WHEN r.estado='Activo' AND (r.fecha_caducidad IS NULL OR r.fecha_caducidad >= CURDATE())
                    THEN 0 ELSE 1
                END,
                r.fecha_caducidad DESC,
                r.id DESC
            LIMIT 1
        ");
        $stmt->execute([$usuario, $usuario]);
        $ref = $stmt->fetch();

        return $ref ?: null;

    } catch (Throwable $e) {
        return null;
    }
}

function buscarReferidoDirectoSinJoinV25($pdo, $usuario) {
    $usuario = trim((string)$usuario);

    try {
        $stmt = $pdo->prepare("
            SELECT *
            FROM referidos
            WHERE LOWER(TRIM(nombre)) = LOWER(TRIM(?))
               OR REPLACE(LOWER(TRIM(nombre)), ' ', '') = REPLACE(LOWER(TRIM(?)), ' ', '')
            ORDER BY id DESC
            LIMIT 1
        ");
        $stmt->execute([$usuario, $usuario]);
        $ref = $stmt->fetch();

        if (!$ref) {
            return null;
        }

        $ref["referente_id"] = (int)($ref["cliente_id"] ?? 0);
        $ref["referente_nombre"] = "No disponible";
        $ref["referente_telegram"] = "";
        $ref["referente_contacto"] = "";

        if (!empty($ref["cliente_id"])) {
            $st = $pdo->prepare("SELECT id,nombre,telegram,contacto FROM clientes WHERE id=? LIMIT 1");
            $st->execute([(int)$ref["cliente_id"]]);
            $cli = $st->fetch();

            if ($cli) {
                $ref["referente_id"] = (int)$cli["id"];
                $ref["referente_nombre"] = $cli["nombre"] ?? "No disponible";
                $ref["referente_telegram"] = $cli["telegram"] ?? "";
                $ref["referente_contacto"] = $cli["contacto"] ?? "";
            }
        }

        return $ref;

    } catch (Throwable $e) {
        return null;
    }
}

function construirRespuestaReferido($referido) {
    $caducidad = $referido["fecha_caducidad"] ?? null;
    $estado_real = "Inactivo";

    if (($referido["estado"] ?? "") === "Activo" && (!$caducidad || strtotime($caducidad) >= strtotime(date("Y-m-d")))) {
        $estado_real = "Activo";
    }

    $dias = null;
    if ($caducidad) {
        $hoy = new DateTime(date("Y-m-d"));
        $cad = new DateTime($caducidad);
        $dias = (int)$hoy->diff($cad)->format("%r%a");
    }

    return [
        "ok" => true,
        "tipo" => "referido",
        "referido" => [
            "id" => (int)($referido["id"] ?? 0),
            "nombre" => $referido["nombre"] ?? ($referido["nombre_match_v24"] ?? "Sin nombre"),
            "estado" => $estado_real,
            "fecha_alta" => (!empty($referido["fecha_alta"])) ? date("d/m/Y", strtotime($referido["fecha_alta"])) : "Sin fecha",
            "caducidad" => ($caducidad) ? date("d/m/Y", strtotime($caducidad)) : "Sin fecha",
            "dias" => $dias,
            "nota" => $referido["nota"] ?? ""
        ],
        "referente" => [
            "id" => (int)($referido["referente_id"] ?? 0),
            "nombre" => $referido["referente_nombre"] ?? "",
            "telegram" => $referido["referente_telegram"] ?? "",
            "contacto" => $referido["referente_contacto"] ?? ""
        ]
    ];
}


function construirRespuestaClienteNormal($normal) {
    $caducidad = $normal["fecha_caducidad"] ?? null;
    $estado_real = "Inactivo";

    if (($normal["estado"] ?? "") === "Activo" && (!$caducidad || strtotime($caducidad) >= strtotime(date("Y-m-d")))) {
        $estado_real = "Activo";
    }

    $dias = null;
    if ($caducidad) {
        $hoy = new DateTime(date("Y-m-d"));
        $cad = new DateTime($caducidad);
        $dias = (int)$hoy->diff($cad)->format("%r%a");
    }

    return [
        "ok" => true,
        "tipo" => "normal",
        "cliente_normal" => [
            "id" => (int)($normal["id"] ?? 0),
            "nombre" => $normal["nombre"] ?? "Sin nombre",
            "estado" => $estado_real,
            "fecha_alta" => (!empty($normal["fecha_alta"])) ? date("d/m/Y", strtotime($normal["fecha_alta"])) : "Sin fecha",
            "caducidad" => ($caducidad) ? date("d/m/Y", strtotime($caducidad)) : "Sin fecha",
            "dias" => $dias,
            "telegram" => $normal["telegram"] ?? "",
            "contacto" => $normal["contacto"] ?? ($normal["telefono"] ?? ""),
            "nota" => $normal["nota"] ?? ""
        ]
    ];
}


function mdprimeNormalizarBusqueda($txt) {
    $txt = trim((string)$txt);
    $txt = str_replace(["\r", "\n", "\t"], " ", $txt);
    $txt = preg_replace('/\s+/', ' ', $txt);
    $txt = ltrim($txt, "@");

    $lower = mb_strtolower($txt, "UTF-8");

    $map = [
        "á"=>"a","à"=>"a","ä"=>"a","â"=>"a",
        "é"=>"e","è"=>"e","ë"=>"e","ê"=>"e",
        "í"=>"i","ì"=>"i","ï"=>"i","î"=>"i",
        "ó"=>"o","ò"=>"o","ö"=>"o","ô"=>"o",
        "ú"=>"u","ù"=>"u","ü"=>"u","û"=>"u",
        "ñ"=>"n"
    ];

    return strtr($lower, $map);
}

function buscarReferenteParaAlta($entrada) {
    $entrada = trim((string)$entrada);
    if ($entrada === "") {
        return ["ok" => false, "error" => "Falta el referente"];
    }

    try {
        $pdo = getRailwayPdo();
        $buscado = mdprimeNormalizarBusqueda($entrada);

        $buscarEnFilas = function($rows) use ($buscado) {
            $exactos = [];
            $parciales = [];

            foreach ($rows as $row) {
                $campos = [
                    mdprimeNormalizarBusqueda($row["nombre"] ?? ""),
                    mdprimeNormalizarBusqueda($row["telegram"] ?? ""),
                    mdprimeNormalizarBusqueda($row["contacto"] ?? ""),
                    mdprimeNormalizarBusqueda($row["telefono"] ?? "")
                ];

                foreach ($campos as $campo) {
                    if ($campo === "") continue;

                    if ($campo === $buscado) {
                        $exactos[(int)$row["id"]] = $row;
                        break;
                    }

                    if (mb_strlen($buscado, "UTF-8") >= 3 && mb_strpos($campo, $buscado, 0, "UTF-8") !== false) {
                        $parciales[(int)$row["id"]] = $row;
                    }
                }
            }

            if (count($exactos) === 1) return ["ok" => true, "row" => array_values($exactos)[0]];
            if (count($exactos) > 1) return ["ok" => false, "error" => "Hay varios usuarios con ese dato. Escribe el nombre exacto completo."];
            if (count($parciales) === 1) return ["ok" => true, "row" => array_values($parciales)[0]];
            if (count($parciales) > 1) return ["ok" => false, "error" => "Hay varios usuarios parecidos. Escribe el nombre exacto completo."];
            return ["ok" => false, "error" => "No encontrado"];
        };

        // 1) Buscar primero entre los referentes ya existentes.
        $rows = $pdo->query("SELECT id, nombre, telegram, contacto, telefono FROM clientes ORDER BY id ASC")->fetchAll();
        $enReferentes = $buscarEnFilas($rows);

        if (!empty($enReferentes["ok"])) {
            $ref = $enReferentes["row"];
            $nivelInfo = obtenerNivelReferentePorId((int)$ref["id"]);
            $nivel = $nivelInfo["nivel"] ?? "";
            if ($nivel === "") $nivel = "cobre";

            return [
                "ok" => true,
                "origen" => "clientes",
                "referente" => [
                    "id" => (int)$ref["id"],
                    "nombre" => $ref["nombre"] ?? "Sin nombre",
                    "telegram" => $ref["telegram"] ?? "",
                    "contacto" => $ref["contacto"] ?? ""
                ],
                "nivel" => $nivel,
                "activos" => (int)($nivelInfo["activos"] ?? 0)
            ];
        }

        if (($enReferentes["error"] ?? "") !== "No encontrado") {
            return $enReferentes;
        }

        // 2) Comprobar si el dato pertenece a un cliente normal.
        // Un cliente normal NO puede utilizarse como referente hasta que administración
        // lo convierta previamente en Referente VIP dentro del panel.
        $rowsNormales = $pdo->query("SELECT id, nombre, telegram, contacto, telefono FROM clientes_normales ORDER BY id ASC")->fetchAll();
        $enNormales = $buscarEnFilas($rowsNormales);

        if (!empty($enNormales["ok"])) {
            $normal = $enNormales["row"];
            return [
                "ok" => false,
                "tipo_error" => "cliente_normal_no_referente",
                "nombre_encontrado" => $normal["nombre"] ?? $entrada,
                "cliente_normal" => [
                    "id" => (int)($normal["id"] ?? 0),
                    "nombre" => $normal["nombre"] ?? $entrada,
                    "telegram" => $normal["telegram"] ?? "",
                    "contacto" => $normal["contacto"] ?? "",
                    "telefono" => $normal["telefono"] ?? ""
                ],
                "error" => "Ese usuario existe en el panel, pero actualmente es un cliente normal y no está registrado como Referente VIP."
            ];
        }

        return ["ok" => false, "error" => ($enNormales["error"] ?? "Referente no encontrado")];
    } catch (Throwable $e) {
        return ["ok" => false, "error" => "Error al consultar referentes: ".$e->getMessage()];
    }
}

function buscarClienteNormalExactoParaReferir($usuario) {
    $usuario = trim((string)$usuario);
    if ($usuario === "") return null;

    try {
        $pdo = getRailwayPdo();
        $stmt = $pdo->prepare("
            SELECT *
            FROM clientes_normales
            WHERE LOWER(TRIM(nombre)) = LOWER(TRIM(?))
               OR REPLACE(LOWER(TRIM(nombre)), ' ', '') = REPLACE(LOWER(TRIM(?)), ' ', '')
            ORDER BY id DESC
            LIMIT 1
        ");
        $stmt->execute([$usuario, $usuario]);
        $normal = $stmt->fetch();

        return $normal ? construirRespuestaClienteNormal($normal) : null;
    } catch (Throwable $e) {
        return null;
    }
}

function consultarClienteApi($usuario) {
    global $db_host, $db_port, $db_name, $db_user, $db_pass;

    $usuario = trim((string)$usuario);
    $usuario = str_replace(["\r", "\n", "\t"], " ", $usuario);
    $usuario = preg_replace('/\s+/', ' ', $usuario);

    if ($usuario === "") {
        return ["ok" => false, "error" => "Falta usuario"];
    }

    $usuario_sin_arroba = ltrim($usuario, "@");
    $usuario_like = "%".$usuario."%";

    try {
        $pdo = getRailwayPdo();

        /* V49: buscar primero directamente en REFERIDOS, sin exigir que esté activo
           ni depender del INNER JOIN. Así también reconoce usuarios caducados
           mientras sigan existiendo en el panel. */
        try {
            $stmtRefPrimero = $pdo->prepare("
                SELECT *
                FROM referidos
                WHERE LOWER(TRIM(nombre)) = LOWER(TRIM(?))
                   OR REPLACE(LOWER(TRIM(nombre)), ' ', '') = REPLACE(LOWER(TRIM(?)), ' ', '')
                ORDER BY id DESC
                LIMIT 1
            ");
            $stmtRefPrimero->execute([$usuario, $usuario]);
            $referidoPrimero = $stmtRefPrimero->fetch();

            if ($referidoPrimero) {
                $referidoPrimero["referente_id"] = (int)($referidoPrimero["cliente_id"] ?? 0);
                $referidoPrimero["referente_nombre"] = "No disponible";
                $referidoPrimero["referente_telegram"] = "";
                $referidoPrimero["referente_contacto"] = "";

                if (!empty($referidoPrimero["cliente_id"])) {
                    $stmtRefCliente = $pdo->prepare("SELECT id, nombre, telegram, contacto FROM clientes WHERE id = ? LIMIT 1");
                    $stmtRefCliente->execute([(int)$referidoPrimero["cliente_id"]]);
                    $referentePrimero = $stmtRefCliente->fetch();

                    if ($referentePrimero) {
                        $referidoPrimero["referente_id"] = (int)$referentePrimero["id"];
                        $referidoPrimero["referente_nombre"] = $referentePrimero["nombre"] ?? "No disponible";
                        $referidoPrimero["referente_telegram"] = $referentePrimero["telegram"] ?? "";
                        $referidoPrimero["referente_contacto"] = $referentePrimero["contacto"] ?? "";
                    }
                }

                return construirRespuestaReferido($referidoPrimero);
            }
        } catch (Throwable $e) {
            // Continuar con las búsquedas anteriores si esta comprobación falla.
        }

        // Primero búsqueda exacta rápida usando índices
        $stmt = $pdo->prepare("
            SELECT *
            FROM clientes
            WHERE nombre = ?
               OR telegram = ?
               OR telegram = ?
               OR contacto = ?
               OR telefono = ?
            LIMIT 1
        ");
        $stmt->execute([$usuario, $usuario, $usuario_sin_arroba, $usuario, $usuario]);
        $cliente = $stmt->fetch();

        // Si no hay coincidencia exacta, usar búsqueda flexible
        if (!$cliente) {
            $stmt = $pdo->prepare("
                SELECT *
                FROM clientes
                WHERE LOWER(TRIM(nombre)) = LOWER(TRIM(?))
                   OR LOWER(TRIM(telegram)) = LOWER(TRIM(?))
                   OR LOWER(TRIM(telegram)) = LOWER(TRIM(?))
                   OR LOWER(TRIM(contacto)) = LOWER(TRIM(?))
                   OR LOWER(TRIM(telefono)) = LOWER(TRIM(?))
                   OR REPLACE(LOWER(TRIM(nombre)), ' ', '') = REPLACE(LOWER(TRIM(?)), ' ', '')
                   OR REPLACE(LOWER(TRIM(telegram)), ' ', '') = REPLACE(LOWER(TRIM(?)), ' ', '')
                LIMIT 1
            ");
            $stmt->execute([$usuario, $usuario, $usuario_sin_arroba, $usuario, $usuario, $usuario, $usuario_sin_arroba]);
            $cliente = $stmt->fetch();
        }

        if ($cliente) {
            $cliente_id = (int)$cliente["id"];

            $stmt = $pdo->prepare("
                SELECT *
                FROM referidos
                WHERE cliente_id = ?
                ORDER BY 
                    CASE 
                        WHEN estado='Activo' AND (fecha_caducidad IS NULL OR fecha_caducidad >= CURDATE()) 
                        THEN 0 ELSE 1 
                    END,
                    fecha_caducidad ASC,
                    nombre ASC
            ");
            $stmt->execute([$cliente_id]);
            $referidos_db = $stmt->fetchAll();

            $referidos_lista = [];
            $total = count($referidos_db);
            $activos = 0;
            $inactivos = 0;
            $proxima_caducidad = null;

            foreach ($referidos_db as $ref) {
                $caducidad = $ref["fecha_caducidad"] ?? null;
                $estado_real = "Inactivo";

                if (($ref["estado"] ?? "") === "Activo" && (!$caducidad  || strtotime($caducidad) >= strtotime(date("Y-m-d")))) {
                    $estado_real = "Activo";
                    $activos++;

                    if ($caducidad  && (!$proxima_caducidad || $caducidad < $proxima_caducidad)) {
                        $proxima_caducidad = $caducidad;
                    }
                } else {
                    $inactivos++;
                }

                $dias = null;
                if ($caducidad ) {
                    $hoy = new DateTime(date("Y-m-d"));
                    $cad = new DateTime($caducidad);
                    $dias = (int)$hoy->diff($cad)->format("%r%a");
                }

                $referidos_lista[] = [
                    "id" => (int)$ref["id"],
                    "nombre" => $ref["nombre"],
                    "estado" => $estado_real,
                    "fecha_alta" => (!empty($ref["fecha_alta"]) ) ? date("d/m/Y", strtotime($ref["fecha_alta"])) : "Sin fecha",
                    "caducidad" => ($caducidad ) ? date("d/m/Y", strtotime($caducidad)) : "Sin fecha",
                    "dias" => $dias,
                    "nota" => $ref["nota"] ?? ""
                ];
            }

            $niveles = $pdo->query("SELECT * FROM configuracion_niveles ORDER BY min_activos ASC")->fetchAll();

            $nivel_actual = [
                "nivel" => "SIN NIVEL",
                "min_activos" => 0,
                "trimestral" => 0,
                "semestral" => 0,
                "anual" => 0
            ];

            $siguiente = null;

            foreach ($niveles as $nivel) {
                if ($activos >= (int)$nivel["min_activos"]) {
                    $nivel_actual = $nivel;
                } elseif (!$siguiente) {
                    $siguiente = $nivel;
                }
            }

            $dias_proxima = null;
            if ($proxima_caducidad) {
                $hoy = new DateTime(date("Y-m-d"));
                $cad = new DateTime($proxima_caducidad);
                $dias_proxima = (int)$hoy->diff($cad)->format("%r%a");
            }

            return [
                "ok" => true,
                "tipo" => "referente",
                "cliente" => [
                    "id" => $cliente_id,
                    "nombre" => $cliente["nombre"],
                    "contacto" => $cliente["contacto"] ?? "",
                    "telegram" => $cliente["telegram"] ?? ""
                ],
                "resumen" => [
                    "total_referidos" => $total,
                    "activos" => $activos,
                    "inactivos" => $inactivos,
                    "proxima_caducidad" => $proxima_caducidad ? date("d/m/Y", strtotime($proxima_caducidad)) : "Sin fecha",
                    "dias_proxima_caducidad" => $dias_proxima
                ],
                "nivel" => [
                    "actual" => $nivel_actual["nivel"],
                    "precio_3_meses" => (float)$nivel_actual["trimestral"],
                    "precio_6_meses" => (float)$nivel_actual["semestral"],
                    "precio_12_meses" => (float)$nivel_actual["anual"]
                ],
                "siguiente_nivel" => $siguiente ? [
                    "nivel" => $siguiente["nivel"],
                    "min_activos" => (int)$siguiente["min_activos"],
                    "faltan" => max(0, (int)$siguiente["min_activos"] - $activos)
                ] : null,
                "referidos" => $referidos_lista
            ];
        }

        // Búsqueda exacta rápida de referido
        $stmt = $pdo->prepare("
            SELECT 
                r.*,
                c.id AS referente_id,
                c.nombre AS referente_nombre,
                c.telegram AS referente_telegram,
                c.contacto AS referente_contacto
            FROM referidos r
            INNER JOIN clientes c ON c.id = r.cliente_id
            WHERE r.nombre = ?
            ORDER BY 
                CASE 
                    WHEN r.estado='Activo' AND (r.fecha_caducidad IS NULL OR r.fecha_caducidad >= CURDATE())
                    THEN 0 ELSE 1
                END,
                r.fecha_caducidad DESC,
                r.id DESC
            LIMIT 1
        ");
        $stmt->execute([$usuario]);
        $referido = $stmt->fetch();

        // Si no hay coincidencia exacta, usar búsqueda flexible
        if (!$referido) {
            $stmt = $pdo->prepare("
                SELECT 
                    r.*,
                    c.id AS referente_id,
                    c.nombre AS referente_nombre,
                    c.telegram AS referente_telegram,
                    c.contacto AS referente_contacto
                FROM referidos r
                INNER JOIN clientes c ON c.id = r.cliente_id
                WHERE LOWER(TRIM(r.nombre)) = LOWER(TRIM(?))
                   OR REPLACE(LOWER(TRIM(r.nombre)), ' ', '') = REPLACE(LOWER(TRIM(?)), ' ', '')
                ORDER BY 
                    CASE 
                        WHEN r.estado='Activo' AND (r.fecha_caducidad IS NULL OR r.fecha_caducidad >= CURDATE())
                        THEN 0 ELSE 1
                    END,
                    r.fecha_caducidad DESC,
                    r.id DESC
                LIMIT 1
            ");
            $stmt->execute([$usuario, $usuario]);
            $referido = $stmt->fetch();
        }

        if ($referido) {
            // Seguridad V30: si hay más de un referido con el mismo nombre exacto normalizado, no se autoguarda.
            $chkDup = $pdo->prepare("
                SELECT COUNT(*)
                FROM referidos
                WHERE LOWER(TRIM(nombre)) = LOWER(TRIM(?))
                   OR REPLACE(LOWER(TRIM(nombre)), ' ', '') = REPLACE(LOWER(TRIM(?)), ' ', '')
            ");
            $chkDup->execute([$usuario, $usuario]);
            $numDup = (int)$chkDup->fetchColumn();

            if ($numDup > 1) {
                return [
                    "ok" => false,
                    "error" => "Hay varios usuarios parecidos con ese nombre. Por seguridad, escribe el usuario exacto completo tal como aparece en el panel.",
                    "buscado" => $usuario
                ];
            }

            return construirRespuestaReferido($referido);
        }

        // Fallback V24: búsqueda robusta por todas las columnas de texto de referidos.
        // Esto corrige referidos creados por el panel que puedan quedar guardados con otro formato/campo.
        $referido_flexible = buscarReferidoFlexibleV24($pdo, $usuario);

        if ($referido_flexible) {
            return construirRespuestaReferido($referido_flexible);
        }

        // Fallback V25: búsqueda directa en referidos sin INNER JOIN.
        // Sirve para detectar registros creados aunque la relación cliente_id tenga algún problema.
        $referido_directo = buscarReferidoDirectoSinJoinV25($pdo, $usuario);

        if ($referido_directo) {
            return construirRespuestaReferido($referido_directo);
        }

        // V36: buscar también en clientes normales antes de decir no encontrado.
        try {
            $stmt = $pdo->prepare("
                SELECT *
                FROM clientes_normales
                WHERE LOWER(TRIM(nombre)) = LOWER(TRIM(?))
                   OR REPLACE(LOWER(TRIM(nombre)), ' ', '') = REPLACE(LOWER(TRIM(?)), ' ', '')
                   OR LOWER(TRIM(telegram)) = LOWER(TRIM(?))
                   OR LOWER(TRIM(telegram)) = LOWER(TRIM(?))
                   OR LOWER(TRIM(contacto)) = LOWER(TRIM(?))
                   OR LOWER(TRIM(telefono)) = LOWER(TRIM(?))
                ORDER BY id DESC
                LIMIT 1
            ");
            $stmt->execute([$usuario, $usuario, $usuario, $usuario_sin_arroba, $usuario, $usuario]);
            $normal = $stmt->fetch();

            if ($normal) {
                return construirRespuestaClienteNormal($normal);
            }
        } catch (Throwable $e) {
            // Si la tabla no existe todavía, el bot no se rompe.
        }

        // V46: búsqueda flexible extra para evitar fallos por espacios, mayúsculas, @ o pequeños cambios.
        try {
            $qNorm = mdprimeNormalizarBusqueda($usuario);
            $qLike = "%".$qNorm."%";

            // Referidos VIP
            $stmt = $pdo->prepare("
                SELECT 
                    r.*,
                    c.id AS referente_id,
                    c.nombre AS referente_nombre,
                    c.telegram AS referente_telegram,
                    c.contacto AS referente_contacto
                FROM referidos r
                INNER JOIN clientes c ON c.id = r.cliente_id
                WHERE REPLACE(LOWER(TRIM(r.nombre)), ' ', '') LIKE REPLACE(?, ' ', '')
                   OR LOWER(TRIM(r.nombre)) LIKE ?
                ORDER BY 
                    CASE 
                        WHEN r.estado='Activo' AND (r.fecha_caducidad IS NULL OR r.fecha_caducidad >= CURDATE())
                        THEN 0 ELSE 1
                    END,
                    r.fecha_caducidad DESC,
                    r.id DESC
                LIMIT 1
            ");
            $stmt->execute([$qLike, $qLike]);
            $refExtra = $stmt->fetch();

            if ($refExtra) {
                return construirRespuestaReferido($refExtra);
            }

            // Clientes normales / no referidos
            $stmt = $pdo->prepare("
                SELECT *
                FROM clientes_normales
                WHERE REPLACE(LOWER(TRIM(nombre)), ' ', '') LIKE REPLACE(?, ' ', '')
                   OR LOWER(TRIM(nombre)) LIKE ?
                   OR REPLACE(LOWER(TRIM(telegram)), ' ', '') LIKE REPLACE(?, ' ', '')
                   OR LOWER(TRIM(telegram)) LIKE ?
                   OR LOWER(TRIM(contacto)) LIKE ?
                   OR LOWER(TRIM(telefono)) LIKE ?
                ORDER BY id DESC
                LIMIT 1
            ");
            $stmt->execute([$qLike, $qLike, $qLike, $qLike, $qLike, $qLike]);
            $normalExtra = $stmt->fetch();

            if ($normalExtra) {
                return construirRespuestaClienteNormal($normalExtra);
            }

            // Referentes
            $stmt = $pdo->prepare("
                SELECT *
                FROM clientes
                WHERE REPLACE(LOWER(TRIM(nombre)), ' ', '') LIKE REPLACE(?, ' ', '')
                   OR LOWER(TRIM(nombre)) LIKE ?
                   OR REPLACE(LOWER(TRIM(telegram)), ' ', '') LIKE REPLACE(?, ' ', '')
                   OR LOWER(TRIM(telegram)) LIKE ?
                   OR LOWER(TRIM(contacto)) LIKE ?
                   OR LOWER(TRIM(telefono)) LIKE ?
                ORDER BY id DESC
                LIMIT 1
            ");
            $stmt->execute([$qLike, $qLike, $qLike, $qLike, $qLike, $qLike]);
            $clienteExtra = $stmt->fetch();

            if ($clienteExtra) {
                // Volver a consultar con el nombre exacto encontrado para reutilizar toda la lógica del bot.
                return consultarClienteApi($clienteExtra["nombre"]);
            }

        } catch (Throwable $e) {
            // Si esta búsqueda extra falla, seguimos con el mensaje normal.
        }

        return [
            "ok" => false,
            "error" => "Cliente o referido no encontrado",
            "buscado" => $usuario
        ];

    } catch (Throwable $e) {
        return [
            "ok" => false,
            "error" => "Error MySQL directo",
            "detalle" => $e->getMessage()
        ];
    }
}


function construirRespuestaReferenteSesionV64($pdo, $cliente) {
    $cliente_id = (int)($cliente["id"] ?? 0);

    $stmt = $pdo->prepare("\n        SELECT *\n        FROM referidos\n        WHERE cliente_id = ?\n        ORDER BY\n            CASE\n                WHEN estado='Activo' AND (fecha_caducidad IS NULL OR fecha_caducidad >= CURDATE())\n                THEN 0 ELSE 1\n            END,\n            fecha_caducidad ASC,\n            nombre ASC\n    ");
    $stmt->execute([$cliente_id]);
    $referidos_db = $stmt->fetchAll();

    $referidos_lista = [];
    $activos = 0;
    $inactivos = 0;
    $proxima_caducidad = null;

    foreach ($referidos_db as $ref) {
        $caducidad = $ref["fecha_caducidad"] ?? null;
        $estado_real = "Inactivo";

        if (($ref["estado"] ?? "") === "Activo" && (!$caducidad || strtotime($caducidad) >= strtotime(date("Y-m-d")))) {
            $estado_real = "Activo";
            $activos++;
            if ($caducidad && (!$proxima_caducidad || $caducidad < $proxima_caducidad)) {
                $proxima_caducidad = $caducidad;
            }
        } else {
            $inactivos++;
        }

        $dias = null;
        if ($caducidad) {
            $hoy = new DateTime(date("Y-m-d"));
            $cad = new DateTime($caducidad);
            $dias = (int)$hoy->diff($cad)->format("%r%a");
        }

        $referidos_lista[] = [
            "id" => (int)($ref["id"] ?? 0),
            "nombre" => $ref["nombre"] ?? "Sin nombre",
            "estado" => $estado_real,
            "fecha_alta" => !empty($ref["fecha_alta"]) ? date("d/m/Y", strtotime($ref["fecha_alta"])) : "Sin fecha",
            "caducidad" => $caducidad ? date("d/m/Y", strtotime($caducidad)) : "Sin fecha",
            "dias" => $dias,
            "nota" => $ref["nota"] ?? ""
        ];
    }

    $niveles = $pdo->query("SELECT * FROM configuracion_niveles ORDER BY min_activos ASC")->fetchAll();
    $nivel_actual = [
        "nivel" => "SIN NIVEL",
        "min_activos" => 0,
        "trimestral" => 0,
        "semestral" => 0,
        "anual" => 0
    ];
    $siguiente = null;

    foreach ($niveles as $nivel) {
        if ($activos >= (int)$nivel["min_activos"]) {
            $nivel_actual = $nivel;
        } elseif (!$siguiente) {
            $siguiente = $nivel;
        }
    }

    $dias_proxima = null;
    if ($proxima_caducidad) {
        $hoy = new DateTime(date("Y-m-d"));
        $cad = new DateTime($proxima_caducidad);
        $dias_proxima = (int)$hoy->diff($cad)->format("%r%a");
    }

    return [
        "ok" => true,
        "tipo" => "referente",
        "cliente" => [
            "id" => $cliente_id,
            "nombre" => $cliente["nombre"] ?? "Sin nombre",
            "contacto" => $cliente["contacto"] ?? "",
            "telegram" => $cliente["telegram"] ?? ""
        ],
        "resumen" => [
            "total_referidos" => count($referidos_db),
            "activos" => $activos,
            "inactivos" => $inactivos,
            "proxima_caducidad" => $proxima_caducidad ? date("d/m/Y", strtotime($proxima_caducidad)) : "Sin fecha",
            "dias_proxima_caducidad" => $dias_proxima
        ],
        "nivel" => [
            "actual" => $nivel_actual["nivel"] ?? "SIN NIVEL",
            "precio_3_meses" => (float)($nivel_actual["trimestral"] ?? 0),
            "precio_6_meses" => (float)($nivel_actual["semestral"] ?? 0),
            "precio_12_meses" => (float)($nivel_actual["anual"] ?? 0)
        ],
        "siguiente_nivel" => $siguiente ? [
            "nivel" => $siguiente["nivel"] ?? "",
            "min_activos" => (int)($siguiente["min_activos"] ?? 0),
            "faltan" => max(0, (int)($siguiente["min_activos"] ?? 0) - $activos)
        ] : null,
        "referidos" => $referidos_lista
    ];
}

function consultarClienteParaSesionV64($usuario) {
    $usuario = trim((string)$usuario);
    if ($usuario === "") return ["ok" => false, "error" => "Falta usuario"];

    try {
        $pdo = getRailwayPdo();

        // 1. REFERENTE: siempre tiene prioridad si existe en la tabla clientes.
        $stmt = $pdo->prepare("\n            SELECT * FROM clientes\n            WHERE LOWER(TRIM(nombre)) = LOWER(TRIM(?))\n               OR REPLACE(LOWER(TRIM(nombre)), ' ', '') = REPLACE(LOWER(TRIM(?)), ' ', '')\n            ORDER BY id DESC\n            LIMIT 1\n        ");
        $stmt->execute([$usuario, $usuario]);
        $cliente = $stmt->fetch();
        if ($cliente) return construirRespuestaReferenteSesionV64($pdo, $cliente);

        // 2. REFERIDO: solo si no es referente.
        $stmt = $pdo->prepare("\n            SELECT * FROM referidos\n            WHERE LOWER(TRIM(nombre)) = LOWER(TRIM(?))\n               OR REPLACE(LOWER(TRIM(nombre)), ' ', '') = REPLACE(LOWER(TRIM(?)), ' ', '')\n            ORDER BY id DESC\n            LIMIT 1\n        ");
        $stmt->execute([$usuario, $usuario]);
        $referido = $stmt->fetch();
        if ($referido) {
            $referido["referente_id"] = (int)($referido["cliente_id"] ?? 0);
            $referido["referente_nombre"] = "No disponible";
            $referido["referente_telegram"] = "";
            $referido["referente_contacto"] = "";

            if (!empty($referido["cliente_id"])) {
                $st = $pdo->prepare("SELECT id,nombre,telegram,contacto FROM clientes WHERE id=? LIMIT 1");
                $st->execute([(int)$referido["cliente_id"]]);
                $ref = $st->fetch();
                if ($ref) {
                    $referido["referente_id"] = (int)$ref["id"];
                    $referido["referente_nombre"] = $ref["nombre"] ?? "No disponible";
                    $referido["referente_telegram"] = $ref["telegram"] ?? "";
                    $referido["referente_contacto"] = $ref["contacto"] ?? "";
                }
            }
            return construirRespuestaReferido($referido);
        }

        // 3. CLIENTE NORMAL: únicamente si no aparece en las tablas anteriores.
        $stmt = $pdo->prepare("\n            SELECT * FROM clientes_normales\n            WHERE LOWER(TRIM(nombre)) = LOWER(TRIM(?))\n               OR REPLACE(LOWER(TRIM(nombre)), ' ', '') = REPLACE(LOWER(TRIM(?)), ' ', '')\n            ORDER BY id DESC\n            LIMIT 1\n        ");
        $stmt->execute([$usuario, $usuario]);
        $normal = $stmt->fetch();
        if ($normal) return construirRespuestaClienteNormal($normal);

        return ["ok" => false, "error" => "Cliente o referido no encontrado", "buscado" => $usuario];
    } catch (Throwable $e) {
        return ["ok" => false, "error" => "Error MySQL directo", "detalle" => $e->getMessage()];
    }
}

// Alias mantenido para no romper llamadas existentes.
function consultarClienteParaSesionV63($usuario) {
    return consultarClienteParaSesionV64($usuario);
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


function textoPreciosNivelReferidos($nivel_key) {
    $nivel_key = strtolower((string)$nivel_key);

    if ($nivel_key === "") {
        return "";
    }

    return "🏆 Paquete Referidos:
".renovarNivelTxt($nivel_key)."

💶 Precios de tu paquete:
3 meses → ".renovarPrecioReferidos($nivel_key, 3)."€
6 meses → ".renovarPrecioReferidos($nivel_key, 6)."€
12 meses → ".renovarPrecioReferidos($nivel_key, 12)."€";
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

        $paquete_txt = "";
        $referente_id = $referente["id"] ?? 0;

        if ($referente_id) {
            $info_nivel = obtenerNivelReferentePorId($referente_id);
            $nivel_key = $info_nivel["nivel"] ?? "";
            $paquete_txt = textoPreciosNivelReferidos($nivel_key);
        }

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
".fmtDias($dias).($paquete_txt !== "" ? "

━━━━━━━━━━━━━━━━━━

".$paquete_txt : "")."

━━━━━━━━━━━━━━━━━━

🔄 ¿Deseas renovar tu cuenta?
Pulsa el comando:
/renovar

━━━━━━━━━━━━━━━━━━

⭐ Gracias por confiar en MDPRIME.";
    }

    if ($tipo === "normal" || isset($data["cliente_normal"])) {
        $normal = $data["cliente_normal"] ?? [];
        $estado = $normal["estado"] ?? "Sin estado";
        $caducidad = $normal["caducidad"] ?? "Sin fecha";
        $alta = $normal["fecha_alta"] ?? "Sin fecha";
        $dias = $normal["dias"] ?? null;

        return "👤 MI CUENTA MDPRIME

━━━━━━━━━━━━━━━━━━

🙋 Usuario:
".($normal["nombre"] ?? "Sin nombre")."

💳 Tipo de cuenta:
Cliente normal

".estadoIcono($estado)." Estado:
".$estado."

📅 Alta:
".$alta."

📅 Caducidad:
".$caducidad."

⏳ Tiempo restante:
".fmtDias($dias)."

━━━━━━━━━━━━━━━━━━

💶 Precios normales:
3 meses → ".renovarPrecioNormal(3)."€
6 meses → ".renovarPrecioNormal(6)."€
12 meses → ".renovarPrecioNormal(12)."€

━━━━━━━━━━━━━━━━━━

🔄 ¿Deseas renovar tu cuenta?
Pulsa el comando:
/renovar

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

    $msg .= "

━━━━━━━━━━━━━━━━━━

🔄 ¿Deseas renovar una cuenta?
Pulsa el comando:
/renovar";

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
    $data = consultarClienteApi($usuario);

    if (empty($data["ok"])) {
        $detalle_error = $data["error"] ?? "Sin detalle";
        $buscado_api = $data["buscado"] ?? $usuario;

        sendMessage($chat_id, "❌ No he encontrado ese usuario.

Buscado:
".$buscado_api."

Detalle:
".$detalle_error."

Prueba directo así:
/caducidad Brandon10

O cambia el usuario con:
/cambiarusuario");
        return;
    }

    if ($tipo === "/caducidad") {
        sendLongMessage($chat_id, formatCaducidad($data));
        return;
    }

    if (($data["tipo"] ?? "") === "referente") {
        if ($tipo === "/misreferidos") {
            [$txt,$kb] = listaReferidosV60($data, 0, false);
            sendInlineMessage($chat_id, $txt, $kb);
            return;
        }
        sendLongMessage($chat_id, formatMiCuenta($data));
        sendInlineMessage($chat_id, "👑 Accede a tu panel de referente:", tecladoPanelReferenteV60());
        return;
    }

    if ($tipo === "/misreferidos") {
        sendLongMessage($chat_id, formatMisReferidos($data));
        return;
    }

    sendLongMessage($chat_id, formatMiCuenta($data));
}


function obtenerNivelReferentePorId($referente_id) {
    try {
        $pdo = getRailwayPdo();

        $stmt = $pdo->prepare("
            SELECT COUNT(*) 
            FROM referidos 
            WHERE cliente_id = ?
              AND estado = 'Activo'
              AND (fecha_caducidad IS NULL OR fecha_caducidad >= CURDATE())
        ");
        $stmt->execute([(int)$referente_id]);
        $activos = (int)$stmt->fetchColumn();

        $niveles = $pdo->query("SELECT * FROM configuracion_niveles ORDER BY min_activos ASC")->fetchAll();

        $nivel_actual = "";

        foreach ($niveles as $nivel) {
            if ($activos >= (int)$nivel["min_activos"]) {
                $nivel_actual = renovarNivelKeyDesdeTexto($nivel["nivel"] ?? "");
            }
        }

        return [
            "nivel" => $nivel_actual,
            "activos" => $activos
        ];

    } catch (Throwable $e) {
        return [
            "nivel" => "",
            "activos" => 0
        ];
    }
}

function renovarPrecioNormal($meses) {
    $precios = [
        3 => 35,
        6 => 55,
        12 => 80
    ];

    return $precios[(int)$meses] ?? 0;
}

function renovarPrecioReferidos($nivel, $meses) {
    $nivel = strtolower((string)$nivel);

    $precios = [
        "cobre" => [3 => 30, 6 => 45, 12 => 65],
        "plata" => [3 => 27, 6 => 40, 12 => 58],
        "oro" => [3 => 25, 6 => 37, 12 => 52],
        "platinum" => [3 => 22, 6 => 33, 12 => 45]
    ];

    return $precios[$nivel][(int)$meses] ?? 0;
}

function renovarNivelTxt($nivel) {
    $nivel = strtolower((string)$nivel);

    if ($nivel === "cobre") return "🥉 Cobre";
    if ($nivel === "plata") return "🥈 Plata";
    if ($nivel === "oro") return "🥇 Oro";
    if ($nivel === "platinum") return "💠 Platinum";

    return "Sin nivel";
}

function renovarDuracionKeyboard($data = []) {
    $esVip = !empty($data["es_vip"]);
    $nivel = $data["nivel_actual"] ?? "";

    if ($esVip && $nivel !== "") {
        $p3 = renovarPrecioReferidos($nivel, 3);
        $p6 = renovarPrecioReferidos($nivel, 6);
        $p12 = renovarPrecioReferidos($nivel, 12);
    } else {
        $p3 = renovarPrecioNormal(3);
        $p6 = renovarPrecioNormal(6);
        $p12 = renovarPrecioNormal(12);
    }

    return [
        "inline_keyboard" => [
            [
                ["text" => "📦 3 meses · ".$p3."€", "callback_data" => "ren_dur_3"]
            ],
            [
                ["text" => "📦 6 meses · ".$p6."€", "callback_data" => "ren_dur_6"]
            ],
            [
                ["text" => "📦 12 meses · ".$p12."€", "callback_data" => "ren_dur_12"]
            ],
            [
                ["text" => "❌ Cancelar", "callback_data" => "ren_cancelar"]
            ]
        ]
    ];
}

function renovarOrdenNivel($nivel) {
    $nivel = strtolower((string)$nivel);

    if ($nivel === "cobre") return 1;
    if ($nivel === "plata") return 2;
    if ($nivel === "oro") return 3;
    if ($nivel === "platinum") return 4;

    return 0;
}

function renovarNivelKeyDesdeTexto($nivel) {
    $nivel = strtoupper(trim((string)$nivel));

    if ($nivel === "COBRE") return "cobre";
    if ($nivel === "PLATA") return "plata";
    if ($nivel === "ORO") return "oro";
    if ($nivel === "PLATINUM") return "platinum";

    return "";
}

function renovarNivelesPermitidos($nivel_actual) {
    $actual = renovarOrdenNivel($nivel_actual);

    $niveles = [
        "cobre" => "🥉 Cobre",
        "plata" => "🥈 Plata",
        "oro" => "🥇 Oro",
        "platinum" => "💠 Platinum"
    ];

    $permitidos = [];

    foreach ($niveles as $key => $txt) {
        if (renovarOrdenNivel($key) <= $actual) {
            $permitidos[$key] = $txt;
        }
    }

    return $permitidos;
}

function renovarNivelKeyboard($nivel_actual = "") {
    $permitidos = renovarNivelesPermitidos($nivel_actual);

    if (empty($permitidos)) {
        $permitidos = ["cobre" => "🥉 Cobre"];
    }

    $rows = [];
    $row = [];

    foreach ($permitidos as $key => $txt) {
        $row[] = ["text" => $txt, "callback_data" => "ren_lvl_".$key];

        if (count($row) === 2) {
            $rows[] = $row;
            $row = [];
        }
    }

    if (!empty($row)) {
        $rows[] = $row;
    }

    $rows[] = [
        ["text" => "❌ Cancelar", "callback_data" => "ren_cancelar"]
    ];

    return [
        "inline_keyboard" => $rows
    ];
}

function renovarConfirmarKeyboard() {
    return [
        "inline_keyboard" => [
            [
                ["text" => "✅ Solicitar renovación", "callback_data" => "ren_confirmar"]
            ],
            [
                ["text" => "❌ Cancelar", "callback_data" => "ren_cancelar"]
            ]
        ]
    ];
}

function renovarEstado($states, $chat_id) {
    if (!isset($states[$chat_id]) || !is_array($states[$chat_id])) {
        return [];
    }

    return $states[$chat_id]["renovar_data"] ?? [];
}

function guardarRenovarEstado($file, &$states, $chat_id, $data) {
    if (!isset($states[$chat_id]) || !is_array($states[$chat_id])) {
        $states[$chat_id] = [];
    }

    $states[$chat_id]["mode"] = "renovar_opciones";
    $states[$chat_id]["renovar_data"] = $data;

    saveStates($file, $states);
}

function limpiarRenovarEstado($file, &$states, $chat_id) {
    if (isset($states[$chat_id]) && is_array($states[$chat_id])) {
        unset($states[$chat_id]["mode"]);
        unset($states[$chat_id]["pending_command"]);
        unset($states[$chat_id]["renovar_data"]);

        if (empty($states[$chat_id])) {
            unset($states[$chat_id]);
        }

        saveStates($file, $states);
    }
}

function renovarResumenTexto($data) {
    $usuario = $data["usuario"] ?? "Sin usuario";
    $meses = (int)($data["meses"] ?? 0);
    $esVip = !empty($data["es_vip"]);
    $caduca = $data["caduca"] ?? "No encontrada";
    $dias = $data["dias"] ?? "No disponible";

    if ($esVip) {
        $nivel = $data["nivel"] ?? "";
        $precio = renovarPrecioReferidos($nivel, $meses);

        return "📋 RESUMEN DE RENOVACIÓN

━━━━━━━━━━━━━━━━━━

👤 Usuario:
".$usuario."

👥 Referente:
".(($data["referente_nombre"] ?? "") !== "" ? $data["referente_nombre"] : "No disponible")."

📅 Caduca:
".$caduca."

⏳ Tiempo restante:
".$dias."

📦 Duración:
".$meses." meses

🏆 Tarifa:
Referidos VIP

".renovarNivelTxt($nivel)."

💶 Precio:
".$precio."€

━━━━━━━━━━━━━━━━━━

¿Deseas enviar la solicitud?";
    }

    $precio = renovarPrecioNormal($meses);

    return "📋 RESUMEN DE RENOVACIÓN

━━━━━━━━━━━━━━━━━━

👤 Usuario:
".$usuario."

ℹ️ Tipo:
Tarifa estándar

📦 Duración:
".$meses." meses

💶 Precio:
".$precio."€

━━━━━━━━━━━━━━━━━━

¿Deseas enviar la solicitud?";
}


function iniciarRenovacionConUsuario($state_file, &$states, $chat_id, $usuario_mdprime) {
    $usuario_mdprime = trim($usuario_mdprime);
    $datos = consultarClienteApi($usuario_mdprime);

    $caduca = "No encontrada";
    $dias = "No disponible";
    $nombre_encontrado = $usuario_mdprime;
    $nivel_actual = "";
    $referente_nombre = "";
    $es_referido = false;

    if (!empty($datos["ok"]) && !empty($datos["cliente"])) {
        $nombre_encontrado = $datos["cliente"]["nombre"] ?? $usuario_mdprime;
        $caduca = $datos["resumen"]["proxima_caducidad"] ?? "Sin fecha";
        $dias = fmtDias($datos["resumen"]["dias_proxima_caducidad"] ?? null);
        $nivel_actual = renovarNivelKeyDesdeTexto($datos["nivel"]["actual"] ?? "");
        $referente_nombre = $nombre_encontrado;
    } elseif (!empty($datos["ok"]) && !empty($datos["referido"])) {
        $es_referido = true;
        $nombre_encontrado = $datos["referido"]["nombre"] ?? $usuario_mdprime;
        $caduca = $datos["referido"]["caducidad"] ?? "Sin fecha";
        $dias = fmtDias($datos["referido"]["dias"] ?? null);

        $referente_nombre = $datos["referente"]["nombre"] ?? "";
        $referente_id = $datos["referente"]["id"] ?? 0;

        if ($referente_id) {
            $info_nivel = obtenerNivelReferentePorId($referente_id);
            $nivel_actual = $info_nivel["nivel"] ?? "";
        }

        // Todo usuario que siga en la tabla de referidos se reconoce como referido,
        // aunque esté caducado. Si el referente no devuelve nivel, se usa Cobre.
        if ($nivel_actual === "") {
            $nivel_actual = "cobre";
        }
    } elseif (!empty($datos["ok"]) && !empty($datos["cliente_normal"])) {
        $nombre_encontrado = $datos["cliente_normal"]["nombre"] ?? $usuario_mdprime;
        $caduca = $datos["cliente_normal"]["caducidad"] ?? "Sin fecha";
        $dias = fmtDias($datos["cliente_normal"]["dias"] ?? null);
        $referente_nombre = "Cliente normal";
        $nivel_actual = "";
    }

    $es_vip = ($es_referido || $nivel_actual !== "");
    $encontrado_en_bd = !empty($datos["ok"]);

    $ren_data = [
        "usuario" => $usuario_mdprime,
        "usuario_encontrado" => $nombre_encontrado,
        "referente_nombre" => $referente_nombre,
        "es_vip" => $es_vip,
        "es_normal" => (!empty($datos["ok"]) && !empty($datos["cliente_normal"])),
        "nivel_actual" => $nivel_actual,
        "caduca" => $caduca,
        "dias" => $dias,
        "encontrado_en_bd" => $encontrado_en_bd
    ];

    guardarRenovarEstado($state_file, $states, $chat_id, $ren_data);

    if (!$encontrado_en_bd) {
        if (!isset($states[$chat_id]) || !is_array($states[$chat_id])) {
            $states[$chat_id] = [];
        }

        $states[$chat_id]["mode"] = "confirmando_usuario_no_encontrado";
        $states[$chat_id]["renovar_data"] = $ren_data;
        saveStates($state_file, $states);

        $msg = "⚠️ USUARIO NO ENCONTRADO

━━━━━━━━━━━━━━━━━━

👤 Usuario escrito:
".$usuario_mdprime."

No aparece en la base de datos como referido ni como cliente normal.

Puede tratarse de un error al escribir el nombre.

¿Estás seguro de que el usuario es correcto y deseas continuar con precio normal?";

        sendInlineMessage($chat_id, $msg, tecladoConfirmarUsuarioNoEncontrado());
        return;
    }

    if ($es_vip) {
        $msg = "✅ Usuario encontrado

━━━━━━━━━━━━━━━━━━

👤 Usuario:
".$nombre_encontrado."

👥 Referente:
".($referente_nombre !== "" ? $referente_nombre : "No disponible")."

📅 Caduca:
".$caduca."

⏳ Tiempo restante:
".$dias."

━━━━━━━━━━━━━━━━━━

🏆 Nivel disponible:
".renovarNivelTxt($nivel_actual)."

💶 Precios disponibles:
3 meses → ".renovarPrecioReferidos($nivel_actual, 3)."€
6 meses → ".renovarPrecioReferidos($nivel_actual, 6)."€
12 meses → ".renovarPrecioReferidos($nivel_actual, 12)."€

━━━━━━━━━━━━━━━━━━

Selecciona la duración de tu renovación:";
    } else {
        $msg = "ℹ️ USUARIO NO REFERIDO

No aparece como Referido VIP en la base de datos.

❌ No tiene beneficios de referidos.
✅ Puede contratar con precio normal.

━━━━━━━━━━━━━━━━━━

👤 Usuario escrito:
".$usuario_mdprime."

💶 Precios normales:
3 meses → ".renovarPrecioNormal(3)."€
6 meses → ".renovarPrecioNormal(6)."€
12 meses → ".renovarPrecioNormal(12)."€

━━━━━━━━━━━━━━━━━━

Selecciona la duración que quieres contratar:";
    }

    sendInlineMessage($chat_id, $msg, renovarDuracionKeyboard($ren_data));
}

function enviarRenovacionAdmin($admin_id, $chat_id, $update_from, $data) {
    $usuario = $data["usuario"] ?? "Sin usuario";
    $meses = (int)($data["meses"] ?? 0);
    $esVip = !empty($data["es_vip"]);
    $nivel = $data["nivel"] ?? "";
    $caduca = $data["caduca"] ?? "No encontrada";
    $dias = $data["dias"] ?? "No disponible";

    $nombre = trim(
        ($update_from["first_name"] ?? "") . " " .
        ($update_from["last_name"] ?? "")
    );

    $usernameTelegram = $update_from["username"] ?? "";

    if ($esVip) {
        $precio = renovarPrecioReferidos($nivel, $meses);
        $tipo = "Referidos VIP";
        $nivelTxt = renovarNivelTxt($nivel);
    } else {
        $precio = renovarPrecioNormal($meses);
        $tipo = "Tarifa estándar";
        $nivelTxt = "No aplica";
    }

    $admin_msg = "🔄 NUEVA SOLICITUD DE RENOVACIÓN

━━━━━━━━━━━━━━━━━━

👤 Usuario MDPRIME:
".$usuario."

👥 Referente:
".(($data["referente_nombre"] ?? "") !== "" ? $data["referente_nombre"] : "No disponible")."

👤 Nombre Telegram:
".$nombre."

📱 Usuario Telegram:
".($usernameTelegram != "" ? "@".$usernameTelegram : "No disponible")."

🆔 Chat ID:
".$chat_id."

📦 Duración:
".$meses." meses

💳 Tipo:
".$tipo."

🏆 Nivel:
".$nivelTxt."

💶 Precio:
".$precio."€

📅 Caduca:
".$caduca."

⏳ Tiempo restante:
".$dias."

🕒 Fecha:
".date("d/m/Y H:i")."

━━━━━━━━━━━━━━━━━━

💬 Responder:

/reply ".$chat_id." Hola ".$usuario.", hemos recibido tu solicitud de renovación.";

    sendMessage($admin_id, $admin_msg, false);
}


function guardarComprobanteRenovacionEstado($file, &$states, $chat_id, $data) {
    if (!isset($states[$chat_id]) || !is_array($states[$chat_id])) {
        $states[$chat_id] = [];
    }

    $states[$chat_id]["mode"] = "esperando_comprobante_renovacion";
    $states[$chat_id]["comprobante_renovacion"] = $data;

    saveStates($file, $states);
}

function obtenerComprobanteRenovacionEstado($states, $chat_id) {
    if (!isset($states[$chat_id]) || !is_array($states[$chat_id])) {
        return [];
    }

    return $states[$chat_id]["comprobante_renovacion"] ?? [];
}

function limpiarComprobanteRenovacionEstado($file, &$states, $chat_id) {
    if (isset($states[$chat_id]) && is_array($states[$chat_id])) {
        unset($states[$chat_id]["mode"]);
        unset($states[$chat_id]["pending_command"]);
        unset($states[$chat_id]["comprobante_renovacion"]);
        unset($states[$chat_id]["renovar_data"]);

        if (empty($states[$chat_id])) {
            unset($states[$chat_id]);
        }

        saveStates($file, $states);
    }
}

function guardarRenovacionPendienteAdmin($file, &$states, $ren_id, $data) {
    if (!isset($states["_renovaciones_pendientes"]) || !is_array($states["_renovaciones_pendientes"])) {
        $states["_renovaciones_pendientes"] = [];
    }

    $data["ren_id"] = $ren_id;
    $data["creado_en"] = date("Y-m-d H:i:s");
    $states["_renovaciones_pendientes"][$ren_id] = $data;

    saveStates($file, $states);
}

function obtenerRenovacionPendienteAdmin($states, $ren_id) {
    return $states["_renovaciones_pendientes"][$ren_id] ?? null;
}

function borrarRenovacionPendienteAdmin($file, &$states, $ren_id) {
    if (isset($states["_renovaciones_pendientes"][$ren_id])) {
        unset($states["_renovaciones_pendientes"][$ren_id]);
    }

    if (isset($states["_renovaciones_pendientes"]) && empty($states["_renovaciones_pendientes"])) {
        unset($states["_renovaciones_pendientes"]);
    }

    saveStates($file, $states);
}

function tecladoAdminRenovacion($ren_id) {
    return [
        "inline_keyboard" => [
            [
                ["text" => "✅ Aprobar renovación", "callback_data" => "admin_ren_ok_".$ren_id]
            ],
            [
                ["text" => "💬 Abrir chat", "callback_data" => "admin_ren_chat_".$ren_id]
            ],
            [
                ["text" => "❌ Rechazar pago", "callback_data" => "admin_ren_no_".$ren_id]
            ]
        ]
    ];
}

function aplicarRenovacionRailway($usuario, $meses, $es_normal = false) {
    $meses = (int)$meses;
    $usuario = trim((string)$usuario);

    if (!in_array($meses, [3, 6, 12], true)) {
        return ["ok" => false, "error" => "Duración no válida."];
    }

    if ($usuario === "") {
        return ["ok" => false, "error" => "Usuario vacío."];
    }

    try {
        $pdo = getRailwayPdo();

        $tabla = $es_normal ? "clientes_normales" : "referidos";
        $row = null;

        // 1) Buscar en la tabla principal
        $buscar = $pdo->prepare("
            SELECT id, nombre, fecha_caducidad
            FROM ".$tabla."
            WHERE LOWER(TRIM(nombre)) = LOWER(TRIM(?))
               OR REPLACE(LOWER(TRIM(nombre)), ' ', '') = REPLACE(LOWER(TRIM(?)), ' ', '')
            ORDER BY id DESC
            LIMIT 1
        ");
        $buscar->execute([$usuario, $usuario]);
        $row = $buscar->fetch();

        // 2) Si no está en referidos, buscar en clientes normales
        if (!$row && !$es_normal) {
            try {
                $tabla = "clientes_normales";
                $buscar = $pdo->prepare("
                    SELECT id, nombre, fecha_caducidad
                    FROM clientes_normales
                    WHERE LOWER(TRIM(nombre)) = LOWER(TRIM(?))
                       OR REPLACE(LOWER(TRIM(nombre)), ' ', '') = REPLACE(LOWER(TRIM(?)), ' ', '')
                    ORDER BY id DESC
                    LIMIT 1
                ");
                $buscar->execute([$usuario, $usuario]);
                $row = $buscar->fetch();
            } catch (Throwable $e) {
                $row = null;
            }
        }

        // 3) V37: si tampoco existe, crear automáticamente cliente normal
        if (!$row) {
            $tabla = "clientes_normales";

            try {
                $pdo->exec("CREATE TABLE IF NOT EXISTS clientes_normales(
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    nombre VARCHAR(150) NOT NULL,
                    contacto VARCHAR(150) DEFAULT '',
                    telefono VARCHAR(150) DEFAULT '',
                    telegram VARCHAR(100) DEFAULT '',
                    fecha_alta DATE NULL,
                    fecha_caducidad DATE NULL,
                    estado VARCHAR(20) DEFAULT 'Activo',
                    nota TEXT,
                    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
            } catch (Throwable $e) {}

            $ins = $pdo->prepare("
                INSERT INTO clientes_normales(nombre, contacto, telefono, telegram, fecha_alta, fecha_caducidad, estado, nota)
                VALUES(?,?,?,?,CURDATE(),NULL,'Activo',?)
            ");
            $ins->execute([$usuario, "", "", "", "Creado automáticamente desde el bot al aprobar renovación"]);

            $nuevoId = (int)$pdo->lastInsertId();

            $verNuevo = $pdo->prepare("SELECT id, nombre, fecha_caducidad FROM clientes_normales WHERE id=? LIMIT 1");
            $verNuevo->execute([$nuevoId]);
            $row = $verNuevo->fetch();

            if (!$row) {
                return ["ok" => false, "error" => "Intenté crear cliente normal, pero no pude verificarlo."];
            }
        }

        $id = (int)$row["id"];

        $pdo->prepare("UPDATE ".$tabla." SET fecha_caducidad = NULL WHERE id = ? AND CAST(fecha_caducidad AS CHAR) = '0000-00-00'")->execute([$id]);

        $sql = "
            UPDATE ".$tabla."
            SET fecha_caducidad = DATE_ADD(
                CASE
                    WHEN fecha_caducidad IS NOT NULL
                     AND fecha_caducidad >= CURDATE()
                    THEN fecha_caducidad
                    ELSE CURDATE()
                END,
                INTERVAL ".$meses." MONTH
            ),
            estado = 'Activo'
            WHERE id = ?
            LIMIT 1
        ";

        $upd = $pdo->prepare($sql);
        $upd->execute([$id]);

        $ver = $pdo->prepare("SELECT id, nombre, fecha_caducidad, estado FROM ".$tabla." WHERE id=? LIMIT 1");
        $ver->execute([$id]);
        $nuevo = $ver->fetch();

        if (!$nuevo) {
            return ["ok" => false, "error" => "Se actualizó, pero no pude verificar la nueva fecha."];
        }

        return [
            "ok" => true,
            "id" => $id,
            "usuario" => $nuevo["nombre"],
            "nueva_caducidad" => $nuevo["fecha_caducidad"],
            "estado" => $nuevo["estado"],
            "tipo_tabla" => $tabla
        ];

    } catch (Throwable $e) {
        return ["ok" => false, "error" => $e->getMessage()];
    }
}


function fechaBonita($fecha) {
    if (!$fecha || $fecha === "0000-00-00") {
        return "Sin fecha";
    }

    $ts = strtotime($fecha);
    return $ts ? date("d/m/Y", $ts) : $fecha;
}

function renovarPrecioDesdeData($data) {
    $meses = (int)($data["meses"] ?? 0);

    if (!empty($data["es_vip"])) {
        return renovarPrecioReferidos($data["nivel"] ?? "", $meses);
    }

    return renovarPrecioNormal($meses);
}

function renovarTipoDesdeData($data) {
    return !empty($data["es_vip"]) ? "Referidos VIP" : "Tarifa estándar";
}

function mensajePagoRenovacion($data) {
    global $payment_link;

    $usuario = $data["usuario"] ?? "Sin usuario";
    $meses = (int)($data["meses"] ?? 0);
    $precio = renovarPrecioDesdeData($data);
    $tipo = renovarTipoDesdeData($data);
    $nivel = !empty($data["es_vip"]) ? renovarNivelTxt($data["nivel"] ?? "") : "No aplica";

    return "💳 PAGO DE RENOVACIÓN MDPRIME

━━━━━━━━━━━━━━━━━━

👤 Usuario:
".$usuario."

📦 Duración:
".$meses." meses

💳 Tipo:
".$tipo."

🏆 Nivel:
".$nivel."

💶 Importe:
".$precio."€

━━━━━━━━━━━━━━━━━━

🔗 Enlace de pago:
".$payment_link."

━━━━━━━━━━━━━━━━━━

📸 Cuando termines el pago, envía aquí la captura del comprobante.

Tu solicitud quedará pendiente hasta revisar el pago.";
}

function mensajeAdminComprobanteRenovacion($chat_id, $update_from, $data) {
    $usuario = $data["usuario"] ?? "Sin usuario";
    $meses = (int)($data["meses"] ?? 0);
    $precio = renovarPrecioDesdeData($data);
    $tipo = renovarTipoDesdeData($data);
    $nivel = !empty($data["es_vip"]) ? renovarNivelTxt($data["nivel"] ?? "") : "No aplica";
    $caduca = $data["caduca"] ?? "No encontrada";
    $dias = $data["dias"] ?? "No disponible";

    $nombre = trim(
        ($update_from["first_name"] ?? "") . " " .
        ($update_from["last_name"] ?? "")
    );

    $usernameTelegram = $update_from["username"] ?? "";

    return "💳 NUEVO COMPROBANTE DE RENOVACIÓN

━━━━━━━━━━━━━━━━━━

👤 Usuario MDPRIME:
".$usuario."

👤 Nombre Telegram:
".$nombre."

📱 Usuario Telegram:
".($usernameTelegram != "" ? "@".$usernameTelegram : "No disponible")."

🆔 Chat ID:
".$chat_id."

📦 Duración:
".$meses." meses

💳 Tipo:
".$tipo."

🏆 Nivel:
".$nivel."

💶 Importe:
".$precio."€

📅 Caduca:
".$caduca."

⏳ Tiempo restante:
".$dias."

🕒 Fecha:
".date("d/m/Y H:i")."

━━━━━━━━━━━━━━━━━━

📸 Comprobante recibido debajo.

💬 Responder:

/reply ".$chat_id." Hola ".$usuario.", pago recibido. Procedemos con tu renovación.";
}


function mensajeComoRenovar() {
    return "💳 CÓMO RENOVAR POR EL BOT

━━━━━━━━━━━━━━━━━━

1️⃣ Pulsa el comando /renovar.

2️⃣ Escribe tu usuario de MDPRIME.

3️⃣ El bot detectará automáticamente si eres:
👑 Referido VIP
👤 Cliente normal

4️⃣ Elige la duración de tu renovación:
📦 3 meses
📦 6 meses
📦 12 meses

5️⃣ Accederás al enlace de pago correspondiente.

6️⃣ Una vez realizado el pago, envía el comprobante a través del bot.

7️⃣ Cuando el pago sea revisado y aprobado, la renovación se aplicará automáticamente a tu cuenta y recibirás una confirmación.

━━━━━━━━━━━━━━━━━━

⚠️ IMPORTANTE

• Es obligatorio enviar el comprobante de pago para poder validar la renovación.
• Hasta que el pago no sea aprobado, la renovación no se aplicará.
• Si tu usuario no aparece como referido, el bot te mostrará los precios normales.

━━━━━━━━━━━━━━━━━━

🛠 ¿Tienes alguna duda o has tenido algún problema durante el proceso?

Pulsa el botón /soporte del menú principal y nuestro equipo te ayudará lo antes posible.";
}




/* =========================
   PLAN MULTICUENTA (2 O 3 USUARIOS)
========================= */
function multiPrecio($cantidad, $meses) {
    $precios = [
        2 => [3 => 55, 6 => 85, 12 => 120],
        3 => [3 => 80, 6 => 125, 12 => 165]
    ];
    return $precios[(int)$cantidad][(int)$meses] ?? 0;
}

function multiCantidadKeyboard() {
    return ["inline_keyboard" => [
        [["text" => "👥 2 usuarios", "callback_data" => "multi_qty_2"]],
        [["text" => "👨‍👩‍👦 3 usuarios", "callback_data" => "multi_qty_3"]],
        [["text" => "❌ Cancelar", "callback_data" => "multi_cancel"]]
    ]];
}

function multiDuracionKeyboard($cantidad) {
    return ["inline_keyboard" => [
        [["text" => "📦 3 meses · ".multiPrecio($cantidad,3)."€", "callback_data" => "multi_dur_3"]],
        [["text" => "📦 6 meses · ".multiPrecio($cantidad,6)."€", "callback_data" => "multi_dur_6"]],
        [["text" => "📦 12 meses · ".multiPrecio($cantidad,12)."€", "callback_data" => "multi_dur_12"]],
        [["text" => "❌ Cancelar", "callback_data" => "multi_cancel"]]
    ]];
}

function multiConfirmarNombreKeyboard() {
    return ["inline_keyboard" => [
        [["text" => "✅ Sí, continuar", "callback_data" => "multi_name_ok"]],
        [["text" => "✏️ No, escribir de nuevo", "callback_data" => "multi_name_no"]],
        [["text" => "❌ Cancelar", "callback_data" => "multi_cancel"]]
    ]];
}

function multiResumenKeyboard($cantidad) {
    $rows = [[
        ["text" => "✅ Confirmar y pagar", "callback_data" => "multi_pay"]
    ]];
    for ($i=0; $i<(int)$cantidad; $i++) {
        $rows[] = [["text" => "✏️ Cambiar usuario ".($i+1), "callback_data" => "multi_edit_".$i]];
    }
    $rows[] = [["text" => "❌ Cancelar", "callback_data" => "multi_cancel"]];
    return ["inline_keyboard" => $rows];
}

function multiGuardarEstado($file, &$states, $chat_id, $data, $mode) {
    if (!isset($states[$chat_id]) || !is_array($states[$chat_id])) $states[$chat_id] = [];
    $states[$chat_id]["mode"] = $mode;
    $states[$chat_id]["multi_data"] = $data;
    saveStates($file, $states);
}

function multiEstado($states, $chat_id) {
    return (isset($states[$chat_id]) && is_array($states[$chat_id])) ? ($states[$chat_id]["multi_data"] ?? []) : [];
}

function multiLimpiarEstado($file, &$states, $chat_id) {
    if (isset($states[$chat_id]) && is_array($states[$chat_id])) {
        unset($states[$chat_id]["mode"], $states[$chat_id]["multi_data"], $states[$chat_id]["multi_comprobante"]);
        if (empty($states[$chat_id])) unset($states[$chat_id]);
    }
    saveStates($file, $states);
}

function multiUsuarioExiste($usuario) {
    $d = consultarClienteApi($usuario);
    return !empty($d["ok"]);
}

function multiNombreReservado($states, $usuario, $chat_id_actual = "") {
    $n = mdprimeNormalizarBusqueda($usuario);
    foreach (($states["_multicuentas_pendientes"] ?? []) as $p) {
        foreach (($p["usuarios"] ?? []) as $u) {
            if (mdprimeNormalizarBusqueda($u) === $n) return true;
        }
    }
    foreach ($states as $cid => $st) {
        if ((string)$cid === (string)$chat_id_actual || !is_array($st)) continue;
        foreach (($st["multi_data"]["usuarios"] ?? []) as $u) {
            if (mdprimeNormalizarBusqueda($u) === $n) return true;
        }
    }
    return false;
}

function multiResumenTexto($data) {
    $cantidad=(int)($data["cantidad"]??0); $meses=(int)($data["meses"]??0);
    $txt="💎 RESUMEN PLAN MULTICUENTA\n\n━━━━━━━━━━━━━━━━━━\n\n👥 Usuarios:\n".$cantidad."\n\n📦 Duración:\n".$meses." meses\n";
    foreach (($data["usuarios"]??[]) as $i=>$u) $txt.="\n".($i+1)."️⃣ ".$u."\n";
    $txt.="\n💶 Total:\n".multiPrecio($cantidad,$meses)."€\n\n━━━━━━━━━━━━━━━━━━\n\nComprueba los nombres antes de continuar.";
    return $txt;
}

function multiMensajePago($data) {
    global $payment_link;
    return multiResumenTexto($data)."\n\n🔗 Enlace de pago:\n".$payment_link."\n\n📸 Realiza un único pago y envía aquí una sola captura del comprobante.\n\n⚠️ Las cuentas no se crearán hasta que administración apruebe el pago.";
}

function multiGuardarPendiente($file, &$states, $id, $data) {
    if (!isset($states["_multicuentas_pendientes"]) || !is_array($states["_multicuentas_pendientes"])) $states["_multicuentas_pendientes"]=[];
    $data["multi_id"]=$id; $data["creado_en"]=date("Y-m-d H:i:s");
    $states["_multicuentas_pendientes"][$id]=$data; saveStates($file,$states);
}
function multiObtenerPendiente($states,$id){ return $states["_multicuentas_pendientes"][$id]??null; }
function multiBorrarPendiente($file,&$states,$id){ unset($states["_multicuentas_pendientes"][$id]); if(empty($states["_multicuentas_pendientes"]))unset($states["_multicuentas_pendientes"]); saveStates($file,$states); }
function multiAdminKeyboard($id){ return ["inline_keyboard"=>[
    [["text"=>"✅ Aprobar todas","callback_data"=>"adm_multi_ok_".$id]],
    [["text"=>"💬 Abrir chat","callback_data"=>"adm_multi_chat_".$id]],
    [["text"=>"❌ Rechazar","callback_data"=>"adm_multi_no_".$id]]
]]; }

function multiAdminTexto($data) {
    $from=$data["telegram_from"]??[]; $nom=trim(($from["first_name"]??"")." ".($from["last_name"]??"")); $alias=$from["username"]??"";
    $txt="💎 NUEVO PAGO MULTICUENTA\n\n━━━━━━━━━━━━━━━━━━\n\n👥 Plan: ".($data["cantidad"]??0)." usuarios\n📦 Duración: ".($data["meses"]??0)." meses\n💶 Importe: ".multiPrecio($data["cantidad"]??0,$data["meses"]??0)."€\n";
    foreach(($data["usuarios"]??[]) as $i=>$u)$txt.="\n".($i+1)."️⃣ ".$u;
    $txt.="\n\n👤 Telegram: ".($nom!==""?$nom:"No disponible")."\n📲 Alias: ".($alias!==""?"@".$alias:"No disponible")."\n🆔 Chat ID: ".($data["chat_id_cliente"]??"")."\n\n✅ Un solo comprobante para todas las cuentas.";
    return $txt;
}

function multiAplicarAltas($data) {
    $usuarios=$data["usuarios"]??[]; $meses=(int)($data["meses"]??0); $from=$data["telegram_from"]??[]; $chat=$data["chat_id_cliente"]??"";
    $ok=[]; $errores=[];
    foreach($usuarios as $u){
        $r=aplicarNuevaCuentaRailway($u,$meses,$from,$chat);
        if(!empty($r["ok"]))$ok[]=$r; else $errores[]=["usuario"=>$u,"error"=>$r["error"]??"Error desconocido"];
    }
    return ["ok"=>count($ok)>0,"creadas"=>$ok,"errores"=>$errores];
}

/* =========================
   NUEVA CUENTA MDPRIME
========================= */

function precioAltaDesdeData($data, $meses) {
    if (($data["alta_tipo"] ?? "normal") === "referido") {
        $nivel = $data["nivel_referente"] ?? "cobre";
        return renovarPrecioReferidos($nivel, $meses);
    }
    return renovarPrecioNormal($meses);
}

function nuevoDuracionKeyboard($data = []) {
    $p3 = precioAltaDesdeData($data, 3);
    $p6 = precioAltaDesdeData($data, 6);
    $p12 = precioAltaDesdeData($data, 12);

    return [
        "inline_keyboard" => [
            [
                ["text" => "📦 3 meses · ".$p3."€", "callback_data" => "nuevo_dur_3"]
            ],
            [
                ["text" => "📦 6 meses · ".$p6."€", "callback_data" => "nuevo_dur_6"]
            ],
            [
                ["text" => "📦 12 meses · ".$p12."€", "callback_data" => "nuevo_dur_12"]
            ],
            [
                ["text" => "❌ Cancelar", "callback_data" => "nuevo_cancelar"]
            ]
        ]
    ];
}

function guardarNuevoEstado($file, &$states, $chat_id, $data, $mode = "nuevo_opciones") {
    if (!isset($states[$chat_id]) || !is_array($states[$chat_id])) {
        $states[$chat_id] = [];
    }

    $states[$chat_id]["mode"] = $mode;
    $states[$chat_id]["nuevo_data"] = $data;

    saveStates($file, $states);
}

function nuevoEstado($states, $chat_id) {
    if (!isset($states[$chat_id]) || !is_array($states[$chat_id])) {
        return [];
    }

    return $states[$chat_id]["nuevo_data"] ?? [];
}

function limpiarNuevoEstado($file, &$states, $chat_id) {
    if (isset($states[$chat_id]) && is_array($states[$chat_id])) {
        unset($states[$chat_id]["mode"]);
        unset($states[$chat_id]["pending_command"]);
        unset($states[$chat_id]["nuevo_data"]);
        unset($states[$chat_id]["comprobante_nuevo"]);

        if (empty($states[$chat_id])) {
            unset($states[$chat_id]);
        }

        saveStates($file, $states);
    }
}

function guardarComprobanteNuevoEstado($file, &$states, $chat_id, $data) {
    if (!isset($states[$chat_id]) || !is_array($states[$chat_id])) {
        $states[$chat_id] = [];
    }

    $states[$chat_id]["mode"] = "esperando_comprobante_nuevo";
    $states[$chat_id]["comprobante_nuevo"] = $data;

    saveStates($file, $states);
}

function obtenerComprobanteNuevoEstado($states, $chat_id) {
    if (!isset($states[$chat_id]) || !is_array($states[$chat_id])) {
        return [];
    }

    return $states[$chat_id]["comprobante_nuevo"] ?? [];
}

function guardarNuevoPendienteAdmin($file, &$states, $nuevo_id, $data) {
    if (!isset($states["_nuevas_cuentas_pendientes"]) || !is_array($states["_nuevas_cuentas_pendientes"])) {
        $states["_nuevas_cuentas_pendientes"] = [];
    }

    $data["nuevo_id"] = $nuevo_id;
    $data["creado_en"] = date("Y-m-d H:i:s");
    $states["_nuevas_cuentas_pendientes"][$nuevo_id] = $data;

    saveStates($file, $states);
}

function obtenerNuevoPendienteAdmin($states, $nuevo_id) {
    return $states["_nuevas_cuentas_pendientes"][$nuevo_id] ?? null;
}

function borrarNuevoPendienteAdmin($file, &$states, $nuevo_id) {
    if (isset($states["_nuevas_cuentas_pendientes"][$nuevo_id])) {
        unset($states["_nuevas_cuentas_pendientes"][$nuevo_id]);
    }

    if (isset($states["_nuevas_cuentas_pendientes"]) && empty($states["_nuevas_cuentas_pendientes"])) {
        unset($states["_nuevas_cuentas_pendientes"]);
    }

    saveStates($file, $states);
}

function tecladoAdminNuevo($nuevo_id) {
    return [
        "inline_keyboard" => [
            [
                ["text" => "✅ Aprobar alta", "callback_data" => "admin_new_ok_".$nuevo_id]
            ],
            [
                ["text" => "💬 Abrir chat", "callback_data" => "admin_new_chat_".$nuevo_id]
            ],
            [
                ["text" => "❌ Rechazar pago", "callback_data" => "admin_new_no_".$nuevo_id]
            ]
        ]
    ];
}

function mensajePagoNuevo($data) {
    global $payment_link;

    $usuario = $data["usuario"] ?? "Sin usuario";
    $meses = (int)($data["meses"] ?? 0);
    $precio = precioAltaDesdeData($data, $meses);
    $esReferido = (($data["alta_tipo"] ?? "normal") === "referido");

    return ($esReferido ? "👥 ALTA DE NUEVO REFERIDO MDPRIME" : "🆕 ALTA DE CUENTA NUEVA MDPRIME")."

━━━━━━━━━━━━━━━━━━

👤 Usuario solicitado:
".$usuario."

🔐 Contraseña:
La genera nuestro panel. No se puede elegir manualmente.

📦 Duración:
".$meses." meses

💶 Importe:
".$precio."€

━━━━━━━━━━━━━━━━━━

🔗 Enlace de pago:
".$payment_link."

━━━━━━━━━━━━━━━━━━

📸 Cuando termines el pago, envía aquí la captura del comprobante.

⚠️ Importante:
Tu cuenta NO se crea en la base de datos hasta que el pago sea revisado y aprobado.";
}

function mensajeAdminComprobanteNuevo($chat_id, $update_from, $data) {
    $usuario = $data["usuario"] ?? "Sin usuario";
    $meses = (int)($data["meses"] ?? 0);
    $precio = precioAltaDesdeData($data, $meses);
    $esReferido = (($data["alta_tipo"] ?? "normal") === "referido");

    $nombre = trim(
        ($update_from["first_name"] ?? "") . " " .
        ($update_from["last_name"] ?? "")
    );

    $usernameTelegram = $update_from["username"] ?? "";

    return ($esReferido ? "👥 NUEVO COMPROBANTE DE REFERIDO" : "🆕 NUEVO COMPROBANTE PARA ALTA")."

━━━━━━━━━━━━━━━━━━

👤 Usuario solicitado:
".$usuario."

👤 Nombre Telegram:
".$nombre."

📱 Usuario Telegram:
".($usernameTelegram != "" ? "@".$usernameTelegram : "No disponible")."

🆔 Chat ID:
".$chat_id."

📦 Duración:
".$meses." meses

💶 Importe:
".$precio."€

🕒 Fecha:
".date("d/m/Y H:i")."

━━━━━━━━━━━━━━━━━━

📸 Comprobante recibido debajo.

".($esReferido ? "⚠️ Al aprobar, se vinculará al referente: ".($data["referente_nombre"] ?? "No disponible")."." : "⚠️ Al aprobar, se creará la cuenta en clientes_normales como Activo.");
}

function aplicarNuevaCuentaRailway($usuario, $meses, $from_cliente = [], $chat_id_cliente = "") {
    $meses = (int)$meses;
    $usuario = trim((string)$usuario);

    if (!in_array($meses, [3, 6, 12], true)) {
        return ["ok" => false, "error" => "Duración no válida."];
    }

    if ($usuario === "") {
        return ["ok" => false, "error" => "Usuario vacío."];
    }

    try {
        $pdo = getRailwayPdo();

        try {
            $pdo->exec("CREATE TABLE IF NOT EXISTS clientes_normales(
                id INT AUTO_INCREMENT PRIMARY KEY,
                nombre VARCHAR(150) NOT NULL,
                contacto VARCHAR(150) DEFAULT '',
                telefono VARCHAR(150) DEFAULT '',
                telegram VARCHAR(100) DEFAULT '',
                fecha_alta DATE NULL,
                fecha_caducidad DATE NULL,
                estado VARCHAR(20) DEFAULT 'Activo',
                nota TEXT,
                creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        } catch (Throwable $e) {}

        $chk = $pdo->prepare("
            SELECT 'clientes_normales' AS tabla, id, nombre
            FROM clientes_normales
            WHERE LOWER(TRIM(nombre)) = LOWER(TRIM(?))
               OR REPLACE(LOWER(TRIM(nombre)), ' ', '') = REPLACE(LOWER(TRIM(?)), ' ', '')
            LIMIT 1
        ");
        $chk->execute([$usuario, $usuario]);
        $existe = $chk->fetch();

        if (!$existe) {
            $chk = $pdo->prepare("
                SELECT 'referidos' AS tabla, id, nombre
                FROM referidos
                WHERE LOWER(TRIM(nombre)) = LOWER(TRIM(?))
                   OR REPLACE(LOWER(TRIM(nombre)), ' ', '') = REPLACE(LOWER(TRIM(?)), ' ', '')
                LIMIT 1
            ");
            $chk->execute([$usuario, $usuario]);
            $existe = $chk->fetch();
        }

        if ($existe) {
            return ["ok" => false, "error" => "Ese usuario ya existe en la base de datos. Debe usar /renovar."];
        }

        $telegram = $from_cliente["username"] ?? "";
        $contacto = $telegram !== "" ? "@".$telegram : "Chat ID: ".$chat_id_cliente;
        $nota = "Alta creada desde el bot al aprobar pago. Contraseña generada desde panel externo.";

        $ins = $pdo->prepare("
            INSERT INTO clientes_normales(nombre, contacto, telefono, telegram, fecha_alta, fecha_caducidad, estado, nota)
            VALUES(?,?,?,?,CURDATE(),DATE_ADD(CURDATE(), INTERVAL ".$meses." MONTH),'Activo',?)
        ");
        $ins->execute([$usuario, $contacto, (string)$chat_id_cliente, $telegram, $nota]);

        $nuevoId = (int)$pdo->lastInsertId();

        $ver = $pdo->prepare("SELECT id, nombre, fecha_caducidad, estado FROM clientes_normales WHERE id=? LIMIT 1");
        $ver->execute([$nuevoId]);
        $row = $ver->fetch();

        if (!$row) {
            return ["ok" => false, "error" => "Se insertó la cuenta, pero no pude verificarla."];
        }

        return [
            "ok" => true,
            "id" => (int)$row["id"],
            "usuario" => $row["nombre"],
            "nueva_caducidad" => $row["fecha_caducidad"],
            "estado" => $row["estado"],
            "tipo_tabla" => "clientes_normales"
        ];

    } catch (Throwable $e) {
        return ["ok" => false, "error" => $e->getMessage()];
    }
}



function promoverClienteNormalAReferenteRailway($pdo, $normal_id) {
    $normal_id = (int)$normal_id;
    if ($normal_id <= 0) {
        throw new Exception("Cliente normal no válido para convertir en referente.");
    }

    $st = $pdo->prepare("SELECT * FROM clientes_normales WHERE id=? LIMIT 1 FOR UPDATE");
    $st->execute([$normal_id]);
    $normal = $st->fetch();
    if (!$normal) {
        throw new Exception("El cliente normal que iba a convertirse ya no existe.");
    }

    // Evitar duplicados si ya fue convertido desde otra solicitud.
    $chk = $pdo->prepare("SELECT id,nombre FROM clientes WHERE LOWER(TRIM(nombre))=LOWER(TRIM(?)) OR REPLACE(LOWER(TRIM(nombre)),' ','')=REPLACE(LOWER(TRIM(?)),' ','') LIMIT 1 FOR UPDATE");
    $chk->execute([$normal["nombre"], $normal["nombre"]]);
    $yaExiste = $chk->fetch();

    if ($yaExiste) {
        $pdo->prepare("DELETE FROM clientes_normales WHERE id=?")->execute([$normal_id]);
        return ["id" => (int)$yaExiste["id"], "nombre" => $yaExiste["nombre"], "convertido" => true];
    }

    // Insertar únicamente en columnas que realmente existan en la tabla clientes.
    $columnasDisponibles = [];
    foreach ($pdo->query("SHOW COLUMNS FROM clientes")->fetchAll() as $col) {
        $columnasDisponibles[$col["Field"]] = true;
    }

    $datos = [
        "nombre" => $normal["nombre"] ?? "",
        "contacto" => $normal["contacto"] ?? "",
        "telefono" => $normal["telefono"] ?? "",
        "telegram" => $normal["telegram"] ?? ""
    ];

    $cols = [];
    $vals = [];
    $params = [];
    foreach ($datos as $campo => $valor) {
        if (isset($columnasDisponibles[$campo])) {
            $cols[] = $campo;
            $vals[] = "?";
            $params[] = $valor;
        }
    }

    if (!in_array("nombre", $cols, true) || trim((string)($normal["nombre"] ?? "")) === "") {
        throw new Exception("No se puede crear el referente porque falta su nombre.");
    }

    $sql = "INSERT INTO clientes(".implode(",", $cols).") VALUES(".implode(",", $vals).")";
    $ins = $pdo->prepare($sql);
    $ins->execute($params);
    $nuevoId = (int)$pdo->lastInsertId();

    if ($nuevoId <= 0) {
        throw new Exception("No se pudo crear el nuevo referente.");
    }

    $pdo->prepare("DELETE FROM clientes_normales WHERE id=?")->execute([$normal_id]);

    return ["id" => $nuevoId, "nombre" => $normal["nombre"], "convertido" => true];
}

function convertirClienteNormalAReferidoRailway($normal_id, $referente_id, $referente_normal_id = 0, $chat_id_cliente = "") {
    $normal_id = (int)$normal_id;
    $referente_id = (int)$referente_id;
    $referente_normal_id = (int)$referente_normal_id;
    if ($normal_id <= 0 || ($referente_id <= 0 && $referente_normal_id <= 0)) return ["ok"=>false,"error"=>"Datos incompletos para realizar el cambio."];

    try {
        $pdo = getRailwayPdo();
        $pdo->beginTransaction();
        if ($referente_id <= 0 && $referente_normal_id > 0) {
            if ($normal_id === $referente_normal_id) throw new Exception("Una cuenta no puede ser su propio referente.");
            $promocion = promoverClienteNormalAReferenteRailway($pdo, $referente_normal_id);
            $referente_id = (int)$promocion["id"];
        }
        $stRef=$pdo->prepare("SELECT id,nombre FROM clientes WHERE id=? LIMIT 1 FOR UPDATE");
        $stRef->execute([$referente_id]);
        $referente=$stRef->fetch();
        if(!$referente) throw new Exception("El referente seleccionado ya no existe en el panel.");

        $st=$pdo->prepare("SELECT * FROM clientes_normales WHERE id=? LIMIT 1 FOR UPDATE");
        $st->execute([$normal_id]);
        $normal=$st->fetch();
        if(!$normal) throw new Exception("La cuenta normal ya no existe o ya fue movida.");
        $nombre=trim((string)($normal["nombre"]??""));
        if($nombre==="") throw new Exception("La cuenta normal no tiene un nombre válido.");

        $chk=$pdo->prepare("SELECT id FROM referidos WHERE LOWER(TRIM(nombre))=LOWER(TRIM(?)) OR REPLACE(LOWER(TRIM(nombre)),' ','')=REPLACE(LOWER(TRIM(?)),' ','') LIMIT 1");
        $chk->execute([$nombre,$nombre]);
        if($chk->fetch()) throw new Exception("Ese usuario ya figura como referido.");

        $estado=trim((string)($normal["estado"]??"Activo")); if($estado==="") $estado="Activo";
        $fechaAlta=!empty($normal["fecha_alta"])?$normal["fecha_alta"]:date("Y-m-d");
        $fechaCad=!empty($normal["fecha_caducidad"])?$normal["fecha_caducidad"]:null;
        $notaAnterior=trim((string)($normal["nota"]??""));
        $notaCambio="Convertido de cliente normal a referido desde el bot".($chat_id_cliente!==""?". Chat ID: ".$chat_id_cliente:"");
        $nota=$notaAnterior!==""?$notaAnterior." · ".$notaCambio:$notaCambio;

        $ins=$pdo->prepare("INSERT INTO referidos(cliente_id,nombre,estado,fecha_alta,fecha_caducidad,nota) VALUES(?,?,?,?,?,?)");
        $ins->execute([$referente_id,$nombre,$estado,$fechaAlta,$fechaCad,$nota]);
        $nuevoId=(int)$pdo->lastInsertId();
        if($nuevoId<=0) throw new Exception("No se pudo crear el registro como referido.");
        $pdo->prepare("DELETE FROM clientes_normales WHERE id=?")->execute([$normal_id]);
        $pdo->commit();
        return ["ok"=>true,"id"=>$nuevoId,"usuario"=>$nombre,"referente_nombre"=>$referente["nombre"],"fecha_caducidad"=>$fechaCad,"estado"=>$estado];
    } catch(Throwable $e) {
        if(isset($pdo)&&$pdo->inTransaction()) $pdo->rollBack();
        return ["ok"=>false,"error"=>$e->getMessage()];
    }
}

function aplicarNuevoReferidoRailway($usuario, $meses, $referente_id, $from_cliente = [], $chat_id_cliente = "", $referente_normal_id = 0) {
    $usuario = trim((string)$usuario);
    $meses = (int)$meses;
    $referente_id = (int)$referente_id;
    $referente_normal_id = (int)$referente_normal_id;

    if ($usuario === "" || !in_array($meses, [3,6,12], true) || ($referente_id <= 0 && $referente_normal_id <= 0)) {
        return ["ok" => false, "error" => "Datos del nuevo referido no válidos."];
    }

    try {
        $pdo = getRailwayPdo();
        $pdo->beginTransaction();

        $referenteConvertido = false;
        if ($referente_id <= 0 && $referente_normal_id > 0) {
            $promocion = promoverClienteNormalAReferenteRailway($pdo, $referente_normal_id);
            $referente_id = (int)$promocion["id"];
            $referenteConvertido = true;
        }

        $st = $pdo->prepare("SELECT id,nombre FROM clientes WHERE id=? LIMIT 1 FOR UPDATE");
        $st->execute([$referente_id]);
        $referente = $st->fetch();
        if (!$referente) throw new Exception("El referente indicado no existe.");

        foreach (["referidos", "clientes_normales"] as $tabla) {
            $chk = $pdo->prepare("SELECT id FROM ".$tabla." WHERE LOWER(TRIM(nombre))=LOWER(TRIM(?)) OR REPLACE(LOWER(TRIM(nombre)),' ','')=REPLACE(LOWER(TRIM(?)),' ','') LIMIT 1");
            $chk->execute([$usuario,$usuario]);
            if ($chk->fetch()) throw new Exception("Ese usuario ya existe en la base de datos. Debe usar /renovar.");
        }

        $telegram = $from_cliente["username"] ?? "";
        $nota = "Alta de referido creada desde el bot. Chat ID: ".$chat_id_cliente.($telegram !== "" ? " · Telegram: @".$telegram : "");
        $ins = $pdo->prepare("INSERT INTO referidos(cliente_id,nombre,estado,fecha_alta,fecha_caducidad,nota) VALUES(?,?,'Activo',CURDATE(),DATE_ADD(CURDATE(), INTERVAL ".$meses." MONTH),?)");
        $ins->execute([$referente_id,$usuario,$nota]);
        $id=(int)$pdo->lastInsertId();
        $ver=$pdo->prepare("SELECT r.id,r.nombre,r.fecha_caducidad,r.estado,c.nombre referente_nombre FROM referidos r JOIN clientes c ON c.id=r.cliente_id WHERE r.id=? LIMIT 1");
        $ver->execute([$id]);
        $row=$ver->fetch();
        if (!$row) throw new Exception("Se insertó el referido, pero no se pudo verificar.");
        $pdo->commit();
        return ["ok"=>true,"id"=>$id,"usuario"=>$row["nombre"],"nueva_caducidad"=>$row["fecha_caducidad"],"estado"=>$row["estado"],"referente_nombre"=>$row["referente_nombre"],"referente_convertido"=>$referenteConvertido,"tipo_tabla"=>"referidos"];
    } catch (Throwable $e) {
        if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
        return ["ok"=>false,"error"=>$e->getMessage()];
    }
}

/* =========================
   CENTRO DE AYUDA / SOPORTE
========================= */

function soporteMenuKeyboard() {
    return [
        "inline_keyboard" => [
            [["text" => "🔥 Activar apps desconocidas", "callback_data" => "sup_firetv"]],
            [["text" => "🚫 Bloqueos de instalación", "callback_data" => "sup_bloqueos"]],
            [["text" => "🌐 Problemas de conexión", "callback_data" => "sup_conexion"]],
            [["text" => "📺 Dispositivos compatibles", "callback_data" => "sup_dispositivos"]],
            [["text" => "👨‍💻 Contactar con soporte", "callback_data" => "sup_contactar"]]
        ]
    ];
}

function soporteRespuestaKeyboard() {
    return [
        "inline_keyboard" => [
            [["text" => "⬅️ Volver a preguntas", "callback_data" => "sup_menu"]],
            [["text" => "👨‍💻 Contactar con soporte", "callback_data" => "sup_contactar"]]
        ]
    ];
}

function soporteMenuTexto() {
    return "🛠 CENTRO DE AYUDA MDPRIME

Selecciona una pregunta frecuente:

Pulsa sobre la opción para abrir la respuesta.";
}

function soporteTextoFireTv() {
    return "🔥 ACTIVAR APLICACIONES DESCONOCIDAS

🛠️ Paso 1: Mostrar el menú de Desarrollador

1. Ve a Configuración ⚙️.
2. Entra en Mi Fire TV.
3. Selecciona Acerca de.
4. Colócate sobre el nombre de tu Fire TV Stick.
5. Pulsa el botón central del mando 7 veces seguidas.

Aparecerá el mensaje:
«No es necesario, ya eres un desarrollador».

🔓 Paso 2: Activar los orígenes desconocidos

1. Regresa a la pantalla anterior.
2. Entra en Opciones para desarrolladores.
3. Pulsa Instalar aplicaciones desconocidas.
4. Selecciona Downloader y cámbialo a ACTIVADO.";
}

function soporteTextoBloqueos() {
    return "🚫 BLOQUEOS DE INSTALACIÓN

Si el sistema bloquea la APK o aparece un aviso de seguridad, haz esto:

1. Cuando termine la descarga, pulsa Instalar.
2. Si aparece un aviso de seguridad, pulsa Más detalles.
3. Pulsa Instalar de todas formas.
4. Espera a que termine la instalación.
5. Abre la aplicación.";
}

function soporteTextoConexion() {
    return "🌐 PROBLEMAS DE CONEXIÓN

Si la app va lenta, se corta o no conecta, prueba estos pasos en este orden:

1. Cierra las aplicaciones abiertas para liberar memoria.
2. Borra la caché de la app.
3. Reinicia el dispositivo.
4. Apaga el router durante 2 minutos.
5. Enciende primero el router y espera a que tenga Internet.
6. Después enciende el dispositivo y prueba otra vez.
7. Si usas VPN, prueba a desactivarla.
8. Si puedes, conecta el dispositivo por cable Ethernet.";
}

function soporteTextoDispositivos() {
    return "📺 DISPOSITIVOS COMPATIBLES

✅ COMPATIBLES

• Android TV: compatible.
• TV Box Android: compatible.
• Fire TV / Fire Stick: compatible activando permisos.
• Móvil Android: compatible si permite instalar APK.
• Tablet Android: compatible si permite instalar APK.

❌ NO COMPATIBLES DE FORMA DIRECTA

• iPhone
• iPad
• Apple TV

Estos dispositivos no pueden instalar archivos APK de Android de forma directa.";
}



/* =========================
   V62: MODO ADMINISTRADOR / USUARIO ACTIVO
   V60 PANEL INTERACTIVO REFERENTE
========================= */
function tecladoPanelReferenteV60() {
    return ["inline_keyboard" => [
        [["text" => "👤 Mi cuenta", "callback_data" => "refpanel_cuenta"]],
        [["text" => "👥 Mis referidos", "callback_data" => "refpanel_lista_0"]],
        [["text" => "📊 Estadísticas", "callback_data" => "refpanel_stats"]],
        [["text" => "💰 Mis tarifas", "callback_data" => "refpanel_tarifas"]],
        [["text" => "➕ Añadir referido", "callback_data" => "refpanel_anadir"]],
        [["text" => "🔄 Renovar referido", "callback_data" => "refpanel_renovar_0"]],
        [["text" => "📲 Apps", "callback_data" => "menu_apps"]],
        [["text" => "⚽ Agenda deportiva", "callback_data" => "menu_agenda"]],
        [["text" => "💬 Soporte", "callback_data" => "menu_soporte"]],
        [["text" => "🔄 Cambiar de cuenta", "callback_data" => "menu_identificate"]],
        [["text" => "🏠 Inicio", "callback_data" => "menu_inicio"]]
    ]];
}

function tecladoVolverPanelReferenteV60($extra = []) {
    $rows = $extra;
    $rows[] = [["text" => "⬅️ Volver al panel", "callback_data" => "refpanel_inicio"]];
    return ["inline_keyboard" => $rows];
}

function datosReferenteV60($usuario) {
    $data = consultarClienteParaSesionV64($usuario);
    if (empty($data["ok"]) || ($data["tipo"] ?? "") !== "referente") {
        return null;
    }
    return $data;
}

function textoPanelReferenteV60($data) {
    $c = $data["cliente"] ?? [];
    $r = $data["resumen"] ?? [];
    $n = $data["nivel"] ?? [];
    return "👑 PANEL REFERENTE MDPRIME

".
        "🙋 Referente: ".($c["nombre"] ?? "Sin nombre")."
".
        nivelIcono($n["actual"] ?? "")." Nivel: ".($n["actual"] ?? "SIN NIVEL")."
".
        "👥 Referidos: ".($r["total_referidos"] ?? 0)."
".
        "🟢 Activos: ".($r["activos"] ?? 0)." · 🔴 Inactivos: ".($r["inactivos"] ?? 0)."

".
        "Selecciona una opción:";
}

function mostrarPanelReferenteV60($chat_id, $usuario, $editar_id = null) {
    $data = datosReferenteV60($usuario);
    if (!$data) {
        $txt = "⚠️ Esta cuenta no está registrada como Referente VIP.";
        if ($editar_id) editMessageText($chat_id, $editar_id, $txt);
        else sendMessage($chat_id, $txt);
        return false;
    }
    $txt = textoPanelReferenteV60($data);
    $kb = agregarBotonAdminV62(tecladoPanelReferenteV60(), $chat_id);
    if ($editar_id) editMessageText($chat_id, $editar_id, $txt, $kb);
    else sendInlineMessage($chat_id, $txt, $kb);
    return true;
}

function listaReferidosV60($data, $pagina = 0, $modoRenovar = false) {
    $todos = $data["referidos"] ?? [];
    $porPagina = 8;
    $totalPaginas = max(1, (int)ceil(count($todos) / $porPagina));
    $pagina = max(0, min((int)$pagina, $totalPaginas - 1));
    $items = array_slice($todos, $pagina * $porPagina, $porPagina);
    $rows = [];
    foreach ($items as $ref) {
        $id = (int)($ref["id"] ?? 0);
        $icono = estadoIcono($ref["estado"] ?? "Inactivo");
        $pref = $modoRenovar ? "refpanel_renficha_" : "refpanel_ficha_";
        $rows[] = [["text" => $icono." ".($ref["nombre"] ?? "Sin nombre"), "callback_data" => $pref.$id."_".$pagina]];
    }
    $nav = [];
    $prefPag = $modoRenovar ? "refpanel_renovar_" : "refpanel_lista_";
    if ($pagina > 0) $nav[] = ["text" => "⬅️", "callback_data" => $prefPag.($pagina-1)];
    $nav[] = ["text" => "Página ".($pagina+1)."/".$totalPaginas, "callback_data" => "refpanel_nada"];
    if ($pagina < $totalPaginas-1) $nav[] = ["text" => "➡️", "callback_data" => $prefPag.($pagina+1)];
    if ($nav) $rows[] = $nav;
    $rows[] = [["text" => "⬅️ Volver al panel", "callback_data" => "refpanel_inicio"]];
    $titulo = $modoRenovar ? "🔄 SELECCIONA EL REFERIDO A RENOVAR" : "👥 MIS REFERIDOS";
    $texto = $titulo."

🙋 Referente: ".($data["cliente"]["nombre"] ?? "")."
".
             "👥 Total: ".count($todos)." · 🟢 ".($data["resumen"]["activos"] ?? 0)." · 🔴 ".($data["resumen"]["inactivos"] ?? 0);
    if (!$items) $texto .= "

No tienes referidos registrados.";
    return [$texto, ["inline_keyboard" => $rows]];
}

function buscarFichaReferidoV60($data, $referido_id) {
    foreach (($data["referidos"] ?? []) as $ref) {
        if ((int)($ref["id"] ?? 0) === (int)$referido_id) return $ref;
    }
    return null;
}

function textoFichaReferidoV60($ref) {
    return "👤 FICHA DEL REFERIDO

".
        "🙋 Usuario: ".($ref["nombre"] ?? "Sin nombre")."
".
        estadoIcono($ref["estado"] ?? "Inactivo")." Estado: ".($ref["estado"] ?? "Sin estado")."
".
        "📅 Alta: ".($ref["fecha_alta"] ?? "Sin fecha")."
".
        "📅 Caducidad: ".($ref["caducidad"] ?? "Sin fecha")."
".
        "⏳ ".fmtDias($ref["dias"] ?? null).
        (!empty($ref["nota"]) ? "
📝 ".$ref["nota"] : "");
}



/* =========================
   V60 PANEL INTERACTIVO CLIENTE NORMAL
========================= */
function tecladoPanelClienteNormalV60() {
    return ["inline_keyboard" => [
        [["text" => "👤 Mi cuenta", "callback_data" => "normalpanel_cuenta"]],
        [["text" => "🔄 Renovar", "callback_data" => "normalpanel_renovar"]],
        [["text" => "👥 Unirme a un referente", "callback_data" => "normalpanel_unir"]],
        [["text" => "📲 Apps", "callback_data" => "menu_apps"]],
        [["text" => "⚽ Agenda deportiva", "callback_data" => "menu_agenda"]],
        [["text" => "💬 Soporte", "callback_data" => "menu_soporte"]],
        [["text" => "🔄 Cambiar de cuenta", "callback_data" => "menu_identificate"]],
        [["text" => "🏠 Inicio", "callback_data" => "menu_inicio"]]
    ]];
}

function tecladoVolverPanelClienteNormalV60($extra = []) {
    $rows = $extra;
    $rows[] = [["text" => "⬅️ Volver al panel", "callback_data" => "normalpanel_inicio"]];
    return ["inline_keyboard" => $rows];
}

function datosClienteNormalV60($usuario) {
    $data = consultarClienteParaSesionV64($usuario);
    if (empty($data["ok"]) || ($data["tipo"] ?? "") !== "normal") return null;
    return $data;
}

function textoPanelClienteNormalV60($data) {
    $n = $data["cliente_normal"] ?? [];
    return "👤 PANEL CLIENTE MDPRIME

".
        "🙋 Usuario: ".($n["nombre"] ?? "Sin nombre")."
".
        estadoIcono($n["estado"] ?? "Inactivo")." Estado: ".($n["estado"] ?? "Sin estado")."
".
        "📅 Caducidad: ".($n["caducidad"] ?? "Sin fecha")."
".
        "⏳ ".fmtDias($n["dias"] ?? null)."

".
        "Puedes renovar tu cuenta o solicitar unirte a un Referente VIP con aprobación administrativa.";
}

function mostrarPanelClienteNormalV60($chat_id, $usuario, $editar_id = null) {
    $data = datosClienteNormalV60($usuario);
    if (!$data) {
        $txt = "⚠️ Esta cuenta no está registrada como cliente normal.";
        if ($editar_id) editMessageText($chat_id, $editar_id, $txt);
        else sendMessage($chat_id, $txt);
        return false;
    }
    $txt = textoPanelClienteNormalV60($data);
    $kb = agregarBotonAdminV62(tecladoPanelClienteNormalV60(), $chat_id);
    if ($editar_id) editMessageText($chat_id, $editar_id, $txt, $kb);
    else sendInlineMessage($chat_id, $txt, $kb);
    return true;
}


function enviarAgendaV61($chat_id) {
    $json = getAgendaJsonCache();
    if (!$json) { sendMessage($chat_id, "❌ No se pudo cargar la agenda deportiva."); return; }
    $agenda = json_decode($json, true);
    if (empty($agenda["events"])) { sendMessage($chat_id, "⚠️ No hay eventos disponibles."); return; }
    $primerDia = $agenda["events"][0]["fecha"] ?? "Hoy";
    $eventos = array_values(array_filter($agenda["events"], function($e) use ($primerDia) { return ($e["fecha"] ?? "") === $primerDia; }));
    $msg = "🏆 AGENDA DEPORTIVA MDPRIME
📅 ".$primerDia."
🎯 Eventos: ".count($eventos)."

";
    foreach ($eventos as $evento) {
        $msg .= "🕒 ".($evento["hora"] ?? "--:--")."
";
        $msg .= "🏅 ".($evento["deporte"] ?? "Deporte")."
";
        if (!empty($evento["competicion"])) $msg .= "🏆 ".$evento["competicion"]."
";
        $msg .= "📌 ".($evento["evento"] ?? "Evento")."
";
        if (!empty($evento["canal"])) $msg .= "📺 ".$evento["canal"]."
";
        $msg .= "━━━━━━━━━━━━━━
";
        if (mb_strlen($msg, "UTF-8") > 3500) break;
    }
    sendLongMessage($chat_id, $msg);
}

function tecladoPanelReferidoV61() {
    return ["inline_keyboard" => [
        [["text" => "👤 Mi cuenta", "callback_data" => "referidopanel_cuenta"]],
        [["text" => "🔄 Renovar", "callback_data" => "referidopanel_renovar"]],
        [["text" => "📲 Apps", "callback_data" => "menu_apps"]],
        [["text" => "⚽ Agenda deportiva", "callback_data" => "menu_agenda"]],
        [["text" => "💬 Soporte", "callback_data" => "menu_soporte"]],
        [["text" => "🔄 Cambiar de cuenta", "callback_data" => "menu_identificate"]],
        [["text" => "🏠 Inicio", "callback_data" => "menu_inicio"]]
    ]];
}

function esAdministradorV62($chat_id) {
    global $admin_id;
    return (string)$chat_id === (string)$admin_id;
}

function agregarBotonAdminV62($keyboard, $chat_id) {
    // Cuando hay un usuario identificado, se muestra únicamente su menú real.
    // No se mezclan controles administrativos con las opciones del cliente.
    return $keyboard;
}

function tecladoEleccionAdminV62($tipo, $usuario) {
    $tipoTxt = $tipo === "normal" ? "Cliente normal" : ($tipo === "referente" ? "Referente VIP" : "Referido");
    return ["inline_keyboard" => [
        [["text" => "👤 Entrar como este usuario", "callback_data" => "admin_entrar_usuario"]],
        [["text" => "👑 Abrir panel administrador", "callback_data" => "admin_abrir_panel"]],
        [["text" => "🔄 Identificar otra cuenta", "callback_data" => "menu_identificate"]]
    ]];
}

function textoEleccionAdminV62($data, $usuario) {
    $tipo = $data["tipo"] ?? "";
    $tipoTxt = $tipo === "normal" ? "Cliente normal" : ($tipo === "referente" ? "Referente VIP" : "Referido");
    return "✅ Usuario confirmado:\n".$usuario."\n\n".
           "🏷️ Tipo detectado: ".$tipoTxt."\n\n".
           "Eres administrador. Elige cómo deseas entrar:";
}

function tecladoPanelAdminV61() {
    return ["inline_keyboard" => [
        [["text" => "📩 Solicitudes", "callback_data" => "adminpanel_solicitudes"]],
        [["text" => "💳 Renovaciones", "callback_data" => "adminpanel_renovaciones"]],
        [["text" => "👥 Referentes", "callback_data" => "adminpanel_referentes"]],
        [["text" => "👤 Clientes", "callback_data" => "adminpanel_clientes"]],
        [["text" => "📊 Estadísticas", "callback_data" => "adminpanel_estadisticas"]],
        [["text" => "⚙️ Configuración", "callback_data" => "adminpanel_configuracion"]],
        [["text" => "🏠 Inicio", "callback_data" => "menu_inicio"]]
    ]];
}


function adminTextoSolicitudesV61($states) {
    $nuevas = $states["_nuevas_cuentas_pendientes"] ?? [];
    $multi = $states["_multicuentas_pendientes"] ?? [];
    $conversiones = 0;
    foreach ($states as $k => $st) {
        if (!is_array($st) || str_starts_with((string)$k, "_")) continue;
        $modo = $st["mode"] ?? "";
        if (in_array($modo, ["referir_conversion_pendiente_admin", "referir_promocion_pendiente_admin"], true)) $conversiones++;
    }
    $total = count($nuevas) + count($multi) + $conversiones;
    $txt = "📩 SOLICITUDES PENDIENTES\n\n";
    $txt .= "🆕 Nuevas cuentas: ".count($nuevas)."\n";
    $txt .= "👥 Multicuentas: ".count($multi)."\n";
    $txt .= "🔁 Conversiones / promociones: ".$conversiones."\n";
    $txt .= "━━━━━━━━━━━━━━━━━━\n";
    $txt .= "📌 Total pendiente: ".$total;
    if ($total === 0) $txt .= "\n\n✅ No hay solicitudes pendientes.";
    else $txt .= "\n\nLas solicitudes completas siguen apareciendo en este chat con sus botones de aprobar o rechazar.";
    return $txt;
}

function adminTextoRenovacionesV61($states) {
    $pend = $states["_renovaciones_pendientes"] ?? [];
    $txt = "💳 RENOVACIONES PENDIENTES\n\n📌 Total: ".count($pend)."\n";
    if (!$pend) return $txt."\n✅ No hay renovaciones pendientes.";
    $i=0;
    foreach ($pend as $id => $r) {
        if (++$i > 20) { $txt .= "\n… y ".(count($pend)-20)." más."; break; }
        $txt .= "\n━━━━━━━━━━━━━━\n👤 ".($r["usuario"] ?? $r["usuario_mdprime"] ?? "Sin usuario");
        $txt .= "\n📦 ".($r["meses"] ?? "?")." meses";
        if (isset($r["precio"])) $txt .= " · ".$r["precio"]."€";
    }
    $txt .= "\n\nPulsa los botones de aprobar o rechazar en el mensaje original de cada solicitud.";
    return $txt;
}

function adminTextoReferentesV61() {
    try {
        $pdo=getRailwayPdo();
        $rows=$pdo->query("SELECT c.id,c.nombre,c.telegram, COUNT(r.id) total, SUM(CASE WHEN r.estado='Activo' AND (r.fecha_caducidad IS NULL OR r.fecha_caducidad>=CURDATE()) THEN 1 ELSE 0 END) activos FROM clientes c LEFT JOIN referidos r ON r.cliente_id=c.id GROUP BY c.id,c.nombre,c.telegram ORDER BY activos DESC,c.nombre ASC LIMIT 50")->fetchAll();
        $txt="👥 REFERENTES MDPRIME\n\n📌 Total mostrados: ".count($rows)."\n";
        if (!$rows) return $txt."\nNo hay referentes registrados.";
        foreach($rows as $i=>$r){
            $txt.="\n━━━━━━━━━━━━━━\n".($i+1).". 👤 ".($r["nombre"]??"Sin nombre")."\n🟢 Activos: ".(int)($r["activos"]??0)." · 👥 Total: ".(int)($r["total"]??0);
            if (!empty($r["telegram"])) $txt.="\n📲 @".ltrim($r["telegram"],"@");
        }
        return $txt;
    } catch(Throwable $e){ return "❌ No se pudieron cargar los referentes.\n\n".$e->getMessage(); }
}

function adminTextoClientesV61() {
    try {
        $pdo=getRailwayPdo();
        $normales=(int)$pdo->query("SELECT COUNT(*) FROM clientes_normales")->fetchColumn();
        $referidos=(int)$pdo->query("SELECT COUNT(*) FROM referidos")->fetchColumn();
        $actNorm=(int)$pdo->query("SELECT COUNT(*) FROM clientes_normales WHERE estado='Activo' AND (fecha_caducidad IS NULL OR fecha_caducidad>=CURDATE())")->fetchColumn();
        $actRef=(int)$pdo->query("SELECT COUNT(*) FROM referidos WHERE estado='Activo' AND (fecha_caducidad IS NULL OR fecha_caducidad>=CURDATE())")->fetchColumn();
        $rows=$pdo->query("SELECT nombre,estado,fecha_caducidad,'Normal' tipo FROM clientes_normales UNION ALL SELECT nombre,estado,fecha_caducidad,'Referido' tipo FROM referidos ORDER BY fecha_caducidad ASC LIMIT 40")->fetchAll();
        $txt="👤 CLIENTES MDPRIME\n\n👤 Normales: $normales · 🟢 $actNorm activos\n👥 Referidos: $referidos · 🟢 $actRef activos\n📌 Total: ".($normales+$referidos)."\n";
        foreach($rows as $i=>$r){ $txt.="\n━━━━━━━━━━━━━━\n".($i+1).". ".estadoIcono($r["estado"]??"")." ".($r["nombre"]??"Sin nombre")."\n🏷️ ".$r["tipo"]." · 📅 ".(!empty($r["fecha_caducidad"])?date("d/m/Y",strtotime($r["fecha_caducidad"])):"Sin fecha"); }
        return $txt;
    } catch(Throwable $e){ return "❌ No se pudieron cargar los clientes.\n\n".$e->getMessage(); }
}

function adminTextoEstadisticasV61($states) {
    try {
        $pdo=getRailwayPdo();
        $refs=(int)$pdo->query("SELECT COUNT(*) FROM clientes")->fetchColumn();
        $norm=(int)$pdo->query("SELECT COUNT(*) FROM clientes_normales")->fetchColumn();
        $referidos=(int)$pdo->query("SELECT COUNT(*) FROM referidos")->fetchColumn();
        $activos=(int)$pdo->query("SELECT (SELECT COUNT(*) FROM clientes_normales WHERE estado='Activo' AND (fecha_caducidad IS NULL OR fecha_caducidad>=CURDATE())) + (SELECT COUNT(*) FROM referidos WHERE estado='Activo' AND (fecha_caducidad IS NULL OR fecha_caducidad>=CURDATE()))")->fetchColumn();
        $cad7=(int)$pdo->query("SELECT (SELECT COUNT(*) FROM clientes_normales WHERE fecha_caducidad BETWEEN CURDATE() AND DATE_ADD(CURDATE(),INTERVAL 7 DAY)) + (SELECT COUNT(*) FROM referidos WHERE fecha_caducidad BETWEEN CURDATE() AND DATE_ADD(CURDATE(),INTERVAL 7 DAY))")->fetchColumn();
        $ren=count($states["_renovaciones_pendientes"]??[]);
        $sol=count($states["_nuevas_cuentas_pendientes"]??[])+count($states["_multicuentas_pendientes"]??[]);
        return "📊 ESTADÍSTICAS MDPRIME\n\n👑 Referentes: $refs\n👤 Clientes normales: $norm\n👥 Referidos: $referidos\n🟢 Cuentas activas: $activos\n⏳ Caducan en 7 días: $cad7\n📩 Solicitudes pendientes: $sol\n💳 Renovaciones pendientes: $ren";
    } catch(Throwable $e){ return "❌ No se pudieron cargar las estadísticas.\n\n".$e->getMessage(); }
}

function adminTextoConfiguracionV61() {
    try {
        $pdo=getRailwayPdo();
        $niveles=$pdo->query("SELECT nivel,min_activos,trimestral,semestral,anual FROM configuracion_niveles ORDER BY min_activos ASC")->fetchAll();
        $txt="⚙️ CONFIGURACIÓN MDPRIME\n\n💶 Tarifas normales\n3 meses → ".renovarPrecioNormal(3)."€\n6 meses → ".renovarPrecioNormal(6)."€\n12 meses → ".renovarPrecioNormal(12)."€\n\n🏆 Niveles de referentes";
        foreach($niveles as $n){ $txt.="\n\n".nivelIcono($n["nivel"]??"")." ".($n["nivel"]??"Sin nivel")." · desde ".(int)($n["min_activos"]??0)." activos\n3M ".$n["trimestral"]."€ · 6M ".$n["semestral"]."€ · 12M ".$n["anual"]."€"; }
        return $txt;
    } catch(Throwable $e){ return "❌ No se pudo cargar la configuración.\n\n".$e->getMessage(); }
}

function mostrarMenuPrincipalV61($chat_id, &$states, $editar_id = null) {
    $esAdmin = esAdministradorV62($chat_id);
    $usuario = getSavedUsuario($states, $chat_id);
    $vista = (isset($states[$chat_id]) && is_array($states[$chat_id])) ? ($states[$chat_id]["vista_actual"] ?? "") : "";

    // El panel administrativo solo se fuerza cuando el administrador lo ha elegido
    // o todavía no tiene un usuario activo.
    if ($esAdmin && ($vista === "admin" || $usuario === "")) {
        $txt = "👑 PANEL DE ADMINISTRACIÓN MDPRIME\n\nSelecciona una opción:";
        if ($editar_id) editMessageText($chat_id, $editar_id, $txt, tecladoPanelAdminV61());
        else sendInlineMessage($chat_id, $txt, tecladoPanelAdminV61());
        return;
    }

    if ($usuario === "") {
        $txt = "🔥 BIENVENIDO A MDPRIME\n\nPara acceder a tu menú, identifícate una sola vez. El bot recordará tu cuenta.";
        $kb = ["inline_keyboard" => [
            [["text" => "👤 Identificarme", "callback_data" => "menu_identificate"]],
            [["text" => "🆕 Nuevo usuario", "callback_data" => "menu_nuevo_usuario"]],
            [["text" => "📲 Apps", "callback_data" => "menu_apps"]],
            [["text" => "⚽ Agenda deportiva", "callback_data" => "menu_agenda"]],
            [["text" => "💬 Soporte", "callback_data" => "menu_soporte"]]
        ]];
        if ($editar_id) editMessageText($chat_id, $editar_id, $txt, $kb); else sendInlineMessage($chat_id, $txt, $kb);
        return;
    }

    $data = consultarClienteParaSesionV63($usuario);
    if (empty($data["ok"])) {
        unset($states[$chat_id]["usuario_mdprime"], $states[$chat_id]["vista_actual"]);
        saveStates($GLOBALS["state_file"], $states);
        mostrarMenuPrincipalV61($chat_id, $states, $editar_id);
        return;
    }

    if (($data["tipo"] ?? "") === "referente") { mostrarPanelReferenteV60($chat_id, $usuario, $editar_id); return; }
    if (($data["tipo"] ?? "") === "normal") { mostrarPanelClienteNormalV60($chat_id, $usuario, $editar_id); return; }

    $txt = "👤 PANEL DE REFERIDO MDPRIME\n\n🙋 Usuario: ".($data["referido"]["nombre"] ?? $usuario)."\n".estadoIcono($data["referido"]["estado"] ?? "Inactivo")." Estado: ".($data["referido"]["estado"] ?? "Sin estado")."\n📅 Caducidad: ".($data["referido"]["caducidad"] ?? "Sin fecha")."\n\nSelecciona una opción:";
    $kb = agregarBotonAdminV62(tecladoPanelReferidoV61(), $chat_id);
    if ($editar_id) editMessageText($chat_id, $editar_id, $txt, $kb); else sendInlineMessage($chat_id, $txt, $kb);
}

/* =========================
   RECIBIR UPDATE
========================= */


if (isset($_GET["cron"]) && $_GET["cron"] === "avisos_caducidad") {
    $resultado = enviarAvisosCaducidadMdprime();
    header("Content-Type: application/json; charset=utf-8");
    echo json_encode($resultado, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

$content = file_get_contents("php://input");

if (!$content) {
    http_response_code(200);
    exit;
}

$update = json_decode($content, true);

if (isset($update["callback_query"])) {
    $callback = $update["callback_query"];
    $callback_id = $callback["id"] ?? "";
    $callback_data = $callback["data"] ?? "";
    $callback_message = $callback["message"] ?? [];
    $chat_id = $callback_message["chat"]["id"] ?? "";
    $message_id = $callback_message["message_id"] ?? "";
    $from = $callback["from"] ?? [];

    answerCallbackQuery($callback_id);

    $states = loadStates($state_file);

    if ($callback_data === "menu_inicio") { mostrarMenuPrincipalV61($chat_id, $states, $message_id); http_response_code(200); exit; }
    if ($callback_data === "admin_abrir_panel") {
        if (!esAdministradorV62($chat_id)) { answerCallbackQuery($callback_id, "No autorizado."); http_response_code(200); exit; }
        if (!isset($states[$chat_id]) || !is_array($states[$chat_id])) $states[$chat_id] = [];
        $states[$chat_id]["vista_actual"] = "admin";
        saveStates($state_file, $states);
        mostrarMenuPrincipalV61($chat_id, $states, $message_id);
        http_response_code(200); exit;
    }
    if ($callback_data === "admin_entrar_usuario") {
        if (!esAdministradorV62($chat_id)) { answerCallbackQuery($callback_id, "No autorizado."); http_response_code(200); exit; }
        if (!isset($states[$chat_id]) || !is_array($states[$chat_id])) $states[$chat_id] = [];
        $states[$chat_id]["vista_actual"] = "usuario";
        saveStates($state_file, $states);
        mostrarMenuPrincipalV61($chat_id, $states, $message_id);
        http_response_code(200); exit;
    }
    if ($callback_data === "menu_identificate") {
        setUserMode($state_file, $states, $chat_id, "esperando_usuario_mdprime", "/micuenta");
        editMessageText($chat_id, $message_id, "👤 IDENTIFÍCATE\n\nEscribe tu usuario MDPRIME. El bot lo recordará para las próximas veces.");
        http_response_code(200); exit;
    }
    if ($callback_data === "menu_nuevo_usuario") {
        clearUserMode($state_file, $states, $chat_id);
        setUserMode($state_file, $states, $chat_id, "nuevo_usuario");
        editMessageText($chat_id, $message_id, "🆕 CREAR CUENTA NUEVA MDPRIME\n\nEscribe cómo quieres que se llame tu usuario.\n\nEjemplo:\nMiguelTV\n\n⚠️ Si el nombre ya existe, el bot te indicará que debes renovarlo. La cuenta no se activará hasta completar el pago y la aprobación.");
        http_response_code(200); exit;
    }
    if ($callback_data === "menu_apps") { editMessageText($chat_id,$message_id,"📲 APPS POR DOWNLOADER\n\n🔥 V9 → 6713896\n📺 OTT → 7669716\n⚡ V8 → 6541023",["inline_keyboard"=>[[["text"=>"⬅️ Atrás","callback_data"=>"menu_inicio"]]]]); http_response_code(200); exit; }
    if ($callback_data === "menu_agenda") { editMessageText($chat_id,$message_id,"⚽ AGENDA DEPORTIVA

Cargando programación…",["inline_keyboard"=>[[["text"=>"⬅️ Atrás","callback_data"=>"menu_inicio"]]]]); enviarAgendaV61($chat_id); http_response_code(200); exit; }
    if ($callback_data === "menu_soporte") { setUserMode($state_file,$states,$chat_id,"soporte"); editMessageText($chat_id,$message_id,"💬 SOPORTE\n\nEscribe tu consulta y será enviada a administración."); http_response_code(200); exit; }
    if (strpos($callback_data,"referidopanel_")===0) {
        $usuario=getSavedUsuario($states,$chat_id); $data=consultarClienteApi($usuario);
        if ($callback_data==="referidopanel_cuenta") { editMessageText($chat_id,$message_id,formatMiCuenta($data),["inline_keyboard"=>[[["text"=>"⬅️ Atrás","callback_data"=>"menu_inicio"]]]]); }
        elseif ($callback_data==="referidopanel_renovar") { pedirConfirmacionNombreProceso($state_file,$states,$chat_id,$usuario,"renovar"); }
        http_response_code(200); exit;
    }
    if (strpos($callback_data,"adminpanel_")===0) {
        if ((string)$chat_id !== (string)$admin_id) { http_response_code(200); exit; }
        if ($callback_data === "adminpanel_solicitudes") $txt = adminTextoSolicitudesV61($states);
        elseif ($callback_data === "adminpanel_renovaciones") $txt = adminTextoRenovacionesV61($states);
        elseif ($callback_data === "adminpanel_referentes") $txt = adminTextoReferentesV61();
        elseif ($callback_data === "adminpanel_clientes") $txt = adminTextoClientesV61();
        elseif ($callback_data === "adminpanel_estadisticas") $txt = adminTextoEstadisticasV61($states);
        elseif ($callback_data === "adminpanel_configuracion") $txt = adminTextoConfiguracionV61();
        else $txt = "⚠️ Sección no disponible.";
        editMessageText($chat_id,$message_id,$txt,["inline_keyboard"=>[[["text"=>"⬅️ Atrás","callback_data"=>"admin_abrir_panel"]]]]);
        http_response_code(200); exit;
    }

    if (strpos($callback_data, "normalpanel_") === 0) {
        $usuario = getSavedUsuario($states, $chat_id);
        if ($usuario === "") {
            editMessageText($chat_id, $message_id, "⚠️ Primero vincula tu usuario con /micuenta.");
            http_response_code(200); exit;
        }
        $dataNormal = datosClienteNormalV60($usuario);
        if (!$dataNormal) {
            editMessageText($chat_id, $message_id, "⚠️ La cuenta vinculada ya no figura como cliente normal.");
            http_response_code(200); exit;
        }
        if ($callback_data === "normalpanel_inicio") {
            mostrarPanelClienteNormalV60($chat_id, $usuario, $message_id);
            http_response_code(200); exit;
        }
        if ($callback_data === "normalpanel_cuenta") {
            editMessageText($chat_id, $message_id, formatMiCuenta($dataNormal), tecladoVolverPanelClienteNormalV60());
            http_response_code(200); exit;
        }
        if ($callback_data === "normalpanel_renovar") {
            editMessageText($chat_id, $message_id, "🔄 RENOVAR CUENTA

Vamos a iniciar la renovación de:
👤 ".$usuario);
            iniciarRenovacionConUsuario($state_file, $states, $chat_id, $usuario);
            http_response_code(200); exit;
        }
        if ($callback_data === "normalpanel_unir") {
            if (!isset($states[$chat_id]) || !is_array($states[$chat_id])) $states[$chat_id] = [];
            $states[$chat_id]["mode"] = "normalpanel_elegir_referente";
            saveStates($state_file, $states);
            editMessageText($chat_id, $message_id, "👥 UNIRME A UN REFERENTE VIP

Escribe el nombre exacto, Telegram o contacto del referente al que deseas unirte.

⚠️ El cambio necesitará aprobación del administrador.

Para cancelar: /cancelar");
            http_response_code(200); exit;
        }
        if ($callback_data === "normalpanel_apps") {
            editMessageText($chat_id, $message_id, "📲 APPS MDPRIME

Pulsa /apps para ver las aplicaciones disponibles.", tecladoVolverPanelClienteNormalV60());
            http_response_code(200); exit;
        }
        if ($callback_data === "normalpanel_soporte") {
            editMessageText($chat_id, $message_id, "🆘 SOPORTE MDPRIME

Pulsa /soporte y explica tu incidencia.", tecladoVolverPanelClienteNormalV60());
            http_response_code(200); exit;
        }
    }

    if (strpos($callback_data, "refpanel_") === 0) {
        $usuario = getSavedUsuario($states, $chat_id);
        if ($usuario === "") {
            editMessageText($chat_id, $message_id, "⚠️ Primero vincula tu usuario con /micuenta.");
            http_response_code(200); exit;
        }
        $dataRef = datosReferenteV60($usuario);
        if (!$dataRef) {
            editMessageText($chat_id, $message_id, "⚠️ La cuenta vinculada no es un Referente VIP.");
            http_response_code(200); exit;
        }
        if ($callback_data === "refpanel_nada") { http_response_code(200); exit; }
        if ($callback_data === "refpanel_inicio") {
            mostrarPanelReferenteV60($chat_id, $usuario, $message_id);
            http_response_code(200); exit;
        }
        if ($callback_data === "refpanel_cuenta") {
            editMessageText($chat_id, $message_id, formatMiCuenta($dataRef), tecladoVolverPanelReferenteV60());
            http_response_code(200); exit;
        }
        if ($callback_data === "refpanel_stats") {
            $r=$dataRef["resumen"]??[]; $n=$dataRef["nivel"]??[]; $sig=$dataRef["siguiente_nivel"]??null;
            $txt="📊 ESTADÍSTICAS

👥 Total: ".($r["total_referidos"]??0)."
🟢 Activos: ".($r["activos"]??0)."
🔴 Inactivos: ".($r["inactivos"]??0)."
".nivelIcono($n["actual"]??"")." Nivel: ".($n["actual"]??"SIN NIVEL")."
📅 Próxima caducidad: ".($r["proxima_caducidad"]??"Sin fecha");
            if ($sig) $txt.="
🎯 Faltan ".($sig["faltan"]??0)." activos para ".($sig["nivel"]??"").".";
            editMessageText($chat_id,$message_id,$txt,tecladoVolverPanelReferenteV60());
            http_response_code(200); exit;
        }
        if ($callback_data === "refpanel_tarifas") {
            $n=$dataRef["nivel"]??[];
            $txt="💶 MIS TARIFAS

".nivelIcono($n["actual"]??"")." Nivel: ".($n["actual"]??"SIN NIVEL")."

3 meses → ".($n["precio_3_meses"]??0)."€
6 meses → ".($n["precio_6_meses"]??0)."€
12 meses → ".($n["precio_12_meses"]??0)."€";
            editMessageText($chat_id,$message_id,$txt,tecladoVolverPanelReferenteV60());
            http_response_code(200); exit;
        }
        if ($callback_data === "refpanel_anadir") {
            setUserMode($state_file, $states, $chat_id, "referir_usuario");
            editMessageText($chat_id,$message_id,"➕ AÑADIR REFERIDO

Escribe el usuario MDPRIME que deseas unir a tu cuenta de referente.

Para cancelar: /cancelar");
            http_response_code(200); exit;
        }
        if (preg_match('/^refpanel_(lista|renovar)_(\d+)$/',$callback_data,$m)) {
            [$txt,$kb]=listaReferidosV60($dataRef,(int)$m[2],$m[1]==="renovar");
            editMessageText($chat_id,$message_id,$txt,$kb);
            http_response_code(200); exit;
        }
        if (preg_match('/^refpanel_(ficha|renficha)_(\d+)_(\d+)$/',$callback_data,$m)) {
            $ref=buscarFichaReferidoV60($dataRef,(int)$m[2]);
            if(!$ref){ editMessageText($chat_id,$message_id,"⚠️ Referido no encontrado.",tecladoVolverPanelReferenteV60()); http_response_code(200); exit; }
            $pag=(int)$m[3];
            $rows=[];
            $rows[]=[["text"=>"🔄 Renovar este referido","callback_data"=>"refpanel_renovarid_".(int)$ref["id"]]];
            $rows[]=[["text"=>"⬅️ Volver a la lista","callback_data"=>(($m[1]==="renficha")?"refpanel_renovar_":"refpanel_lista_").$pag]];
            $rows[]=[["text"=>"🏠 Panel","callback_data"=>"refpanel_inicio"]];
            editMessageText($chat_id,$message_id,textoFichaReferidoV60($ref),["inline_keyboard"=>$rows]);
            http_response_code(200); exit;
        }
        if (preg_match('/^refpanel_renovarid_(\d+)$/',$callback_data,$m)) {
            $ref=buscarFichaReferidoV60($dataRef,(int)$m[1]);
            if(!$ref){ editMessageText($chat_id,$message_id,"⚠️ Referido no encontrado."); http_response_code(200); exit; }
            pedirConfirmacionNombreProceso($state_file,$states,$chat_id,$ref["nombre"]??"","renovar");
            http_response_code(200); exit;
        }
    }

    if (strpos($callback_data, "adm_multi_") === 0) {
        if ((string)$chat_id !== (string)$admin_id) { answerCallbackQuery($callback_id,"No autorizado."); http_response_code(200); exit; }
        if (!preg_match('/^adm_multi_(ok|no|chat)_(.+)$/',$callback_data,$mm)) { http_response_code(200); exit; }
        $accion=$mm[1]; $id=$mm[2]; $p=multiObtenerPendiente($states,$id);
        if(!$p){ editMessageText($chat_id,$message_id,"ℹ️ Este pedido ya fue gestionado o no existe."); http_response_code(200); exit; }
        $cliente=$p["chat_id_cliente"]??"";
        if($accion==="chat"){
            $fr=$p["telegram_from"]??[]; $al=$fr["username"]??"";
            sendMessage($chat_id,"💬 DATOS DEL CLIENTE\n\nAlias: ".($al!==""?"@".$al:"No disponible")."\nChat ID: ".$cliente."\n\n/reply ".$cliente." Hola, sobre tu plan multicuenta: ");
            http_response_code(200); exit;
        }
        if($accion==="no"){
            multiBorrarPendiente($state_file,$states,$id);
            editMessageText($chat_id,$message_id,"❌ PLAN MULTICUENTA RECHAZADO\n\nNo se ha creado ninguna cuenta.");
            if($cliente!=="")sendMessage($cliente,"❌ No hemos podido validar el pago del plan multicuenta. No se ha creado ninguna cuenta. Pulsa /soporte si necesitas ayuda.");
            http_response_code(200); exit;
        }
        $res=multiAplicarAltas($p);
        if(empty($res["ok"])){
            $err=[]; foreach(($res["errores"]??[]) as $e)$err[]=$e["usuario"].": ".$e["error"];
            editMessageText($chat_id,$message_id,"❌ NO SE PUDO CREAR NINGUNA CUENTA\n\n".implode("\n",$err)."\n\nLa solicitud sigue pendiente.");
            http_response_code(200); exit;
        }
        multiBorrarPendiente($state_file,$states,$id);
        $creadas=[]; foreach(($res["creadas"]??[]) as $r)$creadas[]="✅ ".$r["usuario"]." · caduca ".fechaBonita($r["nueva_caducidad"]??"");
        $errs=[]; foreach(($res["errores"]??[]) as $e)$errs[]="❌ ".$e["usuario"]." · ".$e["error"];
        $texto="✅ PLAN MULTICUENTA APROBADO\n\n".implode("\n",$creadas).(empty($errs)?"":"\n\nINCIDENCIAS:\n".implode("\n",$errs));
        editMessageText($chat_id,$message_id,$texto);
        if($cliente!=="")sendMessage($cliente,$texto."\n\n⭐ Gracias por confiar en MDPRIME.");
        http_response_code(200); exit;
    }

    if (strpos($callback_data, "multi_") === 0) {
        $md=multiEstado($states,$chat_id);
        if($callback_data==="multi_cancel"){ multiLimpiarEstado($state_file,$states,$chat_id); editMessageText($chat_id,$message_id,"❌ Plan multicuenta cancelado."); http_response_code(200); exit; }
        if(strpos($callback_data,"multi_qty_")===0){
            $q=(int)str_replace("multi_qty_","",$callback_data); if(!in_array($q,[2,3],true)){http_response_code(200);exit;}
            $md=["cantidad"=>$q,"usuarios"=>[]]; multiGuardarEstado($state_file,$states,$chat_id,$md,"multi_duracion");
            editMessageText($chat_id,$message_id,"💎 PLAN MULTICUENTA\n\nHas elegido ".$q." usuarios.\n\nSelecciona la duración:",multiDuracionKeyboard($q)); http_response_code(200); exit;
        }
        if(strpos($callback_data,"multi_dur_")===0 && !empty($md["cantidad"])){
            $m=(int)str_replace("multi_dur_","",$callback_data); if(!in_array($m,[3,6,12],true)){http_response_code(200);exit;}
            $md["meses"]=$m; $md["usuarios"]=[]; multiGuardarEstado($state_file,$states,$chat_id,$md,"multi_nombre");
            editMessageText($chat_id,$message_id,"👤 PASO 1 DE ".$md["cantidad"]."\n\nEscribe el nombre de la primera cuenta nueva."); http_response_code(200); exit;
        }
        if($callback_data==="multi_name_no" && !empty($md)){
            unset($md["nombre_pendiente"]); multiGuardarEstado($state_file,$states,$chat_id,$md,"multi_nombre");
            editMessageText($chat_id,$message_id,"✏️ Escribe de nuevo el nombre de esta cuenta."); http_response_code(200); exit;
        }
        if($callback_data==="multi_name_ok" && !empty($md["nombre_pendiente"])){
            $u=$md["nombre_pendiente"]; unset($md["nombre_pendiente"]); $idx=$md["edit_index"]??null;
            if($idx!==null){ $md["usuarios"][(int)$idx]=$u; unset($md["edit_index"]); multiGuardarEstado($state_file,$states,$chat_id,$md,"multi_resumen"); editMessageText($chat_id,$message_id,multiResumenTexto($md),multiResumenKeyboard($md["cantidad"])); }
            else { $md["usuarios"][]=$u; if(count($md["usuarios"])<(int)$md["cantidad"]){ multiGuardarEstado($state_file,$states,$chat_id,$md,"multi_nombre"); editMessageText($chat_id,$message_id,"✅ Nombre guardado: ".$u."\n\n👤 PASO ".(count($md["usuarios"])+1)." DE ".$md["cantidad"]."\n\nEscribe el nombre de la siguiente cuenta."); } else { multiGuardarEstado($state_file,$states,$chat_id,$md,"multi_resumen"); editMessageText($chat_id,$message_id,multiResumenTexto($md),multiResumenKeyboard($md["cantidad"])); } }
            http_response_code(200); exit;
        }
        if(strpos($callback_data,"multi_edit_")===0 && !empty($md)){
            $i=(int)str_replace("multi_edit_","",$callback_data); if(!isset($md["usuarios"][$i])){http_response_code(200);exit;}
            $md["edit_index"]=$i; multiGuardarEstado($state_file,$states,$chat_id,$md,"multi_nombre"); editMessageText($chat_id,$message_id,"✏️ Escribe el nuevo nombre para el usuario ".($i+1).".\n\nNombre actual: ".$md["usuarios"][$i]); http_response_code(200); exit;
        }
        if($callback_data==="multi_pay" && count($md["usuarios"]??[])===(int)($md["cantidad"]??0)){
            multiGuardarEstado($state_file,$states,$chat_id,$md,"esperando_comprobante_multi"); editMessageText($chat_id,$message_id,multiMensajePago($md)); http_response_code(200); exit;
        }
    }

    if (strpos($callback_data, "sup_") === 0) {
        if ($callback_data === "sup_menu") {
            clearUserMode($state_file, $states, $chat_id);
            editMessageText($chat_id, $message_id, soporteMenuTexto(), soporteMenuKeyboard());
        } elseif ($callback_data === "sup_firetv") {
            editMessageText($chat_id, $message_id, soporteTextoFireTv(), soporteRespuestaKeyboard());
        } elseif ($callback_data === "sup_bloqueos") {
            editMessageText($chat_id, $message_id, soporteTextoBloqueos(), soporteRespuestaKeyboard());
        } elseif ($callback_data === "sup_conexion") {
            editMessageText($chat_id, $message_id, soporteTextoConexion(), soporteRespuestaKeyboard());
        } elseif ($callback_data === "sup_dispositivos") {
            editMessageText($chat_id, $message_id, soporteTextoDispositivos(), soporteRespuestaKeyboard());
        } elseif ($callback_data === "sup_contactar") {
            setUserMode($state_file, $states, $chat_id, "soporte");
            editMessageText($chat_id, $message_id, "👨‍💻 CONTACTAR CON SOPORTE\n\nDescribe tu problema con el mayor detalle posible.\n\nPuedes enviar texto, foto o captura de pantalla.");
        }

        http_response_code(200);
        exit;
    }

    if (strpos($callback_data, "adm_refprom_") === 0) {
        if ((string)$chat_id !== (string)$admin_id) {
            answerCallbackQuery($callback_id, "Acción exclusiva de administración.");
            http_response_code(200); exit;
        }

        if (!preg_match('/^adm_refprom_(si|no)_(-?[0-9]+)$/', $callback_data, $m)) {
            editMessageText($chat_id, $message_id, "❌ Solicitud no válida.");
            http_response_code(200); exit;
        }

        $accion = $m[1];
        $clienteChatId = (string)$m[2];
        $clienteStates = loadStates($state_file);
        $ctxCliente = $clienteStates[$clienteChatId] ?? [];
        $normalId = (int)($ctxCliente["promover_referente_normal_id"] ?? 0);
        $normalNombre = trim((string)($ctxCliente["promover_referente_normal_nombre"] ?? ""));

        if ($normalId <= 0 || $normalNombre === "") {
            editMessageText($chat_id, $message_id, "⚠️ La solicitud ya no está disponible o perdió sus datos.");
            http_response_code(200); exit;
        }

        if ($accion === "no") {
            clearUserMode($state_file, $clienteStates, $clienteChatId);
            editMessageText($chat_id, $message_id, "❌ SOLICITUD RECHAZADA

👤 Usuario: ".$normalNombre."

No se ha convertido en Referente VIP.");
            sendMessage($clienteChatId, "❌ El administrador no ha aprobado que ".$normalNombre." sea convertido en Referente VIP.

Puedes escribir otro referente válido o pulsar /soporte.");
            http_response_code(200); exit;
        }

        try {
            $pdoProm = getRailwayPdo();
            $pdoProm->beginTransaction();
            $promocion = promoverClienteNormalAReferenteRailway($pdoProm, $normalId);
            $pdoProm->commit();

            $nuevoReferenteId = (int)($promocion["id"] ?? 0);
            $nuevoReferenteNombre = $promocion["nombre"] ?? $normalNombre;
            if ($nuevoReferenteId <= 0) {
                throw new Exception("No se obtuvo el identificador del nuevo referente.");
            }

            $nivelInfo = obtenerNivelReferentePorId($nuevoReferenteId);
            $nivel = $nivelInfo["nivel"] ?? "";
            if ($nivel === "") $nivel = "cobre";

            if (!isset($clienteStates[$clienteChatId]) || !is_array($clienteStates[$clienteChatId])) {
                $clienteStates[$clienteChatId] = [];
            }
            $clienteStates[$clienteChatId]["mode"] = "referir_usuario";
            $clienteStates[$clienteChatId]["referir_context"] = [
                "alta_tipo" => "referido",
                "referente_id" => $nuevoReferenteId,
                "referente_normal_id" => 0,
                "convertir_referente" => false,
                "referente_nombre" => $nuevoReferenteNombre,
                "nivel_referente" => $nivel
            ];
            unset($clienteStates[$clienteChatId]["promover_referente_normal_id"], $clienteStates[$clienteChatId]["promover_referente_normal_nombre"]);
            saveStates($state_file, $clienteStates);

            editMessageText($chat_id, $message_id, "✅ REFERENTE VIP APROBADO

━━━━━━━━━━━━━━━━━━

👤 Usuario:
".$nuevoReferenteNombre."

🏆 Nivel inicial:
".renovarNivelTxt($nivel)."

✅ Eliminado de clientes normales.
✅ Añadido como Referente VIP.
✅ El usuario puede continuar el alta de su referido.");

            sendMessage($clienteChatId, "✅ APROBACIÓN COMPLETADA

━━━━━━━━━━━━━━━━━━

👥 Referente VIP:
".$nuevoReferenteNombre."

🏆 Nivel actual:
".renovarNivelTxt($nivel)."

💶 Tarifas disponibles:
3 meses → ".renovarPrecioReferidos($nivel, 3)."€
6 meses → ".renovarPrecioReferidos($nivel, 6)."€
12 meses → ".renovarPrecioReferidos($nivel, 12)."€

━━━━━━━━━━━━━━━━━━

Ahora escribe el nombre que quieres para la nueva cuenta MDPRIME que quedará unida a este referente.");
        } catch (Throwable $e) {
            if (isset($pdoProm) && $pdoProm->inTransaction()) $pdoProm->rollBack();
            editMessageText($chat_id, $message_id, "❌ No se pudo convertir el cliente normal en Referente VIP.

Detalle:
".$e->getMessage());
        }

        http_response_code(200); exit;
    }

    if (strpos($callback_data, "adm_refconv_") === 0) {
        if ((string)$chat_id !== (string)$admin_id) {
            answerCallbackQuery($callback_id, "Acción exclusiva de administración.");
            http_response_code(200); exit;
        }

        if (!preg_match('/^adm_refconv_(si|no)_([0-9]+)$/', $callback_data, $m)) {
            editMessageText($chat_id, $message_id, "❌ Solicitud no válida.");
            http_response_code(200); exit;
        }

        $accion = $m[1];
        $solicitudId = (int)$m[2];

        try {
            $pdoSol = asegurarTablaSolicitudesConversion();
            $stSol = $pdoSol->prepare("SELECT * FROM solicitudes_conversion WHERE id=? LIMIT 1");
            $stSol->execute([$solicitudId]);
            $sol = $stSol->fetch();
        } catch (Throwable $e) {
            $sol = false;
        }

        if (!$sol) {
            editMessageText($chat_id, $message_id, "⚠️ La solicitud no existe.");
            http_response_code(200); exit;
        }

        if (($sol["estado"] ?? "") !== "pendiente") {
            editMessageText($chat_id, $message_id, "ℹ️ Esta solicitud ya fue ".($sol["estado"] ?? "resuelta").".");
            http_response_code(200); exit;
        }

        $clienteChatId = (string)$sol["chat_id"];
        $normalId = (int)$sol["normal_id"];
        $normalNombre = trim((string)$sol["normal_nombre"]);
        $referenteId = (int)$sol["referente_id"];
        $referenteNombreSolicitado = trim((string)$sol["referente_nombre"]);

        if ($accion === "no") {
            $up = $pdoSol->prepare("UPDATE solicitudes_conversion SET estado='rechazada',resuelto_en=NOW(),resuelto_por=? WHERE id=? AND estado='pendiente'");
            $up->execute([(string)$chat_id,$solicitudId]);
            editMessageText($chat_id, $message_id, "❌ SOLICITUD RECHAZADA

👤 Usuario: ".$normalNombre."
👥 Referente solicitado: ".$referenteNombreSolicitado."

No se ha modificado ninguna cuenta.");
            sendMessage($clienteChatId, "❌ El administrador no ha aprobado el cambio de tu cuenta normal a referido.");
            http_response_code(200); exit;
        }

        $resultado = convertirClienteNormalAReferidoRailway($normalId, $referenteId, 0, $clienteChatId);

        if (empty($resultado["ok"])) {
            editMessageText($chat_id, $message_id, "❌ No se pudo aprobar la conversión.

Detalle:
".($resultado["error"] ?? "Error desconocido"));
            http_response_code(200); exit;
        }

        $up = $pdoSol->prepare("UPDATE solicitudes_conversion SET estado='aprobada',resuelto_en=NOW(),resuelto_por=? WHERE id=? AND estado='pendiente'");
        $up->execute([(string)$chat_id,$solicitudId]);

        $usuarioMovido = $resultado["usuario"] ?? $normalNombre;
        $referenteNombre = $resultado["referente_nombre"] ?? $referenteNombreSolicitado;

        editMessageText($chat_id, $message_id, "✅ CONVERSIÓN APROBADA

━━━━━━━━━━━━━━━━━━

👤 Usuario:
".$usuarioMovido."

👥 Referente asignado:
".$referenteNombre."

🟢 Estado:
".($resultado["estado"] ?? "Activo")."

📅 Caducidad conservada:
".(!empty($resultado["fecha_caducidad"]) ? date("d/m/Y", strtotime($resultado["fecha_caducidad"])) : "Sin fecha")."

━━━━━━━━━━━━━━━━━━

✅ Eliminado de clientes normales.
✅ Añadido a los referidos de ".$referenteNombre.".");

        sendMessage($clienteChatId, "✅ El administrador ha aprobado el cambio.

👤 Tu cuenta: ".$usuarioMovido."
👥 Referente: ".$referenteNombre."

Tu cuenta ya aparece dentro de los referidos de ese referente.");
        http_response_code(200); exit;
    }

    if (in_array($callback_data, ["confirmar_renovar_si", "confirmar_renovar_no", "confirmar_nuevo_si", "confirmar_nuevo_no"], true)) {
        $es_nuevo = strpos($callback_data, "confirmar_nuevo_") === 0;
        $es_si = substr($callback_data, -3) === "_si";
        $usuario_pendiente = trim($states[$chat_id]["nombre_proceso_pendiente"] ?? "");

        if (!$es_si) {
            if (!isset($states[$chat_id]) || !is_array($states[$chat_id])) {
                $states[$chat_id] = [];
            }
            $states[$chat_id]["mode"] = $es_nuevo ? "nuevo_usuario" : "renovar";
            unset($states[$chat_id]["nombre_proceso_pendiente"]);
            saveStates($state_file, $states);

            editMessageText($chat_id, $message_id, $es_nuevo
                ? "✏️ Escribe de nuevo el nombre de la cuenta que quieres crear."
                : "✏️ Escribe de nuevo el usuario MDPRIME que quieres renovar.");
            http_response_code(200);
            exit;
        }

        if ($usuario_pendiente === "") {
            editMessageText($chat_id, $message_id, "⚠️ No hay ningún nombre pendiente. Inicia el proceso de nuevo.");
            http_response_code(200);
            exit;
        }

        unset($states[$chat_id]["nombre_proceso_pendiente"]);
        saveStates($state_file, $states);

        if ($es_nuevo) {
            $existe = consultarClienteApi($usuario_pendiente);
            if (!empty($existe["ok"])) {
                limpiarNuevoEstado($state_file, $states, $chat_id);
                editMessageText($chat_id, $message_id, "⚠️ Ese usuario ya existe. Para esa cuenta debes usar /renovar.");
                http_response_code(200);
                exit;
            }

            $nuevo_data = ["usuario" => $usuario_pendiente];
            if (!empty($states[$chat_id]["referir_context"]) && is_array($states[$chat_id]["referir_context"])) {
                $nuevo_data = array_merge($nuevo_data, $states[$chat_id]["referir_context"]);
                unset($states[$chat_id]["referir_context"]);
            }
            guardarNuevoEstado($state_file, $states, $chat_id, $nuevo_data);
            editMessageText($chat_id, $message_id,
                "🆕 CUENTA NUEVA MDPRIME

━━━━━━━━━━━━━━━━━━

👤 Usuario solicitado:
".$usuario_pendiente."

🔐 Contraseña:
La genera nuestro panel. No se puede elegir manualmente.

⚠️ Tu cuenta NO se creará todavía.
Primero debes elegir plan, pagar y enviar el comprobante.

━━━━━━━━━━━━━━━━━━

Selecciona la duración:",
                nuevoDuracionKeyboard($nuevo_data)
            );
        } else {
            editMessageText($chat_id, $message_id, "✅ Usuario confirmado:

".$usuario_pendiente."

Continuando con la renovación...");
            iniciarRenovacionConUsuario($state_file, $states, $chat_id, $usuario_pendiente);
        }

        http_response_code(200);
        exit;
    }

    if ($callback_data === "ren_no_encontrado_si" || $callback_data === "ren_no_encontrado_no") {
        $ren_data = renovarEstado($states, $chat_id);

        if ($callback_data === "ren_no_encontrado_no") {
            if (!isset($states[$chat_id]) || !is_array($states[$chat_id])) {
                $states[$chat_id] = [];
            }

            $states[$chat_id]["mode"] = "renovar";
            unset($states[$chat_id]["renovar_data"]);
            saveStates($state_file, $states);

            editMessageText($chat_id, $message_id, "✏️ Escribe de nuevo el usuario MDPRIME que quieres renovar.");
            http_response_code(200);
            exit;
        }

        if (empty($ren_data["usuario"])) {
            editMessageText($chat_id, $message_id, "⚠️ No hay ningún usuario pendiente. Inicia de nuevo con /renovar.");
            http_response_code(200);
            exit;
        }

        $states[$chat_id]["mode"] = "renovar_opciones";
        saveStates($state_file, $states);

        $usuario = $ren_data["usuario"];
        $msg = "ℹ️ CONTINUAR CON PRECIO NORMAL

━━━━━━━━━━━━━━━━━━

👤 Usuario confirmado:
".$usuario."

❌ No figura en la base de datos.
✅ Continuarás con tarifa normal.

💶 Precios normales:
3 meses → ".renovarPrecioNormal(3)."€
6 meses → ".renovarPrecioNormal(6)."€
12 meses → ".renovarPrecioNormal(12)."€

━━━━━━━━━━━━━━━━━━

Selecciona la duración:";

        editMessageText($chat_id, $message_id, $msg, renovarDuracionKeyboard($ren_data));
        http_response_code(200);
        exit;
    }

    if ($callback_data === "confirm_usuario_si" || $callback_data === "confirm_usuario_no") {
        if ($callback_data === "confirm_usuario_no") {
            if (!isset($states[$chat_id]) || !is_array($states[$chat_id])) {
                $states[$chat_id] = [];
            }

            $states[$chat_id]["mode"] = "esperando_usuario_mdprime";
            unset($states[$chat_id]["usuario_pendiente"]);
            saveStates($state_file, $states);

            sendMessage($chat_id, "✏️ Escribe de nuevo tu usuario MDPRIME.");
            http_response_code(200);
            exit;
        }

        $usuario_confirmado = $states[$chat_id]["usuario_pendiente"] ?? "";

        if ($usuario_confirmado === "") {
            sendMessage($chat_id, "⚠️ No hay usuario pendiente de confirmar. Escribe /micuenta para empezar de nuevo.");
            http_response_code(200);
            exit;
        }

        $pending = $states[$chat_id]["pending_command"] ?? "/micuenta";
        $data = consultarClienteParaSesionV63($usuario_confirmado);

        saveUsuarioMdprime($state_file, $states, $chat_id, $usuario_confirmado);

        if (!isset($states[$chat_id]) || !is_array($states[$chat_id])) {
            $states[$chat_id] = [];
        }
        $states[$chat_id]["usuario_mdprime"] = $usuario_confirmado;
        unset($states[$chat_id]["mode"], $states[$chat_id]["usuario_pendiente"]);
        saveStates($state_file, $states);

        if (!isset($states[$chat_id]) || !is_array($states[$chat_id])) $states[$chat_id] = [];
        $states[$chat_id]["vista_actual"] = "usuario";
        saveStates($state_file, $states);
        sendMessage($chat_id, "✅ Usuario confirmado:
".$usuario_confirmado."

Tu usuario ha quedado vinculado correctamente.");

        if (!empty($data["ok"])) {
            if ($pending === "/caducidad") {
                sendLongMessage($chat_id, formatCaducidad($data));
            } elseif ($pending === "/misreferidos" && ($data["tipo"] ?? "") === "referente") {
                [$txt,$kb] = listaReferidosV60($data, 0, false);
                sendInlineMessage($chat_id, $txt, $kb);
            } else {
                mostrarMenuPrincipalV61($chat_id, $states);
            }
        }

        http_response_code(200);
        exit;
    }

    if (strpos($callback_data, "admin_new_ok_") === 0 || strpos($callback_data, "admin_new_no_") === 0 || strpos($callback_data, "admin_new_chat_") === 0) {
        if ((string)$chat_id !== (string)$admin_id) {
            answerCallbackQuery($callback_id, "No autorizado.");
            http_response_code(200);
            exit;
        }

        $aprobar = strpos($callback_data, "admin_new_ok_") === 0;
        $abrir_chat = strpos($callback_data, "admin_new_chat_") === 0;

        if ($aprobar) {
            $nuevo_id = substr($callback_data, strlen("admin_new_ok_"));
        } elseif ($abrir_chat) {
            $nuevo_id = substr($callback_data, strlen("admin_new_chat_"));
        } else {
            $nuevo_id = substr($callback_data, strlen("admin_new_no_"));
        }

        $pendiente = obtenerNuevoPendienteAdmin($states, $nuevo_id);

        if (!$pendiente) {
            editMessageText($chat_id, $message_id, "ℹ️ Esta alta ya fue gestionada o no existe.");
            http_response_code(200);
            exit;
        }

        $usuario = $pendiente["usuario"] ?? "Sin usuario";
        $meses = (int)($pendiente["meses"] ?? 0);
        $cliente_chat_id = $pendiente["chat_id_cliente"] ?? "";
        $precio = precioAltaDesdeData($pendiente, $meses);
        $from_cliente = $pendiente["telegram_from"] ?? [];
        $nombreTelegram = trim(($from_cliente["first_name"] ?? "") . " " . ($from_cliente["last_name"] ?? ""));
        $aliasTelegram = $from_cliente["username"] ?? "";
        $aliasTxt = $aliasTelegram !== "" ? "@".$aliasTelegram : "Sin alias público";
        $linkTelegram = $aliasTelegram !== "" ? "https://t.me/".$aliasTelegram : "No disponible";

        if ($abrir_chat) {
            answerCallbackQuery($callback_id, "Datos del cliente enviados.");
            sendMessage($chat_id, "💬 DATOS PARA CONTACTAR\n\n━━━━━━━━━━━━━━━━━━\n\n👤 Usuario solicitado:\n".$usuario."\n\n👤 Nombre Telegram:\n".($nombreTelegram !== "" ? $nombreTelegram : "No disponible")."\n\n📲 Alias Telegram:\n".$aliasTxt."\n\n🔗 Abrir chat:\n".$linkTelegram."\n\n🆔 Chat ID:\n".$cliente_chat_id."\n\n📦 Meses:\n".$meses."\n\n💶 Importe:\n".$precio."€\n\n━━━━━━━━━━━━━━━━━━\n\nPara responder desde el bot:\n/reply ".$cliente_chat_id." Hola ".$usuario.", ");
            http_response_code(200);
            exit;
        }

        if ($aprobar) {
            $resultado = (($pendiente["alta_tipo"] ?? "normal") === "referido")
                ? aplicarNuevoReferidoRailway($usuario, $meses, (int)($pendiente["referente_id"] ?? 0), $from_cliente, $cliente_chat_id, (int)($pendiente["referente_normal_id"] ?? 0))
                : aplicarNuevaCuentaRailway($usuario, $meses, $from_cliente, $cliente_chat_id);

            if (!empty($resultado["ok"])) {
                borrarNuevoPendienteAdmin($state_file, $states, $nuevo_id);
                $nueva = fechaBonita($resultado["nueva_caducidad"] ?? "");

                editMessageText($chat_id, $message_id, "━━━━━━━━━━━━━━━━━━\n✅ ALTA APROBADA\n━━━━━━━━━━━━━━━━━━\n\n👤 Usuario creado:\n<code>".telegramHtml($usuario)."</code>\n\n📋 Pulsa sobre el usuario para copiarlo.\n\n👤 Nombre Telegram:\n".telegramHtml($nombreTelegram !== "" ? $nombreTelegram : "No disponible")."\n\n📲 Alias Telegram:\n".telegramHtml($aliasTxt)."\n\n🔗 Abrir chat:\n".telegramHtml($linkTelegram)."\n\n🆔 Chat ID:\n<code>".telegramHtml($cliente_chat_id)."</code>\n\n📦 Plan contratado:\n".$meses." meses\n\n💶 Importe pagado:\n".$precio."€\n\n📅 Caducidad:\n".telegramHtml($nueva)."\n\n".(($pendiente["alta_tipo"] ?? "normal") === "referido" ? "✅ Referido vinculado a: ".telegramHtml($resultado["referente_nombre"] ?? ($pendiente["referente_nombre"] ?? "No disponible")).".".(!empty($resultado["referente_convertido"]) ? "
🔄 El cliente normal fue convertido automáticamente en Referente VIP." : "") : "✅ Cuenta creada en clientes_normales como Activo.")."\n━━━━━━━━━━━━━━━━━━", null, "HTML");

                if ($cliente_chat_id !== "") {
                    sendMessage($cliente_chat_id, "✅ Pago aprobado.\n\n".(($pendiente["alta_tipo"] ?? "normal") === "referido" ? "Tu cuenta ha sido creada y vinculada correctamente a tu referente." : "Tu cuenta nueva ya ha sido creada y activada.")."\n\n👤 Usuario MDPRIME:\n<code>".telegramHtml($usuario)."</code>\n\n📋 Pulsa sobre el usuario para copiarlo.\n\n🔐 Contraseña: se genera desde el panel correspondiente.\n📦 Plan contratado: ".$meses." meses\n💶 Importe pagado: ".$precio."€\n📅 Caducidad: ".telegramHtml($nueva)."\n\n⭐ Gracias por confiar en MDPRIME.", true, "HTML");
                }
            } else {
                editMessageText($chat_id, $message_id, "❌ NO SE PUDO CREAR LA CUENTA\n\n👤 Usuario:\n".$usuario."\n\nError:\n".($resultado["error"] ?? "Error desconocido")."\n\nNo se ha borrado la solicitud pendiente.");
            }
        } else {
            borrarNuevoPendienteAdmin($state_file, $states, $nuevo_id);
            editMessageText($chat_id, $message_id, "❌ ALTA RECHAZADA\n\n👤 Usuario:\n".$usuario."\n\nNo se ha creado ninguna cuenta.");

            if ($cliente_chat_id !== "") {
                sendMessage($cliente_chat_id, "❌ No hemos podido validar tu pago.\n\nNo se ha creado ninguna cuenta.\n\nSi crees que es un error, contacta con soporte.");
            }
        }

        http_response_code(200);
        exit;
    }

    if (strpos($callback_data, "admin_ren_ok_") === 0 || strpos($callback_data, "admin_ren_no_") === 0 || strpos($callback_data, "admin_ren_chat_") === 0) {
        if ((string)$chat_id !== (string)$admin_id) {
            answerCallbackQuery($callback_id, "No autorizado.");
            http_response_code(200);
            exit;
        }

        $aprobar = strpos($callback_data, "admin_ren_ok_") === 0;
        $abrir_chat = strpos($callback_data, "admin_ren_chat_") === 0;

        if ($aprobar) {
            $ren_id = substr($callback_data, strlen("admin_ren_ok_"));
        } elseif ($abrir_chat) {
            $ren_id = substr($callback_data, strlen("admin_ren_chat_"));
        } else {
            $ren_id = substr($callback_data, strlen("admin_ren_no_"));
        }

        $pendiente = obtenerRenovacionPendienteAdmin($states, $ren_id);

        if (!$pendiente) {
            editMessageText($chat_id, $message_id, "ℹ️ Esta renovación ya fue gestionada o no existe.");
            http_response_code(200);
            exit;
        }

        if ($abrir_chat) {
            $usuario = $pendiente["usuario"] ?? "Sin usuario";
            $cliente_chat_id = $pendiente["chat_id_cliente"] ?? "";
            $from_cliente = $pendiente["telegram_from"] ?? [];

            $nombreTelegram = trim(
                ($from_cliente["first_name"] ?? "") . " " .
                ($from_cliente["last_name"] ?? "")
            );
            $aliasTelegram = $from_cliente["username"] ?? "";
            $aliasTxt = $aliasTelegram !== "" ? "@".$aliasTelegram : "Sin alias público";
            $linkTelegram = $aliasTelegram !== "" ? "https://t.me/".$aliasTelegram : "No disponible";
            $mesesInfo = (int)($pendiente["meses"] ?? 0);
            $precioInfo = renovarPrecioDesdeData($pendiente);
            $tipoInfo = renovarTipoDesdeData($pendiente);
            $nivelInfo = !empty($pendiente["es_vip"]) ? renovarNivelTxt($pendiente["nivel"] ?? "") : "Plan normal";

            answerCallbackQuery($callback_id, "Datos del cliente enviados.");

            sendMessage(
                $chat_id,
                "💬 DATOS PARA CONTACTAR\n\n━━━━━━━━━━━━━━━━━━\n\n👤 Usuario MDPRIME:\n".$usuario."\n\n👤 Nombre Telegram:\n".($nombreTelegram !== "" ? $nombreTelegram : "No disponible")."\n\n📲 Alias Telegram:\n".$aliasTxt."\n\n🔗 Abrir chat:\n".$linkTelegram."\n\n🆔 Chat ID:\n".$cliente_chat_id."\n\n📦 Plan:\n".$tipoInfo."\n\n🏆 Paquete / nivel:\n".$nivelInfo."\n\n⏳ Meses:\n".$mesesInfo."\n\n💶 Importe:\n".$precioInfo."€\n\n━━━━━━━━━━━━━━━━━━\n\nPara responder desde el bot:\n/reply ".$cliente_chat_id." Hola ".$usuario.", "
            );

            http_response_code(200);
            exit;
        }

        $usuario = $pendiente["usuario"] ?? "Sin usuario";
        $meses = (int)($pendiente["meses"] ?? 0);
        $cliente_chat_id = $pendiente["chat_id_cliente"] ?? "";

        if ($aprobar) {
            $resultado = aplicarRenovacionRailway($usuario, $meses, !empty($pendiente["es_normal"]));

            if (!empty($resultado["ok"])) {
                borrarRenovacionPendienteAdmin($state_file, $states, $ren_id);

                $nueva = fechaBonita($resultado["nueva_caducidad"] ?? "");

                $precio = renovarPrecioDesdeData($pendiente);
                $tipo = renovarTipoDesdeData($pendiente);
                $nivelTxt = !empty($pendiente["es_vip"]) ? renovarNivelTxt($pendiente["nivel"] ?? "") : "Plan normal";

                $from_cliente = $pendiente["telegram_from"] ?? [];
                $nombreTelegram = trim(
                    ($from_cliente["first_name"] ?? "") . " " .
                    ($from_cliente["last_name"] ?? "")
                );
                $aliasTelegram = $from_cliente["username"] ?? "";
                $aliasTxt = $aliasTelegram !== "" ? "@".$aliasTelegram : "Sin alias público";
                $linkTelegram = $aliasTelegram !== "" ? "https://t.me/".$aliasTelegram : "No disponible";

                editMessageText(
                    $chat_id,
                    $message_id,
                    "━━━━━━━━━━━━━━━━━━\n✅ RENOVACIÓN APROBADA\n━━━━━━━━━━━━━━━━━━\n\n👤 Usuario MDPRIME:\n<code>".telegramHtml($usuario)."</code>\n\n📋 Pulsa sobre el usuario para copiarlo.\n\n👤 Nombre Telegram:\n".telegramHtml($nombreTelegram !== "" ? $nombreTelegram : "No disponible")."\n\n📲 Alias Telegram:\n".telegramHtml($aliasTxt)."\n\n🔗 Abrir chat:\n".telegramHtml($linkTelegram)."\n\n🆔 Chat ID:\n<code>".telegramHtml($cliente_chat_id)."</code>\n\n📦 Plan contratado:\n".telegramHtml($tipo)."\n\n🏆 Paquete / nivel:\n".telegramHtml($nivelTxt)."\n\n⏳ Meses añadidos:\n".$meses."\n\n💶 Importe pagado:\n".$precio."€\n\n📅 Nueva caducidad:\n".telegramHtml($nueva)."\n\n✅ Panel y bot actualizados.\n━━━━━━━━━━━━━━━━━━",
                    null,
                    "HTML"
                );

                if ($cliente_chat_id !== "") {
                    sendMessage(
                        $cliente_chat_id,
                        "✅ Pago aprobado.\n\nTu renovación se ha aplicado correctamente.\n\n👤 Usuario MDPRIME:\n<code>".telegramHtml($usuario)."</code>\n\n📋 Pulsa sobre el usuario para copiarlo.\n\n📦 Plan contratado: ".telegramHtml($tipo)."\n🏆 Paquete / nivel: ".telegramHtml($nivelTxt)."\n⏳ Meses añadidos: ".$meses."\n💶 Importe pagado: ".$precio."€\n📅 Nueva caducidad: ".telegramHtml($nueva)."\n\n⭐ Gracias por confiar en MDPRIME.",
                        true,
                        "HTML"
                    );
                }

            } else {
                editMessageText(
                    $chat_id,
                    $message_id,
                    "❌ NO SE PUDO APLICAR LA RENOVACIÓN\n\n👤 Usuario:\n".$usuario."\n\nError:\n".($resultado["error"] ?? "Error desconocido")."\n\nNo se ha borrado la solicitud pendiente."
                );
            }

        } else {
            borrarRenovacionPendienteAdmin($state_file, $states, $ren_id);

            editMessageText(
                $chat_id,
                $message_id,
                "❌ RENOVACIÓN RECHAZADA\n\n👤 Usuario:\n".$usuario."\n\nNo se han sumado meses."
            );

            if ($cliente_chat_id !== "") {
                sendMessage(
                    $cliente_chat_id,
                    "❌ No hemos podido validar tu pago.\n\nNo se ha aplicado ninguna renovación.\n\nSi crees que es un error, contacta con soporte."
                );
            }
        }

        http_response_code(200);
        exit;
    }

    $nuevo_data = nuevoEstado($states, $chat_id);

    if (strpos($callback_data, "nuevo_") === 0 && !empty($nuevo_data)) {
        if ($callback_data === "nuevo_cancelar") {
            limpiarNuevoEstado($state_file, $states, $chat_id);
            editMessageText($chat_id, $message_id, "❌ Alta de cuenta nueva cancelada.");
            http_response_code(200);
            exit;
        }

        if (strpos($callback_data, "nuevo_dur_") === 0) {
            $meses = (int)str_replace("nuevo_dur_", "", $callback_data);
            $nuevo_data["meses"] = $meses;
            guardarComprobanteNuevoEstado($state_file, $states, $chat_id, $nuevo_data);

            editMessageText($chat_id, $message_id, mensajePagoNuevo($nuevo_data));

            http_response_code(200);
            exit;
        }
    }

    $ren_data = renovarEstado($states, $chat_id);

    if (strpos($callback_data, "ren_") !== 0 || empty($ren_data)) {
        http_response_code(200);
        exit;
    }

    if ($callback_data === "ren_cancelar") {
        limpiarRenovarEstado($state_file, $states, $chat_id);
        editMessageText($chat_id, $message_id, "❌ Renovación cancelada.");
        http_response_code(200);
        exit;
    }

    if (strpos($callback_data, "ren_dur_") === 0) {
        $meses = (int)str_replace("ren_dur_", "", $callback_data);
        $ren_data["meses"] = $meses;

        if (!empty($ren_data["es_vip"])) {
            // V27: si el sistema ya sabe el nivel, no se pregunta paquete.
            $nivel_auto = strtolower(trim($ren_data["nivel_actual"] ?? ""));

            if ($nivel_auto !== "") {
                $ren_data["nivel"] = $nivel_auto;
                guardarComprobanteRenovacionEstado($state_file, $states, $chat_id, $ren_data);

                editMessageText(
                    $chat_id,
                    $message_id,
                    mensajePagoRenovacion($ren_data)
                );
            } else {
                guardarRenovarEstado($state_file, $states, $chat_id, $ren_data);

                editMessageText(
                    $chat_id,
                    $message_id,
                    "🏆 REFERIDOS VIP\n\nNo he podido detectar automáticamente tu nivel.\nSelecciona tu nivel de referidos:",
                    renovarNivelKeyboard($ren_data["nivel_actual"] ?? "")
                );
            }
        } else {
            guardarComprobanteRenovacionEstado($state_file, $states, $chat_id, $ren_data);

            editMessageText(
                $chat_id,
                $message_id,
                mensajePagoRenovacion($ren_data)
            );
        }

        http_response_code(200);
        exit;
    }

    if (strpos($callback_data, "ren_lvl_") === 0) {
        $nivel = str_replace("ren_lvl_", "", $callback_data);
        $nivel_actual = $ren_data["nivel_actual"] ?? "";

        if (renovarOrdenNivel($nivel) > renovarOrdenNivel($nivel_actual)) {
            answerCallbackQuery(
                $callback_id,
                "❌ No tienes acceso a ese nivel. Tu nivel actual es ".renovarNivelTxt($nivel_actual)."."
            );
            http_response_code(200);
            exit;
        }

        $ren_data["nivel"] = $nivel;
        guardarRenovarEstado($state_file, $states, $chat_id, $ren_data);

        editMessageText(
            $chat_id,
            $message_id,
            renovarResumenTexto($ren_data),
            renovarConfirmarKeyboard()
        );

        http_response_code(200);
        exit;
    }

    if ($callback_data === "ren_confirmar") {
        if (empty($ren_data["meses"])) {
            editMessageText($chat_id, $message_id, "❌ Faltan datos de la renovación. Vuelve a iniciar /renovar.");
            limpiarRenovarEstado($state_file, $states, $chat_id);
            http_response_code(200);
            exit;
        }

        guardarComprobanteRenovacionEstado($state_file, $states, $chat_id, $ren_data);

        editMessageText(
            $chat_id,
            $message_id,
            mensajePagoRenovacion($ren_data)
        );

        http_response_code(200);
        exit;
    }

    http_response_code(200);
    exit;
}

if (!isset($update["message"])) {
    http_response_code(200);
    exit;
}

$chat_id = $update["message"]["chat"]["id"];
$text = trim($update["message"]["text"] ?? "");
$message_id = $update["message"]["message_id"] ?? null;

$states = loadStates($state_file);
$user_state = getUserMode($states, $chat_id);


// Comprobante único del plan multicuenta.
if ($user_state === "esperando_comprobante_multi") {
    $tiene = isset($update["message"]["photo"]) || isset($update["message"]["document"]);
    if ($tiene && $message_id) {
        $md=multiEstado($states,$chat_id); $from_user=$update["message"]["from"]??[]; $id=uniqid("m");
        $md["chat_id_cliente"]=$chat_id; $md["telegram_from"]=$from_user;
        multiGuardarPendiente($state_file,$states,$id,$md);
        sendInlineMessage($admin_id,multiAdminTexto($md)."\n\n📸 Comprobante recibido debajo.",multiAdminKeyboard($id));
        forwardMessage($admin_id,$chat_id,$message_id); multiLimpiarEstado($state_file,$states,$chat_id);
        sendMessage($chat_id,"✅ Comprobante único recibido. El plan multicuenta queda pendiente de revisión administrativa.");
        http_response_code(200); exit;
    }
    if($text!=="" && substr($text,0,1)!=="/"){ sendMessage($chat_id,"📸 Envía una captura o documento del comprobante único. Para cancelar, pulsa /start."); http_response_code(200); exit; }
}

// Si estamos esperando el comprobante de alta nueva, aceptar captura/foto/documento.
if ($user_state === "esperando_comprobante_nuevo") {
    $tiene_comprobante = isset($update["message"]["photo"]) || isset($update["message"]["document"]);

    if ($tiene_comprobante && $message_id) {
        $comp_data = obtenerComprobanteNuevoEstado($states, $chat_id);
        $from_user = $update["message"]["from"] ?? [];

        $nuevo_id = uniqid("n");
        $comp_data["chat_id_cliente"] = $chat_id;
        $comp_data["telegram_from"] = $from_user;

        guardarNuevoPendienteAdmin($state_file, $states, $nuevo_id, $comp_data);

        sendInlineMessage(
            $admin_id,
            mensajeAdminComprobanteNuevo($chat_id, $from_user, $comp_data)."\n\n━━━━━━━━━━━━━━━━━━\n\n✅ Revisa el comprobante y aprueba o rechaza el alta.",
            tecladoAdminNuevo($nuevo_id)
        );

        forwardMessage($admin_id, $chat_id, $message_id);

        limpiarNuevoEstado($state_file, $states, $chat_id);

        sendMessage(
            $chat_id,
            "✅ Comprobante recibido correctamente.\n\nQueda pendiente de revisión. Tu cuenta NO se creará hasta que el pago sea aprobado."
        );

        http_response_code(200);
        exit;
    }

    if ($text !== "" && substr($text, 0, 1) !== "/") {
        sendMessage(
            $chat_id,
            "📸 Para finalizar el alta, envía una captura o imagen del comprobante de pago.\n\nSi quieres cancelar, escribe /start."
        );

        http_response_code(200);
        exit;
    }
}

// Si estamos esperando el comprobante de renovación, aceptar captura/foto/documento.
if ($user_state === "esperando_comprobante_renovacion") {
    $tiene_comprobante = isset($update["message"]["photo"]) || isset($update["message"]["document"]);

    if ($tiene_comprobante && $message_id) {
        $comp_data = obtenerComprobanteRenovacionEstado($states, $chat_id);
        $from_user = $update["message"]["from"] ?? [];

        $ren_id = uniqid("r");
        $comp_data["chat_id_cliente"] = $chat_id;
        $comp_data["telegram_from"] = $from_user;

        guardarRenovacionPendienteAdmin($state_file, $states, $ren_id, $comp_data);

        sendInlineMessage(
            $admin_id,
            mensajeAdminComprobanteRenovacion($chat_id, $from_user, $comp_data)."\n\n━━━━━━━━━━━━━━━━━━\n\n✅ Revisa el comprobante y aprueba o rechaza la renovación.",
            tecladoAdminRenovacion($ren_id)
        );

        forwardMessage($admin_id, $chat_id, $message_id);

        limpiarComprobanteRenovacionEstado($state_file, $states, $chat_id);

        sendMessage(
            $chat_id,
            "✅ Comprobante recibido correctamente.\n\nQueda pendiente de revisión. Cuando se apruebe el pago, tu renovación se aplicará automáticamente."
        );

        http_response_code(200);
        exit;
    }

    if ($text !== "" && substr($text, 0, 1) !== "/") {
        sendMessage(
            $chat_id,
            "📸 Para finalizar la renovación, envía una captura o imagen del comprobante de pago.\n\nSi quieres cancelar, escribe /start."
        );

        http_response_code(200);
        exit;
    }
}

if ($text === "") {
    http_response_code(200);
    exit;
}

$command = strtolower(trim(explode(" ", $text)[0]));
$command = explode("@", $command)[0];

// Botón visible del teclado: reutiliza exactamente el flujo existente de /nuevo.
if (trim($text) === "🆕 Nuevo usuario") {
    $command = "/nuevo";
}

$parts_text = explode(" ", $text, 2);
$command_arg = isset($parts_text[1]) ? trim($parts_text[1]) : "";

$chat_type = $update["message"]["chat"]["type"] ?? "private";

// En grupos, ignorar cualquier texto normal que no sea comando.
// Así el bot no responde "Comando no reconocido" a conversaciones normales.
if ($chat_type !== "private" && substr($text, 0, 1) !== "/") {
    http_response_code(200);
    exit;
}

$message_id = $update["message"]["message_id"] ?? null;

// Comandos privados usados dentro de grupos:
// se borra el comando, se muestra aviso con botón al privado y se borra el aviso.
$private_group_commands = ["/identificate"];

if (in_array($command, $private_group_commands, true) && $chat_type !== "private") {
    if ($message_id) {
        deleteMessage($chat_id, $message_id);
    }

    $aviso = "🔒 Esta consulta es privada.\n\nPara proteger tus datos, abre el bot en privado y usa el comando allí.";

    $keyboard_inline = [
        "inline_keyboard" => [
            [
                [
                    "text" => "🔒 Abrir MDPRIME Bot",
                    "url" => $bot_link
                ]
            ]
        ]
    ];

    $sent = sendInlineMessage($chat_id, $aviso, $keyboard_inline);
    $aviso_id = $sent["result"]["message_id"] ?? null;

    if ($aviso_id) {
        sleep(8);
        deleteMessage($chat_id, $aviso_id);
    }

    http_response_code(200);
    exit;
}



$states = loadStates($state_file);
$user_state = getUserMode($states, $chat_id);
$saved_usuario = getSavedUsuario($states, $chat_id);

// Seguridad extra para grupos:
// si quedó un estado antiguo pendiente en un grupo, se limpia y NO se responde allí.
// Esto evita que aparezca el mensaje "Introduce tu usuario..." en el grupo.
if ($chat_type !== "private" && $user_state !== "") {
    clearUserMode($state_file, $states, $chat_id);
    http_response_code(200);
    exit;
}

// Si el usuario escribe otro comando mientras el bot esperaba un dato,
// cancelamos el estado anterior para que el comando funcione normal.
if ($user_state !== "" && substr($text, 0, 1) === "/" && $command !== "/reply") {
    clearUserMode($state_file, $states, $chat_id);
    $user_state = "";
}

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

    $data = consultarClienteApi($usuario);

    if (empty($data["ok"])) {
        // Si no existe en la base de datos, no se guarda como cuenta.
        // Se le ofrece contratación con precios normales.
        iniciarRenovacionConUsuario($state_file, $states, $chat_id, $usuario);
        http_response_code(200);
        exit;
    }

    if (!isset($states[$chat_id]) || !is_array($states[$chat_id])) {
        $states[$chat_id] = [];
    }

    $states[$chat_id]["mode"] = "confirmando_usuario_mdprime";
    $states[$chat_id]["pending_command"] = $pending;
    $states[$chat_id]["usuario_pendiente"] = $usuario;
    saveStates($state_file, $states);

    sendInlineMessage(
        $chat_id,
        resumenConfirmacionUsuario($usuario, $data),
        tecladoConfirmarUsuarioMdprime()
    );

    http_response_code(200);
    exit;
}


if ($user_state === "confirmando_usuario_mdprime") {
    $usuario_pendiente = "";
    $datos_pendientes = null;

    if (isset($states[$chat_id]) && is_array($states[$chat_id])) {
        $usuario_pendiente = trim($states[$chat_id]["usuario_pendiente"] ?? "");
    }

    if ($usuario_pendiente !== "") {
        $datos_pendientes = consultarClienteParaSesionV63($usuario_pendiente);
    }

    if (!empty($datos_pendientes["ok"])) {
        sendInlineMessage(
            $chat_id,
            resumenConfirmacionUsuario($usuario_pendiente, $datos_pendientes),
            tecladoConfirmarUsuarioMdprime()
        );
    } else {
        sendInlineMessage(
            $chat_id,
            "⚠️ Tienes un usuario pendiente de confirmar.

👤 Usuario:
".$usuario_pendiente."

¿Confirmas que esta cuenta es tuya?",
            tecladoConfirmarUsuarioMdprime()
        );
    }

    http_response_code(200);
    exit;
}

if ($user_state === "normalpanel_elegir_referente") {
    $nombre_referente = preg_replace('/\s+/', ' ', trim(str_replace(["\r","\n","\t"], ' ', $text)));
    if ($nombre_referente === "" || substr($nombre_referente, 0, 1) === "/") {
        sendMessage($chat_id, "👥 Escribe el nombre exacto, Telegram o contacto del Referente VIP.");
        http_response_code(200); exit;
    }

    $usuarioNormal = getSavedUsuario($states, $chat_id);
    $dataNormal = datosClienteNormalV60($usuarioNormal);
    if (!$dataNormal) {
        resetUserProcessState($state_file, $states, $chat_id);
        sendMessage($chat_id, "⚠️ Tu cuenta ya no figura como cliente normal. Pulsa /micuenta para actualizarla.");
        http_response_code(200); exit;
    }

    $infoRef = buscarReferenteParaAlta($nombre_referente);
    if (empty($infoRef["ok"]) || empty($infoRef["referente"]["id"])) {
        sendMessage($chat_id, "❌ No encuentro ese Referente VIP.

Detalle:
".($infoRef["error"] ?? "No encontrado")."

Escríbelo de nuevo o pulsa /cancelar.");
        http_response_code(200); exit;
    }

    $normal = $dataNormal["cliente_normal"] ?? [];
    $normalId = (int)($normal["id"] ?? 0);
    if ($normalId <= 0) {
        sendMessage($chat_id, "❌ No pude obtener el identificador de tu cuenta. Pulsa /soporte.");
        http_response_code(200); exit;
    }

    $refId = (int)$infoRef["referente"]["id"];
    $refNombre = $infoRef["referente"]["nombre"] ?? $nombre_referente;

    $solicitud = crearSolicitudConversionMysql(
        $chat_id,
        $normalId,
        $normal["nombre"] ?? $usuarioNormal,
        $refId,
        $refNombre
    );

    if (empty($solicitud["ok"])) {
        sendMessage($chat_id, "❌ No se pudo guardar la solicitud.

Detalle:
".($solicitud["error"] ?? "Error desconocido"));
        http_response_code(200); exit;
    }

    resetUserProcessState($state_file, $states, $chat_id);

    sendMessage($chat_id, "⏳ SOLICITUD ENVIADA

👤 Tu cuenta: ".($normal["nombre"] ?? $usuarioNormal)."
👥 Referente solicitado: ".$refNombre."
📅 Caducidad conservada: ".($normal["caducidad"] ?? "Sin fecha")."

El cambio solo se realizará si lo aprueba el administrador.");

    $nombreTelegram = trim(($from["first_name"] ?? "")." ".($from["last_name"] ?? ""));
    $aliasTelegram = $from["username"] ?? "";
    sendInlineMessage($admin_id,
        "🔔 SOLICITUD: CLIENTE NORMAL → REFERIDO

👤 Cliente normal:
".($normal["nombre"] ?? $usuarioNormal)."

👥 Referente solicitado:
".$refNombre."

📅 Caducidad actual:
".($normal["caducidad"] ?? "Sin fecha")."

👤 Solicitante Telegram:
".($nombreTelegram !== "" ? $nombreTelegram : "No disponible")."

📲 Alias:
".($aliasTelegram !== "" ? "@".$aliasTelegram : "No disponible")."

🆔 Chat ID:
".$chat_id."

¿Aprobar el cambio conservando la fecha de caducidad?",
        tecladoAdminConvertirNormalAReferido((int)$solicitud["id"])
    );
    http_response_code(200); exit;
}

if ($user_state === "referir_referente") {
    $nombre_referente = preg_replace('/\s+/', ' ', trim(str_replace(["\r","\n","\t"], ' ', $text)));

    if ($nombre_referente === "" || substr($nombre_referente, 0, 1) === "/") {
        sendMessage($chat_id, "👥 Escribe el nombre exacto del referente que te ha invitado.\n\nEjemplo:\nVictor");
        http_response_code(200);
        exit;
    }

    $infoRef = buscarReferenteParaAlta($nombre_referente);

    if (empty($infoRef["ok"]) || empty($infoRef["referente"]["id"])) {
        if (($infoRef["tipo_error"] ?? "") === "cliente_normal_no_referente") {
            $normalRef = $infoRef["cliente_normal"] ?? [];
            $normalRefId = (int)($normalRef["id"] ?? 0);
            $normalRefNombre = $normalRef["nombre"] ?? ($infoRef["nombre_encontrado"] ?? $nombre_referente);

            if ($normalRefId <= 0) {
                sendMessage($chat_id, "❌ He encontrado el cliente normal, pero no pude obtener su identificador. Pulsa /soporte.");
                http_response_code(200); exit;
            }

            if (!isset($states[$chat_id]) || !is_array($states[$chat_id])) $states[$chat_id] = [];
            $states[$chat_id]["mode"] = "referir_promocion_pendiente_admin";
            $states[$chat_id]["promover_referente_normal_id"] = $normalRefId;
            $states[$chat_id]["promover_referente_normal_nombre"] = $normalRefNombre;
            saveStates($state_file, $states);

            sendMessage($chat_id, "⏳ SOLICITUD ENVIADA AL ADMINISTRADOR

━━━━━━━━━━━━━━━━━━

👤 Usuario encontrado:
".$normalRefNombre."

Actualmente es cliente normal. Se ha solicitado convertirlo en Referente VIP.

No se realizará ningún cambio hasta que administración lo apruebe.

Cuando sea aprobado, el bot continuará automáticamente y te pedirá el nombre de la nueva cuenta.");

            $nombreTelegram = trim(($from["first_name"] ?? "")." ".($from["last_name"] ?? ""));
            $aliasTelegram = $from["username"] ?? "";
            sendInlineMessage($admin_id,
                "🔔 SOLICITUD DE NUEVO REFERENTE VIP

━━━━━━━━━━━━━━━━━━

👤 Cliente normal:
".$normalRefNombre."

📲 Telegram guardado:
".(($normalRef["telegram"] ?? "") !== "" ? "@".ltrim($normalRef["telegram"], "@") : "No disponible")."

☎️ Contacto:
".(($normalRef["contacto"] ?? "") !== "" ? $normalRef["contacto"] : "No disponible")."

👤 Solicitante Telegram:
".($nombreTelegram !== "" ? $nombreTelegram : "No disponible")."

📲 Alias solicitante:
".($aliasTelegram !== "" ? "@".$aliasTelegram : "No disponible")."

🆔 Chat ID:
".$chat_id."

━━━━━━━━━━━━━━━━━━

¿Deseas convertir este cliente normal en Referente VIP?",
                tecladoAdminPromoverNormalAReferente($chat_id));
        } else {
            sendMessage($chat_id, "❌ No encuentro ese referente.\n\n👤 Referente escrito:\n".$nombre_referente."\n\nDetalle:\n".($infoRef["error"] ?? "No encontrado")."\n\nPuedes escribir su nombre del panel, su usuario de Telegram o su contacto.\n\nVuelve a escribirlo o pulsa /soporte.");
        }
        http_response_code(200);
        exit;
    }

    $nivelRef = $infoRef["nivel"] ?? "cobre";

    if (!isset($states[$chat_id]) || !is_array($states[$chat_id])) {
        $states[$chat_id] = [];
    }

    $states[$chat_id]["mode"] = "referir_usuario";
    $states[$chat_id]["referir_context"] = [
        "alta_tipo" => "referido",
        "referente_id" => (int)($infoRef["referente"]["id"] ?? 0),
        "referente_normal_id" => (int)($infoRef["referente"]["normal_id"] ?? 0),
        "convertir_referente" => !empty($infoRef["convertir_a_referente"]),
        "referente_nombre" => $infoRef["referente"]["nombre"],
        "nivel_referente" => $nivelRef
    ];
    saveStates($state_file, $states);

    sendMessage($chat_id, "✅ REFERENTE ENCONTRADO\n\n━━━━━━━━━━━━━━━━━━\n\n👥 Referente:\n".$infoRef["referente"]["nombre"]."\n\n🏆 Nivel actual:\n".renovarNivelTxt($nivelRef)."\n\n💶 Tarifas disponibles:\n3 meses → ".renovarPrecioReferidos($nivelRef, 3)."€\n6 meses → ".renovarPrecioReferidos($nivelRef, 6)."€\n12 meses → ".renovarPrecioReferidos($nivelRef, 12)."€\n\n━━━━━━━━━━━━━━━━━━\n\nAhora escribe el nombre que quieres para tu nueva cuenta MDPRIME.\n\nEjemplo:\nMiguelTV");
    http_response_code(200);
    exit;
}

if ($user_state === "referir_usuario") {
    $usuario_nuevo = preg_replace('/\s+/', ' ', trim(str_replace(["\r","\n","\t"], ' ', $text)));
    if ($usuario_nuevo === "" || substr($usuario_nuevo,0,1) === "/") {
        sendMessage($chat_id, "👤 Escribe el nombre que quieres para tu nueva cuenta MDPRIME.");
        http_response_code(200); exit;
    }
    // En /referir damos prioridad absoluta a una coincidencia exacta en clientes_normales.
    // Así un nombre numérico (por ejemplo 111111111) no se confunde con teléfono,
    // Telegram o contacto de un referente existente.
    $existe = buscarClienteNormalExactoParaReferir($usuario_nuevo);
    if (!$existe) {
        $existe = consultarClienteApi($usuario_nuevo);
    }

    if (!empty($existe["ok"])) {
        if (!empty($existe["cliente_normal"])) {
            if (!isset($states[$chat_id]) || !is_array($states[$chat_id])) $states[$chat_id] = [];
            $normal = $existe["cliente_normal"];
            $states[$chat_id]["mode"] = "referir_conversion_pendiente_admin";
            $states[$chat_id]["referir_normal_id"] = (int)($normal["id"] ?? 0);
            $states[$chat_id]["referir_normal_nombre"] = $normal["nombre"] ?? $usuario_nuevo;
            saveStates($state_file, $states);
            $ctx = $states[$chat_id]["referir_context"] ?? [];

            sendMessage($chat_id, "⏳ SOLICITUD ENVIADA AL ADMINISTRADOR

━━━━━━━━━━━━━━━━━━

👤 Cuenta normal:
".($normal["nombre"] ?? $usuario_nuevo)."

👥 Referente solicitado:
".($ctx["referente_nombre"] ?? "No disponible")."

📅 Caducidad actual:
".($normal["caducidad"] ?? "Sin fecha")."

━━━━━━━━━━━━━━━━━━

La conversión solo puede aprobarla la administración.

No se realizará ningún cambio hasta que el administrador la acepte.");

            $nombreTelegram = trim(($from["first_name"] ?? "")." ".($from["last_name"] ?? ""));
            $aliasTelegram = $from["username"] ?? "";
            sendInlineMessage($admin_id,
                "🔔 SOLICITUD DE CONVERSIÓN

━━━━━━━━━━━━━━━━━━

👤 Cliente normal:
".($normal["nombre"] ?? $usuario_nuevo)."

👥 Referente solicitado:
".($ctx["referente_nombre"] ?? "No disponible")."

📅 Caducidad actual:
".($normal["caducidad"] ?? "Sin fecha")."

👤 Solicitante Telegram:
".($nombreTelegram !== "" ? $nombreTelegram : "No disponible")."

📲 Alias:
".($aliasTelegram !== "" ? "@".$aliasTelegram : "No disponible")."

🆔 Chat ID:
".$chat_id."

━━━━━━━━━━━━━━━━━━

Solo al aprobar se eliminará de clientes normales y se añadirá a los referidos del referente indicado.",
                tecladoAdminConvertirNormalAReferido($chat_id));
            http_response_code(200); exit;
        }
        sendMessage($chat_id, "⚠️ Ese usuario ya existe como referente o como referido.

Para esa cuenta debes usar /renovar.

Escribe otro nombre diferente.");
        http_response_code(200); exit;
    }
    pedirConfirmacionNombreProceso($state_file, $states, $chat_id, $usuario_nuevo, "nuevo");
    http_response_code(200); exit;
}


if ($user_state === "multi_nombre") {
    $u=trim($text); $u=preg_replace('/\s+/',' ',str_replace(["\r","\n","\t"]," ",$u));
    if($u==="" || substr($u,0,1)==="/"){ sendMessage($chat_id,"👤 Escribe un nombre válido para la cuenta."); http_response_code(200); exit; }
    $md=multiEstado($states,$chat_id); $norm=mdprimeNormalizarBusqueda($u);
    foreach(($md["usuarios"]??[]) as $i=>$ya){ if(isset($md["edit_index"]) && (int)$md["edit_index"]===$i)continue; if(mdprimeNormalizarBusqueda($ya)===$norm){ sendMessage($chat_id,"⚠️ Ese nombre ya está incluido en este mismo pedido. Escribe otro diferente."); http_response_code(200); exit; } }
    if(multiUsuarioExiste($u)){ sendMessage($chat_id,"⚠️ Ese usuario ya existe en el panel. Para esa cuenta debes usar /renovar.\n\nEscribe otro nombre para este plan multicuenta."); http_response_code(200); exit; }
    if(multiNombreReservado($states,$u,$chat_id)){ sendMessage($chat_id,"⚠️ Ese nombre ya está reservado en otra solicitud pendiente. Escribe otro diferente."); http_response_code(200); exit; }
    $md["nombre_pendiente"]=$u; multiGuardarEstado($state_file,$states,$chat_id,$md,"multi_confirmando_nombre");
    sendInlineMessage($chat_id,"⚠️ CONFIRMA EL NOMBRE\n\n👤 ".$u."\n\n¿Está escrito correctamente?",multiConfirmarNombreKeyboard());
    http_response_code(200); exit;
}

if ($user_state === "nuevo_usuario") {

    $usuario_nuevo = trim($text);
    $usuario_nuevo = str_replace(["\r", "\n", "\t"], " ", $usuario_nuevo);
    $usuario_nuevo = preg_replace('/\s+/', ' ', $usuario_nuevo);

    if ($usuario_nuevo === "" || substr($usuario_nuevo, 0, 1) === "/") {
        sendMessage($chat_id, "👤 Escribe el nombre de usuario que quieres crear.\n\nEjemplo:\nMiguelTV");
        http_response_code(200);
        exit;
    }

    $existe = consultarClienteApi($usuario_nuevo);
    if (!empty($existe["ok"])) {
        limpiarNuevoEstado($state_file, $states, $chat_id);
        sendMessage($chat_id, "⚠️ Ese usuario ya aparece en la base de datos.\n\nPara esa cuenta debes usar /renovar.\n\nSi quieres crear una cuenta nueva, escribe /nuevo y pon otro usuario.");
        http_response_code(200);
        exit;
    }

    pedirConfirmacionNombreProceso($state_file, $states, $chat_id, $usuario_nuevo, "nuevo");

    http_response_code(200);
    exit;
}

if ($user_state === "renovar") {

    $usuario_mdprime = trim($text);

    if ($usuario_mdprime === "" || substr($usuario_mdprime, 0, 1) === "/") {
        sendMessage($chat_id, "👤 Escribe el usuario MDPRIME que quieres renovar.");
        http_response_code(200);
        exit;
    }

    pedirConfirmacionNombreProceso($state_file, $states, $chat_id, $usuario_mdprime, "renovar");

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

    case "/avisoscaducidad":

        if ((string)$chat_id !== (string)$admin_id) {
            sendMessage($chat_id, "❌ Comando reservado para administración.");
            break;
        }

        $res = enviarAvisosCaducidadMdprime();

        $msg = "🔔 AVISOS DE CADUCIDAD

Versión:
".$bot_version."

Enviados:
".($res["enviados"] ?? 0)."

Errores:
".($res["errores"] ?? 0);

        if (!empty($res["detalle"])) {
            $msg .= "

Detalle:
".implode("
", array_slice($res["detalle"], 0, 20));
        }

        sendLongMessage($chat_id, $msg);
        break;

    case "/actualizarcomandos":
        if ((string)$chat_id !== (string)$admin_id) {
            sendMessage($chat_id, "❌ Comando exclusivo para administración.");
            break;
        }

        $resultadoComandos = configurarComandosTelegram();
        if (!empty($resultadoComandos["ok"])) {
            sendMessage($chat_id, "✅ Menú de comandos actualizado.

El menú global y el menú privado han sido regenerados.

Pulsa /start y cierra/abre el chat para refrescar Telegram.");
        } else {
            sendMessage($chat_id, "❌ No se pudo actualizar el menú de comandos de Telegram.");
        }
        break;

    case "/cancelar":
        resetUserProcessState($state_file, $states, $chat_id);
        sendMessage($chat_id, "✅ Proceso cancelado.

Has vuelto al menú principal.");
        break;

    case "/start":
        resetUserProcessState($state_file, $states, $chat_id);
        configurarComandosTelegram();
        mostrarMenuPrincipalV61($chat_id, $states);
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

            sendMessage($chat_id, "👤 Introduce tu usuario de P2P.

Puede ser:

• Tu nombre de referente
• El nombre del referido
• debes de estar en la base de datos
• si no estas contacta con @zeppplay


Ejemplo:
Canelobel");
        }

        break;

    case "/identificate":
    case "/cambiarusuario":

        setUserMode($state_file, $states, $chat_id, "esperando_usuario_mdprime", "/micuenta");

        sendMessage($chat_id, "👤 IDENTIFÍCATE

Introduce el nuevo usuario MDPRIME que quieres guardar.");

        break;

case "/comorenovar":

        sendMessage($chat_id, mensajeComoRenovar());
        break;

case "/renovar":

    if ($saved_usuario !== "") {
        pedirConfirmacionNombreProceso($state_file, $states, $chat_id, $saved_usuario, "renovar");
        break;
    }

    setUserMode($state_file, $states, $chat_id, "renovar");

    $nombre = trim(($update["message"]["from"]["first_name"] ?? "") . " " . ($update["message"]["from"]["last_name"] ?? ""));
    $usernameTelegram = $update["message"]["from"]["username"] ?? "";

    $texto = "🔄 RENOVACIÓN MDPRIME

";
    $texto .= "Introduce tu usuario de MDPRIME para comprobar tu tipo de cuenta.

";
    $texto .= "Ejemplo:
";
    $texto .= "Pepito44

";
    $texto .= "━━━━━━━━━━━━━━━━━━━━━━
";
    $texto .= "👤 Nombre Telegram: ".$nombre."
";

    if ($usernameTelegram != "") {
        $texto .= "📱 Usuario Telegram: @".$usernameTelegram."
";
    } else {
        $texto .= "📱 Usuario Telegram: (No disponible)
";
    }

    sendMessage($chat_id, $texto);

    break;
   

    case "/multicuenta":
        clearUserMode($state_file,$states,$chat_id);
        multiGuardarEstado($state_file,$states,$chat_id,[],"multi_cantidad");
        sendInlineMessage($chat_id,"💎 PLAN MULTICUENTA\n\nContrata 2 o 3 cuentas nuevas con un único pago y un único comprobante.\n\nEste paquete es para clientes normales y no aplica precios de referidos.\n\n¿Cuántos usuarios quieres contratar?",multiCantidadKeyboard());
        break;

    case "/multicuentas":
        if((string)$chat_id!==(string)$admin_id){ sendMessage($chat_id,"❌ Comando reservado para administración."); break; }
        $pend=$states["_multicuentas_pendientes"]??[];
        if(empty($pend)){ sendMessage($chat_id,"📋 No hay planes multicuenta pendientes."); break; }
        $txt="📋 MULTICUENTAS PENDIENTES\n";
        foreach($pend as $id=>$p){ $txt.="\n━━━━━━━━━━━━━━━━━━\n#".$id."\n👥 ".($p["cantidad"]??0)." usuarios · ".($p["meses"]??0)." meses\n💶 ".multiPrecio($p["cantidad"]??0,$p["meses"]??0)."€\n👤 ".implode(", ",$p["usuarios"]??[]); }
        sendLongMessage($chat_id,$txt,false);
        break;

    case "/referir":
        clearUserMode($state_file, $states, $chat_id);
        setUserMode($state_file, $states, $chat_id, "referir_referente");

        sendMessage($chat_id, "👥 UNIRME A UN REFERENTE\n\n━━━━━━━━━━━━━━━━━━\n\nEste proceso sirve para crear una cuenta nueva y guardarla dentro del grupo de la persona que te ha recomendado MDPRIME.\n\nPASO 1 DE 4\n\nEscribe el nombre exacto de tu referente, tal como aparece registrado en el panel.\n\nEjemplo:\nVictor\n\n⚠️ El bot comprobará el referente antes de continuar.");
        break;

    case "/nuevo":

        setUserMode($state_file, $states, $chat_id, "nuevo_usuario");

        sendMessage($chat_id, "🆕 CREAR CUENTA NUEVA MDPRIME\n\nEscribe cómo quieres que se llame tu usuario.\n\nEjemplo:\nMiguelTV\n\n⚠️ La contraseña será generada desde nuestro panel. No se puede elegir manualmente.\n\nLa cuenta NO se creará hasta que pagues, envíes el comprobante y el pago sea aprobado.");
        break;

    case "/pagar":

        sendMessage($chat_id, "ℹ️ El comando /pagar ha sido sustituido por /nuevo.\n\nPara crear una cuenta nueva usa:\n/nuevo");
        break;

    case "/soporte":

        clearUserMode($state_file, $states, $chat_id);
        sendInlineMessage($chat_id, soporteMenuTexto(), soporteMenuKeyboard());
        break;

    case "/agenda":

        $espera = sendMessage($chat_id, "⏳ Cargando agenda deportiva...", false);
        $espera_id = $espera["result"]["message_id"] ?? null;

        $json = getAgendaJsonCache();

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

    case "/optimizarmd":

        if ((string)$chat_id !== (string)$admin_id) {
            sendMessage($chat_id, "❌ Comando reservado para administración.");
            break;
        }

        $ok = optimizarIndicesRailway();

        sendMessage($chat_id, $ok ? "✅ Índices de Railway optimizados correctamente." : "❌ No se pudieron optimizar los índices.");
        break;

    case "/debugrefs":

        if ((string)$chat_id !== (string)$admin_id) {
            sendMessage($chat_id, "❌ Comando reservado para administración.");
            break;
        }

        try {
            $pdo = getRailwayPdo();
            $rows = $pdo->query("SELECT id, cliente_id, nombre, estado, fecha_alta, fecha_caducidad FROM referidos ORDER BY id DESC LIMIT 12")->fetchAll();

            $msg = "🧪 ÚLTIMOS REFERIDOS EN RAILWAY\n\n";

            foreach ($rows as $r) {
                $msg .= "#".$r["id"]." · ".$r["nombre"]."\n";
                $msg .= "cliente_id: ".$r["cliente_id"]." · estado: ".$r["estado"]."\n";
                $msg .= "alta: ".$r["fecha_alta"]." · caduca: ".$r["fecha_caducidad"]."\n";
                $msg .= "━━━━━━━━━━━━━━\n";
            }

            sendLongMessage($chat_id, $msg);

        } catch (Throwable $e) {
            sendMessage($chat_id, "❌ Error debugrefs:\n".$e->getMessage());
        }

        break;

    case "/debugmd":

        global $bot_version;

        $debug_usuario = $command_arg !== "" ? $command_arg : "Brandon10";
        $debug_data = consultarClienteApi($debug_usuario);

        $debug_msg = "🧪 DEBUG MDPRIME\n\n";
        $debug_msg .= "Versión bot:\n".$bot_version."\n\n";
        $debug_msg .= "Usuario prueba:\n".$debug_usuario."\n\n";
        $debug_msg .= "API ok:\n".(!empty($debug_data["ok"]) ? "SI" : "NO")."\n\n";
        $debug_msg .= "Tipo:\n".($debug_data["tipo"] ?? "Sin tipo")."\n\n";
        $debug_msg .= "Error:\n".($debug_data["error"] ?? "Sin error")."\n\n";

        if (!empty($debug_data["detalle"])) {
            $debug_msg .= "Detalle:\n".$debug_data["detalle"]."\n\n";
        }

        if (!empty($debug_data["http_code"])) {
            $debug_msg .= "HTTP:\n".$debug_data["http_code"]."\n\n";
        }

        if (!empty($debug_data["raw_inicio"])) {
            $debug_msg .= "Respuesta inicio:\n".$debug_data["raw_inicio"]."\n\n";
        }

        if (!empty($debug_data["referido"]["nombre"])) {
            $debug_msg .= "Referido encontrado:\n".$debug_data["referido"]["nombre"]."\n";
            $debug_msg .= "Caduca:\n".($debug_data["referido"]["caducidad"] ?? "Sin fecha")."\n";
        }

        if (!empty($debug_data["cliente"]["nombre"])) {
            $debug_msg .= "Referente encontrado:\n".$debug_data["cliente"]["nombre"]."\n";
        }

        sendMessage($chat_id, $debug_msg);
        break;

    case "/test":

        global $bot_version;
        sendMessage($chat_id, "allow_url_fopen: ".ini_get("allow_url_fopen")."\nVersión: ".$bot_version);
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
/nuevo
/referir
/soporte";

        sendMessage($chat_id, $msg);
        break;
}

http_response_code(200);
exit;

?>
