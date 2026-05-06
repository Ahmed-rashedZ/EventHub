<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Performance indexes migration.
 *
 * Adds indexes to columns that are frequently used in WHERE, JOIN,
 * and ORDER BY clauses across the application's controllers.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── Events table ─────────────────────────────────────────────
        Schema::table('events', function (Blueprint $table) {
            $table->index('status');
            $table->index('created_by');
            $table->index('start_time');
            $table->index('end_time');
            // venue_id already has FK index from migration
        });

        // ── Tickets table ────────────────────────────────────────────
        Schema::table('tickets', function (Blueprint $table) {
            $table->index('status');
            // event_id and user_id already have FK indexes
        });

        // ── Ratings table ────────────────────────────────────────────
        Schema::table('ratings', function (Blueprint $table) {
            $table->index('event_id');
            // user_id+event_id unique index already exists
        });

        // ── Sponsorship requests table ───────────────────────────────
        Schema::table('sponsorship_requests', function (Blueprint $table) {
            $table->index('event_manager_id');
            $table->index('status');
            $table->index(['event_id', 'sponsor_id']);
        });

        // ── Notifications table (Laravel polymorphic) ────────────────
        Schema::table('notifications', function (Blueprint $table) {
            $table->index(['notifiable_id', 'notifiable_type', 'read_at']);
        });

        // ── Users table ──────────────────────────────────────────────
        Schema::table('users', function (Blueprint $table) {
            $table->index('role');
            $table->index('verification_status');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['created_by']);
            $table->dropIndex(['start_time']);
            $table->dropIndex(['end_time']);
        });

        Schema::table('tickets', function (Blueprint $table) {
            $table->dropIndex(['status']);
        });

        Schema::table('ratings', function (Blueprint $table) {
            $table->dropIndex(['event_id']);
        });

        Schema::table('sponsorship_requests', function (Blueprint $table) {
            $table->dropIndex(['event_manager_id']);
            $table->dropIndex(['status']);
            $table->dropIndex(['event_id', 'sponsor_id']);
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex(['notifiable_id', 'notifiable_type', 'read_at']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['role']);
            $table->dropIndex(['verification_status']);
            $table->dropIndex(['is_active']);
        });
    }
};
