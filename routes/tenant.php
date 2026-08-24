<?php

use App\Http\Controllers\CalendarController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\WorkflowController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {

    // ---- Shared content workspace (internal team + clients) --------------
    Route::middleware('role:company_admin,company_manager,company_approver,client')->group(function () {
        Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar.index');
        Route::get('/calendar/events', [CalendarController::class, 'events'])->name('calendar.events');

        Route::get('/posts', [PostController::class, 'index'])->name('posts.index');
        Route::get('/posts/{post}/preview', [PostController::class, 'preview'])
            ->whereNumber('post')->name('posts.preview');

        // Approval loop (client)
        Route::post('/posts/{post}/approve', [WorkflowController::class, 'clientApprove'])
            ->whereNumber('post')->name('posts.approve');
        Route::post('/posts/{post}/reject', [WorkflowController::class, 'clientReject'])
            ->whereNumber('post')->name('posts.reject');

        // Internal review loop (approver)
        Route::post('/posts/{post}/review', [WorkflowController::class, 'internalDecision'])
            ->whereNumber('post')->name('posts.review');
    });

    // ---- Drafting (manager + company_admin) ------------------------------
    Route::middleware('role:company_admin,company_manager')->group(function () {
        Route::resource('posts', PostController::class)->except(['index', 'show']);

        Route::post('/media', [MediaController::class, 'store'])->name('media.store');
        Route::delete('/posts/{post}/media/{media}', [MediaController::class, 'destroy'])
            ->whereNumber(['post', 'media'])->name('media.destroy');

        Route::post('/posts/{post}/submit-review', [WorkflowController::class, 'submit'])
            ->whereNumber('post')->name('posts.submit-review');
    });
});
