iptables notes
==============


## Completely clear all iptables rules and chains

From https://www.digitalocean.com/community/tutorials/how-to-list-and-delete-iptables-firewall-rules

First, set the default policies for each of the built-in chains to ACCEPT

    sudo iptables -P INPUT ACCEPT
    sudo iptables -P FORWARD ACCEPT
    sudo iptables -P OUTPUT ACCEPT

Then flush the `nat` and `mangle` tables, flush all chains (-F), and delete all non-default chains (-X):

    sudo iptables -t nat -F
    sudo iptables -t mangle -F
    sudo iptables -F
    sudo iptables -X
