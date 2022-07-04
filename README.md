## LSMCD Secure User Data CloudLinux/cPanel Interface 

This facility provides a user interface for those CloudLinux/cPanel users using LSMCD configured with SASL and User-Level security.  See [[litespeed_wiki:lsmcd:sasl_secure_user_data|LSMCD Secure User Data Using SASL]].

This interface is intended for cPanel end-users.  This panel lets a user change their own password and see statistics for their specific data in their user-managed LSMCD space.

WHM Administrators will need to use the command line SASL to create and delete users. Users must be created in advance and must match their cPanel user names. There is a way to automatically create a user for each cPanel user for WHM administrator explained later in this article.

## Installation 

Installation must be performed on the system running as root.  The process is:

  - Download the software
  - Run the installation script.

### Download the Software 

The easiest way to download the software is to clone the specific git repository.  This is done from a root command prompt, after changing to a directory where the software can be stored (''cd /tmp'' is often used):
  git clone https://github.com/rperper/lsmcd_cpanel_plugin.git
  
### Install the Software

To install the software you will need to change to the correct directory where the installation script is stored and execute the script:
  cd lsmcd_cpanel_plugin/res/lsmcd_usermgr
  ./install.sh
The install should run without errors, but any significant ones will be displayed on the screen.  It will determine if lsmcd has been installed and install it if it's not already there.

If you see missing dependencies, particularly concerning Perl and Git, check your ''/etc/yum.conf'' file.  You must not have ''perl*'' in the exclude list.  If it's there, temporarily remove it and try the install script again.

## Administration

You must configure LSMCD and SASL using the sasldb method which uses the ''saslpasswd2'' program.  This is described at [[litespeed_wiki:lsmcd:new_sasl|LSMCD Security Using SASL]]

Users must be created in advance and must match their cPanel user names.  Passwords and stats can be managed by the users themselves using the cPanel plugin described here.

There is a way to automatically create a user for each cPanel user for WHM administrator. You may use a script like the following when you ssh login as a root user:

<code>
#!/bin/bash

user_list=$(sasldblistusers2 /etc/sasllsmcd | cut -d@ -f1)
#get current user list

for name in $(ls /home/);
do 
  if [[ -d /home/$name/public_html ]] ; then
  #check public_html existance to make sure it's vhost user instead of cPanel created dir
        if ! echo $user_list | grep -i -q $name ; then    
            #check if user already in the list to avoid override existing users
            passwd=$(head /dev/urandom | tr -dc A-Za-z0-9 | head -c 10 ; echo '')
            echo $passwd | saslpasswd2 -p -f /etc/sasllsmcd $name
            # use -p to set a random password without prompt 
            echo "$name added into LSMCD"
        else 
            echo "$name already in the list..."
        fi
  fi
done
</code>

You can also use the similar commands to create a custom script, and hook up with cPanel user creation to auto-run it.

  passwd=$(head /dev/urandom | tr -dc A-Za-z0-9 | head -c 10 ; echo '')
  echo $passwd | saslpasswd2 -p -f /etc/sasllsmcd $name

### Use 
Once the software is installed, cPanel users will see a new option in their **Advanced** group:

<img src="https://www.litespeedtech.com/support/wiki/lib/exe/fetch.php/litespeed_wiki:lsmcd:lsmcdmenuitem.jpg"></img>

When the item has been selected users will be brought to the main menu:

<img src="https://www.litespeedtech.com/support/wiki/lib/exe/fetch.php/litespeed_wiki:lsmcd:lsmcdcpanelmain.jpg"></img>

This screen has 3 groups of data:
  - Who you are:
    - User to be used for LSMCD (the logged on user)
    - LSMCD server address extracted from ''/usr/local/lsmcd/conf/node.conf''.  Can be an IP address/port or UDS (Unix Domain Socket).
    - Whether SASL security is enabled (the setting of ''Cached.UseSasl'' in node.conf)
    - Whether User Level Security is enabled (the setting of ''Cached.DataByUser'' in node.conf).
  - A button to change the password.  Will only be enabled if SASL and User Level Security is enabled.
  - A button to display stats.  If user level security is enabled, the stats will be only for the user.  If no security is enabled, the stats are system wide.  Otherwise the button is disabled.

### Change Password

The ''Change Password'' button will only be enabled if both SASL and user level security is enabled.  This facility is provided as access to a command prompt for running saslpasswd2 is not available to regular users, and regular users need the ability to keep the SASL password consistent with company policy.  Press the button to enter the **Change Password** screen.

<img src="https://www.litespeedtech.com/support/wiki/lib/exe/fetch.php/litespeed_wiki:lsmcd:lsmcdcpanelchangepassword.jpg"></img>

As is common with password change facilities, the new password must be entered twice and must match.  Other than requiring a password, no additional password restrictions are placed on the password.  When the user enters the new password in both text boxes and presses **Change Password**, the ''saslpasswd2'' program is run and it is up to that program to validate the new password with system restrictions.

If there are errors, they are displayed in this screen and the user can fix the problem and try again.  If the password change is successful, that fact is displayed and the text of the button is changed to **Ok**.  When the user presses the button, the main window is redisplayed.

### Display Stats
To display the Memcached statistics for the user (or the system as a whole if security is disabled), the user can press the **Display Stats** button.

Stats are displayed in the format below, the format determined by LSMCD, and are basically identical to those available from Memcached.  The primary difference being that the stats only reflect activity for those transactions done by the user validated by SASL and LSMCD.

<img src="https://www.litespeedtech.com/support/wiki/lib/exe/fetch.php/litespeed_wiki:lsmcd:lsmcdcpanelstats.jpg"></img>

Use the browser **Back** button to return to the main window.

