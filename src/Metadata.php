<?php
namespace Flatten;

use Illuminate\Support\Arr;

class Metadata
{
    /**
     * @var string
     */
    protected $directory;

    /**
     * Metadata constructor.
     *
     * @param string $directory
     */
    public function __construct($directory)
    {
        $this->directory = $directory;
    }

    /**
     * @param $key
     *
     * @return mixed
     */
    public function get($key)
    {
        return Arr::get($this->getMetadata(), $key);
    }

    /**
     * @param $key
     * @param $value
     */
    public function set($key, $value)
    {
        $array = $this->getMetadata();
        Arr::set($array, $key, $value);

        $this->setMetadata($array);
    }

    /**
     * Clear the metadata cache
     */
    public function clear()
    {
        file_put_contents($this->getFilepath(), '{}');
    }

    /**
     * @return array|string
     */
    protected function getMetadata()
    {
        $filepath = $this->getFilepath();
        $contents = file_get_contents($filepath);
        $contents = json_decode($contents, true) ?: [];

        return $contents;
    }

    /**
     * @param $metadata
     */
    protected function setMetadata($metadata)
    {
        $metadata = json_encode($metadata);
        file_put_contents($this->getFilepath(), $metadata);
    }

    /**
     * @return string
     */
    protected function getFilepath()
    {
        $filepath = $this->directory.DIRECTORY_SEPARATOR.'flatten.json';
        if (!is_dir($filepath)) {

        }

        if (!file_exists($filepath)) {
            file_put_contents($filepath, '{}');
        }

        return $filepath;
    }
}
