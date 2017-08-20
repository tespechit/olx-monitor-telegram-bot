<?php

namespace App\Olx;

use PHPHtmlParser\Dom;
use SebastianBergmann\GlobalState\RuntimeException;

class OlxCliente
{
    /**
     * @var Dom
     */
    protected $dom;

    /**
     * @var OlxCriterio
     */
    protected $criterio;

    protected $urls;

    function __construct(OlxCriterio $criterio, array $urls)
    {
        $this->dom = new Dom();
        $this->criterio = $criterio;
        $this->urls = $urls;
    }

    public function procurarAnuncios($seguir_paginacao = false, $max_paginas = 5)
    {
        $urls_anuncios = [];

        foreach ($this->urls as $url) {

            $num_paginas = 1;

            if ($seguir_paginacao) {
                $num_paginas = $this->getQuantidadePaginas($url);
                $num_paginas = $num_paginas > $max_paginas ? $max_paginas: $num_paginas;
            }

            $urls = $this->getAnunciosUrls($url, $num_paginas);

            $urls_anuncios = array_merge($urls_anuncios, $urls);
        }

        return $this->getAnuncios($this->criterio, $urls_anuncios);
    }

    private function extraiAnunciosUrl($url)
    {
        $urls = [];

        $collection = $this->getDom($url)->find('.section_listing .OLXad-list-link');
        foreach ($collection as $item) {
            $urls[] = $item->getAttribute('href');
        }

        return $urls;
    }

    private function getAnunciosUrls(string $url, $num_paginas = 1)
    {
        if ($num_paginas == 1) {
            return $this->extraiAnunciosUrl($url);
        }

        $urls = [];

        for ($pagina = 1; $pagina <= $num_paginas; $pagina++) {
            $url_pagina = $url . '?o=' . $pagina;

            $urls_anuncios_pagina = $this->extraiAnunciosUrl($url_pagina);

            $urls = array_merge($urls, $urls_anuncios_pagina);
        }

        return $urls;
    }

    private function getDom($url)
    {
        $context = stream_context_create([
            'http' => [
                'method' => "GET",
                'header' => "User-Agent: Mozilla/5.0 (iPad; U; CPU OS 3_2 like Mac OS X; en-us) AppleWebKit/531.21.10 (KHTML, like Gecko) Version/4.0.4 Mobile/7B334b Safari/531.21.102011-10-16 20:23:10\r\n",
                'timeout' => 5,
            ]
        ]);

        if (!$html = @file_get_contents($url, false, $context)) {
            throw new RuntimeException("file_get_contents('$url')'");
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

    private function parseNumber($str)
    {
        return preg_replace('/[^0-9]+/', '', $str);
    }

    private function parseAnuncio(string $url)
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

                case 'Vagas na garagem:':
                    $vagas_garagem = $this->parseNumber($description);
                    break;

                default:
                    throw new RuntimeException('OLXad-details term desconhecido: ' . $term);
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

                default:
                    throw new RuntimeException('OLXad-location term desconhecido: ' . $term);
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

    private function getAnuncios(OlxCriterio $criterio, array $urls)
    {
        $anuncios = [];

        foreach ($urls as $url) {
            $anuncio = $this->parseAnuncio($url);

            if (!$criterio->validar($anuncio)) {
                continue;
            }

            $anuncios[] = $anuncio;
        }

        return $anuncios;
    }
}