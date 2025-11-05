# MODX Cloud CLI

Usage:
command [options] [arguments]

Options:

| Option               | Description                                                                                    |
|----------------------|------------------------------------------------------------------------------------------------|
| -h, --help           | Display help for the given command. When no command is given display help for the list command |
| -q, --quiet          | Do not output any message                                                                      |
| -V, --version        | Display this application version                                                               |
| --ansi               | --no-ansi                                                                                      | Force (or disable --no-ansi) ANSI output |
| -n, --no-interaction | Do not ask any interactive question                                                            |
| -v                   | vv                                                                                             |vvv, --verbose  | Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug |

Available commands:

| Command          | Description                                |
|------------------|--------------------------------------------|
| completion       | Dump the shell completion script           |
| config           | [c] Configure connection to MODX           |
| help             | Display help for a command                 |
| list             | List commands                              |
| refresh          | [r] Refresh the MODX cache                 |
| **extras**       |
| extras:clean     | Cleans all extras that are not installed.  |
| extras:list      | List all installed extras.                 |
| extras:upgrade   | Upgrades all extras to the latest version. |
| **plugins**      |
| plugins:disable  | Disable a plugin                           |
| plugins:enable   | Enable a plugin                            |
| plugins:list     | List plugins                               |
| **search**       |
| search:find      | Search for a string                        |
| **settings**     |
| settings:list    | List all system settings                   |
| settings:set     | Set the value of a system setting          |
| **users**        |
| users:activate   | Activate a user                            |
| users:block      | Block a user                               |
| users:create     | Create a new admin user                    |
| users:deactivate | Deactivate a user                          |
| users:list       | Refresh the MODX cache                     |
| users:password   | Change a user's password                   |
| users:unblock    | Unblock a user                             |