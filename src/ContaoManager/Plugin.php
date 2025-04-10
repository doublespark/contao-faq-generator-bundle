<?php

declare(strict_types=1);

namespace Doublespark\FaqGeneratorBundle\ContaoManager;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use Doublespark\FaqGeneratorBundle\FaqGeneratorBundle;

class Plugin implements BundlePluginInterface
{
    /**
     * {@inheritdoc}
     */
    public function getBundles(ParserInterface $parser): array
    {
        return [
            BundleConfig::create(FaqGeneratorBundle::class)->setLoadAfter([ContaoCoreBundle::class])
        ];
    }
}
