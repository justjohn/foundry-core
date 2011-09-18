<?php
/**
 * Utility for rendering Models in different formats (XML, JSON).
 *
 * @category  Foundry-Core
 * @package   Foundry\Core\Utilities
 * @author    John Roepke <john@justjohn.us>
 * @copyright 2010-2011 John Roepke
 * @license   http://phpfoundry.com/license/bsd New BSD license
 * @version   1.0.0
 */

namespace Foundry\Core\Utilities;

use Foundry\Core\Core;
use Foundry\Core\Logging\Log;
use Foundry\Core\Model;

/**
 * Class for rendering Models into different formats (XML, JSON).
 *
 * @category  Foundry-Core
 * @package   Foundry\Core\Utilities
 * @author    John Roepke <john@justjohn.us>
 * @copyright 2010-2011 John Roepke
 * @license   http://phpfoundry.com/license/bsd New BSD license
 * @since     1.0.0
 */
class Renderer {

    static function asXML(Model $model) {
        $element_name = get_class($model);
        $element_name = str_replace('\\', '-', $element_name);
        $data = $model->asArray();
        $output = "\t\t<$element_name>\n";
        if (count($data) > 0) {
            $i = 0;
            foreach ($data as $key=>$value) {
                $type = $model->getFieldType($key);
                if ($type == Model::INT) {
                    $value = intval($value);
                } else if ($type == Model::LST) {
                    $values = "";
                    $j = 0;
                    foreach ($value as $item) {
                        $item = self::xmlValue($item);
                        $values .= "\t\t\t\t<item>$item</item>\n";
                    }
                    $value = "<list>" . (empty($values)?"":"\n$values\t\t\t") . "</list>";
                } else if ($type == Model::BOOL) {
                    $value = $value?"true":"false";
                    $value = "<bool>$value</bool>";
                } else { //  STR
                    $value = self::xmlValue($value);
                }
                $output .= "\t\t\t<$key>$value</$key>\n";
            }
        }

        $output .= "\t\t</$element_name>\n";
        return $output;
    }

    static function asJSON(Model $model) {
        $data = $model->asArray();
        $output = "{";
        if (count($data) > 0) {
            $i = 0;
            foreach ($data as $key=>$value) {
                $type = $model->getFieldType($key);
                if ($type == Model::INT) {
                    $value = intval($value);
                } else {
                    $value = self::jsonIt($value);
                }
                $output .= "\"$key\": $value" . (++$i<count($data)?",":"");
            }
        }
        $output .= "}";
        return $output;
    }

    private static function jsonIt($item) {
        if (is_array($item)) {
            $item = self::jsonArray($item);
        } else if ($item instanceof Model) {
            $item = $item->asJSON();
        } else {
            $item = self::jsonValue($item);
        }
        return $item;
    }

    private static function jsonArray($arr) {
        $values = "";
        $j = 0;
        foreach ($arr as $item) {
            $item = self::jsonIt($item);
            $values .= "$item".(++$j<count($arr)?",":"");
        }
        $value = " [$values]";
        return $value;
    }

    private static function jsonValue($value) {
        $value = str_replace('\\', '\\\\', $value);
        $value = str_replace('"', '\\"', $value);
        $value = str_replace("\n", '\\n', $value);
        $value = str_replace("\r", '', $value);
        $value = str_replace("\t", '\\t', $value);
        return '"'.$value.'"';
    }
    private static function xmlValue($value) {
        $value = self::xml_entities($value);
        return $value;
    }

    private static function xml_entities($text){
        // Debug and Test
        // $text = "test &amp; &trade; &amp;trade; abc &reg; &amp;reg; &#45;";

        // First we encode html characters that are also invalid in xml
        $text = htmlentities($text, ENT_COMPAT, 'ISO-8859-15', false);

        // XML character entity array from Wiki
        // Note: &apos; is useless in UTF-8 or in UTF-16
        $arr_xml_special_char = array("&quot;","&amp;","&apos;","&lt;","&gt;");

        // Building the regex string to exclude all strings with xml special char
        $arr_xml_special_char_regex = "(?";
        foreach($arr_xml_special_char as $key => $value){
            $arr_xml_special_char_regex .= "(?!$value)";
        }
        $arr_xml_special_char_regex .= ")";

        // Scan the array for &something_not_xml; syntax
        $pattern = "/$arr_xml_special_char_regex&([a-zA-Z0-9]+;)/";

        // Replace the &something_not_xml; with &amp;something_not_xml;
        $replacement = '&amp;${1}';
        return preg_replace($pattern, $replacement, $text);
    }
}
?>
