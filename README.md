<img src="https://upr.io/pRyK8d.png" style="width: 100%" />

## Introduction

Infra allows you to define all objects in your infrastructure in a way that you can load it
into a GraphQL service. For example: Hosts, HostGroups, Users, Dns, Monitoring, BackupRules, Deployments, etc, and all of their relationships.

Accessing your infrastructure as a graph allows you to do a couple of cool things:

* Generate multiple inventory definitions from a single source (ansible, puppet, rundeck, etc)
* Generate configuration files for backup and monitoring services
* Generate complex firewall scripts
* Configure objects in context. I.e. configure dns records as part of your Deployment, or monitoring rules as part of your HostGroup
* Generate dashboard configurations based on infra objects
* Generate comprehensive infrastructure documentation
* Create scripts that operate on your infrastructure data in language agnostic ways
* As your configuration consists of YAML files only, everything is heavily scriptable (for both importing and exporting data from all relevant systems) and managable through version control.

## Resources

You define your infrastructure as a set of Resources in YAML files. The format is heavily inspired by Kubernetes. Here's an example:

```yaml
---
kind: Host
metadata:
  name: my-app-server
  description: This my application server
spec:
  os: Ubuntu 16.04
  publicIp: 192.168.1.1
  privateIp: 10.0.1.1
  hostGroups: app-servers
---
kind: HostGroup
metadata:
  name: app-servers
  description: All my application server
  labels:
    color: green
spec: ~
```

For a more comprehensive example, see the `example/` directory in this repository.

You can define your resources in standalone YAML files, or (as in this example) configure multiple resources in one file using YAML's [multiple document feature](https://en.wikipedia.org/wiki/YAML#Advanced_components)

If you're familiar with Kubernetes, you'll feel right at home.

Each resource specifies it's type using the `kind` key. A list of available resource types is provided below.

Resources contain a `metadata` key that allows you to specify the resource name, description, a set of labels and annotations (more on metadata below).

The `spec` key configures the resource, and it's available keys depend on the `kind` of resource you're creating.

## Installation

    cd /opt
    git clone git@github.com:linkorb/infra.git
    cd infra
    composer install # See https://getcomposer.org/download/ if you don't have composer installed yet

**NOTE:** It is **strongly** recommended to add `bin/` to your environment's `PATH` variable, so you can easily invoke the `infra` command-line tool from anywhere on your system.

If you're using Bash, you can do this by adding the following line to your `~/.bashrc` file:

    PATH=/opt/infra/bin:$PATH

Don't forget to open a new bash session for the changes to take effect (close your terminal, or just run `bash` again).

## Configuration

Infra decides where to load it's infrastructure data based on the `INFRA_CONFIG` environment variable. It should contain a path to directory that contains a set of YAML files describing your infrastructure.

You can define the variable wherever you like. For example in your `~/.bashrc`, or a `.env` file in the root of the infra directory (i.e. `/opt/infra/.env`). A `.env.dist` file is provided.

If `INFRA_CONFIG` is undefined, it will point to the `example/` directory as a default.

## Example project

