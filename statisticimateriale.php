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

$materialSelectat = isset($_GET['material'])
    ? $_GET['material']
    : 'Toate';

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


$queryMateriale = "
SELECT DISTINCT Material
FROM materiale
ORDER BY Material ASC
";

$resultMateriale = mysqli_query($conn, $queryMateriale);


$conditieMaterial = "";

if($materialSelectat != 'Toate'){
    $conditieMaterial = " AND m.Material = '$materialSelectat' ";
}


$query = "
SELECT 
    DATE_FORMAT(c.Data, '%d-%m-%Y') AS Zi,
    SUM(c.Pret_Produs) AS Venit_Zi
FROM comenzi c
JOIN produse p ON c.Cod_Produs = p.Cod_Produs
JOIN materiale m ON p.Cod_Material = m.Cod_Material
WHERE YEAR(c.Data) = '$anCurent'
AND MONTH(c.Data) = '$lunaSelectata'
$conditieMaterial
GROUP BY DATE(c.Data)
ORDER BY DATE(c.Data) ASC
";

$result = mysqli_query($conn, $query);

$zile = [];
$venituri = [];

$totalLuna = 0;
$ceaMaiBunaZi = "-";
$venitMaximZi = 0;

while($row = mysqli_fetch_assoc($result)){

    $zile[] = $row['Zi'];
    $venituri[] = $row['Venit_Zi'];

    $totalLuna += $row['Venit_Zi'];

    if($row['Venit_Zi'] > $venitMaximZi){
        $venitMaximZi = $row['Venit_Zi'];
        $ceaMaiBunaZi = $row['Zi'];
    }
}


$queryTopMaterial = "
SELECT 
    m.Material,
    SUM(c.Pret_Produs) AS Total_Material
FROM comenzi c
JOIN produse p ON c.Cod_Produs = p.Cod_Produs
JOIN materiale m ON p.Cod_Material = m.Cod_Material
WHERE YEAR(c.Data) = '$anCurent'
AND MONTH(c.Data) = '$lunaSelectata'
GROUP BY m.Material
ORDER BY Total_Material DESC
LIMIT 1
";

$resultTop = mysqli_query($conn, $queryTopMaterial);
$top = mysqli_fetch_assoc($resultTop);

$materialTop = $top ? $top['Material'] : "-";
$venitTop = $top ? $top['Total_Material'] : 0;
?>

<!DOCTYPE html>
<html lang="ro">

<head>
<meta charset="UTF-8">
<title>Statistici Materiale</title>
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
        <h2>Statistici Materiale</h2>
    </div>

</div>


<div class="stats-container compact-stats">

    <div class="stats-card compact-card">

        <h3>
            Încasări materiale -
            <?php echo $luniNume[$lunaSelectata]." ".$anCurent; ?>
        </h3>

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


            <label class="luna-label" style="margin-left:15px;">
                Selectează materialul:
            </label>

            <select
                name="material"
                class="luna-select"
                onchange="this.form.submit()"
            >

                <option
                    value="Toate"
                    <?php if($materialSelectat == 'Toate') echo "selected"; ?>
                >
                    Toate materialele
                </option>

                <?php while($m = mysqli_fetch_assoc($resultMateriale)){ ?>

                    <option
                        value="<?php echo $m['Material']; ?>"
                        <?php if($m['Material'] == $materialSelectat) echo "selected"; ?>
                    >
                        <?php echo $m['Material']; ?>
                    </option>

                <?php } ?>

            </select>

        </form>


        <div class="stats-summary">

            <div class="summary-box">
                <span>Total încasări selecție</span>
                <strong><?php echo number_format($totalLuna, 2); ?> lei</strong>
            </div>

            <div class="summary-box">
                <span>Cea mai bună zi</span>
                <strong><?php echo $ceaMaiBunaZi; ?></strong>
                <small><?php echo number_format($venitMaximZi, 2); ?> lei</small>
            </div>

            <div class="summary-box best-box">
                <span>Material top în luna aleasă</span>
                <strong><?php echo $materialTop; ?></strong>
                <small><?php echo number_format($venitTop, 2); ?> lei</small>
            </div>

        </div>


        <div class="chart-box venituri-chart-box">
            <canvas id="materialeChart"></canvas>
        </div>

    </div>

</div>

<a href="manager.php" class="cancel-btn">Cancel</a>


<script>
const zile = <?php echo json_encode($zile); ?>;
const venituri = <?php echo json_encode($venituri); ?>;

new Chart(document.getElementById('materialeChart'), {
    type: 'bar',
    data: {
        labels: zile,
        datasets: [{
            label: 'Încasări',
            data: venituri,
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