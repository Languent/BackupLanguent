<?php
session_start();
header('Content-Type: application/json');
ini_set('display_errors', 0);
error_reporting(0);

require_once 'conection.php';

define('VIDEOS_PER_PAGE', 12);

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

if (!isset($_SESSION['id_usuario'])) { send_json_error('Usuário não autenticado', 401); }

$id_usuario = $_SESSION['id_usuario'];
$mode = $_GET['mode'] ?? 'recommendations';
$type = $_GET['type'] ?? 'videos';
$query = trim($_GET['q'] ?? '');
$pageToken = $_GET['pageToken'] ?? '';

$sql_user = "SELECT l.lingua, p.preferencia, a.video_id FROM tb_usuario u LEFT JOIN tb_lingua l ON u.id_lingua = l.id_lingua LEFT JOIN tb_usuario_preferencia up ON u.id_usuario = up.id_usuario LEFT JOIN tb_preferencias p ON up.id_preferencia = p.id_preferencia LEFT JOIN tb_avaliacoes a ON u.id_usuario = a.id_usuario AND a.tipo = 'dislike' WHERE u.id_usuario = ?";
$stmt_user = $conn->prepare($sql_user);
$stmt_user->bind_param("i", $id_usuario);
$stmt_user->execute();
$result = $stmt_user->get_result();
$user_prefs_en = []; $disliked_videos = []; $lingua_db = '';
while ($row = $result->fetch_assoc()) {
    if (!empty($row['lingua'])) $lingua_db = $row['lingua'];
    if (!empty($row['preferencia']) && !in_array($row['preferencia'], $user_prefs_en)) $user_prefs_en[] = $row['preferencia'];
    if (!empty($row['video_id'])) $disliked_videos[] = $row['video_id'];
}
$stmt_user->close();

$lingua_key = strtolower(trim($lingua_db));
$lang_map = ['ingles' => ['iso' => 'en', 'region' => 'US', 'native' => 'english'], 'espanhol' => ['iso' => 'es', 'region' => 'ES', 'native' => 'español'], 'italiano' => ['iso' => 'it', 'region' => 'IT', 'native' => 'italiano'], 'frances' => ['iso' => 'fr', 'region' => 'FR', 'native' => 'français']];
$lang_config = $lang_map[$lingua_key] ?? $lang_map['ingles'];

$translation_map = [
    'es' => ['sports' => 'deportes', 'movies' => 'cine', 'music' => 'música', 'technology' => 'tecnología', 'gastronomy' => 'gastronomía', 'literature' => 'literatura', 'art' => 'arte', 'nature' => 'naturaleza', 'podcast' => 'podcast español', 'documentary' => 'documental', 'news' => 'noticias'],
    'fr' => ['sports' => 'sport', 'movies' => 'cinéma', 'music' => 'musique', 'technology' => 'technologie', 'gastronomy' => 'gastronomie', 'literature' => 'littérature', 'art' => 'art', 'nature' => 'nature', 'podcast' => 'podcast français', 'documentary' => 'documentaire', 'news' => 'actualités'],
    'it' => ['sports' => 'sport', 'movies' => 'cinema', 'music' => 'musica', 'technology' => 'tecnologia', 'gastronomy' => 'gastronomia', 'literature' => 'letteratura', 'art' => 'arte', 'nature' => 'natura', 'podcast' => 'podcast italiano', 'documentary' => 'documentario', 'news' => 'notizie']
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

$final_items = [];
$next_page_token = $pageToken;
$processed_ids = $disliked_videos;
$attempts = 0;

while (count($final_items) < VIDEOS_PER_PAGE && $attempts < 3) {
    $api_params = ['key' => $apiKey, 'part' => 'snippet', 'regionCode' => $lang_config['region'], 'relevanceLanguage' => $lang_config['iso'], 'maxResults' => 50, 'pageToken' => $next_page_token, 'type' => 'video'];
    
    if ($mode === 'search') {
        $api_params['q'] = $query;
    } else {
        $api_params['order'] = 'viewCount';
        switch ($type) {
            case 'music':
                $api_params += ['q' => $translation_map[$lang_iso]['music'] ?? 'music', 'videoCategoryId' => '10'];
                break;
            case 'podcasts':
                $api_params += ['q' => $translation_map[$lang_iso]['podcast'] ?? 'podcast', 'videoDuration' => 'long'];
                break;
            case 'shorts':
                $prefs_query = !empty($user_prefs_translated) ? implode('|', $user_prefs_translated) : 'interessante';
                $api_params += ['q' => "($prefs_query) #shorts", 'videoDuration' => 'short'];
                break;
            default: // Vídeos Longos
                $fallback_terms = [$translation_map[$lang_iso]['documentary'] ?? 'documentary', $translation_map[$lang_iso]['news'] ?? 'news'];
                $prefs_query = !empty($user_prefs_translated) ? implode('|', $user_prefs_translated) : implode('|', $fallback_terms);
                $api_params += ['q' => $prefs_query, 'videoDuration' => 'medium'];
                break;
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
    if (count($final_items) >= VIDEOS_PER_PAGE || is_null($next_page_token)) break;
}

echo json_encode(['items' => $final_items, 'nextPageToken' => $next_page_token]);