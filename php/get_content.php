<?php
session_start();
header('Content-Type: application/json');

// --- LÓGICA DE CACHE ---
define('CACHE_DIR', __DIR__ . '/cache/');
if (!is_dir(CACHE_DIR)) {
    mkdir(CACHE_DIR, 0755, true);
}
$cache_duration = empty($_GET['pageToken']) ? 900 : 21600;

require_once 'conection.php'; 

if (!isset($_SESSION['id_usuario'])) { send_json_error('Usuário não autenticado', 401); }
$id_usuario = $_SESSION['id_usuario'];

$sql_lang = "SELECT l.lingua FROM tb_usuario u JOIN tb_lingua l ON u.id_lingua = l.id_lingua WHERE u.id_usuario = ?";
$stmt_lang = $conn->prepare($sql_lang);
$stmt_lang->bind_param("i", $id_usuario);
$stmt_lang->execute();
$stmt_lang->bind_result($lingua_db);
$stmt_lang->fetch();
$stmt_lang->close();

$lingua_key = strtolower(trim($lingua_db ?: 'ingles'));
$lang_map = ['ingles' => ['iso' => 'en', 'region' => 'US'], 'espanhol' => ['iso' => 'es', 'region' => 'ES'], 'italiano' => ['iso' => 'it', 'region' => 'IT'], 'frances' => ['iso' => 'fr', 'region' => 'FR']];
$lang_config = $lang_map[$lingua_key] ?? $lang_map['ingles'];

$cache_key_params = [
    'id_usuario' => $id_usuario,
    'lang_iso' => $lang_config['iso'],
    'mode' => $_GET['mode'] ?? 'recommendations',
    'type' => $_GET['type'] ?? 'videos',
    'q' => $_GET['q'] ?? '',
    'pageToken' => $_GET['pageToken'] ?? ''
];
$cache_file = CACHE_DIR . md5(http_build_query($cache_key_params)) . '.json';

if (file_exists($cache_file) && (time() - filemtime($cache_file)) < $cache_duration) {
    echo file_get_contents($cache_file);
    exit;
}
// --- FIM DA LÓGICA DE CACHE ---

ini_set('display_errors', 0);
error_reporting(0);

define('VIDEOS_PER_PAGE', 12);
define('POOL_SIZE_TARGET', 36);

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

function is_video_restricted($item, $region_code) {
    if (!($item['status']['embeddable'] ?? false) || ($item['status']['uploadStatus'] ?? 'processed') !== 'processed') return true;
    if (($item['status']['madeForKids'] ?? false)) return true;
    if (isset($item['contentDetails']['contentRating']['ytRating']) && $item['contentDetails']['contentRating']['ytRating'] === 'ytAgeRestricted') return true;
    if (isset($item['snippet']['liveBroadcastContent']) && $item['snippet']['liveBroadcastContent'] !== 'none') return true;
    if (isset($item['contentDetails']['regionRestriction'])) {
        $restriction = $item['contentDetails']['regionRestriction'];
        if (isset($restriction['blocked']) && in_array($region_code, $restriction['blocked'])) return true;
        if (isset($restriction['allowed']) && !in_array($region_code, $restriction['allowed'])) return true;
    }
    return false;
}

function is_from_blocked_channel($channelTitle) {
    $blocked_keywords = ['NFL', 'FIFA', 'NBA', 'VEVO', 'Sony Pictures', 'Warner Bros', 'Disney', 'UFC', 'LaLiga', 'Premier League', 'MLB', 'NHL', 'Paramount', 'Universal Pictures', 'ABC', 'CBS', 'NBC', 'ESPN', 'Fox Sports'];
    $channelTitleLower = strtolower($channelTitle);
    foreach ($blocked_keywords as $keyword) { if (str_contains($channelTitleLower, strtolower($keyword))) return true; }
    return false;
}

function is_childrens_content_by_keyword($title) {
    $children_keywords = ['crianças', 'infantil', 'kids', 'for children', 'desenho animado', 'cartoon', 'nursery rhymes', 'canções de ninar', 'baby songs', 'niños', 'dibujos animados', 'caricaturas', 'canciones infantiles', 'enfants', 'pour enfants', 'dessin animé', 'comptines', 'bambini', 'per bambini', 'cartone animato', 'canzoni per bambini', 'filastrocche'];
    $titleLower = strtolower($title);
    foreach ($children_keywords as $keyword) { if (str_contains($titleLower, $keyword)) return true; }
    return false;
}
// --- FIM DAS FUNÇÕES AUXILIARES ---

