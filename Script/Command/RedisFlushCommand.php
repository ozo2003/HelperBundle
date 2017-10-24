<?php

namespace Sludio\HelperBundle\Script\Command;

use Predis\Client;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RedisFlushCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('sludio:redis:flush')->setDefinition(new InputDefinition([
            new InputArgument('clients', InputArgument::IS_ARRAY | InputArgument::OPTIONAL),
        ]));
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $clients = [];
        foreach ($this->getContainer()->getServiceIds() as $id) {
            if (substr($id, 0, 9) === 'snc_redis' && $this->getContainer()->get($id) instanceof Client) {
                $clients[] = $id;
            }
        }

        if (!empty($clients)) {
            $allowed = $clients;
            $clientsInput = $input->getArgument('clients');
            if (!empty($clientsInput)) {
                foreach ($clientsInput as &$client) {
                    $client = 'snc_redis.'.$client;
                }
                $allowed = array_intersect($clients, $clientsInput);
            }
            foreach ($clients as $snc) {
                if (in_array($snc, $allowed)) {
                    $this->getContainer()->get($snc)->flushdb();
                    $output->writeln('redis database '.$snc.' flushed');
                }
            }
        }
    }
}
