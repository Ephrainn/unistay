<?php
require_once 'includes/auth.php';
require_once 'config/db.php';
requireLogin();

$hostel_id = (int)($_GET['hostel_id'] ?? 0);
$hStmt     = $pdo->prepare("SELECT * FROM hostels WHERE id = ?");
$hStmt->execute([$hostel_id]);
$hostel    = $hStmt->fetch();
if (!$hostel) { header('Location: home.php'); exit; }

$rStmt = $pdo->prepare("SELECT * FROM rooms WHERE hostel_id = ?");
$rStmt->execute([$hostel_id]);
$rooms = $rStmt->fetchAll();

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $room_id  = (int)($_POST['room_id'] ?? 0);
    $move_in  = $_POST['move_in'] ?? '';
    $move_out = $_POST['move_out'] ?? '';
    $agree    = $_POST['agree'] ?? '';
    if (!$room_id || !$move_in || !$move_out || !$agree) {
        $error = 'Please fill in all required fields and agree to terms.';
    } else {
        $file = '';
        if (!empty($_FILES['student_id']['name'])) {
            $ext  = pathinfo($_FILES['student_id']['name'], PATHINFO_EXTENSION);
            $file = 'uploads/' . uniqid() . '.' . $ext;
            move_uploaded_file($_FILES['student_id']['tmp_name'], $file);
        }
        $rRow = $pdo->prepare("SELECT price FROM rooms WHERE id = ?");
        $rRow->execute([$room_id]);
        $price = $rRow->fetchColumn();
        $pdo->prepare("INSERT INTO bookings (user_id,hostel_id,room_id,move_in,move_out,student_id_file,monthly_rent) VALUES (?,?,?,?,?,?,?)")
            ->execute([$_SESSION['user_id'], $hostel_id, $room_id, $move_in, $move_out, $file, $price]);
        $bookingId = $pdo->lastInsertId();
        // Notification
        $pdo->prepare("INSERT INTO notifications (user_id,title,message,type,link) VALUES (?,?,?,'booking','my-bookings.php')")
            ->execute([$_SESSION['user_id'], 'Booking Submitted', 'Your booking for ' . $hostel['name'] . ' has been submitted and is pending confirmation.']);
        // Decrease room availability
        $pdo->prepare("UPDATE rooms SET available_count = GREATEST(0, available_count - 1) WHERE id=?")->execute([$room_id]);
        header('Location: booking-confirm.php?id=' . $bookingId); exit;
    }
}

$sel = $rooms[0] ?? null;
if (!empty($_POST['room_id'])) {
    foreach ($rooms as $r) { if ($r['id'] == $_POST['room_id']) { $sel = $r; break; } }
}
$pageTitle = 'Booking Request – UniStay';
$showNav   = true; $activePage = 'bookings';
include 'includes/header.php';
?>

<!-- Top bar -->
<div class="booking-topbar">
    <a href="hostel.php?id=<?= $hostel_id ?>">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--text)" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
    </a>
    <h2>Booking Request</h2>
    <div class="booking-steps">
        <div class="bstep active"><div class="dot">1</div> Details</div>
        <div class="bstep-line"></div>
        <div class="bstep"><div class="dot">2</div> Verify</div>
        <div class="bstep-line"></div>
        <div class="bstep"><div class="dot">3</div> Pay</div>
    </div>
</div>

