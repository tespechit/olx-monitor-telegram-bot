<?php

namespace App;

use PHPHtmlParser\Dom;

class OlxCliente
{
    protected $urls;

    public function __construct($urls)
    {
        $this->urls = $urls;
    }

    private static function filtrarAnuncios($anuncios_pagina, $area_min, $area_max, $vaga_garagem, $com_foto)
    {
        $filtrado = [];

        foreach ($anuncios_pagina as $anuncio) {
            if ($area_min) {
                if ($anuncio['area'] < $area_min) {
                    continue;
                }
            }

            if ($area_max) {
                if ($anuncio['area'] > $area_max) {
                    continue;
                }
            }

            if ($vaga_garagem) {
                if (empty($anuncio['carros'])) {
                    continue;
                }
            }

            if ($com_foto) {
                if (empty($anuncio['foto'])) {
                    continue;
                }
            }

            $filtrado[] = $anuncio;
        }

        return $filtrado;
    }

    private static function getAnuncios(Dom $dom)
    {
        $anuncios = [];

        $collection = $dom->find('.section_listing .OLXad-list-link');

        foreach ($collection as $item) {
            $anuncios[] = self::parseAnuncio($item);
        }

        return $anuncios;
    }

    /**
     * @param $url
     * @return \PHPHtmlParser\Dom
     */
    private function getDom($url)
    {
        $dom = new Dom();

        $dom->loadFromUrl($url, [
            'cleanupInput' => false,
            'whitespaceTextNode' => false,
        ], new OlxClienteCurl());

        return $dom;
    }

    private function getQuantidadePaginas(Dom $dom)
    {
        $li = $dom->find('.module_pagination li.number');
        return count($li);
    }

    private static function parseAnuncio(Dom\HtmlNode $item)
    {

        $titulo = $item->getAttribute('title');
        $url = $item->getAttribute('href');

        $img = $item->find('.OLXad-list-image-box img');
        if (count($img)) {
            $foto = $img[0]->getAttribute('src');
        } else {
            $foto = '';
        }


        $preco = $item->find('.OLXad-list-price')->innerHtml();
        $preco = preg_replace('/[^0-9]+/', '', $preco);


        $detalhes = $item->find('.detail-specific')->innerHtml();


        preg_match('#.*([0-9]) quarto.*#', $detalhes, $matches);
        $quartos = count($matches) > 1 ? $matches[1] : '';

        preg_match('#.* ([0-9]+) m.*#', $detalhes, $matches);
        $area = count($matches) > 1 ? $matches[1] : '';

        preg_match('#.*([0-9]) vaga.*#', $detalhes, $matches);
        $carros = count($matches) > 1 ? $matches[1] : '';

        //$regiao = $item->find('.OLXad-list-line-2')->innerHtml();


        $created_at = date('Y-m-d H:i:s');

        if ($texts_datahora = $item->find('.col-4 .text')) {

            $data = strtolower($texts_datahora[0]->innerHtml());

            switch ($data) {
                case 'hoje':
                    $data = date('Y-m-d');
                    break;
                case 'ontem':
                    $data = date('Y-m-d', time() - 86400);
                    break;
                default:
                    $data = self::parseData($texts_datahora[0]);
            }

            $hora = $texts_datahora[1]->innerHtml();

            $datahora = "$data $hora";


            if ($tmp = \DateTime::createFromFormat('Y-m-d H:i', $datahora)) {
                $created_at = $tmp->format('Y-m-d H:i:s');
            }
        }

        return [
            'id' => base64_encode($titulo . $created_at),
            'titulo' => $titulo,
            'url' => $url,
            'preco' => $preco,
            'quartos' => $quartos,
            'area' => $area,
            'carros' => $carros,
            'cidade' => '',
            'bairro' => '',
            'foto' => $foto,
            'created_at' => $created_at,
        ];
    }

    private static function parseData($data)
    {
        $meses = [
            'Jan' => '01',
            'Fev' => '02',
            'Mar' => '03',
            'Abr' => '04',
            'Mai' => '05',
            'Jun' => '06',
            'Jul' => '07',
            'Ago' => '08',
            'Set' => '09',
            'Out' => '10',
            'Nov' => '11',
            'Dez' => '12',
        ];

        $arr = explode(' ', $data);

        if (count($arr) != 2) {
            return date('Y-m-d');
        }

        $dia = trim($arr[0]);
        $mes = $meses[trim($arr[1])];

        return date('Y') . "-$mes-$dia";
    }

    public function procurar(
        $preco_min,
        $preco_max,
        $area_min,
        $area_max,
        $quartos_min = 1,
        $vaga_garagem = false,
        $com_foto = true
    ) {

        $anuncios = [];

        foreach ($this->urls as $url) {

            $params = [];

            if ($preco_max) {
                $params[] = 'pe=' . round($preco_max);
            }

            if ($preco_min) {
                $params[] = 'ps=' . round($preco_min);
            }

            if ($quartos_min) {
                $params[] = 'ros=' . round($quartos_min);
            }

            if (count($params)) {
                $url .= '?' . implode('&', $params);
            }

            $dom = self::getDom($url);

            if (!$qtd_paginas = self::getQuantidadePaginas($dom)) {
                $qtd_paginas = 1;
            }

            $anuncios_pagina = self::getAnuncios($dom);

            $anuncios_pagina = self::filtrarAnuncios($anuncios_pagina, $area_min, $area_max, $vaga_garagem, $com_foto);

            $anuncios = array_merge($anuncios, $anuncios_pagina);

            sleep(.5);

            for ($pagina = 2; $pagina <= $qtd_paginas; $pagina++) {

                $dom = $this->getDom($url . '&o=' . $pagina);

                $anuncios_pagina = self::getAnuncios($dom);
                $anuncios_pagina = self::filtrarAnuncios($anuncios_pagina, $area_min, $area_max, $vaga_garagem,
                    $com_foto);

                $anuncios = array_merge($anuncios, $anuncios_pagina);

                sleep(.5);
            }

        }

        return $anuncios;
    }
}