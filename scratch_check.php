<?php
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

foreach (App\Models\PlaystationUnit::all() as $unit) {
    echo "ID: {$unit->id}, Name: {$unit->name}, Status: '{$unit->status}' (Length: " . strlen($unit->status) . ")\n";
}
