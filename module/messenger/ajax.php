<?php
require __DIR__ . '/common.php';
header('Content-Type: application/json; charset=utf-8');

$me = ms_identity();
if (!$me) { http_response_code(401); echo json_encode(['error' => 'Not logged in']); exit; }
[$mt, $mid] = $me;
$act = $_POST['action'] ?? '';

q("REPLACE INTO tblmsg_presence (m_type,m_id,last_seen) VALUES (?,?,NOW())", [$mt, $mid]);

function parse_key($k) { $p = explode(':', (string)$k); return (count($p) == 2 && in_array($p[0], ['U','S','G'], true)) ? [$p[0], (int)$p[1]] : null; }
function in_group($g, $t, $i) { return (bool)q("SELECT 1 FROM tblmsg_members WHERE group_id=? AND m_type=? AND m_id=?", [$g, $t, $i])->fetch(); }
function dm_where() { return "group_id IS NULL AND ((sender_type=:mt AND sender_id=:mid AND receiver_type=:ot AND receiver_id=:oid) OR (sender_type=:ot AND sender_id=:oid AND receiver_type=:mt AND receiver_id=:mid))"; }
function conv_where($c) {
    global $mt, $mid;
    if ($c[0] === 'G') return ["group_id=:g", [':g' => $c[1]]];
    return [dm_where(), [':mt' => $mt, ':mid' => $mid, ':ot' => $c[0], ':oid' => $c[1]]];
}
function reply_info($id) {
    global $mt, $mid;
    $r = q("SELECT sender_type,sender_id,body,file_name,deleted FROM tblmessages WHERE id=?", [$id])->fetch();
    if (!$r) return null;
    $mine = $r['sender_type'] === $mt && (int)$r['sender_id'] === $mid;
    $txt = $r['deleted'] ? 'Message removed' : ($r['body'] !== '' ? mb_substr($r['body'], 0, 120) : '📎 ' . $r['file_name']);
    return ['id' => (int)$id, 'sender' => $mine ? 'You' : ms_person($r['sender_type'], $r['sender_id'])['name'], 'text' => $txt];
}
function preview($m) {
    if (!$m) return '';
    if ($m['deleted']) return 'Message removed';
    return $m['body'] !== '' ? $m['body'] : ($m['file_name'] ? '📎 ' . $m['file_name'] : '');
}

