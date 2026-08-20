<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/cars.php';
require_once __DIR__ . '/../../form/inc/connect.php';

require_admin();

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
$car = get_car_by_id($id, true);

if (!$car) {
    header('Location: /cardealership/admin/cars.php');
    exit;
}

$message = '';
$error = '';

$fields = [
    'name', 'tagline', 'year', 'price', 'mileage', 'exterior_color',
    'interior_color', 'status', 'engine', 'horsepower', 'torque',
    'transmission', 'drivetrain', 'top_speed', 'zero_to_sixty',
    'features', 'description', 'description_long'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($fields as $field) {
        $$field = trim($_POST[$field] ?? '');
    }

    if ($name === '') {
        $error = 'Car name is required.';
    }

    $images = $car['images'] ?? [];

    if (!$error && isset($_POST['remove_image']) && is_array($_POST['remove_image'])) {
        foreach ($_POST['remove_image'] as $remove) {
            $remove = trim((string)$remove);
            $index = array_search($remove, $images, true);
            if ($index === false) {
                continue;
            }

            array_splice($images, $index, 1);

            if (str_starts_with($remove, 'uploads/cars/')) {
                $file = __DIR__ . '/../' . ltrim($remove, '/');
                if (is_file($file)) {
                    @unlink($file);
                }
            }
        }
    }

    if (!$error && isset($_FILES['new_images'])) {
        $upload = save_uploaded_car_images($_FILES['new_images']);
        $images = array_merge($images, $upload['paths']);

        if (!empty($upload['errors'])) {
            $error = implode(' ', $upload['errors']);
        }
    }

    if (!$error) {
        $jsonImages = json_encode(array_values($images), JSON_UNESCAPED_SLASHES);
        $stmt = $con->prepare(
            'UPDATE cars SET name=?, tagline=?, year=?, price=?, mileage=?, exterior_color=?, interior_color=?, status=?, engine=?, horsepower=?, torque=?, transmission=?, drivetrain=?, top_speed=?, zero_to_sixty=?, features=?, description=?, description_long=?, images=? WHERE id=?'
        );

        if (!$stmt) {
            $error = 'Could not prepare the database query.';
        } else {
            $stmt->bind_param(
                'sssssssssssssssssssi',
                $name,
                $tagline,
                $year,
                $price,
                $mileage,
                $exterior_color,
                $interior_color,
                $status,
                $engine,
                $horsepower,
                $torque,
                $transmission,
                $drivetrain,
                $top_speed,
                $zero_to_sixty,
                $features,
                $description,
                $description_long,
                $jsonImages,
                $id
            );

            if ($stmt->execute()) {
                $message = 'Car updated successfully.';
                $car = get_car_by_id($id, true);
            } else {
                $error = 'Could not update car: ' . $stmt->error;
            }
            $stmt->close();
        }
    }
}

render_page_start('Edit Car | Admin');
render_navbar('Admin');
?>
<div class="page">
    <div class="card">
        <div style="display:flex;justify-content:space-between;align-items:center;gap:16px;flex-wrap:wrap">
            <div>
                <h2>Edit Car</h2>
                <p class="muted">Update details, pictures and visibility.</p>
            </div>
            <a class="btn-secondary" href="cars.php">Back to Cars</a>
        </div>

        <?php if ($message): ?>
            <p class="success" style="margin-top:14px"><?php echo esc($message); ?></p>
        <?php endif; ?>

        <?php if ($error): ?>
            <p class="error" style="margin-top:14px"><?php echo esc($error); ?></p>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data" style="margin-top:20px">
            <input type="hidden" name="id" value="<?php echo (int)$id; ?>">

            <div class="grid grid-2">
                <?php
                $labels = [
                    'name' => 'Car Name',
                    'tagline' => 'Tagline',
                    'year' => 'Year',
                    'price' => 'Price',
                    'mileage' => 'Mileage',
                    'exterior_color' => 'Exterior Color',
                    'interior_color' => 'Interior Color',
                    'status' => 'Status',
                    'engine' => 'Engine',
                    'horsepower' => 'Horsepower',
                    'torque' => 'Torque',
                    'transmission' => 'Transmission',
                    'drivetrain' => 'Drivetrain',
                    'top_speed' => 'Top Speed',
                    'zero_to_sixty' => '0–60',
                ];
                foreach ($labels as $field => $label):
                    $value = $_POST[$field] ?? ($car[$field] ?? '');
                ?>
                    <div>
                        <label><?php echo esc($label); ?></label>
                        <input type="text" name="<?php echo esc($field); ?>" value="<?php echo esc($value); ?>">
                    </div>
                <?php endforeach; ?>
            </div>

            <div>
                <label>Features</label>
                <textarea name="features" rows="5"><?php echo esc($_POST['features'] ?? implode("\n", $car['features'] ?? [])); ?></textarea>
            </div>

            <div>
                <label>Description</label>
                <textarea name="description" rows="5"><?php echo esc($_POST['description'] ?? ($car['description'] ?? '')); ?></textarea>
            </div>

            <div>
                <label>Long Description</label>
                <textarea name="description_long" rows="7"><?php echo esc($_POST['description_long'] ?? ($car['description_long'] ?? '')); ?></textarea>
            </div>

            <?php if (!empty($car['images'])): ?>
                <div>
                    <label>Current Pictures</label>
                    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:14px;margin-top:10px">
                        <?php foreach ($car['images'] as $image): ?>
                            <div>
                                <img src="<?php echo esc(car_image_url($image)); ?>" alt="" style="width:100%;height:120px;object-fit:cover;border-radius:14px;margin-bottom:7px">
                                <label style="font-weight:400;display:block">
                                    <input type="checkbox" name="remove_image[]" value="<?php echo esc($image); ?>">
                                    Remove this picture
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php else: ?>
                <p class="muted">This car currently has no picture. Upload one below and it will become the car's picture.</p>
            <?php endif; ?>

            <div>
                <label><?php echo !empty($car['images']) ? 'Add More Pictures' : 'Upload Picture'; ?></label>
                <input type="file" name="new_images[]" accept="image/jpeg,image/png,image/webp" multiple>
                <p class="muted" style="margin-top:6px">JPG, PNG or WebP, up to 8 MB each.</p>
            </div>

            <div class="actions">
                <button class="btn-primary" type="submit">Save Changes</button>
                <a class="btn-secondary" href="/cardealership/car-details.php?id=<?php echo (int)$id; ?>" target="_blank">View Details</a>
            </div>
        </form>
    </div>
</div>
<?php render_page_end(); ?>
