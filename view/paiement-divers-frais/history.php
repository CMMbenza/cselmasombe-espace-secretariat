<?php
require_once ('../../layouts/constants/head.php');
require_once ('../../webapp/service/paiement.service.php'); // $rstlistPaiement, $afficheTotalDePaiements, $currentFilters, $annee_scolaire
require_once ('../../layouts/navbar/navbar.php');

/* Export CSV minimal */
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
  export_paiements_csv($con, $annee_scolaire); // exit dans la fonction
}

/* Réponse AJAX depuis ce même fichier pour renvoyer juste le TBODY (LIKE sur ménage) */
if (isset($_GET['ajax']) && $_GET['ajax'] === 'table') {
    $flt = build_filters($con, $annee_scolaire);

    // AJOUT: exclusion des reçus annulés (scolaire)
    $sql = "SELECT paie.id AS id,
                   men.id AS idMenage,
                   men.noms AS menage,
                   paie.montantAPayer,
                   paie.montantPayer,
                   paie.resteAPayer,
                   paie.observation,
                   paie.dateCreated
            FROM paiement paie
            JOIN menage men ON paie.menage = men.id
            WHERE {$flt['where']}
              AND NOT EXISTS (
                SELECT 1 FROM recu_annule ra
                WHERE ra.recu_id = paie.id
                  AND ra.recu_type = 'scolaire'
              )
            ORDER BY paie.dateCreated DESC, paie.id DESC";
    $st = $con->prepare($sql);
    $st->bind_param($flt['types'], ...$flt['params']);
    $st->execute();
    $rs = $st->get_result();

    header('Content-Type: text/html; charset=UTF-8');
    if (!$rs || $rs->num_rows <= 0) {
        echo '<tr><td colspan="9"><div class="alert alert-danger mb-0">Aucune donnée</div></td></tr>';
        exit;
    }
    while ($row = $rs->fetch_assoc()):
?>
<tr>
    <td><?= (int)$row['id']; ?></td>
    <td>
        <span class="badge badge-info me-1">Scolaire</span>
        <a href="../menage/detail_menage.php?id=<?= (int)$row['idMenage']; ?>">
            <?= htmlspecialchars($row['menage']); ?>
        </a>
    </td>
    <td><?= number_format((float)$row['montantAPayer'], 2, '.', ' ') ?> $</td>
    <td class="text-primary"><?= number_format((float)$row['montantPayer'], 2, '.', ' ') ?> $</td>
    <td><?= number_format((float)$row['resteAPayer'], 2, '.', ' ') ?> $</td>
    <td class="text-danger"><?= htmlspecialchars($row['observation']); ?></td>
    <td><?= htmlspecialchars($row['dateCreated']); ?></td>
    <td><a href="apercu_recu.php?ordre=<?= (int)$row['id']; ?>" class="btn btn-danger">Imprimer</a></td>
    <td>
        <button class="btn btn-outline-warning btn-annuler"
                data-recu-id="<?= (int)$row['id']; ?>"
                data-recu-type="scolaire"
                data-montant="<?= number_format((float)$row['montantPayer'], 2, '.', ' ') ?>">
            Annuler
        </button>
    </td>
</tr>
<?php
    endwhile;
    exit;
}
?>

