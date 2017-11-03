<?php

namespace PragmaRX\Support;

use Illuminate\Contracts\Support\Arrayable;
use Symfony\Component\Yaml\Yaml as SymfonyYaml;

class Yaml
{
    /**
     * Check if the string is a directory.
     *
     * @param $item
     * @return bool
     */
    private function isDirectory($item)
    {
        return
            is_dir($item) &&
            (ends_with($item, '.') || ends_with($item, '..'));
    }

    /**
     * Check if the file is a yaml file.
     *
     * @param $item
     * @return bool
     */
    private function isYamlFile($item)
    {
        return
            !is_dir($item) &&
            !ends_with(strtolower($item), '.yml') &&
            !ends_with(strtolower($item), '.yaml');
    }

    /**
     * Get all files from dir.
     *
     * @param $directory
     * @return \Illuminate\Support\Collection
     */
    public function listYamlFilesFromDir($directory)
    {
        if (! file_exists($directory)) {
            return collect([]);
        }

        return $this->scanDir($directory)->reject(function ($item) {
            return $this->isDirectory($item) || $this->isYamlFile($item);
        })->mapWithKeys(function ($item, $key) {
            if (is_dir($item)) {
                return [basename($item) => $this->listYamlFilesFromDir($item)->toArray()];
            }

            return [$key => $item];
        });
    }

    /**
     * Load yaml files from directory.
     *
     * @param $directory
     * @param bool $parseYaml
     * @return \Illuminate\Support\Collection
     */
    public function loadYamlFilesFromDir($directory, $parseYaml = true)
    {
        return $this->cleanArrayKeysRecursive(
            $this->listYamlFilesFromDir($directory)->mapWithKeys(function ($file, $key) use ($directory, $parseYaml) {
                if ((is_string($file) && file_exists($file)) || is_array($file)) {
                    list($file, $contents) = $this->loadFile($file, $parseYaml);

                    return [($file ?: $key) => $contents];
                }

                return [$key => $file];
            })
        );
    }

    /**
     * Parse a yaml file.
     *
     * @param $contents
     * @return mixed
     */
    private function parseFile($contents)
    {
        return SymfonyYaml::parse($this->replaceContents($contents));
    }

    /**
     * Remove quotes.
     *
     * @param $string
     * @return string
     */
    private function removeQuotes($string)
    {
        return trim(trim($string, "'"), '"');
    }

    /**
     * Replace contents.
     *
     * @param $contents
     * @return mixed
     */
    private function replaceContents($contents)
    {
        preg_match_all('/{{(.*)}}/', $contents, $matches);

        foreach ($matches[0] as $key => $match) {
            if (count($match)) {
                $contents = str_replace($matches[0][$key], $this->resolveVariable($matches[1][$key]), $contents);
            }
        }

        return $contents;
    }

    /**
     * Resolve variable.
     *
     * @param $key
     * @return string
     */
    private function resolveVariable($key)
    {
        $key = trim($key);

        if ($result = $this->executeFunction($key)) {
            return $result;
        }

        return config($key) ?: 'null';
    }

    /**
     * Execute function.
     *
     * @param $string
     * @return mixed
     */
    private function executeFunction($string)
    {
        preg_match_all('/(.*)\((.*)\)/', $string, $matches);

        if (count($matches) && count($matches[0])) {
            $function = $matches[1][0];

            return $function($this->removeQuotes($matches[2][0]));
        }
    }

    /**
     * Load yaml file.
     *
     * @param $file
     * @param bool $parseYaml
     * @return mixed|string
     */
    private function loadFile($file, $parseYaml = true)
    {
        if (is_array($file)) {
            return [
                false,
                collect($file)->mapWithKeys(function($subfile, $key) use ($parseYaml) {
                    list($subfile, $contents) = $this->loadFile($subfile, $parseYaml);

                    return [$subfile => $contents];
                })->toArray()
            ];
        };

        $contents = file_get_contents($file);

        if ($parseYaml) {
            $contents = $this->parseFile($contents);
        }

        return [$file, $contents];
    }

    /**
     * Dump array to yaml.
     *
     * @param $input
     * @param int $inline
     * @param int $indent
     * @param int $flags
     * @return string
     */
    public function dump($input, $inline = 5, $indent = 4, $flags = 0)
    {
        return SymfonyYaml::dump($input, $inline, $indent, $flags);
    }

    /**
     * Remove extension from file name.
     *
     * @param $dirty
     * @return \Illuminate\Support\Collection|mixed
     */
    public function cleanArrayKeysRecursive($dirty)
    {
        if (is_array($dirty instanceof Arrayable ? $dirty->toArray() : $dirty)) {
            return collect($dirty)->mapWithKeys(function ($item, $key) {
                return [
                    $this->cleanKey($key) => $this->cleanArrayKeysRecursive($item)
                ];
            });
        }

        return $dirty;
    }

    /**
     * Clean the array key.
     *
     * @param $key
     * @return mixed|string
     */
    public function cleanKey($key)
    {
        return is_string($key) && file_exists($key)
            ? preg_replace('/\.[^.]+$/', '', basename($key))
            : $key;
    }

    /**
     * Scan the directory for files.
     *
     * @param string $dir
     * @return \Illuminate\Support\Collection
     */
    private function scanDir($dir)
    {
        return collect(scandir($dir))->map(function ($item) use ($dir) {
            return $dir . DIRECTORY_SEPARATOR . $item;
        });
    }
}
