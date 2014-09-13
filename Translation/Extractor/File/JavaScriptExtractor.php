<?php
/**
 * Created by IntelliJ IDEA.
 * User: Jiri
 * Date: 9/9/2014
 * Time: 12:59 PM
 */

namespace JMS\TranslationBundle\Translation\Extractor\File;


use JMS\TranslationBundle\Model\FileSource;
use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Model\MessageCatalogue;
use JMS\TranslationBundle\Translation\Extractor\FileVisitorInterface;

class JavaScriptExtractor implements FileVisitorInterface
{
    private $file;
    private $catalogue;

    /**
     * Called for non-specially handled files.
     *
     * This is not called if handled by a more specific method.
     *
     * @param \SplFileInfo $file
     * @param MessageCatalogue $catalogue
     */
    function visitFile(\SplFileInfo $file, MessageCatalogue $catalogue)
    {
        if ('.js' !== substr($file, -3)) {
            return;
        }
        $this->file = $file;
        $this->catalogue = $catalogue;
        $filePath = $file->getRealPath();
        $content = file_get_contents($filePath);

        // matches content of dom variable
        $varDomMatch = [];
        $domain = null;
        if(preg_match("/var dom = '(.*)'/", $content, $varDomMatch)) {
            $domain = $varDomMatch[1];
        }

        $transVarDomainMatch = [];
        $transStringDomainMatch = [];
        preg_match_all("/{id: '(.*)', domain: (.*), desc: '(.*)'}/", $content, $transVarDomainMatch);
        preg_match_all("/{id: '(.*)', domain: '(.*)', desc: '(.*)'}/", $content, $transStringDomainMatch);
        $this->addMessages($transVarDomainMatch, $domain);
        $this->addMessages($transStringDomainMatch);

    }

    /**
     * @param $transMatch
     * @param null $domain
     */
    private function addMessages($transMatch, $domain = null)
    {   // if there is string matched by reg ex, array $transMatch[0] in array $transMatch is not empty
        // if there is not matched string, array $transMatch[0] exists but is empty
        if (!empty($transMatch[0])) {
            $idArr = $transMatch[1];
            $domainArr = $transMatch[2];
            $descArr = $transMatch[3];

            for ($i = 0; $i < count($idArr); $i++) {
                if (!empty($domain)) {
                    $message = new Message($idArr[$i], $domain);
                } else {
                    $message = new Message($idArr[$i], $domainArr[$i]);
                }
                $message->setDesc($descArr[$i]);
                $message->addSource(new FileSource((string)$this->file));
                $this->catalogue->add($message);
            }
        }
    }

    /**
     * Called when a PHP file is encountered.
     *
     * The visitor already gets a parsed AST passed along.
     *
     * @param \SplFileInfo $file
     * @param MessageCatalogue $catalogue
     * @param array|\PHPParser_Node $ast
     */
    function visitPhpFile(\SplFileInfo $file, MessageCatalogue $catalogue, array $ast)
    {
        // TODO: Implement visitPhpFile() method.
    }

    /**
     * Called when a Twig file is encountered.
     *
     * The visitor already gets a parsed AST passed along.
     *
     * @param \SplFileInfo $file
     * @param MessageCatalogue $catalogue
     * @param \Twig_Node $ast
     */
    function visitTwigFile(\SplFileInfo $file, MessageCatalogue $catalogue, \Twig_Node $ast)
    {
        // TODO: Implement visitTwigFile() method.
    }

    /**
     * Called when a Json file is encountered.
     *
     * @param \SplFileInfo $file
     * @param MessageCatalogue $catalogue
     * @param array $data json decoded data
     */
    function visitJsonFile(\SplFileInfo $file, MessageCatalogue $catalogue, array $data)
    {
        // TODO: Implement visitJsonFile() method.
    }
}