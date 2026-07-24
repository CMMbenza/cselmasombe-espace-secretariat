<?php
require_once('../../webapp/database/config.php'); // $con (mysqli)
require_once('annee_scolaire.encours.php');

/* Helpers */
function s($k){ return isset($_POST[$k]) ? trim((string)$_POST[$k]) : ''; }

if (isset($_POST['submit']) || isset($_POST['submit_continue'])) {
  // ===== CREATION =====
  $noms       = s('noms');
  $telephone  = s('telephone');
  $numero     = s('numero');
  $avenue     = s('avenue');
  $quartier   = s('quartier');
  $commune    = s('commune');
  $email    = s('email');
  $montantAPayer = 0.00;

  $sql = "INSERT INTO menage (noms, telephone, numero, avenue, quartier, commune, dateCreated, dateUpdate, createdby, anneeScolaire, montantAPayer, email, STATUS, start_tranche)
          VALUES (?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, 'Administrateur(trice)', ?, ?, ?, 'actif', 1)";
  $stmt = $con->prepare($sql);
  $stmt->bind_param("sssssssds", $noms, $telephone, $numero, $avenue, $quartier, $commune, $annee_scolaire, $montantAPayer, $email);
  $stmt->execute();
  $stmt->close();
  // Reload pour refléter la nouvelle valeur      

    if (isset($_POST['submit_continue'])) {
      header("Location: ../../view/eleve/create-update.php?$noms");
    }else {
      // Reload pour refléter la nouvelle valeur
      header("Location: ../../view/menage/?$noms");
    }
    exit;

} elseif (isset($_POST['update']) || isset($_POST['update_continue'])) {
  // ===== MISE A JOUR =====
  $id         = (int)($_POST['id'] ?? 0);
  if ($id <= 0) { return; }

  $noms       = s('noms');
  $telephone  = s('telephone');
  $numero     = s('numero');
  $avenue     = s('avenue');
  $quartier   = s('quartier');
  $commune    = s('commune');
  $start_tranche    = s('start_tranche');
  $email    = s('email');

  $sql = "UPDATE menage SET
            noms = ?, telephone = ?, numero = ?, avenue = ?, quartier = ?, commune = ?,
            dateUpdate = CURRENT_TIMESTAMP,
            createdby = 'Administrateur(trice)',
            anneeScolaire = ?,
            start_tranche = ?,
            email = ?
          WHERE id = ?";
  $stmt = $con->prepare($sql);
  $stmt->bind_param("sssssssisi", $noms, $telephone, $numero, $avenue, $quartier, $commune, $annee_scolaire, $start_tranche, $email, $id);
  $stmt->execute();
  $stmt->close();
  
   if (isset($_POST['update_continue'])) {
        header("Location: ../../view/eleve/create-update.php?id_menage=" . $id); 
    }else {
        // Reload pour refléter la nouvelle valeur
        header("Location: ../../view/menage/?$noms");
    }

    exit;
}else {
  if(isset($_GET['id']) && isset($_GET['status'])){

    $id = (int) $_GET['id'];
    $status = $_GET['status'];

    // sécurité
    if(!in_array($status, ['actif','inactif'])){
        die('Statut invalide');
    }

    $sql = "UPDATE menage SET status = '$status' WHERE id = $id";
    mysqli_query($con, $sql);

    $sqlEleve = "UPDATE eleve SET status = '$status' WHERE menage = $id";
    mysqli_query($con, $sqlEleve);

  // retour à la page précédente
  header("Location: " . $_SERVER['HTTP_REFERER']);
  exit;
  }
}