<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
/** @var CBitrixComponent $this */
/** @var array $arParams */
/** @var array $arResult */
/** @var string $componentPath */
/** @var string $componentName */
/** @var string $componentTemplate */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

$this->setFrameMode(false);
CJSCore::Init(array('jquery2'));

if (!CModule::IncludeModule("iblock")) {
    ShowError(GetMessage("CC_BIEAF_IBLOCK_MODULE_NOT_INSTALLED"));
    return;
}

if (0 >= (int)$arParams['IBLOCK_USER_LINK_ID']) {
    //todo: LANG
    ShowError('Не выбран инфоблок с элементами, привязанными к текущему пользователю');
    return;
}


$arElement = false;

if ($arParams["IBLOCK_ID"] > 0) {
    $arIBlock = CIBlock::GetArrayByID($arParams["IBLOCK_ID"]);
} else {
    $arIBlock = false;
}


// обработка ajax запросов
if ($_POST['ajax'] == 'Y' && $_POST['component'] == $componentName) {
    $APPLICATION->RestartBuffer();
    if ($_POST['action'] == 'get_subsections' && (int)$_POST['id']) {


        $rsIBlockSectionList = CIBlockSection::GetList(
            array("left_margin" => "asc"),
            array(
                "ACTIVE" => "Y",
                "IBLOCK_ID" => $arParams["IBLOCK_ID"],
                "SECTION_ID" => $_POST['id']
            ),
            false,
            array("ID", "NAME", "DEPTH_LEVEL")
        );

        $arResult["SECTION_LIST"] = array(0 => array('VALUE' => 'Не установлено'));
        while ($arSection = $rsIBlockSectionList->GetNext()) {
            $arResult["SECTION_LIST"][$arSection["ID"]] = array(
                "VALUE" => $arSection["NAME"]
            );
        }

        echo json_encode($arResult["SECTION_LIST"]);
    }
    exit;
}

$arParams["ID"] = intval($_REQUEST["CODE"]);
$arParams["MAX_FILE_SIZE"] = intval($arParams["MAX_FILE_SIZE"]);
$arParams["PREVIEW_TEXT_USE_HTML_EDITOR"] = $arParams["PREVIEW_TEXT_USE_HTML_EDITOR"] === "Y" && CModule::IncludeModule("fileman");
$arParams["DETAIL_TEXT_USE_HTML_EDITOR"] = $arParams["DETAIL_TEXT_USE_HTML_EDITOR"] === "Y" && CModule::IncludeModule("fileman");
$arParams["RESIZE_IMAGES"] = $arParams["RESIZE_IMAGES"] === "Y";
$arParams["AGREEMENT"] = $arParams["AGREEMENT"] == "Y" && strlen($arParams["AGREEMENT_URL"]) ? true : false;


// заполняем массив свойств, которые требуется отобразить
if (!is_array($arParams["PROPERTY_CODES"])) {
    $arParams["PROPERTY_CODES"] = array();
} else {
    foreach ($arParams["PROPERTY_CODES"] as $i => $k)
        if (strlen($k) <= 0)
            unset($arParams["PROPERTY_CODES"][$i]);
}

// свойства обязательные к заполнению
$arParams["PROPERTY_CODES_REQUIRED"] = is_array($arParams["PROPERTY_CODES_REQUIRED"]) ? $arParams["PROPERTY_CODES_REQUIRED"] : array();
foreach ($arParams["PROPERTY_CODES_REQUIRED"] as $key => $value)
    if (strlen(trim($value)) <= 0)
        unset($arParams["PROPERTY_CODES_REQUIRED"][$key]);

// сообщение об успешном добавлнии элемента
$arParams["USER_MESSAGE_ADD"] = trim($arParams["USER_MESSAGE_ADD"]);
if (strlen($arParams["USER_MESSAGE_ADD"]) <= 0)
    $arParams["USER_MESSAGE_ADD"] = GetMessage("IBLOCK_USER_MESSAGE_ADD_DEFAULT");

// группы пользователей, имеющие право на добавление
if (!is_array($arParams["GROUPS"]))
    $arParams["GROUPS"] = array();

// группы текущего пользоателя
$arGroups = $USER->GetUserGroupArray();

// может ли текущий пользователь добавлять элемент
$bAllowAccess = count(array_intersect($arGroups, $arParams["GROUPS"])) > 0 || $USER->IsAdmin();

$arResult["ERRORS"] = array();

