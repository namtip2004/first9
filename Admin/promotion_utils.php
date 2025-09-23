<?php
/**
 * Helper functions for managing promotion logic.
 */

if (!function_exists('getPromotionColumns')) {
    function getPromotionColumns(mysqli $conn): array
    {
        $columns = [];
        if ($result = $conn->query("SHOW COLUMNS FROM promotion")) {
            while ($row = $result->fetch_assoc()) {
                $columns[] = $row['Field'];
            }
            $result->free();
        }
        return $columns;
    }
}

if (!function_exists('ensurePromotionSupport')) {
    function ensurePromotionSupport(mysqli $conn): void
    {
        $conn->query(
            "CREATE TABLE IF NOT EXISTS promotion_service (
                pm_service_id INT AUTO_INCREMENT PRIMARY KEY,
                service_id INT NOT NULL,
                promotion_id INT NOT NULL,
                UNIQUE KEY uniq_promotion_service (service_id, promotion_id)
            )"
        );

        $conn->query(
            "CREATE TABLE IF NOT EXISTS promotion_service_option (
                id INT AUTO_INCREMENT PRIMARY KEY,
                promotion_id INT NOT NULL,
                service_id INT NOT NULL,
                option_id INT NOT NULL,
                discount_percent DECIMAL(6,2) NOT NULL DEFAULT 0,
                discount_amount DECIMAL(10,2) NOT NULL DEFAULT 0,
                final_price DECIMAL(10,2) NOT NULL DEFAULT 0,
                is_active TINYINT(1) NOT NULL DEFAULT 1,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY uniq_promotion_option (promotion_id, option_id)
            )"
        );
    }
}

if (!function_exists('parseDateTimeValue')) {
    function parseDateTimeValue(?string $value): ?DateTimeImmutable
    {
        if (empty($value)) {
            return null;
        }

        $formats = [
            'Y-m-d\TH:i',
            'Y-m-d H:i:s',
            'Y-m-d H:i',
            DateTimeInterface::ATOM,
        ];

        foreach ($formats as $format) {
            $dt = DateTimeImmutable::createFromFormat($format, $value);
            if ($dt instanceof DateTimeImmutable) {
                return $dt;
            }
        }

        try {
            return new DateTimeImmutable($value);
        } catch (Exception $e) {
            return null;
        }
    }
}

if (!function_exists('promotionStatus')) {
    function promotionStatus(string $start, string $end, ?DateTimeImmutable $now = null): string
    {
        $now = $now ?: new DateTimeImmutable('now');
        $startDt = parseDateTimeValue($start);
        $endDt = parseDateTimeValue($end);

        if (!$startDt || !$endDt) {
            return 'unknown';
        }

        if ($now < $startDt) {
            return 'upcoming';
        }

        if ($now > $endDt) {
            return 'ended';
        }

        return 'running';
    }
}

if (!function_exists('promotionStatusLabel')) {
    function promotionStatusLabel(string $status): string
    {
        return match ($status) {
            'upcoming' => 'ยังไม่เริ่ม',
            'running' => 'กำลังดำเนินการ',
            'ended' => 'สิ้นสุด',
            default => 'ไม่ทราบ',
        };
    }
}

if (!function_exists('formatDateTimeDisplay')) {
    function formatDateTimeDisplay(?string $value): string
    {
        $dt = parseDateTimeValue($value);
        return $dt ? $dt->format('Y-m-d H:i') : '-';
    }
}

if (!function_exists('findConflictingServiceIds')) {
    function findConflictingServiceIds(mysqli $conn, array $serviceIds, string $start, string $end, ?int $excludePromotionId = null): array
    {
        if (empty($serviceIds)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($serviceIds), '?'));
        $sql = "SELECT DISTINCT ps.service_id
                FROM promotion_service ps
                INNER JOIN promotion p ON p.promotion_id = ps.promotion_id
                WHERE ps.service_id IN ($placeholders)
                  AND p.pm_start_date <= ?
                  AND p.pm_end_date >= ?";

        $types = str_repeat('i', count($serviceIds)) . 'ss';
        $params = array_values($serviceIds);
        $params[] = $end;
        $params[] = $start;

        if ($excludePromotionId) {
            $sql .= " AND ps.promotion_id <> ?";
            $types .= 'i';
            $params[] = $excludePromotionId;
        }

        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            return [];
        }

        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        $conflicts = [];
        while ($row = $result->fetch_assoc()) {
            $conflicts[] = (int)$row['service_id'];
        }
        $stmt->close();

        return $conflicts;
    }
}

