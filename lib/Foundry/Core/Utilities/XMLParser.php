<?php
/**
 * Utility for parsing XML into an array.
 *
 * @category  Foundry-Core
 * @package   Foundry\Core\Utilities
 * @author    John Roepke <john@justjohn.us>
 * @copyright 2010-2011 John Roepke
 * @license   http://phpfoundry.com/license/bsd New BSD license
 * @version   1.0.0
 */

namespace Foundry\Core\Utilities;

/**
 * An XML to Array parser based on the comment in the PHP manual
 *  by Glen@ITIstudios.ca and available under a Creative Commons
 *  Attribution 3.0 License.
 * 
 * @link http://www.php.net/manual/en/ref.xml.php#101555
 * 
 * @copyright Copyright (c) the PHP Documentation Group
 */
class XMLParser {
  /**
   * The array created by the parser which can be assigned to a variable with: $varArr = $domObj->array.
   *
   * @var Array
   */
  public  $array;
  private $parser;
  private $pointer;

  /**
   * $domObj = new XMLParser($xml);
   *
   * @param Str $xml file/string
   */
  public function __construct($xml) {
    $this->pointer =& $this->array;
    $this->parser = xml_parser_create("UTF-8");
    xml_set_object($this->parser, $this);
    xml_parser_set_option($this->parser, XML_OPTION_CASE_FOLDING, false);
    xml_set_element_handler($this->parser, "tag_open", "tag_close");
    xml_set_character_data_handler($this->parser, "cdata");
    xml_parse($this->parser, ltrim($xml));
  }

  private function tag_open($parser, $tag, $attributes) {
    $this->convert_to_array($tag, '_');
    $idx=$this->convert_to_array($tag, 'cdata');
    if(isset($idx)) {
      $this->pointer[$tag][$idx] = Array('@idx' => $idx,'@parent' => &$this->pointer);
      $this->pointer =& $this->pointer[$tag][$idx];
    }else {
      $this->pointer[$tag] = Array('@parent' => &$this->pointer);
      $this->pointer =& $this->pointer[$tag];
    }
    if (!empty($attributes)) { $this->pointer['_'] = $attributes; }
  }

  /**
   * Adds the current elements content to the current pointer[cdata] array.
   */
  private function cdata($parser, $cdata) {
    if(isset($this->pointer['cdata'])) { $this->pointer['cdata'] .= $cdata;}
    else { $this->pointer['cdata'] = $cdata;}
  }

  private function tag_close($parser, $tag) {
    $current = & $this->pointer;
    if(isset($this->pointer['@idx'])) {unset($current['@idx']);}
    $this->pointer = & $this->pointer['@parent'];
    unset($current['@parent']);
    if(isset($current['cdata']) && count($current) == 1) { $current = $current['cdata'];}
    else if(empty($current['cdata'])) { unset($current['cdata']); }
  }

  /**
   * Converts a single element item into array(element[0]) if a second element of the same name is encountered.
   */
  private function convert_to_array($tag, $item) {
    if(isset($this->pointer[$tag][$item])) {
      $content = $this->pointer[$tag];
      $this->pointer[$tag] = array((0) => $content);
      $idx = 1;
    }else if (isset($this->pointer[$tag])) {
      $idx = count($this->pointer[$tag]);
      if(!isset($this->pointer[$tag][0])) {
        foreach ($this->pointer[$tag] as $key => $value) {
            unset($this->pointer[$tag][$key]);
            $this->pointer[$tag][0][$key] = $value;
    }}}else $idx = null;
    return $idx;
  }
}

?>