<div class="page-wrap" style="padding-top:20px;">
    <?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

    <div class="two-col">
        <!-- Left: form -->
        <div>
            <div class="booking-hostel-preview">
                <img src="<?= htmlspecialchars($hostel['image']) ?>" alt="" onerror="this.style.display='none'">
                <div>
                    <h4><?= htmlspecialchars($hostel['name']) ?></h4>
                    <p><?= htmlspecialchars($hostel['distance_from_campus']) ?></p>
                    <div class="booking-hostel-tags"><span>📶 Free Wi-Fi</span><span>🧺 Laundry</span></div>
                </div>
            </div>

            <form method="POST" enctype="multipart/form-data" id="bookingForm">
                <div class="booking-section">
                    <h3>Select Stay Period</h3>
                    <div class="date-row">
                        <div class="form-group" style="margin-bottom:0;">
                            <label>Move-in</label>
                            <input type="date" name="move_in" class="form-control" value="<?= htmlspecialchars($_POST['move_in'] ?? '') ?>" required>
                        </div>
                        <div class="form-group" style="margin-bottom:0;">
                            <label>Move-out</label>
                            <input type="date" name="move_out" class="form-control" value="<?= htmlspecialchars($_POST['move_out'] ?? '') ?>" required>
                        </div>
                    </div>
                </div>

                <div class="booking-section">
                    <h3>Room Type</h3>
                    <?php foreach ($rooms as $room):
                        $isSel = $sel && $sel['id'] == $room['id']; ?>
                    <label class="room-option <?= $isSel ? 'selected' : '' ?>" onclick="pickRoom(this,<?= $room['id'] ?>,<?= $room['price'] ?>)">
                        <div class="room-option-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/></svg>
                        </div>
                        <div class="room-option-info">
                            <h4><?= htmlspecialchars($room['type']) ?></h4>
                            <p><?= CURRENCY ?><?= number_format($room['price'], 0) ?>/mo · <?= htmlspecialchars($room['description']) ?></p>
                        </div>
                        <div class="radio-dot <?= $isSel ? 'on' : '' ?>"></div>
                    </label>
                    <?php endforeach; ?>
                    <input type="hidden" name="room_id" id="room_id" value="<?= $sel ? $sel['id'] : '' ?>">
                </div>

                <div class="booking-section">
                    <h3>Student Verification</h3>
                    <div class="upload-box">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v2"/></svg>
                        <h4>Upload Student ID</h4>
                        <p>JPG or PNG, max 5MB</p>
                        <label class="btn btn-outline btn-auto" style="padding:10px 20px;cursor:pointer;border-radius:10px;">
                            Select File
                            <input type="file" name="student_id" accept=".jpg,.jpeg,.png" style="display:none;" onchange="document.getElementById('fname').textContent=this.files[0]?.name||''">
                        </label>
                        <p id="fname" style="margin-top:8px;color:var(--green);font-size:0.72rem;"></p>
                    </div>
                </div>

                <div class="check-row">
                    <input type="checkbox" name="agree" id="agree" value="1">
                    <label for="agree">I agree to the UniStay Terms of Service and the hostel's house rules for students.</label>
                </div>

                <!-- Mobile sticky footer -->
                <div class="sticky-footer mobile-only" style="position:static;border-top:1px solid var(--border);padding:16px 0;margin-top:8px;">
                    <div class="sticky-footer-price">
                        <strong id="price_mob"><?= CURRENCY ?><?= $sel ? number_format($sel['price'], 2) : '0.00' ?></strong>
                        <span>Monthly Rent</span>
                    </div>
                    <button type="submit" class="btn btn-primary btn-auto">→ Continue</button>
                </div>
            </form>
        </div>

        <!-- Right: summary (desktop) -->
        <div>
            <div class="booking-summary">
                <h3>Booking Summary</h3>
                <div class="booking-hostel-preview" style="margin-bottom:16px;">
                    <img src="<?= htmlspecialchars($hostel['image']) ?>" alt="" onerror="this.style.display='none'">
                    <div><h4><?= htmlspecialchars($hostel['name']) ?></h4><p><?= htmlspecialchars($hostel['distance_from_campus']) ?></p></div>
                </div>
                <div class="summary-row"><span>Monthly Rent</span><strong id="price_desk"><?= CURRENCY ?><?= $sel ? number_format($sel['price'], 2) : '—' ?></strong></div>
                <div class="summary-row"><span>Security Deposit</span><strong id="dep_desk"><?= CURRENCY ?><?= $sel ? number_format($sel['price'], 2) : '—' ?></strong></div>
                <div class="summary-total"><span>Total Due Now</span><strong id="total_desk"><?= CURRENCY ?><?= $sel ? number_format($sel['price'] * 2, 2) : '—' ?></strong></div>
                <button form="bookingForm" type="submit" class="btn btn-primary" style="margin-top:20px;">→ Continue to Verify</button>
            </div>
        </div>
    </div>
</div>

<script>
function pickRoom(el, id, price) {
    document.querySelectorAll('.room-option').forEach(function(r){
        r.classList.remove('selected');
        r.querySelector('.radio-dot').classList.remove('on');
    });
    el.classList.add('selected');
    el.querySelector('.radio-dot').classList.add('on');
    document.getElementById('room_id').value = id;
    var fmt = '₵' + parseFloat(price).toFixed(2);
    var mob = document.getElementById('price_mob');
    var desk = document.getElementById('price_desk');
    var dep  = document.getElementById('dep_desk');
    var tot  = document.getElementById('total_desk');
    if (mob)  mob.textContent  = fmt;
    if (desk) desk.textContent = fmt;
    if (dep)  dep.textContent  = fmt;
    if (tot)  tot.textContent  = '₵' + (parseFloat(price)*2).toFixed(2);
}
</script>
<?php include 'includes/footer.php'; ?>
