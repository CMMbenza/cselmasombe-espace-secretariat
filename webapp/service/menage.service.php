<?php
    require_once('../../webapp/database/config.php');
    require_once('annee_scolaire.encours.php');

    $sql = "SELECT 
        men.id, 
        men.noms AS noms, 
        COUNT(el.menage) AS nbreEnfant, 
        men.montantAPayer, 
        men.telephone, 
        men.numero, 
        men.avenue, 
        men.quartier, 
        men.commune, 
        men.dateCreated, 
        men.dateUpdate, 
        men.createdby,
        men.status
    FROM eleve el 
    JOIN menage men ON el.menage = men.id 
    WHERE men.anneeScolaire = '$annee_scolaire' AND men.status = 'actif'
    GROUP BY men.id 
    ORDER BY men.noms ASC";

    $rst = mysqli_query($con, $sql);
    
    
    $sql = "SELECT * FROM menage WHERE anneeScolaire = '$annee_scolaire' AND status = 'actif' ORDER BY noms ASC";
    $rst_menage_eleve = mysqli_query($con, $sql);

    $sql = "SELECT 
            men.id,
            men.noms,
            COUNT(el.id) AS nbreEnfant,
            men.montantAPayer,
            men.telephone,
            men.numero,
            men.avenue,
            men.quartier,
            men.commune,
            men.dateCreated,
            men.dateUpdate,
            men.createdby,
            men.status,

            -- frais connexe
            COALESCE(SUM(sco.montant),0) AS frais_connexe,

            -- TOTAL PAYÉ (scolaire + divers)
            (
                COALESCE((
                    SELECT SUM(p.montantPayer)
                    FROM paiement p
                    WHERE p.menage = men.id
                    AND p.anneeScolaire = men.anneeScolaire
                ),0)
                +
                COALESCE((
                    SELECT SUM(pd.montantPayer)
                    FROM paiement_divers pd
                    WHERE pd.menage = men.id
                    AND pd.anneeScolaire = men.anneeScolaire
                ),0)
            ) AS montant_paye

        FROM menage men

        LEFT JOIN eleve el 
            ON el.menage = men.id 
            AND el.status = 'inactif'

        LEFT JOIN classe cl 
            ON cl.id = el.classe

        LEFT JOIN scolarite sco 
            ON sco.cycle = cl.cycle
            AND sco.anneeScolaire = men.anneeScolaire
            AND sco.description = 'Frais connexe'

        WHERE men.anneeScolaire = '$annee_scolaire'
        AND men.status = 'inactif'

        GROUP BY men.id
        ORDER BY men.noms DESC";
        $rst_inactif = mysqli_query($con, $sql);
?>