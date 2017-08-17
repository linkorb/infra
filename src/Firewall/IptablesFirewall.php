<?php

namespace Infra\Firewall;

use Infra\Model\Infra;
use Infra\Model\Host;
use Infra\Model\Group;
use Infra\Model\Rule;
use RuntimeException;

class IptablesFirewall
{
    protected $iptables = '/sbin/iptables';
    public function generateScript(Infra $infra, Host $host)
    {
        $out = '# Firewall script generated on ' . date('Y-m-d H:i') . " for " . $host->getName() . "\n";
        $out .= $this->generateScriptHeader();
        $groups = $this->getHostGroups($host);
        $out .= $this->generateLines($infra, $host, $groups, $this->iptables);
        return $out;
    }

    public function generateRules(Infra $infra, Host $host)
    {
        $out = '# Firewall rules generated on ' . date('Y-m-d H:i') . " for " . $host->getName() . "\n";
        $out .= $this->generateRulesHeader();
        $groups = $this->getHostGroups($host);
        $out .= $this->generateLines($infra, $host, $groups, '');
        $out .= "COMMIT\n";
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


    public function generateLines(Infra $infra, Host $host, $groups, $prefix)
    {
        $out = '';
        $out .= '# ======================== Rules for host `' . $host->getName() . "` ========================\n";
        foreach ($host->getRules() as $rule) {
            $out .= '# host:' . $host->getName() . ':' . $rule->getName() . "\n";
            $out .= $this->generateRuleLines($infra, $host, $rule);
        }

        foreach ($groups as $hostGroup) {
            //print_r($rule);
            if (count($hostGroup->getRules())>0) {
                $out .= '# ======================== Rules for hostgroup `' . $hostGroup->getName() . "` ========================\n";

                foreach ($hostGroup->getRules() as $rule) {
                    $out .= '# group:' . $hostGroup->getName() . ':' . $rule->getName() . "\n";
                    $out .= $this->generateRuleLines($infra, $host, $rule);
                }
            }
        }
        return $out;
    }

    protected function generateRuleLines(Infra $infra, Host $host, $rule)
    {
        $out = '';
        $data = [
            'host' => $host
        ];
        $remote = $rule->getRemote();
        $remote = str_replace('*', '', $remote);
        //$comment = ' -m comment --comment "' . $hostGroup->getName() . ':' . $rule->getName() . '"';
        $comment = '';
        if (!$remote) {
            // Rule without remote. Execute as-is
            $out .= trim($prefix . ' ' . $this->processTemplate($rule->getTemplate(), $data)) . "$comment\n";
        } else {
            // Rule with remote(s). Resolve and loop over them
            $remotes = $infra->getHostsByExpression($remote);
            foreach ($remotes as $remote) {
                $data['remote'] = $remote;
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
            if ($host->getProperties()->hasKey($propertyName)) {
                $v = $host->getProperties()->get($propertyName)->getValue();
                $template = str_replace($m, $v, $template);
            } else {
                //$this->addWarning("Host " . $host->getName() . ' doesn)
                return null;
            }
        }
        $out .= $template . "\n";

        return $out;
    }

}
