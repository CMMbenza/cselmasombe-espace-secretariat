<?php
header('Content-Type: application/json');

// Configuration de votre connexion PDO
$host = 'localhost';
$db   = 'votre_base_de_donnees';
$user = 'votre_utilisateur';
$pass = 'votre_mot_de_passe';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Erreur de connexion DB']);
    exit;
}

$action = $_GET['action'] ?? '';

// 1. RECHERCHE MÉNAGE
if ($action === 'search') {
    $q = $_GET['q'] ?? '';
    $stmt = $pdo->prepare("SELECT id, noms, telephone FROM menage WHERE noms LIKE :q OR telephone LIKE :q LIMIT 5");
    $stmt->execute(['q' => "%$q%"]);
    echo json_encode($stmt->fetchAll());
    exit;
}

// 2. RÉCUPÉRER LES DÉTAILS ET DÉTECTER LE STATUT DU PAIEMENT
if ($action === 'get_details') {
    $id = intval($_GET['id']);

    // Infos Ménage
    $stmt = $pdo->prepare("SELECT * FROM menage WHERE id = :id");
    $stmt->execute(['id' => $id]);
    $menage = $stmt->fetch();

    if (!$menage) {
        echo json_encode(['error' => 'Ménage introuvable']);
        exit;
    }

    // Nombre de paiements scolaires effectués
    $stmtCount = $pdo->prepare("SELECT COUNT(*) as nb FROM paiement WHERE menage = :id");
    $stmtCount->execute(['id' => $id]);
    $nbPaiements = $stmtCount->fetch()['nb'];

    // Somme déjà payée en Scolarité
    $stmtScolaire = $pdo->prepare("SELECT COALESCE(SUM(montantPayer), 0) as total_scolarite FROM paiement WHERE menage = :id");
    $stmtScolaire->execute(['id' => $id]);
    $scolaritePaye = (float) $stmtScolaire->fetch()['total_scolarite'];

    // Somme déjà payée en Divers
    $stmtDivers = $pdo->prepare("SELECT COALESCE(SUM(montantPayer), 0) as total_divers FROM paiement_divers WHERE menage = :id");
    $stmtDivers->execute(['id' => $id]);
    $diversPaye = (float) $stmtDivers->fetch()['total_divers'];

    echo json_encode([
        'menage' => $menage,
        'nb_paiements_scolaire' => (int) $nbPaiements,
        'totaux' => [
            'scolarite_paye' => $scolaritePaye,
            'divers_paye' => $diversPaye
        ]
    ]);
    exit;
}

// 3. ENREGISTRER UN PAIEMENT (SCOLARITÉ OU DIVERS)
if ($action === 'save_paiement' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $menageId = intval($_POST['menage_id']);
    $typeOp = $_POST['type_operation'];
    $montantPayer = (float) $_POST['montantPayer'];
    $observation = $_POST['observation'] ?? '';

    // Récupérer le ménage
    $stmt = $pdo->prepare("SELECT * FROM menage WHERE id = :id");
    $stmt->execute(['id' => $menageId]);
    $menage = $stmt->fetch();

    if ($typeOp === 'scolarite') {
        // Obtenir le total déjà payé pour calculer le reste
        $stmtSum = $pdo->prepare("SELECT COALESCE(SUM(montantPayer), 0) as total FROM paiement WHERE menage = :id");
        $stmtSum->execute(['id' => $menageId]);
        $dejaPaye = (float) $stmtSum->fetch()['total'];

        $montantTotal = (float) $menage['montantAPayer'];
        $nouveauReste = $montantTotal - ($dejaPaye + $montantPayer);

        $insert = $pdo->prepare("INSERT INTO paiement (menage, montantAPayer, montantPayer, resteAPayer, observation, dateCreated, anneeScolaire) 
                                 VALUES (:menage, :montantAPayer, :montantPayer, :resteAPayer, :observation, NOW(), :anneeScolaire)");
        $insert->execute([
            'menage' => $menageId,
            'montantAPayer' => $montantTotal,
            'montantPayer' => $montantPayer,
            'resteAPayer' => $nouveauReste,
            'observation' => $observation,
            'anneeScolaire' => $menage['anneeScolaire']
        ]);

    } elseif ($typeOp === 'divers') {
        $typeFrais = $_POST['type_frais'];
        $montantAPayerFC = (float) $menage['montantAPayerFC'];

        $stmtSum = $pdo->prepare("SELECT COALESCE(SUM(montantPayer), 0) as total FROM paiement_divers WHERE menage = :id");
        $stmtSum->execute(['id' => $menageId]);
        $dejaPaye = (float) $stmtSum->fetch()['total'];

        $nouveauReste = $montantAPayerFC - ($dejaPaye + $montantPayer);

        $insert = $pdo->prepare("INSERT INTO paiement_divers (menage, type_frais, montantAPayer, montantPayer, resteAPayer, observation, createdby, anneeScolaire) 
                                 VALUES (:menage, :type_frais, :montantAPayer, :montantPayer, :resteAPayer, :observation, :createdby, :anneeScolaire)");
        $insert->execute([
            'menage' => $menageId,
            'type_frais' => $typeFrais,
            'montantAPayer' => $montantAPayerFC,
            'montantPayer' => $montantPayer,
            'resteAPayer' => $nouveauReste,
            'observation' => $observation,
            'createdby' => 'Caisse',
            'anneeScolaire' => $menage['anneeScolaire']
        ]);
    }

    echo json_encode(['success' => true]);
    exit;
}