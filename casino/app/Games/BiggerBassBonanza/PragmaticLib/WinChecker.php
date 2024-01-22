<?php

namespace VanguardLTE\Games\BiggerBassBonanza\PragmaticLib;

class WinChecker
{
    private $paytable;
    private $paylines;
    public function __construct($gameSettings){
        $paytable = explode(';', $gameSettings['paytable']);
        $paylines = explode(';', $gameSettings['payline']);

        $this->paytable = [];
        foreach ($paytable as $item) {
            $this->paytable[] = explode(',', $item);
        }

        $this->paylines = [];
        foreach($paylines as $payline){
            $this->paylines[] = explode(',', $payline);
        }
    }

    public function getWin($pur, $log, $bet,$slotArea){
        $mo = $slotArea['mo'];
        $mo_t = $slotArea['mo_t'];
        $mo_wpos = [];
        if(array_key_exists('mo_wpos', $slotArea))
          $mo_wpos = $slotArea['mo_wpos'];

        $slotArea = array_chunk($slotArea['SlotArea'], 5);
        var_dump('1_1');
				$totalWin = 0;
        $winSymbols = [];
        foreach($this->paylines as $index => $payline){
						var_dump('1_2_1_'.$index);
            // fetch symbols which are in win line patterns from slot area
            $line = [];
            foreach($payline as $key => $value){
                $line[] = $slotArea[$value][$key];
            }
            // get the number of win symbols in the win line patterns
            $cnt = 1;
            $winSymbol = 0;
            foreach($line as $lineKey => $lineValue){
                if($lineKey == 0){
                    $winSymbol = $lineValue;
                }
                else{
                    if($lineValue != 2 && $winSymbol == 2)
                        $winSymbol = $lineValue;
                    if($winSymbol != 2 && $winSymbol != $lineValue && $lineValue != 2)
                        break;
                    $cnt ++;
                }
            }
            // calc the win amount according to the win symbol and number of symbols
						var_dump('1_2_1_'.$index.'_0');
						var_dump($winSymbol, $cnt);
            $win = round($this->paytable[$winSymbol][count($this->paytable[$winSymbol]) - $cnt] * $bet, 2); // access the paytable element from the end
						if($winSymbol == 7 && $cnt == 5){
              $win = 0;
              foreach($line as $pos)
                $win += $mo[$pos] * $bet;
            }
            var_dump('1_2_1_'.$index.'_1');
            // if the line is a win line then put the positions and win amount to an array
            if ($win > 0){
                $winPositions = [];
                foreach($line as $col => $lineValue){
                    if($col < $cnt)
                        $winPositions[] = $payline[$col] * 5 + $col;
                }
								var_dump('1_2_1_'.$index.'_2');
                $winSymbols[] = ['WinSymbol' => $winSymbol, 'CountSymbols' => $cnt, 'Pay' => number_format($win, 2, ".", ""), 'Positions' => $winPositions, 'l' => $index];
								var_dump('1_2_1_'.$index.'_3');
                $totalWin += $win;
            }
						var_dump('1_2_2_'.$index.'_'.$pur);
          }
          
        // Calculate mo_tw
        $i = 0;
        $mma = 0;
        $mo_tv = 0;
        while($i < 20){
          if(array_key_exists($i, array_count_values($mo_wpos)))
            $mma += $mo[$i];
          if($mo_t[$i] == 'v')
            $mo_tv += $mo[$i];
          $i ++;
        }
        var_dump('1_3_$mma='.$mma, array_count_values($mo_wpos));
        $totalWin += $mma * $mo_tv * $bet;
        foreach($mo_wpos as $pos){
          $mo[$pos] = $mo_tv;
        }

        // return an array with the total payout, the winning symbol, the positions of the winning symbol, the payout for that symbol
        if($mma * $mo_tv > 0)
          return ['TotalWin' => number_format($totalWin, 2, ".", ""), 'WinLines' => $winSymbols, 'mo_tv' => $mo_tv, 'mo_tw' => $mma * $mo_tv * $bet, 'mo' => $mo];
        else return ['TotalWin' => number_format($totalWin, 2, ".", ""), 'WinLines' => $winSymbols];
		}

}
