<?php


namespace ALS\Helper;


class DistCleaner {
    /**
     * Функция запускает очистку директории /f/dist/ от старых билдов.
     * Работает в режиме агента в битриксе по расписанию.
     * @link https://dev.1c-bitrix.ru/learning/course/index.php?COURSE_ID=43&LESSON_ID=3436&LESSON_PATH=3913.4619.3436
     */
    public static function run() {
        // Массив файлов, которые не удаляем в любом случае
        $skipFiles = [
            '.', // Результат из функции scandir()
            '..', // Результат из функции scandir()
            '3rdpartylicenses.txt',
            'index.html',
        ];


        // Сначала собираем список всех файлов в /f/dist
        $dir = $_SERVER['DOCUMENT_ROOT'] . '/f/dist/';
        $allFiles = scandir($dir);


        // Узнаем из index.html какие файлы не нужны для текущего билда
        $filesToDelete = [];
        $indexHtml = file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/f/dist/index.html');

        foreach ($allFiles as $fileName) {
            // Если файл в списке исключений на удаление, то не удаляем его
            $isFileNeedToSkip = in_array($fileName, $skipFiles, true);

            // Если файл упоминается в index.html, то не удаляем его
            $isFileUsedInIndexHtml = strpos($indexHtml, $fileName);

            if ($isFileNeedToSkip || $isFileUsedInIndexHtml) {
                continue;
            }

            $filesToDelete[] = $fileName;
        }


        // Удаляем ненужные файлы
        foreach ($filesToDelete as $fileToDelete) {
            unlink($dir . $fileToDelete);
        }


        // Возвращаем код запуска агента
        return '\ALS\Helper\DistCleaner::run();';
    }
}
