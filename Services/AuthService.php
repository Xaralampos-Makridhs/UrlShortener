<?php

class AuthService
{
    // PDO database connection object
    private $conn;

    // Constructor receives and stores the database connection
    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    // Registers a new user account
    public function register($name, $email, $password)
    {
        // Remove extra spaces from the user's name
        $name = trim($name);

        // Normalize email by trimming spaces and converting it to lowercase
        $email = strtolower(trim($email));

        // Validate name, email format, and minimum password length
        if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 8) {
            return false;
        }

        // Hash the password before storing it in the database
        $passwordHashed = password_hash($password, PASSWORD_DEFAULT);

        try {
            // Prepare SQL query to insert the new user
            $stmt = $this->conn->prepare("
                INSERT INTO users (name, email, password)
                VALUES (:name, :email, :password)
            ");

            // Execute query with user data
            return $stmt->execute([
                ':name' => $name,
                ':email' => $email,
                ':password' => $passwordHashed
            ]);

        } catch (PDOException $e) {
            // Log registration error and return false
            error_log('Register Error: ' . $e->getMessage());
            return false;
        }
    }

    // Logs in a user using email and password
    public function login($email, $password)
    {
        // Normalize email before searching in the database
        $email = strtolower(trim($email));

        try {
            // Find user by email address
            $stmt = $this->conn->prepare("
                SELECT id, name, email, password
                FROM users
                WHERE email = :email
                LIMIT 1
            ");

            // Execute query with submitted email
            $stmt->execute([
                ':email' => $email
            ]);

            // Fetch user record as an associative array
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Check if user exists and password is correct
            if (!$user || !password_verify($password, $user['password'])) {
                return false;
            }

            // Regenerate session ID to prevent session fixation attacks
            session_regenerate_id(true);

            // Store authenticated user data in the session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];

            // Login successful
            return true;

        } catch (PDOException $e) {
            // Log login error and return false
            error_log('Login Error: ' . $e->getMessage());
            return false;
        }
    }

    // Logs out the current user
    public function logout()
    {
        // Clear all session variables
        $_SESSION = [];

        // If sessions use cookies, remove the session cookie from the browser
        if (ini_get('session.use_cookies')) {

            // Get current session cookie parameters
            $params = session_get_cookie_params();

            // Expire the session cookie by setting it to a past time
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        // Destroy the session completely
        session_destroy();
    }

    // Checks if a user is currently logged in
    public function check()
    {
        // User is considered logged in if user_id exists in the session
        return isset($_SESSION['user_id']);
    }

    // Returns the currently authenticated user's data
    public function user()
    {
        // Return null if no user is logged in
        if (!$this->check()) {
            return null;
        }

        // Return user data stored in the session
        return [
            'id' => $_SESSION['user_id'],
            'name' => $_SESSION['user_name'],
            'email' => $_SESSION['user_email']
        ];
    }
}