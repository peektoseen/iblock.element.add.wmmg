<?php
class CIBlockPropertyDateTime_
{
    public static $component;

    public static function GetPublicEditHTML($arProperty, $value, $strHTMLControlName)
    {
        /** @var CMain */
        global $APPLICATION;

        $s = '<input type="hidden" name="' . htmlspecialcharsbx($strHTMLControlName["VALUE"]) . '" size="25" value="' . htmlspecialcharsbx($value["VALUE"]) . '" />';
        ob_start();
        $APPLICATION->IncludeComponent(
            'bitrix:main.calendar',
            '',
            array(
                'FORM_NAME' => $strHTMLControlName["FORM_NAME"],
                'INPUT_NAME' => $strHTMLControlName["VALUE"],
                'INPUT_VALUE' => $value["VALUE"],
                'SHOW_TIME' => "Y",
            ),
            self::$component,
            array('HIDE_ICONS' => 'Y')
        );
        $s .= ob_get_contents();
        ob_end_clean();
        return $s;
    }
}
