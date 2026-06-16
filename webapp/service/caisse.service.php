<?php
declare(strict_types=1);

/**
 * Service Caisse :
 * - Colonne gauche : cycles / solde annuel (inchangé)
 * - Colonne droite : 2 résumés mensuels + virements distincts
 *     1) Frais Scolaire = Paiements (paiement) - Dépenses(reference='Frais Scolaire')
 *     2) Frais Divers   = Paiements (paiement_divers) - Dépenses(reference='Frais Connexe')
 */

if (session_status() === PHP_SESSION_NONE) { session_start(); }

require_once('../../webapp/database/config.php');
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
/** @var mysqli $con */
$con->set_charset('utf8mb4');

/* ================== Helpers ================== */
if (!function_exists('e')) {
  function e($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
}
if (!function_exists('school_year_from_month')) {
  function school_year_from_month(int $y, int $m): string {
    // Année scolaire: Septembre (9) -> Août (8)
    return ($m >= 9) ? sprintf('%d-%d', $y, $y+1) : sprintf('%d-%d', $y-1, $y);
  }
}

/* ================== Colonne gauche (tes calculs existants) ================== */
require_once('annee_scolaire.encours.php'); // expose $annee_scolaire si dispo

// Par cycles (eleve / classe / cycle)
$query_cycles = "
    SELECT c.description AS cycle_description,
           SUM(e.montant_a_payer) AS total_montant
    FROM eleve e
    JOIN classe cl ON e.classe = cl.id
    JOIN cycle  c  ON cl.cycle = c.id
    GROUP BY c.id, c.description
";
$r_cycles = mysqli_query($con, $query_cycles);
$cycles = [];
while ($row = mysqli_fetch_assoc($r_cycles)) { $cycles[] = $row; }
$colors = ['success','danger','primary'];

// Somme globale (eleve)
$query_total = "SELECT SUM(e.montant_a_payer) AS somme_globale FROM eleve e";
$r_total = mysqli_query($con, $query_total);
$somme_globale = 0.0;
if ($rt = mysqli_fetch_assoc($r_total)) { $somme_globale = (float)($rt['somme_globale'] ?? 0); }

/* ================== Résumés mensuels (colonne droite) ================== */
/* Inputs */
$mois_param = trim($_GET['mois'] ?? '');  // attendu: YYYY-MM
if (!preg_match('~^\d{4}-\d{2}$~', $mois_param)) {
  $mois_param = date('Y-m');              // défaut: mois courant
}
$periode_annee = (int)substr($mois_param, 0, 4);
$periode_mois  = (int)substr($mois_param, 5, 2);

// Année scolaire : GET > fichier > déduction
$annee_scolaire_sel = $_GET['annee_scolaire'] ?? ($annee_scolaire ?? null);
if (!$annee_scolaire_sel) {
  $annee_scolaire_sel = school_year_from_month($periode_annee, $periode_mois);
}

$firstDay = sprintf('%04d-%02d-01', $periode_annee, $periode_mois);
$lastDay  = date('Y-m-t', strtotime($firstDay));
$firstTS  = $firstDay.' 00:00:00';
$lastTS   = $lastDay.' 23:59:59';

/* ----- Scolaire ----- */
$total_paiements_mois        = 0.0;  // table paiement
$total_depenses_scolaire_mois= 0.0;  // depenses.reference='Frais Scolaire'
$net_mois_scolaire           = 0.0;
$flash_success               = '';
$flash_error                 = '';
$deja_verse                  = false; // virement scolaire déjà fait ?

/* ----- Divers ----- */
$total_paiements_divers_mois = 0.0;  // table paiement_divers
$total_depenses_connexe_mois = 0.0;  // depenses.reference='Frais Connexe'
$net_mois_divers             = 0.0;
$flash_success_divers        = '';
$flash_error_divers          = '';
$deja_verse_divers           = false; // virement divers déjà fait ?

try {
  /* === Paiements (scolaire) === */
  $sqlP = "SELECT COALESCE(SUM(montantPayer),0) AS s
           FROM paiement
           WHERE anneeScolaire = ?
             AND dateCreated BETWEEN ? AND ?";
  $st = $con->prepare($sqlP);
  $st->bind_param('sss', $annee_scolaire_sel, $firstDay, $lastDay);
  $st->execute();
  $r = $st->get_result()->fetch_assoc();
  $st->close();
  $total_paiements_mois = (float)($r['s'] ?? 0);

  /* === Dépenses SCOLAIRES === */
  $sqlDS = "SELECT COALESCE(SUM(montant),0) AS s
            FROM depenses
            WHERE anneeScolaire = ?
              AND reference = 'Frais Scolaire'
              AND dateCreaty BETWEEN ? AND ?";
  $st = $con->prepare($sqlDS);
  $st->bind_param('sss', $annee_scolaire_sel, $firstDay, $lastDay);
  $st->execute();
  $r = $st->get_result()->fetch_assoc();
  $st->close();
  $total_depenses_scolaire_mois = (float)($r['s'] ?? 0);

  $net_mois_scolaire = $total_paiements_mois - $total_depenses_scolaire_mois;

  /* === Paiements DIVERS === */
  $sqlPD = "SELECT COALESCE(SUM(montantPayer),0) AS s
            FROM paiement_divers
            WHERE anneeScolaire = ?
              AND dateCreated BETWEEN ? AND ?";
  $st = $con->prepare($sqlPD);
  $st->bind_param('sss', $annee_scolaire_sel, $firstTS, $lastTS); // TIMESTAMP -> bornes avec hh:mm:ss
  $st->execute();
  $r = $st->get_result()->fetch_assoc();
  $st->close();
  $total_paiements_divers_mois = (float)($r['s'] ?? 0);

  /* === Dépenses CONNEXES (Divers) === */
  $sqlDC = "SELECT COALESCE(SUM(montant),0) AS s
            FROM depenses
            WHERE anneeScolaire = ?
              AND reference = 'Frais Connexe'
              AND dateCreaty BETWEEN ? AND ?";
  $st = $con->prepare($sqlDC);
  $st->bind_param('sss', $annee_scolaire_sel, $firstDay, $lastDay);
  $st->execute();
  $r = $st->get_result()->fetch_assoc();
  $st->close();
  $total_depenses_connexe_mois = (float)($r['s'] ?? 0);

  $net_mois_divers = $total_paiements_divers_mois - $total_depenses_connexe_mois;

} catch (Throwable $t) {
  $flash_error = "Erreur de calcul: " . $t->getMessage();
}

/* Vérifier si déjà viré — SCOLAIRE */
try {
  $sqlChk = "SELECT id FROM balance
             WHERE anneeScolaire = ?
               AND periode_annee = ?
               AND periode_mois = ?
               AND type_virement = 'Dépôt du frais scolaire'
             LIMIT 1";
  $st = $con->prepare($sqlChk);
  $st->bind_param('sii', $annee_scolaire_sel, $periode_annee, $periode_mois);
  $st->execute();
  $res = $st->get_result();
  $deja_verse = (bool)$res->fetch_row();
  $st->close();
} catch (Throwable $t) {}

/* Vérifier si déjà viré — DIVERS */
try {
  $sqlChk2 = "SELECT id FROM balance
              WHERE anneeScolaire = ?
                AND periode_annee = ?
                AND periode_mois = ?
                AND type_virement = 'Dépôt des frais divers'
              LIMIT 1";
  $st = $con->prepare($sqlChk2);
  $st->bind_param('sii', $annee_scolaire_sel, $periode_annee, $periode_mois);
  $st->execute();
  $res = $st->get_result();
  $deja_verse_divers = (bool)$res->fetch_row();
  $st->close();
} catch (Throwable $t) {}

/* POST: virement SCOLAIRE */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verser_balance'])) {
  if ($deja_verse) {
    $flash_error = "Le virement de $mois_param ($annee_scolaire_sel) a d&eacute;j&agrave; &eacute;t&eacute; effectu&eacute;.";
  } else {
    if ($flash_error === '') {
      try {
        $con->begin_transaction();

        // Insert balance (trace complète)
        $sqlB = "INSERT INTO balance
                  (type_virement, entre, sorti, reste, periode_mois, periode_annee, dateBalance, anneeScolaire)
                 VALUES
                  ('Dépôt du frais scolaire', ?, ?, ?, ?, ?, CURDATE(), ?)";
        $st = $con->prepare($sqlB);
        // d d d i i s
        $st->bind_param('dddiis',
          $total_paiements_mois,
          $total_depenses_scolaire_mois,
          $net_mois_scolaire,
          $periode_mois,
          $periode_annee,
          $annee_scolaire_sel
        );
        $st->execute();
        $st->close();

        // Insert virement (journal)
        $sqlV = "INSERT INTO virement
                  (type_virement, entre, sorti, reste, periode_mois, periode_annee, date_virement, anneeScolaire, observation)
                 VALUES
                  ('Versement frais scolaire vers la caisse centrale', ?, ?, ?, ?, ?, NOW(), ?, 'Transfert mensuel (paiements scolaires & d&eacute;penses scolaires)')";
        $st = $con->prepare($sqlV);
        $st->bind_param('dddiis',
          $total_paiements_mois,
          $total_depenses_scolaire_mois,
          $net_mois_scolaire,
          $periode_mois,
          $periode_annee,
          $annee_scolaire_sel
        );
        $st->execute();
        $st->close();

        $con->commit();
        $flash_success = "Virement (frais scolaire) effectu&eacute; avec succ&egrave;s pour $mois_param ($annee_scolaire_sel).";
        $deja_verse = true;

      } catch (Throwable $t) {
        $con->rollback();
        $flash_error = "Erreur virement: " . $t->getMessage();
      }
    }
  }
}

