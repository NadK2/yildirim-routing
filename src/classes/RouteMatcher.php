<?php

namespace Yildirim\Routing;

class RouteMatcher
{

/**
 * matches
 *
 * @param  mixed $path
 * @return bool
 */
    public static function matches($route, $path)
    {
        //exact route match. no slugs.
        if (rtrim($route->uri, "/") == rtrim($path->uri, "/")) {
            return true;
        }

        //route with slugs matches requested url.
        if ($route->withoutSlugs() == $path->withoutSlugValues($route->slugs()) &&
            ($route->requiredParameters()->count() <= $path->parameters->count() || $route->parameters->count() == $path->parameters->count())
        ) {

            $slugValues = $route->getSlugValues($path);
            //run regex.
            foreach ($route->slugs()->toArray() as $slug) {
                if ($slug->regex && isset($slugValues[$slug->id])) {

                    if (!preg_match('/' . trim($slug->regex, "/") . '/', $slugValues[$slug->id])) {
                        return false;
                    }

                }
            }

            return true;
        }

        if ($route->parameters->count() != $path->parameters->count()) {
            return false;
        }

        return false;
    }
}
