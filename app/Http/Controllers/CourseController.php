<?php

namespace App\Http\Controllers;

use App\Models\Categorie;
use App\Models\Chrono;
use App\Models\Course;
use App\Models\Membre;
use App\Models\Participant;
use App\Models\Saison;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CourseController extends Controller
{
    public function index(): Response
    {
        $saison = Saison::active();

        $courses = Course::with(['saison', 'participants'])
            ->orderByDesc('date')
            ->paginate(20);

        return Inertia::render('Courses/Index', [
            'courses' => $courses,
            'saison'  => $saison,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Courses/Form', [
            'course'  => null,
            'saisons' => Saison::orderByDesc('annee_debut')->get(),
            'mode'    => 'create',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'saison_id'  => 'required|exists:saisons,id',
            'nom'        => 'required|string|max:200',
            'date'       => 'nullable|date',
            'lieu'       => 'nullable|string|max:200',
            'nb_manches' => 'integer|min:1|max:4',
            'notes'      => 'nullable|string',
        ]);

        $course = Course::create($data);

        return redirect()->route('courses.show', $course)
            ->with('success', "Course \"{$course->nom}\" créée.");
    }

    public function show(Course $course): Response
    {
        $course->load([
            'saison',
            'participants.membre',
            'participants.categorie',
            'participants.chronos',
        ]);

        $categories = $course->saison->categories()->orderBy('ordre')->get();

        return Inertia::render('Courses/Show', [
            'course'        => $course,
            'categories'    => $categories,
            'membres_dispo' => Membre::actifs()
                ->whereNotIn('id', $course->participants->pluck('membre_id'))
                ->orderBy('nom')
                ->get(['id', 'prenom', 'nom', 'date_naissance']),
        ]);
    }

    public function edit(Course $course): Response
    {
        return Inertia::render('Courses/Form', [
            'course'  => $course,
            'saisons' => Saison::orderByDesc('annee_debut')->get(),
            'mode'    => 'edit',
        ]);
    }

    public function update(Request $request, Course $course): RedirectResponse
    {
        $data = $request->validate([
            'nom'        => 'required|string|max:200',
            'date'       => 'nullable|date',
            'lieu'       => 'nullable|string|max:200',
            'nb_manches' => 'integer|min:1|max:4',
            'statut'     => 'required|in:preparation,actif,termine,archive',
            'notes'      => 'nullable|string',
        ]);

        $course->update($data);

        return back()->with('success', 'Course mise à jour.');
    }

    public function destroy(Course $course): RedirectResponse
    {
        $nom = $course->nom;
        $course->delete();

        return redirect()->route('courses.index')
            ->with('success', "Course \"{$nom}\" supprimée.");
    }

    /** Vue saisie des chronos */
    public function chronos(Course $course): Response
    {
        $course->load([
            'saison',
            'participants.membre',
            'participants.categorie',
            'participants.chronos',
        ]);

        return Inertia::render('Courses/Chronos', [
            'course' => $course,
        ]);
    }

    /** Saisir ou mettre à jour un chrono */
    public function saisirChrono(Request $request, Course $course): RedirectResponse
    {
        $data = $request->validate([
            'identifiant' => 'required|string',  // dossard ou nom
            'manche'      => 'required|integer|min:1',
            'temps'       => 'required|string',  // format "1:23.45"
            'disqualifie' => 'boolean',
            'raison_dq'   => 'nullable|string',
        ]);

        // Recherche du participant par dossard ou nom
        $participant = null;
        if (is_numeric($data['identifiant'])) {
            $participant = $course->participants()
                ->where('dossard', (int) $data['identifiant'])
                ->first();
        } else {
            $participant = $course->participants()
                ->whereHas('membre', fn($q) =>
                    $q->where('nom', 'like', "%{$data['identifiant']}%")
                      ->orWhere('prenom', 'like', "%{$data['identifiant']}%")
                )
                ->first();
        }

        if (! $participant) {
            return back()->withErrors(['identifiant' => 'Participant non trouvé.']);
        }

        $tempsMs = $data['disqualifie'] ?? false ? null : Course::parseTemps($data['temps']);

        Chrono::updateOrCreate(
            ['participant_id' => $participant->id, 'manche' => $data['manche']],
            [
                'temps_ms'    => $tempsMs,
                'disqualifie' => $data['disqualifie'] ?? false,
                'raison_dq'   => $data['raison_dq'] ?? null,
            ]
        );

        // Met à jour le statut participant
        $participant->update(['statut' => 'classe']);

        return back()->with('success', "Chrono enregistré pour dossard {$participant->dossard}.");
    }

    /** Classements par catégorie */
    public function classement(Course $course): Response
    {
        $course->load('saison');
        $categories = $course->saison->categories()->orderBy('ordre')->get();

        $classements = $categories->mapWithKeys(fn($cat) => [
            $cat->id => $course->classement($cat->id),
        ]);

        return Inertia::render('Courses/Classement', [
            'course'      => $course,
            'categories'  => $categories,
            'classements' => $classements,
        ]);
    }

    public function classementPdf(Course $course)
    {
        $course->load('saison');
        $categories  = $course->saison->categories()->orderBy('ordre')->get();
        $classements = $categories->mapWithKeys(fn($cat) => [
            $cat->id => $course->classement($cat->id),
        ]);

        $pdf = Pdf::loadView('pdf.classement', compact('course', 'categories', 'classements'))
            ->setPaper('a4');

        return $pdf->download("classement-{$course->nom}.pdf");
    }

    public function ajouterParticipant(Request $request, Course $course): RedirectResponse
    {
        $data = $request->validate([
            'membre_id'   => 'required|exists:membres,id',
            'dossard'     => 'required|integer|min:1',
            'categorie_id'=> 'nullable|exists:categories,id',
        ]);

        // Attribution automatique de catégorie si non fournie
        if (! $data['categorie_id']) {
            $membre = Membre::find($data['membre_id']);
            $cat    = $membre->getCategorieForSaison($course->saison);
            $data['categorie_id'] = $cat?->id;
        }

        Participant::create([...$data, 'course_id' => $course->id]);

        return back()->with('success', 'Participant ajouté.');
    }

    public function retirerParticipant(Course $course, Participant $participant): RedirectResponse
    {
        $participant->delete();

        return back()->with('success', 'Participant retiré.');
    }
}
