<?php

if (isset($_POST['register'])) {
    echo '<div style="background: yellow; color: black; padding: 10px; font-weight: bold;">DEBUG: process_register.php registration handler is running!</div>';
}

session_start();
require_once 'config.php';
$conn->set_charset("utf8");

function logActivity($conn, $userId, $activityType, $description) { $stmt = $conn->prepare("INSERT INTO activity_logs (user_id, activity_type, description, created_at) VALUES (?, ?, ?, NOW())");
    if ($stmt) {
        $stmt->bind_param("iss", $userId, $activityType, $description);
        $stmt->execute();
        $stmt->close();
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register'])) {
    file_put_contents('debug_registration.txt', "Registration handler triggered at " . date('Y-m-d H:i:s') . "\n", FILE_APPEND);

    $lastName   = trim($_POST['LastName'] ?? '');
    $firstName  = trim($_POST['FirstName'] ?? '');
    $middleName = trim($_POST['MiddleName'] ?? '');
    $birthdate  = trim($_POST['birthdate'] ?? '');
    $gender     = trim($_POST['gender'] ?? '');
    $phone      = trim($_POST['phone'] ?? '');
    $email      = trim($_POST['email'] ?? '');
    $password   = trim($_POST['EnterPassword'] ?? '');
    $confirmPwd = trim($_POST['confirmPassword'] ?? '');

    $errors = [];

    if (!$lastName || !$firstName || !$middleName || !$birthdate || !$gender || !$phone || !$email || !$password || !$confirmPwd) {
        $errors[] = "All fields are required.";
    }

    file_put_contents('debug_registration.txt', "Input values: " . json_encode([
        'LastName' => $lastName,
        'FirstName' => $firstName,
        'MiddleName' => $middleName,
        'birthdate' => $birthdate,
        'gender' => $gender,
        'phone' => $phone,
        'email' => $email,
        'password' => $password,
        'confirmPwd' => $confirmPwd
    ]) . "\n", FILE_APPEND);

     if ($lastName && !preg_match("/^[a-zA-Z\s]+$/", $lastName)) {
        $errors[] = "Last name must have only letters and spaces.";
    }
    if ($firstName && !preg_match("/^[a-zA-Z\s]+$/", $firstName)) {
        $errors[] = "First name must have only letters and spaces.";
    }
    if ($middleName && !preg_match("/^[a-zA-Z\s]+$/", $middleName)) {
        $errors[] = "Middle name must have only letters and spaces.";
    }

     if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }

    if ($email) {
        $stmt = $conn->prepare("SELECT id FROM authentication WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $errors[] = "This email is already registered.";
        }
        $stmt->close();
    }

     if ($phone && !preg_match("/^[0-9]{10}$/", $phone)) {
        $errors[] = "Phone number must be exactly 10 digits.";
    }

    if ($phone) {
        $stmt = $conn->prepare("SELECT id FROM authentication WHERE phone_number = ?");
        $stmt->bind_param("s", $phone);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $errors[] = "This phone number is already registered.";
        }
        $stmt->close();
    }

    if ($birthdate) {
        $birthDateObj = DateTime::createFromFormat('Y-m-d', $birthdate);
        $now = new DateTime();
        if (!$birthDateObj || $birthDateObj->format('Y-m-d') !== $birthdate) {
            $errors[] = "Invalid birthdate format.";
        } else if ($birthDateObj > $now) {
            $errors[] = "Birthdate cannot be in the future.";
        } else {
            $age = $now->diff($birthDateObj)->y;
            if ($age < 13) {
                $errors[] = "You must be at least 13 years old to register.";
            }
        }
    }

    if ($gender && !in_array($gender, ['male', 'female', 'other'])) {
        $errors[] = "Invalid gender selection.";
    }

    if ($password && strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters long.";
    }
    if ($password && !preg_match("/[a-z]/", $password)) {
        $errors[] = "Password must have at least one lowercase letter.";
    }
    if ($password && !preg_match("/[A-Z]/", $password)) {
        $errors[] = "Password must have at least one uppercase letter.";
    }
    if ($password && !preg_match("/[0-9]/", $password)) {
        $errors[] = "Password must have at least one number.";
    }
    if ($password && $confirmPwd && $password !== $confirmPwd) {
        $errors[] = "Passwords do not match.";
    }

    if (!empty($errors)) {
        file_put_contents('debug_registration.txt', "Validation errors: " . json_encode($errors) . "\n", FILE_APPEND);
    }

     if (empty($errors)) {
        file_put_contents('debug_registration.txt', "No validation errors, attempting SQL insert...\n", FILE_APPEND);
      
     $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $role = 'user'; // Default role
        $age = $now->diff($birthDateObj)->y;

         $stmt = $conn->prepare("INSERT INTO authentication (last_name, first_name, middle_name, birthdate, age, gender, phone_number, email, password_hash, role, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
        $stmt->bind_param("ssssisssss", $lastName, $firstName, $middleName, $birthdate, $age, $gender, $phone, $email, $hashedPassword, $role);

        if ($stmt->execute()) {
            $userId = $stmt->insert_id;
            $stmt->close();

        logActivity($conn, $userId, 'Registration', 'User registered successfully');
           
         $_SESSION['success_message'] = "Registration successful! Please log in.";
            header("Location: index.php?success=1");
            exit();
        } else {
             file_put_contents('debug_registration.txt', "SQL Error: " . $stmt->error . "\n", FILE_APPEND);
            $errors[] = "Registration failed. Please try again. SQL Error: " . $stmt->error;
        }
    }

     if (!empty($errors)) {
        $_SESSION['registration_errors'] = $errors;
        $_SESSION['form_data'] = [
            'LastName' => $lastName,
            'FirstName' => $firstName,
            'MiddleName' => $middleName,
            'email' => $email
        ];
        header("Location: index.php?error=1");
        exit();
    }
}

// Login Handler 

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['login'])) {
    $email = trim($_POST["email"] ?? '');
    $password = $_POST["password"] ?? '';

    if (!$email || !$password) {
        $_SESSION["login_error"] = "Email and password are required.";
        header("Location: index.php");
        exit();
    }

    $stmt = $conn->prepare("SELECT id, first_name, password_hash, role FROM authentication WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user["password_hash"])) {
            // Set session variables
            $_SESSION["user_id"] = $user["id"];
            $_SESSION["email"] = $email;
            $_SESSION["role"] = $user["role"];
            // Redirect by role
            if ($user["role"] === "admin") {
                header("Location: adminDashboard.php");
            } else {
                header("Location: userDashboard.php");
            }
            exit();
        }
    }

    $_SESSION["login_error"] = "Invalid email or password.";
    header("Location: index.php");
    exit();
}