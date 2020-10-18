JaverInfluxDBAdminBundle
========================

This bundle integrates the [InfluxDB Object Document Mapper (ODM) library](https://github.com/javer/influxdb-odm)
into SonataAdminBundle so that you can persist and retrieve objects to and from InfluxDB.

[![Build Status](https://secure.travis-ci.org/javer/JaverInfluxDBAdminBundle.png?branch=master)](http://travis-ci.org/javer/JaverInfluxDBAdminBundle)

Installation
============

Make sure Composer is installed globally, as explained in the
[installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

Applications that use Symfony Flex
----------------------------------

Open a command console, enter your project directory and execute:

```console
$ composer require javer/influxdb-admin-bundle
```

Applications that don't use Symfony Flex
----------------------------------------

### Step 1: Download the Bundle

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```console
$ composer require javer/influxdb-admin-bundle
```

### Step 2: Enable the Bundle

Then, enable the bundle by adding it to the list of registered bundles
in the `config/bundles.php` file of your project:

```php
// config/bundles.php

return [
    // ...
    Javer\InfluxDB\AdminBundle\JaverInfluxDBAdminBundle::class => ['all' => true],
];
```

Configuration
=============

Full configuration options:

```yaml
javer_influx_db_admin:
    templates:
        form:
            - '@SonataAdmin/Form/form_admin_fields.html.twig'
        filter:
            - '@SonataAdmin/Form/filter_admin_fields.html.twig'
        types:
            list:
                array:      "@SonataAdmin/CRUD/list_array.html.twig"
                boolean:    "@SonataAdmin/CRUD/list_boolean.html.twig"
                date:       "@SonataAdmin/CRUD/list_date.html.twig"
                time:       "@SonataAdmin/CRUD/list_time.html.twig"
                datetime:   "@SonataAdmin/CRUD/list_datetime.html.twig"
                text:       "@SonataAdmin/CRUD/base_list_field.html.twig"
                trans:      "@SonataAdmin/CRUD/list_trans.html.twig"
                string:     "@SonataAdmin/CRUD/base_list_field.html.twig"
                smallint:   "@SonataAdmin/CRUD/base_list_field.html.twig"
                bigint:     "@SonataAdmin/CRUD/base_list_field.html.twig"
                integer:    "@SonataAdmin/CRUD/base_list_field.html.twig"
                decimal:    "@SonataAdmin/CRUD/base_list_field.html.twig"
                identifier: "@SonataAdmin/CRUD/base_list_field.html.twig"
            show:
                array:      "@SonataAdmin/CRUD/show_array.html.twig"
                boolean:    "@SonataAdmin/CRUD/show_boolean.html.twig"
                date:       "@SonataAdmin/CRUD/show_date.html.twig"
                time:       "@SonataAdmin/CRUD/show_time.html.twig"
                datetime:   "@SonataAdmin/CRUD/show_datetime.html.twig"
                text:       "@SonataAdmin/CRUD/base_show_field.html.twig"
                trans:      "@SonataAdmin/CRUD/show_trans.html.twig"
                string:     "@SonataAdmin/CRUD/base_show_field.html.twig"
                smallint:   "@SonataAdmin/CRUD/base_show_field.html.twig"
                bigint:     "@SonataAdmin/CRUD/base_show_field.html.twig"
                integer:    "@SonataAdmin/CRUD/base_show_field.html.twig"
                decimal:    "@SonataAdmin/CRUD/base_show_field.html.twig"
```

Admin class definition
======================

Example of `CpuLoadAdmin` definition:

```yaml
# config/services.yaml
services:
    acme.admin.cpu_load:
        class: App\Admin\CpuLoadAdmin
        arguments: [ ~, App\Measurement\CpuLoad, ~ ]
        tags:
            - { name: sonata.admin, manager_type: influxdb, label: 'CPU Load', pager_type: simple }
```

Please note that you must use `influxdb` as `manager_type` to work with InfluxDB measurement class.
Pager `pager_type` can be either `default` or `simple`.

Example of `CpuLoad` measurement class:

```php
// src/Measurement/CpuLoad.php
namespace App\Measurement;

use Javer\InfluxDB\ODM\Mapping\Annotations as InfluxDB;

/**
 * @InfluxDB\Measurement(name="cpu_load")
 */
class CpuLoad
{
    /**
     * @InfluxDB\Timestamp(precision="u")
     */
    private ?\DateTime $time = null;

    /**
     * @InfluxDB\Tag(name="server_id", type="integer")
     */
    private ?int $serverId = null;

    /**
     * @InfluxDB\Tag(name="core_number", type="integer")
     */
    private ?int $coreNumber = null;

    /**
     * @InfluxDB\Field(name="load", type="float")
     */
    private ?float $load = null;

    // ...getters and setters
}
```

Example of `CpuLoadAdmin` class:

```php
namespace App\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;

class CpuLoadAdmin extends AbstractAdmin
{
    protected $baseRouteName = 'cpu_load';

    protected $baseRoutePattern = 'cpu_load';

    protected function configureListFields(ListMapper $listMapper): void
    {
        // ...
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper): void
    {
        // ...
    }

    protected function configureShowFields(ShowMapper $showMapper): void
    {
        // ...
    }

    protected function configureFormFields(FormMapper $formMapper): void
    {
        // ...
    }
}
```

Please note that you must explicitly declare `baseRouteName` and `baseRoutePattern`
because they cannot detected automatically from the measurement class name.

List field definition
=====================

These fields are used to display the information inside the list table.

Example
-------

```php
namespace App\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Templating\TemplateRegistry;

class CpuLoadAdmin extends AbstractAdmin
{
    protected function configureListFields(ListMapper $listMapper): void
    {
        $listMapper
            ->addIdentifier('time', TemplateRegistry::TYPE_DATETIME, [
                'format' => 'Y-m-d H:i:s.u',
            ])
            ->add('serverId')
            ->add('load')
            ->add('_actions', 'actions', [
                'actions' => [
                    'show' => [],
                    'edit' => [],
                    'delete' => [],
                ],
            ]);
    }
}
```

Available types
---------------

The most important option for each field is the `type`. The available types include:
* datetime (`TemplateRegistry::TYPE_DATETIME`)
* boolean (`TemplateRegistry::TYPE_BOOLEAN`)
* integer (`TemplateRegistry::TYPE_INTEGER`)
* float (`TemplateRegistry::TYPE_FLOAT`)
* string (`TemplateRegistry::TYPE_STRING`)

If no type is set, the `Admin` class will use the type defined in the doctrine mapping definition.

Filter field definition
=======================

These fields are displayed inside the filter box. They allow you to filter the list of entities by a number of different methods.

Example
-------

```php
namespace App\Admin;

use Javer\InfluxDB\AdminBundle\Filter\DateTimeRangeFilter;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;

class CpuLoadAdmin extends AbstractAdmin
{
    protected function configureDatagridFilters(DatagridMapper $datagridMapper): void
    {
        $datagridMapper
            ->add('time', DateTimeRangeFilter::class)
            ->add('serverId');
    }
}
```

Available types
---------------

The most important option for each filter is the `type`. The available types from namespace `Javer\InfluxDB\AdminBundle\Filter` are:
* BooleanFilter 
* NumberFilter
* StringFilter
* ChoiceFilter
* CallbackFilter
* DateFilter
* DateTimeFilter
* DateRangeFilter
* DateTimeRangeFilter

Form field definition
=====================

These fields are used to edit data on the edit page.

Example
-------

```php
namespace App\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Form\FormMapper;

class CpuLoadAdmin extends AbstractAdmin
{
    protected function configureFormFields(FormMapper $formMapper): void
    {
        $formMapper
            ->add('serverId')
            ->add('load');
    }
}
```

Available types
---------------

* checkbox
* integer
* text
* choice
* datetime

If no type is set, the `Admin` class will use the one set in the doctrine mapping definition.

InfluxDB Proxy Query
====================

The `ProxyQuery` object is used to add missing features from the original Doctrine Query builder:

```php
use Javer\InfluxDB\AdminBundle\Datagrid\ProxyQuery;

$query = $this->measurementManager->createQuery();

$proxyQuery = new ProxyQuery($query);
$proxyQuery->setSortBy('time');
$proxyQuery->setMaxResults(10);

$results = $proxyQuery->execute();
```
