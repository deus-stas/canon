<?php

namespace ALS\Helper;

use CFile;


class Photos {
    /**
     * Функция формирует превью фотографий для фотогалереи
     * @param array $item  — Массив элементов для фото которых надо сделать превью
     * @param string $keySrc — Ключ с массивом фотографий
     * @param string $keyDst — Ключ, куда сложить превью
     */
    public static function setThumbs(&$item, $keySrc, $keyDst): void {
        if (count($item[$keySrc])) {
            $item[$keyDst] = [];

            foreach ($item[$keySrc] as $photo) {
                $item[$keyDst][] = self::getThumb($photo['id'], [465, 700], 60);
            }
        }
    }


    /**
     * Функция возвращает массив для превью
     * @param int $id — ID файла
     * @param int[] $size — Размер [ширина, высота]
     * @param int $quality — Качество 0–100
     * @return array
     */
    public static function getThumb(int $id, array $size, int $quality): ?array {
        $thumb = CFile::ResizeImageGet(
            $id,
            [ 'width' => $size[1], 'height' => $size[0] ],
            BX_RESIZE_IMAGE_EXACT,
            true,
            false,
            false,
            $quality
        );

        return File::getImageDataByResize($thumb);
    }

}
