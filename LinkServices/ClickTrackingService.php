<?php

class ClickTrackingService
{
    //PDO database connection object
    private $conn;

    //Constructor receives and stores database connection
    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    //Tracks a new click for a short link
    public function track($shortLinkId)
    {
        //Get a visitor IP address
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
        //Get a visitor browser user agent string
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
        //Get a referring URL if available
        $referer = $_SERVER['HTTP_REFERER'] ?? null;

        //Detect browser name from user agent
        $browser = $this->detectBrowser($userAgent);
        //Detect device type from user agent
        $device = $this->detectDevice($userAgent);

        //Default country value, can be replaced with GeoIP detection
        $country='Unknown';

        try {
            //Prepare SQL insert statement(protect against SQL injection)
            $stmt = $this->conn->prepare("
                INSERT INTO link_clicks (
                    short_link_id,
                    ip_address,
                    user_agent,
                    referer,
                    browser,
                    device,
                    country
                )
                VALUES (
                    :short_link_id,
                    :ip_address,
                    :user_agent,
                    :referer,
                    :browser,
                    :device,
                    :country
                )
            ");

            //Execute query with actual values
            return $stmt->execute([
                ':short_link_id' => $shortLinkId,
                ':ip_address' => $ipAddress,
                ':user_agent' => $userAgent,
                ':referer' => $referer,
                ':browser' => $browser,
                ':device' => $device,
                ':country' => $country
            ]);

        } catch (PDOException $e) {
            //Stop execution and display error message
            error_log("Click tracking Error: " . $e->getMessage());
            return false;
        }
    }

    //Detect browser based on user agent string
    private function detectBrowser($userAgent)
    {
        //Return null if user agent is missing
        if (!$userAgent) {
            return null;
        }

        //Detect Microsoft Edge browser
        if (stripos($userAgent, 'Edge') !== false) {
            return 'Edge';
        }

        //Detect Google Chrome browser
        if (stripos($userAgent, 'Chrome') !== false) {
            return 'Chrome';
        }

        //Detect Mozilla Firefox browser
        if (stripos($userAgent, 'Firefox') !== false) {
            return 'Firefox';
        }

        //Detect Safari browser
        if (stripos($userAgent, 'Safari') !== false) {
            return 'Safari';
        }

        //Return fallback value if browser is unknown
        return 'Unknown';
    }

    //Detect device type based on user agent string
    private function detectDevice($userAgent)
    {
        if (!$userAgent) {
            return null;
        }

        //Detect tablet devices
        if (preg_match('/tablet/i', $userAgent)) {
            return 'Tablet';
        }

        //Detect mobile devices
        if (preg_match('/mobile/i', $userAgent)) {
            return 'Mobile';
        }

        //Default device type
        return 'Desktop';
    }

    //Returns total number of clicks for a short link
    public function getTotalClicks($shortLinkId)
    {
        try {
            //Count all click records for the given short link
            $stmt = $this->conn->prepare("
                SELECT COUNT(*)
                FROM link_clicks
                WHERE short_link_id = :short_link_id
            ");

            //Execute query
            $stmt->execute([
                ':short_link_id' => $shortLinkId
            ]);

            //fetchColumn returns the count result directly
            return (int) $stmt->fetchColumn();

        } catch (PDOException $e) {
            //Log database error into server logs
            error_log("Get total Clicks error: " . $e->getMessage());
            return 0;
        }
    }

    //Returns latest clicks for a short link
    public function getRecentClicks($shortLinkId, $limit = 10)
    {
        try {
            //Retrieve latest click records ordered by newest first
            $stmt = $this->conn->prepare("
                SELECT *
                FROM link_clicks
                WHERE short_link_id = :short_link_id
                ORDER BY clicked_at DESC
                LIMIT :limit
            ");

            //Bind parameters as integers for security
            $stmt->bindValue(':short_link_id', $shortLinkId, PDO::PARAM_INT);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);

            //Execute query
            $stmt->execute();

            //Return all rows as associative arrays
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            //Log Database error
            error_log('Get Recent Clicks Error: ' . $e->getMessage());
            return [];
        }
    }

    //Returns click statistics grouped by browser
    public function getClicksByBrowser($shortLinkId)
    {
        try {
            //Count clicks per browser
            $stmt = $this->conn->prepare("
                SELECT browser, COUNT(*) AS total
                FROM link_clicks
                WHERE short_link_id = :short_link_id
                GROUP BY browser
                ORDER BY total DESC
            ");

            //Execute query
            $stmt->execute([
                ':short_link_id' => $shortLinkId
            ]);

            //Return grouped browser statistics
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            //Log database error
            error_log('Get Clicks Browser Error: ' . $e->getMessage());
            return [];
        }
    }

    //Returns click statistics grouped by device type
    public function getClicksByDevice($shortLinkId)
    {
        try {
            //Count clicks per device type
            $stmt = $this->conn->prepare("
                SELECT device, COUNT(*) AS total
                FROM link_clicks
                WHERE short_link_id = :short_link_id
                GROUP BY device
                ORDER BY total DESC
            ");

            //Execute query
            $stmt->execute([
                ':short_link_id' => $shortLinkId
            ]);

            //Return grouped device statistics
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            //Log database error
            error_log('Get Clicks By Device Error: ' . $e->getMessage());
            return [];
        }
    }
}