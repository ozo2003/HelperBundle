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
        global $kernel;

        if ('AppCache' == get_class($kernel)) {
            $kernel = $kernel->getKernel();
        }

        $clients = [];
        foreach ($kernel->getContainer()->getServiceIds() as $id) {
            if (substr($id, 0, 9) === 'snc_redis' && $kernel->getContainer()->get($id) instanceof Client) {
                $clients[] = $id;
            }
        }

        foreach ($clients as $snc) {
            $kernel->getContainer()->get($snc)->flushdb();
        }

        $output->writeln('redis database flushed');
    }
}
