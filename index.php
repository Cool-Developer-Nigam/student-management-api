<?php
echo json_encode([
    'success' => true,
    'message' => 'Student Management API is running',
    'endpoints' => [
        'login' => '/login.php',
        'students' => '/students.php'
    ]
]);
?>
