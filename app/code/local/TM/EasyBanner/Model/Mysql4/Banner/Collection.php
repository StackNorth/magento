<?php
/**
 * DO NOT REMOVE OR MODIFY THIS NOTICE
 * 
 * EasyBanner module for Magento - flexible banner management
 * 
 * @author Templates-Master Team <www.templates-master.com>
 */

class TM_EasyBanner_Model_Mysql4_Banner_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected $_map = array('fields' => array(
        'placeholder'   => 'placeholder.name',
        'status'        => 'main_table.status'
    ));
    
    protected function _construct()
    {
        $this->_init('easybanner/banner');
    }
    
    /**
     * Adding banner placeholder names to result collection
     * Add for each banner placeholder information
     *
     * @return TM_EasyBanner_Model_Mysql4_Banner_Collection
     */
    public function addPlaceholderNamesToResult()
    {
        $bannerIds = $this->getColumnValues('banner_id');
        $placeholdersToBanner = array();
        
        if (count($bannerIds) > 0) {
            $select = $this->getConnection()->select()
                ->from(array('placeholder' => $this->getTable('easybanner/placeholder')), 'name')
                ->join(array('banner_placeholder' => $this->getResource()->getTable('easybanner/banner_placeholder')),
                    'banner_placeholder.placeholder_id = placeholder.placeholder_id',
                    array('banner_placeholder.banner_id'))
                ->where('banner_placeholder.banner_id IN (?)', $bannerIds);
            $result = $this->getConnection()->fetchAll($select);
            
            foreach ($result as $row) {
                if (!isset($placeholdersToBanner[$row['banner_id']])) {
                    $placeholdersToBanner[$row['banner_id']] = array();
                }
                $placeholdersToBanner[$row['banner_id']][] = $row['name'];
            }
        }
        
        foreach ($this as $item) {
            if (isset($placeholdersToBanner[$item->getId()])) {
                $item->setPlaceholder(implode(", ", $placeholdersToBanner[$item->getId()]));
            } else {
                $item->setPlaceholder(null);
            }
        }
        
        return $this;
    }
    
    /**
     * Adding banner statistics to result collection
     *
     * @return TM_EasyBanner_Model_Mysql4_Banner_Collection
     */
    public function addStatisticsToResult()
    {
        $bannerIds = $this->getColumnValues('banner_id');
        $statToBanner = array();
        
        if (count($bannerIds) > 0) {
            $select = $this->getConnection()->select()
                ->from(array('stat' => $this->getTable('easybanner/banner_statistic')))
                ->where('stat.banner_id IN (?)', $bannerIds);
            $result = $this->getConnection()->fetchAll($select);
            
            foreach ($result as $row) {
                $statToBanner[$row['banner_id']] = $row;
            }
        }
        
        foreach ($this as $item) {
            if (isset($statToBanner[$item->getId()])) {
                $item->setDisplayCount($statToBanner[$item->getId()]['display_count']);
                $item->setClicksCount($statToBanner[$item->getId()]['clicks_count']);
            } else {
                $item->setDisplayCount(null);
                $item->setClicksCount(null);
            }
        }
        
        return $this;
    }
    
    public function joinLeft($table, $cond, $cols='*')
    {
        if (!isset($this->_joinedTables[$table])) {
            $this->getSelect()->joinLeft(array($table => $this->getTable($table)), $cond, $cols);
            $this->_joinedTables[$table] = true;
        }
        return $this;
    }
}