<?php
session_start();
ini_set('display_errors', 1);
ini_set('log_errors', 1);
error_reporting(E_ALL);

// Conexão com o banco de dados
$dbhost = "sql205.infinityfree.com";
$dbuser = "if0_37044542";
$dbpass = "SenhaLanguent";
$dbname = "if0_37044542_languent";
$conn = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);

if (!$conn) {
    die(json_encode(['error' => 'Erro: Conexão com o banco de dados falhou! ' . mysqli_connect_error()]));
}

// Pega o ID do usuário da sessão
$id_usuario = $_SESSION['id_usuario'] ?? null;
if (!$id_usuario) {
    die(json_encode(['error' => 'Erro: ID do usuário não encontrado na sessão']));
}

// Obtém as preferências do usuário
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

// Obtém o idioma do usuário
$sql_lingua = "SELECT TRIM(l.lingua) AS lingua FROM tb_usuario u 
               JOIN tb_lingua l ON u.id_lingua = l.id_lingua 
               WHERE u.id_usuario = ?";
$stmt_lingua = $conn->prepare($sql_lingua);
$stmt_lingua->bind_param("i", $id_usuario);
$stmt_lingua->execute();
$result_lingua = $stmt_lingua->get_result();

if ($result_lingua->num_rows == 0) {
    die(json_encode(['error' => 'Erro: Idioma do usuário não encontrado']));
}

$lingua_row = $result_lingua->fetch_assoc();
$lingua = $lingua_row['lingua'] ?? die(json_encode(['error' => 'Erro: Variável lingua não está definida corretamente']));

$lingua_map = [
    'ingles' => 'en',
    'espanhol' => 'es',
    'italiano' => 'it',
    'frances' => 'fr'
];

$lingua_codigo = $lingua_map[$lingua] ?? die(json_encode(['error' => 'Erro: Idioma não suportado']));

$preferencia_map = [
    'ingles' => ['sports' => 'sports', 'movies' => 'movies', 'music' => 'music', 'technology' => 'technology', 'gastronomy' => 'gastronomy', 'literature' => 'literature', 'art' => 'art'],
    'espanhol' => ['sports' => 'deportes', 'movies' => 'películas', 'music' => 'música', 'technology' => 'tecnología', 'gastronomy' => 'gastronomía', 'literature' => 'literatura', 'art' => 'arte'],
    'italiano' => ['sports' => 'sport', 'movies' => 'film', 'music' => 'musica', 'technology' => 'tecnologia', 'gastronomy' => 'gastronomia', 'literature' => 'letteratura', 'art' => 'arte'],
    'frances' => ['sports' => 'sports', 'movies' => 'films', 'music' => 'musique', 'technology' => 'technologie', 'gastronomy' => 'gastronomie', 'literature' => 'littérature', 'art' => 'art'],
];

$preferencias_traduzidas = [];
foreach ($preferencias as $preferencia) {
    $preferencias_traduzidas[] = $preferencia_map[$lingua][$preferencia] ?? $preferencia;
}

function get_youtube_videos($preferencias, $language, $pageToken = '', $maxResults = 3, $totalResults = 10) {
    $apiKey = "AIzaSyDjSYNA3VDKSPUikwbuJqt4-kUwhd-7vG8";
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
    foreach ($preferencias as $preferencia) {
        $categoriaId = $categoria_map[$preferencia] ?? null;
        if (!$categoriaId) {
            continue;
        }

        $collectedResults = 0;
        do {
            $url = "https://www.googleapis.com/youtube/v3/videos?part=snippet&chart=mostPopular&regionCode=$regionCode&videoCategoryId=$categoriaId&videoDuration=long&maxResults=" . min($maxResults, $totalResults - $collectedResults) . "&key=$apiKey&pageToken=$pageToken";

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_URL, $url);
            $response = curl_exec($ch);
            curl_close($ch);

            $response_data = json_decode($response, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                error_log("Erro ao decodificar JSON: " . json_last_error_msg());
                break;
            }

            if (!empty($response_data['items'])) {
                $all_videos = array_merge($all_videos, $response_data['items']);
                $collectedResults += count($response_data['items']);
            }

            $pageToken = $response_data['nextPageToken'] ?? null;
        } while ($pageToken && $collectedResults < $totalResults);
    }

    return [
        'items' => array_map(function($video) {
            return ['id' => $video['id']];
        }, $all_videos),
        'nextPageToken' => $pageToken,
    ];
}

// Processamento da requisição
$pageToken = $_GET['pageToken'] ?? '';
$maxResults = 9;

$response_data = get_youtube_videos($preferencias_traduzidas, $lingua_codigo, $pageToken, $maxResults);

$response = [
    'items' => $response_data['items'] ?? [],
    'nextPageToken' => $response_data['nextPageToken'] ?? null,
];

header('Content-Type: application/json');
echo json_encode($response);
?>
