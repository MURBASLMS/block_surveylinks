<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Abstract class for defining structured models with known attributes.
 *
 * @package    block_surveylinks
 * @author     Tom Dickman <tomdickman@catalyst-au.net>
 * @copyright  2020 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_surveylinks;

/**
 * Abstract class for defining structured models with known attributes.
 *
 * @package    block_surveylinks
 * @author     Tom Dickman <tomdickman@catalyst-au.net>
 * @copyright  2020 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class model {

    /**
     * @var array the model data.
     */
    protected $data = [];

    /**
     * An associative array keyed by the model attribute with the value being a comma delimited
     * string of the path to get attribute value from raw associative array of data
     *
     * The only data types which should be used in this model and any submodels are scalar types
     * (int, bool, string, float), arrays, and this (\block_surveylinks\model) or extending classes
     * otherwise any streams or other complex data types (like custom classes) will not necessarily be
     * converted correctly to a record and may have other unexpected behaviour.
     *
     * For example:
     * Raw data = '{"name":"Exam","value":50,"_embedded":{"unitOfferings": []}'
     * ATTRIBUTE_MAP = [
     *      'name' => 'name',
     *      'value' => 'value'
     *      'unitoffers' => '_embedded,unitOfferings'
     * ]
     *
     * If the mapping requires any custom logic to be run on raw data before setting in model
     * instance, a custom setter method may be defined by declaring a `set_{$attribute}` protected
     * method in the extending model class.
     */
    const ATTRIBUTE_MAP = [];

    /**
     * Models which specific attributes are to be mapped to, keyed by attribute with the value
     * being the classname of the model extending this class to apply.
     *
     * For example:
     * ATTRIBUTE_SUBMODELS = [
     *      'unitoffers' => '\local_preview\assessment_unit_offer_model'
     * ]
     */
    const ATTRIBUTE_SUBMODELS = [];

    /**
     * Is this model immutable? (ie. attribute values cannot be changed outside of constructor).
     *
     * Extending classes can set this to `true` and after construction, no attribute values may be
     * altered in instance.
     */
    const IMMUTABLE = false;

    /**
     * model constructor.
     *
     * @param array $data raw associative array of model data.
     * @throws \coding_exception if trying to create a submodel for an unregistered model class.
     */
    public function __construct(array $data) {
        foreach (static::ATTRIBUTE_MAP as $attribute => $rawdatapath) {
            // If an attribute is no set in the data, default to `null`.
            $attributevalue = null;
            $path = explode(',', $rawdatapath);

            // Move down through levels of multidimensonal array to find the mapped value.
            foreach ($path as $index => $subpath) {
                if (empty($index) && isset($data[$subpath])) {
                    $attributevalue = $data[$subpath];
                } else if (!empty($attributevalue) && array_key_exists($subpath, $attributevalue)) {
                    $attributevalue = $attributevalue[$subpath];
                } else {
                    $attributevalue = null;
                }
            }

            // Cast any values to submodels which require it.
            if (array_key_exists($attribute, static::ATTRIBUTE_SUBMODELS)) {
                $submodelclassname = static::ATTRIBUTE_SUBMODELS[$attribute];

                if (!class_exists($submodelclassname)) {
                    throw new \coding_exception("Could not find model for classname '$submodelclassname', " .
                        "cannot cast attribute value to model.");
                } else if (!empty($attributevalue) && is_array($attributevalue)) {
                    foreach ($attributevalue as $key => $value) {
                        $attributevalue[$key] = new $submodelclassname($value);
                    }
                }
            }

            // Do not use magic `__set` method here in case this is an immutable model,
            // in which case attribute value setting would fail with `__set` method.
            $setmethod = 'set_' . $attribute;
            if (method_exists($this, $setmethod)) {
                $this->$setmethod($attributevalue);
            } else {
                $this->data[$attribute] = $attributevalue;
            }
        }
    }

    /**
     * Magic getter method to get values for model attributes from model data.
     *
     * @param string $name the model data attribute to get value for.
     *
     * @return mixed depending on the attribute format.
     */
    public function __get(string $name) {
        $result = null;

        if (array_key_exists($name, static::ATTRIBUTE_MAP)) {
            if (array_key_exists($name, $this->data)) {
                // Use get method for attribute if declared in class.
                $getmethod = "get_$name";
                if (method_exists(static::class, $getmethod)) {
                    $result = $this->$getmethod();
                } else {
                    $result = $this->data[$name];
                }
            }
        } else {
            throw new \coding_exception("Invalid model attribute, cannot get value for '$name'");
        }

        return $result;
    }

    /**
     * Magic isset method for determining if a model attribute has a value set.
     *
     * @param string $name the model data attribute to check is set.
     *
     * @return bool true if instance data attribute is declared and not `null`, false otherwise.
     */
    public function __isset(string $name) {
        return isset($this->data[$name]);
    }

    /**
     * Magic setter method to set values for model attributes in model data.
     *
     * @param string $name the model data attribute to set value for.
     * @param mixed $value the value to set.
     *
     * @throws \coding_exception if there is no attribute to set of name.
     */
    public function __set(string $name, $value) {
        if (static::is_immutable()) {
            throw new \coding_exception(static::class . ' models are immutable, cannot alter attribute values.');
        }

        if (array_key_exists($name, static::ATTRIBUTE_MAP)) {
            // If there is a setter method defined in model for attribute, use this to set the data value.
            // You can define a setter method in extending model classes by declaring a protected method
            // named `set_{$attribute}` which conducts any logic required on the raw value before settings
            // as model instance data value.
            $setmethod = 'set_' . $name;
            if (method_exists($this, $setmethod)) {
                $this->$setmethod($value);
            } else {
                $this->data[$name] = $value;
            }
        } else {
            throw new \coding_exception("Invalid model attribute, cannot set value for '$name'");
        }
    }

    /**
     * Convert an arrays values for a record representation of data, including any models.
     *
     * @param array $array the values to convert for record.
     *
     * @return array the converted array with record values.
     */
    final protected function convert_array_values_for_record(array $array) {
        foreach ($array as $key => $value) {
            if (is_a($value, self::class)) {
                // If it's a model, convert it to a record.
                $array[$key] = $value->to_record();
            } else if (is_array($value)) {
                // If it's an array, recursively convert all values for record.
                $array[$key] = self::convert_array_values_for_record($value);
            } else if (!is_scalar($value)) {
                // If it isn't scalar or a submodel, rely on JSON encode/decode to coerce the data.
                $array[$key] = json_decode(json_encode($value));
            }
        }
        return $array;
    }

    /**
     * Is this models data immutable?
     *
     * Override this method to add custom logic for checking immutability, or set constant IMMUTABLE
     * value to `true` in extending class to make immutable.
     *
     * @return bool true if model is immutable, false otherwise.
     */
    public function is_immutable() : bool {
        return static::IMMUTABLE;
    }

    /**
     * Convert model data to a record.
     */
    final public function to_record() {
        $record = new \stdClass();

        foreach (array_keys(static::ATTRIBUTE_MAP) as $attribute) {
            // Pass through magic getter in case there is a custom `get_` method for attribute.
            $value = $this->$attribute;
            if (is_scalar($value) || empty($value)) {
                // This is a basic scalar data type or an empty value, nothing to do.
                $record->$attribute = $value;
            } else if (is_a($value, self::class)) {
                // Found a submodel, convert it to a record too.
                $record->$attribute = $value->to_record();
            } else if (is_array($value)) {
                // We need to recursively check arrays to see if there are any submodels which
                // require conversion.
                $record->$attribute = self::convert_array_values_for_record($value);
            } else {
                // Unknown and unsupported data type, hopefully JSON conversion can sort it out...
                $record->$attribute = json_decode(json_encode($value));
            }
        }
        return $record;
    }

    /**
     * Deconstruct the model into raw source data.
     *
     * @return array associative array of raw model data.
     */
    final public function to_raw() : array {
        $rawdata = [];

        foreach (static::ATTRIBUTE_MAP as $attribute => $rawdatapath) {
            $paths = explode(',', $rawdatapath);
            // Get a reference to the rawdata array to set the subpaths in.
            $subpathreference = &$rawdata;
            foreach ($paths as $depth => $subpath) {
                if ($depth < count($paths) - 1) {
                    // We aren't at the end of the subpaths, so add this subpath to reference (if it isn't there already).
                    if (!array_key_exists($subpath, $subpathreference)) {
                        $subpathreference[$subpath] = [];
                    }
                    // Set the subpath reference to the current subpath in multi-dimensional array.
                    $subpathreference = &$subpathreference[$subpath];
                } else if (array_key_exists($attribute, static::ATTRIBUTE_SUBMODELS)) {
                    // This subpath is an array of models itself, recursively convert all submodels to raw data.
                    $rawsubmodeldata = [];
                    foreach ($this->$attribute as $attributemodel) {
                        $rawsubmodeldata[] = $attributemodel->to_raw();
                    }
                    $subpathreference[$subpath] = $rawsubmodeldata;
                } else {
                    // We are at the last subpath, set the value in the reference.
                    $subpathreference[$subpath] = $this->$attribute;
                }
            }
            // Unset the subpath reference to prevent overriding.
            unset($subpathreference);
        }

        return $rawdata;
    }
}
