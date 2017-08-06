<?php

namespace App;

use PHPHtmlParser\Dom;

class OlxCliente
{
    protected $urls;
    protected $limite_paginas_por_url;

    public function __construct($urls, $limite_paginas_por_url = 5)
    {
        $this->urls = $urls;
        $this->limite_paginas_por_url = $limite_paginas_por_url;
    }

    private function filtrarAnuncios($anuncios_pagina, $area_min, $area_max, $vaga_garagem)
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

            $filtrado[] = $anuncio;
        }

        return $filtrado;
    }

    private function getAnuncios(Dom $dom)
    {
        $anuncios = [];

        $collection = $dom->find('.section_listing .OLXad-list-link');

        foreach ($collection as $item) {
            $anuncios[] = $this->parseAnuncio($item);
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

    private function parseAnuncio(Dom\HtmlNode $item)
    {

        $titulo = $item->getAttribute('title');
        $url = $item->getAttribute('href');

        $img = $item->find('.OLXad-list-image-box img');

        $preco = $item->find('.OLXad-list-price')->innerHtml();
        $preco = preg_replace('/[^0-9]+/', '', $preco);


        $detalhes = $item->find('.detail-specific')->innerHtml();


        preg_match('#.*([0-9]) quarto.*#', $detalhes, $matches);
        $quartos = count($matches) > 1 ? $matches[1] : '';

        preg_match('#.* ([0-9]+) m.*#', $detalhes, $matches);
        $area = count($matches) > 1 ? $matches[1] : '';

        preg_match('#.*([0-9]) vaga.*#', $detalhes, $matches);
        $carros = count($matches) > 1 ? $matches[1] : '';


        $item_dom = $this->getDom($url);
        $localizacao_atributos = $item_dom->find('.section_OLXad-info .atributes');

        $cidade = $cep = $bairro = '';

        if (count($localizacao_atributos) == 2) {
            $atributos = $localizacao_atributos[1]->find('.description');

            if (count($atributos) >= 3) {
                $cidade = trim($atributos[0]->innerHtml());
                $cep = trim($atributos[1]->innerHtml());
                $bairro = trim($atributos[2]->innerHtml());

                $cep = preg_replace('/\D/', '', $cep);
            }
        }

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
                    $data = $this->parseData($data);
            }

            $hora = $texts_datahora[1]->innerHtml();

            $datahora = "$data $hora";

            if ($tmp = \DateTime::createFromFormat('Y-m-d H:i', $datahora)) {
                $created_at = $tmp->format('Y-m-d H:i:s');
            }
        }

        $id = (int)current(array_reverse(explode('-', $url)));

        return [
            'id' => $id,
            'titulo' => utf8_encode($titulo),
            'url' => $url,
            'preco' => $preco,
            'quartos' => $quartos,
            'area' => $area,
            'carros' => $carros,
            'cidade' => utf8_encode($cidade),
            'bairro' => utf8_encode($bairro),
            'cep' => $cep,
            'created_at' => $created_at,
        ];
    }

    private function parseData($data)
    {
        $meses = [
            'jan' => '01',
            'fev' => '02',
            'mar' => '03',
            'abr' => '04',
            'mai' => '05',
            'jun' => '06',
            'jul' => '07',
            'ago' => '08',
            'set' => '09',
            'out' => '10',
            'nov' => '11',
            'dez' => '12',
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
        $vaga_garagem = false
    ) {

        $anuncios = [];

        foreach ($this->urls as $url_raw) {

            $parsed_url = parse_url($url_raw);
            $url = $parsed_url['scheme'] . '://' . $parsed_url['host'] . $parsed_url['path'];

            if (filter_var($url, FILTER_VALIDATE_URL) === false) {
                continue;
            }

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

            $dom = $this->getDom($url);

            $total_paginas = $this->getQuantidadePaginas($dom);

            if ($total_paginas > $this->limite_paginas_por_url) {
                $total_paginas = $this->limite_paginas_por_url;
            }

            for ($pagina = 1; $pagina <= $total_paginas; $pagina++) {
                $dom = $this->getDom($url . '&o=' . $pagina);

                $anuncios_pagina = $this->filtrarAnuncios(
                    $this->getAnuncios($dom),
                    $area_min,
                    $area_max,
                    $vaga_garagem
                );

                $anuncios = array_merge($anuncios, $anuncios_pagina);

                sleep(.25);
            }

        }

        return $anuncios;
    }
}