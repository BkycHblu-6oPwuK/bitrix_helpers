<?php

namespace Beeralex\Catalog\Location\Contracts;

interface LocationDataParserContract
{
    /**
     * Parses the data from the location API into a structured array of variants.
     *
     * @param array $data The data part of the suggestion from the API.
     * @return array Returns an array with settlement, city, area, and region variants.
     */
    public function parse(array $suggestions): array;
}
