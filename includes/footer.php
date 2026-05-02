    </main>

    <!-- Footer -->
    <footer class="main-footer">
        <div class="footer-wave">
            <svg viewBox="0 0 1440 100" preserveAspectRatio="none">
                <path d="M0,40 C150,80 350,0 500,40 C650,80 800,20 1000,50 C1200,80 1350,30 1440,60 L1440,100 L0,100 Z" fill="currentColor"></path>
            </svg>
        </div>
        <div class="container">
            <div class="footer-grid">
                <!-- About -->
                <div class="footer-col">
                    <div class="footer-logo">
                        <span class="logo-text">H.A</span>
                        <span class="logo-sub">Collection</span>
                    </div>
                    <p class="footer-about">Your premium destination for elegant feminine fashion. We curate the finest pieces to make every woman feel confident and beautiful.</p>
                    <div class="footer-social">
                        <a href="#" title="Facebook"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" title="Instagram"><i class="fab fa-instagram"></i></a>
                        <a href="#" title="Twitter"><i class="fab fa-twitter"></i></a>
                        <a href="#" title="Pinterest"><i class="fab fa-pinterest-p"></i></a>
                        <a href="#" title="TikTok"><i class="fab fa-tiktok"></i></a>
                    </div>
                </div>

                <!-- Quick Links -->
                <div class="footer-col">
                    <h3>Quick Links</h3>
                    <ul>
                        <li><a href="<?= SITE_URL ?>/">Home</a></li>
                        <li><a href="<?= SITE_URL ?>/pages/shop.php">Shop</a></li>
                        <li><a href="<?= SITE_URL ?>/pages/shop.php?filter=new">New Arrivals</a></li>
                        <li><a href="<?= SITE_URL ?>/pages/shop.php?filter=sale">Sale</a></li>
                        <li><a href="<?= SITE_URL ?>/pages/cart.php">Cart</a></li>
                    </ul>
                </div>

                <!-- Categories -->
                <div class="footer-col">
                    <h3>Categories</h3>
                    <ul>
                        <?php
                        $footerCats = getCategories($pdo);
                        foreach (array_slice($footerCats, 0, 6) as $cat): ?>
                            <li><a href="<?= SITE_URL ?>/pages/shop.php?category=<?= $cat['slug'] ?>"><?= sanitize($cat['name']) ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <!-- Contact -->
                <div class="footer-col">
                    <h3>Contact Us</h3>
                    <ul class="contact-list">
                        <li><i class="fas fa-map-marker-alt"></i> Lahore, Pakistan</li>
                        <li><i class="fas fa-phone"></i> +92 300 1234567</li>
                        <li><i class="fas fa-envelope"></i> info@hacollection.com</li>
                        <li><i class="fas fa-clock"></i> Mon - Sat: 10AM - 9PM</li>
                    </ul>
                </div>
            </div>

            <!-- Payment Methods -->
            <div class="footer-payment">
                <span>We Accept:</span>
                <div class="payment-icons">
                    <i class="fas fa-credit-card"></i>
                    <i class="fas fa-money-bill-wave"></i>
                    <span class="payment-label">Cash on Delivery</span>
                </div>
            </div>

            <div class="footer-bottom">
                <p>&copy; <?= date('Y') ?> <?= SITE_NAME ?>. All Rights Reserved. Designed with <i class="fas fa-heart"></i></p>
            </div>
        </div>
    </footer>

    <script src="<?= SITE_URL ?>/assets/js/main.js"></script>
    <?php if (isset($extraJS)): ?>
        <script src="<?= SITE_URL ?>/assets/js/<?= $extraJS ?>"></script>
    <?php endif; ?>
</body>
</html>
