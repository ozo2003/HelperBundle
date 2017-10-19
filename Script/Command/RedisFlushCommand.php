<?php

namespace Sludio\HelperBundle\Script\Command;

use Predis\Client;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RedisFlushCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('sludio:redis:flush')->setDescription('FlushAll redis');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $clients = [];
        foreach ($this->getContainer()->getServiceIds() as $id) {
            if (substr($id, 0, 9) === 'snc_redis' && $this->getContainer()->get($id) instanceof Client) {
                $clients[] = $id;
            }
        }

        foreach ($clients as $snc) {
            $this->getContainer()->get($snc)->flushdb();
        }

        $output->writeln('redis database flushed');
    }
}
