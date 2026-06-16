<?php
require_once ('../../layouts/constants/head.php');
require_once ('../../webapp/service/classe.service.php');
require_once ('../../layouts/navbar/navbar.php');
?>

<div class="main-panel-copy">
  <div class="content-wrapper">
    <div class="row">
      <div class="col-12 grid-margin">
        <div class="card">
          <div class="card-body">
            <h3 class="card-title">
              <span class="menu-icon"><i class="fa fa-user-circle"></i></span>
              Classe
            </h3>

            <h6 class="wrapper-filtrage">Filtrage des données :</h6>

            <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap">
              <div class="wrapper-entity">
                <a href="create-update.php" class="btn btn-success btn-block enter-btn">Nouvelle classe</a>
              </div>

              <div class="d-flex align-items-center gap-2">
                <a class="btn border me-2" href="../cycle/">Cycle</a>
                <input type="text" id="noms" class="form-control p_input" placeholder="Rechercher une classe / cycle"
                       onkeyup="myFunctionSearch()">
              </div>
            </div>

          </div>
        </div>
      </div>
    </div>

    <!-- LISTE -->
    <div class="row">
      <div class="col-12 grid-margin">
        <div class="card">
          <div class="card-body">
            <h4 class="card-title">Liste des classes</h4>

            <div class="table-responsive">
              <table class="table" id="myTable">
                <thead>
                  <tr>
                    <th>ID classe</th>
                    <th>Description</th>
                    <th>Cycle</th>
                    <th>Nbre d'élèves</th>
                    <th style="width:110px;"></th>
                  </tr>
                </thead>
                <tbody>
                <?php if (!$rsts || $rsts->num_rows <= 0): ?>
                  <tr>
                    <td colspan="5">
                      <div class="alert alert-warning mb-0">Aucune classe trouvée.</div>
                    </td>
                  </tr>
                <?php else: ?>
                  <?php while ($row = $rsts->fetch_assoc()): ?>
                    <tr>
                      <td><?= (int)$row['id']; ?></td>
                      <td><?= htmlspecialchars($row['description'], ENT_QUOTES, 'UTF-8'); ?></td>
                      <td><?= htmlspecialchars($row['cycle'], ENT_QUOTES, 'UTF-8'); ?></td>
                      <td><?= (int)$row['nb_eleves']; ?></td>
                      <td>
                        <a href="detail_classe.php?id=<?= (int)$row['id']; ?>" class="btn btn-sm btn-primary">
                          Voir
                        </a>
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

<!-- Recherche simple côté client -->
<script>
function myFunctionSearch() {
  const input = document.getElementById("noms");
  const filter = input.value.toLowerCase();
  const table = document.getElementById("myTable");
  const trs = table.getElementsByTagName("tr");

  // colonnes Description (1) et Cycle (2) comme clés
  for (let i = 1; i < trs.length; i++) {
    const tds = trs[i].getElementsByTagName("td");
    if (!tds || tds.length === 0) continue;

    const description = (tds[1].innerText || tds[1].textContent).toLowerCase();
    const cycle       = (tds[2].innerText || tds[2].textContent).toLowerCase();

    if (description.indexOf(filter) > -1 || cycle.indexOf(filter) > -1) {
      trs[i].style.display = "";
    } else {
      trs[i].style.display = "none";
    }
  }
}
</script>
