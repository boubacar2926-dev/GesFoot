<?php
require_once ROOT_PATH . '/config/database.php';

// ============================================================
// SESSION
// ============================================================
function startSession(): void {
    if (session_status() === PHP_SESSION_NONE) {
        ini_set('session.cookie_httponly', 1);
        ini_set('session.use_strict_mode', 1);
        session_name('fc_session');
        session_start();
    }
}

// ============================================================
// AUTH
// ============================================================
function isLoggedIn(): bool {
    startSession();
    return !empty($_SESSION['user_id']);
}

function currentUser(): ?array {
    startSession();
    return $_SESSION['user'] ?? null;
}

function currentRole(): ?string {
    startSession();
    return $_SESSION['role'] ?? null;
}

function requireLogin(): void {
    if (!isLoggedIn()) {
        redirect('/auth/login.php');
    }
}

function requireRole(string|array $roles): void {
    requireLogin();
    $allowed = is_array($roles) ? $roles : [$roles];
    if (!in_array(currentRole(), $allowed, true)) {
        setFlash('error', 'Accès non autorisé.');
        redirect('/index.php');
    }
}

// ============================================================
// NAVIGATION
// ============================================================
function redirect(string $path): never {
    header('Location: ' . BASE_URL . $path);
    exit;
}

// ============================================================
// CSRF
// ============================================================
function csrfToken(): string {
    startSession();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrfField(): string {
    return '<input type="hidden" name="csrf_token" value="' . csrfToken() . '">';
}

function csrfVerify(): void {
    startSession();
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(403);
        die('Token CSRF invalide.');
    }
}

// ============================================================
// FLASH MESSAGES
// ============================================================
function setFlash(string $type, string $message): void {
    startSession();
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function renderFlash(): string {
    startSession();
    if (empty($_SESSION['flash'])) return '';
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    $map = ['success' => 'success', 'error' => 'danger', 'warning' => 'warning', 'info' => 'info'];
    $cls = $map[$flash['type']] ?? 'info';
    $icon = ['success' => 'check-circle-fill', 'danger' => 'x-circle-fill', 'warning' => 'exclamation-triangle-fill', 'info' => 'info-circle-fill'][$cls] ?? 'info-circle-fill';
    return sprintf(
        '<div class="alert alert-%s alert-dismissible fade show d-flex align-items-center gap-2" role="alert">
            <i class="bi bi-%s"></i>
            <span>%s</span>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>',
        $cls, $icon, htmlspecialchars($flash['message'])
    );
}

// ============================================================
// HELPERS
// ============================================================
function e(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function formatDate(?string $date): string {
    if (!$date) return '—';
    return date('d/m/Y', strtotime($date));
}

function formatTime(?string $time): string {
    if (!$time) return '—';
    return substr($time, 0, 5);
}

function age(?string $dateNaissance): string {
    if (!$dateNaissance) return '—';
    return (new DateTime($dateNaissance))->diff(new DateTime())->y . ' ans';
}

function statusBadge(string $status): string {
    $map = [
        'actif'      => 'success',
        'inactif'    => 'secondary',
        'blessé'     => 'warning',
        'suspendu'   => 'danger',
        'Programmé'  => 'primary',
        'Terminé'    => 'success',
        'En cours'   => 'warning',
        'Annulé'     => 'danger',
        'Reporté'    => 'secondary',
        'Publiée'    => 'success',
        'Brouillon'  => 'secondary',
        'Convoqué'   => 'success',
        'Absent'     => 'danger',
        'Blessé'     => 'warning',
        'Titulaire'  => 'primary',
        'Remplaçant' => 'secondary',
    ];
    $color = $map[$status] ?? 'secondary';
    return "<span class=\"badge bg-$color\">" . e($status) . '</span>';
}

function roleBadge(string $role): string {
    $map = ['admin' => 'danger', 'coach' => 'primary', 'staff' => 'warning', 'joueur' => 'success'];
    $labels = ['admin' => 'Admin', 'coach' => 'Coach', 'staff' => 'Staff', 'joueur' => 'Joueur'];
    $color = $map[$role] ?? 'secondary';
    $label = $labels[$role] ?? $role;
    return "<span class=\"badge bg-$color\">" . e($label) . '</span>';
}

function avatarUrl(?string $photo, string $name = '?'): string {
    if ($photo && file_exists(UPLOAD_PATH . '/' . $photo)) {
        return UPLOAD_URL . '/' . e($photo);
    }
    $initials = urlencode(strtoupper(substr($name, 0, 1)));
    return "https://ui-avatars.com/api/?name=$initials&background=2d6a4f&color=fff&size=80";
}

function uploadPhoto(array $file, string $subfolder = 'photos'): ?string {
    if ($file['error'] !== UPLOAD_ERR_OK) return null;
    $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime  = $finfo->file($file['tmp_name']);
    if (!in_array($mime, $allowed, true)) return null;
    if ($file['size'] > 2 * 1024 * 1024) return null; // max 2 Mo

    $dir = UPLOAD_PATH . '/' . $subfolder;
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    // Extension dérivée du MIME vérifié, jamais du nom client
    $mimeToExt = ['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp','image/gif'=>'gif'];
    $ext      = $mimeToExt[$mime];
    $filename = $subfolder . '/' . bin2hex(random_bytes(8)) . '.' . $ext;
    move_uploaded_file($file['tmp_name'], UPLOAD_PATH . '/' . $filename);
    return $filename;
}

function paginate(int $total, int $perPage, int $currentPage, string $url): string {
    $pages = (int)ceil($total / $perPage);
    if ($pages <= 1) return '';
    $html = '<nav><ul class="pagination pagination-sm mb-0">';
    for ($i = 1; $i <= $pages; $i++) {
        $active = $i === $currentPage ? 'active' : '';
        $html .= "<li class=\"page-item $active\"><a class=\"page-link\" href=\"{$url}&page=$i\">$i</a></li>";
    }
    return $html . '</ul></nav>';
}