The `example/` directory contains an example infrastructure configuration (You can view `example/README.md` for it's details). To load it, make sure your `INFRA_CONFIG` variable points to the `example/` directory (absolute path required), or simply leave `INFRA_CONFIG` undefined: the example project is loaded by default.

It is recommended to explore the example project first, before using infra to configure your own infrastructure. The example configuration will contain examples for all relevant resource types, configured in a sensible way to show you all available functionality.

Once you're ready to define your own infrastructure, simply point `INFRA_CONFIG` to a new directory (ideally a git repository), and start creating configuration files there.

## Usage

You can get a list of available commands by simply typing `infra list` or just `infra`.

### Exploring using the `get` command.

The `infra get` command lets you explore your infrastructure configuration. Type `infra get --help` for it's options. All arguments are optional, and the more you specify, the more specific your response will be. Some examples:

* `infra get`: returns a list of available resource types (and their aliases)
* `infra get hosts`: returns all defined objects of that resource type. 
* `infra get hg`: you can use any of the available resource type aliases as shortcuts: hg, hostGroup, HostGroup, hostGroups, HostGroups are all equivalent here.
* `infra get Host app0`: returns a resource definition by type and name.
* `infra get HostGroup db hosts`: returns a calculated property from a resource by type, name and propertyname.
 
### Running GraphQL queries

The `infra query` command let's you perform a GraphQL query against your infrastructure configuration.
It reads the query from `stdin` and outputs the response (as json) to `stdout`.

For example:

    infra query < /opt/infra/example/graphql/example.graphql

Introspection is also enabled, so you can execute the following command to get a full export of all supported types with detailed information about available fields:

    infra query < /opt/infra/example/graphql/introspection.graphql

## Listing hosts (host expansion algorithm)

The `infra host:list <hosts>` allows you to list a set of hosts. The list you can provide is quite flexible. It's using the host expansion algorithm which is used in all places in infra where you can specify a set of hosts. Some of the command-line utilities allow you to specify a list of hosts, but also linking to hosts from `spec` sections in your resource definitions follow this algorithm. This command helps you to test this. Some examples:

* `app0`: (i.e., a host name) returns an list containing only the app0 host
* `db`: (i.e., a hostgroup name) returns an list containing all hosts in this hostgroup.
* `app0,db0m`: (i.e., two host name) returns a list containing these specific hosts.
* `app0,db`: (i.e., a host name and a host group name) returns a list containing hosts in the `db` host group, plus the specific `app0` host.
* `db0m,db`: (i.e., a host group name and a host name already in that host group) returns a list containing hosts in the `db` host, not listing the host `db0m` twice.
* `prod-east`: (i.e., a child host group name of `prod`) returns a list in the `prod-east` host group.
* `prod-west`: (i.e., a child host group name of `prod`) returns a list in the `prod-west` host group.
* `prod`: (i.e., a parent host group name) returns a list of all hosts linked to the `prod` host group directly, or through one of it's child host groups (`prod-east` and `prod-west`).

As you can see, you can freely mix and match host names, host group names, and take advantage of hierarchy in host groups.

## Executing commands in bulk over SSH

The `infra host:exec <hosts> <command>` command lets you perform a standard shell command one one or more hosts in bulk.

The first argument is the list of hosts you want to perform the command on (following the *host expansion algorithm* described earlier), and the second argument is the actual command. 

## Generating firewall rules

Infra can generate complex firewall configurations based on very simple rule defintions. For example, it allows you to set up a rule that allows access to a specified port from all hosts in group X to all hosts in group Y over public or private IP addresses. It will dynamically generate the required rules based on your infra graph. You can leverage the full power of the host expansion algorithm (described earlier) to specify source and remote target host lists.

Run `infra firewall:show <hosts>` (or short-hand notation `infra f:s <hosts>`) to let infra generate firewall rules for the specified host.

Running `infra f:s db0m` will output the generated iptables rules to the console like this:

```
#### iptables boilerplate removed for brevity ####

# rule=allow-mysql hosts='db' remoteHosts='app'
-A INPUT -d 10.0.2.1 -s 10.0.1.1 -p tcp --dport 3306 -j ACCEPT -m comment --comment "host='db1m' remoteHost='app1' rule='allow-mysql'"
-A INPUT -d 10.0.2.1 -s 10.0.1.2 -p tcp --dport 3306 -j ACCEPT -m comment --comment "host='db1m' remoteHost='app2' rule='allow-mysql'"

# rule=allow-ssh hosts='prod' remoteHosts=''
-A INPUT -d 10.0.2.1 -s 192.168.99.99 -p tcp --dport 22 -j ACCEPT -m comment --comment "host='db1m' rule='allow-ssh'"

COMMIT
```

Compare these to the definitions in `example/resources/FirewallRule/`;

Note how you'll find rules defined for both the "db" hostgroup (of which db0m is a direct member), and rules for the "prod" hostgroup (of which db0m is only indirectly a member through the "prod" child hostgroup "prod-east").

Additionally, you'll notice that the db0m server has 2 rules generated for the 2 app servers (app1 and app2). In case you'll add 5 more app servers, infra will automatically generate a rule for each of them (7 in total).

## Installing firewall rules

Run `infra firewall:install <hosts>` to install the firewall. Infra will ssh into the host(s) and:

1. backup the current iptable rules using `iptables-save`. The backup is created in /tmp, and the exact filename will be displayed on the console.
2. Copy the firewall rules to the server
3. Run `iptables-restore` on the rules file and output the results.

## Scripts (in your language of choice)

If you'd like to build tools that leverage your infrastructure graph in your language of choice (php, node, python, ruby, bash, etc), scripts are your solution.

Simply add an executable script in the `scripts/` directory of your infrastructure configuration repository, and let them execute GraphQL queries using the `infra query` command (pass in the query over stdin, getting the response json from stdout) and apply any logic you see fit.

Examples are included in the `example/scripts` directory. (examples in more languages welcomed as PRs!)

## License

MIT. Please refer to the [license file](LICENSE) for details.

## Brought to you by the LinkORB Engineering team

<img src="http://www.linkorb.com/d/meta/tier1/images/linkorbengineering-logo.png" width="200px" /><br />
Check out our other projects at [linkorb.com/engineering](http://www.linkorb.com/engineering).

Btw, we're hiring!
