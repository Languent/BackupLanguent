<?php
session_start();
header('Content-Type: application/json');
ini_set('display_errors', 0);
error_reporting(0);

require_once 'conection.php';

define('VIDEOS_PER_PAGE', 12);

// --- FUNÇÕES AUXILIARES ---
function send_json_error($message, $code = 400) { http_response_code($code); echo json_encode(['error' => $message]); exit; }
function fetch_from_api($url) {
    $ch = curl_init($url);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => 1, CURLOPT_TIMEOUT => 15, CURLOPT_SSL_VERIFYPEER => false]);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($http_code !== 200) { send_json_error('Falha na API do YouTube', $http_code); }
    return $response;
}
function parse_iso8601_duration($d) { try { $di = new DateInterval($d); return $di->s + ($di->i * 60) + ($di->h * 3600); } catch (Exception $e) { return 0; } }

// --- LÓGICA PRINCIPAL ---
if (!isset($_SESSION['id_usuario'])) { send_json_error('Usuário não autenticado', 401); }

$id_usuario = $_SESSION['id_usuario'];
$mode = $_GET['mode'] ?? 'recommendations';
$type = $_GET['type'] ?? 'videos';
$query = trim($_GET['q'] ?? '');
$pageToken = $_GET['pageToken'] ?? '';

// 1. BUSCAR DADOS DO USUÁRIO
$sql_user = "SELECT l.lingua, p.preferencia, a.video_id FROM tb_usuario u LEFT JOIN tb_lingua l ON u.id_lingua = l.id_lingua LEFT JOIN tb_usuario_preferencia up ON u.id_usuario = up.id_usuario LEFT JOIN tb_preferencias p ON up.id_preferencia = p.id_preferencia LEFT JOIN tb_avaliacoes a ON u.id_usuario = a.id_usuario AND a.tipo = 'dislike' WHERE u.id_usuario = ?";
$stmt_user = $conn->prepare($sql_user); $stmt_user->bind_param("i", $id_usuario); $stmt_user->execute(); $result = $stmt_user->get_result();
$user_prefs_en = []; $disliked_videos = []; $lingua_db = '';
while ($row = $result->fetch_assoc()) {
    if (!empty($row['lingua'])) $lingua_db = $row['lingua'];
    if (!empty($row['preferencia']) && !in_array($row['preferencia'], $user_prefs_en)) $user_prefs_en[] = $row['preferencia'];
    if (!empty($row['video_id'])) $disliked_videos[] = $row['video_id'];
}
$stmt_user->close();

$lingua_key = strtolower(trim($lingua_db));
$lang_map = [
    'ingles' => ['iso' => 'en', 'region' => 'US', 'native' => 'english'], 
    'espanhol' => ['iso' => 'es', 'region' => 'ES', 'native' => 'español'], 
    'italiano' => ['iso' => 'it', 'region' => 'IT', 'native' => 'italiano'], 
    'frances' => ['iso' => 'fr', 'region' => 'FR', 'native' => 'français']
];
$lang_config = $lang_map[$lingua_key] ?? $lang_map['ingles'];

// 2. INTERNACIONALIZAÇÃO DAS PREFERÊNCIAS
$translation_map = [
    'es' => ['sports' => 'deportes', 'movies' => 'cine', 'music' => 'música', 'technology' => 'tecnología', 'gastronomy' => 'gastronomía', 'literature' => 'literatura', 'art' => 'arte', 'nature' => 'naturaleza'],
    'fr' => ['sports' => 'sport', 'movies' => 'cinéma', 'music' => 'musique', 'technology' => 'technologie', 'gastronomy' => 'gastronomie', 'literature' => 'littérature', 'art' => 'art', 'nature' => 'nature'],
    'it' => ['sports' => 'sport', 'movies' => 'cinema', 'music' => 'musica', 'technology' => 'tecnologia', 'gastronomy' => 'gastronomia', 'literature' => 'letteratura', 'art' => 'arte', 'nature' => 'natura']
];
$user_prefs_translated = [];
if ($lang_config['iso'] !== 'en') {
    foreach ($user_prefs_en as $pref) {
        $user_prefs_translated[] = $translation_map[$lang_config['iso']][$pref] ?? $pref;
    }
} else {
    $user_prefs_translated = $user_prefs_en;
}

