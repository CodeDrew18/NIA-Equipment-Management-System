<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$controller = app(\App\Http\Controllers\admin\monthlyEquipmentUtilizationReportController::class);

$reflection = new \ReflectionClass($controller);
$method = $reflection->getMethod('buildVehicleRows');
$method->setAccessible(true);

$rows = $method->invoke($controller, [], 31);
echo "Count: " . count($rows) . "\n";
foreach ($rows as $row) {
    echo $row['typeLabel'] . ' - ' . $row['propPlateLabel'] . "\n";
}
