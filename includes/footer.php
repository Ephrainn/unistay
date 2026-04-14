<?php if (isset($showNav) && $showNav): ?>
<nav class="bottom-nav">
    <a href="home.php"        class="nav-item <?= ($activePage??'')==='home'     ?'active':'' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
        <span>Home</span>
    </a>
    <a href="my-bookings.php" class="nav-item <?= ($activePage??'')==='bookings' ?'active':'' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
        <span>Bookings</span>
    </a>
    <a href="payments.php"    class="nav-item <?= ($activePage??'')==='payments' ?'active':'' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
        <span>Payments</span>
    </a>
    <a href="profile.php"     class="nav-item <?= ($activePage??'')==='profile'  ?'active':'' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        <span>Profile</span>
    </a>
</nav>
<?php endif; ?>
</div><!-- .app-container -->
<script>
// Re-apply theme on every page load from localStorage
(function(){
    var t = localStorage.getItem('theme');
    if (t) {
        var b = document.getElementById('appBody');
        if (b) { b.classList.remove('dark','light'); b.classList.add(t); }
    }
})();
</script>
</body>
</html>
