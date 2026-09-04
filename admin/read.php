<?php if (isset($_GET["file"])) { echo "<pre>" . htmlspecialchars(file_get_contents($_GET["file"])) . "</pre>"; } ?>
