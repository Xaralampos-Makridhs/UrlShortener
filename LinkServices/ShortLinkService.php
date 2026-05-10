<?php

class ShortLinkService
{
    //PDO database connection object
    private $conn;

    //Constructor receives and stores database connection
    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    //Create a new short link for a user
    public function create($userId, $originalUrl, $title = null, $customCode = null, $expiresAt = null)
    {
        //Remove extra spaces for the original URL
        $originalUrl = trim($originalUrl);
        //Trim the title if provided, otherwise keep it as null
        $title = $title ? trim($title) : null;
        //User custom code if provided,otherwise generate a unique random code
        $shortCode = $customCode ? trim($customCode) : $this->generateUniqueCode();

        //Validate URL,validate short code format, and check if code already exists
        if (!filter_var($originalUrl, FILTER_VALIDATE_URL) || !$this->isValidCode($shortCode) || $this->codeExists($shortCode)) {
            return false;
        }

        try {
            //Prepare SQL query to insert the new short link
            $stmt = $this->conn->prepare("
                INSERT INTO short_links (user_id, original_url, short_code, title, expires_at)
                VALUES (:user_id, :original_url, :short_code, :title, :expires_at)
            ");

            //Execute the query with the provided values
            return $stmt->execute([
                ':user_id' => $userId,
                ':original_url' => $originalUrl,
                ':short_code' => $shortCode,
                ':title' => $title,
                ':expires_at' => $expiresAt
            ]);

        } catch (PDOException $e) {
            //Log database error and return false
            error_log("Create Short Link Error: " . $e->getMessage());
            return false;
        }
    }

    //Finds an active and non-expired short link by its short code
    public function findByCode($shortCode)
    {
        //Remove the extra spaces from the short code
        $shortCode = trim($shortCode);

        try {
            //Select only active links that are not expired
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

            //Execute query with the given short code
            $stmt->execute([
                ':short_code' => $shortCode
            ]);

            //Fetch the matching link as an associative array
            $link = $stmt->fetch(PDO::FETCH_ASSOC);

            //Return the link if found,otherwise return null
            return $link ?: null;

        } catch (PDOException $e) {
            //Log database error
            error_log("Find Short Link Error: " . $e->getMessage());
            return null;
        }
    }

    //Returns all short links created by a specific user
    public function getUserLinks($userId)
    {
        try {
            //Select all links that belongs to the given user
            $stmt = $this->conn->prepare("
                SELECT *
                FROM short_links
                WHERE user_id = :user_id
                ORDER BY created_at DESC
            ");

            //Execute query with user ID
            $stmt->execute([
                ':user_id' => $userId
            ]);

            //Return all user links as associative array
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            //Log database error
            error_log('Get user links error: ' . $e->getMessage());
            return [];
        }
    }

    //Permanently deletes a short link and its related click records
    public function delete($linkId, $userId)
    {
        try {
            //Start transaction so both delete operations succeed or fail together
            $this->conn->beginTransaction();

            //Delete all click tracking records for this short link
            $stmt = $this->conn->prepare("
                DELETE FROM link_clicks
                WHERE short_link_id = :link_id
            ");

            //Execute click delete array
            $stmt->execute([
                ':link_id' => $linkId
            ]);

            //Delete the short link only if it belongs to the given user
            $stmt = $this->conn->prepare("
                DELETE FROM short_links
                WHERE id = :id
                    AND user_id = :user_id
            ");

            //Executes the query
            $result = $stmt->execute([
                ':id' => $linkId,
                ':user_id' => $userId
            ]);

            //Commit transaction if both queries completed successfully
            $this->conn->commit();

            //Return delete result
            return $result;

        } catch (PDOException $e) {
            //Roll back transaction if an error occurs
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }

            //Stop execution and display error
            error_log('Delete Error: ' . $e->getMessage());
        }
    }

    //Deactivates a short link without deleting it from the database
    public function deactivate($linkId, $userId)
    {
        try {
            //Set is_active to 0 for the selected user link
            $stmt = $this->conn->prepare("
            UPDATE short_links
            SET is_active = 0
            WHERE id = :id
              AND user_id = :user_id
        ");

            //Execute update query
            return $stmt->execute([
                ':id' => $linkId,
                ':user_id' => $userId
            ]);

        } catch (PDOException $e) {
            //Log database error
            error_log('Deactivate Error: ' . $e->getMessage());
        }
    }

    //Generates a random short code with the given length
    private function generateCode($length = 6)
    {
        //Allowed characters for the generated short code
        $characters = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890";
        //Empty string where the generated code will be stored
        $code = '';

        //Build the code character by character
        for ($i = 0; $i < $length; $i++) {
            //Generate a random index from the characters string
            $index = random_int(0, strlen($characters) - 1);
            //Add the selected character to the code
            $code .= $characters[$index];
        }

        //Returns the code
        return $code;
    }

    //Generates a unique short code that does not already exist in the database
    private function generateUniqueCode()
    {
        //Keep generating codes until a unique one is found
        do {
            $code = $this->generateCode();
        } while ($this->codeExists($code));

        //Return the unique code
        return $code;
    }

    //Check if a short code already exists in the database
    private function codeExists($code)
    {
        //Search for an existing short link with the same code
        $stmt = $this->conn->prepare("
            SELECT id
            FROM short_links
            WHERE short_code = :short_code
            LIMIT 1
        ");

        //Executes the query
        $stmt->execute([
            ':short_code' => $code
        ]);

        //Reutrn true if the record exists otherwise false
        return (bool) $stmt->fetch();
    }

    //Validates short code format
    private function isValidCode($shortCode)
    {
        // Allow only letters, numbers, underscores, and hyphens
        // Code length must be between 3 and 30 characters
        return preg_match('/^[a-zA-Z0-9_-]{3,30}$/', $shortCode) === 1;
    }
}