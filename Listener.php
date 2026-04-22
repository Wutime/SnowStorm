<?php

namespace Wutime\SnowStorm;

use Detection\MobileDetect;
use XF\Container;
use XF\Template\Templater;

class Listener
{
    public static function templater_setup(Container $container, Templater &$templater)
    {
        $templater->addFunction('wutime_snowstorm_is_mobile', function (Templater $templater, &$escape)
        {
            static $isMobile = null;

            if ($isMobile !== null) {
                return $isMobile;
            }

            try {
                if (class_exists(MobileDetect::class)) {
                    $detect = new MobileDetect();
                    $isMobile = $detect->isMobile();
                    return $isMobile;
                }
            } catch (\Throwable $e) {
                // Fallback below handles detection if MobileDetect is unavailable.
            }

            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
            $isMobile = (bool)preg_match('/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i', $userAgent);

            return $isMobile;
        });

        $templater->addFunction('wutime_snowstorm_is_in_active_window', function (Templater $templater, &$escape, $start = '', $end = '')
        {
            return self::isInActiveWindow((string)$start, (string)$end);
        });
    }

    protected static function isInActiveWindow(string $start, string $end): bool
    {
        $startKey = self::parseMonthDayToKey($start);
        $endKey = self::parseMonthDayToKey($end);

        if ($startKey === null || $endKey === null) {
            return true;
        }

        $now = new \DateTimeImmutable('@' . \XF::$time);
        $now = $now->setTimezone(new \DateTimeZone(date_default_timezone_get()));
        $currentKey = (int)$now->format('md');

        if ($startKey <= $endKey) {
            return $currentKey >= $startKey && $currentKey <= $endKey;
        }

        return $currentKey >= $startKey || $currentKey <= $endKey;
    }

    protected static function parseMonthDayToKey(string $raw): ?int
    {
        $raw = trim($raw);
        if ($raw === '') {
            return null;
        }

        if (preg_match('/^(\d{1,2})[\/\-](\d{1,2})(?:[\/\-]\d{2,4})?$/', $raw, $matches)) {
            $month = (int)$matches[1];
            $day = (int)$matches[2];
            if (checkdate($month, $day, 2000)) {
                return ($month * 100) + $day;
            }
            return null;
        }

        if (preg_match('/^([a-z]{3,9})\.?\s+(\d{1,2})(?:,?\s*\d{2,4})?$/i', $raw, $matches)) {
            $monthParsed = strtotime($matches[1] . ' 1 2000');
            if ($monthParsed === false) {
                return null;
            }

            $month = (int)date('n', $monthParsed);
            $day = (int)$matches[2];
            if (!checkdate($month, $day, 2000)) {
                return null;
            }

            return ($month * 100) + $day;
        }

        if (preg_match('/^(\d{1,2})\s+([a-z]{3,9})\.?(?:,?\s*\d{2,4})?$/i', $raw, $matches)) {
            $monthParsed = strtotime($matches[2] . ' 1 2000');
            if ($monthParsed === false) {
                return null;
            }

            $month = (int)date('n', $monthParsed);
            $day = (int)$matches[1];
            if (!checkdate($month, $day, 2000)) {
                return null;
            }

            return ($month * 100) + $day;
        }

        return null;
    }
}
