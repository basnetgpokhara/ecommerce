<footer class="site-footer mt-auto">
    <div class="container py-5">
        <div class="row g-4">
            <div class="col-lg-4 col-md-6">
                <h6 class="footer-title"><?= e(setting('site_name', APP_NAME)) ?></h6>
                <p class="small text-light-emphasis">
                    <?= e(setting('site_tagline', 'A modern multi-vendor marketplace connecting local sellers and shoppers across Nepal.')) ?>
                </p>
                <div class="d-flex gap-2 mt-3">
                    <a href="#" class="footer-social" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="footer-social" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="footer-social" aria-label="Twitter"><i class="fab fa-x-twitter"></i></a>
                    <a href="#" class="footer-social" aria-label="YouTube"><i class="fab fa-youtube"></i></a>
                </div>
            </div>
            <div class="col-lg-2 col-6">
                <h6 class="footer-title">Shop</h6>
                <ul class="footer-links">
                    <li><a href="<?= url('/categories') ?>">All Categories</a></li>
                    <li><a href="<?= url('/shops') ?>">All Shops</a></li>
                    <li><a href="<?= url('/category/electronics') ?>">Electronics</a></li>
                    <li><a href="<?= url('/category/fashion') ?>">Fashion</a></li>
                </ul>
            </div>
            <div class="col-lg-2 col-6">
                <h6 class="footer-title">Customer Service</h6>
                <ul class="footer-links">
                    <li><a href="<?= url('/page/about') ?>">About Us</a></li>
                    <li><a href="<?= url('/page/returns') ?>">Returns Policy</a></li>
                    <li><a href="<?= url('/page/shipping') ?>">Shipping</a></li>
                    <li><a href="<?= url('/page/contact') ?>">Contact Us</a></li>
                </ul>
            </div>
            <div class="col-lg-4 col-md-6">
                <h6 class="footer-title">Newsletter</h6>
                <p class="small text-light-emphasis">Get the best deals delivered to your inbox.</p>
                <form class="d-flex" onsubmit="return false;">
                    <input type="email" class="form-control" placeholder="Your email" aria-label="Email">
                    <button class="btn btn-primary ms-2">Subscribe</button>
                </form>
                <div class="d-flex flex-wrap gap-2 mt-3">
                    <span class="pay-badge pay-esewa">eSewa</span>
                    <span class="pay-badge pay-khalti">Khalti</span>
                    <span class="pay-badge pay-fonepay">Fonepay</span>
                    <span class="pay-badge pay-card"><i class="fab fa-cc-visa me-1"></i>Cards</span>
                    <span class="pay-badge pay-cod">COD</span>
                </div>
            </div>
        </div>
    </div>
    <div class="footer-bottom">
        <div class="container d-flex flex-column flex-md-row justify-content-between align-items-center py-2 small">
            <span>&copy; <?= date('Y') ?> <?= e(setting('site_name', APP_NAME)) ?>. All rights reserved.</span>
            <span>
                <a href="<?= url('/page/privacy') ?>" class="text-light text-decoration-none me-3">Privacy</a>
                <a href="<?= url('/page/terms') ?>" class="text-light text-decoration-none">Terms</a>
            </span>
        </div>
    </div>
</footer>
