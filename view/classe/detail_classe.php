<?php
require_once('../../layouts/constants/head.php');
require_once('../../webapp/database/config.php');
require_once('../../layouts/navbar/navbar.php');

function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

$classe_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($classe_id <= 0) {
  echo '<div class="content-wrapper"><div class="alert alert-danger m-3">Classe introuvable.</div></div>';
  require_once('../../layouts/constants/footer.php'); exit;
}

/* Infos classe */
$sqlInfo = "
  SELECT cl.id, cl.description, cl.dateCreaty, cl.dateUpdate, cl.createdby,
         cy.id AS cycle_id, cy.description AS cycle
  FROM classe cl
  JOIN cycle  cy ON cy.id = cl.cycle
  WHERE cl.id = ?
";
$st = $con->prepare($sqlInfo);
$st->bind_param('i', $classe_id);
$st->execute();
$info = $st->get_result()->fetch_assoc();
$st->close();

if (!$info) {
  echo '<div class="content-wrapper"><div class="alert alert-danger m-3">Classe introuvable.</div></div>';
  require_once('../../layouts/constants/footer.php'); exit;
}

/* Élèves de la classe */
$sqlEleves = "
  SELECT
    e.id,
    e.nom,
    e.postnom,
    e.prenom,
    e.genre,
    e.dateDeNaissance,
    e.anneeScolaire,
    m.id AS id_menage,
    m.noms AS menage
  FROM eleve e
  LEFT JOIN menage m ON m.id = e.menage
  WHERE e.classe = ?
  ORDER BY e.nom ASC, e.postnom ASC, e.prenom ASC
";
$st = $con->prepare($sqlEleves);
$st->bind_param('i', $classe_id);
$st->execute();
$res = $st->get_result();
$rows = [];
while ($e = $res->fetch_assoc()) { $rows[] = $e; }
$st->close();

$nb_eleves = count($rows);

/* --------- EXPORT CSV --------- */
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    // Nom de fichier propre
    $filename = 'eleves_classe_'.$classe_id.'_'.date('Y-m-d_H-i-s').'.csv';

    // En-têtes pour forcer le téléchargement
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="'.$filename.'"');

    // BOM pour Excel (évite les problèmes d'accents)
    echo "\xEF\xBB\xBF";

    $out = fopen('php://output', 'w');

    // En-têtes colonnes
    fputcsv($out, [
        'ID', 'Noms', 'Genre', 'Date de naissance', 'Ménage', 'Année scolaire'
    ], ';'); // séparateur ; (souvent préféré en francophonie)

    // Lignes
    foreach ($rows as $e) {
        $noms = trim(($e['nom'] ?? '').' '.($e['postnom'] ?? '').' '.($e['prenom'] ?? ''));
        fputcsv($out, [
            (int)$e['id'],
            $noms,
            (string)$e['genre'],
            (string)$e['dateDeNaissance'],
            (string)$e['menage'],
            (string)$e['anneeScolaire'],
        ], ';');
    }
    fclose($out);
    exit;
}
?>
<div class="main-panel">
  <div class="content-wrapper">
    <div class="row">
      <div class="col-lg-12 col-sm-12 grid-margin">
        <div class="card">
          <div class="card-body">
            <h4 class="card-title text-uppercase">
              <span>
                <button type="button" class="btn btn-danger" onclick="history.back()">&lt; Retour</button>
              </span>
              Détails / Élèves de la classe
            </h4>

            <div class="d-flex flex-wrap align-items-center justify-content-between mb-3 gap-2">
              <small class="text-muted">
                Classe : <strong><?= h($info['description']); ?></strong> — Cycle : <strong><?= h($info['cycle']); ?></strong>
                <span class="badge bg-primary">Nbre d'élèves <?= (int)$nb_eleves; ?></span>
              </small>

              <div class="d-flex align-items-center gap-2" style="min-width:320px">
                <input type="text" id="searchEleves" class="form-control" placeholder="Rechercher élève (nom, postnom, prénom)" onkeyup="filterEleves()">
                <!-- Boutons Export / Imprimer -->
                <div class="btn-group">
                  <a href="?id=<?= (int)$classe_id; ?>&export=csv" class="btn btn-outline-success" title="Exporter en CSV">
                    Export CSV
                  </a>
                  <button type="button" class="btn btn-outline-secondary" onclick="window.print()" title="Imprimer">
                    Imprimer
                  </button>
                </div>
              </div>
            </div>

            <div class="table-responsive">
              <table class="table table-striped" id="tableEleves">
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>Noms</th>
                    <th>Genre</th>
                    <th>Date de naissance</th>
                    <th>Ménage</th>
                    <th>Année scolaire</th>
                    <th style="width:90px;"></th>
                  </tr>
                </thead>
                <tbody>
                  <?php if ($nb_eleves === 0): ?>
                    <tr>
                      <td colspan="7">
                        <div class="alert alert-info mb-0">Aucun élève inscrit dans cette classe.</div>
                      </td>
                    </tr>
                  <?php else: ?>
                    <?php foreach ($rows as $e): ?>
                      <tr>
                        <td><?= (int)$e['id']; ?></td>
                        <td><?= h(trim(($e['nom'] ?? '').' '.($e['postnom'] ?? '').' '.($e['prenom'] ?? ''))); ?></td>
                        <td><?= h($e['genre']); ?></td>
                        <td><?= h($e['dateDeNaissance']); ?></td>
                        <td>
                          <?php if (!empty($e['id_menage'])): ?>
                            <a href="../menage/detail_menage.php?id=<?= (int)$e['id_menage']; ?>"><?= h($e['menage']); ?></a>
                          <?php else: ?>
                            <span class="text-muted">—</span>
                          <?php endif; ?>
                        </td>
                        <td><?= h($e['anneeScolaire']); ?></td>
                        <td>
                          <a href="../eleve/detail_eleve.php?id=<?= (int)$e['id']; ?>" class="btn btn-sm btn-primary">Voir</a>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>

          </div>
        </div>
      </div>

    </div>
  </div>
  <?php require_once('../../layouts/constants/footer.php'); ?>
</div>

<script>
function filterEleves() {
  const input = document.getElementById('searchEleves');
  const filter = (input.value || '').toLowerCase();
  const table = document.getElementById('tableEleves');
  const trs = table.getElementsByTagName('tr');

  for (let i = 1; i < trs.length; i++) {
    const tds = trs[i].getElementsByTagName('td');
    if (!tds || tds.length === 0) continue;

    const noms = (tds[1].innerText || tds[1].textContent || '').toLowerCase();
    trs[i].style.display = noms.indexOf(filter) > -1 ? '' : 'none';
  }
}
</script>
