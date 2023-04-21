<?php

namespace ALS\Helper;

use Bitrix\Main\Application as BxApp;
use Bitrix\Main\Db\SqlQueryException;
use CFile;
use CUtil;


class File {


    /**
     * Удаляет информацию о файле
     *
     * @param int $id
     * @return bool
     */
    public static function deleteFile(int $id): bool {
        CFile::Delete($id);

        return true;

        return self::getData($id) === false;
    }


    /**
     * Функция округляет размер файла в байтах и переводит в Кб, Мб, Гб и т.д.
     *
     * @param int $size - Размер файла в байтах
     * @param int $precision - Порядок округления. По умолчанию 2
     * @return string
     */
    public static function formatSize($size, $precision = 2) {
        $bitrixFormat = CFile::FormatSize($size, $precision);

        if (LANGUAGE_ID === 'ru') {
            $bitrixFormat = str_replace('.', ',', $bitrixFormat);
        }

        if (LANGUAGE_ID === 'en') {
            $bitrixFormat = strtoupper($bitrixFormat);
        }

        $bitrixFormat = str_replace(' ', '&nbsp;', $bitrixFormat);

        return $bitrixFormat;
    }


    /**
     * Функция возвращает массив на основе CSV файла
     *
     * @param string $path Путь к файлу
     * @param array $params Дополнительные параметры <br>
     *    <li> ENC_IN - исходная кодировка
     *    <li> ENC_TO - требуемая кодировка
     * @return array Ассоциативный массив
     */
    public static function getArrFromCSV($path, array $params = []) {
        $array = $fields = [];
        $i = 0;
        $handle = @fopen($path, 'rb');


        if ($handle) {
            while (($row = fgetcsv($handle, 4096)) !== false) {
                if (empty($fields)) {
                    $fields = $row;
                    continue;
                }

                foreach ($row as $k => $value) {
                    if ($params['ENC_IN'] && $params['ENC_TO']) {
                        $value = iconv($params['ENC_IN'], $params['ENC_TO'], $value);
                    }

                    $array[$i][$k] = trim($value);
                }

                $i++;
            }

            fclose($handle);
        }


        // Преобразование полученного массива к ассоциативному
        $result = [];

        if (is_array($fields) && is_array($array)) {
            $fields = explode(';', $fields[0]);

            foreach ($array as $k => $row) {
                $row = explode(';', $row[0]);

                foreach ($row as $n => $column) {
                    $assocKey = $fields[$n];

                    if ($assocKey) {
                        $result[$k][$fields[$n]] = $column;
                    }
                }
            }
        }


        return $result;
    }


    /**
     * Функция возвращает Base64 для файла (пока только с картинками работает)
     *
     * @param array $param Массив с параметрами <br>
     * <li> PATH - Путь к файлу относительно корня сайта
     * <li> SOURCE - Исходник файла
     * <li> TYPE - Тип данных, например «svg»
     * @return string Строка с закодированным в base64 файлом
     */
    public static function getBase64($param) {
        $base64 = '';

        if ($param['PATH']) {
            $path = $_SERVER['DOCUMENT_ROOT'] . $param['PATH'];
            $type = pathinfo($path, PATHINFO_EXTENSION);
            $data = file_get_contents($path);

            if ($type === 'svg') {
                $type = 'svg+xml';
            }

            $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);

        } elseif ($param['SOURCE'] && $param['TYPE']) {
            if ($param['TYPE'] === 'svg') {
                $param['TYPE'] = 'svg+xml';
            }

            $base64 = 'data:image/' . $param['TYPE'] . ';base64,' . base64_encode($param['SOURCE']);

        }

