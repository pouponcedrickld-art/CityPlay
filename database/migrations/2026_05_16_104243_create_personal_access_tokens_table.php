<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration pour créer la table des tokens d'accès personnels (Sanctum)
 * 
 * Cette table est utilisée par Laravel Sanctum pour stocker les tokens
 * d'authentification API des utilisateurs.
 */
return new class extends Migration {
    /**
     * Crée la table personal_access_tokens
     * 
     * Stocke les tokens Bearer utilisés pour l'authentification API.
     * Chaque token est lié à un utilisateur (tokenable) et a une date d'expiration.
     */
    public function up(): void
    {
        Schema::create('personal_access_tokens', function (Blueprint $table) {
            // Clé primaire auto-incrémentée
            $table->id();

            // Relation polymorphe vers le modèle propriétaire du token
            // Crée deux colonnes : tokenable_id (bigint) et tokenable_type (varchar)
            // Utilisé pour lier le token à un User, Admin, etc.
            $table->morphs('tokenable');

            // Nom du token (ex: "demo", "mobile-app", "postman")
            // Permet d'identifier l'origine du token
            $table->string('name');

            // Le token hashé (64 caractères)
            // C'est cette valeur qui est envoyée dans le header Authorization: Bearer {token}
            // Unique pour éviter les collisions
            $table->string('token', 64)->unique();

            // Capacités/permissions du token (JSON)
            // Ex: ["*"] = tous les droits, ["read"] = lecture seule
            // Null = tous les droits par défaut
            $table->text('abilities')->nullable();

            // Date de dernière utilisation
            // Permet de nettoyer les tokens inactifs
            $table->timestamp('last_used_at')->nullable();

            // Date d'expiration du token
            // Après cette date, le token est invalide
            // Null = jamais expiré
            $table->timestamp('expires_at')->nullable();

            // Timestamps Laravel (created_at, updated_at)
            $table->timestamps();
        });
    }

    /**
     * Supprime la table personal_access_tokens
     * 
     * Appelée lors d'un rollback de migration.
     */
    public function down(): void
    {
        Schema::dropIfExists('personal_access_tokens');
    }
};