<div class="main-panel">
    <div class="content-wrapper">

        <div class="row">
            <div class="col-12 grid-margin">
                <div class="card">
                    <div class="card-body">
                        <h3 class="card-title">
                            <span class="menu-icon"><i class="fa fa-user-circle"></i></span>
                            Historique des paiements
                        </h3>

                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <div class="wrapper-entity mt-3">
                                <a href="nouveau-paiement.php" class="btn btn-success">Nouveau cas</a>
                            </div>

                            <!-- FILTRES (soumission classique) -->
                            <form id="filter-form" class="row g-2 mt-3" method="get" action="">
                                <input type="hidden" name="annee_sco"
                                    value="<?= htmlspecialchars($currentFilters['annee_sco'] ?? '') ?>">

                                <div class="col-auto">
                                    <label class="form-label mb-0">Date</label>
                                    <input type="date" name="date" class="form-control"
                                        value="<?= htmlspecialchars($currentFilters['date'] ?? '') ?>">
                                </div>

                                <div class="col-auto">
                                    <label class="form-label mb-0">Mois</label>
                                    <select name="mois" class="form-control">
                                        <option value="">--</option>
                                        <?php for($m=1;$m<=12;$m++): ?>
                                        <option value="<?=$m?>"
                                            <?= (!empty($currentFilters['mois']) && (int)$currentFilters['mois']===$m)?'selected':''; ?>>
                                            <?=$m?>
                                        </option>
                                        <?php endfor; ?>
                                    </select>
                                </div>

                                <div class="col-auto">
                                    <label class="form-label mb-0">Année (EX : 2025)</label>
                                    <input type="number" name="annee" class="form-control" min="2000" max="2100"
                                        value="<?= htmlspecialchars($currentFilters['annee'] ?? '') ?>">
                                </div>

                                <div class="col-auto">
                                    <label class="form-label mb-0">Du</label>
                                    <input type="date" name="du" class="form-control"
                                        value="<?= htmlspecialchars($currentFilters['du'] ?? '') ?>">
                                </div>

                                <div class="col-auto">
                                    <label class="form-label mb-0">Au</label>
                                    <input type="date" name="au" class="form-control"
                                        value="<?= htmlspecialchars($currentFilters['au'] ?? '') ?>">
                                </div>

                                <!-- RECHERCHE MENAGE : mise à jour du tableau (AJAX interne) -->
                                <div class="d-none col-auto">
                                    <label class="form-label mb-0">Ménage</label>
                                    <input type="text" name="menage" id="search-menage" class="form-control"
                                        placeholder="Nom ménage…"
                                        value="<?= htmlspecialchars($currentFilters['menage'] ?? '') ?>">
                                </div>

                                <div class="col-auto align-self-end">
                                    <button type="submit" class="btn btn-primary">Filtrer</button>
                                </div>

                                <div class="col-auto align-self-end">
                                    <a id="export-btn" href="#" class="btn btn-danger">Export CSV</a>
                                </div>
                            </form>
                        </div>

                        <!-- Badges filtres actifs -->
                        <?php if (!empty($currentFilters['labels'])): ?>
                        <div class="mt-3">
                            <?php foreach ($currentFilters['labels'] as $lab): ?>
                            <span class="badge badge-info"
                                style="margin-right:6px;"><?= htmlspecialchars($lab) ?></span>
                            <?php endforeach; ?>
                            <a href="history.php" class="badge badge-light text-danger">Réinitialiser</a>
                        </div>
                        <?php endif; ?>

                        <div class="row mt-3">
                            <?php if ($currentFilters['total_jour'] !== null): ?>
                            <div class="col-md-4">
                                <div class="alert alert-success mb-0">
                                    <strong>Total du jour (<?= htmlspecialchars($currentFilters['date']) ?>) :</strong>
                                    <?= number_format((float)$currentFilters['total_jour'], 2, '.', ' ') ?> $
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Totaux -->
        <div class="d-none row">
            <div class="col-12 grid-margin">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Totaux</h4>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="alert alert-primary mb-0">
                                    <strong>Total période filtrée :</strong>
                                    <?= number_format((float)$afficheTotalDePaiements['montantPayer'] ?? 0, 2, '.', ' ') ?> $
                                </div>
                            </div>
                            <?php if ($currentFilters['total_jour'] !== null): ?>
                            <div class="col-md-4">
                                <div class="alert alert-success mb-0">
                                    <strong>Total du jour (<?= htmlspecialchars($currentFilters['date']) ?>) :</strong>
                                    <?= number_format((float)$currentFilters['total_jour'], 2, '.', ' ') ?> $
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Historique + recherche rapide -->
        <div class="row">
            <div class="col-12 grid-margin">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <h4 class="card-title mb-0">Historique des paiements</h4>

                            <!-- Recherche rapide côté client -->
                            <div class="d-flex align-items-center gap-2">
                                <input type="text" id="table-search" class="form-control"
                                    placeholder="Recherche rapide (ID, ménage, obs, date...)">
                                <span class="badge badge-dark text-white" id="result-count"></span>
                            </div>
                        </div>

                        <div class="table-responsive mt-3">
                            <table class="table" id="myTable">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Ménage</th>
                                        <th>Montant à payer</th>
                                        <th class="text-primary">Montant payé</th>
                                        <th>Reste à payer</th>
                                        <th class="text-danger">Obs.</th>
                                        <th>Date paiement</th>
                                        <th>Imprimer</th>
                                        <th>Annuler</th>
                                    </tr>
                                </thead>
                                <tbody id="table-body">
                                    <?php if (!$rstlistPaiement || $rstlistPaiement->num_rows <= 0): ?>
                                    <tr>
                                        <td colspan="9">
                                            <div class="alert alert-danger mb-0">Aucune donnée</div>
                                        </td>
                                    </tr>
                                    <?php else: ?>
                                    <?php while ($row = $rstlistPaiement->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= (int)$row['id']; ?></td>
                                        <td>
                                            <span class="badge badge-info me-1">Scolaire</span>
                                            <a href="../menage/detail_menage.php?id=<?= (int)$row['idMenage']; ?>">
                                                <?= htmlspecialchars($row['menage']); ?>
                                            </a>
                                        </td>
                                        <td><?= number_format((float)$row['montantAPayer'], 2, '.', ' ') ?> $</td>
                                        <td class="text-primary">
                                            <?= number_format((float)$row['montantPayer'], 2, '.', ' ') ?> $</td>
                                        <td><?= number_format((float)$row['resteAPayer'], 2, '.', ' ') ?> $</td>
                                        <td class="text-danger"><?= htmlspecialchars($row['observation']); ?></td>
                                        <td><?= htmlspecialchars($row['dateCreated']); ?></td>
                                        <td><a href="apercu_recu.php?ordre=<?= (int)$row['id']; ?>"
                                                class="btn btn-danger">Imprimer</a></td>
                                        <td>
                                            <button class="btn btn-outline-warning btn-annuler"
                                                    data-recu-id="<?= (int)$row['id']; ?>"
                                                    data-recu-type="scolaire"
                                                    data-montant="<?= number_format((float)$row['montantPayer'], 2, '.', ' ') ?>">
                                                Annuler
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>
            </div>
        </div>

    </div>
    <?php require_once ('../../layouts/constants/footer.php'); ?>
</div>

<!-- Modal Annulation + JS -->
<div class="modal fade" id="modalAnnuler" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form id="formAnnuler" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Annuler le reçu</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="recu_id" id="annul_recu_id">
        <input type="hidden" name="recu_type" id="annul_recu_type">
        <div class="mb-2">
          <label class="form-label">Montant</label>
          <input type="text" id="annul_montant" class="form-control" disabled>
        </div>
        <div class="mb-2">
          <label class="form-label">Motif (optionnel)</label>
          <input type="text" name="motif" class="form-control" placeholder="Ex: double saisie">
        </div>
        <div class="mb-2">
          <label class="form-label">Code de confirmation</label>
          <input type="password" name="code" class="form-control" placeholder="Tapez 1024" required>
        </div>
        <div class="alert alert-warning small mb-0">
          L’annulation retirera ce reçu de tous les totaux et historiques (sans le supprimer physiquement).
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal" type="button">Fermer</button>
        <button class="btn btn-warning" type="submit">Confirmer l’annulation</button>
      </div>
    </form>
  </div>
</div>

<script>
// Export CSV : utilise les valeurs du formulaire courant
(function() {
    const btn = document.getElementById('export-btn');
    const form = document.getElementById('filter-form');
    if (btn && form) {
      btn.addEventListener('click', function(e) {
        e.preventDefault();
        const fd = new FormData(form);
        const params = new URLSearchParams(fd);
        params.set('export', 'csv');
        window.location = '?' + params.toString(); // download natif
      });
    }
})();

// Recherche Ménage AJAX + recherche rapide
(function() {
    const form = document.getElementById('filter-form');
    const input = document.getElementById('search-menage');
    const tbody = document.getElementById('table-body');
    const quick = document.getElementById('table-search');
    let t = null;

    function buildAjaxUrl() {
        const fd = new FormData(form);
        const params = new URLSearchParams(fd);
        params.set('ajax', 'table');
        return '?' + params.toString();
    }

    async function refreshTable() {
        const url = buildAjaxUrl();
        const r = await fetch(url, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        const html = await r.text();
        tbody.innerHTML = html;
        if (quick && quick.value.trim() !== '') {
            applyQuickFilter(quick.value.trim());
        } else {
            updateCount();
        }
    }

    if (input) {
      input.addEventListener('input', function() {
          clearTimeout(t);
          t = setTimeout(refreshTable, 300);
      });
    }

    const counter = document.getElementById('result-count');
    function updateCount() {
        const visible = [...tbody.querySelectorAll('tr')].filter(tr => tr.style.display !== 'none').length;
        const total = tbody.querySelectorAll('tr').length;
        if (counter) counter.textContent = visible + ' / ' + total;
    }

    function applyQuickFilter(q) {
        const query = q.toLowerCase();
        const rows = tbody.querySelectorAll('tr');
        rows.forEach(tr => {
            const txt = tr.textContent.toLowerCase();
            tr.style.display = txt.includes(query) ? '' : 'none';
        });
        updateCount();
    }

    if (quick) {
        quick.addEventListener('input', function() {
            applyQuickFilter(quick.value.trim());
        });
        updateCount();
    }
})();

// Modal Annulation : ouverture + submit
(function(){
  let modalEl = document.getElementById('modalAnnuler');
  let bsModal = null;
  function ensureModal(){
    if (!bsModal && window.bootstrap && bootstrap.Modal) {
      bsModal = new bootstrap.Modal(modalEl);
    }
  }

  document.querySelectorAll('.btn-annuler').forEach(btn => {
    btn.addEventListener('click', function(){
      ensureModal();
      document.getElementById('annul_recu_id').value = this.dataset.recuId;
      document.getElementById('annul_recu_type').value = this.dataset.recuType;
      document.getElementById('annul_montant').value = this.dataset.montant + ' $';
      if (bsModal) bsModal.show();
    });
  });

  const form = document.getElementById('formAnnuler');
  form && form.addEventListener('submit', async function(e){
    e.preventDefault();
    const fd = new FormData(this);
    try{
      // cancel_recu.php placé dans le même dossier
      const r = await fetch('cancel_recu.php', { method: 'POST', body: fd });
      const js = await r.json();
      if(!js.ok){ alert(js.message || 'Échec annulation'); return; }
      alert('Reçu annulé ✔');
      window.location.reload();
    }catch(err){
      alert('Erreur réseau.');
    }
  });
})();
</script>
