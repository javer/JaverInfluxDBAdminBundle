<?php

namespace Javer\InfluxDB\AdminBundle\Datagrid;

use Javer\InfluxDB\ODM\Query\Query;

interface ProxyQueryInterface
{
    public function getQuery(): Query;
}
