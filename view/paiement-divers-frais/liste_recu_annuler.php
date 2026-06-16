<?php
require_once('../../layouts/constants/head.php');
require_once('../../layouts/navbar/navbar.php');
require_once('../../webapp/database/config.php');

function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function fmt($n){ return number_format((float)$n, 2, '.', ' '); }

$qtype = isset($_GET['type']) ? trim($_GET['type']) : ''; // '', 'scolaire', 'connexe'

$where = [];
$bind  = '';
$args  = [];

if ($qtype !== '' && in_array($qtype, ['scolaire','connexe'], true)) {
  $where[] = 'ra.recu_type = ?';
  $bind   .= 's';
  $args[]  = $qtype;
}

$sql = "SELECT ra.*, m.noms AS menage_nom
        FROM recu_annule ra
        LEFT JOIN menage m ON m.id = ra.menage_id
        ".(count($where) ? "WHERE ".implode(' AND ', $where) : "")."
        ORDER BY ra.cancelled_at DESC, ra.id DESC";
$rows = [];
if ($st = $con->prepare($sql)) {
  if ($bind !== '') $st->bind_param($bind, ...$args);
  $st->execute();
  $rs = $st->get_result();
  while ($r = $rs->fetch_assoc()) $rows[] = $r;
  $st->close();
}
?>
<div class="main-panel">
  <div class="content-wrapper">
    <div class="row">
      <div class="col-12 grid-margin">
        <div class="card">
          <div class="card-body">
            <h4 class="card-title">Reçus annulés</h4>

            <form class="row g-2 mb-3" method="get">
              <div class="col-md-3">
                <label class="form-label">Type</label>
                <select name="type" class="form-control">
                  <option value="">Tous</option>
                  <option value="scolaire" <?= $qtype==='scolaire'?'selected':''; ?>>Scolaire</option>
                  <option value="connexe"  <?= $qtype==='connexe'?'selected':''; ?>>Connexe</option>
                </select>
              </div>
              <div class="col-md-2 d-flex align-items-end">
                <button class="btn btn-primary w-100" type="submit">Filtrer</button>
              </div>
            </form>

            <div class="table-responsive">
              <table class="table align-middle">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>Reçu</th>
                    <th>Type</th>
                    <th>Ménage</th>
                    <th>Montant</th>
                    <th>Date reçu</th>
                    <th>Motif</th>
                    <th>Par</th>
                    <th>Annulé le</th>
                    <th>Source</th>
                  </tr>
                </thead>
                <tbody>
                <?php if (empty($rows)): ?>
                  <tr><td colspan="10"><div class="alert alert-info mb-0">Aucun reçu annulé.</div></td></tr>
                <?php else: foreach ($rows as $r): ?>
                  <tr>
                    <td><?= (int)$r['id'] ?></td>
                    <td><?= (int)$r['recu_id'] ?></td>
                    <td>
                      <?php if ($r['recu_type']==='scolaire'): ?>
                        <span class="badge bg-info">Scolaire</span>
                      <?php else: ?>
                        <span class="badge bg-secondary">Connexe</span>
                      <?php endif; ?>
                    </td>
                    <td><?= h($r['menage_nom'] ?? '') ?></td>
                    <td><?= fmt($r['montant_payer']) ?> $</td>
                    <td><?= h($r['date_recu']) ?></td>
                    <td><?= h($r['motif']) ?></td>
                    <td><?= h($r['cancelled_by']) ?></td>
                    <td><?= h($r['cancelled_at']) ?></td>
                    <td>
                      <?php if ($r['recu_type']==='scolaire'): ?>
                        <a class="btn btn-sm btn-outline-primary" href="../paiement-divers-frais/apercu_recu.php?ordre=<?= (int)$r['recu_id'] ?>" target="_blank">Voir reçu</a>
                      <?php else: ?>
                        <a class="btn btn-sm btn-outline-primary" href="../paiement-divers-frais/apercu_recu_divers.php?ordre=<?= (int)$r['recu_id'] ?>" target="_blank">Voir reçu</a>
                      <?php endif; ?>
                    </td>
                  </tr>
                <?php endforeach; endif; ?>
                </tbody>
              </table>
            </div>

            <div class="mt-2">
              <small class="text-muted">
                Les reçus annulés sont exclus automatiquement des historiques et totaux.
              </small>
            </div>

          </div>
        </div>
      </div>
    </div>
  </div>
  <?php require_once('../../layouts/constants/footer.php'); ?>
</div>
