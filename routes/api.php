<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VolunteerProfileController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\VolunteerOpportunityController;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\VolunteerActivityController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\NotificationController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public routes (không cần authentication)
Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
});

// Protected routes (cần authentication)
Route::middleware('auth:sanctum')->group(function () {
    
    // Auth routes
    Route::prefix('auth')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);
    });

    // Dashboard - role-based
    Route::get('dashboard', [DashboardController::class, 'index']);

    // User management
    Route::prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'index']);
        Route::get('{id}', [UserController::class, 'show']);
        Route::put('{id}', [UserController::class, 'update']);
        Route::put('{id}/password', [UserController::class, 'updatePassword']);
        Route::post('{id}/deactivate', [UserController::class, 'deactivate']);
        Route::post('{id}/activate', [UserController::class, 'activate']);
        Route::post('{id}/verify', [UserController::class, 'verify']);
    });

    // Volunteer Profile
    Route::prefix('volunteer-profiles')->group(function () {
        Route::get('{userId}', [VolunteerProfileController::class, 'show']);
        Route::put('{userId}', [VolunteerProfileController::class, 'update']);
        Route::post('{userId}/skills', [VolunteerProfileController::class, 'addSkill']);
        Route::delete('{userId}/skills', [VolunteerProfileController::class, 'removeSkill']);
        Route::get('{userId}/statistics', [VolunteerProfileController::class, 'statistics']);
    });

    // Organizations
    Route::prefix('organizations')->group(function () {
        Route::get('/', [OrganizationController::class, 'index']);
        Route::get('{id}', [OrganizationController::class, 'show']);
        Route::put('{id}', [OrganizationController::class, 'update']);
        Route::post('{id}/verify', [OrganizationController::class, 'verify']);
        Route::post('{id}/reject', [OrganizationController::class, 'reject']);
        Route::get('{id}/statistics', [OrganizationController::class, 'statistics']);
    });

    // Categories
    Route::get('categories', [CategoryController::class, 'index']);
    Route::get('categories/{id}', [CategoryController::class, 'show']);

    // Opportunities
    Route::prefix('opportunities')->group(function () {
        Route::get('/', [VolunteerOpportunityController::class, 'index']);
        Route::post('/', [VolunteerOpportunityController::class, 'store']);
        Route::get('recommendations', [VolunteerOpportunityController::class, 'recommendations']);
        Route::get('{id}', [VolunteerOpportunityController::class, 'show']);
        Route::put('{id}', [VolunteerOpportunityController::class, 'update']);
        Route::post('{id}/pause', [VolunteerOpportunityController::class, 'pause']);
        Route::post('{id}/resume', [VolunteerOpportunityController::class, 'resume']);
        Route::post('{id}/complete', [VolunteerOpportunityController::class, 'complete']);
        Route::post('{id}/cancel', [VolunteerOpportunityController::class, 'cancel']);
    });

    // Applications
    Route::prefix('applications')->group(function () {
        Route::get('/', [ApplicationController::class, 'index']);
        Route::post('/', [ApplicationController::class, 'store']);
        Route::get('{id}', [ApplicationController::class, 'show']);
        Route::post('{id}/accept', [ApplicationController::class, 'accept']);
        Route::post('{id}/reject', [ApplicationController::class, 'reject']);
        Route::post('{id}/withdraw', [ApplicationController::class, 'withdraw']);
        Route::post('{id}/schedule-interview', [ApplicationController::class, 'scheduleInterview']);
    });

    // Activities
    Route::prefix('activities')->group(function () {
        Route::get('/', [VolunteerActivityController::class, 'index']);
        Route::post('/', [VolunteerActivityController::class, 'store']);
        Route::get('{id}', [VolunteerActivityController::class, 'show']);
        Route::post('{id}/verify', [VolunteerActivityController::class, 'verify']);
        Route::post('{id}/dispute', [VolunteerActivityController::class, 'dispute']);
        Route::get('report/{volunteerId}', [VolunteerActivityController::class, 'report']);
    });

    // Reviews
    Route::prefix('reviews')->group(function () {
        Route::get('/', [ReviewController::class, 'index']);
        Route::post('/', [ReviewController::class, 'store']);
        Route::get('{id}', [ReviewController::class, 'show']);
        Route::post('{id}/approve', [ReviewController::class, 'approve']);
        Route::post('{id}/helpful', [ReviewController::class, 'markHelpful']);
    });

    // Favorites
    Route::prefix('favorites')->group(function () {
        Route::get('/', [FavoriteController::class, 'index']);
        Route::post('/', [FavoriteController::class, 'store']);
        Route::put('{id}', [FavoriteController::class, 'update']);
        Route::delete('{id}', [FavoriteController::class, 'destroy']);
    });

    // Notifications
    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index']);
        Route::get('unread-count', [NotificationController::class, 'unreadCount']);
        Route::post('{id}/read', [NotificationController::class, 'markAsRead']);
        Route::post('mark-all-read', [NotificationController::class, 'markAllAsRead']);
        Route::delete('{id}', [NotificationController::class, 'destroy']);
    });
});