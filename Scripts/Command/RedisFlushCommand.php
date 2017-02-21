<?php

namespace Sludio\HelperBundle\Scripts\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RedisFlushCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('redis:flush')
            ->setDescription('FlushAll redis')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        global $kernel;

        if ('AppCache' == get_class($kernel)) {
            $kernel = $kernel->getKernel();
        }

        foreach ($this->getContainer()->getParameter('sludio_helper.redis.managers') as $redis) {
            $kernel->getContainer()->get('snc_redis.'.$redis)->flushdb();
        }

        $output->writeln('redis database flushed');
    }
}
