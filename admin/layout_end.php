        </div><!-- .admin-content -->
    </div><!-- .admin-main -->
</div><!-- .admin-shell -->
<!-- Mobile sidebar overlay -->
<div id="adminOverlay" onclick="toggleAdminSidebar()" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:99;"></div>
<script>
function toggleAdminSidebar() {
    var s = document.querySelector('.admin-sidebar');
    var o = document.getElementById('adminOverlay');
    var open = s.style.display === 'flex';
    s.style.display = open ? 'none' : 'flex';
    o.style.display  = open ? 'none' : 'block';
}
</script>
</body>
</html>
