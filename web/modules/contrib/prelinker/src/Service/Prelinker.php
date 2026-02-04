<?php

namespace Drupal\prelinker\Service;

class Prelinker
{
    /**
     * Get current node id
     */
    public function get_nodeid()
    {
        return \Drupal::service('path.current')->getPath();
    }

    /**
     * Get the current page path
     */
    public function get_path()
    {
        $nodeid = $this->get_nodeid();
        $parameter = \Drupal::request()->query->get('q') ?? '';

        $path = explode('/', trim($parameter, '/'));
        if ($path[0] == "" && \Drupal::service('path.matcher')->isFrontPage() != true) {
            $path = explode('/', trim(\Drupal::service('path_alias.manager')->getAliasByPath($nodeid), '/'));
        }
        
        $language = \Drupal::languageManager()->getCurrentLanguage()->getId();

        // - unset language id if present in path.
        if ($path[0] == $language) {
            unset($path[0]);
        }

        // - join paths.
        return "/" . implode("/", $path);
    }

    /**
     * Check this belongs on a page
     */
    public function check_page($pages)
    {
        // get current
        $nodeid = $this->get_nodeid();
        $path = $this->get_path();

        $pages = explode("\n", $pages);
        $pages = array_map(function ($p) {
            return trim($p);
        }, $pages);

        if (count($pages) > 0 && $pages[0] != '') {
            $pathMatcher = \Drupal::service('path.matcher');
            foreach ($pages as $p) {
                if ($pathMatcher->matchPath($nodeid, $p) || $pathMatcher->matchPath($path, $p)) {
                    return true;
                }
            }

            return false;
        }

        return true;
    }
}
