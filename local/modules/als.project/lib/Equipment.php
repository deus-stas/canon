<?php

namespace ALS\Project;

use ALS\Helper\Hel;

class Equipment
{
    public static function getEquipmentForForm()
    {
        $hl_Equipment = 'Equipment';
        if(LANGUAGE_CODE == 'en'){
            $hl_Equipment = 'EquipmentEN';
        }
        $hParams=[
            'filter'=>[],
            'select'=>[
                'UF_NAME',
                'UF_CATEGORY',
            ]
        ];
        $data=Hel::getList($hl_Equipment,$hParams);
        $result = [];
        $id_cat = [];
        $arrCategory = [];
        // Сбор Id-шников для одного запроса
        foreach ($data as $item){
            $id_cat[] = $item['UF_CATEGORY'];
        }

        // Получаем из ID, наименование разделов
        $rsCategory = \CUserFieldEnum::GetList(array(), array(
            "ID" => $id_cat,
        ));
        while($arGender = $rsCategory->GetNext()){
            $arrCategory[$arGender["ID"]] = $arGender["VALUE"];
        }

        foreach ($data as $item){
            $result[] = array(
                'name' => $item['UF_NAME'],
                'category' => $arrCategory[$item['UF_CATEGORY']]
            );
        }
        return $result;
    }
}
