<?php
/***
 * Class D1m_Common_Model_Resource_Collection
 */
class D1m_Common_Model_Resource_Collection extends Varien_Data_Collection
{
    protected $_filters = array();

    /**
     * All collection data array
     * Used for getData method
     *
     * @var array
     */
    protected $_data = null;

    public function addFieldToFilter($attribute, $condition=null)
    {
        if (isset($condition['eq'])) {
            $value  = $condition['eq'];
            $method = 'equal';
        } elseif (isset($condition['like'])) {
            $value  = $condition['like'];
            $method = 'like';
        }

        $this->_filters[] = array(
            'attribute' => $attribute,
            'method'    => $method,
            'value'     => $value,
        );

        return $this;
    }

    public function load($printQuery = false, $logQuery = false)
    {
        if ($this->isLoaded())
        {
            return $this;
        }

        $this->_addFilterToCollection();
        $this->_sortCollection();

        $this->_setIsLoaded();
        return $this;
    }

    public function getSize()
    {
        if (is_null($this->_totalRecords))
        {
            $this->load();
            $this->_totalRecords = count($this->_items);
        }
        return intval($this->_totalRecords);
    }

    public function addFilter($field, $value, $type = 'and')
    {
        $this->addFieldToFilter($field, $value);
        $this->_isFiltersRendered = false;

        return $this;
    }

    public  function renderLimit()
    {
      /*  if ($this->_pageSize) {
            $this->_items = array_slice($this->_items, ($this->getCurPage() - 1) * $this->_pageSize, $this->_pageSize, true);
        }*/

        if($this->_pageSize && $this->_curPage)
        {
            $totalPages = ceil( $this->getSize()/ $this->_pageSize );

            $page = max($this->_curPage, 1);
            $page = min($page, $totalPages);
            $offset = ($page - 1) * $this->_pageSize;
            if( $offset < 0 ) $offset = 0;

            $items = array_slice( $this->_items, $offset, $this->_pageSize );
            $this->_items = array();
            foreach($items as $item) {
                $this->addItem($item);
            }
        }

        return $this;
    }

    /***
     * reset  order
     *
     * @return $this
     */
    public  function resetOrder()
    {
        $this->_orders = null;
        return $this;
    }

    /***
     * @return $this
     */
    protected function _sortCollection()
    {
        if (!count($this->_orders))  return $this;
        usort($this->_items , array($this, '_doCompare'));
        return $this;
    }

    protected function _addFilterToCollection()
    {
        $items = $this->_items;
        $this->_items = array();
        foreach($items as $item) {
            if ($this->_filterItem($item)) {
                $this->addItem($item);
            }
        }
    }

    protected function _filterItem($item)
    {
        foreach ($this->_filters as $filter) {
            $method = $filter['method'];
            $attribute = $filter['attribute'];
            $itemValue = $item[$attribute];

            if (is_array($itemValue) && isset($itemValue['filter_condition']))
            {
                $itemValue = $itemValue['filter_condition'];
            }

            if (!$this->$method($itemValue, $filter['value'])) {
                return false;
            }
        }

        return true;
    }

    protected function _getColumnsValue($item, $column)
    {
        $value = $item->getData($column);
        if (is_array($value)) {
            $value = implode(',', $value);
        }
        return $value;
    }

    protected function _doCompare($a, $b)
    {
        $result = 0;
        $cnt=0;

        $columns = array_keys($this->_orders);
        // check each key in the order specified
        foreach ( $columns as $column )
        {
            $order = $this->_orders[$column];

            // check the value for ignorecase and do natural compare accordingly
            $ignore = false; //排序的大小写敏感

            $valueA = $this->_getColumnsValue($a, $column);
            $valueB = $this->_getColumnsValue($b, $column);

            $result = $ignore ? strnatcasecmp ($valueA, $valueB) : strnatcmp($valueA, $valueB);

            // check the value for reverse and reverse the sort order accordingly
            $revcmp = !(strtolower($order) == 'asc');

            $result = $revcmp ? ($result * -1) : $result;

            // the first key that results in a non-zero comparison determines
            // the order of the elements
            if ( $result != 0 ) break;
            $cnt++;
        }
        return $result;
    }

    public function equal($filerValue, $needle)
    {
        return ($filerValue == $needle);
    }

    public function like($filerValue, $needle)
    {
        $needle = trim($needle, ' \'"%');
        return stristr($filerValue, $needle);
    }

    /**
     * Reset loaded for collection data array
     *
     * @return Varien_Data_Collection_Db
     */
    public function resetData()
    {
        $this->_data = null;
        return $this;
    }

    /**
     * Get all data array for collection
     *
     * @return array
     */
    public function getData()
    {
        if ($this->_data === null)
        {
            $this->load();
            foreach($this->_items as $item)
            {
                $this->_data[] = $item->getData();
            }
        }
        return $this->_data;
    }
}