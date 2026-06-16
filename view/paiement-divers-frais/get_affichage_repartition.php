<?php
require_once('../../webapp/database/dbcongig.php');

$menage_id = intval($_GET['menage_id'] ?? 0);
$montant_total = floatval($_GET['montant'] ?? 0);

if (!$menage_id || $montant_total <= 0) {
    exit('<p class="text-muted">Aucune répartition possible</p>');
}

// Charger progression des tranches
$url = "get_tranche_progression.php?menage_id=$menage_id";
$eleves = json_decode(file_get_contents($url), true);

$html = '<table class="table table-striped">
            <thead>
                <tr>
                    <th>Nom élève</th>
                    <th>Tranche en cours</th>
                    <th>Montant tranche</th>
                    <th>Montant déjà payé</th>
                    <th>Solde tranche</th>
                    <th>À affecter maintenant</th>
                </tr>
            </thead>
            <tbody>';

foreach ($eleves as $eleve) {
    if ($montant_total <= 0) break;

    $a_affecter = min($eleve['reste'], $montant_total);

    $html .= '<tr>
                <td>' . $eleve['nom'] . '</td>
                <td>Tranche ' . $eleve['numero_tranche'] . '</td>
                <td>' . number_format($eleve['montant_tranche'], 2) . ' $</td>
                <td>' . number_format($eleve['montant_paye'], 2) . ' $</td>
                <td>' . number_format($eleve['reste'], 2) . ' $</td>
                <td><strong>' . number_format($a_affecter, 2) . ' $</strong></td>
            </tr>';

    $montant_total -= $a_affecter;
}

$html .= '</tbody></table>';

echo $html;