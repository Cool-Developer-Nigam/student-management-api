<?php
require_once 'config.php';

$conn = getDBConnection();
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        getStudents($conn);
        break;
    case 'POST':
        addStudent($conn);
        break;
    case 'PUT':
        updateStudent($conn);
        break;
    case 'DELETE':
        deleteStudent($conn);
        break;
    default:
        sendResponse(false, 'Method not allowed');
}

function getStudents($conn) {
    $query = "SELECT id, name, class, roll_no, contact FROM students ORDER BY id DESC";
    $result = $conn->query($query);
    
    if (!$result) {
        sendResponse(false, 'Failed to fetch students');
    }
    
    $students = [];
    while ($row = $result->fetch_assoc()) {
        $students[] = $row;
    }
    
    sendResponse(true, 'Students fetched successfully', $students);
}

function addStudent($conn) {
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    
    if (!$data) {
        sendResponse(false, 'Invalid JSON data');
    }
    
    $error = validateRequired($data, ['name', 'class', 'roll_no', 'contact']);
    if ($error) {
        sendResponse(false, $error);
    }
    
    $name = trim($data['name']);
    $class = trim($data['class']);
    $rollNo = trim($data['roll_no']);
    $contact = trim($data['contact']);
    
    $checkStmt = $conn->prepare("SELECT id FROM students WHERE roll_no = ?");
    $checkStmt->bind_param("s", $rollNo);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    if ($checkResult->num_rows > 0) {
        sendResponse(false, 'Roll number already exists');
    }
    $checkStmt->close();
    
    $stmt = $conn->prepare("INSERT INTO students (name, class, roll_no, contact) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $name, $class, $rollNo, $contact);
    
    if ($stmt->execute()) {
        $studentId = $conn->insert_id;
        
        $selectStmt = $conn->prepare("SELECT id, name, class, roll_no, contact FROM students WHERE id = ?");
        $selectStmt->bind_param("i", $studentId);
        $selectStmt->execute();
        $result = $selectStmt->get_result();
        $student = $result->fetch_assoc();
        $selectStmt->close();
        
        sendResponse(true, 'Student added successfully', $student);
    } else {
        sendResponse(false, 'Failed to add student');
    }
    
    $stmt->close();
}

function updateStudent($conn) {
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    
    if (!$data) {
        sendResponse(false, 'Invalid JSON data');
    }
    
    $error = validateRequired($data, ['id', 'name', 'class', 'roll_no', 'contact']);
    if ($error) {
        sendResponse(false, $error);
    }
    
    $id = intval($data['id']);
    $name = trim($data['name']);
    $class = trim($data['class']);
    $rollNo = trim($data['roll_no']);
    $contact = trim($data['contact']);
    
    $checkStmt = $conn->prepare("SELECT id FROM students WHERE roll_no = ? AND id != ?");
    $checkStmt->bind_param("si", $rollNo, $id);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    if ($checkResult->num_rows > 0) {
        sendResponse(false, 'Roll number already exists');
    }
    $checkStmt->close();
    
    $stmt = $conn->prepare("UPDATE students SET name = ?, class = ?, roll_no = ?, contact = ? WHERE id = ?");
    $stmt->bind_param("ssssi", $name, $class, $rollNo, $contact, $id);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            $selectStmt = $conn->prepare("SELECT id, name, class, roll_no, contact FROM students WHERE id = ?");
            $selectStmt->bind_param("i", $id);
            $selectStmt->execute();
            $result = $selectStmt->get_result();
            $student = $result->fetch_assoc();
            $selectStmt->close();
            
            sendResponse(true, 'Student updated successfully', $student);
        } else {
            sendResponse(false, 'Student not found');
        }
    } else {
        sendResponse(false, 'Failed to update student');
    }
    
    $stmt->close();
}

function deleteStudent($conn) {
    if (!isset($_GET['id']) || empty($_GET['id'])) {
        sendResponse(false, 'Student ID is required');
    }
    
    $id = intval($_GET['id']);
    
    $stmt = $conn->prepare("DELETE FROM students WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            sendResponse(true, 'Student deleted successfully');
        } else {
            sendResponse(false, 'Student not found');
        }
    } else {
        sendResponse(false, 'Failed to delete student');
    }
    
    $stmt->close();
}

$conn->close();
?>