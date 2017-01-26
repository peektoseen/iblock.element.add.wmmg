<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

if (!CModule::IncludeModule("iblock"))
    return;

$arIBlockType = CIBlockParameters::GetIBlockTypes();

$arIBlock = array();
$rsIBlock = CIBlock::GetList(Array("sort" => "asc"), Array("TYPE" => $arCurrentValues["IBLOCK_TYPE"], "ACTIVE" => "Y"));
while ($arr = $rsIBlock->Fetch()) {
    $arIBlock[$arr["ID"]] = "[" . $arr["ID"] . "] " . $arr["NAME"];
}

$arProperty_LNSF = array(
    "NAME" => GetMessage("IBLOCK_ADD_NAME"),
    "DATE_ACTIVE_FROM" => GetMessage("IBLOCK_ADD_ACTIVE_FROM"),
    "DATE_ACTIVE_TO" => GetMessage("IBLOCK_ADD_ACTIVE_TO"),
    "IBLOCK_SECTION" => GetMessage("IBLOCK_ADD_IBLOCK_SECTION"),
    "PREVIEW_TEXT" => GetMessage("IBLOCK_ADD_PREVIEW_TEXT"),
    "PREVIEW_PICTURE" => GetMessage("IBLOCK_ADD_PREVIEW_PICTURE"),
    "DETAIL_TEXT" => GetMessage("IBLOCK_ADD_DETAIL_TEXT"),
    "DETAIL_PICTURE" => GetMessage("IBLOCK_ADD_DETAIL_PICTURE"),
);
$arVirtualProperties = $arProperty_LNSF;

$rsProp = CIBlockProperty::GetList(Array("sort" => "asc", "name" => "asc"), Array("ACTIVE" => "Y", "IBLOCK_ID" => $arCurrentValues["IBLOCK_ID"]));
while ($arr = $rsProp->Fetch()) {
    $arProperty[$arr["ID"]] = "[" . $arr["CODE"] . "] " . $arr["NAME"];
    if (in_array($arr["PROPERTY_TYPE"], array("L", "N", "S", "F"))) {
        $arProperty_LNSF[$arr["ID"]] = "[" . $arr["CODE"] . "] " . $arr["NAME"];
    }
}

$arGroups = array();
$rsGroups = CGroup::GetList($by = "c_sort", $order = "asc", Array("ACTIVE" => "Y"));
while ($arGroup = $rsGroups->Fetch()) {
    $arGroups[$arGroup["ID"]] = $arGroup["NAME"];
}



$arAllowEdit = array("CREATED_BY" => GetMessage("IBLOCK_CREATED_BY"), "PROPERTY_ID" => GetMessage("IBLOCK_PROPERTY_ID"));


$arUserLinkIBlock = array();
$rsUserLinkIBlock = CIBlock::GetList(Array("sort" => "asc"), Array("TYPE" => $arCurrentValues["IBLOCK_USER_LINK_TYPE"], "ACTIVE" => "Y"));
while ($arr = $rsUserLinkIBlock->Fetch()) {
    $arUserLinkIBlock[$arr["ID"]] = "[" . $arr["ID"] . "] " . $arr["NAME"];
}

$arElementLinkProp = array();
$rsElementLinkProp = CIBlockProperty::GetList(
    array("sort"=>"asc", "name"=>"asc"),
    array("ACTIVE"=>"Y", "IBLOCK_ID"=>$arCurrentValues['IBLOCK_ID'], "PROPERTY_TYPE"=>"E"));
while ($arProp = $rsElementLinkProp->GetNext()) {
    $arElementLinkProp[$arProp["ID"]] = $arProp["NAME"];
}

$arUserLinkProperty = array();
$rsUserLinkProperty = CIBlockProperty::GetList(array(), array('IBLOCK_ID'=>$arCurrentValues['IBLOCK_USER_LINK_ID'], 'USER_TYPE'=> 'UserID'));
while($arProp = $rsUserLinkProperty->GetNext()){
    $arUserLinkProperty[$arProp["ID"]] = $arProp['NAME'];
}

