    </main>
    
    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div>
                    <h3>Gustico's</h3>
                    <p>Helados artesanales con el mejor sabor de Los Samanes, Valencia.</p>
                </div>
                <div>
                    <h3>Contacto</h3>
                    <p><i class="fab fa-whatsapp"></i> Sucursal Norte: 0424-1791335</p>
                    <p><i class="fab fa-whatsapp"></i> Sucursal Sur: 0412-4567890</p>
                    <p><i class="fab fa-instagram"></i> @gusticos.helados</p>
                </div>
                <div>
                    <h3>Horario</h3>
                    <p>Lunes a Domingo</p>
                    <p>2:00 PM - 10:00 PM</p>
                </div>
                <div>
                    <h3>Dirección</h3>
                    <p>Sucursal Norte: LOS SAMANES</p>
                    <p>Sucursal Sur: VALENCIA SUR</p>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- CARRITO FLOTANTE -->
    <div class="fab" onclick="window.location.href='carrito.php'">
        <i class="fas fa-shopping-cart"></i>
        <span class="cart-count" id="cart-count">0</span>
    </div>
    
    <script>
        // Actualizar contador del carrito
        function actualizarContadorCarrito() {
            const carrito = JSON.parse(localStorage.getItem('carrito') || '{}');
            const total = Object.values(carrito).reduce((a, b) => a + b, 0);
            const contador = document.getElementById('cart-count');
            if (contador) {
                contador.textContent = total;
                contador.style.display = total > 0 ? 'flex' : 'none';
            }
        }
        
        // Inicializar al cargar la página
        document.addEventListener('DOMContentLoaded', function() {
            actualizarContadorCarrito();
        });
    </script>
</body>
</html>