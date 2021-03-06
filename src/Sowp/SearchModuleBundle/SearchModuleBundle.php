<?php

namespace Sowp\SearchModuleBundle;

use Sowp\SearchModuleBundle\DependencyInjection\Compiler\SearchProviderPass;
use Sowp\SearchModuleBundle\DependencyInjection\Compiler\SearchTemplateRegisterPass;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class SearchModuleBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new SearchProviderPass());
        $container->addCompilerPass(new SearchTemplateRegisterPass());
    }
}
