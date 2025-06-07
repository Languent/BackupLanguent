<?php
session_start();
header('Content-Type: application/json');

ini_set('display_errors', 0);
error_reporting(0);

require_once 'conection.php';

// --- CONFIGURAÇÕES DE CACHE ---
define('CACHE_DIR', __DIR__ . '/cache'); // Cria uma pasta 'cache' dentro da pasta 'php'
define('CACHE_EXPIRATION_SECONDS', 7200); // 2 horas (2 * 60 * 60)

// Cria o diretório de cache se ele não existir
if (!is_dir(CACHE_DIR)) {
    mkdir(CACHE_DIR, 0755, true);
}

// Função para enviar erros JSON padronizados
function send_json_error($message, $code = 400, $details = []) {
    http_response_code($code);
    $response = ['error' => $message];
    if (!empty($details)) {
        $response['details'] = $details;
    }
    echo json_encode($response);
    exit;
}

// Função para fazer requisições à API usando cURL
function fetch_from_api($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code !== 200) {
        $error_data = json_decode($response, true);
        $error_message = $error_data['error']['message'] ?? 'Erro desconhecido na API do YouTube.';
        send_json_error('Falha na comunicação com a API', $http_code, ['api_error' => $error_message]);
    }
    
    return $response; // Retorna a string JSON crua para o cache
}

if (!isset($_SESSION['id_usuario'])) {
    send_json_error('Usuário não autenticado', 401);
}

$id_usuario = $_SESSION['id_usuario'];
$mode = $_GET['mode'] ?? 'recommendations';
$type = $_GET['type'] ?? 'videos';
$query = $_GET['q'] ?? '';
$pageToken = $_GET['pageToken'] ?? '';

// --- LÓGICA DE CACHE ---
// Cria uma chave única para esta requisição específica
$cache_key_params = ($mode === 'search') ? [$type, $query, $pageToken] : [$id_usuario, $type, $pageToken];
$cache_key = md5(implode('-', $cache_key_params));
$cache_file = CACHE_DIR . '/' . $cache_key . '.json';

// Verifica se existe um cache válido
if (file_exists($cache_file) && (time() - filemtime($cache_file)) < CACHE_EXPIRATION_SECONDS) {
    // Entrega o resultado do cache e encerra o script
    readfile($cache_file);
    exit;
}

// --- LÓGICA PRINCIPAL (Executada apenas se não houver cache) ---

$sql_user_data = "SELECT l.lingua, p.preferencia FROM tb_usuario u LEFT JOIN tb_lingua l ON u.id_lingua = l.id_lingua LEFT JOIN tb_usuario_preferencia up ON u.id_usuario = up.id_usuario LEFT JOIN tb_preferencias p ON up.id_preferencia = p.id_preferencia WHERE u.id_usuario = ?";
$stmt_user = $conn->prepare($sql_user_data);
$stmt_user->bind_param("i", $id_usuario);
$stmt_user->execute();
$result = $stmt_user->get_result();

$user_prefs = [];
$lingua_db = '';
while ($row = $result->fetch_assoc()) {
    if (!empty($row['lingua'])) $lingua_db = $row['lingua'];
    if (!empty($row['preferencia'])) $user_prefs[] = $row['preferencia'];
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

$params = [];
$search_phrase = ($mode === 'search' && !empty($query)) ? $query : (!empty($user_prefs) ? implode(' | ', $user_prefs) : 'vídeos populares');

switch ($type) {
    case 'music': $search_phrase .= ' música'; $params['videoCategoryId'] = '10'; break;
    case 'shorts': $search_phrase .= ' #shorts'; $params['videoDuration'] = 'short'; break;
    case 'podcasts': $search_phrase .= ' podcast'; $params['videoDuration'] = 'long'; break;
    default: $search_phrase .= ' -#shorts'; break;
}

if ($lang_config['iso'] !== 'en') $search_phrase .= ' ' . $lang_config['native'];

$params += [
    'q' => $search_phrase, 'key' => $apiKey, 'part' => 'snippet', 'type' => 'video',
    'maxResults' => 40, 'regionCode' => $lang_config['region'], 'relevanceLanguage' => $lang_config['iso']
];
if ($pageToken) $params['pageToken'] = $pageToken;

$search_url = "https://www.googleapis.com/youtube/v3/search?" . http_build_query($params);
$search_response_json = fetch_from_api($search_url);
$search_data = json_decode($search_response_json, true);

if (empty($search_data['items'])) {
    echo json_encode(['items' => [], 'nextPageToken' => null]);
    exit;
}

$video_ids = implode(',', array_column(array_column($search_data['items'], 'id'), 'videoId'));
$verify_params = ['part' => 'snippet,status', 'id' => $video_ids, 'key' => $apiKey];
$verify_url = "https://www.googleapis.com/youtube/v3/videos?" . http_build_query($verify_params);
$verify_data = json_decode(fetch_from_api($verify_url), true);

$final_items = [];
if (!empty($verify_data['items'])) {
    foreach ($verify_data['items'] as $item) {
        if (($item['status']['embeddable'] ?? false) && str_starts_with($item['snippet']['defaultAudioLanguage'] ?? '', $lang_config['iso'])) {
            $final_items[] = ['id' => $item['id']];
        }
    }
}

$final_response = json_encode([
    'items' => $final_items,
    'nextPageToken' => $search_data['nextPageToken'] ?? null
]);

// Salva o resultado no arquivo de cache
file_put_contents($cache_file, $final_response);

// Entrega a resposta final
echo $final_response;
?>