if (!function_exists('getAvailablePromotionServices')) {
    function getAvailablePromotionServices(mysqli $conn, string $start, string $end, ?int $promotionId = null): array
    {
        $services = [];
        $sql = "SELECT service_id, service_name
                FROM service
                WHERE is_active = 1
                ORDER BY service_name";

        if ($result = $conn->query($sql)) {
            while ($row = $result->fetch_assoc()) {
                $services[(int)$row['service_id']] = [
                    'service_id' => (int)$row['service_id'],
                    'service_name' => $row['service_name'],
                ];
            }
            $result->free();
        }

        if (empty($services)) {
            return [];
        }

        $conflicts = findConflictingServiceIds($conn, array_keys($services), $start, $end, $promotionId);
        foreach ($conflicts as $conflictId) {
            unset($services[$conflictId]);
        }

        return array_values($services);
    }
}

if (!function_exists('normalizePromotionPayload')) {
    function normalizePromotionPayload(mysqli $conn, array $payload): array
    {
        if (empty($payload)) {
            throw new InvalidArgumentException('กรุณาเลือกบริการอย่างน้อย 1 รายการ');
        }

        $serviceIds = [];
        $optionIds = [];
        foreach ($payload as $service) {
            if (!is_array($service) || !isset($service['service_id'])) {
                continue;
            }
            $sid = (int) $service['service_id'];
            if ($sid <= 0) {
                continue;
            }
            $serviceIds[$sid] = $sid;
            if (empty($service['options']) || !is_array($service['options'])) {
                throw new InvalidArgumentException('บริการที่เลือกต้องมี option อย่างน้อย 1 รายการ');
            }
            foreach ($service['options'] as $option) {
                if (!is_array($option) || !isset($option['option_id'])) {
                    continue;
                }
                $oid = (int) $option['option_id'];
                if ($oid <= 0) {
                    continue;
                }
                $optionIds[$oid] = $oid;
            }
        }

        if (empty($serviceIds) || empty($optionIds)) {
            throw new InvalidArgumentException('ข้อมูลบริการหรือ option ไม่ถูกต้อง');
        }

        // Fetch services
        $placeholders = implode(',', array_fill(0, count($serviceIds), '?'));
        $types = str_repeat('i', count($serviceIds));
        $stmt = $conn->prepare("SELECT service_id, service_name FROM service WHERE service_id IN ($placeholders)");
        if (!$stmt) {
            throw new InvalidArgumentException('ไม่สามารถตรวจสอบข้อมูลบริการได้');
        }
        $stmt->bind_param($types, ...array_values($serviceIds));
        $stmt->execute();
        $result = $stmt->get_result();
        $serviceMap = [];
        while ($row = $result->fetch_assoc()) {
            $serviceMap[(int)$row['service_id']] = $row['service_name'];
        }
        $stmt->close();

        if (count($serviceMap) !== count($serviceIds)) {
            throw new InvalidArgumentException('พบบริการที่ไม่สามารถใช้งานได้ กรุณาตรวจสอบใหม่');
        }

        // Fetch options
        $placeholders = implode(',', array_fill(0, count($optionIds), '?'));
        $types = str_repeat('i', count($optionIds));
        $stmt = $conn->prepare(
            "SELECT option_id, service_id, duration, price
             FROM service_option
             WHERE option_id IN ($placeholders)"
        );
        if (!$stmt) {
            throw new InvalidArgumentException('ไม่สามารถตรวจสอบข้อมูล option ได้');
        }
        $stmt->bind_param($types, ...array_values($optionIds));
        $stmt->execute();
        $result = $stmt->get_result();
        $optionMap = [];
        while ($row = $result->fetch_assoc()) {
            $optionMap[(int)$row['option_id']] = [
                'service_id' => (int)$row['service_id'],
                'duration' => (int)$row['duration'],
                'price' => (float)$row['price'],
            ];
        }
        $stmt->close();

        if (count($optionMap) !== count($optionIds)) {
            throw new InvalidArgumentException('พบ option ที่ไม่สามารถใช้งานได้ กรุณาตรวจสอบใหม่');
        }

        $normalized = [];
        $maxPercent = 0.0;

        foreach ($payload as $service) {
            $sid = isset($service['service_id']) ? (int)$service['service_id'] : 0;
            if ($sid <= 0 || !isset($serviceMap[$sid])) {
                continue;
            }

            $serviceEntry = [
                'service_id' => $sid,
                'service_name' => $serviceMap[$sid],
                'options' => [],
            ];

            foreach ($service['options'] as $option) {
                $oid = isset($option['option_id']) ? (int)$option['option_id'] : 0;
                if ($oid <= 0 || !isset($optionMap[$oid])) {
                    continue;
                }
                $meta = $optionMap[$oid];
                if ($meta['service_id'] !== $sid) {
                    throw new InvalidArgumentException('มี option ที่ไม่ตรงกับบริการที่เลือก');
                }
                $percent = isset($option['discount_percent']) ? (float)$option['discount_percent'] : 0.0;
                if ($percent < 0 || $percent > 100) {
                    throw new InvalidArgumentException('เปอร์เซ็นต์ส่วนลดต้องอยู่ระหว่าง 0-100');
                }

                $discountAmount = round($meta['price'] * $percent / 100, 2);
                $finalPrice = round($meta['price'] - $discountAmount, 2);

                $serviceEntry['options'][] = [
                    'option_id' => $oid,
                    'duration' => $meta['duration'],
                    'price' => $meta['price'],
                    'discount_percent' => $percent,
                    'discount_amount' => $discountAmount,
                    'final_price' => $finalPrice,
                ];

                if ($percent > $maxPercent) {
                    $maxPercent = $percent;
                }
            }

            if (empty($serviceEntry['options'])) {
                throw new InvalidArgumentException('บริการแต่ละรายการต้องมี option ที่ร่วมโปรโมชั่นอย่างน้อย 1 รายการ');
            }

            $normalized[] = $serviceEntry;
        }

        if (empty($normalized)) {
            throw new InvalidArgumentException('ไม่พบข้อมูลบริการที่ถูกต้อง');
        }

        return [
            'services' => $normalized,
            'max_percent' => $maxPercent,
        ];
    }
}

