<?php
/**
 * rapport.service.php — Service de reporting budget (journalier + date personnalisée)
 * - Agrège les entrées/sorties de balance
 * - Dépenses du jour (liste + total)
 * - Paiements du jour (scolaire + divers)
 * - Tableaux de synthèse par Référence (Frais Scolaire vs Frais Connexe)
 */

require_once('../../webapp/database/config.php'); // $con (mysqli)

// --------- Helpers ----------
function safe_date(?string $d): string {
    // Accepte "YYYY-MM-DD" ou tout autre et tente de normaliser en "Y-m-d"
    $d = trim((string)$d);
    if ($d === '') return date('Y-m-d');
    $ts = strtotime($d);
    return $ts ? date('Y-m-d', $ts) : date('Y-m-d');
}
function one_row_assoc(mysqli_result $rst): array {
    return $rst ? ($rst->fetch_assoc() ?: []) : [];
}
function sumOrZero($v): string {
    return number_format((float)($v ?? 0), 2, '.', '');
}
// Security
$date_du_jour = date('Y-m-d');

// --------- Contexte (horodatage affichage) ----------
$date_etabli_rapport = date('d.m.Y');
$heure_etabli_rapport = date('H:i:s');

// --------- Choix de la date (POST) ----------
$target_date = $date_du_jour;
if (isset($_POST['BtndatePersonalisee'])) {
    $target_date = safe_date($_POST['datePersonalisee'] ?? '');
} elseif (isset($_POST['today'])) {
    $target_date = $date_du_jour;
}

// --------- Requêtes (préparées) ----------

