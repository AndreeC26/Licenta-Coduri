<?php
session_start();

if(!isset($_SESSION['user'])){
    header("Location: main.php");
    exit;
}

include "db.php";

$sort = isset($_GET['sort']) ? $_GET['sort'] : 'Cod_Produs';
$order = isset($_GET['order']) ? $_GET['order'] : 'ASC';

$allowed_columns = [
    'Cod_Produs',
    'Tip_Produs',
    'Cod_Material',
    'Material',
    'Denumire',
    'Culoare',
    'Pret',
    'Dimensiune',
    'Stoc',
    'Manopera'
];

if(!in_array($sort, $allowed_columns)){
    $sort = 'Cod_Produs';
}

if($order !== 'ASC' && $order !== 'DESC'){
    $order = 'ASC';
}

if(isset($_GET['live_search'])){
    $q = isset($_GET['q']) ? trim($_GET['q']) : '';

    $sql = "
    SELECT produse.Cod_Produs, produse.Tip_Produs, produse.Cod_Material,
           materiale.Material, materiale.Denumire, materiale.Culoare,
           materiale.Pret, materiale.Dimensiune, materiale.Stoc, materiale.Manopera
    FROM produse
    LEFT JOIN materiale ON produse.Cod_Material = materiale.Cod_Material
    WHERE produse.Cod_Produs LIKE '%$q%'
       OR produse.Tip_Produs LIKE '%$q%'
       OR produse.Cod_Material LIKE '%$q%'
       OR materiale.Material LIKE '%$q%'
       OR materiale.Denumire LIKE '%$q%'
    ORDER BY $sort $order
    ";

    $result = mysqli_query($conn, $sql);

    while($row = mysqli_fetch_assoc($result)){
        echo "<tr>
                <td class='cod'>{$row['Cod_Produs']}</td>
                <td class='tip'>{$row['Tip_Produs']}</td>
                <td class='cod_material'>{$row['Cod_Material']}</td>
                <td class='material'>{$row['Material']}</td>
                <td class='denumire'>{$row['Denumire']}</td>
                <td class='culoare'>{$row['Culoare']}</td>
                <td class='pret'>{$row['Pret']}</td>
                <td class='dimensiune'>{$row['Dimensiune']}</td>
                <td class='stoc'>{$row['Stoc']}</td>
                <td class='manopera'>{$row['Manopera']}</td>
                <td class='actions'>
                    <button type='button' class='edit-btn'>Edit</button>
                </td>
              </tr>";
    }
    exit;
}

