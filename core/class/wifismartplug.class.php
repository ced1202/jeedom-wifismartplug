<?php

/* This file is part of Jeedom.
 *
 * Jeedom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Jeedom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
 */

/* * ***************************Includes********************************* */
require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';
require_once dirname(__FILE__) . '/wifismartplugCmd.class.php';

class wifismartplug extends eqLogic
{
    /*     * *************************Attributs****************************** */
    public static $_widgetPossibility = array('custom' => true);

    public static $_MAC_ADDRESS = 'macAddress';
    public static $_NIGHT_MODE = 'nightmode';
    public static $_NIGHT_MODE_ON = 'nightmodeon';
    public static $_NIGHT_MODE_OFF = 'nightmodeoff';
    public static $_REFRESH = 'refresh';
    public static $_STATE = 'etat';
    public static $_CURRENT_RUNTIME = 'currentRunTime';
    public static $_ALIAS = 'alias';
    public static $_CONFIG_ADDR = 'addr';
    public static $_CONFIG_MODEL = 'model';

    /*     * ***********************Methode static*************************** */

    /**
     * Fonction exécutée automatiquement toutes les minutes
     *
     */
    public static function cron($eqLogicId = null)
    {

        if ($eqLogicId !== null) {
            $eqLogics = array(eqLogic::byId($eqLogicId));
        } else {
            $eqLogics = eqLogic::byType(__CLASS__);
        }

        foreach ($eqLogics as $smartPlug) {
            if ($smartPlug->getIsEnable() == 1) {
                log::add(__CLASS__, 'debug', 'Pull Cron pour wifismartplug');
                $smartplugID = $smartPlug->getId();
                log::add(__CLASS__, 'debug', 'ID : ' . $smartPlug->getId());
                log::add(__CLASS__, 'debug', 'Name : ' . $smartPlug->getName());

                /* ilfaudrait tester sur le model afin d'appeler par la suite la bonne methode pour les infos */

                $smartpluginfo = $smartPlug->getSmartPlugInfo();
            }
        }
    }

