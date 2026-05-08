<?php
    class AuthService
    {
        private $conn;

        public function __construct($conn)
        {
            $this->conn = $conn;
        }

        public function register($name, $email, $password)
        {
            $name = trim($name);
            $email = strtolower(trim($email));

            if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 8) {
                return false;
            }

            $passwordHashed = password_hash($password, PASSWORD_BCRYPT);

            try {
                $stmt = $this->conn->prepare("
                    INSERT INTO users(name,email,password) VALUES(:name,:email,:password)
                ");

                return $stmt->execute([
                    ':name' => $name,
                    ':email' => $email,
                    ':password' => $passwordHashed
                ]);
            } catch (PDOException $e) {
                error_log("Register Error:" . $e->getMessage());
                return false;
            }
        }

        public function login($email, $password)
        {
            $email = strtolower($email);

            try {
                $stmt = $this->conn->prepare("
                    SELECT id,name,email,password FROM users WHERE email=:email LIMIT 1
                ");

                $stmt->execute([
                    ':email' => $email
                ]);

                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$user || password_verify($password, $user['password'])) {
                    return false;
                }

                session_regenerate_id(true);

                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];

                return true;

            } catch (PDOException $e) {
                error_log("Login Error: " . $e->getMessage());
                return false;
            }
        }

        public function logout()
        {
            $_SESSION = [];

            if (ini_get('session.use_cookies')) {
                $params = session_get_cookie_params();

                setcookie(
                    session_name(),
                    '',
                    time() - 4200,
                    $params['path'],
                    $params['domain'],
                    $params['secure'],
                    $params['httponly']
                );

                session_destroy();
            }
        }

        public function check()
        {
            if (!$this->check()) {
                return null;
            }

            return [
                'id' => $_SESSION['id'],
                'name' => $_SESSION['name'],
                'email' => $_SESSION['user_email']
            ];


        }


    }