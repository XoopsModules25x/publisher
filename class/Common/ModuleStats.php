<?php

declare(strict_types=1);

namespace XoopsModules\Publisher\Common;

/**
 * Created by PhpStorm.
 * User: mamba
 * Date: 2015-07-06
 * Time: 11:27
 */
trait ModuleStats
{
    /**
     * @param \XoopsModules\Publisher\Common\Configurator $configurator
     * @return array
     */

    public static function getModuleStats($configurator)
    {
        $moduleStats = [];
        if (\count($configurator->moduleStats) > 0) {
            foreach (\array_keys($configurator->moduleStats) as $i) {
                $moduleStats[$i] = $configurator->moduleStats[$i];
            }
        }

        return $moduleStats;
    }
}
