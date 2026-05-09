<?php

class ShortLinkService
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    public function create($userId, $originalUrl, $title = null, $customCode = null, $expiresAt = null)
    {
        $originalUrl = trim($originalUrl);
        $title = $title ? trim($title) : null;
        $shortCode = $customCode ? trim($customCode) : $this->generateUniqueCode();

        if (
            !filter_var($originalUrl, FILTER_VALIDATE_URL) ||
            !$this->isValidCode($shortCode) ||
            $this->codeExists($shortCode)
        ) {
            return false;
        }

        try {
            $stmt = $this->conn->prepare("
                INSERT INTO short_links (user_id, original_url, short_code, title, expires_at)
                VALUES (:user_id, :original_url, :short_code, :title, :expires_at)
            ");

            return $stmt->execute([
                ':user_id' => $userId,
                ':original_url' => $originalUrl,
                ':short_code' => $shortCode,
                ':title' => $title,
                ':expires_at' => $expiresAt
            ]);

        } catch (PDOException $e) {
            error_log("Create Short Link Error: " . $e->getMessage());
            return false;
        }
    }

    public function findByCode($shortCode)
    {
        $shortCode = trim($shortCode);

        try {
            $stmt = $this->conn->prepare("
                SELECT *
                FROM short_links
                WHERE short_code = :short_code
                  AND is_active = 1
                  AND (
                        expires_at IS NULL
                        OR expires_at > NOW()
                  )
                LIMIT 1
            ");

            $stmt->execute([
                ':short_code' => $shortCode
            ]);

            $link = $stmt->fetch(PDO::FETCH_ASSOC);

            return $link ?: null;

        } catch (PDOException $e) {
            error_log("Find Short Link Error: " . $e->getMessage());
            return null;
        }
    }

    public function getUserLinks($userId)
    {
        try {
            $stmt = $this->conn->prepare("
                SELECT *
                FROM short_links
                WHERE user_id = :user_id
                ORDER BY created_at DESC
            ");

            $stmt->execute([
                ':user_id' => $userId
            ]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log('Get user links error: ' . $e->getMessage());
            return [];
        }
    }

    public function delete($linkId, $userId)
    {
        try {
            $stmt = $this->conn->prepare("
                DELETE FROM short_links
                WHERE id = :id
                  AND user_id = :user_id
            ");

            return $stmt->execute([
                ':id' => $linkId,
                ':user_id' => $userId
            ]);

        } catch (PDOException $e) {
            error_log("Delete short Link error: " . $e->getMessage());
            return false;
        }
    }

    public function deactivate($linkId, $userId)
    {
        try {
            $stmt = $this->conn->prepare("
                UPDATE short_links
                SET is_active = 0
                WHERE id = :id
                  AND user_id = :user_id
            ");

            return $stmt->execute([
                ':id' => $linkId,
                ':user_id' => $userId
            ]);

        } catch (PDOException $e) {
            error_log("Deactivate Short Link error: " . $e->getMessage());
            return false;
        }
    }

    private function generateCode($length = 6)
    {
        $characters = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890";
        $code = '';

        for ($i = 0; $i < $length; $i++) {
            $index = random_int(0, strlen($characters) - 1);
            $code .= $characters[$index];
        }

        return $code;
    }

    private function generateUniqueCode()
    {
        do {
            $code = $this->generateCode();
        } while ($this->codeExists($code));

        return $code;
    }

    private function codeExists($code)
    {
        $stmt = $this->conn->prepare("
            SELECT id
            FROM short_links
            WHERE short_code = :short_code
            LIMIT 1
        ");

        $stmt->execute([
            ':short_code' => $code
        ]);

        return (bool) $stmt->fetch();
    }

    private function isValidCode($shortCode)
    {
        return preg_match('/^[a-zA-Z0-9_-]{3,30}$/', $shortCode) === 1;
    }
}