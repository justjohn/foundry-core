<?php
    header("Content-type: text/plain\n");

    $class = "ResetToken";
    $model = "password reset tokens";
    $fields = array("token"=>"string",
                    "username"=>"string",
                    "expiration"=>"integer",
                    "id"=>"integer");

print("<?php
/**
 * A model for $model.
 *
 * @package DataModel
 */

/**
 * A model class for $model.
 *
 * @package DataModel
 */
class $class {\n");
    foreach ($fields as $name=>$type) {
        print("    /**
     * The $name field.
     * @var $type
     */
    private \$$name;\n");
    }
    foreach ($fields as $name=>$type) {
        $uname = strtoupper($name{0}).substr($name, 1);
        print("
    /**
     * Set the $name field.
     * @param $type \$$name 
     */
    public function set$uname(\$$name) {
        \$this->$name = \$$name;
    }
    /**
     * Get the $name field.
     * @return $type
     */
    public function get$uname() {
        return \$this->$name;
    }\n");

    }
print("}\n?>");
?>
