<?php 
require_once ('../../layouts/constants/head.php');
require_once ('../../webapp/service/caisse.service.php');  // calcule cycles (gauche) + résumés mensuels (droite)
require_once ('../../layouts/navbar/navbar.php');

/* Helpers sûrs */
if (!function_exists('e'))  { function e($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); } }
if (!function_exists('nf')) { function nf($n){ return number_format((float)$n, 2, ',', ' '); } }

/* Récup service */
$mois_param      = $CAISSE['mois_param'];
$annee_scolaire  = $CAISSE['annee_scolaire'];
$periode_annee   = $CAISSE['periode_annee'];
$periode_mois    = $CAISSE['periode_mois'];

/* Scolaire */
$total_paie      = $CAISSE['total_paiements_mois'];
$total_dep       = $CAISSE['total_depenses_mois'];      // (Frais Scolaire)
$net             = $CAISSE['net_mois'];
$deja_verse      = $CAISSE['deja_verse'];
$flash_success   = $CAISSE['flash_success'];
$flash_error     = $CAISSE['flash_error'];
$mois_verses     = $CAISSE['mois_verses'];

/* Divers */
$total_paie_div  = $CAISSE['total_paiements_divers_mois'];
$total_dep_div   = $CAISSE['total_depenses_connexe_mois']; // (Frais Connexe)
$net_div         = $CAISSE['net_mois_divers'];
$deja_verse_div  = $CAISSE['deja_verse_divers'];
$flash_success_div = $CAISSE['flash_success_divers'];
$flash_error_div   = $CAISSE['flash_error_divers'];

/* Map mois->année pour la grille */
$annee1 = null; $annee2 = null;
if (preg_match('~^(\d{4})-(\d{4})$~', $annee_scolaire, $m)) {
  $annee1 = (int)$m[1];
  $annee2 = (int)$m[2];
}
$yearForMonth = function(int $mois) use ($annee1,$annee2,$periode_annee) : int {
  if ($annee1 && $annee2) return ($mois >= 9 ? $annee1 : $annee2);
  return ($mois >= 9) ? $periode_annee : ($periode_annee + 1);
};

/* Détail du virement scolaire du mois affiché */
$detail_balance = null;
try {
  $yr = (int)substr($mois_param, 0, 4);
  $mo = (int)substr($mois_param, 5, 2);
  if ($annee1 && $annee2) $yr = $yearForMonth($mo);

  $sqlDet = "SELECT id, entre, sorti, reste, dateBalance
             FROM balance
             WHERE type_virement = 'Dépôt du frais scolaire'
               AND anneeScolaire = ?
               AND periode_annee = ?
               AND periode_mois = ?
             LIMIT 1";
  $st = $con->prepare($sqlDet);
  $st->bind_param('sii', $annee_scolaire, $yr, $mo);
  $st->execute();
  $rs = $st->get_result();
  $detail_balance = $rs->fetch_assoc() ?: null;
  $st->close();
} catch (Throwable $t) {
  $detail_balance = null;
}
?>
<style>
.bg-light-success {
    background-color: #e6f4ea;
}

.bg-light-danger {
    background-color: #fce9e9;
}

.bg-alert {
    border: none;
}

.rotate-icon {
    transition: transform .3s ease;
}

.rotate-icon.active {
    transform: rotate(180deg);
}

.pointer {
    cursor: pointer;
}
</style>

