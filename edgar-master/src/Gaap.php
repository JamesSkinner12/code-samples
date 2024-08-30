<?php
/**
 * Created by PhpStorm.
 * User: james
 * Date: 4/30/19
 * Time: 8:58 PM
 */

namespace Edgar;

use Edgar\Schemas\Meta\DeiDocItemSchema;
use Edgar\Schemas\Meta\GaapItemSchema;
use Edgar\Tools\DocumentLoader;

class Gaap extends DocumentLoader
{
    const GAAP_URL = "http://xbrl.fasb.org/us-gaap/2019/elts/us-gaap-2019-01-31.xsd";
    const GAAP_DOCS_URL = "http://xbrl.fasb.org/us-gaap/2019/elts/us-gaap-doc-2019-01-31.xml";
    const GAAP_LABELS_URL = "http://xbrl.fasb.org/us-gaap/2019/elts/us-gaap-lab-2019-01-31.xml";
    protected $docs;
    protected $labels;
    protected $items;
    protected $labelItems;
    protected $docItems;

    /**
     * Gaap constructor.
     * @throws \Exception
     */
    public function __construct($overrides = null)
    {
        $this->contents = $this->load((!empty($overrides['gaap'])) ? $overrides['gaap'] : self::GAAP_URL);
        $this->docs = $this->load((!empty($overrides['docs'])) ? $overrides['docs'] : self::GAAP_DOCS_URL);
        $this->labels = $this->load((!empty($overrides['labels'])) ? $overrides['labels'] : self::GAAP_LABELS_URL);
    }

    public function data()
    {
        return $this->contents;
    }

    public function docs()
    {
        return $this->docs;
    }

    public function labels()
    {
        return $this->labels;
    }

    public function docItems()
    {
        if (empty($this->docItems)) {

            $func = function ($item) {
                return DeiDocItemSchema::serialize($item);
            };
            $this->docItems = array_map($func, $this->docs['link:labelLink']['link:label']);
        }
        return $this->docItems;
    }

    public function labelItems()
    {
        if (empty($this->labelItems)) {
            $func = function ($item) {
                return DeiDocItemSchema::serialize($item);
            };
            $this->labelItems = array_map($func, $this->labels['link:labelLink']['link:label']);
        }
        return $this->labelItems;
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

    public function locators()
    {
        return array_map(function ($item) {
            return str_replace(["lab_", "label_"], "", $item['label']);
        }, $this->labelItems());
    }

    public function docLocators()
    {
        return array_map(function ($item) {
            return str_replace(["lab_", "label_"], "", $item['label']);
        }, $this->docItems());
    }

    public function labelsAndDescriptions()
    {
        $labelItems = $this->labelItems();

        $func = function ($item) {
            $item['locator'] = str_replace(["lab_", "label_"], "", $item['label']);
            $description = $this->findDescription($item['label']);
            if (!empty($description)) {
                $item['description'] = $description['value'];
            }
            return $item;
        };
        return array_map($func, $labelItems);
    }

    public function findDescription($id)
    {
        $items = $this->docLocators();
        $idx = array_search($id, $items);
        if (!empty($idx) || $idx === 0) {
            return $this->docItems()[$idx];
        }
        return null;
    }

    public function findItem($id)
    {
        $item = $this->items()[array_search($id, array_column($this->items(), 'name'))];
        $locators = $this->locators();
        $idx = array_search($id, $locators);
        if (!empty($idx) || $idx === 0) {
            $labelItems = $this->labelItems()[$idx];
            $description = $this->findDescription($id);
            if (!empty($description)) {
                $labelItems['description'] = $description['value'];
            }
            $item['meta'] = $labelItems;
            return $item;
        }
        return null;
    }

    public function hasItem($id)
    {
        $col = array_column($this->items(), 'name');
        return (!empty(array_search($id, $col)));
    }
}
