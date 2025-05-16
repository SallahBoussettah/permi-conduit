<?php

// Check if the course_categories table exists
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

// Check if table exists
$sql = "SHOW TABLES LIKE 'course_categories'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo "course_categories table exists\n";
    
    // Get table structure
    $sql = "DESCRIBE course_categories";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        echo "\nStructure of course_categories table:\n";
        echo "--------------------------------------------\n";
        echo "Field\tType\tNull\tKey\tDefault\tExtra\n";
        echo "--------------------------------------------\n";
        
        while($row = $result->fetch_assoc()) {
            echo $row["Field"] . "\t" . $row["Type"] . "\t" . $row["Null"] . "\t" . $row["Key"] . "\t" . $row["Default"] . "\t" . $row["Extra"] . "\n";
        }
    }
} else {
    echo "course_categories table does not exist";
}

$conn->close(); 