<?php
    class ClickTrackingService{
        private $conn;

        public function __construct($conn){
            $this->conn=$conn;
        }

        public function track($shortLinkId){
            $ipAddress=$_SERVER['REMOTE_ADDR'] ?? null;
            $userAgent=$_SERVER['HTTP_USER_AGENT'] ?? null;
            $referer=$_SERVER['HTTP_REFERER'] ?? null;

            $browser=$this->dedectBrowser($userAgent);
            $device=$this->dedectDevice($userAgent);

            try{
                $stmt=$this->conn->prepare("
                    INSERT INTO link_clicks(short_link_id,ip_address,user_agent,referer,browser,device) VALUES
                    (:short_link_id,:ip_address,:user_agent,:referer,:browser,:device)                                             
                ");

                return $stmt->execute([
                   ':short_link_id'=>$shortLinkId,
                   ':ip_address'=>$ipAddress,
                   'user_agent'=>$userAgent,
                   ':referer'=>$referer,
                   ':browser'=>$browser,
                   ':device'=>$device
                ]);
            }catch (PDOException $e){
                error_log("Click tracking Error: ".$e->getMessage());
                return false;
            }
        }

        private function detectBrowser($userAgent){
            if(!$userAgent){
                return null;
            }

            if(stripos($userAgent,'Chrome')!== false){
                return 'Chrome';
            }

            if(stripos($userAgent,'Firefox')!==false){
                return 'Firefox';
            }

            if(stripos($userAgent,'Safari')!==false){
                return 'Safari';
            }

            if(stripos($userAgent,'Edge')!==false){
                return 'Edge';
            }

            return 'Unknown';
        }

        private function detectDevice($userAgent){
            if(!$userAgent){
                return null;
            }

            if(preg_match('/tablet/i',$userAgent)){
                return 'Tablet';
            }

            if(preg_match('/mobile/i',$userAgent)){
                return 'Mobile';
            }
            return 'Desktop';
        }
    }