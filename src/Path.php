<?php

namespace Yildirim\Routing;

use Exception;

/**
 *
 */
class Path
{

    /**
     * __construct
     *
     * @param array $data
     * @return void
     */
    public function __construct($data = [])
    {
        if ($data) {
            $this->setAttributes($data);
        }
    }

    /**
     * setAttributes
     *
     * @param  mixed $data
     * @return void
     */
    public function setAttributes($data = [])
    {
        foreach ($data as $key => $val) {
            $this->{$key} = $val;
        }

        $this->setParameters();
    }

    /**
     * middleware
     *
     * @param  mixed $middleware
     * @return Path
     */
    public function middleware($middleware)
    {
        $middleware = is_array($middleware) ? $middleware : func_get_args();

        foreach ($middleware as &$m) {
            $m = Router::getMiddlewareNamespace() . $m;
            if (!class_exists($m)) {
                throw new Exception("Middleware:['$m'] attached to Route:['$this->uri'] does not exist");
            }
        }

        $this->middleware = array_merge($this->middleware ?? [], $middleware);

        return $this;
    }

    /**
     * setParameters
     *
     * @return void
     */
    private function setParameters()
    {
        $this->parameters = collect();
        foreach (explode("/", rtrim($this->uri, "/")) as $index => $param) {
            $this->parameters[] = new Parameter($param, $index);
        }
    }

    /**
     * withoutSlugs
     *
     * @return Collection
     */
    public function withoutSlugs()
    {
        return $this->parameters->filter(function ($p) {
            return !$p->isSlug();
        });
    }

    /**
     * slugs
     *
     * @return Collection
     */
    public function slugs()
    {
        return $this->parameters->filter(function ($param) {
            return $param->isSlug();
        });
    }

    /**
     * removeSlugValues
     *
     * @param  mixed $slugs
     * @return Collection
     */
    public function withoutSlugValues($slugs)
    {
        $positions = $slugs->pluck('position');

        return $this->removeSlugsAtPositions($positions);
    }

    /**
     * removeSlugsAtPositions
     *
     * @param  mixed $positions
     * @return Collection
     */
    private function removeSlugsAtPositions($positions)
    {
        return $this->parameters->filter(function ($param) use ($positions) {
            return !in_array($param->position, $positions);
        });
    }

    /**
     * valuesAtPositions
     *
     * @return Collection
     */
    private function valuesAtPositions($positions)
    {
        return $this->parameters->filter(function ($param) use ($positions) {
            return in_array($param->position, $positions);
        });
    }

    /**
     * getSlugValues
     *
     * @param  mixed $path
     * @return array
     */
    public function getSlugValues(Path $path)
    {
        $positions = $this->slugs()->pluck('position');

        $slugs = $this->slugs()->map(function ($slug) {

            return $slug->slugIdentifier();

        })->toArray();

        $slugValues = $path->valuesAtPositions($positions)->pluck('value');

        //ensure optional/required parameters are correct length for combine.
        while (count($slugs) > count($slugValues)) {
            array_pop($slugs);
        }

        return array_combine($slugs, $slugValues);

    }

    /**
     * hasOptionalParameters
     *
     * @return Collection
     */
    private function optionalSlugs()
    {
        return $this->slugs()->filter(function ($slug) {
            return $slug->isOptional();
        });
    }

    /**
     * requiredSlugs
     *
     * @return Collection
     */
    private function requiredSlugs()
    {
        return $this->slugs()->filter(function ($slug) {
            return !$slug->isOptional();
        });
    }

    /**
     * getSlugAtPosition
     *
     * @param  mixed $position
     * @return Parameter
     */
    public function getSlugAtPosition($position)
    {
        return $this->slugs()->values()[$position] ?? null;
    }

    /**
     * requiredParameters
     *
     * @return Collection
     */
    private function requiredParameters()
    {
        return $this->parameters->filter(function ($param) {
            return ($param->isSlug() && !$param->isOptional()) || !$param->isSlug();
        });
    }

    /**
     * matches
     *
     * @param  mixed $path
     * @return bool
     */
    public function matches($path)
    {
        //exact route match. no slugs.
        if (rtrim($this->uri, "/") == rtrim($path->uri, "/")) {
            return true;
        }

        //route with slugs matches requested url.
        if (
            $this->withoutSlugs() == $path->withoutSlugValues($this->slugs()) &&
            (
                $this->requiredParameters()->count() <= $path->parameters->count() ||
                $this->parameters->count() == $path->parameters->count()
            )
        ) {
            return true;
        }

        if ($this->parameters->count() != $path->parameters->count()) {
            return false;
        }

        return false;
    }

}
