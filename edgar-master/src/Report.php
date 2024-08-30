<?php
/**
 * Created by PhpStorm.
 * User: james
 * Date: 4/29/19
 * Time: 11:15 AM
 */

namespace Edgar;


use Edgar\Schemas\Report\DocumentItemSchema;
use Edgar\Schemas\Report\ReportContextItemSchema;
use Edgar\Schemas\Report\ContextPeriodSchema;
use Edgar\Schemas\Report\ReportSchema;
use Edgar\ReportSchema as Schema;
use Edgar\Tools\DocumentLoader;
use Edgar\Country;
use Edgar\Invest;
class Report extends DocumentLoader
{
    protected $url;
    protected $symbol;
    protected $meta;
    protected $schema;
    protected $stash;
    protected $contexts;

    /**
     * Report constructor.
     * @param $url
     * @throws \Exception
     */
    public function __construct($url)
    {
        $this->url = $url;
        $this->contents = $this->load($url, true);
        $this->contexts = [];
        $this->meta = $this->buildMeta();
        $this->stash = [];
    }

    public function buildMeta()
    {
        $schema = new Schema($this->schemaFile());
        $gaapPath = $schema->gaapImport();
        $deiPath = $schema->deiImport();
        $gaapData = [
            'gaap' => $gaapPath,
            'labels' => str_replace(".xsd", ".xml", str_replace("us-gaap-", "us-gaap-lab-", $gaapPath)),
            'docs' => str_replace(".xsd", ".xml", str_replace("us-gaap-", "us-gaap-doc-", $gaapPath))
        ];
        $deiData = $this->loadDataFormat('dei', $schema->deiImport());
        $metaCollection = [
            'dei' => new Dei($deiData),
            'gaap' => new Gaap($gaapData),
            'schema' => $schema,
            'srt' => new Srt(),
            'countries' => new Country()
        ];
        if (!empty($schema->importPath('invest'))) {
            $investData = new Invest($this->loadDataFormat('invest', $schema->importPath('invest')));
            $metaCollection['invest'] = $investData;
        }
        return new Meta($metaCollection);
    }

    public function loadDataFormat($name, $path)
    {
        return [
            $name => $path,
            'labels' =>  str_replace(".xsd", ".xml", str_replace($name . "-", $name . "-lab-", $path)),
            'docs' => str_replace(".xsd", ".xml", str_replace($name . "-", $name . "-doc-", $path))
        ];
    }

    public function meta()
    {
        return $this->meta;
    }

    public function schemaFile()
    {
        foreach ($this->contents as $key => $values) {
            if (stristr($key, ':schemaRef')) {
                $uri = $values['@xlink:href'];
                $handle = explode("/", $this->url);
                $handle[count($handle) - 1] = $uri;
                return implode("/", $handle);
            }
        } 
        return false;
    }

    public function schema()
    {
        return $this->meta()->getCollectionItem("schema");
    }

    public function data()
    {
        return $this->contents;
    }

    public function setSymbol($symbol)
    {
        $this->symbol = strtolower($symbol);
    }

    public function details()
    {
        return ReportSchema::serialize($this->contents);
    }

    public function dei()
    {
        foreach ($this->details()['dei'] as $key => $values) {
            $output[$key] = DocumentItemSchema::serialize($values);
        }
        return (!empty($output)) ? $output : [];
    }

    public function gaap($includeTextblocks = false)
    {
        foreach ($this->details()['gaap'] as $key => $values) {
            if (!$includeTextblocks && stristr($key, "TextBlock")) {
                continue;
            } else {
                $output[$key] = $this->buildWithSchema($values);
            }
        }
        return (!empty($output)) ? $output : [];
    }

    public function usableKeys()
    {
        $keys = array_keys($this->contents);
        return array_filter($keys, function ($key) {
            $patterns = [
                '@',
                'xbrli',
                'xbrll',
                'link:schemaRef',
                'context',
                'unit'
            ];
            foreach ($patterns as $pattern) {
                if (stristr($key, $pattern)) {
                    return false;
                }
            }
            return true;
        });
    }

    public function getById($id)
    {
        $func = function ($item) {
            if (stristr($item, ':')) {
                return explode(":", $item)[1];
            }
            return $item;
        };
        $ids = array_map($func, array_keys($this->contents));
        return $this->contents[array_keys($this->contents)[array_search($id, $ids)]];
    }

    protected function buildWithSchema($data)
    {
        if (!$this->isMulti($data)) {
            $tmp = DocumentItemSchema::serialize($data);
            return $this->verifyIntegrity($data, $tmp);
        } else {
            foreach ($data as $item) {
                $tmp = DocumentItemSchema::serialize($item);
                $output[] = $this->verifyIntegrity($item, $tmp);
            }
            return (!empty($output)) ? $output : [];
        }
    }

    protected function verifyIntegrity($input, $output)
    {
        if (isset($input['#'])) {
            if ($input['#'] === '0') {
                $output['value'] = 0;
            }
            if (isset($input['@xsi:nil']) && $input['@xsi:nil'] === "true") {
                $output['value'] = 'nil';
            }
        }
        return $output;
    }

    public function isMulti($data)
    {
        return (!empty($data)) ? is_int(array_keys($data)[0]) : false;
    }

