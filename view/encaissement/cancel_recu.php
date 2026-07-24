<?php
// cancel_recu.php
require_once('../../webapp/database/config.php');
header('Content-Type: application/json; charset=UTF-8');

function jdie($ok, $msg, $extra = []) {
  http_response_code($ok ? 200 : 400);
  echo json_encode(array_merge(['ok'=>$ok,'message'=>$msg], $extra));
  exit;
}

$recu_id   = isset($_POST['recu_id'])   ? (int)$_POST['recu_id'] : 0;
$recu_type = isset($_POST['recu_type']) ? trim($_POST['recu_type']) : '';
$code      = isset($_POST['code'])      ? trim($_POST['code']) : '';
$motif     = isset($_POST['motif'])     ? trim($_POST['motif']) : '';

if ($recu_id <= 0 || !in_array($recu_type, ['scolaire','connexe'], true)) {
  jdie(false, "Paramètres invalides.");
}
if ($code !== '1024') {
  jdie(false, "Code d’annulation incorrect.");
}

// Vérifier si déjà annulé
$st = $con->prepare("SELECT 1 FROM recu_annule WHERE recu_id=? AND recu_type=? LIMIT 1");
$st->bind_param('is', $recu_id, $recu_type);
$st->execute();
$st->store_result();
if ($st->num_rows > 0) {
  $st->close();
  jdie(false, "Ce reçu est déjà annulé.");
}
$st->close();

// Récupérer info reçu source
if ($recu_type === 'scolaire') {
  $sql = "SELECT p.id, p.menage AS menage_id, p.montantPayer, p.dateCreated
          FROM paiement p
          WHERE p.id = ?";
} else {
  $sql = "SELECT pd.id, pd.menage AS menage_id, pd.montantPayer, pd.dateCreated
          FROM paiement_divers pd
          WHERE pd.id = ?";
}
$st = $con->prepare($sql);
$st->bind_param('i', $recu_id);
$st->execute();
$rs = $st->get_result();
if ($rs->num_rows === 0) {
  $st->close();
  jdie(false, "Reçu introuvable.");
}
$src = $rs->fetch_assoc();
$st->close();

// Insérer dans recu_annule
$cancelled_by = isset($_SESSION['username']) ? (string)$_SESSION['username'] : 'system';
$ins = $con->prepare("
  INSERT INTO recu_annule (recu_id, recu_type, menage_id, montant_payer, date_recu, motif, code_saisi, cancelled_by)
  VALUES (?,?,?,?,?,?,?,?)
");
$date_recu = $src['dateCreated'];
$montant   = (float)$src['montantPayer'];
$menage_id = (int)$src['menage_id'];
$ins->bind_param('isidssss', $recu_id, $recu_type, $menage_id, $montant, $date_recu, $motif, $code, $cancelled_by);
$ok = $ins->execute();
$ins->close();

if (!$ok) {
  jdie(false, "Échec enregistrement annulation.");
}

// Réponse OK
jdie(true, "Reçu annulé avec succès.", [
  'recu_id' => $recu_id,
  'recu_type' => $recu_type,
  'montant' => number_format($montant, 2, '.', ' ')
]);