$mode = $_GET['mode'] ?? 'recommendations';
$type = $_GET['type'] ?? 'videos';
$query = trim($_GET['q'] ?? '');
$pageToken = $_GET['pageToken'] ?? '';
$exclude_ids_str = $_GET['exclude_ids'] ?? '';

$sql_prefs = "SELECT p.preferencia, a.video_id FROM tb_usuario u LEFT JOIN tb_usuario_preferencia up ON u.id_usuario = up.id_usuario LEFT JOIN tb_preferencias p ON up.id_preferencia = p.id_preferencia LEFT JOIN tb_avaliacoes a ON u.id_usuario = a.id_usuario AND a.tipo = 'dislike' WHERE u.id_usuario = ?";
$stmt_prefs = $conn->prepare($sql_prefs);
$stmt_prefs->bind_param("i", $id_usuario);
$stmt_prefs->execute();
$result = $stmt_prefs->get_result();
$user_prefs_en = []; $disliked_videos = [];
while ($row = $result->fetch_assoc()) {
    if (!empty($row['preferencia']) && !in_array($row['preferencia'], $user_prefs_en)) $user_prefs_en[] = $row['preferencia'];
    if (!empty($row['video_id'])) $disliked_videos[] = $row['video_id'];
}
$stmt_prefs->close();

$translation_map = [
    'es' => ['sports' => 'deportes fitness', 'history' => 'historia biografías', 'science' => 'ciencia tecnología', 'travel' => 'viajes culturas', 'gastronomy' => 'gastronomía cocina', 'art' => 'arte literatura', 'nature' => 'naturaleza documental', 'education' => 'educación curiosidades', 'music' => 'música', 'podcast' => 'podcast español'],
    'fr' => ['sports' => 'sport fitness', 'history' => 'histoire biographie', 'science' => 'science technologie', 'travel' => 'voyage cultures', 'gastronomy' => 'gastronomie cuisine', 'art' => 'art littérature', 'nature' => 'nature documentaire', 'education' => 'éducation curiosités', 'music' => 'musique', 'podcast' => 'podcast français'],
    'it' => ['sports' => 'sport fitness', 'history' => 'storia biografie', 'science' => 'scienza tecnologia', 'travel' => 'viaggi culture', 'gastronomy' => 'gastronomia cucina', 'art' => 'arte letteratura', 'nature' => 'natura documentario', 'education' => 'educazione curiosità', 'music' => 'musica', 'podcast' => 'podcast italiano']
];
$user_prefs_translated = [];
$lang_iso = $lang_config['iso'];
if ($lang_iso !== 'en') {
    foreach ($user_prefs_en as $pref) {
        $user_prefs_translated[] = $translation_map[$lang_iso][$pref] ?? $pref;
    }
} else {
    $user_prefs_translated = $user_prefs_en;
}

$pool = [];
$next_page_token = $pageToken;
$processed_ids = array_merge($disliked_videos, array_filter(explode(',', $exclude_ids_str)));
$attempts = 0;

