// Variable pour stocker les informations actuelles du produit
let currentProduct = {};

// Fonction pour ouvrir la fenêtre modale avec les détails du produit
function openCartModal(id, name, price, stock, image) {
    currentProduct = { id, name, price, stock };

    // Mise à jour des éléments de la modale
    document.getElementById("product-name").innerText = name;
    document.getElementById("product-price").innerText = price.toFixed(2);
    document.getElementById("product-stock").innerText = stock;
    document.getElementById("total-price").innerText = "0";
    document.getElementById("quantity").value = 0;
    document.getElementById("product-image").src = image;

    // Affiche la modale
    document.getElementById("cart-modal").style.display = "flex";
}

// Fonction pour fermer la fenêtre modale
function closeCartModal() {
    document.getElementById("cart-modal").style.display = "none";
}

// Fonction pour augmenter la quantité du produit
function increaseQuantity() {
    const quantityInput = document.getElementById("quantity");
    const stock = currentProduct.stock;
    let quantity = parseInt(quantityInput.value);

    if (quantity < stock) {
        quantity++;
        quantityInput.value = quantity;
        updateTotalPrice(quantity);
    }
}

// Fonction pour diminuer la quantité du produit
function decreaseQuantity() {
    const quantityInput = document.getElementById("quantity");
    let quantity = parseInt(quantityInput.value);

    if (quantity > 0) {
        quantity--;
        quantityInput.value = quantity;
        updateTotalPrice(quantity);
    }
}

// Fonction pour mettre à jour le prix total en fonction de la quantité
function updateTotalPrice(quantity) {
    const total = quantity * currentProduct.price;
    document.getElementById("total-price").innerText = total.toFixed(2);
}

// Fonction pour confirmer l'ajout au panier
function confirmAddToCart() {
    const quantity = parseInt(document.getElementById("quantity").value);
    if (quantity > 0) {
        alert(`Ajouté ${quantity} x ${currentProduct.name} au panier.`);
        closeCartModal();

        // Logique pour ajouter au panier (par ex., appel AJAX pour mettre à jour la base de données)
        // Exemple :
        // fetch('/add-to-cart', {
        //     method: 'POST',
        //     headers: { 'Content-Type': 'application/json' },
        //     body: JSON.stringify({ productId: currentProduct.id, quantity })
        // }).then(response => response.json())
        // .then(data => console.log(data));
    } else {
        alert("Veuillez sélectionner une quantité valide.");
    }
}
