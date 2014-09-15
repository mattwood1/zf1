<?php
class God_Model_ModelRanking extends God_Model_ModelTable {

    private $_factor = 10;
    private $_modes = array();
    private $_mode = null;
    private $_models = array();
    private $_ignoreModel;
    
    private $_rankingStats = array();
    private $_rankingCalc = array();
    private $_rankingStatsKey;
    private $_topHigh;
    private $_topLow;
    private $_highKey;

    public function __construct($ignoreModel = null) {
        $this->_rankingStats = $this->getRankingStats(2, true);
        $this->_ignoreModel = $ignoreModel;
        
        $this->_topHigh = max(array_keys($this->getRankingStats(1, true)));
        $$this->_topLow = $this->_topHigh - floor(($this->_topHigh / 100) * $this->_factor);
        
        $this->_calculateArrays();
        $this->_filterModes();
        $this->_chooseMode();
    }
    
    public function getMode()
    {
        return $this->_mode;
    }
    
    public function getRankingModels()
    {
        $this->_models = $this->getModelsByRanking($this->_rankingStatsKey);
        
        // Prevent model flow
        if ($this->_ignoreModel && $this->getModelCount() > 2) {
            foreach ($this->_models as $modelKey => $model) {
                if ($model->ID == $this->_ignoreModel->ID) {
                    unset($this->_models[$modelKey]);
                }
            }
        }
        
        $modelArrayKeys = array_keys($this->_models->toArray());
        $modelKeys[] = $modelArrayKeys[0];
        unset($modelArrayKeys[0]);
        shuffle($modelArrayKeys);
        $modelKeys[] = $modelArrayKeys[0];
        shuffle($modelKeys);
        
        foreach ($modelKeys as $modelKey) {
            $modelArray[] = $this->_models[$modelKey];
        }
        
        return $modelArray;
    }
    
    public function getModelCount()
    {
        return count($this->_models->toArray());
    }

    private function _calculateArrays()
    {
        $this->_calculateRandom();
        $this->_calculateHigh();
        $this->_calculateTop();
        $this->_calculateBottom();
    }
    
    private function _calculateRandom()
    {
        if ($this->_rankingStats) {
            $this->_rankingCalc['random'] = array_rand($this->_rankingStats, 1);
            $this->_modes[] = 'random';
        }
    }
    
    private function _calculateHigh()
    {
        $highArray = array_keys($this->_rankingStats, max($this->_rankingStats));
        $this->_highKey = $highArray[0];
        
        if ($this->_highKey) {
            $this->_rankingCalc['high-ordered'] = $this->_highKey;
            $this->_modes[] = 'high-ordered';
        }
    }
    
    private function _calculateTop()
    {
        $topRankingStats = $this->_rankingStats;
        foreach (array_keys($topRankingStats) as $topKey) {
            if ($topKey < $this->_topLow) {
                unset($topRankingStats[$topKey]);
            }
        }
        if ($topRankingStats) {
            $ordered = array_keys($topRankingStats);
            $this->_rankingCalc['top-random'] = array_rand($topRankingStats, 1);
            $this->_rankingCalc['top-ordered'] = $ordered[0];
            $modes[] = 'top-random';
            $modes[] = 'top-ordered';
        }
    }
    
    private function _calculateBottom()
    {
        $bottomRankingStats = $this->_rankingStats;
        foreach ($bottomRankingStats as $bottomKey => $bottomStat) {
            
            $offset = ceil(($this->_highKey-1) / 100 ) * $this->_factor;
            
//            $highCount = $this->_rankingStats[$this->_highKey];
            
            if ( ($bottomKey < $this->_highKey) || ($bottomStat < ($this->_highKey-$offset)) ) {
                unset($bottomRankingStats[$bottomKey]);
            }
        }
        
        if ($bottomRankingStats) {
            $ordered = array_keys($bottomRankingStats);
            $this->_rankingCalc['bottom-random'] = array_rand($bottomRankingStats,1 );
            $this->_rankingCalc['bottom-ordered'] = $ordered[0];
            $modes[] = 'bottom-random';
            $modes[] = 'bottom-ordered';
        }
    }
    
    /**
     * Only use 'ordered' on even hours, 'random' on odd
     */
    private function _filterModes()
    {
        $hour = (int)date("G", mktime());
        if ( $hour%2 == 0 ) {
            foreach (array('random', 'top-random', 'bottom-random') as $remove) {
                $key = array_search($remove, $this->_modes);
                if($key !== false) {
                    unset($this->_modes[$key]);
                }
            }
        } else {
            foreach (array('top-ordered', 'bottom-ordered') as $remove) {
                $key = array_search($remove, $this->_modes);
                if($key !== false) {
                    unset($this->_modes[$key]);
                }
            }
        }
    }
    
    /**
     * Choose a mode from available modes
     */
    private function _chooseMode()
    {
        $this->_mode = $this->_modes[array_rand($this->_modes, 1)];
        switch ($this->_mode) {
            case 'random':
                $this->_rankingStatsKey = $this->_rankingCalc['random']; break;
            case 'top-random':
                $this->_rankingStatsKey = $this->_rankingCalc['top-random'];
                break;
            case 'top-ordered':
                $this->_rankingStatsKey = $this->_rankingCalc['top-ordered'];
                break;
            case 'high-ordered':
                $this->_rankingStatsKey = $this->_rankingCalc['high-ordered'];
                break;
            case 'bottom-random':
                $this->_rankingStatsKey = $this->_rankingCalc['bottom-random'];
                break;
            case 'bottom-ordered':
                $this->_rankingStatsKey = $this->_rankingCalc['bottom-ordered'];
                break;
        }
    }
}