<?php

class ClickTrackingService
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    public function track($shortLinkId)
    {
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
        $referer = $_SERVER['HTTP_REFERER'] ?? null;

        $browser = $this->detectBrowser($userAgent);
        $device = $this->detectDevice($userAgent);

        try {
            $stmt = $this->conn->prepare("
                INSERT INTO link_clicks (
                    short_link_id,
                    ip_address,
                    user_agent,
                    referer,
                    browser,
                    device
                )
                VALUES (
                    :short_link_id,
                    :ip_address,
                    :user_agent,
                    :referer,
                    :browser,
                    :device
                )
            ");

            return $stmt->execute([
                ':short_link_id' => $shortLinkId,
                ':ip_address' => $ipAddress,
                ':user_agent' => $userAgent,
                ':referer' => $referer,
                ':browser' => $browser,
                ':device' => $device
            ]);

        } catch (PDOException $e) {
            error_log("Click tracking Error: " . $e->getMessage());
            return false;
        }
    }

    private function detectBrowser($userAgent)
    {
        if (!$userAgent) {
            return null;
        }

        if (stripos($userAgent, 'Edge') !== false) {
            return 'Edge';
        }

        if (stripos($userAgent, 'Chrome') !== false) {
            return 'Chrome';
        }

        if (stripos($userAgent, 'Firefox') !== false) {
            return 'Firefox';
        }

        if (stripos($userAgent, 'Safari') !== false) {
            return 'Safari';
        }

        return 'Unknown';
    }

    private function detectDevice($userAgent)
    {
        if (!$userAgent) {
            return null;
        }

        if (preg_match('/tablet/i', $userAgent)) {
            return 'Tablet';
        }

        if (preg_match('/mobile/i', $userAgent)) {
            return 'Mobile';
        }

        return 'Desktop';
    }

    public function getTotalClicks($shortLinkId)
    {
        try {
            $stmt = $this->conn->prepare("
                SELECT COUNT(*)
                FROM link_clicks
                WHERE short_link_id = :short_link_id
            ");

            $stmt->execute([
                ':short_link_id' => $shortLinkId
            ]);

            return (int) $stmt->fetchColumn();

        } catch (PDOException $e) {
            error_log("Get total Clicks error: " . $e->getMessage());
            return 0;
        }
    }

    public function getRecentClicks($shortLinkId, $limit = 10)
    {
        try {
            $stmt = $this->conn->prepare("
                SELECT *
                FROM link_clicks
                WHERE short_link_id = :short_link_id
                ORDER BY clicked_at DESC
                LIMIT :limit
            ");

            $stmt->bindValue(':short_link_id', $shortLinkId, PDO::PARAM_INT);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);

            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log('Get Recent Clicks Error: ' . $e->getMessage());
            return [];
        }
    }

    public function getClicksByBrowser($shortLinkId)
    {
        try {
            $stmt = $this->conn->prepare("
                SELECT browser, COUNT(*) AS total
                FROM link_clicks
                WHERE short_link_id = :short_link_id
                GROUP BY browser
                ORDER BY total DESC
            ");

            $stmt->execute([
                ':short_link_id' => $shortLinkId
            ]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log('Get Clicks Browser Error: ' . $e->getMessage());
            return [];
        }
    }

    public function getClicksByDevice($shortLinkId)
    {
        try {
            $stmt = $this->conn->prepare("
                SELECT device, COUNT(*) AS total
                FROM link_clicks
                WHERE short_link_id = :short_link_id
                GROUP BY device
                ORDER BY total DESC
            ");

            $stmt->execute([
                ':short_link_id' => $shortLinkId
            ]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log('Get Clicks By Device Error: ' . $e->getMessage());
            return [];
        }
    }
}