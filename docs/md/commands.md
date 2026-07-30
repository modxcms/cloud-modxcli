---
sidebar_position: 3
---

# Commands

## Usage:  
`command [options] [arguments]`

## Options:  
| option               | description                                                                                        |
|----------------------|----------------------------------------------------------------------------------------------------|
| -h, --help           | Display help for the given command. When no command is given display help for the list command     | 
| -q, --quiet          | Do not output any message                                                                          |  
| -V, --version        | Display this application version                                                                   |
| --ansi --no-ansi     | Force (or disable --no-ansi) ANSI output                                                           |
| -n, --no-interaction | Do not ask any interactive question                                                                |
| -v vv vvv, --verbose | Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug |

## Available commands:
| command                              | description                                |
|--------------------------------------|--------------------------------------------|
| [about](#about)                      | [a] Information about this MODX install    |
| [config](#config)                    | [c] Configure connection to MODX           |
| [help](#help)                        | Display help for a command                 |
| [list](#list)                        | List commands                              |
| [refresh](#refresh)                  | [r] Refresh the MODX cache                 |
| **extras**                           |
| [extras:clean](#extrasclean)         | Cleans all extras that are not installed.  |
| [extras:list](#extraslist)           | List all installed extras.                 |
| [extras:upgrade](#extrasupgrade)     | Upgrades all extras to the latest version. |
| **plugins**                          |
| [plugins:disable](#pluginsdisable)   | Disable a plugin                           |
| [plugins:enable](#pluginsenable)     | Enable a plugin                            |
| [plugins:list](#pluginslist)         | List plugins                               |
| **search**                           |
| [search:find](#searchfind)           | Search for a string                        |
| [search:replace](#searchreplace)     | Search and replace a string                |
| **settings**                         |
| [settings:list](#settingslist)       | List all system settings                   |
| [settings:set](#settingsset)         | Set the value of a system setting          |
| **users**                            |
| [users:activate](#usersactivate)     | Activate a user                            |
| [users:block](#usersblock)           | Block a user                               |
| [users:create](#userscreate)         | Create a new admin user                    |
| [users:deactivate](#usersdeactivate) | Deactivate a user                          |
| [users:list](#userslist)             | List users                                 |
| [users:password](#userspassword)     | Change a user's password                   |
| [users:unblock](#usersunblock)       | Unblock a user                             |

-----------
### about
#### Description:
Information about this MODX install

#### Usage:
about  
a 

-----------
### config
#### Description:
Configure connection to MODX

#### Usage: 
      config [options] 
      c

#### Options:
      --core-path=CORE-PATH    
      --config-key=CONFIG-KEY  

-----------
### help
#### Description:
Display help for a command

#### Usage:
      help [command] 

-----------
### list
#### Description:
List all available commands

#### Usage:
      list

#### Arguments:
      namespace         The namespace to list commands for

#### Options
      --raw             To output raw command list
      --format=FORMAT   The output format (txt, xml, json, or md) [default: "txt"]
      --short           To skip describing commands' arguments


-----------
### refresh
#### Description:
Refresh the MODX cache

#### Usage:
      refresh
      r

-----------
### extras:clean
#### Description:
Cleans all extras that are not installed.

#### Usage:
      extras:clean

-----------
### extras:list
#### Description:
List all installed extras.

#### Usage:
      extras:list [options]

#### Options:
      --updates-only    
      --limit=LIMIT      [default: 20]
      --offset=OFFSET    [default: 0]

-----------
### extras:upgrade
#### Description:
Upgrades all extras to the latest version.

#### Usage:
      extras:upgrade [options]

#### Options:
      --clean

----------
### plugins:disable
#### Description:
Disable a plugin

#### Usage:
      plugins:disable [name-or-id]

----------
### plugins:enable
#### Description:
Enable a plugin

#### Usage:
      plugins:enable [name-or-id]

----------
### plugins:list
#### Description:
List plugins

#### Usage:
      plugins:list [options]

#### Options:
      --show-inactive    
      --sort=SORT         [default: "name"]
      --limit=LIMIT       [default: 20]
      --offset=OFFSET     [default: 0]

----------
### search:find
#### Description:
Search for a string

#### Usage:
      search:find [options] [--] [query]

#### Options:
      -c, --chunks                     Search in chunks
      --chunk-fields=CHUNK-FIELDS      
      -p, --plugins                    Search in plugins
      --plugin-fields=PLUGIN-FIELDS    
      -r, --resources                  Search in resources
      --resource-fields=RESOURCE-FIELDS
      -s, --snippets                   Search in snippets
      --snippet-fields=SNIPPET-FIELDS  
      -t, --templates                  Search in templates
      --template-fields=TEMPLATE-FIELDS
      -b, --tvs                        Search in TVs
      --tv-fields=TV-FIELDS            
      --limit=LIMIT                    [default: 20]
      --offset=OFFSET                  [default: 0]

----------
### search:replace
#### Description:
Search and replace a string

#### Usage:
      search:replace [options] [--] [query]

#### Options:
      --replace=REPLACE                
      --regex=REGEX                    
      -c, --chunks                     Search in chunks
      --chunk-fields=CHUNK-FIELDS      
      -p, --plugins                    Search in plugins
      --plugin-fields=PLUGIN-FIELDS    
      -r, --resources                  Search in resources
      --resource-fields=RESOURCE-FIELDS
      -s, --snippets                   Search in snippets
      --snippet-fields=SNIPPET-FIELDS  
      -t, --templates                  Search in templates
      --template-fields=TEMPLATE-FIELDS
      -b, --tvs                        Search in TVs
      --tv-fields=TV-FIELDS            
      --limit=LIMIT                    [default: 20]
      --offset=OFFSET                  [default: 0]

----------
### settings:list
#### Description:
List all system settings

#### Usage:
      settings:list [options] [--] [key]

#### Options:
      --namespace=NAMESPACE  
      --area=AREA            
      --context=CONTEXT      
      --limit=LIMIT          [default: 20]
      --offset=OFFSET        [default: 0]

----------
### settings:set
#### Description:
Set the value of a system setting

#### Usage:
      settings:set [options] [--] [key] [value]

#### Options:
      --namespace=NAMESPACE  [default: "core"]
      --area=AREA            [default: "default"]
      --context=CONTEXT      
      --new                  

----------
### users:list
#### Description:
List users

#### Usage:
      users:list [options]

#### Options:
      --active-only    
      --sort=SORT       [default: "username"]
      --username=USERNAME
      --limit=LIMIT     [default: 20]
      --offset=OFFSET   [default: 0]

----------
### users:activate
#### Description:
Activate a user

#### Usage:
      users:activate [name-or-id]

----------
### users:deactivate
#### Description:
Deactivate a user

#### Usage:
      users:deactivate [name-or-id]

----------
### users:block
#### Description:
Block a user

#### Usage:
      users:block [name-or-id]

----------
### users:unblock
#### Description:
Unblock a user

#### Usage:
      users:unblock [name-or-id]

----------
### users:password
#### Description:
Change a user's password

#### Usage:
      users:password [name-or-id] [password] [options]

#### Options:
      --reset

----------
### users:create
#### Description:
Create a new admin user

#### Usage:
      users:create [options]

#### Options:
      --email=EMAIL        
      --username=USERNAME  
      --password=PASSWORD  -