        return $base64;
    }


    public static function base64ToImage($base64) {
        $data = explode(',', $base64);

        $extMatches = [];
        preg_match('/data:.+\/(\w+);/mu', $data[0], $extMatches);

        $fileName = md5($base64) . '.' . $extMatches[1];
        $path = $_SERVER['DOCUMENT_ROOT'] . '/upload/tmp/' . $fileName;

        $file = fopen($path, 'wb');

        fwrite($file, base64_decode($data[1]));
        fclose($file);

        return $path;
    }


    /**
     * Функция возвращает информацию о файле по его пути из b_file и если файла
     * там нет, то сохраняет
     *
     * @param string $path Путь к файлу от корня сайта
     * @return array|bool
     * @throws SqlQueryException
     */
    public static function getByPath($path) {
        $result = false;

        $filePath = realpath($_SERVER['DOCUMENT_ROOT'] . $path);

        if (is_string($path) && file_exists($filePath)) {
            // Найдём файл в b_file
            $conn = BxApp::getConnection();
            $sqlQuery = 'SELECT ID FROM b_file WHERE DESCRIPTION = "' . $path . '"';
            $recordList = $conn->query($sqlQuery);

            while ($record = $recordList->fetch()) {
                if ($record['ID']) {
                    $result = self::getData($record['ID']);
                }
            }
            // -----------------------------------------------------------------


            if (!$result) {
                // Если файл не нашелся
                $fileArrayToSave = CFile::MakeFileArray($filePath);
                $fileArrayToSave['description'] = $path;

                $resultFileSave = CFile::SaveFile($fileArrayToSave, 'custom');

                if ((int)$resultFileSave) {
                    $result = self::getData($resultFileSave);
                }

            }

        }

        return $result;

    }


    /**
     * Функция возвращает массив с информацией о файле
     *
     * @param int $fileId ID файла
     * @return array
     */
    public static function getData($fileId) {
        $result = false;

        if ((int)$fileId) {
            $file = CFile::GetFileArray($fileId);

            $fileInfo = pathinfo($_SERVER['DOCUMENT_ROOT'] . $file['SRC']);
            $file['EXT'] = strtoupper($fileInfo['extension']);
            $file['FILE_SIZE_FORMAT'] = self::formatSize($file['FILE_SIZE'], 0);

            $result = $file;
        }

        return $result;
    }


    /**
     * Функция возвращает массив с синформацией о файле в укороченном виде
     *
     * @param int $file ID файла
     * @return array
     */
    public static function getDataTiny($file) {
        $result = self::getData($file);

        if ($result) {
            $result = [
                'ext'  => $result['EXT'],
                'id'   => (int) $result['ID'],
                'name' => $result['ORIGINAL_NAME'],
                'size' => $result['FILE_SIZE_FORMAT'],
                'src'  => $result['SRC'],
            ];
        }

        return $result;
    }


    /**
     * Функция возвращает путь до текущего скрипта относительно корня сайта
     *
     * @param string $dir Путь к файлу или директории, например __DIR__
     * @return string URl файла/директории относительно корня
     */
    public static function getDir($dir) {
        $resultSlashes = str_replace("\\", '/', $dir);
        $resultClear = str_replace($_SERVER['DOCUMENT_ROOT'], '', $resultSlashes);
        $resultClearSlashes = '/' . $resultClear . '/';

        return str_replace('//', '/', $resultClearSlashes);
    }


    /**
     * Функция сохраняет файлы $files в b_files и возвращает сведения о них
     *
     * @param array $files Массив файлов из $_FILES
     * @param int $userId ID авторизованного пользователя или IP неавторизованного
     * @param string $folderName Папка для загрузки файлов. По умолчанию 'files-upload'
     * @param string $moduleName Модуль, загружающий файлы. По умолчанию 'iblock'
     * @return array Сведения о загруженных файлах или ошибке
     */
    public static function getFileUploadProtectedForSave($files, $userId, $folderName = 'files-upload', $moduleName = 'iblock') {
        if (!is_array($files)) {
            return null;
        }

        $filePrefix = '';

        $filesId = [];

        foreach ($files['name'] as $keyFile => $fileName) {
            $file = [
                'name'        => $filePrefix . '_' . $fileName,
                'size'        => $files['size'][$keyFile],
                'tmp_name'    => $files['tmp_name'][$keyFile],
                'type'        => $files['type'][$keyFile],
                'description' => json_encode(['USER_ID' => $userId]),
                'MODULE_ID'   => $moduleName,
            ];

            if ($files['error'][$keyFile] === 0) {
                $fileId = CFile::SaveFile($file, $folderName);

                if ($fileId) {
                    $fileOnServer = CFile::GetFileArray($fileId);
                    $fileSystem = pathinfo($_SERVER['DOCUMENT_ROOT'] . $fileOnServer['SRC']);

                    $fileReturn = [
                        'id'   => $fileId,
                        'name' => $fileOnServer['FILE_NAME'],
                        'size' => str_replace('.', ',', CFile::FormatSize($fileOnServer['FILE_SIZE'])),
                        'ext'  => $fileSystem['extension'],
                        'src'  => $fileOnServer['SRC'],
                    ];

                    $filesId[] = $fileReturn;
                }

            } else {
                $filesId['ERROR'][] = [
                    'ERROR'     => 'Ошибка загрузки файла',
                    'FILE_DATA' => $file,
                    'FILES_ALL' => $files,
                ];

            }
        }

        return $filesId;
    }


    public static function getFileDataById(int $id): ?array {
        $fileData = CFile::GetFileArray($id);

        if (empty($fileData)) {
            return null;
        }

        $result = [
            'id'   => $id,
            'src'  => $fileData['SRC'],
            'size' => (int) $fileData['FILE_SIZE'],
            'alt'  => $fileData['DESCRIPTION'],
        ];

        // Удалим необязательные пустые поля
        $optionalFields = ['size', 'alt'];

        foreach ($optionalFields as $field) {
            if (!$result[$field]) {
                unset($result[$field]);
            }
        }

        return $result;
    }

    /**
     * @param int $id
     * @return array
     */
    public static function getImageDataById($id) {
        if (!$id) {
            return null;
        }

        $fileData = CFile::GetFileArray((int)$id);

        $result = [
            'id'   => (int)$id,
            'src'  => $fileData['SRC'],
            'h'    => (int)$fileData['HEIGHT'],
            'w'    => (int)$fileData['WIDTH'],
            'size' => (int)$fileData['FILE_SIZE'],
            // пока поле нигде не нужно
            // 'name' => $fileData['ORIGINAL_NAME'],
            'alt'  => $fileData['DESCRIPTION'],
        ];

        // Удалим необязательные пустые поля
        $optionalFields = ['name', 'size', 'alt'];

        foreach ($optionalFields as $field) {
            if ($result[$field]) {
                continue;
            }
            unset($result[$field]);
        }

        return $result;
    }


    /**
     * Метод формирует информацию о картинке из превьюхи сформированной CFile::ResizeImageGet
     * @param $thumb
     * @return array|null
     */
    public static function getImageDataByResize($thumb): ?array
    {
        if (!is_array($thumb)) {
            return null;
        }

        return [
            'id'   => null,
            'src'  => $thumb['src'],
            'h'    => (int)$thumb['height'],
            'w'    => (int)$thumb['width'],
            'size' => (int)$thumb['size'],
            'name' => '',
            'alt'  => '',
        ];
    }


    /**
     * Функция возвращает массив пригодный для сохранения элемента
     *
     * @param array $filesId Массив ID файлов из b_file
     * @param string $moduleName Имя модуля
     * @return array Массив для CIBlockElement:Add()
     */
    public static function getFileArrayForSaveInProp($filesId, $moduleName = 'main') {
        if (!is_array($filesId)) {
            return null;
        }

        $result = [];
        foreach ($filesId as $key => $fileId) {
            if (!$fileId) { continue; }

            $fileThis = CFile::MakeFileArray($fileId);
            $fileThis['MODULE_ID'] = $moduleName;
            $fileThis['name'] = CUtil::translit($fileThis['name'], 'ru');

            if ($fileThis) {
                $result['n' . $key] = [
                    'VALUE'       => $fileThis,
                    'DESCRIPTION' => $fileThis['description'],
                ];
            }
        }

        return $result;
    }


    /**
     * Функция возвращает путь к заблюренной картинке
     *
     * @param string $filePath - Путь к исходной картинке
     * @param int $blurSize - Сила блюра 0-10
     * @return string|null - Путь к заблюренной картинке
     */
    public static function getImageBlurred($filePath, $blurSize) {
        $fileData = pathinfo($filePath);
        $image = imagecreatefromjpeg($filePath);
        if (!$image) { return null; }

        $fileName = md5(serialize([$filePath, $blurSize]));
        $imageBlurredPath = '/upload/blur-images/' . $fileName . '.' . $fileData['extension'];
        $imageBlurredServerPath = $_SERVER['DOCUMENT_ROOT'] . $imageBlurredPath;

        if (!file_exists($imageBlurredServerPath)) {
            // Если заблюренная картинка для этого файла и с этим блюром еще не создана
            $image = self::blurImage($image, $blurSize);
            imagejpeg($image, $imageBlurredServerPath, 90);
            imagedestroy($image);
        }

        return $imageBlurredPath;
    }


    /**
     * Функция сохраняет файлы $files в b_files и возвращает сведения о них
     *
     * @param array $file файл из $_FILES
     * @param string $folderName папка для загрузки файлов. По умолчанию 'files-upload'
     * @param string $moduleName модуль, загружающий файлы. По умолчанию 'iblock'
     * @return int
     */
    public static function saveFile(array $file, string $folderName = 'files-upload', string $moduleName = 'iblock'): ?int {
        $file = [
            'name'        => $file['name'],
            'size'        => $file['size'],
            'tmp_name'    => $file['tmp_name'],
            'type'        => $file['type'],
            'MODULE_ID'   => $moduleName,
        ];

        $fileId = CFile::SaveFile($file, $folderName);

        return is_numeric($fileId) ? (int)$fileId : null;
    }


    /**
     * Strong Blur
     *
     * @param resource $gdImageResource
     * @param int $blurFactor - optional
     *  This is the strength of the blur
     *  0 = no blur, 3 = default, anything over 5 is extremely blurred
     * @return resource - image resource
     * @author Martijn Frazer, idea based on http://stackoverflow.com/a/20264482
     * @see https://www.php.net/manual/ru/function.imagefilter.php#114750
     */
    private static function blurImage($gdImageResource, $blurFactor = 3) {
        // blurFactor has to be an integer
        $blurFactor = (int)$blurFactor;

        $originalWidth = imagesx($gdImageResource);
        $originalHeight = imagesy($gdImageResource);

        $smallestWidth = ceil($originalWidth * (0.5 ** $blurFactor));
        $smallestHeight = ceil($originalHeight * (0.5 ** $blurFactor));

        // for the first run, the previous image is the original input
        $prevImage = $gdImageResource;
        $prevWidth = $originalWidth;
        $prevHeight = $originalHeight;

        // scale way down and gradually scale back up, blurring all the way
        for ($i = 0; $i < $blurFactor; $i++) {
            // determine dimensions of next image
            $nextWidth = $smallestWidth * (2 ** $i);
            $nextHeight = $smallestHeight * (2 ** $i);

            // resize previous image to next size
            $nextImage = imagecreatetruecolor($nextWidth, $nextHeight);
            imagecopyresized($nextImage, $prevImage, 0, 0, 0, 0,
                $nextWidth, $nextHeight, $prevWidth, $prevHeight);

            // apply blur filter
            imagefilter($nextImage, IMG_FILTER_GAUSSIAN_BLUR);

            // now the new image becomes the previous image for the next step
            $prevImage = $nextImage;
            $prevWidth = $nextWidth;
            $prevHeight = $nextHeight;
        }

        // scale back to original size and blur one more time
        imagecopyresized($gdImageResource, $nextImage,
            0, 0, 0, 0, $originalWidth, $originalHeight, $nextWidth, $nextHeight);
        imagefilter($gdImageResource, IMG_FILTER_GAUSSIAN_BLUR);

        // clean up
        imagedestroy($prevImage);

        // return result
        return $gdImageResource;
    }

}
