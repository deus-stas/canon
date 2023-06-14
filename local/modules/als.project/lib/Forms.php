<?php


namespace ALS\Project;


use ALS\Helper\Mail;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use CForm;
use CFormResult;


class Forms
{

    /**
     * @throws LoaderException
     */
    public static function saveForm(array $params): array
    {
        if (Loader::IncludeModule("form")) {
            if ($resultId = CFormResult::Add($params['form_id'], $params['values'])) {
                $result  = [];
                $answers = [];

                $data           = [];
                $data['VALUES'] = CFormResult::GetDataByID($resultId, [], $result, $answers);

                $baseUrl      = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . '/';
                $data['PATH'] = $baseUrl . 'bitrix/admin/form_result_edit.php?lang=ru&WEB_FORM_ID=' . $params['form_id'] . '&RESULT_ID=' . $resultId;

                $template = Mail::getInteractiveTemplate(
                    'feedback/add',
                    $data
                );

                Mail::send([
                    'TEXT'    => $template,
                    'SUBJECT' => 'Canon MS. Обратная связь. Новый отклик',
                ]);
                Mail::forceSending();


                return [
                    'status' => 'ok',
                ];
            }

            return [
                'status' => 'error',
            ];
        }

        return [
            'status'  => 'error',
            'message' => 'Module "form" not included',
        ];
    }
    /**
     * @throws LoaderException
     */
    public static function saveFormPostWarranty(array $params): array
    {
        if (Loader::IncludeModule("form")) {

            if ($resultId = CFormResult::Add($params['form_id'], $params['values'])) {
                $result  = [];
                $answers = [];

                $data           = [];
                $data['VALUES'] = CFormResult::GetDataByID($resultId, [], $result, $answers);

                $baseUrl      = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . '/';
                $data['PATH'] = $baseUrl . 'bitrix/admin/form_result_edit.php?lang=ru&WEB_FORM_ID=' . $params['form_id'] . '&RESULT_ID=' . $resultId;

                $template = Mail::getInteractiveTemplate(
                    'post_warranty/add',
                    $data
                );
                $res_files = [];
                if($data['VALUES']['files']){
                    foreach ($data['VALUES']['files'] as $file){
                        $res_files[] = $file['USER_FILE_ID'];
                    }
                }
                Mail::send([
                    'TEXT'    => $template,
                    'EMAIL_TO' => \COption::GetOptionString( "askaron.settings", "UF_EMAIL_TO_POST_WARRANTY"),
                    'SUBJECT' => 'Canon MS. Постгарантийный сервис. Новый отклик',
                ],$res_files);
                Mail::forceSending();


                return [
                    'status' => 'ok',
                ];
            }

            return [
                'status' => 'error',
            ];
        }

        return [
            'status'  => 'error',
            'message' => 'Module "form" not included',
        ];
    }

    /**
     * @throws LoaderException
     */
    public static function getFeedback(): array
    {
        return self::getForm(LANGUAGE_CODE === 'en' ? 'feedback_en' : 'feedback');
    }

    /**
     * @throws LoaderException
     */
    public static function getPostWarranty(): array
    {
        return self::getForm(LANGUAGE_CODE === 'en' ? 'post_warranty_en' : 'post_warranty');
    }

    /**
     * @throws LoaderException
     */
    public static function getForm(string $sid): array
    {

        $arResult = [];

        if (Loader::IncludeModule("form")) {
            CForm::GetDataByID(
                CForm::GetBySID($sid)->fetch()['ID'],
                $form,
                $questions,
                $answers,
                $dropdown,
                $multiselect);
        } else {
            return [
                'status'  => 'error',
                'message' => 'Module "form" not included',
            ];
        }

        $arResult['ID']               = $form['ID'];
        $arResult['NAME']             = $form['NAME'];
        $arResult['DESCRIPTION']      = $form['DESCRIPTION'];
        $arResult['SAVE_BUTTON_TEXT'] = $form['BUTTON'];

        $arResult['QUESTIONS'] = [];

        foreach ($questions as $key => $question) {
            $arResult['QUESTIONS'][$key] = [
                'ID'           => $question['ID'],
                'SID'          => $question['SID'],
                'TITLE'        => $question['TITLE'],
                'SORT'         => $question['C_SORT'],
                'REQUIRED'     => $question['REQUIRED'],
                'AUTOCOMPLETE' => $question["COMMENTS"],
            ];
        }
        // Постгарантийный сервис
        $id_related = []; // Сбор id ответов от поля
        if($sid == 'post_warranty' || $sid == 'post_warranty_en'){
            foreach ($questions as $key => $question) {
                if($key == 'related'){
                    foreach ($answers as $subKey => $answer) {
                        if($subKey == $key){
                            foreach ($answer as $value) {
                                $id_related[] = $value['ID'];
                            }
                        }
                    }
                }
            }
        }

        //имена полей формируются https://dev.1c-bitrix.ru/api_help/form/htmlnames.php
        $htmlNameSid   = ['radio', 'dropdown', 'checkbox', 'multiselect'];
        $htmlNameArray = ['checkbox', 'multiselect'];

        foreach ($answers as $key => $answer) {
            foreach ($answer as $value) {
                $arResult['QUESTIONS'][$key]['ANSWERS'][] = [
                    'ID'         => $value['ID'],
                    'CODE'       => str_replace('_', '-', $key),
                    'MESSAGE'    => $value['MESSAGE'],
                    'FIELD_TYPE' => $value['FIELD_TYPE'],
                    'HTML_NAME'  => 'form_' . $value['FIELD_TYPE'] . '_' . (in_array($value['FIELD_TYPE'], $htmlNameSid, true) ? $key : $value['ID']) . (in_array($value['FIELD_TYPE'], $htmlNameArray, true) ? '[]' : ''),
                    'SORT'       => $value['C_SORT'],
                    'PARENT_ID'  => $value['FIELD_PARAM'],
                ];

                // Для зависимых полей, связку производим через свойства "параметры" в ответе у поля, прописав туда id ответа от другого поля
                if($value['FIELD_PARAM']){
                    $params = explode(",", $value['FIELD_PARAM']); // разбиваем строку на массив
                    $found = false;
                    foreach ($params as $param) {
                        if (in_array($param, $id_related)) {
                            $found = true;
                            break;
                        }
                    }

                    if ($found) {
                        // Добавляем в поле, а именно у related (Тема запроса) группе под полей
                        $arResult['QUESTIONS']['SUB_QUESTIONS']['SID'] = 'SUB_QUESTIONS';
                        $arResult['QUESTIONS']['SUB_QUESTIONS']['SORT'] = '910';
                        $arResult['QUESTIONS']['SUB_QUESTIONS']['fields'][] = $arResult['QUESTIONS'][$key];

                        // Убираем ненужные поля из общего списка
                        unset($arResult['QUESTIONS'][$key]); // очищаем
                    }
                }

            }
        }

        return $arResult;
    }

}