    public function getSmartPlugInfo()
    {
        try {
            $changed = false;

            $smartPlugIp = $this->getConfiguration(self::$_CONFIG_ADDR);
            $smartPlugModel = $this->getConfiguration(self::$_CONFIG_MODEL);

            /* first get relay status, nightmode, mac address alias currentruntime from info */
            $command = '/usr/bin/python ' . dirname(__FILE__) . '/../../3rdparty/smartplug.py  -t ' . $smartPlugIp . ' -c info';
            $result = trim(shell_exec($command));
            log::add(__CLASS__, 'debug', 'retour [info]');
            log::add(__CLASS__, 'debug', $command);
            log::add(__CLASS__, 'debug', $result);

            /* decode reponse info */
            $jsonInfo = json_decode($result, true);
            $state = $jsonInfo['system']['get_sysinfo']['relay_state'];
            $runtTime = $jsonInfo['system']['get_sysinfo']['on_time'];
            $mac = $jsonInfo['system']['get_sysinfo']['mac'];
            $alias = $jsonInfo['system']['get_sysinfo']['alias'];
            $nightmode = $jsonInfo['system']['get_sysinfo']['led_off'];

            log::add(__CLASS__, 'debug', 'state : ' . $state);
            log::add(__CLASS__, 'debug', 'mac : ' . $mac);
            log::add(__CLASS__, 'debug', 'alias : ' . $alias);
            log::add(__CLASS__, 'debug', 'runTime : ' . $runtTime);
            log::add(__CLASS__, 'debug', 'nightmode : ' . $nightmode);

            /* set state */
            $cmdState = wifismartplugCmd::byEqLogicIdAndLogicalId($this->getId(), self::$_STATE);
            if (is_object($cmdState)) {
                if ($cmdState->execCmd() == null || $cmdState->execCmd() != $state) {
                    $changed = true;
                    $cmdState->setCollectDate('');
                    $cmdState->event($state);
                }
            }


            /* set nightemod */
            $cmdState = wifismartplugCmd::byEqLogicIdAndLogicalId($this->getId(), self::$_NIGHT_MODE);
            if (is_object($cmdState)) {
                if ($cmdState->execCmd() == null || $cmdState->execCmd() != $nightmode) {
                    $changed = true;
                    $cmdState->setCollectDate('');
                    $cmdState->event($nightmode);
                }
            }


            /* set currentRunTime */
            $cmdState = wifismartplugCmd::byEqLogicIdAndLogicalId($this->getId(), self::$_CURRENT_RUNTIME);
            if (is_object($cmdState)) {
                if ($cmdState->execCmd() == null || $cmdState->execCmd() != $runtTime) {
                    $changed = true;
                    $cmdState->setCollectDate('');
                    $cmdState->event($runtTime);
                }
            }


            /* set macAddress*/
            $cmdState = wifismartplugCmd::byEqLogicIdAndLogicalId($this->getId(), self::$_MAC_ADDRESS);
            if (is_object($cmdState)) {
                if ($cmdState->execCmd() == null || $cmdState->execCmd() != $mac) {
                    $changed = true;
                    $cmdState->setCollectDate('');
                    $cmdState->event($mac);
                }
            }

            /* set alias*/
            $cmdState = wifismartplugCmd::byEqLogicIdAndLogicalId($this->getId(), self::$_ALIAS);
            if (is_object($cmdState)) {
                if ($cmdState->execCmd() == null || $cmdState->execCmd() != $alias) {
                    $changed = true;
                    $cmdState->setCollectDate('');
                    $cmdState->event($alias);
                }
            }


            /* ajout commande pour modele HS110 */

            $model = $this->getConfiguration('model');
            if ($model == 'HS110') {


                /* -- set daily consumption --*/
                $command = '/usr/bin/python ' . dirname(__FILE__) . '/../../3rdparty/smartplug.py  -t ' . $smartPlugIp . ' -c dailyConsumption';
                $result = trim(shell_exec($command));
                log::add(__CLASS__, 'debug', 'retour [dailyConso]');
                log::add(__CLASS__, 'debug', $command);
                log::add(__CLASS__, 'debug', $result);
                $cmdState = wifismartplugCmd::byEqLogicIdAndLogicalId($this->getId(), 'dailyConso');
                if (is_object($cmdState)) {
                    if ($cmdState->execCmd() == null) {
                        $changed = true;
                        $cmdState->setCollectDate('');
                        $cmdState->event($result);
                    }
                }

                /* power and voltage from */
                $command = '/usr/bin/python ' . dirname(__FILE__) . '/../../3rdparty/smartplug.py  -t ' . $smartPlugIp . ' -c realtimeVoltage';
                $result = trim(shell_exec($command));
                log::add(__CLASS__, 'debug', 'retour [realvoltage]');
                log::add(__CLASS__, 'debug', $command);
                log::add(__CLASS__, 'debug', $result);

                /* decode reponse info */
                $jsonInfo = json_decode($result, true);
                $voltage = $jsonInfo['emeter']['get_realtime']['voltage_mv']/1000;
                $power = $jsonInfo['emeter']['get_realtime']['power_mw'] / 1000;

                log::add(__CLASS__, 'debug', 'voltage : ' . $voltage);
                log::add(__CLASS__, 'debug', 'power : ' . $power);


                /*--set current power --*/
                $cmdState = wifismartplugCmd::byEqLogicIdAndLogicalId($this->getId(), 'currentPower');
                if (is_object($cmdState)) {
                    if ($cmdState->execCmd() == null || $cmdState->execCmd() != $power) {
                        $changed = true;
                        $cmdState->setCollectDate('');
                        $cmdState->event($power);
                    }
                }

                /*--set voltage--*/

                $cmdState = wifismartplugCmd::byEqLogicIdAndLogicalId($this->getId(), 'voltage');
                if (is_object($cmdState)) {
                    if ($cmdState->execCmd() == null || $cmdState->execCmd() != $voltage) {
                        $changed = true;
                        $cmdState->setCollectDate('');
                        $cmdState->event($voltage);
                    }
                }


            }


            if ($changed == true) {
                $this->refreshWidget();
            }


        } catch (Exception $e) {
            log::add(__CLASS__, 'debug', $e);
            return '';
        }
    }

