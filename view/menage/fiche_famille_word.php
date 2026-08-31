<?php
require_once('../../webapp/database/config.php');

function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

$codeMenage = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($codeMenage <= 0) {
    die("ID ménage invalide.");
}

/* ===================== Infos Ménage ===================== */
$sqlMenage = "
  SELECT id, noms, nom_du_pere, nom_de_la_mere, profesion, telephone, 
         numero, avenue, quartier, commune, province, dateCreated, anneeScolaire, createdby
  FROM menage
  WHERE id = ? LIMIT 1
";
$stM = $con->prepare($sqlMenage);
$stM->bind_param('i', $codeMenage);
$stM->execute();
$men = $stM->get_result()->fetch_assoc();
$stM->close();

if (!$men) {
    die("Famille introuvable.");
}

/* ===================== Infos Élèves ===================== */
$sqlEleves = "
  SELECT el.matricule, el.nom, el.postnom, el.prenom, el.genre, el.lieu, el.dateDeNaissance, el.nationalite,
         el.dateCreated AS date_inscription,
         cl.description AS classe, cy.description AS cycle
  FROM eleve el
  JOIN classe cl ON el.classe = cl.id
  JOIN cycle  cy ON cl.cycle  = cy.id
  WHERE el.menage = ?
  ORDER BY el.id ASC
";
$stE = $con->prepare($sqlEleves);
$stE->bind_param('i', $codeMenage);
$stE->execute();
$rstEleve = $stE->get_result();
$eleves = [];
while ($r = $rstEleve->fetch_assoc()) $eleves[] = $r;
$stE->close();

/* ===================== Téléchargement du Logo ===================== */
$remoteLogoUrl = 'https://cselmasombe.org/home/asset/img/airtel.png';
$imageBase64 = '';
$imageMime = 'image/png';

function fetchRemoteImage($url) {
    if (function_exists('curl_init')) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $data = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($httpCode == 200 && $data !== false) {
            return $data;
        }
    }
    return @file_get_contents($url);
}

$imageData = fetchRemoteImage($remoteLogoUrl);
if ($imageData !== false && !empty($imageData)) {
    $imageBase64 = chunk_split(base64_encode($imageData));
}

// Formatage du code ménage (ex: 0012)
$codeFamilleFormatted = sprintf('%04d', $men['id']);
$anneeScolaireStr = !empty($men['anneeScolaire']) ? h($men['anneeScolaire']) : date('Y') . '-' . (date('Y') + 1);

// En-têtes HTTP pour format MHTML Word (.doc)
header("Content-Type: application/msword");
header("Content-Disposition: attachment; filename=Fiche_Famille_" . preg_replace('/[^a-zA-Z0-9]/', '_', $men['noms']) . ".doc");
header("Pragma: no-cache");
header("Expires: 0");

$boundary = "----=_NextPart_000_0000_01D12345.6789ABCD";

echo "MIME-Version: 1.0\r\n";
echo "Content-Type: multipart/related; boundary=\"{$boundary}\"\r\n\r\n";

echo "--{$boundary}\r\n";
echo "Content-Type: text/html; charset=\"utf-8\"\r\n";
echo "Content-Transfer-Encoding: 8bit\r\n\r\n";
?>
<!DOCTYPE html>
<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:w="urn:schemas-microsoft-com:office:word"
    xmlns="http://www.w3.org/TR/REC-html40">

<head>
    <meta charset="utf-8">
    <!--[if gte mso 9]>
<xml>
 <w:WordDocument>
  <w:View>Print</w:View>
  <w:Zoom>100</w:Zoom>
  <w:DoNotOptimizeForBrowser/>
 </w:WordDocument>
