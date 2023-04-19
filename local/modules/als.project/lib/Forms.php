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
    public static function getFeedback(): array
    {
        return self::getForm(LANGUAGE_CODE === 'en' ? 'feedback_en' : 'feedback');
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
            }
        }

        return $arResult;
    }

}