$arComponentParameters = array(
    "GROUPS" => array(
        "PARAMS" => array(
            "NAME" => GetMessage("IBLOCK_PARAMS"),
            "SORT" => "200"
        ),
        "ACCESS" => array(
            "NAME" => GetMessage("IBLOCK_ACCESS"),
            "SORT" => "400",
        ),
        "FIELDS" => array(
            "NAME" => GetMessage("IBLOCK_FIELDS"),
            "SORT" => "300",
        ),
        "TITLES" => array(
            "NAME" => GetMessage("IBLOCK_TITLES"),
            "SORT" => "1000",
        ),
        "SORT" => array(
            "NAME" => "Сортировка полей",
            "SORT" => "1000",
        ),
        "USER_LINK" => array(
            //todo: вынести в lang
            "NAME" => "Привязка к пользователю",
            "SORT" => "2000",
        ),
    ),
    "PARAMETERS" => array(
        "IBLOCK_TYPE" => array(
            "PARENT" => "DATA_SOURCE",
            "NAME" => GetMessage("IBLOCK_TYPE"),
            "TYPE" => "LIST",
            "ADDITIONAL_VALUES" => "Y",
            "VALUES" => $arIBlockType,
            "REFRESH" => "Y",
        ),
        "IBLOCK_ID" => array(
            "PARENT" => "DATA_SOURCE",
            "NAME" => GetMessage("IBLOCK_IBLOCK"),
            "TYPE" => "LIST",
            "ADDITIONAL_VALUES" => "Y",
            "VALUES" => $arIBlock,
            "REFRESH" => "Y",
        ),
        "IBLOCK_USER_LINK_TYPE" => array(
            "PARENT" => "USER_LINK",
            //todo: в LANG
            "NAME" => 'Тип инфоблока для свойства с привзякой к пользователю',
            "TYPE" => "LIST",
            "ADDITIONAL_VALUES" => "Y",
            "VALUES" => $arIBlockType,
            "REFRESH" => "Y",
        ),
        "IBLOCK_USER_LINK_ID" => array(
            "PARENT" => "USER_LINK",
            //todo: в LANG
            "NAME" => "ID инфоблока для привязки к пользователю",
            "TYPE" => "LIST",
            "ADDITIONAL_VALUES" => "Y",
            "VALUES" => $arUserLinkIBlock,
            "REFRESH" => "Y"
        ),
        "IBLOCK_USER_LINK_PROPERTY" => array(
            "PARENT" => "USER_LINK",
            //todo: в LANG
            "NAME" => "Свойство - привязка к пользователю в связанном инфоблоке",
            "TYPE" => "LIST",
            "ADDITIONAL_VALUES" => "Y",
            "VALUES" => $arUserLinkProperty,
        ),
        "IBLOCK_USER_LINK_INADD_PROPERTY" => array(
            "PARENT" => "USER_LINK",
            //todo: LANG
            "NAME" => "Свойство добавлеяемого элемента, в котором будет храниться id привязанного по пользователю элемента (Тип свойства -\"Привязка к элементу\")",
            "TYPE" => "LIST",
            "ADDITIONAL_VALUES" => "N",
            "VALUES" => $arElementLinkProp,
        ),
        "PROPERTY_CODES" => array(
            "PARENT" => "FIELDS",
            "NAME" => GetMessage("IBLOCK_PROPERTY"),
            "TYPE" => "LIST",
            "MULTIPLE" => "Y",
            "VALUES" => $arProperty_LNSF,
        ),

        "PROPERTY_CODES_REQUIRED" => array(
            "PARENT" => "FIELDS",
            "NAME" => GetMessage("IBLOCK_PROPERTY_REQUIRED"),
            "TYPE" => "LIST",
            "MULTIPLE" => "Y",
            "ADDITIONAL_VALUES" => "N",
            "VALUES" => $arProperty_LNSF,
        ),

        "GROUPS" => array(
            "PARENT" => "ACCESS",
            "NAME" => GetMessage("IBLOCK_GROUPS"),
            "TYPE" => "LIST",
            "MULTIPLE" => "Y",
            "ADDITIONAL_VALUES" => "N",
            "VALUES" => $arGroups,
        ),
        "AGREEMENT" => array(
            "PARENT" => "PARAMS",
            "NAME" => GetMessage("IBLOCK_ADD_SHOW_AGREEMENT"),
            "TYPE" => "CHECKBOX",
            "DEFAULT" => "Y"
        ),
        "AGREEMENT_URL" => array(
            "PARENT" => "PARAMS",
            "NAME" => GetMessage("IBLOCK_ADD_AGREEMENT_URL"),
            "TYPE" => "TEXT",
            "DEFAULT" => ""
        )
    ),
);


