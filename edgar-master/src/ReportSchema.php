<?php
/**
 * Created by PhpStorm.
 * User: james
 * Date: 5/2/19
 * Time: 12:28 PM
 */

namespace Edgar;


use Edgar\Schemas\Meta\DeiDocItemSchema;
use Edgar\Schemas\Meta\GaapItemSchema;
use Edgar\Tools\DocumentLoader;

class ReportSchema extends DocumentLoader
{
    protected $url;
    protected $data;
    protected $labels;

    /**
     * ReportSchema constructor.
     * @param $url
     * @throws \Exception
     */
    public function __construct($url)
    {
        $this->contents = $this->load($url, false);
        $this->labels = $this->load(str_replace(".xsd", "_lab.xml", $url), false);
    }

    public function imports()
    {
        $keys = array_keys($this->contents);
        foreach ($keys as $key) {
            if (stristr($key, "import")) {
                return $this->contents[$key];
            }
        }
        return null;
    }

    public function gaapImport()
    {
        return $this->importPath('us-gaap', ['negated']);
    }

    public function deiImport()
    {
        return $this->importPath('dei');
    }

    public function importPath($name, $ignored = [])
    {
        foreach ($this->imports() as $import) {
            if (stristr($import['@schemaLocation'], $name) && stristr($import['@namespace'], $name)) {
	        foreach ($ignored as $ignore) {
                    if (stristr($import['@schemaLocation'], $ignore)) {
			continue;
                    }
                }
                return $import['@schemaLocation'];
            }
        }
        return null;
    }

    public function elements()
    {
        foreach (['element', 'xs:element', 'xsd:element'] as $keyName) {
            if (!empty($this->contents[$keyName])) {
                return $this->contents[$keyName];
            }
        }
        return null;
    }

    public function labelLinkPath()
    {
        foreach (array_keys($this->labels) as $key) {
            if (stristr($key, "labelLink")) {
                return $key;
            }
        }
        return null;
    }

    public function labelLocatorPath()
    {
        if (!empty($this->labelLinkPath())) {
            foreach (array_keys($this->labels[$this->labelLinkPath()]) as $key) {
                if (stristr($key, "loc")) {
                    return $key;
                }
            }
        }
        return null;
    }

    public function labelArcPath()
    {
        if (!empty($this->labelLinkPath())) {
            foreach (array_keys($this->labels[$this->labelLinkPath()]) as $key) {
                if (stristr($key, "labelArc")) {
                    return $key;
                }
            }
        }
        return null;
    }

    public function labelLinks()
    {
        $labelsPath = $this->labelLinkPath();
        $locPath = $this->labelLocatorPath();
        return $this->labels[$labelsPath][$locPath];
    }

    public function labelArcs()
    {
        $labelsPath = $this->labelLinkPath();
        $arcsPath = $this->labelArcPath();
        return $this->labels[$labelsPath][$arcsPath];
    }

    public function debug()
    {
        return $this->labels[$this->labelLinkPath()];
    }

    public function details()
    {
        $func = function ($item) {
            return GaapItemSchema::serialize($item);
        };
        return array_map($func, $this->elements());
    }

    public function locator($id)
    {
        $id = str_replace(":", "_", $id);
        $locators = [];
        foreach ($this->labelLinks() as $locator) {
            $val = explode("#", $locator['@xlink:href'])[1];
            if ($val == $id) {
                $itm = $locator['@xlink:label'];
                if ($arc = $this->locatorArc($itm)) {
                    return $arc;
                }
                return $itm;
            }
        }
        return false;
    }

    public function locatorArc($locator)
    {
        $arcs = [];
        foreach ($this->labelArcs() as $labelArc) {
            if ($labelArc['@xlink:from'] === $locator) {
                $arcs[] = $labelArc['@xlink:to'];
            }
        }
        return (!empty($arcs)) ? $arcs : false;
    }

    public function labelLocators()
    {
        $func = function ($item) {
            if (stristr($item, "_")) {
                $handle = explode("_", $item);
                return $handle[1];
            }
            return $item;
        };
        $col = array_column($this->labelItems(), 'label');
        return array_map($func, $col);
    }

    public function labels()
    {
        return $this->labels;
    }

    public function labelElements()
    {
        $labelsKey = (!empty($this->labels['link:labelLink'])) ? 'link:labelLink' : 'labelLink';
        $labelItemsKey = (!empty($this->labels[$labelsKey]['link:label'])) ? 'link:label' : 'label';
        return $this->labels[$labelsKey][$labelItemsKey];
    }

    public function labelItems()
    {
        $func = function ($item) {
            return DeiDocItemSchema::serialize($item);
        };
        return array_map($func, $this->labelElements());
    }

    public function hasItem($id)
    {
        $func = function ($item) {
            if (stristr($item, ':')) {
                return explode(":", $item)[1];
            }
            return $item;
        };
        $id = $func($id);
        $col = array_column($this->details(), 'name');
        return (in_array($id, $col));
    }

    public function itemCouldMatch($id, $label)
    {
        $handle = explode("_", $label);
        if (is_array($handle) && count($handle) > 0) {
            if (is_int($handle[1])) {
                return $id === $handle[0];
            }
        }
        return false;
    }

    public function find($id)
    {
        $id = str_replace(":", "_", $id);
        $id = str_replace(["label_"], "", $id);
        $re = '/(.*)([^a-z]|[^A-z])(' . $id . ')(([^a-z]|[^A-z])|$)/mi';
        foreach ($this->labelItems() as $key => $values) {
            preg_match($re, $values['label'], $matches);
            if (!empty($matches)) {
                $output[] = array_merge($values, ['id' => $matches[3], 'key' => $key]);
                unset($matches);
             }
        }
        return (!empty($output)) ? $output : [];
    }

    public function findItem($id)
    {
        $placedId = $id;
        $id = str_replace("_", ":", $id);
        $func = function ($item) {
            if (stristr($item, ':')) {
                return explode(":", $item)[1];
            }
            return $item;
        };
        $id = $func($id);
        $col = array_column($this->details(), 'name');
        $idx = array_search($id, $col);
        try {
            $detail = $this->details()[$idx];
            $output = [];
            $locators = $this->locator($detail['id']);
            $locators[] = $placedId;
            foreach ($locators as $locator) {
                foreach ($this->find($locator) as $vals) {
                    if ($vals['role'] === "http://www.xbrl.org/2003/role/label") {
                        $output = array_merge($output, $vals);
                    } elseif ($vals['role'] === "http://www.xbrl.org/2003/role/terseLabel") {
                        $output['readable_label'] = $vals['value'];
                    } elseif ($vals['role'] === "http://www.xbrl.org/2003/role/documentation") {
                        $output['description'] = $vals['value'];
                    }
                }
            }
            $detail['meta'] = (!empty($output)) ? $output : [];
            return $detail;
        } catch (\Exception $e) {
            return null;
        }
    }
}
