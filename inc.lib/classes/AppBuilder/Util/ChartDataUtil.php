<?php

namespace AppBuilder\Util;

use Exception;
use MagicApp\Field;
use MagicObject\Database\PicoPredicate;
use MagicObject\Database\PicoSort;
use MagicObject\Database\PicoSortable;
use MagicObject\Database\PicoSpecification;
use MagicObject\MagicObject;

/**
 * Utility class for handling chart data operations.
 *
 * This class provides static methods to calculate and update chart data.
 * It is used to aggregate data from a source database entity
 * and save the total count to a target entity for a specific time period.
 */
class ChartDataUtil
{
    /**
     * Updates chart data based on a given source, target, and period.
     * This method counts records from a source object and saves the total
     * to a target object for a specific time period.
     *
     * @param MagicObject $source The source object from which to count the records.
     * @param MagicObject $target The target object where the total count will be saved.
     * @param string $period The period to filter the data, in 'YYYYMM' format.
     * @return bool Returns true on success, false on failure.
     */
    public static function updateChartData($source, $target, $period)
    {
        try
        {
            $timeCreate = substr($period, 0, 4).'-'.substr($period, 4, 2)."-%";
            $specs = PicoSpecification::getInstance()
                ->addAnd(PicoPredicate::getInstance()->like(Field::of()->timeCreate, $timeCreate))
                ;
            $total = $source->countAll($specs);
            $target->setPeriod($period);
            $target->setTotal($total);
            $target->save();
            return true;
        }
        catch(Exception $e)
        {
            return false;
        }
    }
    
    /**
     * Retrieves data from a database object for a specified list of periods.
     * The method queries the database and returns an associative array
     * with the period as the key and the total count as the value. If no
     * data is found for a period, its value is set to 0.
     *
     * @param MagicObject $object The database object to query.
     * @param string[] $periods An array of periods in 'YYYYMM' format.
     * @return array An associative array with periods as keys and total counts as values.
     * Returns an array with all values set to 0 on a database failure.
     */
    public static function getData($object, $periods)
    {
        $specs = PicoSpecification::getInstance()
            ->addAnd(PicoPredicate::getInstance()->in(Field::of()->period, $periods));
        $sorts = PicoSortable::getInstance()
            ->addSortable([Field::of()->period, PicoSort::ORDER_TYPE_ASC]);
        try
        {
            $result = [];
            $pagedata = $object->findAll($specs, null, $sorts);
            foreach($pagedata->getResult() as $data)
            {
                $result[$data->getPeriod()] = $data->getTotal();
            }
            $finalResult = [];
            foreach($periods as $period)
            {
                $finalResult[$period] = isset($result[$period]) ? (int) $result[$period] : 0;
            }
            return $finalResult;
        }
        catch(Exception $e)
        {
            $finalResult = [];
            foreach($periods as $period)
            {
                $finalResult[$period] = 0;
            }
            return $finalResult;
        }
    }
    
    /**
     * Generates an array of the last specified number of months.
     * The months are returned in 'YYYYMM' format and in ascending order.
     *
     * @param int $lastMonth The number of months to retrieve, counting from the current month.
     * @return string[] An array of months in 'YYYYMM' format.
     */
    public static function getLastMonth($lastMonth)
    {
        $months = [];
        for ($i = -($lastMonth - 1); $i <= 0; $i++) {
            $month = date("Ym", strtotime("$i months"));
            $months[] = $month;
        }
        return $months;
    }
}