$arComponentParameters["PARAMETERS"]["MAX_LEVELS"] = array(
    "PARENT" => "ACCESS",
    "NAME" => GetMessage("IBLOCK_MAX_LEVELS"),
    "TYPE" => "TEXT",
    "DEFAULT" => "100000",
);

$arComponentParameters["PARAMETERS"]["LEVEL_LAST"] = array(
    "PARENT" => "ACCESS",
    "NAME" => GetMessage("IBLOCK_LEVEL_LAST"),
    "TYPE" => "CHECKBOX",
    "DEFAULT" => "N",
);

$arComponentParameters["PARAMETERS"]["USE_CAPTCHA"] = array(
    "PARENT" => "PARAMS",
    "NAME" => GetMessage("IBLOCK_USE_CAPTCHA"),
    "TYPE" => "CHECKBOX",
    "DEFAULT" => "N",
);


$arComponentParameters["PARAMETERS"]["USE_JQUERY"] = array(
    "PARENT" => "PARAMS",
    "NAME" => GetMessage("IBLOCK_USE_JQUERY"),
    "TYPE" => "CHECKBOX",
    "DEFAULT" => "N",
);

$arComponentParameters["PARAMETERS"]["USER_MESSAGE_ADD"] = array(
    "PARENT" => "PARAMS",
    "NAME" => GetMessage("IBLOCK_USER_MESSAGE_ADD"),
    "TYPE" => "TEXT",
);

$arComponentParameters["PARAMETERS"]["DEFAULT_INPUT_SIZE"] = array(
    "PARENT" => "PARAMS",
    "NAME" => GetMessage("IBLOCK_DEFAULT_INPUT_SIZE"),
    "TYPE" => "TEXT",
    "DEFAULT" => 500,
);

$arComponentParameters["PARAMETERS"]["PREVIEW_TEXT_INPUT_SIZE"] = array(
    "PARENT" => "PARAMS",
    "NAME" => GetMessage("IBLOCK_PREVIEW_TEXT_INPUT_SIZE"),
    "TYPE" => "TEXT",
    "DEFAULT" => 500,
);

$arComponentParameters["PARAMETERS"]["NAME_INPUT_SIZE"] = array(
    "PARENT" => "PARAMS",
    "NAME" => GetMessage("IBLOCK_NAME_INPUT_SIZE"),
    "TYPE" => "TEXT",
    "DEFAULT" => 500,
);

$arComponentParameters["PARAMETERS"]["RESIZE_IMAGES"] = array(
    "PARENT" => "PARAMS",
    "NAME" => GetMessage("CP_BIEAF_RESIZE_IMAGES"),
    "TYPE" => "CHECKBOX",
    "DEFAULT" => "N",
);

$arComponentParameters["PARAMETERS"]["MAX_FILE_SIZE"] = array(
    "PARENT" => "ACCESS",
    "NAME" => GetMessage("IBLOCK_MAX_FILE_SIZE"),
    "TYPE" => "TEXT",
    "DEFAULT" => "0",
);

foreach ($arVirtualProperties as $key => $title) {
    $arComponentParameters["PARAMETERS"]["CUSTOM_TITLE_" . $key] = array(
        "PARENT" => "TITLES",
        "NAME" => $title,
        "TYPE" => "STRING",
    );

    $arComponentParameters["PARAMETERS"]["CUSTOM_SORT_" . $key] = array(
        "PARENT" => "SORT",
        "NAME" => 'Индекс сортировки для ' . $title,
        "TYPE" => "STRING",
    );

}

?>