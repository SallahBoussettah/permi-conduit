<?php

// Get the table structure for user_course_completions
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "permi_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get table structure
$sql = "DESCRIBE user_course_completions";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo "Structure of user_course_completions table:\n";
    echo "--------------------------------------------\n";
    echo "Field\tType\tNull\tKey\tDefault\tExtra\n";
    echo "--------------------------------------------\n";
    
    while($row = $result->fetch_assoc()) {
        echo $row["Field"] . "\t" . $row["Type"] . "\t" . $row["Null"] . "\t" . $row["Key"] . "\t" . $row["Default"] . "\t" . $row["Extra"] . "\n";
    }
} else {
    echo "Table does not exist or has no columns";
}

// Get table structure for user_course_progress
$sql = "DESCRIBE user_course_progress";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo "\n\nStructure of user_course_progress table:\n";
    echo "--------------------------------------------\n";
    echo "Field\tType\tNull\tKey\tDefault\tExtra\n";
    echo "--------------------------------------------\n";
    
    while($row = $result->fetch_assoc()) {
        echo $row["Field"] . "\t" . $row["Type"] . "\t" . $row["Null"] . "\t" . $row["Key"] . "\t" . $row["Default"] . "\t" . $row["Extra"] . "\n";
    }
} else {
    echo "\n\nTable user_course_progress does not exist or has no columns";
}

$conn->close(); 