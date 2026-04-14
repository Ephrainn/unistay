<?php
require_once 'includes/auth.php';
require_once 'config/db.php';
requireLogin();
$user = currentUser($pdo);
if (!$user) { session_destroy(); header('Location: login.php'); exit; }

$success = '';
$error   = '';

// ── Handle all POST actions ──
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'profile';

    // Profile edit
    if ($action === 'profile') {
        $name       = trim($_POST['name']       ?? $user['name']);
        $university = trim($_POST['university'] ?? $user['university']);
        $course     = trim($_POST['course']     ?? $user['course']);
        $year       = (int)($_POST['year']      ?? $user['year']);
        $uid        = trim($_POST['university_id']  ?? $user['university_id']);
        $dist       = trim($_POST['campus_distance'] ?? $user['campus_distance']);
        $dark       = isset($_POST['dark_mode']) ? 1 : 0;
        $push       = isset($_POST['push_notifications']) ? 1 : 0;
        $pdo->prepare("UPDATE users SET name=?,university=?,course=?,year=?,university_id=?,campus_distance=?,dark_mode=?,push_notifications=? WHERE id=?")
            ->execute([$name,$university,$course,$year,$uid,$dist,$dark,$push,$_SESSION['user_id']]);
        $user    = currentUser($pdo);
        $success = 'Profile updated.';
    }

    // University ID update
    if ($action === 'university_id') {
        $uid = trim($_POST['university_id'] ?? '');
        $pdo->prepare("UPDATE users SET university_id=? WHERE id=?")->execute([$uid, $_SESSION['user_id']]);
        $user    = currentUser($pdo);
        $success = 'University ID saved.';
    }

    // Campus distance update
    if ($action === 'campus_distance') {
        $dist = trim($_POST['campus_distance'] ?? '');
        $pdo->prepare("UPDATE users SET campus_distance=? WHERE id=?")->execute([$dist, $_SESSION['user_id']]);
        $user    = currentUser($pdo);
        $success = 'Campus distance saved.';
    }

    // Enrollment letter upload
    if ($action === 'enrollment_letter') {
        if (!empty($_FILES['enrollment_letter']['name'])) {
            $allowed = ['pdf','doc','docx','jpg','jpeg','png'];
            $ext     = strtolower(pathinfo($_FILES['enrollment_letter']['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, $allowed)) {
                $error = 'Invalid file type. Allowed: PDF, DOC, DOCX, JPG, PNG.';
            } elseif ($_FILES['enrollment_letter']['size'] > 5 * 1024 * 1024) {
                $error = 'File too large. Max 5MB.';
            } else {
                $filename = 'uploads/enrollment_' . $_SESSION['user_id'] . '_' . time() . '.' . $ext;
                move_uploaded_file($_FILES['enrollment_letter']['tmp_name'], '../' . $filename);
                $pdo->prepare("UPDATE users SET enrollment_letter=? WHERE id=?")->execute([$filename, $_SESSION['user_id']]);
                $user    = currentUser($pdo);
                $success = 'Enrollment letter uploaded.';
            }
        }
    }

    // Support ticket
    if ($action === 'support') {
        $subject = trim($_POST['subject'] ?? '');
        $message = trim($_POST['message'] ?? '');
        if ($subject && $message) {
            $pdo->prepare("INSERT INTO support_tickets (user_id, subject, message) VALUES (?,?,?)")
                ->execute([$_SESSION['user_id'], $subject, $message]);
            $success = 'Support ticket submitted. We\'ll get back to you shortly.';
        } else {
            $error = 'Please fill in both subject and message.';
        }
    }

    // Dark mode toggle (AJAX-friendly)
    if ($action === 'dark_mode') {
        $dark = (int)($_POST['dark_mode'] ?? 1);
        $pdo->prepare("UPDATE users SET dark_mode=? WHERE id=?")->execute([$dark, $_SESSION['user_id']]);
        $user = currentUser($pdo);
        if (!empty($_POST['ajax'])) { echo 'ok'; exit; }
    }

    // Push notifications toggle
    if ($action === 'push_notifications') {
        $push = (int)($_POST['push_notifications'] ?? 1);
        $pdo->prepare("UPDATE users SET push_notifications=? WHERE id=?")->execute([$push, $_SESSION['user_id']]);
        $user = currentUser($pdo);
    }
}

$bookingCount = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE user_id=?");
$bookingCount->execute([$_SESSION['user_id']]);
$bookingCount = $bookingCount->fetchColumn();

$initials  = strtoupper(implode('', array_map(fn($w) => $w[0], explode(' ', $user['name']))));
$pageTitle = 'Student Profile – UniStay';
$showNav   = true; $activePage = 'profile';
include 'includes/header.php';
?>

<div class="page-wrap">
    <?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>
    <?php if ($error):   ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

    <div class="two-col">
        <!-- Left panel -->
        <div>
            <div class="profile-panel">
                <div class="profile-hero">
                    <div class="avatar-wrap" style="display:inline-block;">
                        <div class="avatar avatar-lg"><?= htmlspecialchars($initials) ?></div>
                        <?php if ($user['is_verified']): ?><div class="verified-dot"></div><?php endif; ?>
                    </div>
                    <h2><?= htmlspecialchars($user['name']) ?></h2>
                    <p class="sub"><?= htmlspecialchars($user['course'] ?? 'Student') ?><?= $user['year'] ? ' · Year '.$user['year'] : '' ?></p>
                    <div class="loc">
                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
                        <?= htmlspecialchars($user['university'] ?? 'University') ?>
                    </div>
                </div>

                <div class="profile-stats">
                    <div class="profile-stat"><h3><?= $user['rating'] ?: '—' ?></h3><p>Rating</p></div>
                    <div class="profile-stat"><h3><?= $bookingCount ?></h3><p>Bookings</p></div>
                    <div class="profile-stat"><h3><?= $user['is_verified'] ? 'Yes' : 'No' ?></h3><p>Verified</p></div>
                </div>

                <div style="display:flex;flex-direction:column;gap:8px;">
                    <button onclick="openModal('editModal')" class="btn btn-outline" style="padding:12px;">Edit Profile</button>
                    <button onclick="openModal('supportModal')" class="btn btn-outline" style="padding:12px;">🎧 Contact Support</button>
                    <a href="logout.php" class="btn btn-outline" style="padding:12px;display:flex;align-items:center;justify-content:center;gap:8px;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                        Sign Out
                    </a>
                </div>
                <p style="text-align:center;margin-top:16px;font-size:0.68rem;color:var(--dim);">UniStay v2.4.0</p>
            </div>
        </div>

        <!-- Right: details + prefs -->
        <div>
            <div class="profile-section">
                <div class="profile-section-label">Academic Details</div>

                <!-- University ID -->
                <div class="profile-item" onclick="openModal('uidModal')" style="cursor:pointer;">
                    <div class="profile-item-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/></svg></div>
                    <div class="profile-item-body">
                        <div class="label">University ID</div>
                        <div class="value"><?= htmlspecialchars($user['university_id'] ?: 'Tap to set') ?></div>
                    </div>
                    <svg class="chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
                </div>

                <!-- Enrollment Letter -->
                <div class="profile-item" onclick="openModal('enrollModal')" style="cursor:pointer;">
                    <div class="profile-item-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg></div>
                    <div class="profile-item-body">
                        <div class="label">Enrollment Letter</div>
                        <div class="value">
                            <?php if ($user['enrollment_letter']): ?>
                            <span style="color:var(--green);">✓ Uploaded</span>
                            <?php else: ?>
                            Tap to upload
                            <?php endif; ?>
                        </div>
                    </div>
                    <svg class="chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
                </div>

                <!-- Campus Distance -->
                <div class="profile-item" onclick="openModal('distModal')" style="cursor:pointer;">
                    <div class="profile-item-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg></div>
                    <div class="profile-item-body">
                        <div class="label">Campus Distance</div>
                        <div class="value"><?= htmlspecialchars($user['campus_distance'] ?: 'Tap to set') ?></div>
                    </div>
                    <svg class="chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
                </div>
            </div>

            <form method="POST" id="prefForm">
                <input type="hidden" name="action" value="push_notifications">
            <div class="profile-section">
                <div class="profile-section-label">Preferences</div>
                <div class="pref-item">
                    <h4>Push Notifications</h4>
                    <label class="toggle">
                        <input type="checkbox" name="push_notifications" value="1" <?= $user['push_notifications']?'checked':'' ?> onchange="document.getElementById('prefForm').submit()">
                        <span class="toggle-track"></span>
                    </label>
                </div>
                <div class="pref-item">
                    <h4>Dark Mode</h4>
                    <label class="toggle">
                        <input type="checkbox" name="dark_mode" id="darkModeToggle" <?= $user['dark_mode']?'checked':'' ?> onchange="toggleDarkMode(this)">
                        <span class="toggle-track"></span>
                    </label>
                </div>
                <div class="pref-item"><h4>Language</h4><span class="val"><?= htmlspecialchars($user['language']) ?></span></div>
                <a href="javascript:void(0)" onclick="openModal('supportModal')" class="pref-item" style="display:flex;"><h4>Privacy & Security</h4><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="var(--dim)" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg></a>
            </div>
            </form>
        </div>
    </div>
</div>

<!-- ── MODALS ── -->

<!-- Edit Profile -->
<div id="editModal" class="modal-overlay" onclick="closeOnOverlay(event,'editModal')">
    <div class="modal-box">
        <div class="modal-header"><h3>Edit Profile</h3><button onclick="closeModal('editModal')">✕</button></div>
        <form method="POST">
            <input type="hidden" name="action" value="profile">
            <div class="form-group"><label>Full Name</label><input type="text" name="name" class="form-control" value="<?= htmlspecialchars($user['name']) ?>"></div>
            <div class="form-group"><label>University</label><input type="text" name="university" class="form-control" value="<?= htmlspecialchars($user['university'] ?? '') ?>"></div>
            <div class="form-group"><label>Course</label><input type="text" name="course" class="form-control" value="<?= htmlspecialchars($user['course'] ?? '') ?>"></div>
            <div class="form-group"><label>Year</label><input type="number" name="year" class="form-control" min="1" max="6" value="<?= $user['year'] ?>"></div>
            <button type="submit" class="btn btn-primary">Save Changes</button>
            <button type="button" onclick="closeModal('editModal')" class="btn btn-outline" style="margin-top:10px;">Cancel</button>
        </form>
    </div>
</div>

<!-- University ID -->
<div id="uidModal" class="modal-overlay" onclick="closeOnOverlay(event,'uidModal')">
    <div class="modal-box">
        <div class="modal-header"><h3>University ID</h3><button onclick="closeModal('uidModal')">✕</button></div>
        <form method="POST">
            <input type="hidden" name="action" value="university_id">
            <div class="form-group">
                <label>Your University ID Number</label>
                <input type="text" name="university_id" class="form-control" placeholder="e.g. ID-8829401" value="<?= htmlspecialchars($user['university_id'] ?? '') ?>">
                <small style="color:var(--muted);font-size:0.72rem;margin-top:4px;display:block;">This is used to verify your student status.</small>
            </div>
            <button type="submit" class="btn btn-primary">Save</button>
            <button type="button" onclick="closeModal('uidModal')" class="btn btn-outline" style="margin-top:10px;">Cancel</button>
        </form>
    </div>
</div>

<!-- Enrollment Letter -->
<div id="enrollModal" class="modal-overlay" onclick="closeOnOverlay(event,'enrollModal')">
    <div class="modal-box">
        <div class="modal-header"><h3>Enrollment Letter</h3><button onclick="closeModal('enrollModal')">✕</button></div>
        <?php if ($user['enrollment_letter']): ?>
        <div style="background:var(--green-bg);border-radius:10px;padding:12px 16px;margin-bottom:16px;display:flex;align-items:center;gap:10px;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--green)" stroke-width="2"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            <span style="font-size:0.85rem;color:var(--green);">Letter already uploaded.</span>
            <a href="../<?= htmlspecialchars($user['enrollment_letter']) ?>" target="_blank" style="margin-left:auto;font-size:0.78rem;color:var(--accent);">View</a>
        </div>
        <?php endif; ?>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="enrollment_letter">
            <div class="form-group">
                <label>Upload Enrollment Letter</label>
                <div class="upload-box" style="padding:20px;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:32px;height:32px;color:var(--dim);margin-bottom:8px;"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                    <h4 style="font-size:0.875rem;">Choose file</h4>
                    <p style="font-size:0.72rem;margin:4px 0 12px;">PDF, DOC, DOCX, JPG or PNG · Max 5MB</p>
                    <label class="btn btn-outline btn-auto" style="padding:8px 18px;cursor:pointer;border-radius:8px;font-size:0.82rem;">
                        Select File
                        <input type="file" name="enrollment_letter" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" style="display:none;" onchange="document.getElementById('enrollName').textContent=this.files[0]?.name||''">
                    </label>
                    <p id="enrollName" style="margin-top:8px;font-size:0.72rem;color:var(--accent);"></p>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Upload</button>
            <button type="button" onclick="closeModal('enrollModal')" class="btn btn-outline" style="margin-top:10px;">Cancel</button>
        </form>
    </div>
</div>

<!-- Campus Distance -->
<div id="distModal" class="modal-overlay" onclick="closeOnOverlay(event,'distModal')">
    <div class="modal-box">
        <div class="modal-header"><h3>Campus Distance</h3><button onclick="closeModal('distModal')">✕</button></div>
        <form method="POST">
            <input type="hidden" name="action" value="campus_distance">
            <div class="form-group">
                <label>Distance from Campus</label>
                <input type="text" name="campus_distance" class="form-control" placeholder="e.g. 0.8 miles away" value="<?= htmlspecialchars($user['campus_distance'] ?? '') ?>">
                <small style="color:var(--muted);font-size:0.72rem;margin-top:4px;display:block;">Enter your current distance from the main campus.</small>
            </div>
            <button type="submit" class="btn btn-primary">Save</button>
            <button type="button" onclick="closeModal('distModal')" class="btn btn-outline" style="margin-top:10px;">Cancel</button>
        </form>
    </div>
</div>

<!-- Contact Support -->
<div id="supportModal" class="modal-overlay" onclick="closeOnOverlay(event,'supportModal')">
    <div class="modal-box">
        <div class="modal-header"><h3>🎧 Contact Support</h3><button onclick="closeModal('supportModal')">✕</button></div>
        <p style="font-size:0.85rem;color:var(--muted);margin-bottom:20px;">We typically respond within 24 hours. For urgent issues call <strong style="color:var(--text);">+233 30 291 7152</strong>.</p>
        <form method="POST">
            <input type="hidden" name="action" value="support">
            <div class="form-group">
                <label>Subject</label>
                <select name="subject" class="form-control">
                    <option>Booking Issue</option>
                    <option>Payment Problem</option>
                    <option>Hostel Complaint</option>
                    <option>Account & Verification</option>
                    <option>Refund Request</option>
                    <option>Other</option>
                </select>
            </div>
            <div class="form-group">
                <label>Message</label>
                <textarea name="message" class="form-control" rows="4" placeholder="Describe your issue in detail..." style="resize:vertical;"></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Submit Ticket</button>
            <button type="button" onclick="closeModal('supportModal')" class="btn btn-outline" style="margin-top:10px;">Cancel</button>
        </form>
    </div>
</div>

<script>
function openModal(id)  { document.getElementById(id).style.display='flex'; document.body.style.overflow='hidden'; }
function closeModal(id) { document.getElementById(id).style.display='none'; document.body.style.overflow=''; }
function closeOnOverlay(e, id) { if (e.target.id===id) closeModal(id); }

function toggleDarkMode(checkbox) {
    var theme = checkbox.checked ? 'dark' : 'light';
    document.getElementById('appBody').classList.remove('dark','light');
    document.getElementById('appBody').classList.add(theme);
    localStorage.setItem('theme', theme);
    // Save to DB via fetch
    var fd = new FormData();
    fd.append('action','dark_mode');
    fd.append('dark_mode', checkbox.checked ? '1' : '0');
    fd.append('ajax','1');
    fetch('profile.php', { method:'POST', body:fd });
}
</script>

<?php include 'includes/footer.php'; ?>
