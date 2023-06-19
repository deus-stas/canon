<?php

require $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';
?>
<style>
    body {
        font: 400 16px Arial, Sans-Serif;
        color: #000;
    }

    a {
        color: #00aeef;
    }

    .mainMail {
        line-height: 1.4;
        margin: 0 auto;
        max-width: 580px;
        width: 100%;
        background: #fff;
        padding: 2em;
    }

    h1 {
        font-weight: 400;
        font-size: 2em;
    }

    table td {
        vertical-align: top;
        padding-bottom: .5em;
    }

    .note {
        color: #a0a0a0;
        font-size: .6em;
    }
</style>
<div class="mainMail"><?php
    foreach ($arResult['VALUES'] as $questionKey => $resultItem): ?><?php
        if ($questionKey === 'agree') {
            continue;
        }

        $resultString = "{$resultItem[0]['TITLE']}: ";


        $separator = ', ';
        $isFirst = true;

        foreach ($resultItem as $key => $resultValue) {
            $text = $resultValue['USER_TEXT'];

            // Исключение для полей Старана и Регион
            if(($resultValue['VARNAME'] == 'country' || $resultValue['VARNAME'] == 'region') && !$text){
                $text = $resultValue['ANSWER_TEXT'];
            }

            if (!$isFirst) {
                $resultString .= $separator;
            }
            $isFirst = false;

            $resultString .= $text;
        }

        echo $resultString.'<br>';
    endforeach; ?>

  <p>Ссылка на результат: <a href="<?php
      echo $arResult['PATH'] ?>"><?php
          echo $arResult['PATH'] ?></a>
  </p>

  <p class="note">Письмо сформировано автоматически.</p>
</div>
