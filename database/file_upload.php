<?php
require_once '../includes/header.php';
require_once '../includes/sidebar.php';

?>
<form action="backup.php" method="post" enctype="multipart/form-data">
  <label>Select SQL Backup:</label>
  <input type="file" name="sql_file" accept=".sql" required>
  <button type="submit" name="upload">Upload and Restore</button>
</form>
<?php require_once '../includes/footer.php'; ?>