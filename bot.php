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
$bot_version = "MDPRIME-BOT-BUSQUEDA-REFERIDOS-FIX-20260708-24";

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
        "text" => $text,
        "disable_notification" => ((string)$chat_id !== (string)abs((int)$chat_id))
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

function editMessageText($chat_id, $message_id, $text, $reply_markup = null) {
    $data = [
        "chat_id" => $chat_id,
        "message_id" => $message_id,
        "text" => $text
    ];

    if ($reply_markup) {
        $data["reply_markup"] = json_encode($reply_markup);
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
    $usuario_like = "%".$usuario."%";

    try {
        $cols = $pdo->query("SHOW COLUMNS FROM referidos")->fetchAll();
        $text_cols = [];

        foreach ($cols as $col) {
            $field = $col["Field"] ?? "";
            $type = strtolower($col["Type"] ?? "");

            if ($field !== "" && (
                strpos($type, "char") !== false ||
                strpos($type, "text") !== false ||
                strpos($type, "varchar") !== false
            )) {
                $text_cols[] = $field;
            }
        }

        if (empty($text_cols)) {
            return null;
        }

        $where = [];
        $params = [];

        foreach ($text_cols as $field) {
            $safe = str_replace("`", "", $field);

            $where[] = "LOWER(TRIM(r.`".$safe."`)) = LOWER(TRIM(?))";
            $params[] = $usuario;

            $where[] = "REPLACE(LOWER(TRIM(r.`".$safe."`)), ' ', '') = REPLACE(LOWER(TRIM(?)), ' ', '')";
            $params[] = $usuario;

            $where[] = "LOWER(TRIM(r.`".$safe."`)) LIKE LOWER(TRIM(?))";
            $params[] = $usuario_like;
        }

        $nombre_expr = "r.nombre";
        if (!in_array("nombre", $text_cols, true)) {
            $nombre_expr = "r.`".$text_cols[0]."`";
        }

        $sql = "
            SELECT 
                r.*,
                ".$nombre_expr." AS nombre_match_v24,
                c.id AS referente_id,
                c.nombre AS referente_nombre,
                c.telegram AS referente_telegram,
                c.contacto AS referente_contacto
            FROM referidos r
            INNER JOIN clientes c ON c.id = r.cliente_id
            WHERE ".implode(" OR ", $where)."
            ORDER BY 
                CASE 
                    WHEN r.estado='Activo' AND (r.fecha_caducidad IS NULL OR r.fecha_caducidad >= CURDATE())
                    THEN 0 ELSE 1
                END,
                r.fecha_caducidad DESC,
                r.id DESC
            LIMIT 1
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $ref = $stmt->fetch();

        if ($ref && empty($ref["nombre"]) && !empty($ref["nombre_match_v24"])) {
            $ref["nombre"] = $ref["nombre_match_v24"];
        }

        return $ref ?: null;

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
                   OR LOWER(TRIM(r.nombre)) LIKE LOWER(TRIM(?))
                ORDER BY 
                    CASE 
                        WHEN r.estado='Activo' AND (r.fecha_caducidad IS NULL OR r.fecha_caducidad >= CURDATE())
                        THEN 0 ELSE 1
                    END,
                    r.fecha_caducidad DESC,
                    r.id DESC
                LIMIT 1
            ");
            $stmt->execute([$usuario, $usuario, $usuario_like]);
            $referido = $stmt->fetch();
        }

        if ($referido) {
            return construirRespuestaReferido($referido);
        }

        // Fallback V24: búsqueda robusta por todas las columnas de texto de referidos.
        // Esto corrige referidos creados por el panel que puedan quedar guardados con otro formato/campo.
        $referido_flexible = buscarReferidoFlexibleV24($pdo, $usuario);

        if ($referido_flexible) {
            return construirRespuestaReferido($referido_flexible);
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

    if (!empty($datos["ok"]) && !empty($datos["cliente"])) {
        $nombre_encontrado = $datos["cliente"]["nombre"] ?? $usuario_mdprime;
        $caduca = $datos["resumen"]["proxima_caducidad"] ?? "Sin fecha";
        $dias = fmtDias($datos["resumen"]["dias_proxima_caducidad"] ?? null);
        $nivel_actual = renovarNivelKeyDesdeTexto($datos["nivel"]["actual"] ?? "");
        $referente_nombre = $nombre_encontrado;
    } elseif (!empty($datos["ok"]) && !empty($datos["referido"])) {
        $nombre_encontrado = $datos["referido"]["nombre"] ?? $usuario_mdprime;
        $caduca = $datos["referido"]["caducidad"] ?? "Sin fecha";
        $dias = fmtDias($datos["referido"]["dias"] ?? null);

        $referente_nombre = $datos["referente"]["nombre"] ?? "";
        $referente_id = $datos["referente"]["id"] ?? 0;

        if ($referente_id) {
            $info_nivel = obtenerNivelReferentePorId($referente_id);
            $nivel_actual = $info_nivel["nivel"] ?? "";
        }
    }

    $es_vip = ($nivel_actual !== "");

    $ren_data = [
        "usuario" => $usuario_mdprime,
        "usuario_encontrado" => $nombre_encontrado,
        "referente_nombre" => $referente_nombre,
        "es_vip" => $es_vip,
        "nivel_actual" => $nivel_actual,
        "caduca" => $caduca,
        "dias" => $dias
    ];

    guardarRenovarEstado($state_file, $states, $chat_id, $ren_data);

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
        $msg = "ℹ️ Tu usuario no pertenece al programa de Referidos VIP.

La renovación se realizará con tarifa estándar.

━━━━━━━━━━━━━━━━━━

👤 Usuario escrito:
".$usuario_mdprime."

💶 Tarifa estándar:
3 meses → ".renovarPrecioNormal(3)."€
6 meses → ".renovarPrecioNormal(6)."€
12 meses → ".renovarPrecioNormal(12)."€

Selecciona la duración:";
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

/* =========================
   RECIBIR UPDATE
========================= */

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
        guardarRenovarEstado($state_file, $states, $chat_id, $ren_data);

        if (!empty($ren_data["es_vip"])) {
            editMessageText(
                $chat_id,
                $message_id,
                "🏆 REFERIDOS VIP\n\nSelecciona tu nivel de referidos:",
                renovarNivelKeyboard($ren_data["nivel_actual"] ?? "")
            );
        } else {
            editMessageText(
                $chat_id,
                $message_id,
                renovarResumenTexto($ren_data),
                renovarConfirmarKeyboard()
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

// Si estamos esperando el comprobante de renovación, aceptar captura/foto/documento.
if ($user_state === "esperando_comprobante_renovacion") {
    $tiene_comprobante = isset($update["message"]["photo"]) || isset($update["message"]["document"]);

    if ($tiene_comprobante && $message_id) {
        $comp_data = obtenerComprobanteRenovacionEstado($states, $chat_id);
        $from_user = $update["message"]["from"] ?? [];

        sendMessage($admin_id, mensajeAdminComprobanteRenovacion($chat_id, $from_user, $comp_data), false);
        forwardMessage($admin_id, $chat_id, $message_id);

        limpiarComprobanteRenovacionEstado($state_file, $states, $chat_id);

        sendMessage(
            $chat_id,
            "✅ Comprobante recibido correctamente.\n\nNuestro equipo revisará el pago y contactará contigo lo antes posible."
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
$private_group_commands = ["/micuenta", "/caducidad", "/misreferidos", "/cambiarusuario", "/renovar", "/soporte"];

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

    $usuario_mdprime = trim($text);
    iniciarRenovacionConUsuario($state_file, $states, $chat_id, $usuario_mdprime);

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

    case "/cambiarusuario":

        setUserMode($state_file, $states, $chat_id, "esperando_usuario_mdprime", "/micuenta");

        sendMessage($chat_id, "🔄 CAMBIAR USUARIO

Introduce el nuevo usuario MDPRIME que quieres guardar.");

        break;

case "/renovar":

    if ($saved_usuario !== "") {
        iniciarRenovacionConUsuario($state_file, $states, $chat_id, $saved_usuario);
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
/pagar
/soporte";

        sendMessage($chat_id, $msg);
        break;
}

http_response_code(200);
exit;

?>
