Infra
=====

Powertools for managing your infrastructure

## Introduction

Infra is a set of tools that can perform actions on your infrastructure.

It first loads your infrastructure configuration (Hosts, HostGroups, Properties, FirewallRules and the infra itself).
This configuration can be loaded from a local .yml file, or a remote inventory system over HTTP(s).

## Configuration

Your first mission is to define your inventory file. Check the example [example/infra.yml](example/infra.yml).

This example file defines 3 hosts (`app0`, `db0m` and `db0s`) and 2 host groups (`app-servers` and `db-servers`). Both host groups "extend" the "all" group.

For each host it defines a set of custom properties. You can name your properties whatever you want, they do not have a special meaning to Infra, but you will use them as variables later.

Additionally you'll notice each host_group can list a set of firewall_rules.

## Pointing to your configuration

Create a `.env` file (use `.env.dist` as an example) to tell infra where to find your infrastructure definition file.
This can be a local file or a http(s) address. Infra will look for a `.env` file in the current directory, or fall back to `~/.infra`.

## Verifying the configuration

Run `infra config` to load the infra from your configuration and output it to the console. Verify if your inventory was parsed correctly.

## Generating firewall rules

Run `infra firewall:show app0` (or short-hand notation `infra f:s app0`) to let infra generate firewall rules for the specified host.

It will output the rules to the console like this:

```
# ======================== Rules for hostgroup `app-servers` ========================
# app-servers:allow_ssh_from_db-servers
-A INPUT -i eth0 -s 10.0.0.101 --dport 22 -j ACCEPT
-A INPUT -i eth0 -s 10.0.0.102 --dport 22 -j ACCEPT
# ======================== Rules for hostgroup `all` ========================
# all:allow_dns
-A INPUT -p udp --sport 53 -j ACCEPT
```

Note how you'll find rules defined for both the "app-servers" (as app0 is an app-server), and the extended group "all".

Additionally, you'll notice that the app server has 2 rules generated for the 2 db servers. In case you'll add 5 more db servers, infra will
automatically generate a rule for each of them (7 in total).

## Installing firewall rules

Run `infra firewall:install app0` to install the firewall. Infra will ssh into the server and:

1. backup the current iptable rules using `iptables-save`. The backup is created in /tmp, and the exact filename will be displayed on the console.
2. Copy the firewall rules to the server
3. Run `iptables-restore` on the rules file and output the results.

## License

MIT. Please refer to the [license file](LICENSE) for details.

## Brought to you by the LinkORB Engineering team

<img src="http://www.linkorb.com/d/meta/tier1/images/linkorbengineering-logo.png" width="200px" /><br />
Check out our other projects at [linkorb.com/engineering](http://www.linkorb.com/engineering).

Btw, we're hiring!
