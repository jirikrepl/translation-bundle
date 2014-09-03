<?php

/*
 * Copyright 2011 Johannes M. Schmitt <schmittjoh@gmail.com>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace JMS\TranslationBundle\Translation\Extractor\File;

use JMS\TranslationBundle\Model\FileSource;
use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Model\MessageCatalogue;
use JMS\TranslationBundle\Translation\Extractor\CommonTools;
use JMS\TranslationBundle\Translation\Extractor\FileVisitorInterface;

class TwigVariableExtractor implements FileVisitorInterface, \Twig_NodeVisitorInterface
{
    private $file;
    private $catalogue;
    private $traverser;

    public function __construct(\Twig_Environment $env)
    {
        $this->traverser = new \Twig_NodeTraverser($env, array($this));
    }

    public function enterNode(\Twig_NodeInterface $node, \Twig_Environment $env)
    {
        if ($node instanceof \Twig_Node_Set) {
            $varName = $node->getNode('names')->getNode(0)->getAttribute('name');
            // create transKey
            // process only $varName with prefix 'trans'
            if (CommonTools::startsWith($varName, 'trans')) {
                $values = $node->getNode('values')->getNode(0);
                // process constant string node
                if ($values instanceof \Twig_Node_Expression_Constant) {
                    $value = $values->getAttribute('value');
                    $this->addMessage($value, $node);
                } else {
                    $this->processArrayNode($node, $values);
                }
            }
        }
        return $node;
    }

    /**
     * process array node, add message to catalogue
     * if twig variable is defined as array [] or assoc array, this is content of \Twig_Node_Expression_Array
     * ['key1', 'value1', 'key2', 'value2', ...]
     *
     * when it is twig assoc array {} keyN has user defined name attribute (from twig assoc. array key)
     * when is wig array[], key have natural number sequence (plain array) keyN == 0, ..., N
     *
     * @param $node \Twig_Node_Set
     * @param $values \Twig_Node_Expression_Array
     */
    private function processArrayNode($node, $values)
    {
        $i = 0;
        $translate = false;
        foreach ($values->getIterator() as $value) {
            if (!($value instanceof \Twig_Node_Expression_Constant)) {
                // continue if element of twig array is NOT constant
                continue;
            }
            $valueAttr = $value->getAttribute('value');
            // in case of: ..................value from assoc array ...or ... plain number indexed array
            if ($i % 2 == 0 && (CommonTools::startsWith($valueAttr, 'trans') || is_int($valueAttr))) {
                // even
                $translate = true;
            } else {
                // odd
                if ($translate) {
                    $this->addMessage($valueAttr, $node);
                    $translate = false;
                }
            }
            $i++;
        }
    }

    /**
     * @param $desc
     * @param $node
     */
    private function addMessage($desc, $node)
    {
        $message = new Message($desc, 'variables');
        $message->addSource(new FileSource((string)$this->file, $node->getLine()));
        $this->catalogue->add($message);
    }

    /**
     * @return int
     */
    public function getPriority()
    {
        return 0;
    }

    /**
     * @param \SplFileInfo $file
     * @param MessageCatalogue $catalogue
     * @param \Twig_Node $ast
     */
    public function visitTwigFile(\SplFileInfo $file, MessageCatalogue $catalogue, \Twig_Node $ast)
    {
        $this->file = $file;
        $this->catalogue = $catalogue;
        $this->traverser->traverse($ast);
        $this->traverseEmbeddedTemplates($ast);
    }

    /**
     * If the current Twig Node has embedded templates, we want to travese these templates
     * in the same manner as we do the main twig template to ensure all translations are
     * caught.
     *
     * @param \Twig_Node $node
     */
    private function traverseEmbeddedTemplates(\Twig_Node $node)
    {
        $templates = $node->getAttribute('embedded_templates');

        foreach ($templates as $template) {
            $this->traverser->traverse($template);
            if ($template->hasAttribute('embedded_templates')) {
                $this->traverseEmbeddedTemplates($template);
            }
        }
    }

    public function leaveNode(\Twig_NodeInterface $node, \Twig_Environment $env)
    {
        return $node;
    }

    public function visitFile(\SplFileInfo $file, MessageCatalogue $catalogue)
    {
    }

    public function visitPhpFile(\SplFileInfo $file, MessageCatalogue $catalogue, array $ast)
    {
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