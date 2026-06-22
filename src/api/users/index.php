<?php
// Legacy users API — superseded by the uifw users API at /app/api/users.php
// (auth, roles and per-user management now live in the uifw UI layer).
http_response_code(410);
header('Content-Type: application/json');
echo json_encode(['status' => 'error', 'message' => 'Gone: use /app/api/users.php']);