if (!function_exists('fetchPromotionServicesWithOptions')) {
    function fetchPromotionServicesWithOptions(mysqli $conn, int $promotionId): array
    {
        $sql = "SELECT ps.service_id, s.service_name,
                       pso.option_id, pso.discount_percent, pso.discount_amount, pso.final_price,
                       so.duration, so.price
                FROM promotion_service ps
                INNER JOIN service s ON s.service_id = ps.service_id
                LEFT JOIN promotion_service_option pso
                       ON pso.promotion_id = ps.promotion_id AND pso.service_id = ps.service_id
                LEFT JOIN service_option so ON so.option_id = pso.option_id
                WHERE ps.promotion_id = ?
                ORDER BY s.service_name, so.duration";

        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            return [];
        }
        $stmt->bind_param('i', $promotionId);
        $stmt->execute();
        $result = $stmt->get_result();

        $services = [];
        while ($row = $result->fetch_assoc()) {
            $sid = (int) $row['service_id'];
            if (!isset($services[$sid])) {
                $services[$sid] = [
                    'service_id' => $sid,
                    'service_name' => $row['service_name'],
                    'options' => [],
                ];
            }

            if (!empty($row['option_id'])) {
                $services[$sid]['options'][(int) $row['option_id']] = [
                    'option_id' => (int) $row['option_id'],
                    'discount_percent' => (float) $row['discount_percent'],
                    'discount_amount' => (float) $row['discount_amount'],
                    'final_price' => (float) $row['final_price'],
                    'duration' => isset($row['duration']) ? (int) $row['duration'] : 0,
                    'price' => isset($row['price']) ? (float) $row['price'] : 0.0,
                    'included' => true,
                ];
            }
        }
        $stmt->close();

        if (empty($services)) {
            return [];
        }

        $serviceIds = array_keys($services);
        $placeholders = implode(',', array_fill(0, count($serviceIds), '?'));
        $types = str_repeat('i', count($serviceIds));
        $optStmt = $conn->prepare("SELECT option_id, service_id, duration, price FROM service_option WHERE service_id IN ($placeholders) ORDER BY duration");
        if ($optStmt) {
            $optStmt->bind_param($types, ...$serviceIds);
            $optStmt->execute();
            $optResult = $optStmt->get_result();
            while ($opt = $optResult->fetch_assoc()) {
                $sid = (int) $opt['service_id'];
                if (!isset($services[$sid])) {
                    continue;
                }
                $optionId = (int) $opt['option_id'];
                if (!isset($services[$sid]['options'][$optionId])) {
                    $services[$sid]['options'][$optionId] = [
                        'option_id' => $optionId,
                        'discount_percent' => 0.0,
                        'discount_amount' => 0.0,
                        'final_price' => (float) $opt['price'],
                        'duration' => (int) $opt['duration'],
                        'price' => (float) $opt['price'],
                        'included' => false,
                    ];
                } else {
                    $services[$sid]['options'][$optionId]['duration'] = (int) $opt['duration'];
                    $services[$sid]['options'][$optionId]['price'] = (float) $opt['price'];
                    if (!$services[$sid]['options'][$optionId]['final_price']) {
                        $services[$sid]['options'][$optionId]['final_price'] = (float) $opt['price'];
                    }
                    if (!isset($services[$sid]['options'][$optionId]['included'])) {
                        $services[$sid]['options'][$optionId]['included'] = true;
                    }
                }
            }
            $optStmt->close();
        }

        foreach ($services as &$service) {
            $service['options'] = array_values($service['options']);
        }
        unset($service);

        return array_values($services);
    }
}

