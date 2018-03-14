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

class wifismartplug extends eqLogic {
    /*     * *************************Attributs****************************** */
    public static $_widgetPossibility = array('custom' => true);
  

    /*     * ***********************Methode static*************************** */

     /* Fonction exécutée automatiquement toutes les minutes par Jeedom */
      public static function cron($_eqlogic_id = null) {

          if($_eqlogic_id !== null){
              $eqLogics = array(eqLogic::byId($_eqlogic_id));
          }else{
              $eqLogics = eqLogic::byType('wifismartplug');
          }
          
          foreach($eqLogics as $smartplug) {
              if ($smartplug->getIsEnable() == 1) {
                  log::add('wifismartplug', 'debug', 'Pull Cron pour wifismartplug' );
                  $smartplugID = $smartplug->getId();
                  log::add('wifismartplug', 'debug', 'ID : '.$smartplug->getId() );
                   log::add('wifismartplug', 'debug', 'Name : '.$smartplug->getName() );
                  
                  /* ilfaudrait tester sur le model afin d'appeler par la suite la bonne methode pour les infos */
                  
                $smartpluginfo = $smartplug->getsmartplugInfo();
              }
          }

          return;
      }


    /*
     * Fonction exécutée automatiquement toutes les heures par Jeedom
      public static function cronHourly() {

      }
     */

    /*
     * Fonction exécutée automatiquement tous les jours par Jeedom
      public static function cronDayly() {

      }
     */


