<?php

namespace ALS\Helper;


use Bitrix\Main\Data\Cache;
use CPHPCache;

class CacheManager {
    private const DIR = '/als.project';


    /**
     * Метод возвращает список инфоблоков с использованием CacheManager проекта
     * @param $params - Параметры выборки инфоблоков
     * @return array
     */
    public static function getIblocksFromCache($params): array {
        $obCache = new CPHPCache;
        $cachePath = '/iblocks/';

        if ($obCache->InitCache(3600, serialize($params), $cachePath)) {
            $vars = $obCache->GetVars();
            $items = $vars['RESULT'];

        } else {
            $items = Iblock::getList($params);

            if ($obCache->StartDataCache()) {
                $obCache->EndDataCache(['RESULT' => $items]);
            }
        }

        return $items;
    }


    /**
     * Метод возвращает список элементов инфоблока с использованием CacheManager проекта
     * @param array $params - Параметры выборки элементов
     *              $params[__SKIP_CACHE] - Прямая выборка без кеша
     * @param null $callback
     * @param int $ttl
     * @return array
     */
    public static function getIblockItemsFromCache($params, $callback = null, $ttl = 3600): array {
        if ($params['__SKIP_CACHE']) {
            $items = El::getList($params);

            if (is_callable($callback)) {
                $callback($items);
            }

            return $items;
        }

        $obCache = new CPHPCache;
        $iblockCacheDir = $params['IBLOCK_CODE'] ?: (string)$params['IBLOCK_ID'];
        $cachePath = '/' . $iblockCacheDir . '/items/';

        if ($obCache->InitCache($ttl, serialize($params), $cachePath)) {
            $vars = $obCache->GetVars();
            $items = $vars['RESULT'];

        } else {
            $items = El::getList($params);

            if (is_callable($callback)) {
                $callback($items);
            }

            if ($obCache->StartDataCache()) {
                $obCache->EndDataCache(['RESULT' => $items]);
            }
        }

        return $items;
    }


    /**
     * Метод возвращает список разделов инфоблока с использованием CacheManager проекта
     * @param $params - Параметры выборки разделов
     * @param callable $callback
     * @return array
     */
    public static function getIblockSectionsFromCache($params, $callback = null): array {
        if ($params['__SKIP_CACHE']) {
            $items = Sect::getList($params);

            if (is_callable($callback)) {
                $callback($items);
            }

            return $items;
        }

        $obCache = new CPHPCache;
        $iblockCacheDir = $params['IBLOCK_CODE'] ?: (string)$params['IBLOCK_ID'];
        $cachePath = '/' . $iblockCacheDir . '/sections/';

        if ($obCache->InitCache(3600, serialize($params), $cachePath)) {
            $vars = $obCache->GetVars();
            $items = $vars['RESULT'];

        } else {
            $items = Sect::getList($params);

            if (is_callable($callback)) {
                $callback($items);
            }

            if ($obCache->StartDataCache()) {
                $obCache->EndDataCache(['RESULT' => $items]);
            }
        }

        return $items;
    }


    /**
     * Метод возвращает значения свойства список инфоблока. Кеш-обертка над El::getPropEnumDict()
     * @param array $params - Параметры выборки записей
     *              $params[__SKIP_CACHE] - Прямая выборка без кеша
     * @param callable $callback
     * @return array
     */
    public static function getIblockPropEnumDict($params, $callback = null): array {
        if ($params['__SKIP_CACHE']) {
            $items = El::getPropEnumDict($params);

            if (is_callable($callback)) {
                $callback($items);
            }

            return $items;
        }

        $obCache = new CPHPCache;
        $cachePath = '/' . $params['IBLOCK_CODE'] . '/iblock-prop-enum-dict/';

        if ($obCache->InitCache(3600, serialize($params), $cachePath)) {
            $vars = $obCache->GetVars();
            $items = $vars['RESULT'];

        } else {
            $items = El::getPropEnumDict($params);

            if (is_callable($callback)) {
                $callback($items);
            }

            if ($obCache->StartDataCache()) {
                $obCache->EndDataCache(['RESULT' => $items]);
            }
        }

        return $items;
    }


    /**
     * Метод очищает кеш по ключу
     * @param int|string $key
     */
    public static function clear($key = ''): void {
        $dirs = [
            '/' . $key . '/items/',
            '/' . $key . '/sections/',
        ];

        foreach ($dirs as $dir) {
            if ($dir) {
                $cache = Cache::createInstance();
                $cache->cleanDir($dir);
            }
        }
    }


    /**
     * По событию метод сбрасывает кеши соответствующего инфоблока
     * @param array $event
     */
    public static function processingEvent($event): void {
        $iblockId = (int)$event['IBLOCK_ID'];
        $iblockCode = Help::getIblockCode($iblockId);

        self::clear($iblockId);
        self::clear($iblockCode);
    }

    /**
     * Закешировать данные через колбек
     *
     * Пока что вызывается так:
     * \ALS\Helper\Cache::cached([
     * 	'ttl'     => 60,
     * 	'hash'    => $arParams, // по умолчанию []
     * 	'dir'     => '/iblock/section/list/',
     * 	'refresh' => $arParamArg['cache']['refresh'],
     * 	'func'    => function() {
     * 		return 'cached string';
     * 	}
     * ]);
     *
     * @param array $param Параметры в виде [ttl, id, hash, dir, refresh, func]
     * @return mixed         Данные, которые могли быть закешированы
     */
    public static function cached(array $param = []) {
        $param['dir'] = $param['dir'] ?? '/';

        $hash = sha1(serialize(($param['hash'] ?: [])));
        $cache = Cache::createInstance();

        if ($param['refresh']) {
            $cache->forceRewriting('Y');
        }

        $result = null;
        if ($cache->initCache($param['ttl'], $param['dir'] . $hash, $param['dir'])) {
            $result = $cache->getVars();
        } elseif ($cache->startDataCache()) {
            $result = $param['func']();

            $cache->endDataCache($result);
        }

        return $result;
    }

}
