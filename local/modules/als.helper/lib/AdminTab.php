<?php

namespace ALS\Helper;

class AdminTab {

    /**
     * Убирает таб Реклама в элементах инфоблока
     * @param $tabControl
     */
    public static function removeSeoAdvTab(&$tabControl) {
        if ($GLOBALS['APPLICATION']->GetCurPage() === '/bitrix/admin/iblock_element_edit.php') {
            $tabs = [];

            foreach ($tabControl->tabs as $k => $tab) {
                if ($tab['DIV'] !== 'seo_adv_seo_adv') {
                    $tabs[] = $tab;
                }
            }

            $tabControl->tabs = $tabs;
        }
    }

}