if ($bAllowAccess) {

    // получаем список разделов инфоблока
    // todo: выбор раздела
    $rsIBlockSectionList = CIBlockSection::GetList(
        array("left_margin" => "asc"),
        array(
            "ACTIVE" => "Y",
            "IBLOCK_ID" => $arParams["IBLOCK_ID"],
            "DEPTH_LEVEL" => 1
        ),
        false,
        array("ID", "NAME", "DEPTH_LEVEL")
    );

    $arResult["SECTION_LIST"] = array();
    while ($arSection = $rsIBlockSectionList->GetNext()) {
        $arResult["SECTION_LIST"][$arSection["ID"]] = array(
            "VALUE" => $arSection["NAME"]
        );
    }

    // todo: ограничение максимальной длинны для некоторых полей
    $COL_COUNT = intval($arParams["DEFAULT_INPUT_SIZE"]);
    if ($COL_COUNT < 1)
        $COL_COUNT = 30;


    $arResult["PROPERTY_LIST"] = array();

    $arResult["PROPERTY_LIST_FULL"] = array(
        "NAME" => array(
            "PROPERTY_TYPE" => "S",
            "MULTIPLE" => "N",
            "COL_COUNT" => ((int)$arParams["NAME_INPUT_SIZE"] ? (int)$arParams["NAME_INPUT_SIZE"] : $COL_COUNT),
            "SORT" => ((int)$arParams["CUSTOM_SORT_NAME"] ? (int)$arParams["CUSTOM_SORT_NAME"] : 0)
        ),

        "DATE_ACTIVE_FROM" => array(
            "PROPERTY_TYPE" => "S",
            "MULTIPLE" => "N",
            "USER_TYPE" => "DateTime",
            "SORT" => ((int)$arParams["CUSTOM_SORT_DATE_ACTIVE_FROM"] ? (int)$arParams["CUSTOM_SORT_DATE_ACTIVE_FROM"] : 0)
        ),

        "DATE_ACTIVE_TO" => array(
            "PROPERTY_TYPE" => "S",
            "MULTIPLE" => "N",
            "USER_TYPE" => "DateTime",
            "SORT" => ((int)$arParams["CUSTOM_SORT_DATE_ACTIVE_TO"] ? (int)$arParams["CUSTOM_SORT_DATE_ACTIVE_TO"] : 0)
        ),

        "IBLOCK_SECTION" => array(
            "PROPERTY_TYPE" => "L",
            "ROW_COUNT" => "8",
            "MULTIPLE" => $arParams["MAX_LEVELS"] == 1 ? "N" : "Y",
            "ENUM" => $arResult["SECTION_LIST"],
            "SORT" => ((int)$arParams["CUSTOM_SORT_IBLOCK_SECTION"] ? (int)$arParams["CUSTOM_SORT_IBLOCK_SECTION"] : 0)

        ),

        "PREVIEW_TEXT" => array(
            "PROPERTY_TYPE" => ($arParams["PREVIEW_TEXT_USE_HTML_EDITOR"] ? "HTML" : "T"),
            "MULTIPLE" => "N",
            "ROW_COUNT" => "5",
            "COL_COUNT" => ((int)$arParams["PREVIEW_TEXT_INPUT_SIZE"] ? (int)$arParams["PREVIEW_TEXT_INPUT_SIZE"] : $COL_COUNT),
            "SORT" => ((int)$arParams["CUSTOM_SORT_PREVIEW_TEXT"] ? (int)$arParams["CUSTOM_SORT_PREVIEW_TEXT"] : 0)
        ),
        "PREVIEW_PICTURE" => array(
            "PROPERTY_TYPE" => "F",
            "FILE_TYPE" => "jpg, gif, bmp, png, jpeg",
            "MULTIPLE" => "N",
            "SORT" => ((int)$arParams["CUSTOM_SORT_PREVIEW_PICTURE"] ? (int)$arParams["CUSTOM_SORT_PREVIEW_PICTURE"] : 0)
        ),
        "DETAIL_TEXT" => array(
            "PROPERTY_TYPE" => ($arParams["DETAIL_TEXT_USE_HTML_EDITOR"] ? "HTML" : "T"),
            "MULTIPLE" => "N",
            "ROW_COUNT" => "5",
            "COL_COUNT" => $COL_COUNT,
            "SORT" => ((int)$arParams["CUSTOM_SORT_DETAIL_TEXT"] ? (int)$arParams["CUSTOM_SORT_DETAIL_TEXT"] : 0)
        ),
        "DETAIL_PICTURE" => array(
            "PROPERTY_TYPE" => "F",
            "FILE_TYPE" => "jpg, gif, bmp, png, jpeg",
            "MULTIPLE" => "N",
            "SORT" => ((int)$arParams["CUSTOM_SORT_DETAIL_PICTURE"] ? (int)$arParams["CUSTOM_SORT_DETAIL_PICTURE"] : 0)
        ),

    );

    // Добавляем коды свойств из PROPERTY_LIST_FULL в PROPERTY_LIST
    foreach ($arResult["PROPERTY_LIST_FULL"] as $key => $arr) {
        if (in_array($key, $arParams["PROPERTY_CODES"])) $arResult["PROPERTY_LIST"][] = $key;
    }


    // получаем список свойств инфоблока
    $rsIBLockPropertyList = CIBlockProperty::GetList(array("sort" => "asc", "name" => "asc"), array("ACTIVE" => "Y", "IBLOCK_ID" => $arParams["IBLOCK_ID"]));
    while ($arProperty = $rsIBLockPropertyList->GetNext()) {

        // получаем свойства типа "Список" и запоминаем возможные значения
        if ($arProperty["PROPERTY_TYPE"] == "L") {
            $rsPropertyEnum = CIBlockProperty::GetPropertyEnum($arProperty["ID"]);
            $arProperty["ENUM"] = array();
            while ($arPropertyEnum = $rsPropertyEnum->GetNext()) {
                $arProperty["ENUM"][$arPropertyEnum["ID"]] = $arPropertyEnum;
            }
        }

        if ($arProperty["PROPERTY_TYPE"] == "T") {
            if (empty($arProperty["COL_COUNT"])) $arProperty["COL_COUNT"] = "30";
            if (empty($arProperty["ROW_COUNT"])) $arProperty["ROW_COUNT"] = "5";
        }


        // если свойство пользовательского типа - смотрим, нужно ли отображение
        if (strlen($arProperty["USER_TYPE"]) > 0) {
            $arUserType = CIBlockProperty::GetUserType($arProperty["USER_TYPE"]);
            if (array_key_exists("GetPublicEditHTML", $arUserType))
                $arProperty["GetPublicEditHTML"] = $arUserType["GetPublicEditHTML"];
            else
                $arProperty["GetPublicEditHTML"] = false;
        } else {
            $arProperty["GetPublicEditHTML"] = false;
        }

        // обрабатываем совйство-привязка к элементу другого инффоблока по пользователю
        if ($arProperty["ID"] == $arParams['IBLOCK_USER_LINK_INADD_PROPERTY']) {

            $arProperty['PROPERTY_TYPE'] = 'USER_LINKED';
            // получаем список возможных значений свойства, исходя из названия элементов, находящихся в связанном инфоблоке
            // и у которых свойство "привязка к пользователю" = $ID текущего пользователя.

            // todo: проверять, дейстивтельно ли пользователю доступен данный адрес, переданный в $_REQUEST

            if (!(int)$arParams['IBLOCK_USER_LINK_ID']) {
                ShowError(GetMessage("IBLOCK_ADD_LINKED_USER_PROP_ERROR"));
                return;
            }


            if (!(int)$arParams['IBLOCK_USER_LINK_PROPERTY']) {
                ShowError(GetMessage("IBLOCK_USER_LINK_PROPERTY_ERROR"));
                return;
            }

            $arParams["PROPERTY_CODES"][] = $arParams['IBLOCK_USER_LINK_INADD_PROPERTY'];

            $arFilter = array('IBLOCK_ID' => (int)$arParams['IBLOCK_USER_LINK_ID'], 'PROPERTY_' . $arParams['IBLOCK_USER_LINK_PROPERTY'] => $USER->GetID());
            $arSelect = array('ID', 'NAME');
            $arSort = array('NAME' => 'ASC');
            $obRes = CIBlockElement::GetList($arSort, $arFilter, false, false, $arSelect);
            $arProperty['ENUM'] = array();
            while ($arRes = $obRes->GetNext()) {
                $arProperty['ENUM'][$arRes['ID']] = $arRes['NAME'];
            }
        }


        // добавляем ID совйства в PROPERTY_LIST,  если свойство имеется в PROPERTY_CODES
        if (in_array($arProperty["ID"], $arParams["PROPERTY_CODES"]) || $arProperty["ID"] == $arParams['IBLOCK_USER_LINK_INADD_PROPERTY']) {
            $arResult["PROPERTY_LIST"][] = $arProperty["ID"];
        }
        $arResult["PROPERTY_LIST_FULL"][$arProperty["ID"]] = $arProperty;

    }


    // в $arResult["PROPERTY_LIST"] - свойства, которые необходимо отобразить
    // в $arResult["PROPERTY_LIST_FULL"] - все свойства текущего инфоблока


    // сортируем в соответствии с индексами сортировки
    $arTmpPropList = array();
    foreach ($arResult["PROPERTY_LIST"] as $i => $item) {
        $arTmpPropList[$item] = $arResult["PROPERTY_LIST_FULL"][$item];
    }
    uasort($arTmpPropList, function ($item1, $item2) {
        if ((int)$item1["SORT"] == (int)$item2["SORT"]) {
            return 0;
        }
        return (int)$item1["SORT"] > (int)$item2["SORT"] ? 1 : -1;
    });
    $arResult['PROPERTY_LIST'] = array_keys($arTmpPropList);

    // обработка POST запроса
    if (check_bitrix_sessid() && (!empty($_REQUEST["iblock_submit"]) || !empty($_REQUEST["iblock_apply"]))) {

        $arProperties = $_REQUEST["PROPERTY"];

        $arUpdateValues = array();
        $arUpdatePropertyValues = array();

        // обрабатываем список свойств

        foreach ($arParams["PROPERTY_CODES"] as $i => $propertyID) {

            $arPropertyValue = $arProperties[$propertyID];

            // проверяем - действительно ли это свойство, или это поле элемента.
            if (intval($propertyID) > 0) {
                // если свойство - не файл
                if ($arResult["PROPERTY_LIST_FULL"][$propertyID]["PROPERTY_TYPE"] != "F") {

                    // если свойство - множественное
                    if ($arResult["PROPERTY_LIST_FULL"][$propertyID]["MULTIPLE"] == "Y") {
                        $arUpdatePropertyValues[$propertyID] = array();
                        if (!is_array($arPropertyValue)) {
                            $arUpdatePropertyValues[$propertyID][] = $arPropertyValue;
                        } else {
                            foreach ($arPropertyValue as $key => $value) {
                                if (
                                    $arResult["PROPERTY_LIST_FULL"][$propertyID]["PROPERTY_TYPE"] == "L" && intval($value) > 0
                                    ||
                                    $arResult["PROPERTY_LIST_FULL"][$propertyID]["PROPERTY_TYPE"] != "L" && !empty($value)
                                ) {
                                    $arUpdatePropertyValues[$propertyID][] = $value;
                                }
                            }
                        }
                    } // если свойство не множественное
                    else {
                        if ($arResult["PROPERTY_LIST_FULL"][$propertyID]["PROPERTY_TYPE"] != "L")
                            // если свойство не список
                            $arUpdatePropertyValues[$propertyID] = $arPropertyValue[0];
                        else
                            // если список
                            $arUpdatePropertyValues[$propertyID] = $arPropertyValue;
                    }
                } // если свойство типа "файл"
                else {
                    $arUpdatePropertyValues[$propertyID] = array();
                    foreach ($arPropertyValue as $key => $value) {

                        if (strlen($value)) {
                            $data = $value;
                            list($type, $data) = explode(';', $data);
                            list(, $data) = explode(',', $data);
                            $data = base64_decode($data);
                            $h = tmpfile();
                            fwrite($h, $data);
                            $file_data = stream_get_meta_data($h);
                            $arFile = CFile::MakeFileArray($file_data['uri']);
                            $arFile['name'] = $_FILES["PROPERTY_FILE_" . $propertyID . "_" . $key]['name'];
                            $fileId = CFile::SaveFile($arFile);
                            $arUpdatePropertyValues[$propertyID][$key] = $fileId;
                        } else {
                            $arFile = $_FILES["PROPERTY_FILE_" . $propertyID . "_" . $key];
                            $arFile["del"] = $_REQUEST["DELETE_FILE"][$propertyID][$key] == "Y" ? "Y" : "";
                            $arUpdatePropertyValues[$propertyID][$key] = $arFile;

                            if (($arParams["MAX_FILE_SIZE"] > 0) && ($arFile["size"] > $arParams["MAX_FILE_SIZE"]))
                                $arResult["ERRORS"][] = GetMessage("IBLOCK_ERROR_FILE_TOO_LARGE");
                        }

                    }

                    if (empty($arUpdatePropertyValues[$propertyID]))
                        unset($arUpdatePropertyValues[$propertyID]);
                }
            } else {
                // для "виртуальных" свойст, являющихся полями элемента
                if ($propertyID == "IBLOCK_SECTION") {  // раздел инфоблока
                    if (!is_array($arProperties[$propertyID]))
                        $arProperties[$propertyID] = array($arProperties[$propertyID]);
                    $arUpdateValues[$propertyID] = $arProperties[$propertyID];

                    // проверяем, если добавление разрешено только на последний уровень
                    if ($arParams["LEVEL_LAST"] == "Y" && is_array($arUpdateValues[$propertyID])) {
                        foreach ($arUpdateValues[$propertyID] as $section_id) {
                            $rsChildren = CIBlockSection::GetList(
                                array("SORT" => "ASC"),
                                array(
                                    "IBLOCK_ID" => $arParams["IBLOCK_ID"],
                                    "SECTION_ID" => $section_id,
                                ),
                                false,
                                array("ID")
                            );
                            if ($rsChildren->SelectedRowsCount() > 0) {
                                $arResult["ERRORS"][] = GetMessage("IBLOCK_ADD_LEVEL_LAST_ERROR");
                                break;
                            }
                        }
                    }

                    if ($arParams["MAX_LEVELS"] > 0 && count($arUpdateValues[$propertyID]) > $arParams["MAX_LEVELS"]) {
                        // проверяем на ограничение количества одновременных разделов у элемента
                        $arResult["ERRORS"][] = str_replace("#MAX_LEVELS#", $arParams["MAX_LEVELS"], GetMessage("IBLOCK_ADD_MAX_LEVELS_EXCEEDED"));
                    }

                } else {
                    // для оствльных "виртуальных" свойств и полей элемента инфоблока

                    if ($arResult["PROPERTY_LIST_FULL"][$propertyID]["PROPERTY_TYPE"] == "F") {
                        // свойство типа "файл"
                        $arFile = $_FILES["PROPERTY_FILE_" . $propertyID . "_0"];
                        $arFile["del"] = $_REQUEST["DELETE_FILE"][$propertyID][0] == "Y" ? "Y" : "";
                        $arUpdateValues[$propertyID] = $arFile;
                        if ($arParams["MAX_FILE_SIZE"] > 0 && $arFile["size"] > $arParams["MAX_FILE_SIZE"])
                            $arResult["ERRORS"][] = GetMessage("IBLOCK_ERROR_FILE_TOO_LARGE");
                    } elseif ($arResult["PROPERTY_LIST_FULL"][$propertyID]["PROPERTY_TYPE"] == "HTML") {
                        // если это DETAIL_TEXT или PREVIEW_TEXT и они HTML
                        if ($propertyID == "DETAIL_TEXT")
                            $arUpdateValues["DETAIL_TEXT_TYPE"] = "html";
                        if ($propertyID == "PREVIEW_TEXT")
                            $arUpdateValues["PREVIEW_TEXT_TYPE"] = "html";
                        $arUpdateValues[$propertyID] = $arProperties[$propertyID][0];
                    } else {
                        // если это DETAIL_TEXT или PREVIEW_TEXT и они text
                        if ($propertyID == "DETAIL_TEXT")
                            $arUpdateValues["DETAIL_TEXT_TYPE"] = "text";
                        if ($propertyID == "PREVIEW_TEXT")
                            $arUpdateValues["PREVIEW_TEXT_TYPE"] = "text";
                        $arUpdateValues[$propertyID] = $arProperties[$propertyID][0];
                    }


                }
            }

        }

        // проверяем обязательные к заполнению свойства
        foreach ($arParams["PROPERTY_CODES_REQUIRED"] as $key => $propertyID) {
            $bError = false;
            $propertyValue = intval($propertyID) > 0 ? $arUpdatePropertyValues[$propertyID] : $arUpdateValues[$propertyID];

            if ($arResult["PROPERTY_LIST_FULL"][$propertyID]["USER_TYPE"] != "")
                $arUserType = CIBlockProperty::GetUserType($arResult["PROPERTY_LIST_FULL"][$propertyID]["USER_TYPE"]);
            else
                $arUserType = array();

            // проверка файла
            if ($arResult["PROPERTY_LIST_FULL"][$propertyID]['PROPERTY_TYPE'] == 'F') {

                $bError = true;
                if (is_array($propertyValue)) {
                    if (array_key_exists("tmp_name", $propertyValue) && array_key_exists("size", $propertyValue)) {
                        if ($propertyValue['size'] > 0) {
                            $bError = false;
                        }
                    } else {
                        foreach ($propertyValue as $arFile) {
                            if ($arFile['size'] > 0) {
                                $bError = false;
                                break;
                            }
                        }
                    }
                }

            } elseif (array_key_exists("GetLength", $arUserType)) {
                $len = 0;
                if (is_array($propertyValue) && !array_key_exists("VALUE", $propertyValue)) {
                    foreach ($propertyValue as $value) {
                        if (is_array($value) && !array_key_exists("VALUE", $value))
                            foreach ($value as $val)
                                $len += call_user_func_array($arUserType["GetLength"], array($arResult["PROPERTY_LIST_FULL"][$propertyID], array("VALUE" => $val)));
                        elseif (is_array($value) && array_key_exists("VALUE", $value))
                            $len += call_user_func_array($arUserType["GetLength"], array($arResult["PROPERTY_LIST_FULL"][$propertyID], $value));
                        else
                            $len += call_user_func_array($arUserType["GetLength"], array($arResult["PROPERTY_LIST_FULL"][$propertyID], array("VALUE" => $value)));
                    }
                } elseif (is_array($propertyValue) && array_key_exists("VALUE", $propertyValue)) {
                    $len += call_user_func_array($arUserType["GetLength"], array($arResult["PROPERTY_LIST_FULL"][$propertyID], $propertyValue));
                } else {
                    $len += call_user_func_array($arUserType["GetLength"], array($arResult["PROPERTY_LIST_FULL"][$propertyID], array("VALUE" => $propertyValue)));
                }

                if ($len <= 0)
                    $bError = true;

            } // множественные свойства и свойства типа "список"
            elseif ($arResult["PROPERTY_LIST_FULL"][$propertyID]["MULTIPLE"] == "Y" || $arResult["PROPERTY_LIST_FULL"][$propertyID]["PROPERTY_TYPE"] == "L") {
                if (is_array($propertyValue)) {
                    $bError = true;
                    foreach ($propertyValue as $value) {
                        if (strlen($value) > 0) {
                            $bError = false;
                            break;
                        }
                    }
                } elseif (strlen($propertyValue) <= 0) {
                    $bError = true;
                }
            } // обычное свойство
            elseif (is_array($propertyValue) && array_key_exists("VALUE", $propertyValue)) {
                if (strlen($propertyValue["VALUE"]) <= 0)
                    $bError = true;
            } elseif (!is_array($propertyValue)) {
                if (strlen($propertyValue) <= 0)
                    $bError = true;
            }

            // если есть ошибка - добавляем в массив ошибок
            if ($bError) {
                $arResult["ERRORS"][] = str_replace(
                    "#PROPERTY_NAME#",
                    intval($propertyID) > 0 ?
                        $arResult["PROPERTY_LIST_FULL"][$propertyID]["NAME"] :
                        (!empty($arParams["CUSTOM_TITLE_" . $propertyID]) ?
                            $arParams["CUSTOM_TITLE_" . $propertyID] :
                            GetMessage("IBLOCK_FIELD_" . $propertyID)),
                    GetMessage("IBLOCK_ADD_ERROR_REQUIRED"));
            }

        }


        // проверяем капчу
        if ($arParams["USE_CAPTCHA"] == "Y" && $arParams["ID"] <= 0) {
            if (!$APPLICATION->CaptchaCheckCode($_REQUEST["captcha_word"], $_REQUEST["captcha_sid"])) {
                $arResult["ERRORS"][] = GetMessage("IBLOCK_FORM_WRONG_CAPTCHA");
            }
        }

        // проверяем соглашение
        if ($arParams["AGREEMENT"]) {
            if ($_POST["AGREEMENT"] !== "Y") {
                $arResult["ERRORS"][] = GetMessage("IBLOCK_ADD_NEED_AGREEMENT");
            }
        }


        if (empty($arResult["ERRORS"])) {
            if ($arParams["ELEMENT_ASSOC"] == "PROPERTY_ID")
                $arUpdatePropertyValues[$arParams["ELEMENT_ASSOC_PROPERTY"]] = $USER->GetID();
            $arUpdateValues["MODIFIED_BY"] = $USER->GetID();

            $arUpdateValues["PROPERTY_VALUES"] = $arUpdatePropertyValues;

            $oElement = new CIBlockElement();

            $arUpdateValues["IBLOCK_ID"] = $arParams["IBLOCK_ID"];

            // получаем DATE_ACTIVE_FROM = текущей дате
            if (strlen($arUpdateValues["DATE_ACTIVE_FROM"]) <= 0) {
                $arUpdateValues["DATE_ACTIVE_FROM"] = ConvertTimeStamp(time() + CTimeZone::GetOffset(), "FULL");
            }

            // Добавляем элемент
            if (!$arParams["ID"] = $oElement->Add($arUpdateValues, false, true, $arParams["RESIZE_IMAGES"])) {
                $arResult["ERRORS"][] = $oElement->LAST_ERROR;
            }
        }
    }

    // подгатавливаем данные для формы
    $arResult["PROPERTY_REQUIRED"] = is_array($arParams["PROPERTY_CODES_REQUIRED"]) ? $arParams["PROPERTY_CODES_REQUIRED"] : array(); // обязательные к заполнению свойства в массив


    // подгатавливаем данные для формы, если есть ошибки
    if (!empty($arResult["ERRORS"])) {

        // запоминаем поля, которые были заполнены
        foreach ($arUpdateValues as $key => $value) {
            if ($key == "IBLOCK_SECTION") {
                $arResult["ELEMENT"][$key] = array();
                if (!is_array($value)) {
                    $arResult["ELEMENT"][$key][] = array("VALUE" => htmlspecialcharsbx($value));
                } else {
                    foreach ($value as $vkey => $vvalue) {
                        $arResult["ELEMENT"][$key][$vkey] = array("VALUE" => htmlspecialcharsbx($vvalue));
                    }
                }
            } elseif ($key == "PROPERTY_VALUES") {
                //Skip
            } elseif ($arResult["PROPERTY_LIST_FULL"][$key]["PROPERTY_TYPE"] == "F") {
                //Skip
            } elseif ($arResult["PROPERTY_LIST_FULL"][$key]["PROPERTY_TYPE"] == "HTML") {
                $arResult["ELEMENT"][$key] = $value;
            } else {
                $arResult["ELEMENT"][$key] = htmlspecialcharsbx($value);
            }
        }

        // запоминаем свойства, которые были заполнены
        foreach ($arUpdatePropertyValues as $key => $value) {
            if ($arResult["PROPERTY_LIST_FULL"][$key]["PROPERTY_TYPE"] != "F") {
                $arResult["ELEMENT_PROPERTIES"][$key] = array();
                if (!is_array($value)) {
                    $value = array(
                        array("VALUE" => $value),
                    );
                }
                foreach ($value as $vv) {
                    if (is_array($vv)) {
                        if (array_key_exists("VALUE", $vv))
                            $arResult["ELEMENT_PROPERTIES"][$key][] = array(
                                "~VALUE" => $vv["VALUE"],
                                "VALUE" => !is_array($vv["VALUE"]) ? htmlspecialcharsbx($vv["VALUE"]) : $vv["VALUE"],
                            );
                        else
                            $arResult["ELEMENT_PROPERTIES"][$key][] = array(
                                "~VALUE" => $vv,
                                "VALUE" => $vv,
                            );
                    } else {
                        $arResult["ELEMENT_PROPERTIES"][$key][] = array(
                            "~VALUE" => $vv,
                            "VALUE" => htmlspecialcharsbx($vv),
                        );
                    }
                }
            }
        }


    }


    // запоминаем код для капчи
    if ($arParams["USE_CAPTCHA"] == "Y" && $arParams["ID"] <= 0) {
        $arResult["CAPTCHA_CODE"] = htmlspecialcharsbx($APPLICATION->CaptchaGetCode());
    }

    $arResult["MESSAGE"] = '';
    if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_REQUEST["strIMessage"]) && is_string($_REQUEST["strIMessage"]))
        $arResult["MESSAGE"] = htmlspecialcharsbx($_REQUEST["strIMessage"]);

    $this->includeComponentTemplate();

}

if (!$bAllowAccess && !$bHideAuth) {
    //echo ShowError(GetMessage("IBLOCK_ADD_ACCESS_DENIED"));
    $APPLICATION->AuthForm("");
}