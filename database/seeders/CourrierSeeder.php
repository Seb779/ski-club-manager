<?php

namespace Database\Seeders;

use App\Models\Courrier;
use App\Models\Membre;
use App\Models\Saison;
use Illuminate\Database\Seeder;

class CourrierSeeder extends Seeder
{
    public function run(): void
    {
        $saison = Saison::where('active', true)->first();
        $tous   = Membre::where('actif', true)->get();

        // ── Courrier 1 : Convocation AG (envoyé) ──────────────────────────────
        $ag = Courrier::create([
            'saison_id' => $saison->id,
            'titre'     => 'Convocation AG 2026',
            'corps'     => <<<HTML
<p>Chères membres, chers membres,</p>
<p>Nous avons le plaisir de vous convoquer à l'Assemblée Générale du Ski-Club qui se tiendra le <strong>vendredi 14 novembre 2025 à 20h00</strong> à la Salle communale de Sion.</p>
<h3>Ordre du jour</h3>
<ol>
  <li>Accueil et ouverture</li>
  <li>Procès-verbal de l'AG précédente</li>
  <li>Rapport du président</li>
  <li>Rapport financier</li>
  <li>Activités de la saison 2025-2026</li>
  <li>Divers</li>
</ol>
<p>Nous comptons sur votre présence et vous adressons nos cordiales salutations.</p>
<p><em>Le Comité du Ski-Club</em></p>
HTML,
            'statut'    => 'envoye',
            'envoye_le' => now()->subMonths(3),
        ]);

        foreach ($tous as $m) {
            $ag->membres()->attach($m->id, [
                'mode_envoi' => $m->preference_envoi,
                'statut'     => $m->preference_envoi === 'email' ? 'envoye' : 'imprime',
                'traite_le'  => now()->subMonths(3),
            ]);
        }

        // ── Courrier 2 : Programme saison (envoyé) ────────────────────────────
        $prog = Courrier::create([
            'saison_id' => $saison->id,
            'titre'     => 'Programme saison hiver 2025-2026',
            'corps'     => <<<HTML
<p>Chères membres, chers membres,</p>
<p>Voici le programme de la saison 2025-2026 :</p>
<ul>
  <li><strong>Décembre 2025</strong> : Début des cours — tous les samedis matin</li>
  <li><strong>15 février 2026</strong> : Concours interne (piste des Chamois)</li>
  <li><strong>Mars 2026</strong> : Sorties ski libres</li>
  <li><strong>Avril 2026</strong> : Clôture de saison et remise des prix</li>
</ul>
<p>Bon début de saison à tous !</p>
HTML,
            'statut'    => 'envoye',
            'envoye_le' => now()->subMonths(2),
        ]);

        foreach ($tous as $m) {
            $prog->membres()->attach($m->id, [
                'mode_envoi' => $m->preference_envoi,
                'statut'     => $m->preference_envoi === 'email' ? 'envoye' : 'imprime',
                'traite_le'  => now()->subMonths(2),
            ]);
        }

        // ── Courrier 3 : Résultats concours (brouillon) ───────────────────────
        Courrier::create([
            'saison_id' => $saison->id,
            'titre'     => 'Résultats concours interne 2026',
            'corps'     => <<<HTML
<p>Chères membres, chers membres,</p>
<p>Le concours interne du 15 février 2026 s'est parfaitement déroulé. Voici les résultats :</p>
<p>[Résultats à compléter après la course]</p>
<p>Nous félicitons tous les participants pour leur engagement et leur esprit sportif.</p>
<p>La remise des prix aura lieu lors de la clôture de saison en avril.</p>
HTML,
            'statut' => 'brouillon',
        ]);
    }
}
