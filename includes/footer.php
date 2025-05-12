</div> <!-- Close flex-grow-1> -->
<script src="../assets/js/jquery.min.js"></script>
<script src="../assets/js/bootstrap.bundle.min.js"></script>

<!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script> -->
<script>
    document.querySelector('.navbar-toggler').addEventListener('click', function () {
        const sidebar = document.getElementById('sidebarMenu');
        sidebar.classList.toggle('d-none');
    });

    function toggleSidebar() {
        const sidebar = document.getElementById('mobileSidebar');
        sidebar.style.display = (sidebar.style.display === 'none' || sidebar.style.display === '') ? 'block' : 'none';
    }
</script>
</body>
</html>

