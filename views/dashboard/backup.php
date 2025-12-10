<?php
if ($_SESSION['role'] !== 'admin') exit('Access Denied');

$db = (new Database())->getConnection();
$backupDir = __DIR__ . '/../../backups/'; 
if (!file_exists($backupDir)) mkdir($backupDir, 0755, true);

// --- HELPER: GENERATE SQL ---
function getSqlContent($db) {
    $content = "-- BACKUP DATABASE " . Config::SITE_NAME . "\n";
    $content .= "-- Date: " . date('Y-m-d H:i:s') . "\n\n";
    $content .= "SET FOREIGN_KEY_CHECKS = 0;\n\n";
    
    $tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    foreach ($tables as $table) {
        $create = $db->query("SHOW CREATE TABLE $table")->fetch(PDO::FETCH_NUM);
        $content .= "DROP TABLE IF EXISTS `$table`;\n" . $create[1] . ";\n\n";
        
        $rows = $db->query("SELECT * FROM $table")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as $row) {
            $vals = array_map(fn($x) => $x === null ? "NULL" : $db->quote($x), array_values($row));
            $content .= "INSERT INTO `$table` VALUES (" . implode(", ", $vals) . ");\n";
        }
        $content .= "\n";
    }
    $content .= "SET FOREIGN_KEY_CHECKS = 1;\n";
    return $content;
}

// --- HANDLERS ---
if (isset($_POST['create_backup'])) {
    $filename = 'db_backup_' . date('Y-m-d_H-i-s') . '.sql';
    try {
        file_put_contents($backupDir . $filename, getSqlContent($db));
        setFlash('success', 'Berhasil', 'Backup baru dibuat.');
    } catch (Exception $e) { setFlash('error', 'Gagal', $e->getMessage()); }
    header("Location: /dashboard/backup"); exit;
}

if (isset($_POST['delete_file'])) {
    $file = $backupDir . basename($_POST['delete_file']);
    if (file_exists($file)) unlink($file);
    setFlash('success', 'Dihapus', 'File backup dihapus.');
    header("Location: /dashboard/backup"); exit;
}

if (isset($_GET['download'])) {
    $file = $backupDir . basename($_GET['download']);
    if (file_exists($file)) {
        header('Content-Type: application/octet-stream');
        header("Content-disposition: attachment; filename=\"" . basename($file) . "\"");
        readfile($file); exit;
    }
}

if (isset($_POST['restore_file'])) {
    $file = $backupDir . basename($_POST['restore_file']);
    if (file_exists($file)) {
        try {
            $db->exec("SET FOREIGN_KEY_CHECKS = 0");
            $queries = explode(";\n", file_get_contents($file));
            foreach ($queries as $query) { if (trim($query)) $db->exec($query); }
            $db->exec("SET FOREIGN_KEY_CHECKS = 1");
            setFlash('success', 'Sukses', 'Database berhasil dipulihkan!');
        } catch (Exception $e) { setFlash('error', 'Error', $e->getMessage()); }
    }
    header("Location: /dashboard/backup"); exit;
}

// LIST FILE
$files = glob($backupDir . '*.sql');
rsort($files);
?>

<div class="header-flex">
    <div>
        <h2 class="page-title">💾 Manajemen Backup</h2>
        <p style="color:var(--text-muted)">Cadangkan dan pulihkan data server.</p>
    </div>
    <form method="POST">
        <button type="submit" name="create_backup" class="btn btn-primary">
            <i class="ri-add-circle-line"></i> Backup Baru
        </button>
    </form>
</div>

<div class="card">
    <table class="datatable display" style="width:100%">
        <thead>
            <tr>
                <th>Nama File</th>
                <th>Ukuran</th>
                <th>Tanggal</th>
                <th style="width: 150px;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($files as $f): ?>
            <tr>
                <td style="font-weight:bold; color:#fff;"><?= basename($f) ?></td>
                <td style="color:#4ade80;"><?= round(filesize($f) / 1024, 2) ?> KB</td>
                <td style="color:#9ca3af;"><?= date('d M Y H:i', filemtime($f)) ?></td>
                <td>
                    <div style="display:flex; gap:8px;">
                        <form method="POST" onsubmit="return confirm('⚠️ PERINGATAN: Database akan DITIMPA. Lanjutkan?');">
                            <input type="hidden" name="restore_file" value="<?= basename($f) ?>">
                            <button class="btn btn-sm btn-secondary" style="color:#ffd700; border-color:#ffd700;" title="Restore">
                                <i class="ri-refresh-line"></i>
                            </button>
                        </form>

                        <a href="/dashboard/backup&download=<?= basename($f) ?>" class="btn btn-sm btn-secondary" title="Download">
                            <i class="ri-download-line"></i>
                        </a>

                        <form method="POST" onsubmit="return confirm('Hapus file ini?');">
                            <input type="hidden" name="delete_file" value="<?= basename($f) ?>">
                            <button class="btn btn-sm btn-secondary" style="color:#ef4444; border-color:#ef4444;" title="Hapus">
                                <i class="ri-delete-bin-line"></i>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
    </table>
</div>