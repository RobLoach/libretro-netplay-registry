<?php

namespace RobLoach\LibretroNetplayRegistry;

use RobLoach\LibretroNetplayRegistry\Registry;

/**
 * Class PlaylistFormatter.
 */
class PlaylistFormatter
{
    /**
     * @var Registry
     */
    private $registry;
    private $testing;

    /**
     * PlaylistFormatter constructor.
     *
     * @param Registry $registry
     * @param bool $testing Whether or not to include fields currently in testing.
     */
    public function __construct(Registry $registry, $testing = false)
    {
        $this->registry = $registry;
        $this->testing = $testing;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $output = array();
        foreach ($this->registry->selectAll() as $index => $entry) {
            $properties = array(
                $entry['username'],
                $entry['ip'],
                $entry['port'],
                $entry['corename'],
                $entry['coreversion'],
                $entry['gamename'],
                $entry['gamecrc'],
                $entry['created'],
            );

            // If we are testing any new fields, add them here.
            if ($this->testing) {
                array_push($properties, $entry['haspassword'] ? '1' : '0');
            }
            array_push($output, implode($properties, "\n"));
        }
        return implode($output, "\n");
    }
}
