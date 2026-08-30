<?php
/**
 * Kobe City Library eBoothWeb Automation Client
 * Libraryes Automation Core
 */

class KobeLibraryClient {
    public const BASE_URL = 'https://ebwebreserve3.tackport.co.jp/eboothweb_kobe';
    public const USER_AGENT = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36';

    private string $cookieJar;
    private ?string $csrfToken = null;
    private ?string $usercode = null;
    private ?string $password = null;

    public const AREAS = [
        '10000' => '名谷図書館',
        '20000' => '西図書館',
        '30000' => '中央図書館',
        '40000' => '東灘図書館',
        '50000' => '北神図書館',
        '60000' => '垂水図書館'
    ];

    public const CORNERS = [
        '60000' => [
            '61000' => '2F 南カウンター席',
            '62000' => '2F キャレル席',
            '63000' => '2F 西カウンター席',
            '64000' => '3F 学習室',
            '66000' => 'セミナー室'
        ]
    ];

    public function __construct(?string $cookieFile = null) {
        if ($cookieFile === null) {
            $tmpDir = __DIR__ . '/../data/cookies';
            if (!is_dir($tmpDir)) {
                mkdir($tmpDir, 0755, true);
            }
            $this->cookieJar = $tmpDir . '/cookie_' . md5(uniqid('', true)) . '.txt';
        } else {
            $this->cookieJar = $cookieFile;
        }
    }

    public function __destruct() {
        // Leave cookie jar or clean up if needed
    }

    public function getCookieJar(): string {
        return $this->cookieJar;
    }

