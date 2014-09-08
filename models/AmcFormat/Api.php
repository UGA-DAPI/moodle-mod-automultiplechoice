<?php

/* 
 * @license http://www.gnu.org/licenses/gpl-3.0.html  GNU GPL v3
 */

namespace mod\automultiplechoice\AmcFormat;

/**
 * @param string $formatName "txt" | "latex"
 * @return mod\automultiplechoice\AmcFormat\Api
 * @throws \Exception
 */
function buildFormat($formatName) {
    $formatName = ucfirst(strtolower($formatName));
    $filename = __DIR__ . '/' . $formatName . '.php';
    if (file_exists($filename)) {
        require_once $filename;
    } else {
        throw new \Exception("Unknown format");
    }
    $formatName = 'mod\\automultiplechoice\\AmcFormat\\' . $formatName;
    return (new $formatName);
}

interface Api
{

     /**
     * Compute the whole source file content for AMC, by merging header and question blocks.
     *
     * @return string file content
     */
    public function getContent();

    public function getFilename();

    public function getFilterName();
}
