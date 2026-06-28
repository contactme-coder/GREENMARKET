<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) session_start();

$theme_actif = 'light';
$lang_active = 'fr';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $changed = false;
    if (isset($_POST['lang_select']) && in_array($_POST['lang_select'], ['fr', 'en', 'ar'])) {
        $lang_active = $_POST['lang_select'];
        setcookie('lang', $lang_active, time() + (86400 * 30), "/");
        $changed = true;
    }
    if (isset($_POST['theme_select']) && in_array($_POST['theme_select'], ['light', 'dark'])) {
        $theme_actif = $_POST['theme_select'];
        setcookie('theme', $theme_actif, time() + (86400 * 30), "/");
        $changed = true;
    }
    if ($changed) {
        header("Location: " . $_SERVER['REQUEST_URI']);
        exit;
    }
}
if (isset($_COOKIE['lang']) && in_array($_COOKIE['lang'], ['fr', 'en', 'ar'])) $lang_active = $_COOKIE['lang'];
if (isset($_COOKIE['theme']) && in_array($_COOKIE['theme'], ['light', 'dark'])) $theme_actif = $_COOKIE['theme'];

$t = [
    'fr' => [
        'home' => 'Accueil', 'cat' => 'Catalogue', 'shop' => 'Boutiques', 'cart' => 'Panier',
        'login' => 'Se connecter', 'logout' => 'Déconnexion', 'acc' => 'Mon Espace', 'dash' => 'Dashboard',
        'prod_by' => 'Nos Producteurs Locaux', 'prod_sub' => 'Achetez directement depuis les fermes éco-responsables.',
        'empty_shop' => 'Aucune boutique disponible.', 'empty_cart' => 'Votre panier est vide.',
        'summary' => 'Résumé de la commande', 'total' => 'Total', 'remove' => 'Retirer',
        'cmd_hist' => 'Mon historique de commandes', 'no_cmd' => 'Vous n\'avez pas encore passé de commande.',
        'num_cmd' => 'N° Commande', 'date' => 'Date', 'articles' => 'Articles',
        'montant' => 'Montant', 'pay' => 'Paiement', 'statut' => 'Statut',
        'my_products' => 'Mes Produits', 'my_sales' => 'Mes Ventes',
        'add_product' => 'Ajouter un nouveau produit', 'ref_lbl' => 'Référence',
        'image_lbl' => 'Image', 'product_lbl' => 'Produit', 'category_lbl' => 'Catégorie',
        'stock_lbl' => 'Quantité en Stock', 'price_lbl' => 'Prix Unitaire',
        'actions_lbl' => 'Actions', 'modify_lbl' => 'Modifier', 'delete_lbl' => 'Supprimer',
        'no_products_yet' => 'Vous n\'avez pas encore de produits en ligne.',
        'no_sales_yet' => 'Aucune vente enregistrée pour le moment.',
        'sales_lines' => 'Lignes de commandes reçues', 'revenue_lbl' => "Chiffre d'affaires total",
        'online_products_lbl' => 'Produits en ligne',
        'producer_welcome' => 'Gestion de votre catalogue de produits locaux',
        'producer_sub' => 'Ajoutez, modifiez ou supprimez vos produits en temps réel.',
        'producer_tag' => 'Producteur', 'admin_tag' => 'Admin',
        'qty_sold' => 'Quantité vendue', 'unit_price' => 'Prix unitaire', 'subtotal' => 'Sous-total',
        'admin_space' => 'Espace Administration',
        'pending_producers' => 'Producteurs en attente de validation',
        'pending_products' => 'Produits en attente de publication',
        'approve_lbl' => 'Approuver', 'reject_lbl' => 'Rejeter',
        'publish_lbl' => 'Publier', 'refuse_lbl' => 'Refuser',
        'no_pending_accounts' => 'Aucun compte en attente.', 'no_pending_products' => 'Aucun produit en attente.',
        'order_surveillance' => 'Surveillance des commandes',
        'search_ph' => 'Rechercher par nom client, email ou n° de commande...',
        'search_btn' => 'Rechercher', 'reset_btn' => 'Réinitialiser',
        'total_orders_lbl' => 'Commandes totales', 'orders_today_lbl' => "Commandes aujourd'hui",
        'revenue_today_lbl' => "Chiffre d'affaires aujourd'hui",
        'client_lbl' => 'Client', 'no_order_found' => 'Aucune commande trouvée.',
        'name_lbl' => 'Nom', 'email_lbl' => 'Email', 'price_short' => 'Prix',
        'method_lbl' => 'Mode de paiement',
        'connexion' => 'Connexion', 'inscription' => 'Créer un compte',
        'fullname' => 'Nom complet', 'submit_conx' => 'Se connecter', 'submit_insc' => "S'inscrire",
        'role_client' => 'Client', 'role_prod' => 'Producteur',
        'profile_btn' => 'Mon Profil',
        'submit_review' => 'Donner mon avis',
        'stars' => 'Étoiles', 'review_comment' => 'Commentaire',
        'see_reviews' => 'Voir les avis',
        'moderate_reviews' => 'Modération des avis',
        'pending_reviews' => 'Avis en attente',
        'points_label' => 'Points de fidélité',
        'badges_label' => 'Mes badges',
        'no_badges' => 'Aucun badge obtenu pour le moment.',
        'buy_to_unlock' => 'Réalisez plus de ventes pour débloquer des badges !'
    ],
    'en' => [
        'home' => 'Home', 'cat' => 'Catalog', 'shop' => 'Shops', 'cart' => 'Cart',
        'login' => 'Login', 'logout' => 'Logout', 'acc' => 'My Account', 'dash' => 'Dashboard',
        'prod_by' => 'Our Local Producers', 'prod_sub' => 'Buy directly from eco-responsible farms.',
        'empty_shop' => 'No shops available.', 'empty_cart' => 'Your cart is empty.',
        'summary' => 'Order Summary', 'total' => 'Total', 'remove' => 'Remove',
        'cmd_hist' => 'My Order History', 'no_cmd' => 'You have not placed any orders yet.',
        'num_cmd' => 'Order No.', 'date' => 'Date', 'articles' => 'Articles',
        'montant' => 'Amount', 'pay' => 'Payment', 'statut' => 'Status',
        'my_products' => 'My Products', 'my_sales' => 'My Sales',
        'add_product' => 'Add a new product', 'ref_lbl' => 'Reference',
        'image_lbl' => 'Image', 'product_lbl' => 'Product', 'category_lbl' => 'Category',
        'stock_lbl' => 'Stock Quantity', 'price_lbl' => 'Unit Price',
        'actions_lbl' => 'Actions', 'modify_lbl' => 'Edit', 'delete_lbl' => 'Delete',
        'no_products_yet' => 'You don\'t have any products online yet.',
        'no_sales_yet' => 'No sales recorded yet.',
        'sales_lines' => 'Order lines received', 'revenue_lbl' => 'Total revenue',
        'online_products_lbl' => 'Products online',
        'producer_welcome' => 'Manage your local product catalog',
        'producer_sub' => 'Add, edit or delete your products in real time.',
        'producer_tag' => 'Producer', 'admin_tag' => 'Admin',
        'qty_sold' => 'Quantity sold', 'unit_price' => 'Unit price', 'subtotal' => 'Subtotal',
        'admin_space' => 'Admin Area',
        'pending_producers' => 'Producers pending validation',
        'pending_products' => 'Products pending publication',
        'approve_lbl' => 'Approve', 'reject_lbl' => 'Reject',
        'publish_lbl' => 'Publish', 'refuse_lbl' => 'Refuse',
        'no_pending_accounts' => 'No pending accounts.', 'no_pending_products' => 'No pending products.',
        'order_surveillance' => 'Order monitoring',
        'search_ph' => 'Search by client name, email, or order number...',
        'search_btn' => 'Search', 'reset_btn' => 'Reset',
        'total_orders_lbl' => 'Total orders', 'orders_today_lbl' => 'Orders today',
        'revenue_today_lbl' => 'Revenue today',
        'client_lbl' => 'Client', 'no_order_found' => 'No orders found.',
        'name_lbl' => 'Name', 'email_lbl' => 'Email', 'price_short' => 'Price',
        'method_lbl' => 'Payment method',
        'connexion' => 'Login', 'inscription' => 'Create an account',
        'fullname' => 'Full name', 'submit_conx' => 'Login', 'submit_insc' => 'Register',
        'role_client' => 'Client', 'role_prod' => 'Producer',
        'profile_btn' => 'My Profile',
        'submit_review' => 'Submit Review',
        'stars' => 'Stars', 'review_comment' => 'Comment',
        'see_reviews' => 'View Reviews',
        'moderate_reviews' => 'Moderate Reviews',
        'pending_reviews' => 'Pending Reviews',
        'points_label' => 'Loyalty Points',
        'badges_label' => 'My Badges',
        'no_badges' => 'No badges earned yet.',
        'buy_to_unlock' => 'Make more sales to unlock badges!'
    ],
    'ar' => [
        'home' => 'الرئيسية', 'cat' => 'الكتالوج', 'shop' => 'المتاجر', 'cart' => 'السلة',
        'login' => 'دخول', 'logout' => 'خروج', 'acc' => 'حسابي', 'dash' => 'لوحة التحكم',
        'prod_by' => 'منتجونا المحليون', 'prod_sub' => 'اشترِ مباشرة من المزارع والتعاونيات البيئية.',
        'empty_shop' => 'لا توجد متاجر متاحة حالياً.', 'empty_cart' => 'سلتك فارغة.',
        'summary' => 'ملخص الطلب', 'total' => 'المجموع', 'remove' => 'حذف',
        'cmd_hist' => 'سجل طلباتي', 'no_cmd' => 'لم تقم بإنشاء أي طلب بعد.',
        'num_cmd' => 'رقم الطلب', 'date' => 'التاريخ', 'articles' => 'المنتجات',
        'montant' => 'المبلغ', 'pay' => 'الدفع', 'statut' => 'الحالة',
        'my_products' => 'منتجاتي', 'my_sales' => 'مبيعاتي',
        'add_product' => 'إضافة منتج جديد', 'ref_lbl' => 'المرجع',
        'image_lbl' => 'صورة', 'product_lbl' => 'المنتج', 'category_lbl' => 'الفئة',
        'stock_lbl' => 'الكمية المتوفرة', 'price_lbl' => 'سعر الوحدة',
        'actions_lbl' => 'الإجراءات', 'modify_lbl' => 'تعديل', 'delete_lbl' => 'حذف',
        'no_products_yet' => 'لا توجد منتجات لديك حتى الآن.',
        'no_sales_yet' => 'لا توجد مبيعات مسجلة حتى الآن.',
        'sales_lines' => 'عدد المبيعات المسجلة', 'revenue_lbl' => 'إجمالي رقم المعاملات',
        'online_products_lbl' => 'المنتجات المعروضة',
        'producer_welcome' => 'إدارة كتالوج منتجاتك المحلية',
        'producer_sub' => 'أضف أو عدّل أو حذف منتجاتك في الوقت الحقيقي.',
        'producer_tag' => 'منتج', 'admin_tag' => 'مدير',
        'qty_sold' => 'الكمية المباعة', 'unit_price' => 'سعر الوحدة', 'subtotal' => 'المجموع الفرعي',
        'admin_space' => 'فضاء الإدارة',
        'pending_producers' => 'منتجون في انتظار التفعيل',
        'pending_products' => 'منتجات في انتظار النشر',
        'approve_lbl' => 'قبول', 'reject_lbl' => 'رفض',
        'publish_lbl' => 'نشر', 'refuse_lbl' => 'رفض',
        'no_pending_accounts' => 'لا توجد حسابات في الانتظار.', 'no_pending_products' => 'لا توجد منتجات في الانتظار.',
        'order_surveillance' => 'مراقبة الطلبات',
        'search_ph' => 'البحث باسم الزبون، البريد الإلكتروني أو رقم الطلب...',
        'search_btn' => 'بحث', 'reset_btn' => 'إعادة تعيين',
        'total_orders_lbl' => 'مجموع الطلبات', 'orders_today_lbl' => 'طلبات اليوم',
        'revenue_today_lbl' => 'رقم المعاملات اليوم',
        'client_lbl' => 'الزبون', 'no_order_found' => 'لم يتم العثور على أي طلب.',
        'name_lbl' => 'الاسم', 'email_lbl' => 'البريد الإلكتروني', 'price_short' => 'السعر',
        'method_lbl' => 'طريقة الدفع',
        'connexion' => 'تسجيل الدخول', 'inscription' => 'إنشاء حساب',
        'fullname' => 'الاسم الكامل', 'submit_conx' => 'تسجيل الدخول', 'submit_insc' => 'إنشاء الحساب',
        'role_client' => 'زبون', 'role_prod' => 'منتج',
        'profile_btn' => 'ملفي الشخصي',
        'submit_review' => 'أضف تقييمك',
        'stars' => 'نجوم', 'review_comment' => 'تعليق',
        'see_reviews' => 'عرض التقييمات',
        'moderate_reviews' => 'إدارة التقييمات',
        'pending_reviews' => 'تقييمات في الانتظار',
        'points_label' => 'نقاط الولاء',
        'badges_label' => 'شاراتي',
        'no_badges' => 'لا توجد شارات بعد.',
        'buy_to_unlock' => 'قم بالمزيد من المبيعات لفتح الشارات!'
    ]
];

