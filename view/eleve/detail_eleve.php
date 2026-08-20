<?php 
require_once ('../../layouts/constants/head.php');
require_once ('../../layouts/navbar/navbar.php');
require_once('../../webapp/database/config.php');

/** Helpers */
function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function fmt($n){ return number_format((float)$n, 2, '.', ' '); }

/** ID Élève */
$idEleve = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($idEleve <= 0) {
  echo '<div class="container mt-4"><div class="alert alert-danger">Élève introuvable (ID invalide).</div></div>';
  require_once ('../../layouts/constants/footer.php'); exit;
}

/* ===================== Élève + Classe + Cycle + Ménage ===================== */
$sqlEleve = "
  SELECT 
    e.id, e.matricule, e.nom, e.postnom, e.prenom, e.genre, e.lieu, e.dateDeNaissance, 
    e.nationalite, e.dateCreated, e.dateUpdate, e.anneeScolaire, e.createdby, e.STATUS,
    e.montant_a_payer, e.montantAPayerFC,
    cl.description AS nom_classe,
    cy.description AS nom_cycle,
    m.id AS menage_id, m.noms AS nom_menage, m.telephone AS telephone_menage,
    m.nom_du_pere, m.nom_de_la_mere, m.profesion, m.avenue, m.numero, m.quartier, m.commune, m.province
  FROM eleve e
  LEFT JOIN classe cl ON e.classe = cl.id
  LEFT JOIN cycle cy ON cl.cycle = cy.id
  LEFT JOIN menage m ON e.menage = m.id
  WHERE e.id = ?
  LIMIT 1
";

$stE = $con->prepare($sqlEleve);
$stE->bind_param('i', $idEleve);
$stE->execute();
$rsE = $stE->get_result();

if ($rsE->num_rows === 0) {
  echo '<div class="container mt-4"><div class="alert alert-warning">Élève introuvable.</div></div>';
  require_once ('../../layouts/constants/footer.php'); exit;
}
$el = $rsE->fetch_assoc();
$stE->close();

/* Variables Élève */
$matricule       = (string)$el['matricule'];
$nomComplet      = trim($el['nom'] . ' ' . $el['postnom'] . ' ' . $el['prenom']);
$genre           = (string)$el['genre'];
$lieu            = (string)$el['lieu'];
$dateDeNaissance = (string)$el['dateDeNaissance'];
$nationalite     = (string)$el['nationalite'];
$classe          = (string)($el['nom_classe'] ?? 'Non assignée');
$cycle           = (string)($el['nom_cycle'] ?? '');
$anneeScolaire   = (string)$el['anneeScolaire'];
$status          = (string)$el['STATUS'];
$createdby       = (string)$el['createdby'];
$dateCreated     = (string)$el['dateCreated'];
$dateUpdate      = (string)$el['dateUpdate'];

$montantUSD      = (float)$el['montant_a_payer'];
$montantFC       = (float)$el['montantAPayerFC'];

/* Variables Ménage / Parents */
$menageId        = (int)$el['menage_id'];
$nomMenage       = (string)$el['nom_menage'];
$phoneMenage     = (string)$el['telephone_menage'];
$nomPere         = (string)$el['nom_du_pere'];
$nomMere         = (string)$el['nom_de_la_mere'];
$profession      = (string)$el['profesion'];
$adresseMenage   = 'AV. ' . h($el['avenue']) . ', N° ' . h($el['numero']) . ', Q. ' . h($el['quartier']) . ', C. ' . h($el['commune']) . ' PROVINCE : ' . h($el['province']) . '';
?>

