<?php
require_once('../../webapp/database/config.php');

header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=nouvelles_familles_" . date('Y-m-d_H-i') . ".xls");
header("Pragma: no-cache");
header("Expires: 0");

echo "\xEF\xBB\xBF";

$sql = "
    SELECT 
        m.noms AS nom_menage,
        m.nom_du_pere,
        m.nom_de_la_mere,
        m.telephone,
        m.commune,
        m.quartier,
        m.avenue,
        m.numero,
        m.dateCreated,
        COUNT(e.id) AS total_enfants,
        
        GROUP_CONCAT(
            DISTINCT CONCAT(e.nom, ' ', e.postnom, ' ', e.prenom) 
            SEPARATOR ' | '
        ) AS liste_enfants,
        
        GROUP_CONCAT(
            DISTINCT CONCAT(c.description, ' (', cy.description, ')') 
            SEPARATOR ' | '
        ) AS liste_classes

    FROM menage m
    LEFT JOIN eleve e ON e.menage = m.id
    LEFT JOIN classe c ON c.id = e.classe
    LEFT JOIN cycle cy ON cy.id = c.cycle
    
    WHERE m.id_original IS NULL 
      AND m.STATUS = 'actif'
      
    GROUP BY m.id
    ORDER BY m.id DESC
";

$result = mysqli_query($con, $sql);

$total_menages = 0;
$total_eleves_global = 0;
?>

<table border="1">
    <thead>
        <tr style="background-color: #343a40; color: #ffffff;">
            <th>#</th>
            <th>Ménage</th>
            <th>Nom du Père</th>
            <th>Nom de la Mère</th>
            <th>Téléphone</th>
            <th>Adresse</th>
            <th>Enfants Inscrits</th>
            <th>Classe(s) & Cycle</th>
            <th>Total Enfants</th>
            <th>Date d'inscription</th>
        </tr>
    </thead>
    <tbody>
        <?php 
        if ($result && mysqli_num_rows($result) > 0): 
            $i = 1;
            while ($row = mysqli_fetch_assoc($result)): 
                $adresse = $row['avenue'] . ' n°' . $row['numero'] . ', Q/' . $row['quartier'] . ', C/' . $row['commune'];
                $total_menages++;
                $total_eleves_global += (int)$row['total_enfants'];
        ?>
        <tr>
            <td><?= $i++ ?></td>
            <td><?= htmlspecialchars($row['nom_menage']) ?></td>
            <td><?= htmlspecialchars($row['nom_du_pere'] ?: 'N/A') ?></td>
            <td><?= htmlspecialchars($row['nom_de_la_mere'] ?: 'N/A') ?></td>
            <td><?= htmlspecialchars($row['telephone']) ?></td>
            <td><?= htmlspecialchars($adresse) ?></td>
            <td><?= htmlspecialchars($row['liste_enfants'] ?: 'Aucun') ?></td>
            <td><?= htmlspecialchars($row['liste_classes'] ?: 'N/A') ?></td>
            <td><?= $row['total_enfants'] ?></td>
            <td><?= date('d/m/Y', strtotime($row['dateCreated'])) ?></td>
        </tr>
        <?php 
            endwhile; 
        endif; 
        ?>
    </tbody>
    <tfoot>
        <tr style="background-color: #e9ecef; font-weight: bold;">
            <td colspan="8">TOTAL GENERAL (Ménages: <?= $total_menages ?>)</td>
            <td><?= $total_eleves_global ?></td>
            <td></td>
        </tr>
    </tfoot>
</table>