while (count($pool) < POOL_SIZE_TARGET && $attempts < 5) {
    $api_params = ['key' => $apiKey, 'part' => 'snippet', 'regionCode' => $lang_config['region'], 'relevanceLanguage' => $lang_config['iso'], 'maxResults' => 50, 'pageToken' => $next_page_token, 'type' => 'video', 'videoEmbeddable' => 'true'];
    if ($mode === 'search') {
        $api_params['q'] = $query;
    } else {
        $api_params['order'] = 'relevance';
        switch ($type) {
            case 'music': $api_params += ['q' => $translation_map[$lang_iso]['music'] ?? 'music', 'videoCategoryId' => '10']; break;
            case 'podcasts': $api_params += ['q' => $translation_map[$lang_iso]['podcast'] ?? 'podcast', 'videoDuration' => 'long']; break;
            case 'shorts': $prefs_query = !empty($user_prefs_translated) ? implode('|', $user_prefs_translated) : 'interessante'; $api_params += ['q' => "($prefs_query) #shorts", 'videoDuration' => 'short']; break;
            default: $prefs_query = !empty($user_prefs_translated) ? implode('|', $user_prefs_translated) : 'documental'; $api_params['q'] = $prefs_query; $api_params += ['videoDuration' => 'medium']; break;
        }
    }

    $response_data = json_decode(fetch_from_api("https://www.googleapis.com/youtube/v3/search?" . http_build_query($api_params)), true);
    $next_page_token = $response_data['nextPageToken'] ?? null;
    $items_to_process = $response_data['items'] ?? [];
    if (empty($items_to_process)) break;

    $ids_to_check = array_column(array_column($items_to_process, 'id'), 'videoId');
    $ids_to_check = array_diff($ids_to_check, $processed_ids);
    if (empty($ids_to_check)) { if (is_null($next_page_token)) break; $attempts++; continue; }
    
    $verify_params = ['part' => 'snippet,status,contentDetails', 'id' => implode(',', $ids_to_check), 'key' => $apiKey];
    $verify_data = json_decode(fetch_from_api("https://www.googleapis.com/youtube/v3/videos?" . http_build_query($verify_params)), true);
    if (empty($verify_data['items'])) continue;

    foreach ($verify_data['items'] as $item) {
        if (in_array($item['id'], $processed_ids)) continue;
        if (is_video_restricted($item, $lang_config['region'])) { continue; }
        if (isset($item['snippet']['title']) && is_childrens_content_by_keyword($item['snippet']['title'])) { continue; }
        if ($type === 'videos' && isset($item['snippet']['channelTitle']) && is_from_blocked_channel($item['snippet']['channelTitle'])) { continue; }
        if (!str_starts_with($item['snippet']['defaultAudioLanguage'] ?? '', $lang_config['iso'])) { continue; }
        
        $duration = parse_iso8601_duration($item['contentDetails']['duration'] ?? 'PT0S');
        if ($type === 'videos' && $duration <= 180) continue;
        if ($type === 'shorts' && $duration > 180) continue;
        if ($type === 'podcasts' && $duration < 1200) continue;

        $processed_ids[] = $item['id'];
        $pool[] = $item;
    }
    $attempts++;
    if (is_null($next_page_token)) break;
}

// --- LÓGICA DE CURADORIA E MONTAGEM FINAL ---
$final_items = [];
if ($mode === 'recommendations' && $type === 'videos' && !empty($pool)) {
    $topic_buckets = [];
    foreach ($user_prefs_translated as $pref) {
        $topic_buckets[$pref] = [];
    }
    $unassigned_bucket = [];

    foreach ($pool as $item) {
        $assigned = false;
        $video_title_lower = strtolower($item['snippet']['title']);
        foreach ($user_prefs_translated as $pref) {
            $keywords = explode(' ', $pref);
            foreach($keywords as $keyword) {
                 if (str_contains($video_title_lower, $keyword)) {
                    $topic_buckets[$pref][] = ['id' => $item['id']];
                    $assigned = true;
                    break 2;
                }
            }
        }
        if (!$assigned) { $unassigned_bucket[] = ['id' => $item['id']]; }
    }

    $prefs_count = count($user_prefs_translated);
    $max_per_topic = $prefs_count > 0 ? ceil(VIDEOS_PER_PAGE / $prefs_count) : VIDEOS_PER_PAGE;
    
    foreach ($topic_buckets as $pref => $bucket) {
        $items_to_add = array_slice($bucket, 0, $max_per_topic);
        $final_items = array_merge($final_items, $items_to_add);
    }
     if (count($final_items) < VIDEOS_PER_PAGE) {
        $final_items = array_merge($final_items, $unassigned_bucket);
    }
} else {
    foreach($pool as $item) {
        $final_items[] = ['id' => $item['id']];
    }
}

shuffle($final_items);
$final_items = array_slice($final_items, 0, VIDEOS_PER_PAGE);
// --- FIM DA LÓGICA DE CURADORIA ---

$output_data = json_encode(['items' => $final_items, 'nextPageToken' => $next_page_token]);
file_put_contents($cache_file, $output_data);
echo $output_data;