<?php

namespace ALS\Helper;


use CUserFieldEnum;


class TypeConvert {
    /**
     * Регулярка для разбора строки в select <br>
     * 0 - исходная строка <br>
     * 1 - поле в битриксе <br>
     * 3 - тип данных <br>
     * 5 - название поля на выходе <br>
     */
    private const typePattern = '/^([\w\.]+)(:|)([\w\[\]]+|)(>|)(\w+|)$/';

    /** @var array Массив строк - чистый SELECT */
    private $select = [];

    /** @var array Массив типов данных для select */
    private $types = [];


    public function __construct(array $select = []) {
        foreach ($select as $field) {
            /**
             * 0 - исходная строка
             * 1 - поле в битриксе
             * 3 - тип данных
             * 5 - название поля на выходе
             */
            $fieldMatches = [];

            if (preg_match(self::typePattern, $field, $fieldMatches)) {
                $bxFieldName = $fieldMatches[1];
                $this->select[] = $bxFieldName;

                if (false !== strpos($bxFieldName, 'PROPERTY_')) {
                    // Если это не поле элемента, а его свойство:

                    $isLinkToAnother = (bool)strpos($bxFieldName, '.');
                    $isLinkToProperty = (bool)strpos($bxFieldName, '.PROPERTY_');

                    if (!$isLinkToAnother || $isLinkToProperty) {
                        // И это не свойство привязки или привязка к другому свойству, то добавим _VALUE
                        $bxFieldName = $fieldMatches[1] . '_VALUE';
                    }
                }

                $this->types[] = [$bxFieldName, $fieldMatches[3], $fieldMatches[5]];
            }
        }
    }


    public function getSelect(): array {
        return $this->select ?: [];
    }


    public function getTypes(): array {
        return $this->types ?: [];
    }


