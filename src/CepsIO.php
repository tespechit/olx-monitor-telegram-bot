<?php


namespace App;

use PHPHtmlParser\Dom;

class CepsIO
{
    /**
     * @var Dom
     */
    protected $dom;

    public function __construct()
    {
        $this->dom = new Dom();
    }

    function getEndereco($cep)
    {
        $this->dom->loadFromUrl('http://ceps.io/busca/?query=' . $cep);
        $node = $this->dom->find('.box1 .box_text h2');

        if (!count($node)) {
            throw new \RuntimeException('CEP não cadastrado');
        }

        $endereco = explode(',', $node->innerHtml);

        return sprintf('%s, %s',
            trim($endereco[0]),
            trim($endereco[1])
        );
    }
}