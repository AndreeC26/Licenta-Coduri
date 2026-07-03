<?php
session_start();

if(!isset($_SESSION['user'])){
    header("Location: main.php");
    exit;
}

include "db.php";

$query = "
SELECT 
    Cod_Produs,
    SUM(Nr_Bucati) AS Total_Vandute
FROM comenzi
GROUP BY Cod_Produs
ORDER BY Total_Vandute DESC
LIMIT 5
";

$result = mysqli_query($conn, $query);

$produse = [];
$totaluri = [];

while($row = mysqli_fetch_assoc($result)){
    $produse[] = $row['Cod_Produs'];
    $totaluri[] = $row['Total_Vandute'];
}
?>

<!DOCTYPE html>
<html lang="ro">
<head>
<meta charset="UTF-8">
<title>Statistici Produse</title>
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
        <h2>Top Produse Vandute</h2>
    </div>
</div>

<div class="stats-container compact-stats">

    <div class="stats-card compact-card">

        <h3>Top 5 produse dupa numarul de bucati vandute</h3>

        <div class="chart-box">
            <canvas id="produseChart"></canvas>
        </div>

    </div>

</div>

<a href="manager.php" class="cancel-btn">Cancel</a>

<script>
const produse = <?php echo json_encode($produse); ?>;
const totaluri = <?php echo json_encode($totaluri); ?>;

const ctx = document.getElementById('produseChart');

new Chart(ctx, {
    type: 'bar',
    data: {
        labels: produse,
        datasets: [{
            label: 'Bucăți vândute',
            data: totaluri,
            borderWidth: 1
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
                    precision: 0
                }
            }
        }
    }
});
</script>

</body>
</html>