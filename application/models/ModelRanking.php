<?php
class God_Model_ModelRanking extends God_Model_ModelTable {

    private $_factor = 20;
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
    private $_highTop;
    private $_highBottom;

    private $_bottomRankingStats;
    private $_highRankingStats;
    private $_topRankingStats;
        
    
    public function __construct($ignoreModel = null) {
        $this->_rankingStats = $this->getRankingStats(2, true);
        $this->_ignoreModel = $ignoreModel;
        
        // Sorting Top Ranking
        $this->_topHigh = max(array_keys($this->getRankingStats(1, true)));
        $this->_topLow = (int)($this->_topHigh - floor(($this->_topHigh / 100) * $this->_factor));
        
        foreach ($this->_rankingStats as $rankingStatKey => $rankingStat) {
            if ($rankingStatKey >= $this->_topLow) {
                $this->_topRankingStats[$rankingStatKey] = $rankingStat;
                unset($this->_rankingStats[$rankingStatKey]);
            }
        }
        
        $this->_highKey = reset(array_keys($this->_rankingStats, max($this->_rankingStats)));
        
        if (array_key_exists($this->_highKey -1, $this->_rankingStats)) {
            $highBottomPrev = reset(array_keys($this->_rankingStats, $this->_rankingStats[$this->_highKey -1]))-1;
            $highBottomMode = 'split';
//            _d(array('1st' => $highBottomPrev, $highBottomMode));
        } else {
            $highBottomPrev = reset(array_keys($this->_rankingStats, $this->_rankingStats[$this->_highKey]))-1;
            $highBottomMode = 'flat';
//            _d(array('2nd' => $highBottomPrev, $highBottomMode));
        }
        
        if ($highBottomMode == 'flat') {
            _d($highBottomMode);
        }
        
        if (array_key_exists($this->_highKey - 1, $this->_rankingStats)) {
            $this->_highTop = end(array_keys($this->_rankingStats, $this->_rankingStats[$this->_highKey - 1]));
        } else {
            $this->_highTop = end(array_keys($this->_rankingStats, $this->_rankingStats[$this->_highKey]));
        }
        
        if ($this->_highTop >= $this->_topLow) {
            $this->_highTop = $this->_topLow - 1;
        }

        // Check consequitive keys and set highBottom
        for ($currentHighKey = $this->_highTop; $currentHighKey >= 0; $currentHighKey--) {
            
            // Concurrency
            if ( !array_key_exists($currentHighKey, $this->_rankingStats) ) {
                $this->_highBottom = $currentHighKey+1;
                break;
            } 
            // Ensures that if it is concurrent it needs to be less than highBottomPrev
            elseif ( $highBottomMode == 'split' && $this->_rankingStats[$currentHighKey] <= $this->_rankingStats[$highBottomPrev] -1 ) {
                $this->_highBottom = $currentHighKey;
                break;
            }
            elseif ( $highBottomMode == 'flat' && $this->_rankingStats[$currentHighKey] <= $this->_rankingStats[$highBottomPrev+1] -1 ) {
                $this->_highBottom = $currentHighKey;
                break;
            }

        }

        foreach ($this->_rankingStats as $rankingStatKey => $rankingStat) {
            if ($rankingStatKey < $this->_highBottom) {
                $this->_bottomRankingStats[$rankingStatKey] = $rankingStat;
            } elseif ($rankingStatKey >= $this->_highBottom && $rankingStatKey <= $this->_highTop) {
                $this->_highRankingStats[$rankingStatKey] = $rankingStat;
            }
        }
        
        $this->_calculateArrays();
        $this->_filterModes();
        $this->_chooseMode();
        
        if (@$_GET['test']) {
            _d(array('$this->_rankingStats' => $this->_rankingStats));
            _d(array('modes' => $this->_modes));
            _d(array('mode' => $this->_mode));
            
            _d(array('Top High' => $this->_topHigh, 'Top Low' => $this->_topLow));
            _d(array('High Key' => $this->_highKey));
            _d(array('High Top' => $this->_highTop, 'High Bottom' => $this->_highBottom));
            
            _d(array(
                'bottom' => $this->_bottomRankingStats,
                'high' => $this->_highRankingStats,
                'top' => $this->_topRankingStats
            ));
            
            exit;
        }
    }
    
    public function getMode()
    {
        return $this->_mode;
    }
    
    public function getModes()
    {
        return $this->_modes;
    }
    
    public function getRankingModels()
    {
        $this->_models = $this->getModelsByRanking($this->_rankingStatsKey);
        
//        _dexit($this->_models);
        
        $models = $this->_models; // copy models as it is modified.
        
        // Prevent model flow
        if ($this->_ignoreModel && $this->getModelCount() > 2 && array_key_exists($this->_rankingStatsKey, $this->_ignoreModel)) {
            foreach ($models as $modelKey => $model) {
                if ($model['ID'] == $this->_ignoreModel[$this->_rankingStatsKey]) {
                    unset($models[$modelKey]);
                }
            }
        }
        
        $modelArrayKeys = array_keys($models);
        $modelKeys[] = $modelArrayKeys[0];
        unset($modelArrayKeys[0]);
        shuffle($modelArrayKeys);
        $modelKeys[] = $modelArrayKeys[0];
        shuffle($modelKeys);
        
        foreach ($modelKeys as $modelKey) {
            $modelArray[] = $models[$modelKey];
        }
        
        return $modelArray;
    }
    
    public function getModelCount()
    {
        return count($this->_models);
    }

    private function _calculateArrays()
    {
        $this->_calculateRandom();
        $this->_calculateHigh();
        $this->_calculateTop();
        $this->_calculateBottom();
        $this->_caclulateHighBottom();
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
        if ($this->_highKey) {
            $this->_rankingCalc['high-ordered'] = $this->_highKey;
            $this->_modes[] = 'high-ordered';
        }
    }
    
    private function _calculateTop()
    {
        if ($this->_topRankingStats) {
            $ordered = array_keys($this->_topRankingStats);
            $this->_rankingCalc['top-random'] = array_rand($this->_topRankingStats, 1);
            $this->_rankingCalc['top-ordered'] = $ordered[0];
            $this->_modes[] = 'top-random';
            $this->_modes[] = 'top-ordered';
        }
    }
    
    private function _calculateBottom()
    {
        if ($this->_bottomRankingStats) {
            $ordered = array_keys($this->_bottomRankingStats);
            $this->_rankingCalc['bottom-random'] = array_rand($this->_bottomRankingStats,1 );
            $this->_rankingCalc['bottom-ordered'] = $ordered[0];
            $this->_modes[] = 'bottom-random';
            $this->_modes[] = 'bottom-ordered';
        }
    }
    
    private function _caclulateHighBottom()
    {
        if (
            !in_array('bottom-random', $this->_modes) 
            && count(array_keys($this->_rankingStats, $this->_rankingStats[$this->_highKey])) > 1
        ) {
            $this->_rankingCalc['high-bottom'] = reset(array_keys($this->_rankingStats, $this->_rankingStats[$this->_highKey-1]));
//            $this->_modes[] = 'high-bottom';
        }
    }
    
    /**
     * Only use 'ordered' on even hours, 'random' on odd
     */
    private function _filterModes()
    {
        $hour = (int)date("G", mktime());
        $hour = $hour < 24 ? $hour = 2 : $hour;
        if ( $hour%2 == 0 ) {
            foreach (array('random', 'top-random', 'bottom-ordered') as $remove) {
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
            case 'high-bottom':
                $this->_rankingStatsKey = $this->_rankingCalc['high-bottom'];
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
