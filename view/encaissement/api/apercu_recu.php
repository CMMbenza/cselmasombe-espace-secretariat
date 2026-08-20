<?php 
require_once ('../../../layouts/constants/head.php'); 
require_once('../../../webapp/database/config.php');

  if (isset($_GET['ordre'])) {

    $codeRecu = $_GET['ordre']; 

    $sql = "SELECT men.id AS codeMenage, men.noms AS menage, men.montantAPayer AS montantAPayer, paie.resteAPayer AS resteAPayer, paie.dateCreated AS dateCreated, paie.anneeScolaire AS anneeScolaire, paie.montantPayer AS montantPayer, paie.observation AS observation FROM paiement paie JOIN menage men ON paie.menage = men.id WHERE paie.id = $codeRecu";

      $result = mysqli_query($con, $sql);     

        while ($row = $result->fetch_assoc()) {

            // $id = $row['id'];
            $menage = $row['menage'];
            $montantPayer = $row['montantPayer']; 
            $codeMenage  = $row['codeMenage']; 
            $resteAPayer = $row['resteAPayer'];
            // $avenue = $row['avenue'];
            // $quartier = $row['quartier'];
            // $commune = $row['commune']; 
            $montantAPayer = $row['montantAPayer']; 
            $dateCreated = $row['dateCreated'];
            $observation = $row['observation'];
            $anneeScolaire = $row['anneeScolaire'];
            // $createdby = $row['createdby']; 
      } 

      $date = $dateCreated;

// Conversion et formatage
$formattedDate = date("d/m/Y", strtotime($date));
$derniereChiffreDeLAnnee = date('y');
?>

<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title> Order confirmation </title>
<meta name="robots" content="noindex,nofollow" />
<meta name="viewport" content="width=device-width; initial-scale=1.0;" />
<style type="text/css">
@import url(https://fonts.googleapis.com/css?family=Open+Sans:400,700);

body {
    margin: 0;
    padding: 0;
    background: #e1e1e1;
}

div,
p,
a,
li,
td {
    -webkit-text-size-adjust: none;
}

.ReadMsgBody {
    width: 100%;
    background-color: #ffffff;
}

.ExternalClass {
    width: 100%;
    background-color: #ffffff;
}

body {
    width: 100%;
    height: 100%;
    background-color: #e1e1e1;
    margin: 0;
    padding: 0;
    -webkit-font-smoothing: antialiased;
}

html {
    width: 100%;
}

p {
    padding: 0 !important;
    margin-top: 0 !important;
    margin-right: 0 !important;
    margin-bottom: 0 !important;
    margin-left: 0 !important;
}

.visibleMobile {
    display: none;
}

.hiddenMobile {
    display: block;
}

@media only screen and (max-width: 600px) {
    body {
        width: auto !important;
    }

    table[class=fullTable] {
        width: 96% !important;
        clear: both;
    }

    table[class=fullPadding] {
        width: 85% !important;
        clear: both;
    }

    table[class=col] {
        width: 45% !important;
    }

    .erase {
        display: none;
    }
}

@media only screen and (max-width: 420px) {
    table[class=fullTable] {
        width: 100% !important;
        clear: both;
    }

    table[class=fullPadding] {
        width: 85% !important;
        clear: both;
    }

    table[class=col] {
        width: 100% !important;
        clear: both;
    }

    table[class=col] td {
        text-align: left !important;
    }

    .erase {
        display: none;
        font-size: 0;
        max-height: 0;
        line-height: 0;
        padding: 0;
    }

    .visibleMobile {
        display: block !important;
    }

    .hiddenMobile {
        display: none !important;
    }
}

.btn {
    position: relative;
    margin-left: 35%;
    width: 30%;
    height: 50px;
}

@media print {

    .hidden_print,
    .hidden_print * {
        display: none !important
    }
}

.hidden_print,
.hidden_print * {
    display: none !important
}
</style>


<div class="contentToPrint" id="content">


    <!-- Header -->
    <table width="100%" border="0" cellpadding="0" cellspacing="0" align="center" class="fullTable" bgcolor="#e1e1e1">
        <tr>
            <td height="20"></td>
        </tr>
        <tr>
            <td>
                <table width="600" border="0" cellpadding="0" cellspacing="0" align="center" class="fullTable"
                    bgcolor="#ffffff" style="border-radius: 10px 10px 0 0;">
                    <tr class="hiddenMobile">
                        <td height="40"></td>
                    </tr>
                    <tr class="visibleMobile">
                        <td height="30"></td>
                    </tr>

                    <tr>
                        <td>
                            <table width="480" border="0" cellpadding="0" cellspacing="0" align="center"
                                class="fullPadding">
                                <tbody>
                                    <tr>
                                        <td>
                                            <table width="220" border="0" cellpadding="0" cellspacing="0" align="left"
                                                class="col">
                                                <tbody>
                                                    <tr>
                                                        <td align="left">
                                                            <img src="../../../assets/images/IMG-20250624-WA0013.jpg"
                                                                width="80" height="80" alt="logo" border="0"
                                                                class="img-fluid" />
                                                            <!--ECCB-->
                                                        </td>
                                                    </tr>
                                                    <tr class="hiddenMobile">
                                                        <td height="40"></td>
                                                    </tr>
                                                    <tr class="visibleMobile">
                                                        <td height="20"></td>
                                                    </tr>
                                                    <tr>
                                                        <td
                                                            style="font-size: 15px; color: #5b5b5b; font-family: 'Open Sans', sans-serif; line-height: 18px; vertical-align: top; text-align: left;">
                                                            Bonjour !
                                                            <br> Merci de rendre l'éducation accessible et d'investir
                                                            dans
                                                            l'avenir !
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                            <table width="220" border="0" cellpadding="0" cellspacing="0" align="right"
                                                class="col">
                                                <tbody>
                                                    <tr class="visibleMobile">
                                                        <td height="20"></td>
                                                    </tr>
                                                    <tr>
                                                        <td height="5"></td>
                                                    </tr>
                                                    <tr>
                                                        <td
                                                            style="font-size: 20px; color: #ff0000; letter-spacing: -1px; font-family: 'Open Sans', sans-serif; line-height: 1; vertical-align: top; text-align: right;">
                                                            N° Reçu :
                                                            <?php echo $codeRecu;?>/
                                                            <?php echo $derniereChiffreDeLAnnee;?>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                    <tr class="hiddenMobile">
                                                        <td height="50"></td>
                                                    </tr>
                                                    <tr class="visibleMobile">
                                                        <td height="20"></td>
                                                    </tr>
                                                    <tr>
                                                        <td
                                                            style="font-size: 20px; color: #5b5b5b; font-family: 'Open Sans', sans-serif; line-height: 18px; vertical-align: top; text-align: right;">
                                                            <!-- <small>ORDER</small> <?php echo $codeRecu;?><br /> -->
                                                            <small>Date :
                                                                <?php echo $formattedDate;?>
                                                            </small>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    <!-- /Header -->
    <!-- Order Details -->
    <table width="100%" border="0" cellpadding="0" cellspacing="0" align="center" class="fullTable" bgcolor="#e1e1e1">
        <tbody>
            <tr>
                <td>
                    <table width="600" border="0" cellpadding="0" cellspacing="0" align="center" class="fullTable"
                        bgcolor="#ffffff">
                        <tbody>
                            <tr>
                            <tr class="hiddenMobile">
                                <td height="60"></td>
                            </tr>
                            <tr class="visibleMobile">
                                <td height="40"></td>
                            </tr>
                            <tr>
                                <td>
                                    <table width="480" border="0" cellpadding="0" cellspacing="0" align="center"
                                        class="fullPadding">
                                        <tbody>
                                            <tr>
                                                <th style="font-size: 20px; font-family: 'Open Sans', sans-serif; color: #5b5b5b; font-weight: normal; line-height: 1; vertical-align: top; padding: 0 10px 7px 0;"
                                                    width="52%" align="left">
                                                    Code - Famille
                                                </th>
                                                <th style="font-size: 20px; font-family: 'Open Sans', sans-serif; color: #5b5b5b; font-weight: normal; line-height: 1; vertical-align: top; padding: 0 0 7px;"
                                                    align="left">
                                                    <small>Montant payé</small>
                                                </th>
                                                <th style="font-size: 20px; font-family: 'Open Sans', sans-serif; color: #5b5b5b; font-weight: normal; line-height: 1; vertical-align: top; padding: 0 0 7px;"
                                                    align="center">
                                                    Solde
                                                </th>
                                                <!-- <th style="font-size: 12px; font-family: 'Open Sans', sans-serif; color: #1e2b33; font-weight: normal; line-height: 1; vertical-align: top; padding: 0 0 7px;"
                                                align="right">
                                                Subtotal
                                            </th> -->
                                            </tr>
                                            <tr>
                                                <td height="1" style="background: #bebebe;" colspan="4"></td>
                                            </tr>
                                            <tr>
                                                <td height="10" colspan="4"></td>
                                            </tr>
                                            <tr>
                                                <td style="font-size: 20px; font-family: 'Open Sans', sans-serif; color: #ff0000;  line-height: 18px;  vertical-align: top; padding:10px 0;"
                                                    class="article">
                                                    <?php echo $codeMenage;?> - <?php echo $menage;?>
                                                    <div style="font-size:13px;color:#646a6e;margin-top:4px;">
                                                        Type de frais : <strong>Frais scolaire</strong>
                                                    </div>
                                                </td>
                                                <td
                                                    style="font-size: 20px; font-family: 'Open Sans', sans-serif; color: #646a6e;  line-height: 18px;  vertical-align: top; padding:10px 0;">
                                                    <small>
                                                        <?php echo $montantPayer;?> $
                                                    </small>
                                                </td>
                                                <td style="font-size: 20px; font-family: 'Open Sans', sans-serif; color: #646a6e;  line-height: 18px;  vertical-align: top; padding:10px 0;"
                                                    align="center">
                                                    <?php echo $resteAPayer;?> $
                                                </td>
                                            </tr>
                                            <tr>
                                                <td height="1" colspan="4" style="border-bottom:1px solid #e4e4e4"></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                            <tr>
                                <td height="20"></td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
        </tbody>
    </table>
    <!-- /Order Details -->
    <!-- Total -->
    <table width="100%" border="0" cellpadding="0" cellspacing="0" align="center" class="fullTable" bgcolor="#e1e1e1">
        <tbody>
            <tr>
                <td>
                    <table width="600" border="0" cellpadding="0" cellspacing="0" align="center" class="fullTable"
                        bgcolor="#ffffff">
                        <tbody>
                            <tr>
                                <td>

                                    <!-- Table Total -->
                                    <table width="480" border="0" cellpadding="0" cellspacing="0" align="center"
                                        class="fullPadding">
                                        <tbody>
                                            <!-- <tr>
                                                <td
                                                    style="font-size: 20px; font-family: 'Open Sans', sans-serif; color: #646a6e; line-height: 22px; vertical-align: top; text-align:right; ">
                                                    Montant payer :
                                                </td>
                                                <td style="font-size: 20px; font-family: 'Open Sans', sans-serif; color: #646a6e; line-height: 22px; vertical-align: top; text-align:right; white-space:nowrap;"
                                                    width="80">
                                                    <?php echo $montantPayer;?> $
                                                </td>
                                             </tr> -->
                                            <!-- <tr>
                                            <td
                                                style="font-size: 12px; font-family: 'Open Sans', sans-serif; color: #646a6e; line-height: 22px; vertical-align: top; text-align:right; ">
                                                Total à payer
                                            </td>
                                            <td
                                                style="font-size: 12px; font-family: 'Open Sans', sans-serif; color: #646a6e; line-height: 22px; vertical-align: top; text-align:right; ">
                                                <?php echo $montantPayer;?> $
                                            </td>
                                        </tr> -->
                                            <tr>
                                                <td
                                                    style="font-size: 17px; font-family: 'Open Sans', sans-serif; color: #000; line-height: 22px; vertical-align: top; text-align:right; ">
                                                    <strong>Montant payé</strong>
                                                </td>
                                                <td
                                                    style="font-size: 17px; font-family: 'Open Sans', sans-serif; color: #000; line-height: 22px; vertical-align: top; text-align:right; ">
                                                    <strong>
                                                        <?php echo $montantPayer;?> $
                                                    </strong>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td
                                                    style="font-size: 20px; font-family: 'Open Sans', sans-serif; color: #b0b0b0; line-height: 22px; vertical-align: top; text-align:right; ">
                                                    <!-- <small>Solde à payer</small> -->
                                                </td>
                                                <td
                                                    style="font-size: 20px; font-family: 'Open Sans', sans-serif; color: #b0b0b0; line-height: 22px; vertical-align: top; text-align:right; ">
                                                    <small><?php echo $observation;?></small>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <!-- /Table Total -->

                                </td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
        </tbody>
    </table>
    <!-- /Total -->
    <!-- /Information -->
    <table width="100%" border="0" cellpadding="0" cellspacing="0" align="center" class="fullTable" bgcolor="#e1e1e1">

        <tr>
            <td>
                <table width="600" border="0" cellpadding="0" cellspacing="0" align="center" class="fullTable"
                    bgcolor="#ffffff" style="border-radius: 0 0 10px 10px; margin-to: 12px;">
                    <tr>
                        <td>
                            <table width="480" border="0" cellpadding="0" cellspacing="0" align="center"
                                class="fullPadding">
                                <tbody>
                                    <tr>
                                        <td
                                            style="font-size: 16px; color: #5b5b5b; font-family: 'Open Sans', sans-serif; line-height: 16px; vertical-align: top; text-align: left;">
                                            Valable pour l'année scolaire : <?php echo $anneeScolaire;?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td
                                            style="font-size: 16px; color: #5b5b5b; font-family: 'Open Sans', sans-serif; line-height: 16px; vertical-align: top; text-align: left; margin-top: 12px;">
                                            N.B : L'argent perçu n'est pas remboursable.
                                        </td>
                                    </tr>
                                    <tr>
                                        <td
                                            style="font-size: 16px; color: #5b5b5b; font-family: 'Open Sans', sans-serif; line-height: 16px; vertical-align: top; text-align: left; margin-top: 12px; margin-bottom: 12px;">
                                            Vérifiez le paiement sur : <a
                                                href="https://www.cselmasombe.org/parent">https://www.cselmasombe.org/parent</a>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td height="20"></td>
        </tr>
    </table>

    <?php  }   ?>

</div>

<div class="row">
    <div class="col">
        <button class="btn btn-primary" id="btnPrinter" onclick="imprimerDiv()" name="btnPrinter">IMPRIMER</button>
        <button class="btn btn-danger" onclick="history.back()">RETOUR</button>
    </div>
</div>


<script>
function imprimerDiv() {
    var contenu = document.getElementById('content').innerHTML; // Récupérer le contenu du div
    var fenetre = window.open('', '', 'width=600, height=500'); // Ouvrir une nouvelle fenêtre pour l'impression
    fenetre.document.write('<html><head><title>Impression</title></head><body>');
    fenetre.document.write(contenu); // Insérer le contenu dans la nouvelle fenêtre
    fenetre.document.write('</body></html>');
    fenetre.document.close(); // Fermer le document pour éviter que le code ne se bloque
    fenetre.print(); // Lancer l'impression
}
</script>