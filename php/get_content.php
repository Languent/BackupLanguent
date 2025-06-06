<?php
session_start();
header('Content-Type: application/json');
require_once 'conection.php'; // Inclui $conn e $apiKey

if (!isset($_SESSION['id_usuario'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Usuário não autenticado']);
    exit;
}

// Parâmetros do frontend
$id_usuario = $_SESSION['id_usuario'];
$mode = $_GET['mode'] ?? 'recommendations';
$type = $_GET['type'] ?? 'videos';
$query = $_GET['q'] ?? '';
$pageToken = $_GET['pageToken'] ?? '';

// --- 1. Obter Configurações de Idioma e Preferências do Usuário ---
$sql_user_data = "
    SELECT l.lingua, p.preferencia
    FROM tb_usuario u
    LEFT JOIN tb_lingua l ON u.id_lingua = l.id_lingua
    LEFT JOIN tb_usuario_preferencia up ON u.id_usuario = up.id_usuario
    LEFT JOIN tb_preferencias p ON up.id_preferencia = p.id_preferencia
    WHERE u.id_usuario = ?
";
$stmt_user = $conn->prepare($sql_user_data);
$stmt_user->bind_param("i", $id_usuario);
$stmt_user->execute();
$result = $stmt_user->get_result();

$user_prefs = [];
$lingua_db = '';
while ($row = $result->fetch_assoc()) {
    $lingua_db = $row['lingua'];
    if ($row['preferencia']) {
        $user_prefs[] = $row['preferencia'];
    }
}
$stmt_user->close();

$lingua_key = strtolower(trim($lingua_db));
$lang_map = [
    'ingles' => ['iso' => 'en', 'native' => 'english', 'region' => 'US'],
    'espanhol' => ['iso' => 'es', 'native' => 'español', 'region' => 'ES'],
    'italiano' => ['iso' => 'it', 'native' => 'italiano', 'region' => 'IT'],
    'frances' => ['iso' => 'fr', 'native' => 'français', 'region' => 'FR']
];
$lang_config = $lang_map[$lingua_key] ?? $lang_map['ingles'];

// --- 2. Lógica Central para Definir a Consulta de Busca ---
$params = [];
$search_phrase = '';

// Define a frase de busca principal com base no modo
if ($mode === 'search' && !empty($query)) {
    $search_phrase = $query;
} else { // Modo de recomendações
    $search_phrase = !empty($user_prefs) ? implode(' | ', $user_prefs) : 'vídeos populares';
}

// Aplica modificadores com base no tipo de conteúdo
switch ($type) {
    case 'music':
        // A guia de música sempre busca por música, usando a categoria para maior relevância.
        $search_phrase = ($mode === 'search' && !empty($query)) ? $query : 'música';
        $params['videoCategoryId'] = '10';
        break;
    case 'shorts':
        $search_phrase .= ' #shorts';
        $params['videoDuration'] = 'short';
        break;
    case 'podcasts':
        $search_phrase .= ' podcast';
        $params['videoDuration'] = 'long';
        break;
    case 'videos':
    default:
        $search_phrase .= ' -#shorts';
        break;
}

// Adiciona o idioma nativo à busca para máxima relevância
if ($lang_config['iso'] !== 'en') {
    $search_phrase .= ' ' . $lang_config['native'];
}

// --- 3. Execução da API e Filtragem ---
$params['q'] = $search_phrase;
$params['key'] = $apiKey;
$params['part'] = 'snippet';
$params['type'] = 'video';
$params['maxResults'] = 30; // Pede mais para compensar a filtragem
$params['regionCode'] = $lang_config['region'];
$params['relevanceLanguage'] = $lang_config['iso'];
if ($pageToken) $params['pageToken'] = $pageToken;

$search_url = "https://www.googleapis.com/youtube/v3/search?" . http_build_query($params);
$ch_search = curl_init($search_url);
curl_setopt($ch_search, CURLOPT_RETURNTRANSFER, 1);
$search_response = curl_exec($ch_search);
curl_close($ch_search);
$search_data = json_decode($search_response, true);

if (empty($search_data['items'])) {
    echo json_encode(['items' => [], 'nextPageToken' => null]);
    exit;
}

// Extrai IDs e faz a verificação de idioma e disponibilidade
$video_ids = implode(',', array_column(array_column($search_data['items'], 'id'), 'videoId'));
$verify_params = ['part' => 'snippet,status', 'id' => $video_ids, 'key' => $apiKey];
$verify_url = "https://www.googleapis.com/youtube/v3/videos?" . http_build_query($verify_params);

$ch_verify = curl_init($verify_url);
curl_setopt($ch_verify, CURLOPT_RETURNTRANSFER, 1);
$verify_response = curl_exec($ch_verify);
curl_close($ch_verify);
$verify_data = json_decode($verify_response, true);

// Filtra os resultados
$final_items = [];
if (!empty($verify_data['items'])) {
    foreach ($verify_data['items'] as $item) {
        $is_embeddable = $item['status']['embeddable'] ?? false;
        $audio_language = $item['snippet']['defaultAudioLanguage'] ?? '';
        $is_correct_language = str_starts_with($audio_language, $lang_config['iso']);

        if ($is_embeddable && $is_correct_language) {
            $final_items[] = ['id' => $item['id']];
        }
    }
}

echo json_encode([
    'items' => $final_items,
    'nextPageToken' => $search_data['nextPageToken'] ?? null
]);
?>