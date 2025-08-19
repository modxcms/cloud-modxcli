<?php

namespace MODX\CloudCLI\Commands\Users;

use MODX\CloudCLI\Commands\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\OutputInterface;

class GetList extends Command
{
    public function __invoke(
        OutputInterface $output,
        $verbose = false,
        $activeOnly = false,
        $sort = 'username',
        $username = null,
        $limit = 20,
        $offset = 0
    ): void
    {
        if (!in_array($sort, ['username', 'id'])) {
            $output->writeln("Invalid sort option.");
            return;
        }
        if ($verbose) {
            $output->writeln("Checking for users.");
        }
        $c = $this->modx->newQuery('modUser');
        $c->sortby($sort, 'ASC');
        if ($activeOnly) {
            $c->where([
                'active' => 1
            ]);
        }
        if ($username) {
            $c->where([
                'username:LIKE' => '%' . $username . '%'
            ]);
        }
        $count = $this->modx->getCount('modUser', $c);
        $c->limit($limit, $offset);
        $users = $this->modx->getCollection('modUser', $c);
        $table = new Table($output);
        $header = [
            'ID',
            'Username'
        ];
        if ($verbose) {
            $header[] = 'Active';
            $header[] = 'Blocked';
            $header[] = 'Sudo';
            $header[] = 'Email';
            $header[] = 'Primary Group';
        } else {
            $table->setStyle('compact');
        }
        $table->setHeaders($header);
        /** @var \MODX\Revolution\modUser $user */
        foreach ($users as $user) {
            $row = [
                $user->get('id'),
                $user->get('username')
            ];
            if ($verbose) {
                $row[] = $user->get('active') ? 'Yes' : 'No';
                $row[] = $user->get('blocked') ? 'Yes' : 'No';
                $row[] = $user->get('sudo') ? 'Yes' : 'No';
                $profile = $user->getOne('Profile');
                $row[] = $profile ? $profile->get('email') : '';
                $primaryGroup = $this->getPrimaryGroup($user);
                if ($primaryGroup) {
                    $row[] = $primaryGroup->get('name');
                } else {
                    $row[] = '';
                }
            }
            $table->addRow($row);
        }
        $table->render();

        if ($count == 0) {
            $output->writeln("No Users Found.");
        } else {
            $start = $offset + 1;
            $through = $offset + $limit;
            if ($verbose) {
                if ($count > $limit || $through > $limit) {
                    $output->writeln("Showing $start through $through");
                }
                $output->writeln("Total Users: ".$count);
            } elseif ($count > $limit || $through > $limit) {
                $output->writeln("$start-$through of $count");
            }
        }
    }

    private function getPrimaryGroup($user)
    {
        $c = $this->modx->newQuery('modUserGroup');
        if ($user->get('primary_group') > 0) {
            $c->where([
                'id' => $user->get('primary_group')
            ]);
        } else {
            $c->innerJoin('modUserGroupMember', 'UserGroupMembers');
            $c->where([
                'UserGroupMembers.member' => $user->get('id'),
            ]);
            $c->sortby('UserGroupMembers.rank', 'ASC');
        }
        return $this->modx->getObject('modUserGroup', $c);
    }
}