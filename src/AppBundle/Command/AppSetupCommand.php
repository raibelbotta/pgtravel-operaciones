<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use FOS\UserBundle\Model\UserManagerInterface;

/**
 * Description of AppSetupCommand
 *
 * @author Raibel Botta <raibelbotta@gmail.com>
 */
class AppSetupCommand extends ContainerAwareCommand
{
    /**
     * @var UserManagerInterface
     */
    private $userManager;

    public function configure()
    {
        $this
                ->setName('app:setup')
                ->addArgument('username', InputArgument::REQUIRED, 'Admin username')
                ->addArgument('password', InputArgument::REQUIRED, 'Admin password')
                ;
    }

    public function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->userManager = $this->getContainer()->get('fos_user.user_manager');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->createAdminUser($input->getArgument('username'),
                $input->getArgument('password'));

        $output->writeln('Done!');
    }

    private function createAdminUser($username, $password)
    {
        $user = $this->userManager->createUser();
        $user
                ->setEmail($username)
                ->setPlainPassword($password)
                ->setEnabled(true)
                ->addRole('ROLE_SUPER_ADMIN')
                ;

        $this->userManager->updateUser($user);
    }
}
