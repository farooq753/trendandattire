<?php

use App\Http\Controllers\Account\AddressController;
use App\Http\Controllers\Account\DashboardController;
use App\Http\Controllers\Account\OrderController as AccountOrderController;
use App\Http\Controllers\Account\ProfileController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\CouponController as AdminCouponController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\InventoryController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\PaymentController as AdminPaymentController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\ProductionController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\ReturnController as AdminReturnController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ConfiguratorController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReturnController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\TrackingController;
use App\Http\Controllers\Webhook\PaymentWebhookController;
use App\Http\Controllers\WishlistController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('/shop', [CatalogController::class, 'index'])->name('shop');
Route::get('/men', [CatalogController::class, 'gender'])->defaults('gender', 'men')->name('men');
Route::get('/women', [CatalogController::class, 'gender'])->defaults('gender', 'women')->name('women');
Route::get('/unisex', [CatalogController::class, 'gender'])->defaults('gender', 'unisex')->name('unisex');
Route::get('/tops', [CatalogController::class, 'garment'])->defaults('garment', 'top')->name('tops');
Route::get('/pants', [CatalogController::class, 'garment'])->defaults('garment', 'pants')->name('pants');
Route::get('/sets', [CatalogController::class, 'garment'])->defaults('garment', 'set')->name('sets');
Route::get('/jackets', [CatalogController::class, 'garment'])->defaults('garment', 'jacket')->name('jackets');
Route::get('/accessories', [CatalogController::class, 'garment'])->defaults('garment', 'accessory')->name('accessories');
Route::get('/new-arrivals', [CatalogController::class, 'arrivals'])->name('new-arrivals');
Route::get('/best-sellers', [CatalogController::class, 'bestSellers'])->name('best-sellers');
Route::get('/sale', [CatalogController::class, 'sale'])->name('sale');
Route::get('/search', [CatalogController::class, 'search'])->name('search');
Route::get('/products/{product:slug}', [ProductController::class, 'show'])->name('products.show');
Route::post('/products/{product:slug}/cart', [ProductController::class, 'addToCart'])->name('products.cart');

Route::get('/custom-scrubs', [ConfiguratorController::class, 'index'])->name('custom.index');
Route::get('/products/{product:slug}/configure', [ConfiguratorController::class, 'show'])->name('custom.configure');
Route::post('/products/{product:slug}/configure', [ConfiguratorController::class, 'store'])->name('custom.store');

Route::get('/cart', [CartController::class, 'show'])->name('cart.show');
Route::patch('/cart/items/{item}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/cart/items/{item}', [CartController::class, 'destroy'])->name('cart.destroy');
Route::post('/cart/coupon', [CartController::class, 'coupon'])->name('cart.coupon');

Route::get('/checkout', [CheckoutController::class, 'show'])->name('checkout.show');
Route::post('/checkout', [CheckoutController::class, 'store'])->middleware('throttle:checkout')->name('checkout.store');
Route::get('/checkout/confirmation/{order:number}', [CheckoutController::class, 'confirmation'])->name('checkout.confirmation');
Route::post('/checkout/{order:number}/bank-evidence', [CheckoutController::class, 'bankEvidence'])->middleware('throttle:checkout')->name('checkout.bank-evidence');

Route::get('/track', [TrackingController::class, 'create'])->name('tracking.create');
Route::post('/track', [TrackingController::class, 'store'])->middleware('throttle:tracking')->name('tracking.store');
Route::get('/orders/track/{token}', [TrackingController::class, 'show'])->name('tracking.show');

Route::get('/about', [PageController::class, 'about'])->name('about');
Route::get('/contact', [PageController::class, 'contact'])->name('contact');
Route::post('/contact', [PageController::class, 'sendContact'])->middleware('throttle:contact')->name('contact.send');
Route::get('/faq', [PageController::class, 'faq'])->name('faq');
Route::get('/shipping', [PageController::class, 'shipping'])->name('shipping');
Route::get('/returns', [PageController::class, 'returns'])->name('returns.policy');
Route::get('/privacy', [PageController::class, 'privacy'])->name('privacy');
Route::get('/terms', [PageController::class, 'terms'])->name('terms');
Route::get('/size-guide', [PageController::class, 'sizeGuide'])->name('size-guide');
Route::get('/measurement-guide', [PageController::class, 'measurementGuide'])->name('measurement-guide');
Route::post('/preferences', [PageController::class, 'preferences'])->name('preferences');

Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');
Route::get('/robots.txt', [SitemapController::class, 'robots'])->name('robots');

