<?php

require __DIR__ . '/PHP-Minecraft-Query-master/src/MinecraftQuery.php';
require __DIR__ . '/PHP-Minecraft-Query-master/src/MinecraftQueryException.php';

use xPaw\MinecraftQuery;
use xPaw\MinecraftQueryException;

$query = new MinecraftQuery();
$statsDir = 'C:\Users\ServidorMinecraft\Desktop\KingdomsPostMods\world\stats';
$playersStats = [];
$connectedPlayers = 0;

function getPlayerNameFromUUID($uuid) {
    $uuid = str_replace('-', '', $uuid);
    $url = "https://sessionserver.mojang.com/session/minecraft/profile/{$uuid}";
    $response = @file_get_contents($url);

    if ($response) {
        $data = json_decode($response, true);
        if (isset($data['name'])) {
            return $data['name'];
        }
    }
    return '';
}

function getServerStatus($host, $port) {
    try {
        $socket = @fsockopen($host, $port, $errno, $errstr, 2);
        if ($socket) {
            fclose($socket);
            $query = new MinecraftQuery();
            
            try {
                $query->Connect($host, $port, 2);
                $info = $query->GetInfo();
                $connectedPlayers = $info['Players'] ?? 0;
                return ['Activo', $connectedPlayers];
            } catch (MinecraftQueryException $e) {
                // Si falla la consulta pero el puerto está abierto
                return ['Activo', 0];
            }
        } else {
            return ['Inactivo', 0];
        }
    } catch (Exception $e) {
        return ['Error: ' . $e->getMessage(), 0];
    }
}

$serverStatus = getServerStatus('teamrocket.serveminecraft.net', 7697);

// Leer estadísticas de jugadores desde archivos JSON
if (is_dir($statsDir)) {
    foreach (glob($statsDir . '/*.json') as $statsFile) {
        $uuid = basename($statsFile, '.json');
        $data = json_decode(file_get_contents($statsFile), true);

        if ($data) {
            $playerName = getPlayerNameFromUUID($uuid);
            
            $deaths = $data['stats']['minecraft:custom']['minecraft:deaths'] ?? 0;
            $totalWorldTimeTicks = $data['stats']['minecraft:custom']['minecraft:play_time'] ?? 0;
            $timeSinceLastDeath = $data['stats']['minecraft:custom']['minecraft:time_since_death'] ?? 0;
            $damageTaken = $data['stats']['minecraft:custom']['minecraft:damage_taken'] ?? 0;

            $timePlayedHours = round($totalWorldTimeTicks / 20 / 3600, 2);
            $hoursSinceLastDeath = round($timeSinceLastDeath / 20 / 3600, 2);

            $playersStats[] = [
                'player_name' => $playerName,
                'deaths' => $deaths,
                'time_played' => $timePlayedHours,
                'tdum' => $hoursSinceLastDeath,
                'dmg_taken' => $damageTaken,
            ];
        }
    }
}

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Team Rocket Minecraft Server</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <style>
        body {
            background-color: #121212;
            color: #f0f0f0;
        }

        /* Tarjetas con animación, bordes blancos y sombra */
        .card {
            background-color: #1f1f1f;
            border: 1px solid #ffffff33;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.5);
            color: #f0f0f0;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.7);
        }

        .main-card {
            box-shadow: 0 4px 10px rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            background-color: #1f1f1f;
            padding: 30px;
        }

        /* Tabla con estilo similar a las tarjetas */
        .table-container {
            overflow-x: auto;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.5);
            border: 1px solid #ffffff33;
            border-radius: 10px;
            padding: 10px;
            background-color: #1f1f1f;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .table-container:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.7);
        }

        .table {
            background-color: transparent;
            margin-bottom: 0;
            border-collapse: collapse;
        }

        .table th,
        .table td {
            border-left: none;
            border-right: none;
            border-bottom: none;
            text-align: center;
        }

        /* Encabezado gris oscuro */
        .table th {
            background-color: #2a2a2a;
            color: white;
            text-align: center;
        }

        .table th:hover {
            cursor: pointer;
        }

        /* Alternar colores: tarjetas (#1f1f1f) y secundario (#2a2a2a) */
        .table tbody tr:nth-of-type(odd) td {
            background-color: #1f1f1f !important;

        }

        .table tbody tr:nth-of-type(even) td {
            background-color: #2a2a2a !important;
        }

        .table td {
            color: #d3d3d3;
        }

        /* Sombra en los textos */
        h1,
        h2,
        h5,
        p,
        th,
        td {
            text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.7);
        }

        /* Botones y enlaces */
        a {
            color: #007bff;
        }

        a:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>
    <div class="container my-5">
        <div class="main-card">
            <h1 class="text-center mb-4">Servidor Oficial de TEAM ROCKET</h1>
            <div class="row g-4">
                
                <div class="col-md-6">
                    <div class="card text-center">
                        <div class="card-body">
                            <h5 class="card-title">Estado del Servidor</h5>
                            <p class="card-text"><?php echo $serverStatus[0]; ?></p>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card text-center">
                        <div class="card-body">
                            <h5 class="card-title">IP del Servidor</h5>
                            <p class="card-text">teamrocket.serveminecraft.net:7697</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card text-center">
                        <div class="card-body">
                            <h5 class="card-title">Modpack del Servidor</h5>
                            <a href="https://drive.google.com/drive/folders/1kFJ4PRredQxD3jgsHttNwqBHjf0FzRWz?usp=sharing"
                                class="card-link">Modpack Oficial de TeamRocket</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card text-center">
                        <div class="card-body">
                            <h5 class="card-title">Jugadores Conectados</h5>
                            <p class="card-text"><?php echo $serverStatus[1]; ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabla de estadísticas -->
            <h2 class="text-center mt-5">Estadísticas de Jugadores</h2>
            <div class="table-container mt-3">
                <?php if (!empty($playersStats)): ?>
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th onclick="sortTable(0)">Jugador</th>
                                <th onclick="sortTable(1, true)">Muertes</th>
                                <th onclick="sortTable(2, true)">Horas Jugadas</th>
                                <th onclick="sortTable(3, true)">TDUM</th>
                                <th onclick="sortTable(4, true)">Daño Recibido</th>
                            </tr>
                        </thead>
                        <tbody id="statsTable">
                            <?php foreach ($playersStats as $player): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($player['player_name']); ?></td>
                                    <td><?php echo htmlspecialchars($player['deaths']); ?></td>
                                    <td><?php echo htmlspecialchars($player['time_played']); ?></td>
                                    <td><?php echo htmlspecialchars($player['tdum']); ?></td>
                                    <td><?php echo htmlspecialchars($player['dmg_taken']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p class="text-center">No se encontraron estadísticas para los jugadores.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        let sortOrder = {};

        function sortTable(columnIndex, isNumeric = false) {
            const table = document.getElementById("statsTable");
            const rows = Array.from(table.rows);

            const currentOrder = sortOrder[columnIndex] === "asc" ? "desc" : "asc";
            sortOrder[columnIndex] = currentOrder;

            rows.sort((a, b) => {
                let aText = a.cells[columnIndex].innerText;
                let bText = b.cells[columnIndex].innerText;

                if (isNumeric) {
                    return currentOrder === "asc"
                        ? parseFloat(aText) - parseFloat(bText)
                        : parseFloat(bText) - parseFloat(aText);
                } else {
                    return currentOrder === "asc"
                        ? aText.localeCompare(bText)
                        : bText.localeCompare(aText);
                }
            });

            rows.forEach(row => table.appendChild(row));
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>