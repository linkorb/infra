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

## Useful commands

    iptables -L # List rules in table format
    iptables -S # List rules in "Specification" format
    iptables-save > rules.txt # Output current rule specification to textfile
    iptables-restore --verbose < rules.txt # Load rules from textfile

## Example rules

    -A INPUT -s 1.2.3.4 -p tcp --dport 80 -j ACCEPT # Allow incoming http traffic from ip 1.2.3.4
    -A INPUT -m conntrack --ctstate ESTABLISHED,RELATED -j ACCEPT # Enable connection tracking
    -A INPUT -i lo -j ACCEPT # Allow localhost traffic
    -A INPUT -s 1.2.3.4 -p tcp --dport 80 -j ACCEPT -m comment --comment "Allow incoming http traffic from ip 1.2.3.4" # Use comments
