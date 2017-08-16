iptables-persistent
===================

The easiest way to persist your iptables rules is to install iptables-persist:

    apt-get install iptables-persistent

This will add a service "iptables-persistent" that is configured to run at boot-time.

It loads iptables rules from /etc/iptables/rules.v4 and /etc/iptables/rules.v6

Infra will update /etc/iptables/rules.v4 during the `infra firewall:install` command.

## Disabling the firewall

    /etc/init.d/iptables-persistent flush

## Enabling the firewall

    /etc/init.d/iptables-persistent start
