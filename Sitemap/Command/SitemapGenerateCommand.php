<?php

namespace Sludio\HelperBundle\Sitemap\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SitemapGenerateCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('sludio:sitemap:generate')->setDescription('Regenerate sitemap');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $format = $this->getContainer()->getParameter('sludio_helper.sitemap.format');
        $type = $this->getContainer()->getParameter('sludio_helper.sitemap.type');
        $sitemap = $this->getContainer()->get("sludio_helper.sitemap.{$format}.{$type}");

        $output->writeln('Starting generation...');
        $sitemap->build();
        $output->writeln('Sitemap <info>generated!</info>');
    }
}