Route::post('/webhooks/stripe', [PaymentWebhookController::class, 'stripe'])->name('webhooks.stripe');
Route::post('/webhooks/paypal', [PaymentWebhookController::class, 'paypal'])->name('webhooks.paypal');

Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store'])->middleware('throttle:login');
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->middleware('throttle:login');
    Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])->middleware('throttle:password')->name('password.email');
    Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password', [NewPasswordController::class, 'store'])->name('password.update');
});

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/email/verify', [EmailVerificationController::class, 'notice'])->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])->middleware('signed')->name('verification.verify');
    Route::post('/email/verification-notification', [EmailVerificationController::class, 'send'])->middleware('throttle:password')->name('verification.send');

    Route::get('/account', DashboardController::class)->name('account.dashboard');
    Route::get('/account/profile', [ProfileController::class, 'edit'])->name('account.profile');
    Route::put('/account/profile', [ProfileController::class, 'update'])->name('account.profile.update');
    Route::post('/account/deletion-request', [ProfileController::class, 'destroyRequest'])->name('account.deletion');
    Route::get('/account/addresses', [AddressController::class, 'index'])->name('account.addresses');
    Route::post('/account/addresses', [AddressController::class, 'store'])->name('account.addresses.store');
    Route::delete('/account/addresses/{address}', [AddressController::class, 'destroy'])->name('account.addresses.destroy');
    Route::get('/account/orders', [AccountOrderController::class, 'index'])->name('account.orders');
    Route::get('/account/orders/{order:number}', [AccountOrderController::class, 'show'])->name('account.orders.show');
    Route::post('/account/orders/{order:number}/cancel', [AccountOrderController::class, 'cancel'])->name('account.orders.cancel');
    Route::get('/account/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
    Route::post('/wishlist/{product:slug}', [WishlistController::class, 'store'])->name('wishlist.store');
    Route::delete('/wishlist/{item}', [WishlistController::class, 'destroy'])->name('wishlist.destroy');
    Route::post('/products/{product:slug}/reviews', [ReviewController::class, 'store'])->name('reviews.store');
    Route::post('/account/orders/{order:number}/returns', [ReturnController::class, 'store'])->name('account.returns.store');
});

Route::prefix('admin')->name('admin.')->middleware(['auth', 'staff'])->group(function () {
    Route::get('/', AdminDashboardController::class)->middleware('permission:dashboard.view')->name('dashboard');
    Route::middleware('permission:products.manage')->group(function () {
        Route::resource('products', AdminProductController::class)->except(['show']);
        Route::post('products/{product:id}/variants', [AdminProductController::class, 'storeVariant'])->name('products.variants.store');
        Route::post('products/{product:id}/images', [AdminProductController::class, 'storeImage'])->name('products.images.store');
    });
    Route::middleware('permission:categories.manage')->group(function () {
        Route::resource('categories', AdminCategoryController::class)->except(['show']);
    });
    Route::middleware('permission:inventory.manage')->group(function () {
        Route::get('inventory', [InventoryController::class, 'index'])->name('inventory.index');
        Route::post('inventory/{variant}/adjust', [InventoryController::class, 'adjust'])->name('inventory.adjust');
    });
    Route::middleware('permission:orders.view')->group(function () {
        Route::get('orders', [AdminOrderController::class, 'index'])->name('orders.index');
        Route::get('orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
        Route::get('orders/{order}/invoice', [AdminOrderController::class, 'invoice'])->name('orders.invoice');
        Route::get('orders/{order}/packing-slip', [AdminOrderController::class, 'packingSlip'])->name('orders.packing-slip');
    });
    Route::middleware('permission:orders.manage')->group(function () {
        Route::post('orders/{order}/shipments', [AdminOrderController::class, 'ship'])->name('orders.ship');
        Route::post('orders/{order}/cancel', [AdminOrderController::class, 'cancel'])->name('orders.cancel');
    });
    Route::middleware('permission:payments.verify')->group(function () {
        Route::post('payments/{payment}/verify-bank', [AdminPaymentController::class, 'verifyBank'])->name('payments.verify-bank');
        Route::post('payments/{payment}/reject-bank', [AdminPaymentController::class, 'rejectBank'])->name('payments.reject-bank');
        Route::post('payments/{payment}/collect-cod', [AdminPaymentController::class, 'collectCod'])->name('payments.collect-cod');
    });
    Route::middleware('permission:refunds.manage')->group(function () {
        Route::post('payments/{payment}/refunds', [AdminPaymentController::class, 'refund'])->name('payments.refund');
    });
    Route::middleware('permission:production.manage')->group(function () {
        Route::get('production', [ProductionController::class, 'index'])->name('production.index');
        Route::post('production/{job}', [ProductionController::class, 'update'])->name('production.update');
    });
    Route::middleware('permission:customers.view')->group(function () {
        Route::get('customers', [CustomerController::class, 'index'])->name('customers.index');
        Route::get('customers/{user}', [CustomerController::class, 'show'])->name('customers.show');
    });
    Route::middleware('permission:coupons.manage')->resource('coupons', AdminCouponController::class)->except(['show']);
    Route::middleware('permission:banners.manage')->resource('banners', BannerController::class)->except(['show']);
    Route::middleware('permission:settings.manage')->group(function () {
        Route::get('settings', [SettingController::class, 'edit'])->name('settings.edit');
        Route::put('settings', [SettingController::class, 'update'])->name('settings.update');
    });
    Route::middleware('permission:reports.view')->group(function () {
        Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
    });
    Route::middleware('permission:reports.export')->group(function () {
        Route::get('reports/export', [ReportController::class, 'export'])->name('reports.export');
    });
    Route::middleware('permission:returns.manage')->group(function () {
        Route::get('returns', [AdminReturnController::class, 'index'])->name('returns.index');
        Route::post('returns/{productReturn}', [AdminReturnController::class, 'update'])->name('returns.update');
    });
    Route::middleware('permission:reviews.moderate')->post('reviews/{review}/approve', [ReviewController::class, 'approve'])->name('reviews.approve');
});
