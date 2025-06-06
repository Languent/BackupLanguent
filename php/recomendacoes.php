<?php
session_start();
ini_set('display_errors', 1);
ini_set('log_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

require_once 'conection.php';

if (!$conn) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro: Conexão com o banco de dados falhou!']);
    exit;
}

$id_usuario = $_SESSION['id_usuario'] ?? null;
if (!$id_usuario) {
    http_response_code(401);
    echo json_encode(['error' => 'Erro: ID do usuário não encontrado na sessão']);
    exit;
}

$pageToken = $_GET['pageToken'] ?? '';
$tema = $_GET['tema'] ?? '';
$idioma = $_GET['idioma'] ?? '';

if (!$tema || !$idioma) {
    echo json_encode(['error' => 'Parâmetros de tema ou idioma ausentes']);
    exit;
}

$categoria_map = [
    'music' => '10',
    'sports' => '17',
    'movies' => '1',
    'technology' => '28',
    'gastronomy' => '26',
    'literature' => '24',
    'art' => '27',
    'nature' => '15'
];

if (!isset($categoria_map[$tema])) {
    echo json_encode(['error' => 'Preferência não mapeada']);
    exit;
}

$videoCategoryId = $categoria_map[$tema];

$regionCodes = ['en' => 'US', 'es' => 'ES', 'it' => 'IT', 'fr' => 'FR'];
$regionCode = $regionCodes[$idioma] ?? 'US';

$apiKey = "AIzaSyDjSYNA3VDKSPUikwbuJqt4-kUwhd-7vG8";

$url = "https://www.googleapis.com/youtube/v3/videos?part=snippet&chart=mostPopular&regionCode=$regionCode&videoCategoryId=$videoCategoryId&maxResults=16&key=$apiKey";

if ($pageToken) {
    $url .= "&pageToken=$pageToken";
}

$ch = curl_init();
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_URL, $url);
$response = curl_exec($ch);
curl_close($ch);

if (!$response) {
    echo json_encode(['error' => 'Erro na requisição à API']);
    exit;
}

$response_data = json_decode($response, true);

$items = [];

if (!empty($response_data['items'])) {
    foreach ($response_data['items'] as $item) {
        if (!empty($item['id'])) {
            $items[] = ['id' => $item['id']];
        }
    }
}

echo json_encode([
    'items' => $items,
    'nextPageToken' => $response_data['nextPageToken'] ?? null
]);
