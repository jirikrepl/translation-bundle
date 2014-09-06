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

use \stdClass;
use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Model\MessageCatalogue;
use JMS\TranslationBundle\Translation\Extractor\FileVisitorInterface;

class JsonExtractor implements FileVisitorInterface
{
    private $file;
    private $catalogue;

    /**
     * crawl php array created from json data
     *
     * @param array $categories
     */
    private function traverseData(array $categories)
    {
        // manually create message for 'All calculators'
        $allCalc = new stdClass;
        $allCalc->desc = 'All calculators';
        $this->addMessage($allCalc);

        foreach ($categories as $category) {
            if (isset($category->subSections)) {
                $this->traverseData($category->subSections);
            }
            // extract category name
            $this->addMessage($category);
            // extract category items
            if (isset($category->items)) {
                foreach ($category->items as $item) {
                    $this->addMessage($item);
                }
            }
        }
    }

    /**
     * add message to catalogue
     *
     * @param $item
     */
    private function addMessage($item)
    {
        $id = str_replace(" ", ".", $item->desc);
        $message = new Message($id, 'menuItems');
//        $message->addSource(new FileSource((string)$this->file));
        $message->setDesc($item->desc);
        $this->catalogue->add($message);
    }

    /**
     * call this interface method to crawl json file
     *
     * @param \SplFileInfo $file
     * @param MessageCatalogue $catalogue
     * @param array $data
     */
    public function visitJsonFile(\SplFileInfo $file, MessageCatalogue $catalogue, array $data)
    {
        $this->file = $file;
        $this->catalogue = $catalogue;
        $this->traverseData($data);
    }

    public function visitTwigFile(\SplFileInfo $file, MessageCatalogue $catalogue, \Twig_Node $ast)
    {
    }

    public function visitFile(\SplFileInfo $file, MessageCatalogue $catalogue)
    {
    }

    public function visitPhpFile(\SplFileInfo $file, MessageCatalogue $catalogue, array $ast)
    {
    }
}