if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax_update'])){

    $Cod_Produs   = $_POST['Cod_Produs'];
    $Tip_Produs   = $_POST['tip'];
    $Cod_Material = $_POST['cod_material'];
    $Material     = $_POST['material'];
    $Denumire     = $_POST['denumire'];
    $Culoare      = $_POST['culoare'];
    $Pret         = $_POST['pret'];
    $Dimensiune   = $_POST['dimensiune'];
    $Stoc         = $_POST['stoc'];
    $Manopera     = $_POST['manopera'];

    mysqli_query($conn, "
        UPDATE produse
        SET Tip_Produs='$Tip_Produs'
        WHERE Cod_Produs='$Cod_Produs'
    ");

    mysqli_query($conn, "
        UPDATE materiale
        SET Material='$Material',
            Denumire='$Denumire',
            Culoare='$Culoare',
            Pret='$Pret',
            Dimensiune='$Dimensiune',
            Stoc='$Stoc',
            Manopera='$Manopera'
        WHERE Cod_Material='$Cod_Material'
    ");

    echo json_encode(['status'=>'success']);
    exit;
}

$query = "
SELECT produse.Cod_Produs, produse.Tip_Produs, produse.Cod_Material,
       materiale.Material, materiale.Denumire, materiale.Culoare,
       materiale.Pret, materiale.Dimensiune, materiale.Stoc, materiale.Manopera
FROM produse
LEFT JOIN materiale ON produse.Cod_Material = materiale.Cod_Material
ORDER BY $sort $order
";

$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="ro">
<head>
<meta charset="UTF-8">
<title>Admin Produse</title>
<link rel="stylesheet" href="style.css">
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
        <h2>Lista Produse</h2>
    </div>

</div>


<div class="search-container">
    <input type="text" id="smart-search" placeholder="Cautare produse...">

    <div class="button-group">
        <button onclick="window.location.href='insert.php'">Insert</button>
        <button onclick="window.location.href='delete.php'">Delete</button>
        <button onclick="window.location.href='logout.php'">Logout</button>
    </div>
</div>

<div class="table-container">
<table id="products-table">

<thead>
<tr>

    <th class="cod-col">
        Cod_Produs
        <span class="sort-icons">
            <span onclick="sortTable('Cod_Produs','ASC')">▲</span>
            <span onclick="sortTable('Cod_Produs','DESC')">▼</span>
        </span>
    </th>

    <th>
        Tip_Produs
        <span class="sort-icons">
            <span onclick="sortTable('Tip_Produs','ASC')">▲</span>
            <span onclick="sortTable('Tip_Produs','DESC')">▼</span>
        </span>
    </th>

    <th class="codmat-col">
        Cod_Material
        <span class="sort-icons">
            <span onclick="sortTable('Cod_Material','ASC')">▲</span>
            <span onclick="sortTable('Cod_Material','DESC')">▼</span>
        </span>
    </th>

    <th>
        Material
        <span class="sort-icons">
            <span onclick="sortTable('Material','ASC')">▲</span>
            <span onclick="sortTable('Material','DESC')">▼</span>
        </span>
    </th>

    <th>
        Denumire
        <span class="sort-icons">
            <span onclick="sortTable('Denumire','ASC')">▲</span>
            <span onclick="sortTable('Denumire','DESC')">▼</span>
        </span>
    </th>

    <th>
        Culoare
        <span class="sort-icons">
            <span onclick="sortTable('Culoare','ASC')">▲</span>
            <span onclick="sortTable('Culoare','DESC')">▼</span>
        </span>
    </th>

    <th>
        Pret
        <span class="sort-icons">
            <span onclick="sortTable('Pret','ASC')">▲</span>
            <span onclick="sortTable('Pret','DESC')">▼</span>
        </span>
    </th>

    <th>
        Dimensiune
        <span class="sort-icons">
            <span onclick="sortTable('Dimensiune','ASC')">▲</span>
            <span onclick="sortTable('Dimensiune','DESC')">▼</span>
        </span>
    </th>

    <th>
        Stoc
        <span class="sort-icons">
            <span onclick="sortTable('Stoc','ASC')">▲</span>
            <span onclick="sortTable('Stoc','DESC')">▼</span>
        </span>
    </th>

    <th>
        Manopera
        <span class="sort-icons">
            <span onclick="sortTable('Manopera','ASC')">▲</span>
            <span onclick="sortTable('Manopera','DESC')">▼</span>
        </span>
    </th>

    <th>Actiuni</th>

</tr>
</thead>
<tbody>
<?php while($row = mysqli_fetch_assoc($result)) { ?>
<tr>

    <td class="cod"><?php echo $row['Cod_Produs']; ?></td>
    <td class="tip"><?php echo $row['Tip_Produs']; ?></td>
    <td class="cod_material"><?php echo $row['Cod_Material']; ?></td>
    <td class="material"><?php echo $row['Material']; ?></td>
    <td class="denumire"><?php echo $row['Denumire']; ?></td>
    <td class="culoare"><?php echo $row['Culoare']; ?></td>
    <td class="pret"><?php echo $row['Pret']; ?></td>
    <td class="dimensiune"><?php echo $row['Dimensiune']; ?></td>
    <td class="stoc"><?php echo $row['Stoc']; ?></td>
    <td class="manopera"><?php echo $row['Manopera']; ?></td>

    <td class="actions">
        <button type="button" class="edit-btn">Edit</button>
    </td>

</tr>
<?php } ?>
</tbody>

</table>
</div>

<script>

function sortTable(column, order){
    window.location.href = `admin.php?sort=${column}&order=${order}`;
}

const searchInput = document.getElementById('smart-search');
const tableBody = document.querySelector('#products-table tbody');

searchInput.addEventListener('keyup', () => {
    const text = searchInput.value;

    fetch(`admin.php?live_search=1&q=${encodeURIComponent(text)}&sort=<?php echo $sort; ?>&order=<?php echo $order; ?>`)
    .then(res => res.text())
    .then(data => tableBody.innerHTML = data);
});

document.addEventListener('click', function(e){

    const btn = e.target;

    if(btn.classList.contains('edit-btn')){

        const row = btn.closest('tr');

        const fields = ['tip','cod_material','material','denumire','culoare','pret','dimensiune','stoc','manopera'];

        fields.forEach(cls => {
            const cell = row.querySelector(`.${cls}`);
            const value = cell.innerText;
            cell.innerHTML = `<input type="text" value="${value}">`;
        });

        btn.classList.remove('edit-btn');
        btn.classList.add('save-btn');
        btn.innerText = 'Save';
    }

    else if(btn.classList.contains('save-btn')){

        const row = btn.closest('tr');
        const Cod_Produs = row.querySelector('.cod').innerText;

        const fields = ['tip','cod_material','material','denumire','culoare','pret','dimensiune','stoc','manopera'];

        const data = {
            ajax_update: 1,
            Cod_Produs: Cod_Produs
        };

        fields.forEach(cls => {
            data[cls] = row.querySelector(`.${cls} input`).value;
        });

        fetch('admin.php', {
            method: 'POST',
            headers: {'Content-Type':'application/x-www-form-urlencoded'},
            body: new URLSearchParams(data)
        })
        .then(res => res.json())
        .then(resp => {

            if(resp.status === 'success'){

                fields.forEach(cls => {
                    const cell = row.querySelector(`.${cls}`);
                    cell.innerText = data[cls];
                });

                btn.classList.remove('save-btn');
                btn.classList.add('edit-btn');
                btn.innerText = 'Edit';

            } else {
                alert('Eroare la salvare!');
            }
        });
    }

});

</script>

</body>
</html>