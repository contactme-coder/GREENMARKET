document.addEventListener("DOMContentLoaded", () => {
    console.log("Système multi-passerelle GreenMarket initialisé.");

    const cards = document.querySelectorAll(".method-card");
    const panels = document.querySelectorAll(".payment-details");

    // Fonction pour afficher le bon panneau de formulaire en fonction du bouton radio coché
    function switchPaymentPanel(targetId, activeCard) {
        // Retirer la classe 'active' de toutes les cartes visuelles
        cards.forEach(c => c.classList.remove("active"));
        // Cacher tous les panneaux de détails
        panels.forEach(p => p.classList.remove("active"));

        // Activer la carte sélectionnée visuellement
        if(activeCard) {
            activeCard.classList.add("active");
            const radio = activeCard.querySelector("input[type='radio']");
            if(radio) radio.checked = true;
        }

        // Afficher le panneau correspondant
        const targetPanel = document.getElementById(targetId);
        if(targetPanel) {
            targetPanel.classList.add("active");
        }
    }

    // Écouteur sur le clic des cartes visuelles
    cards.forEach(card => {
        card.addEventListener("click", () => {
            const targetId = card.getAttribute("data-target");
            switchPaymentPanel(targetId, card);
        });
    });

    // Code d'activation initial au chargement de la page (si PHP renvoie des erreurs pour conserver le choix)
    const checkedRadio = document.querySelector("input[name='methode_paiement']:checked");
    if(checkedRadio) {
        const associatedCard = checkedRadio.closest(".method-card");
        const targetId = associatedCard.getAttribute("data-target");
        switchPaymentPanel(targetId, associatedCard);
    } else {
        // Par défaut, on active la carte bancaire
        const defaultCard = cards[0];
        if(defaultCard) {
            const targetId = defaultCard.getAttribute("data-target");
            switchPaymentPanel(targetId, defaultCard);
        }
    }

    // Auto-formattage basique pour le numéro de carte (ajoute des espaces tous les 4 chiffres)
    const cardInput = document.querySelector("input[name='card_number']");
    if(cardInput) {
        cardInput.addEventListener("input", (e) => {
            let v = e.target.value.replace(/\s+/g, '').replace(/[^0-9]/gi, '');
            let matches = v.match(/\d{4,16}/g);
            let match = matches && matches[0] || '';
            let parts = [];

            for (let i=0, len=match.length; i<len; i+=4) {
                parts.push(match.substring(i, i+4));
            }

            if (parts.length > 0) {
                e.target.value = parts.join(' ');
            } else {
                e.target.value = v;
            }
        });
    }
});