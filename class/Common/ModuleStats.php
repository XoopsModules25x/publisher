<?php

namespace XoopsModules\Publisher\Common;

/**
 * Created by PhpStorm.
 * User: mamba
 * Date: 2015-07-06
 * Time: 11:27
 */

trait ModuleStats
{
    private $db;

    /**
     * @return array
     */
    public static function getModuleStats($configurator, $moduleStats)
    {
        if (count($configurator->moduleStats) > 0) {
            foreach (array_keys($configurator->moduleStats) as $i) {
                $moduleStats[$i] = $configurator->moduleStats[$i];
            }
        }

        //print_r($moduleStats);
        return $moduleStats;
    }
}
