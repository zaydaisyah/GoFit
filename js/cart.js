/* Cart Logic for GoFit */

const Cart = {
    // Key for localStorage
    STORAGE_KEY: 'gofit_cart',

    // Initialize cart from storage or empty array
    getCart: function () {
        const cart = localStorage.getItem(this.STORAGE_KEY);
        return cart ? JSON.parse(cart) : [];
    },

    // Save cart to storage
    saveCart: function (cart) {
        localStorage.setItem(this.STORAGE_KEY, JSON.stringify(cart));
        this.updateCartCount();
    },

    // Add item to cart
    addItem: function (product) {
        let cart = this.getCart();
        // Generate unique ID based on product ID + Size
        const uniqueId = product.size ? `${product.id}-${product.size}` : product.id;
        product.uniqueId = uniqueId;

        const existingItemIndex = cart.findIndex(item => item.uniqueId === uniqueId);

        if (existingItemIndex > -1) {
            // Item exists, update quantity
            cart[existingItemIndex].quantity += 1;
        } else {
            // New item
            product.quantity = 1;
            cart.push(product);
        }

        this.saveCart(cart);
        alert(`${product.name} ${product.size ? '(' + product.size + ') ' : ''}added to cart!`);
    },

    // Remove item from cart
    removeItem: function (uniqueId) {
        let cart = this.getCart();
        cart = cart.filter(item => item.uniqueId !== uniqueId);
        this.saveCart(cart);
        // If we are on the cart page, re-render
        if (window.location.pathname.includes('cart.html')) {
            this.renderCartPage();
        }
    },

    // Update item quantity
    updateQuantity: function (uniqueId, change) {
        let cart = this.getCart();
        const itemIndex = cart.findIndex(item => item.uniqueId === uniqueId);

        if (itemIndex > -1) {
            cart[itemIndex].quantity += change;
            if (cart[itemIndex].quantity <= 0) {
                this.removeItem(uniqueId);
                return;
            }
            this.saveCart(cart);
            // If we are on the cart page, re-render
            if (window.location.pathname.includes('cart.html')) {
                this.renderCartPage();
            }
        }
    },

    // Clear cart
    clearCart: function () {
        localStorage.removeItem(this.STORAGE_KEY);
        this.updateCartCount();
    },

    // Get total items count
    getItemCount: function () {
        const cart = this.getCart();
        return cart.reduce((total, item) => total + item.quantity, 0);
    },

    // Get total price
    getTotalPrice: function () {
        const cart = this.getCart();
        return cart.reduce((total, item) => total + (item.price * item.quantity), 0);
    },

    // Update header cart count badge
    updateCartCount: function () {
        const count = this.getItemCount();
        const badge = document.getElementById('cart-count');
        if (badge) {
            badge.innerText = count;
            badge.style.display = count > 0 ? 'inline-block' : 'none';
        }
    },

    // Render logic for cart.html
    renderCartPage: function () {
        const cartContainer = document.getElementById('cart-items-container');
        const cartTotalElement = document.getElementById('cart-total');
        const cartSubtotalElement = document.getElementById('cart-subtotal');

        if (!cartContainer) return;

        const cart = this.getCart();
        cartContainer.innerHTML = '';

        if (cart.length === 0) {
            cartContainer.innerHTML = '<tr><td colspan="6" class="text-center">Your cart is empty.</td></tr>';
            if (cartTotalElement) cartTotalElement.innerText = 'RM 0';
            if (cartSubtotalElement) cartSubtotalElement.innerText = 'RM 0';
            return;
        }

        cart.forEach(item => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td class="cart-pic first-row"><img src="${item.image}" alt="${item.name}" style="width: 80px; border-radius: 5px;"></td>
                <td class="cart-title first-row">
                    <h5>${item.name}</h5>
                </td>
                <td class="p-price first-row">${item.size ? item.size : '-'}</td>
                <td class="p-price first-row">RM ${item.price}</td>
                <td class="qua-col first-row">
                    <div class="quantity">
                        <div class="pro-qty">
                            <span class="dec qtybtn" onclick="Cart.updateQuantity('${item.uniqueId}', -1)">-</span>
                            <input type="text" value="${item.quantity}" readonly>
                            <span class="inc qtybtn" onclick="Cart.updateQuantity('${item.uniqueId}', 1)">+</span>
                        </div>
                    </div>
                </td>
                <td class="total-price first-row">RM ${item.price * item.quantity}</td>
                <td class="close-td first-row"><i class="ti-close" onclick="Cart.removeItem('${item.uniqueId}')" style="cursor: pointer;">x</i></td>
            `;
            cartContainer.appendChild(tr);
        });

        const subtotal = this.getTotalPrice();
        const shipping = subtotal > 0 ? 15 : 0;
        const total = subtotal + shipping;

        if (cartTotalElement) cartTotalElement.innerText = `RM ${total}`;
        if (cartSubtotalElement) cartSubtotalElement.innerText = `RM ${subtotal}`;
        
        const shippingElement = document.getElementById('cart-shipping');
        if (shippingElement) shippingElement.innerText = `RM ${shipping}`;
    }
};

// Initialize cart count on load
document.addEventListener('DOMContentLoaded', () => {
    Cart.updateCartCount();
    if (window.location.pathname.includes('cart.html')) {
        Cart.renderCartPage();
    }
});