    public function addCmdsmartplug()
    {

        /*   add currentRunTimeHour format hh:mm:ss */

        $currentRunTimeHour = $this->getCmd(null, 'currentRunTimeHour');
        if (!is_object($currentRunTimeHour)) {
            $currentRunTimeHour = new wifismartplugCmd();
            $currentRunTimeHour->setLogicalId('currentRunTimeHour');
            $currentRunTimeHour->setIsVisible(1);
            $currentRunTimeHour->setName(__('currentRunTimeHour', __FILE__));
        }
        $currentRunTimeHour->setType('info');
        $currentRunTimeHour->setSubType('other');
        $currentRunTimeHour->setEqLogic_id($this->getId());
        $currentRunTimeHour->save();

        /*   add currentRunTime en seconde */

        $currentRunTime = $this->getCmd(null, self::$_CURRENT_RUNTIME);
        if (!is_object($currentRunTime)) {
            $currentRunTime = new wifismartplugCmd();
            $currentRunTime->setLogicalId('currentRunTime');
            $currentRunTime->setIsVisible(1);
            $currentRunTime->setName(__('currentRunTime', __FILE__));
        }
        $currentRunTime->setType('info');
        $currentRunTime->setSubType('numeric');
        $currentRunTime->setEqLogic_id($this->getId());
        $currentRunTime->save();

        /* add mac Adresse */

        $macAddress = $this->getCmd(null, self::$_MAC_ADDRESS);
        if (!is_object($macAddress)) {
            $macAddress = new wifismartplugCmd();
            $macAddress->setLogicalId('macAddress');
            $macAddress->setIsVisible(0);
            $macAddress->setName(__('macAddress', __FILE__));
        }
        $macAddress->setType('info');
        $macAddress->setSubType('other');
        $macAddress->setEqLogic_id($this->getId());
        $macAddress->save();


        /* add alias */

        $alias = $this->getCmd(null, self::$_ALIAS);
        if (!is_object($alias)) {
            $alias = new wifismartplugCmd();
            $alias->setLogicalId('alias');
            $alias->setIsVisible(0);
            $alias->setName(__('alias', __FILE__));
        }
        $alias->setType('info');
        $alias->setSubType('other');
        $alias->setEqLogic_id($this->getId());
        $alias->save();

        /* -- pour HSS110 ---*/
        $model = $this->getConfiguration(self::$_CONFIG_MODEL);
        if ($model == 'HS110') {

            /* add dailyConso */

            $dailyconso = $this->getCmd(null, 'dailyConso');
            if (!is_object($dailyconso)) {
                $dailyconso = new wifismartplugCmd();
                $dailyconso->setLogicalId('dailyConso');
                $dailyconso->setIsVisible(1);
                $dailyconso->setName(__('dailyConso', __FILE__));
            }
            $dailyconso->setType('info');
            $dailyconso->setSubType('numeric');
            $dailyconso->setEqLogic_id($this->getId());
            $dailyconso->save();


            /* current power */

            $currentpower = $this->getCmd(null, 'currentPower');
            if (!is_object($currentpower)) {
                $currentpower = new wifismartplugCmd();
                $currentpower->setLogicalId('currentPower');
                $currentpower->setIsVisible(1);
                $currentpower->setName(__('currentPower', __FILE__));
            }
            $currentpower->setType('info');
            $currentpower->setSubType('numeric');
            $currentpower->setEqLogic_id($this->getId());
            $currentpower->save();


            /* current voltage */

            $voltage = $this->getCmd(null, 'voltage');
            if (!is_object($voltage)) {
                $voltage = new wifismartplugCmd();
                $voltage->setLogicalId('voltage');
                $voltage->setIsVisible(1);
                $voltage->setName(__('voltage', __FILE__));
            }
            $voltage->setType('info');
            $voltage->setSubType('numeric');
            $voltage->setEqLogic_id($this->getId());
            $voltage->save();


        }


    }


    /*     * *********************Méthodes d'instance************************* */

    public function preInsert()
    {
        $this->setCategory('energy', 1);

    }

    public function postInsert()
    {

    }

    public function preSave()
    {

    }

