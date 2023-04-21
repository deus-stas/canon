<?php

namespace ALS\Helper;

use COption;

class ImageCrop {

    /** @var float|int Время создания объекта в микросекнудах */
    public $iMicroTimeInit = 0;

    /** @var array Исходные параметры объекта */
    public $arParams = [];

    /** @var array Массив с историей объекта */
    public $arHistory = [];

    /** @var bool Ресурс изображения с обрезанными краями */
    public $imageTrim = false;

    /** @var bool Ресурс изображения в квадрате */
    public $imageOnSquare = false;

    /** @var bool Параметры изображения в квадрате */
    public $arImageOnSquare = false;

    /** @var string Тип изображения */
    private $imageType;


    // =========================================================================
    // === Служебные методы для работы с объектом ==============================
    // =========================================================================

    /**
     * Конструктор, устанавливает переданные параметры объекта, если есть
     * @param array $arParams Опции нового объекта
     */
    public function __construct($arParams = []) {
        // Установим время создания объекта
        $this->iMicroTimeInit = getmicrotime();


        // Установим ID элемента
        $this->arParams['ID'] = randString();


        // Установим качество картинок по умолчанию из настроек модуля
        $this->arParams['QUALITY'] = COption::GetOptionString('main', 'image_resize_quality');


        // Размеры квадратного изображения
        $this->arParams['SQUARE_HEIGHT'] = 600;
        $this->arParams['SQUARE_WIDTH'] = 600;


        // Установим опции от создателя объекта
        $this->setParams($arParams);


        // Сохраним запись в истории
        $this->arHistory[] = [
            'TEXT' => 'Создан объект',
            'TIME' => $this->getTimeDiff(),
        ];
    }


    /**
     * Метод устанавливает опции, сохраняя старые
     * @param array $arParams Опции объекта
     */
    public function setParams($arParams) {
        if ($arParams['IMG']) {                                                    // Если среди опций есть путь к картинке
            $arParams['IMG'] = str_replace('//', '/', $arParams['IMG']);        // то исключим из него двойные слеши
        }

        $this->arParams = array_merge(                                            // Сохраним новые параметры
            $this->arParams,                                                    //	объединив их состарыми
            $arParams
        );
    }


    /**
     * Функция вычисляет сколько времени прошло с момента создания объекта до её вызова
     * @return int
     */
    public function getTimeDiff() {
        $iTimeDiff = round(getmicrotime() - $this->iMicroTimeInit, 4);

        return $iTimeDiff;
    }


    // =========================================================================
    // === Методы обработки изображения ========================================
    // =========================================================================

