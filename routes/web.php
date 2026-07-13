<?php

use App\Http\Controllers\CotisationController;
use App\Http\Controllers\CourrierController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\GroupeController;
use App\Http\Controllers\MembreController;
use App\Http\Controllers\SaisonController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// ── Dashboard ─────────────────────────────────────────────────────────────────
Route::get('/', function () {
    $saison = \App\Models\Saison::active();
    return Inertia::render('Dashboard', [
        'saison' => $saison,
        'stats'  => $saison ? $saison->stats_cotisations : null,
    ]);
})->name('dashboard');

// ── Membres ───────────────────────────────────────────────────────────────────
Route::resource('membres', MembreController::class);
Route::get('membres/{membre}/famille', [MembreController::class, 'famille'])->name('membres.famille');

// ── Cotisations ───────────────────────────────────────────────────────────────
Route::resource('cotisations', CotisationController::class)->except(['show']);
Route::post('cotisations/{cotisation}/envoyer', [CotisationController::class, 'envoyer'])->name('cotisations.envoyer');
Route::post('cotisations/{cotisation}/marquer-paye', [CotisationController::class, 'marquerPaye'])->name('cotisations.marquer-paye');
Route::post('cotisations/envoyer-masse', [CotisationController::class, 'envoyerMasse'])->name('cotisations.envoyer-masse');
Route::get('cotisations/{cotisation}/pdf', [CotisationController::class, 'pdf'])->name('cotisations.pdf');

// ── Groupes ───────────────────────────────────────────────────────────────────
Route::resource('groupes', GroupeController::class)->except(['show']);
Route::post('groupes/{groupe}/membres/{membre}', [GroupeController::class, 'ajouterMembre'])->name('groupes.ajouter-membre');
Route::delete('groupes/{groupe}/membres/{membre}', [GroupeController::class, 'retirerMembre'])->name('groupes.retirer-membre');

// ── Courses ───────────────────────────────────────────────────────────────────
Route::resource('courses', CourseController::class);
Route::get('courses/{course}/chronos', [CourseController::class, 'chronos'])->name('courses.chronos');
Route::post('courses/{course}/chronos', [CourseController::class, 'saisirChrono'])->name('courses.saisir-chrono');
Route::get('courses/{course}/classement', [CourseController::class, 'classement'])->name('courses.classement');
Route::get('courses/{course}/classement/pdf', [CourseController::class, 'classementPdf'])->name('courses.classement-pdf');
Route::post('courses/{course}/participants', [CourseController::class, 'ajouterParticipant'])->name('courses.ajouter-participant');
Route::delete('courses/{course}/participants/{participant}', [CourseController::class, 'retirerParticipant'])->name('courses.retirer-participant');

// ── Catégories (courses) ──────────────────────────────────────────────────────
Route::get('categories', [\App\Http\Controllers\CategorieController::class, 'index'])->name('categories.index');
Route::post('categories', [\App\Http\Controllers\CategorieController::class, 'store'])->name('categories.store');
Route::put('categories/{categorie}', [\App\Http\Controllers\CategorieController::class, 'update'])->name('categories.update');
Route::delete('categories/{categorie}', [\App\Http\Controllers\CategorieController::class, 'destroy'])->name('categories.destroy');

// ── Courriers ─────────────────────────────────────────────────────────────────
Route::resource('courriers', CourrierController::class);
Route::post('courriers/{courrier}/envoyer', [CourrierController::class, 'envoyer'])->name('courriers.envoyer');
Route::get('courriers/{courrier}/apercu', [CourrierController::class, 'apercu'])->name('courriers.apercu');
Route::get('courriers/{courrier}/pdf', [CourrierController::class, 'pdf'])->name('courriers.pdf');

// ── Saisons / Paramètres ──────────────────────────────────────────────────────
Route::resource('saisons', SaisonController::class)->except(['show']);
Route::post('saisons/{saison}/activer', [SaisonController::class, 'activer'])->name('saisons.activer');
Route::post('saisons/{saison}/archiver', [SaisonController::class, 'archiver'])->name('saisons.archiver');