<body>
    <div class="main-panel-copy">
        <div class="content-wrapper">
            <div class="row">

                <!-- ========== COLONNE GAUCHE (inchangée) ========== -->
                <div class="col-lg-6 col-sm-12 grid-margin">
                    <!-- Carte 2 : Résumé mensuel (Frais Divers) -->
                    <div class="card mt-3">
                        <div class="card-body">
                            <h3 class="card-title mb-3 text-uppercase">
                                <span class="menu-icon"><i class="fa fa-user-circle"></i></span>
                                R&eacute;sum&eacute; mensuel (Frais Divers)
                            </h3>

                            <!-- Messages de retour (divers) -->
                            <?php if ($flash_success_div): ?>
                            <div class="alert alert-success"><?php echo e($flash_success_div); ?></div>
                            <?php endif; ?>
                            <?php if ($flash_error_div): ?>
                            <div class="alert alert-danger"><?php echo e($flash_error_div); ?></div>
                            <?php endif; ?>

                            <!-- 2 DIV : paiements DIVERS + dépenses CONNEXES -->
                            <div class="row mt-1">
                                <div class="col-md-6">
                                    <div class="alert alert-warning bg-alert">
                                        <label class="card-label">Paiements (Divers) —
                                            <?php echo e($mois_param); ?></label>
                                        <h3 class="card-text mb-0"><?php echo nf($total_paie_div); ?> $</h3>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="alert alert-danger bg-alert">
                                        <label class="card-label">D&eacute;penses &laquo; Frais Connexe &raquo;</label>
                                        <h3 class="card-text mb-0"><?php echo nf($total_dep_div); ?> $</h3>
                                    </div>
                                </div>
                            </div>

                            <!-- Net -->
                            <div class="mt-2">
                                <span class="badge bg-secondary">Net (Divers) :
                                    <strong><?php echo nf($net_div); ?> $</strong></span>
                            </div>

                            <p class="text-muted small mb-2">
                                Virement (divers) = paiements divers &minus; d&eacute;penses <em>Frais Connexe</em>.
                            </p>

                            <!-- Bouton Virement (divers) -->
                            <form method="post">
                                <input type="hidden" name="verser_balance_divers" value="1">
                                <?php if ($deja_verse_div): ?>
                                <button type="button" class="btn btn-secondary" disabled>
                                    <i class="fa fa-check me-1"></i> Virement (divers) d&eacute;j&agrave;
                                    effectu&eacute;
                                </button>
                                <?php else: ?>
                                <button type="submit" class="btn btn-primary"
                                    onclick="return confirm('Confirmer le virement (frais divers) du mois <?php echo e($mois_param); ?> ?');">
                                    <i class="fa fa-exchange me-1"></i> Virement (divers)
                                </button>
                                <?php endif; ?>
                            </form>

                        </div>
                    </div>
                </div>

                <!-- ========== COLONNE DROITE ========== -->
                <div class="col-lg-6 col-sm-12 grid-margin">

                    <!-- Carte 1 : Résumé mensuel (Frais Scolaire) -->
                    <div class="card">
                        <div class="card-body">
                            <h3 class="card-title mb-3 text-uppercase">
                                <span class="menu-icon"><i class="fa fa-user-circle"></i></span>
                                R&eacute;sum&eacute; mensuel (Frais Scolaire)
                            </h3>

                            <!-- Filtre par mois + année scolaire -->
                            <form method="get" class="row g-3 align-items-end">
                                <div class="col-md-5">
                                    <label class="form-label">Mois</label>
                                    <input type="month" name="mois" value="<?php echo e($mois_param); ?>"
                                        class="form-control">
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label">Ann&eacute;e scolaire</label>
                                    <input type="text" name="annee_scolaire" value="<?php echo e($annee_scolaire); ?>"
                                        class="form-control" placeholder="2024-2025">
                                </div>
                                <div class="col-md-2">
                                    <button class="btn btn-primary w-100"><i class="fa fa-filter me-1"></i> OK</button>
                                </div>
                            </form>

                            <!-- Messages de retour -->
                            <?php if ($flash_success): ?>
                            <div class="alert alert-success mt-3"><?php echo e($flash_success); ?></div>
                            <?php endif; ?>
                            <?php if ($flash_error): ?>
                            <div class="alert alert-danger mt-3"><?php echo e($flash_error); ?></div>
                            <?php endif; ?>

                            <!-- 2 DIV : paiements scolaires + dépenses SCOLAIRES -->
                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <div class="alert alert-success bg-alert">
                                        <label class="card-label">Somme paiements
                                            (<?php echo e($mois_param); ?>)</label>
                                        <h3 class="card-text mb-0"><?php echo nf($total_paie); ?> $</h3>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="alert alert-danger bg-alert">
                                        <label class="card-label">D&eacute;penses &laquo; Frais Scolaire &raquo;</label>
                                        <h3 class="card-text mb-0"><?php echo nf($total_dep); ?> $</h3>
                                    </div>
                                </div>
                            </div>

                            <!-- Net -->
                            <div class="mt-2">
                                <span class="badge bg-secondary">Net (Scolaire) :
                                    <strong><?php echo nf($net); ?> $</strong></span>
                            </div>

                            <!-- Bouton Virement (scolaire) -->
                            <form method="post" class="mt-2">
                                <input type="hidden" name="verser_balance" value="1">
                                <?php if ($deja_verse): ?>
                                <button type="button" class="btn btn-secondary" disabled>
                                    <i class="fa fa-check me-1"></i> Virement d&eacute;j&agrave; effectu&eacute;
                                </button>
                                <?php else: ?>
                                <button type="submit" class="btn btn-primary"
                                    onclick="return confirm('Confirmer le virement (frais scolaire) du mois <?php echo e($mois_param); ?> ?');">
                                    <i class="fa fa-exchange me-1"></i> Virement
                                </button>
                                <?php endif; ?>
                            </form>

                        </div>
                    </div>

                </div><!-- /.col droite -->

            </div><!-- /.row -->

            <!-- ====== Suivi des virements — Grille (vert si viré) + détail du mois sélectionné (SCOLAIRE) ====== -->
            <?php
      $mois_noms = [1=>'Janvier',2=>'F&eacute;vrier',3=>'Mars',4=>'Avril',5=>'Mai',6=>'Juin',7=>'Juillet',8=>'Ao&ucirc;t',9=>'Septembre',10=>'Octobre',11=>'Novembre',12=>'D&eacute;cembre'];
    ?>
            <div class="row">
                <div class="col-12 grid-margin">
                    <div class="card mt-2">
                        <div class="card-body">
                            <h5 class="card-title text-uppercase">Suivi des virements &mdash;
                                <?php echo e($annee_scolaire); ?></h5>
                            <div class="row">
                                <?php foreach ($mois_noms as $num => $nom): 
                $isDone = in_array($num, $mois_verses, true);
                $cls    = $isDone ? 'bg-light-success text-success' : 'bg-light-danger text-danger';
                $icon   = $isDone ? 'fa-check-circle' : 'fa-times-circle';
                $year   = $yearForMonth($num);
                $qs     = http_build_query(['mois'=>sprintf('%04d-%02d', $year, $num),
                                            'annee_scolaire'=>$annee_scolaire]);
              ?>
                                <div class="col-md-3 col-sm-6 mb-2">
                                    <a href="?<?php echo $qs; ?>"
                                        class="d-flex align-items-center p-2 border rounded text-decoration-none <?php echo $cls; ?>">
                                        <i class="fa <?php echo $icon; ?> me-2"></i>
                                        <strong><?php echo $nom; ?></strong>
                                    </a>
                                </div>
                                <?php endforeach; ?>
                            </div>

                            <!-- Détail du virement SCOLAIRE du mois affiché -->
                            <div class="mt-3">
                                <h6 class="text-uppercase mb-2">D&eacute;tail (Frais scolaire) &mdash;
                                    <?php echo e($mois_param); ?></h6>
                                <?php if ($detail_balance): ?>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Entr&eacute;e</th>
                                                <th>Sortie</th>
                                                <th>Reste (Net)</th>
                                                <th>Date balance</th>
                                                <th>ID</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td><?php echo nf($detail_balance['entre']); ?> $</td>
                                                <td><?php echo nf($detail_balance['sorti']); ?> $</td>
                                                <td><?php echo nf($detail_balance['reste']); ?> $</td>
                                                <td><?php echo e($detail_balance['dateBalance']); ?></td>
                                                <td><?php echo e($detail_balance['id']); ?></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <?php else: ?>
                                <div class="alert alert-warning mb-0">
                                    Aucun virement (frais scolaire) trouv&eacute; pour <?php echo e($mois_param); ?>
                                    dans l'ann&eacute;e scolaire <?php echo e($annee_scolaire); ?>.
                                </div>
                                <?php endif; ?>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

        </div><!-- /.content-wrapper -->
        <?php require_once ('../../layouts/constants/footer.php'); ?>
    </div>
</body>