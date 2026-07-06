<?php
ini_set('log_errors', 1);
error_reporting(E_ALL);
// MDPRIME - Panel eventos deportivos desde Worker Cloudflare -> MARCA
// Sube este archivo a htdocs.
// IMPORTANTE: el Worker debe devolver el HTML completo de https://www.marca.com/programacion-tv.html

$WORKER_URL = 'https://quiet-waterfall-830d.zeppplay7.workers.dev/';
$CACHE_FILE = __DIR__ . '/cache_mdprime_marca_worker.html';
$CACHE_TIME = 60 * 15; // 15 minutos


// Compatibilidad para hostings donde mbstring no está activo.
function mdp_lower($s){ return function_exists('mb_strtolower') ? mb_strtolower($s, 'UTF-8') : strtolower($s); }
function mdp_upper($s){ return function_exists('mb_strtoupper') ? mb_strtoupper($s, 'UTF-8') : strtoupper($s); }
function mdp_strlen($s){ return function_exists('mb_strlen') ? mb_strlen($s, 'UTF-8') : strlen($s); }
function mdp_substr($s,$start,$len=null){
    if(function_exists('mb_substr')) return $len===null ? mb_substr($s,$start,null,'UTF-8') : mb_substr($s,$start,$len,'UTF-8');
    return $len===null ? substr($s,$start) : substr($s,$start,$len);
}
function mdp_strpos($haystack,$needle,$offset=0){
    if(function_exists('mb_strpos')) return mb_strpos($haystack,$needle,$offset,'UTF-8');
    return strpos($haystack,$needle,$offset);
}

function fetch_url_mdprime($url){
    $html = false;
    if(function_exists('curl_init')){
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 12);
        curl_setopt($ch, CURLOPT_TIMEOUT, 25);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 MDPRIME Panel');
        $html = curl_exec($ch);
        curl_close($ch);
    }
    if(!$html && ini_get('allow_url_fopen')){
        $ctx = stream_context_create(array('http'=>array('timeout'=>25,'header'=>"User-Agent: Mozilla/5.0 MDPRIME Panel\r\n")));
        $html = @file_get_contents($url, false, $ctx);
    }
    return $html ? $html : '';
}

function normalize_text_mdprime($html){
    // MARCA viene en iso-8859-15 muchas veces
    if(function_exists('mb_convert_encoding')){
        $html = @mb_convert_encoding($html, 'UTF-8', 'ISO-8859-15, ISO-8859-1, UTF-8');
    } else if(function_exists('iconv')){
        $converted = @iconv('ISO-8859-15', 'UTF-8//IGNORE', $html);
        if($converted) $html = $converted;
    }
    $html = preg_replace('/<script\b[^>]*>[\s\S]*?<\/script>/i', ' ', $html);
    $html = preg_replace('/<style\b[^>]*>[\s\S]*?<\/style>/i', ' ', $html);
    $html = preg_replace('/<br\s*\/?>/i', ' ', $html);
    $html = preg_replace('/<\/p>|<\/div>|<\/li>|<\/article>|<\/section>|<\/h\d>/i', ' ', $html);
    $text = strip_tags($html);
    $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $text = preg_replace('/\s+/u', ' ', $text);
    return trim($text);
}

function title_case_mdprime($s){
    $s = trim($s);
    if(function_exists('mb_convert_case')) return mb_convert_case($s, MB_CASE_TITLE, 'UTF-8');
    return ucwords(strtolower($s));
}

