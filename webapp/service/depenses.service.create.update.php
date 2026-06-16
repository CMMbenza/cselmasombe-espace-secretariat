<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) { session_start(); }

require_once('../../webapp/database/config.php');   // doit fournir $con (mysqli)
require_once('annee_scolaire.encours.php');         // peut fournir $annee_scolaire (ex: "2024-2025")

/* ---------- Helpers ---------- */
if (!function_exists('e')) {
  function e($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
}
if (!function_exists('school_year_from_date')) {
  function school_year_from_date(string $ymd): string {
    $d = DateTime::createFromFormat('Y-m-d', $ymd) ?: new DateTime();
    $y = (int)$d->format('Y');
    $m = (int)$d->format('n');
    return ($m >= 9) ? sprintf('%d-%d', $y, $y+1) : sprintf('%d-%d', $y-1, $y);
  }
}

/** @var mysqli $con */
if (!isset($con) || !($con instanceof mysqli)) {
  $depense_error = "Connexion MySQL indisponible. Vérifie /webapp/database/config.php.";
  $depense_success = '';
  return;
}
$con->set_charset('utf8mb4');

$depense_error   = $depense_error   ?? '';
$depense_success = $depense_success ?? '';

/* ---------- Traitement POST (INSERT dans `depenses`) ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {

  $beneficiaire = trim($_POST['beneficiaire'] ?? '');
  $reference    = trim($_POST['reference'] ?? '');
  $description  = trim($_POST['description'] ?? '');
  $montantStr   = trim($_POST['montant'] ?? '');
  $date_depense = trim($_POST['date_depense'] ?? '');

  // Validations
  if ($reference === '' || !in_array($reference, ['Frais Scolaire','Frais Connexe'], true)) {
    $depense_error = "Veuillez choisir une référence valide (Frais Scolaire / Frais Connexe).";
  } elseif ($beneficiaire === '') {
    $depense_error = "Le bénéficiaire est requis.";
  } elseif ($description === '') {
    $depense_error = "Le motif (description) est requis.";
  } elseif ($montantStr === '' || !is_numeric($montantStr) || (float)$montantStr <= 0) {
    $depense_error = "Montant invalide.";
  } elseif ($date_depense === '' || !preg_match('~^\d{4}-\d{2}-\d{2}$~', $date_depense)) {
    $depense_error = "Date de dépense invalide.";
  }

  if ($depense_error === '') {
    try {
      $montant = (float)$montantStr;

      // Qui a créé ?
      $createdby = 'system';
      if (!empty($_SESSION['username'])) {
        $createdby = (string)$_SESSION['username'];
      } elseif (!empty($_SESSION['user_id'])) {
        $createdby = 'user#'.(string)$_SESSION['user_id'];
      }

      // Année scolaire : si fournie par le fichier, sinon calculée
      $anneeEff = (!empty($annee_scolaire)) ? $annee_scolaire : school_year_from_date($date_depense);

      // === Sécuriser l'attribution du numero_reference par (reference, anneeScolaire) ===
      // 1) Démarrer une transaction
      if (!$con->begin_transaction()) {
        throw new RuntimeException("Impossible de démarrer la transaction.");
      }

      // 2) Verrouiller la "plage" via SELECT ... FOR UPDATE
      //    On prend le max actuel pour cette référence et cette année scolaire
      $sqlMax = "
        SELECT COALESCE(MAX(numero_reference), 0) AS last_num
        FROM depenses
        WHERE reference = ? AND anneeScolaire = ?
        FOR UPDATE
      ";
      $stMax = $con->prepare($sqlMax);
      if (!$stMax) { throw new RuntimeException("Préparation SELECT MAX() échouée."); }
      $stMax->bind_param('ss', $reference, $anneeEff);
      $stMax->execute();
      $resMax = $stMax->get_result();
      $rowMax = $resMax->fetch_assoc();
      $stMax->close();

      $nextNumero = (int)($rowMax['last_num'] ?? 0) + 1; // s'il n'y a rien, on commence à 1

      // 3) INSERT explicite (inclut numero_reference)
      $sql = "INSERT INTO depenses
              (beneficiaire, reference, numero_reference, description, montant, dateCreaty, dateUpdate, createdby, anneeScolaire)
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
      $st = $con->prepare($sql);
      if (!$st) { throw new RuntimeException("Préparation INSERT échouée."); }

      $dateUpdate = $date_depense; // au create, = dateCreaty

      // Types: s s i s d s s s s
      $st->bind_param(
        'ssisdssss',
        $beneficiaire,
        $reference,
        $nextNumero,
        $description,
        $montant,
        $date_depense,
        $dateUpdate,
        $createdby,
        $anneeEff
      );
      $st->execute();
      $newId = $con->insert_id;
      $st->close();

      // 4) Valider la transaction
      $con->commit();

      $depense_success = "Dépense enregistrée avec succès (ID #{$newId}, N° {$nextNumero} pour « {$reference} », Année {$anneeEff}).";
      $depense_error = '';

      // Optionnel: vider les valeurs postées (garde la date si tu veux)
      unset($_POST['beneficiaire'], $_POST['reference'], $_POST['description'], $_POST['montant']);

    } catch (Throwable $t) {
      // rollback si échec
      if ($con->errno === 0) {
        // Si begin_transaction avait réussi, tenter un rollback
        @ $con->rollback();
      }
      $depense_error = "Erreur lors de l'enregistrement : " . $t->getMessage();
      $depense_success = '';
    }
  } else {
    $depense_success = '';
  }
}
