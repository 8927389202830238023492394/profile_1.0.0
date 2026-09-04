<?php
$finfo = new finfo(FILEINFO_MIME_TYPE);
$img = base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');
file_put_contents('test.gif', $img);
echo "GIF: " . $finfo->file('test.gif') . "\n";
unlink('test.gif');

file_put_contents('test.svg', '<svg xmlns="http://www.w3.org/2000/svg" width="1" height="1"></svg>');
echo "SVG: " . $finfo->file('test.svg') . "\n";
unlink('test.svg');
