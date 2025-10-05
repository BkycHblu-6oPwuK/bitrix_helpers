<?php

namespace App\Main;

class MenuHelper
{
    /**
     * Переводит разделы из DFS порядка в древовидную структуру.
     *
     * @param array $sections разделы полученные из компонента catalog.section.list или menu
     *
     * @return array [firstLvlSections => [
     *      child => [secondLvlSections => [
     *          child => thirdLvlSections ...
     *      ]]
     * ]
     */
    public static function convertDfsToTree(array $sections): array
    {
        $tree = [];
        $prevLvls = [];

        foreach ($sections as &$section) {
            $section['child'] = [];

            $depthLvl = $section['RELATIVE_DEPTH_LEVEL'] ?? $section['DEPTH_LEVEL'];

            if ($depthLvl == 1) {
                $tree[] = &$section;
            } else {
                $prevLvls[$depthLvl - 1]['child'][] = &$section;
            }

            $prevLvls[$depthLvl] = &$section;
        }

        return $tree;
    }
}
