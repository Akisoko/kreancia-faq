<?php


header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Méthode non autorisée. Seul POST est accepté."]);
    exit;
}


$rawData = file_get_contents("php://input");
$data = json_decode($rawData, true);

if (!$data || !isset($data['partnerName'])) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Données JSON invalides ou nom du partenaire manquant."]);
    exit;
}


$partnerName = $data['partnerName'];
$slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $partnerName), '-'));

if (empty($slug)) {
    $slug = "partenaire-inconnu";
}

$dir = __DIR__ . '/configs';

if (!is_dir($dir)) {
    if (!mkdir($dir, 0755, true)) {
        http_response_code(500);
        echo json_encode(["success" => false, "message" => "Impossible de créer le répertoire des configurations."]);
        exit;
    }
}

// Recherche si un fichier correspondant au slug existe déjà pour conserver le même token/nom de fichier
$existingFiles = glob($dir . '/' . $slug . '-*.json');
$token = '';
if ($existingFiles && count($existingFiles) > 0) {
    // Extrait le token du premier fichier correspondant trouvé
    $filename = basename($existingFiles[0], '.json');
    $parts = explode('-', $filename);
    $token = end($parts);
}

// S'il n'y a pas de token existant, on en génère un unique
if (empty($token) || strlen($token) !== 8) {
    $token = bin2hex(random_bytes(4)); // Token de 8 caractères hexadécimaux
}

$finalSlug = $slug . '-' . $token;
$filePath = $dir . '/' . $finalSlug . '.json';

$jsonData = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

if (file_put_contents($filePath, $jsonData) !== false) {
    echo json_encode([
        "success" => true,
        "message" => "Configuration enregistrée avec succès !",
        "slug" => $finalSlug,
        "filename" => $finalSlug . '.json'
    ]);
} else {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Erreur lors de l'écriture du fichier de configuration."]);
}