/* POST: virement DIVERS */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verser_balance_divers'])) {
  if ($deja_verse_divers) {
    $flash_error_divers = "Le virement des frais divers de $mois_param ($annee_scolaire_sel) a d&eacute;j&agrave; &eacute;t&eacute; effectu&eacute;.";
  } else {
    if ($flash_error_divers === '') {
      try {
        $con->begin_transaction();

        // Insert balance (entrées/sorties/restes pour DIVERS)
        $sqlB = "INSERT INTO balance
                  (type_virement, entre, sorti, reste, periode_mois, periode_annee, dateBalance, anneeScolaire)
                 VALUES
                  ('Dépôt des frais divers', ?, ?, ?, ?, ?, CURDATE(), ?)";
        $st = $con->prepare($sqlB);
        // d d d i i s
        $st->bind_param('dddiis',
          $total_paiements_divers_mois,   // entre
          $total_depenses_connexe_mois,   // sorti
          $net_mois_divers,               // reste
          $periode_mois,
          $periode_annee,
          $annee_scolaire_sel
        );
        $st->execute();
        $st->close();

        // Insert virement (journal)
        $sqlV = "INSERT INTO virement
                  (type_virement, entre, sorti, reste, periode_mois, periode_annee, date_virement, anneeScolaire, observation)
                 VALUES
                  ('Versement frais divers vers la caisse centrale', ?, ?, ?, ?, ?, NOW(), ?, 'Transfert mensuel (frais divers & d&eacute;penses connexes)')";
        $st = $con->prepare($sqlV);
        $st->bind_param('dddiis',
          $total_paiements_divers_mois,   // entre
          $total_depenses_connexe_mois,   // sorti
          $net_mois_divers,               // reste
          $periode_mois,
          $periode_annee,
          $annee_scolaire_sel
        );
        $st->execute();
        $st->close();

        $con->commit();
        $flash_success_divers = "Virement (frais divers) effectu&eacute; avec succ&egrave;s pour $mois_param ($annee_scolaire_sel).";
        $deja_verse_divers = true;

      } catch (Throwable $t) {
        $con->rollback();
        $flash_error_divers = "Erreur virement (divers): " . $t->getMessage();
      }
    }
  }
}

