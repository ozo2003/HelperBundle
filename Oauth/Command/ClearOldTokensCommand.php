<?php

namespace Sludio\HelperBundle\Oauth\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ClearOldTokensCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('sludio:oauth:tokens:clear')
            ->setDescription('Clears old tokens')
            ->addOption(
                'type',
                null,
                InputOption::VALUE_REQUIRED,
                'Which type of tokens to clear. access, refresh or both (default)',
                null
            )
            ->setHelp(
                <<<EOT
                    The <info>%command.name%</info>clears tokens that are no longer valid.
EOT
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output){
        $type = $input->getOption('type');
        if($type){
            $types = [];
            switch($type){
                case 'access': $types[] = 'access'; break;
                case 'refresh': $types[] = 'refresh'; break;
                case 'both': $types[] = 'access'; $types[] = 'refresh'; break;
            }
            if(!empty($types)){
                $conn = $this->getContainer()->get('doctrine.orm.default_entity_manager')->getConnection();
                foreach($types as $type){
                    $table = $this->getContainer()->getParameter('sludio_helper.oauth.tables')[$type];
                    $sql = "
                        DELETE FROM
                            oauth_access_token
                        WHERE
                            expires_at < UNIX_TIMESTAMP(NOW())
                    ";
                    $conn->query($sql);
                }
            }
        }
    }
}