function extract_events_mdprime($html){
    $text = normalize_text_mdprime($html);
    $events = array();

    // Lista amplia: NO limita la fuente, solo ayuda a detectar el inicio de cada evento.
    $sports = array('Fútbol','Futbol','Baloncesto','Motor','Fórmula 1','Formula 1','F. Sala','Fútbol Sala','Futbol Sala','Tenis','Boxeo','Pádel','Padel','Ciclismo','Golf','Rugby','NHL','NFL','MMA','UFC','Waterpolo','Balonmano','Motos','MotoGP','H. Patines','Hockey Patines','Atletismo','Natación','Natacion','Voleibol','Ajedrez','Hípica','Hipica','Hockey Hierba','Juegos Olímpicos','Juegos Olimpicos','Paralímpicos','Paralimpicos','Triatlón','Triatlon','Vela','Turf','Otros deportes','Deportes Invierno','Deportes Aventura','eSports','Tenis de mesa','Toros');
    $sportsRe = implode('|', array_map(function($x){ return preg_quote($x, '/'); }, $sports));

    $dateRe = '/(?:Lunes|Martes|Mi[eé]rcoles|Miercoles|Jueves|Viernes|S[aá]bado|Sabado|Domingo)\s*\d{1,2}\s*de\s*[A-Za-zÁÉÍÓÚáéíóúñÑ]+\s*de\s*\d{4}/iu';
    preg_match_all($dateRe, $text, $dateMatches, PREG_OFFSET_CAPTURE);

    // Canales habituales. Si aparece un canal nuevo, el fallback toma palabras finales.
    $channelRe = '(?:La 1|La 2|Teledeporte|Telecinco|Cuatro|Antena 3|M\+ Vamos|M\+ Deportes(?: \d+)?|M\+ Liga de Campeones(?: \d+)?|M\+ LALIGA|M\+ Golf|Movistar Plus\+?|Movistar Primera Federación|Movistar Primera Federacion|DAZN(?: \d+)?|DAZN MUNDIAL|DAZN Baloncesto|Eurosport(?: \d+)?|Esport 3|Gol(?: Play)?|RTVE Play|Disney\+|ESPN\+?|NBA TV|NBA League Pass|NFL Network|NHL Network|UFC Fight Pass|Canal\+|Vamos)';

    // Cortamos cabecera y pie para evitar menús, publicidad y enlaces de footer.
    $work = $text;
    $posStart = stripos($work, 'Programación deportiva en TV');
    if($posStart === false) $posStart = stripos($work, 'Programacion deportiva en TV');
    if($posStart !== false) $work = substr($work, $posStart);
    $work = preg_split('/La última hora del deporte|La ultima hora del deporte|Resultados Ver más resultados|Resultados Ver mas resultados|Otras webs de Unidad Editorial/iu', $work)[0];

    // Volvemos a buscar fechas dentro del bloque útil.
    preg_match_all($dateRe, $work, $dm, PREG_OFFSET_CAPTURE);

    // Estrategia principal: detectar cualquier "DEPORTE HORA ..." y asignar la fecha anterior más cercana.
    $itemRe = '/\b('.$sportsRe.')\s+(\d{1,2}:\d{2})\s+([\s\S]*?)(?=\b(?:'.$sportsRe.')\s+\d{1,2}:\d{2}\b|(?:Lunes|Martes|Mi[eé]rcoles|Miercoles|Jueves|Viernes|S[aá]bado|Sabado|Domingo)\s*\d{1,2}\s*de\s*[A-Za-zÁÉÍÓÚáéíóúñÑ]+\s*de\s*\d{4}|$)/iu';
    preg_match_all($itemRe, $work, $im, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);

    foreach($im as $m){
        $sport = trim($m[1][0]);
        $time = trim($m[2][0]);
        $body = trim(preg_replace('/\s+/u',' ', $m[3][0]));
        $offset = $m[0][1];
        if($body==='') continue;

        $dateLabel = 'Sin fecha';
        if(!empty($dm[0])){
            foreach($dm[0] as $d){
                if($d[1] <= $offset) $dateLabel = trim($d[0]);
                else break;
            }
        }

        // Limpiar trozos de fecha colados al principio del cuerpo.
        $body = preg_replace($dateRe, ' ', $body);
        $body = trim(preg_replace('/\s+/u',' ', $body));
        if($body==='') continue;

        $channel = '';
        if(preg_match('/\b'.$channelRe.'\b\s*$/iu', $body, $cm)){
            $channel = trim($cm[0]);
            $body = trim(preg_replace('/\b'.preg_quote($channel,'/').'\b\s*$/iu','',$body));
        } else {
            // Fallback: varios canales están al final, tomar 1-4 palabras finales si parecen canal.
            $words = preg_split('/\s+/u', $body);
            for($take=4; $take>=1; $take--){
                if(count($words) <= $take) continue;
                $possible = implode(' ', array_slice($words, -$take));
                if(preg_match('/^(La|M\+|DAZN|Movistar|Eurosport|Teledeporte|Telecinco|Esport|Gol|RTVE|Cuatro|Antena|Disney|ESPN|NBA|NFL|NHL)/iu', $possible)){
                    $channel = $possible;
                    $body = trim(implode(' ', array_slice($words, 0, -$take)));
                    break;
                }
            }
        }

        $competition = '';
        $event = $body;
        if(preg_match('/^([A-ZÁÉÍÓÚÑ0-9ªº\.\- ]{3,55})\s+(.+)$/u', $body, $bm)){
            $competition = trim($bm[1]);
            $event = trim($bm[2]);
        }
        if($event==='') $event = $body;

        // Evitar falsos positivos de textos generales.
        if(mdp_strlen($event) < 3) continue;
        if(preg_match('/Compartir en|Hazte socio|Portada|Agenda TV|Cerrar/iu', $event)) continue;

        $events[] = array(
            'fecha' => $dateLabel,
            'hora' => $time,
            'deporte' => $sport,
            'competicion' => title_case_mdprime($competition),
            'evento' => $event,
            'canal' => $channel
        );
    }

    // Eliminar duplicados exactos.
    $unique = array(); $clean = array();
    foreach($events as $e){
        $key = $e['fecha'].'|'.$e['hora'].'|'.$e['deporte'].'|'.$e['evento'].'|'.$e['canal'];
        if(isset($unique[$key])) continue;
        $unique[$key] = true;
        $clean[] = $e;
    }

    return array('events'=>$clean, 'debug'=>mdp_substr($work, 0, 2600), 'text'=>$text);
}

$refresh = isset($_GET['refresh']);
$html = '';
$fromCache = false;
$cacheAge = file_exists($CACHE_FILE) ? (time()-filemtime($CACHE_FILE)) : null;
if(!$refresh && file_exists($CACHE_FILE) && $cacheAge < $CACHE_TIME){
    $html = file_get_contents($CACHE_FILE);
    $fromCache = true;
} else {
    $html = fetch_url_mdprime($WORKER_URL);
    if(strlen($html)>50000) @file_put_contents($CACHE_FILE, $html);
}
$nextAutoRefresh = 15 * 60;

$parsed = extract_events_mdprime($html);
$events = $parsed['events'];
$updated = date('Y-m-d H:i:s');
$days = array(); $sportsCount = array();
foreach($events as $e){ $days[$e['fecha']] = true; $sportsCount[$e['deporte']] = true; }

