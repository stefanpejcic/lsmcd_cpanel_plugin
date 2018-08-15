<?php

/* * ******************************************
 * LiteSpeed LSMCD User Management Plugin for cPanel
 * @author: LiteSpeed Technologies, Inc. (https://www.litespeedtech.com)
 * @copyright: (c) 2018
 * ******************************************* */

namespace LsmcdUserPanel\View\Model;

use LsmcdUserPanel\CPanelWrapper;
use LsmcdUserPanel\Lsmcd_UserMgr_Controller;
use LsmcdUserPanel\Lsmcd_UserMgr_Util;
use LsmcdUserPanel\Lsc\UserLogger;
use LsmcdUserPanel\Lsc\UserLSMCDException;
use LsmcdUserPanel\Lsc\Context\UserPanelContextOption;


class ChangePasswordModel
{

    const FLD_USER = 'user';
    const FLD_SERVER = 'server';
    const FLD_MESSAGE = 'message';
    const FLD_DONE = 'done';
    const FLD_PWD1DISABLED = 'pwd1Disabled';
    const FLD_PWD2DISABLED = 'pwd2Disabled';

    /**
     * @var mixed[]
     */
    private $tplData = array();
    
    /**
     *
     * @param UserControlPanel  $panelEnv
     */
    public function __construct($subFunction)
    {
        $this->init($subFunction);
    }

    private function doChange($user, $password)
    {
        /* Create password file as it only works with a file */
        $result = FALSE;
        
        $cpanel = CPanelWrapper::getCpanelObj();

        $result = $cpanel->uapi('lsmcd', 'issueSaslChangePassword',
                                array ('password' => $password ));
        $return_var = $result['cpanelresult']['result']['data']['retVar'];
        $resOutput = $result['cpanelresult']['result']['data']['output'];
        if ($return_var > 0) 
        {
            $this->setMessage('saslpasswd2 error: ' . $resOutput);
        }
        else
        {
            $this->setMessage("Password set successfully");
            $result = TRUE;
        }
        return $result;
        
    }
    
    
    private function tryChange()
    {
        $password1 = $_POST["pwd1"];
        $password2 = $_POST["pwd2"];
        if ((strlen($password1) == 0) ||
            (strlen($password2) == 0))
        {
            $this->setMessage('ERROR: Enter the new password in both fields');
        }
        else if (strcmp($password1, $password2) != 0)
        {
            $this->setMessage('ERROR: Passwords do not match.');
        }
        else 
        {
            if ($this->doChange(Lsmcd_UserMgr_Util::getCurrentCpanelUser(), 
                                $password1))
            {
                $this->setDone("DONE!");
                $this->disabledPwd1(TRUE);
                $this->disabledPwd2(TRUE);
            }
        }
    }
   
    private function init($subFunction)
    {
        $this->setUser();
        $this->setServer();
        $this->setDone('');
        $this->disabledPwd1(FALSE);
        $this->disabledPwd2(FALSE);
        if ($subFunction != '')
            $this->tryChange();
        else 
            $this->setMessage('');
    }

    /**
     *
     * @param string  $feild
     * @return null|mixed
     */
    public function getTplData( $feild )
    {
        if ( !isset($this->tplData[$feild]) ) {
            return null;
        }

        return $this->tplData[$feild];
    }

    private function setUser()
    {
        $this->tplData[self::FLD_USER] = 
                Lsmcd_UserMgr_Util::getCurrentCpanelUser();
    }
    
    private function setServer()
    {
        $this->tplData[self::FLD_SERVER] = Lsmcd_UserMgr_Util::getServerAddr();
    }

    private function strArray($array)
    {
        $str = '';
        foreach ($array as $key => $value)
        {
            if (strlen($str))
                $str .= ', ';
            $str .= 'key: ' . $key . ' value: ' . $value; 
        }
        return $str;
    }
    
    
    public function setMessage($message)
    {
        $this->tplData[self::FLD_MESSAGE] = $message;
    }

    public function setDone($flag)
    {
        $this->tplData[self::FLD_DONE] = $flag;
    }
    /**
     *
     * @return string
     */
    public function getTpl()
    {
        return realpath(__DIR__ . '/../Tpl') . '/ChangePassword.tpl';
    }
    
    public function disabledPwd1($disabled)
    {
        $this->tplData[self::FLD_PWD1DISABLED] = ($disabled ? 'disabled' : '');
    }

    public function disabledPwd2($disabled)
    {
        $this->tplData[self::FLD_PWD2DISABLED] = ($disabled ? 'disabled' : '');
    }

}