  public function getsmartplugInfo() {
      
      try {
      
          $changed = false;
          
          $ipsmartplug = $this->getConfiguration('addr');
          $modelsmartplug = $this->getConfiguration('model');
          
          /* first get relay status, nightmode, mac address alias currentruntime from info */
           $command = '/usr/bin/python ' .dirname(__FILE__).'/../../3rparty/smartplug.py  -t ' . $ipsmartplug . ' -c info';
           $result=trim(shell_exec($command));
           log::add('wifismartplug','debug','retour [info]');
           log::add('wifismartplug','debug',$command);
           log::add('wifismartplug','debug',$result);
          
          /* decode reponse info */
           $jsoninfo = json_decode($result,true);
           $state =$jsoninfo['system']['get_sysinfo']['relay_state'];
           $runtTime =$jsoninfo['system']['get_sysinfo']['on_time'];
           $mac =$jsoninfo['system']['get_sysinfo']['mac'];
           $alias =$jsoninfo['system']['get_sysinfo']['alias'];
           $nightmode =$jsoninfo['system']['get_sysinfo']['led_off'];
          
            log::add('wifismartplug','debug', 'state : '.$state );
            log::add('wifismartplug','debug', 'mac : '.$mac );
            log::add('wifismartplug','debug', 'alias : '.$alias );
            log::add('wifismartplug','debug', 'runTime : '.$runtTime );
            log::add('wifismartplug','debug', 'nightmode : '.$nightmode );
   
          
          /* set etat */
          $statecmd = wifismartplugCmd::byEqLogicIdAndLogicalId($this->getId(),'etat');
          if (is_object($statecmd)) {
              if ($statecmd->execCmd() == null || $statecmd->execCmd() != $state) {
                  $changed = true;
                  $statecmd->setCollectDate('');
                  $statecmd->event($state);
              }
          }
          
          
          /* set nightemod */
          $statecmd = wifismartplugCmd::byEqLogicIdAndLogicalId($this->getId(),'nightmode');
          if (is_object($statecmd)) {
              if ($statecmd->execCmd() == null || $statecmd->execCmd() != $nightmode) {
                  $changed = true;
                  $statecmd->setCollectDate('');
                  $statecmd->event($nightmode);
              }
          }
          
          
          /* set currentRunTime */
          $statecmd = wifismartplugCmd::byEqLogicIdAndLogicalId($this->getId(),'currentRunTime');
          if (is_object($statecmd)) {
              if ($statecmd->execCmd() == null || $statecmd->execCmd() != $runtTime) {
                  $changed = true;
                  $statecmd->setCollectDate('');
                  $statecmd->event($runtTime);
              }
          }


          /* set macAddress*/
          $statecmd = wifismartplugCmd::byEqLogicIdAndLogicalId($this->getId(),'macAddress');
          if (is_object($statecmd)) {
              if ($statecmd->execCmd() == null || $statecmd->execCmd() != $mac) {
                  $changed = true;
                  $statecmd->setCollectDate('');
                  $statecmd->event($mac);
              }
          }
           
          /* set alias*/
          $statecmd = wifismartplugCmd::byEqLogicIdAndLogicalId($this->getId(),'alias');
          if (is_object($statecmd)) {
              if ($statecmd->execCmd() == null || $statecmd->execCmd() != $alias) {
                  $changed = true;
                  $statecmd->setCollectDate('');
                  $statecmd->event($alias);
              }
          }
          

          /* ajout commande pour modele HS110 */
          
          $model = $this->getConfiguration('model');
          if($model == 'HS110') {
              
              
              /* -- set daily consumption --*/
              $command = '/usr/bin/python ' .dirname(__FILE__).'/../../3rparty/smartplug.py  -t ' . $ipsmartplug . ' -c dailyConsumption';
              $result=trim(shell_exec($command));
              log::add('wifismartplug','debug','retour [dailyConso]');
              log::add('wifismartplug','debug',$command);
              log::add('wifismartplug','debug',$result);
              $statecmd = wifismartplugCmd::byEqLogicIdAndLogicalId($this->getId(),'dailyConso');
              if (is_object($statecmd)) {
                  if ($statecmd->execCmd() == null || $statecmd->execCmd() != $retourcommand) {
                      $changed = true;
                      $statecmd->setCollectDate('');
                      $statecmd->event($result);
                  }
              }
              
              /* power and voltage from */
              $command = '/usr/bin/python ' .dirname(__FILE__).'/../../3rparty/smartplug.py  -t ' . $ipsmartplug . ' -c realtimeVoltage';
              $result=trim(shell_exec($command));
              log::add('wifismartplug','debug','retour [realvoltage]');
              log::add('wifismartplug','debug',$command);
              log::add('wifismartplug','debug',$result);
              
              /* decode reponse info */
              $jsoninfo = json_decode($result,true);
              $voltage =$jsoninfo['emeter']['get_realtime']['voltage'];
              $power =$jsoninfo['emeter']['get_realtime']['power'];
              
              log::add('wifismartplug','debug', 'voltage : '.$voltage );
              log::add('wifismartplug','debug', 'power : '.$power );

              
              /*--set current power --*/
              $statecmd = wifismartplugCmd::byEqLogicIdAndLogicalId($this->getId(),'currentPower');
              if (is_object($statecmd)) {
                  if ($statecmd->execCmd() == null || $statecmd->execCmd() != $power) {
                      $changed = true;
                      $statecmd->setCollectDate('');
                      $statecmd->event($power);
                  }
              }

              /*--set voltage--*/

              $statecmd = wifismartplugCmd::byEqLogicIdAndLogicalId($this->getId(),'voltage');
              if (is_object($statecmd)) {
                  if ($statecmd->execCmd() == null || $statecmd->execCmd() != $voltage) {
                      $changed = true;
                      $statecmd->setCollectDate('');
                      $statecmd->event($voltage);
                  }
              }
              
              
          }
          
          
          if ($changed == true){
              $this->refreshWidget();
          }
          
      
       
        } catch (Exception $e) {
              log::add('wifismartplug','debug',$e);
      return '';
		}
  }

