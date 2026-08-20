<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/cars.php';
require_once __DIR__ . '/../../form/inc/connect.php';
require_admin();

$message=''; $error='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $fields=['name','tagline','year','price','mileage','exterior_color','interior_color','status','engine','horsepower','torque','transmission','drivetrain','top_speed','zero_to_sixty','features','description','description_long'];
    foreach($fields as $f) $$f=trim($_POST[$f]??'');
    if($name==='') $error='Car name is required.';
    $images = [];
    if (!$error && isset($_FILES['images'])) {
        $upload = save_uploaded_car_images($_FILES['images']);
        $images = $upload['paths'];
        if (!empty($upload['errors'])) {
            $error = implode(' ', $upload['errors']);
        }
    }
    if(!$error){
        $newId = next_car_id($con);
        $jsonImages=json_encode($images, JSON_UNESCAPED_SLASHES);
        $stmt=$con->prepare('INSERT INTO cars (id,name,tagline,year,price,mileage,exterior_color,interior_color,status,engine,horsepower,torque,transmission,drivetrain,top_speed,zero_to_sixty,features,description,description_long,images,created_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,NOW())');
        if($stmt){
            $types='i'.str_repeat('s',19);
            $stmt->bind_param($types,$newId,$name,$tagline,$year,$price,$mileage,$exterior_color,$interior_color,$status,$engine,$horsepower,$torque,$transmission,$drivetrain,$top_speed,$zero_to_sixty,$features,$description,$description_long,$jsonImages);
            if($stmt->execute()) $message='Car added successfully.'; else $error='Could not add car: '.$stmt->error;
            $stmt->close();
        } else $error='Could not prepare the database query.';
    }
}
render_page_start('Add Car | Admin'); render_navbar('Admin');
echo '<div class="page">'; echo '<div class="card">'; echo '<h2>Add New Car</h2><p class="muted">Add a vehicle and its pictures to the inventory.</p>';
if($message) echo '<p class="success" style="margin-top:14px">'.esc($message).'</p>';
if($error) echo '<p class="error" style="margin-top:14px">'.esc($error).'</p>';
echo '<form method="post" enctype="multipart/form-data" style="margin-top:20px">';
echo '<div class="grid grid-2">';
function add_car_input($name,$label){ echo '<div><label>'.esc($label).'</label><input type="text" name="'.esc($name).'" value="'.esc($_POST[$name]??'').'"></div>'; }
foreach(['name'=>'Car Name *','tagline'=>'Tagline','year'=>'Year','price'=>'Price','mileage'=>'Mileage','exterior_color'=>'Exterior Color','interior_color'=>'Interior Color','status'=>'Status','engine'=>'Engine','horsepower'=>'Horsepower','torque'=>'Torque','transmission'=>'Transmission','drivetrain'=>'Drivetrain','top_speed'=>'Top Speed','zero_to_sixty'=>'0–60'] as $n=>$l) add_car_input($n,$l);
echo '</div>';
echo '<div><label>Features</label><textarea name="features" rows="5" placeholder="One feature per line">'.esc($_POST['features']??'').'</textarea></div>';
echo '<div><label>Description</label><textarea name="description" rows="5">'.esc($_POST['description']??'').'</textarea></div>';
echo '<div><label>Long Description</label><textarea name="description_long" rows="7">'.esc($_POST['description_long']??'').'</textarea></div>';
echo '<div><label>Pictures</label><input type="file" name="images[]" accept="image/jpeg,image/png,image/webp" multiple><p class="muted" style="margin-top:6px">Pictures are optional. JPG, PNG or WebP, up to 8 MB each. You can add them later from Edit Car.</p></div>';
echo '<div class="actions"><button class="btn-primary" type="submit">Add Car</button><a class="btn-secondary" href="cars.php">Manage Cars</a></div>';
echo '</form></div></div>'; render_page_end();
