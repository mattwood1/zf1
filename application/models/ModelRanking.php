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
    private $_highTop;
    private $_highBottom;

    public function __construct($ignoreModel = null) {
        $this->_rankingStats = $this->getRankingStats(2, true);
        $this->_ignoreModel = $ignoreModel;
        
        $this->_topHigh = max(array_keys($this->getRankingStats(1, true)));
        $this->_topLow = $this->_topHigh - floor(($this->_topHigh / 100) * $this->_factor);
        
        $this->_highKey = reset(array_keys($this->_rankingStats, max($this->_rankingStats)));
        
        if (count(array_keys($this->_rankingStats, $this->_rankingStats[$this->_highKey])) > 1) {
            //_d('More than 1 highkey');
            $highBlock = array_keys($this->_rankingStats, $this->_rankingStats[$this->_highKey]);
            if ($highBlock[0] >= $this->_highKey) {
                //_d('Get this too');
                $highBlock = array_merge($highBlock, array_keys($this->_rankingStats, $this->_rankingStats[$this->_highKey]-1));
                $highBlock = array_merge($highBlock, array_keys($this->_rankingStats, $this->_rankingStats[$this->_highKey]-2));
            }
            $highBlockKey = $highBlock[0] - 1;
        } else {
            $highBlock = array_keys($this->_rankingStats, $this->_rankingStats[$this->_highKey]);
            $highBlock = array_merge($highBlock, array_keys($this->_rankingStats, $this->_rankingStats[$this->_highKey]-1));
            if (!empty($highBlock) && $highBlock[0] >= $this->_highKey) {
                //_d('HighBlock above highkey');
                $highBlock = array_merge($highBlock, array_keys($this->_rankingStats, $this->_rankingStats[$this->_highKey]-2));
                $highBlockKey = $highBlock[0] - 1;
            } else {
                //_d('highblock normal');
                if (array_keys($this->_rankingStats, $this->_rankingStats[$this->_highKey]-2)) {
                    //_d('Moving block');
                    $highBlock = array_merge($highBlock, array_keys($this->_rankingStats, $this->_rankingStats[$this->_highKey]-2));
                    $highBlock = array_merge($highBlock, array_keys($this->_rankingStats, $this->_rankingStats[$this->_highKey]-3));
                }
                if (!empty($highBlock)) {
                    $highBlockKey = $highBlock[0] - 2;
                } else {
                    $highBlockKey = 0;
                }
            }
        }

        sort($highBlock);

        // reversed array to check sequential numbers
        $reversedHighBlock = array_reverse($highBlock, true);
        $reversed = reset($reversedHighBlock);
        foreach ($reversedHighBlock as $key => $count) {
            if ($reversed != $count) {
                unset($highBlock[$key]);
            }
            $reversed--;
        }
        
        $this->_highTop = end($highBlock);
        $this->_highBottom = reset($highBlock);
        
       // _d($highBlock, $this->_highBottom, $this->_highTop);
        
        $this->_calculateArrays();
        $this->_filterModes();
        $this->_chooseMode();
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
        $models = clone($this->_models); // copy models as it is modified.
        
        // Prevent model flow
        if ($this->_ignoreModel && $this->getModelCount() > 2) {
            foreach ($models as $modelKey => $model) {
                if ($model->ID == $this->_ignoreModel->ID) {
                    unset($models[$modelKey]);
                }
            }
        }
        
        $modelArrayKeys = array_keys($models->toArray());
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
            $this->_modes[] = 'top-random';
            $this->_modes[] = 'top-ordered';
        }
    }
    
    private function _calculateBottom()
    {
        $bottomRankingStats = $this->_rankingStats;
        foreach ($bottomRankingStats as $bottomKey => $bottomStat) {
/*
_d(array(
	'bottomKey' =>$bottomKey,
        'next bottom count' => (array_key_exists($bottomKey+1, $bottomRankingStats) && $bottomRankingStats[$bottomKey+1]),
        'highkey - 1' => $this->_rankingStats[$this->_highKey]-1
));
*/
            if (
                $bottomKey >= ($this->_highBottom)
             || (array_key_exists($bottomKey+1, $bottomRankingStats) && $bottomRankingStats[$bottomKey+1] >= ($this->_rankingStats[$this->_highKey]-1) ) ) {
                unset($bottomRankingStats[$bottomKey]);
// _d('Unset Key ' . $bottomKey);
                continue;
            }

            if ( $bottomStat > $this->_rankingStats[$this->_highKey] ) {
                unset($bottomRankingStats[$bottomKey]);
                continue;
            }
            
        }
        
        if ($bottomRankingStats) {
            $ordered = array_keys($bottomRankingStats);
            $this->_rankingCalc['bottom-random'] = array_rand($bottomRankingStats,1 );
            $this->_rankingCalc['bottom-ordered'] = $ordered[0];
            $this->_modes[] = 'bottom-random';
            $this->_modes[] = 'bottom-ordered';
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
