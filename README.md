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

| Command          | Description                                     |
|------------------|-------------------------------------------------|
| completion       | Dump the shell completion script                |
| config           | [c] Configure connection to MODX                |
| help             | Display help for a command                      |
| list             | List commands                                   |
| refresh          | [r] Refresh the MODX cache                      |
| **extras**       |
| extras:clean     | [eC] Cleans all extras that are not installed.  |
| extras:list      | [eL] List all installed extras.                 |
| extras:upgrade   | [eU] Upgrades all extras to the latest version. |
| **plugins**      |
| plugins:disable  | [pD] Disable a plugin                           |
| plugins:enable   | [pE] Enable a plugin                            |
| plugins:list     | [pL] List plugins                               |
| **settings**     |
| settings:list    | [sL] List all system settings                   |
| settings:set     | [sU] Set the value of a system setting          |
| **users**        |
| users:activate   | [uA] Activate a user                            |
| users:block      | [uB] Block a user                               |
| users:create     | [uC] Create a new admin user                    |
| users:deactivate | [uD] Deactivate a user                          |
| users:list       | [uL] Refresh the MODX cache                     |
| users:password   | [uP] Change a user's password                   |
| users:unblock    | [uU] Unblock a user                             |