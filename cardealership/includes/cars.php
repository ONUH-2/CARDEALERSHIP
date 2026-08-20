<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../../form/inc/connect.php';

function normalize_car(array $car): array {
    $images = [];
    if (array_key_exists('images', $car)) {
        $raw = $car['images'];
        if (is_array($raw)) {
            $images = $raw;
        } else {
            $raw = trim((string)$raw);
            if ($raw !== '') {
                $decoded = json_decode($raw, true);
                if (is_array($decoded)) {
                    $images = $decoded;
                } else {
                    $images = preg_split('/\s*,\s*/', $raw);
                }
            }
        }
    }

    $features = [];
    if (array_key_exists('features', $car)) {
        $raw = $car['features'];
        if (is_array($raw)) {
            $features = $raw;
        } else {
            $raw = trim((string)$raw);
            if ($raw !== '') {
                $decoded = json_decode($raw, true);
                $features = is_array($decoded)
                    ? $decoded
                    : preg_split('/\r\n|\r|\n/', $raw);
            }
        }
    }

    $car['images'] = array_values(array_filter(array_map(
        static fn($value) => trim((string)$value),
        $images
    )));
    $car['features'] = array_values(array_filter(array_map(
        static fn($value) => trim((string)$value),
        $features
    )));

    return $car;
}

function starter_inventory_cars(): array {
    static $cars = null;
    if ($cars !== null) {
        return $cars;
    }

    $cars = [];
    $file = __DIR__ . '/../cars_data.php';
    if (is_file($file)) {
        $data = include $file;
        if (is_array($data)) {
            foreach ($data as $car) {
                if (is_array($car)) {
                    $cars[] = normalize_car($car);
                }
            }
        }
    }

    return $cars;
}

function get_database_cars(bool $includeHidden = false): array {
    global $con;
    if (!isset($con) || $con === false) {
        return [];
    }

    $sql = $includeHidden
        ? 'SELECT * FROM cars ORDER BY created_at DESC, id DESC'
        : "SELECT * FROM cars WHERE status IS NULL OR TRIM(status) = '' OR LOWER(status) <> 'hidden' ORDER BY created_at DESC, id DESC";

    $result = $con->query($sql);
    if (!$result) {
        return [];
    }

    $cars = [];
    while ($row = $result->fetch_assoc()) {
        $cars[] = normalize_car($row);
    }

    return $cars;
}

function get_all_cars(bool $includeHidden = false): array {
    // Every car - starter inventory or admin-added - now lives in the
    // database as a real, editable row. No more hardcoded merge.
    return get_database_cars($includeHidden);
}

function get_car_by_id(int $id, bool $includeHidden = false): ?array {
    global $con;
    if ($id <= 0) {
        return null;
    }

    if (isset($con) && $con !== false) {
        $sql = $includeHidden
            ? 'SELECT * FROM cars WHERE id = ? LIMIT 1'
            : "SELECT * FROM cars WHERE id = ? AND (status IS NULL OR TRIM(status) = '' OR LOWER(status) <> 'hidden') LIMIT 1";

        $stmt = $con->prepare($sql);
        if ($stmt) {
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $car = ($result && $result->num_rows > 0) ? $result->fetch_assoc() : null;
            $stmt->close();

            if ($car) {
                return normalize_car($car);
            }
        }
    }

    return null;
}

function car_image_url(string $image): string {
    $image = trim($image);
    if ($image === '') {
        return '/cardealership/cardealership/images/placeholder.svg';
    }
    if (preg_match('#^https?://#i', $image)) {
        return $image;
    }
    return '/cardealership/cardealership/' . ltrim($image, '/');
}

function first_car_image(array $car): string {
    return !empty($car['images'][0])
        ? (string)$car['images'][0]
        : 'images/placeholder.svg';
}

/**
 * Downscale an image file in place so it fits within the given max
 * dimensions, preserving aspect ratio (no stretching, no cropping).
 * Images already smaller than the max dimensions are left untouched.
 * Requires the GD extension; if GD isn't available the original
 * uploaded file is simply left as-is.
 */
