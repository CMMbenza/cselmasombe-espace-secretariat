<?php
require_once('../../webapp/database/config.php'); // $con (mysqli)
require_once('annee_scolaire.encours.php');

/* Helpers */
function s($k){ return isset($_POST[$k]) ? trim((string)$_POST[$k]) : ''; }

if (isset($_POST['submit']) || isset($_POST['submit_continue'])) {
  // ===== CREATION =====
  $noms           = s('noms');
  $nom_du_pere    = s('nom_du_pere');
  $nom_de_la_mere = s('nom_de_la_mere');
  $profesion      = s('profesion');
  $telephone      = s('telephone');
  $numero         = s('numero');
  $avenue         = s('avenue');
  $quartier       = s('quartier');
  $commune        = s('commune');
  $province       = s('province');
  $email          = s('email');
  $montantAPayer  = 0.00;

  $sql = "INSERT INTO menage (
            noms, nom_du_pere, nom_de_la_mere, profesion, telephone, numero, avenue, 
            quartier, commune, province, dateCreated, dateUpdate, 
            createdby, anneeScolaire, montantAPayer, email, STATUS, start_tranche
          ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, 'Administrateur(trice)', ?, ?, ?, 'actif', 1)";
          
  $stmt = $con->prepare($sql);
  
  // Correction ici : "ssssssssssdss" (13 caractères pour 13 variables)
  $stmt->bind_param(
    "ssssssssssdss", 
    $noms, $nom_du_pere, $nom_de_la_mere, $profesion, $telephone, $numero, $avenue, 
    $quartier, $commune, $province, $annee_scolaire, $montantAPayer, $email
  );
  $stmt->execute();
  $stmt->close();

  if (isset($_POST['submit_continue'])) {
    header("Location: ../../view/eleve/create-update.php?noms=" . urlencode($noms));
  } else {
    header("Location: ../../view/menage/?noms=" . urlencode($noms));
  }
  exit;

} elseif (isset($_POST['update']) || isset($_POST['update_continue'])) {
  // ===== MISE A JOUR =====
  $id = (int)($_POST['id'] ?? 0);
  if ($id <= 0) { return; }

  $noms           = s('noms');
  $nom_du_pere    = s('nom_du_pere');
  $nom_de_la_mere = s('nom_de_la_mere');
  $profesion      = s('profesion');
  $telephone      = s('telephone');
  $numero         = s('numero');
  $avenue         = s('avenue');
  $quartier       = s('quartier');
  $commune        = s('commune');
  $province       = s('province');
  $start_tranche  = (int)s('start_tranche');
  $email          = s('email');

  $sql = "UPDATE menage SET
            noms = ?, 
            nom_du_pere = ?, 
            nom_de_la_mere = ?, 
            profesion = ?,
            telephone = ?, 
            numero = ?, 
            avenue = ?, 
            quartier = ?, 
            commune = ?, 
            province = ?,
            dateUpdate = CURRENT_TIMESTAMP,
            createdby = 'Administrateur(trice)',
            anneeScolaire = ?,
            start_tranche = ?,
            email = ?
          WHERE id = ?";
          
  $stmt = $con->prepare($sql);
  
  // Correction ici : "ssssssssssisi" (14 caractères pour 14 variables)
  $stmt->bind_param(
    "sssssssssssisi", 
    $noms, $nom_du_pere, $nom_de_la_mere, $profesion, $telephone, $numero, $avenue, 
    $quartier, $commune, $province, $annee_scolaire, $start_tranche, $email, $id
  );
  $stmt->execute();
  $stmt->close();
  
  if (isset($_POST['update_continue'])) {
    header("Location: ../../view/eleve/create-update.php?id_menage=" . $id); 
  } else {
    header("Location: ../../view/menage/?noms=" . urlencode($noms));
  }
  exit;

} else {
  if (isset($_GET['id']) && isset($_GET['status'])) {
    $id = (int) $_GET['id'];
    $status = $_GET['status'];

    if (!in_array($status, ['actif', 'inactif'])) {
      die('Statut invalide');
    }

    $stmt = $con->prepare("UPDATE menage SET STATUS = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $id);
    $stmt->execute();
    $stmt->close();

    $stmtEleve = $con->prepare("UPDATE eleve SET STATUS = ? WHERE menage = ?");
    $stmtEleve->bind_param("si", $status, $id);
    $stmtEleve->execute();
    $stmtEleve->close();

    header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '../menage/'));
    exit;
  }
}