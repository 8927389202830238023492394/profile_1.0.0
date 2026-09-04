<?php
require 'c:/xampp/htdocs/profile/config/database.php';
require 'c:/xampp/htdocs/profile/includes/functions.php';

// Mock auth & CSRF
\['admin_id'] = 1;
\[CSRF_TOKEN_NAME] = 'mocktoken';
function requireCsrf() {} 

// Create a copy of crud.php to test so we can redefine requireCsrf
\ = file_get_contents('c:/xampp/htdocs/profile/admin/includes/crud.php');
eval('?>'.\);

\['REQUEST_METHOD'] = 'POST';
\['action'] = 'create';
\['name'] = 'Test Service 123';
\['description'] = 'Desc 123';
\['status'] = '1';

ob_start();
AdminCRUD::handle('services', ['name', 'description', 'status']);
\ = ob_get_clean();

echo "Output:\n\\n";
