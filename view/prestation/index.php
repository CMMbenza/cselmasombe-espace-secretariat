<?php
// rapport_mensuel / index.php - Rapport mensuel des présences
declare(strict_types=1);

require_once ('../../webapp/database/config.php'); // doit définir $con (mysqli)

// Sécurité de base : helper échappement
function e(string $v): string {
    return htmlspecialchars($v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// Forcer timezone Kinshasa
date_default_timezone_set('Africa/Kinshasa');

// --- Récup filtres ---
$currentMonth = (int)date('n');
$currentYear  = (int)date('Y');

$month    = isset($_GET['month'])    ? (int)$_GET['month']    : $currentMonth;
$year     = isset($_GET['year'])     ? (int)$_GET['year']     : $currentYear;
$service  = isset($_GET['service'])  ? trim((string)$_GET['service'])  : '';
$agentId  = isset($_GET['agent_id']) ? (int)$_GET['agent_id'] : 0;
$weekday  = isset($_GET['weekday'])  ? (int)$_GET['weekday']  : 0; // 0 = tous, 1=dim ... 7=sam
$export   = isset($_GET['export'])   ? trim((string)$_GET['export'])   : '';

// Normaliser mois/année
if ($month < 1 || $month > 12) $month = $currentMonth;
if ($year < 2000 || $year > ($currentYear + 1)) $year = $currentYear;
if ($weekday < 0 || $weekday > 7) $weekday = 0;

// Services possibles (enum de agent.service)
$services = [
    ''            => 'Tous les services',
    'PRIM & MAT'  => 'PRIM & MAT',
    'SEC & HUM'   => 'SEC & HUM',
    'DIR & ADM'   => 'DIR & ADM',
];

// Noms des mois
$moisNoms = [
    1=>'Janvier',2=>'Février',3=>'Mars',4=>'Avril',5=>'Mai',6=>'Juin',
    7=>'Juillet',8=>'Août',9=>'Septembre',10=>'Octobre',11=>'Novembre',12=>'Décembre'
];

// Noms des jours (DAYOFWEEK : 1=dimanche ... 7=samedi)
$weekdayLabels = [
    0 => 'Tous les jours',
    1 => 'Dimanche',
    2 => 'Lundi',
    3 => 'Mardi',
    4 => 'Mercredi',
    5 => 'Jeudi',
    6 => 'Vendredi',
    7 => 'Samedi',
];

// --- Récup liste agents (pour filtre + affichage) ---
$agents      = [];
$agentsById  = [];

try {
    $sqlA = "SELECT id, nom, postnom, prenom, service FROM agent ORDER BY nom, postnom, prenom";
    $rsA = $con->query($sqlA);
    if ($rsA) {
        while ($row = $rsA->fetch_assoc()) {
            $agents[] = $row;
            $agentsById[(int)$row['id']] = $row;
        }
        $rsA->free();
    }
} catch (Throwable $e) {
    // agents reste vide
}

// --- Fonction : charger le rapport mensuel par agent ---
function loadMonthlyReport(mysqli $con, int $month, int $year, string $service, int $agentId, int $weekday): array {
    $rows   = [];
    $where  = "WHERE 1=1";
    $params = [];
    $types  = '';

    if ($service !== '') {
        $where .= " AND a.service = ?";
        $params[] = $service;
        $types   .= 's';
    }

    if ($agentId > 0) {
        $where .= " AND a.id = ?";
        $params[] = $agentId;
        $types   .= 'i';
    }

    $sql = "
        SELECT
            a.id,
            a.nom,
            a.postnom,
            a.prenom,
            a.service,
            COUNT(p.id) AS nb_jours_pointes,
            SUM(CASE WHEN p.statut = 'present' THEN 1 ELSE 0 END) AS nb_present,
            SUM(CASE WHEN p.statut = 'retard'  THEN 1 ELSE 0 END) AS nb_retard,
            SUM(CASE WHEN p.statut = 'absent'  THEN 1 ELSE 0 END) AS nb_absent,
            MIN(p.heure_arrivee) AS meilleure_arrivee,
            MAX(p.heure_arrivee) AS plus_grand_retard,
            MIN(p.heure_arrivee) AS premiere_arrivee,
            MAX(p.heure_depart)  AS derniere_depart
        FROM agent a
        LEFT JOIN presence_agent p
          ON p.agent_id = a.id
         AND MONTH(p.date_presence) = ?
         AND YEAR(p.date_presence)  = ?
         AND (? = 0 OR DAYOFWEEK(p.date_presence) = ?)
        $where
        GROUP BY a.id, a.nom, a.postnom, a.prenom, a.service
        ORDER BY a.service, a.nom, a.postnom, a.prenom
    ";

    $stmt = $con->prepare($sql);
    if ($stmt) {
        $typesFull   = 'iiii' . $types; // month, year, weekday, weekday + autres filtres
        $paramsFull  = [$month, $year, $weekday, $weekday];
        foreach ($params as $p) {
            $paramsFull[] = $p;
        }

        $stmt->bind_param($typesFull, ...$paramsFull);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $row['nb_jours_pointes']   = (int)($row['nb_jours_pointes'] ?? 0);
                $row['nb_present']         = (int)($row['nb_present'] ?? 0);
                $row['nb_retard']          = (int)($row['nb_retard'] ?? 0);
                $row['nb_absent']          = (int)($row['nb_absent'] ?? 0);
                $row['meilleure_arrivee']  = $row['meilleure_arrivee'] ?? null;
                $row['plus_grand_retard']  = $row['plus_grand_retard'] ?? null;
                $rows[] = $row;
            }
            $res->free();
        }
        $stmt->close();
    }

    return $rows;
}

