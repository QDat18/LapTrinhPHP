<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VolunteerProfileController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\VolunteerOpportunityController;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\VolunteerActivityController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\ConversationController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\VideoCallController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\AdminEmailController;

/*
|--------------------------------------------------------------------------
| Public Routes (Không cần đăng nhập)
|--------------------------------------------------------------------------
*/

// Home & Static Pages
Route::get('/', function () {
    return view('pages.home');
})->name('home');

Route::get('/about', function () {
    return view('pages.about');
})->name('about');

Route::get('/contact', function () {
    return view('pages.contact');
})->name('contact');

Route::get('/privacy', function () {
    return view('pages.privacy');
})->name('privacy');

Route::get('/terms', function () {
    return view('pages.terms');
})->name('terms');

// Public Opportunities
Route::get('/opportunities', [VolunteerOpportunityController::class, 'index'])->name('opportunities.index');
Route::get('/opportunities/{id}', [VolunteerOpportunityController::class, 'show'])->name('opportunities.show');

// Public Organizations
Route::get('/organizations', [OrganizationController::class, 'index'])->name('organizations.index');
Route::get('/organizations/{id}', [OrganizationController::class, 'show'])->name('organizations.show');

// Public Search
Route::get('/search', [SearchController::class, 'search'])->name('search');
Route::get('/search/advanced', [SearchController::class, 'advancedSearch'])->name('search.advanced');
Route::get('/search/suggestions', [SearchController::class, 'suggestions'])->name('search.suggestions');
Route::get('/search/category/{id}', [SearchController::class, 'searchByCategory'])->name('search.category');