if (!function_exists('combineDateAndTime')) {
    function combineDateAndTime(string $date, ?string $time): ?string
    {
        $date = trim($date);
        $time = $time !== null ? trim($time) : '';
        if ($date === '') {
            return null;
        }

        $format = 'Y-m-d';
        $dt = DateTimeImmutable::createFromFormat($format, $date);
        if (!$dt) {
            return null;
        }

        if ($time === '') {
            $time = '00:00';
        }

        $dateTimeString = $dt->format('Y-m-d') . ' ' . $time;
        $combined = parseDateTimeValue($dateTimeString);
        return $combined ? $combined->format('Y-m-d H:i:s') : null;
    }
}

if (!function_exists('getApplicableOptionDiscounts')) {
    function getApplicableOptionDiscounts(mysqli $conn, array $optionIds, string $targetDateTime): array
    {
        if (empty($optionIds)) {
            return [
                'by_option' => [],
                'by_promotion' => [],
                'total_discount' => 0.0,
            ];
        }

        $placeholders = implode(',', array_fill(0, count($optionIds), '?'));
        $types = str_repeat('i', count($optionIds)) . 'ss';

        $sql = "SELECT p.promotion_id, p.pm_name, p.pm_start_date, p.pm_end_date,
                       pso.option_id, pso.discount_percent, pso.discount_amount, pso.final_price,
                       s.service_id, s.service_name,
                       so.duration, so.price
                FROM promotion_service_option pso
                INNER JOIN promotion p ON p.promotion_id = pso.promotion_id
                INNER JOIN service_option so ON so.option_id = pso.option_id
                INNER JOIN service s ON s.service_id = pso.service_id
                WHERE pso.option_id IN ($placeholders)
                  AND IFNULL(pso.is_active, 1) = 1
                  AND p.pm_start_date <= ?
                  AND p.pm_end_date >= ?";

        $params = array_values($optionIds);
        $params[] = $targetDateTime;
        $params[] = $targetDateTime;

        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            return [
                'by_option' => [],
                'by_promotion' => [],
                'total_discount' => 0.0,
            ];
        }

        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();

        $byOption = [];
        while ($row = $result->fetch_assoc()) {
            $optionId = (int) $row['option_id'];
            $percent = (float) $row['discount_percent'];
            $discountAmount = (float) $row['discount_amount'];
            $finalPrice = (float) $row['final_price'];
            $price = (float) $row['price'];

            if (!isset($byOption[$optionId]) || $percent > $byOption[$optionId]['discount_percent']) {
                $byOption[$optionId] = [
                    'promotion_id' => (int) $row['promotion_id'],
                    'promotion_name' => $row['pm_name'],
                    'service_id' => (int) $row['service_id'],
                    'service_name' => $row['service_name'],
                    'duration' => (int) $row['duration'],
                    'price' => $price,
                    'discount_percent' => $percent,
                    'discount_amount' => $discountAmount,
                    'final_price' => $finalPrice ?: max($price - $discountAmount, 0),
                ];
            }
        }
        $stmt->close();

        $byPromotion = [];
        $totalDiscount = 0.0;
        foreach ($byOption as $optionId => $info) {
            $promotionId = $info['promotion_id'];
            if (!isset($byPromotion[$promotionId])) {
                $byPromotion[$promotionId] = [
                    'promotion_id' => $promotionId,
                    'promotion_name' => $info['promotion_name'],
                    'options' => [],
                    'total_discount' => 0.0,
                ];
            }
            $byPromotion[$promotionId]['options'][] = [
                'option_id' => $optionId,
                'service_name' => $info['service_name'],
                'duration' => $info['duration'],
                'price' => $info['price'],
                'discount_percent' => $info['discount_percent'],
                'discount_amount' => $info['discount_amount'],
                'final_price' => $info['final_price'],
            ];
            $byPromotion[$promotionId]['total_discount'] += $info['discount_amount'];
            $totalDiscount += $info['discount_amount'];
        }

        return [
            'by_option' => $byOption,
            'by_promotion' => array_values($byPromotion),
            'total_discount' => $totalDiscount,
        ];
    }
}

if (!function_exists('summarizePromotionDiscountDetail')) {
    function summarizePromotionDiscountDetail(array $promotions): string
    {
        if (empty($promotions)) {
            return '';
        }

        $lines = [];
        foreach ($promotions as $promotion) {
            $lines[] = ($promotion['promotion_name'] ?? 'Promotion') . ':';
            if (!empty($promotion['options'])) {
                foreach ($promotion['options'] as $option) {
                    $label = ($option['service_name'] ?? 'Service') . ' ' . ($option['duration'] ?? '-') . ' นาที';
                    $discount = number_format((float) ($option['discount_amount'] ?? 0), 2);
                    $lines[] = "- {$label} ลด ฿{$discount}";
                }
            }
        }

        return implode("\n", $lines);
    }
}
