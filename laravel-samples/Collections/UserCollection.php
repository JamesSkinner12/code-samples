<?php
/**
 * Created by PhpStorm.
 * User: james
 * Date: 4/3/19
 * Time: 2:42 PM
 */

namespace App\Collections;


use Illuminate\Database\Eloquent\Collection;

class UserCollection extends Collection
{

    public function monthlyCounts()
    {
        $yearFunc = function ($item) {
            return date("Y", strtotime($item['created_at']));
        };
        $monthFunc = function ($item) {
            return date("F", strtotime($item['created_at']));
        };
        $monthlyStats = [];
        foreach ($this->items as $user) {
            if (empty($monthlyStats[$yearFunc($user)])) {
                $monthlyStats[$yearFunc($user)] = [];
            }
            if (empty($monthlyStats[$yearFunc($user)][$monthFunc($user)])) {
                $monthlyStats[$yearFunc($user)][$monthFunc($user)] = 0;
            }
            $monthlyStats[$yearFunc($user)][$monthFunc($user)]++;
        }
        return $monthlyStats;
    }

    public function lastYearsCounts()
    {
        $summary = $this->monthlyCounts();

        $counts = [];
        foreach (array_keys($summary) as $year) {
            foreach (array_keys($summary[$year]) as $month) {
                $counts[] = ['year' => $year, 'month' => $month, 'count' => $summary[$year][$month]];
            }
        }
        return array_reverse(array_slice(array_reverse($counts), 0, 12));
    }
}