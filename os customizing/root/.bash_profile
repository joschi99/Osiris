# .bash_profile

# Get the aliases and functions
if [ -f ~/.bashrc ]; then
        . ~/.bashrc
fi

# User specific environment and startup programs

PATH=$PATH:$HOME/bin

export PATH


sw=/opt/bi-s/software
plugins=/usr/lib/nagios/plugins
centlog=/var/log/centreon-engine/centengine.log
backup=/opt/bi-s/cifs/backup
syslog=/opt/bi-s/cifs/rsyslog
tools=/opt/bi-s/cifs/tools

alias sw="cd $sw"
alias plugins="cd $plugins"
alias centlog="tail -f $centlog"
alias backup="cd $backup"
alias syslog="cd $syslog"
alias tools="cd $tools"
alias toat="shutdown -h now"

ORACLE_BASE=/opt/oracle
ORACLE_HOME=$ORACLE_BASE/112
#LD_LIBRARY_PATH=$ORACLE_HOME/lib
PATH=$PATH:$ORACLE_HOME/bin
export ORACLE_BASE ORACLE_HOME LD_LIBRARY_PATH PATH