</xml>
<![endif]-->
    <style>
    @page {
        size: A4 portrait;
        margin: 15mm 15mm 15mm 15mm;
    }

    body {
        font-family: 'Segoe UI', Arial, sans-serif;
        font-size: 9.5pt;
        color: #1a252f;
        margin: 0;
        padding: 0;
    }

    /* Structure universelle pour un alignement parfait des deux tableaux */
    table {
        border-collapse: collapse;
        width: 100% !important;
        max-width: 100% !important;
        table-layout: fixed;
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    /* En-tête Institutionnel */
    .header-table td {
        vertical-align: middle;
        border: none !important;
    }

    .header-text {
        text-align: center;
        font-weight: bold;
        font-size: 9.5pt;
        line-height: 1.35;
        color: #1a252f;
    }

    .header-line {
        border-bottom: 2.5pt solid #0f2a4a;
        margin-top: 6px;
        margin-bottom: 16px;
    }

    /* Titre du document */
    .main-title-container {
        text-align: center;
        margin-bottom: 18px;
    }

    .main-title {
        display: inline-block;
        font-size: 14pt;
        font-weight: bold;
        color: #0f2a4a;
        letter-spacing: 1px;
        text-transform: uppercase;
        border-bottom: 2pt solid #0f2a4a;
        padding-bottom: 3px;
    }

    /* Titres de Sections / En-têtes de Tableaux */
    table.block-table {
        margin-bottom: 15px;
    }

    .section-banner {
        font-weight: bold;
        font-size: 10.5pt;
        color: #ffffff;
        background-color: #0f2a4a;
        padding: 8px 12px;
        text-align: left;
        letter-spacing: 0.5px;
        border: 1pt solid #0f2a4a !important;
    }

    /* Cellules Générales */
    table.block-table td,
    table.block-table th {
        padding: 7px 8px;
        vertical-align: middle;
        font-size: 9.5pt;
        border: 1pt solid #0f2a4a;
        word-wrap: break-word;
        overflow: hidden;
    }

    .label {
        font-weight: bold;
        background-color: #f1f5f9;
        color: #0f2a4a;
    }

    table.block-table th {
        font-weight: bold;
        background-color: #f1f5f9;
        color: #0f2a4a;
        text-align: center;
        font-size: 9pt;
        text-transform: uppercase;
    }

    .nb-text {
        font-size: 8.5pt;
        font-style: italic;
        color: #555555;
        margin-top: 6px;
        margin-bottom: 20px;
    }

    /* Bloc de Signature */
    .signature-container {
        width: 100% !important;
        margin-top: 20px;
    }

    .signature-container td {
        border: none !important;
    }

    .signature-block {
        float: right;
        text-align: center;
        width: 250px;
        font-size: 9.5pt;
    }

    /* Pied de page */
    .footer-address {
        margin-top: 60px;
        text-align: center;
        font-weight: bold;
        font-size: 8.5pt;
        color: #4a5568;
        border-top: 1pt solid #0f2a4a;
        padding-top: 6px;
        clear: both;
    }
    </style>
</head>

<body>

    <!-- EN-TÊTE AVEC LOGO RÉDUIT -->
    <table class="header-table">
        <tr>
            <td style="width: 15%; text-align: left;">
                <?php if (!empty($imageBase64)): ?>
                <img src="logo_header.png" style="width: 55px; height: auto;" alt="Logo">
                <?php endif; ?>
            </td>
            <td style="width: 85%;" class="header-text">
                MINISTÈRE DE L'ÉDUCATION NATIONALE ET DE LA NOUVELLE CITOYENNETÉ<br>
                <span style="font-size: 11pt; color: #0f2a4a;">COMPLEXE SCOLAIRE ELMA SOMBE</span><br>
                ÉCOLE LAÏQUE PRIVÉE AGRÉÉE — MEMBRE DE L'ASSONEPA<br>
                <span style="font-size: 8.5pt; font-weight: normal;">N° ARRETÉ : DEPS/CCE/001/00330/88</span><br>
                <strong>DIRECTION GÉNÉRALE ADMINISTRATIVE ET FINANCIÈRE</strong>
            </td>
        </tr>
    </table>

    <div class="header-line"></div>

    <!-- TITRE -->
    <div class="main-title-container">
        <div class="main-title">FICHE FAMILLE <?= $anneeScolaireStr ?></div>
    </div>

    <!-- I. RENSEIGNEMENTS SUR LE MÉNAGE -->
    <table class="block-table">
        <tr>
            <td colspan="4" class="section-banner">I. RENSEIGNEMENTS SUR LE MÉNAGE</td>
        </tr>
        <tr>
            <td style="width: 25%;" class="label">NOM FAMILLE (CODE) :</td>
            <td style="width: 38%;"><strong><?= h(strtoupper($men['noms'])) ?></strong> &nbsp;<span
                    style="color: #555;">(CODE <?= $codeFamilleFormatted ?>)</span></td>
            <td style="width: 17%;" class="label">TÉLÉPHONE :</td>
            <td style="width: 20%;"><?= h($men['telephone']) ?></td>
        </tr>
        <tr>
            <td class="label">NOM DU PÈRE :<br><br>NOM DE LA MÈRE :</td>
            <td><?= h($men['nom_du_pere'] ?: 'N/A') ?><br><br><?= h($men['nom_de_la_mere'] ?: 'N/A') ?></td>
            <td class="label">PROFESSION :</td>
            <td><?= h($men['profesion'] ?: 'N/A') ?></td>
        </tr>
        <tr>
            <td class="label">ADRESSE PHYSIQUE :</td>
            <td colspan="3">
                Av. <?= h($men['avenue']) ?>, N° <?= h($men['numero']) ?>,
                Q/ <?= h($men['quartier']) ?>, C/ <?= h($men['commune']) ?>
                (Province : <?= h($men['province']) ?>)
            </td>
        </tr>
    </table>

    <!-- II. ÉLÈVES INSCRITS -->
    <table class="block-table">
        <tr>
            <td colspan="6" class="section-banner">II. ÉLÈVES INSCRITS DANS L'ÉTABLISSEMENT (<?= count($eleves) ?>)</td>
        </tr>
        <thead>
            <tr>
                <th style="width: 6%;">#</th>
                <th style="width: 16%;">MATRICULE</th>
                <th style="width: 36%;">NOM, POSTNOM & PRÉNOM</th>
                <th style="width: 8%;">SEXE</th>
                <th style="width: 16%;">CLASSE & CYCLE</th>
                <th style="width: 18%;">DATE NAISSANCE</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($eleves)): ?>
            <tr>
                <td colspan="6" style="text-align: center; color: #7f8c8d; padding: 12px;">Aucun enfant inscrit pour
                    cette famille.</td>
            </tr>
            <?php else: ?>
            <?php $i = 1; foreach ($eleves as $el): ?>
            <tr style="height: 24px;">
                <td style="text-align: center;"><?= $i++ ?></td>
                <td style="text-align: center; font-family: 'Consolas', monospace; font-size: 8.5pt;">
                    <?= h($el['matricule']) ?></td>
                <td style="text-align: left; padding-left: 8px;">
                    <strong><?= h($el['nom'] . ' ' . $el['postnom'] . ' ' . $el['prenom']) ?></strong></td>
                <td style="text-align: center;"><?= h($el['genre']) ?></td>
                <td style="text-align: center;"><?= h($el['classe']) ?><br>(<?= h($el['cycle']) ?>)</td>
                <td style="text-align: center;"><?= h($el['lieu']) ?>, <?= h($el['dateDeNaissance']) ?></td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="nb-text">
        * N.B. L'inscription est définitive après validation des documents scolaires requis par la direction.
    </div>

    <!-- SIGNATURE -->
    <table class="signature-container">
        <tr>
            <td style="width: 50%;"></td>
            <td style="width: 50%; text-align: right;">
                <div class="signature-block">
                    Fait à Kinshasa, le <?= date('d/m/Y') ?><br><br><br>
                    <strong>Signature du père (tuteur légal)</strong>
                </div>
            </td>
        </tr>
    </table>

    <!-- PIED DE PAGE -->
    <div class="footer-address">
        5A, AVENUE GARAGISTE, Q. FUNA, C/LIMETE — KINSHASA / RDC
    </div>

</body>

</html>
<?php
// Encodage MHTML du logo de l'en-tête
if (!empty($imageBase64)) {
    echo "\r\n--{$boundary}\r\n";
    echo "Content-Type: {$imageMime}\r\n";
    echo "Content-Transfer-Encoding: base64\r\n";
    echo "Content-Location: logo_header.png\r\n\r\n";
    echo $imageBase64;
}

echo "\r\n--{$boundary}--\r\n";
?>