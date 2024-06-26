<?php
// Handle API requests when called through the requesting browser aka client
switch ($_SERVER['REQUEST_METHOD']) {
    case 'POST':
        register();
        break;
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method Not Allowed']);
        break;
}

function register(){
    $host = 'localhost';
    $dbName = 'webwizards'; // create this DB first
    $username = 'root';
    $password = '';
    $conn = new mysqli($host, $username, $password, $dbName);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $uname = $_POST['username'];
    $pw = $_POST['password'];
    $confirmpw = $_POST['confirmPassword'];

    if ($pw === $confirmpw) {

        //Get the largest user_id and increment it by one for the new id
        $sql = "SELECT user_id FROM users ORDER BY user_id DESC LIMIT 1";
        $id = mysqli_query($conn, $sql);
        if ($id) {
            $latestRecord = mysqli_fetch_assoc($id);
            $id = intval($latestRecord['user_id']);
            $id++;
        } else {
            $id = 1;
        }

        // Prepare the SQL query
        $stmt = $conn->prepare("SELECT COUNT(*) AS count FROM users WHERE username = ?");
        $stmt->bind_param("s", $uname);
        $stmt->execute();
        $result = $stmt->get_result();

        // Get the count value
        $row = $result->fetch_assoc();
        $count = $row["count"];

        // Check if the account exists
        if ($count > 0) {
            $response = "Username Already Registered!";
            echo json_encode(['success' => false, 'error' => $response]);
        } else {
            $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
            $stmt->bind_param("ss", $uname, $pw);
            if ($stmt->execute()) {
                $response = "Successfully Registered!";
                echo json_encode(['success' => true, 'res' => $response]);
            } else {    
                echo json_encode(['success' => false, 'error' => 'Invalid Username or Password']);
            }
        }
    } else {
        // Username not found in the database
        echo json_encode(['success' => false, 'error' => 'Password Not The Same']);
    }

    
    // Close the database connection
    $conn->close();

    // Set the response content type to JSON
    header('Content-Type: application/json');
}