// C'est ici qu'on gère les animations et les interactions côté client de la page authentification
document.addEventListener("DOMContentLoaded", () => {
    console.log("JavaScript d'authentification chargé avec succès !");
    
    // Tu peux ajouter ici tes effets d'origine pour basculer en douceur entre les formulaires si nécessaire.
    const navbar = document.getElementById('navbar');
    if (navbar) {
        window.addEventListener('scroll', () => {
            if (window.scrollY > 20) navbar.classList.add('scrolled');
            else navbar.classList.remove('scrolled');
        });
    }
});