<?php
function e(?string $value): string {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function old(string $key): string {
    return e($_POST[$key] ?? '');
}

function vehicle_image(?string $image): string {
    return $image ?: 'https://images.unsplash.com/photo-1553440569-bcc63803a83d?auto=format&fit=crop&w=1200&q=85';
}
?>
