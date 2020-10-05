#!/usr/bin/php
<?php

/**
 * Use MIB-jnx-virtualchassis
 */

$check = new nagiosCheck();

$check->check(getopt("hH:A:C:", array("help")));

class nagiosCheck
{
    const STATE_OK       = 0;
    const STATE_WARNING  = 1;
    const STATE_CRITICAL = 2;
    const STATE_UNKNOWN  = 3;

    // Community string, default "public"
    private $communityString;

    private $oid = array(
        'virtualchassisPorts'  => "iso.3.6.1.4.1.2636.3.40.1.4.1.2.1.4",
    );

    function __construct()
    {
        snmp_set_valueretrieval(SNMP_VALUE_PLAIN);
    }

    /**
     * Entry function that switches to the correct subfunction depending on the device and check
     *
     * @param  array $options array containing cli options
     */

    public function check($options)
    {
        if(isset($options['h']) || isset($options['help']))
        {
            $this->help();
        }

        $this->communityString = isset($options['C']) ? $options['C'] : 'public';

        try {
            $snmpRawInterfaces = snmp2_real_walk($options['H'], $this->communityString, $this->oid['virtualchassisPorts'], 5000000, 5); //5 retries and 5 second timeout
        } catch (Exception $e) {
            echo "Check Failed";
            exit(self::STATE_UNKNOWN);
        }

        $snmpRawInterfaces = array (
            'iso.3.6.1.4.1.2636.3.40.1.4.1.2.1.4.0.17.118.99.112.45.50.53.53.47.49.47.50.46.51.50.55.54.56' => '1',
            'iso.3.6.1.4.1.2636.3.40.1.4.1.2.1.4.0.17.118.99.112.45.50.53.53.47.49.47.51.46.51.50.55.54.56' => '1',
            'iso.3.6.1.4.1.2636.3.40.1.4.1.2.1.4.1.17.118.99.112.45.50.53.53.47.49.47.50.46.51.50.55.54.56' => '1',
            'iso.3.6.1.4.1.2636.3.40.1.4.1.2.1.4.1.17.118.99.112.45.50.53.53.47.49.47.51.46.51.50.55.54.56' => '1',
            'iso.3.6.1.4.1.2636.3.40.1.4.1.2.1.4.2.17.118.99.112.45.50.53.53.47.49.47.50.46.51.50.55.54.56' => '1',
            'iso.3.6.1.4.1.2636.3.40.1.4.1.2.1.4.2.17.118.99.112.45.50.53.53.47.49.47.51.46.51.50.55.54.56' => '1',
            'iso.3.6.1.4.1.2636.3.40.1.4.1.2.1.4.3.17.118.99.112.45.50.53.53.47.49.47.50.46.51.50.55.54.56' => '1',
            'iso.3.6.1.4.1.2636.3.40.1.4.1.2.1.4.3.17.118.99.112.45.50.53.53.47.49.47.51.46.51.50.55.54.56' => '1',
            'iso.3.6.1.4.1.2636.3.40.1.4.1.2.1.4.4.17.118.99.112.45.50.53.53.47.49.47.50.46.51.50.55.54.56' => '1',
            'iso.3.6.1.4.1.2636.3.40.1.4.1.2.1.4.4.17.118.99.112.45.50.53.53.47.49.47.51.46.51.50.55.54.56' => '1',
        );

        /**
         * Loop all the interfaces. If one vcp interface is down it means a member must be down.
         * 1 == up; 2 == down
         */

        $downInterfaces = 0;

        foreach($snmpRawInterfaces as $oid => $value)
        {
            if($value == 2)
            {
                $downInterfaces++;
            }
        }

        if($downInterfaces == 0)
        {
            echo "Virtual chassis ok";
            exit(self::STATE_OK);
        }
        else
        {
            echo "Virtual chassis degraded";
            exit(self::STATE_CRITICAL);
        }
    }

    /**
     * Displays the help information
     */

    private function help()
    {
        echo "
        Check plugin that checks the status of a Juniper virtual chassis

        // Base parameters
        -H hostname

        -C the snmp v2 community string. Default public

        \010\010\010\010\010\010\010\010";
        exit;
    }
}
