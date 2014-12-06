<?php


namespace Frizinak\Corked\Definition;

use Frizinak\Corked\Definition\Exception\ValidationException;

/**
 * Key-Value container.
 * Value has to be a Traversable (arrays and objects included) and can be nested to any depth.
 * Value will be resolved into a flat array where the original traversal `path` is implode with a `.`
 * i.e.:
 * <code>
 * TokenDefinition::setValue(array(
 *   'ancestor' => array(
 *      'grandparent1' => array(
 *          'parent' => 'value',
 *      ),
 *      'grandparent2' => array(
 *          'parent' => 'value2',
 *          'key' => 'value3',
 *      ),
 *   ),
 * ));
 *
 * TokenDefinition::getValue() == array(
 *   'ancestor.grandparent1.parent' => 'value2',
 *   'ancestor.grandparent2.parent' => 'value2',
 *   'ancestor.grandparent2.key' => 'value3',
 * );
 *
 * </code>
 *
 *
 * TODO prevent recursion.
 */
class TokensDefinition extends AbstractDefinition
{

    /**
     * @inheritdoc
     */
    public function getDefinitionId()
    {
        return 'tokens';
    }

    /**
     * @inheritdoc
     */
    protected function validateValue($value)
    {
        if (!is_array($value) && !is_object($value)) {
            throw new ValidationException('Tokens should be traversable');
        }

        return $this->flatten($value);
    }

    protected function flatten($iterable, $prefix = '')
    {
        $flat = array();
        foreach ($iterable as $k => $item) {
            if (is_object($item) || is_array($item)) {
                $flat = array_merge($this->flatten((array) $item, $prefix . $k . '.'), $flat);
                continue;
            }
            $flat[$prefix . $k] = $item;
        }

        return $flat;
    }
}
