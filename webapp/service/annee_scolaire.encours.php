<?php
    require_once('../../webapp/database/config.php');

    // afficher l'annee scolaire encours
    $sql = "SELECT * FROM annee_scolaire WHERE status = 'encours'";
    $anneeEnCours = mysqli_query($con, $sql);
    $annee = mysqli_fetch_assoc($anneeEnCours);
    $annee_scolaire = $annee['annee_scolaire'];
    // if ($anneeEnCours && mysqli_num_rows($anneeEnCours) > 0) {
    //     // On récupère la première ligne du résultat sous forme de tableau associatif
    //     $annee = mysqli_fetch_assoc($anneeEnCours);

    //     // Exemple d'utilisation : afficher l'année
    //     echo "Année scolaire en cours : " . $annee['annee_scolaire'];
    // } else {
    //     echo "Aucune année scolaire en cours trouvée.";
    // }


?>