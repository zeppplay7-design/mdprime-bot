<?php
/* MDPRIME PANEL V10 - BUSCADOR CLIENTES NORMALES - RENDER + RAILWAY */
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Zona horaria fija para que las caducidades se calculen con fecha real de España
date_default_timezone_set('Europe/Madrid');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

/* ===== PROTECCIÓN DE ACCESO MDPRIME - SOLO CONTRASEÑA ===== */
session_start();
$panel_password = 'Aa251171'; // Cambia aquí la contraseña si quieres otra

if (isset($_GET['logout'])) {
  session_destroy();
  header('Location: '.$_SERVER['PHP_SELF']);
  exit;
}

$login_error = '';
if (isset($_POST['panel_login_password'])) {
  if (hash_equals($panel_password, (string)$_POST['panel_login_password'])) {
    $_SESSION['mdprime_panel_auth'] = true;
    header('Location: '.$_SERVER['PHP_SELF']);
    exit;
  } else {
    $login_error = 'Contraseña incorrecta.';
  }
}

if (empty($_SESSION['mdprime_panel_auth'])) {
  $err = $login_error ? '<div class="error">'.htmlspecialchars($login_error, ENT_QUOTES, 'UTF-8').'</div>' : '';
  echo '<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Acceso MDPRIME</title><style>
  *{box-sizing:border-box}body{margin:0;min-height:100vh;display:grid;place-items:center;background:radial-gradient(circle at 20% 0%,rgba(245,197,66,.20),transparent 28%),linear-gradient(135deg,#030405,#071018 55%,#030405);font-family:Inter,system-ui,Segoe UI,Arial;color:#fff;padding:18px}.login{width:min(430px,100%);border:1px solid rgba(245,197,66,.35);border-radius:26px;background:linear-gradient(180deg,rgba(17,24,32,.94),rgba(4,8,12,.96));box-shadow:0 24px 70px rgba(0,0,0,.65);padding:28px;text-align:center}.brand{font-size:42px;font-weight:1000;color:#f5c542;letter-spacing:-1px;margin-bottom:8px}.sub{color:#aeb7c4;font-weight:800;text-transform:uppercase;letter-spacing:1.5px;font-size:12px;margin-bottom:24px}.lock{font-size:54px;margin-bottom:10px}input{width:100%;background:#050914;color:#fff;border:1px solid rgba(255,255,255,.14);border-radius:16px;padding:16px;font-size:18px;outline:none;margin-bottom:14px}input:focus{border-color:#f5c542;box-shadow:0 0 0 4px rgba(245,197,66,.12)}button{width:100%;border:0;border-radius:16px;padding:16px;background:linear-gradient(135deg,#f5c542,#b78317);color:#111;font-weight:1000;font-size:16px;cursor:pointer}.error{margin-bottom:14px;padding:12px;border-radius:14px;background:rgba(255,59,48,.14);border:1px solid rgba(255,59,48,.35);color:#fecaca;font-weight:900}.hint{margin-top:15px;color:#94a3b8;font-size:12px}</style>
<style id="mdBuscadorClientesVisualCss">
.clientesSearchPanel{
  margin:16px 0 12px;
  padding:16px;
  border:1px solid rgba(245,197,66,.30);
  background:linear-gradient(180deg,rgba(17,24,32,.86),rgba(4,8,12,.91));
  border-radius:22px;
  box-shadow:0 24px 70px rgba(0,0,0,.35);
}
.clientesSearchPanel h2{
  margin:0 0 10px;
  font-size:24px;
}
.clientesSearchPanel input{
  max-width:100%;
}
@media(max-width:760px){
  .clientesSearchPanel{
    padding:13px!important;
    border-radius:20px!important;
  }
  .clientesSearchPanel h2{
    font-size:22px!important;
  }
}
</style>



<style id="mdprimeBotonesUniformesHoverFinal">
/* ===== MDPRIME BOTONES UNIFORMES + HOVER PRO ===== */
.clientMainActions,
.inactivoActions{
  display:grid!important;
  grid-template-columns:repeat(5,minmax(0,1fr))!important;
  gap:8px!important;
  align-items:stretch!important;
}
.clientMainActions form,
.inactivoActions form{
  margin:0!important;
  width:100%!important;
  height:100%!important;
  display:flex!important;
}
.clientMainActions .btn,
.inactivoActions .btn{
  width:100%!important;
  min-width:0!important;
  height:62px!important;
  min-height:62px!important;
  padding:8px 7px!important;
  display:flex!important;
  align-items:center!important;
  justify-content:center!important;
  text-align:center!important;
  font-size:13px!important;
  font-weight:1000!important;
  line-height:1.12!important;
  white-space:normal!important;
  border-radius:14px!important;
  position:relative!important;
  transform:translateZ(0) scale(1)!important;
  transform-origin:center!important;
  transition:transform .18s ease, box-shadow .18s ease, filter .18s ease!important;
  will-change:transform!important;
}
.clientMainActions .btn:hover,
.inactivoActions .btn:hover{
  transform:translateZ(0) scale(1.08)!important;
  z-index:20!important;
  filter:brightness(1.08)!important;
  box-shadow:0 14px 34px rgba(0,0,0,.50), 0 0 18px rgba(245,197,66,.20)!important;
}
.clientMainActions .btn.red:hover,
.inactivoActions .btn.red:hover{
  box-shadow:0 14px 34px rgba(0,0,0,.50), 0 0 22px rgba(255,59,48,.42)!important;
}
.clientMainActions .btn.green:hover,
.inactivoActions .btn.green:hover{
  box-shadow:0 14px 34px rgba(0,0,0,.50), 0 0 22px rgba(53,208,79,.34)!important;
}
.clientMainActions .btnTelegramRef:hover{
  box-shadow:0 14px 34px rgba(0,0,0,.50), 0 0 22px rgba(31,182,255,.42)!important;
}
@media(max-width:760px){
  .clientMainActions,
  .inactivoActions{
    grid-template-columns:1fr!important;
  }
  .clientMainActions .btn,
  .inactivoActions .btn{
    height:52px!important;
    min-height:52px!important;
    font-size:14px!important;
  }
  .clientMainActions .btn:hover,
  .inactivoActions .btn:hover{
    transform:none!important;
  }
}
</style>
</head><body><form class="login" method="post"><div class="lock">🔒</div><div class="brand">MDPRIME</div><div class="sub">Panel privado</div>'.$err.'<input type="password" name="panel_login_password" placeholder="Introduce la contraseña" required autofocus><button>Entrar al panel</button><div class="hint">Acceso protegido por contraseña</div></form></body></html>';
  exit;
}
/* ===== FIN PROTECCIÓN DE ACCESO ===== */

$db_host = "reseau.proxy.rlwy.net";
$db_port = 39553;
$db_name = "railway";
$db_user = "root";
$db_pass = "ZRNWfdsxefUJrBMSJMchlLxzMHrAZjug";

function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function clean($v){ return trim(strip_tags((string)$v)); }
function dnull($v){ $v=clean($v); return preg_match('/^\d{4}-\d{2}-\d{2}$/',$v)?$v:null; }
function euro($v){ return ((float)$v>0) ? rtrim(rtrim(number_format((float)$v,2,',','.'),'0'),',').'€' : '-'; }
function fechaTxt($v){ return ($v && $v !== '0000-00-00') ? date('d/m/Y', strtotime($v)) : 'Sin fecha'; }
function redirectBack($msg){
  $url = $_SERVER['PHP_SELF'].'?msg='.urlencode($msg);
  if(!empty($_POST['return_modal'])){
    $url .= '&open='.urlencode(clean($_POST['return_modal']));
  }
  header('Location: '.$url);
  exit;
}

try{
  $pdo = new PDO("mysql:host=$db_host;port=$db_port;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass, [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,PDO::ATTR_TIMEOUT=>10]);
}catch(Throwable $e){ die("<body style='background:#050505;color:white;font-family:Arial;padding:20px'><div style='border:1px solid #ef4444;border-radius:20px;padding:20px;background:#111'><h2>Error MySQL</h2><p>".h($e->getMessage())."</p></div></body>"); }
function hasCol($pdo,$table,$col){ try{$s=$pdo->prepare("SHOW COLUMNS FROM `$table` LIKE ?");$s->execute([$col]);return (bool)$s->fetch();}catch(Throwable $e){return false;} }
$pdo->exec("CREATE TABLE IF NOT EXISTS clientes(id INT AUTO_INCREMENT PRIMARY KEY,nombre VARCHAR(150) NOT NULL,contacto VARCHAR(150) DEFAULT '',telefono VARCHAR(150) DEFAULT '',telegram VARCHAR(100) DEFAULT '',nota TEXT,fecha_alta TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
$pdo->exec("CREATE TABLE IF NOT EXISTS referidos(id INT AUTO_INCREMENT PRIMARY KEY,cliente_id INT NOT NULL,nombre VARCHAR(150) NOT NULL,fecha_alta DATE NULL,fecha_caducidad DATE NULL,estado VARCHAR(20) DEFAULT 'Activo',nota TEXT,creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
$pdo->exec("CREATE TABLE IF NOT EXISTS clientes_normales(id INT AUTO_INCREMENT PRIMARY KEY,nombre VARCHAR(150) NOT NULL,contacto VARCHAR(150) DEFAULT '',telefono VARCHAR(150) DEFAULT '',telegram VARCHAR(100) DEFAULT '',fecha_alta DATE NULL,fecha_caducidad DATE NULL,estado VARCHAR(20) DEFAULT 'Activo',nota TEXT,creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
$pdo->exec("CREATE TABLE IF NOT EXISTS configuracion_niveles(id INT AUTO_INCREMENT PRIMARY KEY,nivel VARCHAR(50),min_activos INT,trimestral DECIMAL(10,2),semestral DECIMAL(10,2),anual DECIMAL(10,2)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
foreach(['contacto'=>"ALTER TABLE clientes ADD contacto VARCHAR(150) DEFAULT ''",'telefono'=>"ALTER TABLE clientes ADD telefono VARCHAR(150) DEFAULT ''",'telegram'=>"ALTER TABLE clientes ADD telegram VARCHAR(100) DEFAULT ''",'nota'=>"ALTER TABLE clientes ADD nota TEXT"] as $c=>$sql){ if(!hasCol($pdo,'clientes',$c)) $pdo->exec($sql); }
foreach(['fecha_alta'=>"ALTER TABLE referidos ADD fecha_alta DATE NULL",'fecha_caducidad'=>"ALTER TABLE referidos ADD fecha_caducidad DATE NULL",'estado'=>"ALTER TABLE referidos ADD estado VARCHAR(20) DEFAULT 'Activo'",'nota'=>"ALTER TABLE referidos ADD nota TEXT"] as $c=>$sql){ if(!hasCol($pdo,'referidos',$c)) $pdo->exec($sql); }
foreach(['contacto'=>"ALTER TABLE clientes_normales ADD contacto VARCHAR(150) DEFAULT ''",'telefono'=>"ALTER TABLE clientes_normales ADD telefono VARCHAR(150) DEFAULT ''",'telegram'=>"ALTER TABLE clientes_normales ADD telegram VARCHAR(100) DEFAULT ''",'fecha_alta'=>"ALTER TABLE clientes_normales ADD fecha_alta DATE NULL",'fecha_caducidad'=>"ALTER TABLE clientes_normales ADD fecha_caducidad DATE NULL",'estado'=>"ALTER TABLE clientes_normales ADD estado VARCHAR(20) DEFAULT 'Activo'",'nota'=>"ALTER TABLE clientes_normales ADD nota TEXT"] as $c=>$sql){ if(!hasCol($pdo,'clientes_normales',$c)) $pdo->exec($sql); }
if((int)$pdo->query("SELECT COUNT(*) FROM configuracion_niveles")->fetchColumn()===0){$ins=$pdo->prepare("INSERT INTO configuracion_niveles(nivel,min_activos,trimestral,semestral,anual) VALUES(?,?,?,?,?)");foreach([['COBRE',4,30,45,65],['PLATA',8,27,40,58],['ORO',12,25,37,52],['PLATINUM',20,22,33,45]] as $r) $ins->execute($r);} 
/* ===== PRECIOS REFERIDOS VIP ACTUALIZADOS ===== */
$updNivel = $pdo->prepare("UPDATE configuracion_niveles SET min_activos=?, trimestral=?, semestral=?, anual=? WHERE UPPER(nivel)=?");
foreach([['COBRE',4,30,45,65],['PLATA',8,27,40,58],['ORO',12,25,37,52],['PLATINUM',20,22,33,45]] as $r){
  $updNivel->execute([$r[1],$r[2],$r[3],$r[4],$r[0]]);
}

$today=date('Y-m-d');
$pdo->prepare("UPDATE referidos SET estado='Inactivo' WHERE fecha_caducidad IS NOT NULL AND fecha_caducidad < ?")->execute([$today]);
$msg='';

/* ===== DEBUG REFERIDO MDPRIME =====
   Uso: panel.php?debug_ref=usuario
   Muestra si un referido está realmente guardado en Railway. */
if (isset($_GET['debug_ref']) && $_GET['debug_ref'] !== '') {
  header('Content-Type: text/plain; charset=utf-8');

  $q = clean($_GET['debug_ref']);
  echo "MDPRIME PANEL DEBUG REFERIDO\n";
  echo "━━━━━━━━━━━━━━━━━━━━━━\n";
  echo "Buscado: ".$q."\n\n";

  try {
    $stmt = $pdo->prepare("
      SELECT r.id, r.cliente_id, r.nombre, r.fecha_alta, r.fecha_caducidad, r.estado, c.nombre AS referente
      FROM referidos r
      LEFT JOIN clientes c ON c.id = r.cliente_id
      WHERE LOWER(TRIM(r.nombre)) = LOWER(TRIM(?))
         OR REPLACE(LOWER(TRIM(r.nombre)), ' ', '') = REPLACE(LOWER(TRIM(?)), ' ', '')
      ORDER BY r.id DESC
      LIMIT 20
    ");
    $stmt->execute([$q,$q]);
    $rows = $stmt->fetchAll();

    if (!$rows) {
      echo "❌ No aparece en Railway.\n";
      echo "Últimos 10 referidos guardados:\n\n";
      $last = $pdo->query("SELECT r.id, r.nombre, r.cliente_id, r.fecha_caducidad, r.estado, c.nombre AS referente FROM referidos r LEFT JOIN clientes c ON c.id=r.cliente_id ORDER BY r.id DESC LIMIT 10")->fetchAll();
      foreach ($last as $r) {
        echo "#".$r['id']." · ".$r['nombre']." · referente: ".($r['referente'] ?: 'Sin referente')." · caduca: ".$r['fecha_caducidad']." · estado: ".$r['estado']."\n";
      }
      exit;
    }

    echo "✅ Encontrado en Railway:\n\n";
    foreach ($rows as $r) {
      echo "#".$r['id']."\n";
      echo "Nombre: ".$r['nombre']."\n";
      echo "cliente_id: ".$r['cliente_id']."\n";
      echo "Referente: ".($r['referente'] ?: 'Sin referente')."\n";
      echo "Alta: ".$r['fecha_alta']."\n";
      echo "Caduca: ".$r['fecha_caducidad']."\n";
      echo "Estado: ".$r['estado']."\n";
      echo "━━━━━━━━━━━━━━━━━━━━━━\n";
    }
    exit;

  } catch(Throwable $e) {
    echo "❌ Error debug:\n".$e->getMessage()."\n";
    exit;
  }
}


if($_SERVER['REQUEST_METHOD']==='POST'){
  $a=$_POST['action']??'';
  try{
    if($a==='add_cliente'){$nombre=clean($_POST['nombre']??'');$contacto=clean($_POST['contacto']??'');$telegram=clean($_POST['telegram']??'');$telegram=ltrim($telegram,'@');$nota=clean($_POST['nota']??'');if($nombre!==''){$pdo->prepare("INSERT INTO clientes(nombre,contacto,telefono,telegram,nota) VALUES(?,?,?,?,?)")->execute([$nombre,$contacto,$contacto,$telegram,$nota]);$msg='Cliente añadido.';}}
    if($a==='update_cliente'){$id=(int)($_POST['cliente_id']??0);$nombre=clean($_POST['nombre']??'');$contacto=clean($_POST['contacto']??'');$telegram=clean($_POST['telegram']??'');$telegram=ltrim($telegram,'@');$nota=clean($_POST['nota']??'');if($id&&$nombre!==''){$pdo->prepare("UPDATE clientes SET nombre=?,contacto=?,telefono=?,telegram=?,nota=? WHERE id=?")->execute([$nombre,$contacto,$contacto,$telegram,$nota,$id]);$msg='Perfil del referente actualizado.';}}
    if($a==='delete_cliente'){$id=(int)($_POST['cliente_id']??0);if($id){$pdo->prepare("DELETE FROM referidos WHERE cliente_id=?")->execute([$id]);$pdo->prepare("DELETE FROM clientes WHERE id=?")->execute([$id]);$msg='Referente eliminado junto con todos sus referidos.';}}
    if($a==='add_referido'){
      $cid=(int)($_POST['cliente_id']??0);

      // Compatibilidad con distintos formularios del panel.
      $nombre=clean($_POST['nombre'] ?? ($_POST['nombre_referido'] ?? ''));
      $alta=dnull($_POST['fecha_alta'] ?? ($_POST['fecha'] ?? ''))?:$today;
      $cad=dnull($_POST['fecha_caducidad'] ?? ($_POST['caduca'] ?? ''));
      $estado=clean($_POST['estado']??'Activo');
      $nota=clean($_POST['nota']??'');

      if($cad&&$cad<$today)$estado='Inactivo';

      if($nombre===''){
        throw new Exception('El nombre del referido está vacío. No se ha guardado.');
      }

      // Si por cualquier motivo el cliente_id no llega, intentamos recuperarlo desde return_modal/open.
      if(!$cid){
        $modalRaw = clean($_POST['return_modal'] ?? ($_GET['open'] ?? ''));
        if(preg_match('/^m(\d+)$/', $modalRaw, $m)){
          $cid = (int)$m[1];
        }
      }

      if(!$cid){
        throw new Exception('No se recibió el ID del referente. No se ha guardado el referido.');
      }

      $checkCliente=$pdo->prepare("SELECT id,nombre FROM clientes WHERE id=? LIMIT 1");
      $checkCliente->execute([$cid]);
      $clienteOk=$checkCliente->fetch();

      if(!$clienteOk){
        throw new Exception('El referente seleccionado no existe en Railway. ID: '.$cid);
      }

      // Evitar duplicados exactos en el mismo referente.
      $dup=$pdo->prepare("SELECT id FROM referidos WHERE cliente_id=? AND LOWER(TRIM(nombre))=LOWER(TRIM(?)) LIMIT 1");
      $dup->execute([$cid,$nombre]);
      if($dup->fetch()){
        throw new Exception('Ese referido ya existe para este referente: '.$nombre);
      }

      $insertRef=$pdo->prepare("INSERT INTO referidos(cliente_id,nombre,fecha_alta,fecha_caducidad,estado,nota) VALUES(?,?,?,?,?,?)");
      $insertRef->execute([$cid,$nombre,$alta,$cad,$estado,$nota]);

      $newId=(int)$pdo->lastInsertId();

      $verify=$pdo->prepare("SELECT id,nombre,cliente_id FROM referidos WHERE id=? LIMIT 1");
      $verify->execute([$newId]);
      $rowVerify=$verify->fetch();

      if(!$newId || !$rowVerify){
        throw new Exception('El panel intentó guardar el referido, pero Railway no devolvió confirmación.');
      }

      $msg='Referido añadido correctamente. ID Railway: '.$newId.' · Usuario: '.$rowVerify['nombre'].' · Referente: '.$clienteOk['nombre'];
    }
    if($a==='update_fecha_inactivo'){
      $id=(int)($_POST['ref_id']??0);
      $cad=dnull($_POST['fecha_caducidad']??'');
      if($id && $cad){
        $estado = ($cad < $today) ? 'Inactivo' : 'Activo';
        $pdo->prepare("UPDATE referidos SET fecha_caducidad=?, estado=? WHERE id=?")->execute([$cad,$estado,$id]);
        $msg='Fecha de caducidad actualizada.';
      }
    }
    if($a==='update_referido'){$id=(int)($_POST['ref_id']??0);$nombre=clean($_POST['nombre']??'');$alta=dnull($_POST['fecha_alta']??'');$cad=dnull($_POST['fecha_caducidad']??'');$estado=clean($_POST['estado']??'Activo');$nota=clean($_POST['nota']??'');if($cad&&$cad<$today)$estado='Inactivo';if($id&&$nombre!==''){$pdo->prepare("UPDATE referidos SET nombre=?,fecha_alta=?,fecha_caducidad=?,estado=?,nota=? WHERE id=?")->execute([$nombre,$alta,$cad,$estado,$nota,$id]);$msg='Referido actualizado.';}}
    if($a==='toggle_ref'){$id=(int)($_POST['ref_id']??0);if($id){$pdo->prepare("UPDATE referidos SET estado=IF(estado='Activo','Inactivo','Activo') WHERE id=?")->execute([$id]);$msg='Estado cambiado.';}}
    if($a==='delete_ref'){$id=(int)($_POST['ref_id']??0);if($id){$pdo->prepare("DELETE FROM referidos WHERE id=?")->execute([$id]);$msg='Referido eliminado.';}}
    if($a==='renew_ref'){
      $id=(int)($_POST['ref_id']??0);
      $months=(int)($_POST['months']??0);
      if($id && in_array($months,[3,6,12])){
        $pdo->prepare("UPDATE referidos SET fecha_caducidad = DATE_ADD(COALESCE(NULLIF(fecha_caducidad,'0000-00-00'), NULLIF(fecha_caducidad,''), CURDATE()), INTERVAL ? MONTH), estado='Activo' WHERE id=?")->execute([$months,$id]);
        $msg='Referido renovado '.$months.' meses.';
      }
    }
    if($a==='add_normal'){
      $nombre=clean($_POST['nombre']??'');
      $contacto=clean($_POST['contacto']??'');
      $telegram=ltrim(clean($_POST['telegram']??''),'@');
      $alta=dnull($_POST['fecha_alta']??'')?:$today;
      $cad=dnull($_POST['fecha_caducidad']??'');
      $estado=clean($_POST['estado']??'Activo');
      $nota=clean($_POST['nota']??'');
      if($cad&&$cad<$today)$estado='Inactivo';
      if($nombre!==''){
        $dup=$pdo->prepare("SELECT id FROM clientes_normales WHERE LOWER(TRIM(nombre))=LOWER(TRIM(?)) LIMIT 1");
        $dup->execute([$nombre]);
        if($dup->fetch()) throw new Exception('Ese cliente normal ya existe: '.$nombre);
        $pdo->prepare("INSERT INTO clientes_normales(nombre,contacto,telefono,telegram,fecha_alta,fecha_caducidad,estado,nota) VALUES(?,?,?,?,?,?,?,?)")->execute([$nombre,$contacto,$contacto,$telegram,$alta,$cad,$estado,$nota]);
        $msg='Cliente normal añadido correctamente. ID Railway: '.$pdo->lastInsertId();
      }
    }
    if($a==='update_normal'){
      $id=(int)($_POST['normal_id']??0);
      $nombre=clean($_POST['nombre']??'');
      $contacto=clean($_POST['contacto']??'');
      $telegram=ltrim(clean($_POST['telegram']??''),'@');
      $alta=dnull($_POST['fecha_alta']??'');
      $cad=dnull($_POST['fecha_caducidad']??'');
      $estado=clean($_POST['estado']??'Activo');
      $nota=clean($_POST['nota']??'');
      if($cad&&$cad<$today)$estado='Inactivo';
      if($id&&$nombre!==''){
        $pdo->prepare("UPDATE clientes_normales SET nombre=?,contacto=?,telefono=?,telegram=?,fecha_alta=?,fecha_caducidad=?,estado=?,nota=? WHERE id=?")->execute([$nombre,$contacto,$contacto,$telegram,$alta,$cad,$estado,$nota,$id]);
        $msg='Cliente normal actualizado.';
      }
    }
    if($a==='toggle_normal'){
      $id=(int)($_POST['normal_id']??0);
      if($id){$pdo->prepare("UPDATE clientes_normales SET estado=IF(estado='Activo','Inactivo','Activo') WHERE id=?")->execute([$id]);$msg='Estado de cliente normal cambiado.';}
    }
    if($a==='delete_normal'){
      $id=(int)($_POST['normal_id']??0);
      if($id){$pdo->prepare("DELETE FROM clientes_normales WHERE id=?")->execute([$id]);$msg='Cliente normal eliminado.';}
    }
    if($a==='renew_normal'){
      $id=(int)($_POST['normal_id']??0);
      $months=(int)($_POST['months']??0);
      if($id && in_array($months,[3,6,12])){
        $pdo->prepare("UPDATE clientes_normales SET fecha_caducidad = NULL WHERE id = ? AND CAST(fecha_caducidad AS CHAR) = '0000-00-00'")->execute([$id]);
        $pdo->prepare("UPDATE clientes_normales SET fecha_caducidad = DATE_ADD(CASE WHEN fecha_caducidad IS NOT NULL AND fecha_caducidad >= CURDATE() THEN fecha_caducidad ELSE CURDATE() END, INTERVAL ".$months." MONTH), estado='Activo' WHERE id=?")->execute([$id]);
        $msg='Cliente normal renovado '.$months.' meses.';
      }
    }

    if($a==='export_json'){$out=['clientes'=>[]];$cs=$pdo->query("SELECT * FROM clientes ORDER BY nombre")->fetchAll();foreach($cs as $c){$rs=$pdo->prepare("SELECT * FROM referidos WHERE cliente_id=? ORDER BY fecha_alta DESC,id DESC");$rs->execute([$c['id']]);$c['referidos']=$rs->fetchAll();$out['clientes'][]=$c;}header('Content-Type: application/json; charset=utf-8');header('Content-Disposition: attachment; filename="backup_mdprime_referidos_'.date('Ymd_His').'.json"');echo json_encode($out, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE);exit;}
  }catch(Throwable $e){$msg='Error: '.$e->getMessage();}
  redirectBack($msg);
}
$msg=$_GET['msg']??'';
$niveles=$pdo->query("SELECT * FROM configuracion_niveles ORDER BY min_activos ASC")->fetchAll();
function nivelActual($activos,$niveles){$r=['nivel'=>'SIN NIVEL','min_activos'=>0,'trimestral'=>0,'semestral'=>0,'anual'=>0];foreach($niveles as $n){if($activos>=(int)$n['min_activos'])$r=$n;}$n=strtoupper($r['nivel']);$r['icon']=['COBRE'=>'🛡️','PLATA'=>'⚜️','ORO'=>'🏆','PLATINUM'=>'💎'][$n]??'🔒';$r['class']=strtolower($n);return $r;}
function nextLevel($activos,$niveles){foreach($niveles as $n){if((int)$n['min_activos']>$activos)return $n;}return null;}
function levelIcon($nivel){$n=strtoupper($nivel);return ['COBRE'=>'🛡️','PLATA'=>'⚜️','ORO'=>'🏆','PLATINUM'=>'💎'][$n]??'🔒';}
$clientes=$pdo->query("SELECT c.*,COUNT(r.id) total_refs,SUM(CASE WHEN r.estado='Activo' AND (r.fecha_caducidad IS NULL OR r.fecha_caducidad >= CURDATE()) THEN 1 ELSE 0 END) activos,SUM(CASE WHEN r.id IS NOT NULL AND NOT (r.estado='Activo' AND (r.fecha_caducidad IS NULL OR r.fecha_caducidad >= CURDATE())) THEN 1 ELSE 0 END) inactivos FROM clientes c LEFT JOIN referidos r ON r.cliente_id=c.id GROUP BY c.id ORDER BY activos DESC,total_refs DESC,nombre ASC")->fetchAll();
$totalClientes=count($clientes);$totalRefs=0;$totalActivos=0;$totalInactivos=0;$nearUpgrade=0;$platinumCount=0;
foreach($clientes as $c){$act=(int)$c['activos'];$totalRefs+=(int)$c['total_refs'];$totalActivos+=$act;$totalInactivos+=(int)$c['inactivos'];$n=nivelActual($act,$niveles);if(strtoupper($n['nivel'])==='PLATINUM')$platinumCount++;$nx=nextLevel($act,$niveles);if($nx&&((int)$nx['min_activos']-$act)<=2)$nearUpgrade++;}
$top=$clientes[0]??null;$topNivel=$top?nivelActual((int)$top['activos'],$niveles):nivelActual(0,$niveles);$next=$top?nextLevel((int)$top['activos'],$niveles):null;$progress=$next?min(100,round(((int)$top['activos']/(int)$next['min_activos'])*100)):100;$pctAct=$totalRefs>0?round(($totalActivos/$totalRefs)*100):0;
$latest=$pdo->query("SELECT r.*,c.nombre cliente_nombre FROM referidos r JOIN clientes c ON c.id=r.cliente_id ORDER BY r.id DESC LIMIT 7")->fetchAll();
$limite3dias = date('Y-m-d', strtotime($today.' +3 days'));
$soonStmt = $pdo->prepare("SELECT r.*, c.nombre cliente_nombre FROM referidos r JOIN clientes c ON c.id=r.cliente_id WHERE r.estado='Activo' AND r.fecha_caducidad IS NOT NULL AND r.fecha_caducidad >= ? AND r.fecha_caducidad <= ? ORDER BY r.fecha_caducidad ASC LIMIT 8");
$soonStmt->execute([$today, $limite3dias]);
$soon=$soonStmt->fetchAll();
$caducan7Stmt=$pdo->prepare("SELECT COUNT(*) FROM referidos WHERE estado='Activo' AND fecha_caducidad IS NOT NULL AND fecha_caducidad >= ? AND fecha_caducidad <= ?");
$caducan7Stmt->execute([$today, $limite3dias]);
$caducan7=(int)$caducan7Stmt->fetchColumn();
$buscadorRefs=$pdo->query("SELECT r.*, c.nombre cliente_nombre, c.id cliente_id_real FROM referidos r JOIN clientes c ON c.id=r.cliente_id ORDER BY r.nombre ASC, c.nombre ASC")->fetchAll();
$referidosInactivos=$pdo->query("SELECT r.*, c.nombre cliente_nombre, c.id cliente_id_real FROM referidos r JOIN clientes c ON c.id=r.cliente_id WHERE NOT (r.estado='Activo' AND (r.fecha_caducidad IS NULL OR r.fecha_caducidad >= CURDATE())) ORDER BY r.fecha_caducidad ASC, r.nombre ASC")->fetchAll();

$clientesNormales=$pdo->query("SELECT * FROM clientes_normales ORDER BY CASE WHEN estado='Activo' AND (fecha_caducidad IS NULL OR fecha_caducidad >= CURDATE()) THEN 0 ELSE 1 END, fecha_caducidad ASC, nombre ASC")->fetchAll();
$totalNormales=count($clientesNormales);
$totalNormalesActivos=0;
$totalNormalesInactivos=0;
foreach($clientesNormales as $cn){
  if(($cn['estado']??'')==='Activo' && (empty($cn['fecha_caducidad']) || $cn['fecha_caducidad'] >= $today)) $totalNormalesActivos++;
  else $totalNormalesInactivos++;
}


/* ===== DETECTOR DE REFERIDOS REPETIDOS ENTRE REFERENTES =====
   Compara nombres normalizados y solo avisa cuando aparecen en más de un referente. */
$duplicadosReferidos=$pdo->query("
  SELECT 
    LOWER(TRIM(r.nombre)) AS nombre_normalizado,
    MIN(r.nombre) AS nombre_mostrado,
    COUNT(*) AS repeticiones,
    COUNT(DISTINCT r.cliente_id) AS referentes_distintos,
    GROUP_CONCAT(DISTINCT c.nombre ORDER BY c.nombre SEPARATOR ' · ') AS referentes,
    GROUP_CONCAT(CONCAT(r.id,'|',c.nombre,'|',r.estado,'|',COALESCE(r.fecha_alta,''),'|',COALESCE(r.fecha_caducidad,''),'|',COALESCE(r.nota,'')) ORDER BY c.nombre SEPARATOR '###') AS detalles
  FROM referidos r
  JOIN clientes c ON c.id=r.cliente_id
  WHERE TRIM(r.nombre) <> ''
  GROUP BY LOWER(TRIM(r.nombre))
  HAVING referentes_distintos > 1
  ORDER BY referentes_distintos DESC, repeticiones DESC, nombre_mostrado ASC
")->fetchAll();
$totalDuplicadosReferidos=count($duplicadosReferidos);


/* ===== PAGINACIÓN MDPRIME: 12 CLIENTES Y 12 INACTIVOS POR PÁGINA ===== */
$porPagina = 12;
$paginaClientes = max(1, (int)($_GET['pagina_clientes'] ?? 1));
$paginaInactivos = max(1, (int)($_GET['pagina_inactivos'] ?? 1));
$totalPagClientes = max(1, (int)ceil(count($clientes) / $porPagina));
$totalPagInactivos = max(1, (int)ceil(count($referidosInactivos) / $porPagina));
if($paginaClientes > $totalPagClientes) $paginaClientes = $totalPagClientes;
if($paginaInactivos > $totalPagInactivos) $paginaInactivos = $totalPagInactivos;
$clientesPagina = array_slice($clientes, ($paginaClientes - 1) * $porPagina, $porPagina);
$referidosInactivosPagina = array_slice($referidosInactivos, ($paginaInactivos - 1) * $porPagina, $porPagina);
function pageUrl($key, $value){ $q=$_GET; $q[$key]=max(1,(int)$value); return $_SERVER['PHP_SELF'].'?'.http_build_query($q); }

?>
<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover"><title>MDPRIME Referidos VIP V6</title>
<style>
:root{--bg:#030405;--panel:#0b1014;--panel2:#111820;--gold:#f5c542;--gold2:#b78317;--line:rgba(245,197,66,.28);--txt:#f8fafc;--muted:#aeb7c4;--green:#35d04f;--red:#ff3b30;--blue:#1fb6ff;--cyan:#32d3c6;--shadow:0 24px 70px rgba(0,0,0,.55);--radius:22px}*{box-sizing:border-box;-webkit-tap-highlight-color:transparent}html,body{margin:0;max-width:100%;overflow-x:hidden}body{font-family:Inter,system-ui,Segoe UI,Arial;background:var(--bg);color:var(--txt);min-height:100vh}body:before{content:"";position:fixed;inset:0;z-index:-3;background:radial-gradient(circle at 20% 0%,rgba(245,197,66,.17),transparent 26%),radial-gradient(circle at 83% 5%,rgba(31,182,255,.12),transparent 24%),linear-gradient(135deg,#010203,#071018 45%,#030405)}body:after{content:"";position:fixed;inset:0;z-index:-2;background-image:linear-gradient(rgba(245,197,66,.045) 1px,transparent 1px),linear-gradient(90deg,rgba(245,197,66,.035) 1px,transparent 1px);background-size:48px 48px;opacity:.34}.app{display:grid;grid-template-columns:245px 1fr;min-height:100vh}.sidebar{position:sticky;top:0;height:100vh;border-right:1px solid var(--line);background:linear-gradient(180deg,rgba(5,9,12,.96),rgba(1,2,3,.97));padding:22px 18px;box-shadow:var(--shadow)}.logo{font-size:40px;font-weight:1000;letter-spacing:-2px;color:var(--gold);line-height:.85;margin-bottom:24px}.logo small{display:block;color:white;font-size:13px;letter-spacing:5px;margin-top:9px}.nav a,.quick a,.quick button{width:100%;display:flex;align-items:center;gap:12px;border:0;text-decoration:none;color:white;background:transparent;padding:13px 14px;border-radius:14px;font-weight:800;font-size:15px;cursor:pointer}.nav a.active,.nav a:hover,.quick a:hover,.quick button:hover{background:linear-gradient(90deg,rgba(245,197,66,.9),rgba(183,131,23,.75));color:#111}.quick{margin-top:28px;border:1px solid var(--line);border-radius:18px;padding:12px;background:rgba(255,255,255,.035)}.quick h4{margin:0 0 8px;color:var(--gold);font-size:14px;text-transform:uppercase}.main{padding:20px 24px 90px}.header{text-align:center;position:relative;margin-bottom:17px}.header h1{margin:0;font-size:clamp(28px,4vw,52px);font-weight:1000;letter-spacing:-1.3px}.header h1 span{color:var(--gold)}.header p{margin:7px 0 0;color:#d5d7dc;letter-spacing:2px;font-weight:700;text-transform:uppercase}.admin{position:absolute;right:0;top:0;border:1px solid var(--line);border-radius:999px;padding:10px 16px;background:rgba(255,255,255,.04);font-weight:900}.panel{border:1px solid var(--line);background:linear-gradient(180deg,rgba(17,24,32,.86),rgba(4,8,12,.91));border-radius:var(--radius);box-shadow:var(--shadow)}.dashboard{display:grid;grid-template-columns:1fr 310px;gap:16px}.center{padding:16px}.right{display:grid;gap:16px}.alerts{display:grid;grid-template-columns:repeat(5,1fr);gap:10px;margin-bottom:14px}.alert{border:1px solid rgba(255,255,255,.12);border-radius:16px;padding:12px;background:linear-gradient(135deg,rgba(245,197,66,.12),rgba(255,255,255,.03));font-weight:900;color:#fff}.alert b{display:block;font-size:20px;color:var(--gold)}.stats{display:grid;grid-template-columns:repeat(4,1fr);gap:12px}.stat{padding:18px;border:1px solid rgba(255,255,255,.12);border-radius:17px;background:linear-gradient(135deg,rgba(255,255,255,.07),rgba(255,255,255,.02));display:flex;align-items:center;gap:14px;min-height:105px}.ico{font-size:42px;filter:drop-shadow(0 0 10px rgba(255,255,255,.18))}.stat b{font-size:34px;display:block}.stat span{color:var(--muted);font-weight:800;font-size:12px;text-transform:uppercase}.mid{display:grid;grid-template-columns:1fr 1.15fr;gap:14px;margin-top:14px}.level{padding:22px;border-color:rgba(245,197,66,.55)}.level h3,.box h3{margin:0 0 14px;color:white;font-size:17px;text-transform:uppercase}.levelInner{display:flex;align-items:center;gap:22px}.medal{font-size:72px}.levelName{font-size:43px;font-weight:1000;color:#e5e7eb}.progress{height:20px;background:#232a31;border-radius:999px;overflow:hidden;border:1px solid rgba(255,255,255,.12);margin-top:17px}.bar{height:100%;width:var(--w);background:linear-gradient(90deg,var(--gold),#fff29b)}.donutBox{padding:22px}.donutWrap{display:flex;align-items:center;justify-content:center;gap:28px}.donut{width:185px;height:185px;border-radius:50%;background:conic-gradient(var(--green) 0 calc(var(--p)*1%), var(--red) calc(var(--p)*1%) 100%);display:grid;place-items:center}.donutIn{width:105px;height:105px;border-radius:50%;background:#081018;display:grid;place-items:center;text-align:center;border:1px solid rgba(255,255,255,.15)}.donutIn b{font-size:29px}.legend div{margin:10px 0}.sq{display:inline-block;width:14px;height:14px;border-radius:4px;margin-right:8px;vertical-align:-2px}.g{background:var(--green)}.r{background:var(--red)}.bottomGrid{display:grid;grid-template-columns:1.15fr .85fr;gap:14px;margin-top:14px}.box{padding:18px}.miniTable{width:100%;border-collapse:collapse}.miniTable th,.miniTable td{padding:10px;border-bottom:1px solid rgba(255,255,255,.08);text-align:left}.miniTable th{font-size:12px;color:#cbd5e1;text-transform:uppercase}.activo{color:#46ff60}.inactivo{color:#ff453a}.gold{color:var(--gold)}.infoCard{padding:20px}.infoCard h3{margin:0 0 12px;text-align:center;text-transform:uppercase}.infoCard ul{margin:0;padding-left:0;list-style:none}.infoCard li{margin:11px 0;color:#e4e8ee}.ok{color:#65e572}.mysql{font-size:36px;color:#70e45c;font-weight:1000}.formAdd{margin-top:16px;padding:16px;display:grid;grid-template-columns:1fr 1fr 1fr 1.4fr auto;gap:12px;align-items:end}.levels{margin-top:16px;padding:16px}.levelGrid{display:grid;grid-template-columns:repeat(4,1fr);gap:14px}.levelCard{position:relative;overflow:hidden;padding:18px;border-radius:20px;border:1px solid rgba(255,255,255,.13);min-height:170px;background:#070b12}.levelCard:before{content:"";position:absolute;inset:-70px -40px auto auto;width:160px;height:160px;border-radius:50%;background:radial-gradient(circle,rgba(255,255,255,.22),transparent 65%)}.levelCard:after{content:"";position:absolute;inset:0;background:linear-gradient(110deg,transparent 20%,rgba(255,255,255,.12) 45%,transparent 65%);transform:translateX(-120%);animation:shine 5s infinite}@keyframes shine{0%,55%{transform:translateX(-130%)}75%,100%{transform:translateX(130%)}}.levelIcon{font-size:44px}.levelCard h3{margin:8px 0 4px;font-size:22px}.levelCard p{margin:0;color:#d7dce5}.levelPrices{display:flex;gap:6px;margin-top:12px}.levelPrices span{flex:1;text-align:center;border-radius:12px;padding:8px 4px;background:rgba(0,0,0,.25);font-weight:900}.lv-cobre{background:linear-gradient(135deg,rgba(184,96,34,.45),rgba(70,36,16,.55))}.lv-plata{background:linear-gradient(135deg,rgba(226,232,240,.38),rgba(76,88,105,.48))}.lv-oro{background:linear-gradient(135deg,rgba(245,197,66,.45),rgba(92,62,5,.55))}.lv-platinum{background:linear-gradient(135deg,rgba(45,212,191,.35),rgba(31,182,255,.22),rgba(139,92,246,.22))}input,select,textarea{width:100%;background:#050914;color:white;border:1px solid rgba(255,255,255,.12);border-radius:14px;padding:13px;outline:none;font:inherit}input:focus,textarea:focus,select:focus{border-color:var(--gold);box-shadow:0 0 0 4px rgba(245,197,66,.1)}label{display:block;color:#d8dee8;font-weight:900;font-size:12px;margin-bottom:7px}.btn{border:0;border-radius:14px;padding:13px 16px;min-height:47px;background:linear-gradient(135deg,var(--gold),var(--gold2));color:#111;font-weight:1000;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;justify-content:center;gap:8px}.btn.dark{background:#111823;color:white;border:1px solid rgba(255,255,255,.14)}.btn.green{background:linear-gradient(135deg,#30df73,#16bfb3);color:white}.btn.red{background:linear-gradient(135deg,#ff5c5c,#c41235);color:white}.btn.small{min-height:38px;padding:9px 11px;font-size:13px}.notice{margin:12px 0;padding:13px 16px;border:1px solid rgba(53,208,79,.45);background:rgba(53,208,79,.12);border-radius:16px;color:#bbf7d0;font-weight:900}.clientTools{display:flex;justify-content:space-between;gap:12px;align-items:center;margin:20px 0 12px}.searchBox{max-width:420px}.clients{display:grid;grid-template-columns:repeat(3,1fr);gap:14px}.client{padding:16px;position:relative;overflow:hidden}.client:before{content:"";position:absolute;right:-40px;top:-40px;width:130px;height:130px;border-radius:50%;background:radial-gradient(circle,rgba(245,197,66,.18),transparent 68%)}.client h3{margin:0 0 6px;font-size:22px}.badge{display:inline-flex;border:1px solid var(--line);border-radius:999px;padding:7px 10px;background:rgba(245,197,66,.08);font-weight:1000}.prices{display:grid;grid-template-columns:repeat(3,1fr);gap:8px;margin:12px 0}.price{padding:9px;border-radius:12px;background:#050914;border:1px solid rgba(255,255,255,.08);text-align:center}.price b{display:block;color:var(--gold);font-size:19px}.metrics{display:grid;grid-template-columns:repeat(3,1fr);gap:8px;margin:12px 0}.metric{padding:10px;background:#050914;border:1px solid rgba(255,255,255,.08);border-radius:12px;text-align:center}.metric b{display:block;font-size:21px}.note{font-size:13px;color:#dbeafe;background:rgba(255,255,255,.045);border-radius:12px;padding:10px;min-height:42px}.miniProgress{margin-top:10px}.miniProgress small{display:flex;justify-content:space-between;color:#cbd5e1;margin-bottom:5px}.miniBar{height:10px;border-radius:999px;background:#222b34;overflow:hidden}.miniBar span{display:block;height:100%;width:var(--w);background:linear-gradient(90deg,var(--gold),#fff29b)}.expiryList{display:grid;gap:8px}.expiry{display:flex;justify-content:space-between;gap:10px;align-items:center;background:rgba(255,255,255,.045);border:1px solid rgba(255,255,255,.08);border-radius:14px;padding:10px}.expiry b{color:var(--gold)}.rankCards{display:grid;gap:9px}.rankCard{display:flex;align-items:center;justify-content:space-between;gap:10px;background:rgba(255,255,255,.045);border:1px solid rgba(255,255,255,.08);border-radius:14px;padding:11px}.rankCard strong{font-size:17px}.modal{display:none;position:fixed;inset:0;z-index:50;background:rgba(0,0,0,.78);backdrop-filter:blur(10px);padding:25px;overflow:auto}.modal.open{display:block}.sheet{max-width:1080px;margin:auto}.sheetHead{padding:17px;display:flex;justify-content:space-between;gap:12px;align-items:center;position:sticky;top:0;z-index:2}.sheetHead h2{margin:0;font-size:32px}.tabs{display:flex;gap:8px;flex-wrap:wrap;margin-top:10px}.tabs a{padding:9px 12px;border-radius:999px;background:#050914;border:1px solid rgba(255,255,255,.12);text-decoration:none;font-weight:900}.modalBody{display:grid;grid-template-columns:360px 1fr;gap:14px;margin-top:14px}.refList{display:grid;gap:10px}.ref{padding:13px;border:1px solid rgba(255,255,255,.1);border-radius:16px;background:#080e16}.refTop{display:flex;justify-content:space-between;gap:8px}.status{padding:6px 9px;border-radius:999px;font-weight:1000;font-size:12px}.status.act{background:rgba(53,208,79,.15);color:#87ff97}.status.in{background:rgba(255,59,48,.15);color:#ff9b94}.refActions{display:grid;grid-template-columns:repeat(3,1fr);gap:7px;margin-top:10px}.editBox{display:none;margin-top:10px;padding-top:10px;border-top:1px solid rgba(255,255,255,.1)}.ref.edit .editBox{display:block}.mobileNav{display:none}@media(max-width:1200px){.dashboard{grid-template-columns:1fr}.right{grid-template-columns:repeat(3,1fr)}.clients{grid-template-columns:repeat(2,1fr)}}@media(max-width:980px){.app{grid-template-columns:1fr}.sidebar{display:none}.header{text-align:left}.admin{position:static;display:inline-flex;margin-bottom:10px}.alerts,.stats,.mid,.bottomGrid,.formAdd,.levelGrid,.right,.clients,.modalBody{grid-template-columns:1fr}.donutWrap{flex-direction:column}.clientTools{display:block}.searchBox{max-width:100%;margin-top:10px}}@media(max-width:760px){.main{padding:12px 10px 90px}.header h1{font-size:27px}.header p{font-size:11px;letter-spacing:1px}.panel{border-radius:18px}.modal{padding:0}.sheet{min-height:100dvh;border-radius:0}.sheetHead{border-radius:0}.refActions{grid-template-columns:1fr}.mobileNav{display:grid;grid-template-columns:repeat(4,1fr);position:fixed;bottom:0;left:0;right:0;background:rgba(3,4,5,.95);backdrop-filter:blur(16px);border-top:1px solid var(--line);z-index:20}.mobileNav a{padding:10px 5px;text-align:center;text-decoration:none;color:white;font-size:12px;font-weight:900}.stat{min-height:88px}.ico{font-size:34px}}

/* =========================
   MDPRIME MOBILE APP PRO FIX
   ========================= */
@media(max-width:760px){
  html,body{
    width:100%!important;
    max-width:100%!important;
    overflow-x:hidden!important;
    background:#020304!important;
  }

  body:before{
    background:
      radial-gradient(circle at 50% -5%,rgba(245,197,66,.20),transparent 32%),
      radial-gradient(circle at 100% 18%,rgba(31,182,255,.14),transparent 28%),
      linear-gradient(180deg,#030405 0%,#071018 42%,#030405 100%)!important;
  }

  .app{
    display:block!important;
    width:100%!important;
    max-width:100%!important;
    padding:0!important;
    margin:0!important;
  }

  .sidebar,.rightRail{
    display:none!important;
  }

  .main{
    padding:10px 10px 88px!important;
    width:100%!important;
    max-width:100%!important;
    overflow-x:hidden!important;
  }

  .header{
    text-align:left!important;
    margin:0 0 12px!important;
    padding:16px 14px!important;
    border:1px solid rgba(245,197,66,.28)!important;
    border-radius:22px!important;
    background:
      radial-gradient(circle at 90% 10%,rgba(245,197,66,.20),transparent 35%),
      linear-gradient(135deg,rgba(17,24,32,.94),rgba(4,8,12,.96))!important;
    box-shadow:0 16px 45px rgba(0,0,0,.45)!important;
  }

  .header h1{
    font-size:24px!important;
    line-height:1.02!important;
    letter-spacing:-.8px!important;
    margin-top:8px!important;
  }

  .header p{
    font-size:10px!important;
    line-height:1.35!important;
    letter-spacing:.7px!important;
    margin-top:8px!important;
  }

  .admin{
    position:static!important;
    display:inline-flex!important;
    padding:7px 11px!important;
    font-size:12px!important;
    margin:0!important;
  }

  .panel{
    border-radius:20px!important;
    box-shadow:0 14px 42px rgba(0,0,0,.42)!important;
    max-width:100%!important;
    overflow:hidden!important;
  }

  .dashboard{
    display:block!important;
  }

  .center{
    padding:12px!important;
  }

  .alerts{
    display:grid!important;
    grid-template-columns:1fr 1fr!important;
    gap:8px!important;
    margin-bottom:10px!important;
  }

  .alert{
    min-height:74px!important;
    padding:11px!important;
    border-radius:16px!important;
    font-size:12px!important;
    line-height:1.22!important;
  }

  .alert b{
    font-size:23px!important;
    margin-top:3px!important;
  }

  .stats{
    grid-template-columns:1fr 1fr!important;
    gap:8px!important;
  }

  .stat{
    min-height:92px!important;
    padding:12px!important;
    border-radius:17px!important;
    display:flex!important;
    align-items:center!important;
    gap:10px!important;
  }

  .ico,.statIcon{
    width:38px!important;
    height:38px!important;
    font-size:25px!important;
    margin:0!important;
    flex:0 0 auto!important;
  }

  .stat b{
    font-size:27px!important;
  }

  .stat span{
    font-size:10px!important;
    line-height:1.15!important;
  }

  .mid,.bottomGrid,.formAdd,.levelGrid,.clients,.clientsGrid,.modalBody,.visualGrid,.tableGrid,.right{
    grid-template-columns:1fr!important;
    display:grid!important;
    gap:10px!important;
  }

  .level,.levelCard,.donutBox,.donutCard,.box,.tableCard,.formCard,.levels,.levelsCard,.infoCard{
    padding:13px!important;
    border-radius:19px!important;
    width:100%!important;
    max-width:100%!important;
  }

  .levelInner,.levelBig{
    gap:12px!important;
  }

  .medal{
    width:58px!important;
    height:58px!important;
    font-size:34px!important;
    flex:0 0 auto!important;
  }

  .levelName{
    font-size:27px!important;
    line-height:1!important;
    word-break:break-word!important;
  }

  .progress,.miniBar{
    height:12px!important;
  }

  .donutWrap{
    display:grid!important;
    grid-template-columns:1fr!important;
    text-align:center!important;
    gap:10px!important;
  }

  .donut{
    width:148px!important;
    height:148px!important;
    margin:0 auto!important;
  }

  .donutInner,.donutIn{
    width:88px!important;
    height:88px!important;
  }

  .donutInner b,.donutIn b{
    font-size:24px!important;
  }

  .legend,.legendItem{
    justify-content:center!important;
    font-size:13px!important;
  }

  .miniTable{
    font-size:12px!important;
  }

  .miniTable th,.miniTable td,table th,table td{
    padding:8px 6px!important;
    font-size:12px!important;
  }

  .tableCard{
    overflow-x:auto!important;
    -webkit-overflow-scrolling:touch!important;
  }

  .formAdd{
    padding:13px!important;
    margin-top:10px!important;
  }

  input,select,textarea{
    font-size:16px!important;
    padding:13px 12px!important;
    border-radius:14px!important;
    max-width:100%!important;
  }

  textarea{
    min-height:88px!important;
  }

  .btn{
    width:100%!important;
    min-height:50px!important;
    border-radius:15px!important;
    padding:12px!important;
    font-size:14px!important;
    white-space:normal!important;
  }

  .levelGrid,.levelsMini{
    grid-template-columns:1fr!important;
  }

  .levelCard{
    min-height:132px!important;
  }

  .levelIcon{
    font-size:38px!important;
  }

  .levelCard h3{
    font-size:22px!important;
  }

  .levelPrices{
    gap:7px!important;
  }

  .levelPrices span{
    padding:9px 4px!important;
    font-size:13px!important;
  }

  .clientTools,.clientsHeader{
    display:block!important;
    margin:14px 0 10px!important;
  }

  .clientTools h2,.clientsHeader h2{
    font-size:22px!important;
    margin-bottom:8px!important;
  }

  .searchBox,.search{
    max-width:100%!important;
    margin-top:8px!important;
  }

  .client{
    padding:14px!important;
    border-radius:20px!important;
    width:100%!important;
    max-width:100%!important;
    overflow:hidden!important;
  }

  .clientTop{
    align-items:flex-start!important;
    gap:8px!important;
  }

  .clientName,.client h3{
    font-size:21px!important;
    line-height:1.05!important;
    word-break:break-word!important;
  }

  .clientContact,.muted{
    font-size:12px!important;
    word-break:break-word!important;
  }

  .badge{
    font-size:12px!important;
    padding:7px 9px!important;
    white-space:nowrap!important;
  }

  .metrics,.clientMetrics,.prices,.priceRow{
    grid-template-columns:repeat(3,minmax(0,1fr))!important;
    gap:6px!important;
  }

  .metric,.cm,.price{
    padding:8px 4px!important;
    border-radius:12px!important;
    min-width:0!important;
  }

  .metric b,.cm b,.price b{
    font-size:17px!important;
  }

  .metric span,.cm span,.price span{
    font-size:10px!important;
  }

  .note{
    font-size:12px!important;
    line-height:1.35!important;
  }

  .clientActions{
    grid-template-columns:1fr!important;
    gap:8px!important;
  }

  .expiry,.rankCard{
    padding:10px!important;
    border-radius:14px!important;
    font-size:13px!important;
  }

  .modal{
    padding:0!important;
    overflow:hidden!important;
    touch-action:pan-y!important;
  }

  .modal.open{
    display:block!important;
  }

  .sheet,.modalSheet{
    width:100vw!important;
    max-width:100vw!important;
    height:100dvh!important;
    min-height:100dvh!important;
    margin:0!important;
    border-radius:0!important;
    border:0!important;
    overflow:hidden!important;
    display:flex!important;
    flex-direction:column!important;
  }

  .sheetHead,.modalHead{
    flex:0 0 auto!important;
    position:sticky!important;
    top:0!important;
    z-index:5!important;
    padding:12px!important;
    border-radius:0!important;
    background:linear-gradient(135deg,rgba(17,24,32,.98),rgba(4,8,12,.98))!important;
    backdrop-filter:blur(16px)!important;
  }

  .sheetHead h2,.modalTitle h2{
    font-size:21px!important;
    line-height:1.05!important;
    word-break:break-word!important;
    max-width:72vw!important;
  }

  .modalTitle{
    align-items:flex-start!important;
  }

  .closeBtn,.sheetHead > button{
    width:44px!important;
    height:44px!important;
    min-height:44px!important;
    padding:0!important;
    border-radius:999px!important;
    flex:0 0 auto!important;
  }

  .tabs,.modalTabs{
    display:flex!important;
    gap:7px!important;
    overflow-x:auto!important;
    padding-bottom:4px!important;
    scrollbar-width:none!important;
  }

  .tabs::-webkit-scrollbar,.modalTabs::-webkit-scrollbar{
    display:none!important;
  }

  .tabs a,.modalTabs a{
    flex:0 0 auto!important;
    font-size:12px!important;
    padding:9px 11px!important;
    border-radius:999px!important;
  }

  .modalBody{
    overflow-y:auto!important;
    overflow-x:hidden!important;
    padding:12px 12px 92px!important;
    width:100%!important;
    max-width:100%!important;
    -webkit-overflow-scrolling:touch!important;
  }

  .ref,.refCard{
    border-radius:16px!important;
    padding:12px!important;
    width:100%!important;
    max-width:100%!important;
  }

  .refTop{
    align-items:flex-start!important;
  }

  .refName{
    font-size:16px!important;
    word-break:break-word!important;
  }

  .status,.estado{
    font-size:11px!important;
    padding:6px 8px!important;
    white-space:nowrap!important;
  }

  .refDates{
    grid-template-columns:1fr!important;
    gap:5px!important;
    font-size:12px!important;
  }

  .refActions{
    grid-template-columns:1fr!important;
    gap:7px!important;
  }

  .editBox .modalGrid{
    grid-template-columns:1fr!important;
  }

  .full{
    grid-column:1!important;
  }

  .mobileNav{
    display:grid!important;
    grid-template-columns:repeat(4,1fr)!important;
    position:fixed!important;
    left:8px!important;
    right:8px!important;
    bottom:8px!important;
    z-index:2000!important;
    border:1px solid rgba(245,197,66,.32)!important;
    background:rgba(3,4,5,.94)!important;
    backdrop-filter:blur(18px)!important;
    border-radius:22px!important;
    padding:7px!important;
    box-shadow:0 18px 50px rgba(0,0,0,.65)!important;
  }

  .mobileNav a{
    text-decoration:none!important;
    color:white!important;
    text-align:center!important;
    font-size:11px!important;
    font-weight:900!important;
    padding:8px 4px!important;
    border-radius:15px!important;
  }

  .mobileNav a.active,.mobileNav a:hover{
    background:linear-gradient(135deg,var(--gold),var(--gold2))!important;
    color:#111!important;
  }
}

/* Evita que cualquier elemento fuerce scroll lateral */
@media(max-width:760px){
  *{
    max-width:100%;
  }
}



/* ===== FIX SCROLL MODAL MÓVIL SAFARI/IPHONE ===== */
@media(max-width:760px){
  html,body{
    overflow-x:hidden!important;
  }
  .modal,
  .modal.open{
    overflow-y:auto!important;
    overflow-x:hidden!important;
    -webkit-overflow-scrolling:touch!important;
    overscroll-behavior:contain!important;
  }
  .sheet,
  .modalSheet{
    height:100dvh!important;
    min-height:100dvh!important;
    max-height:100dvh!important;
    overflow-y:auto!important;
    overflow-x:hidden!important;
    -webkit-overflow-scrolling:touch!important;
  }
  .modalBody{
    display:block!important;
    height:auto!important;
    min-height:calc(100dvh - 150px)!important;
    max-height:none!important;
    overflow-y:visible!important;
    overflow-x:hidden!important;
    padding-bottom:130px!important;
    -webkit-overflow-scrolling:touch!important;
  }
  .sheetHead,
  .modalHead{
    position:sticky!important;
    top:0!important;
    z-index:999!important;
    flex:0 0 auto!important;
  }
  .modalBody > aside,
  .modalBody > section,
  .modalBody > .panel{
    margin-bottom:12px!important;
  }
}



/* ===== MDPRIME DUPLICADOS REFERIDOS ===== */
.duplicadosPanel{margin:16px 0;padding:16px;border:1px solid rgba(245,197,66,.36);background:linear-gradient(180deg,rgba(31,23,8,.90),rgba(6,8,12,.94));border-radius:22px;box-shadow:0 24px 70px rgba(0,0,0,.35)}
.duplicadosHead{display:flex;justify-content:space-between;align-items:center;gap:12px;margin-bottom:12px}
.duplicadosHead h2{margin:0;font-size:24px}.duplicadosBadge{display:inline-flex;align-items:center;gap:8px;border-radius:999px;padding:9px 13px;background:rgba(245,197,66,.14);border:1px solid rgba(245,197,66,.38);color:#fde68a;font-weight:1000}.duplicadosGrid{display:grid;grid-template-columns:repeat(2,1fr);gap:10px}.duplicadoCard{border:1px solid rgba(255,255,255,.12);background:rgba(255,255,255,.045);border-radius:18px;padding:13px}.duplicadoTop{display:flex;justify-content:space-between;gap:10px;align-items:flex-start}.duplicadoNombre{font-size:19px;font-weight:1000;color:#fff}.duplicadoMeta{color:#cbd5e1;font-size:13px;margin-top:5px;line-height:1.35}.duplicadoAlert{padding:7px 10px;border-radius:999px;background:rgba(245,197,66,.18);color:#fde68a;font-weight:1000;font-size:12px;white-space:nowrap}.duplicadoDetalles{display:grid;gap:7px;margin-top:10px}.duplicadoLinea{padding:10px;border-radius:13px;background:rgba(0,0,0,.22);border:1px solid rgba(255,255,255,.08);font-size:13px;color:#e5e7eb}.duplicadoLinea b{color:#f5c542}.duplicadoOk{padding:14px;border-radius:16px;background:rgba(53,208,79,.12);border:1px solid rgba(53,208,79,.35);color:#bbf7d0;font-weight:1000}
@media(max-width:980px){.duplicadosGrid{grid-template-columns:1fr}}
@media(max-width:760px){.duplicadosHead{display:block}.duplicadosBadge{margin-top:8px}.duplicadoTop{display:block}.duplicadoAlert{display:inline-flex;margin-top:8px}}

/* ===== CLIENTES NORMALES MDPRIME ===== */
.normalesPanel{margin:16px 0;padding:16px;border:1px solid rgba(31,182,255,.32);background:linear-gradient(180deg,rgba(12,25,36,.90),rgba(4,8,12,.94));border-radius:22px;box-shadow:0 24px 70px rgba(0,0,0,.35)}
.normalesHead{display:flex;justify-content:space-between;align-items:center;gap:12px;margin-bottom:12px}
.normalesHead h2{margin:0;font-size:24px}.normalesBadge{display:inline-flex;align-items:center;gap:8px;border-radius:999px;padding:9px 13px;background:rgba(31,182,255,.14);border:1px solid rgba(31,182,255,.38);color:#bae6fd;font-weight:1000}
.normalesForm{display:grid;grid-template-columns:1fr 1fr 1fr 1fr 1fr auto;gap:9px;align-items:end;margin:12px 0}.normalesGrid{display:grid;grid-template-columns:repeat(3,1fr);gap:10px}
.normalCard{border:1px solid rgba(255,255,255,.12);background:rgba(255,255,255,.045);border-radius:18px;padding:13px;overflow:visible!important}.normalTop{display:flex;justify-content:space-between;gap:10px;align-items:flex-start}.normalNombre{font-size:18px;font-weight:1000}.normalMeta{color:#cbd5e1;font-size:13px;margin-top:5px;line-height:1.35}.normalActions{display:grid;grid-template-columns:repeat(5,1fr);gap:7px;margin-top:10px}.normalEdit{display:none;margin-top:10px;padding:12px;border-top:1px solid rgba(255,255,255,.12);background:rgba(0,0,0,.18);border-radius:14px}.normalCard.editing .normalEdit{display:block}
@media(max-width:980px){.normalesGrid{grid-template-columns:1fr 1fr}.normalesForm{grid-template-columns:1fr 1fr}}@media(max-width:760px){.normalesHead{display:block}.normalesBadge{margin-top:8px}.normalesGrid,.normalesForm{grid-template-columns:1fr!important}.normalActions{grid-template-columns:1fr!important}}

/* ===== BUSCADOR CLIENTES NORMALES V10 ===== */
.normalesSearchBox{
  margin:14px 0 14px;
  padding:13px;
  border:1px solid rgba(31,182,255,.28);
  background:rgba(0,0,0,.18);
  border-radius:18px;
}
.normalesSearchGrid{
  display:grid;
  grid-template-columns:1.5fr auto auto auto auto;
  gap:8px;
  align-items:end;
}
.normalesSearchBox input{
  width:100%;
}
.normalFilterBtn{
  border:1px solid rgba(255,255,255,.14)!important;
  background:#111823!important;
  color:#fff!important;
}
.normalFilterBtn.active{
  background:linear-gradient(135deg,#1fb6ff,#32d3c6)!important;
  color:#031018!important;
}
.normalNoResults{
  display:none;
  margin-top:10px;
  padding:12px;
  border-radius:14px;
  background:rgba(255,59,48,.12);
  border:1px solid rgba(255,59,48,.28);
  color:#fecaca;
  font-weight:1000;
}
.normalNoResults.show{display:block}
@media(max-width:980px){
  .normalesSearchGrid{grid-template-columns:1fr 1fr}
}
@media(max-width:760px){
  .normalesSearchGrid{grid-template-columns:1fr!important}
}
</style>
<style id="mdPerfilReferenteSoloCss">
.clientMainActions{display:grid;grid-template-columns:repeat(5,1fr);gap:8px;margin-top:12px}
.clientMainActions form{margin:0}
.clientMainActions form .btn{width:100%;height:100%}
.perfilReferenteBox{display:none;margin-top:12px;padding:14px;border:1px solid rgba(245,197,66,.30);background:linear-gradient(135deg,rgba(245,197,66,.10),rgba(255,255,255,.035));border-radius:18px}
.perfilReferenteBox.open{display:block!important}
.perfilReferenteHead{display:flex;align-items:center;justify-content:space-between;gap:10px;margin-bottom:10px}
.perfilReferenteHead h3{margin:0}
.btnXPerfil{width:42px!important;min-height:42px!important;padding:0!important;border-radius:999px!important}
.tgRefLink{color:#7dd3fc;text-decoration:none;font-weight:1000}
.tgRefLink:hover{text-decoration:underline}
.btnTelegramRef{background:linear-gradient(135deg,#229ED9,#1d6fd6)!important;color:white!important;border:0!important}
.btnTelegramRef.off{background:#111823!important;color:#94a3b8!important;border:1px solid rgba(255,255,255,.14)!important;cursor:not-allowed!important;opacity:.72}
@media(max-width:760px){.clientMainActions{grid-template-columns:1fr!important}.perfilReferenteBox{padding:12px!important}}
</style>

<style id="mdBuscadorReferidosCss">
.quickRefSearch{margin:16px 0;padding:16px;border:1px solid rgba(245,197,66,.30);background:linear-gradient(180deg,rgba(17,24,32,.86),rgba(4,8,12,.91));border-radius:22px;box-shadow:0 24px 70px rgba(0,0,0,.35)}
.quickRefSearch h2{margin:0 0 10px;font-size:24px}
.quickRefResults{display:grid;gap:10px;margin-top:12px}
.quickRefCard{display:none;border:1px solid rgba(255,255,255,.12);background:rgba(255,255,255,.045);border-radius:18px;padding:13px}
.quickRefCard.show{display:block}
.quickRefTop{display:flex;justify-content:space-between;gap:10px;align-items:flex-start}
.quickRefName{font-size:18px;font-weight:1000}
.quickRefMeta{color:#cbd5e1;font-size:13px;margin-top:4px}
.quickRefActions{display:grid;grid-template-columns:repeat(5,1fr);gap:7px;margin-top:10px}
.quickRefEdit{display:none;margin-top:10px;padding-top:10px;border-top:1px solid rgba(255,255,255,.12)}
.quickRefCard.editing .quickRefEdit{display:grid;gap:8px}
.quickRefEditHead{display:flex;justify-content:space-between;align-items:center;gap:10px}
.quickRefEditHead h3{margin:0}
.quickRefEmpty{display:none;color:#cbd5e1;background:rgba(255,255,255,.05);border-radius:14px;padding:12px;margin-top:10px}
.quickRefEmpty.show{display:block}
@media(max-width:760px){
  .quickRefActions{grid-template-columns:1fr!important}
  .quickRefTop{display:block!important}
}

/* ===== MDPRIME INACTIVOS PRO ===== */
.inactivosPanel{margin:16px 0;padding:16px;border:1px solid rgba(255,59,48,.32);background:linear-gradient(180deg,rgba(32,17,17,.88),rgba(12,4,4,.92));border-radius:22px;box-shadow:0 24px 70px rgba(0,0,0,.35)}
.inactivosHead{display:flex;justify-content:space-between;align-items:center;gap:12px;margin-bottom:12px}
.inactivosHead h2{margin:0;font-size:24px}
.inactivosBadge{display:inline-flex;align-items:center;gap:8px;border-radius:999px;padding:9px 13px;background:rgba(255,59,48,.15);border:1px solid rgba(255,59,48,.35);color:#fecaca;font-weight:1000}
.inactivosGrid{display:grid;grid-template-columns:repeat(3,1fr);gap:10px}
.inactivoCard{border:1px solid rgba(255,255,255,.12);background:rgba(255,255,255,.045);border-radius:18px;padding:13px}
.inactivoTop{display:flex;justify-content:space-between;gap:10px;align-items:flex-start}
.inactivoNombre{font-size:18px;font-weight:1000}
.inactivoMeta{color:#cbd5e1;font-size:13px;margin-top:5px;line-height:1.35}
.inactivoRef{color:#f5c542;font-weight:1000}
.inactivoEstado{padding:6px 9px;border-radius:999px;background:rgba(255,59,48,.15);color:#fecaca;font-weight:1000;font-size:12px;white-space:nowrap}
.inactivoActions{display:grid;grid-template-columns:repeat(6,1fr);gap:7px;margin-top:10px}
.inactivoFechaEdit{display:none;margin-top:10px;padding:12px;border-top:1px solid rgba(255,255,255,.12);background:rgba(0,0,0,.18);border-radius:14px}
.inactivoFechaEdit.open{display:block}
.inactivoFechaEdit form{display:grid;grid-template-columns:1fr auto;gap:8px;align-items:end}
.inactivoFechaEdit label{margin:0 0 6px}
.mdPagination{display:flex;justify-content:center;align-items:center;gap:10px;flex-wrap:wrap;margin:16px 0 4px}
.mdPaginationInfo{padding:10px 14px;border-radius:999px;border:1px solid rgba(245,197,66,.28);background:rgba(255,255,255,.045);color:#dbeafe;font-weight:1000}
.mdPagination .btn.disabled{opacity:.45;pointer-events:none;filter:grayscale(1)}
@media(max-width:980px){.inactivosGrid{grid-template-columns:1fr 1fr}}
@media(max-width:760px){.inactivosHead{display:block}.inactivosBadge{margin-top:8px}.inactivosGrid{grid-template-columns:1fr}.inactivoActions{grid-template-columns:1fr!important}.inactivoFechaEdit form{grid-template-columns:1fr!important}}
</style>

<style id="mdprimeHoverBotonesRealFinal">
/* ===== MDPRIME FIX REAL: BOTONES IGUALES + AUMENTO AL PASAR RATÓN ===== */
.client,
.inactivoCard,
.quickRefCard,
.ref{
  overflow:visible!important;
}
.clientMainActions,
.inactivoActions,
.quickRefActions,
.refActions{
  display:grid!important;
  grid-template-columns:repeat(5,minmax(0,1fr))!important;
  gap:8px!important;
  align-items:stretch!important;
  overflow:visible!important;
}
.clientMainActions form,
.inactivoActions form,
.quickRefActions form,
.refActions form{
  margin:0!important;
  width:100%!important;
  height:100%!important;
  display:flex!important;
  overflow:visible!important;
}
.clientMainActions .btn,
.inactivoActions .btn,
.quickRefActions .btn,
.refActions .btn{
  width:100%!important;
  min-width:0!important;
  height:58px!important;
  min-height:58px!important;
  padding:8px 7px!important;
  display:flex!important;
  align-items:center!important;
  justify-content:center!important;
  text-align:center!important;
  font-size:13px!important;
  font-weight:1000!important;
  line-height:1.12!important;
  white-space:normal!important;
  border-radius:14px!important;
  position:relative!important;
  z-index:1!important;
  transform:scale(1)!important;
  transform-origin:center center!important;
  transition:transform .18s ease, box-shadow .18s ease, filter .18s ease!important;
  will-change:transform!important;
}
.clientMainActions .btn:hover,
.inactivoActions .btn:hover,
.quickRefActions .btn:hover,
.refActions .btn:hover{
  transform:scale(1.10)!important;
  z-index:999!important;
  filter:brightness(1.10)!important;
  box-shadow:0 16px 38px rgba(0,0,0,.62), 0 0 20px rgba(245,197,66,.28)!important;
}
.clientMainActions form:hover,
.inactivoActions form:hover,
.quickRefActions form:hover,
.refActions form:hover{
  z-index:999!important;
}
@media(max-width:760px){
  .clientMainActions,
  .inactivoActions,
  .quickRefActions,
  .refActions{
    grid-template-columns:1fr!important;
  }
  .clientMainActions .btn,
  .inactivoActions .btn,
  .quickRefActions .btn,
  .refActions .btn{
    height:52px!important;
    min-height:52px!important;
    font-size:14px!important;
  }
}
</style>
</head><body>
<div class="app"><aside class="sidebar"><div class="logo">MD<small>PRIME</small></div><nav class="nav"><a class="active" href="#dashboard">🏠 Dashboard</a><a href="#clientes">👥 Clientes</a><a href="#referidos">👥 Referidos</a><a href="#duplicados">🔁 Repetidos</a><a href="#inactivos">❌ Inactivos</a><a href="#addCliente">➕ Añadir Cliente</a><a href="#ranking">🏆 Ranking</a><a href="#niveles">🛡️ Niveles</a><a href="#caducidades">📅 Caducidades</a></nav><div class="quick"><h4>Acceso rápido</h4><a href="#addCliente">👤 Añadir Cliente</a><a href="#ranking">🏆 Ver Ranking</a><form method="post"><input type="hidden" name="action" value="export_json"><button>💾 Exportar Backup</button></form></div></aside><main class="main"><header class="header"><div class="admin">🔒 Privado · <a href="?logout=1" style="color:#f5c542;text-decoration:none">Salir</a></div><h1>PANEL DE REFERIDOS <span>MDPRIME</span></h1><p>Sistema profesional de gestión de clientes y referidos</p></header><?php if($msg): ?><div class="notice"><?=h($msg)?></div><?php endif; ?>
<section class="dashboard" id="dashboard"><div class="center panel"><div class="alerts"><div class="alert">⚠️ Caducan en 3 días <b><?= $caducan7 ?></b></div><div class="alert">🔁 Repetidos <b><?= $totalDuplicadosReferidos ?></b></div><div class="alert">🏆 Cerca de subir <b><?= $nearUpgrade ?></b></div><div class="alert">💎 Platinum <b><?= $platinumCount ?></b></div><div class="alert">📊 Controlados <b><?= $totalRefs ?></b></div></div><div class="stats"><div class="stat"><div class="ico">👤</div><div><b><?= $totalClientes ?></b><span>Clientes totales</span></div></div><div class="stat"><div class="ico">👥</div><div><b><?= $totalRefs ?></b><span>Referidos totales</span></div></div><div class="stat"><div class="ico">✅</div><div><b><?= $totalActivos ?></b><span>Activos <?= $totalRefs? '('.$pctAct.'%)':'' ?></span></div></div><div class="stat"><div class="ico">❌</div><div><b><?= $totalInactivos ?></b><span>Inactivos <?= $totalRefs? '('.(100-$pctAct).'%)':'' ?></span></div></div></div><div class="mid"><div class="level panel"><h3>Nivel actual destacado</h3><div class="levelInner"><div class="medal"><?= $topNivel['icon'] ?></div><div><div class="levelName"><?= h($topNivel['nivel']) ?></div><div><?= $top ? h($top['nombre']).' · '.(int)$top['activos'].' referidos activos' : 'Sin clientes todavía' ?></div></div></div><div style="margin-top:16px;color:#e7edf5"><?= $next ? 'Siguiente nivel: '.h($next['nivel']).' ('.(int)$next['min_activos'].' activos)' : 'Nivel máximo alcanzado' ?></div><div class="progress"><div class="bar" style="--w:<?= $progress ?>%"></div></div></div><div class="donutBox panel"><h3>Referidos por estado</h3><div class="donutWrap"><div class="donut" style="--p:<?= $pctAct ?>"><div class="donutIn"><div><b><?= $totalRefs ?></b><br><span>Total</span></div></div></div><div class="legend"><div><span class="sq g"></span> Activos: <?= $totalActivos ?> <?= $totalRefs?'('.$pctAct.'%)':'' ?></div><div><span class="sq r"></span> Inactivos: <?= $totalInactivos ?> <?= $totalRefs?'('.(100-$pctAct).'%)':'' ?></div></div></div></div></div><div class="bottomGrid"><div class="box panel" id="referidos"><h3>Últimos referidos</h3><table class="miniTable"><thead><tr><th>Nombre</th><th>Fecha</th><th>Estado</th><th>Cliente</th></tr></thead><tbody><?php foreach($latest as $r): ?><tr><td><?=h($r['nombre'])?></td><td><?=h($r['fecha_alta'] ?: '-')?></td><td class="<?= $r['estado']==='Activo'?'activo':'inactivo' ?>"><?=h($r['estado'])?></td><td><?=h($r['cliente_nombre'])?></td></tr><?php endforeach; if(!$latest): ?><tr><td colspan="4">Sin referidos todavía.</td></tr><?php endif; ?></tbody></table></div><div class="box panel" id="ranking"><h3>Ranking visual</h3><div class="rankCards"><?php foreach(array_slice($clientes,0,5) as $i=>$c): ?><div class="rankCard"><div><?= $i==0?'🥇':($i==1?'🥈':($i==2?'🥉':'#'.($i+1))) ?> <strong><?=h($c['nombre'])?></strong></div><span class="gold"><?= (int)$c['activos'] ?> activos</span></div><?php endforeach; if(!$clientes): ?><div class="note">Sin clientes.</div><?php endif; ?></div></div></div></div><aside class="right"><div class="infoCard panel"><h3>Ahora MySQL</h3><div class="mysql">MySQL</div><ul><li><span class="ok">✔</span> Datos seguros y permanentes</li><li><span class="ok">✔</span> Acceso desde cualquier móvil</li><li><span class="ok">✔</span> Niveles automáticos</li></ul></div><div class="infoCard panel" id="caducidades"><h3>Próximas caducidades</h3><div class="expiryList"><?php foreach($soon as $s): ?><div class="expiry"><div><b><?= date('d/m', strtotime($s['fecha_caducidad'])) ?></b><br><?=h($s['nombre'])?></div><span><?=h($s['cliente_nombre'])?></span></div><?php endforeach; if(!$soon): ?><div class="note">Sin caducidades próximas.</div><?php endif; ?></div></div><div class="infoCard panel"><h3>Importación</h3><div style="font-size:50px">🏆</div><h2 style="margin:0"><?= $totalRefs ?> referidos controlados</h2><p class="muted">Clientes, notas, fechas y caducidades guardadas.</p></div></aside></section>
<form class="formAdd panel" method="post" id="addCliente"><input type="hidden" name="action" value="add_cliente"><div><label>Cliente referente</label><input name="nombre" placeholder="Nombre" required></div><div><label>WhatsApp / Contacto</label><input name="contacto" placeholder="WhatsApp o email"></div><div><label>Telegram referente</label><input name="telegram" placeholder="@usuarioTelegram"></div><div><label>Nota rápida</label><input name="nota" placeholder="Ej: cliente anual, confianza..."></div><button class="btn green">➕ Añadir</button></form>
<section class="normalesPanel panel" id="clientesNormales"><div class="normalesHead"><h2>👤 CLIENTES NORMALES</h2><span class="normalesBadge">Activos <?=$totalNormalesActivos?> · Inactivos <?=$totalNormalesInactivos?> · Total <?=$totalNormales?></span></div><div class="note">Clientes sin programa de referidos. Precios normales: 3 meses 35€ · 6 meses 55€ · 12 meses 80€.</div>

  <div class="normalesSearchBox">
    <label>🔎 Buscar cliente normal</label>
    <div class="normalesSearchGrid">
      <input id="normalSearchInput" type="search" placeholder="Escribe nombre, Telegram, WhatsApp o fecha..." oninput="mdFiltrarNormales()">
      <button type="button" class="btn normalFilterBtn active" data-normal-filter="todos" onclick="mdSetFiltroNormales('todos',this)">Todos</button>
      <button type="button" class="btn normalFilterBtn" data-normal-filter="activo" onclick="mdSetFiltroNormales('activo',this)">🟢 Activos</button>
      <button type="button" class="btn normalFilterBtn" data-normal-filter="inactivo" onclick="mdSetFiltroNormales('inactivo',this)">🔴 Inactivos</button>
      <button type="button" class="btn dark" onclick="mdLimpiarNormales()">❌ Limpiar</button>
    </div>
    <div id="normalNoResults" class="normalNoResults">No se encontraron clientes normales con esa búsqueda.</div>
  </div><form class="normalesForm" method="post"><input type="hidden" name="action" value="add_normal"><div><label>Cliente normal</label><input name="nombre" placeholder="Usuario" required></div><div><label>Contacto</label><input name="contacto" placeholder="WhatsApp o email"></div><div><label>Telegram</label><input name="telegram" placeholder="@usuario"></div><div><label>Alta</label><input type="date" name="fecha_alta" value="<?=$today?>"></div><div><label>Caduca</label><input type="date" name="fecha_caducidad"></div><button class="btn green">➕ Añadir normal</button><div style="grid-column:1/-1"><label>Nota</label><input name="nota" placeholder="Nota privada"></div></form><div class="normalesGrid"><?php foreach($clientesNormales as $cn): $normalActivo=(($cn['estado']??'')==='Activo' && (empty($cn['fecha_caducidad']) || $cn['fecha_caducidad'] >= $today)); ?><article class="normalCard" data-status="<?=$normalActivo?'activo':'inactivo'?>" data-caduca="<?=h($cn['fecha_caducidad'] ?: '')?>" data-search="<?=h(strtolower(($cn['nombre']??'').' '.($cn['telegram']??'').' '.($cn['contacto']??'').' '.($cn['telefono']??'').' '.($cn['estado']??'').' '.($cn['fecha_caducidad']??'')))?>"><div class="normalTop"><div><div class="normalNombre"><?=h($cn['nombre'])?></div><div class="normalMeta">Alta: <?=h($cn['fecha_alta'] ?: '-')?> · Caduca: <?=h($cn['fecha_caducidad'] ?: 'Sin fecha')?></div><div class="normalMeta">Telegram: <?=!empty($cn['telegram'])?'@'.h($cn['telegram']):'Sin Telegram'?> · Contacto: <?=h(($cn['contacto'] ?: $cn['telefono']) ?: '-')?></div><div class="normalMeta"><?= $cn['nota'] ? h($cn['nota']) : 'Sin nota' ?></div></div><span class="status <?=$normalActivo?'act':'in'?>"><?=$normalActivo?'Activo':'Inactivo'?></span></div><div class="normalActions"><button class="btn dark small" type="button" onclick="this.closest('.normalCard').classList.toggle('editing')">Editar</button><form method="post"><input type="hidden" name="action" value="renew_normal"><input type="hidden" name="normal_id" value="<?=$cn['id']?>"><input type="hidden" name="months" value="3"><button class="btn green small">+3 Meses</button></form><form method="post"><input type="hidden" name="action" value="renew_normal"><input type="hidden" name="normal_id" value="<?=$cn['id']?>"><input type="hidden" name="months" value="6"><button class="btn green small">+6 Meses</button></form><form method="post"><input type="hidden" name="action" value="renew_normal"><input type="hidden" name="normal_id" value="<?=$cn['id']?>"><input type="hidden" name="months" value="12"><button class="btn green small">+12 Meses</button></form><form method="post"><input type="hidden" name="action" value="toggle_normal"><input type="hidden" name="normal_id" value="<?=$cn['id']?>"><button class="btn small">Activo/Inactivo</button></form></div><div class="normalEdit"><form method="post" style="display:grid;gap:8px"><input type="hidden" name="action" value="update_normal"><input type="hidden" name="normal_id" value="<?=$cn['id']?>"><label>Nombre</label><input name="nombre" value="<?=h($cn['nombre'])?>" required><label>Contacto</label><input name="contacto" value="<?=h($cn['contacto'] ?: $cn['telefono'])?>"><label>Telegram</label><input name="telegram" value="<?=h($cn['telegram'])?>"><label>Alta</label><input type="date" name="fecha_alta" value="<?=h($cn['fecha_alta'])?>"><label>Caduca</label><input type="date" name="fecha_caducidad" value="<?=h($cn['fecha_caducidad'])?>"><label>Estado</label><select name="estado"><option <?=$cn['estado']==='Activo'?'selected':''?>>Activo</option><option <?=$cn['estado']!=='Activo'?'selected':''?>>Inactivo</option></select><label>Nota</label><input name="nota" value="<?=h($cn['nota'])?>"><button class="btn green">Guardar cambios</button></form><form method="post" onsubmit="return confirm('¿Eliminar definitivamente este cliente normal?')" style="margin-top:8px"><input type="hidden" name="action" value="delete_normal"><input type="hidden" name="normal_id" value="<?=$cn['id']?>"><button class="btn red">Eliminar cliente normal</button></form></div></article><?php endforeach; if(!$clientesNormales): ?><div class="note">Todavía no hay clientes normales añadidos.</div><?php endif; ?></div></section>
<section class="levels panel" id="niveles"><h2 style="margin-top:0">Configuración de niveles Premium</h2><div class="levelGrid"><?php foreach($niveles as $n): $cls='lv-'.strtolower($n['nivel']); ?><div class="levelCard <?=$cls?>"><div class="levelIcon"><?= levelIcon($n['nivel']) ?></div><h3><?=h($n['nivel'])?></h3><p><?= (int)$n['min_activos'] ?>+ referidos activos</p><div class="levelPrices"><span><?=euro($n['trimestral'])?></span><span><?=euro($n['semestral'])?></span><span><?=euro($n['anual'])?></span></div></div><?php endforeach; ?></div></section>
<section class="quickRefSearch panel" id="buscadorReferidosRapido">
  <h2>🔎 BUSCADOR RAPIDO DE REFERIDOS</h2>
  <input id="buscarReferidoRapido" oninput="mdFiltrarReferidosRapido()" placeholder="Buscar referido por nombre, referente, estado o fecha...">
  <div id="quickRefEmpty" class="quickRefEmpty">No se encontró ningún referido con esa búsqueda.</div>
  <div class="quickRefResults">
    <?php foreach($buscadorRefs as $br): 
      $qsearch = strtolower(($br['nombre']??'').' '.($br['cliente_nombre']??'').' '.($br['estado']??'').' '.($br['fecha_alta']??'').' '.($br['fecha_caducidad']??'').' '.($br['nota']??''));
    ?>
    <article class="quickRefCard" data-search="<?=h($qsearch)?>">
      <div class="quickRefTop">
        <div>
          <div class="quickRefName"><?=h($br['nombre'])?></div>
          <div class="quickRefMeta">Referente: <b><?=h($br['cliente_nombre'])?></b> · Alta: <?=h($br['fecha_alta'] ?: '-')?> · Caduca: <?=h($br['fecha_caducidad'] ?: 'Sin fecha')?></div>
          <div class="quickRefMeta"><?= $br['nota'] ? h($br['nota']) : 'Sin nota' ?></div>
        </div>
        <span class="status <?= $br['estado']==='Activo'?'act':'in' ?>"><?=h($br['estado'])?></span>
      </div>

      <div class="quickRefActions">
        <button class="btn dark small" type="button" onclick="mdEditarReferidoRapido(this)">Editar</button>
        <form method="post" onsubmit="return confirm('¿Seguro que quieres renovar este referido 3 meses?')">
          <input type="hidden" name="action" value="renew_ref"><input type="hidden" name="ref_id" value="<?=$br['id']?>"><input type="hidden" name="months" value="3">
          <button class="btn green small">+3 Meses</button>
        </form>
        <form method="post" onsubmit="return confirm('¿Seguro que quieres renovar este referido 6 meses?')">
          <input type="hidden" name="action" value="renew_ref"><input type="hidden" name="ref_id" value="<?=$br['id']?>"><input type="hidden" name="months" value="6">
          <button class="btn green small">+6 Meses</button>
        </form>
        <form method="post" onsubmit="return confirm('¿Seguro que quieres renovar este referido 12 meses?')">
          <input type="hidden" name="action" value="renew_ref"><input type="hidden" name="ref_id" value="<?=$br['id']?>"><input type="hidden" name="months" value="12">
          <button class="btn green small">+12 Meses</button>
        </form>
        <form method="post" onsubmit="return confirm('¿Seguro que quieres cambiar el estado de este referido?')">
          <input type="hidden" name="action" value="toggle_ref"><input type="hidden" name="ref_id" value="<?=$br['id']?>">
          <button class="btn small">Activo/Inactivo</button>
        </form>
      </div>

      <div class="quickRefEdit">
        <div class="quickRefEditHead">
          <h3>Editar referido</h3>
          <button class="btn dark btnXPerfil" type="button" onclick="mdCerrarReferidoRapido(this)">✕</button>
        </div>
        <form method="post" style="display:grid;gap:8px" onsubmit="return confirm('¿Guardar cambios de este referido?')">
          <input type="hidden" name="action" value="update_referido">
          <input type="hidden" name="ref_id" value="<?=$br['id']?>">
          <label>Nombre referido</label>
          <input name="nombre" value="<?=h($br['nombre'])?>" required>
          <label>Fecha alta</label>
          <input type="date" name="fecha_alta" value="<?=h($br['fecha_alta'])?>">
          <label>Fecha caducidad</label>
          <input type="date" name="fecha_caducidad" value="<?=h($br['fecha_caducidad'])?>">
          <label>Estado</label>
          <select name="estado">
            <option <?= $br['estado']==='Activo'?'selected':'' ?>>Activo</option>
            <option <?= $br['estado']!=='Activo'?'selected':'' ?>>Inactivo</option>
          </select>
          <label>Nota</label>
          <input name="nota" value="<?=h($br['nota'])?>" placeholder="Nota">
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px">
            <button class="btn">💾 Guardar cambios</button>
            <button type="button" class="btn dark" onclick="mdCerrarReferidoRapido(this)">✕ Salir sin guardar</button>
          </div>
        </form>
      </div>
    </article>
    <?php endforeach; ?>
  </div>
</section>


<section class="duplicadosPanel panel" id="duplicados">
  <div class="duplicadosHead">
    <h2>🔁 Referidos repetidos entre referentes</h2>
    <span class="duplicadosBadge"><?= $totalDuplicadosReferidos ?> posibles duplicados detectados</span>
  </div>
  <?php if($duplicadosReferidos): ?>
  <div class="duplicadosGrid">
    <?php foreach($duplicadosReferidos as $dup): ?>
    <article class="duplicadoCard">
      <div class="duplicadoTop">
        <div>
          <div class="duplicadoNombre"><?=h($dup['nombre_mostrado'])?></div>
          <div class="duplicadoMeta">Aparece <?= (int)$dup['repeticiones'] ?> veces en <?= (int)$dup['referentes_distintos'] ?> referentes distintos.</div>
          <div class="duplicadoMeta">Referentes: <b><?=h($dup['referentes'])?></b></div>
        </div>
        <span class="duplicadoAlert">REVISAR</span>
      </div>
      <div class="duplicadoDetalles">
        <?php foreach(explode('###', (string)$dup['detalles']) as $det): $parts=explode('|',$det); ?>
          <div class="duplicadoLinea">
            <b><?=h($parts[1] ?? 'Referente')?></b> · Estado: <?=h($parts[2] ?? '-')?> · Alta: <?=h(($parts[3] ?? '') ?: '-')?> · Caduca: <?=h(($parts[4] ?? '') ?: 'Sin fecha')?><?= !empty($parts[5] ?? '') ? ' · Nota: '.h($parts[5]) : '' ?>
          </div>
        <?php endforeach; ?>
      </div>
    </article>
    <?php endforeach; ?>
  </div>
  <?php else: ?>
    <div class="duplicadoOk">✅ No hay referidos repetidos entre diferentes referentes.</div>
  <?php endif; ?>
</section>

<section class="inactivosPanel panel" id="inactivos">
  <div class="inactivosHead">
    <h2>❌ Referidos inactivos</h2>
    <span class="inactivosBadge"><?= count($referidosInactivos) ?> inactivos · con referente asignado</span>
  </div>
  <div class="inactivosGrid">
    <?php foreach($referidosInactivosPagina as $ri): ?>
    <article class="inactivoCard">
      <div class="inactivoTop">
        <div>
          <div class="inactivoNombre"><?=h($ri['nombre'])?></div>
          <div class="inactivoMeta">Referente: <span class="inactivoRef"><?=h($ri['cliente_nombre'])?></span></div>
          <div class="inactivoMeta">Alta: <?=h($ri['fecha_alta'] ?: '-')?> · Caducidad: <?=h($ri['fecha_caducidad'] ?: 'Sin fecha')?></div>
          <div class="inactivoMeta"><?= $ri['nota'] ? h($ri['nota']) : 'Sin nota' ?></div>
        </div>
        <span class="inactivoEstado"><?=h($ri['estado'])?></span>
      </div>
      <div class="inactivoActions">
        <form method="post" onsubmit="return confirm('¿Renovar este referido 3 meses?')">
          <input type="hidden" name="action" value="renew_ref"><input type="hidden" name="ref_id" value="<?=$ri['id']?>"><input type="hidden" name="months" value="3">
          <button class="btn green small">+3 Meses</button>
        </form>
        <form method="post" onsubmit="return confirm('¿Renovar este referido 6 meses?')">
          <input type="hidden" name="action" value="renew_ref"><input type="hidden" name="ref_id" value="<?=$ri['id']?>"><input type="hidden" name="months" value="6">
          <button class="btn green small">+6 Meses</button>
        </form>
        <form method="post" onsubmit="return confirm('¿Renovar este referido 12 meses?')">
          <input type="hidden" name="action" value="renew_ref"><input type="hidden" name="ref_id" value="<?=$ri['id']?>"><input type="hidden" name="months" value="12">
          <button class="btn green small">+12 Meses</button>
        </form>
        <form method="post" onsubmit="return confirm('¿Cambiar este referido a activo/inactivo?')">
          <input type="hidden" name="action" value="toggle_ref"><input type="hidden" name="ref_id" value="<?=$ri['id']?>">
          <button class="btn small">Activo/Inactivo</button>
        </form>
        <button class="btn dark small" type="button" onclick="mdToggleFechaInactivo(this)">📅 Editar fecha</button>
        <form method="post" onsubmit="return confirm('¿Seguro que quieres eliminar definitivamente este referido inactivo? Esta acción no se puede deshacer.')">
          <input type="hidden" name="action" value="delete_ref"><input type="hidden" name="ref_id" value="<?=$ri['id']?>">
          <button class="btn red small">🗑️ Eliminar</button>
        </form>
      </div>
      <div class="inactivoFechaEdit">
        <form method="post" onsubmit="return confirm('¿Guardar nueva fecha de caducidad?')">
          <input type="hidden" name="action" value="update_fecha_inactivo">
          <input type="hidden" name="ref_id" value="<?=$ri['id']?>">
          <div>
            <label>Nueva fecha de caducidad</label>
            <input type="date" name="fecha_caducidad" value="<?=h($ri['fecha_caducidad'])?>" required>
          </div>
          <button class="btn small">💾 Guardar fecha</button>
        </form>
      </div>
    </article>
    <?php endforeach; ?>
    <?php if(!$referidosInactivos): ?><div class="note">No hay referidos inactivos actualmente.</div><?php endif; ?>
  </div>
  <?php if(count($referidosInactivos) > $porPagina): ?>
  <div class="mdPagination">
    <a class="btn small dark <?= $paginaInactivos <= 1 ? 'disabled' : '' ?>" href="<?=h(pageUrl('pagina_inactivos', $paginaInactivos-1))?>#inactivos">⬅ Anterior</a>
    <span class="mdPaginationInfo">Página <?= $paginaInactivos ?> de <?= $totalPagInactivos ?> · <?= count($referidosInactivos) ?> inactivos</span>
    <a class="btn small <?= $paginaInactivos >= $totalPagInactivos ? 'disabled' : '' ?>" href="<?=h(pageUrl('pagina_inactivos', $paginaInactivos+1))?>#inactivos">Siguiente ➜</a>
  </div>
  <?php endif; ?>
</section>
<section class="clientesSearchPanel panel" id="clientes">
  <h2>🔎 BUSCADOR RAPIDO DE CLIENTES REFERENTES</h2>
  <input id="buscarCliente" oninput="filtrarClientes()" placeholder="Buscar cliente por nombre, contacto, Telegram o nivel...">
</section>
<section class="clients">
<?php foreach($clientesPagina as $c): $act=(int)$c['activos'];$ina=(int)$c['inactivos'];$tot=(int)$c['total_refs'];$niv=nivelActual($act,$niveles);$nx=nextLevel($act,$niveles);$miniProgress=$nx?min(100,round(($act/(int)$nx['min_activos'])*100)):100;$faltan=$nx?((int)$nx['min_activos']-$act):0;$refs=$pdo->prepare("SELECT * FROM referidos WHERE cliente_id=? ORDER BY id DESC");$refs->execute([$c['id']]);$rs=$refs->fetchAll();$mid='modal_'.$c['id']; ?>
<article class="client panel" data-search="<?=h(strtolower($c['nombre'].' '.$c['contacto'].' '.$c['telefono'].' '.($c['telegram'] ?? '')) )?>"><span class="badge"><?=h($niv['icon'].' '.$niv['nivel'])?></span><h3><?=h($c['nombre'])?></h3><div class="muted">📱 <?=h($c['contacto'] ?: $c['telefono'] ?: 'Sin WhatsApp/contacto')?></div>
<div class="muted">✈️ <?php if(!empty($c['telegram'])): ?><a class="tgRefLink" href="https://t.me/<?=h(ltrim($c['telegram'],'@'))?>" target="_blank" rel="noopener">@<?=h(ltrim($c['telegram'],'@'))?></a><?php else: ?>Sin Telegram<?php endif; ?></div><div class="metrics"><div class="metric"><b><?= $act ?></b><span>Activos</span></div><div class="metric"><b><?= $ina ?></b><span>Inactivos</span></div><div class="metric"><b><?= $tot ?></b><span>Total</span></div></div><div class="miniProgress"><small><span><?= $nx ? 'Faltan '.$faltan.' para '.$nx['nivel'] : 'Nivel máximo' ?></span><span><?= $miniProgress ?>%</span></small><div class="miniBar"><span style="--w:<?=$miniProgress?>%"></span></div></div><div class="prices"><div class="price"><span>3M</span><b><?=euro($niv['trimestral'])?></b></div><div class="price"><span>6M</span><b><?=euro($niv['semestral'])?></b></div><div class="price"><span>12M</span><b><?=euro($niv['anual'])?></b></div></div><div class="note"><?= $c['nota'] ? h($c['nota']) : 'Sin nota' ?></div><div class="clientMainActions">
<button class="btn" onclick="openM('<?=$mid?>')" type="button">Gestionar</button>
<button class="btn dark" onclick="openM('<?=$mid?>','add')" type="button">+ Referido</button>
<?php if(!empty($c['telegram'])): ?>
<a class="btn btnTelegramRef" href="https://t.me/<?=h(ltrim($c['telegram'],'@'))?>" target="_blank" rel="noopener">✈️ Telegram</a>
<?php else: ?>
<button class="btn btnTelegramRef off" type="button" onclick="alert('Este referente no tiene alias de Telegram añadido. Edita el perfil y añade su @usuario.')">✈️ Sin Telegram</button>
<?php endif; ?>
<button class="btn green" onclick="return mdPerfilToggle(this)" type="button">⚙️ Editar perfil</button>
<form method="post" onsubmit="return confirm('⚠️ ¿Seguro que quieres eliminar este referente?\n\nSe borrará también TODO su listado de referidos y no se podrá recuperar desde el panel.')">
  <input type="hidden" name="action" value="delete_cliente">
  <input type="hidden" name="cliente_id" value="<?=$c['id']?>">
  <button class="btn red" type="submit">🗑️ Eliminar referente</button>
</form>
</div>
<div class="perfilReferenteBox">
  <div class="perfilReferenteHead">
    <h3>⚙️ Editar este referente</h3>
    <button class="btn dark btnXPerfil" type="button" onclick="return mdPerfilClose(this)">✕</button>
  </div>
  <form method="post" style="display:grid;gap:9px" onsubmit="return confirm('¿Guardar cambios de este referente?')">
    <input type="hidden" name="action" value="update_cliente">
    <input type="hidden" name="cliente_id" value="<?=$c['id']?>">
    <label>Nombre del referente</label>
    <input name="nombre" value="<?=h($c['nombre'])?>" required>
    <label>WhatsApp / Contacto del referente</label>
    <input name="contacto" value="<?=h($c['contacto'] ?: $c['telefono'])?>" placeholder="WhatsApp, teléfono o email">
    <label>Telegram del referente</label>
    <input name="telegram" value="<?=h($c['telegram'] ?? '')?>" placeholder="@usuarioTelegram">
    <label>Nota del referente</label>
    <textarea name="nota" placeholder="Nota del referente"><?=h($c['nota'])?></textarea>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px">
      <button class="btn">💾 Guardar perfil</button>
      <button type="button" class="btn dark" onclick="return mdPerfilClose(this)">✕ Salir sin guardar</button>
    </div>
  </form>
</div></article>
<div class="modal" id="<?=$mid?>"><div class="sheet panel"><div class="sheetHead panel"><div><h2><?=h($c['nombre'])?> <span class="gold"><?=h($niv['icon'].' '.$niv['nivel'])?></span></h2><div class="muted"><?= $act ?> activos · <?= $ina ?> inactivos · <?= $tot ?> total</div><div class="tabs"><a href="#res_<?=$mid?>">Resumen</a><a href="#add_<?=$mid?>">Añadir</a><a href="#refs_<?=$mid?>">Referidos</a><a href="#not_<?=$mid?>">Notas</a></div></div><button class="btn dark" onclick="closeM('<?=$mid?>')" type="button">✕</button></div><div class="modalBody"><aside class="panel box" id="res_<?=$mid?>"><h3>Resumen</h3><div class="metrics"><div class="metric"><b><?= $act ?></b><span>Activos</span></div><div class="metric"><b><?= $ina ?></b><span>Inactivos</span></div><div class="metric"><b><?= $tot ?></b><span>Total</span></div></div><div class="miniProgress"><small><span><?= $nx ? 'Faltan '.$faltan.' para '.$nx['nivel'] : 'Nivel máximo' ?></span><span><?= $miniProgress ?>%</span></small><div class="miniBar"><span style="--w:<?=$miniProgress?>%"></span></div></div><div class="prices"><div class="price"><span>3 meses</span><b><?=euro($niv['trimestral'])?></b></div><div class="price"><span>6 meses</span><b><?=euro($niv['semestral'])?></b></div><div class="price"><span>12 meses</span><b><?=euro($niv['anual'])?></b></div></div>
<?php
$copyAll = "━━━━━━━━━━━━━━━━━━\nCLIENTE: ".$c['nombre']."\nNIVEL: ".$niv['nivel']."\n━━━━━━━━━━━━━━━━━━\n\n";
$copyAct = "━━━━━━━━━━━━━━━━━━\nCLIENTE: ".$c['nombre']."\nREFERIDOS ACTIVOS\nNIVEL: ".$niv['nivel']."\n━━━━━━━━━━━━━━━━━━\n\n";
$copyIna = "━━━━━━━━━━━━━━━━━━\nCLIENTE: ".$c['nombre']."\nREFERIDOS INACTIVOS\nNIVEL: ".$niv['nivel']."\n━━━━━━━━━━━━━━━━━━\n\n";
$actCountTxt = 0;
$inaCountTxt = 0;
foreach($rs as $rr){
  $esActivo = (($rr['estado'] ?? '') === 'Activo');
  $linea = ($esActivo ? "✅ " : "❌ ") . ($rr['nombre'] ?? '') . "\n";
  $linea .= ($esActivo ? "Caduca: " : "Caducó: ") . fechaTxt($rr['fecha_caducidad'] ?? '') . "\n";
  if(!empty($rr['nota'])) $linea .= "Nota: ".$rr['nota']."\n";
  $linea .= "\n";
  $copyAll .= $linea;
  if($esActivo){ $copyAct .= $linea; $actCountTxt++; }
  else { $copyIna .= $linea; $inaCountTxt++; }
}
$copyAll .= "━━━━━━━━━━━━━━━━━━\nACTIVOS: ".$actCountTxt."\nINACTIVOS: ".$inaCountTxt."\nTOTAL: ".($actCountTxt+$inaCountTxt)."\n━━━━━━━━━━━━━━━━━━";
$copyAct .= "━━━━━━━━━━━━━━━━━━\nTOTAL ACTIVOS: ".$actCountTxt."\n━━━━━━━━━━━━━━━━━━";
$copyIna .= "━━━━━━━━━━━━━━━━━━\nTOTAL INACTIVOS: ".$inaCountTxt."\n━━━━━━━━━━━━━━━━━━";
?>
<div class="copyBox"><h3>📋 Copiar referidos</h3><div class="copyButtons"><button class="btn dark" type="button" data-copy="<?=h(base64_encode($copyAll))?>" onclick="mdCopyBtn(this)">📋 Copiar todos</button><button class="btn dark" type="button" data-copy="<?=h(base64_encode($copyAct))?>" onclick="mdCopyBtn(this)">✅ Copiar activos</button><button class="btn dark" type="button" data-copy="<?=h(base64_encode($copyIna))?>" onclick="mdCopyBtn(this)">❌ Copiar inactivos</button><button class="btn green" type="button" data-copy="<?=h(base64_encode($copyAll))?>" onclick="mdTelegramBtn(this)">✈️ Telegram</button></div></div>
<h3 id="add_<?=$mid?>">Añadir referido</h3><form method="post" style="display:grid;gap:9px"><input type="hidden" name="action" value="add_referido"><input type="hidden" name="cliente_id" value="<?=$c['id']?>"><input type="hidden" name="return_modal" value="m<?=$c['id']?>"><input name="nombre" placeholder="Nombre referido" required><label>Fecha alta</label><input type="date" name="fecha_alta" value="<?=$today?>"><label>Fecha caducidad</label><input type="date" name="fecha_caducidad"><select name="estado"><option>Activo</option><option>Inactivo</option></select><input name="nota" placeholder="Nota del referido"><button class="btn green">Guardar referido</button>
<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:6px;margin-top:8px">
<button type="button" class="btn dark small" onclick="mdSetMonths(this,3)">✅ +3 Meses</button>
<button type="button" class="btn dark small" onclick="mdSetMonths(this,6)">✅ +6 Meses</button>
<button type="button" class="btn dark small" onclick="mdSetMonths(this,12)">✅ +12 Meses</button>
</div></form></aside><section class="panel box"><h3 id="refs_<?=$mid?>">Referidos</h3><div class="refList"><?php foreach($rs as $r): ?><article class="ref"><div class="refTop"><div><b><?=h($r['nombre'])?></b><div class="muted">Alta: <?=h($r['fecha_alta']?:'-')?> · Caduca: <?=h($r['fecha_caducidad']?:'Sin fecha')?></div></div><span class="status <?= $r['estado']==='Activo'?'act':'in' ?>"><?=h($r['estado'])?></span></div><div class="note" style="margin-top:8px"><?= $r['nota'] ? h($r['nota']) : 'Sin nota' ?></div><div class="refActions"><button class="btn dark small" type="button" onclick="this.closest('.ref').classList.toggle('edit')">Editar</button><form method="post"><input type="hidden" name="action" value="toggle_ref"><input type="hidden" name="ref_id" value="<?=$r['id']?>"><button class="btn small">Activo/Inactivo</button></form><form method="post" onsubmit="return confirm('¿Eliminar referido?')"><input type="hidden" name="action" value="delete_ref"><input type="hidden" name="ref_id" value="<?=$r['id']?>"><button class="btn red small">Eliminar</button></form></div>
<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:6px;margin-top:8px">
<form method="post"><input type="hidden" name="action" value="renew_ref"><input type="hidden" name="ref_id" value="<?=$r['id']?>"><input type="hidden" name="months" value="3"><button class="btn green small">+3 Meses</button></form>
<form method="post"><input type="hidden" name="action" value="renew_ref"><input type="hidden" name="ref_id" value="<?=$r['id']?>"><input type="hidden" name="months" value="6"><button class="btn green small">+6 Meses</button></form>
<form method="post"><input type="hidden" name="action" value="renew_ref"><input type="hidden" name="ref_id" value="<?=$r['id']?>"><input type="hidden" name="months" value="12"><button class="btn green small">+12 Meses</button></form>
</div><div class="editBox"><form method="post" style="display:grid;gap:8px"><input type="hidden" name="action" value="update_referido"><input type="hidden" name="ref_id" value="<?=$r['id']?>"><input name="nombre" value="<?=h($r['nombre'])?>" required><input type="date" name="fecha_alta" value="<?=h($r['fecha_alta'])?>"><input type="date" name="fecha_caducidad" value="<?=h($r['fecha_caducidad'])?>"><select name="estado"><option <?= $r['estado']==='Activo'?'selected':'' ?>>Activo</option><option <?= $r['estado']!=='Activo'?'selected':'' ?>>Inactivo</option></select><input name="nota" value="<?=h($r['nota'])?>" placeholder="Nota"><button class="btn">Guardar cambios</button></form></div></article><?php endforeach; if(!$rs): ?><div class="note">Sin referidos todavía.</div><?php endif; ?></div><h3 id="not_<?=$mid?>" style="margin-top:18px">Datos y nota del cliente</h3><form method="post" style="display:grid;gap:9px"><input type="hidden" name="action" value="update_cliente"><input type="hidden" name="cliente_id" value="<?=$c['id']?>"><input name="nombre" value="<?=h($c['nombre'])?>" required><input name="contacto" value="<?=h($c['contacto'] ?: $c['telefono'])?>" placeholder="Contacto"><textarea name="nota" placeholder="Nota del cliente"><?=h($c['nota'])?></textarea><button class="btn">Guardar cliente</button></form><form method="post" onsubmit="return confirm('⚠️ ¿Seguro que quieres eliminar este referente?\n\nSe borrará también TODO su listado de referidos y no se podrá recuperar desde el panel.')" style="margin-top:10px"><input type="hidden" name="action" value="delete_cliente"><input type="hidden" name="cliente_id" value="<?=$c['id']?>"><button class="btn red">🗑️ Eliminar referente completo</button></form></section></div></div></div>
<?php endforeach; ?></section>
<?php if(count($clientes) > $porPagina): ?>
<div class="mdPagination" id="clientesPaginacion">
  <a class="btn small dark <?= $paginaClientes <= 1 ? 'disabled' : '' ?>" href="<?=h(pageUrl('pagina_clientes', $paginaClientes-1))?>#clientes">⬅ Anterior</a>
  <span class="mdPaginationInfo">Página <?= $paginaClientes ?> de <?= $totalPagClientes ?> · <?= count($clientes) ?> clientes</span>
  <a class="btn small <?= $paginaClientes >= $totalPagClientes ? 'disabled' : '' ?>" href="<?=h(pageUrl('pagina_clientes', $paginaClientes+1))?>#clientes">Siguiente ➜</a>
</div>
<?php endif; ?>
</main></div><nav class="mobileNav"><a href="#dashboard">Inicio</a><a href="#clientes">Clientes</a><a href="#addCliente">Añadir</a><a href="#duplicados">Repetidos</a></nav><script>let sy=0;function openM(id,target){sy=scrollY;document.getElementById(id).classList.add('open');document.body.style.overflow='hidden';setTimeout(()=>{if(target==='add'){let e=document.getElementById('add_'+id);if(e)e.scrollIntoView({behavior:'smooth',block:'start'});}},120)}function closeM(id){document.getElementById(id).classList.remove('open');document.body.style.overflow='';scrollTo(0,sy)}document.addEventListener('keydown',e=>{if(e.key==='Escape'){document.querySelectorAll('.modal.open').forEach(m=>m.classList.remove('open'));document.body.style.overflow='';}});function filtrarClientes(){let q=(document.getElementById('buscarCliente').value||'').toLowerCase();document.querySelectorAll('.client[data-search]').forEach(c=>{c.style.display=c.dataset.search.includes(q)?'block':'none';});}</script>
<style>
/* ===== MDPRIME FIX FINAL: mantener modal abierto + scroll móvil ===== */
@media(max-width:760px){
  .modal,.modal.open{
    overflow-y:auto!important;
    overflow-x:hidden!important;
    -webkit-overflow-scrolling:touch!important;
    touch-action:pan-y!important;
  }
  .sheet,.modalSheet{
    overflow-y:auto!important;
    overflow-x:hidden!important;
    height:100dvh!important;
    min-height:100dvh!important;
    max-height:100dvh!important;
    -webkit-overflow-scrolling:touch!important;
  }
  .modalBody{
    display:block!important;
    overflow-y:visible!important;
    overflow-x:hidden!important;
    height:auto!important;
    min-height:calc(100dvh - 150px)!important;
    max-height:none!important;
    padding-bottom:135px!important;
    -webkit-overflow-scrolling:touch!important;
  }
  .sheetHead,.modalHead{
    position:sticky!important;
    top:0!important;
    z-index:999!important;
  }
}
</style>

<script>
document.addEventListener('submit', function(e){
  const modal = e.target.closest('.modal');
  if(modal && modal.id && !e.target.querySelector('input[name="return_modal"]')){
    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'return_modal';
    input.value = modal.id;
    e.target.appendChild(input);
  }
}, true);

document.addEventListener('DOMContentLoaded', function(){
  const params = new URLSearchParams(window.location.search);
  const openId = params.get('open');
  if(openId && document.getElementById(openId)){
    setTimeout(function(){ openM(openId); }, 150);
  }
});
</script>

<style>
.copyBox{margin-top:12px;border:1px solid rgba(245,197,66,.28);background:linear-gradient(135deg,rgba(245,197,66,.10),rgba(255,255,255,.035));border-radius:18px;padding:13px}.copyBox h3{margin:0 0 10px}.copyButtons{display:grid;grid-template-columns:repeat(2,1fr);gap:8px}.copyButtons .btn{width:100%}.copyToast{position:fixed;left:50%;bottom:90px;transform:translateX(-50%);z-index:99999;background:linear-gradient(135deg,#22c55e,#14b8a6);color:#fff;font-weight:1000;padding:12px 18px;border-radius:999px;box-shadow:0 18px 50px rgba(0,0,0,.45);display:none}.copyToast.show{display:block}@media(max-width:760px){.copyButtons{grid-template-columns:1fr!important}.copyBox{padding:12px!important;border-radius:16px!important}}
</style>
<div id="copyToast" class="copyToast">Referidos copiados</div>
<script>
function mdDecodeB64(b64){try{const bin=atob(b64);const bytes=new Uint8Array(bin.length);for(let i=0;i<bin.length;i++)bytes[i]=bin.charCodeAt(i);return new TextDecoder('utf-8').decode(bytes);}catch(e){try{return decodeURIComponent(escape(atob(b64)));}catch(err){return '';}}}
function mdToast(msg){let t=document.getElementById('copyToast');if(!t){t=document.createElement('div');t.id='copyToast';t.className='copyToast';document.body.appendChild(t);}t.textContent=msg||'Referidos copiados';t.classList.add('show');setTimeout(()=>t.classList.remove('show'),1800);}
function mdFallbackCopy(text){const ta=document.createElement('textarea');ta.value=text;ta.setAttribute('readonly','');ta.style.position='fixed';ta.style.top='0';ta.style.left='0';ta.style.opacity='0';document.body.appendChild(ta);ta.focus();ta.select();ta.setSelectionRange(0,ta.value.length);let ok=false;try{ok=document.execCommand('copy');}catch(e){ok=false;}document.body.removeChild(ta);if(ok)mdToast('Referidos copiados');else prompt('Copia este texto:',text);}
function mdCopyRaw(text){text=String(text||'');if(!text.trim()){mdToast('No hay datos para copiar');return;}if(navigator.clipboard&&window.isSecureContext){navigator.clipboard.writeText(text).then(()=>mdToast('Referidos copiados')).catch(()=>mdFallbackCopy(text));}else mdFallbackCopy(text);}
function mdCopyBtn(btn){mdCopyRaw(mdDecodeB64(btn.getAttribute('data-copy')||''));}
function mdTelegramBtn(btn){const text=mdDecodeB64(btn.getAttribute('data-copy')||'');mdCopyRaw(text);window.open('https://t.me/share/url?url=&text='+encodeURIComponent(text),'_blank');}
</script>

<script>
function mdSetMonths(btn,months){
  var form = btn.closest('form');
  if(!form) return;

  var cad = form.querySelector('input[name="fecha_caducidad"]');
  if(!cad) return;

  var base = new Date();
  if(cad.value){
    var d = new Date(cad.value + 'T00:00:00');
    if(!isNaN(d.getTime())) base = d;
  }

  base.setMonth(base.getMonth() + months);

  var y = base.getFullYear();
  var m = String(base.getMonth() + 1).padStart(2,'0');
  var d2 = String(base.getDate()).padStart(2,'0');

  cad.value = y + '-' + m + '-' + d2;
}
</script>

<script id="mdPerfilReferenteSoloJs">
function mdPerfilToggle(btn){
  var card = btn.closest('.client');
  if(!card) return false;
  var box = card.querySelector('.perfilReferenteBox');
  if(!box) return false;
  box.classList.toggle('open');
  if(box.classList.contains('open')){
    setTimeout(function(){ box.scrollIntoView({behavior:'smooth', block:'center'}); }, 60);
  }
  return false;
}
function mdPerfilClose(btn){
  var box = btn.closest('.perfilReferenteBox');
  if(box) box.classList.remove('open');
  return false;
}
</script>

<script id="mdBuscadorReferidosJs">
function mdFiltrarReferidosRapido(){
  var input = document.getElementById('buscarReferidoRapido');
  if(!input) return;
  var q = (input.value || '').toLowerCase().trim();
  var cards = document.querySelectorAll('.quickRefCard');
  var empty = document.getElementById('quickRefEmpty');
  var count = 0;

  cards.forEach(function(card){
    card.classList.remove('editing');
    if(q.length < 1){
      card.classList.remove('show');
      return;
    }
    var ok = (card.getAttribute('data-search') || '').includes(q);
    card.classList.toggle('show', ok);
    if(ok) count++;
  });

  if(empty) empty.classList.toggle('show', q.length > 0 && count === 0);
}
function mdEditarReferidoRapido(btn){
  var card = btn.closest('.quickRefCard');
  if(!card) return false;
  document.querySelectorAll('.quickRefCard.editing').forEach(function(c){
    if(c !== card) c.classList.remove('editing');
  });
  card.classList.add('editing');
  setTimeout(function(){ card.scrollIntoView({behavior:'smooth', block:'center'}); }, 60);
  return false;
}
function mdCerrarReferidoRapido(btn){
  var card = btn.closest('.quickRefCard');
  if(card) card.classList.remove('editing');
  return false;
}
</script>

<script id="mdInactivosFechaJs">
function mdToggleFechaInactivo(btn){
  var card = btn.closest('.inactivoCard');
  if(!card) return false;
  var box = card.querySelector('.inactivoFechaEdit');
  if(!box) return false;
  document.querySelectorAll('.inactivoFechaEdit.open').forEach(function(el){
    if(el !== box) el.classList.remove('open');
  });
  box.classList.toggle('open');
  if(box.classList.contains('open')){
    setTimeout(function(){ box.scrollIntoView({behavior:'smooth', block:'center'}); }, 60);
  }
  return false;
}
</script>
</body></html>