    /**
     * Метод готовит ресурс изображения для
     * @return boolean
     */
    public function trimWhitespace() {
        $sImageSrc = $this->arParams['IMG'];


        // Проверка исходного изображения
        if (!file_exists($sImageSrc)) {
            return false;

        }


        // Определим, jpg ли это
        $sMime = mime_content_type($sImageSrc);
        if ($sMime === 'image/jpeg') {
            $this->imageType = 'jpg';
            $img = imagecreatefromjpeg($sImageSrc);

        } elseif ($sMime === 'image/png') {
            $this->imageType = 'jpg';
            $image = imagecreatefrompng($sImageSrc);
            $bg = imagecreatetruecolor(imagesx($image), imagesy($image));
            imagefill($bg, 0, 0, imagecolorallocate($bg, 255, 255, 255));
            imagealphablending($bg, true);
            imagecopy($bg, $image, 0, 0, 0, 0, imagesx($image), imagesy($image));
            imagedestroy($image);

            $img = $bg;

        } else {
            return false;
        }


        // Обрезка белых краев
        // За основу взят алгоритм с
        // http://stackoverflow.com/questions/1669683/crop-whitespace-from-image-in-php

        // Найдем размеры границ
        $b_top = 0;
        $b_btm = 0;
        $b_lft = 0;
        $b_rt = 0;

        // Сверху
        for (; $b_top < imagesy($img); ++$b_top) {
            for ($x = 0; $x < imagesx($img); ++$x) {
                if ($this->imageType === 'jpg'
                    && imagecolorat($img, $x, $b_top) != 0xFFFFFF) {

                    break 2; // Перестать искать границу сверху

                } elseif ($this->imageType === 'png'
                    && imagecolorat($img, $x, $b_top) != 0x7FFFFFFF) {

                    break 2; // Перестать искать границу сверху
                }
            }
        }

        // Снизу
        for (; $b_btm < imagesy($img); ++$b_btm) {
            for ($x = 0; $x < imagesx($img); ++$x) {
                if ($this->imageType === 'jpg'
                    && imagecolorat($img, $x, imagesy($img) - $b_btm - 1) != 0xFFFFFF) {

                    break 2; // Перестать искать границу снизу

                } elseif ($this->imageType === 'png'
                    && imagecolorat($img, $x, imagesy($img) - $b_btm - 1) != 0x7FFFFFFF) {

                    break 2; // Перестать искать границу снизу
                }
            }
        }

        // Слева
        for (; $b_lft < imagesx($img); ++$b_lft) {
            for ($y = 0; $y < imagesy($img); ++$y) {
                if ($this->imageType === 'jpg'
                    && imagecolorat($img, $b_lft, $y) != 0xFFFFFF) {

                    break 2; // Перестать искать границу слева

                } elseif ($this->imageType === 'png'
                    && imagecolorat($img, $b_lft, $y) != 0x7FFFFFFF) {

                    break 2; // Перестать искать границу слева
                }
            }
        }

        // Справа
        for (; $b_rt < imagesx($img); ++$b_rt) {
            for ($y = 0; $y < imagesy($img); ++$y) {
                if ($this->imageType === 'jpg'
                    && imagecolorat($img, imagesx($img) - $b_rt - 1, $y) != 0xFFFFFF) {

                    break 2; // Перестать искать границу справа

                } elseif ($this->imageType === 'png'
                    && imagecolorat($img, imagesx($img) - $b_rt - 1, $y) != 0x7FFFFFFF) {

                    break 2; // Перестать искать границу справа
                }
            }
        }

        // Скопируем целевой контент избегая белых областей
        $newimg = imagecreatetruecolor(
            imagesx($img) - ($b_lft + $b_rt),
            imagesy($img) - ($b_top + $b_btm)
        );

        imagecopy($newimg, $img, 0, 0, $b_lft, $b_top, imagesx($newimg), imagesy($newimg));


        // Сохраним ресурс картинки в параметре imageTrim объекта
        $this->imageTrim = $newimg;


        // Сохраним запись в истории
        $this->arHistory[] = [
            'FUNC' => 'trimWhitespace',
            'TEXT' => "У изображения [$sImageSrc] срезаны белые области по краям [$b_top, $b_rt, $b_btm, $b_lft]",
            'TIME' => $this->getTimeDiff(),
        ];


        return true;
    }


