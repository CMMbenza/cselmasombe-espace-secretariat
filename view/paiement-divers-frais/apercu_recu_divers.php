<?php 
require_once ('../../layouts/constants/head.php');
// require_once ('../../layouts/navbar/navbar.php');
require_once('../../webapp/database/config.php');

/* ====== Encodage MySQL conseillé ====== */
if (function_exists('mysqli_set_charset')) {
  @mysqli_set_charset($con, 'utf8mb4');
}

/* ====== Helpers ====== */
if (!function_exists('e')) {
  function e($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
}
function money($n){ return number_format((float)$n, 2, ',', ' '); } // 1 234,56

/* =========================
   Reçu DIVERS : ?ordre=ID
   ========================= */
if (!isset($_GET['ordre']) || !ctype_digit($_GET['ordre'])) {
  // id invalide -> retour
  header('Location: ../../view/menage/index.php');
  exit;
}

$codeRecu = (int)$_GET['ordre'];

/* ====== Requête préparée sur paiement_divers ====== */
$sql = "SELECT 
          pd.id AS codeRecu,
          men.id AS codeMenage,
          men.noms AS menage,
          pd.type_frais,
          pd.montantAPayer,
          pd.montantPayer,
          pd.resteAPayer,
          pd.observation,
          pd.anneeScolaire,
          pd.dateCreated
        FROM paiement_divers pd
        JOIN menage men ON pd.menage = men.id
        WHERE pd.id = ?";

$stmt = mysqli_prepare($con, $sql);
mysqli_stmt_bind_param($stmt, 'i', $codeRecu);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (!$result || mysqli_num_rows($result) === 0) {
  // Rien trouvé
  header('Location: ../../view/menage/index.php');
  exit;
}

$row = mysqli_fetch_assoc($result);

/* ====== Variables ====== */
$codeMenage     = $row['codeMenage'];
$menage         = $row['menage'];
$typeFrais      = $row['type_frais'];
$montantAPayer  = $row['montantAPayer'];
$montantPayer   = $row['montantPayer'];
$resteAPayer    = $row['resteAPayer'];
$observation    = $row['observation'];
$anneeScolaire  = $row['anneeScolaire'];
$dateCreated    = $row['dateCreated'];

/* ====== Date formatée ====== */
$formattedDate = date("d/m/Y", strtotime($dateCreated));
?>

<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Reçu Paiement Divers</title>
<meta name="robots" content="noindex,nofollow" />
<meta name="viewport" content="width=device-width; initial-scale=1.0;" />
<style type="text/css">
@import url(https://fonts.googleapis.com/css?family=Open+Sans:400,700);

body {
    margin: 0;
    padding: 0;
    background: #e1e1e1;
    -webkit-font-smoothing: antialiased;
}

html {
    width: 100%
}

p {
    margin: 0;
    padding: 0
}

.visibleMobile {
    display: none
}

.hiddenMobile {
    display: block
}

@media only screen and (max-width:600px) {
    table[class=fullTable] {
        width: 96% !important;
        clear: both
    }

    table[class=fullPadding] {
        width: 85% !important;
        clear: both
    }

    table[class=col] {
        width: 45% !important
    }

    .erase {
        display: none
    }
}

@media only screen and (max-width:420px) {
    table[class=fullTable] {
        width: 100% !important;
        clear: both
    }

    table[class=fullPadding] {
        width: 85% !important;
        clear: both
    }

    table[class=col] {
        width: 100% !important;
        clear: both
    }

    table[class=col] td {
        text-align: left !important
    }

    .erase {
        display: none;
        font-size: 0;
        max-height: 0;
        line-height: 0;
        padding: 0
    }

    .visibleMobile {
        display: block !important
    }

    .hiddenMobile {
        display: none !important
    }
}

.btn {
    position: relative;
    margin-left: 35%;
    width: 30%;
    height: 50px
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
                    bgcolor="#ffffff" style="border-radius:10px 10px 0 0;">
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
                                                            <img src="../../assets/images/IMG-20250624-WA0013.jpg"
                                                                width="80" height="80" alt="logo" border="0"
                                                                class="img-fluid" />
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
                                                            style="font-size:15px;color:#5b5b5b;font-family:'Open Sans',sans-serif;line-height:18px;vertical-align:top;text-align:left;">
                                                            Bonjour !<br>
                                                            Merci de rendre l'éducation accessible et d'investir dans
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
                                                            style="font-size:20px;color:#ff0000;letter-spacing:-1px;font-family:'Open Sans',sans-serif;line-height:1;vertical-align:top;text-align:right;">
                                                            Reçu FC/N° <?php echo e($codeRecu); ?>/25
                                                        </td>
                                                    </tr>
                                                    <tr class="hiddenMobile">
                                                        <td height="50"></td>
                                                    </tr>
                                                    <tr class="visibleMobile">
                                                        <td height="20"></td>
                                                    </tr>
                                                    <tr>
                                                        <td
                                                            style="font-size:20px;color:#5b5b5b;font-family:'Open Sans',sans-serif;line-height:18px;vertical-align:top;text-align:right;">
                                                            <small>Date : <?php echo e($formattedDate); ?></small>
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

    <!-- Détails -->
    <table width="100%" border="0" cellpadding="0" cellspacing="0" align="center" class="fullTable" bgcolor="#e1e1e1">
        <tbody>
            <tr>
                <td>
                    <table width="600" border="0" cellpadding="0" cellspacing="0" align="center" class="fullTable"
                        bgcolor="#ffffff">
                        <tbody>
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
                                                <th style="font-size:20px;font-family:'Open Sans',sans-serif;color:#5b5b5b;font-weight:normal;line-height:1;vertical-align:top;padding:0 10px 7px 0;"
                                                    width="52%" align="left">
                                                    Code - Famille
                                                </th>
                                                <th style="font-size:20px;font-family:'Open Sans',sans-serif;color:#5b5b5b;font-weight:normal;line-height:1;vertical-align:top;padding:0 0 7px;"
                                                    align="left">
                                                    <small>Montant payé</small>
                                                </th>
                                                <th style="font-size:20px;font-family:'Open Sans',sans-serif;color:#5b5b5b;font-weight:normal;line-height:1;vertical-align:top;padding:0 0 7px;"
                                                    align="center">
                                                    Solde
                                                </th>
                                            </tr>

                                            <tr>
                                                <td height="1" style="background:#bebebe;" colspan="4"></td>
                                            </tr>
                                            <tr>
                                                <td height="10" colspan="4"></td>
                                            </tr>

                                            <tr>
                                                <!-- <td style="font-size:20px;font-family:'Open Sans',sans-serif;color:#ff0000;line-height:18px;vertical-align:top;padding:10px 0;"
                                                    class="article">
                                                    <?php echo e($codeMenage); ?> - <?php echo e($menage); ?>
                                                    <div style="font-size:13px;color:#646a6e;margin-top:4px;">
                                                        Type de frais : <strong><?php echo e($typeFrais); ?></strong>
                                                    </div>
                                                </td> -->
                                                <td
                                                    style="font-size:20px;font-family:'Open Sans',sans-serif;color:#646a6e;line-height:18px;vertical-align:top;padding:10px 0;">
                                                    <small><?php echo money($montantPayer); ?> $</small>
                                                </td>
                                                <td style="font-size:20px;font-family:'Open Sans',sans-serif;color:#646a6e;line-height:18px;vertical-align:top;padding:10px 0;"
                                                    align="center">
                                                    <?php echo money($resteAPayer); ?> $
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
    <!-- /Détails -->

    <!-- Total / Observations -->
    <table width="100%" border="0" cellpadding="0" cellspacing="0" align="center" class="fullTable" bgcolor="#e1e1e1">
        <tbody>
            <tr>
                <td>
                    <table width="600" border="0" cellpadding="0" cellspacing="0" align="center" class="fullTable"
                        bgcolor="#ffffff">
                        <tbody>
                            <tr>
                                <td>
                                    <table width="480" border="0" cellpadding="0" cellspacing="0" align="center"
                                        class="fullPadding">
                                        <tbody>
                                            <tr>
                                                <td
                                                    style="font-size:17px;font-family:'Open Sans',sans-serif;color:#000;line-height:22px;vertical-align:top;text-align:right;">
                                                    <strong>Montant payé</strong>
                                                </td>
                                                <td
                                                    style="font-size:17px;font-family:'Open Sans',sans-serif;color:#000;line-height:22px;vertical-align:top;text-align:right;">
                                                    <strong><?php echo money($montantPayer); ?> $</strong>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td
                                                    style="font-size:20px;font-family:'Open Sans',sans-serif;color:#b0b0b0;line-height:22px;vertical-align:top;text-align:right;">
                                                </td>
                                                <td
                                                    style="font-size:14px;font-family:'Open Sans',sans-serif;color:#666;line-height:20px;vertical-align:top;text-align:right;">
                                                    <?php if(!empty($observation)): ?>
                                                    <small><?php echo e($observation); ?></small>
                                                    <?php endif; ?>
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
        </tbody>
    </table>
    <!-- /Total -->

    <!-- Pied -->
    <table width="100%" border="0" cellpadding="0" cellspacing="0" align="center" class="fullTable" bgcolor="#e1e1e1">
        <tr>
            <td>
                <table width="600" border="0" cellpadding="0" cellspacing="0" align="center" class="fullTable"
                    bgcolor="#ffffff" style="border-radius:0 0 10px 10px;margin-to:12px;">
                    <tr>
                        <td>
                            <table width="480" border="0" cellpadding="0" cellspacing="0" align="center"
                                class="fullPadding">
                                <tbody>
                                    <tr>
                                        <td
                                            style="font-size:16px;color:#5b5b5b;font-family:'Open Sans',sans-serif;line-height:16px;vertical-align:top;text-align:left;">
                                            Valable pour l'année scolaire <br><?php echo e($anneeScolaire); ?>.
                                        </td>
                                    </tr>
                                    <tr>
                                        <td
                                            style="font-size:16px;color:#5b5b5b;font-family:'Open Sans',sans-serif;line-height:16px;vertical-align:top;text-align:left;margin-top:12px;">
                                            N.B : L'argent perçu n'est pas remboursable.
                                        </td>
                                    </tr>
                                    <tr>
                                        <td
                                            style="font-size:16px;color:#5b5b5b;font-family:'Open Sans',sans-serif;line-height:16px;vertical-align:top;text-align:left;margin-top:12px;margin-bottom:12px;">
                                            Vérifiez le paiement sur :
                                            <a
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
</div>

<div class="row">
    <div class="col">
        <button class="btn btn-primary" id="btnPrinter" onclick="imprimerDiv()" name="btnPrinter">IMPRIMER</button>
        <button class="btn btn-danger" onclick="history.back()">RETOUR</button>
    </div>
</div>

<script>
function imprimerDiv() {
    var contenu = document.getElementById('content').innerHTML;
    var fenetre = window.open('', '', 'width=600,height=500');
    fenetre.document.write('<html><head><title>Impression</title></head><body>');
    fenetre.document.write(contenu);
    fenetre.document.write('</body></html>');
    fenetre.document.close();
    fenetre.print();
}
</script>