// Charger la synthèse
$reportRows = loadMonthlyReport($con, $month, $year, $service, $agentId, $weekday);

// --- Détail par agent (jour par jour) ---
$detailRows = [];
if ($agentId > 0) {
    try {
        $sqlD = "
            SELECT
                date_presence,
                heure_arrivee,
                heure_depart,
                statut
            FROM presence_agent
            WHERE agent_id = ?
              AND MONTH(date_presence) = ?
              AND YEAR(date_presence)  = ?
              AND (? = 0 OR DAYOFWEEK(date_presence) = ?)
            ORDER BY date_presence ASC
        ";
        $stmtD = $con->prepare($sqlD);
        if ($stmtD) {
            $stmtD->bind_param('iiiii', $agentId, $month, $year, $weekday, $weekday);
            $stmtD->execute();
            $resD = $stmtD->get_result();
            if ($resD) {
                while ($r = $resD->fetch_assoc()) {
                    $detailRows[] = $r;
                }
                $resD->free();
            }
            $stmtD->close();
        }
    } catch (Throwable $e) {
        // ignore
    }
}

// --- Export Excel ---
if ($export === 'excel') {
    $fileName = 'rapport_presence_'.$year.'_'.sprintf('%02d', $month).'.xls';
    header('Content-Type: application/vnd.ms-excel; charset=utf-8');
    header('Content-Disposition: attachment; filename="'.$fileName.'"');
    header('Pragma: no-cache');
    header('Expires: 0');

    echo "<table border='1'>";
    echo "<thead>
            <tr>
              <th>ID</th>
              <th>Agent</th>
              <th>Service</th>
              <th>Jours pointés</th>
              <th>Présent</th>
              <th>Retard</th>
              <th>Absent</th>
              <th>Meilleure heure d'arrivée</th>
              <th>Heure du grand retard</th>
              <th>Taux de présence (%)</th>
            </tr>
          </thead>
          <tbody>";

    foreach ($reportRows as $row) {
        $idA    = (int)$row['id'];
        $nom    = trim(($row['nom'] ?? '').' '.($row['postnom'] ?? '').' '.($row['prenom'] ?? ''));
        $serviceLib = (string)($row['service'] ?? '');
        $jp     = (int)$row['nb_jours_pointes'];
        $np     = (int)$row['nb_present'];
        $nr     = (int)$row['nb_retard'];
        $na     = (int)$row['nb_absent'];
        $touch  = $np + $nr + $na;
        $denom  = max($jp, $touch, 1);
        $taux   = (($np + $nr) / $denom) * 100.0;

        $meilleure = $row['meilleure_arrivee'] ?: '';
        $grandRet  = $row['plus_grand_retard'] ?: '';

        echo "<tr>";
        echo "<td>".$idA."</td>";
        echo "<td>".e($nom !== '' ? $nom : ('Agent '.$idA))."</td>";
        echo "<td>".e($serviceLib ?: '—')."</td>";
        echo "<td>".$jp."</td>";
        echo "<td>".$np."</td>";
        echo "<td>".$nr."</td>";
        echo "<td>".$na."</td>";
        echo "<td>".e($meilleure !== '' ? $meilleure : '—')."</td>";
        echo "<td>".e($grandRet !== '' ? $grandRet : '—')."</td>";
        echo "<td>".number_format($taux, 1, ',', ' ')."</td>";
        echo "</tr>";
    }

    echo "</tbody></table>";
    exit;
}