function tr($key) {
    global $t, $lang_active;
    return $t[$lang_active][$key] ?? $t['fr'][$key] ?? $key;
}

function afficher_selecteurs() {
    global $lang_active, $theme_actif;
    ?>
    <form method="POST" action="" style="display:inline-flex; gap:8px; margin:0; padding:0; align-items:center;">
      <select name="lang_select" onchange="this.form.submit()" style="padding:5px 8px; border-radius:6px; border:1px solid #d4c5ad; background:inherit; color:inherit; font-size:13px; cursor:pointer; outline:none;">
        <option value="fr" <?= $lang_active === 'fr' ? 'selected' : '' ?>>Français</option>
        <option value="en" <?= $lang_active === 'en' ? 'selected' : '' ?>>English</option>
        <option value="ar" <?= $lang_active === 'ar' ? 'selected' : '' ?>>العربية</option>
      </select>
      <select name="theme_select" onchange="this.form.submit()" style="padding:5px 8px; border-radius:6px; border:1px solid #d4c5ad; background:inherit; color:inherit; font-size:13px; cursor:pointer; outline:none;">
        <option value="light" <?= $theme_actif === 'light' ? 'selected' : '' ?>>☀️</option>
        <option value="dark"  <?= $theme_actif === 'dark'  ? 'selected' : '' ?>>🌙</option>
      </select>
    </form>
    <?php
}
?>