if(isset($_GET['json'])){
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(array(
        'ok'=>count($events)>0,
        'updated'=>$updated,
        'bytes'=>strlen($html),
        'cache'=>array('enabled'=>true,'minutes'=>15,'from_cache'=>$fromCache,'cache_age_seconds'=>$cacheAge),
        'events_count'=>count($events),
        'days'=>array_keys($days),
        'date_debug'=>array_keys($days),
        'events'=>$events,
        'debug_text'=> count($events)? '' : $parsed['debug']
    ), JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
    exit;
}



function logo_canal_mdprime($canal){
    $canal = trim((string)$canal);
    if($canal === '') return '';

    // Sube los logos oficiales a una carpeta llamada /logos/ en el mismo directorio del PHP.
    // Si un logo no existe, el texto del canal se seguirá mostrando igual.
    $logos = array(
        'Teledeporte' => 'logos/teledeporte.png',
        'RTVE Play' => 'logos/rtve-play.png',
        'La 1' => 'logos/la1.png',
        'La 2' => 'logos/la2.png',
        'Telecinco' => 'logos/telecinco.png',
        'Cuatro' => 'logos/cuatro.png',
        'Antena 3' => 'logos/antena3.png',
        'DAZN MUNDIAL' => 'logos/dazn-mundial.png',
        'DAZN Baloncesto' => 'logos/dazn-baloncesto.png',
        'DAZN 1' => 'logos/dazn-1.png',
        'DAZN 2' => 'logos/dazn-2.png',
        'DAZN 3' => 'logos/dazn-3.png',
        'DAZN 4' => 'logos/dazn-4.png',
        'DAZN' => 'logos/dazn.png',
        'M+ Liga de Campeones' => 'logos/mplus-liga-campeones.png',
        'M+ LALIGA' => 'logos/mplus-laliga.png',
        'M+ Deportes' => 'logos/mplus-deportes.png',
        'M+ Vamos' => 'logos/mplus-vamos.png',
        'M+ Golf' => 'logos/mplus-golf.png',
        'Movistar Primera Federación' => 'logos/movistar-primera-federacion.png',
        'Movistar Primera Federacion' => 'logos/movistar-primera-federacion.png',
        'Movistar Plus+' => 'logos/movistar-plus.png',
        'Movistar Plus' => 'logos/movistar-plus.png',
        'Eurosport 1' => 'logos/eurosport-1.png',
        'Eurosport 2' => 'logos/eurosport-2.png',
        'Eurosport' => 'logos/eurosport.png',
        'Esport 3' => 'logos/esport3.png',
        'Gol Play' => 'logos/gol-play.png',
        'Gol' => 'logos/gol.png',
        'Disney+' => 'logos/disney-plus.png',
        'ESPN+' => 'logos/espn-plus.png',
        'ESPN' => 'logos/espn.png',
        'NBA TV' => 'logos/nba-tv.png',
        'NBA League Pass' => 'logos/nba-league-pass.png',
        'NFL Network' => 'logos/nfl-network.png',
        'NHL Network' => 'logos/nhl-network.png',
        'UFC Fight Pass' => 'logos/ufc-fight-pass.png',
        'Canal+' => 'logos/canal-plus.png',
        'Vamos' => 'logos/vamos.png'
    );

    foreach($logos as $name => $path){
        if(stripos($canal, $name) !== false) return $path;
    }
    return '';
}

function canal_html_mdprime($canal){
    $canal = trim((string)$canal);
    if($canal === '') $canal = 'Canal por confirmar';
    $safeCanal = htmlspecialchars($canal, ENT_QUOTES, 'UTF-8');
    $logo = logo_canal_mdprime($canal);
    if($logo !== ''){
        $safeLogo = htmlspecialchars($logo, ENT_QUOTES, 'UTF-8');
        return '<span class="channel-logo-wrap"><img class="channel-logo" src="'.$safeLogo.'" alt="'.$safeCanal.'" loading="lazy" onerror="this.style.display=\'none\';this.parentElement.classList.add(\'no-logo\');"></span><span class="channel-name">'.$safeCanal.'</span>';
    }
    return '<span class="channel-tv">📺</span><span class="channel-name">'.$safeCanal.'</span>';
}

function icon_mdprime($sport){
    $x = mdp_lower($sport);
    if(strpos($x,'fútbol')!==false || strpos($x,'futbol')!==false || strpos($x,'sala')!==false) return '⚽';
    if(strpos($x,'baloncesto')!==false) return '🏀';
    if(strpos($x,'tenis')!==false || strpos($x,'pádel')!==false || strpos($x,'padel')!==false) return '🎾';
    if(strpos($x,'formula')!==false || strpos($x,'fórmula')!==false || strpos($x,'motor')!==false || strpos($x,'motos')!==false) return '🏎️';
    if(strpos($x,'boxeo')!==false || strpos($x,'ufc')!==false || strpos($x,'mma')!==false) return '🥊';
    if(strpos($x,'nhl')!==false || strpos($x,'hockey')!==false || strpos($x,'patines')!==false) return '🏒';
    if(strpos($x,'nfl')!==false || strpos($x,'rugby')!==false) return '🏈';
    if(strpos($x,'ciclismo')!==false) return '🚴';
    if(strpos($x,'golf')!==false) return '⛳';
    if(strpos($x,'waterpolo')!==false || strpos($x,'natación')!==false || strpos($x,'natacion')!==false) return '🏊';
    if(strpos($x,'balonmano')!==false || strpos($x,'voleibol')!==false) return '🏐';
    return '🏆';
}
$featuredWords = array('MUNDIAL','CAMPEONATO DEL MUNDO','FÓRMULA 1','FORMULA 1','MOTOGP','UFC','NBA','NHL','STANLEY CUP','CHAMPIONS','LIGA ENDESA','SUPERBIKE','LE MANS');
$featured = array();
foreach($events as $e){
    $hay = mdp_upper($e['competicion'].' '.$e['evento'].' '.$e['deporte']);
    foreach($featuredWords as $w){ if(mdp_strpos($hay,$w,0)!==false){ $featured[]=$e; break; } }
    if(count($featured)>=6) break;
}
$sportTotals = array();
foreach($events as $e){ $sportTotals[$e['deporte']] = ($sportTotals[$e['deporte']] ?? 0) + 1; }
arsort($sportTotals);
$sportsList = array_keys($sportTotals);
sort($sportsList, SORT_NATURAL | SORT_FLAG_CASE);
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>MDPRIME Eventos Deportivos PRO Auto</title>
<meta http-equiv="refresh" content="900">
<style>
:root{
  --bg:#030611;--panel:#0a1022;--card:#101936;--card2:#0d142b;--line:#26375f;
  --text:#f6f8ff;--muted:#aab8da;--cyan:#00e5ff;--blue:#287bff;--violet:#9b5cff;--pink:#ff3df2;--green:#37ffb2;--gold:#ffd166;
  --danger:#ff5470;
}
*{box-sizing:border-box}html{scroll-behavior:smooth}body{margin:0;color:var(--text);font-family:Inter,Segoe UI,Roboto,Arial,sans-serif;min-height:100vh;background:
 radial-gradient(circle at 10% -10%,rgba(0,229,255,.28),transparent 28%),
 radial-gradient(circle at 85% 5%,rgba(155,92,255,.28),transparent 30%),
 radial-gradient(circle at 50% 110%,rgba(255,61,242,.12),transparent 35%),
 linear-gradient(180deg,#050916 0%,#030611 100%);overflow-x:hidden}
body:before{content:"";position:fixed;inset:0;pointer-events:none;background-image:linear-gradient(rgba(255,255,255,.035) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.035) 1px,transparent 1px);background-size:42px 42px;mask-image:linear-gradient(to bottom,#000,transparent 85%)}
body:after{content:"";position:fixed;inset:-40%;pointer-events:none;background:conic-gradient(from 90deg,transparent,rgba(0,229,255,.12),transparent,rgba(155,92,255,.14),transparent);animation:spin 22s linear infinite;opacity:.45}@keyframes spin{to{transform:rotate(360deg)}}
.orbs{position:absolute;inset:0;overflow:hidden;pointer-events:none}.orb{position:absolute;font-size:64px;opacity:.10;filter:drop-shadow(0 0 22px rgba(0,229,255,.5));animation:floaty 8s ease-in-out infinite}.orb.o1{right:8%;top:12%;animation-delay:0s}.orb.o2{right:24%;bottom:14%;animation-delay:1.5s}.orb.o3{left:4%;bottom:10%;animation-delay:3s}.orb.o4{left:48%;top:6%;animation-delay:4s}@keyframes floaty{0%,100%{transform:translateY(0) rotate(-8deg)}50%{transform:translateY(-18px) rotate(8deg)}}
.featured{margin-top:18px;display:grid;grid-template-columns:repeat(3,1fr);gap:12px}.feat{position:relative;overflow:hidden;border:1px solid rgba(255,209,102,.28);border-radius:20px;background:linear-gradient(135deg,rgba(255,209,102,.13),rgba(0,229,255,.07));padding:14px;min-height:112px}.feat b{display:block;font-size:12px;color:#ffe5a3;text-transform:uppercase;letter-spacing:.08em}.feat .fmain{margin-top:8px;font-size:15px;font-weight:1000;line-height:1.22}.feat .fmeta{margin-top:8px;color:#b9c8ef;font-weight:800;font-size:12px}.channel-mini{display:flex;align-items:center;gap:7px}.channel-mini .channel-logo-wrap{width:24px;height:24px;border-radius:8px}.channel-mini .channel-logo{max-width:21px;max-height:21px}.sports-cloud{display:none}.sport-chip{display:none}.select-wrap{position:relative}.select-wrap:before{content:'FILTRAR POR DEPORTE';position:absolute;left:18px;top:8px;z-index:1;pointer-events:none;color:#7eeaff;font-size:10px;font-weight:1000;letter-spacing:.12em;text-transform:uppercase}.select-wrap select{height:66px;padding:25px 52px 12px 18px;font-size:22px;font-weight:1000;appearance:none;background-image:linear-gradient(45deg,transparent 50%,#cfe8ff 50%),linear-gradient(135deg,#cfe8ff 50%,transparent 50%);background-position:calc(100% - 28px) 56%,calc(100% - 19px) 56%;background-size:9px 9px,9px 9px;background-repeat:no-repeat}.shine{position:absolute;inset:0;background:linear-gradient(115deg,transparent 20%,rgba(255,255,255,.12) 48%,transparent 72%);transform:translateX(-120%);animation:shine 7s infinite}@keyframes shine{12%,100%{transform:translateX(120%)}}
.wrap{position:relative;z-index:1;max-width:1280px;margin:0 auto;padding:22px 16px 46px}.hero{position:relative;overflow:hidden;border:1px solid rgba(120,160,255,.28);background:linear-gradient(135deg,rgba(12,24,55,.92),rgba(21,12,44,.92));border-radius:34px;padding:28px;box-shadow:0 24px 70px rgba(0,0,0,.55),inset 0 1px 0 rgba(255,255,255,.08)}
.hero:before{content:"";position:absolute;inset:-2px;background:linear-gradient(120deg,transparent 10%,rgba(0,229,255,.18),transparent 35%,rgba(155,92,255,.2),transparent 68%);filter:blur(10px);opacity:.9}.hero>*{position:relative;z-index:1}.topline{display:flex;align-items:center;justify-content:space-between;gap:14px;flex-wrap:wrap}.brand-pill{display:inline-flex;align-items:center;gap:10px;border:1px solid rgba(0,229,255,.38);background:rgba(0,229,255,.08);color:#bfffff;border-radius:999px;padding:10px 14px;font-weight:1000;letter-spacing:.12em;font-size:12px;text-transform:uppercase}.live-dot{width:10px;height:10px;border-radius:50%;background:var(--green);box-shadow:0 0 18px var(--green)}.updated{color:#cbd7ff;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.1);border-radius:999px;padding:10px 14px;font-size:13px;font-weight:800}.title{font-size:clamp(42px,7vw,86px);line-height:.86;margin:24px 0 10px;font-weight:1000;letter-spacing:-.05em;text-transform:uppercase;text-shadow:0 0 35px rgba(0,229,255,.22)}.title span{background:linear-gradient(90deg,#fff,#bfefff,#d2c5ff);-webkit-background-clip:text;background-clip:text;color:transparent}.subtitle{max-width:880px;color:#c1cff3;font-size:16px;line-height:1.55}.hero-grid{display:grid;grid-template-columns:1.2fr .8fr;gap:18px;align-items:end;margin-top:22px}.stats{display:grid;grid-template-columns:repeat(4,1fr);gap:12px}.stat{position:relative;overflow:hidden;background:linear-gradient(180deg,rgba(20,32,66,.9),rgba(9,15,34,.94));border:1px solid rgba(105,136,205,.3);border-radius:22px;padding:17px;min-height:92px}.stat:after{content:"";position:absolute;right:-28px;top:-28px;width:85px;height:85px;border-radius:50%;background:rgba(0,229,255,.12);filter:blur(2px)}.num{font-size:32px;font-weight:1000;letter-spacing:-.03em}.label{color:#aab8da;font-size:12px;font-weight:900;text-transform:uppercase;letter-spacing:.08em}.source-card{position:relative;overflow:hidden;background:linear-gradient(135deg,rgba(0,229,255,.12),rgba(155,92,255,.18),rgba(255,61,242,.12));background-size:300% 300%;animation:mdprimeGlow 8s ease infinite;border:1px solid rgba(0,229,255,.35);border-radius:24px;padding:22px;box-shadow:0 0 30px rgba(0,229,255,.15)}.source-card:before{content:"";position:absolute;inset:-2px;background:linear-gradient(120deg,transparent,rgba(0,229,255,.18),transparent,rgba(155,92,255,.22),transparent);background-size:260% 260%;animation:mobileColorMove 6s linear infinite;opacity:.75;filter:blur(8px);pointer-events:none}.source-card>*{position:relative;z-index:1}.source-card b{display:block;color:#fff;font-size:28px;line-height:1.16;font-weight:1000;text-shadow:0 0 20px rgba(0,229,255,.35)}.source-card p{margin:12px 0 0;color:#e3ecff;line-height:1.62;font-size:15px;font-weight:800}@keyframes mdprimeGlow{0%{background-position:0% 50%}50%{background-position:100% 50%}100%{background-position:0% 50%}}.controls{position:sticky;top:0;z-index:5;margin:18px 0;padding:12px;background:rgba(5,8,20,.78);backdrop-filter:blur(16px);border:1px solid rgba(91,119,184,.3);border-radius:22px;display:grid;grid-template-columns:1fr 390px auto auto;gap:12px;box-shadow:0 16px 40px rgba(0,0,0,.28)}.controls input,.controls select{width:100%;background:#0a1022;border:1px solid rgba(108,140,212,.46);color:#fff;padding:18px;border-radius:18px;font-weight:900;font-size:18px;outline:none;box-shadow:inset 0 0 0 1px rgba(255,255,255,.03)}.controls input:focus,.controls select:focus{border-color:var(--cyan);box-shadow:0 0 0 3px rgba(0,229,255,.13)}.btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;border:0;background:linear-gradient(135deg,var(--blue),var(--violet));color:#fff;border-radius:18px;padding:18px 22px;font-size:16px;font-weight:1000;text-decoration:none;white-space:nowrap;box-shadow:0 10px 26px rgba(47,113,255,.25);transition:.2s transform,.2s filter}.btn:hover{transform:translateY(-2px);filter:brightness(1.12)}.btn.dark{background:#101936;border:1px solid rgba(255,255,255,.14);box-shadow:none}.json-hidden{display:none!important}.day-tabs{display:flex;gap:10px;flex-wrap:wrap;margin:8px 0 18px}.tab{border:1px solid rgba(111,145,218,.28);background:rgba(14,23,49,.78);color:#dfe8ff;border-radius:16px;padding:12px 14px;font-weight:1000;text-decoration:none}.tab small{display:block;color:#8fa2ce;font-size:11px}.day{position:relative;margin:18px 0 26px;border:1px solid rgba(96,128,198,.30);border-radius:28px;overflow:hidden;background:linear-gradient(180deg,rgba(13,20,43,.9),rgba(7,12,27,.92));box-shadow:0 20px 60px rgba(0,0,0,.35)}.day-head{display:flex;justify-content:space-between;align-items:center;gap:14px;padding:20px 22px;background:linear-gradient(90deg,rgba(0,229,255,.12),rgba(155,92,255,.11));border-bottom:1px solid rgba(112,143,213,.28)}.day-title{margin:0;font-size:24px;font-weight:1000}.day-count{border:1px solid rgba(0,229,255,.38);color:#c9ffff;background:rgba(0,229,255,.08);border-radius:999px;padding:8px 12px;font-weight:1000}.cards{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px;padding:16px}.event-card{position:relative;overflow:hidden;border:1px solid rgba(106,139,212,.22);background:linear-gradient(135deg,rgba(18,28,60,.95),rgba(8,13,29,.96));border-radius:22px;padding:16px;display:grid;grid-template-columns:84px 1fr;gap:14px;min-height:118px;transition:.18s transform,.18s border-color,.18s background}.event-card:hover{transform:translateY(-2px);border-color:rgba(0,229,255,.55);background:linear-gradient(135deg,rgba(22,36,78,.98),rgba(11,17,38,.98))}.event-card:before{content:"";position:absolute;left:0;top:0;bottom:0;width:4px;background:linear-gradient(var(--cyan),var(--violet))}.timebox{align-self:start;text-align:center;background:rgba(0,229,255,.08);border:1px solid rgba(0,229,255,.22);border-radius:18px;padding:13px 8px}.time{font-size:22px;font-weight:1000;letter-spacing:-.03em}.time-label{color:#90a4d2;font-size:10px;font-weight:1000;text-transform:uppercase;letter-spacing:.12em}.sport-row{display:flex;gap:8px;align-items:center;flex-wrap:wrap;margin-bottom:8px}.sport{font-weight:1000;color:#fff;background:rgba(155,92,255,.16);border:1px solid rgba(155,92,255,.28);border-radius:999px;padding:6px 10px;font-size:12px;text-transform:uppercase;letter-spacing:.06em}.comp{color:#9fb2de;font-size:12px;font-weight:900;text-transform:uppercase;letter-spacing:.08em}.event{font-size:18px;line-height:1.23;font-weight:1000;margin:3px 0 12px}.channel{display:inline-flex;align-items:center;gap:7px;color:#dce7ff;background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.12);border-radius:12px;padding:8px 10px;font-weight:900;font-size:13px}.channel-logo-wrap{display:inline-flex;align-items:center;justify-content:center;width:28px;height:28px;border-radius:9px;background:rgba(255,255,255,.10);border:1px solid rgba(255,255,255,.12);overflow:hidden;flex:0 0 auto}.channel-logo{max-width:24px;max-height:24px;object-fit:contain;display:block}.channel-logo-wrap.no-logo{display:none}.channel-name{display:inline-flex;align-items:center;line-height:1.15}.channel-tv{font-size:15px;filter:drop-shadow(0 0 6px rgba(0,229,255,.25))}.empty{padding:24px;color:#b8c6ec}.warn{margin-top:16px;background:rgba(255,209,102,.12);border:1px solid rgba(255,209,102,.35);color:#ffe6a0;padding:14px;border-radius:16px;font-weight:800}.footer{text-align:center;color:#8293bd;font-size:12px;margin:26px}.hide{display:none!important}.floating-count{position:fixed;right:18px;bottom:18px;z-index:10;background:linear-gradient(135deg,#101936,#1d2a56);border:1px solid rgba(0,229,255,.35);box-shadow:0 18px 40px rgba(0,0,0,.45);border-radius:18px;padding:12px 14px;font-weight:1000;color:#eaf7ff}.table-fallback{display:none}

.day-select-wrap{margin:8px 0 18px;position:relative;z-index:4}.day-select-box{max-width:520px}.day-select-box:before{content:'FILTRAR POR DÍA';left:18px;top:8px}.day-select-box select{width:100%;height:66px;border-radius:18px;border:1px solid rgba(0,229,255,.42);color:#fff;background:
 linear-gradient(135deg,rgba(0,229,255,.22),rgba(155,92,255,.26)),
 linear-gradient(180deg,#101936,#0a1022);box-shadow:0 14px 34px rgba(0,0,0,.28),0 0 24px rgba(0,229,255,.12);animation:selectGlow 5.5s ease-in-out infinite}.day-select-box select option{background:#0a1022;color:#fff}.day-select-box:after{content:"";position:absolute;inset:-2px;border-radius:20px;pointer-events:none;background:linear-gradient(120deg,transparent,rgba(0,229,255,.28),transparent,rgba(155,92,255,.30),transparent);background-size:240% 240%;animation:mobileColorMove 6s linear infinite;filter:blur(7px);opacity:.55;z-index:-1}@keyframes selectGlow{0%,100%{box-shadow:0 14px 34px rgba(0,0,0,.28),0 0 22px rgba(0,229,255,.16)}50%{box-shadow:0 14px 34px rgba(0,0,0,.28),0 0 32px rgba(155,92,255,.25)}}@keyframes mobileColorMove{0%{background-position:0% 50%;transform:rotate(0deg)}50%{background-position:100% 50%;transform:rotate(.5deg)}100%{background-position:0% 50%;transform:rotate(0deg)}}

@media(max-width:980px){.hero-grid{grid-template-columns:1fr}.stats{grid-template-columns:repeat(2,1fr)}.controls{grid-template-columns:1fr 1fr}.cards{grid-template-columns:1fr}.featured{grid-template-columns:1fr}}
@media(max-width:620px){body:after{opacity:.75;animation:spin 12s linear infinite}.hero:before{animation:mobileColorMove 6s linear infinite}.day:before{content:"";position:absolute;inset:-1px;pointer-events:none;background:linear-gradient(120deg,transparent,rgba(0,229,255,.13),transparent,rgba(155,92,255,.14),transparent);background-size:220% 220%;animation:mobileColorMove 7s linear infinite}.wrap{padding:12px}.hero{border-radius:24px;padding:20px}.title{font-size:42px}.controls{position:relative;grid-template-columns:1fr}.cards{padding:10px}.event-card{grid-template-columns:72px 1fr;padding:13px}.time{font-size:19px}.event{font-size:16px}.day-head{padding:16px}.floating-count{left:12px;right:12px;text-align:center}}


/* === MDPRIME PRO: cabeceras de día premium === */
.day{border-radius:30px;box-shadow:0 24px 70px rgba(0,0,0,.42),0 0 34px rgba(0,229,255,.08)}
.day-head{
  position:relative;
  isolation:isolate;
  overflow:hidden;
  display:flex;
  justify-content:space-between;
  align-items:center;
  gap:14px;
  padding:22px 24px;
  min-height:86px;
  background:
    radial-gradient(circle at 12% 15%,rgba(0,229,255,.24),transparent 28%),
    radial-gradient(circle at 86% 10%,rgba(155,92,255,.28),transparent 32%),
    linear-gradient(135deg,rgba(8,31,62,.98),rgba(21,18,62,.96) 48%,rgba(10,16,34,.98));
  border-bottom:1px solid rgba(0,229,255,.28);
  box-shadow:inset 0 1px 0 rgba(255,255,255,.10),inset 0 -1px 0 rgba(155,92,255,.18),0 12px 30px rgba(0,0,0,.25);
}
.day-head:before{
  content:"";
  position:absolute;
  inset:-80%;
  z-index:-2;
  background:conic-gradient(from 90deg,transparent,rgba(0,229,255,.20),transparent 34%,rgba(155,92,255,.24),transparent 70%,rgba(255,61,242,.12),transparent);
  animation:dayHeaderSpin 16s linear infinite;
  opacity:.75;
}
.day-head:after{
  content:"";
  position:absolute;
  inset:0;
  z-index:-1;
  background:linear-gradient(115deg,transparent 0%,rgba(255,255,255,.10) 44%,transparent 68%);
  transform:translateX(-120%);
  animation:dayHeaderShine 6.5s ease-in-out infinite;
}
@keyframes dayHeaderSpin{to{transform:rotate(360deg)}}
@keyframes dayHeaderShine{0%,18%{transform:translateX(-120%)}45%,100%{transform:translateX(120%)}}
.day-title{
  display:flex;
  align-items:center;
  gap:12px;
  margin:0;
  font-size:clamp(22px,2.5vw,34px);
  line-height:1.05;
  font-weight:1000;
  letter-spacing:-.035em;
  color:#fff;
  text-shadow:0 0 18px rgba(0,229,255,.45),0 0 32px rgba(155,92,255,.28);
}
.day-title:before{
  content:"📅";
  display:inline-grid;
  place-items:center;
  width:46px;
  height:46px;
  border-radius:16px;
  background:linear-gradient(135deg,rgba(0,229,255,.22),rgba(155,92,255,.24));
  border:1px solid rgba(255,255,255,.18);
  box-shadow:0 0 24px rgba(0,229,255,.22),inset 0 1px 0 rgba(255,255,255,.12);
  font-size:23px;
}
.day-title{font-size:0!important}
.day-title::after{
  content:attr(data-title);
  font-size:clamp(22px,2.5vw,34px);
}
.day-count{
  position:relative;
  overflow:hidden;
  flex:0 0 auto;
  border:1px solid rgba(0,229,255,.55);
  color:#fff;
  background:linear-gradient(135deg,rgba(0,229,255,.96),rgba(40,123,255,.94),rgba(155,92,255,.96));
  background-size:220% 220%;
  animation:dayBadgeMove 4.5s ease infinite;
  border-radius:999px;
  padding:11px 18px;
  font-weight:1000;
  font-size:15px;
  box-shadow:0 0 22px rgba(0,229,255,.36),0 0 30px rgba(155,92,255,.20),inset 0 1px 0 rgba(255,255,255,.22);
  text-shadow:0 1px 8px rgba(0,0,0,.35);
}
.day-count:before{
  content:"";
  position:absolute;
  inset:0;
  background:linear-gradient(115deg,transparent,rgba(255,255,255,.26),transparent);
  transform:translateX(-120%);
  animation:dayBadgeShine 3.8s ease-in-out infinite;
}
@keyframes dayBadgeMove{0%,100%{background-position:0% 50%}50%{background-position:100% 50%}}
@keyframes dayBadgeShine{0%,28%{transform:translateX(-120%)}62%,100%{transform:translateX(120%)}}
@media(max-width:620px){
  .day-head{padding:18px 16px;min-height:78px;border-radius:0;background-size:260% 260%;animation:mobileDayPulse 6s ease-in-out infinite}
  .day-title:before{width:40px;height:40px;border-radius:14px;font-size:20px}
  .day-title::after{font-size:22px;line-height:1.1}
  .day-count{font-size:13px;padding:9px 13px}
}
@keyframes mobileDayPulse{0%,100%{filter:saturate(1)}50%{filter:saturate(1.25) brightness(1.06)}}

</style>
</head>
<body>
<div class="wrap">
<section class="hero">
  <div class="orbs"><div class="orb o1">⚽</div><div class="orb o2">🏀</div><div class="orb o3">🏎️</div></div><div class="shine"></div>
  <div class="topline">
    <div class="brand-pill"><span class="live-dot"></span> PANEL AUTOMÁTICO · HORA ESPAÑA</div>
    <div class="updated">⏱ <?=$updated?> · <?= $fromCache ? "Caché activo" : "Actualizado ahora" ?></div>
  </div>
  <div class="title"><span>MDPRIME</span><br><span>THE BEST STREAM</span></div>
  <div class="subtitle"></div>
  <div class="hero-grid">
    <div class="stats">
      <div class="stat"><div class="num"><?=count($events)?></div><div class="label">Eventos</div></div>
      <div class="stat"><div class="num"><?=count($days)?></div><div class="label">Días</div></div>
      <div class="stat"><div class="num"><?=count($sportsCount)?></div><div class="label">Deportes</div></div>
      <div class="stat"><div class="num" id="visibleNum"><?=count($events)?></div><div class="label">Visibles</div></div>
    </div>
    <div class="source-card"><p>Si quieres acceder a todos estos eventos, puedes hacerlo contratando nuestros servicios.<br><br>Ponte en contacto con nosotros a través de nuestro grupo y canal oficial para más información.</p></div>
  </div>
  <?php if(count($featured)): ?>
  <div class="featured">
    <?php foreach($featured as $f): ?>
      <div class="feat"><b>🔥 Destacado · <?=htmlspecialchars($f['hora'])?> · <?=htmlspecialchars($f['deporte'])?></b><div class="fmain"><?=htmlspecialchars($f['evento'])?></div><div class="fmeta channel-mini"><?=canal_html_mdprime($f['canal'])?> · <?=htmlspecialchars($f['fecha'])?></div></div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <?php if(!count($events)): ?><div class="warn">⚠️ No se han detectado eventos. Abre esta misma URL con <b>?json=1</b> para ver el diagnóstico.</div><?php endif; ?>
</section>

<div class="controls">
  <input id="q" placeholder="Buscar evento, competición, canal o deporte..." oninput="filterRows()">
  <div class="select-wrap"><select id="sport" onchange="filterRows()"><option value="">Todos los deportes</option><?php foreach($sportsList as $s): ?><option value="<?=htmlspecialchars(mdp_lower($s))?>"><?=htmlspecialchars($s)?> (<?=$sportTotals[$s] ?? 0?>)</option><?php endforeach; ?></select></div>
  <a class="btn" href="?refresh=1">🔄 Actualizar ahora</a>
  <a class="btn dark json-hidden" href="?json=1" aria-hidden="true" tabindex="-1">JSON</a>
</div>

<div class="day-select-wrap">
  <div class="select-wrap day-select-box">
    <select id="dayFilter" onchange="filterRows()">
      <option value="">Todos los días</option>
      <?php foreach(array_keys($days) as $day): $cnt=0; foreach($events as $e){ if($e['fecha']===$day) $cnt++; } ?>
        <option value="<?=md5($day)?>"><?=htmlspecialchars($day)?> · <?=$cnt?> eventos</option>
      <?php endforeach; ?>
    </select>
  </div>
</div>

<?php foreach(array_keys($days) as $day): $cnt=0; foreach($events as $e){ if($e['fecha']===$day) $cnt++; } ?>
<section class="day" id="<?=md5($day)?>">
  <div class="day-head"><h2 class="day-title" data-title="<?=htmlspecialchars($day)?>"><?=htmlspecialchars($day)?></h2><div class="day-count"><?=$cnt?> eventos</div></div>
  <div class="cards">
  <?php foreach($events as $e): if($e['fecha']!==$day) continue; ?>
    <article class="event-card row" data-sport="<?=htmlspecialchars(mdp_lower($e['deporte']))?>" data-search="<?=htmlspecialchars(mdp_lower($e['hora'].' '.$e['deporte'].' '.$e['competicion'].' '.$e['evento'].' '.$e['canal'].' '.$e['fecha']))?>">
      <div class="timebox"><div class="time"><?=htmlspecialchars($e['hora'])?></div><div class="time-label">España</div></div>
      <div>
        <div class="sport-row"><span class="sport"><?=icon_mdprime($e['deporte'])?> <?=htmlspecialchars($e['deporte'])?></span><?php if(trim($e['competicion'])!==''): ?><span class="comp"><?=htmlspecialchars($e['competicion'])?></span><?php endif; ?></div>
        <div class="event"><?=htmlspecialchars($e['evento'])?></div>
        <div class="channel"><?=canal_html_mdprime($e['canal'])?></div>
      </div>
    </article>
  <?php endforeach; ?>
  </div>
</section>
<?php endforeach; ?>

<div class="floating-count"><span id="floatNum"><?=count($events)?></span> eventos visibles</div>

<div class="footer">🔄 Actualización automática cada 15 minutos · <span id="countdown">15:00</span> para la próxima recarga · Caché optimizada para InfinityFree + Cloudflare Worker</div>
<script>
(function(){
  var total=900;
  var el=document.getElementById('countdown');
  function tick(){
    var m=Math.floor(total/60);
    var s=total%60;
    if(el) el.textContent=String(m).padStart(2,'0')+':'+String(s).padStart(2,'0');
    if(total<=0){ location.href=location.pathname; return; }
    total--;
  }
  tick();
  setInterval(tick,1000);
})();
</script>

</div>
<script>
function filterRows(){
  let q=(document.getElementById('q').value||'').toLowerCase();
  let s=(document.getElementById('sport').value||'').toLowerCase();
  let dayId=(document.getElementById('dayFilter')?.value||'');
  let visible=0;

  document.querySelectorAll('.row').forEach(r=>{
    let ok=(!q||r.dataset.search.includes(q))&&(!s||r.dataset.sport===s);
    r.classList.toggle('hide',!ok);
  });

  document.querySelectorAll('.day').forEach(d=>{
    let any=[...d.querySelectorAll('.row')].some(r=>!r.classList.contains('hide'));
    let showDay=!dayId || d.id===dayId;
    d.classList.toggle('hide',!any || !showDay);
    if(any && showDay){
      visible += [...d.querySelectorAll('.row')].filter(r=>!r.classList.contains('hide')).length;
    }
  });

  document.getElementById('visibleNum').textContent=visible;
  document.getElementById('floatNum').textContent=visible;
}
</script>
</body>
</html>
