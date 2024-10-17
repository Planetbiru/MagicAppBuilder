<?php

namespace MagicApp\Utility;

/**
 * Class PeriodUtil
 *
 * Utility class for handling period calculations based on a specific format.
 * This class provides methods to calculate the next and previous periods 
 * given a current period in 'YYYYMM' format, allowing for easy date manipulation 
 * in applications that require monthly period handling.
 */
class PeriodUtil
{
    /**
     * Get the next period by adding a specified number of months.
     *
     * This method takes the current period as a string in 'YYYYMM' format
     * and adds a specified number of months to it, returning the resulting 
     * period also in 'YYYYMM' format. It handles year rollover when 
     * necessary.
     *
     * @param string $currentPeriod The current period in 'YYYYMM' format.
     * @param int $n The number of months to add.
     * @return string The next period in 'YYYYMM' format.
     */
    public static function nextPeriod($currentPeriod, $n) {
        // Convert to year and month
        $year = (int)substr($currentPeriod, 0, 4);
        $month = (int)substr($currentPeriod, 4, 2);

        // Calculate new month and year
        $totalMonths = ($year * 12 + $month + $n);
        $newYear = (int)($totalMonths / 12);
        $newMonth = $totalMonths % 12;

        // Adjust month to be 1-12
        if ($newMonth === 0) {
            $newYear -= 1;
            $newMonth = 12;
        }

        // Format as 'YYYYMM'
        return sprintf('%04d%02d', $newYear, $newMonth);
    }

    /**
     * Get the previous period by subtracting a specified number of months.
     *
     * This method takes the current period as a string in 'YYYYMM' format
     * and subtracts a specified number of months from it, returning the 
     * resulting period also in 'YYYYMM' format. It handles year rollover 
     * when subtracting, ensuring valid output.
     *
     * @param string $currentPeriod The current period in 'YYYYMM' format.
     * @param int $n The number of months to subtract.
     * @return string The previous period in 'YYYYMM' format.
     */
    public static function previousPeriod($currentPeriod, $n) {
        // Convert to year and month
        $year = (int)substr($currentPeriod, 0, 4);
        $month = (int)substr($currentPeriod, 4, 2);

        // Calculate new month and year
        $totalMonths = ($year * 12 + $month - $n);
        $newYear = (int)($totalMonths / 12);
        $newMonth = $totalMonths % 12;

        // Adjust month to be 1-12
        if ($newMonth <= 0) {
            $newYear -= 1;
            $newMonth += 12;
        }

        // Format as 'YYYYMM'
        return sprintf('%04d%02d', $newYear, $newMonth);
    }
}
