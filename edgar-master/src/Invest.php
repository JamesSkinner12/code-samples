<?php
/**
 * Created by PhpStorm.
 * User: james
 * Date: 4/30/19
 * Time: 9:01 PM
 */

namespace Edgar;

use Edgar\Schemas\Meta\DeiDocItemSchema;
use Edgar\Schemas\Meta\DeiItemLocatorSchema;
use Edgar\Schemas\Meta\GaapItemSchema;
use Edgar\Tools\DocumentLoader;

class Invest extends DocumentLoader
{

    protected $docs;
    protected $labels;
    protected $items;

    /**
     * Dei constructor.
     * @throws \Exception
     */
    public function __construct($paths)
    {
        $this->contents = $this->load($paths['invest'], true);
        $this->docs = $this->load($paths['docs'], true);
        $this->labels = $this->load($paths['labels'], true);
    }

    public function data()
    {
        return $this->contents;
    }

    public function types()
    {
        return $this->contents['xs:complexType'];
    }

    public function docs()
    {
        return $this->docs;
    }

    public function labels()
    {
        return $this->labels;
    }

    public function docLocators()
    {
        $func = function ($item) {
            return DeiItemLocatorSchema::serialize($item);
        };
        return array_map($func, $this->docs['link:labelLink']['link:loc']);
    }

    public function docItems()
    {
        $func = function ($item) {
            return DeiDocItemSchema::serialize($item);
        };
        return array_map($func, $this->docs['link:labelLink']['link:label']);
    }

    public function labelItems()
    {
        $func = function ($item) {
            return DeiDocItemSchema::serialize($item);
        };
        return array_map($func, $this->labels['link:labelLink']['link:label']);
    }


    public function items()
    {
        if (empty($this->items)) {
            $func = function ($item) {
                return GaapItemSchema::serialize($item);
            };
            $this->items = array_map($func, $this->contents['xs:element']);
        }
        return $this->items;
    }

    public function labelsAndDescriptions()
    {
        $labelItems = $this->labelItems();
        foreach ($labelItems as &$labelItem) {
            $labelItem['description'] = $this->findDescription($labelItem['label'])['value'];
            $labelItem['locator'] = str_replace(["lab_", "label_"], "", $labelItem['label']);
        }
        return $labelItems;
    }

    public function hasItem($id)
    {
        $id = (stristr($id, "invest:")) ? str_replace("invest:", "", $id) : $id;
        $func = function($item) {
            $replace = (stristr($item['id'], "invest:")) ? "invest:" : "invest_";
            $item['id'] = str_replace($replace, "", $item['id']);
            return $item;
        };
        $items = array_map($func, $this->items());
        return (!empty(array_search($id, array_column($items, 'id'))));
    }

    public function findItem($id)
    {
        $items = $this->items();
        $id = str_replace((stristr($id, "invest:")) ? "invest:" : "invest_", "",  $id);
        $idx = array_search($id, array_column($items, 'name'));
        if (!empty($idx) || $idx === 0) {
            $item = $items[$idx];
            $item['meta'] = $this->findLabelAndDescription($id);
            return $item;
        }
        return null;
    }

    public function findDescription($id)
    {
        $items = $this->docItems();
        return $items[array_search($id, array_column($items, 'label'))];
    }

    public function findLabelAndDescription($id)
    {
        $items = $this->labelsAndDescriptions();
        return $items[array_search($id, array_column($items, 'locator'))];
    }

    public function collection()
    {
        $items = $this->items();
        foreach ($items as &$item) {
            $item['meta'] = $this->findLabelAndDescription($item['label']);
        }
        return $items;
    }
}
