<?php
include 'config.php';

$username = "";
$email = "";
$errors = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Empty field validation
    if (empty($username)) {
        $errors[] = "Username is required.";
    }

    if (empty($email)) {
        $errors[] = "Email is required.";
    }

    if (empty($password)) {
        $errors[] = "Password is required.";
    }

    if (empty($confirm_password)) {
        $errors[] = "Confirm Password is required.";
    }

    // Username length
    if (!empty($username) && strlen($username) < 3) {
        $errors[] = "Username must be at least 3 characters.";
    }

    // Email validation
    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }

    // Password length
    if (!empty($password) && strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters.";
    }

    // Password match
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    }

    // Check username/email exists
    if (empty($errors)) {

        $stmt = $conn->prepare("SELECT * FROM users WHERE username=? OR email=?");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows > 0) {

            while ($row = $result->fetch_assoc()) {

                if ($row['username'] == $username) {
                    $errors[] = "Username already exists.";
                }

                if ($row['email'] == $email) {
                    $errors[] = "Email already exists.";
                }
            }
        }

        $stmt->close();
    }

    // Insert User
    if (empty($errors)) {

        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare(
            "INSERT INTO users (username, email, password_hash)
             VALUES (?, ?, ?)"
        );

        $stmt->bind_param(
            "sss",
            $username,
            $email,
            $hashed_password
        );

        if ($stmt->execute()) {
            echo "<p style='color:green;'>Registration successful!</p>";
        } else {
            echo "<p style='color:red;'>Error: " . $stmt->error . "</p>";
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>User Registration</title>

    <style>
        body{
            font-family: Arial, sans-serif;
            width:500px;
            margin:30px auto;
        }

        input{
            width:100%;
            padding:10px;
            margin-bottom:10px;
        }

        button{
            padding:10px 20px;
        }

        .error{
            color:red;
        }
    </style>
</head>
<body>

<h2>User Registration</h2>

<?php
if(!empty($errors)){
    foreach($errors as $error){
        echo "<p class='error'>$error</p>";
    }
}
?>

<form method="POST">

    <label>Username</label>
    <input type="text" name="username"
           value="<?php echo htmlspecialchars($username); ?>">

    <label>Email</label>
    <input type="email" name="email"
           value="<?php echo htmlspecialchars($email); ?>">

    <label>Password</label>
    <input type="password" name="password">

    <label>Confirm Password</label>
    <input type="password" name="confirm_password">

    <button type="submit">Register</button>

</form>

</body>
</html>
