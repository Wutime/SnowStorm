<?php

namespace Wutime\SnowStorm\Option;

class Validator
{
    public static function validateScheduleDate(&$value, \XF\Entity\Option $option, $optionId)
    {
        $value = trim((string)$value);

        $input = \XF::app()->inputFilterer()->filterArray($_POST['options'] ?? [], [
            'wutime_snowstorm_schedule_enable' => 'bool',
            'wutime_snowstorm_schedule_start' => 'str',
            'wutime_snowstorm_schedule_end' => 'str'
        ]);

        $scheduleEnabled = (bool)$input['wutime_snowstorm_schedule_enable'];
        $startRaw = trim((string)$input['wutime_snowstorm_schedule_start']);
        $endRaw = trim((string)$input['wutime_snowstorm_schedule_end']);

        if (!$scheduleEnabled) {
            return true;
        }

        if ($startRaw === '') {
            $option->error(\XF::phrase('wutime_snowstorm_schedule_start_required'), 'wutime_snowstorm_schedule_start');
            return false;
        }

        if ($endRaw === '') {
            $option->error(\XF::phrase('wutime_snowstorm_schedule_end_required'), 'wutime_snowstorm_schedule_end');
            return false;
        }

        $startKey = self::parseMonthDayKey($startRaw);
        $endKey = self::parseMonthDayKey($endRaw);

        if ($startKey === null) {
            $option->error(\XF::phrase('wutime_snowstorm_schedule_start_invalid'), 'wutime_snowstorm_schedule_start');
            return false;
        }

        if ($endKey === null) {
            $option->error(\XF::phrase('wutime_snowstorm_schedule_end_invalid'), 'wutime_snowstorm_schedule_end');
            return false;
        }

        if ($startKey === $endKey) {
            $option->error(\XF::phrase('wutime_snowstorm_schedule_end_after_start'), 'wutime_snowstorm_schedule_end');
            return false;
        }

        return true;
    }

    protected static function parseMonthDayKey(string $raw): ?int
    {
        $raw = trim($raw);
        if ($raw === '') {
            return null;
        }

        if (preg_match('/^(\d{1,2})[\/\-](\d{1,2})(?:[\/\-]\d{2,4})?$/', $raw, $matches)) {
            $month = (int)$matches[1];
            $day = (int)$matches[2];
            if (!checkdate($month, $day, 2000)) {
                return null;
            }
            return ($month * 100) + $day;
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