    /**
     * Метод размещает изображение imageResource на квадратном холсте
     */
    public function setOnSquare() {
        if ($this->imageTrim) {
            $imageResource = $this->imageTrim;

        } else {
            return false;

        }


        // Определим размеры изображения
        $iImageHeight = imagesy($imageResource);
        $iImageWidth = imagesx($imageResource);


        // Сделаем заготовку нового изображения
        $imageOnSquare = imagecreatetruecolor(
            $this->arParams['SQUARE_WIDTH'],
            $this->arParams['SQUARE_HEIGHT']
        );
        $imageColorWhite = imagecolorallocate($imageOnSquare, 255, 255, 255);
        imagefill($imageOnSquare, 0, 0, $imageColorWhite);


        // Рассчитаем размер вставляемого изображения
        $iPercentDown = 1;
        if ($iImageWidth > $iImageHeight) {
            $iPercentDown = $iImageWidth / $this->arParams['SQUARE_WIDTH'];

            $iImageOnSquareHeight = round($iImageHeight / $iPercentDown);
            $iImageOnSquareWidth = round($iImageWidth / $iPercentDown);

        } else {
            $iPercentDown = $iImageHeight / $this->arParams['SQUARE_HEIGHT'];

            $iImageOnSquareHeight = round($iImageHeight / $iPercentDown);
            $iImageOnSquareWidth = round($iImageWidth / $iPercentDown);
        }


        // Создадим квадратное изображение, вставив оригинал
        if ($iImageWidth > $iImageHeight) {                                     // Если изображение горизонтальное
            // Определим вертикальный отступ
            $iOffssetY = round(($this->arParams['SQUARE_HEIGHT'] - $iImageOnSquareHeight));

            imagecopyresampled(
                $imageOnSquare,                                                 // Ресурс целевого изображения
                $imageResource,                                                 // Ресурс исходного изображения
                0,                                                              // x-координата результирующего изображения
                $iOffssetY,                                                     // y-координата результирующего изображения
                0,                                                              // x-координата исходного изображения
                0,                                                              // y-координата исходного изображения
                $iImageOnSquareWidth,                                           // Результирующая ширина
                $iImageOnSquareHeight,                                          // Результирующая высота
                $iImageWidth,                                                   // Ширина исходного изображения
                $iImageHeight                                                   // Высота исходного изображения
            );

        } else {                                                                // Если изображение вертикальное или квадратное
            // Определим горизонтальный отступ
            $iOffssetX = round(($this->arParams['SQUARE_WIDTH'] - $iImageOnSquareWidth) / 2);

            imagecopyresampled(
                $imageOnSquare,                                                 // Ресурс целевого изображения
                $imageResource,                                                 // Ресурс исходного изображения
                $iOffssetX,                                                     // x-координата результирующего изображения
                0,                                                              // y-координата результирующего изображения
                0,                                                              // x-координата исходного изображения
                0,                                                              // y-координата исходного изображения
                $iImageOnSquareWidth,                                           // Результирующая ширина
                $iImageOnSquareHeight,                                          // Результирующая высота
                $iImageWidth,                                                   // Ширина исходного изображения
                $iImageHeight                                                   // Высота исходного изображения
            );

        }


        $this->imageOnSquare = $imageOnSquare;
        imagejpeg($imageOnSquare, $_SERVER['DOCUMENT_ROOT'] . 'upload/trimWhitespace.jpg', 95);


        // Сохраним запись в истории
        $this->arHistory[] = [
            'FUNC' => 'setOnSquare',
            'TEXT' => 'Изображение разместили на холсте',
            'TIME' => $this->getTimeDiff(),
        ];
    }


    /**
     * Метод сохраняет изображение с обрезанными краями
     */
    public function saveImageTrim() {
        $result = null;

        if ($this->imageTrim) {
            $sFileName = md5($this->arParams['ID']);
            $sFilePath = 'upload/tmp/' . $sFileName . '.' . $this->imageType;
            $sFileRoot = $_SERVER['DOCUMENT_ROOT'] . $sFilePath;

            if ($this->imageType === 'jpg') {
                imagejpeg($this->imageTrim, $sFileRoot, $this->arParams['QUALITY']);

            } elseif ($this->imageType === 'png') {
                $iQuality = $this->arParams['QUALITY_PNG'] ?: 9;
                imagepng($this->imageTrim, $sFileRoot, $iQuality);

            }

            $result = $sFilePath;

        } else {
            $result = false;

        }

        return $result;

    }


    /**
     * Функция создает изображение в структуре сайта
     */
    public function saveSquare() {
        if ($this->imageOnSquare) {
            $sFileName = md5($this->arParams['ID']);
            $sFilePath = 'upload/tmp/' . $sFileName . '.jpg';
            $sFileRoot = $_SERVER['DOCUMENT_ROOT'] . $sFilePath;

            imagejpeg($this->imageOnSquare, $sFileRoot, $this->arParams['QUALITY']);

            $this->arImageOnSquare = [
                'FILE_NAME' => $sFileName,
                'FILE_PATH' => $sFilePath,
                'FILE_ROOT' => $sFileRoot,
            ];

        } else {
            return false;

        }

        // Сохраним запись в истории
        $this->arHistory[] = [
            'FUNC' => 'saveSquare',
            'TEXT' => 'Изображение сохранили в структуре сайта',
            'TIME' => $this->getTimeDiff(),
        ];

    }


    public function goToSquare() {
        $this->trimWhitespace();
        $this->setOnSquare();
        $this->saveSquare();
    }

}