// ------------------------------------------------------------------
// AFFICHAGE HTML NORMAL
// ------------------------------------------------------------------
require_once ('../../layouts/constants/head.php');
require_once ('../../layouts/navbar/navbar.php');
?>
<div class="main-panel-copy">
    <div class="content-wrapper">
        <!-- Titre + Filtres -->
        <div class="row">
            <div class="col-12 grid-margin">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <h3 class="card-title d-flex align-items-center">
                                <span class="menu-icon me-2">
                                    <i class="fa fa-calendar-check-o"></i>
                                </span>
                                Rapport mensuel des présences — Agents
                            </h3>
                            <div class="text-end">
                                <div class="small text-muted">
                                    Période :
                                    <strong>
                                        <?= e($moisNoms[$month] ?? (string)$month) ?>
                                        <?= e((string)$year) ?>
                                    </strong>
                                    <?php if ($weekday !== 0): ?>
                                        • Jour :
                                        <strong><?= e($weekdayLabels[$weekday] ?? (string)$weekday) ?></strong>
                                    <?php endif; ?>
                                </div>
                                <div class="mt-1">
                                    <?php
                                    $baseUrl = basename(__FILE__);
                                    $qs = http_build_query([
                                        'month'   => $month,
                                        'year'    => $year,
                                        'service' => $service,
                                        'agent_id'=> $agentId,
                                        'weekday' => $weekday
                                    ]);
                                    ?>
                                    <a href="<?= e($baseUrl.'?'.$qs.'&export=excel') ?>"
                                       class="btn btn-success btn-sm">
                                        <i class="fa fa-file-excel-o"></i> Export Excel
                                    </a>
                                    <button type="button" class="btn btn-secondary btn-sm" onclick="window.print()">
                                        <i class="fa fa-file-pdf-o"></i> Imprimer / PDF
                                    </button>
                                </div>
                            </div>
                        </div>

                        <h6 class="wrapper-filtrage mb-3">Filtrage des données :</h6>

                        <form method="get" class="row g-2 align-items-end">
                            <!-- Mois -->
                            <div class="col-md-3">
                                <label class="form-label">Mois</label>
                                <select name="month" class="form-control">
                                    <?php foreach ($moisNoms as $num => $label): ?>
                                        <option value="<?= $num ?>" <?= $num===$month ? 'selected' : '' ?>>
                                            <?= e($label) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <!-- Année -->
                            <div class="col-md-2">
                                <label class="form-label">Année</label>
                                <select name="year" class="form-control">
                                    <?php for ($y = $currentYear-1; $y <= $currentYear+1; $y++): ?>
                                        <option value="<?= $y ?>" <?= $y===$year ? 'selected' : '' ?>>
                                            <?= $y ?>
                                        </option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <!-- Service -->
                            <div class="col-md-3">
                                <label class="form-label">Service</label>
                                <select name="service" class="form-control">
                                    <?php foreach ($services as $key => $label): ?>
                                        <option value="<?= e($key) ?>" <?= $key===$service ? 'selected' : '' ?>>
                                            <?= e($label) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <!-- Jour de la semaine -->
                            <div class="col-md-2">
                                <label class="form-label">Jour de la semaine</label>
                                <select name="weekday" class="form-control">
                                    <?php foreach ($weekdayLabels as $key => $label): ?>
                                        <option value="<?= $key ?>" <?= $key===$weekday ? 'selected' : '' ?>>
                                            <?= e($label) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <!-- Agent -->
                            <div class="col-md-2">
                                <label class="form-label">Agent</label>
                                <select name="agent_id" class="form-control">
                                    <option value="0">Tous les agents</option>
                                    <?php foreach ($agents as $ag): ?>
                                        <?php
                                            $idA = (int)$ag['id'];
                                            $nomComplet = trim(($ag['nom'] ?? '').' '.($ag['postnom'] ?? '').' '.($ag['prenom'] ?? ''));
                                            $lib = $nomComplet !== '' ? $nomComplet : ('Agent '.$idA);
                                            $serviceLib = (string)($ag['service'] ?? '');
                                            if ($serviceLib !== '') $lib .= ' — '.$serviceLib;
                                        ?>
                                        <option value="<?= $idA ?>" <?= $idA===$agentId ? 'selected' : '' ?>>
                                            <?= e($lib) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <!-- Bouton -->
                            <div class="col-12 mt-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa fa-filter"></i> Appliquer les filtres
                                </button>
                                <a href="create-update.php" class="btn btn-success">Nouvelles préstations</a>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>

        <!-- Tableau récapitulatif -->
        <div class="row">
            <div class="col-12 grid-margin">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">
                            Synthèse par agent
                            <small class="text-muted d-block" style="font-size: 0.8rem;">
                                Résumé des présences, retards et absences pour la période sélectionnée.
                            </small>
                        </h4>

                        <div class="table-responsive">
                            <table class="table" id="tablePresence">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Agent</th>
                                        <th>Service</th>
                                        <th>Jours pointés</th>
                                        <th>Présent</th>
                                        <th>Retard</th>
                                        <th>Absent</th>
                                        <th>Meilleure heure d'arrivée</th>
                                        <th>Heure du grand retard</th>
                                        <th>Taux de présence</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!$reportRows): ?>
                                        <tr>
                                            <td colspan="11">
                                                <div class="alert alert-info mb-0">
                                                    Aucun enregistrement de présence trouvé pour ce filtre.
                                                </div>
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php
                                            $i = 1;
                                            $totalPresent = $totalRetard = $totalAbsent = 0;
                                        ?>
                                        <?php foreach ($reportRows as $row): ?>
                                            <?php
                                                $idA    = (int)$row['id'];
                                                $nom    = trim(($row['nom'] ?? '').' '.($row['postnom'] ?? '').' '.($row['prenom'] ?? ''));
                                                $serviceLib = (string)($row['service'] ?? '');
                                                $jp     = (int)$row['nb_jours_pointes'];
                                                $np     = (int)$row['nb_present'];
                                                $nr     = (int)$row['nb_retard'];
                                                $na     = (int)$row['nb_absent'];
                                                $touch  = $np + $nr + $na;
                                                $denom  = max($jp, $touch, 1);
                                                $taux   = (($np + $nr) / $denom) * 100.0;

                                                $totalPresent += $np;
                                                $totalRetard  += $nr;
                                                $totalAbsent  += $na;

                                                $meilleure = $row['meilleure_arrivee'] ?: null;
                                                $grandRet  = $row['plus_grand_retard'] ?: null;

                                                $detailQs = http_build_query([
                                                    'month'   => $month,
                                                    'year'    => $year,
                                                    'service' => $service,
                                                    'agent_id'=> $idA,
                                                    'weekday' => $weekday
                                                ]);
                                            ?>
                                            <tr>
                                                <td><?= $i++ ?></td>
                                                <td><?= e($nom !== '' ? $nom : ('Agent '.$idA)) ?></td>
                                                <td><?= e($serviceLib ?: '—') ?></td>
                                                <td><?= $jp ?></td>
                                                <td class="text-success"><?= $np ?></td>
                                                <td class="text-warning"><?= $nr ?></td>
                                                <td class="text-danger"><?= $na ?></td>
                                                <td><?= e($meilleure ?? '—') ?></td>
                                                <td><?= e($grandRet ?? '—') ?></td>
                                                <td><?= number_format($taux, 1, ',', ' ') ?> %</td>
                                                <td>
                                                    <a href="?<?= e($detailQs) ?>"
                                                       class="btn btn-sm btn-primary">
                                                        Détails
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                                <?php if ($reportRows): ?>
                                    <tfoot>
                                        <tr>
                                            <th colspan="4" class="text-end">Totaux (période) :</th>
                                            <th class="text-success"><?= $totalPresent ?></th>
                                            <th class="text-warning"><?= $totalRetard ?></th>
                                            <th class="text-danger"><?= $totalAbsent ?></th>
                                            <th colspan="4"></th>
                                        </tr>
                                    </tfoot>
                                <?php endif; ?>
                            </table>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <!-- Détail jour par jour pour un agent -->
        <?php if ($agentId > 0): ?>
            <div class="row">
                <div class="col-12 grid-margin">
                    <div class="card">
                        <div class="card-body">
                            <?php
                                $agNom = 'Agent '.$agentId;
                                if (isset($agentsById[$agentId])) {
                                    $a = $agentsById[$agentId];
                                    $agNom = trim(($a['nom'] ?? '').' '.($a['postnom'] ?? '').' '.($a['prenom'] ?? ''));
                                    if ($agNom === '') $agNom = 'Agent '.$agentId;
                                }
                                $serviceAgent = $agentsById[$agentId]['service'] ?? '—';
                            ?>
                            <h4 class="card-title">
                                Détail des présences — <?= e($agNom) ?>
                                <small class="text-muted d-block" style="font-size: 0.8rem;">
                                    Mois de <?= e($moisNoms[$month] ?? (string)$month) ?> <?= e((string)$year) ?>
                                    • Service : <?= e($serviceAgent) ?>
                                    <?php if ($weekday !== 0): ?>
                                        • Jour : <?= e($weekdayLabels[$weekday] ?? (string)$weekday) ?>
                                    <?php endif; ?>
                                </small>
                            </h4>

                            <div class="table-responsive">
                                <table class="table table-sm table-bordered mb-0">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Arrivée</th>
                                            <th>Départ</th>
                                            <th>Statut</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!$detailRows): ?>
                                            <tr>
                                                <td colspan="4">
                                                    <em>Aucun détail de présence enregistré pour cet agent sur cette période.</em>
                                                </td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($detailRows as $d): ?>
                                                <?php
                                                    $date  = (string)$d['date_presence'];
                                                    $arr   = $d['heure_arrivee'] ?: '—';
                                                    $dep   = $d['heure_depart']  ?: '—';
                                                    $stat  = (string)$d['statut'];
                                                    $badgeClass = 'secondary';
                                                    if ($stat === 'present') $badgeClass = 'success';
                                                    elseif ($stat === 'retard') $badgeClass = 'warning';
                                                    elseif ($stat === 'absent') $badgeClass = 'danger';
                                                ?>
                                                <tr>
                                                    <td><?= e($date) ?></td>
                                                    <td><?= e($arr) ?></td>
                                                    <td><?= e($dep) ?></td>
                                                    <td>
                                                        <span class="badge badge-outline-<?= $badgeClass ?>">
                                                            <?= e(ucfirst($stat)) ?>
                                                        </span>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>

                            <div class="mt-3">
                                <?php
                                  $backQs = http_build_query([
                                      'month'   => $month,
                                      'year'    => $year,
                                      'service' => $service,
                                      'weekday' => $weekday,
                                      'agent_id'=> 0
                                  ]);
                                ?>
                                <a href="?<?= e($backQs) ?>" class="btn btn-sm btn-outline-secondary">
                                    &larr; Retour au rapport global
                                </a>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

    </div>

    <?php require_once ('../../layouts/constants/footer.php'); ?>
</div>
