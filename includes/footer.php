<!-- Footer -->
<footer class="footer">
    <div class="container">
        <div class="footer-content">
            <!-- Información de contacto -->
            <div class="footer-section">
                <h3>Barber League</h3>
                <p>Tu destino para el cuidado personal premium y recreación deportiva en Ibagué.</p>
                <div class="social-links">
                    <a href="https://facebook.com/barberleague" class="social-link" target="_blank" rel="noopener" aria-label="Facebook">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="https://instagram.com/barberleague" class="social-link" target="_blank" rel="noopener" aria-label="Instagram">
                        <i class="fab fa-instagram"></i>
                    </a>
                    <a href="https://wa.me/573001234567" class="social-link" target="_blank" rel="noopener" aria-label="WhatsApp">
                        <i class="fab fa-whatsapp"></i>
                    </a>
                    <a href="https://tiktok.com/@barberleague" class="social-link" target="_blank" rel="noopener" aria-label="TikTok">
                        <i class="fab fa-tiktok"></i>
                    </a>
                </div>
            </div>
            
            <!-- Horarios -->
            <div class="footer-section">
                <h3>Horarios</h3>
                <ul>
                    <li><i class="far fa-clock"></i> Lunes - Viernes: 9:00 AM - 9:00 PM</li>
                    <li><i class="far fa-clock"></i> Sábados: 9:00 AM - 9:00 PM</li>
                    <li><i class="far fa-clock"></i> Domingos: 10:00 AM - 7:00 PM</li>
                </ul>
            </div>
            
            <!-- Contacto -->
            <div class="footer-section">
                <h3>Contacto</h3>
                <ul>
                    <li>
                        <i class="fas fa-map-marker-alt"></i>
                        <a href="https://maps.google.com/?q=Ibague+Tolima" target="_blank" rel="noopener">
                            Calle 42 #15-32, Ibagué, Tolima
                        </a>
                    </li>
                    <li>
                        <i class="fas fa-phone"></i>
                        <a href="tel:+573001234567">+57 300 123 4567</a>
                    </li>
                    <li>
                        <i class="fas fa-envelope"></i>
                        <a href="mailto:contacto@barberleague.com">contacto@barberleague.com</a>
                    </li>
                </ul>
            </div>
            
            <!-- Enlaces rápidos -->
            <div class="footer-section">
                <h3>Enlaces Rápidos</h3>
                <ul>
                    <li><a href="servicios.php">Servicios</a></li>
                    <li><a href="cancha.php">Cancha Sintética</a></li>
                    <li><a href="reservar.php">Hacer Reserva</a></li>
                    <li><a href="contacto.php">Contacto</a></li>
                    <li><a href="admin/login.php">Acceso Admin</a></li>
                </ul>
            </div>
        </div>
        
        <!-- Copyright -->
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> Barber League. Todos los derechos reservados.</p>
            <p>Desarrollado con <i class="fas fa-heart" style="color: var(--primary-color);"></i> en Ibagué, Colombia</p>
        </div>
    </div>
</footer>

<!-- Scripts -->
<script src="assets/js/main.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Script adicional para página específica (opcional) -->
<?php if (isset($extraScript)): ?>
    <script src="<?php echo $extraScript; ?>"></script>
<?php endif; ?>

</body>
</html>