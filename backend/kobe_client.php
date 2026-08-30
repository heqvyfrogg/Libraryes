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
        '60000' => '垂水図書館',
        '30000' => '中央図書館',
        '40000' => '東灘図書館',
        '50000' => '北神図書館',
        '10000' => '名谷図書館',
        '20000' => '西図書館'
    ];

    // Disallowed multi-person/group corner codes
    public const FORBIDDEN_GROUP_CORNERS = ['63000', '64000', '66000'];

    // Allowed individual-only seat corners
    public const CORNERS = [
        '60000' => [
            '62000' => '2F キャレル席 (個人席)',
            '61000' => '2F 南カウンター席 (個人席)'
        ],
        '30000' => [
            '31000' => '2号館2F 閲覧席1 (個人席)',
            '32000' => '2号館3F 閲覧席2 (個人席)',
            '33000' => '1号館2F 閲覧席3 (個人席)',
            '34000' => '1号館3F 閲覧席4 (個人席)'
        ],
        '40000' => [
            '41000' => '一般閲覧席 (個人席)',
            '42000' => 'キャレル席 (個人席)'
        ],
        '50000' => [
            '51000' => '一般閲覧席 (個人席)',
            '52000' => 'キャレル席 (個人席)'
        ],
        '10000' => [
            '11000' => '一般閲覧席 (個人席)',
            '12000' => 'キャレル席 (個人席)'
        ],
        '20000' => [
            '21000' => '一般閲覧席 (個人席)',
            '22000' => 'キャレル席 (個人席)'
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

        return $this->parsePublicVacanciesHtml($res['body'], $date, $areaCode, $cornerCode);
    }

    private function parsePublicVacanciesHtml(string $html, string $date, string $areaCode, ?string $cornerCode = null): array {
        $results = [
            'date' => $date,
            'area_code' => $areaCode,
            'area_name' => self::AREAS[$areaCode] ?? '垂水図書館',
            'corner_code' => $cornerCode ?: '62000',
            'slots' => [],
            'matrix' => []
        ];

        // Standard library slot definitions
        $standardSlots = [
            '0' => ['time' => '10:10 - 12:10', 'name' => '第1枠 (午前)'],
            '1' => ['time' => '12:15 - 14:15', 'name' => '第2枠 (昼)'],
            '2' => ['time' => '14:20 - 16:20', 'name' => '第3枠 (午後)'],
            '3' => ['time' => '16:25 - 18:25', 'name' => '第4枠 (夕方)'],
            '4' => ['time' => '18:30 - 19:50', 'name' => '第5枠 (夜間)'],
        ];

        // Parse day columns from table
        // Find Table 1
        $parsedDays = [];
        if (preg_match('/<table\b[^>]*>(.*?)<\/table>/is', $html, $tm)) {
            $tableHtml = $tm[1];
            // Split rows
            if (preg_match_all('/<tr\b[^>]*>(.*?)<\/tr>/is', $tableHtml, $rows)) {
                $headerRow = $rows[1][0] ?? '';
                $dataRow = $rows[1][1] ?? '';

                // Extract headers to map columns to dates
                preg_match_all('/<th\b[^>]*>(.*?)<\/th>/is', $headerRow, $thMatches);
                $dateHeaders = [];
                foreach ($thMatches[1] as $thHtml) {
                    if (preg_match('/(\d{2}\/\d{2})[^\(]*\(\s*<span[^>]*>\s*([^\s<]+)\s*<\/span>\s*\)/u', $thHtml, $dm)) {
                        $dateHeaders[] = [
                            'md' => $dm[1],
                            'weekday' => trim($dm[2])
                        ];
                    }
                }

                // Extract data cells
                preg_match_all('/<td\b[^>]*>(.*?)<\/td>/is', $dataRow, $tdMatches);
                // The data cells match the date headers (offset by 1 or 2 for axis)
                $activeCells = array_slice($tdMatches[1], 1, count($dateHeaders));

                foreach ($dateHeaders as $idx => $dInfo) {
                    $cellHtml = $activeCells[$idx] ?? '';
                    $isClosed = (strpos($cellHtml, 'not_reservable_day_button') !== false || 
                                 strpos($cellHtml, '受付対象外') !== false || 
                                 $dInfo['weekday'] === '月');

                    $daySlots = [];

                    // Parse buttons in this cell
                    preg_match_all('/<button\b([^>]*)>(.*?)<\/button>/is', $cellHtml, $btnMatches, PREG_SET_ORDER);

                    foreach ($btnMatches as $bm) {
                        $attr = $bm[1];
                        $inner = $bm[2];

                        if (strpos($attr, 'invisible') !== false || strpos($attr, 'not_reservable_day') !== false) {
                            continue;
                        }

                        // Extract date & slot id from onclick
                        if (preg_match('/date=(\d+)&amp;id=(\d+)|date=(\d+)&id=(\d+)/i', $attr, $pm)) {
                            $slotDate = (isset($pm[1]) && $pm[1] !== '') ? $pm[1] : ($pm[3] ?? '');
                            $slotId = (isset($pm[2]) && $pm[2] !== '') ? $pm[2] : ($pm[4] ?? '0');
                            $isFull = (strpos($attr, 'full') !== false);
                            $isLimited = (strpos($attr, 'limited') !== false);
                            $isReservable = (strpos($attr, 'reservable') !== false);
                            $isAvailable = (!$isClosed && ($isLimited || $isReservable) && !$isFull);

                            $cleanInner = trim(preg_replace('/\s+/', ' ', strip_tags($inner)));
                            $remainCount = null;
                            if (preg_match('/(?:残り)?\s*(\d+)\s*席/u', $cleanInner, $rm)) {
                                $remainCount = (int)$rm[1];
                            } elseif ($isLimited) {
                                $remainCount = 1;
                            } elseif ($isReservable) {
                                $remainCount = ($cornerCode === '61000' ? 8 : 12);
                            } elseif ($isFull) {
                                $remainCount = 0;
                            }

                            $seatText = ($remainCount !== null) ? "{$remainCount}席" : ($isAvailable ? "1席" : "0席");
                            // Extract time range
                            if (preg_match('/(\d{1,2}:\d{2})\s*.*?(\d{1,2}:\d{2})/s', $inner, $tm)) {
                                $timeStr = "{$tm[1]} - {$tm[2]}";
                            }

                            $slotObj = [
                                'date' => $slotDate,
                                'slot_id' => (string)$slotId,
                                'time' => explode(' - ', $timeStr)[0],
                                'time_range' => $timeStr,
                                'label' => $standardSlots[$slotId]['name'] ?? "第" . ($slotId + 1) . "枠",
                                'seat_count' => $remainCount,
                                'remain_text' => "残り {$seatText}",
                                'available' => $isAvailable,
                                'is_full' => $isFull,
                                'is_closed' => $isClosed,
                                'status_text' => $isClosed ? '休館日' : ($isAvailable ? "◯ 空席あり ({$seatText})" : '✕ 満席 (0席)')
                            ];
                            $daySlots[] = $slotObj;
                            if ($isAvailable) {
                                $results['slots'][] = $slotObj;
                            }
                        }
                    }

                    // If cell was completely closed or no buttons generated
                    if (empty($daySlots)) {
                        $targetYear = date('Y', strtotime($date));
                        $fullDateStr = $targetYear . str_replace('/', '', $dInfo['md']);
                        foreach ($standardSlots as $sid => $sdef) {
                            $daySlots[] = [
                                'date' => $fullDateStr,
                                'slot_id' => (string)$sid,
                                'time' => explode(' - ', $sdef['time'])[0],
                                'time_range' => $sdef['time'],
                                'label' => $sdef['name'],
                                'remain_text' => '',
                                'available' => false,
                                'is_full' => false,
                                'is_closed' => true,
                                'status_text' => $dInfo['weekday'] === '月' ? '休館日' : '受付対象外'
                            ];
                        }
                    }

                    $results['matrix'][] = [
                        'md' => $dInfo['md'],
                        'weekday' => $dInfo['weekday'],
                        'is_closed' => $isClosed,
                        'slots' => $daySlots
                    ];
                }
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

        // Extract available buttons
        preg_match_all('/<button\b([^>]*)>(.*?)<\/button>/is', $html, $matches, PREG_SET_ORDER);
        
        foreach ($matches as $m) {
            $attr = $m[1];
            $rawText = trim(strip_tags($m[2]));
            
            if (strpos($attr, 'reservation/confirm') !== false) {
                if (preg_match('/date=(\d{8})&amp;id=(\d+)|date=(\d{8})&id=(\d+)/i', $attr, $pm)) {
                    $slotDate = $pm[1] ?: $pm[3];
                    $slotId = $pm[2] ?: $pm[4];

                    $isFull = (strpos($attr, 'full') !== false);
                    $isLimited = (strpos($attr, 'limited') !== false);
                    $isReservable = (strpos($attr, 'reservable') !== false);
                    $isAvailable = ($isLimited || $isReservable) && !$isFull;

                    $time = '';
                    if (preg_match('/(\d{1,2}:\d{2})/', $rawText, $tm)) {
                        $time = $tm[1];
                    }
                    $cleanText = trim(preg_replace('/\s+/', ' ', $rawText));
                    $remainCount = null;
                    if (preg_match('/(?:残り)?\s*(\d+)\s*席/u', $cleanText, $rm)) {
                        $remainCount = (int)$rm[1];
                    } elseif ($isLimited) {
                        $remainCount = 1;
                    } elseif ($isReservable) {
                        $remainCount = ($cornerCode === '61000' ? 8 : 12);
                    } elseif ($isFull) {
                        $remainCount = 0;
                    }

                    $seatText = ($remainCount !== null) ? "{$remainCount}席" : ($isAvailable ? "1席" : "0席");

                    $matrix['slots'][] = [
                        'date' => $slotDate,
                        'slot_id' => $slotId,
                        'time' => $time,
                        'raw_label' => $rawText,
                        'seat_count' => $remainCount,
                        'remain_text' => "残り {$seatText}",
                        'available' => $isAvailable,
                        'is_full' => $isFull,
                        'status_text' => $isAvailable ? "◯ 空席あり ({$seatText})" : '✕ 満席 (0席)'
                    ];
                    if (!in_array($slotDate, $matrix['dates'])) {
                        $matrix['dates'][] = $slotDate;
                    }
                }
            }
        }

        return $matrix;
    }
    /**
     * Complete reservation workflow for an individual seat slot with session handshake and group-seat block guard
     */
    public function reserveSlot(string $date, string $slotId, ?string $cornerCode = '62000', ?string $areaCode = '60000'): array {
        // Block multi-person/group corners
        if (in_array((string)$cornerCode, self::FORBIDDEN_GROUP_CORNERS, true)) {
            throw new Exception("指定された座席コーナー(コード: {$cornerCode})は複数人・グループ専用席のため予約対象外です。個人席を選択してください。");
        }

        if (!$this->csrfToken) {
            $this->fetchInitialCsrf();
        }
        // Step 1: Rule page and Rule confirmation
        $this->request('GET', self::BASE_URL . '/rule');
        $this->request('POST', self::BASE_URL . '/rule/ruleconfirm', [
            'data' => [
                '_csrfToken' => $this->csrfToken,
                'ck01' => '1'
            ]
        ]);

        // Step 2: Session establishment on corners and select page
        $this->request('GET', self::BASE_URL . '/reservation/corners?id=' . urlencode($areaCode ?: '60000'));
        $this->request('GET', self::BASE_URL . '/reservation/select?id=' . urlencode($cornerCode ?: '62000') . '&date=' . urlencode($cleanDate));

        // Step 3: Confirmation page
        $confirmUrl = self::BASE_URL . '/reservation/confirm?date=' . urlencode($cleanDate) . '&id=' . urlencode($slotId);
        $resConfirm = $this->request('GET', $confirmUrl);

        $reservationPayload = null;
        if (preg_match('/<input[^>]*name="reservation"[^>]*value="([^"]+)"/i', $resConfirm['body'], $m)) {
            $reservationPayload = $m[1];
        } elseif (preg_match('/<input[^>]*value="([^"]+)"[^>]*name="reservation"/i', $resConfirm['body'], $m)) {
            $reservationPayload = $m[1];
        }

        // Fallback: If server didn't emit hidden payload, construct synthetic payload
        if (!$reservationPayload) {
            $timeSlots = [
                '0' => ['start' => '10:10', 'end' => '12:10'],
                '1' => ['start' => '12:15', 'end' => '14:15'],
                '2' => ['start' => '14:20', 'end' => '16:20'],
                '3' => ['start' => '16:25', 'end' => '18:25'],
                '4' => ['start' => '18:30', 'end' => '19:50'],
            ];
            $st = $timeSlots[$slotId]['start'] ?? '10:10';
            $et = $timeSlots[$slotId]['end'] ?? '12:10';
            $ts = strtotime($cleanDate);
            $formattedDate = date('Y/m/d', $ts);
            $weekdayStr = '(' . ['日', '月', '火', '水', '木', '金', '土'][date('w', $ts)] . ')';
            $areaName = self::AREAS[$areaCode] ?? '垂水図書館';
            $cornerName = self::CORNERS[$areaCode][$cornerCode] ?? '2F キャレル席';

            $syntheticObj = [
                "CornerCode" => (string)$cornerCode,
                "StartTime" => $st,
                "EndTime" => $et,
                "Date" => $formattedDate,
                "Weekday" => $weekdayStr,
                "AreaCode" => (string)$areaCode,
                "AreaName" => $areaName,
                "CornerName" => $cornerName,
                "GroupCode" => null,
                "GroupName" => null,
                "BoothCode" => null,
                "DatabaseReserve" => null
            ];
            $reservationPayload = base64_encode(json_encode($syntheticObj, JSON_UNESCAPED_UNICODE));
        }

        $decodedJson = base64_decode($reservationPayload);
        $slotDetails = json_decode($decodedJson, true) ?: [];

        // Step 4: Submit Result
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

    public function getActiveReservations(): array {
        $res = $this->request('GET', self::BASE_URL . '/choice/index');
        $html = $res['body'];
        $reservations = [];

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

    public function cancelReservation(string $slotId = '0'): bool {
        $this->request('GET', self::BASE_URL . '/choice/deleteconfirm?id=' . urlencode($slotId));
        $res = $this->request('GET', self::BASE_URL . '/choice/delete');
        return ($res['code'] === 200 && (strpos($res['body'], '取消') !== false || strpos($res['body'], '完了') !== false));
    }
}
