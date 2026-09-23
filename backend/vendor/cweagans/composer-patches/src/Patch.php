<?php

namespace cweagans\Composer;

use Composer\Package\PackageInterface;
use JsonSerializable;

class Patch implements JsonSerializable
{
    /**
     * The package that the patch belongs to.
     *
     * @var string $package
     */
    public string $package;

    /**
     * The description of what the patch does.
     *
     * @var string $description
     */
    public string $description;

    /**
     * The URL where the patch is stored. Can be local.
     *
     * @var string $url
     */
    public string $url;

    /**
     * The sha256 hash of the patch file.
     *
     * @var ?string sha256
     */
    public ?string $sha256;

    /**
     * The patch depth to use when applying the patch (-p flag for `patch`)
     *
     * @var ?int $depth
     */
    public ?int $depth;

    /**
     * If the patch has been downloaded, the path to where it can be found.
     *
     * @var ?string
     */
    public ?string $localPath;

    /**
     * Can be used as a place for other plugins to store data about a patch.
     *
     * This should be treated as an associative array and should contain only scalar values.
     *
     * @var array
     */
    public array $extra = [];

    /**
     * Create a Patch from a serialized representation.
     *
     * @param $json
     *   A JSON representation of a Patch (or an array from JsonFile).
     *
     * @return Patch
     *   A Patch with all serialized properties set.
     */
    public static function fromJson($json): static
    {
        if (!is_array($json)) {
            $json = json_decode($json, true);
        }
        $properties = ['package', 'description', 'url', 'sha256', 'depth', 'extra'];
        $patch = new static();

        foreach ($properties as $property) {
            if (isset($json[$property])) {
                $patch->{$property} = $json[$property];
            }
        }

        return $patch;
    }

    /**
     * {@inheritDoc}
     */
    public function jsonSerialize(): mixed
    {
        return [
            'package' => $this->package,
            'description' => $this->description,
            'url' => $this->url,
            'sha256' => $this->sha256 ?? null,
            'depth' => $this->depth ?? null,
            'extra' => $this->extra ?? [],
        ];
    }
}
