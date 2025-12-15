<?php

namespace KCESYS\LaravelGenealogy;

use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use KCESYS\Genealogy\Builder;
use Inertia\Inertia;

class Genealogy
{
    protected $source;
    
    public function __construct($source)
    {
        $this->source = $source;
    }

    public static function for($source): self
    {
        return new self($source);
    }

    /**
     * Convert the source data into a React-compatible graph structure.
     * 
     * @param array $config Mapping configuration
     * @return array
     */
    public function toGraph(array $config = []): array
    {
        $items = $this->resolveItems();
        
        $builder = Builder::from($items);

        // customizable mappings via config or defaults
        $idKey = $config['id'] ?? 'id';
        $labelKey = $config['label'] ?? 'name';
        
        $builder->mapId(fn($item) => $item->{$idKey})
                ->mapLabel(fn($item) => $item->{$labelKey});

        // Advanced mappings if closures provided in config
        if (isset($config['parents'])) $builder->mapParents($config['parents']);
        if (isset($config['spouses'])) $builder->mapSpouses($config['spouses']);
        if (isset($config['children'])) $builder->mapChildren($config['children']);
        if (isset($config['siblings'])) $builder->mapSiblings($config['siblings']);
        if (isset($config['data'])) $builder->mapData($config['data']);

        // Build and serialize
        return $builder->build()->jsonSerialize();
    }

    /**
     * Share the genealogy data directly with Inertia.
     * 
     * @param string $propName The prop key to use (default: 'genealogy')
     * @param array $config Mapping configuration
     */
    public function share(string $propName = 'genealogy', array $config = [])
    {
        Inertia::share($propName, $this->toGraph($config));
    }

    protected function resolveItems()
    {
        if ($this->source instanceof Collection) {
            return $this->source;
        }
        if ($this->source instanceof Model) {
            return collect([$this->source]);
        }
        return collect($this->source);
    }
}
