<?php
/**
 * AI Optimal Time Recommendation & Scoring Engine
 * Libraryes AI Core
 */

class AIEngine {
    /**
     * Standard slot time patterns for Kobe City Libraries
     */
    public const TIME_PROFILES = [
        '09:30' => ['concentration' => 95, 'quietness' => 90, 'crowd_tendency' => 35, 'fatigue' => 10],
        '10:10' => ['concentration' => 92, 'quietness' => 85, 'crowd_tendency' => 45, 'fatigue' => 15],
        '11:40' => ['concentration' => 75, 'quietness' => 70, 'crowd_tendency' => 70, 'fatigue' => 35],
        '12:15' => ['concentration' => 70, 'quietness' => 65, 'crowd_tendency' => 80, 'fatigue' => 40],
        '13:50' => ['concentration' => 80, 'quietness' => 75, 'crowd_tendency' => 85, 'fatigue' => 50],
        '14:20' => ['concentration' => 82, 'quietness' => 78, 'crowd_tendency' => 80, 'fatigue' => 50],
        '16:00' => ['concentration' => 88, 'quietness' => 88, 'crowd_tendency' => 60, 'fatigue' => 55],
        '16:25' => ['concentration' => 87, 'quietness' => 85, 'crowd_tendency' => 55, 'fatigue' => 55],
        '18:30' => ['concentration' => 90, 'quietness' => 95, 'crowd_tendency' => 30, 'fatigue' => 65],
    ];

    /**
     * Evaluate and rank available slots using multi-factor AI scoring
     */
    public static function evaluateSlots(array $availableSlots, string $purpose = 'focus', ?string $preferredTime = null): array {
        $scored = [];

        foreach ($availableSlots as $slot) {
            $score = self::calculateSlotScore($slot, $purpose, $preferredTime);
            $reasons = self::generateReasoning($slot, $purpose, $score);

            $slot['ai_score'] = $score['total'];
            $slot['breakdown'] = $score['breakdown'];
            $slot['recommendation_tag'] = $score['tag'];
            $slot['reasons'] = $reasons;

            $scored[] = $slot;
        }

        // Sort descending by total score
        usort($scored, fn($a, $b) => $b['ai_score'] <=> $a['ai_score']);

        return $scored;
    }

    /**
     * Calculate multi-criteria score (0-100)
     */
    private static function calculateSlotScore(array $slot, string $purpose, ?string $preferredTime): array {
        $timeStr = $slot['time'] ?? '10:00';
        $closestProfileKey = self::matchClosestProfile($timeStr);
        $profile = self::TIME_PROFILES[$closestProfileKey];

        $weights = [
            'concentration' => 0.35,
            'quietness' => 0.25,
            'crowd_avoidance' => 0.25,
            'preference_match' => 0.15,
        ];

        if ($purpose === 'pc_work') {
            $weights = ['concentration' => 0.30, 'quietness' => 0.15, 'crowd_avoidance' => 0.25, 'preference_match' => 0.30];
        } elseif ($purpose === 'quick_read') {
            $weights = ['concentration' => 0.20, 'quietness' => 0.20, 'crowd_avoidance' => 0.40, 'preference_match' => 0.20];
        } elseif ($purpose === 'long_study') {
            $weights = ['concentration' => 0.40, 'quietness' => 0.30, 'crowd_avoidance' => 0.20, 'preference_match' => 0.10];
        }

        $crowdAvoidanceScore = 100 - $profile['crowd_tendency'];
        $preferenceScore = 80;

        if (!empty($preferredTime)) {
            $timeDiffMinutes = self::calculateTimeDiffMinutes($timeStr, $preferredTime);
            if ($timeDiffMinutes === 0) {
                $preferenceScore = 100;
            } elseif ($timeDiffMinutes <= 60) {
                $preferenceScore = 90;
            } elseif ($timeDiffMinutes <= 120) {
                $preferenceScore = 70;
            } else {
                $preferenceScore = 40;
            }
        }

        $rawTotal = (
            $profile['concentration'] * $weights['concentration'] +
            $profile['quietness'] * $weights['quietness'] +
            $crowdAvoidanceScore * $weights['crowd_avoidance'] +
            $preferenceScore * $weights['preference_match']
        );

        $totalScore = (int)round(min(100, max(0, $rawTotal)));

        $tag = 'おすすめ';
        if ($totalScore >= 90) {
            $tag = '最上位 AI 推奨';
        } elseif ($totalScore >= 80) {
            $tag = '高集中・快適枠';
        } elseif ($totalScore >= 70) {
            $tag = '良好';
        } else {
            $tag = '標準枠';
        }

        return [
            'total' => $totalScore,
            'tag' => $tag,
            'breakdown' => [
                'concentration' => $profile['concentration'],
                'quietness' => $profile['quietness'],
                'crowd_avoidance' => $crowdAvoidanceScore,
                'preference_match' => $preferenceScore
            ]
        ];
    }

    private static function matchClosestProfile(string $time): string {
        if (isset(self::TIME_PROFILES[$time])) {
            return $time;
        }
        $targetMin = self::toMinutes($time);
        $closest = '10:10';
        $minDiff = 9999;
        foreach (array_keys(self::TIME_PROFILES) as $pTime) {
            $diff = abs(self::toMinutes($pTime) - $targetMin);
            if ($diff < $minDiff) {
                $minDiff = $diff;
                $closest = $pTime;
            }
        }
        return $closest;
    }

    private static function toMinutes(string $time): int {
        $parts = explode(':', $time);
        return ((int)($parts[0] ?? 0)) * 60 + ((int)($parts[1] ?? 0));
    }

    private static function calculateTimeDiffMinutes(string $t1, string $t2): int {
        return abs(self::toMinutes($t1) - self::toMinutes($t2));
    }

    private static function generateReasoning(array $slot, string $purpose, array $score): array {
        $reasons = [];
        $time = $slot['time'] ?? '指定時刻';
        $b = $score['breakdown'];

        if ($b['concentration'] >= 90) {
            $reasons[] = "午前〜朝帯特有の脳内覚醒度ピークに合致し、最高の集中環境が期待できます。";
        } elseif ($b['concentration'] >= 85) {
            $reasons[] = "夕方の落ち着いた時間帯で、深い学習や作業に適しています。";
        }

        if ($b['crowd_avoidance'] >= 65) {
            $reasons[] = "混雑ピークを回避した時間帯のため、周囲の出入りが少なく静寂性が保たれます。";
        }

        if ($b['preference_match'] >= 90) {
            $reasons[] = "ユーザー希望時間帯と合致（または至近）しています。";
        }

        if (empty($reasons)) {
            $reasons[] = "現在利用可能な標準スロットです。";
        }

        return $reasons;
    }

    /**
     * Pick the absolute best slot for automated AI reservation
     */
    public static function selectBestSlot(array $availableSlots, string $purpose = 'focus', ?string $preferredTime = null): ?array {
        $evaluated = self::evaluateSlots($availableSlots, $purpose, $preferredTime);
        return $evaluated[0] ?? null;
    }
}
