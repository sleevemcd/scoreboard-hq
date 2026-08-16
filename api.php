<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
  http_response_code(204);
  exit;
}

$dataDir = '/data';
if (!is_dir($dataDir)) {
  mkdir($dataDir, 0777, true);
}

$route = $_GET['route'] ?? '';
$method = $_SERVER['REQUEST_METHOD'];

function readJson($file, $default) {
  if (!file_exists($file)) return $default;
  $raw = file_get_contents($file);
  $data = json_decode($raw, true);
  return $data !== null ? $data : $default;
}

function writeJson($file, $data) {
  file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
}

switch ($route) {

  // ─── Player roster ───
  case 'roster':
    $file = "$dataDir/roster.json";
    if ($method === 'GET') {
      echo json_encode(readJson($file, ['players' => [], 'selected' => []]));
    } elseif ($method === 'POST') {
      $input = json_decode(file_get_contents('php://input'), true);
      if ($input && is_array($input)) {
        writeJson($file, $input);
        echo '{"ok":true}';
      } else {
        http_response_code(400);
        echo '{"error":"invalid JSON"}';
      }
    } else {
      http_response_code(405);
      echo '{"error":"method not allowed"}';
    }
    break;

  // ─── Game history ───
  case 'games':
    $file = "$dataDir/games.json";
    if ($method === 'GET') {
      echo json_encode(readJson($file, []));
    } elseif ($method === 'POST') {
      $input = json_decode(file_get_contents('php://input'), true);
      if ($input && is_array($input)) {
        $games = readJson($file, []);
        array_unshift($games, $input);
        if (count($games) > 50) $games = array_slice($games, 0, 50);
        writeJson($file, $games);
        echo '{"ok":true}';
      } else {
        http_response_code(400);
        echo '{"error":"invalid JSON"}';
      }
    } elseif ($method === 'DELETE') {
      $id = $_GET['id'] ?? null;
      if ($id) {
        $games = readJson($file, []);
        $games = array_values(array_filter($games, fn($g) => ($g['id'] ?? '') !== $id));
        writeJson($file, $games);
        echo '{"ok":true}';
      } else {
        http_response_code(400);
        echo '{"error":"missing id"}';
      }
    } else {
      http_response_code(405);
      echo '{"error":"method not allowed"}';
    }
    break;

  // ─── Health check ───
  case 'ping':
    echo '{"ok":true,"ts":' . time() . '}';
    break;

  default:
    http_response_code(404);
    echo '{"error":"unknown route"}';
}
