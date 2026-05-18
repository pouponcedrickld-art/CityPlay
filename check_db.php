<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Environnement;

$env = Environnement::where('nom', 'Les Trésors de Cotonou')->first();
if ($env) {
    echo "Env: " . $env->nom . " (ID: " . $env->id . ", Actif: " . ($env->actif ? 'Oui' : 'Non') . ")\n";
    $lieux = $env->lieux;
    echo "Lieux total: " . $lieux->count() . "\n";
    foreach ($lieux as $l) {
        $enigmesActives = $l->enigmes()->where('actif', true)->count();
        echo "- Lieu: " . $l->nom . " (Actif: " . ($l->actif ? 'Oui' : 'Non') . ", Enigmes actives: " . $enigmesActives . ")\n";
    }
} else {
    echo "Environnement Cotonou non trouvé.\n";
}
