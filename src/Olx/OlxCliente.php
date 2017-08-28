<?php

namespace App\Olx;

use PHPHtmlParser\Dom;

class OlxCliente
{
    /**
     * @var Dom
     */
    protected $dom;

    function __construct()
    {
        $this->dom = new Dom();
    }

    public function getAnunciosUrls(string $url, $paginacao = false)
    {
        if (!$paginacao) {
            return $this->extraiAnunciosUrl($url);
        }

        $num_paginas = $this->getQuantidadePaginas($url);

        $urls = [];

        for ($pagina = 1; $pagina <= $num_paginas; $pagina++) {
            $url_pagina = $url . '?o=' . $pagina;
            $urls[] = $this->extraiAnunciosUrl($url_pagina);
        }

        return array_reduce($urls, 'array_merge', []);
    }

    private function extraiAnunciosUrl(string $url)
    {
        $urls = [];

        $collection = $this->getDom($url)->find('.section_listing .OLXad-list-link');
        foreach ($collection as $item) {
            $urls[] = $item->getAttribute('href');
        }

        return $urls;
    }

    private function getDom(string $url)
    {
        $context = stream_context_create([
            'http' => [
                'method' => "GET",
                'header' => "User-Agent: Mozilla/5.0 (iPad; U; CPU OS 3_2 like Mac OS X; en-us) AppleWebKit/531.21.10 (KHTML, like Gecko) Version/4.0.4 Mobile/7B334b Safari/531.21.102011-10-16 20:23:10\r\n",
                'timeout' => 5,
            ]
        ]);

        if (!$html = @file_get_contents($url, false, $context)) {
            throw new \RuntimeException("file_get_contents('$url')'");
        }

        $html = utf8_encode($html);

        return $this->dom->loadStr($html, [
            'cleanupInput' => false,
            'removeScripts' => true,
            'removeStyles' => true,
            'whitespaceTextNode' => false
        ]);
    }

    private function getQuantidadePaginas(string $url)
    {
        $li = $this->getDom($url)->find('.module_pagination li.number');
        return count($li);
    }

    public function getAnuncio(string $url)
    {
        $dom = $this->getDom($url);

        $id = (int)current(array_reverse(explode('-', $url)));

        $titulo = $preco = $tipo = $condominio = $iptu = $area = $quartos = $vagas_garagem = '';

        if ($titulo_raw = $dom->getElementById('ad_title')) {
            $titulo = trim($titulo_raw->innerHtml);
        }

        $preco_raw = $dom->getElementsByClass('actual-price');
        if (count($preco_raw)) {
            $preco = $this->parseNumber($preco_raw[0]->innerHtml);
        }


        $detail_itens = $dom->find('.OLXad-details .item .text');
        foreach ($detail_itens as $item) {

            $term = trim($item->find('.term')->innerHtml);
            $description = $item->find('.description')->innerHtml;

            switch ($term) {
                case 'Tipo:':
                    $tipo = trim($description);
                    break;

                case 'Condomínio:':
                    $condominio = $this->parseNumber($description);
                    break;

                case 'IPTU:':
                    $iptu = $this->parseNumber($description);
                    break;

                case 'Quartos:':
                    $quartos = $this->parseNumber($description);
                    break;

                case 'Área construída:':
                    $area = $this->parseNumber($description);
                    break;

                case 'Área útil:':
                    $area = $this->parseNumber($description);
                    break;

                case 'Vagas na garagem:':
                    $vagas_garagem = $this->parseNumber($description);
                    break;
            }
        }

        $cidade = '';
        $cep = '';
        $bairro = '';

        $detail_itens = $dom->find('.OLXad-location .item .text');
        foreach ($detail_itens as $item) {
            $term = trim($item->find('.term')->innerHtml);
            $description = $item->find('.description')->innerHtml;

            switch ($term) {
                case 'Município:':
                    $cidade = trim($description);
                    break;

                case 'CEP do imóvel:':
                    $cep = $this->parseNumber($description);
                    break;

                case 'Bairro:':
                    $bairro = trim($description);
                    break;
            }
        }

        $created_at = date('Y-m-d H:i:s');

        return new OlxAnuncio([
            'id' => $id,
            'titulo' => $titulo,
            'url' => $url,
            'preco' => $preco,
            'quartos' => $quartos,
            'area' => $area,
            'condominio' => $condominio,
            'vagas_garagem' => $vagas_garagem,
            'cidade' => $cidade,
            'bairro' => $bairro,
            'cep' => $cep,
            'created_at' => $created_at,
        ]);
    }

    private function parseNumber($value)
    {
        return preg_replace('/[^0-9]+/', '', $value);
    }
}