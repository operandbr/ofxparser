<?php

namespace OfxParser;

/**
 * @author Diogo Alexsander Cavilha <diogo@agenciasys.com.br>
 */
class Fix
{
    private $file;

    public function __construct($file)
    {
        $this->file = $file;
    }

    public function replaceStartDate($search, $replace = '20000101100000')
    {
        $fileContent = $this->getFileContent($this->file);

        $fileContent = str_replace(
            '<DTSTART>' . $search,
            '<DTSTART>' . $replace,
            $fileContent
        );

        $this->saveFileContent($this->file, $fileContent);

        return $this;
    }

    protected function getFileContent($file)
    {
        return file_get_contents($file);
    }

    protected function saveFileContent($file, $content)
    {
        file_put_contents($file, $content);
    }
}
