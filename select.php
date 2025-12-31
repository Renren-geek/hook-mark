<?php
// DB接続設定 
try {
    $db_host = "mysql3112.db.sakura.ne.jp";
    $db_name = "renren_hook_db";
    $db_user = "renren_hook_db";
    $db_pass = "renren0613";

    $dsn = "mysql:host={$db_host};dbname={$db_name};charset=utf8mb4";

    $pdo = new PDO($dsn, $db_user, $db_pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, 
        PDO::ATTR_EMULATE_PREPARES => false,        
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC 
    ]);
} catch (PDOException $e) {
    exit('DBConnectError: ' . $e->getMessage());
}

// データ取得（最新30件のみ表示させる）
try {
    $stmt = $pdo->prepare("SELECT * FROM gs_hook_table ORDER BY indate ASC LIMIT 30");
    $status = $stmt->execute();
} catch (PDOException $e) {
    exit("ErrorQuery: " . $e->getMessage());
}

$values = $stmt->fetchAll();

// グラフ用データの加工
$dates = [];
$velocities = [];
foreach ($values as $v) {
    $dates[] = $v["indate"];
    $velocities[] = $v["velocity"];
}
$count = count($values);
$max_v = $count > 0 ? max($velocities) : 0;
$avg_v = $count > 0 ? array_sum($velocities) / $count : 0;
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="utf-8">
    <title>フックマークDB - 拳の戦績</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            background-color: #1a1a1a;
            color: #fff;
            font-family: "Hiragino Mincho ProN", serif;
        }

        .container {
            margin-top: 30px;
        }

        h1 {
            color: #e60000;
            border-bottom: 2px solid #d4af37;
            padding-bottom: 10px;
        }

        .stats-box {
            background: #222;
            border: 1px solid #d4af37;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 30px;
            text-align: center;
        }

        .stats-val {
            font-size: 2.5rem;
            color: #ff0000;
            font-weight: bold;
        }

        .chart-container {
            background: #fff;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 30px;
        }

        .table {
            color: #fff;
            background: #333;
        }

        .btn-training {
            background: #e60000;
            color: white;
            margin-bottom: 20px;
            padding: 10px 30px;
            border: none;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>修行の軌跡</h1>
        <a href="index.php" class="btn btn-training">≪ 道場（測定）へ戻る</a>

        <div class="row">
            <div class="col-md-4">
                <div class="stats-box">
                    <p>通算拳数</p>
                    <div class="stats-val"><?= h($count) ?> <small>発</small></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-box">
                    <p>最高拳速</p>
                    <div class="stats-val"><?= h($max_v) ?> <small>km/h</small></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-box">
                    <p>平均拳速</p>
                    <div class="stats-val"><?= h(round($avg_v, 1)) ?> <small>km/h</small></div>
                </div>
            </div>
        </div>

        <div class="chart-container">
            <canvas id="velocityChart"></canvas>
        </div>

        <h2>修行の足跡（履歴）</h2>
        <table class="table table-striped">
            <tr>
                <th>日時</th>
                <th>速度</th>
                <th>評価</th>
            </tr>
            <?php foreach ($values as $v) { ?>
                <tr>
                    <td><?= h($v["indate"]) ?></td>
                    <td><?= h($v["velocity"]) ?> km/h</td>
                    <td><?= h($v["comment"]) ?></td>
                </tr>
            <?php } ?>
        </table>
    </div>

    <?php
    function h($str)
    {
        return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
    }
    ?>

    <script>
        const ctx = document.getElementById('velocityChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?= json_encode($dates) ?>,
                datasets: [{
                    label: '拳速推移 (km/h)',
                    data: <?= json_encode($velocities) ?>,
                    borderColor: '#ff0000',
                    backgroundColor: 'rgba(230, 0, 0, 0.2)',
                    borderWidth: 3,
                    tension: 0.1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: false
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    </script>
</body>

</html>