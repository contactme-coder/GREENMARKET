<?php
include("preferences.php");
if(!isset($_SESSION['idu'])) {
    header("Location: authentification.php");
    exit;
}

if(!isset($_GET['id'])) {
    header("Location: dashboard.php");
    exit;
}
$idcom = intval($_GET['id']);
include("prodconnex.php");

$req = $c->prepare("SELECT cmd.*, c.nom as client_nom, c.email as client_email FROM commande cmd JOIN compte c ON cmd.id_client = c.id WHERE cmd.idcom = ? AND cmd.id_client = ?");
$req->execute([$idcom, $_SESSION['idu']]);
$cmd = $req->fetch(PDO::FETCH_ASSOC);
if(!$cmd) {
    header("Location: dashboard.php");
    exit;
}

$req_lignes = $c->prepare("SELECT cp.*, p.libelle FROM commande_produit cp JOIN produit p ON cp.reference_produit = p.reference WHERE cp.idcom = ?");
$req_lignes->execute([$idcom]);
$lignes = $req_lignes->fetchAll(PDO::FETCH_ASSOC);

// Vérifier la présence de FPDF
$fpdf_paths = ['libs/fpdf.php', 'fpdf.php'];
$fpdf_found = false;
foreach($fpdf_paths as $path) {
    if(file_exists($path)) {
        require_once($path);
        $fpdf_found = true;
        break;
    }
}

if(!$fpdf_found) {
    die("
        <html><head><title>Erreur FPDF</title></head>
        <body style='font-family:sans-serif; text-align:center; padding:50px;'>
            <h2 style='color:#c95a5a;'>Bibliothèque FPDF manquante</h2>
            <p>Pour générer les factures, vous devez télécharger le fichier <code>fpdf.php</code>.</p>
            <p><a href='https://raw.githubusercontent.com/Setasign/FPDF/master/fpdf.php' target='_blank' style='background:#5c6b3a; color:white; padding:10px 20px; text-decoration:none; border-radius:5px;'>Télécharger fpdf.php</a></p>
            <p>Placez le fichier dans le dossier <code>libs/</code> ou à la racine du projet.</p>
            <p><a href='dashboard.php' style='color:#5c6b3a;'>Retour au tableau de bord</a></p>
        </body></html>
    ");
}

// Génération du PDF
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial','B',16);
$pdf->Cell(0,10,utf8_decode('FACTURE'),0,1,'C');
$pdf->Ln(5);
$pdf->SetFont('Arial','',12);
$pdf->Cell(0,6,utf8_decode('GreenMarket - La nature au cœur de votre table'),0,1,'C');
$pdf->Ln(10);

$pdf->SetFont('Arial','B',12);
$pdf->Cell(60,8,utf8_decode('Numéro de commande :'),0,0);
$pdf->SetFont('Arial','',12);
$pdf->Cell(60,8,'#'.$cmd['idcom'],0,1);

$pdf->SetFont('Arial','B',12);
$pdf->Cell(60,8,utf8_decode('Date :'),0,0);
$pdf->SetFont('Arial','',12);
$pdf->Cell(60,8,date('d/m/Y H:i', strtotime($cmd['date_commande'])),0,1);

$pdf->SetFont('Arial','B',12);
$pdf->Cell(60,8,utf8_decode('Client :'),0,0);
$pdf->SetFont('Arial','',12);
$pdf->Cell(60,8,utf8_decode($cmd['client_nom']),0,1);

$pdf->SetFont('Arial','B',12);
$pdf->Cell(60,8,utf8_decode('Email :'),0,0);
$pdf->SetFont('Arial','',12);
$pdf->Cell(60,8,$cmd['client_email'],0,1);

$pdf->Ln(10);
$pdf->SetFont('Arial','B',12);
$pdf->Cell(70,8,utf8_decode('Produit'),1,0,'L');
$pdf->Cell(30,8,utf8_decode('Quantité'),1,0,'C');
$pdf->Cell(40,8,utf8_decode('Prix unitaire'),1,0,'R');
$pdf->Cell(40,8,utf8_decode('Total'),1,1,'R');

$pdf->SetFont('Arial','',10);
foreach($lignes as $ligne) {
    $pdf->Cell(70,8,utf8_decode($ligne['libelle']),1,0,'L');
    $pdf->Cell(30,8,$ligne['quantite'],1,0,'C');
    $pdf->Cell(40,8,number_format($ligne['prix_unitaire'],2).' DH',1,0,'R');
    $pdf->Cell(40,8,number_format($ligne['quantite']*$ligne['prix_unitaire'],2).' DH',1,1,'R');
}

$pdf->SetFont('Arial','B',12);
$pdf->Cell(140,8,utf8_decode('Total général :'),1,0,'R');
$pdf->Cell(40,8,number_format($cmd['montant_total'],2).' DH',1,1,'R');

$pdf->Ln(10);
$pdf->SetFont('Arial','',10);
$pdf->Cell(0,6,utf8_decode('Merci pour votre confiance !'),0,1,'C');

$pdf->Output('facture_'.$idcom.'.pdf', 'D');
exit;
?>