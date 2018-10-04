<?php

namespace OfxParser;

/**
 * @author Diogo Alexsander Cavilha <diogo@agenciasys.com.br>
 */
class Fix
{
    private $file;
    private $fileContent;

    public function __construct($file)
    {
        $this->file = $file;
        $this->fileContent = $this->getFileContent();
    }

    public function replaceStartDate($search, $replace = '20000101100000')
    {
        $this->fileContent = str_replace(
            '<DTSTART>' . $search,
            '<DTSTART>' . $replace,
            $this->fileContent
        );

        $this->saveFileContent();

        return $this;
    }

    protected function getFileContent()
    {
        return file_get_contents($this->file);
    }

    protected function saveFileContent()
    {
        file_put_contents($this->file, $this->fileContent);
    }

    public function replaceUsingRegex($pattern, $replacement)
    {
        $this->fileContent = preg_replace($pattern, $replacement, $this->fileContent);

        $this->saveFileContent();

        return $this;
    }
}
