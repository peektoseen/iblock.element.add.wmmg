<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die(); ?>
<?
if ($arParams['SILENT'] == 'Y') return;

$cnt = strlen($arParams['INPUT_NAME_FINISH']) > 0 ? 2 : 1;

for ($i = 0; $i < $cnt; $i++):
    if ($arParams['SHOW_INPUT'] == 'Y'):
        ?><input type="hidden" id="<?= $arParams['INPUT_NAME' . ($i == 1 ? '_FINISH' : '')] ?>"
                 name="<?= $arParams['INPUT_NAME' . ($i == 1 ? '_FINISH' : '')] ?>"
                 value="<?= $arParams['INPUT_VALUE' . ($i == 1 ? '_FINISH' : '')] ?>" <?= (Array_Key_Exists("~INPUT_ADDITIONAL_ATTR", $arParams)) ? $arParams["~INPUT_ADDITIONAL_ATTR"] : "" ?>/><?
    endif;
    ?>

    <div class="line_row_w33 fr" onclick="BX.calendar({node:this, field:'<?= htmlspecialcharsbx(CUtil::JSEscape($arParams['INPUT_NAME' . ($i == 1 ? '_FINISH' : '')])) ?>', form: '<? if ($arParams['FORM_NAME'] != '') {
        echo htmlspecialcharsbx(CUtil::JSEscape($arParams['FORM_NAME']));
    } ?>', bTime: <?= $arParams['SHOW_TIME'] == 'Y' ? 'true' : 'false' ?>, currentTime: '<?= (time() + date("Z") + CTimeZone::GetOffset()) ?>', bHideTime: <?= $arParams['HIDE_TIMEBAR'] == 'Y' ? 'true' : 'false' ?>});"
         onmouseover="BX.addClass(this, 'calendar-icon-hover');"
         onmouseout="BX.removeClass(this, 'calendar-icon-hover');">
        <div class="tabs_type2 tabs_type_l">
            Время <img alt="" src="<?= $templateFolder ?>/images/icins_04.png">
        </div>
        <div class="tabs_type2 tabs_type_f">
            Дата <img alt="" src="<?= $templateFolder ?>/images/icins_02.png">
        </div>
        <div class="clearb"></div>
    </div><?

endfor;
?>