try {
switch ($act) {

case 'contacts':
    $rows = [];
    $people = [];
    foreach (q("SELECT UID id, DISPLAYNAME name, TYPE role FROM tblusers WHERE STATUSACTIVE=1") as $r) $people[] = ['t' => 'U'] + $r;
    foreach (q("SELECT S_ID id, CONCAT(FNAME,' ',LNAME) name, 'Student' role FROM tblstudent WHERE STATUS='Active'") as $r) $people[] = ['t' => 'S'] + $r;
    foreach ($people as $p) {
        if ($p['t'] === $mt && (int)$p['id'] === $mid) continue;
        $a = [':mt' => $mt, ':mid' => $mid, ':ot' => $p['t'], ':oid' => $p['id']];
        $last = q("SELECT body,file_name,deleted,created_at FROM tblmessages WHERE " . dm_where() . " ORDER BY id DESC LIMIT 1", $a)->fetch();
        $un = (int)q("SELECT COUNT(*) FROM tblmessages WHERE group_id IS NULL AND sender_type=? AND sender_id=? AND receiver_type=? AND receiver_id=? AND read_at IS NULL AND deleted=0",
                     [$p['t'], $p['id'], $mt, $mid])->fetchColumn();
        $on = (bool)q("SELECT 1 FROM tblmsg_presence WHERE m_type=? AND m_id=? AND last_seen > NOW() - INTERVAL 30 SECOND", [$p['t'], $p['id']])->fetch();
        $rows[] = ['key' => $p['t'] . ':' . $p['id'], 'kind' => 'person', 'name' => $p['name'], 'role' => $p['role'],
                   'preview' => preview($last), 'time' => $last['created_at'] ?? null, 'unread' => $un, 'online' => $on];
    }
    foreach (q("SELECT g.id,g.name,m.last_read_id, (SELECT COUNT(*) FROM tblmsg_members x WHERE x.group_id=g.id) cnt
                FROM tblmsg_groups g JOIN tblmsg_members m ON m.group_id=g.id AND m.m_type=? AND m.m_id=?", [$mt, $mid]) as $g) {
        $last = q("SELECT body,file_name,deleted,created_at FROM tblmessages WHERE group_id=? ORDER BY id DESC LIMIT 1", [$g['id']])->fetch();
        $un = (int)q("SELECT COUNT(*) FROM tblmessages WHERE group_id=? AND id>? AND NOT (sender_type=? AND sender_id=?) AND deleted=0",
                     [$g['id'], $g['last_read_id'], $mt, $mid])->fetchColumn();
        $rows[] = ['key' => 'G:' . $g['id'], 'kind' => 'group', 'name' => $g['name'], 'role' => $g['cnt'] . ' members',
                   'preview' => preview($last), 'time' => $last['created_at'] ?? null, 'unread' => $un, 'online' => false];
    }
    usort($rows, fn($a, $b) => [$b['time'] ?? '', $a['name']] <=> [$a['time'] ?? '', $b['name']]);
    echo json_encode(['contacts' => $rows]);
    break;

case 'messages':
    $c = parse_key($_POST['conv'] ?? ''); $after = (int)($_POST['after'] ?? 0);
    if (!$c) throw new Exception('Bad conversation');
    if ($c[0] === 'G') {
        if (!in_group($c[1], $mt, $mid)) throw new Exception('Not a member');
        $where = "group_id=:g"; $a = [':g' => $c[1]];
    } else {
        $where = dm_where(); $a = [':mt' => $mt, ':mid' => $mid, ':ot' => $c[0], ':oid' => $c[1]];
    }
    $sql = "SELECT * FROM (SELECT * FROM tblmessages WHERE $where " . ($after ? "AND id>:after ORDER BY id ASC LIMIT 200" : "ORDER BY id DESC LIMIT 100") . ") t ORDER BY id ASC";
    if ($after) $a[':after'] = $after;
    $out = [];
    foreach (q($sql, $a) as $m) {
        $mine = $m['sender_type'] === $mt && (int)$m['sender_id'] === $mid;
        $out[] = ['id' => (int)$m['id'], 'mine' => $mine, 'sender' => $mine ? 'You' : ms_person($m['sender_type'], $m['sender_id'])['name'],
                  'body' => $m['deleted'] ? '' : $m['body'], 'deleted' => (bool)$m['deleted'],
                  'file' => ($m['file_path'] && !$m['deleted']) ? ['name' => $m['file_name'], 'url' => MS_UPLOAD_URL . $m['file_path'],
                            'image' => (bool)preg_match('/\.(png|jpe?g|gif|webp)$/i', $m['file_path'])] : null,
                  'reply' => $m['reply_to'] ? reply_info($m['reply_to']) : null,
                  'time' => date('c', strtotime($m['created_at']))];
    }
    /* mark as read */
    if ($c[0] === 'G') {
        q("UPDATE tblmsg_members SET last_read_id=(SELECT COALESCE(MAX(id),0) FROM tblmessages WHERE group_id=?) WHERE group_id=? AND m_type=? AND m_id=?", [$c[1], $c[1], $mt, $mid]);
        $seen = 0;
    } else {
        q("UPDATE tblmessages SET read_at=NOW() WHERE group_id IS NULL AND sender_type=? AND sender_id=? AND receiver_type=? AND receiver_id=? AND read_at IS NULL", [$c[0], $c[1], $mt, $mid]);
        $seen = (int)q("SELECT COALESCE(MAX(id),0) FROM tblmessages WHERE group_id IS NULL AND sender_type=? AND sender_id=? AND receiver_type=? AND receiver_id=? AND read_at IS NOT NULL",
                       [$mt, $mid, $c[0], $c[1]])->fetchColumn();
    }
    /* recently deleted ids + reactions for the visible window */
    $base = array_diff_key($a, [':after' => 1]);
    $del = array_map('intval', q("SELECT id FROM (SELECT id,deleted FROM tblmessages WHERE $where ORDER BY id DESC LIMIT 100) t WHERE deleted=1", $base)->fetchAll(PDO::FETCH_COLUMN));
    $ids = q("SELECT id FROM tblmessages WHERE $where ORDER BY id DESC LIMIT 100", $base)->fetchAll(PDO::FETCH_COLUMN);
    $rx = [];
    if ($ids) {
        $in = implode(',', array_map('intval', $ids));
        foreach (q("SELECT message_id, emoji, COUNT(*) n, SUM(m_type=? AND m_id=?) mine FROM tblmsg_reactions WHERE message_id IN ($in) GROUP BY message_id, emoji ORDER BY MIN(created_at)", [$mt, $mid]) as $r)
            $rx[$r['message_id']][] = ['e' => $r['emoji'], 'n' => (int)$r['n'], 'mine' => (int)$r['mine'] > 0];
    }
    $online = false;
    if ($c[0] !== 'G') $online = (bool)q("SELECT 1 FROM tblmsg_presence WHERE m_type=? AND m_id=? AND last_seen > NOW() - INTERVAL 30 SECOND", [$c[0], $c[1]])->fetch();
    echo json_encode(['messages' => $out, 'seen' => $seen, 'deleted' => $del, 'online' => $online, 'reactions' => (object)$rx]);
    break;

case 'send':
    $c = parse_key($_POST['conv'] ?? ''); $body = trim($_POST['body'] ?? '');
    if (!$c) throw new Exception('Bad conversation');
    $fn = $fp = null;
    if (!empty($_FILES['file']['name']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $f = $_FILES['file'];
        if ($f['size'] > 5 * 1024 * 1024) throw new Exception('File is larger than 5 MB');
        $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['png','jpg','jpeg','gif','webp','pdf','doc','docx','xls','xlsx','ppt','pptx','txt','csv','zip'], true)) throw new Exception('File type not allowed');
        if (!is_dir(MS_UPLOAD_DIR)) mkdir(MS_UPLOAD_DIR, 0755, true);
        $fp = bin2hex(random_bytes(8)) . '.' . $ext; $fn = mb_substr(basename($f['name']), 0, 200);
        if (!move_uploaded_file($f['tmp_name'], MS_UPLOAD_DIR . $fp)) throw new Exception('Upload failed');
    }
    if ($body === '' && !$fp) throw new Exception('Empty message');
    if ($c[0] === 'G' && !in_group($c[1], $mt, $mid)) throw new Exception('Not a member');
    $rt = (int)($_POST['reply_to'] ?? 0) ?: null;
    if ($rt) {
        [$w, $pa] = conv_where($c);
        if (!q("SELECT 1 FROM tblmessages WHERE id=:rid AND $w", $pa + [':rid' => $rt])->fetch()) $rt = null;
    }
    if ($c[0] === 'G')
        q("INSERT INTO tblmessages (sender_type,sender_id,group_id,body,file_name,file_path,reply_to) VALUES (?,?,?,?,?,?,?)", [$mt, $mid, $c[1], $body, $fn, $fp, $rt]);
    else
        q("INSERT INTO tblmessages (sender_type,sender_id,receiver_type,receiver_id,body,file_name,file_path,reply_to) VALUES (?,?,?,?,?,?,?,?)", [$mt, $mid, $c[0], $c[1], $body, $fn, $fp, $rt]);
    echo json_encode(['ok' => true]);
    break;

case 'delete':
    q("UPDATE tblmessages SET deleted=1, body='' WHERE id=? AND sender_type=? AND sender_id=?", [(int)$_POST['id'], $mt, $mid]);
    echo json_encode(['ok' => true]);
    break;

case 'react':
    $id = (int)($_POST['id'] ?? 0); $e = $_POST['emoji'] ?? '';
    if (!in_array($e, ['👍','❤️','😂','😮','😢','🙏'], true)) throw new Exception('Bad reaction');
    $m = q("SELECT * FROM tblmessages WHERE id=? AND deleted=0", [$id])->fetch();
    if (!$m) throw new Exception('Message not found');
    $ok = $m['group_id'] ? in_group($m['group_id'], $mt, $mid)
        : (($m['sender_type'] === $mt && (int)$m['sender_id'] === $mid) || ($m['receiver_type'] === $mt && (int)$m['receiver_id'] === $mid));
    if (!$ok) throw new Exception('Not allowed');
    $cur = q("SELECT emoji FROM tblmsg_reactions WHERE message_id=? AND m_type=? AND m_id=?", [$id, $mt, $mid])->fetchColumn();
    if ($cur === $e) q("DELETE FROM tblmsg_reactions WHERE message_id=? AND m_type=? AND m_id=?", [$id, $mt, $mid]);
    else q("REPLACE INTO tblmsg_reactions (message_id,m_type,m_id,emoji) VALUES (?,?,?,?)", [$id, $mt, $mid, $e]);
    echo json_encode(['ok' => true]);
    break;

case 'create_group':
    $name = trim($_POST['name'] ?? ''); $mem = $_POST['members'] ?? [];
    if ($name === '' || !is_array($mem) || !$mem) throw new Exception('Enter a group name and pick members');
    q("INSERT INTO tblmsg_groups (name,created_type,created_id) VALUES (?,?,?)", [mb_substr($name, 0, 100), $mt, $mid]);
    $gid = (int)db()->lastInsertId();
    q("INSERT IGNORE INTO tblmsg_members (group_id,m_type,m_id) VALUES (?,?,?)", [$gid, $mt, $mid]);
    foreach ($mem as $k) if (($p = parse_key($k)) && $p[0] !== 'G') q("INSERT IGNORE INTO tblmsg_members (group_id,m_type,m_id) VALUES (?,?,?)", [$gid, $p[0], $p[1]]);
    echo json_encode(['ok' => true, 'key' => 'G:' . $gid]);
    break;

default: throw new Exception('Unknown action');
}
} catch (Exception $e) { http_response_code(400); echo json_encode(['error' => $e->getMessage()]); }