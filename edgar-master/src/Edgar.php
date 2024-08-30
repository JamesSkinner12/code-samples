<?php
/**
 * Created by PhpStorm.
 * User: james
 * Date: 4/28/19
 * Time: 2:29 PM
 */

namespace Edgar;

class Edgar
{
    public static function suggestSearchName($search)
    {
        $replace = [
            "Inc." => "Inc",
            "Incorporated" => "Inc",
            "," => "",
            "Corporation" => "Corp"
        ];
        foreach ($replace as $itm => $val) {
            $search = str_replace($itm, $val, $search);
        }
        return $search;
    }

    public static function mostValidItem($word, $terms = [])
    {
        $output = [];
        foreach ($terms as $term) {
            similar_text($word, $term, $similarity);
            if (empty($output['similarity']) || $output['similarity'] < $similarity) {
                $output = ['term' => $term, 'similarity' => $similarity];
            }
        }
        return $output['term'];
    }
}