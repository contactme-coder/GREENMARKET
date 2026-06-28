<?php
include("preferences.php");
include("prodconnex.php");

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['dest_email'], $_POST['message'], $_POST['ref'])) {
    $dest_email = trim($_POST['dest_email']);
    $message = trim($_POST['message']);
    $ref = trim($_POST['ref']);

    // Validation de l'email destinataire
    if (!filter_var($dest_email, FILTER_VALIDATE_EMAIL)) {
        $response['message'] = "L'adresse email du destinataire est invalide.";
        echo json_encode($response);
        exit;
    }

    if (empty($message)) {
        $response['message'] = "Veuillez écrire un message personnel.";
        echo json_encode($response);
        exit;
    }

    // Récupération des infos du produit
    $req = $c->prepare("SELECT libelle, image FROM produit WHERE reference = ? AND statut = 'valide'");
    $req->execute([$ref]);
    $prod = $req->fetch(PDO::FETCH_ASSOC);

    if (!$prod) {
        $response['message'] = "Produit introuvable.";
        echo json_encode($response);
        exit;
    }

    // Génération du lien absolu
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
    $host = $_SERVER['HTTP_HOST'];
    $product_link = "$protocol://$host/greendeliver/produit.php?ref=" . urlencode($ref);

    // Construction de l'email
    $subject = "Un produit GreenMarket vous est recommandé !";
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: GreenMarket <no-reply@greenmarket.ma>" . "\r\n";

    $email_body = "<html><body style='font-family: Arial, sans-serif;'>";
    $email_body .= "<h2 style='color: #5c6b3a;'>🌿 GreenMarket</h2>";
    $email_body .= "<p>Un ami vous recommande ce produit du terroir marocain :</p>";
    $email_body .= "<h3>" . htmlspecialchars($prod['libelle']) . "</h3>";
    $email_body .= "<p><a href='$product_link' style='background:#5c6b3a; color:#fff; padding:10px 20px; text-decoration:none; border-radius:5px;'>Voir le produit</a></p>";
    $email_body .= "<p><strong>Message :</strong><br>" . nl2br(htmlspecialchars($message)) . "</p>";
    $email_body .= "<hr><p style='color: #888; font-size: 12px;'>GreenMarket - La nature au cœur de votre table.</p>";
    $email_body .= "</body></html>";

    // Envoi de l'email (PHP mail)
    if (mail($dest_email, $subject, $email_body, $headers)) {
        $response['success'] = true;
        $response['message'] = "Votre recommandation a été envoyée à " . htmlspecialchars($dest_email) . " !";
    } else {
        $response['message'] = "Échec de l'envoi. Vérifiez la configuration de votre serveur mail.";
    }
} else {
    $response['message'] = "Requête invalide.";
}

echo json_encode($response);
exit;
?>