<?php
session_start();

if(!isset($_SESSION['user'])){
    header("Location: main.php");
    exit;
}

include "db.php";

$anCurent = date('Y');

$lunaSelectata = isset($_GET['luna'])
    ? $_GET['luna']
    : date('m');

$luniNume = [
    '01' => 'Ianuarie',
    '02' => 'Februarie',
    '03' => 'Martie',
    '04' => 'Aprilie',
    '05' => 'Mai',
    '06' => 'Iunie',
    '07' => 'Iulie',
    '08' => 'August',
    '09' => 'Septembrie',
    '10' => 'Octombrie',
    '11' => 'Noiembrie',
    '12' => 'Decembrie'
];


$queryLuni = "
SELECT 
    DATE_FORMAT(Data, '%m') AS Luna,
    DATE_FORMAT(Data, '%m-%Y') AS Luna_Afisare,
    SUM(Pret_Produs) AS Venit_Total
FROM comenzi
WHERE YEAR(Data) = '$anCurent'
GROUP BY DATE_FORMAT(Data, '%m'), DATE_FORMAT(Data, '%m-%Y')
ORDER BY Luna ASC
";

$resultLuni = mysqli_query($conn, $queryLuni);

$luni = [];
$venituriLuni = [];

$totalGeneral = 0;
$ceaMaiBunaLuna = "-";
$maxVenitLuna = 0;

while($row = mysqli_fetch_assoc($resultLuni)){

    $luni[] = $row['Luna_Afisare'];
    $venituriLuni[] = $row['Venit_Total'];

    $totalGeneral += $row['Venit_Total'];

    if($row['Venit_Total'] > $maxVenitLuna){
        $maxVenitLuna = $row['Venit_Total'];
        $ceaMaiBunaLuna = $row['Luna_Afisare'];
    }
}

$queryZile = "
SELECT 
    DATE_FORMAT(Data, '%d-%m-%Y') AS Zi,
    SUM(Pret_Produs) AS Venit_Zi
FROM comenzi
WHERE YEAR(Data) = '$anCurent'
AND MONTH(Data) = '$lunaSelectata'
GROUP BY DATE(Data)
ORDER BY DATE(Data) ASC
";

$resultZile = mysqli_query($conn, $queryZile);

$zile = [];
$venituriZile = [];

$totalLuna = 0;
$ceaMaiBunaZi = "-";
$maxVenitZi = 0;

while($row = mysqli_fetch_assoc($resultZile)){

    $zile[] = $row['Zi'];
    $venituriZile[] = $row['Venit_Zi'];

    $totalLuna += $row['Venit_Zi'];

    if($row['Venit_Zi'] > $maxVenitZi){
        $maxVenitZi = $row['Venit_Zi'];
        $ceaMaiBunaZi = $row['Zi'];
    }
}
?>

<!DOCTYPE html>
<html lang="ro">

<head>
<meta charset="UTF-8">
<title>Statistici Venituri</title>
<link rel="stylesheet" href="style.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body class="admin-page">

<div class="admin-top">

    <div class="admin-brand">

        <div class="admin-logo-row">

            <img src="logo.png" class="admin-logo">

            <div>
                <h1>Marmurar de Ruschita</h1>
                <p>Eleganță naturală, sculptată în piatră</p>
            </div>

        </div>

    </div>

    <div class="admin-title">
        <h2>Statistici Venituri</h2>
    </div>

</div>

<div class="stats-container compact-stats">

    <div class="stats-card compact-card">

        <h3>Venituri pentru anul <?php echo $anCurent; ?></h3>

        <form method="GET" class="luna-form">

            <label class="luna-label">
                Selectează luna:
            </label>

            <select
                name="luna"
                class="luna-select"
                onchange="this.form.submit()"
            >

                <?php foreach($luniNume as $numar => $nume){ ?>

                    <option
                        value="<?php echo $numar; ?>"
                        <?php if($numar == $lunaSelectata) echo "selected"; ?>
                    >
                        <?php echo $nume." ".$anCurent; ?>
                    </option>

                <?php } ?>

            </select>

        </form>

        <div class="stats-summary">

            <div class="summary-box">
                <span>Total venituri an curent</span>
                <strong><?php echo number_format($totalGeneral, 2); ?> lei</strong>
            </div>

            <div class="summary-box best-box">
                <span>Cea mai bună lună</span>
                <strong><?php echo $ceaMaiBunaLuna; ?></strong>
                <small><?php echo number_format($maxVenitLuna, 2); ?> lei</small>
            </div>

            <div class="summary-box">
                <span>Total luna selectată</span>
                <strong><?php echo number_format($totalLuna, 2); ?> lei</strong>
            </div>

            <div class="summary-box">
                <span>Cea mai bună zi</span>
                <strong><?php echo $ceaMaiBunaZi; ?></strong>
                <small><?php echo number_format($maxVenitZi, 2); ?> lei</small>
            </div>

        </div>

        <div class="charts-grid">

            <div class="small-chart-box">
                <h4>Venituri pe luni</h4>
                <canvas id="luniChart"></canvas>
            </div>

            <div class="small-chart-box">
                <h4>Venituri pe zile în luna selectată</h4>
                <canvas id="zileChart"></canvas>
            </div>

        </div>

    </div>

</div>

<a href="manager.php" class="cancel-btn">Cancel</a>

<script>
const luni = <?php echo json_encode($luni); ?>;
const venituriLuni = <?php echo json_encode($venituriLuni); ?>;

const zile = <?php echo json_encode($zile); ?>;
const venituriZile = <?php echo json_encode($venituriZile); ?>;

new Chart(document.getElementById('luniChart'), {
    type: 'line',
    data: {
        labels: luni,
        datasets: [{
            label: 'Venit lunar',
            data: venituriLuni,
            fill: true,
            tension: 0.4,
            borderWidth: 3,
            pointRadius: 5
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,

        plugins: {
            legend: {
                display: false
            }
        },

        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value){
                        return value + ' lei';
                    }
                }
            }
        }
    }
});

new Chart(document.getElementById('zileChart'), {
    type: 'bar',
    data: {
        labels: zile,
        datasets: [{
            label: 'Venit zilnic',
            data: venituriZile,
            borderWidth: 1,
            borderRadius: 8
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,

        plugins: {
            legend: {
                display: false
            }
        },

        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value){
                        return value + ' lei';
                    }
                }
            }
        }
    }
});
</script>

</body>
</html>