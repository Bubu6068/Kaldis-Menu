<?php
declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/vendor/autoload.php';

use App\Helpers\Env;
use App\Helpers\Database;

Env::load(BASE_PATH . '/.env');

$branches = [
    ['code' => 'ADAMA_GERMAN', 'name' => 'Adama German', 'address' => 'City Mall, Near Adama Posta, Adama'],
    ['code' => 'ADDISU_GEBYA', 'name' => 'Addisu Gebya', 'address' => 'Addisu Gebya Jemo Building'],
    ['code' => 'AIRPORT', 'name' => 'Airport', 'address' => 'Inside Airport'],
    ['code' => 'BETEL_ADEBABAY', 'name' => 'Betel Adebabay', 'address' => 'Betel Adebabay'],
    ['code' => 'BETEL_BICHA', 'name' => 'Betel Bicha Foq', 'address' => 'Official Branch'],
    ['code' => 'BISHOFTU', 'name' => 'Bishoftu', 'address' => 'Babogaya Hotel'],
    ['code' => 'BOLE_MED', 'name' => 'Bole Medhanialem', 'address' => 'Next to Bole high school'],
    ['code' => 'BOLE_OROMIYA', 'name' => 'Bole Oromiya', 'address' => 'Official Branch'],
    ['code' => 'BULBULA_93', 'name' => 'Bulbula 93', 'address' => 'Jamboro Building'],
    ['code' => 'BULBULA_MED', 'name' => 'Bulbula Medhanialm', 'address' => 'Official Branch'],
    ['code' => 'ECA', 'name' => 'ECA', 'address' => 'Official Branch'],
    ['code' => 'ENTOTO_PARK', 'name' => 'Entoto Park', 'address' => 'Inside Entoto Park'],
    ['code' => 'GRAND_PALACE', 'name' => 'Grand Palace', 'address' => 'Inside Grand Palace'],
    ['code' => 'GULELE_MED', 'name' => 'Gulele Medhanialm', 'address' => 'Fiance Building'],
    ['code' => 'GURDSHOLA', 'name' => 'Gurdshola', 'address' => 'Holy city mall'],
    ['code' => 'KAZANCHIS', 'name' => 'Kazanchis (Intercontinental)', 'address' => 'Jomo Kenyatta St, Near UNECA'],
    ['code' => 'SARBET', 'name' => 'Sarbet', 'address' => 'Roosevelt St, Near AU Headquarters'],
    ['code' => '4_KILO', 'name' => '4 Kilo', 'address' => 'AAU Area, 4 Kilo'],
];

$stmt = Database::local()->prepare(
    'INSERT INTO branches (code, name, address, is_active) 
     VALUES (?, ?, ?, 1) 
     ON DUPLICATE KEY UPDATE name = VALUES(name), address = VALUES(address)'
);

foreach ($branches as $b) {
    $stmt->execute([$b['code'], $b['name'], $b['address']]);
}

echo "Successfully seeded/updated " . count($branches) . " branches.\n";

