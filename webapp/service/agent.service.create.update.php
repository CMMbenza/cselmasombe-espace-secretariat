<?php
    require_once('../../webapp/database/config.php');

    if (isset($_POST['submit'])) {

        $nom = $_POST['nom'];  
        $postnom = $_POST['postnom'];  
        $prenom = $_POST['prenom'];  
        $genre = $_POST['genre'];  
        $lieu = $_POST['lieu'];  
        $dateDeNaissance = $_POST['dateDeNaissance'];  
        $fonction = $_POST['fonction'];  
        $grade = $_POST['grade'];  
        $niveau = $_POST['niveau'];  
        $salaire = $_POST['salaire'];  
        
        $sql = "INSERT INTO agent VALUES (NULL, '$nom', '$postnom', '$prenom', '$genre', '$lieu', '$dateDeNaissance', '$niveau', '$grade', '$fonction', '$salaire', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, 'Administrateur(trice)', '$annee_scolaire')";
        mysqli_query($con, $sql);
    }
?>