// Funciones JavaScript para interactividad
document.addEventListener('DOMContentLoaded', function() {
    // Manejar formularios de agregar al carrito
    const addToCartForms = document.querySelectorAll('.add-to-cart-form');
    
    addToCartForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const quantityInput = this.querySelector('input[name="quantity"]');
            const quantity = parseInt(quantityInput.value);
            
            if (quantity < 1) {
                e.preventDefault();
                alert('La cantidad debe ser al menos 1');
                quantityInput.value = 1;
            }
        });
    });
    
    // Actualizar contador del carrito en tiempo real
    function updateCartCount() {
        // Esta función podría hacer una petición AJAX para obtener el conteo actual
        // Por ahora, usamos el valor de la sesión PHP
    }
    
    // Manejar búsqueda en tiempo real
    const searchInput = document.querySelector('input[name="search"]');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            // Podría implementarse búsqueda en tiempo real con AJAX
        });
    }
    
    // Smooth scroll para enlaces internos
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
});

// Función para mostrar mensajes toast (podría implementarse)
function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.textContent = message;
    toast.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 1rem 1.5rem;
        background: ${type === 'success' ? '#2ecc71' : '#e74c3c'};
        color: white;
        border-radius: 4px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.2);
        z-index: 1000;
        animation: slideIn 0.3s ease;
    `;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.remove();
    }, 3000);
}