// 3. LÓGICA DE BUSCA E FILTRAGEM
$final_items = [];
$next_page_token = $pageToken;
$processed_ids = $disliked_videos;
$attempts = 0;

while (count($final_items) < VIDEOS_PER_PAGE && $attempts < 2) {
    $api_params = ['key' => $apiKey, 'part' => 'snippet', 'regionCode' => $lang_config['region'], 'relevanceLanguage' => $lang_config['iso'], 'maxResults' => 25, 'pageToken' => $next_page_token, 'type' => 'video'];
    $candidate_ids = [];

    if ($mode === 'search') {
        $api_params['q'] = $query;
        $search_data = json_decode(fetch_from_api("https://www.googleapis.com/youtube/v3/search?" . http_build_query($api_params)), true);
        $candidate_ids = array_column(array_column($search_data['items'] ?? [], 'id'), 'videoId');
        $next_page_token = $search_data['nextPageToken'] ?? null;
    } else {
        // Para recomendações, agrega fontes diferentes
        $base_search_params = ['key' => $apiKey, 'part' => 'id', 'type' => 'video', 'maxResults' => 25, 'regionCode' => $lang_config['region']];

        // Fonte 1: Busca por Preferências Traduzidas
        if (!empty($user_prefs_translated)) {
            $pref_params = $base_search_params + ['q' => implode('|', $user_prefs_translated)];
            $pref_data = json_decode(fetch_from_api("https://www.googleapis.com/youtube/v3/search?" . http_build_query($pref_params)), true);
            if(!empty($pref_data['items'])) $candidate_ids = array_merge($candidate_ids, array_column(array_column($pref_data['items'], 'id'), 'videoId'));
        }

        // Fonte 2: Busca por Categoria Específica
        if ($type === 'music' || $type === 'videos') {
            $chart_params = ['key' => $apiKey, 'part' => 'id', 'chart' => 'mostPopular', 'maxResults' => 25, 'regionCode' => $lang_config['region']];
            if ($type === 'music') $chart_params['videoCategoryId'] = '10';
            $chart_data = json_decode(fetch_from_api("https://www.googleapis.com/youtube/v3/videos?" . http_build_query($chart_params)), true);
            if(!empty($chart_data['items'])) $candidate_ids = array_merge($candidate_ids, array_column($chart_data['items'], 'id'));
        }
    }
    
    $ids_to_check = array_unique($candidate_ids);
    $ids_to_check = array_diff($ids_to_check, $processed_ids);
    
    if (empty($ids_to_check)) { if (is_null($next_page_token)) break; $attempts++; continue; }
    
    $verify_params = ['part' => 'snippet,status,contentDetails', 'id' => implode(',', $ids_to_check), 'key' => $apiKey];
    $verify_data = json_decode(fetch_from_api("https://www.googleapis.com/youtube/v3/videos?" . http_build_query($verify_params)), true);

    if (empty($verify_data['items'])) continue;

    foreach ($verify_data['items'] as $item) {
        $processed_ids[] = $item['id'];
        if (!($item['status']['embeddable'] ?? false) || ($item['status']['madeForKids'] ?? false)) continue;
        if (!str_starts_with($item['snippet']['defaultAudioLanguage'] ?? '', $lang_config['iso'])) continue;
        
        $duration = parse_iso8601_duration($item['contentDetails']['duration'] ?? 'PT0S');
        if ($type === 'videos' && $duration <= 180) continue;
        if ($type === 'shorts' && $duration > 180) continue;
        if ($type === 'podcasts' && $duration < 1200) continue;

        $final_items[] = ['id' => $item['id']];
        if (count($final_items) >= VIDEOS_PER_PAGE) break;
    }
    $attempts++;
}

echo json_encode(['items' => $final_items, 'nextPageToken' => $next_page_token]);
?>