<?php

namespace Infra\Firewall;

use Infra\Infra;
use Infra\Resource\HostResource;
use Infra\Resource\HostGroupResource;
use Infra\Resource\FirewallRuleResource;
use RuntimeException;

class IptablesFirewall
{
    protected $iptables = '/sbin/iptables';
    public function generateScript(Infra $infra, HostResource $host)
    {
        $out = '# Firewall script generated on ' . date('Y-m-d H:i') . " for " . $host->getName() . "\n";
        $out .= $this->generateScriptHeader();
        $groups = $this->getHostGroups($host);
        $out .= $this->generateLines($infra, $host, $groups, $this->iptables);
        return $out;
    }

    public function generateRules(Infra $infra, HostResource $host)
    {
        $out = '# Firewall rules generated on ' . date('Y-m-d H:i') . " for " . $host->getName() . "\n";
        $out .= $this->generateRulesHeader();
        $groups = $this->getHostGroups($host);
        $out .= $this->generateLines($infra, $host, $groups, '');
        $out .= "\nCOMMIT\n";
        return $out;
    }

    private function getHostGroups($host)
    {
        return $host->getHostGroups();
    }

    public function generateScriptHeader()
    {
        $out = '';
        $out .= $this->iptables . " --flush\n";
        $out .= $this->iptables . " --delete-chain\n";

        $out .= $this->iptables . " -P INPUT DROP\n";
        $out .= $this->iptables . " -P FORWARD DROP\n";
        $out .= $this->iptables . " -P OUTPUT ALLOW\n";

        // Allow incoming ping
        $out .= $this->iptables . " -A INPUT -p icmp --icmp-type echo-request -j ACCEPT\n";
        $out .= $this->iptables . " -A OUTPUT -p icmp --icmp-type echo-reply -j ACCEPT\n";

        // Allow outgoing ping
        $out .= $this->iptables . " -A OUTPUT -p icmp --icmp-type echo-request -j ACCEPT\n";
        $out .= $this->iptables . " -A INPUT -p icmp --icmp-type echo-reply -j ACCEPT\n";

        // Allow all loopback traffic
        $out .= $this->iptables . " -A INPUT -i lo -j ACCEPT\n";
        $out .= $this->iptables . " -A OUTPUT -o lo -j ACCEPT\n";

        $out .= $this->iptables . " -A INPUT -m conntrack --ctstate ESTABLISHED,RELATED -j ACCEPT\n";
        return $out;
    }

    public function generateRulesHeader()
    {
        $out =
"*security
:INPUT ACCEPT [0:0]
:FORWARD ACCEPT [0:0]
:OUTPUT ACCEPT [0:0]
COMMIT
*raw
:PREROUTING ACCEPT [0:0]
:OUTPUT ACCEPT [0:0]
COMMIT
*nat
:PREROUTING ACCEPT [0:0]
:INPUT ACCEPT [0:0]
:OUTPUT ACCEPT [0:0]
:POSTROUTING ACCEPT [0:0]
COMMIT
*mangle
:PREROUTING ACCEPT [0:0]
:INPUT ACCEPT [0:0]
:FORWARD ACCEPT [0:0]
:OUTPUT ACCEPT [00:0]
:POSTROUTING ACCEPT [0:0]
COMMIT
*filter
:INPUT DROP [0:0]
:FORWARD DROP [0:0]
:OUTPUT ACCEPT [0:0]
";

        return $out;
    }


    public function generateLines(Infra $infra, HostResource $host, $groups, $prefix)
    {
        $out = '';
        // $out .= '# ======================== Rules for host `' . $host->getName() . "` ========================\n";
        foreach ($host->getFirewallRules() as $rule) {
            $out .= "\n# rule=" . $rule->getName() . " hosts='" . $rule->getHostsAsString() . "' remoteHosts='" . $rule->getRemoteHostsAsString() . "'\n";
            $out .= $this->generateRuleLines($infra, $host, $rule);
        }

        // foreach ($groups as $hostGroup) {
        //     //print_r($rule);
        //     if (count($hostGroup->getFirewallRules())>0) {
        //         $out .= '# ======================== Rules for hostgroup `' . $hostGroup->getName() . "` ========================\n";

        //         foreach ($hostGroup->getFirewallRules() as $rule) {
        //             $out .= '# group:' . $hostGroup->getName() . ':' . $rule->getName() . "\n";
        //             $out .= $this->generateRuleLines($infra, $host, $rule);
        //         }
        //     }
        // }
        return $out;
    }

    protected function generateRuleLines(Infra $infra, HostResource $host, $rule)
    {
        $out = '';
        $data = [
            'host' => $host
        ];
        $remoteHosts = $rule->getRemoteHosts();
        $prefix = '';
        // $comment = '';
        if (count($remoteHosts)==0) {
            // Rule without remote. Execute as-is
            $comment = " -m comment --comment \"host='" . $host->getName() . "' rule='" . $rule->getName() . "'\"";
            $out .= trim($prefix . ' ' . $this->processTemplate($rule->getTemplate(), $data)) . "$comment\n";
        } else {
            // Rule with remote(s). loop over them
            foreach ($remoteHosts as $remoteHost) {
                $comment = " -m comment --comment \"host='" . $host->getName() . "' remoteHost='" . $remoteHost->getName() . "' rule='" . $rule->getName() . "'\"";
                $data['remote'] = $remoteHost;
                $data['remoteHost'] = $remoteHost;
                $out .= trim($prefix . ' ' . $this->processTemplate($rule->getTemplate(), $data)) . "$comment\n";
            }
        }
        return $out;
    }

    public function processTemplate($template, $data = [])
    {
        $out = '';
        preg_match_all('/{(.*?)}/', $template, $matches);
        //print_r($matches);
        foreach ($matches[0] as $i=>$m) {
            $e = trim($matches[1][$i]);
            $part = explode('.', $e);
            if (count($part)!=2) {
                throw new RuntimeException("Rule variables should consist of exactly 2 parts: " . $e);
            }
            $variableName = $part[0];
            $propertyName = $part[1];
            if (!isset($data[$variableName])) {
                throw new RuntimeException("Undefined variable: " . $variableName);
            }
            $host = $data[$variableName];
            if (isset($host[$propertyName])) {
                $v = $host[$propertyName];
                $template = str_replace($m, $v, $template);
            } else {
                throw new RuntimeException("Host " . $host->getName() . ' does not have property ' . $propertyName);
            }
        }
        $out .= $template . "\n";

        return $out;
    }

}
