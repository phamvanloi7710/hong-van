<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hongvan_roles', function (Blueprint $table): void {
            $table->id();
            $table->char('public_id', 26)->unique();
            $table->string('name', 120);
            $table->string('slug', 100)->unique();
            $table->text('description')->nullable();
            $table->boolean('is_system')->default(false)->index();
            $table->timestamps();
        });

        Schema::create('hongvan_permissions', function (Blueprint $table): void {
            $table->id();
            $table->char('public_id', 26)->unique();
            $table->string('key', 160)->unique();
            $table->string('module', 100)->index();
            $table->string('action', 40)->index();
            $table->string('name', 160);
            $table->text('description')->nullable();
            $table->boolean('is_system')->default(false)->index();
            $table->timestamps();

            $table->unique(['module', 'action']);
        });

        Schema::create('hongvan_role_user', function (Blueprint $table): void {
            $table->foreignId('role_id')->constrained('hongvan_roles')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('hongvan_users')->cascadeOnDelete();
            $table->foreignId('assigned_by')->nullable()->constrained('hongvan_users')->nullOnDelete();
            $table->timestamps();

            $table->primary(['role_id', 'user_id']);
            $table->index(['user_id', 'role_id']);
        });

        Schema::create('hongvan_permission_role', function (Blueprint $table): void {
            $table->foreignId('permission_id')->constrained('hongvan_permissions')->cascadeOnDelete();
            $table->foreignId('role_id')->constrained('hongvan_roles')->cascadeOnDelete();
            $table->foreignId('granted_by')->nullable()->constrained('hongvan_users')->nullOnDelete();
            $table->timestamps();

            $table->primary(['permission_id', 'role_id']);
            $table->index(['role_id', 'permission_id']);
        });

        Schema::create('hongvan_user_permission_overrides', function (Blueprint $table): void {
            $table->foreignId('user_id')->constrained('hongvan_users')->cascadeOnDelete();
            $table->foreignId('permission_id')->constrained('hongvan_permissions')->cascadeOnDelete();
            $table->boolean('is_allowed');
            $table->foreignId('assigned_by')->nullable()->constrained('hongvan_users')->nullOnDelete();
            $table->timestamps();

            $table->primary(['user_id', 'permission_id']);
            $table->index(['permission_id', 'is_allowed']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hongvan_user_permission_overrides');
        Schema::dropIfExists('hongvan_permission_role');
        Schema::dropIfExists('hongvan_role_user');
        Schema::dropIfExists('hongvan_permissions');
        Schema::dropIfExists('hongvan_roles');
    }
};