// Public Reviews
Route::get('/reviews', [ReviewController::class, 'index'])->name('reviews.index');
Route::get('/reviews/user/{userId}', [ReviewController::class, 'userReviews'])->name('reviews.user');

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    // Login
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');

    // Register
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.post');

    // Social Login
    Route::get('/login/google', [AuthController::class, 'redirectToGoogle'])->name('login.google');
    Route::get('/login/google/callback', [AuthController::class, 'handleGoogleCallback']);
    Route::get('/login/facebook', [AuthController::class, 'redirectToFacebook'])->name('login.facebook');
    Route::get('/login/facebook/callback', [AuthController::class, 'handleFacebookCallback']);

    // Password Reset
    Route::get('/forgot-password', [AuthController::class, 'showForgotPasswordForm'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reset-password/{token}', [AuthController::class, 'showResetPasswordForm'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

/*
|--------------------------------------------------------------------------
| Authenticated Routes (Tất cả user đã đăng nhập)
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    // Dashboard - Route to appropriate dashboard based on user type
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // User Profile
    // Route::get('/profile', [UserController::class, 'profile'])->name('profile');
    // Route::get('/profile/edit', [UserController::class, 'editProfile'])->name('profile.edit');
    // Route::put('/profile', [UserController::class, 'updateProfile'])->name('profile.update');
    // Route::post('/profile/avatar', [UserController::class, 'updateAvatar'])->name('profile.avatar');
    // Route::get('/settings', [UserController::class, 'settings'])->name('settings');
    // Route::put('/settings', [UserController::class, 'updateSettings'])->name('settings.update');
    // Route::post('/change-password', [UserController::class, 'changePassword'])->name('password.change');
    Route::get('/profile', [UserController::class, 'profile'])->name('profile');
    Route::get('/profile/edit', [UserController::class, 'editProfile'])->name('profile.edit');
    Route::put('/profile', [UserController::class, 'updateProfile'])->name('profile.update');

    Route::get('/change-password', [UserController::class, 'showChangePasswordForm'])->name('user.change-password');
    Route::post('/change-password', [UserController::class, 'changePassword'])->name('password.change');


    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy'])->name('notifications.destroy');

    // Conversations & Messages
    Route::resource('conversations', ConversationController::class);
    Route::post('/conversations/{id}/add-participants', [ConversationController::class, 'addParticipants'])->name('conversations.add-participants');
    Route::post('/conversations/{id}/leave', [ConversationController::class, 'leave'])->name('conversations.leave');
    Route::post('/conversations/{id}/archive', [ConversationController::class, 'archive'])->name('conversations.archive');

    Route::get('/conversations/{conversationId}/messages', [MessageController::class, 'index'])->name('messages.index');
    Route::post('/conversations/{conversationId}/messages', [MessageController::class, 'send'])->name('messages.send');
    Route::post('/conversations/{conversationId}/messages/read', [MessageController::class, 'markRead'])->name('messages.read');
    Route::post('/conversations/{conversationId}/messages/upload', [MessageController::class, 'uploadAttachment'])->name('messages.upload');
    Route::delete('/conversations/{conversationId}/messages/{messageId}', [MessageController::class, 'destroy'])->name('messages.destroy');
    Route::get('/conversations/{conversationId}/messages/latest', [MessageController::class, 'getLatest'])->name('messages.latest');
    Route::get('/messages/unread-count', [MessageController::class, 'getUnreadCount'])->name('messages.unread-count');

    // Video Calls
    Route::get('/video-calls', [VideoCallController::class, 'index'])->name('video-calls.index');
    Route::post('/video-calls/initiate', [VideoCallController::class, 'initiate'])->name('video-calls.initiate');
    Route::post('/video-calls/{id}/join', [VideoCallController::class, 'join'])->name('video-calls.join');
    Route::get('/video-calls/{id}/room', [VideoCallController::class, 'room'])->name('video-calls.room');
    Route::post('/video-calls/{id}/end', [VideoCallController::class, 'end'])->name('video-calls.end');
    Route::post('/video-calls/{id}/decline', [VideoCallController::class, 'decline'])->name('video-calls.decline');
    Route::get('/video-calls/conversation/{conversationId}/active', [VideoCallController::class, 'getActiveCall'])->name('video-calls.active');
    Route::get('/video-calls/conversation/{conversationId}/history', [VideoCallController::class, 'conversationHistory'])->name('video-calls.history');

    // Reviews
    Route::get('/reviews/create', [ReviewController::class, 'create'])->name('reviews.create');
    Route::post('/reviews', [ReviewController::class, 'store'])->name('reviews.store');
    Route::get('/reviews/{id}', [ReviewController::class, 'show'])->name('reviews.show');
    Route::post('/reviews/{id}/helpful', [ReviewController::class, 'markHelpful'])->name('reviews.helpful');
});

/*
|--------------------------------------------------------------------------
| Volunteer Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'volunteer'])->prefix('volunteer')->name('volunteer.')->group(function () {

    // Volunteer Dashboard
    Route::get('/dashboard', [DashboardController::class, 'volunteerDashboard'])->name('dashboard');

    // Volunteer Profile
    Route::get('/profile', [VolunteerProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [VolunteerProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [VolunteerProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/skills', [VolunteerProfileController::class, 'updateSkills'])->name('profile.skills');
    Route::put('/profile/availability', [VolunteerProfileController::class, 'updateAvailability'])->name('profile.availability');

    // Applications
    Route::get('/applications', [ApplicationController::class, 'myApplications'])->name('applications.my');
    Route::get('/applications/create', [ApplicationController::class, 'create'])->name('applications.create');
    Route::post('/applications', [ApplicationController::class, 'store'])->name('applications.store');
    Route::get('/applications/{id}', [ApplicationController::class, 'show'])->name('applications.show');
    Route::post('/applications/{id}/withdraw', [ApplicationController::class, 'withdraw'])->name('applications.withdraw');

    // Volunteer Activities
    Route::get('/activities', [VolunteerActivityController::class, 'index'])->name('activities.index');
    Route::get('/activities/create', [VolunteerActivityController::class, 'create'])->name('activities.create');
    Route::post('/activities', [VolunteerActivityController::class, 'store'])->name('activities.store');
    Route::get('/activities/{id}', [VolunteerActivityController::class, 'show'])->name('activities.show');
    Route::post('/activities/{id}/dispute', [VolunteerActivityController::class, 'dispute'])->name('activities.dispute');
    Route::get('/activities/export', [VolunteerActivityController::class, 'export'])->name('activities.export');

    // Favorites
    Route::get('/favorites', [FavoriteController::class, 'index'])->name('favorites.index');
    Route::post('/favorites/toggle', [FavoriteController::class, 'toggle'])->name('favorites.toggle');
    Route::put('/favorites/{id}/notes', [FavoriteController::class, 'updateNotes'])->name('favorites.notes');
    Route::delete('/favorites/{id}', [FavoriteController::class, 'destroy'])->name('favorites.destroy');
    Route::post('/favorites/bulk-destroy', [FavoriteController::class, 'bulkDestroy'])->name('favorites.bulk-destroy');
    Route::get('/favorites/export', [FavoriteController::class, 'export'])->name('favorites.export');

    // Analytics
    Route::get('/analytics', [AnalyticsController::class, 'volunteerDashboard'])->name('analytics');
});

/*
|--------------------------------------------------------------------------
| Organization Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'organization'])->prefix('organization')->name('organization.')->group(function () {

    // Organization Dashboard
    Route::get('/dashboard', [DashboardController::class, 'organizationDashboard'])->name('dashboard');

    // Organization Profile
    Route::get('/profile', [OrganizationController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [OrganizationController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [OrganizationController::class, 'update'])->name('profile.update');
    Route::post('/profile/documents', [OrganizationController::class, 'uploadDocuments'])->name('profile.documents');

    // Opportunities Management
    Route::get('/opportunities', [VolunteerOpportunityController::class, 'myOpportunities'])->name('opportunities.my');
    Route::get('/opportunities/create', [VolunteerOpportunityController::class, 'create'])->name('opportunities.create');
    Route::post('/opportunities', [VolunteerOpportunityController::class, 'store'])->name('opportunities.store');
    Route::get('/opportunities/{id}/edit', [VolunteerOpportunityController::class, 'edit'])->name('opportunities.edit');
    Route::put('/opportunities/{id}', [VolunteerOpportunityController::class, 'update'])->name('opportunities.update');
    Route::delete('/opportunities/{id}', [VolunteerOpportunityController::class, 'destroy'])->name('opportunities.destroy');
    Route::post('/opportunities/{id}/change-status', [VolunteerOpportunityController::class, 'changeStatus'])->name('opportunities.change-status');
    Route::get('/opportunities/export', [VolunteerOpportunityController::class, 'export'])->name('opportunities.export');

    // Applications Management
    Route::get('/applications', [ApplicationController::class, 'receivedApplications'])->name('applications.received');
    Route::get('/applications/{id}', [ApplicationController::class, 'show'])->name('applications.show');
    Route::patch('/applications/{id}/status', [ApplicationController::class, 'updateStatus'])->name('applications.update-status');
    Route::post('/applications/{id}/schedule-interview', [ApplicationController::class, 'scheduleInterview'])->name('applications.schedule-interview');
    Route::post('/applications/bulk-action', [ApplicationController::class, 'bulkAction'])->name('applications.bulk-action');
    Route::get('/applications/export', [ApplicationController::class, 'exportCSV'])->name('applications.export');

    // Volunteer Activities Verification
    Route::get('/activities', [VolunteerActivityController::class, 'index'])->name('activities.index');
    Route::get('/activities/{id}', [VolunteerActivityController::class, 'show'])->name('activities.show');
    Route::post('/activities/{id}/verify', [VolunteerActivityController::class, 'verify'])->name('activities.verify');
    Route::post('/activities/bulk-verify', [VolunteerActivityController::class, 'bulkVerify'])->name('activities.bulk-verify');

    // Volunteers Management
    Route::get('/volunteers', [OrganizationController::class, 'volunteers'])->name('volunteers.index');
    Route::get('/volunteers/{id}', [OrganizationController::class, 'volunteerDetail'])->name('volunteers.show');

    // Analytics
    Route::get('/analytics', [AnalyticsController::class, 'organizationDashboard'])->name('analytics');
});

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {

    // Admin Dashboard
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

    // Users Management
    Route::get('/users', [AdminController::class, 'users'])->name('users.index');
    Route::get('/users/create', [AdminController::class, 'createUser'])->name('users.create');
    Route::post('/users', [AdminController::class, 'storeUser'])->name('users.store');
    Route::get('/users/export', [AdminController::class, 'exportUsers'])->name('users.export');

    Route::get('/users/{id}', [AdminController::class, 'showUser'])->name('users.show');
    Route::get('/users/{id}/edit', [AdminController::class, 'editUser'])->name('users.edit');
    Route::put('/users/{id}', [AdminController::class, 'updateUser'])->name('users.update');
    Route::delete('/users/{id}', [AdminController::class, 'deleteUser'])->name('users.destroy');
    Route::post('/users/{id}/suspend', [AdminController::class, 'suspendUser'])->name('users.suspend');
    Route::post('/users/{id}/activate', [AdminController::class, 'activateUser'])->name('users.activate');
    Route::post('/users/bulk-action', [AdminController::class, 'userBulkAction'])->name('users.bulk-action');

    // Organizations Management
    Route::get('/organizations', [AdminController::class, 'organizations'])->name('organizations.index');
    Route::get('/organizations/export-page', function () {
        return view('admin.organizations.export');
    })->name('organizations.export-page');
    Route::get('/organizations/export', [AdminController::class, 'organizationsExport'])->name('organizations.export');

    Route::get('/organizations/{id}', [AdminController::class, 'showOrganization'])->name('organizations.show');
    Route::post('/organizations/{id}/verify', [AdminController::class, 'verifyOrganization'])->name('organizations.verify');
    Route::post('/organizations/{id}/reject', [AdminController::class, 'rejectOrganization'])->name('organizations.reject');
    Route::delete('/organizations/{id}', [AdminController::class, 'deleteOrganization'])->name('organizations.destroy');
    // Opportunities Management
    Route::get('/opportunities', [AdminController::class, 'opportunities'])->name('opportunities.index');
    Route::get('/opportunities/{id}', [AdminController::class, 'showOpportunity'])->name('opportunities.show');
    Route::post('/opportunities/{id}/change-status', [AdminController::class, 'changeOpportunityStatus'])->name('opportunities.change-status');
    Route::delete('/opportunities/{id}', [AdminController::class, 'deleteOpportunity'])->name('opportunities.destroy');
    Route::get('/opportunities/export', [AdminController::class, 'exportOpportunities'])->name('opportunities.export');

    // Applications Monitoring
    Route::get('/applications', [AdminController::class, 'index'])->name('applications.index');
    Route::get('/applications-export', [AdminController::class, 'exportApplications'])->name('applications.export');
    Route::get('/applications/{id}', [AdminController::class, 'showApplication'])->name('applications.show');

    // Activities Monitoring
    Route::get('/activities', [AdminController::class, 'activities'])->name('activities.index');
    Route::get('/activities/{id}', [AdminController::class, 'showActivity'])->name('activities.show');
    Route::get('/activities/disputes', [AdminController::class, 'disputedActivities'])->name('activities.disputes');
    Route::post('/activities/{id}/resolve-dispute', [AdminController::class, 'resolveDispute'])->name('activities.resolve-dispute');
    
    // Reviews Management
    Route::get('/reviews', [ReviewController::class, 'pending'])->name('reviews.index'); // Mặc định là pending
    Route::get('/reviews/all', [AdminController::class, 'allReviews'])->name('reviews.all'); // Tất cả reviews
    Route::get('/reviews/pending', [ReviewController::class, 'pending'])->name('reviews.pending'); // Có thể xóa nếu trùng với index
    Route::post('/reviews/{id}/approve', [ReviewController::class, 'approve'])->name('reviews.approve');
    Route::post('/reviews/{id}/reject', [ReviewController::class, 'reject'])->name('reviews.reject');
    Route::post('/reviews/bulk-approve', [ReviewController::class, 'bulkApprove'])->name('reviews.bulk-approve');

    // Categories Management
    Route::get('/categories', [AdminController::class, 'categories'])->name('categories.index');
    Route::get('/categories/create', function () {
        return view('admin.categories.create');
    })->name('categories.create');
    Route::post('/categories', [AdminController::class, 'categoriesStore'])->name('categories.store');
    Route::get('/categories/{id}/edit', function ($id) {
        $category = \App\Models\Category::withCount('opportunities')->findOrFail($id);
        return view('admin.categories.edit', compact('category'));
    })->name('categories.edit');
    Route::put('/categories/{id}', [AdminController::class, 'categoriesUpdate'])->name('categories.update');
    Route::delete('/categories/{id}', [AdminController::class, 'categoriesDestroy'])->name('categories.destroy');
    Route::post('/categories/{id}/toggle', [AdminController::class, 'categoriesToggle'])->name('categories.toggle');

    // Analytics & Reports
    Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics.index');
    Route::get('/analytics/chart-data', [AnalyticsController::class, 'getChartData'])->name('analytics.chart-data');
    Route::get('/analytics/impact', [AnalyticsController::class, 'impactReport'])->name('analytics.impact');
    Route::post('/analytics/custom-report', [AnalyticsController::class, 'customReport'])->name('analytics.custom-report');
    Route::post('/analytics/export', [AnalyticsController::class, 'exportReport'])->name('analytics.export');
    Route::post('/analytics/clear-cache', [AnalyticsController::class, 'clearCache'])->name('analytics.clear-cache');

    // Reports
    Route::get('/analytics/reports', [AnalyticsController::class, 'reports'])->name('analytics.reports');

    // reports
    // Route::get('/reports', [AdminController::class, 'reports'])->name('reports.index');

    Route::get('/settings', [AdminController::class, 'settings'])->name('settings.index');
    Route::put('/settings', [AdminController::class, 'updateSettings'])->name('settings.update');


    Route::prefix('emails')->name('emails.')->group(function () {
        Route::post('/send', [AdminEmailController::class, 'sendEmail'])->name('send');
        Route::get('/history', [AdminEmailController::class, 'history'])->name('history');
        Route::get('/templates', [AdminEmailController::class, 'getTemplates'])->name('templates');
    });

    // Organization Verification Routes
    Route::post('/organizations/{id}/approve', [AdminController::class, 'approveOrganization'])->name('organizations.approve');
    Route::post('/organizations/{id}/reject', [AdminController::class, 'rejectOrganization'])->name('organizations.reject');

    // User Management Routes
    Route::post('/users/{id}/activate', [AdminController::class, 'activateUser'])->name('users.activate');
    Route::post('/users/{id}/deactivate', [AdminController::class, 'deactivateUser'])->name('users.deactivate');

    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount'])->name('notifications.unread-count');

    // System Settings
    Route::get('/settings', [AdminController::class, 'settings'])->name('settings.index');
    Route::put('/settings', [AdminController::class, 'updateSettings'])->name('settings.update');
});
/*
|--------------------------------------------------------------------------
| Shared Routes (Applications - both volunteer and organization can access)
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    Route::get('/applications/{id}', [ApplicationController::class, 'show'])->name('applications.show');
});

/*
|--------------------------------------------------------------------------
| Shared Routes (Activities - both volunteer and organization can access)
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    Route::get('/volunteer-activities', [VolunteerActivityController::class, 'index'])->name('volunteer-activities.index');
    Route::get('/volunteer-activities/create', [VolunteerActivityController::class, 'create'])->name('volunteer-activities.create');
    Route::post('/volunteer-activities', [VolunteerActivityController::class, 'store'])->name('volunteer-activities.store');
    Route::get('/volunteer-activities/{id}', [VolunteerActivityController::class, 'show'])->name('volunteer-activities.show');
    Route::post('/volunteer-activities/{id}/verify', [VolunteerActivityController::class, 'verify'])->name('volunteer-activities.verify');
    Route::get('/volunteer-activities/export', [VolunteerActivityController::class, 'export'])->name('volunteer-activities.export');
});

/*
|--------------------------------------------------------------------------
| API-like Routes (for AJAX calls)
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->prefix('api')->name('api.')->group(function () {

    // Favorites API
    Route::post('/favorites/toggle', [FavoriteController::class, 'toggle'])->name('favorites.toggle');
    Route::get('/favorites/check/{opportunityId}', [FavoriteController::class, 'check'])->name('favorites.check');
    Route::get('/favorites/count', [FavoriteController::class, 'count'])->name('favorites.count');

    // Messages API
    Route::get('/messages/unread-count', [MessageController::class, 'getUnreadCount'])->name('messages.unread-count');
    Route::post('/messages/typing', [MessageController::class, 'typing'])->name('messages.typing');
    Route::get('/conversations/{conversationId}/messages/search', [MessageController::class, 'search'])->name('messages.search');

    // Notifications API
    Route::get('/notifications/unread-count', [NotificationController::class, 'getUnreadCount'])->name('notifications.unread-count');

    // Search API
    Route::get('/search/suggestions', [SearchController::class, 'suggestions'])->name('search.suggestions');
    Route::get('/search/popular', [SearchController::class, 'popularSearches'])->name('search.popular');
    Route::get('/search/trending', [SearchController::class, 'trendingOpportunities'])->name('search.trending');

    // Video Calls API
    Route::get('/video-calls/stats', [VideoCallController::class, 'stats'])->name('video-calls.stats');
});

/*
|--------------------------------------------------------------------------
| Fallback Route (404 Page)
|--------------------------------------------------------------------------
*/

Route::fallback(function () {
    return view('errors.404');
});
