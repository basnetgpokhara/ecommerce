<?php
/** @var array $product @var array $images @var array $related @var array $reviews */
use App\Models\Product;
$price   = (float) $product['price'];
$eff     = Product::effectivePrice($product);
$hasDisc = $eff < $price;
$off     = Product::discountPercent($product);
$primary = $images[0]['image_path'] ?? null;
$out     = (int) $product['stock'] < 1;
$avg     = (float) $product['rating_avg'];
$rcount  = (int) $product['rating_count'];
?>
<section class="page-head">
    <div class="container">
        <nav aria-label="breadcrumb" class="small">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= url('/') ?>">Home</a></li>
                <li class="breadcrumb-item"><a href="<?= url('/shop/' . $product['shop_slug']) ?>"><?= e($product['shop_name']) ?></a></li>
                <li class="breadcrumb-item active"><?= e($product['name']) ?></li>
            </ol>
        </nav>
    </div>
</section>

<div class="container py-4">
    <div class="row g-4">
        <div class="col-lg-5">
            <div class="product-gallery">
                <div class="gallery-main">
                    <img id="galleryMain" src="<?= media_url($primary) ?>" alt="<?= e($product['name']) ?>">
                </div>
                <?php if (count($images) > 1): ?>
                <div class="gallery-thumbs">
                    <?php foreach ($images as $img): ?>
                        <img src="<?= media_url($img['image_path']) ?>" class="js-thumb <?= $img['image_path'] === $primary ? 'active' : '' ?>" alt="thumb">
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="product-shop small text-muted"><?= e($product['shop_name']) ?></div>
            <h1 class="h4 fw-bold mb-2"><?= e($product['name']) ?></h1>
            <div class="d-flex align-items-center gap-2 mb-3">
                <?php \App\Core\View::partial('star_rating', ['rating' => $avg, 'count' => $rcount]); ?>
                <?php if (!empty($product['sku'])): ?><span class="text-muted small">SKU: <?= e($product['sku']) ?></span><?php endif; ?>
            </div>

            <div class="product-detail-price mb-3">
                <?php if ($hasDisc): ?>
                    <span class="now h3 fw-bold"><?= money($eff) ?></span>
                    <span class="was text-muted text-decoration-line-through ms-2"><?= money($price) ?></span>
                    <span class="badge bg-danger ms-2">Save <?= $off ?>%</span>
                <?php else: ?>
                    <span class="now h3 fw-bold"><?= money($price) ?></span>
                <?php endif; ?>
            </div>

            <?php if (!empty($product['short_description'])): ?>
                <p class="text-muted"><?= e($product['short_description']) ?></p>
            <?php endif; ?>

            <p class="small">
                <?php if ($out): ?>
                    <span class="badge bg-secondary">Out of stock</span>
                <?php elseif ((int)$product['stock'] <= 5): ?>
                    <span class="badge bg-warning text-dark"><i class="fas fa-fire me-1"></i>Only <?= (int)$product['stock'] ?> left</span>
                <?php else: ?>
                    <span class="badge bg-success"><i class="fas fa-check me-1"></i>In stock</span>
                <?php endif; ?>
            </p>

            <form action="<?= url('/cart/add') ?>" method="post" class="mt-3">
                <?= csrf_field() ?>
                <input type="hidden" name="product_id" value="<?= (int)$product['id'] ?>">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="qty-selector">
                        <button type="button" class="js-qty-minus">−</button>
                        <input type="number" name="quantity" class="js-qty" value="1" min="1" max="<?= max(1, (int)$product['stock']) ?>">
                        <button type="button" class="js-qty-plus">+</button>
                    </div>
                </div>
                <div class="d-grid gap-2 d-md-flex">
                    <button type="submit" class="btn btn-outline-primary btn-lg flex-grow-1" <?= $out ? 'disabled' : '' ?>>
                        <i class="fas fa-cart-plus me-1"></i> Add to Cart
                    </button>
                    <button type="submit" name="buy_now" value="1" class="btn btn-primary btn-lg flex-grow-1" <?= $out ? 'disabled' : '' ?>>
                        <i class="fas fa-bolt me-1"></i> Buy Now
                    </button>
                </div>
            </form>

            <ul class="product-assurance small mt-3">
                <li><i class="fas fa-truck-fast text-primary"></i> Fast delivery across Nepal</li>
                <li><i class="fas fa-shield-halved text-primary"></i> Secure checkout</li>
                <li><i class="fas fa-rotate-left text-primary"></i> Easy returns</li>
            </ul>
        </div>

        <div class="col-lg-3">
            <div class="seller-card">
                <h6 class="mb-3">Sold by</h6>
                <a href="<?= url('/shop/' . $product['shop_slug']) ?>" class="d-flex align-items-center text-decoration-none">
                    <div class="seller-avatar me-2"><i class="fas fa-store"></i></div>
                    <span class="fw-bold text-dark"><?= e($product['shop_name']) ?></span>
                </a>
                <a href="<?= url('/shop/' . $product['shop_slug']) ?>" class="btn btn-outline-primary btn-sm w-100 mt-3">Visit Shop</a>
            </div>
        </div>
    </div>

    <div class="row g-4 mt-2">
        <div class="col-lg-9">
            <ul class="nav nav-tabs detail-tabs" role="tablist">
                <li class="nav-item"><a class="nav-link active" data-mdb-toggle="tab" href="#desc" role="tab">Description</a></li>
                <li class="nav-item"><a class="nav-link" data-mdb-toggle="tab" href="#spec" role="tab">Specifications</a></li>
                <li class="nav-item"><a class="nav-link" data-mdb-toggle="tab" href="#rev" role="tab">Reviews (<?= $rcount ?>)</a></li>
            </ul>
            <div class="tab-content pt-3">
                <div class="tab-pane fade show active" id="desc" role="tabpanel">
                    <?php if (!empty($product['description'])): ?>
                        <div class="rich-text"><?= nl2br(e($product['description'])) ?></div>
                    <?php else: ?>
                        <p class="text-muted">No description available.</p>
                    <?php endif; ?>
                </div>
                <div class="tab-pane fade" id="spec" role="tabpanel">
                    <?php if (!empty($product['specifications'])): ?>
                        <div class="rich-text"><?= nl2br(e($product['specifications'])) ?></div>
                    <?php else: ?>
                        <p class="text-muted">No specifications provided.</p>
                    <?php endif; ?>
                </div>
                <div class="tab-pane fade" id="rev" role="tabpanel">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="text-center">
                            <div class="display-6 fw-bold"><?= e(number_format($avg, 1)) ?></div>
                            <?php \App\Core\View::partial('star_rating', ['rating' => $avg]); ?>
                        </div>
                        <div class="small text-muted">Based on <?= $rcount ?> review<?= $rcount === 1 ? '' : 's' ?>.</div>
                    </div>
                    <?php if ($reviews): ?>
                        <?php foreach ($reviews as $r): ?>
                            <div class="review-item">
                                <div class="d-flex justify-content-between">
                                    <strong><?= e($r['customer_name']) ?></strong>
                                    <small class="text-muted"><?= e(date('M j, Y', strtotime($r['created_at']))) ?></small>
                                </div>
                                <?php \App\Core\View::partial('star_rating', ['rating' => (int)$r['rating']]); ?>
                                <p class="mb-0"><?= nl2br(e($r['comment'])) ?></p>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted">No reviews yet.</p>
                    <?php endif; ?>

                    <hr class="my-3">
                    <h6 class="mb-2">Write a review</h6>
                    <?php if (auth()->check() && auth()->is('customer')): ?>
                        <form method="post" action="<?= url('/product/' . $product['slug'] . '/review') ?>" class="review-form">
                            <?= csrf_field() ?>
                            <div class="star-input mb-2">
                                <?php for ($s = 5; $s >= 1; $s--): ?>
                                    <input type="radio" name="rating" id="r<?= $s ?>" value="<?= $s ?>" <?= (old('rating') === (string) $s) ? 'checked' : '' ?> required>
                                    <label for="r<?= $s ?>"><i class="fas fa-star"></i></label>
                                <?php endfor; ?>
                            </div>
                            <div class="form-outline mb-2">
                                <textarea name="comment" class="form-control" rows="2" placeholder="Share your experience…"><?= e(old('comment')) ?></textarea>
                                <label class="form-label">Your review (optional comment)</label>
                            </div>
                            <button class="btn btn-primary btn-sm">Submit Review</button>
                        </form>
                    <?php elseif (!auth()->check()): ?>
                        <p class="text-muted small mb-0"><a href="<?= url('/login') ?>">Log in</a> to write a review.</p>
                    <?php else: ?>
                        <p class="text-muted small mb-0">Only customer accounts can write reviews.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php if ($related): ?>
    <section class="mt-5">
        <h3 class="section-title mb-3">Related Products</h3>
        <div class="product-grid">
            <?php foreach ($related as $p): ?>
                <?php \App\Core\View::partial('product_card', ['product' => $p]); ?>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>
</div>
