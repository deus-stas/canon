<?php

namespace ALS\Helper;



/**
 * Статический класс для работы с медиа библиотекой
 */
class Medialibrary
{
    private static function MedialibraryPrepareTree(array $branch): array{
        $tabs=[];
        foreach ($branch["children"] as $tab){
            $el=[];
            $el['name']=$tab['NAME'];
            foreach ($tab['children'] as $item){
                $itm=[];
                $itm['name']=$item['NAME'];

                $photos = \CMedialibItem::GetList(['arCollections' => [$item["ID"]]]);
                foreach ($photos as $photo){
                    $itm['photos'][]=[
                        'name'=>$photo['DESCRIPTION']?:$item["DESCRIPTION"],// todo
                        'path'=>$photo['PATH'],
                        'w'=>$photo['WIDTH'],
                        'h'=>$photo['HEIGHT'],
                        'thumb'=>$photo["THUMB_PATH"],
                        'videoUrl'=>$photo["KEYWORDS"],
                    ];
                }
                $el['items'][]=$itm;
            }
            $tabs[]=$el;
        }
        return $tabs;
    }
    private static function MedialibraryGetCHildCollections(string $id): array{
        $arCollections = \CMedialibCollection::GetList(
            ['arFilter' => ['ACTIVE' => 'Y','PARENT_ID'=>$id]]
        );
        foreach ($arCollections as &$arCollection){
            $arCollection['children']=self::MedialibraryGetCHildCollections($arCollection["ID"]);
        }
        return $arCollections;
    }
    private static function MedialibraryBuildTree(string $name): array{

        $arCollections = \CMedialibCollection::GetList(
            ['arFilter' => ['ACTIVE' => 'Y','NAME'=>$name]]
        );
        $branch=null;
        foreach ($arCollections as $arCollection){
            if($arCollection['NAME']===$name){
                $branch = $arCollection;
            }
        }
        if(!$branch){
            return [];
        }
        $branch['children']=self::MedialibraryGetCHildCollections($branch["ID"]);


        return $branch;
    }
    public static function getMedialibraryItems(string $name): array{
        \CModule::IncludeModule("fileman");
        \CMedialib::Init();


        $tree=self::MedialibraryBuildTree($name);

        return self::MedialibraryPrepareTree($tree);

    }
}
