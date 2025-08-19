<?php

namespace MODX\CloudCLI\Commands\Users;

use MODX\CloudCLI\Commands\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class User extends Command
{
    /** @var $user \MODX\Revolution\modUser */
    private $user;


    private function prepare(InputInterface $input, OutputInterface $output): bool
    {
        $nameOrID = $input->getArgument('nameOrID');
        $verbose = $input->getOption('verbose');
        if (empty($nameOrID)) {
            $output->writeln("No user name or ID provided.");
            return false;
        }

        if ((int) $nameOrID > 0) {
            if ($verbose) {
                $output->writeln("Checking for user ID: " . $nameOrID);
            }
            $user = $this->modx->getObject('modUser', ['id' => $nameOrID]);
        } else {
            if ($verbose) {
                $output->writeln("Checking for user name: " . $nameOrID);
            }
            $user = $this->modx->getObject('modUser', ['username' => $nameOrID]);
        }

        if (empty($user)) {
            $output->writeln("User not found.");
            return false;
        }
        $this->user = $user;
        return true;
    }

    public function activate(InputInterface $input, OutputInterface $output): void
    {
        if (!$this->prepare($input, $output)) {
            return;
        }
        if ($this->user->get('active')) {
            $output->writeln("User is already active.");
            return;
        }
        $this->user->set('active', 1);
        if ($this->user->save()) {
            $output->writeln("User activated.");
        } else {
            $output->writeln("User could not be activated.");
        }
    }

    public function deactivate(InputInterface $input, OutputInterface $output): void
    {
        if (!$this->prepare($input, $output)) {
            return;
        }
        if (!$this->user->get('active')) {
            $output->writeln("User is already inactive.");
            return;
        }
        $this->user->set('active', 0);
        if ($this->user->save()) {
            $output->writeln("User deactivated.");
        } else {
            $output->writeln("User could not be deactivated.");
        }
    }

    public function block(InputInterface $input, OutputInterface $output): void
    {
        if (!$this->prepare($input, $output)) {
            return;
        }
        $profile = $this->user->getOne('Profile');
        if (empty($profile)) {
            $output->writeln("User has no profile.");
            return;
        }
        if ($profile->get('blocked')) {
            $output->writeln("User is already blocked.");
            return;
        }
        $profile->set('blocked', 1);
        if ($profile->save()) {
            $output->writeln("User blocked.");
        } else {
            $output->writeln("User could not be blocked.");
        }
    }

    public function unblock(InputInterface $input, OutputInterface $output): void
    {
        if (!$this->prepare($input, $output)) {
            return;
        }
        $profile = $this->user->getOne('Profile');
        if (empty($profile)) {
            $output->writeln("User has no profile.");
            return;
        }
        if (!$profile->get('blocked')) {
            $output->writeln("User is not blocked.");
            return;
        }
        $profile->set('blocked', 0);
        $profile->set('blocked_until', 0);
        if ($profile->save()) {
            $output->writeln("User unblocked.");
        } else {
            $output->writeln("User could not be unblocked.");
        }
    }

    public function password(InputInterface $input, OutputInterface $output): void
    {
        $verbose = $input->getOption('verbose');
        $reset = $input->getOption('reset');
        $password = $input->getOption('password');
        if (!$this->prepare($input, $output)) {
            return;
        }
        if ($reset) {
            $password = $this->user->generatePassword();
            $this->user->set('password', $password);
            if ($this->user->save()) {
                if ($verbose) {
                    $output->writeln("User password reset.");
                }
                $output->writeln("New password: " . $password);
            } else {
                $output->writeln("User password could not be reset.");
            }
        } else {
            if (empty($password)) {
                $output->writeln("No password provided.");
                return;
            }
            $this->user->set('password', $password);
            if ($this->user->save()) {
                if ($verbose) {
                    $output->writeln("User password changed.");
                }
            } else {
                $output->writeln("User password could not be changed.");
            }
        }
    }

    public function create(InputInterface $input, OutputInterface $output): void
    {
        $verbose = $input->getOption('verbose');
        $email = $input->getOption('email');
        $username = $input->getOption('username');
        $password = $input->getOption('password');
        $user = $this->modx->newObject('modUser');
        if (empty($email)) {
            $output->writeln("No email provided.");
            return;
        }
        if (empty($username)) {
            $username = $email;
        }
        $userCheck = $this->modx->getObject('modUser', ['username' => $username]);
        if (!empty($userCheck)) {
            $output->writeln("User already exists.");
            return;
        }
        $profileCheck = $this->modx->getObject('modUserProfile', ['email' => $email]);
        if (!empty($profileCheck)) {
            $output->writeln("Email already exists.");
            return;
        }
        $user->set('username', $username);
        $user->set('active', true);
        $user->save();
        $user->setSudo(true);
        $profile = $this->modx->newObject('modUserProfile');
        $profile->set('email', $email);
        $user->addOne($profile, 'Profile');
        $user->save();
        if (empty($password)) {
            $password = $user->generatePassword();
            $output->writeln("New password: " . $password);
        }
        $user->changePassword($password, $password, false);
        if ($verbose) {
            $output->writeln("User created.");
        }
    }

}