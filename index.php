<?php
require $_SERVER['DOCUMENT_ROOT'] . '/local/tools/include-bitrix.php';

$filedir = '/f/dist';

// Пасринг index.html
$indexHtml = file_get_contents($_SERVER['DOCUMENT_ROOT'] . $filedir . '/index.html');
$matches = [];
preg_match('/<head[^>]*>(.+)<\/head>.*<body[^>]*>(.+)<\/body>/sm', $indexHtml, $matches);

// Заменим пути к стилям в head и скриптам в body
$head = preg_replace('/href="(styles.+)"/m', 'href="' . $filedir . '/$1"', $matches[1]);
$body = preg_replace('/src="(\w[^"]+)/m', 'src="' . $filedir . '/$1', $matches[2]);
$body = preg_replace('/src="' . str_replace('/', '\/', $filedir) . '\/http([s]?):/m', 'src="http$1:', $body);

?>
<!doctype html>
<!-- (c) Art. Lebedev Studio | http://www.artlebedev.ru/ -->
<html lang="ru">
	<head>
		<?=$head?>
	</head>
	<body>
		<?=$body?>
	</body>
</html>