/* Pour la grille des mois déjà virés — SCOLAIRE */
$mois_verses = [];
try {
  $sqlMv = "SELECT DISTINCT periode_mois
            FROM balance
            WHERE type_virement = 'Dépôt du frais scolaire'
              AND anneeScolaire = ?";
  $st = $con->prepare($sqlMv);
  $st->bind_param('s', $annee_scolaire_sel);
  $st->execute();
  $rs = $st->get_result();
  while ($rw = $rs->fetch_assoc()) {
    $mois_verses[] = (int)$rw['periode_mois'];
  }
  $st->close();
} catch (Throwable $t) {
  $mois_verses = [];
}

/* Expose aux vues */
$CAISSE = [
  'mois_param'                     => $mois_param,
  'annee_scolaire'                 => $annee_scolaire_sel,
  'periode_annee'                  => $periode_annee,
  'periode_mois'                   => $periode_mois,
  'first_day'                      => $firstDay,
  'last_day'                       => $lastDay,

  // SCOLAIRE
  'total_paiements_mois'           => $total_paiements_mois,
  'total_depenses_mois'            => $total_depenses_scolaire_mois,
  'net_mois'                       => $net_mois_scolaire,
  'deja_verse'                     => $deja_verse,
  'flash_success'                  => $flash_success,
  'flash_error'                    => $flash_error,

  // DIVERS
  'total_paiements_divers_mois'    => $total_paiements_divers_mois,
  'total_depenses_connexe_mois'    => $total_depenses_connexe_mois,
  'net_mois_divers'                => $net_mois_divers,
  'deja_verse_divers'              => $deja_verse_divers,
  'flash_success_divers'           => $flash_success_divers,
  'flash_error_divers'             => $flash_error_divers,

  // Grille scolaire
  'mois_verses'                    => $mois_verses,
  // Colonne gauche
  // (exposées via variables globales $cycles, $colors, $somme_globale)
];
