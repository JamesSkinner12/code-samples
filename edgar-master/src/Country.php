<?php

namespace Edgar;
use Edgar\Tools\DocumentLoader;
use Edgar\Schemas\Meta\GaapItemSchema;
use Edgar\Schemas\Meta\DeiDocItemSchema;

class Country extends DocumentLoader
{
    const COUNTRIES_URL = "https://xbrl.sec.gov/country/2017/country-2017-01-31.xsd";
    const COUNTRIES_LABELS_URL = "https://xbrl.sec.gov/country/2017/country-lab-2017-01-31.xml";
    protected $labels;

    public function __construct()
    {
        $this->contents = $this->load(self::COUNTRIES_URL);
        $this->labels = $this->load(self::COUNTRIES_LABELS_URL);
    }
 
    public function items()
    {
        $func = function($item) {
            return GaapItemSchema::serialize($item);
        };
        return array_map($func, $this->contents['xs:element']);
    }

    public function labels()
    {
        return array_map(function($item) {
            return DeiDocItemSchema::serialize($item);
        }, $this->labels['link:labelLink']['link:label']);
    }

    public function convertID($id)
    {
        if (!stristr($id, ":") && !stristr($id, "_")) {
            return "country:" . $id;
        }
        return $id;
    }

    public function hasItem($id)
    {
        $id = str_replace(":", "_", $this->convertID($id));

        $idx = array_search($id, array_column($this->items(), 'id'));
        return (!empty($idx));
    }

    public function findItem($id)
    {
        $id = str_replace(":", "_", $this->convertID($id));
        $item = $this->items()[array_search($id, array_column($this->items(), 'id'))];
        if (!empty($item)) {
            $item['meta'] = $this->labels()[array_search("lab_" . $item['name'], array_column($this->labels(), 'label'))];
            return $item;
        }
        return false;
    }
}
