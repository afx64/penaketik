<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AuthorController;
use App\Http\Controllers\BookLikeController;
use App\Http\Controllers\BookstoreController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\Dashboard\AccessGrantController;
use App\Http\Controllers\Dashboard\BookController;
use App\Http\Controllers\Dashboard\ChapterController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Dashboard\OrderController as DashboardOrderController;
use App\Http\Controllers\Dashboard\ProfileController;
use App\Http\Controllers\Dashboard\SettingsController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LibraryController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ReaderController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public storefront
|--------------------------------------------------------------------------
*/

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/bookstore', [BookstoreController::class, 'index'])->name('books.index');
Route::get('/bookstore/{book:slug}', [BookstoreController::class, 'show'])->name('books.show');
Route::get('/bookstore/{book:slug}/read/{chapter:slug}', [BookstoreController::class, 'read'])->name('books.read');

Route::get('/authors', [AuthorController::class, 'index'])->name('authors.index');
Route::get('/authors/{user}', [AuthorController::class, 'show'])->name('authors.show');

/*
|--------------------------------------------------------------------------
| Authentication
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');
});

/*
|--------------------------------------------------------------------------
| Reader account area
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/library', [LibraryController::class, 'index'])->name('library.index');

    // Reader hub: purchases, invitations, liked books and comments.
    Route::get('/reader', [ReaderController::class, 'index'])->name('reader.index');

    Route::post('/bookstore/{book:slug}/comments', [CommentController::class, 'store'])->name('comments.store');
    Route::post('/bookstore/{book:slug}/like', [BookLikeController::class, 'toggle'])->name('books.like');

    Route::post('/bookstore/{book:slug}/order', [OrderController::class, 'store'])->name('orders.store');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::post('/orders/{order}/proof', [OrderController::class, 'uploadProof'])->name('orders.proof');
    Route::post('/orders/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');
});

/*
|--------------------------------------------------------------------------
| Author dashboard
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'author'])->prefix('dashboard')->name('dashboard.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('index');

    Route::get('/books', [BookController::class, 'index'])->name('books.index');
    Route::get('/books/create', [BookController::class, 'create'])->name('books.create');
    Route::post('/books', [BookController::class, 'store'])->name('books.store');
    Route::get('/books/{book:slug}/edit', [BookController::class, 'edit'])->name('books.edit');
    Route::put('/books/{book:slug}', [BookController::class, 'update'])->name('books.update');
    Route::delete('/books/{book:slug}', [BookController::class, 'destroy'])->name('books.destroy');

    Route::get('/books/{book:slug}/chapters/create', [ChapterController::class, 'create'])->name('chapters.create');
    Route::post('/books/{book:slug}/chapters', [ChapterController::class, 'store'])->name('chapters.store');
    Route::get('/books/{book:slug}/chapters/{chapter}/edit', [ChapterController::class, 'edit'])->name('chapters.edit');
    Route::put('/books/{book:slug}/chapters/{chapter}', [ChapterController::class, 'update'])->name('chapters.update');
    Route::delete('/books/{book:slug}/chapters/{chapter}', [ChapterController::class, 'destroy'])->name('chapters.destroy');
    Route::post('/books/{book:slug}/chapters/reorder', [ChapterController::class, 'reorder'])->name('chapters.reorder');
    Route::post('/books/{book:slug}/chapters/image', [ChapterController::class, 'uploadImage'])->name('chapters.image');

    Route::get('/books/{book:slug}/access', [AccessGrantController::class, 'index'])->name('access.index');
    Route::post('/books/{book:slug}/access', [AccessGrantController::class, 'store'])->name('access.store');
    Route::delete('/books/{book:slug}/access/{user}', [AccessGrantController::class, 'destroy'])->name('access.destroy');

    Route::get('/orders', [DashboardOrderController::class, 'index'])->name('orders.index');
    Route::post('/orders/{order}/approve', [DashboardOrderController::class, 'approve'])->name('orders.approve');
    Route::post('/orders/{order}/reject', [DashboardOrderController::class, 'reject'])->name('orders.reject');
    Route::get('/orders/{order}/proof', [DashboardOrderController::class, 'proof'])->name('orders.proof');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/links', [ProfileController::class, 'storeLink'])->name('profile.links.store');
    Route::delete('/profile/links/{storeLink}', [ProfileController::class, 'destroyLink'])->name('profile.links.destroy');

    Route::get('/settings/branding', [SettingsController::class, 'branding'])->name('settings.branding');
    Route::put('/settings/branding', [SettingsController::class, 'updateBranding'])->name('settings.branding.update');

    Route::get('/settings/seo', [SettingsController::class, 'seo'])->name('settings.seo');
    Route::put('/settings/seo', [SettingsController::class, 'updateSeo'])->name('settings.seo.update');
    Route::get('/settings/mail', [SettingsController::class, 'mail'])->name('settings.mail');
    Route::put('/settings/mail', [SettingsController::class, 'updateMail'])->name('settings.mail.update');
    Route::post('/settings/mail/test', [SettingsController::class, 'testMail'])->name('settings.mail.test');
    Route::get('/settings/payment', [SettingsController::class, 'payment'])->name('settings.payment');
    Route::put('/settings/payment', [SettingsController::class, 'updatePayment'])->name('settings.payment.update');
});
