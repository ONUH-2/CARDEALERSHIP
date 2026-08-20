<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/cars.php';
require_once __DIR__ . '/../../form/inc/connect.php';
require_admin();

$message=''; $error='';
if(isset($_GET['import'])){
    $count = import_starter_inventory($con);
    header('Location: /cardealership/admin/cars.php?imported=' . $count); exit;
}
if(isset($_GET['hide'])){
    $id=(int)$_GET['hide'];
    $st=$con->prepare('UPDATE cars SET status=IF(status="Hidden","In Stock","Hidden") WHERE id=?');
    if($st){$st->bind_param('i',$id);$st->execute();$st->close();}
    header('Location: /cardealership/admin/cars.php'); exit;
}
if(isset($_GET['delete'])){
    $id=(int)$_GET['delete'];
    $car=get_car_by_id($id,true);
    if($car && $con && $con!==false){
        $st=$con->prepare('DELETE FROM cars WHERE id=?');
        if($st){$st->bind_param('i',$id);$st->execute();$st->close();}
        foreach(($car['images']??[]) as $img){
            if(str_starts_with($img,'uploads/cars/')) { $file=__DIR__.'/../'.$img; if(is_file($file)) @unlink($file); }
        }
    }
    header('Location: /cardealership/admin/cars.php'); exit;
}
$cars=get_all_cars(true);
render_page_start('Manage Cars | Admin'); render_navbar('Admin');
echo '<div class="page"><div class="card">';
if(isset($_GET['imported'])){
    $n=(int)$_GET['imported'];
    echo '<p class="success" style="margin-bottom:16px">'.($n>0 ? "Imported $n starter car(s) into inventory." : 'No new starter cars to import — they\'re already in your inventory.').'</p>';
}
echo '<div style="display:flex;justify-content:space-between;align-items:center;gap:16px;flex-wrap:wrap"><div><h2>Manage Cars</h2><p class="muted">Edit, hide, restore or delete vehicles from the inventory.</p></div><div class="actions"><a class="btn-secondary" href="?import=1" onclick="return confirm(\'Import the 15 starter dealership cars as real, editable inventory rows?\')">Import Starter Inventory</a><a class="btn-primary" href="add-car.php">+ Add Car</a></div></div>';
echo '<div class="table-wrap" style="margin-top:20px"><table><thead><tr><th>Car</th><th>Price</th><th>Status</th><th>Actions</th></tr></thead><tbody>';
if(!$cars) echo '<tr><td colspan="4">No cars found.</td></tr>'; else foreach($cars as $car){ $status=$car['status']??'In Stock'; echo '<tr><td><strong>'.esc($car['name']).'</strong><br><span class="muted">'.esc($car['year']).'</span></td><td>'.esc($car['price']).'</td><td><span class="pill">'.esc($status).'</span></td><td><div class="actions"><a class="btn-secondary" href="edit-car.php?id='.(int)$car['id'].'">Edit</a><a class="btn-secondary" href="?hide='.(int)$car['id'].'">'.($status==='Hidden'?'Restore':'Hide').'</a><a class="btn-danger" href="?delete='.(int)$car['id'].'" onclick="return confirm(\'Delete this car permanently?\')">Delete</a></div></td></tr>'; }
echo '</tbody></table></div></div></div>'; render_page_end();
