<?php

namespace Sludio\HelperBundle\Translatable\Command;

use Sludio\HelperBundle\Translatable\Repository\TranslatableRepository as Sludio;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TranslationsGenerateCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('sludio:translations:generate')->setDescription('Regenerate translations');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Starting generation...');
        Sludio::getAllTranslations();
        $output->writeln('Translations generated!');
    }
}
