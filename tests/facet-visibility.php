<?php

declare(strict_types=1);
require dirname(__DIR__).'/vendor/autoload.php';

use App\Twig\FacetExtension;
use Survos\SearchBundle\Context\ContextProvider;
use Survos\SearchBundle\Search\{AbstractSearch, Query};
use Survos\SearchBundle\Search\Filter\TermFilter;
use Survos\SearchBundle\Search\ResultSet\{ResultSet, FacetTermDistribution};

$provider = new ContextProvider();
$search = new class extends AbstractSearch { public function build(array $options = []): void {} };
$query = new Query();
$provider->init($query, $search);
$results = new ResultSet();
$provider->getCurrentContext()->setResults($results);
$extension = new FacetExtension($provider);
$distribution = (new FacetTermDistribution())->setProperty('form');
$results->setFacetDistributions(['form' => $distribution]);
$check = static function (bool $ok): void { if (!$ok) throw new RuntimeException('Facet visibility regression'); };
$check(!$extension->visible('form'));
$distribution->setValues(['Not assessed' => 172]);
$check(!$extension->visible('form'));
$distribution->setValues(['Not assessed' => 171, 'Upright' => 1]);
$check($extension->visible('form'));
$distribution->setValues([0 => 172]);
$check($extension->visible('form')); // An assessed zero is meaningful.
$distribution->setValues(['Upright' => 0]);
$check(!$extension->visible('form'));
$query->addActiveFilter(new TermFilter('form', ['Upright']));
$check($extension->visible('form'));
$check($extension->visible('range'));
echo "Empty and unassessed facets hidden; real zero and selected facets preserved.\n";
