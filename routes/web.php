<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Admin\OrganizationVerificationController;
use App\Http\Controllers\Admin\ActivityVerificationController;
use App\Http\Controllers\Admin\ReviewModerationController;
use App\Http\Controllers\Admin\ReportGenerationController;
use App\Http\Controllers\Admin\AdminController;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VolunteerOpportunityController;
use App\Http\Controllers\ApplicationController;


/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

// Home page
Route::get('/', function () {
    return view('welcome');
})->name('welcome');

// Browse opportunities (public)
Route::get('/opportunities', [VolunteerOpportunityController::class, 'index'])->name('opportunities.index');
Route::get('/opportunities/{id}', [VolunteerOpportunityController::class, 'show'])->name('opportunities.show');

// Public profile
Route::get('/user/{id}/profile', [UserController::class, 'showPublicProfile'])->name('user.public-profile');

/*
|--------------------------------------------------------------------------
| Guest Routes (Authentication)
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    
    // Registration
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);

    // Login
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);

    // Forgot Password
    Route::get('/forgot-password', [AuthController::class, 'showForgotPasswordForm'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLinkEmail'])->name('password.email');

    // Reset Password
    Route::get('/reset-password/{token}', [AuthController::class, 'showResetPasswordForm'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');

    // Social Login (Google & Facebook)
    Route::get('/auth/google', [AuthController::class, 'redirectToGoogle'])->name('auth.google');
    Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback']);
    
    Route::get('/auth/facebook', [AuthController::class, 'redirectToFacebook'])->name('auth.facebook');
    Route::get('/auth/facebook/callback', [AuthController::class, 'handleFacebookCallback']);
});

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    
    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    
    // Home/Dashboard (after login)
    Route::get('/home', function () {
        $user = auth()->user();
        
        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        } elseif ($user->isOrganization()) {
            return redirect()->route('opportunities.my-opportunities');
        } else {
            return redirect()->route('opportunities.recommendations');
        }
    })->name('home');
    
    // ============================================
    // USER ROUTES
    // ============================================
    
    // Profile
    Route::get('/profile', [UserController::class, 'profile'])->name('user.profile');
    Route::get('/profile/edit', [UserController::class, 'editProfile'])->name('user.edit-profile');
    Route::post('/profile/update', [UserController::class, 'updateProfile'])->name('user.update-profile');
    
    // Password
    Route::get('/change-password', [UserController::class, 'showChangePasswordForm'])->name('user.change-password');
    Route::post('/change-password', [UserController::class, 'changePassword'])->name('user.update-password');
    
    // Account
    Route::post('/deactivate-account', [UserController::class, 'deactivateAccount'])->name('user.deactivate');
    
    // Statistics
    Route::get('/api/user/statistics', [UserController::class, 'getStatistics'])->name('user.statistics');
    
    // Search
    Route::get('/api/users/search', [UserController::class, 'search'])->name('users.search');
    
    // Notifications
    Route::get('/notifications', [UserController::class, 'notifications'])->name('user.notifications');
    Route::post('/notifications/{id}/read', [UserController::class, 'markNotificationRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [UserController::class, 'markAllNotificationsRead'])->name('notifications.read-all');
    Route::delete('/notifications/{id}', [UserController::class, 'deleteNotification'])->name('notifications.delete');
    
    // ============================================
    // OPPORTUNITY ROUTES
    // ============================================
    
    // Create opportunity (Organization only)
    Route::get('/opportunities/create', [VolunteerOpportunityController::class, 'create'])->name('opportunities.create');
    Route::post('/opportunities', [VolunteerOpportunityController::class, 'store'])->name('opportunities.store');
    
    // Edit/Update opportunity
    Route::get('/opportunities/{id}/edit', [VolunteerOpportunityController::class, 'edit'])->name('opportunities.edit');
    Route::put('/opportunities/{id}', [VolunteerOpportunityController::class, 'update'])->name('opportunities.update');
    Route::delete('/opportunities/{id}', [VolunteerOpportunityController::class, 'destroy'])->name('opportunities.destroy');
    
    // Organization's opportunities
    Route::get('/my-opportunities', [VolunteerOpportunityController::class, 'myOpportunities'])->name('opportunities.my-opportunities');
    
    // Change status
    Route::post('/opportunities/{id}/change-status', [VolunteerOpportunityController::class, 'changeStatus'])->name('opportunities.change-status');
    
    // Recommendations
    Route::get('/opportunities/recommendations', [VolunteerOpportunityController::class, 'recommendations'])->name('opportunities.recommendations');
    
    // ============================================
    // APPLICATION ROUTES
    // ============================================
    
    // Apply for opportunity
    Route::get('/opportunities/{id}/apply', [ApplicationController::class, 'create'])->name('applications.create');
    Route::post('/opportunities/{id}/apply', [ApplicationController::class, 'store'])->name('applications.store');
    
    // View application
    Route::get('/applications/{id}', [ApplicationController::class, 'show'])->name('applications.show');
    
    // Volunteer's applications
    Route::get('/my-applications', [ApplicationController::class, 'myApplications'])->name('applications.my-applications');
    Route::post('/applications/{id}/withdraw', [ApplicationController::class, 'withdraw'])->name('applications.withdraw');
    
    // Organization's received applications
    Route::get('/received-applications', [ApplicationController::class, 'receivedApplications'])->name('applications.received');
    Route::get('/opportunities/{opportunityId}/applications', [ApplicationController::class, 'receivedApplications'])->name('applications.by-opportunity');
    Route::post('/applications/{id}/update-status', [ApplicationController::class, 'updateStatus'])->name('applications.update-status');
    Route::post('/applications/{id}/schedule-interview', [ApplicationController::class, 'scheduleInterview'])->name('applications.schedule-interview');
    Route::post('/applications/bulk-update', [ApplicationController::class, 'bulkUpdateStatus'])->name('applications.bulk-update');
    
    // Statistics & Export
    Route::get('/api/applications/statistics', [ApplicationController::class, 'getStatistics'])->name('applications.statistics');
    Route::get('/applications/export/{opportunityId?}', [ApplicationController::class, 'export'])->name('applications.export');
});

Route::prefix('admin')->middleware(['auth', \App\Http\Middleware\AdminMiddleware::class])->name('admin.')->group(function () {
    
    // Dashboard
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    
    // Users Management
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', [AdminController::class, 'usersIndex'])->name('index');
        Route::get('/{id}', [AdminController::class, 'usersShow'])->name('show');
        Route::post('/', [AdminController::class, 'usersStore'])->name('store');
        Route::put('/{id}', [AdminController::class, 'usersUpdate'])->name('update');
        Route::post('/{id}/status', [AdminController::class, 'usersUpdateStatus'])->name('updateStatus');
        Route::delete('/{id}', [AdminController::class, 'usersDestroy'])->name('destroy');
        Route::post('/bulk-action', [AdminController::class, 'usersBulkAction'])->name('bulk-action');
    });
    
    // Organizations Management
    Route::prefix('organizations')->name('organizations.')->group(function () {
        Route::get('/', [AdminController::class, 'organizationsIndex'])->name('index');
        Route::post('/{id}/approve', [AdminController::class, 'organizationsApprove'])->name('approve');
        Route::post('/{id}/reject', [AdminController::class, 'organizationsReject'])->name('reject');
    });
    
    // Opportunities Management
    Route::prefix('opportunities')->name('opportunities.')->group(function () {
        Route::get('/', [AdminController::class, 'opportunitiesIndex'])->name('index');
    });
    
    // Applications Management
    Route::prefix('applications')->name('applications.')->group(function () {
        Route::get('/', [AdminController::class, 'applicationsIndex'])->name('index');
    });
    
    // Categories Management
    Route::prefix('categories')->name('categories.')->group(function () {
        Route::get('/', [AdminController::class, 'categoriesIndex'])->name('index');
        Route::post('/', [AdminController::class, 'categoriesStore'])->name('store');
    });
    
    // Activities Management
    Route::prefix('activities')->name('activities.')->group(function () {
        Route::get('/', [AdminController::class, 'activitiesIndex'])->name('index');
    });
    
    // Reviews Management
    Route::prefix('reviews')->name('reviews.')->group(function () {
        Route::get('/', [AdminController::class, 'reviewsIndex'])->name('index');
    });
    
    // Analytics
    Route::get('/analytics', [AdminController::class, 'analytics'])->name('analytics');
    
    // Reports
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [AdminController::class, 'reportsIndex'])->name('index');
        Route::post('/generate', [AdminController::class, 'reportsGenerate'])->name('generate');
    });
    
    // Settings
    Route::get('/settings', [AdminController::class, 'settings'])->name('settings');
    Route::post('/settings', [AdminController::class, 'updateSettings'])->name('settings.update');
});
// Route::get('/test-auth-public', function() {
//     return [
//         'is_authenticated' => Auth::check(),
//         'user' => Auth::user(),
//         'message' => Auth::check() ? 'User is logged in' : 'Not logged in'
//     ];
// });

// // Test route - AUTHENTICATED (phải login)
// Route::get('/test-auth-protected', function() {
//     $user = auth()->user();
    
//     return [
//         'success' => true,
//         'user_id' => $user->user_id,
//         'email' => $user->email,
//         'name' => $user->first_name . ' ' . $user->last_name,
//         'user_type' => $user->user_type,
//     ];
// })->middleware('auth');

