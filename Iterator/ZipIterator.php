<?php
/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @since         3.0.5
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace Cake\Collection\Iterator;

use Cake\Collection\Collection;
use Cake\Collection\CollectionInterface;
use Cake\Collection\CollectionTrait;
use MultipleIterator;

/**
 * Creates an iterator that returns elements grouped in pairs
 *
 * ### Example
 *
 * {{{
 *  $iterator = new ZipIterator([[1, 2], [3, 4]]);
 *  $iterator->toList(); // Returns [[1, 3], [2, 4]]
 * }}}
 *
 * You can also chose a custom function to zip the elements together, such
 * as doing a sum by index:
 *
 * ### Example
 * {{{
 *  $iterator = new ZipIterator([[1, 2], [3, 4]], function ($a, $b) {
 *    return $a + $b;
 *  });
 *  $iterator->toList(); // Returns [3, 6]
 * }}}
 */
class ZipIterator extends MultipleIterator implements CollectionInterface
{

    use CollectionTrait;

    /**
     * The function to use for zipping items together
     *
     * @var callable
     */
    protected $_callback;

    /**
     * Creates the iterator to merge together the values by for all the passed
     * iterators by their corresponding index.
     *
     * @param array $sets The list of array or iterators to be zipped.
     * @param callable $callable The function to use for zipping the elements of each iterator.
     */
    public function __construct(array $sets, $callable = null)
    {
        $sets = array_map(function ($items) {
            return (new Collection($items))->unwrap();
        }, $sets);

        $this->_callback = $callable;
        parent::__construct(MultipleIterator::MIT_NEED_ALL | MultipleIterator::MIT_KEYS_NUMERIC);

        foreach ($sets as $set) {
            $this->attachIterator($set);
        }
    }

    /**
     * Returns the value resulting out of zipping all the elements for all the
     * iterators with the same positional index.
     *
     * @return mixed
     */
    public function current()
    {
        if ($this->_callback === null) {
            return parent::current();
        }

        return call_user_func_array($this->_callback, parent::current());
    }
}