    public function addCmdsmartplug() {
        
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
        
        $currentRunTime = $this->getCmd(null, 'currentRunTime');
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
        
        $macAddress = $this->getCmd(null, 'macAddress');
        if (!is_object($macAddress)) {
            $macAddress = new wifismartplugCmd();
            $macAddress->setLogicalId('macAddress');
            $macAddress->setIsVisible(1);
            $macAddress->setName(__('macAddress', __FILE__));
        }
        $macAddress->setType('info');
        $macAddress->setSubType('other');
        $macAddress->setEqLogic_id($this->getId());
        $macAddress->save();
        
        
        /* add alias */
        
        $alias = $this->getCmd(null, 'alias');
        if (!is_object($alias)) {
            $alias = new wifismartplugCmd();
            $alias->setLogicalId('alias');
            $alias->setIsVisible(1);
            $alias->setName(__('alias', __FILE__));
        }
        $alias->setType('info');
        $alias->setSubType('other');
        $alias->setEqLogic_id($this->getId());
        $alias->save();

          /* -- pour HSS110 ---*/
        $model = $this->getConfiguration('model');
        if($model == 'HS110') {
            
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

    public function preInsert() {
        $this->setCategory('energy', 1);
        
    }

    public function postInsert() {
        
    }

    public function preSave() {
        
    }

    public function postSave() {
        
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
        $etat->setDisplay('generic_type','ENERGY_STATE');
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
        $on->setDisplay('generic_type','ENERGY_ON');
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
        $off->setDisplay('generic_type','ENERGY_OFF');
        $off->setSubType('other');
        $off->setEqLogic_id($this->getId());
        $off->setValue($etatid);
        $off->save();
        
        /* nightmode */
        $nightmode = $this->getCmd(null, 'nightmode');
        if (!is_object($nightmode)) {
            $nightmode = new wifismartplugCmd();
            $nightmode->setLogicalId('nightmode');
            $nightmode->setName(__('Nightmode', __FILE__));
        }
        $nightmode->setType('info');
        $nightmode->setIsVisible(1);
        $nightmode->setDisplay('generic_type','ENERGY_STATE');
        $nightmode->setSubType('binary');
        $nightmode->setEqLogic_id($this->getId());
        $nightmode->save();
        $nightmodeid = $nightmode->getId();
        
        /* nightmodeon */
        $nightmodeon = $this->getCmd(null, 'nightmodeon');
        if (!is_object($nightmodeon)) {
            $nightmodeon = new wifismartplugCmd();
            $nightmodeon->setLogicalId('nightmodeon');
            $nightmodeon->setName(__('NightModeOn', __FILE__));
        }
        $nightmodeon->setType('action');
        $nightmodeon->setIsVisible(0);
        $nightmodeon->setDisplay('generic_type','ENERGY_ON');
        $nightmodeon->setSubType('other');
        $nightmodeon->setEqLogic_id($this->getId());
        $nightmodeon->setValue($nightmodeid);
        $nightmodeon->save();
        
        /* nightmodeoff */
        $nightmodeoff = $this->getCmd(null, 'nightmodeoff');
        if (!is_object($nightmodeoff)) {
            $nightmodeoff = new wifismartplugCmd();
            $nightmodeoff->setLogicalId('nightmodeoff');
            $nightmodeoff->setName(__('NightModeOff', __FILE__));
        }
        $nightmodeoff->setType('action');
        $nightmodeoff->setIsVisible(0);
        $nightmodeoff->setDisplay('generic_type','ENERGY_OFF');
        $nightmodeoff->setSubType('other');
        $nightmodeoff->setEqLogic_id($this->getId());
        $nightmodeoff->setValue($nightmodeid);
        $nightmodeoff->save();
        
        /* refresh */
        $refresh = $this->getCmd(null, 'refresh');
        if (!is_object($refresh)) {
            $refresh = new wifismartplugCmd();
            $refresh->setLogicalId('refresh');
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
    
    public function testIp(){
        
        // test if @IP exist
        $host =  $this->getConfiguration('addr');
        log::add('wifismartplug', 'debug',$host );
        $fsock = fsockopen($host, '9999', $errno, $errstr, 10   );
        if (! $fsock )
        {
            fclose($fsock);
             log::add('wifismartplug', 'debug','Communication error check @IP :'. $host );
            throw new Exception(__('Communication error check @IP ',__FILE__));

        }
        fclose($fsock);
       

    }

    public function preUpdate() {
         $this->testIp();
                   }

    public function postUpdate() {
        
    }

    public function preRemove() {
        
    }

    public function postRemove() {
        
    }
    
    public function postAjax() {
        $this->cron($this->getId());
    }

    /*
     * Non obligatoire mais permet de modifier l'affichage du widget si vous en avez besoin */
    
      public function toHtml($_version = 'dashboard') {
          $replace = $this->preToHtml($_version);
          if (!is_array($replace)) {
              return $replace;
          }
          
          $version = jeedom::versionAlias($_version);
          if ($this->getDisplay('hideOn' . $version) == 1) {
              return '';
          }
          
          foreach ($this->getCmd() as $cmd) {
              if ($cmd->getType() == 'info') {
                  $replace['#' . $cmd->getLogicalId() . '_history#'] = '';
                  $replace['#' . $cmd->getLogicalId() . '#'] = $cmd->execCmd();
                  $replace['#' . $cmd->getLogicalId() . '_id#'] = $cmd->getId();
                  $replace['#' . $cmd->getLogicalId() . '_collectDate#'] = $cmd->getCollectDate();
                  if ($cmd->getIsHistorized() == 1) {
                      $replace['#' . $cmd->getLogicalId() . '_history#'] = 'history cursor';
                  }
              } else {
                  $replace['#' . $cmd->getLogicalId() . '_id#'] = $cmd->getId();
              }
          }
          
          return $this->postToHtml($_version, template_replace($replace, getTemplate('core', $version, $this->getConfiguration('model'), 'wifismartplug')));
          
          
      }


    /*     * **********************Getteur Setteur*************************** */
}

class wifismartplugCmd extends cmd {
    /*     * *************************Attributs****************************** */


    /*     * ***********************Methode static*************************** */


    /*     * *********************Methode d'instance************************* */

    /*
     * Non obligatoire permet de demander de ne pas supprimer les commandes même si elles ne sont pas dans la nouvelle configuration de l'équipement envoyé en JS
      public function dontRemoveCmd() {
      return true;
      }
     */

    public function execute($_options = array()) {
        
        if ($this->getType() == '') {
            return '';
        }
        
        $action= $this->getLogicalId();
        $eqLogic = $this->getEqlogic();
        $ipsmartplug = $eqLogic->getConfiguration('addr');
        
          log::add('wifismartplug', 'debug','action'. $action );
        log::add('wifismartplug', 'debug', $eqLogic );
        
        if ($action == 'refresh') {
            $eqLogic->cron($eqLogic->getId());
             log::add('wifismartplug','debug','REFRESH !!!');
        }
        
        /*  a modifier par la suite pour prendre en compte different constructeur */
        
         /* set  : on */
        if ($action == 'on') {
            $command = '/usr/bin/python ' .dirname(__FILE__).'/../../3rparty/smartplug.py  -t '  . $ipsmartplug . ' -c on';
           $result=trim(shell_exec($command));
            log::add('wifismartplug','debug','action on');
            log::add('wifismartplug','debug',$command);
            log::add('wifismartplug','debug',$result);
            $eqLogic->cron($eqLogic->getId());
        }
        
                /* set  : off */
        if ($action == 'off') {
            $command = '/usr/bin/python ' .dirname(__FILE__).'/../../3rparty/smartplug.py  -t '  . $ipsmartplug . ' -c off';
            $result=trim(shell_exec($command));
            log::add('wifismartplug','debug','action off');
            log::add('wifismartplug','debug',$command);
            log::add('wifismartplug','debug',$result);
            $eqLogic->cron($eqLogic->getId());
        }
        
        /* set  : nightmodeon */
        if ($action == 'nightmodeon') {
            $command = '/usr/bin/python ' .dirname(__FILE__).'/../../3rparty/smartplug.py  -t '  . $ipsmartplug . ' -c nightModeOn';
            $result=trim(shell_exec($command));
            log::add('wifismartplug','debug','action nightModeOn');
            log::add('wifismartplug','debug',$command);
            log::add('wifismartplug','debug',$result);
            $eqLogic->cron($eqLogic->getId());
        }
        
        /* set  : nightmodeoff */
        if ($action == 'nightmodeoff') {
            $command = '/usr/bin/python ' .dirname(__FILE__).'/../../3rparty/smartplug.py  -t '  . $ipsmartplug . ' -c nightModeOff';
             $result=trim(shell_exec($command));
            log::add('wifismartplug','debug','action nightModeOff');
            log::add('wifismartplug','debug',$command);
            log::add('wifismartplug','debug',$result);
            $eqLogic->cron($eqLogic->getId());
        }

        
    }

    /*     * **********************Getteur Setteur*************************** */
}

?>
