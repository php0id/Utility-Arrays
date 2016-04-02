<?php
/**
 * @copyright  <a href="http://php0id.web-box.ru/" target="_blank">php0id</a>
 * @package    Utility
 * @license    https://opensource.org/licenses/mit-license.php
 * @version    {$Id}
 */

namespace PHPOID\Utility;

/**
 * Arrays library.
 *
 * @package Utility
 * @link    https://github.com/php0id/Utility-Arrays/tree/master
 * @static
 */
class Arrays
{
    /**
     * @var string|int
     */
    protected static $key;

    /**
     * @var mixed
     */
    protected static $value;

    /**
     * @var bool
     */
    protected static $strict;

    /**
    * Searches two-dimensional array rows for a given pair key/value and returns
    * the corresponding rows.
    *
    * Example:
    * <code>
    * use use PHPOID\Utility\Arrays;
    *
    * $data = array(
    *     'first'  => array('id' => 2,  'name' => 'Patricia Peloquin', 'sex' => 'yes',      ),
    *     'second' => array('id' => 12, 'name' => 'Deedee Koerner',    'sex' => 'no',       ),
    *     'third'  => 'not an array, will never be found',
    *     'fourth' => array('id' => 85, 'name' => 'Buford Devereaux',  'sex' => 'male',     ),
    *     'fifth'  => array('id' => 06, 'name' => 'Kaci Hillyard',     'sex' => 'ofcaurse', ),
    * );
    *
    * $result = Arrays::searchRowBySubkeyValue($data, 'id', '2');
    * print_r($result);
    * </code>
    * will output:
    * <code>
    * Array
    * (
    *     [first] => Array
    *         (
    *             [id] => 2
    *             [name] => Patricia Peloquin
    *             [sex] => yes
    *         )
    * )
    * </code>
    * <code>
    * $result = Arrays::searchRowBySubkeyValue($data, 'id', '2', FALSE);
    * print_r($result);
    * </code>
    * will output:
    * <code>
    * Array
    * (
    *     [0] => Array
    *         (
    *             [id] => 2
    *             [name] => Patricia Peloquin
    *             [sex] => yes
    *         )
    * )
    * </code>
    * <code>
    * $result = Arrays::searchRowBySubkeyValue($data, 'id', '2', TRUE, TRUE);
    * print_r($result);
    * </code>
    * will output:
    * <code>
    * Array
    * (
    * )
    * </code>
    * <code>
    * $result = Arrays::searchRowBySubkeyValue($data, 'name', 'Kaci Hillyard');
    * print_r($result);
    * </code>
    * will output:
    * <code>
    * Array
    * (
    *     [fifth] => Array
    *         (
    *             [id] => 6
    *             [name] => Kaci Hillyard
    *             [sex] => ofcaurse
    *         )
    * )
    * </code>
    * <code>
    * $result = Arrays::searchRowBySubkeyValue($data, 'name', '/ER/i');
    * print_r($result);
    * </code>
    * will output:
    * <code>
    * Array
    * (
    *     [second] => Array
    *         (
    *             [id] => 12
    *             [name] => Deedee Koerner
    *             [sex] => no
    *         )
    *     [fourth] => Array
    *         (
    *             [id] => 85
    *             [name] => Buford Devereaux
    *             [sex] => male
    *         )    * )
    * </code>
    *
    * @param  array      $haystack      The array
    * @param  int|string $key           The searched key
    * @param  mixed      $value         The searched value, if passed as string
    *                                   and starts from '/' symbol, will be
    *                                   processed as regular expression, in this
    *                                   case $strict argument will be ignored
    * @param  bool       $preserveKeys  Flag specifying to maintain rows index
    *                                   assotiation
    * @param  bool       $strict        Flag specifying to compare value strict
    *                                   way
    * @return array
    * @todo   Implement regexp for key?
    */
    public static function searchRowBySubkeyValue(
        array $haystack, $key, $value, $preserveKeys = TRUE, $strict = FALSE
    )
    {
        self::$key    = $key;
        self::$value  = $value;
        self::$strict = (bool)$strict;

        $result = array_filter($haystack, array('self', 'filterBySubkey'));
        if (!$preserveKeys && sizeof($result)) {
            $result = array_combine(
                range(0, sizeof($result) - 1),
                array_values($result)
            );
        }
        self::$key   = NULL;
        self::$value = NULL;

        return $result;
    }

    /**
     * Sort two-dimensional array by column preserving row keys.
     *
     * @param  array      &$array    Array
     * @param  int|string $column    Sort column
     * @param  int        $sort      Sort type: {@see http://php.net/manual/en/function.array-multisort.php}
     * @param  int        $direction Sort direction: SORT_ASC | SORT_DESC
     * @return void
     */
    public static function sortByCol(array &$array, $column, $sort = SORT_STRING, $direction = SORT_ASC)
    {
        if (!sizeof($array)) {
            return;
        }

        $index = [];
        $i = 0;
        foreach ($array as $key => $row) {
            $index['pos'][$i]  = $key;
            $index['name'][$i] = $row[$column];
            ++$i;
        }
        array_multisort($index['name'], $sort, $direction, $index['pos']);
        $result = array();
        for ($j = 0; $j < $i; ++$j) {
            $result[$index['pos'][$j]] = $array[$index['pos'][$j]];
        }
        $array = $result;
    }

    /**
     * Filters two-dimensional array for a given pair key/value.
     *
     * @param  mixed $row
     * @return bool
     * @see    self::searchRowBySubkeyValue()
     */
    protected static function filterBySubkey($row)
    {
        $result = FALSE;
        if (is_array($row) && array_key_exists(self::$key, $row)) {
            if ('/' == substr(self::$value, 0, 1)) {
                $result = preg_match(self::$value, $row[self::$key]);
            } else {
                $result = self::$strict
                    ? self::$value === $row[self::$key]
                    : self::$value == $row[self::$key];
            }
        }

        return $result;
    }
}
