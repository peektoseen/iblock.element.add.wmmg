<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Page\Asset;


require_once($_SERVER['DOCUMENT_ROOT'] . $templateFolder . '/CIBlockPropertyDateTime_.php');
CIBlockPropertyDateTime_::$component = $component;

$this->setFrameMode(false);

$asset = Asset::getInstance();

$asset->addJs($templateFolder . '/assets/js/bootstrap.min.js', true);
//$asset->addJs($templateFolder . '/assets/js/darkroom.js', true);

$asset->addCss($templateFolder . '/assets/css/bootstrap.min.css');
$asset->addCss($templateFolder . '/assets/css/darkroom.css');
?>
<script type="text/javascript" src="<?=$templateFolder . '/assets/js/fabric.js'?>"></script>
<script type="text/javascript" src="<?=$templateFolder . '/assets/js/darkroom.js'?>"></script>
<?

if ((int)$arParams["ID"] > 0):?>
    <? ShowNote($arParams["USER_MESSAGE_ADD"]);
    return; ?>
<? endif ?>


<div class="body_obr" id="<?= $this->GetEditAreaID('form') ?>">
    <? if (!empty($arResult["ERRORS"])): ?>
        <? ShowError(implode("<br />", $arResult["ERRORS"])) ?>
    <?endif;
    if (strlen($arResult["MESSAGE"]) > 0):?>
        <? ShowNote($arResult["MESSAGE"]) ?>
    <? endif ?>

    <form name="iblock_add" action="<?= POST_FORM_ACTION_URI ?>" method="post" enctype="multipart/form-data">
        <?= bitrix_sessid_post() ?>

        <? if ($arParams["MAX_FILE_SIZE"] > 0): ?>
            <input type="hidden" name="MAX_FILE_SIZE" value="<?= $arParams["MAX_FILE_SIZE"] ?>"/>
        <? endif ?>
        <? if (is_array($arResult["PROPERTY_LIST"]) && !empty($arResult["PROPERTY_LIST"])): ?>

            <? foreach ($arResult["PROPERTY_LIST"] as $id => $propertyID): ?>

                <div class="line_row">
                    <div class="line_row_header">
                        <h2 class="h2_s">

                            <? if (intval($propertyID) > 0) {
                                $title = $arResult["PROPERTY_LIST_FULL"][$propertyID]["NAME"];
                            } else {
                                $title = !empty($arParams["CUSTOM_TITLE_" . $propertyID]) ?
                                    $arParams["CUSTOM_TITLE_" . $propertyID] : GetMessage("IBLOCK_FIELD_" . $propertyID);
                            }
                            echo $title; ?>

                            <? if (in_array($propertyID, $arResult["PROPERTY_REQUIRED"])): ?>
                                <span class="starrequired">*</span>
                            <? endif ?>
                            <? if (strlen($arResult["PROPERTY_LIST_FULL"][$propertyID]["HINT"])): ?>
                                <img alt="<?= $arResult["PROPERTY_LIST_FULL"][$propertyID]["HINT"] ?>"
                                     title="<?= $arResult["PROPERTY_LIST_FULL"][$propertyID]["HINT"] ?>"
                                     class="cp"
                                     src="<?= $templateFolder ?>/img/icins_06.png">
                            <? endif ?>
                            <? //todo: показывать иконку вопроса?>
                            <!--                            <img class="cp" src="-->
                            <? //= $templateFolder . '/img/icins_06.png' ?><!--">-->
                        </h2>
                    </div>
                </div>

                <?


                //для полей типа "Текст" и размером =1 строка -  устанавливаем тип = "S" (строковый)
                if (
                    $arResult["PROPERTY_LIST_FULL"][$propertyID]["PROPERTY_TYPE"] == "T"
                    &&
                    $arResult["PROPERTY_LIST_FULL"][$propertyID]["ROW_COUNT"] == "1"
                )
                    $arResult["PROPERTY_LIST_FULL"][$propertyID]["PROPERTY_TYPE"] = "S";
                elseif (
                    (
                        $arResult["PROPERTY_LIST_FULL"][$propertyID]["PROPERTY_TYPE"] == "S"
                        ||
                        $arResult["PROPERTY_LIST_FULL"][$propertyID]["PROPERTY_TYPE"] == "N"
                    )
                    &&
                    $arResult["PROPERTY_LIST_FULL"][$propertyID]["ROW_COUNT"] > "1"
                )
                    $arResult["PROPERTY_LIST_FULL"][$propertyID]["PROPERTY_TYPE"] = "T";


                if ($arResult["PROPERTY_LIST_FULL"][$propertyID]["MULTIPLE"] == "Y") {
                    $inputNum = ($arParams["ID"] > 0 || count($arResult["ERRORS"]) > 0) ? count($arResult["ELEMENT_PROPERTIES"][$propertyID]) : 0;
                    $inputNum += $arResult["PROPERTY_LIST_FULL"][$propertyID]["MULTIPLE_CNT"];
                } else {
                    $inputNum = 1;
                }


                if ($arResult["PROPERTY_LIST_FULL"][$propertyID]["GetPublicEditHTML"])
                    $INPUT_TYPE = "USER_TYPE";
                else
                    $INPUT_TYPE = $arResult["PROPERTY_LIST_FULL"][$propertyID]["PROPERTY_TYPE"];


                switch ($INPUT_TYPE):
                    case "USER_LINKED":
                        ?>
                        <div class="line_row js-radiobutton <? if (!empty($arResult["PROPERTY_LIST_FULL"][$propertyID]['CODE'])): ?>property_<?= $arResult["PROPERTY_LIST_FULL"][$propertyID]['CODE'] ?><? else: ?>property_<?= $propertyID ?><? endif ?>">
                            <div class="line_dashed">
                                <? $first = true; ?>
                                <? foreach ($arResult["PROPERTY_LIST_FULL"][$propertyID]["ENUM"] as $id => $name): ?>
                                    <label class="custom-radio" for="property_<?= $propertyID ?>">
                                        <input name="PROPERTY[<?= $propertyID ?>][]"
                                               value="<?= $id ?>" type="radio" <? if ($first): ?>checked<? endif ?>>
                                        <div></div>
                                        <?= $name ?>
                                    </label>
                                    <div class="cleard"></div><br/>
                                    <? $first = false; ?>
                                <? endforeach ?>
                            </div> <!--<div class="line_dashed">-->
                        </div> <!--<div class="line_row">-->
                        <?
                        break;
                    case "USER_TYPE":


                        for ($i = 0; $i < $inputNum; $i++) {
                            if ($arParams["ID"] > 0 || count($arResult["ERRORS"]) > 0) {
                                $value = intval($propertyID) > 0 ? $arResult["ELEMENT_PROPERTIES"][$propertyID][$i]["~VALUE"] : $arResult["ELEMENT"][$propertyID];
                                $description = intval($propertyID) > 0 ? $arResult["ELEMENT_PROPERTIES"][$propertyID][$i]["DESCRIPTION"] : "";
                            } elseif ($i == 0) {
                                $value = intval($propertyID) <= 0 ? "" : $arResult["PROPERTY_LIST_FULL"][$propertyID]["DEFAULT_VALUE"];
                                $description = "";
                            } else {
                                $value = "";
                                $description = "";
                            }

                            // кастомная обработка свойтсва типа "Дата/Время"
                            if ($arResult["PROPERTY_LIST_FULL"][$propertyID]["GetPublicEditHTML"] == array(
                                    0 => 'CIBlockPropertyDateTime',
                                    1 => 'GetPublicEditHTML',
                                )
                            ) {
                                $arResult["PROPERTY_LIST_FULL"][$propertyID]["GetPublicEditHTML"] = array(
                                    'CIBlockPropertyDateTime_', 'GetPublicEditHTML');
                            }


                            echo call_user_func_array($arResult["PROPERTY_LIST_FULL"][$propertyID]["GetPublicEditHTML"],
                                array(
                                    $arResult["PROPERTY_LIST_FULL"][$propertyID],
                                    array(
                                        "VALUE" => $value,
                                        "DESCRIPTION" => $description,
                                    ),
                                    array(
                                        "VALUE" => "PROPERTY[" . $propertyID . "][" . $i . "][VALUE]",
                                        "DESCRIPTION" => "PROPERTY[" . $propertyID . "][" . $i . "][DESCRIPTION]",
                                        "FORM_NAME" => "iblock_add",
                                    ),
                                ));
                            ?><br/><?
                        }
                        break;
                    case "HTML":
                        $LHE = new CHTMLEditor;
                        $LHE->Show(array(
                            'name' => "PROPERTY[" . $propertyID . "][0]",
                            'id' => preg_replace("/[^a-z0-9]/i", '', "PROPERTY[" . $propertyID . "][0]"),
                            'inputName' => "PROPERTY[" . $propertyID . "][0]",
                            'content' => $arResult["ELEMENT"][$propertyID],
                            'width' => '100%',
                            'minBodyWidth' => 350,
                            'normalBodyWidth' => 555,
                            'height' => '200',
                            'bAllowPhp' => false,
                            'limitPhpAccess' => false,
                            'autoResize' => true,
                            'autoResizeOffset' => 40,
                            'useFileDialogs' => false,
                            'saveOnBlur' => true,
                            'showTaskbars' => false,
                            'showNodeNavi' => false,
                            'askBeforeUnloadPage' => true,
                            'bbCode' => false,
                            'siteId' => SITE_ID,
                            'controlsMap' => array(
                                array('id' => 'Bold', 'compact' => true, 'sort' => 80),
                                array('id' => 'Italic', 'compact' => true, 'sort' => 90),
                                array('id' => 'Underline', 'compact' => true, 'sort' => 100),
                                array('id' => 'Strikeout', 'compact' => true, 'sort' => 110),
                                array('id' => 'RemoveFormat', 'compact' => true, 'sort' => 120),
                                array('id' => 'Color', 'compact' => true, 'sort' => 130),
                                array('id' => 'FontSelector', 'compact' => false, 'sort' => 135),
                                array('id' => 'FontSize', 'compact' => false, 'sort' => 140),
                                array('separator' => true, 'compact' => false, 'sort' => 145),
                                array('id' => 'OrderedList', 'compact' => true, 'sort' => 150),
                                array('id' => 'UnorderedList', 'compact' => true, 'sort' => 160),
                                array('id' => 'AlignList', 'compact' => false, 'sort' => 190),
                                array('separator' => true, 'compact' => false, 'sort' => 200),
                                array('id' => 'InsertLink', 'compact' => true, 'sort' => 210),
                                array('id' => 'InsertImage', 'compact' => false, 'sort' => 220),
                                array('id' => 'InsertVideo', 'compact' => true, 'sort' => 230),
                                array('id' => 'InsertTable', 'compact' => false, 'sort' => 250),
                                array('separator' => true, 'compact' => false, 'sort' => 290),
                                array('id' => 'Fullscreen', 'compact' => false, 'sort' => 310),
                                array('id' => 'More', 'compact' => true, 'sort' => 400)
                            ),
                        ));
                        break;
                    case "T":
                        for ($i = 0; $i < $inputNum; $i++) {

                            if ($arParams["ID"] > 0 || count($arResult["ERRORS"]) > 0) {
                                $value = intval($propertyID) > 0 ? $arResult["ELEMENT_PROPERTIES"][$propertyID][$i]["VALUE"] : $arResult["ELEMENT"][$propertyID];
                            } elseif ($i == 0) {
                                $value = intval($propertyID) > 0 ? "" : $arResult["PROPERTY_LIST_FULL"][$propertyID]["DEFAULT_VALUE"];
                            } else {
                                $value = "";
                            }
                            ?>

                            <div class="line_row <? if (!empty($arResult["PROPERTY_LIST_FULL"][$propertyID]['CODE'])): ?>property_<?= $arResult["PROPERTY_LIST_FULL"][$propertyID]['CODE'] ?><? else: ?>property_<?= $propertyID ?><? endif ?>">
                                <textarea class="w100"
                                          cols="<?= $arResult["PROPERTY_LIST_FULL"][$propertyID]["COL_COUNT"] ?>"
                                          data-cols="<?= $arResult["PROPERTY_LIST_FULL"][$propertyID]["COL_COUNT"] ?>"
                                          rows="<?= $arResult["PROPERTY_LIST_FULL"][$propertyID]["ROW_COUNT"] ?>"
                                          name="PROPERTY[<?= $propertyID ?>][<?= $i ?>]"
                                          placeholder="Введите текст обращения"
                                ><?= $value ?></textarea>
                                <div class="line_row_input_ann">
                                    <div class="line_row_w50">
                                        МАКСИМАЛЬНАЯ ДЛИНА
                                        - <?= $arResult["PROPERTY_LIST_FULL"][$propertyID]["COL_COUNT"] ?> символов
                                    </div>
                                    <div class="line_row_w50">
                                        ВВЕДЕНО - <span class="js-counter">0</span> символов
                                        <progress max="100" value="1"></progress>
                                    </div>
                                </div>
                            </div>


                            <?
                        }
                        break;

                    case "S":
                    case "N":
                        for ($i = 0; $i < $inputNum; $i++) {
                            if ($arParams["ID"] > 0 || count($arResult["ERRORS"]) > 0) {
                                $value = intval($propertyID) > 0 ? $arResult["ELEMENT_PROPERTIES"][$propertyID][$i]["VALUE"] : $arResult["ELEMENT"][$propertyID];
                            } elseif ($i == 0) {
                                $value = intval($propertyID) <= 0 ? "" : $arResult["PROPERTY_LIST_FULL"][$propertyID]["DEFAULT_VALUE"];

                            } else {
                                $value = "";
                            }


                            ?>

                            <div class="line_row <? if (!empty($arResult["PROPERTY_LIST_FULL"][$propertyID]['CODE'])): ?>property_<?= $arResult["PROPERTY_LIST_FULL"][$propertyID]['CODE'] ?><? else: ?>property_<?= $propertyID ?><? endif ?>">
                                <? //todo: вынести в lang файл?>
                                <input type="text" name="PROPERTY[<?= $propertyID ?>][<?= $i ?>]"
                                       data-cols="<?= $arResult["PROPERTY_LIST_FULL"][$propertyID]["COL_COUNT"] ?>"
                                       class="w100 header_obr" size="25" placeholder="Введите <?= $title ?>"
                                       value="<?= $value ?>">
                                <? //todo: указывать максимальную длинну. и высчитывать сколько символов введено?>
                                <div class="line_row_input_ann">
                                    <div class="line_row_w50">
                                        МАКСИМАЛЬНАЯ ДЛИНА
                                        - <?= $arResult["PROPERTY_LIST_FULL"][$propertyID]["COL_COUNT"] ?> символов
                                    </div>
                                    <div class="line_row_w50">
                                        ВВЕДЕНО - <span class="js-counter">0</span> символов
                                        <progress max="100" value="1"></progress>
                                    </div>
                                </div>
                            </div>

                            <?


                            if ($arResult["PROPERTY_LIST_FULL"][$propertyID]["USER_TYPE"] == "DateTime"): ?><?
                                $APPLICATION->IncludeComponent(
                                    'bitrix:main.calendar',
                                    '',
                                    array(
                                        'FORM_NAME' => 'iblock_add',
                                        'INPUT_NAME' => "PROPERTY[" . $propertyID . "][" . $i . "]",
                                        'INPUT_VALUE' => $value,
                                    ),
                                    null,
                                    array('HIDE_ICONS' => 'Y')
                                );
                                ?>
                                <small><?= GetMessage("IBLOCK_FORM_DATE_FORMAT") ?><?= FORMAT_DATETIME ?></small><?
                            endif;
                        }
                        break;

                    case "F":
                        ?>
                        <div class="line_row <? if (!empty($arResult["PROPERTY_LIST_FULL"][$propertyID]['CODE'])): ?>property_<?= $arResult["PROPERTY_LIST_FULL"][$propertyID]['CODE'] ?><? else: ?>property_<?= $propertyID ?><? endif ?>">
                            <div class="line_border">
                                <img align="absmiddle" alt="" src="<?= $templateFolder . '/img/icins_19.png' ?>">ДОБАВИТЬ
                                <?= strtoupper($arResult["PROPERTY_LIST_FULL"][$propertyID]['NAME']) ?>
                            </div>
                        </div>

                        <div class="line_row <? if (!empty($arResult["PROPERTY_LIST_FULL"][$propertyID]['CODE'])): ?>property_<?= $arResult["PROPERTY_LIST_FULL"][$propertyID]['CODE'] ?><? else: ?>property_<?= $propertyID ?><? endif ?>">

                            <?

                            for ($i = 0; $i < $inputNum; $i++) {
                                $value = intval($propertyID) > 0 ? $arResult["ELEMENT_PROPERTIES"][$propertyID][$i]["VALUE"] : $arResult["ELEMENT"][$propertyID];
                                ?>
                                <div class="media_border js-file-input">
                                    <img alt="" class="uploaded" src="<?= $templateFolder . '/img/upload_09.jpg' ?>"
                                         data-src="<?= $templateFolder . '/img/upload_09.jpg' ?>">

                                    <div class="media_del js-del" style="display: none">
                                        <img alt="" src="<?= $templateFolder . '/img/icins_14.png' ?>">
                                    </div>


                                    <input type="hidden" id="<?=$this->GetEditAreaID('file_input'.$i.'_hidden')?>"
                                           name="PROPERTY[<?= $propertyID ?>][<?= $arResult["ELEMENT_PROPERTIES"][$propertyID][$i]["VALUE_ID"] ? $arResult["ELEMENT_PROPERTIES"][$propertyID][$i]["VALUE_ID"] : $i ?>]"
                                           value="<?= $value ?>"/>
                                    <input type="file" style="display: none;" id="<?=$this->GetEditAreaID('file_input'.$i)?>"
                                           size="<?= $arResult["PROPERTY_LIST_FULL"][$propertyID]["COL_COUNT"] ?>"
                                           name="PROPERTY_FILE_<?= $propertyID ?>_<?= $arResult["ELEMENT_PROPERTIES"][$propertyID][$i]["VALUE_ID"] ? $arResult["ELEMENT_PROPERTIES"][$propertyID][$i]["VALUE_ID"] : $i ?>"/>

                                </div>
                                <?
                            }
                            ?>
                        </div> <!--<div class="line_row">-->
                        <?
                        break;
                    case "L":

                        if ($arResult["PROPERTY_LIST_FULL"][$propertyID]["LIST_TYPE"] == "C")
                            $type = $arResult["PROPERTY_LIST_FULL"][$propertyID]["MULTIPLE"] == "Y" ? "checkbox" : "radio";
                        else
                            $type = $arResult["PROPERTY_LIST_FULL"][$propertyID]["MULTIPLE"] == "Y" ? "multiselect" : "dropdown";

                        switch ($type):
                            case "checkbox":
                            case "radio":
                                foreach ($arResult["PROPERTY_LIST_FULL"][$propertyID]["ENUM"] as $key => $arEnum) {
                                    $checked = false;
                                    if ($arParams["ID"] > 0 || count($arResult["ERRORS"]) > 0) {
                                        if (is_array($arResult["ELEMENT_PROPERTIES"][$propertyID])) {
                                            foreach ($arResult["ELEMENT_PROPERTIES"][$propertyID] as $arElEnum) {
                                                if ($arElEnum["VALUE"] == $key) {
                                                    $checked = true;
                                                    break;
                                                }
                                            }
                                        }
                                    } else {
                                        if ($arEnum["DEF"] == "Y") $checked = true;
                                    }

                                    ?>
                                    <input type="<?= $type ?>"
                                           name="PROPERTY[<?= $propertyID ?>]<?= $type == "checkbox" ? "[" . $key . "]" : "" ?>"
                                           value="<?= $key ?>"
                                           id="property_<?= $key ?>"<?= $checked ? " checked=\"checked\"" : "" ?> />
                                    <label for="property_<?= $key ?>"><?= $arEnum["VALUE"] ?></label><br/>
                                    <?
                                }
                                break;

                            case "dropdown":
                            case "multiselect":

                                if ($propertyID == 'IBLOCK_SECTION'):?>
                                    <div class="line_row">

                                        <div class="line_row_w50">
                                            <div class="line_row_header">
                                                <h2 class="h2_s">
                                                    Категория <img alt="" class="cp"
                                                                   src="<?= $templateFolder . '/img/icins_06.png' ?>">
                                                </h2><select name="PROPERTY[<?= $propertyID ?>][]" class="js-section">
                                                    <option value="0" selected>
                                                        Не установлено
                                                    </option>
                                                    <? foreach ($arResult['SECTION_LIST'] as $section_id => $arSection): ?>
                                                        <option value="<?= $section_id ?>">
                                                            <?= $arSection['VALUE'] ?>
                                                        </option>
                                                    <? endforeach; ?>
                                                </select>
                                            </div>
                                        </div>


                                        <div class="line_row_w50 custom_01">
                                            <div class="line_row_header">
                                                <h2 class="h2_s">
                                                    Подкатегория <img alt="" class="cp"
                                                                      src="<?= $templateFolder . '/img/icins_06.png' ?>">
                                                </h2><select name="PROPERTY[<?= $propertyID ?>][]"
                                                             class="js-subsection">
                                                    <option value="0">
                                                        Не установлено
                                                    </option>
                                                </select>
                                            </div>
                                        </div>

                                    </div>
                                    <?
                                else: ?>
                                    <select name="PROPERTY[<?= $propertyID ?>]<?= $type == "multiselect" ? "[]\" size=\"" . $arResult["PROPERTY_LIST_FULL"][$propertyID]["ROW_COUNT"] . "\" multiple=\"multiple" : "" ?>">
                                        <option value=""><? echo GetMessage("CT_BIEAF_PROPERTY_VALUE_NA") ?></option>
                                        <?
                                        if (intval($propertyID) > 0) $sKey = "ELEMENT_PROPERTIES";
                                        else $sKey = "ELEMENT";

                                        foreach ($arResult["PROPERTY_LIST_FULL"][$propertyID]["ENUM"] as $key => $arEnum) {
                                            $checked = false;
                                            if ($arParams["ID"] > 0 || count($arResult["ERRORS"]) > 0) {
                                                foreach ($arResult[$sKey][$propertyID] as $elKey => $arElEnum) {
                                                    if ($key == $arElEnum["VALUE"]) {
                                                        $checked = true;
                                                        break;
                                                    }
                                                }
                                            } else {
                                                if ($arEnum["DEF"] == "Y") $checked = true;
                                            }
                                            ?>
                                            <option value="<?= $key ?>" <?= $checked ? " selected=\"selected\"" : "" ?>><?= $arEnum["VALUE"] ?></option>
                                            <?
                                        }
                                        ?>
                                    </select>
                                <?endif;
                                break;

                        endswitch;
                        break;
                endswitch;

                ?>


            <? endforeach ?>
        <? endif ?>


        <div class="line_row">
            <div class="line_border">
                <img align="absmiddle" alt="" src="<?= $templateFolder . '/img/icins_19.png' ?>">ДОБАВИТЬ ВИДЕО
            </div>
        </div>
        <div class="line_row">
            <div class="media_border">
                <img alt="" class="uploaded" src="<?= $templateFolder . '/img/vetton.jpg' ?>">
                <div class="media_video">
                    <img alt="" src="<?= $templateFolder . '/img/icins_11.png' ?>">
                </div>
                <div class="media_del">
                    <img alt="" src="<?= $templateFolder . '/img/icins_14.png' ?>">
                </div>
            </div>
            <div class="media_border">
                <img alt="" src="<?= $templateFolder . '/img/upload_05.jpg' ?>">
                <div class="media_del">
                    <img alt="" src="<?= $templateFolder . '/img/icins_14.png' ?>">
                </div>
            </div>
            <div class="media_border">
                <img alt="" src="<?= $templateFolder . '/img/upload_05.jpg' ?>">
                <div class="media_del">
                    <img alt="" src="<?= $templateFolder . '/img/icins_14.png' ?>">
                </div>
            </div>
        </div>


        <? if ($arParams["USE_CAPTCHA"] == "Y" && $arParams["ID"] <= 0): ?>

            <div class="line_row">
                <div class="line_row_header">
                    <h2 class="h2_s">
                        <?= GetMessage("IBLOCK_FORM_CAPTCHA_TITLE") ?>
                        <span class="starrequired">*</span>
                    </h2>
                </div>
            </div>

            <div class="line_row" style="margin-bottom: 33px;">
                <input type="hidden" name="captcha_sid" value="<?= $arResult["CAPTCHA_CODE"] ?>"/><br>
                <img src="/bitrix/tools/captcha.php?captcha_sid=<?= $arResult["CAPTCHA_CODE"] ?>"
                     width="180" height="40" alt="CAPTCHA" style="margin-bottom: 10px"/><br/>

                <input type="text" name="captcha_word" maxlength="50" value=""><br>
            </div>


        <? endif ?>

        <div class="line_row">
            <input class="epic_big_btn" name="iblock_submit" type="submit"
                   <? if ($arParams['AGREEMENT']): ?>disabled="disabled"<? endif ?>
                   value="<?= GetMessage("IBLOCK_FORM_SUBMIT") ?>">
        </div>


        <? if ($arParams['AGREEMENT']): ?>
            <div class="line_row text-center">
                <label class="custom-check">
                    <input id="id_orders" name="AGREEMENT" type="checkbox" value="Y">
                    <div></div>
                    Я согласен с <a href="<?= $arParams["AGREEMENT_URL"] ?>" target="_blank">Правилами
                        сайта</a></label>
            </div>
        <? endif ?>
    </form>

</div>


<? // добавление изображения ?>
<!-- Trigger the modal with a button -->

<!-- Modal -->
<div id="myModal" class="modal fade" role="dialog">
    <div class="modal-dialog">

        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Редактор изображений</h4>
            </div>
            <div class="modal-body">
                <img src="">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Принять</button>
            </div>
        </div>

    </div>
</div>


<script type="text/javascript">
    $(function () {
        $('#<?=$this->GetEditAreaID('form')?>').FormAdd(
            {component: "<?=$component->getName()?>"}
        );
    })
</script>

