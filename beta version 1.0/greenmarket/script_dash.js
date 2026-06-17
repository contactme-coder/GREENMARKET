// C'est ici qu'on gère les confirmations de suppression et autres effets visuels du tableau de bord
document.addEventListener("DOMContentLoaded", () => {
    console.log("JavaScript du Dashboard Producteur chargé !");

    // Attacher un écouteur sur tous les boutons de suppression pour demander confirmation
    const deleteButtons = document.querySelectorAll(".btn-delete");
    deleteButtons.forEach(button => {
        button.addEventListener("click", (e) => {
            const approbation = confirm("Êtes-vous certain de vouloir supprimer définitivement ce produit de votre catalogue ?");
            if (!approbation) {
                e.preventDefault(); // Annule le clic et le lien si l'utilisateur clique sur Annuler
            }
        });
    });
});