// Balance du jour (NB: dans ton schéma, la table balance a `dateBalance` et `created_at`,
// pas `dateUpdate`. On utilise `dateBalance`.)
$reqBalanceJour = $con->prepare("
    SELECT
      COALESCE(SUM(entre),0)  AS entre,
      COALESCE(SUM(sorti),0)  AS sorti,
      COALESCE(SUM(entre) - SUM(sorti),0) AS reste
    FROM balance
    WHERE dateBalance = ?
");
$reqBalanceJour->bind_param('s', $target_date);
$reqBalanceJour->execute();
$rowSommeDuMoisEncours = one_row_assoc($reqBalanceJour->get_result());

// Balance globale (tous temps)
$rstSomme = $con->query("SELECT COALESCE(SUM(entre) - SUM(sorti),0) AS balance FROM balance");
$rowSomme = one_row_assoc($rstSomme);

// Dépenses du jour — liste
$reqDepenses = $con->prepare("
    SELECT id, beneficiaire, reference, numero_reference, description, montant, dateCreaty
    FROM depenses
    WHERE DATE(dateCreaty) = ?
    ORDER BY id DESC
");
$reqDepenses->bind_param('s', $target_date);
$reqDepenses->execute();
$rstDepense = $reqDepenses->get_result();

// Dépenses du jour — total
$reqSommeDepense = $con->prepare("
    SELECT COALESCE(SUM(montant),0) AS montant
    FROM depenses
    WHERE DATE(dateCreaty) = ?
");
$reqSommeDepense->bind_param('s', $target_date);
$reqSommeDepense->execute();
$rowSommeDepense = one_row_assoc($reqSommeDepense->get_result());

// Paiements SCOLAIRE (table `paiement`) — total jour
$reqSommePayeScol = $con->prepare("
    SELECT COALESCE(SUM(montantPayer),0) AS montant, COUNT(*) AS nbre
    FROM paiement
    WHERE DATE(dateCreated) = ?
");
$reqSommePayeScol->bind_param('s', $target_date);
$reqSommePayeScol->execute();
$rowSommePaiementScol = one_row_assoc($reqSommePayeScol->get_result());

// Paiements DIVERS (table `paiement_divers`) — total jour
$reqSommePayeDivers = $con->prepare("
    SELECT COALESCE(SUM(montantPayer),0) AS montant, COUNT(*) AS nbre
    FROM paiement_divers
    WHERE DATE(dateCreated) = ?
");
$reqSommePayeDivers->bind_param('s', $target_date);
$reqSommePayeDivers->execute();
$rowSommePaiementDivers = one_row_assoc($reqSommePayeDivers->get_result());

// --------- Découpage par Référence (mapping) ----------
// On mappe une Référence de dépense à la table de paiements correspondante.
// Astuce : on utilise des LIKE souples pour tes libellés ("Depense de Frais Scolaire", "Frais Scolaire", etc.)
$REFS = [
  [
    'label'        => 'Frais Scolaire',
    'depense_like' => '%Frais%Scolaire%',
    'payment_tbl'  => 'paiement',         // somme sur `montantPayer` et DATE(dateCreated)=?
  ],
  [
    'label'        => 'Frais Connexe',
    'depense_like' => '%Frais%Connexe%',   // "Connexe" ou "Connexes" selon tes usages
    'payment_tbl'  => 'paiement_divers',   // somme sur `montantPayer` et DATE(dateCreated)=?
  ],
];

// Préparations dynamiques
$reqDepenseByRef = $con->prepare("
    SELECT COALESCE(SUM(montant),0) AS total_depense
    FROM depenses
    WHERE DATE(dateCreaty) = ?
      AND reference LIKE ?
");

$reqPayScolByDay = $con->prepare("
    SELECT COALESCE(SUM(montantPayer),0) AS total_pay
    FROM paiement
    WHERE DATE(dateCreated) = ?
");

$reqPayDiversByDay = $con->prepare("
    SELECT COALESCE(SUM(montantPayer),0) AS total_pay
    FROM paiement_divers
    WHERE DATE(dateCreated) = ?
");

$rowsBreakdown = [];   // pour le tableau "Budget par Référence"
$total_depenses_refs = 0.0;
$total_paiements_refs = 0.0;

foreach ($REFS as $r) {
    // Somme dépense par ref
    $like = $r['depense_like'];
    $reqDepenseByRef->bind_param('ss', $target_date, $like);
    $reqDepenseByRef->execute();
    $dep = one_row_assoc($reqDepenseByRef->get_result());
    $depTotal = (float)($dep['total_depense'] ?? 0);

    // Somme paiement par ref (selon table mappée)
    if ($r['payment_tbl'] === 'paiement') {
        $reqPayScolByDay->bind_param('s', $target_date);
        $reqPayScolByDay->execute();
        $pay = one_row_assoc($reqPayScolByDay->get_result());
    } else {
        $reqPayDiversByDay->bind_param('s', $target_date);
        $reqPayDiversByDay->execute();
        $pay = one_row_assoc($reqPayDiversByDay->get_result());
    }
    $payTotal = (float)($pay['total_pay'] ?? 0);

    $rowsBreakdown[] = [
        'label'          => $r['label'],
        'depense_total'  => $depTotal,
        'paiement_total' => $payTotal,
        'ecart'          => $payTotal - $depTotal, // positif = excédent; négatif = déficit
    ];

    $total_depenses_refs += $depTotal;
    $total_paiements_refs += $payTotal;
}

// --------- Synthèse toutes catégories ----------
$total_depense_jour = (float)($rowSommeDepense['montant'] ?? 0);
$total_paiements_jour = (float)($rowSommePaiementScol['montant'] ?? 0) + (float)($rowSommePaiementDivers['montant'] ?? 0);
$ecart_global_jour = $total_paiements_jour - $total_depense_jour;

// --------- Variables exportées vers la vue ----------
/** @var string $target_date - date sélectionnée (Y-m-d) */
$SVC = [
  'target_date'            => $target_date,
  'date_etabli_rapport'    => $date_etabli_rapport,
  'heure_etabli_rapport'   => $heure_etabli_rapport,

  'rowSommeDuMoisEncours'  => $rowSommeDuMoisEncours,
  'rowSomme'               => $rowSomme,

  'rstDepense'             => $rstDepense,
  'rowSommeDepense'        => $rowSommeDepense,

  'rowSommePaiementScol'   => $rowSommePaiementScol,
  'rowSommePaiementDivers' => $rowSommePaiementDivers,

  'rowsBreakdown'          => $rowsBreakdown,
  'total_depense_jour'     => $total_depense_jour,
  'total_paiements_jour'   => $total_paiements_jour,
  'ecart_global_jour'      => $ecart_global_jour,
];

// Raccourcis (pour compatibilité avec ton index existant)
extract($SVC, EXTR_OVERWRITE);
