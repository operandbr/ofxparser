<?php

namespace OfxParser;

/**
 * An OFX parser library
 *
 * Heavily refactored from Guillaume Bailleul's grimfor/ofxparser
 *
 * @author Guillaume BAILLEUL <contact@guillaume-bailleul.fr>
 * @author James Titcumb <hello@jamestitcumb.com>
 * @author Oliver Lowe <mrtriangle@gmail.com>
 */
class Parser
{
    /**
     * Load an OFX file into this parser by way of a filename
     *
     * @param string $ofxFile A path that can be loaded with file_get_contents
     * @return Ofx
     * @throws \Exception
     */
    public function loadFromFile($ofxFile)
    {
        if (!file_exists($ofxFile)) {
            throw new \InvalidArgumentException("File '{$ofxFile}' could not be found");
        }

        return $this->loadFromString(file_get_contents($ofxFile));
    }

    /**
     * Load an OFX by directly using the text content
     *
     * @param string $ofxContent
     * @return  Ofx
     * @throws \Exception
     * @see https://www.ofx.net/downloads.html
     */
    public function loadFromString($ofxContent)
    {
        $ofxContent = utf8_encode($ofxContent);
        $ofxContent = $this->conditionallyAddNewlines($ofxContent);

        $sgmlStart = stripos($ofxContent, '<OFX>');
        $ofxSgml = trim(substr($ofxContent, $sgmlStart));

        $ofxXml = $this->convertSgmlToXml($ofxSgml);

        $xml = $this->xmlLoadString($ofxXml);

        if (empty($xml) || is_null($xml)) {
            throw new \InvalidArgumentException('Content is not valid ofx schema, please visit https://www.ofx.net/downloads.html and check valid schemas.');
        }

        return new Ofx($xml);
    }

    /**
     * Detect if the OFX file is on one line. If it is, add newlines automatically.
     *
     * @param string $ofxContent
     * @return string
     */
    private function conditionallyAddNewlines($ofxContent)
    {
        if (preg_match('/<OFX>.*<\/OFX>/', $ofxContent) === 1) {
            return str_replace('<', "\n<", $ofxContent); // add line breaks to allow XML to parse
        }

        return $ofxContent;
    }

    /**
     * Load an XML string without PHP errors - throws exception instead
     *
     * @param string $xmlString
     * @throws \Exception
     * @return \SimpleXMLElement
     */
    private function xmlLoadString($xmlString)
    {
        libxml_clear_errors();
        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($xmlString);

        if ($errors = libxml_get_errors()) {
            throw new \RuntimeException('Failed to parse OFX: ' . var_export($errors, true));
        }

        return $xml;
    }

    /**
     * Detect any unclosed XML tags - if they exist, close them
     * Matches: <SOMETHING>blah
     * Does not match <SOMETHING> but, will match <DTPOSTED> and <TRNTYPE> specifically
     * Does not match: <SOMETHING>blah</SOMETHING>
     * 
     * @param string $line
     * @return string
     */
    private function closeUnclosedXmlTags($line)
    {
        $line = str_replace('"', '', $line);
        if (preg_match(
            "/<([A-Za-z0-9.]+)>([\wà-úÀ-Ú0-9\.\-\_\+\, ;:\[\]\'\&\/\\\*\(\)\+\{\|\}\!\£\$\?=@€£#%±§~`]+)$/",
            trim($line),
            $matches
        )) {
            $line = "<{$matches[1]}>{$matches[2]}</{$matches[1]}>";
        }

        $tagsShouldClosed = [
            'DTPOSTED',
            'TRNTYPE'
        ];

        return 
        preg_replace_callback(
            "/<(" . implode('|', $tagsShouldClosed) . ")>(?!\w)/", 
            function($tagMatched) {
            
                return "<{$tagMatched[1]}></{$tagMatched[1]}>";

            }, 
            $line
        );
    }

    /**
     * Convert an SGML to an XML string
     *
     * @param string $sgml
     * @return string
     */
    private function convertSgmlToXml($sgml)
    {
        $sgml = str_replace(["\r\n", "\r"], "\n", $sgml);

        $lines = explode("\n", $sgml);

        $xml = '';
        foreach ($lines as $line) {
            $openingTag = preg_quote(str_replace("/", "", $line), '/');
            $closingTag = preg_quote($line, '/');

            if (preg_match("/{$openingTag}.*{$closingTag}/", $xml)) {
                continue;
            }

            $xml .= trim($this->closeUnclosedXmlTags($line)) . "\n";
        }

        return trim($xml);
    }
}