    private function request(string $method, string $url, array $options = []): array {
        $ch = curl_init();
        $headers = $options['headers'] ?? [];
        $headers[] = 'User-Agent: ' . self::USER_AGENT;
        $headers[] = 'Accept-Language: ja-JP,ja;q=0.9,en;q=0.8';

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, $options['follow'] ?? true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookieJar);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookieJar);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_TIMEOUT, $options['timeout'] ?? 15);

        if (strtoupper($method) === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if (isset($options['data'])) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($options['data']));
                $headers[] = 'Content-Type: application/x-www-form-urlencoded';
            }
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $effectiveUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
        $error = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            throw new Exception("cURL Error [{$url}]: {$error}");
        }

        // Try extracting CSRF token if present
        if (preg_match('/name="_csrfToken"\s+(?:autocomplete="off"\s+)?value="([a-f0-9]+)"/i', $response, $m)) {
            $this->csrfToken = $m[1];
        }

        return [
            'code' => $httpCode,
            'url' => $effectiveUrl,
            'body' => $response
        ];
    }

    public function fetchInitialCsrf(): string {
        $res = $this->request('GET', self::BASE_URL . '/areainfo/login');
        if (!$this->csrfToken) {
            if (preg_match('/name="_csrfToken"\s+[^>]*value="([^"]+)"/i', $res['body'], $m)) {
                $this->csrfToken = $m[1];
            }
        }
        if (!$this->csrfToken) {
            throw new Exception("Failed to extract CSRF token from login page.");
        }
        return $this->csrfToken;
    }

    public function login(string $usercode, string $password): bool {
        $this->usercode = $usercode;
        $this->password = $password;

        if (!$this->csrfToken) {
            $this->fetchInitialCsrf();
        }

        $res = $this->request('POST', self::BASE_URL . '/areainfo/login', [
            'data' => [
                '_method' => 'POST',
                '_csrfToken' => $this->csrfToken,
                'usercode' => $usercode,
                'password' => $password,
            ],
            'follow' => true
        ]);

        if (strpos($res['url'], '/menu') !== false || strpos($res['body'], '座席の予約') !== false) {
            return true;
        }

        // Check if there is an error message in HTML
        if (preg_match('/<div[^>]*class="[^"]*text-danger[^"]*"[^>]*>(.*?)<\/div>/is', $res['body'], $m)) {
            $msg = trim(strip_tags($m[1]));
            if ($msg) {
                throw new Exception("Login failed: " . $msg);
            }
        }

        return false;
    }

    /**
     * Get public vacancy overview (No login required)
     */
    public function getPublicVacancies(string $date, string $areaCode = '60000', ?string $cornerCode = null): array {
        $query = [
            'date' => str_replace(['-', '/'], '', $date),
            'area' => $areaCode
        ];
        if ($cornerCode) {
            $query['corner'] = $cornerCode;
        }

        $url = self::BASE_URL . '/areainfo/usagesWeb?' . http_build_query($query);
        $res = $this->request('GET', $url);

        return $this->parsePublicVacanciesHtml($res['body'], $date, $areaCode);
    }

    private function parsePublicVacanciesHtml(string $html, string $date, string $areaCode): array {
        $results = [
            'date' => $date,
            'area_code' => $areaCode,
            'area_name' => self::AREAS[$areaCode] ?? '垂水図書館',
            'slots' => []
        ];

        // Parse tables or slot buttons from usagesWeb HTML
        // Buttons often have: onclick="location.href='/eboothweb_kobe/reservation/login?date=...&id=0'"
        preg_match_all('/<button[^>]*onclick="location\.href=\'([^\']+)\'"[^>]*>(.*?)<\/button>/is', $html, $matches, PREG_SET_ORDER);
        foreach ($matches as $m) {
            $href = html_entity_decode($m[1]);
            $btnText = trim(strip_tags($m[2]));
            if (preg_match('/date=(\d+)&amp;id=(\d+)|date=(\d+)&id=(\d+)/i', $href, $pm)) {
                $pDate = $pm[1] ?: $pm[3];
                $pId = $pm[2] ?: $pm[4];
                $results['slots'][] = [
                    'date' => $pDate,
                    'slot_id' => $pId,
                    'label' => $btnText,
                    'available' => true,
                    'url' => $href
                ];
            }
        }

        return $results;
    }

    /**
     * Get authenticated reservation matrix for a specific corner and date
     */
    public function getReservationMatrix(string $cornerCode = '62000', ?string $date = null): array {
        $url = self::BASE_URL . '/reservation/select?id=' . urlencode($cornerCode);
        if ($date) {
            $url .= '&date=' . str_replace(['-', '/'], '', $date);
        }

        $res = $this->request('GET', $url);
        return $this->parseReservationMatrixHtml($res['body'], $cornerCode);
    }

    private function parseReservationMatrixHtml(string $html, string $cornerCode): array {
        $matrix = [
            'corner_code' => $cornerCode,
            'dates' => [],
            'slots' => []
        ];

        // Extract available buttons with confirmation URLs
        // Pattern: onclick="location.href='/eboothweb_kobe/reservation/confirm?date=20260902&amp;id=0'"
        preg_match_all('/<button[^>]*onclick="location\.href=\'([^\']*\/reservation\/confirm\?[^\']+)\'"[^>]*>(.*?)<\/button>/is', $html, $matches, PREG_SET_ORDER);
        
        foreach ($matches as $m) {
            $href = html_entity_decode($m[1]);
            $rawText = trim(strip_tags($m[2]));
            
            if (preg_match('/date=(\d{8})&id=(\d+)/i', $href, $pm)) {
                $slotDate = $pm[1];
                $slotId = $pm[2];
                
                // Extract time if text has format e.g. "10:10"
                $time = '';
                if (preg_match('/(\d{1,2}:\d{2})/', $rawText, $tm)) {
                    $time = $tm[1];
                }

                $matrix['slots'][] = [
                    'date' => $slotDate,
                    'slot_id' => $slotId,
                    'time' => $time,
                    'raw_label' => $rawText,
                    'confirm_url' => $href,
                    'available' => true
                ];

                if (!in_array($slotDate, $matrix['dates'])) {
                    $matrix['dates'][] = $slotDate;
                }
            }
        }

        return $matrix;
    }

    /**
     * Complete reservation workflow for a given slot
     */
    public function reserveSlot(string $date, string $slotId, ?string $cornerCode = '62000'): array {
        // Step 1: Confirm rule acceptance
        if (!$this->csrfToken) {
            $this->fetchInitialCsrf();
        }

        $this->request('POST', self::BASE_URL . '/rule/ruleconfirm', [
            'data' => [
                '_csrfToken' => $this->csrfToken,
                'ck01' => '1'
            ]
        ]);

        // Step 2: Access confirmation page to obtain exact base64 payload
        $confirmUrl = self::BASE_URL . '/reservation/confirm?date=' . urlencode($date) . '&id=' . urlencode($slotId);
        $resConfirm = $this->request('GET', $confirmUrl);

        // Extract hidden reservation field
        $reservationPayload = null;
        if (preg_match('/<input[^>]*name="reservation"[^>]*value="([^"]+)"/i', $resConfirm['body'], $m)) {
            $reservationPayload = $m[1];
        } elseif (preg_match('/<input[^>]*value="([^"]+)"[^>]*name="reservation"/i', $resConfirm['body'], $m)) {
            $reservationPayload = $m[1];
        }

        if (!$reservationPayload) {
            // Check if slot was already taken
            if (strpos($resConfirm['body'], '満席') !== false || strpos($resConfirm['body'], '予約できません') !== false) {
                throw new Exception("Slot {$date} ID:{$slotId} is no longer available (Already booked).");
            }
            throw new Exception("Failed to retrieve reservation token from confirm page.");
        }

        // Decode payload for detail inspection
        $decodedJson = base64_decode($reservationPayload);
        $slotDetails = json_decode($decodedJson, true) ?: [];

        // Step 3: POST reservation result
        $resResult = $this->request('POST', self::BASE_URL . '/reservation/result', [
            'data' => [
                '_csrfToken' => $this->csrfToken,
                'reservation' => $reservationPayload
            ]
        ]);

        $isSuccess = (strpos($resResult['body'], '予約が完了') !== false || 
                      strpos($resResult['body'], '予約完了') !== false ||
                      strpos($resResult['body'], '予約番号') !== false ||
                      $resResult['code'] === 200);

        // Extract reservation number if present
        $reservationNumber = null;
        if (preg_match('/予約番号[^\d]*(\d+)/u', $resResult['body'], $rnm)) {
            $reservationNumber = $rnm[1];
        }

        return [
            'success' => $isSuccess,
            'reservation_number' => $reservationNumber,
            'details' => $slotDetails,
            'raw_payload' => $reservationPayload,
            'response_url' => $resResult['url']
        ];
    }

    /**
     * Get active user reservations
     */
    public function getActiveReservations(): array {
        $res = $this->request('GET', self::BASE_URL . '/choice/index');
        $html = $res['body'];
        $reservations = [];

        // Parse reservation cards / table rows
        // Look for items with deleteconfirm / rule links
        if (preg_match_all('/<tr\b[^>]*>(.*?)<\/tr>/is', $html, $rows)) {
            foreach ($rows[1] as $row) {
                if (strpos($row, 'deleteconfirm') !== false || strpos($row, '取消') !== false) {
                    $item = ['raw' => strip_tags($row)];
                    if (preg_match('/deleteconfirm\?id=(\d+)/i', $row, $dm)) {
                        $item['id'] = $dm[1];
                    }
                    if (preg_match('/(\d{4}\/\d{2}\/\d{2})/', $row, $dtm)) {
                        $item['date'] = $dtm[1];
                    }
                    if (preg_match('/(\d{1,2}:\d{2})\s*～\s*(\d{1,2}:\d{2})/', $row, $tm)) {
                        $item['time'] = $tm[1] . ' - ' . $tm[2];
                    }
                    $reservations[] = $item;
                }
            }
        }

        return $reservations;
    }

    /**
     * Cancel an active reservation
     */
    public function cancelReservation(string $slotId = '0'): bool {
        // Step 1: Delete confirmation page
        $this->request('GET', self::BASE_URL . '/choice/deleteconfirm?id=' . urlencode($slotId));

        // Step 2: Perform delete
        $res = $this->request('GET', self::BASE_URL . '/choice/delete');
        return ($res['code'] === 200 && (strpos($res['body'], '取消') !== false || strpos($res['body'], '完了') !== false));
    }
}