    /**
     * Метод конвертирует результаты getList-а по заданным в SELECT типам
     *
     * Возможные типы данных
     *  <li> string | string[]
     *  <li> int | int[]
     *  <li> float | float[]
     *  <li> bool
     *  <li> DateHuman
     *  <li> DescriptiveFloat | DescriptiveFloat[]
     *  <li> DescriptiveInt | DescriptiveInt[]
     *  <li> DescriptiveString | DescriptiveString[]
     *  <li> EnumBool // depend from `'GET_ENUM_CODE' => 'Y'`
     *  <li> EnumCode // depend from `'GET_ENUM_CODE' => 'Y'`
     *  <li> EnumId // depend from `'GET_ENUM_CODE' => 'Y'`
     *  <li> File | File[]
     *  <li> Json
     *  <li> Image | Image[]
     *  <li> Html
     *  <li> DescriptiveHtml[]
     *  <li> Map
     *  <li> Table | Table[]
     *  <li> Tags
     *  <li> UFEnumCode
     *  <li> UFEnumValue
     *  <li> X - Удалить поле из финальной выдачи
     *
     * @param array $items
     * @return array
     */
    public function convertDataTypes(array $items = []): array {
        $result = [];

        foreach ($items as $k => $item) {
            foreach ($this->types as $type) {
                $value = null;

                if (isset($item[$type[0]])) {
                    $value = $item[$type[0]];
                } elseif (false !== strpos($type[0], '.')) {
                    // Если это свойство привязки
                    $itemField = str_replace('.', '_', $type[0]);
                    $value = $item[$itemField];
                }

                $fieldType = $type[1] ?: 'string';
                $fieldName = $type[2] ?: $type[0];

                $descriptionPropKey = str_replace('_VALUE', '_DESCRIPTION', $type[0]);

                if ($fieldType === 'string') {
                    $value = (string) ($value ?: '');

                } elseif ($fieldType === 'string[]') {
                    $newValue = [];

                    if (is_array($value)) {
                        foreach ($value as $string) {
                            $newValue[] = (string) ($string ?: '');
                        }
                    }
                    elseif (is_string($value)) {
                        $newValue[] = $value;
                    }

                    $value = $newValue;

                } elseif ($fieldType === 'int') {
                    $value = is_string($value) || !empty($value) ? (int) $value : null;

                } elseif ($fieldType === 'int[]') {
                    $newValue = [];

                    foreach ($value as $number) {
                        $newValue[] = (int) $number;
                    }

                    $value = $newValue;

                } elseif ($fieldType === 'float') {
                    $value = (float)$value;

                } elseif ($fieldType === 'float[]') {
                    $newValue = [];

                    foreach ($value as $number) {
                        $newValue[] = (float)$number;
                    }

                    $value = $newValue;

                } elseif ($fieldType === 'bool') {
                    $value = (bool)$value;

                } elseif ($fieldType === 'DateHuman') {
                    $yearNow = date('Y');
                    $newYear = preg_replace('/(.+)(\d{4})$/', '$2', $value);
                    $dateFormat = ($yearNow === $newYear) ? 'DD MMMM' : 'DD MMMM YYYY';
                    $value = Help::formatDateHuman($value, $dateFormat);

                } elseif ($fieldType === 'EnumBool') {
                    $fieldNameEnum = str_replace('_VALUE', '_XML_ID', $type[0]);
                    $value = ($item[$fieldNameEnum] === 'Y');

                } elseif ($fieldType === 'EnumCode') {
                    $fieldNameEnum = str_replace('_VALUE', '_XML_ID', $type[0]);
                    $value = $item[$fieldNameEnum];

                } elseif ($fieldType === 'EnumId') {
                    $fieldNameEnum = str_replace('_VALUE', '_ENUM_ID', $type[0]);
                    $value = (int)$item[$fieldNameEnum];

                } elseif ($fieldType === 'DescriptiveFloat') {
                    $value = [
                        'value'       => (float)$value,
                        'description' => $item[$descriptionPropKey],
                    ];

                } elseif ($fieldType === 'DescriptiveFloat[]') {
                    $dataFormatted = [];
                    foreach ($value as $valueKey => $valueData) {
                        $dataFormatted[] = [
                            'value'       => (float)$valueData,
                            'description' => $item[$descriptionPropKey][$valueKey],
                        ];
                    }
                    $value = $dataFormatted;

                } elseif ($fieldType === 'DescriptiveInt') {
                    $value = [
                        'value'       => (int)$value,
                        'description' => $item[$descriptionPropKey],
                    ];

                } elseif ($fieldType === 'DescriptiveInt[]') {
                    $dataFormatted = [];
                    foreach ($value as $valueKey => $valueData) {
                        $dataFormatted[] = [
                            'value'       => (int)$valueData,
                            'description' => $item[$descriptionPropKey][$valueKey],
                        ];
                    }
                    $value = $dataFormatted;

                } elseif ($fieldType === 'DescriptiveString') {
                    $value = [
                        'value'       => $value,
                        'description' => $item[$descriptionPropKey],
                    ];

                } elseif ($fieldType === 'DescriptiveString[]') {
                    $dataFormatted = [];
                    foreach ($value as $valueKey => $valueData) {
                        $dataFormatted[] = [
                            'value'       => $valueData,
                            'description' => $item[$descriptionPropKey][$valueKey],
                        ];
                    }
                    $value = $dataFormatted;

                } elseif ($fieldType === 'File') {
                    $value = is_numeric($value)
                        ? File::getDataTiny((int)$value)
                        : null;

                } elseif ($fieldType === 'File[]') {
                    $valueNew = [];

                    foreach ($value as $valueKey => $fileId) {
                        $fileData = is_numeric($fileId)
                            ? File::getDataTiny((int)$fileId)
                            : [];
                        $fileData['description'] = $item[$descriptionPropKey][$valueKey];

                        $valueNew[] = $fileData;
                    }

                    $value = $valueNew;

                } elseif ($fieldType === 'Json') {
                    $value = json_decode(trim($value), true);

                } elseif ($fieldType === 'Image') {
                    $value = File::getImageDataById($value);

                } elseif ($fieldType === 'Image[]' && is_array($value)) {
                    $valueNew = [];

                    foreach ($value as $imageId) {
                        $valueNew[] = File::getImageDataById($imageId);
                    }

                    $value = $valueNew;

                } elseif ($fieldType === 'Html') {
                    $value = $value['TYPE'] === 'HTML' ? $value['TEXT'] : TxtToHTML($value['TEXT']);

                } elseif ($fieldType === 'Html[]') {
                    $valueNew = [];

                    foreach ($value as $valueData) {
                        $valueNew[] = $valueData['TYPE'] === 'HTML' ? $valueData['TEXT'] : TxtToHTML($valueData['TEXT']);
                    }

                    $value = $valueNew;

                } elseif ($fieldType === 'DescriptiveHtml[]') {
                    $dataFormatted = [];
                    foreach ($value as $valueKey => $valueData) {
                        $dataFormatted[] = [
                            'value'       => $valueData['TYPE'] === 'HTML'
                                ? $valueData['TEXT']
                                : TxtToHTML($valueData['TEXT']),
                            'description' => $item[$descriptionPropKey][$valueKey],
                        ];
                    }
                    $value = $dataFormatted;

                } elseif ($fieldType === 'Map') {
                    $coordinates = explode(',', $value);
                    $value = $value ? [(float)$coordinates[0], (float)$coordinates[1]] : null;

                } elseif ($fieldType === 'Table' && $value['TYPE'] === 'HTML' && $value['TEXT']) {
                    $value = Html::getDataFromTable($value['TEXT']);

                } elseif ($fieldType === 'Table[]' && count($value)) {
                    $valueFormatted = [];
                    foreach ($value as $bitrixText) {
                        $valueFormatted[] = Html::getDataFromTable($bitrixText['TEXT']);
                    }

                    $value = $valueFormatted;

                } elseif ($fieldType === 'Tags') {
                    $tagsInArray = explode(',', $value);

                    $value = [];

                    if ($value) {
                        foreach ($tagsInArray as $tag) {
                            $value[] = trim($tag);
                        }
                    }

                } elseif ($fieldType === 'DescriptiveTable[]') {
                    $dataFormatted = [];
                    foreach ($value as $valueKey => $valueData) {
                        $dataFormatted[] = [
                            'value'       => Html::getDataFromTable($valueData['TEXT']),
                            'description' => $item[$descriptionPropKey][$valueKey],
                        ];
                    }

                    $value = $dataFormatted;

                } elseif ($fieldType === 'UFEnumCode') {
                    $enumResult = CUserFieldEnum::GetList([], ['ID' => $value]);
                    if ($enum = $enumResult->getNext()) {
                        $value = $enum['XML_ID'];
                    }
                    else {
                        $value = '';
                    }

                } elseif ($fieldType === 'UFEnumValue') {
                    $enumResult = CUserFieldEnum::GetList([], ['ID' => $value]);
                    if ($enum = $enumResult->getNext()) {
                        $value = $enum['VALUE'];
                    }
                    else {
                        $value = '';
                    }

                }

                if ($fieldType !== 'X') {
                    $result[$k][$fieldName] = $value;
                }
            }
        }


        return $result;
    }

}
