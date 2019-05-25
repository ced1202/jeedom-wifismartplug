<?php

class wifismartplugCmd extends cmd
{
    /*     * *************************Attributs****************************** */


    /*     * ***********************Methode static*************************** */


    /*     * *********************Methode d'instance************************* */

    public function execute($_options = array())
    {
        if ($this->getType() == '') {
            return '';
        }

        $action = $this->getLogicalId();
        $eqLogic = $this->getEqlogic();
        $ipsmartplug = $eqLogic->getConfiguration('addr');

        log::add('wifismartplug', 'debug', 'action' . $action);
        log::add('wifismartplug', 'debug', $eqLogic->getLogicalId());

        if ($action == 'refresh') {
            $eqLogic->cron($eqLogic->getId());
            log::add('wifismartplug', 'debug', 'REFRESH !!!');
        }

        /*  a modifier par la suite pour prendre en compte different constructeur */

        /* set  : on */
        if ($action == 'on') {
            $command = '/usr/bin/python ' . dirname(__FILE__) . '/../../3rdparty/smartplug.py  -t ' . $ipsmartplug . ' -c on';
            $result = trim(shell_exec($command));
            log::add('wifismartplug', 'debug', 'action on');
            log::add('wifismartplug', 'debug', $command);
            log::add('wifismartplug', 'debug', $result);
            $eqLogic->cron($eqLogic->getId());
        }

        /* set  : off */
        if ($action == 'off') {
            $command = '/usr/bin/python ' . dirname(__FILE__) . '/../../3rdparty/smartplug.py  -t ' . $ipsmartplug . ' -c off';
            $result = trim(shell_exec($command));
            log::add('wifismartplug', 'debug', 'action off');
            log::add('wifismartplug', 'debug', $command);
            log::add('wifismartplug', 'debug', $result);
            $eqLogic->cron($eqLogic->getId());
        }

        /* set  : nightmodeon */
        if ($action == 'nightmodeon') {
            $command = '/usr/bin/python ' . dirname(__FILE__) . '/../../3rdparty/smartplug.py  -t ' . $ipsmartplug . ' -c nightModeOn';
            $result = trim(shell_exec($command));
            log::add('wifismartplug', 'debug', 'action nightModeOn');
            log::add('wifismartplug', 'debug', $command);
            log::add('wifismartplug', 'debug', $result);
            $eqLogic->cron($eqLogic->getId());
        }

        /* set  : nightmodeoff */
        if ($action == 'nightmodeoff') {
            $command = '/usr/bin/python ' . dirname(__FILE__) . '/../../3rdparty/smartplug.py  -t ' . $ipsmartplug . ' -c nightModeOff';
            $result = trim(shell_exec($command));
            log::add('wifismartplug', 'debug', 'action nightModeOff');
            log::add('wifismartplug', 'debug', $command);
            log::add('wifismartplug', 'debug', $result);
            $eqLogic->cron($eqLogic->getId());
        }
        return '';
    }
}
