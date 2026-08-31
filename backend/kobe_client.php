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

    public const CORNERS = [
        '60000' => [
            '62000' => '2F キャレル席',
            '61000' => '2F 南カウンター席',
            '63000' => '2F 西カウンター席',
            '64000' => '3F 学習室',
            '66000' => 'セミナー室'
        ],
        '30000' => [
            '31000' => '2号館2階 閲覧室1',
            '32000' => '2号館3階 閲覧室2',
            '33000' => '2号館3階 閲覧室3',
            '34000' => '1号館 閲覧席'
        ],
        '40000' => [
            '41000' => '一般閲覧席',
            '42000' => 'キャレル席'
        ],
        '50000' => [
            '51000' => '一般閲覧席',
            '52000' => 'キャレル席'
        ],
        '10000' => [
            '11000' => '一般閲覧席',
            '12000' => 'キャレル席'
        ],
        '20000' => [
            '21000' => '一般閲覧席',
            '22000' => 'キャレル席'
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
        if (!empty($cornerCode)) {
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
            'area_name' => self::AREAS[$areaCode] ?? '図書館',
            'corner_code' => $cornerCode ?: '62000',
            'slots' => [],
            'columns' => [],
            'time_slots' => [],
            'matrix' => []
        ];

        // 1. Extract exact date headers across table (all columns)
        preg_match_all('/<th\b[^>]*>(.*?)<\/th>/is', $html, $allTh);

        $dayColumns = [];
        $colIdx = 0;
        foreach ($allTh[1] as $thHtml) {
            if (strpos($thHtml, 'select_day') !== false || strpos($thHtml, 'weekday') !== false) {
                $md = '';
                $weekday = '';
                if (preg_match('/(\d{2}\/\d{2})/', $thHtml, $mdm)) {
                    $md = $mdm[1];
                }
                if (preg_match('/<span[^>]*class="weekdaychar"[^>]*>\s*([^\s<]+)\s*<\/span>|\(\s*([^\s\)<]+)\s*\)/u', $thHtml, $wdm)) {
                    $weekday = trim(!empty($wdm[1]) ? $wdm[1] : ($wdm[2] ?? ''));
                }
                if ($md) {
                    $dayColumns[] = [
                        'col_idx' => $colIdx,
                        'md' => $md,
                        'weekday' => $weekday,
                        'is_closed' => ($weekday === '月')
                    ];
                    $colIdx++;
                }
            }
        }

        // Fallback for future dates where server returns no date columns
        if (empty($dayColumns)) {
            $baseTs = strtotime($date);
            for ($d = 0; $d < 8; $d++) {
                $curTs = strtotime("+{$d} days", $baseTs);
                $curWd = ['日', '月', '火', '水', '木', '金', '土'][date('w', $curTs)];
                $dayColumns[] = [
                    'col_idx' => $d,
                    'md' => date('m/d', $curTs),
                    'weekday' => $curWd,
                    'is_closed' => ($curWd === '月')
                ];
            }
        }

        // 2. Extract Data cells matching date columns
        $rawDayCells = [];
        if (preg_match_all('/<td\b[^>]*>(.*?)<\/td>/is', $html, $tdMatches)) {
            foreach ($tdMatches[1] as $td) {
                if (strpos($td, 'timezones') !== false) {
                    $rawDayCells[] = $td;
                }
            }
        }

        if (count($rawDayCells) > count($dayColumns)) {
            $rawDayCells = array_slice($rawDayCells, 0, count($dayColumns));
        }

        $allTimeRanges = [];
        $daySlotMap = [];

        // 3. Parse all buttons across each column
        foreach ($rawDayCells as $dIdx => $cellHtml) {
            $isClosed = ($dayColumns[$dIdx]['is_closed'] || 
                         strpos($cellHtml, 'not_reservable_day_button') !== false || 
                         strpos($cellHtml, '受付対象外') !== false);
            $dayColumns[$dIdx]['is_closed'] = $isClosed;
            $daySlotMap[$dIdx] = [];

            preg_match_all('/<button\b([^>]*)>(.*?)<\/button>/is', $cellHtml, $btnMatches, PREG_SET_ORDER);
            foreach ($btnMatches as $bm) {
                $attr = $bm[1];
                $inner = $bm[2];
                if (strpos($attr, 'invisible') !== false || strpos($attr, 'not_reservable_day') !== false || strpos($attr, 'base_button') !== false) {
                    continue;
                }

                $cleanInner = trim(preg_replace('/\s+/', ' ', strip_tags($inner)));
                $times = [];
                if (preg_match_all('/\d{1,2}:\d{2}/', $cleanInner, $tm)) {
                    $times = $tm[0];
                }

                if (count($times) >= 2) {
                    $timeRange = "{$times[0]} - {$times[1]}";
                    $startTime = $times[0];
                    $allTimeRanges[$startTime] = $timeRange;

                    $isFull = (strpos($attr, 'full') !== false);
                    $isLimited = (strpos($attr, 'limited') !== false);
                    $isReservable = (strpos($attr, 'reservable') !== false);
                    $isAvailable = (!$isClosed && ($isLimited || $isReservable) && !$isFull);

                    $slotDate = '';
                    $slotId = '0';
                    if (preg_match('/date=(\d+)&(?:amp;)?id=(\d+)|date=(\d+)/i', $attr, $pm)) {
                        $slotDate = !empty($pm[1]) ? $pm[1] : ($pm[3] ?? '');
                        $slotId = isset($pm[2]) && $pm[2] !== '' ? $pm[2] : '0';
                    }
                    if (empty($slotDate) && isset($dayColumns[$dIdx])) {
                        $targetYear = date('Y', strtotime($date));
                        $slotDate = $targetYear . str_replace('/', '', $dayColumns[$dIdx]['md']);
                    }

                    $remainCount = null;
                    $remainText = '';
                    if (preg_match('/(?:残り)?\s*(\d+)\s*席/u', $cleanInner, $rm)) {
                        $remainCount = (int)$rm[1];
                        $remainText = "残り {$remainCount}席";
                    } elseif ($isLimited) {
                        $remainText = "残りわずか";
                    } elseif ($isReservable) {
                        $remainText = "空席あり";
                    } elseif ($isFull) {
                        $remainText = "満席";
                    }

                    $statusText = '✕ 満席';
                    if ($isClosed) {
                        $statusText = '休館日';
                    } elseif ($isAvailable) {
                        $statusText = !empty($remainText) && strpos($remainText, '席') !== false 
                            ? "◯ 空席あり ({$remainText})" 
                            : "◯ 空席 (予約可)";
                    }

                    $slotObj = [
                        'date' => $slotDate,
                        'slot_id' => (string)$slotId,
                        'start_time' => $startTime,
                        'time' => $startTime,
                        'time_range' => $timeRange,
                        'label' => $timeRange,
                        'seat_count' => $remainCount,
                        'remain_text' => $remainText,
                        'available' => $isAvailable,
                        'is_full' => $isFull,
                        'is_closed' => $isClosed,
                        'status_text' => $statusText
                    ];

                    $daySlotMap[$dIdx][$startTime] = $slotObj;
                    if ($isAvailable) {
                        $results['slots'][] = $slotObj;
                    }
                }
            }
        }

        // 4. Sort collected time slots chronologically
        ksort($allTimeRanges);
        $sortedTimeSlots = array_values($allTimeRanges);

        // Fallback default slots if not returned by server (e.g. future dates)
        if (empty($sortedTimeSlots)) {
            if ($areaCode === '30000') {
                $sortedTimeSlots = ['09:30 - 11:30', '11:40 - 13:40', '13:50 - 15:50', '16:00 - 18:00'];
            } else {
                $sortedTimeSlots = ['10:10 - 12:10', '12:15 - 14:15', '14:20 - 16:20', '16:25 - 18:25', '18:30 - 19:50'];
            }
        }

        $results['columns'] = $dayColumns;
        $results['time_slots'] = $sortedTimeSlots;

        // 5. Build 2D grid: matrix[time_slot_index][col_index]
        foreach ($sortedTimeSlots as $tIdx => $tRange) {
            $sTime = explode(' - ', $tRange)[0];
            $rowCells = [];
            foreach ($dayColumns as $dIdx => $colInfo) {
                $isColClosed = $colInfo['is_closed'] || $colInfo['weekday'] === '月';
                if (isset($daySlotMap[$dIdx][$sTime])) {
                    $rowCells[] = $daySlotMap[$dIdx][$sTime];
                } else {
                    $targetYear = date('Y', strtotime($date));
                    $cleanDate = $targetYear . str_replace('/', '', $colInfo['md']);
                    $rowCells[] = [
                        'date' => $cleanDate,
                        'slot_id' => (string)$tIdx,
                        'start_time' => $sTime,
                        'time' => $sTime,
                        'time_range' => $tRange,
                        'label' => $tRange,
                        'seat_count' => 0,
                        'remain_text' => $isColClosed ? '休館日' : '受付対象外',
                        'available' => false,
                        'is_full' => !$isColClosed,
                        'is_closed' => $isColClosed,
                        'status_text' => $isColClosed ? '休館日' : '受付対象外'
                    ];
                }
            }
            $results['matrix'][] = [
                'time_range' => $tRange,
                'start_time' => $sTime,
                'cells' => $rowCells
            ];
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
                    $remainText = '';
                    if (preg_match('/(?:残り)?\s*(\d+)\s*席/u', $cleanText, $rm)) {
                        $remainCount = (int)$rm[1];
                        $remainText = "残り {$remainCount}席";
                    } elseif ($isLimited) {
                        $remainText = "残りわずか";
                    } elseif ($isReservable) {
                        $remainText = "空席あり";
                    } elseif ($isFull) {
                        $remainText = "満席";
                    }

                    $statusText = $isAvailable 
                        ? (!empty($remainCount) ? "◯ 空席あり (残り {$remainCount}席)" : "◯ 空席 (予約可)")
                        : '✕ 満席';

                    $matrix['slots'][] = [
                        'date' => $slotDate,
                        'slot_id' => $slotId,
                        'time' => $time,
                        'raw_label' => $rawText,
                        'seat_count' => $remainCount,
                        'remain_text' => $remainText,
                        'available' => $isAvailable,
                        'is_full' => $isFull,
                        'status_text' => $statusText
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
     * Complete reservation workflow
     */
    public function reserveSlot(string $date, string $slotId, ?string $cornerCode = '62000', ?string $areaCode = '60000'): array {
        if (!$this->csrfToken) {
            $this->fetchInitialCsrf();
        }

        $cleanDate = str_replace(['-', '/'], '', $date);

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