<body>
    <div class="main-panel">
        <div class="content-wrapper">

            <div class="row">
                <!-- Carte Informations Personnelles & Scolaires de l'Élève -->
                <div class="col-lg-7 col-sm-12 grid-margin">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title text-uppercase d-flex align-items-center justify-content-between">
                                <span>Profil Élève</span>
                                <button type="button" class="btn btn-primary btn-sm" onclick="history.back()">&lt;
                                    Retour</button>
                            </h5>
                            <hr>
                            <dl class="row jh-entity-details">
                                <dt class="col-sm-4">Matricule</dt>
                                <dd class="col-sm-8"><span class="badge bg-dark fs-6"><?= h($matricule) ?></span></dd>

                                <dt class="col-sm-4">Nom complet</dt>
                                <dd class="col-sm-8"><strong class="fs-5"><?= h($nomComplet) ?></strong></dd>

                                <dt class="col-sm-4">Sexe / Genre</dt>
                                <dd class="col-sm-8"><span class="badge bg-primary"><?= h($genre) ?></span></dd>

                                <dt class="col-sm-4">Lieu de naissance</dt>
                                <dd class="col-sm-8"><span><?= h($lieu) ?></span></dd>

                                <dt class="col-sm-4">Date de naissance</dt>
                                <dd class="col-sm-8"><span><?= h($dateDeNaissance) ?></span></dd>

                                <dt class="col-sm-4">Nationalité</dt>
                                <dd class="col-sm-8"><span><?= h($nationalite) ?></span></dd>

                                <hr class="my-3">

                                <dt class="col-sm-4">Classe & Cycle</dt>
                                <dd class="col-sm-8"><span class="fw-bold"><?= h($classe) ?> <?= h($cycle) ?></span>
                                </dd>

                                <dt class="col-sm-4">Année scolaire</dt>
                                <dd class="col-sm-8"><span><?= h($anneeScolaire) ?></span></dd>

                                <dt class="col-sm-4">Statut</dt>
                                <dd class="col-sm-8">
                                    <span class="badge <?= $status === 'actif' ? 'bg-success' : 'bg-danger' ?>">
                                        <?= strtoupper(h($status)) ?>
                                    </span>
                                </dd>

                                <hr class="my-3">

                                <dt class="col-sm-4">Inscrit par</dt>
                                <dd class="col-sm-8"><span><?= h($createdby) ?></span></dd>

                                <dt class="col-sm-4">Date création</dt>
                                <dd class="col-sm-8"><span><?= h($dateCreated) ?></span></dd>

                                <dt class="col-sm-4">Dernière modification</dt>
                                <dd class="col-sm-8"><span><?= h($dateUpdate) ?></span></dd>
                            </dl>
                        </div>
                    </div>
                </div>

                <!-- Colonne de droite : Famille & Montants -->
                <div class="col-lg-5 col-sm-12 grid-margin">
                    <!-- Bloc Informations Familiales -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <h5 class="card-title text-uppercase">Informations Familiales</h5>
                            <hr>
                            <?php if ($menageId > 0): ?>
                            <dl class="row jh-entity-details">
                                <dt class="col-sm-4">Ménage / Famille</dt>
                                <dd class="col-sm-8">
                                    <a href="../menage/detail_menage.php?id=<?= $menageId ?>"
                                        class="fw-bold text-decoration-none">
                                        <?= h($nomMenage) ?>
                                    </a>
                                </dd>

                                <dt class="col-sm-4">Nom du père</dt>
                                <dd class="col-sm-8"><span><?= h($nomPere) ?></span></dd>

                                <dt class="col-sm-4">Nom de la mère</dt>
                                <dd class="col-sm-8"><span><?= h($nomMere) ?></span></dd>

                                <dt class="col-sm-4">Profession</dt>
                                <dd class="col-sm-8"><span><?= h($profession) ?></span></dd>

                                <dt class="col-sm-4">Téléphone</dt>
                                <dd class="col-sm-8"><span><?= h($phoneMenage) ?></span></dd>

                                <dt class="col-sm-4">Adresse</dt>
                                <dd class="col-sm-8"><span><?= $adresseMenage ?></span></dd>
                            </dl>
                            <?php else: ?>
                            <div class="alert alert-warning mb-0">Aucun ménage ou tuteur rattaché à cet élève.</div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Bloc Montants de l'Élève -->
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title text-uppercase">Montants à payer de l'Élève</h5>
                            <hr>
                            <div class="row text-center">
                                <div class="col-6">
                                    <dt class="text-muted">Montant USD</dt>
                                    <dd class="fs-4 text-primary fw-bold mt-2"><?= fmt($montantUSD) ?> $</dd>
                                </div>
                                <div class="col-6">
                                    <dt class="text-muted">Montant USD</dt>
                                    <dd class="fs-4 text-primary fw-bold mt-2"><?= fmt($montantFC) ?> USD</dd>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div><!-- /content-wrapper -->

        <?php require_once ('../../layouts/constants/footer.php'); ?>
</body>