function resize_car_image_if_needed(string $path, string $mime, int $maxWidth = 1600, int $maxHeight = 1200): void {
    if (!extension_loaded('gd')) {
        return;
    }

    $info = @getimagesize($path);
    if ($info === false) {
        return;
    }

    [$width, $height] = $info;
    if ($width <= 0 || $height <= 0) {
        return;
    }

    // Already fits, nothing to do (never upscale).
    if ($width <= $maxWidth && $height <= $maxHeight) {
        return;
    }

    $scale = min($maxWidth / $width, $maxHeight / $height);
    $newWidth = max(1, (int)round($width * $scale));
    $newHeight = max(1, (int)round($height * $scale));

    switch ($mime) {
        case 'image/jpeg':
            $source = @imagecreatefromjpeg($path);
            break;
        case 'image/png':
            $source = @imagecreatefrompng($path);
            break;
        case 'image/webp':
            $source = function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($path) : false;
            break;
        default:
            $source = false;
    }

    if (!$source) {
        return;
    }

    $resized = imagecreatetruecolor($newWidth, $newHeight);

    if ($mime === 'image/png' || $mime === 'image/webp') {
        imagealphablending($resized, false);
        imagesavealpha($resized, true);
        $transparent = imagecolorallocatealpha($resized, 0, 0, 0, 127);
        imagefilledrectangle($resized, 0, 0, $newWidth, $newHeight, $transparent);
    }

    imagecopyresampled($resized, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

    switch ($mime) {
        case 'image/jpeg':
            imagejpeg($resized, $path, 85);
            break;
        case 'image/png':
            imagepng($resized, $path, 6);
            break;
        case 'image/webp':
            if (function_exists('imagewebp')) {
                imagewebp($resized, $path, 85);
            }
            break;
    }

    imagedestroy($source);
    imagedestroy($resized);
}

/**
 * Determine a safe id for a new car row.
 *
 * Some live database setups do not have AUTO_INCREMENT enabled on
 * cars.id. When that's the case, inserting without an id makes every new
 * row default to id 0 - the first insert "succeeds" but permanently
 * occupies id 0, every insert after that fails with a duplicate-key
 * error, and a car stuck at id 0 can never be opened, edited, viewed on
 * its details page, or bought (this whole app treats id <= 0 as "not a
 * real car"). To work correctly no matter how the table was created,
 * every new car is given an explicit id here - always one higher than
 * the highest id currently used in the table.
 */
function next_car_id($con): int {
    $next = 1;
    if ($con && $con !== false) {
        $result = @$con->query('SELECT MAX(id) AS max_id FROM cars');
        if ($result) {
            $row = $result->fetch_assoc();
            $maxId = isset($row['max_id']) ? (int)$row['max_id'] : 0;
            if ($maxId + 1 > $next) {
                $next = $maxId + 1;
            }
        }
    }
    return $next;
}

/**
 * One-time helper for the "Import Starter Inventory" admin action: insert
 * the demo cars from cars_data.php as real rows in the cars table (each
 * with an explicit, never-colliding id), skipping any name that's already
 * present so the button is safe to click more than once. Returns how many
 * rows were inserted.
 */
function import_starter_inventory($con): int {
    if (!$con || $con === false) {
        return 0;
    }

    $existingNames = [];
    $result = @$con->query('SELECT name FROM cars');
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $existingNames[strtolower(trim((string)$row['name']))] = true;
        }
    }

    $inserted = 0;
    $stmt = $con->prepare('INSERT INTO cars (id,name,tagline,year,price,mileage,exterior_color,interior_color,status,engine,horsepower,torque,transmission,drivetrain,top_speed,zero_to_sixty,features,description,description_long,images,created_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,NOW())');
    if (!$stmt) {
        return 0;
    }

    foreach (starter_inventory_cars() as $car) {
        $nameKey = strtolower(trim((string)($car['name'] ?? '')));
        if ($nameKey === '' || isset($existingNames[$nameKey])) {
            continue;
        }

        $id = next_car_id($con);
        $jsonImages = json_encode($car['images'] ?? [], JSON_UNESCAPED_SLASHES);
        $jsonFeatures = json_encode($car['features'] ?? [], JSON_UNESCAPED_SLASHES);
        $status = $car['status'] ?? 'In Stock';

        $stmt->bind_param(
            'isssssssssssssssssss',
            $id,
            $car['name'],
            $car['tagline'],
            $car['year'],
            $car['price'],
            $car['mileage'],
            $car['exterior_color'],
            $car['interior_color'],
            $status,
            $car['engine'],
            $car['horsepower'],
            $car['torque'],
            $car['transmission'],
            $car['drivetrain'],
            $car['top_speed'],
            $car['zero_to_sixty'],
            $jsonFeatures,
            $car['description'],
            $car['description_long'],
            $jsonImages
        );

        if ($stmt->execute()) {
            $inserted++;
            $existingNames[$nameKey] = true;
        }
    }

    $stmt->close();
    return $inserted;
}

function save_uploaded_car_images(array $fileData): array {
    $uploaded = [];
    $errors = [];

    if (!isset($fileData['name']) || !is_array($fileData['name'])) {
        return ['paths' => [], 'errors' => []];
    }

    $uploadDir = __DIR__ . '/../uploads/cars/';
    if (!is_dir($uploadDir) && !@mkdir($uploadDir, 0755, true) && !is_dir($uploadDir)) {
        return ['paths' => [], 'errors' => ['The image upload folder could not be created.']];
    }

    $maxBytes = 8 * 1024 * 1024;
    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    foreach ($fileData['name'] as $i => $originalName) {
        $errorCode = (int)($fileData['error'][$i] ?? UPLOAD_ERR_NO_FILE);
        if ($errorCode === UPLOAD_ERR_NO_FILE) {
            continue;
        }
        if ($errorCode !== UPLOAD_ERR_OK) {
            $errors[] = 'Could not upload ' . (string)$originalName . ' (upload error ' . $errorCode . ').';
            continue;
        }

        $tmpName = $fileData['tmp_name'][$i] ?? '';
        if ($tmpName === '' || !is_uploaded_file($tmpName)) {
            $errors[] = 'The uploaded file ' . (string)$originalName . ' was not received correctly.';
            continue;
        }

        if (($fileData['size'][$i] ?? 0) > $maxBytes) {
            $errors[] = (string)$originalName . ' is larger than 8 MB.';
            continue;
        }

        $imageInfo = @getimagesize($tmpName);
        if ($imageInfo === false || empty($imageInfo['mime']) || !isset($allowed[$imageInfo['mime']])) {
            $errors[] = (string)$originalName . ' is not a supported image. Use JPG, PNG, or WebP.';
            continue;
        }

        $extension = $allowed[$imageInfo['mime']];
        $fileName = 'car_' . bin2hex(random_bytes(10)) . '.' . $extension;
        $destination = $uploadDir . $fileName;

        if (!move_uploaded_file($tmpName, $destination)) {
            $errors[] = 'The server could not save ' . (string)$originalName . '.';
            continue;
        }

        // Automatically downscale oversized images so they always fit
        // the existing inventory image container, preserving aspect ratio.
        resize_car_image_if_needed($destination, $imageInfo['mime']);

        $uploaded[] = 'uploads/cars/' . $fileName;
    }

    return ['paths' => $uploaded, 'errors' => $errors];
}