    /**
     * Méthode appelée une fois l'équipement sauvegardé.
     * Créée l'ensemble des commandes nécessaires
     *
     * @throws Exception
     */
    public function postSave()
    {

        if (!$this->getId())
            return;

        /* ----------------------------*/
        /*       commande commune      */
        /* ----------------------------*/

        /* etat */
        $etat = $this->getCmd(null, 'etat');
        if (!is_object($etat)) {
            $etat = new wifismartplugCmd();
            $etat->setLogicalId('etat');
            $etat->setName(__('Etat', __FILE__));
        }
        $etat->setType('info');
        $etat->setIsVisible(1);
        $etat->setDisplay('generic_type', 'ENERGY_STATE');
        $etat->setSubType('binary');
        $etat->setEqLogic_id($this->getId());
        $etat->save();
        $etatid = $etat->getId();

        /* on */
        $on = $this->getCmd(null, 'on');
        if (!is_object($on)) {
            $on = new wifismartplugCmd();
            $on->setLogicalId('on');
            $on->setName(__('On', __FILE__));
        }
        $on->setType('action');
        $on->setIsVisible(0);
        $on->setDisplay('generic_type', 'ENERGY_ON');
        $on->setSubType('other');
        $on->setEqLogic_id($this->getId());
        $on->setValue($etatid);
        $on->save();

        /* off */
        $off = $this->getCmd(null, 'off');
        if (!is_object($off)) {
            $off = new wifismartplugCmd();
            $off->setLogicalId('off');
            $off->setName(__('Off', __FILE__));
        }
        $off->setType('action');
        $off->setIsVisible(0);
        $off->setDisplay('generic_type', 'ENERGY_OFF');
        $off->setSubType('other');
        $off->setEqLogic_id($this->getId());
        $off->setValue($etatid);
        $off->save();

        /* nightmode */
        $nightmode = $this->getCmd(null, self::$_NIGHT_MODE);
        if (!is_object($nightmode)) {
            $nightmode = new wifismartplugCmd();
            $nightmode->setLogicalId(self::$_NIGHT_MODE);
            $nightmode->setName(__('Nightmode', __FILE__));
        }
        $nightmode->setType('info');
        $nightmode->setIsVisible(1);
        $nightmode->setDisplay('generic_type', 'ENERGY_STATE');
        $nightmode->setSubType('binary');
        $nightmode->setEqLogic_id($this->getId());
        $nightmode->save();
        $nightmodeid = $nightmode->getId();

        /* nightmodeon */
        $nightmodeon = $this->getCmd(null, self::$_NIGHT_MODE_ON);
        if (!is_object($nightmodeon)) {
            $nightmodeon = new wifismartplugCmd();
            $nightmodeon->setLogicalId(self::$_NIGHT_MODE_ON);
            $nightmodeon->setName(__('NightModeOn', __FILE__));
        }
        $nightmodeon->setType('action');
        $nightmodeon->setIsVisible(0);
        $nightmodeon->setDisplay('generic_type', 'ENERGY_ON');
        $nightmodeon->setSubType('other');
        $nightmodeon->setEqLogic_id($this->getId());
        $nightmodeon->setValue($nightmodeid);
        $nightmodeon->save();

        /* nightmodeoff */
        $nightmodeoff = $this->getCmd(null, self::$_NIGHT_MODE_OFF);
        if (!is_object($nightmodeoff)) {
            $nightmodeoff = new wifismartplugCmd();
            $nightmodeoff->setLogicalId(self::$_NIGHT_MODE_OFF);
            $nightmodeoff->setName(__('NightModeOff', __FILE__));
        }
        $nightmodeoff->setType('action');
        $nightmodeoff->setIsVisible(0);
        $nightmodeoff->setDisplay('generic_type', 'ENERGY_OFF');
        $nightmodeoff->setSubType('other');
        $nightmodeoff->setEqLogic_id($this->getId());
        $nightmodeoff->setValue($nightmodeid);
        $nightmodeoff->save();

        /* refresh */
        $refresh = $this->getCmd(null, self::$_REFRESH);
        if (!is_object($refresh)) {
            $refresh = new wifismartplugCmd();
            $refresh->setLogicalId(self::$_REFRESH);
            $refresh->setIsVisible(1);
            $refresh->setName(__('Rafraichir', __FILE__));
        }
        $refresh->setType('action');
        $refresh->setSubType('other');
        $refresh->setEqLogic_id($this->getId());
        $refresh->save();


        /* a faire tester le constructeur pour appeler la methode d'ajout de commande 
         spécifique en fonction constructeur et modéle
         */

        $this->addCmdsmartplug();

    }

    /* test @ip */

    public function testIp()
    {

        // test if @IP exist
        $host = $this->getConfiguration(self::$_CONFIG_ADDR);
        log::add(__CLASS__, 'debug', $host);
        $fsock = fsockopen($host, '9999', $errno, $errstr, 10);
        if (!$fsock) {
            fclose($fsock);
            log::add(__CLASS__, 'debug', 'Communication error check @IP :' . $host);
            throw new Exception(__('Communication error check @IP ', __FILE__));

        }
        fclose($fsock);


    }

    public function preUpdate()
    {
        $this->testIp();
    }

    public function postAjax()
    {
        $this->cron($this->getId());
    }

    /*
     * Non obligatoire mais permet de modifier l'affichage du widget si vous en avez besoin */

    public function toHtml($_version = 'dashboard')
    {
        $replace = $this->preToHtml($_version);
        if (!is_array($replace)) {
            return $replace;
        }

        $version = jeedom::versionAlias($_version);
        if ($this->getDisplay('hideOn' . $version) == 1) {
            return '';
        }

        foreach ($this->getCmd() as $cmd) {
            $logicalId = $cmd->getLogicalId();
            $cmdId = $cmd->getId();
            if ($cmd->getType() == 'info') {
                $replace['#' . $logicalId . '_history#'] = '';
                $replace['#' . $logicalId . '#'] = $cmd->execCmd();
                $replace['#' . $logicalId . '_id#'] = $cmdId;
                $replace['#' . $logicalId . '_collectDate#'] = $cmd->getCollectDate();
                if ($cmd->getIsHistorized() == 1) {
                    $replace['#' . $logicalId . '_history#'] = 'history cursor';
                }
            } else {
                $replace['#' . $logicalId . '_id#'] = $cmdId;
            }
        }

        return $this->postToHtml($_version, template_replace($replace, getTemplate('core', $version, $this->getConfiguration('model'), __CLASS__)));


    }
}

