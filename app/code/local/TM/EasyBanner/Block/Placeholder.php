<?php
/**
 * DO NOT REMOVE OR MODIFY THIS NOTICE
 *
 * EasyBanner module for Magento - flexible banner management
 *
 * @author Templates-Master Team <www.templates-master.com>
 */

class TM_EasyBanner_Block_Placeholder extends Mage_Core_Block_Template
{
	protected $_filters = array();
	protected $_banners = array();

	public function getTemplate()
	{
		if (!$this->hasData('template')) {
			$this->setData('template', 'easybanner/placeholder.phtml');
		}
		return $this->_getData('template');
	}

	/**
	 * Adds banner object to array.
	 * Before add, banner is checking with filters
	 *
	 * @param string $id
	 * @param array $filters
	 * @return TM_EasyBanner_Block_Placeholder Provides fluent interface
	 */
	public function addBanner()
	{
		$args = func_get_args();
		if (count($args)) {
			$this->_filters[array_shift($args)] = $args;
		}
		return $this;
	}

	/**
	 * Retrieve filtered and sorted banners
	 *
	 * @return array
	 */
	public function getBanner()
	{
		// load to filter later by display_count or clicks_count
		$this->_banners = Mage::getResourceModel('easybanner/banner')
		->getByIdentifier(array_keys($this->_filters));

		foreach ($this->_filters as $id => $filters) {
			$filters = $this->_convertFlatToRecursive($filters);
			$filters = current($filters);
			$this->_key = $id;
			if (!$this->_validate($filters)) {
				unset($this->_banners[$id]);
			}
		}

		uasort($this->_banners, array($this, '_sortBanners'));

		// sort banners according to placeholder offset iterator
		$placeholder = Mage::getModel('easybanner/placeholder')->load($this->getPlaceholderId());
		$i = $placeholder->getBannerOffset();
		$i = count($this->_banners) > $i ? $i : 0;
		$head = array_splice($this->_banners, $i);

		$this->_banners = $head + $this->_banners;

		$placeholder->setDoNotUpdateLayout(true)
		->setBannerOffset($i + $placeholder->getLimit())
		->save();

		// limit by placeholder config
		array_splice($this->_banners, $placeholder->getLimit());

		return $this->_banners;
	}

	private function _sortBanners($a, $b)
	{
		if ($a['sort_order'] == $b['sort_order']) {
			return 0;
		}
		return $a['sort_order'] < $b['sort_order'] ? -1 : 1;
	}

	protected function _validate($filters, $aggregator = null, $value = null)
	{
		$result = true;
		foreach ($filters as $filter) {
			if (isset($filter['aggregator'])) {
				$i = 1;
				while (isset($filter[$i])) {
					$result = $this->_validate(array($filter[$i]), $filter['aggregator'], $filter['value']);

					if (($filter['aggregator'] == 'all' && $filter['value'] == '1' && !$result)
					|| ($filter['aggregator'] == 'any' && $filter['value'] == '1' && $result)) {

						break 2;
					} elseif (($filter['aggregator'] == 'all' && $filter['value'] == '0' && $result)
					|| ($filter['aggregator'] == 'any' && $filter['value'] == '0' && !$result)) {

						$result = !$result;
						break 2;
					}
					$i++;
				}
			} else {
				switch($filter['attribute']) {
					case 'category_ids':
						if ($category = Mage::registry('current_category')) {
							$comparator = $category->getId();
						} else {
							$comparator = null;
						}
						break;
					case 'product_ids':
						if ($product = Mage::registry('current_product')) {
							$comparator = $product->getId();
						} else {
							$comparator = null;
						}
						break;
					case 'date': case 'time':
						$filter['value'] = strtotime($filter['value']);
						$comparator = time();
						break;
					case 'handle':
						$comparator = Mage::getSingleton('core/layout')->getUpdate()->getHandles();
						break;
					case 'clicks_count':
						$comparator = $this->_banners[$this->_key]['clicks_count'];
						break;
					case 'display_count':
						$comparator = $this->_banners[$this->_key]['display_count'];
						break;
					case 'customer_group':
						if ($customer = Mage::registry('current_customer')) {
							$comparator = $customer->getGroupId();
						} else {
							$comparator = 0;
						}
						break;
					default:
						return false;
				}
				$result = $this->_compare($filter['value'], $comparator, $filter['operator']);
			}
		}
		return $result;
	}

	protected function _compare($v1, $v2, $op)
	{
		if ($op=='()' || $op=='!()' || $op=='!=' || $op=='==' || $op=='{}' || $op=='!{}') {
			$v1 = explode(',', $v1);
			foreach ($v1 as &$v) {
				$v = trim($v);
			}
		}

		$result = false;

		switch ($op) {
			case '==': case '!=':
				if (is_array($v1)) {
					if (is_array($v2)) {
						$result = array_diff($v2, $v1);
						$result = empty($result) && (sizeof($v2) == sizeof($v1));
					} else {
						return false;
					}
				} else {
					if (is_array($v2)) {
						$result = in_array($v1, $v2);
					} else {
						$result = $v2==$v1;
					}
				}
				break;

			case '<=': case '>':
				if (is_array($v2)) {
					$result = false;
				} else {
					$result = $v2<=$v1;
				}
				break;

			case '>=': case '<':
				if (is_array($v2)) {
					$result = false;
				} else {
					$result = $v2>=$v1;
				}
				break;

			case '{}': case '!{}':
				if (is_array($v1)) {
					if (is_array($v2)) {
						$result = array_diff($v1, $v2);
						$result = empty($result);
					} else {
						return false;
					}
				} else {
					if (is_array($v2)) {
						$result = false;
					} else {
						$result = stripos((string)$v2, (string)$v1)!==false;
					}
				}
				break;

			case '()': case '!()':
				if (is_array($v2)) {
					$result = count(array_intersect($v2, (array)$v1)) > 0;
				} else {
					$result = in_array($v2, (array)$v1);
				}
				break;
		}

		if ('!='==$op || '>'==$op || '<'==$op || '!{}'==$op || '!()'==$op) {
			$result = !$result;
		}

		return $result;
	}

	protected function _convertFlatToRecursive($filters)
	{
		$arr = array();
		foreach ($filters as $filter) {
			$path = explode('-', $filter['key']);
			$node =& $arr;
			for ($i = 0, $l = sizeof($path); $i < $l; $i++) {
				if (!isset($node[$path[$i]])) {
					$node[$path[$i]] = array();
				}
				$node =& $node[$path[$i]];
			}
			foreach ($filter as $k => $v) {
				$node[$k] = $v;
			}
		}
		return $arr;
	}

	public function getIsProductPage() {
		$route = Mage::app()->getFrontController()->getRequest()->getRouteName();
		if ($route == 'catalog') {
			return Mage::registry('current_product');

		}
	}
}