    public function buildContextOverrides()
    {
        foreach ($this->contexts() as $context) {
            if (!empty($context['period'])) {
                $overrides['period'] = 'period';
            }
            if (!empty($context['entity'])) {
                $overrides['entity'] = 'entity';
                if (!empty($context['entity']['segment'])) {
                    $overrides['segment'] = 'entity|segment';
                }
                if (!empty($context['entity']['identifier'])) {
                    $overrides['cik'] = 'entity|identifier|#';
                }
            }
        }
        return (!empty($overrides)) ? $overrides : [];
    }

    public function contexts()
    {
        $contextKey = (!empty($this->contents['context'])) ? 'context' : 'xbrli:context';
        return $this->contents[$contextKey];
    }

    public function contextItems()
    {
        $overrides = $this->buildContextOverrides();
        foreach ($this->contexts() as $context) {
            $itm = ReportContextItemSchema::serialize($context, $overrides);
            if (!empty($itm['period'])) {
                $periodOverrides = [];
                foreach (['instant', 'startDate', 'endDate'] as $periodKey) {
                    if (!empty($itm['period'][$periodKey])) {
                        $periodOverrides[str_replace("Date", "", $periodKey)] = $periodKey;
                    }
                }
                $itm['period'] = ContextPeriodSchema::serialize($itm['period'], $periodOverrides);
            }
            $output[] = $itm;
        }
        return (!empty($output)) ? $output : [];
    }

    public function stashContextMeta($id, $data)
    {
        if (empty($this->stash['context'])) {
            $this->stash['context'] = [];
        }
        if (!in_array($id, array_keys($this->stash['context']))) {
            $this->stash['context'][$id] = $data;
        }
    }

    public function getContextMeta($contextId)
    {
        if (!empty($this->stash['context']) && in_array($contextId, array_keys($this->stash['context']))) {
            return $this->stash['context'][$contextId];
        }
        $detail = $this->meta->findDetail($contextId);
        $this->stashContextMeta($contextId, $detail);
        return $detail;
    }

    public function findContextById($id)
    {
        if (!in_array($id, array_keys($this->contexts))) {
            $ids = array_column($this->contextItems(), 'id');
            $context = $this->contextItems()[array_search($id, $ids)];
            if (!empty($context['segment']['members'])) {
                if (!$this->isMulti($context['segment']['members'])) {
                    $members = $context['segment']['members'];
                    $context['segment']['members'] = [$members];
                }
                foreach ($context['segment']['members'] as &$member) {
                    $member['dimension'] = $this->getContextMeta($member['dimension']);
                    $member['value'] = $this->getContextMeta($member['value']);
                }
            }
            $this->contexts[$id] = $context;
        }
        return (!empty($id)) ? $this->contexts[$id] : null;
    }

    public function logTaskTime($start, $end, $task, $key = null)
    {
        $diff = round($end - $start, 3);
        $this->stash['timings'][$task][] = [
            'start' => $start,
            'end' => $end,
            'run_time' => $diff
        ];
        if ($key !== null && $diff > ($this->getTaskAverages()[$task]) && !in_array($key, $this->stash['problems'])) {
            $this->stash['problems'][] = $key;
        }
    }

    public function getTaskAverages()
    {
        foreach ($this->stash['timings'] as $task => $values) {
            $output[$task] = array_sum(array_column($values, 'run_time')) / count(array_column($values, 'run_time'));
        }
        return (!empty($output)) ? $output : [];
    }

    public function findItem($id)
    {
        $func = function ($item) {
            if (stristr($item, ':')) {
                return explode(":", $item)[1];
            }
            return $item;
        };
        $id = $func($id);
        $data['items'] = $this->buildWithSchema($this->getById($id));
        if (!$this->isMulti($data['items'])) {
            $item = $data['items'];
            unset($data['items']);
            $item['context'] = $this->findContextById($item['context']);
            $data['items'] = [$item];
        } else {
            foreach ($data['items'] as &$item) {
                $item['context'] = $this->findContextById($item['context']);
            }
        }
        $data['details'] = $this->meta->findDetail($id);
        return $data;
    }

    public function outputTimings()
    {
        foreach ($this->getTaskAverages() as $tag => $time) {
            echo "Average time to " . $tag . ": " . $time . "\n";
        }
        if (!empty($this->stash['problems'])) {
            echo "Problem tasks: \n";
            foreach ($this->stash['problems'] as $problem) {
                echo $problem . "\n";
            }
        }
    }

    public function collection()
    {
        foreach ($this->usableKeys() as $key) {
            $output[$key] = $this->findItem($key);
        }
        return (!empty($output)) ? $output : [];
    }

    protected function collectContextNames()
    {
        $contexts = [];
        foreach ($this->contents as $key => $value) {
            if (is_array($value)) {
                if (!$this->isMulti($value)) {
                    if (!empty($value['@contextRef'])) {
                        $contexts[] = $value['@contextRef'];
                    }
                } else {
                    foreach ($value as $item) {
                        if (!empty($item['@contextRef'])) {
                            $contexts[] = $item['@contextRef'];
                        }
                    }
                }
            }
        }
        return array_values(array_unique($contexts));
    }

    public function prebuildContexts()
    {
        foreach ($this->collectContextNames() as $contextName) {
            $output[] = $this->findContextById($contextName);
        }
    }
}
