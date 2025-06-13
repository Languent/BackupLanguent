<?php
header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['error' => 'Usuário não autenticado']);
    exit;
}

$id_usuario = $_SESSION['id_usuario'];
require_once 'conection.php';

// 1. Obtém as preferências do usuário (em inglês, como antes)
$sql_preferencias = "SELECT p.preferencia FROM tb_usuario_preferencia up
                                JOIN tb_preferencias p ON up.id_preferencia = p.id_preferencia
                                WHERE up.id_usuario = ?";
$stmt_prefs = $conn->prepare($sql_preferencias);
$stmt_prefs->bind_param("i", $id_usuario);
$stmt_prefs->execute();
$result_prefs = $stmt_prefs->get_result();
$preferences_en = [];
while ($row = $result_prefs->fetch_assoc()) {
    $preferences_en[] = $row['preferencia'];
}
$stmt_prefs->close();

// Se o usuário não tiver preferências, não há nada a fazer.
if (empty($preferences_en)) {
    echo json_encode(['preferences' => [], 'translated_preferences' => []]);
    exit;
}

// 2. Obtém o idioma preferido do usuário
$sql_lingua = "SELECT l.lingua FROM tb_usuario u
                    JOIN tb_lingua l ON u.id_lingua = l.id_lingua
                    WHERE u.id_usuario = ?";
$stmt_lingua = $conn->prepare($sql_lingua);
$stmt_lingua->bind_param("i", $id_usuario);
$stmt_lingua->execute();
$stmt_lingua->bind_result($lingua_db);
$stmt_lingua->fetch();
$stmt_lingua->close();
$conn->close();

// 3. Lógica de tradução (agora dentro deste mesmo arquivo)
$lingua_key = strtolower(trim($lingua_db ?: 'ingles'));
$lang_iso = ['ingles' => 'en', 'espanhol' => 'es', 'italiano' => 'it', 'frances' => 'fr'][$lingua_key] ?? 'en';

$translation_map = [
    'es' => ['sports' => 'deportes fitness', 'history' => 'historia biografías', 'science' => 'ciencia tecnología', 'travel' => 'viajes culturas', 'gastronomy' => 'gastronomía cocina', 'art' => 'arte literatura', 'nature' => 'naturaleza documental', 'education' => 'educación curiosidades'],
    'fr' => ['sports' => 'sport fitness', 'history' => 'histoire biographie', 'science' => 'science technologie', 'travel' => 'voyage cultures', 'gastronomy' => 'gastronomie cuisine', 'art' => 'art littérature', 'nature' => 'nature documentaire', 'education' => 'éducation curiosités'],
    'it' => ['sports' => 'sport fitness', 'history' => 'storia biografie', 'science' => 'scienza tecnologia', 'travel' => 'viaggi culture', 'gastronomy' => 'gastronomia cucina', 'art' => 'arte letteratura', 'nature' => 'natura documentario', 'education' => 'educazione curiosità']
];

$preferences_translated = [];
if ($lang_iso !== 'en' && isset($translation_map[$lang_iso])) {
    foreach ($preferences_en as $pref) {
        $preferences_translated[] = $translation_map[$lang_iso][$pref] ?? $pref;
    }
} else {
    $preferences_translated = $preferences_en;
}

// 4. Retorna um JSON completo com tudo que o frontend precisa
echo json_encode([
    'preferences' => $preferences_en, // As preferências originais em inglês
    'translated_preferences' => $preferences_translated // As preferências traduzidas
]);