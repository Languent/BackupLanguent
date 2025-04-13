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

// Obter preferências do usuário
$sql_preferencias = "SELECT p.preferencia FROM tb_usuario_preferencia up
                     JOIN tb_preferencias p ON up.id_preferencia = p.id_preferencia
                     WHERE up.id_usuario = ?";
$stmt_preferencias = $conn->prepare($sql_preferencias);
$stmt_preferencias->bind_param("i", $id_usuario);
$stmt_preferencias->execute();
$result_preferencias = $stmt_preferencias->get_result();

$preferencias = [];
while ($row = $result_preferencias->fetch_assoc()) {
    $preferencias[] = $row['preferencia'];
}

// Obter idioma do usuário
$sql_lingua = "SELECT TRIM(l.lingua) AS lingua FROM tb_usuario u
               JOIN tb_lingua l ON u.id_lingua = l.id_lingua
               WHERE u.id_usuario = ?";
$stmt_lingua = $conn->prepare($sql_lingua);
$stmt_lingua->bind_param("i", $id_usuario);
$stmt_lingua->execute();
$result_lingua = $stmt_lingua->get_result();

if ($result_lingua->num_rows == 0) {
    echo json_encode(['error' => 'Erro: Idioma do usuário não encontrado']);
    exit;
}

$lingua = $result_lingua->fetch_assoc()['lingua'] ?? null;

$lingua_map = [
    'ingles' => 'en',
    'espanhol' => 'es',
    'italiano' => 'it',
    'frances' => 'fr'
];
$lingua_codigo = $lingua_map[$lingua] ?? null;

if (!$lingua_codigo) {
    echo json_encode(['error' => 'Erro: Idioma não suportado']);
    exit;
}

// Neste ponto, mantemos as preferências em inglês
$preferencias_traduzidas = $preferencias;

// Log das preferências que serão usadas na API
file_put_contents('log_preferencias.txt', print_r($preferencias_traduzidas, true));

// Chamada para a API do YouTube
function get_youtube_videos($preferencias, $language, $pageToken = '', $maxResults = 3, $totalResults = 9) {
    $apiKey = "AIzaSyDjSYNA3VDKSPUikwbuJqt4-kUwhd-7vG8"; // Substitua pela sua chave da API
    $regionCodes = ['en' => 'US', 'es' => 'ES', 'it' => 'IT', 'fr' => 'FR'];
    $regionCode = $regionCodes[$language] ?? 'US';

    $categoria_map = [
        'music' => '10',
        'sports' => '17',
        'movies' => '1',
        'technology' => '28',
        'gastronomy' => '26',
        'literature' => '24',
        'art' => '27'
    ];

    $all_videos = [];
    $collectedResults = 0;

    foreach ($preferencias as $preferencia) {
        if (!isset($categoria_map[$preferencia])) {
            error_log("Preferência não mapeada: $preferencia");
            continue;
        }

        $categoriaId = $categoria_map[$preferencia];
        do {
            $url = "https://www.googleapis.com/youtube/v3/videos?part=snippet&chart=mostPopular&regionCode=$regionCode&videoCategoryId=$categoriaId&maxResults=" . min($maxResults, $totalResults - $collectedResults) . "&key=$apiKey&pageToken=$pageToken";
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_URL, $url);
            $response = curl_exec($ch);
            curl_close($ch);

            if (!$response) {
                error_log("Erro ao fazer requisição para a API: " . curl_error($ch));
                break;
            }

            $response_data = json_decode($response, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                error_log("Erro ao decodificar JSON: " . json_last_error_msg() . ". Resposta: $response");
                break;
            }

            if (isset($response_data['error'])) {
                error_log("Erro da API do YouTube: " . json_encode($response_data['error']));
                break;
            }

            if (!empty($response_data['items'])) {
                foreach ($response_data['items'] as $item) {
                    if (!empty($item['id'])) {
                        $all_videos[] = ['id' => $item['id']];
                        $collectedResults++;
                        if ($collectedResults >= $totalResults) break 2;
                    }
                }
            }

            $pageToken = $response_data['nextPageToken'] ?? null;
        } while ($pageToken && $collectedResults < $totalResults);
    }

    return [
        'items' => $all_videos,
        'nextPageToken' => $pageToken
    ];
}

// Coletar token e enviar resposta
$pageToken = $_GET['pageToken'] ?? '';
$maxResults = 9;

$response_data = get_youtube_videos($preferencias_traduzidas, $lingua_codigo, $pageToken, 3, $maxResults);

echo json_encode([
    'items' => $response_data['items'] ?? [],
    'nextPageToken' => $response_data['nextPageToken'] ?? ''
]);
?>
