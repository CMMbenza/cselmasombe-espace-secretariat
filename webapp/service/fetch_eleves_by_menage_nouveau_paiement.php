<?php
require_once('../../webapp/database/config.php');
require_once('../../webapp/service/annee_scolaire.encours.php');
header('Content-Type: application/json');

if (!$con) {
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

if (!isset($_GET['menage_id'])) {
    echo json_encode([]);
    exit;
}

$menage_id = intval($_GET['menage_id']);

// Note : la table "paiement" ne contient pas de colonne "eleve_id".
// Le lien avec l'élève se fait via "paiement_detail.eleve_id".
// La somme des paiements par élève doit passer par paiement_detail -> paiement

$sql = "
SELECT
  e.id AS eleve_id,
  e.nom,
  e.postnom,
  e.prenom,
  c.id AS cycle_id,
  c.description AS cycle_nom,
  -- Montant total des tranches du cycle
  (SELECT SUM(t.montant)
   FROM tranche t
   JOIN scolarite s ON t.frais_id = s.id
   WHERE s.cycle = c.id AND s.anneeScolaire = ?) AS montant_total_cycle,
  -- Paiement total de cet élève sur l'année scolaire
  (
    SELECT COALESCE(SUM(pd.montant), 0)
    FROM paiement_detail pd
    JOIN paiement p ON pd.paiement_id = p.id
    WHERE pd.eleve_id = e.id AND p.anneeScolaire = ?
  ) AS total_paye
FROM eleve e
JOIN classe cl ON e.classe = cl.id
JOIN cycle c ON cl.cycle = c.id
WHERE e.menage = ?
";

$stmt = $con->prepare($sql);
$stmt->bind_param("ssi", $annee_scolaire, $annee_scolaire, $menage_id);
$stmt->execute();
$result = $stmt->get_result();

$eleves = [];

while ($row = $result->fetch_assoc()) {
    $eleve_id = $row['eleve_id'];
    $cycle_id = $row['cycle_id'];

    // Récupérer les tranches du cycle + montant payé par tranche par cet élève
    $sqlTranches = "
      SELECT
        t.id AS tranche_id,
        t.numero_tranche,
        t.montant,
        COALESCE(SUM(pd.montant), 0) AS montant_paye
      FROM tranche t
      JOIN scolarite s ON t.frais_id = s.id
      LEFT JOIN paiement_detail pd ON pd.tranche_id = t.id AND pd.eleve_id = ?
      LEFT JOIN paiement p ON pd.paiement_id = p.id AND p.anneeScolaire = s.anneeScolaire
      WHERE s.cycle = ? AND s.anneeScolaire = ?
      GROUP BY t.id, t.numero_tranche, t.montant
      ORDER BY t.numero_tranche ASC
    ";

    $stmtTranches = $con->prepare($sqlTranches);
    $stmtTranches->bind_param("iis", $eleve_id, $cycle_id, $annee_scolaire);
    $stmtTranches->execute();
    $resTranches = $stmtTranches->get_result();

    $tranches = [];
    $tranche_en_cours = null;

    while ($tr = $resTranches->fetch_assoc()) {
        $reste_tranche = $tr['montant'] - $tr['montant_paye'];
        $tranche = [
            'id' => $tr['tranche_id'],
            'numero_tranche' => (int)$tr['numero_tranche'],
            'montant' => (float)$tr['montant'],
            'montant_paye' => (float)$tr['montant_paye'],
            'reste' => max(0, $reste_tranche),
        ];
        $tranches[] = $tranche;

        if ($tranche_en_cours === null && $reste_tranche > 0) {
            $tranche_en_cours = $tranche;
        }
    }
    $stmtTranches->close();

    if ($tranche_en_cours === null) {
        $tranche_en_cours = ['numero_tranche' => null, 'reste' => 0];
    }

    $eleves[] = [
        'id' => $eleve_id,
        'nom' => $row['nom'],
        'postnom' => $row['postnom'],
        'prenom' => $row['prenom'],
        'cycle_id' => $row['cycle_id'], 
        'cycle' => $row['cycle_nom'],
        'montant_total_cycle' => (float)$row['montant_total_cycle'],
        'total_paye' => (float)$row['total_paye'],
        'reste_total' => max(0, (float)$row['montant_total_cycle'] - (float)$row['total_paye']),
        'tranche_en_cours' => $tranche_en_cours,
        'tranches' => $tranches,
    ];
}

$stmt->close();

echo json_encode($eleves);
exit;
